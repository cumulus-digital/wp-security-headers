<template>
	<link rel="stylesheet" v-for="style in shadowStyles" v-bind:href="style">
	<div class="functions">
		<button v-on:click="loadFromServer" class="refresh">&#8635; Refresh</button>
		<div>
			Filter Directives:
			<Multiselect
				v-model="effectiveDirective"
				:options="directiveOptions"
				mode="tags"
				:close-on-select="false"
				placeholder="Showing all"
			/>
		</div>
		<div v-if="statusMessage">{{ statusMessage }}</div>
	</div>
	<EasyDataTable
		v-model:server-options="serverOptions"
		:server-items-length="serverItemsLength"
		:loading="loading"
		:headers="headers"
		:items="items"
		buttons-pagination
		:rows-items=[15,25,50]
		alternating
		must-sort
	>
		<template #expand="item">
			<div class="uagent">
				<strong>User Agent:</strong> {{ item.user_agent }}
			</div>
			<div class="raw-report">
				<pre>{{ item.full_report }}</pre>
			</div>
		</template>
		<template #empty-message>
			{{errorMessage}}
		</template>

	</EasyDataTable>
	<div class="functions secondary">
		<button v-on:click="loadFromServer" class="refresh">&#8635; Refresh</button>
		<button v-on:click="flushServer" class="flush-reports">&#x1f5d1; Flush Reports</button>
	</div>
</template>
<script>
import { defineComponent, ref, computed, watch, onBeforeUnmount } from "vue";
import Multiselect from '@vueform/multiselect';
import { throttle } from 'lodash-es';

export default defineComponent({
	components: { Multiselect },
	setup() {
		const headers = ref([
			{ text: 'Received', value: 'created_at', sortable: true },
			{ text: 'Directive', value: 'violated_directive', sortable: true },
			{ text: 'Document', value: 'document_uri', sortable: true },
			{ text: 'Status', value: 'status_code', sortable: true },
			{ text: 'Blocked URI', value: 'blocked_uri' },
			{ text: 'Source File', value: 'source_file' },
			{ text: 'Line', value: 'line_number' },
		]);
		const items = ref([]);
		const serverItemsLength = ref(0);

		const effectiveDirective = ref([]);
		const directiveOptions = {
			'base-uri': 'base-uri',
			'child-src': 'child-src',
			'connect-src': 'connect-src',
			'default-src': 'default-src',
			'font-src': 'font-src',
			'frame-action': 'frame-action',
			'frame-ancestors': 'frame-ancestors',
			'frame-src': 'frame-src',
			'img-src': 'img-src',
			'manifest-src': 'manifest-src',
			'media-src': 'media-src',
			'object-src': 'object-src',
			'sandbox': 'sandbox',
			'script-src': 'script-src*',
			'style-src': 'style-src*',
			'worker-src': 'worker-src',
		};

		const serverOptions = ref({
			page: 1,
			rowsPerPage: 15,
			sortBy: 'created_at',
			sortType: 'desc',
		});

		const restApiUrl = computed(() => {
			const { page, rowsPerPage, sortBy, sortType } = serverOptions.value;
			let config = window.cmls_wpsh_ajax;
			const vars = new URLSearchParams({
				action: config.actions.get,
				p: page,
				pp: rowsPerPage,
				s: sortBy,
				o: sortType,
				d: effectiveDirective.value.join(',')
			});
			return `${config.url}?${vars.toString()}`;
		});

		const shadowStyles = computed(() => {
			return window.cmls_wpsh_ajax.shadow_styles || [];
		});

		const statusMessage = ref('');

		const errorMessage = ref('');

		const loading = ref(false);

		const loadFromServer = async (e) => {
			if(e?.preventDefault) e.preventDefault();
			loading.value = true;
			try {
				const response = await fetch(
					restApiUrl.value,
					{
						method: 'GET',
						headers: new Headers({
							'Accept': 'application/json'
						})
					}
				);
				const { success, data } = await response.json();
				if (!response.ok) {
					throw new Error(data.error);
				}
				if (!success || data.error) {
					errorMessage.value = data.error;
					serverItemsLength.value = 0;
					items.value = [];
				} else {
					items.value = data.items;
					serverItemsLength.value = data.total;
					if (data.message) {
						statusMessage.value = data.message;
					}
				}
			} catch (error) {
				console.log(error);
				errorMessage.value = error.message;
				serverItemsLength.value = 0;
			}
			loading.value = false;
		};
		loadFromServer();

		const throttleWatch = throttle(
			loadFromServer,
			1000,
			{ leading: false, trailing: true }
		);
		watch(
			effectiveDirective,
			throttleWatch,
			{ deep: true }
		);
		onBeforeUnmount(() => {
			throttleWatch.cancel();
		});
		watch(
			serverOptions,
			loadFromServer,
			{ deep: true }
		);

		const flushServer = async () => {
			if (confirm('This will delete all reports, are you sure?')) {
				loading.value = true;
				try {
					const response = await fetch(
						window.cmls_wpsh_ajax.url + '?action=' + window.cmls_wpsh_ajax.actions.flush,
						{
							method: 'DELETE',
							headers: new Headers({
								'Accept': 'application/json'
							})
						}
					);
					await loadFromServer();
				} catch (error) {
					errorMessage.value = error.message;
				}
				loading.value = false;
			}
		};

		return {
			shadowStyles,
			headers,
			items,
			serverOptions,
			effectiveDirective,
			directiveOptions,
			serverItemsLength,
			restApiUrl,
			errorMessage,
			statusMessage,
			loading,
			loadFromServer,
			flushServer
		};
	}
});
</script>