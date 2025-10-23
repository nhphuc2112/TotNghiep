<?php   global $woocommerce; ?>
<div class="tbay-topcart">
 <div id="cart" class="dropdown">
        
        <a class="dropdown-toggle mini-cart" data-toggle="dropdown" aria-expanded="true" role="button" aria-haspopup="true" data-delay="0" href="#" title="<?php esc_attr_e('View your shopping cart', 'cena'); ?>">
            <span class="text-skin cart-icon">
                <i class="icon-basket icons"></i>
            </span>
			<span class="sub-title"><?php echo esc_html__('Cart', 'cena'); ?> : <?php echo WC()->cart->get_cart_subtotal();?></span>
            <span class="mini-cart-items">
            	   <?php echo sprintf( '%d', $woocommerce->cart->cart_contents_count );?>
                </span>
        </a>            
        <div class="dropdown-menu"><div class="widget_shopping_cart_content">
            <?php woocommerce_mini_cart(); ?>
        </div></div>
    </div>
</div>    