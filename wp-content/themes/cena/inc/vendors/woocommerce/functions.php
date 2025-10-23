<?php

// add to cart modal box
if (!function_exists('cena_tbay_woocommerce_add_to_cart_modal')) {
    function cena_tbay_woocommerce_add_to_cart_modal()
    {
        ?>
    <div class="modal fade" id="tbay-cart-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close btn btn-close" data-dismiss="modal" aria-hidden="true">
                        <i class="fa fa-times"></i>
                    </button>
                    <div class="modal-body-content"></div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
}

// cart modal
if (!function_exists('cena_tbay_woocommerce_cart_modal')) {
    function cena_tbay_woocommerce_cart_modal()
    {
        if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'cena_ajax_nonce') ) {
            exit;
        }

        wc_get_template('content-product-cart-modal.php', array( 'product_id' => (int)$_GET['product_id'] ));
        die;
    }
    add_action('wp_ajax_cena_add_to_cart_product', 'cena_tbay_woocommerce_cart_modal');
    add_action('wp_ajax_nopriv_cena_add_to_cart_product', 'cena_tbay_woocommerce_cart_modal');
}

add_action('wp_footer', 'cena_tbay_woocommerce_add_to_cart_modal');

if (! function_exists('cena_get_query_products')) {
    function cena_get_query_products($categories = array(), $cat_operator = 'IN', $product_type = 'newest', $limit = '', $orderby = '', $order = '')
    {
        $atts = [
            'limit' => $limit,
            'orderby' => $orderby,
            'order' => $order
        ];

        if (!empty($categories)) {
            if (!is_array($categories)) {
                $atts['category'] = $categories;
            } else {
                $atts['category'] = implode(', ', $categories);
                $atts['cat_operator'] = $cat_operator;
            }
        }

        $type = 'products';

        $shortcode = new WC_Shortcode_Products($atts, $type);
        $args = $shortcode->get_query_args();

        $args = cena_get_attribute_query_product_type($args, $product_type);
        return new WP_Query($args);
    }
}

if (! function_exists('cena_get_attribute_query_product_type')) {
    function cena_get_attribute_query_product_type($args, $product_type)
    {
        global $woocommerce;

        switch ($product_type) {
            case 'best_selling':
                $args['meta_key']   = 'total_sales';
                $args['order']      = 'DESC';
                $args['orderby']    = 'meta_value_num';
                $args['ignore_sticky_posts']   = 1;
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
                break;

            case 'featured':
            case 'featured_product':
                $args['ignore_sticky_posts']    = 1;
                $args['meta_query']             = array();
                $args['orderby']                = 'date';
                $args['order']                  = 'DESC';
                $args['meta_query'][]           = $woocommerce->query->stock_status_meta_query();
                $args['meta_query'][]           = $woocommerce->query->visibility_meta_query();
                $args['tax_query'][]              = array(
                    array(
                        'taxonomy' => 'product_visibility',
                        'field'    => 'name',
                        'terms'    => 'featured',
                        'operator' => 'IN'
                    )
                );
                break;

            case 'top_rated':
            case 'top_rate':
                $args['meta_key']       = '_wc_average_rating';
                $args['orderby']        = 'meta_value_num';
                $args['order']          = 'DESC';
                break;

            case 'newest':
            case 'recent_product':
                $args['orderby']    = 'date';
                $args['order']      = 'DESC';
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                break;

            case 'random_product':
                $args['orderby']    = 'rand';
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                break;

            case 'deals':
                $product_ids_on_sale    = wc_get_product_ids_on_sale();
                $product_ids_on_sale[]  = 0;
                $args['post__in'] = $product_ids_on_sale;
                $args['meta_query'] = array();
                $args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
                $args['meta_query'][] = $woocommerce->query->visibility_meta_query();
                $args['meta_query'][] =  array(
                    'relation' => 'AND',
                    array(
                        'relation' => 'OR',
                        array(
                            'key'           => '_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        ),
                        array(
                            'key'           => '_min_variation_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        ),
                    ),
                    array(
                        'key'           => '_sale_price_dates_to',
                        'value'         => time(),
                        'compare'       => '>',
                        'type'          => 'numeric'
                    ),
                );
                break;

            case 'on_sale':
                $product_ids_on_sale    = wc_get_product_ids_on_sale();
                $product_ids_on_sale[]  = 0;
                $args['post__in'] = $product_ids_on_sale;
                break;
        }

        if('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
            $args['meta_query'][] =  array(
                'relation' => 'AND',
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                )
            );
        }

        $product_visibility_term_ids = wc_get_product_visibility_term_ids();
        $args[ 'tax_query' ]         = isset($args[ 'tax_query' ]) ? $args[ 'tax_query' ] : array();
        $args[ 'tax_query' ][]       = array(
            'taxonomy' => 'product_visibility',
            'field'    => 'term_taxonomy_id',
            'terms'    => is_search() ? $product_visibility_term_ids[ 'exclude-from-search' ] : $product_visibility_term_ids[ 'exclude-from-catalog' ],
            'operator' => 'NOT IN',
        );

        return $args;
    }
}



// hooks
if (!function_exists('cena_tbay_woocommerce_enqueue_styles')) {
    function cena_tbay_woocommerce_enqueue_styles()
    {

        $suffix         = (cena_tbay_get_config('minified_js', false)) ? '.min' : CENA_MIN_JS;
        // Load our main stylesheet.
        if(is_rtl()) {
            $css_path =  CENA_STYLES . '/woocommerce.rtl.css';
        } else {
            $css_path =  CENA_STYLES . '/woocommerce.css';
        }

        wp_enqueue_style('cena-woocommerce', $css_path, array(), CENA_THEME_VERSION, 'all');

        wp_enqueue_script('wc-single-product');
        wp_enqueue_script('flexslider');

        wp_register_script('slick', CENA_SCRIPTS . '/slick' . $suffix . '.js', array( 'jquery' ), '1.0.0', true);

    }
}
add_action('wp_enqueue_scripts', 'cena_tbay_woocommerce_enqueue_styles', 50);


/*Call funciton WCVariation Swatches  swallow2603*/
if(class_exists('TA_WC_Variation_Swatches')) {
    function cena_get_swatch_html($html, $args)
    {
        $swatch_types = TA_WCVS()->types;
        $attr         = TA_WCVS()->get_tax_attribute($args['attribute']);

        // Return if this is normal attribute
        if (empty($attr)) {
            return $html;
        }

        if (! array_key_exists($attr->attribute_type, $swatch_types)) {
            return $html;
        }

        $options   = $args['options'];
        $product   = $args['product'];
        $attribute = $args['attribute'];
        $class     = "variation-selector variation-select-{$attr->attribute_type}";
        $swatches  = '';

        if (empty($options) && ! empty($product) && ! empty($attribute)) {
            $attributes = $product->get_variation_attributes();
            $options    = $attributes[$attribute];
        }

        if (array_key_exists($attr->attribute_type, $swatch_types)) {
            if (! empty($options) && $product && taxonomy_exists($attribute)) {
                // Get terms if this is a taxonomy - ordered. We need the names too.
                $terms = wc_get_product_terms($product->get_id(), $attribute, array( 'fields' => 'all' ));

                foreach ($terms as $term) {
                    if (in_array($term->slug, $options)) {
                        $swatches .= apply_filters('tawcvs_swatch_html', '', $term, $attr, $args);
                    }
                }
            }

            if (! empty($swatches)) {
                $class .= ' hidden';

                $swatches = '<div class="tawcvs-swatches" data-attribute_name="attribute_' . esc_attr($attribute) . '">' . $swatches . '</div>';
                $html     = '<div class="' . esc_attr($class) . '">' . $html . '</div>' . $swatches;
            }
        }

        return $html;
    }

    function cena_swatch_html($html, $term, $attr, $args)
    {
        $selected = sanitize_title($args['selected']) == $term->slug ? 'selected' : '';
        $name     = esc_html(apply_filters('woocommerce_variation_option_name', $term->name));

        switch ($attr->attribute_type) {
            case 'color':
                $color = get_term_meta($term->term_id, 'color', true);
                list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
                $html = sprintf(
                    '<span class="swatch swatch-color swatch-%s %s" style="background-color:%s;color:%s;" title="%s" data-value="%s">%s</span>',
                    esc_attr($term->slug),
                    $selected,
                    esc_attr($color),
                    "rgba($r,$g,$b,0.5)",
                    esc_attr($name),
                    esc_attr($term->slug),
                    $name
                );
                break;

            case 'image':
                $image = get_term_meta($term->term_id, 'image', true);
                $image = $image ? wp_get_attachment_image_src($image) : '';
                $image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
                $html  = sprintf(
                    '<span class="swatch swatch-image swatch-%s %s" title="%s" data-value="%s"><img src="%s" alt="%s">%s</span>',
                    esc_attr($term->slug),
                    $selected,
                    esc_attr($name),
                    esc_attr($term->slug),
                    esc_url($image),
                    esc_attr($name),
                    esc_attr($name)
                );
                break;

            case 'label':
                $label = get_term_meta($term->term_id, 'label', true);
                $label = $label ? $label : $name;
                $html  = sprintf(
                    '<span class="swatch swatch-label swatch-%s %s" title="%s" data-value="%s">%s</span>',
                    esc_attr($term->slug),
                    $selected,
                    esc_attr($name),
                    esc_attr($term->slug),
                    esc_html($label)
                );
                break;
        }

        return $html;
    }
}

// cart
if (!function_exists('cena_tbay_woocommerce_header_add_to_cart_fragment')) {
    function cena_tbay_woocommerce_header_add_to_cart_fragment($fragments)
    {
        $fragments['#cart .mini-cart-items'] =  sprintf(_n(' <span class="mini-cart-items"> %d  </span> ', ' <span class="mini-cart-items"> %d </span> ', WC()->cart->get_cart_contents_count(), 'cena'), WC()->cart->get_cart_contents_count());
        $fragments['#cart .mini-cart-total'] = trim(WC()->cart->get_cart_subtotal());
        return $fragments;
    }
    add_filter('woocommerce_add_to_cart_fragments', 'cena_tbay_woocommerce_header_add_to_cart_fragment', 10, 1);
}

add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    ob_start();
    ?>

     <span class="sub-title"><?php echo esc_html__('Cart', 'cena'); ?> : <?php echo WC()->cart->get_cart_subtotal();?></span>
     
    <?php $fragments['#cart span.sub-title'] = ob_get_clean();

    return $fragments;

});

