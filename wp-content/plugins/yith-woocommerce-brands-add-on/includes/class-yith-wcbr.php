<?php
/**
 * Main class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Brands\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBR' ) ) {
	/**
	 * YITH_WCBR class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCBR {
		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCBR
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Default taxonomy slug
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public static $default_taxonomy = 'yith_product_brand';

		/**
		 * Taxonomy slug
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public static $brands_taxonomy = 'yith_product_brand';

		/**
		 * Rewrite for brands
		 *
		 * @var string
		 * @since 1.2.0
		 */
		public static $brands_rewrite = 'product-brands';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCBR
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// register brand taxonomy.
			add_action( 'init', array( $this, 'register_taxonomy' ) );

			// enqueue styles.
			add_action( 'init', array( $this, 'register_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			add_filter( 'yith_wcan_product_taxonomy_type', array( $this, 'add_ajax_navigation_taxonomy' ) );

			// register new image dimensions.
			add_action( 'after_setup_theme', array( $this, 'register_image_size' ) );

			// register shortcodes.
			add_action( 'init', array( 'YITH_WCBR_Shortcode', 'init' ), 5 );

			// init templates loading.
			add_action( 'init', array( 'YITH_WCBR_Template_Loader', 'init' ) );

			// add brand template to products page.
			add_action( 'wp', array( $this, 'add_single_product_brand_action' ) );
			add_filter( 'yith_wcbr_skip_single_brand_block', array( $this, 'skip_single_product_brand_block_action' ), 10, 3 );

			// register taxonomy as product taxonomy for YIT Layout.
			add_filter( 'yit_layout_option_is_product_tax', array( $this, 'register_layout' ) );

			// add brand taxonomy to ones WooCommerce uses to alter count basing on products visibility.
			add_filter( 'woocommerce_change_term_counts', array( $this, 'change_term_counts' ) );

			// Set Brand taxonomy term when you duplicate the product.
			add_action( 'woocommerce_product_duplicate', array( $this, 'woocommerce_product_duplicate' ), 10, 2 );

			add_action( 'before_woocommerce_init', array( $this, 'declare_wc_features_support' ) );
		}

		/* === TAXONOMY METHODS === */

		/**
		 * Register taxonomy for brands
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_taxonomy() {
			/**
			 * APPLY_FILTERS: yith_wcbr_taxonomy_slug
			 *
			 * Filter the key for the Brands taxonomy.
			 *
			 * @param string $brands_taxonomy Taxonomy key
			 *
			 * @return string
			 */
			self::$brands_taxonomy = apply_filters( 'yith_wcbr_taxonomy_slug', self::$brands_taxonomy );

			/**
			 * APPLY_FILTERS: yith_wcbr_taxonomy_label_name
			 *
			 * Filter the name for the Brands taxonomy.
			 *
			 * @param string $taxonomy_name Taxonomy name
			 *
			 * @return string
			 */
			$taxonomy_labels = array(
				'name'                       => apply_filters( 'yith_wcbr_taxonomy_label_name', __( 'Brands', 'yith-woocommerce-brands-add-on' ) ),
				'singular_name'              => __( 'Brand', 'yith-woocommerce-brands-add-on' ),
				'all_items'                  => __( 'All brands', 'yith-woocommerce-brands-add-on' ),
				'edit_item'                  => __( 'Edit brand', 'yith-woocommerce-brands-add-on' ),
				'view_item'                  => __( 'View brand', 'yith-woocommerce-brands-add-on' ),
				'update_item'                => __( 'Update brand', 'yith-woocommerce-brands-add-on' ),
				'add_new_item'               => __( 'Add new brand', 'yith-woocommerce-brands-add-on' ),
				'new_item_name'              => __( 'New brand name', 'yith-woocommerce-brands-add-on' ),
				'parent_item'                => __( 'Parent brand', 'yith-woocommerce-brands-add-on' ),
				'parent_item_colon'          => __( 'Parent brand:', 'yith-woocommerce-brands-add-on' ),
				'search_items'               => __( 'Search brands', 'yith-woocommerce-brands-add-on' ),
				'separate_items_with_commas' => __( 'Separate brands with commas', 'yith-woocommerce-brands-add-on' ),
				'not_found'                  => __( 'No brands found', 'yith-woocommerce-brands-add-on' ),
				'back_to_items'              => __( '&larr; Go to brands', 'yith-woocommerce-brands-add-on' ),
			);

			/**
			 * APPLY_FILTERS: yith_wcbr_taxonomy_args
			 *
			 * Filter the array with the available parameters for the taxonomy creation.
			 *
			 * @param array $arfs Array of arguments
			 *
			 * @return array
			 */
			$taxonomy_args = apply_filters(
				'yith_wcbr_taxonomy_args',
				array(
					/**
					 * APPLY_FILTERS: yith_wcbr_taxonomy_label
					 *
					 * Filter the label for the Brands taxonomy.
					 *
					 * @param string $taxonomy_label Taxonomy label
					 *
					 * @return string
					 */
					'label'                 => apply_filters( 'yith_wcbr_taxonomy_label', __( 'Brands', 'yith-woocommerce-brands-add-on' ) ),
					/**
					 * APPLY_FILTERS: yith_wcbr_taxonomy_labels
					 *
					 * Filter the labels for the Brands taxonomy.
					 *
					 * @param array $taxonomy_labels Taxonomy labels
					 *
					 * @return array
					 */
					'labels'                => apply_filters( 'yith_wcbr_taxonomy_labels', $taxonomy_labels ),
					'public'                => true,
					'show_admin_column'     => true,
					/**
					 * APPLY_FILTERS: yith_wcbr_show_taxonomy_in_menu
					 *
					 * Filter whether to show the taxonomy in the menu.
					 *
					 * @param bool $show_in_menu Whether to show the taxonomy in the menu or not
					 *
					 * @return bool
					 */
					'show_in_menu'          => apply_filters( 'yith_wcbr_show_taxonomy_in_menu', false ),
					'show_ui'               => true,
					/**
					 * APPLY_FILTERS: yith_wcbr_taxonomy_hierarchical
					 *
					 * Filter whether the taxonomy is hierarchical.
					 *
					 * @param bool $is_hierarchical Whether the taxonomy is hierarchical or not
					 *
					 * @return bool
					 */
					'hierarchical'          => apply_filters( 'yith_wcbr_taxonomy_hierarchical', true ),
					'rewrite'               => array(
						/**
						 * APPLY_FILTERS: yith_wcbr_taxonomy_rewrite
						 *
						 * Filter the taxonomy slug for the rewrite rules.
						 *
						 * @param string $slug Taxonomy slug
						 *
						 * @return string
						 */
						'slug'         => apply_filters( 'yith_wcbr_taxonomy_rewrite', self::$brands_rewrite ),
						'hierarchical' => true,
						/**
						 * APPLY_FILTERS: yith_wcbr_taxonomy_with_front
						 *
						 * Filter whether the permastruct should be prepended with WP_Rewrite::$front.
						 *
						 * @param bool $with_front Whether prepend with WP_Rewrite::$front or not
						 *
						 * @return bool
						 */
						'with_front'   => apply_filters( 'yith_wcbr_taxonomy_with_front', true ),
					),
					/**
					 * APPLY_FILTERS: yith_wcbr_taxonomy_capabilities
					 *
					 * Filter the capabilities for the Brands taxonomy.
					 *
					 * @param array $capabilites Taxonomy capabilites
					 *
					 * @return array
					 */
					'capabilities'          => apply_filters(
						'yith_wcbr_taxonomy_capabilities',
						array(
							'manage_terms' => 'manage_product_terms',
							'edit_terms'   => 'edit_product_terms',
							'delete_terms' => 'delete_product_terms',
							'assign_terms' => 'assign_product_terms',
						)
					),
					'update_count_callback' => '_wc_term_recount',
				)
			);

			/**
			 * APPLY_FILTERS: yith_wcbr_taxonomy_object_type
			 *
			 * Filter the object type with which the taxonomy should be associated.
			 *
			 * @param string $object_type Object type
			 *
			 * @return string
			 */
			$object_type = apply_filters( 'yith_wcbr_taxonomy_object_type', 'product' );

			register_taxonomy( self::$brands_taxonomy, $object_type, $taxonomy_args );

			if ( is_array( $object_type ) && ! empty( $object_type ) ) {
				foreach ( $object_type as $type ) {
					register_taxonomy_for_object_type( self::$brands_taxonomy, $type );
				}
			} else {
				register_taxonomy_for_object_type( self::$brands_taxonomy, $object_type );
			}
		}

		/**
		 * Register frontend scripts
		 *
		 * @return void
		 * @since 1.3.0
		 */
		public function register_scripts() {
			// include payment form template.
			$template_name = 'brands.css';
			$locations     = array(
				trailingslashit( WC()->template_path() ) . 'yith-wcbr/' . $template_name,
				trailingslashit( WC()->template_path() ) . $template_name,
				'yith-wcbr/' . $template_name,
				$template_name,
			);

			$template = locate_template( $locations );

			if ( ! $template ) {
				$template = YITH_WCBR_URL . 'assets/css/yith-wcbr.css';
			} else {
				$search   = array( get_stylesheet_directory(), get_template_directory() );
				$replace  = array( get_stylesheet_directory_uri(), get_template_directory_uri() );
				$template = str_replace( $search, $replace, $template );
			}

			wp_register_style( 'yith-wcbr', $template, array(), YITH_WCBR_VERSION );
		}

		/**
		 * Enqueue frontend scripts
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			/**
			 * DO_ACTION: yith_wcbr_enqueue_frontend_style
			 *
			 * Allows to fire some action before enqueueing the plugin style.
			 */
			do_action( 'yith_wcbr_enqueue_frontend_style' );
			wp_enqueue_style( 'yith-wcbr' );
		}

		/**
		 * Add compatibility to Ajax Navigation, forcing widget to display on brands archive pages
		 *
		 * @param array $tax Valid product taxonomies where to show ajax navigation widget.
		 *
		 * @return array Filtered array
		 * @since 1.0.0
		 */
		public function add_ajax_navigation_taxonomy( $tax ) {
			return array_merge(
				$tax,
				array( self::$brands_taxonomy )
			);
		}

		/* === FRONTEND METHODS === */

		/**
		 * Register thumb size for brand logo on single product page
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_image_size() {
			$default_values = array(
				'width'  => 500,
				'height' => 100,
				'crop'   => true,
			);
			$stored_values  = get_option( 'yith_wcbr_single_product_brands_size', $default_values );

			/**
			 * APPLY_FILTERS: yith_wcbr_single_thumb_width
			 *
			 * Filter the default width for the image size in the product page.
			 *
			 * @param int $width Default width
			 *
			 * @return int
			 */
			$single_thumb_width = apply_filters( 'yith_wcbr_single_thumb_width', $stored_values['width'] );

			/**
			 * APPLY_FILTERS: yith_wcbr_single_thumb_height
			 *
			 * Filter the default height for the image size in the product page.
			 *
			 * @param int $height Default height
			 *
			 * @return int
			 */
			$single_thumb_height = apply_filters( 'yith_wcbr_single_thumb_height', $stored_values['height'] );

			/**
			 * APPLY_FILTERS: yith_wcbr_single_thumb_crop
			 *
			 * Filter whether to crop image in the product page.
			 *
			 * @param bool $crop Whether to crop image
			 *
			 * @return bool
			 */
			$single_thumb_crop = apply_filters( 'yith_wcbr_single_thumb_crop', isset( $stored_values['crop'] ) ? $stored_values['crop'] : false );

			add_image_size( 'yith_wcbr_logo_size', $single_thumb_width, $single_thumb_height, $single_thumb_crop );
		}

		/**
		 * Adds single product brand template to correct action
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_single_product_brand_action() {
			if ( yith_plugin_fw_wc_is_using_block_template_in_single_product() ) {
				$this->add_single_product_brand_block_action();
				return;
			}

			add_action( 'woocommerce_product_meta_end', array( $this, 'add_single_product_brand_template' ) );
		}

		/**
		 * Append/Prepend single product brand to the correct block when using blockified templates
		 */
		public function add_single_product_brand_block_action() {
			if ( ! is_product() ) {
				return;
			}

			add_filter(
				'render_block_core/post-terms',
				function( $block_content, $parsed_block, $block ) {
					// if we're currently printing a loop (like related products in single product page) skip.
					if ( apply_filters( 'yith_wcbr_skip_single_brand_block', isset( $GLOBALS['woocommerce_loop'] ), $block, 'woocommerce_product_meta_end' ) ) {
						return $block_content;
					}

					return $this->add_brand_after_block( $block_content, $parsed_block, $block );
				},
				10,
				3
			);
		}

		/**
		 * Skip Single brand template addition to blockified template
		 *
		 * @param bool     $skip     Whether to skip or not.
		 * @param WP_Block $block    Block object that is used as reference (brand template will be appended to its content).
		 * @param string   $position Position where brands template should be hooked in single product page.
		 *
		 * @return bool Whether to skip template addition or not.
		 */
		public function skip_single_product_brand_block_action( $skip, $block, $position ) {
			if (
				in_array( $position, array( 'woocommerce_product_meta_end', 'woocommerce_template_single_sharing' ), true ) &&
				(
					! isset( $block->attributes['term'] ) ||
					'product_tag' !== $block->attributes['term']
				)
			) {
				return true;
			}

			return $skip;
		}

		/**
		 * Prepend brands template to a block
		 * Uses block context to retrieve product id.
		 *
		 * @param string   $block_content The block content.
		 * @param string   $parsed_block  The full block, including name and attributes.
		 * @param WP_Block $block         The block instance.
		 *
		 * @return string Filtered block content.
		 */
		public function add_brand_before_block( $block_content, $parsed_block, $block ) {
			$post_id = $block->context['postId'];
			$product = wc_get_product( $post_id );

			if ( ! $product ) {
				return $block_content;
			}

			$method = ! empty( $GLOBALS['woocommerce_loop'] ) ? 'add_loop_brand_template' : 'add_single_product_brand_template';

			ob_start();
			$this->$method( $product->get_id() );
			$template = ob_get_clean();

			return "$template $block_content";
		}

		/**
		 * Append brands template to a block
		 * Uses block context to retrieve product id.
		 *
		 * @param string   $block_content The block content.
		 * @param string   $parsed_block  The full block, including name and attributes.
		 * @param WP_Block $block         The block instance.
		 *
		 * @return string Filtered block content.
		 */
		public function add_brand_after_block( $block_content, $parsed_block, $block ) {
			global $post;

			$post_id = isset( $block->context['postId'] ) ? $block->context['postId'] : false;
			$post_id = $post_id ?? isset( $post->ID ) ? $post->ID : false;
			$product = wc_get_product( $post_id );

			if ( ! $product ) {
				return $block_content;
			}

			$method = ! empty( $GLOBALS['woocommerce_loop'] ) ? 'add_loop_brand_template' : 'add_single_product_brand_template';

			ob_start();
			$this->$method( $product->get_id() );
			$template = ob_get_clean();

			return "$block_content $template";
		}

		/**
		 * Include template for brands on single product page
		 *
		 * @param int|bool    $product_id Current product id; leave empty to use global product.
		 * @param string      $title      Title to show when using in shortcode.
		 * @param string|bool $show_logo  Whether to show logo or not (yes - no; false to use default).
		 * @param string|bool $show_title Whether to show title or not (yes - no; false to use default).
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_single_product_brand_template( $product_id = false, $title = '', $show_logo = false, $show_title = false ) {
			global $post, $product;

			$current_product = $product_id ? wc_get_product( $product_id ) : $product;
			$current_product = $current_product instanceof WC_Product ? $current_product : ( isset( $post->ID ) ? wc_get_product( $post->ID ) : false );

			if ( ! $current_product instanceof WC_Product ) {
				return;
			}

			$current_product_id = $current_product->get_id();

			// retrieve data to use in template.
			$brands_taxonomy = self::$brands_taxonomy;

			/**
			 * APPLY_FILTERS: yith_wcbr_single_product_before_term_list
			 *
			 * Filter the content to be displayed before the terms list in the product page.
			 *
			 * @param string $content Content
			 *
			 * @return string
			 */
			$before_term_list = apply_filters( 'yith_wcbr_single_product_before_term_list', '' );

			/**
			 * APPLY_FILTERS: yith_wcbr_single_product_after_term_list
			 *
			 * Filter the content to be displayed after the terms list in the product page.
			 *
			 * @param string $content Content
			 *
			 * @return string
			 */
			$after_term_list = apply_filters( 'yith_wcbr_single_product_after_term_list', '' );

			/**
			 * APPLY_FILTERS: yith_wcbr_single_product_term_list_sep
			 *
			 * Filter the separator for the terms list in the product page.
			 *
			 * @param string $separator Separator
			 *
			 * @return string
			 */
			$term_list_sep      = apply_filters( 'yith_wcbr_single_product_term_list_sep', ', ' );
			$brands_label       = get_option( 'yith_wcbr_brands_label' );
			$product_brands     = get_the_terms( $current_product_id, self::$brands_taxonomy );
			$product_has_brands = ! is_wp_error( $product_brands ) && $product_brands;
			$content_to_show    = get_option( 'yith_wcbr_single_product_brands_content', 'both' );

			if ( 'yes' === $show_logo && 'yes' === $show_title ) {
				$content_to_show = 'both';
			} elseif ( 'yes' === $show_logo ) {
				$content_to_show = 'logo';
			} elseif ( 'yes' === $show_title ) {
				$content_to_show = 'name';
			}

			/**
			 * APPLY_FILTERS: yith_wcbr_single_product_brand_template_args
			 *
			 * Filter the array with the data passed to the template.
			 *
			 * @param array $data Data passed to the template
			 *
			 * @return array
			 */
			$args = apply_filters(
				'yith_wcbr_single_product_brand_template_args',
				array(
					'title'              => $title,
					'product'            => $current_product,
					'product_id'         => $current_product_id,
					'brands_taxonomy'    => $brands_taxonomy,
					'before_term_list'   => $before_term_list,
					'after_term_list'    => $after_term_list,
					'term_list_sep'      => $term_list_sep,
					'brands_label'       => $brands_label,
					'product_brands'     => $product_brands,
					'product_has_brands' => $product_has_brands,
					'content_to_show'    => $content_to_show,
				)
			);

			// include payment form template.
			$template_name = 'single-product-brands.php';

			yith_wcbr_get_template( $template_name, $args );
		}

		/**
		 * Register Brands as product taxonomy for YIT Layout
		 *
		 * @param bool $is_product_taxonomy Whether current queried object is a product taxonomy.
		 *
		 * @return bool Filtered value
		 */
		public function register_layout( $is_product_taxonomy ) {
			global $wp_query;

			if ( $wp_query->is_tax( self::$brands_taxonomy ) ) {
				return true;
			}

			return $is_product_taxonomy;
		}


		/**
		 * Register brand taxonomy to change term counts depending on product visibility
		 *
		 * @param array $taxonomies Array of registered taxonomies.
		 *
		 * @return array Filtered array of registered taxonomies
		 * @since 1.1.2
		 */
		public function change_term_counts( $taxonomies ) {
			$taxonomies[] = self::$brands_taxonomy;

			return $taxonomies;
		}

		/**
		 * Set brands for duplicated product
		 *
		 * @param WC_Product $duplicate Duplicated.
		 * @param WC_Product $product   Original.
		 */
		public function woocommerce_product_duplicate( $duplicate, $product ) {
			$brands     = wp_get_object_terms( $product->get_id(), self::$brands_taxonomy );
			$brands_ids = array();

			if ( count( $brands ) > 0 ) {
				foreach ( $brands as $brand ) {
					$brands_ids[] = $brand->term_id;
				}

				wp_set_object_terms( $duplicate->get_id(), $brands_ids, self::$brands_taxonomy );
			}
		}

		/**
		 * Declare support for WooCommerce features.
		 *
		 * @since 2.10.0
		 */
		public function declare_wc_features_support() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YITH_WCBR_INIT, true );
			}
		}
	}
}

/**
 * Unique access to instance of YITH_WCBR class
 *
 * @return \YITH_WCBR
 * @since 1.0.0
 */
function YITH_WCBR() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	if ( class_exists( 'YITH_WCBR_Premium' ) ) {
		return YITH_WCBR_Premium::get_instance();
	}

	return YITH_WCBR::get_instance();
}
