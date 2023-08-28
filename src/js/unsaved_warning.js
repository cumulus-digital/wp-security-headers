/**
 * Track changes and issue a warning before window unloads
 */
let unsaved = false;

window.cmlsSetUnsaved = (state) => {
	unsaved = state;
};
window.addEventListener('change', (e) => {
	if (e.target.matches('input, textarea, select')) {
		let defaultValue = e.target.defaultValue,
			currentValue = e.target.value;
		if (e.target.matches('select')) {
			const options = e.target.querySelectorAll('option');
			if (options) {
				options.forEach((option) => {
					if (option.defaultSelected) {
						defaultValue = option.value;
					}
				});
			}
		}
		if (e.target.matches('input[type="checkbox"]')) {
			defaultValue = !!e.target.getAttribute('checked') ? 1 : 0;
			currentValue = !!e.target.checked ? 1 : 0;
		}
		if (currentValue !== defaultValue) {
			cmlsSetUnsaved(true);
		}
	}
});
window.addEventListener('beforeunload', (e) => {
	if (unsaved) {
		e.preventDefault();
		e.returnValue = 'string';
		return 'string';
	}
});
