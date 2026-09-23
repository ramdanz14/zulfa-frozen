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
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-2">Akun Kas</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Master global kategori pemasukan dan pengeluaran kas operasional.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0 d-none d-lg-block">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border">
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0 table-light thead">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td>No data to show</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView template for akun kas -->
                <template id="card-akunkas-template">
                    <div class="card border mb-2 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="min-width:0; flex:1;">
                                    <div class="fw-semibold text-truncate" data-dtcv-field="0"></div>
                                    <div class="d-flex gap-2 mt-2 flex-wrap">
                                        <!-- jenis akun badge — already rendered by DT render() -->
                                        <div data-dtcv-field="1"></div>
                                        <!-- beban usaha badge — already rendered by DT render() -->
                                        <div data-dtcv-field="2"></div>
                                    </div>
                                </div>
                                <div class="ms-2 flex-shrink-0" data-dtcv-field="3"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- Modal Akun Kas -->
<div class="modal fade" id="modal-akun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <h5 class="modal-title fw-semibold"><i class="ti ti-wallet me-2 text-success"></i>Akun Kas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-akun">
                <div class="modal-body p-3">
                    <input type="hidden" id="old_nama_akun">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Akun</label>
                        <input type="text" class="form-control form-control-sm" id="nama_akun" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jenis Akun</label>
                        <select class="form-select form-select-sm" id="jenis_akun" required>
                            <option value="MASUK">MASUK</option>
                            <option value="KELUAR">KELUAR</option>
                        </select>
                    </div>
                    <div class="mb-1" id="flag-beban-wrapper">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="flag_beban">
                            <label class="form-check-label small fw-semibold" for="flag_beban">Beban Usaha</label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Akun keluar yang ditandai sebagai beban usaha akan dipakai sebagai komponen BEBAN USAHA untuk perhitungan laba bersih.
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-3" id="btn-save">Simpan</button>
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

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary btn-sm';
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
        responsive: false,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/akunkas/ajax') ?>',
            type: 'post'
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-akunkas-template',
            gridClass: 'col-12 col-sm-6 mb-2'
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
                data: 'flag_beban',
                title: 'Beban Usaha',
                className: 'text-center',
                render: data => data === 'Y' ? '<span class="badge bg-warning-subtle text-warning">YA</span>' : '<span class="badge bg-secondary-subtle text-secondary">TIDAK</span>'
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
        $('#flag_beban').prop('checked', false);
        $('#btn-save').show();

        if (mode === 'edit' && row) {
            $('#old_nama_akun').val(row.nama_akun || '');
            $('#nama_akun').val(row.nama_akun || '');
            $('#jenis_akun').val(row.jenis_akun || 'KELUAR');
            $('#flag_beban').prop('checked', row.flag_beban === 'Y');
            $('.modal-title').html('<i class="ti ti-pencil me-2 text-warning"></i>Edit Akun Kas');
            $('#btn-save').text('Update');
        } else {
            $('.modal-title').html('<i class="ti ti-wallet me-2 text-success"></i>Tambah Akun Kas');
            $('#btn-save').text('Simpan');
        }

        toggleFlagBeban();
        akunModal.show();
    }

    function toggleFlagBeban() {
        const isKeluar = $('#jenis_akun').val() === 'KELUAR';
        $('#flag-beban-wrapper').toggle(isKeluar);
        if (!isKeluar) {
            $('#flag_beban').prop('checked', false);
        }
    }

    $('#jenis_akun').on('change', toggleFlagBeban);

    $('#form-akun').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
        $.ajax({
            type: modalMode === 'create' ? 'PUT' : 'PATCH',
            url: '<?= base_url('/akunkas') ?>',
            dataType: 'json',
            data: {
                old_nama_akun: $('#old_nama_akun').val(),
                nama_akun: $('#nama_akun').val(),
                jenis_akun: $('#jenis_akun').val(),
                flag_beban: $('#flag_beban').is(':checked') ? 'Y' : 'N'
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
            },
            complete: function() {
                btn.prop('disabled', false).text(modalMode === 'create' ? 'Simpan' : 'Update');
            }
        });
    });

    function deleteAkun(namaAkun) {
        Swal.fire({
            title: `Hapus akun kas "${namaAkun}"?`,
            icon: 'warning',
            showCancelButton: true,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger px-3 me-2',
                cancelButton: 'btn btn-light px-3'
            },
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
