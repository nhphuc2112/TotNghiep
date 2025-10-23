<?php

namespace WPFormsCoupons\Integrations;

/**
 * Divi integrations class.
 *
 * @since 1.3.1
 */
class Divi implements IntegrationInterface {

	/**
	 * Check if styles should be loaded.
	 *
	 * @since 1.3.1
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		// Do not include styles if the "Include Form Styling > No Styles" is set.
		if ( wpforms_setting( 'disable-css', '1' ) === '3' ) {
			return false;
		}

		return $this->is_plugin_active() || $this->is_builder();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.3.1
	 */
	public function hooks() {

		add_action( 'wpforms_frontend_css', [ $this, 'builder_styles' ], 12 );
	}

	/**
	 * Enqueue builder styles.
	 *
	 * @since 1.3.1
	 */
	public function builder_styles() {

		// Check if the default addon stylesheet is enqueued.
		if ( ! wp_style_is( 'wpforms-coupons' ) ) {
			return; // Return early if the stylesheet is not enqueued.
		}

		// Check if plugin is active and the Divi Builder is not used.
		if ( ! et_pb_is_pagebuilder_used() ) {
			return;
		}

		// Get the minified suffix for the assets.
		$min = wpforms_get_min_suffix();

		// Dequeue the default WPForms builder stylesheet to avoid duplication.
		wp_dequeue_style( 'wpforms-coupons' );

		// Enqueue the customized Divi builder stylesheet with increased specificity.
		wp_enqueue_style(
			'wpforms-coupons-integrations-divi',
			WPFORMS_COUPONS_URL . "assets/css/integrations/main{$min}.css",
			[],
			WPFORMS_COUPONS_VERSION
		);
	}

	/**
	 * Check if the current page is opened in the Divi Builder.
	 *
	 * Method should be reconsidered once the minimum core version is raised to 1.9.4.
	 *
	 * @since 1.3.1
	 *
	 * @return bool
	 */
	private function is_builder(): bool {

		if ( function_exists( 'wpforms_is_divi_editor' ) ) {
			return wpforms_is_divi_editor();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		return ! empty( $_GET['et_fb'] ) || ( ! empty( $_POST['action'] ) && $_POST['action'] === 'wpforms_divi_preview' );
	}

	/**
	 * Check if the Divi Builder plugin is active.
	 *
	 * @since 1.3.1
	 *
	 * @return bool
	 */
	private function is_plugin_active(): bool {

		return function_exists( 'et_divi_builder_init_plugin' );
	}
}