// breadcrumb for woocommerce page
if (!function_exists('cena_tbay_woocommerce_breadcrumb_defaults')) {
    function cena_tbay_woocommerce_breadcrumb_defaults($args)
    {
        $breadcrumb_img = cena_tbay_get_config('woo_breadcrumb_image');
        $breadcrumb_color = cena_tbay_get_config('woo_breadcrumb_color');
        $style = array();
        $img = '';
        if($breadcrumb_color) {
            $style[] = 'background-color:'.$breadcrumb_color;
        }
        if (isset($breadcrumb_img['url']) && !empty($breadcrumb_img['url'])) {
            $img = '<img src="'.esc_url($breadcrumb_img['url']).'">';
        }
        $estyle = !empty($style) ? ' style="'.implode(";", $style).'"' : "";

        $args['wrap_before'] = '<section id="tbay-breadscrumb" class="tbay-breadscrumb"><div class="container">'.$img.'<div class="breadscrumb-inner"'.$estyle.'><ol class="tbay-woocommerce-breadcrumb breadcrumb" ' . (is_single() ? 'itemprop="breadcrumb"' : '') . '>';
        $args['wrap_after'] = '</ol></div></div></section>';

        return $args;
    }
}
add_filter('woocommerce_breadcrumb_defaults', 'cena_tbay_woocommerce_breadcrumb_defaults');
add_action('cena_woo_template_main_before', 'woocommerce_breadcrumb', 30, 0);

if (!function_exists('cena_tbay_woocommerce_get_display_mode')) {
    add_action('woocommerce_before_shop_loop', 'cena_tbay_woocommerce_get_display_mode', 2);
    function cena_tbay_woocommerce_get_display_mode()
    {

        $woo_mode = cena_tbay_get_config('product_display_mode', 'grid');
        if(!empty($woo_mode)) {
            $woo_mode = 'grid';
        }

        if (isset($_COOKIE['display_mode']) && ($_COOKIE['display_mode'] == 'list' || $_COOKIE['display_mode'] == 'grid')) {
            $woo_mode = $_COOKIE['display_mode'];
        }

        if(wp_is_mobile()) {
            $woo_mode = 'grid';
        }

        return $woo_mode;
    }
}

if (!function_exists('cena_tbay_woocommerce_show_sidebar_btn')) {
    add_action('woocommerce_before_shop_loop', 'cena_tbay_woocommerce_show_sidebar_btn', 2);
    function cena_tbay_woocommerce_show_sidebar_btn()
    {

        $sidebar_configs = cena_tbay_get_woocommerce_layout_configs();

        if ((isset($sidebar_configs['left']['sidebar']) && is_active_sidebar($sidebar_configs['left']['sidebar'])) && (isset($sidebar_configs['right']['sidebar']) && is_active_sidebar($sidebar_configs['right']['sidebar']))) {
            return;
        }

        if(is_product()) {
            return;
        }

        if ((isset($sidebar_configs['left']['sidebar']) && is_active_sidebar($sidebar_configs['left']['sidebar'])) || (isset($sidebar_configs['right']['sidebar']) && is_active_sidebar($sidebar_configs['right']['sidebar']))) :

            $text = cena_tbay_get_config('title_sidebar_mobile', esc_html__('sidebar', 'cena'))
            ?>
            <div class="cena-sidebar-mobile-btn">
                <i class="icon-equalizer icons"></i>
                <span><?php echo trim($text); ?></span>
            </div>
           <?php
        endif;
    }
}

// display woocommerce modes
if (!function_exists('cena_tbay_woocommerce_display_modes')) {
    add_action('woocommerce_before_shop_loop', 'cena_tbay_woocommerce_display_modes', 2);
    function cena_tbay_woocommerce_display_modes()
    {
        global $wp;
        $current_url = add_query_arg($wp->query_string, '', home_url($wp->request));
        $woo_mode = cena_tbay_woocommerce_get_display_mode();
        echo '<form action="javascript:void(0);" class="display-mode" method="get">';
        echo '<button title="'.esc_html__('Grid', 'cena').'" class="change-view grid '.($woo_mode == 'grid' ? 'active' : '').'" value="grid" name="display" type="submit"><i class="fa fa-th"></i></button>';
        echo '<button title="'.esc_html__('List', 'cena').'" class="change-view list '.($woo_mode == 'list' ? 'active' : '').'" value="list" name="display" type="submit"><i class="fa fa-th-list"></i></button>';
        echo '</form>';
    }
}


