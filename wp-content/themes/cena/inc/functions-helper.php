<?php

if ( ! function_exists( 'cena_tbay_body_classes' ) ) {
	function cena_tbay_body_classes( $classes ) {
		global $post;
		if ( is_page() && is_object($post) ) {
			$class = get_post_meta( $post->ID, 'tbay_page_extra_class', true );
			if ( !empty($class) ) {
				$classes[] = trim($class);
			}
		}
		if ( cena_tbay_get_config('preload') ) {
			$classes[] = 'tbay-body-loader';
		}

		if( !defined('CENA_TBAY_FRAMEWORK_ACTIVED') ) {
			$classes[] = 'tbay-body-default';
	   }

		return $classes;
	}
	add_filter( 'body_class', 'cena_tbay_body_classes' );
}

if ( ! function_exists( 'cena_tbay_get_shortcode_regex' ) ) {
	function cena_tbay_get_shortcode_regex( $tagregexp = '' ) {
		// WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
		// Also, see shortcode_unautop() and shortcode.js.
		return
			'\\['                                // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($tagregexp)"                     // 2: Shortcode name
			. '(?![\\w-])'                       // Not followed by word character or hyphen
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
			. '[^\\]\\/]*'                   // Not a closing bracket or forward slash
			. '(?:'
			. '\\/(?!\\])'               // A forward slash not followed by a closing bracket
			. '[^\\]\\/]*'               // Not a closing bracket or forward slash
			. ')*?'
			. ')'
			. '(?:'
			. '(\\/)'                        // 4: Self closing tag ...
			. '\\]'                          // ... and closing bracket
			. '|'
			. '\\]'                          // Closing bracket
			. '(?:'
			. '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			. '[^\\[]*+'             // Not an opening bracket
			. '(?:'
			. '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			. '[^\\[]*+'         // Not an opening bracket
			. ')*+'
			. ')'
			. '\\[\\/\\2\\]'             // Closing shortcode tag
			. ')?'
			. ')'
			. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
	}
}

if ( ! function_exists( 'cena_tbay_tagregexp' ) ) {
	function cena_tbay_tagregexp() {
		return apply_filters( 'cena_tbay_custom_tagregexp', 'video|audio|playlist|video-playlist|embed|cena_tbay_media' );
	}
}

if ( !function_exists('cena_tbay_class_container_vc') ) {
	function cena_tbay_class_container_vc($class, $isfullwidth, $post_type) {
		global $post;
		$isfullwidth = false;
		if ( $post_type == 'tbay_megamenu' ) {
			$isfullwidth = false;
		} elseif ( $post_type == 'tbay_footer' ) {
			$isfullwidth = false;
		} else {
			if ( is_page() ) {
				$isfullwidth  = get_post_meta( $post->ID, 'tbay_page_fullwidth', true );
				if ( $isfullwidth == 'no' ) {
					$isfullwidth = false;
				} else {
					$isfullwidth = true;
				}
			} elseif ( is_woocommerce() ) {
				if ( is_singular('product') ) {
					$isfullwidth  = cena_tbay_get_config( 'product_single_fullwidth', false );
				} else {
					$isfullwidth  = cena_tbay_get_config( 'product_archive_fullwidth', false );
				}
			} else {
				if ( is_singular('post') ) {
					$isfullwidth  = cena_tbay_get_config( 'post_single_fullwidth', false );
				} else {
					$isfullwidth  = cena_tbay_get_config( 'post_archive_fullwidth', false );
				}
			}
		}

		if ( $isfullwidth ) {
			return 'tbay-'.$class;
		}
		return $class;
	}
}
add_filter( 'cena_tbay_class_container_vc', 'cena_tbay_class_container_vc', 1, 3);


if ( !function_exists('cena_tbay_get_header_layouts') ) {
	function cena_tbay_get_header_layouts() {
		$headers = array();
		$files = glob( get_template_directory() . '/headers/*.php' );
	    if ( !empty( $files ) ) {
	        foreach ( $files as $file ) {
	        	$header = str_replace( '.php', '', basename($file) );
	            $headers[$header] = $header;
	        }
	    }
		return $headers;
	}
}

if (!function_exists('cena_is_cmb2')) {
    function cena_is_cmb2() {
        return defined( 'CMB2_LOADED' ) ? true : false;
    }
}

if (!function_exists('cena_vc_is_activated')) {
    function cena_vc_is_activated()
    {
        return class_exists('Vc_Manager');
    }
}

if (!function_exists('cena_is_woocommerce_activated')) {
    function cena_is_woocommerce_activated() {
        return class_exists('WooCommerce') ? true : false;
    }
}

if ( ! function_exists( 'cena_clean' ) ) {
	function cena_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'cena_clean', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}
}

if ( !function_exists('cena_tbay_get_header_layout') ) {
	function cena_tbay_get_header_layout() {
		global $post;
		if ( is_page() && is_object($post) && isset($post->ID) ) {
			return cena_tbay_page_header_layout();
		} else if( class_exists( 'WooCommerce' ) && is_shop() ) {
			return cena_tbay_woo_get_header_layout( wc_get_page_id( 'shop' ) );
		} else if( class_exists( 'WooCommerce' ) && is_cart() ) {
			return cena_tbay_woo_get_header_layout( wc_get_page_id( 'cart' ) );
		} else if( class_exists( 'WooCommerce' ) && is_checkout() ) {
			return cena_tbay_woo_get_header_layout( wc_get_page_id( 'checkout' ) );
		}

		return cena_tbay_get_config('header_type');
	}
	add_filter( 'cena_tbay_get_header_layout', 'cena_tbay_get_header_layout' );
}

if ( !function_exists('cena_tbay_woo_get_header_layout') ) {
	function cena_tbay_woo_get_header_layout( $page_id ) {
		$header = get_post_meta( $page_id, 'tbay_page_header_type', true );

		if ( $header == 'global' ||  $header == '') {
			return cena_tbay_get_config('header_type', 'header_default');
		} else {
			return $header;
		}
	}
}


if ( !function_exists('cena_tbay_get_footer_layouts') ) {
	function cena_tbay_get_footer_layouts() {
		$footers = array( '' => esc_html__('Default', 'cena'));
		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => 'tbay_footer',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			$footers[$post->post_name] = $post->post_title;
		}
		return $footers;
	}
}

