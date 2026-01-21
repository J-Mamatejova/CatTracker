<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $post */
/** @var array $cats */
?>
<div class="container mt-4">
    <h2>Edit post</h2>
    <form method="post" action="<?= $link->url('post.update') ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($post['id']) ?>">
        <label for="post-title" class="visually-hidden">Title</label>
        <div class="mb-2">
            <input id="post-title" type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
        </div>
        <label for="post-content" class="visually-hidden">Content</label>
        <div class="mb-2">
            <textarea id="post-content" name="content" class="form-control" rows="4" required><?= htmlspecialchars($post['content']) ?></textarea>
        </div>
        <label for="post-cat" class="visually-hidden">Related cat</label>
        <div class="mb-2">
            <select id="post-cat" name="cat_id" class="form-select" required>
                <?php foreach ($cats as $c): ?>
                    <option value="<?= htmlspecialchars($c['id']) ?>" <?= ($c['id'] == $post['cat_id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['meno'] ?? $c['cat_name'] ?? 'Cat') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-secondary" href="<?= $link->url('post.index') ?>">Cancel</a>
        </div>
    </form>
</div>
