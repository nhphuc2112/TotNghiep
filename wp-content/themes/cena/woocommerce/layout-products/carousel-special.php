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
$nav_type   = ($nav_type == 'yes') ? 'true' : 'false';;
?>
<div class="owl-carousel products scroll-init" data-items="<?php echo esc_attr($columns); ?>" data-large="<?php echo esc_attr($screen_desktop);?>" data-medium="<?php echo esc_attr($screen_desktopsmall); ?>" data-smallmedium="<?php echo esc_attr($screen_tablet); ?>" data-extrasmall="<?php echo esc_attr($screen_mobile); ?>" data-carousel="owl" data-pagination="<?php echo esc_attr( $pagi_type ); ?>" data-nav="<?php echo esc_attr( $nav_type ); ?>">
    <?php $count = 0; while ( $loop->have_posts() ): $loop->the_post(); global $product; ?>
	
			<?php if($count%$rows_count == 0){ ?>
				<div class="item">
			<?php } ?>
	
        
            <div class="product-block grid  products-grid carousel-special product">
                <div class="row">
						<div class="block-inner col-lg-5 col-md-5 col-sm-5">
							<figure class="image">
								<?php woocommerce_show_product_loop_sale_flash(); ?>
								<a title="<?php the_title_attribute(); ?>" href="<?php echo (get_option( 'woocommerce_enable_lightbox' )=='yes' && is_product()) ? $image_attributes[0] : the_permalink(); ?>" class="product-image <?php echo (get_option( 'woocommerce_enable_lightbox' )=='yes' &&  is_product())?'zoom':'zoom-2' ;?>">
									<?php
										/**
										* woocommerce_before_shop_loop_item_title hook
										*
										* @hooked woocommerce_show_product_loop_sale_flash - 10
										* @hooked woocommerce_template_loop_product_thumbnail - 10
										*/
										remove_action('woocommerce_before_shop_loop_item_title','woocommerce_show_product_loop_sale_flash', 10);
										do_action( 'woocommerce_before_shop_loop_item_title' );
									?>
								</a>

							</figure>
					
						</div>
						<div class="caption col-lg-7 col-md-7 col-sm-7">
							<div class="meta">
								<div class="infor">
									<?php (class_exists( 'YITH_WCBR' )) ? cena_brands_get_name($product->get_id()): ''; ?>
									<h3 class="name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<?php
										/**
										* woocommerce_after_shop_loop_item_title hook
										*
										* @hooked woocommerce_template_loop_rating - 5
										* @hooked woocommerce_template_loop_price - 10
										*/
										do_action( 'woocommerce_after_shop_loop_item_title');

									?>
									
									<div class="groups-button">
										<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
										<?php
											$action_add = 'yith-woocompare-add-product';
											$url_args = array(
												'action' => $action_add,
												'id' => $product->get_id()
											);
										?>
										<?php if (class_exists('YITH_WCQV_Frontend')) { ?>
											<a href="#" class="button yith-wcqv-button tbay-tooltip" data-toggle="tooltip" title="<?php esc_attr_e('Quick View', 'cena'); ?>" data-product_id="<?php echo esc_attr($product->get_id()); ?>">
												<span>
													<i class="zmdi zmdi-eye"></i>
												</span>
											</a>
										<?php } ?>
										<?php
					                        $enabled_on_loop = 'yes' == get_option( 'yith_wcwl_show_on_loop', 'no' );
					                            if( class_exists( 'YITH_WCWL' ) || $enabled_on_loop ) {
					                            echo do_shortcode( '[yith_wcwl_add_to_wishlist]' );
					                        }
					                    ?>    
								
										<?php if( class_exists( 'YITH_Woocompare' ) ) { ?>
											<?php
												$action_add = 'yith-woocompare-add-product';
												$url_args = array(
													'action' => $action_add,
													'id' => $product->get_id()
												);
											?>
											<div class="yith-compare">
					                            <a href="<?php echo wp_nonce_url( add_query_arg( $url_args ), $action_add ); ?>" data-toggle="tooltip" title="<?php esc_attr_e('Compare', 'cena'); ?>" class="compare tbay-tooltip" data-product_id="<?php echo esc_attr($product->get_id()); ?>">
					                                <i class="zmdi zmdi-refresh-alt"></i>
					                            </a>
											</div>
										<?php } ?> 
									</div>
								</div>
							</div>    
						</div>    
				    </div>
            </div>
		
			<?php if($count%$rows_count == $rows_count-1 || $count==$loop->post_count -1){ ?>
				</div>
			<?php }
			$count++; ?>
		
    <?php endwhile; ?>
</div> 
<?php wp_reset_postdata(); ?>