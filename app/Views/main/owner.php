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
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
    }

    .owner-dashboard .metric-value {
        font-size: 1.35rem;
        line-height: 1.2;
    }

    .owner-dashboard .metric-title {
        font-size: 0.72rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .owner-dashboard .product-name {
        max-width: 65%;
        min-width: 0;
    }

    .owner-dashboard .list-amount {
        max-width: 40%;
        overflow-wrap: anywhere;
    }

    @media (max-width: 767.98px) {
        .owner-dashboard {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            padding-top: 0.75rem !important;
        }

        .owner-dashboard .metric-value {
            font-size: 1.05rem;
        }

        .owner-dashboard .icon-shape {
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
        }

        .owner-dashboard .icon-shape i {
            font-size: 1.1rem !important;
        }

        .owner-dashboard .metric-title {
            font-size: 0.68rem;
        }

        .owner-dashboard .product-name {
            max-width: 60%;
        }
    }
</style>
<div class="body-wrapper">
    <div class="container-fluid owner-dashboard py-3 py-md-4">
        <!-- Banner Header Responsif -->
        <div class="card border-0 shadow-sm bg-primary text-white mb-3 mb-md-4 rounded-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-8">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white text-primary p-2 rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                <i class="ti ti-crown fs-5"></i>
                            </span>
                            <div>
                                <h4 class="fw-bold mb-0 text-white fs-5 fs-md-4">Dashboard Pemilik</h4>
                                <p class="mb-0 text-white-50 small">
                                    <?= esc($store['toko_nama'] ?? session('toko_id')) ?>
                                    <span class="mx-1">&bull;</span>
                                    <span><?= esc($dashboard['period']['label'] ?? date('F Y')) ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <span class="badge bg-white-subtle text-white border border-white border-opacity-25 fw-medium px-2 py-1 small">
                            <i class="ti ti-refresh me-1"></i> Update: <?= esc($dashboard['generated_at'] ?? '-') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 KPI Utama (Reflow: 2x2 di Mobile, 4 Col di Desktop) -->
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <!-- Omzet -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-3 border-primary mb-0 rounded-3">
                    <div class="card-body p-2.5 p-md-3">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="min-w-0">
                                <span class="text-secondary metric-title d-block text-truncate">Omzet</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-dark text-truncate"><?= $money($sales['omzet'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-primary-subtle text-primary p-1.5 rounded icon-shape d-none d-sm-flex">
                                <i class="ti ti-chart-line fs-5"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-2 pt-1 border-top border-light-subtle text-truncate" style="font-size:0.75rem;">
                            <span class="text-dark fw-semibold"><?= $num($sales['transaksi'] ?? 0) ?></span> trs
                            <span class="mx-1">&bull;</span>
                            Avg <span class="text-dark fw-semibold"><?= $money($sales['rata_transaksi'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laba Kotor -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-3 border-success mb-0 rounded-3">
                    <div class="card-body p-2.5 p-md-3">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="min-w-0">
                                <span class="text-secondary metric-title d-block text-truncate">Laba Kotor</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-success text-truncate"><?= $money($profit['laba_kotor'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-success-subtle text-success p-1.5 rounded icon-shape d-none d-sm-flex">
                                <i class="ti ti-coins fs-5"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-2 pt-1 border-top border-light-subtle text-truncate" style="font-size:0.75rem;">
                            Margin <span class="text-success fw-bold"><?= $num($profit['margin_pct'] ?? 0, 2) ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Piutang -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-3 border-warning mb-0 rounded-3">
                    <div class="card-body p-2.5 p-md-3">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="min-w-0">
                                <span class="text-secondary metric-title d-block text-truncate">Piutang</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-warning-emphasis text-truncate"><?= $money($receivable['total'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-warning-subtle text-warning-emphasis p-1.5 rounded icon-shape d-none d-sm-flex">
                                <i class="ti ti-receipt-refund fs-5"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-2 pt-1 border-top border-light-subtle text-truncate" style="font-size:0.75rem;">
                            JT: <span class="text-dark fw-semibold"><?= $money($receivable['jatuh_tempo'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hutang -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-3 border-danger mb-0 rounded-3">
                    <div class="card-body p-2.5 p-md-3">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="min-w-0">
                                <span class="text-secondary metric-title d-block text-truncate">Hutang</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-danger text-truncate"><?= $money($payable['total'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-danger-subtle text-danger p-1.5 rounded icon-shape d-none d-sm-flex">
                                <i class="ti ti-credit-card-pay fs-5"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-2 pt-1 border-top border-light-subtle text-truncate" style="font-size:0.75rem;">
                            JT: <span class="text-dark fw-semibold"><?= $money($payable['jatuh_tempo'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Control & Alert Bisnis -->
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <!-- Cash Control (Reflow 2x2 di Mobile) -->
            <div class="col-12 col-xl-7">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3 p-md-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 fs-6 fs-md-5"><i class="ti ti-wallet text-warning me-2"></i>Cash Control</h5>
                                <small class="text-secondary d-none d-sm-inline">Arus kas berjalan dari saldo awal, POS, piutang, hutang, dan mutasi.</small>
                            </div>
                        </div>
                        <div class="row g-2 text-center">
                            <div class="col-6 col-sm-6 col-lg-3">
                                <div class="p-2.5 p-md-3 border rounded-3 h-100 bg-light">
                                    <small class="text-secondary d-block mb-1 text-truncate" style="font-size:0.75rem;"><i class="ti ti-scale me-1"></i>Saldo Awal</small>
                                    <div class="fw-bold text-truncate <?= $moneyClass($cash['saldo_awal'] ?? 0) ?>"><?= $money($cash['saldo_awal'] ?? 0) ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-lg-3">
                                <div class="p-2.5 p-md-3 border rounded-3 h-100 bg-light">
                                    <small class="text-secondary d-block mb-1 text-truncate" style="font-size:0.75rem;"><i class="ti ti-arrow-up-right text-success me-1"></i>Kas Masuk</small>
                                    <div class="fw-bold text-success text-truncate"><?= $money($cash['kas_masuk'] ?? 0) ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-lg-3">
                                <div class="p-2.5 p-md-3 border rounded-3 h-100 bg-light">
                                    <small class="text-secondary d-block mb-1 text-truncate" style="font-size:0.75rem;"><i class="ti ti-arrow-down-left text-danger me-1"></i>Kas Keluar</small>
                                    <div class="fw-bold text-danger text-truncate"><?= $money($cash['kas_keluar'] ?? 0) ?></div>
                                </div>
                            </div>
                            <?php $saldoAkhirClass = ((float) ($cash['saldo_akhir'] ?? 0)) < 0 ? 'text-danger bg-danger-subtle border-danger' : 'text-success bg-success-subtle border-success'; ?>
                            <div class="col-6 col-sm-6 col-lg-3">
                                <div class="p-2.5 p-md-3 border rounded-3 h-100 <?= esc($saldoAkhirClass) ?>">
                                    <small class="d-block mb-1 fw-medium text-truncate" style="font-size:0.75rem;"><i class="ti ti-report-money me-1"></i>Saldo Akhir</small>
                                    <div class="fw-bold text-truncate"><?= $money($cash['saldo_akhir'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2.5 p-2 bg-light rounded-2 text-center small text-secondary">
                            Tunai: <span class="fw-bold <?= $moneyClass($cash['tunai_akhir'] ?? 0) ?>"><?= $money($cash['tunai_akhir'] ?? 0) ?></span>
                            <span class="mx-2 text-muted">|</span>
                            Non-Tunai: <span class="fw-bold <?= $moneyClass($cash['non_tunai_akhir'] ?? 0) ?>"><?= $money($cash['non_tunai_akhir'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Bisnis -->
            <div class="col-12 col-xl-5">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3 p-md-3">
                        <h5 class="fw-bold mb-2.5 fs-6 fs-md-5"><i class="ti ti-bell-ringing text-danger me-2"></i>Alert Bisnis</h5>
                        <div class="d-grid gap-2">
                            <?php foreach ($alerts as $alert) : ?>
                                <?php $level = in_array($alert['level'] ?? '', ['danger', 'warning', 'success', 'info'], true) ? $alert['level'] : 'info'; ?>
                                <?php $textClass = $level === 'warning' ? 'text-warning-emphasis' : 'text-' . $level; ?>
                                <div class="d-flex align-items-center p-2 border rounded-3 border-<?= esc($level) ?>-subtle bg-<?= esc($level) ?>-subtle <?= esc($textClass) ?>">
                                    <div class="p-1.5 me-2.5 rounded bg-white <?= esc($textClass) ?> shadow-sm d-flex align-items-center justify-content-center" style="width:32px; height:32px; flex: 0 0 32px;">
                                        <i class="ti <?= esc($alertIcon($level)) ?> fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-bold small text-truncate"><?= esc($alert['title'] ?? '-') ?></div>
                                        <div class="small opacity-75 text-break" style="font-size:0.75rem;"><?= esc($alert['message'] ?? '-') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($alerts)) : ?>
                                <div class="d-flex align-items-center p-2 border rounded-3 border-success-subtle bg-success-subtle text-success">
                                    <div class="p-1.5 me-2.5 rounded bg-white text-success shadow-sm d-flex align-items-center justify-content-center" style="width:32px; height:32px; flex: 0 0 32px;">
                                        <i class="ti ti-circle-check fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold small">Semua Aman</div>
                                        <div class="small opacity-75" style="font-size:0.75rem;">Belum ada alert bisnis untuk periode ini.</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3 Kartu Analisa Produk (Reflow: 1 Col di Mobile, 3 Col di Desktop) -->
        <div class="row g-2 g-md-3">
            <!-- Produk Terlaris -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3 p-md-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <h6 class="fw-bold mb-0 text-primary fs-6"><i class="ti ti-thumb-up me-2"></i>Produk Terlaris</h6>
                            <span class="badge bg-primary-subtle text-primary small">Volume</span>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach (($product['terlaris'] ?? []) as $row) : ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2">
                                    <div class="product-name text-truncate">
                                        <h6 class="mb-0 text-truncate fw-semibold small text-dark"><?= esc($row['nama_item'] ?? '-') ?></h6>
                                        <small class="text-secondary" style="font-size:0.75rem;">Qty: <span class="text-dark fw-medium"><?= $num($row['qty'] ?? 0, 2) ?></span></small>
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

            <!-- Margin Tinggi -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3 p-md-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <h6 class="fw-bold mb-0 text-success fs-6"><i class="ti ti-trending-up me-2"></i>Margin Tinggi</h6>
                            <span class="badge bg-success-subtle text-success small">Profit %</span>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach (($product['margin_tinggi'] ?? []) as $row) : ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2">
                                    <div class="product-name text-truncate">
                                        <h6 class="mb-0 text-truncate fw-semibold small text-dark"><?= esc($row['nama_item'] ?? '-') ?></h6>
                                        <small class="text-secondary" style="font-size:0.75rem;">Nominal: <span class="text-dark fw-medium"><?= $money($row['margin'] ?? 0) ?></span></small>
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

            <!-- Stok Lambat -->
            <div class="col-12 col-md-12 col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3 p-md-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <h6 class="fw-bold mb-0 text-secondary fs-6"><i class="ti ti-hourglass-empty me-2 text-warning"></i>Stok Lambat</h6>
                            <span class="badge bg-secondary-subtle text-secondary small">Slow Moving</span>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach (($product['stok_lambat'] ?? []) as $row) : ?>
                                <?php $cover = (float) ($row['cover_hari'] ?? 0); ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2">
                                    <div class="product-name text-truncate">
                                        <h6 class="mb-0 text-truncate fw-semibold small text-dark"><?= esc($row['nama_item'] ?? '-') ?></h6>
                                        <?php if ($cover >= 999999) : ?>
                                            <small class="text-danger-emphasis fw-medium bg-danger-subtle px-1 rounded small">Tidak Berputar</small>
                                        <?php else : ?>
                                            <small class="text-secondary" style="font-size:0.75rem;">Cover: <span class="<?= $cover > 365 ? 'text-danger' : ($cover > 180 ? 'text-warning-emphasis' : 'text-dark') ?> fw-medium"><?= $num($cover, 1) ?> hr</span></small>
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
