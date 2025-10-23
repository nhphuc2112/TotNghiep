<?php

namespace WPFormsCoupons;

/**
 * Coupons Frontend class.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * Define hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'wpforms_frontend_css', [ $this, 'enqueue_styles' ] );
		add_action( 'wpforms_frontend_js', [ $this, 'enqueue_scripts' ] );
		add_filter( 'wpforms_frontend_strings', [ $this, 'strings' ] );
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Array of forms on the page.
	 */
	public function enqueue_styles( $forms ) {

		if ( ! $this->has_coupon_form( $forms ) && ! wpforms()->obj( 'frontend' )->assets_global() ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-coupons',
			WPFORMS_COUPONS_URL . "assets/css/main$min.css",
			[],
			WPFORMS_COUPONS_VERSION
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Array of forms on the page.
	 */
	public function enqueue_scripts( $forms ) {

		if ( ! $this->has_coupon_form( $forms ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-coupons',
			WPFORMS_COUPONS_URL . "assets/js/main$min.js",
			[ 'wpforms' ],
			WPFORMS_COUPONS_VERSION,
			true
		);
	}

	/**
	 * Check if form has coupon field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Array of forms on the page.
	 *
	 * @return bool
	 */
	private function has_coupon_form( $forms ) {

		foreach ( $forms as $form_data ) {
			foreach ( $form_data['fields'] as $field ) {
				if ( $field['type'] === 'payment-coupon' ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Add strings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $strings Array of strings.
	 *
	 * @return array
	 */
	public function strings( $strings ) {

		$strings['val_invalid_coupon']      = wpforms_setting( 'coupon-invalid', esc_html__( 'This is not a valid coupon.', 'wpforms-coupons' ) );
		$strings['remove_coupon_icon_text'] = esc_html__( 'Remove Coupon Icon', 'wpforms-coupons' );
		$strings['ppc_applied_coupon']      = esc_html__( 'Heads up! We have successfully applied your coupon. Now you can proceed with your payment.', 'wpforms-coupons' );
		$strings['summary_coupon_name']     = esc_html__( 'Coupon (%name%)', 'wpforms-coupons' );

		return $strings;
	}
}
