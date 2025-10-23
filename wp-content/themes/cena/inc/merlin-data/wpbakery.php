<?php

class Cena_Merlin_Wpbakery {
	public function import_files_wpb_vc(){
		$rev_sliders = [
			"http://demosamples.thembay.com/cena/revslider/home1.zip",
			"http://demosamples.thembay.com/cena/revslider/home-2.zip",
			"http://demosamples.thembay.com/cena/revslider/home-4.zip",
			"http://demosamples.thembay.com/cena/revslider/home-7.zip",
			"http://demosamples.thembay.com/cena/revslider/home-9.zip",
			"http://demosamples.thembay.com/cena/revslider/home10.zip",
		];

		$data_url = "http://demosamples.thembay.com/cena/data.xml";
		$widget_url = "http://demosamples.thembay.com/cena/widgets.wie";

		return array(
			array(
				'import_file_name'           => 'Home 1',
				'home'                       => 'home',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home1/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home1/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/',
			),
			array(
				'import_file_name'           => 'Home 2',
				'home'                       => 'home-2',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home2/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home2/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/home-2/',
			),
			array(
				'import_file_name'           => 'Home 3',
				'home'                       => 'home-3',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home3/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home3/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/home-3/',
			),
			array(
				'import_file_name'           => 'Home 4',
				'home'                       => 'home-4',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home4/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home4/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/home-4/',
			),
			array(
				'import_file_name'           => 'Home 5',
				'home'                       => 'home-5',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home5/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home5/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/home-5/',
			),
			array(
				'import_file_name'           => 'Home 6',
				'home'                       => 'home-6',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home6/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home6/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/home-6/',
			),
			array(
				'import_file_name'           => 'Home 7',
				'home'                       => 'home-7',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home7/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home7/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/home-7/',
			),
			array(
				'import_file_name'           => 'Home 8',
				'home'                       => 'home-8',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home8/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home8/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/home-8/',
			),
			array(
				'import_file_name'           => 'Home 9',
				'home'                       => 'home-9',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home9/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home9/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/home-9/',
			),
			array(
				'import_file_name'           => 'Home 10',
				'home'                       => 'home-10',
				'import_file_url'          	 => $data_url,
				'import_widget_file_url'     => $widget_url,
				'import_redux'         => array(
					array(
						'file_url'   => "http://demosamples.thembay.com/cena/home10/redux_options.json",
						'option_name' => 'cena_tbay_theme_options',
					),
				),
				'rev_sliders'                => $rev_sliders,
				'import_preview_image_url'   => "http://demosamples.thembay.com/cena/home10/screenshot.jpg",
				'import_notice'              => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'cena' ),
				'preview_url'                => 'https://wpbakery.thembay.com/cena/home-10/',
			),
		);
	}

}