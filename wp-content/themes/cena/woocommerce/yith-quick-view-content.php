<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */


while ( have_posts() ) : the_post(); ?>

	<div class="product">

	<?php if ( !post_password_required() ) { ?>

		<div class="row">
			<div class="col-lg-5 col-md-5 col-sm-5">
				<?php 
					do_action( 'tbay_quick_view_product_image' ); 
				?>
			</div>
			<div class="col-lg-7 col-md-7 col-sm-7">
				<?php
					$summary_class = '';
					if ( intval( cena_tbay_get_config('enable_buy_now', false) ) ) {
			            $summary_class = ' has-buy-now';
			        }
				?>
				<div class="summary entry-summary <?php echo esc_attr($summary_class); ?>">
                    <?php 

                    	if( class_exists( 'TA_WC_Variation_Swatches' ) ) {
	                    	add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'cena_get_swatch_html' , 10, 2 ); 
							add_filter( 'tawcvs_swatch_html', 'cena_swatch_html' , 5, 4 );
						}

                    	do_action( 'yith_wcqv_product_summary' ); 


                    ?>
                </div> 
			</div>
            <?php do_action( 'yith_wcqv_after_product_summary' ); ?>
		</div>

		<?php

	} else {
		echo get_the_password_form();
	}
	?> 

	</div>


<?php endwhile; // end of the loop.

?> 
<?php if( class_exists( 'TA_WC_Variation_Swatches' ) ) : ?>
<script type="text/javascript">
    jQuery(function ($) {
		$( '#yith-quick-view-modal .variations_form' ).tawcvs_variation_swatches_form();
		$( document.body ).trigger( 'tawcvs_initialized' );
    });
</script>
<?php endif; ?>