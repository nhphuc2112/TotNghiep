<?php if ( has_nav_menu( 'primary' ) ) : ?>
    <?php 
        $tbay_location = 'primary';
        $locations  = get_nav_menu_locations();
        $menu_id    = $locations[ $tbay_location ] ;
        $menu_obj   = wp_get_nav_menu_object( $menu_id );
        $menu_name  = cena_get_transliterate($menu_obj->slug);
    ?>
        <nav data-duration="400" class="hidden-xs hidden-sm tbay-megamenu slide animate navbar" role="navigation">
        <?php
            $args = array(
                'theme_location' => 'primary',
                'container_class' => 'collapse navbar-collapse',
                'fallback_cb' => '',
                'menu_id' => 'primary-menu',
                'items_wrap'  => '<ul id="%1$s" class="%2$s" data-id="'. $menu_name .'">%3$s</ul>',
            );

	        if( class_exists("Cena_Tbay_Nav_Menu") ){

	            $args['walker'] = new Cena_Tbay_Nav_Menu();
	        } 

            $args['menu_class'] = cena_nav_menu_get_menu_class();

            wp_nav_menu($args);
        ?>
        </nav>
<?php endif; ?>