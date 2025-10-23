<?php

//convert hex to rgb
if ( !function_exists ('cena_tbay_getbowtied_hex2rgb') ) {
	function cena_tbay_getbowtied_hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);
		
		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		return implode(",", $rgb); // returns the rgb values separated by commas
		//return $rgb; // returns an array with the rgb values
	}
}


if ( !function_exists ('cena_tbay_color_lightens_darkens') ) {
	/**
	 * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.7
	 * @param str $hex Colour as hexadecimal (with or without hash);
	 * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
	 * @return str Lightened/Darkend colour as hexadecimal (with hash);
	 */
	function cena_tbay_color_lightens_darkens( $hex, $percent ) {

		// validate hex string
		if( empty($hex) ) return $hex;
		
		$hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
		$new_hex = '#';
		
		if ( strlen( $hex ) < 6 ) {
			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}
		
		// convert to decimal and change luminosity
		for ($i = 0; $i < 3; $i++) {
			$dec = hexdec( substr( $hex, $i*2, 2 ) );
			$dec = min( max( 0, $dec + $dec * $percent ), 255 ); 
			$new_hex .= str_pad( dechex( $dec ) , 2, 0, STR_PAD_LEFT );
		}		
		
		return $new_hex;
	}
}

if ( !function_exists ('cena_tbay_default_theme_primary_color') ) {
	function cena_tbay_default_theme_primary_color() {

		$theme_variable = array();

		$theme_variable['main_color'] 						= '#009aff';

		$theme_variable['bg_buy_now'] 						= '#00f2bc';

		$theme_variable['topbar_bg'] 						= '#2b87c3';

		$theme_variable['topbar_bg_2'] 						= '#fafafa';

		$theme_variable['topbar_bg_3'] 						= '#262626';

		$theme_variable['topbar_bg_4'] 						= '#0b6baa';

		$theme_variable['topbar_icon_color'] 				= '#00f2bc';

		$theme_variable['topbar_text_color'] 				= '#fff';

		$theme_variable['topbar_text_color_second'] 		= '#666';

		$theme_variable['topbar_text_color_third'] 			= '#999';

		$theme_variable['top_cart_background'] 				= '#0b6baa';

		$theme_variable['top_cart_background_2'] 			= '#3a3a3a';

		$theme_variable['top_cart_background_3'] 			= '#fafafa';

		$theme_variable['header_bg'] 						= '#0b6baa';

		$theme_variable['header_bg_second'] 				= '#fff';

		$theme_variable['header_bg_3'] 						= '#2b87c3';

		$theme_variable['header_link_color'] 				= '#fff';

		$theme_variable['main_menu_link_color'] 			= '#fff';

		$theme_variable['main_menu_link_color_second'] 		= '#333';

		$theme_variable['main_menu_link_color_active'] 		= '#fff';

		$theme_variable['main_menu_bg_color_active'] 		= '#00f2bc';

		$theme_variable['footer_heading_color'] 			= '#222';

		$theme_variable['footer_text_color'] 				= '#333';

		$theme_variable['footer_link_color'] 				= '#333';

		$theme_variable['copyright_bg'] 					= '#fff';

		$theme_variable['copyright_text_color'] 			= '#666';

		return apply_filters( 'cena_get_default_theme_color', $theme_variable);
	}
}

if ( !function_exists ('cena_tbay_default_theme_primary_fonts') ) {
	function cena_tbay_default_theme_primary_fonts() {

		$theme_variable = array();

		$theme_variable['main_font'] 			= 'Lato, sans-serif';

		$theme_variable['secondary_font'] 		= 'Montserrat, sans-serif';

		return apply_filters( 'cena_get_default_theme_fonts', $theme_variable);
	}
}
 
if (!function_exists('cena_tbay_check_empty_customize')) {
    function cena_check_empty_customize($option, $default){
		echo (!empty($option) && !is_array($option) && $option !== 'Array' ) ? trim($option) : trim($default);
	} 
}

