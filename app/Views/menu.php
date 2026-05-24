<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="body-wrapper">
    <div class="container-fluid p-0">

        <!-- BEGIN PAGE HEADER -->
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Menu</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Manajemen Web Menu. </p>
                    </div>
                    <div class="col-3">
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
            <div class="container-xl">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body p-2">
                                <table id="table-data" class="table table-bordered table-hover table-striped table-sm table-head-fixed">
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

    <!-- END PAGE BODY -->
</div>
<div class="modal fade" id="modal-web" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div id="loadingOverlay" class="d-flex justify-content-center align-items-center" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.7); z-index: 1051;">
                <i class="fas fa-2x fa-sync fa-spin text-primary"></i>
            </div>
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modal-form">
                <div class="modal-body"></div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" class="btn btn-primary" id="btn-aksi">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                    title: 'Laporan-Menu',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5],
                        orthogonal: 'export'
                    },
                }, "pageLength"]
            }
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
                render: (d) => d ? `<i class="${d}"></i> <span class="text-secondary">${d}</span>` : ''
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
                    const editMenu = akses_menu?.akses_update === 'Y' ? `<a class='dropdown-item' onclick='showModal("edit",${JSON.stringify(data)})'><i class='ti ti-pencil text-warning'></i> Edit</a>` : '';
                    const deleteMenu = akses_menu?.akses_delete === 'Y' ? `<a class='dropdown-item' onclick='showModal("delete",${JSON.stringify(data)})'><i class='ti ti-trash-x text-danger'></i> Hapus</a>` : '';
                    return `<span class="dropdown">
                          <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-boundary="viewport" data-bs-toggle="dropdown">Actions</button>
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
        $(".page-pretitle").text(`Total Data : ` + (json?.recordsTotal || 0));
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

    function showModal(action, data) {
        $("#modal-form > .modal-body").empty();
        const list = ['menu_id', 'menu_name', 'link', 'icon', 'header_menu', 'urutan'];
        list.forEach((item) => {
            $("#modal-form > .modal-body").append(`<div class="form-group mb-1">
          <label for="${item}" class="form-label">${item.toUpperCase()}</label>
          <input type="text" class="form-control" name="${item}" id="${item}" />
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