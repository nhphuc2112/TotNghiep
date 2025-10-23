<header id="tbay-header" class="site-header header-v10 <?php echo (cena_tbay_get_config('keep_header') ? 'main-sticky-header' : ''); ?>" role="banner">    
    <div class="header-main clearfix hidden-sm hidden-xs">
        <div class="container">
            <div class="header-inner">
				<!-- LOGO -->
				<div class="pull-left logo-in-theme">
					<?php get_template_part( 'page-templates/parts/logo','03' ); ?>
				</div>
			   
				<div class="pull-left">
					<?php get_template_part( 'page-templates/parts/nav' ); ?>
				</div>
			   
				<!-- //LOGO -->
				<div class="pull-right hidden-sm hidden-xs">
					<div class="header-setting">
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
								
								<?php get_template_part( 'page-templates/parts/topbar-account','04' ); ?>
								
							</div>
						<?php endif; ?>

					</div>
				</div>
            </div>
        </div>
    </div>
	
	<div id="tbay-category-image" class="hidden-sm hidden-xs">
		<?php get_template_part( 'page-templates/parts/categorymenuimg' ); ?>
	</div>
 
</header>