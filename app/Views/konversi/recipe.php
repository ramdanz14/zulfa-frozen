<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Setting Recipe Konversi</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Formula global item asal ke item hasil untuk bundling dan repacking.</p>
                    </div>
                    <div class="col-3">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
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

<div class="modal fade" id="modal-recipe" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Recipe Konversi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="recipe-form">
                <div class="modal-body">
                    <input type="hidden" id="recipe_id">
                    <div class="mb-2">
                        <label class="form-label">Item Asal</label>
                        <select class="form-select select2-item" id="kode_item_asal" style="width:100%;"></select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Satuan Asal</label>
                        <select class="form-select" id="sat_asal"></select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Qty Asal</label>
                        <input type="number" min="0.01" step="0.01" class="form-control" id="qty_asal" required>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <label class="form-label">Item Hasil</label>
                        <select class="form-select select2-item" id="kode_item_hasil" style="width:100%;"></select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Satuan Hasil</label>
                        <select class="form-select" id="sat_hasil"></select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Qty Hasil</label>
                        <input type="number" min="0.01" step="0.01" class="form-control" id="qty_hasil" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save">Simpan</button>
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
            placeholder: 'Pilih item',
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
                    text: '<i class="ti ti-plus"></i> Tambah',
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
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/konversi/recipe-ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'kode_item_asal',
                title: 'Item Asal',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${row.nama_item_asal || data}</div><small class="text-muted">${data} | ${row.sat_asal}</small>`;
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
                    return `<div class="fw-semibold">${row.nama_item_hasil || data}</div><small class="text-muted">${data} | ${row.sat_hasil}</small>`;
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
                    const editMenu = akses_menu?.akses_update === 'Y' ? `<a class="dropdown-item" href="javascript:void(0)" onclick='openModal("edit", ${JSON.stringify(row)})'><i class="ti ti-pencil text-warning"></i> Edit</a>` : '';
                    const deleteMenu = akses_menu?.akses_update === 'Y' ? `<a class="dropdown-item" href="javascript:void(0)" onclick='deleteRecipe(${row.recipe_id})'><i class="ti ti-trash text-danger"></i> Hapus</a>` : '';
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
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
            $('.modal-title').text('Edit Recipe Konversi');
            $('#btn-save').text('Update');
        } else {
            $('.modal-title').text('Tambah Recipe Konversi');
            $('#btn-save').text('Simpan');
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
                    toastr.success(res.data || 'Berhasil');
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
            title: 'Hapus recipe ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
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
                        toastr.success(res.data || 'Berhasil');
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
