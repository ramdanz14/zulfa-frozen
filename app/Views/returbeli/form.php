<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 * @var array $supplierOptions
 * @var array $formData
 */
$header = $formData['header'] ?? [];
$supplier = $formData['supplier'] ?? null;
$debtOptions = $formData['debt_options'] ?? [];
$detailRows = $formData['details'] ?? [];
?>
<style>
/* =========================================================
   RETUR BELI WORKSPACE STYLING (Optimized for Mobile & Tablet)
   ========================================================= */
.returbeli-container {
    padding-bottom: 90px; /* Space for mobile sticky action bar */
}

/* Compact Collapsible Header */
.retur-header-card {
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.08);
    transition: all 0.2s ease;
}
.header-tag-pill {
    font-size: 0.75rem;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    background: #f1f5f9;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

/* Item Workspace (Dominant Area) */
.item-workspace-card {
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}
.item-search-wrapper .select2-container--default .select2-selection--single {
    height: 46px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    border: 2px solid #cbd5e1;
    transition: border-color 0.2s;
}
.item-search-wrapper .select2-container--default .select2-selection--single:focus,
.item-search-wrapper .select2-container--default.select2-container--open .select2-selection--single {
    border-color: var(--bs-primary);
}
.item-search-wrapper .select2-selection__rendered {
    font-size: 0.95rem;
    font-weight: 500;
    padding-left: 12px !important;
}

/* Detail Item Cards */
.detail-item-card {
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    transition: box-shadow 0.15s ease, border-color 0.15s ease;
    position: relative;
    padding: 0.85rem;
}
.detail-item-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}
.detail-item-card .item-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.3;
}
.detail-item-card .item-code {
    font-size: 0.75rem;
    color: #64748b;
    font-family: var(--bs-font-monospace);
    background: #f8fafc;
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-block;
}
.detail-item-card .row-stock-hint {
    font-size: 0.75rem;
    color: #059669;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}
.detail-input-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}
@media (min-width: 768px) {
    .detail-input-grid {
        grid-template-columns: 140px 110px 1fr 1fr;
    }
}
.input-label-compact {
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 0.2rem;
    display: flex;
    align-items: center;
    gap: 3px;
}
.form-control-touch {
    height: 40px;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 6px;
}

/* Delete item button */
.btn-delete-item {
    width: 38px;
    height: 38px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: #dc3545;
    background: #fff5f5;
    border: 1px solid #fed7d7;
    transition: all 0.15s;
}
.btn-delete-item:hover, .btn-delete-item:active {
    background: #dc3545;
    color: #ffffff;
    border-color: #dc3545;
}

/* Mobile Sticky Action Bar */
.mobile-sticky-action-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
    box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
    z-index: 1040;
}

