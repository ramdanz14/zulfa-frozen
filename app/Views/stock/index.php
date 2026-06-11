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
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-xl-3 col-lg-12">
                        <h3 class="mb-0 fw-semibold" id="stock-summary-label"><?= esc($summary['label'] ?? 'Total Stock Rp.0') ?></h3>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label d-block fw-semibold">Jenis:</label>
                        <div class="btn-group w-100" role="group" aria-label="Jenis Stock">
                            <input type="radio" class="btn-check stock-filter" name="jenis" id="jenis-qty" value="qty" <?= ($initialJenis ?? '') === 'qty' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="jenis-qty">Qty</label>
                            <input type="radio" class="btn-check stock-filter" name="jenis" id="jenis-rupiah" value="rupiah" <?= ($initialJenis ?? 'rupiah') === 'rupiah' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="jenis-rupiah">Rupiah</label>

                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label d-block fw-semibold">Urutan:</label>
                        <div class="btn-group w-100" role="group" aria-label="Urutan Stock">
                            <input type="radio" class="btn-check stock-order" name="urutan" id="urutan-saldo" value="saldo" <?= ($initialUrutan ?? 'saldo') === 'saldo' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-secondary" for="urutan-saldo">Saldo Akhir</label>

                            <input type="radio" class="btn-check stock-order" name="urutan" id="urutan-kategori" value="kategori" <?= ($initialUrutan ?? '') === 'kategori' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-secondary" for="urutan-kategori">Kategori</label>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-12">
                        <div class="d-grid">
                            <button type="button" class="btn btn-danger" id="btn-recalculate" <?= (($aksesMenuData['akses_update'] ?? 'N') === 'Y') ? '' : 'disabled' ?>>
                                Hitung Ulang
                            </button>
                        </div>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    Klik kode item untuk melihat history mutasi stock periode berjalan.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-stock" class="table table-bordered table-hover table-striped table-sm align-middle">
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

<div class="modal fade" id="stock-history-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">History Stock Item</h5>
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
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-end">Beli</th>
                                <th class="text-end">Retur Beli</th>
                                <th class="text-end">Jual</th>
                                <th class="text-end">Retur Jual</th>
                                <th class="text-end">Adj</th>
                                <th class="text-end">Saldo Akhir</th>
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
    let currentJenis = '<?= esc($initialJenis ?? 'qty') ?>';
    let currentUrutan = '<?= esc($initialUrutan ?? 'saldo') ?>';
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';

    const stockTable = $('#table-stock').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-Stock',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                        orthogonal: 'export'
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
            url: '<?= base_url('/stock/ajax') ?>',
            type: 'POST',
            data: function(d) {
                d.jenis = currentJenis;
                d.urutan = currentUrutan;
            }
        },
        columns: [{
                data: 'kode_item',
                title: 'Kode Item',
                className: 'not-mobile',
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data;
                    }
                    return `<button type="button" class="btn btn-link btn-sm p-0 text-decoration-none btn-stock-history" data-kode-item="${escapeHtml(data)}" data-nama-item="${escapeHtml(row.nama_item || '')}">
                        ${escapeHtml(data)}
                    </button>`;
                }
            },
            {
                data: 'nama_item',
                title: 'Nama Item'
            },
            {
                data: 'kat_id',
                title: 'Kategori',
                className: 'not-mobile'
            },
            {
                data: 'begbal',
                title: 'Awal',
                className: 'text-end',
                render: renderMetric
            },
            {
                data: 'beli',
                title: 'Beli',
                className: 'text-end',
                render: renderMetric
            },
            {
                data: 'retur_beli',
                title: 'Retur Beli',
                className: 'text-end',
                render: renderMetric
            },
            {
                data: 'jual',
                title: 'Jual',
                className: 'text-end',
                render: renderMetric
            },
            {
                data: 'retur_jual',
                title: 'Retur Jual',
                className: 'text-end',
                render: renderMetric
            },
            {
                data: 'spd',
                title: 'SPD',
                className: 'text-end',
                render: renderSpd
            },
            {
                data: 'adj',
                title: 'Adj',
                className: 'text-end',
                render: renderMetric
            },
            {
                data: 'dsi',
                title: 'DSI',
                className: 'text-end',
                render: renderSpd
            },
            {
                data: 'saldo_akhir',
                title: ' Akhir',
                className: 'text-end fw-semibold',
                render: renderMetric
            },
            {
                data: 'stok_konversi',
                title: 'Stock Konversi',
                className: 'not-mobile',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const stockKonversi = data || '-';
                        const dasar = row.satuan_dasar ? `<div class="small text-muted">Satuan dasar: ${escapeHtml(row.satuan_dasar)}</div>` : '';
                        return `${escapeHtml(stockKonversi)}${dasar}`;
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
        $button.prop('disabled', true).text('Memproses...');

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
                $button.prop('disabled', false).text('Hitung Ulang');
            }
        });
    });

    $('#table-stock tbody').on('click', '.btn-stock-history', function() {
        const kodeItem = $(this).data('kode-item');
        const namaItem = $(this).data('nama-item') || '';
        openStockHistory(kodeItem, namaItem);
    });

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
                    <td class="text-end">${formatQty(row.beli || 0)}</td>
                    <td class="text-end">${formatQty(row.retur_beli || 0)}</td>
                    <td class="text-end">${formatQty(row.jual || 0)}</td>
                    <td class="text-end">${formatQty(row.retur_jual || 0)}</td>
                    <td class="text-end">${formatQty(row.adj || 0)}</td>
                    <td class="text-end fw-semibold">${formatQty(row.saldo_akhir || 0)}</td>
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