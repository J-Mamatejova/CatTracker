<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Cat Tracker Mapa</title>
    <?php /** @var \Framework\Support\LinkGenerator $link */ ?>
    <!-- Local Leaflet assets -->
    <link rel="stylesheet" href="<?= $link->asset('css/leaflet.css') ?>" />
    <script src="<?= $link->asset('js/leaflet.js') ?>"></script>
    <style>
        #map { height: 100vh; width: 100%; }
    </style>
</head>
<body>
<div class="container-fluid mt-3">
    <h2>Mapa Slovenska</h2>
    <div id="map" style="height:75vh; width:100%; border:1px solid #ccc; border-radius:6px;"></div>
</div>
<script>
    // Initialize map after Leaflet library is available
    function initMapWhenReady() {
        if (typeof L === 'undefined') {
            setTimeout(initMapWhenReady, 100);
            return;
        }

        // Center on Slovakia
        const map = L.map('map').setView([48.666667, 19.5], 7);

        // OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Add one preset marker (Bratislava approx.)
        const presetMarker = L.marker([48.148598, 17.107748]).addTo(map);
        presetMarker.bindPopup('Tu je mačka 🐱');

        // Add marker on click with popup
        map.on('click', function (e) {
            const m = L.marker(e.latlng).addTo(map);
            m.bindPopup('Tu je mačka 🐱').openPopup();
        });
    }

    initMapWhenReady();
</script>
</body>
</html>
