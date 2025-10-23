<?php
namespace Wpexperts\CurrencySwitcherForWoocommerce;

if (!class_exists('Deactivator')) {
	
	class Deactivator {

		// Hold the class instance.
		private static $_instance = null; 

		/**
		 * Contructor of class.
		 *
		 * @since 1.0
		 */
		private function __construct() {        
			wp_clear_scheduled_hook('wccs_update_rates', array( true ));
		} 

		/**
		 * Singleton Instance Method to initiate class.
		 *
		 * @since 1.0
		 */
		public static function Instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new Deactivator();
			}

			return self::$_instance;
		}
	}
	
}
