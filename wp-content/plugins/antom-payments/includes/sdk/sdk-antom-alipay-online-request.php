<?php
/**
 * Antom_Alipay_Online_Request
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once ANTOM_PAYMENT_GATEWAYS_PATH . '/includes/sdk/sdk-antom-alipay-request.php';

class Antom_Alipay_Online_Request extends Antom_Alipay_Request implements ArrayAccess {



	public function get_key_version() {
		return $this->offsetGet( 'keyVersion' );
	}


	public function set_key_version( $keyVersion ) {
		$this->offsetSet( 'keyVersion', $keyVersion );
	}


	public function get_client_id() {
		return $this->offsetGet( 'clientId' );
	}


	public function set_client_id( $clientId ) {
		$this->offsetSet( 'clientId', $clientId );
	}


	public function get_path() {
		return $this->offsetGet( 'path' );
	}


	public function set_path( $path ) {
		$this->offsetSet( 'path', $path );
	}

	public function offsetExists( $offset ) {
		return isset( $this->data[ $offset ] );
	}

	public function offsetGet( $offset ) {
		if ( isset( $this->data[ $offset ] ) ) {
			return $this->data[ $offset ];
		}
		return '';
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


	public function get_product_code() {
		return $this->offsetGet( 'productCode' );
	}


	public function set_product_code( $productCode ) {
		$this->offsetSet( 'productCode', $productCode );
	}


	public function get_payment_request_id() {
		return $this->offsetGet( 'paymentRequestId' );
	}


	public function set_payment_request_id( $paymentRequestId ) {
		$this->offsetSet( 'paymentRequestId', $paymentRequestId );
	}


	public function get_order() {
		return $this->offsetGet( 'order' );
	}

	public function set_order( $order ) {
		$this->offsetSet( 'order', $order );
	}


	public function get_payment_amount() {
		return $this->offsetGet( 'paymentAmount' );
	}


	public function set_payment_amount( $paymentAmount ) {
		$this->offsetSet( 'paymentAmount', $paymentAmount );
	}


	public function get_pay_to_method() {
		return $this->offsetGet( 'payToMethod' );
	}


	public function set_pay_to_method( $payToMethod ) {
		$this->offsetSet( 'payToMethod', $payToMethod );
	}


	public function get_payment_method() {
		return $this->offsetGet( 'paymentMethod' );
	}

	public function set_payment_method( $paymentMethod ) {
		$this->offsetSet( 'paymentMethod', $paymentMethod );
	}


	public function get_payment_expiry_time() {
		return $this->offsetGet( 'paymentExpiryTime' );
	}


	public function set_payment_expiry_time( $paymentExpiryTime ) {
		$this->offsetSet( 'paymentExpiryTime', $paymentExpiryTime );
	}


	public function get_payment_redirect_url() {
		return $this->offsetGet( 'paymentRedirectUrl' );
	}


	public function set_payment_redirect_url( $paymentRedirectUrl ) {
		$this->offsetSet( 'paymentRedirectUrl', $paymentRedirectUrl );
	}


	public function get_payment_notify_url() {
		return $this->offsetGet( 'paymentNotifyUrl' );
	}


	public function set_payment_notify_url( $paymentNotifyUrl ) {
		$this->offsetSet( 'paymentNotifyUrl', $paymentNotifyUrl );
	}


	public function get_is_authorization() {
		return $this->offsetGet( 'isAuthorization' );
	}


	public function set_is_authorization( $isAuthorization ) {
		$this->offsetSet( 'isAuthorization', $isAuthorization );
	}


	public function get_payment_verification_data() {
		return $this->offsetGet( 'paymentVerificationData' );
	}


	public function set_payment_verification_data( $paymentVerificationData ) {
		$this->offsetSet( 'paymentVerificationData', $paymentVerificationData );
	}


	public function get_payment_factor() {
		return $this->offsetGet( 'paymentFactor' );
	}


	public function set_payment_factor( $paymentFactor ) {
		$this->offsetSet( 'paymentFactor', $paymentFactor );
	}


	public function get_merchant() {
		return $this->offsetGet( 'merchant' );
	}

	public function set_merchant( $merchant ) {
		$this->offsetSet( 'merchant', $merchant );
	}


	public function get_extend_info() {
		return $this->offsetGet( 'extendInfo' );
	}


	public function set_extend_info( $extendInfo ) {
		$this->offsetSet( 'extendInfo', $extendInfo );
	}


	public function get_credit_pay_plan() {
		return $this->offsetGet( 'creditPayPlan' );
	}


	public function set_credit_pay_plan( $creditPayPlan ) {
		$this->offsetSet( 'creditPayPlan', $creditPayPlan );
	}


	public function get_settlement_strategy() {
		return $this->offsetGet( 'settlementStrategy' );
	}


	public function set_settlement_strategy( $settlementStrategy ) {
		$this->offsetSet( 'settlementStrategy', $settlementStrategy );
	}


	public function get_app_id() {
		return $this->offsetGet( 'appId' );
	}


	public function set_app_id( $appId ) {
		$this->offsetSet( 'appId', $appId );
	}


	public function get_merchant_region() {
		return $this->offsetGet( 'merchantRegion' );
	}


	public function set_merchant_region( $merchantRegion ) {
		$this->offsetSet( 'merchantRegion', $merchantRegion );
	}


	public function get_env() {
		return $this->offsetGet( 'env' );
	}


	public function set_env( $env ) {
		$this->offsetSet( 'env', $env );
	}
}
