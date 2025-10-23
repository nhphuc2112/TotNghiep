<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */

namespace WPFormsCoupons\Db;

use WPForms_DB;

/**
 * Coupons database table class.
 *
 * @since 1.0.0
 */
class Coupons extends WPForms_DB {

	/**
	 * Coupons forms usage table.
	 *
	 * @since 1.0.0
	 *
	 * @var CouponsFormsUsage
	 */
	private $usage_table;

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
		$this->type        = 'coupons';
		$this->usage_table = CouponsFormsUsage::get_table_name();
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

		return $wpdb->prefix . 'wpforms_coupons';
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
			'id'                  => '%d',
			'name'                => '%s',
			'code'                => '%s',
			'discount_amount'     => '%f',
			'discount_type'       => '%s',
			'usage_limit'         => '%d',
			'usage_limit_reached' => '%d',
			'start_date_time_gmt' => '%s',
			'end_date_time_gmt'   => '%s',
			'status'              => '%s',
			'is_global'           => '%s',
			'date_created_gmt'    => '%s',
		];
	}

	/**
	 * Set default values for all insert/update queries.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_column_defaults(): array {

		return [
			'date_created_gmt' => gmdate( 'Y-m-d H:i:s' ),
		];
	}

	/**
	 * Insert a new record into the database.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $data Column data.
	 * @param string $type Optional. Data type context.
	 *
	 * @return int ID for the newly inserted record. Zero otherwise.
	 */
	public function add( $data, $type = '' ) {

		if ( empty( $data['code'] ) || empty( $data['name'] ) || empty( $data['discount_amount'] ) ) {
			return 0;
		}

		$data['code'] = strtoupper( $data['code'] );

		return parent::add( $data, $type );
	}


	/**
	 * Get coupon by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $row_id Coupon ID.
	 *
	 * @return array
	 */
	public function get( $row_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (array) $wpdb->get_row(
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT coupons.*, SUM(usages.usage_count) AS usage_count
				FROM $this->table_name as coupons
					LEFT JOIN $this->usage_table AS usages
					    ON coupons.id = usages.coupon_id
				WHERE coupons.$this->primary_key = %d
				GROUP BY coupons.id LIMIT 1;",
				(int) $row_id
			)
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Get a coupon by code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $coupon_code Coupon code.
	 *
	 * @return array
	 */
	public function get_by_code( $coupon_code ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (array) $wpdb->get_row(
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT coupons.*, SUM(usages.usage_count) AS usage_count
				FROM $this->table_name as coupons
				    LEFT JOIN $this->usage_table AS usages
				        ON coupons.id = usages.coupon_id
				WHERE coupons.code = %s
				GROUP BY coupons.id LIMIT 1;",
				$coupon_code
			)
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Return all coupons with limits and offsets for pagination.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Array of arguments.
	 *
	 * @return array
	 */
	public function get_coupons( $args ) {

		global $wpdb;

		$args = wp_parse_args(
			$args,
			[
				'limit'   => 20,
				'orderby' => 'id',
				'order'   => 'DESC',
				'is_used' => null,
			]
		);

		$sql = "SELECT coupons.*, COALESCE(SUM(usages.usage_count), 0) as usage_count
			FROM $this->table_name AS coupons
			    LEFT JOIN $this->usage_table AS usages
					ON coupons.id = usages.coupon_id ";

		$sql .= $this->prepare_where( $args );

		$columns = $this->get_columns();
		$orderby = isset( $columns[ $args['orderby'] ] ) ? $args['orderby'] : 'id';
		$order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		$sql .= ' GROUP BY coupons.id';

		$sql .= $this->prepare_having( $args );

		$sql .= ' ORDER BY ' . $orderby . ' ' . $order;

		if ( $args['limit'] !== - 1 ) {
			$sql .= ' LIMIT ' . absint( $args['limit'] );
		}

		if ( ! empty( $args['offset'] ) ) {
			$sql .= ' OFFSET ' . absint( $args['offset'] );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get the total number of coupons.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Array of arguments.
	 *
	 * @return int
	 */
	public function get_count( $args ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var(
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT COUNT(*) FROM $this->table_name as coupons " . $this->prepare_where( $args )
		);
	}

	/**
	 * Prepare WHERE clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments.
	 *
	 * @return string
	 */
	private function prepare_where( $args ) {

		global $wpdb;

		$args = wp_parse_args(
			$args,
			[
				'status'  => 'publish',
				's'       => '',
				'is_used' => null,
				'id__in'  => [],
			]
		);

		$where = 'WHERE 1 = 1';

		if ( ! empty( $args['status'] ) ) {
			$where .= $wpdb->prepare( ' AND coupons.status = %s', $args['status'] );
		}

		if ( ! empty( $args['id__in'] ) ) {
			$where .= $this->prepare_in_clause( $args['id__in'] );
		}

		if ( ! wpforms_is_empty_string( $args['s'] ) ) {
			$where .= $wpdb->prepare(
				' AND (coupons.name LIKE %s OR coupons.code LIKE %s)',
				'%' . $wpdb->esc_like( $args['s'] ) . '%',
				'%' . $wpdb->esc_like( $args['s'] ) . '%'
			);
		}

		return $where;
	}

	/**
	 * Prepare HAVING clause for the query.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments.
	 *
	 * @return string
	 */
	private function prepare_having( $args ) {

		$having = '';

		$args = wp_parse_args(
			$args,
			[
				'is_used' => null,
			]
		);

		if ( $args['is_used'] !== null ) {
			$having .= $args['is_used']
				? ' HAVING usage_count > 0'
				: ' HAVING usage_count = 0';
		}

		return $having;
	}

	/**
	 * Create table for coupon entity.
	 *
	 * @since      1.0.0
	 * @deprecated 1.2.0
	 */
	public function create_table() {

		_deprecated_function(
			__METHOD__,
			'{WPFORMS_COUPONS_VERSION} of the Coupons addon.',
			'\WPFormsCoupons\Install::create_coupons_table'
		);
	}

	/**
	 * Get global coupons.
	 *
	 * @since 1.0.0
	 */
	public function get_global_coupons_ids() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$global_ids = $wpdb->get_col(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT id FROM $this->table_name WHERE is_global = 1 AND status = 'publish';"
		);

		return array_map( 'absint', $global_ids );
	}

	/**
	 * Get status by counts.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_status_counts() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		$status_counts = $wpdb->get_results(
			"SELECT status, COUNT(*) AS count FROM $this->table_name GROUP BY status;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		return array_map( 'absint', wp_list_pluck( $status_counts, 'count', 'status' ) );
	}

	/**
	 * Update coupons status.
	 *
	 * @since 1.0.0
	 *
	 * @param string $status     Status.
	 * @param array  $coupon_ids List of coupon IDs.
	 * @param array  $where      Conditions.
	 *
	 * @return int
	 * @noinspection SqlWithoutWhere
	 */
	public function update_status_where_in( $status, $coupon_ids, $where = [] ) {

		if ( ! in_array( $status, [ 'publish', 'trash', 'archive' ], true ) ) {
			return 0;
		}

		// Remove status from the where clause.
		$where['status'] = '';
		$where['id__in'] = $coupon_ids;

		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = $wpdb->prepare( "UPDATE $this->table_name as coupons SET status = %s", $status );

		$sql .= $this->prepare_where( $where );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->query( $sql );
	}

	/**
	 * Prepare WHERE IN clause for the coupon IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $coupon_ids List of coupon IDs.
	 *
	 * @return string
	 */
	private function prepare_in_clause( $coupon_ids ) {

		global $wpdb;

		$coupon_ids   = array_map( 'absint', (array) $coupon_ids );
		$placeholders = implode( ',', array_fill( 0, count( $coupon_ids ), '%d' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		return $wpdb->prepare( " AND coupons.id IN ($placeholders)", $coupon_ids );
	}
}
