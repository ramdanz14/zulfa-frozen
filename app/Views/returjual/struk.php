<!doctype html>
<html lang="id">
<?php

/**
 * @var array $document
 * @var boolean $isMobile
 */
?>

<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Struk Retur Penjualan') ?></title>
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

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .muted {
            color: #666;
            font-size: var(--font-xs);
        }

        .divider {
            border-top: 1px dashed var(--line-color);
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td {
            vertical-align: top;
            padding: 1px 0;
            word-break: break-word;
        }

        .receipt strong {
            font-weight: 700;
        }

        .receipt-header strong {
            display: block;
            font-size: var(--font-lg);
            line-height: 1.2;
            margin-bottom: 1px;
        }

        .receipt-meta td:first-child,
        .receipt-summary td:first-child {
            width: 42%;
        }

        .item-block {
            padding: 2px 0;
        }

        .item-name {
            font-size: var(--font-md);
            line-height: 1.2;
            margin-bottom: 1px;
        }

        .item-sub td {
            font-size: var(--font-xs);
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

            .btn-print {
                display: none;
            }

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
    <?php $receipt = $receipt ?? []; ?>
    <div class="text-center">
        <a href="javascript:window.print()" class="btn-print">Cetak Struk</a>
    </div>
    <div class="receipt">
        <div class="text-center receipt-header">
            <div><strong><?= esc($receipt['toko_nama'] ?? 'TOKO') ?></strong></div>
            <div class="muted"><?= esc($receipt['toko_alamat'] ?? '-') ?></div>
            <div class="muted"><?= esc($receipt['toko_phone'] ?? '-') ?></div>
            <div><strong>RETUR PENJUALAN</strong></div>
        </div>

        <div class="divider"></div>
        <table class="receipt-meta">
            <tr>
                <td>No Retur</td>
                <td class="text-end"><?= esc($receipt['rj_id'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>No Struk</td>
                <td class="text-end"><?= esc($receipt['jual_id'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Tgl Retur</td>
                <td class="text-end"><?= esc($receipt['tanggal'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Tgl Jual</td>
                <td class="text-end"><?= esc($receipt['tanggal_jual'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td class="text-end"><?= esc($receipt['updid'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Customer</td>
                <td class="text-end"><?= esc($receipt['customer_nama'] ?? '-') ?></td>
            </tr>
        </table>
        <div class="divider"></div>

        <?php foreach (($receipt['details'] ?? []) as $row) : ?>
            <div class="item-block">
                <div class="item-name"><strong><?= esc($row['nama_item'] ?? $row['kode_item']) ?></strong></div>
                <table class="item-sub">
                    <tr>
                        <td class="muted"><?= esc($row['qty_retur']) ?> x <?= number_format((float) ($row['price'] ?? 0), 0, ',', '.') ?> / <?= esc($row['sat_id'] ?? '-') ?></td>
                        <td class="text-end"><?= number_format((float) ($row['gross_retur'] ?? 0), 0, ',', '.') ?></td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="divider"></div>
        <table class="receipt-summary">
            <tr class="summary-total">
                <td><strong>Total Refund</strong></td>
                <td class="text-end"><strong><?= number_format((float) ($receipt['gross_retur'] ?? 0), 0, ',', '.') ?></strong></td>
            </tr>
        </table>

        <div class="divider"></div>
        <div class="text-center muted">Kas keluar akun RETUR PENJUALAN</div>
    </div>
</body>

<script>
    const isMobile = <?= $isMobile ?>;
    var lama = 15000;
    t = null;

    function printOut() {
        window.print();
        window.onafterprint = (event) => {
            if (isMobile) {
                t = setTimeout("self.close()", lama);
            } else {
                self.close();
            }
        };
    }
</script>


</html>