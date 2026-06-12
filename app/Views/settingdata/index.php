<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
/**
 * @var string $akses_menu
 * @var array $settings
 * @var array $logos
 */
?>
<div class="body-wrapper">
    <div class="container-fluid">
        <div class="card bg-secondary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h4 class="fw-semibold mb-2">Setting Data Aplikasi</h4>
                        <p class="mb-0">Pengaturan konstanta global dan logo aplikasi.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <span class="badge bg-secondary-subtle text-secondary">URL /setting-data</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-7">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h5 class="fw-semibold mb-1">Konstanta Aplikasi</h5>
                                <small class="text-muted">Nilai disimpan di tabel <code>const</code> dan dipakai langsung oleh modul terkait.</small>
                            </div>
                            <i class="ti ti-adjustments fs-7 text-secondary"></i>
                        </div>

                        <form id="form-setting-data">
                            <div class="row g-3">
                                <?php foreach ($settings as $setting) : ?>
                                    <?php
                                    $rkey = (string) ($setting['rkey'] ?? '');
                                    $isNumber = ($setting['type'] ?? '') === 'number';
                                    ?>
                                    <div class="col-12">
                                        <div class="border rounded-2 p-3">
                                            <div class="row g-3 align-items-start">
                                                <div class="col-lg-5">
                                                    <div class="fw-semibold"><?= esc($setting['label'] ?? $rkey) ?></div>
                                                    <code><?= esc($rkey) ?></code>
                                                    <div class="small text-muted mt-2"><?= esc($setting['description'] ?? '-') ?></div>
                                                </div>
                                                <div class="col-lg-7">
                                                    <label class="form-label">Nilai</label>
                                                    <div class="input-group">
                                                        <input type="<?= $isNumber ? 'number' : 'text' ?>" class="form-control" name="<?= esc($rkey) ?>" value="<?= esc($setting['nilai'] ?? '') ?>" <?= $isNumber ? 'min="0" step="0.01"' : '' ?> required>
                                                        <span class="input-group-text"><?= esc($setting['suffix'] ?? '') ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary" id="btn-save-setting"><i class="ti ti-device-floppy"></i> Simpan Setting</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h5 class="fw-semibold mb-1">Logo Aplikasi</h5>
                                <small class="text-muted">Upload PNG baru akan mengganti file lama dengan nama yang sama.</small>
                            </div>
                            <i class="ti ti-photo-up fs-7 text-primary"></i>
                        </div>

                        <div class="row g-3 mb-3">
                            <?php foreach ($logos as $key => $logo) : ?>
                                <div class="col-md-6">
                                    <div class="border rounded-2 p-3 h-100">
                                        <div class="bg-light rounded-2 p-3 text-center mb-2">
                                            <?php if (! empty($logo['exists'])) : ?>
                                                <img src="<?= esc($logo['url']) ?>" alt="<?= esc($logo['label']) ?>" class="img-fluid" style="max-height:76px;">
                                            <?php else : ?>
                                                <div class="text-muted py-4">Belum ada file</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fw-semibold"><?= esc($logo['label'] ?? $key) ?></div>
                                        <code><?= esc($logo['filename'] ?? '-') ?></code>
                                        <div class="small text-muted mt-2"><?= esc($logo['description'] ?? '-') ?></div>
                                        <div class="small text-muted mt-2">Update: <?= esc($logo['updated_at'] ?? '-') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <form id="form-upload-logo" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Upload Logo Zulfa Colour</label>
                                <input type="file" class="form-control" name="logo_color" accept="image/png">
                                <small class="text-muted">Target file: <code>zulfa-logo-color.png</code></small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload Logo Zulfa BW</label>
                                <input type="file" class="form-control" name="logo_bw" accept="image/png">
                                <small class="text-muted">Target file: <code>zulfa-logo-bw.png</code></small>
                            </div>
                            <button type="submit" class="btn btn-success w-100" id="btn-upload-logo"><i class="ti ti-upload"></i> Upload & Replace Logo</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content') ?>

<?= $this->section('javascript') ?>
<script>
    const akses_menu = <?= $akses_menu ?>;
    const canUpdate = akses_menu?.akses_update === 'Y';

    $(function() {
        $('#btn-save-setting, #btn-upload-logo').prop('disabled', !canUpdate);
        if (!canUpdate) {
            $('#form-setting-data :input, #form-upload-logo :input').prop('disabled', true);
        }
    });

    $('#form-setting-data').on('submit', function(e) {
        e.preventDefault();
        if (!canUpdate) {
            toastr.error('Anda tidak memiliki akses update setting data');
            return;
        }

        $.ajax({
            type: 'POST',
            url: '<?= base_url('/setting-data/save') ?>',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Setting data berhasil disimpan');
                    return;
                }
                toastr.error(res.data || 'Gagal menyimpan setting data');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal menyimpan setting data'));
            }
        });
    });

    $('#form-upload-logo').on('submit', function(e) {
        e.preventDefault();
        if (!canUpdate) {
            toastr.error('Anda tidak memiliki akses upload logo');
            return;
        }

        const formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: '<?= base_url('/setting-data/upload-logo') ?>',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Logo berhasil diupload');
                    setTimeout(() => window.location.reload(), 700);
                    return;
                }
                toastr.error(res.data || 'Gagal upload logo');
            },
            error: function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal upload logo'));
            }
        });
    });
</script>
<?= $this->endSection('javascript') ?>
