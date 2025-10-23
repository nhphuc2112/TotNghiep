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

class Antom_Order_Name_Model implements ArrayAccess {

	use Antom_Model_Trait;


	public function get_first_name() {
		return $this->offsetGet( 'firstName' );
	}


	public function set_first_name( $firstName ) {
		if ( strlen( $firstName ) > 32 ) {
			throw new InvalidArgumentException( 'firstName can not more than 32 characters' );
		}
		$this->offsetSet( 'firstName', $firstName );
	}

	public function get_middle_name() {
		return $this->offsetGet( 'middleName' );
	}

	public function set_middle_name( $middleName ) {
		if ( strlen( $middleName ) > 32 ) {
			throw new InvalidArgumentException( 'middleName can not more than 32 characters' );
		}
		$this->offsetSet( 'middleName', $middleName );
	}


	public function get_last_name() {
		return $this->offsetGet( 'lastName' );
	}


	public function set_last_name( $lastName ) {
		if ( strlen( $lastName ) > 32 ) {
			throw new InvalidArgumentException( 'lastName can not more than 32 characters' );
		}
		$this->offsetSet( 'lastName', $lastName );
	}


	public function get_full_name() {
		return $this->offsetGet( 'fullName' );
	}


	public function set_full_name( $fullName ) {
		if ( strlen( $fullName ) > 128 ) {
			throw new InvalidArgumentException( 'fullName can not more than 128 characters' );
		}
		$this->offsetSet( 'fullName', $fullName );
	}
}
