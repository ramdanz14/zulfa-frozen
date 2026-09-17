<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $settings
 * @var array $logos
 */
$aksesMenuParsed = is_string($akses_menu ?? null) ? (json_decode($akses_menu, true) ?: []) : ($akses_menu ?? []);
$canUpdateUser = (!empty($aksesMenuParsed['akses_update']) && $aksesMenuParsed['akses_update'] === 'Y');
?>

<style>
    /* Styling & Anti-Slop Human / Mobile Optimizations */
    :root {
        --color-focus-ring: #0284c7;
        --color-card-border: #e2e8f0;
        --color-text-main: #0f172a;
        --color-text-muted: #475569;
    }

    /* Focus indicator WCAG AA / AAA */
    .form-control:focus,
    .btn:focus-visible,
    .nav-pills .nav-link:focus-visible {
        outline: 2px solid var(--color-focus-ring) !important;
        outline-offset: 2px !important;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2) !important;
    }

    /* Touch target >= 44px */
    .btn,
    .form-control,
    .input-group-text,
    .nav-pills .nav-link {
        min-height: 44px !important;
    }

    /* Card setting item */
    .setting-item-card {
        background: #ffffff;
        border: 1px solid var(--color-card-border);
        border-radius: 10px;
        padding: 16px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .setting-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .setting-item-card.has-warning {
        border-color: #f59e0b;
        background-color: #fffdfa;
    }

    .setting-item-card.has-error {
        border-color: #ef4444;
        background-color: #fffafa;
    }

    /* High contrast guidance banner (WCAG AAA) */
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
        margin-bottom: 14px;
    }

    .guardrail-warning {
        background-color: #fffbeb;
        border: 1px solid #fde68a;
        border-left: 5px solid #d97706;
        color: #78350f;
    }

    .guardrail-danger {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        border-left: 5px solid #dc2626;
        color: #7f1d1d;
    }

    .guardrail-success {
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-left: 5px solid #16a34a;
        color: #14532d;
    }

    /* Simulation Preview Box */
    .simulasi-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        margin-top: 10px;
        font-size: 0.85rem;
        color: #334155;
    }

    .simulasi-box strong {
        color: #0f172a;
    }

    /* Quick Preset Chips */
    .preset-chips-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .preset-chip {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
        min-height: 28px;
        display: inline-flex;
        align-items: center;
    }

    .preset-chip:hover {
        background: #e2e8f0;
        color: #0f172a;
        border-color: #94a3b8;
    }

    .preset-chip:focus-visible {
        outline: 2px solid var(--color-focus-ring);
    }

    /* Transparent Logo Preview Grid Background */
    .checkerboard-bg {
        background-color: #f8fafc;
        background-image: linear-gradient(45deg, #e2e8f0 25%, transparent 25%),
            linear-gradient(-45deg, #e2e8f0 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #e2e8f0 75%),
            linear-gradient(-45deg, transparent 75%, #e2e8f0 75%);
        background-size: 16px 16px;
        background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100px;
    }

    /* Live Upload Preview Box */
    .file-preview-box {
        background: #ffffff;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 12px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Mobile Segmented Navigation */
    .mobile-tab-nav {
        display: none;
        gap: 8px;
        padding: 4px;
        background: #f1f5f9;
        border-radius: 10px;
        margin-bottom: 18px;
    }

    .mobile-tab-nav .nav-link {
        flex: 1;
        text-align: center;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 10px 14px;
        border-radius: 8px;
        color: #475569;
        border: 1px solid transparent;
        background: transparent;
    }

    .mobile-tab-nav .nav-link.active {
        background: #ffffff;
        color: #0369a1;
        border-color: #bae6fd;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    /* Tag badges for gramasi */
    .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
        border-radius: 16px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .mobile-tab-nav {
            display: flex;
        }

        .tab-section-content {
            display: none;
        }

        .tab-section-content.active-mobile-section {
            display: block;
        }

        .bottom-sticky-bar {
            position: sticky;
            bottom: 0;
            background: #ffffff;
            padding: 12px 0;
            border-top: 1px solid #e2e8f0;
            z-index: 100;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
        }
    }

    @media (min-width: 992px) {
        .tab-section-content {
            display: block !important;
        }
    }
</style>

<div class="body-wrapper pb-5">
    <div class="container-fluid p-0">
        <!-- Header Banner -->
        <div class="card bg-secondary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-8 col-md-9">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary rounded-circle p-2 text-white">
                                <i class="ti ti-adjustments-alt fs-5"></i>
                            </span>
                            <div>
                                <h4 class="fw-semibold mb-0 fs-5">Setting Data Aplikasi</h4>
                                <small class="text-muted d-block">Pengaturan rumus sistem, konstanta global, dan logo aplikasi.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 col-md-3 text-end">
                        <span class="badge bg-light text-dark border px-2 py-1">
                            <i class="ti ti-link"></i> <span class="d-none d-sm-inline">URL</span> /setting-data
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Segmented Tab Navigation -->
        <ul class="nav mobile-tab-nav" id="settingMobileTabs" role="tablist">
            <li class="nav-item flex-fill">
                <button class="nav-link active" type="button" data-target="#section-konstanta">
                    <i class="ti ti-adjustments me-1"></i> Konstanta (5)
                </button>
            </li>
            <li class="nav-item flex-fill">
                <button class="nav-link" type="button" data-target="#section-logo">
                    <i class="ti ti-photo me-1"></i> Logo Aplikasi (2)
                </button>
            </li>
        </ul>

        <div class="row g-3">
            <!-- ======================================================== -->
            <!-- KOLOM KIRI: KONSTANTA APLIKASI                           -->
            <!-- ======================================================== -->
            <div class="col-12 col-lg-7 tab-section-content active-mobile-section" id="section-konstanta">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h5 class="fw-semibold mb-1 text-dark">
                                    <i class="ti ti-adjustments text-primary me-1"></i> Konstanta Global Sistem
                                </h5>
                                <small class="text-muted">
                                    Nilai tersimpan di tabel <code>const</code> dan langsung mempengaruhi kalkulasi modul terkait.
                                </small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 d-none d-sm-inline-flex align-items-center gap-1">
                                <i class="ti ti-server"></i> Database System
                            </span>
                        </div>

                        <!-- Banner Petunjuk Pengisian -->
                        <div class="guide-banner">
                            <h6><i class="ti ti-bulb fs-5"></i> Panduan untuk Newbie:</h6>
                            <ul>
                                <li>Setiap angka memiliki <strong>simulasi otomatis</strong> di bawah kolom input agar Anda bisa melihat dampak perubahannya sebelum disimpan.</li>
                                <li>Pastikan tidak mengisi angka <strong>0 atau negatif</strong> pada perhitungan yang menjadi pembagi (seperti Nominal per Poin).</li>
                                <li>Klik tombol <strong>Preset Cepat</strong> untuk mengisi nilai standar umum dengan satu sentuhan.</li>
                            </ul>
                        </div>

                        <!-- Guardrail Alert jika ada input error -->
                        <div class="guardrail-alert guardrail-danger d-none" id="setting-error-alert">
                            <i class="ti ti-alert-circle fs-5 shrink-0 mt-1"></i>
                            <div>
                                <strong>Peringatan Kesalahan Input:</strong>
                                <div id="setting-error-msg" class="mt-1"></div>
                            </div>
                        </div>

                        <form id="form-setting-data" novalidate>
                            <div class="row g-3">
                                <?php foreach ($settings as $setting) : ?>
                                    <?php
                                    $rkey = (string) ($setting['rkey'] ?? '');
                                    $isNumber = ($setting['type'] ?? '') === 'number';
                                    $val = (string) ($setting['nilai'] ?? '');

                                    // Icon per setting
                                    $icon = 'ti-settings';
                                    if ($rkey === 'nominal_per_poin') $icon = 'ti-coin text-warning';
                                    elseif ($rkey === 'satuan_gramasi') $icon = 'ti-scale text-info';
                                    elseif ($rkey === 'batas_retur_jual') $icon = 'ti-calendar-time text-danger';
                                    elseif ($rkey === 'markup_gudang') $icon = 'ti-building-warehouse text-primary';
                                    elseif ($rkey === 'min_margin_grosir') $icon = 'ti-truck-delivery text-success';
                                    ?>
                                    <div class="col-12">
                                        <div class="setting-item-card" id="card-<?= esc($rkey) ?>">
                                            <div class="row g-3 align-items-start">
                                                <!-- Deskripsi Setting -->
                                                <div class="col-12 col-md-5">
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <i class="ti <?= $icon ?> fs-5"></i>
                                                        <span class="fw-bold text-dark fs-6"><?= esc($setting['label'] ?? $rkey) ?></span>
                                                    </div>
                                                    <div>
                                                        <code class="text-secondary"><?= esc($rkey) ?></code>
                                                    </div>
                                                    <div class="small text-muted mt-2" style="line-height: 1.4;">
                                                        <?= esc($setting['description'] ?? '-') ?>
                                                    </div>
                                                </div>

                                                <!-- Input Field & Interaktivitas -->
                                                <div class="col-12 col-md-7">
                                                    <label class="form-label fw-bold mb-1" for="input-<?= esc($rkey) ?>">
                                                        Nilai Pengaturan <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <?php if ($rkey === 'nominal_per_poin') : ?>
                                                            <span class="input-group-text fw-bold">Rp</span>
                                                        <?php endif; ?>

                                                        <input type="<?= $isNumber ? 'number' : 'text' ?>"
                                                            class="form-control form-control-lg setting-input <?= $isNumber ? 'setting-number' : '' ?>"
                                                            id="input-<?= esc($rkey) ?>"
                                                            name="<?= esc($rkey) ?>"
                                                            data-key="<?= esc($rkey) ?>"
                                                            value="<?= esc($val) ?>"
                                                            <?= $isNumber ? 'min="0" step="any" inputmode="decimal"' : '' ?>
                                                            required
                                                            placeholder="Ketik nilai...">

                                                        <span class="input-group-text fw-semibold text-secondary">
                                                            <?= esc($setting['suffix'] ?? '') ?>
                                                        </span>
                                                    </div>

                                                    <!-- Preset Chips & Helpers Khusus -->
                                                    <?php if ($rkey === 'nominal_per_poin') : ?>
                                                        <div class="preset-chips-container">
                                                            <span class="small text-muted me-1 align-self-center">Pilihan cepat:</span>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('nominal_per_poin', '500')">Rp 500</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('nominal_per_poin', '1000')">Rp 1.000 (Standar)</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('nominal_per_poin', '2000')">Rp 2.000</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('nominal_per_poin', '5000')">Rp 5.000</button>
                                                        </div>
                                                        <div class="simulasi-box" id="sim-nominal_per_poin">
                                                            <!-- Simulasi JS -->
                                                        </div>

                                                    <?php elseif ($rkey === 'satuan_gramasi') : ?>
                                                        <div class="preset-chips-container">
                                                            <span class="small text-muted me-1 align-self-center">Tambah satuan:</span>
                                                            <button type="button" class="preset-chip" onclick="appendGramasi('ONS')">+ ONS</button>
                                                            <button type="button" class="preset-chip" onclick="appendGramasi('KG')">+ KG</button>
                                                            <button type="button" class="preset-chip" onclick="appendGramasi('LTR')">+ LTR</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('satuan_gramasi', 'GR;GRAM;ML')">Reset Standar</button>
                                                        </div>
                                                        <div class="simulasi-box" id="sim-satuan_gramasi">
                                                            <!-- Simulasi JS -->
                                                        </div>

                                                    <?php elseif ($rkey === 'batas_retur_jual') : ?>
                                                        <div class="preset-chips-container">
                                                            <span class="small text-muted me-1 align-self-center">Pilihan cepat:</span>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('batas_retur_jual', '3')">3 Hari</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('batas_retur_jual', '7')">7 Hari (1 Minggu)</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('batas_retur_jual', '14')">14 Hari (2 Minggu)</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('batas_retur_jual', '30')">30 Hari (1 Bulan)</button>
                                                        </div>
                                                        <div class="simulasi-box" id="sim-batas_retur_jual">
                                                            <!-- Simulasi JS -->
                                                        </div>

                                                    <?php elseif ($rkey === 'markup_gudang') : ?>
                                                        <div class="preset-chips-container">
                                                            <span class="small text-muted me-1 align-self-center">Pilihan cepat:</span>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('markup_gudang', '0')">0% (Harga Pokok)</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('markup_gudang', '2')">2% (Standar)</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('markup_gudang', '3')">3%</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('markup_gudang', '5')">5%</button>
                                                        </div>
                                                        <div class="simulasi-box" id="sim-markup_gudang">
                                                            <!-- Simulasi JS -->
                                                        </div>

                                                    <?php elseif ($rkey === 'min_margin_grosir') : ?>
                                                        <div class="preset-chips-container">
                                                            <span class="small text-muted me-1 align-self-center">Pilihan cepat:</span>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('min_margin_grosir', '1')">1%</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('min_margin_grosir', '2')">2% (Standar)</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('min_margin_grosir', '3')">3%</button>
                                                            <button type="button" class="preset-chip" onclick="applyPreset('min_margin_grosir', '5')">5%</button>
                                                        </div>
                                                        <div class="simulasi-box" id="sim-min_margin_grosir">
                                                            <!-- Simulasi JS -->
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Action Button Simpan Konstanta -->
                            <div class="d-flex justify-content-end mt-4 pt-3 border-top bottom-sticky-bar">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold" id="btn-save-setting">
                                    <i class="ti ti-device-floppy me-1"></i> Simpan Setting Konstanta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- KOLOM KANAN: LOGO APLIKASI                               -->
            <!-- ======================================================== -->
            <div class="col-12 col-lg-5 tab-section-content" id="section-logo">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h5 class="fw-semibold mb-1 text-dark">
                                    <i class="ti ti-photo-up text-primary me-1"></i> Logo & Dokumen
                                </h5>
                                <small class="text-muted">Logo resmi aplikasi untuk web, nota transaksi, dan cetak struk thermal.</small>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 d-none d-sm-inline-flex align-items-center gap-1">
                                <i class="ti ti-file-type-png"></i> PNG Only
                            </span>
                        </div>

                        <!-- Petunjuk Khusus Logo -->
                        <div class="guide-banner" style="background-color: #f0fdf4; border-color: #16a34a; color: #14532d;">
                            <h6 style="color: #14532d;"><i class="ti ti-info-circle fs-5"></i> Panduan Logo Kasir & Struk:</h6>
                            <ul>
                                <li><strong>Logo Colour</strong>: Logo berwarna untuk tampilan web, kop faktur PDF, dan laporan.</li>
                                <li><strong>Logo BW (Hitam Putih)</strong>: Khusus printer struk thermal kasir. Wajib monokrom kontras tinggi agar tidak bergaris/buram saat dicetak.</li>
                                <li>Format file <strong>Wajib PNG (.png)</strong>. Gambar transparan disarankan.</li>
                            </ul>
                        </div>

                        <!-- Tampilan Logo Saat Ini -->
                        <div class="row g-3 mb-4">
                            <?php foreach ($logos as $key => $logo) : ?>
                                <div class="col-12 col-sm-6 col-lg-12 col-xl-6">
                                    <div class="border rounded-3 p-3 h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-dark small"><?= esc($logo['label'] ?? $key) ?></span>
                                            <?php if (!empty($logo['exists'])) : ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle py-1">
                                                    <i class="ti ti-check"></i> Ada File
                                                </span>
                                            <?php else : ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1">
                                                    <i class="ti ti-x"></i> Belum Ada
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="checkerboard-bg mb-2">
                                            <?php if (!empty($logo['exists'])) : ?>
                                                <img src="<?= esc($logo['url']) ?>" alt="<?= esc($logo['label']) ?>" class="img-fluid" style="max-height: 72px; object-fit: contain;">
                                            <?php else : ?>
                                                <div class="text-muted small py-3 text-center">
                                                    <i class="ti ti-photo-off fs-5 d-block mb-1"></i>Belum ada file
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="small">
                                            <code class="text-secondary d-block mb-1"><?= esc($logo['filename'] ?? '-') ?></code>
                                            <div class="text-muted" style="font-size: 0.75rem; line-height: 1.3;"><?= esc($logo['description'] ?? '-') ?></div>
                                            <div class="text-muted mt-2" style="font-size: 0.75rem;">
                                                <i class="ti ti-clock"></i> Update: <strong><?= esc($logo['updated_at'] ?? 'Belum pernah') ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Form Upload Logo Baru dengan Live Preview & Jegatan -->
                        <div class="card bg-light border-0 p-3">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="ti ti-upload text-primary me-1"></i> Upload & Ganti Logo
                            </h6>

                            <form id="form-upload-logo" enctype="multipart/form-data" novalidate>
                                <!-- Upload Logo Color -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="logo_color">
                                        1. Upload Logo Colour (Berwarna)
                                        <span class="badge bg-light text-muted border ms-1">PNG</span>
                                    </label>
                                    <input type="file" class="form-control logo-file-input" name="logo_color" id="logo_color" accept="image/png" data-preview="#preview-color-box">
                                    <small class="text-muted d-block mt-1">Mengganti target: <code>zulfa-logo-color.png</code></small>

                                    <!-- Live Preview Color -->
                                    <div id="preview-color-box" class="file-preview-box d-none">
                                        <img src="" alt="Preview Color" class="preview-img" style="max-height: 50px; max-width: 80px; object-fit: contain;">
                                        <div class="grow small">
                                            <strong class="file-name d-block text-truncate"></strong>
                                            <span class="file-info text-muted"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="clearLogoInput('logo_color')" title="Batal pilih file">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Upload Logo BW -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="logo_bw">
                                        2. Upload Logo BW (Struk Thermal)
                                        <span class="badge bg-light text-muted border ms-1">PNG</span>
                                    </label>
                                    <input type="file" class="form-control logo-file-input" name="logo_bw" id="logo_bw" accept="image/png" data-preview="#preview-bw-box">
                                    <small class="text-muted d-block mt-1">Mengganti target: <code>zulfa-logo-bw.png</code></small>

                                    <!-- Live Preview BW -->
                                    <div id="preview-bw-box" class="file-preview-box d-none">
                                        <img src="" alt="Preview BW" class="preview-img" style="max-height: 50px; max-width: 80px; object-fit: contain;">
                                        <div class="grow small">
                                            <strong class="file-name d-block text-truncate"></strong>
                                            <span class="file-info text-muted"></span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="clearLogoInput('logo_bw')" title="Batal pilih file">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-semibold" id="btn-upload-logo">
                                    <i class="ti ti-upload me-1"></i> Upload & Terapkan Logo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const canUpdate = <?= $canUpdateUser ? 'true' : 'false' ?>;

    $(function() {
        // Akses kontrol check
        $('#btn-save-setting, #btn-upload-logo').prop('disabled', !canUpdate);
        if (!canUpdate) {
            $('#form-setting-data :input, #form-upload-logo :input').prop('disabled', true);
        }

        // Jalankan kalkulasi simulasi awal untuk semua input
        $('.setting-input').each(function() {
            triggerSimulation($(this).data('key'), $(this).val());
        });

        // Event listener saat user mengetik
        $('.setting-input').on('input change', function() {
            const key = $(this).data('key');
            const val = $(this).val();
            triggerSimulation(key, val);
            validateFieldLive($(this));
        });

        // Mobile Segmented Navigation Tab Switcher
        $('#settingMobileTabs button').on('click', function() {
            $('#settingMobileTabs button').removeClass('active');
            $(this).addClass('active');

            const target = $(this).data('target');
            $('.tab-section-content').removeClass('active-mobile-section');
            $(target).addClass('active-mobile-section');

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Live File Upload Preview & Jegatan Format File
        $('.logo-file-input').on('change', function() {
            const file = this.files[0];
            const previewTarget = $(this).data('preview');
            const $box = $(previewTarget);

            if (!file) {
                $box.addClass('d-none');
                return;
            }

            // Validasi Jegatan: Wajib file PNG
            if (file.type !== 'image/png' && !file.name.toLowerCase().endsWith('.png')) {
                toastr.error(`File "${file.name}" bukan format PNG! Wajib menggunakan file berekstensi .png.`);
                $(this).val('');
                $box.addClass('d-none');
                return;
            }

            // Validasi Ukuran File: Warning jika > 2MB
            const sizeKB = Math.round(file.size / 1024);
            if (sizeKB > 2048) {
                toastr.warning(`Ukuran file (${(sizeKB / 1024).toFixed(1)} MB) cukup besar. Disarankan di bawah 1 MB.`);
            }

            // Render Preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $box.find('.preview-img').attr('src', e.target.result);
                $box.find('.file-name').text(file.name);
                $box.find('.file-info').html(`<span class="badge bg-success-subtle text-success me-1">PNG Valid</span> ${sizeKB} KB`);
                $box.removeClass('d-none');
            };
            reader.readAsDataURL(file);
        });
    });

    // ========================================================
    // PRESET BUTTONS & SIMULASI KALKULASI REAL-TIME
    // ========================================================
    function applyPreset(key, val) {
        const $input = $(`#input-${key}`);
        $input.val(val).trigger('input').focus();
    }

    function appendGramasi(unit) {
        const $input = $('#input-satuan_gramasi');
        let current = ($input.val() || '').trim();
        const parts = current ? current.split(';').map(s => s.trim().toUpperCase()) : [];

        if (!parts.includes(unit)) {
            parts.push(unit);
            $input.val(parts.join(';')).trigger('input').focus();
            toastr.info(`Satuan ${unit} ditambahkan ke daftar gramasi.`);
        } else {
            toastr.warning(`Satuan ${unit} sudah ada dalam daftar.`);
        }
    }

    function triggerSimulation(key, rawVal) {
        const val = String(rawVal ?? '').trim();
        const $card = $(`#card-${key}`);

        if (key === 'nominal_per_poin') {
            const num = Number(val);
            const $box = $('#sim-nominal_per_poin');
            if (isNaN(num) || num <= 0) {
                $card.addClass('has-error').removeClass('has-warning');
                $box.html(`<span class="text-danger fw-bold"><i class="ti ti-alert-triangle"></i> Peringatan:</span> Nominal per poin harus lebih dari Rp 0 agar poin member dapat dihitung.`);
            } else {
                $card.removeClass('has-error has-warning');
                const sampleBelanja = 100000;
                const earnedPoin = Math.floor(sampleBelanja / num);
                $box.html(`
                    <i class="ti ti-bulb text-warning me-1"></i>
                    <strong>Simulasi Belanja Rp 100.000:</strong> Pelanggan akan mendapatkan 
                    <span class="badge bg-success text-white fs-6 px-2">${earnedPoin} Poin</span> 
                    <small class="text-muted">(Rp 100.000 / Rp ${formatMoney(num)})</small>
                `);
            }
        } else if (key === 'satuan_gramasi') {
            const $box = $('#sim-satuan_gramasi');
            const rawParts = val ? val.split(/[;,]+/).map(s => s.trim().toUpperCase()).filter(Boolean) : [];
            const uniqueParts = [...new Set(rawParts)];

            if (uniqueParts.length === 0) {
                $card.addClass('has-error').removeClass('has-warning');
                $box.html(`<span class="text-danger fw-bold"><i class="ti ti-alert-triangle"></i> Peringatan:</span> Wajib mengisi minimal satu satuan gramasi (misal: GR;GRAM;ML).`);
            } else {
                $card.removeClass('has-error has-warning');
                const tagsHtml = uniqueParts.map(u => `<span class="tag-badge"><i class="ti ti-check"></i> ${u}</span>`).join(' ');
                $box.html(`
                    <div class="mb-1"><i class="ti ti-tags text-info me-1"></i> <strong>${uniqueParts.length} Satuan Aktif:</strong></div>
                    <div class="d-flex flex-wrap gap-1">${tagsHtml}</div>
                    <div class="text-muted small mt-1">Satuan di atas tidak dibulatkan ke kelipatan 50 saat transfer gudang.</div>
                `);
            }
        } else if (key === 'batas_retur_jual') {
            const days = Number(val);
            const $box = $('#sim-batas_retur_jual');
            if (isNaN(days) || days < 0) {
                $card.addClass('has-error').removeClass('has-warning');
                $box.html(`<span class="text-danger fw-bold"><i class="ti ti-alert-triangle"></i> Peringatan:</span> Batas hari retur tidak boleh negatif.`);
            } else if (days === 0) {
                $card.addClass('has-warning').removeClass('has-error');
                $box.html(`<span class="text-warning-emphasis fw-bold"><i class="ti ti-alert-circle"></i> Info:</span> Batas 0 hari berarti retur hanya diperbolehkan pada hari yang sama dengan pembelian.`);
            } else {
                $card.removeClass('has-error has-warning');
                const targetDate = new Date();
                targetDate.setDate(targetDate.getDate() - days);
                const tglStr = targetDate.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                $box.html(`
                    <i class="ti ti-calendar text-danger me-1"></i>
                    <strong>Simulasi Transaksi Hari Ini:</strong> Struk pembelian paling lama yang masih bisa diretur adalah tanggal 
                    <span class="badge bg-light text-dark border fw-bold">${tglStr}</span> 
                    <small class="text-muted">(${days} hari terakhir)</small>.
                `);
            }
        } else if (key === 'markup_gudang') {
            const markup = Number(val);
            const $box = $('#sim-markup_gudang');
            if (isNaN(markup) || markup < 0) {
                $card.addClass('has-error').removeClass('has-warning');
                $box.html(`<span class="text-danger fw-bold"><i class="ti ti-alert-triangle"></i> Peringatan:</span> Persentase markup tidak boleh minus.`);
            } else {
                $card.removeClass('has-error has-warning');
                const modal = 10000;
                const transferPrice = Math.round(modal * (1 + (markup / 100)));
                const laba = transferPrice - modal;
                $box.html(`
                    <i class="ti ti-calculator text-primary me-1"></i>
                    <strong>Simulasi Modal Rp 10.000:</strong> Harga transfer dari gudang ke toko menjadi 
                    <span class="badge bg-primary text-white fs-6 px-2">Rp ${formatMoney(transferPrice)}</span> 
                    <small class="text-muted">(Laba gudang: +Rp ${formatMoney(laba)} / +${markup}%)</small>.
                `);
            }
        } else if (key === 'min_margin_grosir') {
            const margin = Number(val);
            const $box = $('#sim-min_margin_grosir');
            if (isNaN(margin) || margin < 0) {
                $card.addClass('has-error').removeClass('has-warning');
                $box.html(`<span class="text-danger fw-bold"><i class="ti ti-alert-triangle"></i> Peringatan:</span> Minimal margin grosir tidak boleh minus.`);
            } else {
                $card.removeClass('has-error has-warning');
                const modal = 10000;
                const minPrice = Math.round(modal * (1 + (margin / 100)));
                $box.html(`
                    <i class="ti ti-tag text-success me-1"></i>
                    <strong>Simulasi Modal Rp 10.000:</strong> Batas bawah harga jual pelanggan grosir adalah 
                    <span class="badge bg-success text-white fs-6 px-2">Rp ${formatMoney(minPrice)}</span> 
                    <small class="text-muted">(Margin minimal +${margin}%)</small>.
                `);
            }
        }
    }

    function validateFieldLive($input) {
        const key = $input.data('key');
        const val = $input.val().trim();
        const isNum = $input.hasClass('setting-number');

        let isValid = true;
        if (val === '') {
            isValid = false;
        } else if (isNum) {
            const num = Number(val);
            if (isNaN(num) || num < 0) isValid = false;
            if (key === 'nominal_per_poin' && num <= 0) isValid = false;
        }

        if (!isValid) {
            $input.addClass('is-invalid').removeClass('is-valid');
        } else {
            $input.removeClass('is-invalid').addClass('is-valid');
        }
    }

    function formatMoney(num) {
        if (!num || isNaN(num)) return '0';
        return Math.round(num).toLocaleString('id-ID');
    }

    function clearLogoInput(id) {
        $(`#${id}`).val('').trigger('change');
    }

    // ========================================================
    // FORM SUBMISSION & VALIDATION JEGATAN
    // ========================================================
    $('#form-setting-data').on('submit', function(e) {
        e.preventDefault();
        if (!canUpdate) {
            toastr.error('Anda tidak memiliki akses update setting data');
            return;
        }

        // Client-side Guardrails
        const errors = [];
        $('.setting-input').each(function() {
            const key = $(this).data('key');
            const val = $(this).val().trim();
            const label = $(`#card-${key} .fw-bold`).first().text() || key;

            if (val === '') {
                errors.push(`Kolom <strong>${label}</strong> wajib diisi.`);
            } else if ($(this).hasClass('setting-number')) {
                const num = Number(val);
                if (isNaN(num) || num < 0) {
                    errors.push(`Kolom <strong>${label}</strong> tidak boleh bernilai negatif.`);
                }
                if (key === 'nominal_per_poin' && num <= 0) {
                    errors.push(`Kolom <strong>Nominal per Poin</strong> harus bernilai lebih dari 0.`);
                }
            }
        });

        const $alert = $('#setting-error-alert');
        if (errors.length > 0) {
            $('#setting-error-msg').html(`<ul class="mb-0 ps-3">${errors.map(err => `<li>${err}</li>`).join('')}</ul>`);
            $alert.removeClass('d-none');
            toastr.error('Periksa kembali input yang ditandai merah.');
            return;
        }

        $alert.addClass('d-none');

        const $btnSave = $('#btn-save-setting');
        const origText = $btnSave.html();
        $btnSave.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan Setting...');

        $.ajax({
            type: 'POST',
            url: '<?= base_url('/setting-data/save') ?>',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(res) {
                $btnSave.prop('disabled', false).html(origText);
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Setting data berhasil disimpan');
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan setting data');
            },
            error: function(xhr) {
                $btnSave.prop('disabled', false).html(origText);
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan setting data'));
            }
        });
    });

    $('#form-upload-logo').on('submit', function(e) {
        e.preventDefault();
        if (!canUpdate) {
            toastr.error('Anda tidak memiliki akses upload logo');
            return;
        }

        const colorFile = $('#logo_color')[0].files[0];
        const bwFile = $('#logo_bw')[0].files[0];

        if (!colorFile && !bwFile) {
            toastr.warning('Pilih minimal satu file logo PNG untuk diupload.');
            return;
        }

        const $btnUpload = $('#btn-upload-logo');
        const origText = $btnUpload.html();
        $btnUpload.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Mengupload & Memproses Logo...');

        const formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/setting-data/upload-logo') ?>',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $btnUpload.prop('disabled', false).html(origText);
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Logo berhasil diupload');
                    setTimeout(() => window.location.reload(), 800);
                    return;
                }
                toastr.error(res.data || 'Gagal upload logo');
            },
            error: function(xhr) {
                $btnUpload.prop('disabled', false).html(origText);
                toastr.error(extractErrorMessage(xhr, 'Gagal upload logo'));
            }
        });
    });
</script>
<?= $this->endSection('javascript') ?>