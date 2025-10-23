<?php if ( cena_tbay_get_config('show_searchform',1) ): ?>

	<div class="tbay-search-form">
		<form class="form-ajax-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
			<div class="form-group">
				<div class="input-group">
					<?php if ( cena_tbay_get_config('search_type') != 'all' && cena_tbay_get_config('search_category') ): ?>
						<?php 
							wp_enqueue_style('sumoselect'); 
							wp_enqueue_script('jquery-sumoselect');	
						?>
						<div class="select-category input-group-addon">
							<?php if ( cena_tbay_get_config('search_type') == 'product' ):
								$args = array(
								    'show_count' => false,
								    'hierarchical' => true,
								    'show_uncategorized' => 0
								);
							?>
							    <?php wc_product_dropdown_categories( $args ); ?>

							<?php elseif ( cena_tbay_get_config('search_type') == 'post' ):
								$args = array(
									'show_option_all' => esc_html__( 'All categories', 'cena' ),
								    'show_count' => false,
								    'hierarchical' => true,
								    'show_uncategorized' => 0,
								    'name' => 'category',
									'id' => 'search-category',
									'class' => 'postform dropdown_product_cat',
								);
							?>
								<?php wp_dropdown_categories( $args ); ?>
							<?php endif; ?>
					  	</div>
					  	<?php endif; ?>
					  	<div class="button-group input-group-addon">
							<i class="icon-magnifier icons"></i>
						</div>
						<div class="tbay-search-result-wrapper"></div>
				  		<input type="text" placeholder="<?php esc_attr_e( 'I&rsquo;m searching for...', 'cena' ); ?>" name="s" class="tbay-search form-control input-sm"/>
						<div class="tbay-preloader"></div>
					<?php if ( cena_tbay_get_config('search_type') != 'all' ): ?>
						<input type="hidden" name="post_type" value="<?php echo cena_tbay_get_config('search_type'); ?>" class="post_type" />
					<?php endif; ?>
				</div>
				
			</div>
		</form>
	</div>

<?php endif; ?>