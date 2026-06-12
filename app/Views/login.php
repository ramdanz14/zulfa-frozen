<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Cyan_Theme" data-layout="vertical">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/png" href="<?= base_url(APP_LOGO_PATH); ?>" />

    <!-- Core Css -->
    <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/styles.css" />

    <title><?= esc(APP_NAME); ?> | Login</title>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <img src="<?= base_url(APP_LOGO_PATH); ?>" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <div id="main-wrapper" class="auth-customizer-none">
        <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100">
            <div class="position-relative z-index-5">
                <div class="row">
                    <div class="col-xl-7 col-xxl-8">

                        <div class="d-none d-xl-flex align-items-center justify-content-center h-n80">
                            <video autoplay loop muted playsinline>
                                <source src="<?= base_url(); ?>/assets/Zulfa.mp4" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                    <div class="col-xl-5 col-xxl-4">
                        <div class="authentication-login min-vh-100 bg-body row justify-content-center align-items-center p-4">
                            <div class="auth-max-width col-sm-8 col-md-6 col-xl-7 px-4">
                                <div class="row d-flex align-items-center ">
                                    <div class="col-3 mb-2 mb-sm-0">
                                        <img src="<?= base_url(APP_LOGO_PATH); ?>" class="dark-logo" style="height: 63px;" alt="<?= esc(APP_NAME); ?>" />
                                    </div>
                                    <div class="col-9">
                                        <h2 class="mb-1 fs-7 fw-bolder"><?= esc(strtoupper(APP_NAME)); ?></h2>
                                    </div>
                                    <?= session()->getFlashdata('warning'); ?>
                                </div>
                                <div class="position-relative text-center my-4">
                                    <p class="mb-0 fs-4 px-3 d-inline-block bg-body text-dark z-index-5 position-relative">Silahkan Login</p>
                                    <span class="border-top w-100 position-absolute top-50 start-50 translate-middle"></span>
                                </div>
                                <form class="needs-validation" novalidate method="post" action="<?php $currentURL = current_url(true);
                                                                                                $fullURL = (string)$currentURL;
                                                                                                echo $fullURL; ?> ">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
                                        <div class="invalid-feedback">
                                            Username Harus diisi.
                                        </div>
                                    </div>
                                    <div class="mb-4">

                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                                        <div class="invalid-feedback">
                                            Password Harus diisi.
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2">Login</button>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            (function() {
                'use strict'

                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.querySelectorAll('.needs-validation')

                // Loop over them and prevent submission
                Array.prototype.slice.call(forms)
                    .forEach(function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault()
                                event.stopPropagation()
                            }

                            form.classList.add('was-validated')
                        }, false)
                    })
            })()
        </script>
    </div>
    <div class="dark-transparent sidebartoggler"></div>
    <!-- Import Js Files -->
    <script src="<?= base_url(); ?>/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url(); ?>/assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/app.init.js"></script>
    <script src="<?= base_url(); ?>/assets/js/theme/theme.js"></script>
    <!-- <script src="<?= base_url(); ?>/assets/js/theme/app.min.js"></script> -->

    <!-- solar icons -->
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>
