<?php

namespace WPFormsCoupons;

use WP_Site;

/**
 * Coupons addon install.
 *
 * @since 1.2.0
 */
class Install {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.2.0
	 */
	public function hooks() {

		register_activation_hook( WPFORMS_COUPONS_FILE, [ $this, 'install' ] );

		add_action( 'wp_initialize_site', [ $this, 'new_multisite_blog' ], 10, 2 );
	}

	/**
	 * Perform certain actions on plugin activation.
	 *
	 * @since 1.2.0
	 *
	 * @param bool|mixed $network_wide Whether to enable the plugin for all sites in the network
	 *                                 or just the current site. Multisite only. Default is false.
	 *
	 * @noinspection DisconnectedForeachInstructionInspection
	 * @noinspection OneTimeUseVariablesInspection
	 */
	public function install( $network_wide = false ) {

		// Normal single site.
		if ( ! ( $network_wide && is_multisite() ) ) {
			$this->run();

			return;
		}

		// Multisite - go through each subsite and run the installer.
		$sites = get_sites(
			[
				'fields' => 'ids',
				'number' => 0,
			]
		);

		foreach ( $sites as $blog_id ) {
			switch_to_blog( $blog_id );
			$this->run();
			restore_current_blog();
		}
	}

	/**
	 * When a new site is created in multisite, see if we are network activated,
	 * and if so run the installer.
	 *
	 * @since 1.2.0
	 *
	 * @param WP_Site $new_site New site object.
	 * @param array   $args     Arguments for the initialization.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function new_multisite_blog( $new_site, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( is_plugin_active_for_network( plugin_basename( WPFORMS_COUPONS_FILE ) ) ) {
			switch_to_blog( $new_site->blog_id );
			$this->run();
			restore_current_blog();
		}
	}

	/**
	 * Run the actual installer.
	 *
	 * @since 1.2.0
	 */
	private function run() {

		if ( ! $this->tables_exist() ) {
			$this->create_coupons_table();
			$this->create_coupons_forms_table();
			$this->create_coupons_forms_usage_table();
		}

		update_option( 'wpforms_coupons_version', WPFORMS_COUPONS_VERSION );
	}

	/**
	 * If required tables exist.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	private function tables_exist(): bool {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return count( $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}wpforms_coupons%'" ) ) === 3;
	}

	/**
	 * Create table for coupon entity.
	 *
	 * @since 1.2.0
	 */
	private function create_coupons_table() {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$table           = $wpdb->prefix . 'wpforms_coupons';
		$charset_collate = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE $table (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(200) NOT NULL,
				code VARCHAR(36) NOT NULL,
				discount_amount DECIMAL(26,8) NOT NULL,
				discount_type ENUM('flat', 'percentage') NOT NULL DEFAULT 'flat',
				usage_limit SMALLINT UNSIGNED NULL,
				usage_limit_reached TINYINT NOT NULL DEFAULT 0,
				start_date_time_gmt DATETIME DEFAULT NULL,
				end_date_time_gmt DATETIME DEFAULT NULL,
				status VARCHAR(36) NOT NULL DEFAULT 'publish',
				is_global TINYINT NOT NULL DEFAULT 0,
				date_created_gmt TIMESTAMP NOT NULL,
				PRIMARY KEY (id),
				KEY code (code)
			) $charset_collate;"
		);
	}

	/**
	 * Create table for coupon form pivot table.
	 *
	 * @since 1.2.0
	 */
	private function create_coupons_forms_table() {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$table           = $wpdb->prefix . 'wpforms_coupons_forms';
		$charset_collate = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE $table (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				coupon_id BIGINT UNSIGNED NOT NULL,
				form_id BIGINT UNSIGNED NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY coupon_form_ids (coupon_id, form_id)
			) $charset_collate;"
		);
	}

	/**
	 * Create table for coupon usage table by form.
	 *
	 * @since 1.2.0
	 */
	private function create_coupons_forms_usage_table() {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$table           = $wpdb->prefix . 'wpforms_coupons_forms_usage';
		$charset_collate = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				coupon_id BIGINT UNSIGNED NOT NULL,
				form_id BIGINT UNSIGNED NOT NULL,
				payments_count MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
				usage_count MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
				UNIQUE KEY coupon_form_ids (coupon_id, form_id)
			) {$charset_collate};"
		);
	}
}
