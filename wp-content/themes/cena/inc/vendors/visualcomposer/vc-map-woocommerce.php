<?php
if(!class_exists('WPBakeryShortCode')) return;

if ( class_exists( 'WooCommerce' ) ) {
	if ( !function_exists('cena_tbay_vc_get_term_object')) {
		function cena_tbay_vc_get_term_object($term) { 
			$vc_taxonomies_types = vc_taxonomies_types();

			return array(
				'label' => $term->name,
				'value' => $term->term_id,
				'group_id' => $term->taxonomy,
				'group' => isset( $vc_taxonomies_types[ $term->taxonomy ], $vc_taxonomies_types[ $term->taxonomy ]->labels, $vc_taxonomies_types[ $term->taxonomy ]->labels->name ) ? $vc_taxonomies_types[ $term->taxonomy ]->labels->name : esc_html__( 'Taxonomies', 'cena' ),
			);
		}
	}

	if ( !function_exists('cena_tbay_category_field_search')) {
		function cena_tbay_category_field_search( $search_string ) {
			$data = array();
			$vc_taxonomies_types = array('product_cat');
			$vc_taxonomies = get_terms( $vc_taxonomies_types, array(
				'hide_empty' => false,
				'search' => $search_string
			) );
			if ( is_array( $vc_taxonomies ) && ! empty( $vc_taxonomies ) ) {
				foreach ( $vc_taxonomies as $t ) {
					if ( is_object( $t ) ) {
						$data[] = cena_tbay_vc_get_term_object( $t );
					}
				}
			}
			return $data;
		}
	}

	if ( !function_exists('cena_tbay_category_render')) {
		function cena_tbay_category_render($query) {  
			$category = get_term_by('id', (int)$query['value'], 'product_cat');
			if ( ! empty( $query ) && !empty($category)) {
				$data = array();
				$data['value'] = $category->slug;
				$data['label'] = $category->name;
				return ! empty( $data ) ? $data : false;
			}
			return false;
		}
	}

	$bases = array( 'tbay_productstabs', 'tbay_products', 'tbay_product_countdown' );
	foreach( $bases as $base ){   
		add_filter( 'vc_autocomplete_'.$base .'_categories_callback', 'cena_tbay_category_field_search', 10, 1 );
	 	add_filter( 'vc_autocomplete_'.$base .'_categories_render', 'cena_tbay_category_render', 10, 1 );
	}

	if ( !function_exists('cena_tbay_woocommerce_get_categories') ) {
	    function cena_tbay_woocommerce_get_categories() {
	        $return = array( esc_html__(' --- Choose a Category --- ', 'cena') );

	        $args = array(
	            'type' => 'post',
	            'child_of' => 0,
	            'orderby' => 'name',
	            'order' => 'ASC',
	            'hide_empty' => false,
	            'hierarchical' => 1,
	            'taxonomy' => 'product_cat'
	        );

	        $categories = get_categories( $args );
	        cena_tbay_get_category_childs( $categories, 0, 0, $return );

	        return $return;
	    }
	}

	if ( !function_exists('cena_tbay_get_category_childs') ) {
	    function cena_tbay_get_category_childs( $categories, $id_parent, $level, &$dropdown ) {
	        foreach ( $categories as $key => $category ) {
	            if ( $category->category_parent == $id_parent ) {
	                $dropdown = array_merge( $dropdown, array( str_repeat( "- ", $level ) . $category->name . ' (' .$category->count .')' => $category->term_id ) );
	                unset($categories[$key]);
	                cena_tbay_get_category_childs( $categories, $category->term_id, $level + 1, $dropdown );
	            }
	        }
	    }
	}

	if ( !function_exists('cena_tbay_load_woocommerce_element')) {
		function cena_tbay_load_woocommerce_element() {
			$categories = cena_tbay_woocommerce_get_categories();
			$orderbys = array(
				esc_html__( 'Date', 'cena' ) => 'date',
				esc_html__( 'Price', 'cena' ) => 'price',
				esc_html__( 'Random', 'cena' ) => 'rand',
				esc_html__( 'Sales', 'cena' ) => 'sales',
				esc_html__( 'ID', 'cena' ) => 'ID'
			);

			$orderways = array(
				esc_html__( 'Descending', 'cena' ) => 'DESC',
				esc_html__( 'Ascending', 'cena' ) => 'ASC',
			);
			$layouts = array(
				'Grid'=>'grid',
				'Special'=>'special',
				'List'=>'list',
				'Carousel'=>'carousel',
				'Carousel Special'=>'carousel-special'
			);
			$types = array(
				'Best Selling' => 'best_selling',
				'Featured Products' => 'featured_product',
				'Top Rate' => 'top_rate',
				'Recent Products' => 'recent_product',
				'On Sale' => 'on_sale',
				'Recent Review' => 'recent_review'
			);

			$producttabs = array(
	            array( 'recent_product', esc_html__('Latest Products', 'cena') ),
	            array( 'featured_product', esc_html__('Featured Products', 'cena') ),
	            array( 'best_selling', esc_html__('BestSeller Products', 'cena') ),
	            array( 'top_rate', esc_html__('TopRated Products', 'cena') ),
	            array( 'on_sale', esc_html__('On Sale Products', 'cena') )
	        );
			$columns = array(1,2,3,4,6);
			$rows 	 = array(1,2,3);
			vc_map( array(
		        "name" => esc_html__("Tbay Product CountDown",'cena'),
		        "base" => "tbay_product_countdown",
		        "icon" => "vc-icon-tbay",
		        "class" => "",
		    	"category" => esc_html__('Tbay Woocommerce','cena'),
		    	'description'	=> esc_html__( 'Display Product Sales with Count Down', 'cena' ),
		        "params" => array(
		            array(
		                "type" => "textfield",
		                "class" => "",
		                "heading" => esc_html__('Title','cena'),
		                "param_name" => "title",
		            ),
		            array(
		                "type" => "textfield",
		                "class" => "",
		                "heading" => esc_html__('Sub Title','cena'),
		                "param_name" => "subtitle",
		            ),
		            array(
					    'type' => 'autocomplete',
					    'heading' => esc_html__( 'Categories', 'cena' ),
					    'value' => '',
					    'param_name' => 'categories',
					    "admin_label" => true,
					    'description' => esc_html__( 'Choose a categories if you want to show products of that them', 'cena' ),
					    'settings' => array(
					     	'multiple' => true,
					     	'unique_values' => true
					    ),
				   	),
		            array(
		                "type" => "textfield",
		                "heading" => esc_html__("Number items to show", 'cena'),
		                "param_name" => "number",
		                'std' => '1',
		                "description" => esc_html__("", 'cena')
		            ),
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Columns','cena'),
		                "param_name" => 'columns',
		                "value" => $columns
		            ),
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__("Layout",'cena'),
		                "param_name" => "layout_type",
		                "value" => array(
		                			esc_html__('Carousel', 'cena') => 'carousel', 
		                			esc_html__('Carousel Vertical', 'cena') => 'carousel-vertical', 
		                			esc_html__('Carousel Thumbnail', 'cena') => 'carousel-thumbnail', 
		                		 	esc_html__('Grid', 'cena') =>'grid',
		                		 ),
		                "admin_label" => true,
		                "description" => esc_html__("Select Columns.",'cena')
		            ),
					array(
		                "type" 		=> "dropdown",
		                "heading" 	=> esc_html__('Rows','cena'),
		                "param_name" => 'rows',
		                "value" 	=> $rows,
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-vertical',
									'carousel-thumbnail',
								),
						),
		            ),
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Navigation ", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Navigation ', 'cena' ),
						"param_name" 	=> "nav_type",
		                "value" 		=> array(
		                					esc_html__('No', 'cena') => 'no', 
		                		 			esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-vertical',
									'carousel-thumbnail',
								),
						),
					),					
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Pagination", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Pagination', 'cena' ),
						"param_name" 	=> "pagi_type",
		                "value" 		=> array(
		                					esc_html__('No', 'cena') => 'no', 
		                		 			esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-vertical',
									'carousel-thumbnail',
								),
						),
					),

					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Show config Responsive", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden config Responsive', 'cena' ),
						"param_name" 	=> "responsive_type",
		                "value" 		=> array(
		                					esc_html__('No', 'cena') => 'no', 
		                		 			esc_html__('Yes', 'cena') =>'yes' ),
					),
					array(
		                "type" 	  => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktop','cena'),
		                "param_name" => 'screen_desktop',
		                "value" => $columns,
		                'std'       => '4',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),					
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktopsmall','cena'),
		                "param_name" => 'screen_desktopsmall',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		           
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen tablet','cena'),
		                "param_name" => 'screen_tablet',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		            
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen mobile','cena'),
		                "param_name" => 'screen_mobile',
		                "value" => $columns,
		                'std'       => '1',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),

		            array(
		                "type" => "textfield",
		                "heading" => esc_html__("Extra class name", 'cena'),
		                "param_name" => "el_class",
		                "description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
		            ),
		        )
		    ));
			
			// Product Category
			vc_map( array(
			    "name" => esc_html__("Tbay Product Category",'cena'),
			    "base" => "tbay_productcategory",
			    "icon" => "vc-icon-tbay",
			    "class" => "",
				"category" => esc_html__('Tbay Woocommerce','cena'),
			    'description'=> esc_html__( 'Show Products In Carousel, Grid, List, Special','cena' ), 
			    "params" => array(
			    	array(
						"type" => "textfield",
						"class" => "",
						"heading" => esc_html__('Title', 'cena'),
						"param_name" => "title",
						"value" =>''
					),
					 array(
		                "type" => "textfield",
		                "class" => "",
		                "heading" => esc_html__('Sub Title','cena'),
		                "param_name" => "subtitle",
		            ),
				   	array(
						"type" => "dropdown",
						"heading" => esc_html__("Category",'cena'),
						"param_name" => "category",
						"value" => $categories
					),
					array(
						"type" => "dropdown",
						"heading" => esc_html__("Layout Type",'cena'),
						"param_name" => "layout_type",
						"value" => $layouts
					),
					array(
						"type"        => "attach_image",
						"description" => esc_html__("Upload an image for categories", 'cena'),
						"param_name"  => "image_cat",
						"value"       => '',
						'heading'     => esc_html__('Image', 'cena' )
					),
					array(
						"type" => "textfield",
						"heading" => esc_html__("Number of products to show",'cena'),
						"param_name" => "number",
						"value" => '4'
					),
					array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Columns','cena'),
		                "param_name" => 'columns',
		                "value" => $columns,
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special',
									'special',
									'grid',
								),
						),
		            ),

					array(
						"type" => "dropdown",
						"heading" => esc_html__('Rows','cena'),
						"param_name" => 'rows',
						"value" => $rows,
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special',
								),
						),
					),
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Navigation ", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Navigation ', 'cena' ),
						"param_name" 	=> "nav_type",
						"value" 		=> array(
											esc_html__('No', 'cena') => 'no', 
											esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special',
								),
						),
					),					
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Pagination", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Pagination', 'cena' ),
						"param_name" 	=> "pagi_type",
						"value" 		=> array(
											esc_html__('No', 'cena') => 'no', 
											esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special',
								),
						),
					),

					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Show config Responsive", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden config Responsive', 'cena' ),
						"param_name" 	=> "responsive_type",
		                "value" 		=> array(
		                					esc_html__('No', 'cena') => 'no', 
		                		 			esc_html__('Yes', 'cena') =>'yes' ),
					),
					array(
		                "type" 	  => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktop','cena'),
		                "param_name" => 'screen_desktop',
		                "value" => $columns,
		                'std'       => '4',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),					
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktopsmall','cena'),
		                "param_name" => 'screen_desktopsmall',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		           
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen tablet','cena'),
		                "param_name" => 'screen_tablet',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		            
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen mobile','cena'),
		                "param_name" => 'screen_mobile',
		                "value" => $columns,
		                'std'       => '1',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),

					array(
						"type" => "textfield",
						"heading" => esc_html__("Extra class name",'cena'),
						"param_name" => "el_class",
						"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.",'cena')
					)
			   	)
			));
			
			
			/**
			 * tbay_products
			 */
			vc_map( array(
			    "name" => esc_html__("Tbay Products",'cena'),
			    "base" => "tbay_products",
			    "icon" => "vc-icon-tbay",
			    'description'=> esc_html__( 'Show products as bestseller, featured in block', 'cena' ),
			    "class" => "",
			   	"category" => esc_html__('Tbay Woocommerce','cena'),
			    "params" => array(
			    	array(
						"type" => "textfield",
						"heading" => esc_html__("Title",'cena'),
						"param_name" => "title",
						"admin_label" => true,
						"value" => ''
					),
					array(
		                "type" => "textfield",
		                "class" => "",
		                "heading" => esc_html__('Sub Title','cena'),
		                "param_name" => "subtitle",
		            ),
					array(
					    'type' => 'autocomplete',
					    'heading' => esc_html__( 'Categories', 'cena' ),
					    'value' => '',
					    'param_name' => 'categories',
					    "admin_label" => true,
					    'description' => esc_html__( 'Choose categories if you want show products of them', 'cena' ),
					    'settings' => array(
					     	'multiple' => true,
					     	'unique_values' => true
					    ),
				   	),
			    	array(
						"type" => "dropdown",
						"heading" => esc_html__("Type",'cena'),
						"param_name" => "type",
						"value" => $types,
						"admin_label" => true,
						"description" => esc_html__("Select Columns.",'cena')
					),
					array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Columns','cena'),
		                "param_name" => 'columns',
		                "value" => $columns,
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special',
									'special',
									'grid',
								),
						),
		            ),
					array(
						"type" => "textfield",
						"heading" => esc_html__("Number of products to show",'cena'),
						"param_name" => "number",
						"value" => '4'
					),
					array(
						"type" => "dropdown",
						"heading" => esc_html__("Layout Type",'cena'),
						"param_name" => "layout_type",
						"value" => $layouts
					),
					array(
						"type" => "dropdown",
						"heading" => esc_html__('Rows','cena'),
						"param_name" => 'rows',
						"value" => $rows,
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special',
								),
						),
					),
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Navigation ", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Navigation ', 'cena' ),
						"param_name" 	=> "nav_type",
						"value" 		=> array(
											esc_html__('No', 'cena') => 'no', 
											esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special',
								),
						),
					),					
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Pagination", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Pagination', 'cena' ),
						"param_name" 	=> "pagi_type",
						"value" 		=> array(
											esc_html__('No', 'cena') => 'no', 
											esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special',
								),
						),
					),

					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Show config Responsive", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden config Responsive', 'cena' ),
						"param_name" 	=> "responsive_type",
		                "value" 		=> array(
		                					esc_html__('No', 'cena') => 'no', 
		                		 			esc_html__('Yes', 'cena') =>'yes' ),
					),
					array(
		                "type" 	  => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktop','cena'),
		                "param_name" => 'screen_desktop',
		                "value" => $columns,
		                'std'       => '4',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),					
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktopsmall','cena'),
		                "param_name" => 'screen_desktopsmall',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		           
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen tablet','cena'),
		                "param_name" => 'screen_tablet',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		            
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen mobile','cena'),
		                "param_name" => 'screen_mobile',
		                "value" => $columns,
		                'std'       => '1',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),

					array(
						"type" => "textfield",
						"heading" => esc_html__("Extra class name",'cena'),
						"param_name" => "el_class",
						"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.",'cena')
					)
			   	)
			));
			/**
			 * tbay_all_products
			 */
			vc_map( array(
			    "name" => esc_html__("Tbay Products Tabs",'cena'),
			    "base" => "tbay_productstabs",
			    "icon" => "vc-icon-tbay",
			    'description'	=> esc_html__( 'Display BestSeller, TopRated ... Products In tabs', 'cena' ),
			    "class" => "",
			   	"category" => esc_html__('Tbay Woocommerce','cena'),
			    "params" => array(
			    	array(
						"type" => "textfield",
						"heading" => esc_html__("Title",'cena'),
						"param_name" => "title",
						"value" => ''
					),
					array(
		                "type" => "textfield",
		                "class" => "",
		                "heading" => esc_html__('Sub Title','cena'),
		                "param_name" => "subtitle",
		            ),
		            array(
					    'type' => 'autocomplete',
					    'heading' => esc_html__( 'Categories', 'cena' ),
					    'value' => '',
					    'param_name' => 'categories',
					    "admin_label" => true,
					    'description' => esc_html__( 'Choose categories if you want show products of them', 'cena' ),
					    'settings' => array(
					     	'multiple' => true,
					     	'unique_values' => true
					    ),
				   	),
					array(
			            "type" => "sorted_list",
			            "heading" => esc_html__("Show Tab", 'cena'),
			            "param_name" => "producttabs",
			            "description" => esc_html__("Control teasers look. Enable blocks and place them in desired order.", 'cena'),
			            "value" => "recent_product",
			            "options" => $producttabs
			        ),
			        array(
						"type" => "dropdown",
						"heading" => esc_html__("Layout Type",'cena'),
						"param_name" => "layout_type",
						"value" => $layouts
					),		
					array(
						"type"          => "checkbox",
						"heading"       => esc_html__('Show Ajax Product Tabs?', 'cena'),
						"description"   => esc_html__('Show/hidden Ajax Product Tabs', 'cena'),
						"param_name"    => "ajax_tabs",
						"std"           => "",
						"value"         => array( 
											esc_html__('Yes', 'cena') =>'yes' ),
					),
					array(
						"type" => "dropdown",
						"heading" => esc_html__('Rows','cena'),
						"param_name" => 'rows',
						"value" => $rows,
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special'
							),
						),
					),
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Navigation ", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Navigation ', 'cena' ),
						"param_name" 	=> "nav_type",
						"value" 		=> array(
											esc_html__('No', 'cena') => 'no', 
											esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special'
							),
						),
					),					
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Pagination", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Pagination', 'cena' ),
						"param_name" 	=> "pagi_type",
						"value" 		=> array(
											esc_html__('No', 'cena') => 'no', 
											esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> array (
									'carousel',
									'carousel-special'
							),
						),
					),
					array(
						"type" => "textfield",
						"heading" => esc_html__("Number of products to show",'cena'),
						"param_name" => "number",
						"value" => '4'
					),
					array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Columns','cena'),
		                "param_name" => 'columns',
		                "value" => $columns
		            ),

					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Show config Responsive", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden config Responsive', 'cena' ),
						"param_name" 	=> "responsive_type",
		                "value" 		=> array(
		                					esc_html__('No', 'cena') => 'no', 
		                		 			esc_html__('Yes', 'cena') =>'yes' ),
					),
					array(
		                "type" 	  => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktop','cena'),
		                "param_name" => 'screen_desktop',
		                "value" => $columns,
		                'std'       => '4',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),					
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktopsmall','cena'),
		                "param_name" => 'screen_desktopsmall',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		           
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen tablet','cena'),
		                "param_name" => 'screen_tablet',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		            
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen mobile','cena'),
		                "param_name" => 'screen_mobile',
		                "value" => $columns,
		                'std'       => '1',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),

					array(
						"type" => "textfield",
						"heading" => esc_html__("Extra class name",'cena'),
						"param_name" => "el_class",
						"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.",'cena')
					)
			   	)
			));
			// Categories tabs
			vc_map( array(
				'name' => esc_html__( 'Products Categories Tabs ', 'cena' ),
				'base' => 'tbay_categoriestabs',
				"icon" => "vc-icon-tbay",
				'category' => esc_html__( 'Tbay Woocommerce', 'cena' ),
				'description' => esc_html__( 'Display  categories in Tabs', 'cena' ),
				'params' => array(
					array(
						"type" => "textfield",
						"heading" => esc_html__( 'Title','cena' ),
						"param_name" => "title",
						"value" => ''
					),
					array(
		                "type" => "textfield",
		                "class" => "",
		                "heading" => esc_html__( 'Sub Title','cena' ),
		                "param_name" => "subtitle",
		            ),
					
					array(
						'type' => 'param_group',
						'heading' => esc_html__( 'Tabs', 'cena' ),
						'param_name' => 'categoriestabs',
						'description' => '',
						'value' => '',
						'params' => array(
							array(
								"type" => "dropdown",
								"heading" => esc_html__( 'Category', 'cena' ),
								"param_name" => "category",
								"value" => $categories
							),
							array(
								'type' => 'attach_image',
								'heading' => esc_html__( 'Icon', 'cena' ),
								'param_name' => 'icon',
								'description' => esc_html__( 'You can choose a icon image or you can use icon font', 'cena' ),
							),
							array(
								'type' => 'textfield',
								'heading' => esc_html__( 'Icon Font', 'cena' ),
								'param_name' => 'icon_font',
								'description' => esc_html__( 'You can use font awesome icon. Eg: fa fa-home', 'cena' ),
							),
							
						)
					),
					array(
						"type" => "dropdown",
						"heading" => esc_html__("Type",'cena'),
						"param_name" => "type",
						"value" => $types,
						"admin_label" => true,
						"description" => esc_html__("Select Columns.",'cena')
					),

					array(
						"type"          => "checkbox",
						"heading"       => esc_html__('Show Ajax Categories Tabs?', 'cena'),
						"description"   => esc_html__('Show/hidden Ajax Categories Tabs', 'cena'),
						"param_name"    => "ajax_tabs",
						"std"           => "",
						"value"         => array(
											esc_html__('Yes', 'cena') =>'yes' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number Products', 'cena' ),
						'value' => 12,
						'param_name' => 'number',
						'description' => esc_html__( 'Number products per page to show', 'cena' ),
					),
					array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Columns','cena'),
		                "param_name" => 'columns',
		                "value" => $columns
		            ),
					
					array(
		                "type" => "dropdown",
		                "heading" => esc_html__("Layout",'cena'),
		                "param_name" => "layout_type",
		                "value" => array(
		                			esc_html__('Carousel', 'cena') => 'carousel', 
		                		 	esc_html__('Grid', 'cena') =>'grid' ),
		                "admin_label" => true,
		                "description" => esc_html__("Select Columns.",'cena')
		            ),
					array(
					    "type" => "dropdown",
					    "heading" => esc_html__('Rows','cena'),
					    "param_name" => 'rows',
					    "value" => $rows,
					    'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> 'carousel',
						),
					),
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Navigation ", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Navigation ', 'cena' ),
						"param_name" 	=> "nav_type",
		                "value" 		=> array(
		                					esc_html__('No', 'cena') => 'no', 
		                		 			esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> 'carousel',
						),
					),					
					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Pagination", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden Pagination', 'cena' ),
						"param_name" 	=> "pagi_type",
		                "value" 		=> array(
		                					esc_html__('No', 'cena') => 'no', 
		                		 			esc_html__('Yes', 'cena') =>'yes' ),
						'dependency' 	=> array(
								'element' 	=> 'layout_type',
								'value' 	=> 'carousel',
						),
					),


					array(
						"type" 			=> "dropdown",
						"heading" 		=> esc_html__( "Show config Responsive", 'cena' ),
						"description" 	=> esc_html__( 'Show/hidden config Responsive', 'cena' ),
						"param_name" 	=> "responsive_type",
		                "value" 		=> array(
		                					esc_html__('No', 'cena') => 'no', 
		                		 			esc_html__('Yes', 'cena') =>'yes' ),
					),
					array(
		                "type" 	  => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktop','cena'),
		                "param_name" => 'screen_desktop',
		                "value" => $columns,
		                'std'       => '4',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),					
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen desktopsmall','cena'),
		                "param_name" => 'screen_desktopsmall',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		           
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen tablet','cena'),
		                "param_name" => 'screen_tablet',
		                "value" => $columns,
		                'std'       => '3',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),		            
		            array(
		                "type" => "dropdown",
		                "heading" => esc_html__('Number of columns screen mobile','cena'),
		                "param_name" => 'screen_mobile',
		                "value" => $columns,
		                'std'       => '1',
		                'dependency' 	=> array(
								'element' 	=> 'responsive_type',
								'value' 	=> 'yes',
						),
		            ),

					
		            array(
						"type" => "textfield",
						"heading" => esc_html__("Extra class name",'cena'),
						"param_name" => "el_class",
						"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.",'cena')
					)
				)
			) );
		}
	}
	add_action( 'vc_after_set_mode', 'cena_tbay_load_woocommerce_element', 99 );

	class WPBakeryShortCode_Tbay_productstabs extends WPBakeryShortCode {

		public function getListQuery( $atts ) { 
			$this->atts  = $atts; 
			$list_query = array();
			$types = isset($this->atts['producttabs']) ? explode(',', $this->atts['producttabs']) : array();
			foreach ($types as $type) {
				$list_query[$type] = $this->getTabTitle($type);
			}
			return $list_query;
		}

		public function getTabTitle($type){ 
			switch ($type) {
				case 'recent_product':
					return array('title' => esc_html__('Latest Products', 'cena'), 'title_tab'=>esc_html__('Latest', 'cena'));
				case 'featured_product':
					return array('title' => esc_html__('Featured Products', 'cena'), 'title_tab'=>esc_html__('Featured', 'cena'));
				case 'top_rate':
					return array('title' => esc_html__('Top Rated Products', 'cena'), 'title_tab'=>esc_html__('Top Rated', 'cena'));
				case 'best_selling':
					return array('title' => esc_html__('BestSeller Products', 'cena'), 'title_tab'=>esc_html__('BestSeller', 'cena'));
				case 'on_sale':
					return array('title' => esc_html__('Special Products', 'cena'), 'title_tab'=>esc_html__('Special', 'cena'));
			}
		}
	}

	class WPBakeryShortCode_Tbay_product_countdown extends WPBakeryShortCode {}
	class WPBakeryShortCode_Tbay_productcategory extends WPBakeryShortCode {}
	class WPBakeryShortCode_Tbay_category_info extends WPBakeryShortCode {}
	class WPBakeryShortCode_Tbay_products extends WPBakeryShortCode {}
	class WPBakeryShortCode_Tbay_categoriestabs extends WPBakeryShortCode {}
}