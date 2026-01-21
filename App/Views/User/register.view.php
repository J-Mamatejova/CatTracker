<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */
?>
<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $formErrors */
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-6">
            <h3 data-i18n="signup.title">Registrovať sa</h3>
            <form method="post" action="<?= $link->url('user.register') ?>">
                <div class="mb-3">
                    <label for="signup-username" class="form-label" data-i18n="signup.username">Používateľské meno</label>
                    <input type="text" class="form-control" id="signup-username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="signup-email" class="form-label" data-i18n="signup.email">Email</label>
                    <input type="email" class="form-control" id="signup-email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="signup-password" class="form-label" data-i18n="signup.password">Heslo</label>
                    <input type="password" class="form-control" id="signup-password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="signup-password-confirm" class="form-label" data-i18n="signup.password_confirm">Potvrď heslo</label>
                    <input type="password" class="form-control" id="signup-password-confirm" name="password_confirm" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary" data-i18n="signup.submit">Registrovať</button>
            </form>
        </div>
    </div>
</div>
