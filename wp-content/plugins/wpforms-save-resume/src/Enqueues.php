<?php

namespace WPFormsSaveResume;

/**
 * Assets enqueues for frontend.
 *
 * @since 1.8.0
 */
class Enqueues {

	/**
	 * Initialize.
	 *
	 * @since 1.8.0
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.0
	 */
	private function hooks() {

		// Front-end related hooks.
		add_action( 'wpforms_frontend_css', [ $this, 'enqueue_css' ] );
		add_action( 'wpforms_save_resume_frontend_confirmation_message_before', [ $this, 'enqueue_css' ] );
		add_action( 'wpforms_frontend_js', [ $this, 'enqueue_js' ] );
		add_action( 'wpforms_frontend_confirmation', [ $this, 'enqueue_confirmation_js' ] );
		add_action( 'wp_footer', [ $this, 'enqueue_footer' ] );

		// Add plugin error messages to frontend.
		add_filter( 'wpforms_frontend_strings', [ $this, 'add_frontend_strings' ] );

		// Conversational Forms integration.
		add_action( 'wpforms_conversational_forms_enqueue_styles', [ $this, 'enqueue_conversational_forms_styles' ] );
	}


	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.8.0
	 *
	 * @param array $forms List of forms on the current page.
	 */
	public function enqueue_css( $forms ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['wpforms_sr_email_is_sent'] ) && ! empty( $forms ) && ! $this->has_forms_with_save_resume( $forms ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-save-resume',
			WPFORMS_SAVE_RESUME_URL . "assets/css/wpforms-save-resume{$min}.css",
			[],
			WPFORMS_SAVE_RESUME_VERSION
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.8.0
	 *
	 * @param array $forms List of forms on the current page.
	 */
	public function enqueue_js( $forms ) {

		if ( ! $this->has_forms_with_save_resume( $forms ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-save-resume',
			WPFORMS_SAVE_RESUME_URL . "assets/js/wpforms-save-resume{$min}.js",
			[ 'wpforms', 'wpforms-validation' ],
			WPFORMS_SAVE_RESUME_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-save-resume',
			'wpforms_save_resume',
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			]
		);
	}

	/**
	 * JS for successfully sent email notification.
	 *
	 * @since 1.8.0
	 */
	public function enqueue_footer() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['wpforms_resume_entry'] ) && empty( $_GET['wpforms_sr_email_is_sent'] ) && empty( $_GET['wpforms_sr_entry_is_completed'] ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-save-resume-query',
			WPFORMS_SAVE_RESUME_URL . "assets/js/wpforms-save-resume-query{$min}.js",
			[ 'jquery' ],
			WPFORMS_SAVE_RESUME_VERSION,
			true
		);
	}

	/**
	 * Pass additional settings and strings to frontend.
	 *
	 * @since 1.8.0
	 *
	 * @param array $strings Frontend strings.
	 *
	 * @return array
	 */
	public function add_frontend_strings( $strings ) {

		$strings['save_resume_hash_error'] = esc_html__( 'Unfortunately, we couldn\'t save your entry. If the problem persists, please contact the site administrator.', 'wpforms-save-resume' );

		return $strings;
	}

	/**
	 * Enqueue styles for Conversational Forms compatibility.
	 *
	 * @since 1.8.0
	 */
	public function enqueue_conversational_forms_styles() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-save-resume-conversational',
			WPFORMS_SAVE_RESUME_URL . "assets/css/wpforms-save-resume-conversational-forms{$min}.css",
			[ 'wpforms-conversational-forms' ],
			WPFORMS_SAVE_RESUME_VERSION
		);
	}

	/**
	 * Whether any of the form has the Save and Resume functionality enabled.
	 *
	 * @since 1.8.0
	 *
	 * @param array $forms List of forms on the current page.
	 */
	private function has_forms_with_save_resume( $forms ) {

		foreach ( (array) $forms as $form ) {
			if ( wpforms_save_resume()->is_enabled( $form ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Enqueue scripts for confirmation page.
	 *
	 * @since 1.8.0
	 */
	public function enqueue_confirmation_js() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-save-resume-confirmation',
			WPFORMS_SAVE_RESUME_URL . "assets/js/wpforms-save-resume-confirmation{$min}.js",
			[ 'jquery' ],
			WPFORMS_SAVE_RESUME_VERSION,
			true
		);
	}
}
