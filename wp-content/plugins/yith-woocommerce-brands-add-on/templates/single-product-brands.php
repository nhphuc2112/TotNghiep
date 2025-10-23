<?php
/**
 * Brand in single product page.
 *
 * @author  YITH <plugins@yithemes.com>
 *
 * @package YITH\Brands\Templates
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly

?>

<?php if ( $product_has_brands ) : ?>
	<?php if ( ! empty( $title ) ) : ?>
		<h3><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>

	<?php if ( ! isset( $content_to_show ) || ( 'both' === $content_to_show || 'name' === $content_to_show ) ) : ?>
		<span class="yith-wcbr-brands">
			<?php echo $brands_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span itemprop="brand" ><?php echo get_the_term_list( $product_id, $brands_taxonomy, $before_term_list, $term_list_sep, $after_term_list ); ?></span>
		</span>
	<?php endif; ?>

	<?php if ( ! isset( $content_to_show ) || ( 'both' === $content_to_show || 'logo' === $content_to_show ) ) : ?>
		<span class="yith-wcbr-brands-logo">
			<?php
			foreach ( $product_brands as $p_term ) {
				$thumbnail_id = absint( yith_wcbr_get_term_meta( $p_term->term_id, 'thumbnail_id', true ) );

				if ( $thumbnail_id ) {
					/**
					 * APPLY_FILTERS: yith_wcbr_image_size_single_product_brads
					 *
					 * Filter the HTML string for the brand image.
					 *
					 * @param string $img_html     HTML image string
					 * @param int    $thumbnail_id Brand thumbnaild ID
					 *
					 * @return string
					 */
					$image = apply_filters( 'yith_wcbr_image_size_single_product_brads', wp_get_attachment_image( $thumbnail_id, 'yith_wcbr_logo_size' ), $thumbnail_id );

					if ( $image ) {
						echo sprintf( '<a href="%s">%s</a>', get_term_link( $p_term ), $image ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				} else {
					/**
					 * DO_ACTION: yith_wcbr_no_brand_logo
					 *
					 * Allows to render some content or fire some action when the brand has no logo.
					 *
					 * @param int     $term_id         Term ID
					 * @param WP_Term $term            Term object
					 * @param string  $image_size      Image size
					 * @param bool    $show_term_name  Whether to show the brand name
					 * @param bool    $show_avg_rating Whether to show the brand rating
					 */
					do_action( 'yith_wcbr_no_brand_logo', $p_term->term_id, $p_term, 'yith_wcbr_logo_size', false, false );
				}
			}
			?>
		</span>
	<?php endif; ?>
<?php endif; ?>
