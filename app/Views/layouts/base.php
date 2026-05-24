<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="<?= session("toko_theme") ?>" data-layout="vertical">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" type="image/png" href="<?= base_url(); ?>/assets/images/zulfa.png" />
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/styles.css" />
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/libs/sweetalert2/dist/sweetalert2.min.css">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.3.8/b-3.2.6/b-colvis-3.2.6/b-html5-3.2.6/b-print-3.2.6/r-3.0.8/sp-2.3.5/datatables.min.css" rel="stylesheet" integrity="sha384-Ardp6FCkpCmEUMnE5/KjGBWG2nRUVIRu9FC/rX34QDRbJ+ebmGFWYRrv2DGEtRtc" crossorigin="anonymous">

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

    <!-- <script src="<?= base_url(); ?>/assets/js/vendor.min.js"></script> -->
    <script src="<?= base_url(); ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/app.init.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/theme.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/app.min.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/sidebarmenu.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.3.8/b-3.2.6/b-colvis-3.2.6/b-html5-3.2.6/b-print-3.2.6/r-3.0.8/sp-2.3.5/datatables.min.js" integrity="sha384-Lo4Q6eTHry7JUodG9B4/XYYSYOP8lFCvm3oSCs1dk9wQ+ZswNONRRo7glE454e4Y" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="<?= base_url(); ?>/assets/js/plugins/toastr-init.js"></script>
    <?= $this->renderSection('javascript'); ?>

</body>

</html>