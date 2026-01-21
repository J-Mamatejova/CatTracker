<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
?>

<div class="container mt-4">
    <div class="row">
        <div class="col">
            <h3 data-i18n="profile.heading">Profil</h3>
            <?php if (!isset($user) || !$user->isLoggedIn()): ?>
                <p data-i18n="profile.please_login">Prosím, prihláste sa alebo si vytvorte účet.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal" data-i18n="login.open">Prihlásiť sa</button>
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#signupModal" data-i18n="signup.open">Registrovať sa</button>
            <?php else: ?>
                <p data-i18n="profile.welcome">Vitaj,</p>
                <div class="card">
                    <div class="card-body">
                        <p><strong><?= htmlspecialchars($user->getName()) ?></strong></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
