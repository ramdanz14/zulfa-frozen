<!doctype html>
<html lang="id">
<?php
/**
 * @var array $slip
 * @var bool $isMobile
 */
?>
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Slip Gaji') ?></title>
    <style>
        :root {
            --paper-width: 58mm;
            --receipt-padding: 2.2mm;
            --font-xs: 8.5px;
            --font-sm: 9.5px;
            --font-md: 10.5px;
            --font-lg: 12px;
            --line-color: #555;
        }

        @page {
            size: 58mm auto;
            margin: 0;
        }

        body {
            font-family: "Arial Narrow", Arial, Helvetica, sans-serif;
            font-size: var(--font-sm);
            line-height: 1.3;
            color: #111;
            margin: 0;
            padding: 16px;
            background: #f5f5f5;
        }

        .receipt {
            width: var(--paper-width);
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: var(--receipt-padding);
            border: 1px solid #d4d4d4;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .muted { color: #666; font-size: var(--font-xs); }
        .divider { border-top: 1px dashed var(--line-color); margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { vertical-align: top; padding: 1px 0; word-break: break-word; }

        .receipt strong {
            font-weight: 700;
        }

        .receipt-header strong {
            display: block;
            font-size: var(--font-lg);
            line-height: 1.2;
            margin-bottom: 1px;
        }

        .summary-total td {
            padding-top: 3px;
            border-top: 1px dashed var(--line-color);
            font-size: var(--font-md);
        }

        .btn-print {
            display: inline-block;
            padding: 10px 14px;
            background: #111827;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        @media print {
            body {
                width: var(--paper-width);
                background: #fff;
                padding: 0;
            }

            .btn-print { display: none; }

            .receipt {
                box-shadow: none;
                width: var(--paper-width);
                margin: 0;
                padding: 0;
                border: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body class="struk" onload="printOut()">
    <?php $slip = $slip ?? []; ?>
    <div class="text-center">
        <a href="javascript:window.print()" class="btn-print">Cetak Slip</a>
    </div>
    <div class="receipt">
        <div class="text-center receipt-header">
            <div><strong>SLIP GAJI</strong></div>
            <div class="muted">Batch <?= esc($slip['batch_id'] ?? '-') ?></div>
        </div>

        <div class="divider"></div>
        <table>
            <tr>
                <td>Karyawan</td>
                <td class="text-end"><?= esc($slip['fullname'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>ID</td>
                <td class="text-end"><?= esc($slip['karyawan_id'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Tgl Bayar</td>
                <td class="text-end"><?= esc($slip['tanggal_bayar'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Periode</td>
                <td class="text-end"><?= esc(($slip['periode_start'] ?? '-') . ' s/d ' . ($slip['periode_end'] ?? '-')) ?></td>
            </tr>
        </table>

        <div class="divider"></div>
        <?php foreach (($slip['details'] ?? []) as $row) : ?>
            <div class="item-block">
                <div><strong><?= esc($row['tanggal'] ?? '-') ?></strong></div>
                <table>
                    <tr>
                        <td class="muted"><?= esc(($row['toko_nama'] ?? $row['toko_id'] ?? '-') . ' | ' . ($row['status_absensi'] ?? '-')) ?></td>
                        <td class="text-end"><?= number_format((float) ($row['nominal_gaji'] ?? 0), 0, ',', '.') ?></td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="divider"></div>
        <table>
            <?php foreach (($slip['store_rows'] ?? []) as $store) : ?>
                <tr>
                    <td class="muted"><?= esc($store['toko_nama'] ?? $store['toko_id']) ?></td>
                    <td class="text-end muted"><?= number_format((float) ($store['nominal'] ?? 0), 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="summary-total">
                <td><strong>Total Gaji</strong></td>
                <td class="text-end"><strong><?= number_format((float) ($slip['total_nominal'] ?? 0), 0, ',', '.') ?></strong></td>
            </tr>
        </table>

        <div class="divider"></div>
        <div class="text-center muted">Mutasi kas akun GAJI sesuai toko absensi</div>
    </div>
</body>

<script>
    const isMobile = <?= $isMobile ?>;
    var lama = 15000;
    t = null;

    function printOut() {
        window.print();
        window.onafterprint = () => {
            if (isMobile) {
                t = setTimeout("self.close()", lama);
            } else {
                self.close();
            }
        };
    }
</script>
</html>
