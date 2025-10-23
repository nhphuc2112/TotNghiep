<?php if ( has_nav_menu( 'category-menu-image' ) ): ?>
	<nav class="tbay-category-menu-image" role="navigation">
		<?php   $args = array(
				'theme_location' => 'category-menu-image',
				'container_class' => 'collapse navbar-collapse',
				'menu_class' => 'nav navbar-nav megamenu',
				'fallback_cb' => '',
				'menu_id' => 'category-menu-image',
				'walker' => new Cena_Tbay_Nav_Menu()
			);
			wp_nav_menu($args);
		?>
	</nav>
<?php endif;?>