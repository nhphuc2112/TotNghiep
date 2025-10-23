<?php
/**
 * Antom_Order_Payment_Method_Model
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../antom-model-trait.php';

class Antom_Order_Payment_Method_Model implements ArrayAccess {
	use Antom_Model_Trait;


	public function get_payment_method_type() {
		return $this->offsetGet( 'paymentMethodType' );
	}


	public function set_payment_method_type( $paymentMethodType ) {
		if ( strlen( $paymentMethodType ) > 64 ) {
			throw new InvalidArgumentException( 'paymentMethodType must be less than 65 characters' );
		}
		$this->offsetSet( 'paymentMethodType', $paymentMethodType );
	}


	public function get_payment_method_id() {
		return $this->offsetGet( 'paymentMethodId' );
	}


	public function set_payment_method_id( $paymentMethodId ) {
		if ( strlen( $paymentMethodId ) > 128 ) {
			throw new InvalidArgumentException( 'paymentMethodId must be less than 128 characters' );
		}
		$this->offsetSet( 'paymentMethodId', $paymentMethodId );
	}


	public function get_payment_method_meta_data() {
		return $this->offsetGet( 'paymentMethodMetaData' );
	}


	public function set_payment_method_meta_data( $paymentMethodMetaData ) {
		if ( ! is_array( $paymentMethodMetaData ) ) {
			throw new InvalidArgumentException( 'paymentMethodMetaData must be an array' );
		}
		if ( isset( $paymentMethodMetaData['blikCode'] ) && strlen( $paymentMethodMetaData['blikCode'] ) > 6 ) {
			throw new InvalidArgumentException( 'blikCode of paymentMethodMetaData must be less than 7 characters' );
		}
		if ( isset( $paymentMethodMetaData['payerEmail'] ) && strlen( $paymentMethodMetaData['payerEmail'] ) > 64 ) {
			throw new InvalidArgumentException( 'payerEmail of paymentMethodMetaData must be less than 65 characters' );
		}
		if ( isset( $paymentMethodMetaData['bankIdentifierCode'] ) ) {
			$allow_bank_list = antom_get_bank_list();

			$bank_list = array();
			foreach ( $allow_bank_list as $value ) {
				$bank_list = array_merge( $bank_list, array_values( $value ) );
			}

			if ( ! in_array( $paymentMethodMetaData['bankIdentifierCode'], $bank_list ) ) {
				throw new InvalidArgumentException( 'bankIdentifierCode of paymentMethodMetaData must be one of allow bank list' );
			}
		}
		if ( isset( $paymentMethodMetaData['is3DSAuthentication'] ) ) {
			$valid = array( true, false );
			if ( ! in_array( $paymentMethodMetaData['is3DSAuthentication'], $valid ) ) {
				throw new InvalidArgumentException( 'is3DSAuthentication of paymentMethodMetaData must be true or false' );
			}
		}
		if ( isset( $paymentMethodMetaData['cardNo'] ) && strlen( $paymentMethodMetaData['cardNo'] ) > 32 ) {
			throw new InvalidArgumentException( 'cardNo of paymentMethodMetaData must be less than 33 characters' );
		}
		if ( isset( $paymentMethodMetaData['cvv'] ) && strlen( $paymentMethodMetaData['cvv'] ) > 4 ) {
			throw new InvalidArgumentException( 'cvv of paymentMethodMetaData must be less than 5 characters' );
		}
		if ( isset( $paymentMethodMetaData['expiryYear'] ) && strlen( $paymentMethodMetaData['expiryYear'] ) > 2 ) {
			throw new InvalidArgumentException( 'expiryYear of paymentMethodMetaData must be less than 3 characters' );
		}
		if ( isset( $paymentMethodMetaData['expiryMonth'] ) && strlen( $paymentMethodMetaData['expiryMonth'] ) > 2 ) {
			throw new InvalidArgumentException( 'expiryMonth of paymentMethodMetaData must be less than 3 characters' );
		}
		if ( isset( $paymentMethodMetaData['cardholderName'] ) ) {
			if ( ! is_array( $paymentMethodMetaData['cardholderName'] ) ) {
				throw new InvalidArgumentException( 'cardholderName of paymentMethodMetaData must be an array' );
			}
			if ( ! isset( $paymentMethodMetaData['cardholderName']['firstName'] ) ) {
				throw new InvalidArgumentException( 'firstName of cardholderName of paymentMethodMetaData must exist' );
			}
			if ( strlen( $paymentMethodMetaData['cardholderName']['firstName'] ) > 32 ) {
				throw new InvalidArgumentException( 'firstName of cardholderName of paymentMethodMetaData must be less than 33 characters' );
			}
			if ( ! isset( $paymentMethodMetaData['cardholderName']['lastName'] ) ) {
				throw new InvalidArgumentException( 'lastName of cardholderName of paymentMethodMetaData must exist' );
			}
			if ( strlen( $paymentMethodMetaData['cardholderName']['lastName'] ) > 32 ) {
				throw new InvalidArgumentException( 'lastName of cardholderName of paymentMethodMetaData must be less than 33 characters' );
			}
			if ( isset( $paymentMethodMetaData['cardholderName']['middleName'] ) && strlen( $paymentMethodMetaData['cardholderName']['middleName'] ) > 32 ) {
				throw new InvalidArgumentException( 'middleName of cardholderName of paymentMethodMetaData must be less than 33 characters' );
			}
			if ( isset( $paymentMethodMetaData['cardholderName']['fullName'] ) && strlen( $paymentMethodMetaData['cardholderName']['prefix'] ) > 128 ) {
				throw new InvalidArgumentException( 'fullName of cardholderName of paymentMethodMetaData must be less than 129 characters' );
			}
		}
		if ( isset( $paymentMethodMetaData['billingAddress'] ) ) {
			if ( ! is_array( $paymentMethodMetaData['billingAddress'] ) ) {
				throw new InvalidArgumentException( 'billingAddress of paymentMethodMetaData must be an array' );
			}
			if ( ! isset( $paymentMethodMetaData['billingAddress']['region'] ) ) {
				throw new InvalidArgumentException( 'region of billingAddress of paymentMethodMetaData must exist' );
			}
			if ( strlen( $paymentMethodMetaData['billingAddress']['region'] ) > 2 ) {
				throw new InvalidArgumentException( 'region of billingAddress of paymentMethodMetaData must be less than 3 characters' );
			}
			if ( isset( $paymentMethodMetaData['billingAddress']['state'] ) && strlen( $paymentMethodMetaData['billingAddress']['state'] ) > 8 ) {
				throw new InvalidArgumentException( 'state of billingAddress of paymentMethodMetaData must be less than 9 characters' );
			}
			if ( isset( $paymentMethodMetaData['billingAddress']['city'] ) && strlen( $paymentMethodMetaData['billingAddress']['city'] ) > 32 ) {
				throw new InvalidArgumentException( 'city of billingAddress of paymentMethodMetaData must be less than 33 characters' );
			}
			if ( isset( $paymentMethodMetaData['billingAddress']['address1'] ) && strlen( $paymentMethodMetaData['billingAddress']['address1'] ) > 32 ) {
				throw new InvalidArgumentException( 'address1 of billingAddress of paymentMethodMetaData must be less than 33 characters' );
			}
			if ( isset( $paymentMethodMetaData['billingAddress']['address2'] ) && strlen( $paymentMethodMetaData['billingAddress']['address2'] ) > 32 ) {
				throw new InvalidArgumentException( 'address2 of billingAddress of paymentMethodMetaData must be less than 33 characters' );
			}
			if ( isset( $paymentMethodMetaData['billingAddress']['zipCode'] ) && strlen( $paymentMethodMetaData['billingAddress']['zipCode'] ) > 32 ) {
				throw new InvalidArgumentException( 'zipCode of billingAddress of paymentMethodMetaData must be less than 33 characters' );
			}
		}
		if ( isset( $paymentMethodMetaData['cpf'] ) && strlen( $paymentMethodMetaData['cpf'] ) > 11 ) {
			throw new InvalidArgumentException( 'cpf of paymentMethodMetaData must be less than 12 characters' );
		}
		if ( isset( $paymentMethodMetaData['dateOfBirth'] ) ) {
			if ( strlen( $paymentMethodMetaData['dateOfBirth'] ) != 10 ) {
				throw new InvalidArgumentException( 'dateOfBirth of paymentMethodMetaData must be 10 characters' );
			}

			$format   = 'Y-m-d';
			$dateTime = DateTime::createFromFormat( $format, $paymentMethodMetaData['dateOfBirth'] );
			if ( false == $dateTime || $paymentMethodMetaData['dateOfBirth'] !== $dateTime->format( $format ) ) {
				throw new InvalidArgumentException( 'dateOfBirth of paymentMethodMetaData must be a valid date' );
			}
		}
		if ( isset( $paymentMethodMetaData['businessNo'] ) && strlen( $paymentMethodMetaData['businessNo'] ) > 10 ) {
			throw new InvalidArgumentException( 'businessNo of paymentMethodMetaData must be less than 11 characters' );
		}
		if (
			isset( $paymentMethodMetaData['cardPasswordDigest'] ) &&
			strlen( $paymentMethodMetaData['cardPasswordDigest'] ) > 2
		) {
			throw new InvalidArgumentException( 'cardPasswordDigest of paymentMethodMetaData must be less than 3 characters' );
		}
		if (
			isset( $paymentMethodMetaData['paymentMethodRegion'] ) &&
			strlen( $paymentMethodMetaData['paymentMethodRegion'] ) > 6
		) {
			throw new InvalidArgumentException( 'paymentMethodRegion of paymentMethodMetaData must be less than 7 characters' );
		}
		if (
			isset( $paymentMethodMetaData['payerEmail'] ) &&
			strlen( $paymentMethodMetaData['payerEmail'] ) > 64
		) {
			throw new InvalidArgumentException( 'payerEmail of paymentMethodMetaData must be less than 65 characters' );
		}
		if ( isset( $paymentMethodMetaData['tokenize'] ) ) {
			$valid = array( true, false );
			if ( ! in_array( $paymentMethodMetaData['tokenize'], $valid ) ) {
				throw new InvalidArgumentException( 'tokenize of paymentMethodMetaData must be true or false' );
			}
		}
		if ( isset( $paymentMethodMetaData['isCardOnFile'] ) ) {
			$valid = array( true, false );
			if ( ! in_array( $paymentMethodMetaData['isCardOnFile'], $valid ) ) {
				throw new InvalidArgumentException( 'isCardOnFile of paymentMethodMetaData must be true or false' );
			}
		}
		if ( isset( $paymentMethodMetaData['recurringType'] ) ) {
			$valid = array( 'SCHEDULED', 'UNSCHEDULED' );
			if ( ! in_array( $paymentMethodMetaData['recurringType'], $valid ) ) {
				throw new InvalidArgumentException( 'recurringType of paymentMethodMetaData must be true or false' );
			}
		}
		if (
			isset( $paymentMethodMetaData['networkTransactionId'] ) &&
			strlen( $paymentMethodMetaData['networkTransactionId'] ) > 100
		) {
			throw new InvalidArgumentException( 'networkTransactionId of paymentMethodMetaData must be less than 101 characters' );
		}
		if (
			isset( $paymentMethodMetaData['selectedCardBrand'] ) &&
			strlen( $paymentMethodMetaData['selectedCardBrand'] ) > 64
		) {
			throw new InvalidArgumentException( 'selectedCardBrand of paymentMethodMetaData must be less than 65 characters' );
		}
		if ( isset( $paymentMethodMetaData['enableAuthenticationUpgrade'] ) ) {
			$valid = array( true, false );
			if ( ! in_array( $paymentMethodMetaData['enableAuthenticationUpgrade'], $valid ) ) {
				throw new InvalidArgumentException( 'enableAuthenticationUpgrade of paymentMethodMetaData must be true or false' );
			}
		}
		if ( isset( $paymentMethodMetaData['mpiData'] ) ) {
			if ( ! is_array( $paymentMethodMetaData['mpiData'] ) ) {
				throw new InvalidArgumentException( 'mpiData of paymentMethodMetaData must be an array' );
			}
			if ( ! isset( $paymentMethodMetaData['mpiData']['threeDSVersion'] ) ) {
				throw new InvalidArgumentException( 'threeDSVersion of mpiData of paymentMethodMetaData must exist' );
			}
			$valid = array(
				'1.0.2',
				'2.1.0',
				'2.2.0',
			);
			if ( ! in_array( $paymentMethodMetaData['mpiData']['threeDSVersion'], $valid ) ) {
				throw new InvalidArgumentException( 'threeDSVersion of mpiData of paymentMethodMetaData must be 1.0.2, 2.1.0 or 2.2.0' );
			}

			if ( ! isset( $paymentMethodMetaData['mpiData']['eci'] ) ) {
				throw new InvalidArgumentException( 'eci of mpiData of paymentMethodMetaData must exist' );
			}

			$valid = array(
				'02',
				'01',
				'00',
				'05',
				'06',
				'07',
			);
			if ( ! in_array( $paymentMethodMetaData['mpiData']['eci'], $valid ) ) {
				throw new InvalidArgumentException( 'eci of mpiData of paymentMethodMetaData must be 02, 01, 00, 05, 06 or 07' );
			}

			if (
				isset( $paymentMethodMetaData['mpiData']['cavv'] ) &&
				strlen( $paymentMethodMetaData['mpiData']['cavv'] ) > 64
			) {
				throw new InvalidArgumentException( 'cavv of mpiData of paymentMethodMetaData must be less than 65 characters' );
			}

			if (
				isset( $paymentMethodMetaData['mpiData']['dsTransactionId'] ) &&
				strlen( $paymentMethodMetaData['mpiData']['dsTransactionId'] ) > 64
			) {
				throw new InvalidArgumentException( 'dsTransactionId of mpiData of paymentMethodMetaData must be less than 65 characters' );
			}
		}

		if ( isset( $paymentMethodMetaData['payerName'] ) ) {
			if ( ! isset( $paymentMethodMetaData['payerName']['firstName'] ) ) {
				throw new InvalidArgumentException( 'firstName of payerName of paymentMethodMetaData must exist' );
			}
			if ( ! isset( $paymentMethodMetaData['payerName']['lastName'] ) ) {
				throw new InvalidArgumentException( 'lastName of payerName of paymentMethodMetaData must exist' );
			}

			if ( strlen( $paymentMethodMetaData['payerName']['firstName'] ) > 32 ) {
				throw new InvalidArgumentException( 'firstName of payerName of paymentMethodMetaData must be less than 33 characters' );
			}

			if ( strlen( $paymentMethodMetaData['payerName']['lastName'] ) > 32 ) {
				throw new InvalidArgumentException( 'lastName of payerName of paymentMethodMetaData must be less than 33 characters' );
			}
		}

		$this->offsetSet( 'paymentMethodMetaData', $paymentMethodMetaData );
	}


	public function get_customer_id() {
		return $this->offsetGet( 'customerId' );
	}


	public function set_customer_id( $customerId ) {
		if ( strlen( $customerId ) > 32 ) {
			throw new InvalidArgumentException( 'customerId must be less than 33 characters' );
		}
		$this->offsetSet( 'customerId', $customerId );
	}


	public function get_extend_info() {
		return $this->offsetGet( 'extendInfo' );
	}


	public function set_extend_info( $extendInfo ) {
		if ( is_array( $extendInfo ) ) {
			$extendInfo = json_encode( $extendInfo );
		}

		if ( strlen( $extendInfo ) > 2048 ) {
			throw new InvalidArgumentException( 'extendInfo must be less than 2049 characters' );
		}
		$this->offsetSet( 'extendInfo', $extendInfo );
	}
}