/* Desktop summary sidebar sticky */
@media (min-width: 1200px) {
    .sticky-summary {
        position: sticky;
        top: 80px;
    }
}
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0 returbeli-container">
        <!-- Breadcrumb / Header Banner -->
        <div class="card bg-danger-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-2 px-md-4 py-md-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                            <i class="ti ti-arrow-back-up fs-6 text-white"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark"><?= $mode === 'edit' ? 'Edit' : 'Tambah' ?> Retur Pembelian</h5>
                            <small class="text-muted d-none d-sm-inline">Pengembalian barang ke supplier, potong hutang atau cashback</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= base_url('/returbeli') ?>" class="btn btn-secondary btn-sm d-inline-flex align-items-center gap-1">
                            <i class="ti ti-arrow-left"></i> Kembali ke List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="form-retur">
            <input type="hidden" name="_method" value="<?= $mode === 'edit' ? 'PATCH' : 'PUT' ?>">
            <input type="hidden" name="retur_id" id="retur_id" value="<?= esc($header['retur_id'] ?? '') ?>">
            <input type="hidden" name="detail_json" id="detail_json">

            <div class="row g-3">
                <!-- MAIN WORKSPACE: Item Area & Compact Header -->
                <div class="col-xl-8">
                    
                    <!-- COMPACT INFO RETUR (Collapsible on Mobile, streamlined on Tablet/Desktop) -->
                    <div class="card retur-header-card mb-3">
                        <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapseHeaderInfo" aria-expanded="true">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold text-dark fs-3"><i class="ti ti-file-text text-danger me-1"></i> Data Transaksi & Supplier</span>
                                <span class="header-tag-pill" id="header-chip-sup">
                                    <i class="ti ti-building-store"></i> <span id="chip-sup-text">Pilih Supplier</span>
                                </span>
                                <span class="header-tag-pill" id="header-chip-settle">
                                    <i class="ti ti-receipt-2"></i> <span id="chip-settle-text">POTONG HUTANG</span>
                                </span>
                                <span class="badge bg-light-warning text-warning fw-semibold" id="chip-status-text">DRAFT</span>
                            </div>
                            <div class="text-muted small d-flex align-items-center gap-1">
                                <span class="d-none d-sm-inline">Ubah Info</span>
                                <i class="ti ti-chevron-down fs-4 transition-all" id="header-collapse-icon"></i>
                            </div>
                        </div>
                        <div class="collapse show" id="collapseHeaderInfo">
                            <div class="card-body p-3 bg-light-subtle border-top">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3">
                                        <label class="input-label-compact">ID Retur</label>
                                        <input type="text" class="form-control form-control-sm font-monospace bg-white" value="<?= esc($header['retur_id'] ?? '') ?>" readonly placeholder="(Auto)">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="input-label-compact">Tanggal Retur <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm bg-white" name="tanggal" id="tanggal" value="<?= esc($header['tanggal'] ?? date('Y-m-d')) ?>" required>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="input-label-compact">Status Retur <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm bg-white fw-semibold" name="status_retur" id="status_retur">
                                            <option value="DRAFT" <?= ($header['status_retur'] ?? 'DRAFT') === 'DRAFT' ? 'selected' : '' ?>>DRAFT (Draft PO)</option>
                                            <option value="SELESAI" <?= ($header['status_retur'] ?? '') === 'SELESAI' ? 'selected' : '' ?>>SELESAI (Potong Stok)</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="input-label-compact">Penyelesaian Retur <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm bg-white fw-semibold" name="settlement_mode" id="settlement_mode">
                                            <option value="POTONG_HUTANG" <?= ($header['settlement_mode'] ?? 'POTONG_HUTANG') === 'POTONG_HUTANG' ? 'selected' : '' ?>>POTONG HUTANG</option>
                                            <option value="CASHBACK" <?= ($header['settlement_mode'] ?? '') === 'CASHBACK' ? 'selected' : '' ?>>CASHBACK SUPPLIER</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="input-label-compact">Supplier <span class="text-danger">*</span></label>
                                        <select class="form-select select2" name="supco" id="supco" required>
                                            <option value="">Pilih Supplier</option>
                                            <?php foreach ($supplierOptions as $option) : ?>
                                                <option value="<?= esc($option['supco']) ?>" <?= ($header['supco'] ?? '') === $option['supco'] ? 'selected' : '' ?>>
                                                    <?= esc($option['supplier_nama'] ?? $option['supco']) ?> (<?= esc($option['supco']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6" id="debt-select-wrapper">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="input-label-compact">Faktur Hutang Target <span class="text-danger">*</span></label>
                                            <small class="text-muted" style="font-size: 0.68rem;">Untuk POTONG HUTANG</small>
                                        </div>
                                        <select class="form-select select2" name="beli_id" id="beli_id">
                                            <option value="">Pilih faktur hutang supplier</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="input-label-compact">Keterangan / Alasan Retur</label>
                                        <input type="text" class="form-control form-control-sm bg-white" name="keterangan" id="keterangan" value="<?= esc($header['keterangan'] ?? '') ?>" placeholder="Alasan retur (barang rusak, expired, salah kirim, dll)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SUPPLIER & DEBT INFO SUMMARY (Appears dynamically) -->
                    <div id="supplier-info" class="<?= $supplier ? '' : 'd-none' ?> mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 10px; background: #fff5f5; border: 1px solid #fed7d7;">
                            <div class="card-body p-2 px-3" id="supplier-summary"></div>
                        </div>
                    </div>

                    <div id="debt-info" class="d-none mb-3">
                        <div class="card border-0 shadow-sm" style="border-radius: 10px; background: #f0fdf4; border: 1px solid #bbf7d0;">
                            <div class="card-body p-2 px-3" id="debt-summary"></div>
                        </div>
                    </div>

                    <!-- PRIMARY ITEM WORKSPACE (Dominant Focus Area) -->
                    <div class="card item-workspace-card">
                        <div class="card-header bg-white py-3 px-3 border-bottom">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-5">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-danger-subtle text-danger p-2 rounded-3"><i class="ti ti-arrow-back-up fs-5"></i></span>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark">Daftar Item Diretur</h6>
                                            <small class="text-muted" id="item-count-badge">0 item dipilih</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-7">
                                    <div class="item-search-wrapper position-relative">
                                        <select class="form-select" id="item-search" style="width: 100%;"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-2 p-md-3">
                            <div id="detail-list" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR / SUMMARY COLUMN -->
                <div class="col-xl-4">
                    <div class="sticky-summary d-flex flex-column gap-3">
                        <!-- Ringkasan Nilai -->
                        <div class="card shadow-sm border-0" id="summary-card" style="border-radius: 12px; background: #ffffff;">
                            <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold mb-0 text-dark"><i class="ti ti-calculator text-danger me-1"></i> Ringkasan Retur</h6>
                                <span class="badge bg-warning-subtle text-warning fs-2" id="summary-nota-badge">DRAFT</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light">
                                    <span class="text-muted small">Total Jenis Item</span>
                                    <span class="fw-bold" id="sum-items">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light">
                                    <span class="text-muted small">Total Kuantitas (Qty)</span>
                                    <span class="fw-bold" id="sum-qty">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-danger-subtle rounded-3">
                                    <span class="fw-semibold text-danger">Total Nilai Retur</span>
                                    <span class="fw-bolder fs-4 text-danger font-monospace" id="display-sum-total">Rp 0</span>
                                    <input type="hidden" id="sum-total" value="0">
                                </div>

                                <div id="retur-warning" class="mb-2"></div>

                                <!-- Desktop Action Button -->
                                <div class="d-none d-xl-grid gap-2 mt-3 pt-2 border-top">
                                    <button type="submit" class="btn btn-danger btn-lg fw-bold d-flex align-items-center justify-content-center gap-2" id="btn-save">
                                        <i class="ti ti-device-floppy fs-5"></i> Simpan Retur
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MOBILE STICKY ACTION BAR -->
            <div class="mobile-sticky-action-bar d-xl-none">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="small text-muted" style="font-size: 0.72rem; line-height: 1;">Total Retur</div>
                        <div class="fw-bolder fs-4 text-danger" id="mobile-summary-gross">Rp 0</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#summary-modal-mobile" title="Detail Ringkasan">
                            <i class="ti ti-info-circle fs-5"></i>
                        </button>
                        <button type="submit" class="btn btn-danger px-3 fw-bold d-flex align-items-center gap-1" id="mobile-btn-save">
                            <i class="ti ti-device-floppy fs-5"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SUMMARY UNTUK MOBILE -->
<div class="modal fade" id="summary-modal-mobile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Detail Ringkasan Retur</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Jenis Item:</span>
                    <span class="fw-bold" id="m-modal-item">0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Qty:</span>
                    <span class="fw-bold" id="m-modal-qty">0</span>
                </div>
                <div class="d-flex justify-content-between p-2 rounded bg-danger-subtle">
                    <span class="text-danger small fw-semibold">Nilai Retur:</span>
                    <span class="fw-bold text-danger" id="m-modal-total">Rp 0</span>
                </div>
            </div>
        </div>
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
        syncHeaderChips();

        // Auto toggle chevron icon on collapse
        $('#collapseHeaderInfo').on('show.bs.collapse', function () {
            $('#header-toggle-icon').removeClass('ti-chevron-down').addClass('ti-chevron-up');
        }).on('hide.bs.collapse', function () {
            $('#header-toggle-icon').removeClass('ti-chevron-up').addClass('ti-chevron-down');
        });
    });

    function syncHeaderChips() {
        // Supplier Chip
        const supText = $('#supco option:selected').text();
        const supVal = $('#supco').val();
        if (supVal && supText) {
            $('#header-chip-sup').text(supText.trim()).removeClass('d-none');
        } else {
            $('#header-chip-sup').addClass('d-none');
        }

        // Settlement Mode Chip
        const mode = $('#settlement_mode').val();
        if (mode === 'POTONG_HUTANG') {
            $('#header-chip-settle').html('<i class="ti ti-receipt-tax"></i> POTONG HUTANG').removeClass('bg-info-subtle text-info').addClass('bg-primary-subtle text-primary');
        } else {
            $('#header-chip-settle').html('<i class="ti ti-cash"></i> CASHBACK').removeClass('bg-primary-subtle text-primary').addClass('bg-info-subtle text-info');
        }

        // Status Retur Chip
        const status = $('#status_retur').val();
        $('#chip-status-text').text(status);
        $('#summary-nota-badge').text(status);
        if (status === 'SELESAI') {
            $('#header-chip-status').removeClass('bg-warning-subtle text-warning').addClass('bg-success-subtle text-success');
            $('#summary-nota-badge').removeClass('bg-warning-subtle text-warning').addClass('bg-success-subtle text-success');
        } else {
            $('#header-chip-status').removeClass('bg-success-subtle text-success').addClass('bg-warning-subtle text-warning');
            $('#summary-nota-badge').removeClass('bg-success-subtle text-success').addClass('bg-warning-subtle text-warning');
        }
    }

    $('#supco').on('change', function() {
        syncHeaderChips();
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
            syncHeaderChips();
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat data supplier retur'));
        });
    });

    $('#status_retur, #settlement_mode').on('change', function() {
        syncHeaderChips();
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
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <small class="text-muted d-block" style="font-size: 0.75rem;">Supplier Terpilih</small>
                    <div class="fw-bold text-dark fs-3">${supplierData.supplier_nama || supplierData.supco}</div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <small class="text-muted d-block" style="font-size: 0.75rem;">Total Hutang Supplier Tersedia</small>
                    <div class="fw-bold text-danger fs-4 font-monospace">Rp ${formatMoneyValue(supplierData.total_outstanding_debt || 0)}</div>
                </div>
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
            <div class="row g-2">
                <div class="col-6 col-md-3"><small class="text-muted d-block" style="font-size: 0.72rem;">Beli ID</small><span class="fw-semibold font-monospace">${debt.beli_id}</span></div>
                <div class="col-6 col-md-3"><small class="text-muted d-block" style="font-size: 0.72rem;">Invoice</small><span class="fw-semibold">${debt.invoice || '-'}</span></div>
                <div class="col-6 col-md-3"><small class="text-muted d-block" style="font-size: 0.72rem;">Status Bayar</small><span class="badge bg-warning-subtle text-warning">${debt.status_bayar || '-'}</span></div>
                <div class="col-6 col-md-3"><small class="text-muted d-block" style="font-size: 0.72rem;">Sisa Hutang</small><span class="fw-bold text-danger font-monospace">Rp ${formatMoneyValue(debt.sisa_bayar_form || 0)}</span></div>
            </div>
        `);
    }

    function renderDetailList() {
        const wrapper = $('#detail-list');
        wrapper.empty();

        const count = detailRows.length;
        $('#item-count-badge').text(`${count} item dipilih`);

        if (!count) {
            wrapper.html(`
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-basket-cancel fs-8 text-secondary opacity-50 mb-2 d-block"></i>
                    <h6 class="fw-semibold mb-1">Belum Ada Item Diretur</h6>
                    <p class="small mb-0">Cari item di atas dengan mengetik nama atau barcode produk.</p>
                </div>
            `);
            return;
        }

        detailRows.forEach((row, idx) => {
            const satOptions = (row.satuan_options || []).map((opt) => `<option value="${opt.sat_id}" data-konversi="${opt.qty_konversi}" data-hpp="${opt.harga_pokok || 0}" ${String(row.sat_id || row.source_sat_id) === String(opt.sat_id) ? 'selected' : ''}>${opt.sat_id}</option>`).join('');
            const maxSelected = getMaxSelectedQty(row);
            const isOver = (Number(row.qty_retur || 0) - maxSelected) > 0.0001;

            wrapper.append(`
                <div class="detail-item-card position-relative" data-index="${idx}">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark fs-3">${row.nama_item || row.kode_item}</div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">${row.kode_item}</span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 0.72rem;">
                                    <i class="ti ti-box"></i> Stok: ${Number(row.stok_aktual || 0).toLocaleString('id-ID')}
                                </span>
                                <span class="badge ${isOver ? 'bg-danger-subtle text-danger' : 'bg-light text-muted border'}" style="font-size: 0.72rem;">
                                    Maks: ${Number(maxSelected).toLocaleString('id-ID')} ${row.sat_id || row.source_sat_id}
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-item row-delete" title="Hapus item">
                            <i class="ti ti-trash fs-5"></i>
                        </button>
                    </div>

                    <div class="detail-input-grid">
                        <div>
                            <label class="form-label mb-1">Satuan</label>
                            <select class="form-select form-select-sm row-sat bg-white">${satOptions}</select>
                        </div>
                        <div>
                            <label class="form-label mb-1">Qty Retur</label>
                            <input type="number" inputmode="decimal" min="0" step="any" class="form-control form-control-sm text-end row-qty fw-bold ${isOver ? 'is-invalid border-danger text-danger' : ''}" value="${row.qty_retur || 0}">
                        </div>
                        <div>
                            <label class="form-label mb-1">Harga Satuan</label>
                            <input type="text" class="form-control form-control-sm money text-end row-price bg-light" value="${row.price || 0}" readonly tabindex="-1">
                        </div>
                        <div>
                            <label class="form-label mb-1 text-danger">Subtotal Gross</label>
                            <input type="text" class="form-control form-control-sm money text-end row-gross fw-bold text-danger bg-danger-subtle border-0" value="${row.gross_retur || 0}" readonly tabindex="-1">
                        </div>
                        <input type="hidden" class="row-qty-stock" value="${Number(row.qty_stok || 0).toLocaleString('id-ID')}">
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

        const formattedTotal = 'Rp ' + formatMoneyValue(totalRetur);

        $('#sum-items').text(totalItems.toLocaleString('id-ID'));
        $('#sum-qty').text(totalQty.toLocaleString('id-ID'));
        $('#display-sum-total').text(formattedTotal);
        $('#sum-total').val(totalRetur);

        // Mobile summary sync
        $('#mobile-summary-gross').text(formattedTotal);
        $('#m-modal-item').text(totalItems.toLocaleString('id-ID'));
        $('#m-modal-qty').text(totalQty.toLocaleString('id-ID'));
        $('#m-modal-total').text(formattedTotal);

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
                warningBox.html('<div class="alert alert-warning border-warning-subtle mb-0">Pilih faktur hutang supplier terlebih dulu untuk menyelesaikan retur dengan potong hutang.</div>');
                return;
            }

            if (totalRetur - Number(debt.sisa_bayar_form || 0) > 0.0001) {
                warningBox.html(`
                    <div class="alert customize-alert alert-dismissible alert-light-danger bg-danger-subtle text-danger fade show remove-close-icon mb-0" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="d-flex align-items-center me-3 me-md-0">
                            <i class="ti ti-cancel fs-5 me-2 text-danger"></i>
                            <span class="text-dark">Total retur sebesar <strong class="text-danger font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong> melebihi <strong>sisa hutang</strong> faktur terpilih sebesar <strong class="text-primary">Rp ${formatMoneyValue(debt.sisa_bayar_form || 0)}</strong>.</span>
                        </div>
                    </div>
                `);
                return;
            }

            warningBox.html(`<div class="alert alert-warning border-warning-subtle mb-0">Saat retur diselesaikan, stok akan dikurangi dan sistem akan mencatat <strong>POTONGAN RETUR</strong> sebesar <strong class="font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong> ke faktur <strong>${debt.beli_id}</strong>.</div>`);
            return;
        }

        warningBox.html(`<div class="alert alert-info border-info-subtle mb-0">Saat retur diselesaikan, stok akan dikurangi dan sistem akan mencatat <strong>kas masuk</strong> akun <strong>RETUR PEMBELIAN</strong> sebesar <strong class="font-monospace">Rp ${formatMoneyValue(totalRetur)}</strong> dengan keterangan nomor retur ini.</div>`);
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