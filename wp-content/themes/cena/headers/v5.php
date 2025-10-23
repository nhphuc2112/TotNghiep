<header id="tbay-header" class="site-header header-v5 <?php echo (cena_tbay_get_config('keep_header') ? 'main-sticky-header' : ''); ?>" role="banner">

    <div class="header-main clearfix hidden-sm hidden-xs">
        <div class="container">
            <div class="header-inner">
                <div class="row">
					<!-- //LOGO -->
                    <div class="logo-in-theme col-lg-3 text-center col-md-3">
						<?php get_template_part( 'page-templates/parts/logo','03' ); ?>
                    </div>
					
					<div class="main-menu col-lg-6 col-md-9">
						
						<?php get_template_part( 'page-templates/parts/nav' ); ?>

					</div>
					
					<!-- Shipping -->
					<?php if(is_active_sidebar('top-shipping')) : ?>
					<div class="top-shipping col-lg-3 visible-lg">
						<?php dynamic_sidebar('top-shipping'); ?>
					</div><!-- End Top shipping Widget -->
					<?php endif;?>
					
                </div>
            </div>
        </div>
    </div>
    <section id="tbay-mainmenu" class="tbay-mainmenu hidden-xs hidden-sm">
        <div class="container">
			<div class="row">

				<div class="col-md-3">
					<?php get_template_part( 'page-templates/parts/categorymenu' ); ?>
				</div>


				
				<div class="col-md-6">
					<?php get_template_part( 'page-templates/parts/productsearchform' ); ?>
				</div>
				
				<div class="col-md-3 ">
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
    </section>
</header>