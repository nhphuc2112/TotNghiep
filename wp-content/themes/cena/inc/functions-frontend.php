<?php

if ( ! function_exists( 'cena_tbay_category' ) ) {
	function cena_tbay_category( $post ) {
		// format
		$post_format = get_post_format();
		$header_class = $post_format ? '' : 'border-left';
		echo '<span class="category "> ';
		$cat = wp_get_post_categories( $post->ID );
		$k   = count( $cat );
		foreach ( $cat as $c ) {
			$categories = get_category( $c );
			$k -= 1;
			if ( $k == 0 ) {
				echo '<a href="' . get_category_link( $categories->term_id ) . '" class="categories-name"><i class="fa fa-bar-chart"></i>' . esc_html($categories->name) . '</a>';
			} else {
				echo '<a href="' . get_category_link( $categories->term_id ) . '" class="categories-name"><i class="fa fa-bar-chart"></i>' . esc_html($categories->name) . ', </a>';
			}
		}
		echo '</span>';
	}
}

if ( ! function_exists( 'cena_tbay_center_meta' ) ) {
	function cena_tbay_center_meta( $post ) {
		// format
		$post_format = get_post_format();
		$id = get_the_author_meta( 'ID' );
		echo '<div class="entry-meta">';
			the_title( '<h4 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h4>' );
		
			echo "<div class='entry-create'>";
			echo "<span class='entry-date'>". get_the_date( 'M d, Y' ).'</span>';
			"<span class='author'>". esc_html_e('/ By ', 'cena'); the_author_posts_link() .'</span>';
			echo '</div>';
		echo '</div>';
	}
}



if ( ! function_exists( 'cena_tbay_full_top_meta' ) ) {
	function cena_tbay_full_top_meta( $post ) {
		// format
		$post_format = get_post_format();
		$header_class = $post_format ? '' : 'border-left';
		echo '<header class="entry-header-top ' . esc_attr($header_class) . '">';
		if(!is_single()){
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		}
		// details
		$id = get_the_author_meta( 'ID' );
		echo '<span class="entry-profile"><span class="col"><span class="entry-author-link"><strong>' . esc_html__( 'By:', 'cena' ) . '</strong><span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url( $id )) . '" rel="author">' . get_the_author() . '</a></span></span><span class="entry-date"><strong>Posted: </strong>' . esc_html( get_the_date( 'M jS, Y' ) ) . '</span></span></span>';
		// comments
		echo '<span class="entry-categories"><strong>'. esc_html__('In:', 'cena') .'</strong> ';
		$cat = wp_get_post_categories( $post->ID );
		$k   = count( $cat );
		foreach ( $cat as $c ) {
			$categories = get_category( $c );
			$k -= 1;
			if ( $k == 0 ) {
				echo '<a href="' . get_category_link( $categories->term_id ) . '" class="categories-name">' . esc_html($categories->name) . '</a>';
			} else {
				echo '<a href="' . get_category_link( $categories->term_id ) . '" class="categories-name">' . esc_html($categories->name) . ', </a>';
			}
		}
		echo '</span>';
		if ( ! is_search() ) {
			if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
				echo '<span class="entry-comments-link">';
				comments_popup_link( esc_html__( '0', 'cena' ), esc_html__( '1', 'cena' ), esc_html__( '%', 'cena' ) );
				echo '</span>';
			}
		}
		echo '</header>';
	}
}

if ( ! function_exists( 'cena_tbay_post_tags' ) ) {
	function cena_tbay_post_tags() {
		$posttags = get_the_tags();
		if ( $posttags ) {
			echo '<span class="entry-tags-list"><span class="meta-title">'. esc_html__('Tags: ', 'cena') .'</span>';
			
			$size = count( $posttags );
			foreach ( $posttags as $tag ) {
				echo '<a href="' . get_tag_link( $tag->term_id ) . '">';
				echo esc_html($tag->name);
				echo '</a>';
			}
			echo '</span>';
		}
	}
}

