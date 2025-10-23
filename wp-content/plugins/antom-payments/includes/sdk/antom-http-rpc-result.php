<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class Antom_Http_Rpc_Result {

	public $rspBody;
	public $rspSign;
	public $rspTime;


	public function getRspBody() {
		return $this->rspBody;
	}


	public function setRspBody( $rspBody ) {
		$this->rspBody = $rspBody;
	}


	public function getRspSign() {
		return $this->rspSign;
	}


	public function setRspSign( $rspSign ) {
		$this->rspSign = $rspSign;
	}


	public function getRspTime() {
		return $this->rspTime;
	}

	public function setRspTime( $rspTime ) {
		$this->rspTime = $rspTime;
	}
}
