<header id="tbay-header" class="site-header header-v9 <?php echo (cena_tbay_get_config('keep_header') ? 'main-sticky-header' : ''); ?>" role="banner">
	<div class="header-main clearfix hidden-sm hidden-xs">
        <div class="container">
            <div class="header-inner">
                <div class="row">
					<!-- //LOGO -->
                    <div class="logo-in-theme col-md-3 text-center">
                        <?php get_template_part( 'page-templates/parts/logo' ); ?>
                    </div>
					
                    <!-- Categories -->
                    <div class="col-md-6 hidden-sm hidden-xs">
						<?php get_template_part( 'page-templates/parts/categorymenuimg' ); ?>
                    </div>
					
					<div class="top-right col-md-3 hidden-sm hidden-xs">
						<?php get_template_part( 'page-templates/parts/topbar-account-09' ); ?>
						<?php dynamic_sidebar('top-phone'); ?>
					</div>
					
                </div>
            </div>
        </div>
    </div>
	<section id="tbay-search" class="tbay-mainmenu hidden-xs hidden-sm">
		 <div class="container"> 
			<?php get_template_part( 'page-templates/parts/productsearchform' ); ?>
		 </div>
	</section>
    <section id="tbay-mainmenu" class="tbay-mainmenu hidden-xs hidden-sm">
        <div class="container"> 

        	<div class="pull-left">
        		<?php get_template_part( 'page-templates/parts/categorymenu' ); ?>
        	</div>

			
			<div class="pull-left">
				<?php get_template_part( 'page-templates/parts/nav' ); ?>
			</div>
			
			<!-- Cart -->
			<div class="pull-right">
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
			</div><!-- End Top Cart -->
			
        </div>      
    </section>
</header>