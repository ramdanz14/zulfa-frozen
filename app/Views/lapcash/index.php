<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
?>
<style>
    .metric-card-cash {
        border-radius: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-touch-target {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
    }
    @media (max-width: 767.98px) {
        .metric-title {
            font-size: 0.78rem;
            line-height: 1.2;
        }
        .metric-value {
            font-size: 1.15rem !important;
            word-break: break-word;
        }
    }
    .cashflow-daily-card {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .cashflow-daily-card.is-opening {
        background-color: #f8fafc;
        border-style: dashed;
    }
    .cashflow-daily-card .field-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        font-size: 0.85rem;
    }
    .cashflow-daily-card .field-row-total {
        border-top: 1px dashed rgba(0,0,0,0.12);
        margin-top: 6px;
        padding-top: 6px;
        font-weight: 600;
    }
    .channel-box {
        background: rgba(0,0,0,0.02);
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 8px;
        padding: 8px 10px;
        margin-bottom: 8px;
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-md-7">
                        <h4 class="fw-semibold mb-1">Laporan Cash Flow Bulanan</h4>
                        <p class="mb-0 text-muted small"><span id="period-label">Periode aktif</span> | Mutasi keuangan tunai & non tunai.</p>
                    </div>
                    <div class="col-12 col-md-5 text-md-end mt-2 mt-md-0">
                        <div id="selected-store-info" class="badge bg-primary text-wrap text-start text-md-end" style="font-weight: 500;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body p-3 p-md-4">
                <div class="row g-2 g-md-3 align-items-end">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label small fw-medium mb-1">Periode Bulan</label>
                        <input type="month" class="form-control" id="filter-periode" value="<?= date('Y-m') ?>">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-5" id="filter-toko-wrapper" style="display:none;">
                        <label class="form-label small fw-medium mb-1">Filter Toko</label>
                        <select class="form-select select2" id="filter-toko" multiple>
                            <?php foreach ($tokoOptions as $row) : ?>
                                <option value="<?= esc($row['toko_id']) ?>"><?= esc($row['toko_id']) ?> - <?= esc($row['toko_nama'] ?? $row['toko_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="row g-2">
                            <div class="col-6 col-sm-6">
                                <button type="button" class="btn btn-primary w-100 btn-touch-target" id="btn-filter">
                                    <i class="ti ti-search me-1"></i> Tampilkan
                                </button>
                            </div>
                            <div class="col-6 col-sm-6">
                                <button type="button" class="btn btn-light border w-100 btn-touch-target" id="btn-reset">
                                    <i class="ti ti-rotate me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 1: Arus Kas Tunai (2x2 di Mobile) -->
        <div class="d-flex align-items-center justify-content-between mb-2 px-1">
            <span class="small fw-bold text-uppercase text-muted"><i class="ti ti-cash me-1 text-primary"></i> Arus Kas Tunai</span>
        </div>
        <div class="row g-2 g-md-3 mb-3">
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-secondary border-start border-3 metric-card-cash">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Saldo Awal Tunai</div>
                        <div class="fs-6 fw-bold mt-1 text-dark metric-value" id="saldo-awal-cash">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-success border-start border-3 metric-card-cash">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Masuk Tunai</div>
                        <div class="fs-6 fw-bold mt-1 text-success metric-value" id="pemasukan-cash">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-danger border-start border-3 metric-card-cash">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Keluar Tunai</div>
                        <div class="fs-6 fw-bold mt-1 text-danger metric-value" id="pengeluaran-cash">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-primary border-start border-3 metric-card-cash">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Saldo Akhir Tunai</div>
                        <div class="fs-6 fw-bold mt-1 text-primary metric-value" id="saldo-akhir-cash">Rp 0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 2: Arus Kas Non Tunai & Total (2x2 di Mobile) -->
        <div class="d-flex align-items-center justify-content-between mb-2 px-1">
            <span class="small fw-bold text-uppercase text-muted"><i class="ti ti-credit-card me-1 text-info"></i> Non-Tunai & Akumulasi</span>
        </div>
        <div class="row g-2 g-md-3 mb-3">
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-secondary border-start border-3 metric-card-cash">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Saldo Awal Non-Tunai</div>
                        <div class="fs-6 fw-bold mt-1 text-dark metric-value" id="saldo-awal-noncash">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-success border-start border-3 metric-card-cash">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Masuk Non-Tunai</div>
                        <div class="fs-6 fw-bold mt-1 text-success metric-value" id="pemasukan-noncash">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-danger border-start border-3 metric-card-cash">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Keluar Non-Tunai</div>
                        <div class="fs-6 fw-bold mt-1 text-danger metric-value" id="pengeluaran-noncash">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-dark border-start border-3 metric-card-cash">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Saldo Akhir Semua</div>
                        <div class="fs-6 fw-bold mt-1 text-dark metric-value" id="saldo-akhir-all">Rp 0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collapsible Summary Card Flow -->
        <div class="card mb-3">
            <div class="card-header bg-transparent p-3 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#collapseSummaryCash" aria-expanded="false" style="cursor: pointer;">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="ti ti-layout-grid me-1 text-primary"></i> Rincian & Akumulasi Arus Kas</span>
                    <span class="badge bg-primary-subtle text-primary"><i class="ti ti-chevron-down"></i> Buka/Tutup</span>
                </div>
            </div>
            <div class="collapse show" id="collapseSummaryCash">
                <div class="card-body p-3 p-md-4 pt-0">
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <tbody id="summary-body-left"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <tbody id="summary-body-right"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table / CardView Data Harian -->
        <div class="card mb-3">
            <div class="card-body p-2 p-md-3">
                <table id="table-data" class="table table-bordered table-hover table-striped table-sm align-middle w-100">
                    <thead>
                        <tr>
                            <th>TANGGAL</th>
                            <th>IN CASH</th>
                            <th>OUT CASH</th>
                            <th>SALDO CASH</th>
                            <th>IN NON TUNAI</th>
                            <th>OUT NON TUNAI</th>
                            <th>SALDO NON TUNAI</th>
                            <th>SALDO ALL</th>
                            <th>DETAIL</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Template CardView Mobile untuk Riwayat Kas Harian -->
<template id="card-cashflow-template">
    <div class="card cashflow-daily-card shadow-sm mb-2 border-start border-3 border-primary">
        <div class="card-body p-3">
            <!-- Header Kartu: Tanggal & Tombol Detail -->
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <div class="fw-bold fs-5 text-dark" data-dtcv-field="0"></div>
                <div data-dtcv-field="8"></div>
            </div>

            <!-- Box Tunai -->
            <div class="channel-box">
                <div class="small fw-bold text-primary mb-1"><i class="ti ti-cash me-1"></i> Jalur Tunai (Cash)</div>
                <div class="field-row">
                    <span class="text-muted">Masuk (In)</span>
                    <span class="text-success fw-medium">+<span data-dtcv-field="1"></span></span>
                </div>
                <div class="field-row">
                    <span class="text-muted">Keluar (Out)</span>
                    <span class="text-danger fw-medium">-<span data-dtcv-field="2"></span></span>
                </div>
                <div class="field-row">
                    <span class="text-muted">Saldo Tunai</span>
                    <span class="fw-bold text-primary" data-dtcv-field="3"></span>
                </div>
            </div>

            <!-- Box Non Tunai -->
            <div class="channel-box">
                <div class="small fw-bold text-info mb-1"><i class="ti ti-credit-card me-1"></i> Jalur Non Tunai (Bank/QRIS)</div>
                <div class="field-row">
                    <span class="text-muted">Masuk (In)</span>
                    <span class="text-success fw-medium">+<span data-dtcv-field="4"></span></span>
                </div>
                <div class="field-row">
                    <span class="text-muted">Keluar (Out)</span>
                    <span class="text-danger fw-medium">-<span data-dtcv-field="5"></span></span>
                </div>
                <div class="field-row">
                    <span class="text-muted">Saldo Non Tunai</span>
                    <span class="fw-bold text-info" data-dtcv-field="6"></span>
                </div>
            </div>

            <!-- Total Akumulasi -->
            <div class="field-row field-row-total">
                <span class="text-dark">Saldo Akumulasi (All)</span>
                <span class="fw-bold text-primary fs-5" data-dtcv-field="7"></span>
            </div>
        </div>
    </div>
</template>

<div class="modal fade" id="modal-detail-cashflow" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Cash Flow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <h5 class="fw-semibold mb-3 text-primary" id="detail-title">Periode -</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <tbody id="detail-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-touch-target" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const canMultiStore = akses_menu?.akses_delete === 'Y';
    const sessionTokoId = '<?= esc((string) session('toko_id')) ?>';
    let table = null;
    let currentRows = [];
    const detailModal = new bootstrap.Modal(document.getElementById('modal-detail-cashflow'));

    $(function() {
        if (canMultiStore) {
            $('#filter-toko-wrapper').show();
            $('#filter-toko').select2({
                width: '100%',
                placeholder: 'Pilih satu atau banyak toko'
            });
        }
        updateStoreInfo();
        initTable();
        loadReport();
    });

    $('#btn-filter').on('click', loadReport);
    $('#btn-reset').on('click', function() {
        $('#filter-periode').val(moment().format('YYYY-MM'));
        if (canMultiStore) {
            $('#filter-toko').val(null).trigger('change');
        }
        updateStoreInfo();
        loadReport();
    });
    $('#filter-toko').on('change', updateStoreInfo);

    function selectedStores() {
        if (!canMultiStore) {
            return [sessionTokoId];
        }
        return ($('#filter-toko').val() || []).filter(Boolean);
    }

    function updateStoreInfo() {
        const stores = selectedStores();
        if (!canMultiStore) {
            $('#selected-store-info').text(`Toko aktif: ${sessionTokoId}`);
            return;
        }
        $('#selected-store-info').text(stores.length ? `Toko dipilih: ${stores.join(', ')}` : 'Toko: semua toko');
    }

    function initTable() {
        DataTable.Buttons.defaults.dom.button.className = 'btn btn-primary';
        table = $('#table-data').DataTable({
            layout: {
                topStart: {
                    buttons: [{
                        extend: 'excelHtml5',
                        text: 'Excel',
                        title: 'Laporan-Cash-Flow'
                    }, {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        title: 'Laporan Cash Flow'
                    }, {
                        extend: 'print',
                        text: 'Print',
                        title: 'Laporan Cash Flow'
                    }, 'cardViewToggle', 'pageLength']
                }
            },
            cardView: {
                enable: true,
                breakpoint: 768,
                columns: {
                    xs: 1,
                    sm: 1,
                    md: 2,
                    lg: 3
                },
                template: '#card-cashflow-template',
                onCardRender: function($card, rowData) {
                    if (rowData.is_opening) {
                        $card.addClass('is-opening border-secondary border-dashed');
                        $card.find('.channel-box').addClass('bg-light');
                    }
                }
            },
            data: [],
            ordering: true,
            order: [[0, 'asc']],
            responsive: true,
            pageLength: 32,
            lengthMenu: [[32, 50, 100, -1], ['32 rows', '50 rows', '100 rows', 'Show all']],
            columns: [
                { data: 'tanggal' },
                { data: 'in_cash', className: 'text-end', render: moneyCell },
                { data: 'out_cash', className: 'text-end', render: moneyCell },
                { data: 'saldo_cash', className: 'text-end', render: moneyCell },
                { data: 'in_noncash', className: 'text-end', render: moneyCell },
                { data: 'out_noncash', className: 'text-end', render: moneyCell },
                { data: 'saldo_noncash', className: 'text-end', render: moneyCell },
                { data: 'saldo_all', className: 'text-end text-primary', render: moneyCell },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(row) {
                        if (row.is_opening) {
                            return '<span class="text-muted">-</span>';
                        }
                        return `<button type="button" class="btn btn-sm btn-outline-primary btn-touch-target" style="min-height:36px; padding:4px 10px;" onclick="showDailyDetail('${escapeAttr(row.tanggal || '')}')"><i class="ti ti-list-details me-1"></i> Rincian</button>`;
                    }
                }
            ],
            createdRow: function(row, data) {
                if (data.is_opening) {
                    $(row).addClass('table-light fw-semibold');
                }
            }
        });
    }

    function loadReport() {
        updateStoreInfo();
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lapcash/report') ?>',
            dataType: 'json',
            data: {
                periode: $('#filter-periode').val() + '-01',
                toko_ids: selectedStores()
            },
            success: function(res) {
                renderReport(res?.data || {});
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan cash flow'));
            }
        });
    }

    function renderReport(report) {
        const summary = report.summary || {};
        $('#period-label').text(`Periode: ${report.period_label || '-'}`);
        $('#saldo-awal-cash').text(rp(summary.saldo_awal_cash || 0));
        $('#pemasukan-cash').text(rp(summary.pemasukan_cash || 0));
        $('#pengeluaran-cash').text(rp(summary.pengeluaran_cash || 0));
        $('#saldo-akhir-cash').text(rp(summary.saldo_akhir_cash || 0));
        $('#saldo-awal-noncash').text(rp(summary.saldo_awal_noncash || 0));
        $('#pemasukan-noncash').text(rp(summary.pemasukan_noncash || 0));
        $('#pengeluaran-noncash').text(rp(summary.pengeluaran_noncash || 0));
        $('#saldo-akhir-all').text(rp(summary.saldo_akhir_all || 0));

        const breakdown = summary.breakdown || {};
        $('#summary-body-left').html([
            summaryRow('Saldo Awal Cash', summary.saldo_awal_cash, 'neutral'),
            summaryRow('Saldo Awal Non Tunai', summary.saldo_awal_noncash, 'neutral'),
            summaryRow('Total Pemasukan Cash', summary.pemasukan_cash, 'in'),
            summaryRow('Total Pemasukan Non Tunai', summary.pemasukan_noncash, 'in'),
            summaryRow('Total Pengeluaran Cash', summary.pengeluaran_cash, 'out'),
            summaryRow('Total Pengeluaran Non Tunai', summary.pengeluaran_noncash, 'out'),
            summaryRow('Sisa Saldo Cash', summary.saldo_akhir_cash, 'total'),
            summaryRow('Sisa Saldo Non Tunai', summary.saldo_akhir_noncash, 'total'),
            summaryRow('Sisa Saldo Akumulasi', summary.saldo_akhir_all, 'total')
        ].join(''));
        $('#summary-body-right').html(Object.keys(breakdown).map(label => summaryRow(label, breakdown[label], labelType(label))).join('') || '<tr><td class="text-center text-muted">Belum ada mutasi</td></tr>');

        currentRows = report.rows || [];
        table.clear().rows.add(currentRows).draw();
    }

    function summaryRow(label, amount, type) {
        const cls = type === 'in' ? 'text-success' : (type === 'out' ? 'text-danger' : (type === 'total' ? 'fw-semibold' : ''));
        return `<tr><td>${escapeHtml(label)}</td><td class="text-end ${cls}">${rp(amount || 0)}</td></tr>`;
    }

    function showDailyDetail(tanggal) {
        const row = currentRows.find(item => String(item.tanggal || '') === String(tanggal || ''));
        if (!row) {
            toastr.error('Detail tanggal tidak ditemukan');
            return;
        }

        $('#detail-title').text(`Periode ${row.tanggal || '-'}`);
        $('#detail-body').html((row.detail || []).map(item => summaryRow(item.label, item.amount, item.type || labelType(item.label))).join(''));
        detailModal.show();
    }

    function labelType(label) {
        return String(label || '').includes('Pengeluaran') ||
            String(label || '').includes('Pembayaran Supplier') ||
            String(label || '').includes('Mutasi Saldo Keluar') ? 'out' : 'in';
    }

    function moneyCell(data) {
        return formatMoneyValue(data || 0);
    }

    function rp(value) {
        return 'Rp ' + formatMoneyValue(value || 0);
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function escapeAttr(value) {
        return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }
</script>
<?= $this->endSection('javascript') ?>
