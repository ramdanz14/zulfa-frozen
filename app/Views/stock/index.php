<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $summary
 * @var string $initialJenis
 * @var string $initialUrutan
 * @var string $akses_menu
 */
$aksesMenuData = json_decode($akses_menu ?? '{}', true) ?: [];
?>
<style>
    /* Styling khusus monitoring stock mobile & responsive */
    .stock-summary-card {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.08) 0%, rgba(var(--bs-info-rgb), 0.04) 100%);
        border: 1px solid rgba(var(--bs-primary-rgb), 0.15);
    }
    .stock-empty-badge {
        background-color: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 0.375rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .stock-positive-badge {
        background-color: #ecfdf5;
        color: #059669;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 0.375rem;
        display: inline-block;
    }
    .stock-konversi-text {
        font-size: 0.825rem;
        color: var(--bs-body-color);
        font-weight: 500;
        background-color: var(--bs-tertiary-bg);
        padding: 0.25rem 0.5rem;
        border-radius: 0.35rem;
        display: inline-block;
        border: 1px dashed var(--bs-border-color);
    }
    .btn-detail-trigger {
        min-width: 36px;
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    @media (max-width: 767.98px) {
        .stock-filter-group .btn {
            padding: 0.45rem 0.75rem;
            font-size: 0.875rem;
            min-height: 40px;
        }
        #table-stock th, #table-stock td {
            padding: 0.5rem 0.4rem;
            font-size: 0.85rem;
        }
        .stock-summary-card {
            padding: 0.85rem !important;
        }
        .item-name-cell {
            max-width: 170px;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Summary & Quick Action Card -->
        <div class="card mb-3 shadow-none border stock-summary-card">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5 col-xl-4">
                        <span class="text-muted fs-2 d-block text-uppercase fw-semibold mb-1">Monitoring Mutasi Stock</span>
                        <h4 class="mb-0 fw-bold text-primary" id="stock-summary-label"><?= esc($summary['label'] ?? 'Total Stock Rp.0') ?></h4>
                    </div>
                    <div class="col-6 col-md-3 col-xl-3">
                        <label class="form-label fs-2 text-muted fw-semibold mb-1">Jenis Nilai:</label>
                        <div class="btn-group w-100 stock-filter-group" role="group" aria-label="Jenis Stock">
                            <input type="radio" class="btn-check stock-filter" name="jenis" id="jenis-qty" value="qty" <?= ($initialJenis ?? '') === 'qty' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="jenis-qty">Qty</label>
                            <input type="radio" class="btn-check stock-filter" name="jenis" id="jenis-rupiah" value="rupiah" <?= ($initialJenis ?? 'rupiah') === 'rupiah' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="jenis-rupiah">Rupiah</label>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-3">
                        <label class="form-label fs-2 text-muted fw-semibold mb-1">Urutan Data:</label>
                        <div class="btn-group w-100 stock-filter-group" role="group" aria-label="Urutan Stock">
                            <input type="radio" class="btn-check stock-order" name="urutan" id="urutan-saldo" value="saldo" <?= ($initialUrutan ?? 'saldo') === 'saldo' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-secondary" for="urutan-saldo">Saldo Akhir</label>
                            <input type="radio" class="btn-check stock-order" name="urutan" id="urutan-kategori" value="kategori" <?= ($initialUrutan ?? '') === 'kategori' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-secondary" for="urutan-kategori">Kategori</label>
                        </div>
                    </div>
                    <div class="col-12 col-xl-2 text-xl-end mt-2 mt-xl-0">
                        <button type="button" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-1" id="btn-recalculate" <?= (($aksesMenuData['akses_update'] ?? 'N') === 'Y') ? '' : 'disabled' ?> style="min-height: 40px;">
                            <i class="ti ti-refresh fs-4"></i>
                            <span>Hitung Ulang</span>
                        </button>
                    </div>
                </div>
                <div class="small text-muted mt-2 d-flex align-items-center gap-1">
                    <i class="ti ti-info-circle text-primary"></i>
                    <span>Ketuk kode item atau tombol <i class="ti ti-eye text-primary"></i> untuk melihat rincian mutasi & konversi stok.</span>
                </div>
            </div>
        </div>

        <!-- Tabel Stock Utama -->
        <div class="card border">
            <div class="card-body p-2 p-md-3">
                <table id="table-stock" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td>Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Informasi Detail & Mutasi Stock (Custom Responsive Modal) -->
<div class="modal fade" id="stock-info-modal" tabindex="-1" aria-labelledby="stockInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0" id="stockInfoModalLabel">Detail Monitoring Stock</h5>
                    <div class="small text-white-50" id="stock-info-subtitle">-</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <!-- Status Stok & Konversi Highlight -->
                <div class="row g-2 mb-3">
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-2 border bg-light h-100">
                            <span class="text-muted small d-block mb-1">Status Stok Akhir:</span>
                            <div id="modal-saldo-badge" class="fs-4 fw-bold">-</div>
                            <div class="text-muted small mt-1" id="modal-satuan-dasar">-</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="p-3 rounded-2 border bg-light h-100">
                            <span class="text-muted small d-block mb-1">Stok Konversi Satuan:</span>
                            <div id="modal-stok-konversi" class="fs-5 fw-semibold text-primary">-</div>
                            <div class="text-muted small mt-1" id="modal-kategori-info">-</div>
                        </div>
                    </div>
                </div>

                <!-- Rumus & Penjelasan Mutasi -->
                <div class="card border bg-light mb-3">
                    <div class="card-header bg-transparent py-2 border-bottom fw-semibold d-flex align-items-center justify-content-between">
                        <span><i class="ti ti-calculator text-primary me-1"></i> Rincian Mutasi Periode Berjalan</span>
                        <span class="badge bg-secondary-subtle text-secondary" id="modal-jenis-label">Qty</span>
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-2 bg-white text-center">
                                <thead class="table-light small">
                                    <tr>
                                        <th title="Saldo Awal Bulan">Awal</th>
                                        <th title="Beli ke Supplier (+)" class="text-success">+ Beli</th>
                                        <th title="Retur ke Supplier (-)" class="text-danger">- Retur Beli</th>
                                        <th title="Jual ke Konsumen (-)" class="text-danger">- Jual</th>
                                        <th title="Retur dari Konsumen (+)" class="text-success">+ Retur Jual</th>
                                        <th title="Hasil Stock Opname">Adj</th>
                                        <th title="Saldo Akhir Saat Ini" class="table-primary fw-bold">= Akhir</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold">
                                    <tr>
                                        <td id="m-val-begbal">0</td>
                                        <td id="m-val-beli" class="text-success">0</td>
                                        <td id="m-val-retur-beli" class="text-danger">0</td>
                                        <td id="m-val-jual" class="text-danger">0</td>
                                        <td id="m-val-retur-jual" class="text-success">0</td>
                                        <td id="m-val-adj">0</td>
                                        <td id="m-val-saldo-akhir" class="table-primary fw-bold text-primary">0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 rounded bg-white border small text-secondary">
                            <strong>Rumus:</strong> Saldo Akhir = <code>Awal + Beli - Retur Beli - Jual + Retur Jual + Adj</code>
                        </div>
                    </div>
                </div>

                <!-- Indikator Tambahan SPD / DSI -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="border rounded p-2 text-center bg-white">
                            <div class="small text-muted">SPD (Sales Per Day)</div>
                            <div class="fw-bold fs-4 text-dark" id="modal-val-spd">0</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 text-center bg-white">
                            <div class="small text-muted">DSI (Day Sales Inventory)</div>
                            <div class="fw-bold fs-4 text-dark" id="modal-val-dsi">0 Hari</div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="btn-open-full-history">
                        <i class="ti ti-history me-1"></i> Lihat Buku History Mutasi Harian Lengkap
                    </button>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal History Harian Mutasi Stock -->
<div class="modal fade" id="stock-history-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1 fw-bold">History Mutasi Harian Item</h5>
                    <div class="small text-muted" id="stock-history-item-label">-</div>
                    <div class="small text-muted" id="stock-lastso-label">-</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="stock-history-loading" class="text-center py-4 d-none">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    <span>Memuat history stock...</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-end text-success">+ Beli</th>
                                <th class="text-end text-danger">- Retur Beli</th>
                                <th class="text-end text-danger">- Jual</th>
                                <th class="text-end text-success">+ Retur Jual</th>
                                <th class="text-end">Adj</th>
                                <th class="text-end fw-bold">Saldo Akhir</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody id="stock-history-body">
                            <tr>
                                <td colspan="9" class="text-center text-muted">Klik kode item untuk melihat history.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const stockHistoryModal = new bootstrap.Modal(document.getElementById('stock-history-modal'));
    const stockInfoModal = new bootstrap.Modal(document.getElementById('stock-info-modal'));
    let currentJenis = '<?= esc($initialJenis ?? 'qty') ?>';
    let currentUrutan = '<?= esc($initialUrutan ?? 'saldo') ?>';
    let currentSelectedItem = null;
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';

    const stockTable = $('#table-stock').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-Stock',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        orthogonal: 'export'
                    }
                }, 'pageLength']
            }
        },
        lengthMenu: [
            [25, 50, 100, -1],
            ['25 rows', '50 rows', '100 rows', 'Show all']
        ],
        responsive: {
            details: {
                type: 'column',
                target: '.dtr-control-btn',
                renderer: function(api, rowIdx, columns) {
                    // Custom modal info handler dipanggil saat tombol detail diklik
                    return false;
                }
            }
        },
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/stock/ajax') ?>',
            type: 'POST',
            data: function(d) {
                d.jenis = currentJenis;
                d.urutan = currentUrutan;
            }
        },
        columns: [
            {
                data: null,
                title: '#',
                orderable: false,
                searchable: false,
                className: 'dtr-control-btn text-center',
                responsivePriority: 1,
                render: function(data, type, row) {
                    if (type !== 'display') return '';
                    return `<button type="button" class="btn btn-sm btn-light-primary text-primary btn-detail-trigger" title="Lihat Informasi Stok" aria-label="Lihat Rincian Stok">
                        <i class="ti ti-eye fs-5"></i>
                    </button>`;
                }
            },
            {
                data: 'kode_item',
                title: 'Kode Item',
                className: 'not-mobile',
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data;
                    }
                    return `<button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold btn-stock-history" data-kode-item="${escapeHtml(data)}" data-nama-item="${escapeHtml(row.nama_item || '')}">
                        ${escapeHtml(data)}
                    </button>`;
                }
            },
            {
                data: 'nama_item',
                title: 'Nama & Stok Konversi',
                responsivePriority: 1,
                className: 'item-name-cell',
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    const nama = escapeHtml(data || '-');
                    const konversi = row.stok_konversi ? `<div class="mt-1"><span class="stock-konversi-text"><i class="ti ti-box me-1"></i>${escapeHtml(row.stok_konversi)}</span></div>` : '';
                    const kodeMobile = `<div class="d-inline-block d-md-none small text-muted font-monospace me-1">${escapeHtml(row.kode_item || '')}</div>`;
                    return `<div>
                        <div class="fw-semibold text-dark">${kodeMobile}${nama}</div>
                        ${konversi}
                    </div>`;
                }
            },
            {
                data: 'kat_id',
                title: 'Kategori',
                className: 'not-mobile',
                render: function(data, type) {
                    return type === 'display' ? `<span class="badge bg-secondary-subtle text-secondary">${escapeHtml(data || '-')}</span>` : data;
                }
            },
            {
                data: 'begbal',
                title: 'Awal',
                className: 'text-end not-mobile',
                render: renderMetric
            },
            {
                data: 'beli',
                title: 'Beli',
                className: 'text-end not-mobile',
                render: renderMetric
            },
            {
                data: 'retur_beli',
                title: 'Retur Beli',
                className: 'text-end not-mobile',
                render: renderMetric
            },
            {
                data: 'jual',
                title: 'Jual',
                className: 'text-end not-mobile',
                render: renderMetric
            },
            {
                data: 'retur_jual',
                title: 'Retur Jual',
                className: 'text-end not-mobile',
                render: renderMetric
            },
            {
                data: 'spd',
                title: 'SPD',
                className: 'text-end not-mobile',
                render: renderSpd
            },
            {
                data: 'adj',
                title: 'Adj',
                className: 'text-end not-mobile',
                render: renderMetric
            },
            {
                data: 'dsi',
                title: 'DSI',
                className: 'text-end not-mobile',
                render: renderSpd
            },
            {
                data: 'saldo_akhir',
                title: 'Saldo Akhir',
                className: 'text-end fw-semibold',
                responsivePriority: 2,
                render: function(data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        return Number(data || 0);
                    }
                    if (type === 'export') {
                        return currentJenis === 'rupiah' ? `Rp ${formatMoneyValue(data || 0)}` : formatQty(data || 0);
                    }
                    if (type !== 'display') {
                        return data;
                    }

                    const numVal = Number(data || 0);
                    const formatted = currentJenis === 'rupiah' ? `Rp ${formatMoneyValue(numVal)}` : formatQty(numVal);

                    if (numVal <= 0) {
                        return `<span class="stock-empty-badge" title="Stok Kosong / Habis">
                            <i class="ti ti-alert-triangle"></i> ${formatted}
                        </span>`;
                    }
                    return `<span class="stock-positive-badge">${formatted}</span>`;
                }
            },
            {
                data: 'stok_konversi',
                title: 'Stock Konversi',
                className: 'not-mobile',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const stockKonversi = data || '-';
                        const dasar = row.satuan_dasar ? `<div class="small text-muted">Dasar: ${escapeHtml(row.satuan_dasar)}</div>` : '';
                        return `<span class="fw-semibold text-primary">${escapeHtml(stockKonversi)}</span>${dasar}`;
                    }
                    return data || '';
                }
            }
        ]
    });

    stockTable.on('xhr.dt', function(e, settings, json) {
        if (json?.summary?.label) {
            $('#stock-summary-label').text(json.summary.label);
        }
    });

    $('.stock-filter').on('change', function() {
        currentJenis = $('input[name="jenis"]:checked').val() || 'rupiah';
        stockTable.ajax.reload();
    });

    $('.stock-order').on('change', function() {
        currentUrutan = $('input[name="urutan"]:checked').val() || 'saldo';
        stockTable.ajax.reload();
    });

    $('#btn-recalculate').on('click', function() {
        if (akses_menu?.akses_update !== 'Y') {
            toastr.error('Anda tidak memiliki akses untuk hitung ulang stock');
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...');

        $.ajax({
            url: '<?= base_url('/stock/recalculate') ?>',
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Hitung ulang stock berhasil');
                    stockTable.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal hitung ulang stock');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal hitung ulang stock'));
            },
            complete: function() {
                $button.prop('disabled', false).html('<i class="ti ti-refresh fs-4"></i><span>Hitung Ulang</span>');
            }
        });
    });

    // Handle klik tombol eye info di baris datatables
    $('#table-stock tbody').on('click', '.btn-detail-trigger', function(e) {
        e.stopPropagation();
        const tr = $(this).closest('tr');
        const rowData = stockTable.row(tr).data();
        if (rowData) {
            openStockInfoModal(rowData);
        }
    });

    // Handle klik kode item langsung buka history
    $('#table-stock tbody').on('click', '.btn-stock-history', function(e) {
        e.stopPropagation();
        const kodeItem = $(this).data('kode-item');
        const namaItem = $(this).data('nama-item') || '';
        openStockHistory(kodeItem, namaItem);
    });

    // Buka history lengkap dari dalam modal info
    $('#btn-open-full-history').on('click', function() {
        if (!currentSelectedItem) return;
        stockInfoModal.hide();
        openStockHistory(currentSelectedItem.kode_item, currentSelectedItem.nama_item);
    });

    function openStockInfoModal(row) {
        currentSelectedItem = row;
        const saldoAkhir = Number(row.saldo_akhir || 0);
        const formattedSaldo = currentJenis === 'rupiah' ? `Rp ${formatMoneyValue(saldoAkhir)}` : formatQty(saldoAkhir);

        $('#stockInfoModalLabel').text(row.nama_item || 'Detail Stock');
        $('#stock-info-subtitle').text(`Kode: ${row.kode_item || '-'} | Kategori: ${row.kat_id || '-'}`);
        $('#modal-jenis-label').text(currentJenis === 'rupiah' ? 'Nilai Rupiah' : 'Jumlah Qty');

        // Badge saldo akhir
        if (saldoAkhir <= 0) {
            $('#modal-saldo-badge').html(`<span class="text-danger"><i class="ti ti-alert-triangle me-1"></i>${formattedSaldo} (KOSONG / 0)</span>`);
        } else {
            $('#modal-saldo-badge').html(`<span class="text-success"><i class="ti ti-check me-1"></i>${formattedSaldo}</span>`);
        }

        $('#modal-satuan-dasar').text(row.satuan_dasar ? `Satuan Dasar: ${row.satuan_dasar}` : 'Satuan Dasar: -');
        $('#modal-stok-konversi').text(row.stok_konversi || '-');
        $('#modal-kategori-info').text(`Kategori Produk: ${row.kat_id || '-'}`);

        // Mutasi formula numbers
        $('#m-val-begbal').text(formatMetricDisplay(row.begbal));
        $('#m-val-beli').text(formatMetricDisplay(row.beli));
        $('#m-val-retur-beli').text(formatMetricDisplay(row.retur_beli));
        $('#m-val-jual').text(formatMetricDisplay(row.jual));
        $('#m-val-retur-jual').text(formatMetricDisplay(row.retur_jual));
        $('#m-val-adj').text(formatMetricDisplay(row.adj));
        $('#m-val-saldo-akhir').text(formattedSaldo);

        // SPD & DSI
        $('#modal-val-spd').text(formatQty(row.spd || 0));
        $('#modal-val-dsi').text(`${formatQty(row.dsi || 0)} Hari`);

        stockInfoModal.show();
    }

    function formatMetricDisplay(val) {
        const num = Number(val || 0);
        return currentJenis === 'rupiah' ? `Rp ${formatMoneyValue(num)}` : formatQty(num);
    }

    function renderMetric(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        if (type === 'export') {
            return currentJenis === 'rupiah' ?
                `Rp ${formatMoneyValue(data || 0)}` :
                formatQty(data || 0);
        }
        if (type !== 'display') {
            return data;
        }
        if (currentJenis === 'rupiah') {
            return `Rp ${formatMoneyValue(data || 0)}`;
        }
        return formatQty(data || 0);
    }

    function renderSpd(data, type) {
        if (type === 'sort' || type === 'type') {
            return Number(data || 0);
        }
        if (type !== 'display' && type !== 'export') {
            return data;
        }
        return formatQty(data || 0);
    }

    function formatQty(value) {
        const number = Number(value || 0);
        return number.toLocaleString('id-ID', {
            minimumFractionDigits: Number.isInteger(number) ? 0 : 2,
            maximumFractionDigits: 2
        });
    }

    function openStockHistory(kodeItem, namaItem) {
        $('#stock-history-item-label').text(`${kodeItem}${namaItem ? ' | ' + namaItem : ''}`);
        $('#stock-history-body').html('');
        $('#stock-history-loading').removeClass('d-none');
        stockHistoryModal.show();

        $.ajax({
            url: `<?= base_url('/stock/history') ?>/${encodeURIComponent(kodeItem)}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                renderStockHistory(res?.data || {});
            },
            error: function(xhr) {
                $('#stock-history-body').html(`
                    <tr>
                        <td colspan="9" class="text-center text-danger">${escapeHtml(extractErrorMessage(xhr, 'Gagal memuat history stock'))}</td>
                    </tr>
                `);
            },
            complete: function() {
                $('#stock-history-loading').addClass('d-none');
            }
        });
    }

    function renderStockHistory(payload) {
        const rows = Array.isArray(payload?.rows) ? payload.rows : [];
        const item = payload?.item || {};

        $('#stock-history-item-label').text([
            item.kode_item || '',
            item.nama_item || '',
            item.kat_id ? `Kategori ${item.kat_id}` : '',
            item.sat_dasar ? `Sat ${item.sat_dasar}` : ''
        ].filter(Boolean).join(' | '));

        $('#stock-lastso-label').text(`Terakhir SO : ${item.last_so} (${humanizeDate(item.last_so)}) `);

        if (!rows.length) {
            $('#stock-history-body').html(`
                <tr>
                    <td colspan="9" class="text-center text-muted">Belum ada history stock untuk item ini.</td>
                </tr>
            `);
            return;
        }

        const html = rows.map(function(row) {
            return `
                <tr>
                    <td>${formatDate(row.tanggal)}</td>
                    <td>${escapeHtml(row.label || '-')}</td>
                    <td class="text-end text-success">${formatQty(row.beli || 0)}</td>
                    <td class="text-end text-danger">${formatQty(row.retur_beli || 0)}</td>
                    <td class="text-end text-danger">${formatQty(row.jual || 0)}</td>
                    <td class="text-end text-success">${formatQty(row.retur_jual || 0)}</td>
                    <td class="text-end">${formatQty(row.adj || 0)}</td>
                    <td class="text-end fw-bold">${formatQty(row.saldo_akhir || 0)}</td>
                    <td>${escapeHtml(row.detail || '-')}</td>
                </tr>
            `;
        }).join('');

        $('#stock-history-body').html(html);
    }

    function formatDate(value) {
        if (!value) {
            return '-';
        }
        const date = new Date(`${value}T00:00:00`);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?= $this->endSection('javascript') ?>