if ( !function_exists('cena_tbay_get_footer_layout') ) {
	function cena_tbay_get_footer_layout() {

		if ( is_page() ) {
			global $post;
			$footer = '';
			if ( is_object($post) && isset($post->ID) ) {
				$footer = get_post_meta( $post->ID, 'tbay_page_footer_type', true );
				if ( $footer == 'global' ) {
					return cena_tbay_get_config('footer_type', '');
				}
			}
			return $footer;
		} else if( class_exists( 'WooCommerce' ) && is_shop() ) {
			return cena_tbay_woo_get_footer_layout( wc_get_page_id( 'shop' ) );
		} else if( class_exists( 'WooCommerce' ) && is_cart() ) {
			return cena_tbay_woo_get_footer_layout( wc_get_page_id( 'cart' ) );
		} else if( class_exists( 'WooCommerce' ) && is_checkout() ) {
			return cena_tbay_woo_get_footer_layout( wc_get_page_id( 'checkout' ) );
		}

		return cena_tbay_get_config('footer_type', '');
	}
	add_filter('cena_tbay_get_footer_layout', 'cena_tbay_get_footer_layout');
}

if ( !function_exists('cena_tbay_woo_get_footer_layout') ) {
	function cena_tbay_woo_get_footer_layout( $page_id ) {
		$footer = get_post_meta( $page_id, 'tbay_page_footer_type', true );

		if ( $footer == 'global' ||  $footer == '') {
			return cena_tbay_get_config('footer_type', 'footer_default');
		} else {
			return $footer;
		}
	}
}

if ( !function_exists('cena_tbay_blog_content_class') ) {
	function cena_tbay_blog_content_class( $class ) {
		$page = 'archive';
		if ( is_singular( 'post' ) ) {
            $page = 'single';
        }
		if ( cena_tbay_get_config('blog_'.$page.'_fullwidth') ) {
			return 'container-fluid';
		}
		return $class;
	}
}
add_filter( 'cena_tbay_blog_content_class', 'cena_tbay_blog_content_class', 1 , 1  );


