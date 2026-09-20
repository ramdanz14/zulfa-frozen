<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
$storeContext = $context ?? [];
$isGudang = (bool) ($storeContext['is_gudang'] ?? false);
$storeName = $storeContext['toko']['toko_nama'] ?? session('toko_id');
?>
<style>
    /* =========================================================
       TRANSFER ANTAR TOKO MOBILE & CARDVIEW STYLING
       ========================================================= */
    .transfer-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
    }
    .transfer-id-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        color: #475569;
        background: #f8fafc;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }
    .transfer-hero-banner {
        border-radius: 8px;
        padding: 0.6rem 0.85rem;
        margin-bottom: 0.5rem;
    }

    /* Mobile 2-row item card in detail modal */
    .transfer-item-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .transfer-item-card .row-1 {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.4rem;
    }
    .transfer-item-card .row-2 {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding-top: 0.35rem;
        border-top: 1px dashed #e2e8f0;
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

        .modal-body-compact {
            padding: 0.75rem !important;
        }
        .detail-card-metric {
            padding: 0.5rem 0.75rem !important;
        }
    }

    .detail-card-metric {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Header Banner -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-8">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary text-white px-2.5 py-1">Mode <?= $isGudang ? 'Gudang Kirim' : 'Cabang Terima' ?></span>
                            <h4 class="fw-bold mb-0 text-dark">Transfer Antar Toko - <?= $isGudang ? 'KIRIM' : 'TERIMA' ?></h4>
                        </div>
                        <p class="mb-0 text-muted small"><?= $isGudang ? 'Monitoring PO dari cabang, buat draft pengiriman, dan kirim barang.' : 'Terima pengiriman dari gudang, cek fisik seluruh item barang, dan verifikasi approve / reject.' ?></p>
                        <small class="text-secondary d-block mt-0.5" style="font-size: 0.75rem;"><i class="ti ti-building me-1"></i>Toko aktif: <strong><?= esc($storeName) ?></strong></small>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isGudang) : ?>
            <!-- Card PO Cabang Belum Dipenuhi -->
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning-subtle text-warning p-1.5 rounded"><i class="ti ti-clock fs-5"></i></span>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">PO Cabang Menunggu Dipenuhi</h6>
                            <small class="text-muted" style="font-size: 0.75rem;">Daftar Purchase Order dari cabang yang belum dibuatkan draft transfer</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2 p-md-3">
                    <table id="table-po" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                        <thead class="table-light"></thead>
                        <tbody>
                            <tr>
                                <td>No data to show</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- CardView Template for PO Cabang (Gudang Mode) -->
                    <template id="card-po-template">
                        <div class="card h-100 border shadow-sm p-3 position-relative" style="border-radius: 10px; background: #fff;">
                            <!-- Top: Cabang Tujuan & Action Button -->
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                <div style="flex: 1; min-width: 0;">
                                    <div class="transfer-card-title text-truncate">
                                        <i class="ti ti-building-store text-primary me-1"></i><span data-dtcv-field="0_cabang"></span>
                                    </div>
                                    <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                        <span class="transfer-id-badge"><i class="ti ti-hash"></i><span data-dtcv-field="0_po"></span></span>
                                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.75rem;"><i class="ti ti-file-invoice me-1"></i><span data-dtcv-field="0_inv"></span></span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0" data-dtcv-field="5"></div>
                            </div>

                            <!-- Mid info: Tanggal & Jumlah Item -->
                            <div class="row g-2 text-muted small mb-2 align-items-center">
                                <div class="col-6 text-truncate" style="font-size: 0.775rem;">
                                    <i class="ti ti-calendar me-1"></i><span data-dtcv-field="0_tgl"></span>
                                </div>
                                <div class="col-6 text-end text-truncate" style="font-size: 0.775rem;">
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <i class="ti ti-package me-1 text-primary"></i><span data-dtcv-field="3"></span> Item
                                    </span>
                                </div>
                            </div>

                            <!-- Bottom: Hero Total Nilai PO -->
                            <div class="p-2 px-3 rounded-2 bg-primary-subtle border border-primary-subtle">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-primary fw-semibold" style="font-size: 0.72rem;">Total Nilai PO</small>
                                    <span class="fw-bolder text-primary font-monospace fs-4" data-dtcv-field="4"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        <?php endif; ?>

        <!-- Card Riwayat Transfer Antar Toko -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info-subtle text-info p-1.5 rounded"><i class="ti ti-truck-delivery fs-5"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><?= $isGudang ? 'Draft / Riwayat Pengiriman Gudang' : 'Transfer Masuk Cabang' ?></h6>
                        <small class="text-muted" style="font-size: 0.75rem;"><?= $isGudang ? 'Kelola draft transfer, kirim barang, dan pantau status approve cabang' : 'Periksa barang transfer yang dikirim gudang untuk approve stok atau reject' ?></small>
                    </div>
                </div>
            </div>
            <div class="card-body p-2 p-md-3">
                <table id="table-transfer" class="table table-bordered table-hover table-striped table-sm align-middle w-100 mb-0">
                    <thead class="table-light"></thead>
                    <tbody>
                        <tr>
                            <td>No data to show</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template for Transfer History -->
                <template id="card-transfer-template">
                    <div class="card h-100 border shadow-sm p-3 position-relative" style="border-radius: 10px; background: #fff;">
                        <!-- Top: Cabang Tujuan / Gudang Asal, Transfer ID, Status & Actions -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div style="flex: 1; min-width: 0;">
                                <div class="transfer-card-title text-truncate">
                                    <i class="ti ti-building-store text-info me-1"></i><span data-dtcv-field="0_store"></span>
                                </div>
                                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                    <span class="transfer-id-badge"><i class="ti ti-hash"></i><span data-dtcv-field="0_trf_id"></span></span>
                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.75rem;"><i class="ti ti-clipboard-list me-1"></i>PO: <span data-dtcv-field="0_po_id"></span></span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <span data-dtcv-field="4"></span>
                                <div data-dtcv-field="6"></div>
                            </div>
                        </div>

                        <!-- Mid info: Tanggal Draft & Item Count -->
                        <div class="row g-2 text-muted small mb-2 align-items-center">
                            <div class="col-7 text-truncate" style="font-size: 0.775rem;">
                                <i class="ti ti-calendar me-1"></i><span data-dtcv-field="0_tgl"></span>
                            </div>
                            <div class="col-5 text-end text-truncate" style="font-size: 0.775rem;">
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="ti ti-package me-1 text-info"></i><span data-dtcv-field="3"></span> Item
                                </span>
                            </div>
                        </div>

                        <!-- Bottom: Ref Jual & Beli badges -->
                        <div class="p-2 px-3 rounded-2 bg-light border text-muted small d-flex justify-content-between align-items-center flex-wrap gap-1" style="font-size: 0.75rem;">
                            <span>Jual Gudang: <strong class="font-monospace text-dark" data-dtcv-field="0_jual_id"></strong></span>
                            <span>Beli Cabang: <strong class="font-monospace text-dark" data-dtcv-field="0_beli_id"></strong></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transfer Antar Toko (Optimized Responsive Layout) -->
