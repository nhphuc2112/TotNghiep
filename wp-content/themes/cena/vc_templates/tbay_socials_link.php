<?php

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$socials = array(
	'facebook' => esc_html__('Facebook', 'cena'), 
	'twitter' => esc_html__('Twitter', 'cena'),
	'youtube' => esc_html__('Youtube', 'cena'), 
	'pinterest' => esc_html__('Pinterest', 'cena'),
	'google-plus' => esc_html__('Google Plus', 'cena'),
	'snapchat' => esc_html__('Snapchat', 'cena'),
	'instagram' => esc_html__('Instagram', 'cena')
);
?>
<div class="widget widget-social <?php echo esc_attr($el_class); ?>">
    <?php if ($title!=''): ?>
        <h3 class="widget-title">
            <span><?php echo esc_html( $title ); ?></span>
            <?php if ( isset($subtitle) && $subtitle ): ?>
                <span class="subtitle"><?php echo esc_html($subtitle); ?></span>
            <?php endif; ?>
        </h3>
    <?php endif; ?>
    <div class="widget-content">
    	<?php if ($description != ''): ?>
	        <?php echo trim($description); ?>
	    <?php endif; ?>
		<ul class="social list-inline">
		    <?php foreach( $socials as $key=>$social):
		            if( isset($atts[$key.'_url']) && !empty($atts[$key.'_url']) ): ?>
		                <li>
		                    <a href="<?php echo esc_url($atts[$key.'_url']);?>" target="_blank" class="<?php echo esc_attr($key); ?>">
		                        <i class="fa fa-<?php echo esc_attr($key); ?> "></i>
		                    </a>
		                </li>
		    <?php
		            endif;
		        endforeach;
		    ?>
		</ul>
	</div>
</div>