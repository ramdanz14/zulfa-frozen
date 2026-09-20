<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<style>
    /* =========================================================
       PIUTANG CUSTOMER MONITORING MOBILE & CARDVIEW STYLING
       ========================================================= */
    .piutang-main-cell {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .piutang-cust-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
    }
    .piutang-date-badge {
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
    .piutang-id-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        color: #475569;
        background: #f8fafc;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }
    .piutang-sisa-amount {
        font-weight: 700;
        font-size: 0.92rem;
        color: #d97706;
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
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-7">
                        <h4 class="fw-bold mb-1 text-dark">Monitoring Piutang Customer</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold">Total Data : 0</span> | Pantau piutang member, overdue, histori cicilan, & input pembayaran baru.</p>
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

                <!-- CardView Template for Piutang Customer (Mobile Reflow) -->
                <template id="card-piutang-template">
                    <div class="card h-100 border shadow-sm p-3 position-relative" style="border-radius: 10px; background: #fff;">
                        <!-- Baris Atas: Customer, ID Jual, & Action Dropdown -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div style="flex: 1; min-width: 0;">
                                <div class="fw-bold text-dark fs-3 text-truncate">
                                    <i class="ti ti-user text-warning me-1"></i><span data-dtcv-field="0_cust"></span>
                                </div>
                                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.75rem;">
                                        <i class="ti ti-hash"></i><span data-dtcv-field="0_id"></span>
                                    </span>
                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.75rem;" data-dtcv-field="0_kontak_badge">
                                        <i class="ti ti-phone"></i> <span data-dtcv-field="0_kontak"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="7"></div>
                        </div>

                        <!-- Baris Tengah: Tanggal Jual & Jatuh Tempo / Overdue -->
                        <div class="row g-2 text-muted small mb-2 align-items-center">
                            <div class="col-6 text-truncate" style="font-size: 0.775rem;">
                                <i class="ti ti-calendar me-1"></i><span data-dtcv-field="0_tgl"></span>
                            </div>
                            <div class="col-6 text-end text-truncate" style="font-size: 0.775rem;">
                                <span data-dtcv-field="0_jt"></span>
                            </div>
                        </div>

                        <!-- Baris Bawah: Hero Sisa Piutang, Terbayar, & Status -->
                        <div class="p-2 px-3 rounded-2 bg-warning-subtle border border-warning-subtle mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-warning-emphasis fw-semibold d-block" style="font-size: 0.72rem;">Sisa Piutang</small>
                                    <span class="fw-bolder text-warning-emphasis font-monospace fs-4" data-dtcv-field="4"></span>
                                </div>
                                <div class="text-end">
                                    <span data-dtcv-field="6"></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-1 mt-1 border-top border-warning-subtle text-muted small" style="font-size: 0.75rem;">
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

<!-- MODAL DETAIL & BAYAR PIUTANG (Altered to ergonomic responsive modal matching hutang) -->
<div class="modal fade" id="modal-piutang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning p-2 rounded-circle">
                        <i class="ti ti-receipt-2 fs-5"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark">Detail & Pembayaran Piutang</h6>
                        <small class="text-muted" id="piutang-modal-sub">-</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-compact p-3">
                <div id="piutang-detail" class="mb-3"></div>

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
                                <h6 class="fw-bold mb-0 text-dark small"><i class="ti ti-cash-banknote text-success me-1"></i> Input Cicilan / Pembayaran Baru</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" id="btn-add-modal-payment">
                                    <i class="ti ti-plus"></i> Tambah Cicilan
                                </button>
                            </div>
                            <div class="card-body p-2 p-md-3">
                                <form id="form-pay-piutang">
                                    <input type="hidden" id="modal-jual-id">
                                    <div id="modal-payment-list" class="d-flex flex-column mb-2" style="max-height: 280px; overflow-y: auto;"></div>

                                    <!-- Summary Sisa Piutang Realtime -->
                                    <div class="p-2 px-3 bg-warning-subtle rounded-3 border border-warning-subtle d-flex justify-content-between align-items-center mb-3">
                                        <span class="small fw-semibold text-warning-emphasis">Estimasi Sisa Piutang:</span>
                                        <span class="fw-bolder text-warning-emphasis font-monospace fs-4" id="modal-sisa-piutang">Rp 0</span>
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
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    let currentPiutang = null;
    let modalPayments = [];
    const piutangModal = new bootstrap.Modal(document.getElementById('modal-piutang'));
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';

    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    className: 'btn btn-outline-success btn-sm px-3',
                    extend: 'excelHtml5',
                    title: 'Laporan-Piutang',
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
            search: "Cari Piutang / Customer:",
            searchPlaceholder: "Ketik No ID, Customer, Kontak...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ piutang",
            infoEmpty: "Tidak ada data piutang",
            zeroRecords: "Data piutang tidak ditemukan",
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
        responsive: false,
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-piutang-template',
            gridClass: 'col-12 col-sm-6 col-lg-4 mb-3',
            onCardRender: function($card, rowData, rowIdx, rowNode) {
                const isLunas = rowData.status_bayar === 'LUNAS';
                const overdue = !isLunas && rowData.jatuh_tempo && rowData.jatuh_tempo < '<?= date('Y-m-d') ?>';
                const jtDate = rowData.jatuh_tempo ? new Date(rowData.jatuh_tempo).toLocaleDateString('id-ID') : '-';
                const tglFormat = rowData.tgl ? new Date(rowData.tgl).toLocaleDateString('id-ID') : '-';

                $card.find('[data-dtcv-field="0_cust"]').text(rowData.customer_nama || rowData.cust_id || '-');
                $card.find('[data-dtcv-field="0_id"]').text(rowData.jual_id || '-');

                if (rowData.customer_kontak) {
                    $card.find('[data-dtcv-field="0_kontak"]').text(rowData.customer_kontak);
                    $card.find('[data-dtcv-field="0_kontak_badge"]').removeClass('d-none');
                } else {
                    $card.find('[data-dtcv-field="0_kontak_badge"]').addClass('d-none');
                }

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
            url: '<?= base_url('/piutang/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.status_filter = $('#status_filter').val();
            }
        },
        columns: [{
                data: 'customer_nama',
                title: 'Customer',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${data || row.cust_id}</div><small class="text-muted">${row.cust_id}${row.customer_kontak ? ` | ${row.customer_kontak}` : ''}</small>`;
                }
            },
            {
                data: 'jual_id',
                title: 'ID / Tgl',
                render: function(data, type, row) {
                    return `<div class="fw-semibold font-monospace">${data}</div><small class="text-muted"><i class="ti ti-calendar"></i> ${new Date(row.tgl).toLocaleDateString('id-ID')}</small>`;
                }
            },
            {
                data: 'netto',
                title: 'Total Piutang',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'total_bayar',
                title: 'Terbayar',
                className: 'text-end text-success fw-semibold',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'sisa_piutang',
                title: 'Sisa Piutang',
                className: 'text-end fw-bold text-danger font-monospace',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'jatuh_tempo',
                title: 'Jatuh Tempo',
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
                className: 'text-center',
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
                responsivePriority: 1,
                render: function(data) {
                    return `
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle btn-sm px-2 py-1" data-bs-toggle="dropdown" aria-expanded="false" style="min-height: 36px; min-width: 40px;">
                                <i class="ti ti-dots-vertical fs-4"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                <a class="dropdown-item" href="javascript:void(0)" onclick="openPiutangModal('${data.jual_id}')"><i class="ti ti-cash text-success me-1"></i> Bayar Piutang</a>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="openPiutangModal('${data.jual_id}')"><i class="ti ti-history text-info me-1"></i> Lihat Detail & History</a>
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
            tgl_bayar: nowLocalValue(),
            nominal_bayar: 0,
            bank_nama: '',
            rekening_no: ''
        });
        renderModalPayments();
    });

    $('#form-pay-piutang').on('submit', function(e) {
        e.preventDefault();
        submitPiutangPayment();
    });

    function openPiutangModal(jualId) {
        currentPiutang = null;
        modalPayments = [{
            cara_bayar: 'TUNAI',
            tgl_bayar: nowLocalValue(),
            nominal_bayar: 0,
            bank_nama: '',
            rekening_no: ''
        }];
        $('#piutang-modal-sub').text(`Memuat Faktur ${jualId}...`);
        $('#piutang-detail').html(`
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-warning me-1" role="status"></div>
                Memuat data piutang...
            </div>
        `);
        $('#history-list').html('<div class="text-center py-3 text-muted small">Memuat histori pembayaran...</div>');
        $('#modal-jual-id').val(jualId);
        renderModalPayments();
        piutangModal.show();

        $.getJSON(`<?= base_url('/piutang/show') ?>/${jualId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#piutang-detail').html(`<div class="alert alert-danger mb-0">${res.data || 'Data piutang tidak ditemukan'}</div>`);
                return;
            }
            currentPiutang = res.data;
            $('#piutang-modal-sub').text(`${currentPiutang.jual_id} &bull; ${currentPiutang.customer_nama || currentPiutang.cust_id}`);
            renderPiutangHeader();
            renderHistory(currentPiutang.payments || []);
            renderModalPayments();
        }).fail(function(xhr) {
            $('#piutang-detail').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat data piutang')}</div>`);
        });
    }

    function renderPiutangHeader() {
        if (!currentPiutang) return;
        const overdue = currentPiutang.status_bayar !== 'LUNAS' && currentPiutang.jatuh_tempo && currentPiutang.jatuh_tempo < '<?= date('Y-m-d') ?>';
        const detailRows = currentPiutang.details || [];

        let statusBadge = '<span class="badge bg-danger-subtle text-danger">BELUM LUNAS</span>';
        if (currentPiutang.status_bayar === 'LUNAS') {
            statusBadge = '<span class="badge bg-success-subtle text-success">LUNAS</span>';
        } else if (currentPiutang.status_bayar === 'CICIL') {
            statusBadge = '<span class="badge bg-info-subtle text-info">CICIL</span>';
        }

        const jtLabel = currentPiutang.jatuh_tempo ? new Date(currentPiutang.jatuh_tempo).toLocaleDateString('id-ID') : '-';

        // Summary items preview
        const itemsPreview = detailRows.length ? `
            <div class="mt-2 pt-2 border-top">
                <small class="text-muted d-block mb-1" style="font-size: 0.72rem;">Barang Penjualan (${detailRows.length} item):</small>
                <div class="d-flex flex-wrap gap-1">
                    ${detailRows.slice(0, 4).map(r => `<span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.7rem;">${r.nama_item || r.kode_item} (${Number(r.qty_jual || 0).toLocaleString('id-ID')} ${r.sat_id})</span>`).join('')}
                    ${detailRows.length > 4 ? `<span class="badge bg-light text-muted border" style="font-size: 0.7rem;">+${detailRows.length - 4} item lainnya</span>` : ''}
                </div>
            </div>
        ` : '';

        $('#piutang-detail').html(`
            <div class="row g-2 mb-3">
                <div class="col-6 col-sm-3">
                    <div class="detail-card-metric">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Tanggal Transaksi</small>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">${new Date(currentPiutang.tgl).toLocaleDateString('id-ID')}</div>
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
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Customer & Kontak</small>
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">
                            ${currentPiutang.customer_nama || currentPiutang.cust_id}
                        </div>
                        <small class="text-secondary font-monospace" style="font-size: 0.75rem;">${currentPiutang.customer_kontak ? `<i class="ti ti-phone me-1"></i>${currentPiutang.customer_kontak}` : 'Cust ID: ' + currentPiutang.cust_id}</small>
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-4">
                    <div class="border rounded p-2 text-center bg-light">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Total Piutang</small>
                        <div class="fw-bold text-dark font-monospace" style="font-size: 0.95rem;">Rp ${formatMoneyValue(currentPiutang.netto)}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-2 text-center bg-light">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Total Terbayar</small>
                        <div class="fw-semibold text-success font-monospace" style="font-size: 0.95rem;">Rp ${formatMoneyValue(currentPiutang.total_bayar)}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-2 text-center bg-warning-subtle border-warning-subtle">
                        <small class="text-warning-emphasis d-block fw-semibold" style="font-size: 0.72rem;">Sisa Piutang</small>
                        <div class="fw-bolder text-warning-emphasis font-monospace" style="font-size: 0.95rem;">Rp ${formatMoneyValue(currentPiutang.sisa_piutang)}</div>
                    </div>
                </div>
            </div>

            <div class="p-2 px-3 rounded bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="small text-muted fw-semibold">Status Piutang:</span>
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
            const isNonTunai = row.cara_bayar === 'TRANSFER' || row.cara_bayar === 'QRIS';
            const bankInfo = row.cara_bayar === 'TRANSFER' ? `${row.bank_nama || '-'} &bull; ${row.rekening_no || '-'}` : (row.cara_bayar === 'QRIS' ? (row.bank_nama || 'QRIS') : 'Tunai / Kasir');
            $list.append(`
                <div class="p-2 px-3 border rounded bg-white" style="font-size: 0.82rem;">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge ${isNonTunai ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'} rounded-pill" style="font-size: 0.7rem;">
                                ${row.cara_bayar}
                            </span>
                            <span class="fw-bold text-dark font-monospace" style="font-size: 0.9rem;">Rp ${formatMoneyValue(row.nominal_bayar)}</span>
                        </div>
                        <small class="text-muted"><i class="ti ti-clock"></i> ${new Date(row.tgl_bayar).toLocaleDateString('id-ID')}</small>
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
            const isQris = row.cara_bayar === 'QRIS';
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
                                <option value="QRIS" ${row.cara_bayar === 'QRIS' ? 'selected' : ''}>QRIS</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1 text-success fw-semibold" style="font-size: 0.72rem;">Nominal (Rp)</label>
                            <input type="text" inputmode="numeric" class="form-control form-control-sm money text-end modal-amount font-monospace fw-bold" value="${row.nominal_bayar}">
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1" style="font-size: 0.72rem;">Waktu Bayar</label>
                            <input type="datetime-local" class="form-control form-control-sm modal-date" value="${toDatetimeLocal(row.tgl_bayar)}">
                        </div>
                        <div class="col-12 transfer-fields ${isTransfer || isQris ? '' : 'd-none'}">
                            <div class="row g-2">
                                <div class="${isTransfer ? 'col-6' : 'col-12'}">
                                    <input type="text" class="form-control form-control-sm modal-bank" placeholder="${isQris ? 'Bank / E-Wallet' : 'Nama Bank (BCA, dll)'}" value="${row.bank_nama || ''}">
                                </div>
                                <div class="col-6 ${isTransfer ? '' : 'd-none'}">
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
            } else if (modalPayments[idx].cara_bayar === 'QRIS') {
                modalPayments[idx].rekening_no = '';
            }
            renderModalPayments();
        });

        $('#modal-payment-list .modal-date').off('change').on('change', function() {
            const card = $(this).closest('.payment-input-card');
            const idx = Number(card.data('idx'));
            modalPayments[idx].tgl_bayar = $(this).val();
        });

        $('#modal-payment-list .modal-amount').off('input blur').on('input blur', function() {
            const card = $(this).closest('.payment-input-card');
            const idx = Number(card.data('idx'));
            modalPayments[idx].nominal_bayar = Number(normalizeMoneyValue($(this).val() || 0));
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
        const originalRemaining = Number(currentPiutang?.sisa_piutang || 0);
        const incoming = modalPayments.reduce((sum, row) => sum + Number(row.nominal_bayar || 0), 0);
        const nextRemaining = Math.max(originalRemaining - incoming, 0);
        $('#modal-sisa-piutang').text(`Rp ${formatMoneyValue(nextRemaining)}`);

        if (incoming > originalRemaining) {
            $('#modal-sisa-piutang').addClass('text-danger').removeClass('text-warning-emphasis');
        } else {
            $('#modal-sisa-piutang').removeClass('text-danger').addClass('text-warning-emphasis');
        }
    }

    function submitPiutangPayment() {
        normalizeMoneyInputs('#form-pay-piutang');
        if (!currentPiutang) {
            toastr.error('Data piutang belum siap');
            return;
        }

        const cleaned = modalPayments
            .filter(row => Number(row.nominal_bayar || 0) > 0)
            .map(row => ({
                cara_bayar: row.cara_bayar,
                tgl_bayar: row.tgl_bayar ? normalizeDateTime(row.tgl_bayar) : '',
                nominal_bayar: Number(row.nominal_bayar || 0),
                bank_nama: row.bank_nama || '',
                rekening_no: row.rekening_no || ''
            }));

        if (!cleaned.length) {
            toastr.error('Masukkan minimal satu cicilan');
            return;
        }

        const totalPay = cleaned.reduce((sum, row) => sum + row.nominal_bayar, 0);
        if (totalPay > Number(currentPiutang.sisa_piutang || 0)) {
            toastr.error('Nominal cicilan melebihi sisa piutang');
            return;
        }

        for (const row of cleaned) {
            if ((row.cara_bayar === 'TRANSFER' || row.cara_bayar === 'QRIS') && !row.bank_nama) {
                toastr.error('Pembayaran non tunai wajib isi bank atau e-wallet');
                return;
            }
            if (row.cara_bayar === 'TRANSFER' && !row.rekening_no) {
                toastr.error('Transfer wajib isi rekening');
                return;
            }
        }

        $.ajax({
            type: 'POST',
            url: `<?= base_url('/piutang/pay') ?>/${$('#modal-jual-id').val()}`,
            dataType: 'json',
            data: {
                payment_json: JSON.stringify(cleaned)
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Pembayaran tersimpan');
                    piutangModal.hide();
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
</script>
<?= $this->endSection('javascript') ?>
