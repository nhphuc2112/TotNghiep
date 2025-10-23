<?php

namespace WPFormsCalculations;

use WPForms_Updater;
use WPForms\Integrations\AI\Helpers as AIHelpers;

/**
 * WPForms Calculations addon main class.
 *
 * @since 1.0.0
 */
final class Plugin {

	/**
	 * WPForms updater class instance.
	 *
	 * @since      1.2.0
	 * @deprecated 1.3.0
	 * @todo       Remove with core 1.9.2
	 *
	 * @var WPForms_Updater
	 */
	public $updater;

	/**
	 * Helpers class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Helpers
	 */
	public $helpers;

	/**
	 * Date and time helpers class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Transpiler\DateTime
	 */
	public $datetime;

	/**
	 * Process class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Process\Process
	 */
	public $process;

	/**
	 * Functions class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Transpiler\Functions
	 */
	public $functions;

	/**
	 * Inner functions class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Transpiler\InnerFunctions
	 */
	public $inner_functions;

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->load_dependencies();
	}

	/**
	 * All the actual plugin loading is done here.
	 *
	 * @since 1.2.0
	 */
	private function load_dependencies() {

		$this->helpers         = new Helpers();
		$this->datetime        = new Transpiler\DateTime();
		$this->inner_functions = new Transpiler\InnerFunctions();
		$this->functions       = new Transpiler\Functions();

		if ( wpforms_is_admin_page( 'builder' ) || wpforms_is_admin_ajax() ) {
			( new Admin\Builder() )->init();
		}

		if ( wpforms_is_admin_ajax() ) {
			( new Admin\Ajax() )->init();

			if ( AIHelpers::is_disabled() ) {
				return;
			}

			( new AI\Admin\Ajax\Calculations() )->init();
		}

		if ( is_admin() ) {
			( new Admin\EntriesEdit() )->init();
			( new Admin\Admin() )->init();
		} else {
			( new Frontend() )->init();
		}

		$this->process = new Process\Process();

		new Process\Addons();
	}

	/**
	 * Get a single instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin
	 */
	public static function get_instance() {

		static $instance = null;

		if ( ! $instance instanceof self ) {
			$instance = new self();

			$instance->init();
		}

		return $instance;
	}

	/**
	 * Load the plugin updater.
	 *
	 * @since 1.2.0
	 * @deprecated 1.3.0
	 *
	 * @todo Remove with core 1.9.2
	 *
	 * @param string $key License key.
	 */
	public function updater( $key ) {

		_deprecated_function( __METHOD__, '1.3.0 of the WPForms Calculations plugin' );

		$this->updater = new WPForms_Updater(
			[
				'plugin_name' => 'WPForms Calculations',
				'plugin_slug' => 'wpforms-calculations',
				'plugin_path' => plugin_basename( WPFORMS_CALCULATIONS_FILE ),
				'plugin_url'  => trailingslashit( WPFORMS_CALCULATIONS_URL ),
				'remote_url'  => WPFORMS_UPDATER_API,
				'version'     => WPFORMS_CALCULATIONS_VERSION,
				'key'         => $key,
			]
		);
	}
}
