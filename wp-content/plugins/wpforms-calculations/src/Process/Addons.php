<?php

namespace WPFormsCalculations\Process;

/**
 * Process calculations in addons.
 *
 * @since 1.0.0
 */
class Addons {

	/**
	 * Process class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Process
	 */
	private $process;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->process = wpforms_calculations()->process;

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		// Process partial entry fields (Save and Resume addon).
		add_filter( 'wpforms_process_filter_save_resume', [ $this->process, 'process_fields' ], 5, 3 );

		// Process abandoned entry (Form Abandonment addon).
		add_filter( 'wpforms_process_filter_form_abandonment', [ $this->process, 'process_fields' ], 10, 3 );
	}
}
