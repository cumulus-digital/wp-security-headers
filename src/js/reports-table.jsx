import { h, Component, Fragment } from 'preact';
import { useState, useMemo, useEffect, useRef } from 'preact/hooks';
import register from 'preact-custom-element';
import { Grid } from 'gridjs';

let is_init = false;
const tablink = document.querySelector(
	'.wpsf-nav__item-link[href="#tab-reports"]'
);
if (tablink) {
	if (
		window.localStorage.getItem('cmls_wpsh_wpsf_tab_id') === '#tab-reports'
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

	const place = document.querySelector('.wpsf-tab--reports .postbox');
	if (place) {
		const reportsTableDiv = document.createElement('reports-table');
		place.append(reportsTableDiv);

		const ReportsTable = function (props) {
			const wrapperRef = useRef(null);

			const columns = [
				{ header: 'Received', field: 'created_at' },
				{ header: 'Directive', field: 'violated_directive' },
				{ header: 'Document', field: 'document_uri' },
				{ header: 'Status', field: 'status_code' },
				{ header: 'Blocked URI', field: 'blocked_uri' },
				{ header: 'Source File', field: 'source_file' },
				{ header: 'Line #', field: 'line_number' },
			];

			const [effectiveDirectives, setEffectiveDirectives] = useState([]);
			const [totalItems, setTotalItems] = useState(0);
			const [items, setItems] = useState([]);
			const [pageNumber, setPageNumber] = useState(1);
			const [rowsPerPage, setRowsPerPage] = useState(15);
			const [sortBy, setSortBy] = useState('created_at');
			const [sortDirection, setSortDirection] = useState('desc');
			const [isLoading, setIsLoading] = useState(false);
			const [errorMessage, setErrorMessage] = useState();

			const restApiUrl = useMemo(() => {
				let config = window.cmls_wpsh_ajax;
				const vars = new URLSearchParams({
					action: config.actions.get,
					p: pageNumber,
					pp: rowsPerPage,
					s: sortBy,
					o: sortDirection,
					d: effectiveDirectives.join(','),
				});
				return `${config.url}?${vars.toString()}`;
			}, [
				effectiveDirectives,
				pageNumber,
				rowsPerPage,
				sortBy,
				sortDirection,
			]);

			const loadFromServer = async () => {
				setIsLoading(true);
				try {
					const response = await fetch(restApiUrl, {
						method: 'GET',
						headers: new Headers({
							Accept: 'application/json',
						}),
					});
					if (!response.ok) {
						throw new Error(response.status);
					}
					const { data } = await response.json();
					if (data.error) {
						setErrorMessage(data.error);
						setTotalItems(0);
						setItems([]);
					} else {
						setItems(data.items);
						setTotalItems(data.total);
					}
				} catch (err) {
					setErrorMessage(err.message);
					setTotalItems(0);
				}
				setIsLoading(false);
			};
			//loadFromServer();

			const buildSubtable = (item) => {};

			const grid = new Grid({
				server: {
					url: restApiUrl,
					method: 'GET',
					headers: { Accept: 'application/json' },
					then: (data) => {
						console.log(data.data);
						return data.data.items.map((item) => [
							<i
								class="fa fa-chevron-right"
								onClick="buildSubtable(this)"
							>
								-
							</i>,
							item.created_at,
							item.violated_directive,
							item.document_uri,
							item.status_code,
							item.blocked_uri,
							item.source_file,
							item.line_number,
						]);
					},
					handle: (res) => {
						if (res.ok) {
							return res.json();
						}
						return { data: { items: [] } };
					},
				},
			});

			useEffect(() => {
				grid.render(wrapperRef.current);
			});

			return <div ref={wrapperRef}></div>;
		};

		register(ReportsTable, 'reports-table', [], { shadow: false });
	} else {
		console.warn('Could not find placement element for table!');
	}
}
