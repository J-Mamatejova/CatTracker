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

<!-- Inline scripts for profile modals moved to public/js/script.js -->
