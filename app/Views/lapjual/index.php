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
                        <h4 class="fw-semibold mb-2">Laporan Penjualan Per Tanggal</h4>
                        <p class="mb-0"><span class="page-pretitle">Periode aktif</span> | Analisis customer, transaksi, omset, dan margin penjualan per tanggal.</p>
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
                    <div class="col-lg-5" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label">Filter Toko</label>
                        <select class="form-select select2" id="filter-toko" multiple>
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>"><?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-<?= !empty($tokoOptions) ? '3' : '8' ?> d-grid d-lg-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btn-filter">Terapkan Filter</button>
                        <button type="button" class="btn btn-light w-100" id="btn-reset">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Jumlah Customer</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-customer">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Jumlah Transaksi</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-transaksi">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Total Omset</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-omset">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small">Total Margin Bruto</div>
                        <div class="fs-6 fw-semibold mt-2" id="summary-margin">Rp 0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="<?= !empty($tokoOptions) ? 'col-lg-9' : 'col-12' ?>">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Laporan Penjualan Per Tanggal</div>
                        <div id="chart-penjualan" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
            <?php if (!empty($tokoOptions)) : ?>
                <div class="col-lg-3">
                    <div class="card h-100 mb-0">
                        <div class="card-body">
                            <div class="fw-semibold mb-3">Trend Margin</div>
                            <div id="chart-margin" style="min-height: 320px;"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle">
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
            $('#selected-store-info').text(`Toko aktif: ${sessionTokoId}`);
            return;
        }
        if (!selected.length) {
            $('#selected-store-info').text('Toko: semua toko yang diizinkan');
            return;
        }
        $('#selected-store-info').text(`Toko dipilih: ${selected.join(', ')}`);
    }

    function initCharts() {
        chartPenjualan = new ApexCharts(document.querySelector('#chart-penjualan'), {
            chart: {
                type: 'line',
                height: 320,
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
                size: 4,
                hover: {
                    size: 6
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
                    formatter: function(value, {
                        dataPointIndex,
                        w
                    }) {
                        const rawDate = w.config.series?.[0]?.metaDates?.[dataPointIndex] || value;
                        return formatTooltipDate(rawDate);
                    }
                },
                y: {
                    formatter: function(value, {
                        seriesIndex,
                        w
                    }) {
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
                    height: 320,
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
                    size: 4,
                    hover: {
                        size: 6
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
                        formatter: function(value, {
                            dataPointIndex,
                            w
                        }) {
                            const rawDate = w.config.series?.[0]?.metaDates?.[dataPointIndex] || value;
                            return formatTooltipDate(rawDate);
                        }
                    },
                    y: {
                        formatter: function(value, {
                            seriesIndex,
                            w
                        }) {
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
        if (!rawDate) {
            return '-';
        }
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
            if (!tanggal) {
                return;
            }
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
        if (!chart) {
            return;
        }

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

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
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
                title: 'Jumlah Customer',
                className: 'text-center'
            },
            {
                data: 'jumlah_transaksi',
                title: 'Jumlah Transaksi',
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
