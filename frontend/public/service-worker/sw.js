const CACHE_NAME = 'farmhandpro-v1';
const API_CACHE_NAME = 'farmhandpro-api-v1';
const STATIC_ASSETS = [
    '/',
    '/index.html',
    '/assets/css/style.css',
    '/assets/js/app.js',
    '/manifest.json',
    'https://cdn.tailwindcss.com',
    'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[ServiceWorker] Caching static assets');
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME && cacheName !== API_CACHE_NAME) {
                        console.log('[ServiceWorker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - network-first for API, cache-first for assets
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // API requests - network first with fallback to cache
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache successful API responses
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(API_CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Fall back to cached API response
                    return caches.match(request).then((response) => {
                        if (response) {
                            return response;
                        }
                        // Return offline error response
                        return new Response(
                            JSON.stringify({
                                error: 'Offline - cached data unavailable',
                                offline: true
                            }),
                            {
                                status: 503,
                                statusText: 'Service Unavailable',
                                headers: new Headers({
                                    'Content-Type': 'application/json',
                                })
                            }
                        );
                    });
                })
        );
        return;
    }

    // Static assets - cache first
    event.respondWith(
        caches.match(request).then((response) => {
            if (response) {
                return response;
            }

            return fetch(request).then((response) => {
                // Cache successful responses
                if (response.ok && request.method === 'GET') {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }
                return response;
            }).catch(() => {
                // Return offline page for navigation requests
                if (request.destination === 'document') {
                    return caches.match('/index.html');
                }
                return new Response('Offline - Resource unavailable', {
                    status: 503,
                    statusText: 'Service Unavailable',
                });
            });
        })
    );
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('[ServiceWorker] Background sync:', event.tag);

    if (event.tag === 'sync-attendance') {
        event.waitUntil(syncAttendanceData());
    }

    if (event.tag === 'sync-reports') {
        event.waitUntil(syncReportData());
    }
});

// Sync attendance data from IndexedDB
async function syncAttendanceData() {
    try {
        const db = await openDB();
        const items = await getAllFromStore(db, 'attendance');

        for (const item of items) {
            const response = await fetch('/api/attendance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${await getAuthToken()}`
                },
                body: JSON.stringify(item)
            });

            if (response.ok) {
                await deleteFromStore(db, 'attendance', item.id);
            }
        }

        console.log('[ServiceWorker] Attendance sync completed');
    } catch (error) {
        console.error('[ServiceWorker] Attendance sync failed:', error);
        throw error;
    }
}

// Sync reports from IndexedDB
async function syncReportData() {
    try {
        const db = await openDB();
        const items = await getAllFromStore(db, 'reports');

        for (const item of items) {
            const response = await fetch('/api/reports', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${await getAuthToken()}`
                },
                body: JSON.stringify(item)
            });

            if (response.ok) {
                await deleteFromStore(db, 'reports', item.id);
            }
        }

        console.log('[ServiceWorker] Reports sync completed');
    } catch (error) {
        console.error('[ServiceWorker] Reports sync failed:', error);
        throw error;
    }
}

// IndexedDB helpers
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('farmhandpro_db', 1);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function getAllFromStore(db, storeName) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.getAll();

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function deleteFromStore(db, storeName, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.delete(id);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}

function getAuthToken() {
    // This would typically retrieve the auth token from a shared storage
    return Promise.resolve(localStorage.getItem('authToken'));
}

// Push notifications
self.addEventListener('push', (event) => {
    if (!event.data) return;

    const options = {
        body: event.data.text(),
        icon: '/icons/icon-192x192.png',
        badge: '/icons/badge-72x72.png',
        tag: 'farmhandpro-notification',
        requireInteraction: false,
    };

    event.waitUntil(
        self.registration.showNotification('FarmHandPro', options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((clientList) => {
            // Focus existing window or open new one
            for (let i = 0; i < clientList.length; i++) {
                if (clientList[i].url === '/' && 'focus' in clientList[i]) {
                    return clientList[i].focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow('/');
            }
        })
    );
});