if (!function_exists('cena_tbay_close_side_woocommerce_show_sidebar_btn')) {
    add_action('wp_footer', 'cena_tbay_close_side_woocommerce_show_sidebar_btn');
    function cena_tbay_close_side_woocommerce_show_sidebar_btn()
    {

        $sidebar_configs = cena_tbay_get_woocommerce_layout_configs();

        if ((isset($sidebar_configs['left']['sidebar']) && is_active_sidebar($sidebar_configs['left']['sidebar'])) && (isset($sidebar_configs['right']['sidebar']) && is_active_sidebar($sidebar_configs['right']['sidebar']))) {
            return;
        }

        if(is_product()) {
            return;
        }

        if ((isset($sidebar_configs['left']['sidebar']) && is_active_sidebar($sidebar_configs['left']['sidebar'])) || (isset($sidebar_configs['right']['sidebar']) && is_active_sidebar($sidebar_configs['right']['sidebar']))) :

            ?>
            <div class="cena-close-side"></div>
           <?php
        endif;
    }
}


if (!function_exists('cena_tbay_header_mobile_side_woocommerce_sidebar')) {
    add_action('cena_after_sidebar_mobile', 'cena_tbay_header_mobile_side_woocommerce_sidebar');
    function cena_tbay_header_mobile_side_woocommerce_sidebar()
    {

        $sidebar_configs = cena_tbay_get_woocommerce_layout_configs();

        if ((isset($sidebar_configs['left']['sidebar']) && is_active_sidebar($sidebar_configs['left']['sidebar'])) && (isset($sidebar_configs['right']['sidebar']) && is_active_sidebar($sidebar_configs['right']['sidebar']))) {
            return;
        }

        if(is_product()) {
            return;
        }

        if ((isset($sidebar_configs['left']['sidebar']) && is_active_sidebar($sidebar_configs['left']['sidebar'])) || (isset($sidebar_configs['right']['sidebar']) && is_active_sidebar($sidebar_configs['right']['sidebar']))) :

            ?>
           <div class="widget-mobile-heading"> <a href="javascript:void(0);" class="close-side-widget"><i class="icon-close icons"></i></a></div>
           <?php
        endif;
    }
}

if(!function_exists('cena_tbay_filter_before')) {
    function cena_tbay_filter_before()
    {
        echo '<div class="tbay-filter">';
    }
}
if(!function_exists('cena_tbay_filter_after')) {
    function cena_tbay_filter_after()
    {
        echo '</div>';
    }
}
add_action('woocommerce_before_shop_loop', 'cena_tbay_filter_before', 1);
add_action('woocommerce_before_shop_loop', 'cena_tbay_filter_after', 40);

// set display mode to cookie
if (!function_exists('cena_tbay_before_woocommerce_init')) {
    function cena_tbay_before_woocommerce_init()
    {
        if(isset($_GET['display']) && ($_GET['display'] == 'list' || $_GET['display'] == 'grid')) {
            setcookie('cena_woo_mode', trim($_GET['display']), time() + 3600 * 24 * 100, '/');
            $_COOKIE['cena_woo_mode'] = trim($_GET['display']);
        }
    }
}
add_action('init', 'cena_tbay_before_woocommerce_init');

// Number of products per page
if (!function_exists('cena_tbay_woocommerce_shop_per_page')) {
    function cena_tbay_woocommerce_shop_per_page($number)
    {
        $value = cena_tbay_get_config('number_products_per_page');
        if (is_numeric($value) && $value) {
            $number = absint($value);
        }
        return $number;
    }
}
add_filter('loop_shop_per_page', 'cena_tbay_woocommerce_shop_per_page');

// Number of products per row
if (!function_exists('cena_tbay_woocommerce_shop_columns')) {
    function cena_tbay_woocommerce_shop_columns($number)
    {
        $value = cena_tbay_get_config('product_columns');
        if (in_array($value, array(2, 3, 4, 6))) {
            $number = $value;
        }
        return $number;
    }
}
add_filter('loop_shop_columns', 'cena_tbay_woocommerce_shop_columns');


// swap effect
if (!function_exists('cena_tbay_swap_images')) {
    function cena_tbay_swap_images()
    {
        global $post, $product, $woocommerce;
        $size = 'woocommerce_thumbnail';
        $placeholder = wc_get_image_size($size);
        $placeholder_width = $placeholder['width'];
        $placeholder_height = $placeholder['height'];
        $post_thumbnail_id =  $product->get_image_id();

        $output = '';
        $class = 'image-no-effect';
        if (has_post_thumbnail()) {
            $attachment_ids = $product->get_gallery_image_ids();
            if ($attachment_ids && isset($attachment_ids[0])) {
                $class = 'image-hover';
                $output .= cena_tbay_get_attachment_image_loaded($attachment_ids[0], 'woocommerce_thumbnail', array('class' => 'attachment-shop_catalog image-effect' ));
            }

            $output .= cena_tbay_get_attachment_image_loaded($post_thumbnail_id, 'woocommerce_thumbnail', array('class' => $class ));
        } else {

            $output .= cena_tbay_src_image_loaded(wc_placeholder_img_src(), array('class' => $class));
        }
        echo trim($output);
    }
}

if (cena_tbay_get_global_config('show_swap_image') && !wp_is_mobile()) {
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
    add_action('woocommerce_before_shop_loop_item_title', 'cena_tbay_swap_images', 10);
}

// layout class for woo page
if (!function_exists('cena_tbay_woocommerce_content_class')) {
    function cena_tbay_woocommerce_content_class($class)
    {
        $page = 'archive';
        if (is_singular('product')) {
            $page = 'single';
        }
        if(cena_tbay_get_config('product_'.$page.'_fullwidth')) {
            return 'container-fluid';
        }
        return $class;
    }
}
add_filter('cena_tbay_woocommerce_content_class', 'cena_tbay_woocommerce_content_class');

// get layout configs
if (!function_exists('cena_tbay_get_woocommerce_layout_configs')) {
    function cena_tbay_get_woocommerce_layout_configs()
    {
        $page = 'archive';
        if (is_singular('product')) {
            $page = 'single';
        }
        $left = cena_tbay_get_config('product_'.$page.'_left_sidebar');
        $right = cena_tbay_get_config('product_'.$page.'_right_sidebar');

        switch (cena_tbay_get_config('product_'.$page.'_layout')) {
            case 'left-main':
                $configs['left'] = array( 'sidebar' => $left, 'class' => 'sidebar-mobile-wrapper col-xs-12 col-md-12 col-lg-3'  );
                $configs['main'] = array( 'class' => 'col-xs-12 col-md-12 col-lg-9' );
                break;
            case 'main-right':
                $configs['right'] = array( 'sidebar' => $right,  'class' => 'sidebar-mobile-wrapper col-xs-12 col-md-12 col-lg-3' );
                $configs['main'] = array( 'class' => 'col-xs-12 col-md-12 col-lg-9' );
                break;
            case 'main':
                $configs['main'] = array( 'class' => 'col-xs-12 col-md-12' );
                break;
            case 'left-main-right':
                $configs['left'] = array( 'sidebar' => $left,  'class' => 'col-xs-12 col-md-12 col-lg-3'  );
                $configs['right'] = array( 'sidebar' => $right, 'class' => 'col-xs-12 col-md-12 col-lg-3' );
                $configs['main'] = array( 'class' => 'col-xs-12 col-md-12 col-lg-6' );
                break;
            default:
                $configs['main'] = array( 'class' => 'col-xs-12 col-md-12' );
                break;
        }

        return $configs;
    }
}

