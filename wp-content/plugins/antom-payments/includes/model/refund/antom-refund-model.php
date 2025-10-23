<?php
/**
 * Antom_Refund_Model
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/../antom-model-trait.php';

class Antom_Refund_Model implements ArrayAccess {
	use Antom_Model_Trait;


	public function get_refund_request_id() {
		return $this->offsetGet( 'refundRequestId' );
	}

	public function set_refund_request_id( $refundRequestId ) {
		if ( strlen( $refundRequestId ) > 64 ) {
			throw new InvalidArgumentException( 'refundRequestId can not more than 64 characters' );
		}
		$this->offsetSet( 'refundRequestId', $refundRequestId );
	}


	public function get_payment_id() {
		return $this->offsetGet( 'paymentId' );
	}

	public function set_payment_id( $paymentId ) {
		if ( strlen( $paymentId ) > 64 ) {
			throw new InvalidArgumentException( 'refundRequestId can not more than 64 characters' );
		}
		$this->offsetSet( 'paymentId', $paymentId );
	}


	public function get_reference_refund_id() {
		return $this->offsetGet( 'referenceRefundId' );
	}


	public function set_reference_refund_id( $referenceRefundId ) {
		if ( strlen( $referenceRefundId ) > 64 ) {
			throw new InvalidArgumentException( 'referenceRefundId can not more than 64 characters' );
		}
		$this->offsetSet( 'referenceRefundId', $referenceRefundId );
	}


	public function get_refund_amount() {
		return $this->offsetGet( 'refundAmount' );
	}


	public function set_refund_amount( $refundAmount ) {
		if ( ! is_array( $refundAmount ) ) {
			throw new InvalidArgumentException( 'refundAmount must be an array' );
		}
		if ( ! isset( $refundAmount['currency'] ) ) {
			throw new InvalidArgumentException( 'currency of refundAmount must be set' );
		}
		if ( ! isset( $refundAmount['value'] ) ) {
			throw new InvalidArgumentException( 'value of refundAmount must be set' );
		}
		if ( strlen( $refundAmount['currency'] ) != 3 ) {
			throw new InvalidArgumentException( 'currency of refundAmount must be 3 characters' );
		}
		if ( ! is_numeric( $refundAmount['value'] ) && intval( $refundAmount['value'] ) != $refundAmount['value'] ) {
			throw new InvalidArgumentException( 'value of refundAmount must be an integer' );
		}
		if ( $refundAmount['value'] < 1 ) {
			throw new InvalidArgumentException( 'value of refundAmount must be greater than 0' );
		}
		$this->offsetSet( 'refundAmount', $refundAmount );
	}


	public function get_refund_reason() {
		return $this->offsetGet( 'refundReason' );
	}


	public function set_refund_reason( $refundReason ) {
		if ( strlen( $refundReason ) > 256 ) {
			throw new InvalidArgumentException( 'refundReason can not more than 256 characters' );
		}
		$this->offsetSet( 'refundReason', $refundReason );
	}


	public function get_refund_notify_url() {
		return $this->offsetGet( 'refundNotifyUrl' );
	}

	public function set_refund_notify_url( $refundNotifyUrl ) {
		if ( strlen( $refundNotifyUrl ) > 1024 ) {
			throw new InvalidArgumentException( 'refundNotifyUrl can not more than 1024 characters' );
		}
		$this->offsetSet( 'refundNotifyUrl', $refundNotifyUrl );
	}
}
