<?php

namespace WPFormsCoupons\Admin\Coupons;

/**
 * Coupon Overview Screen Options class.
 *
 * @since 1.0.0
 */
class ScreenOptions {

	/**
	 * Screen id.
	 *
	 * @since 1.0.0
	 */
	const SCREEN_ID = 'wpforms_page_wpforms-payments';

	/**
	 * Screen option name.
	 *
	 * @since 1.0.0
	 */
	const PER_PAGE = 'wpforms_payments_per_page';

	/**
	 * Page view.
	 *
	 * @since 1.0.0
	 */
	const VIEW = 'coupons';

	/**
	 * Current view.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $view;


	/**
	 * ScreenOptions constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->view = ! empty( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : '';
	}

	/**
	 * Define hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'load-wpforms_page_wpforms-payments', [ $this, 'screen_options' ] );
		add_filter( 'set_screen_option_wpforms_coupons_per_page', [ $this, 'screen_options_set' ], 10, 3 );
	}

	/**
	 * Add per-page screen option to the Coupons Overview table.
	 *
	 * @since 1.0.0
	 */
	public function screen_options() {

		$screen = get_current_screen();

		if ( ! isset( $screen->id ) || $screen->id !== self::SCREEN_ID || $this->view !== self::VIEW ) {
			return;
		}

		add_screen_option(
			'per_page',
			[
				'label'   => esc_html__( 'Number of coupons per page:', 'wpforms-coupons' ),
				'option'  => 'wpforms_coupons_per_page',
				'default' => 20,
			]
		);
	}

	/**
	 * Coupons table per-page screen option value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $status The value to save instead of the option value.
	 * @param string $option Screen option name.
	 * @param mixed  $value  Screen option value.
	 *
	 * @return mixed
	 */
	public function screen_options_set( $status, $option, $value ) {

		return $value;
	}
}
