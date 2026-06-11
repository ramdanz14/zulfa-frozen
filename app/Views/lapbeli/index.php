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
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Laporan Pembelian Supplier</h4>
                        <p class="mb-0"><span id="period-label">Periode aktif</span> | Rekap pembelian TERIMA per supplier.</p>
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
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Supplier</div><div class="fs-6 fw-semibold mt-2" id="summary-supplier">0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total Invoice</div><div class="fs-6 fw-semibold mt-2" id="summary-invoice">0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total Pembelian</div><div class="fs-6 fw-semibold mt-2" id="summary-nominal">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Jenis Item Supplier</div><div class="fs-6 fw-semibold mt-2" id="summary-item">0</div></div></div></div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Kredit</div><div class="fs-6 fw-semibold mt-2 text-danger" id="summary-kredit">0 invoice / Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Tidak Kredit</div><div class="fs-6 fw-semibold mt-2 text-success" id="summary-non-kredit">0 invoice / Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Frekuensi Kiriman</div><div class="fs-6 fw-semibold mt-2" id="summary-frekuensi">0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Rata-rata Jarak Kirim</div><div class="fs-6 fw-semibold mt-2 text-primary" id="summary-jarak">-</div></div></div></div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Total Invoice</th>
                            <th>Total Pembelian</th>
                            <th>Kredit</th>
                            <th>Tidak Kredit</th>
                            <th>Jenis Item</th>
                            <th>Frekuensi Datang</th>
                            <th>Kiriman Pertama</th>
                            <th>Kiriman Terakhir</th>
                            <th>Rata-rata Jarak Kirim</th>
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
                        title: 'Laporan-Pembelian-Supplier',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export'
                        }
                    }, {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        title: 'Laporan Pembelian Supplier',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }, {
                        extend: 'print',
                        text: 'Print',
                        title: 'Laporan Pembelian Supplier'
                    }, 'pageLength']
                }
            },
            data: [],
            ordering: true,
            order: [[2, 'desc']],
            responsive: true,
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], ['25 rows', '50 rows', '100 rows', 'Show all']],
            columns: [{
                    data: 'supplier_nama',
                    render: function(data, type, row) {
                        if (type === 'export' || type === 'sort') {
                            return data || row.supco || '-';
                        }
                        return `<div class="fw-semibold">${escapeHtml(data || row.supco || '-')}</div><small class="text-muted">${escapeHtml(row.supco || '-')}</small>`;
                    }
                },
                { data: 'total_invoice', className: 'text-center' },
                { data: 'total_nominal', className: 'text-end', render: moneyRender },
                { data: null, className: 'text-end', render: (_, type, row) => bucketRender(row.invoice_kredit, row.nominal_kredit, type) },
                { data: null, className: 'text-end', render: (_, type, row) => bucketRender(row.invoice_non_kredit, row.nominal_non_kredit, type) },
                { data: 'total_jenis_item', className: 'text-center' },
                { data: 'total_frekuensi_datang', className: 'text-center' },
                { data: 'kiriman_pertama', className: 'text-center', render: dateRender },
                { data: 'kiriman_terakhir', className: 'text-center', render: dateRender },
                { data: 'rata_rata_jarak_kirim_hari', className: 'text-center', render: distanceRender }
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
        $('#selected-store-info').text(`Toko aktif: ${report?.toko?.toko_id || selectedStore()} - ${report?.toko?.toko_nama || selectedStore()}`);
        $('#summary-supplier').text(num(summary.supplier_count || 0));
        $('#summary-invoice').text(num(summary.total_invoice || 0));
        $('#summary-nominal').text(rp(summary.total_nominal || 0));
        $('#summary-item').text(num(summary.total_jenis_item || 0));
        $('#summary-kredit').text(`${num(summary.invoice_kredit || 0)} invoice / ${rp(summary.nominal_kredit || 0)}`);
        $('#summary-non-kredit').text(`${num(summary.invoice_non_kredit || 0)} invoice / ${rp(summary.nominal_non_kredit || 0)}`);
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
        return weeks >= 1 ? `${roundedDays} hari (${weeks} minggu)` : `${roundedDays} hari`;
    }

    function rp(value) {
        return 'Rp ' + formatMoneyValue(value || 0);
    }

    function num(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }
</script>
<?= $this->endSection('javascript') ?>
