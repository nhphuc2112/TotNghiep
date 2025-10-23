<?php
/**
 * Single Product Up-Sells
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     9.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

$per_page = cena_tbay_get_config('number_product_releated', 4);

$upsells = $product->get_upsell_ids();

if ( sizeof( $upsells ) == 0 ) {
	return;
}

$meta_query = WC()->query->get_meta_query();

$args = array(
	'post_type'           => 'product',
	'ignore_sticky_posts' => 1,
	'no_found_rows'       => 1,
	'posts_per_page'      => $posts_per_page,
	'orderby'             => $orderby,
	'post__in'            => $upsells,
	'post__not_in'        => array( $product->get_id() ),
	'meta_query'          => $meta_query
);

$products = new WP_Query( $args );

$woocommerce_loop['columns'] = cena_tbay_get_config('releated_product_columns', 4);

$show_product_upsells 		 = cena_tbay_get_config('enable_product_upsells', true);

if ( $show_product_upsells && $products->have_posts() ) : ?>

	<div class="upsells widget products">
		<?php 
			$heading = apply_filters( 'woocommerce_product_upsells_products_heading', esc_html__( 'You may also like&hellip;', 'cena' ) );
		?>
		<?php if ( $heading ) :
			?>
			<h2 class="widget-title"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<?php wc_get_template( 'layout-products/carousel.php' , array( 'loop'=>$products,'rows' => '1', 'pagi_type' => 'no', 'nav_type' => 'yes','columns'=>$woocommerce_loop['columns'],'posts_per_page'=>$products->post_count,'screen_desktop'=>$woocommerce_loop['columns'],'screen_desktopsmall'=>'3','screen_tablet'=>'2','screen_mobile'=>'1' ) ); ?>
	</div>

<?php endif;

wp_reset_postdata();
