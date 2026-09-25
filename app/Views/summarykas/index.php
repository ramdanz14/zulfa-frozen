<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<style>
    /* Styling ergonomis untuk Summary Kas & Mobile Layout */
    .metric-card-summary {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 0.85rem 1rem;
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
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .metric-card-summary .metric-value {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.25;
    }

    /* CardView styling untuk item Summary Kas di mobile */
    .kas-summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .kas-summary-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 10px rgba(0,0,0,0.04);
    }

    /* Collapsible Chart Header */
    .chart-collapse-toggle {
        cursor: pointer;
        user-select: none;
    }

    @media (max-width: 767.98px) {
        .metric-card-summary {
            padding: 0.65rem 0.75rem;
        }
        .metric-card-summary .metric-value {
            font-size: 0.98rem;
        }
        .metric-card-summary .metric-title {
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
        <!-- Header Page -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Summary Kas</h4>
                        <p class="mb-0 small text-muted"><span class="page-pretitle">Periode aktif</span> &bull; Rekap arus kas masuk dan keluar per akun operasional.</p>
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

        <!-- Metric KPI Cards: 2 Kolom di Mobile, 4 Kolom di Desktop -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-cash text-success me-1"></i>Tunai Masuk</div>
                    <div class="metric-value text-success" id="summary-tunai-masuk">Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-cash-off text-danger me-1"></i>Tunai Keluar</div>
                    <div class="metric-value text-danger" id="summary-tunai-keluar">Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-credit-card text-success me-1"></i>Non-Tunai Masuk</div>
                    <div class="metric-value text-success" id="summary-nontunai-masuk">Rp 0</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-credit-card-off text-danger me-1"></i>Non-Tunai Keluar</div>
                    <div class="metric-value text-danger" id="summary-nontunai-keluar">Rp 0</div>
                </div>
            </div>
        </div>

        <!-- Sub Summary: Saldo Bersih & Total Transaksi -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-2 p-md-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border px-2 py-1 small">
                            <i class="ti ti-receipt me-1"></i><span id="summary-transaksi">0 transaksi</span>
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted text-uppercase fw-semibold">Saldo Bersih (Net):</span>
                        <span class="fw-bold fs-6" id="summary-saldo-bersih">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collapsible Graphic Section: Default Terbuka di Desktop, Bisa Diciutkan di Mobile -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between chart-collapse-toggle" data-bs-toggle="collapse" data-bs-target="#collapseAnalyticsCharts">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-chart-bar fs-5 text-primary"></i>
                    <span class="fw-bold text-dark small text-uppercase">Perbandingan Kas Per Akun</span>
                </div>
                <div class="small text-muted d-flex align-items-center gap-1">
                    <span class="d-none d-sm-inline">Tampilkan / Sembunyikan</span>
                    <i class="ti ti-chevron-down"></i>
                </div>
            </div>
            <div class="collapse show" id="collapseAnalyticsCharts">
                <div class="card-body p-2 p-md-3">
                    <div id="chart-kas" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>

        <!-- Data Table / Mobile CardView -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-list-details fs-5 text-primary"></i>
                    <h6 class="mb-0 fw-bold">Rincian Mutasi Kas Per Akun</h6>
                </div>
            </div>
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td>Memuat data...</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView template untuk layar mobile (< 768px) -->
                <template id="card-summarykas-template">
                    <div class="kas-summary-card mb-2">
                        <!-- Baris 1: Tanggal & Toko -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-1">
                                <i class="ti ti-calendar text-primary"></i>
                                <span class="fw-bold text-dark small" data-dtcv-field="0"></span>
                            </div>
                            <span class="badge bg-light text-secondary border small px-2 py-1" data-dtcv-field="1"></span>
                        </div>

                        <!-- Baris 2: Akun Kas & Jenis (MASUK/KELUAR) -->
                        <div class="d-flex justify-content-between align-items-start my-2">
                            <div class="pe-2" style="min-width: 0; flex: 1;">
                                <div class="fw-bold text-dark text-truncate" data-dtcv-field="3"></div>
                                <div class="text-muted small">
                                    <i class="ti ti-receipt me-1"></i><span data-dtcv-field="4"></span> trx
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="2"></div>
                        </div>

                        <!-- Baris 3: Breakdown Tunai & Non Tunai -->
                        <div class="p-2 bg-light-subtle rounded border my-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small" style="font-size: 0.75rem;"><i class="ti ti-cash me-1 text-success"></i>Tunai:</span>
                                <span class="fw-semibold text-dark small" data-dtcv-field="6"></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small" style="font-size: 0.75rem;"><i class="ti ti-credit-card me-1 text-primary"></i>Non Tunai:</span>
                                <span class="fw-semibold text-dark small" data-dtcv-field="7"></span>
                            </div>
                        </div>

                        <!-- Baris 4: Total Nominal -->
                        <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                            <span class="text-muted small text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Total Nominal</span>
                            <span class="fw-bold fs-6" data-dtcv-field="5"></span>
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
    const canMultiStore = akses_menu?.akses_update === 'Y';
    const sessionTokoId = '<?= esc((string) session('toko_id')) ?>';
    let filterStart = moment().startOf('month');
    let filterEnd = moment().endOf('month');
    let chartKas = null;

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
        initChart();
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
            $('#selected-store-info').text(`Toko aktif: ${sessionTokoId}`);
            return;
        }
        if (!selected.length) {
            $('#selected-store-info').text('Toko: semua toko');
            return;
        }
        $('#selected-store-info').text(`Toko dipilih: ${selected.join(', ')}`);
    }

    function initChart() {
        chartKas = new ApexCharts(document.querySelector('#chart-kas'), {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    columnWidth: '65%'
                }
            },
            series: [],
            xaxis: {
                labels: {
                    formatter: function(value) {
                        return formatCompactRupiah(value || 0);
                    }
                }
            },
            yaxis: {
                categories: [],
                labels: {
                    maxWidth: 160,
                    style: {
                        fontSize: '11px'
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function(value, {
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        const category = w.globals.labels?.[dataPointIndex] || '-';
                        const tokoName = w.config.series?.[seriesIndex]?.name || '-';
                        return `${tokoName}<br>${category}<br>Rp ${formatMoneyValue(value || 0)}`;
                    }
                }
            },
            noData: {
                text: 'Belum ada data'
            },
            responsive: [{
                breakpoint: 768,
                options: {
                    chart: {
                        height: 280
                    },
                    yaxis: {
                        labels: {
                            maxWidth: 120,
                            style: {
                                fontSize: '10px'
                            }
                        }
                    }
                }
            }]
        });
        chartKas.render();
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

    function buildChartSeries(rows) {
        const chartRows = Array.isArray(rows) ? rows : [];
        const categories = [...new Set(chartRows.map(row => `${row.jenis_akun} - ${row.nama_akun}`))];
        const stores = [...new Set(chartRows.map(row => String(row.toko_id || '-')))].sort();
        const series = stores.map((storeId) => ({
            name: storeId,
            data: categories.map((category) => {
                const found = chartRows.find(row => String(row.toko_id || '-') === storeId && `${row.jenis_akun} - ${row.nama_akun}` === category);
                return Number(found?.total_nominal || 0);
            })
        }));
        return {
            categories,
            series
        };
    }

    function refreshChart(rows) {
        const chartData = buildChartSeries(rows);
        const hasData = chartData.series.some(series => series.data.some(value => Number(value || 0) > 0));
        chartKas.updateOptions({
            xaxis: {
                categories: chartData.categories
            },
            noData: {
                text: hasData ? '' : 'Belum ada data'
            }
        }, false, false);
        chartKas.updateSeries(hasData ? chartData.series : [], true);
    }

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary btn-sm';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Summary-Kas-Operasional',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
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
            url: '<?= base_url('/summarykas/ajax') ?>',
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
            template: '#card-summarykas-template',
            gridClass: 'col-12 col-sm-6 mb-2',
            onCardRender: function($card, data) {
                const totalNominal = Number(data.total_nominal || 0);
                const $totalNominalEl = $card.find('[data-dtcv-field="5"]');
                if (data.jenis_akun === 'MASUK') {
                    $totalNominalEl.addClass('text-success');
                    $card.addClass('border-start border-success border-3');
                } else {
                    $totalNominalEl.addClass('text-danger');
                    $card.addClass('border-start border-danger border-3');
                }
            }
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? new Date(`${data}T00:00:00`).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'toko_nama',
                title: 'Toko',
                render: function(data, type, row) {
                    return `${row.toko_id || '-'}${data ? ' - ' + data : ''}`;
                }
            },
            {
                data: 'jenis_akun',
                title: 'Jenis',
                className: 'text-center',
                render: data => data === 'MASUK' ? '<span class="badge bg-success-subtle text-success">MASUK</span>' : '<span class="badge bg-danger-subtle text-danger">KELUAR</span>'
            },
            {
                data: 'nama_akun',
                title: 'Akun'
            },
            {
                data: 'total_transaksi',
                title: 'Jml Trx',
                className: 'text-center'
            },
            {
                data: 'total_nominal',
                title: 'Total Nominal',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: 'total_tunai',
                title: 'Total Tunai',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: 'total_nontunai',
                title: 'Total Non Tunai',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
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
            url: '<?= base_url('/summarykas/summary') ?>',
            dataType: 'json',
            data: {
                date_start: filterStart.format('YYYY-MM-DD'),
                date_end: filterEnd.format('YYYY-MM-DD'),
                toko_ids: getSelectedStoreIds()
            },
            success: function(res) {
                const data = res?.data || {};
                const summary = data.summary || {};
                $('#summary-tunai-masuk').text(`Rp ${formatMoneyValue(summary.total_tunai_masuk || 0)}`);
                $('#summary-tunai-keluar').text(`Rp ${formatMoneyValue(summary.total_tunai_keluar || 0)}`);
                $('#summary-nontunai-masuk').text(`Rp ${formatMoneyValue(summary.total_nontunai_masuk || 0)}`);
                $('#summary-nontunai-keluar').text(`Rp ${formatMoneyValue(summary.total_nontunai_keluar || 0)}`);
                $('#summary-transaksi').text(`${Number(summary.total_transaksi || 0).toLocaleString('id-ID')} transaksi`);

                const saldoBersih = Number(summary.saldo_bersih || 0);
                const $saldoBersihEl = $('#summary-saldo-bersih');
                $saldoBersihEl.text(`Rp ${formatMoneyValue(saldoBersih)}`);
                $saldoBersihEl.removeClass('text-success text-danger text-dark');
                if (saldoBersih > 0) {
                    $saldoBersihEl.addClass('text-success');
                } else if (saldoBersih < 0) {
                    $saldoBersihEl.addClass('text-danger');
                } else {
                    $saldoBersihEl.addClass('text-dark');
                }

                refreshChart(data.chart_rows || []);
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat summary kas'));
            }
        });
    }
</script>
<?= $this->endSection('javascript') ?>
