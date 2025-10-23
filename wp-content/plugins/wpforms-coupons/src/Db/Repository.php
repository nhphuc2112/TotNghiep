<?php

namespace WPFormsCoupons\Db;

use WPFormsCoupons\Coupon;

/**
 * Repository class.
 *
 * @since 1.0.0
 */
class Repository {

	/**
	 * Coupons table.
	 *
	 * @since 1.0.0
	 *
	 * @var Coupons
	 */
	private $coupons_db;

	/**
	 * Coupons forms pivot table.
	 *
	 * @since 1.0.0
	 *
	 * @var CouponsForms
	 */
	private $coupons_forms_db;

	/**
	 * Coupons usage by form table.
	 *
	 * @since 1.0.0
	 *
	 * @var CouponsFormsUsage
	 */
	private $coupons_forms_usage_db;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Coupons           $coupons_db             Coupons table.
	 * @param CouponsForms      $coupons_forms_db       Coupons Forms table.
	 * @param CouponsFormsUsage $coupons_forms_usage_db Coupons Forms Usage table.
	 */
	public function __construct( $coupons_db, $coupons_forms_db, $coupons_forms_usage_db ) {

		$this->coupons_db             = $coupons_db;
		$this->coupons_forms_db       = $coupons_forms_db;
		$this->coupons_forms_usage_db = $coupons_forms_usage_db;
	}

	/**
	 * If required tables exist.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function tables_exist(): bool {

		_deprecated_function(
			__METHOD__,
			'{WPFORMS_COUPONS_VERSION} of the Coupons addon.',
			'\WPFormsCoupons\Install::tables_exist'
		);

		return true;
	}

	/**
	 * Create tables for coupon entity.
	 *
	 * @since      1.0.0
	 * @deprecated 1.2.0
	 */
	public function create_tables() {

		_deprecated_function(
			__METHOD__,
			'{WPFORMS_COUPONS_VERSION} of the Coupons addon.',
			'\WPFormsCoupons\Install::run'
		);
	}

	/**
	 * Get all available coupons.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	public function get_coupons( $args ) {

		$coupons = $this->coupons_db->get_coupons( $args );

		if ( empty( $coupons ) ) {
			return [];
		}

		if ( empty( $args['fields'] ) ) {
			return array_map( [ $this, 'convert_record_to_coupon' ], $coupons );
		}

		if ( $args['fields'] === 'no_forms' ) {
			return array_map( [ $this, 'convert_record_to_coupon_no_forms' ], $coupons );
		}

		if ( $args['fields'] === 'id=>name' ) {
			return wp_list_pluck( $coupons, 'name', 'id' );
		}

		return array_map( 'absint', wp_list_pluck( $coupons, 'id' ) );
	}

	/**
	 * Determine if coupons exist.
	 *
	 * @since 1.0.0
	 *
	 * @param string $status Coupon status.
	 */
	public function has_coupons( $status = '' ) {

		static $has_coupons;

		if ( $has_coupons !== null ) {
			return $has_coupons;
		}

		$coupons = $this->coupons_db->get_coupons(
			[
				'limit'  => 1,
				'status' => $status,
				'fields' => 'ids',
			]
		);

		$has_coupons = ! empty( $coupons );

		return $has_coupons;
	}

	/**
	 * Get coupon by code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code Coupon code.
	 *
	 * @return Coupon|null
	 */
	public function get_coupon_by_code( $code ) {

		$coupon = $this->coupons_db->get_by_code( $code );

		if ( ! $coupon ) {
			return null;
		}

		return $this->convert_record_to_coupon( $coupon );
	}

	/**
	 * Get coupon by id.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 *
	 * @return Coupon|null
	 */
	public function get_coupon_by_id( $coupon_id ) {

		$coupon = $this->coupons_db->get( $coupon_id );

		if ( ! $coupon ) {
			return null;
		}

		return $this->convert_record_to_coupon( $coupon );
	}

	/**
	 * Convert database record to Coupon object.
	 *
	 * @since 1.0.0
	 *
	 * @param array $coupon Coupon data.
	 *
	 * @return Coupon
	 */
	private function convert_record_to_coupon( $coupon ) {

		$coupon['allowed_forms'] = empty( $coupon['is_global'] ) ?
			$this->get_coupon_forms( $coupon['id'] ) :
			$this->get_all_forms();

		return new Coupon( $coupon );
	}

