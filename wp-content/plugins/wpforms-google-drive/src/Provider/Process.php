<?php

namespace WPFormsGoogleDrive\Provider;

use WPForms\Tasks\Meta;
use WPForms_Entries_Single;
use WPFormsGoogleDrive\File;
use WPFormsGoogleDrive\Plugin;
use WPFormsGoogleDrive\Api\Client;
use WPFormsGoogleDrive\Admin\Entry;
use WPForms\Providers\Provider\Process as ProcessBase;
use WPFormsGoogleDrive\Provider\Settings\FormBuilder;
use WPForms\Pro\Forms\Fields\FileUpload\Field as FileUploadField;

/**
 * Google Drive processing.
 *
 * @since 1.0.0
 */
class Process extends ProcessBase {

	/**
	 * Async task action.
	 *
	 * @since 1.0.0
	 */
	private const UPLOAD_ACTION = 'wpforms_google_drive_process_upload';

	/**
	 * Async task action.
	 *
	 * @since 1.0.0
	 */
	private const DELETE_ACTION = 'wpforms_google_drive_process_delete';

	/**
	 * Client.
	 *
	 * @since 1.0.0
	 *
	 * @var Client
	 */
	private $client;

	/**
	 * Connection data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $connection;

	/**
	 * Add hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks(): void {

		add_action( 'wpforms_entry_details_init', [ $this, 'reupload_files' ] );
		add_action( 'wpforms_process_entry_saved', [ $this, 'force_download_url' ], 11, 5 );
		add_filter( 'wpforms_pro_fields_file_upload_get_file_url', [ $this, 'force_protected_download_url' ], 10, 2 );
		add_action( self::UPLOAD_ACTION, [ $this, 'process_upload_action' ], 10, 4 );
		add_action( self::DELETE_ACTION, [ $this, 'process_delete_action' ], 10, 4 );
	}

	/**
	 * Reupload files.
	 *
	 * @since 1.1.0
	 *
	 * @param WPForms_Entries_Single $entry_single Current instance of the WPForms_Entries_Single class.
	 *
	 * @return void
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function reupload_files( $entry_single ): void {

		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wpforms_google_drive_reupload' ) ) {
			return;
		}

		$this->fields    = json_decode( $entry_single->entry->fields, true );
		$this->form_data = wpforms_decode( $entry_single->form->post_content );
		$this->entry_id  = (int) $entry_single->entry->entry_id;

		if ( empty( $this->form_data['providers'][ Plugin::SLUG ] ) ) {
			$this->remove_nonce();

			return;
		}

		$account = wpforms_google_drive()->get( 'account' );

		if ( ! $account ) {
			return;
		}

		$saved_attachments = Entry::get_saved_attachments( $entry_single->entry->entry_id );
		$saved_attachments = $saved_attachments && property_exists( $saved_attachments, 'data' ) ? $saved_attachments->data : [];

		foreach ( $this->form_data['providers'][ Plugin::SLUG ] as $connection_data ) {
			$this->connection = $connection_data;

			$this->reupload_files_each_connection( $account, $saved_attachments );
		}

		if ( FormBuilder::is_enabled_delete_local_files( $this->form_data ) ) {
			Entry::delete_local_files( (int) $this->entry_id );
		}

		Entry::save_last_uploaded_files( $this->entry_id, (int) $this->form_data['id'], $saved_attachments );

		$this->remove_nonce();
	}

	/**
	 * Reupload files for each connection.
	 *
	 * @since 1.1.0
	 *
	 * @param Account $account           Account class.
	 * @param array   $saved_attachments Saved attachments.
	 *
	 * @return void
	 */
	private function reupload_files_each_connection( Account $account, array $saved_attachments ): void {

		if ( ! $this->connection_passed() ) {
			return;
		}

		$this->client = $account->get_client_by_id( $this->connection['account_id'] );

		if ( ! $this->client ) {
			return;
		}

		if ( empty( $this->connection['folder_id'] ) ) {
			return;
		}

		$this->ignore_uploaded_files( $saved_attachments );

		$this->upload_files();
	}

	/**
	 * Remove nonce.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	private function remove_nonce(): void {

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			// phpcs:disabled WordPress.Security.ValidatedSanitizedInput
			$_SERVER['REQUEST_URI'] = remove_query_arg( '_wpnonce', wp_unslash( $_SERVER['REQUEST_URI'] ) );

			wp_safe_redirect( $_SERVER['REQUEST_URI'] );
			// phpcs:enabled WordPress.Security.ValidatedSanitizedInput
		}
	}

	/**
	 * Remove uploaded files to avoid duplicates on Google Drive.
	 *
	 * @since 1.1.0
	 *
	 * @param array $saved_attachments Saved attachments.
	 *
	 * @return void
	 */
	private function ignore_uploaded_files( array $saved_attachments ): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$folder_id = $this->connection['folder_id'];

