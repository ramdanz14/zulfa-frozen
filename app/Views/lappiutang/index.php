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
    .metric-piutang-card {
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
    .metric-piutang-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.04);
    }
    .metric-piutang-card .metric-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }
    .metric-piutang-card .metric-number {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.2;
    }

    /* Mobile CardView Styling for LapPiutang */
    .lappiutang-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 0.9rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .lappiutang-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .lappiutang-card.has-sisa {
        border-left: 3px solid #dc3545;
    }
    .lappiutang-card.is-lunas {
        border-left: 3px solid #198754;
    }

    @media (max-width: 767.98px) {
        .metric-piutang-card {
            padding: 0.5rem 0.65rem;
        }
        .metric-piutang-card .metric-number {
            font-size: 0.95rem;
        }
        .metric-piutang-card .metric-label {
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
        <div class="card bg-success-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Laporan Piutang Customer</h4>
                        <p class="mb-0 small text-muted"><span id="period-label">Periode aktif</span> &bull; Rekap penjualan kredit dan saldo piutang per customer.</p>
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

        <!-- KPI Metrics Grid: 2x4 di Mobile, 1x4 di Desktop -->
        <div class="row g-2 mb-2">
            <div class="col-6 col-md-3">
                <div class="metric-piutang-card">
                    <div class="metric-label"><i class="ti ti-users text-primary me-1"></i>Customer Berpiutang</div>
                    <div class="metric-number text-dark" id="summary-customer">0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-piutang-card">
                    <div class="metric-label"><i class="ti ti-file-invoice text-info me-1"></i>Total Invoice</div>
                    <div class="metric-number text-dark" id="summary-invoice">0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-piutang-card">
                    <div class="metric-label"><i class="ti ti-wallet text-secondary me-1"></i>Total Piutang</div>
                    <div class="metric-number text-dark" id="summary-nominal">Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-piutang-card">
                    <div class="metric-label"><i class="ti ti-alert-circle text-danger me-1"></i>Sisa Piutang</div>
                    <div class="metric-number text-danger" id="summary-sisa">Rp 0</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-piutang-card">
                    <div class="metric-label"><i class="ti ti-clock text-danger me-1"></i>Belum Bayar</div>
                    <div class="metric-number text-danger" id="summary-belum">0 inv / Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-piutang-card">
                    <div class="metric-label"><i class="ti ti-hourglass-low text-warning me-1"></i>Cicil</div>
                    <div class="metric-number text-warning" id="summary-cicil">0 inv / Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-piutang-card">
                    <div class="metric-label"><i class="ti ti-circle-check text-success me-1"></i>Lunas</div>
                    <div class="metric-number text-success" id="summary-lunas">0 inv / Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-piutang-card">
                    <div class="metric-label"><i class="ti ti-calendar-stats text-primary me-1"></i>Avg Pelunasan</div>
                    <div class="metric-number text-primary" id="summary-durasi">-</div>
                </div>
            </div>
        </div>

        <!-- Table Data & Mobile CardView -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-receipt-tax fs-5 text-primary"></i>
                    <h6 class="mb-0 fw-bold">Daftar Piutang Per Customer</h6>
                </div>
            </div>
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Total Invoice</th>
                            <th>Total Piutang</th>
                            <th>BELUM</th>
                            <th>CICIL</th>
                            <th>LUNAS</th>
                            <th>Total Bayar</th>
                            <th>Sisa Piutang</th>
                            <th>Rata-rata Pelunasan</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <!-- CardView Template for Mobile (< 768px) -->
                <template id="card-lappiutang-template">
                    <div class="lappiutang-card mb-2">
                        <!-- Baris 1: Customer Nama & Total Invoice Badge -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="text-truncate flex-grow-1">
                                <span class="d-block" data-dtcv-field="0"></span>
                            </div>
                            <span class="badge bg-light text-secondary border small px-2 py-1 flex-shrink-0">
                                <i class="ti ti-file-invoice me-1"></i><span data-dtcv-field="1"></span> Inv
                            </span>
                        </div>

                        <!-- Baris 2: Nilai Total Piutang & Sisa Piutang (Key Highlights) -->
                        <div class="p-2 bg-light-subtle rounded border mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem; text-transform: uppercase;">Total Piutang</small>
                                    <span class="fw-bold text-dark fs-6" data-dtcv-field="2"></span>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block" style="font-size: 0.7rem; text-transform: uppercase;">Sisa Piutang</small>
                                    <span class="fw-bold text-danger fs-6" data-dtcv-field="7"></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-1 border-top small text-muted" style="font-size: 0.75rem;">
                                <span>Sudah Dibayar: <b class="text-success" data-dtcv-field="6"></b></span>
                                <span>Rata-rata Lunas: <b class="text-primary" data-dtcv-field="8"></b></span>
                            </div>
                        </div>

                        <!-- Baris 3: Status Invoice (Belum, Cicil, Lunas) -->
                        <div class="d-flex justify-content-between align-items-center text-muted small pt-1" style="font-size: 0.75rem;">
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    Belum: <span data-dtcv-field="3"></span>
                                </span>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                    Cicil: <span data-dtcv-field="4"></span>
                                </span>
                            </div>
                            <div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    Lunas: <span data-dtcv-field="5"></span>
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
                        title: 'Laporan-Piutang-Customer',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export'
                        }
                    }, {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        title: 'Laporan Piutang Customer',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }, {
                        extend: 'print',
                        text: 'Print',
                        title: 'Laporan Piutang Customer'
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
                template: '#card-lappiutang-template',
                gridClass: 'col-12 col-sm-6 mb-2',
                onCardRender: function($card, rowData) {
                    const sisa = Number(rowData?.sisa_piutang || 0);
                    if (sisa > 0) {
                        $card.find('.lappiutang-card').addClass('has-sisa');
                    } else {
                        $card.find('.lappiutang-card').addClass('is-lunas');
                    }
                }
            },
            columns: [{
                    data: 'customer_nama',
                    render: function(data, type, row) {
                        if (type === 'export' || type === 'sort') {
                            return data || row.cust_id || '-';
                        }
                        const kontak = row.customer_kontak ? ` | ${row.customer_kontak}` : '';
                        return `<div class="fw-semibold text-dark">${escapeHtml(data || row.cust_id || '-')}</div><small class="text-muted font-monospace">${escapeHtml((row.cust_id || '-') + kontak)}</small>`;
                    }
                },
                { data: 'total_invoice', className: 'text-center' },
                { data: 'total_nominal', className: 'text-end', render: moneyRender },
                { data: null, className: 'text-end', render: (_, type, row) => statusRender(row.invoice_belum, row.nominal_belum, type) },
                { data: null, className: 'text-end', render: (_, type, row) => statusRender(row.invoice_cicil, row.nominal_cicil, type) },
                { data: null, className: 'text-end', render: (_, type, row) => statusRender(row.invoice_lunas, row.nominal_lunas, type) },
                { data: 'total_bayar', className: 'text-end', render: moneyRender },
                { data: 'sisa_piutang', className: 'text-end text-danger fw-bold', render: moneyRender },
                { data: 'avg_durasi_lunas_hari', className: 'text-center', render: durationRender }
            ]
        });
    }

    function loadReport() {
        updateStoreInfo();
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lappiutang/report') ?>',
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
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan piutang customer'));
            }
        });
    }

    function renderReport(report) {
        const summary = report.summary || {};
        const periodText = `${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`;
        $('#period-label').text(`Periode: ${periodText}`);
        $('#selected-store-info').text(`Toko: ${report?.toko?.toko_id || selectedStore()} - ${report?.toko?.toko_nama || selectedStore()}`);
        $('#summary-customer').text(Number(summary.customer_count || 0).toLocaleString('id-ID'));
        $('#summary-invoice').text(Number(summary.total_invoice || 0).toLocaleString('id-ID'));
        $('#summary-nominal').text(rp(summary.total_nominal || 0));
        $('#summary-sisa').text(rp(summary.sisa_piutang || 0));
        $('#summary-belum').text(`${Number(summary.invoice_belum || 0).toLocaleString('id-ID')} inv / ${rp(summary.nominal_belum || 0)}`);
        $('#summary-cicil').text(`${Number(summary.invoice_cicil || 0).toLocaleString('id-ID')} inv / ${rp(summary.nominal_cicil || 0)}`);
        $('#summary-lunas').text(`${Number(summary.invoice_lunas || 0).toLocaleString('id-ID')} inv / ${rp(summary.nominal_lunas || 0)}`);
        $('#summary-durasi').text(durationLabel(summary.avg_durasi_lunas_hari || 0));
        table.clear().rows.add(report.rows || []).draw();
    }

    function statusRender(invoice, nominal, type) {
        const invoiceText = Number(invoice || 0).toLocaleString('id-ID');
        const nominalText = formatMoneyValue(nominal || 0);
        if (type === 'export' || type === 'sort') {
            return `${invoiceText} invoice / Rp ${nominalText}`;
        }
        return `<div class="fw-semibold">${invoiceText} inv</div><small class="text-muted">Rp ${nominalText}</small>`;
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
