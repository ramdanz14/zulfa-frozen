<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array  $soAktif
 * @var array  $kategoriOptions
 */
$soLabel = !empty($soAktif['tanggal'] ?? '') ? 'SO aktif: ' . $soAktif['tanggal'] : 'Tidak ada SO aktif';
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Stock Opname</h4>
                        <p class="mb-0"><span class="page-pretitle"><?= esc($soLabel) ?></span> | Buat sesi SO, input hasil fisik, lihat hasil, dan adjust ke stok.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <span class="badge <?= !empty($soAktif['tanggal'] ?? '') ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> fs-2">
                            <?= esc($soLabel) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-xl-3">
                <a href="javascript:void(0)" class="card h-100 text-decoration-none" onclick="createSoAll()">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Buat SO All</div>
                        <div class="text-muted small">Load snapshot semua item aktif toko ini ke sesi SO baru.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3">
                <a href="javascript:void(0)" class="card h-100 text-decoration-none" onclick="openKategoriModal()">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Buat SO Kategori</div>
                        <div class="text-muted small">Load snapshot item berdasarkan kategori tertentu.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3">
                <a href="<?= base_url('/so/input') ?>" class="card h-100 text-decoration-none">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Input SO</div>
                        <div class="text-muted small">Input stok fisik per item pada sesi SO aktif.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3">
                <a href="<?= base_url('/so/hasil') ?>" class="card h-100 text-decoration-none">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Hasil SO</div>
                        <div class="text-muted small">Lihat hasil selisih, ringkasan NK/NL, dan progres input.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3">
                <a href="<?= base_url('/so/satuan') ?>" class="card h-100 text-decoration-none">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">SO Satuan</div>
                        <div class="text-muted small">Tambah adjustment manual satuan ke tabel `adjust`.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3">
                <a href="<?= base_url('/so/history') ?>" class="card h-100 text-decoration-none">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">History SO</div>
                        <div class="text-muted small">Lihat riwayat sesi SO yang pernah dibuat di toko ini.</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3">
                <a href="javascript:void(0)" class="card h-100 text-decoration-none" onclick="adjustSoAll()">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Adjust SO All</div>
                        <div class="text-muted small">Kirim semua selisih SO aktif ke tabel `adjust` dengan `istype=SO`.</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-kategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat SO Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Kategori</label>
                <select class="form-select select2" id="kat_id" multiple>
                    <?php foreach ($kategoriOptions as $row): ?>
                        <option value="<?= esc($row['id']) ?>"><?= esc($row['text']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="createSoKategori()">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const kategoriModal = new bootstrap.Modal(document.getElementById('modal-kategori'));
    $(function() {
        $('#kat_id').select2({
            width: '100%',
            placeholder: 'Pilih kategori',
            dropdownParent: $('#modal-kategori')
        });

        <?php if (session()->getFlashdata('so_error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= esc((string) session()->getFlashdata('so_error')) ?>'
            });
        <?php endif; ?>
    });

    function openKategoriModal() {
        $('#kat_id').val(null).trigger('change');
        kategoriModal.show();
    }

    function createSoAll() {
        Swal.fire({
            title: 'Buat SO semua produk?',
            text: 'Snapshot stok toko aktif akan dibuat untuk sesi SO baru.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, proses',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('<?= base_url('/so/create-all') ?>', function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'SO berhasil dibuat');
                    window.location.reload();
                    return;
                }
                toastr.error(res.data || 'Gagal membuat SO');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal membuat SO'));
            });
        });
    }

    function createSoKategori() {
        const katId = $('#kat_id').val() || [];
        if (!katId.length) {
            toastr.error('Kategori wajib dipilih');
            return;
        }
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/so/create-kategori') ?>',
            dataType: 'json',
            traditional: true,
            data: {
                kat_id: katId
            },
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'SO kategori berhasil dibuat');
                    kategoriModal.hide();
                    window.location.reload();
                    return;
                }
                toastr.error(res.data || 'Gagal membuat SO kategori');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal membuat SO kategori'));
            }
        });
    }

    function adjustSoAll() {
        $.post('<?= base_url('/so/summary') ?>', {
            tanggal: 'aktif'
        }, function(summary) {
            Swal.fire({
                title: 'Adjust SO aktif?',
                html: `Periode: <strong>${summary.sum_periode || '-'}</strong><br>
                    Sudah input: <strong>${summary.sum_sudah_input || 0}</strong><br>
                    Belum input: <strong>${summary.sum_belum_input || 0}</strong><br>
                    NK Qty: <strong>${formatMoneyValue(summary.sum_nk_qty || 0)}</strong> | Rp <strong>${formatMoneyValue(summary.sum_nk_rp || 0)}</strong><br>
                    NL Qty: <strong>${formatMoneyValue(summary.sum_nl_qty || 0)}</strong> | Rp <strong>${formatMoneyValue(summary.sum_nl_rp || 0)}</strong><br>
                    NKL Qty: <strong>${formatMoneyValue(summary.sum_nkl_qty || 0)}</strong> | Rp <strong>${formatMoneyValue(summary.sum_nkl_rp || 0)}</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, adjust',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.post('<?= base_url('/so/adjust-all') ?>', function(res) {
                    if (res.tipe === 'success') {
                        toastr.success(res.data || 'Adjust SO berhasil');
                        window.location.reload();
                        return;
                    }
                    toastr.error(res.data || 'Gagal adjust SO');
                }, 'json').fail(function(xhr) {
                    toastr.error(extractErrorMessage(xhr, 'Gagal adjust SO'));
                });
            });
        }, 'json').fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal membaca summary SO'));
        });
    }
</script>
<?= $this->endSection('javascript') ?>