<?php

/** @var string $contentHTML */
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= App\Configuration::APP_NAME ?></title>
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $link->asset('favicons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $link->asset('favicons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $link->asset('favicons/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= $link->asset('favicons/site.webmanifest') ?>">
    <link rel="shortcut icon" href="<?= $link->asset('favicons/favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= $link->asset('css/styl.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>
    <script src="<?= $link->asset('js/script.js') ?>"></script>
</head>
<body>
<nav class="navbar navbar-expand-sm navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= $link->url('home.index') ?>">
            <img src="<?= $link->asset('images/cat_logo.png') ?>" title="<?= App\Configuration::APP_NAME ?>" alt="CatTracker Logo">
            <span class="site-name ms-2"><?= App\Configuration::APP_NAME ?></span>
        </a>

        <!-- Language switcher (SK / EN) placed to the right of brand -->
        <div class="lang-switch d-flex align-items-center ms-3">
            <button type="button" class="btn btn-sm btn-outline-dark lang-btn me-1" data-lang="sk" aria-label="Slovensky">SK</button>
            <button type="button" class="btn btn-sm btn-outline-dark lang-btn" data-lang="en" aria-label="English">EN</button>
        </div>

        <!-- Toggler for small screens -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Right-side controls wrapped in Bootstrap collapse -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item me-2">
                <a class="nav-link btn btn-outline-dark d-flex align-items-center" href="<?= $link->url('catdatabase.index') ?>">
                    <img src="<?= $link->asset('images/cat-icon.png') ?>" alt="Cat" width="20" height="20" class="me-2">
                    <span data-i18n="nav.catdatabase">Cat Database</span>
                </a>
            </li>

            <!-- NEW: Posts / Feed button -->
            <li class="nav-item me-2">
                <a class="nav-link btn btn-outline-dark d-flex align-items-center" href="<?= $link->url('post.index') ?>">
                    <!-- simple post icon (paper) -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M14 4.5V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h7.5L14 4.5zm-1 0L10.5 1H3v13h10V4.5zM5 5h6v1H5V5zm0 2h6v1H5V7z"/>
                    </svg>
                    <span data-i18n="nav.posts">Posts</span>
                </a>
            </li>

            <li class="nav-item me-2">
                <a class="nav-link btn btn-outline-dark d-flex align-items-center" href="<?= $link->url('map.index') ?>">
                    <!-- simple map marker icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 0a5 5 0 0 0-5 5c0 4.667 5 11 5 11s5-6.333 5-11a5 5 0 0 0-5-5zm0 8a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                    </svg>
                    <span data-i18n="nav.map">Map View</span>
                </a>
            </li>

            <!-- Always show Profile button; profile UI controls login/register via modal -->
            <li class="nav-item">
                <a class="nav-link btn btn-outline-dark" href="<?= $link->url('profile.index') ?>"><span data-i18n="nav.profile">Profile</span></a>
            </li>
        </ul>
        </div>
    </div>
</nav>
<div class="container-fluid mt-3">
    <div class="web-content">
        <?= $contentHTML ?>
    </div>
</div>
</body>

<!-- Login modal (Bootstrap) -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= $link->url('user.login') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Log in</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loginModalAlert"></div>
                    <div class="mb-3">
                        <label for="modal-email" class="form-label" data-i18n="login.email">Email</label>
                        <input type="email" class="form-control" id="modal-email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal-password" class="form-label" data-i18n="login.password">Password</label>
                        <input type="password" class="form-control" id="modal-password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit" class="btn btn-primary" data-i18n="login.submit">Log in</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-i18n="common.close">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sign up modal (Bootstrap) -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= $link->url('user.register') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="signupModalLabel">Sign up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="signupModalAlert"></div>
                    <div class="mb-3">
                        <label for="signup-username" class="form-label" data-i18n="signup.username">Username</label>
                        <input type="text" class="form-control" id="signup-username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="signup-email" class="form-label" data-i18n="signup.email">Email</label>
                        <input type="email" class="form-control" id="signup-email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="signup-password" class="form-label" data-i18n="signup.password">Password</label>
                        <input type="password" class="form-control" id="signup-password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="signup-password-confirm" class="form-label" data-i18n="signup.password_confirm">Confirm password</label>
                        <input type="password" class="form-control" id="signup-password-confirm" name="password_confirm" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit" class="btn btn-primary" data-i18n="signup.submit">Sign up</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-i18n="common.close">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

</html>