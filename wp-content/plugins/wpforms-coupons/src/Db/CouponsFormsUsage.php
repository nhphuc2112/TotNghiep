<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */

namespace WPFormsCoupons\Db;

use WPForms_DB;

/**
 * Coupons usage database table class.
 *
 * @since 1.0.0
 */
class CouponsFormsUsage extends WPForms_DB {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( method_exists( get_parent_class( $this ), '__construct' ) ) {
			parent::__construct();
		}

		$this->table_name  = self::get_table_name();
		$this->primary_key = 'id';
		$this->type        = 'coupons_forms_usage';
	}

	/**
	 * Get the table name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_table_name() {

		global $wpdb;

		return $wpdb->prefix . 'wpforms_coupons_forms_usage';
	}

	/**
	 * Get table columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {

		return [
			'id'             => '%d',
			'coupon_id'      => '%d',
			'form_id'        => '%d',
			'payments_count' => '%d',
			'usage_count'    => '%d',
		];
	}

	/**
	 * Get coupon forms.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 *
	 * @return array
	 */
	public function get_coupon_forms( $coupon_id ) {

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT form_id FROM $this->table_name WHERE coupon_id = %d ORDER BY usage_count", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$coupon_id
			)
		);
	}

	/**
	 * Create table for coupon usage table by form.
	 *
	 * @since      1.0.0
	 * @deprecated 1.2.0
	 */
	public function create_table() {

		_deprecated_function(
			__METHOD__,
			'{WPFORMS_COUPONS_VERSION} of the Coupons addon.',
			'\WPFormsCoupons\Install::create_coupons_forms_usage_table'
		);
	}

	/**
	 * Update coupon usage count.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 * @param int $form_id   Form ID.
	 */
	public function update_counter( $coupon_id, $form_id ) {

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"INSERT INTO $this->table_name
				(coupon_id, form_id, payments_count, usage_count)
				VALUES ( %d, %d, 0, 1 )
				ON DUPLICATE KEY UPDATE
				payments_count = payments_count,
				usage_count = usage_count + 1",
				$coupon_id,
				$form_id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Update coupon payments count.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 * @param int $form_id   Form ID.
	 */
	public function update_payment_counter( $coupon_id, $form_id ) {

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"INSERT INTO $this->table_name
				(coupon_id, form_id, payments_count, usage_count)
				VALUES ( %d, %d, 1, 0 )
				ON DUPLICATE KEY UPDATE
				usage_count = usage_count,
				payments_count = payments_count + 1",
				$coupon_id,
				$form_id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Get coupon counts.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 *
	 * @return array
	 */
	public function get_coupon_counts( $coupon_id ) {

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT form_id, usage_count, payments_count FROM $this->table_name WHERE coupon_id = %d;",
				$coupon_id
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}
