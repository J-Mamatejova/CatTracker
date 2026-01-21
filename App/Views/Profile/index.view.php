<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 * Upravené tak, aby podporoval $user ako objekt alebo pole.
 */

/** @var \Framework\Support\LinkGenerator $link */
/** @var mixed $user */
/** @var int $posts_count */
/** @var int $cats_count */

// read optional messages from query params (redirected by controller)
$pwdSuccess = isset($_GET['changePwdSuccess']) && $_GET['changePwdSuccess'] == 1;
$pwdError = isset($_GET['changePwdError']) && $_GET['changePwdError'] == 1;
$pwdMessage = isset($_GET['changePwdMessage']) ? urldecode($_GET['changePwdMessage']) : '';
?>

<div class="container mt-4">
    <div class="row g-4">
        <!-- LEFT: Welcome + Statistics -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h4 class="card-title" data-i18n="profile.heading">Profil</h4>

                    <?php
                    // Prepare display name/email supporting object or array
                    $displayName = '';
                    $displayEmail = '';
                    if (is_object($user)) {
                        if (method_exists($user, 'getName')) $displayName = $user->getName();
                        elseif (property_exists($user, 'name')) $displayName = $user->name;
                        if (method_exists($user, 'getEmail')) $displayEmail = $user->getEmail();
                        elseif (property_exists($user, 'email')) $displayEmail = $user->email;
                    } elseif (is_array($user)) {
                        $displayName = $user['username'] ?? $user['name'] ?? '';
                        $displayEmail = $user['email'] ?? '';
                    }
                    ?>

                    <?php if (empty($displayName) && empty($displayEmail)): ?>
                        <p data-i18n="profile.please_login">Prosím, prihláste sa alebo si vytvorte účet.</p>
                        <div class="mt-auto">
                            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal" data-i18n="login.open">Prihlásiť sa</button>
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#signupModal" data-i18n="signup.open">Registrovať sa</button>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <p class="h5 mb-1"><?= htmlspecialchars($displayName) ?></p>
                            <?php if ($displayEmail !== ''): ?><p class="text-muted mb-0"><?= htmlspecialchars($displayEmail) ?></p><?php endif; ?>
                        </div>

                        <hr>

                        <h6 data-i18n="profile.stats">Štatistiky</h6>
                        <div class="mt-2">
                            <div class="mb-1"><strong data-i18n="profile.posts">Počet príspevkov:</strong> <span class="post-count"><?= intval($posts_count ?? 0) ?></span></div>
                            <div><strong data-i18n="profile.cats">Počet pridaných mačiek:</strong> <span class="cats-count"><?= intval($cats_count ?? 0) ?></span></div>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Change password + Logout -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title" data-i18n="profile.change_password">Zmeniť heslo</h5>

                    <?php if ($pwdSuccess): ?>
                        <div class="text-success mb-2" data-i18n="profile.change_password_success">Heslo bolo úspešne zmenené</div>
                    <?php elseif ($pwdError): ?>
                        <div class="text-danger mb-2"><?= htmlspecialchars($pwdMessage) ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= $link->url('profile.changePassword') ?>" class="mb-3">
                        <div class="mb-3">
                            <label for="old_password" class="form-label" data-i18n="profile.old_password">Staré heslo</label>
                            <input id="old_password" type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label" data-i18n="profile.new_password">Nové heslo</label>
                            <input id="new_password" type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirm" class="form-label" data-i18n="profile.new_password_confirm">Potvrď nové heslo</label>
                            <input id="new_password_confirm" type="password" name="new_password_confirm" class="form-control" required>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" data-i18n="profile.change_password_submit">Zmeniť heslo</button>
                        </div>
                    </form>

                    <!-- Logout button as a separate form to avoid nested forms -->
                    <form method="post" action="<?= $link->url('user.logout') ?>" class="mt-3">
                        <button type="submit" class="btn btn-outline-secondary" data-i18n="profile.logout">Odhlásiť sa</button>
                    </form>

                    <div class="mt-auto text-muted small">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</div>