if ( !function_exists('cena_tbay_get_blog_layout_configs') ) {
	function cena_tbay_get_blog_layout_configs() {
		$page = 'archive';
		if ( is_singular( 'post' ) ) {
            $page = 'single';
        }
		$left = cena_tbay_get_config('blog_'.$page.'_left_sidebar');
		$right = cena_tbay_get_config('blog_'.$page.'_right_sidebar');

		switch ( cena_tbay_get_config('blog_'.$page.'_layout') ) {
		 	case 'left-main':
		 		$configs['left'] = array( 'sidebar' => $left, 'class' => 'col-xs-12 col-md-12 col-lg-3'  );
		 		$configs['main'] = array( 'class' => 'col-xs-12 col-md-12 col-lg-9' );
		 		break;
		 	case 'main-right':
		 		$configs['right'] = array( 'sidebar' => $right,  'class' => 'col-xs-12 col-md-12 col-lg-3' ); 
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

if ( !function_exists('cena_tbay_page_content_class') ) {
	function cena_tbay_page_content_class( $class ) {
		global $post;
		$fullwidth = get_post_meta( $post->ID, 'tbay_page_fullwidth', true );
		if ( !$fullwidth || $fullwidth == 'no' ) {
			return $class;
		}
		return 'container-fluid';
	}
}
add_filter( 'cena_tbay_page_content_class', 'cena_tbay_page_content_class', 1 , 1  );

if ( !function_exists('cena_tbay_get_page_layout_configs') ) {
	function cena_tbay_get_page_layout_configs() {
		global $post;
		if( isset($post->ID) ) {
			$left = get_post_meta( $post->ID, 'tbay_page_left_sidebar', true );
			$right = get_post_meta( $post->ID, 'tbay_page_right_sidebar', true );

			switch ( get_post_meta( $post->ID, 'tbay_page_layout', true ) ) {
				case 'left-main':
					$configs['left'] = array( 'sidebar' => $left, 'class' => 'col-xs-12 col-md-12 col-lg-3'  );
					$configs['main'] = array( 'class' => 'col-xs-12 col-md-12 col-lg-9' );
					break;
				case 'main-right':
					$configs['right'] = array( 'sidebar' => $right,  'class' => 'col-xs-12 col-md-12 col-lg-3' ); 
					$configs['main'] = array( 'class' => 'col-xs-12 col-md-12 col-lg-9' );
					break;
				case 'main':
					$configs['main'] = array( 'class' => 'clearfix' );
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
}

if ( !function_exists('cena_tbay_page_header_layout') ) {
	function cena_tbay_page_header_layout() {
		global $post;
		$header = get_post_meta( $post->ID, 'tbay_page_header_type', true );
		if ( $header == 'global' ) {
			return cena_tbay_get_config('header_type');
		}
		return $header;
	}
}

if ( ! function_exists( 'cena_tbay_get_first_url_from_string' ) ) {
	function cena_tbay_get_first_url_from_string( $string ) {
		$pattern = "/^\b(?:(?:https?|ftp):\/\/)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
		preg_match( $pattern, $string, $link );

		return ( ! empty( $link[0] ) ) ? $link[0] : false;
	}
}

if ( !function_exists( 'cena_tbay_get_link_attributes' ) ) {
	function cena_tbay_get_link_attributes( $string ) {
		preg_match( '/<a href="(.*?)">/i', $string, $atts );

		return ( ! empty( $atts[1] ) ) ? $atts[1] : '';
	}
}

if ( !function_exists( 'cena_tbay_post_media' ) ) {
	function cena_tbay_post_media( $content ) {
		$is_video = ( get_post_format() == 'video' ) ? true : false;
		$media = cena_tbay_get_first_url_from_string( $content );
		if ( ! empty( $media ) ) {
			global $wp_embed;
			$content = do_shortcode( $wp_embed->run_shortcode( '[embed]' . $media . '[/embed]' ) );
		} else {
			$pattern = cena_tbay_get_shortcode_regex( cena_tbay_tagregexp() );
			preg_match( '/' . $pattern . '/s', $content, $media );
			if ( ! empty( $media[2] ) ) {
				if ( $media[2] == 'embed' ) {
					global $wp_embed;
					$content = do_shortcode( $wp_embed->run_shortcode( $media[0] ) );
				} else {
					$content = do_shortcode( $media[0] );
				}
			}
		}
		if ( ! empty( $media ) ) {
			$output = '<div class="entry-media">';
			$output .= ( $is_video ) ? '<div class="pro-fluid"><div class="pro-fluid-inner">' : '';
			$output .= $content;
			$output .= ( $is_video ) ? '</div></div>' : '';
			$output .= '</div>';

			return $output;
		}

		return false;
	}
}

if ( !function_exists( 'cena_tbay_post_gallery' ) ) {
	function cena_tbay_post_gallery( $content ) {
		$pattern = cena_tbay_get_shortcode_regex( 'gallery' );
		preg_match( '/' . $pattern . '/s', $content, $media );
		if ( ! empty( $media[2] )  ) {
			return '<div class="entry-gallery">' . do_shortcode( $media[0] ) . '<hr class="pro-clear" /></div>';
		}

		return false;
	}
}

if ( !function_exists( 'cena_tbay_random_key' ) ) {
    function cena_tbay_random_key($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $return = '';
        for ($i = 0; $i < $length; $i++) {
            $return .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $return;
    }
}

if ( !function_exists('cena_tbay_substring') ) {
    function cena_tbay_substring($string, $limit, $afterlimit = '[...]') {
        if ( empty($string) ) {
        	return $string;
        }
       	$string = explode(' ', strip_tags( $string ), $limit);

        if (count($string) >= $limit) {
            array_pop($string);
            $string = implode(" ", $string) .' '. $afterlimit;
        } else {
            $string = implode(" ", $string);
        }
        $string = preg_replace('`[[^]]*]`','',$string);
        return strip_shortcodes( $string );
    }
}

if ( !function_exists( 'cena_tbay_autocomplete_search' ) ) {
    function cena_tbay_autocomplete_search() {

        if ( cena_tbay_get_global_config('autocomplete_search', false) ) {
        	$suffix 		= (cena_tbay_get_config('minified_js', false)) ? '.min' : CENA_MIN_JS;
            wp_register_script( 'cena-autocomplete-js', CENA_SCRIPTS . '/autocomplete-search-init' . $suffix . '.js', array('jquery','jquery-ui-autocomplete'), null, true);
            wp_enqueue_script( 'cena-autocomplete-js' );

            add_action( 'wp_ajax_cena_autocomplete_search', 'cena_tbay_autocomplete_suggestions' );
            add_action( 'wp_ajax_nopriv_cena_autocomplete_search', 'cena_tbay_autocomplete_suggestions' );
        }
    }
}
add_action( 'init', 'cena_tbay_autocomplete_search' );

if ( !function_exists( 'cena_tbay_autocomplete_suggestions' ) ) {
    function cena_tbay_autocomplete_suggestions() {
		if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'cena_ajax_nonce') ) {
            exit;
        }

		// Query for suggestions
        $search_keyword  = $_REQUEST['term'];
        $category_keyword  = $_REQUEST['category'];

        $args = array(
            's'                   	=> $search_keyword,
            'post_status'         	=> 'publish', 
            'orderby'         		=> 'relevance',
            'posts_per_page'      	=> -1,
            'ignore_sticky_posts' 	=> 1,
            'suppress_filters'    	=> false, 
        );

        if( cena_tbay_get_config('search_in_options', 'only_title') === 'all' ) {
	       	$args_sku      = array(
				'post_type'        => 'product',
				'posts_per_page'   => -1,
				'meta_query'       => array(
					array(
						'key'     => '_sku',
						'value'   => trim( $search_keyword ),
						'compare' => 'like',
					),
				),
				'suppress_filters' => 0,
				'category_name'    => $category_keyword,
			);

			$args_variation_sku = array(
				'post_type'        => 'product_variation',
				'posts_per_page'   => -1,
				'meta_query'       => array(
					array(
						'key'     => '_sku',
						'value'   => trim( $search_keyword ),
						'compare' => 'like',
					),
				),
				'suppress_filters' => 0,
			);
        }


        if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] != 'all') {
        	$args['post_type'] = $_REQUEST['post_type'];
        } 

		if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'product' && class_exists( 'WooCommerce' ) ) {
			$args['meta_query'] = WC()->query->get_meta_query();
			$args['tax_query'] 	= WC()->query->get_tax_query();

			$product_visibility_term_ids = wc_get_product_visibility_term_ids();
			$args['tax_query']['relation'] = 'AND';

			$args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_term_ids['exclude-from-search'],
				'operator' => 'NOT IN',
			); 
			
            if ( ! empty( $_REQUEST['category'] ) ) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => strip_tags( $_REQUEST['category'] ),
                );
            }
		}


        $posts = get_posts( $args );

        if( cena_tbay_get_config('search_in_options', 'only_title') === 'all' ) { 
	       	$products_sku = get_posts( $args_sku );
	       	$products_variation_sku = get_posts( $args_variation_sku );
        	$posts   = array_merge( $posts, $products_sku, $products_variation_sku );	
        }

        
        $suggestions = array();
        $show_image = cena_tbay_get_config('show_search_product_image', true);
        $show_price = cena_tbay_get_config('search_type') == 'product' ? cena_tbay_get_config('show_search_product_price') : false;
        $number 	= cena_tbay_get_config('search_max_number_results', 5); 

        global $post;
        $count = count($posts);

        $size_image = 'thumbnail';
        if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] === 'product') {
        	$size_image = 'shop_thumbnail';
        }
        
        $view_all = ( ($count - $number ) > 0 ) ? true : false;
        $index = 0;
        foreach ($posts as $post): setup_postdata($post);

        	if( $index == $number ) break;
            
            $suggestion = array();
            $suggestion['label'] = esc_html($post->post_title);
            $suggestion['link'] = get_permalink();
            $suggestion['result'] = $count.' '. esc_html__('result found with', 'cena') .' <span>"'.$search_keyword.'"</span> ';
            $suggestion['view_all'] = $view_all;
            if ( $show_image && has_post_thumbnail( $post->ID ) ) {
                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size_image );
                $suggestion['image'] = $image[0];
            } else {
                $suggestion['image'] = '';
            }
            if ( $show_price ) {
             	global $product;
                $suggestion['price'] = $product->get_price_html();
            } else {
                $suggestion['price'] = '';
            }
 
            $suggestions[]= $suggestion;

            $index++;
        endforeach;
        
        $response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";
        echo trim($response);
     
        exit;
    }
}

