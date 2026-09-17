<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 * @var array  $kategoriOptions
 * @var array  $supplierOptions
 * @var array  $satuanOptions
 * @var array  $formData
 */
?>
<link rel="stylesheet" href="<?= base_url(); ?>/assets/libs/select2/dist/css/select2.min.css" />

<style>
    /* Styling & Anti-Slop Human / Mobile Optimizations */
    :root {
        --color-step-active: #0284c7;
        --color-step-done: #059669;
        --color-focus-ring: #0284c7;
    }

    .form-control:focus,
    .form-select:focus,
    .btn:focus-visible {
        outline: 2px solid var(--color-focus-ring) !important;
        outline-offset: 2px !important;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2) !important;
    }

    /* Touch target >= 44px */
    .btn,
    .form-control,
    .form-select,
    .nav-pills .nav-link,
    .select2-container .select2-selection--single {
        min-height: 44px !important;
    }

    .select2-container .select2-selection--single {
        display: flex !important;
        align-items: center !important;
    }

    /* Stepper Navigation */
    .stepper-nav {
        display: flex;
        gap: 8px;
        padding: 4px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .stepper-nav .nav-item {
        flex: 1;
        min-width: 120px;
    }

    .stepper-nav .nav-link {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 14px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #475569;
        border-radius: 8px;
        background: transparent;
        border: 1px solid transparent;
        transition: all 0.2s ease;
        text-align: center;
    }

    .stepper-nav .nav-link .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 0.75rem;
        font-weight: 700;
        background: #e2e8f0;
        color: #1e293b;
    }

    .stepper-nav .nav-link.active {
        background: #ffffff;
        color: #0369a1;
        border-color: #bae6fd;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .stepper-nav .nav-link.active .step-badge {
        background: #0284c7;
        color: #ffffff;
    }

    .stepper-nav .nav-link.is-completed .step-badge {
        background: #059669;
        color: #ffffff;
    }

    /* Banner Petunjuk (High Contrast WCAG AAA) */
    .guide-banner {
        background-color: #eff6ff;
        border-left: 5px solid #2563eb;
        border-radius: 8px;
        padding: 14px 16px;
        color: #1e3a8a;
        margin-bottom: 18px;
    }

    .guide-banner h6 {
        color: #1e3a8a;
        font-weight: 700;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .guide-banner ul {
        margin: 0;
        padding-left: 20px;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    /* Guardrail Alert Callouts */
    .guardrail-alert {
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 0.875rem;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }

    .guardrail-warning {
        background-color: #fffbeb;
        border: 1px solid #fde68a;
        border-left: 5px solid #d97706;
        color: #78350f;
    }

    .guardrail-success {
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-left: 5px solid #16a34a;
        color: #14532d;
    }

    .guardrail-danger {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        border-left: 5px solid #dc2626;
        color: #7f1d1d;
    }

    /* Mobile Price Card */
    .mobile-price-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 14px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: border-color 0.2s ease;
    }

    .mobile-price-card.has-error {
        border-color: #ef4444;
        background-color: #fffafa;
    }

    .mobile-price-card.has-profit {
        border-color: #86efac;
    }

    .input-rupiah-group {
        position: relative;
    }

    .input-rupiah-group .input-group-text {
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
        border-color: #cbd5e1;
    }

    .price-preview-text {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 4px;
        font-weight: 500;
    }

    .profit-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .stepper-nav {
            justify-content: flex-start;
        }

        .stepper-nav .nav-link {
            font-size: 0.8rem;
            padding: 8px 10px;
            white-space: nowrap;
        }

        .desktop-table-view {
            display: none !important;
        }

        .mobile-card-view {
            display: block !important;
        }

        .bottom-action-bar {
            position: sticky;
            bottom: 0;
            background: #ffffff;
            padding: 12px 0;
            border-top: 1px solid #e2e8f0;
            z-index: 100;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
        }
    }

    @media (min-width: 768px) {
        .desktop-table-view {
            display: block !important;
        }

        .mobile-card-view {
            display: none !important;
        }
    }
</style>

