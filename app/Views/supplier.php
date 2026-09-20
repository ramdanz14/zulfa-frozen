<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<style>
    /* Styling responsif mobile untuk table data supplier & CardView */
    .supplier-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .supplier-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .supplier-card .dropdown-menu {
        z-index: 1055;
    }

    .supco-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.775rem;
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        padding: 0.2rem 0.5rem;
        border-radius: 0.35rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border: 1px solid var(--bs-border-color);
    }

    @media (max-width: 767.98px) {
        .dt-container .dt-search {
            width: 100% !important;
            text-align: left !important;
            margin-bottom: 0.5rem;
        }

        .dt-container .dt-search label {
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
        }

        .dt-container .dt-search input[type="search"],
        .dt-container .dt-search input.form-control {
            width: 100% !important;
            height: 42px !important;
            margin-top: 4px !important;
            margin-left: 0 !important;
            padding: 6px 12px !important;
            font-size: 14px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            box-sizing: border-box !important;
        }

        #modal-form .form-control {
            min-height: 42px;
            font-size: 14px;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-8 col-md-9">
                        <h4 class="fw-semibold mb-1">Supplier</h4>
                        <p class="mb-0"><span class="page-pretitle">Total Data : 0</span> | Manajemen Data Supplier.</p>
                    </div>
                    <div class="col-4 col-md-3 text-end">
                        <div class="text-center mb-n5 d-none d-sm-block">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl p-0">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border">
                            <div class="card-body p-2 p-md-3">
                                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                                    <thead class="table-light"></thead>
                                    <tbody>
                                        <tr>
                                            <td>Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- CardView Template for Supplier (Mobile Reflow) -->
                                <template id="card-supplier-template">
                                    <div class="supplier-card p-3 h-100 shadow-sm mb-2">
                                        <!-- Header Card: Nama & Dropdown Action -->
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                            <div style="flex: 1; min-width: 0;">
                                                <div class="fw-bold text-dark fs-3 text-truncate">
                                                    <i class="ti ti-building-store text-primary me-1"></i><span data-dtcv-field="1"></span>
                                                </div>
                                                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                                    <span class="supco-badge">
                                                        <i class="ti ti-hash"></i><span data-dtcv-field="0"></span>
                                                    </span>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="ti ti-box me-1"></i><span data-dtcv-field="jml_item"></span> Produk
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0" data-dtcv-field="5"></div>
                                        </div>

                                        <!-- Informasi Kontak & Email -->
                                        <div class="row g-2 text-muted small mb-2 align-items-center">
                                            <div class="col-12 text-truncate" style="font-size: 0.8rem;">
                                                <i class="ti ti-phone text-success me-1"></i>
                                                <span data-dtcv-field="4" class="fw-semibold text-dark"></span>
                                            </div>
                                            <div class="col-12 text-truncate" style="font-size: 0.8rem;">
                                                <i class="ti ti-mail text-secondary me-1"></i>
                                                <span data-dtcv-field="2" class="font-monospace text-secondary"></span>
                                            </div>
                                        </div>

                                        <!-- Alamat Supplier -->
                                        <div class="text-muted small text-truncate pt-2 border-top" style="font-size: 0.8rem;">
                                            <i class="ti ti-map-pin text-danger me-1"></i><span data-dtcv-field="3"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Supplier -->
<div class="modal fade" id="modal-web" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div id="loadingOverlay" class="d-flex justify-content-center align-items-center" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.7); z-index: 1051;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="modal-header py-3 px-3 px-md-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0 text-dark" id="modal-title">Form Supplier</h5>
                    <small class="text-muted d-block" id="modal-subtitle">Silakan isi formulir data supplier</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modal-form">
                <div class="modal-body p-3 p-md-4">
                    <div class="card border border-light-subtle shadow-none mb-3 bg-light-subtle" style="border-radius: 10px;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-light-subtle">
                                <i class="ti ti-building-store text-primary fs-5"></i>
                                <span class="fw-bold text-dark small text-uppercase">Identitas Supplier</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <div class="form-group mb-2">
                                        <label for="supco" class="form-label fw-semibold small text-muted mb-1">Kode Supplier <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control font-monospace fw-bold bg-white" id="supco" name="supco" placeholder="SP001" readonly required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-8">
                                    <div class="form-group mb-2">
                                        <label for="nama" class="form-label fw-semibold small text-muted mb-1">Nama Supplier <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-white" id="nama" name="nama" placeholder="Contoh: PT Sumber Pangan Jaya" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-2">
                                        <label for="kontak" class="form-label fw-semibold small text-muted mb-1">No. Kontak / HP <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="ti ti-phone text-muted"></i></span>
                                            <input type="tel" class="form-control bg-white" id="kontak" name="kontak" placeholder="08xxxxxxxxxx" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="email" class="form-label fw-semibold small text-muted mb-0">Email <span class="text-danger">*</span></label>
                                            <small class="text-primary font-monospace" style="font-size: 0.72rem;"><i class="ti ti-wand me-1"></i>Auto: @zulfaa.id</small>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="ti ti-mail text-muted"></i></span>
                                            <input type="email" class="form-control bg-white font-monospace" id="email" name="email" placeholder="nama@zulfaa.id" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border border-light-subtle shadow-none mb-0 bg-light-subtle" style="border-radius: 10px;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ti ti-map-pin text-primary fs-5"></i>
                                <label for="alamat" class="form-label fw-bold text-dark small text-uppercase mb-0">Alamat Lengkap <span class="text-danger">*</span></label>
                            </div>
                            <textarea class="form-control bg-white" id="alamat" name="alamat" rows="2" placeholder="Nama jalan, kota, provinsi..." required></textarea>
                        </div>
                    </div>

                    <input type="hidden" id="_method" name="_method">
                    <input type="hidden" id="primarykey" name="primarykey">
                </div>
                <div class="modal-footer justify-content-between p-3 bg-light-subtle">
                    <button type="button" class="btn btn-secondary px-3 py-2" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold" id="btn-aksi">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    let isEmailManuallyEdited = false;

    // Helper generate email dari nama: to lower, remove spaces/invalid chars, append @zulfaa.id
    function generateSupplierEmail(nama) {
        if (!nama) return '';
        // Bersihkan spasi, ubah ke lowercase, ambil hanya huruf/angka/dash/underscore
        let clean = nama.toLowerCase().trim().replace(/\s+/g, '').replace(/[^a-z0-9_\-\.]/g, '');
        return clean ? clean + '@zulfaa.id' : '';
    }

    // Auto-generate email saat nama diketik (jika belum diedit manual oleh user)
    $('#nama').on('input', function() {
        if (!isEmailManuallyEdited) {
            $('#email').val(generateSupplierEmail($(this).val()));
            if ($('#modal-form').data('validator')) {
                $('#modal-form').validate().element('#email');
            }
        }
    });

    // Deteksi jika user secara manual mengedit kolom email
    $('#email').on('input', function() {
        isEmailManuallyEdited = true;
    });

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $("#table-data").DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah',
                    className: 'btn btn-primary btn-sm px-3 fw-semibold',
                    action: function() {
                        if (akses_menu?.akses_create === "Y") {
                            showModal('tambah');
                        } else {
                            toastr.error('Anda tidak memiliki akses untuk ini!');
                        }
                    }
                }, {
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    className: 'btn btn-outline-secondary btn-sm px-3',
                    extend: 'excelHtml5',
                    title: 'Laporan-Supplier',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
                        orthogonal: 'export'
                    },
                }, "pageLength"]
            }
        },
        lengthMenu: [
            [25, 50, 100, -1],
            ["25 rows", "50 rows", "100 rows", "Show all"]
        ],
        responsive: false,
        lengthChange: false,
        autoWidth: false,
        bProcessing: true,
        ordering: false,
        serverSide: true,
        ajax: {
            url: '<?= base_url('/supplier/ajax') ?>',
            type: 'post',
            data: {}
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-supplier-template',
            gridClass: 'col-12 col-sm-6 col-lg-4 mb-3',
            onCardRender: function($card, rowData) {
                $card.find('[data-dtcv-field="jml_item"]').text(rowData.jml_item || 0);
            }
        },
        columns: [
            {
                data: "supco",
                title: "Kode",
                className: "font-monospace align-middle"
            },
            {
                data: "nama",
                title: "Nama",
                className: "fw-semibold align-middle"
            },
            {
                data: "email",
                title: "Email",
                className: "font-monospace align-middle",
                render: function(data) {
                    return data ? `<span class="text-secondary"><i class="ti ti-mail me-1"></i>${data}</span>` : '-';
                }
            },
            {
                data: "alamat",
                title: "Alamat",
                className: "align-middle",
                render: function(data) {
                    return data ? `<div class="text-truncate" style="max-width: 250px;" title="${data}"><i class="ti ti-map-pin text-danger me-1"></i>${data}</div>` : '-';
                }
            },
            {
                data: "kontak",
                title: "Kontak",
                className: "align-middle",
                render: function(data) {
                    return data ? `<span class="badge bg-light text-dark border"><i class="ti ti-phone text-success me-1"></i>${data}</span>` : '-';
                }
            },
            {
                title: 'Action',
                className: 'dt-center text-center align-middle',
                data: null,
                render: function(data) {
                    const encoded = encodeRowData(data);
                    const editMenu = akses_menu?.akses_update === 'Y' ? `<li><a class='dropdown-item py-2 btn-action-edit' data-row="${encoded}"><i class='ti ti-pencil text-warning me-2'></i> Edit</a></li>` : '';
                    const deleteMenu = akses_menu?.akses_delete === 'Y' ? `<li><a class='dropdown-item py-2 btn-action-delete text-danger' data-row="${encoded}"><i class='ti ti-trash-x me-2'></i> Hapus</a></li>` : '';
                    return `<div class="dropdown">
                          <button class="btn btn-light dropdown-toggle align-text-top btn-sm border px-2 py-1" type="button" data-bs-boundary="viewport" data-bs-toggle="dropdown" style="min-height: 36px; min-width: 40px;">
                              <i class="ti ti-dots-vertical fs-4"></i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            ${editMenu}
                            ${deleteMenu}
                          </ul>
                        </div>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $(".page-pretitle").text(`Total Data : ` + (json?.recordsTotal || 0));
    });

    function encodeRowData(data) {
        return encodeURIComponent(JSON.stringify(data || {}));
    }

    function decodeRowData(encoded) {
        try {
            return JSON.parse(decodeURIComponent(encoded || ''));
        } catch (error) {
            console.error(error);
            return null;
        }
    }

    // Delegated click handlers untuk Action di Table dan Mobile CardView
    $(document).on('click', '.btn-action-edit', function() {
        const data = decodeRowData($(this).attr('data-row'));
        if (data) {
            showModal('edit', data);
        }
    });

    $(document).on('click', '.btn-action-delete', function() {
        const data = decodeRowData($(this).attr('data-row'));
        if (data) {
            showModal('delete', data);
        }
    });

    $('#modal-form').validate({
        rules: {
            supco: 'required',
            nama: 'required',
            kontak: {
                required: true,
                number: true
            },
            email: {
                required: true,
                email: true
            },
            alamat: 'required'
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
        // Reset validasi & form
        $('#modal-form').validate().resetForm();
        $('#modal-form').find('.is-invalid').removeClass('is-invalid');
        $("#btn-aksi").removeAttr('class');
        $("#modal-web input, #modal-web textarea").attr('readonly', false);
        $("#btn-aksi").prop('disabled', false);

        switch (action) {
            case 'tambah':
                isEmailManuallyEdited = false;
                $("#_method").val('PUT');
                $("#modal-title").html('Tambah Supplier');
                $("#modal-subtitle").html('Silakan isi formulir data supplier baru');
                $("#btn-aksi").html('Simpan');
                $("#btn-aksi").addClass('btn btn-success px-4 py-2 fw-semibold');
                $('#supco').val('');
                $('#nama').val('');
                $('#alamat').val('');
                $('#kontak').val('');
                $('#email').val('');

                $.ajax({
                    type: 'GET',
                    url: '<?= base_url('/supplier/lastid') ?>',
                    dataType: 'json',
                    success: function(res) {
                        if (res?.tipe === 'success') {
                            $('#supco').val(res.data || '');
                            $('#supco').prop('readonly', true);
                        }
                    }
                });
                break;
            case 'edit':
                isEmailManuallyEdited = true;
                $("#_method").val('PATCH');
                $("#primarykey").val(data.supco);
                $("#modal-title").html('Edit Supplier');
                $("#modal-subtitle").html(`Mengubah informasi supplier ${data.supco}`);
                $("#btn-aksi").html('Update');
                $("#btn-aksi").addClass('btn btn-warning px-4 py-2 fw-semibold');
                $('#supco').prop('readonly', true);
                break;
            case 'delete':
                $("#_method").val('DELETE');
                $("#primarykey").val(data.supco);
                $("#modal-title").html('Hapus Supplier');
                $("#modal-subtitle").html(`Konfirmasi hapus supplier ${data.supco}`);
                $("#modal-web input, #modal-web textarea").attr('readonly', true);
                $("#btn-aksi").html('Hapus');
                $("#btn-aksi").addClass('btn btn-danger px-4 py-2 fw-semibold');
                break;
        }

        if (data && action !== "tambah") {
            $('#supco').val(data.supco || '');
            $('#nama').val(data.nama || '');
            $('#alamat').val(data.alamat || '');
            $('#kontak').val(data.kontak || '');
            $('#email').val(data.email || '');
        }

        $("#loadingOverlay").addClass('d-none');
        $("#modal-web").modal('show');
    }

    function saveAjax() {
        const formData = $("#modal-form").serializeArray();
        $("#loadingOverlay").removeClass('d-none');
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/supplier') ?>',
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
                toastr.error(xhr.responseText || "Terjadi kesalahan pada server");
            }
        });
    }
</script>
<?= $this->endSection('javascript') ?>