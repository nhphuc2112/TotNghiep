<?php

namespace WPFormsCoupons\Admin\Coupons;

use DateTime;
use DateTimeZone;
use DateTimeImmutable;
use WPForms\Admin\Notice;
use WPFormsCoupons\Coupon;
use WPForms\Admin\Payments\Views\Overview\Helpers;
use WPForms\Admin\Payments\Views\PaymentsViewsInterface;

/**
 * Coupon Add/Edit page class.
 *
 * @since 1.0.0
 */
class Edit implements PaymentsViewsInterface {

	/**
	 * Default coupon fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	const FIELDS = [
		'name'                => [
			'type'     => 'text',
			'required' => true,
		],
		'code'                => [
			'type'     => 'text',
			'required' => true,
		],
		'discount_amount'     => [
			'type'     => 'float',
			'required' => true,
		],
		'discount_type'       => [
			'type' => 'text',
		],
		'start_date_time_gmt' => [
			'type' => 'datetime',
		],
		'end_date_time_gmt'   => [
			'type' => 'datetime',
		],
		'usage_limit'         => [
			'type' => 'number',
		],
		'is_global'           => [
			'type' => 'number',
		],
		'allowed_forms'       => [
			'type' => 'array',
		],
	];

	/**
	 * Current coupon ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $coupon_id;

	/**
	 * Current coupon.
	 *
	 * @since 1.0.0
	 *
	 * @var Coupon|null
	 */
	private $coupon;

	/**
	 * Coupon data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Errors.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$this->coupon_id = ! empty( $_GET['coupon_id'] ) ? absint( $_GET['coupon_id'] ) : 0;
		$this->data      = $this->prepare_data();
		$this->coupon    = $this->coupon_id ? wpforms_coupons()->get( 'repository' )->get_coupon_by_id( $this->coupon_id ) : null;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( $this->coupon_id && ! $this->coupon ) {
			wp_safe_redirect( self::get_page_url() );
			exit;
		}

		if ( $this->coupon && $this->coupon->get_status() !== 'publish' ) {
			wp_safe_redirect( Overview::get_page_url() );
			exit;
		}

		if ( $this->coupon === null && $this->data ) {
			$this->coupon = new Coupon( $this->data );
		}

		$this->process();
		$this->hooks();
	}

	/**
	 * Get page URL.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id Coupon ID.
	 *
	 * @return string
	 */
	public static function get_page_url( $coupon_id = 0 ) {

		$args = [
			'page' => 'wpforms-payments',
			'view' => 'coupon',
		];

		if ( ! empty( $coupon_id ) ) {
			$args['coupon_id'] = $coupon_id;
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Define hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Check if the current user has the capability to view the page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function current_user_can() {

		return wpforms_current_user_can( [ 'create_forms', 'edit_forms' ] );
	}

	/**
	 * Page heading content.
	 *
	 * @since 1.0.0
	 */
	public function heading() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			WPFORMS_COUPONS_PATH . 'templates/admin/header',
			[
				'btn_url'  => Overview::get_page_url(),
				'btn_text' => __( 'Back to All Coupons', 'wpforms-coupons' ),
				'icon'     => '<svg class="page-title-action-icon" viewBox="0 0 13 12" xmlns="http://www.w3.org/2000/svg"><path d="M12.5978 5.20112V6.79888H3.1648L6.29888 9.93296L5.5 11.5L0 6L5.5 0.5L6.29888 2.06704L3.1648 5.20112H12.5978Z"></path></svg>',
			],
			true
		);

		Helpers::get_default_heading(
			wpforms_utm_link(
				'https://wpforms.com/docs/coupons-addon/',
				'Single Coupon Page',
				'Coupon Documentation'
			)
		);
	}

