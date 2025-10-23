<?php

namespace WPFormsCoupons\Admin\Coupons;

use WPForms\Admin\Notice;
use WPForms\Admin\Payments\Views\Overview\Helpers;
use WPForms\Admin\Payments\Views\PaymentsViewsInterface;

/**
 * Coupon Overview page class.
 *
 * @since 1.0.0
 */
class Overview implements PaymentsViewsInterface {

	/**
	 * Allowed actions.
	 *
	 * @since 1.0.0
	 */
	const ALLOWED_ACTIONS = [ 'archive', 'delete', 'restore', 'trash' ];

	/**
	 * Table object.
	 *
	 * @since 1.0.0
	 *
	 * @var Table
	 */
	private $table;

	/**
	 * Coupon ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $coupon_id;

	/**
	 * Action name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Has coupons.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	private $has_coupons;

	/**
	 * Overview constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( ! wpforms_is_admin_page( 'payments', 'coupons' ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$this->coupon_id = isset( $_GET['coupon_id'] ) ? $_GET['coupon_id'] : 0;
		$this->coupon_id = is_array( $this->coupon_id ) ? array_map( 'absint', $this->coupon_id ) : absint( $this->coupon_id );
		$this->action    = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$this->has_coupons = (bool) wpforms_coupons()->get( 'repository' )->has_coupons();
	}

	/**
	 * Get page URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_page_url() {

		return add_query_arg(
			[
				'page' => 'wpforms-payments',
				'view' => 'coupons',
			],
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Initialize class.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->process();

		if ( $this->has_coupons ) {
			$this->table = new Table();

			$this->table->prepare_items();
		}
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
				'btn_url'  => Edit::get_page_url(),
				'btn_text' => __( 'Add Coupon', 'wpforms-coupons' ),
				'icon'     => '<svg class="page-title-action-icon" xmlns="http://www.w3.org/2000/svg" width="11" height="12" fill="none"><path fill="#fff" d="M11 4.728v2.544H6.772V11.5H4.228V7.272H0V4.728h4.228V.5h2.544v4.228H11Z" opacity=".75"/></svg>',
			],
			true
		);

		Helpers::get_default_heading(
			wpforms_utm_link(
				'https://wpforms.com/docs/coupons-addon/',
				'Coupons Overview',
				'Coupon Documentation'
			)
		);
	}

	/**
	 * Process coupon form and actions.
	 *
	 * @since 1.0.0
	 */
	public function process() {

		$this->display_notices();

		if ( ! wpforms_current_user_can( [ 'create_forms', 'edit_forms' ] ) ) {
			return;
		}

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg(
				[
					'_wp_http_referer',
					'_wpnonce',
					'action',
					'coupon_id',
					'action2',
				],
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				wp_unslash( $_SERVER['REQUEST_URI'] )
			);
		}

		if ( ! $this->action || ! in_array( $this->action, self::ALLOWED_ACTIONS, true ) ) {
			return;
		}