if ( ! function_exists( 'cena_tbay_post_share_box' ) ) {
  function cena_tbay_post_share_box() {
      if ( cena_tbay_get_config('show_blog_social_share') ) {
          ?>
            <div class="tbay-post-share">
            	<span class="meta-title"><?php esc_html_e('Share: ', 'cena'); ?></span>
              	<div class="sharethis-inline-share-buttons"></div>
            </div>
          <?php
      }
  }
}


if ( ! function_exists( 'cena_tbay_post_format_link_helper' ) ) {
	function cena_tbay_post_format_link_helper( $content = null, $title = null, $post = null ) {
		if ( ! $content ) {
			$post = get_post( $post );
			$title = $post->post_title;
			$content = $post->post_content;
		}
		$link = cena_tbay_get_first_url_from_string( $content );
		if ( ! empty( $link ) ) {
			$title = '<a href="' . esc_url( $link ) . '" rel="bookmark">' . $title . '</a>';
			$content = str_replace( $link, '', $content );
		} else {
			$pattern = '/^\<a[^>](.*?)>(.*?)<\/a>/i';
			preg_match( $pattern, $content, $link );
			if ( ! empty( $link[0] ) && ! empty( $link[2] ) ) {
				$title = $link[0];
				$content = str_replace( $link[0], '', $content );
			} elseif ( ! empty( $link[0] ) && ! empty( $link[1] ) ) {
				$atts = shortcode_parse_atts( $link[1] );
				$target = ( ! empty( $atts['target'] ) ) ? $atts['target'] : '_self';
				$title = ( ! empty( $atts['title'] ) ) ? $atts['title'] : $title;
				$title = '<a href="' . esc_url( $atts['href'] ) . '" rel="bookmark" target="' . $target . '">' . $title . '</a>';
				$content = str_replace( $link[0], '', $content );
			} else {
				$title = '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $title . '</a>';
			}
		}
		$out['title'] = '<h2 class="entry-title">' . $title . '</h2>';
		$out['content'] = $content;

		return $out;
	}
}

if ( ! function_exists( 'cena_tbay_show_title_page' ) ) {
	function cena_tbay_show_title_page() {
		global $post;
		$title = '';
		if ( is_page() && is_object($post) ) {
			$show = get_post_meta( $post->ID, 'tbay_page_show_breadcrumb', true );
			if ( $show == 'no' ) {
				return ''; 
			}

			$title = the_title( '<header class="page-header"><h1 class="page-title">', '</h1></header>' );

		}
		
		return $title;
	}
}

