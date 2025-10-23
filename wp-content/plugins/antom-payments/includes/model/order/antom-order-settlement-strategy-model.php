<?php
/**
 * Antom_Order_Settlement_Strategy_Model
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../antom-model-trait.php';

class Antom_Order_Settlement_Strategy_Model implements ArrayAccess {
	use Antom_Model_Trait;


	public function get_settlement_currency() {
		return $this->offsetGet( 'settlementCurrency' );
	}


	public function set_settlement_currency( $settlementCurrency ) {
		if ( strlen( $settlementCurrency ) > 3 ) {
			throw new InvalidArgumentException( 'settlementCurrency must be less than 4 characters' );
		}
		$this->offsetSet( 'settlementCurrency', $settlementCurrency );
	}
}
