/**
 * Main JavaScript file for INITO WP theme
 *
 * This file handles general theme functionality and interactions
 * @package INITO_WP_Starter
 * @since 1.0.0
 */

(function () {
	'use strict';

	// Theme namespace
	const InitoWP = {
		// Cache DOM elements
		cache: {
			$window: null,
			$document: null,
			$body: null,
			$header: null,
			menuToggle: null,
			searchToggle: null,
			backToTop: null,
			primaryMenu: null,
			searchForm: null,
		},

		// Initialize theme
		init: function () {
			this.cacheElements();
			this.bindEvents();
			this.setupMobileMenu();
			this.setupSearch();
			this.setupBackToTop();
			this.setupSmoothScroll();
			this.setupFormValidation();
			this.enhanceSearchForms();
		},

		// Cache DOM elements for performance
		cacheElements: function () {
			this.cache.$window = window;
			this.cache.$document = document;
			this.cache.$body = document.body;
			this.cache.$header = document.getElementById('masthead');
			this.cache.menuToggle = document.querySelector('.menu-toggle');
			this.cache.searchToggle = document.querySelector('.search-toggle');
			this.cache.backToTop = document.getElementById('back-to-top');
			this.cache.primaryMenu = document.getElementById('primary-menu');
			this.cache.searchForm = document.getElementById('search-form');
		},

		// Bind event listeners
		bindEvents: function () {
			// Mobile menu toggle
			if (this.cache.menuToggle) {
				this.cache.menuToggle.addEventListener(
					'click',
					this.toggleMobileMenu.bind(this)
				);
			}

			// Search toggle
			if (this.cache.searchToggle) {
				this.cache.searchToggle.addEventListener(
					'click',
					this.toggleSearch.bind(this)
				);
			}

			// Back to top button
			if (this.cache.backToTop) {
				this.cache.backToTop.addEventListener(
					'click',
					this.scrollToTop.bind(this)
				);
			}

			// Window scroll events
			window.addEventListener(
				'scroll',
				this.throttle(this.handleScroll.bind(this), 16)
			);

			// Window resize events
			window.addEventListener(
				'resize',
				this.debounce(this.handleResize.bind(this), 250)
			);

			// Escape key handlers
			document.addEventListener('keydown', this.handleEscapeKey.bind(this));
		},

		// Setup mobile menu functionality
		setupMobileMenu: function () {
			if (!this.cache.primaryMenu) return;

			// Add submenu toggles for mobile
			const menuItems = this.cache.primaryMenu.querySelectorAll(
				'.menu-item-has-children'
			);

			menuItems.forEach((item) => {
				const link = item.querySelector('a');
				const submenu = item.querySelector('.sub-menu');

				if (link && submenu) {
					// Create submenu toggle button
					const toggle = document.createElement('button');
					toggle.className = 'submenu-toggle';
					toggle.setAttribute('aria-expanded', 'false');
					toggle.setAttribute('aria-label', 'Toggle submenu');
					toggle.innerHTML =
						'<span class="submenu-icon" aria-hidden="true">+</span>';

					// Insert toggle after link
					link.parentNode.insertBefore(toggle, link.nextSibling);

					// Bind toggle event
					toggle.addEventListener(
						'click',
						function (e) {
							e.preventDefault();
							this.toggleSubmenu(item, toggle);
						}.bind(this)
					);
				}
			});
		},

		// Toggle mobile menu
		toggleMobileMenu: function (e) {
			e.preventDefault();

			const isExpanded =
				this.cache.menuToggle.getAttribute('aria-expanded') === 'true';

			this.cache.menuToggle.setAttribute('aria-expanded', !isExpanded);
			this.cache.$body.classList.toggle('menu-open', !isExpanded);

			if (this.cache.primaryMenu) {
				this.cache.primaryMenu.classList.toggle('is-open', !isExpanded);
			}
		},

		// Toggle submenu
		toggleSubmenu: function (item, toggle) {
			const submenu = item.querySelector('.sub-menu');
			const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

			toggle.setAttribute('aria-expanded', !isExpanded);
			submenu.classList.toggle('is-open', !isExpanded);

			// Update icon
			const icon = toggle.querySelector('.submenu-icon');
			icon.textContent = isExpanded ? '+' : '−';
		},

		// Setup search functionality
		setupSearch: function () {
			if (!this.cache.searchForm) return;

			// Focus search input when form is opened
			const searchInput = this.cache.searchForm.querySelector(
				'input[type="search"]'
			);
			if (searchInput) {
				this.cache.searchForm.addEventListener(
					'transitionend',
					function () {
						if (this.cache.searchForm.getAttribute('aria-hidden') === 'false') {
							searchInput.focus();
						}
					}.bind(this)
				);
			}
		},

		// Toggle search form
		toggleSearch: function (e) {
			e.preventDefault();

			const isHidden =
				this.cache.searchForm.getAttribute('aria-hidden') === 'true';

			this.cache.searchToggle.setAttribute('aria-expanded', isHidden);
			this.cache.searchForm.setAttribute('aria-hidden', !isHidden);
			this.cache.searchForm.classList.toggle('is-open', isHidden);
		},

		// Setup back to top button
		setupBackToTop: function () {
			if (!this.cache.backToTop) return;

			// Show/hide based on scroll position
			this.toggleBackToTop();
		},

		// Handle scroll events
		handleScroll: function () {
			this.toggleBackToTop();
			this.updateHeaderOnScroll();
		},

		// Toggle back to top button visibility
		toggleBackToTop: function () {
			if (!this.cache.backToTop) return;

			const showThreshold = 300;
			const shouldShow = window.pageYOffset > showThreshold;

			this.cache.backToTop.style.display = shouldShow ? 'block' : 'none';
		},

		// Update header on scroll
		updateHeaderOnScroll: function () {
			if (!this.cache.$header) return;

			const scrolled = window.pageYOffset > 100;
			this.cache.$header.classList.toggle('is-scrolled', scrolled);
		},

		// Scroll to top smoothly
		scrollToTop: function (e) {
			e.preventDefault();

			window.scrollTo({
				top: 0,
				behavior: 'smooth',
			});
		},

		// Setup smooth scrolling for anchor links
		setupSmoothScroll: function () {
			const anchorLinks = document.querySelectorAll('a[href^="#"]');

			anchorLinks.forEach((link) => {
				link.addEventListener('click', function (e) {
					const targetId = this.getAttribute('href').substring(1);
					const targetElement = document.getElementById(targetId);

					if (targetElement) {
						e.preventDefault();

						const headerOffset = 80;
						const elementPosition = targetElement.getBoundingClientRect().top;
						const offsetPosition =
							elementPosition + window.pageYOffset - headerOffset;

						window.scrollTo({
							top: offsetPosition,
							behavior: 'smooth',
						});
					}
				});
			});
		},

		// Enhanced search forms (existing functionality preserved)
		enhanceSearchForms: function () {
			console.log('main js loaded'); // Preserve original console message

			const searchForms = document.querySelectorAll('.search-form');

			searchForms.forEach((form) => {
				const searchField = form.querySelector('.search-form__field');
				const submitButton = form.querySelector('.search-form__submit');

				if (searchField && submitButton) {
					// Add loading state functionality
					form.addEventListener('submit', (e) => {
						const query = searchField.value.trim();

						if (query.length < 2) {
							e.preventDefault();
							searchField.focus();
							return false;
						}

						// Add loading state
						submitButton.disabled = true;
						submitButton.setAttribute('aria-label', 'Searching...');

						// Add loading class for CSS animations
						form.classList.add('search-form--loading');
					});

					// Enhanced keyboard navigation
					searchField.addEventListener('keydown', (e) => {
						switch (e.key) {
							case 'Escape':
								searchField.blur();
								if (searchField.value) {
									searchField.value = '';
								}
								break;
							case 'Enter':
								e.preventDefault();
								form.dispatchEvent(new Event('submit'));
								break;
						}
					});

					// Auto-focus behavior (optional)
					if (form.classList.contains('search-form--auto-focus')) {
						searchField.focus();
					}

					// Clear search on double click
					searchField.addEventListener('dblclick', () => {
						searchField.value = '';
						searchField.focus();
					});
				}
			});
		},

		// Setup form validation
		setupFormValidation: function () {
			const forms = document.querySelectorAll('form:not(.search-form)');

			forms.forEach((form) => {
				form.addEventListener(
					'submit',
					function (e) {
						const requiredFields = form.querySelectorAll('[required]');
						let isValid = true;

						requiredFields.forEach((field) => {
							if (!field.value.trim()) {
								isValid = false;
								field.classList.add('error');
								this.showFieldError(field, 'This field is required');
							} else {
								field.classList.remove('error');
								this.hideFieldError(field);
							}
						});

						if (!isValid) {
							e.preventDefault();
						}
					}.bind(this)
				);
			});
		},

		// Show field error
		showFieldError: function (field, message) {
			let errorElement = field.parentNode.querySelector('.field-error');

			if (!errorElement) {
				errorElement = document.createElement('span');
				errorElement.className = 'field-error';
				errorElement.setAttribute('role', 'alert');
				field.parentNode.appendChild(errorElement);
			}

			errorElement.textContent = message;
			field.setAttribute('aria-describedby', 'error-' + field.name);
			errorElement.id = 'error-' + field.name;
		},

		// Hide field error
		hideFieldError: function (field) {
			const errorElement = field.parentNode.querySelector('.field-error');
			if (errorElement) {
				errorElement.remove();
			}
			field.removeAttribute('aria-describedby');
		},

		// Handle resize events
		handleResize: function () {
			// Close mobile menu on desktop
			if (window.innerWidth > 768) {
				this.cache.$body.classList.remove('menu-open');
				if (this.cache.menuToggle) {
					this.cache.menuToggle.setAttribute('aria-expanded', 'false');
				}
				if (this.cache.primaryMenu) {
					this.cache.primaryMenu.classList.remove('is-open');
				}
			}
		},

		// Handle escape key
		handleEscapeKey: function (e) {
			if (e.key === 'Escape') {
				// Close mobile menu
				if (this.cache.$body.classList.contains('menu-open')) {
					this.toggleMobileMenu(e);
				}

				// Close search
				if (
					this.cache.searchForm &&
					this.cache.searchForm.classList.contains('is-open')
				) {
					this.toggleSearch(e);
				}
			}
		},

		// Throttle function for performance
		throttle: function (func, limit) {
			let inThrottle;
			return function () {
				const args = arguments;
				const context = this;
				if (!inThrottle) {
					func.apply(context, args);
					inThrottle = true;
					setTimeout(() => (inThrottle = false), limit);
				}
			};
		},

		// Debounce function for performance
		debounce: function (func, wait) {
			let timeout;
			return function executedFunction(...args) {
				const later = () => {
					clearTimeout(timeout);
					func(...args);
				};
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
			};
		},
	};

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			InitoWP.init();
		});
	} else {
		InitoWP.init();
	}

	// Make InitoWP globally available
	window.InitoWP = InitoWP;
})();
