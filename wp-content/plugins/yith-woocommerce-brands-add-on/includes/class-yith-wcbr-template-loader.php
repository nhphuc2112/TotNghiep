<?php
/**
 * Template Loader
 *
 * Used to hold some utility regarding templates loading, and hook custom Block templates for Brands archive page
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Brands\Classes
 * @version 2.15.0
 */

use Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils;

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBR_Template_Loader' ) ) {
	/**
	 * YITH_WCR_Template_Loader class
	 *
	 * @since 2.15.0
	 */
	class YITH_WCBR_Template_Loader {
		/**
		 * Hook in methods.
		 */
		public static function init() {
			if ( version_compare( WC()->version, '7.9.0', '<' ) || ! wc_current_theme_supports_woocommerce_or_fse() ) {
				return;
			}

			add_filter( 'woocommerce_has_block_template', array( __CLASS__, 'has_template' ), 10, 2 );
			add_filter( 'get_block_templates', array( __CLASS__, 'add_block_templates' ), 10, 3 );
			add_filter( 'pre_get_block_file_template', array( __CLASS__, 'get_block_file_template' ), 10, 3 );
			add_filter( 'taxonomy_template_hierarchy', array( __CLASS__, 'add_archive_product_fallback' ), 10, 1 );
		}

		/**
		 * Returns slug of custom brands template
		 *
		 * @return string
		 */
		protected static function get_template_slug() {
			$taxonomy_slug = YITH_WCBR::$brands_taxonomy;

			return "taxonomy-$taxonomy_slug";
		}

		/**
		 * Returns title of custom brands template
		 *
		 * @return string
		 */
		protected static function get_template_title() {
			return __( 'Products by Brand', 'yith-woocommerce-brands-add-on' );
		}

		/**
		 * Returns description of custom brands template
		 *
		 * @return string
		 */
		protected static function get_template_description() {
			return __( 'Displays products filtered by a brand.', 'yith-woocommerce-brands-add-on' );
		}

		/**
		 * Returns directory where to search blockified templates
		 *
		 * @return string
		 */
		protected static function get_template_directory() {
			$root = YITH_WCBR_DIR . 'templates/blockified/';

			if ( ! BlockTemplateUtils::should_use_blockified_product_grid_templates() ) {
				$root .= 'legacy/';
			}

			return $root;
		}

		/**
		 * Returns object representing template file
		 *
		 * @return stdClass Template file object.
		 */
		protected static function get_template_file() {
			$template_slug = self::get_template_slug();
			$template_file = self::get_template_directory() . $template_slug . '.html';

			return BlockTemplateUtils::create_new_block_template_object( $template_file, 'wp_template', $template_slug );
		}

		/**
		 * Checks where specific blockified-template exists; filters out default WooCommerce result, to make it handle our template.
		 *
		 * @param bool   $has_template  Whether template exists.
		 * @param string $template_slug Template slug.
		 *
		 * @return bool
		 */
		public static function has_template( $has_template, $template_slug ) {
			if ( self::get_template_slug() === $template_slug && yith_plugin_fw_wc_is_using_block_template_in_product_catalogue() ) {
				return true;
			}

			return $has_template;
		}

		/**
		 * Filters get_block_file_template results, to add info about our template
		 *
		 * @param WP_Block_Template $template      Template being processed.
		 * @param string            $id            ID of the template.
		 * @param string            $template_type Type of the template.
		 *
		 * @return WP_Block_Template
		 */
		public static function get_block_file_template( $template, $id, $template_type ) {
			$template_name_parts = explode( '//', $id );

			if ( count( $template_name_parts ) < 2 ) {
				return $template;
			}

			list( $template_id, $template_slug ) = $template_name_parts;

			if ( self::get_template_slug() === $template_slug ) {
				$template_file = self::get_template_file();

				$template_file->title       = self::get_template_title();
				$template_file->description = self::get_template_description();

				$template_built = BlockTemplateUtils::build_template_result_from_file( $template_file, $template_type );
			}

			if ( ! empty( $template_built ) ) {
				return $template_built;
			}

			return $template;
		}

		/**
		 * Add the block template objects to be used.
		 *
		 * @param array  $query_result Array of template objects.
		 * @param array  $query Optional. Arguments to retrieve templates.
		 * @param string $template_type wp_template or wp_template_part.
		 * @return array
		 */
		public static function add_block_templates( $query_result, $query, $template_type ) {
			// return if blocks templates aren't supported.
			if ( ! BlockTemplateUtils::supports_block_templates() ) {
				return $query_result;
			}

			// return if we're not searching our template.
			if ( isset( $query['slug__in'] ) && ! in_array( self::get_template_slug(), $query['slug__in'], true ) ) {
				return $query_result;
			}

			// add template if can't find already.
			if ( ! wp_list_filter( $query_result, array( 'slug' => self::get_template_slug() ) ) ) {
				$template_file  = self::get_template_file();
				$query_result[] = BlockTemplateUtils::build_template_result_from_file( $template_file, $template_type );
			}

			// We need to remove theme (i.e. filesystem) templates that have the same slug as a customised one.
			// This only affects saved templates that were saved BEFORE a theme template with the same slug was added.
			$query_result = BlockTemplateUtils::remove_theme_templates_with_custom_alternative( $query_result );

			/**
			 * WC templates from theme aren't included in `$this->get_block_templates()` but are handled by Gutenberg.
			 * We need to do additional search through all templates file to update title and description for WC
			 * templates that aren't listed in theme.json.
			 */
			$query_result = array_map(
				function( $template ) {
					if ( self::get_template_slug() == $template->slug ) {
						$template->title = self::get_template_title();
						$template->description = self::get_template_description();
					}

					return $template;
				},
				$query_result
			);

			return $query_result;
		}

		/**
		 * Add product-archive.php template in template hierarchy of brand template
		 *
		 * @param array $template_hierarchy Hierarchy for current template.
		 * @return array Filtered hierarchy.
		 */
		public static function add_archive_product_fallback( $template_hierarchy ) {
			$template_slugs = array_map( '_strip_template_file_suffix', $template_hierarchy );

			if ( in_array( self::get_template_slug(), $template_slugs ) ) {
				$template_hierarchy[] = 'archive-product';
			}

			return $template_hierarchy;
		}
	}
}
