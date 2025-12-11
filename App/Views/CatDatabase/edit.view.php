<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\Cats $cat */
/** @var array|null $location */

use App\Configuration;
?>

<div class="container mt-4">
    <h2>Upraviť mačku</h2>
    <form id="editCatForm" method="post" enctype="multipart/form-data" action="<?= $link->url('catdatabase.save') ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($cat->getId()) ?>">
        <div class="mb-3">
            <label for="cat-name" class="form-label">Name *</label>
            <input id="cat-name" name="meno" type="text" class="form-control" required value="<?= htmlspecialchars($cat->getMeno() ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="cat-text" class="form-label">Text *</label>
            <textarea id="cat-text" name="text" class="form-control" rows="4" required><?= htmlspecialchars($cat->getText() ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="cat-status" class="form-label">Status</label>
            <input id="cat-status" name="status" type="text" class="form-control" value="<?= htmlspecialchars($cat->getStatus() ?? '') ?>">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="cat-kastrovana" name="kastrovana" value="1" <?= $cat->isKastrovana() ? 'checked' : '' ?> >
            <label class="form-check-label" for="cat-kastrovana">Castrated</label>
        </div>
        <div class="mb-3">
            <label for="cat-photo" class="form-label">Photo (leave empty to keep existing)</label>
            <input id="cat-photo" name="fotka" type="file" accept="image/*" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Current location</label>
            <?php if ($location): ?>
                <div class="mb-2 small text-muted">City: <?= htmlspecialchars($location['city']) ?> — Lat: <?= htmlspecialchars($location['latitude']) ?>, Lon: <?= htmlspecialchars($location['longitude']) ?></div>
            <?php else: ?>
                <div class="mb-2 small text-muted">No location recorded.</div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="<?= $link->url('catdatabase.index') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

