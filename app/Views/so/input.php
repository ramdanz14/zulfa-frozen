<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $kategoriOptions
 * @var array $soAktif
 */
$soDate = !empty($soAktif['tanggal'] ?? '') ? esc($soAktif['tanggal']) : 'Tidak Ada SO Aktif';
$hasActiveSo = !empty($soAktif['tanggal'] ?? '');
?>
<style>
    /* Styling responsif mobile untuk Input SO & CardView */
    .so-input-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .so-input-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .so-item-code-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        padding: 0.15rem 0.45rem;
        border-radius: 0.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border: 1px solid var(--bs-border-color);
    }
    .so-card-title {
        font-weight: 700;
        color: var(--bs-heading-color);
        line-height: 1.3;
        font-size: 0.95rem;
    }
    .so-metric-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.45rem 0.65rem;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-1">Input Stock Opname</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold text-dark"><?= $soDate ?></span> | Input pencatatan stok fisik per item secara realtime.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <a href="<?= base_url('/opname') ?>" class="btn btn-outline-secondary btn-sm px-3"><i class="ti ti-arrow-left me-1"></i> Kembali ke Menu SO</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border mb-3">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label small fw-semibold">Status Input</label>
                        <select class="form-select form-select-sm" id="status_input">
                            <option value="belum">Belum Diinput</option>
                            <option value="sudah">Sudah Diinput</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-6">
                        <label class="form-label small fw-semibold">Filter Kategori Produk</label>
                        <select class="form-select select2" id="kat_id">
                            <option value="all">Semua Kategori</option>
                            <?php foreach ($kategoriOptions as $row): ?>
                                <option value="<?= esc($row['id']) ?>"><?= esc($row['text']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-lg-3 d-grid">
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" id="btn-filter"><i class="ti ti-filter me-1"></i> Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border">
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead class="table-light"></thead>
                    <tbody>
                        <tr>
                            <td>No data to show</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template for Input SO (Mobile Reflow) -->
                <template id="card-so-input-template">
                    <div class="so-input-card mb-2">
                        <!-- Baris 1: Nama Item, Kode, Status & Action -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div class="min-w-0 flex-grow-1">
                                <div class="so-card-title text-truncate" data-dtcv-field="1"></div>
                                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                    <span class="so-item-code-badge"><i class="ti ti-barcode"></i><span data-dtcv-field="0"></span></span>
                                    <span class="badge bg-light text-secondary border small"><i class="ti ti-box me-1"></i><span data-dtcv-field="2"></span></span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="5"></div>
                        </div>

                        <!-- Baris 2: Stok Komputer & Stok Fisik -->
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <div class="so-metric-box">
                                    <div class="text-muted small" style="font-size: 0.72rem;">Stok di Sistem:</div>
                                    <div class="fw-bold font-monospace fs-3 text-dark" data-dtcv-field="3"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="so-metric-box card-so-fisik-box">
                                    <div class="text-muted small" style="font-size: 0.72rem;">Stok Fisik Real:</div>
                                    <div class="fw-bolder font-monospace fs-3 card-so-fisik-val" data-dtcv-field="4"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Shortcut Fast Action -->
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <span class="small text-muted card-so-status-text">Status: Belum diinput</span>
                            <button type="button" class="btn btn-sm btn-primary px-3 card-so-btn-input">
                                <i class="ti ti-pencil me-1"></i> Input SO
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <h5 class="modal-title fw-bold fs-4 text-dark"><i class="ti ti-edit text-primary me-1"></i> Form Input Fisik SO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-edit">
                <div class="modal-body p-3">
                    <input type="hidden" id="kode_item" name="kode_item">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Produk</label>
                        <input type="text" class="form-control form-control-sm bg-light fw-bold text-dark" id="nama_item" readonly>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Stok Konversi</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="stok_konversi" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Satuan Dasar</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="sat_dasar" readonly>
                        </div>
                    </div>

                    <div class="p-3 bg-primary-subtle rounded-3 border border-primary-subtle mb-2">
                        <label class="form-label fw-bold text-primary mb-1">Jumlah Fisik (Real) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control form-control-lg font-monospace fw-bolder" id="qty_fisik" name="qty_fisik" placeholder="0.00" required>
                        <div class="form-text small text-muted">Ketik jumlah fisik yang dihitung pada rak/gudang toko.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold"><i class="ti ti-device-floppy me-1"></i> Simpan Hasil Fisik</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-history" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <h5 class="modal-title fw-bold fs-4 text-dark"><i class="ti ti-history text-info me-1"></i> Riwayat Input Fisik Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2 p-2 bg-light rounded border"><strong id="history-title" class="text-dark"></strong></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th class="text-end">Fisik Terinput</th>
                            </tr>
                        </thead>
                        <tbody id="history-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const editModal = new bootstrap.Modal(document.getElementById('modal-edit'));
    const historyModal = new bootstrap.Modal(document.getElementById('modal-history'));
    $(function() {
        $('#kat_id').select2({
            width: '100%'
        });
    });

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
        responsive: false,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/opname/ajax-input') ?>',
            type: 'post',
            data: function(d) {
                d.status_input = $('#status_input').val();
                d.kat_id = $('#kat_id').val();
            },
            dataSrc: function(json) {
                if (json?.tipe === 'error') {
                    toastr.error(json.message || 'Tidak ada SO aktif');
                    return [];
                }
                return json.data || [];
            }
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-so-input-template',
            gridClass: 'col-12 col-sm-6 mb-2',
            onCardRender: function($card, rowData) {
                const encoded = encodeURIComponent(JSON.stringify(rowData));
                const hasInput = (rowData.ttl !== null && rowData.ttl !== undefined && (Number(rowData.ttl) !== 0 || String(rowData.soid || '') !== ''));

                const $fisikBox = $card.find('.card-so-fisik-box');
                const $fisikVal = $card.find('.card-so-fisik-val');
                const $statusText = $card.find('.card-so-status-text');
                const $btnInput = $card.find('.card-so-btn-input');

                if (hasInput) {
                    $fisikBox.addClass('border-success-subtle bg-success-subtle');
                    $fisikVal.addClass('text-success');
                    $statusText.html('<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="ti ti-check"></i> Sudah diinput</span>');
                    $btnInput.removeClass('btn-primary').addClass('btn-outline-primary').html('<i class="ti ti-pencil me-1"></i> Edit Fisik');
                } else {
                    $fisikBox.addClass('border-warning-subtle bg-warning-subtle');
                    $fisikVal.addClass('text-warning');
                    $statusText.html('<span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="ti ti-clock"></i> Belum diinput</span>');
                    $btnInput.removeClass('btn-outline-primary').addClass('btn-primary').html('<i class="ti ti-pencil me-1"></i> Input Fisik');
                }

                $btnInput.on('click', function() {
                    openEdit(encoded);
                });
            }
        },
        columns: [{
                data: 'kode_item',
                title: 'Kode'
            },
            {
                data: 'nama_item',
                title: 'Nama'
            },
            {
                data: 'stok_konversi',
                title: 'Stok Konversi'
            },
            {
                data: 'com',
                title: 'Stok Data',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'ttl',
                title: 'Stok Fisik',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(row) {
                    const encoded = encodeURIComponent(JSON.stringify(row));
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm btn-light border px-2 py-1" data-bs-toggle="dropdown">Aksi</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item py-2" href="javascript:void(0)" onclick="openEdit('${encoded}')"><i class="ti ti-pencil text-warning me-2"></i> Input SO</a>
                            <a class="dropdown-item py-2" href="javascript:void(0)" onclick="showHistory('${row.kode_item}','${escapeHtml(row.nama_item)}')"><i class="ti ti-history text-info me-2"></i> History Input</a>
                        </div>
                    </span>`;
                }
            }
        ]
    });

    $('#btn-filter, #status_input').on('click change', function() {
        table.ajax.reload();
    });
    $('#kat_id').on('change', function() {
        table.ajax.reload();
    });

    function openEdit(encodedRow) {
        const row = JSON.parse(decodeURIComponent(encodedRow));
        $('#kode_item').val(row.kode_item || '');
        $('#nama_item').val(row.nama_item || '');
        $('#stok_konversi').val(row.stok_konversi || '');
        $('#sat_dasar').val(row.sat_dasar || '');
        let qty = Number(row.com || 0);
        const ttl = Number(row.ttl || 0);
        if (ttl !== 0 || String(row.soid || '') !== '') {
            qty = ttl;
        }
        $('#qty_fisik').val(qty);
        editModal.show();
    }

    $('#form-edit').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'PATCH',
            url: '<?= base_url('/opname/input-save') ?>',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Input SO berhasil disimpan');
                    editModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal simpan input SO');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal simpan input SO'));
            }
        });
    });

    function showHistory(kodeItem, namaItem) {
        $('#history-title').text(`${kodeItem} - ${namaItem}`);
        $('#history-body').html('<tr><td colspan="3" class="text-center text-muted py-3">Memuat data...</td></tr>');
        historyModal.show();
        $.post('<?= base_url('/opname/history-input') ?>', {
            kode_item: kodeItem
        }, function(res) {
            if (!Array.isArray(res) || !res.length) {
                $('#history-body').html('<tr><td colspan="3" class="text-center text-muted py-3">Belum ada riwayat input fisik</td></tr>');
                return;
            }
            const rows = res.map((row) => `
                <tr>
                    <td class="font-monospace small">${row.updtime || '-'}</td>
                    <td><span class="badge bg-light text-dark border">${row.updid || '-'}</span></td>
                    <td class="text-end font-monospace fw-bold text-success">${Number(row.ttl || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${row.sat_dasar || ''}</td>
                </tr>
            `).join('');
            $('#history-body').html(rows);
        }, 'json').fail(function(xhr) {
            $('#history-body').html(`<tr><td colspan="3" class="text-center text-danger py-3">${extractErrorMessage(xhr, 'Gagal memuat history')}</td></tr>`);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?= $this->endSection('javascript') ?>