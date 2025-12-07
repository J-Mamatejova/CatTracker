<?php
/** @var LinkGenerator $link */
/** @var array|null $user */
/** @var int $posts_count */

use Framework\Support\LinkGenerator;

?>
<div class="container mt-4">
    <?php if (empty($user)): ?>
        <h2>Not logged in</h2>
        <p>
            <a class="btn btn-primary" href="<?= $link->url('user.login') ?>">Login</a>
            <a class="btn btn-secondary" href="<?= $link->url('user.register') ?>">Register</a>
        </p>
    <?php else: ?>
        <h2>Profile</h2>
        <table class="table">
            <tr><th>Username</th><td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>Joined</th><td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>Posts</th><td><?= (int)$posts_count ?></td></tr>
        </table>
        <p>
            <a class="btn btn-danger" href="<?= $link->url('user.logout') ?>">Logout</a>
        </p>
    <?php endif; ?>
</div>
