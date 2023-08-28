/**
 * Lets wpsf groups order by draggable
 */

((window, undefined) => {
	window.document.addEventListener('mouseover', (e) => {
		if (
			!e.target.matches(
				'.wpsf-group__row-index, .wpsf-group__row-index *'
			)
		) {
			return;
		}
		e.target
			.closest('.wpsf-group__row-index')
			.setAttribute('title', 'Click and drag to reorder');
	});
	window.document.addEventListener('mousedown', (e) => {
		if (
			!e.target.matches(
				'.wpsf-group__row-index, .wpsf-group__row-index *'
			)
		) {
			return;
		}
		e.target.closest('.wpsf-group__row').setAttribute('draggable', true);
	});

	let action_row = null;
	const drag_image = new Image();
	drag_image.src =
		'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
	document.body.appendChild(drag_image);
	let action_group = null;

	window.document.addEventListener('dragstart', (e) => {
		if (!e.target.matches('.wpsf-group__row')) {
			return;
		}

		action_row = e.target;

		e.dataTransfer.setDragImage(drag_image, 0, 0);
		e.dataTransfer.dropEffect = 'move';
		e.dataTransfer.effectAllowed = 'move';

		action_group = e.target.closest('.wpsf-group');
	});
	window.document.addEventListener('dragend', (e) => {
		if (e.target.matches('.wpsf-group__row, .wpsf-group__rot *')) {
			e.target
				.closest('.wpsf-group__row')
				.setAttribute('draggable', false);
		}
	});
	window.document.addEventListener('dragover', (e) => {
		if (!action_group.contains(e.target)) {
			return;
		}
		if (!e.target.matches('.wpsf-group tr, .wpsf-group tr *')) {
			return;
		}
		e.preventDefault();

		let table_rows = Array.prototype.slice.call(
			e.target.closest('.wpsf-group').querySelector('tr').parentNode
				.children
		);

		let target_row = e.target.closest('.wpsf-group__row');

		if (table_rows.indexOf(target_row) > table_rows.indexOf(action_row)) {
			target_row.after(action_row);
		} else {
			target_row.before(action_row);
		}
	});
})(window.self);
