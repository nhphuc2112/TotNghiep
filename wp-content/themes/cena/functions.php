<?php
/**
 * cena functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 * 
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package WordPress
 * @subpackage Cena
 * @since cena 2.8.8
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since cena 2.8.8
 */
define( 'CENA_THEME_VERSION', '2.8.8' );

if ( ! isset( $content_width ) ) {
	$content_width = 660;
}

/**
 * ------------------------------------------------------------------------------------------------
 * Define constants.
 * ------------------------------------------------------------------------------------------------
 */
define( 'CENA_THEME_DIR', 		get_template_directory_uri() );
define( 'CENA_THEMEROOT', 		get_template_directory() );
define( 'CENA_IMAGES', 			CENA_THEME_DIR . '/images' );   
define( 'CENA_SCRIPTS', 			CENA_THEME_DIR . '/js' ); 
define( 'CENA_STYLES', 			CENA_THEME_DIR . '/css' ); 

define( 'CENA_INC', 				'/inc' );
define( 'CENA_MERLIN', 			CENA_INC . '/merlin' );
define( 'CENA_CLASSES', 			CENA_INC . '/classes' );
define( 'CENA_VENDORS', 			CENA_INC . '/vendors' );
define( 'CENA_WIDGETS', 			CENA_INC . '/widgets' );

define( 'CENA_ASSETS', 			CENA_THEME_DIR . '/inc/assets' );
define( 'CENA_ASSETS_IMAGES', 	CENA_ASSETS    . '/images' );

define( 'CENA_MIN_JS', 	'' );

define( 'CENA_ACTIVE_MIN', 	false );

if ( ! function_exists( 'cena_tbay_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 *
 * @since cena 2.7.1
 */
function cena_tbay_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on cena, use a find and replace
	 * to change 'cena' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'cena', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );
	
	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();
	
	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * See: https://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */


	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'cena' ),
		'mobile-menu' => esc_html__( 'Mobile Menu','cena' ),
		'topmenu'  => esc_html__( 'Top Menu', 'cena' ),
		'category-menu'  => esc_html__( 'Category Menu', 'cena' ),
		'category-menu-image'  => esc_html__( 'Category Menu Image', 'cena' ),
		'social'  => esc_html__( 'Social Links Menu', 'cena' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );

	add_theme_support( "woocommerce" );
	/*
	 * Enable support for Post Formats.
	 *
	 * See: https://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat'
	) );

	$color_scheme  = cena_tbay_get_color_scheme();
	$default_color = trim( $color_scheme[0], '#' );

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'cena_custom_background_args', array(
		'default-color'      => $default_color,
		'default-attachment' => 'fixed',
	) ) );

    if( apply_filters('cena_remove_widgets_block_editor', true) ) {
        remove_theme_support( 'block-templates' );
        remove_theme_support( 'widgets-block-editor' );

		/*Remove extendify--spacing--larg CSS*/
		update_option('use_extendify_templates', '');
    }

	cena_tbay_get_load_plugins();
}
endif; // cena_tbay_setup
add_action( 'after_setup_theme', 'cena_tbay_setup' );

if ( !function_exists('cena_tbay_size_image_setup') ) {
	function cena_tbay_size_image_setup() {

		/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* See: https://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		*/

		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 825, 504, true ); 

		update_option('thumbnail_size_w', 825);
		update_option('thumbnail_size_h', 504);

	}
	add_action( 'after_setup_theme', 'cena_tbay_size_image_setup' );
}

if(cena_tbay_get_global_config('config_media',false)) {
    remove_action( 'after_setup_theme', 'cena_tbay_size_image_setup' );
}

/**
 * Load Google Front
 */
