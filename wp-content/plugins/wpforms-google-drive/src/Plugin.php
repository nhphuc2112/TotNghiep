<?php

namespace WPFormsGoogleDrive;

use WPFormsGoogleDrive\Admin\Admin;
use WPFormsGoogleDrive\Admin\Entry;
use WPFormsGoogleDrive\Admin\Export;
use WPFormsGoogleDrive\Provider\Core;
use WPFormsGoogleDrive\Provider\Account;
use WPFormsGoogleDrive\Admin\Notifications;
use WPForms\Providers\Loader as ProvidersLoader;

/**
 * Class Plugin.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Provider name.
	 *
	 * @since 1.0.0
	 */
	public const NAME = 'Google Drive';

	/**
	 * Provider slug.
	 *
	 * @since 1.0.0
	 */
	public const SLUG = 'google-drive';

	/**
	 * Provider core class.
	 *
	 * @since 1.0.0
	 *
	 * @var Core
	 */
	private $core;

	/**
	 * Account class.
	 *
	 * @since 1.0.0
	 *
	 * @var Account
	 * @noinspection PhpPrivateFieldCanBeLocalVariableInspection
	 */
	private $account;

	/**
	 * Plugin constructor.
	 * This method is empty and private, so others can't initialize a new instance of it.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * Initialize plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin
	 */
	public function init(): Plugin {

		$this->load_dependencies();
		$this->hooks();

		return $this;
	}

	/**
	 * Plugin hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks(): void {

		$form_builder = $this->core->get_form_builder();

		add_filter( 'wpforms_helpers_templates_include_html_located', [ $this, 'register' ], 10, 2 );

		// Display integration on the Settings tab instead of Marketing.
		remove_action( 'wpforms_providers_panel_sidebar', [ $form_builder, 'display_sidebar' ], $this->core::PRIORITY );
		remove_action( 'wpforms_providers_panel_content', [ $form_builder, 'display_content' ], $this->core::PRIORITY );

		$process = $this->core->get_process();

		$process->hooks();

		// Run processing after all marketing addons are processed.
		// It warranties we can delete local files but keep URLs up to date.
		remove_action( 'wpforms_process_complete', [ $process, 'process' ], 5 );
		add_action( 'wpforms_process_complete', [ $process, 'process' ], 1000, 4 );
	}

	/**
	 * All the actual plugin loading is done here.
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies(): void {

		$this->core = Core::get_instance();

		$this->account = new Account();

		$this->account->hooks();

		( new Admin() )->init();
		( new Entry() )->init();
		( new Export() )->init();
		( new Links() )->init();
		( new Notifications() )->init();

		ProvidersLoader::get_instance()->register( $this->core );
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {

		static $instance;

		if ( ! $instance ) {
			$instance = ( new self() )->init();
		}

		return $instance;
	}

	/**
	 * Get property.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property_name Property name.
	 *
	 * @return object|null
	 */
	public function get( string $property_name ): ?object {

		return property_exists( $this, $property_name ) ? $this->{$property_name} : null;
	}

	/**
	 * Register an addon location.
	 *
	 * @since 1.0.0
	 *
	 * @param string $located  Template location.
	 * @param string $template Template.
	 *
	 * @return string
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function register( $located, string $template ): string {

		// Checking if `$template` is an absolute path and passed from this plugin.
		if (
			strpos( $template, WPFORMS_GOOGLE_DRIVE_PATH ) === 0 &&
			is_readable( $template )
		) {
			return $template;
		}

		return (string) $located;
	}
}
