var cacheName = 'v003';
var appFiles = [
  './index.html',
  './images/logo-512.png',
  './images/logo-256.png',
  './images/logo-192.png',
  './images/logo-128.png',
  './scripts/jquery.min.js',
  './scripts/mobile.js',
  './scripts/info.js',
  './styles/mobile.css',
  './favicon.ico'
];


self.addEventListener('install', function(e) {
    //console.log('[Service Worker] Install');
    e.waitUntil(
        caches.open(cacheName).then(function(cache) {
            //console.log('[Service Worker] Caching all: app shell and content');
            return cache.addAll(appFiles);
        })
    );
});


self.addEventListener('fetch', function(e) {
    if (e.request.method != 'POST') {
        e.respondWith(
            caches.match(e.request).then(function(r) {
            //console.log('[Service Worker] Fetching resource: '+e.request.url);
            return r || fetch(e.request).then(function(response) {
                return caches.open(cacheName).then(function(cache) {
                    //console.log('[Service Worker] Caching new resource: '+e.request.url);
                    cache.put(e.request, response.clone());
                    return response;
                });
            });
        }));
    }
});

// called when a new version of this worker is activated
// we just delete the old cache
self.addEventListener('activate', function(event) {
    var cacheKeeplist = [cacheName];
    event.waitUntil(
        caches.keys().then(function(keyList) {
        return Promise.all(keyList.map(function(key) {
            if (cacheKeeplist.indexOf(key) === -1) {
                return caches.delete(key);
            }
        }));
    })
  );
});

