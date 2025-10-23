<header id="tbay-header" class="site-header header-v3 <?php echo (cena_tbay_get_config('keep_header') ? 'main-sticky-header' : ''); ?>" role="banner">
    <div class="header-main hidden-sm hidden-xs clearfix">
        <div class="container">
            <div class="header-inner">
                <div class="row">
                    <!-- LOGO -->
                    <div class="logo-in-theme col-md-3 hidden-sm hidden-xs">
                        <?php get_template_part( 'page-templates/parts/logo','03' ); ?>
                    </div>
                   
                    <!-- Shipping -->
					<?php if(is_active_sidebar('top-shipping-3')) : ?>
                    <div class="top-shipping3 col-md-4 hidden-sm hidden-xs">
							<?php dynamic_sidebar('top-shipping-3'); ?>
                    </div>
					<?php endif;?>

					<div class="col-md-5 hidden-sm hidden-xs">
						<div class="search">
							<?php get_template_part( 'page-templates/parts/productsearchform' ); ?>
						</div>	
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section id="tbay-mainmenu" class="tbay-mainmenu hidden-xs hidden-sm">
        <div class="container"> 

			<div class="pull-left">
				<?php get_template_part( 'page-templates/parts/nav' ); ?>
			</div>
			
			<div class="pull-right">
				
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
				
				<?php get_template_part( 'page-templates/parts/topbar-account' ); ?>
				
				
			</div>
   
        </div>      
    </section>
</header>