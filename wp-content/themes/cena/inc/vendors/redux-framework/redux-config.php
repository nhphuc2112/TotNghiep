<?php
/**
 * ReduxFramework Sample Config File
 * For full documentation, please visit: http://docs.reduxframework.com/
 */

if (!class_exists('Cena_Redux_Framework_Config')) {

    class Cena_Redux_Framework_Config
    {
        public $args = array();
        public $sections = array();
        public $theme;
        public $ReduxFramework;
        public $default_color;
        public $default_fonts;

        public function __construct()
        {
            if (!class_exists('ReduxFramework')) {
                return;
            }

            add_action('init', array($this, 'initSettings'), 10);
        }

        public function redux_default_color() 
        {
            $this->default_color = cena_tbay_default_theme_primary_color();
        }

        public function redux_default_theme_fonts() 
        {
            $this->default_fonts = cena_tbay_default_theme_primary_fonts();
        }

        public function initSettings()
        {
            // Just for demo purposes. Not needed per say.
            $this->theme = wp_get_theme();

            // Set the default arguments
            $this->setArguments();

            //Create default color 
            $this->redux_default_color();

            $this->redux_default_theme_fonts();

            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }

            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
        }

        public function setSections()
        {

            $default_color = $this->default_color;
            $default_fonts = $this->default_fonts;

            global $wp_registered_sidebars;
            
            $sidebars = array();

            if ( !empty($wp_registered_sidebars) ) {
                foreach ($wp_registered_sidebars as $sidebar) {
                    $sidebars[$sidebar['id']] = $sidebar['name'];
                }
            }
            $columns = array( '1' => esc_html__('1 Column', 'cena'),
                '2' => esc_html__('2 Columns', 'cena'),
                '3' => esc_html__('3 Columns', 'cena'),
                '4' => esc_html__('4 Columns', 'cena'),
                '6' => esc_html__('6 Columns', 'cena')
            );
            
            // General Settings Tab
            $this->sections[] = array(
                'icon' => 'el-icon-cogs',
                'title' => esc_html__('General', 'cena'),
                'fields' => array(
                    array(
                        'id'        => 'preload',
                        'type'      => 'switch',
                        'title'     => esc_html__('Preload Website', 'cena'),
                        'default'   => false
                    ),
                    array(
                        'id' => 'select_preloader',
                        'type' => 'image_select',
                        'compiler' => true,
                        'title' => esc_html__('Select Preloader', 'cena'),
                        'subtitle' => esc_html__('Choose a Preloader for your website.', 'cena'),
                        'required'  => array('preload','=',true),
                        'options' => array(
                            'loader1' => array(
                                'title' => 'Loader 1',
                                'img'   => CENA_ASSETS_IMAGES . '/preloader/loader1.png'
                            ),         
                            'loader2' => array(
                                'title' => 'Loader 2',
                                'img'   => CENA_ASSETS_IMAGES . '/preloader/loader2.png'
                            ),              
                            'loader3' => array(
                                'title' => 'Loader 3',
                                'img'   => CENA_ASSETS_IMAGES . '/preloader/loader3.png'
                            ),         
                            'loader4' => array(
                                'title' => 'Loader 4',
                                'img'   => CENA_ASSETS_IMAGES . '/preloader/loader4.png'
                            ),          
                            'loader5' => array(
                                'title' => 'Loader 5',
                                'img'   => CENA_ASSETS_IMAGES . '/preloader/loader5.png'
                            ),         
                            'loader6' => array(
                                'title' => 'Loader 6',
                                'img'   => CENA_ASSETS_IMAGES . '/preloader/loader6.png'
                            ),      
                            'custom_image' => array(
                                'title' => 'Custom image',
                                'img'   => CENA_ASSETS_IMAGES . '/preloader/custom_image.png'
                            ),                                 
                        ),
                        'default' => 'loader1'
                    ),
                    array(
                        'id' => 'media-preloader',
                        'type' => 'media',
                        'required' => array('select_preloader','=', 'custom_image'),
                        'title' => esc_html__('Upload preloader image', 'cena'),
                        'subtitle' => esc_html__('Image File (.gif)', 'cena'),
                        'desc' =>   sprintf( wp_kses( __('You can download some the Gif images <a target="_blank" href="%1$s">here</a>.', 'cena' ),  array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), 'https://loading.io/' ), 
                    ),
                    array(
                        'id'            => 'config_media',
                        'type'          => 'switch',
                        'title'         => esc_html__('Enable Config Image Size', 'cena'),
                        'subtitle'      => esc_html__('Config Image Size in WooCommerce and Media Setting', 'cena'),
                        'default'       => false
                    ),
                    array(
                        'id'            => 'enable_lazyloadimage',
                        'type'          => 'switch',
                        'title'         => esc_html__('Enable LazyLoadImage', 'cena'),
                        'default'       => true
                    ),   

                    array(
                        'id' => 'ajax_dropdown_megamenu',
                        'type' => 'switch',
                        'title' => esc_html__('Enable "Ajax Dropdown" Mega Menu', 'cena'),
                        'default' => false,
                    ),
                    
                )
            );
            // Header
            $this->sections[] = array(
                'icon' => 'el el-website',
                'title' => esc_html__('Header', 'cena'),
                'fields' => array(
                    array(
                        'id' => 'media-logo',
                        'type' => 'media',
                        'title' => esc_html__('Logo Upload', 'cena'),
                        'desc' => esc_html__('', 'cena'),
                        'subtitle' => esc_html__('Upload a .png or .gif image that will be your logo.', 'cena'),
                    ),
					array(
                        'id' => 'mobile-logo',
                        'type' => 'media',
                        'title' => esc_html__('Mobile Logo', 'cena'),
                        'desc' => esc_html__('', 'cena'),
                        'subtitle' => esc_html__('Upload a .png or .gif image that will be mobile logo', 'cena'),
                    ),
                    array(
                        'id' => 'header_type',
                        'type' => 'select',
                        'title' => esc_html__('Header Layout Type', 'cena'),
                        'subtitle' => esc_html__('Choose a header for your website.', 'cena'),
                        'options' => cena_tbay_get_header_layouts(),
                        'default' => 'v1'
                    ),
                    array(
                        'id' => 'keep_header',
                        'type' => 'switch',
                        'title' => esc_html__('Keep Header', 'cena'),
                        'default' => false
                    ),
                )
            );


            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Search Form', 'cena'),
                'fields' => array(
                    array(
                        'id'=>'show_searchform',
                        'type' => 'switch',
                        'title' => esc_html__('Show Search Form', 'cena'),
                        'default' => true,
                        'on' => esc_html__('Yes', 'cena'),
                        'off' => esc_html__('No', 'cena'),
                    ),
                    array(
                        'id'=>'search_type',
                        'type' => 'button_set',
                        'title' => esc_html__('Search Content Type', 'cena'),
                        'required' => array('show_searchform','equals',true),
                        'options' => array(
                            'all' => esc_html__('All', 'cena'), 
                            'post' => esc_html__('Post', 'cena'), 
                            'product' => esc_html__('Product', 'cena')),
                        'default' => 'product'
                    ),                    
                    array(
                        'id'=> 'search_in_options',
                        'type' => 'radio',
                        'title' => esc_html__('Search In', 'cena'),
                        'required' => array('search_type', 'equals', 'product'),
                        'options' => array(
                            'only_title' => esc_html__('Only Title', 'cena'), 
                            'all' => esc_html__('All (Title, Content, Sku)', 'cena'), 
                        ),
                        'default' => 'only_title'
                    ), 
                    array(
                        'id'=>'search_category',
                        'type' => 'switch',
                        'title' => esc_html__('Show Categories', 'cena'),
                        'required' => array('search_type', 'equals', array('post', 'product')),
                        'on' => esc_html__('Yes', 'cena'),
                        'off' => esc_html__('No', 'cena'),
                        'default' => 1,
                    ),
                    array(
                        'id' => 'autocomplete_search',
                        'type' => 'switch',
                        'title' => esc_html__('Autocomplete search?', 'cena'),
                        'required' => array('show_searchform','equals',true),
                        'default' => 1
                    ),
                    array(
                        'id' => 'show_search_product_image',
                        'type' => 'switch',
                        'title' => esc_html__('Show Search Result Image', 'cena'),
                        'required' => array('autocomplete_search', '=', '1'),
                        'default' => 1
                    ),
                    array(
                        'id' => 'show_search_product_price',
                        'type' => 'switch',
                        'title' => esc_html__('Show Search Result Price', 'cena'),
                        'required' => array(array('autocomplete_search', '=', '1'), array('search_type', '=', 'product')),
                        'default' => 1
                    ),
                    array(
                        'id' => 'search_max_number_results',
                        'title' => esc_html__('Max number of results show', 'cena'),
                        'required' => array('autocomplete_search', '=', '1'),
                        'default' => 5,
                        'min'   => '2',
                        'step'  => '1',
                        'max'   => '10',
                        'type'  => 'slider'
                    ),  
                )
            );
            // Footer
            $this->sections[] = array(
                'icon' => 'el el-website',
                'title' => esc_html__('Footer', 'cena'),
                'fields' => array(
                    array(
                        'id' => 'footer_type',
                        'type' => 'select',
                        'title' => esc_html__('Footer Layout Type', 'cena'),
                        'subtitle' => esc_html__('Choose a footer for your website.', 'cena'),
                        'options' => cena_tbay_get_footer_layouts(),
                        'default' => 'footer-1'
                    ),
                    array(
                        'id' => 'copyright_text',
                        'type' => 'editor',
                        'title' => esc_html__('Copyright Text', 'cena'),
                        'default' => '<p>Copyright @ 2023 Cena Designed by ThemBay. All Rights Reserved.</p>',
                        'required' => array('footer_type','=','')
                    ),
                    array(
                        'id' => 'back_to_top',
                        'type' => 'switch',
                        'title' => esc_html__('Back To Top Button', 'cena'),
                        'subtitle' => esc_html__('Toggle whether or not to enable a back to top button on your pages.', 'cena'),
                        'default' => true,
                    ),                    
                    array(
                        'id' => 'category_fixed',
                        'type' => 'switch',
                        'title' => esc_html__('Category Fixed', 'cena'),
                        'subtitle' => esc_html__('Toggle whether or not to enable Category Fixed on your pages.', 'cena'),
                        'default' => false,
                    ),                    
                )
            );

            // Mobile
            $this->sections[] = array(
                'icon' => 'el el-photo',
                'title' => esc_html__('Mobile', 'cena'),
            );


            // Footer
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Footer mobile', 'cena'),
                'fields' => array(
                    array(
                        'id' => 'hidden_footer',
                        'type' => 'switch',
                        'title' => esc_html__('Hidden on mobile', 'cena'),
                        'subtitle' => esc_html__('Hide the footer in mobile', 'cena'),
                        'default' => true,
                    ),
                )
            );


            // Menu mobile social settings
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Menu mobile', 'cena'),
                'fields' => array(
                    array(
                        'id'       => 'menu_mobile_type',
                        'type'     => 'button_set',
                        'title'    => esc_html__( 'Menu Mobile Type', 'cena' ),
                        'options'  => array(
                            'smart_menu' => 'Smart Menu',
                            'treeview'   => 'Treeview Menu'
                        ),
                        'default'  => 'treeview'
                    ),
                     array(
                        'id' => 'menu_mobile_themes',
                        'type' => 'button_set', 
                        'title' => esc_html__('Menu mobile theme', 'cena'),
                        'required' => array('menu_mobile_type','=','smart_menu'),
                        'options' => array( 
                            'theme-light'       => esc_html__('Light', 'cena'),
                            'theme-dark'        => esc_html__('Dark', 'cena'),
                        ),
                        'default' => 'theme-light'
                    ),
                    array(
                        'id' => 'enable_menu_mobile_effects',
                        'type' => 'switch',
                        'title' => esc_html__('Menu mobile effects ', 'cena'),
                        'required' => array('menu_mobile_type','=','smart_menu'),
                        'default' => false
                    ),                    
                    array(
                        'id' => 'menu_mobile_effects_panels',
                        'type' => 'select', 
                        'title' => esc_html__('Panels effect', 'cena'),
                        'required' => array('enable_menu_mobile_effects','=', true),
                        'options' => array( 
                            'fx-panels-none'            => esc_html__('No effect', 'cena'),
                            'fx-panels-slide-0'         => esc_html__('Slide 0', 'cena'),
                            'no-effect'                 => esc_html__('Slide 30', 'cena'),
                            'fx-panels-slide-100'       => esc_html__('Slide 100', 'cena'),
                            'fx-panels-slide-up'        => esc_html__('Slide uo', 'cena'),
                            'fx-panels-zoom'            => esc_html__('Zoom', 'cena'),
                        ),
                        'default' => 'no-effect'
                    ),                    
                    array(
                        'id' => 'menu_mobile_effects_listitems',
                        'type' => 'select', 
                        'title' => esc_html__('List items effect', 'cena'),
                        'required' => array('enable_menu_mobile_effects','=', true),
                        'options' => array( 
                            'no-effect'                          => esc_html__('No effect', 'cena'),
                            'fx-listitems-drop'         => esc_html__('Drop', 'cena'),
                            'fx-listitems-fade'         => esc_html__('Fade', 'cena'),
                            'fx-listitems-slide'        => esc_html__('slide', 'cena'),
                        ),
                        'default' => 'no-effect'
                    ),
                    array(
                        'id'       => 'menu_mobile_title',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Menu mobile Title', 'cena' ),
                        'default'  => esc_html__( 'Menu', 'cena' ),
                    ), 
                    array(
                        'id' => 'enable_menu_mobile_search',
                        'type' => 'switch',
                        'title' => esc_html__('Search menu item', 'cena'),
                        'required' => array('menu_mobile_type','=','smart_menu'),
                        'default' => false
                    ),                                     
                    array(
                        'id'       => 'menu_mobile_search_items',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Search item menu placeholder', 'cena' ),
                        'required' => array('enable_menu_mobile_search','=', true),
                        'default'  => esc_html__( 'Search in menu...', 'cena' ),
                    ),                    
                    array(
                        'id'       => 'menu_mobile_no_esults',
                        'type'     => 'text',
                        'title'    => esc_html__( '“No results” text', 'cena' ),
                        'required' => array('enable_menu_mobile_search','=', true),
                        'default'  => esc_html__( 'No results found.', 'cena' ),
                    ),                    
                    array(
                        'id'       => 'menu_mobile_search_splash',
                        'type'     => 'textarea',
                        'title'    => esc_html__( 'Search text splash', 'cena' ),
                        'required' => array('enable_menu_mobile_search','=', true),
                        'default'  => esc_html__( 'What are you looking for? </br> Start typing to search the menu.', 'cena' ),
                    ),
                    array(
                        'id' => 'enable_menu_mobile_counters',
                        'type' => 'switch',
                        'title' => esc_html__('Menu mobile counters', 'cena'),
                        'required' => array('menu_mobile_type','=','smart_menu'),
                        'default' => false
                    ),                     
                    array(
                        'id' => 'enable_menu_social',
                        'type' => 'switch',
                        'title' => esc_html__('Menu mobile social', 'cena'),
                        'required' => array('menu_mobile_type','=','smart_menu'),
                        'default' => false
                    ), 

                    array(
                        'id'          => 'menu_social_slides',
                        'type'        => 'slides',
                        'title'       => esc_html__( 'Menu mobile social slides', 'cena' ),
                        'desc'        => esc_html__( 'This social will store all slides values into a multidimensional array to use into a foreach loop.', 'cena' ),
                        'class' => 'remove-upload-slides',
                        'show' => array(
                            'title' => true,
                            'description' => false,
                            'url' => true,
                        ),
                        'required' => array('enable_menu_social','=', true),
                        'placeholder'   => array(
                            'title'      => esc_html__( 'Enter icon name', 'cena' ),
                            'url'       => esc_html__( 'Link icon', 'cena' ),
                        ),
                    ),
                    array(
                        'id'   => 'opt-divide',
                        'class' => 'big-divide',
                        'type' => 'divide'
                    ),

                    array(
                        'id'       => 'menu_mobile_one_select',
                        'type'     => 'select',
                        'data'     => 'menus',
                        'title'    => esc_html__( 'Main menu', 'cena' ),
                        'subtitle' => '<em>'.esc_html__('Tab 1 menu option', 'cena').'</em>',
                        'desc'     => esc_html__( 'Select the menu you want to display.', 'cena' ),
                    ),
                    array(
                        'id'       => 'menu_mobile_tab_one',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Tab 1 title', 'cena' ),
                        'required' => array('enable_menu_second','=', true),
                        'default'  => esc_html__( 'Menu', 'cena' ),
                    ), 
                    array(
                        'id'       => 'menu_mobile_tab_one_icon',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Tab 1 icon', 'cena' ),
                        'required' => array('enable_menu_second','=', true),
                        'desc'     => esc_html__( 'Enter icon name of font: awesome, simplelineicons', 'cena' ),
                        'default'  => 'icon-menu icons',
                    ), 
                    array(
                        'id' => 'enable_menu_second',
                        'type' => 'switch',
                        'title' => esc_html__('Enable Tab 2', 'cena'),
                        'required' => array('menu_mobile_type','=','smart_menu'),
                        'default' => false
                    ),    

                    array(
                        'id'       => 'menu_mobile_tab_scond',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Tab 2 title', 'cena' ),
                        'required' => array('enable_menu_second','=', true),
                        'default'  => esc_html__( 'Categories', 'cena' ),
                    ), 

                    array(
                        'id'       => 'menu_mobile_second_select',
                        'type'     => 'select',
                        'data'     => 'menus',
                        'title'    => esc_html__( 'Tab 2 menu option', 'cena' ),
                        'required' => array('enable_menu_second','=', true),
                        'desc'     => esc_html__( 'Select the menu you want to display.', 'cena' ),
                    ),
                    array(
                        'id'       => 'menu_mobile_tab_second_icon',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Tab 2 icon', 'cena' ),
                        'required' => array('enable_menu_second','=', true),
                        'desc'     => esc_html__( 'Enter icon name of font: awesome, simplelineicons', 'cena' ),
                        'default'  => 'icon-grid icons',
                    ), 
                )
            );

            // Blog settings
            $this->sections[] = array(
                'icon' => 'el el-pencil',
                'title' => esc_html__('Blog', 'cena'),
                'fields' => array(
                    array(
                        'id' => 'show_blog_breadcrumbs',
                        'type' => 'switch',
                        'title' => esc_html__('Breadcrumbs', 'cena'),
                        'default' => 1
                    ),
                    array (
                        'title' => esc_html__('Breadcrumbs Background Color', 'cena'),
                        'subtitle' => '<em>'.esc_html__('The breadcrumbs background color of the site.', 'cena').'</em>',
                        'id' => 'blog_breadcrumb_color',
                        'type' => 'color',
                        'transparent' => false,
                    ),
                    array(
                        'id' => 'blog_breadcrumb_image',
                        'type' => 'media',
                        'title' => esc_html__('Breadcrumbs Background', 'cena'),
                        'subtitle' => esc_html__('Upload a .jpg or .png image that will be your breadcrumbs.', 'cena'),
                    ),
                )
            );
            // Archive Blogs settings
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Blog & Post Archives', 'cena'),
                'fields' => array(
                    array(
                        'id' => 'blog_archive_layout',
                        'type' => 'image_select',
                        'compiler' => true,
                        'title' => esc_html__('Layout', 'cena'),
                        'subtitle' => esc_html__('Select the variation you want to apply on your store.', 'cena'),
                        'options' => array(
                            'main' => array(
                                'title' => 'Main Only',
                                'alt' => 'Main Only',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen1.png'
                            ),
                            'left-main' => array(
                                'title' => 'Left - Main Sidebar',
                                'alt' => 'Left - Main Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen2.png'
                            ),
                            'main-right' => array(
                                'title' => 'Main - Right Sidebar',
                                'alt' => 'Main - Right Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen3.png'
                            ),
                            'left-main-right' => array(
                                'title' => 'Left - Main - Right Sidebar',
                                'alt' => 'Left - Main - Right Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen4.png'
                            ),
                        ),
                        'default' => 'left-main'
                    ),
                    array(
                        'id' => 'blog_archive_fullwidth',
                        'type' => 'switch',
                        'title' => esc_html__('Is Full Width?', 'cena'),
                        'default' => false
                    ),
                    array(
                        'id' => 'blog_archive_left_sidebar',
                        'type' => 'select',
                        'title' => esc_html__('Archive Left Sidebar', 'cena'),
                        'subtitle' => esc_html__('Choose a sidebar for left sidebar.', 'cena'),
                        'options' => $sidebars,
                        'default' => 'blog-left-sidebar'
                    ),
                    array(
                        'id' => 'blog_archive_right_sidebar',
                        'type' => 'select',
                        'title' => esc_html__('Archive Right Sidebar', 'cena'),
                        'subtitle' => esc_html__('Choose a sidebar for right sidebar.', 'cena'),
                        'options' => $sidebars,
                        'default' => 'blog-right-sidebar'
                        
                    ),
                    array(
                        'id' => 'blog_columns',
                        'type' => 'select',
                        'title' => esc_html__('Blog Columns', 'cena'),
                        'options' => $columns,
                        'default' => 1
                    ),

                )
            );
            // Single Blogs settings
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Blog', 'cena'),
                'fields' => array(
                    
                    array(
                        'id' => 'blog_single_layout',
                        'type' => 'image_select',
                        'compiler' => true,
                        'title' => esc_html__('Archive Blog Layout', 'cena'),
                        'subtitle' => esc_html__('Select the variation you want to apply on your store.', 'cena'),
                        'options' => array(
                            'main' => array(
                                'title' => 'Main Only',
                                'alt' => 'Main Only',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen1.png'
                            ),
                            'left-main' => array(
                                'title' => 'Left - Main Sidebar',
                                'alt' => 'Left - Main Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen2.png'
                            ),
                            'main-right' => array(
                                'title' => 'Main - Right Sidebar',
                                'alt' => 'Main - Right Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen3.png'
                            ),
                            'left-main-right' => array(
                                'title' => 'Left - Main - Right Sidebar',
                                'alt' => 'Left - Main - Right Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen4.png'
                            ),
                        ),
                        'default' => 'left-main'
                    ),
                    array(
                        'id'        => 'blog_single_fullwidth',
                        'type'      => 'switch',
                        'title'     => esc_html__('Is Full Width?', 'cena'),
                        'default'   => false
                    ),
                    array(
                        'id'        => 'blog_single_left_sidebar',
                        'type'      => 'select',
                        'title'     => esc_html__('Single Blog Left Sidebar', 'cena'),
                        'subtitle'  => esc_html__('Choose a sidebar for left sidebar.', 'cena'),
                        'options'   => $sidebars,
                        'default'   => 'blog-left-sidebar'
                    ),
                    array(
                        'id'        => 'blog_single_right_sidebar',
                        'type'      => 'select',
                        'title'     => esc_html__('Single Blog Right Sidebar', 'cena'),
                        'subtitle'  => esc_html__('Choose a sidebar for right sidebar.', 'cena'),
                        'options'   => $sidebars,
                        'default'   => 'blog-right-sidebar'
                    ),
                    array(
                        'id' => 'show_blog_social_share',
                        'type' => 'switch',
                        'title' => esc_html__('Show Social Share', 'cena'),
                        'default' => 1
                    ),
                    array(
                        'id' => 'show_blog_releated',
                        'type' => 'switch',
                        'title' => esc_html__('Show Releated Posts', 'cena'),
                        'default' => 1
                    ),
                    array(
                        'id' => 'number_blog_releated',
                        'type' => 'text',
                        'title' => esc_html__('Number of related posts to show', 'cena'),
                        'required' => array('show_blog_releated', '=', '1'),
                        'default' => 3,
                        'min' => '1',
                        'step' => '1',
                        'max' => '20',
                        'type' => 'slider'
                    ),
                    array(
                        'id' => 'releated_blog_columns',
                        'type' => 'select',
                        'title' => esc_html__('Releated Blogs Columns', 'cena'),
                        'required' => array('show_blog_releated', '=', '1'),
                        'options' => $columns,
                        'default' => 3
                    ),

                )
            );
            // Woocommerce
            $this->sections[] = array(
                'icon' => 'el el-shopping-cart',
                'title' => esc_html__('Woocommerce', 'cena'),
                'fields' => array(
                    array(
                        'title'    => esc_html__('Sale Tag Settings', 'cena'),
                        'subtitle' => '<em>'.esc_html__('Predefined Format', 'cena').'</em>',
                        'id'       => 'sale_tags',
                        'type'     => 'radio',
                        'options'  => array( 
                            'Sale!' => esc_html__('Sale!' ,'cena'),
                            'Save {percent-diff}%' => esc_html__('Save {percent-diff}% (e.g "Save 50%")' ,'cena'),
                            'Save {symbol}{price-diff}' => esc_html__('Save {symbol}{price-diff} (e.g "Save $50")' ,'cena'),
                            'custom' => esc_html__('Custom Format (e.g -50%, -$50)' ,'cena')
                        ),
                        'default' => 'custom'
                    ),
                    array(
                        'id'        => 'sale_tag_custom',
                        'type'      => 'text',
                        'title'     => esc_html__( 'Custom Format', 'cena' ),
                        'desc'      => esc_html__('{price-diff} inserts the dollar amount off.', 'cena'). '</br>'.
                                       esc_html__('{percent-diff} inserts the percent reduction (rounded).', 'cena'). '</br>'.
                                       esc_html__('{symbol} inserts the Default currency symbol.', 'cena'), 
                        'required'  => array('sale_tags','=', 'custom'),
                        'default'   => '-{percent-diff}%'
                    ), 
                    array(
                        'id' => 'enable_label_featured',
                        'type' => 'switch',
                        'title' => esc_html__('Label featured', 'cena'),
                        'subtitle' => esc_html__('Enable/Disable label featured', 'cena'),
                        'default' => true
                    ),   
                    array(
                        'id'        => 'custom_label_featured',
                        'type'      => 'text',
                        'title'     => esc_html__( 'Custom Label featured', 'cena' ),
                        'required'  => array('enable_label_featured','=', true),
                        'default'   => esc_html__('Hot', 'cena')
                    ), 
                    array(
                        'id'   => 'opt-divide',
                        'class' => 'big-divide',
                        'type' => 'divide'
                    ),  
                    array(
                        'id' => 'show_product_breadcrumbs',
                        'type' => 'switch',
                        'title' => esc_html__('Breadcrumbs', 'cena'),
                        'default' => 1
                    ),
                    array (
                        'title' => esc_html__('Breadcrumbs Background Color', 'cena'),
                        'subtitle' => '<em>'.esc_html__('The breadcrumbs background color of the site.', 'cena').'</em>',
                        'id' => 'woo_breadcrumb_color',
                        'type' => 'color',
                        'transparent' => false,
                    ),
                    array(
                        'id' => 'woo_breadcrumb_image',
                        'type' => 'media',
                        'title' => esc_html__('Breadcrumbs Background', 'cena'),
                        'subtitle' => esc_html__('Upload a .jpg or .png image that will be your breadcrumbs.', 'cena'),
                    ),
                    array(
                        'id' => 'ajax_update_quantity',
                        'type' => 'switch',
                        'title' => esc_html__('Enable/Disable Ajax update quantity', 'cena'),
                        'subtitle' => esc_html__('Enable/Disable Ajax update quantity in Cart Page', 'cena'),
                        'default' => true
                    ),  
                )
            );
            // Archive settings
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Product Archives', 'cena'),
                'fields' => array(
                    array(
                        'id' => 'product_archive_layout',
                        'type' => 'image_select',
                        'compiler' => true,
                        'title' => esc_html__('Archive Product Layout', 'cena'),
                        'subtitle' => esc_html__('Select the layout you want to apply on your archive product page.', 'cena'),
                        'options' => array(
                            'main' => array(
                                'title' => 'Main Content',
                                'alt' => 'Main Content',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen1.png'
                            ),
                            'left-main' => array(
                                'title' => 'Left Sidebar - Main Content',
                                'alt' => 'Left Sidebar - Main Content',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen2.png'
                            ),
                            'main-right' => array(
                                'title' => 'Main Content - Right Sidebar',
                                'alt' => 'Main Content - Right Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen3.png'
                            ),
                            'left-main-right' => array(
                                'title' => 'Left Sidebar - Main Content - Right Sidebar',
                                'alt' => 'Left Sidebar - Main Content - Right Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen4.png'
                            ),
                        ),
                        'default' => 'left-main'
                    ),
                    array(
                        'id' => 'product_archive_fullwidth',
                        'type' => 'switch',
                        'title' => esc_html__('Is Full Width?', 'cena'),
                        'default' => false
                    ),
                    array(
                        'id' => 'product_archive_left_sidebar',
                        'type' => 'select',
                        'title' => esc_html__('Archive Left Sidebar', 'cena'),
                        'subtitle' => esc_html__('Choose a sidebar for left sidebar.', 'cena'),
                        'options' => $sidebars,
                        'default' => 'product-left-sidebar'
                    ),
                    array(
                        'id' => 'product_archive_right_sidebar',
                        'type' => 'select',
                        'title' => esc_html__('Archive Right Sidebar', 'cena'),
                        'subtitle' => esc_html__('Choose a sidebar for right sidebar.', 'cena'),
                        'options' => $sidebars,
                        'default' => 'product-right-sidebar'
                    ),
                    array(
                        'id' => 'product_display_mode',
                        'type' => 'select',
                        'title' => esc_html__('Display Mode', 'cena'),
                        'subtitle' => esc_html__('Choose a default layout archive product.', 'cena'),
                        'options' => array('grid' => esc_html__('Grid', 'cena'), 'list' => esc_html__('List', 'cena')),
                        'default' => 'grid'
                    ),                    
                    array(
                        'id' => 'title_sidebar_mobile',
                        'type' => 'text',
                        'title' => esc_html__('Sidebar Title Mobile', 'cena'),
                        'default' => esc_html__( 'sidebar', 'cena' )
                    ),
                    array(
                        'id' => 'number_products_per_page',
                        'type' => 'text',
                        'title' => esc_html__('Number of Products Per Page', 'cena'),
                        'default' => 9,
                        'min' => '1',
                        'step' => '1',
                        'max' => '100',
                        'type' => 'slider'
                    ),
                    array(
                        'id' => 'product_columns',
                        'type' => 'select',
                        'title' => esc_html__('Product Columns', 'cena'),
                        'options' => $columns,
                        'default' => 3
                    ),
                    array(
                        'id' => 'show_swap_image',
                        'type' => 'switch',
                        'title' => esc_html__('Show Second Image (Hover)', 'cena'),
                        'default' => 1
                    ),
                )
            );
            // Product Page
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Single Product', 'cena'),
                'fields' => array(
                    array(
                        'id' => 'product_single_layout',
                        'type' => 'image_select',
                        'compiler' => true,
                        'title' => esc_html__('Single Product Layout', 'cena'),
                        'subtitle' => esc_html__('Select the layout you want to apply on your Single Product Page.', 'cena'),
                        'options' => array(
                            'main' => array(
                                'title' => 'Main Only',
                                'alt' => 'Main Only',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen1.png'
                            ),
                            'left-main' => array(
                                'title' => 'Left - Main Sidebar',
                                'alt' => 'Left - Main Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen2.png'
                            ),
                            'main-right' => array(
                                'title' => 'Main - Right Sidebar',
                                'alt' => 'Main - Right Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen3.png'
                            ),
                            'left-main-right' => array(
                                'title' => 'Left - Main - Right Sidebar',
                                'alt' => 'Left - Main - Right Sidebar',
                                'img' => get_template_directory_uri() . '/inc/assets/images/screen4.png'
                            ),
                        ),
                        'default' => 'main'
                    ),
                    array(
                        'id' => 'product_single_fullwidth',
                        'type' => 'switch',
                        'title' => esc_html__('Is Full Width?', 'cena'),
                        'default' => false
                    ),
                    array(
                        'id' => 'product_single_left_sidebar',
                        'type' => 'select',
                        'title' => esc_html__('Single Product Left Sidebar', 'cena'),
                        'subtitle' => esc_html__('Choose a sidebar for left sidebar.', 'cena'),
                        'options' => $sidebars,
                        'default' => 'product-left-sidebar'
                    ),
                    array(
                        'id' => 'product_single_right_sidebar',
                        'type' => 'select',
                        'title' => esc_html__('Single Product Right Sidebar', 'cena'),
                        'subtitle' => esc_html__('Choose a sidebar for right sidebar.', 'cena'),
                        'options' => $sidebars,
                        'default' => 'product-right-sidebar'
                    ),
                    array(
                        'id' => 'style_single_product',
                        'type' => 'select',
                        'title' => esc_html__('Style Single Product Thumbnail', 'cena'),
                        'subtitle' => esc_html__('Choose a style single product thumbnail.', 'cena'),
                        'options' => array(
                                'horizontal'  => 'Thumbnail Horizontal',
                                'vertical'    => 'Thumbnail Vertical'
                        ),
                        'default' => 'horizontal'
                    ),
                    array( 
                        'id'   => 'opt-divide',
                        'class' => 'big-divide',
                        'type' => 'divide'
                    ), 
                    array(
                        'id' => 'enable_total_sales',
                        'type' => 'switch',
                        'title' => esc_html__('Enable Total Sales', 'cena'),
                        'default' => true
                    ),                     
                    array(
                        'id' => 'enable_buy_now',
                        'type' => 'switch',
                        'title' => esc_html__('Enable Buy Now', 'cena'),
                        'default' => false
                    ),  
                    array(
                        'title' => esc_html__('Background Buy Now', 'cena'),
                        'id' => 'bg_buy_now', 
                        'required' => array('enable_buy_now','=',true),
                        'type' => 'color',
                        'transparent' => false,
                        'default' => $default_color['bg_buy_now'],
                    ),     
                    array( 
                        'id' => 'redirect_buy_now',
                        'required' => array('enable_buy_now','=',true),
                        'type' => 'button_set',
                        'title' => esc_html__('Redirect to page after Buy Now', 'cena'),
                        'options' => array( 
                                'cart'          => 'Page Cart',
                                'checkout'      => 'Page CheckOut',
                        ),
                        'default' => 'cart'
                    ),
                    array( 
                        'id'   => 'opt-divide',
                        'class' => 'big-divide',
                        'type' => 'divide'
                    ), 
                    array(
                        'id' => 'show_product_social_share',
                        'type' => 'switch',
                        'title' => esc_html__('Show Social Share', 'cena'),
                        'default' => 1
                    ),
                    array(
                        'id' => 'show_product_review_tab',
                        'type' => 'switch',
                        'title' => esc_html__('Show Product Review Tab', 'cena'),
                        'default' => 1
                    ),
                    array(
                        'id' => 'show_product_releated',
                        'type' => 'switch',
                        'title' => esc_html__('Show Products Releated', 'cena'),
                        'default' => 1
                    ),
                    array(
                        'id' => 'show_product_upsells',
                        'type' => 'switch',
                        'title' => esc_html__('Show Products upsells', 'cena'),
                        'default' => 1
                    ),
                    array(
                        'id' => 'show_product_countdown',
                        'type' => 'switch',
                        'title' => esc_html__('Show Products Countdown', 'cena'),
                        'default' => true
                    ),                    
                    array(
                        'id' => 'show_product_nav',
                        'type' => 'switch',
                        'title' => esc_html__('Show Products navigation', 'cena'),
                        'default' => true
                    ),
                    array(
                        'id' => 'number_product_thumbnail',
                        'title' => esc_html__('Number Images Thumbnail to show', 'cena'),
                        'default' => 3,
                        'min'   => '2',
                        'step'  => '1',
                        'max'   => '4',
                        'type'  => 'slider'
                    ),  
                    array(
                        'id' => 'number_product_releated',
                        'title' => esc_html__('Number of related/upsells products to show', 'cena'),
                        'default' => 4,
                        'min' => '1',
                        'step' => '1',
                        'max' => '20',
                        'type' => 'slider'
                    ),
                    array(
                        'id' => 'releated_product_columns',
                        'type' => 'select',
                        'title' => esc_html__('Releated Products Columns', 'cena'),
                        'options' => $columns,
                        'default' => 4
                    ),

                )
            );
            // Style
            $this->sections[] = array(
                'icon' => 'el el-icon-css',
                'title' => esc_html__('Style', 'cena'),
                'fields' => array(
                    array (
                        'title' => esc_html__('Main Theme Color', 'cena'),
                        'subtitle' => '<em>'.esc_html__('The main color of the site.', 'cena').'</em>',
                        'id' => 'main_color',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => $default_color['main_color'],
                    ),
                )
            );
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Typography', 'cena'),
                'fields' => array(
                    array(
                        'title'    => esc_html__('Font Source', 'cena'),
                        'subtitle' => '<em>'.esc_html__('Choose the Font Source', 'cena').'</em>',
                        'id'       => 'font_source',
                        'type'     => 'radio',
                        'options'  => array(
                            '1' => 'Standard + Google Webfonts',
                            '2' => 'Google Custom'
                        ),
                        'default' => '2'
                    ),
                    array(
                        'id'=>'font_google_code',
                        'type' => 'text',
                        'title' => esc_html__('Google Code', 'cena'), 
                        'subtitle' => '<em>'.esc_html__('Paste the provided Google Code', 'cena').'</em>',
                        'default' => 'https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap',
                        'required' => array('font_source','=','2')
                    ),
                    array (
                        'id' => 'main_font_info',
                        'icon' => true,
                        'type' => 'info',
                        'raw' => '<h3 style="margin: 0;"> '.esc_html__('Main Font', 'cena').'</h3>',
                    ),
                    // Standard + Google Webfonts
                    array (
                        'title' => esc_html__('Font Face', 'cena'),
                        'subtitle' => '<em>'.esc_html__('Pick the Main Font for your site.', 'cena').'</em>',
                        'id' => 'main_font',
                        'type' => 'typography',
                        'line-height' => false,
                        'text-align' => false,
                        'font-style' => false,
                        'font-weight' => false,
                        'all_styles'=> true,
                        'font-size' => false,
                        'color' => false,
                        'default' => array (
                            'font-family' => '',
                            'subsets' => '',
                        ),
                        'required' => array('font_source','=','1')
                    ),
                    
                    // Google Custom                        
                    array (
                        'title' => esc_html__('Google Font Face', 'cena'),
                        'subtitle' => '<em>'.esc_html__('Enter your Google Font Name for the theme\'s Main Typography', 'cena').'</em>',
                        'desc' => esc_html__('e.g.: open sans', 'cena'),
                        'id' => 'main_google_font_face',
                        'type' => 'text',
                        'default' => '',
                        'required' => array('font_source','=','2')
                    ),

                    array (
                        'id' => 'secondary_font_info',
                        'icon' => true,
                        'type' => 'info',
                        'raw' => '<h3 style="margin: 0;"> '.__(' Secondary Font', 'cena').'</h3>',
                    ),
                    
                    // Standard + Google Webfonts
                    array (
                        'title' => esc_html__('Font Face', 'cena'),
                        'subtitle' => '<em>'.esc_html__('Pick the Secondary Font for your site.', 'cena').'</em>',
                        'id' => 'secondary_font',
                        'type' => 'typography',
                        'line-height' => false,
                        'text-align' => false,
                        'font-style' => false,
                        'font-weight' => false,
                        'all_styles'=> true,
                        'font-size' => false,
                        'color' => false,
                        'default' => array (
                            'font-family-second' => '',
                            'subsets' => '',
                        ),
                        'required' => array('font_source','=','1')
                        
                    ),
                    
                    // Google Custom                        
                    array (
                        'title' => esc_html__('Google Font Face', 'cena'),
                        'subtitle' => '<em>'.esc_html__('Enter your Google Font Name for the theme\'s Secondary Typography', 'cena').'</em>',
                        'desc' => esc_html__('e.g.: open sans', 'cena'),
                        'id' => 'secondary_google_font_face',
                        'type' => 'text',
                        'default' => '',
                        'required' => array('font_source','=','2')
                    ),
                )
            );
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Top Bar', 'cena'),
                'fields' => array(
                    array(
                        'id'=>'topbar_bg',
                        'type' => 'color',
                        'title' => esc_html__('Background', 'cena'),
                        'transparent' => false,
                        'default' => '',
                    ),
                    array(
                        'title' => esc_html__('Text Color', 'cena'),
                        'id' => 'topbar_text_color',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => '',
                    ),
                    array(
                        'title' => esc_html__('Top Cart Background', 'cena'),
                        'id' => 'top_cart_background',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => '',
                    ),
                    array(
                        'title' => esc_html__('Icon Color', 'cena'),
                        'id' => 'topbar_icon_color',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => $default_color['topbar_icon_color'],
                    ),
                )
            );
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Header', 'cena'),
                'fields' => array(
                    array(
                        'id'=>'header_bg',
                        'type' => 'color',
                        'title' => esc_html__('Background', 'cena'),
                        'transparent' => false,
                        'default' => '',
                    ),
                    array(
                        'title' => esc_html__('Link Color', 'cena'),
                        'id' => 'header_link_color',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => '',
                    ),
                )
            );
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Main Menu', 'cena'),
                'fields' => array(
                    array(
                        'title' => esc_html__('Link Color', 'cena'),
                        'id' => 'main_menu_link_color',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => '',
                    ),
                    array(
                        'title' => esc_html__('Link Color Active', 'cena'),
                        'id' => 'main_menu_link_color_active',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => $default_color['main_menu_link_color_active'],
                    ),                    
                    array(
                        'title' => esc_html__('Background Color Active', 'cena'),
                        'id' => 'main_menu_bg_color_active',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => $default_color['main_menu_bg_color_active'],
                    ),
                )
            );
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Footer', 'cena'),
                'fields' => array(
                    array(
                        'title' => esc_html__('Heading Color', 'cena'),
                        'id' => 'footer_heading_color',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => $default_color['footer_heading_color'],
                    ),
                    array(
                        'title' => esc_html__('Text Color', 'cena'),
                        'id' => 'footer_text_color',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => $default_color['footer_text_color'],
                    ),
                    array(
                        'title' => esc_html__('Link Color', 'cena'),
                        'id' => 'footer_link_color',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => $default_color['footer_link_color'],
                    ),
                )
            );
            
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Copyright', 'cena'),
                'fields' => array(
                    array(
                        'id'=>'copyright_bg',
                        'type' => 'color',
                        'title' => esc_html__('Background', 'cena'),
                        'transparent' => false,
                        'default' => $default_color['copyright_bg'],
                    ),
                    array(
                        'title' => esc_html__('Text Color', 'cena'),
                        'id' => 'copyright_text_color',
                        'type' => 'color',
                        'transparent' => false,
                        'default' => $default_color['copyright_text_color'],
                    ),
                )
            );
            // Social Media
            $this->sections[] = array(
                'icon' => 'el el-file',
                'title' => esc_html__('Social Share', 'cena'),
                'fields' => array(
                    array(
                        'id'        =>'code_share',
                        'type'      => 'textarea',
                        'title'     => esc_html__('Addthis your code', 'cena'), 
                        'subtitle'  => esc_html__('Addthis your code', 'cena'),
                        'desc'      => esc_html__('You get your code share in https://www.addthis.com', 'cena'),
                        'validate'  => 'html_custom',
                        'default'   => '<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-59f2a47d2f1aaba2"></script>'
                    ),
                )
            );

            // Performance
            $this->sections[] = array(
                'icon' => 'el-icon-cog',
                'title' => esc_html__('Performance', 'cena'),
            );   
            $this->sections[] = array(
                'subsection' => true,
                'title' => esc_html__('Performance', 'cena'),
                'fields' => array(
                    array (
                        'id'       => 'minified_js',
                        'type'     => 'switch',
                        'title'    => esc_html__('Include minified JS', 'cena'),
                        'subtitle' => esc_html__('Minified version of functions.js and device.js file will be loaded', 'cena'),
                        'default' => true
                    ),
                )
            );

            // Custom Code
            $this->sections[] = array(
                'icon' => 'el-icon-css',
                'title' => esc_html__('Custom CSS/JS', 'cena'),
                'fields' => array(
                    array (
                        'title' => esc_html__('Custom CSS', 'cena'),
                        'subtitle' => '<em>'.esc_html__('Paste your custom CSS code here.</em>', 'cena').'</em>',
                        'id' => 'custom_css',
                        'type' => 'ace_editor',
                        'mode' => 'css',
                    ),
                    
                    array (
                        'title' => esc_html__('Header JavaScript Code', 'cena'),
                        'subtitle' => '<em>'.esc_html__('Paste your custom JS code here. The code will be added to the header of your site.', 'cena').'</em>',
                        'id' => 'header_js',
                        'type' => 'ace_editor',
                        'mode' => 'javascript',
                    ),
                    
                    array (
                        'title' => esc_html__('Footer JavaScript Code', 'cena'),
                        'subtitle' => '<em>'.esc_html__('Here is the place to paste your Google Analytics code or any other JS code you might want to add to be loaded in the footer of your website.', 'cena').'</em>',
                        'id' => 'footer_js',
                        'type' => 'ace_editor',
                        'mode' => 'javascript',
                    ),
                )
            );
            $this->sections[] = array(
                'title' => esc_html__('Import / Export', 'cena'),
                'desc' => esc_html__('Import and Export your Redux Framework settings from file, text or URL.', 'cena'),
                'icon' => 'el-icon-refresh',
                'fields' => array(
                    array(
                        'id' => 'opt-import-export',
                        'type' => 'import_export',
                        'title' => 'Import Export',
                        'subtitle' => 'Save and restore your Redux options',
                        'full_width' => false,
                    ),
                ),
            );

            $this->sections[] = array(
                'type' => 'divide',
            );
        }
        /**
         * All the possible arguments for Redux.
         * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
         * */
        public function setArguments()
        {

            $theme = wp_get_theme(); // For use with some settings. Not necessary.

            $this->args = array(
                // TYPICAL -> Change these values as you need/desire
                'opt_name' => 'cena_tbay_theme_options',
                // This is where your data is stored in the database and also becomes your global variable name.
                'display_name' => $theme->get('Name'),
                // Name that appears at the top of your panel
                'display_version' => $theme->get('Version'),
                // Version that appears at the top of your panel
                'menu_type' => 'menu',
                //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
                'allow_sub_menu' => true,
                // Show the sections below the admin menu item or not
                'menu_title' => esc_html__('Cena Options', 'cena'),
                'page_title' => esc_html__('Cena Options', 'cena'),

                // You will need to generate a Google API key to use this feature.
                // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
                'google_api_key' => '',
                // Set it you want google fonts to update weekly. A google_api_key value is required.
                'google_update_weekly' => false,
                // Must be defined to add google fonts to the typography module
                'async_typography' => true,
                // Use a asynchronous font on the front end or font string
                //'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
                'admin_bar' => true,
                // Show the panel pages on the admin bar
                'admin_bar_icon' => 'dashicons-portfolio',
                // Choose an icon for the admin bar menu
                'admin_bar_priority' => 50,
                // Choose an priority for the admin bar menu
                'global_variable' => 'tbay_options',
                // Set a different name for your global variable other than the opt_name
                'dev_mode' => false,
                'forced_dev_mode_off' => false,
                // Show the time the page took to load, etc
                'update_notice' => true,
                // If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
                'customizer' => true,
                // Enable basic customizer support
                //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
                //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

                // OPTIONAL -> Give you extra features
                'page_priority' => null,
                // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
                'page_parent' => 'themes.php',
                // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
                'page_permissions' => 'manage_options',
                // Permissions needed to access the options panel.
                'menu_icon' => '',
                // Specify a custom URL to an icon
                'last_tab' => '',
                // Force your panel to always open to a specific tab (by id)
                'page_icon' => 'icon-themes',
                // Icon displayed in the admin panel next to your menu_title
                'page_slug' => '_options',
                // Page slug used to denote the panel
                'save_defaults' => true,
                // On load save the defaults to DB before user clicks save or not
                'default_show' => false,
                // If true, shows the default value next to each field that is not the default value.
                'default_mark' => '',
                // What to print by the field's title if the value shown is default. Suggested: *
                'show_import_export' => true,
                // Shows the Import/Export panel when not used as a field.

                // CAREFUL -> These options are for advanced use only
                'transient_time' => 60 * MINUTE_IN_SECONDS,
                'output' => true,
                // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
                'output_tag' => true,
                // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
                // 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.

                // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
                'database' => '',
                // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
                'system_info' => false,
                // REMOVE

                // HINTS
                'hints' => array(
                    'icon' => 'icon-question-sign',
                    'icon_position' => 'right',
                    'icon_color' => 'lightgray',
                    'icon_size' => 'normal',
                    'tip_style' => array(
                        'color' => 'light',
                        'shadow' => true,
                        'rounded' => false,
                        'style' => '',
                    ),
                    'tip_position' => array(
                        'my' => 'top left',
                        'at' => 'bottom right',
                    ),
                    'tip_effect' => array(
                        'show' => array(
                            'effect' => 'slide',
                            'duration' => '500',
                            'event' => 'mouseover',
                        ),
                        'hide' => array(
                            'effect' => 'slide',
                            'duration' => '500',
                            'event' => 'click mouseleave',
                        ),
                    ),
                )
            );
            
            $this->args['intro_text'] = '';

            // Add content after the form.
            $this->args['footer_text'] = '';
            return $this->args;
        }
    }

    global $reduxConfig;
    $reduxConfig = new Cena_Redux_Framework_Config();
}