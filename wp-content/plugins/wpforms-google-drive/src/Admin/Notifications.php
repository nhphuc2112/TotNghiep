<?php

namespace WPFormsGoogleDrive\Admin;

use WPForms_WP_Emails;
use WPFormsGoogleDrive\Field;
use WPForms\SmartTags\SmartTag\SmartTag;
use WPForms\Emails\Notifications as EmailNotifications;

/**
 * Class Entry.
 *
 * @since 1.0.0
 */
class Notifications {

	/**
	 * Init object.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function hooks(): void {

		add_filter( 'wpforms_emails_notifications_field_message_html', [ $this, 'resend_notifications_field_message_html' ], 10, 7 );
		add_filter( 'wpforms_emails_notifications_field_message_plain', [ $this, 'resend_notifications_field_message_plain' ], 10, 6 );

        add_filter( 'wpforms_wp_emails_html_field_value_message_html', [ $this, 'get_resend_notification_legacy' ], 10, 3 );

		add_filter( 'wpforms_smarttags_process_value', [ $this, 'update_smart_tags_field_value' ], 10, 6 );
	}

	/**
	 * Append Google Drive links for resend HTML notifications if local files were deleted.
	 *
	 * @since 1.0.0
	 *
	 * @param string|mixed       $message           Field message.
	 * @param array              $field             Field data.
	 * @param bool               $show_empty_fields Whether to display empty fields in the email.
	 * @param array              $other_fields      List of field types.
	 * @param array              $form_data         Form data.
	 * @param array              $fields            List of submitted fields.
	 * @param EmailNotifications $notifications     Notifications instance.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function resend_notifications_field_message_html( $message, array $field, bool $show_empty_fields, array $other_fields, array $form_data, array $fields, EmailNotifications $notifications ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$message = (string) $message;

		// The field has a local file and markup for it.
		if ( ! wpforms_is_empty_string( $message ) ) {
			return $message;
		}

		if ( empty( $field['id'] ) || empty( $fields[ $field['id'] ] ) ) {
			return $message;
		}

		if ( ! Field::is_file_upload_field( $field ) ) {
			return $message;
		}

		$field_obj = new Field( $fields[ $field['id'] ], wpforms()->obj( 'process' )->entry_id );

		if ( ! $field_obj->has_uploaded_files() ) {
			return $message;
		}

		return str_replace(
			[
				'{field_type}',
				'{field_name}',
				'{field_value}',
			],
			[
				'file-upload',
				$field_obj->get_label(),
				$this->get_field_value_html( $field_obj ),
			],
			$notifications->get_current_field_template()
		);
	}

	/**
	 * Append Google Drive links for resend plain text notifications if local files were deleted.
	 *
	 * @since 1.0.0
	 *
	 * @param string|mixed       $message           Field message.
	 * @param array              $field             Field data.
	 * @param bool               $show_empty_fields Whether to display empty fields in the email.
	 * @param array              $form_data         Form data.
	 * @param array              $fields            List of submitted fields.
	 * @param EmailNotifications $notifications     Notifications instance.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function resend_notifications_field_message_plain( $message, array $field, bool $show_empty_fields, array $form_data, array $fields, EmailNotifications $notifications ): string {

		$message = (string) $message;

		// The field has a local file and markup for it.
		if ( ! wpforms_is_empty_string( $message ) ) {
			return $message;
		}

		if ( empty( $field['id'] ) || empty( $notifications->fields[ $field['id'] ] ) ) {
			return $message;
		}

		if ( ! Field::is_file_upload_field( $field ) ) {
			return $message;
		}

		$field_obj = new Field( $fields[ $field['id'] ], wpforms()->obj( 'process' )->entry_id );

		if ( ! $field_obj->has_uploaded_files() ) {
			return $message;
		}

		$notifications->fields[ $field['id'] ]['value'] = $this->get_field_value_plain( $field_obj );

		return $notifications->get_field_plain( $field, $show_empty_fields );
	}

	/**
	 * Get field value in HTML format.
	 *
	 * @since 1.0.0
	 *
	 * @param Field $field Field object.
	 *
	 * @return string
	 */
	private function get_field_value_html( Field $field ): string {

		$files         = $field->get_files();
		$files_markups = [];

		foreach ( $files as $file_id => $file ) {
			$files_markups[] = $file->get_uploaded_file_html( $file_id, wpforms()->obj( 'process' )->entry_id );
		}

		return implode( '<br/>', $files_markups );
	}

	/**
	 * Get field value in plain text format.
	 *
	 * @since 1.0.0
	 *
	 * @param Field $field Field object.
	 *
	 * @return string
	 */
	private function get_field_value_plain( Field $field ): string {

		$files         = $field->get_files();
		$files_markups = [];

		foreach ( $files as $file_id => $file ) {
			$files_markups[] = $file->get_uploaded_file_plain( $file_id, wpforms()->obj( 'process' )->entry_id );
		}

		return implode( "\r\n", $files_markups );
	}