	/**
	 * Get coupon object.
	 *
	 * @since 1.3.0
	 *
	 * @param array $coupon Coupon data.
	 *
	 * @return Coupon
	 */
	private function convert_record_to_coupon_no_forms( $coupon ) {

		return new Coupon( $coupon );
	}

	/**
	 * Get and cache all forms.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_all_forms() {

		static $forms;

		if ( $forms !== null ) {
			return $forms;
		}

		$form_handler = wpforms()->obj( 'form' );
		$args         = [
			'fields' => 'all',
		];

		// @WPFormsBackCompatStart User Generated Templates since WPForms v1.8.8
		if ( defined( get_class( $form_handler ) . '::POST_TYPES' ) ) {
			$args['post_type'] = $form_handler::POST_TYPES;
		}
		// @WPFormsBackCompatEnd

		$forms = $form_handler->get( '', $args );

		if ( empty( $forms ) ) {
			return [];
		}

		$forms = wp_list_pluck( $forms, 'post_title', 'ID' );

		krsort( $forms );

		return $forms;
	}

	/**
	 * Update coupon usage count.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 * @param int $form_id   Form ID.
	 */
	public function update_coupon_usage_count( $coupon_id, $form_id ) {

		$this->coupons_forms_usage_db->update_counter( $coupon_id, $form_id );
	}

	/**
	 * Update coupon payment count.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 * @param int $form_id   Form ID.
	 */
	public function update_coupon_payment_count( $coupon_id, $form_id ) {

		$this->coupons_forms_usage_db->update_payment_counter( $coupon_id, $form_id );
	}

	/**
	 * Get coupon usage counts.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 *
	 * @return array
	 */
	public function get_coupon_usage_counts( $coupon_id ) {

		$counts = $this->coupons_forms_usage_db->get_coupon_counts( $coupon_id );

		if ( empty( $counts ) ) {
			return [];
		}

		$formatted_counts = [];

		foreach ( $counts as $row ) {
			$formatted_counts[ $row['form_id'] ] = [
				'usage_count'    => $row['usage_count'],
				'payments_count' => $row['payments_count'],
			];
		}

		return $formatted_counts;
	}

	/**
	 * Add new coupon.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Coupon data.
	 *
	 * @return int Coupon ID.
	 */
	public function add( $data ) {

		$coupon_id = $this->coupons_db->add( $data );

		if ( empty( $data['is_global'] ) && ! empty( $data['allowed_forms'] ) ) {
			$this->coupons_forms_db->add_bulk_forms( $coupon_id, $data['allowed_forms'] );
		}

		return $coupon_id;
	}

	/**
	 * Update coupon data.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $coupon_id Coupon ID.
	 * @param array $data      Coupon data.
	 */
	public function update( $coupon_id, $data ) {

		$this->coupons_forms_db->delete_all_coupon_forms( $coupon_id );

		if ( empty( $data['is_global'] ) && ! empty( $data['allowed_forms'] ) ) {
			$this->coupons_forms_db->add_bulk_forms( $coupon_id, $data['allowed_forms'] );
		}

		return $this->coupons_db->update( $coupon_id, $data );
	}

	/**
	 * Change coupons status.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $coupon_ids Coupon ID.
	 * @param string $status     Coupon status.
	 *
	 * @return int
	 */
	public function change_coupons_status( $coupon_ids, $status ) {

		// We allow changing status on publish and archive for all coupons.
		if ( $status === 'publish' || $status === 'archive' ) {
			return $this->coupons_db->update_status_where_in( $status, $coupon_ids );
		}

		// Coupons with trash status can be deleted only if they are not used.
		$args = [
			'id__in'  => $coupon_ids,
			'fields'  => 'ids',
			'is_used' => $status !== 'trash',
		];

		$coupon_ids = $this->get_coupons( $args );

		if ( empty( $coupon_ids ) ) {
			return 0;
		}

		return $this->coupons_db->update_status_where_in( $status, $coupon_ids, $args );
	}

