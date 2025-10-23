<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$bcol = 100/$columns;
$images = $images ? explode(',', $images) : array();
$count = 0;

wp_enqueue_style( 'jquery-fancybox' );
wp_enqueue_script( 'jquery-fancybox' );

if ( !empty($images) ):
?>
	<div class="widget widget-gallery clearfix <?php echo esc_attr($el_class);?>">
	    <?php if ($title!=''): ?>
	    <div class="text-center clearfix heading">
	        <h3 class="widget-title">
	            <span><?php echo esc_html( $title ); ?></span>
	        </h3>
	    </div>
	    <?php endif; ?>
	    <div class="widget-content">
				<?php foreach ($images as $image): ?>
					<?php $img = wp_get_attachment_image_src($image,'full'); ?>
					<?php if ( !empty($img) && isset($img[0]) ): ?>
						<div class="image" style="width:<?php echo esc_attr($bcol); ?>%">
							<a href="<?php echo esc_url_raw($img[0]); ?>" class="fancybox tbay-image-loaded">
	                    		<?php 
									$image_alt  = get_post_meta( $image, '_wp_attachment_image_alt', true);
									cena_tbay_src_image_loaded($img[0], array('alt' => $image_alt)); 
	                    		?>
	                    	</a>
	                    </div>
	                <?php endif; ?>
				<?php $count++;  endforeach; ?>
		</div>
	</div>
<?php endif; ?>