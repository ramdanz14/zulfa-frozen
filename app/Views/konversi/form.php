<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $formData
 */
?>
<style>
    /* Mobile-first optimizations untuk Form Konversi Produksi */
    .summary-card-sticky {
        position: sticky;
        top: 80px;
        z-index: 10;
    }
    .input-qty-pakai {
        min-height: 42px;
        font-size: 1.05rem;
    }

    /* Mobile Recipe Card View (No Horizontal Scroll) */
    .recipe-mobile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        margin-bottom: 0.75rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .recipe-mobile-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .recipe-stat-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.4rem 0.6rem;
    }

    /* Tampilan Desktop Table vs Mobile Cards */
    @media (max-width: 767.98px) {
        .recipe-desktop-table {
            display: none !important;
        }
        .recipe-mobile-container {
            display: block !important;
        }
        .summary-card-sticky {
            position: static;
        }
    }

    @media (min-width: 768px) {
        .recipe-desktop-table {
            display: block !important;
        }
        .recipe-mobile-container {
            display: none !important;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-1">Tambah Produksi & Konversi</h4>
                        <p class="mb-0 text-muted small">Pilih item hasil dan tentukan bahan asal yang dipakai. Stok bahan berkurang dan stok hasil bertambah otomatis.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <a href="<?= base_url('/konversi') ?>" class="btn btn-outline-secondary btn-sm px-3"><i class="ti ti-arrow-left me-1"></i> Kembali ke List</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="konversi-form">
            <div class="row g-2 g-md-3">
                <div class="col-12 col-xl-8">
                    <!-- Card Informasi Transaksi -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2 px-3">
                            <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-file-description text-primary me-1"></i> Informasi Transaksi Konversi</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2 g-md-3">
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">ID Konversi</label>
                                    <input type="text" class="form-control form-control-sm bg-light font-monospace" id="konversi_id" readonly value="<?= esc($formData['header']['konversi_id'] ?? '') ?>">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm" id="tanggal" value="<?= esc($formData['header']['tanggal'] ?? date('Y-m-d')) ?>" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Lokasi Toko</label>
                                    <input type="text" class="form-control form-control-sm bg-light" readonly value="<?= esc(($formData['header']['toko_id'] ?? '') . ' - ' . ($formData['header']['toko_nama'] ?? '')) ?>">
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold">Pilih Produk Jadi (Hasil Konversi) <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="item-hasil" required></select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Satuan Produk Jadi</label>
                                    <input type="text" class="form-control form-control-sm bg-light font-monospace" id="sat_hasil" readonly placeholder="Satuan otomatis">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Keterangan / Catatan Batch</label>
                                    <input type="text" class="form-control form-control-sm" id="keterangan" placeholder="Opsional (misal: Batch 1 repacking pagi)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Recipe Bahan Asal -->
                    <div class="card border mb-3">
                        <div class="card-header bg-warning-subtle py-2 px-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-package-export text-warning me-1"></i> Recipe Bahan Baku Digunakan</h6>
                                    <small class="text-muted">Qty pakai harus kelipatan formula utuh dan stok toko mencukupi.</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-2 p-md-3">
                            <!-- Desktop View: Table Layout -->
                            <div class="table-responsive recipe-desktop-table">
                                <table class="table table-bordered table-hover table-sm align-middle mb-0" id="recipe-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item Bahan Asal</th>
                                            <th>Rasio Formula</th>
                                            <th class="text-end">Stok Toko</th>
                                            <th class="text-end" style="width: 140px;">Qty Dipakai</th>
                                            <th class="text-end">HPP Satuan</th>
                                            <th class="text-end">Hasil Jadi</th>
                                            <th class="text-end">Total HPP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Pilih item hasil terlebih dahulu untuk memuat recipe bahan</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile View: Clean Card Layout (Zero Horizontal Scroll) -->
                            <div class="recipe-mobile-container" id="recipe-mobile-list">
                                <div class="text-center text-muted py-4 small">
                                    <i class="ti ti-package-search fs-6 d-block mb-1 text-secondary"></i>
                                    Pilih item hasil terlebih dahulu untuk memuat recipe bahan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column Summary & Action -->
                <div class="col-12 col-xl-4">
                    <div class="card border mb-3 summary-card-sticky">
                        <div class="card-header bg-light py-2 px-3">
                            <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-calculator text-primary me-1"></i> Rekapitulasi Hasil & HPP</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="p-2 mb-3 rounded-3 bg-success-subtle border border-success-subtle d-flex justify-content-between align-items-center">
                                <span class="small fw-semibold text-success">Total Qty Hasil Jadi:</span>
                                <span class="fw-bolder font-monospace fs-4 text-success" id="summary-qty-hasil">0</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom small">
                                <span class="text-muted">Total HPP Bahan Terpakai:</span>
                                <span class="fw-bold font-monospace text-dark" id="summary-hpp-bahan">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom small">
                                <span class="text-muted">HPP Baru per Satuan:</span>
                                <span class="fw-bold font-monospace text-primary" id="summary-hpp-satuan">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom small">
                                <span class="text-muted">HPP Produk Saat Ini:</span>
                                <span class="fw-semibold font-monospace text-dark" id="summary-hpp-current">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom small">
                                <span class="text-muted">Estimasi HPP Rata-rata:</span>
                                <span class="fw-bolder font-monospace text-danger" id="summary-hpp-after">Rp 0</span>
                            </div>
                            <div class="p-2 bg-light rounded border text-muted small mb-3" id="summary-trace" style="font-size: 0.75rem;">
                                Pilih item hasil untuk melihat simulasi rumus HPP konversi.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success fw-bold py-2"><i class="ti ti-device-floppy me-1"></i> Simpan Transaksi Konversi</button>
                                <a href="<?= base_url('/konversi/recipe') ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-settings me-1"></i> Buka Setting Recipe</a>
                            </div>
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
    let recipePayload = null;
    let recipeLines = [];

    $(function() {
        $('#item-hasil').select2({
            width: '100%',
            placeholder: 'Ketik nama / barcode item hasil...',
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url('/konversi/search-result') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    term: params.term || ''
                }),
                processResults: data => data
            }
        });

        $('#item-hasil').on('select2:select', function(e) {
            loadRecipe(e.params.data.id, e.params.data.text);
        });

        $(document).on('input change', '.input-qty-pakai', function() {
            const idx = Number($(this).data('idx'));
            const row = recipeLines[idx];
            if (!row) return;
            const val = Number($(this).val() || 0);
            row.qty_pakai = val;
            
            // Sync antara desktop input dan mobile input jika ada perubahan
            $(`.input-qty-pakai[data-idx="${idx}"]`).not(this).val(val);

            recalcLine(row);
            updateRowDom(idx);
            updateSummary();
        });

        $('#konversi-form').on('submit', function(e) {
            e.preventDefault();
            if (!recipePayload || !$('#item-hasil').val()) {
                toastr.error('Item hasil wajib dipilih');
                return;
            }

            const payloadLines = recipeLines
                .filter(row => Number(row.qty_pakai || 0) > 0)
                .map(row => ({
                    recipe_id: row.recipe_id,
                    qty_pakai: Number(row.qty_pakai || 0)
                }));

            if (payloadLines.length === 0) {
                toastr.error('Minimal satu bahan asal harus diisi');
                return;
            }

            const invalid = recipeLines.find(row => Number(row.qty_pakai || 0) > 0 && (row.batch_count < 1 || row.multiple_valid === false || Number(row.qty_pakai || 0) > Number(row.stok_asal_satuan || 0)));
            if (invalid) {
                toastr.error('Masih ada qty bahan asal yang tidak valid atau tidak cukup untuk satu recipe utuh');
                return;
            }

            $.ajax({
                type: 'PUT',
                url: '<?= base_url('/konversi') ?>',
                dataType: 'json',
                data: {
                    konversi_id: $('#konversi_id').val(),
                    tanggal: $('#tanggal').val(),
                    keterangan: $('#keterangan').val(),
                    kode_item_hasil: $('#item-hasil').val(),
                    lines_json: JSON.stringify(payloadLines)
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Konversi berhasil disimpan');
                        window.location.href = '<?= base_url('/konversi') ?>';
                        return;
                    }
                    toastr.error(res.data || 'Gagal menyimpan konversi');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan konversi'));
                }
            });
        });
    });

    function loadRecipe(kodeItemHasil, labelText) {
        $.getJSON(`<?= base_url('/konversi/result-recipe') ?>/${encodeURIComponent(kodeItemHasil)}`, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Recipe tidak ditemukan');
                return;
            }

            recipePayload = res.data || null;
            recipeLines = (recipePayload?.recipe_lines || []).map(row => ({
                ...row,
                qty_pakai: 0,
                qty_hasil_line: 0,
                total_hpp_line: 0,
                batch_count: 0,
                multiple_valid: true
            }));
            $('#sat_hasil').val(recipePayload?.sat_hasil || '');
            renderRecipeTable();
            renderRecipeMobileCards();
            updateSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat recipe'));
        });
    }

    function renderRecipeTable() {
        const tbody = $('#recipe-table tbody');
        if (!recipePayload || recipeLines.length === 0) {
            tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">Recipe belum tersedia untuk item hasil ini</td></tr>');
            return;
        }

        tbody.html(recipeLines.map((row, idx) => `
            <tr>
                <td>
                    <div class="fw-semibold text-dark">${row.nama_item_asal || row.kode_item_asal}</div>
                    <small class="text-muted font-monospace">${row.kode_item_asal || '-'} | ${row.sat_asal || '-'}</small>
                </td>
                <td>
                    <span class="badge bg-light text-dark border font-monospace small">
                        ${Number(row.qty_asal || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${row.sat_asal} &rarr; ${Number(row.qty_hasil || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${row.sat_hasil}
                    </span>
                </td>
                <td class="text-end font-monospace row-stock" data-idx="${idx}">${Number(row.stok_asal_satuan || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                <td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end font-monospace fw-bold input-qty-pakai" data-idx="${idx}" value="${row.qty_pakai || 0}"></td>
                <td class="text-end font-monospace">Rp ${formatMoneyValue(row.hpp_asal || 0)}</td>
                <td class="text-end font-monospace fw-bold text-success row-hasil" data-idx="${idx}">${Number(row.qty_hasil_line || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                <td class="text-end font-monospace fw-bold text-danger row-hpp" data-idx="${idx}">Rp ${formatMoneyValue(row.total_hpp_line || 0)}</td>
            </tr>
        `).join(''));
    }

    function renderRecipeMobileCards() {
        const container = $('#recipe-mobile-list');
        if (!recipePayload || recipeLines.length === 0) {
            container.html('<div class="text-center text-muted py-4 small">Recipe belum tersedia untuk item hasil ini</div>');
            return;
        }

        container.html(recipeLines.map((row, idx) => `
            <div class="recipe-mobile-card" data-card-idx="${idx}">
                <!-- Baris 1: Nama Bahan & Rasio Recipe -->
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                    <div class="min-w-0">
                        <div class="fw-bold text-dark text-truncate">${row.nama_item_asal || row.kode_item_asal}</div>
                        <div class="text-muted font-monospace small">${row.kode_item_asal || '-'}</div>
                    </div>
                    <span class="badge bg-light text-dark border font-monospace small flex-shrink-0">
                        ${Number(row.qty_asal || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${row.sat_asal} &rarr; ${Number(row.qty_hasil || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${row.sat_hasil}
                    </span>
                </div>

                <!-- Baris 2: Stok Tersedia & HPP Satuan Bahan -->
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="recipe-stat-box">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Stok Tersedia Toko:</span>
                            <span class="fw-bold font-monospace text-dark">${Number(row.stok_asal_satuan || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${row.sat_asal}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="recipe-stat-box">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">HPP Bahan Asal:</span>
                            <span class="fw-semibold font-monospace text-dark">Rp ${formatMoneyValue(row.hpp_asal || 0)}</span>
                        </div>
                    </div>
                </div>

                <!-- Baris 3: Input Qty Dipakai (Touch Friendly) -->
                <div class="p-2 mb-2 rounded bg-warning-subtle border border-warning-subtle">
                    <label class="form-label small fw-bold text-dark mb-1 d-flex justify-content-between">
                        <span><i class="ti ti-hand-click text-warning me-1"></i> Jumlah Bahan Dipakai (${row.sat_asal}):</span>
                        <span class="card-validation-hint small text-danger d-none">Qty tidak valid / stok kurang</span>
                    </label>
                    <input type="number" min="0" step="0.01" class="form-control form-control-lg text-end font-monospace fw-bolder input-qty-pakai" data-idx="${idx}" value="${row.qty_pakai || 0}" placeholder="0.00">
                </div>

                <!-- Baris 4: Hasil Konversi Jadi & Nilai HPP Bahan -->
                <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                    <div>
                        <span class="text-muted">Hasil Jadi:</span>
                        <strong class="font-monospace text-success ms-1 row-hasil" data-idx="${idx}">${Number(row.qty_hasil_line || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</strong> ${row.sat_hasil || ''}
                    </div>
                    <div>
                        <span class="text-muted">Total HPP:</span>
                        <strong class="font-monospace text-danger ms-1 row-hpp" data-idx="${idx}">Rp ${formatMoneyValue(row.total_hpp_line || 0)}</strong>
                    </div>
                </div>
            </div>
        `).join(''));
    }

    function recalcLine(row) {
        const qtyPakai = Number(row.qty_pakai || 0);
        const qtyAsal = Number(row.qty_asal || 0);
        const qtyHasil = Number(row.qty_hasil || 0);
        row.multiple_valid = true;
        row.batch_count = 0;
        row.qty_hasil_line = 0;
        row.total_hpp_line = 0;

        if (qtyPakai <= 0 || qtyAsal <= 0 || qtyHasil <= 0) return;

        const ratio = qtyPakai / qtyAsal;
        row.multiple_valid = Math.abs(ratio - Math.round(ratio)) <= 0.0001;
        if (!row.multiple_valid) return;

        row.batch_count = Math.round(ratio);
        row.qty_hasil_line = row.batch_count * qtyHasil;
        row.total_hpp_line = qtyPakai * Number(row.hpp_asal || 0);
    }

    function updateRowDom(idx) {
        const row = recipeLines[idx];
        if (!row) return;
        const isInvalid = Number(row.qty_pakai || 0) > 0 && (row.multiple_valid === false || Number(row.qty_pakai || 0) > Number(row.stok_asal_satuan || 0));

        // Update desktop & mobile input invalid state
        $(`.input-qty-pakai[data-idx="${idx}"]`).toggleClass('is-invalid', isInvalid);
        $(`.recipe-mobile-card[data-card-idx="${idx}"] .card-validation-hint`).toggleClass('d-none', !isInvalid);

        // Update label hasil & total HPP
        $(`.row-hasil[data-idx="${idx}"]`).text(Number(row.qty_hasil_line || 0).toLocaleString('id-ID', {
            maximumFractionDigits: 2
        }));
        $(`.row-hpp[data-idx="${idx}"]`).text(`Rp ${formatMoneyValue(row.total_hpp_line || 0)}`);
    }

    function updateSummary() {
        const totalQtyHasil = recipeLines.reduce((sum, row) => sum + Number(row.qty_hasil_line || 0), 0);
        const totalHppBahan = recipeLines.reduce((sum, row) => sum + Number(row.total_hpp_line || 0), 0);
        const qtyKonvHasil = Number(recipePayload?.qty_konversi_hasil || 1);
        const stokHasilBase = Number(recipePayload?.stok_hasil_base || 0);
        const rpSaldoHasil = Number(recipePayload?.rp_saldo_hasil || 0);
        const totalQtyHasilBase = totalQtyHasil * qtyKonvHasil;
        const hppSatuan = totalQtyHasil > 0 ? totalHppBahan / totalQtyHasil : 0;
        const hppCurrent = Number(recipePayload?.hpp_hasil || 0);
        const hppBaseAfter = totalQtyHasilBase > 0 ? ((rpSaldoHasil + totalHppBahan) / Math.max(stokHasilBase + totalQtyHasilBase, 0.0001)) : 0;
        const hppAfter = hppBaseAfter * qtyKonvHasil;

        $('#summary-qty-hasil').text(totalQtyHasil.toLocaleString('id-ID', {
            maximumFractionDigits: 2
        }) + (recipePayload?.sat_hasil ? ` ${recipePayload.sat_hasil}` : ''));
        $('#summary-hpp-bahan').text('Rp ' + formatMoneyValue(totalHppBahan));
        $('#summary-hpp-satuan').text('Rp ' + formatMoneyValue(hppSatuan));
        $('#summary-hpp-current').text('Rp ' + formatMoneyValue(hppCurrent));
        $('#summary-hpp-after').text('Rp ' + formatMoneyValue(hppAfter));

        if (!recipePayload) {
            $('#summary-trace').text('Pilih item hasil untuk melihat simulasi rumus HPP konversi.');
            return;
        }

        $('#summary-trace').text(`hpp_base_baru = (${rpSaldoHasil.toFixed(2)} + ${totalHppBahan.toFixed(2)}) / (${stokHasilBase.toFixed(4)} + ${totalQtyHasilBase.toFixed(4)})`);
    }
</script>
<?= $this->endSection('javascript') ?>
