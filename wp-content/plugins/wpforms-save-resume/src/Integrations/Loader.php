<?php

namespace WPFormsSaveResume\Integrations;

/**
 * Main loader class for integrating with different themes and plugins.
 *
 * @since 1.11.0
 */
class Loader {

	/**
	 * List of integration class names to register.
	 *
	 * @since 1.11.0
	 *
	 * @var array
	 */
	private $class_names = [ 'Divi' ];

	/**
	 * Initialize the integrations.
	 *
	 * @since 1.11.0
	 */
	public function init() {

		// Loop through each class name and register/load the integration.
		foreach ( $this->class_names as $class_name ) {
			$integration = $this->register_class( $class_name );

			// If integration exists, load it.
			if ( $integration ) {
				$this->load_integration( $integration );
			}
		}
	}

	/**
	 * Register a new class.
	 *
	 * @since 1.11.0
	 *
	 * @param string $class_name Class name to register.
	 *
	 * @return IntegrationInterface|null Instance of class or null if class doesn't exist.
	 */
	private function register_class( string $class_name ) {

		// Construct the full class name.
		$class_name = 'WPFormsSaveResume\Integrations\\' . sanitize_text_field( $class_name );

		// Check if the class exists and return an instance of it.
		return class_exists( $class_name ) ? new $class_name() : null;
	}

	/**
	 * Load an integration.
	 *
	 * @param IntegrationInterface $integration Instance of an integration class.
	 *
	 * @since 1.11.0
	 */
	private function load_integration( IntegrationInterface $integration ) {

		// Check if the integration should be loaded.
		if ( ! $integration->allow_load() ) {
			return;
		}

		// Hook into the integration.
		$integration->hooks();
	}
}
