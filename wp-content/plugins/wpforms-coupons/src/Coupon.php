<?php

namespace WPFormsCoupons;

use Exception;
use DateTimeZone;
use DateTimeImmutable;
use WPFormsCoupons\Admin\Coupons\Edit;
use WPFormsCoupons\Admin\Coupons\Overview;

/**
 * Coupon Entity class.
 *
 * @since 1.0.0
 *
 * @method get_id
 * @method get_name
 * @method get_code
 * @method get_discount_amount
 * @method get_discount_type
 * @method get_usage_count
 * @method get_usage_limit
 * @method get_usage_limit_reached
 * @method get_start_date_time_gmt
 * @method get_end_date_time_gmt
 * @method get_date_created_gmt
 * @method get_status
 * @method get_is_global
 * @method get_allowed_forms
 */
class Coupon {

	/**
	 * Coupon ID in database.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Name of the coupon.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Code of the coupon.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $code;

	/**
	 * Amount of the coupon.
	 *
	 * @since 1.0.0
	 *
	 * @var float
	 */
	private $discount_amount;

	/**
	 * Type of the coupon: flat or percentage.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $discount_type;

	/**
	 * Coupon usage limit.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $usage_limit;

	/**
	 * How many times the coupon has been used.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $usage_count;

	/**
	 * Is the coupon usage limit reached.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	private $usage_limit_reached;

	/**
	 * Date and time when the coupon starts to work.
	 *
	 * @since 1.0.0
	 *
	 * @var DateTime|null
	 */
	private $start_date_time_gmt;

	/**
	 * Date and time when the coupon finishes to work.
	 *
	 * @since 1.0.0
	 *
	 * @var DateTime|null
	 */
	private $end_date_time_gmt;

	/**
	 * Status of the coupon.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $status;

	/**
	 * Is the coupon available in any form.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	private $is_global;

	/**
	 * Date and time when the coupon was created.
	 *
	 * @since 1.0.0
	 *
	 * @var DateTime
	 */
	private $date_created_gmt;

	/**
	 * List of forms in which the coupon is available.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $allowed_forms;

	/**
	 * Coupon constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $row Database row.
	 */
	public function __construct( $row ) {

		$row = wp_parse_args(
			$row,
			[
				'id'                  => 0,
				'name'                => '',
				'code'                => '',
				'discount_amount'     => 0,
				'discount_type'       => 'flat',
				'usage_limit'         => null,
				'usage_count'         => 0,
				'usage_limit_reached' => 0,
				'start_date_time_gmt' => null,
				'end_date_time_gmt'   => null,
				'date_created_gmt'    => '',
				'status'              => 'publish',
				'is_global'           => false,
				'allowed_forms'       => [],
			]
		);

		$this->id              = absint( $row['id'] );
		$this->name            = $row['name'];
		$this->code            = $row['code'];
		$this->discount_type   = $row['discount_type'] === 'percentage' ? 'percentage' : 'flat';
		$this->discount_amount = $this->discount_type === 'flat'
			? wpforms_sanitize_amount( $row['discount_amount'] )
			: round( $row['discount_amount'], 2 );

		$this->usage_limit         = $row['usage_limit'] ? absint( $row['usage_limit'] ) : null;
		$this->usage_count         = absint( $row['usage_count'] );
		$this->usage_limit_reached = $row['usage_limit_reached'] && $this->usage_count >= $this->usage_limit;
		$this->status              = $row['status'];
		$this->is_global           = (bool) $row['is_global'];
		$this->allowed_forms       = $row['allowed_forms'];

		$this->set_dates( $row );
	}

	/**
	 * Set date properties.
	 *
	 * @since 1.0.0
	 *
	 * @param @param array $row Database row.
	 */
	private function set_dates( $row ) {

		$utc_zone = new DateTimeZone( 'UTC' );

		try {
			$this->start_date_time_gmt = $row['start_date_time_gmt'] ? new DateTimeImmutable( $row['start_date_time_gmt'], $utc_zone ) : null;
			$this->end_date_time_gmt   = $row['end_date_time_gmt'] ? new DateTimeImmutable( $row['end_date_time_gmt'], $utc_zone ) : null;
			$this->date_created_gmt    = new DateTimeImmutable( $row['date_created_gmt'], $utc_zone );
		} catch ( Exception $e ) {
			$this->start_date_time_gmt = null;
			$this->end_date_time_gmt   = null;
			$this->date_created_gmt    = null;
		}
	}

