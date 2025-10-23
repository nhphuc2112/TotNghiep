<?php

namespace WPFormsCoupons\Admin;

/**
 * Coupons Settings class.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Define hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_filter( 'wpforms_settings_defaults', [ $this, 'register_settings_messages' ] );
	}

	/**
	 * Register validation messages.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings WPForms settings.
	 *
	 * @return array
	 */
	public function register_settings_messages( $settings ) {

		$settings['validation']['coupon-invalid'] = [
			'id'      => 'coupon-invalid',
			'name'    => esc_html__( 'Coupon Error', 'wpforms-coupons' ),
			'type'    => 'text',
			'default' => esc_html__( 'This is not a valid coupon.', 'wpforms-coupons' ),
		];

		return $settings;
	}
}
