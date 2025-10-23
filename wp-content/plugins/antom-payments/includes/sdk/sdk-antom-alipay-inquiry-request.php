<?php
/**
 * Antom_Alipay_Inquiry_Request
 *
 * @user Antom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once ANTOM_PAYMENT_GATEWAYS_PATH . '/includes/sdk/sdk-antom-alipay-request.php';

class Antom_Alipay_Inquiry_Request extends Antom_Alipay_Request implements ArrayAccess {
	private $data = array();

	protected $paymentRequestId;
	protected $paymentId;
	protected $merchantAccountId;

	public function offsetExists( $offset ) {
		return isset( $this->data[ $offset ] );
	}

	public function offsetGet( $offset ) {
		return $this->data[ $offset ];
	}

	public function offsetSet( $offset, $value ) {
		$this->data[ $offset ] = $value;
	}

	public function offsetUnset( $offset ) {
		unset( $this->data[ $offset ] );
	}

	public function toArray() {
		return $this->data;
	}

	public function toJson() {
		return wp_json_encode( $this->data );
	}

	public function get_payment_request_id() {
		return $this->offsetGet( 'paymentRequestId' );
	}

	public function set_payment_request_id( $paymentRequestId ) {
		$this->offsetSet( 'paymentRequestId', $paymentRequestId );
	}


	public function get_payment_id() {
		return $this->offsetGet( 'paymentId' );
	}

	public function set_payment_id( $paymentId ) {
		$this->offsetSet( 'paymentId', $paymentId );
	}


	public function get_merchant_account_id() {
		return $this->offsetGet( 'merchantAccountId' );
	}


	public function set_merchant_account_id( $merchantAccountId ) {
		$this->offsetSet( 'merchantAccountId', $merchantAccountId );
	}
}
