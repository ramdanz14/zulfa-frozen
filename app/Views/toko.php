<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-8 col-sm-9">
                        <h4 class="fw-semibold mb-1 fs-5 fs-md-4 text-truncate">Toko</h4>
                        <p class="mb-0 text-muted small text-truncate"><span class="page-pretitle badge bg-primary-subtle text-primary fw-medium me-1">Total: 0</span> Manajemen data toko & cabang</p>
                    </div>
                    <div class="col-4 col-sm-3 d-none d-sm-block">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl p-0 p-md-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-2 p-md-3">
                                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
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
            </div>
        </div>
    </div>

    <!-- Template CardView Mobile untuk Data Toko -->
    <template id="card-toko-template">
        <div class="card shadow-sm border mb-2 h-100 toko-mobile-card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                    <div>
                        <span class="badge bg-primary-subtle text-primary font-monospace small px-2 py-1" data-dtcv-field="0"></span>
                        <h6 class="fw-bold text-dark mb-0 mt-1" data-dtcv-field="1"></h6>
                    </div>
                    <div data-dtcv-field="6"></div>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block small" style="font-size:0.75rem;">Alamat</span>
                    <span class="text-dark small text-break" data-dtcv-field="2"></span>
                </div>
                <div class="row g-2 pt-2 border-top bg-light-subtle px-2 py-1 rounded small">
                    <div class="col-6">
                        <span class="text-muted d-block" style="font-size:0.72rem;">Telepon</span>
                        <span class="text-muted fw-medium" data-dtcv-field="3"></span>
                    </div>
                    <div class="col-6 text-end">
                        <span class="text-muted d-block" style="font-size:0.72rem;">Gudang</span>
                        <span data-dtcv-field="5"></span>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<div class="modal fade" id="modal-web" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div id="loadingOverlay" class="d-flex justify-content-center align-items-center" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.7); z-index: 1051;">
                <i class="fas fa-2x fa-sync fa-spin text-primary"></i>
            </div>
            <div class="modal-header py-2 px-3">
                <h5 class="modal-title fs-5" id="modal-title">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modal-form">
                <div class="modal-body p-3"></div>
                <div class="modal-footer p-2 justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary" style="min-height:38px; min-width:80px;" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-sm btn-primary" style="min-height:38px; min-width:90px;" id="btn-aksi">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';

    const themeOptions = [
        "Blue_Theme",
        "Aqua_Theme",
        "Purple_Theme",
        "Cyan_Theme",
        "Orange_Theme"
    ];

    const table = $("#table-data").DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah',
                    className: 'btn btn-primary btn-touch-target',
                    action: function() {
                        if (akses_menu?.akses_create === "Y") {
                            showModal('tambah');
                        } else {
                            toastr.error('Anda tidak memiliki akses untuk ini!');
                        }
                    }
                }, {
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    className: 'btn btn-primary btn-touch-target',
                    title: 'Laporan-Toko',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5],
                        orthogonal: 'export'
                    },
                }, 'cardViewToggle', "pageLength"]
            }
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            columns: {
                xs: 1,
                sm: 1,
                md: 2,
                lg: 3
            },
            template: '#card-toko-template'
        },
        lengthMenu: [
            [25, 50, 100, -1],
            ["25 rows", "50 rows", "100 rows", "Show all"]
        ],
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        bProcessing: true,
        ordering: false,
        serverSide: true,
        ajax: {
            url: '<?= base_url('/listoko/ajax') ?>',
            type: 'post',
            data: {}
        },
        columns: [{
                data: 'toko_id',
                title: 'Toko ID'
            },
            {
                data: 'toko_nama',
                title: 'Nama Toko'
            },
            {
                data: 'toko_alamat',
                title: 'Alamat',
                className: "not-mobile",
            },
            {
                data: 'toko_phone',
                title: 'Phone',
                className: "not-mobile",
            },
            {
                data: 'toko_theme',
                title: 'Theme',
                className: "not-mobile",
            },
            {
                data: 'flag_gudang',
                title: 'Gudang',
                render: function(data) {
                    return data === "Y" ? '<span class="badge bg-success-subtle text-success"><i class="ti ti-check me-1"></i>Ya</span>' : '<span class="badge bg-secondary-subtle text-secondary"><i class="ti ti-minus me-1"></i>Tidak</span>';
                }
            },
            {
                title: 'Action',
                class: 'dt-center',
                responsivePriority: 1,
                data: null,
                render: function(data) {
                    const rowJson = JSON.stringify(data).replace(/"/g, '&quot;');
                    const editMenu = akses_menu?.akses_update === 'Y' ? `<li><a class='dropdown-item py-2' href='javascript:void(0)' onclick='showModal("edit", ${rowJson})'><i class='ti ti-pencil text-warning me-2'></i> Edit</a></li>` : '';
                    const deleteMenu = akses_menu?.akses_delete === 'Y' ? `<li><a class='dropdown-item py-2 text-danger' href='javascript:void(0)' onclick='showModal("delete", ${rowJson})'><i class='ti ti-trash text-danger me-2'></i> Hapus</a></li>` : '';
                    return `<div class="dropdown">
                          <button class="btn btn-sm btn-outline-secondary dropdown-toggle btn-touch-target" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" style="min-height:36px; padding:4px 10px;">Aksi</button>
                          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            ${editMenu}
                            ${deleteMenu ? '<li><hr class="dropdown-divider my-1"></li>' + deleteMenu : ''}
                          </ul>
                        </div>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $(".page-pretitle").text(`Total: ` + (json?.recordsTotal || 0));
    });

    $('#modal-form').validate({
        rules: {
            toko_id: 'required',
            toko_nama: 'required',
            toko_alamat: 'required',
            toko_phone: 'required',
            toko_theme: 'required'
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function(el) {
            $(el).addClass('is-invalid');
        },
        unhighlight: function(el) {
            $(el).removeClass('is-invalid');
        },
        submitHandler: function() {
            saveAjax();
        }
    });

    function buildThemeOptions(selected = "") {
        let html = '<option value="">Pilih Theme</option>';
        themeOptions.forEach((item) => {
            const selectedAttr = item === selected ? "selected" : "";
            html += `<option value="${item}" ${selectedAttr}>${item}</option>`;
        });
        return html;
    }

    function showModal(action, data) {
        $("#modal-form > .modal-body").empty();
        $("#modal-form > .modal-body").append(`<div class="form-group mb-2">
          <label for="toko_id" class="form-label small fw-semibold">ID Toko</label>
          <input type="text" class="form-control form-control-sm" name="toko_id" id="toko_id" readonly />
        </div>`);
        $("#modal-form > .modal-body").append(`<div class="form-group mb-2">
          <label for="toko_nama" class="form-label small fw-semibold">Nama Toko *</label>
          <input type="text" class="form-control form-control-sm" name="toko_nama" id="toko_nama" required />
        </div>`);
        $("#modal-form > .modal-body").append(`<div class="form-group mb-2">
          <label for="toko_alamat" class="form-label small fw-semibold">Alamat Toko *</label>
          <textarea class="form-control form-control-sm" rows="2" name="toko_alamat" id="toko_alamat" required></textarea>
        </div>`);
        $("#modal-form > .modal-body").append(`<div class="form-group mb-2">
          <label for="toko_phone" class="form-label small fw-semibold">Nomor Telepon *</label>
          <input type="text" class="form-control form-control-sm" name="toko_phone" id="toko_phone" required />
        </div>`);
        $("#modal-form > .modal-body").append(`<div class="form-group mb-2">
          <label for="toko_theme" class="form-label small fw-semibold">Warna Tema UI *</label>
          <select class="form-select form-select-sm" name="toko_theme" id="toko_theme" required>${buildThemeOptions()}</select>
        </div>`);
        $("#modal-form > .modal-body").append(`<div class="form-group mb-2">
          <label for="flag_gudang" class="form-label small fw-semibold">Status Gudang Utama</label>
          <select class="form-select form-select-sm" name="flag_gudang" id="flag_gudang">
            <option value="N">Bukan Gudang (Toko Cabang Biasa)</option>
            <option value="Y">Gudang Utama (Pusat Distribusi)</option>
          </select>
        </div>`);
        $("#modal-form > .modal-body").append(`<input type="hidden" id="_method" name="_method">`);
        $("#modal-form > .modal-body").append(`<input type="hidden" id="primarykey" name="primarykey">`);
        $("#btn-aksi").removeAttr('class');
        $("#modal-web input").attr('readonly', false);
        $("#modal-web select").attr('disabled', false);

        switch (action) {
            case 'tambah':
                $("#_method").val('PUT');
                $("#modal-title").html('Tambah Toko');
                $("#btn-aksi").html('Save');
                $("#btn-aksi").addClass('btn btn-success');
                $.ajax({
                    type: 'GET',
                    url: '<?= base_url('/listoko/lastid') ?>',
                    dataType: 'json',
                    success: function(res) {
                        if (res?.tipe === 'success') {
                            $('#toko_id').val(res.data || '');
                            $('#toko_id').prop('readonly', true);

                        }
                    }
                });
                break;
            case 'edit':
                $("#_method").val('PATCH');
                $("#primarykey").val(data.toko_id);
                $("#modal-title").html('Edit Toko');
                $("#btn-aksi").html('Update');
                $("#btn-aksi").addClass('btn btn-warning');
                if (data) {
                    $('#toko_id').val(data.toko_id || '');
                    $('#toko_nama').val(data.toko_nama || '');
                    $('#toko_alamat').val(data.toko_alamat || '');
                    $('#toko_phone').val(data.toko_phone || '');
                    $('#toko_theme').html(buildThemeOptions(data.toko_theme || ''));
                    $('#flag_gudang').val(data.flag_gudang || 'N');
                }
                break;
            case 'delete':
                $("#_method").val('DELETE');
                $("#primarykey").val(data.toko_id);
                $("#modal-title").html('Delete Toko');
                $("#modal-web input").attr('readonly', true);
                $("#modal-web select").attr('disabled', true);
                $("#btn-aksi").html('Delete');
                $("#btn-aksi").addClass('btn btn-danger');
                $("#btn-aksi").prop('disabled', false);
                if (data) {
                    $('#toko_id').val(data.toko_id || '');
                    $('#toko_nama').val(data.toko_nama || '');
                    $('#toko_alamat').val(data.toko_alamat || '');
                    $('#toko_phone').val(data.toko_phone || '');
                    $('#toko_theme').html(buildThemeOptions(data.toko_theme || ''));
                    $('#flag_gudang').val(data.flag_gudang || 'N');
                }
                break;
        }
        $("#loadingOverlay").addClass('d-none');
        $("#modal-web").modal('show');
    }

    function saveAjax() {
        const formData = $("#modal-form").serializeArray();
        $("#loadingOverlay").removeClass('d-none');
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/listoko') ?>',
            dataType: 'json',
            data: formData,
            success: function(res) {
                $("#loadingOverlay").addClass('d-none');
                $("#modal-web").modal('hide');
                if (res.tipe === "success") {
                    toastr.success(res.data || "Berhasil");
                } else {
                    toastr.error(res.data || "Gagal");
                }
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                $("#loadingOverlay").addClass('d-none');
                $("#modal-web").modal('hide');
                alert(xhr.responseText);
            }
        });
    }
</script>
<?= $this->endSection('javascript') ?>