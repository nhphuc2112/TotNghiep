<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

?>
<div class="widget widget-banner text-center <?php echo esc_attr($el_class); ?>">
    <?php if ($title!=''): ?>
        <h3 class="widget-title">
            <span><?php echo esc_html( $title ); ?></span>
        </h3>
    <?php endif; ?>
    <div class="widget-content"> 
		<?php if (!empty($description)) { ?>
			<p class="widget-description">
				<?php echo trim( $description ); ?>
			</p>
		<?php } ?>

 	<?php if(trim($link)!=''){ ?>
        <div class="clearfix">
            <a class="btn btn-link btn-xs" href="<?php echo esc_url( $link ); ?>"> <?php esc_html_e('Learn More ', 'cena'); ?> </a>
        </div>
    <?php } ?>

		<?php $img = wp_get_attachment_image_src($image,'full'); ?>
		<?php if ( !empty($img) && isset($img[0]) ): ?>
				<div class="image tbay-image-loaded">
                    <?php 
                        $image_alt  = get_post_meta( $image, '_wp_attachment_image_alt', true);
                        cena_tbay_src_image_loaded($img[0], array('alt' => $image_alt)); 
                    ?>
            	</div>
        <?php endif; ?>
	</div>
</div>