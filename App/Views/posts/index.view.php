<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $posts */
/** @var array $cats */
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Feed / Posts</h2>
        <a href="<?= $link->url('post.index') ?>" class="btn btn-secondary">Refresh</a>
    </div>

    <div id="createPostSection" class="card mb-4">
        <div class="card-body">
            <h5>Create new post</h5>
            <div id="createPostAlert"></div>
            <form id="createPostForm" method="post" action="?c=post&a=save">
                <!-- accessible labels (visually hidden) -->
                <label for="post-title" class="visually-hidden">Title</label>
                <label for="post-content" class="visually-hidden">Content</label>
                <label for="post-cat" class="visually-hidden">Related cat</label>

                <div class="mb-2">
                    <input type="text" name="title" id="post-title" class="form-control" placeholder="Title" required>
                </div>
                <div class="mb-2">
                    <textarea name="content" id="post-content" class="form-control" placeholder="Content" rows="3" required></textarea>
                </div>
                <div class="mb-2">
                    <select name="cat_id" id="post-cat" class="form-select" required>
                        <option value="">Select related cat</option>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['meno'] ?? $c['cat_name'] ?? 'Cat') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div id="postsFeed">
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">No posts yet.</div>
        <?php else: ?>
            <?php foreach ($posts as $p): ?>
                <div class="card mb-3 post-item" data-id="<?= $p['id'] ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($p['title']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">by <?= htmlspecialchars($p['author'] ?? 'user') ?>
                            <?php if (!empty($p['cat_name'])): ?>
                                — cat: <?= htmlspecialchars($p['cat_name']) ?>
                            <?php endif; ?>
                            <small class="text-muted float-end"><?= htmlspecialchars($p['created_at'] ?? '') ?></small>
                        </h6>
                        <p class="card-text"><?= nl2br(htmlspecialchars($p['content'])) ?></p>
                        <div>
                            <a href="<?= $link->url('post.edit', ['id' => $p['id']]) ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <button class="btn btn-sm btn-danger btn-delete-post" data-id="<?= $p['id'] ?>">Delete</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
