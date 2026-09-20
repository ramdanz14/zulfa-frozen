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
       LAPORAN HUTANG SUPPLIER MOBILE & DESKTOP STYLING
       ========================================================= */
    .laphutang-main-cell {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .laphutang-sup-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
    }
    .laphutang-code-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        color: #475569;
        background: #f8fafc;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }
    .laphutang-sisa-amount {
        font-weight: 700;
        font-size: 0.95rem;
        color: #dc3545;
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
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-7">
                        <h4 class="fw-bold mb-1 text-dark">Laporan Hutang Supplier</h4>
                        <p class="mb-0 text-muted small"><span id="period-label" class="fw-semibold">Periode aktif</span> | Rekap pembelian kredit TERIMA per supplier.</p>
                    </div>
                    <div class="col-12 col-md-5 text-md-end">
                        <div id="selected-store-info" class="badge bg-white text-dark border px-2 py-1 font-monospace"></div>
                        <a href="<?= base_url('/hutang') ?>" class="btn btn-outline-danger btn-sm ms-md-2 mt-1 mt-md-0 fw-semibold">
                            <i class="ti ti-credit-card me-1"></i>Kelola Hutang
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
                    <div class="metric-sub">Pemasok kredit</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label">Total Invoice</span>
                        <i class="ti ti-file-invoice text-muted fs-4"></i>
                    </div>
                    <div class="metric-val text-dark font-monospace" id="summary-invoice">0</div>
                    <div class="metric-sub">Faktur tercatat</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label">Total Tagihan</span>
                        <i class="ti ti-wallet text-primary fs-4"></i>
                    </div>
                    <div class="metric-val text-primary font-monospace" id="summary-nominal">Rp 0</div>
                    <div class="metric-sub">Gross pembelian kredit</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100 bg-danger-subtle border-danger-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-danger fw-bold">Sisa Hutang</span>
                        <i class="ti ti-alert-triangle text-danger fs-4"></i>
                    </div>
                    <div class="metric-val text-danger font-monospace" id="summary-sisa">Rp 0</div>
                    <div class="metric-sub text-danger-emphasis">Belum dilunasi</div>
                </div>
            </div>
        </div>

        <!-- Secondary Breakdown Metrics (Status & Rata-rata Durasi) -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-danger">Status BELUM</span>
                        <span class="badge bg-danger-subtle text-danger" style="font-size:0.65rem;">0% BAYAR</span>
                    </div>
                    <div class="metric-val text-danger font-monospace" id="summary-belum-nom">Rp 0</div>
                    <div class="metric-sub font-monospace" id="summary-belum-inv">0 invoice</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-warning-emphasis">Status CICIL</span>
                        <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:0.65rem;">SEBAGIAN</span>
                    </div>
                    <div class="metric-val text-warning-emphasis font-monospace" id="summary-cicil-nom">Rp 0</div>
                    <div class="metric-sub font-monospace" id="summary-cicil-inv">0 invoice</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-success">Status LUNAS</span>
                        <span class="badge bg-success-subtle text-success" style="font-size:0.65rem;">LUNAS</span>
                    </div>
                    <div class="metric-val text-success font-monospace" id="summary-lunas-nom">Rp 0</div>
                    <div class="metric-sub font-monospace" id="summary-lunas-inv">0 invoice</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="metric-label text-primary">Rata-rata Lunas</span>
                        <i class="ti ti-clock text-primary fs-4"></i>
                    </div>
                    <div class="metric-val text-primary font-monospace" id="summary-durasi">-</div>
                    <div class="metric-sub">Waktu pelunasan faktur</div>
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
                        title: 'Laporan-Hutang-Supplier',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export'
                        }
                    }, {
                        extend: 'pdfHtml5',
                        text: '<i class="ti ti-file-type-pdf"></i> PDF',
                        title: 'Laporan Hutang Supplier',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }, {
                        extend: 'print',
                        text: '<i class="ti ti-printer"></i> Print',
                        title: 'Laporan Hutang Supplier'
                    }, 'pageLength']
                }
            },
            data: [],
            ordering: true,
            order: [
                [7, 'desc']
            ],
            responsive: false,
            pageLength: 25,
            lengthMenu: [
                [25, 50, 100, -1],
                ['25 rows', '50 rows', '100 rows', 'Show all']
            ],
            columns: [
                {
                    data: 'supplier_nama',
                    title: 'Supplier & Rincian',
                    render: function(data, type, row) {
                        if (type === 'export' || type === 'sort') {
                            return data || row.supco || '-';
                        }
                        const supNama = data || row.supco || '-';
                        const supCode = row.supco || '-';
                        const totalInv = Number(row.total_invoice || 0).toLocaleString('id-ID');
                        const totalGross = 'Rp ' + formatMoneyValue(row.total_nominal || 0);
                        const sisaHutang = 'Rp ' + formatMoneyValue(row.sisa_hutang || 0);
                        const totalBayar = 'Rp ' + formatMoneyValue(row.total_bayar || 0);

                        return `
                            <div class="laphutang-main-cell">
                                <!-- BARIS 1: NAMA SUPPLIER & KODE -->
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                    <div class="laphutang-sup-name">
                                        <i class="ti ti-building-store text-primary d-inline d-md-none me-1"></i>${escapeHtml(supNama)}
                                    </div>
                                    <span class="laphutang-code-badge">
                                        <i class="ti ti-hash"></i>${escapeHtml(supCode)}
                                    </span>
                                </div>

                                <!-- BARIS 2 KHUSUS MOBILE: TOTAL INVOICE & SISA HUTANG -->
                                <div class="d-flex align-items-center gap-1 flex-wrap d-md-none mt-1" style="font-size: 0.775rem;">
                                    <span class="badge bg-light text-muted border"><i class="ti ti-file-invoice me-1"></i>${totalInv} inv</span>
                                    <span class="badge bg-light text-secondary border">Tagihan: ${totalGross}</span>
                                    <span class="laphutang-sisa-amount ms-auto">${sisaHutang}</span>
                                </div>

                                <!-- BARIS 3 KHUSUS MOBILE: BREAKDOWN STATUS PILLS -->
                                <div class="d-flex align-items-center gap-1 flex-wrap d-md-none mt-1">
                                    ${row.invoice_belum > 0 ? `<span class="badge bg-danger-subtle text-danger" style="font-size:0.7rem;">Belum: ${row.invoice_belum}</span>` : ''}
                                    ${row.invoice_cicil > 0 ? `<span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:0.7rem;">Cicil: ${row.invoice_cicil}</span>` : ''}
                                    ${row.invoice_lunas > 0 ? `<span class="badge bg-success-subtle text-success" style="font-size:0.7rem;">Lunas: ${row.invoice_lunas}</span>` : ''}
                                    ${row.avg_durasi_lunas_hari ? `<span class="badge bg-primary-subtle text-primary ms-auto" style="font-size:0.7rem;"><i class="ti ti-clock me-1"></i>${Math.round(row.avg_durasi_lunas_hari)} hr</span>` : ''}
                                </div>

                                <!-- DESKTOP SUBTITLE -->
                                <small class="text-muted d-none d-md-block font-monospace">
                                    <i class="ti ti-id me-1"></i>Kode: ${escapeHtml(supCode)}
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
                    title: 'Total Tagihan',
                    className: 'text-end col-desktop-only font-monospace',
                    render: moneyRender
                },
                {
                    data: null,
                    title: 'BELUM',
                    className: 'text-end col-desktop-only',
                    render: (_, type, row) => statusRender(row.invoice_belum, row.nominal_belum, type)
                },
                {
                    data: null,
                    title: 'CICIL',
                    className: 'text-end col-desktop-only',
                    render: (_, type, row) => statusRender(row.invoice_cicil, row.nominal_cicil, type)
                },
                {
                    data: null,
                    title: 'LUNAS',
                    className: 'text-end col-desktop-only',
                    render: (_, type, row) => statusRender(row.invoice_lunas, row.nominal_lunas, type)
                },
                {
                    data: 'total_bayar',
                    title: 'Total Bayar',
                    className: 'text-end col-desktop-only font-monospace text-success',
                    render: moneyRender
                },
                {
                    data: 'sisa_hutang',
                    title: 'Sisa Hutang',
                    className: 'text-end text-danger fw-bold font-monospace',
                    render: moneyRender
                },
                {
                    data: 'avg_durasi_lunas_hari',
                    title: 'Rata-rata Lunas',
                    className: 'text-center col-desktop-only',
                    render: durationRender
                }
            ],
            autoWidth: false,
            drawCallback: function() {
                if (window.innerWidth < 768) {
                    $('#table-data colgroup col').each(function() {
                        const colIdx = $(this).attr('data-dt-column');
                        if (colIdx !== undefined && colIdx !== '0' && colIdx !== '7') {
                            $(this).remove();
                        }
                    });
                }
            }
        });
    }

    function loadReport() {
        updateStoreInfo();
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/laphutang/report') ?>',
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
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan hutang supplier'));
            }
        });
    }

    function renderReport(report) {
        const summary = report.summary || {};
        const periodText = `${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`;
        $('#period-label').text(`Periode: ${periodText}`);
        $('#selected-store-info').text(`Toko: ${report?.toko?.toko_id || selectedStore()} - ${report?.toko?.toko_nama || selectedStore()}`);
        
        // Primary metrics
        $('#summary-supplier').text(Number(summary.supplier_count || 0).toLocaleString('id-ID'));
        $('#summary-invoice').text(Number(summary.total_invoice || 0).toLocaleString('id-ID'));
        $('#summary-nominal').text(rp(summary.total_nominal || 0));
        $('#summary-sisa').text(rp(summary.sisa_hutang || 0));
        
        // Secondary status metrics
        $('#summary-belum-nom').text(rp(summary.nominal_belum || 0));
        $('#summary-belum-inv').text(`${Number(summary.invoice_belum || 0).toLocaleString('id-ID')} invoice`);
        
        $('#summary-cicil-nom').text(rp(summary.nominal_cicil || 0));
        $('#summary-cicil-inv').text(`${Number(summary.invoice_cicil || 0).toLocaleString('id-ID')} invoice`);
        
        $('#summary-lunas-nom').text(rp(summary.nominal_lunas || 0));
        $('#summary-lunas-inv').text(`${Number(summary.invoice_lunas || 0).toLocaleString('id-ID')} invoice`);
        
        $('#summary-durasi').text(durationLabel(summary.avg_durasi_lunas_hari || 0));
        
        table.clear().rows.add(report.rows || []).draw();
    }

    function statusRender(invoice, nominal, type) {
        const invoiceText = Number(invoice || 0).toLocaleString('id-ID');
        const nominalText = formatMoneyValue(nominal || 0);
        if (type === 'export' || type === 'sort') {
            return `Rp ${nominalText}/${invoiceText} invoice`;
        }
        return `<div class="fw-semibold">Rp ${nominalText}</div><small class="text-muted font-monospace">${invoiceText} inv</small>`;
    }

    function moneyRender(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        return 'Rp ' + formatMoneyValue(data || 0);
    }

    function durationRender(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        return durationLabel(data || 0);
    }

    function durationLabel(value) {
        const days = Number(value || 0);
        if (!days) {
            return '-';
        }
        const roundedDays = Math.round(days);
        const weeks = Math.round(roundedDays / 7);
        if (weeks >= 1) {
            return `${roundedDays} hari (${weeks} mgg)`;
        }
        return `${roundedDays} hari`;
    }

    function rp(value) {
        return 'Rp ' + formatMoneyValue(value || 0);
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }
</script>
<?= $this->endSection('javascript') ?>