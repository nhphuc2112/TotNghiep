<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     9.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product, $woocommerce_loop;

if ( empty( $product ) || ! $product->exists() ) {
	return;
}
$per_page = cena_tbay_get_config('number_product_releated', 4);
$related = wc_get_related_products( $product->get_id(), $per_page );

if ( sizeof( $related ) == 0 ) return;

$args = apply_filters( 'woocommerce_related_products_args', array(
	'post_type'            => 'product',
	'ignore_sticky_posts'  => 1,
	'no_found_rows'        => 1,
	'posts_per_page'       => $per_page,
	'orderby'              => $orderby,
	'post__in'             => $related,
	'post__not_in'         => array( $product->get_id() )
) );

$products = new WP_Query( $args );

$woocommerce_loop['columns'] 	= cena_tbay_get_config('releated_product_columns', 4);

$show_product_releated 			= cena_tbay_get_config('enable_product_releated', true);

$heading = apply_filters( 'woocommerce_product_related_products_heading', esc_html__( 'Related products', 'cena' ) );

if ( $show_product_releated && $products->have_posts() ) : ?>

	<div class="related products widget ">
		<?php if ( $heading ) : ?>
			<h3 class="widget-title"><span><?php echo esc_html( $heading ); ?></span></h3>
		<?php endif; ?>
		<?php wc_get_template( 'layout-products/carousel.php' , array( 'loop'=>$products,'rows' => '1', 'pagi_type' => 'no', 'nav_type' => 'yes','columns'=>$woocommerce_loop['columns'],'posts_per_page'=>$products->post_count,'screen_desktop'=>$woocommerce_loop['columns'],'screen_desktopsmall'=>'3','screen_tablet'=>'2','screen_mobile'=>'1' ) ); ?>

	</div>

<?php endif;

wp_reset_postdata();