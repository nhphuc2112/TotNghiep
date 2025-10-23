<?php

get_header();
$sidebar_configs = cena_tbay_get_woocommerce_layout_configs();

$page_title = '';
if( is_shop()){
	$page_title .= esc_html__('Shop', 'cena');
}else if( is_singular( 'product' ) ) {
	$page_title .= get_the_title();
} else {
	$page_title .= woocommerce_page_title(false);
}

if ( isset($sidebar_configs['left']) && !isset($sidebar_configs['right']) ) {
	$sidebar_configs['main']['class'] .= ' pull-right';
}

?>

<?php do_action( 'cena_woo_template_main_before' ); ?>

<section id="main-container" class="main-content <?php echo apply_filters('cena_tbay_woocommerce_content_class', 'container');?>">
	<div class="row">
		
		<?php if ( isset($sidebar_configs['left']) && isset($sidebar_configs['right']) ) : ?>
			<div class="<?php echo esc_attr($sidebar_configs['left']['class']) ;?>">
			  	<aside class="sidebar sidebar-left" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
			   		<?php dynamic_sidebar( $sidebar_configs['left']['sidebar'] ); ?>
			  	</aside>
			</div>
		<?php endif; ?>

		<div id="main-content" class="archive-shop col-xs-12 <?php echo esc_attr($sidebar_configs['main']['class']); ?>">

			<div id="primary" class="content-area">
				<div id="content" class="site-content" role="main">

					<?php  
				 if ( is_singular( 'product' ) ) {

		            while ( have_posts() ) : the_post();

		                wc_get_template_part( 'content', 'single-product' );

		            endwhile;

		        } else { ?>
		            <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

		                <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

		            <?php endif; ?>


		            <?php  do_action( 'woocommerce_archive_description' ); ?>


		            


					<?php $display_type = woocommerce_get_loop_display_mode();
					if('subcategories' === $display_type || 'both' === $display_type) : ?>
					
						<ul class="all-subcategories row">
							<?php 			
								woocommerce_output_product_categories(
									array(
										'parent_id' => is_product_category() ? get_queried_object_id() : 0,
									)
								); 
							?>
						</ul>					
					
					<?php endif; ?>
					
		            <?php if ( woocommerce_product_loop() ) : ?>


						<?php if((is_shop() && 'subcategories' !== get_option('woocommerce_shop_page_display')) || is_woocommerce() || ((is_product_category() || is_product_tag() ) && 'subcategories' !== get_option('woocommerce_category_archive_display')) ): ?>	

							<?php do_action('woocommerce_before_shop_loop'); ?>

						<?php endif; ?>

		                <?php woocommerce_product_loop_start(); ?>

		                   
		                    <?php while ( have_posts() ) : the_post(); ?>
		                    	
		                        <?php wc_get_template_part( 'content', 'product' ); ?>

		                    <?php endwhile; // end of the loop. ?>

		                <?php woocommerce_product_loop_end(); ?>



						<?php if( (is_shop() && 'subcategories' !== get_option('woocommerce_shop_page_display')) ||( (is_product_category() || is_product_tag() ) && 'subcategories' !== get_option('woocommerce_category_archive_display')) || ((is_product_category() || is_product_tag() ) && 'subcategories' !== get_option('woocommerce_category_archive_display')) || is_tax('yith_product_brand') ): ?>

		               		<?php do_action('woocommerce_after_shop_loop'); ?>

		               	<?php endif; ?>

		            <?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

		                <?php wc_get_template( 'loop/no-products-found.php' ); ?>

		            <?php endif;
		        }
				?>

				</div><!-- #content -->
			</div><!-- #primary -->
		</div><!-- #main-content -->
		
		<?php if ( isset($sidebar_configs['left']) && !isset($sidebar_configs['right']) ) : ?>
			<div class="<?php echo esc_attr($sidebar_configs['left']['class']) ;?>">
				<?php do_action( 'cena_after_sidebar_mobile' ); ?>
			  	<aside class="sidebar sidebar-left" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
			   		<?php dynamic_sidebar( $sidebar_configs['left']['sidebar'] ); ?>
			  	</aside>
			</div>
		<?php endif; ?>
		
		<?php if ( isset($sidebar_configs['right']) && !isset($sidebar_configs['left']) ) : ?>
			<div class="<?php echo esc_attr($sidebar_configs['right']['class']) ;?>">
				<?php do_action( 'cena_after_sidebar_mobile' ); ?>
			  	<aside class="sidebar sidebar-right" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
			   		<?php dynamic_sidebar( $sidebar_configs['right']['sidebar'] ); ?>
			  	</aside>
			</div>

		<?php elseif( isset($sidebar_configs['right'])  ) : ?>
			<div class="<?php echo esc_attr($sidebar_configs['right']['class']) ;?>">
			  	<aside class="sidebar sidebar-right" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
			   		<?php dynamic_sidebar( $sidebar_configs['right']['sidebar'] ); ?>
			  	</aside>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php if ( is_singular( 'product' ) ) : ?>
 <?php do_action( 'cena_woo_singular_template_main_after' ); ?>
<?php endif; ?>

<?php

get_footer();