if (!function_exists('cena_tbay_product_review_tab')) {
    function cena_tbay_product_review_tab($tabs)
    {
        if (!cena_tbay_get_config('show_product_review_tab') && isset($tabs['reviews'])) {
            unset($tabs['reviews']);
        }
        return $tabs;
    }
}
add_filter('woocommerce_product_tabs', 'cena_tbay_product_review_tab', 100);

if (!function_exists('cena_tbay_minicart')) {
    function cena_tbay_minicart()
    {
        $template = apply_filters('cena_tbay_minicart_version', '');
        get_template_part('woocommerce/cart/mini-cart-button', $template);
    }
}
// Wishlist

if (!function_exists('cena_tbay_remove_quick_view_default')) {
    function cena_tbay_remove_quick_view_default()
    {
        if (class_exists('YITH_WCQV_Frontend')) {
            remove_action('woocommerce_after_shop_loop_item', array( YITH_WCQV_Frontend(), 'yith_add_quick_view_button' ), 15);
        }
    }
    add_action('init', 'cena_tbay_remove_quick_view_default', 10);
}


//remove heading tab single product
add_filter(
    'woocommerce_product_description_heading',
    'cena_product_description_heading'
);

function cena_product_description_heading()
{
    return '';
}

// share box
if (!function_exists('cena_tbay_woocommerce_share_box')) {
    function cena_tbay_woocommerce_share_box()
    {
        if (cena_tbay_get_config('show_product_social_share')) {
            ?>
              <div class="tbay-social-share">
                <div class="sharethis-inline-share-buttons"></div>
              </div>
            <?php
        }
    }
}
add_filter('woocommerce_single_product_summary', 'cena_tbay_woocommerce_share_box', 100);

/**
 * WooCommerce
 *
 */
if (!function_exists('cena_woo_setup')) {
    add_action('after_setup_theme', 'cena_woo_setup');
    // add_action( 'after_switch_theme', 'cena_woo_setup' );
    function cena_woo_setup()
    {
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');

        if(class_exists('YITH_Woocompare')) {
            update_option('yith_woocompare_compare_button_in_products_list', 'no');
            update_option('yith_woocompare_compare_button_in_product_page', 'no');
            update_option('yith_woocompare_show_compare_button_in', 'product');
            update_option('yith_woocompare_is_button', 'link');
        }

        add_filter('woocommerce_get_image_size_gallery_thumbnail', function ($size) {

            $tbay_thumbnail_width       = get_option('tbay_woocommerce_thumbnail_image_width', 160);
            $tbay_thumbnail_height      = get_option('tbay_woocommerce_thumbnail_image_height', 130);
            $tbay_thumbnail_cropping    = get_option('tbay_woocommerce_thumbnail_cropping', 'yes');
            $tbay_thumbnail_cropping    = ($tbay_thumbnail_cropping == 'yes') ? true : false;

            return array(
                'width'  => $tbay_thumbnail_width,
                'height' => $tbay_thumbnail_height,
                'crop'   => $tbay_thumbnail_cropping,
            );
        });
    }
}

if (!function_exists('cena_woo_image_size_setup')) {
    function cena_woo_image_size_setup()
    {

        $thumbnail_width = 427;
        $main_image_width = 570;
        $cropping_custom_width = 61;
        $cropping_custom_height = 78;

        // Image sizes
        update_option('woocommerce_thumbnail_image_width', $thumbnail_width);
        update_option('woocommerce_single_image_width', $main_image_width);

        update_option('woocommerce_thumbnail_cropping', 'custom');
        update_option('woocommerce_thumbnail_cropping_custom_width', $cropping_custom_width);
        update_option('woocommerce_thumbnail_cropping_custom_height', $cropping_custom_height);

    }
    add_action('after_setup_theme', 'cena_woo_image_size_setup');
}

if(cena_tbay_get_global_config('config_media', false)) {
    remove_action('after_setup_theme', 'cena_woo_image_size_setup');
}

// Ajax Wishlist
if(defined('YITH_WCWL') && ! function_exists('cena_yith_wcwl_ajax_update_count')) {
    function cena_yith_wcwl_ajax_update_count()
    {

        $wishlist_count = (YITH_WCWL_VERSION >= '4.0.0') ? yith_wcwl_count_products() : YITH_WCWL()->count_products();

        wp_send_json(array(
        'count' => $wishlist_count
        ));
    }
    add_action('wp_ajax_yith_wcwl_update_wishlist_count', 'cena_yith_wcwl_ajax_update_count');
    add_action('wp_ajax_nopriv_yith_wcwl_update_wishlist_count', 'cena_yith_wcwl_ajax_update_count');
}

if (! function_exists('cena_woocommerce_saved_sales_price')) {

    add_filter('woocommerce_get_saved_sales_price_html', 'cena_woocommerce_saved_sales_price');

    function cena_woocommerce_saved_sales_price($productid)
    {

        $product = wc_get_product($productid);


        $onsale			= $product->is_on_sale();
        $saleprice 		= $product->get_sale_price();
        $regularprice 	= $product->get_regular_price();
        $priceDiff 		= (int)$regularprice - (int)$saleprice;
        $price 			= '';
        $price1 		= '';

        $off_content	= '';
        if($priceDiff != 0) {
            $price1 = '<span class="saved">'. esc_html__('Save you ', 'cena') .' <span class="price">'. sprintf(get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $priceDiff) . '</span></span>';
            $price .= '<div class="block-save-price">'.$price1.'</div>';
        }

        // Sale price
        return $price;

    }
}

if (! function_exists('cena_get_column_thumbnail_images')) {
    //Column of Thumbnail Images*
    function cena_get_column_thumbnail_images()
    {

        $number_thumbnail = cena_tbay_get_config('number_product_thumbnail', 3);

        $inlineJS = "
            jQuery(document).ready(function($) {
                $('.tbay-image-mains .flex-control-thumbs').owlCarousel({
                    items:              $number_thumbnail,
                    itemsDesktop:       [1600,4],
                    itemsDesktopSmall:  [1200,4], 
                    itemsTablet:        [800,4],
                    itemsTabletSmall:   [650,3],
                    itemsMobile:        [599,2],    
                    loop:               false,
                    dots:               false,      
                    slideSpeed:         200,
                    paginationSpeed:    800,
                    rewindSpeed:        1000,               
                    autoPlay:           false,
                    stopOnHover:        false,          
                    scrollPerPage:      false,
                    pagination:         false,
                    paginationNumbers:  false,
                    mouseDrag:          false,
                    touchDrag:          true,
                    itemsCustom :       false,
                    nav:                true,
                    navText:    ['".esc_html__("Prev", "cena")."', '".esc_html__("Next", "cena")."'],
                    leftOffSet:         -14,
                });
            });
        ";
        wp_add_inline_script('cena-script', $inlineJS);
    }
    // add_action('wp_head', 'cena_get_column_thumbnail_images');
}

if(! function_exists('cena_brands_get_name') && class_exists('YITH_WCBR')) {

    function cena_brands_get_name($product_id)
    {

        $terms = wp_get_post_terms($product_id, 'yith_product_brand');

        $brand = '';
        if(!empty($terms)) {

            $brand  = '<ul class="show-brand">';

            foreach ($terms as $term) {

                $name = $term->name;
                $url = get_term_link($term->slug, 'yith_product_brand');

                $brand  .= '<li><a href='. esc_url($url) .'>'. esc_html($name) .'</a></li>';

            }

            $brand  .= '</ul>';
        }

        echo  trim($brand);

    }

}

