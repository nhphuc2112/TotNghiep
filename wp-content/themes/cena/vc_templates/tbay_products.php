<?php

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
$cat_operator = 'IN';
extract( $atts );

if ( $type == '' ) return;

if (isset($categories) && !empty($categories)) {
    $categories = explode(',', $categories);
}

$categories = cena_tbay_get_category_by_id($categories);
$_id = cena_tbay_random_key();
$_count = 1;

$loop      = cena_get_query_products($categories, $cat_operator, $type, $number);

if($responsive_type == 'yes') {
    $screen_desktop          =      isset($screen_desktop) ? $screen_desktop : 4;
    $screen_desktopsmall     =      isset($screen_desktopsmall) ? $screen_desktopsmall : 3;
    $screen_tablet           =      isset($screen_tablet) ? $screen_tablet : 3;
    $screen_mobile           =      isset($screen_mobile) ? $screen_mobile : 1;
} else {
    $screen_desktop          =      $columns;
    $screen_desktopsmall     =      $columns;
    $screen_tablet           =      $columns;
    $screen_mobile           =      $columns;  
}

?>
<div class="widget widget-<?php echo esc_attr($layout_type); ?> widget-products products <?php echo esc_attr($el_class); ?>">

	<?php if ($title!=''): ?>
        <h3 class="widget-title">
            <span><?php echo esc_html( $title ); ?></span>
            <?php if ( isset($subtitle) && $subtitle ): ?>
                <span class="subtitle"><?php echo esc_html($subtitle); ?></span>
            <?php endif; ?>
        </h3>
    <?php endif; ?>

	<?php if ( $loop->have_posts() ) : ?>
		<div class="widget-content woocommerce">
			<div class="<?php echo esc_attr( $layout_type ); ?>-wrapper">

                <?php  wc_get_template( 'layout-products/'. $layout_type .'.php' , array( 'loop' => $loop, 'columns' => $columns, 'rows' => $rows, 'pagi_type' => $pagi_type, 'nav_type' => $nav_type,'screen_desktop' => $screen_desktop,'screen_desktopsmall' => $screen_desktopsmall,'screen_tablet' => $screen_tablet,'screen_mobile' => $screen_mobile, 'number' => $number ) ); ?>

			</div>
		</div>
	<?php endif; ?>

</div>
