<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $dashboard
 */
$money = static fn($value): string => 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
$num = static fn($value, int $decimal = 0): string => number_format((float) ($value ?? 0), $decimal, ',', '.');
$staff = $dashboard['staff'] ?? [];
$store = $dashboard['store'] ?? [];
$shift = $staff['shift'] ?? [];
$cash = $staff['cash'] ?? [];
$tasks = $staff['tasks'] ?? [];
$shiftCashUsers = $staff['shift_cash_users'] ?? [];
$moneyClass = static fn($value): string => ((float) ($value ?? 0)) < 0 ? 'text-danger' : 'text-dark';
?>
<style>
    .staff-dashboard {
        background-color: #f8f9fa;
    }

    .staff-dashboard .icon-shape {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
    }

    .staff-dashboard .icon-action {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .staff-dashboard .btn-pos {
        padding: 0.65rem 1.25rem;
        font-weight: 600;
        min-height: 44px;
        transition: all 0.2s ease;
    }

    .staff-dashboard .metric-value {
        font-size: 1.35rem;
        line-height: 1.2;
    }

    .staff-dashboard .metric-title {
        font-size: 0.72rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .staff-dashboard .border-dashed {
        border-style: dashed !important;
    }

    .staff-dashboard .list-main {
        max-width: 65%;
        min-width: 0;
    }

    .staff-dashboard .list-side {
        max-width: 40%;
        overflow-wrap: anywhere;
    }

    .staff-dashboard .qris-icon {
        background-color: #f3e8ff;
        color: #7c3aed;
    }

    @media (max-width: 767.98px) {
        .staff-dashboard {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            padding-top: 0.75rem !important;
        }

        .staff-dashboard .metric-value {
            font-size: 1.05rem;
        }

        .staff-dashboard .icon-shape {
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
        }

        .staff-dashboard .icon-shape i {
            font-size: 1.1rem !important;
        }

        .staff-dashboard .metric-title {
            font-size: 0.68rem;
        }

        .staff-dashboard .list-main {
            max-width: 60%;
        }

        .staff-dashboard .shift-user-table {
            display: none;
        }

        .staff-dashboard .shift-user-cards {
            display: block;
        }
    }

    @media (min-width: 768px) {
        .staff-dashboard .shift-user-cards {
            display: none;
        }
    }
</style>
<div class="body-wrapper">
    <div class="container-fluid staff-dashboard py-3 py-md-4">
        <!-- Header Banner + POS CTA -->
        <div class="card border-0 shadow-sm bg-info-subtle border-start border-4 border-info mb-3 mb-md-4 rounded-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md-8">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white p-2 rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                <i class="ti ti-user fs-5"></i>
                            </span>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4">Dashboard Karyawan</h4>
                                <p class="mb-0 text-secondary small">
                                    <?= esc($store['toko_nama'] ?? session('toko_id')) ?>
                                    <span class="mx-1">&bull;</span>
                                    <span>Shift: <strong><?= esc(date('d/m/Y', strtotime($dashboard['today'] ?? date('Y-m-d')))) ?></strong></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <a href="<?= base_url('/jual') ?>" class="btn btn-primary btn-pos btn-touch-target w-100 w-md-auto shadow-sm d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-cash-register me-2 fs-5"></i> Buka Aplikasi POS
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 KPI Shift (Reflow: 2x2 di Mobile, 4 Col di Desktop) -->
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <!-- Transaksi Shift -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-3 border-primary mb-0 rounded-3">
                    <div class="card-body p-2.5 p-md-3">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="min-w-0">
                                <span class="text-secondary metric-title d-block text-truncate">Transaksi Shift</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-dark text-truncate"><?= $num($shift['transaksi'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-primary-subtle text-primary p-1.5 rounded icon-shape d-none d-sm-flex">
                                <i class="ti ti-receipt fs-5"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-2 pt-1 border-top border-light-subtle text-truncate" style="font-size:0.75rem;">
                            Omzet: <span class="text-dark fw-semibold"><?= $money($shift['omzet'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kas Tunai Shift -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-3 border-success mb-0 rounded-3">
                    <div class="card-body p-2.5 p-md-3">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="min-w-0">
                                <span class="text-secondary metric-title d-block text-truncate">Kas Tunai</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-success text-truncate"><?= $money($shift['tunai'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-success-subtle text-success p-1.5 rounded icon-shape d-none d-sm-flex">
                                <i class="ti ti-wallet fs-5"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-2 pt-1 border-top border-light-subtle text-truncate" style="font-size:0.75rem;">
                            Transfer: <span class="text-dark fw-semibold"><?= $money($shift['transfer'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QRIS Shift -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-3 border-purple mb-0 rounded-3" style="border-left-color: #7c3aed !important;">
                    <div class="card-body p-2.5 p-md-3">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="min-w-0">
                                <span class="text-secondary metric-title d-block text-truncate">QRIS Shift</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-truncate" style="color: #7c3aed;"><?= $money($shift['qris'] ?? 0) ?></div>
                            </div>
                            <div class="badge p-1.5 rounded icon-shape qris-icon d-none d-sm-flex">
                                <i class="ti ti-qrcode fs-5"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-2 pt-1 border-top border-light-subtle text-truncate" style="font-size:0.75rem;">
                            Non-tunai digital
                        </div>
                    </div>
                </div>
            </div>

            <!-- Piutang Baru -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm border-start border-3 border-warning mb-0 rounded-3">
                    <div class="card-body p-2.5 p-md-3">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="min-w-0">
                                <span class="text-secondary metric-title d-block text-truncate">Piutang Baru</span>
                                <div class="metric-value fw-bold mt-1 mb-0 text-warning-emphasis text-truncate"><?= $money($shift['piutang'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-warning-subtle text-warning-emphasis p-1.5 rounded icon-shape d-none d-sm-flex">
                                <i class="ti ti-file-invoice fs-5"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-2 pt-1 border-top border-light-subtle text-truncate" style="font-size:0.75rem;">
                            Transaksi kredit
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kas Shift Per User & Tugas Belum Selesai -->
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <!-- Kas Shift Per User -->
            <div class="col-12 col-xl-8">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3 p-md-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <h5 class="fw-bold mb-0 fs-6 fs-md-5 text-dark"><i class="ti ti-users text-secondary me-2"></i>Kas Shift Per Kasir</h5>
                            <span class="badge bg-primary-subtle text-primary small"><?= count($shiftCashUsers) ?> Kasir</span>
                        </div>

                        <!-- Tampilan Desktop: Tabel Standard -->
                        <div class="table-responsive shift-user-table">
                            <table class="table table-hover align-middle mb-0 card-table table-sm">
                                <thead class="table-light">
                                    <tr class="small text-uppercase text-secondary">
                                        <th>Kasir</th>
                                        <th class="text-end">Kas Jual</th>
                                        <th class="text-end">Kas Masuk</th>
                                        <th class="text-end">Pengeluaran</th>
                                        <th class="text-end">Saldo Sistem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shiftCashUsers as $row) : ?>
                                        <tr>
                                            <td><?= esc($row['fullname'] ?? $row['username'] ?? '-') ?><br><small class="text-muted"><?= esc($row['username'] ?? '-') ?></small></td>
                                            <td class="text-end"><?= $money($row['pos_tunai'] ?? 0) ?></td>
                                            <td class="text-end"><?= $money($row['kas_masuk'] ?? 0) ?></td>
                                            <td class="text-end"><?= $money($row['pengeluaran_kas'] ?? 0) ?></td>
                                            <td class="text-end fw-semibold <?= $moneyClass($row['saldo_sistem'] ?? 0) ?>"><?= $money($row['saldo_sistem'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($shiftCashUsers)) : ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-secondary">
                                                <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                                                <span class="small">Belum ada rekaman kas tunai pada shift ini</span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-light border-top-2">
                                    <tr class="fw-bold text-dark">
                                        <td>Total</td>
                                        <td class="text-end"><?= $money($shift['tunai'] ?? 0) ?></td>
                                        <td class="text-end"><?= $money(max(0, ($cash['kas_masuk_tunai'] ?? 0) - ($shift['tunai'] ?? 0))) ?></td>
                                        <td class="text-end"><?= $money($cash['kas_keluar_tunai'] ?? 0) ?></td>
                                        <td class="text-end text-primary"><?= $money($cash['saldo_sistem'] ?? 0) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Tampilan Mobile: Card Grid Reflow (Anti-Horizontal-Scroll) -->
                        <div class="shift-user-cards">
                            <?php foreach ($shiftCashUsers as $row) : ?>
                                <div class="p-2.5 mb-2 border rounded-3 bg-light-subtle">
                                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-light-subtle">
                                        <span class="fw-bold text-dark small text-truncate"><?= esc($row['fullname'] ?? $row['username'] ?? '-') ?></span>
                                        <span class="badge bg-secondary-subtle text-secondary small font-monospace"><?= esc($row['username'] ?? '-') ?></span>
                                    </div>
                                    <div class="row g-1 small" style="font-size:0.75rem;">
                                        <div class="col-6">
                                            <span class="text-muted d-block">Kas Penjualan</span>
                                            <span class="fw-semibold text-dark"><?= $money($row['pos_tunai'] ?? 0) ?></span>
                                        </div>
                                        <div class="col-6 text-end">
                                            <span class="text-muted d-block">Kas Masuk</span>
                                            <span class="fw-semibold text-success"><?= $money($row['kas_masuk'] ?? 0) ?></span>
                                        </div>
                                        <div class="col-6 mt-1">
                                            <span class="text-muted d-block">Pengeluaran</span>
                                            <span class="fw-semibold text-danger"><?= $money($row['pengeluaran_kas'] ?? 0) ?></span>
                                        </div>
                                        <div class="col-6 text-end mt-1">
                                            <span class="text-muted d-block">Saldo Sistem</span>
                                            <span class="fw-bold <?= $moneyClass($row['saldo_sistem'] ?? 0) ?>"><?= $money($row['saldo_sistem'] ?? 0) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($shiftCashUsers)) : ?>
                                <div class="text-center py-3 text-secondary">
                                    <i class="ti ti-database-off fs-3 d-block mb-1 opacity-50"></i>
                                    <span class="small">Belum ada mutasi shift kasir</span>
                                </div>
                            <?php else : ?>
                                <div class="p-2 bg-primary-subtle text-primary rounded-2 d-flex justify-content-between align-items-center small fw-bold">
                                    <span>Total Saldo Sistem:</span>
                                    <span><?= $money($cash['saldo_sistem'] ?? 0) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tugas Belum Selesai -->
            <div class="col-12 col-xl-4">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3 p-md-3">
                        <h5 class="fw-bold mb-2.5 fs-6 fs-md-5 text-dark"><i class="ti ti-clipboard-list text-danger me-2"></i>Tugas Belum Selesai</h5>
                        <div class="row g-2">
                            <div class="col-6 col-xl-12">
                                <a href="<?= base_url('/transfer') ?>" class="text-decoration-none d-block h-100 btn-touch-target">
                                    <div class="p-2.5 p-md-3 border rounded-3 h-100 bg-light text-center border-dashed">
                                        <span class="text-secondary small d-block mb-1 text-truncate" style="font-size:0.75rem;">Transfer Terima</span>
                                        <div class="fw-bold fs-4 text-dark mb-1"><?= $num($tasks['transfer_pending_approve'] ?? 0) ?></div>
                                        <span class="badge bg-secondary-subtle text-secondary small">Belum Approve</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-xl-12">
                                <a href="<?= base_url('/pembelian') ?>" class="text-decoration-none d-block h-100 btn-touch-target">
                                    <div class="p-2.5 p-md-3 border rounded-3 h-100 bg-light text-center border-dashed">
                                        <span class="text-secondary small d-block mb-1 text-truncate" style="font-size:0.75rem;">PO Belum Terima</span>
                                        <div class="fw-bold fs-4 text-dark mb-1"><?= $num($tasks['po_belum_terima'] ?? 0) ?></div>
                                        <span class="badge <?= ((float) ($tasks['po_belum_terima'] ?? 0)) > 0 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success' ?> small">
                                            <?= ((float) ($tasks['po_belum_terima'] ?? 0)) > 0 ? 'Perlu Cek' : 'Semua Beres' ?>
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3 Kartu Aktivitas & Monitoring -->
        <div class="row g-2 g-md-3">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark fs-6"><i class="ti ti-clock-bolt me-2 text-warning"></i>Piutang Ditagih</h5>
                            <a href="<?= base_url('/piutang') ?>" class="btn btn-sm btn-light text-primary rounded-circle icon-shape icon-action" title="Lihat Semua Piutang"><i class="ti ti-arrow-right"></i></a>
                        </div>
                        <?php if (empty($staff['receivable_due_rows'])) : ?>
                            <div class="py-4 text-center text-secondary border rounded-3 bg-light bg-opacity-50">
                                <i class="ti ti-circle-check text-success fs-2 d-block mb-2"></i>
                                <span class="small">Tidak ada tagihan jatuh tempo</span>
                            </div>
                        <?php else : ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (($staff['receivable_due_rows'] ?? []) as $row) : ?>
                                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2">
                                        <div class="list-main text-truncate">
                                            <h6 class="mb-0 text-truncate fw-semibold small"><?= esc($row['nama_customer'] ?? '-') ?></h6>
                                            <small class="text-secondary"><i class="ti ti-calendar-due me-1"></i>JT <?= esc(date('d/m', strtotime($row['jatuh_tempo'] ?? date('Y-m-d')))) ?></small>
                                        </div>
                                        <span class="list-side fw-bold text-end text-danger small"><?= $money($row['sisa_piutang'] ?? 0) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark fs-6"><i class="ti ti-alert-circle me-2 text-danger"></i>Stok Perlu Dicek</h5>
                            <a href="<?= base_url('/slowmoving') ?>" class="btn btn-sm btn-light text-primary rounded-circle icon-shape icon-action" title="Lihat Stok Slow Moving"><i class="ti ti-arrow-right"></i></a>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach (($staff['stock_check_rows'] ?? []) as $row) : ?>
                                <?php $qty = (float) ($row['qty'] ?? 0); ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2">
                                    <div class="list-main text-truncate">
                                        <h6 class="mb-0 text-truncate fw-semibold small"><?= esc($row['nama_item'] ?? '-') ?></h6>
                                        <small class="text-secondary">Laju Jual (SPD): <span class="text-dark fw-medium"><?= $num($row['spd'] ?? 0, 2) ?></span></small>
                                    </div>
                                    <span class="badge <?= $qty <= 0 ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning-emphasis' ?> fw-bold px-2 py-1">
                                        <?= $qty <= 0 ? 'Kosong' : $num($qty, 2) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($staff['stock_check_rows'])) : ?>
                                <div class="py-4 text-center text-secondary border rounded-3 bg-light bg-opacity-50">
                                    <i class="ti ti-circle-check text-success fs-2 d-block mb-2"></i>
                                    <span class="small">Tidak ada stok kritis</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-12 col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark fs-6"><i class="ti ti-history me-2 text-primary"></i>Transaksi Terakhir</h5>
                            <a href="<?= base_url('/listjual') ?>" class="btn btn-sm btn-light text-primary rounded-circle icon-shape icon-action" title="Lihat Riwayat Transaksi"><i class="ti ti-arrow-right"></i></a>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach (($staff['last_transactions'] ?? []) as $row) : ?>
                                <?php $isPaid = ($row['status_bayar'] ?? '') === 'LUNAS'; ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center gap-2">
                                    <div class="list-main text-truncate">
                                        <h6 class="mb-0 text-truncate fw-bold small font-monospace text-dark"><?= esc($row['jual_id'] ?? '-') ?></h6>
                                        <small class="text-secondary"><i class="ti ti-clock me-1"></i><?= esc(date('H:i', strtotime($row['tgl'] ?? date('Y-m-d H:i:s')))) ?></small>
                                    </div>
                                    <div class="list-side text-end">
                                        <span class="d-block fw-bold text-dark small"><?= $money($row['netto'] ?? 0) ?></span>
                                        <span class="badge bg-<?= $isPaid ? 'success' : 'warning' ?>-subtle text-<?= $isPaid ? 'success' : 'warning-emphasis' ?> fw-<?= $isPaid ? 'bold' : 'medium' ?> px-2 py-0"><?= esc(ucfirst(strtolower($row['status_bayar'] ?? '-'))) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($staff['last_transactions'])) : ?>
                                <div class="py-4 text-center text-secondary border rounded-3 bg-light bg-opacity-50">
                                    <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                                    <span class="small">Belum ada transaksi</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
