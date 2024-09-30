<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Settings;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CWSH_WordpressSettingsFramework;

/**
 * Overrides jamesckemp/wordpress-settings-framework class to do some
 * custom things for form fields.
 */
class Framework extends CWSH_WordpressSettingsFramework {
	/**
		 * Output raw HTML.
		 *
		 * @param array $args Add html to output to 'html' key
		 */
	public function generate_html_field( $args ) {
		if ( \is_array( $args ) && \array_key_exists( 'html', $args ) ) {
			if ( \array_key_exists( 'raw', $args ) && true === $args['raw'] ) {
				echo $args['html'];
			} else {
				?>
				<div class="html-field <?php echo \esc_attr( $args['class'] ); ?>">
					<?php echo \wp_kses_post( $args['html'] ); ?>
				</div>
				<?php
			}
		}
	}

	/**
	 * Generate: Text field.
	 *
	 * @param array       $args field arguments
	 * @param string|null $type type of text field
	 */
	public function generate_text_field( $args, $type = null ) {
		$args['value'] = \esc_attr( \stripslashes( $args['value'] ) );

		?>
		<input
			type="<?php echo \esc_attr( $type ?? 'text' ); ?>"
			name="<?php echo \esc_attr( $args['name'] ); ?>"
			id="<?php echo \esc_attr( $args['id'] ); ?>"
			value="<?php echo $args['value']; ?>"
			placeholder="<?php echo \esc_attr( $args['placeholder'] ); ?>"
			class="regular-text <?php echo \esc_attr( $args['class'] ); ?>"
			<?php if ( \is_array( $args ) && \array_key_exists( 'attributes', $args ) ): ?>
				<?php foreach( $args['attributes'] as $attr => $aval ): ?>
					<?php echo \esc_html( $attr ); ?>="<?php echo \esc_attr( $aval ); ?>"
				<?php endforeach; ?>
			<?php endif; ?>
		>
		<?php

		$this->generate_description( $args );
	}

	/**
	 * Generate: URL field.
	 *
	 * @param array $args field arguments
	 */
	public function generate_url_field( $args ) {
		$this->generate_text_field( $args, 'url' );
	}

	/**
	 * Generate: Multi-checkboxes field.
	 *
	 * @param array $args field arguments
	 */
	public function generate_checkboxes_field( $args ) {
		echo '<input type="hidden" name="' . \esc_attr( $args['name'] ) . '" value="0" />';

		echo '<ul class="wpsf-list wpsf-list--checkboxes">';

		foreach ( $args['choices'] as $value => $text ) {
			$checked  = ( \is_array( $args['value'] ) && \in_array( \strval( $value ), \array_map( 'strval', $args['value'] ), true ) ) ? 'checked="checked"' : '';
			$field_id = \sprintf( '%s_%s', $args['id'], $value );

			echo '<li>';
			$this->generate_checkbox_field( array(
				'value'         => $value,
				'checked'       => $checked,
				'name'          => $args['name'] . '[]',
				'id'            => $field_id,
				'class'         => $args['class'],
				'desc'          => $text,
				'unsafe_labels' => \array_key_exists( 'unsafe_labels', $args ) ? \wp_kses_post( $args['unsafe_labels'] ) : false,
			) );
			echo '</li>';
		}

		echo '</ul>';

		$this->generate_description( $args );
	}

	/**
	 * Generate: Single checkbox field.
	 *
	 * @param array $args field arguments
	 */
	public function generate_checkbox_field( $args ) {
		$args['value'] = \esc_attr( \stripslashes( $args['value'] ) );

		if ( \in_array( 'checked', $args ) ) {
			$checked = $args['checked'];
		} else {
			$checked = ( $args['value'] ) ? 'checked="checked"' : '';
		}

		?>
			<input type="hidden" name="<?php echo \esc_attr( $args['name'] ); ?>" value="0" />
			<label>
				<input
					type="checkbox"
					name="<?php echo \esc_attr( $args['name'] ); ?>"
					id="<?php echo \esc_attr( $args['id'] ); ?>"
					value="1"
					class="<?php echo \esc_attr( $args['class'] ); ?>"
					<?php echo $checked; ?>
				>
				<?php echo \array_key_exists( 'unsafe_labels', $args ) && $args['unsafe_labels'] ? \wp_kses_post( $args['desc'] ) : \esc_html( $args['desc'] ); ?>
			</label>
		<?php
	}

	/**
	 * Generate: Group field
	 * Override allows setting classes and supports show_if/hide_if.
	 *
	 * @param array $args
	 */
	public function generate_group_field( $args ) {
		$value     = (array) $args['value'];
		$row_count = ( ! empty( $value ) ) ? \count( $value ) : 1;

		$args['class'] .= ' widefat wpsf-group';

		?>
		<table class="<?php echo \esc_attr( $args['class'] ); ?>" cellspacing="0">
			<tbody>
				<?php
				for ( $row = 0; $row < $row_count; $row++ ) {
					// @codingStandardsIgnoreStart
					echo $this->generate_group_row_template( $args, false, $row );
					// @codingStandardsIgnoreEnd
				}
		?>
			</tbody>
		</table>
		<script type="text/html" id="<?php echo \esc_attr( $args['id'] ); ?>_template">
				<?php echo $this->generate_group_row_template( $args, true ); ?>
		</script>
		<?php

		$this->generate_description( $args );
	}

	/**
	 * Generate: Toggle field
	 * override supports a better looking desc output.
	 *
	 * @param array $args
	 */
	public function generate_toggle_field( $args ) {
		$args['value'] = \esc_attr( \stripslashes( $args['value'] ) );
		$checked       = ( $args['value'] ) ? 'checked="checked"' : '';

		?>
			<input type="hidden" name="<?php echo \esc_attr( $args['name'] ); ?>" value="0" />
			<label class="switch">
				<input
					type="checkbox"
					name="<?php echo \esc_attr( $args['name'] ); ?>"
					id="<?php echo \esc_attr( $args['id'] ); ?>"
					value="1"
					class="<?php echo \esc_attr( $args['class'] ); ?>"
					<?php echo $checked; ?>
				>
				<span class="slider"></span>
			</label>
			<?php if ( isset( $args['desc'] ) ): ?>
				<span class="desc"><?php echo \wp_kses_post( $args['desc'] ); ?></span>
			<?php endif; ?>
		<?php
	}

	public function settings() {
		\ob_start();
		parent::settings();
		$settings = \ob_get_clean();
		echo \str_replace( 'novalidate', '', $settings );
	}
}
