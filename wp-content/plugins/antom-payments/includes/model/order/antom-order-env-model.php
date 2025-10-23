<?php
/**
 * Antom_Order_Env_Model
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../antom-model-trait.php';

class Antom_Order_Env_Model implements ArrayAccess {
	use Antom_Model_Trait;


	public function get_terminal_type() {
		return $this->offsetGet( 'terminalType' );
	}


	public function set_terminal_type( $terminalType ) {
		$valid = array(
			'WEB',
			'WAP',
			'APP',
			'MINI_APP',
		);
		if ( ! in_array( $terminalType, $valid ) ) {
			throw new InvalidArgumentException( 'terminalType must be WEB,WAP,APP or MINI_APP' );
		}
		$this->offsetSet( 'terminalType', $terminalType );
	}


	public function get_os_type() {
		return $this->offsetGet( 'osType' );
	}


	public function set_os_type( $osType ) {
		$valid = array( 'IOS', 'ANDROID' );
		if ( ! in_array( $osType, $valid ) ) {
			throw new InvalidArgumentException( 'osType must be IOS or ANDROID' );
		}
		$this->offsetSet( 'osType', $osType );
	}


	public function get_browser_info() {
		return $this->offsetGet( 'browserInfo' );
	}


	public function set_browser_info( $browserInfo ) {
		if ( isset( $browserInfo['acceptHeader'] ) && strlen( $browserInfo['acceptHeader'] ) > 2048 ) {
			throw new InvalidArgumentException( 'acceptHeader of browserInfo must be less than 2049 characters' );
		}
		if ( isset( $browserInfo['javaEnabled'] ) ) {
			$valid = array( true, false );
			if ( ! in_array( $browserInfo['javaEnabled'], $valid ) ) {
				throw new InvalidArgumentException( 'javaEnabled of browserInfo must be true or false' );
			}
		}
		if ( isset( $browserInfo['javaScriptEnabled'] ) ) {
			$valid = array( true, false );
			if ( ! in_array( $browserInfo['javaScriptEnabled'], $valid ) ) {
				throw new InvalidArgumentException( 'javaScriptEnabled of browserInfo must be true or false' );
			}
		}
		if ( isset( $browserInfo['language'] ) && $browserInfo['language'] > 32 ) {
			throw new InvalidArgumentException( 'language of browserInfo must be less than 33 characters' );
		}
		if ( isset( $browserInfo['userAgent'] ) && $browserInfo['userAgent'] > 2048 ) {
			throw new InvalidArgumentException( 'userAgent of browserInfo must be less than 2049 characters' );
		}
		$this->offsetSet( 'browserInfo', $browserInfo );
	}


	public function get_color_depth() {
		return $this->offsetGet( 'colorDepth' );
	}


	public function set_color_depth( $colorDepth ) {
		$valid = array( 0, 1, 4, 8, 15, 16, 24, 30, 32, 48 );
		if ( ! in_array( $colorDepth, $valid ) ) {
			throw new InvalidArgumentException( 'colorDepth must be 0,1,4,8,15,16,24,30,32 or 48' );
		}
		$this->offsetSet( 'colorDepth', $colorDepth );
	}


	public function get_screen_height() {
		return $this->offsetGet( 'screenHeight' );
	}


	public function set_screen_height( $screenHeight ) {
		if ( ! is_numeric( $screenHeight ) && intval( $screenHeight ) != $screenHeight ) {
			throw new InvalidArgumentException( 'screenHeight must be numeric' );
		}
		if ( $screenHeight < 1 ) {
			throw new InvalidArgumentException( 'screenHeight must be greater than or equal with 1' );
		}
		$this->offsetSet( 'screenHeight', $screenHeight );
	}


	public function get_screen_width() {
		return $this->offsetGet( 'screenWidth' );
	}


	public function set_screen_width( $screenWidth ) {
		if ( ! is_numeric( $screenWidth ) && intval( $screenWidth ) != $screenWidth ) {
			throw new InvalidArgumentException( 'screenWidth must be numeric' );
		}
		if ( $screenWidth < 1 ) {
			throw new InvalidArgumentException( 'screenWidth must be greater than or equal with 1' );
		}
		$this->offsetSet( 'screenWidth', $screenWidth );
	}


	public function get_time_zone_offset() {
		return $this->offsetGet( 'timeZoneOffset' );
	}


	public function set_time_zone_offset( $timeZoneOffset ) {
		if ( $timeZoneOffset < - 720 ) {
			throw new InvalidArgumentException( 'timeZoneOffset must be greater than or equal with -720' );
		}
		if ( $timeZoneOffset > 720 ) {
			throw new InvalidArgumentException( 'timeZoneOffset must be less than or equal with 720' );
		}
		$this->offsetSet( 'timeZoneOffset', $timeZoneOffset );
	}


	public function get_device_brand() {
		return $this->offsetGet( 'deviceBrand' );
	}


	public function set_device_brand( $deviceBrand ) {
		if ( strlen( $deviceBrand ) > 64 ) {
			throw new InvalidArgumentException( 'deviceBrand must be less than 65 characters' );
		}
		$this->offsetSet( 'deviceBrand', $deviceBrand );
	}


	public function get_device_model() {
		return $this->offsetGet( 'deviceModel' );
	}


	public function set_device_model( $deviceModel ) {
		if ( strlen( $deviceModel ) > 128 ) {
			throw new InvalidArgumentException( 'deviceModel must be less than 129 characters' );
		}
		$this->offsetSet( 'deviceModel', $deviceModel );
	}


	public function get_device_token_id() {
		return $this->offsetGet( 'deviceTokenId' );
	}

	public function set_device_token_id( $deviceTokenId ) {
		if ( strlen( $deviceTokenId ) > 64 ) {
			throw new InvalidArgumentException( 'deviceTokenId must be less than 65 characters' );
		}
		$this->offsetSet( 'deviceTokenId', $deviceTokenId );
	}


	public function get_client_ip() {
		return $this->offsetGet( 'clientIp' );
	}


	public function set_client_ip( $clientIp ) {
		if ( strlen( $clientIp ) > 64 ) {
			throw new InvalidArgumentException( 'clientIp must be less than 65 characters' );
		}
		$this->offsetSet( 'clientIp', $clientIp );
	}


	public function get_device_language() {
		return $this->offsetGet( 'deviceLanguage' );
	}


	public function set_device_language( $deviceLanguage ) {
		if ( strlen( $deviceLanguage ) > 32 ) {
			throw new InvalidArgumentException( 'deviceLanguage must be less than 33 characters' );
		}
		$this->offsetSet( 'deviceLanguage', $deviceLanguage );
	}


	public function get_device_id() {
		return $this->offsetGet( 'deviceId' );
	}


	public function set_device_id( $deviceId ) {
		if ( strlen( $deviceId ) > 64 ) {
			throw new InvalidArgumentException( 'deviceId must be less than 65 characters' );
		}
		$this->offsetSet( 'deviceId', $deviceId );
	}


	public function get_extend_info() {
		return $this->offsetGet( 'extendInfo' );
	}


	public function set_extend_info( $extendInfo ) {
		if ( strlen( $extendInfo ) > 2048 ) {
			throw new InvalidArgumentException( 'extendInfo must be less than 2049 characters' );
		}
		$this->offsetSet( 'extendInfo', $extendInfo );
	}
}
