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
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Laporan Slow Moving</h4>
                        <p class="mb-0">Klasifikasi item berdasarkan SPD, saldo stok, cover hari, dan target margin.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div id="selected-store-info" class="text-muted small"></div>
                        <div id="as-of-info" class="text-muted small"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
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
            <div class="col-xl-8">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Acuan Klasifikasi</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small">SPD</div>
                                <div class="fw-semibold">Rata-rata sales per hari</div>
                                <div class="small text-muted">Diambil dari <code>stmast.spd</code>, hasil rata-rata jual bersih harian beberapa bulan sesuai setting <code>bulan_spd</code>.</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Cover Hari</div>
                                <div class="fw-semibold">Perkiraan stok bertahan</div>
                                <div class="small text-muted">Rumus: <code>stmast.qty / stmast.spd</code>. Jika SPD nol dan stok masih ada, item dianggap tidak bergerak.</div>
                            </div>
                            <div class="col-md-6">
                                <span class="badge bg-success me-1">FAST MOVING</span>
                                <div class="small text-muted mt-1">Stok ada, SPD lebih dari nol, dan cover hari maksimal 7 hari.</div>
                            </div>
                            <div class="col-md-6">
                                <span class="badge bg-info me-1">NORMAL</span>
                                <div class="small text-muted mt-1">Stok ada, SPD lebih dari nol, dan cover hari 8 sampai 30 hari.</div>
                            </div>
                            <div class="col-md-6">
                                <span class="badge bg-warning text-dark me-1">SLOW MOVING</span>
                                <div class="small text-muted mt-1">Stok ada, SPD lebih dari nol, dan cover hari lebih dari 30 hari.</div>
                            </div>
                            <div class="col-md-6">
                                <span class="badge bg-danger me-1">DEAD STOCK</span>
                                <div class="small text-muted mt-1">Stok masih ada, tetapi SPD nol atau tidak ada penjualan bersih harian.</div>
                            </div>
                            <div class="col-md-6">
                                <span class="badge bg-primary me-1">POTENSI STOCKOUT</span>
                                <div class="small text-muted mt-1">Stok kosong, tetapi SPD masih lebih dari nol.</div>
                            </div>
                            <div class="col-md-6">
                                <span class="badge bg-secondary me-1">STOK KOSONG</span>
                                <div class="small text-muted mt-1">Stok kosong dan SPD nol.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Ringkasan Stok</h5>
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Total Item Aktif</div>
                                <div class="fs-6 fw-semibold" id="summary-item">0</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Fast Moving</div>
                                <div class="fs-6 fw-semibold text-success" id="summary-fast">0</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Slow Moving</div>
                                <div class="fs-6 fw-semibold text-warning" id="summary-slow">0</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Dead Stock</div>
                                <div class="fs-6 fw-semibold text-danger" id="summary-dead">0</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Potensi Stockout</div>
                                <div class="fs-6 fw-semibold text-primary" id="summary-stockout">0</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Rata-rata SPD</div>
                                <div class="fs-6 fw-semibold" id="summary-spd">0</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Nilai Stok Total</div>
                                <div class="fs-6 fw-semibold" id="summary-stock-value">Rp 0</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Nilai Slow + Dead</div>
                                <div class="fs-6 fw-semibold text-danger" id="summary-risk-value">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Qty</th>
                            <th>SPD</th>
                            <th>Cover Hari</th>
                            <th>Nilai Stok</th>
                            <th>Target Margin</th>
                            <th>Last Beli</th>
                            <th>Last Jual</th>
                            <th>Rekomendasi</th>
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

        updateStoreInfo();
        initTable();
        loadReport();
    });

    $('#btn-filter').on('click', loadReport);
    $('#btn-reset').on('click', function() {
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
                        title: 'Laporan-Slow-Moving',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export'
                        }
                    }, {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        title: 'Laporan Slow Moving',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }, {
                        extend: 'print',
                        text: 'Print',
                        title: 'Laporan Slow Moving'
                    }, 'pageLength']
                }
            },
            data: [],
            ordering: true,
            order: [
                [6, 'desc']
            ],
            responsive: true,
            pageLength: 25,
            lengthMenu: [
                [25, 50, 100, -1],
                ['25 rows', '50 rows', '100 rows', 'Show all']
            ],
            columns: [{
                    data: 'nama_item',
                    render: function(data, type, row) {
                        if (type === 'export' || type === 'sort') {
                            return data || row.kode_item || '-';
                        }
                        const meta = [row.kode_item || '-', row.sat_id ? `Sat ${row.sat_id}` : '', row.supco ? `Supplier ${row.supco}` : ''].filter(Boolean).join(' | ');
                        return `<div class="fw-semibold">${escapeHtml(data || row.kode_item || '-')}</div><small class="text-muted">${escapeHtml(meta)}</small>`;
                    }
                },
                {
                    data: 'kat_id',
                    className: 'text-center',
                    render: data => escapeHtml(data || '-')
                },
                {
                    data: 'kategori_moving',
                    className: 'text-center',
                    render: statusRender
                },
                {
                    data: 'qty',
                    className: 'text-end',
                    render: qtyRender
                },
                {
                    data: 'spd',
                    className: 'text-end',
                    render: qtyRender
                },
                {
                    data: 'cover_hari',
                    className: 'text-center',
                    render: coverRender
                },
                {
                    data: 'nilai_stok',
                    className: 'text-end',
                    render: moneyRender
                },
                {
                    data: 'target_psn_margin',
                    className: 'text-center',
                    render: percentRender
                },
                {
                    data: 'last_beli',
                    className: 'text-center',
                    render: dateRender
                },
                {
                    data: 'last_jual',
                    className: 'text-center',
                    render: dateRender
                },
                {
                    data: 'rekomendasi',
                    render: data => escapeHtml(data || '-')
                }
            ]
        });
    }

    function loadReport() {
        updateStoreInfo();
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/slowmoving/report') ?>',
            dataType: 'json',
            data: {
                toko_id: selectedStore()
            },
            success: function(res) {
                renderReport(res?.data || {});
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan slow moving'));
            }
        });
    }

    function renderReport(report) {
        const summary = report.summary || {};
        $('#selected-store-info').text(`Toko aktif: ${report?.toko?.toko_id || selectedStore()} - ${report?.toko?.toko_nama || selectedStore()}`);
        $('#as-of-info').text(report.as_of ? `Data per ${formatDateTime(report.as_of)}` : '');
        $('#summary-item').text(num(summary.total_item || 0));
        $('#summary-fast').text(num(summary.fast_count || 0));
        $('#summary-slow').text(num(summary.slow_count || 0));
        $('#summary-dead').text(num(summary.dead_count || 0));
        $('#summary-stock-value').text(rp(summary.total_nilai_stok || 0));
        $('#summary-risk-value').text(rp(summary.slow_dead_nilai_stok || 0));
        $('#summary-stockout').text(num(summary.stockout_count || 0));
        $('#summary-spd').text(qty(summary.avg_spd || 0));
        table.clear().rows.add(report.rows || []).draw();
    }

    function statusRender(data, type) {
        if (type === 'export' || type === 'sort') {
            return data || '-';
        }
        const status = data || '-';
        const cls = {
            'FAST MOVING': 'bg-success',
            'NORMAL': 'bg-info',
            'SLOW MOVING': 'bg-warning text-dark',
            'DEAD STOCK': 'bg-danger',
            'POTENSI STOCKOUT': 'bg-primary',
            'STOK KOSONG': 'bg-secondary'
        } [status] || 'bg-secondary';
        return `<span class="badge ${cls}">${escapeHtml(status)}</span>`;
    }

    function coverRender(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        const value = Number(data || 0);
        if (value >= 999999) {
            return type === 'export' ? 'Tidak bergerak' : '&infin;';
        }
        return `${qty(value)} hari`;
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

    function dateRender(data) {
        if (!data) {
            return '-';
        }
        return escapeHtml(String(data));
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

    function formatDateTime(value) {
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }
</script>
<?= $this->endSection('javascript') ?>