<div class="topbar-mobile hidden-lg hidden-md hidden-xxs container clearfix">

    <!-- //LOGO -->
    <div class="logo-mobile-theme col-xs-6 text-left">
        <?php get_template_part( 'page-templates/parts/logo' ); ?>
    </div>

     <div class="topbar-mobile-right col-xs-6 text-right">
	 
        <div class="active-mobile">
            <?php echo apply_filters( 'cena_get_menu_mobile_icon', 10,2 ); ?>
        </div>

		
        <div class="topbar-inner">

            <div class="search-popup">
                <?php get_template_part( 'page-templates/parts/search-modal' ); ?>
            </div>
            
            <div class="setting-popup">

                <div class="dropdown">
                    <button class="btn btn-sm btn-primary btn-outline dropdown-toggle" type="button" data-toggle="dropdown"><span class="fa fa-user"></span></button>
                    <div class="dropdown-menu">
                        <?php if ( has_nav_menu( 'topmenu' ) ) { ?>
                            <div class="pull-left">
                                <?php
                                    $args = array(
                                        'theme_location'  => 'topmenu',
                                        'container_class' => '',
                                        'menu_class'      => 'menu-topbar'
                                    );
                                    wp_nav_menu($args);
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

            </div>
            <div class="active-mobile top-cart">

                <div class="dropdown">
                    <button class="btn btn-sm btn-primary btn-outline dropdown-toggle" type="button" data-toggle="dropdown"><span class="fa fa-shopping-cart"></span></button>
                    <div class="dropdown-menu">
                        <div class="widget_shopping_cart_content"></div>
                    </div>
                </div>
                
            </div>  
        </div>
    </div>       
</div>
