<?php

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$cat_operator = 'IN';
$product_type = 'deals';

if (isset($categories) && !empty($categories)) {
    $categories = explode(',', $categories);
}
$categories = cena_tbay_get_category_by_id($categories);
$loop       = cena_get_query_products( $categories, $cat_operator, $product_type, $number);

$rows_count = isset($rows) ? $rows : 1;

if($responsive_type == 'yes') {
    $screen_desktop          =      isset($screen_desktop) ? $screen_desktop : 4;
    $screen_desktopsmall     =      isset($screen_desktopsmall) ? $screen_desktopsmall : 3;
    $screen_tablet           =      isset($screen_tablet) ? $screen_tablet : 3;
    $screen_mobile           =      isset($screen_mobile) ? $screen_mobile : 1;
} else {
    $screen_desktop          =      $columns;
    $screen_desktopsmall     =      $columns;
    $screen_tablet           =      $columns;
    $screen_mobile           =      $columns;  
}

wp_enqueue_script( 'owl-carousel' );
                  
$pagi_type  = ($pagi_type == 'yes') ? 'true' : 'false';
$nav_type   = ($nav_type == 'yes') ? 'true' : 'false';
             
?>

<div class="widget_deals_products widget widget_products <?php echo esc_attr($el_class); ?> product-countdown">
   
    <div class="widget-content woocommerce">
        <div class="products-<?php echo esc_attr($layout_type); ?>"> 
        <?php if ($title!=''): ?>
            <h3 class="widget-title">
                <span><?php echo esc_html( $title ); ?></span>
                <?php if ( isset($subtitle) && $subtitle ): ?>
                    <span class="subtitle"><?php echo esc_html($subtitle); ?></span>
                <?php endif; ?>
            </h3>
        <?php endif; ?>
            <?php if ( $loop->have_posts() ): ?>
                

                <?php if ( $layout_type == 'carousel-thumbnail'): ?>

                    <div class="owl-carousel products scroll-init" data-items="<?php echo esc_attr($columns); ?>" data-large="<?php echo esc_attr($screen_desktop);?>" data-medium="<?php echo esc_attr($screen_desktopsmall); ?>" data-smallmedium="<?php echo esc_attr($screen_tablet); ?>" data-extrasmall="<?php echo esc_attr($screen_mobile); ?>" data-carousel="owl" data-pagination="<?php echo esc_attr( $pagi_type ); ?>" data-nav="<?php echo esc_attr( $nav_type ); ?>">

                        <?php $count = 0; while ( $loop->have_posts() ): $loop->the_post(); global $product; ?>


							<?php if($count%$rows_count == 0){ ?>
								<div class="item">
							<?php } ?>
							
                                <div class="products-carousel product">
                                    <?php wc_get_template_part( 'item-product/inner-countdownthumbnail' ); ?>
                                </div>
								
							<?php if($count%$rows_count == $rows_count-1 || $count==$loop->post_count -1){ ?>
								</div>
							<?php }
							$count++; ?>

                        <?php endwhile; ?>
                    	</div> 


                    <?php wp_reset_postdata(); ?>
                <?php elseif ( $layout_type == 'carousel'): ?>

                        <div class="owl-carousel products scroll-init" data-items="<?php echo esc_attr($columns); ?>" data-large="<?php echo esc_attr($screen_desktop);?>" data-medium="<?php echo esc_attr($screen_desktopsmall); ?>" data-smallmedium="<?php echo esc_attr($screen_tablet); ?>" data-extrasmall="<?php echo esc_attr($screen_mobile); ?>" data-carousel="owl" data-pagination="<?php echo esc_attr( $pagi_type ); ?>" data-nav="<?php echo esc_attr( $nav_type ); ?>">

                        <?php $count = 0; while ( $loop->have_posts() ): $loop->the_post(); global $product; ?>


                            <?php if($count%$rows_count == 0){ ?>
                                <div class="item">
                            <?php } ?>
                            
                                <div class="products-carousel product">
                                    <?php wc_get_template_part( 'item-product/inner-countdown' ); ?>
                                </div>
                                
                            <?php if($count%$rows_count == $rows_count-1 || $count==$loop->post_count -1){ ?>
                                </div>
                            <?php }
                            $count++; ?>
                            

                        <?php endwhile; ?>
                        </div> 


                    <?php wp_reset_postdata(); ?>

                <?php elseif ( $layout_type == 'carousel-vertical'): ?>


                        <div class="owl-carousel carousel-vertical products scroll-init" data-items="<?php echo esc_attr($columns); ?>" data-large="<?php echo esc_attr($screen_desktop);?>" data-medium="<?php echo esc_attr($screen_desktopsmall); ?>" data-smallmedium="<?php echo esc_attr($screen_tablet); ?>" data-extrasmall="<?php echo esc_attr($screen_mobile); ?>" data-carousel="owl" data-pagination="<?php echo esc_attr( $pagi_type ); ?>" data-nav="<?php echo esc_attr( $nav_type ); ?>">

                        <?php $count = 0; while ( $loop->have_posts() ): $loop->the_post(); global $product; ?>


							<?php if($count%$rows_count == 0){ ?>
								<div class="item">
							<?php } ?>
							
                                <div class="products-carousel product">
                                    <?php wc_get_template_part( 'item-product/inner-countdown' ); ?>
                                </div>
								
							<?php if($count%$rows_count == $rows_count-1 || $count==$loop->post_count -1){ ?>
								</div>
							<?php }
							$count++; ?>
							

                        <?php endwhile; ?>
                    	</div> 


                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <?php wc_get_template( 'layout-products/'.$layout_type.'.php' , array( 'loop' => $loop, 'columns' => $columns, 'number' => $number, 'product_item' => 'inner-countdown','screen_desktop' => $screen_desktop,'screen_desktopsmall' => $screen_desktopsmall,'screen_tablet' => $screen_tablet,'screen_mobile' => $screen_mobile ) ); ?>
                <?php endif; ?>

            <?php endif; ?>
        </div>
        
    </div>
</div>
