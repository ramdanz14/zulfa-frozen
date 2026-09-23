<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<style>
    /* Styling responsif mobile untuk Recipe Konversi & CardView */
    .recipe-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .recipe-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .recipe-item-title {
        font-weight: 700;
        color: var(--bs-heading-color);
        line-height: 1.3;
        font-size: 0.95rem;
    }
    .recipe-flow-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-1">Setting Recipe Konversi</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold text-dark">Total</span> | Formula standar item bahan asal menjadi item hasil bundling/repacking.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <a href="<?= base_url('/konversi') ?>" class="btn btn-outline-secondary btn-sm px-3"><i class="ti ti-arrow-left me-1"></i> Kembali ke Konversi</a>
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

                <!-- CardView Template for Recipe Konversi (Mobile Reflow) -->
                <template id="card-recipe-template">
                    <div class="recipe-card mb-2">
                        <!-- Baris 1: Item Hasil & Action Dropdown -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div class="min-w-0 flex-grow-1">
                                <span class="badge bg-success-subtle text-success border border-success-subtle small mb-1">
                                    <i class="ti ti-package-import me-1"></i> Produk Jadi Hasil
                                </span>
                                <div class="recipe-item-title text-truncate" data-dtcv-field="2"></div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="4"></div>
                        </div>

                        <!-- Baris 2: Bahan Baku Asal -->
                        <div class="mb-2">
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle small mb-1">
                                <i class="ti ti-package-export text-warning me-1"></i> Bahan Baku Asal
                            </span>
                            <div data-dtcv-field="0"></div>
                        </div>

                        <!-- Baris 3: Rasio Formula Konversi -->
                        <div class="recipe-flow-box d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.72rem;">Qty Bahan Diambil:</span>
                                <strong class="font-monospace text-dark" data-dtcv-field="1"></strong>
                            </div>
                            <div class="text-center px-2">
                                <i class="ti ti-arrow-right fs-4 text-primary"></i>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small d-block" style="font-size: 0.72rem;">Qty Hasil Jadi:</span>
                                <strong class="font-monospace text-success" data-dtcv-field="3"></strong>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-recipe" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <h5 class="modal-title fw-bold fs-4 text-dark"><i class="ti ti-settings text-primary me-1"></i> Recipe Konversi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="recipe-form">
                <div class="modal-body p-3">
                    <input type="hidden" id="recipe_id">
                    
                    <!-- Section Bahan Asal -->
                    <div class="p-3 bg-warning-subtle rounded-3 border border-warning-subtle mb-3">
                        <div class="fw-bold text-dark small mb-2"><i class="ti ti-package-export text-warning me-1"></i> Komponen Bahan Baku (Asal)</div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Pilih Item Asal <span class="text-danger">*</span></label>
                            <select class="form-select select2-item" id="kode_item_asal" style="width:100%;"></select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Satuan Asal</label>
                                <select class="form-select form-select-sm" id="sat_asal"></select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Qty Asal <span class="text-danger">*</span></label>
                                <input type="number" min="0.01" step="0.01" class="form-control form-control-sm font-monospace fw-bold" id="qty_asal" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section Item Hasil -->
                    <div class="p-3 bg-success-subtle rounded-3 border border-success-subtle mb-2">
                        <div class="fw-bold text-success small mb-2"><i class="ti ti-package-import me-1"></i> Komponen Produk Jadi (Hasil)</div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Pilih Item Hasil <span class="text-danger">*</span></label>
                            <select class="form-select select2-item" id="kode_item_hasil" style="width:100%;"></select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Satuan Hasil</label>
                                <select class="form-select form-select-sm" id="sat_hasil"></select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Qty Hasil <span class="text-danger">*</span></label>
                                <input type="number" min="0.01" step="0.01" class="form-control form-control-sm font-monospace fw-bold text-success" id="qty_hasil" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold" id="btn-save"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const recipeModal = new bootstrap.Modal(document.getElementById('modal-recipe'));
    let modalMode = 'create';

    $(function() {
        $('.select2-item').select2({
            width: '100%',
            placeholder: 'Ketik nama / barcode item...',
            dropdownParent: $('#modal-recipe'),
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url('/konversi/search-item') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    term: params.term || ''
                }),
                processResults: data => data
            }
        });

        $('#kode_item_asal').on('select2:select', function(e) {
            loadItemUnits(e.params.data.id, '#sat_asal');
        });
        $('#kode_item_hasil').on('select2:select', function(e) {
            loadItemUnits(e.params.data.id, '#sat_hasil');
        });
    });

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah Recipe',
                    className: 'btn btn-primary btn-sm px-3',
                    action: function() {
                        if (akses_menu?.akses_update !== 'Y') {
                            toastr.error('Anda tidak memiliki akses untuk ini!');
                            return;
                        }
                        openModal('create');
                    }
                }, 'pageLength']
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
            url: '<?= base_url('/konversi/recipe-ajax') ?>',
            type: 'post'
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-recipe-template',
            gridClass: 'col-12 col-sm-6 mb-2'
        },
        columns: [{
                data: 'kode_item_asal',
                title: 'Item Asal',
                render: function(data, type, row) {
                    return `<div class="fw-semibold text-dark">${row.nama_item_asal || data}</div><small class="text-muted font-monospace">${data} | ${row.sat_asal}</small>`;
                }
            },
            {
                data: 'qty_asal',
                title: 'Qty Asal',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                })
            },
            {
                data: 'kode_item_hasil',
                title: 'Item Hasil',
                render: function(data, type, row) {
                    return `<div class="fw-semibold text-success">${row.nama_item_hasil || data}</div><small class="text-muted font-monospace">${data} | ${row.sat_hasil}</small>`;
                }
            },
            {
                data: 'qty_hasil',
                title: 'Qty Hasil',
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
                    const editMenu = akses_menu?.akses_update === 'Y' ? `<a class="dropdown-item py-2" href="javascript:void(0)" onclick='openModal("edit", ${JSON.stringify(row)})'><i class="ti ti-pencil text-warning me-2"></i> Edit</a>` : '';
                    const deleteMenu = akses_menu?.akses_update === 'Y' ? `<a class="dropdown-item py-2" href="javascript:void(0)" onclick='deleteRecipe(${row.recipe_id})'><i class="ti ti-trash text-danger me-2"></i> Hapus</a>` : '';
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm btn-light border px-2 py-1" data-bs-toggle="dropdown">Aksi</button>
                        <div class="dropdown-menu dropdown-menu-end">
                            ${editMenu}
                            ${deleteMenu}
                        </div>
                    </span>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsTotal || 0}`);
    });

    function loadItemUnits(kodeItem, targetSelector, selectedSat = null) {
        $.getJSON(`<?= base_url('/konversi/item-detail') ?>/${encodeURIComponent(kodeItem)}`, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Item tidak ditemukan');
                return;
            }
            const select = $(targetSelector);
            const satuan = res.data?.satuan || [];
            select.html(satuan.map(row => `<option value="${row.sat_id}" ${selectedSat === row.sat_id ? 'selected' : ''}>${row.sat_id} (${Number(row.qty_konversi || 1).toLocaleString('id-ID', { maximumFractionDigits: 2 })})</option>`).join(''));
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat satuan item'));
        });
    }

    function setItemSelect(selector, kodeItem, labelText) {
        const option = new Option(labelText, kodeItem, true, true);
        $(selector).append(option).trigger('change');
    }

    function openModal(mode, row = null) {
        modalMode = mode;
        $('#recipe-form')[0].reset();
        $('#recipe_id').val(row?.recipe_id || '');
        $('#kode_item_asal').empty().trigger('change');
        $('#kode_item_hasil').empty().trigger('change');
        $('#sat_asal').html('');
        $('#sat_hasil').html('');

        if (mode === 'edit' && row) {
            setItemSelect('#kode_item_asal', row.kode_item_asal, `${row.kode_item_asal} - ${row.nama_item_asal || row.kode_item_asal}`);
            setItemSelect('#kode_item_hasil', row.kode_item_hasil, `${row.kode_item_hasil} - ${row.nama_item_hasil || row.kode_item_hasil}`);
            loadItemUnits(row.kode_item_asal, '#sat_asal', row.sat_asal);
            loadItemUnits(row.kode_item_hasil, '#sat_hasil', row.sat_hasil);
            $('#qty_asal').val(row.qty_asal || 0);
            $('#qty_hasil').val(row.qty_hasil || 0);
            $('.modal-title').html('<i class="ti ti-pencil text-warning me-1"></i> Edit Recipe Konversi');
            $('#btn-save').html('<i class="ti ti-device-floppy me-1"></i> Update Recipe');
        } else {
            $('.modal-title').html('<i class="ti ti-settings text-primary me-1"></i> Tambah Recipe Konversi');
            $('#btn-save').html('<i class="ti ti-device-floppy me-1"></i> Simpan Recipe');
        }
        recipeModal.show();
    }

    $('#recipe-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: modalMode === 'create' ? 'PUT' : 'PATCH',
            url: '<?= base_url('/konversi/recipe') ?>',
            dataType: 'json',
            data: {
                recipe_id: $('#recipe_id').val(),
                kode_item_asal: $('#kode_item_asal').val(),
                sat_asal: $('#sat_asal').val(),
                qty_asal: $('#qty_asal').val(),
                kode_item_hasil: $('#kode_item_hasil').val(),
                sat_hasil: $('#sat_hasil').val(),
                qty_hasil: $('#qty_hasil').val()
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Recipe berhasil disimpan');
                    recipeModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan recipe');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan recipe'));
            }
        });
    });

    function deleteRecipe(recipeId) {
        Swal.fire({
            title: 'Hapus recipe konversi ini?',
            text: 'Formula ini tidak akan bisa dipilih lagi pada saat proses konversi baru.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-trash"></i> Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger px-3 me-2',
                cancelButton: 'btn btn-light px-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/konversi/recipe') ?>',
                dataType: 'json',
                data: {
                    recipe_id: recipeId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Recipe berhasil dihapus');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal menghapus recipe');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus recipe'));
                }
            });
        });
    }
</script>
<?= $this->endSection('javascript') ?>
