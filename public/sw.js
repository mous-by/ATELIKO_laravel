const ATELIKO_CACHE = 'ateliko-shell-v3';
const SHELL_ASSETS = [
    '/',
    '/manifest.json',
    '/assets/css/bootstrap.min.css',
    '/assets/css/icons.css',
    '/assets/css/app.css',
    '/assets/images/logo_ateliko.png',
    '/assets/images/ateliko-icon-32.png',
    '/assets/images/ateliko-icon-192.png',
    '/assets/images/ateliko-icon-512.png',
    '/assets/js/bootstrap.bundle.min.js',
    '/assets/js/jquery.min.js'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(ATELIKO_CACHE)
            .then(cache => cache.addAll(SHELL_ASSETS))
            .catch(() => undefined)
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(key => key !== ATELIKO_CACHE).map(key => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    if (url.pathname.includes('/storage/')) return;

    event.respondWith(
        fetch(event.request).catch(() =>
            caches.match(event.request).then(r => r || new Response('', { status: 503 }))
        )
    );
});