add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    ob_start();
    ?>

    <span class="mini-cart-items cart-mobile">
        <?php echo sprintf('%d', WC()->cart->cart_contents_count);?>
    </span>

    <?php $fragments['span.cart-mobile'] = ob_get_clean();

    return $fragments;

});

if (!function_exists('cena_find_matching_product_variation')) {
    function cena_find_matching_product_variation($product, $attributes)
    {

        foreach($attributes as $key => $value) {
            if(strpos($key, 'attribute_') === 0) {
                continue;
            }

            unset($attributes[ $key ]);
            $attributes[ sprintf('attribute_%s', $key) ] = $value;
        }

        if(class_exists('WC_Data_Store')) {

            $data_store = WC_Data_Store::load('product');
            return $data_store->find_matching_product_variation($product, $attributes);

        } else {

            return $product->get_matching_variation($attributes);

        }

    }
}

if (! function_exists('cena_get_default_attributes')) {
    function cena_get_default_attributes($product)
    {

        if(method_exists($product, 'get_default_attributes')) {

            return $product->get_default_attributes();

        } else {

            return $product->get_variation_default_attributes();

        }

    }
}

if (!function_exists('cena_find_matching_product_variation')) {
    function cena_find_matching_product_variation($product, $attributes)
    {

        foreach($attributes as $key => $value) {
            if(strpos($key, 'attribute_') === 0) {
                continue;
            }

            unset($attributes[ $key ]);
            $attributes[ sprintf('attribute_%s', $key) ] = $value;
        }

        if(class_exists('WC_Data_Store')) {

            $data_store = WC_Data_Store::load('product');
            return $data_store->find_matching_product_variation($product, $attributes);

        } else {

            return $product->get_matching_variation($attributes);

        }

    }
}


if (! function_exists('cena_woo_show_product_loop_sale_flash')) {
    /*Change sales woo*/
    add_filter('woocommerce_sale_flash', 'cena_woo_show_product_loop_sale_flash', 10, 3);
    function cena_woo_show_product_loop_sale_flash($html, $post, $product)
    {
        global $product;

        if(empty($product)) {
            return $html;
        }

        $priceDiff = 0;
        $percentDiff = 0;
        $regularPrice = '';
        $salePrice = $percentage = $return_content = '';

        $decimals   =  wc_get_price_decimals();
        $symbol   =  get_woocommerce_currency_symbol();

        $_product_sale   = $product->is_on_sale();
        $featured        = $product->is_featured();

        $format                 =  cena_tbay_get_config('sale_tags', 'custom');
        $enable_label_featured  =  cena_tbay_get_config('enable_label_featured', false);

        $sale_default = '<span>'. esc_html__('Save', 'cena') .'</span>${price-diff}';

        if ($format == 'custom') {
            $format = cena_tbay_get_config('sale_tag_custom', $sale_default);
        }


        if($featured && $enable_label_featured) {
            $return_content  = '<span class="featured featured-saled">'. esc_html__('Hot', 'cena') .'</span>';
        }

        if(!empty($product) && $product->is_type('variable')) {


            $default_attributes = cena_get_default_attributes($product);
            $variation_id = cena_find_matching_product_variation($product, $default_attributes);

            if(!empty($variation_id)) {
                $variation      = wc_get_product($variation_id);

                $_product_sale  = $variation->is_on_sale();

                $regularPrice   = (float) get_post_meta($variation_id, '_regular_price', true);
                $salePrice      = (float) get_post_meta($variation_id, '_price', true);
            } else {
                $_product_sale = false;
            }

        } elseif(!empty($product) && $product->is_type('grouped')) {
            $_product_sale = false;
        } else {
            $salePrice = (float) get_post_meta($product->get_id(), '_price', true);
            $regularPrice = (float) get_post_meta($product->get_id(), '_regular_price', true);
        }



        if (!empty($regularPrice) && !empty($salePrice) && $regularPrice > $salePrice) {
            $priceDiff = $regularPrice - $salePrice;
            $percentDiff = round($priceDiff / $regularPrice * 100);
            $parsed = str_replace('{price-diff}', number_format((float)$priceDiff, $decimals, '.', ''), $format);
            $parsed = str_replace('{symbol}', $symbol, $parsed);
            $parsed = str_replace('{percent-diff}', $percentDiff, $parsed);
            $percentage = '<span class="saled">'. $parsed .'</span>';
        }

        if(!empty($_product_sale) && $_product_sale) {
            $percentage .= $return_content;
        } else {
            $percentage = '<span class="saled">'. esc_html__('Sale', 'cena') . '</span>';
            $percentage .= $return_content;
        }

        return '<span class="onsale">'. $percentage. '</span>';
    }
}

if (! function_exists('cena_woo_only_feature_product')) {
    /*Change sales woo*/
    add_action('woocommerce_before_shop_loop_item_title', 'cena_woo_only_feature_product', 10);
    add_action('woocommerce_before_single_product_summary', 'cena_woo_only_feature_product', 10);
    add_action('yith_wcqv_product_image', 'cena_woo_only_feature_product', 10);
    function cena_woo_only_feature_product()
    {

        global $product;

        $_product_sale   = $product->is_on_sale();

        $featured        = $product->is_featured();

        $return_content = '';
        if($featured && !$_product_sale) {

            $enable_label_featured  =  cena_tbay_get_config('enable_label_featured', false);

            if($featured && $enable_label_featured) {
                $return_content  .= '<span class="featured not-sale">'. cena_tbay_get_config('custom_label_featured', esc_html__('Hot', 'cena')) .'</span>';
            }
            echo '<span class="onsale">'. $return_content. '</span>';
        }

    }
}

/*Remove related products*/

remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
add_action('cena_woo_singular_template_main_after', 'woocommerce_output_related_products', 15);

remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
add_action('cena_woo_singular_template_main_after', 'woocommerce_upsell_display', 10);

if (! function_exists('cena_ajax_product_remove')) {

    // Remove product in the cart using ajax
    function cena_ajax_product_remove()
    {
        // Get mini cart
        ob_start();

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if($cart_item['product_id'] == $_POST['product_id'] && $cart_item_key == $_POST['cart_item_key']) {
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }

        WC()->cart->calculate_totals();
        WC()->cart->maybe_set_cart_cookies();

        woocommerce_mini_cart();

        $mini_cart = ob_get_clean();

        // Fragments and mini cart are returned
        $data = array(
            'fragments' => apply_filters(
                'woocommerce_add_to_cart_fragments',
                array(
                    'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>'
                )
            ),
            'cart_hash' => apply_filters('woocommerce_cart_hash', WC()->cart->get_cart_for_session() ? md5(json_encode(WC()->cart->get_cart_for_session())) : '', WC()->cart->get_cart_for_session())
        );

        wp_send_json($data);

        die();
    }

    add_action('wp_ajax_product_remove', 'cena_ajax_product_remove');
    add_action('wp_ajax_nopriv_product_remove', 'cena_ajax_product_remove');
}

