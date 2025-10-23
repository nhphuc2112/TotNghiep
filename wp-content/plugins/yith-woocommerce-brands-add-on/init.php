<?php
/**
 * Plugin Name: YITH WooCommerce Brands Add-On
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/
 * Description: <code><strong>YITH WooCommerce Brands Add-On</strong></code> allows organizing products by brand and improves your shop user experience and your visibility on search engines. Let your customers browse your shop based on their favourite brands with only a few clicks. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce on <strong>YITH</strong></a>
 * Version: 2.34.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-brands-add-on
 * Domain Path: /languages/
 * WC requires at least: 9.9
 * WC tested up to: 10.1
 * Requires Plugins: woocommerce
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Brands
 * @version 2.34.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( ! defined( 'YITH_WCBR' ) ) {
	define( 'YITH_WCBR', true );
}

if ( ! defined( 'YITH_WCBR_PLUGIN_NAME' ) ) {
	define( 'YITH_WCBR_PLUGIN_NAME', 'YITH WooCommerce Brands Add-On' );
}

if ( ! defined( 'YITH_WCBR_VERSION' ) ) {
	define( 'YITH_WCBR_VERSION', '2.34.0' );
}

if ( ! defined( 'YITH_WCBR_URL' ) ) {
	define( 'YITH_WCBR_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YITH_WCBR_DIR' ) ) {
	define( 'YITH_WCBR_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YITH_WCBR_INC' ) ) {
	define( 'YITH_WCBR_INC', YITH_WCBR_DIR . 'includes/' );
}

if ( ! defined( 'YITH_WCBR_INIT' ) ) {
	define( 'YITH_WCBR_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_WCBR_SLUG' ) ) {
	define( 'YITH_WCBR_SLUG', 'yith-woocommerce-brands-add-on' );
}

if ( ! defined( 'YITH_WCBR_FREE_INIT' ) ) {
	define( 'YITH_WCBR_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_WCBR_VIEWS' ) ) {
	define( 'YITH_WCBR_VIEWS', YITH_WCBR_DIR . 'views/' );
}

if ( ! defined( 'YITH_WCBR_ASSETS_URL' ) ) {
	define( 'YITH_WCBR_ASSETS_URL', YITH_WCBR_URL . 'assets' );
}


// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}

if ( ! function_exists( 'yith_brands_constructor' ) ) {
	/**
	 * Plugin constructor.
	 */
	function yith_brands_constructor() {

		if ( function_exists( 'yith_plugin_fw_load_plugin_textdomain' ) ) {
			yith_plugin_fw_load_plugin_textdomain( 'yith-woocommerce-brands-add-on', basename( dirname( __FILE__ ) ) . '/languages' );
		}

		require_once YITH_WCBR_INC . 'functions.yith-wcbr.php';
		require_once YITH_WCBR_INC . 'class-yith-wcbr.php';
		require_once YITH_WCBR_INC . 'class-yith-wcbr-shortcode.php';
		require_once YITH_WCBR_INC . 'class-yith-wcbr-template-loader.php';

		// Let's start the game.
		YITH_WCBR();

		if ( is_admin() ) {
			require_once YITH_WCBR_INC . 'class-yith-wcbr-admin.php';

			YITH_WCBR_Admin();
		}
	}
}
add_action( 'yith_wcbr_init', 'yith_brands_constructor' );

if ( ! function_exists( 'yith_brands_install' ) ) {
	/**
	 * Plugin install.
	 */
	function yith_brands_install() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_wcbr_install_woocommerce_admin_notice' );
		} elseif ( defined( 'YITH_WCBR_PREMIUM' ) ) {
			add_action( 'admin_notices', 'yith_wcbr_install_free_admin_notice' );
			deactivate_plugins( plugin_basename( __FILE__ ) );
		} else {
			/**
			 * DO_ACTION: yith_wcbr_init
			 *
			 * Allows the plugin initialization.
			 */
			do_action( 'yith_wcbr_init' );
		}
	}
}
add_action( 'plugins_loaded', 'yith_brands_install', 11 );

if ( ! function_exists( 'yith_wcbr_install_woocommerce_admin_notice' ) ) {
	/**
	 * Woocommerce required notice.
	 */
	function yith_wcbr_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<?php /* translators: %s: plugin name */ ?>
			<p>
				<?php
					// translators: %s is the plugin name.
					echo esc_html( sprintf( __( '%s is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-brands-add-on' ), YITH_WCBR_PLUGIN_NAME ) );
				?>
			</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'yith_wcbr_install_free_admin_notice' ) ) {
	/**
	 * Free version unable due to already premium version notice.
	 */
	function yith_wcbr_install_free_admin_notice() {
		?>
		<div class="error">
			<p>
				<?php
					// translators: %s is the plugin name.
					echo esc_html( sprintf( __( 'You can\'t activate the free version of %s while you are using the premium one.', 'yith-woocommerce-brands-add-on' ), YITH_WCBR_PLUGIN_NAME ) );
				?>
			</p>
		</div>
		<?php
	}
}