function cena_fonts_url() {
    $fonts_url = '';

    /* Translators: If there are characters in your language that are not
    * supported by Montserrat, translate this to 'off'. Do not translate
    * into your own language.
    */
    $raleway 		= _x( 'on', 'Raleway font: on or off', 'cena' );
    $montserrat    = _x( 'on', 'Montserrat font: on or off', 'cena' );
    $lato    		= _x( 'on', 'Lato font: on or off', 'cena' );
 
    if ( 'off' !== $raleway || 'off' !== $montserrat ) {
        $font_families = array();
 
        if ( 'off' !== $raleway ) {
            $font_families[] = 'Raleway:400,300,500,600,700,800,900';
        }
        if ( 'off' !== $montserrat ) {
            $font_families[] = 'Montserrat:400,700';
        }
		
		if ( 'off' !== $lato ) {
            $font_families[] = 'Lato:100,100i,300,300i,400,400i,700,700i,900,900i';
        }
 
        $query_args = array(
            'family' => ( implode( '%7C', $font_families ) ),
            'subset' => urlencode( 'latin,latin-ext' ),
            'display' => urlencode( 'swap' ),
        );
 		
 		$protocol = is_ssl() ? 'https:' : 'http:';
        $fonts_url = add_query_arg( $query_args, $protocol .'//fonts.googleapis.com/css' );
    }
 
    return esc_url_raw( $fonts_url );
}

if ( !function_exists('cena_tbay_fonts_url') ) {
	function cena_tbay_fonts_url() {  
		$font_source 	  = cena_tbay_get_config('font_source', "1");
		$font_google_code = cena_tbay_get_config('font_google_code');

		if ( $font_source == "2" && !empty($font_google_code) ) {
			wp_enqueue_style( 'cena-theme-fonts', $font_google_code, array(), null );
		}
	}
	add_action('wp_enqueue_scripts', 'cena_tbay_fonts_url');
}

function cena_tbay_include_files($path) {
    $files = glob( $path );
    if ( ! empty( $files ) ) {
        foreach ( $files as $file ) {
            include $file;
        }
    }
}

/**
 * Enqueue scripts and styles.
 *
 * @since Cena 1.0
 */
