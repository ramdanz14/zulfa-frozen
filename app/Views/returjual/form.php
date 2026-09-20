<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$header = $formData['header'] ?? [];
$sale = $formData['sale'] ?? null;
$prefillError = $formData['error'] ?? null;
$mode = $mode ?? 'create';
?>
<style>
    /* =========================================================
       RETUR JUAL FORM (Mobile/Tablet Reflow & High-Visibility)
       ========================================================= */
    .retur-item-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .retur-item-card.is-active-retur {
        border-color: #ef4444 !important;
        background-color: #fffaf0 !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08);
    }

    .retur-item-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
        line-height: 1.3;
    }

    .retur-qty-input {
        font-size: 1.1rem !important;
        font-weight: 700 !important;
        height: 44px !important;
    }

    .retur-btn-quick {
        min-height: 38px;
        font-size: 0.78rem;
        padding: 0.25rem 0.5rem;
    }

    .refund-summary-card {
        background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%);
        border: 2px solid #fecaca;
        border-radius: 12px;
    }

    .refund-hero-amount {
        font-size: 1.85rem;
        font-weight: 900;
        color: #dc2626;
        letter-spacing: -0.03em;
        line-height: 1.1;
    }

    @media (max-width: 767.98px) {
        .btn-submit-retur {
            min-height: 50px;
            font-size: 1rem;
            font-weight: 700;
        }

        #btn-load-sale {
            min-height: 44px;
            min-width: 70px;
        }

        .refund-hero-amount {
            font-size: 1.6rem;
        }
    }
