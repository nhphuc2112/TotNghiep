<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WC_Gateway_Antom_Common extends WC_Payment_Gateway {
	/**
	 * The payment gateway support currencies.
	 *
	 * @var array
	 */
	protected $currency;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->init_settings();

		$this->setup_props();

		// Load the settings.
		$this->init_form_fields();

		// Actions.
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
		add_action(
			'woocommerce_scheduled_subscription_payment_' . $this->id,
			array(
				$this,
				'process_subscription_payment',
			),
			10,
			2
		);
		add_action(
			'woocommerce_api_antom_inquiry_webhook',
			array(
				$this,
				'inquiry_handler',
			)
		);
		add_action(
			'woocommerce_api_antom_payment_notify',
			array(
				$this,
				'payment_notify_handler',
			)
		);
	}

	/**
	 * The Common Func to Set Payment Gateway Settings.
	 *
	 * @return void
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	protected function setup_props() {
		/**
		 * With this hook, we can add our custom cion to show.
		 *
		 * @since 1.0.0
		 */
		$this->icon       = apply_filters(
			'antom_payment_gateway_icon_' . $this->id,
			$this->get_pay_setting( 'icon' )
		);
		$this->has_fields = true;

		$this->supports = array(
			'products',
		);

		$this->currency = $this->get_pay_setting( 'support_currencies' );

		$this->title = $this->get_option( 'title' );
		if ( ! $this->title ) {
			$this->title = $this->get_pay_setting( 'default_display_name' );
		}
		$description = $this->get_option( 'description' );
		if ( ! $description ) {
			$description = __( 'Pay via', 'antom-payments' ) . ' ' . $this->title;
		}

		$antom_core_setting = antom_get_core_settings();
		if ( 1 === intval( $antom_core_setting['test_mode'] ) ) {
			$description .= '<p class="antom-test-mode-warning">run in antom test mode</p>';
		}

		$this->description = $description;

		$this->method_title       = $this->get_pay_setting( 'method_title', $this->get_pay_setting( 'pay_name' ) );
		$this->method_description = $this->get_pay_setting( 'method_description' );
		if ( $this->method_description ) {
			$this->method_description .= __( ' . ', 'antom-payments' );
		}
		$section = antom_request( 'section', '' );
		if ( ! $section ) {
			$show_currency = array_slice( $this->currency, 0, 4 );
			if ( count( $this->currency ) > 4 ) {
				$show_currency[] = ' and others';
			}
			$this->method_description .= 'This gateway supports the following currencies : <strong>' . implode( ' ', $show_currency ) . '</strong>';
		} else {
			$this->method_description .= 'This gateway supports the following currencies : <strong>' . implode( ' ', $this->currency ) . '</strong>';
		}
	}

	/**
	 * Rewrite admin_options.
	 *
	 * @return void
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function admin_options() {
		?>
		<h2><?php echo esc_html( $this->get_method_title() ); ?>
			<?php
			wc_back_link(
				__(
					'Return to payments',
					'antom-payments'
				),
				admin_url( 'admin.php?page=wc-settings&tab=checkout' )
			);
			?>
		</h2>
		<?php
		echo wp_kses_post( wpautop( $this->get_method_description() ) );
		antom_init_setting_menus( antom_request( 'section', '' ) );
		?>
		<table class="form-table"><?php $this->generate_settings_html( $this->get_form_fields(), true ); ?></table>
		<?php
	}

	/**
	 * Get Payment Gateway icon. if icon is not set , return empty string.
	 *
	 * @return string The payment gateway icon.
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function get_icon() {

		$icon_list = array();
		if ( is_array( $this->icon ) ) {
			$icon_list = $this->icon;
		} else {
			$icon_list = array( $this->icon );
		}

		$icon = '<span class="antom-payment-icons">';
		foreach ( $icon_list as $icon_item ) {
			$icon .= '<img src="' . esc_url( $icon_item ) . '" class="antom-payment-icons-item">';
		}
		$icon .= '</span>';

		/**
		 * With this hook, we can manage our custom cion to show.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}


	/**
	 * Get Payment Settings from antom-payment-gateway-settings.php file.
	 *
	 * @param string $key the setting key you want to get.
	 * @param string $default_value if the key not set , this default value will be returned.
	 *
	 * @return mixed the value of the setting.
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	private function get_pay_setting( $key, $default_value = '' ) {
		$find_value      = '';
		$payment_methods = antom_get_payment_methods();
		foreach ( $payment_methods as $method ) {
			if ( $method['slug'] === $this->id ) {
				$find_value = isset( $method[ $key ] ) ? $method[ $key ] : $default_value;
				break;
			}
		}

		return $find_value;
	}

	/**
	 * Init Gateway Settings Form Fields.
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function init_form_fields() {
		$pay_name = $this->get_pay_setting( 'pay_name' );

		$form_fields =
			array(
				'enabled'     => array(
					'title'   => __( 'Enable/Disable', 'antom-payments' ),
					'type'    => 'checkbox',
					// translators: %s: $pay_name.
					'label'   => sprintf( esc_html( __( 'Enable %s', 'antom-payments' ) ), $pay_name ),
					'default' => false,
				),
				'title'       => array(
					'title'       => __( 'Title', 'antom-payments' ),
					'type'        => 'text',
					'description' => __(
						'This controls the title which the user sees during checkout.',
						'antom-payments'
					),
					'default'     => $pay_name,
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'antom-payments' ),
					'type'        => 'textarea',
					'description' => __(
						'Payment method description that the customer will see on your checkout.',
						'antom-payments'
					),
					// translators: %s: $pay_name.
					'default'     => sprintf( esc_html( __( 'Pay via %s', 'antom-payments' ) ), $pay_name ),
					'desc_tip'    => true,
				),
			);

		/**
		 * With this hook, we can manage our form fields to show.
		 *
		 * @since 1.0.0
		 */
		$form_fields = apply_filters(
			'antom_payment_gateways_init_' . $this->id . '_form_fields',
			$form_fields,
			$this->id
		);

		$this->form_fields = $form_fields;
	}

	/**
	 * Payment Gateway process_payment.
	 *
	 * @param mixed $order_id Woo order_id.
	 *
	 * @return array
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function process_payment( $order_id ) {
		$order                  = wc_get_order( $order_id );
		$user_id                = $order->get_user_id();
		$payment_method         = $order->get_payment_method();
		$is_card_payment_method = 'antom_card' === $payment_method;

		$is_verify = false;
		if ( isset( $_POST['form_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['form_nonce'] ), 'antom_form_nonce' ) ) {
			$is_verify = true;
		}

		//$card_token = isset( $_POST['antom_card_token'] ) ? sanitize_text_field( $_POST['antom_card_token'] ) : '';

		// if ( $is_card_payment_method ) {
		// 	if ( ! $card_token ) {
		// 		$error_message = __( 'Invalid Card Token.', 'antom-payments' );

		// 		return $this->return_failure( $error_message );
		// 	}
		// }

		$payment_options = $this->settings;
		if ( 'no' === $payment_options['enabled'] ) {
			$error_message = __( 'This payment method is not enabled', 'antom-payments' );

			return $this->return_failure( $error_message );
		}

		$core_settings = antom_get_core_settings();

		$client_id           = $core_settings['clientid'];
		$public_key          = $core_settings['public_key'];
		$private_key         = $core_settings['private_key'];
		$test_mode           = $core_settings['test_mode'];
		$settlement_currency = $core_settings['settlement_currency'];

		$api_host = Antom_Payment_Gateways_Statement::ANTOM_ALIPAY_API_HOST;
		$path     = Antom_Payment_Gateways_Statement::ANTOM_ALIPAY_PATH;
		if ( $test_mode ) {
			$path = Antom_Payment_Gateways_Statement::ANTOM_ALIPAY_SANDBOX_PATH;
		} elseif ( ! $settlement_currency ) {
			$error_message = __(
				'The settlement currency is not set, please contact the site administrator.',
				'antom-payments'
			);

			return $this->return_failure( $error_message );
		}

		$mode_note = $test_mode ? 'in test mode. ' : 'in live mode. ';

		// online request sdk.
		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/sdk/sdk-antom-alipay-online-request.php';

		// include models file.
		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/model/order/antom-order-model.php';
		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/model/order/antom-order-amount-model.php';
		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/model/order/antom-order-payment-method-model.php';
		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/model/order/antom-order-settlement-strategy-model.php';
		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/model/order/antom-order-env-model.php';
		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/model/order/antom-order-buyer-model.php';
		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/model/order/antom-order-name-model.php';

		$request = new Antom_Alipay_Online_Request();

		$extend_info = array(
			'PaymentSource' => 'WooC',
		);
		$request->set_extend_info( $extend_info );

		$total    = $order->get_total();
		$currency = $order->get_currency();

		$total_amount = intval( antom_order_amount_value_to_decimal( $total, $currency ) );
		$amount_model = new Antom_Order_Amount_Model();
		$amount_model->set_currency( $currency );
		$amount_model->set_value( $total_amount );

		$request->set_payment_amount( $amount_model->toArray() );


		$payment_method_type = $this->get_pay_setting( 'payment_method_type' );

		// get system set timezone,if the timezone is not set, set it to default timezone.
		$timezone = get_option( 'timezone_string' );
		if ( ! $timezone ) {
			$timezone = Antom_Payment_Gateways_Statement::ANTOM_DEFAULT_TIME_ZONE;
		}

		// order expire time.
		$order_expiry_time = get_option( 'woocommerce_hold_stock_minutes' );
		if ( ! $order_expiry_time ) {
			// if the order expiry time is not set, set it to 15 minutes.
			$order_expiry_time = 15;
		}
		$order_expiry_time = $order_expiry_time * 60;

		$order_model = new Antom_Order_Model();
		$order_model->set_order_amount( $amount_model->toArray() );
		$order_model->set_reference_order_id( $order_id );
		if($is_card_payment_method) {
			$order_model->set_order_description( 'pay via card' );
		}else{
			$order_model->set_order_description( 'order info' );
		}
		
		if ( $is_card_payment_method ) {
			// set order buyer info.
			$order_buyer_model = new Antom_Order_Buyer_Model();
			if ( $user_id ) {
				$order_buyer_model->set_reference_buyer_id( $user_id );
			}

			$order_buyer_name_model = new Antom_Order_Name_Model();
			$order_buyer_name_model->set_first_name( $order->get_billing_first_name() );
			$order_buyer_name_model->set_last_name( $order->get_billing_last_name() );
			$order_buyer_name_model->set_full_name( $order->get_billing_first_name() . $order->get_billing_last_name() );
			$order_buyer_name_model->set_middle_name( '' );
			$order_buyer_model->set_buyer_name( $order_buyer_name_model->toArray() );
			$order_buyer_model->set_buyer_phone_no( $order->get_billing_phone() );
			$order_buyer_model->set_buyer_email( $order->get_billing_email() );


			if ( $user_id ) {
				$user_data       = get_userdata( $user_id );
				$user_registered = strtotime( $user_data->user_registered );
				$user_registered = new DateTime( "@$user_registered" );
				$user_registered->setTimezone( new DateTimeZone( $timezone ) );
				$$user_registered_iso8601 = $user_registered->format( 'c' );
				$order_buyer_model->set_buyer_registration_time( $$user_registered_iso8601 );
			}
			$order_model->set_buyer( $order_buyer_model->toArray() );

		}


		$request->set_order( $order_model->toArray() );
		$request->set_product_code( 'CASHIER_PAYMENT' );

		$payment_request_checker = new Antom_Payment_Request_Checker( $order );
		$payment_request_checker->load_all_payment_request_ids();

		// There is a record of the payment slip that has been successfully paid, and no further payment is allowed.
		if ( $payment_request_checker->has_successful_payment_request_id() ) {
			$response = array(
				'result'           => 'success',
				'redirect'         => $order->get_view_order_url(),
				'redirect_app_url' => '',
			);

			return $response;
		}

		// Verify whether the maximum number of payment orders is limit.
		if ( $payment_request_checker->payment_request_ids_number_is_limit() ) {
			return $this->return_failure(
				__(
					'The maximum number of payment trys has been reached.',
					'antom-payments'
				)
			);
		}

		$maybe_generate_new_id_payment = $payment_request_checker->antom_is_generate_new_id_payment();

		$payment_request_id_info = $payment_request_checker->get_intermediate_payment_request_id_info();

		if ( empty( $payment_request_id_info ) ) {
			return $this->return_failure( $payment_request_checker->get_error() );
		}

		// if ( $payment_request_id_info['payment_method'] != $payment_method ) {
		// 	return $this->return_failure("Your last payment didn't complete. Please retry with the same payment method to generate your link." );
		// }

		$request->set_payment_request_id( $payment_request_id_info['payment_request_id'] );


		$payment_method_model = new Antom_Order_Payment_Method_Model();
		$payment_method_model->set_payment_method_type( $payment_method_type );

		if ( $user_id ) {
			$payment_method_model->set_customer_id( $user_id );
		}

		if ( $is_card_payment_method ) {
			$payment_method_model->set_payment_method_id( $card_token );
		}

		$payment_method_model->set_extend_info( $extend_info );

		// $payment_method_model->set_payment_method_meta_data(array(
		// 	'is3DSAuthentication' => true,
		// ));

		$request->set_payment_method( $payment_method_model->toArray() );

		if ( $is_card_payment_method ) {
			$payment_factor = array(
				'isAuthorization' => true,
			);
			$request->set_payment_factor( $payment_factor );
		}

		// order real expire time.
		$order_real_expiry_time = $order_expiry_time + time();
		$date_time              = new DateTime( "@$order_real_expiry_time" );
		$date_time->setTimezone( new DateTimeZone( $timezone ) );
		$order_expiry_time_iso8601 = $date_time->format( 'c' );

		// set order expire time.
		$request->set_payment_expiry_time( $order_expiry_time_iso8601 );

		$settlement_strategy_model = new Antom_Order_Settlement_Strategy_Model();
		$settlement_strategy_model->set_settlement_currency( $settlement_currency );
		$request->set_settlement_strategy( $settlement_strategy_model->toArray() );

		$callback_url = $this->get_view_order_detail_url( $order );
		$request->set_payment_redirect_url( $callback_url );

		$terminal_type = 'WEB';
		if ( wp_is_mobile() ) {
			$terminal_type = 'WAP';
		}
		$env_model = new Antom_Order_Env_Model();
		$env_model->set_terminal_type( $terminal_type );
		if ( 'WEB' !== $terminal_type ) {
			$os_type = antom_get_device_os_type();
			$env_model->set_os_type( $os_type );
			$env_model->set_client_ip( $client_ip );
		}
		if ( $is_card_payment_method ) {
			$client_ip = $order->get_customer_ip_address();
			$env_model->set_client_ip( $client_ip );
		}

		$request->set_env( $env_model->toArray() );

		$request->set_client_id( $client_id );
		$request->set_path( $path );
		$request->set_key_version( 1 );


		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/sdk/antom-alipay-client.php';

	
		$client = new Antom_Alipay_Client( $api_host, $private_key, $public_key );

		$order_view_url = $this->get_view_order_detail_url( $order );

		try {
			$response_body = (array) $client->execute( $request );
		} catch ( Exception $e ) {
			//$error_message = __( 'Failed to request payment information,Please check your key config in plugin and retry', 'antom-payments' );
			$error_message = $e->getMessage();

			return $this->return_failure( $error_message );
		}

		$response = array(
			'result'           => 'failure',
			'redirect'         => '',
			'redirect_app_url' => '',
		);

		$result_status = $response_body['result']->resultStatus;

		if ( $is_card_payment_method ) {
			if ( in_array(
				$result_status,
				array(
					'S',
				)
			) ) {
				/* translators: %1$s: antom payment request id , %2$s: antom mode note*/
				if($maybe_generate_new_id_payment){
					$order->add_order_note( sprintf( __( 'request payment by antom_payment_gateway card payment, payment request id is : %1$s, %2$s ', 'antom-payments' ), $payment_request_id_info['payment_request_id'], $mode_note ) );
				}
				
				//WC()->cart->empty_cart();

				$response['result']           = 'success';
				$response['redirect']         = $order_view_url;
				$response['redirect_app_url'] = $order_view_url;

				if ( isset( $response_body['normalUrl'] ) && $response_body['normalUrl'] ) {
					$normal_url                   = $response_body['normalUrl'];
					$response['redirect']         = $normal_url;
					$response['redirect_app_url'] = $normal_url;
				}

				return $response;

			} else if ( $result_status === 'U' && isset( $response_body['normalUrl']) ){
				// if trigger 3d, set 3d url

				if($maybe_generate_new_id_payment){
					$order->add_order_note( sprintf( __( 'request payment by antom_payment_gateway card payment, payment request id is : %1$s, %2$s ', 'antom-payments' ), $payment_request_id_info['payment_request_id'], $mode_note ) );
				}

				//WC()->cart->empty_cart();
				
				if ( isset( $response_body['normalUrl'] ) && $response_body['normalUrl'] ) {

					$response['result']           = 'success';
					$normal_url                   = $response_body['normalUrl'];
					$response['redirect']         = $normal_url;
					$response['redirect_app_url'] = $normal_url;
				}

				return $response;

			}
			else {
				
				// $inquiry_status = $this->inquiry_card_payment_request_status(
				// 	$payment_request_checker,
				// 	$payment_request_id_info['payment_request_id'],
				// 	$response_body['result']->resultCode,
				// 	$order_view_url,
				// 	$order
				// );

				// if( 'FAIL' == $inquiry_status ) {
				// 	return $this->return_failure($this->get_payment_error_msg( $response_body['result']->resultCode,$response_body['result']->resultMessage));
				// }

				// if ( 'SUCCESS' == $inquiry_status ) {
				// 	// payment request id is exist then redirect to order view page.
				// 	// not UNKNOWN/FAIL -> SUCCESS 
				// 	$response['result']           = 'success';
				// 	$response['redirect']         = $order_view_url;
				// 	$response['redirect_app_url'] = $order_view_url;
		
				// 	return $response;
				// } else {
				$order->add_order_note( sprintf( __( 'request payment by antom_payment_gateway %1$s fail,  payment request id is : %2$s, %3$s ', 'antom-payments' ), $payment_method_type, $payment_request_id_info['payment_request_id'], $mode_note ) );
					return $this->return_failure( $response_body['result']->resultMessage );
				//}
			}
		} elseif ( ! $is_card_payment_method ) {
			// Wallet payment.
			if ( in_array(
				$result_status,
				array(
					'S',
					'U',
				)
			) ) {
				
				if($maybe_generate_new_id_payment){
					$order->add_order_note( sprintf( __( 'request payment by antom_payment_gateway %1$s,  payment request id is : %2$s, %3$s ', 'antom-payments' ), $payment_method_type, $payment_request_id_info['payment_request_id'], $mode_note ) );
				}
                //WC()->cart->empty_cart();
				$response['result'] = 'success';
				if ( isset( $response_body['normalUrl'] ) ) {
					$response['redirect']         = $response_body['normalUrl'];
					$response['redirect_app_url'] = $response_body['normalUrl'];
				}

				if ( 'WAP' === $terminal_type ) {
					if ( isset( $response_body['applinkUrl'] ) ) {
						$response['redirect_app_url'] = $response_body['applinkUrl'];
					} elseif ( isset( $response_body['scehemeUrl'] ) ) {
						$response['redirect_app_url'] = $response_body['scehemeUrl'];
					}
				}

				return $response;
			} else {

				$order->add_order_note( sprintf( __( 'request payment by antom_payment_gateway %1$s fail,  payment request id is : %2$s, %3$s ', 'antom-payments' ), $payment_method_type, $payment_request_id_info['payment_request_id'], $mode_note ) );
				return $this->return_failure( $response_body['result']->resultMessage );
			}
		}

		return $response;
	}

	/**
	 * Inquiry antom card payment request status .
	 *
	 * @param mixed $payment_request_checker payment request checker.
	 * @param mixed $payment_request_id payment request id.
	 * @param mixed $error_message error message that antom response.
	 * @param mixed $order_view_url order view url.
	 * @param mixed $order Woo order.
	 *
	 * @return array
	 */
	public function inquiry_card_payment_request_status(
		$payment_request_checker,
		$payment_request_id,
		$error_message,
		$order_view_url,
		$order
	) {
		$inquiry_status = $payment_request_checker->inquiry_payment_request_state( $payment_request_id );

		return $inquiry_status;
	}


	/**
	 * Processing asynchronous notification after antom payment
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function payment_notify_handler() {
		$rep_body = file_get_contents( 'php://input' );
		$params   = json_decode( $rep_body, true );

		if ( empty( $params ) ) {
			antom_notify_response( 'ACCESS_DENIED', 'F', 'Notify Body Cannot Parse' );
		}

		$core_settings    = antom_get_core_settings();
		$public_key       = $core_settings['public_key'];
		$system_client_id = $core_settings['clientid'];
		$path             = '/';

		$headers = getallheaders();

		$client_id       = isset( $headers['Client-Id'] ) ? $headers['Client-Id'] : '';
		$signature       = isset( $headers['Signature'] ) ? $headers['Signature'] : '';
		$rep_time_string = isset( $headers['Request-Time'] ) ? $headers['Request-Time'] : '';

		if ( ! $client_id ) {
			$client_id = isset( $_SERVER['HTTP_CLIENT_ID'] ) ? $_SERVER['HTTP_CLIENT_ID'] : '';
		}
		
		if ( ! $signature ) {
			$signature = isset( $_SERVER['HTTP_SIGNATURE'] ) ? $_SERVER['HTTP_SIGNATURE'] : '';
		}

		if ( ! $rep_time_string ) {
			$rep_time_string = isset( $_SERVER['HTTP_REQUEST_TIME'] ) ? $_SERVER['HTTP_REQUEST_TIME'] : '';
		}

		if ( ! $client_id ) {
			antom_notify_response( 'ACCESS_DENIED', 'F', 'Client ID Is Missed' );
		}

		if ( $system_client_id != $client_id ) {
			antom_notify_response( 'ACCESS_DENIED', 'F', 'Client ID Not Match' );
		}

		if ( ! isset( $rep_time_string ) ) {
			antom_notify_response( 'ACCESS_DENIED', 'F', 'Request Time Is Missed' );
		}

		if ( ! isset( $signature ) ) {
			antom_notify_response( 'ACCESS_DENIED', 'F', 'Signature Is Missed' );
		}

		$rep_time = $rep_time_string;

		if ( strstr( $signature, 'signature' ) ) {
			$start_index = strrpos( $signature, '=' ) + 1;
			$signature   = substr( $signature, $start_index );
		}

		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/sdk/antom-alipay-client.php';
		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/sdk/antom-signature-tool.php';

		// validate signature.
		$verify_result = Antom_Signature_Tool::verify(
			'POST',
			$path,
			$client_id,
			$rep_time,
			$rep_body,
			$signature,
			$public_key
		);

		if ( ! $verify_result ) {
			antom_notify_response( 'ACCESS_DENIED', 'F', 'Signature Is Not Passed' );
		}

		$notify_type = $params['notifyType'];

		if ( 'PAYMENT_RESULT' === $notify_type ) {
			$payment_request_id = $params['paymentRequestId'];
		} elseif ( 'CAPTURE_RESULT' === $notify_type ) {
			$payment_request_id = $params['captureRequestId'];
		}

		$order_id = antom_get_order_id_by_payment_request_id( $payment_request_id );
		if ( $order_id === $payment_request_id ) {
			// If the order ID is the same as the paymentRequestId, it means that the order ID has not been resolved.
			$order_id = antom_get_order_id_by_payment_request_id_from_database( $payment_request_id );
		}

		if ( ! $order_id ) {
			antom_notify_response( 'PARAM_INVALID', 'F', 'paymentRequestId is not a valid value' );
		}

		$order = wc_get_order( $order_id );

		if ( ! is_a( $order, 'WC_Order' ) ) {
			antom_notify_response( 'ORDER_NOT_FOUND', 'F', 'Order Not Found' );
		}

		$payment_request_checker = new Antom_Payment_Request_Checker( $order );
		$payment_request_checker->load_all_payment_request_ids();

		// Determine whether the payment request id matches the order ID.
		if ( ! $payment_request_checker->check_order_has_payment_request_id( $payment_request_id ) ) {
			antom_notify_response( 'PARAM_INVALID', 'F', 'paymentRequestId is not match orderId' );
		}

		// get payment method by $payment_request_id.
		$payment_method = $payment_request_checker->get_payment_method_with_payment_request_id( $payment_request_id );

		if ( ! $payment_method ) {
			antom_notify_response( 'PARAM_INVALID', 'F', 'Payment method not found' );
		}

		$is_card_payment_method = false;
		if ( 'antom_card' === $payment_method ) {
			$is_card_payment_method = true;
		}

		$payment_status = $order->get_status();

		if ( $is_card_payment_method ) {
			$this->handler_order_with_payment_capture_notify(
				$order,
				$payment_request_checker,
				$params,
				$payment_request_id,
				$notify_type,
				$payment_status
			);
		} else {
			$this->handler_order_with_payment_notify(
				$order,
				$payment_request_checker,
				$params,
				$payment_request_id,
				$notify_type,
				$payment_status
			);
		}
	}


	/**
	 * Handler order payment status with antom payment capture notify.
	 *
	 * @param mixed  $order Woo order.
	 * @param mixed  $payment_request_checker payment request checker instance.
	 * @param array  $params antom notify params.
	 * @param string $payment_request_id payment request id.
	 * @param string $notify_type antom notify type.
	 * @param string $payment_status order payment status.
	 *
	 * @return void
	 */
	private function handler_order_with_payment_capture_notify(
		$order,
		$payment_request_checker,
		$params,
		$payment_request_id,
		$notify_type,
		$payment_status
	) {

		if ( 'PAYMENT_RESULT' === $notify_type ) {
			if ( 'F' === $params['result']['resultStatus'] ) {
				// 卡场景，通知到的时候paymentRequestId已经是FAIL，说明是在前端支付后inquiry直接设为FAIL，用户还留在前端，可重新输入卡号再支付，因此不设为FAIL
				// $inquiry_status=$payment_request_checker->get_payment_status_with_payment_request_id( $payment_request_id );
				// if ('FAIL'=== $inquiry_status ){
				// 	antom_notify_response();
				// }else{
					// 卡场景，反之，说明用户没留在前端，属于2D、3D支付场景，将订单关掉
				$payment_request_checker->set_payment_request_id_state( $payment_request_id, 'FAIL' );
				$this->set_order_pay_failed( $order, $payment_status, $payment_request_id, $params );
				//}
				
			}
		} elseif ( 'CAPTURE_RESULT' === $notify_type ) {
			if ( 'S' === $params['result']['resultStatus'] ) {
				$payment_request_checker->set_payment_request_id_state( $payment_request_id, 'SUCCESS' );
				$this->set_order_successfully_paid( $order, $payment_status, $params, true );
			}

			if ( 'F' === $notify_type ) {
				$payment_request_checker->set_payment_request_id_state( $payment_request_id, 'FAIL' );
				$this->set_order_pay_failed( $order, $payment_status, $payment_request_id, $params );
			}
		}
	}


	/**
	 * Handler order payment status with antom payment notify.
	 *
	 * @param mixed $order Woo order.
	 * @param mixed $payment_request_checker payment request checker instance.
	 * @param mixed $params Antom notify params.
	 * @param mixed $payment_request_id antom payment request id.
	 * @param mixed $notify_type notify type.
	 * @param mixed $payment_status order payment status.
	 *
	 * @return void
	 */
	private function handler_order_with_payment_notify(
		$order,
		$payment_request_checker,
		$params,
		$payment_request_id,
		$notify_type,
		$payment_status
	) {

		if ( 'PAYMENT_RESULT' === $notify_type ) {
			if ( 'S' === $params['result']['resultStatus'] ) {
				$payment_request_checker->set_payment_request_id_state( $payment_request_id, 'SUCCESS' );
				$this->set_order_successfully_paid( $order, $payment_status, $params );
			}

			if ( 'F' === $params['result']['resultStatus'] ) {

				$payment_request_checker->set_payment_request_id_state( $payment_request_id, 'FAIL' );
                $this->set_order_pay_failed( $order, $payment_status, $payment_request_id, $params );
			}
		}
	}

	/**
	 * Add an order note to log the order payment status from $from_status to $to_status.
	 *
	 * @param mixed  $order Woo order.
	 * @param string $from_status from payment status.
	 * @param string $to_status to payment status.
	 * @param string $payment_request_id antom payment request id.
	 *
	 * @return mixed
	 */
	protected function set_order_payment_status_change_note(
		$order,
		$from_status,
		$to_status,
		$payment_request_id = ''
	) {

		$status_change_note = sprintf(
			/* translators: %1$s: the order payment status before log, %2$s:the order new payment status */
			__(
				'Order payment status has changed from %1$s to %2$s by antom-payments-gateway.',
				'antom-payments'
			),
			$from_status,
			$to_status
		);
		$payment_request_id_note = '';
		if ( $payment_request_id ) {
			$payment_request_id_note = sprintf(
				/* translators: %s: the antom payment request id */
				__( ' the payment request id is : %s', 'antom-payments' ),
				$payment_request_id
			);
		}

		$order->add_order_note( $status_change_note . $payment_request_id_note );
		return $order;
	}

	/**
	 * The payment status of the order may be set.
	 *
	 * @param mixed $order Woo order.
	 * @param mixed $payment_status payment status.
	 * @param mixed $params antom notify params.
	 * @param bool  $is_card_payment_method set if is antom_card payment method.
	 *
	 * @return void
	 */
	protected function set_order_successfully_paid(
		$order,
		$payment_status,
		$params,
		$is_card_payment_method = false
	) {

		$payment_request_id = isset( $params['paymentRequestId'] ) ? $params['paymentRequestId'] : '';
		$payment_method     = $order->get_payment_method();
		if ( 'antom_card' === $payment_method ) {
			$payment_request_id = isset( $params['captureRequestId'] ) ? $params['captureRequestId'] : '';
		}
		//WC()->cart->empty_cart();
		$order->add_order_note( sprintf( __( ' payment sucess by antom_payment_gateway %1$s,  payment request id is : %2$s', 'antom-payments' ), $payment_method, $payment_request_id ) );
		$order->set_transaction_id( $payment_request_id );
		$order->payment_complete();
		
		if ( in_array(
			$payment_status,
			array(
				'refunded',
				'cancelled',
				'completed',
				'processing',
				'failed',
			),
			true
		) ) {
			$this->create_antom_abnormal_payment_log( $order, $payment_status, $payment_request_id );
		}

		antom_notify_response();
	}

	/**
	 * Set an order pay failed status.
	 *
	 * @param mixed  $order Woo order.
	 * @param string $payment_status Woo order payment status.
	 *
	 * @return void
	 */
	protected function set_order_pay_failed( $order, $payment_status, $payment_request_id, $params ) {

		// If the order is unpaid, set it to fail.
		if ( ! ( $order->is_paid() || $order->get_status() === 'cancelled') ) {
			$order->update_status( 'failed', sprintf( __( 'update by antom_payment_gateway, payment request id is : %1$s, error code is : %2$s. ', 'antom-payments' ), $payment_request_id, $params['result']['resultCode'] ) );
		}

		if ( in_array(
			$payment_status,
			array(
				'refunded',
				'completed',
				'processing',
			),
			true
		) ) {
			$this->create_antom_abnormal_payment_log( $order, $payment_status, 'failed', '' );
		}

		antom_notify_response();
	}

	/**
	 * Maybe need record antom abnormal payment log.
	 *
	 * @param mixed $order Woo order.
	 * @param mixed $payment_status order payment status.
	 * @param mixed $finish_payment_status the new order payment status we want to set.
	 * @param mixed $payment_request_id antom payment request id.
	 *
	 * @return void
	 */
	public function create_antom_abnormal_payment_log(
		$order,
		$payment_status,
		$finish_payment_status = 'completed',
		$payment_request_id = ''
	) {

		if ( $payment_request_id ) {
			$meta_message = gmdate( 'Y-m-d H:i:s' ) . ' : ' . sprintf(
				/* translators: %1$s: the order payment status before log, %2$s:the order new payment status */
				__(
					'The order payment status is %1$s when we set the order payment status to %2$s , the payment request id is : %3$s',
					'antom-payments'
				),
				$payment_status,
				$finish_payment_status,
				$payment_request_id
			);
		} else {
			$meta_message = gmdate( 'Y-m-d H:i:s' ) . ' : ' . sprintf(
				/* translators: %1$s: the order payment status before log,  %2$s:the order new payment status */
				__(
					'The order payment status is %1$s when we set the order payment status to %2$s ',
					'antom-payments'
				),
				$payment_status,
				$finish_payment_status
			);
		}

		$order->add_meta_data( Antom_Payment_Gateways_Statement::ANTOM_NOTIFY_ABNORMAL_KEY, $meta_message );
		$order->save();
	}

	/**
	 * Common function to response a failed data to WC AJAX request.
	 *
	 * @param string $message error message.
	 *
	 * @return array
	 */
	private function return_failure( $message = '' ) {

		if( $this->check_is_block_checkout() ){
			throw new Exception($message);
		}

		wc_add_notice( $message, 'error' );

		return array(
			'result' => 'failure',
		);
	}

		/**
	 * Validate if this payment is woocommerce block checkout way
	 * @return bool
	 * @since 1.0.11
	 */
	private function check_is_block_checkout() {
		// 检查是否是 REST API 请求
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// 检查请求路径是否包含 wc/store/v1/checkout
			if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'],
					'wc/store/v1/checkout' ) !== false ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Commo.
	 *
	 * @param string $message error message.
	 *
	 * @return array
	 */
    private function get_view_order_detail_url($order) {
        // 检查用户是否已登录
        if ( is_user_logged_in() ) {
            // 用户已登录，使用查看订单页面的 URL
            $callback_url = $order->get_view_order_url();
        } else {
            // 用户未登录，使用结账后的感谢页面 URL
            $callback_url = $order->get_checkout_order_received_url();
        }
        return $callback_url;
    }

	private function get_payment_error_msg($code,$error_message) {
		return [
			'ACCESS_DENIED'  => 'Reject by Channel, Please Change a Card and retry',
			'DO_NOT_HONOR'   => 'The payment is declined by the issuing bank.',
			'FRAUD_REJECT'   => 'reject by risk',
			'USER_BALANCE_NOT_ENOUGH' => 'balance not enough',
			'INVALID_CARD_NUMBER' => 'invalid card number',
			'PROCESS_FAIL' => 'payment fail',
			'SELECTED_CARD_BRAND_NOT_AVAILABLE' => 'payment fail, please change a card brand',
		][$code] ?? $error_message;
	}
}
