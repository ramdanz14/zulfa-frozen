<?php

/**
 * @var array $document
 * @var boolean $isMobile
 */
$doc = $document ?? [];
$details = $doc['details'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak BAP <?= esc($doc['bap_id'] ?? '') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            color: #111827;
            background: #ffffff;
        }

        .wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 24px 28px 40px;
        }

        .print-btn {
            display: inline-block;
            margin-bottom: 16px;
            padding: 10px 18px;
            background: #0f766e;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 6px 0 0;
            font-size: 14px;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 18px;
        }

        .meta-box {
            border: 1px solid #1f2937;
            padding: 12px 14px;
            min-height: 110px;
        }

        .meta-row {
            display: grid;
            grid-template-columns: 110px 12px 1fr;
            gap: 4px;
            font-size: 14px;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        th,
        td {
            border: 1px solid #1f2937;
            padding: 6px 8px;
            font-size: 13px;
        }

        th {
            background: #f3f4f6;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid #1f2937;
        }

        .signature-box {
            min-height: 160px;
            border-right: 1px solid #1f2937;
            padding: 14px;
            text-align: center;
        }

        .signature-box:last-child {
            border-right: 0;
        }

        .signature-name {
            margin-top: 92px;
        }

        @media print {
            .print-btn {
                display: none;
            }

            .wrapper {
                padding: 0;
            }
        }
    </style>
</head>

<body onload="printOut()">
    <div class="wrapper">
        <a href="javascript:window.print()" class="print-btn">Cetak Dokumen</a>

        <div class="header">
            <h1>BERITA ACARA PEMUSNAHAN</h1>
            <p>Dokumen pemusnahan barang tidak layak jual</p>
        </div>

        <div class="meta">
            <div class="meta-box">
                <div class="meta-row"><span>No. BAP</span><span>:</span><span><?= esc($doc['bap_id'] ?? '-') ?></span></div>
                <div class="meta-row"><span>Tanggal</span><span>:</span><span><?= esc(substr((string) ($doc['tanggal'] ?? ''), 0, 10)) ?></span></div>
                <div class="meta-row"><span>Admin</span><span>:</span><span><?= esc($doc['updid'] ?? '-') ?></span></div>
                <div class="meta-row"><span>Tanggal Cetak</span><span>:</span><span><?= esc(date('Y-m-d H:i')) ?></span></div>
            </div>
            <div class="meta-box">
                <div class="meta-row"><span>Nama Toko</span><span>:</span><span><?= esc($doc['toko_nama'] ?? '-') ?></span></div>
                <div class="meta-row"><span>Kode Toko</span><span>:</span><span><?= esc($doc['toko_id'] ?? '-') ?></span></div>
                <div class="meta-row"><span>Alamat</span><span>:</span><span><?= esc($doc['toko_alamat'] ?? '-') ?></span></div>
                <div class="meta-row"><span>Keterangan</span><span>:</span><span><?= esc($doc['keterangan'] ?? '-') ?></span></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">No</th>
                    <th>Barang</th>
                    <th class="text-center" style="width: 100px;">Satuan</th>
                    <th class="text-center" style="width: 90px;">Qty</th>
                    <th class="text-end" style="width: 120px;">Harga</th>
                    <th class="text-end" style="width: 140px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($details)) : ?>
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada detail item</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($details as $index => $row) : ?>
                        <tr>
                            <td class="text-center"><?= $index + 1 ?></td>
                            <td><?= esc($row['nama_item'] ?? $row['kode_item'] ?? '-') ?></td>
                            <td class="text-center"><?= esc($row['sat_id'] ?? '-') ?></td>
                            <td class="text-end"><?= number_format((float) ($row['qty_so'] ?? 0), 2, '.', ',') ?></td>
                            <td class="text-end"><?= digit_group($row['price'] ?? 0) ?></td>
                            <td class="text-end"><?= digit_group($row['gross'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr>
                    <td colspan="5" class="text-end"><strong>Total</strong></td>
                    <td class="text-end"><strong><?= digit_group($doc['total_gross'] ?? 0) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="signatures">
            <div class="signature-box">
                <div>Dibuat Oleh</div>
                <div class="signature-name">(................................)</div>
                <div><?= esc($doc['updid'] ?? 'Admin') ?></div>
            </div>
            <div class="signature-box">
                <div>Diperiksa Oleh</div>
                <div class="signature-name">(................................)</div>
                <div>&nbsp;</div>
            </div>
        </div>
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