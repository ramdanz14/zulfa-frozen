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
?>
<div class="body-wrapper">
    <div class="container-fluid">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Dashboard Karyawan</h4>
                        <p class="mb-0"><?= esc($store['toko_nama'] ?? session('toko_id')) ?> | Shift <?= esc(date('d/m/Y', strtotime($dashboard['today'] ?? date('Y-m-d')))) ?></p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/jual') ?>" class="btn btn-primary"><i class="ti ti-cash-register"></i> Buka POS</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Transaksi Hari Ini</div>
                        <div class="fs-5 fw-semibold mt-2"><?= $num($shift['transaksi'] ?? 0) ?></div>
                        <small>Omzet <?= $money($shift['omzet'] ?? 0) ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Tunai</div>
                        <div class="fs-5 fw-semibold mt-2"><?= $money($shift['tunai'] ?? 0) ?></div>
                        <small>Transfer <?= $money($shift['transfer'] ?? 0) ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">QRIS</div>
                        <div class="fs-5 fw-semibold mt-2"><?= $money($shift['qris'] ?? 0) ?></div>
                        <small>Non tunai hari ini</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Piutang Baru</div>
                        <div class="fs-5 fw-semibold mt-2"><?= $money($shift['piutang'] ?? 0) ?></div>
                        <small>Transaksi kredit hari ini</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Kas Shift Per User</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
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
                                            <td class="text-end fw-semibold"><?= $money($row['saldo_sistem'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($shiftCashUsers)) : ?><tr>
                                            <td colspan="5" class="text-center text-muted">Belum ada kas tunai hari ini</td>
                                        </tr><?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-semibold">
                                        <td>Total</td>
                                        <td class="text-end"><?= $money($shift['tunai'] ?? 0) ?></td>
                                        <td class="text-end"><?= $money(max(0, ($cash['kas_masuk_tunai'] ?? 0) - ($shift['tunai'] ?? 0))) ?></td>
                                        <td class="text-end"><?= $money($cash['kas_keluar_tunai'] ?? 0) ?></td>
                                        <td class="text-end"><?= $money($cash['saldo_sistem'] ?? 0) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Tugas Belum Selesai</h5>
                        <div class="row g-3">
                            <div class="col-sm-6"><a href="<?= base_url('/transfer') ?>" class="text-decoration-none">
                                    <div class="p-3 border rounded-2 h-100"><small class="text-muted">Transfer Terima</small>
                                        <div class="fw-semibold fs-5 text-dark"><?= $num($tasks['transfer_pending_approve'] ?? 0) ?></div>
                                        <small class="text-muted">Belum approve</small>
                                    </div>
                                </a></div>
                            <div class="col-sm-6"><a href="<?= base_url('/pembelian') ?>" class="text-decoration-none">
                                    <div class="p-3 border rounded-2 h-100"><small class="text-muted">PO Belum Terima</small>
                                        <div class="fw-semibold fs-5 text-dark"><?= $num($tasks['po_belum_terima'] ?? 0) ?></div>
                                    </div>
                                </a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-semibold mb-0">Piutang Ditagih</h5>
                            <a href="<?= base_url('/piutang') ?>" class="btn btn-sm btn-light"><i class="ti ti-arrow-right"></i></a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>JT</th>
                                        <th class="text-end">Sisa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($staff['receivable_due_rows'] ?? []) as $row) : ?>
                                        <tr>
                                            <td><?= esc($row['nama_customer'] ?? '-') ?></td>
                                            <td><?= esc(date('d/m', strtotime($row['jatuh_tempo'] ?? date('Y-m-d')))) ?></td>
                                            <td class="text-end"><?= $money($row['sisa_piutang'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($staff['receivable_due_rows'])) : ?><tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada tagihan jatuh tempo</td>
                                        </tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-semibold mb-0">Stok Perlu Dicek</h5>
                            <a href="<?= base_url('/slowmoving') ?>" class="btn btn-sm btn-light"><i class="ti ti-arrow-right"></i></a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">SPD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($staff['stock_check_rows'] ?? []) as $row) : ?>
                                        <tr>
                                            <td><?= esc($row['nama_item'] ?? '-') ?></td>
                                            <td class="text-end"><?= $num($row['qty'] ?? 0, 2) ?></td>
                                            <td class="text-end"><?= $num($row['spd'] ?? 0, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($staff['stock_check_rows'])) : ?><tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada stok kritis</td>
                                        </tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-semibold mb-0">Transaksi Terakhir</h5>
                            <a href="<?= base_url('/listjual') ?>" class="btn btn-sm btn-light"><i class="ti ti-arrow-right"></i></a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nota</th>
                                        <th>Status</th>
                                        <th class="text-end">Netto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($staff['last_transactions'] ?? []) as $row) : ?>
                                        <tr>
                                            <td><?= esc($row['jual_id'] ?? '-') ?><br><small class="text-muted"><?= esc(date('H:i', strtotime($row['tgl'] ?? date('Y-m-d H:i:s')))) ?></small></td>
                                            <td><span class="badge bg-<?= ($row['status_bayar'] ?? '') === 'LUNAS' ? 'success' : 'warning' ?>-subtle text-<?= ($row['status_bayar'] ?? '') === 'LUNAS' ? 'success' : 'warning' ?>"><?= esc($row['status_bayar'] ?? '-') ?></span></td>
                                            <td class="text-end"><?= $money($row['netto'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($staff['last_transactions'])) : ?><tr>
                                            <td colspan="3" class="text-center text-muted">Belum ada transaksi</td>
                                        </tr><?php endif; ?>
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
