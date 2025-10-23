<?php

if ( !function_exists( 'cena_tbay_page_metaboxes' ) ) {
	function cena_tbay_page_metaboxes(array $metaboxes) {
		global $wp_registered_sidebars;
        $sidebars = array();

        if ( !empty($wp_registered_sidebars) ) {
            foreach ($wp_registered_sidebars as $sidebar) {
                $sidebars[$sidebar['id']] = $sidebar['name'];
            }
        }
        $headers = array_merge( array('global' => esc_html__( 'Global Setting', 'cena' )), cena_tbay_get_header_layouts() );
        $footers = array_merge( array('global' => esc_html__( 'Global Setting', 'cena' )), cena_tbay_get_footer_layouts() );

		$prefix = 'tbay_page_';
	    $fields = array(
			array(
				'name' => esc_html__( 'Select Layout', 'cena' ),
				'id'   => $prefix.'layout',
				'type' => 'select',
				'options' => array(
					'main' => esc_html__('Main Content Only', 'cena'),
					'left-main' => esc_html__('Left Sidebar - Main Content', 'cena'),
					'main-right' => esc_html__('Main Content - Right Sidebar', 'cena'),
					'left-main-right' => esc_html__('Left Sidebar - Main Content - Right Sidebar', 'cena')
				)
			),
			array(
                'id' => $prefix.'fullwidth',
                'type' => 'select',
                'name' => esc_html__('Is Full Width?', 'cena'),
                'default' => 'no',
                'options' => array(
                    'no' => esc_html__('No', 'cena'),
                    'yes' => esc_html__('Yes', 'cena')
                )
            ),
            array(
                'id' => $prefix.'left_sidebar',
                'type' => 'select',
                'name' => esc_html__('Left Sidebar', 'cena'),
                'options' => $sidebars
            ),
            array(
                'id' => $prefix.'right_sidebar',
                'type' => 'select',
                'name' => esc_html__('Right Sidebar', 'cena'),
                'options' => $sidebars
            ),
            array(
                'id' => $prefix.'show_breadcrumb',
                'type' => 'select',
                'name' => esc_html__('Show Breadcrumb?', 'cena'),
                'options' => array(
                    'no' => esc_html__('No', 'cena'),
                    'yes' => esc_html__('Yes', 'cena')
                ),
                'default' => 'yes',
            ),
            array(
                'id' => $prefix.'breadcrumb_color',
                'type' => 'colorpicker',
                'name' => esc_html__('Breadcrumb Background Color', 'cena')
            ),
            array(
                'id' => $prefix.'breadcrumb_image',
                'type' => 'file',
                'name' => esc_html__('Breadcrumb Background Image', 'cena')
            ),
            array(
                'id' => $prefix.'header_type',
                'type' => 'select',
                'name' => esc_html__('Header Layout Type', 'cena'),
                'description' => esc_html__('Choose a header for your website.', 'cena'),
                'options' => $headers,
                'default' => 'global'
            ),
            array(
                'id' => $prefix.'footer_type',
                'type' => 'select',
                'name' => esc_html__('Footer Layout Type', 'cena'),
                'description' => esc_html__('Choose a footer for your website.', 'cena'),
                'options' => $footers,
                'default' => 'global'
            ),
            array(
                'id' => $prefix.'extra_class',
                'type' => 'text',
                'name' => esc_html__('Extra Class', 'cena'),
                'description' => esc_html__('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'cena')
            )
    	);
		
	    $metaboxes[$prefix . 'display_setting'] = array(
			'id'                        => $prefix . 'display_setting',
			'title'                     => esc_html__( 'Display Settings', 'cena' ),
			'object_types'              => array( 'page' ),
			'context'                   => 'normal',
			'priority'                  => 'high',
			'show_names'                => true,
			'fields'                    => $fields
		);

	    return $metaboxes;
	}
}
add_filter( 'cmb2_meta_boxes', 'cena_tbay_page_metaboxes' );

if ( !function_exists( 'cena_tbay_cmb2_style' ) ) {
	function cena_tbay_cmb2_style() {
		wp_enqueue_style( 'cena-cmb2-style', get_template_directory_uri() . '/inc/vendors/cmb2/assets/style.css', array(), '1.0' );
	}
}
add_action( 'admin_enqueue_scripts', 'cena_tbay_cmb2_style' );


