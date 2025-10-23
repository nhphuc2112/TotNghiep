<?php

namespace WPFormsGoogleDrive\Provider;

use WPFormsGoogleDrive\Plugin;
use WPFormsGoogleDrive\Provider\Settings\FormBuilder;
use WPFormsGoogleDrive\Provider\Settings\PageIntegrations;

/**
 * Class Core.
 *
 * @since 1.0.0
 */
class Core extends \WPForms\Providers\Provider\Core {

	/**
	 * Priority for a provider, that will affect loading/placement order.
	 *
	 * @since 1.0.0
	 */
	public const PRIORITY = 35;

	/**
	 * Core constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			[
				'slug' => Plugin::SLUG,
				'name' => Plugin::NAME,
				'icon' => WPFORMS_GOOGLE_DRIVE_URL . 'assets/images/addon-icon.png',
			]
		);
	}

	/**
	 * Provide an instance of the object, that should process the submitted entry.
	 * It will use data from an already saved entry to pass it further to a Provider.
	 *
	 * @since 1.0.0
	 *
	 * @return Process
	 */
	public function get_process(): Process {

		static $process;

		if ( ! $process ) {
			$process = new Process( static::get_instance() );
		}

		return $process;
	}

	/**
	 * Provide an instance of the object, that should display provider settings
	 * on Settings > Integrations page in the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @return PageIntegrations
	 */
	public function get_page_integrations(): PageIntegrations {

		static $integration;

		if ( ! $integration ) {
			$integration = new PageIntegrations( static::get_instance() );
		}

		return $integration;
	}

	/**
	 * Provide an instance of the object, that should display provider settings in the Form Builder.
	 *
	 * @since 1.0.0
	 *
	 * @return FormBuilder
	 */
	public function get_form_builder(): FormBuilder {

		static $builder;

		if ( ! $builder ) {
			$builder = new FormBuilder( static::get_instance() );
		}

		return $builder;
	}
}
