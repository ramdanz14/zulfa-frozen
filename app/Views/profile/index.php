<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$profile = $profile ?? [];
$avatarOptions = $avatarOptions ?? [];
$currentAvatar = (string) ($profile['avatar'] ?? session('avatar') ?? 'user-1.jpg');
?>
<div class="body-wrapper">
    <div class="container-fluid p-0">
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
                <div class="row align-items-center">
                    <div class="col-9">
                        <h4 class="fw-semibold mb-2">My Profile</h4>
                        <p class="mb-0">Informasi akun pribadi dan pengaturan password/avatar.</p>
                    </div>
                    <div class="col-3">
                        <div class="text-center mb-n5">
                            <img src="<?= base_url(); ?>/assets/images/breadcrumb/ChatBc.png" alt="modernize-img" class="img-fluid mb-n4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-5">
                <div class="card border-0 shadow-lg overflow-hidden">
                    <div class="card-body p-0">
                        <div class="p-4 text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #1a8754 100%);">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="small text-uppercase opacity-75">Employee ID Card</div>
                                    <h3 class="mb-1 fw-bold"><?= esc($profile['fullname'] ?? '-') ?></h3>
                                    <div class="opacity-75"><?= esc($profile['level_name'] ?? '-') ?></div>
                                </div>
                                <img id="profile-avatar-preview" src="<?= base_url('/assets/images/profile/' . $currentAvatar) ?>" class="rounded-4 border border-3 border-white object-fit-cover" width="96" height="96" alt="Avatar">
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="border rounded-3 p-3">
                                        <small class="text-muted d-block">Karyawan ID</small>
                                        <div class="fw-semibold fs-5"><?= esc($profile['karyawan_id'] ?? '-') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block">Username</small>
                                        <div class="fw-semibold"><?= esc($profile['username'] ?? '-') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block">Role</small>
                                        <div class="fw-semibold"><?= esc($profile['level_name'] ?? '-') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block">Email</small>
                                        <div class="fw-semibold"><?= esc($profile['email'] ?? '-') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block">Phone</small>
                                        <div class="fw-semibold"><?= esc($profile['phone'] ?? '-') ?></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="border rounded-3 p-3">
                                        <small class="text-muted d-block">Alamat</small>
                                        <div class="fw-semibold"><?= esc($profile['alamat'] ?? '-') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Ganti Password</h5>
                    </div>
                    <div class="card-body">
                        <form id="form-password">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Password Lama</label>
                                    <input type="password" class="form-control" id="old_password" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ganti Avatar</h5>
                        <small class="text-muted">Pilih avatar dari folder profile yang sudah tersedia</small>
                    </div>
                    <div class="card-body">
                        <form id="form-avatar">
                            <div class="row g-3" id="avatar-grid">
                                <?php foreach ($avatarOptions as $avatar) : ?>
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <label class="card avatar-option h-100 border <?= $avatar === $currentAvatar ? 'border-primary shadow-sm' : '' ?>" data-avatar="<?= esc($avatar) ?>" style="cursor:pointer;">
                                            <div class="card-body text-center p-3">
                                                <input class="form-check-input d-none avatar-radio" type="radio" name="avatar" value="<?= esc($avatar) ?>" <?= $avatar === $currentAvatar ? 'checked' : '' ?>>
                                                <img src="<?= base_url('/assets/images/profile/' . $avatar) ?>" class="rounded-circle mb-2 object-fit-cover" width="72" height="72" alt="<?= esc($avatar) ?>">
                                                <div class="small fw-semibold text-break"><?= esc($avatar) ?></div>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-success">Update Avatar</button>
                            </div>
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
    $('#form-password').on('submit', function(e) {
        e.preventDefault();

        const oldPassword = $('#old_password').val().trim();
        const newPassword = $('#new_password').val().trim();
        const confirmPassword = $('#confirm_password').val().trim();

        if (!oldPassword || !newPassword || !confirmPassword) {
            toastr.error('Semua field password wajib diisi');
            return;
        }

        Swal.fire({
            title: 'Ganti password?',
            text: 'Password login Anda akan langsung berubah.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, ubah',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('<?= base_url('/profile/change-password') ?>', {
                old_password: oldPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            }, function(res) {
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Password berhasil diubah');
                    $('#form-password')[0].reset();
                    return;
                }
                toastr.error(res.data || 'Gagal mengubah password');
            }, 'json').fail(function(xhr) {
                toastr.error(extractErrorMessage(xhr, 'Gagal mengubah password'));
            });
        });
    });

    $(document).on('click', '.avatar-option', function() {
        $('.avatar-option').removeClass('border-primary shadow-sm');
        $(this).addClass('border-primary shadow-sm');
        $(this).find('.avatar-radio').prop('checked', true);
    });

    $('#form-avatar').on('submit', function(e) {
        e.preventDefault();
        const avatar = $('input[name="avatar"]:checked').val() || '';
        if (!avatar) {
            toastr.error('Pilih avatar terlebih dulu');
            return;
        }

        $.post('<?= base_url('/profile/change-avatar') ?>', {
            avatar
        }, function(res) {
            if (res.tipe === 'success') {
                const newUrl = `<?= base_url('/assets/images/profile') ?>/${res.avatar}`;
                $('#profile-avatar-preview').attr('src', newUrl);
                $('.topbar-avatar, .topbar-avatar-lg, .sidebar-avatar').attr('src', newUrl);
                toastr.success(res.data || 'Avatar berhasil diubah');
                return;
            }
            toastr.error(res.data || 'Gagal mengubah avatar');
        }, 'json').fail(function(xhr) {
            toastr.error(extractErrorMessage(xhr, 'Gagal mengubah avatar'));
        });
    });
</script>
<?= $this->endSection('javascript') ?>
