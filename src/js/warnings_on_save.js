/**
 * Warns user before saving an enabled CSP
 */

const form = document.querySelector('.wpsf-settings form');

const enabled = document.querySelector('#csp_mode_enabled');
const was_enabled = enabled?.value === 'enabled';

const in_admin = document.querySelector('#csp_mode_in-admin');
const was_in_admin_enabled = in_admin?.checked;

form.addEventListener('submit', (e) => {
	const warnings = [];

	if (!was_enabled && enabled?.value === 'enabled') {
		warnings.push(
			'Enabling a Content Security Policy may break your site!'
		);
	}

	if (
		enabled?.value === 'enabled' &&
		!was_in_admin_enabled &&
		in_admin.checked
	) {
		warnings.push(
			'Enforcing a Content Security Policy in the WordPress admin area may lock you out! Reversing this action may require database access.'
		);
	}

	if (warnings.length) {
		alert(
			'WARNING!\n\n' +
				warnings.join('\n\n') +
				'\n\nYou will be asked to confirm after this dialog'
		);
		if (!confirm('Are you sure you want to save these changes?')) {
			e.preventDefault();
			return false;
		}
	}
	window.cmlsSetUnsaved(false);
});
