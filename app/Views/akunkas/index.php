<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-success-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Akun Kas</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Master global kategori pemasukan dan pengeluaran kas operasional.</p>
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

<div class="modal fade" id="modal-akun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Akun Kas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-akun">
                <div class="modal-body">
                    <input type="hidden" id="old_nama_akun">
                    <div class="mb-2">
                        <label class="form-label">Nama Akun</label>
                        <input type="text" class="form-control" id="nama_akun" maxlength="50" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jenis Akun</label>
                        <select class="form-select" id="jenis_akun" required>
                            <option value="MASUK">MASUK</option>
                            <option value="KELUAR">KELUAR</option>
                        </select>
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
    const akunModal = new bootstrap.Modal(document.getElementById('modal-akun'));
    let modalMode = 'create';

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah',
                    action: function() {
                        if (akses_menu?.akses_create === 'Y') {
                            openModal('create');
                            return;
                        }
                        toastr.error('Anda tidak memiliki akses untuk ini!');
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
            url: '<?= base_url('/akunkas/ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'nama_akun',
                title: 'Nama Akun'
            },
            {
                data: 'jenis_akun',
                title: 'Jenis',
                className: 'text-center',
                render: data => data === 'MASUK' ? '<span class="badge bg-success-subtle text-success">MASUK</span>' : '<span class="badge bg-danger-subtle text-danger">KELUAR</span>'
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                responsivePriority: 1,
                render: function(row) {
                    const editMenu = akses_menu?.akses_update === 'Y' ? `<a class="dropdown-item" href="javascript:void(0)" onclick='openModal("edit", ${JSON.stringify(row)})'><i class="ti ti-pencil text-warning"></i> Edit</a>` : '';
                    const deleteMenu = akses_menu?.akses_delete === 'Y' ?
                        (Number(row.is_locked || 0) === 1 ?
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="toastr.error('Akun kas sudah dipakai transaksi dan tidak boleh dihapus')"><i class="ti ti-lock text-danger"></i> Hapus Terkunci</a>` :
                            `<a class="dropdown-item" href="javascript:void(0)" onclick='deleteAkun("${row.nama_akun}")'><i class="ti ti-trash text-danger"></i> Hapus</a>`) :
                        '';
                    return `<span class="dropdown">
                        <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-toggle="dropdown">Actions</button>
                        <div class="dropdown-menu dropdown-menu-end">${editMenu}${deleteMenu}</div>
                    </span>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsTotal || 0}`);
    });

    function openModal(mode, row = null) {
        modalMode = mode;
        $('#form-akun')[0].reset();
        $('#nama_akun').prop('readonly', false);
        $('#old_nama_akun').val('');
        $('#btn-save').show();

        if (mode === 'edit' && row) {
            $('#old_nama_akun').val(row.nama_akun || '');
            $('#nama_akun').val(row.nama_akun || '');
            $('#jenis_akun').val(row.jenis_akun || 'KELUAR');
            $('.modal-title').text('Edit Akun Kas');
            $('#btn-save').text('Update');
        } else {
            $('.modal-title').text('Tambah Akun Kas');
            $('#btn-save').text('Simpan');
        }

        akunModal.show();
    }

    $('#form-akun').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: modalMode === 'create' ? 'PUT' : 'PATCH',
            url: '<?= base_url('/akunkas') ?>',
            dataType: 'json',
            data: {
                old_nama_akun: $('#old_nama_akun').val(),
                nama_akun: $('#nama_akun').val(),
                jenis_akun: $('#jenis_akun').val()
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Berhasil');
                    akunModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan akun kas'));
            }
        });
    });

    function deleteAkun(namaAkun) {
        Swal.fire({
            title: `Hapus akun kas ${namaAkun} ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/akunkas') ?>',
                dataType: 'json',
                data: {
                    nama_akun: namaAkun
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Berhasil');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus akun kas'));
                }
            });
        });
    }
</script>
<?= $this->endSection('javascript') ?>