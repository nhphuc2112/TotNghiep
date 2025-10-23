<?php

$align = $title_position  = $nav_type = $pagi_type = '';
$rows = 1;
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

if ( $producttabs == '' ) return;

if (isset($categories) && !empty($categories)) {
    $categories = explode(',', $categories);
}


$_id = cena_tbay_random_key();
$__count = 1;

$list_query = $this->getListQuery( $atts );

if($responsive_type == 'yes') {
    $screen_desktop          =      isset($screen_desktop) ? $screen_desktop : 4;
    $screen_desktopsmall     =      isset($screen_desktopsmall) ? $screen_desktopsmall : 3;
    $screen_tablet           =      isset($screen_tablet) ? $screen_tablet : 3;
    $screen_mobile           =      isset($screen_mobile) ? $screen_mobile : 1;
} else {
    $screen_desktop          =     $columns;
    $screen_desktopsmall     =      $columns;
    $screen_tablet           =      $columns;
    $screen_mobile           =      $columns;  
}
$cat_operator = 'IN';


if( $ajax_tabs === 'yes' ) { 
    $el_class           .= ' tbay-product-tabs-ajax ajax-active';

	if ( isset($categories) && !empty($categories) ) {
		$category_ajax 		= cena_tbay_get_category_by_id($categories);
	} elseif (isset($categories) && !empty($categories)) {
		$category_ajax 	= get_term_by('id', $categories, 'product_cat')->slug;
	} else {
		$category_ajax 	= '';
	}

    $responsive = array(
        'screen_desktop'       => $screen_desktop,
        'screen_desktopsmall'  => $screen_desktopsmall,
        'screen_tablet'        => $screen_tablet,
        'screen_mobile'        => $screen_mobile,
    );

    $data_carousel = array(
        'nav_type'          => $nav_type,
        'pagi_type'         => $pagi_type,
        'rows'              => $rows,
    );
 
    $json = array(
        'cat_operator'                  => $cat_operator,
        'limit'                        	=> $number,
		'categories'                    => $category_ajax,
        'responsive'                    => $responsive, 
        'columns'                       => $columns, 
        'layout_type'                   => $layout_type,
        'data_carousel'                 => $data_carousel,
    ); 

    $json = apply_filters( 'cena_ajax_vc_categoriestabs', $json, 10, 1 );

    $encoded_settings  = wp_json_encode( $json );

    $tabs_data = 'data-atts="'. esc_attr( $encoded_settings ) .'"';
} else {
    $tabs_data = '';
}

if ( count($list_query) > 0 ) {
?>
	<div class="widget <?php echo esc_attr($align); ?> widget-products widget-product-tabs products <?php echo esc_attr($el_class); ?>">
		<div class="tabs-container tab-heading text-center clearfix tab-v8">
			<?php if($title!=''):?>
				<h3 class="widget-title">
            		<span><span><?php echo esc_html( $title ); ?></span></span><?php if( isset($subtitle) && $subtitle ){ ?><span class="subtitle"><?php echo esc_html($subtitle); ?></span> <?php } ?>
				</h3>
			<?php endif; ?>
			<ul class="product-tabs-title tabs-list nav nav-tabs" <?php echo trim($tabs_data); ?>>
				<?php $__count=0; ?>
				<?php foreach ($list_query as $key => $li) { ?> 
						<?php 
							$class_li	= ($__count==0)?' class="active"':'';
						?>
						<li <?php echo trim( $class_li ); ?>><a href="#<?php echo esc_attr($key.'-'.$_id); ?>" data-toggle="tab" data-value="<?php echo esc_attr($key); ?>" data-title="<?php echo esc_attr($li['title']);?>"><?php echo trim( $li['title_tab'] );?></a></li>
					<?php $__count++; ?>
				<?php } ?> 
			</ul>
		</div>


		<?php if(  $layout_type == 'carousel' || $layout_type == 'carousel-special' ) { ?>

			<div class="widget-content tbay-addon-content tab-content woocommerce">
				<?php $__count=0; ?>
				<?php foreach ($list_query as $key => $li) { ?>
					<?php 
						$tab_active = ($__count == 0) ? ' active active-content current' : '';
					?>
					<div class="tab-pane<?php echo esc_attr( $tab_active ); ?>" id="<?php echo esc_attr($key).'-'.$_id; ?>">
						<?php if( $__count === 0 || $ajax_tabs !== 'yes' ) : ?>
						
							<?php
								if ( isset($categories) && is_array($categories) ) {
									$category 	= cena_tbay_get_category_by_id($categories);
									$loop      	= cena_get_query_products($category, $cat_operator, $key, $number);
								} else if( isset($categories) && !empty($categories) ) {
									$category 	= get_term_by( 'id', $categories, 'product_cat' )->slug;
									$loop      	= cena_get_query_products($category, $cat_operator, $key, $number);
								} else {
									$loop      	= cena_get_query_products('', $cat_operator, $key, $number);
								}
								if ( $loop->have_posts()) {

									wc_get_template( 'layout-products/'. $layout_type .'.php' , array( 'loop' => $loop, 'columns' => $columns, 'rows' => $rows, 'pagi_type' => $pagi_type, 'nav_type' => $nav_type,'screen_desktop' => $screen_desktop,'screen_desktopsmall' => $screen_desktopsmall,'screen_tablet' => $screen_tablet,'screen_mobile' => $screen_mobile, 'number' => $number ) );
								}
							?>

						<?php endif; ?>
					</div>
					<?php $__count++; ?>
				<?php } ?>
			</div>

		<?php } else { ?> 

			<div class="widget-content tbay-addon-content tab-content woocommerce">
				<?php $__count=0; ?>
				<?php foreach ($list_query as $key => $li) { ?>
					
					<?php
						$tab_active = ($__count == 0) ? ' active active-content current' : '';
					?>
					<div class="tab-pane<?php echo esc_attr( $tab_active ); ?>" id="<?php echo esc_attr($key).'-'.$_id; ?>">
						<?php if( $__count === 0 || $ajax_tabs !== 'yes' ) : ?>
							<?php
								if ( isset($categories) && is_array($categories) ) {
									$category 	= cena_tbay_get_category_by_id($categories);
									$loop      	= cena_get_query_products($category, $cat_operator, $key, $number);
								} else if( isset($categories) && !empty($categories) ) {
									$category 	= get_term_by( 'id', $categories, 'product_cat' )->slug;
									$loop      	= cena_get_query_products($category, $cat_operator, $key, $number);
								} else {
									$loop      	= cena_get_query_products('', $cat_operator, $key, $number);
								}
								if ( $loop->have_posts()) {
									
									wc_get_template( 'layout-products/'. $layout_type .'.php' , array( 'loop' => $loop, 'columns' => $columns, 'screen_desktop' => $screen_desktop,'screen_desktopsmall' => $screen_desktopsmall,'screen_tablet' => $screen_tablet,'screen_mobile' => $screen_mobile, 'number' => $number ) );
								}
							?>
						<?php endif; ?>

					</div>
					<?php $__count++; ?>
				<?php } ?>
			</div>			
 
		<?php } ?>

	</div>
<?php wp_reset_postdata(); ?>
<?php } ?>

