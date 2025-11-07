/**
 * Lazy Loading Implementation
 * Modern intersection observer-based lazy loading with WebP support
 */

(function () {
	'use strict';

	// Configuration from WordPress
	const config = window.initoLazyConfig || {
		rootMargin: '50px',
		threshold: 0.1,
		enableWebP: false,
	};

	// Feature detection
	const supportsIntersectionObserver = 'IntersectionObserver' in window;
	const supportsWebP = config.enableWebP;

	/**
	 * Intersection Observer Lazy Loading
	 */
	class LazyLoader {
		constructor() {
			this.images = document.querySelectorAll('img[data-src], img.lazy-image');
			this.iframes = document.querySelectorAll('iframe[data-src]');
			this.observer = null;

			this.init();
		}

		init() {
			if (!supportsIntersectionObserver) {
				this.loadAllImages();
				return;
			}

			this.observer = new IntersectionObserver(
				this.handleIntersection.bind(this),
				{
					rootMargin: config.rootMargin,
					threshold: config.threshold,
				}
			);

			this.observeElements();
		}

		observeElements() {
			[...this.images, ...this.iframes].forEach((element) => {
				this.observer.observe(element);
			});
		}

		handleIntersection(entries) {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					this.loadElement(entry.target);
					this.observer.unobserve(entry.target);
				}
			});
		}

		loadElement(element) {
			if (element.tagName === 'IMG') {
				this.loadImage(element);
			} else if (element.tagName === 'IFRAME') {
				this.loadIframe(element);
			}
		}

		loadImage(img) {
			// Handle WebP format if supported
			let src = img.dataset.src;

			if (supportsWebP && src) {
				const webpSrc = this.getWebPVersion(src);
				if (webpSrc) {
					src = webpSrc;
				}
			}

			// Load the image
			const imageLoader = new Image();

			imageLoader.onload = () => {
				img.src = src;

				if (img.dataset.srcset) {
					img.srcset = img.dataset.srcset;
				}

				this.onImageLoaded(img);
			};

			imageLoader.onerror = () => {
				// Fallback to original src
				img.src = img.dataset.src;
				this.onImageLoaded(img);
			};

			imageLoader.src = src;
		}

		loadIframe(iframe) {
			iframe.src = iframe.dataset.src;
			iframe.classList.add('loaded');
		}

		onImageLoaded(img) {
			img.classList.add('loaded');
			img.style.opacity = '1';

			// Remove data attributes to clean up DOM
			delete img.dataset.src;
			delete img.dataset.srcset;

			// Trigger custom event
			const event = new CustomEvent('imageLoaded', { detail: { image: img } });
			document.dispatchEvent(event);
		}

		getWebPVersion(src) {
			// Simple WebP URL generation
			const webpExtensions = ['.jpg', '.jpeg', '.png'];

			for (const ext of webpExtensions) {
				if (src.includes(ext)) {
					return src.replace(ext, '.webp');
				}
			}

			return null;
		}

		loadAllImages() {
			// Fallback for browsers without intersection observer
			[...this.images, ...this.iframes].forEach((element) => {
				this.loadElement(element);
			});
		}
	}

	/**
	 * Performance Monitoring
	 */
	class PerformanceMonitor {
		constructor() {
			this.metrics = {};
			this.init();
		}

		init() {
			// Monitor Core Web Vitals
			this.observeLCP();
			this.observeFID();
			this.observeCLS();

			// Monitor custom metrics
			this.monitorImageLoading();
		}

		observeLCP() {
			if ('PerformanceObserver' in window) {
				const observer = new PerformanceObserver((list) => {
					const entries = list.getEntries();
					const lastEntry = entries[entries.length - 1];
					this.metrics.lcp = lastEntry.startTime;
				});

				observer.observe({ entryTypes: ['largest-contentful-paint'] });
			}
		}

		observeFID() {
			if ('PerformanceObserver' in window) {
				const observer = new PerformanceObserver((list) => {
					const entries = list.getEntries();
					entries.forEach((entry) => {
						if (entry.name === 'first-input') {
							this.metrics.fid = entry.processingStart - entry.startTime;
						}
					});
				});

				observer.observe({ entryTypes: ['first-input'] });
			}
		}

		observeCLS() {
			if ('PerformanceObserver' in window) {
				let clsValue = 0;

				const observer = new PerformanceObserver((list) => {
					const entries = list.getEntries();
					entries.forEach((entry) => {
						if (!entry.hadRecentInput) {
							clsValue += entry.value;
						}
					});

					this.metrics.cls = clsValue;
				});

				observer.observe({ entryTypes: ['layout-shift'] });
			}
		}

		monitorImageLoading() {
			let loadedImages = 0;
			let totalImages = document.querySelectorAll('img').length;

			document.addEventListener('imageLoaded', () => {
				loadedImages++;

				if (loadedImages === totalImages) {
					this.metrics.allImagesLoaded = performance.now();
				}
			});
		}

		getMetrics() {
			return this.metrics;
		}
	}

	/**
	 * Resource Prefetching
	 */
	class ResourcePrefetcher {
		constructor() {
			this.prefetchedUrls = new Set();
			this.init();
		}

		init() {
			this.setupHoverPrefetch();
			this.setupViewportPrefetch();
		}

		setupHoverPrefetch() {
			document.addEventListener('mouseover', (e) => {
				const link = e.target.closest('a[href]');

				if (link && this.shouldPrefetch(link.href)) {
					this.prefetchUrl(link.href);
				}
			});
		}

		setupViewportPrefetch() {
			if (supportsIntersectionObserver) {
				const observer = new IntersectionObserver((entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							const link = entry.target;
							if (this.shouldPrefetch(link.href)) {
								this.prefetchUrl(link.href);
							}
							observer.unobserve(link);
						}
					});
				});

				document.querySelectorAll('a[href]').forEach((link) => {
					observer.observe(link);
				});
			}
		}

		shouldPrefetch(url) {
			// Don't prefetch if already prefetched
			if (this.prefetchedUrls.has(url)) {
				return false;
			}

			// Don't prefetch external links
			const link = new URL(url, window.location.href);
			if (link.origin !== window.location.origin) {
				return false;
			}

			// Don't prefetch certain file types
			const excludeExtensions = ['.pdf', '.zip', '.exe', '.dmg'];
			if (excludeExtensions.some((ext) => url.includes(ext))) {
				return false;
			}

			return true;
		}

		prefetchUrl(url) {
			this.prefetchedUrls.add(url);

			const link = document.createElement('link');
			link.rel = 'prefetch';
			link.href = url;

			document.head.appendChild(link);
		}
	}

	/**
	 * Initialize when DOM is ready
	 */
	function init() {
		// Initialize lazy loading
		new LazyLoader();

		// Initialize performance monitoring
		if (window.location.search.includes('debug=performance')) {
			const monitor = new PerformanceMonitor();

			// Log metrics after page load
			window.addEventListener('load', () => {
				setTimeout(() => {
					console.log('Performance Metrics:', monitor.getMetrics());
				}, 3000);
			});
		}

		// Initialize resource prefetching (only on good connections)
		if (navigator.connection) {
			const connection = navigator.connection;
			const goodConnection =
				connection.effectiveType === '4g' && !connection.saveData;

			if (goodConnection) {
				new ResourcePrefetcher();
			}
		} else {
			// Fallback for browsers without connection API
			new ResourcePrefetcher();
		}
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// Expose utilities to global scope
	window.initoPerformance = {
		LazyLoader,
		PerformanceMonitor,
		ResourcePrefetcher,
	};
})();
