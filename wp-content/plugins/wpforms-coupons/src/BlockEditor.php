<?php

namespace WPFormsCoupons;

/**
 * Integration with Block Editor.
 *
 * @since 1.0.0
 */
class BlockEditor {

	/**
	 * Handle name for wp_register_styles handle.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const HANDLE = 'wpforms-coupons';

	/**
	 * Indicate if is allowed to load.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return wpforms_is_editor_page();
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( ! $this->allow_load() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		// Set editor style for block type editor. Must run at 20 in add-ons.
		add_filter( 'register_block_type_args', [ $this, 'register_block_type_args' ], 20, 2 );
	}


	/**
	 * Set editor style for block type editor.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args       Array of arguments for registering a block type.
	 * @param string $block_type Block type name including namespace.
	 */
	public function register_block_type_args( $args, $block_type ) {

		if ( $block_type !== 'wpforms/form-selector' ) {
			return $args;
		}

		$min = wpforms_get_min_suffix();

		// CSS.
		wp_register_style(
			self::HANDLE,
			WPFORMS_COUPONS_URL . "assets/css/main$min.css",
			[ $args['editor_style'] ],
			WPFORMS_COUPONS_VERSION
		);

		$args['editor_style'] = self::HANDLE;

		return $args;
	}

	/**
	 * Load enqueues for the Gutenberg editor.
	 *
	 * @since 1.0.0
	 * @deprecated 1.2.0
	 */
	public function gutenberg_enqueues() {

		_deprecated_function( __METHOD__, '1.2.0 of the WPForms Coupons addon.' );

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			self::HANDLE,
			WPFORMS_COUPONS_URL . "assets/css/main$min.css",
			[],
			WPFORMS_COUPONS_VERSION
		);
	}

	/**
	 * Load enqueues for Divi.
	 *
	 * @since 1.0.0
	 * @deprecated 1.3.1
	 */
	public function divi_enqueue_styles() {

		_deprecated_function( __METHOD__, '1.3.1 of the WPForms Coupons addon.', 'WPFormsCoupons\Integrations\Divi::builder_styles()' );

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['et_fb'] ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			self::HANDLE . '-integrations',
			WPFORMS_COUPONS_URL . "assets/css/integrations/main$min.css",
			[],
			WPFORMS_COUPONS_VERSION
		);
	}
}
