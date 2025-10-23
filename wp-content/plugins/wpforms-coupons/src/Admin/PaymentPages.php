<?php

namespace WPFormsCoupons\Admin;

// phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use WPFormsCoupons\Coupon;
use WPFormsCoupons\Admin\Coupons\Edit;
use WPFormsCoupons\Admin\Coupons\Overview;

/**
 * Payment pages class.
 *
 * @since 1.0.0
 */
class PaymentPages {

	/**
	 * Edit coupon page.
	 *
	 * @since 1.0.0
	 *
	 * @var Edit
	 */
	private $edit;

	/**
	 * Coupons Overview page.
	 *
	 * @since 1.0.0
	 *
	 * @var Overview
	 */
	private $overview;

	/**
	 * Current view.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $view;

	/**
	 * PaymentPages constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Overview $overview Coupons Overview page.
	 * @param Edit     $edit     Edit coupon page.
	 */
	public function __construct( Overview $overview, Edit $edit ) {

		$this->overview = $overview;
		$this->edit     = $edit;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->view = ! empty( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : '';
	}

	/**
	 * Define hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		if ( ! wpforms_is_admin_page( 'payments' ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter( 'wpforms_admin_payments_payments_get_views', [ $this, 'register_view' ] );

		add_filter( 'wpforms_admin_payments_views_single_get_coupon_info', [ $this, 'add_coupon_name' ], 10, 3 );
	}

	/**
	 * Register payment pages.
	 *
	 * @since 1.0.0
	 *
	 * @param array $views Views.
	 *
	 * @return array
	 */
	public function register_view( $views ) {

		$views['coupons'] = $this->overview;
		$views['coupon']  = $this->edit;

		return $views;
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		$min = wpforms_get_min_suffix();

		if ( in_array( $this->view, [ 'coupons', 'coupon' ], true ) ) {
			wp_enqueue_style(
				'tooltipster',
				WPFORMS_PLUGIN_URL . 'assets/lib/jquery.tooltipster/jquery.tooltipster.min.css',
				null,
				'4.2.6'
			);
		}

		wp_enqueue_style(
			'wpforms-coupon-admin',
			WPFORMS_COUPONS_URL . "assets/css/admin{$min}.css",
			[],
			WPFORMS_COUPONS_VERSION
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		$min = wpforms_get_min_suffix();

		if ( in_array( $this->view, [ 'coupons', 'coupon' ], true ) ) {
			wp_enqueue_script(
				'tooltipster',
				WPFORMS_PLUGIN_URL . 'assets/lib/jquery.tooltipster/jquery.tooltipster.min.js',
				[ 'jquery' ],
				'4.2.6',
				true
			);
		}

		wp_enqueue_script(
			'wpforms-coupon-admin',
			WPFORMS_COUPONS_URL . "assets/js/admin{$min}.js",
			[ 'jquery' ],
			WPFORMS_COUPONS_VERSION,
			true
		);
	}

	/**
	 * Register coupon column.
	 *
	 * @since 1.0.0
	 * @deprecated 1.1.0
	 *
	 * @param array $columns Payments Overview page columns.
	 *
	 * @return array
	 */
	public function register_column( $columns ) {

		_deprecated_function( __METHOD__, '1.1.0 of the WPForms Coupons Add-on' );

		return wpforms_array_insert( $columns, [ 'coupon_id' => __( 'Coupon', 'wpforms-coupons' ) ], 'total', 'before' );
	}

	/**
	 * Print a payment coupon value.
	 *
	 * @since 1.0.0
	 * @deprecated 1.1.0
	 *
	 * @param string $value       Default column value.
	 * @param array  $item        Item data.
	 * @param string $column_name Column name.
	 *
	 * @return string
	 */
	public function column_value( $value, $item, $column_name ) {

		_deprecated_function( __METHOD__, '1.1.0 of the WPForms Coupons Add-on' );

		if ( $column_name !== 'coupon_id' ) {
			return $value;
		}

		$coupon = $this->get_payment_coupon( $item['id'] );

		if ( $coupon === null ) {
			return esc_html__( 'N/A', 'wpforms-coupons' );
		}

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $coupon->get_edit_url() ),
			esc_html( $coupon->get_code() )
		);
	}

	/**
	 * Get coupon by payment ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $payment_id Payment ID.
	 *
	 * @return Coupon|null
	 */
	private function get_payment_coupon( $payment_id ) {

		$coupon_id = wpforms()->obj( 'payment_meta' )->get_single( $payment_id, 'coupon_id' );

		if ( ! $coupon_id ) {
			return null;
		}

		return wpforms_coupons()->get( 'repository' )->get_coupon_by_id( (int) $coupon_id );
	}

	/**
	 * Add coupon name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $coupon_info  Coupon info.
	 * @param object $payment      Payment object.
	 * @param array  $payment_meta Payment meta.
	 *
	 * @return string
	 */
	public function add_coupon_name( $coupon_info, $payment, $payment_meta ) {

		if ( empty( $payment_meta['coupon_id'] ) || empty( $payment_meta['coupon_id']->value ) ) {
			return $coupon_info;
		}

		$coupon = wpforms_coupons()->get( 'repository' )->get_coupon_by_id( $payment_meta['coupon_id']->value );

		if ( $coupon === null ) {
			return $coupon_info;
		}

		return $coupon->get_name() . "\n" . $coupon_info;
	}
}
