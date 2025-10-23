<?php
$product_item = isset($product_item) ? $product_item : 'inner';
$columns = isset($columns) ? $columns : 4;
$rows_count = isset($rows) ? $rows : 1;


$screen_desktop          =      isset($screen_desktop) ? $screen_desktop : 4;
$screen_desktopsmall     =      isset($screen_desktopsmall) ? $screen_desktopsmall : 3;
$screen_tablet           =      isset($screen_tablet) ? $screen_tablet : 3;
$screen_mobile           =      isset($screen_mobile) ? $screen_mobile : 1;

wp_enqueue_script( 'owl-carousel' );

$pagi_type  = ($pagi_type == 'yes') ? 'true' : 'false';
$nav_type   = ($nav_type == 'yes') ? 'true' : 'false';

$classes = array('products-grid', 'product');
?>
<div class="owl-carousel products" data-items="<?php echo esc_attr($columns); ?>" data-large="<?php echo esc_attr($screen_desktop);?>" data-medium="<?php echo esc_attr($screen_desktopsmall); ?>" data-smallmedium="<?php echo esc_attr($screen_tablet); ?>" data-extrasmall="<?php echo esc_attr($screen_mobile); ?>" data-carousel="owl" data-pagination="<?php echo esc_attr( $pagi_type ); ?>" data-nav="<?php echo esc_attr( $nav_type ); ?>">
    <?php $count = 0; while ( $loop->have_posts() ): $loop->the_post(); global $product; ?>
	
			<?php if($count%$rows_count == 0){ ?>
				<div class="item">
			<?php } ?>
	
        
            <div <?php wc_product_class( $classes, $loop->get_id() ); ?>>
                <?php 
					$post_object = get_post( get_the_ID() );
					setup_postdata( $GLOBALS['post'] =& $post_object );
					
					wc_get_template_part( 'item-product/'.$product_item ); 
				?>
            </div>
		
			<?php if($count%$rows_count == $rows_count-1 || $count==$loop->post_count -1){ ?>
				</div>
			<?php }
			$count++; ?>
		
    <?php endwhile; ?>
</div> 
<?php wp_reset_postdata(); ?>