/*Check in home page*/
if ( !function_exists('cena_tbay_is_home_page') ) {
	function cena_tbay_is_home_page() {
		$is_home = false;

		if( is_home() || is_front_page() || is_page( 'home-1' ) || is_page( 'home-2' ) || is_page( 'home-3' ) || is_page( 'home-4' ) || is_page( 'home-5' ) || is_page( 'home-6' ) || is_page( 'home-7' ) || is_page( 'home-8' ) || is_page( 'home-9' ) || is_page( 'home-10' ) ) {
			$is_home = true;
		}

		return $is_home;
	}
}

if ( !function_exists('cena_tbay_share_js') ) {
	function cena_tbay_share_js() {
		 if ( is_single()   ) {
		 			echo cena_tbay_get_config('code_share');
		 	}
	}
	add_action('wp_head', 'cena_tbay_share_js');
}

/*Get Preloader*/
if ( ! function_exists( 'cena_get_select_preloader' ) ) {
	add_action( 'wp_body_open', 'cena_get_select_preloader', 10 );
    function cena_get_select_preloader( ) {

    	$enable_preload = cena_tbay_get_global_config('preload',false);

    	if( !$enable_preload ) return;

    	$preloader 	= cena_tbay_get_global_config('select_preloader', 1);
    	$media 		= cena_tbay_get_global_config('media-preloader');
 
    	if( isset($preloader) ) {
	    	switch ($preloader) {
	    		case 'loader1': 
	    			?>
	                <div class="tbay-page-loader">
					  	<div id="loader"></div>
					  	<div class="loader-section section-left"></div>
					  	<div class="loader-section section-right"></div>
					</div>
	    			<?php
	    			break;    		

	    		case 'loader2':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-two">
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    </div>
					</div>
	    			<?php
	    			break;    		
	    		case 'loader3':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-three">
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    	<span></span>
					    </div>
					</div>
	    			<?php
	    			break;    		
	    		case 'loader4':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-four"> <span class="spinner-cube spinner-cube1"></span> <span class="spinner-cube spinner-cube2"></span> <span class="spinner-cube spinner-cube3"></span> <span class="spinner-cube spinner-cube4"></span> <span class="spinner-cube spinner-cube5"></span> <span class="spinner-cube spinner-cube6"></span> <span class="spinner-cube spinner-cube7"></span> <span class="spinner-cube spinner-cube8"></span> <span class="spinner-cube spinner-cube9"></span> </div>
					</div>
	    			<?php
	    			break;    		
	    		case 'loader5':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-five"> <span class="spinner-cube-1 spinner-cube"></span> <span class="spinner-cube-2 spinner-cube"></span> <span class="spinner-cube-4 spinner-cube"></span> <span class="spinner-cube-3 spinner-cube"></span> </div>
					</div>
	    			<?php
	    			break;    		
	    		case 'loader6':
	    			?>
					<div class="tbay-page-loader">
					    <div class="tbay-loader tbay-loader-six"> <span class=" spinner-cube-1 spinner-cube"></span> <span class=" spinner-cube-2 spinner-cube"></span> </div>
					</div>
	    			<?php
	    			break;

	    		case 'custom_image':
	    			?>
					<div class="tbay-page-loader loader-img">
						<?php if( isset($media['url']) && !empty($media['url']) ): ?>
					   		<img src="<?php echo esc_url($media['url']); ?>">
						<?php endif; ?>
					</div>
	    			<?php
	    			break;
	    			
	    		default:
	    			?>
	    			<div class="tbay-page-loader">
					  	<div id="loader"></div>
					  	<div class="loader-section section-left"></div>
					  	<div class="loader-section section-right"></div>
					</div>
	    			<?php
	    			break;
	    	}
	    }
    }
}