if (!function_exists('cena_tbay_theme_primary_color')) {
    function cena_tbay_theme_primary_color()
    {

		$default 						= cena_tbay_default_theme_primary_color();

		/* Main Color*/
        $main_color   					= cena_tbay_get_config(('main_color'),$default['main_color']);

        /* Background Buy Now */
        $bg_buy_now   					= cena_tbay_get_config(('bg_buy_now'),$default['bg_buy_now']);

        /* Top Bar */
        $topbar_text_color   			= cena_tbay_get_config(('topbar_text_color'),$default['topbar_text_color']);

        $topbar_text_color_second   	= cena_tbay_get_config(('topbar_text_color'),$default['topbar_text_color_second']);

        $topbar_text_color_third   		= cena_tbay_get_config(('topbar_text_color'),$default['topbar_text_color_third']);

        $topbar_bg   					= cena_tbay_get_config(('topbar_bg'),$default['topbar_bg']);

        $topbar_bg_2   					= cena_tbay_get_config(('topbar_bg'),$default['topbar_bg_2']);

        $topbar_bg_3   					= cena_tbay_get_config(('topbar_bg'),$default['topbar_bg_3']);

        $topbar_bg_4   					= cena_tbay_get_config(('topbar_bg'),$default['topbar_bg_4']);

        $topbar_icon_color   			= cena_tbay_get_config(('topbar_icon_color'),$default['topbar_icon_color']);

        $top_cart_background   			= cena_tbay_get_config(('top_cart_background'),$default['top_cart_background']);

        $top_cart_background_2   		= cena_tbay_get_config(('top_cart_background'),$default['top_cart_background_2']);

        $top_cart_background_3   		= cena_tbay_get_config(('top_cart_background'),$default['top_cart_background_3']);

        /* Header */
        $header_bg   					= cena_tbay_get_config(('header_bg'),$default['header_bg']);

        $header_bg_second   			= cena_tbay_get_config(('header_bg'),$default['header_bg_second']);

        $header_bg_3   					= cena_tbay_get_config(('header_bg'),$default['header_bg_3']);

        $header_link_color   			= cena_tbay_get_config(('header_link_color'),$default['header_link_color']);

        /* Main Menu */
        $main_menu_link_color   		= cena_tbay_get_config(('main_menu_link_color'),$default['main_menu_link_color']);

        $main_menu_link_color_second   	= cena_tbay_get_config(('main_menu_link_color'),$default['main_menu_link_color_second']);

        $main_menu_link_color_active   	= cena_tbay_get_config(('main_menu_link_color_active'),$default['main_menu_link_color_active']);

        $main_menu_bg_color_active   	= cena_tbay_get_config(('main_menu_bg_color_active'),$default['main_menu_bg_color_active']);

        /* Footer */
        $footer_heading_color   		= cena_tbay_get_config(('footer_heading_color'),$default['footer_heading_color']);

        $footer_text_color   			= cena_tbay_get_config(('footer_text_color'),$default['footer_text_color']);

        $footer_link_color   			= cena_tbay_get_config(('footer_link_color'),$default['footer_link_color']);

        /* Copy right */
        $copyright_bg   				= cena_tbay_get_config(('copyright_bg'),$default['copyright_bg']);

        $copyright_text_color   		= cena_tbay_get_config(('copyright_text_color'),$default['copyright_text_color']);

		/*Theme Color*/
		?>
		:root {
			--tb-theme-color: <?php cena_check_empty_customize( $main_color, $default['main_color'] ); ?>;
			--tb-theme-color-hover: <?php cena_check_empty_customize( cena_tbay_color_lightens_darkens($main_color, -0.1), cena_tbay_color_lightens_darkens($default['main_color'], -0.1) ); ?>;
			--tb-bg-buy-now: <?php cena_check_empty_customize( $bg_buy_now, $default['bg_buy_now'] ); ?>;
			--tb-topbar-icon-color: <?php cena_check_empty_customize( $topbar_icon_color, $default['topbar_icon_color'] ); ?>;
			--tb-topbar-bg: <?php cena_check_empty_customize( $topbar_bg, $default['topbar_bg'] ); ?>;
			--tb-topbar-bg-2: <?php cena_check_empty_customize( $topbar_bg_2, $default['topbar_bg_2'] ); ?>;
			--tb-topbar-bg-3: <?php cena_check_empty_customize( $topbar_bg_3, $default['topbar_bg_3'] ); ?>;
			--tb-topbar-bg-4: <?php cena_check_empty_customize( $topbar_bg_4, $default['topbar_bg_4'] ); ?>;
			--tb-topbar-text-color: <?php cena_check_empty_customize( $topbar_text_color, $default['topbar_text_color'] ); ?>;
			--tb-topbar-text-color-2: <?php cena_check_empty_customize( $topbar_text_color_second, $default['topbar_text_color_second'] ); ?>;
			--tb-topbar-text-color-third: <?php cena_check_empty_customize( $topbar_text_color_third, $default['topbar_text_color_third'] ); ?>;
			--tb-top-cart-background: <?php cena_check_empty_customize( $top_cart_background, $default['top_cart_background'] ); ?>;
			--tb-top-cart-background-2: <?php cena_check_empty_customize( $top_cart_background_2, $default['top_cart_background_2'] ); ?>;
			--tb-top-cart-background-3: <?php cena_check_empty_customize( $top_cart_background_3, $default['top_cart_background_3'] ); ?>;
			--tb-header-bg: <?php cena_check_empty_customize( $header_bg, $default['header_bg'] ); ?>;
			--tb-header-bg-second: <?php cena_check_empty_customize( $header_bg_second, $default['header_bg_second'] ); ?>;
			--tb-header-bg-3: <?php cena_check_empty_customize( $header_bg_3, $default['header_bg_3'] ); ?>;
			--tb-header-link-color: <?php cena_check_empty_customize( $header_link_color, $default['header_link_color'] ); ?>;
			--tb-main-menu-link-color: <?php cena_check_empty_customize( $main_menu_link_color, $default['main_menu_link_color'] ); ?>;
			--tb-link-menu-active: <?php cena_check_empty_customize( $main_menu_link_color_active, $default['main_menu_link_color_active'] ); ?>;
			--tb-link-menu-color-second: <?php cena_check_empty_customize( $main_menu_link_color_second, $default['main_menu_link_color_second'] ); ?>;
			--tb-main-menu-bg-color-active: <?php cena_check_empty_customize( $main_menu_bg_color_active, $default['main_menu_bg_color_active'] ); ?>;
			--tb-footer-heading-color: <?php cena_check_empty_customize( $footer_heading_color, $default['footer_heading_color'] ); ?>;
			--tb-footer-text-color: <?php cena_check_empty_customize( $footer_text_color, $default['footer_text_color'] ); ?>;
			--tb-footer-link-color: <?php cena_check_empty_customize( $footer_link_color, $default['footer_link_color'] ); ?>;
			--tb-copyright-bg: <?php cena_check_empty_customize( $copyright_bg, $default['copyright_bg'] ); ?>;
			--tb-copyright-text-color: <?php cena_check_empty_customize( $copyright_text_color, $default['copyright_text_color'] ); ?>;
			
		} 
		<?php
    }
}

