<?php

/**
 * Plugin Name: Antom Payments
 * Description: Extends WooCommerce by Adding the Antom Payments Gateway.
 * Author: Antom
 * Author URI: https://www.antom.com/
 * Version: 1.0.13
 * Text Domain: antom-payments
 * Domain Path: /languages
 * WC tested up to: 9.0.0
 * Requires Plugins: woocommerce
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package extension
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

! defined( 'ANTOM_MAIN_PLUGIN_FILE' ) && define( 'ANTOM_MAIN_PLUGIN_FILE', __FILE__ );
! defined( 'ANTOM_PAYMENT_GATEWAYS_VERSION' ) && define( 'ANTOM_PAYMENT_GATEWAYS_VERSION', '1.0.0' );
! defined( 'ANTOM_PAYMENT_GATEWAYS_PATH' ) && define( 'ANTOM_PAYMENT_GATEWAYS_PATH', plugin_dir_path( __FILE__ ) );
! defined( 'ANTOM_PAYMENT_GATEWAYS_URL' ) && define( 'ANTOM_PAYMENT_GATEWAYS_URL', plugin_dir_url( __FILE__ ) );

function antom_check_woocommerce_activated() {
	return class_exists( 'woocommerce' );
}


/**
 * We use this method to determine whether the WooCommerce plugin is enabled or installed.
 *
 * @since 1.0.0
 */
function antom_payment_gateways_activate_checker() {
	if ( ! antom_check_woocommerce_activated() ) {

		// the error message.
		wp_die(
			'<div class="error"><p><strong>' . sprintf(
			/* translators: 1:  woo download page, 2:plugin list page */
				esc_html__(
					'Antom Payment Gateways requires WooCommerce to be installed and active. You can download %1$s here. Or go back to plugin list %2$s',
					'antom-payments'
				),
				'<a href="https://woo.com/" target="_blank">WooCommerce</a>',
				'<a href="' . esc_attr( admin_url( 'plugins.php' ) ) . '">Return to Plugins List</a>'
			) . '</strong></p></div>'
		);
	}
}

/**
 * Validate woocommerce is installed when activating the plugin
 *
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'antom_payment_gateways_activate_checker' );

add_action( 'woocommerce_loaded', 'antom_includes', 0 );
/**
 * We use this method to load the necessary files of the plugin.
 *
 * @since 1.0.0
 */
function antom_includes() {
	require_once 'includes/utils/antom-payment-gateways-options.php';
	require_once 'includes/utils/antom-payment-request-checker.php';
	require_once 'includes/antom-payment-gateway-settings.php';
	require_once 'includes/antom-payment-gateways-statement.php';
	require_once 'includes/functions.php';

	if ( is_admin() ) {
		require_once 'includes/class-antom-admin.php';
	} else {
		require_once 'includes/class-antom-frontend.php';
	}

	require_once 'includes/gateways/class-wc-gateway-antom-common.php';

	// load antom payment gateways
	$payment_methods = antom_get_payment_methods();
	foreach ( $payment_methods as $payment_method ) {
		if ( is_file( $payment_method['gateway_file'] ) ) {
			require_once $payment_method['gateway_file'];
		}
	}

	require_once 'includes/antom-payment-gateway-form-api.php';
}

	/**
	 * We use this method to load antom-payments-gateway multilingual scope.
	 *
	 * @since 1.0.0
	 */
