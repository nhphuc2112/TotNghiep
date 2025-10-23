<?php
/**
 * Core: Plugin Bootstrap
 *
 * @package     AffiliateWP Plugin Template
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

if ( ! class_exists( 'AffiliateWP_REST_API_Extended' ) ) {

	/**
	 * Implements write, edit, and delete endpoints for the AffiliateWP REST API.
	 *
	 * @since 1.0.0
	 */
	final class AffiliateWP_REST_API_Extended {

		/**
		 * Holds the instance.
		 *
		 * Ensures that only one instance of AffiliateWP_REST_API_Extended exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    AffiliateWP_REST_API_Extended
		 * @static
		 */
		private static $instance;

		/**
		 * The version number.
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		private $version = '';

		/**
		 * Plugin loader file.
		 *
		 * @since 1.1
		 * @var   string
		 */
		private $file = '';

		/**
		 * Main AffiliateWP_REST_API_Extended instance.
		 *
		 * Insures that only one instance of AffiliateWP_REST_API_Extended exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access public
		 * @since  1.0.0
		 *
		 * @param string $file Main plugin file.
		 * @return AffiliateWP_REST_API_Extended The one true AffiliateWP_REST_API_Extended instance.
		 */
		public static function instance( $file = '' ) {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AffiliateWP_REST_API_Extended ) ) {

				self::$instance = new AffiliateWP_REST_API_Extended;

				self::$instance->file = $file;
				self::$instance->version = get_plugin_data( self::$instance->file, false, false )['Version'] ?? '';

				self::$instance->setup_constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->init();
				self::$instance->hooks();

			}

			return self::$instance;
		}

		/**
		 * Throws an error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @access protected
		 * @since  1.0.0
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-rest-api-extended' ), '1.0' );
		}

		/**
		 * Disables un-serializing of the class.
		 *
		 * @access protected
		 * @since  1.0.0
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-rest-api-extended' ), '1.0' );
		}

		/**
		 * Constructor.
		 *
		 * @access private
		 * @since  1.0.0
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Resets the instance of the class.
		 *
		 * @access public
		 * @since  1.0.0
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Show a warning to sites running AffiliateWP < 1.9.
		 *
		 * @access public
		 * @since  1.0.0
		 * @static
		 */
		public static function below_affwp_version_notice() {
			echo '<div class="error"><p>' . __( 'AffiliateWP - REST API requires AffiliateWP 2.0 or later.', 'affiliatewp-rest-api-extended' ) . '</p></div>';
		}

		/**
		 * Sets up plugin constants.
		 *
		 * @access private
		 * @since  1.0.0
		 */
		private function setup_constants() {
			// Plugin version
			if ( ! defined( 'AFFWP_REST_VERSION' ) ) {
				define( 'AFFWP_REST_VERSION', $this->version );
			}

			// Plugin Folder Path
			if ( ! defined( 'AFFWP_REST_PLUGIN_DIR' ) ) {
				define( 'AFFWP_REST_PLUGIN_DIR', plugin_dir_path( $this->file ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'AFFWP_REST_PLUGIN_URL' ) ) {
				define( 'AFFWP_REST_PLUGIN_URL', plugin_dir_url( $this->file ) );
			}

			// Plugin Root File
			if ( ! defined( 'AFFWP_REST_PLUGIN_FILE' ) ) {
				define( 'AFFWP_REST_PLUGIN_FILE', $this->file );
			}
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function load_textdomain() {

			// Set filter for plugin's languages directory.
			$lang_dir = dirname( plugin_basename( $this->file ) ) . '/languages/';
			$lang_dir = apply_filters( 'affiliatewp_rest_api_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter.
			$locale   = apply_filters( 'plugin_locale',  get_locale(), 'affiliatewp-rest-api-extended' );
			$mofile   = sprintf( '%1$s-%2$s.mo', 'affiliatewp-rest-api-extended', $locale );

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/affiliatewp-rest-api-extended/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/affiliatewp-rest-api-extended/ folder.
				load_textdomain( 'affiliatewp-rest-api-extended', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/affiliatewp-rest-api-extended/languages/ folder.
				load_textdomain( 'affiliatewp-rest-api-extended', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'affiliatewp-rest-api-extended', false, $lang_dir );
			}
		}

		/**
		 * Includes necessary files.
		 *
		 * @access private
		 * @since  1.0.0
		 */
		private function includes() {
			require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/endpoint-loader.php';

			if ( is_admin() ) {
				require_once AFFWP_REST_PLUGIN_DIR . 'includes/admin/class-settings.php';
			}
		}

		/**
		 * Checks for updates to the add-on on plugin initialization.
		 *
		 * @access private
		 * @since  1.0.1
		 *
		 * @see AffWP_AddOn_Updater
		 */
		private function init() {

			if ( is_admin() && class_exists( 'AffWP_AddOn_Updater' ) ) {
				$updater = new AffWP_AddOn_Updater( 158216, $this->file, $this->version );
			}
		}

		/**
		 * Sets up the default hooks and actions.
		 *
		 * @since 1.0.0
		 */
		private function hooks() {
			// Plugin meta.
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), null, 2 );
		}

		/**
		 * Modifies plugin metalinks.
		 *
		 * @access public
		 * @since  1.0.0
		 *
		 * @param array $links The current links array.
		 * @param string $file A specific plugin table entry.
		 * @return array The modified links array.
		 */
		public function plugin_meta( $links, $file ) {
			if ( $file == plugin_basename( $this->file ) ) {
				$plugins_link = array(
					'<a title="' . __( 'Get more add-ons for AffiliateWP', 'affiliatewp-rest-api-extended' ) . '" href="http://affiliatewp.com/addons/" target="_blank">' . __( 'More add-ons', 'affiliatewp-rest-api-extended' ) . '</a>'
				);

				$links = array_merge( $links, $plugins_link );
			}

			return $links;
		}
	}

	/**
	 * The main function responsible for returning the one true AffiliateWP_REST_API_Extended
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $affiliatewp_rest_api_extended = affiliatewp_rest_api_extended(); ?>
	 *
	 * @since 1.0.0
	 *
	 * @return AffiliateWP_REST_API_Extended The one true AffiliateWP_REST_API_Extended instance.
	 */
	function affiliatewp_rest_api_extended() {
		return AffiliateWP_REST_API_Extended::instance();
	}
}
