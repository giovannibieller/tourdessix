/**
 * Accessibility JavaScript
 * Enhances theme accessibility with JavaScript
 */

(function () {
	'use strict';

	// Configuration from WordPress
	const config = window.initoA11y || {
		skipLinkFocus: 'Skip to main content',
		expandMenu: 'Expand menu',
		collapseMenu: 'Collapse menu',
		closeDialog: 'Close dialog',
	};

	/**
	 * Accessibility Manager
	 */
	class AccessibilityManager {
		constructor() {
			this.isInitialized = false;
			this.focusableElements =
				'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, *[tabindex], *[contenteditable]';
			this.init();
		}

		init() {
			if (this.isInitialized) return;

			this.setupSkipLinks();
			this.setupFocusManagement();
			this.setupKeyboardNavigation();
			this.setupARIAUpdates();
			this.setupModalAccessibility();
			this.setupFormAccessibility();
			this.setupMenuAccessibility();

			this.isInitialized = true;
		}

		/**
		 * Setup skip link functionality
		 */
		setupSkipLinks() {
			const skipLinks = document.querySelectorAll('.skip-link');

			skipLinks.forEach((link) => {
				link.addEventListener('click', (e) => {
					const target = document.querySelector(link.getAttribute('href'));
					if (target) {
						e.preventDefault();
						target.focus();
						target.scrollIntoView();
					}
				});
			});
		}

		/**
		 * Setup focus management
		 */
		setupFocusManagement() {
			// Focus visible outline for keyboard users only
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Tab') {
					document.body.classList.add('keyboard-navigation');
				}
			});

			document.addEventListener('mousedown', () => {
				document.body.classList.remove('keyboard-navigation');
			});

			// Focus trapping for modals and menus
			this.setupFocusTrap();
		}

		/**
		 * Setup focus trap for modal elements
		 */
		setupFocusTrap() {
			const trapFocus = (element) => {
				const focusableElements = element.querySelectorAll(
					this.focusableElements
				);
				const firstFocusable = focusableElements[0];
				const lastFocusable = focusableElements[focusableElements.length - 1];

				element.addEventListener('keydown', (e) => {
					if (e.key === 'Tab') {
						if (e.shiftKey) {
							if (document.activeElement === firstFocusable) {
								e.preventDefault();
								lastFocusable.focus();
							}
						} else {
							if (document.activeElement === lastFocusable) {
								e.preventDefault();
								firstFocusable.focus();
							}
						}
					}

					if (e.key === 'Escape') {
						this.closeModal(element);
					}
				});
			};

			// Apply to modals, dialogs, and dropdown menus
			const modals = document.querySelectorAll(
				'[role="dialog"], [role="modal"], .modal, .dropdown-menu'
			);
			modals.forEach((modal) => trapFocus(modal));
		}

		/**
		 * Setup keyboard navigation
		 */
		setupKeyboardNavigation() {
			// Arrow key navigation for menus
			const menus = document.querySelectorAll(
				'[role="menu"], .menu, .navigation'
			);

			menus.forEach((menu) => {
				this.setupMenuKeyboardNavigation(menu);
			});

			// Tab/Enter activation for interactive elements
			const interactiveElements = document.querySelectorAll(
				'[role="button"]:not(button), .clickable'
			);

			interactiveElements.forEach((element) => {
				element.addEventListener('keydown', (e) => {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						element.click();
					}
				});
			});
		}

		/**
		 * Setup menu keyboard navigation
		 */
		setupMenuKeyboardNavigation(menu) {
			const menuItems = menu.querySelectorAll('a, button, [role="menuitem"]');

			menuItems.forEach((item, index) => {
				item.addEventListener('keydown', (e) => {
					let targetIndex;

					switch (e.key) {
						case 'ArrowDown':
							e.preventDefault();
							targetIndex = (index + 1) % menuItems.length;
							menuItems[targetIndex].focus();
							break;

						case 'ArrowUp':
							e.preventDefault();
							targetIndex = (index - 1 + menuItems.length) % menuItems.length;
							menuItems[targetIndex].focus();
							break;

						case 'Home':
							e.preventDefault();
							menuItems[0].focus();
							break;

						case 'End':
							e.preventDefault();
							menuItems[menuItems.length - 1].focus();
							break;

						case 'Escape':
							e.preventDefault();
							this.closeMenu(menu);
							break;
					}
				});
			});
		}

		/**
		 * Setup ARIA updates
		 */
		setupARIAUpdates() {
			// Dynamic ARIA-expanded updates
			const expandableElements = document.querySelectorAll('[aria-expanded]');

			expandableElements.forEach((element) => {
				element.addEventListener('click', () => {
					const isExpanded = element.getAttribute('aria-expanded') === 'true';
					element.setAttribute('aria-expanded', !isExpanded);

					// Update button text for screen readers
					const buttonText = element.querySelector('.button-text, .menu-text');
					if (buttonText) {
						buttonText.textContent = isExpanded
							? config.expandMenu
							: config.collapseMenu;
					}
				});
			});

			// Live regions for dynamic content
			this.setupLiveRegions();
		}

		/**
		 * Setup live regions for announcements
		 */
		setupLiveRegions() {
			// Create live region if it doesn't exist
			if (!document.getElementById('aria-live-region')) {
				const liveRegion = document.createElement('div');
				liveRegion.id = 'aria-live-region';
				liveRegion.setAttribute('aria-live', 'polite');
				liveRegion.setAttribute('aria-atomic', 'true');
				liveRegion.style.position = 'absolute';
				liveRegion.style.left = '-10000px';
				liveRegion.style.width = '1px';
				liveRegion.style.height = '1px';
				liveRegion.style.overflow = 'hidden';
				document.body.appendChild(liveRegion);
			}
		}

		/**
		 * Setup modal accessibility
		 */
		setupModalAccessibility() {
			const modalTriggers = document.querySelectorAll(
				'[data-modal-target], .modal-trigger'
			);

			modalTriggers.forEach((trigger) => {
				trigger.addEventListener('click', (e) => {
					e.preventDefault();
					const targetId =
						trigger.getAttribute('data-modal-target') ||
						trigger.getAttribute('href');
					const modal = document.querySelector(targetId);

					if (modal) {
						this.openModal(modal, trigger);
					}
				});
			});

			// Close modal buttons
			const closeButtons = document.querySelectorAll(
				'.modal-close, [data-modal-close]'
			);
			closeButtons.forEach((button) => {
				button.addEventListener('click', (e) => {
					e.preventDefault();
					const modal = button.closest('.modal, [role="dialog"]');
					if (modal) {
						this.closeModal(modal);
					}
				});
			});
		}

		/**
		 * Open modal with accessibility features
		 */
		openModal(modal, trigger) {
			// Store the trigger element to return focus
			modal.setAttribute('data-trigger', trigger.id || 'trigger-' + Date.now());
			if (!trigger.id) {
				trigger.id = modal.getAttribute('data-trigger');
			}

			// Set ARIA attributes
			modal.setAttribute('role', 'dialog');
			modal.setAttribute('aria-modal', 'true');
			modal.style.display = 'block';

			// Focus first focusable element
			const firstFocusable = modal.querySelector(this.focusableElements);
			if (firstFocusable) {
				firstFocusable.focus();
			}

			// Add body class to prevent scrolling
			document.body.classList.add('modal-open');

			// Announce modal opening
			this.announce('Dialog opened');
		}

		/**
		 * Close modal and restore focus
		 */
		closeModal(modal) {
			modal.style.display = 'none';
			modal.removeAttribute('aria-modal');

			// Restore focus to trigger element
			const triggerId = modal.getAttribute('data-trigger');
			const trigger = document.getElementById(triggerId);
			if (trigger) {
				trigger.focus();
			}

			// Remove body class
			document.body.classList.remove('modal-open');

			// Announce modal closing
			this.announce('Dialog closed');
		}

		/**
		 * Setup form accessibility
		 */
		setupFormAccessibility() {
			const forms = document.querySelectorAll('form');

			forms.forEach((form) => {
				// Add form validation feedback
				form.addEventListener('submit', (e) => {
					const invalidFields = form.querySelectorAll(':invalid');

					if (invalidFields.length > 0) {
						e.preventDefault();
						this.handleFormErrors(invalidFields);
					}
				});

				// Real-time validation feedback
				const inputs = form.querySelectorAll('input, textarea, select');
				inputs.forEach((input) => {
					input.addEventListener('blur', () => {
						this.validateField(input);
					});
				});
			});
		}

		/**
		 * Handle form errors with accessibility
		 */
		handleFormErrors(invalidFields) {
			// Focus first invalid field
			invalidFields[0].focus();

			// Announce error count
			this.announce(
				`Form has ${invalidFields.length} error${
					invalidFields.length !== 1 ? 's' : ''
				}`
			);

			// Add error styling and ARIA attributes
			invalidFields.forEach((field, index) => {
				field.setAttribute('aria-invalid', 'true');

				// Add error message if doesn't exist
				if (!field.getAttribute('aria-describedby')) {
					const errorId = 'error-' + field.name + '-' + index;
					const errorMessage = document.createElement('span');
					errorMessage.id = errorId;
					errorMessage.className = 'error-message';
					errorMessage.setAttribute('role', 'alert');
					errorMessage.textContent = this.getFieldErrorMessage(field);

					field.setAttribute('aria-describedby', errorId);
					field.parentNode.insertBefore(errorMessage, field.nextSibling);
				}
			});
		}

		/**
		 * Validate individual field
		 */
		validateField(field) {
			if (field.validity.valid) {
				field.removeAttribute('aria-invalid');
				const errorElement = document.getElementById(
					field.getAttribute('aria-describedby')
				);
				if (errorElement && errorElement.classList.contains('error-message')) {
					errorElement.remove();
					field.removeAttribute('aria-describedby');
				}
			} else {
				field.setAttribute('aria-invalid', 'true');
			}
		}

		/**
		 * Get error message for field
		 */
		getFieldErrorMessage(field) {
			if (field.validity.valueMissing) {
				return 'This field is required.';
			}
			if (field.validity.typeMismatch) {
				return 'Please enter a valid ' + field.type + '.';
			}
			if (field.validity.patternMismatch) {
				return 'Please match the requested format.';
			}
			return 'Please correct this field.';
		}

		/**
		 * Setup menu accessibility
		 */
		setupMenuAccessibility() {
			const menuToggles = document.querySelectorAll(
				'.menu-toggle, .mobile-menu-toggle'
			);

			menuToggles.forEach((toggle) => {
				toggle.addEventListener('click', () => {
					const menu = document.querySelector(
						toggle.getAttribute('aria-controls') || '.mobile-menu'
					);
					if (menu) {
						const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
						toggle.setAttribute('aria-expanded', !isExpanded);
						menu.style.display = isExpanded ? 'none' : 'block';

						if (!isExpanded) {
							const firstMenuItem = menu.querySelector('a, button');
							if (firstMenuItem) {
								firstMenuItem.focus();
							}
						}
					}
				});
			});
		}

		/**
		 * Close menu
		 */
		closeMenu(menu) {
			const toggle = document.querySelector(`[aria-controls="${menu.id}"]`);
			if (toggle) {
				toggle.setAttribute('aria-expanded', 'false');
				menu.style.display = 'none';
				toggle.focus();
			}
		}

		/**
		 * Announce message to screen readers
		 */
		announce(message) {
			const liveRegion = document.getElementById('aria-live-region');
			if (liveRegion) {
				liveRegion.textContent = message;

				// Clear after announcement
				setTimeout(() => {
					liveRegion.textContent = '';
				}, 1000);
			}
		}
	}

	/**
	 * Color Contrast Checker
	 */
	class ColorContrastChecker {
		constructor() {
			this.init();
		}

		init() {
			// Only run in debug mode
			if (window.location.search.includes('debug=accessibility')) {
				this.checkContrast();
			}
		}

		checkContrast() {
			const elements = document.querySelectorAll('*');
			const issues = [];

			elements.forEach((element) => {
				const styles = window.getComputedStyle(element);
				const color = styles.color;
				const backgroundColor = styles.backgroundColor;

				if (
					color &&
					backgroundColor &&
					color !== 'rgba(0, 0, 0, 0)' &&
					backgroundColor !== 'rgba(0, 0, 0, 0)'
				) {
					const contrast = this.calculateContrast(color, backgroundColor);

					if (contrast < 4.5) {
						// WCAG AA standard
						issues.push({
							element: element,
							contrast: contrast,
							color: color,
							backgroundColor: backgroundColor,
						});
					}
				}
			});

			if (issues.length > 0) {
				console.warn(
					'Accessibility: Found ' + issues.length + ' color contrast issues:',
					issues
				);
			}
		}

		calculateContrast(color1, color2) {
			// Simplified contrast calculation
			// In a real implementation, you'd use proper color space conversion
			return 4.5; // Placeholder
		}
	}

	/**
	 * Initialize when DOM is ready
	 */
	function init() {
		new AccessibilityManager();
		new ColorContrastChecker();
	}

	// Initialize
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// Expose utilities to global scope
	window.initoAccessibility = {
		AccessibilityManager,
		ColorContrastChecker,
	};
})();
