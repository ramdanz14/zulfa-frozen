<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<style>
    /* =========================================================
       HUTANG MONITORING INDEX MOBILE & DESKTOP STYLING
       ========================================================= */
    .hutang-main-cell {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .hutang-sup-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
    }
    .hutang-date-badge {
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
    .hutang-id-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        color: #475569;
        background: #f8fafc;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }
    .hutang-sisa-amount {
        font-weight: 700;
        font-size: 0.92rem;
        color: #dc3545;
    }

    @media (max-width: 767.98px) {
        /* Full width search bar & filter on mobile */
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

        /* Compact modal on mobile */
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
    .payment-input-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Header Banner -->
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-7">
                        <h4 class="fw-bold mb-1 text-dark">Monitoring Hutang Supplier</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold">Total Data : 0</span> | Pantau hutang, jatuh tempo, overdue, & input cicilan.</p>
                    </div>
                    <div class="col-12 col-md-5 text-md-end">
                        <div class="d-flex align-items-center justify-content-md-end gap-2">
                            <label for="status_filter" class="small fw-semibold text-muted mb-0 text-nowrap"><i class="ti ti-filter"></i> Filter:</label>
                            <select class="form-select form-select-sm bg-white" id="status_filter" style="max-width: 170px;">
                                <option value="BELUM">Belum / Cicil</option>
                                <option value="LUNAS">Lunas</option>
                                <option value="ALL">Semua Kredit</option>
                            </select>
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

                <!-- CardView Template for Hutang Monitoring (Mobile Reflow) -->
                <template id="card-hutang-template">
                    <div class="card h-100 border shadow-sm p-3 position-relative" style="border-radius: 10px; background: #fff;">
                        <!-- Baris Atas: Supplier, Tanggal & Action Dropdown -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div style="flex: 1; min-width: 0;">
                                <div class="fw-bold text-dark fs-3 text-truncate">
                                    <i class="ti ti-building-store text-danger me-1"></i><span data-dtcv-field="0_sup"></span>
                                </div>
                                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.75rem;">
                                        <i class="ti ti-hash"></i><span data-dtcv-field="0_id"></span>
                                    </span>
                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.75rem;">
                                        <i class="ti ti-file-invoice"></i> <span data-dtcv-field="0_inv"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="7"></div>
                        </div>

                        <!-- Baris Tengah: Tanggal Beli & Jatuh Tempo / Overdue -->
                        <div class="row g-2 text-muted small mb-2 align-items-center">
                            <div class="col-6 text-truncate" style="font-size: 0.775rem;">
                                <i class="ti ti-calendar me-1"></i><span data-dtcv-field="0_tgl"></span>
                            </div>
                            <div class="col-6 text-end text-truncate" style="font-size: 0.775rem;">
                                <span data-dtcv-field="0_jt"></span>
                            </div>
                        </div>

                        <!-- Baris Bawah: Hero Sisa Hutang, Terbayar, & Status -->
                        <div class="p-2 px-3 rounded-2 bg-danger-subtle border border-danger-subtle mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-danger fw-semibold d-block" style="font-size: 0.72rem;">Sisa Hutang</small>
                                    <span class="fw-bolder text-danger font-monospace fs-4" data-dtcv-field="4"></span>
                                </div>
                                <div class="text-end">
                                    <span data-dtcv-field="6"></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-1 mt-1 border-top border-danger-subtle text-muted small" style="font-size: 0.75rem;">
                                <span>Total: <strong class="font-monospace text-dark" data-dtcv-field="2"></strong></span>
                                <span class="text-success">Terbayar: <strong class="font-monospace text-success" data-dtcv-field="3"></strong></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL & BAYAR HUTANG (Responsive: Clean Tab/Stack on Mobile & Desktop) -->
<div class="modal fade" id="modal-hutang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger-subtle text-danger p-2 rounded-circle">
                        <i class="ti ti-receipt-2 fs-5"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark">Detail & Pembayaran Hutang</h6>
                        <small class="text-muted" id="debt-modal-sub">-</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-compact p-3">
                <div id="hutang-detail" class="mb-3"></div>

                <div class="row g-3">
                    <!-- Kolom Histori Pembayaran -->
                    <div class="col-12 col-lg-6">
                        <div class="card border mb-0 shadow-none h-100" style="border-radius: 10px;">
                            <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold mb-0 text-dark small"><i class="ti ti-history text-info me-1"></i> Histori Pembayaran</h6>
                                <span class="badge bg-light text-muted border font-monospace" id="badge-payment-count">0 pembayaran</span>
                            </div>
                            <div class="card-body p-2 p-md-3" id="history-container" style="max-height: 380px; overflow-y: auto;">
                                <div id="history-list" class="d-flex flex-column gap-2"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Input Pembayaran / Cicilan Baru -->
                    <div class="col-12 col-lg-6">
                        <div class="card border mb-0 shadow-none" style="border-radius: 10px;">
                            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-dark small"><i class="ti ti-cash-banknote text-success me-1"></i> Input Pembayaran Baru</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" id="btn-add-modal-payment">
                                    <i class="ti ti-plus"></i> Tambah Cicilan
                                </button>
                            </div>
                            <div class="card-body p-2 p-md-3">
                                <form id="form-pay-hutang">
                                    <input type="hidden" id="modal-beli-id">
                                    <div id="modal-payment-list" class="d-flex flex-column mb-2" style="max-height: 280px; overflow-y: auto;"></div>

                                    <!-- Summary Sisa Hutang Realtime -->
                                    <div class="p-2 px-3 bg-danger-subtle rounded-3 border border-danger-subtle d-flex justify-content-between align-items-center mb-3">
                                        <span class="small fw-semibold text-danger">Estimasi Sisa Hutang:</span>
                                        <span class="fw-bolder text-danger font-monospace fs-4" id="modal-sisa-hutang">Rp 0</span>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success fw-bold d-flex align-items-center justify-content-center gap-2" id="btn-save-pay">
                                            <i class="ti ti-device-floppy fs-5"></i> Simpan Pembayaran
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SALDO HUTANG AWAL (Ergonomic form) -->
<div class="modal fade" id="modal-saldo-hutang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger-subtle text-danger p-2 rounded-circle">
                        <i class="ti ti-file-plus fs-5"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark" id="saldo-modal-title">Tambah Saldo Hutang Awal</h6>
                        <small class="text-muted">Pencatatan saldo hutang sebelum sistem berjalan</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-saldo-hutang">
                <div class="modal-body modal-body-compact p-3">
                    <input type="hidden" id="saldo-mode" value="create">
                    <input type="hidden" id="saldo-beli-id" name="beli_id">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="saldo-supplier" class="form-label small fw-semibold">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="saldo-supplier" name="supco" required>
                                <option value="">Pilih supplier</option>
                                <?php foreach (($supplierOptions ?? []) as $supplier): ?>
                                    <option value="<?= esc($supplier['supco']) ?>"><?= esc($supplier['nama']) ?> (<?= esc($supplier['supco']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="saldo-invoice" class="form-label small fw-semibold">Invoice Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="saldo-invoice" name="invoice" placeholder="No Invoice / Ref Hutang" required>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="saldo-tanggal" class="form-label small fw-semibold">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="saldo-tanggal" name="tanggal" required>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Tanggal mundur untuk hutang lama.</small>
                        </div>
                        <div class="col-6 col-md-6">
                            <label for="saldo-jatuh-tempo" class="form-label small fw-semibold">Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="saldo-jatuh-tempo" name="jatuh_tempo" required>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Auto 1 bulan dari tanggal transaksi.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="saldo-total" class="form-label small fw-semibold">Nominal Hutang <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control money font-monospace fw-bold" id="saldo-total" name="total_gross" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="saldo-keterangan" class="form-label small fw-semibold">Keterangan</label>
                            <input type="text" class="form-control form-control-sm" id="saldo-keterangan" name="keterangan" placeholder="Keterangan saldo hutang awal (opsional)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold">Simpan Saldo Hutang</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    let currentDebt = null;
    let modalPayments = [];
    const hutangModal = new bootstrap.Modal(document.getElementById('modal-hutang'));
    const saldoHutangModal = new bootstrap.Modal(document.getElementById('modal-saldo-hutang'));
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';

    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-plus"></i> Tambah Saldo Hutang',
                    className: 'btn btn-danger btn-sm px-3 fw-semibold',
                    action: function() {
                        openSaldoModal();
                    }
                }, {
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    className: 'btn btn-outline-success btn-sm px-3',
                    extend: 'excelHtml5',
                    title: 'Laporan-Hutang-Supplier',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6],
                        orthogonal: 'export'
                    },
                }, {
                    extend: 'pageLength'
                }]
            }
        },
        language: {
            search: "Cari Faktur / Supplier:",
            searchPlaceholder: "Ketik No ID, Supplier, Invoice...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ hutang",
            infoEmpty: "Tidak ada data hutang",
            zeroRecords: "Data hutang tidak ditemukan",
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
        responsive: false, // Menghindari accordion '+' child row di mobile
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-hutang-template',
            gridClass: 'col-12 col-sm-6 col-lg-4 mb-3',
            onCardRender: function($card, rowData, rowIdx, rowNode) {
                const isLunas = rowData.status_bayar === 'LUNAS';
                const overdue = !isLunas && rowData.jatuh_tempo && rowData.jatuh_tempo < '<?= date('Y-m-d') ?>';
                const jtDate = rowData.jatuh_tempo ? new Date(rowData.jatuh_tempo).toLocaleDateString('id-ID') : '-';
                const tglFormat = rowData.tanggal ? new Date(rowData.tanggal).toLocaleDateString('id-ID') : '-';

                $card.find('[data-dtcv-field="0_sup"]').text(rowData.supplier_nama || rowData.supco || '-');
                $card.find('[data-dtcv-field="0_id"]').text(rowData.beli_id || '-');
                $card.find('[data-dtcv-field="0_inv"]').text(rowData.invoice || '-');
                $card.find('[data-dtcv-field="0_tgl"]').text(tglFormat);

                if (overdue) {
                    $card.find('[data-dtcv-field="0_jt"]').html(`<span class="badge bg-danger-subtle text-danger"><i class="ti ti-alert-triangle"></i> Overdue ${Math.max(Number(rowData.hari_lewat || 0), 0)} hr (${jtDate})</span>`);
                } else {
                    $card.find('[data-dtcv-field="0_jt"]').html(`<span class="badge bg-light text-muted border"><i class="ti ti-calendar-due"></i> JT: ${jtDate}</span>`);
                }

                $card.find('.dropdown-item').addClass('py-2');
            }
        },
        ajax: {
            url: '<?= base_url('/hutang/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.status_filter = $('#status_filter').val();
            }
        },
        columns: [
            {
                data: 'supplier_nama',
                title: 'Informasi Hutang & Supplier',
                render: function(data, type, row) {
                    const supNama = data || row.supco || '-';
                    const tglFormat = row.tanggal ? new Date(row.tanggal).toLocaleDateString('id-ID') : '-';
                    const beliId = row.beli_id || '-';
                    const invoice = row.invoice || '-';
                    const sisaText = 'Rp ' + formatMoneyValue(row.sisa_bayar);
                    const totalText = 'Rp ' + formatMoneyValue(row.total_gross);

                    // Overdue calculation
                    const isLunas = row.status_bayar === 'LUNAS';
                    const overdue = !isLunas && row.jatuh_tempo && row.jatuh_tempo < '<?= date('Y-m-d') ?>';
                    const jtDate = row.jatuh_tempo ? new Date(row.jatuh_tempo).toLocaleDateString('id-ID') : '-';

                    let statusBadge = '<span class="badge bg-danger-subtle text-danger">BELUM</span>';
                    if (isLunas) {
                        statusBadge = '<span class="badge bg-success-subtle text-success">LUNAS</span>';
                    } else if (row.status_bayar === 'CICIL') {
                        statusBadge = '<span class="badge bg-info-subtle text-info">CICIL</span>';
                    }

                    const jtBadge = overdue
                        ? `<span class="badge bg-danger-subtle text-danger"><i class="ti ti-alert-triangle"></i> Overdue ${Math.max(Number(row.hari_lewat || 0), 0)} hari (${jtDate})</span>`
                        : `<span class="badge bg-light text-muted border"><i class="ti ti-calendar-due"></i> JT: ${jtDate}</span>`;

                    return `
                        <div>
                            <div class="fw-bold text-dark fs-3">${supNama}</div>
                            <small class="text-muted font-monospace"><i class="ti ti-file-invoice me-1"></i>${invoice} &bull; Total: ${totalText}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'beli_id',
                title: 'ID / Tgl',
                className: 'col-desktop-only',
                render: function(data, type, row) {
                    return `<div class="fw-semibold font-monospace">${data}</div><small class="text-muted"><i class="ti ti-calendar"></i> ${new Date(row.tanggal).toLocaleDateString('id-ID')}</small>`;
                }
            },
            {
                data: 'total_gross',
                title: 'Total Hutang',
                className: 'text-end col-desktop-only',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'total_bayar',
                title: 'Terbayar',
                className: 'text-end col-desktop-only text-success fw-semibold',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'sisa_bayar',
                title: 'Sisa Hutang',
                className: 'text-end col-desktop-only fw-bold text-danger font-monospace',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'jatuh_tempo',
                title: 'Jatuh Tempo',
                className: 'col-desktop-only',
                render: function(data, type, row) {
                    if (!data) return '-';
                    const overdue = row.status_bayar !== 'LUNAS' && data < '<?= date('Y-m-d') ?>';
                    const cls = overdue ? 'text-danger fw-bold' : 'text-dark';
                    const label = overdue
                        ? `<span class="badge bg-danger-subtle text-danger d-block mt-1"><i class="ti ti-alert-triangle"></i> Overdue ${Math.max(Number(row.hari_lewat || 0), 0)} hari</span>`
                        : `<small class="text-muted d-block">${humanizeDate(data)}</small>`;
                    return `<div class="${cls}"><i class="ti ti-calendar-time me-1"></i>${new Date(data).toLocaleDateString('id-ID')}</div>${label}`;
                }
            },
            {
                data: 'status_bayar',
                title: 'Status',
                className: 'text-center col-desktop-only',
                render: function(data) {
                    if (data === 'LUNAS') return '<span class="badge bg-success-subtle text-success">LUNAS</span>';
                    if (data === 'CICIL') return '<span class="badge bg-info-subtle text-info">CICIL</span>';
                    return '<span class="badge bg-danger-subtle text-danger">BELUM</span>';
                }
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(data) {
                    const isSaldoAwal = String(data.beli_id || '').startsWith('SH');
                    const saldoPayload = JSON.stringify({
                        beli_id: data.beli_id,
                        invoice: data.invoice || '-',
                        supplier: data.supplier_nama || data.supco || '-',
                        tanggal: data.tanggal ? new Date(data.tanggal).toLocaleDateString('id-ID') : '-',
                        total_gross: formatMoneyValue(data.total_gross),
                        sisa_bayar: formatMoneyValue(data.sisa_bayar)
                    }).replace(/"/g, '&quot;');

                    const saldoActions = isSaldoAwal ? `
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="openSaldoModal('${data.beli_id}')"><i class="ti ti-pencil text-warning me-1"></i> Edit Saldo Hutang</a>
                        <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteSaldoHutang(${saldoPayload})"><i class="ti ti-trash me-1"></i> Hapus Saldo Hutang</a>` : '';

                    return `
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle btn-sm px-2 py-1" data-bs-toggle="dropdown" aria-expanded="false" style="min-height: 36px; min-width: 40px;">
                                <i class="ti ti-dots-vertical fs-4"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                <a class="dropdown-item" href="javascript:void(0)" onclick="openDebtModal('${data.beli_id}')"><i class="ti ti-cash text-success me-1"></i> Bayar Hutang</a>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="openDebtModal('${data.beli_id}')"><i class="ti ti-history text-info me-1"></i> Lihat Detail & History</a>
                                ${saldoActions}
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsFiltered || 0}`);
    });

    $('#status_filter').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-add-modal-payment').on('click', function() {
        modalPayments.push({
            cara_bayar: 'TUNAI',
            tanggal_bayar: nowLocalValue(),
            jumlah_bayar: 0,
            bank_nama: '',
            rekening_no: ''
        });
        renderModalPayments();
    });

    $('#form-pay-hutang').on('submit', function(e) {
        e.preventDefault();
        submitDebtPayment();
    });

    $('#form-saldo-hutang').on('submit', function(e) {
        e.preventDefault();
        submitSaldoHutang();
    });

    $('#saldo-tanggal').on('change', function() {
        const tanggal = $(this).val();
        if (!tanggal) return;
        $('#saldo-jatuh-tempo').val(addMonthToDate(tanggal));
    });

    function openDebtModal(beliId) {
        currentDebt = null;
        modalPayments = [{
            cara_bayar: 'TUNAI',
            tanggal_bayar: nowLocalValue(),
            jumlah_bayar: 0,
            bank_nama: '',
            rekening_no: ''
        }];
        $('#debt-modal-sub').text(`Memuat Faktur ${beliId}...`);
        $('#hutang-detail').html(`
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-danger me-1" role="status"></div>
                Memuat data hutang...
            </div>
        `);
        $('#history-list').html('<div class="text-center py-3 text-muted small">Memuat histori pembayaran...</div>');
        $('#modal-beli-id').val(beliId);
        renderModalPayments();
        hutangModal.show();

        $.getJSON(`<?= base_url('/pembelian/show') ?>/${beliId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#hutang-detail').html(`<div class="alert alert-danger mb-0">${res.data || 'Data hutang tidak ditemukan'}</div>`);
                return;
            }
            currentDebt = res.data;
            $('#debt-modal-sub').text(`${currentDebt.beli_id} &bull; ${currentDebt.supplier_nama || currentDebt.supco}`);
            renderDebtHeader();
            renderHistory(currentDebt.payments || []);
            renderModalPayments();
        }).fail(function(xhr) {
            $('#hutang-detail').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat data hutang')}</div>`);
        });
    }

    function renderDebtHeader() {
        if (!currentDebt) return;
        const overdue = currentDebt.status_bayar !== 'LUNAS' && currentDebt.jatuh_tempo && currentDebt.jatuh_tempo < '<?= date('Y-m-d') ?>';

        const isSaldoAwal = String(currentDebt.beli_id || '').startsWith('SH');
        const detailRows = currentDebt.details || [];

        let statusBadge = '<span class="badge bg-danger-subtle text-danger">BELUM LUNAS</span>';
        if (currentDebt.status_bayar === 'LUNAS') {
            statusBadge = '<span class="badge bg-success-subtle text-success">LUNAS</span>';
        } else if (currentDebt.status_bayar === 'CICIL') {
            statusBadge = '<span class="badge bg-info-subtle text-info">CICIL</span>';
        }

        const jtLabel = currentDebt.jatuh_tempo ? new Date(currentDebt.jatuh_tempo).toLocaleDateString('id-ID') : '-';

        // Summary items preview
        const itemsPreview = detailRows.length ? `
            <div class="mt-2 pt-2 border-top">
                <small class="text-muted d-block mb-1" style="font-size: 0.72rem;">Barang Dibeli (${detailRows.length} item):</small>
                <div class="d-flex flex-wrap gap-1">
                    ${detailRows.slice(0, 4).map(r => `<span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.7rem;">${r.nama_item || r.kode_item} (${r.qty_beli} ${r.sat_id})</span>`).join('')}
                    ${detailRows.length > 4 ? `<span class="badge bg-light text-muted border" style="font-size: 0.7rem;">+${detailRows.length - 4} item lainnya</span>` : ''}
                </div>
            </div>
        ` : (isSaldoAwal ? '<div class="mt-2 pt-2 border-top text-muted small"><i class="ti ti-info-circle"></i> Saldo hutang awal (tanpa pergerakan stok).</div>' : '');

        $('#hutang-detail').html(`
            <div class="row g-2 mb-3">
                <div class="col-6 col-sm-3">
                    <div class="detail-card-metric">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Tanggal Transaksi</small>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">${new Date(currentDebt.tanggal).toLocaleDateString('id-ID')}</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="detail-card-metric">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Jatuh Tempo</small>
                        <div class="fw-bold ${overdue ? 'text-danger' : 'text-dark'}" style="font-size: 0.88rem;">
                            ${jtLabel} ${overdue ? '<i class="ti ti-alert-circle text-danger"></i>' : ''}
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="detail-card-metric">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Supplier & Invoice</small>
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">
                            ${currentDebt.supplier_nama || currentDebt.supco}
                        </div>
                        <small class="text-secondary font-monospace" style="font-size: 0.75rem;">Inv: ${currentDebt.invoice || '-'}</small>
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-4">
                    <div class="border rounded p-2 text-center bg-light">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Total Hutang</small>
                        <div class="fw-bold text-dark font-monospace" style="font-size: 0.95rem;">Rp ${formatMoneyValue(currentDebt.total_gross)}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-2 text-center bg-light">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Total Terbayar</small>
                        <div class="fw-semibold text-success font-monospace" style="font-size: 0.95rem;">Rp ${formatMoneyValue(currentDebt.total_bayar)}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-2 text-center bg-danger-subtle border-danger-subtle">
                        <small class="text-danger d-block fw-semibold" style="font-size: 0.72rem;">Sisa Hutang</small>
                        <div class="fw-bolder text-danger font-monospace" style="font-size: 0.95rem;">Rp ${formatMoneyValue(currentDebt.sisa_bayar)}</div>
                    </div>
                </div>
            </div>

            <div class="p-2 px-3 rounded bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="small text-muted fw-semibold">Status Hutang:</span>
                <div>${statusBadge}</div>
            </div>

            ${itemsPreview}
        `);
    }

    function renderHistory(rows) {
        const $list = $('#history-list');
        $list.empty();
        $('#badge-payment-count').text(`${rows.length} pembayaran`);

        if (!rows.length) {
            $list.html('<div class="text-center py-4 text-muted small"><i class="ti ti-receipt-off fs-6 d-block mb-1 opacity-50"></i> Belum ada histori pembayaran atau cicilan.</div>');
            return;
        }

        rows.forEach((row, idx) => {
            const isTransfer = row.cara_bayar === 'TRANSFER';
            const bankInfo = isTransfer ? `${row.bank_nama || '-'} &bull; ${row.rekening_no || '-'}` : 'Tunai / Kasir';
            $list.append(`
                <div class="p-2 px-3 border rounded bg-white" style="font-size: 0.82rem;">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge ${isTransfer ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'} rounded-pill" style="font-size: 0.7rem;">
                                ${row.cara_bayar}
                            </span>
                            <span class="fw-bold text-dark font-monospace" style="font-size: 0.9rem;">Rp ${formatMoneyValue(row.jumlah_bayar)}</span>
                        </div>
                        <small class="text-muted"><i class="ti ti-clock"></i> ${new Date(row.tanggal_bayar).toLocaleDateString('id-ID')}</small>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="ti ti-building-bank me-1"></i>${bankInfo}
                    </small>
                </div>
            `);
        });
    }

    function renderModalPayments() {
        const $list = $('#modal-payment-list');
        $list.empty();

        if (!modalPayments.length) {
            $list.html('<div class="text-center py-3 text-muted small">Klik <strong>+ Tambah Cicilan</strong> di atas untuk memasukkan pembayaran baru.</div>');
            updateModalRemaining();
            return;
        }

        modalPayments.forEach((row, idx) => {
            const isTransfer = row.cara_bayar === 'TRANSFER';
            $list.append(`
                <div class="payment-input-card position-relative" data-idx="${idx}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-light text-dark border fw-semibold" style="font-size: 0.75rem;">Pembayaran #${idx + 1}</span>
                        <button type="button" class="btn btn-outline-danger btn-sm px-2 py-0 modal-delete" style="font-size: 0.75rem;" title="Hapus cicilan">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size: 0.72rem;">Metode</label>
                            <select class="form-select form-select-sm modal-method">
                                <option value="TUNAI" ${row.cara_bayar === 'TUNAI' ? 'selected' : ''}>TUNAI</option>
                                <option value="TRANSFER" ${row.cara_bayar === 'TRANSFER' ? 'selected' : ''}>TRANSFER</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1 text-success fw-semibold" style="font-size: 0.72rem;">Nominal (Rp)</label>
                            <input type="text" inputmode="numeric" class="form-control form-control-sm money text-end modal-amount font-monospace fw-bold" value="${row.jumlah_bayar}">
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1" style="font-size: 0.72rem;">Waktu Bayar</label>
                            <input type="datetime-local" class="form-control form-control-sm modal-date" value="${toDatetimeLocal(row.tanggal_bayar)}">
                        </div>
                        <div class="col-12 transfer-fields ${isTransfer ? '' : 'd-none'}">
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm modal-bank" placeholder="Nama Bank (BCA, Mandiri, dll)" value="${row.bank_nama || ''}">
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm modal-rekening" placeholder="No Rekening" value="${row.rekening_no || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });

        bindModalPaymentEvents();
        applyMoneyMask('#modal-payment-list');
        updateModalRemaining();
    }

    function bindModalPaymentEvents() {
        $('#modal-payment-list .modal-method').off('change').on('change', function() {
            const card = $(this).closest('.payment-input-card');
            const idx = Number(card.data('idx'));
            modalPayments[idx].cara_bayar = $(this).val();
            if (modalPayments[idx].cara_bayar === 'TUNAI') {
                modalPayments[idx].bank_nama = '';
                modalPayments[idx].rekening_no = '';
            }
            renderModalPayments();
        });

        $('#modal-payment-list .modal-date').off('change').on('change', function() {
            const card = $(this).closest('.payment-input-card');
            const idx = Number(card.data('idx'));
            modalPayments[idx].tanggal_bayar = $(this).val();
        });

        $('#modal-payment-list .modal-amount').off('input blur').on('input blur', function() {
            const card = $(this).closest('.payment-input-card');
            const idx = Number(card.data('idx'));
            modalPayments[idx].jumlah_bayar = Number(normalizeMoneyValue($(this).val() || 0));
            updateModalRemaining();
        });

        $('#modal-payment-list .modal-bank').off('input').on('input', function() {
            const card = $(this).closest('.payment-input-card');
            const idx = Number(card.data('idx'));
            modalPayments[idx].bank_nama = $(this).val();
        });

        $('#modal-payment-list .modal-rekening').off('input').on('input', function() {
            const card = $(this).closest('.payment-input-card');
            const idx = Number(card.data('idx'));
            modalPayments[idx].rekening_no = $(this).val();
        });

        $('#modal-payment-list .modal-delete').off('click').on('click', function() {
            const card = $(this).closest('.payment-input-card');
            const idx = Number(card.data('idx'));
            modalPayments.splice(idx, 1);
            renderModalPayments();
        });
    }

    function updateModalRemaining() {
        const originalRemaining = Number(currentDebt?.sisa_bayar || 0);
        const incoming = modalPayments.reduce((sum, row) => sum + Number(row.jumlah_bayar || 0), 0);
        const nextRemaining = Math.max(originalRemaining - incoming, 0);
        $('#modal-sisa-hutang').text(`Rp ${formatMoneyValue(nextRemaining)}`);

        if (incoming > originalRemaining) {
            $('#modal-sisa-hutang').addClass('text-danger').removeClass('text-success');
        } else {
            $('#modal-sisa-hutang').removeClass('text-danger').addClass('text-success');
        }
    }

    function submitDebtPayment() {
        normalizeMoneyInputs('#form-pay-hutang');
        if (!currentDebt) {
            toastr.error('Data hutang belum siap');
            return;
        }

        const cleaned = modalPayments
            .filter(row => Number(row.jumlah_bayar || 0) > 0)
            .map(row => ({
                cara_bayar: row.cara_bayar,
                tanggal_bayar: row.tanggal_bayar ? normalizeDateTime(row.tanggal_bayar) : '',
                jumlah_bayar: Number(row.jumlah_bayar || 0),
                bank_nama: row.bank_nama || '',
                rekening_no: row.rekening_no || ''
            }));

        if (!cleaned.length) {
            toastr.error('Masukkan minimal satu cicilan');
            return;
        }

        const totalPay = cleaned.reduce((sum, row) => sum + row.jumlah_bayar, 0);
        if (totalPay > Number(currentDebt.sisa_bayar || 0)) {
            toastr.error('Nominal cicilan melebihi sisa hutang');
            return;
        }

        for (const row of cleaned) {
            if (row.cara_bayar === 'TRANSFER' && (!row.bank_nama || !row.rekening_no)) {
                toastr.error('Transfer wajib isi bank dan rekening');
                return;
            }
        }

        $.ajax({
            type: 'POST',
            url: `<?= base_url('/pembelian/pay') ?>/${$('#modal-beli-id').val()}`,
            dataType: 'json',
            data: {
                payment_json: JSON.stringify(cleaned)
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Pembayaran tersimpan');
                    hutangModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan pembayaran');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan pembayaran'));
            }
        });
    }

    function openSaldoModal(beliId = '') {
        const isEdit = !!beliId;
        $('#saldo-modal-title').text(isEdit ? 'Edit Saldo Hutang Awal' : 'Tambah Saldo Hutang Awal');
        $('#saldo-mode').val(isEdit ? 'edit' : 'create');
        $('#form-saldo-hutang')[0].reset();
        $('#saldo-beli-id').val('');
        $('#saldo-total').val('0');
        applyMoneyMask('#form-saldo-hutang');
        normalizeMoneyInputs('#form-saldo-hutang');

        $.getJSON(isEdit ? `<?= base_url('/hutang/saldo-form') ?>/${beliId}` : `<?= base_url('/hutang/saldo-form') ?>`, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Gagal memuat form saldo hutang');
                return;
            }

            const header = res.data?.header || {};
            $('#saldo-beli-id').val(header.beli_id || '');
            $('#saldo-supplier').val(header.supco || '').trigger('change');
            $('#saldo-invoice').val(header.invoice || '');
            $('#saldo-tanggal').val(header.tanggal || '');
            $('#saldo-jatuh-tempo').val(header.jatuh_tempo || '');
            $('#saldo-total').val(Number(header.total_gross || 0));
            $('#saldo-keterangan').val(header.keterangan || '');
            applyMoneyMask('#form-saldo-hutang');
            saldoHutangModal.show();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat form saldo hutang'));
        });
    }

    function submitSaldoHutang() {
        normalizeMoneyInputs('#form-saldo-hutang');
        const mode = $('#saldo-mode').val();
        const payload = {
            beli_id: $('#saldo-beli-id').val(),
            supco: $('#saldo-supplier').val(),
            invoice: $('#saldo-invoice').val().trim(),
            tanggal: $('#saldo-tanggal').val(),
            jatuh_tempo: $('#saldo-jatuh-tempo').val(),
            total_gross: Number(normalizeMoneyValue($('#saldo-total').val() || 0)),
            keterangan: $('#saldo-keterangan').val().trim()
        };

        if (!payload.supco || !payload.invoice || !payload.tanggal || !payload.jatuh_tempo) {
            toastr.error('Supplier, tanggal, invoice, dan jatuh tempo wajib diisi');
            return;
        }
        if (payload.total_gross <= 0) {
            toastr.error('Nominal hutang harus lebih besar dari nol');
            return;
        }

        $.ajax({
            type: mode === 'edit' ? 'PATCH' : 'PUT',
            url: '<?= base_url('/hutang/saldo') ?>',
            dataType: 'json',
            data: payload,
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Saldo hutang awal tersimpan');
                    saldoHutangModal.hide();
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan saldo hutang awal');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan saldo hutang awal'));
            }
        });
    }

    function deleteSaldoHutang(info) {
        const beliId = typeof info === 'object' ? info.beli_id : info;
        const invoice = typeof info === 'object' ? info.invoice : '-';
        const supplier = typeof info === 'object' ? info.supplier : '-';
        const tanggal = typeof info === 'object' ? info.tanggal : '-';
        const totalGross = typeof info === 'object' ? info.total_gross : '-';

        Swal.fire({
            title: 'Hapus Saldo Hutang Awal?',
            html: `
                <div class="text-start mt-2 p-3 bg-light border rounded" style="font-size: 0.88rem;">
                    <div class="mb-1 text-danger fw-bold"><i class="ti ti-alert-triangle me-1"></i> Anda akan menghapus saldo hutang berikut:</div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">ID Saldo Hutang:</span>
                        <strong class="font-monospace text-dark">${beliId}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Invoice:</span>
                        <strong class="font-monospace text-dark">${invoice}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Supplier:</span>
                        <span class="fw-semibold text-dark text-truncate text-end" style="max-width: 180px;">${supplier}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Tanggal:</span>
                        <span class="text-dark">${tanggal}</span>
                    </div>
                    <div class="d-flex justify-content-between pt-1 border-top">
                        <span class="text-muted">Total Hutang:</span>
                        <strong class="text-danger">Rp ${totalGross}</strong>
                    </div>
                </div>
                <small class="text-muted d-block mt-2 text-start">Perhatian: Data saldo hutang awal ini akan dihapus dari monitoring hutang.</small>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ti ti-trash me-1"></i> Ya, Hapus Saldo Hutang',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                type: 'DELETE',
                url: '<?= base_url('/hutang/saldo') ?>',
                dataType: 'json',
                data: {
                    beli_id: beliId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Saldo hutang awal dihapus');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal menghapus saldo hutang awal');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus saldo hutang awal'));
                }
            });
        });
    }

    function nowLocalValue() {
        const now = new Date();
        const tzOffset = now.getTimezoneOffset() * 60000;
        return new Date(now - tzOffset).toISOString().slice(0, 16);
    }

    function toDatetimeLocal(value) {
        if (!value) return nowLocalValue();
        const dt = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(dt.getTime())) return nowLocalValue();
        const tzOffset = dt.getTimezoneOffset() * 60000;
        return new Date(dt - tzOffset).toISOString().slice(0, 16);
    }

    function normalizeDateTime(value) {
        return value ? value.replace('T', ' ') + ':00' : '';
    }

    function addMonthToDate(value) {
        const base = new Date(`${value}T00:00:00`);
        if (Number.isNaN(base.getTime())) {
            return value;
        }

        const day = base.getDate();
        const next = new Date(base);
        next.setMonth(next.getMonth() + 1);
        if (next.getDate() !== day) {
            next.setDate(0);
        }

        const year = next.getFullYear();
        const month = String(next.getMonth() + 1).padStart(2, '0');
        const date = String(next.getDate()).padStart(2, '0');
        return `${year}-${month}-${date}`;
    }
</script>
<?= $this->endSection('javascript') ?>