// Number of blog per row
if ( !function_exists('cena_tbay_blog_loop_columns') ) {
    function cena_tbay_blog_loop_columns($number) {

    		$sidebar_configs = cena_tbay_get_blog_layout_configs();

    		$columns 	= cena_tbay_get_config('blog_columns', 1);

        if( isset($_GET['blog_columns']) && is_numeric($_GET['blog_columns']) ) {
            $value = $_GET['blog_columns']; 
        } elseif( empty($columns) && isset($sidebar_configs['columns']) ) {
    			$value = 	$sidebar_configs['columns']; 
    		} else {
          $value = $columns;          
        }

        if ( in_array( $value, array(1, 2, 3, 4, 5, 6) ) ) {
            $number = $value;
        }
        return $number;
    }
}
add_filter( 'loop_blog_columns', 'cena_tbay_blog_loop_columns' );

if ( !function_exists('cena_tbay_menu_mobile_type') ) {
    function cena_tbay_menu_mobile_type() {
    	
        $option = cena_tbay_get_config('menu_mobile_type', 'smart_menu');
        $option = (isset($_GET['menu_mobile_type'])) ? $_GET['menu_mobile_type'] : $option;

        return $option;
    }
}
add_filter( 'cena_menu_mobile_option', 'cena_tbay_menu_mobile_type', 10, 3 );

