<?php
/**
 * Antom_Order_Model
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../antom-model-trait.php';

class Antom_Order_Model implements ArrayAccess {
	use Antom_Model_Trait;

	public function get_env() {
		return $this->offsetGet( 'evn' );
	}

	public function set_env( $env ) {
		$this->offsetSet( 'env', $env );
	}

	public function get_order_amount() {
		return $this->offsetGet( 'orderAmount' );
	}


	public function set_order_amount( $orderAmount ) {
		if ( ! is_array( $orderAmount ) ) {
			throw new InvalidArgumentException( 'Order amount must be a array' );
		}
		if ( ! isset( $orderAmount['currency'] ) ) {
			throw new InvalidArgumentException( 'param currency must be set' );
		}
		if ( ! is_string( $orderAmount['currency'] ) ) {
			throw new InvalidArgumentException( 'currency must be a string' );
		}
		if ( strlen( $orderAmount['currency'] ) != 3 ) {
			throw new InvalidArgumentException( 'currency must be a 3-letter code' );
		}
		if ( ! isset( $orderAmount['value'] ) ) {
			throw new InvalidArgumentException( 'param value must be set' );
		}
		if ( ! is_numeric( $orderAmount['value'] ) && intval( $orderAmount['value'] ) != $orderAmount['value'] ) {
			throw new InvalidArgumentException( 'param value must be a number' );
		}
		if ( $orderAmount['value'] < 1 ) {
			throw new InvalidArgumentException( 'value cannot less than 1' );
		}

		if ( ! is_numeric( $orderAmount['value'] ) ) {
			$orderAmount['value'] = intval( $orderAmount['value'] );
		}

		$this->offsetSet( 'orderAmount', $orderAmount );
	}


	public function get_reference_order_id() {
		return $this->offsetGet( 'referenceOrderId' );
	}


	public function set_reference_order_id( $referenceOrderId ) {
		if ( strlen( $referenceOrderId ) > 64 ) {
			throw new InvalidArgumentException( 'referenceOrderId can not more than 64 characters' );
		}
		$this->offsetSet( 'referenceOrderId', $referenceOrderId );
	}


	public function get_order_description() {
		return $this->offsetGet( 'orderDescription' );
	}


	public function set_order_description( $orderDescription ) {
		if ( strlen( $orderDescription ) > 256 ) {
			throw new InvalidArgumentException( 'orderDescription can not more than 256 characters' );
		}
		$this->offsetSet( 'orderDescription', $orderDescription );
	}


	public function get_goods() {
		return $this->offsetGet( 'goods' );
	}


	public function set_goods( $goods ) {
		if ( ! is_array( $goods ) ) {
			throw new InvalidArgumentException( 'goods must be an array' );
		}
		if ( empty( $goods ) ) {
			throw new InvalidArgumentException( 'goods cannot be an empty array' );
		}
		if ( count( $goods ) > 100 ) {
			throw new InvalidArgumentException( 'goods cannot be more than 100 elements' );
		}

		$unique_reference_goods_ids = array();

		foreach ( $goods as &$good ) {
			if ( ! isset( $good['referenceGoodsId'] ) ) {
				throw new InvalidArgumentException( 'goods element must have referenceGoodsId params' );
			}
			if ( ! $good['referenceGoodsId'] ) {
				throw new InvalidArgumentException( 'goods element param referenceGoodsId must have a value' );
			}
			if ( strlen( $good['referenceGoodsId'] ) > 64 ) {
				throw new InvalidArgumentException( 'goods element param referenceGoodsId can not more than 64 characters' );
			}

			if ( in_array( $good['referenceGoodsId'], $unique_reference_goods_ids ) ) {
				throw new InvalidArgumentException( 'goods element param referenceGoodsId must be unique ID to identify the goods' );
			}

			$unique_reference_goods_ids[] = $good['referenceGoodsId'];

			if ( ! isset( $good['goodsName'] ) ) {
				throw new InvalidArgumentException( 'goods element must have goodsName params' );
			}

			if ( ! $good['goodsName'] ) {
				throw new InvalidArgumentException( 'goods element param goodsName must have a value' );
			}

			if ( strlen( $good['goodsName'] ) > 256 ) {
				throw new InvalidArgumentException( 'goods element param goodsName can not more than 256 characters' );
			}

			if ( isset( $good['goodsCategory'] ) && strlen( $good['goodsCategory'] ) > 64 ) {
				throw new InvalidArgumentException( 'goods element param goodsCategory can not more than 64 characters' );
			}

			if ( isset( $good['goodsUnitAmount'] ) ) {
				if ( ! is_array( $good['goodsUnitAmount'] ) ) {
					throw new InvalidArgumentException( 'goods element param goodsUnitAmount must be an array' );
				}
				if ( ! isset( $good['goodsUnitAmount']['currency'] ) ) {
					throw new InvalidArgumentException( 'goods element param goodsUnitAmount must have a currency param' );
				}

				if ( strlen( $good['goodsUnitAmount']['currency'] ) != 3 ) {
					throw new InvalidArgumentException( 'currency of param goodsUnitAmount must be a 3-letter code' );
				}

				if ( ! isset( $good['goodsUnitAmount']['value'] ) ) {
					throw new InvalidArgumentException( 'value of param goodsUnitAmount must be set' );
				}

				if ( ! is_numeric( $good['goodsUnitAmount']['value'] )
					&& intval( $good['goodsUnitAmount']['value'] ) != $good['goodsUnitAmount']['value'] ) {
					throw new InvalidArgumentException( 'value of param goodsUnitAmount must be a number' );
				}

				if ( $good['goodsUnitAmount']['value'] < 1 ) {
					throw new InvalidArgumentException( 'value of param goodsUnitAmount  cannot less than 1' );
				}

				if ( ! is_numeric( $good['goodsUnitAmount']['value'] ) ) {
					$good['goodsUnitAmount']['value'] = intval( $good['goodsUnitAmount']['value'] );
				}
			}

			if ( isset( $good['goodsQuantity'] ) ) {
				if ( ! is_numeric( $good['goodsQuantity'] )
					&& intval( $good['goodsUnitAmount'] ) != $good['goodsUnitAmount'] ) {
					throw new InvalidArgumentException( 'good param goodsQuantity must be a number' );
				}

				if ( $good['goodsQuantity'] < 1 ) {
					throw new InvalidArgumentException( 'good param goodsUnitAmount cannot less than 1' );
				}

				if ( ! is_numeric( $good['goodsQuantity'] ) ) {
					$good['goodsQuantity'] = intval( $good['goodsQuantity'] );
				}
			}

			if ( isset( $good['goodsUrl'] ) ) {
				if ( strlen( $good['goodsUrl'] ) > 2048 ) {
					throw new InvalidArgumentException( 'good param goodsUrl cannot more than 2048 characters' );
				}
			}

			if ( isset( $good['deliveryMethodType'] ) ) {
				$valid_values = array(
					'PHYSICAL',
					'DIGITAL',
				);
				if ( ! in_array( $good['deliveryMethodType'], $valid_values ) ) {
					if ( strlen( $valid_values ) > 32 ) {
						throw new InvalidArgumentException( 'good param goodsUrl cannot more than 32 characters' );
					}
				}
			}
		}

		$this->offsetSet( 'goods', $goods );
	}


	public function get_shipping() {
		return $this->offsetGet( 'shipping' );
	}


	public function set_shipping( $shipping ) {
		if ( isset( $shipping['shippingName'] ) ) {
			if ( ! isset( $shipping['shippingName']['firstName'] ) ) {
				throw new InvalidArgumentException( 'param shippingName of shipping must have a firstName param' );
			}
			if ( ! isset( $shipping['shippingName']['lastName'] ) ) {
				throw new InvalidArgumentException( 'param shippingName of shipping must have a lastName param' );
			}
			if ( strlen( $shipping['shippingName']['firstName'] ) > 32 ) {
				throw new InvalidArgumentException( 'firstName of param shippingName which in shipping can not more than 32 characters' );
			}
			if ( strlen( $shipping['shippingName']['lastName'] ) > 32 ) {
				throw new InvalidArgumentException( 'lastName of param shippingName which in shipping can not more than 32 characters' );
			}
			if ( isset( $shipping['shippingName']['middleName'] ) && strlen( $shipping['shippingName']['middleName'] ) > 32 ) {
				throw new InvalidArgumentException( 'middleName of param shippingName which in shipping can not more than 32 characters' );
			}
			if ( isset( $shipping['shippingName']['fullName'] ) && strlen( $shipping['shippingName']['fullName'] ) > 128 ) {
				throw new InvalidArgumentException( 'fullName of param shippingName which in shipping can not more than 128 characters' );
			}
		}

		if ( isset( $shipping['shippingAddress'] ) ) {
			if ( ! isset( $shipping['shippingAddress']['region'] ) ) {
				throw new InvalidArgumentException( 'param shippingAddress of shipping must have a region param' );
			}
			if ( strlen( $shipping['shippingAddress']['region'] ) != 2 ) {
				throw new InvalidArgumentException( 'region of param shippingAddress which in shipping must be 2 characters' );
			}
			if ( isset( $shipping['shippingAddress']['state'] ) && strlen( $shipping['shippingAddress']['state'] ) > 8 ) {
				throw new InvalidArgumentException( 'state of param shippingAddress which in shipping can not more than 8 characters' );
			}
			if ( isset( $shipping['shippingAddress']['city'] ) && strlen( $shipping['shippingAddress']['city'] ) > 32 ) {
				throw new InvalidArgumentException( 'city of param shippingAddress which in shipping can not more than 32 characters' );
			}
			if (
				isset( $shipping['shippingAddress']['address1'] ) &&
				strlen( $shipping['shippingAddress']['address1'] ) > 256
			) {
				throw new InvalidArgumentException( 'address1 of param shippingAddress which in shipping can not more than 256 characters' );
			}
			if (
				isset( $shipping['shippingAddress']['address2'] ) &&
				strlen( $shipping['shippingAddress']['address2'] ) > 256
			) {
				throw new InvalidArgumentException( 'address2 of param shippingAddress which in shipping can not more than 256 characters' );
			}
			if (
				isset( $shipping['shippingAddress']['zipCode'] ) &&
				strlen( $shipping['shippingAddress']['zipCode'] ) > 32
			) {
				throw new InvalidArgumentException( 'zipCode of param shippingAddress which in shipping can not more than 32 characters' );
			}
		}

		if ( isset( $shipping['shippingCarrier'] ) && strlen( $shipping['shippingCarrier'] ) > 128 ) {
			throw new InvalidArgumentException( 'shippingCarrier of param shipping can not more than 128 characters' );
		}

		if ( isset( $shipping['shippingPhoneNo'] ) && strlen( $shipping['shippingPhoneNo'] ) > 12 ) {
			throw new InvalidArgumentException( 'shippingPhoneNo of param shipping can not more than 12 characters' );
		}

		if ( isset( $shipping['shipToEmail'] ) && strlen( $shipping['shipToEmail'] ) > 64 ) {
			throw new InvalidArgumentException( 'shipToEmail of param shipping can not more than 64 characters' );
		}

		$this->offsetSet( 'shipping', $shipping );
	}


	public function get_buyer() {
		return $this->offsetGet( 'buyer' );
	}


	public function set_buyer( $buyer ) {
		if ( isset( $buyer['referenceBuyerId'] ) && strlen( $buyer['referenceBuyerId'] ) > 64 ) {
			throw new InvalidArgumentException( 'referenceBuyerId of param buyer can not more than 64 characters' );
		}
		if ( isset( $buyer['buyerName'] ) ) {
			if ( isset( $buyer['buyerName']['firstName'] ) && strlen( $buyer['buyerName']['firstName'] ) > 32 ) {
				throw new InvalidArgumentException( 'firstName which in param buyerName of buyer can not more than 32 characters' );
			}
			if ( isset( $buyer['buyerName']['middleName'] ) && strlen( $buyer['buyerName']['middleName'] ) > 32 ) {
				throw new InvalidArgumentException( 'middleName which in param buyerName of buyer can not more than 32 characters' );
			}
			if ( isset( $buyer['buyerName']['lastName'] ) && strlen( $buyer['buyerName']['lastName'] ) > 32 ) {
				throw new InvalidArgumentException( 'lastName which in param buyerName of buyer can not more than 32 characters' );
			}
			if ( isset( $buyer['buyerName']['fullName'] ) && strlen( $buyer['buyerName']['fullName'] ) > 128 ) {
				throw new InvalidArgumentException( 'fullName which in param buyerName of buyer can not more than 128 characters' );
			}
		}
		if ( isset( $buyer['buyerPhoneNo'] ) && strlen( $buyer['buyerPhoneNo'] ) > 24 ) {
			throw new InvalidArgumentException( 'buyerPhoneNo of param buyer can not more than 24 characters' );
		}
		if ( isset( $buyer['buyerEmail'] ) && strlen( $buyer['buyerEmail'] ) > 64 ) {
			throw new InvalidArgumentException( 'buyerEmail of param buyer can not more than 64 characters' );
		}

		if ( isset( $buyer['buyerRegistrationTime'] ) ) {
			if ( strlen( $buyer['buyerRegistrationTime'] ) > 64 ) {
				throw new InvalidArgumentException( 'buyerRegistrationTime of param buyer can not more than 64 characters' );
			}
			$format   = 'Y-m-d\TH:i:sP';
			$dateTime = DateTime::createFromFormat( $format, $buyer['buyerRegistrationTime'] );
			if ( false == $dateTime ) {
				throw new InvalidArgumentException( 'buyerRegistrationTime of param buyer must be a valid datetime' );
			}
		}
		$this->offsetSet( 'buyer', $buyer );
	}


	public function get_merchant() {
		return $this->offsetGet( 'merchant' );
	}

	public function set_merchant( $merchant ) {
		if ( ! is_array( $merchant ) ) {
			throw new InvalidArgumentException( 'param merchant must be an array' );
		}
		if ( ! isset( $merchant['referenceMerchantId'] ) ) {
			throw new InvalidArgumentException( 'param merchant must have a referenceMerchantId param' );
		}
		if ( strlen( $merchant['referenceMerchantId'] ) > 32 ) {
			throw new InvalidArgumentException( 'referenceMerchantId of param merchant can not more than 32 characters' );
		}
		if ( isset( $merchant['merchantMCC'] ) && strlen( $merchant['merchantMCC'] ) > 32 ) {
			throw new InvalidArgumentException( 'merchantMCC of param merchant can not more than 32 characters' );
		}
		if ( isset( $merchant['merchantName'] ) && strlen( $merchant['merchantName'] ) > 256 ) {
			throw new InvalidArgumentException( 'merchantName of param merchant can not more than 256 characters' );
		}
		if ( isset( $merchant['merchantDisplayName'] ) && strlen( $merchant['merchantDisplayName'] ) > 64 ) {
			throw new InvalidArgumentException( 'merchantDisplayName of param merchant can not more than 64 characters' );
		}
		if ( isset( $merchant['merchantAddress'] ) ) {
			if ( ! is_array( $merchant['merchantAddress'] ) ) {
				throw new InvalidArgumentException( 'merchantAddress of param merchant must be an array' );
			}
			if ( ! isset( $merchant['merchantAddress']['region'] ) ) {
				throw new InvalidArgumentException( 'param merchantAddress of merchant must have a region param' );
			}
			if ( strlen( $merchant['merchantAddress']['region'] ) != 2 ) {
				throw new InvalidArgumentException( 'region of param merchantAddress of merchant must be 2 characters' );
			}
			if ( isset( $merchant['merchantAddress']['state'] ) && strlen( $merchant['merchantAddress']['state'] ) > 8 ) {
				throw new InvalidArgumentException( 'state of param merchantAddress of merchant can not more than 8 characters' );
			}
			if ( isset( $merchant['merchantAddress']['city'] ) && strlen( $merchant['merchantAddress']['city'] ) > 32 ) {
				throw new InvalidArgumentException( 'city of param merchantAddress of merchant can not more than 32 characters' );
			}
			if ( isset( $merchant['merchantAddress']['address1'] ) && strlen( $merchant['merchantAddress']['address1'] ) > 256 ) {
				throw new InvalidArgumentException( 'address1 of param merchantAddress of merchant can not more than 256 characters' );
			}
			if ( isset( $merchant['merchantAddress']['address2'] ) && strlen( $merchant['merchantAddress']['address2'] ) > 256 ) {
				throw new InvalidArgumentException( 'address2 of param merchantAddress of merchant can not more than 256 characters' );
			}
			if ( isset( $merchant['merchantAddress']['zipCode'] ) && strlen( $merchant['merchantAddress']['zipCode'] ) > 32 ) {
				throw new InvalidArgumentException( 'zipCode of param merchantAddress of merchant can not more than 32 characters' );
			}
		}
		if ( isset( $merchant['merchantAddress']['merchantRegisterDate'] ) ) {
			$format   = 'Y-m-d\TH:i:sP';
			$dateTime = DateTime::createFromFormat( $format, $merchant['merchantAddress']['merchantRegisterDate'] );
			if ( false == $dateTime ) {
				throw new InvalidArgumentException( 'merchantRegisterDate of param merchantAddress of merchant must be a valid datetime' );
			}
		}
		$this->offsetSet( 'merchant', $merchant );
	}

	public function get_extend_info() {
		return $this->offsetGet( 'extendInfo' );
	}


	public function set_extend_info( $extendInfo ) {
		if ( ! is_array( $extendInfo ) ) {
			throw new InvalidArgumentException( 'param extendInfo must be an array' );
		}
		if ( ! is_array( $extendInfo['chinaExtraTransInfo'] ) ) {
			throw new InvalidArgumentException( 'chinaExtraTransInfo of param extendInfo must be an array' );
		}
		if ( ! isset( $extendInfo['chinaExtraTransInfo']['businessType'] ) ) {
			throw new InvalidArgumentException( 'param chinaExtraTransInfo of extendInfo must have a chinaExtraTransInfo param' );
		}

		$valid               = array( 1, 2, 3, 4, 5 );
		$elements            = explode( '|', $extendInfo['chinaExtraTransInfo']['merchantId'] );
		$intersect           = array_intersect( $valid, $elements );
		$has_unvalid_element = false;
		foreach ( $elements as $key => $value ) {
			if ( ! in_array( $value, $intersect ) ) {
				$has_unvalid_element = true;
				break;
			}
		}

		if ( $has_unvalid_element ) {
			throw new InvalidArgumentException( 'businessType of param chinaExtraTransInfo of extendInfo must be 1,2,3,4,5 or Combination of 1,2,3,4,5' );
		}

		if ( strlen( $extendInfo['chinaExtraTransInfo']['merchantId'] ) > 16 ) {
			throw new InvalidArgumentException( 'merchantId of param chinaExtraTransInfo of extendInfo can not more than 16 characters' );
		}

		if ( isset( $extendInfo['chinaExtraTransInfo']['flightNumber'] ) && strlen( $extendInfo['chinaExtraTransInfo']['flightNumber'] ) > 1000 ) {
			throw new InvalidArgumentException( 'flightNumber of param chinaExtraTransInfo of extendInfo can not more than 1000 characters' );
		}

		if ( isset( $extendInfo['chinaExtraTransInfo']['departureTime'] ) ) {
			$format   = 'Y-m-d\TH:i:sP';
			$dateTime = DateTime::createFromFormat( $format, $extendInfo['chinaExtraTransInfo']['departureTime'] );
			if ( false == $dateTime ) {
				throw new InvalidArgumentException( 'departureTime of param chinaExtraTransInfo of extendInfo must be a valid datetime' );
			}

			if ( strlen( $extendInfo['chinaExtraTransInfo']['departureTime'] ) > 1000 ) {
				throw new InvalidArgumentException( 'departureTime of param chinaExtraTransInfo of extendInfo can not more than 1000 characters' );
			}
		}

		if ( isset( $extendInfo['chinaExtraTransInfo']['hotelName'] ) && strlen( $extendInfo['chinaExtraTransInfo']['hotelName'] ) > 1000 ) {
			throw new InvalidArgumentException( 'hotelName of param chinaExtraTransInfo of extendInfo can not more than 1000 characters' );
		}

		if ( isset( $extendInfo['chinaExtraTransInfo']['checkinTime'] ) ) {
			$format   = 'Y-m-d\TH:i:sP';
			$dateTime = DateTime::createFromFormat( $format, $extendInfo['chinaExtraTransInfo']['checkinTime'] );
			if ( false == $dateTime ) {
				throw new InvalidArgumentException( 'checkinTime of param chinaExtraTransInfo of extendInfo must be a valid datetime' );
			}

			if ( strlen( $extendInfo['chinaExtraTransInfo']['checkinTime'] ) > 1000 ) {
				throw new InvalidArgumentException( 'checkinTime of param chinaExtraTransInfo of extendInfo can not more than 1000 characters' );
			}
		}

		if ( isset( $extendInfo['chinaExtraTransInfo']['admissionNoticeUrl'] ) && strlen( $extendInfo['chinaExtraTransInfo']['admissionNoticeUrl'] ) > 1000 ) {
			throw new InvalidArgumentException( 'admissionNoticeUrl of param chinaExtraTransInfo of extendInfo can not more than 1000 characters' );
		}

		if ( isset( $extendInfo['chinaExtraTransInfo']['goodsInfo'] ) && strlen( $extendInfo['chinaExtraTransInfo']['goodsInfo'] ) > 2000 ) {
			throw new InvalidArgumentException( 'goodsInfo of param chinaExtraTransInfo of extendInfo can not more than 2000 characters' );
		}

		if ( isset( $extendInfo['chinaExtraTransInfo']['totalQuantity'] ) ) {
			if ( ! is_numeric( $extendInfo['chinaExtraTransInfo']['totalQuantity'] ) && intval( $extendInfo['chinaExtraTransInfo']['totalQuantity'] ) != $extendInfo['chinaExtraTransInfo']['totalQuantity'] ) {
				throw new InvalidArgumentException( 'totalQuantity of param chinaExtraTransInfo of extendInfo must be numeric' );
			}

			if ( $extendInfo['chinaExtraTransInfo']['totalQuantity'] < 0 ) {
				throw new InvalidArgumentException( 'totalQuantity of param chinaExtraTransInfo of extendInfo must be greater than or equal to 0' );
			}
		}

		$this->offsetSet( 'extendInfo', $extendInfo );
	}


	public function get_transit() {
		return $this->offsetGet( 'transit' );
	}


	public function set_transit( $transit ) {
		if ( ! is_array( $transit ) ) {
			throw new InvalidArgumentException( 'param transit must be an array' );
		}

		if ( isset( $transit['transitType'] ) ) {
			$valid = array( 'FLIGHT', 'TRAIN', 'CRUISE', 'COACH' );
			if ( ! in_array( $transit['transitType'], $valid ) ) {
				throw new InvalidArgumentException( 'transitType of param transit must be FLIGHT,TRAIN,CRUISE,COACH' );
			}
		}

		if ( isset( $transit['legs'] ) ) {
			if ( ! is_array( $transit['legs'] ) ) {
				throw new InvalidArgumentException( 'legs of param transit must be an array' );
			}

			if ( isset( $transit['legs']['departureTime'] ) ) {
				$format   = 'Y-m-d\TH:i:sP';
				$dateTime = DateTime::createFromFormat( $format, $transit['legs']['departureTime'] );
				if ( false == $dateTime ) {
					throw new InvalidArgumentException( 'departureTime of param legs of transit must be a valid datetime' );
				}
			}

			if ( isset( $transit['legs']['arrivalTime'] ) ) {
				$format   = 'Y-m-d\TH:i:sP';
				$dateTime = DateTime::createFromFormat( $format, $transit['legs']['arrivalTime'] );
				if ( false == $dateTime ) {
					throw new InvalidArgumentException( 'arrivalTime of param legs of transit must be a valid datetime' );
				}
			}

			if ( isset( $transit['legs']['departureAddress'] ) ) {
				if ( ! is_array( $transit['legs']['departureAddress'] ) ) {
					throw new InvalidArgumentException( 'departureAddress of param legs of transit must be an array' );
				}

				if ( isset( $transit['legs']['departureAddress']['region'] ) && strlen( $transit['legs']['departureAddress']['region'] ) != 2 ) {
					throw new InvalidArgumentException( 'region of param departureAddress of legs of transit must be 2 characters' );
				}

				if ( isset( $transit['legs']['departureAddress']['state'] )
					&& strlen( $transit['legs']['departureAddress']['state'] ) > 8
				) {
					throw new InvalidArgumentException( 'state of param departureAddress of legs of transit can not more than 8 characters' );
				}

				if ( isset( $transit['legs']['departureAddress']['city'] )
					&& strlen( $transit['legs']['departureAddress']['city'] ) > 32
				) {
					throw new InvalidArgumentException( 'city of param departureAddress of legs of transit can not more than 32 characters' );
				}

				if ( isset( $transit['legs']['departureAddress']['address1'] )
					&& strlen( $transit['legs']['departureAddress']['address1'] ) > 256
				) {
					throw new InvalidArgumentException( 'address1 of param departureAddress of legs of transit can not more than 256 characters' );
				}

				if ( isset( $transit['legs']['departureAddress']['address2'] )
					&& strlen( $transit['legs']['departureAddress']['address2'] ) > 256
				) {
					throw new InvalidArgumentException( 'address2 of param departureAddress of legs of transit can not more than 256 characters' );
				}
			}

			if ( isset( $transit['legs']['arrivalAddress'] ) ) {
				if ( ! is_array( $transit['legs']['arrivalAddress'] ) ) {
					throw new InvalidArgumentException( 'arrivalAddress of param legs of transit must be an array' );
				}

				if ( isset( $transit['legs']['arrivalAddress']['region'] ) && strlen( $transit['legs']['arrivalAddress']['region'] ) != 2 ) {
					throw new InvalidArgumentException( 'region of param arrivalAddress of legs of transit must be 2 characters' );
				}

				if ( isset( $transit['legs']['arrivalAddress']['state'] )
					&& strlen( $transit['legs']['arrivalAddress']['state'] ) > 8
				) {
					throw new InvalidArgumentException( 'state of param arrivalAddress of legs of transit can not more than 8 characters' );
				}

				if ( isset( $transit['legs']['arrivalAddress']['city'] )
					&& strlen( $transit['legs']['arrivalAddress']['city'] ) > 32
				) {
					throw new InvalidArgumentException( 'city of param arrivalAddress of legs of transit can not more than 32 characters' );
				}

				if ( isset( $transit['legs']['arrivalAddress']['address1'] )
					&& strlen( $transit['legs']['arrivalAddress']['address1'] ) > 256
				) {
					throw new InvalidArgumentException( 'address1 of param arrivalAddress of legs of transit can not more than 256 characters' );
				}

				if ( isset( $transit['legs']['arrivalAddress']['address2'] )
					&& strlen( $transit['legs']['arrivalAddress']['address2'] ) > 256
				) {
					throw new InvalidArgumentException( 'address2 of param arrivalAddress of legs of transit can not more than 256 characters' );
				}
			}

			if ( isset( $transit['legs']['carrierName'] ) && strlen( $transit['legs']['carrierName'] ) > 128 ) {
				throw new InvalidArgumentException( 'carrierName of param legs of transit can not more than 128 characters' );
			}

			if ( isset( $transit['legs']['carrierNo'] ) && strlen( $transit['legs']['carrierNo'] ) > 64 ) {
				throw new InvalidArgumentException( 'carrierNo of param legs of transit can not more than 128 characters' );
			}

			if ( isset( $transit['legs']['classsType'] ) ) {
				$valid = array( 'FIRSTLEVEL', 'SECONDLEVEL', 'THIRDLEVEL' );
				if ( ! in_array( $transit['legs']['classsType'], $valid ) ) {
					throw new InvalidArgumentException( 'classsType of param legs of transit must be FIRSTLEVEL,SECONDLEVEL,THIRDLEVEL' );
				}

				if ( strlen( $transit['legs']['classsType'] ) > 32 ) {
					throw new InvalidArgumentException( 'classsType of param legs of transit can not more than 32 characters' );
				}
			}
		}

		if ( isset( $transit['passengers'] ) ) {
			if ( ! is_array( $transit['passengers'] ) ) {
				throw new InvalidArgumentException( 'passengers of param transit must be an array' );
			}
			if ( isset( $transit['passengers']['passengerName'] ) ) {
				if ( ! is_array( $transit['passengers']['passengerName'] ) ) {
					throw new InvalidArgumentException( 'passengerName of param passengers of transit must be an array' );
				}

				if ( isset( $transit['passengers']['passengerName']['firstName'] ) && strlen( $transit['passengers']['passengerName']['firstName'] ) > 32 ) {
					throw new InvalidArgumentException( 'firstName of param passengerName of passengers of transit can not more than 32 characters' );
				}

				if ( isset( $transit['passengers']['passengerName']['middleName'] ) && strlen( $transit['passengers']['passengerName']['middleName'] ) > 32 ) {
					throw new InvalidArgumentException( 'middleName of param passengerName of passengers of transit can not more than 32 characters' );
				}

				if ( isset( $transit['passengers']['passengerName']['lastName'] ) && strlen( $transit['passengers']['passengerName']['lastName'] ) > 32 ) {
					throw new InvalidArgumentException( 'lastName of param passengerName of passengers of transit can not more than 32 characters' );
				}

				if ( isset( $transit['passengers']['passengerName']['fullName'] ) && strlen( $transit['passengers']['passengerName']['fullName'] ) > 128 ) {
					throw new InvalidArgumentException( 'fullName of param passengerName of passengers of transit can not more than 128 characters' );
				}
			}

			if (
				isset( $transit['passengers']['passengerEmail'] )
				&& strlen( $transit['passengers']['passengerEmail'] ) > 64
			) {
				throw new InvalidArgumentException( 'passengerEmail of param passengers of transit can not more than 64 characters' );
			}

			if (
				isset( $transit['passengers']['passengerPhoneNo'] )
				&& strlen( $transit['passengers']['passengerPhoneNo'] ) > 32
			) {
				throw new InvalidArgumentException( 'passengerPhoneNo of param passengers of transit can not more than 32 characters' );
			}
		}

		$this->offsetSet( 'transit', $transit );
	}


	public function get_lodging() {
		return $this->offsetGet( 'lodging' );
	}


	public function set_lodging( $lodging ) {
		if ( ! is_array( $lodging ) ) {
			throw new InvalidArgumentException( 'lodging of param must be an array' );
		}
		if ( isset( $lodging['hotelName'] ) && strlen( $lodging['hotelName'] ) > 128 ) {
			throw new InvalidArgumentException( 'hotelName of param lodging can not more than 128 characters' );
		}
		if ( isset( $lodging['hotelAddress'] ) ) {
			if ( ! is_array( $lodging['hotelAddress'] ) ) {
				throw new InvalidArgumentException( 'hotelAddress of param lodging must be an array' );
			}
			if ( isset( $lodging['hotelAddress']['region'] ) && strlen( $lodging['hotelAddress']['region'] ) != 2 ) {
				throw new InvalidArgumentException( 'region of param hotelAddress of lodging must be 2 characters' );
			}
			if ( isset( $lodging['hotelAddress']['state'] )
				&& strlen( $lodging['hotelAddress']['state'] ) > 8
			) {
				throw new InvalidArgumentException( 'state of param hotelAddress of lodging can not more than 8 characters' );
			}
			if ( isset( $lodging['hotelAddress']['city'] ) && strlen( $lodging['hotelAddress']['city'] ) > 32 ) {
				throw new InvalidArgumentException( 'city of param hotelAddress of lodging can not more than 32 characters' );
			}
			if ( isset( $lodging['hotelAddress']['address1'] ) && strlen( $lodging['hotelAddress']['address1'] ) > 256 ) {
				throw new InvalidArgumentException( 'address1 of param hotelAddress of lodging can not more than 256 characters' );
			}
			if ( isset( $lodging['hotelAddress']['address2'] ) && strlen( $lodging['hotelAddress']['address2'] ) > 256 ) {
				throw new InvalidArgumentException( 'address2 of param hotelAddress of lodging can not more than 256 characters' );
			}
		}
		if ( isset( $lodging['checkInDate'] ) ) {
			$format   = 'Y-m-d\TH:i:sP';
			$dateTime = DateTime::createFromFormat( $format, $lodging['checkInDate'] );
			if ( false == $dateTime ) {
				throw new InvalidArgumentException( 'checkInDate of param lodging must be a valid date' );
			}
		}
		if ( isset( $lodging['checkOutDate'] ) ) {
			$format   = 'Y-m-d\TH:i:sP';
			$dateTime = DateTime::createFromFormat( $format, $lodging['checkOutDate'] );
			if ( false == $dateTime ) {
				throw new InvalidArgumentException( 'checkOutDate of param lodging must be a valid date' );
			}
		}
		if ( isset( $lodging['numberOfNights'] ) ) {
			if ( ! is_numeric( $lodging['numberOfNights'] ) && intval( $lodging['numberOfNights'] ) != $lodging['numberOfNights'] ) {
				throw new InvalidArgumentException( 'numberOfNights of param lodging must be an integer' );
			}
			if ( $lodging['numberOfNights'] < 1 ) {
				throw new InvalidArgumentException( 'numberOfNights of param lodging must be greater than 0' );
			}
		}
		if ( isset( $lodging['numberOfRooms'] ) ) {
			if ( ! is_numeric( $lodging['numberOfRooms'] ) && intval( $lodging['numberOfRooms'] ) != $lodging['numberOfRooms'] ) {
				throw new InvalidArgumentException( 'numberOfRooms of param lodging must be an integer' );
			}
			if ( $lodging['numberOfRooms'] < 1 ) {
				throw new InvalidArgumentException( 'numberOfRooms of param lodging must be greater than 0' );
			}
		}
		if ( isset( $lodging['guestNames'] ) ) {
			if ( ! is_array( $lodging['guestNames'] ) ) {
				throw new InvalidArgumentException( 'guestNames of param lodging must be an array' );
			}

			if ( count( $lodging['guestNames'] ) > 100 ) {
				throw new InvalidArgumentException( 'guestNames of param lodging can not more than 100 elements' );
			}

			foreach ( $lodging['guestNames'] as $guest_name ) {
				if ( ! is_array( $guest_name ) ) {
					throw new InvalidArgumentException( 'guestNames element of param lodging must be an array' );
				}
				if ( isset( $guest_name['firstName'] ) && $guest_name['firstName'] > 32 ) {
					throw new InvalidArgumentException( 'firstName of element of guestNames of lodging can not more than 32 characters' );
				}
				if ( isset( $guest_name['middleName'] ) && $guest_name['middleName'] > 32 ) {
					throw new InvalidArgumentException( 'middleName of element of guestNames of lodging can not more than 32 characters' );
				}
				if ( isset( $guest_name['lastName'] ) && $guest_name['lastName'] > 32 ) {
					throw new InvalidArgumentException( 'lastName of element of guestNames of lodging can not more than 32 characters' );
				}
				if ( isset( $guest_name['fullName'] ) && $guest_name['fullName'] > 128 ) {
					throw new InvalidArgumentException( 'fullName of element of guestNames of lodging can not more than 128 characters' );
				}
			}
		}
		$this->offsetSet( 'lodging', $lodging );
	}


	public function get_gaming() {
		return $this->offsetGet( 'gaming' );
	}


	public function set_gaming( $gaming ) {
		if ( ! is_array( $gaming ) ) {
			throw new InvalidArgumentException( 'param gaming must be an array' );
		}
		if ( isset( $gaming['gameName'] ) && strlen( $gaming['gameName'] ) > 128 ) {
			throw new InvalidArgumentException( 'gameName of param gaming can not more than 128 characters' );
		}
		if ( isset( $gaming['toppedUpUser'] ) && strlen( $gaming['toppedUpUser'] ) > 128 ) {
			throw new InvalidArgumentException( 'toppedUpUser of param gaming can not more than 128 characters' );
		}
		if ( isset( $gaming['toppedUpEmail'] ) && strlen( $gaming['toppedUpEmail'] ) > 64 ) {
			throw new InvalidArgumentException( 'toppedUpEmail of param gaming can not more than 64 characters' );
		}
		if ( ! isset( $gaming['toppedUpPhoneNo'] ) && strlen( $gaming['toppedUpPhoneNo'] ) > 32 ) {
			throw new InvalidArgumentException( 'toppedUpPhoneNo of param gaming can not more than 32 characters' );
		}
		$this->offsetSet( 'gaming', $gaming );
	}
}
