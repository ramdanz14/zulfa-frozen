<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 */
$header = $formData['header'] ?? [];
$detailRows = $formData['details'] ?? [];
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2"><?= $mode === 'edit' ? 'Edit Draft Kirim Transfer' : 'Buat Draft Kirim Transfer' ?></h4>
                        <p class="mb-0">Draft ini disiapkan dari PO cabang, tetapi qty kirim, item tambahan, dan item yang dibatalkan masih bisa disesuaikan mengikuti stok fisik gudang.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/transfer') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke List Transfer</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="form-transfer">
            <input type="hidden" name="_method" value="<?= $mode === 'edit' ? 'PATCH' : 'PUT' ?>">
            <input type="hidden" name="transfer_id" id="transfer_id" value="<?= esc($header['transfer_id'] ?? '') ?>">
            <input type="hidden" name="po_toko_id" id="po_toko_id" value="<?= esc($header['po_toko_id'] ?? '') ?>">
            <input type="hidden" name="po_beli_id" id="po_beli_id" value="<?= esc($header['po_beli_id'] ?? '') ?>">
            <input type="hidden" name="tujuan_toko_id" id="tujuan_toko_id" value="<?= esc($header['tujuan_toko_id'] ?? '') ?>">
            <input type="hidden" name="detail_json" id="detail_json">

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Header Transfer</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3">
                            <label class="form-label">Transfer ID</label>
                            <input type="text" class="form-control" value="<?= esc($header['transfer_id'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Tanggal Draft</label>
                            <input type="date" class="form-control" name="tanggal_transfer" id="tanggal_transfer" value="<?= esc($header['tanggal_transfer'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">PO Cabang</label>
                            <input type="text" class="form-control" value="<?= esc($header['po_beli_id'] ?? '-') ?>" readonly>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Invoice PO</label>
                            <input type="text" class="form-control" value="<?= esc($header['invoice_po'] ?? '-') ?>" readonly>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Cabang Tujuan</label>
                            <input type="text" class="form-control" value="<?= esc(($header['tujuan_toko_nama'] ?? '-') . ' (' . ($header['tujuan_toko_id'] ?? '-') . ')') ?>" readonly>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" rows="2" name="keterangan" id="keterangan"><?= esc($header['keterangan'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center">
                    <div>
                        <h5 class="mb-1">Detail Item Kirim</h5>
                        <small class="text-muted">Tambahkan item gudang bila perlu. Harga transfer dihitung dari HPP gudang + markup gudang lalu dibulatkan Rp 50 bila satuannya non-gramasi.</small>
                    </div>
                    <div class="w-100" style="max-width: 420px;">
                        <select class="form-select" id="item-search"></select>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div id="detail-list" class="d-grid gap-2"></div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Jumlah Item Kirim</small>
                        <div class="fw-semibold fs-5" id="sum-items">0</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Total Qty Kirim</small>
                        <div class="fw-semibold fs-5" id="sum-qty">0</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Total Nilai Transfer</small>
                        <input type="text" class="form-control form-control-lg money text-end fw-semibold border-0 p-0 bg-transparent" id="sum-total" value="0" readonly>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end pb-4">
                <button type="button" class="btn btn-light" onclick="window.location.href='<?= base_url('/transfer') ?>'">Batal</button>
                <button type="button" class="btn btn-outline-primary" onclick="submitDraft(false)">Simpan Draft</button>
                <button type="button" class="btn btn-primary" onclick="submitDraft(true)">Simpan & Kirim</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const initialDetails = <?= json_encode($detailRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let detailRows = hydrateRows(initialDetails || []);

    $(function() {
        $('#item-search').select2({
            width: '100%',
            placeholder: 'Cari item gudang / barcode',
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url('/transfer/search-item') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    term: params.term
                }),
                processResults: data => data
            }
        });

        $('#item-search').on('select2:select', function(e) {
            addItemByCode(e.params.data.id);
            $(this).val(null).trigger('change');
        });

        renderDetailList();
        recalcSummary();
        applyMoneyMask('#form-transfer');
    });

    function addItemByCode(kodeItem) {
        if (detailRows.some((row) => String(row.kode_item) === String(kodeItem))) {
            toastr.error('Item sudah ada di draft transfer');
            return;
        }

        $.getJSON(`<?= base_url('/transfer/item-detail') ?>/${kodeItem}`, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Item tidak ditemukan');
                return;
            }
            const item = res.data || {};
            const firstSat = (item.satuan || [])[0] || {};
            detailRows.push({
                seq_no: detailRows.length + 1,
                kode_item: item.kode_item,
                barcode: item.barcode || '',
                nama_item: item.nama_item || item.kode_item,
                qty_po: 0,
                stok_base: Number(item.stok_base || 0),
                sat_id: firstSat.sat_id || '',
                qty_konversi: Number(firstSat.qty_konversi || 1),
                qty_kirim: 0,
                qty_stock: 0,
                harga_pokok: Number(firstSat.harga_pokok || 0),
                harga_jual: Number(firstSat.harga_jual_transfer || 0),
                gross: 0,
                satuan_options: item.satuan || [],
                item_error: ''
            });
            renderDetailList();
            recalcSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat item gudang'));
        });
    }

    function renderDetailList() {
        const wrapper = $('#detail-list');
        wrapper.empty();

        if (!detailRows.length) {
            wrapper.html('<div class="text-center text-muted py-4">Belum ada item yang disiapkan untuk dikirim.</div>');
            return;
        }

        detailRows.forEach((row, idx) => {
            const satOptions = (row.satuan_options || []).map((opt) => `
                <option value="${opt.sat_id}"
                        data-konversi="${opt.qty_konversi}"
                        data-hpp="${opt.harga_pokok || 0}"
                        data-price="${opt.harga_jual_transfer || 0}"
                        ${String(row.sat_id) === String(opt.sat_id) ? 'selected' : ''}>
                    ${opt.sat_id}
                </option>
            `).join('');
            const maxQty = getMaxQty(row);

            wrapper.append(`
                <div class="border rounded p-2" data-index="${idx}">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-lg-3">
                            <div class="fw-semibold">${row.nama_item || row.kode_item}</div>
                            <small class="text-muted d-block">${row.kode_item} | PO: ${Number(row.qty_po || 0).toLocaleString('id-ID')} | Stok dasar: ${Number(row.stok_base || 0).toLocaleString('id-ID')}</small>
                            ${row.item_error ? `<small class="text-danger d-block">${row.item_error}</small>` : `<small class="text-muted d-block">Maks kirim di satuan terpilih: ${Number(maxQty).toLocaleString('id-ID')}</small>`}
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label">Satuan</label>
                            <select class="form-select form-select-sm row-sat" ${row.item_error ? 'disabled' : ''}>${satOptions}</select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label">Qty Kirim</label>
                            <input type="number" min="0" step="1" class="form-control form-control-sm text-end row-qty" value="${row.qty_kirim || 0}" ${row.item_error ? 'disabled' : ''}>
                        </div>
                        <div class="col-6 col-md-2 col-lg-1">
                            <label class="form-label">HPP</label>
                            <input type="text" class="form-control form-control-sm money text-end row-hpp" value="${row.harga_pokok || 0}" readonly>
                        </div>
                        <div class="col-6 col-md-2 col-lg-2">
                            <label class="form-label">Harga Transfer</label>
                            <input type="text" class="form-control form-control-sm money text-end row-price" value="${row.harga_jual || 0}" readonly>
                        </div>
                        <div class="col-8 col-md-3 col-lg-1">
                            <label class="form-label">Gross</label>
                            <input type="text" class="form-control form-control-sm money text-end row-gross" value="${row.gross || 0}" readonly>
                        </div>
                        <div class="col-4 col-md-1 col-lg-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger row-delete"><i class="ti ti-trash"></i></button>
                        </div>
                    </div>
                </div>
            `);
        });

        applyMoneyMask('#detail-list');
    }

    $('#detail-list').on('change', '.row-sat', function() {
        const card = $(this).closest('[data-index]');
        const idx = Number(card.data('index'));
        const row = detailRows[idx];
        const option = $(this).find(':selected');
        row.sat_id = option.val();
        row.qty_konversi = Number(option.data('konversi') || 1);
        row.harga_pokok = Number(option.data('hpp') || 0);
        row.harga_jual = Number(option.data('price') || 0);
        recalcRow(row);
        renderDetailList();
        recalcSummary();
    });

    $('#detail-list').on('input', '.row-qty', function() {
        const card = $(this).closest('[data-index]');
        const idx = Number(card.data('index'));
        const row = detailRows[idx];
        row.qty_kirim = Number($(this).val() || 0);
        const maxQty = getMaxQty(row);
        if (row.qty_kirim - maxQty > 0.0001) {
            toastr.error(`Qty kirim ${row.kode_item} melebihi stok gudang`);
            row.qty_kirim = maxQty;
        }
        recalcRow(row);
        renderDetailList();
        recalcSummary();
    });

    $('#detail-list').on('click', '.row-delete', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        detailRows.splice(idx, 1);
        renderDetailList();
        recalcSummary();
    });

    function recalcRow(row) {
        row.qty_stock = roundNumber(Number(row.qty_kirim || 0) * Number(row.qty_konversi || 1), 4);
        row.gross = roundNumber(Number(row.qty_kirim || 0) * Number(row.harga_jual || 0), 2);
    }

    function getMaxQty(row) {
        const konversi = Number(row.qty_konversi || 1);
        if (konversi <= 0) return 0;
        return roundNumber(Number(row.stok_base || 0) / konversi, 2);
    }

    function recalcSummary() {
        let totalItems = 0;
        let totalQty = 0;
        let totalGross = 0;
        detailRows.forEach((row) => {
            if (Number(row.qty_kirim || 0) > 0) {
                totalItems += 1;
                totalQty += Number(row.qty_kirim || 0);
                totalGross += Number(row.gross || 0);
            }
        });

        $('#sum-items').text(totalItems.toLocaleString('id-ID'));
        $('#sum-qty').text(totalQty.toLocaleString('id-ID'));
        $('#sum-total').val(totalGross);
        applyMoneyMask('#sum-total');
    }

    function submitDraft(sendAfterSave) {
        if (!detailRows.length) {
            toastr.error('Detail transfer belum diisi');
            return;
        }

        const payloadDetails = detailRows.map((row) => ({
            kode_item: row.kode_item,
            sat_id: row.sat_id,
            qty_po: Number(row.qty_po || 0),
            qty_kirim: Number(row.qty_kirim || 0),
            qty_konversi: Number(row.qty_konversi || 1),
            qty_stock: Number(row.qty_stock || 0),
            harga_pokok: Number(row.harga_pokok || 0),
            harga_jual: Number(row.harga_jual || 0),
            gross: Number(row.gross || 0)
        }));

        $('#detail_json').val(JSON.stringify(payloadDetails));
        normalizeMoneyInputs('#form-transfer');

        $.ajax({
            type: 'POST',
            url: '<?= base_url('/transfer') ?>',
            dataType: 'json',
            data: $('#form-transfer').serializeArray(),
            success: function(res) {
                if (res.tipe !== 'success') {
                    toastr.error(res.data || 'Gagal menyimpan draft transfer');
                    return;
                }

                if (!sendAfterSave) {
                    toastr.success(res.data || 'Draft transfer berhasil disimpan');
                    window.location.href = '<?= base_url('/transfer') ?>';
                    return;
                }

                const transferId = res.transfer_id || $('#transfer_id').val();
                Swal.fire({
                    title: 'Kirim transfer ini?',
                    text: 'Stok gudang akan langsung berkurang dan transfer dicatat sebagai penjualan kredit ke cabang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, kirim',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        window.location.href = '<?= base_url('/transfer') ?>';
                        return;
                    }

                    $.post(`<?= base_url('/transfer/send') ?>/${transferId}`, function(sendRes) {
                        if (sendRes.tipe === 'success') {
                            toastr.success(sendRes.data || 'Transfer berhasil dikirim');
                            window.location.href = '<?= base_url('/transfer') ?>';
                            return;
                        }
                        toastr.error(sendRes.data || 'Gagal mengirim transfer');
                    }, 'json').fail(function(xhr) {
                        toastr.error(extractErrorMessage(xhr, 'Gagal mengirim transfer'));
                    });
                });
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan draft transfer'));
            }
        });
    }

    function hydrateRows(rows) {
        return (rows || []).map((row) => ({
            ...row,
            stok_base: Number(row.stok_base || 0),
            qty_po: Number(row.qty_po || 0),
            qty_kirim: Number(row.qty_kirim || 0),
            qty_konversi: Number(row.qty_konversi || 1),
            qty_stock: Number(row.qty_stock || 0),
            harga_pokok: Number(row.harga_pokok || 0),
            harga_jual: Number(row.harga_jual || 0),
            gross: Number(row.gross || 0)
        }));
    }

    function roundNumber(value, precision = 2) {
        const factor = Math.pow(10, precision);
        return Math.round((Number(value) || 0) * factor) / factor;
    }
</script>
<?= $this->endSection('javascript') ?>