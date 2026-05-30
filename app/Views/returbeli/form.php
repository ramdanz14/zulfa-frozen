<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 * @var array $purchaseOptions
 */
$header = $formData['header'] ?? [];
$purchase = $formData['purchase'] ?? null;
$detailRows = $formData['details'] ?? [];
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2"><?= $mode === 'edit' ? 'Edit Retur Pembelian' : 'Tambah Retur Pembelian' ?></h4>
                        <p class="mb-0">Retur hanya untuk pembelian kredit yang sudah `TERIMA` dan belum `LUNAS`.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/returbeli') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke List</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="form-retur">
            <input type="hidden" name="_method" value="<?= $mode === 'edit' ? 'PATCH' : 'PUT' ?>">
            <input type="hidden" name="retur_id" id="retur_id" value="<?= esc($header['retur_id'] ?? '') ?>">
            <input type="hidden" name="detail_json" id="detail_json">

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Header Retur</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3">
                            <label class="form-label">ID Retur</label>
                            <input type="text" class="form-control" value="<?= esc($header['retur_id'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Tanggal Retur</label>
                            <input type="date" class="form-control" name="tanggal" id="tanggal" value="<?= esc($header['tanggal'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Status Retur</label>
                            <select class="form-select" name="status_retur" id="status_retur">
                                <option value="DRAFT" <?= ($header['status_retur'] ?? 'DRAFT') === 'DRAFT' ? 'selected' : '' ?>>DRAFT</option>
                                <option value="SELESAI" <?= ($header['status_retur'] ?? '') === 'SELESAI' ? 'selected' : '' ?>>SELESAI</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Faktur Asal</label>
                            <select class="form-select select2" name="beli_id" id="beli_id" required>
                                <option value="">Pilih faktur pembelian</option>
                                <?php foreach ($purchaseOptions as $option) : ?>
                                    <option value="<?= esc($option['beli_id']) ?>" <?= ($header['beli_id'] ?? '') === $option['beli_id'] ? 'selected' : '' ?>>
                                        <?= esc($option['beli_id']) ?> | <?= esc($option['supplier_nama'] ?? $option['supco']) ?> | <?= esc($option['invoice']) ?> | Rp <?= number_format((float) ($option['sisa_bayar'] ?? 0), 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" rows="2" name="keterangan" id="keterangan"><?= esc($header['keterangan'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div id="purchase-info" class="<?= $purchase ? '' : 'd-none' ?>">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Informasi Pembelian Asal</h5>
                    </div>
                    <div class="card-body" id="purchase-summary"></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Item Retur</h5>
                    <small class="text-muted">Qty retur tidak boleh melebihi stok saat ini dan qty pembelian yang masih bisa diretur.</small>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0" id="retur-detail-table">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Item</th>
                                    <th>Qty Beli</th>
                                    <th>Sudah Diretur</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Satuan Retur</th>
                                    <th>Qty Retur</th>
                                    <th>Qty Stok</th>
                                    <th>Price</th>
                                    <th>Gross Retur</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Jumlah Item Diretur</small>
                        <div class="fw-semibold fs-5" id="sum-items">0</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Total Qty Retur</small>
                        <div class="fw-semibold fs-5" id="sum-qty">0</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Total Nilai Retur</small>
                        <input type="text" class="form-control form-control-lg money text-end fw-semibold border-0 p-0 bg-transparent" id="sum-total" value="0" readonly>
                    </div>
                </div>
            </div>

            <div id="retur-warning"></div>

            <div class="d-flex gap-2 justify-content-end pb-4">
                <button type="button" class="btn btn-light" onclick="window.location.href='<?= base_url('/returbeli') ?>'">Batal</button>
                <button type="submit" class="btn btn-danger">Simpan Retur</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const mode = '<?= esc($mode) ?>';
    const existingHeader = <?= json_encode($header, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialPurchase = <?= json_encode($purchase, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialDetails = <?= json_encode($detailRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let purchaseData = initialPurchase;
    let detailRows = hydrateRows(initialDetails || []);

    $(function() {
        $('.select2').select2({
            width: '100%'
        });

        applyMoneyMask('#form-retur');
        renderPurchaseSummary();
        renderDetailTable();
        recalcSummary();
    });

    $('#beli_id').on('change', function() {
        const beliId = $(this).val();
        if (!beliId) {
            purchaseData = null;
            detailRows = [];
            renderPurchaseSummary();
            renderDetailTable();
            recalcSummary();
            return;
        }

        $.getJSON(`<?= base_url('/returbeli/source') ?>/${beliId}`, {
            retur_id: $('#retur_id').val(),
            status_retur: $('#status_retur').val()
        }, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Gagal memuat pembelian asal');
                return;
            }
            purchaseData = res.data.header || null;
            detailRows = hydrateRows(res.data.details || []);
            renderPurchaseSummary();
            renderDetailTable();
            recalcSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat pembelian asal'));
        });
    });

    $('#status_retur').on('change', function() {
        renderWarning();
    });

    function renderPurchaseSummary() {
        if (!purchaseData) {
            $('#purchase-info').addClass('d-none');
            $('#purchase-summary').empty();
            return;
        }

        $('#purchase-info').removeClass('d-none');
        $('#purchase-summary').html(`
            <div class="row g-3">
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><small class="text-muted">Supplier</small><div class="fw-semibold">${purchaseData.supplier_nama || purchaseData.supco}</div></div></div>
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><small class="text-muted">Invoice</small><div class="fw-semibold">${purchaseData.invoice || '-'}</div></div></div>
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><small class="text-muted">Total Gross</small><div class="fw-semibold">Rp ${formatMoneyValue(purchaseData.total_gross || 0)}</div></div></div>
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><small class="text-muted">Sisa Hutang Saat Ini</small><div class="fw-semibold text-danger">Rp ${formatMoneyValue(purchaseData.sisa_bayar_form || 0)}</div></div></div>
            </div>
        `);
    }

    function renderDetailTable() {
        const tbody = $('#retur-detail-table tbody');
        tbody.empty();

        if (!detailRows.length) {
            tbody.append('<tr><td colspan="10" class="text-center text-muted py-4">Pilih faktur pembelian asal terlebih dulu.</td></tr>');
            return;
        }

        detailRows.forEach((row, idx) => {
            const satOptions = (row.satuan_options || []).map((opt) => `<option value="${opt.sat_id}" data-konversi="${opt.qty_konversi}" ${String(row.sat_id || row.source_sat_id) === String(opt.sat_id) ? 'selected' : ''}>${opt.sat_id}</option>`).join('');
            const maxSelected = getMaxSelectedQty(row);
            tbody.append(`
                <tr data-index="${idx}">
                    <td class="text-center">${idx + 1}</td>
                    <td>
                        <div class="fw-semibold">${row.nama_item || row.kode_item}</div>
                        <small class="text-muted">${row.kode_item} ${row.barcode ? '/ ' + row.barcode : ''}</small>
                    </td>
                    <td>
                        <div class="text-end">${Number(row.qty_beli || 0).toLocaleString('id-ID')}</div>
                        <small class="text-muted">${row.source_sat_id}</small>
                    </td>
                    <td class="text-end">${Number(row.returned_qty_stock || 0).toLocaleString('id-ID')} <small class="text-muted d-block">stok dasar</small></td>
                    <td class="text-end">${Number(row.stok_aktual || 0).toLocaleString('id-ID')} <small class="text-muted d-block">stok dasar</small></td>
                    <td>
                        <select class="form-select form-select-sm row-sat">
                            ${satOptions}
                        </select>
                        <small class="text-muted hint-max">Maks: ${Number(maxSelected).toLocaleString('id-ID')} ${row.sat_id || row.source_sat_id}</small>
                    </td>
                    <td>
                        <input type="number" min="0" step="1" class="form-control form-control-sm text-end row-qty" value="${row.qty_retur || 0}">
                    </td>
                    <td class="text-end row-qty-stock">${Number(row.qty_stok || 0).toLocaleString('id-ID')}</td>
                    <td><input type="text" class="form-control form-control-sm money text-end row-price" value="${row.price || 0}" readonly></td>
                    <td><input type="text" class="form-control form-control-sm money text-end row-gross" value="${row.gross_retur || 0}" readonly></td>
                </tr>
            `);
        });

        applyMoneyMask('#retur-detail-table');
    }

    $('#retur-detail-table').on('change', '.row-sat', function() {
        const tr = $(this).closest('tr');
        const idx = Number(tr.data('index'));
        const row = detailRows[idx];
        const option = $(this).find(':selected');
        row.sat_id = option.val();
        row.qty_konversi = Number(option.data('konversi') || 1);
        $('#retur-detail-table .row-qty').each(function() {
            // Memicu event input untuk setiap baris .row-qty yang ada di tabel
            $(this).trigger('input');
        });
        recalcRow(row);
        renderDetailTable();
        recalcSummary();
    });

    $('#retur-detail-table').on('input', '.row-qty', function() {
        const tr = $(this).closest('tr');
        const idx = Number(tr.data('index'));
        const row = detailRows[idx];
        row.qty_retur = Number($(this).val() || 0);
        recalcRow(row);
        const maxSelected = getMaxSelectedQty(row);
        if (row.qty_retur - maxSelected > 0.0001) {
            toastr.error(`Qty retur ${row.kode_item} melebihi batas maksimal ${Number(maxSelected).toLocaleString('id-ID')} ${row.sat_id}`);
            row.qty_retur = maxSelected;
            recalcRow(row);
            renderDetailTable();
        } else {
            const currentTr = $('#retur-detail-table tbody').find(`tr[data-index="${idx}"]`);
            currentTr.find('.row-qty-stock').text(Number(row.qty_stok || 0).toLocaleString('id-ID'));
            currentTr.find('.row-price').val(row.price || 0);
            currentTr.find('.row-gross').val(row.gross_retur || 0);
            currentTr.find('.hint-max').text(`Maks: ${Number(maxSelected).toLocaleString('id-ID')} ${row.sat_id}`);
            applyMoneyMask('#retur-detail-table');
        }
        recalcSummary();
    });

    function recalcRow(row) {
        const qtyKonversi = Number(row.qty_konversi || row.source_qty_konversi || 1);
        const basePriceUnit = Number(row.base_price_unit || 0);
        row.price = roundNumber(basePriceUnit * qtyKonversi, 2);
        row.qty_stok = roundNumber(Number(row.qty_retur || 0) * qtyKonversi, 4);
        row.gross_retur = roundNumber(Number(row.qty_retur || 0) * Number(row.price || 0), 2);
    }

    function getMaxSelectedQty(row) {
        const qtyKonversi = Number(row.qty_konversi || row.source_qty_konversi || 1);
        const maxBySource = Number(row.max_qty_stock_source || 0) / qtyKonversi;
        const maxByStock = Number(row.stok_aktual || 0) / qtyKonversi;
        return roundNumber(Math.max(Math.min(maxBySource, maxByStock), 0), 2);
    }

    function recalcSummary() {
        let totalItems = 0;
        let totalQty = 0;
        let totalRetur = 0;

        detailRows.forEach((row) => {
            if (Number(row.qty_retur || 0) > 0) {
                totalItems += 1;
                totalQty += Number(row.qty_retur || 0);
                totalRetur += Number(row.gross_retur || 0);
            }
        });

        $('#sum-items').text(totalItems.toLocaleString('id-ID'));
        $('#sum-qty').text(totalQty.toLocaleString('id-ID'));
        $('#sum-total').val(totalRetur);
        applyMoneyMask('#sum-total');
        renderWarning();
    }

    function renderWarning() {
        const totalRetur = getCurrentTotalRetur();
        const sisaBayar = Number(purchaseData?.sisa_bayar_form || 0);
        const isSelesai = $('#status_retur').val() === 'SELESAI';
        const warningBox = $('#retur-warning');
        warningBox.empty();

        if (!isSelesai || !purchaseData || totalRetur <= 0) {
            return;
        }

        if (totalRetur - sisaBayar > 0.0001) {
            warningBox.html(`
                <div class="alert customize-alert alert-dismissible alert-light-danger bg-danger-subtle text-danger fade show remove-close-icon" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <div class="d-flex align-items-center me-3 me-md-0">
                        <i class="ti ti-cancel fs-5 me-2 text-danger"></i>
                        <span class="text-dark">Total retur sebesar <strong class="text-danger font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong> melebihi <strong>sisa hutang</strong> pembelian asal sebesar <strong class="text-primary">Rp ${formatMoneyValue(sisaBayar)}</strong>.</span>
                    </div>
                </div>
            `);
            return;
        }

        warningBox.html(`
            <div class="alert alert-warning border-warning-subtle">
                Saat retur diselesaikan, stok akan dikurangi dari gudang dan sistem akan mencatat <strong>POTONGAN RETUR</strong> sebesar <strong class="font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong> ke histori pembayaran pembelian.
            </div>
        `);
    }

    function getCurrentTotalRetur() {
        return detailRows.reduce((sum, row) => sum + Number(row.gross_retur || 0), 0);
    }

    $('#form-retur').on('submit', function(e) {
        e.preventDefault();

        if (!$('#beli_id').val()) {
            toastr.error('Faktur pembelian asal wajib dipilih');
            $('#beli_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            return;
        }

        $('#beli_id').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        const payloadDetails = detailRows.map((row) => ({
            kode_item: row.kode_item,
            sat_id: row.sat_id || row.source_sat_id,
            qty_retur: Number(row.qty_retur || 0),
            qty_konversi: Number(row.qty_konversi || row.source_qty_konversi || 1),
            qty_stok: Number(row.qty_stok || 0),
            price: Number(row.price || 0),
            gross_retur: Number(row.gross_retur || 0)
        }));

        const hasPositive = payloadDetails.some((row) => row.qty_retur > 0);
        if (!hasPositive) {
            toastr.error('Minimal satu item harus memiliki qty retur lebih besar dari nol');
            return;
        }

        if ($('#status_retur').val() === 'SELESAI' && getCurrentTotalRetur() - Number(purchaseData?.sisa_bayar_form || 0) > 0.0001) {
            toastr.error('Total retur melebihi sisa hutang pembelian asal');
            return;
        }

        $('#detail_json').val(JSON.stringify(payloadDetails));
        normalizeMoneyInputs('#form-retur');

        $.ajax({
            type: 'POST',
            url: '<?= base_url('/returbeli') ?>',
            dataType: 'json',
            data: $('#form-retur').serializeArray(),
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Berhasil');
                    window.location.href = '<?= base_url('/returbeli') ?>';
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan retur');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan retur pembelian'));
            }
        });
    });

    function roundNumber(value, precision = 2) {
        const factor = Math.pow(10, precision);
        return Math.round((Number(value) || 0) * factor) / factor;
    }

    function hydrateRows(rows) {
        return (rows || []).map((row) => ({
            ...row,
            qty_retur: Number(row.qty_retur || 0),
            qty_konversi: Number(row.qty_konversi || row.source_qty_konversi || 1),
            qty_stok: Number(row.qty_retur || 0) > 0 ? Number(row.qty_stok || 0) : 0,
            price: Number(row.price || row.source_price || 0),
            gross_retur: Number(row.gross_retur || 0)
        }));
    }
</script>
<?= $this->endSection('javascript') ?>