<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 * @var array $supplierOptions
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2"><?= $mode === 'create' ? 'Tambah' : 'Edit' ?> Pembelian</h4>
                        <p class="mb-0">Kelola draft PO, penerimaan barang, dan pembayaran supplier dalam satu form.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/pembelian') ?>" class="btn btn-secondary btn-sm">Kembali ke List</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="pembelian-form">
            <input type="hidden" name="_method" value="<?= $mode === 'create' ? 'PUT' : 'PATCH' ?>">
            <input type="hidden" name="detail_json" id="detail_json">
            <input type="hidden" name="payment_json" id="payment_json">

            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Pembelian</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">ID Pembelian</label>
                                    <input type="text" class="form-control" name="beli_id" id="beli_id" readonly value="<?= esc($formData['header']['beli_id'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" id="tanggal" value="<?= esc($formData['header']['tanggal'] ?? date('Y-m-d')) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status Nota</label>
                                    <select class="form-select" name="status_nota" id="status_nota">
                                        <option value="PO" <?= ($formData['header']['status_nota'] ?? 'PO') === 'PO' ? 'selected' : '' ?>>PO / Draft</option>
                                        <option value="TERIMA" <?= ($formData['header']['status_nota'] ?? '') === 'TERIMA' ? 'selected' : '' ?>>TERIMA / Barang Masuk</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Supplier</label>
                                    <select class="form-select select2" name="supco" id="supco" required>
                                        <option value="">Pilih Supplier</option>
                                        <?php foreach ($supplierOptions as $row) : ?>
                                            <option value="<?= esc($row['supco']) ?>" <?= ($formData['header']['supco'] ?? '') === $row['supco'] ? 'selected' : '' ?>>
                                                <?= esc($row['supco']) ?> - <?= esc($row['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Invoice Supplier</label>
                                    <input type="text" class="form-control" name="invoice" id="invoice" required value="<?= esc($formData['header']['invoice'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" class="form-control" name="keterangan" id="keterangan" value="<?= esc($formData['header']['keterangan'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center">
                            <div>
                                <h5 class="mb-1">Detail Item</h5>
                                <small class="text-muted">Cari item dengan nama, kode, atau barcode lalu tambahkan ke tabel.</small>
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
                                            <th style="min-width: 140px;">Satuan</th>
                                            <th style="min-width: 110px;">Qty Beli</th>
                                            <th style="min-width: 170px;">Hint Stok</th>
                                            <th style="min-width: 140px;">Price</th>
                                            <th style="min-width: 140px;">Gross</th>
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
                    <div class="card" id="summary-card">
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
                                <span class="text-muted">Total Gross</span>
                                <span class="fw-semibold" id="summary-gross">Rp 0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Pembayaran Existing</span>
                                <span class="fw-semibold" id="summary-existing-paid">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Pembayaran Form</span>
                                <span class="fw-semibold" id="summary-form-paid">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Sisa Bayar</span>
                                <span class="fw-semibold text-danger" id="summary-sisa">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($formData['payments'])) : ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Histori Pembayaran Tersimpan</h5>
                            </div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Metode</th>
                                                <th>Bank</th>
                                                <th>Nominal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($formData['payments'] as $row) : ?>
                                                <tr>
                                                    <td><?= esc($row['tanggal_bayar']) ?></td>
                                                    <td><?= esc($row['cara_bayar']) ?></td>
                                                    <td><?= esc(($row['bank_nama'] ?? '-') . ' / ' . ($row['rekening_no'] ?? '-')) ?></td>
                                                    <td class="text-end">Rp <?= digit_group($row['jumlah_bayar']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted d-block mt-2">Untuk menambah cicilan baru gunakan menu monitoring hutang agar histori tetap konsisten.</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="btn-save">Simpan Pembelian</button>
                            <a href="<?= base_url('/hutang') ?>" class="btn btn-outline-danger">Buka Monitoring Hutang</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="payment-modal" data-bs-focus="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Pembayaran Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted">Total Gross</small>
                            <div class="fw-semibold" id="modal-total-gross">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted">Pembayaran Existing</small>
                            <div class="fw-semibold" id="modal-existing-paid">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted">Sisa Setelah Form</small>
                            <div class="fw-semibold text-danger" id="modal-remaining">Rp 0</div>
                        </div>
                    </div>
                </div>

                <div id="credit-alert-container" class="d-none"></div>

                <div class="mb-3 d-none" id="existing-payment-wrapper">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-1">Histori Pembayaran Tersimpan</h6>
                            <small class="text-muted">Data existing readonly. Hapus dulu jika perlu koreksi total bayar sebelum simpan ulang.</small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" id="existing-payment-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Metode</th>
                                    <th>Tanggal/Jam</th>
                                    <th>Nominal</th>
                                    <th>Bank / Rekening</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h6 class="mb-1">Pembayaran Awal</h6>
                        <small class="text-muted">Boleh campur tunai dan transfer dalam beberapa baris.</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-add-payment">Tambah</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" id="payment-table">
                        <thead class="table-light">
                            <tr>
                                <th>Metode</th>
                                <th>Tanggal/Jam</th>
                                <th>Nominal</th>
                                <th>Bank / Rekening</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="border rounded p-3 mt-3 d-none" id="credit-box">
                    <div class="fw-semibold mb-2 text-danger">Sisa pembayaran akan dicatat sebagai hutang supplier.</div>
                    <label class="form-label">Tanggal Jatuh Tempo</label>
                    <input type="date" class="form-control" name="jatuh_tempo" id="jatuh_tempo" min="<?= date('Y-m-d') ?>" value="<?= esc($formData['header']['jatuh_tempo'] ?? date('Y-m-d', strtotime('+1 month'))) ?>">
                    <small class="text-muted mt-2 d-block">Default jatuh tempo satu bulan ke depan dan tidak boleh lebih kecil dari hari ini.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-confirm-save">Simpan Pembelian</button>
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
        applyMoneyMask('#payment-table');
        applyMoneyMask('#existing-payment-table');
        console.log('jalankan mask dari event');
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

        renderDetailTable();
        renderExistingPaymentTable();
        bindEvents();
        updateSummary();
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

    function bindEvents() {
        $('#status_nota').on('change', updateSummary);

        $('#supco').on('change', function() {
            toggleInvalidState($(this), !!$(this).val());
        });

        $('#invoice').on('input', function() {
            toggleInvalidState($(this), $.trim($(this).val()) !== '');
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
            renderPaymentTable();
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
            detailRows.push({
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
            renderDetailTable();
            updateSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal mengambil detail item'));
        });
    }

    function renderDetailTable() {
        const $tbody = $('#detail-table tbody');
        $tbody.empty();
        if (!detailRows.length) {
            $tbody.append('<tr><td colspan="7" class="text-center text-muted py-4">Belum ada item dipilih</td></tr>');
            return;
        }

        detailRows.forEach((row, idx) => {
            const satuanOptions = (row.satuan_options || []).map(opt => `
                <option value="${opt.sat_id}" ${opt.sat_id === row.sat_id ? 'selected' : ''} data-qty="${opt.qty_konversi}" data-price="${opt.harga_pokok}">
                    ${opt.sat_id} (${Number(opt.qty_konversi).toLocaleString('id-ID')})
                </option>
            `).join('');

            $tbody.append(`
                <tr data-idx="${idx}">
                    <td>
                        <div class="fw-semibold">${row.kode_item} - ${row.nama_item}</div>
                    </td>
                    <td>
                        <select class="form-select form-select-sm row-satuan">
                            ${satuanOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" min="0.01" step="0.01" class="form-control form-control-sm row-qty" value="${row.qty_beli}">
                    </td>
                    <td><small class="text-muted row-stock-hint">${stockHint(row)}</small></td>
                    <td>
                        <input type="text" class="form-control form-control-sm money row-price" value="${row.price}" data-last="price">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm money row-gross" value="${row.gross}" data-last="gross">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger row-delete"><i class="ti ti-trash"></i></button>
                    </td>
                </tr>
            `);
        });

        applyMoneyMask('#detail-table');
        bindDetailRowEvents();
    }

    function bindDetailRowEvents() {
        $('#detail-table .row-satuan').off('change').on('change', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            const selected = $(this).find(':selected');
            const defaultPrice = Number(selected.data('price') || 0);
            detailRows[idx].sat_id = selected.val();
            detailRows[idx].qty_konversi = Number(selected.data('qty') || 1);
            detailRows[idx].qty_stock = Number(detailRows[idx].qty_beli || 0) * detailRows[idx].qty_konversi;
            detailRows[idx].price = defaultPrice;
            detailRows[idx].gross = Math.round(Number(detailRows[idx].qty_beli || 0) * Number(detailRows[idx].price || 0));
            syncDetailRow(idx);
            updateSummary();
        });

        $('#detail-table .row-qty').off('input').on('input', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            detailRows[idx].qty_beli = Number($(this).val() || 0);
            detailRows[idx].qty_stock = detailRows[idx].qty_beli * Number(detailRows[idx].qty_konversi || 1);
            detailRows[idx].gross = Math.round(detailRows[idx].qty_beli * Number(detailRows[idx].price || 0));
            syncDetailRow(idx);
            updateSummary();
        });

        $('#detail-table .row-price').off('input blur').on('input blur', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            detailRows[idx].price = Number(normalizeMoneyValue($(this).val() || 0));
            detailRows[idx].gross = Math.round(Number(detailRows[idx].qty_beli || 0) * detailRows[idx].price);
            syncDetailRow(idx);
            updateSummary();
        });

        $('#detail-table .row-gross').off('input blur').on('input blur', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            detailRows[idx].gross = Number(normalizeMoneyValue($(this).val() || 0));
            const qty = Number(detailRows[idx].qty_beli || 0);
            detailRows[idx].price = qty > 0 ? Math.round(detailRows[idx].gross / qty) : 0;
            syncDetailRow(idx);
            updateSummary();
        });

        $('#detail-table .row-delete').off('click').on('click', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            detailRows.splice(idx, 1);
            renderDetailTable();
            updateSummary();
        });
    }

    function syncDetailRow(idx) {
        const tr = $(`#detail-table tbody tr[data-idx="${idx}"]`);
        tr.find('.row-stock-hint').text(stockHint(detailRows[idx]));
        tr.find('.row-price').val(formatMoneyValue(detailRows[idx].price));
        tr.find('.row-gross').val(formatMoneyValue(detailRows[idx].gross));
    }

    function stockHint(row) {
        const qtyStock = Number(row.qty_beli || 0) * Number(row.qty_konversi || 1);
        return `Stok bertambah ${qtyStock.toLocaleString('id-ID')} ${row.base_sat_id || row.sat_id}`;
    }

    function renderPaymentTable() {
        const $tbody = $('#payment-table tbody');
        $tbody.empty();
        if (!paymentRows.length) {
            $tbody.append('<tr><td colspan="5" class="text-center text-muted">Belum ada pembayaran awal</td></tr>');
            updateSummary();
            return;
        }
        paymentRows.forEach((row, idx) => {
            const transferBox = row.cara_bayar === 'TRANSFER' ? `
                <input type="text" class="form-control form-control-sm mb-1 pay-bank" placeholder="Nama bank" value="${row.bank_nama || ''}">
                <input type="text" class="form-control form-control-sm pay-rekening" placeholder="No rekening" value="${row.rekening_no || ''}">
            ` : '<small class="text-muted">Tidak diperlukan untuk tunai</small>';

            $tbody.append(`
                <tr data-idx="${idx}">
                    <td>
                        <select class="form-select form-select-sm pay-method">
                            <option value="TUNAI" ${row.cara_bayar === 'TUNAI' ? 'selected' : ''}>TUNAI</option>
                            <option value="TRANSFER" ${row.cara_bayar === 'TRANSFER' ? 'selected' : ''}>TRANSFER</option>
                        </select>
                    </td>
                    <td><input type="datetime-local" class="form-control form-control-sm pay-date" value="${toDatetimeLocal(row.tanggal_bayar)}"></td>
                    <td><input type="text" class="form-control form-control-sm money pay-amount"  value="${row.jumlah_bayar}"></td>
                    <td>${transferBox}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger pay-delete"><i class="ti ti-trash"></i></button>
                    </td>
                </tr>
            `);
        });

        applyMoneyMask('#payment-table');
        bindPaymentEvents();
        updateSummary();
    }

    function renderExistingPaymentTable() {
        const $wrapper = $('#existing-payment-wrapper');
        const $tbody = $('#existing-payment-table tbody');
        const rows = existingPaymentRows.filter(row => !row.deleted);
        $tbody.empty();

        if (!rows.length) {
            $wrapper.addClass('d-none');
            return;
        }

        $wrapper.removeClass('d-none');
        rows.forEach((row) => {
            const bankText = row.cara_bayar === 'TRANSFER' ? `${row.bank_nama || '-'} / ${row.rekening_no || '-'}` : 'Tunai';
            $tbody.append(`
                <tr data-bayar-id="${row.bayar_id}">
                    <td><input type="text" class="form-control form-control-sm" readonly value="${row.cara_bayar}"></td>
                    <td><input type="text" class="form-control form-control-sm" readonly value="${String(row.tanggal_bayar).replace('T', ' ')}"></td>
                    <td><input type="text" class="form-control form-control-sm money" readonly value="${row.jumlah_bayar}"></td>
                    <td><input type="text" class="form-control form-control-sm" readonly value="${bankText}"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger existing-pay-delete" data-bayar-id="${row.bayar_id}"><i class="ti ti-trash"></i></button>
                    </td>
                </tr>
            `);
        });

        applyMoneyMask('#existing-payment-table');
        $('#existing-payment-table .existing-pay-delete').off('click').on('click', function() {
            const bayarId = Number($(this).data('bayar-id'));
            const target = existingPaymentRows.find(row => row.bayar_id === bayarId);
            if (!target) return;
            target.deleted = true;
            renderExistingPaymentTable();
            updateSummary();
        });
    }

    function bindPaymentEvents() {
        $('#payment-table .pay-method').off('change').on('change', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            paymentRows[idx].cara_bayar = $(this).val();
            if (paymentRows[idx].cara_bayar === 'TUNAI') {
                paymentRows[idx].bank_nama = '';
                paymentRows[idx].rekening_no = '';
            }
            renderPaymentTable();
        });

        $('#payment-table .pay-date').off('change').on('change', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            paymentRows[idx].tanggal_bayar = $(this).val();
        });

        $('#payment-table .pay-amount').on('input blur', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            paymentRows[idx].jumlah_bayar = Number(normalizeMoneyValue($(this).val() || 0));
            updateSummary();
        });

        $('#payment-table .pay-bank').off('input').on('input', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            paymentRows[idx].bank_nama = $(this).val();
        });

        $('#payment-table .pay-rekening').off('input').on('input', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            paymentRows[idx].rekening_no = $(this).val();
        });

        $('#payment-table .pay-delete').off('click').on('click', function() {
            const idx = Number($(this).closest('tr').data('idx'));
            paymentRows.splice(idx, 1);
            renderPaymentTable();
        });
    }

    function updateSummary() {
        const totalQty = detailRows.reduce((sum, row) => sum + Number(row.qty_beli || 0), 0);
        const totalGross = detailRows.reduce((sum, row) => sum + Number(row.gross || 0), 0);
        const formPaid = paymentRows.reduce((sum, row) => sum + Number(row.jumlah_bayar || 0), 0);
        const activeExistingPaid = existingPaymentRows
            .filter(row => !row.deleted)
            .reduce((sum, row) => sum + Number(row.jumlah_bayar || 0), 0);
        const remaining = Math.max(totalGross - activeExistingPaid - formPaid, 0);
        const isTerima = $('#status_nota').val() === 'TERIMA';
        const isKredit = isTerima && remaining > 0;

        $('#summary-item').text(detailRows.length.toLocaleString('id-ID'));
        $('#summary-qty').text(totalQty.toLocaleString('id-ID'));
        $('#summary-gross').text(`Rp ${formatMoneyValue(totalGross)}`);
        $('#summary-existing-paid').text(`Rp ${formatMoneyValue(activeExistingPaid)}`);
        $('#summary-form-paid').text(`Rp ${formatMoneyValue(formPaid)}`);
        $('#summary-sisa').text(`Rp ${formatMoneyValue(remaining)}`);
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
        renderPaymentTable();
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

    function normalizeDateTime(value) {
        return value ? value.replace('T', ' ') + ':00' : '';
    }
</script>
<?= $this->endSection('javascript') ?>