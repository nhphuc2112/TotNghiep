<div id="tbay-header-mobile" class="header-mobile hidden-lg hidden-md clearfix">
    <div class="container">
    <div class="row">
        <div class="col-xs-4">
            <?php if ( has_nav_menu( 'primary' ) ) : ?>
                <div class="active-mobile pull-left">
                    <button data-toggle="offcanvas" class="btn btn-sm btn-danger btn-offcanvas btn-toggle-canvas offcanvas" type="button">
                       <i class="fa fa-bars"></i>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ( has_nav_menu( 'topmenu' ) ) { ?>
            <div class="setting-popup pull-left">
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary btn-outline dropdown-toggle" type="button" data-toggle="dropdown"><span class="fa fa-user"></span></button>
                    <div class="dropdown-menu">
                        
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
                        
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="col-xs-4">
            <?php
                $logo = cena_tbay_get_config('media-mobile-logo');
            ?>

            <?php if( isset($logo['url']) && !empty($logo['url']) ): ?>
                <div class="logo">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" >
                        <img src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php bloginfo( 'name' ); ?>">
                    </a>
                </div>
            <?php else: ?>
                <div class="logo logo-theme">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" >
                        <img src="<?php echo esc_url_raw( get_template_directory_uri().'/images/mobile-logo.png'); ?>" alt="<?php bloginfo( 'name' ); ?>">
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-xs-4">
            <div class="topbar-inner">
                <div class="search-popup  pull-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary btn-outline dropdown-toggle" type="button" data-toggle="dropdown"><span class="fa fa-search"></span></button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?php get_search_form(); ?>
                        </div>
                    </div>
                </div>
                <?php if ( cena_is_woocommerce_activated() ): ?>
                    <div class="active-mobile top-cart pull-right">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary btn-outline dropdown-toggle mini-cart" type="button" data-toggle="dropdown"><span class="fa fa-shopping-cart"></span></button>
                            <div class="dropdown-menu">
                                <div class="widget_shopping_cart_content"></div>
                            </div>
                        </div>
                        
                    </div>  
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
</div>