if ( !function_exists('cena_tbay_get_attachment_image_loaded') ) {
	function cena_tbay_get_attachment_image_loaded($attachment_id, $size = 'thumbnail', $attr = '', $echo = true) {

		$html = '';
		$image = wp_get_attachment_image_src($attachment_id, $size);
		if ( $image ) {
			list($src, $width, $height) = $image;
			$hwstring = image_hwstring($width, $height);
			$size_class = $size;
			if ( is_array( $size_class ) ) {
				$size_class = join( 'x', $size_class );
			}

			$src_blank = 'data:image/svg+xml;charset=utf-8,%3Csvg xmlns%3D&#039;http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg&#039; viewBox%3D&#039;0 0 '. $width .' '. $height .'&#039;%2F%3E';


			$attachment = get_post($attachment_id);
			$default_attr = array(
				'src'	=> $src_blank,
				'data-src'	=> $src,
				'class'	=> "attachment-$size_class size-$size_class",
				'alt'	=> trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
			);

			if( !cena_tbay_get_global_config('enable_lazyloadimage',false) ) {
				$default_attr['src'] = $src;
				unset($default_attr['data-src']);
			}

			$attr = wp_parse_args( $attr, $default_attr );


			$attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $size );

			if( cena_tbay_get_global_config('enable_lazyloadimage',false) ) {
				$attr['class'] = $attr['class']. ' unveil-image';
			}

			
			$attr = array_map( 'esc_attr', $attr );
			$html = rtrim("<img $hwstring");
			foreach ( $attr as $name => $value ) {
				$html .= " $name=" . '"' . $value . '"';
			}
			$html .= ' />';
		}

		if( $echo ) {
			echo trim($html);
		} else {
			return $html;
		}

	}
}


