<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($code) ?> <?= e($title) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="guest">
<div class="error-page">
    <div class="error-code"><?= e($code) ?></div>
    <h1><?= e($title) ?></h1>
    <p class="muted"><?= e($message ?: 'Silakan kembali ke halaman sebelumnya.') ?></p>
    <a class="btn btn-primary" href="<?= e(url(Auth::check() ? 'dashboard' : 'login')) ?>">Kembali ke beranda</a>
</div>
</body>
</html>
