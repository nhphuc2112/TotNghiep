<?php

namespace WPFormsSaveResume;

use WPFormsSaveResume\Email\EmailNotification;

/**
 * The Frontend.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * Current form data.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	protected $form_data;

	/**
	 * Current entry data.
	 *
	 * @var array
	 *
	 * @since 1.11.0
	 */
	protected $entry_data;

	/**
	 * Entry object.
	 *
	 * @var object
	 *
	 * @since 1.0.0
	 */
	protected $entry;

	/**
	 * Is error.
	 *
	 * @var bool
	 *
	 * @since 1.6.0
	 */
	private $is_error = false;

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Init method.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		// Ajax processing.
		add_action( 'wp_ajax_nopriv_wpforms_save_resume', [ $this, 'process_entry' ] );
		add_action( 'wp_ajax_wpforms_save_resume', [ $this, 'process_entry' ] );

		add_filter( 'wpforms_field_properties', [ $this, 'load_field_data' ], 10, 3 );

		// Front-end related hooks.
		add_filter( 'wpforms_frontend_form_data', [ $this, 'load_entry' ] );
		add_filter( 'wpforms_frontend_output_container_after', [ $this, 'display_disclaimer' ], 10, 1 );
		add_filter( 'wpforms_frontend_output_container_after', [ $this, 'display_confirmation' ], 10, 1 );
		add_filter( 'wpforms_frontend_container_class', [ $this, 'add_addon_class' ], 10, 2 );
		add_filter( 'wpforms_frontend_load', [ $this, 'display_form' ], 10, 2 );
		add_action( 'wpforms_frontend_not_loaded', [ $this, 'display_confirmation_message' ], 10, 1 );
		add_action( 'wpforms_frontend_output', [ $this, 'display_entry_expired_message' ], -PHP_INT_MAX, 2 );
		add_action( 'wpforms_frontend_output', [ $this, 'hiding_wrapper_open' ], 1, 5 );
		add_action( 'wpforms_frontend_output', [ $this, 'hiding_wrapper_close' ], PHP_INT_MAX, 5 );
		add_filter( 'wpforms_helpers_templates_include_html_located', [ $this, 'register' ], 10, 2 );
		add_filter( 'wpforms_pro_forms_fields_repeater_frontend_clones_populate_entry', [ $this, 'repeater_clones_populate_entry' ], 10, 3 );

		add_action( 'wpforms_process_entry_saved', [ $this, 'delete_entry' ], 10, 4 );

		// Notifications.
		add_action( 'wp', [ $this, 'send_email' ] );

		// Conversational Forms integration.
		add_filter( 'wpforms_conversational_forms_start_button_disabled', [ $this, 'is_locked_filter' ], 10 );

		// Preventing multiple form submissions for one unique resume link from multiple opened tabs.
		add_filter( 'wpforms_process_initial_errors', [ $this, 'multiple_submission_check' ], 10, 2 );

		// Skip time limit for saving/resuming entries.
		add_filter( 'wpforms_process_time_limit_check_bypass', [ $this, 'skip_time_limit' ] );

		// Add Save Resume error to AJAX response.
		add_filter( 'wpforms_ajax_submit_errors_response', [ $this, 'add_ajax_submit_errors_response' ] );

		// Disable current location for the form resumed from the link.
		add_filter( 'wpforms_geolocation_front_fields_settings_current_location', [ $this, 'disable_current_location' ], 10, 2 );
	}

	/**
	 * Register addon location.
	 *
	 * @since 1.8.0
	 *
	 * @param string $located  Template location.
	 * @param string $template Template.
	 *
	 * @return string
	 */
	public function register( $located, $template ) {

		// Checking if `$template` is an absolute path and passed from this plugin.
		if (
			strpos( $template, WPFORMS_SAVE_RESUME_PATH ) === 0 &&
			is_readable( $template )
		) {
			return $template;
		}

		return $located;
	}

	/**
	 * Load entry data for the current form.
	 *
	 * @since 1.11.0
	 *
	 * @param array|mixed $form_data Form data and settings.
	 *
	 * @return array
	 */
	public function load_entry( $form_data ): array {

		$form_data = (array) $form_data;

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( ! isset( $_GET['wpforms_resume_entry'] ) ) {
			return $form_data;
		}

		$entry = wpforms()->obj( 'entry' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification
		$hash     = ! empty( $_GET['wpforms_resume_entry'] ) ? $_GET['wpforms_resume_entry'] : '';
		$entry_id = Entry::get_entry_by_hash( $hash );

		if ( $entry_id === 0 ) {
			return $form_data;
		}

		$this->entry_data = $entry->get( $entry_id, [ 'cap' => false ] );

		if ( empty( $this->entry_data ) || $this->entry_data->status === 'trash' ) {
			$this->entry_data = [];

			return $form_data;
		}

		// In the case, when multiple forms are displayed on the same page.
		if ( (int) $this->entry_data->form_id !== (int) $form_data['id'] ) {
			$this->entry_data = [];

			return $form_data;
		}

		$this->entry_data = wpforms_decode( $this->entry_data->fields );

		return $form_data;
	}

	/**
	 * Repeater field clones entry data.
	 *
	 * @since 1.11.0
	 *
	 * @param array|mixed $entry     Entry data.
	 * @param array       $form_data Form data.
	 *
	 * @return array
	 */
	public function repeater_clones_populate_entry( $entry, array $form_data ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$entry = (array) $entry;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['wpforms_resume_entry'] ) ) {
			return $entry;
		}

		if ( empty( $this->entry_data ) ) {
			return $entry;
		}

		return $this->entry_data;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.0.0
	 * @deprecated 1.8.0
	 *
	 * @param array $forms List of forms on the current page.
	 */
	public function enqueue_css( $forms ) {

		_deprecated_function( __METHOD__, '1.8.0 of the WPForms Save and Resume addon', '\WPFormsSaveResume\Enqueues::enqueue_css()' );

		( new Enqueues() )->enqueue_css( $forms );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 * @deprecated 1.8.0
	 *
	 * @param array $forms List of forms on the current page.
	 */
	public function enqueue_js( $forms ) {

		_deprecated_function( __METHOD__, '1.8.0 of the WPForms Save and Resume addon', '\WPFormsSaveResume\Enqueues::enqueue_js()' );

		( new Enqueues() )->enqueue_js( $forms );
	}

	/**
	 * JS for successfully sent email notification.
	 *
	 * @since 1.8.0
	 * @deprecated 1.8.0
	 */
	public function enqueue_footer() {

		_deprecated_function( __METHOD__, '1.8.0 of the WPForms Save and Resume addon', '\WPFormsSaveResume\Enqueues::enqueue_footer()' );

		( new Enqueues() )->enqueue_footer();
	}

	/**
	 * Enqueue styles for Conversational Forms compatibility.
	 *
	 * @since 1.0.0
	 * @deprecated 1.8.0
	 */
	public function enqueue_conversational_forms_styles() {

		_deprecated_function( __METHOD__, '1.8.0 of the WPForms Save and Resume addon', '\WPFormsSaveResume\Enqueues::enqueue_conversational_forms_styles()' );

		( new Enqueues() )->enqueue_conversational_forms_styles();
	}

	/**
	 * Create a new entry.
	 *
	 * @since 1.0.0
	 */
	public function process_entry() {

		// Make sure we have required data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['wpforms'] ) ) {
			wp_send_json_error();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$form_id = ! empty( $_POST['wpforms']['id'] ) ? absint( $_POST['wpforms']['id'] ) : 0;

		if ( $form_id === 0 ) {
			wp_send_json_error();
		}

		$entry = new Entry();

		// Prepare entry data.
		// Check if entry is spam.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput
		if ( is_string( $entry->prepare_data( $form_id, stripslashes_deep( $_POST['wpforms'] ) ) ) ) {
			wp_send_json_error();
		}

		$entry_id = Entry::get_existing_partial_entry_id( $form_id );
		$data     = $entry_id !== 0 ? $entry->update_entry( $entry_id ) : $entry->add_entry();

		/**
		 * Fire after partial entry was processed.
		 *
		 * @since 1.2.0
		 *
		 * @param int $form_id  Form ID.
		 * @param int $entry_id Entry ID.
		 */
		do_action( 'wpforms_save_resume_frontend_process_finished', $form_id, $entry_id );

		wp_send_json_success( $data );
	}

	/**
	 * Load entry to the form.
	 *
	 * @since 1.0.0
	 *
	 * @param array|mixed $properties Properties.
	 * @param array       $field      Field.
	 * @param array       $form_data  Form information.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function load_field_data( $properties, array $field, array $form_data ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$properties = (array) $properties;
		$id         = (int) ! empty( $field['id'] ) ? $field['id'] : 0;

		if ( ! isset( $this->entry_data[ $id ] ) ) {
			return $properties;
		}

		return ( new Entry() )->get_entry( $properties, $field, $this->entry_data );
	}

	/**
	 * Templates for confirmation block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form information.
	 */
	public function display_confirmation( $form_data ) {

		if ( empty( $form_data['settings']['save_resume_enable'] ) ) {
			return;
		}

		if (
			empty( $form_data['settings']['save_resume_enable_resume_link'] ) &&
			empty( $form_data['settings']['save_resume_enable_email_notification'] ) &&
			empty( $form_data['settings']['save_resume_enable_automatically_send_email'] )
		) {
			return;
		}

		$confirmation         = ! empty( $form_data['settings']['save_resume_confirmation_message'] ) ? $form_data['settings']['save_resume_confirmation_message'] : Settings::get_default_confirmation_message();
		$confirmation_callout = ! empty( $form_data['settings']['save_resume_confirmation_message_callout'] ) ? $form_data['settings']['save_resume_confirmation_message_callout'] : '';
		$action               = remove_query_arg( 'wpforms-save-resume' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			WPFORMS_SAVE_RESUME_PATH . 'templates/confirmation',
			[
				'form_data'            => $form_data,
				'confirmation'         => $confirmation,
				'confirmation_callout' => $confirmation_callout,
				'action'               => $action,
			],
			true
		);
	}

	/**
	 * Templates for disclaimer block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form information.
	 */
	public function display_disclaimer( $form_data ) {

		if ( empty( $form_data['settings']['save_resume_enable'] ) ) {
			return;
		}

		if ( empty( $form_data['settings']['save_resume_disclaimer_enable'] ) ) {
			return;
		}

		$message = ! empty( $form_data['settings']['save_resume_disclaimer_message'] ) ? $form_data['settings']['save_resume_disclaimer_message'] : Settings::get_default_disclaimer_message();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			WPFORMS_SAVE_RESUME_PATH . 'templates/disclaimer',
			[
				'form_data' => $form_data,
				'message'   => $message,
			],
			true
		);
	}

	/**
	 * Append wrapper to main form container.
	 *
	 * @since 1.0.0
	 * @deprecated 1.8.0
	 *
	 * @param array $form_data Form information.
	 */
	public function display_save_resume_container_open( $form_data ) {

		_deprecated_function( __METHOD__, '1.8.0 of the WPForms Save and Resume addon' );

		if ( empty( $form_data['settings']['save_resume_enable'] ) ) {
			return $form_data;
		}

		printf( '<div class="wpforms-container-save-resume">' );
	}

	/**
	 * Append wrapper closing tag to form container.
	 *
	 * @since 1.0.0
	 * @deprecated 1.8.0
	 *
	 * @param array $form_data Form information.
	 */
	public function display_save_resume_container_close( $form_data ) {

		_deprecated_function( __METHOD__, '1.8.0 of the WPForms Save and Resume addon' );

		if ( empty( $form_data['settings']['save_resume_enable'] ) ) {
			return $form_data;
		}

		printf( '</div>' );
	}

	/**
	 * Process email form submitting.
	 *
	 * @since 1.0.0
	 */
	public function send_email() {

		// Security check.
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'wpforms_save_resume_process_entries' ) ) {
			return;
		}

		// Required data check.
		if ( ! isset( $_POST['submit'] ) && empty( $_POST['wpforms']['save_resume_email'] ) ) {
			return;
		}
		$entry_id = ! empty( $_POST['wpforms']['entry_id'] ) ? absint( $_POST['wpforms']['entry_id'] ) : 0;
		$form_id  = ! empty( $_POST['wpforms']['form_id'] ) ? absint( $_POST['wpforms']['form_id'] ) : 0;

		if ( $this->is_partial_entry_completed( $entry_id ) ) {
			wp_safe_redirect( add_query_arg( [ 'wpforms_sr_entry_is_completed' => $form_id ] ) );
			exit;
		}

		$address = sanitize_email( wp_unslash( $_POST['wpforms']['save_resume_email'] ) );

		if ( ! is_email( $address ) ) {
			return;
		}

		$token   = ! empty( $_POST['wpforms']['token'] ) ? sanitize_key( $_POST['wpforms']['token'] ) : '';

		// Token check before sending.
		$is_valid_token = wpforms()->obj( 'token' )->verify( $token );

		// If spam - return early.
		if ( ! $is_valid_token ) {

			// Logs spam entry depending on log levels set.
			wpforms_log(
				'Spam Entry (Partial) ' . uniqid(),
				'Email notification has not been delivered.',
				[
					'type'    => [ 'spam' ],
					'parent'  => $entry_id,
					'form_id' => $form_id,
				]
			);

			return;
		}

		$this->email( $form_id, $entry_id, $address );

		wp_safe_redirect( add_query_arg( [ 'wpforms_sr_email_is_sent' => $form_id ] ) );
		exit;
	}

	/**
	 * Send email with partial link.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $form_id  Form id.
	 * @param int    $entry_id Current entry id.
	 * @param string $address  Email address.
	 */
	private function email( $form_id, $entry_id, $address ) {

		$form_data = ! empty( $form_id ) ? wpforms()->obj( 'form' )->get( $form_id, [ 'content_only' => true ] ) : [];
		$message   = ! empty( $form_data['settings']['save_resume_email_notification_message'] ) ? $form_data['settings']['save_resume_email_notification_message'] : Settings::get_default_email_notification();
		$message   = wpforms_process_smart_tags( $message, $form_data, [], $entry_id );

		$email = [
			'address' => $address,
			'subject' => Settings::get_email_subject(),
			'message' => $message,
		];

		( new EmailNotification() )->send( $email );
	}

	/**
	 *
	 * Add .wpforms-save-resume-hide class if form should be hidden on the frontend.
	 *
	 * @since 1.2.0
	 * @deprecated 1.8.0
	 *
	 * @param array $classes   Array of form classes.
	 * @param array $form_data Form information.
	 *
	 * @return array
	 */
	public function hide_form( $classes, $form_data ) {

		_deprecated_function( __METHOD__, '1.8.0 of the WPForms Save and Resume addon' );

		return $classes;
	}

	/**
	 * Add S&R class to form container.
	 *
	 * @since 1.8.0
	 *
	 * @param array $classes   Array of form classes.
	 * @param array $form_data Form information.
	 *
	 * @return array
	 */
	public function add_addon_class( $classes, $form_data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$classes[] = 'wpforms-container-save-resume';

		return $classes;
	}

	/**
	 * Maybe break displaying form on wpforms_frontend_load action to show confirmation message.
	 *
	 * @since 1.0.0
	 *
	 * @param bool  $load_form Indicates whether a form should be loaded.
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	public function display_form( $load_form, $form_data ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['wpforms_sr_email_is_sent'] ) && (int) $_GET['wpforms_sr_email_is_sent'] === (int) $form_data['id'] ) {
			return false;
		}

		return $load_form;
	}

	/**
	 * Append additional HTML to form if needed.
	 *
	 * @since 1.11.0
	 *
	 * @param array $form_data Form data.
	 */
	public function display_confirmation_message( $form_data ) {

		if ( ! wpforms_save_resume()->is_enabled( $form_data ) ) {
			return;
		}

		// Detect if user is on the confirmation page.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['wpforms_sr_email_is_sent'] ) ) {
			return;
		}

		if ( $this->is_entry_completed( $form_data['id'] ) ) {
			$this->print_expired_message( $form_data );
		}

		/**
		 * Update confirmation container classes.
		 *
		 * @since 1.4.0
		 *
		 * @param array $classes   Array of classes.
		 * @param array $form_data Form data.
		 */
		$classes = (array) apply_filters( 'wpforms_save_resume_frontend_display_form_confirmation_classes', [ 'wpforms-save-resume-confirmation' ], $form_data );

		/**
		 * Update confirmation container ID.
		 *
		 * @since 1.4.0
		 *
		 * @param string $id        HTML element ID.
		 * @param array  $form_data Form data.
		 */
		$id      = (string) apply_filters( 'wpforms_save_resume_frontend_display_form_confirmation_id', '', $form_data );
		$message = ! empty( $form_data['settings']['save_resume_email_settings_message'] ) ? $form_data['settings']['save_resume_email_settings_message'] : Settings::get_default_email_sent_message();
		?>
		<div class="<?php echo wpforms_sanitize_classes( $classes, true ); ?>" id="<?php echo esc_attr( $id ); ?>">
			<?php

			/**
			 * Fires before successful email sent confirmation block.
			 *
			 * @since 1.4.0
			 *
			 * @param array $form_data Form data.
			 */
			do_action( 'wpforms_save_resume_frontend_confirmation_message_before', $form_data );

			if ( $message ) {
				echo wp_kses_post( wpautop( $message ) );
			}

			/**
			 * Fires after successful email sent confirmation block.
			 *
			 * @since 1.4.0
			 *
			 * @param array $form_data Form data.
			 */
			do_action( 'wpforms_save_resume_frontend_confirmation_message_after', $form_data );
			?>
		</div>
		<?php
	}

	/**
	 * Load text message if the resume link was expired.
	 *
	 * @since 1.0.0
	 * @deprecated 1.2.0
	 *
	 * @param bool  $load_form Indicates whether a form should be loaded.
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	public function display_expired_message( $load_form, $form_data ) {

		_deprecated_function( __METHOD__, '1.2.0 of the WPForms Save and Resume addon', __CLASS__ . '::display_entry_expired_message()' );

		// The new method signature requires `$form` as a second argument, but it's not used.
		// We always pass all arguments available for a specific hook for consistency.
		// We don't have a form here, but empty array workaround is sufficient.
		$this->display_entry_expired_message( $form_data, [] );

		return $load_form;
	}

	/**
	 * Load text message if the resume link was expired.
	 *
	 * @since 1.2.0
	 *
	 * @param array $form_data Form data.
	 * @param array $form      Current form.
	 *
	 * @return void
	 */
	public function display_entry_expired_message( $form_data, $form ) {

		if ( ! $this->is_resume_link_expired( $form_data ) ) {
			return;
		}

		$this->print_expired_message( $form_data );
	}

	/**
	 * Open hiding wrapper for the form.
	 *
	 * @since 1.4.0
	 *
	 * @param array  $form_data   Form data.
	 * @param null   $deprecated  Deprecated.
	 * @param string $title       Form title.
	 * @param string $description Form description.
	 * @param array  $errors      Form errors.
	 */
	public function hiding_wrapper_open( $form_data, $deprecated, $title, $description, $errors ) {

		if ( $this->is_resume_link_expired( $form_data ) || $this->is_entry_completed( $form_data['id'] ) ) {
			echo '<div class="wpforms-field-hidden wpforms-save-resume-form-hidden" >';
		}
	}

	/**
	 * Close hiding wrapper for the form.
	 *
	 * @since 1.4.0
	 *
	 * @param array  $form_data   Form data.
	 * @param null   $deprecated  Deprecated.
	 * @param string $title       Form title.
	 * @param string $description Form description.
	 * @param array  $errors      Form errors.
	 */
	public function hiding_wrapper_close( $form_data, $deprecated, $title, $description, $errors ) {

		if ( $this->is_resume_link_expired( $form_data ) || $this->is_entry_completed( $form_data['id'] ) ) {
			echo '</div>';
		}
	}

	/**
	 * Check if the resume link is expired.
	 *
	 * @since 1.4.0
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	private function is_resume_link_expired( $form_data ) {

		return wpforms_save_resume()->is_enabled( $form_data ) &&
				// phpcs:ignore WordPress.Security.NonceVerification
				isset( $_GET['wpforms_resume_entry'] ) &&
				! $this->has_partial_entry() &&
		       $this->get_completed_partial_entry_form_id() === (int) $form_data['id'];
	}

	/**
	 * Check if the entry was not completed already.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	private function has_partial_entry() {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification
		$hash     = ! empty( $_GET['wpforms_resume_entry'] ) ? $_GET['wpforms_resume_entry'] : '';
		$entry_id = Entry::get_entry_by_hash( $hash );

		if ( empty( $entry_id ) ) {
			return false;
		}

		$entry_data = wpforms()->obj( 'entry' )->get( $entry_id, [ 'cap' => false ] );

		return ! empty( $entry_data );
	}

	/**
	 * Get form id of completed partial entry.
	 *
	 * @since 1.5.0
	 *
	 * @return int
	 */
	private function get_completed_partial_entry_form_id() {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification
		$hash     = ! empty( $_GET['wpforms_resume_entry'] ) ? $_GET['wpforms_resume_entry'] : '';
		$entry_id = Entry::get_entry_by_hash( $hash );

		if ( empty( $entry_id ) ) {
			return false;
		}

		$entry_data = wpforms()->obj( 'entry_meta' )->get_meta(
			[
				'data'   => $entry_id,
				'type'   => 'partial_entry_meta_id',
				'number' => 1,
			]
		);

		if ( empty( $entry_data ) ) {
			return false;
		}

		return (int) $entry_data[0]->form_id;
	}

	/**
	 * Display email is sent confirmation message on CF page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_locked_filter() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return ! empty( $_GET['wpforms_sr_email_is_sent'] );
	}

	/**
	 * Delete partial entry which was successfully completed.
	 *
	 * @since 1.2.0
	 *
	 * @param array $fields    The fields that have been submitted.
	 * @param array $entry     The post data submitted by the form.
	 * @param array $form_data Form data.
	 * @param int   $entry_id  The entry ID.
	 */
	public function delete_entry( $fields, $entry, $form_data, $entry_id ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $form_data['settings']['save_resume_enable'] ) ) {
			return;
		}

		$partial_entry_id = Entry::get_existing_partial_entry_id( $form_data['id'] );

		if ( $partial_entry_id === 0 ) {
			return;
		}

		// We need to add $partial_entry_id meta to the new Entry for future checks
		// of multiple form submissions for one unique resume link from multiple opened tabs.
		Entry::set_partial_id_meta( $entry_id, $form_data['id'], $partial_entry_id );

		wpforms()->obj( 'entry' )->delete( $partial_entry_id, [ 'cap' => false ] );
	}

	/**
	 * Check for multiple form submissions for one unique resume link from multiple opened tabs.
	 *
	 * @since 1.3.0
	 *
	 * @param array $errors    Form submit errors.
	 * @param array $form_data Form information.
	 *
	 * @return array
	 */
	public function multiple_submission_check( $errors, $form_data ) {

		if ( ! wpforms_save_resume()->is_enabled( $form_data ) ) {
			return $errors;
		}

		$hash               = Entry::get_resume_hash();
		$entry_meta_handler = wpforms()->obj( 'entry_meta' );

		if ( ! $hash || ! $entry_meta_handler ) {
			return $errors;
		}

		$partial_entry_id = Entry::get_entry_by_hash( $hash );
		$form_id          = ! empty( $form_data['id'] ) ? $form_data['id'] : 0;
		$page_url         = wp_get_raw_referer();

		if ( ! $partial_entry_id || ! $form_id || ! $page_url ) {
			return $errors;
		}

		$meta = $entry_meta_handler->get_meta(
			[
				'data'    => $partial_entry_id,
				'form_id' => $form_id,
				'type'    => Entry::PARTIAL_ENTRY_META_ID_TYPE,
				'number'  => 1,
			]
		);

		if ( empty( $meta ) ) {
			return $errors;
		}

		$this->is_error = true;

		$clear_url                         = remove_query_arg( [ 'wpforms_resume_entry', 'wpforms_sr_entry_is_completed' ], $page_url );
		$errors[ $form_id ]['save_resume'] = 'twice';
		$errors[ $form_id ]['header']      = sprintf(
			wp_kses( /* translators: %s - page URL without Save and Resume GET variables. */
				__( 'Unfortunately, the link you used to resume the form submission was already used. <a href="%s">Click here</a> to fill in the form again.', 'wpforms-save-resume' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			$clear_url
		);

		return $errors;
	}

	/**
	 * Pass additional settings and strings to frontend.
	 *
	 * @since 1.4.0
	 * @deprecated 1.8.0
	 *
	 * @param array $strings Frontend strings.
	 *
	 * @return array
	 */
	public function add_frontend_strings( $strings ) {

		_deprecated_function( __METHOD__, '1.8.0 of the WPForms Save and Resume addon', '\WPFormsSaveResume\Enqueues::add_frontend_strings()' );

		return ( new Enqueues() )->add_frontend_strings( $strings );
	}

	/**
	 * Skip time limit for resume link.
	 *
	 * @since 1.6.0
	 *
	 * @param bool $bypass Time limit bypass.
	 *
	 * @return bool
	 */
	public function skip_time_limit( $bypass ) {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification
		$page_url  = ! empty( $_POST['page_url'] ) ? $_POST['page_url'] : wpforms_current_url();
		$url_query = wp_parse_url( $page_url, PHP_URL_QUERY );

		// Return if no query string.
		if ( empty( $url_query ) ) {
			return $bypass;
		}

		parse_str( $url_query, $query );

		$hash     = ! empty( $query['wpforms_resume_entry'] ) ? $query['wpforms_resume_entry'] : '';
		$entry_id = Entry::get_entry_by_hash( $hash );

		if ( empty( $entry_id ) ) {
			return $bypass;
		}

		return true;
	}

	/**
	 * Add error flag to AJAX response.
	 *
	 * @since 1.6.0
	 *
	 * @param array $response AJAX response.
	 *
	 * @return array
	 */
	public function add_ajax_submit_errors_response( $response ) {

		if ( $this->is_error ) {
			$response['save_resume_error'] = true;
		}

		return $response;
	}

	/**
	 * Disable current location for the form resumed from the link.
	 *
	 * @since 1.11.1
	 *
	 * @param bool $is_current_location Current location status.
	 *
	 * @return bool
	 */
	public function disable_current_location( $is_current_location ) {

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( ! empty( $_GET['wpforms_resume_entry'] ) ) {
			return false;
		}

		return $is_current_location;
	}

	/**
	 * Check if partial entry was completed.
	 *
	 * @since 1.8.0
	 *
	 * @param int $entry_id Entry ID.
	 *
	 * @return bool
	 */
	private function is_partial_entry_completed( $entry_id ) {

		if ( empty( $entry_id ) ) {
			return false;
		}

		$entry_detail = wpforms()->obj( 'entry' )->get( $entry_id, [ 'cap' => false ] );

		return empty( $entry_detail ) || $entry_detail->status !== 'partial';
	}

	/**
	 * Output expired message block.
	 *
	 * @since 1.8.0
	 *
	 * @param array $form_data Form data.
	 */
	private function print_expired_message( $form_data ) {

		/**
		 * Change expired messages text.
		 *
		 * @since 1.0.0
		 *
		 * @param string $message   Message.
		 * @param array  $form_data Form data.
		 */
		$message = apply_filters( 'wpforms_save_resume_frontend_expired_message', Settings::get_expired_message(), $form_data );

		printf(
			'<div class="wpforms-error-container wpforms-save-resume-expired-message %1$s" id="%2$s" role="alert">%3$s</div>',
			wpforms_setting( 'disable-css', '1' ) === '1' ? 'wpforms-save-resume-expired-message-full' : '',
			'wpforms-save-resume-expired-message-' . absint( $form_data['id'] ),
			wp_kses_post( wpautop( $message ) )
		);
	}

	/**
	 * Check if entry is completed.
	 *
	 * @since 1.8.0
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return bool
	 */
	private function is_entry_completed( $form_id ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return ! empty( $_GET['wpforms_sr_entry_is_completed'] ) && (int) $_GET['wpforms_sr_entry_is_completed'] === (int) $form_id;
	}
}