<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                        <i class="ti ti-truck-delivery fs-5"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark">Detail Transfer Antar Toko</h6>
                        <small class="text-muted" id="detail-modal-sub">-</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-compact p-3" id="detail-content"></div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const isGudang = <?= $isGudang ? 'true' : 'false' ?>;
    const detailModal = new bootstrap.Modal(document.getElementById('modal-detail'));

    <?php if (session()->getFlashdata('error')) : ?>
        toastr.error("<?= session()->getFlashdata('error') ?>");
    <?php endif; ?>

    if (isGudang) {
        $('#table-po').DataTable({
            responsive: false,
            autoWidth: false,
            processing: true,
            serverSide: true,
            ordering: false,
            language: {
                search: "Cari PO Cabang:",
                searchPlaceholder: "Ketik No PO, Cabang, Invoice...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ PO",
                infoEmpty: "Tidak ada PO cabang",
                zeroRecords: "Data PO tidak ditemukan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: ">>",
                    previous: "<<"
                }
            },
            cardView: {
                enable: true,
                breakpoint: 768,
                template: '#card-po-template',
                gridClass: 'col-12 col-sm-6 col-lg-4 mb-3',
                onCardRender: function($card, rowData, rowIdx, rowNode) {
                    const tglFormat = rowData.tanggal ? new Date(rowData.tanggal).toLocaleDateString('id-ID') : '-';
                    $card.find('[data-dtcv-field="0_cabang"]').text(rowData.tujuan_toko_nama || rowData.tujuan_toko_id || '-');
                    $card.find('[data-dtcv-field="0_po"]').text(rowData.beli_id || '-');
                    $card.find('[data-dtcv-field="0_inv"]').text(rowData.invoice || '-');
                    $card.find('[data-dtcv-field="0_tgl"]').text(tglFormat);
                    $card.find('.btn').addClass('w-100 py-2');
                }
            },
            ajax: {
                url: '<?= base_url('/transfer/ajax-po') ?>',
                type: 'post'
            },
            columns: [{
                    data: 'tanggal',
                    title: 'Tanggal PO',
                    render: data => data ? new Date(data).toLocaleDateString('id-ID') : '-'
                },
                {
                    data: 'beli_id',
                    title: 'PO Cabang',
                    className: 'font-monospace fw-semibold'
                },
                {
                    data: 'tujuan_toko_nama',
                    title: 'Cabang Tujuan',
                    render: (data, type, row) => `<div class="fw-semibold">${data || row.tujuan_toko_id}</div><small class="text-muted font-monospace">${row.tujuan_toko_id} | ${row.invoice || '-'}</small>`
                },
                {
                    data: 'jml_item',
                    title: 'Item',
                    className: 'text-center'
                },
                {
                    data: 'total_gross',
                    title: 'Nilai PO',
                    className: 'text-end font-monospace fw-bold text-primary',
                    render: data => 'Rp ' + formatMoneyValue(data)
                },
                {
                    data: null,
                    title: 'Action',
                    className: 'text-center',
                    render: function(data) {
                        if (akses_menu?.akses_create !== 'Y') {
                            return '<span class="text-muted">Tidak ada akses</span>';
                        }
                        return `<a href="<?= base_url('/transfer/add') ?>/${data.tujuan_toko_id}/${data.beli_id}" class="btn btn-sm btn-primary px-3 d-inline-flex align-items-center gap-1"><i class="ti ti-plus"></i> Buat Draft Kirim</a>`;
                    }
                }
            ]
        });
    }

    const transferTable = $('#table-transfer').DataTable({
        responsive: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        language: {
            search: "Cari Transfer:",
            searchPlaceholder: "Ketik No Transfer, PO, Toko...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transfer",
            infoEmpty: "Tidak ada data transfer",
            zeroRecords: "Data transfer tidak ditemukan",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: ">>",
                previous: "<<"
            }
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-transfer-template',
            gridClass: 'col-12 col-sm-6 col-lg-4 mb-3',
            onCardRender: function($card, rowData, rowIdx, rowNode) {
                const storeName = isGudang ? (rowData.tujuan_toko_nama || rowData.tujuan_toko_id) : (rowData.gudang_toko_nama || rowData.gudang_toko_id);
                const tglFormat = rowData.tanggal_transfer ? new Date(rowData.tanggal_transfer).toLocaleDateString('id-ID') : '-';

                $card.find('[data-dtcv-field="0_store"]').text(storeName || '-');
                $card.find('[data-dtcv-field="0_trf_id"]').text(rowData.transfer_id || '-');
                $card.find('[data-dtcv-field="0_po_id"]').text(rowData.po_beli_id || '-');
                $card.find('[data-dtcv-field="0_tgl"]').text(tglFormat);
                $card.find('[data-dtcv-field="0_jual_id"]').text(rowData.jual_id || '-');
                $card.find('[data-dtcv-field="0_beli_id"]').text(rowData.beli_id || '-');

                $card.find('.dropdown-item').addClass('py-2');
            }
        },
        ajax: {
            url: '<?= base_url('/transfer/ajax') ?>',
            type: 'post'
        },
        columns: [{
                data: 'tanggal_transfer',
                title: 'Tanggal Draft',
                render: data => data ? new Date(data).toLocaleDateString('id-ID') : '-'
            },
            {
                data: 'transfer_id',
                title: 'Transfer ID',
                render: (data, type, row) => `<div class="fw-semibold font-monospace">${data}</div><small class="text-muted font-monospace">PO ${row.po_beli_id || '-'}</small>`
            },
            {
                data: null,
                title: isGudang ? 'Cabang Tujuan' : 'Gudang Asal',
                render: function(data) {
                    const name = isGudang ? data.tujuan_toko_nama : data.gudang_toko_nama;
                    const code = isGudang ? data.tujuan_toko_id : data.gudang_toko_id;
                    return `<div class="fw-semibold">${name || code}</div><small class="text-muted font-monospace">${code}</small>`;
                }
            },
            {
                data: 'jml_item',
                title: 'Item',
                className: 'text-center'
            },
            {
                data: 'status_transfer',
                title: 'Status',
                className: 'text-center',
                render: function(data) {
                    if (data === 'APPROVED') return '<span class="badge bg-success-subtle text-success">APPROVED</span>';
                    if (data === 'REJECTED') return '<span class="badge bg-danger-subtle text-danger">REJECTED</span>';
                    if (data === 'KIRIM') return '<span class="badge bg-info-subtle text-info">KIRIM</span>';
                    return '<span class="badge bg-warning-subtle text-warning">DRAFT</span>';
                }
            },
            {
                data: null,
                title: 'Ref Transaksi',
                render: data => `
                    <small class="d-block text-muted font-monospace">Jual: ${data.jual_id || '-'}</small>
                    <small class="d-block text-muted font-monospace">Beli: ${data.beli_id || '-'}</small>
                `
            },
            {
                data: null,
                title: 'Action',
                className: 'text-center',
                render: function(data) {
                    const actions = [`<a class="dropdown-item" href="javascript:void(0)" onclick="showDetail('${data.transfer_id}')"><i class="ti ti-eye text-info me-1"></i> Detail</a>`];

                    if (isGudang && data.status_transfer === 'DRAFT') {
                        if (akses_menu?.akses_update === 'Y') {
                            actions.push(`<a class="dropdown-item" href="<?= base_url('/transfer/edit') ?>/${data.transfer_id}"><i class="ti ti-pencil text-warning me-1"></i> Edit Draft</a>`);
                            actions.push(`<a class="dropdown-item text-primary fw-semibold" href="javascript:void(0)" onclick="sendTransfer('${data.transfer_id}')"><i class="ti ti-truck-delivery me-1"></i> Kirim Barang</a>`);
                        }
                    }

                    if (!isGudang && data.status_transfer === 'KIRIM') {
                        if (akses_menu?.akses_update === 'Y') {
                            actions.push(`<a class="dropdown-item text-success fw-semibold" href="javascript:void(0)" onclick="showDetail('${data.transfer_id}', true)"><i class="ti ti-checklist me-1"></i> Cek & Approve</a>`);
                            actions.push(`<a class="dropdown-item text-danger" href="javascript:void(0)" onclick="rejectTransfer('${data.transfer_id}')"><i class="ti ti-x me-1"></i> Reject</a>`);
                        }
                    }

                    return `
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle btn-sm px-2 py-1" data-bs-toggle="dropdown" aria-expanded="false" style="min-height: 36px; min-width: 40px;">
                                <i class="ti ti-dots-vertical fs-4"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm">${actions.join('')}</div>
                        </div>
                    `;
                }
            }
        ]
    });

    function showDetail(transferId, enableApprove = false) {
        $('#detail-modal-sub').text(`Memuat Transfer ${transferId}...`);
        $('#detail-content').html(`
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                Memuat data detail transfer...
            </div>
        `);
        detailModal.show();

        $.getJSON(`<?= base_url('/transfer/show') ?>/${transferId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail transfer tidak ditemukan'}</div>`);
                return;
            }

            const data = res.data;
            const canApprove = !isGudang && enableApprove && data.status_transfer === 'KIRIM';
            $('#detail-modal-sub').text(`${data.transfer_id} &bull; PO ${data.po_beli_id || '-'}`);

            let statusBadge = '<span class="badge bg-warning-subtle text-warning">DRAFT</span>';
            if (data.status_transfer === 'APPROVED') statusBadge = '<span class="badge bg-success-subtle text-success">APPROVED</span>';
            else if (data.status_transfer === 'REJECTED') statusBadge = '<span class="badge bg-danger-subtle text-danger">REJECTED</span>';
            else if (data.status_transfer === 'KIRIM') statusBadge = '<span class="badge bg-info-subtle text-info">KIRIM</span>';

            // Desktop Table Rows
            const detailRows = (data.details || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    ${canApprove ? `<td class="text-center"><input type="checkbox" class="form-check-input approve-check approve-check-desktop" data-seq="${row.seq_no}" value="${row.seq_no}" ${Number(row.qty_kirim || 0) <= 0 ? 'checked disabled' : ''} style="width: 1.25rem; height: 1.25rem;"></td>` : ''}
                    <td><div class="fw-semibold text-dark">${row.nama_item || row.kode_item}</div><small class="text-muted font-monospace">${row.kode_item}</small></td>
                    <td class="text-end font-monospace">${Number(row.qty_po || 0).toLocaleString('id-ID')}</td>
                    <td class="text-end font-monospace fw-bold text-primary">${Number(row.qty_kirim || 0).toLocaleString('id-ID')}</td>
                    <td>${row.sat_id}</td>
                    <td class="text-end font-monospace">Rp ${formatMoneyValue(row.harga_pokok || 0)}</td>
                    <td class="text-end font-monospace">Rp ${formatMoneyValue(row.harga_jual || 0)}</td>
                    <td class="text-end font-monospace fw-semibold">Rp ${formatMoneyValue(row.gross || 0)}</td>
                </tr>
            `).join('');

            // Mobile Cards (2 Rows per card for smartphone screen)
            const detailMobileCards = (data.details || []).map((row, idx) => `
                <div class="transfer-item-card">
                    <!-- Baris 1: No, Checkbox (jika approve), Nama Item, Satuan & Qty Kirim vs PO -->
                    <div class="row-1">
                        <div class="d-flex align-items-start gap-2" style="flex: 1; min-width: 0;">
                            <span class="badge bg-light text-dark border px-2 py-1">${idx + 1}</span>
                            ${canApprove ? `<input type="checkbox" class="form-check-input approve-check approve-check-mobile me-1 mt-1" data-seq="${row.seq_no}" value="${row.seq_no}" ${Number(row.qty_kirim || 0) <= 0 ? 'checked disabled' : ''} style="width: 1.25rem; height: 1.25rem;">` : ''}
                            <div class="text-truncate">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">${row.nama_item || row.kode_item}</div>
                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">${row.kode_item}</small>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.8rem;">
                                Kirim: ${Number(row.qty_kirim || 0).toLocaleString('id-ID')} ${row.sat_id}
                            </span>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">PO: ${Number(row.qty_po || 0).toLocaleString('id-ID')} ${row.sat_id}</small>
                        </div>
                    </div>

                    <!-- Baris 2: Harga Transfer & Total Gross -->
                    <div class="row-2">
                        <div class="text-muted small" style="font-size: 0.75rem;">
                            @ Rp ${formatMoneyValue(row.harga_jual || 0)} <span class="opacity-75">(HPP: Rp ${formatMoneyValue(row.harga_pokok || 0)})</span>
                        </div>
                        <div class="fw-bold text-dark text-end font-monospace" style="font-size: 0.9rem;">
                            Rp ${formatMoneyValue(row.gross || 0)}
                        </div>
                    </div>
                </div>
            `).join('');

            $('#detail-content').html(`
                <!-- Info Header Metric Cards -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="detail-card-metric h-100">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Transfer ID</small>
                            <div class="fw-bold text-dark font-monospace" style="font-size: 0.88rem;">${data.transfer_id}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-metric h-100">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">PO Cabang</small>
                            <div class="fw-bold text-dark font-monospace" style="font-size: 0.88rem;">${data.po_beli_id || '-'}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-metric h-100">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Gudang Asal</small>
                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">${data.gudang_toko_nama || data.gudang_toko_id}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-metric h-100">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Cabang Tujuan</small>
                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">${data.tujuan_toko_nama || data.tujuan_toko_id}</div>
                        </div>
                    </div>
                </div>

                <!-- Info Status & Ref Transaksi -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="detail-card-metric h-100">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Status Transfer</small>
                            <div class="mt-1">${statusBadge}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-metric h-100">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">No Penjualan Gudang</small>
                            <div class="fw-semibold text-dark font-monospace" style="font-size: 0.85rem;">${data.jual_id || '-'}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-metric h-100">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">No Pembelian Cabang</small>
                            <div class="fw-semibold text-dark font-monospace" style="font-size: 0.85rem;">${data.beli_id || '-'}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-metric h-100">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Keterangan</small>
                            <div class="fw-normal text-secondary text-truncate" style="font-size: 0.85rem;">${data.keterangan || '-'}</div>
                        </div>
                    </div>
                </div>

                ${canApprove ? `
                    <div class="alert alert-warning border-warning-subtle py-2 px-3 mb-3" style="font-size: 0.82rem;">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="ti ti-alert-triangle fs-4 text-warning mt-0.5"></i>
                            <div>
                                <strong>Pemeriksaan Fisik Barang:</strong> Silakan centang item yang fisiknya sudah diperiksa dan cocok dengan fisik yang diterima.
                                Approve akan otomatis menambah stok cabang, menyesuaikan HPP, dan menambah hutang pembelian ke gudang.
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-warning-subtle">
                            <label class="form-check-label fw-bold d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input type="checkbox" class="form-check-input check-all-approve" style="width: 1.25rem; height: 1.25rem;">
                                <span>Centang / Pilih Semua Item yang Diterima</span>
                            </label>
                            <span class="badge bg-warning text-dark fw-bold check-approve-counter">0 / 0</span>
                        </div>
                    </div>
                ` : ''}

                <!-- Items List Desktop -->
                <div class="table-responsive d-none d-md-block mb-3">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">No</th>
                                ${canApprove ? '<th style="width: 50px;" class="text-center"><input type="checkbox" class="form-check-input check-all-approve" title="Pilih Semua" style="width: 1.15rem; height: 1.15rem;"></th>' : ''}
                                <th>Item Barang</th>
                                <th class="text-end" style="width: 90px;">Qty PO</th>
                                <th class="text-end" style="width: 100px;">Qty Kirim</th>
                                <th style="width: 70px;">Satuan</th>
                                <th class="text-end" style="width: 110px;">HPP Gudang</th>
                                <th class="text-end" style="width: 120px;">Harga Transfer</th>
                                <th class="text-end" style="width: 130px;">Gross</th>
                            </tr>
                        </thead>
                        <tbody>${detailRows || '<tr><td colspan="9" class="text-center text-muted">Tidak ada detail item</td></tr>'}</tbody>
                    </table>
                </div>

                <!-- Items List Mobile Cards -->
                <div class="d-block d-md-none mb-3">
                    ${detailMobileCards || '<div class="text-center py-3 text-muted">Tidak ada detail item</div>'}
                </div>

                ${canApprove ? `
                    <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                        <button type="button" class="btn btn-outline-danger px-3 py-2" onclick="rejectTransfer('${data.transfer_id}')">
                            <i class="ti ti-x me-1"></i> Reject Transfer
                        </button>
                        <button type="button" class="btn btn-success px-4 py-2 fw-bold" onclick="approveTransfer('${data.transfer_id}')">
                            <i class="ti ti-check me-1"></i> Approve Transfer
                        </button>
                    </div>
                ` : ''}
            `);

            // Inisialisasi sinkronisasi checkbox dan counter
            updateApproveCounter();
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail transfer')}</div>`);
        });
    }

    // Helper update counter centang approve
    function updateApproveCounter() {
        const requiredSeqs = getRequiredApproveSeqs();
        const checkedSeqs = getCheckedApproveSeqs();
        $('.check-approve-counter').text(`${checkedSeqs.length} / ${requiredSeqs.length}`);
        const allChecked = requiredSeqs.length > 0 && checkedSeqs.length === requiredSeqs.length;
        $('.check-all-approve').prop('checked', allChecked);
    }

    function getRequiredApproveSeqs() {
        const seqSet = new Set();
        $('.approve-check:not(:disabled)').each(function() {
            seqSet.add(Number($(this).data('seq') || $(this).val()));
        });
        return Array.from(seqSet);
    }

    function getCheckedApproveSeqs() {
        const seqSet = new Set();
        $('.approve-check:checked:not(:disabled)').each(function() {
            seqSet.add(Number($(this).data('seq') || $(this).val()));
        });
        return Array.from(seqSet);
    }

    // Sinkronisasi checkbox desktop & mobile saat salah satu diklik
    $(document).on('change', '.approve-check', function() {
        const seq = $(this).data('seq') || $(this).val();
        const isChecked = $(this).is(':checked');
        $(`.approve-check[data-seq="${seq}"]`).prop('checked', isChecked);
        updateApproveCounter();
    });

    // Check all event
    $(document).on('change', '.check-all-approve', function() {
        const isChecked = $(this).is(':checked');
        $('.check-all-approve').prop('checked', isChecked);
        $('.approve-check:not(:disabled)').prop('checked', isChecked);
        updateApproveCounter();
    });

    function sendTransfer(transferId) {
        Swal.fire({
            title: 'Kirim transfer ini?',
            text: 'Stok gudang akan langsung berkurang dan transaksi dicatat sebagai penjualan kredit ke toko cabang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ti ti-truck-delivery me-1"></i> Ya, kirim sekarang',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post(`<?= base_url('/transfer/send') ?>/${transferId}`, function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Transfer berhasil dikirim');
                    transferTable.ajax.reload(null, false);
                    if ($('#table-po').length) {
                        $('#table-po').DataTable().ajax.reload(null, false);
                    }
                    return;
                }
                toastr.error(res.data || 'Gagal mengirim transfer');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal mengirim transfer'));
            });
        });
    }

    function approveTransfer(transferId) {
        const requiredSeqs = getRequiredApproveSeqs();
        const checkedSeqs = getCheckedApproveSeqs();

        if (requiredSeqs.length === 0) {
            toastr.error('Tidak ada item valid yang dikirim untuk di-approve');
            return;
        }

        if (checkedSeqs.length !== requiredSeqs.length) {
            toastr.error(`Semua item (${requiredSeqs.length} item) yang dikirim harus dicentang sebelum approve. Baru dicentang ${checkedSeqs.length} item.`);
            return;
        }

        Swal.fire({
            title: 'Approve transfer ini?',
            text: 'Approve akan menambah stok cabang, update HPP cabang, dan menambah hutang pembelian ke gudang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ti ti-check me-1"></i> Ya, approve transfer',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post(`<?= base_url('/transfer/approve') ?>/${transferId}`, {
                checked_seqs: JSON.stringify(checkedSeqs)
            }, function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Transfer berhasil di-approve');
                    detailModal.hide();
                    transferTable.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal approve transfer');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal approve transfer'));
            });
        });
    }

    function rejectTransfer(transferId) {
        Swal.fire({
            title: 'Reject transfer ini?',
            text: 'Reject akan membatalkan penjualan transfer di sisi gudang dan mengembalikan stok gudang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ti ti-x me-1"></i> Ya, reject transfer',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post(`<?= base_url('/transfer/reject') ?>/${transferId}`, function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Transfer berhasil direject');
                    detailModal.hide();
                    transferTable.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal reject transfer');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal reject transfer'));
            });
        });
    }
</script>
<?= $this->endSection('javascript') ?>