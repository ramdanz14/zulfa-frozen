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
                        <h4 class="fw-semibold mb-1 fs-5 fs-md-4 text-truncate">Karyawan</h4>
                        <p class="mb-0 text-muted small text-truncate"><span class="page-pretitle badge bg-primary-subtle text-primary fw-medium me-1">Total: 0</span> Kelola user & hak akses</p>
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

    <!-- Template CardView Mobile untuk Karyawan -->
    <template id="card-user-template">
        <div class="card shadow-sm border mb-2 h-100 user-mobile-card">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                    <div>
                        <span class="badge bg-primary-subtle text-primary font-monospace small px-2 py-1" data-dtcv-field="0"></span>
                        <h6 class="fw-bold text-dark mb-0 mt-1" data-dtcv-field="1"></h6>
                    </div>
                    <div data-dtcv-field="8"></div>
                </div>
                <div class="row g-2 small mb-2">
                    <div class="col-6">
                        <span class="text-muted d-block" style="font-size:0.75rem;">Level</span>
                        <span class="fw-semibold text-dark" data-dtcv-field="3"></span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block" style="font-size:0.75rem;">Toko</span>
                        <span class="fw-semibold text-dark" data-dtcv-field="6"></span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block" style="font-size:0.75rem;">Telepon</span>
                        <span class="text-muted" data-dtcv-field="2"></span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block" style="font-size:0.75rem;">Gaji Harian</span>
                        <span class="fw-bold text-success" data-dtcv-field="7"></span>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top bg-light-subtle px-2 py-1 rounded">
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted small">Status:</span>
                        <span data-dtcv-field="4"></span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted small">Absensi:</span>
                        <span data-dtcv-field="5"></span>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- END PAGE BODY -->
</div>

