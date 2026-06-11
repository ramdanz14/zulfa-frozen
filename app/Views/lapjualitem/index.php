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
                        <h4 class="fw-semibold mb-2">Laporan Penjualan Per Item</h4>
                        <p class="mb-0"><span id="period-label">Periode aktif</span> | Total qty, HPP, gross, diskon, margin, dan basket metric per item.</p>
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
                        <label class="form-label">Range Tanggal Transaksi</label>
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
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total Struk</div><div class="fs-6 fw-semibold mt-2" id="summary-struk">0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Jenis Item Terjual</div><div class="fs-6 fw-semibold mt-2" id="summary-item">0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total Qty Stock</div><div class="fs-6 fw-semibold mt-2" id="summary-qty">0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total Margin</div><div class="fs-6 fw-semibold mt-2 text-success" id="summary-margin">Rp 0</div></div></div></div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total HPP</div><div class="fs-6 fw-semibold mt-2" id="summary-hpp">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total Gross</div><div class="fs-6 fw-semibold mt-2" id="summary-gross">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Total Diskon</div><div class="fs-6 fw-semibold mt-2 text-danger" id="summary-diskon">Rp 0</div></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="card h-100 mb-0"><div class="card-body"><div class="text-muted small">Avg Attach Rate / Qty Struk</div><div class="fs-6 fw-semibold mt-2 text-primary" id="summary-basket">0% / 0</div></div></div></div>
        </div>

        <div class="card">
            <div class="card-body p-2">
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
                            <th>Qty per Struk</th>
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
            columns: [{
                    data: 'nama_item',
                    render: function(data, type, row) {
                        if (type === 'export' || type === 'sort') {
                            return data || row.kode_item || '-';
                        }
                        return `<div class="fw-semibold">${escapeHtml(data || row.kode_item || '-')}</div><small class="text-muted">${escapeHtml(row.kode_item || '-')}</small>`;
                    }
                },
                { data: 'kat_id', className: 'text-center', render: data => escapeHtml(data || '-') },
                { data: 'jumlah_struk_item', className: 'text-center', render: numRender },
                { data: 'total_qty', className: 'text-end', render: qtyRender },
                { data: 'total_hpp', className: 'text-end', render: moneyRender },
                { data: 'total_gross', className: 'text-end', render: moneyRender },
                { data: 'total_diskon', className: 'text-end text-danger', render: moneyRender },
                { data: 'total_margin', className: 'text-end text-success', render: moneyRender },
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
        $('#selected-store-info').text(`Toko aktif: ${report?.toko?.toko_id || selectedStore()} - ${report?.toko?.toko_nama || selectedStore()}`);
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
