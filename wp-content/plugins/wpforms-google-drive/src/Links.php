<?php

namespace WPFormsGoogleDrive;

use WPForms\Helpers\Crypto;
use WPForms\Pro\Forms\Fields\FileUpload\Field as FileUploadField;

/**
 * Links class.
 *
 * @since 1.0.0
 */
class Links {

	/**
	 * Init object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hooks(): void {

		add_action( 'init', [ $this, 'redirect_download_file' ] );
		add_action( 'wpforms_pro_access_file_download_template_before_download', [ $this, 'redirect_protected_file' ] );
	}

	/**
	 * Redirect to local or Google Drive File.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function redirect_download_file(): void {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['wpforms_google_drive'] ) || $_GET['wpforms_google_drive'] !== 'download' ) {
			return;
		}

		if ( empty( $_GET['file'] ) ) {
			$this->remove_download_args();

			return;
		}

		// Since the data encoded we can't sanitize_text_field it.
		// Also, we should double-decode the link.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.urlencode_urlencode, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$file_data = (array) json_decode( Crypto::decrypt( rawurldecode( urlencode( wp_unslash( $_GET['file'] ) ) ) ), true );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( ! isset( $file_data['field_id'], $file_data['file_id'], $file_data['entry_id'] ) ) {
			return;
		}

		$field_id = absint( $file_data['field_id'] );
		$file_id  = absint( $file_data['file_id'] );
		$entry_id = absint( $file_data['entry_id'] );

		$url = $this->get_redirect_file_url( $entry_id, $field_id, $file_id );

		if ( ! $url ) {
			$this->remove_download_args();

			return;
		}

		// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		wp_redirect( $url );
		exit;
	}

	/**
	 * Get redirect file URL.
	 *
	 * @since 1.0.0
	 *
	 * @param int $entry_id Entry ID.
	 * @param int $field_id Field ID.
	 * @param int $file_id  File ID.
	 *
	 * @return string
	 */
	private function get_redirect_file_url( int $entry_id, int $field_id, int $file_id ): string {

		$fields = $this->get_entry_fields( $entry_id );

		if ( empty( $fields[ $field_id ] ) || ! Field::is_file_upload_field( $fields[ $field_id ] ) ) {
			$this->remove_download_args();

			return '';
		}

		$field_obj = new Field( $fields[ $field_id ], $entry_id );

		$files = $field_obj->get_files();

		if ( empty( $files[ $file_id ] ) ) {
			return $this->get_local_file_url_from_field( $fields[ $field_id ], $file_id );
		}

		$file = $files[ $file_id ];

		return $file->has_local() ? $file->get_local_file_url() : $file->get_pure_google_drive_file_url();
	}

	/**
	 * When uploading to Google Drive is failed,
	 * we should fallow back a customer to a local file.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field   Field data.
	 * @param int   $file_id File ID.
	 *
	 * @return string
	 */
	private function get_local_file_url_from_field( array $field, int $file_id ): string {

		if ( ! FileUploadField::is_modern_upload( $field ) ) {
			return ! empty( $field['value'] ) ? $field['value'] : '';
		}

		return ! empty( $field['value_raw'][ $file_id ]['value'] ) ? $field['value_raw'][ $file_id ]['value'] : '';
	}

	/**
	 * Remove download URL arguments if the data is broken.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function remove_download_args(): void {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$_SERVER['REQUEST_URI'] = remove_query_arg( [ 'wpforms_google_drive', 'file' ], $_SERVER['REQUEST_URI'] );
	}

	/**
	 * Get entry fields.
	 *
	 * @since 1.0.0
	 *
	 * @param int $entry_id Entry ID.
	 *
	 * @return array
	 */
	private function get_entry_fields( int $entry_id ): array {

		$entry_handler = wpforms()->obj( 'entry' );

		if ( ! $entry_handler ) {
			return [];
		}

		$entry = $entry_handler->get( $entry_id );

		if ( ! $entry || empty( $entry->fields ) ) {
			return [];
		}

		return (array) json_decode( $entry->fields, true );
	}

	/**
	 * Redirect to the protected file.
	 *
	 * @since 1.0.0
	 */
	public function redirect_protected_file(): void {

		// Restrictions are checked inside WPForms\Pro\Access\File class.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$google_drive_url = isset( $_GET['wpforms_google_drive_url'] ) ? sanitize_text_field( wp_unslash( $_GET['wpforms_google_drive_url'] ) ) : '';

		if ( empty( $google_drive_url ) ) {
			return;
		}

		$google_drive_url = rawurldecode( $google_drive_url );
		$decrypt_url      = Crypto::decrypt( $google_drive_url );

		if ( empty( $decrypt_url ) ) {
			return;
		}

		// Redirect to file middleware.
		// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		wp_redirect( $decrypt_url );
		exit;
	}
}
