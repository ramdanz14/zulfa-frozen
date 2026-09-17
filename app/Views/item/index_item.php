<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<style>
    /* Styling responsif mobile untuk table data item */
    .item-code-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.775rem;
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        padding: 0.15rem 0.45rem;
        border-radius: 0.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.25rem;
        border: 1px solid var(--bs-border-color);
    }

    .item-name-title {
        font-weight: 600;
        color: var(--bs-heading-color);
        line-height: 1.35;
    }

    @media (max-width: 767.98px) {

        /* Atur container search agar mengambil lebar penuh layar */
        .dt-container .dt-search {
            width: 100% !important;
            text-align: left !important;
            /* margin-top: 10px !important; */
        }

        /* Ubah label agar menjadi block (membuat input turun ke baris baru di bawah teks Cari) */
        .dt-container .dt-search label {
            /* display: block !important; */
            /* width: 10% !important; */
            font-weight: 600;
            color: #475569;
            font-size: 0.9rem;
            padding-right: 4px;
        }

        /* Paksa kotak input teks search memiliki lebar 100% penuh */
        .dt-container .dt-search input[type="search"],
        .dt-container .dt-search input.form-control {
            /* display: block !important; */
            width: 80% !important;
            height: 42px !important;
            margin-top: 6px !important;
            margin-left: 0 !important;
            padding: 6px 12px !important;
            font-size: 14px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            background-color: #fff !important;
            box-sizing: border-box !important;
        }

        /* Sembunyikan kolom desktop-only di layar HP */
        #table-data .col-desktop-only {
            display: none !important;
        }

        #table-data th,
        #table-data td {
            padding: 0.6rem 0.4rem;
        }

        .item-nama-col {
            word-break: break-word;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-8 col-md-9">
                        <h4 class="fw-semibold mb-1">Data Barang</h4>
                        <p class="mb-0"><span class="page-pretitle">Total Data : 0</span> | Manajemen Data Barang.</p>
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
                            <div class="card-body p-2 p-md-3 sm:p-0">
                                <table id="table-data" class="table table-bordered table-hover table-striped table-sm table-head-fixed align-middle w-100">
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
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';

    // BroadcastChannel dan window focus sync untuk auto-reload saat tab form/view selesai update data
    let itemSyncChannel = null;
    if (typeof BroadcastChannel !== 'undefined') {
        itemSyncChannel = new BroadcastChannel('zulfa_item_channel');
        itemSyncChannel.onmessage = function(event) {
            if (event.data && event.data.action === 'reload_item_table') {
                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false);
                }
            }
        };
    }

    // Fallback via localStorage jika tab berbeda atau browser tertentu
    window.addEventListener('storage', function(e) {
        if (e.key === 'zulfa_item_updated_at') {
            if (typeof table !== 'undefined') {
                table.ajax.reload(null, false);
            }
        }
    });

    // Cek status update saat tab kembali aktif
    window.addEventListener('focus', function() {
        const lastUpdated = localStorage.getItem('zulfa_item_updated_at');
        if (lastUpdated && window._lastKnownItemUpdate !== lastUpdated) {
            window._lastKnownItemUpdate = lastUpdated;
            if (typeof table !== 'undefined') {
                table.ajax.reload(null, false);
            }
        }
    });

    const table = $("#table-data").DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Item Baru',
                    className: 'btn btn-secondary btn-sm px-3',
                    action: function() {
                        if (akses_menu?.akses_create === "Y") {
                            window.open('<?= base_url('/item/create') ?>', '_blank');
                        } else {
                            toastr.error('Anda tidak memiliki akses untuk ini!');
                        }
                    }
                }, {
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    className: 'btn btn-primary btn-sm px-3',
                    extend: 'excelHtml5',
                    title: 'Laporan-Item',
                    exportOptions: {
                        columns: [0, 1, 2, 3],
                        orthogonal: 'export'
                    },
                }, "pageLength"]
            }
        },
        // --- TAMBAHKAN KODE INI UNTUK CUSTOM LABEL & PLACEHOLDER ---
        language: {
            search: "Cari:",
            searchPlaceholder: "Ketik nama barang/kode...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: ">>",
                previous: "<<"
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
            url: '<?= base_url('/item/ajax') ?>',
            type: 'post',
            data: {}
        },
        columns: [{
                data: 'kode_item',
                title: 'Kode Item',
                className: 'col-desktop-only font-monospace'
            },
            {
                data: 'nama_item',
                title: 'Nama Item',
                className: 'item-nama-col',
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data;
                    }
                    const nama = escapeHtml(data || '-');
                    const kode = escapeHtml(row.kode_item || '');
                    const kat = row.kat_id ? `<span class="badge bg-light-subtle text-muted border ms-1 d-inline-block d-md-none" style="font-size:0.7rem;">${escapeHtml(row.kat_id)}</span>` : '';
                    return `<div>
                        <div class="item-name-title">${nama}</div>
                        <div class="d-block d-md-none mt-1">
                            <span class="item-code-badge"><i class="ti ti-barcode"></i>${kode}</span>
                            ${kat}
                        </div>
                    </div>`;
                }
            },
            {
                data: 'kat_id',
                title: 'Kategori',
                className: 'col-desktop-only',
                render: function(data, type) {
                    return type === 'display' ? `<span class="badge bg-secondary-subtle text-secondary">${escapeHtml(data || '-')}</span>` : data;
                }
            },
            {
                data: 'status_item',
                title: 'Status',
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data;
                    }
                    const isAktif = data === 'Y';
                    return isAktif ?
                        '<span class="badge bg-success-subtle text-success fw-bold">Aktif</span>' :
                        '<span class="badge bg-danger-subtle text-danger fw-bold">Nonaktif</span>';
                }
            },
            {
                title: 'Action',
                className: 'text-center align-middle',
                data: null,
                render: function(data) {
                    const viewBtn = `<a class='dropdown-item py-2' href='<?= base_url('/item/view') ?>/${data.kode_item}' target='_blank' rel='noopener noreferrer'><i class='ti ti-eye text-info me-2'></i> View Detail</a>`;
                    const editBtn = akses_menu?.akses_update === 'Y' ? `<a class='dropdown-item py-2' href='<?= base_url('/item/edit') ?>/${data.kode_item}' target='_blank' rel='noopener noreferrer'><i class='ti ti-pencil text-warning me-2'></i> Edit Item</a>` : '';
                    return `<span class="dropdown">
                          <button class="btn btn-outline-secondary dropdown-toggle btn-sm" type="button" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-expanded="false" style="min-height:36px;min-width:40px;">Aksi</button>
                          <div class="dropdown-menu dropdown-menu-end shadow-sm">
                            ${viewBtn}
                            ${editBtn}
                          </div>
                        </span>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $(".page-pretitle").text(`Total Data : ` + (json?.recordsTotal || 0));
    });

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?= $this->endSection('javascript') ?>