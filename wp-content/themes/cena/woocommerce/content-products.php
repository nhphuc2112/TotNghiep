<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product, $woocommerce_loop;
	
// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

// Ensure visibility
if ( ! $product || ! $product->is_visible() ) {
	return;
}

// Increase loop count

// Extra post classes
$classes = array();

if($woocommerce_loop['columns'] == 5) {
	$columns = 'cus-5';
}else {
	$columns = 12/$woocommerce_loop['columns'];
}


$desktop         	 	=      isset($screen_desktop) ? (12/$screen_desktop) : (12/$woocommerce_loop['columns']);
$desktopsmall          	=      isset($screen_desktopsmall) ? (12/$screen_desktopsmall) : (12/$woocommerce_loop['columns']);
$tablet          		=      isset($screen_tablet) ? (12/$screen_tablet) : (12/$woocommerce_loop['columns']);
$mobile          		=      isset($screen_mobile) ? (12/$screen_mobile) : 6;


$classes[] = 'col-xs-'. $mobile .' col-lg-'.$desktop.' col-md-'.$desktopsmall.' col-sm-'.$tablet. ' '.$class_desktop. ' '.$class_desktopsmall. ' '.$class_tablet. ' '.$class_mobile;

?>
<div <?php wc_product_class( $classes, $product ); ?>>
	<?php $product_item = isset($product_item) ? $product_item : 'inner'; ?>
 	<?php wc_get_template_part( 'item-product/'.$product_item ); ?>
</div>
