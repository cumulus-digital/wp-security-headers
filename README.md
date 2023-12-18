# Wordpress Security Headers

Management of a few security-related HTTP headers including Content-Security-Policy. This is by no means a silver bullet.

Note that this plugin starts and flushes output buffers in some WordPress hooks when Auto-Nonce is active. There is a distinct possibility that this could go terribly wrong if another plugin or process does additional output buffering. See CSPAutoNonce::setupFilters in src/php/settings/csp/auto_nonce.php for details.

*This plugin is not licensed for use outside of Cumulus Media, and no warranty or support is offered. Use at your own risk.*

Controls are divided between Settings and Actions.

Settings are registered in the SettingsRegister, and return a class which extends AbstractSettingsHandler, a thin wrapper around James Kemp's Wordpress Settings Framework. Setings are found in `src/php/settings`

Actors are registered in ActionsRegister and extend AbstractActor. Actors are found in `src/php/actions`

By default, a few methods are called on Actors if they exist.
- filterHeaders: Called on the wp_headers hook, passed currently set HTTP headers.
- sendHeaders: Called on the send_headers hook.

Actors are otherwise free to register their own actions on any WP hook.

## Auto-Noncing

When CSP and auto-noncing is active, filters and a nonce shortcode become available.

### Filter
* `cmls_wpsh_filter_scripts` - Filters a given HTML fragment, applying nonces to tags where appropriate.

### Shortcodes

**Note:** WordPress *does not* support shortcodes within HTML attributes.

* `[cmls_wpsh_nonce]` - Outputs the nonce generated for the current request
* `[cmls_wpsh_tag tag="link"]...[/cmls_wpsh_tag]` - Generic tag shortcode, allowing for the output of any HTML tag with nonce including attributes and content. If no content and closing shortcode is provided, only the opening tag will be generated and it will be up to you to close it. Be aware that noncing applies only to a limited number of HTML tags.
* `[cmls_wpsh_script]...[/cmls_wpsh_script]` - Alias for `[cmls_wpsh_tag tag="script"]`
