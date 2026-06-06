<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 * @var array $formData
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2"><?= $mode === 'create' ? 'Tambah' : 'Edit' ?> BAP Pemusnahan</h4>
                        <p class="mb-0">Catat item tidak layak jual yang dimusnahkan dan kurangi stok melalui tabel `adjust` dengan `istype=BAP`.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/bap') ?>" class="btn btn-secondary btn-sm">Kembali ke List</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="bap-form">
            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Dokumen</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">No. BAP</label>
                                    <input type="text" class="form-control" name="bap_id" id="bap_id" readonly value="<?= esc($formData['header']['bap_id'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" id="tanggal" value="<?= esc($formData['header']['tanggal'] ?? date('Y-m-d')) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Toko</label>
                                    <input type="text" class="form-control" readonly value="<?= esc(($formData['header']['toko_id'] ?? '') . ' - ' . ($formData['header']['toko_nama'] ?? '')) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" class="form-control" name="keterangan" id="keterangan" value="<?= esc($formData['header']['keterangan'] ?? '') ?>" placeholder="Opsional">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center">
                            <div>
                                <h5 class="mb-1">Detail Item Pemusnahan</h5>
                                <small class="text-muted">Cari item aktif toko lalu tambahkan ke tabel untuk diinput qty musnah dan nilai barang.</small>
                            </div>
                            <div class="w-100" style="max-width: 420px;">
                                <select class="form-select" id="item-search"></select>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle mb-0" id="detail-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="min-width: 220px;">Item</th>
                                            <th style="min-width: 150px;">Satuan</th>
                                            <th style="min-width: 120px;">Qty Musnah</th>
                                            <th style="min-width: 150px;">Stok Tersedia</th>
                                            <th style="min-width: 140px;">Harga</th>
                                            <th style="min-width: 140px;">Total</th>
                                            <th style="width: 60px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
                                <span class="text-muted">Jumlah Item</span>
                                <span class="fw-semibold" id="summary-item">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Qty</span>
                                <span class="fw-semibold" id="summary-qty">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Total Nilai</span>
                                <span class="fw-semibold text-danger" id="summary-gross">Rp 0</span>
                            </div>
                            <hr>
                            <div class="small text-muted">
                                Closing aktif: <?= esc($formData['header']['closing_date'] ?? '-') ?><br>
                                Dokumen dengan tanggal sebelum closing akan ditolak oleh sistem.
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="btn-save">Simpan BAP</button>
                            <?php if ($mode === 'edit') : ?>
                                <a href="<?= base_url('/bap/print/' . ($formData['header']['bap_id'] ?? '')) ?>" target="_blank" class="btn btn-outline-primary">Cetak Dokumen BAP</a>
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

        renderDetailTable();
        bindEvents();
        updateSummary();
    });

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
            renderDetailTable();
            updateSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat item'));
        });
    }

    function renderDetailTable() {
        const tbody = $('#detail-table tbody');
        if (detailRows.length === 0) {
            tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">Belum ada item yang dimasukkan</td></tr>');
            return;
        }

        tbody.html(detailRows.map((row, idx) => {
            const options = (row.unit_options || []).map(unit => `
                <option value="${unit.sat_id}" ${unit.sat_id === row.sat_id ? 'selected' : ''}>${unit.sat_id}</option>
            `).join('');
            return `
                <tr>
                    <td>
                        <div class="fw-semibold">${row.nama_item || row.kode_item}</div>
                        <small class="text-muted">${row.kode_item || '-'}</small>
                    </td>
                    <td>
                        <select class="form-select form-select-sm row-satuan" data-idx="${idx}">
                            ${options}
                        </select>
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
            renderDetailTable();
            updateSummary();
        });

        $(document).on('input change', '.row-qty', function() {
            const idx = Number($(this).data('idx'));
            const row = detailRows[idx];
            if (!row) return;
            row.qty_bap = Number($(this).val() || 0);
            recalcRow(idx);
            updateRowDom(idx);
            updateSummary();
        });

        $(document).on('change blur', '.row-price', function() {
            const idx = Number($(this).data('idx'));
            const row = detailRows[idx];
            if (!row) return;
            row.price = parseMoney($(this).val());
            recalcRow(idx);
            $(this).val(formatMoneyValue(row.price || 0));
            updateRowDom(idx);
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
                },
                error: function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan dokumen'));
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
        renderDetailTable();
        updateSummary();
    }

    function updateRowDom(idx) {
        const row = detailRows[idx];
        if (!row) return;
        $(`.row-total[data-idx="${idx}"]`).text(`Rp ${formatMoneyValue(row.gross || 0)}`);
        $(`.row-stock[data-idx="${idx}"]`).html(`
            ${Number(row.stock_hint || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}
            <div><small class="text-muted">stok satuan terpilih</small></div>
        `);
    }

    function updateSummary() {
        const totalItem = detailRows.length;
        const totalQty = detailRows.reduce((sum, row) => sum + Number(row.qty_bap || 0), 0);
        const totalGross = detailRows.reduce((sum, row) => sum + Number(row.gross || 0), 0);

        $('#summary-item').text(totalItem.toLocaleString('id-ID'));
        $('#summary-qty').text(totalQty.toLocaleString('id-ID', {
            maximumFractionDigits: 2
        }));
        $('#summary-gross').text('Rp ' + formatMoneyValue(totalGross));
    }

    function parseMoney(value) {
        const normalized = String(value || '').replace(/[^0-9.-]/g, '');
        const parsed = Number(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    }
</script>
<?= $this->endSection('javascript') ?>
