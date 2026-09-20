<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 */
?>
<style>
    /* =========================================================
       HISTORY PERUBAHAN HARGA MOBILE & DESKTOP STYLING
       ========================================================= */
    .historybeli-main-cell {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .historybeli-item-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
    }
    .historybeli-code-badge {
        font-family: var(--bs-font-monospace);
        font-size: 0.75rem;
        color: #475569;
        background: #f8fafc;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }
    .historybeli-time-badge {
        font-size: 0.75rem;
        color: #64748b;
        background: #f1f5f9;
        padding: 0.15rem 0.45rem;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .historybeli-price-delta {
        font-family: var(--bs-font-monospace);
        font-size: 0.785rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.25rem 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
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

        .dt-container .dt-buttons {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 4px !important;
            margin-bottom: 0.5rem !important;
        }

        /* Sembunyikan kolom desktop individual agar tidak sesak di HP */
        #table-data .col-desktop-only,
        .dt-container #table-data .col-desktop-only,
        #table-data th.col-desktop-only,
        #table-data td.col-desktop-only,
        table.dataTable th.col-desktop-only,
        table.dataTable td.col-desktop-only {
            display: none !important;
            width: 0 !important;
            max-width: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            overflow: hidden !important;
        }

        /* Hapus space kosong dari colgroup DataTables di HP */
        #table-data colgroup {
            display: none !important;
        }

        #table-data {
            table-layout: auto !important;
            width: 100% !important;
        }

        #table-data th,
        #table-data td {
            padding: 0.65rem 0.5rem !important;
            vertical-align: middle;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Header Banner -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-8">
                        <h4 class="fw-bold mb-1 text-dark">History Perubahan Harga</h4>
                        <p class="mb-0 text-muted small"><span class="page-pretitle fw-semibold">Total : 0</span> | Riwayat koreksi harga pokok & harga jual per item.</p>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <div class="small text-muted d-none d-md-block">Gunakan filter untuk melihat pergerakan harga naik atau turun.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-8">
                        <label class="form-label small fw-semibold text-muted mb-1 d-block">
                            <i class="ti ti-filter me-1"></i>Filter Perubahan Harga
                        </label>
                        <div class="btn-group w-100 w-md-auto d-flex d-md-inline-flex" role="group" aria-label="Filter perubahan harga">
                            <button type="button" class="btn btn-primary active btn-filter-jenis flex-fill flex-md-grow-0" data-jenis="">
                                Semua
                            </button>
                            <button type="button" class="btn btn-outline-success btn-filter-jenis flex-fill flex-md-grow-0" data-jenis="naik">
                                <i class="ti ti-trending-up"></i> Naik
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-filter-jenis flex-fill flex-md-grow-0" data-jenis="turun">
                                <i class="ti ti-trending-down"></i> Turun
                            </button>
                        </div>
                        <input type="hidden" id="filter-jenis" value="">
                    </div>
                    <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                        <button type="button" class="btn btn-light btn-sm px-3 w-100 w-md-auto" id="btn-reset-filter">
                            <i class="ti ti-refresh me-1"></i>Reset Filter
                        </button>
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
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary btn-sm';

    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-History-Perubahan-Harga',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
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
            url: '<?= base_url('/historybeli/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.jenis = $('#filter-jenis').val();
            }
        },
        drawCallback: function() {
            if (window.innerWidth < 768) {
                $('#table-data colgroup').remove();
            }
        },
        columns: [
            {
                data: 'updtime',
                title: 'Waktu',
                className: 'col-desktop-only font-monospace text-nowrap',
                render: function(data, type) {
                    if (type !== 'display') {
                        return data;
                    }
                    return data ? `<span class="historybeli-time-badge"><i class="ti ti-clock"></i> ${new Date(String(data).replace(' ', 'T')).toLocaleString('id-ID')}</span>` : '-';
                }
            },
            {
                data: 'kode_item',
                title: 'Kode Item',
                className: 'col-desktop-only font-monospace',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${escapeHtml(data || '-')}</div><small class="text-muted">${escapeHtml(row.sat_id || '-')}</small>`;
                }
            },
            {
                data: 'nama_item',
                title: 'Informasi Item & Perubahan',
                render: function(data, type, row) {
                    if (type === 'export' || type === 'sort') {
                        return data || row.kode_item || '-';
                    }

                    const namaItem = data || '-';
                    const kodeItem = row.kode_item || '-';
                    const satId = row.sat_id || '-';
                    const timeFormat = row.updtime ? new Date(String(row.updtime).replace(' ', 'T')).toLocaleString('id-ID') : '-';
                    const isNaik = row.jenis === 'naik';
                    const badgeJenis = isNaik
                        ? '<span class="badge bg-success-subtle text-success"><i class="ti ti-arrow-up-right me-1"></i>Naik</span>'
                        : '<span class="badge bg-danger-subtle text-danger"><i class="ti ti-arrow-down-right me-1"></i>Turun</span>';

                    const hppOld = formatMoneyValue(row.harga_pokok_old || 0);
                    const hppNew = formatMoneyValue(row.harga_pokok_new || 0);
                    const hjualOld = formatMoneyValue(row.harga_jual_old || 0);
                    const hjualNew = formatMoneyValue(row.harga_jual_new || 0);

                    return `
                        <div class="historybeli-main-cell">
                            <!-- BARIS 1: NAMA ITEM & BADGE PERUBAHAN -->
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <div class="historybeli-item-name">
                                    <i class="ti ti-box text-primary d-inline d-md-none me-1"></i>${escapeHtml(namaItem)}
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="d-inline d-md-none">${badgeJenis}</span>
                                    <span class="historybeli-code-badge">${escapeHtml(kodeItem)}</span>
                                </div>
                            </div>

                            <!-- BARIS 2 KHUSUS MOBILE: DETAIL WAKTU & SATUAN -->
                            <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap d-md-none mt-1">
                                <span class="historybeli-time-badge">
                                    <i class="ti ti-clock"></i>${timeFormat}
                                </span>
                                <span class="badge bg-light text-secondary border font-monospace">Satuan: ${escapeHtml(satId)}</span>
                            </div>

                            <!-- BARIS 3 KHUSUS MOBILE: PERBANDINGAN HPP & HARGA JUAL -->
                            <div class="d-flex align-items-center gap-2 flex-wrap d-md-none mt-1" style="font-size: 0.775rem;">
                                <div class="historybeli-price-delta">
                                    <span class="text-muted">HPP:</span>
                                    <span class="text-secondary text-decoration-line-through">${hppOld}</span>
                                    <i class="ti ti-arrow-right text-muted" style="font-size:0.7rem;"></i>
                                    <span class="fw-bold ${isNaik ? 'text-danger' : 'text-success'}">${hppNew}</span>
                                </div>
                                <div class="historybeli-price-delta">
                                    <span class="text-muted">Jual:</span>
                                    <span class="text-secondary text-decoration-line-through">${hjualOld}</span>
                                    <i class="ti ti-arrow-right text-muted" style="font-size:0.7rem;"></i>
                                    <span class="fw-bold text-primary">${hjualNew}</span>
                                </div>
                            </div>

                            <!-- SUBTITLE DESKTOP -->
                            <small class="text-muted d-none d-md-block font-monospace">
                                <i class="ti ti-barcode me-1"></i>${escapeHtml(kodeItem)} &bull; Satuan: ${escapeHtml(satId)}
                            </small>
                        </div>
                    `;
                }
            },
            {
                data: 'jenis',
                title: 'Perubahan',
                className: 'text-center col-desktop-only',
                render: function(data, type, row) {
                    if (type !== 'display') {
                        return data;
                    }
                    if (row.jenis === 'naik') {
                        return `<span class="badge bg-success-subtle text-success"><i class="ti ti-arrow-up-right"></i> Harga Naik</span>`;
                    }
                    return `<span class="badge bg-danger-subtle text-danger"><i class="ti ti-arrow-down-right"></i> Harga Turun</span>`;
                }
            },
            {
                data: 'harga_pokok_old',
                title: 'HPP Lama',
                className: 'text-end col-desktop-only font-monospace text-muted',
                render: data => formatMoneyValue(data || 0)
            },
            {
                data: 'harga_pokok_new',
                title: 'HPP Baru',
                className: 'text-end col-desktop-only font-monospace fw-bold',
                render: (data, type, row) => {
                    const cls = row.jenis === 'naik' ? 'text-danger' : 'text-success';
                    return `<span class="${cls}">${formatMoneyValue(data || 0)}</span>`;
                }
            },
            {
                data: 'harga_jual_old',
                title: 'HJual Lama',
                className: 'text-end col-desktop-only font-monospace text-muted',
                render: data => formatMoneyValue(data || 0)
            },
            {
                data: 'harga_jual_new',
                title: 'HJual Baru',
                className: 'text-end col-desktop-only font-monospace fw-bold text-primary',
                render: data => formatMoneyValue(data || 0)
            },
            {
                title: 'Action',
                data: null,
                className: 'text-center',
                render: function(data) {
                    const targetUrl = `<?= base_url('/settingharga') ?>?search_text=${encodeURIComponent(data.kode_item || '')}`;
                    return `
                        <a href="${targetUrl}" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center gap-1 py-1 px-2" title="Setting Harga Item">
                            <i class="ti ti-settings fs-4"></i>
                            <span class="d-none d-md-inline">Setting Harga</span>
                        </a>
                    `;
                }
            }
        ]
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total : ${json?.recordsFiltered || 0}`);
    });

    $('.btn-filter-jenis').on('click', function() {
        const jenis = $(this).data('jenis') || '';
        setJenisFilter(jenis);
        table.ajax.reload();
    });

    $('#btn-reset-filter').on('click', function() {
        setJenisFilter('');
        table.search('').draw();
    });

    function setJenisFilter(jenis) {
        $('#filter-jenis').val(jenis);
        $('.btn-filter-jenis')
            .removeClass('active btn-primary btn-success btn-danger text-white')
            .addClass('btn-outline-primary');

        $('.btn-filter-jenis[data-jenis="naik"]').removeClass('btn-outline-primary').addClass('btn-outline-success');
        $('.btn-filter-jenis[data-jenis="turun"]').removeClass('btn-outline-primary').addClass('btn-outline-danger');

        const $target = $(`.btn-filter-jenis[data-jenis="${jenis}"]`);
        if (jenis === 'naik') {
            $target.removeClass('btn-outline-success').addClass('active btn-success text-white');
        } else if (jenis === 'turun') {
            $target.removeClass('btn-outline-danger').addClass('active btn-danger text-white');
        } else {
            $target.removeClass('btn-outline-primary').addClass('active btn-primary text-white');
        }
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
