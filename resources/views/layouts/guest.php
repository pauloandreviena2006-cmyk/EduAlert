<?php
/**
 * EduAlert JJP - resources/views/layouts/guest.php
 * Layout para páginas públicas (login, recuperación, instalación)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Sistema') ?> | EduAlert JJP</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="guest-body">
<?php foreach (flash_all() as $__tipo => $__mensajes): ?>
    <?php foreach ($__mensajes as $__msg): ?>
        <div class="flash-alert flash-<?= e($__tipo) ?> position-fixed top-0 start-50 translate-middle-x mt-3 z-3" role="alert" style="min-width:320px">
            <i class="bi bi-<?= $__tipo === 'success' ? 'check-circle' : ($__tipo === 'error' ? 'x-circle' : 'info-circle') ?>"></i>
            <?= e($__msg) ?>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
<main class="guest-main">
    <?= $__contenido ?>
</main>
<script src="<?= asset('vendor/jquery/jquery.min.js') ?>"></script>
<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('vendor/sweetalert2/sweetalert2.min.js') ?>"></script>
<script>window.BASE_URL = '<?= e(BASE_URL) ?>';</script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>