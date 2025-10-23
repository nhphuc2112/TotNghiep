<?php
/**
 * Brand description.
 *
 * @author  YITH <plugins@yithemes.com>
 *
 * @package YITH\Brands\Templates
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly

global $product;

?>

<?php
/**
 * APPLY_FILTERS: yith_wcbr_print_brand_description
 *
 * Filter whether to show the brand description in the brand page.
 *
 * @param bool    $show_description Whether to show brand description in the brand page
 * @param WP_Term $term             Term object
 *
 * @return bool
 */
if ( apply_filters( 'yith_wcbr_print_brand_description', true, $p_term ) ) :
	?>
	<div class="yith-wcbr-archive-header term-description">
		<?php
		if ( ! empty( $term_description ) ) {
			echo wpautop( do_shortcode( $term_description ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</div>
<?php endif; ?>
