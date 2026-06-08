<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$periodStart = $payData['period_start'] ?? date('Y-m-01');
$periodEnd = $payData['period_end'] ?? date('Y-m-d');
$rows = $payData['rows'] ?? [];
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Pembayaran Gaji</h4>
                        <p class="mb-0">Pilih periode, centang absensi yang ingin dibayar, lalu sistem akan membuat mutasi kas akun <strong>GAJI</strong> per toko dan per karyawan sesuai lokasi absensi yang dipilih.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="<?= base_url('/absensi') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Absensi</a>
                    </div>
                </div>
            </div>
        </div>

        <form method="get" action="<?= base_url('/absensi/pay') ?>" class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Periode Awal</label>
                        <input type="date" class="form-control" name="period_start" value="<?= esc($periodStart) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Periode Akhir</label>
                        <input type="date" class="form-control" name="period_end" value="<?= esc($periodEnd) ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Muat Data</button>
                        <button type="button" class="btn btn-success" onclick="processPayment()">Bayar Gaji</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="card mb-3">
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center"><input type="checkbox" id="check-all"></th>
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th class="text-end">Gaji</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)) : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Tidak ada absensi belum dibayar pada periode ini.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($rows as $row) : ?>
                                <tr data-id="<?= esc($row['absensi_id']) ?>" data-nominal="<?= esc($row['nominal_gaji']) ?>">
                                    <td class="text-center"><input type="checkbox" class="row-check"></td>
                                    <td><?= esc($row['tanggal']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($row['fullname']) ?></div>
                                        <small class="text-muted"><?= esc($row['karyawan_id']) ?></small>
                                    </td>
                                    <td><?= esc($row['toko_nama'] ?? $row['toko_id']) ?></td>
                                    <td><?= esc($row['status_absensi']) ?></td>
                                    <td class="text-end">Rp <?= number_format((float) ($row['nominal_gaji'] ?? 0), 0, ',', '.') ?></td>
                                    <td><?= esc($row['keterangan'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Baris Dipilih</small>
                    <div class="fw-semibold fs-5" id="sum-selected">0</div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Karyawan Dipilih</small>
                    <div class="fw-semibold fs-5" id="sum-karyawan">0</div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Total Bayar</small>
                    <input type="text" class="form-control form-control-lg money text-end fw-semibold border-0 p-0 bg-transparent" id="sum-nominal" value="0" readonly>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    $(function() {
        updateSummary();
        applyMoneyMask('#sum-nominal');
    });

    $('#check-all').on('change', function() {
        $('.row-check').prop('checked', $(this).is(':checked'));
        updateSummary();
    });

    $(document).on('change', '.row-check', updateSummary);

    function getSelectedRows() {
        return $('.row-check:checked').map(function() {
            const tr = $(this).closest('tr');
            return {
                absensi_id: Number(tr.data('id')),
                nominal: Number(tr.data('nominal') || 0),
                karyawan: tr.find('small.text-muted').text().trim()
            };
        }).get();
    }

    function updateSummary() {
        const selected = getSelectedRows();
        const uniqueEmployees = Array.from(new Set(selected.map((row) => row.karyawan)));
        const totalNominal = selected.reduce((sum, row) => sum + Number(row.nominal || 0), 0);
        $('#sum-selected').text(selected.length.toLocaleString('id-ID'));
        $('#sum-karyawan').text(uniqueEmployees.length.toLocaleString('id-ID'));
        $('#sum-nominal').val(totalNominal);
        applyMoneyMask('#sum-nominal');
    }

    function processPayment() {
        const selected = getSelectedRows();
        if (!selected.length) {
            toastr.error('Pilih minimal satu absensi untuk dibayar');
            return;
        }

        Swal.fire({
            title: 'Tanggal bayar gaji',
            input: 'date',
            inputValue: new Date().toISOString().slice(0, 10),
            showCancelButton: true,
            confirmButtonText: 'Proses bayar',
            cancelButtonText: 'Batal',
            preConfirm: (value) => {
                if (!value) {
                    Swal.showValidationMessage('Tanggal bayar wajib dipilih');
                }
                return value;
            }
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('<?= base_url('/absensi/process-payment') ?>', {
                tanggal_bayar: result.value,
                period_start: '<?= esc($periodStart) ?>',
                period_end: '<?= esc($periodEnd) ?>',
                selected_ids: JSON.stringify(selected.map((row) => row.absensi_id))
            }, function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Pembayaran gaji berhasil diproses');
                    window.location.href = '<?= base_url('/absensi') ?>';
                    return;
                }
                toastr.error(res.data || 'Gagal memproses pembayaran');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal memproses pembayaran gaji'));
            });
        });
    }
</script>
<?= $this->endSection('javascript') ?>
