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
                        <h4 class="fw-semibold mb-2">Summary Kas</h4>
                        <p class="mb-0"><span class="page-pretitle">Periode aktif</span> | Rekap mutasi kas masuk dan keluar per akun operasional.</p>
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
                        <label class="form-label">Range Tanggal</label>
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
                    <div class="col-lg-3 d-grid d-lg-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btn-filter">Terapkan Filter</button>
                        <button type="button" class="btn btn-light w-100" id="btn-reset">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="text-muted small">Total Tunai</div>
                            <div class="text-muted small" id="summary-transaksi">0 transaksi</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted small">Pemasukan</div>
                                <div class="fs-6 fw-semibold mt-2 text-success" id="summary-tunai-masuk">Rp 0</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Pengeluaran</div>
                                <div class="fs-6 fw-semibold mt-2 text-danger" id="summary-tunai-keluar">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <div class="text-muted small mb-3">Total Non Tunai</div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted small">Pemasukan</div>
                                <div class="fs-6 fw-semibold mt-2 text-success" id="summary-nontunai-masuk">Rp 0</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Pengeluaran</div>
                                <div class="fs-6 fw-semibold mt-2 text-danger" id="summary-nontunai-keluar">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="fw-semibold mb-3">Perbandingan Kas Per Akun</div>
                <div id="chart-kas" style="min-height: 360px;"></div>
            </div>
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
                height: 360,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    columnWidth: '60%'
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
                categories: []

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
            }
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

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: ['pageLength']
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
                refreshChart(data.chart_rows || []);
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat summary kas'));
            }
        });
    }
</script>
<?= $this->endSection('javascript') ?>
