<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<style>
/* ── Card view template styles ─────────────────────────── */
.sm-card-badge { font-size: .7rem; }
.sm-card-item-name { font-size: .9rem; font-weight: 600; }
.sm-card-meta { font-size: .75rem; color: #6c757d; }
.sm-metric { text-align: center; }
.sm-metric .label { font-size: .68rem; color: #6c757d; text-transform: uppercase; letter-spacing: .02em; }
.sm-metric .value { font-size: .88rem; font-weight: 600; }
.sm-rekom { font-size: .78rem; }

/* ── Acuan klasifikasi legend tighter on mobile ─────────── */
.kat-legend-item { display: flex; align-items: flex-start; gap: .5rem; }
.kat-legend-item .badge { flex-shrink: 0; margin-top: 2px; }

/* ── Summary tiles consistent height ───────────────────── */
.summary-tile { border: 1px solid rgba(0,0,0,.08); border-radius: .5rem; padding: .6rem .75rem; }
.summary-tile .s-label { font-size: .72rem; color: #6c757d; }
.summary-tile .s-value { font-size: 1rem; font-weight: 600; }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Page header banner -->
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-2">Laporan Slow Moving</h4>
                        <p class="mb-0">Klasifikasi item berdasarkan SPD, saldo stok, cover hari, dan target margin.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <div id="selected-store-info" class="text-muted small"></div>
                        <div id="as-of-info" class="text-muted small"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="card mb-3">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label small fw-semibold mb-1">Filter Toko</label>
                        <select class="form-select form-select-sm select2" id="filter-toko">
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>" <?= (string) ($row['toko_id'] ?? '') === (string) session('toko_id') ? 'selected' : '' ?>>
                                    <?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-grid d-md-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm w-100" id="btn-filter">
                            <i class="ti ti-search me-1"></i>Tampilkan
                        </button>
                        <button type="button" class="btn btn-light btn-sm w-100" id="btn-reset">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info panels: Acuan klasifikasi + Ringkasan stok -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-8">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3">
                        <h6 class="fw-semibold mb-3">Acuan Klasifikasi</h6>
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <div class="kat-legend-item mb-1">
                                    <span class="badge bg-secondary sm-card-badge">SPD</span>
                                    <div class="small text-muted">Rata-rata sales per hari dari <code>stmast.spd</code>.</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="kat-legend-item mb-1">
                                    <span class="badge bg-secondary sm-card-badge">Cover Hari</span>
                                    <div class="small text-muted">Rumus: <code>qty / spd</code>. SPD nol = tidak bergerak.</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="kat-legend-item">
                                    <span class="badge bg-success sm-card-badge">FAST MOVING</span>
                                    <div class="small text-muted">Stok ada, SPD &gt; 0, cover ≤ 7 hari.</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="kat-legend-item">
                                    <span class="badge bg-info sm-card-badge">NORMAL</span>
                                    <div class="small text-muted">Stok ada, SPD &gt; 0, cover 8–30 hari.</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="kat-legend-item">
                                    <span class="badge bg-warning text-dark sm-card-badge">SLOW MOVING</span>
                                    <div class="small text-muted">Stok ada, SPD &gt; 0, cover &gt; 30 hari.</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="kat-legend-item">
                                    <span class="badge bg-danger sm-card-badge">DEAD STOCK</span>
                                    <div class="small text-muted">Stok masih ada, SPD nol atau tidak ada penjualan.</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="kat-legend-item">
                                    <span class="badge bg-primary sm-card-badge">POTENSI STOCKOUT</span>
                                    <div class="small text-muted">Stok kosong, tetapi SPD masih &gt; 0.</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="kat-legend-item">
                                    <span class="badge bg-secondary sm-card-badge">STOK KOSONG</span>
                                    <div class="small text-muted">Stok kosong dan SPD nol.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3">
                        <h6 class="fw-semibold mb-3">Ringkasan Stok</h6>
                        <div class="row g-2">
                            <div class="col-6 col-sm-4 col-xl-6">
                                <div class="summary-tile">
                                    <div class="s-label">Total Item</div>
                                    <div class="s-value" id="summary-item">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4 col-xl-6">
                                <div class="summary-tile">
                                    <div class="s-label">Fast Moving</div>
                                    <div class="s-value text-success" id="summary-fast">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4 col-xl-6">
                                <div class="summary-tile">
                                    <div class="s-label">Slow Moving</div>
                                    <div class="s-value text-warning" id="summary-slow">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4 col-xl-6">
                                <div class="summary-tile">
                                    <div class="s-label">Dead Stock</div>
                                    <div class="s-value text-danger" id="summary-dead">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4 col-xl-6">
                                <div class="summary-tile">
                                    <div class="s-label">Potensi Stockout</div>
                                    <div class="s-value text-primary" id="summary-stockout">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4 col-xl-6">
                                <div class="summary-tile">
                                    <div class="s-label">Rata-rata SPD</div>
                                    <div class="s-value" id="summary-spd">0</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-12">
                                <div class="summary-tile">
                                    <div class="s-label">Nilai Stok Total</div>
                                    <div class="s-value" id="summary-stock-value">Rp 0</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-12">
                                <div class="summary-tile">
                                    <div class="s-label">Nilai Slow + Dead</div>
                                    <div class="s-value text-danger" id="summary-risk-value">Rp 0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card border">
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0 table-light thead">
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

                <!-- CardView template for slowmoving list -->
                <template id="card-sm-template">
                    <div class="card border mb-2 shadow-sm">
                        <div class="card-body p-3">
                            <!-- Row 1: Item name + status badge -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div style="min-width:0; flex:1;">
                                    <div class="sm-card-item-name text-truncate" data-dtcv-field="0"></div>
                                    <div class="sm-card-meta mt-1" data-dtcv-field="1"></div>
                                </div>
                                <div class="ms-2 flex-shrink-0" data-dtcv-field="2"></div>
                            </div>
                            <!-- Row 2: Key metrics -->
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <div class="sm-metric">
                                        <div class="label">Qty Stok</div>
                                        <div class="value" data-dtcv-field="3"></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="sm-metric">
                                        <div class="label">SPD</div>
                                        <div class="value" data-dtcv-field="4"></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="sm-metric">
                                        <div class="label">Cover</div>
                                        <div class="value" data-dtcv-field="5"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Row 3: Financial + dates -->
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="sm-metric">
                                        <div class="label">Nilai Stok</div>
                                        <div class="value" data-dtcv-field="6"></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="sm-metric">
                                        <div class="label">Target Margin</div>
                                        <div class="value" data-dtcv-field="7"></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="sm-metric">
                                        <div class="label">Last Beli</div>
                                        <div class="value" style="font-size:.78rem;" data-dtcv-field="8"></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="sm-metric">
                                        <div class="label">Last Jual</div>
                                        <div class="value" style="font-size:.78rem;" data-dtcv-field="9"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Row 4: Rekomendasi -->
                            <div class="border-top pt-2">
                                <div class="text-muted" style="font-size:.68rem; text-transform:uppercase; letter-spacing:.02em;">Rekomendasi</div>
                                <div class="sm-rekom" data-dtcv-field="10"></div>
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
        DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary btn-sm';
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
            responsive: false,
            pageLength: 25,
            lengthMenu: [
                [25, 50, 100, -1],
                ['25 rows', '50 rows', '100 rows', 'Show all']
            ],
            cardView: {
                enable: true,
                breakpoint: 768,
                template: '#card-sm-template',
                gridClass: 'col-12 col-sm-6 mb-2'
            },
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
        const btn = $('#btn-filter').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memuat...');
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
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="ti ti-search me-1"></i>Tampilkan');
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