		$this->process_bulk();
		$this->process_single();
	}

	/**
	 * Process an action for multiple coupons.
	 *
	 * @since 1.0.0
	 *
	 * @uses archive
	 * @uses trash
	 * @uses restore
	 * @uses delete
	 */
	private function process_bulk() {

		if ( empty( $_GET['_wpnonce'] ) || empty( $this->coupon_id ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'bulk-wpforms_page_wpforms-payments' ) ) {
			return;
		}

		$this->{$this->action}();
	}

	/**
	 * Process an action for a single coupon.
	 *
	 * @since 1.0.0
	 *
	 * @uses archive
	 * @uses trash
	 */
	private function process_single() {

		if ( empty( $_REQUEST['nonce'] ) ) {
			return;
		}

		$nonce = sanitize_key( $_REQUEST['nonce'] );

		if ( wp_verify_nonce( $nonce, 'wpforms-coupons-nonce::archive::' . $this->coupon_id ) ) {
			wpforms_coupons()->get( 'repository' )->change_coupons_status( $this->coupon_id, 'archive' );

			wp_safe_redirect(
				add_query_arg(
					'message',
					'archived',
					self::get_page_url()
				)
			);
		}

		if ( wp_verify_nonce( $nonce, 'wpforms-coupons-nonce::trash::' . $this->coupon_id ) ) {
			$is_trashed = wpforms_coupons()->get( 'repository' )->change_coupons_status( $this->coupon_id, 'trash' );

			wp_safe_redirect(
				add_query_arg(
					'message',
					$is_trashed ? 'trashed' : 'trash_failed',
					self::get_page_url()
				)
			);
		}
	}

	/**
	 * Move coupons to archive.
	 *
	 * @since 1.0.0
	 */
	private function archive() {

		if ( empty( $this->coupon_id ) ) {
			return;
		}

		$archiving_coupons = (array) $this->coupon_id;

		$success_archived = wpforms_coupons()->get( 'repository' )->change_coupons_status( $archiving_coupons, 'archive' );

		Notice::success(
			esc_html(
				sprintf( /* translators: %1$d - number of archived coupons. */
					_n(
						'%1$d coupon was successfully moved to the Archive.',
						'%1$d coupons were successfully moved to the Archive.',
						$success_archived,
						'wpforms-coupons'
					),
					$success_archived
				)
			)
		);
	}

	/**
	 * Move coupons to trash.
	 *
	 * @since 1.0.0
	 */
	private function trash() {

		if ( empty( $this->coupon_id ) ) {
			return;
		}

		$trashing_coupons = (array) $this->coupon_id;

		$success_trashed = wpforms_coupons()->get( 'repository' )->change_coupons_status( $trashing_coupons, 'trash' );

		if ( $success_trashed ) {
			Notice::success(
				esc_html(
					sprintf( /* translators: %1$d - number of archived coupons. */
						_n(
							'%1$d coupon was successfully moved to the Trash.',
							'%1$d coupons were successfully moved to the Trash.',
							$success_trashed,
							'wpforms-coupons'
						),
						$success_trashed
					)
				)
			);
		}

		$failed_trashed = count( $trashing_coupons ) - $success_trashed;

		if ( $failed_trashed ) {
			Notice::error(
				esc_html(
					sprintf( /* translators: %1$d - number of archived coupons. */
						_n(
							'%1$d coupon could not be moved to the Trash because it has been used.',
							'%1$d coupons could not be moved to the Trash because they have been used.',
							$failed_trashed,
							'wpforms-coupons'
						),
						$failed_trashed
					)
				)
			);
		}
	}

	/**
	 * Restore the coupons.
	 *
	 * @since 1.0.0
	 */
	private function restore() {

		if ( empty( $this->coupon_id ) ) {
			return;
		}

		$success_restored = wpforms_coupons()->get( 'repository' )->change_coupons_status( (array) $this->coupon_id, 'publish' );

		Notice::success(
			esc_html(
				sprintf( /* translators: %1$d - number of archived coupons. */
					_n(
						'%1$d coupon was successfully restored.',
						'%1$d coupons were successfully restored.',
						$success_restored,
						'wpforms-coupons'
					),
					$success_restored
				)
			)
		);
	}

	/**
	 * Delete the coupons.
	 *
	 * @since 1.0.0
	 */
	private function delete() {

		if ( empty( $this->coupon_id ) ) {
			return;
		}

		$success_deleted = wpforms_coupons()->get( 'repository' )->delete_coupons( (array) $this->coupon_id );

		Notice::success(
			esc_html(
				sprintf( /* translators: %1$d - number of archived coupons. */
					_n(
						'%1$d coupon was successfully deleted.',
						'%1$d coupons were successfully deleted.',
						$success_deleted,
						'wpforms-coupons'
					),
					$success_deleted
				)
			)
		);
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

		if ( $message === 'archived' ) {
			Notice::success( __( 'The coupon was successfully moved to the Archive.', 'wpforms-coupons' ) );
		}

		if ( $message === 'trashed' ) {
			Notice::success( __( 'The coupon was successfully moved to the Trash.', 'wpforms-coupons' ) );
		}

		if ( $message === 'trash_failed' ) {
			Notice::error( __( 'The coupon could not be moved to the Trash because it has been used.', 'wpforms-coupons' ) );
		}
	}

	/**
	 * Page content.
	 *
	 * @since 1.0.0
	 */
	public function display() {

		if ( ! $this->has_coupons ) {
			$this->display_empty_state();

			return;
		}

		$this->table->display();
	}

	/**
	 * Display coupons empty state.
	 *
	 * @since 1.0.0
	 */
	private function display_empty_state() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			WPFORMS_COUPONS_PATH . 'templates/admin/no-coupons',
			[
				'add_coupon_link' => Edit::get_page_url(),
			],
			true
		);
	}

	/**
	 * Get the Tab label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_tab_label() {

		return esc_html__( 'Coupons', 'wpforms-coupons' );
	}
}