	/**
	 * Append Google Drive links for resend HTML notifications if local files were deleted for legacy email templates.
	 *
	 * @since 1.0.0
	 *
	 * @param string|mixed $message   Field message.
	 * @param array        $field     Field data.
	 * @param array        $form_data Form data and settings.
	 *
	 * @return string
	 */
	public function get_resend_notification_legacy( $message, array $field, array $form_data ): string { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$message = (string) $message;

		// The field has a local file and markup for it.
		if ( ! wpforms_is_empty_string( $message ) ) {
			return $message;
		}

		$process = wpforms()->obj( 'process' );

		if ( ! $process ) {
			return $message;
		}

		if ( empty( $field['id'] ) || empty( $process->fields[ $field['id'] ] ) ) {
			return $message;
		}

		if ( ! Field::is_file_upload_field( $field ) ) {
			return $message;
		}

		$field_obj = new Field( $process->fields[ $field['id'] ], $process->entry_id );

		if ( ! $field_obj->has_uploaded_files() ) {
			return $message;
		}

		ob_start();

		$email_object = new WPForms_WP_Emails();

		$email_object->get_template_part( 'field', $email_object->get_template() );

		$field_template = ob_get_clean();

		// Remove the top border for the first field.
		if ( $this->is_first_field_in_legacy_template( $field, $form_data ) ) {
			$field_template = str_replace( 'border-top:1px solid #dddddd;', '', $field_template );
		}

		/** This filter is documented in wpforms/includes/emails/class-emails.php */
		$field_name = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_html_field_name',
			$field['label'],
			$field,
			$form_data,
			'email-html'
		);

		/** This filter is documented in wpforms/includes/emails/class-emails.php */
		$field_value = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_html_field_value',
			$this->get_field_value_html( $field_obj ),
			$field,
			$form_data,
			'email-html'
		);

		if ( empty( $field_value ) ) {
			return $message;
		}

		return str_replace( [ '{field_name}', '{field_value}' ], [ $field_name, $field_value ], $field_template );
	}

	/**
	 * Determine if the field is first in the form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Field data.
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	private function is_first_field_in_legacy_template( array $field, array $form_data ): bool {

		if ( empty( $form_data['fields'] ) ) {
			return false;
		}

		$first_field    = reset( $form_data['fields'] );
		$first_field_id = $first_field['id'] ?? 0;

		// Remove the top border for the first field.
		return (int) $first_field_id === (int) $field['id'];
	}

	/**
	 * Update file upload values inside smart tag.
	 *
	 * @since 1.1.0
	 *
	 * @param scalar|null $value            Smart Tag value.
	 * @param string      $tag_name         Smart tag name.
	 * @param array       $form_data        Form data.
	 * @param array       $fields           List of fields.
	 * @param string      $entry_id         Entry ID.
	 * @param SmartTag    $smart_tag_object The smart tag object or the Generic object for those cases when class unregistered.
	 *
	 * @return scalar|null
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function update_smart_tags_field_value( $value, string $tag_name, array $form_data, array $fields, string $entry_id, SmartTag $smart_tag_object ) {

		if ( ! in_array( $tag_name, [ 'field_id', 'field_value_id', 'field_html_id' ], true ) ) {
			return $value;
		}

		if ( empty( $smart_tag_object->get_attributes()[ $tag_name ] ) ) {
			return $value;
		}

		$field_id = $smart_tag_object->get_attributes()[ $tag_name ];

		if ( ! isset( $fields[ $field_id ] ) ) {
			return $value;
		}

		if ( ! Field::is_file_upload_field( $fields[ $field_id ] ) ) {
			return $value;
		}

		return $this->get_smart_tag_value( $value, $tag_name, $fields[ $field_id ], (int) $entry_id );
	}

	/**
	 * Get smart tag value.
	 *
	 * @since 1.0.0
	 *
	 * @param scalar|null $value    Smart tag value.
	 * @param string      $tag_name Tag name.
	 * @param array       $field    Field data.
	 * @param int         $entry_id Entry ID.
	 *
	 * @return scalar|null
	 */
	private function get_smart_tag_value( $value, string $tag_name, array $field, int $entry_id ) {

		$field_obj = new Field( $field, $entry_id );

		if ( ! $field_obj->has_uploaded_files() || $field_obj->has_local_files() ) {
			return $value;
		}

		return $tag_name === 'field_html_id'
			? $this->get_field_value_html( $field_obj )
			: $this->get_field_value_plain( $field_obj );
	}
}
