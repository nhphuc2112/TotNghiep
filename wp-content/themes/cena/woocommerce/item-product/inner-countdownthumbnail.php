<?php 
global $product;

wp_enqueue_script( 'jquery-countdowntimer' ); 

$image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id($product->get_id() ), 'blog-thumbnails' );
$time_sale = get_post_meta( $product->get_id(), '_sale_price_dates_to', true );
?>
   <div class="product-block style-horizontal grid clearfix" data-product-id="<?php echo esc_attr($product->get_id()); ?>">

        <?php 

            echo apply_filters( 'woocommerce_get_saved_sales_price_html', $product->get_id() );

        ?>

        <div class="block-inner">
            <figure class="image image-mains">
			
                <?php 

                    remove_action('woocommerce_before_single_product_summary','woocommerce_show_product_sale_flash', 10);

                    do_action( 'woocommerce_before_single_product_summary' ); 

                ?>

                 <?php (class_exists( 'YITH_WCBR' )) ? cena_brands_get_name($product->get_id()): ''; ?>

            </figure>



        </div>
        <div class="caption">
            <div class="meta">
                <div class="infor">
                    <h3 class="name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php
                        /**
                        * woocommerce_after_shop_loop_item_title hook
                        *
                        * @hooked woocommerce_template_loop_rating - 5
                        * @hooked woocommerce_template_loop_price - 10
                        */
                        //remove_action('woocommerce_after_shop_loop_item_title','woocommerce_template_loop_rating',5);
						add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
                        do_action( 'woocommerce_after_shop_loop_item_title');

                    ?>
                    <div class="description">

                        <?php echo cena_tbay_substring( get_the_excerpt(), 24, '...' ); ?>
                        
                    </div>
                    
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
            <div class="time">
                <?php if ( $time_sale ): ?>
                    <div class="tbay-countdown" data-time="timmer"
                         data-date="<?php echo gmdate('m', $time_sale).'-'.gmdate('d', $time_sale).'-'.gmdate('Y', $time_sale).'-'. gmdate('H', $time_sale) . '-' . gmdate('i', $time_sale) . '-' .  gmdate('s', $time_sale) ; ?>">
                    </div>
                <?php endif; ?> 
            </div>   
        </div>
    </div> 
