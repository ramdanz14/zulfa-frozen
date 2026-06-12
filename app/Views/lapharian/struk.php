<!doctype html>
<html lang="id">
<?php
/**
 * @var array $report
 * @var boolean $isMobile
 */
$money = static fn($value): string => number_format((float) ($value ?? 0), 0, ',', '.');
$stores = array_map(static fn($row): string => (string) ($row['toko_nama'] ?? $row['toko_id'] ?? '-'), $report['stores'] ?? []);
?>
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Struk Laporan Harian') ?></title>
    <style>
        :root { --paper-width: 58mm; --font-xs: 8.5px; --font-sm: 9.5px; --font-md: 10.5px; --line-color: #555; }
        @page { size: 58mm auto; margin: 0; }
        body { font-family: "Arial Narrow", Arial, Helvetica, sans-serif; font-size: var(--font-sm); line-height: 1.28; color: #111; margin: 0; padding: 16px; background: #f5f5f5; }
        .receipt { width: var(--paper-width); max-width: 100%; margin: 0 auto; background: #fff; padding: 2.2mm; border: 1px solid #d4d4d4; border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
        .text-center { text-align: center; } .text-end { text-align: right; } .muted { color: #666; font-size: var(--font-xs); }
        .divider { border-top: 1px dashed var(--line-color); margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; } td { vertical-align: top; padding: 1px 0; word-break: break-word; }
        .title { font-size: 12px; font-weight: 700; } .section { font-weight: 700; margin-bottom: 2px; }
        .btn-print { display: inline-block; padding: 10px 14px; background: #111827; color: #fff; text-decoration: none; border-radius: 8px; margin-bottom: 12px; }
        @media print { body { width: var(--paper-width); background: #fff; padding: 0; } .btn-print { display: none; } .receipt { box-shadow: none; width: var(--paper-width); max-width: var(--paper-width); margin: 0; padding: 0; border: 0; border-radius: 0; } }
    </style>
</head>
<body onload="printOut()">
    <div class="text-center"><a href="javascript:window.print()" class="btn-print">Cetak Struk</a></div>
    <div class="receipt">
        <div class="text-center">
            <div class="title">LAPORAN HARIAN KASIR</div>
            <div class="muted"><?= esc(implode(', ', $stores) ?: '-') ?></div>
            <div class="muted">Tanggal: <?= esc(date('d/m/Y', strtotime($report['tanggal'] ?? date('Y-m-d')))) ?></div>
            <div class="muted">Dicetak: <?= esc($report['printed_at'] ?? '-') ?></div>
        </div>

        <div class="divider"></div>
        <div class="section">SUMMARY PER TOKO</div>
        <table>
            <?php foreach (($report['store_summaries'] ?? []) as $row) : ?>
                <tr>
                    <td><?= esc($row['toko_nama'] ?? $row['toko_id'] ?? '-') ?></td>
                    <td class="text-end"><strong><?= $money($row['uang_harus_disetor'] ?? 0) ?></strong></td>
                </tr>
                <tr class="muted">
                    <td colspan="2">
                        POS T: <?= $money($row['pos_tunai'] ?? 0) ?> |
                        Non T: <?= $money(($row['pos_transfer'] ?? 0) + ($row['pos_qris'] ?? 0)) ?> |
                        Kas: <?= $money($row['kas_bersih'] ?? 0) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="divider"></div>
        <div class="section">PER KASIR</div>
        <table>
            <?php foreach (($report['cashier_groups'] ?? []) as $row) : ?>
                <tr>
                    <td><?= esc(($row['toko_id'] ?? '-') . ' - ' . ($row['nama_kasir'] ?? $row['kasir'] ?? '-')) ?></td>
                    <td class="text-end"><strong><?= $money($row['uang_harus_disetor'] ?? 0) ?></strong></td>
                </tr>
                <tr class="muted">
                    <td colspan="2">
                        Trx: <?= number_format((int) ($row['total_transaksi'] ?? 0), 0, ',', '.') ?> |
                        POS T: <?= $money($row['pos_tunai'] ?? 0) ?> |
                        Non T: <?= $money(($row['pos_transfer'] ?? 0) + ($row['pos_qris'] ?? 0)) ?>
                    </td>
                </tr>
                <tr class="muted">
                    <td colspan="2">
                        Kas: <?= $money($row['kas_bersih'] ?? 0) ?> |
                        Sup T: <?= $money($row['supplier_tunai'] ?? 0) ?> |
                        Piut T: <?= $money($row['customer_tunai'] ?? 0) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="divider"></div>
        <div class="section">PENDAPATAN POS</div>
        <table>
            <tr><td>Tunai</td><td class="text-end"><?= $money($report['pos']['tunai'] ?? 0) ?></td></tr>
            <tr><td>Transfer</td><td class="text-end"><?= $money($report['pos']['transfer'] ?? 0) ?></td></tr>
            <tr><td>QRIS</td><td class="text-end"><?= $money($report['pos']['qris'] ?? 0) ?></td></tr>
            <tr><td><strong>Total POS</strong></td><td class="text-end"><strong><?= $money($report['pos']['total'] ?? 0) ?></strong></td></tr>
        </table>

        <div class="divider"></div>
        <div class="section">DISKON</div>
        <table>
            <tr><td>Diskon Item</td><td class="text-end"><?= $money($report['discount']['item'] ?? 0) ?></td></tr>
            <tr><td>Diskon Nota</td><td class="text-end"><?= $money($report['discount']['nota'] ?? 0) ?></td></tr>
            <tr><td>Redeem Poin</td><td class="text-end"><?= $money($report['discount']['redeem'] ?? 0) ?></td></tr>
        </table>

        <div class="divider"></div>
        <div class="section">KAS KECIL</div>
        <table>
            <?php foreach (($report['kas']['rows'] ?? []) as $row) : ?>
                <tr><td><?= esc(($row['jenis_akun'] ?? '-') . ' ' . ($row['nama_akun'] ?? '-')) ?></td><td class="text-end"><?= $money($row['total'] ?? 0) ?></td></tr>
            <?php endforeach; ?>
            <tr><td>Masuk - Keluar</td><td class="text-end"><?= $money($report['kas']['bersih'] ?? 0) ?></td></tr>
        </table>

        <div class="divider"></div>
        <div class="section">HUTANG SUPPLIER</div>
        <table>
            <?php foreach (($report['supplier']['rows'] ?? []) as $row) : ?>
                <tr><td><?= esc(($row['cara_bayar'] ?? '-') . ' ' . ($row['nama_supplier'] ?? '-')) ?></td><td class="text-end"><?= $money($row['total'] ?? 0) ?></td></tr>
            <?php endforeach; ?>
            <tr><td>Total Tunai</td><td class="text-end"><?= $money($report['supplier']['tunai'] ?? 0) ?></td></tr>
        </table>

        <div class="divider"></div>
        <div class="section">PIUTANG CUSTOMER</div>
        <table>
            <?php foreach (($report['customer']['rows'] ?? []) as $row) : ?>
                <tr><td><?= esc(($row['cara_bayar'] ?? '-') . ' ' . ($row['nama_customer'] ?? '-')) ?></td><td class="text-end"><?= $money($row['total'] ?? 0) ?></td></tr>
            <?php endforeach; ?>
            <tr><td>Total Tunai</td><td class="text-end"><?= $money($report['customer']['tunai'] ?? 0) ?></td></tr>
        </table>

        <div class="divider"></div>
        <table>
            <tr><td><strong>UANG HARUS DISETOR</strong></td><td class="text-end"><strong><?= $money($report['uang_harus_disetor'] ?? 0) ?></strong></td></tr>
        </table>
        <div class="divider"></div>
        <div class="text-center muted">Acuan sesuai data saat laporan dicetak</div>
    </div>
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
