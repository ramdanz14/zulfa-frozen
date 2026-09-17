<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var object $detail
 * 
 */
?>
<style>
    /* Styling optimasi mobile untuk Detail Item */
    .detail-hero-title {
        font-size: 1.25rem;
        line-height: 1.3;
    }

    .val-empty-alert {
        background-color: #fee2e2 !important;
        color: #b91c1c !important;
        border: 1px solid #fca5a5 !important;
        font-weight: 700;
    }

    .val-warning-alert {
        background-color: #fef3c7 !important;
        color: #b45309 !important;
        border: 1px solid #fde68a !important;
        font-weight: 700;
    }

    .price-tag-badge {
        font-size: 0.95rem;
        font-weight: 700;
        font-family: var(--bs-font-monospace);
    }

    .mobile-data-card {
        border: 1px solid var(--bs-border-color);
        border-radius: 0.5rem;
        padding: 0.75rem;
        background: var(--bs-body-bg);
        margin-bottom: 0.75rem;
    }

    @media (max-width: 767.98px) {
        .detail-hero-title {
            font-size: 1.1rem;
        }

        .table-desktop-view {
            display: none !important;
        }

        .card-mobile-view {
            display: block !important;
        }
    }

    @media (min-width: 768px) {
        .table-desktop-view {
            display: block !important;
        }

        .card-mobile-view {
            display: none !important;
        }
    }
</style>

