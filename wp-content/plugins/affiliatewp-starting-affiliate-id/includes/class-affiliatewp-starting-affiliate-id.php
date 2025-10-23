<?php
/**
 * Main plugin bootstrap.
 *
 * @package AffiliateWP_Starting_Affiliate_ID
 *
 * @since 1.0.0
 */
use AffiliateWP_Starting_Affiliate_ID as AffiliateWP_SAI;

/**
 * AffiliateWP addon bootstrap.
 *
 * @since 1.0.0
 * @final
 */
final class AffiliateWP_Starting_Affiliate_ID {

	/**
	 * Holds the instance.
	 *
	 * Ensures that only one instance of the plugin bootstrap exists in memory at any
	 * one time and it also prevents needing to define globals all over the place.
	 *
	 * TL;DR This is a static property property that holds the singleton instance.
	 *
	 * @since 1.0.0
	 * @var   \AffiliateWP_Starting_Affiliate_ID
	 * @static
	 */
	private static $instance;

	/**
	 * Plugin loader file.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $file = '';

	/**
	 * The version number.
	 *
	 * @since 1.0.0
	 * @var    string
	 */
	private $version = '1.2';

	/**
	 * Generates the main bootstrap instance.
	 *
	 * Insures that only one instance of bootstrap exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 *
	 * @param string $file Path to the main plugin file.
	 * @return \AffiliateWP_Starting_Affiliate_ID The one true bootstrap instance.
	 */
	public static function instance( $file = '' ) {
		// Return if already instantiated.
		if ( self::is_instantiated() ) {
			return self::$instance;
		}

		// Setup the singleton.
		self::setup_instance( $file );

		self::$instance->setup_constants();
		self::$instance->load_textdomain();
		self::$instance->hooks();
		self::$instance->start();

		return self::$instance;
	}

	/**
	 * Setup the singleton instance
	 *
	 * @since 1.0.0
	 *
	 * @param string $file File path to the main plugin file.
	 */
	private static function setup_instance( $file ) {
		self::$instance       = new AffiliateWP_Starting_Affiliate_ID;
		self::$instance->file = $file;
	}

	/**
	 * Return whether the main loading class has been instantiated or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if instantiated. False if not.
	 */
	private static function is_instantiated() {

		// Return true if instance is correct class
		if ( ! empty( self::$instance ) && ( self::$instance instanceof AffiliateWP_Starting_Affiliate_ID ) ) {
			return true;
		}

		// Return false if not instantiated correctly.
		return false;
	}

	/**
	 * Throws an error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-starting-affiliate-id' ), '1.0' );
	}

	/**
	 * Disables unserialization of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-starting-affiliate-id' ), '1.0' );
	}

	/**
	 * Sets up the class.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		self::$instance = $this;
	}

	/**
	 * Resets the instance of the class.
	 *
	 * @since 1.0.0
	 * @static
	 */
	public static function reset() {
		self::$instance = null;
	}

	/**
	 * Sets up plugin constants.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'AFFWP_SAI_VERSION' ) ) {
			define( 'AFFWP_SAI_VERSION', $this->version );
		}

		// Plugin Folder Path.
		if ( ! defined( 'AFFWP_SAI_PLUGIN_DIR' ) ) {
			define( 'AFFWP_SAI_PLUGIN_DIR', plugin_dir_path( $this->file ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'AFFWP_SAI_PLUGIN_URL' ) ) {
			define( 'AFFWP_SAI_PLUGIN_URL', plugin_dir_url( $this->file ) );
		}

		// Plugin Root File.
		if ( ! defined( 'AFFWP_SAI_PLUGIN_FILE' ) ) {
			define( 'AFFWP_SAI_PLUGIN_FILE', $this->file );
		}
	}

	/**
	 * Loads the add-on language files.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$lang_dir = dirname( plugin_basename( $this->file ) ) . '/languages/';

		/**
		 * Filters the languages directory for the add-on.
		 *
		 * @since 1.0.0
		 *
		 * @param string $lang_dir Language directory.
		 */
		$lang_dir = apply_filters( 'affiliatewp_starting_affiliate_id_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter..
		$locale = apply_filters( 'plugin_locale',  get_locale(), 'affiliatewp-starting-affiliate-id' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'affiliatewp-starting-affiliate-id', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/affiliatewp-starting-affiliate-id/' . $mofile;

		if ( file_exists( $mofile_global ) ) {

			// Look in global /wp-content/languages/affiliatewp-starting-affiliate-id/ folder.
			load_textdomain( 'affiliatewp-starting-affiliate-id', $mofile_global );

		} elseif ( file_exists( $mofile_local ) ) {

			// Look in local /wp-content/plugins/affiliatewp-starting-affiliate-id/languages/ folder.
			load_textdomain( 'affiliatewp-starting-affiliate-id', $mofile_local );

		} else {

			// Load the default language files.
			load_plugin_textdomain( 'affiliatewp-starting-affiliate-id', false, $lang_dir );

		}
	}

	/**
	 * Sets up the default hooks and actions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function hooks() {
		// Plugin meta.
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), null, 2 );
	}

	/**
	 * Sets up plugin-specific actions and classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function start() {
		if ( is_admin() ) {
			// Include admin settings hooks
			require_once AFFWP_SAI_PLUGIN_DIR . 'includes/class-admin.php';
		}
	}

	/**
	 * Modifies the plugin list table meta links.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $links The current links array.
	 * @param string $file  A specific plugin table entry.
	 * @return array The modified links array.
	 */
	public function plugin_meta( $links, $file ) {

		if ( $file == plugin_basename( $this->file ) ) {

			$url = admin_url( 'admin.php?page=affiliate-wp-add-ons' );

			$plugins_link = array( '<a alt="' . esc_attr__( 'Get more add-ons for AffiliateWP', 'affiliatewp-starting-affiliate-id' ) . '" href="' . esc_url( $url ) . '">' . __( 'More add-ons', 'affiliatewp-starting-affiliate-id' ) . '</a>' );

			$links = array_merge( $links, $plugins_link );
		}

		return $links;

	}
}

/**
 * The main function responsible for returning the one true bootstrap instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $affiliatewp_starting_affiliate_id = affiliatewp_starting_affiliate_id(); ?>
 *
 * @since 1.0.0
 *
 * @return \AffiliateWP_Starting_Affiliate_ID The one true bootstrap instance.
 */
function affiliatewp_starting_affiliate_id() {
	return AffiliateWP_Starting_Affiliate_ID::instance();
}
