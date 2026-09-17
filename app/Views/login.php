<!DOCTYPE html>
<html lang="id" dir="ltr" data-bs-theme="light" data-color-theme="Cyan_Theme" data-layout="vertical">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Favicon icon -->
    <link rel="shortcut icon" type="image/png" href="<?= base_url(APP_LOGO_PATH); ?>" />

    <!-- Core & Tabler Icons Css -->
    <link rel="stylesheet" href="<?= base_url('/assets/css/styles.css'); ?>" />
    <link rel="stylesheet" href="<?= base_url('/assets/fonts/tabler-icons/tabler-icons.css'); ?>" />

    <title><?= esc(APP_NAME); ?> | Login Sistem Retail POS</title>

    <style>
        :root {
            --retail-navy: #0f172a;
            --retail-slate: #1e293b;
            --retail-blue: #0284c7;
            --retail-emerald: #059669;
            --retail-text-muted: #64748b;
            --retail-border: #e2e8f0;
            --color-focus-ring: #0284c7;
        }

        /* Focus accessibility WCAG AA */
        .form-control:focus,
        .btn:focus-visible {
            outline: 2px solid var(--color-focus-ring) !important;
            outline-offset: 2px !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2) !important;
        }

        /* Touch target minimum 44px */
        .btn,
        .form-control,
        .input-group-text {
            min-height: 46px !important;
        }

        /* Background Layout */
        .login-wrapper {
            min-height: 100vh;
            min-height: 100dvh;
            background-color: #f8fafc;
        }

        /* Left Hero Showcase */
        .retail-hero-panel {
            background-color: var(--retail-navy);
            background-image: 
                radial-gradient(at 10% 10%, rgba(2, 132, 199, 0.18) 0px, transparent 50%),
                radial-gradient(at 90% 90%, rgba(5, 150, 105, 0.14) 0px, transparent 50%);
            color: #ffffff;
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .retail-hero-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 60%, rgba(15, 23, 42, 0.8) 100%);
            pointer-events: none;
        }

        .badge-system-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(15, 118, 110, 0.25);
            color: #a7f3d0;
            border: 1px solid rgba(167, 243, 208, 0.3);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 6px #10b981;
        }

        /* Showcase Card */
        .showcase-feature-card {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 16px;
            backdrop-filter: blur(8px);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .showcase-feature-card:hover {
            border-color: rgba(2, 132, 199, 0.5);
            transform: translateY(-2px);
        }

        .feature-icon-wrapper {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .icon-pos {
            background: rgba(2, 132, 199, 0.2);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }

        .icon-stock {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(52, 211, 153, 0.3);
        }

        .icon-report {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        /* Right Form Side */
        .login-card-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            background-color: #ffffff;
        }

        .login-box {
            width: 100%;
            max-width: 440px;
        }

        .login-brand-header {
            margin-bottom: 2rem;
        }

        .brand-logo-img {
            max-height: 58px;
            width: auto;
            object-fit: contain;
        }

        .input-group-custom .input-group-text {
            background-color: #f8fafc;
            border-color: var(--retail-border);
            color: #64748b;
        }

        .btn-toggle-password {
            background-color: #f8fafc;
            border-color: var(--retail-border);
            color: #64748b;
        }

        .btn-toggle-password:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: #64748b;
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 6px;
        }

        /* Responsive Mobile Behavior (< 992px) */
        @media (max-width: 991.98px) {
            .retail-hero-panel {
                padding: 2rem 1.5rem;
                text-align: center;
                align-items: center;
            }

            .showcase-grid {
                display: none;
            }

            .login-card-container {
                padding: 2rem 1.25rem 3rem 1.25rem;
                background-color: #f8fafc;
            }

            .login-box {
                background: #ffffff;
                padding: 1.75rem 1.25rem;
                border-radius: 14px;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
                border: 1px solid var(--retail-border);
            }
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <img src="<?= base_url(APP_LOGO_PATH); ?>" alt="loader" class="lds-ripple img-fluid" style="max-height: 50px;" />
    </div>

    <div id="main-wrapper" class="login-wrapper">
        <div class="row g-0 min-vh-100">
            <!-- ======================================================== -->
            <!-- LEFT HERO PANEL: PRESENTASI SISTEM RETAIL POS & STOK     -->
            <!-- ======================================================== -->
            <div class="col-lg-6 col-xl-7 retail-hero-panel">
                <!-- Top Brand Badge -->
                <div class="z-index-2 mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="badge-system-live">
                            <span class="status-dot"></span>
                            <span>Zulfa Retail POS & Inventory</span>
                        </div>
                        <span class="badge bg-white-subtle text-white small border border-white-subtle px-3 py-1 d-none d-sm-inline-flex">
                            Enterprise Edition
                        </span>
                    </div>
                </div>

                <!-- Center Value Proposition -->
                <div class="z-index-2 my-auto py-3">
                    <h1 class="display-6 fw-bold text-white mb-3" style="line-height: 1.25;">
                        Solusi Penjualan Kasir &amp; Kontrol Stok Retail yang Handal.
                    </h1>
                    <p class="text-slate-300 fs-5 mb-4" style="color: #cbd5e1; max-width: 580px; line-height: 1.6;">
                        Platform operasional toko modern: transaksi kasir cepat, barcode scanner, multi-satuan grosir/eceran, dan pemantauan stok real-time yang mudah digunakan.
                    </p>

                    <!-- Showcase Feature Cards -->
                    <div class="row g-3 showcase-grid">
                        <!-- Card 1: Kasir POS -->
                        <div class="col-12 col-xl-4">
                            <div class="showcase-feature-card h-100">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="feature-icon-wrapper icon-pos">
                                        <i class="ti ti-cash"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white fw-bold mb-1">Kasir Cepat</h6>
                                        <small style="color: #94a3b8; line-height: 1.4; display: block;">
                                            Barcode scanner, struk thermal kasir, dan hitung poin member.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Kontrol Stok & Multi-Satuan -->
                        <div class="col-12 col-xl-4">
                            <div class="showcase-feature-card h-100">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="feature-icon-wrapper icon-stock">
                                        <i class="ti ti-box-seam"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white fw-bold mb-1">Stok Real-Time</h6>
                                        <small style="color: #94a3b8; line-height: 1.4; display: block;">
                                            Konversi satuan PCS/DUS dan mutasi otomatis gudang-toko.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Margin & Laba -->
                        <div class="col-12 col-xl-4">
                            <div class="showcase-feature-card h-100">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="feature-icon-wrapper icon-report">
                                        <i class="ti ti-chart-arrows"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white fw-bold mb-1">Proteksi Margin</h6>
                                        <small style="color: #94a3b8; line-height: 1.4; display: block;">
                                            Kalkulasi HPP akurat, cegah jual rugi, dan rekap kas harian.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Trust Metrics -->
                <div class="z-index-2 pt-3 border-top border-white-subtle mt-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 text-slate-400 small" style="color: #94a3b8;">
                        <span class="d-inline-flex align-items-center gap-1">
                            <i class="ti ti-bolt text-info"></i> Transaksi Kasir Instan
                        </span>
                        <span class="d-inline-flex align-items-center gap-1">
                            <i class="ti ti-shield-check text-success"></i> Data Aman &amp; Tercatat di Log
                        </span>
                        <span class="d-inline-flex align-items-center gap-1">
                            <i class="ti ti-device-mobile text-warning"></i> Ramah Digunakan di Smartphone
                        </span>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- RIGHT FORM SIDE: LOGIN CARD                              -->
            <!-- ======================================================== -->
            <div class="col-lg-6 col-xl-5 login-card-container">
                <div class="login-box">
                    <!-- Brand Logo & Header -->
                    <div class="login-brand-header text-center">
                        <img src="<?= base_url(APP_LOGO_PATH); ?>" class="brand-logo-img mb-3" alt="<?= esc(APP_NAME); ?>" />
                        <h2 class="fw-bold text-dark fs-6 mb-1 text-uppercase tracking-wider">
                            <?= esc(APP_NAME); ?>
                        </h2>
                        <p class="text-muted small mb-0">
                            Masuk ke akun Anda untuk mengelola kasir dan operasional toko.
                        </p>
                    </div>

                    <!-- Flash Message Alert -->
                    <?php if (session()->getFlashdata('warning')) : ?>
                        <div class="mb-3">
                            <?= session()->getFlashdata('warning'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form Login -->
                    <form class="needs-validation" novalidate method="post" action="<?= esc(current_url(true)); ?>" id="form-login">
                        <!-- Input Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-bold text-dark small text-uppercase mb-1">
                                Username
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text"><i class="ti ti-user fs-5"></i></span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       required autocomplete="username" placeholder="Masukkan username Anda" autofocus>
                                <div class="invalid-feedback">
                                    Username wajib diisi.
                                </div>
                            </div>
                        </div>

                        <!-- Input Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold text-dark small text-uppercase mb-1">
                                Password
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text"><i class="ti ti-lock fs-5"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required autocomplete="current-password" placeholder="Masukkan password Anda">
                                <button type="button" class="btn btn-outline-secondary btn-toggle-password" id="btn-toggle-pwd" tabindex="-1" aria-label="Lihat Password">
                                    <i class="ti ti-eye fs-5"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Password wajib diisi.
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Submit Login -->
                        <button type="submit" class="btn btn-primary w-100 py-2 fs-4 fw-bold mb-3 d-flex align-items-center justify-content-center gap-2" id="btn-submit-login">
                            <i class="ti ti-login fs-5"></i>
                            <span>Masuk ke Sistem</span>
                        </button>

                        <!-- Keamanan & Copyright -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <div class="security-badge mb-2">
                                <i class="ti ti-shield-lock text-success fs-5"></i>
                                <span>Sesi login aman &amp; terenkripsi</span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.75rem;">
                                &copy; <?= date('Y'); ?> <?= esc(APP_NAME); ?> Retail Management System.
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url(); ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/app.init.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/theme.js"></script>

    <script>
        (function() {
            'use strict';

            // Form validation
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        var btn = document.getElementById('btn-submit-login');
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Show / Hide Password Toggle
            var toggleBtn = document.getElementById('btn-toggle-pwd');
            var pwdInput = document.getElementById('password');

            if (toggleBtn && pwdInput) {
                toggleBtn.addEventListener('click', function() {
                    var isPassword = pwdInput.getAttribute('type') === 'password';
                    pwdInput.setAttribute('type', isPassword ? 'text' : 'password');
                    var icon = toggleBtn.querySelector('i');
                    if (icon) {
                        if (isPassword) {
                            icon.classList.remove('ti-eye');
                            icon.classList.add('ti-eye-off');
                        } else {
                            icon.classList.remove('ti-eye-off');
                            icon.classList.add('ti-eye');
                        }
                    }
                });
            }
        })();
    </script>
</body>

</html>
