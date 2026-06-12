<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $dashboard
 */
$money = static fn($value): string => 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
$num = static fn($value, int $decimal = 0): string => number_format((float) ($value ?? 0), $decimal, ',', '.');
$owner = $dashboard['owner'] ?? [];
$store = $dashboard['store'] ?? [];
$sales = $owner['sales'] ?? [];
$profit = $owner['profit'] ?? [];
$cash = $owner['cash'] ?? [];
$receivable = $owner['receivable'] ?? [];
$payable = $owner['payable'] ?? [];
$product = $owner['product'] ?? [];
$alerts = $owner['alerts'] ?? [];
?>
<div class="body-wrapper">
    <div class="container-fluid">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Dashboard Pemilik</h4>
                        <p class="mb-0"><?= esc($store['toko_nama'] ?? session('toko_id')) ?> | Bulan berjalan <?= esc($dashboard['period']['label'] ?? date('F Y')) ?></p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <span class="badge bg-primary-subtle text-primary">Update <?= esc($dashboard['generated_at'] ?? '-') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0"><div class="card-body">
                    <div class="text-muted small">Omzet</div>
                    <div class="fs-5 fw-semibold mt-2"><?= $money($sales['omzet'] ?? 0) ?></div>
                    <small><?= $num($sales['transaksi'] ?? 0) ?> transaksi | Avg <?= $money($sales['rata_transaksi'] ?? 0) ?></small>
                </div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0"><div class="card-body">
                    <div class="text-muted small">Laba Kotor</div>
                    <div class="fs-5 fw-semibold mt-2"><?= $money($profit['laba_kotor'] ?? 0) ?></div>
                    <small>Margin <?= $num($profit['margin_pct'] ?? 0, 2) ?>%</small>
                </div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-start border-4 border-success"><div class="card-body">
                    <div class="text-muted small">Piutang</div>
                    <div class="fs-5 fw-semibold mt-2"><?= $money($receivable['total'] ?? 0) ?></div>
                    <small>JT hari ini <?= $money($receivable['jatuh_tempo'] ?? 0) ?> | Lewat <?= $money($receivable['lewat_tempo'] ?? 0) ?></small>
                </div></div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-start border-4 border-danger"><div class="card-body">
                    <div class="text-muted small">Hutang</div>
                    <div class="fs-5 fw-semibold mt-2"><?= $money($payable['total'] ?? 0) ?></div>
                    <small>JT hari ini <?= $money($payable['jatuh_tempo'] ?? 0) ?> | Lewat <?= $money($payable['lewat_tempo'] ?? 0) ?></small>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-7">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-semibold mb-1">Cash Control</h5>
                                <small class="text-muted">Saldo bulan berjalan dari saldo awal, POS, piutang, hutang, dan kas mutasi.</small>
                            </div>
                            <i class="ti ti-wallet fs-7 text-warning"></i>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-3"><div class="p-3 border rounded-2 h-100"><small class="text-muted">Saldo Awal</small><div class="fw-semibold mt-2"><?= $money($cash['saldo_awal'] ?? 0) ?></div></div></div>
                            <div class="col-sm-6 col-lg-3"><div class="p-3 border rounded-2 h-100"><small class="text-muted">Kas Masuk</small><div class="fw-semibold mt-2 text-success"><?= $money($cash['kas_masuk'] ?? 0) ?></div></div></div>
                            <div class="col-sm-6 col-lg-3"><div class="p-3 border rounded-2 h-100"><small class="text-muted">Kas Keluar</small><div class="fw-semibold mt-2 text-danger"><?= $money($cash['kas_keluar'] ?? 0) ?></div></div></div>
                            <div class="col-sm-6 col-lg-3"><div class="p-3 border rounded-2 h-100 bg-light"><small class="text-muted">Saldo Akhir</small><div class="fw-semibold mt-2"><?= $money($cash['saldo_akhir'] ?? 0) ?></div></div></div>
                        </div>
                        <div class="mt-3 small text-muted">Tunai <?= $money($cash['tunai_akhir'] ?? 0) ?> | Non tunai <?= $money($cash['non_tunai_akhir'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Alert Bisnis</h5>
                        <div class="d-grid gap-2">
                            <?php foreach ($alerts as $alert) : ?>
                                <?php $level = in_array($alert['level'] ?? '', ['danger', 'warning', 'success', 'info'], true) ? $alert['level'] : 'info'; ?>
                                <div class="alert alert-<?= esc($level) ?> mb-0 py-2">
                                    <div class="fw-semibold"><?= esc($alert['title'] ?? '-') ?></div>
                                    <div class="small"><?= esc($alert['message'] ?? '-') ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Produk Terlaris</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Produk</th><th class="text-end">Qty</th><th class="text-end">Omzet</th></tr></thead>
                                <tbody>
                                    <?php foreach (($product['terlaris'] ?? []) as $row) : ?>
                                        <tr><td><?= esc($row['nama_item'] ?? '-') ?></td><td class="text-end"><?= $num($row['qty'] ?? 0, 2) ?></td><td class="text-end"><?= $money($row['omzet'] ?? 0) ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($product['terlaris'])) : ?><tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Margin Tinggi</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Produk</th><th class="text-end">Margin</th><th class="text-end">%</th></tr></thead>
                                <tbody>
                                    <?php foreach (($product['margin_tinggi'] ?? []) as $row) : ?>
                                        <tr><td><?= esc($row['nama_item'] ?? '-') ?></td><td class="text-end"><?= $money($row['margin'] ?? 0) ?></td><td class="text-end"><?= $num($row['margin_pct'] ?? 0, 2) ?>%</td></tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($product['margin_tinggi'])) : ?><tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Stok Lambat</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Produk</th><th class="text-end">Cover</th><th class="text-end">Nilai</th></tr></thead>
                                <tbody>
                                    <?php foreach (($product['stok_lambat'] ?? []) as $row) : ?>
                                        <tr><td><?= esc($row['nama_item'] ?? '-') ?></td><td class="text-end"><?= ($row['cover_hari'] ?? 0) >= 999999 ? '&infin;' : $num($row['cover_hari'] ?? 0, 1) ?> hr</td><td class="text-end"><?= $money($row['nilai_stok'] ?? 0) ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($product['stok_lambat'])) : ?><tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
