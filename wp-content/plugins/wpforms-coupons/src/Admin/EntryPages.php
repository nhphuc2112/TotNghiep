<?php

namespace WPFormsCoupons\Admin;

use WPFormsCoupons\Admin\Coupons\Edit;

/**
 * Entry pages class.
 *
 * @since 1.0.0
 */
class EntryPages {

	/**
	 * Define hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_filter( 'wpforms_html_field_value', [ $this, 'add_coupon_link' ], 10, 4 );
	}

	/**
	 * Wrap up a coupon to the coupon page link.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value     Smart tag value.
	 * @param array  $field     The field.
	 * @param array  $form_data Processed form settings/data, prepared to be used later.
	 * @param string $context   Context.
	 *
	 * @return string
	 */
	public function add_coupon_link( $value, $field, $form_data, $context ) {

		if (
			! in_array( $context, [ 'entry-table', 'entry-single', 'payment-single' ], true )
			|| wpforms_is_admin_page( 'entries', 'print' )
		) {
			return $value;
		}

		if ( empty( $field['type'] ) || $field['type'] !== 'payment-coupon' ) {
			return $value;
		}

		if ( empty( $field['coupon_id'] ) || wpforms_is_empty_string( $value ) ) {
			return $value;
		}

		return sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( Edit::get_page_url( absint( $field['coupon_id'] ) ) ),
			esc_html( $value )
		);
	}
}
