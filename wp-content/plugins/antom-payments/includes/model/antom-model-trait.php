<?php

/**
 * Antom_Model_Trait
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
trait Antom_Model_Trait {
	private $data = array();

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
}
