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