function antom_load_textdomain() {
	load_plugin_textdomain(
		'antom-payments',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

	add_action( 'woocommerce_loaded', 'antom_load_textdomain' );

	// Make the antom payments gateway available to WC.
	add_filter( 'woocommerce_payment_gateways', 'atom_add_gateway' );
	/**
	 * We use this method to append the payment gateway supported by the Antom Payments Gateway plugin to the WooCommerce plugin.
	 *
	 * @param array $gateways WooCommerce payment gateways.
	 *
	 * @since 1.0.0
	 */
function atom_add_gateway( $gateways ) {
	$payment_methods = antom_get_payment_methods();
	foreach ( $payment_methods as $payment_method ) {
		if ( isset( $payment_method['gateway_class'] ) && class_exists( $payment_method['gateway_class'] ) ) {
			$gateways[] = $payment_method['gateway_class'];
		}
	}

	return $gateways;
}

	// Registers WooCommerce Blocks integration.
	add_action( 'woocommerce_blocks_loaded', 'antom_woocommerce_gateway_block_support' );
	/**
	 * WooCommerce Checkout block support.
	 *
	 * @since 1.0.0
	 */
function antom_woocommerce_gateway_block_support() {
	if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		require_once 'includes/blocks/class-wc-gateway-antom-block-support-common.php';

		$currency = get_woocommerce_currency();

		$payment_methods = antom_get_payment_methods();
		foreach ( $payment_methods as $payment_method ) {
			if ( Antom_Payment_Gateways_Statement::ANTOM_DEVELOP_MODE || in_array( $currency, $payment_method['support_currencies'] ) ) {
				require_once $payment_method['block_file'];

				add_action(
					'woocommerce_blocks_payment_method_type_registration',
					function (
						Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry
					) use (
						$payment_method
					) {
						if ( isset( $payment_method['block_support_class'] ) && class_exists( $payment_method['block_support_class'] ) ) {
							$payment_method_registry->register( new $payment_method['block_support_class']() );
						}
					}
				);


			}
		}
	}
}


/**
 * Add a Settings button to the Antom Payments Gateway plugin.
 *
 * @param array $links Array of existing plugin links.
 *
 * @since 1.0.0
 */
function antom_settings( $links ) {
	$link          = admin_url(
		'admin.php?page=wc-settings&tab=checkout&section=' .
						Antom_Payment_Gateways_Statement::ANTOM_CORE_SETTING_SLUG
	);
	$settings_link = '<a href="' . esc_url( $link ) . '">' . __(
		'Settings',
		'antom-payments'
	) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

	add_filter( 'plugin_action_links_' . plugin_basename( ANTOM_MAIN_PLUGIN_FILE ), 'antom_settings' );

	/**
	 * We use this method to render the core configuration page of the plugin.
	 *
	 * @since 1.0.0
	 */
function antom_sections_checkout() {
	Antom_Admin::get_instance()->maybe_render_welcome();
	Antom_Admin::get_instance()->maybe_render_setting_page();
}

	add_action( 'woocommerce_settings_checkout', 'antom_sections_checkout' );


if ( is_admin() ) {
	/**
	 * Load admin assets for the plugin.
	 *
	 * @since 1.0.0
	 */
	function antom_load_admin_assets() {
		$section         = antom_request( 'section', '', 'string' );
		$payment_methods = antom_get_payment_methods();
		$antom_sections  = array_merge(
			array(
				'antom-payment-gateway',
			),
			array_column( $payment_methods, 'slug' )
		);
		if ( in_array( $section, $antom_sections ) ) {

			wp_enqueue_style(
				'antom-payment-gateway',
				ANTOM_PAYMENT_GATEWAYS_URL . 'assets/css/antom-payments-gateway-admin.min.css',
				array(),
				ANTOM_PAYMENT_GATEWAYS_VERSION
			);

			if ( 'antom-payment-gateway' === $section ) {
				wp_enqueue_script(
					'antom-payment-gateway',
					ANTOM_PAYMENT_GATEWAYS_URL . 'assets/js/antom-payments-gateway-admin.min.js',
					array(),
					ANTOM_PAYMENT_GATEWAYS_VERSION,
					false
				);
			}

			$antom_common_setting = array(
				'antom_web_site'           => Antom_Payment_Gateways_Statement::ANTOM_WEBSITE_URL,
				'antom_register_site'      => Antom_Payment_Gateways_Statement::ANTOM_REGESITER_URL,
				'visit_logo'               => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/share.svg',
				'visit_text'               => __( 'Antom Dashboard', 'antom-payments' ),
				'register_text'            => __( 'Register Antom', 'antom-payments' ),
				'settlement_warning_text'  => Antom_Payment_Gateways_Statement::ANTOM_LIVE_MODE_SETTLEMENT_CURRENCY_WARNING,
				'settlement_required_text' => __( 'Please select a settlement currency', 'antom-payments' ),
			);

			wp_localize_script( 'antom-payment-gateway', 'antom_common_setting', $antom_common_setting );
		}
	}

	add_action( 'admin_enqueue_scripts', 'antom_load_admin_assets' );

	require_once 'includes/class-antom-admin.php';
	$antom_admin = Antom_Admin::get_instance();

	add_action(
		'woocommerce_page_wc-settings',
		array(
			$antom_admin,
			'maybe_save_core_settings',
		)
	);

	add_action(
		'woocommerce_order_list_table_extra_tablenav',
		array(
			$antom_admin,
			'add_antom_filter_by_extra_tablenav',
		),
		10,
		2
	);


	add_filter(
		'woocommerce_order_list_table_prepare_items_query_args',
		array(
			$antom_admin,
			'woocommerce_order_list_table_prepare_items_query_args',
		)
	);

	add_filter(
		'woocommerce_admin_order_buyer_name',
		array(
			$antom_admin,
			'append_antom_payment_method',
		),
		10,
		2
	);

	add_action(
		'woocommerce_admin_order_data_after_payment_info',
		array(
			$antom_admin,
			'antom_order_abnormal_warning',
		)
	);


} else {
	/**
	 * Load frontend assets for the plugin.
	 *
	 * @since 1.0.0
	 */
	function antom_load_fronted_assets() {

	if ( !is_checkout() ) {
		return;
	}
			

		// if antom card payment gateway is enabled.
		$card_settings = get_option( 'woocommerce_antom_card_settings' );

		if ( ! $card_settings
			|| (
				is_array( $card_settings ) &&
				isset( $card_settings['enabled'] ) &&
				'yes' === $card_settings['enabled'] )
		) {
			wp_enqueue_script(
				'crypto',
				ANTOM_PAYMENT_GATEWAYS_URL . 'assets/js/crypto-js.min.js',
				array(),
				ANTOM_PAYMENT_GATEWAYS_VERSION,
				false
			);
			wp_enqueue_script(
				'jsencrypt',
				ANTOM_PAYMENT_GATEWAYS_URL . 'assets/js/jsencrypt.min.js',
				array(),
				ANTOM_PAYMENT_GATEWAYS_VERSION,
				false
			);
		}

		wp_enqueue_style(
			'antom-payment-gateway',
			ANTOM_PAYMENT_GATEWAYS_URL . 'assets/css/antom-payments-gateway-frontend.min.css',
			array(),
			ANTOM_PAYMENT_GATEWAYS_VERSION
		);
		wp_enqueue_script(
			'antom-payment-gateway',
			ANTOM_PAYMENT_GATEWAYS_URL . 'assets/js/antom-payments-gateway-frontend.min.js',
			array( 'wc-checkout' ),
			ANTOM_PAYMENT_GATEWAYS_VERSION,
			false
		);

		wp_localize_script( 'antom-payment-gateway', 'antom_languages', antom_get_card_error_message() );

		$core_settings = antom_get_core_settings();

		$antom_card_settings = array(
			'card_token_url'   => Antom_Payment_Gateways_Statement::ANTOM_CARD_TOKEN_URL,
			'client_id'        => $core_settings['clientid'],
			'antom_public_key' => $core_settings['public_key'],
		);

		wp_localize_script( 'antom-payment-gateway', 'antom_card_settings', $antom_card_settings );

		$antom_payment_gateway_settings = array_merge(
			array(
				'antom_is_mobile' => wp_is_mobile(),
			),
			antom_get_loading_animate_setting()
		);

		wp_localize_script(
			'antom-payment-gateway',
			'antom_payment_gateways_settings',
			$antom_payment_gateway_settings
		);
	}

	add_action( 'wp_enqueue_scripts', 'antom_load_fronted_assets' );

	require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/class-antom-frontend.php';

	$antom_frontend = new Antom_Frontend();
	add_filter(
		'woocommerce_available_payment_gateways',
		array(
			$antom_frontend,
			'woocommerce_available_payment_gateways',
		)
	);

	add_action(
		'woocommerce_admin_order_data_after_payment_info',
		array(
			$antom_frontend,
			'antom_order_abnormal_warning',
		),
		10,
		1
	);

}


	add_action( 'before_woocommerce_init', 'antom_checkout_blocks_compatibility' );
	/**
	 * WooCommerce Checkout Blocks compatibility
	 *
	 * @since 1.0.0
	 */
function antom_checkout_blocks_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'cart_checkout_blocks',
			__FILE__,
			true
		);
	}
}

	// HPOS support.
	add_action(
		'before_woocommerce_init',
		function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
	);

add_action( 'woocommerce_thankyou', 'display_unknown_result_message', 10, 1 );

function display_unknown_result_message( $order_id ) {
    $order = wc_get_order( $order_id );
    // 如果订单状态为"处理中"或其他你定义的状态，可以显示提示信息
    if ( $order->get_status() === 'pending' ) {
        echo '<div class="woocommerce-info">Your order is waiting payment result.</div>';
    }
    if ( $order->get_status() === 'fail' ) {
        echo '<div class="woocommerce-info">Your order has failed.</div>';
    }
}