if ( ! function_exists( 'cena_tbay_breadcrumbs' ) ) {
	function cena_tbay_breadcrumbs() {

		$delimiter = ' / ';
		$home = esc_html__('Home', 'cena');
		$before = '<li class="active">';
		$after = '</li>';
		$title = '';
		if (!is_home() && !is_front_page() || is_paged()) {

			echo '<ol class="breadcrumb">';

			global $post;
			$homeLink =  home_url();
			echo '<li><a href="' . esc_url($homeLink) . '">' . esc_html($home) . '</a> ' . esc_html($delimiter) . '</li> ';

			if (is_category()) {
				global $wp_query;
				$cat_obj = $wp_query->get_queried_object();
				$thisCat = $cat_obj->term_id;
				$thisCat = get_category($thisCat);
				$parentCat = get_category($thisCat->parent);
				if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
				echo trim($before) . single_cat_title('', false) . $after;
				$title = single_cat_title('', false);
			} elseif (is_day()) {
				echo '<li><a href="' . esc_url( get_year_link(get_the_time('Y')) ) . '">' . get_the_time('Y') . '</a></li> ' . $delimiter . ' ';
				echo '<li><a href="' . esc_url( get_month_link(get_the_time('Y'),get_the_time('m')) ) . '">' . get_the_time('F') . '</a></li> ' . $delimiter . ' ';
				echo trim($before) . get_the_time('d') . $after;
				$title = get_the_time('d');
			} elseif (is_month()) {
				echo '<a href="' . esc_url( get_year_link(get_the_time('Y')) ) . '">' . get_the_time('Y') . '</a></li> ' . $delimiter . ' ';
				echo trim($before) . get_the_time('F') . $after;
				$title = get_the_time('F');
			} elseif (is_year()) {
				echo trim($before) . get_the_time('Y') . $after;
				$title = get_the_time('Y');
			} elseif (is_single() && !is_attachment()) {
				if ( get_post_type() != 'post' ) {
					$post_type = get_post_type_object(get_post_type());
					$slug = $post_type->rewrite;
					echo '<li><a href="' . esc_url($homeLink . '/' . $slug['slug']) . '/">' . esc_html($post_type->labels->singular_name) . '</a></li> ' . esc_html($delimiter) . ' ';
					echo trim($before) . get_the_title() . $after;
        } else {
            $delimiter = '';
            $cat = get_the_category();
            if( !empty( $cat[0] ) ) {
                echo '<li>'.get_category_parents($cat[0]->term_id, true, ' ' . $delimiter . ' ').'</li>';
            }
        }
				$title = get_the_title();
			} elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
				$post_type = get_post_type_object(get_post_type());
				if (is_object($post_type)) {
					echo trim($before) . $post_type->labels->singular_name . $after;
					$title = $post_type->labels->singular_name;
				}
			} elseif (is_attachment()) {
			    $parent = get_post($post->post_parent);
			    $cat = get_the_category($parent->ID); 
			    if( isset($cat) && !empty($cat) ) {
			     $cat = $cat[0];
			     echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
			    }
			    echo '<a href="' . esc_url( get_permalink($parent->ID) ) . '">' . esc_html($parent->post_title) . '</a></li> ' . esc_html($delimiter) . ' ';
			    echo trim($before) . get_the_title() . $after;
			    $title = get_the_title();
			} elseif ( is_page() && !$post->post_parent ) {
				echo trim($before) . get_the_title() . $after;
				$title = get_the_title();

			} elseif ( is_page() && $post->post_parent ) {
				$parent_id  = $post->post_parent;
				$breadcrumbs = array();
				while ($parent_id) {
					$page = get_post($parent_id);
					$breadcrumbs[] = '<a href="' . esc_url( get_permalink($page->ID) ) . '">' . get_the_title($page->ID) . '</a></li>';
					$parent_id  = $page->post_parent;
				}
				$breadcrumbs = array_reverse($breadcrumbs);
				foreach ($breadcrumbs as $crumb) echo trim($crumb) . ' ' . trim($delimiter) . ' ';
				echo trim($before) . get_the_title() . $after;
				$title = get_the_title();
			} elseif ( is_search() ) {
				echo trim($before) . esc_html__('Search results for "','cena')  . get_search_query() . '"' . $after;
				$title = esc_html__('Search results for "','cena')  . get_search_query();
			} elseif ( is_tag() ) {
				echo trim($before) . esc_html__('Posts tagged "', 'cena'). single_tag_title('', false) . '"' . $after;
				$title = esc_html__('Posts tagged "', 'cena'). single_tag_title('', false) . '"';
			} elseif ( is_author() ) {
				global $author;
				$userdata = get_userdata($author);
				echo trim($before) . esc_html__('Articles posted by ', 'cena') . $userdata->display_name . $after;
				$title = esc_html__('Articles posted by ', 'cena') . $userdata->display_name;
			} elseif ( is_404() ) {
				echo trim($before) . esc_html__('Error 404', 'cena') . $after;
				$title = esc_html__('Error 404', 'cena');
			}

			echo '</ol>';
		}
	}
}

