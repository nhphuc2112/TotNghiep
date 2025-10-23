<?php

namespace WPFormsCoupons;

use stdClass;
use WPForms_Updater;
use WPFormsCoupons\Admin\Builder;
use WPFormsCoupons\Db\Repository;
use WPFormsCoupons\Db\Coupons;
use WPFormsCoupons\Admin\Settings;
use WPFormsCoupons\Db\CouponsForms;
use WPFormsCoupons\Admin\Coupons\Edit;
use WPFormsCoupons\Admin\EntryPages;
use WPFormsCoupons\Admin\PaymentPages;
use WPFormsCoupons\Db\CouponsFormsUsage;
use WPFormsCoupons\Admin\Coupons\Overview;
use WPFormsCoupons\Admin\Coupons\ScreenOptions;

/**
 * Coupons Main class.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Provider slug.
	 *
	 * @since 1.0.0
	 */
	public const SLUG = 'coupons';

	/**
	 * Coupons repository.
	 *
	 * @since 1.0.0
	 *
	 * @var Repository
	 */
	private $repository;

	/**
	 * Plugin constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * Initialize plugin.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {

		$this->hooks();
		$this->load_dependencies();
	}

	/**
	 * Plugin hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks(): void {

		add_filter( 'wpforms_helpers_templates_include_html_located', [ $this, 'templates' ], 10, 2 );
	}

	/**
	 * Get property.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property_name Property name.
	 *
	 * @return mixed
	 */
	public function get( $property_name ) {

		return property_exists( $this, $property_name ) ? $this->{$property_name} : new stdClass();
	}

	/**
	 * Get a single instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin
	 */
	public static function get_instance(): ?Plugin {

		static $instance = null;

		if ( ! $instance ) {
			$instance = new Plugin();

			$instance->init();
		}

		return $instance;
	}

	/**
	 * All the actual plugin loading is done here.
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies(): void {

		$this->repository = new Repository( new Coupons(), new CouponsForms(), new CouponsFormsUsage() );

		$overview = new Overview();
		$edit     = new Edit();

		( new Builder() )->hooks();
		( new ScreenOptions() )->hooks();
		( new Settings() )->hooks();
		( new Generator() )->hooks();
		( new EntryPages() )->hooks();
		( new PaymentPages( $overview, $edit ) )->hooks();
		( new Frontend() )->hooks();
		( new BlockEditor() )->hooks();
		( new Integrations\Loader() )->init();
	}

	/**
	 * Change a template location.
	 *
	 * @since 1.0.0
	 *
	 * @param string|mixed $located  Template location.
	 * @param string       $template Template.
	 *
	 * @return string
	 */
	public function templates( $located, $template ): string {

		// Checking if `$template` is an absolute path and passed from this plugin.
		if (
			( strpos( $template, WPFORMS_COUPONS_PATH ) === 0 ) &&
			is_readable( $template )
		) {
			return $template;
		}

		return (string) $located;
	}

	/**
	 * Load the plugin updater.
	 *
	 * @since 1.0.0
	 * @deprecated 1.5.0
	 *
	 * @todo Remove with core 1.9.2
	 *
	 * @param string $key License key.
	 */
	public function updater( $key ): void {

		_deprecated_function( __METHOD__, '1.5.0 of the WPForms Coupons plugin' );

		new WPForms_Updater(
			[
				'plugin_name' => 'WPForms Coupons',
				'plugin_slug' => 'wpforms-coupons',
				'plugin_path' => plugin_basename( WPFORMS_COUPONS_FILE ),
				'plugin_url'  => trailingslashit( WPFORMS_COUPONS_URL ),
				'remote_url'  => WPFORMS_UPDATER_API,
				'version'     => WPFORMS_COUPONS_VERSION,
				'key'         => $key,
			]
		);
	}
}
