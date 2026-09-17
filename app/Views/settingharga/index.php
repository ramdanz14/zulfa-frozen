<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $recentInvoices
 */
$aksesMenuParsed = is_string($akses_menu ?? null) ? (json_decode($akses_menu, true) ?: []) : ($akses_menu ?? []);
$canUpdateUser = (!empty($aksesMenuParsed['akses_update']) && $aksesMenuParsed['akses_update'] === 'Y');
?>

<style>
    /* Styling & Anti-Slop Layout Mobile / Human Optimizations */
    :root {
        --color-focus-ring: #0284c7;
        --color-profit: #15803d;
        --color-loss: #b91c1c;
    }

    .form-control:focus,
    .btn:focus-visible,
    .form-select:focus {
        outline: 2px solid var(--color-focus-ring) !important;
        outline-offset: 1px !important;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2) !important;
    }

    /* Desktop input size */
    @media (min-width: 768px) {
        .btn-save-row {
            min-width: 42px;
            min-height: 38px;
        }
    }

    /* Filter Chips */
    .filter-chips-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .filter-chip-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
        transition: all 0.15s ease;
        cursor: pointer;
        min-height: 36px;
    }

    .filter-chip-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .filter-chip-btn.active {
        background: #0284c7;
        border-color: #0284c7;
        color: #ffffff;
    }

    .filter-chip-btn.chip-warning {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #92400e;
    }

    .filter-chip-btn.chip-warning.active {
        background: #d97706;
        border-color: #d97706;
        color: #ffffff;
    }

    /* Guidance Alert (Compact) */
    .guide-alert {
        background-color: #eff6ff;
        border-left: 4px solid #2563eb;
        border-radius: 6px;
        padding: 8px 12px;
        color: #1e3a8a;
        font-size: 0.825rem;
    }

    /* Status Profit / Loss Badges */
    .profit-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .profit-pill.pill-profit {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .profit-pill.pill-loss {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .profit-pill.pill-warning {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .profit-pill.pill-neutral {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    /* Highlight states */
    tr.row-loss {
        background-color: #fef2f2 !important;
    }

    tr.row-dirty {
        background-color: #f0fdf4 !important;
    }

    .btn-save-row.is-dirty {
        background-color: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
        font-weight: 700;
        animation: pulseSave 1.5s infinite;
    }

    @keyframes pulseSave {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    /* ======================================================== */
    /* COMPACT RESPONSIVE MOBILE CARD REFLOW (< 768px)          */
    /* ======================================================== */
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

        #table-data thead {
            display: none !important;
        }

        #table-data,
        #table-data tbody {
            display: block !important;
            width: 100% !important;
        }

        /* Ubah struktur grid card mobile menjadi lebih lega untuk input harga */
        #table-data tbody tr {
            display: grid !important;
            grid-template-columns: 1fr 1fr auto !important;
            /* Ubah grid agar input harga mendominasi */
            gap: 6px 8px !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 10px !important;
            margin-bottom: 8px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
            align-items: center !important;
        }

        #table-data tbody tr.row-loss {
            border: 1.5px solid #ef4444 !important;
            background-color: #fffafa !important;
        }

        #table-data tbody tr.row-dirty {
            border: 1.5px solid #22c55e !important;
            background-color: #f0fdf4 !important;
        }

        #table-data tbody td {
            display: block !important;
            padding: 0 !important;
            border: none !important;
        }

        /* Kolom 0: Kode item disembunyikan di card mobile */
        #table-data tbody td:nth-child(1) {
            display: none !important;
        }

        /* Kolom 1: Nama Barang & Satuan (Lebar penuh / 2 kolom span) */
        #table-data tbody td:nth-child(2) {
            grid-column: 1 / 3 !important;
        }

        /* Kolom 2: Satuan standalone disembunyikan */
        #table-data tbody td:nth-child(3) {
            display: none !important;
        }

        /* Kolom 3: Input HPP (Modal) - Mengambil 1 kolom penuh di sebelah kiri */
        #table-data tbody td:nth-child(4) {
            grid-column: 1 / 2 !important;
        }

        /* Kolom 4: Input Harga Jual - Mengambil 1 kolom penuh di sebelah kanan */
        #table-data tbody td:nth-child(5) {
            grid-column: 2 / 3 !important;
        }

        /* Kolom 5: Status Laba/Rugi (Pindah ke bawah input harga, span penuh) */
        #table-data tbody td:nth-child(6) {
            grid-column: 1 / 3 !important;
            margin-top: 2px !important;
        }

        /* Kolom 6: Tombol Simpan (Disejajarkan di samping status atau full width bawah) */
        #table-data tbody td:nth-child(7) {
            grid-column: 3 / 4 !important;
            grid-row: 2 / 4 !important;
            /* Membuat tombol simpan memanjang ke bawah sejajar input harga */
            display: flex !important;
            align-items: center;
            justify-content: flex-end;
        }

        /* Perbesar tinggi tombol simpan di mobile agar mudah diklik */
        .btn-save-row {
            width: 100%;
            height: 100%;
            min-height: 70px !important;
            padding: 4px 8px !important;
            border-radius: 6px !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .compact-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 2px;
            display: block;
            text-transform: uppercase;
        }


    }

    @media (min-width: 768px) {
        .compact-label {
            display: none !important;
        }
    }