if ( ! function_exists( 'cena_tbay_render_breadcrumbs' ) ) {
	function cena_tbay_render_breadcrumbs() {
		global $post;

		$show = true;
		$img = '';
		$style = array();
		if ( is_page() && is_object($post) ) {
			$show = get_post_meta( $post->ID, 'tbay_page_show_breadcrumb', true );
			if ( $show == 'no' ) {
				return ''; 
			}
			$bgimage = get_post_meta( $post->ID, 'tbay_page_breadcrumb_image', true );
			$bgcolor = get_post_meta( $post->ID, 'tbay_page_breadcrumb_color', true );
			$style = array();
			if( $bgcolor  ){
				$style[] = 'background-color:'.$bgcolor;
			}
			if( $bgimage  ){ 
				$img = ' <img src="'.esc_url($bgimage).'">  ';
			}

		} elseif ( is_singular('post') || is_category() || is_home() || is_tag() || is_author() || is_day() || is_month() || is_year()  || is_search() ) {
			$show = cena_tbay_get_config('show_blog_breadcrumbs', true);
			if ( !$show  ) {
				return ''; 
			}
			$breadcrumb_img = cena_tbay_get_config('blog_breadcrumb_image');
	        $breadcrumb_color = cena_tbay_get_config('blog_breadcrumb_color');
	        $style = array();
	        if( $breadcrumb_color  ){
	            $style[] = 'background-color:'.$breadcrumb_color;
	        }
	        if ( isset($breadcrumb_img['url']) && !empty($breadcrumb_img['url']) ) {
	            $img = ' <img src="'.esc_url($breadcrumb_img['url']).'">  ';
	        }
		}
		
		$estyle = !empty($style)? ' style="'.implode(";", $style).'"':"";

		echo '<section id="tbay-breadscrumb" class="tbay-breadscrumb"><div class="container">'. esc_html($img).'<div class="p-relative breadscrumb-inner" '.$estyle.'>';
			cena_tbay_breadcrumbs();
		echo '</div></div></section>';
	}
}

if ( !function_exists( 'cena_tbay_print_style_footer' ) ) {
	function cena_tbay_print_style_footer() {
    	$footer = cena_tbay_get_footer_layout();
    	if ( $footer ) {
    		$args = array(
				'name'        => $footer,
				'post_type'   => 'tbay_footer',
				'post_status' => 'publish',
				'numberposts' => 1
			);
			$posts = get_posts($args);
			foreach ( $posts as $post ) {
	    		return get_post_meta( $post->ID, '_wpb_shortcodes_custom_css', true );
	 	 	}
    	}
	}
  	add_action('wp_head', 'cena_tbay_print_style_footer', 18);
}

if ( ! function_exists( 'cena_tbay_paging_nav' ) ) {
	function cena_tbay_paging_nav() {
		global $wp_query, $wp_rewrite;

		if ( $wp_query->max_num_pages < 2 ) {
			return;
		}

		$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$query_args   = array();
		$url_parts    = explode( '?', $pagenum_link );

		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}

		$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
		$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

		$format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

		// Set up paginated links.
		$links = paginate_links( array(
			'base'     => $pagenum_link,
			'format'   => $format,
			'total'    => $wp_query->max_num_pages,
			'current'  => $paged,
			'mid_size' => 1,
			'add_args' => array_map( 'urlencode', $query_args ),
			'prev_text' => esc_html__( '&larr; Previous', 'cena' ),
			'next_text' => esc_html__( 'Next &rarr;', 'cena' ),
		) );

		if ( $links ) :

		?>
		<nav class="navigation paging-navigation" role="navigation">
			<h1 class="screen-reader-text hidden"><?php esc_html_e( 'Posts navigation', 'cena' ); ?></h1>
			<div class="tbay-pagination">
				<?php echo trim($links); ?>
			</div><!-- .pagination -->
		</nav><!-- .navigation -->
		<?php
		endif;
	}
}

if ( ! function_exists( 'cena_tbay_post_nav' ) ) {
	function cena_tbay_post_nav() {
		// Don't print empty markup if there's nowhere to navigate.
		$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
		$next     = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous ) {
			return;
		}

		?>
		<nav class="navigation post-navigation" role="navigation">
			<h3 class="screen-reader-text"><?php esc_html_e( 'Post navigation', 'cena' ); ?></h3>
			<div class="nav-links clearfix">
				<?php
				if ( is_attachment() ) :
					previous_post_link( '%link','<div class="col-lg-6"><span class="meta-nav">'. esc_html__('Published In', 'cena').'</span></div>');
				else :
					previous_post_link( '%link','<div class="pull-left"><span class="meta-nav">'. esc_html__('Previous Post', 'cena').'</span></div>' );
					next_post_link( '%link', '<div class="pull-right"><span class="meta-nav">' . esc_html__('Next Post', 'cena').'</span><span></span></div>');
				endif;
				?>
			</div><!-- .nav-links -->
		</nav><!-- .navigation -->
		<?php
	}
}

