<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 * @var array $formData
 */
?>
<style>
/* Desktop table hidden on mobile, mobile cards hidden on desktop */
@media (max-width: 767.98px) {
    .bap-desktop-table { display: none !important; }
    .bap-mobile-container { display: block !important; }
}
@media (min-width: 768px) {
    .bap-desktop-table { display: block !important; }
    .bap-mobile-container { display: none !important; }
}
.bap-mobile-container { display: none; }
.bap-item-card { border-radius: .5rem; }
.bap-item-card .field-label { font-size: .72rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: .02em; }
.bap-item-card .form-control-sm,
.bap-item-card .form-select-sm { min-height: 40px; font-size: .9rem; }
.bap-item-card .btn-remove { min-height: 36px; min-width: 36px; }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-8">
                        <h4 class="fw-semibold mb-2"><?= $mode === 'create' ? 'Tambah' : 'Edit' ?> BAP Pemusnahan</h4>
                        <p class="mb-0">Catat item tidak layak jual yang dimusnahkan dan kurangi stok melalui tabel <code>adjust</code> dengan <code>istype=BAP</code>.</p>
                    </div>
                    <div class="col-12 col-lg-4 text-start text-lg-end mt-2 mt-lg-0">
                        <a href="<?= base_url('/bap') ?>" class="btn btn-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>Kembali ke List</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="bap-form">
            <div class="row g-3">
                <div class="col-12 col-xl-8">
                    <!-- Header info -->
                    <div class="card mb-3">
                        <div class="card-header py-2 px-3">
                            <h6 class="mb-0 fw-semibold">Informasi Dokumen</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">No. BAP</label>
                                    <input type="text" class="form-control form-control-sm" name="bap_id" id="bap_id" readonly value="<?= esc($formData['header']['bap_id'] ?? '') ?>">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="tanggal" id="tanggal" value="<?= esc($formData['header']['tanggal'] ?? date('Y-m-d')) ?>" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Toko</label>
                                    <input type="text" class="form-control form-control-sm" readonly value="<?= esc(($formData['header']['toko_id'] ?? '') . ' - ' . ($formData['header']['toko_nama'] ?? '')) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Keterangan</label>
                                    <input type="text" class="form-control form-control-sm" name="keterangan" id="keterangan" value="<?= esc($formData['header']['keterangan'] ?? '') ?>" placeholder="Opsional">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail item -->
                    <div class="card">
                        <div class="card-header d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center py-2 px-3">
                            <div>
                                <h6 class="mb-1 fw-semibold">Detail Item Pemusnahan</h6>
                                <small class="text-muted">Cari item aktif toko lalu tambahkan. Isi qty musnah dan harga per item.</small>
                            </div>
                            <div class="w-100" style="max-width: 420px;">
                                <select class="form-select form-select-sm" id="item-search"></select>
                            </div>
                        </div>
                        <div class="card-body p-2 p-md-3">
                            <!-- Desktop table view -->
                            <div class="bap-desktop-table">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle mb-0" id="detail-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="min-width:200px;">Item</th>
                                                <th style="min-width:130px;">Satuan</th>
                                                <th style="min-width:110px;">Qty Musnah</th>
                                                <th style="min-width:130px;">Stok Tersedia</th>
                                                <th style="min-width:130px;">Harga</th>
                                                <th style="min-width:130px;">Total</th>
                                                <th style="width:52px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Mobile card view -->
                            <div class="bap-mobile-container" id="bap-mobile-list">
                                <p class="text-muted text-center py-4" id="bap-mobile-empty">Belum ada item yang dimasukkan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary + Save -->
                <div class="col-12 col-xl-4">
                    <div class="card mb-3">
                        <div class="card-header py-2 px-3">
                            <h6 class="mb-0 fw-semibold">Summary</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Jumlah Item</span>
                                <span class="fw-semibold" id="summary-item">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Total Qty</span>
                                <span class="fw-semibold" id="summary-qty">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Total Nilai</span>
                                <span class="fw-semibold text-danger" id="summary-gross">Rp 0</span>
                            </div>
                            <hr class="my-2">
                            <div class="small text-muted">
                                Closing aktif: <?= esc($formData['header']['closing_date'] ?? '-') ?><br>
                                Dokumen dengan tanggal sebelum closing akan ditolak sistem.
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body p-3 d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="btn-save"><i class="ti ti-device-floppy me-1"></i>Simpan BAP</button>
                            <?php if ($mode === 'edit') : ?>
                                <a href="<?= base_url('/bap/print/' . ($formData['header']['bap_id'] ?? '')) ?>" target="_blank" class="btn btn-outline-primary"><i class="ti ti-printer me-1"></i>Cetak Dokumen BAP</a>
                            <?php endif; ?>
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
    let detailRows = <?= json_encode($formData['details'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    $(function() {
        $('#item-search').select2({
            width: '100%',
            placeholder: 'Cari item / barcode',
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url('/bap/search-item') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    term: params.term || ''
                }),
                processResults: data => data
            }
        });

        $('#item-search').on('select2:select', function(e) {
            loadItem(e.params.data.id);
            $(this).val(null).trigger('change');
        });

        detailRows = detailRows.map(row => ({
            kode_item: row.kode_item || '',
            nama_item: row.nama_item || '',
            sat_id: row.sat_id || '',
            qty_bap: Number(row.qty_bap || 0),
            qty_konversi: Number(row.qty_konversi || 1),
            price: Number(row.price || 0),
            gross: Number(row.gross || 0),
            stock_hint: Number(row.stock_hint || 0),
            unit_options: Array.isArray(row.unit_options) ? row.unit_options : []
        }));

        renderAll();
        bindEvents();
        updateSummary();
    });

    function renderAll() {
        renderDetailTable();
        renderMobileCards();
    }

    function loadItem(kodeItem) {
        $.getJSON(`<?= base_url('/bap/item-detail') ?>/${encodeURIComponent(kodeItem)}`, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Item tidak ditemukan');
                return;
            }

            const payload = res.data || {};
            const firstUnit = (payload.unit_options || [])[0] || {};
            const existingIndex = detailRows.findIndex(row => row.kode_item === payload.kode_item && row.sat_id === (firstUnit.sat_id || ''));
            if (existingIndex >= 0) {
                detailRows[existingIndex].qty_bap = Number(detailRows[existingIndex].qty_bap || 0) + 1;
                recalcRow(existingIndex);
            } else {
                detailRows.push({
                    kode_item: payload.kode_item || '',
                    nama_item: payload.nama_item || '',
                    sat_id: firstUnit.sat_id || '',
                    qty_bap: 1,
                    qty_konversi: Number(firstUnit.qty_konversi || 1),
                    price: Number(firstUnit.harga_pokok || 0),
                    gross: Number(firstUnit.harga_pokok || 0),
                    stock_hint: Number(firstUnit.stock_hint || 0),
                    unit_options: payload.unit_options || []
                });
            }
            renderAll();
            updateSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat item'));
        });
    }

    /* ── Desktop table ─────────────────────────────────────────── */
    function renderDetailTable() {
        const tbody = $('#detail-table tbody');
        if (detailRows.length === 0) {
            tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">Belum ada item yang dimasukkan</td></tr>');
            return;
        }

        tbody.html(detailRows.map((row, idx) => {
            const options = (row.unit_options || []).map(unit =>
                `<option value="${unit.sat_id}" ${unit.sat_id === row.sat_id ? 'selected' : ''}>${unit.sat_id}</option>`
            ).join('');
            return `
                <tr>
                    <td>
                        <div class="fw-semibold">${row.nama_item || row.kode_item}</div>
                        <small class="text-muted">${row.kode_item || '-'}</small>
                    </td>
                    <td>
                        <select class="form-select form-select-sm row-satuan" data-idx="${idx}">${options}</select>
                    </td>
                    <td>
                        <input type="number" min="0.01" step="0.01" class="form-control form-control-sm row-qty text-end" data-idx="${idx}" value="${row.qty_bap || 0}">
                    </td>
                    <td class="text-end row-stock" data-idx="${idx}">
                        ${Number(row.stock_hint || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}
                        <div><small class="text-muted">stok satuan terpilih</small></div>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm row-price text-end money" data-idx="${idx}" value="${formatMoneyValue(row.price || 0)}">
                    </td>
                    <td class="text-end row-total" data-idx="${idx}">Rp ${formatMoneyValue(row.gross || 0)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(${idx})"><i class="ti ti-trash"></i></button>
                    </td>
                </tr>
            `;
        }).join(''));

        applyMoneyMask('#detail-table');
    }

    /* ── Mobile card list ──────────────────────────────────────── */
    function renderMobileCards() {
        const container = $('#bap-mobile-list');
        if (detailRows.length === 0) {
            container.html('<p class="text-muted text-center py-4">Belum ada item yang dimasukkan</p>');
            return;
        }

        container.html(detailRows.map((row, idx) => {
            const options = (row.unit_options || []).map(unit =>
                `<option value="${unit.sat_id}" ${unit.sat_id === row.sat_id ? 'selected' : ''}>${unit.sat_id}</option>`
            ).join('');
            return `
                <div class="card border bap-item-card mb-2" data-card-idx="${idx}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold">${row.nama_item || row.kode_item}</div>
                                <small class="text-muted">${row.kode_item || '-'}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger btn-remove ms-2" onclick="removeRow(${idx})">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <div class="field-label mb-1">Satuan</div>
                                <select class="form-select form-select-sm row-satuan" data-idx="${idx}">${options}</select>
                            </div>
                            <div class="col-6">
                                <div class="field-label mb-1">Qty Musnah</div>
                                <input type="number" min="0.01" step="0.01"
                                    class="form-control form-control-sm row-qty text-end"
                                    data-idx="${idx}" value="${row.qty_bap || 0}">
                            </div>
                            <div class="col-6">
                                <div class="field-label mb-1">Harga</div>
                                <input type="text"
                                    class="form-control form-control-sm row-price text-end money"
                                    data-idx="${idx}" value="${formatMoneyValue(row.price || 0)}">
                            </div>
                            <div class="col-6">
                                <div class="field-label mb-1">Stok Tersedia</div>
                                <div class="form-control-sm py-1 px-2 border rounded bg-light row-stock text-end" data-idx="${idx}">
                                    ${Number(row.stock_hint || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}
                                    <small class="text-muted d-block" style="font-size:.7rem;">stok satuan terpilih</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                            <span class="text-muted small">Total</span>
                            <span class="fw-semibold text-danger row-total" data-idx="${idx}">Rp ${formatMoneyValue(row.gross || 0)}</span>
                        </div>
                    </div>
                </div>
            `;
        }).join(''));

        applyMoneyMask('#bap-mobile-list');
    }

    /* ── Events ────────────────────────────────────────────────── */
    function bindEvents() {
        $(document).on('change', '.row-satuan', function() {
            const idx = Number($(this).data('idx'));
            const row = detailRows[idx];
            if (!row) return;
            const satId = $(this).val();
            const unit = (row.unit_options || []).find(item => item.sat_id === satId) || null;
            if (!unit) return;
            row.sat_id = unit.sat_id || '';
            row.qty_konversi = Number(unit.qty_konversi || 1);
            row.price = Number(unit.harga_pokok || 0);
            row.stock_hint = Number(unit.stock_hint || 0);
            recalcRow(idx);
            renderAll();
            updateSummary();
        });

        $(document).on('input change', '.row-qty', function() {
            const idx = Number($(this).data('idx'));
            const row = detailRows[idx];
            if (!row) return;
            row.qty_bap = Number($(this).val() || 0);
            recalcRow(idx);
            updateRowDom(idx, this);
            updateSummary();
        });

        $(document).on('change blur', '.row-price', function() {
            const idx = Number($(this).data('idx'));
            const row = detailRows[idx];
            if (!row) return;
            row.price = parseMoney($(this).val());
            recalcRow(idx);
            // Sync both desktop and mobile price fields
            $(`.row-price[data-idx="${idx}"]`).not(this).val(formatMoneyValue(row.price || 0));
            updateRowDom(idx, this);
            updateSummary();
        });

        $('#bap-form').on('submit', function(e) {
            e.preventDefault();
            if (detailRows.length === 0) {
                toastr.error('Minimal satu item BAP wajib diisi');
                return;
            }

            const cleaned = detailRows.map(row => ({
                kode_item: row.kode_item,
                sat_id: row.sat_id,
                qty_bap: Number(row.qty_bap || 0),
                price: Number(row.price || 0)
            })).filter(row => row.kode_item && row.sat_id && row.qty_bap > 0);

            if (cleaned.length === 0) {
                toastr.error('Semua baris detail masih kosong atau tidak valid');
                return;
            }

            const btn = $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

            $.ajax({
                type: mode === 'create' ? 'PUT' : 'PATCH',
                url: '<?= base_url('/bap') ?>',
                dataType: 'json',
                data: {
                    bap_id: $('#bap_id').val(),
                    tanggal: $('#tanggal').val(),
                    keterangan: $('#keterangan').val(),
                    detail_json: JSON.stringify(cleaned)
                },
                success: function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Dokumen BAP berhasil disimpan');
                        window.location.href = '<?= base_url('/bap') ?>';
                        return;
                    }
                    toastr.error(res.data || 'Gagal menyimpan dokumen');
                    btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Simpan BAP');
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan dokumen'));
                    btn.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Simpan BAP');
                }
            });
        });
    }

    function recalcRow(idx) {
        const row = detailRows[idx];
        if (!row) return;
        row.qty_bap = Number(row.qty_bap || 0);
        row.price = Number(row.price || 0);
        row.gross = Math.round(row.qty_bap * row.price);
    }

    function removeRow(idx) {
        detailRows.splice(idx, 1);
        renderAll();
        updateSummary();
    }

    function updateRowDom(idx, skipEl) {
        const row = detailRows[idx];
        if (!row) return;
        const totalHtml = `Rp ${formatMoneyValue(row.gross || 0)}`;
        $(`.row-total[data-idx="${idx}"]`).text(totalHtml);
        const stockHtml = `${Number(row.stock_hint || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}<div><small class="text-muted" style="font-size:.7rem;">stok satuan terpilih</small></div>`;
        $(`.row-stock[data-idx="${idx}"]`).html(stockHtml);
        // Sync qty across desktop/mobile without re-render
        $(`.row-qty[data-idx="${idx}"]`).not(skipEl).val(row.qty_bap);
    }

    function updateSummary() {
        const totalItem = detailRows.length;
        const totalQty = detailRows.reduce((sum, row) => sum + Number(row.qty_bap || 0), 0);
        const totalGross = detailRows.reduce((sum, row) => sum + Number(row.gross || 0), 0);

        $('#summary-item').text(totalItem.toLocaleString('id-ID'));
        $('#summary-qty').text(totalQty.toLocaleString('id-ID', { maximumFractionDigits: 2 }));
        $('#summary-gross').text('Rp ' + formatMoneyValue(totalGross));
    }

    function parseMoney(value) {
        const normalized = String(value || '').replace(/[^0-9.-]/g, '');
        const parsed = Number(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    }
</script>
<?= $this->endSection('javascript') ?>
