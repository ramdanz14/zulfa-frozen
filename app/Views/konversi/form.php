<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array $formData
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Tambah Produksi / Konversi</h4>
                        <p class="mb-0">Pilih item hasil, input bahan asal yang dipakai sesuai recipe, lalu sistem akan kurangi stok bahan dan tambah stok hasil otomatis.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/konversi') ?>" class="btn btn-secondary btn-sm">Kembali ke List</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="konversi-form">
            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Konversi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">ID Konversi</label>
                                    <input type="text" class="form-control" id="konversi_id" readonly value="<?= esc($formData['header']['konversi_id'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="tanggal" value="<?= esc($formData['header']['tanggal'] ?? date('Y-m-d')) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Toko</label>
                                    <input type="text" class="form-control" readonly value="<?= esc(($formData['header']['toko_id'] ?? '') . ' - ' . ($formData['header']['toko_nama'] ?? '')) ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Item Hasil</label>
                                    <select class="form-select" id="item-hasil" required></select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Satuan Hasil</label>
                                    <input type="text" class="form-control" id="sat_hasil" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" class="form-control" id="keterangan" placeholder="Opsional">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recipe Bahan Asal</h5>
                            <small class="text-muted">Isi qty bahan yang benar-benar dipakai. Qty harus memenuhi formula utuh, tidak boleh setengah recipe.</small>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle mb-0" id="recipe-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item Asal</th>
                                            <th>Formula</th>
                                            <th>Stok Tersedia</th>
                                            <th>Qty Pakai</th>
                                            <th>HPP Satuan</th>
                                            <th>Qty Hasil</th>
                                            <th>Total HPP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Pilih item hasil untuk memuat recipe</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Qty Hasil</span>
                                <span class="fw-semibold" id="summary-qty-hasil">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total HPP Bahan</span>
                                <span class="fw-semibold" id="summary-hpp-bahan">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">HPP Hasil / Satuan</span>
                                <span class="fw-semibold" id="summary-hpp-satuan">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">HPP Hasil Saat Ini</span>
                                <span class="fw-semibold" id="summary-hpp-current">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Estimasi HPP Baru</span>
                                <span class="fw-semibold text-danger" id="summary-hpp-after">Rp 0</span>
                            </div>
                            <hr>
                            <div class="small text-muted" id="summary-trace">Pilih item hasil untuk melihat simulasi rumus HPP konversi.</div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body d-grid gap-2">
                            <button type="submit" class="btn btn-success">Simpan Konversi</button>
                            <a href="<?= base_url('/konversi/recipe') ?>" class="btn btn-outline-primary">Buka Setting Recipe</a>
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
            placeholder: 'Pilih item hasil',
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
            row.qty_pakai = Number($(this).val() || 0);
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
                    <div class="fw-semibold">${row.nama_item_asal || row.kode_item_asal}</div>
                    <small class="text-muted">${row.kode_item_asal || '-'} | ${row.sat_asal || '-'}</small>
                </td>
                <td>${Number(row.qty_asal || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${row.sat_asal} -> ${Number(row.qty_hasil || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${row.sat_hasil}</td>
                <td class="text-end row-stock" data-idx="${idx}">${Number(row.stok_asal_satuan || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                <td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end input-qty-pakai" data-idx="${idx}" value="${row.qty_pakai || 0}"></td>
                <td class="text-end">Rp ${formatMoneyValue(row.hpp_asal || 0)}</td>
                <td class="text-end row-hasil" data-idx="${idx}">${Number(row.qty_hasil_line || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}</td>
                <td class="text-end row-hpp" data-idx="${idx}">Rp ${formatMoneyValue(row.total_hpp_line || 0)}</td>
            </tr>
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
        const $input = $(`.input-qty-pakai[data-idx="${idx}"]`);
        $input.toggleClass('is-invalid', Number(row.qty_pakai || 0) > 0 && (row.multiple_valid === false || Number(row.qty_pakai || 0) > Number(row.stok_asal_satuan || 0)));
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
