<ul class="pull-right list-inline acount">
	<?php if( !is_user_logged_in() ){ ?>
		<li><i class="icon-login icons"></i> <a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>" title="<?php esc_attr_e('Sign up','cena'); ?>"> <?php esc_html_e('Sign up', 'cena'); ?> </a></li>
		<li> <a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>" title="<?php esc_attr_e('Login','cena'); ?>"> <?php esc_html_e('Login', 'cena'); ?> </a></li>
	<?php }else{ ?>
		<?php $current_user = wp_get_current_user(); ?>
	  <li><a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>" title="<?php esc_attr_e('My account','cena'); ?>"><i class="icon-login icons"></i> <span class="hidden-xs"><?php esc_html_e('Welcome ','cena'); ?><?php echo esc_html( $current_user->display_name); ?> !</span></a></li>
	  <li><a href="<?php echo wp_logout_url(home_url()); ?>"><?php esc_html_e('Logout ','cena'); ?></a></li>
	<?php } ?>
</ul>