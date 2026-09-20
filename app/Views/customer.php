<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<style>
    .member-card-preview {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 16px;
        border-radius: 12px;
        background: radial-gradient(circle at top right, rgba(13, 110, 253, 0.15), transparent 35%), linear-gradient(135deg, #eef7ff 0%, #f8fbff 100%);
        overflow: hidden;
        width: 100%;
    }

    .member-card-scale-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        overflow: hidden;
        transition: height 0.2s ease;
    }

    .member-card {
        width: 856px;
        min-width: 856px;
        max-width: 856px;
        height: 540px;
        min-height: 540px;
        flex-shrink: 0;
        position: relative;
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        color: #ffffff;
        padding: 40px 48px;
        overflow: hidden;
        border-radius: 18px;
        box-shadow: 0 24px 60px rgba(15, 32, 39, 0.28);
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        box-sizing: border-box;
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

    /* =========================================================
       CUSTOMER DATATABLES CARDVIEW (Mobile/Tablet Reflow)
       ========================================================= */
    .customer-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .customer-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .customer-card .dropdown-menu {
        z-index: 1055;
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

        #modal-form .form-control,
        #modal-form select.form-control {
            min-height: 42px;
            font-size: 14px;
        }
    }
</style>
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

        <!-- Table Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead class="table-light"></thead>
                    <tbody>
                        <tr>
                            <td>No data to show</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template for Mobile/Tablet Reflow -->
                <template id="card-customer-template">
                    <div class="customer-card p-3 h-100 shadow-sm">
                        <!-- Top: Nama, Customer ID, & Dropdown Actions -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div style="flex: 1; min-width: 0;">
                                <div class="fw-bold text-dark fs-3 text-truncate" data-dtcv-field="1"></div>
                                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                    <span data-dtcv-field="0"></span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.75rem;">
                                        <i class="ti ti-coin me-1"></i><span data-dtcv-field="6"></span> Pts
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="9"></div>
                        </div>

                        <!-- Mid info: Telepon & Tanggal Daftar -->
                        <div class="row g-2 text-muted small mb-2">
                            <div class="col-7 text-truncate">
                                <span data-dtcv-field="2"></span>
                            </div>
                            <div class="col-5 text-end text-truncate">
                                <i class="ti ti-calendar me-1"></i><span data-dtcv-field="4"></span>
                            </div>
                        </div>

                        <!-- Grosir & Max Faktur Badges -->
                        <div class="d-flex flex-wrap align-items-center gap-1 mb-2">
                            <span class="badge-grosir-wrap" data-dtcv-field="7"></span>
                            <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.75rem;">
                                Max <span data-dtcv-field="5"></span> Faktur
                            </span>
                        </div>

                        <!-- Alamat -->
                        <div class="text-muted small text-truncate pt-2 border-top" style="font-size: 0.8rem;">
                            <i class="ti ti-map-pin text-primary me-1"></i><span data-dtcv-field="3"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-web" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div id="loadingOverlay" class="d-flex justify-content-center align-items-center" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.7); z-index: 1051;">
                <i class="fas fa-2x fa-sync fa-spin text-primary"></i>
            </div>
            <div class="modal-header py-3 px-3 px-md-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0 text-dark" id="modal-title">Form Customer</h5>
                    <small class="text-muted d-block" id="modal-subtitle">Silakan isi data pelanggan di bawah ini</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modal-form">
                <div class="modal-body p-3 p-md-4"></div>
                <div class="modal-footer justify-content-between p-3">
                    <button type="button" class="btn btn-secondary px-3 py-2" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold" id="btn-aksi">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-card" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Kartu Member Customer</h5>
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
                    <div class="member-card-scale-wrapper">
                        <div id="member-card" class="member-card">
                            <div class="card-overlay"></div>
                            <div class="card-header clearfix">
                                <div class="logo-container">
                                    <img src="<?= base_url(APP_LOGO_PATH); ?>" alt="<?= esc(APP_NAME); ?> Logo">
                                </div>
                                <div class="card-title-text"><?= esc(strtoupper(APP_NAME)); ?> MEMBER CARD</div>
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
                                    * Kartu ini merupakan hak milik <?= esc(APP_NAME); ?>.<br>
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
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
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
            url: '<?= base_url('/customer/ajax') ?>',
            type: 'post',
            data: {}
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-customer-template',
            gridClass: 'col-12 col-sm-6 col-lg-4 mb-3',
            onCardRender: function($card, rowData, rowIdx, rowNode) {
                const isGrosir = rowData.harga_grosir === 'Y';
                const margin = Number(rowData.margin_grosir || 0);
                const $grosirBadge = $card.find('.badge-grosir-wrap');
                if (isGrosir) {
                    $grosirBadge.html(`<span class="badge bg-success px-2 py-1" style="font-size: 0.75rem;">Grosir (${margin}%)</span>`);
                } else {
                    $grosirBadge.html(`<span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 0.75rem;">Retail</span>`);
                }
            }
        },
        columns: [{
                data: "cust_id",
                title: "Customer ID",
                className: "font-monospace",
                render: function(data, type) {
                    if (type === 'display') {
                        return `<span class="badge bg-light text-dark border font-monospace">${data || '-'}</span>`;
                    }
                    return data || '';
                }
            },
            {
                data: "nama",
                title: "Customer",
                render: function(data, type) {
                    if (type === 'display') {
                        return `<div class="fw-bold text-dark">${data || '-'}</div>`;
                    }
                    return data || '';
                }
            },
            {
                data: "kontak",
                title: "HP",
                render: function(data, type) {
                    if (type === 'display') {
                        return data ? `<a href="tel:${data}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1"><i class="ti ti-phone text-muted"></i>${data}</a>` : '-';
                    }
                    return data || '';
                }
            },
            {
                data: "alamat",
                title: "Alamat",
                className: "text-wrap",
                render: function(data, type) {
                    if (type === 'display') {
                        return data ? `<span title="${data}">${data}</span>` : '-';
                    }
                    return data || '';
                }
            },
            {
                data: "tgl_daftar",
                title: "Tgl Daftar",
                className: "text-nowrap",
            },
            {
                data: "max_faktur",
                title: "Max Faktur",
                className: "text-end",
            },
            {
                data: "poin",
                title: "Poin",
                className: "text-end fw-semibold text-primary",
                render: function(data, type) {
                    if (type === 'display') {
                        return Number(data || 0).toLocaleString('en-US');
                    }
                    return data || 0;
                }
            },
            {
                data: "harga_grosir",
                title: "Grosir",
                className: "text-center",
                render: function(data, type, row) {
                    if (type === 'display') {
                        return data === 'Y' ?
                            `<span class="badge bg-success">Y</span>` :
                            `<span class="badge bg-secondary">N</span>`;
                    }
                    return data || 'N';
                }
            },
            {
                data: "margin_grosir",
                title: "Margin %",
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
                className: 'dt-center text-center',
                data: null,
                render: function(data) {
                    const encoded = encodeRowData(data);
                    const editMenu = akses_menu?.akses_update === 'Y' && data.cust_id && data.cust_id.substring(0, 2) === "KS" ? `<li><a class='dropdown-item py-2 btn-action-edit' data-row="${encoded}"><i class='ti ti-pencil text-warning me-2'></i> Edit</a></li>` : '';
                    const deleteMenu = akses_menu?.akses_delete === 'Y' && data.cust_id && data.cust_id.substring(0, 2) === "KS" ? `<li><a class='dropdown-item py-2 btn-action-delete text-danger' data-row="${encoded}"><i class='ti ti-trash-x me-2'></i> Hapus</a></li>` : '';
                    const cardMenu = `<li><a class='dropdown-item py-2 btn-action-card' data-row="${encoded}"><i class='ti ti-id-badge-2 text-primary me-2'></i> Kartu Member</a></li>`;
                    return `<div class="dropdown">
                          <button class="btn btn-light dropdown-toggle align-text-top btn-sm border" type="button" data-bs-boundary="viewport" data-bs-toggle="dropdown">Actions</button>
                          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            ${cardMenu}
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

    // Delegated click handlers (works for both #table-data and .dt-cardview-container)
    $(document).on('click', '.btn-action-card', function() {
        const data = decodeRowData($(this).attr('data-row'));
        if (data) {
            showMemberCard(data);
        }
    });
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
            },
            margin_grosir: {
                min: 0,
                max: 100
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
        <div class="customer-form-container">
            <!-- Section 1: Identitas Customer -->
            <div class="card border border-light-subtle shadow-none mb-3 bg-light-subtle" style="border-radius: 10px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-light-subtle">
                        <i class="ti ti-user-circle text-primary fs-5"></i>
                        <span class="fw-bold text-dark small text-uppercase">Identitas Customer</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label for="cust_id" class="form-label fw-semibold small text-muted mb-1">Customer ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace fw-bold bg-white" id="cust_id" name="cust_id" placeholder="KSxxx" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <label for="nama" class="form-label fw-semibold small text-muted mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: Toko Berkah / Bpk Ahmad" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="kontak" class="form-label fw-semibold small text-muted mb-1">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="ti ti-phone text-muted"></i></span>
                                    <input type="tel" class="form-control" id="kontak" name="kontak" placeholder="08xxxxxxxxxx" maxlength="13" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="tgl_daftar" class="form-label fw-semibold small text-muted mb-1">Tanggal Terdaftar</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="ti ti-calendar text-muted"></i></span>
                                    <input type="date" class="form-control bg-light" id="tgl_daftar" name="tgl_daftar" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Transaksi, Poin & Grosir -->
            <div class="card border border-light-subtle shadow-none mb-3 bg-light-subtle" style="border-radius: 10px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-light-subtle">
                        <i class="ti ti-receipt-tax text-primary fs-5"></i>
                        <span class="fw-bold text-dark small text-uppercase">Pengaturan Grosir & Transaksi</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <div class="form-group">
                                <label for="harga_grosir" class="form-label fw-semibold small text-muted mb-1">Harga Grosir?</label>
                                <select class="form-control form-select" id="harga_grosir" name="harga_grosir">
                                    <option value="N">Tidak (Retail)</option>
                                    <option value="Y">Ya (Grosir)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="form-group">
                                <label for="margin_grosir" class="form-label fw-semibold small text-muted mb-1">Margin (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control text-end" id="margin_grosir" name="margin_grosir" min="0" max="100" step="0.01" value="0">
                                    <span class="input-group-text bg-white">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="form-group">
                                <label for="max_faktur" class="form-label fw-semibold small text-muted mb-1">Max Faktur <span class="text-danger">*</span></label>
                                <input type="number" class="form-control text-end" id="max_faktur" name="max_faktur" min="1" max="999" value="3" required>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="form-group">
                                <label for="poin" class="form-label fw-semibold small text-muted mb-1">Saldo Poin <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="ti ti-coin text-warning"></i></span>
                                    <input type="number" class="form-control text-end fw-bold text-primary" id="poin" name="poin" min="0" max="999999" value="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Alamat Lengkap -->
            <div class="card border border-light-subtle shadow-none mb-0 bg-light-subtle" style="border-radius: 10px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ti ti-map-pin text-primary fs-5"></i>
                        <label for="alamat" class="form-label fw-bold text-dark small text-uppercase mb-0">Alamat Lengkap <span class="text-danger">*</span></label>
                    </div>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Nama jalan, RT/RW, kelurahan, kecamatan..." required></textarea>
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
        $("#modal-web input, #modal-web select, #modal-web textarea").attr('readonly', false);
        $("#btn-aksi").prop('disabled', false);
        $('#max_faktur').val(3);
        $('#poin').val(0);
        $('#harga_grosir').val('N');
        $('#margin_grosir').val(0);
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
                $("#modal-web input, #modal-web select, #modal-web textarea").attr('readonly', true);
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
            $('#harga_grosir').val(data.harga_grosir || 'N');
            $('#margin_grosir').val(data.margin_grosir ?? 0);
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

    function adjustMemberCardScale() {
        const $card = $('#member-card');
        const $wrapper = $('.member-card-scale-wrapper');
        const $preview = $('.member-card-preview');
        if (!$card.length || !$wrapper.length) return;

        // Reset first to get clean measurement
        $card.css({
            'transform': '',
            'transform-origin': ''
        });
        $wrapper.css('height', '');

        const containerWidth = $preview.width() || $wrapper.width() || $(window).width() - 32;
        const cardWidth = 856;
        const cardHeight = 540;

        if (containerWidth < cardWidth) {
            const scale = Math.max(0.3, Math.min(1, containerWidth / cardWidth));
            $card.css({
                'transform': `scale(${scale})`,
                'transform-origin': 'top center'
            });
            $wrapper.css('height', `${Math.ceil(cardHeight * scale)}px`);
        } else {
            $card.css({
                'transform': 'none',
                'transform-origin': 'center center'
            });
            $wrapper.css('height', 'auto');
        }
    }

    $('#modal-card').on('shown.bs.modal', function() {
        adjustMemberCardScale();
    });

    $(window).on('resize', function() {
        if ($('#modal-card').hasClass('show')) {
            adjustMemberCardScale();
        }
    });

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
        const prevTransform = card.style.transform;
        const prevOrigin = card.style.transformOrigin;

        // Temporarily reset transform so html2canvas renders pristine unscaled 856x540 canvas
        card.style.transform = 'none';
        card.style.transformOrigin = '0 0';

        try {
            return await html2canvas(card, {
                backgroundColor: null,
                scale: 2,
                useCORS: true,
                width: 856,
                height: 540
            });
        } finally {
            card.style.transform = prevTransform;
            card.style.transformOrigin = prevOrigin;
        }
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