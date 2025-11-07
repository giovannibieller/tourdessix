const CACHE_NAME = 'inito-wp-cache-v1';

// Listen for fetch events
self.addEventListener('fetch', (event) => {
	const request = event.request;

	// Only handle GET requests for CSS or JS files
	if (
		request.method === 'GET' &&
		(request.destination === 'style' ||
			request.destination === 'script' ||
			request.url.match(/\.css(\?|$)/) ||
			request.url.match(/\.js(\?|$)/))
	) {
		event.respondWith(
			caches.match(request).then((response) => {
				if (response) {
					// Serve from cache
					return response;
				}
				// Fetch from network and cache it
				return fetch(request).then((networkResponse) => {
					// Only cache valid responses
					if (!networkResponse || networkResponse.status !== 200) {
						return networkResponse;
					}
					// Clone the response so we can cache it
					const responseToCache = networkResponse.clone();
					caches.open(CACHE_NAME).then((cache) => {
						cache.put(request, responseToCache);
					});
					return networkResponse;
				});
			})
		);
	}
});

// Cleanup old caches on activate
self.addEventListener('activate', (event) => {
	event.waitUntil(
		caches.keys().then((cacheNames) => {
			return Promise.all(
				cacheNames
					.filter((name) => name !== CACHE_NAME) // keep only the current cache
					.map((name) => caches.delete(name))
			);
		})
	);
});
