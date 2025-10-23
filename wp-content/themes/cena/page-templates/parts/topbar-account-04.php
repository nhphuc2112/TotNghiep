<div class="pull-right user-menu">
	<div class="dropdown menu">
		<?php if( !is_user_logged_in() ){ ?>
			<a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>" title="<?php esc_attr_e('Login','cena'); ?>"><i class="icon-user-unfollow icons"></i> </a>
		<?php }else{ ?>
			<?php $current_user = wp_get_current_user(); ?>
		  	<a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>" title="<?php esc_attr_e('Log Out','cena'); ?>"><i class="icon-user icons"></i></a>
		<?php } ?>
	</div>
</div>