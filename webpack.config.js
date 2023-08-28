const webpack = require('webpack');
const path = require('path');
let defaultConfig = require('./node_modules/@wordpress/scripts/config/webpack.config.js');

const { VueLoaderPlugin } = require('vue-loader');
defaultConfig.plugins.push(new VueLoaderPlugin());
defaultConfig.module.rules.push({
	test: /\.vue$/,
	loader: 'vue-loader',
});
defaultConfig.plugins.push(
	new webpack.DefinePlugin({
		__VUE_OPTIONS_API__: false,
		__VUE_PROD_DEVTOOLS__: false,
	})
);

/*
defaultConfig.module.rules.push({
	test: /vue3\-easy\-data\-table\/dist\/style\.css/,
	loader: 'string-replace-loader',
	options: {
		search: ':root',
		replace: ':host',
		flags: 'g',
	},
});
*/

//defaultConfig.resolve.alias.vue = 'vue/dist/vue.runtime.esm-browser.prod.js';
module.exports = {
	...defaultConfig,
	entry: {
		safe_inline_styles: path.resolve(
			process.cwd(),
			'src',
			'safe_inline_styles.js'
		),
		backend: path.resolve(process.cwd(), 'src', 'backend.js'),
		'reports-table': path.resolve(process.cwd(), 'src', 'reports-table.js'),
	},
};
