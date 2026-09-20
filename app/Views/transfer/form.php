<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $mode
 */
$header = $formData['header'] ?? [];
$detailRows = $formData['details'] ?? [];
?>
<style>
    /* Styling ergonomis & compact untuk form transfer PO gudang */
    .transfer-card-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: #fff;
    }
    .badge-soft-primary { background-color: rgba(13, 110, 253, 0.12); color: #0d6efd; }
    .badge-soft-success { background-color: rgba(25, 135, 84, 0.12); color: #198754; }
    .badge-soft-warning { background-color: rgba(255, 193, 7, 0.2); color: #997404; }
    .badge-soft-danger { background-color: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .badge-soft-secondary { background-color: rgba(108, 117, 125, 0.12); color: #495057; }

    /* List item ultra compact */
    .detail-item-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        transition: all 0.15s ease-in-out;
        position: relative;
    }
    .detail-item-card:hover {
        border-color: #b0c4de;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .detail-item-card.is-zero-qty {
        background-color: #fafbfc;
        opacity: 0.92;
        border-left: 3px solid #ffc107;
    }
    .detail-item-card.is-out-of-stock {
        background-color: #fff8f8;
        border-left: 3px solid #dc3545;
    }
    .detail-item-card.is-fulfilled {
        border-left: 3px solid #198754;
    }
    .detail-item-card.has-error {
        border-left: 3px solid #dc3545;
        background-color: #fff5f5;
    }

    /* Stepper Qty Button touch ergonomis */
    .qty-stepper-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .input-qty-compact {
        font-weight: 700;
        font-size: 0.95rem;
    }
    .btn-action-touch {
        min-height: 44px;
        min-width: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Sticky Bottom Bar */
    .sticky-bottom-summary {
        position: sticky;
        bottom: 0;
        z-index: 1020;
        background: #ffffff;
        border-top: 1px solid #dee2e6;
        box-shadow: 0 -4px 16px rgba(0,0,0,0.08);
        padding: 12px 16px;
        border-radius: 12px 12px 0 0;
    }

    /* Compact row grid */
    .dense-info-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 2px;
    }
    .dense-info-val {
        font-size: 0.85rem;
        font-weight: 600;
        color: #212529;
    }

    @media (max-width: 767.98px) {
        .detail-item-card {
            padding: 8px 10px;
        }
        .dense-info-label {
            font-size: 0.68rem;
        }
        .dense-info-val {
            font-size: 0.8rem;
        }
        .sticky-bottom-summary {
            padding: 8px 12px;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-2 p-md-3">

        <!-- Top Header Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3 p-md-4 transfer-card-header rounded-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-light text-dark fw-bold">GUDANG</span>
                            <span class="badge bg-warning text-dark fw-bold"><?= $mode === 'edit' ? 'EDIT DRAFT' : 'BUAT TRANSFER' ?></span>
                        </div>
                        <h4 class="fw-bold mb-1 text-white"><?= $mode === 'edit' ? 'Edit Draft Kirim Transfer' : 'Pemenuhan PO Cabang (Transfer)' ?></h4>
                        <p class="mb-0 small text-white-50">Sesuaikan jumlah stok gudang yang dikirim ke cabang. Item kosong dapat dihapus secara otomatis.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= base_url('/transfer') ?>" class="btn btn-light btn-sm fw-semibold shadow-sm px-3">
                            <i class="ti ti-arrow-left me-1"></i> Kembali ke List
                        </a>
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

            <!-- Collapsible / Compact Header PO & Cabang Tujuan -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between cursor-pointer" data-bs-toggle="collapse" data-bs-target="#collapseHeaderInfo">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-building-warehouse fs-5 text-primary"></i>
                        <span class="fw-bold text-dark fs-6">Informasi Cabang & PO</span>
                        <span class="badge badge-soft-primary ms-1"><?= esc($header['tujuan_toko_nama'] ?? 'Cabang') ?></span>
                    </div>
                    <div class="text-muted small d-flex align-items-center gap-1">
                        <span class="d-none d-sm-inline">Tampilkan / Sembunyikan</span>
                        <i class="ti ti-chevron-down"></i>
                    </div>
                </div>
                <div class="collapse show" id="collapseHeaderInfo">
                    <div class="card-body p-3 bg-light-subtle">
                        <div class="row g-2 g-md-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label dense-info-label">Transfer ID</label>
                                <input type="text" class="form-control form-control-sm bg-white" value="<?= esc($header['transfer_id'] ?: 'AUTO DRAFT') ?>" readonly>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label dense-info-label">Tanggal Draft <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm bg-white" name="tanggal_transfer" id="tanggal_transfer" value="<?= esc($header['tanggal_transfer'] ?? date('Y-m-d')) ?>" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label dense-info-label">ID PO Cabang</label>
                                <input type="text" class="form-control form-control-sm bg-white" value="<?= esc($header['po_beli_id'] ?? '-') ?>" readonly>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label dense-info-label">Invoice PO Cabang</label>
                                <input type="text" class="form-control form-control-sm bg-white" value="<?= esc($header['invoice_po'] ?? '-') ?>" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label dense-info-label">Toko / Cabang Pemesan</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="ti ti-map-pin text-primary"></i></span>
                                    <input type="text" class="form-control bg-white fw-semibold" value="<?= esc(($header['tujuan_toko_nama'] ?? '-') . ' (' . ($header['tujuan_toko_id'] ?? '-') . ')') ?>" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label dense-info-label">Catatan / Keterangan Transfer</label>
                                <input type="text" class="form-control form-control-sm bg-white" name="keterangan" id="keterangan" placeholder="Tambahkan catatan untuk pengiriman..." value="<?= esc($header['keterangan'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar Pencarian & Aksi Massal Item List -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-6">
                            <label class="form-label dense-info-label mb-1">Tambah Item Tambahan ke Transfer</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="ti ti-barcode"></i></span>
                                <select class="form-select" id="item-search"></select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label dense-info-label mb-1 d-none d-md-block">Aksi Cepat Pengisian List</label>
                            <div class="d-flex flex-wrap gap-1 justify-content-start justify-content-md-end">
                                <button type="button" class="btn btn-sm btn-outline-success" id="btn-fulfill-max" title="Set Qty Kirim = Maks Stok Tersedia (tidak melebihi PO)">
                                    <i class="ti ti-bolt me-1"></i>Penuhi Otomatis
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" id="btn-clear-zero" title="Hapus item yang Qty Kirim = 0 atau Stok Kosong">
                                    <i class="ti ti-filter-off me-1"></i>Bersihkan Qty 0
                                </button>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-adjustments-horizontal me-1"></i>Filter Tampilan
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item filter-btn active" href="javascript:void(0)" data-filter="all"><i class="ti ti-list me-2"></i>Semua Item (<span class="badge-count-all">0</span>)</a></li>
                                        <li><a class="dropdown-item filter-btn" href="javascript:void(0)" data-filter="ready"><i class="ti ti-check text-success me-2"></i>Ada Stok & Kirim (<span class="badge-count-ready">0</span>)</a></li>
                                        <li><a class="dropdown-item filter-btn" href="javascript:void(0)" data-filter="empty"><i class="ti ti-alert-triangle text-warning me-2"></i>Qty 0 / Stok Habis (<span class="badge-count-empty">0</span>)</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Item Kirim (Compact Cards) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-packages fs-5 text-primary"></i>
                        <h6 class="mb-0 fw-bold">Daftar Item Pemenuhan Transfer</h6>
                        <span class="badge bg-primary rounded-pill px-2" id="badge-total-items-count">0 Item</span>
                    </div>
                    <div class="small text-muted d-none d-sm-block">
                        Pastikan Qty Kirim tidak melebihi stok gudang
                    </div>
                </div>
                <div class="card-body p-2 p-md-3 bg-light-subtle">
                    <div id="detail-list" class="d-grid gap-2">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Bar untuk Rangkuman & Submit Aksi -->
            <div class="sticky-bottom-summary">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center justify-content-between justify-content-md-start gap-3">
                            <div>
                                <div class="dense-info-label">Item Terpenuhi</div>
                                <div class="fs-6 fw-bold text-dark"><span id="sum-items">0</span> item</div>
                            </div>
                            <div class="border-start ps-3">
                                <div class="dense-info-label">Total Qty Kirim</div>
                                <div class="fs-6 fw-bold text-primary"><span id="sum-qty">0</span> unit</div>
                            </div>
                            <div class="border-start ps-3">
                                <div class="dense-info-label">Total Nilai Transfer</div>
                                <div class="fs-5 fw-bold text-success" id="sum-total-display">Rp 0</div>
                                <input type="hidden" class="money" id="sum-total" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="d-flex gap-2 justify-content-end align-items-center">
                            <button type="button" class="btn btn-outline-secondary btn-action-touch px-3" onclick="window.location.href='<?= base_url('/transfer') ?>'">
                                Batal
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-action-touch px-3 fw-semibold" onclick="promptSubmitDraft(false)">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Draft
                            </button>
                            <button type="button" class="btn btn-primary btn-action-touch px-3 fw-semibold shadow-sm" onclick="promptSubmitDraft(true)">
                                <i class="ti ti-send me-1"></i>Simpan & Kirim
                            </button>
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
    const initialDetails = <?= json_encode($detailRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let detailRows = hydrateRows(initialDetails || []);
    let activeFilter = 'all';

    $(function() {
        // Init Select2 pencarian item
        $('#item-search').select2({
            width: '100%',
            placeholder: 'Ketik nama item atau scan barcode...',
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

        // Filter toolbar
        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            activeFilter = $(this).data('filter');
            renderDetailList();
        });

        // Tombol Penuhi Otomatis (Set Qty Kirim = Min(PO, Maks Stok Gudang))
        $('#btn-fulfill-max').on('click', function() {
            if (!detailRows.length) {
                toastr.warning('Belum ada item dalam list.');
                return;
            }
            let updatedCount = 0;
            detailRows.forEach(row => {
                const maxQty = getMaxQty(row);
                // Jika ada Qty PO, penuhi maks sejumlah PO tapi tidak melebihi stok gudang
                const targetQty = (row.qty_po > 0) ? Math.min(row.qty_po, maxQty) : maxQty;
                if (row.qty_kirim !== targetQty) {
                    row.qty_kirim = targetQty;
                    recalcRow(row);
                    updatedCount++;
                }
            });
            renderDetailList();
            recalcSummary();
            toastr.success(`Berhasil mengisikan Qty Kirim untuk ${updatedCount} item.`);
        });

        // Tombol Bersihkan Qty 0 / Stok Habis
        $('#btn-clear-zero').on('click', function() {
            const zeroCount = detailRows.filter(r => Number(r.qty_kirim || 0) <= 0 || Number(r.stok_base || 0) <= 0).length;
            if (zeroCount === 0) {
                toastr.info('Tidak ada item dengan Qty Kirim 0 atau stok kosong.');
                return;
            }
            Swal.fire({
                title: 'Bersihkan Item Qty 0?',
                html: `Ditemukan <b>${zeroCount}</b> item dengan Qty Kirim 0 atau stok kosong di gudang.<br>Item ini akan dihapus dari daftar pengiriman.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Ya, Hapus Item Kosong',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    detailRows = detailRows.filter(r => Number(r.qty_kirim || 0) > 0 && Number(r.stok_base || 0) > 0);
                    renderDetailList();
                    recalcSummary();
                    toastr.success(`Berhasil menghapus ${zeroCount} item kosong.`);
                }
            });
        });

        renderDetailList();
        recalcSummary();
        applyMoneyMask('#form-transfer');
    });

    function addItemByCode(kodeItem) {
        if (detailRows.some((row) => String(row.kode_item) === String(kodeItem))) {
            toastr.warning('Item sudah ada di draft transfer.');
            return;
        }

        $.getJSON(`<?= base_url('/transfer/item-detail') ?>/${kodeItem}`, function(res) {
            if (res.tipe !== 'success') {
                toastr.error(res.data || 'Item tidak ditemukan');
                return;
            }
            const item = res.data || {};
            const firstSat = (item.satuan || [])[0] || {};
            const stokBase = Number(item.stok_base || 0);
            const konversi = Number(firstSat.qty_konversi || 1);
            const maxKirim = konversi > 0 ? Math.floor(stokBase / konversi) : 0;

            detailRows.push({
                seq_no: detailRows.length + 1,
                kode_item: item.kode_item,
                barcode: item.barcode || '',
                nama_item: item.nama_item || item.kode_item,
                qty_po: 0,
                stok_base: stokBase,
                sat_id: firstSat.sat_id || '',
                qty_konversi: konversi,
                qty_kirim: maxKirim > 0 ? 1 : 0, // default isi 1 jika ada stok
                qty_stock: (maxKirim > 0 ? 1 : 0) * konversi,
                harga_pokok: Number(firstSat.harga_pokok || 0),
                harga_jual: Number(firstSat.harga_jual_transfer || 0),
                gross: (maxKirim > 0 ? 1 : 0) * Number(firstSat.harga_jual_transfer || 0),
                satuan_options: item.satuan || [],
                item_error: ''
            });
            renderDetailList();
            recalcSummary();
            toastr.success(`Item ${item.nama_item || item.kode_item} ditambahkan.`);
        }).fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal memuat item gudang'));
        });
    }

    function renderDetailList() {
        const wrapper = $('#detail-list');
        wrapper.empty();

        // Update badge counts
        const countAll = detailRows.length;
        const countReady = detailRows.filter(r => Number(r.qty_kirim || 0) > 0 && Number(r.stok_base || 0) > 0).length;
        const countEmpty = detailRows.filter(r => Number(r.qty_kirim || 0) <= 0 || Number(r.stok_base || 0) <= 0).length;
        $('.badge-count-all').text(countAll);
        $('.badge-count-ready').text(countReady);
        $('.badge-count-empty').text(countEmpty);
        $('#badge-total-items-count').text(`${countAll} Item`);

        if (!detailRows.length) {
            wrapper.html(`
                <div class="text-center py-5 bg-white rounded border">
                    <i class="ti ti-box-off fs-1 text-muted d-block mb-2"></i>
                    <h6 class="text-muted fw-semibold">Belum ada item dalam transfer ini</h6>
                    <p class="small text-muted mb-0">Gunakan kotak pencarian di atas untuk menambahkan item dari gudang.</p>
                </div>
            `);
            return;
        }

        // Filter item
        const displayRows = detailRows.map((row, originalIndex) => ({ row, originalIndex })).filter(({ row }) => {
            if (activeFilter === 'ready') return Number(row.qty_kirim || 0) > 0 && Number(row.stok_base || 0) > 0;
            if (activeFilter === 'empty') return Number(row.qty_kirim || 0) <= 0 || Number(row.stok_base || 0) <= 0;
            return true;
        });

        if (!displayRows.length) {
            wrapper.html(`
                <div class="text-center py-4 bg-white rounded border">
                    <p class="text-muted mb-0">Tidak ada item yang sesuai dengan filter "${activeFilter}".</p>
                </div>
            `);
            return;
        }

        displayRows.forEach(({ row, originalIndex }) => {
            const satOptions = (row.satuan_options || []).map((opt) => `
                <option value="${opt.sat_id}"
                        data-konversi="${opt.qty_konversi}"
                        data-hpp="${opt.harga_pokok || 0}"
                        data-price="${opt.harga_jual_transfer || 0}"
                        ${String(row.sat_id) === String(opt.sat_id) ? 'selected' : ''}>
                    ${opt.sat_id} (${opt.qty_konversi}x)
                </option>
            `).join('');

            const maxQty = getMaxQty(row);
            const isOutOfStock = Number(row.stok_base || 0) <= 0;
            const isZeroQty = Number(row.qty_kirim || 0) <= 0;
            const isFulfilled = Number(row.qty_kirim || 0) > 0 && !isOutOfStock;

            let cardStateClass = '';
            let stockBadge = '';
            if (isOutOfStock) {
                cardStateClass = 'is-out-of-stock';
                stockBadge = '<span class="badge badge-soft-danger px-2 py-1"><i class="ti ti-x me-1"></i>Stok Habis (0)</span>';
            } else if (isZeroQty) {
                cardStateClass = 'is-zero-qty';
                stockBadge = '<span class="badge badge-soft-warning px-2 py-1"><i class="ti ti-alert-circle me-1"></i>Qty Kirim 0</span>';
            } else {
                cardStateClass = 'is-fulfilled';
                stockBadge = '<span class="badge badge-soft-success px-2 py-1"><i class="ti ti-check me-1"></i>Tersedia</span>';
            }

            if (row.item_error) {
                cardStateClass += ' has-error';
            }

            wrapper.append(`
                <div class="detail-item-card ${cardStateClass}" data-index="${originalIndex}">
                    <div class="row g-2 align-items-center">
                        
                        <!-- Col 1: Nama Item, Barcode, Status Badge -->
                        <div class="col-12 col-lg-4">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-light text-secondary border mt-1">${originalIndex + 1}</span>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-bold text-dark text-truncate" title="${row.nama_item || row.kode_item}">
                                        ${row.nama_item || row.kode_item}
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1 small">
                                        <code class="text-secondary">${row.kode_item}</code>
                                        ${row.barcode ? `<span class="text-muted"><i class="ti ti-barcode"></i> ${row.barcode}</span>` : ''}
                                        ${stockBadge}
                                    </div>
                                    ${row.item_error ? `<div class="text-danger small mt-1"><i class="ti ti-alert-triangle"></i> ${row.item_error}</div>` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Col 2: Info PO & Stok Gudang -->
                        <div class="col-6 col-sm-3 col-lg-2">
                            <div class="bg-light p-1 px-2 rounded border">
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">PO:</span>
                                    <span class="fw-bold ${Number(row.qty_po) > 0 ? 'text-primary' : 'text-muted'}">${Number(row.qty_po || 0).toLocaleString('id-ID')}</span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">Stok Dasar:</span>
                                    <span class="fw-bold ${isOutOfStock ? 'text-danger' : 'text-dark'}">${Number(row.stok_base || 0).toLocaleString('id-ID')}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Maks:</span>
                                    <span>${Number(maxQty).toLocaleString('id-ID')} ${row.sat_id || ''}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Col 3: Pilihan Satuan -->
                        <div class="col-6 col-sm-3 col-lg-2">
                            <label class="dense-info-label d-block">Satuan Kirim</label>
                            <select class="form-select form-select-sm row-sat bg-white" ${row.item_error || isOutOfStock ? 'disabled' : ''}>
                                ${satOptions}
                            </select>
                        </div>

                        <!-- Col 4: Qty Kirim dengan Stepper Touch Friendly -->
                        <div class="col-8 col-sm-4 col-lg-2">
                            <label class="dense-info-label d-block">Qty Kirim</label>
                            <div class="input-group input-group-sm">
                                <button type="button" class="btn btn-outline-secondary qty-stepper-btn btn-qty-minus" ${row.item_error || isOutOfStock ? 'disabled' : ''}>-</button>
                                <input type="number" min="0" max="${maxQty}" step="1" 
                                       class="form-control text-center input-qty-compact row-qty ${Number(row.qty_kirim) > 0 ? 'border-primary text-primary' : 'text-muted'}" 
                                       value="${row.qty_kirim || 0}" 
                                       ${row.item_error || isOutOfStock ? 'disabled' : ''}>
                                <button type="button" class="btn btn-outline-secondary qty-stepper-btn btn-qty-plus" ${row.item_error || isOutOfStock ? 'disabled' : ''}>+</button>
                            </div>
                        </div>

                        <!-- Col 5: Nilai Gross & Action Delete -->
                        <div class="col-4 col-sm-2 col-lg-2">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <div class="text-end">
                                    <label class="dense-info-label d-block text-end">Subtotal</label>
                                    <div class="dense-info-val text-success">
                                        Rp ${Number(row.gross || 0).toLocaleString('id-ID')}
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-action-touch row-delete" title="Hapus item dari daftar">
                                    <i class="ti ti-trash fs-5"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            `);
        });
    }

    // Event: Ubah Satuan
    $('#detail-list').on('change', '.row-sat', function() {
        const card = $(this).closest('[data-index]');
        const idx = Number(card.data('index'));
        const row = detailRows[idx];
        const option = $(this).find(':selected');
        row.sat_id = option.val();
        row.qty_konversi = Number(option.data('konversi') || 1);
        row.harga_pokok = Number(option.data('hpp') || 0);
        row.harga_jual = Number(option.data('price') || 0);

        // Auto clamp qty_kirim ke max baru
        const maxQty = getMaxQty(row);
        if (Number(row.qty_kirim) > maxQty) {
            row.qty_kirim = maxQty;
            toastr.info(`Qty kirim ${row.kode_item} disesuaikan ke batas maks (${maxQty}).`);
        }
        recalcRow(row);
        renderDetailList();
        recalcSummary();
    });

    // Event: Input Qty Kirim
    $('#detail-list').on('input change', '.row-qty', function() {
        const card = $(this).closest('[data-index]');
        const idx = Number(card.data('index'));
        const row = detailRows[idx];
        let val = Number($(this).val() || 0);
        if (val < 0) val = 0;

        const maxQty = getMaxQty(row);
        if (val - maxQty > 0.0001) {
            toastr.error(`Qty kirim ${row.kode_item} (${val}) melebihi stok gudang (${maxQty})`);
            val = maxQty;
            $(this).val(val);
        }
        row.qty_kirim = val;
        recalcRow(row);
        recalcSummary();

        // Update subtotal display on the card without full rerender for typing smoothness
        card.find('.dense-info-val.text-success').text('Rp ' + Number(row.gross || 0).toLocaleString('id-ID'));
        if (val > 0) {
            card.removeClass('is-zero-qty').addClass('is-fulfilled');
        } else {
            card.removeClass('is-fulfilled').addClass('is-zero-qty');
        }
    });

    // Event: Stepper Plus
    $('#detail-list').on('click', '.btn-qty-plus', function() {
        const card = $(this).closest('[data-index]');
        const idx = Number(card.data('index'));
        const row = detailRows[idx];
        const maxQty = getMaxQty(row);
        let val = Number(row.qty_kirim || 0) + 1;
        if (val > maxQty) {
            toastr.warning(`Stok gudang tidak mencukupi (Maks: ${maxQty})`);
            val = maxQty;
        }
        row.qty_kirim = val;
        recalcRow(row);
        renderDetailList();
        recalcSummary();
    });

    // Event: Stepper Minus
    $('#detail-list').on('click', '.btn-qty-minus', function() {
        const card = $(this).closest('[data-index]');
        const idx = Number(card.data('index'));
        const row = detailRows[idx];
        let val = Number(row.qty_kirim || 0) - 1;
        if (val < 0) val = 0;
        row.qty_kirim = val;
        recalcRow(row);
        renderDetailList();
        recalcSummary();
    });

    // Event: Hapus Item
    $('#detail-list').on('click', '.row-delete', function() {
        const idx = Number($(this).closest('[data-index]').data('index'));
        const item = detailRows[idx];
        detailRows.splice(idx, 1);
        renderDetailList();
        recalcSummary();
        toastr.info(`Item ${item ? (item.nama_item || item.kode_item) : ''} dihapus.`);
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
        $('#sum-total-display').text('Rp ' + Number(totalGross).toLocaleString('id-ID'));
    }

    /**
     * Validasi cerdas sebelum submit draft/kirim:
     * Cek apakah ada item dengan stok kosong (0) atau qty_kirim (0)
     */
    function promptSubmitDraft(sendAfterSave) {
        if (!detailRows.length) {
            toastr.error('Detail transfer belum diisi!');
            return;
        }

        // Cek item dengan stok kosong atau Qty Kirim = 0
        const zeroItems = detailRows.filter(r => Number(r.qty_kirim || 0) <= 0 || Number(r.stok_base || 0) <= 0);
        const validItems = detailRows.filter(r => Number(r.qty_kirim || 0) > 0 && Number(r.stok_base || 0) > 0);

        if (validItems.length === 0) {
            Swal.fire({
                title: 'Tidak Ada Item yang Dikirim',
                text: 'Semua item dalam daftar memiliki Qty Kirim 0 atau stok gudang kosong. Masukkan minimal 1 item dengan Qty Kirim > 0.',
                icon: 'warning',
                confirmButtonText: 'Periksa Kembali'
            });
            return;
        }

        if (zeroItems.length > 0) {
            Swal.fire({
                title: 'Ada Item dengan Qty 0 / Stok Kosong',
                html: `
                    <div class="text-start small">
                        <p>Ditemukan <b>${zeroItems.length} item</b> yang stoknya kosong atau Qty Kirim bernilai <b>0</b>.</p>
                        <p class="mb-1 text-muted">Apakah Anda ingin menghapus item kosong tersebut sebelum ${sendAfterSave ? 'mengirim' : 'menyimpan draft'}?</p>
                    </div>
                `,
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="ti ti-trash me-1"></i>Hapus Item Kosong & Lanjut',
                confirmButtonColor: '#0d6efd',
                denyButtonText: 'Tetap Simpan Semua',
                denyButtonColor: '#6c757d',
                cancelButtonText: 'Periksa Kembali'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Hapus item kosong dan lanjutkan
                    detailRows = validItems;
                    renderDetailList();
                    recalcSummary();
                    executeSubmit(sendAfterSave);
                } else if (result.isDenied) {
                    // Simpan apa adanya
                    executeSubmit(sendAfterSave);
                }
            });
        } else {
            executeSubmit(sendAfterSave);
        }
    }

    function executeSubmit(sendAfterSave) {
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
            beforeSend: function() {
                Swal.fire({
                    title: 'Menyimpan Transfer...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            },
            success: function(res) {
                if (res.tipe !== 'success') {
                    Swal.close();
                    toastr.error(res.data || 'Gagal menyimpan draft transfer');
                    return;
                }

                if (!sendAfterSave) {
                    Swal.fire({
                        title: 'Tersimpan!',
                        text: res.data || 'Draft transfer berhasil disimpan.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '<?= base_url('/transfer') ?>';
                    });
                    return;
                }

                const transferId = res.transfer_id || $('#transfer_id').val();
                Swal.fire({
                    title: 'Kirim Transfer Sekarang?',
                    text: 'Stok gudang akan langsung dipotong dan transfer dicatat sebagai penjualan kredit ke cabang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Kirim Sekarang',
                    cancelButtonText: 'Nanti Saja'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        window.location.href = '<?= base_url('/transfer') ?>';
                        return;
                    }

                    Swal.fire({
                        title: 'Memproses Pengiriman...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.post(`<?= base_url('/transfer/send') ?>/${transferId}`, function(sendRes) {
                        if (sendRes.tipe === 'success') {
                            Swal.fire({
                                title: 'Berhasil Terkirim!',
                                text: sendRes.data || 'Transfer berhasil dikirim ke cabang.',
                                icon: 'success'
                            }).then(() => {
                                window.location.href = '<?= base_url('/transfer') ?>';
                            });
                            return;
                        }
                        Swal.close();
                        toastr.error(sendRes.data || 'Gagal mengirim transfer');
                    }, 'json').fail(function(xhr) {
                        Swal.close();
                        toastr.error(extractErrorMessage(xhr, 'Gagal mengirim transfer'));
                    });
                });
            },
            error: function(xhr) {
                Swal.close();
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