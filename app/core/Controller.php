<?php
/**
 * Kelas dasar controller: render view di dalam layout dan validasi sederhana.
 */
abstract class Controller
{
    /** Aksi yang hanya boleh diakses dengan metode POST (diperiksa router). */
    public array $postActions = ['store', 'update', 'delete'];

    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new RuntimeException("View $view tidak ditemukan");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require APP_PATH . '/views/' . $layout . '.php';
    }

    /**
     * Validasi input berdasarkan aturan sederhana.
     * Contoh aturan: 'required|max:100', 'required|int|min:1|max:6', 'email', 'date', 'in:L,P'.
     * Mengembalikan array pesan galat per field (kosong berarti valid).
     */
    protected function validate(array $input, array $rules, array $labels = []): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $value = trim((string) ($input[$field] ?? ''));
            $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
            foreach (explode('|', $ruleString) as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                if ($name === 'required' && $value === '') {
                    $errors[$field] = "$label wajib diisi.";
                    break;
                }
                if ($value === '') {
                    continue;
                }
                $isNumeric = str_contains($ruleString, 'int') || str_contains($ruleString, 'numeric');
                $msg = match ($name) {
                    'int'     => filter_var($value, FILTER_VALIDATE_INT) === false ? "$label harus berupa bilangan bulat." : null,
                    'numeric' => !is_numeric($value) ? "$label harus berupa angka." : null,
                    'min'     => $isNumeric ? ((float) $value < (float) $arg ? "$label minimal $arg." : null)
                                            : (mb_strlen($value) < (int) $arg ? "$label minimal $arg karakter." : null),
                    'max'     => $isNumeric ? ((float) $value > (float) $arg ? "$label maksimal $arg." : null)
                                            : (mb_strlen($value) > (int) $arg ? "$label maksimal $arg karakter." : null),
                    'digits'  => !preg_match('/^\d{' . (int) $arg . '}$/', $value) ? "$label harus $arg digit angka." : null,
                    'email'   => filter_var($value, FILTER_VALIDATE_EMAIL) === false ? "Format $label tidak valid." : null,
                    'date'    => !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) || !strtotime($value) ? "$label harus berupa tanggal yang valid." : null,
                    'time'    => !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value) ? "$label harus berformat JJ:MM." : null,
                    'in'      => !in_array($value, explode(',', (string) $arg), true) ? "$label tidak valid." : null,
                    'regex'   => !preg_match((string) $arg, $value) ? "Format $label tidak valid." : null,
                    default   => null,
                };
                if ($msg !== null) {
                    $errors[$field] = $msg;
                    break;
                }
            }
        }
        return $errors;
    }

    /** Kembali ke form sebelumnya dengan pesan galat dan input lama. */
    protected function backWithErrors(array $errors, string $page, array $params = []): never
    {
        $_SESSION['_errors'] = $errors;
        $_SESSION['_old'] = $_POST;
        unset($_SESSION['_old']['_csrf'], $_SESSION['_old']['password']);
        flash('error', 'Periksa kembali isian formulir.');
        redirect($page, $params);
    }

    protected function idParam(): int
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            abort(404, 'Data tidak ditemukan.');
        }
        return (int) $id;
    }
}