/*product time countdown*/
if(!function_exists('cena_woo_product_single_time_countdown')) {

    add_action('woocommerce_single_product_summary', 'cena_woo_product_single_time_countdown', 25);

    function cena_woo_product_single_time_countdown()
    {

        $style_countdown   = cena_tbay_get_config('show_product_countdown', false);

        if (isset($_GET['countdown'])) {
            $countdown = $_GET['countdown'];
        } else {
            $countdown = $style_countdown;
        }

        if(!$countdown) {
            return '';
        }

        global $product;
        wp_enqueue_script('jquery-countdowntimer');
        $time_sale = get_post_meta($product->get_id(), '_sale_price_dates_to', true);
        ?>
        <?php if ($time_sale): ?>
          <div class="time tbay-single-time">
                <div class="tbay-countdown" data-time="timmer" data-days="<?php esc_attr_e('D', 'cena'); ?>" data-hours="<?php esc_attr_e('H', 'cena'); ?>"  data-mins="<?php esc_attr_e('M', 'cena'); ?>" data-secs="<?php esc_attr_e('S', 'cena'); ?>" 
                   data-date="<?php echo gmdate('m', $time_sale).'-'.gmdate('d', $time_sale).'-'.gmdate('Y', $time_sale).'-'. gmdate('H', $time_sale) . '-' . gmdate('i', $time_sale) . '-' .  gmdate('s', $time_sale) ; ?>" >
              </div>
          </div> 
        <?php endif; ?> 
        <?php
    }
}

/*product nav*/

if(!function_exists('cena_render_product_nav')) {
    function cena_render_product_nav($post, $position)
    {
        if($post) {
            $product = wc_get_product($post->ID);
            $img = '';
            if(has_post_thumbnail($post)) {
                $img = get_the_post_thumbnail($post, 'shop_thumbnail');
            }
            $link = get_permalink($post);
            echo "<div class='{$position} psnav'>";
            echo "<a class='img-link' href=\"{$link}\">";
            echo trim(($position == 'left') ? $img : '');
            echo "</a>";
            echo "  <div class='product_single_nav_inner single_nav'>
                        <a href=\"{$link}\">
                            <span class='name-pr'>{$post->post_title}</span>
                        </a>
                    </div>";
            echo "<a class='img-link' href=\"{$link}\">";
            echo trim(($position == 'right') ? $img : '');
            echo "</a>";
            echo "</div>";
        }
    }
}

if(!function_exists('cena_woo_product_nav')) {
    function cena_woo_product_nav()
    {

        $product_nav   =      cena_tbay_get_config('show_product_nav', false);

        if($product_nav) {
            $prev = get_previous_post();
            $next = get_next_post();

            echo '<div class="product-nav pull-right">';
            echo '<div class="link-images visible-lg">';
            cena_render_product_nav($prev, 'left');
            cena_render_product_nav($next, 'right');
            echo '</div>';



            echo '</div>';
        }
    }

    add_action('woocommerce_before_single_product', 'cena_woo_product_nav', 1);
}

/*Product thumbnail style*/
if (!function_exists('cena_tbay_woocommerce_images_layout_product')) {
    function cena_tbay_woocommerce_images_layout_product($images_layout)
    {
        $sidebar_configs        = cena_tbay_get_woocommerce_layout_configs();
        $style_single_product   = cena_tbay_get_config('style_single_product', 'horizontal');

        if (isset($_GET['style_single_product'])) {
            $images_layout = $_GET['style_single_product'];
        } else {
            $images_layout = $style_single_product;
        }

        return $images_layout;
    }
}

add_filter('woo_images_layout_single_product', 'cena_tbay_woocommerce_images_layout_product');

// Number of products per page
if (!function_exists('cena_tbay_woocommerce_class_single_product')) {
    function cena_tbay_woocommerce_class_single_product($styles)
    {

        $images_layout   =  apply_filters('woo_images_layout_single_product', 10, 2);

        if(isset($images_layout)) {
            $styles = 'style-'.$images_layout;
        }

        return $styles;
    }
}
add_filter('woo_class_single_product', 'cena_tbay_woocommerce_class_single_product');

/*Add video to product detail*/
if (!function_exists('cena_tbay_woocommerce_add_video_field')) {
    add_action('woocommerce_product_options_general_product_data', 'cena_tbay_woocommerce_add_video_field');

    function cena_tbay_woocommerce_add_video_field()
    {

        $args = apply_filters(
            'cena_tbay_woocommerce_simple_url_video_args',
            array(
            'id' => '_video_url',
            'label' => esc_html__('Featured Video URL', 'cena'),
            'placeholder' => esc_html__('Video URL', 'cena'),
            'desc_tip' => true,
            'description' => esc_html__('Enter the video url at https://vimeo.com/ or https://www.youtube.com/', 'cena'))
        );

        echo '<div class="options_group">';

        woocommerce_wp_text_input($args) ;

        echo '</div>';
    }
}

if (!function_exists('cena_tbay_save_video_url')) {
    add_action('woocommerce_process_product_meta', 'cena_tbay_save_video_url', 10, 2);
    function cena_tbay_save_video_url($post_id, $post)
    {
        if (isset($_POST['_video_url'])) {
            update_post_meta($post_id, '_video_url', esc_attr($_POST['_video_url']));
        }
    }
}

if (!function_exists('cena_tbay_VideoUrlType')) {
    function cena_tbay_VideoUrlType($url)
    {


        $yt_rx = '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/';
        $has_match_youtube = preg_match($yt_rx, $url, $yt_matches);


        $vm_rx = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/';
        $has_match_vimeo = preg_match($vm_rx, $url, $vm_matches);


        //Then we want the video id which is:
        if($has_match_youtube) {
            $video_id = $yt_matches[5];
            $type = 'youtube';
        } elseif($has_match_vimeo) {
            $video_id = $vm_matches[5];
            $type = 'vimeo';
        } else {
            $video_id = 0;
            $type = 'none';
        }


        $data['video_id'] = $video_id;
        $data['video_type'] = $type;

        return $data;
    }
}

if (!function_exists('cena_tbay_get_video_product')) {
    add_action('tbay_product_video', 'cena_tbay_get_video_product', 10);
    function cena_tbay_get_video_product()
    {
        global $product;


        if(get_post_meta($product->get_id(), '_video_url', true)) {
            $video = cena_tbay_VideoUrlType(get_post_meta($product->get_id(), '_video_url', true));

            if($video['video_type'] == 'youtube') {
                $url  = 'https://www.youtube.com/embed/'.$video['video_id'].'?autoplay=1';
                $icon = '<i class="fa fa-youtube-play" aria-hidden="true"></i>'.esc_html__('View Video', 'cena');

            } elseif(($video['video_type'] == 'vimeo')) {
                $url = 'https://player.vimeo.com/video/'.$video['video_id'].'?autoplay=1';
                $icon = '<i class="fa fa-youtube-play" aria-hidden="true"></i>'.esc_html__('View Video', 'cena');

            }

        }

        ?>

    <?php if(!empty($url)) : ?>

      <div class="modal fade" id="productvideo">
        <div class="modal-dialog">
          <div class="modal-content tbay-modalContent">

            <div class="modal-body">
              
              <div class="close-button">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div>
              <div class="embed-responsive embed-responsive-16by9">
                          <iframe class="embed-responsive-item" frameborder="0"></iframe>
              </div>
            </div>

          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->

      <button type="button" class="tbay-modalButton" data-toggle="modal" data-tbaySrc="<?php echo esc_attr($url); ?>" data-tbayWidth="640" data-tbayHeight="480" data-target="#productvideo"  data-tbayVideoFullscreen="true"><?php echo trim($icon); ?></button>

    <?php endif; ?>
  <?php
    }
}

if(! function_exists('cena_compare_styles')) {
    add_action('wp_print_styles', 'cena_compare_styles', 200);
    function cena_compare_styles()
    {
        if(! class_exists('YITH_Woocompare')) {
            return;
        }
        $view_action = 'yith-woocompare-view-table';
        if ((! defined('DOING_AJAX') || ! DOING_AJAX) && (! isset($_REQUEST['action']) || $_REQUEST['action'] != $view_action)) {
            return;
        }
        wp_enqueue_style('font-awesome');
        wp_enqueue_style('simple-line-icons');
        wp_enqueue_style('cena-woocommerce');
        wp_enqueue_style('cena-style');
    }
}

