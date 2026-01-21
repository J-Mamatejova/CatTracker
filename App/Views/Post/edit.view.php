<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */

/** @var \Framework\Support\LinkGenerator $link */
/** @var array $post */
/** @var array $cats */
?>

<div class="container mt-4">
    <h3 data-i18n="post.create">Upraviť príspevok</h3>
    <form method="post" action="<?= $link->url('post.update') ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($post['id']) ?>">
        <div class="mb-3">
            <label class="form-label" data-i18n="post.label.title">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" data-i18n="post.label.content">Content</label>
            <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($post['content']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label" data-i18n="post.label.cat">Related cat</label>
            <select name="cat_id" class="form-select" required>
                <?php foreach ($cats as $c): ?>
                    <option value="<?= htmlspecialchars($c['id']) ?>" <?= ($c['id'] == $post['cat_id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['meno'] ?? $c['cat_name'] ?? 'Cat') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit" data-i18n="post.create_btn">Uložiť</button>
        <a href="<?= $link->url('post.index') ?>" class="btn btn-secondary" data-i18n="common.close">Zrušiť</a>
    </form>
</div>
