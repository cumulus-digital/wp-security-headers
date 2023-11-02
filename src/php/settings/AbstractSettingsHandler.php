<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Settings;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

/**
 * Base class for settings handlers.
 */
abstract class AbstractSettingsHandler {
	protected $tab;

	protected $section;

	/**
	 * Reports if this settings section should be active.
	 *
	 * @return bool
	 */
	public function isActive() {
	}

	/**
	 * Return the formatted WPSF section ID for this handler.
	 *
	 * @return string
	 */
	public function getSectionId() {
		$section = $this->section;

		if ( $this->tab ) {
			$section = $this->tab . '_' . $this->section;
		}

		return $section;
	}

	/**
	 * Sets the default value for a settings field in this handler.
	 *
	 * @param string $field
	 * @param mixed  $default
	 */
	public function setDefault( $field, $default ) {
		SettingsRegister::setDefault(
			$this->getSectionId(),
			$field,
			$default
		);
	}

	/**
	 * Set defaults for multiple fields.
	 *
	 * @param array<{ id: mixed }> $fields
	 */
	public function setDefaults( $fields ) {
		foreach ( $fields as $field => $default ) {
			$this->setDefault( $field, $default );
		}
	}

	/**
	 * Retrieve the default value for a settings field in this handler.
	 *
	 * @param string $field
	 *
	 * @return mixed
	 */
	public function getDefault( $field ) {
		$default = SettingsRegister::getDefault(
			$this->getSectionId(),
			$field
		);

		if ( \is_callable( $default ) ) {
			return $default( $field );
		}

		return $default;
	}

	/**
	 * Retrieve the current value of a field.
	 *
	 * @param string $field
	 *
	 * @return mixed
	 */
	public function getSetting( $field ) {
		return SettingsRegister::getSetting( $this->getSectionId(), $field );
	}

	/**
	 * Performs validation on submit for this handler.
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public function validate( $input ) {
		return $this->runValidations( array(), $input );
	}

	/**
	 * Allows for validations of this handler's settings
	 * without having to repeatedly specify the section ID.
	 *
	 * @param array<array{ id: string, callback: function }> $validations
	 * @param mixed                                          $input
	 *
	 * @return array
	 */
	public function runValidations( $validations, $input ) {
		foreach ( $validations as $validate ) {
			if ( \is_array( $validate ) && ! \array_key_exists( 'id', $validate ) ) {
				\trigger_error( 'Validations must supply a setting id!', \E_USER_WARNING );

				return;
			}

			if ( \is_array( $validate ) && ! \array_key_exists( 'callback', $validate ) ) {
				\trigger_error( 'Validation supplied without a callback', \E_USER_WARNING );

				return;
			}

			$original_key = $this->getSectionId() . '_' . $validate['id'];

			if (
				\is_array( $input )
				&& \array_key_exists( $original_key, $input )
				&& \is_callable( $validate['callback'] )
			) {
				$input[$original_key] = $validate['callback']( $input[$original_key] );
			}
		}

		return $input;
	}
}