if (function_exists('Woo_Variation_Swatches')) {
    deactivate_plugins(plugin_basename('variation-swatches-for-woocommerce'));
}

/* ---------------------------------------------------------------------------
 * WooCommerce - Function More List Product Ajax
 * --------------------------------------------------------------------------- */
if(!function_exists('cena_list_post_ajax_fnc_more_post_ajax')) {
    add_action('wp_ajax_nopriv_cena_list_post_ajax', 'cena_list_post_ajax_fnc_more_post_ajax');
    add_action('wp_ajax_cena_list_post_ajax', 'cena_list_post_ajax_fnc_more_post_ajax');

    function cena_list_post_ajax_fnc_more_post_ajax()
    {
        if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'cena_ajax_nonce') ) {
            exit;
        }

        // prepare our arguments for the query
        $args = json_decode(stripslashes($_POST['query']), true);

        // it is always better to use WP_Query but not here
        query_posts($args);

        $mode = 'list';

        if(have_posts()) :

            while(have_posts()): the_post();

                wc_get_template('content-product.php', array('mode' => $mode));


            endwhile;

        endif;
        die;
    }
}
/* ---------------------------------------------------------------------------
 * WooCommerce - Function More Grid Product Ajax
 * --------------------------------------------------------------------------- */
if(!function_exists('cena_grid_post_ajax_fnc_more_post_ajax')) {
    add_action('wp_ajax_nopriv_cena_grid_post_ajax', 'cena_grid_post_ajax_fnc_more_post_ajax');
    add_action('wp_ajax_cena_grid_post_ajax', 'cena_grid_post_ajax_fnc_more_post_ajax');

    function cena_grid_post_ajax_fnc_more_post_ajax()
    {
        if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'cena_ajax_nonce') ) {
            exit;
        }

        // prepare our arguments for the query
        $args = json_decode(stripslashes($_POST['query']), true);

        // it is always better to use WP_Query but not here
        query_posts($args);

        $mode = 'grid';

        if(have_posts()) :

            while(have_posts()): the_post();

                wc_get_template('content-product.php', array('mode' => $mode));


            endwhile;

        endif;
        die;
    }
}

/*Add The WooCommerce Total Sales Count*/
if(!function_exists('cena_single_product_add_total_sales_count')) {
    function cena_single_product_add_total_sales_count()
    {
        global $product;
        if(!intval(cena_tbay_get_config('enable_total_sales', true)) || $product->get_type() == 'external') {
            return;
        }

        $count = (float) get_post_meta($product->get_id(), 'total_sales', true);

        $text =  sprintf(
            '<span class="rate-sold"><span class="count">%s</span> <span class="sold-text">%s</span></span>',
            number_format_i18n($count),
            esc_html__('sold', 'cena')
        );


        echo trim($text);
    }
    add_action('cena_woo_after_single_rating', 'cena_single_product_add_total_sales_count', 10);
}

if(!function_exists('cena_woocommerce_buy_now')) {
    function cena_woocommerce_buy_now()
    {
        global $product;
        if (! intval(cena_tbay_get_config('enable_buy_now', false))) {
            return;
        }

        if ($product->get_type() == 'external') {
            return;
        }

        $class = 'tbay-buy-now button';

        echo '<button class="'. esc_attr($class) .'">'. esc_html__('Buy Now', 'cena') .'</button>';
        echo '<input type="hidden" value="0" name="cena_buy_now" />';
    }
    add_action('woocommerce_after_add_to_cart_button', 'cena_woocommerce_buy_now', 10);
}

/*Add To Cart Redirect*/
if(!function_exists('cena_woocommerce_buy_now_redirect')) {
    function cena_woocommerce_buy_now_redirect($url)
    {

        if (! isset($_REQUEST['cena_buy_now']) || $_REQUEST['cena_buy_now'] == false) {
            return $url;
        }

        if (empty($_REQUEST['quantity'])) {
            return $url;
        }

        if (is_array($_REQUEST['quantity'])) {
            $quantity_set = false;
            foreach ($_REQUEST['quantity'] as $item => $quantity) {
                if ($quantity <= 0) {
                    continue;
                }
                $quantity_set = true;
            }

            if (! $quantity_set) {
                return $url;
            }
        }

        $redirect = cena_tbay_get_config('redirect_buy_now', 'cart') ;

        switch ($redirect) {
            case 'cart':
                return wc_get_cart_url();

            case 'checkout':
                return wc_get_checkout_url();

            default:
                return wc_get_cart_url();
        }

    }
    add_filter('woocommerce_add_to_cart_redirect', 'cena_woocommerce_buy_now_redirect', 99);
}

// Mobile add to cart message html
if (! function_exists('cena_tbay_add_to_cart_message_html_mobile')) {
    function cena_tbay_add_to_cart_message_html_mobile($message)
    {
        if (isset($_REQUEST['cena_buy_now']) && $_REQUEST['cena_buy_now'] == true) {
            return __return_empty_string();
        }

        $active = cena_tbay_get_config('disable_redirect_add_to_cart', false);

        if ($active && wp_is_mobile() && ! intval(cena_tbay_get_config('enable_buy_now', false))) {
            return __return_empty_string();
        } else {
            return $message;
        }

    }
    add_filter('wc_add_to_cart_message_html', 'cena_tbay_add_to_cart_message_html_mobile');
}

if (! function_exists('cena_gwp_affiliate_id')) {
    function cena_gwp_affiliate_id()
    {
        return 2403;
    }
    add_filter('gwp_affiliate_id', 'cena_gwp_affiliate_id');
}

/*get category by id array*/
if (!function_exists('cena_tbay_get_category_by_id')) {
    function cena_tbay_get_category_by_id($categories_id = array())
    {
        $categories = array();

        if(!is_array($categories_id)) {
            return $categories;
        }

        foreach ($categories_id as $key => $value) {
            $categories[$key] = get_term_by('id', $value, 'product_cat')->slug;
        }

        return $categories;

    }
}


/** Ajax Elementor Addon cena Product Tabs **/
if (! function_exists('cena_get_products_tab_ajax')) {
    function cena_get_products_tab_ajax()
    {
        if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'cena_ajax_nonce') ) {
            exit;
        }

        if (! empty($_POST['atts'])) {

            $atts                   = cena_clean($_POST['atts']);
            $product_type           = cena_clean($_POST['value']);
            $atts['product_type']   = $product_type;

            $data = cena_products_ajax_template($atts);
            echo json_encode($data);
            die();
        }
    }
    add_action('wp_ajax_cena_get_products_tab_shortcode', 'cena_get_products_tab_ajax');
    add_action('wp_ajax_nopriv_cena_get_products_tab_shortcode', 'cena_get_products_tab_ajax');
}

/** Ajax Elementor Addon Product Categories Tabs **/
if (! function_exists('cena_get_products_categories_tab_shortcode')) {
    function cena_get_products_categories_tab_shortcode()
    {
        if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'cena_ajax_nonce') ) {
            exit;
        }

        if (! empty($_POST['atts'])) {

            $atts               = cena_clean($_POST['atts']);
            $categories         = cena_clean($_POST['value']);
            $atts['categories'] = $categories;

            $data = cena_products_ajax_template($atts);
            echo json_encode($data);
            die();
        }
    }
    add_action('wp_ajax_cena_get_products_categories_tab_shortcode', 'cena_get_products_categories_tab_shortcode');
    add_action('wp_ajax_nopriv_cena_get_products_categories_tab_shortcode', 'cena_get_products_categories_tab_shortcode');
}



