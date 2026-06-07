<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 * @var array  $kategoriOptions
 * @var array  $supplierOptions
 * @var array  $satuanOptions
 */
?>
<link rel="stylesheet" href="<?= base_url(); ?>/assets/libs/select2/dist/css/select2.min.css" />
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2"><?= $mode === 'create' ? 'Tambah' : 'Edit' ?> Data Barang</h4>
                        <p class="mb-0">Input data barang 3 langkah: master, satuan, harga toko.</p>
                    </div>
                    <div class="col-3 text-end">
                        <a onclick="window.close()" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills mb-3" id="stepTabs">
                    <li class="nav-item"><button class="nav-link active" type="button" data-step="1">Step 1 - Master</button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-step="2">Step 2 - Satuan</button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-step="3">Step 3 - Harga</button></li>
                </ul>

                <form id="item-form">
                    <input type="hidden" name="_method" id="_method" value="<?= $mode === 'create' ? 'PUT' : 'PATCH' ?>">
                    <input type="hidden" name="satuan_json" id="satuan_json">
                    <input type="hidden" name="store_json" id="store_json">

                    <div class="step-panel" data-step-panel="1">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label">Kode Item<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="kode_item" id="kode_item" readonly required value="<?= esc($formData['prodmast']['kode_item'] ?? '') ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Nama Item<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_item" id="nama_item" required value="<?= esc($formData['prodmast']['nama_item'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Barcode</label>
                                <input type="text" class="form-control" name="barcode" id="barcode" value="<?= esc($formData['prodmast']['barcode'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kategori<span class="text-danger">*</span></label>
                                <select class="form-select select2" name="kat_id" id="kat_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($kategoriOptions as $row) : ?>
                                        <option value="<?= esc($row['kat_id']) ?>" <?= ($formData['prodmast']['kat_id'] ?? '') === $row['kat_id'] ? 'selected' : '' ?>><?= esc($row['kat_id']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Supplier</label>
                                <select class="form-select select2" name="supco" id="supco">
                                    <option value="">Pilih Supplier</option>
                                    <?php foreach ($supplierOptions as $row) : ?>
                                        <option value="<?= esc($row['supco']) ?>" <?= ($formData['store_supco'] ?? '') === $row['supco'] ? 'selected' : '' ?>><?= esc($row['supco']) ?> - <?= esc($row['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status Item</label>
                                <div class="form-check form-switch mt-2">
                                    <?php $statusItem = $formData['store'][0]['status_item'] ?? 'N'; ?>
                                    <input class="form-check-input" type="checkbox" id="status_item" <?= $statusItem === 'Y' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="status_item">Aktifkan item untuk toko ini</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <input type="text" class="form-control" name="keterangan" id="keterangan" value="<?= esc($formData['prodmast']['keterangan'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="step-panel d-none" data-step-panel="2">
                        <div style="background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 12px; margin-bottom: 15px; border-radius: 4px; font-size: 14px; color: #0d47a1;">
                            <strong>💡 Petunjuk Pengisian:</strong>
                            <ul style="margin: 5px 0 0 20px; padding: 0;">
                                <li>Wajib mendaftarkan satuan terkecil dengan <strong>Qty Konversi = 1</strong> (Contoh: PCS = 1).</li>
                                <li>Satuan yang lebih besar diisi berdasarkan jumlah satuan terkecilnya (Contoh: DUS = 24, karena 1 DUS berisi 24 PCS).</li>
                                <li>Perhitungan stok akan mengacu pada qty_konversi satuan ini jika penjualan 1 DUS maka stok berkurang 24</li>
                            </ul>
                        </div>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Satuan</label>
                                <select class="form-select select2" id="sat_id_input">
                                    <option value="">Pilih Satuan</option>
                                    <?php foreach ($satuanOptions as $row) : ?>
                                        <option value="<?= esc($row['sat_id']) ?>"><?= esc($row['sat_id']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Qty Konversi</label>
                                <input type="number" min="0.0001" step="0.0001" class="form-control" id="qty_konversi_input">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn-add-satuan" class="btn btn-primary">Tambah</button>
                            </div>
                        </div>
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-sm" id="table-satuan">
                                <thead>
                                    <tr>
                                        <th>Satuan</th>
                                        <th>Qty Konversi</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="step-panel d-none" data-step-panel="3">
                        <div style="background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 12px; margin-bottom: 15px; border-radius: 4px; font-size: 14px; color: #0d47a1;">
                            <strong>💡 Petunjuk Pengisian:</strong>
                            <ul style="margin: 5px 0 0 20px; padding: 0;">
                                <li>Harga Pokok dan Harga Jual tidak boleh kosong.</li>
                                <li>Harga Jual tidak boleh lebih kecil dari Harga Pokok.</li>
                                <li>Persentase margin akan digunakan sebagai target untuk kenaikan harga otomatis dari pembelian supplier.</li>
                            </ul>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="table-harga">
                                <thead>
                                    <tr>
                                        <th>Satuan</th>
                                        <th>Qty Konversi</th>
                                        <th>Harga Pokok</th>
                                        <th>Harga Jual</th>
                                        <th>Margin %</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" id="btn-prev">Sebelumnya</button>
                        <div>
                            <button type="button" class="btn btn-primary" id="btn-next">Selanjutnya</button>
                            <button type="submit" class="btn btn-success d-none" id="btn-save">Simpan</button>
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
        $('.select2').select2({
            width: '100%'
        });
        initStepper();
        renderSatuanTable();
        syncHargaTable();
    });

    function initStepper() {
        updateStepper();
        $('#btn-prev').on('click', function() {
            if (activeStep > 1) {
                activeStep--;
                updateStepper();
            }
        });
        $('#btn-next').on('click', function() {
            if (activeStep === 1 && !validateStep1()) return;
            if (activeStep === 2 && !validateStep2()) return;
            if (activeStep < 3) {
                activeStep++;
                updateStepper();
            }
        });
        $('#stepTabs .nav-link').on('click', function() {
            const target = Number($(this).data('step'));
            if (target <= activeStep || (target === 2 && validateStep1()) || (target === 3 && validateStep1() && validateStep2())) {
                activeStep = target;
                updateStepper();
            }
        });
    }

    function updateStepper() {
        $('.step-panel').addClass('d-none');
        $(`[data-step-panel="${activeStep}"]`).removeClass('d-none');
        $('#stepTabs .nav-link').removeClass('active');
        $(`#stepTabs .nav-link[data-step="${activeStep}"]`).addClass('active');
        $('#btn-prev').prop('disabled', activeStep === 1);
        $('#btn-next').toggleClass('d-none', activeStep === 3);
        $('#btn-save').toggleClass('d-none', activeStep !== 3);
        if (activeStep === 3) syncHargaTable();
    }

    function validateStep1() {
        if (!$('#kode_item').val() || !$('#nama_item').val() || !$('#kat_id').val()) {
            toastr.error('Isi data wajib pada Step 1');
            return false;
        }
        return true;
    }

    function validateStep2() {
        if (satuanRows.length === 0) {
            toastr.error('Minimal satu satuan harus diisi');
            return false;
        }
        const hasBase = satuanRows.some(row => Number(row.qty_konversi) === 1);
        if (!hasBase) {
            toastr.error('Wajib ada satuan dasar dengan qty_konversi = 1');
            return false;
        }
        return true;
    }

    $('#btn-add-satuan').on('click', function() {
        const satId = $('#sat_id_input').val();
        const qty = Number($('#qty_konversi_input').val());
        if (!satId || !qty || qty <= 0) {
            toastr.error('Satuan dan qty wajib valid');
            return;
        }
        if (satuanRows.some(row => row.sat_id === satId)) {
            toastr.error('Satuan tidak boleh duplikat');
            return;
        }
        if (satuanRows.some(row => row.qty_konversi === qty)) {
            toastr.error(`Qty ${qty} tidak boleh duplikat`);
            return;
        }
        satuanRows.push({
            sat_id: satId,
            qty_konversi: qty
        });
        renderSatuanTable();
        syncHargaTable();
        $('#sat_id_input').val('').trigger('change');
        $('#qty_konversi_input').val('');
    });

    function renderSatuanTable() {
        const $tbody = $('#table-satuan tbody');
        $tbody.empty();
        if (satuanRows.length === 0) {
            $tbody.append('<tr><td colspan="3" class="text-center">Belum ada data</td></tr>');
            return;
        }
        satuanRows.forEach((row, idx) => {
            $tbody.append(`<tr>
                <td>${row.sat_id}</td>
                <td>${row.qty_konversi}</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-del-satuan" data-idx="${idx}">Hapus</button></td>
            </tr>`);
        });
    }

    $(document).on('click', '.btn-del-satuan', function() {
        const idx = Number($(this).data('idx'));
        satuanRows.splice(idx, 1);
        renderSatuanTable();
        syncHargaTable();
    });

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
                if (oldBasePokok > 0) {
                    hargaPokok = Math.round(oldBasePokok * newQty);
                }
                if (oldBaseJual > 0) {
                    hargaJual = Math.round(oldBaseJual * newQty);
                }
            }

            if (newQty === 1 && oldBasePokok > 0 && hargaPokok === 0) {
                hargaPokok = oldBasePokok;
            }
            if (newQty === 1 && oldBaseJual > 0 && hargaJual === 0) {
                hargaJual = oldBaseJual;
            }

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
        $tbody.empty();
        if (storeRows.length === 0) {
            $tbody.append('<tr><td colspan="5" class="text-center">Belum ada data satuan</td></tr>');
            return;
        }
        const qtyBySat = {};
        satuanRows.forEach(row => qtyBySat[row.sat_id] = Number(row.qty_konversi || 0));
        storeRows.forEach((row, idx) => {
            const qtyKonversi = qtyBySat[row.sat_id] || 0;
            const margin = row.harga_pokok > 0 ? ((((row.harga_jual - row.harga_pokok) / row.harga_pokok) * 100).toFixed(1)) : '0.0';
            $tbody.append(`<tr>
                <td>${row.sat_id}</td>
                <td class="text-end">${qtyKonversi}</td>
                <td><input type="text" class="form-control form-control-sm money harga-pokok" data-idx="${idx}" value="${row.harga_pokok}"></td>
                <td><input type="text" class="form-control form-control-sm money harga-jual" data-idx="${idx}" value="${row.harga_jual}"></td>
                <td><span class="badge bg-info-subtle text-info margin-label">${margin}%</span></td>
            </tr>`);
        });
        applyMoneyMask('#table-harga');
    }

    function updateHargaRow(idx) {
        const tr = $(`#table-harga tbody tr:eq(${idx})`);
        const hp = Number(normalizeMoneyValue(tr.find('.harga-pokok').val() || 0));
        const hj = Number(normalizeMoneyValue(tr.find('.harga-jual').val() || 0));
        storeRows[idx].harga_pokok = hp;
        storeRows[idx].harga_jual = hj;
        const margin = hp > 0 ? ((((hj - hp) / hp) * 100).toFixed(1)) : '0.0';
        tr.find('.margin-label').text(`${margin}%`);
    }

    function autoFillByBase() {
        const qtyBySat = {};
        satuanRows.forEach(row => qtyBySat[row.sat_id] = Number(row.qty_konversi || 0));
        const baseRow = storeRows.find(row => (qtyBySat[row.sat_id] || 0) === 1);
        if (!baseRow) return;
        const basePokok = Number(baseRow.harga_pokok || 0);
        const baseJual = Number(baseRow.harga_jual || 0);
        storeRows.forEach((row, idx) => {
            const qty = qtyBySat[row.sat_id] || 0;
            if (qty <= 0 || qty === 1) return;
            if (Number(row.harga_pokok) === 0 && basePokok > 0) {
                row.harga_pokok = Math.round(basePokok * qty);
                $(`#table-harga tbody tr:eq(${idx}) .harga-pokok`).val(formatMoneyValue(row.harga_pokok));
            }
            if (Number(row.harga_jual) === 0 && baseJual > 0) {
                row.harga_jual = Math.round(baseJual * qty);
                $(`#table-harga tbody tr:eq(${idx}) .harga-jual`).val(formatMoneyValue(row.harga_jual));
            }
            updateHargaRow(idx);
        });
    }

    $(document).on('blur', '.harga-pokok, .harga-jual', function() {
        const idx = Number($(this).data('idx'));
        updateHargaRow(idx);
        const satId = storeRows[idx]?.sat_id || '';
        const sat = satuanRows.find(row => row.sat_id === satId);
        if (Number(sat?.qty_konversi || 0) === 1) {
            autoFillByBase();
        }
    });

    $('#item-form').on('submit', function(e) {
        e.preventDefault();
        if (!validateStep1() || !validateStep2() || storeRows.length === 0) {
            toastr.error('Lengkapi semua data sebelum simpan');
            return;
        }
        const status = $('#status_item').is(':checked') ? 'Y' : 'N';
        $('#satuan_json').val(JSON.stringify(satuanRows));
        $('#store_json').val(JSON.stringify(storeRows));
        normalizeMoneyInputs('#table-harga');
        storeRows = storeRows.map(row => ({
            ...row,
            harga_pokok: Number(normalizeMoneyValue(row.harga_pokok)),
            harga_jual: Number(normalizeMoneyValue(row.harga_jual))
        }));
        for (const r of storeRows) {
            if (r.harga_jual == 0 || r.harga_pokok == 0) {
                toastr.error('Harga Jual atau Harga Pokok tidak boleh kosong');
                return;
            }
            if (r.harga_jual < r.harga_pokok) {
                toastr.error('Harga Jual tidak boleh lebih kecil dari harga pokok');
                return;
            }
        }
        $('#store_json').val(JSON.stringify(storeRows));
        const formData = $(this).serializeArray();
        formData.push({
            name: 'status_item',
            value: status
        });

        $.ajax({
            type: 'POST',
            url: '<?= base_url('/item') ?>',
            dataType: 'json',
            data: formData,
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Berhasil');
                    window.close();
                } else {
                    toastr.error(res.data || 'Gagal');
                }
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Terjadi kesalahan saat simpan data'));
            }
        });
    });
</script>
<?= $this->endSection('javascript') ?>
