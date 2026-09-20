<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<style>
    /* Metric KPI Cards Styling (Compact & Clean) */
    .metric-item-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 0.65rem 0.85rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.04);
    }
    .metric-item-card .metric-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }
    .metric-item-card .metric-number {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.2;
    }

    /* Mobile CardView Styling for LapJualItem */
    .lapjualitem-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 0.9rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .lapjualitem-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    @media (max-width: 767.98px) {
        .metric-item-card {
            padding: 0.5rem 0.65rem;
        }
        .metric-item-card .metric-number {
            font-size: 0.95rem;
        }
        .metric-item-card .metric-label {
            font-size: 0.68rem;
        }
        .btn-filter-touch {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-2 p-md-3">
        <!-- Header Page Title -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Laporan Penjualan Per Item</h4>
                        <p class="mb-0 small text-muted"><span id="period-label">Periode aktif</span> &bull; Analisis Qty, HPP, Gross, Diskon, Margin, dan Basket Metric per Item.</p>
                    </div>
                    <div>
                        <div id="selected-store-info" class="badge bg-white text-dark border px-2 py-1 small"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Range Tanggal Transaksi</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="ti ti-calendar text-primary"></i></span>
                            <input type="text" class="form-control" id="filter-range" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-4" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Filter Toko</label>
                        <select class="form-select select2" id="filter-toko">
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>" <?= (string) ($row['toko_id'] ?? '') === (string) session('toko_id') ? 'selected' : '' ?>>
                                    <?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-<?= !empty($tokoOptions) ? '3' : '7' ?> d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm flex-fill btn-filter-touch" id="btn-filter">
                            <i class="ti ti-search me-1"></i> Tampilkan
                        </button>
                        <button type="button" class="btn btn-light btn-sm flex-fill btn-filter-touch border" id="btn-reset">
                            <i class="ti ti-refresh me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8 KPI Metrics Cards: Compact Grid (4 baris di Mobile 2x4, atau 2x4 di Desktop) -->
        <div class="row g-2 mb-2">
            <div class="col-6 col-md-3">
                <div class="metric-item-card">
                    <div class="metric-label"><i class="ti ti-receipt text-primary me-1"></i>Total Struk</div>
                    <div class="metric-number text-dark" id="summary-struk">0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-item-card">
                    <div class="metric-label"><i class="ti ti-category text-info me-1"></i>Jenis Item Terjual</div>
                    <div class="metric-number text-dark" id="summary-item">0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-item-card">
                    <div class="metric-label"><i class="ti ti-package text-warning me-1"></i>Total Qty Terjual</div>
                    <div class="metric-number text-dark" id="summary-qty">0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-item-card">
                    <div class="metric-label"><i class="ti ti-trending-up text-success me-1"></i>Total Margin Bruto</div>
                    <div class="metric-number text-success" id="summary-margin">Rp 0</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-item-card">
                    <div class="metric-label"><i class="ti ti-coins text-secondary me-1"></i>Total HPP</div>
                    <div class="metric-number text-dark" id="summary-hpp">Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-item-card">
                    <div class="metric-label"><i class="ti ti-cash text-primary me-1"></i>Total Gross</div>
                    <div class="metric-number text-dark" id="summary-gross">Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-item-card">
                    <div class="metric-label"><i class="ti ti-tag text-danger me-1"></i>Total Diskon</div>
                    <div class="metric-number text-danger" id="summary-diskon">Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-item-card">
                    <div class="metric-label"><i class="ti ti-shopping-cart text-primary me-1"></i>Avg Attach / Struk</div>
                    <div class="metric-number text-primary" id="summary-basket">0% / 0</div>
                </div>
            </div>
        </div>

        <!-- Table Data & CardView Mobile -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-box fs-5 text-primary"></i>
                    <h6 class="mb-0 fw-bold">Daftar Penjualan Per Item</h6>
                </div>
            </div>
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Kategori</th>
                            <th>Struk Item</th>
                            <th>Total Qty</th>
                            <th>Total HPP</th>
                            <th>Total Gross</th>
                            <th>Total Diskon</th>
                            <th>Total Margin</th>
                            <th>Attach Rate</th>
                            <th>Qty / Struk</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <!-- CardView Template for Mobile (< 768px) -->
                <template id="card-lapjualitem-template">
                    <div class="lapjualitem-card mb-2">
                        <!-- Baris 1: Nama Item & Kategori Badge -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="text-truncate flex-grow-1">
                                <span class="d-block" data-dtcv-field="0"></span>
                            </div>
                            <span class="badge bg-light text-secondary border small px-2 py-1 flex-shrink-0" data-dtcv-field="1"></span>
                        </div>

                        <!-- Baris 2: Qty Terjual, Gross, & Margin Bruto (Highlight) -->
                        <div class="p-2 bg-light-subtle rounded border mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem; text-transform: uppercase;">Total Qty Terjual</small>
                                    <span class="fw-bold text-dark fs-6" data-dtcv-field="3"></span>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block" style="font-size: 0.7rem; text-transform: uppercase;">Margin Bruto</small>
                                    <span class="fw-bold text-success fs-6" data-dtcv-field="7"></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-1 border-top small text-muted" style="font-size: 0.75rem;">
                                <span>Gross: <b class="text-dark" data-dtcv-field="5"></b></span>
                                <span>Diskon: <b class="text-danger" data-dtcv-field="6"></b></span>
                            </div>
                        </div>

                        <!-- Baris 3: Basket Metrics: Struk Item, Attach Rate, Qty/Struk -->
                        <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 0.75rem;">
                            <div>
                                <span><i class="ti ti-receipt me-1"></i><span data-dtcv-field="2"></span> struk</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    Attach: <span data-dtcv-field="8"></span>
                                </span>
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    <span data-dtcv-field="9"></span> /struk
                                </span>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const canSelectStore = akses_menu?.akses_delete === 'Y';
    const sessionTokoId = '<?= esc((string) session('toko_id')) ?>';
    let filterStart = moment().startOf('month');
    let filterEnd = moment().endOf('month');
    let table = null;

    $(function() {
        if (canSelectStore) {
            $('#filter-toko-wrapper').show();
            $('#filter-toko').select2({
                width: '100%',
                placeholder: 'Pilih toko'
            });
            if (!$('#filter-toko').val()) {
                $('#filter-toko').val(sessionTokoId).trigger('change');
            }
        }

        $('#filter-range').daterangepicker({
            startDate: filterStart,
            endDate: filterEnd,
            autoApply: true,
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Batal',
                fromLabel: 'Dari',
                toLabel: 'Sampai',
                customRangeLabel: 'Pilih Sendiri',
                weekLabel: 'M',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                firstDay: 1
            },
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            filterStart = start;
            filterEnd = end;
        });
        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);

        updateStoreInfo();
        initTable();
        loadReport();
    });

    $('#btn-filter').on('click', loadReport);
    $('#btn-reset').on('click', function() {
        filterStart = moment().startOf('month');
        filterEnd = moment().endOf('month');
        $('#filter-range').data('daterangepicker').setStartDate(filterStart);
        $('#filter-range').data('daterangepicker').setEndDate(filterEnd);
        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        if (canSelectStore) {
            $('#filter-toko').val(sessionTokoId).trigger('change');
        }
        loadReport();
    });
    $('#filter-toko').on('change', updateStoreInfo);

    function selectedStore() {
        return canSelectStore ? ($('#filter-toko').val() || sessionTokoId) : sessionTokoId;
    }

    function updateStoreInfo() {
        $('#selected-store-info').text(`Toko: ${selectedStore()}`);
    }

    function initTable() {
        DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary btn-sm';
        table = $('#table-data').DataTable({
            layout: {
                topStart: {
                    buttons: [{
                        text: '<i class="ti ti-file-type-xls"></i> Excel',
                        extend: 'excelHtml5',
                        title: 'Laporan-Penjualan-Per-Item',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export'
                        }
                    }, {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        title: 'Laporan Penjualan Per Item',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }, {
                        extend: 'print',
                        text: 'Print',
                        title: 'Laporan Penjualan Per Item'
                    }, 'pageLength']
                }
            },
            data: [],
            ordering: true,
            order: [[7, 'desc']],
            responsive: true,
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], ['25 rows', '50 rows', '100 rows', 'Show all']],
            cardView: {
                enable: true,
                breakpoint: 768,
                template: '#card-lapjualitem-template',
                gridClass: 'col-12 col-sm-6 mb-2'
            },
            columns: [{
                    data: 'nama_item',
                    render: function(data, type, row) {
                        if (type === 'export' || type === 'sort') {
                            return data || row.kode_item || '-';
                        }
                        return `<div class="fw-semibold text-dark">${escapeHtml(data || row.kode_item || '-')}</div><small class="text-muted font-monospace">${escapeHtml(row.kode_item || '-')}</small>`;
                    }
                },
                { data: 'kat_id', className: 'text-center', render: data => escapeHtml(data || '-') },
                { data: 'jumlah_struk_item', className: 'text-center', render: numRender },
                { data: 'total_qty', className: 'text-end', render: qtyRender },
                { data: 'total_hpp', className: 'text-end', render: moneyRender },
                { data: 'total_gross', className: 'text-end', render: moneyRender },
                { data: 'total_diskon', className: 'text-end text-danger', render: moneyRender },
                { data: 'total_margin', className: 'text-end text-success fw-bold', render: moneyRender },
                { data: 'attach_rate', className: 'text-center', render: percentRender },
                { data: 'qty_per_struk', className: 'text-center', render: qtyRender }
            ]
        });
    }

    function loadReport() {
        updateStoreInfo();
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lapjualitem/report') ?>',
            dataType: 'json',
            data: {
                date_start: filterStart.format('YYYY-MM-DD'),
                date_end: filterEnd.format('YYYY-MM-DD'),
                toko_id: selectedStore()
            },
            beforeSend: function() {
                // Show subtle indicator
            },
            success: function(res) {
                renderReport(res?.data || {});
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan penjualan per item'));
            }
        });
    }

    function renderReport(report) {
        const summary = report.summary || {};
        const periodText = `${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`;
        $('#period-label').text(`Periode: ${periodText}`);
        $('#selected-store-info').text(`Toko: ${report?.toko?.toko_id || selectedStore()} - ${report?.toko?.toko_nama || selectedStore()}`);
        $('#summary-struk').text(num(summary.total_struk || 0));
        $('#summary-item').text(num(summary.total_item || 0));
        $('#summary-qty').text(qty(summary.total_qty || 0));
        $('#summary-margin').text(rp(summary.total_margin || 0));
        $('#summary-hpp').text(rp(summary.total_hpp || 0));
        $('#summary-gross').text(rp(summary.total_gross || 0));
        $('#summary-diskon').text(rp(summary.total_diskon || 0));
        $('#summary-basket').text(`${percent(summary.avg_attach_rate || 0)} / ${qty(summary.avg_qty_per_struk || 0)}`);
        table.clear().rows.add(report.rows || []).draw();
    }

    function moneyRender(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        return rp(data || 0);
    }

    function percentRender(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        return percent(data || 0);
    }

    function qtyRender(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        return qty(data || 0);
    }

    function numRender(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        return num(data || 0);
    }

    function rp(value) {
        return 'Rp ' + formatMoneyValue(value || 0);
    }

    function percent(value) {
        return Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }) + '%';
    }

    function qty(value) {
        return Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function num(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }
</script>
<?= $this->endSection('javascript') ?>