if (! function_exists('cena_products_ajax_template')) {
    function cena_products_ajax_template($settings)
    {
        $categories = isset($settings['categories']) ? $settings['categories'] : '';
        $cat_operator = isset($settings['cat_operator']) ? $settings['cat_operator'] : 'IN';
        $product_type = isset($settings['product_type']) ? $settings['product_type'] : 'newest';
        $limit = isset($settings['limit']) ? $settings['limit'] : '';
        $orderby = isset($settings['orderby']) ? $settings['orderby'] : '';
        $order = isset($settings['order']) ? $settings['order'] : '';

        $responsive = isset($settings['responsive']) ? $settings['responsive'] : [];
        $data_carousel = isset($settings['data_carousel']) ? $settings['data_carousel'] : [];

        $columns = isset($data_carousel['columns']) ? $data_carousel['columns'] : 4;
        $rows = isset($data_carousel['rows']) ? $data_carousel['rows'] : 1;
        $pagi_type = isset($data_carousel['pagi_type']) ? $data_carousel['pagi_type'] : '';
        $nav_type = isset($data_carousel['nav_type']) ? $data_carousel['nav_type'] : '';

        $screen_desktop = isset($responsive['screen_desktop']) ? $responsive['screen_desktop'] : 4;
        $screen_desktopsmall = isset($responsive['screen_desktopsmall']) ? $responsive['screen_desktopsmall'] : 3;
        $screen_tablet = isset($responsive['screen_tablet']) ? $responsive['screen_tablet'] : 2;
        $screen_mobile = isset($responsive['screen_mobile']) ? $responsive['screen_mobile'] : 1;
        $number = isset($settings['number']) ? $settings['number'] : 10;

        $layout_type = isset($settings['layout_type']) ? $settings['layout_type'] : 'grid';
        $allowed_layouts = ['grid', 'list', 'carousel', 'carousel-special', 'special']; 
        if (!in_array($layout_type, $allowed_layouts)) {
            $layout_type = 'grid';
        }

        $layout_type = preg_replace('/[^a-zA-Z0-9_-]/', '', $layout_type);


        $loop = cena_get_query_products($categories, $cat_operator, $product_type, $limit, $orderby, $order);

        ob_start();

        if($loop->have_posts()) :
            wc_get_template('layout-products/'. $layout_type .'.php', array( 
                'loop' => $loop, 
                'columns' => $columns, 
                'rows' => $rows, 
                'pagi_type' => $pagi_type, 
                'nav_type' => $nav_type,
                'screen_desktop' => $screen_desktop,
                'screen_desktopsmall' => $screen_desktopsmall,
                'screen_tablet' => $screen_tablet,
                'screen_mobile' => $screen_mobile, 
                'number' => $number 
            ));
        endif;

        wc_reset_loop();
        wp_reset_postdata();

        return [
            'html' => ob_get_clean(),
        ];
    }
}
/*YITH Wishlist*/
if (! function_exists('cena_custom_wishlist_icon_html')) {
    function cena_custom_wishlist_icon_html($html)
    {
        $icon               = get_option('yith_wcwl_add_to_wishlist_icon');
        $custom_icon        = get_option('yith_wcwl_add_to_wishlist_custom_icon');
        if ((class_exists('YITH_WCWL') && apply_filters('tbay_yith_wcwl_remove_text', true)) && 'custom' === $icon && empty($custom_icon)) {
            return '<i class="fa fa-heart-o"></i>';
        } else {
            return $html;
        }
    }
    add_filter('yith_wcwl_add_to_wishlist_icon_html', 'cena_custom_wishlist_icon_html', 10, 1);
}

if (! function_exists('cena_custom_add_to_wishlist_icon_html')) {
    function cena_custom_add_to_wishlist_icon_html($html)
    {
        $icon                       = get_option('yith_wcwl_added_to_wishlist_custom_icon');
        $custom_icon          = get_option('yith_wcwl_added_to_wishlist_custom_icon');
        if ((class_exists('YITH_WCWL') && apply_filters('tbay_yith_wcwl_remove_text', true)) && 'custom' === $icon && empty($custom_icon)) {
            return '<i class="fa fa-heart-o"></i>';
        } else {
            return $html;
        }
    }
    add_filter('yith_wcwl_add_to_wishlist_heading_icon_html', 'cena_custom_add_to_wishlist_icon_html', 10, 1);
}
if (! function_exists('cena_remove_wishlist_text')) {
    function cena_remove_wishlist_text($text)
    {
        if(class_exists('YITH_WCWL') && apply_filters('tbay_yith_wcwl_remove_text', true)) {
            return '';
        } else {
            return $text;
        }
    }
    add_filter('yith_wcwl_product_already_in_wishlist_text_button', 'cena_remove_wishlist_text', 10, 1);
    add_filter('yith_wcwl_product_added_to_wishlist_message_button', 'cena_remove_wishlist_text', 10, 1);
}
if (! function_exists('cena_custom_label_add_to_wishlist')) {
    function cena_custom_label_add_to_wishlist($text)
    {
        $text_custom                       = esc_html__('Wishlist', 'cena');

        if (class_exists('YITH_WCWL') && apply_filters('tbay_yith_wcwl_remove_text', true)) {
            return $text_custom;
        } else {
            return $text;
        }
    }
    add_filter('yith_wcwl_browse_wishlist_label', 'cena_custom_label_add_to_wishlist', 10, 1);
    add_filter('yith_wcwl_button_label', 'cena_custom_label_add_to_wishlist', 10, 1);
    add_filter('yith_wcwl_view_wishlist_label', 'cena_custom_label_add_to_wishlist', 10, 1);
}


if (!function_exists('maia_update_yith_wishlist_40')) {
    function maia_update_yith_wishlist_40()
    {
        update_option('yith_wcwl_add_to_wishlist_icon_type', 'default');
        update_option('yith_wcwl_added_to_wishlist_icon_type', 'default');
        update_option('yith_wcwl_add_to_wishlist_icon', 'heart-outline');
        update_option('yith_wcwl_added_to_wishlist_icon', 'heart-outline');
    }
}

if (!function_exists('maia_update_fix_new_plugin')) {
    add_action('after_setup_theme', 'maia_update_fix_new_plugin', 10);
    function maia_update_fix_new_plugin()
    {
        $current_theme_version = wp_get_theme()->get('Version');

        $stored_theme_version = get_option('maia_theme_version_fix_wishlist');

        if ($current_theme_version !== $stored_theme_version) {
            maia_update_yith_wishlist_40();

            update_option('maia_theme_version_fix_wishlist', $current_theme_version);
        }
    }
}


if (! function_exists('cena_remove_compare_button_from_loop')) {
    add_action( 'init', 'cena_remove_compare_button_from_loop', 20 );
    function cena_remove_compare_button_from_loop() {
        if ( class_exists( 'YITH_Woocompare_Frontend' ) ) {
            $frontend = new YITH_WooCompare_Frontend();
            global $yith_woocompare;
            
            remove_action( 'woocommerce_after_shop_loop_item', array( $yith_woocompare->obj, 'output_button' ), 20 );
            add_action( 'cena_before_add_to_compare_button', array( $frontend, 'output_button' ), 10 );
        }
    }
}