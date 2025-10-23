<?php
/**
 * Antom Payment Gateways Common Functions
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'antom_request' ) ) {
	/**
	 * Antom get safe request params
	 *
	 * @param string $name the param name you want to get
	 * @param mixed  $default the default value if the name you want to get is not exist
	 * @param string $type the type of the param you want to get,it will be filtering by itself safe method
	 *
	 * @return array|mixed
	 * @since: 1.0.0
	 * @author:Antom
	 */
	function antom_request( $name = '', $default = '', $type = 'string' ) {

		$is_verify = false;
		if ( isset( $_POST['form_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['form_nonce'] ), 'antom_form_nonce' ) ) {
			$is_verify = true;
		}

		$param = isset( $_REQUEST[ $name ] ) ? sanitize_text_field( $_REQUEST[ $name ] ) : sanitize_text_field( $default );

		$allow_type = array(
			'string',
			'int',
			'intval',
			'float',
			'floatval',
			'bool',
			'json',
			'array',
		);

		if ( ! in_array( $type, $allow_type ) ) {
			$type = 'string';
		}

		$safe_methods = 'sanitize_textarea_field';
		switch ( $type ) {
			case 'int':
			case 'intval':
				$safe_methods = 'absint';
				break;
			case 'float':
			case 'floatval':
				$safe_methods = 'floatval';
				break;
			case 'bool':
				$safe_methods = 'boolval';
				break;
			case 'json':
				$safe_methods = 'wp_unslash';
				break;
		}

		if ( 'json' == $type ) {
			return json_decode( ( $safe_methods )( $param ), true );
		} elseif ( 'array' == $type ) {
			return (array) $param;
		} else {
			return ( $safe_methods )( $param );
		}

		return $param;
	}
}


if ( ! function_exists( 'antom_is_active' ) ) {
	function antom_is_active() {
		return \Antom_Payment_Gateways_Options::get_instance()->get_option(
			\Antom_Payment_Gateways_Statement::ANTOM_ACTIVE_STATE_FIELD
		) == \Antom_Payment_Gateways_Statement::ANTOM_ACTIVE_STATE;
	}
}

if ( ! function_exists( 'antom_load_core_setting' ) ) {
	function antom_load_core_setting() {
		$core_setting = Antom_Payment_Gateways_Options::get_instance()->get_option( Antom_Payment_Gateways_Statement::ANTOM_CORE_SETTING_FIELD );
		if ( ! $core_setting ) {
			$core_setting = array(
				'clientid'         => '',
				'public_key'       => '',
				'private_key'      => '',
				'test_mode'        => '',
				'test_clientid'    => '',
				'test_public_key'  => '',
				'test_private_key' => '',
				'prod_currency'    => '',
				'test_currency'    => '',
			);
		}

		/**
		 * With this hook ,you can modify the core settings before it's being used.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'antom_payment_gateways_get_core_settings_values', $core_setting );
	}
}

if ( ! function_exists( 'antom_get_core_settings' ) ) {
	function antom_get_core_settings() {
		$core_settings   = antom_load_core_setting();
		$return_settings = array(
			'clientid'            => isset( $core_settings['clientid'] ) ? $core_settings['clientid'] : '',
			'public_key'          => isset( $core_settings['public_key'] ) ? $core_settings['public_key'] : '',
			'private_key'         => isset( $core_settings['private_key'] ) ? $core_settings['private_key'] : '',
			'settlement_currency' => isset( $core_settings['prod_currency'] ) ? $core_settings['prod_currency'] : '',
			'test_mode'           => isset( $core_settings['test_mode'] ) ? $core_settings['test_mode'] : 0,
		);
		if ( 1 === intval( $return_settings['test_mode'] ) ) {
			$return_settings['clientid']            = isset( $core_settings['test_clientid'] ) ? $core_settings['test_clientid'] : '';
			$return_settings['public_key']          = isset( $core_settings['test_public_key'] ) ? $core_settings['test_public_key'] : '';
			$return_settings['private_key']         = isset( $core_settings['test_private_key'] ) ? $core_settings['test_private_key'] : '';
			$return_settings['settlement_currency'] = isset( $core_settings['test_currency'] ) ? $core_settings['test_currency'] : '';
		}

		return $return_settings;
	}
}

if ( ! function_exists( 'antom_init_setting_menus' ) ) {
	function antom_init_setting_menus( $section ) {
		$methods = antom_get_payment_methods();
		$menus   = array(
			array(
				'slug'  => Antom_Payment_Gateways_Statement::ANTOM_CORE_SETTING_SLUG,
				'title' => 'Core Settings',
			),
		);
		foreach ( $methods as $method ) {
			$menus[] = array(
				'slug'  => $method['slug'],
				'title' => $method['menu_title'],
			);
		}

		?>
		<ul class="antom-payment-gateway-settings">
			<?php
			foreach ( $menus as $menu ) {
				$active = '';
				if ( $menu['slug'] === $section ) {
					$active = 'current';
				}
				$link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $menu['slug'] );
				?>
				<li><a class="<?php echo esc_html( $active ); ?>"
						href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $menu['title'] ); ?></a>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
}

if ( ! function_exists( 'antom_is_post_request' ) ) {
	function antom_is_post_request() {
		return isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'];
	}
}


if ( ! function_exists( 'antom_get_notify_url' ) ) {
	function antom_get_notify_url( $type = 'payment' ) {
		return add_query_arg( 'wc-api', 'antom_' . $type . '_notify', home_url( '/' ) );
	}
}


if ( ! function_exists( 'antom_notify_response' ) ) {
	function antom_notify_response( $resultCode = 'SUCCESS', $resultStatus = 'S', $resultMessage = 'success' ) {
		$response = array(
			'result' => array(
				'resultCode'    => $resultCode,
				'resultStatus'  => $resultStatus,
				'resultMessage' => $resultMessage,
			),
		);
		echo wp_json_encode( $response );
		exit;
	}
}

if ( ! function_exists( 'antom_get_device_os_type' ) ) {
	function antom_get_device_os_type() {
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '';

		if ( strpos( $user_agent, 'iPhone' ) !== false || strpos(
			$user_agent,
			'iPad'
		) !== false || strpos( $user_agent, 'iPod' ) !== false ) {
			return 'IOS';
		} elseif ( strpos( $user_agent, 'Android' ) !== false ) {
			return 'ANDROID';
		} else {
			return 'unknown';
		}
	}
}


if ( ! function_exists( 'antom_generate_payment_request_id' ) ) {
	/**
	 * Generate a payment request ID based on the order ID.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return string
	 * @since: 1.0.0
	 * @author:Antom
	 */
	function antom_generate_payment_request_id( $order_id ) {
		// antom request payment id's length is max 64 character.
		$order_id_length    = strlen( $order_id );
		$payment_request_id = time();// length 10
		if ( $order_id_length <= 49 ) {
			$payment_request_id .= 'antom' . $order_id;
		} else {
			$payment_request_id .= $order_id;
		}
		return $payment_request_id;
	}
}

if ( ! function_exists( 'antom_get_order_id_by_payment_request_id' ) ) {
	/**
	 * Verify the payment request ID format. if the payment request ID format is valid. return order ID, otherwise return false.
	 *
	 * @param $payment_request_id
	 *
	 * @return false|int
	 * @since: 1.0.0
	 * @author:Antom
	 */
	function antom_get_order_id_by_payment_request_id( $payment_request_id ) {
		if ( false !== strpos( $payment_request_id, 'antom' ) ) {
			$payment_request_array = explode( 'antom', $payment_request_id );
		} else {
			$payment_request_array[0] = substr( $payment_request_id, 0, 10 );
			$payment_request_array[1] = substr( $payment_request_id, 10 );
		}

		if ( ! isset( $payment_request_array[1] ) ) {
			return $payment_request_id;
		}

		return absint( $payment_request_array[1] );
	}
}

/**
 * In a normal payment process, this query is not required and is only used as a fallback logic in extremely special exception scenarios.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'antom_get_order_id_by_payment_request_id_from_database' ) ) {
	function antom_get_order_id_by_payment_request_id_from_database( $payment_request_id ) {
		$args = array(
			'meta_key'    => Antom_Payment_Gateways_Statement::ANTOM_REQUEST_PAYMENT_KEY,
			'meta_value'  => $payment_request_id,
			'post_type'   => 'shop_order',
			'numberposts' => 1,
			'limit'       => 1,
		);

		$orders = wc_get_orders( $args );

		if ( ! empty( $orders ) ) {
			return '';
		}

		foreach ( $orders as $order ) {
			return $order->get_id();
		}

		return '';
	}
}

if ( ! function_exists( 'antom_order_amount_value_to_decimal' ) ) {
	function antom_order_amount_value_to_decimal( $amount, $currency_symbol ) {
		$currency                    = strtoupper( $currency_symbol );
		$three_decimal_currency_list = array(
			'BHD',
			'LYD',
			'JOD',
			'IQD',
			'KWD',
			'OMR',
			'TND',
		);
		$zero_decimal_currency_list  = array(
			'BYR',
			'XOF',
			'BIF',
			'XAF',
			'KMF',
			'XOF',
			'DJF',
			'XPF',
			'GNF',
			'JPY',
			'KRW',
			'PYG',
			'RWF',
			'VUV',
			'VND',
		);

		if ( in_array( $currency, $three_decimal_currency_list, true ) ) {
			$value = (int) ( $amount * 1000 );
		} elseif ( in_array( $currency, $zero_decimal_currency_list, true ) ) {
			$value = floor( $amount );
		} else {
			$value = round( $amount * 100 );
		}

		return $value;
	}
}

if ( ! function_exists( 'getallheaders' ) ) {
	function getallheaders() {
		$headers = array();
		foreach ( $_SERVER as $name => $value ) {
			if ( 'HTTP_' === substr( $name, 0, 5 ) ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}

		return $headers;
	}

}