		if ( ! $folder_id || empty( $saved_attachments ) ) {
			return;
		}

		$required_keys = [
			'folder_id',
			'google_drive_id',
			'field_id',
			'file_id',
		];

		foreach ( $saved_attachments as $attachment ) {
			// Skip the attachment if it does not have all required keys.
			foreach ( $required_keys as $key ) {
				if ( ! isset( $attachment[ $key ] ) ) {
					continue 2;
				}
			}

			if ( $attachment['folder_id'] !== $folder_id ) {
				continue;
			}

			$is_exist = $this->client->file_exists( $attachment['google_drive_id'] );

			if ( ! $is_exist ) {
				continue;
			}

			$field = $this->fields[ $attachment['field_id'] ];

			if ( ! empty( $field['file'] ) ) {
				unset( $this->fields[ $attachment['field_id'] ] );

				continue;
			}

			if ( ! empty( $field['value_raw'][ $attachment['file_id'] ] ) ) {
				unset( $this->fields[ $attachment['field_id'] ]['value_raw'][ $attachment['file_id'] ] );
			}
		}
	}

	/**
	 * Force generation of download URLs for form fields where files should be deleted.
	 *
	 * @since        1.0.0
	 *
	 * @param array $fields     The form fields containing uploaded file data.
	 * @param array $entry      The specific form entry being processed.
	 * @param array $form_data  The form configuration data.
	 * @param int   $entry_id   The unique ID of the form entry.
	 * @param int   $payment_id The payment ID associated with the entry, if applicable.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function force_download_url( $fields, $entry, $form_data, $entry_id, $payment_id ): void {

		if ( empty( $fields ) || empty( $entry_id ) ) {
			return;
		}

		if ( ! FormBuilder::is_enabled_delete_local_files( $form_data ) ) {
			return;
		}

		$this->fields    = $fields;
		$this->form_data = $form_data;
		$this->entry_id  = $entry_id;

		$uploaded_field_ids = $this->get_fields_with_deleted_files();

		foreach ( $uploaded_field_ids as $field_id ) {
			$this->force_field_download_url( $field_id );
		}
	}

	/**
	 * Update protected URL replacing it with protected download URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file_url Protected file URL.
	 * @param array  $field    Field/File data.
	 *
	 * @return string
	 */
	public function force_protected_download_url( string $file_url, array $field ): string { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( str_contains( $file_url, 'wpforms_google_drive_url' ) ) {
			return $file_url;
		}

		// The data is set in the `force_download_url` method.
		if ( empty( $this->fields ) || empty( $this->form_data ) || empty( $this->entry_id ) ) {
			return $file_url;
		}

		if ( ! FormBuilder::is_enabled_delete_local_files( $this->form_data ) ) {
			return $file_url;
		}

		remove_filter( 'wpforms_pro_fields_file_upload_get_file_url', [ $this, 'force_protected_download_url' ] );

		$download_url = $this->get_download_url( $field );

		add_filter( 'wpforms_pro_fields_file_upload_get_file_url', [ $this, 'force_protected_download_url' ], 10, 2 );

		return wpforms_is_empty_string( $download_url ) ? $file_url : $download_url;
	}

	/**
	 * Get download URL.
	 *
	 * @since 1.1.0
	 *
	 * @param array $field Field/File data.
	 *
	 * @return string
	 */
	private function get_download_url( array $field ): string {

		if ( empty( $field['id'] ) ) {
			return '';
		}

		$field_id = $field['id'];

		if ( empty( $this->fields[ $field_id ] ) ) {
			return '';
		}

		$uploaded_field_ids = $this->get_fields_with_deleted_files();

		if ( ! in_array( $field_id, $uploaded_field_ids, true ) ) {
			return '';
		}

		$file_id = $this->get_field_id( $this->fields[ $field_id ], $field );

		return File::generate_download_url( $field, $file_id, $this->entry_id );
	}

	/**
	 * Get file ID to a generate download link with a Google Drive file URL.
	 *
	 * @since 1.1.0
	 *
	 * @param array $submitted_field Submitted field.
	 * @param array $file            Field/file data.
	 *
	 * @return int
	 */
	private function get_field_id( array $submitted_field, array $file ): int {

		if ( ! FileUploadField::is_modern_upload( $submitted_field ) || empty( $submitted_field['value_raw'] ) ) {
			return 0;
		}

		// Compare by `protection_hash` for protected files and by file URL for unprotected files.
		$compare_key = ! empty( $file['protection_hash'] ) ? 'protection_hash' : 'file';

		if ( empty( $file[ $compare_key ] ) ) {
			return 0;
		}

		foreach ( $submitted_field['value_raw'] as $key => $submitted_file ) {
			if ( isset( $submitted_file[ $compare_key ] ) && $submitted_file[ $compare_key ] === $file[ $compare_key ] ) {
				return (int) $key;
			}
		}

		return 0;
	}

	/**
	 * Prepare value with download URL for a file upload field.
	 *
	 * @since 1.0.0
	 *
	 * @param int $field_id Field ID.
	 *
	 * @return void
	 */
	private function force_field_download_url( int $field_id ): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $this->fields[ $field_id ]['value'] ) ) {
			return;
		}

		if ( empty( wpforms()->obj( 'process' )->fields[ $field_id ] ) ) {
			return;
		}

		$field = $this->fields[ $field_id ];

		if ( ! FileUploadField::is_modern_upload( $field ) ) {
			wpforms()->obj( 'process' )->fields[ $field_id ]['value'] = File::generate_download_url( $field, 0, $this->entry_id );
		}

		if ( empty( $field['value_raw'] ) ) {
			return;
		}

		$value = [];
		$files = count( $field['value_raw'] );

		for ( $i = 0; $i < $files; $i++ ) {
			if ( empty( $field['value_raw'][ $i ] ) ) {
				continue;
			}

			$url     = File::generate_download_url( $field['value_raw'][ $i ], $i, $this->entry_id );
			$value[] = $url;

			wpforms()->obj( 'process' )->fields[ $field_id ]['value_raw'][ $i ]['value'] = $url;
		}

		wpforms()->obj( 'process' )->fields[ $field_id ]['value'] = implode( "\n", $value );
	}

	/**
	 * Get a list of form fields where files should be deleted.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_fields_with_deleted_files(): array {

		static $fields_with_deleted_files = [];

		if ( ! empty( $fields_with_deleted_files ) ) {
			return $fields_with_deleted_files;
		}

		$connections = $this->get_valid_connections();

		if ( ! $connections ) {
			return [];
		}

		$field_ids = [];

		foreach ( $connections as $connection_data ) {
			$files = ( new FieldsMapper( $this->fields, $this->form_data, $connection_data ) )->get_files();

			if ( ! $files ) {
				continue;
			}

			$field_ids[] = wp_list_pluck( $files, 'field_id' );
		}

		$fields_with_deleted_files = array_unique( array_merge( [], ...$field_ids ) );

		return $fields_with_deleted_files;
	}

	/**
	 * Receive only valid connections with the required configuration.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_valid_connections(): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $this->form_data['providers'][ Plugin::SLUG ] ) ) {
			return [];
		}

		// Form must have at least one file upload field.
		if ( ! wpforms_has_field_type( 'file-upload', $this->form_data ) ) {
			return [];
		}

		$valid_connections = [];

		foreach ( $this->form_data['providers'][ Plugin::SLUG ] as $key => $connection_data ) {

			if ( $key === '__lock__' ) {
				continue;
			}

			$this->connection = $connection_data;

			if ( ! $this->connection_passed() ) {
				continue;
			}

			if ( ! $this->condition_passed() ) {
				continue;
			}

			$valid_connections[] = $connection_data;
		}

		return $valid_connections;
	}

	/**
	 * Receive all wpforms_process_complete params and do the actual processing.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields    Array of form fields.
	 * @param array $entry     Submitted form content.
	 * @param array $form_data Form data and settings.
	 * @param int   $entry_id  ID of a saved entry.
	 */
	public function process( $fields, $entry, $form_data, $entry_id ): void {

		// Form must have at least one file upload field.
		if ( ! wpforms_has_field_type( 'file-upload', $form_data ) ) {
			return;
		}

		$tasks_obj = wpforms()->obj( 'tasks' );

		if ( ! $tasks_obj ) {
			return;
		}

		$this->fields    = $fields;
		$this->form_data = $form_data;
		$this->entry_id  = $entry_id;

		$connections = $this->get_valid_connections();

		if ( ! $connections ) {
			return;
		}

		foreach ( $connections as $connection_data ) {
			$this->connection = $connection_data;

			$this->process_each_connection();
		}

		if ( ! FormBuilder::is_enabled_delete_local_files( $this->form_data ) ) {
			return;
		}

		$tasks_obj
			->create( self::DELETE_ACTION )
			->async()
			->params( $this->fields, $this->form_data, $this->entry_id )
			->register();
	}

	/**
	 * Iteration loop for connections - add action for each connection.
	 *
	 * @since 1.0.0
	 */
	protected function process_each_connection(): void {

		$tasks_obj = wpforms()->obj( 'tasks' );

		if ( ! $tasks_obj ) {
			return;
		}

		$tasks_obj
			->create( self::UPLOAD_ACTION )
			->async()
			->params( $this->connection, $this->fields, $this->form_data, $this->entry_id )
			->register();
	}

	/**
	 * Process the addon async tasks.
	 *
	 * @since 1.0.0
	 *
	 * @param int|mixed $meta_id Task meta ID.
	 */
	public function process_upload_action( $meta_id ): void {

		$task_meta = new Meta();
		$meta      = $task_meta->get( $meta_id );

		// We should actually receive something.
		if ( empty( $meta ) || empty( $meta->data ) ) {
			return;
		}

		if ( count( $meta->data ) !== 4 ) {
			return;
		}

		// We expect a certain metadata structure for this task.
		[ $this->connection, $this->fields, $this->form_data, $this->entry_id ] = $meta->data;

		if ( ! $this->connection_passed() ) {
			return;
		}

		$account = wpforms_google_drive()->get( 'account' );

		if ( ! $account ) {
			return;
		}

		$this->client = $account->get_client_by_id( $this->connection['account_id'] );

		if ( ! $this->client ) {
			return;
		}

		$this->upload_files();
	}

	/**
	 * Process the addon async tasks.
	 *
	 * @since 1.0.0
	 *
	 * @param int|mixed $meta_id Task meta ID.
	 */
	public function process_delete_action( $meta_id ): void {

		$task_meta = new Meta();
		$meta      = $task_meta->get( $meta_id );

		// We should actually receive something.
		if ( empty( $meta ) || empty( $meta->data ) ) {
			return;
		}

		if ( count( $meta->data ) !== 3 ) {
			return;
		}

		// We expect a certain metadata structure for this task.
		[ $this->fields, $this->form_data, $this->entry_id ] = $meta->data;

		if ( empty( $this->form_data['id'] ) ) {
			return;
		}

		if ( ! FormBuilder::is_enabled_delete_local_files( $this->form_data ) ) {
			return;
		}

		Entry::delete_local_files( (int) $this->entry_id );
	}

	/**
	 * Upload files to Google Drive.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function upload_files(): void {

		$files = ( new FieldsMapper( $this->fields, $this->form_data, $this->connection ) )->get_files();

		foreach ( $files as $key => $file ) {
			$file_id = $this->client->upload_file( $file );

			if ( ! $file_id ) {
				unset( $files[ $key ] );
				continue;
			}

			$files[ $key ]['google_drive_id'] = $file_id;
		}

		if ( ! empty( $files ) ) {
			Entry::save_google_drive_files( $files, $this->entry_id, $this->form_data['id'] );
		}
	}

	/**
	 * Process Conditional Logic for the provided connection.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function condition_passed(): bool {

		$pass = $this->process_conditionals( $this->fields, $this->form_data, $this->connection );
		$hash = hash( 'adler32', wp_json_encode( $this->connection ) );

		static $logged = [];

		// Check for conditional logic.
		if ( ! $pass && empty( $logged[ $hash ] ) ) {
			wpforms_log(
				sprintf( 'The Google Drive connection %s was not processed due to conditional logic.', $this->connection['name'] ?? '' ),
				[
					'connection' => $this->connection,
					'fields'     => $this->fields,
				],
				[
					'type'    => [ 'provider', 'conditional_logic' ],
					'parent'  => $this->entry_id,
					'form_id' => $this->form_data['id'],
				]
			);

			$logged[ $hash ] = true;
		}

		return $pass;
	}

	/**
	 * Validate if the connection has the required data to proceed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function connection_passed(): bool {

		if ( ! empty( $this->connection['account_id'] ) && ! empty( $this->connection['folder_id'] ) ) {
			return true;
		}

		$hash = hash( 'adler32', wp_json_encode( $this->connection ) );

		static $logged = [];

		if ( empty( $logged[ $hash ] ) ) {
			wpforms_log(
				sprintf( 'The Google Drive connection %s doesn\'t have required data to proceed.', $this->connection['name'] ?? '' ),
				[
					'connection' => $this->connection,
					'fields'     => $this->fields,
				],
				[
					'type'    => [ 'provider', 'conditional_logic' ],
					'parent'  => $this->entry_id,
					'form_id' => $this->form_data['id'],
				]
			);

			$logged[ $hash ] = true;
		}

		return false;
	}
}