<div class="body-wrapper pb-5">
    <div class="container-fluid p-0">
        <!-- Header Card -->
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-8 col-md-9">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-circle p-2 text-white">
                                <i class="ti <?= $mode === 'create' ? 'ti-plus' : 'ti-pencil' ?> fs-5"></i>
                            </span>
                            <div>
                                <h4 class="fw-semibold mb-0 fs-5"><?= $mode === 'create' ? 'Tambah' : 'Edit' ?> Data Barang</h4>
                                <small class="text-muted d-block">Panduan 3 langkah: Master, Satuan, dan Harga.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 col-md-3 text-end">
                        <button type="button" onclick="handleItemBack()" class="btn btn-outline-secondary btn-sm" aria-label="Kembali ke Daftar Barang">
                            <i class="ti ti-arrow-left"></i> <span class="d-none d-sm-inline">Kembali</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-3 p-md-4">
                <!-- Stepper Tabs -->
                <ul class="nav stepper-nav mb-4" id="stepTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" type="button" data-step="1" id="tab-step-1">
                            <span class="step-badge" id="badge-step-1">1</span>
                            <span>1. Master Barang</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" type="button" data-step="2" id="tab-step-2">
                            <span class="step-badge" id="badge-step-2">2</span>
                            <span>2. Satuan & Konversi</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" type="button" data-step="3" id="tab-step-3">
                            <span class="step-badge" id="badge-step-3">3</span>
                            <span>3. Harga & Margin</span>
                        </button>
                    </li>
                </ul>

                <form id="item-form" novalidate>
                    <input type="hidden" name="_method" id="_method" value="<?= $mode === 'create' ? 'PUT' : 'PATCH' ?>">
                    <input type="hidden" name="satuan_json" id="satuan_json">
                    <input type="hidden" name="store_json" id="store_json">

                    <!-- ============================================== -->
                    <!-- STEP 1: MASTER DATA                            -->
                    <!-- ============================================== -->
                    <div class="step-panel" data-step-panel="1">
                        <!-- Validation Callout for Step 1 -->
                        <div class="guardrail-alert guardrail-danger d-none" id="step1-alert">
                            <i class="ti ti-alert-circle fs-5 shrink-0 mt-1"></i>
                            <div>
                                <strong>Lengkapi Data Wajib:</strong>
                                <div id="step1-alert-msg" class="mt-1">Nama Item dan Kategori wajib diisi sebelum lanjut.</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- Kode Item -->
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold" for="kode_item">
                                    Kode Item <span class="text-danger">*</span>
                                    <span class="badge bg-light text-secondary border ms-1" title="Kode item otomatis digenerate oleh sistem">
                                        <i class="ti ti-lock"></i> Otomatis
                                    </span>
                                </label>
                                <input type="text" class="form-control bg-light" name="kode_item" id="kode_item" readonly required value="<?= esc($formData['prodmast']['kode_item'] ?? '') ?>">
                                <div class="form-text text-muted small">Nomor unik dari sistem (tidak bisa diubah).</div>
                            </div>

                            <!-- Nama Item -->
                            <div class="col-12 col-md-5">
                                <label class="form-label fw-bold" for="nama_item">
                                    Nama Barang / Item <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="nama_item" id="nama_item" required placeholder="Contoh: Nugget Fiesta Crispy 500gr" value="<?= esc($formData['prodmast']['nama_item'] ?? '') ?>">
                                <div class="form-text text-muted small" id="nama-item-hint">
                                    <i class="ti ti-pencil"></i> Ketik nama barang lengkap dengan gramatur / rasa.
                                </div>
                            </div>

                            <!-- Barcode -->
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" for="barcode">
                                    Barcode Scanner <span class="badge bg-light text-muted border ms-1">Opsional</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-scan"></i></span>
                                    <input type="text" class="form-control" name="barcode" id="barcode" placeholder="Scan barcode kemasan produk" value="<?= esc($formData['prodmast']['barcode'] ?? '') ?>">
                                </div>
                                <div class="form-text text-muted small">Bisa scan langsung atau kosongkan jika tidak ada.</div>
                            </div>

                            <!-- Kategori -->
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" for="kat_id">
                                    Kategori Barang <span class="text-danger">*</span>
                                </label>
                                <select class="form-select select2" name="kat_id" id="kat_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($kategoriOptions as $row) : ?>
                                        <option value="<?= esc($row['kat_id']) ?>" <?= ($formData['prodmast']['kat_id'] ?? '') === $row['kat_id'] ? 'selected' : '' ?>><?= esc($row['kat_id']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted small">Pilih kelompok kategori produk.</div>
                            </div>

                            <!-- Supplier -->
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" for="supco">
                                    Supplier Utama <span class="badge bg-light text-muted border ms-1">Opsional</span>
                                </label>
                                <select class="form-select select2" name="supco" id="supco">
                                    <option value="">-- Tanpa Supplier Tertentu --</option>
                                    <?php foreach ($supplierOptions as $row) : ?>
                                        <option value="<?= esc($row['supco']) ?>" <?= ($formData['store_supco'] ?? '') === $row['supco'] ? 'selected' : '' ?>><?= esc($row['supco']) ?> - <?= esc($row['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted small">Pemasok utama untuk riwayat pembelian.</div>
                            </div>

                            <!-- Status Item -->
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">Status Penjualan Toko</label>
                                <div class="p-2 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                                    <?php $statusItem = $formData['store'][0]['status_item'] ?? 'N'; ?>
                                    <div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="status_item" <?= $statusItem === 'Y' ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold ms-1" for="status_item" id="status-label">
                                                <?= $statusItem === 'Y' ? 'Barang Aktif' : 'Barang Nonaktif' ?>
                                            </label>
                                        </div>
                                    </div>
                                    <span id="status-badge" class="badge <?= $statusItem === 'Y' ? 'bg-success text-white' : 'bg-secondary text-white' ?>">
                                        <i class="ti <?= $statusItem === 'Y' ? 'ti-check' : 'ti-eye-off' ?>"></i>
                                        <?= $statusItem === 'Y' ? 'Bisa Dijual' : 'Disembunyikan' ?>
                                    </span>
                                </div>
                                <div class="form-text text-muted small">Jika nonaktif, barang tidak muncul di menu kasir POS.</div>
                            </div>

                            <!-- Keterangan -->
                            <div class="col-12">
                                <label class="form-label fw-bold" for="keterangan">Keterangan / Catatan Tambahan</label>
                                <input type="text" class="form-control" name="keterangan" id="keterangan" placeholder="Contoh: Suhu freezer min -18C, expired tertera di kemasan" value="<?= esc($formData['prodmast']['keterangan'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- STEP 2: SATUAN & KONVERSI                      -->
                    <!-- ============================================== -->
                    <div class="step-panel d-none" data-step-panel="2">
                        <!-- Petunjuk Pengisian Step 2 -->
                        <div class="guide-banner">
                            <h6><i class="ti ti-bulb fs-5"></i> Panduan Satuan & Konversi untuk Newbie:</h6>
                            <ul>
                                <li><strong>Wajib ada Satuan Terkecil (Qty = 1)</strong>: Daftarkan satuan dasar jual satuan (misal: <strong>PCS = 1</strong> atau <strong>BKS = 1</strong>).</li>
                                <li><strong>Satuan Grosir / Dus</strong>: Isi jumlah isi satuan kecilnya (misal: <strong>DUS = 24</strong> karena 1 DUS berisi 24 PCS).</li>
                                <li>Pengurangan stok penjualan dan pembelian akan otomatis dihitung berdasarkan satuan ini.</li>
                            </ul>
                        </div>

                        <!-- Live Status Guardrail: Satuan Dasar -->
                        <div id="satuan-dasar-status" class="guardrail-alert guardrail-warning">
                            <i class="ti ti-alert-triangle fs-5 shrink-0 mt-1"></i>
                            <div>
                                <strong id="satuan-status-title">Satuan Dasar Belum Terdaftar!</strong>
                                <div id="satuan-status-desc" class="mt-1">
                                    Tambahkan satuan terkecil dengan <strong>Qty Konversi = 1</strong> (contoh: PCS = 1).
                                </div>
                            </div>
                        </div>

                        <!-- Form Input Satuan -->
                        <div class="card bg-light border-0 p-3 mb-3">
                            <h6 class="fw-bold mb-3 text-dark"><i class="ti ti-plus"></i> Tambah Satuan Barang:</h6>
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label class="form-label fw-bold" for="sat_id_input">Pilih Satuan</label>
                                    <select class="form-select select2" id="sat_id_input">
                                        <option value="">-- Pilih Satuan --</option>
                                        <?php foreach ($satuanOptions as $row) : ?>
                                            <option value="<?= esc($row['sat_id']) ?>"><?= esc($row['sat_id']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold m-0" for="qty_konversi_input">Qty Konversi</label>
                                        <button type="button" class="btn btn-sm btn-link p-0 text-primary fw-semibold" onclick="setQuickQty(1)" title="Set sebagai satuan dasar = 1">
                                            <i class="ti ti-star"></i> Set Qty = 1 (Dasar)
                                        </button>
                                    </div>
                                    <input type="number" min="0.0001" step="any" inputmode="decimal" class="form-control" id="qty_konversi_input" placeholder="Misal: 1 atau 24">
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="button" id="btn-add-satuan" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i class="ti ti-plus"></i> Tambah Satuan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Satuan: Tampilan Desktop (Table) -->
                        <div class="desktop-table-view">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" id="table-satuan">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 25%;">Satuan</th>
                                            <th style="width: 25%;">Qty Konversi</th>
                                            <th style="width: 35%;">Penjelasan Stok</th>
                                            <th style="width: 15%;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Daftar Satuan: Tampilan Mobile (Cards) -->
                        <div class="mobile-card-view" id="mobile-satuan-cards"></div>
                    </div>

                    <!-- ============================================== -->
                    <!-- STEP 3: HARGA & MARGIN                         -->
                    <!-- ============================================== -->
                    <div class="step-panel d-none" data-step-panel="3">
                        <!-- Petunjuk Pengisian Step 3 -->
                        <div class="guide-banner">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                                <div>
                                    <h6><i class="ti ti-cash fs-5"></i> Panduan Harga & Margin Keuntungan:</h6>
                                    <ul class="mb-0">
                                        <li><strong>Harga Pokok (Modal)</strong>: Modal beli barang dari supplier.</li>
                                        <li><strong>Harga Jual</strong>: Harga jual ke konsumen toko (tidak boleh lebih kecil dari Modal).</li>
                                        <li><strong>Margin Keuntungan</strong> dihitung otomatis: <code>((Jual - Pokok) / Pokok) * 100%</code>.</li>
                                    </ul>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary shrink-0 mt-2 mt-md-0" id="btn-auto-fill-helper" onclick="manualTriggerAutoFill()">
                                    <i class="ti ti-calculator"></i> Hitung Satuan Lain Otomatis
                                </button>
                            </div>
                        </div>

                        <!-- Live Alert Banner jika ada harga rugi atau belum diisi -->
                        <div class="guardrail-alert guardrail-danger d-none" id="harga-error-alert">
                            <i class="ti ti-alert-triangle fs-5 shrink-0 mt-1"></i>
                            <div>
                                <strong>Peringatan Kesalahan Input Harga:</strong>
                                <div id="harga-error-list" class="mt-1"></div>
                            </div>
                        </div>

                        <!-- Tampilan Desktop: Tabel Harga -->
                        <div class="desktop-table-view">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" id="table-harga">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 15%;">Satuan</th>
                                            <th style="width: 12%;" class="text-end">Qty Konversi</th>
                                            <th style="width: 25%;">Harga Pokok (Modal)</th>
                                            <th style="width: 25%;">Harga Jual (Konsumen)</th>
                                            <th style="width: 23%;">Margin & Keuntungan</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tampilan Mobile: Kartu Harga Touch-Friendly -->
                        <div class="mobile-card-view" id="mobile-harga-cards"></div>
                    </div>

                    <!-- Action Bar Navigasi (Bottom Bar) -->
                    <div class="mt-4 pt-3 border-top bottom-action-bar">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <button type="button" class="btn btn-secondary px-3 px-md-4" id="btn-prev" disabled>
                                <i class="ti ti-arrow-left me-1"></i> Sebelumnya
                            </button>
                            <div>
                                <button type="button" class="btn btn-primary px-3 px-md-4" id="btn-next">
                                    Selanjutnya <i class="ti ti-arrow-right ms-1"></i>
                                </button>
                                <button type="submit" class="btn btn-success px-3 px-md-4 d-none" id="btn-save">
                                    <i class="ti ti-device-floppy me-1"></i> Simpan Data Barang
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script src="<?= base_url(); ?>/assets/libs/select2/dist/js/select2.min.js"></script>
<script>
    const mode = '<?= $mode ?>';
    let activeStep = 1;
    let satuanRows = <?= json_encode($formData['satuan'] ?? []) ?>;
    let storeRows = <?= json_encode($formData['store'] ?? []) ?>;
    let previousSatuanRows = JSON.parse(JSON.stringify(satuanRows));

    $(function() {
        // Init Select2
        $('.select2').select2({
            width: '100%'
        });

        // Interactivity switch status item
        $('#status_item').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('#status-label').text(isChecked ? 'Barang Aktif' : 'Barang Nonaktif');
            const badge = $('#status-badge');
            if (isChecked) {
                badge.removeClass('bg-secondary').addClass('bg-success').html('<i class="ti ti-check"></i> Bisa Dijual');
            } else {
                badge.removeClass('bg-success').addClass('bg-secondary').html('<i class="ti ti-eye-off"></i> Disembunyikan');
            }
        });

        // Real-time input validation feedback on Step 1
        $('#nama_item').on('input blur', function() {
            const val = $(this).val().trim();
            if (val.length >= 3) {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $('#step1-alert').addClass('d-none');
            } else {
                $(this).removeClass('is-valid');
            }
        });

        $('#kat_id').on('change', function() {
            if ($(this).val()) {
                $('#step1-alert').addClass('d-none');
            }
        });

        initStepper();
        renderSatuanTable();
        syncHargaTable();
        updateSatuanStatusAlert();
    });

    // ========================================================
    // STEPPER MANAGEMENT & VALIDATION GUARDS (JEGATAN)
    // ========================================================
    function initStepper() {
        updateStepper();

        $('#btn-prev').on('click', function() {
            if (activeStep > 1) {
                activeStep--;
                updateStepper();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });

        $('#btn-next').on('click', function() {
            if (activeStep === 1) {
                if (!validateStep1()) return;
                activeStep = 2;
                updateStepper();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                return;
            }

            if (activeStep === 2) {
                if (!validateStep2()) return;
                activeStep = 3;
                updateStepper();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                return;
            }
        });

        $('#stepTabs .nav-link').on('click', function() {
            const target = Number($(this).data('step'));
            if (target === activeStep) return;

            if (target === 2) {
                if (!validateStep1()) return;
                activeStep = 2;
                updateStepper();
            } else if (target === 3) {
                if (!validateStep1()) return;
                if (!validateStep2()) return;
                activeStep = 3;
                updateStepper();
            } else if (target === 1) {
                activeStep = 1;
                updateStepper();
            }
        });
    }

    function updateStepper() {
        // Toggle panel display
        $('.step-panel').addClass('d-none');
        $(`[data-step-panel="${activeStep}"]`).removeClass('d-none');

        // Update nav-pills active state
        $('#stepTabs .nav-link').removeClass('active');
        $(`#stepTabs .nav-link[data-step="${activeStep}"]`).addClass('active');

        // Update completion badge
        if (checkStep1ValidSilent()) {
            $('#tab-step-1').addClass('is-completed');
            $('#badge-step-1').html('<i class="ti ti-check"></i>');
        } else {
            $('#tab-step-1').removeClass('is-completed');
            $('#badge-step-1').text('1');
        }

        if (checkStep2ValidSilent()) {
            $('#tab-step-2').addClass('is-completed');
            $('#badge-step-2').html('<i class="ti ti-check"></i>');
        } else {
            $('#tab-step-2').removeClass('is-completed');
            $('#badge-step-2').text('2');
        }

        // Action buttons state
        $('#btn-prev').prop('disabled', activeStep === 1);
        $('#btn-next').toggleClass('d-none', activeStep === 3);
        $('#btn-save').toggleClass('d-none', activeStep !== 3);

        if (activeStep === 3) {
            syncHargaTable();
            validatePricesLive();
        }
    }

    function checkStep1ValidSilent() {
        const kode = ($('#kode_item').val() || '').trim();
        const nama = ($('#nama_item').val() || '').trim();
        const kat = ($('#kat_id').val() || '').trim();
        return (kode !== '' && nama.length >= 2 && kat !== '');
    }

    function checkStep2ValidSilent() {
        if (satuanRows.length === 0) return false;
        return satuanRows.some(row => Number(row.qty_konversi) === 1);
    }

    // Jegatan Step 1: Validasi form master
    function validateStep1() {
        const kode = ($('#kode_item').val() || '').trim();
        const nama = ($('#nama_item').val() || '').trim();
        const kat = ($('#kat_id').val() || '').trim();

        const missing = [];
        if (!kode) missing.push('Kode Item');
        if (!nama) missing.push('Nama Item / Barang');
        if (!kat) missing.push('Kategori Barang');

        if (missing.length > 0) {
            $('#step1-alert-msg').html(`Mohon lengkapi kolom wajib berikut: <strong>${missing.join(', ')}</strong>.`);
            $('#step1-alert').removeClass('d-none');

            if (!nama) {
                $('#nama_item').addClass('is-invalid').focus();
            } else if (!kat) {
                $('#kat_id').select2('open');
            }
            toastr.error('Lengkapi data wajib Step 1 terlebih dahulu.');
            return false;
        }

        $('#step1-alert').addClass('d-none');
        $('#nama_item').removeClass('is-invalid');
        return true;
    }

    // Jegatan Step 2: Validasi satuan & konversi dasar
    function validateStep2() {
        if (satuanRows.length === 0) {
            toastr.error('Belum ada satuan yang ditambahkan! Minimal isi 1 satuan terkecil.');
            $('#sat_id_input').select2('open');
            return false;
        }

        const hasBase = satuanRows.some(row => Number(row.qty_konversi) === 1);
        if (!hasBase) {
            toastr.error('Wajib mendaftarkan satuan dasar terkecil dengan Qty Konversi = 1');
            $('#qty_konversi_input').focus();
            return false;
        }

        return true;
    }

    // Quick helper button: set Qty = 1
    function setQuickQty(val) {
        $('#qty_konversi_input').val(val);
        $('#qty_konversi_input').focus();
    }

    // Update alert status satuan dasar
    function updateSatuanStatusAlert() {
        const base = satuanRows.find(row => Number(row.qty_konversi) === 1);
        const $alert = $('#satuan-dasar-status');

        if (!base) {
            $alert.removeClass('guardrail-success').addClass('guardrail-warning');
            $alert.find('i').removeClass('ti-circle-check text-success').addClass('ti-alert-triangle text-warning');
            $('#satuan-status-title').html('Satuan Dasar (Qty = 1) Belum Ada!');
            $('#satuan-status-desc').html('Wajib menambahkan satu satuan terkecil dengan <strong>Qty Konversi = 1</strong> (Contoh: PCS = 1 atau BKS = 1) agar bisa lanjut ke Step 3.');
        } else {
            $alert.removeClass('guardrail-warning').addClass('guardrail-success');
            $alert.find('i').removeClass('ti-alert-triangle text-warning').addClass('ti-circle-check text-success');
            $('#satuan-status-title').html(`Satuan Dasar: <span class="badge bg-success fs-6">${base.sat_id}</span> (Qty = 1)`);
            $('#satuan-status-desc').html(`Satuan dasar berhasil ditentukan. Satuan grosir/lainnya akan dihitung kelipatannya dari 1 <strong>${base.sat_id}</strong>.`);
        }
    }

    // ========================================================
    // STEP 2: SATUAN TABLE & MOBILE CARDS
    // ========================================================
    $('#btn-add-satuan').on('click', function() {
        const satId = ($('#sat_id_input').val() || '').trim();
        const qtyRaw = $('#qty_konversi_input').val();
        const qty = Number(qtyRaw);

        if (!satId) {
            toastr.warning('Silakan pilih nama satuan terlebih dahulu');
            $('#sat_id_input').select2('open');
            return;
        }

        if (!qtyRaw || isNaN(qty) || qty <= 0) {
            toastr.warning('Qty konversi harus berupa angka lebih dari 0');
            $('#qty_konversi_input').focus();
            return;
        }

        if (satuanRows.some(row => row.sat_id === satId)) {
            toastr.error(`Satuan "${satId}" sudah terdaftar dalam daftar.`);
            return;
        }

        if (satuanRows.some(row => Number(row.qty_konversi) === qty)) {
            toastr.error(`Qty Konversi ${qty} sudah digunakan oleh satuan lain.`);
            return;
        }

        // Cegah double base unit (qty = 1)
        if (qty === 1 && satuanRows.some(row => Number(row.qty_konversi) === 1)) {
            const existingBase = satuanRows.find(row => Number(row.qty_konversi) === 1);
            toastr.warning(`Satuan dasar dengan Qty = 1 sudah ada (${existingBase.sat_id}). Hanya boleh ada 1 satuan terkecil.`);
            return;
        }

        satuanRows.push({
            sat_id: satId,
            qty_konversi: qty
        });

        // Urutkan dari qty konversi terkecil ke terbesar
        satuanRows.sort((a, b) => Number(a.qty_konversi) - Number(b.qty_konversi));

        renderSatuanTable();
        syncHargaTable();
        updateSatuanStatusAlert();

        // Reset inputs
        $('#sat_id_input').val('').trigger('change');
        $('#qty_konversi_input').val('');
        toastr.success(`Satuan ${satId} (${qty}) berhasil ditambahkan.`);
    });

    function renderSatuanTable() {
        const $tbody = $('#table-satuan tbody');
        const $mobileList = $('#mobile-satuan-cards');
        $tbody.empty();
        $mobileList.empty();

        if (satuanRows.length === 0) {
            $tbody.append('<tr><td colspan="4" class="text-center py-3 text-muted"><i class="ti ti-inbox fs-5 d-block mb-1"></i>Belum ada data satuan. Silakan tambahkan satuan di atas.</td></tr>');
            $mobileList.append('<div class="text-center py-4 text-muted bg-light rounded-3 border"><i class="ti ti-inbox fs-4 d-block mb-1"></i>Belum ada data satuan. Silakan tambahkan di atas.</div>');
            return;
        }

        const baseRow = satuanRows.find(row => Number(row.qty_konversi) === 1);
        const baseName = baseRow ? baseRow.sat_id : 'Satuan Dasar';

        satuanRows.forEach((row, idx) => {
            const isBase = Number(row.qty_konversi) === 1;
            const explanation = isBase ?
                `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="ti ti-star"></i> Satuan Terkecil / Dasar</span>` :
                `<span class="text-secondary fw-semibold">1 ${row.sat_id} = ${row.qty_konversi} ${baseName}</span>`;

            // Desktop Row
            $tbody.append(`<tr>
                <td class="fw-bold">${row.sat_id}</td>
                <td><span class="badge bg-light text-dark border px-2 py-1 fs-6">${row.qty_konversi}</span></td>
                <td>${explanation}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-del-satuan" data-idx="${idx}" title="Hapus satuan ini">
                        <i class="ti ti-trash"></i> Hapus
                    </button>
                </td>
            </tr>`);

            // Mobile Card
            $mobileList.append(`
                <div class="mobile-price-card mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-5 fw-bold text-dark me-2">${row.sat_id}</span>
                            <span class="badge bg-light text-dark border">Qty: ${row.qty_konversi}</span>
                            <div class="mt-1">${explanation}</div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-del-satuan btn-sm" data-idx="${idx}" aria-label="Hapus satuan ${row.sat_id}">
                            <i class="ti ti-trash fs-5"></i>
                        </button>
                    </div>
                </div>
            `);
        });
    }

    $(document).on('click', '.btn-del-satuan', function() {
        const idx = Number($(this).data('idx'));
        const satName = satuanRows[idx]?.sat_id || '';

        if (confirm(`Yakin ingin menghapus satuan "${satName}"?`)) {
            satuanRows.splice(idx, 1);
            renderSatuanTable();
            syncHargaTable();
            updateSatuanStatusAlert();
            toastr.info(`Satuan ${satName} dihapus.`);
        }
    });

    // ========================================================
    // STEP 3: SYNC HARGA, MARGIN, PROFIT CALCULATIONS
    // ========================================================
    function syncHargaTable() {
        const oldQtyBySat = {};
        previousSatuanRows.forEach(row => oldQtyBySat[row.sat_id] = Number(row.qty_konversi || 0));
        const existingMap = {};
        storeRows.forEach(row => existingMap[row.sat_id] = row);
        const oldBaseRow = storeRows.find(row => (oldQtyBySat[row.sat_id] || 0) === 1) || null;
        const oldBasePokok = Number(oldBaseRow?.harga_pokok || 0);
        const oldBaseJual = Number(oldBaseRow?.harga_jual || 0);

        storeRows = satuanRows.map(row => {
            const old = existingMap[row.sat_id] || {};
            const newQty = Number(row.qty_konversi || 0);
            const oldQty = Number(oldQtyBySat[row.sat_id] || 0);
            let hargaPokok = Number(old.harga_pokok || 0);
            let hargaJual = Number(old.harga_jual || 0);

            if (oldQty > 0 && newQty > 0 && oldQty !== newQty) {
                const unitPokok = hargaPokok > 0 ? (hargaPokok / oldQty) : 0;
                const unitJual = hargaJual > 0 ? (hargaJual / oldQty) : 0;
                hargaPokok = unitPokok > 0 ? Math.round(unitPokok * newQty) : 0;
                hargaJual = unitJual > 0 ? Math.round(unitJual * newQty) : 0;
            }

            if ((!old || Object.keys(old).length === 0) && newQty > 0) {
                if (oldBasePokok > 0) hargaPokok = Math.round(oldBasePokok * newQty);
                if (oldBaseJual > 0) hargaJual = Math.round(oldBaseJual * newQty);
            }

            if (newQty === 1 && oldBasePokok > 0 && hargaPokok === 0) hargaPokok = oldBasePokok;
            if (newQty === 1 && oldBaseJual > 0 && hargaJual === 0) hargaJual = oldBaseJual;

            return {
                sat_id: row.sat_id,
                harga_pokok: hargaPokok,
                harga_jual: hargaJual
            };
        });

        previousSatuanRows = JSON.parse(JSON.stringify(satuanRows));
        renderHargaTable();
    }

    function renderHargaTable() {
        const $tbody = $('#table-harga tbody');
        const $mobileCards = $('#mobile-harga-cards');
        $tbody.empty();
        $mobileCards.empty();

        if (storeRows.length === 0) {
            $tbody.append('<tr><td colspan="5" class="text-center py-3 text-muted">Belum ada data satuan. Selesaikan Step 2 terlebih dahulu.</td></tr>');
            $mobileCards.append('<div class="text-center py-4 text-muted bg-light rounded-3 border">Belum ada satuan terdaftar. Selesaikan Step 2 terlebih dahulu.</div>');
            return;
        }

        const qtyBySat = {};
        satuanRows.forEach(row => qtyBySat[row.sat_id] = Number(row.qty_konversi || 0));

        storeRows.forEach((row, idx) => {
            const qtyKonversi = qtyBySat[row.sat_id] || 0;
            const isBase = (qtyKonversi === 1);
            const badgeBase = isBase ? '<span class="badge bg-primary text-white ms-1"><i class="ti ti-star"></i> Dasar</span>' : '';
            const valHp = formatMoneyValue(row.harga_pokok);
            const valHj = formatMoneyValue(row.harga_jual);

            // Desktop Row
            $tbody.append(`
                <tr data-row-idx="${idx}">
                    <td>
                        <span class="fw-bold fs-6">${row.sat_id}</span>
                        ${badgeBase}
                    </td>
                    <td class="text-end fw-semibold">${qtyKonversi}</td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control money harga-pokok" data-idx="${idx}" inputmode="numeric" value="${valHp}" placeholder="Modal" aria-label="Harga Pokok ${row.sat_id}">
                        </div>
                        <div class="price-preview-text hp-preview">Terbaca: Rp ${valHp}</div>
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control money harga-jual" data-idx="${idx}" inputmode="numeric" value="${valHj}" placeholder="Harga Konsumen" aria-label="Harga Jual ${row.sat_id}">
                        </div>
                        <div class="price-preview-text hj-preview">Terbaca: Rp ${valHj}</div>
                    </td>
                    <td>
                        <div class="status-profit-container" id="status-profit-desktop-${idx}">
                            <!-- Dynamic profit pill rendered in updateHargaRow -->
                        </div>
                    </td>
                </tr>
            `);

            // Mobile Card
            $mobileCards.append(`
                <div class="mobile-price-card" id="mobile-card-${idx}">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <span class="fs-5 fw-bold text-dark">${row.sat_id}</span>
                            ${badgeBase}
                        </div>
                        <span class="badge bg-light text-dark border">Isi: ${qtyKonversi} Satuan Dasar</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted mb-1">HARGA POKOK (MODAL BELI):</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">Rp</span>
                            <input type="text" class="form-control form-control-lg money harga-pokok" data-idx="${idx}" inputmode="numeric" value="${valHp}" placeholder="0" aria-label="Harga Pokok Mobile ${row.sat_id}">
                        </div>
                        <div class="price-preview-text hp-preview">Terbaca: Rp ${valHp}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted mb-1">HARGA JUAL (KONSUMEN TOKO):</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">Rp</span>
                            <input type="text" class="form-control form-control-lg money harga-jual" data-idx="${idx}" inputmode="numeric" value="${valHj}" placeholder="0" aria-label="Harga Jual Mobile ${row.sat_id}">
                        </div>
                        <div class="price-preview-text hj-preview">Terbaca: Rp ${valHj}</div>
                    </div>

                    <div class="mt-2 pt-2 border-top">
                        <div class="status-profit-container" id="status-profit-mobile-${idx}"></div>
                    </div>
                </div>
            `);

            updateHargaRow(idx);
        });

        applyMoneyMask('#table-harga');
        applyMoneyMask('#mobile-harga-cards');
    }

    function calculateProfitStatus(hp, hj) {
        if (hp === 0 || hj === 0) {
            return {
                type: 'empty',
                margin: '0.0',
                profit: 0,
                pillClass: 'bg-warning-subtle text-warning-emphasis border border-warning',
                icon: 'ti-alert-triangle',
                text: 'Harga belum lengkap (Rp 0)'
            };
        }

        const profit = hj - hp;
        const margin = (((hj - hp) / hp) * 100).toFixed(1);

        if (profit < 0) {
            return {
                type: 'loss',
                margin: margin,
                profit: profit,
                pillClass: 'bg-danger text-white',
                icon: 'ti-alert-circle',
                text: `RUGI Rp ${formatMoneyValue(Math.abs(profit))} (${margin}%)`
            };
        } else if (profit === 0) {
            return {
                type: 'breakeven',
                margin: '0.0',
                profit: 0,
                pillClass: 'bg-secondary text-white',
                icon: 'ti-arrows-diff',
                text: 'Impas (Margin 0%)'
            };
        } else {
            return {
                type: 'profit',
                margin: margin,
                profit: profit,
                pillClass: 'bg-success text-white',
                icon: 'ti-trending-up',
                text: `Untung +Rp ${formatMoneyValue(profit)} (+${margin}%)`
            };
        }
    }

    function updateHargaRow(idx) {
        // Cari input dari desktop atau mobile (keduanya sinkron)
        const desktopInputHp = $(`#table-harga input.harga-pokok[data-idx="${idx}"]`);
        const mobileInputHp = $(`#mobile-harga-cards input.harga-pokok[data-idx="${idx}"]`);
        const desktopInputHj = $(`#table-harga input.harga-jual[data-idx="${idx}"]`);
        const mobileInputHj = $(`#mobile-harga-cards input.harga-jual[data-idx="${idx}"]`);

        const rawHp = desktopInputHp.val() || mobileInputHp.val() || '0';
        const rawHj = desktopInputHj.val() || mobileInputHj.val() || '0';

        const hp = Number(normalizeMoneyValue(rawHp));
        const hj = Number(normalizeMoneyValue(rawHj));

        storeRows[idx].harga_pokok = hp;
        storeRows[idx].harga_jual = hj;

        const formattedHp = formatMoneyValue(hp);
        const formattedHj = formatMoneyValue(hj);

        // Update preview text
        $(`tr[data-row-idx="${idx}"] .hp-preview, #mobile-card-${idx} .hp-preview`).text(`Terbaca: Rp ${formattedHp}`);
        $(`tr[data-row-idx="${idx}"] .hj-preview, #mobile-card-${idx} .hj-preview`).text(`Terbaca: Rp ${formattedHj}`);

        const status = calculateProfitStatus(hp, hj);
        const htmlBadge = `<span class="profit-pill ${status.pillClass}"><i class="ti ${status.icon}"></i> ${status.text}</span>`;

        $(`#status-profit-desktop-${idx}`).html(htmlBadge);
        $(`#status-profit-mobile-${idx}`).html(htmlBadge);

        // Highlighting error visual
        const $desktopRow = $(`tr[data-row-idx="${idx}"]`);
        const $mobileCard = $(`#mobile-card-${idx}`);

        if (status.type === 'loss') {
            $desktopRow.addClass('table-danger');
            $mobileCard.addClass('has-error').removeClass('has-profit');
            $(`input[data-idx="${idx}"]`).addClass('is-invalid');
        } else {
            $desktopRow.removeClass('table-danger');
            $mobileCard.removeClass('has-error');
            $(`input[data-idx="${idx}"]`).removeClass('is-invalid');

            if (status.type === 'profit') {
                $mobileCard.addClass('has-profit');
            } else {
                $mobileCard.removeClass('has-profit');
            }
        }
    }

    // Sync input antar desktop table dan mobile card ketika user mengetik
    $(document).on('input', 'input.harga-pokok, input.harga-jual', function() {
        const idx = Number($(this).data('idx'));
        const isPokok = $(this).hasClass('harga-pokok');
        const currentVal = $(this).val();

        if (isPokok) {
            $(`input.harga-pokok[data-idx="${idx}"]`).not(this).val(currentVal);
        } else {
            $(`input.harga-jual[data-idx="${idx}"]`).not(this).val(currentVal);
        }

        updateHargaRow(idx);
    });

    $(document).on('blur', 'input.harga-pokok, input.harga-jual', function() {
        const idx = Number($(this).data('idx'));
        updateHargaRow(idx);
        validatePricesLive();

        // Jika user mengubah satuan dasar (qty = 1), auto-fill satuan yang lebih besar jika masih kosong
        const satId = storeRows[idx]?.sat_id || '';
        const sat = satuanRows.find(row => row.sat_id === satId);
        if (Number(sat?.qty_konversi || 0) === 1) {
            autoFillByBase();
        }
    });

    function autoFillByBase() {
        const qtyBySat = {};
        satuanRows.forEach(row => qtyBySat[row.sat_id] = Number(row.qty_konversi || 0));
        const baseRow = storeRows.find(row => (qtyBySat[row.sat_id] || 0) === 1);
        if (!baseRow) return;

        const basePokok = Number(baseRow.harga_pokok || 0);
        const baseJual = Number(baseRow.harga_jual || 0);

        if (basePokok === 0 && baseJual === 0) return;

        storeRows.forEach((row, idx) => {
            const qty = qtyBySat[row.sat_id] || 0;
            if (qty <= 0 || qty === 1) return;

            if (Number(row.harga_pokok) === 0 && basePokok > 0) {
                row.harga_pokok = Math.round(basePokok * qty);
                $(`input.harga-pokok[data-idx="${idx}"]`).val(formatMoneyValue(row.harga_pokok));
            }

            if (Number(row.harga_jual) === 0 && baseJual > 0) {
                row.harga_jual = Math.round(baseJual * qty);
                $(`input.harga-jual[data-idx="${idx}"]`).val(formatMoneyValue(row.harga_jual));
            }

            updateHargaRow(idx);
        });
        validatePricesLive();
    }

    function manualTriggerAutoFill() {
        const qtyBySat = {};
        satuanRows.forEach(row => qtyBySat[row.sat_id] = Number(row.qty_konversi || 0));
        const baseRow = storeRows.find(row => (qtyBySat[row.sat_id] || 0) === 1);

        if (!baseRow || (Number(baseRow.harga_pokok) === 0 && Number(baseRow.harga_jual) === 0)) {
            toastr.warning('Isi Harga Pokok dan Harga Jual pada Satuan Dasar (Qty = 1) terlebih dahulu.');
            return;
        }

        const basePokok = Number(baseRow.harga_pokok || 0);
        const baseJual = Number(baseRow.harga_jual || 0);

        storeRows.forEach((row, idx) => {
            const qty = qtyBySat[row.sat_id] || 0;
            if (qty === 1) return;

            if (basePokok > 0) {
                row.harga_pokok = Math.round(basePokok * qty);
                $(`input.harga-pokok[data-idx="${idx}"]`).val(formatMoneyValue(row.harga_pokok));
            }
            if (baseJual > 0) {
                row.harga_jual = Math.round(baseJual * qty);
                $(`input.harga-jual[data-idx="${idx}"]`).val(formatMoneyValue(row.harga_jual));
            }
            updateHargaRow(idx);
        });

        validatePricesLive();
        toastr.success('Harga satuan besar berhasil dihitung otomatis proporsional dari satuan dasar.');
    }

    // Live Validation Guard: Cek apakah ada harga yang rugi atau kosong
    function validatePricesLive() {
        const errors = [];

        storeRows.forEach(row => {
            const hp = Number(row.harga_pokok || 0);
            const hj = Number(row.harga_jual || 0);

            if (hp === 0 || hj === 0) {
                errors.push(`Satuan <strong>${row.sat_id}</strong>: Harga belum lengkap (masih Rp 0).`);
            } else if (hj < hp) {
                errors.push(`Satuan <strong>${row.sat_id}</strong>: Harga Jual (Rp ${formatMoneyValue(hj)}) lebih murah dari Modal (Rp ${formatMoneyValue(hp)}) - <strong>RUGI!</strong>`);
            }
        });

        const $alert = $('#harga-error-alert');
        if (errors.length > 0) {
            $('#harga-error-list').html(`<ul class="mb-0 ps-3">${errors.map(e => `<li>${e}</li>`).join('')}</ul>`);
            $alert.removeClass('d-none');
            return false;
        } else {
            $alert.addClass('d-none');
            return true;
        }
    }

    // ========================================================
    // FORM SUBMISSION & BACKEND NOTIFICATION
    // ========================================================
    $('#item-form').on('submit', function(e) {
        e.preventDefault();

        // Jegatan Step 1
        if (!validateStep1()) {
            activeStep = 1;
            updateStepper();
            return;
        }

        // Jegatan Step 2
        if (!validateStep2()) {
            activeStep = 2;
            updateStepper();
            return;
        }

        // Jegatan Step 3
        if (storeRows.length === 0) {
            toastr.error('Daftar harga toko kosong!');
            return;
        }

        normalizeMoneyInputs('#table-harga');
        normalizeMoneyInputs('#mobile-harga-cards');

        storeRows = storeRows.map(row => ({
            ...row,
            harga_pokok: Number(normalizeMoneyValue(row.harga_pokok)),
            harga_jual: Number(normalizeMoneyValue(row.harga_jual))
        }));

        for (const r of storeRows) {
            if (r.harga_jual == 0 || r.harga_pokok == 0) {
                toastr.error(`Harga Jual atau Harga Pokok untuk satuan ${r.sat_id} tidak boleh kosong / Rp 0`);
                activeStep = 3;
                updateStepper();
                validatePricesLive();
                return;
            }
            if (r.harga_jual < r.harga_pokok) {
                toastr.error(`Harga Jual untuk satuan ${r.sat_id} tidak boleh lebih kecil dari harga modal (RUGI)!`);
                activeStep = 3;
                updateStepper();
                validatePricesLive();
                return;
            }
        }

        const status = $('#status_item').is(':checked') ? 'Y' : 'N';
        $('#satuan_json').val(JSON.stringify(satuanRows));
        $('#store_json').val(JSON.stringify(storeRows));

        const formData = $(this).serializeArray();
        formData.push({
            name: 'status_item',
            value: status
        });

        const $btnSave = $('#btn-save');
        const origBtnHtml = $btnSave.html();
        $btnSave.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.ajax({
            type: 'POST',
            url: '<?= base_url('/item') ?>',
            dataType: 'json',
            data: formData,
            success: function(res) {
                $btnSave.prop('disabled', false).html(origBtnHtml);
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Data barang berhasil disimpan');
                    notifyItemUpdated();
                    setTimeout(function() {
                        handleItemBack();
                    }, 500);
                } else {
                    toastr.error(res.data || 'Gagal menyimpan data barang');
                }
            },
            error: function(xhr) {
                $btnSave.prop('disabled', false).html(origBtnHtml);
                toastr.error(extractErrorMessage(xhr, 'Terjadi kesalahan saat simpan data'));
            }
        });
    });

    function notifyItemUpdated() {
        const timestamp = new Date().getTime().toString();
        try {
            localStorage.setItem('zulfa_item_updated_at', timestamp);
        } catch (e) {}

        try {
            if (typeof BroadcastChannel !== 'undefined') {
                const ch = new BroadcastChannel('zulfa_item_channel');
                ch.postMessage({
                    action: 'reload_item_table',
                    timestamp: timestamp
                });
                ch.close();
            }
        } catch (e) {}

        try {
            if (window.opener && !window.opener.closed) {
                if (window.opener.$ && window.opener.$('#table-data').length) {
                    window.opener.$('#table-data').DataTable().ajax.reload(null, false);
                }
            }
        } catch (e) {}
    }

    function handleItemBack() {
        if (window.opener && !window.opener.closed) {
            window.close();
        } else if (window.history.length > 1) {
            window.location.href = '<?= base_url('/item') ?>';
        } else {
            window.close();
        }
    }
</script>
<?= $this->endSection('javascript') ?>