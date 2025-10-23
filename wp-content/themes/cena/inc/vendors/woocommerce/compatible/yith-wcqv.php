<?php

if( !class_exists('YITH_WCQV') ) return;

add_action( 'cena_woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 30 );

add_action( 'tbay_quick_view_product_image', 'woocommerce_show_product_sale_flash', 10 ); 
add_action( 'tbay_quick_view_product_image', 'cena_woo_only_feature_product', 10 ); 

if ( ! function_exists( 'cena_woo_show_product_images' ) ) {
    add_action( 'tbay_quick_view_product_image', 'cena_woo_show_product_images', 20 ); 
	function cena_woo_show_product_images() {
		wc_get_template( 'single-product/quickview-product-image.php' );
	}
}