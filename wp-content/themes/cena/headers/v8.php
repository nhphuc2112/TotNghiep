<header id="tbay-header" class="site-header header-v8 <?php echo (cena_tbay_get_config('keep_header') ? 'main-sticky-header' : ''); ?>" role="banner">
	<div class="tbay-topbar topbar-mobile fixed hidden-sm hidden-xs">
        <div class="top active-mobile pull-left">
			<div class="top active-mobile pull-left">
				<button data-toggle="offcanvas" class="btn btn-sm btn-danger btn-offcanvas btn-toggle-canvas offcanvas" type="button">
				   +
				</button>
			</div>
		</div>
		
		<div class="middle">
			
			<div class="top-cart-wishlist">

				<div class="block">

					<div id="search-form-modal">
						<div class="search-form">
							<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#searchformshow">
							  <i class="zmdi zmdi-search"></i>
							</button>
						</div>

					</div>    

				</div>

						<?php if ( cena_is_woocommerce_activated() ): ?>
						<div class="block">
							<?php get_template_part( 'page-templates/parts/wishlist' ); ?>
						</div>
						<?php endif; ?>

						<?php if ( cena_is_woocommerce_activated() ): ?>
							<div class="block">
								<!-- Cart  -->
								<div class="top-cart hidden-xs">
									<?php get_template_part( 'woocommerce/cart/mini-cart-button' ); ?>
								</div>
							</div>
						<?php endif; ?>
				

			</div>
		</div>
		
		<div class="footer">

			<?php get_template_part( 'page-templates/parts/topbar-account' ); ?>

		</div>
    </div>
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
   
   	
	<div class="modal fade" id="searchformshow" tabindex="-1" role="dialog" aria-labelledby="searchformlable">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="searchformlable"><?php esc_html_e('Products search form', 'cena'); ?></h4>
	      </div>
	      <div class="modal-body">
				<?php get_template_part( 'page-templates/parts/productsearchform' ); ?>
	      </div>
	    </div>
	  </div>
	</div>

</header>