<?php

session_start();

require_once __DIR__ . '/../../app/includes/config.php';
require_once __DIR__ . '/../../app/includes/functions.php';
require_once __DIR__ . '/../../app/includes/auth.php';

requireGuest();

?>

<!DOCTYPE html>
<html lang="pt-br" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-path="public/assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title><?= APP_NAME ?>| Login</title>
    <!--<link rel="icon" type="image/x-icon" href="public/assets/img/favicon/favicon.ico" />-->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="public/assets/vendor/fonts/boxicons.css" />

    <link rel="stylesheet" href="public/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="public/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="public/assets/css/demo.css" />

    <link rel="stylesheet" href="public/assets/vendor/css/pages/page-auth.css" />
    
    <style>
        .btn-google {
            background-color: #fff;
            border-color: #d9dee3;
            color: #566a7f;
        }
        .btn-google:hover {
            background-color: #f8f9fa;
            border-color: #d9dee3;
            color: #566a7f;
        }
        .btn-google img {
            width: 18px;
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card">
                    <div class="card-body">

                        <div class="app-brand justify-content-center">
               
               
                <a href="/" class="app-brand-link gap-2">
                  <?php if(!empty(APP_ICON)):?>
                      <span class="app-brand-logo demo">
                        <img src="<?= APP_ICON ?>" alt="Logo" style="width: 150px;">
                      </span>
                  <?php else:?>
                  <span class="app-brand-text demo text-body fw-bolder text-capitalize"><?= APP_NAME ?></span>
                  <?php endif;?>
                </a>
              </div>

                       <!-- <div class="app-brand justify-content-center">
                            <a href="index.html" class="app-brand-link gap-2">
                                <span class="app-brand-text demo text-body fw-bolder text-capitalize">SpyZap</span>
                            </a>
                        </div>-->
                        <h4 class="mb-2">Bem-vindo ao <?= APP_NAME ?>! 👋</h4>
                        <p class="mb-4">Por favor, faça login na sua conta.</p>

                        <form id="formAuthentication" class="mb-3" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="text" class="form-control" id="email" name="email" placeholder="seu@email.com" autofocus />
                            </div>
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Senha</label>
                                    <a href="/forgot">
                                        <small>Esqueceu a senha?</small>
                                    </a>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password" placeholder="············" aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember-me" />
                                    <label class="form-check-label" for="remember-me"> Lembrar-me </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">Entrar</button>
                            </div>
                        </form>

                        <div class="divider my-4">
                            <div class="divider-text">OU</div>
                        </div>

                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-google d-grid w-100 shadow-sm">
                                <span class="d-flex align-items-center justify-content-center">
                                    <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Google Logo">
                                    Entrar com Google
                                </span>
                            </button>
                        </div>

                        <p class="text-center mt-4">
                            <span>Novo na plataforma?</span>
                            <a href="/registrar">
                                <span>Criar uma conta</span>
                            </a>
                        </p>
                    </div>
                </div>
                </div>
        </div>
    </div>

    

     <!-- Alertas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= asset('assets/js/alerts.js') ?>"></script>
    
    <!-- Firebase -->
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>

    <!-- Fingerprint -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fingerprintjs2/2.1.4/fingerprint2.min.js"></script>

    <!-- Meu Public -->
    <script src="<?= asset('assets/js/firebase-config.js') ?>"></script>
    <script src="<?= asset('assets/js/fingerprint.js') ?>"></script>
    <script src="<?= asset('assets/js/firebase-auth.js') ?>"></script>
    <script src="<?= asset('assets/js/pages/login.js') ?>"></script>
   
</body>
</html>