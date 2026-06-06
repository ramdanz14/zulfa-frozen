<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 * @var array $supplierOptions
 */
$header = $formData['header'] ?? [];
$supplier = $formData['supplier'] ?? null;
$debtOptions = $formData['debt_options'] ?? [];
$detailRows = $formData['details'] ?? [];
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2"><?= $mode === 'edit' ? 'Edit Retur Pembelian' : 'Tambah Retur Pembelian' ?></h4>
                        <p class="mb-0">Pilih supplier untuk settlement, lalu tambahkan item aktif satu per satu seperti di pembelian. Stok hanya dipotong saat status `SELESAI`.</p>
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
                            <label class="form-label">Supplier</label>
                            <select class="form-select select2" name="supco" id="supco" required>
                                <option value="">Pilih supplier</option>
                                <?php foreach ($supplierOptions as $option) : ?>
                                    <option value="<?= esc($option['supco']) ?>" <?= ($header['supco'] ?? '') === $option['supco'] ? 'selected' : '' ?>>
                                        <?= esc($option['supplier_nama'] ?? $option['supco']) ?> (<?= esc($option['supco']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Penyelesaian Retur</label>
                            <select class="form-select" name="settlement_mode" id="settlement_mode">
                                <option value="POTONG_HUTANG" <?= ($header['settlement_mode'] ?? 'POTONG_HUTANG') === 'POTONG_HUTANG' ? 'selected' : '' ?>>POTONG HUTANG</option>
                                <option value="CASHBACK" <?= ($header['settlement_mode'] ?? '') === 'CASHBACK' ? 'selected' : '' ?>>CASHBACK SUPPLIER</option>
                            </select>
                        </div>
                        <div class="col-lg-8" id="debt-select-wrapper">
                            <label class="form-label">Faktur Hutang Target</label>
                            <select class="form-select select2" name="beli_id" id="beli_id">
                                <option value="">Pilih faktur hutang supplier</option>
                            </select>
                            <small class="text-muted">Dipakai hanya saat penyelesaian `POTONG HUTANG`.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" rows="2" name="keterangan" id="keterangan"><?= esc($header['keterangan'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div id="supplier-info" class="<?= $supplier ? '' : 'd-none' ?>">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Informasi Supplier Retur</h5>
                    </div>
                    <div class="card-body" id="supplier-summary"></div>
                </div>
            </div>

            <div id="debt-info" class="d-none">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Informasi Faktur Potong Hutang</h5>
                    </div>
                    <div class="card-body" id="debt-summary"></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center">
                    <div>
                        <h5 class="mb-1">Detail Item Retur</h5>
                        <small class="text-muted">Cari item aktif lalu tambahkan ke list retur.</small>
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
    const existingHeader = <?= json_encode($header, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialSupplier = <?= json_encode($supplier, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialDebtOptions = <?= json_encode($debtOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialDetails = <?= json_encode($detailRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let supplierData = initialSupplier;
    let debtOptions = initialDebtOptions || [];
    let detailRows = hydrateRows(initialDetails || []);

    $(function() {
        $('.select2').select2({
            width: '100%'
        });

        $('#item-search').select2({
            width: '100%',
            placeholder: 'Cari item / barcode',
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url('/returbeli/search-item') ?>',
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
            addItemByCode(kodeItem);
            $(this).val(null).trigger('change');
        });

        applyMoneyMask('#form-retur');
        populateDebtOptions(existingHeader.beli_id || '');
        renderSupplierSummary();
        renderDebtSummary();
        renderDetailList();
        recalcSummary();
        toggleSettlementFields();
    });

    $('#supco').on('change', function() {
        const supco = $(this).val();
        if (!supco) {
            supplierData = null;
            debtOptions = [];
            populateDebtOptions('');
            renderSupplierSummary();
            renderDebtSummary();
            recalcSummary();
            return;
        }

        $.getJSON(`<?= base_url('/returbeli/source') ?>/${supco}`, {
            retur_id: $('#retur_id').val(),
            status_retur: $('#status_retur').val(),
            beli_id: $('#beli_id').val()
        }, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Gagal memuat data supplier retur');
                return;
            }
            supplierData = res.data.header || null;
            debtOptions = res.data.debt_options || [];
            populateDebtOptions(existingHeader.beli_id || '');
            renderSupplierSummary();
            renderDebtSummary();
            recalcSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat data supplier retur'));
        });
    });

    $('#status_retur, #settlement_mode').on('change', function() {
        toggleSettlementFields();
        renderDebtSummary();
        renderWarning();
    });

    $('#beli_id').on('change', function() {
        renderDebtSummary();
        renderWarning();
    });

    function addItemByCode(kodeItem) {
        if (detailRows.some((row) => String(row.kode_item) === String(kodeItem))) {
            toastr.error('Item sudah ada di list retur');
            return;
        }

        $.getJSON(`<?= base_url('/returbeli/item-detail') ?>/${kodeItem}`, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Item tidak ditemukan');
                return;
            }

            const item = res.data || {};
            const satuan = item.satuan || [];
            const firstSat = satuan[0] || {
                sat_id: '-',
                qty_konversi: 1,
                harga_pokok: 0
            };
            detailRows.push({
                kode_item: item.kode_item,
                barcode: item.barcode || '',
                nama_item: item.nama_item || item.kode_item,
                stok_aktual: Number(item.stok_aktual || 0),
                satuan_options: satuan,
                source_sat_id: firstSat.sat_id,
                source_qty_konversi: Number(firstSat.qty_konversi || 1),
                source_price: Number(firstSat.harga_pokok || 0),
                base_price_unit: roundNumber(Number(firstSat.harga_pokok || 0) / Math.max(Number(firstSat.qty_konversi || 1), 1), 4),
                sat_id: firstSat.sat_id,
                qty_konversi: Number(firstSat.qty_konversi || 1),
                qty_retur: 0,
                qty_stok: 0,
                price: Number(firstSat.harga_pokok || 0),
                gross_retur: 0
            });
            renderDetailList();
            recalcSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat item'));
        });
    }

    function populateDebtOptions(selectedValue = '') {
        const select = $('#beli_id');
        select.empty().append('<option value="">Pilih faktur hutang supplier</option>');
        debtOptions.forEach((row) => {
            const option = new Option(
                `${row.beli_id} | ${row.invoice || '-'} | Rp ${formatMoneyValue(row.sisa_bayar_form || 0)}`,
                row.beli_id,
                false,
                String(selectedValue) === String(row.beli_id)
            );
            select.append(option);
        });
        select.trigger('change.select2');
    }

    function toggleSettlementFields() {
        const isPotongHutang = $('#settlement_mode').val() === 'POTONG_HUTANG';
        $('#debt-select-wrapper').toggleClass('d-none', !isPotongHutang);
        if (!isPotongHutang) {
            $('#debt-info').addClass('d-none');
        }
    }

    function renderSupplierSummary() {
        if (!supplierData) {
            $('#supplier-info').addClass('d-none');
            $('#supplier-summary').empty();
            return;
        }

        $('#supplier-info').removeClass('d-none');
        $('#supplier-summary').html(`
            <div class="row g-3">
                <div class="col-lg-6"><div class="border rounded p-3 h-100"><small class="text-muted">Supplier</small><div class="fw-semibold">${supplierData.supplier_nama || supplierData.supco}</div></div></div>
                <div class="col-lg-6"><div class="border rounded p-3 h-100"><small class="text-muted">Total Hutang Supplier Tersedia</small><div class="fw-semibold text-danger">Rp ${formatMoneyValue(supplierData.total_outstanding_debt || 0)}</div></div></div>
            </div>
        `);
    }

    function getSelectedDebt() {
        const selectedId = $('#beli_id').val();
        return (debtOptions || []).find((row) => String(row.beli_id) === String(selectedId)) || null;
    }

    function renderDebtSummary() {
        const isPotongHutang = $('#settlement_mode').val() === 'POTONG_HUTANG';
        const debt = getSelectedDebt();
        if (!isPotongHutang || !debt) {
            $('#debt-info').addClass('d-none');
            $('#debt-summary').empty();
            return;
        }

        $('#debt-info').removeClass('d-none');
        $('#debt-summary').html(`
            <div class="row g-3">
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><small class="text-muted">Beli ID</small><div class="fw-semibold">${debt.beli_id}</div></div></div>
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><small class="text-muted">Invoice</small><div class="fw-semibold">${debt.invoice || '-'}</div></div></div>
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><small class="text-muted">Status Bayar</small><div class="fw-semibold">${debt.status_bayar || '-'}</div></div></div>
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><small class="text-muted">Sisa Hutang</small><div class="fw-semibold text-danger">Rp ${formatMoneyValue(debt.sisa_bayar_form || 0)}</div></div></div>
            </div>
        `);
    }

    function renderDetailList() {
        const wrapper = $('#detail-list');
        wrapper.empty();

        if (!detailRows.length) {
            wrapper.html('<div class="text-center text-muted py-4">Cari item aktif untuk menambahkan ke retur.</div>');
            return;
        }

        detailRows.forEach((row, idx) => {
            const satOptions = (row.satuan_options || []).map((opt) => `<option value="${opt.sat_id}" data-konversi="${opt.qty_konversi}" data-hpp="${opt.harga_pokok || 0}" ${String(row.sat_id || row.source_sat_id) === String(opt.sat_id) ? 'selected' : ''}>${opt.sat_id}</option>`).join('');
            const maxSelected = getMaxSelectedQty(row);
            wrapper.append(`
                <div class="border rounded p-1" data-index="${idx}">
                    <div class="row align-items-center">
                        <div class="col-10 col-md-3">
                            <div class="fw-semibold">${row.nama_item || row.kode_item}</div>
                            <small class="text-muted">${row.kode_item} || Stok dasar tersedia: ${Number(row.stok_aktual || 0).toLocaleString('id-ID')}</small></br>
                            <small class="text-muted">Maks retur sesuai stok saat ini: ${Number(maxSelected).toLocaleString('id-ID')} ${row.sat_id || row.source_sat_id}. </small>

                        </div>
                         <div class="col-1 col-md-1 text-end order-md-last">
                            <button type="button" class="btn btn-sm btn-outline-danger row-delete"><i class="ti ti-trash fs-5"></i></button>
                        </div>
                        <div class="col-6 col-md-2 col-lg-2">
                            <label class="form-label">Satuan</label>
                            <select class="form-select form-select-sm row-sat">${satOptions}</select>
                        </div>
                       <div class="col-6 col-md-2 col-lg-2">
                            <label class="form-label">Qty Retur</label>
                            <input type="number" min="0" step="1" class="form-control form-control-sm text-end row-qty" value="${row.qty_retur || 0}">
                        </div>
                       
                        <div class="col-6 col-md-2 col-lg-2">
                            <label class="form-label">Price</label>
                            <input type="text" class="form-control form-control-sm money text-end row-price" value="${row.price || 0}" readonly>
                        </div>
                       <div class="col-6 col-md-2 col-lg-2">
                        <label class="form-label">Gross</label>
                            <input type="text" class="form-control form-control-sm money text-end row-gross" value="${row.gross_retur || 0}" readonly>
                        </div>
                        <input type="hidden" class="form-control form-control-sm text-end row-qty-stock" value="${Number(row.qty_stok || 0).toLocaleString('id-ID')}" readonly>
                       
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
        row.base_price_unit = roundNumber(Number(option.data('hpp') || 0) / Math.max(row.qty_konversi, 1), 4);
        recalcRow(row);
        renderDetailList();
        recalcSummary();
    });

    $('#detail-list').on('input', '.row-qty', function() {
        const card = $(this).closest('[data-index]');
        const idx = Number(card.data('index'));
        const row = detailRows[idx];
        row.qty_retur = Number($(this).val() || 0);
        recalcRow(row);
        const maxSelected = getMaxSelectedQty(row);
        if (row.qty_retur - maxSelected > 0.0001) {
            toastr.error(`Qty retur ${row.kode_item} melebihi stok tersedia`);
            row.qty_retur = maxSelected;
            recalcRow(row);
            renderDetailList();
        } else {
            card.find('.row-qty-stock').val(Number(row.qty_stok || 0).toLocaleString('id-ID'));
            card.find('.row-price').val(row.price || 0);
            card.find('.row-gross').val(row.gross_retur || 0);
            applyMoneyMask('#detail-list');
        }
        recalcSummary();
    });

    $('#detail-list').on('click', '.row-delete', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        detailRows.splice(idx, 1);
        renderDetailList();
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
        const maxByStock = Number(row.stok_aktual || 0) / qtyKonversi;
        return roundNumber(Math.max(maxByStock, 0), 2);
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
        const isSelesai = $('#status_retur').val() === 'SELESAI';
        const settlementMode = $('#settlement_mode').val();
        const debt = getSelectedDebt();
        const warningBox = $('#retur-warning');
        warningBox.empty();

        if (!isSelesai || totalRetur <= 0) {
            return;
        }

        if (settlementMode === 'POTONG_HUTANG') {
            if (!debt) {
                warningBox.html('<div class="alert alert-warning border-warning-subtle">Pilih faktur hutang supplier terlebih dulu untuk menyelesaikan retur dengan potong hutang.</div>');
                return;
            }

            if (totalRetur - Number(debt.sisa_bayar_form || 0) > 0.0001) {
                warningBox.html(`
                    <div class="alert customize-alert alert-dismissible alert-light-danger bg-danger-subtle text-danger fade show remove-close-icon" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="d-flex align-items-center me-3 me-md-0">
                            <i class="ti ti-cancel fs-5 me-2 text-danger"></i>
                            <span class="text-dark">Total retur sebesar <strong class="text-danger font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong> melebihi <strong>sisa hutang</strong> faktur terpilih sebesar <strong class="text-primary">Rp ${formatMoneyValue(debt.sisa_bayar_form || 0)}</strong>.</span>
                        </div>
                    </div>
                `);
                return;
            }

            warningBox.html(`<div class="alert alert-warning border-warning-subtle">Saat retur diselesaikan, stok akan dikurangi dan sistem akan mencatat <strong>POTONGAN RETUR</strong> sebesar <strong class="font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong> ke faktur <strong>${debt.beli_id}</strong>.</div>`);
            return;
        }

        warningBox.html(`<div class="alert alert-info border-info-subtle">Saat retur diselesaikan, stok akan dikurangi dan sistem akan mencatat <strong>kas masuk</strong> akun <strong>RETUR PEMBELIAN</strong> sebesar <strong class="font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong> dengan keterangan nomor retur ini.</div>`);
    }

    function getCurrentTotalRetur() {
        return detailRows.reduce((sum, row) => sum + Number(row.gross_retur || 0), 0);
    }

    $('#form-retur').on('submit', function(e) {
        e.preventDefault();

        if (!$('#supco').val()) {
            toastr.error('Supplier wajib dipilih');
            $('#supco').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            return;
        }
        $('#supco').next('.select2-container').find('.select2-selection').removeClass('is-invalid');

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

        if ($('#status_retur').val() === 'SELESAI' && $('#settlement_mode').val() === 'POTONG_HUTANG') {
            const debt = getSelectedDebt();
            if (!debt) {
                toastr.error('Faktur hutang target wajib dipilih');
                return;
            }
            if (getCurrentTotalRetur() - Number(debt.sisa_bayar_form || 0) > 0.0001) {
                toastr.error('Total retur melebihi sisa hutang pada faktur yang dipilih');
                return;
            }
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
            stok_aktual: Number(row.stok_aktual || 0),
            qty_retur: Number(row.qty_retur || 0),
            qty_konversi: Number(row.qty_konversi || row.source_qty_konversi || 1),
            qty_stok: Number(row.qty_retur || 0) > 0 ? Number(row.qty_stok || 0) : 0,
            price: Number(row.price || row.source_price || 0),
            gross_retur: Number(row.gross_retur || 0)
        }));
    }
</script>
<?= $this->endSection('javascript') ?>