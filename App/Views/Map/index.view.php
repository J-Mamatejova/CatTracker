<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */
?>

<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $locations */
?>

<div class="container mt-4">
    <h3 data-i18n="map.title">Mapa</h3>
    <div id="map" style="height:600px;"></div>

    <!-- vložiť JSON data pre JS -->
    <script id="map-data" type="application/json"><?= json_encode($locations ?? []) ?></script>
</div>

<!-- Leaflet assets (loaded per-page) -->
<link rel="stylesheet" href="<?= $link->asset('css/leaflet.css') ?>" />
<script src="<?= $link->asset('js/leaflet.js') ?>"></script>

<!-- Provide locations data for public/js/script.js to consume -->
<script id="map-locations" type="application/json">
<?= json_encode($locations ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>
</script>

<!-- Inline map init moved to public/js/script.js -->
