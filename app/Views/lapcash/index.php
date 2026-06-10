<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Laporan Cash Flow Per Bulan</h4>
                        <p class="mb-0"><span id="period-label">Periode aktif</span> | Monitoring mutasi keuangan tunai dan non tunai.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div id="selected-store-info" class="text-muted small"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3">
                        <label class="form-label">Periode</label>
                        <input type="month" class="form-control" id="filter-periode" value="<?= date('Y-m') ?>">
                    </div>
                    <div class="col-lg-5" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label">Filter Toko</label>
                        <select class="form-select select2" id="filter-toko" multiple>
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>"><?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-4 d-grid d-lg-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btn-filter"><i class="ti ti-search"></i> Tampilkan</button>
                        <button type="button" class="btn btn-light w-100" id="btn-reset">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Saldo Awal Tunai</div><div class="fs-6 fw-semibold mt-2" id="saldo-awal-cash">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Pemasukan Tunai</div><div class="fs-6 fw-semibold mt-2 text-success" id="pemasukan-cash">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Pengeluaran Tunai</div><div class="fs-6 fw-semibold mt-2 text-danger" id="pengeluaran-cash">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Saldo Akhir Tunai</div><div class="fs-6 fw-semibold mt-2" id="saldo-akhir-cash">Rp 0</div></div></div></div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Saldo Awal Non Tunai</div><div class="fs-6 fw-semibold mt-2" id="saldo-awal-noncash">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Pemasukan Non Tunai</div><div class="fs-6 fw-semibold mt-2 text-success" id="pemasukan-noncash">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Pengeluaran Non Tunai</div><div class="fs-6 fw-semibold mt-2 text-danger" id="pengeluaran-noncash">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Saldo Akhir Semua</div><div class="fs-6 fw-semibold mt-2" id="saldo-akhir-all">Rp 0</div></div></div></div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-3">Summary Cash Flow</div>
                <div class="row">
                    <div class="col-lg-6">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <tbody id="summary-body-left"></tbody>
                        </table>
                    </div>
                    <div class="col-lg-6 mt-3 mt-lg-0">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <tbody id="summary-body-right"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead>
                        <tr>
                            <th>TANGGAL</th>
                            <th>IN CASH</th>
                            <th>OUT CASH</th>
                            <th>SALDO CASH</th>
                            <th>IN NON TUNAI</th>
                            <th>OUT NON TUNAI</th>
                            <th>SALDO NON TUNAI</th>
                            <th>SALDO ALL</th>
                            <th>DETAIL</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail-cashflow" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Cash Flow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="fw-semibold mb-3" id="detail-title">Periode -</h5>
                <table class="table table-sm table-bordered align-middle mb-0">
                    <tbody id="detail-body"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const canMultiStore = akses_menu?.akses_delete === 'Y';
    const sessionTokoId = '<?= esc((string) session('toko_id')) ?>';
    let table = null;
    let currentRows = [];
    const detailModal = new bootstrap.Modal(document.getElementById('modal-detail-cashflow'));

    $(function() {
        if (canMultiStore) {
            $('#filter-toko-wrapper').show();
            $('#filter-toko').select2({
                width: '100%',
                placeholder: 'Pilih satu atau banyak toko'
            });
        }
        updateStoreInfo();
        initTable();
        loadReport();
    });

    $('#btn-filter').on('click', loadReport);
    $('#btn-reset').on('click', function() {
        $('#filter-periode').val(moment().format('YYYY-MM'));
        if (canMultiStore) {
            $('#filter-toko').val(null).trigger('change');
        }
        updateStoreInfo();
        loadReport();
    });
    $('#filter-toko').on('change', updateStoreInfo);

    function selectedStores() {
        if (!canMultiStore) {
            return [sessionTokoId];
        }
        return ($('#filter-toko').val() || []).filter(Boolean);
    }

    function updateStoreInfo() {
        const stores = selectedStores();
        if (!canMultiStore) {
            $('#selected-store-info').text(`Toko aktif: ${sessionTokoId}`);
            return;
        }
        $('#selected-store-info').text(stores.length ? `Toko dipilih: ${stores.join(', ')}` : 'Toko: semua toko');
    }

    function initTable() {
        DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
        table = $('#table-data').DataTable({
            layout: {
                topStart: {
                    buttons: [{
                        extend: 'excelHtml5',
                        text: 'Excel',
                        title: 'Laporan-Cash-Flow'
                    }, {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        title: 'Laporan Cash Flow'
                    }, {
                        extend: 'print',
                        text: 'Print',
                        title: 'Laporan Cash Flow'
                    }, 'pageLength']
                }
            },
            data: [],
            ordering: true,
            order: [[0, 'asc']],
            responsive: true,
            pageLength: 32,
            lengthMenu: [[32, 50, 100, -1], ['32 rows', '50 rows', '100 rows', 'Show all']],
            columns: [
                { data: 'tanggal' },
                { data: 'in_cash', className: 'text-end', render: moneyCell },
                { data: 'out_cash', className: 'text-end', render: moneyCell },
                { data: 'saldo_cash', className: 'text-end', render: moneyCell },
                { data: 'in_noncash', className: 'text-end', render: moneyCell },
                { data: 'out_noncash', className: 'text-end', render: moneyCell },
                { data: 'saldo_noncash', className: 'text-end', render: moneyCell },
                { data: 'saldo_all', className: 'text-end text-primary', render: moneyCell },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(row) {
                        if (row.is_opening) {
                            return '<span class="text-muted">-</span>';
                        }
                        return `<button type="button" class="btn btn-sm btn-outline-primary" onclick="showDailyDetail('${escapeAttr(row.tanggal || '')}')"><i class="ti ti-list-details"></i></button>`;
                    }
                }
            ],
            createdRow: function(row, data) {
                if (data.is_opening) {
                    $(row).addClass('table-light fw-semibold');
                }
            }
        });
    }

    function loadReport() {
        updateStoreInfo();
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lapcash/report') ?>',
            dataType: 'json',
            data: {
                periode: $('#filter-periode').val() + '-01',
                toko_ids: selectedStores()
            },
            success: function(res) {
                renderReport(res?.data || {});
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan cash flow'));
            }
        });
    }

    function renderReport(report) {
        const summary = report.summary || {};
        $('#period-label').text(`Periode: ${report.period_label || '-'}`);
        $('#saldo-awal-cash').text(rp(summary.saldo_awal_cash || 0));
        $('#pemasukan-cash').text(rp(summary.pemasukan_cash || 0));
        $('#pengeluaran-cash').text(rp(summary.pengeluaran_cash || 0));
        $('#saldo-akhir-cash').text(rp(summary.saldo_akhir_cash || 0));
        $('#saldo-awal-noncash').text(rp(summary.saldo_awal_noncash || 0));
        $('#pemasukan-noncash').text(rp(summary.pemasukan_noncash || 0));
        $('#pengeluaran-noncash').text(rp(summary.pengeluaran_noncash || 0));
        $('#saldo-akhir-all').text(rp(summary.saldo_akhir_all || 0));

        const breakdown = summary.breakdown || {};
        $('#summary-body-left').html([
            summaryRow('Saldo Awal Cash', summary.saldo_awal_cash, 'neutral'),
            summaryRow('Saldo Awal Non Tunai', summary.saldo_awal_noncash, 'neutral'),
            summaryRow('Total Pemasukan Cash', summary.pemasukan_cash, 'in'),
            summaryRow('Total Pemasukan Non Tunai', summary.pemasukan_noncash, 'in'),
            summaryRow('Total Pengeluaran Cash', summary.pengeluaran_cash, 'out'),
            summaryRow('Total Pengeluaran Non Tunai', summary.pengeluaran_noncash, 'out'),
            summaryRow('Sisa Saldo Cash', summary.saldo_akhir_cash, 'total'),
            summaryRow('Sisa Saldo Non Tunai', summary.saldo_akhir_noncash, 'total'),
            summaryRow('Sisa Saldo Akumulasi', summary.saldo_akhir_all, 'total')
        ].join(''));
        $('#summary-body-right').html(Object.keys(breakdown).map(label => summaryRow(label, breakdown[label], labelType(label))).join('') || '<tr><td class="text-center text-muted">Belum ada mutasi</td></tr>');

        currentRows = report.rows || [];
        table.clear().rows.add(currentRows).draw();
    }

    function summaryRow(label, amount, type) {
        const cls = type === 'in' ? 'text-success' : (type === 'out' ? 'text-danger' : (type === 'total' ? 'fw-semibold' : ''));
        return `<tr><td>${escapeHtml(label)}</td><td class="text-end ${cls}">${rp(amount || 0)}</td></tr>`;
    }

    function showDailyDetail(tanggal) {
        const row = currentRows.find(item => String(item.tanggal || '') === String(tanggal || ''));
        if (!row) {
            toastr.error('Detail tanggal tidak ditemukan');
            return;
        }

        $('#detail-title').text(`Periode ${row.tanggal || '-'}`);
        $('#detail-body').html((row.detail || []).map(item => summaryRow(item.label, item.amount, item.type || labelType(item.label))).join(''));
        detailModal.show();
    }

    function labelType(label) {
        return String(label || '').includes('Pengeluaran') ||
            String(label || '').includes('Pembayaran Supplier') ||
            String(label || '').includes('Mutasi Saldo Keluar') ? 'out' : 'in';
    }

    function moneyCell(data) {
        return formatMoneyValue(data || 0);
    }

    function rp(value) {
        return 'Rp ' + formatMoneyValue(value || 0);
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function escapeAttr(value) {
        return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }
</script>
<?= $this->endSection('javascript') ?>
