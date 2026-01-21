<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $locations */
?>

<div class="container-fluid mt-3">
    <h2 data-i18n="map.title">Mapa</h2>
    <div id="map" style="height:75vh; width:100%; border:1px solid #ccc; border-radius:6px;"></div>
</div>

<!-- Leaflet assets (loaded per-page) -->
<link rel="stylesheet" href="<?= $link->asset('css/leaflet.css') ?>" />
<script src="<?= $link->asset('js/leaflet.js') ?>"></script>

<!-- Provide locations data for public/js/script.js to consume -->
<script id="map-locations" type="application/json">
<?= json_encode($locations ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>
</script>

<!-- Inline map init moved to public/js/script.js -->
