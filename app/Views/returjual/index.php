<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<style>
    /* =========================================================
       RETUR JUAL MONITORING CARDVIEW (Mobile/Tablet Reflow)
       ========================================================= */
    .retur-history-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .retur-history-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
    }

    .retur-history-card .dropdown-menu {
        z-index: 1055;
    }

    .retur-card-refund {
        font-size: 1.2rem;
        font-weight: 800;
        color: #dc2626;
        letter-spacing: -0.02em;
    }

    .retur-card-id {
        font-family: var(--bs-font-monospace);
        font-size: 0.8rem;
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        border-radius: 5px;
        padding: 0.15rem 0.45rem;
        font-weight: 700;
    }

    .retur-card-struk {
        font-family: var(--bs-font-monospace);
        font-size: 0.78rem;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 0.1rem 0.4rem;
    }

    .retur-card-customer {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
        line-height: 1.25;
    }

    /* Modal Detail 2-Row Item Card on Mobile */
    .detail-retur-item-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem;
        margin-bottom: 0.6rem;
    }

    .detail-retur-item-card .row-1 {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }

    .detail-retur-item-card .row-2 {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.35rem;
        border-top: 1px dashed #e2e8f0;
        font-size: 0.85rem;
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
    }
</style>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">History Retur Penjualan</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Monitoring transaksi retur barang, pengembalian dana konsumen, dan cetak struk.</p>
                    </div>
                    <div class="col-3">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead></thead>
                    <tbody><tr><td>No data to show</td></tr></tbody>
                </table>

                <!-- CardView Template for History Retur (Mobile/Tablet Reflow) -->
                <template id="card-returjual-template">
                    <div class="retur-history-card p-3 h-100 shadow-sm">
                        <!-- Top: Customer Name, ID Retur & Dropdown Actions -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div style="flex: 1; min-width: 0;">
                                <div class="retur-card-customer text-truncate" data-dtcv-field="3"></div>
                                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                    <span class="retur-card-id" data-dtcv-field="1"></span>
                                    <span class="retur-card-struk" title="No Struk Asal"><i class="ti ti-receipt me-1"></i><span data-dtcv-field="2"></span></span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="6"></div>
                        </div>

                        <!-- Mid: Waktu Transaksi & Item Count -->
                        <div class="row g-2 text-muted small mb-2 align-items-center">
                            <div class="col-7 text-truncate">
                                <i class="ti ti-calendar me-1"></i><span data-dtcv-field="0"></span>
                            </div>
                            <div class="col-5 text-end text-truncate">
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="ti ti-package me-1 text-danger"></i><span data-dtcv-field="4"></span> Item Retur
                                </span>
                            </div>
                        </div>

                        <!-- Bottom: Nominal Refund Hero -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Dana Dikembalikan</small>
                            <span class="retur-card-refund" data-dtcv-field="5"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3 px-3 px-md-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-rotate-clockwise-2 text-danger fs-5"></i>
                    <h5 class="modal-title fw-bold text-dark mb-0">Detail Retur Penjualan</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4" id="detail-content"></div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const detailModal = new bootstrap.Modal(document.getElementById('modal-detail'));

    <?php if (session()->getFlashdata('error')) : ?>
        toastr.error("<?= session()->getFlashdata('error') ?>");
    <?php endif; ?>

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah',
                    action: function() {
                        if (akses_menu?.akses_create === 'Y') {
                            window.location.href = '<?= base_url('/returjual/add') ?>';
                            return;
                        }
                        toastr.error('Anda tidak memiliki akses untuk ini!');
                    }
                }, 'pageLength']
            }
        },
        lengthMenu: [
            [25, 50, 100, -1],
            ['25 rows', '50 rows', '100 rows', 'Show all']
        ],
        responsive: false,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/returjual/ajax') ?>',
            type: 'post'
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-returjual-template',
            gridClass: 'col-12 col-sm-6 col-lg-4 mb-3',
            onCardRender: function($card, rowData, rowIdx, rowNode) {
                $card.find('.dropdown-item').addClass('py-2');
            }
        },
        columns: [{
                data: 'tanggal',
                title: 'Tanggal',
                render: function(data, type) {
                    if (type === 'display') {
                        if (!data) return '-';
                        const d = new Date(data);
                        const dateStr = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                        const timeStr = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        return `<span title="${data}">${dateStr} <small class="text-muted">${timeStr}</small></span>`;
                    }
                    return data || '';
                }
            },
            {
                data: 'rj_id',
                title: 'ID Retur',
                className: 'font-monospace'
            },
            {
                data: 'jual_id',
                title: 'No Struk',
                className: 'font-monospace'
            },
            {
                data: 'customer_nama',
                title: 'Customer',
                render: function(data) {
                    return `<div class="fw-semibold text-dark">${escapeHtml(data || 'Pelanggan Umum')}</div>`;
                }
            },
            {
                data: 'jml_item',
                title: 'Item',
                className: 'text-center',
                render: data => Number(data || 0).toLocaleString('id-ID')
            },
            {
                data: 'gross_retur',
                title: 'Refund',
                className: 'text-end fw-bold text-danger',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(data) {
                    const editBtn = akses_menu?.akses_update === 'Y'
                        ? `<li><a class="dropdown-item py-2" href="<?= base_url('/returjual/edit') ?>/${data.rj_id}"><i class="ti ti-pencil text-warning me-2"></i> Edit</a></li>`
                        : '';
                    const deleteBtn = akses_menu?.akses_delete === 'Y'
                        ? `<li><a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="deleteRetur('${data.rj_id}')"><i class="ti ti-trash me-2"></i> Hapus</a></li>`
                        : '';
                    return `<div class="dropdown">
                        <button class="btn btn-light dropdown-toggle align-text-top btn-sm border" type="button" data-bs-boundary="viewport" data-bs-toggle="dropdown">Actions</button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="showDetail('${data.rj_id}')"><i class="ti ti-eye text-info me-2"></i> Detail</a></li>
                            <li><a class="dropdown-item py-2" href="<?= base_url('/returjual/struk') ?>/${data.rj_id}" target="_blank"><i class="ti ti-printer text-success me-2"></i> Cetak Struk</a></li>
                            ${editBtn}
                            ${deleteBtn}
                        </ul>
                    </div>`;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsTotal || 0}`);
    });

    function showDetail(rjId) {
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
        detailModal.show();
        $.getJSON(`<?= base_url('/returjual/show') ?>/${rjId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail tidak ditemukan'}</div>`);
                return;
            }

            const data = res.data || {};
            // Desktop table rows
            const detailRowsHtml = (data.details || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${escapeHtml(row.nama_item || row.kode_item)}<br><small class="text-muted">${escapeHtml(row.kode_item || '-')}</small></td>
                    <td class="text-end fw-bold">${Number(row.qty_retur || 0).toLocaleString('id-ID')}</td>
                    <td>${escapeHtml(row.sat_id || '-')}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.price || 0)}</td>
                    <td class="text-end fw-bold text-danger">Rp ${formatMoneyValue(row.gross_retur || 0)}</td>
                </tr>
            `).join('');

            // Mobile cards (2 rows per card: baris 1 no, item, satuan, qty retur; baris 2 info harga refund)
            const detailMobileCards = (data.details || []).length ? (data.details || []).map((row, idx) => `
                <div class="detail-retur-item-card">
                    <!-- Baris 1: No, Item, Satuan, Qty Retur -->
                    <div class="row-1">
                        <div class="d-flex align-items-start gap-2" style="flex: 1; min-width: 0;">
                            <span class="badge bg-light text-dark border px-2 py-1">${idx + 1}</span>
                            <div class="text-truncate">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">${escapeHtml(row.nama_item || row.kode_item)}</div>
                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">${escapeHtml(row.kode_item || '-')}</small>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 0.8rem;">
                                Retur: <strong>${Number(row.qty_retur || 0).toLocaleString('id-ID')}</strong> ${escapeHtml(row.sat_id || '')}
                            </span>
                        </div>
                    </div>

                    <!-- Baris 2: Refund per unit & Total Refund Item -->
                    <div class="row-2">
                        <div class="text-muted small">
                            @ Rp ${formatMoneyValue(row.price || 0)}
                        </div>
                        <div class="fw-bold text-danger text-end" style="font-size: 0.95rem;">
                            Rp ${formatMoneyValue(row.gross_retur || 0)}
                        </div>
                    </div>
                </div>
            `).join('') : '<div class="text-center py-3 text-muted">Tidak ada detail item retur</div>';

            $('#detail-content').html(`
                <!-- Info Header Retur -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">ID Retur</small><div class="fw-bold text-dark font-monospace">${data.rj_id || '-'}</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">No Struk Asal</small><div class="fw-semibold text-dark font-monospace">${data.jual_id || '-'}</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Tanggal Retur</small><div class="fw-semibold text-dark">${data.tanggal ? new Date(data.tanggal).toLocaleString('id-ID') : '-'}</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Customer</small><div class="fw-semibold text-dark text-truncate">${data.customer_nama || 'Pelanggan Umum'}</div></div></div>
                </div>

                <!-- Info Nilai Refund & Kasir -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-4"><div class="border rounded p-2 p-md-3 h-100 border-danger-subtle bg-danger-subtle"><small class="text-danger d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Total Dana Dikembalikan</small><div class="fw-bold fs-4 text-danger">Rp ${formatMoneyValue(data.gross_retur || 0)}</div></div></div>
                    <div class="col-6 col-md-4"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Kasir / Input</small><div class="fw-semibold text-dark">${data.updid || '-'}</div></div></div>
                    <div class="col-12 col-md-4"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Keterangan / Alasan</small><div class="fw-semibold text-dark">${escapeHtml(data.keterangan || '-')}</div></div></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-dark">Daftar Barang yang Diretur</h6>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">${(data.details || []).length} Item Diretur</span>
                </div>

                <!-- Mobile View: Dual-Row Card Layout -->
                <div class="d-md-none mb-3">
                    ${detailMobileCards}
                </div>

                <!-- Desktop View: Standard Table -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">No</th>
                                <th>Item</th>
                                <th class="text-end">Qty Retur</th>
                                <th>Satuan</th>
                                <th class="text-end">Refund/Satuan</th>
                                <th class="text-end">Total Refund</th>
                            </tr>
                        </thead>
                        <tbody>${detailRowsHtml || '<tr><td colspan="6" class="text-center text-muted">Tidak ada detail</td></tr>'}</tbody>
                    </table>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail')}</div>`);
        });
    }

    function deleteRetur(rjId) {
        Swal.fire({
            title: 'Hapus retur penjualan ini?',
            text: 'Stok, mutasi kas, dan histori poin akan disesuaikan ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/returjual') ?>',
                dataType: 'json',
                data: {
                    rj_id: rjId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Berhasil');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal menghapus retur penjualan');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus retur penjualan'));
                }
            });
        });
    }

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
