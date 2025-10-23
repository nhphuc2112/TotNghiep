<header id="tbay-header" class="site-header header-v7 <?php echo (cena_tbay_get_config('keep_header') ? 'main-sticky-header' : ''); ?>" role="banner">
	<div id="tbay-topbar" class="tbay-topbar hidden-sm hidden-xs">
        <div class="container">
	
            <div class="topbar-inner clearfix">
                <div class="row">
					<?php if(is_active_sidebar('top-contact')) : ?>
						<div class="col-md-5 top-contact">
							<?php dynamic_sidebar('top-contact'); ?>
						</div><!-- End Top Contact Widget -->
					<?php endif;?>

					<!-- Shipping -->
					<?php if(is_active_sidebar('top-shipping-2')) : ?>
					<div class="top-shipping2 col-md-4 hidden-sm hidden-xs">
						<?php dynamic_sidebar('top-shipping-2'); ?>
					</div><!-- End Top shipping Widget -->
					<?php endif;?>
					
					<div class="col-md-3 text-right ">

						<?php get_template_part( 'page-templates/parts/topbar-account' ); ?>

					</div>
				</div>
				
            </div>
        </div> 
    </div>
	
	<div class="header-main clearfix hidden-sm hidden-xs">
        <div class="container">
            <div class="header-inner clearfix">
                <!-- LOGO -->
                <div class="logo-in-theme pull-left">
					<?php get_template_part( 'page-templates/parts/logo','03' ); ?>
                </div>
				
				<!-- Main menu -->
				<div class="tbay-mainmenu pull-left">
					<?php get_template_part( 'page-templates/parts/nav' ); ?>
                </div>
				
                <!-- //Cart -->
                <div class="pull-right hidden-sm hidden-xs header-setting">
                    <div class="header-setting">
                        <?php if ( cena_is_woocommerce_activated() ): ?>
							<div class="top-cart-wishlist pull-right">
								<?php if ( cena_is_woocommerce_activated() ): ?>
									<!-- Cart  -->
									<div class="pull-right top-cart hidden-xs">
										<?php get_template_part( 'woocommerce/cart/mini-cart-button' ); ?>
									</div>
								<?php endif; ?>
								
								<div class="pull-right">
									<?php get_template_part( 'page-templates/parts/wishlist' ); ?>
								</div>

							</div>
						<?php endif; ?>
							
						
                        <div class=" pull-right">
                            <?php get_template_part( 'page-templates/parts/search-modal' ); ?>                
                        </div>
                    </div>
                </div>
				
            </div>
        </div>
    </div>
</header>