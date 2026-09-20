<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<style>
    /* Compact, ergonomic styles for Laporan Penjualan Per Tanggal */
    .metric-card-summary {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 0.75rem 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card-summary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .metric-card-summary .metric-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .metric-card-summary .metric-value {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.2;
    }

    /* Mobile CardView Styling for LapJual */
    .lapjual-date-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .lapjual-date-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 10px rgba(0,0,0,0.04);
    }

    /* Collapsible Chart Toolbar for mobile viewport */
    .chart-collapse-toggle {
        cursor: pointer;
        user-select: none;
    }

    @media (max-width: 767.98px) {
        .metric-card-summary {
            padding: 0.6rem 0.75rem;
        }
        .metric-card-summary .metric-value {
            font-size: 1rem;
        }
        .metric-card-summary .metric-title {
            font-size: 0.7rem;
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
        <!-- Page Title & Store Header -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Laporan Penjualan Per Tanggal</h4>
                        <p class="mb-0 small text-muted"><span class="page-pretitle">Periode aktif</span> &bull; Analisis customer, transaksi, omset, dan margin.</p>
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
                        <select class="form-select select2" id="filter-toko" multiple>
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>"><?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-<?= !empty($tokoOptions) ? '3' : '7' ?> d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm flex-fill btn-filter-touch" id="btn-filter">
                            <i class="ti ti-search me-1"></i> Terapkan
                        </button>
                        <button type="button" class="btn btn-light btn-sm flex-fill btn-filter-touch border" id="btn-reset">
                            <i class="ti ti-refresh me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compact KPI Metric Cards (2x2 di Mobile, 1x4 di Desktop) -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-users text-info me-1"></i>Customer</div>
                    <div class="metric-value text-dark" id="summary-customer">0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-receipt text-primary me-1"></i>Transaksi</div>
                    <div class="metric-value text-dark" id="summary-transaksi">0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-wallet text-success me-1"></i>Total Omset</div>
                    <div class="metric-value text-success" id="summary-omset">Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-chart-line text-warning me-1"></i>Margin Bruto</div>
                    <div class="metric-value text-primary" id="summary-margin">Rp 0</div>
                </div>
            </div>
        </div>

        <!-- Collapsible Graphic Section (Default Collapse di Mobile untuk Menghemat Layar) -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between chart-collapse-toggle" data-bs-toggle="collapse" data-bs-target="#collapseAnalyticsCharts">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-chart-bar fs-5 text-primary"></i>
                    <span class="fw-bold text-dark small text-uppercase">Grafik Trend Penjualan & Margin</span>
                </div>
                <div class="small text-muted d-flex align-items-center gap-1">
                    <span class="d-none d-sm-inline">Tampilkan / Sembunyikan</span>
                    <i class="ti ti-chevron-down"></i>
                </div>
            </div>
            <div class="collapse show" id="collapseAnalyticsCharts">
                <div class="card-body p-2 p-md-3">
                    <div class="row g-3">
                        <div class="<?= !empty($tokoOptions) ? 'col-lg-8' : 'col-12' ?>">
                            <div class="fw-semibold small text-muted mb-2">Trend Omset Harian</div>
                            <div id="chart-penjualan" style="min-height: 280px;"></div>
                        </div>
                        <?php if (!empty($tokoOptions)) : ?>
                            <div class="col-lg-4">
                                <div class="fw-semibold small text-muted mb-2">Trend Margin Harian</div>
                                <div id="chart-margin" style="min-height: 280px;"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table / Mobile CardView -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-list-details fs-5 text-primary"></i>
                    <h6 class="mb-0 fw-bold">Rincian Penjualan Harian</h6>
                </div>
            </div>
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td>Memuat data...</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template for Mobile (< 768px) -->
                <template id="card-lapjual-template">
                    <div class="lapjual-date-card mb-2">
                        <!-- Baris 1: Tanggal Transaksi & Toko -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-1">
                                <i class="ti ti-calendar text-primary"></i>
                                <span class="fw-bold text-dark fs-6" data-dtcv-field="0"></span>
                            </div>
                            <span class="badge bg-light text-secondary border small px-2 py-1" data-dtcv-field="1"></span>
                        </div>

                        <!-- Baris 2: Omset & Margin Bruto (Key Metrics) -->
                        <div class="d-flex justify-content-between align-items-center my-2 p-2 bg-light-subtle rounded border">
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase;">Omset Penjualan</small>
                                <span class="fw-bold text-success fs-6" data-dtcv-field="5"></span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase;">Margin Bruto</small>
                                <span class="fw-bold text-primary fs-6" data-dtcv-field="6"></span>
                            </div>
                        </div>

                        <!-- Baris 3: Info Ringkas Volume: Customer, Transaksi, Total Qty -->
                        <div class="d-flex justify-content-between align-items-center pt-1 text-muted small" style="font-size: 0.78rem;">
                            <div>
                                <span><i class="ti ti-users me-1"></i><span data-dtcv-field="2"></span> cust</span>
                                <span class="mx-1">&bull;</span>
                                <span><i class="ti ti-receipt me-1"></i><span data-dtcv-field="3"></span> trx</span>
                            </div>
                            <div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="ti ti-package me-1"></i><span data-dtcv-field="4"></span> Qty
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const canMultiStore = akses_menu?.akses_delete === 'Y';
    const sessionTokoId = '<?= esc((string) session('toko_id')) ?>';
    let filterStart = moment().startOf('month');
    let filterEnd = moment().endOf('month');
    let chartPenjualan = null;
    let chartMargin = null;

    $(function() {
        // Otomatis collapse grafik di HP (< 768px) agar layar langsung memperlihatkan data ringkas
        if ($(window).width() < 768) {
            $('#collapseAnalyticsCharts').removeClass('show');
        }

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
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
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
        initCharts();
        refreshSummary();
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
            $('#selected-store-info').text(`Toko: ${sessionTokoId}`);
            return;
        }
        if (!selected.length) {
            $('#selected-store-info').text('Toko: Semua toko');
            return;
        }
        $('#selected-store-info').text(`Toko: ${selected.join(', ')}`);
    }

    function initCharts() {
        chartPenjualan = new ApexCharts(document.querySelector('#chart-penjualan'), {
            chart: {
                type: 'line',
                height: 280,
                toolbar: {
                    show: false
                }
            },
            series: [],
            xaxis: {
                categories: [],
                tooltip: {
                    enabled: false
                }
            },
            markers: {
                size: 3,
                hover: {
                    size: 5
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            legend: {
                position: 'bottom'
            },
            yaxis: {
                labels: {
                    formatter: function(value) {
                        return formatCompactRupiah(value || 0);
                    }
                }
            },
            tooltip: {
                shared: false,
                intersect: true,
                x: {
                    formatter: function(value, { dataPointIndex, w }) {
                        const rawDate = w.config.series?.[0]?.metaDates?.[dataPointIndex] || value;
                        return formatTooltipDate(rawDate);
                    }
                },
                y: {
                    formatter: function(value, { seriesIndex, w }) {
                        const tokoId = w.config.series?.[seriesIndex]?.name || '-';
                        return `${tokoId} : Rp ${formatMoneyValue(value || 0)}`;
                    }
                }
            },
            noData: {
                text: 'Belum ada data'
            }
        });
        chartPenjualan.render();

        if (canMultiStore && document.querySelector('#chart-margin')) {
            chartMargin = new ApexCharts(document.querySelector('#chart-margin'), {
                chart: {
                    type: 'line',
                    height: 280,
                    toolbar: {
                        show: false
                    }
                },
                series: [],
                xaxis: {
                    categories: [],
                    tooltip: {
                        enabled: false
                    }
                },
                markers: {
                    size: 3,
                    hover: {
                        size: 5
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                legend: {
                    position: 'bottom'
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return formatCompactRupiah(value || 0);
                        }
                    }
                },
                tooltip: {
                    shared: false,
                    intersect: true,
                    x: {
                        formatter: function(value, { dataPointIndex, w }) {
                            const rawDate = w.config.series?.[0]?.metaDates?.[dataPointIndex] || value;
                            return formatTooltipDate(rawDate);
                        }
                    },
                    y: {
                        formatter: function(value, { seriesIndex, w }) {
                            const tokoId = w.config.series?.[seriesIndex]?.name || '-';
                            return `${tokoId} : Rp ${formatMoneyValue(value || 0)}`;
                        }
                    }
                },
                noData: {
                    text: 'Belum ada data'
                }
            });
            chartMargin.render();
        }
    }

    function formatCompactRupiah(value) {
        const amount = Number(value || 0);
        const abs = Math.abs(amount);
        if (abs >= 1000000000) {
            return `Rp${trimCompactNumber(amount / 1000000000)}M`;
        }
        if (abs >= 1000000) {
            return `Rp${trimCompactNumber(amount / 1000000)}jt`;
        }
        if (abs >= 1000) {
            return `Rp${trimCompactNumber(amount / 1000)}rb`;
        }
        return `Rp${Math.round(amount).toLocaleString('id-ID')}`;
    }

    function trimCompactNumber(value) {
        return Number(value).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 1
        }).replace(',0', '');
    }

    function formatTooltipDate(rawDate) {
        if (!rawDate) return '-';
        return moment(rawDate, 'YYYY-MM-DD').format('DD/MM/YYYY');
    }

    function buildSeriesByStore(rows, valueKey) {
        const rawRows = Array.isArray(rows) ? rows : [];
        const uniqueDates = [...new Set(rawRows.map(row => String(row.tanggal || '')).filter(Boolean))];
        const sortedDates = uniqueDates.sort();
        const categories = sortedDates.map(date => moment(date, 'YYYY-MM-DD').format('DD'));
        const seriesMap = {};

        rawRows.forEach((row) => {
            const tokoId = String(row.toko_id || '-');
            const tanggal = String(row.tanggal || '');
            if (!tanggal) return;
            if (!seriesMap[tokoId]) {
                seriesMap[tokoId] = {};
            }
            seriesMap[tokoId][tanggal] = Number(row[valueKey] || 0);
        });

        const series = Object.keys(seriesMap).sort().map((tokoId) => ({
            name: tokoId,
            data: sortedDates.map(date => seriesMap[tokoId][date] ?? null),
            metaDates: sortedDates
        }));

        return {
            categories,
            series,
            dates: sortedDates
        };
    }

    function updateChartInstance(chart, rows, valueKey) {
        if (!chart) return;
        const chartData = buildSeriesByStore(rows, valueKey);
        const hasData = chartData.series.some(item => item.data.some(point => point !== null));

        chart.updateOptions({
            noData: {
                text: hasData ? '' : 'Belum ada data'
            },
            xaxis: {
                categories: chartData.categories,
                tooltip: {
                    enabled: false
                }
            }
        }, false, false);
        chart.updateSeries(hasData ? chartData.series : [], true);
    }

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary btn-sm';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-Penjualan-Per-Tanggal',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    }
                }, 'pageLength']
            }
        },
        lengthMenu: [
            [25, 50, 100, -1],
            ['25 rows', '50 rows', '100 rows', 'Show all']
        ],
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/lapjual/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.date_start = filterStart.format('YYYY-MM-DD');
                d.date_end = filterEnd.format('YYYY-MM-DD');
                d.toko_ids = getSelectedStoreIds();
            }
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-lapjual-template',
            gridClass: 'col-12 col-sm-6 mb-2'
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(`${data}T00:00:00`).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'daftar_toko',
                title: 'Toko',
                render: data => data || '-'
            },
            {
                data: 'jumlah_customer',
                title: 'Customer',
                className: 'text-center'
            },
            {
                data: 'jumlah_transaksi',
                title: 'Transaksi',
                className: 'text-center'
            },
            {
                data: 'total_qty',
                title: 'Qty Terjual',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'omset',
                title: 'Omset',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'margin_bruto',
                title: 'Margin Bruto',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')} | ${json?.recordsFiltered || 0} baris`);
    });

    $('#btn-filter').on('click', function() {
        updateStoreInfo();
        table.ajax.reload();
        refreshSummary();
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
        table.ajax.reload();
        refreshSummary();
    });

    if (canMultiStore) {
        $('#filter-toko').on('change', function() {
            updateStoreInfo();
        });
    }

    function refreshSummary() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lapjual/summary') ?>',
            dataType: 'json',
            data: {
                date_start: filterStart.format('YYYY-MM-DD'),
                date_end: filterEnd.format('YYYY-MM-DD'),
                toko_ids: getSelectedStoreIds()
            },
            success: function(res) {
                const data = res?.data || {};
                $('#summary-customer').text(Number(data.total_customer || 0).toLocaleString('id-ID'));
                $('#summary-transaksi').text(Number(data.total_transaksi || 0).toLocaleString('id-ID'));
                $('#summary-omset').text(`Rp ${formatMoneyValue(data.total_omset || 0)}`);
                $('#summary-margin').text(`Rp ${formatMoneyValue(data.total_margin || 0)}`);
                updateChartInstance(chartPenjualan, data.daily_omset_by_store || [], 'omset');
                if (chartMargin) {
                    updateChartInstance(chartMargin, data.daily_margin_by_store || [], 'margin_bruto');
                }
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat ringkasan laporan penjualan'));
            }
        });
    }
</script>
<?= $this->endSection('javascript') ?>
