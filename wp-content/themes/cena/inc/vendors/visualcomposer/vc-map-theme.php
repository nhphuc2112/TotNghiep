<?php
if(!class_exists('WPBakeryShortCode')) return;

if ( !function_exists('cena_tbay_load_load_theme_element')) {
	function cena_tbay_load_load_theme_element() {
		$columns = array(1,2,3,4,6);
		// Heading Text Block
		vc_map( array(
			'name'        => esc_html__( 'Tbay Widget Heading','cena'),
			'base'        => 'tbay_title_heading',
			"icon" 		  => "vc-icon-tbay",
			"class"       => "",
			"category" => esc_html__('Tbay Elements', 'cena'),
			'description' => esc_html__( 'Create title for one Widget', 'cena' ),
			"params"      => array(
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Widget title', 'cena' ),
					'param_name' => 'title',
					'value'       => esc_html__( 'Title', 'cena' ),
					'description' => esc_html__( 'Enter heading title.', 'cena' ),
					"admin_label" => true
				),
				array(
				    'type' => 'colorpicker',
				    'heading' => esc_html__( 'Title Color', 'cena' ),
				    'param_name' => 'font_color',
				    'description' => esc_html__( 'Select font color', 'cena' )
				),
				 
				array(
					"type" => "textarea",
					'heading' => esc_html__( 'Description', 'cena' ),
					"param_name" => "descript",
					"value" => '',
					'description' => esc_html__( 'Enter description for title.', 'cena' )
			    ),

				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Text Button', 'cena' ),
					'param_name' => 'textbutton',
					'description' => esc_html__( 'Text Button', 'cena' ),
					"admin_label" => true
				),

				array(
					'type' => 'textfield',
					'heading' => esc_html__( ' Link Button', 'cena' ),
					'param_name' => 'linkbutton',
					'description' => esc_html__( 'Link Button', 'cena' ),
					"admin_label" => true
				),
				array(
					"type" => "dropdown",
					"heading" => esc_html__("Button Style", 'cena'),
					"param_name" => "buttons",
					'value' 	=> array(
						esc_html__('Default Outline', 'cena') => 'btn-default btn-outline', 
						esc_html__('Primary Outline', 'cena') => 'btn-primary btn-outline', 
						esc_html__('Lighten', 'cena') => 'btn-lighten'
					),
					'std' => ''
				),


				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Text Button 2', 'cena' ),
					'param_name' => 'textbutton2',
					'description' => esc_html__( 'Text Button 2', 'cena' ),
					"admin_label" => true
				),

				array(
					'type' => 'textfield',
					'heading' => esc_html__( ' Link Button 2', 'cena' ),
					'param_name' => 'linkbutton2',
					'description' => esc_html__( 'Link Button 2', 'cena' ),
					"admin_label" => true
				),
				array(
					"type" => "dropdown",
					"heading" => esc_html__("Button Style", 'cena'),
					"param_name" => "buttons2",
					'value' 	=> array(
						esc_html__('Default Outline', 'cena') => 'btn-default btn-outline', 
						esc_html__('Primary Outline', 'cena') => 'btn-primary btn-outline', 
						esc_html__('Lighten', 'cena') => 'btn-lighten'
					),
					'std' => ''
				),


				array(
					"type" => "dropdown",
					"heading" => esc_html__("Style", 'cena'),
					"param_name" => "style",
					'value' 	=> array(
						esc_html__('Style Default', 'cena') => '', 
						esc_html__('Style1', 'cena') => 'style1', 
						esc_html__('Style2', 'cena') => 'style2', 
						esc_html__('Style3', 'cena') => 'style3' ,
						esc_html__('Style4', 'cena') => 'style4',
						esc_html__('Style5', 'cena') => 'style5',
						esc_html__('Style Small', 'cena') => 'stylesmall'
					),
					'std' => ''
				),

				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Extra class name', 'cena' ),
					'param_name' => 'el_class',
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'cena' )
				)

			),
		));
		
		// Banner CountDown
		vc_map( array(
			'name'        => esc_html__( 'Tbay Banner CountDown','cena'),
			'base'        => 'tbay_banner_countdown',
			"icon" 		  => "vc-icon-tbay",
			"class"       => "",
			"category" => esc_html__('Tbay Elements', 'cena'),
			'description' => esc_html__( 'Show CountDown with banner', 'cena' ),
			"params"      => array(
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Widget title', 'cena' ),
					'param_name' => 'title',
					'value'       => esc_html__( 'Title', 'cena' ),
					'description' => esc_html__( 'Enter heading title.', 'cena' ),
					"admin_label" => true
				),
				array(
					"type" => "attach_image",
					"description" => esc_html__("If you upload an image, icon will not show.", 'cena'),
					"param_name" => "image",
					"value" => '',
					'heading'	=> esc_html__('Image', 'cena' )
				),
				array(
				    'type' => 'textfield',
				    'heading' => esc_html__( 'Date Expired', 'cena' ),
				    'param_name' => 'input_datetime',
				    'description' => esc_html__( 'Select font color', 'cena' ),
				),
				array(
				    'type' => 'colorpicker',
				    'heading' => esc_html__( 'Title Color', 'cena' ),
				    'param_name' => 'font_color',
				    'description' => esc_html__( 'Select font color', 'cena' ),
				    'class'	=> 'hacongtien'
				),
				array(
					"type" => "textarea",
					'heading' => esc_html__( 'Description', 'cena' ),
					"param_name" => "descript",
					"value" => '',
					'description' => esc_html__( 'Enter description for title.', 'cena' )
			    ),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Extra class name', 'cena' ),
					'param_name' => 'el_class',
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'cena' )
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Text Link', 'cena' ),
					'param_name' => 'text_link',
					'value'		 => 'Find Out More',
					'description' => esc_html__( 'Enter your link text', 'cena' )
				),
				array(
					'type' => 'textfield',
					'heading' => esc_html__( 'Link', 'cena' ),
					'param_name' => 'link',
					'value'		 => 'http://',
					'description' => esc_html__( 'Enter your link to redirect', 'cena' )
				)
			),
		));
		$fields = array();
		for ($i=1; $i <= 5; $i++) { 
			$fields[] = array(
				"type" => "textfield",
				"heading" => esc_html__("Title", 'cena').' '.$i,
				"param_name" => "title".$i,
				"value" => '',    "admin_label" => true,
			);
			$fields[] = array(
				"type" => "attach_image",
				"heading" => esc_html__("Photo", 'cena').' '.$i,
				"param_name" => "photo".$i,
				"value" => '',
				'description' => ''
			);
			$fields[] = array(
				"type" => "textarea",
				"heading" => esc_html__("information", 'cena').' '.$i,
				"param_name" => "information".$i,
				"value" => 'Your Description Here',
				'description'	=> esc_html__('Allow  put html tags', 'cena' )
			);
	    	$fields[] = array(
				"type" => "textfield",
				"heading" => esc_html__("Link Read More", 'cena').' '.$i,
				"param_name" => "link".$i,
				"value" => '',
			);
		}
		$fields[] = array(
			"type" => "textfield",
			"heading" => esc_html__("Extra class name", 'cena'),
			"param_name" => "el_class",
			"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
		);
		// Featured Box
		vc_map( array(
		    "name" => esc_html__("Tbay Featured Banner",'cena'),
		    "base" => "tbay_featurebanner",
		    "icon" => "vc-icon-tbay",
		    "description"=> esc_html__('Decreale Service Info', 'cena'),
		    "class" => "",
		    "category" => esc_html__('Tbay Elements', 'cena'),
		    "params" => $fields
		));
		
		// Tbay Counter
		vc_map( array(
		    "name" => esc_html__("Tbay Counter",'cena'),
		    "base" => "tbay_counter",
		    "icon" => "vc-icon-tbay",
		    "class" => "",
		    "description"=> esc_html__('Counting number with your term', 'cena'),
		    "category" => esc_html__('Tbay Elements', 'cena'),
		    "params" => array(
		    	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textarea",
					"heading" => esc_html__("Description", 'cena'),
					"param_name" => "description",
					"value" => '',
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Number", 'cena'),
					"param_name" => "number",
					"value" => ''
				),
			 	array(
					"type" => "textfield",
					"heading" => esc_html__("FontAwsome Icon", 'cena'),
					"param_name" => "icon",
					"value" => '',
					'description' => esc_html__( 'This support display icon from FontAwsome,Material Design Iconic and Simple Line, Please click', 'cena' )
									. '<a href="' . ( is_ssl()  ? 'https' : 'http') . '://fortawesome.github.io/Font-Awesome/" target="_blank">'
									. esc_html__( 'here to see the list', 'cena' ) . '</a>'
				),
				array(
					"type" => "attach_image",
					"description" => esc_html__("If you upload an image, icon will not show.", 'cena'),
					"param_name" => "image",
					"value" => '',
					'heading'	=> esc_html__('Image', 'cena' )
				),
				array(
					"type" => "colorpicker",
					"heading" => esc_html__("Text Color", 'cena'),
					"param_name" => "text_color",
					'value' 	=> '',
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
		   	)
		));

		// Tbay Counter
		vc_map( array(
		    "name" => esc_html__("Tbay Brands",'cena'),
		    "base" => "tbay_brands",
		    "icon" => "vc-icon-tbay",
		    "class" => "",
		    "description"=> esc_html__('Display brands on front end', 'cena'),
		    "category" => esc_html__('Tbay Elements', 'cena'),
		    "params" => array(
		    	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Number", 'cena'),
					"param_name" => "number",
					"value" => ''
				),
			 	array(
					"type" => "dropdown",
					"heading" => esc_html__("Layout Type", 'cena'),
					"param_name" => "layout_type",
					'value' 	=> array(
						esc_html__('Carousel', 'cena') => 'carousel', 
						esc_html__('Grid', 'cena') => 'grid'
					),
					'std' => ''
				),
				array(
	                "type" => "dropdown",
	                "heading" => esc_html__('Columns','cena'),
	                "param_name" => 'columns',
	                "value" => $columns
	            ),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
		   	)
		));
		
		vc_map( array(
		    "name" => esc_html__("Tbay Socials link",'cena'),
		    "base" => "tbay_socials_link",
		    "icon" => "vc-icon-tbay",
		    "description"=> esc_html__('Show socials link', 'cena'),
		    "category" => esc_html__('Tbay Elements', 'cena'),
		    "params" => array(
		    	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textarea",
					"heading" => esc_html__("Description", 'cena'),
					"param_name" => "description",
					"value" => '',
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Facebook Page URL", 'cena'),
					"param_name" => "facebook_url",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Twitter Page URL", 'cena'),
					"param_name" => "twitter_url",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Youtube Page URL", 'cena'),
					"param_name" => "youtube_url",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Pinterest Page URL", 'cena'),
					"param_name" => "pinterest_url",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Google Plus Page URL", 'cena'),
					"param_name" => "google-plus_url",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Snapchat Page URL", 'cena'),
					"param_name" => "snapchat_url",
					"value" => '',
					"admin_label"	=> true
				),				
				array(
					"type" => "textfield",
					"heading" => esc_html__("Instagram Page URL", 'cena'),
					"param_name" => "instagram_url",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
		   	)
		));
		// newsletter
		vc_map( array(
		    "name" => esc_html__("Tbay Newsletter",'cena'),
		    "base" => "tbay_newsletter",
		    "icon" => "vc-icon-tbay",
		    "class" => "",
		    "description"=> esc_html__('Show newsletter form', 'cena'),
		    "category" => esc_html__('Tbay Elements', 'cena'),
		    "params" => array(
		    	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textarea",
					"heading" => esc_html__("Description", 'cena'),
					"param_name" => "description",
					"value" => '',
				),
				
				array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
		   	)
		));
		
		// Testimonial
		vc_map( array(
            "name" => esc_html__("Tbay Testimonials",'cena'),
            "base" => "tbay_testimonials",
            "icon" => "vc-icon-tbay",
            'description'=> esc_html__('Display Testimonials In FrontEnd', 'cena'),
            "class" => "",
            "category" => esc_html__('Tbay Widgets', 'cena'),
            "params" => array(
              	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"admin_label" => true,
					"value" => '',
				),
              	array(
	              	"type" => "textfield",
	              	"heading" => esc_html__("Number", 'cena'),
	              	"param_name" => "number",
	              	"value" => '4',
	            ),
	            array(
	                "type" => "dropdown",
	                "heading" => esc_html__('Columns','cena'),
	                "param_name" => 'columns',
	                "value" => $columns
	            ),
	            array(
	                "type" => "dropdown",
	                "heading" => esc_html__('Style','cena'),
	                "param_name" => 'style',
	                'value' 	=> array(
						esc_html__('Default ', 'cena') => 'default', 
						esc_html__('Styel Lighten ', 'cena') => 'lighten', 
					),
					'std' => ''
	            ),
	            array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
            )
        ));
        // Our Team
		vc_map( array(
            "name" => esc_html__("Tbay Our Team",'cena'),
            "base" => "tbay_ourteam",
            "icon" => "vc-icon-tbay",
            'description'=> esc_html__('Display Our Team In FrontEnd', 'cena'),
            "class" => "",
            "category" => esc_html__('Tbay Widgets', 'cena'),
            "params" => array(
              	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"admin_label" => true,
					"value" => '',
				),
				array(
					"type" => "attach_image",
					"description" => esc_html__("If you upload an image, icon will not show.", 'cena'),
					"param_name" => "image_icon",
					"value" => '',
					'heading'	=> esc_html__('Title Icon', 'cena' )
				),
              	array(
					'type' => 'param_group',
					'heading' => esc_html__('Members Settings', 'cena' ),
					'param_name' => 'members',
					'description' => '',
					'value' => '',
					'params' => array(
						array(
			                "type" => "textfield",
			                "class" => "",
			                "heading" => esc_html__('Name','cena'),
			                "param_name" => "name",
			            ),
			            array(
			                "type" => "textfield",
			                "class" => "",
			                "heading" => esc_html__('Job','cena'),
			                "param_name" => "job",
			            ),
						array(
							"type" => "attach_image",
							"heading" => esc_html__("Image", 'cena'),
							"param_name" => "image"
						),

			            array(
			                "type" => "textfield",
			                "class" => "",
			                "heading" => esc_html__('Facebook','cena'),
			                "param_name" => "facebook",
			            ),

			            array(
			                "type" => "textfield",
			                "class" => "",
			                "heading" => esc_html__('Twitter Link','cena'),
			                "param_name" => "twitter",
			            ),

			            array(
			                "type" => "textfield",
			                "class" => "",
			                "heading" => esc_html__('Google plus Link','cena'),
			                "param_name" => "google",
			            ),

			            array(
			                "type" => "textfield",
			                "class" => "",
			                "heading" => esc_html__('Linkin Link','cena'),
			                "param_name" => "linkin",
			            ),

					),
				),
				array(
	                "type" => "dropdown",
	                "heading" => esc_html__('Columns','cena'),
	                "param_name" => 'columns',
	                "value" => $columns
	            ),
	            array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
            )
        ));
        // Gallery Images
		vc_map( array(
            "name" => esc_html__("Tbay Gallery",'cena'),
            "base" => "tbay_gallery",
            "icon" => "vc-icon-tbay",
            'description'=> esc_html__('Display Gallery In FrontEnd', 'cena'),
            "class" => "",
            "category" => esc_html__('Tbay Widgets', 'cena'),
            "params" => array(
              	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"admin_label" => true,
					"value" => '',
				),
              	array(
					"type" => "attach_images",
					"heading" => esc_html__("Images", 'cena'),
					"param_name" => "images"
				),
				array(
	                "type" => "dropdown",
	                "heading" => esc_html__('Columns','cena'),
	                "param_name" => 'columns',
	                'value' 	=> array(1,2,3,4,6,7,8,9,10),
	            ),
	            array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
            )
        ));
        // Gallery Images
		vc_map( array(
            "name" => esc_html__("Tbay Video",'cena'),
            "base" => "tbay_video",
            "icon" => "vc-icon-tbay",
            'description'=> esc_html__('Display Video In FrontEnd', 'cena'),
            "class" => "",
            "category" => esc_html__('Tbay Widgets', 'cena'),
            "params" => array(
              	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"admin_label" => true,
					"value" => '',
				),
				array(
					"type" => "textarea",
					'heading' => esc_html__( 'Description', 'cena' ),
					"param_name" => "description",
					"value" => '',
					'description' => esc_html__( 'Enter description for title.', 'cena' )
			    ),
              	array(
					"type" => "attach_image",
					"heading" => esc_html__("Video Cover Image", 'cena'),
					"param_name" => "image"
				),
				array(
	                "type" => "textfield",
	                "heading" => esc_html__('Youtube Video Link','cena'),
	                "param_name" => 'video_link'
	            ),
	           	array(
	                "type" => "dropdown",
	                "heading" => esc_html__('Style','cena'),
	                "param_name" => 'style',
	                'value' 	=> array(
						esc_html__('Default ', 'cena') => '', 
						esc_html__('Styel 1 ', 'cena') => 'style1'
					),
					'std' => ''
	            ),
	            array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
            )
        ));
        // Features Box
		vc_map( array(
            "name" => esc_html__("Tbay Features",'cena'),
            "base" => "tbay_features",
            "icon" => "vc-icon-tbay",
            'description'=> esc_html__('Display Features In FrontEnd', 'cena'),
            "class" => "",
            "category" => esc_html__('Tbay Widgets', 'cena'),
            "params" => array(
            	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"admin_label" => true,
					"value" => '',
				),
				array(
					'type' => 'param_group',
					'heading' => esc_html__('Members Settings', 'cena' ),
					'param_name' => 'items',
					'description' => '',
					'value' => '',
					'params' => array(
						array(
			                "type" => "textfield",
			                "class" => "",
			                "heading" => esc_html__('Title','cena'),
			                "param_name" => "title",
			            ),
			            array(
			                "type" => "textarea",
			                "class" => "",
			                "heading" => esc_html__('Description','cena'),
			                "param_name" => "description",
			            ),
						array(
							"type" => "textfield",
							"heading" => esc_html__("FontAwsome Icon", 'cena'),
							"param_name" => "icon",
							"value" => '',
							'description' => esc_html__( 'This support display icon from FontAwsome, Please click', 'cena' )
											. '<a href="' . ( is_ssl()  ? 'https' : 'http') . '://fortawesome.github.io/Font-Awesome/" target="_blank">'
											. esc_html__( 'here to see the list', 'cena' ) . '</a>'
						),
						array(
							"type" => "attach_image",
							"description" => esc_html__("If you upload an image, icon will not show.", 'cena'),
							"param_name" => "image",
							"value" => '',
							'heading'	=> esc_html__('Image', 'cena' )
						),
						array(
			                "type" => "textfield",
			                "class" => "",
			                "heading" => esc_html__('Button Link','cena'),
			                "param_name" => "link",
			            ),
					),
				),
	           	array(
	                "type" => "dropdown",
	                "heading" => esc_html__('Style','cena'),
	                "param_name" => 'style',
	                'value' 	=> array(
						esc_html__('Default ', 'cena') => 'default', 
						esc_html__('Styel 1 ', 'cena') => 'style1', 
						esc_html__('Styel 2 ', 'cena') => 'style2',
						esc_html__('Styel 3 ', 'cena') => 'style3'
					),
					'std' => ''
	            ),
	            array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
            )
        ));

		// Banner
		vc_map( array(
		    "name" => esc_html__("Tbay Banner",'cena'),
		    "base" => "tbay_banner",
		    "icon" => "vc-icon-tbay",
		    "class" => "",
		    "description"=> esc_html__('Show Text Images', 'cena'),
		    "category" => esc_html__('Tbay Elements', 'cena'),
		    "params" => array(
		    	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textarea",
					"heading" => esc_html__("Description", 'cena'),
					"param_name" => "description",
					"value" => '',
				),
				array(
					"type" => "attach_image",
					"heading" => esc_html__("Images", 'cena'),
					"param_name" => "image"
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Link", 'cena'),
					"param_name" => "link",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
		   	)
		));
		
		$custom_menus = array();
		if ( is_admin() ) {
			$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
			if ( is_array( $menus ) && ! empty( $menus ) ) {
				foreach ( $menus as $single_menu ) {
					if ( is_object( $single_menu ) && isset( $single_menu->name, $single_menu->slug ) ) {
						$custom_menus[ $single_menu->name ] = $single_menu->slug;
					}
				}
			}
		}
		// Menu
		vc_map( array(
		    "name" => esc_html__("Tbay Custom Menu",'cena'),
		    "base" => "tbay_custom_menu",
		    "icon" => "vc-icon-tbay",
		    "class" => "",
		    "description"=> esc_html__('Show Custom Menu', 'cena'),
		    "category" => esc_html__('Tbay Elements', 'cena'),
		    "params" => array(
		    	array(
					"type" => "textfield",
					"heading" => esc_html__("Title", 'cena'),
					"param_name" => "title",
					"value" => '',
					"admin_label"	=> true
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Menu', 'cena' ),
					'param_name' => 'nav_menu',
					'value' => $custom_menus,
					'description' => empty( $custom_menus ) ? esc_html__( 'Custom menus not found. Please visit <b>Appearance > Menus</b> page to create new menu.', 'cena' ) : esc_html__( 'Select menu to display.', 'cena' ),
					'admin_label' => true,
					'save_always' => true,
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Active treeview menu?', 'cena' ),
					'param_name' => 'ac_treeview',
					'value'       => array(
						'No'  	=> 'no',
						'Yes'   => 'yes'
					),
					'description' => esc_html__( 'Show treeview menu', 'cena' ) ,
					'save_always' => true,
				),
				array(
					"type" => "textfield",
					"heading" => esc_html__("Extra class name", 'cena'),
					"param_name" => "el_class",
					"description" => esc_html__("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'cena')
				)
		   	)
		));
	}
}
add_action( 'vc_after_set_mode', 'cena_tbay_load_load_theme_element', 99 );

class WPBakeryShortCode_tbay_title_heading extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_banner_countdown extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_featurebanner extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_brands extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_socials_link extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_newsletter extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_banner extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_testimonials extends WPBakeryShortCode {}

class WPBakeryShortCode_tbay_counter extends WPBakeryShortCode {
	public function __construct( $settings ) {
		parent::__construct( $settings );
		$this->load_scripts();
	}

	public function load_scripts() {
		$suffix 		= (cena_tbay_get_config('minified_js', false)) ? '.min' : CENA_MIN_JS;
		wp_register_script('jquery-counterup', get_template_directory_uri().'/js/jquery.counterup' . $suffix . '.js', array('jquery'), false, true);
	}
}

class WPBakeryShortCode_tbay_ourteam extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_gallery extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_video extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_features extends WPBakeryShortCode {}
class WPBakeryShortCode_tbay_custom_menu extends WPBakeryShortCode {}