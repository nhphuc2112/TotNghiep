<?php

/**
 * Antom_Frontend
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Antom_Frontend {

	public function woocommerce_available_payment_gateways( $available_gateways ) {
		if ( Antom_Payment_Gateways_Statement::ANTOM_DEVELOP_MODE ) {
			return $available_gateways;
		}

		$payment_methods       = antom_get_payment_methods();
		$final_payment_methods = array();
		foreach ( $payment_methods as $payment_method ) {
			$final_payment_methods[ $payment_method['slug'] ] = $payment_method['support_currencies'];
		}

		$currency = get_woocommerce_currency();

		foreach ( $available_gateways as $slug => $available_gateway ) {
			if ( false !== strpos( $slug, 'antom_' ) ) {
				if ( ! isset( $final_payment_methods[ $slug ] ) || ! in_array(
					$currency,
					$final_payment_methods[ $slug ]
				) ) {
					unset( $available_gateways[ $slug ] );
				}
			}
		}

		return $available_gateways;
	}
}
