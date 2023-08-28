import jQuery from 'jquery';

/**
 * Watch changes on the policies. If they contain 'none' or 'unsafe-inline',
 * we add a warning to the toggle for auto-nonce.
 */
(function ($, window, undefined) {
	const auto_nonce_directives = $('input[id^="csp_auto-nonce_directives-"]');

	const updateLabel = ($label, html) => {
		let $warning = $label.parent().find('span.desc');
		if (!$warning?.length) {
			$warning = $label.after('<span class="desc"></span>');
		}
		$warning.html(
			`<strong class="error-message">Warning!</strong> ${html}`
		);
	};

	$(() => {
		$(document).on('keyup', 'input[name$="[policy]"]', function () {
			const matchPolicy = this.id.match(
				/csp_policies\_([a-z\-]+)\-policy/
			);
			if (matchPolicy?.length > 1) {
				const policy = matchPolicy[1];
				const $toggle = auto_nonce_directives.filter(
					`#csp_auto-nonce_directives-${policy}`
				);
				if ($toggle?.length) {
					const warnings = [];
					if (this.value.includes("'none'")) {
						warnings.push("'none'");
					}

					if (this.value.includes("'unsafe-inline'")) {
						warnings.push("'unsafe-inline'");
					}

					if (warnings.length) {
						updateLabel(
							$toggle.parent(),
							`<a href="#csp_policies_${policy}">Policy</a> contains
								${warnings.join(' and ')}`
						);
					}
				}
			}
		});

		setTimeout(() => {
			$('.wpsf-button-submit')
				.off('click')
				.on('click', function () {
					$(
						'.wpsf-settings__content > form > p.submit > input[type="submit"]'
					).trigger('click');
				});
		}, 500);
	});
})(jQuery);
