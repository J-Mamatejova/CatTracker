// Local wrapper for Leaflet JS - re-exports official CDN script
// We use a dynamic script loader to avoid CSP issues and keep parity with CDN version.
(function(){
    var s = document.createElement('script');
    s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    s.defer = false;
    document.head.appendChild(s);
})();

