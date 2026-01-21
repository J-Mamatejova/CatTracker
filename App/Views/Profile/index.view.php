<?php
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
/** @var int $posts_count */
/** @var int $cats_count */

// Read query params for change password result
$changeSuccess = isset($_GET['changePwdSuccess']);
$changeError = isset($_GET['changePwdError']);
$changeMsg = '';
if ($changeError && isset($_GET['changePwdMessage'])) {
    // message may be urlencoded by controller
    $changeMsg = urldecode($_GET['changePwdMessage']);
}
?>

<div class="container mt-4">
    <h2 data-i18n="profile.heading">Profil</h2>

    <?php if ($user && $user->isLoggedIn()) { ?>
        <div class="row g-4">
            <!-- Left column: user info + stats -->
            <div class="col-12 col-md-5">
                <div class="card profile-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3" data-i18n="profile.welcome">Vitaj,</h5>
                        <p class="h6 mb-3"><strong><?= htmlspecialchars($user->getName()) ?></strong></p>

                        <div class="stats-list mt-3">
                            <div class="stat-item mb-2">
                                <div class="stat-label" data-i18n="profile.posts">Počet príspevkov:</div>
                                <div class="stat-value"><strong><?= $posts_count ?? 0 ?></strong></div>
                            </div>
                            <div class="stat-item mb-2">
                                <div class="stat-label" data-i18n="profile.cats">Počet pridaných mačiek:</div>
                                <div class="stat-value"><strong><?= $cats_count ?? 0 ?></strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column: actions (change password, logout) -->
            <div class="col-12 col-md-7">
                <div class="card profile-card">
                    <div class="card-body">
                        <h5 class="card-title" data-i18n="profile.change_password">Zmeniť heslo</h5>

                        <?php if ($changeSuccess): ?>
                            <div class="mb-3">
                                <small class="text-success" data-i18n="profile.change_password_success">Heslo bolo úspešne zmenené</small>
                            </div>
                        <?php elseif ($changeError): ?>
                            <div class="mb-3">
                                <small class="text-danger"><?= htmlspecialchars($changeMsg) ?></small>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?= $link->url('profile.changePassword') ?>" class="mb-3">
                            <div class="mb-3">
                                <label class="form-label" for="old_password" data-i18n="profile.old_password">Staré heslo</label>
                                <input id="old_password" name="old_password" type="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="new_password" data-i18n="profile.new_password">Nové heslo</label>
                                <input id="new_password" name="new_password" type="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="new_password_confirm" data-i18n="profile.new_password_confirm">Potvrď nové heslo</label>
                                <input id="new_password_confirm" name="new_password_confirm" type="password" class="form-control" required>
                            </div>
                            <div class="d-flex align-items-center">
                                <button class="btn btn-primary" type="submit" data-i18n="profile.change_password_submit">Zmeniť heslo</button>
                                <a class="btn btn-outline-dark ms-3" href="<?= $link->url('auth.logout') ?>" data-i18n="profile.logout">Odhlásiť sa</a>
                            </div>
                        </form>

                        <!-- Optionally other actions could go here in future -->
                    </div>
                </div>
            </div>
        </div>

    <?php } else { ?>
        <div class="card profile-card">
            <div class="card-body">
                <p data-i18n="profile.please_login">Prosím, prihláste sa alebo si vytvorte účet.</p>
                <p>
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal" data-i18n="login.open">Prihlásiť sa</button>
                    <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#signupModal" data-i18n="signup.open">Zaregistrovať sa</button>
                </p>
            </div>
        </div>
    <?php } ?>
</div>