<div class="modal fade" id="user-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2 px-3">
                <h5 class="modal-title fs-5" id="user-modal-title">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="user-form">
                <div class="modal-body p-3">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Karyawan ID *</label>
                            <input type="text" class="form-control form-control-sm" id="karyawan_id" name="karyawan_id" required readonly>
                            <small class="text-danger" data-error="karyawan_id"></small>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Username *</label>
                            <input type="text" class="form-control form-control-sm" id="username" name="username" required>
                            <small class="text-danger" data-error="username"></small>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Nama Lengkap *</label>
                            <input type="text" class="form-control form-control-sm" id="fullname" name="fullname" required>
                            <small class="text-danger" data-error="fullname"></small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Nomor HP / WhatsApp *</label>
                            <input type="text" class="form-control form-control-sm" id="phone" name="phone" required>
                            <small class="text-danger" data-error="phone"></small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Email *</label>
                            <input type="email" class="form-control form-control-sm" id="email" name="email" required>
                            <small class="text-danger" data-error="email"></small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Level Hak Akses *</label>
                            <select class="form-select form-select-sm" id="level_id" name="level_id" required>
                                <option value="">Pilih level</option>
                                <?php foreach (($list_role ?? []) as $role): ?>
                                    <option value="<?= esc($role->level_id); ?>"><?= esc($role->level_id); ?> - <?= esc($role->level_name ?? $role->level_id); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-danger" data-error="level_id"></small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Toko Penugasan *</label>
                            <select class="form-select form-select-sm" id="toko_id" name="toko_id" required>
                                <option value="">Pilih toko</option>
                                <?php foreach (($list_toko ?? []) as $toko): ?>
                                    <option value="<?= esc($toko->toko_id); ?>"><?= esc($toko->toko_id); ?> - <?= esc($toko->toko_nama ?? $toko->toko_id); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-danger" data-error="toko_id"></small>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="form-check p-2 border rounded mt-1 bg-light">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="active" name="active">
                                <label class="form-check-label fw-semibold small" for="active">Status Aktif</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="form-check p-2 border rounded mt-1 bg-light">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="absensi" name="absensi">
                                <label class="form-check-label fw-semibold small" for="absensi">Wajib Absensi</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Gaji Harian (Rp)</label>
                            <input type="text" class="form-control form-control-sm money" id="gaji" name="gaji">
                            <small class="text-danger" data-error="gaji"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm bg-danger-subtle text-danger" style="min-height:38px; min-width:80px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary" style="min-height:38px; min-width:90px;" id="btn-save-user">Simpan</button>
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
    let currentAction = "tambah";
    const userModal = new bootstrap.Modal(document.getElementById("user-modal"));

    const table = $("#table-data").DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah',
                    className: 'btn btn-primary btn-touch-target',
                    action: function(e, dt, node, config) {
                        if (akses_menu.akses_create == "Y") {
                            showModal('tambah');
                        } else {
                            toastr.error('Anda tidak memiliki akses untuk ini!');
                        }
                    }
                }, {
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    className: "btn btn-primary btn-touch-target",
                    title: 'Laporan-Karyawan',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 6, 7],
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
            template: '#card-user-template'
        },
        lengthMenu: [
            [25, 50, 100, -1],
            ["25 rows", "50 rows", "100 rows", "Show all"]
        ],
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "bProcessing": true,
        "ordering": false,
        "serverSide": true,
        ajax: {
            url: '<?= base_url('/user/ajax') ?>',
            type: "post",
            data: {}
        },
        columns: [{
                data: "username",
                title: "Username",
                className: "not-mobile",
            },
            {
                data: "fullname",
                title: "Nama Lengkap",
            },
            {
                data: "phone",
                title: "Telephone",
                className: "not-mobile",
            },
            {
                data: "level_id",
                title: "Level",
                className: "not-mobile",
            },
            {
                data: "active",
                title: "Active",
                render: function(data) {
                    return data === "Y" ? '<span class="badge bg-success-subtle text-success"><i class="ti ti-check me-1"></i>Aktif</span>' : '<span class="badge bg-danger-subtle text-danger"><i class="ti ti-x me-1"></i>Nonaktif</span>';
                }
            },
            {
                data: "absensi",
                title: "Absensi",
                className: "not-mobile text-center",
                render: function(data) {
                    return data === "Y" ? '<span class="badge bg-info-subtle text-info"><i class="ti ti-check me-1"></i>Ya</span>' : '<span class="badge bg-secondary-subtle text-secondary"><i class="ti ti-minus me-1"></i>Tidak</span>';
                }
            },
            {
                data: "toko_id",
                title: "Toko",
                className: "not-mobile",
            },
            {
                data: "gaji",
                title: "Gaji",
                className: "not-mobile text-end",
                render: function(data) {
                    return 'Rp ' + formatMoneyValue(data || 0);
                }
            },
            {
                title: "Action",
                class: "dt-center",
                responsivePriority: 1,
                data: null,
                render: function(data, type, row) {
                    const rowJson = JSON.stringify(row).replace(/"/g, '&quot;');
                    const editMenu = akses_menu?.akses_update == "Y" ? `<li><a class='dropdown-item py-2' href='javascript:void(0)' onclick='showModal("edit", ${rowJson})'><i class='ti ti-pencil text-warning me-2'></i> Edit</a></li>` : "";
                    const deleteMenu = akses_menu?.akses_delete == "Y" ? `<li><a class='dropdown-item py-2 text-danger' href='javascript:void(0)' onclick='showModal("delete", ${rowJson})'><i class='ti ti-trash text-danger me-2'></i> Hapus</a></li>` : "";
                    const resetMenu = akses_menu?.akses_delete == "Y" ? `<li><a class='dropdown-item py-2' href='javascript:void(0)' onclick='resetUser(${rowJson})'><i class='ti ti-key text-secondary me-2'></i> Reset Password</a></li>` : "";
                    return `<div class="dropdown">
                              <button class="btn btn-sm btn-outline-secondary dropdown-toggle btn-touch-target" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" style="min-height:36px; padding:4px 10px;">Aksi</button>
                              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                ${editMenu}
                                ${resetMenu}
                                ${deleteMenu ? '<li><hr class="dropdown-divider my-1"></li>' + deleteMenu : ''}
                              </ul>
                            </div>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json, xhr) {
        $(".page-pretitle").text(`Total: ` + (json?.recordsTotal || 0));
    });

    $('#user-form').validate({
        ignore: [],
        rules: {
            karyawan_id: "required",
            username: "required",
            fullname: "required",
            phone: "required",
            level_id: "required",
            toko_id: "required",
            gaji: {
                required: function() {
                    return $("#absensi").is(":checked");
                }
            },
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            karyawan_id: "Karyawan ID wajib diisi",
            username: "Username wajib diisi",
            fullname: "Fullname wajib diisi",
            phone: "Phone wajib diisi",
            email: {
                required: "Email wajib diisi",
                email: "Format email tidak valid"
            },
            level_id: "Level wajib dipilih",
            toko_id: "Toko wajib dipilih",
            gaji: "Gaji wajib diisi jika absensi aktif"
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            const fieldName = element.attr("name");
            const errorTarget = $(`[data-error="${fieldName}"]`);
            if (errorTarget.length) {
                errorTarget.text(error.text());
            } else {
                error.addClass('invalid-feedback');
                element.after(error);
            }
        },
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
            const fieldName = $(element).attr("name");
            $(`[data-error="${fieldName}"]`).text("");
        },
        submitHandler: function() {
            saveAjax();
        }
    });

    function clearFormErrors() {
        $("#user-form [data-error]").text("");
        $("#user-form .is-invalid").removeClass("is-invalid");
    }

    function resetFormValues() {
        $("#user-form")[0].reset();
        clearFormErrors();
        $("#karyawan_id").prop("readonly", true);
        $('#gaji').val('0');
        $("#user-form :input").prop("disabled", false);
        $("#btn-save-user").prop("disabled", false).show();
        applyMoneyMask('#user-form');
    }

    function loadLastId() {
        return $.ajax({
            type: "GET",
            url: '<?= base_url('/user/lastid') ?>',
            dataType: "json"
        });
    }

    function showModal(action, data) {
        currentAction = action;
        resetFormValues();

        switch (action) {
            case "tambah":
                $("#user-modal-title").text("Tambah User");
                loadLastId().done(function(res) {
                    if (res?.tipe === "success") {
                        $("#karyawan_id").val(res.data || "");
                    } else {
                        $("#karyawan_id").val("");
                    }
                    userModal.show();
                }).fail(function() {
                    $("#karyawan_id").val("");
                    userModal.show();
                });
                break;
            case "edit":
                $("#user-modal-title").text("Edit User");
                if (data) {
                    $("#karyawan_id").val(data.karyawan_id || "");
                    $("#username").val(data.username || "");
                    $("#fullname").val(data.fullname || "");
                    $("#phone").val(data.phone || "");
                    $("#email").val(data.email || "");
                    $("#level_id").val(data.level_id || "");
                    $("#toko_id").val(data.toko_id || "");
                    $("#active").prop("checked", data.active === "Y");
                    $("#absensi").prop("checked", data.absensi === "Y");
                    $("#gaji").val(data.gaji || 0);
                }
                applyMoneyMask('#user-form');
                userModal.show();
                break;
            case "delete":
                if (!data) break;
                swal.fire({
                        title: "Apakah anda yakin?",
                        text: `Hapus user ${data.username} (${data.karyawan_id})?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Tidak',
                        confirmButtonText: 'Ya'
                    })
                    .then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "DELETE",
                                url: '<?= base_url('/user') ?>',
                                dataType: "json",
                                contentType: "application/json",
                                data: JSON.stringify({
                                    karyawan_id: data.karyawan_id
                                }),
                                success: function(res) {
                                    if (res.tipe === "success") {
                                        toastr.success(res.data || "Berhasil");
                                    } else {
                                        toastr.error(res.data || "Gagal");
                                    }
                                    table.ajax.reload(null, false);
                                },
                                error: function(xhr) {
                                    alert(xhr.responseText);
                                },
                            });
                        }
                    });
                break;
        }
    }




    function saveAjax() {
        clearFormErrors();
        const payload = {
            karyawan_id: $("#karyawan_id").val().trim(),
            username: $("#username").val().trim(),
            fullname: $("#fullname").val().trim(),
            phone: $("#phone").val().trim(),
            email: $("#email").val().trim(),
            level_id: $("#level_id").val(),
            toko_id: $("#toko_id").val(),
            active: $("#active").is(":checked") ? "Y" : "N",
            absensi: $("#absensi").is(":checked") ? "Y" : "N",
            gaji: $("#gaji").val()
        };

        const method = currentAction === "edit" ? "PATCH" : "PUT";
        $.ajax({
            type: method,
            url: '<?= base_url('/user') ?>',
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify(payload),
            success: function(res) {
                if (res.tipe === "error" && res.errors) {
                    Object.entries(res.errors).forEach(([key, message]) => {
                        $(`[data-error="${key}"]`).text(message);
                        $(`[name="${key}"]`).addClass("is-invalid");
                    });
                    return;
                }
                userModal.hide();
                if (res.tipe === "success") {
                    toastr.success(res.data || "Berhasil");
                } else {
                    toastr.error(res.data || "Gagal");
                }
                table.ajax.reload(null, false);
            },
            error: function(xhr, textStatus, thrownError) {
                alert(xhr.responseText);
            },
        });


    }

    function resetUser(data) {
        swal.fire({
                title: "Apakah anda yakin?",
                text: `Apakah anda yakin ingi mereset password untuk user dengan id : ${data.karyawan_id}, username : ${data.username} fullname : ${data.fullname}?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Tidak',
                confirmButtonText: 'Ya'
            })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: '<?= base_url('/user/reset') ?>',
                        dataType: "json",
                        data: JSON.stringify({
                            karyawan_id: data.karyawan_id
                        }),
                        success: function(res) {
                            if (res.tipe === "success") {
                                toastr.success(res.data || "Password berhasil direset");
                            } else {
                                toastr.error(res.data || "Gagal reset pasword");
                            }
                            table.ajax.reload(null, false);

                        },
                        error: function(xhr, textStatus, thrownError) {
                            alert(xhr.responseText);
                        },
                    });


                }
            });
    }
</script>
<?= $this->endSection('javascript') ?>
