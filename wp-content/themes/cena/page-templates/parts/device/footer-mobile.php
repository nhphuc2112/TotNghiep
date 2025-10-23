<?php   global $woocommerce; ?>
<div class="footer-device-mobile visible-xxs clearfix">
    <?php //var_dump(is_front_page()); ?>
    <div class="device-home <?php echo is_front_page() ? 'active' : '' ?> ">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" >
            <i class="icon-home icons"></i>
            <?php esc_html_e('Home','cena'); ?>
        </a>   
    </div>	

        <div class="device-cart">
            <a class="mobil-view-cart" href="/dat-lich-sua-chua" >
                <span class="icon">
					<i class="fa fa-wrench"></i>
                    <?php esc_html_e('Đặt lịch','cena'); ?>
                </span>
            </a>   
        </div>
    <?php if ( cena_is_woocommerce_activated() ): ?>
        <div class="device-cart <?php echo is_cart() ? 'active' : '' ?>">
            <a class="mobil-view-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" >
                <span class="icon">
                    <i class="icon-basket icons"></i>
                    <span class="count mini-cart-items cart-mobile"><?php echo sprintf( '%d', $woocommerce->cart->get_cart_contents_count() );?></span>
                    <?php esc_html_e('View Cart','cena'); ?>
                </span>
            </a>   
        </div>
    <?php endif; ?>
    <?php if ( cena_is_woocommerce_activated() ): ?>
    <div class="device-account <?php echo is_account_page() ? 'active' : '' ?>">
        <a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>" title="<?php esc_attr_e('Login','cena'); ?>">
            <i class="icon-user icons"></i>
            <?php esc_html_e('Account','cena'); ?>
        </a>
    </div>
    <?php endif; ?>

</div>
