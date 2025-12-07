<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Core\IAuthenticator $auth */
/** @var array $cats */
/** @var int $catCount */

use App\Configuration;
?>

<div class="container-fluid mt-4">
    <h2>Cat Database</h2>

    <!-- Debug panel -->
    <div class="mb-3">
        <div>Total cats: <?= isset($cats) ? count($cats) : 'N/A' ?></div>
    </div>

    <?php if (empty($cats)) { ?>
        <div class="alert alert-info">No cats found yet.</div>
    <?php } else { ?>
        <!-- Always 3 columns per row on all breakpoints -->
        <div class="row row-cols-3 g-4">
            <?php foreach ($cats as $cat):
                // Support both model objects and associative arrays
                if (is_array($cat)) {
                    $id = htmlspecialchars($cat['id']);
                    $name = htmlspecialchars($cat['meno'] ?? '');
                    $text = htmlspecialchars($cat['text'] ?? '');
                    $status = htmlspecialchars($cat['status'] ?? '');
                    $kastrovana = !empty($cat['kastrovana']);
                    $fotka = $cat['fotka'] ?? '';
                } else {
                    // object (model) access
                    $id = htmlspecialchars($cat->getId());
                    $name = htmlspecialchars($cat->getMeno() ?? '');
                    $text = htmlspecialchars($cat->getText() ?? '');
                    $status = htmlspecialchars($cat->getStatus() ?? '');
                    $kastrovana = method_exists($cat, 'isKastrovana') ? (bool)$cat->isKastrovana() : (!empty($cat->kastrovana));
                    $fotka = method_exists($cat, 'getFotka') ? $cat->getFotka() : (property_exists($cat, 'fotka') ? $cat->fotka : '');
                }

                // determine image source
                if (!empty($fotka)) {
                    $fotka = (string)$fotka;
                    if ($fotka[0] === '/') {
                        $imgSrc = $fotka; // absolute path stored in DB (e.g. /uploads/...)
                    } else {
                        $imgSrc = $link->asset(Configuration::UPLOAD_URL . ltrim($fotka, '/'));
                    }
                } else {
                    $imgSrc = $link->asset('images/cat-icon.png');
                }

                // short text
                $short = mb_strlen($text) > 100 ? mb_substr($text, 0, 100) . '...' : $text;
                ?>
                <div class="col">
                    <div class="card h-100">
                        <!-- Make images taller (300px) and cover the card width -->
                        <img src="<?= $imgSrc ?>" class="card-img-top" alt="<?= $name ?>" style="object-fit:cover; height:300px;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= $name ?></h5>
                            <p class="card-text"><?= $short ?></p>
                            <div class="mt-auto small text-muted">
                                <div>Status: <?= $status ?></div>
                                <div>Kastrovaná: <?= $kastrovana ? 'Áno' : 'Nie' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php } ?>
</div>
