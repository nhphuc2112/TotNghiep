<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */

namespace WPFormsCoupons\Db;

use WPForms_DB;

/**
 * Pivot database table class.
 *
 * @since 1.0.0
 */
class CouponsForms extends WPForms_DB {

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
		$this->type        = 'coupons_forms';
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

		return $wpdb->prefix . 'wpforms_coupons_forms';
	}

	/**
	 * Bulk add forms for a coupon.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $coupon_id Coupon ID.
	 * @param array $form_ids  Form IDs.
	 */
	public function add_bulk_forms( $coupon_id, $form_ids ) {

		global $wpdb;

		$sql = "INSERT IGNORE INTO $this->table_name ( coupon_id, form_id ) VALUES ";

		foreach ( $form_ids as $form_id => $value ) {
			$sql .= $wpdb->prepare( '(%d, %d),', $coupon_id, $form_id );
		}

		$sql = rtrim( $sql, ',' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $sql );
	}

	/**
	 * Bulk add coupons for a form.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $form_id    Form ID.
	 * @param array $coupon_ids Coupon IDs.
	 */
	public function add_bulk_coupons( $form_id, $coupon_ids ) {

		global $wpdb;

		$sql = "INSERT IGNORE INTO $this->table_name ( coupon_id, form_id ) VALUES ";

		foreach ( $coupon_ids as $coupon_id ) {
			$sql .= $wpdb->prepare( '(%d, %d),', $coupon_id, $form_id );
		}

		$sql = rtrim( $sql, ',' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $sql );
	}

	/**
	 * Delete all coupon forms.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 */
	public function delete_all_coupon_forms( $coupon_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $this->table_name WHERE coupon_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$coupon_id
			)
		);
	}

	/**
	 * Delete all form coupons.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $form_id    Form ID.
	 * @param array $coupon_ids Coupon IDs.
	 */
	public function delete_bulk_coupons( $form_id, $coupon_ids ) {

		global $wpdb;

		$sql = $wpdb->prepare(
			"DELETE FROM $this->table_name WHERE form_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$form_id
		);

		$sql .= ' AND coupon_id IN (';

		foreach ( $coupon_ids as $coupon_id ) {
			$sql .= $wpdb->prepare( '%d,', $coupon_id );
		}

		$sql = rtrim( $sql, ',' ) . ');';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $sql );
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
			'id'        => '%d',
			'coupon_id' => '%d',
			'form_id'   => '%d',
		];
	}

	/**
	 * Get coupon allowed forms.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 *
	 * @return array
	 */
	public function get_coupon_forms( $coupon_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT form_id FROM $this->table_name WHERE coupon_id = %d;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(int) $coupon_id
			)
		);

		return array_map( 'absint', $form_ids );
	}

	/**
	 * Get allowed coupons for form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return array
	 */
	public function get_form_coupons( $form_id ) {

		global $wpdb;

		$coupons_table_name = Coupons::get_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		$coupon_ids = $wpdb->get_col(
		 	$wpdb->prepare(
				"SELECT cf.coupon_id
					FROM $this->table_name cf
					LEFT JOIN $coupons_table_name c ON cf.coupon_id = c.id
					WHERE cf.form_id = %d
					AND c.status = 'publish'",
				(int) $form_id
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		return array_map( 'absint', $coupon_ids );
	}

	/**
	 * Create table for coupon form pivot table.
	 *
	 * @since      1.0.0
	 * @deprecated 1.2.0
	 */
	public function create_table() {

		_deprecated_function(
			__METHOD__,
			'{WPFORMS_COUPONS_VERSION} of the Coupons addon.',
			'\WPFormsCoupons\Install::create_coupons_forms_table'
		);
	}
}
