<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Sistema de Filas') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/app.css">
</head>
<body data-page="<?= htmlspecialchars($page ?? '') ?>">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= $basePath ?>/">Sistema de Filas</a>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="<?= $basePath ?>/">Público</a>
            <a class="btn btn-light btn-sm" href="<?= $basePath ?>/admin">Painel Admin</a>
        </div>
    </div>
</nav>

<main class="container pb-5">
    <?= $content ?>
</main>

<script>
window.APP_BASE_URL = '<?= htmlspecialchars($basePath) ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $basePath ?>/assets/js/app.js"></script>
</body>
</html>
