<!doctype html>
<html lang="id">
<?php
/**
 * @var array $receipt
 * @var boolean $isMobile
 */
$receipt = $receipt ?? [];
$details = $receipt['details'] ?? [];
$payments = $receipt['payments'] ?? [];
$money = static fn($value): string => number_format((float) ($value ?? 0), 0, ',', '.');
$qty = static fn($value): string => number_format((float) ($value ?? 0), 2, ',', '.');
$chunks = array_chunk($details, 18);
if (empty($chunks)) {
    $chunks = [[]];
}
$paymentText = implode(', ', array_map(static fn(array $row): string => (string) ($row['cara_bayar'] ?? '-'), $payments));
$paymentText = $paymentText !== '' ? $paymentText : ($receipt['status_bayar'] ?? '-');
?>

<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Faktur Penjualan') ?> <?= esc($receipt['jual_id'] ?? '') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            margin: 0;
            background: #f5f5f5;
            font-size: 12px;
        }

        .toolbar {
            text-align: center;
            padding: 12px;
        }

        .btn-print {
            display: inline-block;
            padding: 10px 14px;
            background: #111827;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
        }

        .page {
            width: 190mm;
            min-height: 270mm;
            margin: 0 auto 12px;
            background: #fff;
            padding: 8mm;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        }

        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header td {
            vertical-align: top;
        }

        .logo {
            width: 82px;
            height: auto;
        }

        .store-name {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            text-align: right;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 10px;
        }

        .meta td {
            padding: 2px 0;
            vertical-align: top;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .items th,
        .items td {
            border: 1px solid #111;
            padding: 4px 5px;
            vertical-align: top;
        }

        .items th {
            text-align: center;
            font-weight: 700;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .blank td {
            height: 22px;
        }

        .summary td {
            font-weight: 700;
        }

        .signature {
            height: 78px;
            text-align: center;
        }

        .note {
            margin-top: 8px;
            font-size: 11px;
            font-style: italic;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .page {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .page:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>

<body onload="printOut()">
    <div class="toolbar"><a href="javascript:window.print()" class="btn-print">Cetak Faktur</a></div>
    <?php foreach ($chunks as $pageIndex => $rows) : ?>
        <?php
        $pageNo = $pageIndex + 1;
        $totalPage = count($chunks);
        ?>
        <div class="page">
            <table class="header">
                <tr>
                    <td style="width:95px;"><img src="<?= base_url('/assets/images/logos/zulfa-logo-bw.png') ?>" alt="Zulfa" class="logo"></td>
                    <td>
                        <div class="store-name"><?= esc($receipt['toko_nama'] ?? 'TOKO') ?></div>
                        <div><?= esc($receipt['toko_alamat'] ?? '-') ?></div>
                        <div>Telp: <?= esc($receipt['toko_phone'] ?? '-') ?></div>
                    </td>
                    <td style="width:36%;">
                        <div class="title">FAKTUR PENJUALAN</div>
                        <table style="width:100%; margin-top:4px;">
                            <tr><td>No.</td><td class="text-end"><?= esc($receipt['jual_id'] ?? '-') ?></td></tr>
                            <tr><td>Tanggal</td><td class="text-end"><?= esc(!empty($receipt['tgl']) ? date('d/m/Y H:i', strtotime((string) $receipt['tgl'])) : '-') ?></td></tr>
                            <tr><td>Hal</td><td class="text-end"><?= $pageNo ?>/<?= $totalPage ?></td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table class="meta">
                <tr>
                    <td style="width:18%;">Pelanggan</td>
                    <td style="width:52%;">: <?= esc($receipt['customer_nama'] ?? 'Pelanggan Umum') ?><?= !empty($receipt['cust_id']) ? ' [' . esc($receipt['cust_id']) . ']' : '' ?></td>
                    <td style="width:12%;">Kasir</td>
                    <td>: <?= esc($receipt['updid'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: <?= esc($receipt['customer_alamat'] ?? '-') ?></td>
                    <td>Bayar</td>
                    <td>: <?= esc($paymentText) ?></td>
                </tr>
            </table>

            <table class="items">
                <thead>
                    <tr>
                        <th style="width:6%;">No</th>
                        <th>Nama Item</th>
                        <th style="width:10%;">Jml</th>
                        <th style="width:10%;">Satuan</th>
                        <th style="width:15%;">Harga</th>
                        <th style="width:12%;">Disc</th>
                        <th style="width:16%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $idx => $row) : ?>
                        <?php $no = ($pageIndex * 18) + $idx + 1; ?>
                        <tr>
                            <td class="text-center"><?= $no ?></td>
                            <td><?= esc($row['nama_item'] ?? $row['kode_item'] ?? '-') ?></td>
                            <td class="text-end"><?= $qty($row['qty_jual'] ?? 0) ?></td>
                            <td class="text-center"><?= esc($row['sat_id'] ?? '-') ?></td>
                            <td class="text-end"><?= $money($row['price'] ?? 0) ?></td>
                            <td class="text-end"><?= $money($row['diskon_item'] ?? 0) ?></td>
                            <td class="text-end"><?= $money($row['netto'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php for ($i = count($rows); $i < 18; $i++) : ?>
                        <tr class="blank"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    <?php endfor; ?>
                    <?php if ($pageNo === $totalPage) : ?>
                        <tr class="summary">
                            <td colspan="4" rowspan="5" class="signature">
                                Hormat Kami,<br><br><br>
                                (________________)
                            </td>
                            <td colspan="2">Gross</td>
                            <td class="text-end"><?= $money($receipt['gross'] ?? 0) ?></td>
                        </tr>
                        <tr class="summary"><td colspan="2">Diskon Nota</td><td class="text-end"><?= $money($receipt['diskon_nota'] ?? 0) ?></td></tr>
                        <tr class="summary"><td colspan="2">Redeem Poin</td><td class="text-end"><?= $money($receipt['redeem_nominal'] ?? 0) ?></td></tr>
                        <tr class="summary"><td colspan="2">Total</td><td class="text-end"><?= $money($receipt['netto'] ?? 0) ?></td></tr>
                        <tr class="summary"><td colspan="2">Sisa Piutang</td><td class="text-end"><?= $money($receipt['sisa_piutang'] ?? 0) ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="note">** Simpan faktur ini sebagai bukti transaksi penjualan.</div>
        </div>
    <?php endforeach; ?>
</body>
<script>
    const isMobile = <?= $isMobile ?>;

    function printOut() {
        window.print();
        window.onafterprint = () => {
            if (isMobile) {
                setTimeout("self.close()", 15000);
            } else {
                self.close();
            }
        };
    }
</script>

</html>
