<?php 
global $product;
$image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id($product->get_id() ), 'blog-thumbnails' );

?>
<div class="product-block list" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
	<div class="row">
		<div class="col-lg-4 col-md-4">
		    <figure class="image">
		        <?php woocommerce_show_product_loop_sale_flash(); ?>
		        <a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" class="product-image">
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

		        <?php (class_exists( 'YITH_WCBR' )) ? cena_brands_get_name($product->get_id()): ''; ?>
				
				
		
		    </figure>
		</div>    
	    <div class="col-lg-8 col-md-8">
		    <div class="caption-list">
		        
		        <div class="meta">

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
		            <?php echo  the_excerpt();  ?>
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
								<div class="quick-view">
									<a href="#" class="button yith-wcqv-button tbay-tooltip" data-toggle="tooltip" title="<?php esc_attr_e('Quick View', 'cena'); ?>" data-product_id="<?php echo esc_attr($product->get_id()); ?>">
										<span>
											<i class="zmdi zmdi-eye"></i>
										</span>
									</a>
								</div>
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