	/**
	 * Delete coupons.
	 *
	 * @since 1.0.0
	 *
	 * @param array $coupon_ids Coupon ID.
	 *
	 * @return int
	 */
	public function delete_coupons( $coupon_ids ) {

		// Double-check if deleting coupons has trash status and not used.
		$coupon_ids = $this->get_coupons(
			[
				'fields'  => 'ids',
				'status'  => 'trash',
				'is_used' => false,
				'id__in'  => $coupon_ids,
			]
		);

		if ( empty( $coupon_ids ) ) {
			return 0;
		}

		$this->coupons_forms_db->delete_where_in( 'coupon_id', $coupon_ids );

		return (int) $this->coupons_db->delete_where_in( 'id', $coupon_ids );
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
	private function get_coupon_forms( $coupon_id ) {

		$coupon_form_ids = $this->coupons_forms_db->get_coupon_forms( $coupon_id );
		$all_forms       = $this->get_all_forms();

		$allowed_forms = [];

		foreach ( $coupon_form_ids as $form_id ) {

			if ( ! isset( $all_forms[ $form_id ] ) ) {
				continue;
			}

			$allowed_forms[ $form_id ] = $all_forms[ $form_id ];
		}

		krsort( $allowed_forms, SORT_NUMERIC );

		return $allowed_forms;
	}

	/**
	 * Get coupon used forms.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 *
	 * @return array
	 */
	public function get_coupons_used_forms( $coupon_id ) {

		return array_map( 'absint', $this->coupons_forms_usage_db->get_coupon_forms( $coupon_id ) );
	}

	/**
	 * Get available coupons by form id.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return array
	 */
	public function get_form_coupons( $form_id ) {

		$form_coupons = array_unique(
			array_merge(
				$this->coupons_db->get_global_coupons_ids(),
				$this->coupons_forms_db->get_form_coupons( $form_id )
			)
		);

		rsort( $form_coupons, SORT_NUMERIC );

		return array_reverse( $form_coupons );
	}

	/**
	 * Set allowed coupons for form.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $form_id    Form ID.
	 * @param array $coupon_ids Array of coupon ids.
	 */
	public function set_allowed_coupons( $form_id, $coupon_ids ) {

		$globally_allowed_coupons = $this->coupons_db->get_global_coupons_ids();
		$all_forms                = $this->get_all_forms();
		$all_forms_ids            = array_keys( $all_forms );
		$all_forms_except_current = array_diff( $all_forms_ids, [ $form_id ] );

		// Disabled global coupons and remove them from the list.
		foreach ( $globally_allowed_coupons as $global_coupon ) {
			if ( ! in_array( $global_coupon, $coupon_ids, true ) ) {
				$this->update(
					$global_coupon,
					[
						'is_global'     => false,
						'allowed_forms' => $all_forms_except_current,
					]
				);

				$coupon_ids = array_diff( $coupon_ids, [ $global_coupon ] );
			}
		}

		$prev_form_allowed_coupons = $this->coupons_forms_db->get_form_coupons( $form_id );
		$deleted_coupons           = [];

		foreach ( $prev_form_allowed_coupons as $coupon_id ) {
			// The record should be deleted.
			if ( ! in_array( $coupon_id, $coupon_ids, true ) ) {
				$deleted_coupons[] = $coupon_id;
				$coupon_ids        = array_diff( $coupon_ids, [ $coupon_id ] );
			}

			// The record is already exist.
			if ( in_array( $coupon_id, $coupon_ids, true ) ) {
				$coupon_ids = array_diff( $coupon_ids, [ $coupon_id ] );
			}
		}

		if ( ! empty( $deleted_coupons ) ) {
			$this->coupons_forms_db->delete_bulk_coupons( $form_id, $deleted_coupons );
		}

		if ( ! empty( $coupon_ids ) ) {
			$this->coupons_forms_db->add_bulk_coupons( $form_id, $coupon_ids );
		}
	}

	/**
	 * Get coupons count with query args.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query args.
	 *
	 * @return int
	 */
	public function get_coupons_count( $args ) {

		return $this->coupons_db->get_count( $args );
	}

	/**
	 * Get coupon counts by status.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_coupons_counts() {

		return $this->coupons_db->get_status_counts();
	}
}
