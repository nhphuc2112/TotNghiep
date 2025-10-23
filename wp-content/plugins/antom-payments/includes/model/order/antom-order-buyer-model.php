<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../antom-model-trait.php';
class Antom_Order_Buyer_Model implements ArrayAccess {


	use Antom_Model_Trait;


	public function get_reference_buyer_id() {
		return $this->offsetGet( 'referenceBuyerId' );
	}


	public function set_reference_buyer_id( $referenceBuyerId ) {
		$referenceBuyerId = (string) $referenceBuyerId;
		if ( strlen( $referenceBuyerId ) > 64 ) {
			throw new InvalidArgumentException( 'referenceBuyerId can not more than 64 characters' );
		}
		$this->offsetSet( 'referenceBuyerId', $referenceBuyerId );
	}


	public function get_buyer_name() {
		return $this->offsetGet( 'buyerName' );
	}


	public function set_buyer_name( $buyerName ) {
		if ( ! is_array( $buyerName ) ) {
			throw new InvalidArgumentException( 'buyerName must be an array' );
		}

		if ( isset( $buyerName['firstName'] ) && strlen( $buyerName['firstName'] ) > 32 ) {
			throw new InvalidArgumentException( 'firstName of buyerName can not more than 32 characters' );
		}

		if ( isset( $buyerName['middleName'] ) && strlen( $buyerName['middleName'] ) > 32 ) {
			throw new InvalidArgumentException( 'middleName of buyerName can not more than 32 characters' );
		}

		if ( isset( $buyerName['lastName'] ) && strlen( $buyerName['lastName'] ) > 32 ) {
			throw new InvalidArgumentException( 'lastName of buyerName can not more than 32 characters' );
		}

		if ( isset( $buyerName['fullName'] ) && strlen( $buyerName['fullName'] ) > 128 ) {
			throw new InvalidArgumentException( 'fullName of buyerName can not more than 128 characters' );
		}
		$this->offsetSet( 'buyerName', $buyerName );
	}


	public function get_buyer_phone_no() {
		return $this->offsetGet( 'buyerPhoneNo' );
	}


	public function set_buyer_phone_no( $buyerPhoneNo ) {
		if ( strlen( $buyerPhoneNo ) > 24 ) {
			throw new InvalidArgumentException( 'buyerPhoneNo can not more than 24 characters' );
		}
		$this->offsetSet( 'buyerPhoneNo', $buyerPhoneNo );
	}


	public function get_buyer_email() {
		return $this->offsetGet( 'buyerEmail' );
	}


	public function set_buyer_email( $buyerEmail ) {
		if ( strlen( $buyerEmail ) > 64 ) {
			throw new InvalidArgumentException( 'buyerEmail can not more than 64 characters' );
		}
		$this->offsetSet( 'buyerEmail', $buyerEmail );
	}


	public function get_buyer_registration_time() {
		return $this->offsetGet( 'buyerRegistrationTime' );
	}

	public function set_buyer_registration_time( $buyerRegistrationTime ) {
		if ( strlen( $buyerRegistrationTime ) > 64 ) {
			throw new InvalidArgumentException( 'buyerRegistrationTime can not more than 64 characters' );
		}
		$format   = 'Y-m-d\TH:i:sP';
		$dateTime = DateTime::createFromFormat( $format, $buyerRegistrationTime );

		if ( false === $dateTime ) {
			throw new InvalidArgumentException( 'buyerRegistrationTime must be a valid date' );
		}
		$this->offsetSet( 'buyerRegistrationTime', $buyerRegistrationTime );
	}
}
