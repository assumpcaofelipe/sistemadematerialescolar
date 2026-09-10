const CACHE = 'central-pedidos-v1';
const ASSETS = [
    '/',
    '/assets/css/app.css',
    '/assets/js/app.js',
    '/manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE).then(function (cache) {
            return cache.addAll(ASSETS);
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys.filter(function (key) {
                    return key !== CACHE;
                }).map(function (key) {
                    return caches.delete(key);
                })
            );
        }).then(function () {
            return self.clients.claim();
        })
    );
});

// Estratégia: rede primeiro com fallback para o cache (assets estáticos)
self.addEventListener('fetch', function (event) {
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(function (response) {
                const copy = response.clone();
                caches.open(CACHE).then(function (cache) {
                    cache.put(event.request, copy);
                });
                return response;
            })
            .catch(function () {
                return caches.match(event.request);
            })
    );
});