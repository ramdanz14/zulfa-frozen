<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var string $defaultStartDate
 * @var string $defaultEndDate
 */
?>
<style>
    /* =========================================================
       MONITORING PENJUALAN CARDVIEW (Mobile/Tablet Reflow)
       ========================================================= */
    .sale-history-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .sale-history-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
    }

    .sale-history-card .dropdown-menu {
        z-index: 1055;
    }

    .sale-card-netto {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    .sale-card-id {
        font-family: var(--bs-font-monospace);
        font-size: 0.8rem;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        padding: 0.15rem 0.45rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
    }

    .sale-card-customer {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
        line-height: 1.25;
    }

    .sale-card-time {
        font-size: 0.8rem;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Detail Modal Item Cards on Mobile */
    .detail-item-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem;
        margin-bottom: 0.6rem;
    }

    .detail-item-card .row-1 {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }

    .detail-item-card .row-2 {
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

        #btn-apply-filter,
        #btn-reset-filter {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }
</style>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">Monitoring Penjualan</h4>
                        <p class="mb-0"><span class="page-pretitle">Total</span> | Daftar transaksi penjualan untuk edit, hapus, reprint, dan buat retur.</p>
                        <small class="text-muted d-block mt-1">Edit dan hapus hanya diizinkan untuk transaksi dengan tanggal hari ini.</small>
                    </div>
                    <div class="col-3">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-8 col-md-7">
                        <label class="form-label fw-semibold text-dark small text-uppercase mb-1">Range Tanggal Transaksi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="ti ti-calendar text-primary"></i></span>
                            <input type="text" class="form-control" id="filter-range" readonly>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-5 d-grid d-sm-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btn-apply-filter"><i class="ti ti-filter me-1"></i> Terapkan Filter</button>
                        <button type="button" class="btn btn-light w-100" id="btn-reset-filter"><i class="ti ti-refresh me-1"></i> Hari Ini</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td>No data to show</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CardView Template for Monitoring Penjualan (Mobile/Tablet Reflow) -->
                <template id="card-listjual-template">
                    <div class="sale-history-card p-3 h-100 shadow-sm">
                        <!-- Header Kartu: Customer Highlight & Dropdown Aksi -->
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <div style="flex: 1; min-width: 0;">
                                <div class="sale-card-customer text-truncate" data-dtcv-field="2"></div>
                                <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                    <span class="sale-card-id" data-dtcv-field="1"></span>
                                    <span class="status-bayar-badge" data-dtcv-field="7"></span>
                                </div>
                            </div>
                            <div class="flex-shrink-0" data-dtcv-field="9"></div>
                        </div>

                        <!-- Baris Nilai & Waktu Transaksi (Key Highlights) -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Netto</small>
                                <span class="sale-card-netto text-success" data-dtcv-field="6"></span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Waktu Transaksi</small>
                                <span class="sale-card-time fw-medium" data-dtcv-field="0"></span>
                            </div>
                        </div>

                        <!-- Info Rincian: Gross, Jumlah Jenis Item & Total Qty -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top text-muted small" style="font-size: 0.78rem;">
                            <div>
                                <span>Gross: </span><span class="fw-semibold text-dark" data-dtcv-field="5"></span>
                            </div>
                            <div>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="ti ti-package me-1"></i><span data-dtcv-field="3"></span> jenis (<span data-dtcv-field="4"></span> qty)
                                </span>
                            </div>
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
            <div class="modal-header">
                <h5 class="modal-title">Detail Penjualan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detail-content"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const detailModal = new bootstrap.Modal(document.getElementById('modal-detail'));
    let filterStart = moment().startOf('day');
    let filterEnd = moment().endOf('day');

    $(function() {
        $('#filter-range').daterangepicker({
            startDate: filterStart,
            endDate: filterEnd,
            autoApply: true,
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Batal',
                fromLabel: 'Dari',
                toLabel: 'Sampai',
                customRangeLabel: 'Pilih Sendiri',
                weekLabel: 'M',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                firstDay: 1
            },
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            filterStart = start;
            filterEnd = end;
        });

        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
    });

    <?php if (session()->getFlashdata('error')) : ?>
        toastr.error("<?= session()->getFlashdata('error') ?>");
    <?php endif; ?>

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: ['pageLength']
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
            url: '<?= base_url('/listjual/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.start_date = filterStart.format('YYYY-MM-DD');
                d.end_date = filterEnd.format('YYYY-MM-DD');
            }
        },
        cardView: {
            enable: true,
            breakpoint: 768,
            template: '#card-listjual-template',
            gridClass: 'col-12 col-sm-6 col-lg-4 mb-3',
            onCardRender: function($card, rowData, rowIdx, rowNode) {
                // Ensure card dropdown menu has touch friendly tap targets
                $card.find('.dropdown-item').addClass('py-2');
            }
        },
        columns: [{
                data: 'tgl',
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
                data: 'jual_id',
                title: 'ID Jual',
                className: 'font-monospace'
            },
            {
                data: 'customer_nama',
                title: 'Customer',
                render: function(data, type, row) {
                    return `<div class="fw-semibold text-dark">${data || 'Pelanggan Umum'}</div><small class="text-muted font-monospace">${row.cust_id || 'CUST-GENERAL'}</small>`;
                }
            },
            {
                data: 'jml_item',
                title: 'Jenis',
                className: 'text-center'
            },
            {
                data: 'total_qty',
                title: 'Qty',
                className: 'text-end',
                render: data => Number(data || 0).toLocaleString('id-ID')
            },
            {
                data: 'gross',
                title: 'Gross',
                className: 'text-end',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: 'netto',
                title: 'Netto',
                className: 'text-end fw-bold text-dark',
                render: data => 'Rp ' + formatMoneyValue(data || 0)
            },
            {
                data: 'status_bayar',
                title: 'Status',
                className: 'text-center',
                render: function(data, type, row) {
                    const kredit = row.is_kredit === '1' ? 'KREDIT' : 'TUNAI';
                    let badge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">LUNAS</span>';
                    if (data === 'CICIL') {
                        badge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">CICIL</span>';
                    } else if (data === 'BELUM') {
                        badge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">BELUM</span>';
                    }
                    return `${badge}<div><small class="text-muted fw-semibold">${kredit}</small></div>`;
                }
            },
            {
                data: 'reprint_count',
                title: 'Reprint',
                className: 'text-center'
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(data) {
                    const editBtn = akses_menu?.akses_update === 'Y' ?
                        (data.can_edit ?
                            `<li><a class="dropdown-item py-2" href="<?= base_url('/listjual/edit') ?>/${data.jual_id}"><i class="ti ti-pencil text-warning me-2"></i> Edit</a></li>` :
                            `<li><a class="dropdown-item py-2 text-muted" href="javascript:void(0)" onclick="showLockedNotice()"><i class="ti ti-lock text-danger me-2"></i> Edit Terkunci</a></li>`) :
                        '';
                    const deleteBtn = akses_menu?.akses_delete === 'Y' ?
                        (data.can_edit ?
                            `<li><a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="deleteSale('${data.jual_id}')"><i class="ti ti-trash me-2"></i> Hapus</a></li>` :
                            `<li><a class="dropdown-item py-2 text-muted" href="javascript:void(0)" onclick="showLockedNotice()"><i class="ti ti-lock text-danger me-2"></i> Hapus Terkunci</a></li>`) :
                        '';
                    const returBtn = akses_menu?.akses_create === 'Y' ?
                        `<li><a class="dropdown-item py-2" href="<?= base_url('/returjual/add') ?>?jual_id=${encodeURIComponent(data.jual_id)}"><i class="ti ti-repeat text-primary me-2"></i> Buat Retur</a></li>` :
                        '';
                    return `<div class="dropdown">
                        <button class="btn btn-light dropdown-toggle align-text-top btn-sm border" type="button" data-bs-boundary="viewport" data-bs-toggle="dropdown">Actions</button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="showDetail('${data.jual_id}')"><i class="ti ti-eye text-info me-2"></i> Detail</a></li>
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="chooseReprintFormat('${data.jual_id}')"><i class="ti ti-printer text-success me-2"></i> Reprint</a></li>
                            ${returBtn}
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

    $('#btn-apply-filter').on('click', function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').on('click', function() {
        filterStart = moment().startOf('day');
        filterEnd = moment().endOf('day');
        $('#filter-range').data('daterangepicker').setStartDate(filterStart);
        $('#filter-range').data('daterangepicker').setEndDate(filterEnd);
        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        table.ajax.reload();
    });

    function showDetail(jualId) {
        $('#detail-content').html('<div class="text-center py-5 text-muted">Memuat detail...</div>');
        detailModal.show();
        $.getJSON(`<?= base_url('/listjual/show') ?>/${jualId}`, function(res) {
            if (res.tipe !== 'success') {
                $('#detail-content').html(`<div class="alert alert-danger mb-0">${res.data || 'Detail tidak ditemukan'}</div>`);
                return;
            }

            const data = res.data;
            // Desktop table rows
            const detailRows = (data.details || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${row.nama_item || row.kode_item}<br><small class="text-muted">${row.kode_item || '-'}</small></td>
                    <td class="text-end">${Number(row.qty_jual || 0).toLocaleString('id-ID')}</td>
                    <td>${row.sat_id || '-'}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.price || 0)}</td>
                    <td class="text-end">Rp ${formatMoneyValue(row.diskon_item || 0)}</td>
                    <td class="text-end fw-bold">Rp ${formatMoneyValue(row.netto || 0)}</td>
                </tr>
            `).join('');

            // Mobile cards (2 rows per card: baris 1 no, item, satuan, qty; baris 2 info harga)
            const detailMobileCards = (data.details || []).length ? (data.details || []).map((row, idx) => {
                const diskonBadge = Number(row.diskon_item || 0) > 0 ?
                    `<span class="badge bg-danger-subtle text-danger ms-1">-Rp ${formatMoneyValue(row.diskon_item || 0)}</span>` : '';
                return `
                <div class="detail-item-card">
                    <!-- Baris 1: No, Item, Satuan, Qty -->
                    <div class="row-1">
                        <div class="d-flex align-items-start gap-2" style="flex: 1; min-width: 0;">
                            <span class="badge bg-light text-dark border px-2 py-1">${idx + 1}</span>
                            <div class="text-truncate">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">${row.nama_item || row.kode_item}</div>
                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">${row.kode_item || '-'}</small>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.8rem;">
                                ${Number(row.qty_jual || 0).toLocaleString('id-ID')} ${row.sat_id || ''}
                            </span>
                        </div>
                    </div>

                    <!-- Baris 2: Informasi Harga (Price, Diskon, Netto) -->
                    <div class="row-2">
                        <div class="text-muted small">
                            @ Rp ${formatMoneyValue(row.price || 0)}${diskonBadge}
                        </div>
                        <div class="fw-bold text-success text-end" style="font-size: 0.95rem;">
                            Rp ${formatMoneyValue(row.netto || 0)}
                        </div>
                    </div>
                </div>
                `;
            }).join('') : '<div class="text-center py-3 text-muted">Tidak ada detail item</div>';

            const payRows = (data.payments || []).length ? (data.payments || []).map((row, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>${row.cara_bayar || '-'}</td>
                    <td>${row.bank_nama || '-'}</td>
                    <td>${row.rekening_no || '-'}</td>
                    <td class="text-end fw-bold">Rp ${formatMoneyValue(row.nominal_bayar || 0)}</td>
                </tr>
            `).join('') : '<tr><td colspan="5" class="text-center text-muted">Belum ada pembayaran</td></tr>';

            $('#detail-content').html(`
                <!-- Info Header Transaksi -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block">ID Jual</small><div class="fw-bold text-dark font-monospace">${data.jual_id || '-'}</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block">Tanggal</small><div class="fw-semibold text-dark">${data.tgl ? new Date(data.tgl).toLocaleString('id-ID') : '-'}</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block">Customer</small><div class="fw-semibold text-dark text-truncate">${data.customer_nama || 'Pelanggan Umum'}</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100 bg-light-subtle"><small class="text-muted d-block">Kasir</small><div class="fw-semibold text-dark">${data.updid || '-'}</div></div></div>
                </div>

                <!-- Info Nilai & Ringkasan Transaksi -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100"><small class="text-muted d-block">Gross</small><div class="fw-semibold text-dark">Rp ${formatMoneyValue(data.gross || 0)}</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100"><small class="text-muted d-block">Diskon Nota</small><div class="fw-semibold text-danger">Rp ${formatMoneyValue(data.diskon_nota || 0)}</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100 border-success-subtle bg-success-subtle"><small class="text-success d-block fw-semibold">Total Netto</small><div class="fw-bold fs-4 text-success">Rp ${formatMoneyValue(data.netto || 0)}</div></div></div>
                    <div class="col-6 col-md-3"><div class="border rounded p-2 p-md-3 h-100"><small class="text-muted d-block">Reprint</small><div class="fw-semibold text-dark">${Number(data.reprint_count || 0).toLocaleString('id-ID')}x</div></div></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">Daftar Item Belanja</h6>
                    <span class="badge bg-light text-muted border">${(data.details || []).length} Item</span>
                </div>

                <!-- Mobile View: Dua Baris per Card -->
                <div class="d-md-none mb-3">
                    ${detailMobileCards}
                </div>

                <!-- Desktop View: Tabel Standar -->
                <div class="table-responsive d-none d-md-block mb-3">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">No</th>
                                <th>Item</th>
                                <th class="text-end">Qty</th>
                                <th>Satuan</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Diskon</th>
                                <th class="text-end">Netto</th>
                            </tr>
                        </thead>
                        <tbody>${detailRows || '<tr><td colspan="7" class="text-center text-muted">Tidak ada detail</td></tr>'}</tbody>
                    </table>
                </div>

                <h6 class="fw-bold mb-2">Informasi Pembayaran</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">No</th>
                                <th>Metode</th>
                                <th>Bank / E-Wallet</th>
                                <th>No. Rekening</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>${payRows}</tbody>
                    </table>
                </div>
            `);
        }).fail(function(xhr) {
            $('#detail-content').html(`<div class="alert alert-danger mb-0">${extractErrorMessage(xhr, 'Gagal memuat detail')}</div>`);
        });
    }

    function deleteSale(jualId) {
        Swal.fire({
            title: 'Hapus transaksi penjualan ini?',
            text: 'Stok barang akan dikembalikan dan poin customer akan disesuaikan ulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST',
                url: '<?= base_url('/listjual') ?>',
                dataType: 'json',
                data: {
                    _method: 'DELETE',
                    jual_id: jualId
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Berhasil');
                        table.ajax.reload(null, false);
                        return;
                    }
                    toastr.error(res.data || 'Gagal menghapus transaksi');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menghapus transaksi'));
                }
            });
        });
    }

    function chooseReprintFormat(jualId) {
        Swal.fire({
            title: 'Pilih format reprint',
            text: `Cetak ulang transaksi ${jualId}`,
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Struk',
            denyButtonText: 'Faktur',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(`<?= base_url('/listjual/reprint') ?>/${jualId}?format=struk`, '_blank', 'noopener');
                return;
            }
            if (result.isDenied) {
                window.open(`<?= base_url('/listjual/reprint') ?>/${jualId}?format=faktur`, '_blank', 'noopener');
            }
        });
    }

    function showLockedNotice() {
        toastr.error('Transaksi ini bukan tanggal hari ini sehingga tidak bisa diedit atau dihapus.');
    }
</script>
<?= $this->endSection('javascript') ?>