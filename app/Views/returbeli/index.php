<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<style>
    /* =========================================================
       RETUR BELI INDEX MOBILE & DESKTOP STYLING
       ========================================================= */
    .retur-main-cell {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .retur-sup-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
    }
    .retur-meta-line {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .retur-date-badge {
        font-size: 0.775rem;
        font-weight: 600;
        color: #0f172a;
        background: #f1f5f9;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .retur-id-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        color: #475569;
        background: #f8fafc;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }
    .retur-total-amount {
        font-weight: 700;
        font-size: 0.9rem;
        color: #dc3545;
    }

    @media (max-width: 767.98px) {
        /* Full width search bar for mobile */
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

        /* Sembunyikan kolom desktop individual agar tidak sesak di HP */
        #table-data .col-desktop-only {
            display: none !important;
        }

        #table-data th,
        #table-data td {
            padding: 0.65rem 0.5rem !important;
            vertical-align: middle;
        }

        /* Compact modal detail on mobile */
        .modal-body-compact {
            padding: 0.75rem !important;
        }
        .detail-card-metric {
            padding: 0.5rem 0.75rem !important;
        }
    }

    /* Styling for detail modal cards & items */
    .detail-card-metric {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }
    .detail-item-pill {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        background: #ffffff;
        margin-bottom: 0.5rem;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Banner Header -->
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-bold mb-1 text-dark">Retur Pembelian</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold">Total Data : 0</span> | Daftar retur pembelian supplier dan potongan hutang.</p>
                        <small class="text-secondary d-block mt-1" style="font-size: 0.75rem;">
                            <i class="ti ti-lock me-1"></i>Closing aktif: <strong><?= esc($closingDate ?? '-') ?></strong> (SELESAI sebelum tanggal ini terkunci).
                        </small>
                    </div>
                    <div class="col-3 text-end d-none d-sm-block">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

                <!-- CardView Template for Retur Beli (Mobile Reflow) -->
                <template id="card-returbeli-template">
                    <div class="card h-100 border shadow-sm p-3 position-relative mb-2" style="border-radius: 10px; background: #fff;">
                        <!-- Baris Atas: Supplier & Action Dropdown -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div style="flex: 1; min-width: 0;">
                                <div class="fw-bold text-dark fs-3 text-truncate">
                                    <i class="ti ti-building-store text-danger me-1"></i><span data-dtcv-field="2_sup"></span>
                                </div>
                                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.75rem;">
                                        <i class="ti ti-hash"></i><span data-dtcv-field="1"></span>
                                    </span>
                                    <span data-dtcv-field="2_target"></span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="6"></div>
                        </div>

                        <!-- Baris Tengah: Tanggal & Mode Penyelesaian -->
                        <div class="row g-2 text-muted small mb-2 align-items-center">
                            <div class="col-6 text-truncate" style="font-size: 0.775rem;">
                                <i class="ti ti-calendar me-1"></i><span data-dtcv-field="0"></span>
                            </div>
                            <div class="col-6 text-end text-truncate" style="font-size: 0.775rem;">
                                <span class="badge bg-light-danger text-danger"><i class="ti ti-package me-1"></i><span data-dtcv-field="3"></span> item</span>
                            </div>
                        </div>

                        <!-- Baris Bawah: Total Retur & Status Badges -->
                        <div class="p-2 px-3 rounded-2 bg-danger-subtle border border-danger-subtle mb-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-danger fw-semibold d-block" style="font-size: 0.72rem; text-transform: uppercase;">Total Retur</small>
                                    <span class="fw-bold text-danger fs-5" data-dtcv-field="4"></span>
                                </div>
                                <div class="text-end d-flex align-items-center gap-1">
                                    <span data-dtcv-field="5"></span>
                                    <span data-dtcv-field="2_mode"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL RETUR (Compact & Ergonomic for Mobile & Desktop) -->
<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger-subtle text-danger p-2 rounded-circle">
                        <i class="ti ti-arrow-back-up fs-5"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark">Detail Retur Pembelian</h6>
                        <small class="text-muted" id="detail-modal-sub">-</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-compact p-3">
                <div id="detail-content"></div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const detailModal = new bootstrap.Modal(document.getElementById('modal-detail'));
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';

    <?php if (session()->getFlashdata('error')) : ?>
        toastr.error("<?= session()->getFlashdata('error') ?>");
    <?php endif; ?>

    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah Retur',
                    className: 'btn btn-primary btn-sm px-3 fw-semibold',
                    action: function() {
                        if (akses_menu?.akses_create === 'Y') {
                            window.location.href = '<?= base_url('/returbeli/add') ?>';
                            return;
                        }
                        toastr.error('Anda tidak memiliki akses untuk ini!');
                    }
                }, 'pageLength']
            }
        },
        language: {
            search: "Cari Retur / Supplier:",
            searchPlaceholder: "Ketik No Retur, Supplier, Invoice...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ retur",
            infoEmpty: "Tidak ada data retur",
            zeroRecords: "Data retur tidak ditemukan",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: ">>",
                previous: "<<"
            }
        },
        lengthMenu: [
            [25, 50, 100, -1],
            ['25 rows', '50 rows', '100 rows', 'Show all']
        ],
        responsive: false, // Bebas dari child-row accordion bawaan DataTable
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-returbeli-template',
            gridClass: 'col-12 col-sm-6 mb-2',
            onCardRender: function($card, rowData) {
                const supNama = rowData.supplier_nama || rowData.supco || '-';
                $card.find('[data-dtcv-field="2_sup"]').text(supNama);
                $card.find('[data-dtcv-field="2_item"]').text(rowData.jml_item || 0);

                const isCashback = rowData.settlement_mode === 'CASHBACK';
                const modeBadge = isCashback
                    ? '<span class="badge bg-info-subtle text-info"><i class="ti ti-cash"></i> CASHBACK</span>'
                    : '<span class="badge bg-primary-subtle text-primary"><i class="ti ti-receipt-tax"></i> POTONG HUTANG</span>';
                $card.find('[data-dtcv-field="2_mode"]').html(modeBadge);

                const debtTarget = !isCashback && (rowData.beli_id || rowData.invoice)
                    ? `<span class="badge bg-light text-secondary border font-monospace" style="font-size:0.75rem;"><i class="ti ti-file-invoice"></i> ${rowData.beli_id || '-'}${rowData.invoice ? ' (' + rowData.invoice + ')' : ''}</span>`
                    : '';
                $card.find('[data-dtcv-field="2_target"]').html(debtTarget);

                $card.find('.dropdown-item').addClass('py-2');
            }
        },
        ajax: {
            url: '<?= base_url('/returbeli/ajax') ?>',
            type: 'post'
        },
        columns: [
            {
                data: 'tanggal',
                title: 'Tanggal',
                render: data => data ? `<span class="retur-date-badge"><i class="ti ti-calendar"></i> ${new Date(data).toLocaleDateString('id-ID')}</span>` : '-'
            },
            {
                data: 'retur_id',
                title: 'ID Retur',
                className: 'font-monospace'
            },
            {
                data: 'supplier_nama',
                title: 'Supplier & Ref',
                render: function(data, type, row) {
                    const supNama = data || row.supco || '-';
                    const isCashback = row.settlement_mode === 'CASHBACK';
                    const modeText = isCashback ? 'CASHBACK' : 'POTONG HUTANG';
                    const debtTarget = !isCashback && (row.beli_id || row.invoice)
                        ? `&bull; ${row.beli_id || '-'}${row.invoice ? ' (' + row.invoice + ')' : ''}`
                        : '';
                    return `
                        <div>
                            <div class="fw-bold text-dark fs-3">${supNama}</div>
                            <small class="text-muted"><i class="ti ti-receipt-2 me-1"></i>${modeText} ${debtTarget}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'jml_item',
                title: 'Item',
                className: 'text-center'
            },
            {
                data: 'total_retur',
                title: 'Total Retur',
                className: 'text-end fw-bold text-danger',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'status_retur',
                title: 'Status',
                className: 'text-center',
                render: data => data === 'SELESAI' ?
                    '<span class="badge bg-success-subtle text-success">SELESAI</span>' :
                    '<span class="badge bg-warning-subtle text-warning">DRAFT</span>'
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(data) {
                    const isLocked = data.can_edit === false;
                    const editBtn = akses_menu?.akses_update === 'Y' ?
                        (isLocked ?
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockedNotice('${data.closing_date}','edit')"><i class="ti ti-lock text-danger me-1"></i> Edit Terkunci</a>` :
                            `<a class="dropdown-item" href="<?= base_url('/returbeli/edit') ?>/${data.retur_id}"><i class="ti ti-pencil text-warning me-1"></i> Edit Retur</a>`) :
                        '';

                    // Passing info retur into deleteRetur
                    const deletePayload = JSON.stringify({
                        retur_id: data.retur_id,
                        supplier: data.supplier_nama || data.supco || '-',
                        tanggal: data.tanggal ? new Date(data.tanggal).toLocaleDateString('id-ID') : '-',
                        settlement_mode: data.settlement_mode || 'POTONG_HUTANG',
                        status_retur: data.status_retur || 'DRAFT',
                        total_retur: formatMoneyValue(data.total_retur)
                    }).replace(/"/g, '&quot;');

                    const deleteBtn = akses_menu?.akses_delete === 'Y' ?
                        (isLocked ?
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockedNotice('${data.closing_date}','hapus')"><i class="ti ti-lock text-danger me-1"></i> Hapus Terkunci</a>` :
                            `<a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteRetur(${deletePayload})"><i class="ti ti-trash me-1"></i> Hapus Retur</a>`) :
                        '';

                    return `
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle btn-sm px-2 py-1" data-bs-toggle="dropdown" aria-expanded="false" style="min-height: 36px; min-width: 40px;">
                                <i class="ti ti-dots-vertical fs-4"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                <a class="dropdown-item" href="javascript:void(0)" onclick="showDetail('${data.retur_id}')"><i class="ti ti-eye text-info me-1"></i> Lihat Detail</a>
                                ${editBtn}
                                ${deleteBtn}
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsTotal || 0}`);
    });

    function showDetail(returId) {
        $('#detail-modal-sub').text(`Memuat Retur ${returId}...`);
        $('#detail-content').html(`
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-danger me-1" role="status"></div>
                Memuat detail retur...
            </div>
        `);
        detailModal.show();
        $.getJSON(`<?= base_url('/returbeli/show') ?>/${returId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail tidak ditemukan'}</div>`);
                return;
            }
            const data = res.data;
            $('#detail-modal-sub').text(`${data.retur_id} &bull; ${data.supplier_nama || data.supco}`);

            const detailItems = data.details || [];

            // Card item list responsif untuk mobile & tablet
            const detailCardsHtml = detailItems.length ? detailItems.map((row, idx) => `
                <div class="detail-item-pill">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">${idx + 1}. ${row.nama_item || row.kode_item}</div>
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.7rem;">${row.kode_item || '-'}</span>
                                <span class="badge bg-info-subtle text-info" style="font-size: 0.7rem;">Stok: ${Number(row.qty_stok || 0).toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bolder text-danger" style="font-size: 0.9rem;">Rp ${formatMoneyValue(row.gross_retur)}</div>
                            <small class="text-muted">${Number(row.qty_retur || 0).toLocaleString('id-ID')} ${row.sat_id} &times; Rp ${formatMoneyValue(row.price)}</small>
                        </div>
                    </div>
                </div>
            `).join('') : '<div class="text-muted text-center py-3 bg-light rounded small">Tidak ada rincian item diretur.</div>';

            const statusBadge = data.status_retur === 'SELESAI'
                ? '<span class="badge bg-success text-white">SELESAI (Stok Terpotong)</span>'
                : '<span class="badge bg-warning text-dark">DRAFT (Draft Retur)</span>';

            const isCashback = data.settlement_mode === 'CASHBACK';
            const modeBadge = isCashback
                ? '<span class="badge bg-info text-white">CASHBACK SUPPLIER</span>'
                : '<span class="badge bg-primary text-white">POTONG HUTANG</span>';

            const settlementTarget = !isCashback && (data.beli_id || data.invoice)
                ? `${data.beli_id || '-'} / Inv: ${data.invoice || '-'}`
                : (isCashback ? 'Pengembalian Dana Tunai / Transfer' : '-');

            $('#detail-content').html(`
                <!-- Ringkasan Header Retur -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-3">
                        <div class="detail-card-metric">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Tanggal Retur</small>
                            <div class="fw-bold text-dark" style="font-size: 0.88rem;">${new Date(data.tanggal).toLocaleDateString('id-ID')}</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="detail-card-metric">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">ID Retur</small>
                            <div class="fw-bold font-monospace text-dark" style="font-size: 0.88rem;">${data.retur_id}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="detail-card-metric">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Supplier & Penyelesaian</small>
                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">
                                ${data.supplier_nama || data.supco}
                            </div>
                            <small class="text-secondary font-monospace" style="font-size: 0.75rem;">Target: ${settlementTarget}</small>
                        </div>
                    </div>
                </div>

                <!-- Financial Metric Grid (Compact) -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="border rounded p-2 text-center bg-danger-subtle border-danger-subtle">
                            <small class="text-danger d-block fw-semibold" style="font-size: 0.72rem;">Total Nilai Retur</small>
                            <div class="fw-bolder text-danger font-monospace" style="font-size: 1.05rem;">Rp ${formatMoneyValue(data.total_retur)}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 text-center bg-light">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Total Kuantitas Item</small>
                            <div class="fw-bold text-dark font-monospace" style="font-size: 1.05rem;">${detailItems.length} Produk</div>
                        </div>
                    </div>
                </div>

                <!-- Status Badges Bar -->
                <div class="d-flex align-items-center justify-content-between p-2 mb-3 rounded bg-light border flex-wrap gap-2">
                    <span class="small text-muted fw-semibold">Status & Mode:</span>
                    <div class="d-flex gap-1">
                        ${statusBadge}
                        ${modeBadge}
                    </div>
                </div>

                <!-- Keterangan Alasan Retur -->
                ${data.keterangan ? `
                    <div class="p-2 px-3 mb-3 rounded bg-light border">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Keterangan / Alasan:</small>
                        <span class="text-dark small">${data.keterangan}</span>
                    </div>
                ` : ''}

                <!-- Daftar Item Barang Diretur -->
                <div class="mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold mb-0 text-dark small"><i class="ti ti-arrow-back-up text-danger me-1"></i> Rincian Barang Diretur (${detailItems.length})</h6>
                    </div>
                    <div class="d-flex flex-column">
                        ${detailCardsHtml}
                    </div>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail')}</div>`);
        });
    }

    function deleteRetur(info) {
        // Mendukung argumen berupa string returId langsung maupun object info
        const returId = typeof info === 'object' ? info.retur_id : info;
        const supplier = typeof info === 'object' ? info.supplier : '-';
        const tanggal = typeof info === 'object' ? info.tanggal : '-';
        const settlementMode = typeof info === 'object' ? info.settlement_mode : '-';
        const statusRetur = typeof info === 'object' ? info.status_retur : 'DRAFT';
        const totalRetur = typeof info === 'object' ? info.total_retur : '-';

        const isSelesai = statusRetur === 'SELESAI';
        const warningNotice = isSelesai
            ? 'Perhatian: Status retur ini sudah SELESAI. Stok barang di gudang dan potongan hutang/kas retur akan otomatis dikembalikan (rollback).'
            : 'Perhatian: Draft retur pembelian ini beserta seluruh item di dalamnya akan dihapus permanen.';

        Swal.fire({
            title: 'Hapus Retur Pembelian?',
            html: `
                <div class="text-start mt-2 p-3 bg-light border rounded" style="font-size: 0.88rem;">
                    <div class="mb-1 text-danger fw-bold"><i class="ti ti-alert-triangle me-1"></i> Anda akan menghapus transaksi retur:</div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">ID Retur:</span>
                        <strong class="font-monospace text-dark">${returId}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Supplier:</span>
                        <span class="fw-semibold text-dark text-truncate text-end" style="max-width: 180px;">${supplier}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Tanggal:</span>
                        <span class="text-dark">${tanggal}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Penyelesaian:</span>
                        <span class="badge ${settlementMode === 'CASHBACK' ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary'}">${settlementMode}</span>
                    </div>
                    <div class="d-flex justify-content-between pt-1 border-top">
                        <span class="text-muted">Total Nilai Retur:</span>
                        <strong class="text-danger">Rp ${totalRetur}</strong>
                    </div>
                </div>
                <small class="text-muted d-block mt-2 text-start">${warningNotice}</small>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ti ti-trash me-1"></i> Ya, Hapus Retur',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST',
                url: '<?= base_url('/returbeli') ?>',
                dataType: 'json',
                data: {
                    _method: 'DELETE',
                    retur_id: returId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Berhasil menghapus retur');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus retur pembelian'));
                }
            });
        });
    }

    function showLockedNotice(closingDate, jenis) {
        toastr.error(`Retur SELESAI sebelum ${new Date(closingDate).toLocaleDateString('id-ID')} sudah melewati periode closing dan tidak bisa di${jenis}.`);
    }
</script>
<?= $this->endSection('javascript') ?>
