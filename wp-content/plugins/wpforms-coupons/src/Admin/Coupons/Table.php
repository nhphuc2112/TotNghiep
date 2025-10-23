<?php

namespace WPFormsCoupons\Admin\Coupons;

// Exit if accessed directly.
use WP_List_Table;
use WPFormsCoupons\Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Coupons Overview Table class.
 *
 * @since 1.0.0
 */
class Table extends WP_List_Table {

	/**
	 * Current page number.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $page;

	/**
	 * Order by parameter ASC or DESC.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $order;

	/**
	 * Order by parameter.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $orderby;

	/**
	 * Search term.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $search;

	/**
	 * Active status.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $status;

	/**
	 * Items per page.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $per_page;

	/**
	 * Total counts.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $counts;

	/**
	 * List of statuses.
	 *
	 * @since 1.0.0
	 */
	const STATUSES = [ 'archive', 'publish', 'trash' ];

	/**
	 * Table constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct();

		$this->page = $this->get_pagenum();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$this->order   = isset( $_GET['order'] ) && strtoupper( sanitize_key( $_GET['order'] ) ) === 'ASC' ? 'ASC' : 'DESC';
		$this->orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'id';
		$this->search  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$this->status  = isset( $_GET['status'] ) && in_array( $_GET['status'], self::STATUSES, true ) ? sanitize_key( $_GET['status'] ) : 'publish';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$this->per_page = $this->get_items_per_page( 'wpforms_coupons_per_page' );
		$this->counts   = wp_parse_args(
			wpforms_coupons()->get( 'repository' )->get_coupons_counts(),
			[
				'total'   => 0,
				'publish' => 0,
				'trash'   => 0,
				'archive' => 0,
			]
		);

		$this->counts['total'] = empty( $this->search )
			? $this->counts[ $this->status ]
			: wpforms_coupons()->get( 'repository' )->get_coupons_count(
				[
					's'      => $this->search,
					'status' => $this->status,
					'count'  => true,
				]
			);
	}

	/**
	 * Remove the pagination links from the top navigation if table is empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string $which Top or bottom.
	 */
	public function display_tablenav( $which ) {

		// Completely hide top menu if table is empty.
		if ( $which === 'top' && ! $this->has_items() ) {
			return;
		}

		parent::display_tablenav( $which );
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {

		$this->items = wpforms_coupons()->get( 'repository' )->get_coupons(
			[
				'limit'   => $this->per_page,
				'offset'  => $this->per_page * ( $this->page - 1 ),
				'order'   => $this->order,
				'orderby' => $this->orderby,
				's'       => $this->search,
				'status'  => $this->status,
			]
		);

		// Finalize pagination.
		$this->set_pagination_args(
			[
				'total_items' => $this->counts['total'],
				'total_pages' => ceil( $this->counts['total'] / $this->per_page ),
				'per_page'    => $this->per_page,
			]
		);
	}

	/**
	 * Gets a list of columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {

		return [
			'cb'                  => '<input type="checkbox" />',
			'name'                => esc_html__( 'Name', 'wpforms-coupons' ),
			'code'                => esc_html__( 'Code', 'wpforms-coupons' ),
			'discount_amount'     => esc_html__( 'Amount', 'wpforms-coupons' ),
			'usage_limit'         => esc_html__( 'Usage / Limit', 'wpforms-coupons' ),
			'allowed_forms'       => esc_html__( 'Forms', 'wpforms-coupons' ),
			'start_date_time_gmt' => esc_html__( 'Start Date', 'wpforms-coupons' ),
			'end_date_time_gmt'   => esc_html__( 'End Date', 'wpforms-coupons' ),
		];
	}

	/**
	 * Gets a list of sortable columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {

		return [
			'name'                => [ 'name', false ],
			'code'                => [ 'code', false ],
			'discount_amount'     => [ 'discount_amount', false ],
			'start_date_time_gmt' => [ 'start_date_time_gmt', false ],
			'end_date_time_gmt'   => [ 'end_date_time_gmt', false ],
		];
	}

	/**
	 * Define the checkbox column.
	 *
	 * @since 1.0.0
	 *
	 * @param Coupon $item The current item.
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="coupon_id[]" value="%d" />',
			$item->get_id()
		);
	}

	/**
	 * Name column.
	 *
	 * @since 1.0.0
	 *
	 * @param Coupon $coupon Current coupon.
	 *
	 * @return string
	 */
	protected function column_name( Coupon $coupon ) {

		if ( $this->status !== 'publish' ) {
			return sprintf( '<span><strong>%1$s</strong></span>', esc_html( $coupon->get_name() ) );
		}

		return sprintf(
			'<a href="%1$s" title="%2$s"><strong>%3$s</strong></a>',
			esc_url( $coupon->get_edit_url() ),
			esc_attr__( 'Edit This Coupon', 'wpforms-coupons' ),
			esc_html( $coupon->get_name() )
		);
	}

	/**
	 * Coupon code column.
	 *
	 * @since 1.0.0
	 *
	 * @param Coupon $coupon Current coupon.
	 *
	 * @return string
	 */
	protected function column_code( Coupon $coupon ) {

		return $coupon->get_code();
	}

	/**
	 * Discount amount column.
	 *
	 * @since 1.0.0
	 *
	 * @param Coupon $coupon Current coupon.
	 *
	 * @return string
	 */
	protected function column_discount_amount( Coupon $coupon ) {

		return $coupon->get_formatted_amount();
	}

	/**
	 * Usage limit column.
	 *
	 * @since 1.0.0
	 *
	 * @param Coupon $coupon Current coupon.
	 *
	 * @return string
	 */
	protected function column_usage_limit( Coupon $coupon ) {

		return sprintf(
			'%s / %s',
			$coupon->get_usage_count(),
			$coupon->get_usage_limit() === null ? esc_html__( 'Unlimited', 'wpforms-coupons' ) : $coupon->get_usage_limit()
		);
	}

	/**
	 * Allowed forms column.
	 *
	 * @since 1.0.0
	 *
	 * @param Coupon $coupon Current coupon.
	 *
	 * @return string
	 */
	protected function column_allowed_forms( Coupon $coupon ) {

		$allowed_forms = $coupon->get_allowed_forms();

		if ( empty( $allowed_forms ) ) {
			return esc_html__( 'N/A', 'wpforms-coupons' );
		}

		$allowed_forms_number = count( $allowed_forms );

		if ( $allowed_forms_number === 1 ) {

			$form_id = key( $allowed_forms );

			return $this->get_edit_form_link( $form_id, $allowed_forms[ $form_id ] );
		}

		$has_all_forms_link = false;
		$tooltip            = '';

		if ( $allowed_forms_number > 5 ) {
			$allowed_forms      = array_slice( $allowed_forms, 0, 5, true );
			$has_all_forms_link = true;
		}

		$first_form_id    = $this->wpforms_coupon_array_key_first( $allowed_forms );
		$first_form_title = $allowed_forms[ $first_form_id ];

		unset( $allowed_forms[ $first_form_id ] );

		foreach ( $allowed_forms as $form_id => $form_name ) {
			$tooltip .= $this->get_edit_form_link( $form_id, $form_name ) . '<br>';
		}

		if ( $has_all_forms_link ) {
			$tooltip .= sprintf(
				'<a href="%1$s" title="%2$s">%2$s</a>',
				esc_url( $coupon->get_edit_url() . '#wpforms-coupons-allowed-table' ),
				esc_attr__( 'View All Forms', 'wpforms-coupons' )
			);
		}

		return sprintf(
			'<a href="%1$s" title="%2$s">%3$s</a> <span class="wpforms-coupons-tooltip" title="%4$s">(%5$s)</span>',
			esc_url(
				add_query_arg(
					[
						'page'    => 'wpforms-builder',
						'view'    => 'fields',
						'form_id' => absint( $first_form_id ),
					],
					admin_url( 'admin.php' )
				)
			),
			esc_attr__( 'Edit this form', 'wpforms-coupons' ),
			esc_html( $first_form_title ),
			esc_html( $tooltip ),
			sprintf( /* translators: %d - number of the coupon forms. */
				__( '+%d more', 'wpforms-coupons' ),
				absint( $allowed_forms_number - 1 )
			)
		);
	}

	/**
	 * Get edit form link.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $form_id   Form ID.
	 * @param string $form_name Form name.
	 *
	 * @return string
	 */
	private function get_edit_form_link( $form_id, $form_name ) {

		return sprintf(
			'<a href="%1$s" title="%2$s">%3$s</a>',
			esc_url(
				add_query_arg(
					[
						'page'    => 'wpforms-builder',
						'view'    => 'fields',
						'form_id' => absint( $form_id ),
					],
					admin_url( 'admin.php' )
				)
			),
			esc_attr__( 'Edit this form', 'wpforms-coupons' ),
			esc_html( $form_name )
		);
	}

	/**
	 * Start date column.
	 *
	 * @since 1.0.0
	 *
	 * @param Coupon $coupon Current coupon.
	 *
	 * @return string
	 */
	protected function column_start_date_time_gmt( Coupon $coupon ) {

		$date_time = $coupon->get_start_date_time_gmt();

		if ( $date_time === null ) {
			return sprintf(
				'<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
				esc_html( __( 'No start date set', 'wpforms-coupons' ) )
			);
		}

		return wpforms_date_format( $date_time->getTimestamp(), '', true );
	}

	/**
	 * End date column.
	 *
	 * @since 1.0.0
	 *
	 * @param Coupon $coupon Current coupon.
	 *
	 * @return string
	 */
	protected function column_end_date_time_gmt( Coupon $coupon ) {

		$date_time = $coupon->get_end_date_time_gmt();

		if ( $date_time === null ) {
			return sprintf(
				'<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
				esc_html( __( 'No end date set', 'wpforms-coupons' ) )
			);
		}

		return wpforms_date_format( $date_time->getTimestamp(), '', true );
	}

	/**
	 * Gets the list of views available on this table.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_views() {

		$views = [
			'publish' => [
				'label' => __( 'Published', 'wpforms-coupons' ),
				'count' => $this->counts['publish'],
			],
			'archive' => [
				'label' => __( 'Archived', 'wpforms-coupons' ),
				'count' => $this->counts['archive'],
			],
			'trash'   => [
				'label' => __( 'Trash', 'wpforms-coupons' ),
				'count' => $this->counts['trash'],
			],
		];

		foreach ( $views as $status => $settings ) {
			$views[ $status ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( add_query_arg( 'status', $status, Overview::get_page_url() ) ),
				$status === $this->status ? ' class="current"' : '',
				esc_html( $settings['label'] ),
				(int) $settings['count']
			);
		}

		return $views;
	}

	/**
	 * Get bulk actions to be displayed in bulk action dropdown.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {

		if ( $this->status === 'publish' ) {
			return [
				'archive' => esc_html__( 'Move to Archive', 'wpforms-coupons' ),
				'trash'   => esc_html__( 'Move to Trash', 'wpforms-coupons' ),
			];
		}

		$actions = [
			'restore' => esc_html__( 'Restore', 'wpforms-coupons' ),
		];

		if ( $this->status === 'archive' ) {
			return $actions;
		}

		$actions['delete'] = esc_html__( 'Delete Permanently', 'wpforms-coupons' );

		return $actions;
	}

	/**
	 * Prepare the items and display the table.
	 *
	 * @since 1.0.0
	 */
	public function display() {
		?>
		<form id="wpforms-coupons-overview-table" method="GET"
			  action="<?php echo esc_url( Overview::get_page_url() ); ?>">
			<?php
			$this->show_reset_filter();
			$this->views();
			echo '<input type="hidden" name="page" value="wpforms-payments">';
			echo '<input type="hidden" name="view" value="coupons">';
			echo '<input type="hidden" name="status" value="' . esc_attr( $this->status ) . '">';
			$this->search_box( esc_html__( 'Search Coupons', 'wpforms-coupons' ), 'wpforms-coupons-search-input' );
			parent::display();
			?>
		</form>
		<?php
	}

	/**
	 * Gets a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_table_classes() {

		global $mode;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$mode       = get_user_setting( 'posts_list_mode', 'list' );
		$mode_class = esc_attr( 'table-view-' . $mode );
		$classes    = [
			'widefat',
			'striped',
			'wpforms-table-list',
			$mode_class,
		];

		// For styling purposes, we'll add a dedicated class name for determining the number of visible columns.
		// The ideal threshold for applying responsive styling is set at "5" columns based on the need for "Tablet" view.
		$columns_class = $this->get_column_count() > 5 ? 'many' : 'few';

		return array_merge( $classes, [ "has-{$columns_class}-columns" ] );
	}


	/**
	 * Message to be displayed when there are no items.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {

		esc_html_e( 'No coupons found.', 'wpforms-coupons' );
	}

	/**
	 * Show reset filter box.
	 *
	 * @since 1.0.0
	 */
	private function show_reset_filter() {

		if ( ! $this->search ) {
			return;
		}

		?>
		<div id="wpforms-reset-filter" class="wpforms-reset-filter">
			<?php
			printf(
				wp_kses( /* translators: %d - number of payments found. */
					_n(
						'Found <strong>%d coupon</strong> containing',
						'Found <strong>%d coupons</strong> containing',
						$this->counts['total'],
						'wpforms-coupons'
					),
					[
						'strong' => [],
					]
				),
				(int) $this->counts['total']
			);

			printf(
				' <em>"%s"</em> <a href="%s" class="reset fa fa-times-circle" title="%s"></a>',
				esc_html( $this->search ),
				esc_url( Overview::get_page_url() ),
				esc_attr__( 'Reset search', 'wpforms-coupons' )
			);
			?>
		</div>
		<?php
	}

	/**
	 * Get first array key.
	 *
	 * @since 1.0.0
	 *
	 * @param array $array Array.
	 *
	 * @return int|string|null
	 */
	private function wpforms_coupon_array_key_first( $array ) {

		foreach ( $array as $key => $unused ) {
			return $key;
		}

		return null;
	}
}
