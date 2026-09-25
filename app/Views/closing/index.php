<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $dashboard
 */
$dash = $dashboard ?? [];
$cash = $dash['cash_flow'] ?? [];
$stock = $dash['stock_summary'] ?? [];
$logs = $dash['logs'] ?? [];
?>
<style>
    /* Styling ergonomis untuk Closing Bulanan & Mobile Layout */
    .metric-card-summary {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 0.85rem 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card-summary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .metric-card-summary .metric-title {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .metric-card-summary .metric-value {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.25;
    }

    /* Log card untuk tampilan mobile */
    .closing-log-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 767.98px) {
        .metric-card-summary {
            padding: 0.65rem 0.75rem;
        }
        .metric-card-summary .metric-value {
            font-size: 0.98rem;
        }
        .metric-card-summary .metric-title {
            font-size: 0.68rem;
        }
        .btn-touch-target {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-2 p-md-3">
        <!-- Header Page -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Closing Bulanan Toko</h4>
                        <p class="mb-0 small text-muted">
                            <span id="closing-period" class="fw-semibold text-dark">Periode aktif: <?= esc($dash['period_label'] ?? '-') ?></span> &bull; 
                            Toko: <?= esc(session('toko_id')) ?> - <?= esc(session('toko_nama')) ?>
                        </p>
                    </div>
                    <div>
                        <span class="badge bg-primary fs-3 px-3 py-2">
                            <i class="ti ti-calendar me-1"></i>Closing Date: <span id="closing-date"><?= esc($dash['closing_date'] ?? '-') ?></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards: 2 Kolom di Mobile, 4 Kolom di Desktop -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-box me-1 text-primary"></i>Saldo Awal Stok</div>
                    <div class="metric-value text-dark" id="stock-awal"><?= number_format((float) ($stock['awal_qty'] ?? 0), 0, ',', '.') ?></div>
                    <small class="text-muted mt-1 text-truncate" id="stock-awal-rp">Rp <?= number_format((float) ($stock['awal_rp'] ?? 0), 0, ',', '.') ?></small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-box-check me-1 text-info"></i>Saldo Akhir Stok</div>
                    <div class="metric-value text-dark" id="stock-akhir"><?= number_format((float) ($stock['akhir_qty'] ?? 0), 0, ',', '.') ?></div>
                    <small class="text-muted mt-1 text-truncate" id="stock-akhir-rp">Rp <?= number_format((float) ($stock['akhir_rp'] ?? 0), 0, ',', '.') ?></small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-cash me-1 text-success"></i>Kas Tunai Toko</div>
                    <div class="metric-value text-success" id="saldo-tunai">Rp <?= number_format((float) ($cash['saldo_tunai'] ?? 0), 0, ',', '.') ?></div>
                    <small class="text-muted mt-1 text-truncate">Acuan cash drawer</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-wallet me-1 text-warning"></i>Total Cash Flow</div>
                    <div class="metric-value text-primary" id="saldo-all">Rp <?= number_format((float) ($cash['saldo_all'] ?? 0), 0, ',', '.') ?></div>
                    <small class="text-muted mt-1 text-truncate">Tunai + transfer + QRIS</small>
                </div>
            </div>
        </div>

        <!-- Aksi Proses Closing -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark"><i class="ti ti-lock-access text-danger me-2"></i>Proses Closing Toko Aktif</h6>
                        <small class="text-muted d-block">
                            Closing akan mem-backup <code>stmast</code> periode ini, memindahkan saldo akhir menjadi <code>begbal</code> bulan berikutnya, mencatat <code>saldo_cash</code>, lalu menggeser periode closing.
                        </small>
                    </div>
                    <div class="d-flex gap-2 w-100 w-md-auto flex-wrap">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill btn-touch-target" id="btn-refresh">
                            <i class="ti ti-refresh me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-danger btn-sm flex-fill btn-touch-target px-3" id="btn-process">
                            <i class="ti ti-lock-check me-1"></i> Proses Closing
                        </button>
                    </div>
                </div>

                <!-- Section IT Closing Ulang -->
                <div id="it-reclose" class="border-top mt-3 pt-3" style="display:none;">
                    <div class="alert alert-warning border-warning-subtle py-2 px-3 small mb-2 d-flex align-items-center gap-2">
                        <i class="ti ti-alert-triangle fs-5 text-warning flex-shrink-0"></i>
                        <span>Fitur Khusus IT: Closing ulang akan menghitung kembali posisi stok & kas mulai dari bulan yang dipilih.</span>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Pilih Bulan Awal Hitung Ulang</label>
                            <input type="month" class="form-control form-control-sm" id="reclose-period" value="<?= esc(substr((string) ($dash['closing_date'] ?? date('Y-m-01')), 0, 7)) ?>">
                        </div>
                        <div class="col-12 col-md-7 d-flex">
                            <button type="button" class="btn btn-warning btn-sm w-100 btn-touch-target" id="btn-reclose">
                                <i class="ti ti-history me-1"></i> Jalankan Closing Ulang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rincian Cash Flow & Riwayat Log -->
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100 mb-0">
                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center gap-2">
                        <i class="ti ti-report-money fs-5 text-success"></i>
                        <h6 class="mb-0 fw-bold">Report Cash Flow Periode</h6>
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <tbody id="cash-flow-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100 mb-0">
                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center gap-2">
                        <i class="ti ti-notes fs-5 text-primary"></i>
                        <h6 class="mb-0 fw-bold">Log Closing Terakhir</h6>
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <!-- Desktop View Table (>= 768px) -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-sm table-bordered table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Pesan</th>
                                    </tr>
                                </thead>
                                <tbody id="log-body">
                                    <?php foreach ($logs as $row) : ?>
                                        <tr>
                                            <td class="small"><?= esc($row['created_at'] ?? '-') ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= esc($row['mode'] ?? '-') ?></span></td>
                                            <td>
                                                <span class="badge <?= ($row['status'] ?? '') === 'SUCCESS' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                                    <?= esc($row['status'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td class="small"><?= esc($row['message'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View (< 768px) -->
                        <div class="d-md-none" id="log-body-mobile">
                            <?php if (empty($logs)) : ?>
                                <div class="text-center text-muted py-3">Belum ada log</div>
                            <?php else : ?>
                                <?php foreach ($logs as $row) : ?>
                                    <div class="closing-log-card">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted"><i class="ti ti-clock me-1"></i><?= esc($row['created_at'] ?? '-') ?></span>
                                            <span class="badge <?= ($row['status'] ?? '') === 'SUCCESS' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                                <?= esc($row['status'] ?? '-') ?>
                                            </span>
                                        </div>
                                        <div class="small fw-semibold text-dark mb-1">Mode: <span class="badge bg-light text-dark border"><?= esc($row['mode'] ?? '-') ?></span></div>
                                        <div class="small text-muted"><?= esc($row['message'] ?? '-') ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    let dashboard = <?= json_encode($dashboard ?? [], JSON_UNESCAPED_SLASHES) ?>;

    $(function() {
        if (akses_menu?.akses_delete === 'Y') {
            $('#it-reclose').show();
        }
        renderDashboard(dashboard);
    });

    $('#btn-refresh').on('click', refreshDashboard);
    $('#btn-process').on('click', function() {
        Swal.fire({
            title: 'Proses closing?',
            text: 'Saldo akhir stock akan dipindahkan menjadi saldo awal bulan berikutnya.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, proses',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                postAction('<?= base_url('/closing/process') ?>', {});
            }
        });
    });

    $('#btn-reclose').on('click', function() {
        Swal.fire({
            title: 'Closing ulang?',
            text: 'Proses akan menghitung ulang mulai periode yang dipilih sampai periode aktif.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, closing ulang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                postAction('<?= base_url('/closing/reclose') ?>', {
                    periode: $('#reclose-period').val() + '-01'
                });
            }
        });
    });

    function postAction(url, data) {
        $.ajax({
            type: 'POST',
            url,
            data,
            dataType: 'json',
            success: function(res) {
                if (res?.tipe === 'success') {
                    toastr.success(res.data || 'Proses berhasil');
                    refreshDashboard();
                } else {
                    toastr.error(res?.data || 'Proses gagal');
                }
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Proses closing gagal'));
            }
        });
    }

    function refreshDashboard() {
        $.ajax({
            type: 'GET',
            url: '<?= base_url('/closing/dashboard') ?>',
            dataType: 'json',
            success: function(res) {
                dashboard = res?.data || {};
                renderDashboard(dashboard);
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal refresh dashboard closing'));
            }
        });
    }

    function renderDashboard(data) {
        const cash = data.cash_flow || {};
        const stock = data.stock_summary || {};
        $('#closing-date').text(data.closing_date || '-');
        $('#closing-period').text(`Periode aktif: ${data.period_label || '-'}`);
        $('#stock-awal').text(formatMoneyValue(stock.awal_qty || 0));
        $('#stock-awal-rp').text(`Rp ${formatMoneyValue(stock.awal_rp || 0)}`);
        $('#stock-akhir').text(formatMoneyValue(stock.akhir_qty || 0));
        $('#stock-akhir-rp').text(`Rp ${formatMoneyValue(stock.akhir_rp || 0)}`);
        $('#saldo-tunai').text(rp(cash.saldo_tunai || 0));
        $('#saldo-all').text(rp(cash.saldo_all || 0));
        $('#cash-flow-body').html([
            row('Saldo Awal Tunai', cash.saldo_awal_tunai),
            row('POS Tunai', cash.pos_tunai),
            row('Bayar Piutang Tunai', cash.bayar_piutang_tunai),
            row('Kas Masuk', cash.kas_masuk),
            row('Bayar Hutang Tunai', -(cash.bayar_hutang_tunai || 0)),
            row('Kas Keluar', -(cash.kas_keluar || 0)),
            row('Saldo Tunai', cash.saldo_tunai, true),
            row('Saldo Transfer', cash.saldo_transfer, true),
            row('Saldo QRIS', cash.saldo_qris, true),
            row('Saldo Total', cash.saldo_all, true)
        ].join(''));

        const logs = data.logs || [];
        $('#log-body').html(logs.length ? logs.map(log => {
            const statusBadge = (log.status || '') === 'SUCCESS' ? '<span class="badge bg-success-subtle text-success">SUCCESS</span>' : '<span class="badge bg-danger-subtle text-danger">' + esc(log.status) + '</span>';
            return `<tr><td class="small">${esc(log.created_at)}</td><td><span class="badge bg-light text-dark border">${esc(log.mode)}</span></td><td>${statusBadge}</td><td class="small">${esc(log.message)}</td></tr>`;
        }).join('') : '<tr><td colspan="4" class="text-center text-muted">Belum ada log</td></tr>');

        $('#log-body-mobile').html(logs.length ? logs.map(log => {
            const statusBadge = (log.status || '') === 'SUCCESS' ? '<span class="badge bg-success-subtle text-success">SUCCESS</span>' : '<span class="badge bg-danger-subtle text-danger">' + esc(log.status) + '</span>';
            return `
                <div class="closing-log-card">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted"><i class="ti ti-clock me-1"></i>${esc(log.created_at)}</span>
                        ${statusBadge}
                    </div>
                    <div class="small fw-semibold text-dark mb-1">Mode: <span class="badge bg-light text-dark border">${esc(log.mode)}</span></div>
                    <div class="small text-muted">${esc(log.message)}</div>
                </div>
            `;
        }).join('') : '<div class="text-center text-muted py-3">Belum ada log</div>');
    }

    function row(label, amount, strong = false) {
        const left = strong ? `<strong>${label}</strong>` : label;
        const right = strong ? `<strong>${rp(amount || 0)}</strong>` : rp(amount || 0);
        return `<tr><td>${left}</td><td class="text-end">${right}</td></tr>`;
    }

    function rp(value) {
        return 'Rp ' + formatMoneyValue(value || 0);
    }

    function esc(value) {
        return $('<div>').text(value || '-').html();
    }
</script>
<?= $this->endSection('javascript') ?>
