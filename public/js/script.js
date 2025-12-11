(function(){
    'use strict';

    /* Helper: parse URL params */
    function getParam(name) {
        const params = new URLSearchParams(window.location.search);
        return params.get(name);
    }

    /* Helper: show modal with message (Bootstrap 5) */
    function showModalWithMessage(modalId, alertContainerId, message, isError = true) {
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        const alertContainer = document.getElementById(alertContainerId);
        if (alertContainer) {
            alertContainer.innerHTML = '';
            const wrapper = document.createElement('div');
            wrapper.className = isError ? 'alert alert-danger' : 'alert alert-success';
            wrapper.textContent = message;
            alertContainer.appendChild(wrapper);
        }
        try {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } catch (e) {
            console.warn('Bootstrap modal not available', e);
        }
    }

    /* Profile page: open login/signup modals when URL params are present */
    function initProfileModalsFromURL() {
        const loginError = getParam('loginError');
        const loginMessage = getParam('loginMessage');
        const signupError = getParam('signupError');
        const signupMessage = getParam('signupMessage');

        if (loginError && loginMessage) {
            showModalWithMessage('loginModal', 'loginModalAlert', decodeURIComponent(loginMessage));
        }
        if (signupError && signupMessage) {
            showModalWithMessage('signupModal', 'signupModalAlert', decodeURIComponent(signupMessage));
        }
    }

    /* CatDatabase: add new cat modal, mini-map and submit */
    function initCatDatabaseAddModal() {
        const addBtn = document.getElementById('addNewCatBtn');
        if (!addBtn) return;

        const loginMsg = document.getElementById('loginRequiredMsg');
        const addCatModalEl = document.getElementById('addCatModal');
        const addCatForm = document.getElementById('addCatForm');
        const addCatAlert = document.getElementById('addCatAlert');
        if (!addCatModalEl || !addCatForm) return;

        const addCatModal = (function(){ try { return new bootstrap.Modal(addCatModalEl); } catch(e){ return null; } })();

        // read logged state from data- attribute (set by server in the view)
        const isLogged = (addBtn.dataset.logged === '1');

        addBtn.addEventListener('click', function(){
            try {
                if (!isLogged) {
                    if (loginMsg) {
                        loginMsg.style.display = 'inline';
                        setTimeout(() => { loginMsg.style.display = 'none'; }, 3000);
                    }
                    return;
                }
                if (addCatAlert) addCatAlert.innerHTML = '';
                if (addCatModal) addCatModal.show();
            } catch (err) {
                console.error('Error handling add button click', err);
            }
        });

        // Mini-map setup
        let miniMap = null;
        let miniMarker = null;

        addCatModalEl.addEventListener ? addCatModalEl.addEventListener('shown.bs.modal', function () {
            if (miniMap === null) {
                // Try to initialize Leaflet; if not loaded, retry later
                try {
                    if (typeof L === 'undefined') {
                        console.warn('Leaflet not yet loaded for mini map');
                        setTimeout(function(){
                            // try again after a short delay
                            const ev = new Event('shown.bs.modal');
                            addCatModalEl.dispatchEvent(ev);
                        }, 200);
                        return;
                    }

                    miniMap = L.map('addCatMiniMap').setView([48.666667, 19.5], 7);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(miniMap);

                    miniMap.on('click', function(e) {
                        const lat = e.latlng.lat.toFixed(6);
                        const lon = e.latlng.lng.toFixed(6);
                        if (miniMarker) {
                            miniMarker.setLatLng(e.latlng);
                        } else {
                            miniMarker = L.marker(e.latlng).addTo(miniMap);
                        }
                        const latEl = document.getElementById('cat-latitude');
                        const lonEl = document.getElementById('cat-longitude');
                        if (latEl) latEl.value = lat;
                        if (lonEl) lonEl.value = lon;
                    });
                } catch (err) {
                    console.error('Failed to initialize mini map', err);
                }
            } else {
                setTimeout(() => { try{ miniMap.invalidateSize(); } catch(e){} }, 200);
            }
        }) : null;

        // Form submit via fetch
        addCatForm.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            if (addCatAlert) addCatAlert.innerHTML = '';

            const name = document.getElementById('cat-name')?.value.trim() || '';
            const text = document.getElementById('cat-text')?.value.trim() || '';
            const lat = document.getElementById('cat-latitude')?.value.trim() || '';
            const lon = document.getElementById('cat-longitude')?.value.trim() || '';

            if (!name || !text) {
                if (addCatAlert) addCatAlert.innerHTML = '<div class="alert alert-danger">Name and text are required.</div>';
                return;
            }
            if (!lat || !lon) {
                if (addCatAlert) addCatAlert.innerHTML = '<div class="alert alert-danger">Please select a location on the mini map.</div>';
                return;
            }

            const formData = new FormData(addCatForm);

            try {
                // include credentials to ensure session cookie is sent
                const resp = await fetch('?c=catdatabase&a=save', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (resp.ok) {
                    if (addCatModal) addCatModal.hide();
                    window.location.reload();
                } else {
                    const txt = await resp.text();
                    if (addCatAlert) addCatAlert.innerHTML = '<div class="alert alert-danger">Save failed: ' + (txt || resp.statusText) + '</div>';
                    console.error('Save failed', resp.status, txt);
                }
            } catch (err) {
                if (addCatAlert) addCatAlert.innerHTML = '<div class="alert alert-danger">Save failed: ' + err.message + '</div>';
                console.error('Fetch error when saving cat', err);
            }
        });
    }

    /* Map page: read JSON data from a script#map-data (application/json) or from global var 'locations' */
    function initMapPage() {
        const mapEl = document.getElementById('map');
        if (!mapEl) return;

        let locations = [];
        const dataScript = document.getElementById('map-data') || document.getElementById('map-locations');
        if (dataScript) {
            try { locations = JSON.parse(dataScript.textContent || '[]'); } catch(e){ console.warn('Failed to parse map data', e); locations = []; }
        } else if (window.locations) {
            locations = window.locations;
        }

        function initMapWhenReady() {
            if (typeof L === 'undefined') {
                setTimeout(initMapWhenReady, 100);
                return;
            }

            var map = L.map('map').setView([48.666667, 19.5], 7);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
            }).addTo(map);

            // optional preset marker
            try {
                var presetMarker = L.marker([48.148598, 17.107748]).addTo(map);
                presetMarker.bindPopup('Tu je mačka 🐱');
            } catch (e) {}

            if (!Array.isArray(locations) || locations.length === 0) {
                console.info('No locations to show on the map.');
                return;
            }

            locations.forEach(function(loc) {
                var lat = parseFloat(loc.latitude);
                var lon = parseFloat(loc.longitude);
                var name = loc.cat_name || loc.meno || 'Mačka';
                var city = loc.city || '';

                if (!isFinite(lat) || !isFinite(lon)) {
                    console.warn('Skipping invalid location', loc);
                    return;
                }

                L.marker([lat, lon]).addTo(map).bindPopup((name ? (name + ' - ') : '') + city);
            });
        }

        initMapWhenReady();
    }

    /* DOMContentLoaded: initialize parts depending on presence */
    document.addEventListener('DOMContentLoaded', function(){
        try { initProfileModalsFromURL(); } catch(e) { console.error(e); }
        try { initCatDatabaseAddModal(); } catch(e) { console.error(e); }
        try { initMapPage(); } catch(e) { console.error(e); }
    });

})();
