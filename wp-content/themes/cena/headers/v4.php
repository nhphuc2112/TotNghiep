<header id="tbay-header" class="site-header header-v4 <?php echo (cena_tbay_get_config('keep_header') ? 'main-sticky-header' : ''); ?>" role="banner">
	<?php if ( is_active_sidebar( 'top-slider' ) ) : ?>
	<div class="top-slider  hidden-sm hidden-xs">
		<?php dynamic_sidebar( 'top-slider' ); ?>
	</div>
	<?php endif; ?>
    
    <div class="header-main  hidden-sm hidden-xs clearfix">
        <div class="container">
            <div class="header-inner">
				<!-- LOGO -->
				<div class="pull-left logo-in-theme">
					<?php get_template_part( 'page-templates/parts/logo' ); ?>
				</div>
			   
				<?php get_template_part( 'page-templates/parts/nav', '04' ); ?>
			   
				<!-- //LOGO -->
				<div class="pull-right hidden-sm hidden-xs">
					<div class="header-setting">

						<?php get_template_part( 'page-templates/parts/topmenu' ); ?>

						<?php if ( cena_is_woocommerce_activated() ): ?>
							<div class="pull-right top-cart-wishlist">
								<div class="pull-right">
									<!-- Setting -->
									<div class="top-cart hidden-xs">
										<?php get_template_part( 'woocommerce/cart/mini-cart-button' ); ?>
									</div>
								</div>						
							
							<div class="pull-right">
								<?php get_template_part( 'page-templates/parts/wishlist' ); ?>
							</div>

							</div>
						<?php endif; ?>
						
						<?php get_template_part( 'page-templates/parts/topbar-account','04' ); ?>
						
						<div class="pull-right">
						   <?php get_template_part( 'page-templates/parts/search-modal' ); ?>
						</div>

					</div>
				</div>
            </div>
        </div>
    </div>
 
</header>