	/**
	 * Magic method to get properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name      Property name.
	 * @param array  $arguments Arguments.
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {

		$property = strtolower( str_replace( 'get_', '', $name ) );

		if ( property_exists( $this, $property ) ) {
			return $this->{$property};
		}

		return $name( $arguments );
	}

	/**
	 * Get coupon payment information.
	 *
	 * @since 1.0.0
	 *
	 * @param float $subtotal Subtotal.
	 *
	 * @return string
	 */
	public function get_payment_info( $subtotal ) {

		$lines = [
			$this->get_code(),
			$this->get_formatted_flat_discount( $subtotal ),
		];

		return implode( "\n", $lines );
	}

	/**
	 * Get formatted flat discount.
	 *
	 * @since 1.0.0
	 *
	 * @param float $subtotal Subtotal.
	 *
	 * @return string
	 */
	private function get_formatted_flat_discount( $subtotal ) {

		$amount = $this->discount_amount;

		if ( $this->discount_type === 'flat' ) {
			return sprintf( '-%s', wpforms_format_amount( $amount, true ) );
		}

		$discount = $subtotal * ( $amount / 100 );

		return sprintf(
			'-%s (%s)',
			$amount . '%',
			wpforms_format_amount( $discount, true )
		);
	}

	/**
	 * Get formatted amount.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_formatted_amount() {

		return $this->discount_type === 'percentage' ?
			sprintf( '%s%%', $this->discount_amount ) :
			wpforms_format_amount( $this->discount_amount, true );
	}

	/**
	 * Determine if the coupon has already started.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function has_started() {

		if ( empty( $this->start_date_time_gmt ) ) {
			return true;
		}

		try {
			$now = new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
		} catch ( Exception $e ) {
			return false;
		}

		return $now > $this->start_date_time_gmt;
	}

	/**
	 * Determine if the coupon is expired.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_expired() {

		if ( empty( $this->end_date_time_gmt ) ) {
			return false;
		}

		try {
			$now = new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
		} catch ( Exception $e ) {
			return false;
		}

		return $now > $this->end_date_time_gmt;
	}

	/**
	 * Determine if the coupon is allowed for the form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return bool
	 */
	public function is_allowed_for_form( $form_id ) {

		if ( $this->is_global ) {
			return true;
		}

		return isset( $this->allowed_forms[ $form_id ] );
	}

	/**
	 * Get edit page.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_edit_url() {

		return Edit::get_page_url( $this->id );
	}

	/**
	 * Get the coupon archive action url.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_archive_url() {

		return $this->get_nonce_url( 'archive' );
	}

	/**
	 * Get the coupon trash action url.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_trash_url() {

		return $this->get_nonce_url( 'trash' );
	}

	/**
	 * Get the coupon restore action url.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_restore_url() {

		return $this->get_nonce_url( 'restore' );
	}

	/**
	 * Get the coupon delete action url.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_delete_url() {

		return $this->get_nonce_url( 'delete' );
	}

	/**
	 * Get the coupon action URL with a nonce.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action name.
	 *
	 * @return string
	 */
	private function get_nonce_url( $action ) {

		$nonce_action = sprintf( 'wpforms-coupons-nonce::%1$s::%2$d', $action, $this->id );

		return wp_nonce_url(
			add_query_arg(
				[
					'action'    => $action,
					'coupon_id' => $this->id,
				],
				Overview::get_page_url()
			),
			$nonce_action,
			'nonce'
		);
	}

	/**
	 * Get the coupon usage counts e.g. payments or forms submissions.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_usage_counts() {

		static $usage_counts = null;

		if ( $usage_counts !== null ) {
			return $usage_counts;
		}

		$usage_counts = wpforms_coupons()->get( 'repository' )->get_coupon_usage_counts( $this->id );

		return $usage_counts;
	}

	/**
	 * Is valid code.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return bool
	 */
	public function is_valid( $form_id ) {

		if ( $this->status !== 'publish' ) {
			return false;
		}

		if ( ! $this->has_started() ) {
			return false;
		}

		if ( $this->is_expired() ) {
			return false;
		}

		if ( $this->usage_limit_reached ) {
			return false;
		}

		return $this->is_allowed_for_form( $form_id );
	}
}
