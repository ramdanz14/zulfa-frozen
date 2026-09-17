<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<?php
$profile = $profile ?? [];
$avatarOptions = $avatarOptions ?? [];
$currentAvatar = (string) ($profile['avatar'] ?? session('avatar') ?? 'user-1.jpg');
?>

<style>
    /* Styling & Anti-Slop Layout Mobile / Human Optimizations */
    :root {
        --color-focus-ring: #0284c7;
        --color-card-border: #e2e8f0;
    }

    .form-control:focus,
    .btn:focus-visible {
        outline: 2px solid var(--color-focus-ring) !important;
        outline-offset: 1px !important;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2) !important;
    }

    /* Touch target minimum 44px */
    .btn,
    .form-control,
    .input-group-text {
        min-height: 44px !important;
    }

    /* Profile ID Card */
    .id-card-header {
        background: linear-gradient(135deg, #0284c7 0%, #059669 100%);
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    .info-tile {
        background: #ffffff;
        border: 1px solid var(--color-card-border);
        border-radius: 8px;
        padding: 10px 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .info-tile:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    /* Guidance Alert (High Contrast WCAG AAA) */
    .guide-banner {
        background-color: #eff6ff;
        border-left: 4px solid #2563eb;
        border-radius: 6px;
        padding: 10px 14px;
        color: #1e3a8a;
        font-size: 0.835rem;
        line-height: 1.5;
    }

    .guide-banner strong {
        color: #1e3a8a;
    }

    /* Horizontal Avatar Carousel */
    .avatar-carousel-wrapper {
        position: relative;
    }

    .avatar-carousel-rail {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        padding: 8px 4px 14px 4px;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    .avatar-carousel-rail::-webkit-scrollbar {
        height: 6px;
    }

    .avatar-carousel-rail::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 10px;
    }

    .avatar-carousel-rail::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .avatar-card-item {
        flex: 0 0 96px;
        scroll-snap-align: start;
        text-align: center;
        cursor: pointer;
        border-radius: 12px;
        padding: 10px 6px;
        border: 2px solid transparent;
        background: #f8fafc;
        transition: all 0.2s ease;
        position: relative;
        user-select: none;
    }

    .avatar-card-item:hover {
        background: #f1f5f9;
        transform: translateY(-2px);
    }

    .avatar-card-item.is-selected {
        border-color: #0284c7;
        background: #e0f2fe;
        box-shadow: 0 4px 10px rgba(2, 132, 199, 0.15);
    }

    .avatar-card-item .avatar-img {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease;
    }

    .avatar-card-item.is-selected .avatar-img {
        border-color: #0284c7;
        transform: scale(1.05);
    }

    .avatar-selected-badge {
        position: absolute;
        top: 6px;
        right: 12px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #0284c7;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }

    /* Carousel Nav Arrow Buttons */
    .carousel-nav-btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
        font-size: 1rem;
    }

    .carousel-nav-btn:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
    }

    /* Password Match Indicator */
    .pwd-feedback-text {
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 4px;
    }

    @media (max-width: 767.98px) {
        .avatar-card-item {
            flex: 0 0 88px;
            padding: 8px 4px;
        }

        .avatar-card-item .avatar-img {
            width: 58px;
            height: 58px;
        }
    }
</style>

<div class="body-wrapper pb-5">
    <div class="container-fluid p-0">
        <!-- Header Banner (Compact) -->
        <div class="card bg-primary-subtle shadow-none position-relative overflow-hidden mb-3">
            <div class="card-body px-3 px-md-4 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-semibold mb-1 fs-5"><i class="ti ti-user-circle text-primary me-1"></i> Profil Pengguna</h4>
                        <small class="text-muted d-block">Kelola identitas akun, ganti password, dan sesuaikan avatar Anda.</small>
                    </div>
                    <span class="badge bg-primary px-3 py-2 text-white d-none d-sm-inline-flex align-items-center gap-1">
                        <i class="ti ti-shield-check"></i> Akun Terverifikasi
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- ======================================================== -->
            <!-- KOLOM KIRI: ID CARD & INFO KARYAWAN                      -->
            <!-- ======================================================== -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
                    <!-- Kartu Header ID Karyawan -->
                    <div class="p-3 p-md-4 text-white id-card-header">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <span class="badge bg-white text-primary fw-bold text-uppercase px-2 py-1 mb-2" style="font-size: 0.72rem;">
                                    Kartu Karyawan
                                </span>
                                <h4 class="mb-1 fw-bold text-white text-truncate" style="max-width: 220px;" title="<?= esc($profile['fullname'] ?? '-') ?>">
                                    <?= esc($profile['fullname'] ?? '-') ?>
                                </h4>
                                <div class="text-white-50 small">
                                    <i class="ti ti-briefcase me-1"></i> <?= esc($profile['level_name'] ?? 'User') ?>
                                </div>
                            </div>
                            <div class="position-relative flex-shrink-0">
                                <img id="profile-avatar-preview" 
                                     src="<?= base_url('/assets/images/profile/' . $currentAvatar) ?>" 
                                     class="rounded-circle border border-3 border-white shadow-sm object-fit-cover" 
                                     width="84" height="84" alt="Avatar Aktif">
                                <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success border border-2 border-white p-1" title="Online">
                                    <span class="visually-hidden">Online</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Biodata Karyawan -->
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-2">
                            <!-- Karyawan ID -->
                            <div class="col-12">
                                <div class="info-tile">
                                    <small class="text-muted d-block"><i class="ti ti-id me-1 text-primary"></i> ID Karyawan</small>
                                    <div class="fw-bold fs-6 text-dark"><?= esc($profile['karyawan_id'] ?? '-') ?></div>
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="col-6">
                                <div class="info-tile h-100">
                                    <small class="text-muted d-block"><i class="ti ti-user me-1 text-info"></i> Username</small>
                                    <div class="fw-semibold text-dark text-truncate"><?= esc($profile['username'] ?? '-') ?></div>
                                </div>
                            </div>

                            <!-- Role Level -->
                            <div class="col-6">
                                <div class="info-tile h-100">
                                    <small class="text-muted d-block"><i class="ti ti-shield me-1 text-success"></i> Jabatan / Role</small>
                                    <div class="fw-semibold text-dark text-truncate"><?= esc($profile['level_name'] ?? '-') ?></div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-12 col-sm-6">
                                <div class="info-tile h-100">
                                    <small class="text-muted d-block"><i class="ti ti-mail me-1 text-warning"></i> Email</small>
                                    <div class="fw-semibold text-dark text-truncate" title="<?= esc($profile['email'] ?? '-') ?>"><?= esc($profile['email'] ?? '-') ?></div>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-12 col-sm-6">
                                <div class="info-tile h-100">
                                    <small class="text-muted d-block"><i class="ti ti-phone me-1 text-danger"></i> No. HP / Telepon</small>
                                    <div class="fw-semibold text-dark"><?= esc($profile['phone'] ?? '-') ?></div>
                                </div>
                            </div>

                            <!-- Alamat -->
                            <div class="col-12">
                                <div class="info-tile">
                                    <small class="text-muted d-block"><i class="ti ti-map-pin me-1 text-secondary"></i> Alamat</small>
                                    <div class="fw-semibold text-dark small" style="line-height: 1.4;"><?= esc($profile['alamat'] ?? '-') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- KOLOM KANAN: GANTI PASSWORD & CAROUSEL AVATAR             -->
            <!-- ======================================================== -->
            <div class="col-12 col-xl-7">
                <!-- 1. FORM GANTI PASSWORD -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold fs-6 text-dark">
                            <i class="ti ti-lock text-primary me-1"></i> Ganti Password Akun
                        </h5>
                        <span class="badge bg-light text-muted border small"><i class="ti ti-key"></i> Keamanan</span>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <!-- Hint & Tips Keamanan Password -->
                        <div class="guide-banner mb-3">
                            <div class="d-flex align-items-start gap-2">
                                <i class="ti ti-info-circle fs-5 flex-shrink-0 mt-1 text-primary"></i>
                                <div>
                                    <strong>Tips Keamanan Password:</strong>
                                    <ul class="mb-0 ps-3 mt-1">
                                        <li>Password baru <strong>minimal 4 karakter</strong> (disarankan kombinasi huruf & angka).</li>
                                        <li>Hindari password mudah ditebak seperti tanggal lahir atau nomor telepon.</li>
                                        <li>Klik ikon mata <i class="ti ti-eye"></i> untuk memastikan Anda tidak salah ketik.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <form id="form-password" novalidate>
                            <div class="row g-3">
                                <!-- Password Lama -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-1" for="old_password">
                                        Password Lama <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="old_password" required placeholder="Password saat ini">
                                        <button type="button" class="btn btn-outline-secondary btn-toggle-pwd" tabindex="-1" aria-label="Lihat Password Lama">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Password Baru -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-1" for="new_password">
                                        Password Baru <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" required placeholder="Min 4 karakter">
                                        <button type="button" class="btn btn-outline-secondary btn-toggle-pwd" tabindex="-1" aria-label="Lihat Password Baru">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </div>
                                    <div id="pwd-length-feedback" class="pwd-feedback-text text-muted"></div>
                                </div>

                                <!-- Konfirmasi Password Baru -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-1" for="confirm_password">
                                        Konfirmasi Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" required placeholder="Ulangi password baru">
                                        <button type="button" class="btn btn-outline-secondary btn-toggle-pwd" tabindex="-1" aria-label="Lihat Konfirmasi Password">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </div>
                                    <div id="pwd-match-feedback" class="pwd-feedback-text"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3 pt-2 border-top">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold" id="btn-submit-password">
                                    <i class="ti ti-check me-1"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 2. FORM GANTI AVATAR (HORIZONTAL CAROUSEL SWIPE) -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0 fw-bold fs-6 text-dark">
                                <i class="ti ti-palette text-success me-1"></i> Ganti Avatar Foto Profil
                            </h5>
                            <small class="text-muted">Geser ke samping untuk memilih avatar favorit Anda</small>
                        </div>

                        <!-- Tombol Navigasi Carousel Panah Kiri-Kanan -->
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="carousel-nav-btn" id="btn-carousel-prev" aria-label="Geser ke kiri">
                                <i class="ti ti-chevron-left"></i>
                            </button>
                            <button type="button" class="carousel-nav-btn" id="btn-carousel-next" aria-label="Geser ke kanan">
                                <i class="ti ti-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <form id="form-avatar">
                            <!-- Horizontal Swipeable Rail -->
                            <div class="avatar-carousel-wrapper">
                                <div class="avatar-carousel-rail" id="avatar-carousel">
                                    <?php foreach ($avatarOptions as $avatar) : ?>
                                        <?php $isSelected = ($avatar === $currentAvatar); ?>
                                        <div class="avatar-card-item <?= $isSelected ? 'is-selected' : '' ?>" data-avatar="<?= esc($avatar) ?>">
                                            <input class="form-check-input d-none avatar-radio" type="radio" name="avatar" value="<?= esc($avatar) ?>" <?= $isSelected ? 'checked' : '' ?>>
                                            
                                            <?php if ($isSelected) : ?>
                                                <span class="avatar-selected-badge" title="Avatar Terpilih"><i class="ti ti-check"></i></span>
                                            <?php endif; ?>

                                            <img src="<?= base_url('/assets/images/profile/' . $avatar) ?>" 
                                                 class="avatar-img mb-1" 
                                                 alt="<?= esc($avatar) ?>">
                                            
                                            <div class="small fw-semibold text-truncate text-secondary" style="font-size: 0.72rem;">
                                                <?= esc(pathinfo($avatar, PATHINFO_FILENAME)) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Petunjuk Sentuh di HP -->
                            <div class="d-flex justify-content-between align-items-center mt-2 text-muted small">
                                <span><i class="ti ti-arrows-left-right me-1"></i> Swipe / geser jari untuk melihat opsi lain</span>
                                <span class="badge bg-light text-secondary border"><?= count($avatarOptions) ?> Pilihan Avatar</span>
                            </div>

                            <div class="d-flex justify-content-end mt-3 pt-2 border-top">
                                <button type="submit" class="btn btn-success px-4 fw-semibold" id="btn-submit-avatar">
                                    <i class="ti ti-photo me-1"></i> Terapkan Avatar
                                </button>
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
    $(function() {
        // Auto scroll carousel agar avatar yang saat ini aktif langsung terlihat di layar
        const $activeCard = $('#avatar-carousel .avatar-card-item.is-selected');
        if ($activeCard.length) {
            const rail = document.getElementById('avatar-carousel');
            const cardLeft = $activeCard.position().left;
            rail.scrollLeft = cardLeft - 20;
        }

        // Real-time Password Strength & Match Checking
        $('#new_password, #confirm_password').on('input', function() {
            validatePasswordInputsLive();
        });

        // Show / Hide Password Toggle
        $('.btn-toggle-pwd').on('click', function() {
            const $input = $(this).closest('.input-group').find('input');
            const isPassword = $input.attr('type') === 'password';
            $input.attr('type', isPassword ? 'text' : 'password');
            $(this).find('i').toggleClass('ti-eye ti-eye-off');
        });

        // Carousel Nav Arrow Buttons
        $('#btn-carousel-prev').on('click', function() {
            const rail = document.getElementById('avatar-carousel');
            rail.scrollBy({ left: -220, behavior: 'smooth' });
        });

        $('#btn-carousel-next').on('click', function() {
            const rail = document.getElementById('avatar-carousel');
            rail.scrollBy({ left: 220, behavior: 'smooth' });
        });
    });

    // Validasi Real-time Password Baru & Konfirmasi
    function validatePasswordInputsLive() {
        const newPwd = $('#new_password').val().trim();
        const confirmPwd = $('#confirm_password').val().trim();

        // 1. Cek panjang password baru
        const $lenFeedback = $('#pwd-length-feedback');
        if (newPwd.length === 0) {
            $lenFeedback.html('');
            $('#new_password').removeClass('is-valid is-invalid');
        } else if (newPwd.length < 4) {
            $lenFeedback.html('<span class="text-danger"><i class="ti ti-alert-triangle"></i> Terlalu pendek (min 4 karakter).</span>');
            $('#new_password').addClass('is-invalid').removeClass('is-valid');
        } else {
            $lenFeedback.html('<span class="text-success"><i class="ti ti-check"></i> Panjang password memenuhi syarat.</span>');
            $('#new_password').addClass('is-valid').removeClass('is-invalid');
        }

        // 2. Cek kesamaan konfirmasi password
        const $matchFeedback = $('#pwd-match-feedback');
        if (confirmPwd.length === 0) {
            $matchFeedback.html('');
            $('#confirm_password').removeClass('is-valid is-invalid');
        } else if (confirmPwd !== newPwd) {
            $matchFeedback.html('<span class="text-danger"><i class="ti ti-x"></i> Konfirmasi password belum cocok.</span>');
            $('#confirm_password').addClass('is-invalid').removeClass('is-valid');
        } else {
            $matchFeedback.html('<span class="text-success"><i class="ti ti-check"></i> Password cocok.</span>');
            $('#confirm_password').addClass('is-valid').removeClass('is-invalid');
        }
    }

    // Ganti Password Handler
    $('#form-password').on('submit', function(e) {
        e.preventDefault();

        const oldPassword = $('#old_password').val().trim();
        const newPassword = $('#new_password').val().trim();
        const confirmPassword = $('#confirm_password').val().trim();

        if (!oldPassword || !newPassword || !confirmPassword) {
            toastr.error('Semua kolom password wajib diisi.');
            return;
        }

        if (newPassword.length < 4) {
            toastr.warning('Password baru harus terdiri dari minimal 4 karakter.');
            $('#new_password').focus();
            return;
        }

        if (newPassword !== confirmPassword) {
            toastr.error('Konfirmasi password baru tidak cocok. Periksa kembali.');
            $('#confirm_password').focus();
            return;
        }

        Swal.fire({
            title: 'Simpan Password Baru?',
            text: 'Password login akun Anda akan langsung diperbarui.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="ti ti-check me-1"></i> Ya, Perbarui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const $btn = $('#btn-submit-password');
            const origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.post('<?= base_url('/profile/change-password') ?>', {
                old_password: oldPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            }, function(res) {
                $btn.prop('disabled', false).html(origHtml);
                if (res.tipe === 'success') {
                    toastr.success(res.data || 'Password berhasil diubah.');
                    $('#form-password')[0].reset();
                    $('#new_password, #confirm_password').removeClass('is-valid is-invalid');
                    $('#pwd-length-feedback, #pwd-match-feedback').html('');
                    return;
                }
                toastr.error(res.data || 'Gagal mengubah password.');
            }, 'json').fail(function(xhr) {
                $btn.prop('disabled', false).html(origHtml);
                toastr.error(extractErrorMessage(xhr, 'Gagal mengubah password'));
            });
        });
    });

    // Pilih Avatar dari Carousel
    $(document).on('click', '.avatar-card-item', function() {
        $('.avatar-card-item').removeClass('is-selected').find('.avatar-selected-badge').remove();
        $(this).addClass('is-selected');
        $(this).append('<span class="avatar-selected-badge" title="Avatar Terpilih"><i class="ti ti-check"></i></span>');
        $(this).find('.avatar-radio').prop('checked', true);

        // Real-time Preview di Kartu Karyawan Sebelah Kiri
        const selectedAvatar = $(this).data('avatar');
        if (selectedAvatar) {
            const previewUrl = `<?= base_url('/assets/images/profile') ?>/${selectedAvatar}`;
            $('#profile-avatar-preview').attr('src', previewUrl);
        }
    });

    // Simpan Avatar Handler
    $('#form-avatar').on('submit', function(e) {
        e.preventDefault();
        const avatar = $('input[name="avatar"]:checked').val() || '';
        if (!avatar) {
            toastr.warning('Silakan pilih salah satu avatar terlebih dulu.');
            return;
        }

        const $btn = $('#btn-submit-avatar');
        const origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menerapkan...');

        $.post('<?= base_url('/profile/change-avatar') ?>', {
            avatar: avatar
        }, function(res) {
            $btn.prop('disabled', false).html(origHtml);
            if (res.tipe === 'success') {
                const newUrl = `<?= base_url('/assets/images/profile') ?>/${res.avatar}`;
                $('#profile-avatar-preview').attr('src', newUrl);
                $('.topbar-avatar, .topbar-avatar-lg, .sidebar-avatar').attr('src', newUrl);
                toastr.success(res.data || 'Avatar profil berhasil diperbarui.');
                return;
            }
            toastr.error(res.data || 'Gagal mengubah avatar.');
        }, 'json').fail(function(xhr) {
            $btn.prop('disabled', false).html(origHtml);
            toastr.error(extractErrorMessage(xhr, 'Gagal mengubah avatar'));
        });
    });
</script>
<?= $this->endSection('javascript') ?>
