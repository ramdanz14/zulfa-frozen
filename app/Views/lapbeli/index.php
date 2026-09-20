<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<style>
    /* =========================================================
       LAPORAN PEMBELIAN SUPPLIER MOBILE & DESKTOP STYLING
       ========================================================= */
    .lapbeli-main-cell {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .lapbeli-sup-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
    }

    .lapbeli-code-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        color: #475569;
        background: #f8fafc;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }

    .lapbeli-amount-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.95rem;
        font-weight: 700;
        color: #0d6efd;
    }

    /* Metric Cards Styling */
    .metric-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        transition: all 0.2s ease;
    }

    .metric-card .metric-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .metric-card .metric-val {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .metric-card .metric-sub {
        font-size: 0.725rem;
        color: #64748b;
    }

    @media (max-width: 767.98px) {

        /* Search bar full width pada mobile */
        .dt-container .dt-search {
            width: 100% !important;
            text-align: left !important;
            margin-bottom: 0.5rem;
        }

        .dt-container .dt-search label {
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
        }

        .dt-container .dt-search input[type="search"],
        .dt-container .dt-search input.form-control {
            width: 100% !important;
            height: 42px !important;
            margin-top: 4px !important;
            margin-left: 0 !important;
            padding: 6px 12px !important;
            font-size: 14px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            box-sizing: border-box !important;
        }

        /* Buttons export stack on mobile */
        .dt-container .dt-buttons {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 4px !important;
            margin-bottom: 0.5rem !important;
        }

        /* Sembunyikan kolom desktop-only di HP */
        #table-data .col-desktop-only,
        .dt-container #table-data .col-desktop-only,
        #table-data th.col-desktop-only,
        #table-data td.col-desktop-only,
        table.dataTable th.col-desktop-only,
        table.dataTable td.col-desktop-only {
            display: none !important;
            width: 0 !important;
            max-width: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            overflow: hidden !important;
        }

        /* Hapus space kosong dari colgroup DataTables di HP */
        #table-data colgroup {
            display: none !important;
        }

        #table-data {
            table-layout: auto !important;
            width: 100% !important;
        }

        #table-data th,
        #table-data td {
            padding: 0.65rem 0.5rem !important;
            vertical-align: middle;
        }

        /* Metric card padding compact di HP */
        .metric-card {
            padding: 0.6rem 0.75rem !important;
        }

        .metric-card .metric-val {
            font-size: 0.98rem !important;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Header Banner -->
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-7">
                        <h4 class="fw-bold mb-1 text-dark">Laporan Pembelian Supplier</h4>
                        <p class="mb-0 text-muted small"><span id="period-label" class="fw-semibold">Periode aktif</span> | Rekap pembelian status TERIMA per supplier.</p>
                    </div>
                    <div class="col-12 col-md-5 text-md-end">
                        <div id="selected-store-info" class="badge bg-white text-dark border px-2 py-1 font-monospace"></div>
                        <a href="<?= base_url('/pembelian') ?>" class="btn btn-outline-primary btn-sm ms-md-2 mt-1 mt-md-0 fw-semibold">
                            <i class="ti ti-shopping-cart me-1"></i>Faktur Pembelian
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-calendar me-1"></i>Range Tanggal Faktur</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="ti ti-calendar-event"></i></span>
                            <input type="text" class="form-control" id="filter-range" readonly style="background-color: #fff; cursor: pointer;">
                        </div>
                    </div>
                    <div class="col-12 col-md-4" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-building-store me-1"></i>Filter Toko</label>
                        <select class="form-select form-select-sm select2" id="filter-toko">
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>" <?= (string) ($row['toko_id'] ?? '') === (string) session('toko_id') ? 'selected' : '' ?>>
                                    <?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-<?= !empty($tokoOptions) ? '4' : '8' ?>">
                        <div class="row g-1">
                            <div class="col-7">
                                <button type="button" class="btn btn-primary btn-sm w-100 py-2 fw-semibold" id="btn-filter">
                                    <i class="ti ti-search me-1"></i>Tampilkan
                                </button>
                            </div>
                            <div class="col-5">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-2" id="btn-reset">
                                    <i class="ti ti-refresh me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Primary Summary Metrics (Ringkasan Utama) -->
        <div class="row g-2 mb-2">
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label">Supplier</span>
                        <i class="ti ti-building-store text-muted fs-4"></i>
                    </div>
                    <div class="metric-val text-dark font-monospace" id="summary-supplier">0</div>
                    <div class="metric-sub">Pemasok aktif</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label">Total Invoice</span>
                        <i class="ti ti-file-invoice text-muted fs-4"></i>
                    </div>
                    <div class="metric-val text-dark font-monospace" id="summary-invoice">0</div>
                    <div class="metric-sub">Faktur diterima</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100 bg-primary-subtle border-primary-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-primary fw-bold">Total Pembelian</span>
                        <i class="ti ti-wallet text-primary fs-4"></i>
                    </div>
                    <div class="metric-val text-primary font-monospace" id="summary-nominal">Rp 0</div>
                    <div class="metric-sub text-primary-emphasis">Gross pembelian diterima</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label">Jenis Item</span>
                        <i class="ti ti-box text-muted fs-4"></i>
                    </div>
                    <div class="metric-val text-dark font-monospace" id="summary-item">0</div>
                    <div class="metric-sub">Varian produk dibeli</div>
                </div>
            </div>
        </div>

        <!-- Secondary Breakdown Metrics (Kredit, Non Kredit, Frekuensi, Jarak Kirim) -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-danger">Kredit</span>
                        <span class="badge bg-danger-subtle text-danger" style="font-size:0.65rem;">HUTANG</span>
                    </div>
                    <div class="metric-val text-danger font-monospace" id="summary-kredit-nom">Rp 0</div>
                    <div class="metric-sub font-monospace" id="summary-kredit-inv">0 invoice</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-success">Tidak Kredit</span>
                        <span class="badge bg-success-subtle text-success" style="font-size:0.65rem;">TUNAI</span>
                    </div>
                    <div class="metric-val text-success font-monospace" id="summary-non-kredit-nom">Rp 0</div>
                    <div class="metric-sub font-monospace" id="summary-non-kredit-inv">0 invoice</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-info-emphasis">Frekuensi Kiriman</span>
                        <i class="ti ti-truck-delivery text-info-emphasis fs-4"></i>
                    </div>
                    <div class="metric-val text-dark font-monospace" id="summary-frekuensi">0</div>
                    <div class="metric-sub">Total pengiriman</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-primary">Rata Jarak Kirim</span>
                        <i class="ti ti-calendar-time text-primary fs-4"></i>
                    </div>
                    <div class="metric-val text-primary font-monospace" id="summary-jarak">-</div>
                    <div class="metric-sub">Interval antar pesanan</div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead class="table-light"></thead>
                    <tbody>
                        <tr>
                            <td>No data to show</td>
                        </tr>
                    </tbody>
                </table>
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
                        title: 'Laporan-Pembelian-Supplier',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export'
                        }
                    }, {
                        extend: 'pdfHtml5',
                        text: '<i class="ti ti-file-type-pdf"></i> PDF',
                        title: 'Laporan Pembelian Supplier',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }, {
                        extend: 'print',
                        text: '<i class="ti ti-printer"></i> Print',
                        title: 'Laporan Pembelian Supplier'
                    }, 'pageLength']
                }
            },
            data: [],
            ordering: true,
            order: [
                [2, 'desc']
            ],
            responsive: false,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [
                [25, 50, 100, -1],
                ['25 rows', '50 rows', '100 rows', 'Show all']
            ],
            drawCallback: function() {
                if (window.innerWidth < 768) {
                    $('#table-data colgroup').remove();
                }
            },
            columns: [{
                    data: 'supplier_nama',
                    title: 'Supplier & Rincian Pembelian',
                    render: function(data, type, row) {
                        if (type === 'export' || type === 'sort') {
                            return data || row.supco || '-';
                        }
                        const supNama = data || row.supco || '-';
                        const supCode = row.supco || '-';
                        const totalInv = num(row.total_invoice || 0);
                        const totalNom = rp(row.total_nominal || 0);
                        const totalItem = num(row.total_jenis_item || 0);
                        const frekDatang = num(row.total_frekuensi_datang || 0);
                        const jarakKirim = distanceLabel(row.rata_rata_jarak_kirim_hari || 0);

                        const invKredit = Number(row.invoice_kredit || 0);
                        const nomKredit = rp(row.nominal_kredit || 0);
                        const invTunai = Number(row.invoice_non_kredit || 0);
                        const nomTunai = rp(row.nominal_non_kredit || 0);

                        return `
                            <div class="lapbeli-main-cell">
                                <!-- BARIS 1: NAMA SUPPLIER & KODE -->
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                    <div class="lapbeli-sup-name">
                                        <i class="ti ti-building-store text-primary d-inline d-md-none me-1"></i>${escapeHtml(supNama)}
                                    </div>
                                    <span class="lapbeli-code-badge">
                                        <i class="ti ti-hash"></i>${escapeHtml(supCode)}
                                    </span>
                                </div>

                                <!-- BARIS 2 KHUSUS MOBILE: TOTAL INVOICE & TOTAL PEMBELIAN -->
                                <div class="d-flex align-items-center gap-1 flex-wrap d-md-none mt-1" style="font-size: 0.775rem;">
                                    <span class="badge bg-light text-muted border"><i class="ti ti-file-invoice me-1"></i>${totalInv} inv</span>
                                    <span class="badge bg-light text-secondary border"><i class="ti ti-box me-1"></i>${totalItem} item</span>
                                </div>

                                <!-- BARIS 3 KHUSUS MOBILE: KREDIT / TUNAI BADGES -->
                                <div class="d-flex align-items-center gap-1 flex-wrap d-md-none mt-1">
                                    ${invKredit > 0 ? `<span class="badge bg-danger-subtle text-danger" style="font-size:0.7rem;"><i class="ti ti-clock-pause me-1"></i>Kredit: ${invKredit} inv (${nomKredit})</span>` : ''}
                                    ${invTunai > 0 ? `<span class="badge bg-success-subtle text-success" style="font-size:0.7rem;"><i class="ti ti-check me-1"></i>Tunai: ${invTunai} inv (${nomTunai})</span>` : ''}
                                </div>

                                <!-- BARIS 4 KHUSUS MOBILE: FREKUENSI & JARAK KIRIM -->
                                <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap d-md-none mt-1" style="font-size:0.75rem;">
                                    <span class="text-muted"><i class="ti ti-truck-delivery me-1"></i>${frekDatang}x kirim</span>
                                    ${jarakKirim !== '-' ? `<span class="badge bg-primary-subtle text-primary ms-auto" style="font-size:0.7rem;"><i class="ti ti-calendar-time me-1"></i>${jarakKirim}</span>` : ''}
                                </div>

                                <!-- DESKTOP SUBTITLE -->
                                <small class="text-muted d-none d-md-block font-monospace">
                                    <i class="ti ti-id me-1"></i>Kode: ${escapeHtml(supCode)} &bull; ${totalItem} jenis item &bull; ${frekDatang}x kirim
                                </small>
                            </div>
                        `;
                    }
                },
                {
                    data: 'total_invoice',
                    title: 'Total Invoice',
                    className: 'text-center col-desktop-only font-monospace'
                },
                {
                    data: 'total_nominal',
                    title: 'Total Pembelian',
                    className: 'lapbeli-amount-badge ms-auto',
                    render: moneyRender
                },
                {
                    data: null,
                    title: 'Kredit',
                    className: 'text-end col-desktop-only',
                    render: (_, type, row) => bucketRender(row.invoice_kredit, row.nominal_kredit, type)
                },
                {
                    data: null,
                    title: 'Tidak Kredit',
                    className: 'text-end col-desktop-only',
                    render: (_, type, row) => bucketRender(row.invoice_non_kredit, row.nominal_non_kredit, type)
                },
                {
                    data: 'total_jenis_item',
                    title: 'Jenis Item',
                    className: 'text-center col-desktop-only font-monospace'
                },
                {
                    data: 'total_frekuensi_datang',
                    title: 'Frekuensi',
                    className: 'text-center col-desktop-only font-monospace'
                },
                {
                    data: 'kiriman_pertama',
                    title: 'Kiriman Pertama',
                    className: 'text-center col-desktop-only font-monospace',
                    render: dateRender
                },
                {
                    data: 'kiriman_terakhir',
                    title: 'Kiriman Terakhir',
                    className: 'text-center col-desktop-only font-monospace',
                    render: dateRender
                },
                {
                    data: 'rata_rata_jarak_kirim_hari',
                    title: 'Rata Jarak Kirim',
                    className: 'text-center col-desktop-only',
                    render: distanceRender
                }
            ]
        });
    }

    function loadReport() {
        updateStoreInfo();
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lapbeli/report') ?>',
            dataType: 'json',
            data: {
                date_start: filterStart.format('YYYY-MM-DD'),
                date_end: filterEnd.format('YYYY-MM-DD'),
                toko_id: selectedStore()
            },
            success: function(res) {
                renderReport(res?.data || {});
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan pembelian supplier'));
            }
        });
    }

    function renderReport(report) {
        const summary = report.summary || {};
        const periodText = `${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`;
        $('#period-label').text(`Periode: ${periodText}`);
        $('#selected-store-info').text(`Toko: ${report?.toko?.toko_id || selectedStore()} - ${report?.toko?.toko_nama || selectedStore()}`);

        // Primary metrics
        $('#summary-supplier').text(num(summary.supplier_count || 0));
        $('#summary-invoice').text(num(summary.total_invoice || 0));
        $('#summary-nominal').text(rp(summary.total_nominal || 0));
        $('#summary-item').text(num(summary.total_jenis_item || 0));

        // Secondary metrics
        $('#summary-kredit-nom').text(rp(summary.nominal_kredit || 0));
        $('#summary-kredit-inv').text(`${num(summary.invoice_kredit || 0)} invoice`);

        $('#summary-non-kredit-nom').text(rp(summary.nominal_non_kredit || 0));
        $('#summary-non-kredit-inv').text(`${num(summary.invoice_non_kredit || 0)} invoice`);

        $('#summary-frekuensi').text(num(summary.total_frekuensi_datang || 0));
        $('#summary-jarak').text(distanceLabel(summary.rata_rata_jarak_kirim_hari || 0));

        table.clear().rows.add(report.rows || []).draw();
    }

    function bucketRender(invoice, nominal, type) {
        const invoiceText = num(invoice || 0);
        const nominalText = formatMoneyValue(nominal || 0);
        if (type === 'export' || type === 'sort') {
            return `${invoiceText} invoice / Rp ${nominalText}`;
        }
        return `<div class="fw-semibold">Rp ${nominalText}</div><small class="text-muted">${invoiceText} invoice</small>`;
    }

    function moneyRender(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        return rp(data || 0);
    }

    function dateRender(data, type) {
        if (type === 'sort' || type === 'type' || type === 'export') {
            return data || '';
        }
        return data ? moment(data, 'YYYY-MM-DD').format('DD/MM/YYYY') : '-';
    }

    function distanceRender(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        return distanceLabel(data || 0);
    }

    function distanceLabel(value) {
        const days = Number(value || 0);
        if (!days) {
            return '-';
        }
        const roundedDays = Math.round(days);
        const weeks = Math.round(roundedDays / 7);
        return weeks >= 1 ? `${roundedDays} hari (${weeks} mgg)` : `${roundedDays} hari`;
    }

    function rp(value) {
        return 'Rp ' + formatMoneyValue(value || 0);
    }

    function num(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?= $this->endSection('javascript') ?>