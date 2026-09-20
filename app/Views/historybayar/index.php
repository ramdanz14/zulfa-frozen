<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array  $supplierOptions
 */
?>
<style>
    /* =========================================================
       HISTORY PEMBAYARAN MOBILE & DESKTOP STYLING
       ========================================================= */
    .history-main-cell {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .history-sup-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
    }
    .history-date-badge {
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
    .history-id-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        color: #475569;
        background: #f8fafc;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }
    .history-amount {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0d6efd;
    }

    @media (max-width: 767.98px) {
        /* Full width search bar & buttons on mobile */
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

    /* Styling for detail modal cards */
    .detail-card-metric {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }
    .edit-input-card {
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
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-7">
                        <h4 class="fw-bold mb-1 text-dark">History Pembayaran Supplier</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold">Total Data : 0</span> | Riwayat semua pembayaran pembelian & cicilan kredit.</p>
                        <div class="small text-muted mt-1">
                            <i class="ti ti-lock me-1 text-primary"></i>Closing aktif: <span class="fw-bold text-dark font-monospace"><?= esc($closingDate ?? '-') ?></span>. <span class="d-none d-sm-inline">Pembayaran sebelum tanggal ini dikunci dari edit & hapus.</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-5 text-md-end">
                        <div class="d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="<?= base_url('/hutang') ?>" class="btn btn-primary btn-sm px-3 py-2 fw-semibold">
                                <i class="ti ti-credit-card me-1"></i>Menu Hutang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-building-store me-1"></i>Filter Supplier</label>
                        <select class="form-select form-select-sm select2" id="filter-supplier">
                            <option value="">Semua Supplier</option>
                            <?php foreach ($supplierOptions as $row) : ?>
                                <option value="<?= esc($row['supco']) ?>"><?= esc($row['supco']) ?> - <?= esc($row['nama'] ?? $row['supco']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-calendar me-1"></i>Range Tanggal Bayar</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="ti ti-calendar-event"></i></span>
                            <input type="text" class="form-control" id="filter-range" readonly style="background-color: #fff; cursor: pointer;">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="row g-1">
                            <div class="col-7">
                                <button type="button" class="btn btn-primary btn-sm w-100 py-2 fw-semibold" id="btn-filter">
                                    <i class="ti ti-filter me-1"></i>Terapkan
                                </button>
                            </div>
                            <div class="col-5">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-2" id="btn-reset">
                                    <i class="ti ti-refresh me-1"></i>Reset
                                </button>
                            </div>
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
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit / Detail Pembayaran -->
<div class="modal fade" id="modal-web" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div id="loadingOverlay" class="d-none justify-content-center align-items-center" style="position:absolute;top:0;left:0;width:100%;height:100%;background-color:rgba(255,255,255,.8);z-index:1051;border-radius:12px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="modal-header border-bottom py-2 px-3 bg-light">
                <h5 class="modal-title fw-bold fs-5 text-dark" id="modal-title">Edit Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="modal-form">
                <div class="modal-body modal-body-compact p-3"></div>
                <div class="modal-footer border-top py-2 px-3 justify-content-between bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold" id="btn-aksi">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>

<script>
    const akses_menu = <?= $akses_menu ?>;
    let filterStart = moment().startOf('month');
    let filterEnd = moment().endOf('month');

    $(function() {
        $('.select2').select2({
            width: '100%'
        });

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

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary btn-sm';
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-History-Pembayaran',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6],
                        orthogonal: 'export'
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
            url: '<?= base_url('/historybayar/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.supco = $('#filter-supplier').val();
                d.date_start = filterStart.format('YYYY-MM-DD');
                d.date_end = filterEnd.format('YYYY-MM-DD');
            }
        },
        columns: [
            {
                data: 'tanggal_bayar',
                title: 'Tgl Bayar',
                className: 'col-desktop-only font-monospace small',
                render: data => data ? new Date(String(data).replace(' ', 'T')).toLocaleString('id-ID') : '-'
            },
            {
                data: 'supplier_nama',
                title: 'Informasi Pembayaran & Supplier',
                render: function(data, type, row) {
                    const supNama = data || row.supco || '-';
                    const tglBayarFormat = row.tanggal_bayar ? new Date(String(row.tanggal_bayar).replace(' ', 'T')).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) : '-';
                    const nominalText = 'Rp ' + formatMoneyValue(row.jumlah_bayar);
                    const invoice = row.invoice || '-';
                    const beliId = row.beli_id || '-';
                    const caraBayar = row.cara_bayar || 'TUNAI';

                    // Metode badge
                    let metodeBadge = '<span class="badge bg-success-subtle text-success">TUNAI</span>';
                    if (caraBayar === 'TRANSFER') {
                        metodeBadge = '<span class="badge bg-primary-subtle text-primary">TRANSFER</span>';
                    } else if (caraBayar === 'POTONGAN RETUR') {
                        metodeBadge = '<span class="badge bg-warning-subtle text-warning">RETUR</span>';
                    }

                    // Bank text if transfer
                    let bankDetail = '';
                    if (caraBayar === 'TRANSFER' && (row.bank_nama || row.rekening_no)) {
                        bankDetail = `<span class="text-muted font-monospace" style="font-size:0.75rem;"><i class="ti ti-building-bank me-1"></i>${row.bank_nama || '-'} (${row.rekening_no || '-'})</span>`;
                    }

                    // Status bayar invoice
                    const statusBadge = row.status_bayar === 'LUNAS' ?
                        '<span class="badge bg-success-subtle text-success">LUNAS</span>' :
                        '<span class="badge bg-warning-subtle text-warning">BELUM</span>';

                    return `
                        <div class="history-main-cell">
                            <!-- BARIS 1: NAMA SUPPLIER & TANGGAL BAYAR -->
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <div class="history-sup-name">
                                    <i class="ti ti-building-store text-primary d-inline d-md-none me-1"></i>${supNama}
                                </div>
                                <span class="history-date-badge d-inline-flex d-md-none">
                                    <i class="ti ti-calendar"></i> ${tglBayarFormat}
                                </span>
                            </div>

                            <!-- BARIS 2 KHUSUS MOBILE: FAKTUR, METODE, DAN NOMINAL -->
                            <div class="d-flex align-items-center gap-1 flex-wrap d-md-none mt-1" style="font-size: 0.775rem;">
                                <span class="history-id-badge"><i class="ti ti-hash"></i>${beliId}</span>
                                <span class="badge bg-light text-muted border font-monospace"><i class="ti ti-file-invoice"></i> ${invoice}</span>
                                ${metodeBadge}
                                <span class="history-amount ms-auto">${nominalText}</span>
                            </div>

                            <!-- BARIS 3 KHUSUS MOBILE: REKENING & STATUS -->
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap d-md-none mt-1">
                                ${bankDetail ? bankDetail : '<span></span>'}
                                <div>
                                    <span class="small text-muted me-1" style="font-size: 0.7rem;">Faktur:</span>
                                    ${statusBadge}
                                </div>
                            </div>

                            <!-- SUBTITLE DESKTOP -->
                            <small class="text-muted d-none d-md-block">
                                <span class="font-monospace me-2"><i class="ti ti-file-invoice me-1"></i>Inv: ${invoice}</span>
                                <span class="badge bg-light text-secondary border font-monospace me-1">Beli ID: ${beliId}</span>
                            </small>
                        </div>
                    `;
                }
            },
            {
                data: 'beli_id',
                title: 'ID Beli / Faktur',
                className: 'col-desktop-only font-monospace',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${data || '-'}</div><small class="text-muted font-monospace">${row.tanggal || '-'}</small>`;
                }
            },
            {
                data: 'cara_bayar',
                title: 'Metode',
                className: 'text-center col-desktop-only',
                render: function(data) {
                    if (data === 'TRANSFER') return '<span class="badge bg-primary-subtle text-primary">TRANSFER</span>';
                    if (data === 'POTONGAN RETUR') return '<span class="badge bg-warning-subtle text-warning">RETUR</span>';
                    return '<span class="badge bg-success-subtle text-success">TUNAI</span>';
                }
            },
            {
                data: 'bank_nama',
                title: 'Bank / Rekening',
                className: 'col-desktop-only',
                render: function(data, type, row) {
                    return row.cara_bayar === 'TRANSFER' ? `<div class="fw-semibold">${data || '-'}</div><small class="text-muted font-monospace">${row.rekening_no || '-'}</small>` : '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'jumlah_bayar',
                title: 'Nominal',
                className: 'text-end col-desktop-only fw-bold text-primary',
                render: data => 'Rp ' + formatMoneyValue(data)
            },
            {
                data: 'status_bayar',
                title: 'Status Faktur',
                className: 'text-center col-desktop-only',
                render: function(data, type, row) {
                    return `<span class="badge ${row.status_bayar === 'LUNAS' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}">${row.status_bayar}</span>`;
                }
            },
            {
                title: 'Aksi',
                data: null,
                className: 'text-center',
                render: function(data) {
                    const isLocked = data.can_modify === false;
                    const editBtn = akses_menu?.akses_update === 'Y' ?
                        (isLocked ?
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockMessage('${data.closing_date}','${data.cara_bayar}')"><i class="ti ti-lock text-danger me-1"></i> ${data.cara_bayar === 'POTONGAN RETUR' ? 'Kelola dari Retur' : 'Edit Terkunci'}</a>` :
                            `<a class="dropdown-item" href="javascript:void(0)" onclick="editBayar(${data.bayar_id})"><i class="ti ti-pencil text-warning me-1"></i> Edit Pembayaran</a>`) :
                        '';
                    
                    const deletePayload = JSON.stringify({
                        bayar_id: data.bayar_id,
                        supplier: (data.supplier_nama || data.supco || '-').replace(/"/g, '&quot;'),
                        beli_id: data.beli_id,
                        invoice: data.invoice || '-',
                        tanggal_bayar: data.tanggal_bayar ? new Date(String(data.tanggal_bayar).replace(' ', 'T')).toLocaleString('id-ID') : '-',
                        cara_bayar: data.cara_bayar,
                        bank_nama: data.bank_nama || '',
                        rekening_no: data.rekening_no || '',
                        jumlah_bayar: 'Rp ' + formatMoneyValue(data.jumlah_bayar)
                    }).replace(/"/g, '&quot;');

                    const deleteBtn = akses_menu?.akses_delete === 'Y' ?
                        (isLocked ?
                            `<a class="dropdown-item text-muted" href="javascript:void(0)" onclick="showLockMessage('${data.closing_date}','${data.cara_bayar}')"><i class="ti ti-lock text-danger me-1"></i> ${data.cara_bayar === 'POTONGAN RETUR' ? 'Hapus via Retur' : 'Hapus Terkunci'}</a>` :
                            `<a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDeleteBayar(${deletePayload})"><i class="ti ti-trash text-danger me-1"></i> Hapus Pembayaran</a>`) :
                        '';

                    return `
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle py-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical d-md-none"></i>
                                <span class="d-none d-md-inline">Aksi</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                ${editBtn ? `<li>${editBtn}</li>` : ''}
                                ${deleteBtn ? `<li>${deleteBtn}</li>` : ''}
                            </ul>
                        </div>
                    `;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Data : ${json?.recordsFiltered || 0}`);
    });

    $('#btn-filter').on('click', function() {
        table.ajax.reload();
    });

    $('#btn-reset').on('click', function() {
        $('#filter-supplier').val('').trigger('change');
        filterStart = moment().startOf('month');
        filterEnd = moment().endOf('month');
        $('#filter-range').data('daterangepicker').setStartDate(filterStart);
        $('#filter-range').data('daterangepicker').setEndDate(filterEnd);
        $('#filter-range').val(`${filterStart.format('DD/MM/YYYY')} - ${filterEnd.format('DD/MM/YYYY')}`);
        table.ajax.reload();
    });

    $('#modal-form').validate({
        rules: {
            cara_bayar: 'required',
            tanggal_bayar: 'required',
            jumlah_bayar: 'required'
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

    function editBayar(bayarId) {
        $('#loadingOverlay').removeClass('d-none').addClass('d-flex');
        $('#modal-form > .modal-body').empty();
        $('#btn-aksi').removeAttr('class').prop('disabled', false).addClass('btn btn-warning btn-sm px-4 fw-semibold').text('Simpan Perubahan');
        $('#modal-title').html('<i class="ti ti-edit me-1 text-warning"></i> Edit Pembayaran');

        $.getJSON(`<?= base_url('/historybayar/show') ?>/${bayarId}`, function(res) {
            $('#loadingOverlay').removeClass('d-flex').addClass('d-none');
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Data pembayaran tidak ditemukan');
                return;
            }
            const data = res.data;

            $('#modal-form > .modal-body').append(`
                <input type="hidden" name="bayar_id" id="bayar_id" value="${data.bayar_id}">
                <input type="hidden" name="_method" id="_method" value="PATCH">
                
                <!-- Info Banner Transaksi -->
                <div class="edit-input-card bg-light-primary border-primary-subtle mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted fw-semibold">Supplier:</span>
                        <span class="badge bg-primary-subtle text-primary font-monospace">${data.beli_id}</span>
                    </div>
                    <div class="fw-bold text-dark fs-5 mb-1">${data.supplier_nama || data.supco}</div>
                    <div class="small text-muted font-monospace"><i class="ti ti-file-invoice me-1"></i>Faktur Inv: ${data.invoice || '-'}</div>
                </div>

                <!-- Form Inputs Card -->
                <div class="edit-input-card">
                    <div class="form-group mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-wallet me-1"></i>Metode Pembayaran</label>
                        <select class="form-select" name="cara_bayar" id="cara_bayar">
                            <option value="TUNAI" ${data.cara_bayar === 'TUNAI' ? 'selected' : ''}>TUNAI</option>
                            <option value="TRANSFER" ${data.cara_bayar === 'TRANSFER' ? 'selected' : ''}>TRANSFER</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-calendar-time me-1"></i>Tanggal & Waktu Bayar</label>
                        <input type="datetime-local" class="form-control" name="tanggal_bayar" id="tanggal_bayar" value="${toDatetimeLocal(data.tanggal_bayar)}">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-cash me-1"></i>Nominal Pembayaran (Rp)</label>
                        <input type="text" class="form-control money fs-5 fw-bold text-primary" name="jumlah_bayar" id="jumlah_bayar" value="${data.jumlah_bayar}">
                    </div>

                    <div class="form-group mb-3 transfer-only">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-building-bank me-1"></i>Nama Bank</label>
                        <input type="text" class="form-control" name="bank_nama" id="bank_nama" placeholder="Contoh: BCA / Mandiri / BRI" value="${data.bank_nama || ''}">
                    </div>

                    <div class="form-group mb-2 transfer-only">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-credit-card me-1"></i>Nomor Rekening</label>
                        <input type="text" class="form-control font-monospace" name="rekening_no" id="rekening_no" placeholder="Contoh: 1234567890" value="${data.rekening_no || ''}">
                    </div>
                </div>
            `);

            applyMoneyMask('#modal-form');
            bindTransferVisibility();
            $('#modal-web').modal('show');
        }).fail(function(xhr) {
            $('#loadingOverlay').removeClass('d-flex').addClass('d-none');
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat data pembayaran'));
        });
    }

    function confirmDeleteBayar(info) {
        const bayarId = info.bayar_id;
        const supplier = info.supplier || '-';
        const beliId = info.beli_id || '-';
        const invoice = info.invoice || '-';
        const tglBayar = info.tanggal_bayar || '-';
        const nominal = info.jumlah_bayar || '-';
        const caraBayar = info.cara_bayar || 'TUNAI';

        Swal.fire({
            title: 'Hapus Pembayaran Ini?',
            html: `
                <div class="text-start mt-2 p-3 bg-light border rounded" style="font-size: 0.88rem;">
                    <div class="mb-2 text-danger fw-bold"><i class="ti ti-alert-triangle me-1"></i> Konfirmasi penghapusan riwayat pembayaran:</div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Supplier:</span>
                        <strong class="text-dark text-truncate" style="max-width: 180px;">${supplier}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">ID Beli / Inv:</span>
                        <span class="font-monospace text-dark">${beliId} (${invoice})</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Tgl Bayar:</span>
                        <span class="text-dark">${tglBayar}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Metode:</span>
                        <span class="badge ${caraBayar === 'TRANSFER' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'}">${caraBayar}</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top">
                        <span class="text-muted fw-semibold">Nominal Dihapus:</span>
                        <strong class="text-danger fs-6">${nominal}</strong>
                    </div>
                </div>
                <div class="small text-danger text-start mt-2">
                    <i class="ti ti-info-circle me-1"></i>Sisa hutang pada faktur ini akan otomatis bertambah kembali sesuai nominal pembayaran yang dihapus.
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="ti ti-trash me-1"></i> Ya, Hapus Pembayaran',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingOverlay').removeClass('d-none').addClass('d-flex');
                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('/historybayar') ?>',
                    dataType: 'json',
                    data: {
                        bayar_id: bayarId,
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        $('#loadingOverlay').removeClass('d-flex').addClass('d-none');
                        if (res.tipe === 'success') {
                            toastr.success(res.data || 'Pembayaran berhasil dihapus');
                            table.ajax.reload(null, false);
                        } else {
                            toastr.error(res.data || 'Gagal menghapus pembayaran');
                        }
                    },
                    error: function(xhr) {
                        $('#loadingOverlay').removeClass('d-flex').addClass('d-none');
                        toastr.error(extractErrorMessage(xhr, 'Gagal menghapus pembayaran'));
                    }
                });
            }
        });
    }

    function bindTransferVisibility() {
        const toggle = () => {
            const isTransfer = $('#cara_bayar').val() === 'TRANSFER';
            $('.transfer-only').toggleClass('d-none', !isTransfer);
        };
        $('#cara_bayar').off('change').on('change', toggle);
        toggle();
    }

    function saveAjax() {
        normalizeMoneyInputs('#modal-form');
        const formData = $('#modal-form').serializeArray();
        $('#loadingOverlay').removeClass('d-none').addClass('d-flex');
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/historybayar') ?>',
            dataType: 'json',
            data: formData,
            success: function(res) {
                $('#loadingOverlay').removeClass('d-flex').addClass('d-none');
                $('#modal-web').modal('hide');
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Berhasil disimpan');
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan');
            },
            error: function(xhr) {
                $('#loadingOverlay').removeClass('d-flex').addClass('d-none');
                toastr.error(extractErrorMessage(xhr, 'Gagal simpan perubahan'));
            }
        });
    }

    function showLockMessage(closingDate, caraBayar = '') {
        if (caraBayar === 'POTONGAN RETUR') {
            toastr.error('Pembayaran POTONGAN RETUR hanya bisa dikelola dari menu retur pembelian.');
            return;
        }
        toastr.error(`Pembayaran sebelum ${new Date(closingDate).toLocaleDateString('id-ID')} sudah melewati closing dan dikunci.`);
    }

    function toDatetimeLocal(value) {
        if (!value) return '';
        const dt = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(dt.getTime())) return '';
        const tzOffset = dt.getTimezoneOffset() * 60000;
        return new Date(dt - tzOffset).toISOString().slice(0, 16);
    }
</script>
<?= $this->endSection('javascript') ?>

