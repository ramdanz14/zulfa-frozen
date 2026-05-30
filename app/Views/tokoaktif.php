<?= $this->extend('layouts/base'); ?>

<?= $this->section('content'); ?>
<div class="body-wrapper">
    <div class="container-fluid">
        <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Toko Aktif</h4>
                        <p class="mb-0">Pilih toko yang ingin digunakan untuk session saat ini.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <span class="badge bg-primary fs-2 px-3 py-2">Aktif: <?= esc(session('toko_id')); ?> - <?= esc(session('toko_nama')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach ($tokoList as $toko): ?>
                <?php $isActive = (string) $activeTokoId === (string) $toko['toko_id']; ?>
                <div class="col-sm-6 col-xl-4 d-flex align-items-stretch">
                    <div class="card w-100 border <?= $isActive ? 'border-primary' : 'border-light'; ?> shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div>
                                    <h5 class="card-title mb-1"><?= esc($toko['toko_id']); ?></h5>
                                    <p class="text-muted mb-0"><?= esc($toko['toko_nama']); ?></p>
                                </div>
                                <?php if ($isActive): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= esc($toko['toko_theme']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <div class="small text-muted mb-1">Alamat</div>
                                <div><?= esc($toko['toko_alamat'] ?: '-'); ?></div>
                            </div>

                            <div class="mt-auto">
                                <button
                                    type="button"
                                    class="btn <?= $isActive ? 'btn-light text-muted' : 'btn-primary'; ?> w-100 btn-switch-toko"
                                    data-toko-id="<?= esc($toko['toko_id']); ?>"
                                    data-toko-nama="<?= esc($toko['toko_nama']); ?>"
                                    <?= $isActive ? 'disabled' : ''; ?>>
                                    <?= $isActive ? 'Sedang Aktif' : 'Pilih Toko'; ?>
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
