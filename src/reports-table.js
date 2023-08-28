import { createApp, ref } from 'vue';
import ReportsTable from './js/reports-table.vue';
import Vue3EasyDataTable from 'vue3-easy-data-table';
import './scss/reports-table.scss';
import 'vue3-easy-data-table/dist/style.css';

(function (window) {
	let is_init = false;
	const tablink = document.querySelector(
		'.wpsf-nav__item-link[href="#tab-reports"]'
	);
	if (tablink) {
		if (
			window.localStorage.getItem('cmls_wpsh_wpsf_tab_id') ===
			'#tab-reports'
		) {
			initApp();
		}
		tablink.parentNode.addEventListener('click', (e) => {
			initApp();
		});
	}

	function initApp() {
		if (is_init) {
			return;
		}

		const place = document.querySelector(
			//'.wpsf-section-description--dashboard'
			'.wpsf-tab--reports .postbox'
		);
		if (place) {
			/*
			console.log('creating reports table');
			const parent = document.createElement('div');
			parent.className = 'reports-table-container';
			const holder = document.createElement('div');
			const shadow = place.attachShadow({ mode: 'open' });

			window.cmls_wpsh_ajax.shadow_styles.forEach((s) => {
				let link = document.createElement('link');
				link.setAttribute('rel', 'stylesheet');
				link.setAttribute('href', s);
				parent.appendChild(link);
			});
			parent.appendChild(holder);
			shadow.appendChild(parent);
			*/

			place.attachShadow({ mode: 'open' });

			const app = createApp(ReportsTable);
			app.component('EasyDataTable', Vue3EasyDataTable);

			app.mount(place.shadowRoot);
			is_init = true;
		} else {
			console.log('where did our element go?');
		}
	}
})(window, undefined);
