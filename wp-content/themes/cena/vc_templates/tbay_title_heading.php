<?php

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

?>

<div class="widget widget-text-heading <?php echo esc_attr($el_class.' '.$style); ?>">
	<?php if($title!=''): ?>
        <h3 class="widget-title" <?php if($font_color!=''): ?> style="color: <?php echo esc_attr( $font_color ); ?>;"<?php endif; ?>>
           <span><?php echo esc_html( $title ); ?></span>
        </h3>
    <?php endif; ?>
    <?php if(trim($descript)!=''){ ?>
        <div class="description">
            <?php echo trim( $descript ); ?>
        </div>
    <?php } ?>
    <?php if(trim($linkbutton)!='' || trim($linkbutton2)!=''){ ?>
        <div class="clearfix action">
            <?php if(trim($linkbutton)!=''){ ?>
            <a class="btn btn-befo <?php echo esc_attr( $buttons ); ?>" href="<?php echo esc_attr( $linkbutton ); ?>"> <?php echo trim( $textbutton ); ?> </a>
            <?php } ?>
            <?php if(trim($linkbutton2)!=''){ ?>
            <a class="btn btn-befo <?php echo esc_attr( $buttons2 ); ?>" href="<?php echo esc_attr( $linkbutton2 ); ?>"> <?php echo trim( $textbutton2 ); ?> </a>
            <?php } ?>
        </div>
    <?php } ?>
</div>