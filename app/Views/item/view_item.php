<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var object $detail
 */
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold text-info-emphasis mb-1">Detail Barang - <?= esc($detail['prodmast']['nama_item']) ?> </h4>
                    <p class="mb-0 text-muted fw-medium"><i class="ti ti-barcode"></i><?= esc($detail['prodmast']['kode_item']) ?> - <span class="badge bg-info text-dark"><?= esc(session('toko_id')) ?></span></p>
                </div>
                <a onclick="window.close()" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                Master Barang
            </div>
            <div class="card-body py-3">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Kode Item</span>
                        <span class="fw-bold text-dark"><?= esc($detail['prodmast']['kode_item']) ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Barcode</span>
                        <span class="fw-medium text-dark"><?= esc($detail['prodmast']['barcode']) ?? "-" ?></span>
                    </div>
                    <div class="col-12 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Nama Item</span>
                        <span class="fw-bold text-dark"><?= esc($detail['prodmast']['nama_item']) ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Kategori</span>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis fw-semibold"><?= esc($detail['prodmast']['kat_id']) ?></span>
                    </div>

                    <div class="col-12">
                        <hr class="my-1 opacity-25">
                    </div>

                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Supplier Code</span>
                        <span class="text-muted italic"><?= esc($detail['store_supco'] ?? '-') ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Keterangan</span>
                        <span class="text-muted italic"><?= esc($detail['prodmast']['keterangan'] ?? "-") ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Updater ID</span>
                        <span class="fw-medium text-dark"><i class="ti ti-user"></i> <?= esc($detail['prodmast']['updid']) ?></span>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block small text-uppercase fw-semibold">Update Terakhir</span>
                        <span class="fw-medium text-dark"><?= esc($detail['prodmast']['updtime']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-transparent fw-bold text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        Satuan / Pecahan Barang
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm align-middle mb-0">
                                <thead class="table-light text-uppercase" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="ps-3">Kode Item</th>
                                        <th>Satuan</th>
                                        <th class="text-center pe-3">Qty Konversi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detail['satuan'])) : foreach ($detail['satuan'] as $row) : ?>
                                            <tr>
                                                <td class="ps-3 fw-medium text-secondary"><?= esc($row['kode_item']) ?></td>
                                                <td><span class="badge bg-primary-subtle text-primary fw-bold"><?= esc($row['sat_id']) ?></span></td>
                                                <td class="text-center fw-bold text-dark pe-3"><?= esc(number_format((int) $row['qty_konversi'])) ?></td>
                                            </tr>
                                        <?php endforeach;
                                    else : ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-transparent fw-bold text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        Harga Per Toko
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm align-middle mb-0">
                                <thead class="table-light text-uppercase" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="ps-3">Toko</th>
                                        <th>Satuan</th>
                                        <th class="text-end">Harga Pokok</th>
                                        <th class="text-end">Harga Jual</th>
                                        <th class="text-center">Margin %</th>
                                        <th class="text-center pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detail['store'])) : foreach ($detail['store'] as $row) : ?>
                                            <tr>
                                                <td><?= esc($row['toko_id']) ?></td>
                                                <td><?= esc($row['sat_id']) ?></td>
                                                <td class="text-end"><?= esc(number_format((int) $row['harga_pokok'])) ?></td>
                                                <td class="text-end"><?= esc(number_format((int) $row['harga_jual'])) ?></td>
                                                <td><?= esc((string) $row['target_psn_margin']) ?></td>
                                                <td><?= esc($row['status_item']) ==  'Y' ? '<span class="badge bg-success-subtle text-success">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger">Nonaktif</span>' ?></td>
                                            </tr>
                                        <?php endforeach;
                                    else : ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-transparent fw-bold text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        Log Transaksi Terakhir
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
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
                                                <td id="last_beli" class="ps-3 text-muted italic py-3"><?= esc($row['last_beli']) ?></td>
                                                <td id="last_jual" class="ps-3 text-muted italic py-3"><?= esc($row['last_jual']) ?></td>
                                                <td id="last_so" class="ps-3 text-muted italic py-3"><?= esc($row['last_so']) ?></td>
                                            </tr>
                                        <?php endforeach;
                                    else : ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
        $("#last_beli").text(`${last_beli} (${humanizeDate(last_beli)}) `);
    } else {
        $("#last_beli").text('Belum ada transaksi');
    }
    if (last_jual != "") {
        $("#last_jual").text(`${last_jual} (${humanizeDate(last_jual)}) `);
    } else {
        $("#last_jual").text('Belum ada transaksi');
    }
    if (last_so != "") {
        $("#last_so").text(`${last_so} (${humanizeDate(last_so)}) `);
    } else {
        $("#last_so").text('Belum ada transaksi');
    }
</script>
<?= $this->endSection('javascript') ?>