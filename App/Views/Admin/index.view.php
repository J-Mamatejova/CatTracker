<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div>
                Vitajte, <strong><?= $user->getName() ?></strong>!<br><br>
                Táto časť aplikácie je prístupná iba po prihlásení.
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <h3>Admin panel</h3>
    <p>Admin nástroje idú sem.</p>
</div>
