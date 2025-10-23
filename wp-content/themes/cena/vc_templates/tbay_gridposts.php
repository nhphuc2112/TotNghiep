<?php
$align  = $orderby = $order = $title_position = $title_bg = $nav_type = $pagi_type = $loop_type = $auto_type = $autospeed_type = $disable_mobile = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$el_class = isset( $atts['el_class'] ) ? $atts['el_class'] : '';
if( isset($title_position) && $title_position == 'left' ) {
    $el_class .= ' title-left';

    $el_class .= (isset($title_bg) && $title_bg == 'yes') ? ' title-bg' : '';
}

$args = array(
    'posts_per_page' =>     $number,
    'post_status'    =>    'publish',
    'orderby'        =>     $orderby,
    'order'          =>     $order,
    'taxonomy'       =>    'category',
); 

if( $category && ($category != esc_html__('--- Choose a Category ---', 'cena')) ) {
    $args['category_name'] =  $category;
}

$loop = new WP_Query($args);

$rows_count = isset($rows) ? $rows : 1;
set_query_var( 'thumbsize', $thumbsize );

?>

<div class="widget widget-blog <?php echo esc_attr($align); ?> <?php echo esc_attr($layout_type); ?> <?php echo esc_attr($el_class); ?>">
    <?php if ($title!=''): ?>
        <h3 class="widget-title">
            <span><?php echo esc_html( $title ); ?></span>
            <?php if ( isset($subtitle) && $subtitle ): ?>
                <span class="subtitle"><?php echo esc_html($subtitle); ?></span> 
            <?php endif; ?>
        </h3>
    <?php endif; ?>
    <div class="widget-content"> 
        <?php $post_item = '_single'; ?>
        <?php if ( $layout_type == 'carousel' ): ?> 

            <div class="owl-carousel posts scroll-init" data-items="<?php echo esc_attr($columns); ?>" data-extrasmall="1" data-carousel="owl" data-nav="false">
                <?php while ( $loop->have_posts() ): $loop->the_post(); ?>
                    <div class="item">
                        <?php get_template_part( 'vc_templates/post/_single _carousel'); ?>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php elseif ( $layout_type == 'grid' ): ?>

            <?php $bcol = 12/$columns; ?>
            <div class="layout-blog">
                <div class="row">
                    <?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
                        <div class="col-md-<?php echo esc_attr($bcol); ?>">
                            <?php get_template_part( 'vc_templates/post/_single' ); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>


        <?php else: ?>

                <?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
                        <?php get_template_part( 'vc_templates/post/_single_list' ); ?>
                <?php endwhile; ?>
            
        <?php endif; ?>
    </div>
</div>
<?php wp_reset_postdata(); ?>