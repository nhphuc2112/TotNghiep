<?php
/**
 * Utility functions
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Brands\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly.

if ( ! function_exists( 'yith_wcbr_is_valid_url' ) ) {
	/**
	 * Simple check for validating a URL, it must start with http:// or https://.
	 * and pass FILTER_VALIDATE_URL validation.
	 *
	 * @param string $url Url.
	 *
	 * @return bool
	 * @since 1.0.7
	 */
	function yith_wcbr_is_valid_url( $url ) {
		if ( function_exists( 'wc_is_valid_url' ) ) {
			return wc_is_valid_url( $url );
		}

		// Must start with http:// or https:// .
		if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) ) {
			return false;
		}

		// Must pass validation.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'yith_wcbr_get_terms' ) ) {
	/**
	 * Wrapper for get_terms function
	 * Prior 4.5 -> get_terms( taxonomy, args )
	 * After 4.5 -> get_terms( args )
	 *
	 * @param string|array $taxonomy Taxonomy slug, or list of them.
	 * @param mixed        $args Arguments for the query.
	 *
	 * @return mixed List of \WP_Terms or \WP_Error.
	 * @since 1.0.7
	 */
	function yith_wcbr_get_terms( $taxonomy, $args ) {
		global $wp_version;

		$terms = array();

		// is no orderby param is set, user order.
		if ( ! isset( $args['orderby'] ) || 'none' === $args['orderby'] ) {
			$args['meta_query']   = isset( $args['meta_query'] ) ? $args['meta_query'] : array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => 'order',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'order',
					'compare' => 'NOT EXISTS',
				),
			);
			$args['orderby']      = 'meta_value_num';
		}

		// remove empty terms when hide_empty is set.
		if ( isset( $args['hide_empty'] ) && $args['hide_empty'] && in_array( $taxonomy, array( 'product_cat', 'product_tag', 'yith_product_brand' ), true ) ) {
			add_filter( 'get_terms', '_yith_wcbr_remove_empty_terms', 15, 2 );

			$args['meta_query']   = isset( $args['meta_query'] ) ? $args['meta_query'] : array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['meta_query'][] = array(
				'key'     => 'product_count_' . $taxonomy,
				'value'   => 0,
				'compare' => '>',
			);
		}

		if ( version_compare( $wp_version, '4.5', '<' ) ) {
			/**
			 * APPLY_FILTERS: yith_wcbr_get_terms_args
			 *
			 * Filter the array with the available parameters to get the terms.
			 *
			 * @param array $args Array of arguments
			 *
			 * @return array
			 */
			$terms = get_terms( $taxonomy, apply_filters( 'yith_wcbr_get_terms_args', $args ) );
		} else {
			$args = array_merge(
				$args,
				array(
					'taxonomy' => $taxonomy,
				)
			);

			$terms = get_terms( apply_filters( 'yith_wcbr_get_terms_args', $args ) );
		}

		// remove empty terms when hide_empty is set.
		if ( isset( $args['hide_empty'] ) && $args['hide_empty'] ) {
			remove_filter( 'get_terms', '_yith_wcbr_remove_empty_terms', 15 );
		}

		return $terms;
	}
}

if ( ! function_exists( 'yith_wcbr_get_template' ) ) {
	/**
	 * Get template for Brands plugin
	 *
	 * @param string $filename Template name (with or without extension).
	 * @param array  $args     Array of params to use in the template.
	 * @param string $section  Subdirectory where to search.
	 */
	function yith_wcbr_get_template( $filename, $args = array(), $section = '' ) {
		$ext = strpos( $filename, '.php' ) === false ? '.php' : '';

		$template_name = $section . '/' . $filename . $ext;
		$template_path = WC()->template_path() . 'yith-wcbr/';
		$default_path  = YITH_WCBR_DIR . 'templates/';

		if ( defined( 'YITH_WCBR_PREMIUM' ) ) {
			$premium_template = str_replace( '.php', '-premium.php', $template_name );
			$located_premium  = wc_locate_template( $premium_template, $template_path, $default_path );
			$template_name    = file_exists( $located_premium ) ? $premium_template : $template_name;
		}

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}
}

if ( ! function_exists( 'yith_wcbr_add_slider_post_class' ) ) {
	/**
	 * Add classes to posts for sliders
	 *
	 * @param array $classes Array of available class.
	 *
	 * @return mixed Filtered array of classes
	 * @since 1.0.0
	 */
	function yith_wcbr_add_slider_post_class( $classes ) {
		$classes[] = 'swiper-slide';

		return $classes;
	}
}

if ( ! function_exists( 'yith_wcbr_get_term_meta' ) ) {
	/**
	 * Get term meta (wrapper added to handle backward compatibility with WC < 2.6 and WP < 4.4)
	 *
	 * @param int    $term_id Id.
	 * @param string $key     Key.
	 * @param bool   $single  (default true).
	 *
	 * @return mixed meta value
	 * @since 1.0.7
	 */
	function yith_wcbr_get_term_meta( $term_id, $key, $single = true ) {
		if ( version_compare( preg_replace( '/-beta-([0-9]+)/', '', WC()->version ), '2.6', '>=' ) && function_exists( 'get_term_meta' ) ) {
			return get_term_meta( $term_id, $key, $single );
		} else {
			return get_metadata( 'woocommerce_term', $term_id, $key, $single );
		}
	}
}

if ( ! function_exists( 'yith_wcbr_update_term_meta' ) ) {
	/**
	 * Update term meta (wrapper added to handle backward compatibility with WC < 2.6 and WP < 4.4)
	 *
	 * @param int    $term_id .
	 * @param string $meta_key .
	 * @param mixed  $meta_value .
	 * @param string $prev_value mixed.
	 *
	 * @return bool|int|WP_Error
	 * @since 1.0.7
	 */
	function yith_wcbr_update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		if ( version_compare( preg_replace( '/-beta-([0-9]+)/', '', WC()->version ), '2.6', '>=' ) && function_exists( 'update_term_meta' ) ) {
			return update_term_meta( $term_id, $meta_key, $meta_value, $prev_value );
		} else {
			return update_metadata( 'woocommerce_term', $term_id, $meta_key, $meta_value, $prev_value );
		}
	}
}

if ( ! function_exists( '_yith_wcbr_remove_empty_terms' ) ) {
	/**
	 * Remove empty terms when required
	 * Do not call this function directly; this is only intended to by used by the plugin internally
	 *
	 * @param array $terms      Array of retrieved terms.
	 * @param array $taxonomies Array of current taxonomies.
	 *
	 * @return array Array of filtered terms
	 * @since 1.1.2
	 */
	function _yith_wcbr_remove_empty_terms( $terms, $taxonomies ) {
		if ( YITH_WCBR::$brands_taxonomy !== $taxonomies[0] ) {
			return $terms;
		}

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $id => $p_term ) {
				if ( ! is_object( $p_term ) || ! isset( $p_term->count ) || ! $p_term->count ) {
					unset( $terms[ $id ] );
				}
			}
		}

		return $terms;
	}
}

if ( ! function_exists( 'yith_wcbr_get_view' ) ) {
	/**
	 * Get the view
	 *
	 * @param string $view View name.
	 * @param array  $args Parameters to include in the view.
	 */
	function yith_wcbr_get_view( $view, $args = array() ) {
		$view_path = trailingslashit( YITH_WCBR_VIEWS ) . $view;

		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		if ( file_exists( $view_path ) ) {
			include $view_path;
		}
	}
}
