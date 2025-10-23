<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
abstract class WC_Gateway_Antom_Block_Support extends AbstractPaymentMethodType {
	/**
	 * The gateway instance.
	 *
	 * @var WC_Gateway_antom_alipay_cn
	 */
	private $gateway;

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', array() );
		$gateways       = WC()->payment_gateways->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_name       = str_replace( 'antom_', '', $this->name );
		$script_path       = 'assets/blocks/' . $script_name . '/' . $script_name . '.js';
		$script_asset_path = ANTOM_PAYMENT_GATEWAYS_PATH . 'assets/blocks/' . $script_name . '/' . $script_name . '.asset.php';

		$script_asset = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => ANTOM_PAYMENT_GATEWAYS_VERSION,
			);
		$script_url   = ANTOM_PAYMENT_GATEWAYS_URL . $script_path;

		wp_register_script(
			'wc-' . $this->name . '-payments-blocks',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			$name_format = str_replace( '_', '-', $this->name );
			wp_set_script_translations( 'wc-' . $name_format . '-payments-blocks', 'antom-payments', ANTOM_PAYMENT_GATEWAYS_URL . 'languages/' );
		}

		return array( 'wc-' . $this->name . '-payments-blocks' );
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$title = $this->get_pay_setting( 'title' );
		if ( ! $title ) {
			$title = $this->get_pay_setting( 'default_display_name' );
		}

		$description = $this->get_pay_setting( 'description' );
		if ( ! $description ) {
			$description = $this->gateway->form_fields['description']['default'];
		}

		$core_setting = antom_get_core_settings();

		$icon = $this->get_pay_setting( 'icon' );

		$data = array(
			'title'       => $title,
			'description' => $description,
			'icon'        => is_array( $icon ) ? $icon : array( $icon ),
			'supports'    => array_filter( $this->gateway->supports, array( $this->gateway, 'supports' ) ),
		);

		$data['is_test_mode']    = 1 === intval( $core_setting['test_mode'] ) ? true : false;
		$data['animate_setting'] = antom_get_loading_animate_setting();
		$data['payment_method']  = $this->name;

		if ( 'antom_card' === $this->name ) {
			$assets_url                           = ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/';
			$data['assets_url']                   = $assets_url;
			$data['card_icon_lists']              = array(
				'card'       => $assets_url . 'card-gray.svg',
				'Visa'       => $assets_url . 'VISA.svg',
				'Mastercard' => $assets_url . 'MasterCard.svg',
			);
			$data['card_validate_message']        = antom_get_card_error_message();
			$data['card_public_key']              = $core_setting['public_key'];
			$data['card_token_url']               = Antom_Payment_Gateways_Statement::ANTOM_CARD_TOKEN_URL;
			$data['client_id']                    = $core_setting['clientid'];
			$data['antom_card_token_fetch_error'] = __( 'Card Info Error, Please change a card or check your card info', 'antom-payments' );
			$data['is_logged_in']                 = is_user_logged_in();
			$data['login_in_message']             = __( 'Please log in before using this payment method.', 'antom-payments' );
		}

		return $data;
	}

	/**
	 * Read the set value according to the key name from the WooCommerce settings. If not, try to read it from Antom Payment Methods Settings. If not exist, return the default value.
	 *
	 * @param string $key the find key.
	 * @param mixed  $default_value default value.
	 * @return mixed
	 */
	private function get_pay_setting( $key, $default_value = '' ) {
		$find_value = '';
		if ( isset( $this->settings[ $key ] ) ) {
			$find_value = $this->settings[ $key ];
		} else {
			// If not found in settings, check in gateway settings.
			$payment_methods = antom_get_payment_methods();
			foreach ( $payment_methods as $payment_method ) {
				if ( $payment_method['slug'] !== $this->name ) {
					continue;
				}
				if ( isset( $payment_method[ $key ] ) ) {
					$find_value = $payment_method[ $key ];
					break;
				}
			}

			if ( ! $find_value ) {
				$find_value = $default_value;
			}
		}

		return $find_value;
	}
}
