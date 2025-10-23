<header id="tbay-header" class="site-header header-default header-v1 hidden-sm hidden-xs <?php echo (cena_tbay_get_config('keep_header') ? 'main-sticky-header' : ''); ?>" role="banner">
	 <div id="tbay-topbar" class="tbay-topbar">
        <div class="container">
            <div class="topbar-inner clearfix">
                
				<?php if(is_active_sidebar('top-contact')) : ?>
					<div class="pull-left top-contact">
						<?php dynamic_sidebar('top-contact'); ?> 
					</div><!-- End Top Contact Widget -->
				<?php endif;?>

                <div class="pull-right ">
				
					<?php if ( cena_is_woocommerce_activated() ): ?>
						<div class="pull-right top-cart-wishlist">
							
							<!-- Cart -->
							<div class="pull-right top-cart hidden-xs">
								<?php get_template_part( 'woocommerce/cart/mini-cart-button' ); ?>
							</div>
							
							<div class="pull-right">
								<?php get_template_part( 'page-templates/parts/wishlist' ); ?>
							</div>
						
						</div>
					<?php endif; ?>
					
					<?php get_template_part( 'page-templates/parts/topbar-account' ); ?>
					
                </div>
				
            </div>
        </div> 
    </div> 
    <div class="header-main clearfix">
        <div class="container">
            <div class="header-inner">
                <div class="row">
					<!-- //LOGO -->
                    <div class="logo-in-theme col-md-3 text-center hidden-sm hidden-xs">
                        <?php get_template_part( 'page-templates/parts/logo' ); ?>
                    </div>
					
                    <!-- SEARCH -->
					<?php if ( cena_tbay_get_config('show_searchform') ): ?>
                    <div class="search col-md-6 hidden-sm hidden-xs">
						<?php get_template_part( 'page-templates/parts/productsearchform' ); ?>
                    </div>
					<?php endif; ?>
					
					<!-- Shipping -->
					<?php if(is_active_sidebar('top-shipping')) : ?>
					<div class="top-shipping col-md-3 hidden-sm hidden-xs">
						<?php dynamic_sidebar('top-shipping'); ?>
					</div><!-- End Top shipping Widget -->
					<?php endif;?>
					
                </div>
            </div>
        </div>
    </div>
    <section id="tbay-mainmenu" class="tbay-mainmenu hidden-xs hidden-sm">
        <div class="container"> 

        	<div class="pull-left">
        		<?php get_template_part( 'page-templates/parts/categorymenu' ); ?>
        	</div>

			
			<?php get_template_part( 'page-templates/parts/nav' ); ?>
			
			<!-- Offer -->
			<?php if(is_active_sidebar('top-offer')) : ?>
			<div class="pull-right top-offer visible-lg">
				<?php dynamic_sidebar('top-offer'); ?>
			</div><!-- End Top offer Widget -->
			<?php endif;?>
			
        </div>      
    </section>
</header>