if ( !function_exists('cena_tbay_src_image_loaded') ) {
	function cena_tbay_src_image_loaded($src, $attr = '', $hwstring ='' , $echo = true)  {

		$src_blank = 'data:image/svg+xml;charset=utf-8,%3Csvg xmlns%3D&#039;http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg&#039; viewBox%3D&#039;0 0 600 400&#039;%2F%3E';



		$default_attr = array(
			'src'	=> $src_blank,
			'data-src'	=> $src,
			'class'	=> '',
		);

		if( !cena_tbay_get_global_config('enable_lazyloadimage',false) ) {
			$default_attr['src'] = $src;
			unset($default_attr['data-src']);
		}


		$attr = wp_parse_args( $attr, $default_attr );

		if( cena_tbay_get_global_config('enable_lazyloadimage',false) ) {
			$attr['class'] = $attr['class']. ' unveil-image';
		}

		$attr = array_map( 'esc_attr', $attr );
		$html = rtrim("<img $hwstring");
		foreach ( $attr as $name => $value ) {
			$html .= " $name=" . '"' . $value . '"';
		}
		$html .= ' />';

		if( $echo ) {
			echo trim($html);
		} else {
			return $html;
		}
		
	}
}


if (!function_exists('cena_elements_ajax_tabs')) {
    function cena_elements_ajax_tabs()
    { 
        $array = [
            'product-categories-tabs',  
            'product-tabs',
        ];

        return $array;
    }
}

if (! function_exists('cena_get_transliterate')) {
    function cena_get_transliterate( $slug ) {
        $slug = urldecode($slug);

        if (function_exists('iconv') && defined('ICONV_IMPL') && @strcasecmp(ICONV_IMPL, 'unknown') !== 0) {
            $slug = iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', $slug);
        }

        return $slug;
    }
}

if ( !function_exists('cena_demo_rocket_lazyload_exclude_class') ) {
	function cena_demo_rocket_lazyload_exclude_class( $attributes ) {
		$attributes[] = 'class="logo-img"';
		$attributes[] = 'class="logo-mobile-img"';

		return $attributes;
	}
	add_filter( 'rocket_lazyload_excluded_attributes', 'cena_demo_rocket_lazyload_exclude_class' );
}


if (! function_exists('cena_nav_menu_get_menu_class')) {
    function cena_nav_menu_get_menu_class()
    { 
 
		$menu_class    = ' menu nav navbar-nav megamenu flex-row';
		
		return  $menu_class;
    }
}
if ( ! function_exists( 'cena_clear_transient' ) ) {
	function cena_clear_transient() {
		delete_transient( 'cena-hash-time' );
	} 
	add_action( 'wp_update_nav_menu_item', 'cena_clear_transient', 11, 1 );
}  