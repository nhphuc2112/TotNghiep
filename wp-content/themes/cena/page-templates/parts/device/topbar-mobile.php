<div class="topbar-device-mobile  visible-xxs clearfix">
	<?php
		$mobilelogo = cena_tbay_get_config('mobile-logo');
	?>
	<?php if( cena_tbay_is_home_page() ) : ?>
        <div class="active-mobile">
            <?php echo apply_filters( 'cena_get_menu_mobile_icon', 10,2 ); ?>
        </div>
		<div class="mobile-logo">
			<?php if( isset($mobilelogo['url']) && !empty($mobilelogo['url']) ): ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img class="logo-mobile-img" src="<?php echo esc_url( $mobilelogo['url'] ); ?>" alt="<?php bloginfo( 'name' ); ?>">
				</a>
			<?php else: ?>
				<div class="logo-theme">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img class="logo-mobile-img" src="<?php echo esc_url_raw( get_template_directory_uri().'/images/mobile-logo.png'); ?>" alt="<?php bloginfo( 'name' ); ?>">
					</a>
				</div>
			<?php endif; ?>
		</div>
		<div class="search-device">
			<?php get_template_part( 'page-templates/parts/productsearchform-mobile' ); ?>
		</div>
		<?php if ( cena_is_woocommerce_activated() ): ?>
			<div class="device-cart">
				<a class="mobil-view-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" >
					<i class="icon-basket icons"></i>
					<?php   global $woocommerce; ?>
					<span class="mini-cart-items cart-mobile"><?php echo sprintf( '%d', $woocommerce->cart->get_cart_contents_count() );?></span>
				</a>   
			</div>
		<?php endif; ?>

	<?php else: ?>

	<div class="topbar-post">
		<div class="topbar-back">
			<a href = "javascript:history.back()"><i class="icon-action-undo icons"></i></a>
		</div>

		<div class="mobile-logo">
			<?php if( isset($mobilelogo['url']) && !empty($mobilelogo['url']) ): ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( $mobilelogo['url'] ); ?>" alt="<?php bloginfo( 'name' ); ?>">
				</a>
			<?php else: ?>
				<div class="logo-theme">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img src="<?php echo esc_url_raw( get_template_directory_uri().'/images/mobile-logo.png'); ?>" alt="<?php bloginfo( 'name' ); ?>">
					</a>
				</div>
			<?php endif; ?>
		</div>
 
		<div class="topbar-title">
			<?php $title = apply_filters( 'cena_get_filter_title_mobile', 10,1 ); ?>
			<?php echo trim($title);?> 
		</div>


		<div class="active-mobile">
			<?php echo apply_filters( 'cena_get_menu_mobile_icon', 10,2 ); ?>
		</div>

		</div>
	<?php endif; ?>

</div>
