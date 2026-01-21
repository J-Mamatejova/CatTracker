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

    /* Posts page: AJAX create + delete handlers */
    function escapeHtml(s) {
        if (!s) return '';
        return s.replace(/[&<>"]|'/g, function (c) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[c];
        });
    }

    function nl2br(str) {
        return (str || '').replace(/\n/g, '<br>');
    }

    function renderPostHtml(post) {
        var div = document.createElement('div');
        div.className = 'card mb-3 post-item';
        div.setAttribute('data-id', post.id);
        // use translations for Edit/Delete text
        var editText = t('post.edit') || 'Edit';
        var deleteText = t('post.delete') || 'Delete';
        div.innerHTML = '' +
            '<div class="card-body">' +
            '<h5 class="card-title">' + escapeHtml(post.title) + '</h5>' +
            '<h6 class="card-subtitle mb-2 text-muted">by ' + escapeHtml(post.author || 'user') +
            (post.cat_name ? ' — cat: ' + escapeHtml(post.cat_name) : '') +
            '<small class="text-muted float-end">' + escapeHtml(post.created_at || '') + '</small>' +
            '</h6>' +
            '<p class="card-text">' + nl2br(escapeHtml(post.content)) + '</p>' +
            '<div>' +
            '<a href="?c=post&a=edit&id=' + post.id + '" class="btn btn-sm btn-secondary">' + escapeHtml(editText) + '</a> ' +
            '<button class="btn btn-sm btn-danger btn-delete-post" data-id="' + post.id + '">' + escapeHtml(deleteText) + '</button>' +
            '</div></div>';
        return div;
    }

    function initPostsPage() {
        var createForm = document.getElementById('createPostForm');
        var createAlert = document.getElementById('createPostAlert');
        var feed = document.getElementById('postsFeed');

        if (createForm) {
            createForm.addEventListener('submit', function(ev){
                ev.preventDefault();
                if (createAlert) createAlert.innerHTML = '';
                var fd = new FormData(createForm);
                fetch('?c=post&a=save', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                }).then(function(resp){
                    return resp.json().catch(function(){ return { error: 'Invalid response' }; });
                }).then(function(json){
                    if (json && json.success && json.post) {
                        var node = renderPostHtml(json.post);
                        if (feed) feed.insertBefore(node, feed.firstChild);
                        createForm.reset();
                    } else {
                        var msg = (json && json.error) ? json.error : 'Save failed';
                        if (createAlert) createAlert.innerHTML = '<div class="alert alert-danger">' + escapeHtml(msg) + '</div>';
                    }
                }).catch(function(err){
                    if (createAlert) createAlert.innerHTML = '<div class="alert alert-danger">Save failed: ' + escapeHtml(err.message) + '</div>';
                    console.error('Create post error', err);
                });
            });
        }

        if (feed) {
            feed.addEventListener('click', function(ev){
                var t = ev.target;
                if (t && t.classList && t.classList.contains('btn-delete-post')) {
                    var id = t.getAttribute('data-id');
                    if (!id) return;
                    var confirmMsg = t('post.delete_confirm') || 'Are you sure you want to delete this post?';
                    if (!confirm(confirmMsg)) return;

                    var btn = t; btn.disabled = true;
                    var params = new URLSearchParams(); params.append('id', id);

                    fetch('?c=post&a=delete', {
                        method: 'POST',
                        body: params,
                        credentials: 'same-origin'
                    }).then(function(resp){
                        return resp.json().catch(function(){ return { error: 'Invalid response' }; });
                    }).then(function(json){
                        if (json && json.success) {
                            var item = feed.querySelector('.post-item[data-id="' + id + '"]');
                            if (item && item.parentNode) item.parentNode.removeChild(item);
                        } else {
                            alert('Delete failed: ' + (json && json.error ? json.error : 'Unknown'));
                            btn.disabled = false;
                        }
                    }).catch(function(err){
                        alert('Delete failed: ' + err.message);
                        console.error('Delete error', err);
                        btn.disabled = false;
                    });
                }
            });
        }
    }

    /* Simple i18n: dictionaries and helpers */
    const I18N = {
        sk: {
            'nav.catdatabase': 'Databáza mačiek',
            'nav.posts': 'Príspevky',
            'nav.map': 'Mapa',
            'nav.profile': 'Profil',
            'home.heading': 'Pomôžte nám sledovať a chrániť uličné mačky',
            'home.p1': 'Útulky a záchranné stanice sú často preplnené a nie všetky mačky sa dajú umiestniť do stáleho domova. Mnohé z nich žijú vo voľnej prírode a potrebujú našu podporu, aby prežili a mali čo najlepšiu starostlivosť.',
            'home.p2': '<strong>Cat Tracker</strong> umožňuje každému prispieť k tvorbe databázy uličných mačiek. Môžete:',
            'home.li1': 'poslať informácie o mačke, ktorú ste stretli,',
            'home.li2': 'skontrolovať, či už bola dokumentovaná,',
            'home.li3': 'pozrieť si na mape, kde sa nachádza viac mačiek a ktoré miesta potrebujú pomoc.',
            'home.p3': 'Vďaka vašim príspevkom môžeme lepšie sledovať tieto mačky, koordinovať odchyt pre kastráciu, zabezpečiť veterinárnu starostlivosť alebo im jednoducho doplniť jedlo tam, kde je potrebné.',
            'home.p4': 'Spoločne môžeme urobiť život uličných mačiek bezpečnejším a zdravším.',
            'home.authors': 'Autori',
            'login.title': 'Prihlásiť sa',
            'signup.title': 'Registrovať sa',
            'login.email': 'Email',
            'login.password': 'Heslo',
            'login.submit': 'Prihlásiť',
            'signup.username': 'Používateľské meno',
            'signup.email': 'Email',
            'signup.password': 'Heslo',
            'signup.password_confirm': 'Potvrď heslo',
            'signup.submit': 'Registrovať',
            'common.close': 'Zatvoriť',
            'catdb.add': 'Pridať macku',
            'catdb.login_required': 'musíte sa prihlásiť, aby ste pridali macky',
            'catdb.edit': 'Upraviť',
            'catdb.delete': 'Zmazať',
            'catdb.title': 'Databáza mačiek',
            'confirm.delete.cat': 'Naozaj chcete zmazať túto mačku?',
            'post.title': 'Príspevky',
            'post.create': 'Vytvoriť príspevok',
            'post.create_btn': 'Vytvoriť',
            'post.edit': 'Upraviť',
            'post.delete': 'Zmazať',
            'post.refresh': 'Obnoviť',
            'post.delete_confirm': 'Naozaj chcete zmazať tento príspevok?',
            'post.label.title': 'Názov',
            'post.label.content': 'Text',
            'post.label.cat': 'Súvisiaca mačka',
            'post.placeholder.title': 'Názov',
            'post.placeholder.content': 'Text príspevku',
            'post.select.cat': 'Vybrať mačku',
            'map.title': 'Mapa',
            'profile.heading': 'Profil',
            'profile.welcome': 'Vitaj,',
            'profile.stats': 'Štatistiky',
            'profile.posts': 'Počet príspevkov:',
            'profile.cats': 'Počet pridaných mačiek:',
            'profile.change_password': 'Zmeniť heslo',
            'profile.old_password': 'Staré heslo',
            'profile.new_password': 'Nové heslo',
            'profile.new_password_confirm': 'Potvrď nové heslo',
            'profile.change_password_submit': 'Zmeniť heslo',
            'profile.logout': 'Odhlásiť sa',
            'profile.change_password_success': 'Heslo bolo úspešne zmenené',
            'profile.please_login': 'Prosím, prihláste sa alebo si vytvorte účet.',
            'login.open': 'Prihlásiť sa',
            'signup.open': 'Registrovať sa'
        },
        en: {
            'nav.catdatabase': 'Cat Database',
            'nav.posts': 'Posts',
            'nav.map': 'Map View',
            'nav.profile': 'Profile',
            'home.heading': 'Help us track and protect stray cats',
            'home.p1': 'Shelters and rescue centers are often over capacity and not all cats can be placed into permanent homes. Many live outdoors and need our support to survive and receive proper care.',
            'home.p2': '<strong>Cat Tracker</strong> lets anyone contribute to a database of stray cats. You can:',
            'home.li1': 'submit information about a cat you encountered,',
            'home.li2': 'check whether it has already been documented,',
            'home.li3': 'view on a map where more cats are located and which places need help.',
            'home.p3': 'With your contributions we can better track these cats, coordinate trapping for neutering, provide veterinary care or simply top up food where needed.',
            'home.p4': 'Together we can make stray cats\' lives safer and healthier.',
            'home.authors': 'Authors',
            'login.title': 'Log in',
            'signup.title': 'Sign up',
            'login.email': 'Email',
            'login.password': 'Password',
            'login.submit': 'Log in',
            'signup.username': 'Username',
            'signup.email': 'Email',
            'signup.password': 'Password',
            'signup.password_confirm': 'Confirm password',
            'signup.submit': 'Sign up',
            'common.close': 'Close',
            'catdb.add': 'Add new cat',
            'catdb.login_required': 'you must log in to add cats',
            'catdb.edit': 'Edit',
            'catdb.delete': 'Delete',
            'catdb.title': 'Cat Database',
            'confirm.delete.cat': 'Are you sure you want to delete this cat?',
            'post.title': 'Feed / Posts',
            'post.create': 'Create new post',
            'post.create_btn': 'Create',
            'post.edit': 'Edit',
            'post.delete': 'Delete',
            'post.refresh': 'Refresh',
            'post.delete_confirm': 'Are you sure you want to delete this post?',
            'post.label.title': 'Title',
            'post.label.content': 'Content',
            'post.label.cat': 'Related cat',
            'post.placeholder.title': 'Title',
            'post.placeholder.content': 'Content',
            'post.select.cat': 'Select related cat',
            'map.title': 'Map',
            'profile.heading': 'Profile',
            'profile.welcome': 'Welcome,',
            'profile.stats': 'Statistics',
            'profile.posts': 'Number of posts:',
            'profile.cats': 'Number of added cats:',
            'profile.change_password': 'Change password',
            'profile.old_password': 'Old password',
            'profile.new_password': 'New password',
            'profile.new_password_confirm': 'Confirm new password',
            'profile.change_password_submit': 'Change password',
            'profile.logout': 'Log out',
            'profile.change_password_success': 'Password successfully changed',
            'profile.please_login': 'Please log in or create an account.',
            'login.open': 'Log in',
            'signup.open': 'Sign up'
        }
    };

    /* i18n helpers */
    function currentLang() {
        return localStorage.getItem('cattracker_lang') || 'sk';
    }

    function t(key) {
        const lang = currentLang();
        const dict = I18N[lang] || I18N['sk'];
        return dict[key] || '';
    }

    function applyTranslations(lang) {
        const dict = I18N[lang] || I18N['sk'];
        document.querySelectorAll('[data-i18n]').forEach(function(el){
            const key = el.getAttribute('data-i18n');
            if (!key) return;
            if (dict[key]) {
                // if element contains HTML (like <strong>) allow HTML
                if (dict[key].indexOf('<') !== -1) el.innerHTML = dict[key]; else el.textContent = dict[key];
            }
        });

        // handle placeholders (data-i18n-placeholder)
        document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el){
            const key = el.getAttribute('data-i18n-placeholder');
            if (!key) return;
            if (dict[key]) {
                el.setAttribute('placeholder', dict[key]);
            }
        });

        // update modal titles
        const loginLabel = document.querySelector('#loginModalLabel');
        const signupLabel = document.querySelector('#signupModalLabel');
        if (loginLabel) loginLabel.textContent = dict['login.title'] || loginLabel.textContent;
        if (signupLabel) signupLabel.textContent = dict['signup.title'] || signupLabel.textContent;

        // update text on buttons/inputs that used data-i18n (already set above for BUTTON elements) - ensure translated text for existing posts
        document.querySelectorAll('.post-item').forEach(function(item){
            const editBtn = item.querySelector('.btn-secondary');
            const delBtn = item.querySelector('.btn-delete-post');
            if (editBtn) editBtn.textContent = dict['post.edit'] || editBtn.textContent;
            if (delBtn) delBtn.textContent = dict['post.delete'] || delBtn.textContent;
        });
        // update modal footer button texts (if present)
        document.querySelectorAll('[data-i18n]').forEach(function(el){
            // already done above
        });
        // Translate buttons like modal Save/Close
        document.querySelectorAll('[data-i18n]').forEach(function(el){
            const key = el.getAttribute('data-i18n');
            if (!key) return;
            if (dict[key]) {
                if (el.tagName === 'INPUT' || el.tagName === 'BUTTON') {
                    el.textContent = dict[key];
                }
            }
        });

    }

    function initI18n() {
        const saved = localStorage.getItem('cattracker_lang') || 'sk';
        applyTranslations(saved);
        // set active class on buttons
        document.querySelectorAll('.lang-btn').forEach(function(btn){
            if (btn.dataset.lang === saved) btn.classList.add('active'); else btn.classList.remove('active');
            btn.addEventListener('click', function(){
                const l = btn.dataset.lang || 'sk';
                localStorage.setItem('cattracker_lang', l);
                applyTranslations(l);
                document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                // Re-bind confirmation handlers to use new translations
                bindDeleteConfirmHandlers();
                // update dynamically created elements (e.g. posts)
                document.querySelectorAll('.post-item').forEach(function(item){
                    const editBtn = item.querySelector('.btn-secondary');
                    const delBtn = item.querySelector('.btn-delete-post');
                    if (editBtn) editBtn.textContent = t('post.edit');
                    if (delBtn) delBtn.textContent = t('post.delete');
                });
            });
        });
    }

    function bindDeleteConfirmHandlers() {
        const lang = localStorage.getItem('cattracker_lang') || 'sk';
        const dict = I18N[lang] || I18N['sk'];
        document.querySelectorAll('.confirm-delete-form').forEach(function(form){
            if (form._i18nBound) return;
            form.addEventListener('submit', function(ev){
                const key = form.getAttribute('data-confirm-key') || 'confirm.delete.cat';
                const msg = dict[key] || 'Are you sure?';
                if (!confirm(msg)) {
                    ev.preventDefault();
                }
            });
            form._i18nBound = true;
        });
    }

    /* DOMContentLoaded: initialize parts depending on presence */
    document.addEventListener('DOMContentLoaded', function(){
        try { initProfileModalsFromURL(); } catch(e) { console.error(e); }
        try { initCatDatabaseAddModal(); } catch(e) { console.error(e); }
        try { initMapPage(); } catch(e) { console.error(e); }
        try { initPostsPage(); } catch(e) { console.error(e); }
        try { initI18n(); } catch(e) { console.error('i18n init failed', e); }
        try { bindDeleteConfirmHandlers(); } catch(e) { console.error('bind confirm failed', e); }
    });

})();
