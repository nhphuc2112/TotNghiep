<?php
/**
 * Antom_Order_Amount_Model
 *
 * @user Antom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../antom-model-trait.php';

class Antom_Order_Amount_Model implements ArrayAccess {
	use Antom_Model_Trait;


	public function get_currency() {
		return $this->offsetGet( 'currency' );
	}


	public function set_currency( $currency ) {
		if ( strlen( $currency ) != 3 ) {
			throw new InvalidArgumentException( 'currency of param amount must be 3 characters' );
		}
		$this->offsetSet( 'currency', $currency );
	}


	public function get_value() {
		return $this->offsetGet( 'value' );
	}


	public function set_value( $value ) {
		if ( ! is_numeric( $value ) && intval( $value ) != $value ) {
			throw new InvalidArgumentException( 'value of param amount must be an integer' );
		}
		if ( $value < 1 ) {
			throw new InvalidArgumentException( 'value of param amount must be greater than 0' );
		}
		$this->offsetSet( 'value', $value );
	}
}