</style>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2"><?= $mode === 'edit' ? 'Edit Retur Penjualan' : 'Tambah Retur Penjualan' ?></h4>
                        <p class="mb-0 text-muted">Input retur dari no struk. Periksa kuantitas item dan pastikan nominal refund sesuai.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/returjual') ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i> Kembali ke History</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="returjual-form">
            <input type="hidden" id="rj_id" value="<?= esc($header['rj_id'] ?? '') ?>">

            <!-- Header Input Section -->
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-header py-2 bg-light border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-receipt me-1 text-primary"></i> Data Referensi Struk</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-semibold mb-1">ID Retur</label>
                            <input type="text" class="form-control font-monospace fw-bold bg-light" value="<?= esc($header['rj_id'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted text-uppercase fw-semibold mb-1">Tanggal Retur <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal" value="<?= esc($header['tanggal'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-semibold mb-1">No Struk / ID Jual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace fw-bold" id="jual_id" value="<?= esc($header['jual_id'] ?? '') ?>" placeholder="Contoh: JLTK012606070001" <?= $mode === 'edit' ? 'readonly' : '' ?> autofocus>
                                <?php if ($mode !== 'edit') : ?>
                                    <button type="button" class="btn btn-primary px-3 fw-semibold" id="btn-load-sale"><i class="ti ti-download me-1"></i> Load Struk</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted text-uppercase fw-semibold mb-1">Alasan / Keterangan Retur</label>
                            <input type="text" class="form-control" id="keterangan" value="<?= esc($header['keterangan'] ?? '') ?>" placeholder="Contoh: Barang rusak / kemasan bocor / salah beli">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Referensi Penjualan -->
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-header py-2 bg-light border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-info-circle me-1 text-info"></i> Informasi Transaksi Asal</h6>
                </div>
                <div class="card-body p-3" id="sale-summary"></div>
            </div>

            <!-- Content Area: Item List & Summary Side by Side -->
            <div class="row g-3">
                <!-- Left: List Item Belanja yang bisa diretur -->
                <div class="col-xl-7">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-box-seam me-1 text-danger"></i> Pilih Item & Tentukan Qty Retur</h6>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" id="active-item-badge">0 item dipilih</span>
                        </div>
                        <div class="card-body p-2 p-md-3">
                            <div id="detail-list" class="d-grid gap-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Right: Ringkasan Nilai Refund & Tombol Simpan -->
                <div class="col-xl-5">
                    <!-- Hero Refund Card -->
                    <div class="card refund-summary-card shadow-sm mb-3">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-danger text-white px-2 py-1 text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Nominal Refund Konsumen</span>
                                <i class="ti ti-cash text-danger fs-6"></i>
                            </div>
                            <div class="refund-hero-amount mb-3" id="sum-total">Rp 0</div>

                            <div class="border-top border-danger-subtle pt-2">
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Item Diretur:</span>
                                    <span class="fw-bold text-dark" id="sum-items">0 Jenis Item</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>Total Qty Fisik:</span>
                                    <span class="fw-bold text-dark" id="sum-qty">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="warning-box"></div>

                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-body p-3 d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-submit-retur py-2 py-md-3 fs-4" id="btn-save-retur">
                                <i class="ti ti-check me-1"></i> <?= $mode === 'edit' ? 'Update Retur Penjualan' : 'Konfirmasi & Simpan Retur' ?>
                            </button>
                            <a href="<?= base_url('/returjual') ?>" class="btn btn-light py-2">Batal & Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const mode = '<?= esc($mode) ?>';
    const prefillError = <?= json_encode($prefillError, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let saleData = <?= json_encode($sale, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let detailRows = hydrateDetails(saleData?.details || []);

    $(function() {
        if (prefillError) {
            toastr.error(prefillError);
        }
        renderSaleSummary();
        renderDetailList();
        recalcSummary();
    });

    $('#btn-load-sale').on('click', function() {
        loadSaleReference();
    });

    $('#jual_id').on('keypress', function(e) {
        if (e.which === 13 && mode !== 'edit') {
            e.preventDefault();
            loadSaleReference();
        }
    });

    $('#detail-list').on('input', '.row-qty-retur', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        const row = detailRows[idx];
        row.qty_retur = Number($(this).val() || 0);
        if (row.qty_retur - Number(row.qty_jual || 0) > 0.0001) {
            toastr.error(`Qty retur ${row.kode_item} tidak boleh melebihi qty jual`);
            row.qty_retur = Number(row.qty_jual || 0);
        }
        row.gross_retur = round2(Number(row.qty_retur || 0) * Number(row.refund_unit || 0));
        renderDetailList();
        recalcSummary();
    });

    $('#returjual-form').on('submit', function(e) {
        e.preventDefault();

        if (!saleData?.jual_id) {
            toastr.error('Load transaksi penjualan dulu sebelum menyimpan retur');
            return;
        }

        const payloadDetails = detailRows
            .filter(row => Number(row.qty_retur || 0) > 0)
            .map(row => ({
                seq_no: row.seq_no,
                qty_retur: Number(row.qty_retur || 0),
                refund_unit: Number(row.refund_unit || 0)
            }));

        if (payloadDetails.length === 0) {
            toastr.error('Minimal satu item harus memiliki qty retur lebih besar dari nol');
            return;
        }

        $.ajax({
            type: mode === 'edit' ? 'PATCH' : 'PUT',
            url: '<?= base_url('/returjual') ?>',
            dataType: 'json',
            data: {
                rj_id: $('#rj_id').val(),
                tanggal: $('#tanggal').val(),
                jual_id: $('#jual_id').val().trim(),
                keterangan: $('#keterangan').val().trim(),
                detail_json: JSON.stringify(payloadDetails)
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Retur penjualan berhasil disimpan');
                    window.location.href = '<?= base_url('/returjual') ?>';
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan retur penjualan');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan retur penjualan'));
            }
        });
    });

    function loadSaleReference() {
        const jualId = $('#jual_id').val().trim();
        if (!jualId) {
            toastr.error('No struk / jual_id wajib diisi');
            return;
        }

        $.getJSON(`<?= base_url('/returjual/sale') ?>/${encodeURIComponent(jualId)}`, function(res) {
            if (res.tipe !== 'success') {
                saleData = null;
                detailRows = [];
                renderSaleSummary();
                renderDetailList();
                recalcSummary();
                toastr.error(res.data || 'Transaksi penjualan tidak valid untuk retur');
                return;
            }

            saleData = res.data || null;
            detailRows = hydrateDetails(saleData?.details || []);
            renderSaleSummary();
            renderDetailList();
            recalcSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat referensi penjualan'));
        });
    }

    function renderSaleSummary() {
        const wrapper = $('#sale-summary');
        if (!saleData) {
            wrapper.html('<div class="text-muted p-2"><i class="ti ti-hand-point-up me-1"></i> Masukkan nomor struk di atas lalu tekan tombol <strong>Load Struk</strong> atau tekan <strong>Enter</strong>.</div>');
            return;
        }

        wrapper.html(`
            <div class="row g-2">
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="border rounded p-2 h-100 bg-light-subtle">
                        <small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">No Struk</small>
                        <div class="fw-bold font-monospace text-dark">${saleData.jual_id || '-'}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="border rounded p-2 h-100 bg-light-subtle">
                        <small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Tanggal Jual</small>
                        <div class="fw-semibold text-dark">${saleData.tgl ? new Date(saleData.tgl).toLocaleString('id-ID') : '-'}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="border rounded p-2 h-100 bg-light-subtle">
                        <small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Customer</small>
                        <div class="fw-semibold text-dark text-truncate">${saleData.customer_nama || 'Pelanggan Umum'}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="border rounded p-2 h-100 bg-light-subtle">
                        <small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Kasir</small>
                        <div class="fw-semibold text-dark">${saleData.updid || '-'}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="border rounded p-2 h-100 border-primary-subtle bg-primary-subtle">
                        <small class="text-primary d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Netto Jual</small>
                        <div class="fw-bold text-primary fs-4">Rp ${formatMoneyValue(saleData.netto || 0)}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-1">
                    <div class="border rounded p-2 h-100 bg-light-subtle">
                        <small class="text-muted d-block text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Umur</small>
                        <div class="fw-semibold text-dark">${Number(saleData.umur_hari || 0).toLocaleString('id-ID')} hari</div>
                    </div>
                </div>
            </div>
        `);
    }

    function renderDetailList() {
        const wrapper = $('#detail-list');
        wrapper.empty();

        if (!detailRows.length) {
            wrapper.html('<div class="text-center text-muted py-5"><i class="ti ti-package-off fs-8 d-block mb-2 text-muted"></i>Belum ada referensi penjualan yang dimuat.</div>');
            $('#active-item-badge').text('0 item dipilih');
            return;
        }

        let activeCount = 0;

        detailRows.forEach((row, idx) => {
            const isReturActive = Number(row.qty_retur || 0) > 0;
            if (isReturActive) activeCount++;

            wrapper.append(`
                <div class="retur-item-card ${isReturActive ? 'is-active-retur' : ''}" data-index="${idx}">
                    <!-- Baris 1: No urut, Identitas Produk & Status Retur -->
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                        <div style="flex: 1; min-width: 0;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border px-2 py-1">${idx + 1}</span>
                                <div class="retur-item-name text-truncate">${escapeHtml(row.nama_item || row.kode_item)}</div>
                            </div>
                            <div class="mt-1 d-flex flex-wrap align-items-center gap-2 text-muted small">
                                <span class="font-monospace">${escapeHtml(row.kode_item || '-')}</span>
                                <span class="badge bg-light text-secondary border px-2 py-1">${escapeHtml(row.sat_id || '-')}</span>
                                <span>Harga: <strong>Rp ${formatMoneyValue(row.price || row.refund_unit || 0)}</strong></span>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            ${isReturActive
                                ? '<span class="badge bg-danger text-white px-2 py-1"><i class="ti ti-check me-1"></i>DIRETUR</span>'
                                : '<span class="badge bg-light text-muted border px-2 py-1">Tidak Diretur</span>'}
                        </div>
                    </div>

                    <!-- Baris 2: Qty Jual vs Qty Retur Input & Quick Action Buttons -->
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-12 col-md-5">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Maksimal Beli:</span>
                                <span class="fw-bold text-dark fs-3">${Number(row.qty_jual || 0).toLocaleString('id-ID')} <small class="text-muted fw-normal">${escapeHtml(row.sat_id || '')}</small></span>
                            </div>
                        </div>
                        <div class="col-12 col-md-7">
                            <div class="d-flex gap-2 align-items-center">
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-danger fw-bold"><i class="ti ti-rotate-clockwise-2"></i></span>
                                    <input type="number" min="0" max="${Number(row.qty_jual || 0)}" step="0.01" class="form-control text-end row-qty-retur retur-qty-input ${isReturActive ? 'is-valid border-danger text-danger' : ''}" value="${row.qty_retur || 0}" placeholder="0">
                                </div>
                                <button type="button" class="btn btn-outline-danger retur-btn-quick btn-retur-all text-nowrap" title="Retur seluruh kuantitas">
                                    Semua (${Number(row.qty_jual || 0)})
                                </button>
                                <button type="button" class="btn btn-outline-secondary retur-btn-quick btn-retur-reset text-nowrap" title="Reset qty ke 0">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Baris 3: Informasi Kalkulasi Refund Per Item -->
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="text-muted small">
                            Refund per unit: <strong>Rp ${formatMoneyValue(row.refund_unit || 0)}</strong>
                        </span>
                        <div class="text-end">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Subtotal Refund Item</small>
                            <span class="fw-bold ${isReturActive ? 'text-danger fs-4' : 'text-muted fs-5'}">
                                Rp ${formatMoneyValue(row.gross_retur || 0)}
                            </span>
                        </div>
                    </div>
                </div>
            `);
        });

        $('#active-item-badge').text(`${activeCount} item dipilih`);
    }

    // Quick Retur All Button
    $('#detail-list').on('click', '.btn-retur-all', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        const row = detailRows[idx];
        row.qty_retur = Number(row.qty_jual || 0);
        row.gross_retur = round2(Number(row.qty_retur || 0) * Number(row.refund_unit || 0));
        renderDetailList();
        recalcSummary();
    });

    // Quick Retur Reset Button
    $('#detail-list').on('click', '.btn-retur-reset', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        const row = detailRows[idx];
        row.qty_retur = 0;
        row.gross_retur = 0;
        renderDetailList();
        recalcSummary();
    });

    $('#detail-list').on('input change', '.row-qty-retur', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        const row = detailRows[idx];
        let val = Number($(this).val() || 0);

        if (val < 0) {
            val = 0;
            $(this).val(0);
        }

        if (val - Number(row.qty_jual || 0) > 0.0001) {
            toastr.error(`Qty retur untuk ${row.nama_item || row.kode_item} tidak boleh melebihi ${row.qty_jual}`);
            val = Number(row.qty_jual || 0);
            $(this).val(val);
        }

        row.qty_retur = val;
        row.gross_retur = round2(Number(row.qty_retur || 0) * Number(row.refund_unit || 0));
        renderDetailList();
        recalcSummary();
    });

    function recalcSummary() {
        const totalItems = detailRows.filter(row => Number(row.qty_retur || 0) > 0).length;
        const totalQty = detailRows.reduce((sum, row) => sum + Number(row.qty_retur || 0), 0);
        const totalRetur = detailRows.reduce((sum, row) => sum + Number(row.gross_retur || 0), 0);

        $('#sum-items').text(`${totalItems.toLocaleString('id-ID')} Jenis Item`);
        $('#sum-qty').text(totalQty.toLocaleString('id-ID'));
        $('#sum-total').text('Rp ' + formatMoneyValue(totalRetur));
        $('#active-item-badge').text(`${totalItems} item dipilih`);
        renderWarning(totalRetur);
    }

    function renderWarning(totalRetur) {
        const wrapper = $('#warning-box');
        wrapper.empty();
        if (!saleData || totalRetur <= 0) {
            return;
        }

        wrapper.html(`
            <div class="alert alert-warning border-warning-subtle">
                Saat retur disimpan, sistem akan menambah stok kembali, membentuk <strong>kas keluar</strong> akun <strong>RETUR PENJUALAN</strong> sebesar <strong class="font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong>, dan transaksi ini tidak bisa diretur ulang.
            </div>
        `);
    }

    function hydrateDetails(rows) {
        return (rows || []).map(row => ({
            ...row,
            qty_jual: Number(row.qty_jual || 0),
            qty_retur: Number(row.qty_retur || 0),
            refund_unit: Number(row.refund_unit || row.price || 0),
            gross_retur: Number(row.gross_retur || 0)
        }));
    }

    function round2(value) {
        return Math.round((Number(value) || 0) * 100) / 100;
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