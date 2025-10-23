<?php
/**
 * Shortcode class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Brands\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBR_Shortcode' ) ) {
	/**
	 * YITH_WCBR_Shortcode class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCBR_Shortcode {

		/**
		 * Performs all required add_shortcode
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function init() {
			// register shortcodes to WPBackery Visual Composer & Gutenberg & Elementor.
			add_action( 'vc_before_init', array( 'YITH_WCBR_Shortcode', 'register_vc_shortcodes' ) );
			add_action( 'init', array( 'YITH_WCBR_Shortcode', 'register_gutenberg_blocks' ) );
			add_action( 'yith_plugin_fw_gutenberg_before_do_shortcode', array( 'YITH_WCBR_Shortcode', 'fix_for_gutenberg_blocks' ), 10, 1 );
			add_action( 'init', array( 'YITH_WCBR_Shortcode', 'init_elementor_widgets' ) );

			add_shortcode( 'yith_wcbr_show_brand', array( 'YITH_WCBR_Shortcode', 'product_brand' ) );
			add_shortcode( 'yith_wcbr_product_brand', array( 'YITH_WCBR_Shortcode', 'product_brand' ) );
		}

		/**
		 * Register brands shortcode to visual composer
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function register_vc_shortcodes() {
			/**
			 * APPLY_FILTERS: yith_wcbr_vc_shortcodes_params
			 *
			 * Filter the array with the available parameters for the Visual Composer shortcodes.
			 *
			 * @param array $vc_map_params Array of parameters
			 *
			 * @return array
			 */
			$vc_map_params = apply_filters(
				'yith_wcbr_vc_shortcodes_params',
				array(
					'yith_wcbr_product_brand' => array(
						'name'        => __( 'YITH Product Brand', 'yith-woocommerce-brands-add-on' ),
						'base'        => 'yith_wcbr_product_brand',
						'description' => __( 'Adds the brand name and logo for a specific product anywhere you want.', 'yith-woocommerce-brands-add-on' ),
						'category'    => __( 'Brands', 'yith-woocommerce-brands-add-on' ),
						'params'      => array(
							array(
								'type'       => 'textfield',
								'holder'     => 'div',
								'heading'    => __( 'Title', 'yith-woocommerce-brands-add-on' ),
								'param_name' => 'title',
								'value'      => '',
							),
							array(
								'type'       => 'textfield',
								'holder'     => 'div',
								'heading'    => __( 'Product ID (leave empty to use global product)', 'yith-woocommerce-brands-add-on' ),
								'param_name' => 'product_id',
								'value'      => '',
							),
							array(
								'type'       => 'dropdown',
								'holder'     => '',
								'heading'    => __( 'Show logo', 'yith-woocommerce-brands-add-on' ),
								'param_name' => 'show_logo',
								'value'      => array(
									__( 'Show logo', 'yith-woocommerce-brands-add-on' ) => 'yes',
									__( 'Hide logo', 'yith-woocommerce-brands-add-on' ) => 'no',
								),
							),
							array(
								'type'       => 'dropdown',
								'holder'     => '',
								'heading'    => __( 'Show title', 'yith-woocommerce-brands-add-on' ),
								'param_name' => 'show_title',
								'value'      => array(
									__( 'Show title', 'yith-woocommerce-brands-add-on' ) => 'yes',
									__( 'Hide title', 'yith-woocommerce-brands-add-on' ) => 'no',
								),
							),
						),
					),
				)
			);

			if ( ! empty( $vc_map_params ) && function_exists( 'vc_map' ) ) {
				foreach ( $vc_map_params as $params ) {
					vc_map( $params );
				}
			}
		}

		/**
		 * Register Gutenberg blocks for this plugin
		 *
		 * @return void
		 */
		public static function register_gutenberg_blocks() {
			$blocks = array(
				'yith-wcbr-product-brand' => array(
					'style'          => 'yith-wcbr-shortcode',
					'script'         => 'yith-wcbr',
					'title'          => __( 'YITH Product Brand', 'yith-woocommerce-brands-add-on' ),
					'description'    => __( 'Adds the brand name and logo for a specific product anywhere you want.', 'yith-woocommerce-brands-add-on' ),
					'shortcode_name' => 'yith_wcbr_product_brand',
					'attributes'     => array(
						'title'      => array(
							'type'    => 'text',
							'label'   => __( 'Title', 'yith-woocommerce-brands-add-on' ),
							'default' => '',
						),
						'product_id' => array(
							'type'    => 'text',
							'label'   => __( 'Product ID (leave empty to use global product)', 'yith-woocommerce-brands-add-on' ),
							'default' => '',
						),
						'show_logo'  => array(
							'type'    => 'select',
							'label'   => __( 'Show logo', 'yith-woocommerce-brands-add-on' ),
							'default' => 'yes',
							'options' => array(
								'no'  => __( 'Hide logo', 'yith-woocommerce-brands-add-on' ),
								'yes' => __( 'Show logo', 'yith-woocommerce-brands-add-on' ),
							),
						),
						'show_title' => array(
							'type'    => 'select',
							'label'   => __( 'Show title', 'yith-woocommerce-brands-add-on' ),
							'default' => 'yes',
							'options' => array(
								'no'  => __( 'Hide title', 'yith-woocommerce-brands-add-on' ),
								'yes' => __( 'Show title', 'yith-woocommerce-brands-add-on' ),
							),
						),
					),
				),
			);

			yith_plugin_fw_gutenberg_add_blocks( $blocks );
		}

		/**
		 * Fix preview of Gutenberg blocks at backend
		 *
		 * @param string $shortcode Shortcode to render.
		 *
		 * @return void
		 */
		public static function fix_for_gutenberg_blocks( $shortcode ) {
			if ( strpos( $shortcode, '[yith_wcbr_product_brand' ) !== false ) {
				if ( strpos( $shortcode, 'product_id=""' ) !== false ) {
					$terms = yith_wcbr_get_terms(
						YITH_WCBR::$brands_taxonomy,
						array(
							'hide_empty' => true,
						)
					);

					if ( ! empty( $terms ) ) {
						$products = get_posts(
							array(
								'post_type'      => 'product',
								'post_status'    => 'publish',
								'posts_per_page' => 1,
								'fields'         => 'ids',
								'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
									array(
										'taxonomy' => YITH_WCBR::$brands_taxonomy,
										'terms'    => wp_list_pluck( $terms, 'term_id' ),
									),
								),
							)
						);

						if ( ! empty( $products ) ) {
							global $product;
							$product_id = array_pop( $products );
							$product    = wc_get_product( $product_id );
						}
					}
				}
			}
		}

		/**
		 * Register custom widgets for Elementor
		 *
		 * @return void
		 */
		public static function init_elementor_widgets() {
			// check if elementor is active.
			if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
				return;
			}

			// include widgets.
			include_once YITH_WCBR_INC . 'widget/elementor/class-yith-wcbr-elementor-product-brand.php';

			$register_widget_hook = version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ? 'elementor/widgets/register' : 'elementor/widgets/widgets_registered';

			// register widgets.
			add_action( $register_widget_hook, array( 'YITH_WCBR_Shortcode', 'register_elementor_widgets' ) );
		}

		/**
		 * Register Elementor Widgets
		 *
		 * @return void
		 */
		public static function register_elementor_widgets() {
			$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;

			if ( is_callable( array( $widgets_manager, 'register' ) ) ) {
				$widgets_manager->register( new YITH_WCBR_Elementor_Product_Brand() );
			} else {
				$widgets_manager->register_widget_type( new YITH_WCBR_Elementor_Product_Brand() );
			}
		}

		/**
		 * Returns output for product brand
		 *
		 * @param array $atts Array of shortcodes attributes.
		 *
		 * @return string Shortcode content
		 * @since 1.0.10
		 */
		public static function product_brand( $atts ) {
			/**
			 * The following variables will be extracted from $atts
			 *
			 * @var $product_id
			 * @var $title
			 * @var $show_logo
			 * @var $show_title
			 */

			global $product;

			$defaults = array(
				'title'           => '',
				'product_id'      => '',    // int (product id that will be used to retrieve brands; leave empty to use global product, if defined).
				'show_logo'       => 'yes', // yes - no (whether to show brand logo or not).
				'show_title'      => 'yes', // yes - no (whether to show brand title or not).
				'content_to_show' => '',    // both - name - logo (whether to show both brand logo & title, just title, or just logo; when passed overrides show_logo and show_title).
			);

			$atts = shortcode_atts(
				$defaults,
				$atts
			);

			// make attributes available.
			list ( $title, $product_id, $show_logo, $show_title, $content_to_show ) = yith_plugin_fw_extract( $atts, 'title', 'product_id', 'show_logo', 'show_title', 'content_to_show' );

			if ( ! $product && ! $product_id ) {
				return '';
			}

			if ( ! empty( $content_to_show ) ) {
				switch ( $content_to_show ) {
					case 'name':
						$show_logo  = 'no';
						$show_title = 'yes';
						break;

					case 'logo':
						$show_logo  = 'yes';
						$show_title = 'no';
						break;

					default:
						$show_logo  = 'yes';
						$show_title = 'yes';
				}
			}

			ob_start();
			YITH_WCBR()->add_single_product_brand_template( $product_id, $title, $show_logo, $show_title );

			return ob_get_clean();
		}
	}
}
