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
$moneyClass = static fn($value): string => ((float) ($value ?? 0)) < 0 ? 'text-danger' : 'text-dark';
$alertIcon = static function (string $level): string {
    return match ($level) {
        'danger' => 'ti-alert-triangle',
        'warning' => 'ti-alert-circle',
        'success' => 'ti-circle-check',
        default => 'ti-info-circle',
    };
};
?>
<style>
    .owner-dashboard {
        background-color: #f8f9fa;
    }

    .owner-dashboard .icon-shape {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
    }

    .owner-dashboard .metric-value {
        font-size: 1.45rem;
        line-height: 1.2;
    }

    .owner-dashboard .product-name {
        max-width: 68%;
        min-width: 0;
    }

    .owner-dashboard .list-amount {
        max-width: 38%;
        overflow-wrap: anywhere;
    }

    @media (max-width: 575.98px) {
        .owner-dashboard .metric-value {
            font-size: 1.25rem;
        }

        .owner-dashboard .product-name {
            max-width: 62%;
        }
    }
</style>
<div class="body-wrapper">
    <div class="container-fluid owner-dashboard py-4">
        <div class="card border-0 shadow-sm bg-primary text-white mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-bold mb-1">Dashboard Pemilik</h4>
                        <p class="mb-0 opacity-75">
                            <?= esc($store['toko_nama'] ?? session('toko_id')) ?>
                            <span class="mx-2">|</span>
                            Bulan berjalan: <strong><?= esc($dashboard['period']['label'] ?? date('F Y')) ?></strong>
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <span class="badge bg-white text-primary fw-medium px-3 py-2 shadow-sm">
                            <i class="ti ti-refresh me-1"></i> Update: <?= esc($dashboard['generated_at'] ?? '-') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="text-secondary small fw-medium">Omzet</span>
                                <div class="metric-value fw-bold mt-1 mb-0"><?= $money($sales['omzet'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-primary-subtle text-primary p-2 rounded icon-shape">
                                <i class="ti ti-chart-line fs-4"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3">
                            <i class="ti ti-shopping-cart text-primary me-1"></i>
                            <span class="text-dark fw-semibold"><?= $num($sales['transaksi'] ?? 0) ?></span> transaksi
                            <span class="mx-1">&bull;</span>
                            Avg <span class="text-dark fw-semibold"><?= $money($sales['rata_transaksi'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="text-secondary small fw-medium">Laba Kotor</span>
                                <div class="metric-value fw-bold mt-1 mb-0"><?= $money($profit['laba_kotor'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-success-subtle text-success p-2 rounded icon-shape">
                                <i class="ti ti-coins fs-4"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3">
                            <i class="ti ti-percentage text-success me-1"></i>
                            Margin <span class="text-success fw-bold"><?= $num($profit['margin_pct'] ?? 0, 2) ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-success mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="text-secondary small fw-medium">Piutang</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-success"><?= $money($receivable['total'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-success-subtle text-success p-2 rounded icon-shape">
                                <i class="ti ti-receipt-refund fs-4"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3">
                            JT Hari Ini: <span class="text-dark fw-semibold"><?= $money($receivable['jatuh_tempo'] ?? 0) ?></span>
                            <span class="mx-1">|</span>
                            Lewat: <span class="text-dark fw-semibold"><?= $money($receivable['lewat_tempo'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-4 border-danger mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="text-secondary small fw-medium">Hutang</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-danger"><?= $money($payable['total'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-danger-subtle text-danger p-2 rounded icon-shape">
                                <i class="ti ti-credit-card-pay fs-4"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3">
                            JT Hari Ini: <span class="text-dark fw-semibold"><?= $money($payable['jatuh_tempo'] ?? 0) ?></span>
                            <span class="mx-1">|</span>
                            Lewat: <span class="text-dark fw-semibold"><?= $money($payable['lewat_tempo'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-7">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold mb-1"><i class="ti ti-wallet text-warning me-2"></i>Cash Control</h5>
                                <small class="text-secondary">Arus kas berjalan dari saldo awal, POS, piutang, hutang, dan mutasi.</small>
                            </div>
                        </div>
                        <div class="row g-2 text-center">
                            <div class="col-sm-6 col-lg-3">
                                <div class="p-3 border rounded-3 h-100 bg-light">
                                    <small class="text-secondary d-block mb-1"><i class="ti ti-scale me-1"></i>Saldo Awal</small>
                                    <div class="fw-bold <?= $moneyClass($cash['saldo_awal'] ?? 0) ?>"><?= $money($cash['saldo_awal'] ?? 0) ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="p-3 border rounded-3 h-100 bg-light">
                                    <small class="text-secondary d-block mb-1"><i class="ti ti-arrow-up-right text-success me-1"></i>Kas Masuk</small>
                                    <div class="fw-bold text-success"><?= $money($cash['kas_masuk'] ?? 0) ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="p-3 border rounded-3 h-100 bg-light">
                                    <small class="text-secondary d-block mb-1"><i class="ti ti-arrow-down-left text-danger me-1"></i>Kas Keluar</small>
                                    <div class="fw-bold text-danger"><?= $money($cash['kas_keluar'] ?? 0) ?></div>
                                </div>
                            </div>
                            <?php $saldoAkhirClass = ((float) ($cash['saldo_akhir'] ?? 0)) < 0 ? 'text-danger bg-danger-subtle border-danger' : 'text-success bg-success-subtle border-success'; ?>
                            <div class="col-sm-6 col-lg-3">
                                <div class="p-3 border rounded-3 h-100 <?= esc($saldoAkhirClass) ?>">
                                    <small class="d-block mb-1 fw-medium"><i class="ti ti-report-money me-1"></i>Saldo Akhir</small>
                                    <div class="fw-bold"><?= $money($cash['saldo_akhir'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 p-2 bg-light rounded-2 text-center small text-secondary">
                            Tunai:
                            <span class="fw-bold <?= $moneyClass($cash['tunai_akhir'] ?? 0) ?>"><?= $money($cash['tunai_akhir'] ?? 0) ?></span>
                            <span class="mx-2">|</span>
                            Non-Tunai:
                            <span class="fw-bold <?= $moneyClass($cash['non_tunai_akhir'] ?? 0) ?>"><?= $money($cash['non_tunai_akhir'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="ti ti-bell-ringing text-danger me-2"></i>Alert Bisnis</h5>
                        <div class="d-grid gap-2">
                            <?php foreach ($alerts as $alert) : ?>
                                <?php $level = in_array($alert['level'] ?? '', ['danger', 'warning', 'success', 'info'], true) ? $alert['level'] : 'info'; ?>
                                <?php $textClass = $level === 'warning' ? 'text-warning-emphasis' : 'text-' . $level; ?>
                                <div class="d-flex align-items-center p-2 border rounded-3 border-<?= esc($level) ?>-subtle bg-<?= esc($level) ?>-subtle <?= esc($textClass) ?>">
                                    <div class="p-2 me-3 rounded bg-white <?= esc($textClass) ?> shadow-sm">
                                        <i class="ti <?= esc($alertIcon($level)) ?> fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small"><?= esc($alert['title'] ?? '-') ?></div>
                                        <div class="small opacity-75"><?= esc($alert['message'] ?? '-') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($alerts)) : ?>
                                <div class="d-flex align-items-center p-2 border rounded-3 border-success-subtle bg-success-subtle text-success">
                                    <div class="p-2 me-3 rounded bg-white text-success shadow-sm">
                                        <i class="ti ti-circle-check fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold small">Semua Aman</div>
                                        <div class="small opacity-75">Belum ada alert bisnis untuk periode ini.</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3 text-primary"><i class="ti ti-thumb-up me-2"></i>Produk Terlaris</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach (($product['terlaris'] ?? []) as $row) : ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2">
                                    <div class="product-name text-truncate">
                                        <h6 class="mb-0 text-truncate fw-semibold small"><?= esc($row['nama_item'] ?? '-') ?></h6>
                                        <small class="text-secondary">Qty: <span class="text-dark fw-medium"><?= $num($row['qty'] ?? 0, 2) ?></span></small>
                                    </div>
                                    <span class="list-amount fw-bold text-end text-dark small"><?= $money($row['omzet'] ?? 0) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($product['terlaris'])) : ?>
                                <div class="list-group-item px-0 py-3 text-center text-muted small">Belum ada data</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3 text-success"><i class="ti ti-trending-up me-2"></i>Margin Tinggi</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach (($product['margin_tinggi'] ?? []) as $row) : ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2">
                                    <div class="product-name text-truncate">
                                        <h6 class="mb-0 text-truncate fw-semibold small"><?= esc($row['nama_item'] ?? '-') ?></h6>
                                        <small class="text-secondary">Nominal: <span class="text-dark fw-medium"><?= $money($row['margin'] ?? 0) ?></span></small>
                                    </div>
                                    <span class="badge bg-success-subtle text-success fw-bold px-2 py-1"><?= $num($row['margin_pct'] ?? 0, 2) ?>%</span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($product['margin_tinggi'])) : ?>
                                <div class="list-group-item px-0 py-3 text-center text-muted small">Belum ada data</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3 text-secondary"><i class="ti ti-hourglass-empty me-2"></i>Stok Lambat</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach (($product['stok_lambat'] ?? []) as $row) : ?>
                                <?php $cover = (float) ($row['cover_hari'] ?? 0); ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2">
                                    <div class="product-name text-truncate">
                                        <h6 class="mb-0 text-truncate fw-semibold small"><?= esc($row['nama_item'] ?? '-') ?></h6>
                                        <?php if ($cover >= 999999) : ?>
                                            <small class="text-danger-emphasis fw-medium bg-danger-subtle px-1 rounded small">Tidak Berputar</small>
                                        <?php else : ?>
                                            <small class="text-secondary">Cover: <span class="<?= $cover > 365 ? 'text-danger' : ($cover > 180 ? 'text-warning-emphasis' : 'text-dark') ?> fw-medium"><?= $num($cover, 1) ?> hr</span></small>
                                        <?php endif; ?>
                                    </div>
                                    <span class="list-amount fw-semibold text-end text-secondary small"><?= $money($row['nilai_stok'] ?? 0) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($product['stok_lambat'])) : ?>
                                <div class="list-group-item px-0 py-3 text-center text-muted small">Belum ada data</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
