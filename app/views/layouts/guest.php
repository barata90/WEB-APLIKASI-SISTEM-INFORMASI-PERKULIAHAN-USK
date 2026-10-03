<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Login') ?> · <?= e(config('app_name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="icon" href="<?= e(asset('img/logo.svg')) ?>">
</head>
<body class="guest">
<?= $content ?>
</body>
</html>