	/**
	 * Page content.
	 *
	 * @since 1.0.0
	 */
	public function display() {

		$title       = __( 'Add New Coupon', 'wpforms-coupons' );
		$description = __( 'Create a coupon code that can be used to receive a discount on your payment forms.', 'wpforms-coupons' );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $this->coupon_id ) ) {
			$title       = __( 'Edit Coupon', 'wpforms-coupons' );
			$description = __( 'You cannot change the coupon\'s code and amount once it\'s been created.', 'wpforms-coupons' );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$currency = wpforms_get_currencies()[ wpforms_get_currency() ]['symbol'];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			WPFORMS_COUPONS_PATH . 'templates/admin/edit',
			[
				'coupon'      => $this->coupon,
				'title'       => $title,
				'description' => $description,
				'currency'    => $currency,
				'date_format' => $this->get_date_format(),
				'time_format' => $this->get_time_format(),
				'forms'       => $this->get_all_forms(),
			],
			true
		);
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
			'wpforms-jquery-timepicker',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.timepicker/jquery.timepicker.min.css',
			[],
			'1.11.5'
		);

		wp_enqueue_style(
			'wpforms-flatpickr',
			WPFORMS_PLUGIN_URL . 'assets/lib/flatpickr/flatpickr.min.css',
			[],
			'4.6.9'
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		wp_localize_script(
			'wpforms-coupon-admin',
			'wpforms_coupon_admin',
			[
				'date_format' => $this->get_date_format(),
				'time_format' => $this->get_time_format(),
				'generating'  => esc_html__( 'Generating...', 'wpforms-coupons' ),
				'decimals'    => wpforms_get_currency_decimals( wpforms_get_currency() ),
				'leave_page'  => esc_html__( 'Leave site?', 'wpforms-coupons' ),
			]
		);

		wp_enqueue_script(
			'wpforms-flatpickr',
			WPFORMS_PLUGIN_URL . 'assets/lib/flatpickr/flatpickr.min.js',
			[ 'jquery' ],
			'4.6.9',
			true
		);

		wp_enqueue_script(
			'wpforms-jquery-timepicker',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.timepicker/jquery.timepicker.min.js',
			[ 'jquery' ],
			'1.11.5',
			true
		);

		wp_enqueue_script(
			'wpforms-jquery-datepair',
			WPFORMS_COUPONS_URL . 'assets/js/vendor/jquery.datepair.min.js',
			[ 'jquery' ],
			'0.4.16',
			true
		);
	}

	/**
	 * Get date format for Flatpickr.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_date_format() {

		$supported_formats = [ 'F j, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y' ];
		$current_format    = get_option( 'date_format' );

		/**
		 * Allow modifying date format for Flatpickr on the Edit Coupon page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $current_format WordPress date format.
		 */
		$current_format = apply_filters( 'wpforms_coupons_admin_coupons_edit_date_format', $current_format );

		if ( ! in_array( $current_format, $supported_formats, true ) ) {
			return $supported_formats[0];
		}

		return $current_format;
	}

	/**
	 * Get time format for Flatpickr.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_time_format() {

		$supported_formats = [ 'g:i a', 'g:i A', 'H:i' ];
		$current_format    = get_option( 'time_format' );

		/**
		 * Allow modifying date format for Flatpickr on the Edit Coupon page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $current_format WordPress date format.
		 */
		$current_format = apply_filters( 'wpforms_coupons_admin_coupons_edit_time_format', $current_format );

		if ( ! in_array( $current_format, $supported_formats, true ) ) {
			return $supported_formats[0];
		}

		return $current_format;
	}

	/**
	 * We need to show all forms, even if they were deleted.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_all_forms() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$form_handler = wpforms()->obj( 'form' );
		$args         = [
			'order'       => 'DESC',
			'post_status' => 'publish',
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

		$formatted_forms = [];
		$used_forms      = $this->coupon ? wpforms_coupons()->get( 'repository' )->get_coupons_used_forms( $this->coupon->get_id() ) : [];

		foreach ( $forms as $form ) {

			if ( in_array( $form->ID, $used_forms, true ) ) {
				continue;
			}

			$formatted_forms[] = [
				'id'     => $form->ID,
				'title'  => $form->post_title,
				'status' => $form->post_status,
			];
		}

		$forms = wp_list_pluck( $forms, 'post_title', 'ID' );

		foreach ( $used_forms as $form_id ) {
			if ( isset( $forms[ $form_id ] ) ) {
				array_unshift(
					$formatted_forms,
					[
						'id'     => $form_id,
						'title'  => $forms[ $form_id ],
						'status' => 'publish',
					]
				);

				unset( $formatted_forms[ $form_id ] );

				continue;
			}

			$form_title = get_the_title( $form_id );

			if ( ! $form_title ) {
				$form_title = sprintf( /* translators: %d deleted form id. */
					esc_html__( 'Deleted Form #%d', 'wpforms-coupons' ),
					absint( $form_id )
				);
			}

			array_unshift(
				$formatted_forms,
				[
					'id'     => $form_id,
					'title'  => $form_title,
					'status' => 'deleted',
				]
			);
		}

		return $formatted_forms;
	}

	/**
	 * Process coupon form and actions.
	 *
	 * @since 1.0.0
	 */
	private function process() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$this->display_notices();

		if ( empty( $_REQUEST['nonce'] ) ) {
			return;
		}

		if ( ! wpforms_current_user_can( [ 'create_forms', 'edit_forms' ] ) ) {
			return;
		}

		$nonce = sanitize_key( $_REQUEST['nonce'] );

		if ( wp_verify_nonce( $nonce, 'wpforms-coupons-nonce::trash::' . $this->coupon_id ) ) {
			$this->trash();
		}

		if ( ! wp_verify_nonce( $nonce, 'wpforms-coupons-nonce' ) ) {
			return;
		}

		$this->process_validation();

		if ( ! empty( $this->errors ) ) {
			$this->display_validate_notices();

			return;
		}

		if ( $this->coupon_id ) {
			$this->update();

			return;
		}

		$this->insert();
	}

	/**
	 * Display notices.
	 *
	 * @since 1.0.0
	 */
	private function display_notices() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['message'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$message = sanitize_key( $_GET['message'] );

		if ( $message === 'created' ) {
			Notice::success(
				wp_kses(
					__( '<strong>Success!</strong> Your coupon has been created. Don’t forget to add a Coupon field to your form.', 'wpforms-coupons' ),
					[
						'strong' => [],
					]
				)
			);
		}

		if ( $message === 'updated' ) {
			Notice::success(
				wp_kses(
					__( '<strong>Success!</strong> Your changes have been saved.', 'wpforms-coupons' ),
					[
						'strong' => [],
					]
				)
			);
		}
	}

	/**
	 * Create new coupon.
	 *
	 * @since 1.0.0
	 */
	private function insert() {

		$coupon_id = wpforms_coupons()->get( 'repository' )->add( $this->data );

		wp_safe_redirect(
			add_query_arg( 'message', 'created', self::get_page_url( $coupon_id ) )
		);
		exit;
	}

	/**
	 * Update an existed coupon.
	 *
	 * @since 1.0.0
	 */
	private function update() {

		// The data isn't editable.
		unset( $this->data['code'], $this->data['discount_amount'], $this->data['discount_type'] );

		wpforms_coupons()->get( 'repository' )->update( $this->coupon_id, $this->data );

		wp_safe_redirect(
			add_query_arg( 'message', 'updated', self::get_page_url( $this->coupon_id ) )
		);
	}

	/**
	 * Move coupon to trash.
	 *
	 * @since 1.0.0
	 */
	private function trash() {

		if ( empty( $this->coupon_id ) || empty( $this->coupon ) ) {
			return;
		}

		wp_safe_redirect( $this->coupon->get_trash_url() );
		exit;
	}

	/**
	 * Prepare data before save/edit.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function prepare_data() {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['wpforms-coupons'] ) ) {
			return [];
		}

		static $data = [];

		if ( ! empty( $data ) ) {
			return $data;
		}

		foreach ( self::FIELDS as $key => $item ) {
			$value = $this->sanitize_field( $key, $item['type'] );

			$data[ $key ] = $value;
		}

		return $data;
	}

	/**
	 * Sanitize submitted field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key  Field key.
	 * @param string $type Field type.
	 *
	 * @return float|int|string|array|null
	 */
	private function sanitize_field( $key, $type ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$coupons_post_data = $_POST['wpforms-coupons'] ?? [];
		$value             = isset( $coupons_post_data[ $key ] ) && ! wpforms_is_empty_string( $coupons_post_data[ $key ] ) ? $coupons_post_data[ $key ] : '';
		$result = [];

		if ( $type === 'float' ) {
			return abs( (float) $value );
		}

		if ( $type === 'number' ) {
			return absint( $value );
		}

		if ( $type === 'datetime' ) {
			return $this->sanitize_datetime_field( $key, $coupons_post_data );
		}

		if ( $type === 'array' ) {

			if ( ! empty( $value ) ) {
				foreach ( $value as $key_value ) {
					$result[ $key_value ] = get_the_title( $key_value );
				}
			}

			return $result;
		}

		return sanitize_text_field( wp_unslash( $value ) );
	}

	/**
	 * Sanitize datetime field.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Added $data parameter.
	 *
	 * @param string $key  Field key.
	 * @param array  $data Data array.
	 *
	 * @return string|null
	 */
	private function sanitize_datetime_field( $key, $data ) {

		$date_type = $key === 'start_date_time_gmt' ? 'start' : 'end';

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $data ) ) {
			return null;
		}

		if ( empty( $data[ $date_type . '_date' ] ) ) {
			return null;
		}

		$time = ! empty( $data[ $date_type . '_time' ] ) ? $data[ $date_type . '_time' ] : gmdate( $this->get_time_format(), strtotime( '00:00:00' ) );

		$prepare_date = DateTime::createFromFormat(
			$this->get_date_format() . ' ' . $this->get_time_format(),
			sanitize_text_field( wp_unslash( $data[ $date_type . '_date' ] . ' ' . $time ) ),
			new DateTimeZone( 'UTC' )
		);
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $prepare_date ) {
			return null;
		}

		$offset = (float) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

		$prepare_date->modify( '-' . $offset . ' seconds' );

		return $prepare_date->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Get the Tab label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_tab_label() {

		return '';
	}

	/**
	 * Display validation notices.
	 *
	 * @since 1.0.0
	 */
	private function display_validate_notices() {

		foreach ( $this->errors as $error ) {
			Notice::error( $this->get_validate_notice( $error ) );
		}
	}

	/**
	 * Get validation notice.
	 *
	 * @since 1.0.0
	 *
	 * @param string $error Error key.
	 *
	 * @return string
	 */
	private function get_validate_notice( $error ) {

		$notices = $this->get_validate_notices();

		return ! empty( $notices[ $error ] )
			? $notices[ $error ]
			: esc_html__( 'Something went wrong. Please try again.', 'wpforms-coupons' );
	}

	/**
	 * Get validation notices.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_validate_notices() {

		return [
			'name'            => esc_html__( 'The coupon name is a required field.', 'wpforms-coupons' ),
			'code'            => esc_html__( 'The coupon code is a required field.', 'wpforms-coupons' ),
			'usage_limit'     => esc_html__( 'The usage limit should be more than the usage count.', 'wpforms-coupons' ),
			'discount_amount' => esc_html__( 'The coupon amount is a required field.', 'wpforms-coupons' ),
			'coupon'          => esc_html__( 'The coupon code you entered already exists.', 'wpforms-coupons' ),
			'discount'        => esc_html__( 'The discount amount should be more than zero.', 'wpforms-coupons' ),
			'discount_type'   => esc_html__( 'You can\'t submit discount amount greater than 100 percent.', 'wpforms-coupons' ),
			'code_length'     => esc_html__( 'The coupon code must have fewer than 36 characters.', 'wpforms-coupons' ),
			'date'            => esc_html__( 'The coupon start date should be less than the end date.', 'wpforms-coupons' ),
			'date_now_start'  => esc_html__( 'The coupon start date should be greater than the current date.', 'wpforms-coupons' ),
			'date_now_end'    => esc_html__( 'The coupon end date should be greater than the current date.', 'wpforms-coupons' ),
		];
	}

	/**
	 * Process validation.
	 *
	 * @since 1.0.0
	 */
	private function process_validation() {

		if ( $this->coupon_id ) {
			$this->validate_update();

			return;
		}

		$this->validate_insert();
	}

	/**
	 * Validate update.
	 *
	 * @since 1.0.0
	 */
	private function validate_update() {

		if ( empty( $this->data['name'] ) ) {
			$this->errors[] = 'name';
		}

		$code   = $this->coupon->get_code();
		$coupon = wpforms_coupons()->get( 'repository' )->get_coupon_by_code( $code );

		if ( $coupon === null ) {
			$this->errors[] = 'coupon';
		}

		$this->process_date_fields( $this->coupon );

		$usage_limit = $this->data['usage_limit'];

		if ( empty( $usage_limit ) ) {
			$this->data['usage_limit_reached'] = 0;

			return;
		}

		$usage_count = $coupon->get_usage_count();

		if ( ! $usage_count ) {
			return;
		}

		if ( $usage_count > $usage_limit ) {
			$this->errors[] = 'usage_limit';

			return;
		}

		$this->data['usage_limit_reached'] = $usage_count === $usage_limit;
	}

	/**
	 * Validate insert.
	 *
	 * @since 1.0.0
	 */
	private function validate_insert() {

		$data = $this->data;

		$this->process_required_fields();

		if ( strlen( $data['code'] ) > 36 ) {
			$this->errors[] = 'code_length';
		}

		$coupon = wpforms_coupons()->get( 'repository' )->get_coupon_by_code( $data['code'] );

		if ( $coupon !== null ) {
			$this->errors[] = 'coupon';
		}

		if ( $data['discount_amount'] <= 0 ) {
			$this->errors[] = 'discount';
			$this->errors   = array_diff( $this->errors, [ 'discount_amount' ] );
		}

		if ( $data['discount_type'] === 'percentage' && $data['discount_amount'] > 100 ) {
			$this->errors[] = 'discount_type';
		}

		$this->process_date_fields();
	}

	/**
	 * Process required fields.
	 *
	 * @since 1.0.0
	 */
	private function process_required_fields() {

		$required_fields = array_filter(
			self::FIELDS,
			static function( $field ) {

				return ! empty( $field['required'] );
			}
		);

		$required_fields = array_keys( $required_fields );

		foreach ( $required_fields as $field ) {
			if ( empty( $this->data[ $field ] ) ) {
				$this->errors[] = $field;
			}
		}
	}

	/**
	 * Process date fields.
	 *
	 * @since 1.0.0
	 */
	public function process_date_fields() {

		if ( empty( $this->data['start_date_time_gmt'] ) || empty( $this->data['end_date_time_gmt'] ) ) {
			return;
		}

		// Case: when two dates are filled and end date is less than start date - throw error.

		$start_date_time_gmt = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $this->data['start_date_time_gmt'], new DateTimeZone( 'UTC' ) );
		$end_date_time_gmt   = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $this->data['end_date_time_gmt'], new DateTimeZone( 'UTC' ) );

		if ( $start_date_time_gmt->getTimestamp() >= $end_date_time_gmt->getTimestamp() ) {
			$this->errors[] = 'date';
		}
	}
}
