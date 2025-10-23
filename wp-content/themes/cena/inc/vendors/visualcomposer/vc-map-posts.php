<?php
if(!class_exists('WPBakeryShortCode')) return;

if ( !function_exists('cena_tbay_load_post_element')) {

	if ( !function_exists('cena_tbay_post_get_categories') ) {
	    function cena_tbay_post_get_categories() {
	        $return = array( esc_html__('--- Choose a Category ---', 'cena') );

	        $args = array(
	            'type' => 'post',
	            'child_of' => 0,
	            'orderby' => 'name',
	            'order' => 'ASC',
	            'hide_empty' => false,
	            'hierarchical' => 1,
	            'taxonomy' => 'category'
	        );

	        $categories = get_categories( $args );

	        cena_post_tbay_get_category_childs( $categories, 0, 0, $return );



	        return $return;
	    }
	}

	if ( !function_exists('cena_post_tbay_get_category_childs') ) {
	    function cena_post_tbay_get_category_childs( $categories, $id_parent, $level, &$dropdown ) {
	        foreach ( $categories as $key => $category ) {
	            if ( $category->category_parent == $id_parent ) {
	                $dropdown = array_merge( $dropdown, array( str_repeat( "- ", $level ) . $category->name => $category->slug ) );
	                unset($categories[$key]);
	                cena_post_tbay_get_category_childs( $categories, $category->term_id, $level + 1, $dropdown );
	            }
	        }
	    }
	}

	function cena_tbay_load_post_element() {
		$categories = cena_tbay_post_get_categories();
		$layouts = array(
			esc_html__('Grid', 'cena') => 'grid',
			esc_html__('List', 'cena') => 'list',
			esc_html__('Carousel', 'cena') => 'carousel',
		);
		$columns = array(1,2,3,4,6);
		$rows 	 = array(1,2,3);
		vc_map( array(
			'name' => esc_html__( 'Tbay Grid Posts', 'cena' ),
			'base' => 'tbay_gridposts',
			'icon'        => 'vc-icon-tbay',
			'category' => esc_html__('Tbay Post', 'cena'),
			'description' => esc_html__( 'Create Post having blog styles', 'cena' ),
			 
			'params' => array(
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Title', 'cena' ),
					'param_name' => 'title',
					'description' => esc_html__( 'Enter text which will be used as widget title. Leave blank if no title is needed.', 'cena' ),
					"admin_label" => true
				),
				array(
	                "type" => "textfield",
	                "class" => "",
	                "heading" => esc_html__('Sub Title','cena'),
	                "param_name" => "subtitle",
	            ),
		   		array(
					"type" => "dropdown",
					"heading" => esc_html__("Categories",'cena'),
					"param_name" => "category",
					"value" => $categories,
					"admin_label" => true
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Thumbnail size', 'cena' ),
					'param_name' => 'thumbsize',
					'description' => esc_html__( 'Enter thumbnail size. Example: thumbnail, medium, large, full or other sizes defined by current theme.', 'cena' ),
						'std'       => 'thumbnail',
				),
				array(
	                "type" => "dropdown",
	                "heading" => esc_html__('Columns','cena'),
	                "param_name" => 'columns',
	                "value" => $columns,
	                'std'   => '4',
	            ),
            	array(
					"type" => "textfield",
					"heading" => esc_html__("Number of post to show",'cena'),
					"param_name" => "number",
					"value" => '4'
				),
				array(
					"type" => "dropdown",
					"heading" => esc_html__("Layout Type", 'cena'),
					"param_name" => "layout_type",
					"value" => $layouts,
					"admin_label" => true
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
							),
					),
	            ),
	            // Data settings
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Order by', 'cena' ),
					'param_name' => 'orderby',
					'admin_label' => true,
					'value' => array(
						esc_html__( 'Date', 'cena' ) => 'date',
						esc_html__( 'Order by post ID', 'cena' ) => 'ID',
						esc_html__( 'Author', 'cena' ) => 'author',
						esc_html__( 'Title', 'cena' ) => 'title',
						esc_html__( 'Last modified date', 'cena' ) => 'modified',
						esc_html__( 'Random order', 'cena' ) => 'rand',
					),
					'description' => esc_html__( 'Select order type. If "Meta value" or "Meta value Number" is chosen then meta key is required.', 'cena' ),
					'group' => esc_html__( 'Data Settings', 'cena' ),
					'param_holder_class' => 'vc_grid-data-type-not-ids',
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Sort order', 'cena' ),
					'param_name' => 'order',
					'admin_label' => true,
					'group' => esc_html__( 'Data Settings', 'cena' ),
					'value' => array(
						esc_html__( 'Descending', 'cena' ) => 'DESC',
						esc_html__( 'Ascending', 'cena' ) => 'ASC',
					),
					'param_holder_class' => 'vc_grid-data-type-not-ids',
					'description' => esc_html__( 'Select sorting order.', 'cena' ),
				),

				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Extra class name', 'cena' ),
					'param_name' => 'el_class',
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'cena' )
				)
			)
		) );
	}
}
add_action( 'vc_after_set_mode', 'cena_tbay_load_post_element', 99 );

class WPBakeryShortCode_tbay_gridposts extends WPBakeryShortCode {}