<?php
namespace Wpexperts\CurrencySwitcherForWoocommerce;

class Activator {

	// Hold the class instance.
	private static $_instance = null;

	private static $_activator = false;

	/**
	 * Contructor of class.
	 *
	 * @since 1.0
	 */
	private function __construct() {        
		/**
		 * Plugin need woocomerce plugin
		 */
		if ( is_multisite() ) {
			/**
			* Filter.
			* 
			* @since 1.0
			*/
			$active_plugin = apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins' ) );
		}

		/**
		* Filter.
		* 
		* @since 1.0
		*/
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins', false) ), true ) || isset( $active_plugin['woocommerce/woocommerce.php'] ) ) {
			self::$_activator = true;           
		} else {            
			/**
			 * Notice for admin
			 */
			add_action( 'admin_notices', array( $this, 'inactive_plugin_notice' ) );
		}
	}

	/**
	 * Check Woo Dependency.
	 *
	 * @since 1.0
	 */
	public static function check_woo_dependecies() {
		return self::$_activator;
	}

	/**
	 * DOCFW on not active
	 *
	 * @since 1.0
	 */
	public function inactive_plugin_notice() {
		?>
		<div id="message" class="error">
			<p><?php printf( esc_html( __( 'Currency Switcher webhooks Need Woocommerce to be active!', 'wccs' ) ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Singleton Instance Method to initiate class.
	 *
	 * @since 1.0
	 */
	public static function Instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new Activator();
		}

		return self::$_instance;
	}
}
