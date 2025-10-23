<?php
/**
 * Cross-sells
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cross-sells.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product, $woocommerce_loop;

$crosssells = WC()->cart->get_cross_sells();

if ( sizeof( $crosssells ) == 0 ) return;

$meta_query = WC()->query->get_meta_query();

$args = array(
	'post_type'           => 'product',
	'ignore_sticky_posts' => 1,
	'no_found_rows'       => 1,
	'posts_per_page'      => apply_filters( 'woocommerce_cross_sells_total', $posts_per_page ),
	'orderby'             => $orderby,
	'post__in'            => $crosssells,
	'meta_query'          => $meta_query
);

$products = new WP_Query( $args );

$woocommerce_loop['columns'] = apply_filters( 'woocommerce_cross_sells_columns', $columns );

if ( $products->have_posts() ) : ?>

	<div class="cross-sells related products widget ">
    	<?php
		$heading = apply_filters( 'woocommerce_product_cross_sells_products_heading', esc_html__( 'You may be interested in&hellip;', 'cena' ) );

		if ( $heading ) :
			?>
			<h3 class="widget-title"><span><?php echo esc_html( $heading ); ?></span></h3>
		<?php endif; ?>

		<?php woocommerce_product_loop_start(); ?>

			
			<?php wc_get_template( 'layout-products/carousel.php' , array( 'loop'=>$products,'rows' => '1', 'pagi_type' => 'no', 'nav_type' => 'yes','columns'=>$woocommerce_loop['columns'],'posts_per_page'=>$products->post_count,'screen_desktop'=>'2','screen_desktopsmall'=>'1','screen_tablet'=>'2','screen_mobile'=>'1' ) ); ?>
			
		<?php woocommerce_product_loop_end(); ?>

	</div>

<?php endif;

wp_reset_postdata();
