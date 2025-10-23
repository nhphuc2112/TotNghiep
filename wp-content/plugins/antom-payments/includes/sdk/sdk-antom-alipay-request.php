<?php

/**
 * Antom_Alipay_Request
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Antom_Alipay_Request {

	private $httpMethod = 'POST';
	private $path;
	private $clientId;
	private $keyVersion;


	public function get_path() {
		return $this->path;
	}

	public function set_path( $path ) {
		$this->path = $path;
	}


	public function get_client_id() {
		return $this->clientId;
	}


	public function set_client_id( $clientId ) {
		$this->clientId = $clientId;
	}


	public function get_key_version() {
		return $this->keyVersion;
	}


	public function set_key_version( $keyVersion ) {
		$this->keyVersion = $keyVersion;
	}

	public function get_http_method() {
		return $this->httpMethod;
	}

	public function set_http_method( $httpMethod ) {
		$this->httpMethod = $httpMethod;
	}
}
