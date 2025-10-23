<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$args = array(
	'post_type' => 'tbay_testimonial',
	'posts_per_page' => $number,
	'post_status' => 'publish',
);
$loop = new WP_Query($args);

wp_enqueue_script( 'owl-carousel' );

?>

<div class="widget-testimonials widget  <?php echo esc_attr($el_class); ?> <?php echo esc_attr($style); ?>">
	
	<?php if ($title!=''): ?>
        <div class="clearfix">
            <h3 class="widget-title">
                <span><?php echo esc_html( $title ); ?></span>
            </h3>
        </div>
    <?php endif; ?>
	<?php if ( $loop->have_posts() ): ?>

		<div class="owl-carousel scroll-init" data-items="<?php echo esc_attr($columns); ?>" data-carousel="owl" data-smallmedium="2" data-extrasmall="1"  data-pagination="true" data-nav="true">
            <?php while ( $loop->have_posts() ): $loop->the_post(); global $product; ?>
                <div class="item">
                    <?php get_template_part( 'vc_templates/testimonial/testimonial', 'v1' ); ?>
                </div>
            <?php endwhile; ?>
        </div>

	<?php endif; ?>
</div>
<?php wp_reset_postdata(); ?>