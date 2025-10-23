<?php

namespace WPFormsCoupons;

/**
 * Coupons Generator class.
 *
 * @since 1.0.0
 */
class Generator {

	/**
	 * Define hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		if ( ! wpforms_is_admin_ajax() ) {
			return;
		}

		add_action( 'wp_ajax_wpforms_coupons_generate_coupon_code', [ $this, 'ajax_generate_code' ] );
	}

	/**
	 * Ajax callback for code generation.
	 *
	 * @since 1.0.0
	 */
	public function ajax_generate_code() {

		check_ajax_referer( 'wpforms-admin', 'nonce' );

		wp_send_json_success( $this->generate_code() );
	}

	/**
	 * Generate code.
	 *
	 * @since 1.0.0
	 */
	private function generate_code() {

		$default = [
			'prefix'      => '',
			'suffix'      => '',
			'characters'  => 'ABCDEFGHJKMNPQRSTUVWXYZ23456789',
			'code_length' => 8,
		];

		/**
		 * Allow modifying coupon code generator.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Generate coupon arguments.
		 */
		$args = apply_filters( 'wpforms_coupons_generator_generate_code_args', $default );

		$args              = wp_parse_args( $args, $default );
		$characters_length = strlen( $args['characters'] );
		$result            = '';

		for ( $i = 0; $i < $args['code_length']; $i++ ) {
			$result .= $args['characters'][ wp_rand( 0, $characters_length - 1 ) ];
		}

		return $args['prefix'] . $result . $args['suffix'];
	}
}
