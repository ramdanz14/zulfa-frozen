<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="<?= session("toko_theme") ?>" data-layout="vertical">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" type="image/png" href="<?= base_url(APP_LOGO_PATH); ?>" />
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/styles.css" />
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/libs/sweetalert2/dist/sweetalert2.min.css">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.3.8/b-3.2.6/b-colvis-3.2.6/b-html5-3.2.6/b-print-3.2.6/r-3.0.8/sp-2.3.5/datatables.min.css" rel="stylesheet" integrity="sha384-Ardp6FCkpCmEUMnE5/KjGBWG2nRUVIRu9FC/rX34QDRbJ+ebmGFWYRrv2DGEtRtc" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/libs/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <title><?= esc(APP_NAME); ?> | <?= esc($title ?? 'Dashboard'); ?></title>
</head>

<body class="link-sidebar">
    <div class="preloader">
        <img src="<?= base_url(APP_LOGO_PATH); ?>" alt="loader" class="lds-ripple img-fluid" />
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url(); ?>/assets/js/plugins/toastr-init.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        function normalizeMoneyValue(value) {
            const raw = String(value ?? '').replace(/[^\d-]/g, '');
            if (raw === '' || raw === '-') return '0';
            return raw;
        }

        function formatMoneyValue(value) {
            const raw = String(value ?? '').trim();
            const numeric = Number(raw.replace(/,/g, ''));
            if (raw !== '' && Number.isFinite(numeric)) {
                return Math.round(numeric).toLocaleString('en-US');
            }
            const normalized = normalizeMoneyValue(raw);
            const number = parseInt(normalized, 10) || 0;
            return number.toLocaleString('en-US');
        }

        function normalizeMoneyInputs(scope) {
            const $scope = scope ? $(scope) : $(document);
            $scope.find('input.money').each(function() {
                $(this).val(normalizeMoneyValue($(this).val()));
            });
        }

        function applyMoneyMask(scope) {
            const $scope = scope ? $(scope) : $(document);

            $scope.find('input.money').each(function() {
                const $input = $(this);
                if ($input.data('money-init') === true) return;
                $input.data('money-init', true);
                $input.val(formatMoneyValue($input.val()));
                $input.on('input', function() {
                    const caret = this.selectionStart;
                    const beforeLen = this.value.length;
                    this.value = formatMoneyValue(this.value);
                    const afterLen = this.value.length;
                    const delta = afterLen - beforeLen;
                    const nextPos = Math.max(0, (caret || 0) + delta);
                    this.setSelectionRange(nextPos, nextPos);
                });
            });
        }

        $(function() {
            applyMoneyMask();
        });

        function showToastr(type, message) {
            const msg = message || '';
            const toastType = (type || 'info').toLowerCase();

            if (typeof toastr !== 'undefined' && typeof toastr[toastType] === 'function') {
                toastr[toastType](msg);
                return;
            }
            if (typeof Toast === 'function') {
                Toast({
                    type: toastType,
                    text: msg
                });
                return;
            }
            alert(msg);
        }



        function extractErrorMessage(xhr, fallback) {
            if (!xhr) {
                return fallback;
            }
            if (xhr.responseJSON && xhr.responseJSON.data) {
                return xhr.responseJSON.data;
            }
            if (xhr.responseText) {
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed && parsed.data) {
                        return parsed.data;
                    }
                } catch (err) {
                    return xhr.responseText;
                }
            }
            return fallback;
        }

        function humanizeDate(targetDateString) {
            const targetDate = new Date(targetDateString);
            const currentDate = new Date();

            // Hitung selisih waktu dalam milidetik
            const diffTime = targetDate - currentDate;

            // Definisikan ukuran waktu dalam milidetik
            const msPerSecond = 1000;
            const msPerMinute = msPerSecond * 60;
            const msPerHour = msPerMinute * 60;
            const msPerDay = msPerHour * 24;
            const msPerWeek = msPerDay * 7;
            const msPerMonth = msPerDay * 30.4375; // Rata-rata hari dalam sebulan (365/12)
            const msPerYear = msPerDay * 365;

            const absDiffTime = Math.abs(diffTime);
            let value, unit;

            // Tentukan satuan berdasarkan besaran selisih waktu
            if (absDiffTime >= msPerYear) {
                value = Math.round(diffTime / msPerYear);
                unit = 'year';
            } else if (absDiffTime >= msPerMonth) {
                value = Math.round(diffTime / msPerMonth);
                unit = 'month';
            } else if (absDiffTime >= msPerWeek) {
                value = Math.round(diffTime / msPerWeek);
                unit = 'week';
            } else if (absDiffTime >= msPerDay) {
                value = Math.round(diffTime / msPerDay);
                unit = 'day';
            } else if (absDiffTime >= msPerHour) {
                value = Math.round(diffTime / msPerHour);
                unit = 'hour';
            } else if (absDiffTime >= msPerMinute) {
                value = Math.round(diffTime / msPerMinute);
                unit = 'minute';
            } else {
                value = Math.round(diffTime / msPerSecond);
                unit = 'second';
            }

            // Gunakan Intl bawaan browser dengan lokal Indonesia
            const rtf = new Intl.RelativeTimeFormat('id', {
                numeric: 'auto'
            });

            return rtf.format(value, unit);
        }
    </script>
    <?= $this->renderSection('javascript'); ?>

</body>

</html>
