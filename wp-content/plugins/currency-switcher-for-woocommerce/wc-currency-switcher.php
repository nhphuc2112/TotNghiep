<?php
/**
 * Plugin Name: WC Currency Switcher
 * Description: Currency Switcher for WooCommerce is a versatile multi-currency converter plugin that automatically displays product prices in your customer’s local currency. By detecting their location through Geo-Location IP, it enhances their shopping experience with live exchange rates that update at set intervals. Easily integrated across your site, from the shop to checkout, this extension empowers global customers to shop seamlessly in their preferred currency.
 * Version: 1.9.5
 * Tags: currency switcher, currency switcher woocommerce, currency switcher WordPress, currency converter plugin, currency switcher extension, currency switcher plugin, currency switcher at checkout, woocommerce, WordPress, woocommerce extension, donation currency switcher
 * Author: WPExperts
 * Author URI: http://wpexperts.io/
 * Developer: WPExperts
 * Developer URI: https://wpexperts.io/
 * Text Domain: wccs
 * WC requires at least: 5.0
 * WC tested up to: 9.6
 * Tested up to: 6.7
 * 
 * Woo: 6302270:6147044df74946ce8d021941c85612a6

 */

namespace Wpexperts\CurrencySwitcherForWoocommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Define plugin constants
 */
define( 'WCCS_DIR', __DIR__ );
define( 'WCCS_VERSION', '1.9.5' );
define( 'WCCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCCS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCCS_API_TOKEN', '$2b$10$CI2yIq4uvV3v66v0XqIqQ.1U4emkcL.16Ft6XKq/YI4jT2HiGCUPO' );

if ( file_exists( WCCS_DIR . '/vendor/autoload.php' ) ) {
	require_once WCCS_DIR . '/vendor/autoload.php';
}

use Wpexperts\CurrencySwitcherForWoocommerce\Activator; 
use Wpexperts\CurrencySwitcherForWoocommerce\Deactivator; 
use Wpexperts\CurrencySwitcherForWoocommerce\Storage; 
use Wpexperts\CurrencySwitcherForWoocommerce\SwitcherWidget; 
use Wpexperts\CurrencySwitcherForWoocommerce\WCCS; 
use Wpexperts\CurrencySwitcherForWoocommerce\Settings; 
use Wpexperts\CurrencySwitcherForWoocommerce\AjaxProcess; 
use Wpexperts\CurrencySwitcherForWoocommerce\Cron; 

use GeoIp2\Database\Reader;

class WCCS_Currency_Switcher {

	// Hold the class instance.
	private static $_instance = null;   

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wccs' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wccs' ), '1.0' );
	}

	/**
	 * Contructor of class.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		include WCCS_PLUGIN_PATH . 'includes/Helper.php';

		Activator::Instance();
		add_action('woocommerce_init', array( $this, 'run' ), 10);
		add_action( 'before_woocommerce_init', array( $this, 'hpos_compatibility' ), 10 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action('admin_notices', array( $this, 'wccs_limit_location_reach' ) );
		add_filter( 'plugin_row_meta', array( $this, 'wccs_modify_plugin_view_details_link' ), 15, 2 );
		add_action( 'wp_footer', array( $this, 'wccs_call_refresh_cart_fragment' ) );

		register_activation_hook( __FILE__, array( $this, 'wccs_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'wccs_deactivation' ) );
	}

	/**
	 * Load Text Domain.
	 *
	 * @since 1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wccs', false, basename( __DIR__ ) . '/languages/' );
	}

	/**
	 * HPOS Compatibility & Cart & Checkout Compatibilty.
	 *
	 * @since 1.0
	 */
	public function hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}

	/**
	 * Run.
	 *
	 * @since 1.0
	 */
	public function run() {


		if ( Activator::check_woo_dependecies() ) {         


			AjaxProcess::Instance(); //Init Ajax Process
			Cron::Instance();
			Storage::Instance();
			$GLOBALS['WCCS'] = WCCS::Instance();
			Settings::Instance();

		}
	}


	public function wccs_activation() {
		Activator::Instance();
	}

	public function wccs_deactivation() {
		Deactivator::Instance();
	}

	public function wccs_limit_location_reach() {

		$data =  wccs_get_client_ip_server();

		if ( isset( $data['geoplugin_error'] ) ) {              
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo esc_attr( $data['geoplugin_error'] ); ?></p>
			</div>
			<?php	          
		}
	}


	public function wccs_modify_plugin_view_details_link( $plugin_meta, $plugin_file ) {
		
		// Check if this is the plugin you want to modify
		if ( 'currency-switcher-for-woocommerce/wc-currency-switcher.php' === $plugin_file ) {

			unset($plugin_meta[2]);

			// Modify the link
			$plugin_meta[] = sprintf(
				'<a href="%s" aria-label="%s" target="_blank">%s</a>',
				esc_url( 'https://woocommerce.com/products/currency-switcher-for-woocommerce/' ),
				__( 'Visit plugin site', 'wccs' ),
				__( 'View Details', 'wccs' )
			);
		}

		return $plugin_meta;
	}

	public function wccs_call_refresh_cart_fragment() {
		?>
		<script>
		setTimeout(() => {
			jQuery( document.body ).trigger( 'wc_fragment_refresh' );
		}, 300);
		</script>
		<?php
	}

	/**
	 * Singleton Instance Method to initiate class.
	 *
	 * @since 1.0
	 */
	public static function Instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new WCCS_Currency_Switcher();
		}

		return self::$_instance;
	}
}

WCCS_Currency_Switcher::Instance();