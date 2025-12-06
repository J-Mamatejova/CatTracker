<?php

/** @var string $contentHTML */
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <title><?= App\Configuration::APP_NAME ?></title>
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $link->asset('favicons/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $link->asset('favicons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $link->asset('favicons/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= $link->asset('favicons/site.webmanifest') ?>">
    <link rel="shortcut icon" href="<?= $link->asset('favicons/favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?= $link->asset('css/styl.css') ?>">
    <script src="<?= $link->asset('js/script.js') ?>"></script>
</head>
<body>
<nav class="navbar navbar-expand-sm navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= $link->url('home.index') ?>">
            <img src="<?= $link->asset('images/cat_logo.png') ?>" title="<?= App\Configuration::APP_NAME ?>" alt="CatTracker Logo">
            <span class="site-name ms-2"><?= App\Configuration::APP_NAME ?></span>
        </a>

        <!-- Right-side controls: Cat Database, Map View, and auth actions -->
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item me-2">
                <a class="nav-link btn btn-outline-dark d-flex align-items-center" href="<?= $link->url('catdatabase.index') ?>">
                    <!-- simple cat icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M2.165 4.5c-.5.2-.99.7-1.01 1.3C1.08 8.7 3.6 11 8 11s6.92-2.3 6.845-5.2c-.02-.6-.51-1.1-1.01-1.3-.08-.03-.16.04-.19.12C12.9 6 11.8 6.5 10 6.5s-2.9-.5-3.8-1.08c-.11-.07-.26-.07-.37 0C5.1 6 4 6.5 2.205 5.62c-.03-.08-.11-.15-.04-.12z"/>
                    </svg>
                    Cat Database
                </a>
            </li>
            <li class="nav-item me-2">
                <a class="nav-link btn btn-outline-dark d-flex align-items-center" href="<?= $link->url('map.index') ?>">
                    <!-- simple map marker icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 0a5 5 0 0 0-5 5c0 4.667 5 11 5 11s5-6.333 5-11a5 5 0 0 0-5-5zm0 8a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                    </svg>
                    Map View
                </a>
            </li>

            <?php if ($user && $user->isLoggedIn()) { ?>
                <li class="nav-item me-2">
                    <span class="navbar-text">Logged in: <strong><?= htmlspecialchars($user->getName()) ?></strong></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $link->url('auth.logout') ?>">Log out</a>
                </li>
            <?php } else { ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-dark" href="<?= $link->url('profile.index') ?>">Profile</a>
                </li>
            <?php } ?>
        </ul>
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
            <form method="post" action="<?= $link->url('auth.login') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Log in</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="modal-username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="modal-password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit" class="btn btn-primary">Log in</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sign up modal (Bootstrap) -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= $link->url('auth.signup') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="signupModalLabel">Sign up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="signup-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="signup-username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="signup-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="signup-password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="signup-password-confirm" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" id="signup-password-confirm" name="password_confirm" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit" class="btn btn-primary">Sign up</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

</html>