if ( !function_exists('cena_tbay_pagination') ) {
    function cena_tbay_pagination($per_page, $total, $max_num_pages = '') {
    	global $wp_query, $wp_rewrite;
        ?>
        <div class="tbay-pagination text-center">
        	<?php
        	$prev = esc_html__('Previous','cena');
        	$next = esc_html__('Next','cena');
        	$pages = $max_num_pages;
        	$args = array('class'=>'');

        	$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
	        if ( empty($pages) ) {
	            global $wp_query;
	            $pages = $wp_query->max_num_pages;
	            if ( !$pages ) {
	                $pages = 1;
	            }
	        }
	        $pagination = array(
	            'base' => @add_query_arg('paged','%#%'),
	            'format' => '',
	            'total' => $pages,
	            'current' => $current,
	            'prev_text' => $prev,
	            'next_text' => $next,
	            'type' => 'array'
	        );

	        if( $wp_rewrite->using_permalinks() ) {
	            $pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
	        }
	        
	        if ( isset($_GET['s']) ) {
	            $cq = $_GET['s'];
	            $sq = str_replace(" ", "+", $cq);
	        }
	        
	        if ( !empty($wp_query->query_vars['s']) ) {
	            $pagination['add_args'] = array( 's' => $sq);
	        }
	        $paginations = paginate_links( $pagination );
	        if ( !empty($paginations) ) {
	            echo '<div class="pagination '.esc_attr( $args["class"] ).'">';
	                foreach ($paginations as $key => $pg) {
	                    echo trim($pg);
	                }
	            echo '</div>';
	        }
        	?>
            
        </div>
    <?php
    }
}

if ( !function_exists('cena_tbay_comment_form') ) {
	function cena_tbay_comment_form($arg, $class = 'btn-primary btn-outline ') {
		global $post;
		if ('open' == $post->comment_status) {
			ob_start();
	      	comment_form($arg);
	      	$form = ob_get_clean();
	      	?>
	      	<div class="commentform row reset-button-default">
		    	<div class="col-sm-12">
			    	<?php
						echo str_replace('id="submit"','id="submit" class="btn '. esc_attr($class) .'"', $form);
			      	?>
		      	</div>
	      	</div>
	      	<?php
	      }
	}
}

if (!function_exists('cena_tbay_list_comment') ) {
	function cena_tbay_list_comment($comment, $args, $depth) {
		if ( is_file(get_template_directory().'/list_comments.php') ) {
	        require get_template_directory().'/list_comments.php';
      	}
	}
}

function cena_tbay_display_footer_builder($footer) {
	global $footer_builder;
	$footer_builder = true;
	$args = array(
		'name'        => $footer,
		'post_type'   => 'tbay_footer',
		'post_status' => 'publish',
		'numberposts' => 1
	);
	$posts = get_posts($args);
	foreach ( $posts as $post ) {
		echo do_shortcode( $post->post_content );
	}
	$footer_builder = false;
}

/*product one page body class*/
if ( ! function_exists( 'cena_body_class_hidden_footer' ) ) {
  function cena_body_class_hidden_footer( $classes ) {

    if(cena_tbay_get_config('hidden_footer',false)) {
      $classes[] = 'mobile-hidden-footer';
    }
    return $classes;

  }
  add_filter( 'body_class', 'cena_body_class_hidden_footer',99 );
}

if ( ! function_exists( 'cena_tbay_get_menu_mobile_icon' ) ) {
	function cena_tbay_get_menu_mobile_icon( $ouput) {

		$menu_option            = apply_filters( 'cena_menu_mobile_option', 10 );

		$ouput = '';
		if( $menu_option == 'smart_menu' ) {

			$ouput 	.= '<a href="#tbay-mobile-menu-navbar" class="btn btn-sm btn-danger btn-click-menu">';
			$ouput  .= '<i class="fa fa-bars"></i>';
			$ouput  .= '</a>';			

			$ouput 	.= '<a href="#page" class="btn btn-sm btn-danger">';
			$ouput  .= '<i class="fa fa-close"></i>';
			$ouput  .= '</a>';

		}
		else {
			$ouput 	.= '<button data-toggle="offcanvas" class="btn btn-sm btn-danger btn-offcanvas btn-toggle-canvas offcanvas" type="button"><i class="fa fa-bars"></i></button>';
			
		}

		return $ouput;

	}

	add_filter( 'cena_get_menu_mobile_icon', 'cena_tbay_get_menu_mobile_icon',99 );
}

