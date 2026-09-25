<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">

        <!-- BEGIN PAGE HEADER -->
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-8 col-sm-9">
                        <h4 class="fw-semibold mb-1 fs-5 fs-md-4 text-truncate">Menu Navigasi</h4>
                        <p class="mb-0 text-muted small text-truncate"><span class="page-pretitle badge bg-primary-subtle text-primary fw-medium me-1">Total: 0</span> Manajemen struktur menu web</p>
                    </div>
                    <div class="col-4 col-sm-3 d-none d-sm-block">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE HEADER -->
        <!-- BEGIN PAGE BODY -->
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

    <!-- Template CardView Mobile untuk Data Menu -->
    <template id="card-menu-template">
        <div class="card shadow-sm border mb-2 h-100 menu-mobile-card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                    <div>
                        <span class="badge bg-primary-subtle text-primary font-monospace small px-2 py-1" data-dtcv-field="0"></span>
                        <h6 class="fw-bold text-dark mb-0 mt-1" data-dtcv-field="1"></h6>
                    </div>
                    <div data-dtcv-field="6"></div>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block small" style="font-size:0.75rem;">Link / URL</span>
                    <code class="text-primary small text-break" data-dtcv-field="2"></code>
                </div>
                <div class="row g-2 pt-2 border-top bg-light-subtle px-2 py-1 rounded small">
                    <div class="col-4">
                        <span class="text-muted d-block" style="font-size:0.72rem;">Icon</span>
                        <span data-dtcv-field="3"></span>
                    </div>
                    <div class="col-5">
                        <span class="text-muted d-block" style="font-size:0.72rem;">Header Parent</span>
                        <span class="text-dark fw-medium text-truncate d-block" data-dtcv-field="4"></span>
                    </div>
                    <div class="col-3 text-end">
                        <span class="text-muted d-block" style="font-size:0.72rem;">Urutan</span>
                        <span class="badge bg-secondary-subtle text-secondary" data-dtcv-field="5"></span>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- END PAGE BODY -->
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
                    title: 'Laporan-Menu',
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
            template: '#card-menu-template'
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
            url: '<?= base_url('/menu/ajax') ?>',
            type: 'post',
            data: {}
        },
        columns: [{
                data: 'menu_id',
                title: 'Menu ID'
            },
            {
                data: 'menu_name',
                title: 'Nama Menu'
            },
            {
                data: 'link',
                title: 'Link'
            },
            {
                data: 'icon',
                title: 'Icon',
                render: (d) => d ? `<i class="${d} me-1"></i> <span class="text-secondary small font-monospace">${d}</span>` : '<span class="text-muted">-</span>'
            },
            {
                data: 'header_menu',
                title: 'Header (Parent)'
            },
            {
                data: 'urutan',
                title: 'Urutan',
                className: 'dt-right'
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
            menu_id: 'required',
            menu_name: 'required',
            link: 'required',
            urutan: 'required'
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

    const menuFieldsConfig = [
        { name: 'menu_id', label: 'ID Menu *', placeholder: 'Contoh: lapcash' },
        { name: 'menu_name', label: 'Nama Menu *', placeholder: 'Contoh: Laporan Cash Flow' },
        { name: 'link', label: 'Link URL *', placeholder: 'Contoh: /lapcash' },
        { name: 'icon', label: 'Icon (Tabler / Feather)', placeholder: 'Contoh: ti ti-report-money' },
        { name: 'header_menu', label: 'Header Parent (Opsional)', placeholder: 'Contoh: Laporan' },
        { name: 'urutan', label: 'Nomor Urutan *', placeholder: 'Contoh: 1' }
    ];

    function showModal(action, data) {
        $("#modal-form > .modal-body").empty();
        menuFieldsConfig.forEach((item) => {
            $("#modal-form > .modal-body").append(`<div class="form-group mb-2">
          <label for="${item.name}" class="form-label small fw-semibold">${item.label}</label>
          <input type="text" class="form-control form-control-sm" name="${item.name}" id="${item.name}" placeholder="${item.placeholder}" />
        </div>`);
        });
        $("#modal-form > .modal-body").append(`<input type="hidden" id="_method" name="_method">`);
        $("#modal-form > .modal-body").append(`<input type="hidden" id="primarykey" name="primarykey">`);
        $("#btn-aksi").removeAttr('class');

        switch (action) {
            case 'tambah':
                $("#_method").val('PUT');
                $("#modal-title").html('Tambah Menu');
                $("#btn-aksi").html('Save');
                $("#btn-aksi").addClass('btn btn-success');
                break;
            case 'edit':
                $("#_method").val('PATCH');
                $("#primarykey").val(data.menu_id);
                $("#modal-title").html('Edit Menu');
                $("#btn-aksi").html('Update');
                $("#btn-aksi").addClass('btn btn-warning');
                $('#menu_id').prop('readonly', true);
                break;
            case 'delete':
                $("#_method").val('DELETE');
                $("#primarykey").val(data.menu_id);
                $("#modal-title").html('Delete Menu');
                $("#modal-web input").attr('readonly', true);
                $("#modal-web select").attr('disabled', true);
                $("#btn-aksi").html('Delete');
                $("#btn-aksi").addClass('btn btn-danger');
                $("#btn-aksi").prop('disabled', false);
                break;
        }
        if (data) {
            for (const k in data) {
                $('#' + k).val(data[k]);
            }
        }
        $("#loadingOverlay").addClass('d-none');
        $("#modal-web").modal('show');
    }

    function saveAjax() {
        const formData = $("#modal-form").serializeArray();
        const arrNumbers = ['urutan'];
        formData.forEach((fd) => {
            if (arrNumbers.includes(fd.name)) fd.value = fd.value.replace(/\D/g, '');
        });
        $("#loadingOverlay").removeClass('d-none');
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/menu') ?>',
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