</style>

<div class="body-wrapper pb-5">
    <div class="container-fluid p-0">
        <!-- Header Banner (Compact) -->
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-2">
            <div class="card-body px-3 py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-semibold mb-0 fs-5"><i class="ti ti-tags me-1"></i> Setting & Koreksi Harga</h5>
                        <small class="text-muted"><span class="page-pretitle">Memuat data...</span></small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetAllFilters()">
                            <i class="ti ti-refresh"></i> <span class="d-none d-sm-inline">Reset</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Card (Compact) -->
        <div class="card mb-2 shadow-sm border-0">
            <div class="card-body p-2 p-md-3">
                <!-- Info Banner Singkat -->
                <div class="guide-alert mb-2 d-flex align-items-center justify-content-between">
                    <div>
                        <i class="ti ti-bulb me-1 text-primary"></i> <strong>Tips:</strong> Ketik HPP atau Harga Jual, lalu tekan tombol <strong>Simpan</strong> di sampingnya. Klik Kode Item untuk histori beli.
                    </div>
                </div>

                <!-- Quick Filter Chips -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="filter-chips-container">
                        <button type="button" class="filter-chip-btn active" id="chip-filter-all" onclick="setQuickFilter('')">
                            <i class="ti ti-box"></i> Semua Item
                        </button>
                        <button type="button" class="filter-chip-btn chip-warning" id="chip-filter-wrong" onclick="setQuickFilter('salah-harga')">
                            <i class="ti ti-alert-triangle"></i> Cek Salah Setting Harga (Rugi / Rp 0)
                        </button>
                    </div>

                    <!-- Filter Faktur Pembelian Supplier -->
                    <div class="d-flex align-items-center gap-2" style="min-width: 220px; flex-grow: 1; max-width: 400px;">
                        <select class="form-select form-select-sm" id="invoice_filter">
                            <option value="">-- Semua Faktur --</option>
                            <option value="salah-harga">⚠️ Salah Setting Harga</option>
                            <?php foreach (($recentInvoices ?? []) as $row): ?>
                                <option value="<?= esc($row['beli_id']) ?>">
                                    <?= esc($row['beli_id']) ?> | <?= esc($row['supplier_nama'] ?? '-') ?> (<?= esc($row['tanggal']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel / Kartu Setting Harga -->
        <div class="card shadow-sm border-0">

            <div class="table-responsive">
                <table id="table-data" class="table table-bordered align-middle w-100 mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th style="width: 13%;">Kode Item</th>
                            <th style="width: 28%;">Nama Barang</th>
                            <th style="width: 7%;" class="text-center">Sat</th>
                            <th style="width: 20%;">Harga Pokok (Modal)</th>
                            <th style="width: 20%;">Harga Jual (Konsumen)</th>
                            <th style="width: 12%;" class="text-center">Margin & Status</th>
                            <th style="width: 8%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Memuat data barang...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal History Pembelian Supplier -->
<div class="modal fade" id="purchase-history-modal" tabindex="-1" aria-labelledby="purchaseHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0" id="purchaseHistoryModalLabel">
                        <i class="ti ti-history me-1"></i> Riwayat Pembelian Supplier
                    </h6>
                    <div class="small text-white-50" id="purchase-history-item-label">-</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3">
                <div id="purchase-history-loading" class="text-center py-3 d-none">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    <span>Memuat riwayat pembelian...</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Faktur</th>
                                <th class="text-center">Sat</th>
                                <th class="text-end">Harga Beli</th>
                                <th class="text-end">Harga Dasar</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-history-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-2">Klik kode item untuk melihat riwayat.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2 px-3">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    // Evaluasi hak akses update dari PHP secara langsung
    const canUpdate = <?= $canUpdateUser ? 'true' : 'false' ?>;
    const pendingChanges = {};
    const purchaseHistoryModal = new bootstrap.Modal(document.getElementById('purchase-history-modal'));
    const initialSearchText = new URLSearchParams(window.location.search).get('search_text') || '';

    DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary btn-sm';

    // Inisialisasi DataTable dengan serverSide processing
    const table = $('#table-data').DataTable({
        layout: {
            topStart: {
                buttons: [{
                    text: '<i class="ti ti-file-type-xls"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Laporan-Setting-Harga',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5],
                        orthogonal: 'export'
                    }
                }, 'pageLength']
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
        // -----------------------------------------------------------
        lengthMenu: [
            [25, 50, 100, -1],
            ['25 baris', '50 baris', '100 baris', 'Semua']
        ],
        responsive: false, // Reflow mobile menggunakan CSS Grid ultra-compact untuk stabilitas input
        lengthChange: false,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '<?= base_url('/settingharga/ajax') ?>',
            type: 'post',
            data: function(d) {
                d.beli_id = $('#invoice_filter').val();
            }
        },
        columns: [
            // Kolom 0: Kode Item (Desktop)
            {
                data: 'kode_item',
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    return `
                        <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold text-primary btn-item-history text-start" 
                                data-kode-item="${escapeHtml(data)}" data-nama-item="${escapeHtml(row.nama_item || '')}"
                                title="Histori Pembelian Supplier">
                            <i class="ti ti-history me-1"></i>${escapeHtml(data)}
                        </button>
                    `;
                }
            },
            // Kolom 1: Nama Item (Mobile & Desktop Header)
            {
                data: 'nama_item',
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    return `
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                            <span class="fw-bold text-dark fs-3 text-truncate" title="${escapeHtml(data)}">${escapeHtml(data)}</span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">${escapeHtml(row.sat_id)}</span>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted d-md-none btn-item-history" 
                                        data-kode-item="${escapeHtml(row.kode_item)}" data-nama-item="${escapeHtml(data)}"
                                        title="Histori Pembelian">
                                    <i class="ti ti-history"></i> <small>${escapeHtml(row.kode_item)}</small>
                                </button>
                            </div>
                        </div>
                    `;
                }
            },
            // Kolom 2: Satuan (Desktop Only)
            {
                data: 'sat_id',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge bg-light text-dark border px-2 py-1">${escapeHtml(data)}</span>`;
                }
            },
            // Kolom 3: Harga Pokok (Modal)
            {
                data: 'harga_pokok',
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    const state = getRowState(row);
                    const valHp = formatMoneyValue(state.harga_pokok);
                    return `
                        <div>
                            <span class="compact-label">HPP (Modal)</span>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text py-0 px-2 text-muted fw-semibold">Rp</span>
                                <input type="text" class="form-control form-control-sm money input-hpp" value="${valHp}" 
                                       inputmode="numeric" placeholder="0" aria-label="Harga Pokok"
                                       ${canUpdate ? '' : 'disabled'}>
                            </div>
                        </div>
                    `;
                }
            },
            // Kolom 4: Harga Jual (Konsumen)
            {
                data: 'harga_jual',
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    const state = getRowState(row);
                    const valHj = formatMoneyValue(state.harga_jual);
                    return `
                        <div>
                            <span class="compact-label">Harga Jual</span>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text py-0 px-2 text-muted fw-semibold">Rp</span>
                                <input type="text" class="form-control form-control-sm money input-hjual" value="${valHj}" 
                                       inputmode="numeric" placeholder="0" aria-label="Harga Jual"
                                       ${canUpdate ? '' : 'disabled'}>
                            </div>
                        </div>
                    `;
                }
            },
            // Kolom 5: Margin & Status
            {
                data: 'target_psn_margin',
                className: 'text-center',
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    const state = getRowState(row);
                    const status = calculateProfitStatus(state.harga_pokok, state.harga_jual);
                    return `
                        <div class="status-profit-container text-md-center text-start">
                            <span class="profit-pill ${status.pillClass}">
                                <i class="ti ${status.icon}"></i> ${status.text}
                            </span>
                        </div>
                        <input type="hidden" class="input-margin" value="${Number(state.target_psn_margin || 0).toFixed(1)}">
                    `;
                }
            },
            // Kolom 6: Aksi Simpan
            {
                title: 'Aksi',
                className: 'text-center',
                data: null,
                render: function(data, type, row) {
                    if (!canUpdate) {
                        return '<span class="badge bg-light text-muted border">Readonly</span>';
                    }
                    return `
                        <button type="button" class="btn btn-sm btn-outline-primary btn-save-row" title="Simpan Koreksi Harga">
                            <i class="ti ti-device-floppy"></i> <span class="d-inline d-md-none ms-1">Simpan</span>
                        </button>
                    `;
                }
            }
        ],
        rowCallback: function(row, data) {
            const key = getRowKey(data);
            $(row).attr('data-key', key);
            const state = getRowState(data);
            applyRowVisuals($(row), data, state);
        },
        drawCallback: function() {
            applyMoneyMask('#table-data');
        }
    });

    table.on('xhr.dt', function(e, settings, json) {
        $('.page-pretitle').text(`Total Item: ${json?.recordsFiltered || 0}`);
    });

    if (initialSearchText) {
        table.search(initialSearchText).draw();
    }

    // ========================================================
    // FILTER MANAGEMENT & CHIPS
    // ========================================================
    function setQuickFilter(val) {
        $('#invoice_filter').val(val);
        $('.filter-chip-btn').removeClass('active');
        if (val === '') {
            $('#chip-filter-all').addClass('active');
        } else if (val === 'salah-harga') {
            $('#chip-filter-wrong').addClass('active');
        }
        table.ajax.reload();
    }

    function resetAllFilters() {
        $('#invoice_filter').val('');
        $('.filter-chip-btn').removeClass('active');
        $('#chip-filter-all').addClass('active');
        table.search('').ajax.reload();
    }

    $('#invoice_filter').on('change', function() {
        const val = $(this).val();
        $('.filter-chip-btn').removeClass('active');
        if (val === '') {
            $('#chip-filter-all').addClass('active');
        } else if (val === 'salah-harga') {
            $('#chip-filter-wrong').addClass('active');
        }
        table.ajax.reload();
    });

    // ========================================================
    // KALKULASI & INTERAKTIVITAS INPUT HARGA
    // ========================================================
    $('#table-data tbody').on('input', '.input-hpp', function() {
        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        if (!rowData) return;
        const state = ensurePendingState(rowData);
        state.harga_pokok = Number(normalizeMoneyValue($(this).val() || 0));
        recalcFromHpp(rowData, state);
        syncStateToRow($tr, rowData, state);
    });

    $('#table-data tbody').on('blur', '.input-hpp', function() {
        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        if (!rowData) return;
        const state = ensurePendingState(rowData);
        state.harga_pokok = Number(normalizeMoneyValue($(this).val() || 0));
        recalcFromHpp(rowData, state);
        syncStateToRow($tr, rowData, state);
    });

    $('#table-data tbody').on('input', '.input-hjual', function() {
        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        if (!rowData) return;
        const state = ensurePendingState(rowData);
        state.harga_jual = Number(normalizeMoneyValue($(this).val() || 0));
        recalcMargin(state);
        syncStateToRow($tr, rowData, state);
    });

    $('#table-data tbody').on('blur', '.input-hjual', function() {
        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        if (!rowData) return;
        const state = ensurePendingState(rowData);
        state.harga_jual = Number(normalizeMoneyValue($(this).val() || 0));
        recalcMargin(state);
        syncStateToRow($tr, rowData, state);
    });

    $('#table-data tbody').on('click', '.btn-save-row', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        if (!rowData) return;
        submitRowCorrection(rowData, $tr);
    });

    $('#table-data tbody').on('click', '.btn-item-history', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const kodeItem = $(this).data('kode-item');
        const namaItem = $(this).data('nama-item') || '';
        openPurchaseHistory(kodeItem, namaItem);
    });

    function getRowKey(row) {
        return `${row.kode_item}__${row.sat_id}`;
    }

    function getRowState(row) {
        const key = getRowKey(row);
        return pendingChanges[key] || {
            kode_item: row.kode_item,
            sat_id: row.sat_id,
            harga_pokok: Number(row.harga_pokok || 0),
            harga_jual: Number(row.harga_jual || 0),
            target_psn_margin: Number(row.target_psn_margin || 0)
        };
    }

    function ensurePendingState(row) {
        const key = getRowKey(row);
        if (!pendingChanges[key]) {
            pendingChanges[key] = getRowState(row);
        }
        return pendingChanges[key];
    }

    function recalcFromHpp(row, state) {
        const hargaPokok = Number(state.harga_pokok || 0);
        const margin = Number(state.target_psn_margin || row.target_psn_margin || 0);
        let hargaJual = Math.round(hargaPokok + (hargaPokok * margin / 100));
        if (row.can_round_50) {
            hargaJual = roundUpTo50(hargaJual);
        }
        state.harga_jual = hargaJual;
    }

    function recalcMargin(state) {
        const hargaPokok = Number(state.harga_pokok || 0);
        const hargaJual = Number(state.harga_jual || 0);
        state.target_psn_margin = hargaPokok > 0 ? (((hargaJual - hargaPokok) / hargaPokok) * 100) : 0;
    }

    function calculateProfitStatus(hp, hj) {
        if (hp <= 0 || hj <= 0) {
            return {
                type: 'empty',
                pillClass: 'pill-warning',
                icon: 'ti-alert-triangle',
                text: 'Harga Rp 0'
            };
        }

        const profit = hj - hp;
        const margin = (((hj - hp) / hp) * 100).toFixed(1);

        if (profit < 0) {
            return {
                type: 'loss',
                pillClass: 'pill-loss',
                icon: 'ti-alert-circle',
                text: `RUGI Rp ${formatMoneyValue(Math.abs(profit))} (${margin}%)`
            };
        } else if (profit === 0) {
            return {
                type: 'breakeven',
                pillClass: 'pill-neutral',
                icon: 'ti-arrows-diff',
                text: 'Impas (0.0%)'
            };
        } else {
            return {
                type: 'profit',
                pillClass: 'pill-profit',
                icon: 'ti-trending-up',
                text: `Untung +Rp ${formatMoneyValue(profit)} (+${margin}%)`
            };
        }
    }

    function syncStateToRow($tr, row, state) {
        const valHp = formatMoneyValue(state.harga_pokok);
        const valHj = formatMoneyValue(state.harga_jual);

        $tr.find('.input-hpp').val(valHp);
        $tr.find('.input-hjual').val(valHj);
        $tr.find('.input-margin').val(Number(state.target_psn_margin || 0).toFixed(1));

        applyRowVisuals($tr, row, state);
        cleanupPendingState(row, state);
    }

    function applyRowVisuals($tr, row, state) {
        const hp = Number(state.harga_pokok || 0);
        const hj = Number(state.harga_jual || 0);
        const status = calculateProfitStatus(hp, hj);

        // Update Profit Pill
        $tr.find('.status-profit-container').html(`
            <span class="profit-pill ${status.pillClass}">
                <i class="ti ${status.icon}"></i> ${status.text}
            </span>
        `);

        // Cek perubahan data (isDirty)
        const hpOrig = Number(row.harga_pokok || 0);
        const hjOrig = Number(row.harga_jual || 0);
        const isDirty = (hp !== hpOrig || hj !== hjOrig);

        const $btnSave = $tr.find('.btn-save-row');

        if (status.type === 'loss') {
            $tr.addClass('row-loss').removeClass('row-dirty');
            $tr.find('.input-hjual').addClass('is-invalid');
            $btnSave.removeClass('btn-outline-primary btn-success is-dirty').addClass('btn-danger');
            $btnSave.find('span').text('RUGI');
        } else {
            $tr.removeClass('row-loss');
            $tr.find('.input-hjual').removeClass('is-invalid');

            if (isDirty) {
                $tr.addClass('row-dirty');
                $btnSave.removeClass('btn-outline-primary btn-danger').addClass('btn-success is-dirty');
                $btnSave.find('span').text('Simpan');
            } else {
                $tr.removeClass('row-dirty');
                $btnSave.removeClass('btn-success btn-danger is-dirty').addClass('btn-outline-primary');
                $btnSave.find('span').text('Simpan');
            }
        }
    }

    function cleanupPendingState(row, state) {
        const key = getRowKey(row);
        const hpOrig = Number(row.harga_pokok || 0);
        const hjOrig = Number(row.harga_jual || 0);
        if (hpOrig === Number(state.harga_pokok || 0) && hjOrig === Number(state.harga_jual || 0)) {
            delete pendingChanges[key];
        } else {
            pendingChanges[key] = state;
        }
    }

    // ========================================================
    // JEGATAN VALIDASI & SUBMIT KOREKSI HARGA
    // ========================================================
    function submitRowCorrection(row, $tr) {
        if (!canUpdate) {
            toastr.error('Anda tidak memiliki akses untuk simpan koreksi harga');
            return;
        }

        const key = getRowKey(row);
        const state = pendingChanges[key] || getRowState(row);
        const hp = Number(state.harga_pokok || 0);
        const hj = Number(state.harga_jual || 0);

        // JEGATAN 1: Cek apakah ada perubahan
        if (Number(row.harga_pokok || 0) === hp && Number(row.harga_jual || 0) === hj) {
            toastr.info('Belum ada perubahan harga pada item ini.');
            return;
        }

        // JEGATAN 2: Cek harga 0 atau kosong
        if (hp <= 0 || hj <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Harga Belum Lengkap!',
                html: `Harga Pokok dan Harga Jual untuk <strong>${escapeHtml(row.nama_item)}</strong> (${escapeHtml(row.sat_id)}) harus lebih besar dari Rp 0.`,
                confirmButtonText: 'Perbaiki'
            });
            return;
        }

        // JEGATAN 3: Cegah Jual Rugi (Harga Jual < Harga Pokok)
        if (hj < hp) {
            const lossRp = hp - hj;
            Swal.fire({
                icon: 'error',
                title: 'Peringatan Harga Jual Rugi!',
                html: `
                    Item: <strong>${escapeHtml(row.nama_item)}</strong> (${escapeHtml(row.sat_id)})<br><br>
                    Harga Pokok (Modal): <b>Rp ${formatMoneyValue(hp)}</b><br>
                    Harga Jual: <b>Rp ${formatMoneyValue(hj)}</b><br><br>
                    <span class="text-danger fw-bold fs-5">
                        <i class="ti ti-alert-triangle"></i> Terjadi RUGI Rp ${formatMoneyValue(lossRp)} per barang!
                    </span><br><br>
                    <small class="text-muted">Sistem memblokir penyimpanan harga jual yang lebih rendah dari modal untuk mencegah kerugian toko.</small>
                `,
                confirmButtonText: 'Perbaiki Harga Jual'
            });
            return;
        }

        // Simpan Koreksi ke Backend
        const $btn = $tr.find('.btn-save-row');
        const origBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            type: 'PATCH',
            url: '<?= base_url('/settingharga') ?>',
            dataType: 'json',
            data: {
                source_beli_id: $('#invoice_filter').val() || 'KOREKSI',
                kode_item: row.kode_item,
                sat_id: row.sat_id,
                harga_pokok: hp,
                harga_jual: hj
            },
            success: function(res) {
                $btn.prop('disabled', false).html(origBtnHtml);
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Koreksi harga berhasil disimpan');
                    delete pendingChanges[key];
                    table.ajax.reload(null, false);
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan koreksi harga');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(origBtnHtml);
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan koreksi harga'));
            }
        });
    }

    // ========================================================
    // MODAL HISTORI PEMBELIAN SUPPLIER
    // ========================================================
    function openPurchaseHistory(kodeItem, namaItem) {
        $('#purchase-history-item-label').text(`${kodeItem}${namaItem ? ' | ' + namaItem : ''}`);
        $('#purchase-history-body').html('');
        $('#purchase-history-loading').removeClass('d-none');
        purchaseHistoryModal.show();

        $.ajax({
            url: `<?= base_url('/settingharga/history') ?>/${encodeURIComponent(kodeItem)}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const rows = Array.isArray(res?.data) ? res.data : [];
                renderPurchaseHistory(rows);
            },
            error: function(xhr) {
                $('#purchase-history-body').html(`
                    <tr>
                        <td colspan="6" class="text-center text-danger py-2">
                            ${escapeHtml(extractErrorMessage(xhr, 'Gagal memuat riwayat pembelian'))}
                        </td>
                    </tr>
                `);
            },
            complete: function() {
                $('#purchase-history-loading').addClass('d-none');
            }
        });
    }

    function renderPurchaseHistory(rows) {
        if (!rows.length) {
            $('#purchase-history-body').html(`
                <tr>
                    <td colspan="6" class="text-center text-muted py-2">Belum ada riwayat pembelian supplier untuk item ini.</td>
                </tr>
            `);
            return;
        }

        const html = rows.map(function(row) {
            const supplier = [row.supplier_nama || '-', row.supco || ''].filter(Boolean).join(' / ');
            const faktur = [row.beli_id || '-', row.invoice || ''].filter(Boolean).join(' | ');

            return `
                <tr>
                    <td>${escapeHtml(row.tanggal || '-')}</td>
                    <td>${escapeHtml(supplier || '-')}</td>
                    <td>${escapeHtml(faktur || '-')}</td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${escapeHtml(row.sat_id || '-')}</span></td>
                    <td class="text-end fw-semibold">Rp ${formatMoneyValue(row.price || 0)}</td>
                    <td class="text-end text-primary fw-semibold">Rp ${formatMoneyValue(row.price_dasar || 0)}</td>
                </tr>
            `;
        }).join('');

        $('#purchase-history-body').html(html);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function roundUpTo50(value) {
        const number = Number(value || 0);
        if (number <= 0) return 0;
        return Math.ceil(number / 50) * 50;
    }
</script>
<?= $this->endSection('javascript') ?>