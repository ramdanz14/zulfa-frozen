<?= $this->extend('layouts/base'); ?>

<?= $this->section('content'); ?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="row align-items-center">
                    <div class="col-12 col-md-7 mb-2 mb-md-0">
                        <h4 class="fw-semibold mb-1 fs-5 fs-md-4 text-truncate">Toko Aktif</h4>
                        <p class="mb-0 text-muted small">Pilih toko yang digunakan untuk sesi transaksi saat ini.</p>
                    </div>
                    <div class="col-12 col-md-5 text-md-end">
                        <span class="badge bg-primary fs-3 px-3 py-2 text-wrap"><i class="ti ti-building-store me-1"></i> <?= esc(session('toko_id')); ?> - <?= esc(session('toko_nama')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 g-md-3">
            <?php foreach ($tokoList as $toko): ?>
                <?php $isActive = (string) $activeTokoId === (string) $toko['toko_id']; ?>
                <div class="col-12 col-sm-6 col-xl-4 d-flex align-items-stretch">
                    <div class="card w-100 border <?= $isActive ? 'border-2 border-primary shadow' : 'border-light-subtle shadow-sm'; ?> rounded-3">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-2 pb-2 border-bottom">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary font-monospace small px-2 py-1 mb-1"><?= esc($toko['toko_id']); ?></span>
                                    <h5 class="card-title fw-bold mb-0 text-dark"><?= esc($toko['toko_nama']); ?></h5>
                                </div>
                                <?php if ($isActive): ?>
                                    <span class="badge bg-success-subtle text-success px-2 py-1"><i class="ti ti-check me-1"></i>Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1"><?= esc($toko['toko_theme']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3 flex-grow-1">
                                <div class="small text-muted mb-1" style="font-size:0.75rem;">Alamat Cabang:</div>
                                <div class="small text-dark text-break"><?= esc($toko['toko_alamat'] ?: '-'); ?></div>
                                <?php if (!empty($toko['toko_phone'])): ?>
                                    <div class="small text-muted mt-2"><i class="ti ti-phone me-1"></i><?= esc($toko['toko_phone']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto pt-2">
                                <button
                                    type="button"
                                    class="btn <?= $isActive ? 'btn-light border text-muted disabled' : 'btn-primary btn-touch-target'; ?> w-100 btn-switch-toko py-2"
                                    style="min-height: 42px;"
                                    data-toko-id="<?= esc($toko['toko_id']); ?>"
                                    data-toko-nama="<?= esc($toko['toko_nama']); ?>"
                                    <?= $isActive ? 'disabled' : ''; ?>>
                                    <?php if ($isActive): ?>
                                        <i class="ti ti-circle-check me-1"></i> Sedang Digunakan
                                    <?php else: ?>
                                        <i class="ti ti-arrows-left-right me-1"></i> Pilih Toko Ini
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('javascript'); ?>
<script>
    $(function() {
        $('.btn-switch-toko').on('click', function() {
            const button = $(this);
            const tokoId = button.data('toko-id');
            const tokoNama = button.data('toko-nama');

            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi pindah toko',
                text: `Apakah anda ingin berpindah ke toko ${tokoId} - ${tokoNama} ?`,
                showCancelButton: true,
                confirmButtonText: 'Ya, pindah',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                button.prop('disabled', true);

                $.ajax({
                    url: '<?= base_url('tokoaktif/switch'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        toko_id: tokoId
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: response.tipe || 'success',
                            title: 'Berhasil',
                            text: response.data || 'Toko aktif berhasil diubah.',
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        button.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: extractErrorMessage(xhr, 'Gagal mengubah toko aktif.')
                        });
                    }
                });
            });
        });
    });
</script>
<?= $this->endSection(); ?>
