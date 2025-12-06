<?php
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container mt-4">
    <h2>Profil</h2>

    <?php if ($user && $user->isLoggedIn()) { ?>
        <p>Vitaj, <strong><?= htmlspecialchars($user->getName()) ?></strong>!</p>
        <p><a class="btn btn-outline-dark" href="<?= $link->url('auth.logout') ?>">Log out</a></p>
    <?php } else { ?>
        <p>Prosím, prihláste sa alebo si vytvorte účet.</p>
        <p>
            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Log in</button>
            <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#signupModal">Sign up</button>
        </p>

        <!-- login and signup modals are defined in the root layout and will be opened by these buttons -->

    <?php } ?>
</div>

<script>
    (function(){
        // Parse URL params
        function getParam(name) {
            const params = new URLSearchParams(window.location.search);
            return params.get(name);
        }

        const loginError = getParam('loginError');
        const loginMessage = getParam('loginMessage');
        const signupError = getParam('signupError');
        const signupMessage = getParam('signupMessage');

        // Helper to inject alert into modal body
        function showModalWithMessage(modalId, message, isError = true) {
            const modalEl = document.getElementById(modalId);
            if (!modalEl) return;
            const body = modalEl.querySelector('.modal-body');
            if (body) {
                const alert = document.createElement('div');
                alert.className = isError ? 'alert alert-danger' : 'alert alert-success';
                alert.textContent = message;
                body.insertBefore(alert, body.firstChild);
            }
            // Use Bootstrap's modal show API
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        if (loginError && loginMessage) {
            showModalWithMessage('loginModal', decodeURIComponent(loginMessage));
        }
        if (signupError && signupMessage) {
            showModalWithMessage('signupModal', decodeURIComponent(signupMessage));
        }
    })();
</script>
