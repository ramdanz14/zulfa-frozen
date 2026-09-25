<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$periodStart = $payData['period_start'] ?? date('Y-m-01');
$periodEnd = $payData['period_end'] ?? date('Y-m-d');
$rows = $payData['rows'] ?? [];
$aksesMenuData = json_decode((string) ($akses_menu ?? '{}'), true) ?: [];
$canDeleteAkses = ($aksesMenuData['akses_delete'] ?? '') === 'Y';
?>
<style>
    /* Mobile-first styling untuk Pembayaran Gaji */
    .metric-card-summary {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 0.75rem 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .metric-card-summary .metric-title {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .metric-card-summary .metric-value {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.25;
    }

    /* Mobile item card untuk seleksi gaji */
    .pay-item-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        position: relative;
    }
    .pay-item-card.is-selected {
        border-color: #0284c7;
        background-color: #f0f9ff;
    }
    .pay-checkbox-touch {
        width: 22px;
        height: 22px;
        cursor: pointer;
    }

    /* Sticky Bottom Action Bar */
    .sticky-bottom-summary {
        position: sticky;
        bottom: 0;
        z-index: 1020;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border-top: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.06);
    }

    @media (max-width: 767.98px) {
        .btn-touch-target {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .metric-card-summary {
            padding: 0.6rem 0.75rem;
        }
        .metric-card-summary .metric-value {
            font-size: 1rem;
        }
    }
</style>

<div class="body-wrapper pb-5">
    <div class="container-fluid p-2 p-md-3">
        <!-- Header Page -->
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Pembayaran Gaji Karyawan</h4>
                        <p class="mb-0 small text-muted">Pilih absensi yang akan dibayar dan buat pengeluaran kas operasional.</p>
                    </div>
                    <div>
                        <a href="<?= base_url('/absensi') ?>" class="btn btn-outline-secondary btn-sm btn-touch-target">
                            <i class="ti ti-arrow-left me-1"></i> Kembali ke Absensi
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Periode Form -->
        <form method="get" action="<?= base_url('/absensi/pay') ?>" class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Periode Awal</label>
                        <input type="date" class="form-control form-control-sm" name="period_start" value="<?= esc($periodStart) ?>">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Periode Akhir</label>
                        <input type="date" class="form-control form-control-sm" name="period_end" value="<?= esc($periodEnd) ?>">
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill btn-touch-target">
                            <i class="ti ti-filter me-1"></i> Muat Data
                        </button>
                        <button type="button" class="btn btn-success btn-sm flex-fill btn-touch-target" onclick="processPayment()">
                            <i class="ti ti-cash me-1"></i> Bayar Gaji
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Container Daftar Absensi Siap Bayar -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-checkbox fs-5 text-primary"></i>
                    <h6 class="mb-0 fw-bold">Daftar Absensi Belum Dibayar</h6>
                </div>
                <div class="form-check m-0">
                    <input type="checkbox" class="form-check-input pay-checkbox-touch" id="check-all">
                    <label class="form-check-label small fw-semibold ms-1" for="check-all">Pilih Semua</label>
                </div>
            </div>

            <div class="card-body p-2 p-md-3">
                <?php if (empty($rows)) : ?>
                    <div class="text-center text-muted py-5">
                        <i class="ti ti-mood-empty fs-2 d-block mb-2"></i>
                        <span>Tidak ada absensi yang belum dibayar pada periode ini.</span>
                    </div>
                <?php else : ?>
                    <!-- Desktop Table View (>= 768px) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered table-hover table-striped table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 44px;"><i class="ti ti-check"></i></th>
                                    <th>Tanggal</th>
                                    <th>Karyawan</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th class="text-end">Gaji</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row) : ?>
                                    <tr class="item-row" data-id="<?= esc($row['absensi_id']) ?>" data-nominal="<?= esc($row['nominal_gaji']) ?>">
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input row-check pay-checkbox-touch">
                                        </td>
                                        <td><?= esc(date('d/m/Y', strtotime($row['tanggal']))) ?></td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= esc($row['fullname']) ?></div>
                                            <small class="text-muted"><?= esc($row['karyawan_id']) ?></small>
                                        </td>
                                        <td><?= esc($row['toko_nama'] ?? $row['toko_id']) ?></td>
                                        <td>
                                            <span class="badge <?= $row['status_absensi'] === 'HADIR' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                                                <?= esc($row['status_absensi']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-dark">Rp <?= number_format((float) ($row['nominal_gaji'] ?? 0), 0, ',', '.') ?></td>
                                        <td><?= esc($row['keterangan'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View (< 768px) -->
                    <div class="d-md-none d-grid gap-2">
                        <?php foreach ($rows as $row) : ?>
                            <div class="pay-item-card item-card-mobile" data-id="<?= esc($row['absensi_id']) ?>" data-nominal="<?= esc($row['nominal_gaji']) ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2 pb-1 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="checkbox" class="form-check-input row-check pay-checkbox-touch">
                                        <div>
                                            <span class="fw-bold text-dark fs-6"><?= esc($row['fullname']) ?></span>
                                            <small class="text-muted d-block"><?= esc($row['karyawan_id']) ?></small>
                                        </div>
                                    </div>
                                    <span class="badge <?= $row['status_absensi'] === 'HADIR' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                                        <?= esc($row['status_absensi']) ?>
                                    </span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-1 text-muted small">
                                    <span><i class="ti ti-calendar me-1"></i><?= esc(date('d/m/Y', strtotime($row['tanggal']))) ?></span>
                                    <span><i class="ti ti-building-store me-1"></i><?= esc($row['toko_nama'] ?? $row['toko_id']) ?></span>
                                </div>

                                <?php if (!empty($row['keterangan'])) : ?>
                                    <div class="small text-muted mb-2">
                                        <i class="ti ti-notes me-1"></i><?= esc($row['keterangan']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="text-muted small text-uppercase" style="font-size: 0.72rem; font-weight: 600;">Nominal Gaji</span>
                                    <span class="fw-bold text-success fs-6">Rp <?= number_format((float) ($row['nominal_gaji'] ?? 0), 0, ',', '.') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Summary Metric Cards (3 Kolom Responsif) -->
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-checkbox me-1 text-primary"></i>Dipilih</div>
                    <div class="metric-value text-dark" id="sum-selected">0</div>
                </div>
            </div>
            <div class="col-4">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-users me-1 text-success"></i>Karyawan</div>
                    <div class="metric-value text-success" id="sum-karyawan">0</div>
                </div>
            </div>
            <div class="col-4">
                <div class="metric-card-summary">
                    <div class="metric-title"><i class="ti ti-wallet me-1 text-info"></i>Total Bayar</div>
                    <div class="metric-value text-primary fs-6" id="sum-nominal-display">Rp 0</div>
                    <input type="hidden" class="money" id="sum-nominal" value="0">
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Action Bar untuk Mobile & Desktop -->
        <div class="sticky-bottom-summary rounded-top">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div>
                    <small class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase;">Total Pembayaran</small>
                    <span class="fw-bold fs-5 text-success" id="sticky-total-bayar">Rp 0</span>
                </div>
                <button type="button" class="btn btn-success btn-touch-target px-3" onclick="processPayment()">
                    <i class="ti ti-cash me-1"></i> Bayar Sekarang
                </button>
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
        const isChecked = $(this).is(':checked');
        $('.row-check').prop('checked', isChecked);
        $('.item-card-mobile').toggleClass('is-selected', isChecked);
        updateSummary();
    });

    $(document).on('change', '.row-check', function() {
        const trOrCard = $(this).closest('.item-card-mobile, .item-row');
        if (trOrCard.hasClass('item-card-mobile')) {
            trOrCard.toggleClass('is-selected', $(this).is(':checked'));
        }
        updateSummary();
    });

    function getSelectedRows() {
        return $('.row-check:checked').map(function() {
            const container = $(this).closest('.item-row, .item-card-mobile');
            return {
                absensi_id: Number(container.data('id')),
                nominal: Number(container.data('nominal') || 0),
                karyawan: container.find('small.text-muted').first().text().trim()
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
        $('#sum-nominal-display').text(`Rp ${formatMoneyValue(totalNominal)}`);
        $('#sticky-total-bayar').text(`Rp ${formatMoneyValue(totalNominal)}`);
        applyMoneyMask('#sum-nominal');
    }

    function processPayment() {
        const selected = getSelectedRows();
        if (!selected.length) {
            toastr.error('Pilih minimal satu absensi untuk dibayar');
            return;
        }

        Swal.fire({
            title: 'Pembayaran gaji',
            html: `
                <div class="text-start">
                    <label class="form-label">Tanggal Bayar</label>
                    <input type="date" id="swal-tanggal-bayar" class="form-control" value="${new Date().toISOString().slice(0, 10)}">
                    <label class="form-label mt-3">Saldo Pembayaran</label>
                    <select id="swal-saldo-channel" class="form-select">
                        <option value="CASH">Tunai</option>
                        <option value="NONCASH">Non Tunai</option>
                    </select>
                    <div id="swal-saldo-target-wrapper" class="mt-2" style="display:none;">
                        <label class="form-label">Sumber Saldo Tunai</label>
                        <select id="swal-saldo-target" class="form-select">
                            <option value="TOKO">Saldo Toko</option>
                            <option value="PEMILIK" <?= ($canDeleteAkses ? '' : 'disabled') ?>>Saldo Pemilik</option>
                        </select>
                    </div>
                </div>
            `,
            didOpen: () => {
                const toggleTarget = () => {
                    $('#swal-saldo-target-wrapper').toggle($('#swal-saldo-channel').val() === 'CASH');
                };
                $('#swal-saldo-channel').on('change', toggleTarget);
                toggleTarget();
            },
            showCancelButton: true,
            confirmButtonText: 'Proses bayar',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const tanggalBayar = $('#swal-tanggal-bayar').val();
                const saldoChannel = $('#swal-saldo-channel').val();
                const saldoTarget = $('#swal-saldo-target').val() || 'TOKO';
                if (!tanggalBayar) {
                    Swal.showValidationMessage('Tanggal bayar wajib dipilih');
                    return false;
                }
                return {
                    tanggal_bayar: tanggalBayar,
                    saldo_channel: saldoChannel,
                    saldo_target: saldoTarget
                };
            }
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('<?= base_url('/absensi/process-payment') ?>', {
                tanggal_bayar: result.value.tanggal_bayar,
                saldo_channel: result.value.saldo_channel,
                saldo_target: result.value.saldo_target,
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