<div class="body-wrapper">
    <div class="container-fluid p-0">
        <!-- Hero Header -->
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3 d-flex justify-content-between align-items-center gap-2">
                <div class="grow">
                    <h4 class="fw-bold text-info-emphasis mb-1 detail-hero-title"><?= esc($detail['prodmast']['nama_item']) ?></h4>
                    <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                        <span class="font-monospace fw-semibold"><i class="ti ti-barcode me-1"></i><?= esc($detail['prodmast']['kode_item']) ?></span>
                        <span class="badge bg-primary text-white"><?= esc(session('toko_id')) ?></span>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis fw-semibold"><?= esc($detail['prodmast']['kat_id']) ?></span>
                    </div>
                </div>
                <div class="shrink-0">
                    <button type="button" onclick="handleItemBack()" class="btn btn-secondary btn-sm px-3" aria-label="Kembali ke Daftar Barang" style="min-height: 38px;">
                        <i class="ti ti-arrow-left me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Master Barang -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-transparent fw-bold text-uppercase text-secondary py-2" style="font-size: 0.825rem; letter-spacing: 0.5px;">
                <i class="ti ti-info-circle me-1"></i> Informasi Master
            </div>
            <div class="card-body py-3">
                <div class="row g-2 g-md-3">
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Kode Item</span>
                        <span class="fw-bold text-dark font-monospace"><?= esc($detail['prodmast']['kode_item']) ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Barcode</span>
                        <span class="fw-medium text-dark font-monospace"><?= esc($detail['prodmast']['barcode'] ?: '-') ?></span>
                    </div>
                    <div class="col-12 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Nama Item</span>
                        <span class="fw-bold text-dark"><?= esc($detail['prodmast']['nama_item']) ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Kategori</span>
                        <span class="badge bg-secondary-subtle text-secondary fw-semibold"><?= esc($detail['prodmast']['kat_id']) ?></span>
                    </div>

                    <div class="col-12">
                        <hr class="my-1 opacity-25">
                    </div>

                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Supplier Code</span>
                        <span class="text-dark fw-medium"><?= esc($detail['store_supco'] ?: '-') ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Keterangan</span>
                        <span class="text-muted italic"><?= esc($detail['prodmast']['keterangan'] ?: '-') ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Updater ID</span>
                        <span class="fw-medium text-dark"><i class="ti ti-user me-1"></i><?= esc($detail['prodmast']['updid'] ?: '-') ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Update Terakhir</span>
                        <span class="fw-medium text-dark small"><?= esc($detail['prodmast']['updtime'] ?: '-') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <!-- Satuan & Stok Konversi -->
            <div class="col-12 col-lg-5">
                <div class="card shadow-sm h-100 mb-0">
                    <div class="card-header bg-transparent fw-bold text-uppercase text-secondary d-flex justify-content-between align-items-center py-2" style="font-size: 0.825rem; letter-spacing: 0.5px;">
                        <span><i class="ti ti-packages me-1"></i> Satuan & Stok</span>
                        <span class="badge bg-light-subtle text-muted border fw-normal" style="font-size: 0.7rem;">Gudang / Toko</span>
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <!-- Desktop Table -->
                        <div class="table-responsive table-desktop-view">
                            <table class="table table-bordered table-striped table-sm align-middle mb-0">
                                <thead class="table-light text-uppercase" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="ps-2">Satuan</th>
                                        <th class="text-center">Qty Konversi</th>
                                        <th class="text-end pe-2">Stok Tersedia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detail['satuan'])) : foreach ($detail['satuan'] as $row) :
                                            $stokVal = (float) ($row['stok'] ?? 0);
                                            $isStokKosong = $stokVal <= 0;
                                    ?>
                                            <tr>
                                                <td class="ps-2">
                                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1"><?= esc($row['sat_id']) ?></span>
                                                </td>
                                                <td class="text-center fw-semibold text-dark"><?= esc(number_format((float) $row['qty_konversi'])) ?></td>
                                                <td class="text-end pe-2">
                                                    <?php if ($isStokKosong) : ?>
                                                        <span class="badge val-empty-alert px-2 py-1">
                                                            <i class="ti ti-alert-triangle me-1"></i>0 (Habis)
                                                        </span>
                                                    <?php else : ?>
                                                        <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 fs-3">
                                                            <?= esc(number_format($stokVal)) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    else : ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Tidak ada data satuan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="card-mobile-view">
                            <?php if (!empty($detail['satuan'])) : foreach ($detail['satuan'] as $row) :
                                    $stokVal = (float) ($row['stok'] ?? 0);
                                    $isStokKosong = $stokVal <= 0;
                            ?>
                                    <div class="mobile-data-card <?= $isStokKosong ? 'border-danger-subtle bg-danger-subtle bg-opacity-10' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-primary px-2 py-1 fs-3 fw-bold"><?= esc($row['sat_id']) ?></span>
                                            <div class="text-end">
                                                <span class="text-muted small d-block" style="font-size:0.7rem;">STOK TERSEDIA</span>
                                                <?php if ($isStokKosong) : ?>
                                                    <span class="badge val-empty-alert px-2 py-1">
                                                        <i class="ti ti-alert-triangle me-1"></i>0 (HABIS)
                                                    </span>
                                                <?php else : ?>
                                                    <span class="badge bg-success text-white px-2 py-1 fs-3 fw-bold">
                                                        <?= esc(number_format($stokVal)) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="small text-muted mt-1 pt-1 border-top border-opacity-25">
                                            <span>Konversi: <strong><?= esc(number_format((float) $row['qty_konversi'])) ?></strong></span>
                                        </div>
                                    </div>
                                <?php endforeach;
                            else : ?>
                                <div class="text-center text-muted py-3">Tidak ada data satuan</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Harga Per Toko & Margin -->
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm h-100 mb-0">
                    <div class="card-header bg-transparent fw-bold text-uppercase text-secondary d-flex justify-content-between align-items-center py-2" style="font-size: 0.825rem; letter-spacing: 0.5px;">
                        <span><i class="ti ti-currency-dollar me-1"></i> Harga Per Toko & Margin</span>
                        <span class="badge bg-info-subtle text-info border fw-normal" style="font-size: 0.7rem;">Toko: <?= esc(session('toko_id')) ?></span>
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <!-- Desktop Table -->
                        <div class="table-responsive table-desktop-view">
                            <table class="table table-bordered table-striped table-sm align-middle mb-0">
                                <thead class="table-light text-uppercase" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="ps-2">Satuan</th>
                                        <th class="text-end">Harga Pokok</th>
                                        <th class="text-end">Harga Jual</th>
                                        <th class="text-center">Margin %</th>
                                        <th class="text-center pe-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detail['store'])) : foreach ($detail['store'] as $row) :
                                            $hp = (float) ($row['harga_pokok'] ?? 0);
                                            $hj = (float) ($row['harga_jual'] ?? 0);
                                            $margin =  ($row['target_psn_margin'] ?? 0);
                                            $isHpNol = $hp <= 0;
                                            $isHjNol = $hj <= 0;
                                            $isMarginNol = $margin <= 0;
                                    ?>
                                            <tr>
                                                <td class="ps-2">
                                                    <span class="badge bg-primary-subtle text-primary fw-bold"><?= esc($row['sat_id']) ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($isHpNol) : ?>
                                                        <span class="badge val-empty-alert px-2 py-1">Rp 0 (Kosong)</span>
                                                    <?php else : ?>
                                                        <span class="fw-semibold text-dark">Rp <?= esc(number_format($hp)) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($isHjNol) : ?>
                                                        <span class="badge val-empty-alert px-2 py-1">Rp 0 (Belum Set)</span>
                                                    <?php else : ?>
                                                        <span class="price-tag-badge text-success">Rp <?= esc(number_format($hj)) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($isMarginNol) : ?>
                                                        <span class="badge val-warning-alert px-2 py-1">0%</span>
                                                    <?php else : ?>
                                                        <span class="badge bg-info-subtle text-info-emphasis fw-bold px-2 py-1"><?= esc($margin) ?>%</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center pe-2">
                                                    <?= esc($row['status_item']) === 'Y' ?
                                                        '<span class="badge bg-success-subtle text-success fw-bold">Aktif</span>' :
                                                        '<span class="badge bg-danger-subtle text-danger fw-bold">Nonaktif</span>' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    else : ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">Tidak ada data harga</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="card-mobile-view">
                            <?php if (!empty($detail['store'])) : foreach ($detail['store'] as $row) :
                                    $hp = (float) ($row['harga_pokok'] ?? 0);
                                    $hj = (float) ($row['harga_jual'] ?? 0);
                                    $margin =  ($row['target_psn_margin'] ?? 0);
                                    $isHpNol = $hp <= 0;
                                    $isHjNol = $hj <= 0;
                                    $isMarginNol = $margin <= 0;
                            ?>
                                    <div class="mobile-data-card <?= ($isHpNol || $isHjNol) ? 'border-warning-subtle bg-warning-subtle bg-opacity-10' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge bg-primary px-2 py-1 fs-3 fw-bold"><?= esc($row['sat_id']) ?></span>
                                                <?= esc($row['status_item']) === 'Y' ?
                                                    '<span class="badge bg-success-subtle text-success fw-bold">Aktif</span>' :
                                                    '<span class="badge bg-danger-subtle text-danger fw-bold">Nonaktif</span>' ?>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-muted small" style="font-size:0.7rem;">TARGET MARGIN</span>
                                                <div>
                                                    <?php if ($isMarginNol) : ?>
                                                        <span class="badge val-warning-alert px-2 py-0">0%</span>
                                                    <?php else : ?>
                                                        <span class="badge bg-info-subtle text-info-emphasis fw-bold px-2 py-0"><?= esc($margin) ?>%</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-2 pt-1 border-top border-opacity-25">
                                            <div class="col-6">
                                                <span class="text-muted d-block small" style="font-size:0.72rem;">HARGA POKOK</span>
                                                <?php if ($isHpNol) : ?>
                                                    <span class="badge val-empty-alert px-2 py-1 d-inline-block">Rp 0 (KOSONG)</span>
                                                <?php else : ?>
                                                    <span class="fw-semibold text-dark font-monospace">Rp <?= esc(number_format($hp)) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-6 text-end">
                                                <span class="text-muted d-block small" style="font-size:0.72rem;">HARGA JUAL</span>
                                                <?php if ($isHjNol) : ?>
                                                    <span class="badge val-empty-alert px-2 py-1 d-inline-block">Rp 0 (BELUM SET)</span>
                                                <?php else : ?>
                                                    <span class="price-tag-badge text-success">Rp <?= esc(number_format($hj)) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach;
                            else : ?>
                                <div class="text-center text-muted py-3">Tidak ada data harga</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Transaksi Terakhir -->
            <div class="col-12">
                <div class="card shadow-sm mb-0">
                    <div class="card-header bg-transparent fw-bold text-uppercase text-secondary py-2" style="font-size: 0.825rem; letter-spacing: 0.5px;">
                        <i class="ti ti-history me-1"></i> Log Transaksi Terakhir
                    </div>
                    <div class="card-body p-2 p-md-3">
                        <!-- Desktop View Table -->
                        <div class="table-responsive table-desktop-view">
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <thead class="table-light text-uppercase" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="ps-3 w-33">Pembelian Terakhir</th>
                                        <th class="w-33">Penjualan Terakhir</th>
                                        <th class="pe-3 w-33">Stock Opname Terakhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detail['store'])) : foreach ($detail['store'] as $row) : ?>
                                            <tr>
                                                <td id="last_beli" class="ps-3 text-muted italic py-2"><?= esc($row['last_beli']) ?></td>
                                                <td id="last_jual" class="ps-3 text-muted italic py-2"><?= esc($row['last_jual']) ?></td>
                                                <td id="last_so" class="ps-3 text-muted italic py-2"><?= esc($row['last_so']) ?></td>
                                            </tr>
                                        <?php endforeach;
                                    else : ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Tidak ada data transaksi</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="card-mobile-view">
                            <?php if (!empty($detail['store'])) : foreach ($detail['store'] as $row) : ?>
                                    <div class="mobile-data-card">
                                        <div class="mb-2 pb-2 border-bottom border-opacity-25">
                                            <span class="text-muted d-block small" style="font-size:0.72rem;"><i class="ti ti-shopping-cart me-1 text-primary"></i>PEMBELIAN TERAKHIR</span>
                                            <span class="fw-semibold text-dark" id="mob_last_beli"><?= esc($row['last_beli']) ?></span>
                                        </div>
                                        <div class="mb-2 pb-2 border-bottom border-opacity-25">
                                            <span class="text-muted d-block small" style="font-size:0.72rem;"><i class="ti ti-receipt me-1 text-success"></i>PENJUALAN TERAKHIR</span>
                                            <span class="fw-semibold text-dark" id="mob_last_jual"><?= esc($row['last_jual']) ?></span>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block small" style="font-size:0.72rem;"><i class="ti ti-clipboard-check me-1 text-warning"></i>STOCK OPNAME TERAKHIR</span>
                                            <span class="fw-semibold text-dark" id="mob_last_so"><?= esc($row['last_so']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach;
                            else : ?>
                                <div class="text-center text-muted py-3">Tidak ada data transaksi</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const last_beli = $("#last_beli").text();
    const last_jual = $("#last_jual").text();
    const last_so = $("#last_so").text();
    if (last_beli != "") {
        const textFormatted = `${last_beli} (${humanizeDate(last_beli)})`;
        $("#last_beli").text(textFormatted);
        $("#mob_last_beli").text(textFormatted);
    } else {
        $("#last_beli").text('Belum ada transaksi');
        $("#mob_last_beli").text('Belum ada transaksi');
    }
    if (last_jual != "") {
        const textFormatted = `${last_jual} (${humanizeDate(last_jual)})`;
        $("#last_jual").text(textFormatted);
        $("#mob_last_jual").text(textFormatted);
    } else {
        $("#last_jual").text('Belum ada transaksi');
        $("#mob_last_jual").text('Belum ada transaksi');
    }
    if (last_so != "") {
        const textFormatted = `${last_so} (${humanizeDate(last_so)})`;
        $("#last_so").text(textFormatted);
        $("#mob_last_so").text(textFormatted);
    } else {
        $("#last_so").text('Belum ada transaksi');
        $("#mob_last_so").text('Belum ada transaksi');
    }

    function handleItemBack() {
        if (window.opener && !window.opener.closed) {
            window.close();
        } else if (window.history.length > 1) {
            window.location.href = '<?= base_url('/item') ?>';
        } else {
            window.close();
        }
    }
</script>
<?= $this->endSection('javascript') ?>