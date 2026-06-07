<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$header = $formData['header'] ?? [];
$sale = $formData['sale'] ?? null;
$prefillError = $formData['error'] ?? null;
$mode = $mode ?? 'create';
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-2">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2"><?= $mode === 'edit' ? 'Edit Retur Penjualan' : 'Tambah Retur Penjualan' ?></h4>
                        <p class="mb-0">Input retur penjualan dari no struk. Detail item dibuat dalam list responsif agar nyaman di HP.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/returjual') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke History</a>
                    </div>
                </div>
            </div>
        </div>

        <form id="returjual-form">
            <input type="hidden" id="rj_id" value="<?= esc($header['rj_id'] ?? '') ?>">

            <div class="card">
                <div class="card-header py-1 bg-light">
                    <h5 class="mb-0">Header Retur</h5>
                </div>
                <div class="card-body py-1">
                    <div class="row g-3">
                        <div class="col-lg-3">
                            <label class="form-label">ID Retur</label>
                            <input type="text" class="form-control" value="<?= esc($header['rj_id'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Tanggal Retur</label>
                            <input type="date" class="form-control" id="tanggal" value="<?= esc($header['tanggal'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">No Struk / Jual ID</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="jual_id" value="<?= esc($header['jual_id'] ?? '') ?>" placeholder="Contoh: JLTK012606070001" <?= $mode === 'edit' ? 'readonly' : '' ?>>
                                <?php if ($mode !== 'edit') : ?>
                                    <button type="button" class="btn btn-primary" id="btn-load-sale">Load</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" class="form-control" id="keterangan" value="<?= esc($header['keterangan'] ?? '') ?>" placeholder="Opsional">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="card">
                    <div class="card-header py-1 bg-light">
                        <h5 class="mb-0 py-1">Referensi Penjualan</h5>
                    </div>
                    <div class="card-body" id="sale-summary"></div>
                </div>
                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 py-1">Detail Item Retur</h5>
                        </div>
                        <div class="card-body p-2">
                            <div id="detail-list" class="d-grid gap-2"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">


                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Summary Retur</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Jumlah Item Retur</span>
                                <span class="fw-semibold" id="sum-items">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Qty Retur</span>
                                <span class="fw-semibold" id="sum-qty">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Total Refund</span>
                                <span class="fw-semibold text-danger" id="sum-total">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div id="warning-box"></div>

                    <div class="card">
                        <div class="card-body d-grid gap-2">
                            <button type="submit" class="btn btn-danger"><?= $mode === 'edit' ? 'Update Retur Penjualan' : 'Simpan Retur Penjualan' ?></button>
                            <a href="<?= base_url('/returjual') ?>" class="btn btn-light">Kembali ke History</a>
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
    const prefillError = <?= json_encode($prefillError, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let saleData = <?= json_encode($sale, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let detailRows = hydrateDetails(saleData?.details || []);

    $(function() {
        if (prefillError) {
            toastr.error(prefillError);
        }
        renderSaleSummary();
        renderDetailList();
        recalcSummary();
    });

    $('#btn-load-sale').on('click', function() {
        loadSaleReference();
    });

    $('#jual_id').on('keypress', function(e) {
        if (e.which === 13 && mode !== 'edit') {
            e.preventDefault();
            loadSaleReference();
        }
    });

    $('#detail-list').on('input', '.row-qty-retur', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        const row = detailRows[idx];
        row.qty_retur = Number($(this).val() || 0);
        if (row.qty_retur - Number(row.qty_jual || 0) > 0.0001) {
            toastr.error(`Qty retur ${row.kode_item} tidak boleh melebihi qty jual`);
            row.qty_retur = Number(row.qty_jual || 0);
        }
        row.gross_retur = round2(Number(row.qty_retur || 0) * Number(row.refund_unit || 0));
        renderDetailList();
        recalcSummary();
    });

    $('#returjual-form').on('submit', function(e) {
        e.preventDefault();

        if (!saleData?.jual_id) {
            toastr.error('Load transaksi penjualan dulu sebelum menyimpan retur');
            return;
        }

        const payloadDetails = detailRows
            .filter(row => Number(row.qty_retur || 0) > 0)
            .map(row => ({
                seq_no: row.seq_no,
                qty_retur: Number(row.qty_retur || 0),
                refund_unit: Number(row.refund_unit || 0)
            }));

        if (payloadDetails.length === 0) {
            toastr.error('Minimal satu item harus memiliki qty retur lebih besar dari nol');
            return;
        }

        $.ajax({
            type: mode === 'edit' ? 'PATCH' : 'PUT',
            url: '<?= base_url('/returjual') ?>',
            dataType: 'json',
            data: {
                rj_id: $('#rj_id').val(),
                tanggal: $('#tanggal').val(),
                jual_id: $('#jual_id').val().trim(),
                keterangan: $('#keterangan').val().trim(),
                detail_json: JSON.stringify(payloadDetails)
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Retur penjualan berhasil disimpan');
                    window.location.href = '<?= base_url('/returjual') ?>';
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan retur penjualan');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan retur penjualan'));
            }
        });
    });

    function loadSaleReference() {
        const jualId = $('#jual_id').val().trim();
        if (!jualId) {
            toastr.error('No struk / jual_id wajib diisi');
            return;
        }

        $.getJSON(`<?= base_url('/returjual/sale') ?>/${encodeURIComponent(jualId)}`, function(res) {
            if (res.tipe !== 'success') {
                saleData = null;
                detailRows = [];
                renderSaleSummary();
                renderDetailList();
                recalcSummary();
                toastr.error(res.data || 'Transaksi penjualan tidak valid untuk retur');
                return;
            }

            saleData = res.data || null;
            detailRows = hydrateDetails(saleData?.details || []);
            renderSaleSummary();
            renderDetailList();
            recalcSummary();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat referensi penjualan'));
        });
    }

    function renderSaleSummary() {
        const wrapper = $('#sale-summary');
        if (!saleData) {
            wrapper.html('<div class="text-muted">Masukkan no struk lalu klik Load.</div>');
            return;
        }

        wrapper.html(`
            <div class="row g-1">
                <div class="col-md-3"><div class="border rounded p-1 h-100"><small class="text-muted">No Struk</small><div class="fw-semibold">${saleData.jual_id || '-'}</div></div></div>
                <div class="col-md-2"><div class="border rounded p-1 h-100"><small class="text-muted">Tanggal Jual</small><div class="fw-semibold">${saleData.tgl ? new Date(saleData.tgl).toLocaleString('id-ID') : '-'}</div></div></div>
                <div class="col-md-2"><div class="border rounded p-1 h-100"><small class="text-muted">Customer</small><div class="fw-semibold">${saleData.customer_nama || 'Pelanggan Umum'}</div></div></div>
                <div class="col-md-2"><div class="border rounded p-1 h-100"><small class="text-muted">Kasir</small><div class="fw-semibold">${saleData.updid || '-'}</div></div></div>
                <div class="col-md-2"><div class="border rounded p-1 h-100"><small class="text-muted">Netto Jual</small><div class="fw-semibold">Rp ${formatMoneyValue(saleData.netto || 0)}</div></div></div>
                <div class="col-md-1"><div class="border rounded p-1 h-100"><small class="text-muted">Umur</small><div class="fw-semibold">${Number(saleData.umur_hari || 0).toLocaleString('id-ID')} hari</div></div></div>
            </div>
        `);
    }

    function renderDetailList() {
        const wrapper = $('#detail-list');
        wrapper.empty();

        if (!detailRows.length) {
            wrapper.html('<div class="text-center text-muted py-4">Belum ada referensi penjualan yang dimuat.</div>');
            return;
        }

        detailRows.forEach((row, idx) => {
            wrapper.append(`
                <div class="border rounded p-1" data-index="${idx}">                    
                    <div class="row g-3">
                        <div class="col-12 col-md-3 d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">${escapeHtml(row.nama_item || row.kode_item)}</div>
                                <small class="text-muted">${escapeHtml(row.kode_item || '-')}</small>
                                <span class="badge bg-light text-dark">${escapeHtml(row.sat_id || '-')}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Qty Jual</label>
                            <input type="text" class="form-control form-control-sm text-end" value="${Number(row.qty_jual || 0).toLocaleString('id-ID')}" readonly>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Qty Retur</label>
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end row-qty-retur" value="${row.qty_retur || 0}">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label">Refund/Satuan</label>
                            <input type="text" class="form-control form-control-sm text-end" value="Rp ${formatMoneyValue(row.refund_unit || 0)}" readonly>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Total Refund</label>
                            <input type="text" class="form-control form-control-sm text-end" value="Rp ${formatMoneyValue(row.gross_retur || 0)}" readonly>
                        </div>
                    </div>
                </div>
            `);
        });
    }

    function recalcSummary() {
        const totalItems = detailRows.filter(row => Number(row.qty_retur || 0) > 0).length;
        const totalQty = detailRows.reduce((sum, row) => sum + Number(row.qty_retur || 0), 0);
        const totalRetur = detailRows.reduce((sum, row) => sum + Number(row.gross_retur || 0), 0);
        $('#sum-items').text(totalItems.toLocaleString('id-ID'));
        $('#sum-qty').text(totalQty.toLocaleString('id-ID'));
        $('#sum-total').text('Rp ' + formatMoneyValue(totalRetur));
        renderWarning(totalRetur);
    }

    function renderWarning(totalRetur) {
        const wrapper = $('#warning-box');
        wrapper.empty();
        if (!saleData || totalRetur <= 0) {
            return;
        }

        wrapper.html(`
            <div class="alert alert-warning border-warning-subtle">
                Saat retur disimpan, sistem akan menambah stok kembali, membentuk <strong>kas keluar</strong> akun <strong>RETUR PENJUALAN</strong> sebesar <strong class="font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong>, dan transaksi ini tidak bisa diretur ulang.
            </div>
        `);
    }

    function hydrateDetails(rows) {
        return (rows || []).map(row => ({
            ...row,
            qty_jual: Number(row.qty_jual || 0),
            qty_retur: Number(row.qty_retur || 0),
            refund_unit: Number(row.refund_unit || row.price || 0),
            gross_retur: Number(row.gross_retur || 0)
        }));
    }

    function round2(value) {
        return Math.round((Number(value) || 0) * 100) / 100;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
<?= $this->endSection('javascript') ?>