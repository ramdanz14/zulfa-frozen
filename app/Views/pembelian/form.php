<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 * @var array $supplierOptions
 */
?>

<style>
    /* =========================================================
   PEMBELIAN WORKSPACE STYLING (Optimized for Mobile & Tablet)
   ========================================================= */
    .pembelian-container {
        padding-bottom: 90px;
        /* Space for sticky bottom bar on mobile */
    }

    /* Compact Collapsible Header */
    .purchase-header-card {
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        transition: all 0.2s ease;
    }

    .purchase-header-summary {
        cursor: pointer;
        user-select: none;
        padding: 0.75rem 1rem;
        border-radius: 12px;
    }

    .purchase-header-summary:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.04);
    }

    .header-tag-pill {
        font-size: 0.75rem;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        background: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    /* Item Workspace (Dominant Area) */
    .item-workspace-card {
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .item-search-wrapper .select2-container--default .select2-selection--single {
        height: 46px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        border: 2px solid #cbd5e1;
        transition: border-color 0.2s;
    }

    .item-search-wrapper .select2-container--default .select2-selection--single:focus,
    .item-search-wrapper .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--bs-primary);
    }

    .item-search-wrapper .select2-selection__rendered {
        font-size: 0.95rem;
        font-weight: 500;
        padding-left: 12px !important;
    }

    /* Detail Item Cards */
    .detail-item-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
        position: relative;
        padding: 0.85rem;
    }

    .detail-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .detail-item-card .item-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.3;
    }

    .detail-item-card .item-code {
        font-size: 0.75rem;
        color: #64748b;
        font-family: var(--bs-font-monospace);
        background: #f8fafc;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
    }

    .detail-item-card .row-stock-hint {
        font-size: 0.75rem;
        color: #059669;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .detail-input-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }

    @media (min-width: 768px) {
        .detail-input-grid {
            grid-template-columns: 140px 100px 1fr 1fr;
        }
    }

    .input-label-compact {
        font-size: 0.72rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 0.2rem;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .form-control-touch {
        height: 40px;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 6px;
    }

    /* Delete item button */
    .btn-delete-item {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: #dc3545;
        background: #fff5f5;
        border: 1px solid #fed7d7;
        transition: all 0.15s;
    }

    .btn-delete-item:hover,
    .btn-delete-item:active {
        background: #dc3545;
        color: #ffffff;
        border-color: #dc3545;
    }

    /* Mobile Sticky Action Bar */
    .mobile-sticky-action-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
        z-index: 1040;
    }

    /* Payment Card Row Styling (Card-based, No Horizontal Scroll) */
    .pay-card-item {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 0.85rem;
        position: relative;
        transition: all 0.2s ease;
    }
    .pay-card-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 3px 10px rgba(0,0,0,0.03);
    }
    .pay-card-item.is-transfer {
        border-left: 4px solid #0d6efd;
    }
    .pay-card-item.is-tunai {
        border-left: 4px solid #198754;
    }
    .btn-delete-pay {
        width: 36px;
        height: 36px;
        min-width: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: #dc3545;
        background: #fff5f5;
        border: 1px solid #fed7d7;
        transition: all 0.15s;
    }
    .btn-delete-pay:hover, .btn-delete-pay:active {
        background: #dc3545;
        color: #ffffff;
        border-color: #dc3545;
    }
    .existing-pay-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 0.75rem 0.85rem;
    }

    /* Desktop summary sidebar sticky */
    @media (min-width: 1200px) {
        .sticky-summary {
            position: sticky;
            top: 80px;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0 pembelian-container">
        <!-- Breadcrumb / Header Banner -->
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                            <i class="ti ti-shopping-cart fs-6 text-white"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark"><?= $mode === 'create' ? 'Tambah' : 'Edit' ?> Pembelian</h5>
                            <small class="text-muted d-none d-sm-inline">Draft PO, penerimaan barang & pembayaran supplier</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= base_url('/hutang') ?>" class="btn btn-outline-danger btn-sm d-none d-md-inline-flex align-items-center gap-1">
                            <i class="ti ti-receipt-2"></i> Monitoring Hutang
                        </a>
                        <a href="<?= base_url('/pembelian') ?>" class="btn btn-secondary btn-sm d-inline-flex align-items-center gap-1">
                            <i class="ti ti-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="pembelian-form">
            <input type="hidden" name="_method" value="<?= $mode === 'create' ? 'PUT' : 'PATCH' ?>">
            <input type="hidden" name="detail_json" id="detail_json">
            <input type="hidden" name="payment_json" id="payment_json">

            <div class="row g-3">
                <!-- MAIN WORKSPACE: Item Area & Compact Header -->
                <div class="col-xl-8">

                    <!-- COMPACT INFO PEMBELIAN (Collapsible on Mobile, streamlined on Tablet/Desktop) -->
                    <div class="card purchase-header-card mb-3">
                        <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapseHeaderInfo" aria-expanded="true">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold text-dark fs-3"><i class="ti ti-file-text text-primary me-1"></i> Data Transaksi & Supplier</span>
                                <span class="header-tag-pill" id="header-chip-sup">
                                    <i class="ti ti-building-store"></i> <span id="chip-sup-text">Pilih Supplier</span>
                                </span>
                                <span class="header-tag-pill" id="header-chip-inv">
                                    <i class="ti ti-barcode"></i> <span id="chip-inv-text">-</span>
                                </span>
                                <span class="badge bg-light-primary text-primary" id="chip-status-text">PO / Draft</span>
                            </div>
                            <div class="text-muted small d-flex align-items-center gap-1">
                                <span class="d-none d-sm-inline">Ubah Info</span>
                                <i class="ti ti-chevron-down fs-4 transition-all" id="header-collapse-icon"></i>
                            </div>
                        </div>
                        <div class="collapse show" id="collapseHeaderInfo">
                            <div class="card-body p-3 bg-light-subtle border-top">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3">
                                        <label class="input-label-compact">ID Pembelian</label>
                                        <input type="text" class="form-control form-control-sm font-monospace bg-white" name="beli_id" id="beli_id" readonly value="<?= esc($formData['header']['beli_id'] ?? '') ?>" placeholder="(Auto)">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="input-label-compact">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm bg-white" name="tanggal" id="tanggal" value="<?= esc($formData['header']['tanggal'] ?? date('Y-m-d')) ?>" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="input-label-compact">Supplier <span class="text-danger">*</span></label>
                                        <select class="form-select select2" name="supco" id="supco" required>
                                            <option value="">Pilih Supplier</option>
                                            <?php foreach ($supplierOptions as $row) : ?>
                                                <option value="<?= esc($row['supco']) ?>" <?= ($formData['header']['supco'] ?? '') === $row['supco'] ? 'selected' : '' ?>>
                                                    <?= esc($row['supco']) ?> - <?= esc($row['nama']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="input-label-compact">Invoice Supplier <span class="text-danger">*</span></label>
                                            <small class="text-muted" style="font-size: 0.68rem;">Auto format: <span class="font-monospace">supco-YYMMDD</span></small>
                                        </div>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="ti ti-file-invoice text-muted"></i></span>
                                            <input type="text" class="form-control form-control-sm bg-white font-monospace" name="invoice" id="invoice" required value="<?= esc($formData['header']['invoice'] ?? '') ?>" placeholder="SUP01-260917">
                                            <button class="btn btn-outline-secondary" type="button" id="btn-re-generate-inv" title="Generate ulang kode invoice otomatis"><i class="ti ti-refresh"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="input-label-compact">Status Nota <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm bg-white fw-semibold" name="status_nota" id="status_nota">
                                            <option value="PO" <?= ($formData['header']['status_nota'] ?? 'PO') === 'PO' ? 'selected' : '' ?>>PO (Draft Pesanan)</option>
                                            <option value="TERIMA" <?= ($formData['header']['status_nota'] ?? '') === 'TERIMA' ? 'selected' : '' ?>>TERIMA (Barang Masuk)</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <label class="input-label-compact">Keterangan / Catatan</label>
                                        <input type="text" class="form-control form-control-sm bg-white" name="keterangan" id="keterangan" value="<?= esc($formData['header']['keterangan'] ?? '') ?>" placeholder="Catatan pengiriman / PO">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRIMARY ITEM WORKSPACE (Dominant Focus Area) -->
                    <div class="card item-workspace-card">
                        <div class="card-header bg-white py-3 px-3 border-bottom">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-5">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary-subtle text-primary p-2 rounded-3"><i class="ti ti-package fs-5"></i></span>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark">Daftar Item Pembelian</h6>
                                            <small class="text-muted" id="item-count-badge">0 item dipilih</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-7">
                                    <div class="item-search-wrapper position-relative">
                                        <select class="form-select" id="item-search" style="width: 100%;"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-2 p-md-3">
                            <!-- Items List Container -->
                            <div id="detail-list" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR / SUMMARY COLUMN -->
                <div class="col-xl-4">
                    <div class="sticky-summary d-flex flex-column gap-3">
                        <!-- Ringkasan Nilai -->
                        <div class="card shadow-sm border-0" id="summary-card" style="border-radius: 12px; background: #ffffff;">
                            <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold mb-0 text-dark"><i class="ti ti-calculator text-primary me-1"></i> Ringkasan Pembelian</h6>
                                <span class="badge bg-success-subtle text-success fs-2" id="summary-nota-badge">PO / DRAFT</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light">
                                    <span class="text-muted small">Total Jenis Item</span>
                                    <span class="fw-bold" id="summary-item">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light">
                                    <span class="text-muted small">Total Kuantitas (Qty)</span>
                                    <span class="fw-bold" id="summary-qty">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded-3">
                                    <span class="fw-semibold text-dark">Total Gross / Tagihan</span>
                                    <span class="fw-bolder fs-5 text-primary" id="summary-gross">Rp 0</span>
                                </div>

                                <div id="summary-payment-section" class="border-top pt-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">Pembayaran Tersimpan</span>
                                        <span class="fw-semibold text-secondary" id="summary-existing-paid">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">Pembayaran Baru (Form)</span>
                                        <span class="fw-semibold text-success" id="summary-form-paid">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-danger-subtle">
                                        <span class="fw-semibold text-danger small">Sisa Bayar (Hutang)</span>
                                        <span class="fw-bolder fs-4 text-danger font-monospace" id="summary-sisa">Rp 0</span>
                                    </div>
                                </div>

                                <!-- Desktop Action Button -->
                                <div class="d-none d-xl-grid gap-2 mt-3 pt-2 border-top">
                                    <button type="submit" class="btn btn-success btn-lg fw-bold d-flex align-items-center justify-content-center gap-2" id="btn-save">
                                        <i class="ti ti-device-floppy fs-5"></i> Simpan Pembelian
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Histori Pembayaran Tersimpan Card (if any) -->
                        <?php if (!empty($formData['payments'])) : ?>
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-header bg-white py-2 px-3 border-bottom">
                                    <h6 class="fw-bold mb-0 text-dark small"><i class="ti ti-history text-secondary me-1"></i> Histori Pembayaran</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.8rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tgl</th>
                                                    <th>Metode</th>
                                                    <th>Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($formData['payments'] as $row) : ?>
                                                    <tr>
                                                        <td><?= esc(date('d/m/y', strtotime($row['tanggal_bayar']))) ?></td>
                                                        <td><?= esc($row['cara_bayar']) ?></td>
                                                        <td class="text-end fw-semibold">Rp <?= digit_group($row['jumlah_bayar']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">* Guna menambah cicilan lanjutan, gunakan menu monitoring hutang.</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- MOBILE STICKY ACTION BAR -->
            <div class="mobile-sticky-action-bar d-xl-none">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="small text-muted" style="font-size: 0.72rem; line-height: 1;">Total Tagihan</div>
                        <div class="fw-bolder fs-4 text-primary" id="mobile-summary-gross">Rp 0</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#summary-modal-mobile" title="Detail Ringkasan">
                            <i class="ti ti-info-circle fs-5"></i>
                        </button>
                        <button type="submit" class="btn btn-success px-3 fw-bold d-flex align-items-center gap-1" id="mobile-btn-save">
                            <i class="ti ti-device-floppy fs-5"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SUMMARY UNTUK MOBILE -->
<div class="modal fade" id="summary-modal-mobile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Detail Ringkasan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Jenis Item:</span>
                    <span class="fw-bold" id="m-modal-item">0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Qty:</span>
                    <span class="fw-bold" id="m-modal-qty">0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Gross:</span>
                    <span class="fw-bold text-primary" id="m-modal-gross">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Sudah Dibayar:</span>
                    <span class="fw-semibold text-secondary" id="m-modal-paid">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between p-2 rounded bg-danger-subtle">
                    <span class="text-danger small fw-semibold">Sisa Tagihan:</span>
                    <span class="fw-bold text-danger" id="m-modal-sisa">Rp 0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL INPUT PEMBAYARAN -->
<div class="modal fade" id="payment-modal" data-bs-focus="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Input Pembayaran Pembelian</h5>
                    <small class="text-muted">Lengkapi pembayaran untuk transaksi penerimaan barang (TERIMA)</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Total Tagihan</small>
                            <div class="fw-bold text-primary" id="modal-total-gross">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Telah Dibayar</small>
                            <div class="fw-semibold text-secondary" id="modal-existing-paid">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2 text-center bg-danger-subtle border-danger-subtle">
                            <small class="text-danger d-block fw-semibold" style="font-size: 0.72rem;">Sisa Tagihan</small>
                            <div class="fw-bolder text-danger font-monospace" id="modal-remaining">Rp 0</div>
                        </div>
                    </div>
                </div>

                <div id="credit-alert-container" class="d-none mb-3"></div>

                <div class="mb-3 d-none" id="existing-payment-wrapper">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0 fw-bold small text-secondary">Histori Pembayaran Tersimpan</h6>
                            <small class="text-muted">Hapus baris jika ingin membatalkan pembayaran sebelumnya.</small>
                        </div>
                    </div>
                    <div id="existing-payment-list" class="d-flex flex-column gap-2"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark fs-3">Pembayaran Transaksi</h6>
                        <small class="text-muted">Cukup pilih Metode & Nominal (tanggal otomatis saat ini).</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" id="btn-add-payment">
                        <i class="ti ti-plus"></i> Tambah Metode
                    </button>
                </div>

                <!-- Card-based payment list (No horizontal scrolling) -->
                <div id="payment-list" class="d-flex flex-column gap-2 mb-3"></div>

                <div class="border border-warning rounded p-3 bg-warning-subtle d-none" id="credit-box">
                    <div class="d-flex align-items-center gap-2 mb-2 text-warning-emphasis">
                        <i class="ti ti-alert-triangle fs-5"></i>
                        <span class="fw-bold">Pencatatan Hutang Supplier</span>
                    </div>
                    <p class="small text-muted mb-2">Sisa pembayaran yang belum terbayar akan otomatis dicatat sebagai hutang dagang supplier.</p>
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-6">
                            <label class="input-label-compact">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm bg-white" name="jatuh_tempo" id="jatuh_tempo" min="<?= date('Y-m-d') ?>" value="<?= esc($formData['header']['jatuh_tempo'] ?? date('Y-m-d', strtotime('+1 month'))) ?>">
                        </div>
                        <div class="col-12 col-sm-6">
                            <small class="text-muted d-block mt-3">* Minimal hari ini. Default adalah tempo 30 hari.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success fw-bold px-4" id="btn-confirm-save">
                    <i class="ti ti-check me-1"></i> Konfirmasi & Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const mode = '<?= $mode ?>';
    const initialHeader = <?= json_encode($formData['header'] ?? [], JSON_UNESCAPED_SLASHES) ?>;
    let detailRows = <?= json_encode($formData['details'] ?? [], JSON_UNESCAPED_SLASHES) ?>;
    let paymentRows = [];
    const existingPayments = <?= json_encode($formData['payments'] ?? [], JSON_UNESCAPED_SLASHES) ?>;
    const existingPaidTotal = Number(initialHeader.total_bayar || 0);
    const hasStoredPayments = existingPayments.length > 0;
    let existingPaymentRows = existingPayments.map(row => ({
        bayar_id: Number(row.bayar_id || 0),
        tanggal_bayar: row.tanggal_bayar || '',
        cara_bayar: row.cara_bayar || '',
        jumlah_bayar: Number(row.jumlah_bayar || 0),
        bank_nama: row.bank_nama || '',
        rekening_no: row.rekening_no || '',
        deleted: false
    }));
    let pendingRequestData = null;

    $('#payment-modal').on('shown.bs.modal', function() {
        applyMoneyMask('#payment-list');
        applyMoneyMask('#existing-payment-list');
    });
    $(function() {
        $('.select2').select2({
            width: '100%'
        });

        $('#item-search').select2({
            width: '100%',
            placeholder: 'Cari item / barcode',
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url('/pembelian/search-item') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    term: params.term
                }),
                processResults: data => data
            }
        });

        $('#item-search').on('select2:select', function(e) {
            const kodeItem = e.params.data.id;
            loadItem(kodeItem);
            $(this).val(null).trigger('change');
        });

        if (detailRows.length > 0) {
            detailRows = detailRows.map(row => normalizeInitialRow(row));
        }

        renderDetailList();
        renderExistingPaymentCards();
        bindEvents();
        updateSummary();
        syncHeaderChips();

        // If create mode and invoice empty, check if we can auto-generate
        if (mode === 'create' && !$('#invoice').val().trim()) {
            autoGenerateInvoice();
        }
    });

    function normalizeInitialRow(row) {
        const options = row.satuan_options || [];
        return {
            kode_item: row.kode_item,
            barcode: row.barcode || '',
            nama_item: row.nama_item || '',
            sat_id: row.sat_id,
            qty_beli: Number(row.qty_beli || 0),
            qty_konversi: Number(row.qty_konversi || 1),
            qty_stock: Number(row.qty_stock || 0),
            price: Number(row.price || 0),
            gross: Number(row.gross || 0),
            base_sat_id: options.length ? options[0].sat_id : row.sat_id,
            satuan_options: options.map(item => ({
                sat_id: item.sat_id,
                qty_konversi: Number(item.qty_konversi || 1),
                harga_pokok: Number(item.harga_pokok || 0)
            }))
        };
    }

    // Auto-generate Invoice Supplier format: supco-YYMMDD
    let invoiceManuallyEdited = <?= !empty($formData['header']['invoice']) ? 'true' : 'false' ?>;

    function autoGenerateInvoice(force = false) {
        if (invoiceManuallyEdited && !force) {
            return;
        }
        const supco = $('#supco').val();
        const tglVal = $('#tanggal').val(); // YYYY-MM-DD
        if (!supco) {
            return;
        }

        let dateStr = '';
        if (tglVal && tglVal.length >= 10) {
            const parts = tglVal.split('-'); // [YYYY, MM, DD]
            const yy = parts[0].slice(2);
            const mm = parts[1];
            const dd = parts[2];
            dateStr = `${yy}${mm}${dd}`;
        } else {
            const d = new Date();
            const yy = String(d.getFullYear()).slice(2);
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            dateStr = `${yy}${mm}${dd}`;
        }

        const generated = `${supco}-${dateStr}`;
        $('#invoice').val(generated);
        toggleInvalidState($('#invoice'), true);
        syncHeaderChips();
    }

    function syncHeaderChips() {
        const supText = $('#supco option:selected').text().trim() || 'Pilih Supplier';
        const invVal = $('#invoice').val().trim() || '-';
        const statusVal = $('#status_nota').val();

        $('#chip-sup-text').text(supText.length > 22 ? supText.substring(0, 20) + '...' : supText);
        $('#chip-inv-text').text(invVal);

        if (statusVal === 'TERIMA') {
            $('#chip-status-text').removeClass('bg-light-primary text-primary').addClass('bg-success-subtle text-success').text('TERIMA (Masuk)');
            $('#summary-nota-badge').removeClass('bg-primary-subtle text-primary').addClass('bg-success-subtle text-success').text('TERIMA / MASUK');
        } else {
            $('#chip-status-text').removeClass('bg-success-subtle text-success').addClass('bg-light-primary text-primary').text('PO / Draft');
            $('#summary-nota-badge').removeClass('bg-success-subtle text-success').addClass('bg-primary-subtle text-primary').text('PO / DRAFT');
        }
    }

    function bindEvents() {
        $('#status_nota').on('change', function() {
            syncHeaderChips();
            updateSummary();
        });

        $('#supco').on('change', function() {
            toggleInvalidState($(this), !!$(this).val());
            autoGenerateInvoice();
            syncHeaderChips();
        });

        $('#tanggal').on('change', function() {
            autoGenerateInvoice();
        });

        $('#invoice').on('input', function() {
            invoiceManuallyEdited = true;
            toggleInvalidState($(this), $.trim($(this).val()) !== '');
            syncHeaderChips();
        });

        $('#btn-re-generate-inv').on('click', function() {
            autoGenerateInvoice(true);
            toastr.info('Invoice di-generate ulang sesuai Supplier & Tanggal');
        });

        $('#collapseHeaderInfo').on('show.bs.collapse', function() {
            $('#header-collapse-icon').removeClass('ti-chevron-down').addClass('ti-chevron-up');
        }).on('hide.bs.collapse', function() {
            $('#header-collapse-icon').removeClass('ti-chevron-up').addClass('ti-chevron-down');
        });

        $('#jatuh_tempo').on('change', function() {
            $(this).removeClass('is-invalid');
            updateSummary();
        });

        $('#btn-add-payment').on('click', function() {
            paymentRows.push({
                cara_bayar: 'TUNAI',
                tanggal_bayar: nowLocalValue(),
                jumlah_bayar: 0,
                bank_nama: '',
                rekening_no: ''
            });
            renderPaymentCards();
            updateSummary();
        });

        $('#pembelian-form').on('submit', function(e) {
            e.preventDefault();
            submitForm();
        });

        $('#btn-confirm-save').on('click', function() {
            finalizeSave();
        });
    }

    function loadItem(kodeItem) {
        if (detailRows.some(row => row.kode_item === kodeItem)) {
            toastr.error('Item yang dipilih sudah ada di tabel pembelian');
            return;
        }

        $.getJSON(`<?= base_url('/pembelian/item-detail') ?>/${kodeItem}`, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Item tidak ditemukan');
                return;
            }
            const item = res.data;
            const options = item.satuan || [];
            if (!options.length) {
                toastr.error('Item belum memiliki satuan pembelian');
                return;
            }
            const first = options[0];
            detailRows.unshift({
                kode_item: item.kode_item,
                barcode: item.barcode || '',
                nama_item: item.nama_item || '',
                sat_id: first.sat_id,
                qty_beli: 1,
                qty_konversi: Number(first.qty_konversi || 1),
                qty_stock: Number(first.qty_konversi || 1),
                price: Number(first.harga_pokok || 0),
                gross: Number(first.harga_pokok || 0),
                base_sat_id: item.base_sat_id || first.sat_id,
                satuan_options: options.map(opt => ({
                    sat_id: opt.sat_id,
                    qty_konversi: Number(opt.qty_konversi || 1),
                    harga_pokok: Number(opt.harga_pokok || 0)
                }))
            });
            renderDetailList();
            updateSummary();
            toastr.success(`${item.nama_item} ditambahkan`);
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal mengambil detail item'));
        });
    }

    function renderDetailList() {
        const wrapper = $('#detail-list');
        wrapper.empty();
        $('#item-count-badge').text(`${detailRows.length} item dipilih`);

        if (!detailRows.length) {
            wrapper.html(`
                <div class="text-center text-muted py-5 border rounded-3 bg-light-subtle">
                    <i class="ti ti-package-off fs-8 text-secondary d-block mb-2"></i>
                    <h6 class="fw-semibold text-dark mb-1">Belum ada item dipilih</h6>
                    <small>Cari item atau scan barcode pada kolom pencarian di atas untuk memasukkan pesanan.</small>
                </div>
            `);
            return;
        }

        detailRows.forEach((row, idx) => {
            const satuanOptions = (row.satuan_options || []).map(opt => `
                <option value="${opt.sat_id}" ${opt.sat_id === row.sat_id ? 'selected' : ''} data-qty="${opt.qty_konversi}" data-price="${opt.harga_pokok}">
                    ${opt.sat_id} (1=${Number(opt.qty_konversi).toLocaleString('id-ID')})
                </option>
            `).join('');

            wrapper.append(`
                <div class="detail-item-card" data-idx="${idx}">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="item-code">${row.kode_item}</span>
                                ${row.barcode ? `<span class="badge bg-light text-muted border font-monospace"><i class="ti ti-barcode"></i> ${row.barcode}</span>` : ''}
                                <span class="row-stock-hint"><i class="ti ti-check"></i> ${stockHint(row)}</span>
                            </div>
                            <div class="item-title">${row.nama_item}</div>
                        </div>
                        <button type="button" class="btn-delete-item row-delete" title="Hapus item">
                            <i class="ti ti-trash fs-5"></i>
                        </button>
                    </div>

                    <!-- Compact Input Grid for Ergonomic Mobile & Tablet Typing -->
                    <div class="detail-input-grid">
                        <div>
                            <label class="input-label-compact"><i class="ti ti-scale"></i> Satuan</label>
                            <select class="form-select form-control-touch row-satuan">
                                ${satuanOptions}
                            </select>
                        </div>
                        <div>
                            <label class="input-label-compact"><i class="ti ti-numbers"></i> Qty Beli</label>
                            <input type="number" inputmode="decimal" min="0.01" step="any" class="form-control form-control-touch text-end fw-bold row-qty" value="${row.qty_beli}">
                        </div>
                        <div>
                            <label class="input-label-compact"><i class="ti ti-coin"></i> Harga Satuan</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light" style="font-size: 0.75rem;">Rp</span>
                                <input type="text" inputmode="numeric" class="form-control form-control-touch money text-end row-price" value="${row.price}" data-last="price">
                            </div>
                        </div>
                        <div>
                            <label class="input-label-compact text-primary"><i class="ti ti-cash"></i> Gross Subtotal</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary" style="font-size: 0.75rem;">Rp</span>
                                <input type="text" inputmode="numeric" class="form-control form-control-touch money text-end fw-bolder text-primary row-gross" value="${row.gross}" data-last="gross">
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });

        applyMoneyMask('#detail-list');
    }

    function stockHint(row) {
        const qtyStock = Number(row.qty_beli || 0) * Number(row.qty_konversi || 1);
        return `Stok bertambah ${qtyStock.toLocaleString('id-ID')} ${row.base_sat_id || row.sat_id}`;
    }

    $('#detail-list').on('change', '.row-satuan', function() {
        const idx = Number($(this).closest('[data-idx]').data('idx'));
        const selected = $(this).find(':selected');
        const defaultPrice = Number(selected.data('price') || 0);
        detailRows[idx].sat_id = selected.val();
        detailRows[idx].qty_konversi = Number(selected.data('qty') || 1);
        detailRows[idx].qty_stock = Number(detailRows[idx].qty_beli || 0) * detailRows[idx].qty_konversi;
        detailRows[idx].price = defaultPrice;
        detailRows[idx].gross = Math.round(Number(detailRows[idx].qty_beli || 0) * Number(detailRows[idx].price || 0));
        renderDetailList();
        updateSummary();
    });

    $('#detail-list').on('input', '.row-qty', function() {
        const card = $(this).closest('[data-idx]');
        const idx = Number(card.data('idx'));
        detailRows[idx].qty_beli = Number($(this).val() || 0);
        detailRows[idx].qty_stock = detailRows[idx].qty_beli * Number(detailRows[idx].qty_konversi || 1);
        detailRows[idx].gross = Math.round(detailRows[idx].qty_beli * Number(detailRows[idx].price || 0));
        card.find('.row-stock-hint').text(stockHint(detailRows[idx]));
        card.find('.row-gross').val(formatMoneyValue(detailRows[idx].gross));
        updateSummary();
    });

    $('#detail-list').on('input blur', '.row-price', function() {
        const card = $(this).closest('[data-idx]');
        const idx = Number(card.data('idx'));
        detailRows[idx].price = Number(normalizeMoneyValue($(this).val() || 0));
        detailRows[idx].gross = Math.round(Number(detailRows[idx].qty_beli || 0) * detailRows[idx].price);
        card.find('.row-gross').val(formatMoneyValue(detailRows[idx].gross));
        applyMoneyMask('#detail-list');
        updateSummary();
    });

    $('#detail-list').on('input blur', '.row-gross', function() {
        const card = $(this).closest('[data-idx]');
        const idx = Number(card.data('idx'));
        detailRows[idx].gross = Number(normalizeMoneyValue($(this).val() || 0));
        const qty = Number(detailRows[idx].qty_beli || 0);
        detailRows[idx].price = qty > 0 ? Math.round(detailRows[idx].gross / qty) : 0;
        card.find('.row-price').val(formatMoneyValue(detailRows[idx].price));
        applyMoneyMask('#detail-list');
        updateSummary();
    });

    $('#detail-list').on('click', '.row-delete', function() {
        const idx = Number($(this).closest('[data-idx]').data('idx'));
        detailRows.splice(idx, 1);
        renderDetailList();
        updateSummary();
    });

    function renderPaymentCards() {
        const $wrapper = $('#payment-list');
        $wrapper.empty();

        if (!paymentRows.length) {
            $wrapper.append(`
                <div class="text-center text-muted py-3 border rounded-3 bg-light-subtle">
                    <small>Belum ada metode pembayaran yang ditambahkan.</small>
                </div>
            `);
            updateSummary();
            return;
        }

        paymentRows.forEach((row, idx) => {
            // Ensure timestamp is auto-generated
            if (!row.tanggal_bayar) {
                row.tanggal_bayar = nowLocalValue();
            }

            const isTransfer = row.cara_bayar === 'TRANSFER';
            const cardClass = isTransfer ? 'pay-card-item is-transfer' : 'pay-card-item is-tunai';

            $wrapper.append(`
                <div class="${cardClass}" data-idx="${idx}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge ${isTransfer ? 'bg-primary' : 'bg-success'} rounded-pill px-2 py-1">
                                <i class="ti ${isTransfer ? 'ti-building-bank' : 'ti-cash'} me-1"></i>${row.cara_bayar}
                            </span>
                            <small class="text-muted font-monospace" style="font-size: 0.72rem;">
                                <i class="ti ti-clock"></i> Auto: ${toDatetimeDisplay(row.tanggal_bayar)}
                            </small>
                        </div>
                        <button type="button" class="btn-delete-pay pay-delete" title="Hapus Pembayaran">
                            <i class="ti ti-trash fs-5"></i>
                        </button>
                    </div>

                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-5">
                            <label class="input-label-compact"><i class="ti ti-wallet"></i> Metode Bayar</label>
                            <select class="form-select form-control-touch pay-method">
                                <option value="TUNAI" ${row.cara_bayar === 'TUNAI' ? 'selected' : ''}>TUNAI (Cash)</option>
                                <option value="TRANSFER" ${row.cara_bayar === 'TRANSFER' ? 'selected' : ''}>TRANSFER BANK</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-7">
                            <label class="input-label-compact text-success"><i class="ti ti-coin"></i> Nominal Bayar (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-success fw-bold" style="font-size: 0.8rem;">Rp</span>
                                <input type="text" inputmode="numeric" class="form-control form-control-touch money text-end fw-bold text-success pay-amount" value="${row.jumlah_bayar}" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <!-- Conditional Transfer Box: Only shows when TRANSFER is selected -->
                    <div class="transfer-info-box mt-2 pt-2 border-top ${isTransfer ? '' : 'd-none'}">
                        <div class="row g-2">
                            <div class="col-12 col-sm-5">
                                <label class="input-label-compact"><i class="ti ti-building-bank"></i> Nama Bank <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-touch pay-bank" placeholder="BCA / Mandiri / BRI" value="${row.bank_nama || ''}">
                            </div>
                            <div class="col-12 col-sm-7">
                                <label class="input-label-compact"><i class="ti ti-credit-card"></i> Nomor Rekening <span class="text-danger">*</span></label>
                                <input type="text" inputmode="numeric" class="form-control form-control-touch font-monospace pay-rekening" placeholder="Contoh: 1234567890" value="${row.rekening_no || ''}">
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });

        applyMoneyMask('#payment-list');
        bindPaymentEvents();
        updateSummary();
    }

    function renderExistingPaymentCards() {
        const $wrapper = $('#existing-payment-wrapper');
        const $container = $('#existing-payment-list');
        const rows = existingPaymentRows.filter(row => !row.deleted);
        $container.empty();

        if (!rows.length) {
            $wrapper.addClass('d-none');
            return;
        }

        $wrapper.removeClass('d-none');
        rows.forEach((row) => {
            const isTransfer = row.cara_bayar === 'TRANSFER';
            const bankText = isTransfer ? `${row.bank_nama || '-'} (${row.rekening_no || '-'})` : 'Tunai / Kasir';

            $container.append(`
                <div class="existing-pay-card d-flex align-items-center justify-content-between gap-2" data-bayar-id="${row.bayar_id}">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge ${isTransfer ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'} rounded-pill" style="font-size: 0.72rem;">
                                ${row.cara_bayar}
                            </span>
                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">Rp ${formatMoneyValue(row.jumlah_bayar)}</span>
                        </div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                            <i class="ti ti-calendar"></i> ${String(row.tanggal_bayar).replace('T', ' ')} &bull; <i class="ti ti-building-bank"></i> ${bankText}
                        </small>
                    </div>
                    <button type="button" class="btn-delete-pay existing-pay-delete" data-bayar-id="${row.bayar_id}" title="Hapus histori pembayaran">
                        <i class="ti ti-trash fs-5"></i>
                    </button>
                </div>
            `);
        });

        $('#existing-payment-list .existing-pay-delete').off('click').on('click', function() {
            const bayarId = Number($(this).data('bayar-id'));
            const target = existingPaymentRows.find(row => row.bayar_id === bayarId);
            if (!target) return;
            target.deleted = true;
            renderExistingPaymentCards();
            updateSummary();
        });
    }

    function bindPaymentEvents() {
        $('#payment-list .pay-method').off('change').on('change', function() {
            const card = $(this).closest('[data-idx]');
            const idx = Number(card.data('idx'));
            const method = $(this).val();
            paymentRows[idx].cara_bayar = method;
            if (method === 'TUNAI') {
                paymentRows[idx].bank_nama = '';
                paymentRows[idx].rekening_no = '';
            }
            renderPaymentCards();
        });

        $('#payment-list .pay-amount').on('input blur', function() {
            const card = $(this).closest('[data-idx]');
            const idx = Number(card.data('idx'));
            paymentRows[idx].jumlah_bayar = Number(normalizeMoneyValue($(this).val() || 0));
            updateSummary();
        });

        $('#payment-list .pay-bank').off('input').on('input', function() {
            const card = $(this).closest('[data-idx]');
            const idx = Number(card.data('idx'));
            paymentRows[idx].bank_nama = $(this).val();
        });

        $('#payment-list .pay-rekening').off('input').on('input', function() {
            const card = $(this).closest('[data-idx]');
            const idx = Number(card.data('idx'));
            paymentRows[idx].rekening_no = $(this).val();
        });

        $('#payment-list .pay-delete').off('click').on('click', function() {
            const card = $(this).closest('[data-idx]');
            const idx = Number(card.data('idx'));
            paymentRows.splice(idx, 1);
            renderPaymentCards();
        });
    }

    function updateSummary() {
        const totalQty = detailRows.reduce((sum, row) => sum + Number(row.qty_beli || 0), 0);
        const totalGross = detailRows.reduce((sum, row) => sum + Number(row.gross || 0), 0);
        const formPaid = paymentRows.reduce((sum, row) => sum + Number(row.jumlah_bayar || 0), 0);
        const activeExistingPaid = existingPaymentRows
            .filter(row => !row.deleted)
            .reduce((sum, row) => sum + Number(row.jumlah_bayar || 0), 0);
        const totalPaidAll = activeExistingPaid + formPaid;
        const remaining = Math.max(totalGross - totalPaidAll, 0);
        const isTerima = $('#status_nota').val() === 'TERIMA';
        const isKredit = isTerima && remaining > 0;

        $('#summary-item').text(detailRows.length.toLocaleString('id-ID'));
        $('#summary-qty').text(totalQty.toLocaleString('id-ID'));
        $('#summary-gross').text(`Rp ${formatMoneyValue(totalGross)}`);
        $('#summary-existing-paid').text(`Rp ${formatMoneyValue(activeExistingPaid)}`);
        $('#summary-form-paid').text(`Rp ${formatMoneyValue(formPaid)}`);
        $('#summary-sisa').text(`Rp ${formatMoneyValue(remaining)}`);

        // Mobile elements sync
        $('#mobile-summary-gross').text(`Rp ${formatMoneyValue(totalGross)}`);
        $('#m-modal-item').text(detailRows.length.toLocaleString('id-ID'));
        $('#m-modal-qty').text(totalQty.toLocaleString('id-ID'));
        $('#m-modal-gross').text(`Rp ${formatMoneyValue(totalGross)}`);
        $('#m-modal-paid').text(`Rp ${formatMoneyValue(totalPaidAll)}`);
        $('#m-modal-sisa').text(`Rp ${formatMoneyValue(remaining)}`);

        $('#credit-box').toggleClass('d-none', !isKredit || !isTerima);
        $('#modal-total-gross').text(`Rp ${formatMoneyValue(totalGross)}`);
        $('#modal-existing-paid').text(`Rp ${formatMoneyValue(activeExistingPaid)}`);
        $('#modal-remaining').text(`Rp ${formatMoneyValue(remaining)}`);
        renderCreditAlert(remaining, isTerima);
    }

    function submitForm() {
        normalizeMoneyInputs('#pembelian-form');
        const supco = $('#supco').val();
        const invoice = $.trim($('#invoice').val());

        toggleInvalidState($('#supco'), !!supco);
        toggleInvalidState($('#invoice'), invoice !== '');

        if (!$('#tanggal').val()) {
            toastr.error('Tanggal wajib diisi');
            return;
        }
        if (!supco) {
            toastr.error('Supplier wajib dipilih');
            return;
        }
        if (!invoice) {
            toastr.error('Invoice supplier wajib diisi');
            return;
        }
        if (!detailRows.length) {
            toastr.error('Tambahkan minimal satu item pembelian');
            return;
        }

        const isTerima = $('#status_nota').val() === 'TERIMA';
        const cleanedDetails = detailRows.map((row) => ({
            kode_item: row.kode_item,
            qty_beli: Number(row.qty_beli || 0),
            sat_id: row.sat_id,
            qty_konversi: Number(row.qty_konversi || 1),
            price: Number(row.price || 0),
            gross: Number(row.gross || 0)
        }));

        for (const row of cleanedDetails) {
            if (!row.kode_item || !row.sat_id || row.qty_beli <= 0 || row.price <= 0 || row.gross <= 0) {
                toastr.error('Pastikan qty, satuan, price, dan gross semua item valid');
                return;
            }
        }

        const cleanedPayments = paymentRows
            .filter(row => Number(row.jumlah_bayar || 0) > 0)
            .map((row) => ({
                cara_bayar: row.cara_bayar,
                tanggal_bayar: row.tanggal_bayar ? normalizeDateTime(row.tanggal_bayar) : '',
                jumlah_bayar: Number(row.jumlah_bayar || 0),
                bank_nama: row.bank_nama || '',
                rekening_no: row.rekening_no || ''
            }));
        const deletedPaymentIds = existingPaymentRows.filter(row => row.deleted).map(row => row.bayar_id);

        if (isTerima) {
            for (const row of cleanedPayments) {
                if (row.cara_bayar === 'TRANSFER' && (!row.bank_nama || !row.rekening_no)) {
                    toastr.error('Pembayaran transfer wajib isi nama bank dan nomor rekening');
                    return;
                }
            }
        }

        const totalGross = cleanedDetails.reduce((sum, row) => sum + row.gross, 0);
        const activeExistingPaid = existingPaymentRows
            .filter(row => !row.deleted)
            .reduce((sum, row) => sum + Number(row.jumlah_bayar || 0), 0);
        const totalPayment = cleanedPayments.reduce((sum, row) => sum + row.jumlah_bayar, 0) + activeExistingPaid;
        const remaining = Math.max(totalGross - totalPayment, 0);

        if (totalPayment > totalGross) {
            toastr.error('Total pembayaran melebihi total gross');
            return;
        }

        $('#detail_json').val(JSON.stringify(cleanedDetails));
        $('#payment_json').val(JSON.stringify(isTerima ? cleanedPayments : []));
        $('#deleted_payment_ids').remove();
        $('#pembelian-form').append(`<input type="hidden" id="deleted_payment_ids" name="deleted_payment_ids" value='${JSON.stringify(deletedPaymentIds)}'>`);
        pendingRequestData = $('#pembelian-form').serializeArray();

        if (!isTerima) {
            finalizeSave();
            return;
        }

        if (!paymentRows.length) {
            paymentRows = [{
                cara_bayar: 'TUNAI',
                tanggal_bayar: nowLocalValue(),
                jumlah_bayar: 0,
                bank_nama: '',
                rekening_no: ''
            }];
        }
        updateSummary();
        renderPaymentCards();
        $("#payment-modal").modal('show');

    }

    function finalizeSave() {
        const isTerima = $('#status_nota').val() === 'TERIMA';
        const cleanedPayments = paymentRows
            .filter(row => Number(row.jumlah_bayar || 0) > 0)
            .map((row) => ({
                cara_bayar: row.cara_bayar,
                tanggal_bayar: row.tanggal_bayar ? normalizeDateTime(row.tanggal_bayar) : '',
                jumlah_bayar: Number(row.jumlah_bayar || 0),
                bank_nama: row.bank_nama || '',
                rekening_no: row.rekening_no || ''
            }));
        const deletedPaymentIds = existingPaymentRows.filter(row => row.deleted).map(row => row.bayar_id);

        if (isTerima) {
            for (const row of cleanedPayments) {
                if (row.cara_bayar === 'TRANSFER' && (!row.bank_nama || !row.rekening_no)) {
                    toastr.error('Pembayaran transfer wajib isi nama bank dan nomor rekening');
                    return;
                }
            }
        }

        const totalGross = detailRows.reduce((sum, row) => sum + Number(row.gross || 0), 0);
        const activeExistingPaid = existingPaymentRows
            .filter(row => !row.deleted)
            .reduce((sum, row) => sum + Number(row.jumlah_bayar || 0), 0);
        const totalPayment = cleanedPayments.reduce((sum, row) => sum + row.jumlah_bayar, 0) + activeExistingPaid;
        const remaining = Math.max(totalGross - totalPayment, 0);

        if (totalPayment > totalGross) {
            toastr.error('Total pembayaran melebihi total gross');
            return;
        }

        const jatuhTempo = $('#jatuh_tempo').val();
        if (isTerima && remaining > 0) {
            if (!jatuhTempo) {
                toastr.error('Jatuh tempo wajib diisi untuk pembelian kredit');
                $('#jatuh_tempo').addClass('is-invalid');
                return;
            }
            if (jatuhTempo < '<?= date('Y-m-d') ?>') {
                toastr.error('Jatuh tempo tidak boleh mundur');
                $('#jatuh_tempo').addClass('is-invalid');
                return;
            }
            $('#jatuh_tempo').removeClass('is-invalid');
        } else {
            $('#jatuh_tempo').removeClass('is-invalid');
        }

        $('#payment_json').val(JSON.stringify(isTerima ? cleanedPayments : []));
        $('#deleted_payment_ids').remove();
        $('#pembelian-form').append(`<input type="hidden" id="deleted_payment_ids" name="deleted_payment_ids" value='${JSON.stringify(deletedPaymentIds)}'>`);
        pendingRequestData = $('#pembelian-form').serializeArray();
        if (jatuhTempo) {
            pendingRequestData.push({
                name: 'jatuh_tempo',
                value: jatuhTempo
            });
        }

        $.ajax({
            type: 'POST',
            url: '<?= base_url('/pembelian') ?>',
            dataType: 'json',
            data: pendingRequestData,
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Berhasil');
                    $("#payment-modal").modal('hide');
                    window.location.href = '<?= base_url('/pembelian') ?>';
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan pembelian');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan pembelian'));
            }
        });
    }

    function renderCreditAlert(remaining, isTerima) {
        const jatuhTempo = $('#jatuh_tempo').val();
        const shouldShow = isTerima && remaining > 0 && !!jatuhTempo;
        $('#credit-box').toggleClass('d-none', !shouldShow);
        $('#credit-alert-container').toggleClass('d-none', !shouldShow);
        if (!shouldShow) {
            $('#credit-alert-container').empty();
            return;
        }

        $('#credit-alert-container').html(`
            <div class="alert customize-alert alert-dismissible alert-light-danger bg-danger-subtle text-danger fade show remove-close-icon" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="d-flex align-items-center me-3 me-md-0">
                    <i class="ti ti-cancel fs-5 me-2 text-danger"></i>
                    <span class="text-dark">Sisa pembayaran sebesar <strong class="text-danger font-monospace">Rp ${formatMoneyValue(remaining)}</strong> akan dicatat sebagai <strong>hutang</strong> dengan tanggal jatuh tempo <strong class="text-primary">${new Date(jatuhTempo).toLocaleDateString('id-ID')}</strong> (${humanizeDate(jatuhTempo)}).</span>
                </div>
            </div>
        `);
    }

    function toggleInvalidState($element, isValid) {
        $element.toggleClass('is-invalid', !isValid);
        if ($element.hasClass('select2-hidden-accessible')) {
            $element.next('.select2-container').find('.select2-selection').toggleClass('is-invalid', !isValid);
        }
    }

    function nowLocalValue() {
        const now = new Date();
        const tzOffset = now.getTimezoneOffset() * 60000;
        return new Date(now - tzOffset).toISOString().slice(0, 16);
    }

    function toDatetimeLocal(value) {
        if (!value) return nowLocalValue();
        const dt = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(dt.getTime())) return nowLocalValue();
        const tzOffset = dt.getTimezoneOffset() * 60000;
        return new Date(dt - tzOffset).toISOString().slice(0, 16);
    }

    function toDatetimeDisplay(value) {
        if (!value) return '';
        try {
            const dt = new Date(value.replace(' ', 'T'));
            if (Number.isNaN(dt.getTime())) return value;
            return dt.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace('.', ':');
        } catch (e) {
            return value;
        }
    }

    function normalizeDateTime(value) {
        return value ? value.replace('T', ' ') + ':00' : '';
    }
</script>
<?= $this->endSection('javascript') ?>