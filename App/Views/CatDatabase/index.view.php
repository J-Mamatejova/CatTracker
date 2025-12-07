<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Auth\AppUser $user */
/** @var array $cats */
/** @var int $catCount */

use App\Configuration;
?>

<div class="container-fluid mt-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0">Cat Database</h2>
        <div>
            <button id="addNewCatBtn" class="btn btn-success">Add new cat</button>
            <small id="loginRequiredMsg" class="text-danger ms-3" style="display:none;">you must log in to add cats</small>
        </div>
    </div>

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

<!-- Add Cat Modal -->
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
                    <!-- Mini interactive map for picking a location (required) -->
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
                    <!-- Hidden fields populated by map click -->
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

<script>
    (function(){
        const addBtn = document.getElementById('addNewCatBtn');
        const loginMsg = document.getElementById('loginRequiredMsg');
        const addCatModalEl = document.getElementById('addCatModal');
        const addCatForm = document.getElementById('addCatForm');
        const addCatAlert = document.getElementById('addCatAlert');
        // Bootstrap modal
        const addCatModal = new bootstrap.Modal(addCatModalEl);

        // whether user is logged in (injected by server)
        const isLogged = <?= ($user?->isLoggedIn() ? 'true' : 'false') ?>;

        addBtn.addEventListener('click', function(){
            if (!isLogged) {
                // show red message briefly
                loginMsg.style.display = 'inline';
                setTimeout(() => { loginMsg.style.display = 'none'; }, 3000);
                return;
            }
            // open modal
            addCatAlert.innerHTML = '';
            addCatModal.show();
        });

        // Mini-map selection
        let miniMap = null;
        let miniMarker = null;

        addCatModalEl.addEventListener('shown.bs.modal', function () {
            // initialize map once
            if (miniMap === null) {
                try {
                    miniMap = L.map('addCatMiniMap').setView([48.666667, 19.5], 7);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(miniMap);

                    miniMap.on('click', function(e) {
                        const lat = e.latlng.lat.toFixed(6);
                        const lon = e.latlng.lng.toFixed(6);
                        // place or move marker
                        if (miniMarker) {
                            miniMarker.setLatLng(e.latlng);
                        } else {
                            miniMarker = L.marker(e.latlng).addTo(miniMap);
                        }
                        // populate hidden inputs
                        document.getElementById('cat-latitude').value = lat;
                        document.getElementById('cat-longitude').value = lon;
                        // do not set city (no geocoding requested)
                    });
                } catch (err) {
                    console.error('Failed to initialize mini map', err);
                }
            } else {
                // resize if reopened
                setTimeout(() => { miniMap.invalidateSize(); }, 200);
            }
        });

        addCatForm.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            addCatAlert.innerHTML = '';

            // client-side validation
            const name = document.getElementById('cat-name').value.trim();
            const text = document.getElementById('cat-text').value.trim();
            const lat = document.getElementById('cat-latitude').value.trim();
            const lon = document.getElementById('cat-longitude').value.trim();
            if (!name || !text) {
                addCatAlert.innerHTML = '<div class="alert alert-danger">Name and text are required.</div>';
                return;
            }
            if (!lat || !lon) {
                addCatAlert.innerHTML = '<div class="alert alert-danger">Please select a location on the mini map.</div>';
                return;
            }

            const formData = new FormData(addCatForm);

            try {
                const resp = await fetch('?c=catdatabase&a=save', {
                    method: 'POST',
                    body: formData,
                });

                if (resp.ok) {
                    // success - close modal and reload page to show new cat
                    addCatModal.hide();
                    location.reload();
                } else {
                    const txt = await resp.text();
                    addCatAlert.innerHTML = '<div class="alert alert-danger">Save failed: ' + (txt || resp.statusText) + '</div>';
                }
            } catch (err) {
                addCatAlert.innerHTML = '<div class="alert alert-danger">Save failed: ' + err.message + '</div>';
            }
        });
    })();
</script>