function cena_tbay_scripts() {
	
	$menu_option 	= apply_filters( 'cena_menu_mobile_option', 10,2 );
	$suffix 		= (cena_tbay_get_config('minified_js', false)) ? '.min' : CENA_MIN_JS;

	// Load our main stylesheet.
	if( is_rtl() ){
		$css_path =  CENA_STYLES . '/template.rtl.css';
	} else{
		$css_path =  CENA_STYLES . '/template.css';
	}
	 
	wp_enqueue_style( 'cena-template', $css_path, array(), CENA_THEME_VERSION );
	
	$footer_style = cena_tbay_print_style_footer();
	if ( !empty($footer_style) ) {
		wp_add_inline_style( 'cena-template', $footer_style );
	}
	
	//load font awesome
	wp_enqueue_style( 'font-awesome', CENA_STYLES . '/font-awesome.css', array(), '4.5.0' );
	
	//load font simple-line-icons
	wp_enqueue_style( 'simple-line-icons',CENA_STYLES . '/simple-line-icons.css', array(), '2.4.0' );

	wp_enqueue_style( 'themify-icons', CENA_STYLES . '/themify-icons.css', array(), '1.0.0' );
	
	//load font material-design-iconic-font
	wp_enqueue_style( 'material-design-iconic-font', CENA_STYLES . '/material-design-iconic-font.min.css', array(), '2.2.0' );

	// load animate version 3.5.0
	wp_enqueue_style( 'animate', CENA_STYLES . '/animate.css', array(), '3.5.0' );
	
	// load fancybox
	wp_register_style( 'jquery-fancybox', CENA_STYLES . '/jquery.fancybox.css', array(), '3.2.0' );
	wp_register_script( 'jquery-fancybox',CENA_SCRIPTS . '/jquery.fancybox' . $suffix . '.js', array( 'jquery' ), '20150315', true );
	
	// load sumoselect
	wp_register_style('sumoselect', CENA_STYLES . '/sumoselect.css', array(), '1.0.0', 'all');
	wp_register_script('jquery-sumoselect', CENA_SCRIPTS . '/jquery.sumoselect' . $suffix . '.js', array(), '3.0.2', TRUE);

	
	wp_enqueue_script( 'skip-link-cena-fix', CENA_SCRIPTS . '/skip-link-cena-fix' . $suffix . '.js', array(), '20141010', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	/*mmenu menu*/
	if( $menu_option == 'smart_menu' ){
		wp_enqueue_script( 'jquery-mmenu', CENA_SCRIPTS . '/jquery.mmenu' . $suffix . '.js', array( 'jquery' ), '7.0.5', true );
	}
	
	/*Treeview menu*/
	wp_enqueue_style( 'jquery-treeview', CENA_STYLES . '/jquery.treeview.css', array(), '1.0.0' );
	wp_enqueue_script( 'jquery-treeview', CENA_SCRIPTS . '/jquery.treeview' . $suffix . '.js', array( 'jquery' ), '20150330', true );
	
	wp_enqueue_script( 'bootstrap', CENA_SCRIPTS . '/bootstrap' . $suffix . '.js', array( 'jquery' ), '20150330', true );

	wp_enqueue_script('waypoints', CENA_SCRIPTS . '/jquery.waypoints' . $suffix . '.js', array(), '4.0.0', true);

	wp_dequeue_script('wpb_composer_front_js');
	wp_enqueue_script( 'wpb_composer_front_js');
	 
	wp_register_script( 'owl-carousel', CENA_SCRIPTS . '/owl.carousel' . $suffix . '.js', array( 'jquery' ), '2.0.0', true );
	
	if( class_exists('YITH_WCWL') ) {
		wp_enqueue_script( 'wishlist', CENA_SCRIPTS . '/wishlist' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
	}

	if ( is_cart() || is_checkout() ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}

	wp_enqueue_script( 'cena-woocommerce-script', CENA_SCRIPTS . '/woocommerce' . $suffix . '.js', array( 'jquery' ), '20150330', true );

	wp_register_script( 'jquery-countdowntimer',CENA_SCRIPTS . '/jquery.countdownTimer' . $suffix . '.js', array( 'jquery' ), '20150315', true );


	wp_register_script( 'cena-script', CENA_SCRIPTS . '/functions' . $suffix . '.js', array( 'jquery' ), '20150330', true );
	
	global $wp_query; 

	$cena_hash_transient = get_transient( 'cena-hash-time' );
		if ( false === $cena_hash_transient ) {
			$cena_hash_transient = time();
			set_transient( 'cena-hash-time', $cena_hash_transient );
		}

	$config = array(
		'storage_key'  		=> apply_filters( 'cena_storage_key', 'cena_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() . $cena_hash_transient ) ),
		'ajaxurl' 			=> admin_url( 'admin-ajax.php' ), 
		'posts' 				=> json_encode( $wp_query->query_vars ),
		'ajax_update_quantity' => (bool) cena_tbay_get_config('ajax_update_quantity', false),
		'view_all' 			=> esc_html__('View All', 'cena'),
		'cancel' 			=> esc_html__('cancel', 'cena'),  
		'search'			 => esc_html__('Search', 'cena'), 
		'no_results' 		=> esc_html__('No results found', 'cena'),
		'nonce' 			=> wp_create_nonce('cena_ajax_nonce'),
	); 

	if( cena_is_woocommerce_activated() ) {  
		$config['enable_ajax_add_to_cart'] 	= ( get_option('woocommerce_enable_ajax_add_to_cart') === 'yes' ) ? true : false;
		$config['ajax_tabs'] 	= cena_elements_ajax_tabs();
	}

	wp_localize_script( 'cena-script', 'cena_settings', apply_filters('cena_localize_translate', $config));

	wp_enqueue_script( 'cena-script' );
	if ( cena_tbay_get_config('header_js') != "" ) {
		wp_add_inline_script( 'jquery-core', cena_tbay_get_config('header_js') );
	}
 
	wp_enqueue_style( 'cena-style', CENA_THEME_DIR . '/style.css', array(), '1.0' );

}
add_action( 'wp_enqueue_scripts', 'cena_tbay_scripts', 100 );

function cena_tbay_footer_scripts() {
	if ( cena_tbay_get_config('footer_js') != "" ) {
		$footer_js = cena_tbay_get_config('footer_js');
		echo trim($footer_js);
	}
}
add_action('wp_footer', 'cena_tbay_footer_scripts');

add_action( 'admin_enqueue_scripts', 'cena_tbay_load_admin_styles' );
function cena_tbay_load_admin_styles() {
	wp_enqueue_style( 'cena-custom-admin', get_template_directory_uri() . '/css/admin/custom-admin.css', false, '1.0.0' );
}  


/**
 * Display descriptions in main navigation.
 *
 * @since Cena 1.0
 *
 * @param string  $item_output The menu item output.
 * @param WP_Post $item        Menu item object.
 * @param int     $depth       Depth of the menu.
 * @param array   $args        wp_nav_menu() arguments.
 * @return string Menu item with possible description.
 */
function cena_tbay_nav_description( $item_output, $item, $depth, $args ) {
	if ( 'primary' == $args->theme_location && $item->description ) {
		$item_output = str_replace( $args->link_after . '</a>', '<div class="menu-item-description">' . $item->description . '</div>' . $args->link_after . '</a>', $item_output );
	}

	return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'cena_tbay_nav_description', 10, 4 );

/**
 * Add a `screen-reader-text` class to the search form's submit button.
 *
 * @since Cena 1.0
 *
 * @param string $html Search form HTML.
 * @return string Modified search form HTML.
 */
function cena_tbay_search_form_modify( $html ) {
	return str_replace( 'class="search-submit"', 'class="search-submit screen-reader-text"', $html );
}
add_filter( 'get_search_form', 'cena_tbay_search_form_modify' );

/**
 * Function for remove srcset (WP4.4)
 *
 */
function cena_tbay_disable_srcset( $sources ) {
    return false;
}
// add_filter( 'wp_calculate_image_srcset', 'cena_tbay_disable_srcset' );


function cena_tbay_get_config($name, $default = '') {
	global $tbay_options;
    if ( isset($tbay_options[$name]) ) {
        return $tbay_options[$name];
    }
    return $default;
}

if ( ! function_exists( 'cena_time_link' ) ) :
/**
 * Gets a nicely formatted string for the published date.
 */
function cena_time_link() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

	$time_string = sprintf( $time_string,
		get_the_date( DATE_W3C ),
		get_the_date(),
		get_the_modified_date( DATE_W3C ),
		get_the_modified_date()
	);

	// Wrap the time string in a link, and preface it with 'Posted on'.
	return sprintf(
		/* translators: %s: post date */
		__( '<span class="screen-reader-text">Posted on</span> %s', 'cena' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);
}
endif;

function cena_tbay_get_global_config($name, $default = '') {
	$options = get_option( 'cena_tbay_theme_options', array() );
	if ( isset($options[$name]) ) {
        return $options[$name];
    }
    return $default;
}

function cena_tbay_widgets_init() {
	
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar Default', 'cena' ),
		'id'            => 'sidebar-default',
		'description'   => esc_html__( 'Add widgets here to appear in your Sidebar.', 'cena' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	

    if (defined('CENA_TBAY_FRAMEWORK_ACTIVED')) {
        register_sidebar(array(
            'name'          => esc_html__('Top Contact', 'cena'),
            'id'            => 'top-contact',
            'description'   => esc_html__('Add widgets here to appear in Top Contact.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Top Shipping', 'cena'),
            'id'            => 'top-shipping',
            'description'   => esc_html__('Add widgets here to appear in Top Shipping.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Top Shipping for Layout 2', 'cena'),
            'id'            => 'top-shipping-2',
            'description'   => esc_html__('Add widgets here to appear in Top Shipping.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Top Shipping for Layout 3', 'cena'),
            'id'            => 'top-shipping-3',
            'description'   => esc_html__('Add widgets here to appear in Top Shipping.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Top Offer', 'cena'),
            'id'            => 'top-offer',
            'description'   => esc_html__('Add widgets here to appear in Top Offer.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Top Slider for Layout4', 'cena'),
            'id'            => 'top-slider',
            'description'   => esc_html__('Add widgets here to appear in Top Slider for Layout 4.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Top Phone for Layout8', 'cena'),
            'id'            => 'top-phone',
            'description'   => esc_html__('Add widgets here to appear in Top Phone for Layout 8,9.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        
        register_sidebar(
            array(
            'name'          => esc_html__('Newsletter', 'cena'),
            'id'            => 'newsletter',
            'description'   => esc_html__('Appears on posts and pages in the sidebar.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget  clearfix %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h3 class="widget-title"><span><span>',
            'after_title'   => '</span></span></h3>',
        )
        );

        register_sidebar(
            array(
            'name'          => esc_html__('Social', 'cena'),
            'id'            => 'social',
            'description'   => esc_html__('Appears on posts and pages in the sidebar.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="clearfix %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h3 class="widget-title"><span><span>',
            'after_title'   => '</span></span></h3>',
        )
        );

        register_sidebar(
            array(
            'name'          => esc_html__('Tbay Popup Newsletter', 'cena'),
            'id'            => 'popupnewletter',
            'description'   => esc_html__('Appears on posts and pages in the sidebar.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget  clearfix %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h3 class="widget-title"><span><span>',
            'after_title'   => '</span></span></h3>',
        )
        );
        register_sidebar(array(
            'name'          => esc_html__('Currency Switcher', 'cena'),
            'id'            => 'currency-switcher',
            'description'   => esc_html__('Add widgets here to appear in your Header.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Information Topbar', 'cena'),
            'id'            => 'info-topbar',
            'description'   => esc_html__('Add widgets here to appear in your Top Bar.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Social Topbar', 'cena'),
            'id'            => 'social-topbar',
            'description'   => esc_html__('Add widgets here to appear in your Top Bar.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Blog left sidebar', 'cena'),
            'id'            => 'blog-left-sidebar',
            'description'   => esc_html__('Add widgets here to appear in your sidebar.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Blog right sidebar', 'cena'),
            'id'            => 'blog-right-sidebar',
            'description'   => esc_html__('Add widgets here to appear in your sidebar.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Product left sidebar', 'cena'),
            'id'            => 'product-left-sidebar',
            'description'   => esc_html__('Add widgets here to appear in your sidebar.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
        register_sidebar(array(
            'name'          => esc_html__('Product right sidebar', 'cena'),
            'id'            => 'product-right-sidebar',
            'description'   => esc_html__('Add widgets here to appear in your sidebar.', 'cena'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
    }	
	register_sidebar( array(
		'name'          => esc_html__( 'Footer', 'cena' ),
		'id'            => 'footer',
		'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'cena' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	
}
add_action( 'widgets_init', 'cena_tbay_widgets_init' );

function cena_tbay_get_load_plugins() {

	$plugins[] =(array(
		'name'                     => 'Cmb2',
	    'slug'                     => 'cmb2',
	    'required'                 => true,
	));

	$plugins[] =(array(
		'name'                     => 'WooCommerce',
	    'slug'                     => 'woocommerce',
	    'required'                 => true,
	));

	$plugins[] =(array(
		'name'                     => 'MailChimp',
	    'slug'                     => 'mailchimp-for-wp',
	    'required'                 =>  true
	));

	$plugins[] =(array(
		'name'                     => 'Contact Form 7',
	    'slug'                     => 'contact-form-7',
	    'required'                 => true
	));

	$plugins[] =(array(
		'name'                     => 'WPBakery Visual Composer',
		'slug'                     => 'js_composer',
		'required'                 => true, 
		'source'         		   => esc_url( 'plugins.thembay.com/js_composer.zip' ),
	));

	$plugins[] =(array(
		'name'                     => 'Tbay Framework For Themes',
		'slug'                     => 'tbay-framework',
		'required'                 => true ,
		'source'       			   => esc_url( 'plugins.thembay.com/tbay-framework.zip' )
	));

	
	$plugins[] =(array(
		'name'                     => 'Redux Framework',
		'slug'                     => 'redux-framework',
		'required'                 => true ,
	));

	$plugins[] =(array(
		'name'                     => 'WooCommerce Variation Swatches',
	    'slug'                     => 'woo-variation-swatches',
	    'required'                 =>  true,
	   	'source'         		   => esc_url( 'downloads.wordpress.org/plugin/woo-variation-swatches.zip' ),
	));	  

	$plugins[] =(array(
		'name'                     => 'YITH WooCommerce Brands Add-On',
	    'slug'                     => 'yith-woocommerce-brands-add-on',
	    'required'                 =>  true
	));

	$plugins[] =(array(
		'name'                     => 'YITH WooCommerce Quick View',
	    'slug'                     => 'yith-woocommerce-quick-view',
	    'required'                 =>  true
	));
	
	$plugins[] =(array(
		'name'                     => 'YITH WooCommerce Wishlist',
	    'slug'                     => 'yith-woocommerce-wishlist',
	    'required'                 =>  true
	));

	$plugins[] =(array(
		'name'                     => 'YITH Woocommerce Compare',
        'slug'                     => 'yith-woocommerce-compare',
        'required'                 => true
	));
	
	$plugins[] =(array(
		'name'                     => 'Revolution Slider',
		'slug'                     => 'revslider',
		'required'                 => true ,
		'source'         		   => esc_url( 'plugins.thembay.com/revslider.zip' ),
	));
	
	tgmpa( $plugins );
}

require get_template_directory() . '/inc/plugins/class-tgm-plugin-activation.php';

/**Include Merlin Import Demo**/
require_once( get_parent_theme_file_path( CENA_MERLIN . '/vendor/autoload.php') );
require_once( get_parent_theme_file_path( CENA_MERLIN . '/class-merlin.php') );
require_once( get_parent_theme_file_path( CENA_INC . '/merlin-config.php') );

require get_template_directory() . '/inc/functions-helper.php';
require get_template_directory() . '/inc/functions-frontend.php';

/**
 * Implement the Custom Header feature.
 *
 */
require get_template_directory() . '/inc/custom-header.php';
require get_template_directory() . '/inc/classes/custommenu.php';
require get_template_directory() . '/inc/classes/megamenu.php'; 
require get_template_directory() . '/inc/classes/mmenu.php';

/**
 * Custom template tags for this theme.
 *
 */
require get_template_directory() . '/inc/template-tags.php'; 


if ( defined( 'TBAY_FRAMEWORK_REDUX_ACTIVED' ) ) {
	cena_tbay_include_files( get_template_directory() . '/inc/vendors/redux-framework/*.php' );
	define( 'CENA_REDUX_FRAMEWORK_ACTIVED', true );
}
if( cena_is_cmb2() ) {
	cena_tbay_include_files( get_template_directory() . '/inc/vendors/cmb2/*.php' );
}
if( cena_is_woocommerce_activated() ) {
	cena_tbay_include_files( get_template_directory() . '/inc/vendors/woocommerce/functions.php' ); 
	cena_tbay_include_files( get_template_directory() . '/inc/vendors/woocommerce/compatible/yith-wcqv.php' );
}
if( cena_vc_is_activated() ) {
	cena_tbay_include_files( get_template_directory() . '/inc/vendors/visualcomposer/*.php' );
}
if( defined( 'TBAY_FRAMEWORK_REDUX_ACTIVED' ) ) {
	cena_tbay_include_files( get_template_directory() . '/inc/widgets/*.php' );
	define( 'CENA_TBAY_FRAMEWORK_ACTIVED', true );
	define( 'TBAY_FRAMEWORK_WIDGETS_ACTIVED', true );
}
/**
 * Customizer additions.
 *
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Custom Styles
 *
 */
require get_template_directory() . '/inc/custom-styles.php';

