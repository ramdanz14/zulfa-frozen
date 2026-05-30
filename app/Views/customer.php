<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Customer</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Manajemen Data Customer dan kartu member.</p>
                    </div>
                    <div class="col-3">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
</div>

<div class="modal fade" id="modal-web" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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

<div class="modal fade" id="modal-card" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kartu Member Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
                    <div class="text-muted">Preview kartu member untuk disimpan di HP atau dicetak.</div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btn-download-png"><i class="ti ti-photo-down"></i> Download PNG</button>
                        <button type="button" class="btn btn-outline-danger" id="btn-download-pdf"><i class="ti ti-file-type-pdf"></i> Download PDF</button>
                    </div>
                </div>

                <div class="member-card-preview">
                    <div id="member-card" class="member-card">
                        <div class="card-overlay"></div>
                        <div class="card-header clearfix">
                            <div class="logo-container">
                                <img src="<?= base_url(); ?>/assets/images/zulfa.png" alt="Zulfa Frozen Logo">
                            </div>
                            <div class="card-title-text">ZULFAA FROZEN MEMBER CARD</div>
                        </div>

                        <div class="card-body-member">
                            <div class="data-group">
                                <div class="data-label">ID Anggota / Customer ID</div>
                                <div class="data-value cust-id" id="card-cust-id">-</div>
                            </div>
                            <div class="data-group clearfix">
                                <div style="float: left; width: 55%;">
                                    <div class="data-label">Nama Lengkap</div>
                                    <div class="data-value" id="card-nama">-</div>
                                </div>
                                <div style="float: left; width: 45%; padding-left: 12px;">
                                    <div class="data-label">Nomor Telepon</div>
                                    <div class="data-value" id="card-kontak">-</div>
                                </div>
                            </div>
                            <div class="data-group clearfix">
                                <div style="float: left; width: 55%;">
                                    <div class="data-label">Tanggal Daftar</div>
                                    <div class="data-value" id="card-tgl-daftar">-</div>
                                </div>
                                <div style="float: left; width: 45%; padding-left: 12px;">
                                    <div class="data-label">Saldo Poin</div>
                                    <div class="data-value" id="card-poin">0</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer-member clearfix">
                            <div class="footer-left">
                                * Kartu ini merupakan hak milik Zulfa Frozen Food.<br>
                                * Tunjukkan kartu ini saat transaksi untuk pencatatan poin.
                            </div>
                            <div class="footer-right">PREMIUM MEMBER</div>
                        </div>
                        <div class="card-wave"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<style>
    .member-card-preview {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 16px;
        border-radius: 12px;
        background:
            radial-gradient(circle at top right, rgba(13, 110, 253, 0.15), transparent 35%),
            linear-gradient(135deg, #eef7ff 0%, #f8fbff 100%);
        overflow-x: auto;
    }

    .member-card {
        width: 856px;
        max-width: 100%;
        min-height: 540px;
        position: relative;
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        color: #ffffff;
        padding: 40px 48px;
        overflow: hidden;
        border-radius: 18px;
        box-shadow: 0 24px 60px rgba(15, 32, 39, 0.28);
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .member-card .card-overlay {
        position: absolute;
        top: -180px;
        right: -180px;
        width: 480px;
        height: 480px;
        background: radial-gradient(circle, rgba(212, 175, 55, 0.16) 0%, rgba(0, 0, 0, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .member-card .card-wave {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 22px;
        background: #d4af37;
    }

    .member-card .card-header {
        width: 100%;
        min-height: 104px;
        margin-bottom: 28px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 18px;
    }

    .member-card .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }

    .member-card .logo-container {
        float: left;
        width: 250px;
        height: 64px;
        display: flex;
        align-items: center;
    }

    .member-card .logo-container img {
        max-height: 64px;
        width: auto;
        display: block;
        object-fit: contain;
    }

    .member-card .card-title-text {
        float: right;
        text-align: right;
        font-size: 24px;
        font-weight: 700;
        color: #d4af37;
        letter-spacing: 2px;
        margin-top: 8px;
        text-transform: uppercase;
    }

    .member-card .card-body-member {
        width: 100%;
        min-height: 236px;
        margin-top: 12px;
    }

    .member-card .data-group {
        margin-bottom: 18px;
    }

    .member-card .data-label {
        font-size: 13px;
        text-transform: uppercase;
        color: #a0aec0;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    .member-card .data-value {
        font-size: 28px;
        font-weight: 600;
        color: #ffffff;
        letter-spacing: 0.3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .member-card .data-value.cust-id {
        font-family: 'Courier New', Courier, monospace;
        font-size: 34px;
        color: #e2e8f0;
        letter-spacing: 2px;
    }

    .member-card .card-footer-member {
        position: absolute;
        bottom: 42px;
        left: 48px;
        right: 48px;
        font-size: 12px;
        color: #cbd5e0;
        line-height: 1.4;
    }

    .member-card .footer-left {
        float: left;
        width: 60%;
    }

    .member-card .footer-right {
        float: right;
        width: 40%;
        text-align: right;
        font-weight: 700;
        color: #d4af37;
        font-size: 18px;
        letter-spacing: 1px;
    }

    @media (max-width: 768px) {
        .member-card {
            min-height: 420px;
            padding: 24px;
        }

        .member-card .logo-container {
            width: 140px;
            height: 42px;
        }

        .member-card .logo-container img {
            max-height: 42px;
        }

        .member-card .card-title-text {
            font-size: 14px;
            margin-top: 0;
        }

        .member-card .card-header {
            min-height: 72px;
            margin-bottom: 18px;
            padding-bottom: 12px;
        }

        .member-card .data-label {
            font-size: 10px;
        }

        .member-card .data-value {
            font-size: 16px;
        }

        .member-card .data-value.cust-id {
            font-size: 20px;
        }

        .member-card .card-footer-member {
            left: 24px;
            right: 24px;
            bottom: 28px;
            font-size: 9px;
        }

        .member-card .footer-right {
            font-size: 12px;
        }
    }
</style>
<script>
    const akses_menu = <?= $akses_menu ?>;
    let activeCardData = null;

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
                    title: 'Laporan-Customer',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6],
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
            url: '<?= base_url('/customer/ajax') ?>',
            type: 'post',
            data: {}
        },
        columns: [{
                data: "cust_id",
                title: "Customer ID"
            },
            {
                data: "nama",
                title: "Nama",
            },
            {
                data: "kontak",
                title: "HP",
            },
            {
                data: "alamat",
                title: "Alamat",
                className: "not-mobile",
            },
            {
                data: "tgl_daftar",
                title: "Tgl Daftar",
                className: "not-mobile",
            },
            {
                data: "max_faktur",
                title: "Max Faktur",
                className: "text-end",
            },
            {
                data: "poin",
                title: "Poin",
                className: "text-end",
                render: function(data, type) {
                    if (type === 'display') {
                        return Number(data || 0).toLocaleString('en-US');
                    }
                    return data || 0;
                }
            },
            {
                title: 'Action',
                class: 'dt-center',
                responsivePriority: 1,
                data: null,
                render: function(data) {
                    const encoded = encodeRowData(data);
                    const editMenu = akses_menu?.akses_update === 'Y' ? `<a class='dropdown-item btn-action-edit' data-row="${encoded}"><i class='ti ti-pencil text-warning'></i> Edit</a>` : '';
                    const deleteMenu = akses_menu?.akses_delete === 'Y' ? `<a class='dropdown-item btn-action-delete' data-row="${encoded}"><i class='ti ti-trash-x text-danger'></i> Hapus</a>` : '';
                    const cardMenu = `<a class='dropdown-item btn-action-card' data-row="${encoded}"><i class='ti ti-id-badge-2 text-primary'></i> Kartu Member</a>`;
                    return `<span class="dropdown">
                          <button class="btn dropdown-toggle align-text-top btn-sm" data-bs-boundary="viewport" data-bs-toggle="dropdown">Actions</button>
                          <div class="dropdown-menu dropdown-menu-end">
                            ${cardMenu}
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

    $('#table-data').on('click', '.btn-action-card', function() {
        const data = decodeRowData($(this).attr('data-row'));
        if (data) {
            showMemberCard(data);
        }
    });

    $('#table-data').on('click', '.btn-action-edit', function() {
        const data = decodeRowData($(this).attr('data-row'));
        if (data) {
            showModal('edit', data);
        }
    });

    $('#table-data').on('click', '.btn-action-delete', function() {
        const data = decodeRowData($(this).attr('data-row'));
        if (data) {
            showModal('delete', data);
        }
    });

    $('#modal-form').validate({
        rules: {
            cust_id: 'required',
            nama: 'required',
            alamat: 'required',
            kontak: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 13
            },
            max_faktur: {
                required: true,
                digits: true,
                min: 1,
                max: 999
            },
            poin: {
                required: true,
                digits: true,
                min: 0,
                max: 999999
            }
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

    function buildCustomerForm() {
        return `
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="form-group mb-1">
                        <label for="cust_id" class="form-label">CUSTOMER ID</label>
                        <input type="text" class="form-control" name="cust_id" id="cust_id" />
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group mb-1">
                        <label for="nama" class="form-label">NAMA</label>
                        <input type="text" class="form-control" name="nama" id="nama" />
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group mb-1">
                        <label for="alamat" class="form-label">ALAMAT</label>
                        <textarea class="form-control" name="alamat" id="alamat" rows="3"></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-1">
                        <label for="kontak" class="form-label">HP</label>
                        <input type="text" class="form-control" name="kontak" id="kontak" maxlength="13" />
                    </div>
                    <div class="form-group mb-1">
                        <label for="max_faktur" class="form-label">MAX FAKTUR</label>
                        <input type="number" class="form-control" name="max_faktur" id="max_faktur" min="1" max="999" />
                    </div>
                    <div class="form-group mb-1">
                        <label for="poin" class="form-label">POIN</label>
                        <input type="number" class="form-control" name="poin" id="poin" min="0" max="999999" />
                    </div>
                    <div class="form-group mb-1">
                        <label for="tgl_daftar" class="form-label">TGL DAFTAR</label>
                        <input type="date" class="form-control" name="tgl_daftar" id="tgl_daftar" readonly />
                    </div>
                </div>
            </div>
            <input type="hidden" id="_method" name="_method">
            <input type="hidden" id="primarykey" name="primarykey">
        `;
    }

    function showModal(action, data = null) {
        $("#modal-form > .modal-body").html(buildCustomerForm());
        $("#btn-aksi").removeAttr('class');
        $("#modal-web input, #modal-web textarea").attr('readonly', false);
        $("#btn-aksi").prop('disabled', false);
        $('#max_faktur').val(3);
        $('#poin').val(0);
        $('#tgl_daftar').val(new Date().toISOString().slice(0, 10));

        switch (action) {
            case 'tambah':
                $("#_method").val('PUT');
                $("#modal-title").html('Tambah Customer');
                $("#btn-aksi").html('Save');
                $("#btn-aksi").addClass('btn btn-success');
                $.ajax({
                    type: 'GET',
                    url: '<?= base_url('/customer/lastid') ?>',
                    dataType: 'json',
                    success: function(res) {
                        if (res?.tipe === 'success') {
                            $('#cust_id').val(res.data || '');
                            $('#cust_id').prop('readonly', true);
                        }
                    }
                });
                break;
            case 'edit':
                $("#_method").val('PATCH');
                $("#primarykey").val(data.cust_id);
                $("#modal-title").html('Edit Customer');
                $("#btn-aksi").html('Update');
                $("#btn-aksi").addClass('btn btn-warning');
                $('#cust_id').prop('readonly', true);
                $('#tgl_daftar').prop('readonly', true);
                break;
            case 'delete':
                $("#_method").val('DELETE');
                $("#primarykey").val(data.cust_id);
                $("#modal-title").html('Delete Customer');
                $("#modal-web input, #modal-web textarea").attr('readonly', true);
                $("#btn-aksi").html('Delete');
                $("#btn-aksi").addClass('btn btn-danger');
                break;
        }

        if (data) {
            $('#cust_id').val(data.cust_id || '');
            $('#nama').val(data.nama || '');
            $('#alamat').val(data.alamat || '');
            $('#kontak').val(data.kontak || '');
            $('#max_faktur').val(data.max_faktur ?? 3);
            $('#poin').val(data.poin ?? 0);
            $('#tgl_daftar').val(data.tgl_daftar || '');
        }

        $("#loadingOverlay").addClass('d-none');
        $("#modal-web").modal('show');
    }

    function saveAjax() {
        const formData = $("#modal-form").serializeArray();
        $("#loadingOverlay").removeClass('d-none');
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/customer') ?>',
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
                toastr.error(extractErrorMessage(xhr, 'Terjadi kesalahan saat menyimpan data customer.'));
            }
        });
    }

    function showMemberCard(data) {
        activeCardData = data;
        $('#card-cust-id').text(data.cust_id || '-');
        $('#card-nama').text(data.nama || '-');
        $('#card-kontak').text(data.kontak || '-');
        $('#card-tgl-daftar').text(data.tgl_daftar || '-');
        $('#card-poin').text(Number(data.poin || 0).toLocaleString('en-US'));
        $('#modal-card').modal('show');
    }

    async function captureCardCanvas() {
        const card = document.getElementById('member-card');
        return await html2canvas(card, {
            backgroundColor: null,
            scale: 2,
            useCORS: true
        });
    }

    $('#btn-download-png').on('click', async function() {
        if (!activeCardData) return;
        try {
            const canvas = await captureCardCanvas();
            const link = document.createElement('a');
            link.download = `${activeCardData.cust_id || 'member-card'}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        } catch (error) {
            console.error(error);
            toastr.error('Gagal membuat file PNG kartu member.');
        }
    });

    $('#btn-download-pdf').on('click', async function() {
        if (!activeCardData) return;
        try {
            const canvas = await captureCardCanvas();
            const imageData = canvas.toDataURL('image/png');
            const {
                jsPDF
            } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: [54, 85.6]
            });
            pdf.addImage(imageData, 'PNG', 0, 0, 85.6, 54);
            pdf.save(`${activeCardData.cust_id || 'member-card'}.pdf`);
        } catch (error) {
            console.error(error);
            toastr.error('Gagal membuat file PDF kartu member.');
        }
    });
</script>
<?= $this->endSection('javascript') ?>
