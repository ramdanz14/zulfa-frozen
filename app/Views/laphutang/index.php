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
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Laporan Hutang Supplier</h4>
                        <p class="mb-0"><span id="period-label">Periode aktif</span> | Rekap pembelian kredit TERIMA per supplier.</p>
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
                    <div class="col-lg-4">
                        <label class="form-label">Range Tanggal Faktur</label>
                        <input type="text" class="form-control" id="filter-range" readonly>
                    </div>
                    <div class="col-lg-4" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label">Filter Toko</label>
                        <select class="form-select select2" id="filter-toko">
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>" <?= (string) ($row['toko_id'] ?? '') === (string) session('toko_id') ? 'selected' : '' ?>>
                                    <?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-<?= !empty($tokoOptions) ? '4' : '8' ?> d-grid d-lg-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btn-filter"><i class="ti ti-search"></i> Tampilkan</button>
                        <button type="button" class="btn btn-light w-100" id="btn-reset">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Supplier</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-supplier">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Total Invoice</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-invoice">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Total Nominal</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-nominal">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Sisa Hutang</div>
                        <div class="fs-6 fw-semibold mt-2 text-danger" id="summary-sisa">Rp 0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">BELUM</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-belum">0 invoice / Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">CICIL</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-cicil">0 invoice / Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">LUNAS</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-lunas">0 invoice / Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Rata-rata Pelunasan</div>
                        <div class="fs-6 fw-semibold mt-2 text-primary" id="summary-durasi">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Total Invoice</th>
                            <th>Total Nominal</th>
                            <th>BELUM</th>
                            <th>CICIL</th>
                            <th>LUNAS</th>
                            <th>Total Bayar</th>
                            <th>Sisa Hutang</th>
                            <th>Rata-rata Pelunasan</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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
        $('#selected-store-info').text(`Toko aktif: ${selectedStore()}`);
    }

    function initTable() {
        DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
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
                        text: 'PDF',
                        title: 'Laporan Hutang Supplier',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }, {
                        extend: 'print',
                        text: 'Print',
                        title: 'Laporan Hutang Supplier'
                    }, 'pageLength']
                }
            },
            data: [],
            ordering: true,
            order: [
                [7, 'desc']
            ],
            responsive: true,
            pageLength: 25,
            lengthMenu: [
                [25, 50, 100, -1],
                ['25 rows', '50 rows', '100 rows', 'Show all']
            ],
            columns: [{
                    data: 'supplier_nama',
                    render: function(data, type, row) {
                        if (type === 'export' || type === 'sort') {
                            return data || row.supco || '-';
                        }
                        return `<div class="fw-semibold">${escapeHtml(data || row.supco || '-')}</div><small class="text-muted">${escapeHtml(row.supco || '-')}</small>`;
                    }
                },
                {
                    data: 'total_invoice',
                    className: 'text-center'
                },
                {
                    data: 'total_nominal',
                    className: 'text-end',
                    render: moneyRender
                },
                {
                    data: null,
                    className: 'text-end',
                    render: (_, type, row) => statusRender(row.invoice_belum, row.nominal_belum, type)
                },
                {
                    data: null,
                    className: 'text-end',
                    render: (_, type, row) => statusRender(row.invoice_cicil, row.nominal_cicil, type)
                },
                {
                    data: null,
                    className: 'text-end',
                    render: (_, type, row) => statusRender(row.invoice_lunas, row.nominal_lunas, type)
                },
                {
                    data: 'total_bayar',
                    className: 'text-end',
                    render: moneyRender
                },
                {
                    data: 'sisa_hutang',
                    className: 'text-end text-danger',
                    render: moneyRender
                },
                {
                    data: 'avg_durasi_lunas_hari',
                    className: 'text-center',
                    render: durationRender
                }
            ]
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
        $('#selected-store-info').text(`Toko aktif: ${report?.toko?.toko_id || selectedStore()} - ${report?.toko?.toko_nama || selectedStore()}`);
        $('#summary-supplier').text(Number(summary.supplier_count || 0).toLocaleString('id-ID'));
        $('#summary-invoice').text(Number(summary.total_invoice || 0).toLocaleString('id-ID'));
        $('#summary-nominal').text(rp(summary.total_nominal || 0));
        $('#summary-sisa').text(rp(summary.sisa_hutang || 0));
        $('#summary-belum').text(`${Number(summary.invoice_belum || 0).toLocaleString('id-ID')} invoice / ${rp(summary.nominal_belum || 0)}`);
        $('#summary-cicil').text(`${Number(summary.invoice_cicil || 0).toLocaleString('id-ID')} invoice / ${rp(summary.nominal_cicil || 0)}`);
        $('#summary-lunas').text(`${Number(summary.invoice_lunas || 0).toLocaleString('id-ID')} invoice / ${rp(summary.nominal_lunas || 0)}`);
        $('#summary-durasi').text(durationLabel(summary.avg_durasi_lunas_hari || 0));
        table.clear().rows.add(report.rows || []).draw();
    }

    function statusRender(invoice, nominal, type) {
        const invoiceText = Number(invoice || 0).toLocaleString('id-ID');
        const nominalText = formatMoneyValue(nominal || 0);
        if (type === 'export' || type === 'sort') {
            return `Rp ${nominalText}/${invoiceText} invoice`;
        }
        return `<div class="fw-semibold">Rp ${nominalText}</div><small class="text-muted">${invoiceText} invoice</small>`;
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
            return `${roundedDays} hari (${weeks} minggu)`;
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