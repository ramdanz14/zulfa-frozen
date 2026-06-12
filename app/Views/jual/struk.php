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
    <title><?= esc($title ?? 'Struk POS') ?></title>
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
            max-width: var(--paper-width);
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

        .receipt-header {
            display: grid;
            grid-template-columns: 14mm minmax(0, 1fr);
            gap: 3mm;
            align-items: center;
            text-align: left;
        }

        .receipt-header strong {
            display: block;
            font-size: var(--font-lg);
            line-height: 1.2;
            margin-bottom: 1px;
        }

        .receipt-logo {
            width: 14mm;
            max-width: 14mm;
            height: auto;
            display: block;
        }

        .receipt-meta td:first-child,
        .receipt-summary td:first-child,
        .receipt-payments td:first-child {
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
                max-width: var(--paper-width);
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
        <div class="receipt-header">
            <img src="<?= base_url('/assets/images/logos/zulfa-logo-bw.png') ?>" alt="Zulfa" class="receipt-logo">
            <div>
                <strong><?= esc($receipt['toko_nama'] ?? 'TOKO') ?></strong>
                <div class="muted"><?= esc($receipt['toko_alamat'] ?? '-') ?></div>
                <div class="muted">Telp: <?= esc($receipt['toko_phone'] ?? '-') ?></div>
            </div>
        </div>

        <div class="divider"></div>
        <table class="receipt-meta">
            <tr>
                <td>No Struk</td>
                <td class="text-end"><?= esc($receipt['jual_id'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="text-end"><?= esc($receipt['tgl'] ?? '-') ?></td>
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
                        <td class="muted"><?= esc($row['qty_jual']) ?> x <?= number_format((float) ($row['price'] ?? 0), 0, ',', '.') ?> / <?= esc($row['sat_id'] ?? '-') ?></td>
                        <td class="text-end"><?= number_format((float) ($row['gross'] ?? 0), 0, ',', '.') ?></td>
                    </tr>
                    <?php if ((float) ($row['diskon_item'] ?? 0) > 0) : ?>
                        <tr>
                            <td class="muted">Diskon Item</td>
                            <td class="text-end">-<?= number_format((float) ($row['diskon_item'] ?? 0), 0, ',', '.') ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="divider"></div>
        <table class="receipt-summary">
            <tr>
                <td>Gross</td>
                <td class="text-end"><?= number_format((float) ($receipt['gross'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <?php if ((float) ($receipt['diskon_nota'] ?? 0) > 0) : ?>
                <tr>
                    <td>Diskon Nota</td>
                    <td class="text-end">-<?= number_format((float) ($receipt['diskon_nota'] ?? 0), 0, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
            <?php if ((float) ($receipt['redeem_nominal'] ?? 0) > 0) : ?>
                <tr>
                    <td>Redeem Poin</td>
                    <td class="text-end">-<?= number_format((float) ($receipt['redeem_nominal'] ?? 0), 0, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
            <tr class="summary-total">
                <td><strong>Netto</strong></td>
                <td class="text-end"><strong><?= number_format((float) ($receipt['netto'] ?? 0), 0, ',', '.') ?></strong></td>
            </tr>
            <?php if ((float) ($receipt['sisa_piutang'] ?? 0) > 0) : ?>
                <tr>
                    <td>Nominal Kredit</td>
                    <td class="text-end"><?= number_format((float) ($receipt['sisa_piutang'] ?? 0), 0, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td>Cash Diterima</td>
                <td class="text-end"><?= number_format((float) ($receipt['cash_received'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Kembalian</td>
                <td class="text-end"><?= number_format((float) ($receipt['cash_change'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <?php if ((float) ($receipt['earned_points'] ?? 0) > 0) : ?>
                <tr>
                    <td>Poin Didapat</td>
                    <td class="text-end"><?= number_format((int) ($receipt['earned_points'] ?? 0), 0, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <?php if ((float) ($receipt['customer_total_piutang'] ?? 0) > 0) : ?>
            <div class="divider"></div>
            <table>
                <tr>
                    <td>Total Piutang Customer</td>
                    <td class="text-end"><?= number_format((float) ($receipt['customer_total_piutang'] ?? 0), 0, ',', '.') ?></td>
                </tr>
            </table>
        <?php endif; ?>

        <?php if (! empty($receipt['payments'])) : ?>
            <div class="divider"></div>
            <div><strong>Pembayaran</strong></div>
            <table class="receipt-payments">
                <?php foreach ($receipt['payments'] as $row) : ?>
                    <tr>
                        <td><?= esc($row['cara_bayar'] ?? '-') ?></td>
                        <td class="text-end"><?= number_format((float) ($row['nominal_bayar'] ?? 0), 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <div class="divider"></div>
        <div class="text-center muted">Terima kasih atas kunjungan Anda</div>
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
