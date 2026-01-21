<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */

/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var array $cats */
/** @var int $catCount */

use App\Configuration;
?>

<div class="container-fluid mt-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0" data-i18n="catdb.title">Cat Database</h2>
        <div>
            <button id="addNewCatBtn" data-logged="<?= ($user?->isLoggedIn() ? '1' : '0') ?>" class="btn btn-success" data-i18n="catdb.add">Add new cat</button>
            <small id="loginRequiredMsg" class="text-danger ms-3" style="display:none;" data-i18n="catdb.login_required">you must log in to add cats</small>
        </div>
    </div>

    <!-- Panel pre debug/počet mačiek -->
    <div class="mb-3">
        <div><span data-i18n="catdb.total_label">Total cats:</span> <span class="cats-count"><?= isset($cats) ? count($cats) : 'N/A' ?></span></div>
    </div>

    <?php if (empty($cats)) { ?>
        <div class="alert alert-info">No cats found yet.</div>
    <?php } else { ?>
        <!-- Vždy 3 stĺpce na riadok -->
        <div class="row row-cols-3 g-4">
            <?php foreach ($cats as $cat):
                // Podpora buď model objektu alebo asociatívneho poľa
                $isOwner = false;
                if (is_array($cat)) {
                    $id = htmlspecialchars($cat['id']);
                    $name = htmlspecialchars($cat['meno'] ?? '');
                    $text = htmlspecialchars($cat['text'] ?? '');
                    $status = htmlspecialchars($cat['status'] ?? '');
                    $kastrovana = !empty($cat['kastrovana']);
                    $fotka = $cat['fotka'] ?? '';
                    $ownerId = isset($cat['user_id']) ? (int)$cat['user_id'] : null;
                } else {
                    // objekt (model) prístup
                    $id = htmlspecialchars($cat->getId());
                    $name = htmlspecialchars($cat->getMeno() ?? '');
                    $text = htmlspecialchars($cat->getText() ?? '');
                    $status = htmlspecialchars($cat->getStatus() ?? '');
                    $kastrovana = method_exists($cat, 'isKastrovana') ? (bool)$cat->isKastrovana() : (!empty($cat->kastrovana));
                    $fotka = method_exists($cat, 'getFotka') ? $cat->getFotka() : (property_exists($cat, 'fotka') ? $cat->fotka : '');
                    $ownerId = method_exists($cat, 'getUserId') ? $cat->getUserId() : (property_exists($cat, 'user_id') ? $cat->user_id : null);
                }

                // kontrola vlastníctva (prihlásený = vlastník)
                if (isset($user) && $user?->isLoggedIn()) {
                    $isOwner = ($ownerId !== null && $ownerId == $user->getId());
                }

                // určiť zdroj obrázku
                if (!empty($fotka)) {
                    $fotka = (string)$fotka;
                    if ($fotka[0] === '/') {
                        $imgSrc = $fotka; // absolútna cesta uložená v DB
                    } else {
                        $imgSrc = $link->asset(Configuration::UPLOAD_URL . ltrim($fotka, '/'));
                    }
                } else {
                    $imgSrc = $link->asset('images/cat-icon.png');
                }

                // skrátený text
                $short = mb_strlen($text) > 100 ? mb_substr($text, 0, 100) . '...' : $text;
                ?>
                <div class="col">
                    <div class="card h-100" id="cat-<?= $id ?>">
                        <!-- Obrázky vyššie (300px) -->
                        <img src="<?= $imgSrc ?>" class="card-img-top" alt="<?= $name ?>" style="object-fit:cover; height:300px;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= $name ?></h5>
                            <p class="card-text"><?= $short ?></p>
                            <div class="mt-auto small text-muted">
                                <div>Status: <?= $status ?></div>
                                <div>Kastrovaná: <?= $kastrovana ? 'Áno' : 'Nie' ?></div>
                            </div>

                            <?php if ($isOwner): ?>
                                <div class="mt-3 d-flex gap-2 justify-content-end">
                                    <a href="<?= $link->url('catdatabase.edit', ['id' => $id]) ?>" class="btn btn-primary btn-sm" data-i18n="catdb.edit">Upraviť</a>
                                    <form method="post" action="<?= $link->url('catdatabase.delete') ?>" class="confirm-delete-form" data-confirm-key="confirm.delete.cat" style="display:inline-block;">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-i18n="catdb.delete">Zmazať</button>
                                    </form>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php } ?>
</div>

<!-- Add Cat Modal (markup only) -->
<div class="modal fade" id="addCatModal" tabindex="-1" aria-labelledby="addCatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addCatForm" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCatModalLabel">Add new cat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="addCatAlert"></div>
                    <div class="mb-3">
                        <label for="cat-name" class="form-label">Name *</label>
                        <input id="cat-name" name="meno" type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="cat-text" class="form-label">Text *</label>
                        <textarea id="cat-text" name="text" class="form-control" rows="3" required></textarea>
                    </div>
                    <!-- Mini interaktívna mapa pre výber lokality (povinné) -->
                    <div class="mb-3">
                        <label class="form-label">Location (click map to choose) *</label>
                        <div id="addCatMiniMap" style="height:250px; border:1px solid #ddd; border-radius:6px;"></div>
                        <div class="form-text">Click the map to place a marker for the cat location.</div>
                    </div>
                    <div class="mb-3">
                        <label for="cat-status" class="form-label">Status</label>
                        <input id="cat-status" name="status" type="text" class="form-control">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="cat-kastrovana" name="kastrovana" value="1">
                        <label class="form-check-label" for="cat-kastrovana">Castrated</label>
                    </div>
                    <div class="mb-3">
                        <label for="cat-photo" class="form-label">Photo</label>
                        <input id="cat-photo" name="fotka" type="file" accept="image/*" class="form-control">
                    </div>
                    <!-- Skryté polia vyplnené mapou -->
                    <input type="hidden" id="cat-latitude" name="latitude" value="">
                    <input type="hidden" id="cat-longitude" name="longitude" value="">
                    <input type="hidden" id="cat-city" name="city" value="">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet assets for the mini map -->
<link rel="stylesheet" href="<?= $link->asset('css/leaflet.css') ?>" />
<script src="<?= $link->asset('js/leaflet.js') ?>"></script>
