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
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
    }

    .staff-dashboard .icon-action {
        width: 32px;
        height: 32px;
    }

    .staff-dashboard .btn-pos {
        padding: 0.6rem 1.2rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .staff-dashboard .btn-pos:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
    }

    .staff-dashboard .metric-value {
        font-size: 1.45rem;
        line-height: 1.2;
    }

    .staff-dashboard .border-dashed {
        border-style: dashed !important;
    }

    .staff-dashboard .list-main {
        max-width: 72%;
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

    @media (max-width: 575.98px) {
        .staff-dashboard .metric-value {
            font-size: 1.25rem;
        }

        .staff-dashboard .list-main {
            max-width: 62%;
        }
    }
</style>
<div class="body-wrapper">
    <div class="container-fluid staff-dashboard py-4">
        <div class="card border-0 shadow-sm bg-info text-dark mb-4" style="--bs-bg-opacity: 0.15;">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-bold mb-1 text-info-emphasis">Dashboard Karyawan</h4>
                        <p class="mb-0 text-secondary">
                            <?= esc($store['toko_nama'] ?? session('toko_id')) ?>
                            <span class="mx-2">|</span>
                            Shift Berjalan: <strong><?= esc(date('d/m/Y', strtotime($dashboard['today'] ?? date('Y-m-d')))) ?></strong>
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/jual') ?>" class="btn btn-primary btn-pos shadow-sm">
                            <i class="ti ti-cash-register me-2 fs-5"></i> Buka Aplikasi POS
                        </a>
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
                                <span class="text-secondary small fw-medium">Transaksi Hari Ini</span>
                                <div class="metric-value fw-bold mt-1 mb-0"><?= $num($shift['transaksi'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-primary-subtle text-primary p-2 rounded icon-shape">
                                <i class="ti ti-receipt fs-4"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3">
                            Omzet: <span class="text-dark fw-semibold"><?= $money($shift['omzet'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="text-secondary small fw-medium">Tunai</span>
                                <div class="metric-value fw-bold mt-1 mb-0"><?= $money($shift['tunai'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-success-subtle text-success p-2 rounded icon-shape">
                                <i class="ti ti-wallet fs-4"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3">
                            Transfer: <span class="text-dark fw-semibold"><?= $money($shift['transfer'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="text-secondary small fw-medium">QRIS</span>
                                <div class="metric-value fw-bold mt-1 mb-0"><?= $money($shift['qris'] ?? 0) ?></div>
                            </div>
                            <div class="badge p-2 rounded icon-shape qris-icon">
                                <i class="ti ti-qrcode fs-4"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3">
                            Total non-tunai hari ini
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="text-secondary small fw-medium">Piutang Baru</span>
                                <div class="metric-value fw-bold mt-1 mb-0"><?= $money($shift['piutang'] ?? 0) ?></div>
                            </div>
                            <div class="badge bg-warning-subtle text-warning p-2 rounded icon-shape">
                                <i class="ti ti-file-invoice fs-4"></i>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3">
                            Transaksi kredit hari ini
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-8">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="ti ti-users text-secondary me-2"></i>Kas Shift Per User</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 card-table">
                                <thead class="table-light">
                                    <tr class="small text-uppercase text-secondary">
                                        <th>User</th>
                                        <th class="text-end">Kas Penjualan</th>
                                        <th class="text-end">Kas Masuk</th>
                                        <th class="text-end">Pengeluaran Kas</th>
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
                                    <?php if (empty($shiftCashUsers)) : ?><tr>
                                            <td colspan="5" class="text-center py-4 text-secondary">
                                                <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                                                <span class="small">Belum ada rekaman kas tunai pada shift ini</span>
                                            </td>
                                        </tr><?php endif; ?>
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
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="ti ti-clipboard-list text-danger me-2"></i>Tugas Belum Selesai</h5>
                        <div class="row g-2">
                            <div class="col-sm-6"><a href="<?= base_url('/transfer') ?>" class="text-decoration-none d-block h-100">
                                    <div class="p-3 border rounded-3 h-100 bg-light text-center border-dashed">
                                        <span class="text-secondary small d-block mb-1">Transfer Terima</span>
                                        <div class="fw-bold fs-4 text-dark mb-1"><?= $num($tasks['transfer_pending_approve'] ?? 0) ?></div>
                                        <span class="badge bg-secondary-subtle text-secondary small">Belum Approve</span>
                                    </div>
                                </a></div>
                            <div class="col-sm-6"><a href="<?= base_url('/pembelian') ?>" class="text-decoration-none d-block h-100">
                                    <div class="p-3 border rounded-3 h-100 bg-light text-center border-dashed">
                                        <span class="text-secondary small d-block mb-1">PO Belum Terima</span>
                                        <div class="fw-bold fs-4 text-dark mb-1"><?= $num($tasks['po_belum_terima'] ?? 0) ?></div>
                                        <span class="badge <?= ((float) ($tasks['po_belum_terima'] ?? 0)) > 0 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success' ?> small">
                                            <?= ((float) ($tasks['po_belum_terima'] ?? 0)) > 0 ? 'Perlu Cek' : 'Semua Beres' ?>
                                        </span>
                                    </div>
                                </a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="ti ti-clock-bolt me-2 text-warning"></i>Piutang Ditagih</h5>
                            <a href="<?= base_url('/piutang') ?>" class="btn btn-sm btn-light text-primary rounded-circle p-2 icon-shape icon-action"><i class="ti ti-arrow-right"></i></a>
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
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="ti ti-alert-circle me-2 text-danger"></i>Stok Perlu Dicek</h5>
                            <a href="<?= base_url('/slowmoving') ?>" class="btn btn-sm btn-light text-primary rounded-circle p-2 icon-shape icon-action"><i class="ti ti-arrow-right"></i></a>
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
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="ti ti-history me-2 text-primary"></i>Transaksi Terakhir</h5>
                            <a href="<?= base_url('/listjual') ?>" class="btn btn-sm btn-light text-primary rounded-circle p-2 icon-shape icon-action"><i class="ti ti-arrow-right"></i></a>
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
