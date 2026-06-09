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
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Closing Bulanan</h4>
                        <p class="mb-0"><span id="closing-period">Periode aktif: <?= esc($dash['period_label'] ?? '-') ?></span> | Toko aktif <?= esc(session('toko_id')) ?> - <?= esc(session('toko_nama')) ?></p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <span class="badge bg-primary fs-2 px-3 py-2">Closing Date: <span id="closing-date"><?= esc($dash['closing_date'] ?? '-') ?></span></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Saldo Awal Stock</div><div class="fs-6 fw-semibold mt-2" id="stock-awal"><?= number_format((float) ($stock['awal_qty'] ?? 0), 0, ',', '.') ?></div><small class="text-muted">Rp <?= number_format((float) ($stock['awal_rp'] ?? 0), 0, ',', '.') ?></small></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Saldo Akhir Stock</div><div class="fs-6 fw-semibold mt-2" id="stock-akhir"><?= number_format((float) ($stock['akhir_qty'] ?? 0), 0, ',', '.') ?></div><small class="text-muted">Rp <?= number_format((float) ($stock['akhir_rp'] ?? 0), 0, ',', '.') ?></small></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Saldo Tunai Closing</div><div class="fs-6 fw-semibold mt-2" id="saldo-tunai">Rp <?= number_format((float) ($cash['saldo_tunai'] ?? 0), 0, ',', '.') ?></div><small class="text-muted">Acuan cash drawer</small></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Saldo Cash Flow Total</div><div class="fs-6 fw-semibold mt-2" id="saldo-all">Rp <?= number_format((float) ($cash['saldo_all'] ?? 0), 0, ',', '.') ?></div><small class="text-muted">Tunai + transfer + QRIS</small></div></div></div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row gap-2 justify-content-between">
                    <div>
                        <div class="fw-semibold">Proses Closing Toko Aktif</div>
                        <small class="text-muted">Closing akan backup `stmast` periode ini, menjadikan saldo akhir sebagai `begbal` bulan berikutnya, mencatat `saldo_cash`, lalu menggeser periode closing.</small>
                    </div>
                    <div class="d-grid d-lg-flex gap-2">
                        <button type="button" class="btn btn-primary" id="btn-refresh"><i class="ti ti-refresh"></i> Refresh</button>
                        <button type="button" class="btn btn-danger" id="btn-process"><i class="ti ti-lock-check"></i> Proses Closing</button>
                    </div>
                </div>
                <div id="it-reclose" class="border-top mt-3 pt-3" style="display:none;">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label">Closing Ulang Dari Periode</label>
                            <input type="month" class="form-control" id="reclose-period" value="<?= esc(substr((string) ($dash['closing_date'] ?? date('Y-m-01')), 0, 7)) ?>">
                        </div>
                        <div class="col-lg-8 d-grid d-lg-flex gap-2">
                            <button type="button" class="btn btn-warning" id="btn-reclose"><i class="ti ti-history"></i> Closing Ulang</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Report Cash Flow Periode</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <tbody id="cash-flow-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Log Closing Terakhir</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead><tr><th>Waktu</th><th>Mode</th><th>Status</th><th>Pesan</th></tr></thead>
                                <tbody id="log-body">
                                    <?php foreach ($logs as $row) : ?>
                                        <tr>
                                            <td><?= esc($row['created_at'] ?? '-') ?></td>
                                            <td><?= esc($row['mode'] ?? '-') ?></td>
                                            <td><?= esc($row['status'] ?? '-') ?></td>
                                            <td><?= esc($row['message'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
        $('#stock-akhir').text(formatMoneyValue(stock.akhir_qty || 0));
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
        $('#log-body').html(logs.length ? logs.map(log => `<tr><td>${esc(log.created_at)}</td><td>${esc(log.mode)}</td><td>${esc(log.status)}</td><td>${esc(log.message)}</td></tr>`).join('') : '<tr><td colspan="4" class="text-center text-muted">Belum ada log</td></tr>');
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
