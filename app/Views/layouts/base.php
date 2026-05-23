<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="<?= session("toko_theme") ?>" data-layout="vertical">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" type="image/png" href="<?= base_url(); ?>/assets/images/zulfa.png" />
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/styles.css" />
    <title>Zulfaa Frozen |<?= esc($title ?? 'Dashboard'); ?></title>
</head>

<body class="link-sidebar">
    <div class="preloader">
        <img src="<?= base_url(); ?>/assets/images/zulfa.png" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <div id="main-wrapper">
        <?= $this->include('layouts/sidebar'); ?>
        <div class="page-wrapper">
            <?= $this->include('layouts/topbar'); ?>
            <?= $this->renderSection('content'); ?>
        </div>
    </div>

    <script src="<?= base_url(); ?>/assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/app.init.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/theme.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/app.min.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/sidebarmenu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

    <?= $this->renderSection('content'); ?>

</body>

</html>