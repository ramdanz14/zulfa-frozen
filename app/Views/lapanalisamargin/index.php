<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<style>
    .metric-card-margin {
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-touch-target {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
    }
    @media (max-width: 767.98px) {
        .metric-title {
            font-size: 0.78rem;
            line-height: 1.2;
        }
        .metric-value {
            font-size: 1.15rem !important;
            word-break: break-word;
        }
    }
    .category-margin-card {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .category-margin-card .field-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        font-size: 0.85rem;
    }
    .category-margin-card .field-row-total {
        border-top: 1px dashed rgba(0,0,0,0.12);
        margin-top: 6px;
        padding-top: 6px;
        font-weight: 600;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-md-7">
                        <h4 class="fw-semibold mb-1">Laporan Analisa Margin</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle">Periode aktif</span> | Analisa gross sales, HPP, & margin per kategori produk.</p>
                    </div>
                    <div class="col-12 col-md-5 text-md-end mt-2 mt-md-0">
                        <div id="selected-store-info" class="badge bg-primary text-wrap text-start text-md-end" style="font-weight: 500;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body p-3 p-md-4">
                <div class="row g-2 g-md-3 align-items-end">
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="form-label small fw-medium mb-1">Range Tanggal Transaksi</label>
                        <input type="text" class="form-control" id="filter-range" readonly>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-5" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label small fw-medium mb-1">Filter Toko</label>
                        <select class="form-select select2" id="filter-toko" multiple>
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>"><?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-lg-<?= !empty($tokoOptions) ? '3' : '8' ?>">
                        <div class="row g-2">
                            <div class="col-6 col-sm-6">
                                <button type="button" class="btn btn-primary w-100 btn-touch-target" id="btn-filter">
                                    <i class="ti ti-filter me-1"></i> Filter
                                </button>
                            </div>
                            <div class="col-6 col-sm-6">
                                <button type="button" class="btn btn-light border w-100 btn-touch-target" id="btn-reset">
                                    <i class="ti ti-rotate me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Grid: 2x2 di Mobile, 4 kolom di Desktop -->
        <div class="row g-2 g-md-3 mb-3">
            <!-- 1. Total Gross Sales -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-primary border-start border-3 metric-card-margin">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Gross Sales</div>
                        <div class="fs-6 fw-bold mt-1 text-primary metric-value" id="summary-sales">Rp 0</div>
                        <div class="text-muted small mt-1">Penjualan kotor</div>
                    </div>
                </div>
            </div>
            <!-- 2. Total Sales HPP -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-danger border-start border-3 metric-card-margin">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Sales HPP</div>
                        <div class="fs-6 fw-bold mt-1 text-danger metric-value" id="summary-hpp">Rp 0</div>
                        <div class="text-muted small mt-1">Modal pokok</div>
                    </div>
                </div>
            </div>
            <!-- 3. Total Margin -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-success border-start border-3 metric-card-margin">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Total Margin</div>
                        <div class="fs-6 fw-bold mt-1 text-success metric-value" id="summary-margin">Rp 0</div>
                        <div class="text-muted small mt-1">Keuntungan kotor</div>
                    </div>
                </div>
            </div>
            <!-- 4. Gross Margin % -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-info border-start border-3 metric-card-margin">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Gross Margin %</div>
                        <div class="fs-6 fw-bold mt-1 text-info metric-value" id="summary-margin-percent">0%</div>
                        <div class="text-muted small mt-1">Persentase margin</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table / CardView Data -->
        <div class="card mb-3">
            <div class="card-body p-2 p-md-3">
                <table id="table-category" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead></thead>
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

<!-- Template CardView Mobile untuk Kategori Margin -->
<template id="card-category-template">
    <div class="card category-margin-card shadow-sm mb-2 border-start border-3 border-primary">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <div class="fw-bold fs-5 text-primary text-truncate" data-dtcv-field="0"></div>
                <div class="badge bg-info-subtle text-info fs-6 fw-bold" data-dtcv-field="6"></div>
            </div>
            <div class="field-row">
                <span class="text-muted">Jumlah Struk</span>
                <span class="fw-medium" data-dtcv-field="1"></span>
            </div>
            <div class="field-row">
                <span class="text-muted">Total Qty</span>
                <span class="fw-medium" data-dtcv-field="2"></span>
            </div>
            <div class="field-row">
                <span class="text-muted">Gross Sales</span>
                <span class="fw-bold" data-dtcv-field="3"></span>
            </div>
            <div class="field-row">
                <span class="text-muted">Sales HPP</span>
                <span class="fw-medium text-danger" data-dtcv-field="4"></span>
            </div>
            <div class="field-row field-row-total">
                <span class="text-dark">Total Margin</span>
                <span class="fw-bold text-success fs-6" data-dtcv-field="5"></span>
            </div>
        </div>
    </div>
</template>

<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const canMultiStore = akses_menu?.akses_delete === 'Y';
    const sessionTokoId = '<?= esc((string) session('toko_id')) ?>';
    let filterStart = moment().startOf('month');
    let filterEnd = moment().endOf('month');

    $(function() {
        if (canMultiStore) {
            $('#filter-toko-wrapper').show();
            $('#filter-toko').select2({
                width: '100%',
                placeholder: 'Pilih satu atau banyak toko'
            });
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
                '1 Minggu': [moment().subtract(6, 'days'), moment()],
                '1 Bulan': [moment().subtract(1, 'month').add(1, 'days'), moment()],
                'Minggu Ini': [moment().startOf('week'), moment().endOf('week')],
                'Minggu Lalu': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            filterStart = start;
            filterEnd = end;
        });

        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        updateStoreInfo();
        refreshReport();
    });

    function getSelectedStoreIds() {
        if (!canMultiStore) {
            return [sessionTokoId];
        }
        return ($('#filter-toko').val() || []).filter(Boolean);
    }

    function updateStoreInfo() {
        const selected = getSelectedStoreIds();
        if (!canMultiStore) {
            $('#selected-store-info').text(`Toko aktif: ${sessionTokoId}`);
            return;
        }
        if (!selected.length) {
            $('#selected-store-info').text('Toko: semua toko');
            return;
        }
        $('#selected-store-info').text(`Toko dipilih: ${selected.join(', ')}`);
    }

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const categoryTable = $('#table-category').DataTable({
        layout: {
            topStart: {
                buttons: ['cardViewToggle', 'pageLength']
            }
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            columns: {
                xs: 1,
                sm: 1,
                md: 2,
                lg: 3
            },
            template: '#card-category-template'
        },
        data: [],
        lengthMenu: [
            [25, 50, 100, -1],
            ['25 rows', '50 rows', '100 rows', 'Show all']
        ],
        pageLength: 25,
        responsive: true,
        autoWidth: false,
        order: [
            [6, 'asc']
        ],
        columns: [{
                data: 'kat_id',
                title: 'Kategori',
                render: function(data, type) {
                    if (type !== 'display') {
                        return data || '';
                    }
                    return `<a class="fw-semibold" href="${buildDetailUrl(data || '')}">${escapeHtml(data || '-')}</a>`;
                }
            },
            {
                data: 'jumlah_transaksi',
                title: 'Jml Struk',
                className: 'text-end',
                render: function(data, type) {
                    if (type !== 'display') {
                        return Number(data || 0);
                    }
                    return Number(data || 0).toLocaleString('id-ID');
                }
            },
            {
                data: 'total_qty',
                title: 'Total Qty',
                className: 'text-end',
                render: function(data, type) {
                    if (type !== 'display') {
                        return Number(data || 0);
                    }
                    return formatQty(data);
                }
            },
            {
                data: 'total_gross_sales',
                title: 'Gross Sales',
                className: 'text-end',
                render: function(data, type) {
                    if (type !== 'display') {
                        return Number(data || 0);
                    }
                    return 'Rp ' + formatMoneyValue(data);
                }
            },
            {
                data: 'total_sales_hpp',
                title: 'Sales HPP',
                className: 'text-end',
                render: function(data, type) {
                    if (type !== 'display') {
                        return Number(data || 0);
                    }
                    return 'Rp ' + formatMoneyValue(data);
                }
            },
            {
                data: 'total_margin',
                title: 'Margin',
                className: 'text-end',
                render: function(data, type) {
                    if (type !== 'display') {
                        return Number(data || 0);
                    }
                    return 'Rp ' + formatMoneyValue(data);
                }
            },
            {
                data: 'gross_margin_percent',
                title: 'Gross Margin %',
                className: 'text-end',
                render: function(data, type) {
                    if (type !== 'display') {
                        return Number(data || 0);
                    }
                    return formatPercent(data);
                }
            }
        ]
    });

    $('#btn-filter').on('click', function() {
        updateStoreInfo();
        refreshReport();
    });

    $('#btn-reset').on('click', function() {
        filterStart = moment().startOf('month');
        filterEnd = moment().endOf('month');
        $('#filter-range').data('daterangepicker').setStartDate(filterStart);
        $('#filter-range').data('daterangepicker').setEndDate(filterEnd);
        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        if (canMultiStore) {
            $('#filter-toko').val(null).trigger('change');
        }
        updateStoreInfo();
        refreshReport();
    });

    if (canMultiStore) {
        $('#filter-toko').on('change', updateStoreInfo);
    }

    function refreshReport() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lapanalisamargin/report') ?>',
            dataType: 'json',
            data: buildFilterPayload(),
            success: function(res) {
                const data = res?.data || {};
                const summary = data.summary || {};
                $('#summary-sales').text(`Rp ${formatMoneyValue(summary.total_gross_sales || 0)}`);
                $('#summary-hpp').text(`Rp ${formatMoneyValue(summary.total_sales_hpp || 0)}`);
                $('#summary-margin').text(`Rp ${formatMoneyValue(summary.total_margin || 0)}`);
                $('#summary-margin-percent').text(formatPercent(summary.gross_margin_percent || 0));

                const rows = data.categories || [];
                categoryTable.clear().rows.add(rows).draw();
                $('.page-pretitle').text(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')} | ${rows.length} kategori`);
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan analisa margin'));
            }
        });
    }

    function buildFilterPayload() {
        return {
            date_start: filterStart.format('YYYY-MM-DD'),
            date_end: filterEnd.format('YYYY-MM-DD'),
            toko_ids: getSelectedStoreIds()
        };
    }

    function buildDetailUrl(katId) {
        const params = new URLSearchParams();
        params.set('kat_id', katId || '');
        params.set('date_start', filterStart.format('YYYY-MM-DD'));
        params.set('date_end', filterEnd.format('YYYY-MM-DD'));
        getSelectedStoreIds().forEach((storeId) => params.append('toko_ids[]', storeId));
        return `<?= base_url('/lapanalisamargin/detail') ?>?${params.toString()}`;
    }

    function formatQty(value) {
        return Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function formatPercent(value) {
        return `${Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}%`;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [char];
        });
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/`/g, '&#096;');
    }
</script>
<?= $this->endSection('javascript') ?>