if ( !function_exists ('cena_tbay_custom_styles') ) {
	function cena_tbay_custom_styles() {
		ob_start();

		cena_tbay_theme_primary_color();

		$default_fonts 		= cena_tbay_default_theme_primary_fonts();

		if (!defined('CENA_TBAY_FRAMEWORK_ACTIVED')) {
			?>
			:root {
				--tb-text-primary-font: <?php echo trim($default_fonts['main_font']); ?>;
				--tb-text-second-font: <?php echo trim($default_fonts['secondary_font']); ?>;
			}  
			<?php
		} else {

			$font_source 			= cena_tbay_get_config('font_source');
			$primary_font 			= cena_tbay_get_config('main_font')['font-family'];
			$main_google_font_face  = cena_tbay_get_config('main_google_font_face');
	
			$second_font					= cena_tbay_get_config('secondary_font')['font-family'];
			$main_second_google_font_face 	= cena_tbay_get_config('secondary_google_font_face');
	
			if ($font_source  == "2" && $main_google_font_face) {
				$primary_font = $main_google_font_face;
				$second_font  = $main_second_google_font_face;
			} 
			?>
			:root {
				--tb-text-primary-font: <?php cena_check_empty_customize( $primary_font, $default_fonts['main_font'] ); ?>;
				--tb-text-second-font: <?php cena_check_empty_customize( $second_font, $default_fonts['secondary_font'] ); ?>;
			}  
	
			/* Woocommerce Breadcrumbs */
			<?php if ( cena_tbay_get_config('breadcrumbs') == "0" ) : ?>
			.woocommerce .woocommerce-breadcrumb,
			.woocommerce-page .woocommerce-breadcrumb
			{
				display:none;
			}
			<?php endif; ?>
	
	
			/********************************************************************/
			/* Custom CSS *******************************************************/
			/********************************************************************/
			<?php if ( cena_tbay_get_config('custom_css') != "" ) : ?>
				<?php echo cena_tbay_get_config('custom_css') ?>
			<?php endif; ?>
		
		<?php }
	
		$content = ob_get_clean();
		$content = str_replace(array("\r\n", "\r"), "\n", $content);
		$lines = explode("\n", $content);
		$new_lines = array();
		foreach ($lines as $i => $line) {
			if (!empty($line)) {
				$new_lines[] = trim($line);
			}
		}

		$custom_css = implode($new_lines);

		wp_enqueue_style( 'cena-style', get_template_directory_uri() . '/style.css', array(), '1.0' );

		wp_add_inline_style( 'cena-style', $custom_css );
	}
}
add_action('wp_enqueue_scripts', 'cena_tbay_custom_styles', 999);