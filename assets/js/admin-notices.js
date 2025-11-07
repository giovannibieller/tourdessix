/**
 * Admin Notices JavaScript
 * Handles dismissible admin notices with permanent dismissal
 */

jQuery(document).ready(function ($) {
	// Handle permanent dismissal of accessibility notice
	$(document).on('click', '.inito-dismiss-forever', function (e) {
		e.preventDefault();

		var $notice = $(this).closest('.notice');
		var noticeType = $(this).data('notice');

		// Show loading state
		$(this).prop('disabled', true);
		$notice.fadeTo(100, 0.5);

		// Send AJAX request
		$.ajax({
			url: initoAdmin.ajax_url,
			type: 'POST',
			data: {
				action: 'inito_dismiss_accessibility_notice',
				notice: noticeType,
				nonce: initoAdmin.nonce,
			},
			success: function (response) {
				if (response.success) {
					// Remove notice with fade effect
					$notice.fadeOut(300, function () {
						$(this).remove();
					});
				} else {
					// Show error and restore notice
					alert(initoAdmin.strings.error);
					$notice.fadeTo(100, 1);
					$(this).prop('disabled', false);
				}
			},
			error: function () {
				// Show error and restore notice
				alert(initoAdmin.strings.error);
				$notice.fadeTo(100, 1);
				$(this).prop('disabled', false);
			},
		});
	});

	// Handle standard WordPress dismissible notices (temporary dismissal)
	$(document).on(
		'click',
		'.notice-dismiss:not(.inito-dismiss-forever)',
		function () {
			// This handles the default WordPress notice dismissal behavior
			// No additional action needed as WordPress handles this natively
		}
	);
});
