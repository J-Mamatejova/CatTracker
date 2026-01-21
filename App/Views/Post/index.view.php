<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */

/** @var \Framework\Support\LinkGenerator $link */
/** @var array $posts */
/** @var array $cats */
?>
<div class="container mt-4">
    <div class="row g-4 posts-layout">
        <!-- Ľavý stĺpec: tlačidlo pre vytvorenie príspevku (otvára modal) -->
        <div class="col-12 col-md-4">
            <div class="card create-card">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title" data-i18n="post.create">Create new post</h5>
                    <p class="card-text text-muted"> <span data-i18n="post.create_hint">Share a short update related to a cat.</span></p>

                    <div class="mt-auto">
                        <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#createPostModal" data-i18n="post.new_button">New post</button>
                    </div>
                </div>
            </div>

            <!-- Kompaktné tipy -->
            <div class="card mt-3 small-tip-card">
                <div class="card-body">
                    <h6 data-i18n="post.tips_title">Tips</h6>
                    <ul class="mb-0">
                        <li data-i18n="post.tip1">Keep posts brief and helpful.</li>
                        <li data-i18n="post.tip2">Link posts to a cat from the database.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pravý stĺpec: feed príspevkov -->
        <div class="col-12 col-md-8">
            <div class="card feed-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0" data-i18n="post.title">Feed / Posts</h5>
                        <small class="text-muted"><span data-i18n="post.count_label">Total:</span> <span class="post-count"><?= count($posts) ?></span></small>
                    </div>

                    <div id="postsFeed">
                        <?php if (empty($posts)): ?>
                            <div class="empty-state text-center py-5">
                                <div class="mb-3">
                                    <img src="<?= $link->asset('images/cat-icon.png') ?>" alt="empty" style="width:96px; opacity:0.85;">
                                </div>
                                <h6 data-i18n="post.empty_title">No posts yet.</h6>
                                <p class="text-muted" data-i18n="post.empty_text">Be the first to create a post about a cat.</p>
                                <!-- tvorba príspevku je dostupná cez tlačidlo vľavo "New post" -->
                            </div>
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
                                            <a href="<?= $link->url('post.edit', ['id' => $p['id']]) ?>" class="btn btn-sm btn-secondary" data-i18n="post.edit">Edit</a>
                                            <button class="btn btn-sm btn-danger btn-delete-post" data-id="<?= $p['id'] ?>" data-i18n="post.delete">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Post Modal (pop-out card) -->
<div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="createPostForm" method="post" action="?c=post&a=save">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPostModalLabel" data-i18n="post.create">Create new post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="createPostAlert"></div>

                    <div class="mb-3">
                        <label for="post-title" class="form-label" data-i18n="post.label.title">Title</label>
                        <input type="text" name="title" id="post-title" class="form-control" placeholder="" data-i18n-placeholder="post.placeholder.title" required>
                    </div>
                    <div class="mb-3">
                        <label for="post-content" class="form-label" data-i18n="post.label.content">Content</label>
                        <textarea name="content" id="post-content" class="form-control" placeholder="" data-i18n-placeholder="post.placeholder.content" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="post-cat" class="form-label" data-i18n="post.label.cat">Related cat</label>
                        <select name="cat_id" id="post-cat" class="form-select" required>
                            <option value="" data-i18n="post.select.cat">Select related cat</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['meno'] ?? $c['cat_name'] ?? 'Cat') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" data-i18n="post.create_btn">Create</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-i18n="common.close">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
