<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $tokoOptions
 */
$aksesMenuData = json_decode((string) ($akses_menu ?? '{}'), true) ?: [];
$canDeleteAkses = ($aksesMenuData['akses_delete'] ?? '') === 'Y';
?>
<style>
    .lapharian-metric-card {
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
    .metric-action-btn {
        min-height: 38px;
        min-width: 38px;
        padding: 6px 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 8px;
    }
    .lapharian-item-card {
        background: var(--bs-card-bg, #fff);
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .lapharian-item-card:last-child {
        margin-bottom: 0;
    }
    .lapharian-item-card .card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        font-size: 0.875rem;
    }
    .lapharian-item-card .card-row-total {
        border-top: 1px dashed rgba(0,0,0,0.12);
        margin-top: 6px;
        padding-top: 6px;
        font-weight: 600;
    }
    @media (max-width: 767.98px) {
        .metric-title {
            font-size: 0.78rem;
            line-height: 1.2;
        }
        .metric-value {
            font-size: 1.1rem !important;
            word-break: break-word;
        }
        .metric-subtitle {
            font-size: 0.72rem;
        }
        .table-desktop-view {
            display: none !important;
        }
        .cards-mobile-view {
            display: block !important;
        }
    }
    @media (min-width: 768px) {
        .table-desktop-view {
            display: block !important;
        }
        .cards-mobile-view {
            display: none !important;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-md-7">
                        <h4 class="fw-semibold mb-1">Laporan Harian Kasir</h4>
                        <p class="mb-0 text-muted small"><span id="report-subtitle">Pertanggungjawaban kasir akhir shift / akhir hari</span></p>
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
                        <label class="form-label small fw-medium mb-1">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="filter-tanggal" value="<?= date('Y-m-d') ?>">
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
                            <div class="col-4 col-sm-4">
                                <button type="button" class="btn btn-primary w-100 btn-touch-target" id="btn-view">
                                    <i class="ti ti-search me-1"></i> View
                                </button>
                            </div>
                            <div class="col-4 col-sm-4">
                                <button type="button" class="btn btn-success w-100 btn-touch-target" id="btn-print">
                                    <i class="ti ti-printer me-1"></i> Cetak
                                </button>
                            </div>
                            <div class="col-4 col-sm-4">
                                <button type="button" class="btn btn-info w-100 text-white btn-touch-target" id="btn-wa">
                                    <i class="ti ti-brand-whatsapp me-1"></i> Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Grid: 2x2 on Mobile, 4 columns on Desktop -->
        <div class="row g-2 g-md-3 mb-3">
            <!-- 1. Uang Harus Disetor -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-primary border-start border-3 lapharian-metric-card">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Uang Harus Disetor</div>
                        <div class="fs-6 fw-bold mt-1 text-primary metric-value" id="sum-setor">Rp 0</div>
                        <div class="text-muted metric-subtitle mt-1">Kas komputer</div>
                    </div>
                </div>
            </div>
            <!-- 2. Saldo Kas Toko -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-info border-start border-3 lapharian-metric-card">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Saldo Kas Toko</div>
                        <div class="fs-6 fw-bold mt-1 text-info metric-value" id="sum-saldo-toko">Rp 0</div>
                        <div class="mt-2">
                            <button class="btn btn-xs btn-outline-success metric-action-btn w-100" id="btn-deposit" title="Setor tunai toko ke pemilik">
                                <i class="ti ti-arrow-up-circle"></i> <span>Setor Pemilik</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 3. Saldo Kas Pemilik -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-dark border-start border-3 lapharian-metric-card">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Saldo Pemilik</div>
                        <div class="fs-6 fw-bold mt-1 text-dark metric-value" id="sum-saldo-pemilik">Rp 0</div>
                        <div class="mt-2">
                            <button class="btn btn-xs btn-outline-primary metric-action-btn w-100" id="btn-withdraw-profit" title="Tarik keuntungan pemilik" <?= ($canDeleteAkses ? '' : 'disabled') ?>>
                                <i class="ti ti-arrow-down-circle"></i> <span>Tarik Untung</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 4. Arus Kas Kecil -->
            <div class="col-6 col-md-6 col-xl-3">
                <div class="card h-100 mb-0 border-warning border-start border-3 lapharian-metric-card">
                    <div class="card-body p-3">
                        <div class="text-muted metric-title text-truncate">Arus Kas Kecil</div>
                        <div class="fs-6 fw-bold mt-1 text-warning metric-value" id="sum-kas">Rp 0</div>
                        <div class="text-muted metric-subtitle mt-1 text-truncate" id="sum-kas-detail">-</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Setoran Toko -> Pemilik (Jika ada) -->
        <div class="row g-3 mb-3" id="deposit-history-section" style="display:none;">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="fw-semibold mb-2 d-flex align-items-center">
                            <i class="ti ti-history me-1 text-success"></i> Setoran Hari Ini (Toko &rarr; Pemilik)
                        </div>
                        <!-- Desktop Table -->
                        <div class="table-responsive table-desktop-view">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Toko</th>
                                        <th class="text-end">Nominal</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="table-deposit-history"></tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="cards-mobile-view" id="cards-deposit-history"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Laporan Sections -->
        <div class="row g-3">
            <!-- Summary Per Toko -->
            <div class="col-12">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="fw-semibold mb-3 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-building-store me-1 text-primary"></i> Summary Per Toko</span>
                            <span class="badge bg-primary-subtle text-primary small d-md-none">Reflow Card</span>
                        </div>
                        <!-- Desktop Table -->
                        <div class="table-responsive table-desktop-view">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Toko</th>
                                        <th class="text-end">POS Tunai</th>
                                        <th class="text-end">POS Non Tunai</th>
                                        <th class="text-end">Kas Bersih</th>
                                        <th class="text-end">Supplier Tunai</th>
                                        <th class="text-end">Piutang Tunai</th>
                                        <th class="text-end">Uang Harus Disetor</th>
                                    </tr>
                                </thead>
                                <tbody id="table-store-summary"></tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="cards-mobile-view" id="cards-store-summary"></div>
                    </div>
                </div>
            </div>

            <!-- Pertanggungjawaban Per Kasir -->
            <div class="col-12">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="fw-semibold mb-3 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-user-check me-1 text-info"></i> Pertanggungjawaban Per Kasir</span>
                            <span class="badge bg-info-subtle text-info small d-md-none">Reflow Card</span>
                        </div>
                        <!-- Desktop Table -->
                        <div class="table-responsive table-desktop-view">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Toko</th>
                                        <th>Kasir</th>
                                        <th class="text-end">Trx POS</th>
                                        <th class="text-end">POS Tunai</th>
                                        <th class="text-end">POS Non Tunai</th>
                                        <th class="text-end">Kas Bersih</th>
                                        <th class="text-end">Supplier Tunai</th>
                                        <th class="text-end">Piutang Tunai</th>
                                        <th class="text-end">Uang Harus Disetor</th>
                                    </tr>
                                </thead>
                                <tbody id="table-cashier-summary"></tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="cards-mobile-view" id="cards-cashier-summary"></div>
                    </div>
                </div>
            </div>

            <!-- Pendapatan POS per Metode Bayar -->
            <div class="col-12 col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="fw-semibold mb-3"><i class="ti ti-credit-card me-1 text-success"></i> Pendapatan POS per Metode Bayar</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <tbody id="table-pos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Arus Kas Kecil per Akun -->
            <div class="col-12 col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="fw-semibold mb-3"><i class="ti ti-wallet me-1 text-warning"></i> Arus Kas Kecil per Akun</div>
                        <!-- Desktop Table -->
                        <div class="table-responsive table-desktop-view">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Akun</th>
                                        <th>Jenis</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody id="table-kas"></tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="cards-mobile-view" id="cards-kas"></div>
                    </div>
                </div>
            </div>

            <!-- Rekap Pembayaran Hutang ke Supplier -->
            <div class="col-12 col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="fw-semibold mb-3"><i class="ti ti-truck me-1 text-danger"></i> Rekap Bayar Hutang ke Supplier</div>
                        <!-- Desktop Table -->
                        <div class="table-responsive table-desktop-view">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Metode</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody id="table-supplier"></tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="cards-mobile-view" id="cards-supplier"></div>
                    </div>
                </div>
            </div>

            <!-- Rekap Pembayaran Piutang dari Customer -->
            <div class="col-12 col-lg-6">
                <div class="card h-100 mb-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="fw-semibold mb-3"><i class="ti ti-cash me-1 text-primary"></i> Rekap Terima Piutang Customer</div>
                        <!-- Desktop Table -->
                        <div class="table-responsive table-desktop-view">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Customer</th>
                                        <th>Metode</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody id="table-customer"></tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="cards-mobile-view" id="cards-customer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-deposit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setor Tunai Toko &rarr; Pemilik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nominal</label>
                    <input type="text" class="form-control money" id="deposit-nominal" placeholder="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" class="form-control" id="deposit-keterangan" value="Setoran tunai dari toko ke pemilik">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success" id="btn-deposit-save">Simpan Setoran</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-withdraw-profit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tarik Keuntungan Pemilik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nominal</label>
                    <input type="text" class="form-control money" id="withdraw-nominal" placeholder="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Channel</label>
                    <select class="form-select" id="withdraw-channel">
                        <option value="CASH">Tunai</option>
                        <option value="NONCASH">Non Tunai</option>
                    </select>
                </div>
                <div class="mb-3" id="withdraw-target-wrapper">
                    <label class="form-label">Saldo Tunai Tujuan</label>
                    <select class="form-select" id="withdraw-target">
                        <option value="TOKO">Saldo Toko</option>
                        <option value="PEMILIK">Saldo Pemilik</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" class="form-control" id="withdraw-keterangan" value="Penarikan keuntungan  pemilik">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary" id="btn-withdraw-save">Simpan Penarikan</button>
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
    let currentReport = null;

    $(function() {
        if (canMultiStore) {
            $('#filter-toko-wrapper').show();
            $('#filter-toko').select2({
                width: '100%',
                placeholder: 'Pilih satu atau banyak toko'
            });
        }
        updateStoreInfo();
        loadReport();
    });

    $('#btn-view').on('click', loadReport);
    $('#btn-print').on('click', function() {
        window.open(buildPrintUrl(), '_blank');
    });
    $('#btn-wa').on('click', function() {
        if (!currentReport) {
            return;
        }
        window.open('https://wa.me/?text=' + encodeURIComponent(buildWhatsappText(currentReport)), '_blank');
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

    function loadReport() {
        updateStoreInfo();
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/lapharian/report') ?>',
            dataType: 'json',
            data: {
                tanggal: $('#filter-tanggal').val(),
                toko_ids: selectedStores()
            },
            success: function(res) {
                currentReport = res?.data || {};
                renderReport(currentReport);
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memuat laporan harian'));
            }
        });
    }

    function renderReport(report) {
        const pos = report.pos || {};
        const diskon = report.discount || {};
        const kas = report.kas || {};
        $('#report-subtitle').text(`${formatDate(report.tanggal)} | Dicetak ${report.printed_at || '-'}`);
        $('#sum-pos').text(rp(pos.total || 0));
        $('#sum-pos-detail').text(`Tunai ${rp(pos.tunai || 0)} + Transfer ${rp(pos.transfer || 0)} + QRIS ${rp(pos.qris || 0)}`);
        $('#sum-diskon').text(rp(diskon.item || 0));
        $('#sum-diskon-detail').text(`Nota ${rp(diskon.nota || 0)} | Poin ${rp(diskon.redeem || 0)}`);
        $('#sum-kas').text(rp(kas.bersih || 0));
        $('#sum-kas-detail').text(`Masuk ${rp(kas.masuk || 0)} - Keluar ${rp(kas.keluar || 0)}`);
        $('#sum-setor').text(rp(report.uang_harus_disetor || 0));
        const cb = report.cash_balances || {};
        $('#sum-saldo-toko').text(rp(cb.saldo_toko || 0));
        $('#sum-saldo-pemilik').text(rp(cb.saldo_pemilik || 0));
        
        // Render Store Summary (Desktop & Mobile)
        $('#table-store-summary').html(summaryRows(report.store_summaries || [], false));
        $('#cards-store-summary').html(summaryCards(report.store_summaries || [], false));

        // Render Cashier Summary (Desktop & Mobile)
        $('#table-cashier-summary').html(summaryRows(report.cashier_groups || [], true));
        $('#cards-cashier-summary').html(summaryCards(report.cashier_groups || [], true));

        const depRows = report.deposit_history || [];
        if (depRows.length) {
            $('#deposit-history-section').show();
            $('#table-deposit-history').html(depRows.map(row =>
                `<tr><td>${esc(row.tanggal || '-')}</td><td>${esc(row.toko_nama || row.toko_id)}</td><td class="text-end">${rp(row.nominal || 0)}</td><td>${esc(row.keterangan || '-')}</td></tr>`
            ).join(''));
            $('#cards-deposit-history').html(depRows.map(row =>
                `<div class="lapharian-item-card">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-success-subtle text-success">${esc(row.tanggal || '-')}</span>
                        <span class="fw-bold text-success">${rp(row.nominal || 0)}</span>
                    </div>
                    <div class="card-row">
                        <span class="text-muted">Toko</span>
                        <span class="fw-medium">${esc(row.toko_nama || row.toko_id)}</span>
                    </div>
                    <div class="card-row">
                        <span class="text-muted">Keterangan</span>
                        <span class="text-end small">${esc(row.keterangan || '-')}</span>
                    </div>
                </div>`
            ).join(''));
        } else {
            $('#deposit-history-section').hide();
        }

        $('#table-pos').html([
            rowHtml('Tunai', pos.tunai || 0),
            rowHtml('Transfer', pos.transfer || 0),
            rowHtml('QRIS', pos.qris || 0),
            rowHtml('Total POS', pos.total || 0, true)
        ].join(''));

        // Render Kas Kecil
        const kasRows = kas.rows || [];
        $('#table-kas').html(tableRows(kasRows, row => `<tr><td>${esc(row.nama_akun)}</td><td>${esc(row.jenis_akun)}</td><td class="text-end">${rp(row.total || 0)}</td></tr>`));
        $('#cards-kas').html(cardList(kasRows, row => {
            const isKeluar = String(row.jenis_akun || '').toLowerCase().includes('keluar') || String(row.jenis_akun || '').toLowerCase().includes('beban');
            const badgeClass = isKeluar ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success';
            return `<div class="lapharian-item-card">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">${esc(row.nama_akun)}</span>
                    <span class="badge ${badgeClass}">${esc(row.jenis_akun)}</span>
                </div>
                <div class="card-row">
                    <span class="text-muted">Nominal</span>
                    <span class="fw-bold text-end">${rp(row.total || 0)}</span>
                </div>
            </div>`;
        }));

        // Render Supplier
        const supRows = report.supplier?.rows || [];
        $('#table-supplier').html(tableRows(supRows, row => `<tr><td>${esc(row.nama_supplier)}</td><td>${esc(row.cara_bayar)}</td><td class="text-end">${rp(row.total || 0)}</td></tr>`));
        $('#cards-supplier').html(cardList(supRows, row =>
            `<div class="lapharian-item-card">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">${esc(row.nama_supplier)}</span>
                    <span class="badge bg-secondary-subtle text-secondary">${esc(row.cara_bayar)}</span>
                </div>
                <div class="card-row">
                    <span class="text-muted">Nominal</span>
                    <span class="fw-bold text-danger text-end">${rp(row.total || 0)}</span>
                </div>
            </div>`
        ));

        // Render Customer
        const custRows = report.customer?.rows || [];
        $('#table-customer').html(tableRows(custRows, row => `<tr><td>${esc(row.nama_customer)}</td><td>${esc(row.cara_bayar)}</td><td class="text-end">${rp(row.total || 0)}</td></tr>`));
        $('#cards-customer').html(cardList(custRows, row =>
            `<div class="lapharian-item-card">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">${esc(row.nama_customer)}</span>
                    <span class="badge bg-secondary-subtle text-secondary">${esc(row.cara_bayar)}</span>
                </div>
                <div class="card-row">
                    <span class="text-muted">Nominal</span>
                    <span class="fw-bold text-primary text-end">${rp(row.total || 0)}</span>
                </div>
            </div>`
        ));
    }

    function rowHtml(label, amount, strong = false) {
        const left = strong ? `<strong>${label}</strong>` : label;
        const right = strong ? `<strong>${rp(amount)}</strong>` : rp(amount);
        return `<tr><td>${left}</td><td class="text-end">${right}</td></tr>`;
    }

    function tableRows(rows, renderer) {
        if (!rows.length) {
            return '<tr><td colspan="3" class="text-center text-muted">Tidak ada data</td></tr>';
        }
        return rows.map(renderer).join('');
    }

    function cardList(rows, renderer) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-3 small">Tidak ada data</div>';
        }
        return rows.map(renderer).join('');
    }

    function summaryRows(rows, withCashier) {
        if (!rows.length) {
            return `<tr><td colspan="${withCashier ? 9 : 7}" class="text-center text-muted">Tidak ada data</td></tr>`;
        }
        return rows.map(row => {
            const nonCash = Number(row.pos_transfer || 0) + Number(row.pos_qris || 0);
            if (withCashier) {
                return `<tr>
                    <td>${esc(row.toko_nama || row.toko_id)}</td>
                    <td>${esc(row.nama_kasir || row.kasir)}<br><small class="text-muted">${esc(row.kasir || '-')}</small></td>
                    <td class="text-end">${Number(row.total_transaksi || 0).toLocaleString('id-ID')}</td>
                    <td class="text-end">${rp(row.pos_tunai || 0)}</td>
                    <td class="text-end">${rp(nonCash)}</td>
                    <td class="text-end">${rp(row.kas_bersih || 0)}</td>
                    <td class="text-end">${rp(row.supplier_tunai || 0)}</td>
                    <td class="text-end">${rp(row.customer_tunai || 0)}</td>
                    <td class="text-end fw-semibold text-primary">${rp(row.uang_harus_disetor || 0)}</td>
                </tr>`;
            }
            return `<tr>
                <td>${esc(row.toko_nama || row.toko_id)}</td>
                <td class="text-end">${rp(row.pos_tunai || 0)}</td>
                <td class="text-end">${rp(nonCash)}</td>
                <td class="text-end">${rp(row.kas_bersih || 0)}</td>
                <td class="text-end">${rp(row.supplier_tunai || 0)}</td>
                <td class="text-end">${rp(row.customer_tunai || 0)}</td>
                <td class="text-end fw-semibold text-primary">${rp(row.uang_harus_disetor || 0)}</td>
            </tr>`;
        }).join('');
    }

    function summaryCards(rows, withCashier) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-3 small">Tidak ada data</div>';
        }
        return rows.map(row => {
            const nonCash = Number(row.pos_transfer || 0) + Number(row.pos_qris || 0);
            return `
            <div class="lapharian-item-card border-start border-primary border-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold">${esc(row.toko_nama || row.toko_id)}</div>
                        ${withCashier ? `<div class="small text-muted"><i class="ti ti-user"></i> ${esc(row.nama_kasir || row.kasir)} (${esc(row.kasir || '-')})</div>` : ''}
                    </div>
                    ${withCashier ? `<span class="badge bg-light text-dark">${Number(row.total_transaksi || 0).toLocaleString('id-ID')} Trx</span>` : ''}
                </div>
                <div class="card-row">
                    <span class="text-muted">POS Tunai</span>
                    <span class="fw-medium">${rp(row.pos_tunai || 0)}</span>
                </div>
                <div class="card-row">
                    <span class="text-muted">POS Non Tunai</span>
                    <span class="fw-medium">${rp(nonCash)}</span>
                </div>
                <div class="card-row">
                    <span class="text-muted">Kas Kecil Bersih</span>
                    <span class="fw-medium ${Number(row.kas_bersih || 0) < 0 ? 'text-danger' : ''}">${rp(row.kas_bersih || 0)}</span>
                </div>
                <div class="card-row">
                    <span class="text-muted">Supplier Tunai</span>
                    <span class="fw-medium text-danger">-${rp(row.supplier_tunai || 0)}</span>
                </div>
                <div class="card-row">
                    <span class="text-muted">Piutang Tunai</span>
                    <span class="fw-medium text-success">+${rp(row.customer_tunai || 0)}</span>
                </div>
                <div class="card-row card-row-total">
                    <span>Uang Harus Disetor</span>
                    <span class="text-primary fs-5 fw-bold">${rp(row.uang_harus_disetor || 0)}</span>
                </div>
            </div>`;
        }).join('');
    }

    function buildPrintUrl() {
        const params = new URLSearchParams();
        params.set('tanggal', $('#filter-tanggal').val());
        selectedStores().forEach(store => params.append('toko_ids[]', store));
        return `<?= base_url('/lapharian/struk') ?>?${params.toString()}`;
    }

    function buildWhatsappText(report) {
        const pos = report.pos || {};
        const kas = report.kas || {};
        const supplier = report.supplier || {};
        const customer = report.customer || {};
        return [
            'LAPORAN HARIAN KASIR',
            `Tanggal: ${formatDate(report.tanggal)}`,
            `Dicetak: ${report.printed_at || '-'}`,
            `Toko: ${(report.stores || []).map(row => row.toko_nama || row.toko_id).join(', ') || '-'}`,
            '',
            'SUMMARY PER TOKO',
            ...(report.store_summaries || []).map(row => `${row.toko_nama || row.toko_id}: ${rp(row.uang_harus_disetor || 0)} (Tunai POS ${rp(row.pos_tunai || 0)}, Kas ${rp(row.kas_bersih || 0)})`),
            '',
            'PERTANGGUNGJAWABAN PER KASIR',
            ...(report.cashier_groups || []).map(row => `${row.toko_nama || row.toko_id} - ${row.nama_kasir || row.kasir}: ${rp(row.uang_harus_disetor || 0)} (Trx ${Number(row.total_transaksi || 0).toLocaleString('id-ID')}, POS Tunai ${rp(row.pos_tunai || 0)})`),
            '',
            `POS Tunai: ${rp(pos.tunai || 0)}`,
            `POS Transfer: ${rp(pos.transfer || 0)}`,
            `POS QRIS: ${rp(pos.qris || 0)}`,
            `Total POS: ${rp(pos.total || 0)}`,
            `Diskon Item: ${rp(report.discount?.item || 0)}`,
            `Kas Masuk: ${rp(kas.masuk || 0)}`,
            `Kas Keluar: ${rp(kas.keluar || 0)}`,
            `Bayar Supplier Tunai: ${rp(supplier.tunai || 0)}`,
            `Terima Piutang Tunai: ${rp(customer.tunai || 0)}`,
            '',
            `UANG HARUS DISETOR: ${rp(report.uang_harus_disetor || 0)}`
        ].join('\n');
    }

    function rp(value) {
        return 'Rp ' + formatMoneyValue(value || 0);
    }

    function formatDate(value) {
        return value ? moment(value, 'YYYY-MM-DD').format('DD/MM/YYYY') : '-';
    }

    function esc(value) {
        return $('<div>').text(value || '-').html();
    }

    const modalDeposit = new bootstrap.Modal(document.getElementById('modal-deposit'));
    const modalWithdrawProfit = new bootstrap.Modal(document.getElementById('modal-withdraw-profit'));
    const today = () => $('#filter-tanggal').val() || new Date().toISOString().slice(0, 10);
    $('#withdraw-channel').on('change', function() {
        $('#withdraw-target-wrapper').toggle($(this).val() === 'CASH');
    });

    $('#btn-deposit').on('click', function() {
        $('#deposit-nominal').val('');
        $('#deposit-keterangan').val('Setoran tunai dari toko ke pemilik');
        modalDeposit.show();
    });
    $('#btn-deposit-save').on('click', function() {
        const nominal = parseFloat($('#deposit-nominal').val()) || 0;
        if (nominal <= 0) {
            toastr.error('Nominal harus lebih dari 0');
            return;
        }
        $.post('<?= base_url('/lapharian/deposit') ?>', {
            nominal: nominal,
            tanggal: today(),
            keterangan: $('#deposit-keterangan').val(),
            toko_id: '<?= session('toko_id') ?>'
        }, function(res) {
            if (res.tipe === 'success') {
                toastr.success(res.data || 'Setoran berhasil');
                modalDeposit.hide();
                loadReport();
                return;
            }
            toastr.error(res.data || 'Gagal menyimpan');
        }, 'json').fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan setoran'));
        });
    });

    $('#btn-withdraw-profit').on('click', function() {
        $('#withdraw-nominal').val('');
        $('#withdraw-keterangan').val('Penarikan keuntungan dari pemilik');
        modalWithdrawProfit.show();
    });
    $('#btn-withdraw-save').on('click', function() {
        const nominal = parseFloat($('#withdraw-nominal').val()) || 0;
        if (nominal <= 0) {
            toastr.error('Nominal harus lebih dari 0');
            return;
        }
        $.post('<?= base_url('/lapharian/withdraw-profit') ?>', {
            nominal: nominal,
            channel: $('#withdraw-channel').val(),
            target: $('#withdraw-target').val(),
            tanggal: today(),
            keterangan: $('#withdraw-keterangan').val(),
            toko_id: '<?= session('toko_id') ?>'
        }, function(res) {
            if (res.tipe === 'success') {
                toastr.success(res.data || 'Penarikan berhasil');
                modalWithdrawProfit.hide();
                loadReport();
                return;
            }
            toastr.error(res.data || 'Gagal menyimpan');
        }, 'json').fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan penarikan'));
        });
    });
</script>
<?= $this->endSection('javascript') ?>