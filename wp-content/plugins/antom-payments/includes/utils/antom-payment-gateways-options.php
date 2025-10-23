<?php
/**
 * Antom_Payment_Gateways_Options
 *
 * @user Antom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Antom_Payment_Gateways_Options class provides a convenient way to manage and access options related to the "Antom Payment Gateways" plugin within the WordPress environment.
 */
final class Antom_Payment_Gateways_Options {

	protected static $_instance = null;
	protected $option_prefix    = 'antom_payment_gateway_';

	protected function __construct() {
	}

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}


	/**
	 * A method within the Antom_Payment_Gateways_Options class. This method is used to retrieve a specific option value from the class's options array.
	 *
	 * @param mixed $option_name option name.
	 * @param mixed $default default value will be return when the value is not set.
	 * @return mixed
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function get_option( $option_name, $default = null ) {
		return get_option( $this->option_prefix . $option_name, $default );
	}

	/**
	 * This method provides a convenient way to set or update options within the Antom_Payment_Gateways_Options class. It ensures that the options are stored in both the class's options array and the WordPress database.
	 *
	 * @param $option_name
	 * @param $option_value
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function set_option( $option_name, $option_value ) {
		$new_option_name = $this->option_prefix . $option_name;

		update_option( $new_option_name, $option_value );
	}

	/**
	 * This method provides a way to access the option prefix, which can be useful for various purposes, such as constructing option names or performing operations related to the option prefix.
	 *
	 * @return string
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function get_option_prefix() {
		return $this->option_prefix;
	}
}
