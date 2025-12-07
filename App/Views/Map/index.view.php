<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $locations */
?>

<div class="container-fluid mt-3">
    <h2>Mapa</h2>
    <div id="map" style="height:75vh; width:100%; border:1px solid #ccc; border-radius:6px;"></div>
</div>

<!-- Leaflet assets (loaded per-page) -->
<link rel="stylesheet" href="<?= $link->asset('css/leaflet.css') ?>" />
<script src="<?= $link->asset('js/leaflet.js') ?>"></script>

<script>
    // Safe JSON encoding of PHP locations
    var locations = <?= json_encode($locations ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
    console.debug('Loaded locations for map:', locations);

    function initMapWhenReady() {
        if (typeof L === 'undefined') {
            setTimeout(initMapWhenReady, 100);
            return;
        }

        // Center on Slovakia
        var map = L.map('map').setView([48.666667, 19.5], 7);

        // OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Add one preset marker (Bratislava approx.) for context
        var presetMarker = L.marker([48.148598, 17.107748]).addTo(map);
        presetMarker.bindPopup('Tu je mačka 🐱');

        if (!Array.isArray(locations) || locations.length === 0) {
            console.info('No locations to show on the map.');
            return;
        }

        locations.forEach(function(loc) {
            // validate coordinates
            var lat = parseFloat(loc.latitude);
            var lon = parseFloat(loc.longitude);
            var name = loc.cat_name || loc.meno || 'Mačka';
            var city = loc.city || '';

            if (!isFinite(lat) || !isFinite(lon)) {
                console.warn('Skipping invalid location', loc);
                return;
            }

            L.marker([lat, lon])
                .addTo(map)
                .bindPopup((name ? (name + ' - ') : '') + city);
        });
    }

    initMapWhenReady();
</script>