if( ! function_exists('cena_load_html_dropdowns_action') ) {
	function cena_load_html_dropdowns_action() {
		if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'cena_ajax_nonce') ) {
            exit;
        }

		$response = array(
			'status' => 'error',
			'message' => 'Can\'t load HTML blocks with AJAX',
			'data' => array(),
		); 

		if( cena_vc_is_activated() ) {
            WPBMap::addAllMappedShortcodes();
        }

		if( isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ) {
			$ids = cena_clean( $_POST['ids'] ); 
			foreach ($ids as $id) {   
				$id = (int) $id;
				
				$content = cena_get_html_custom_post($id);

				if( ! $content ) continue;

				$response['status'] = 'success';
				$response['message'] = 'At least one HTML block loaded';
				$response['data'][$id] = $content;
			}
		}    

		echo json_encode($response);

		die();
	}
	add_action( 'wp_ajax_cena_load_html_dropdowns', 'cena_load_html_dropdowns_action' );
	add_action( 'wp_ajax_nopriv_cena_load_html_dropdowns', 'cena_load_html_dropdowns_action' );
}





if( ! function_exists( 'cena_get_html_custom_post' ) ) {
	function cena_get_html_custom_post($id) { 
        if( is_null($id) ) return;
        
        $post = get_post( $id );

        return do_shortcode($post->post_content);
	}

}


/*Get title mobile in top bar mobile*/
if ( ! function_exists( 'cena_tbay_get_title_mobile' ) ) {
    function cena_tbay_get_title_mobile( $title = '') {
        $delimiter = ' / ';

        if (  ( cena_is_woocommerce_activated() && is_product_category() ) || is_category() ) {
            $title = single_cat_title();
        }  else if ( is_search() ) {
            $title = esc_html__('Search results for "','cena')  . get_search_query();
        } else if ( is_tag() ) {
            $title = esc_html__('Posts tagged "', 'cena'). single_tag_title('', false) . '"';
        } else if ( cena_is_woocommerce_activated() && is_product_tag() ) {
            $title = esc_html__('Product tagged "', 'cena'). single_tag_title('', false) . '"';
        } else if ( is_author() ) {
            global $author;
            $userdata = get_userdata($author);
            $title = esc_html__('Articles posted by ', 'cena') . $userdata->display_name;
        } else if ( is_404() ) {
            $title = esc_html__('Error 404', 'cena');
        } else if( cena_is_woocommerce_activated() && is_shop () ) {
            $title = esc_html__('shop','cena');
        } else if (is_category()) {
            global $wp_query;
            $cat_obj = $wp_query->get_queried_object();
            $thisCat = $cat_obj->term_id;
            $thisCat = get_category($thisCat);
            $parentCat = get_category($thisCat->parent);
            if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
            $title = single_cat_title('', false);
            
        } elseif (is_day()) {
            $title = get_the_time('d');
        } elseif (is_month()) {
            $title = get_the_time('F');
        } elseif (is_year()) {
            $title = get_the_time('Y');
        } elseif ( is_single()  && !is_attachment()) {
            $title = get_the_title();
        } else {
            $title = get_the_title();
        }
        
        return $title;
    }
    add_filter( 'cena_get_filter_title_mobile', 'cena_tbay_get_title_mobile', 10, 1 );
}


if (!function_exists('cena_logout_without_confirm')) {
    add_action('check_admin_referer', 'cena_logout_without_confirm', 10, 2);
    function cena_logout_without_confirm($action, $result)
    {
        /**
         * Allow logout without confirmation
         */
        if ($action == "log-out" && !isset($_GET['_wpnonce'])) {
            $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : home_url();
            $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));
            header("Location: $location");
            die;
        }
    }
}