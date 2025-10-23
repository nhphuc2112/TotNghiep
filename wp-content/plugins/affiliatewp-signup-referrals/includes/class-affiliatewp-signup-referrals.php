<?php
/**
 * Core: Plugin Bootstrap
 *
 * @package     AffiliateWP Signup Referrals
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AffiliateWP_Signup_Referrals' ) ) {

	/**
	 * Main plugin bootstrap class.
	 *
	 * @since 1.0
	 */
	final class AffiliateWP_Signup_Referrals {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of AffiliateWP_Signup_Referrals exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @var object
		 * @static
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Plugin loader file.
		 *
		 * @since 1.1
		 * @var   string
		 */
		private $file = '';

		/**
		 * The version number of AffiliateWP
		 *
		 * @since 1.0
		 */
		private $version = '1.2';

		/**
		 * Represents the Signup Referrals base integration class.
		 *
		 * @since 1.1
		 * @var   \AffiliateWP_Signup_Referrals_Base
		 */
		public $base;

		/**
		 * Holds the admin setup class instance.
		 *
		 * @since 1.1
		 * @var   \AffiliateWP_Signup_Referrals_Admin
		 */
		private $admin;

		/**
		 * Main AffiliateWP_Signup_Referrals Instance
		 *
		 * Insures that only one instance of AffiliateWP_Signup_Referrals exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0
		 * @static
		 *
		 * @param string $file Path to the main plugin file.
		 * @return \AffiliateWP_Signup_Referrals The one true bootstrap instance.
		 */
		public static function instance( $file = '' ) {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AffiliateWP_Signup_Referrals ) ) {

				self::$instance = new AffiliateWP_Signup_Referrals;
				self::$instance->file = $file;

				self::$instance->setup_constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->init();
				self::$instance->hooks();

			}

			return self::$instance;
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-signup-referrals' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-signup-referrals' ), '1.0' );
		}

		/**
		 * Constructor Function
		 *
		 * @since 1.0
		 * @access private
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Reset the instance of the class
		 *
		 * @since 1.0
		 * @access public
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version
			if ( ! defined( 'AFFWP_SR_VERSION' ) ) {
				define( 'AFFWP_SR_VERSION', $this->version );
			}

			// Plugin Folder Path
			if ( ! defined( 'AFFWP_SR_PLUGIN_DIR' ) ) {
				define( 'AFFWP_SR_PLUGIN_DIR', plugin_dir_path( $this->file ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'AFFWP_SR_PLUGIN_URL' ) ) {
				define( 'AFFWP_SR_PLUGIN_URL', plugin_dir_url( $this->file ) );
			}

			// Plugin Root File
			if ( ! defined( 'AFFWP_SR_PLUGIN_FILE' ) ) {
				define( 'AFFWP_SR_PLUGIN_FILE', $this->file );
			}

		}

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 1.0
		 * @return void
		 */
		public function load_textdomain() {

			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( $this->file ) ) . '/languages/';
			$lang_dir = apply_filters( 'affwp_signup_referrals_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale   = apply_filters( 'plugin_locale',  get_locale(), 'affiliatewp-signup-referrals' );
			$mofile   = sprintf( '%1$s-%2$s.mo', 'affiliatewp-signup-referrals', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/affiliatewp-signup-referrals/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/affiliatewp-signup-referrals/ folder
				load_textdomain( 'affiliatewp-signup-referrals', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/affiliatewp-signup-referrals/languages/ folder
				load_textdomain( 'affiliatewp-signup-referrals', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'affiliatewp-signup-referrals', false, $lang_dir );
			}
		}

		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {

			require_once AFFWP_SR_PLUGIN_DIR . 'includes/functions.php';
			require_once AFFWP_SR_PLUGIN_DIR . 'includes/class-base.php';

			if ( is_admin() ) {
				require_once AFFWP_SR_PLUGIN_DIR . 'includes/class-admin.php';
			}

		}

		/**
		 * Init
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function init() {
			$this->base = new \AffiliateWP_Signup_Referrals_Base();

			if ( is_admin() ) {
				$this->admin = new \AffiliateWP_Signup_Referrals_Admin();

				self::$instance->updater();
			}

		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function hooks() {

			// plugin meta
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), null, 2 );

		}

		/**
		 * Load the custom plugin updater
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		public function updater() {

			if ( class_exists( 'AffWP_AddOn_Updater' ) ) {
				$updater = new AffWP_AddOn_Updater( 64096, $this->file, AFFWP_SR_VERSION );
			}
		}

		/**
		 * Modify plugin metalinks
		 *
		 * @access      public
		 * @since       1.0.0
		 * @param       array $links The current links array
		 * @param       string $file A specific plugin table entry
		 * @return      array $links The modified links array
		 */
		public function plugin_meta( $links, $file ) {
		    if ( $file == plugin_basename( $this->file ) ) {
		        $plugins_link = array(
		            '<a title="' . __( 'Get more add-ons for AffiliateWP', 'affiliatewp-signup-referrals' ) . '" href="http://affiliatewp.com/addons/" target="_blank">' . __( 'More add-ons', 'affiliatewp-signup-referrals' ) . '</a>'
		        );

		        $links = array_merge( $links, $plugins_link );
		    }

		    return $links;
		}


	}

}

/**
 * The main function responsible for returning the one true AffiliateWP_Signup_Referrals
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $affwp_sign_up_referrals = affiliatewp_signup_referrals(); ?>
 *
 * @since 1.0.0
 * @return object The one true AffiliateWP_Signup_Referrals Instance
 */
function affiliatewp_signup_referrals() {
	return AffiliateWP_Signup_Referrals::instance();
}
