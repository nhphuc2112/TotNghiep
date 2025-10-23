<?php
/**
 * Admin class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Brands\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBR_Admin' ) ) {
	/**
	 * YITH_WCBR_Admin class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCBR_Admin {
		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCBR_Admin
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin Panel.
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		private $panel;

		/**
		 * Docs url
		 *
		 * @var string Official documentation url
		 * @since 1.0.0
		 */
		public $doc_url = 'https://yithemes.com/docs-plugins/yith-woocommerce-brands-add-on/';

		/**
		 * Premium landing url
		 *
		 * @var string Premium landing url
		 * @since 1.0.0
		 */
		public $premium_landing_url = 'https://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCBR_Admin
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor method
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			// register plugin panel.
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

			// register plugin links & meta row.
			add_filter( 'plugin_action_links_' . YITH_WCBR_INIT, array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'add_plugin_meta' ), 10, 5 );

			// enqueue needed scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

			// register taxonomy custom fields.
			add_action( 'init', array( $this, 'init_brand_taxonomy_fields' ), 15 );
			add_action( 'created_term', array( $this, 'save_brand_taxonomy_fields' ), 10, 3 );
			add_action( 'edit_term', array( $this, 'save_brand_taxonomy_fields' ), 10, 3 );

			// add taxonomy columns.
			add_action( 'init', array( $this, 'init_brand_taxonomy_columns' ), 15 );

			// handle blank state from option table.
			add_action( 'after-' . YITH_WCBR::$default_taxonomy . '-table', array( $this, 'maybe_render_blank_state' ) );

			add_filter( 'tag_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );

			add_filter( 'term_updated_messages', array( $this, 'updated_term_messages' ) );

			add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_ids' ), 99, 1 );
		}

		/**
		 * Register panel
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_panel() {
			/**
			 * APPLY_FILTERS: yith_wcbr_available_admin_tabs
			 *
			 * Filter the available tabs in the plugin panel.
			 *
			 * @param array $tabs Admin tabs
			 *
			 * @return array
			 */
			$admin_tabs = apply_filters(
				'yith_wcbr_available_admin_tabs',
				array(
					'settings' => __( 'General Options', 'yith-woocommerce-brands-add-on' ),
					'brands'   => __( 'Brands', 'yith-woocommerce-brands-add-on' ),
				)
			);

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => 'YITH WooCommerce Brands Add-On',
				'menu_title'       => 'Brands Add-On',
				/**
				 * APPLY_FILTERS: yith_wcbr_panel_capability
				 *
				 * Filter the capability used to access the plugin panel.
				 *
				 * @param string $capability Capability
				 *
				 * @return string
				 */
				'capability'       => apply_filters( 'yith_wcbr_panel_capability', 'manage_options' ),
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'class'            => yith_set_wrapper_class(),
				'page'             => 'yith_wcbr_panel',
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_WCBR_DIR . 'plugin-options',
				'plugin_slug'      => YITH_WCBR_SLUG,
				'is_free'          => defined( 'YITH_WCBR_FREE_INIT' ),
				'is_premium'       => defined( 'YITH_WCBR_PREMIUM_INIT' ),
				'help_tab'         => array(),
			);

			if ( ! defined( 'YITH_WCBR_PREMIUM_INIT' ) ) {
				$args['premium_tab'] = array(
					'landing_page_url'          => $this->get_premium_landing_uri(),
					'premium_features'          => array(
						__( 'Set a default image to apply to brands without a logo', 'yith-woocommerce-brands-add-on' ),
						__( 'Upload a <b>banner image</b> to customize the brand archive page', 'yith-woocommerce-brands-add-on' ),
						__( 'Set up other product taxonomies available as "brands" (i.e. categories, tags, and attributes)', 'yith-woocommerce-brands-add-on' ),
						__( 'Show brands also on the Shop page and <b>customize the size and position</b>', 'yith-woocommerce-brands-add-on' ),
						__( '<b>5 widgets</b> to show a list of brands, brand logos, sliders, etc. in a widgetized area', 'yith-woocommerce-brands-add-on' ),
						__( '<b>9 shortcodes</b> to highlight brands in different ways', 'yith-woocommerce-brands-add-on' ),
						__( 'Integration with YITH WooCommerce Ajax Product Filter to <b>show a "Filter by brand" on the Shop page</b>', 'yith-woocommerce-brands-add-on' ),
						__( 'Integration with YITH WooCommerce Dynamic Pricing and Discounts to <b>create offers, discount rules and promotions only for products of specific brands</b>', 'yith-woocommerce-brands-add-on' ),
						'<b>' . __( 'Regular updates, translations, and premium support', 'yith-woocommerce-brands-add-on' ) . '</b>',
					),
					'main_image_url'            => YITH_WCBR_ASSETS_URL . '/images/get-premium-brands.png',
					'show_free_vs_premium_link' => true,
				);
			}

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_WCBR_DIR . 'plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Add plugin action links
		 *
		 * @param mixed $links Plugins links array.
		 *
		 * @return array Filtered link array
		 * @since 1.0.0
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, 'yith_wcbr_panel', defined( 'YITH_WCBR_PREMIUM_INIT' ), YITH_WCBR_SLUG );

			return $links;
		}

		/**
		 * Adds plugin row meta
		 *
		 * @param array    $new_row_meta_args  New arguments.
		 * @param string[] $plugin_meta        An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
		 * @param string   $plugin_file        Path to the plugin file relative to the plugins directory.
		 * @param array    $plugin_data        An array of plugin data.
		 * @param string   $status             Status filter currently applied to the plugin list.
		 * @param string   $init_file          Constant with plugin_file.
		 *
		 * @return array Filtered array of plugin meta
		 * @since 1.0.0
		 */
		public function add_plugin_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCBR_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YITH_WCBR_SLUG;
			}

			if ( defined( 'YITH_WCBR_PREMIUM_INIT' ) ) {
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Get the premium landing uri
		 *
		 * @return  string The premium landing link
		 * @since   1.0.0
		 */
		public function get_premium_landing_uri() {
			return $this->premium_landing_url;
		}

		/**
		 * Enqueue plugin admin styles when required
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue() {
			// enqueue admin scripts.
			$screen = get_current_screen();
			$path   = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'unminified/' : '';
			$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';

			if ( 'edit-' . YITH_WCBR::$brands_taxonomy === $screen->id || 'yith-plugins_page_yith_wcbr_panel' === $screen->id ) {
				wp_enqueue_media();
				wp_enqueue_script( 'yith-wcbr-admin', YITH_WCBR_URL . 'assets/js/admin/' . $path . 'yith-wcbr' . $suffix . '.js', array( 'jquery' ), false, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
				wp_enqueue_style( 'yith-wcbr-admin', YITH_WCBR_URL . 'assets/css/admin/yith-wcbr.css', array(), YITH_WCBR_VERSION );

				wp_localize_script(
					'yith-wcbr-admin',
					'yith_wcbr',
					array(
						'labels'                 => array(
							'upload_file_frame_title'  => __( 'Choose an image', 'yith-woocommerce-brands-add-on' ),
							'upload_file_frame_button' => __( 'Use image', 'yith-woocommerce-brands-add-on' ),
						),
						'ajax_url'               => admin_url( 'admin-ajax.php' ),
						'wc_placeholder_img_src' => wc_placeholder_img_src(),
					)
				);
			}
		}

		/**
		 * Init custom fields for brand taxonomy
		 *
		 * @return void
		 * @since 1.1.2
		 */
		public function init_brand_taxonomy_fields() {
			add_action( YITH_WCBR::$brands_taxonomy . '_add_form_fields', array( $this, 'add_brand_taxonomy_fields' ), 15, 1 );
			add_action( YITH_WCBR::$brands_taxonomy . '_edit_form_fields', array( $this, 'edit_brand_taxonomy_fields' ), 15, 1 );
		}

		/**
		 * Prints custom term fields on "Add Brand" page
		 *
		 * @param string $p_term Current taxonomy id.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_brand_taxonomy_fields( $p_term ) {
			yith_wcbr_get_view( 'add-brand-taxonomy-form.php' );
		}

		/**
		 * Prints custom term fields on "Edit brand" page
		 *
		 * @param string $p_term Current taxonomy id.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function edit_brand_taxonomy_fields( $p_term ) {
			$thumbnail_id = absint( yith_wcbr_get_term_meta( $p_term->term_id, 'thumbnail_id', true ) );
			$image        = $thumbnail_id ? wp_get_attachment_thumb_url( $thumbnail_id ) : wc_placeholder_img_src();

			$args = array(
				'thumbnail_id' => $thumbnail_id,
				'image'        => $image,
			);

			yith_wcbr_get_view( 'edit-brand-taxonomy-form.php', $args );
		}

		/**
		 * Save custom term fields
		 *
		 * @param int        $term_id Currently saved term id.
		 * @param int|string $tt_id Term Taxonomy id.
		 * @param string     $taxonomy Current taxonomy slug.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function save_brand_taxonomy_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['product_brand_thumbnail_id'] ) && YITH_WCBR::$brands_taxonomy === $taxonomy ) {
				yith_wcbr_update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['product_brand_thumbnail_id'] ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Add custom columns to brand taxonomy table
		 *
		 * @return void
		 * @since 1.2.2
		 */
		public function init_brand_taxonomy_columns() {
			add_filter( 'manage_edit-' . YITH_WCBR::$brands_taxonomy . '_columns', array( $this, 'brand_taxonomy_columns' ), 15 );
			add_filter( 'manage_' . YITH_WCBR::$brands_taxonomy . '_custom_column', array( $this, 'brand_taxonomy_column' ), 15, 3 );
			add_filter( 'manage_edit-' . YITH_WCBR::$brands_taxonomy . '_sortable_columns', array( $this, 'add_sortable_columns' ) );
		}

		/**
		 * Register custom columns for "Add Brand" taxonomy view
		 *
		 * @param mixed $columns Old columns.
		 *
		 * @return mixed Filtered array of columns
		 * @since 1.0.0
		 */
		public function brand_taxonomy_columns( $columns ) {
			$new_columns = array();

			if ( isset( $columns['cb'] ) ) {
				$new_columns['cb'] = $columns['cb'];
				unset( $columns['cb'] );
			}

			if ( isset( $columns['posts'] ) ) {
				unset( $columns['posts'] );
			}

			$new_columns['thumb'] = __( 'Image', 'yith-woocommerce-brands-add-on' );

			$columns = array_merge( $new_columns, $columns );

			$columns['count']   = _x( 'Count', 'Column heading in the brands table', 'yith-woocommerce-brands-add-on' );
			$columns['actions'] = '';

			/**
			 * APPLY_FILTERS: yith_wcbr_brand_taxonomy_columns
			 *
			 * Filter the columns for the Brands table.
			 *
			 * @param array $columns Columns
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcbr_brand_taxonomy_columns', $columns );
		}

		/**
		 * Prints custom columns for "Add Brand" taxonomy view
		 *
		 * @param string $content mixed Custom column output.
		 * @param string $column  string Id of current column.
		 * @param int    $id      int id of term being printed.
		 *
		 * @return string Output for the columns
		 */
		public function brand_taxonomy_column( $content, $column, $id ) {
			switch ( $column ) {
				case 'thumb':
					$thumbnail_id = yith_wcbr_get_term_meta( $id, 'thumbnail_id', true );

					if ( $thumbnail_id ) {
						$image = wp_get_attachment_thumb_url( $thumbnail_id );
					} else {
						$image = wc_placeholder_img_src();
					}

					$image = str_replace( ' ', '%20', $image );

					$content = '<img src="' . esc_url( $image ) . '" alt="' . __( 'Thumbnail', 'yith-woocommerce-brands-add-on' ) . '" class="wp-post-image" height="48" width="48" />';

					break;

				case 'count':
					$term     = get_term( $id );
					$taxonomy = get_taxonomy( $term->taxonomy );
					$count    = number_format_i18n( $term->count );

					if ( $taxonomy->query_var ) {
						$args = array( $taxonomy->query_var => $term->slug );
					} else {
						$args = array(
							'taxonomy' => $taxonomy->name,
							'term'     => $term->slug,
						);
					}

					$args['post_type'] = 'product';

					$count_url = add_query_arg( $args, 'edit.php' );

					$content = '<a href="' . $count_url . '">' . $count . '</a>';

					break;

				case 'actions':
					$actions = yith_plugin_fw_get_default_term_actions( $id );

					foreach ( $actions as $action ) {
						$content .= yith_plugin_fw_get_component( $action, false );
					}

					break;
			}

			/**
			 * APPLY_FILTERS: yith_wcbr_brand_taxonomy_column
			 *
			 * Filter the content for the custom columns in the Brands table.
			 *
			 * @param string $content Custom column content
			 * @param string $column  Custom column name
			 * @param int    $id      Term ID
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcbr_brand_taxonomy_column', $content, $column, $id );
		}

		/**
		 * Manage sortable columns in brands table
		 *
		 * @param array $sortable_columns Sortable columns.
		 *
		 * @return array
		 */
		public function add_sortable_columns( $sortable_columns ) {
			$sortable_columns['count'] = 'count';

			return $sortable_columns;
		}

		/**
		 * Render blank state.
		 *
		 * @since 2.0.0
		 */
		protected function render_blank_state() {
			$component = array(
				'type'     => 'list-table-blank-state',
				'icon_url' => YITH_WCBR_URL . 'assets/images/empty-brand.svg',
				'message'  => __( 'You have no Brands yet!', 'yith-woocommerce-brands-add-on' ),
			);

			yith_plugin_fw_get_component( $component, true );
		}

		/**
		 * Maybe render blank state
		 *
		 * @since 2.0.0
		 */
		public function maybe_render_blank_state() {
			$count = absint( wp_count_terms( YITH_WCBR::$default_taxonomy ) );

			if ( 0 < $count ) {
				return;
			}

			$this->render_blank_state();

			echo '<style type="text/css" id="yith-wcbr-blank-state-style">#posts-filter { display: none; } form.search-form {visibility: hidden;}</style>';
		}

		/**
		 * Delete action links in the Brands table
		 *
		 * @param  mixed $actions Actions.
		 * @param  mixed $tag     Object to check.
		 * @return $actions
		 */
		public function remove_row_actions( $actions, $tag ) {
			if ( YITH_WCBR::$default_taxonomy === $tag->taxonomy ) {
				$actions = array();
			}

			return $actions;
		}

		/**
		 * Change Brands update messages.
		 *
		 * @param array $messages Messages.
		 *
		 * @return array
		 */
		public function updated_term_messages( $messages ) {
			$messages[ YITH_WCBR::$default_taxonomy ] = array(
				0 => '',
				1 => __( 'Brand added.', 'yith-woocommerce-brands-add-on' ),
				2 => __( 'Brand deleted.', 'yith-woocommerce-brands-add-on' ),
				3 => __( 'Brand updated.', 'yith-woocommerce-brands-add-on' ),
				4 => __( 'Brand not added.', 'yith-woocommerce-brands-add-on' ),
				5 => __( 'Brand not updated.', 'yith-woocommerce-brands-add-on' ),
				6 => __( 'Brands deleted.', 'yith-woocommerce-brands-add-on' ),
			);

			return $messages;
		}

		/**
		 * Add custom screen ids to standard WC
		 *
		 * @access public
		 *
		 * @param array $screen_ids Screen IDs.
		 *
		 * @return array
		 */
		public function add_screen_ids( $screen_ids ) {
			if ( 'yith_product_brand' === YITH_WCBR::$brands_taxonomy ) {
				$screen_ids[] = 'edit-' . YITH_WCBR::$brands_taxonomy;
			}

			return $screen_ids;
		}
	}
}

/**
 * Unique access to instance of YITH_WCBR_Admin class
 *
 * @return \YITH_WCBR_Admin
 * @since 1.0.0
 */
function YITH_WCBR_Admin() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	if ( class_exists( 'YITH_WCBR_Admin_Premium' ) ) {
		return YITH_WCBR_Admin_Premium::get_instance();
	}
	return YITH_WCBR_Admin::get_instance();
}
