<?php

namespace WPFormsGoogleDrive\Admin;

use WPForms\Admin\Notice;
use WPForms_Entries_Single;
use WPFormsGoogleDrive\File;
use WPForms\Helpers\File as FileHelper;
use WPFormsGoogleDrive\Field;
use WPFormsGoogleDrive\Plugin;

/**
 * Class Entry.
 *
 * @since 1.0.0
 */
class Entry {

	/**
	 * Meta-key for storing Google Drive attachments.
	 *
	 * @since 1.0.0
	 */
	private const META = 'google_drive_attachments';

	/**
	 * Meta-key for storing the last update information of Google Drive files.
	 *
	 * @since 1.1.0
	 */
	private const LAST_UPDATE_META = 'google_drive_last_update';

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

		add_filter( 'wpforms_html_field_value', [ $this, 'field_html_value' ], 11, 4 );

		if ( wpforms_is_admin_page( 'entries', 'details' ) || wpforms_is_admin_page( 'entries', 'edit' ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_style' ], 10, 3 );
		}

		add_action( 'wpforms_pro_admin_entries_printpreview_print_html_head', [ $this, 'print_preview_head' ] );
		add_filter( 'wpforms_entry_details_sidebar_actions_link', [ $this, 'add_reupload_action' ], 10, 3 );
		add_action( 'wpforms_entry_details_init', [ $this, 'maybe_display_reupload_notice' ] );
	}

	/**
	 * Add a reupload action to the entry details sidebar.
	 *
	 * @since 1.1.0
	 *
	 * @param array  $actions   Entry actions.
	 * @param object $entry     Entry object.
	 * @param array  $form_data Form data and settings.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function add_reupload_action( $actions, $entry, $form_data ): array {

		$actions = (array) $actions;

		if ( ! $this->is_reupload_available( $entry, $form_data ) ) {
			return $actions;
		}

		return wpforms_array_insert(
			$actions,
			[
				'google_drive_reupload' => [
					'url'   => add_query_arg(
						[
							'page'     => 'wpforms-entries',
							'view'     => 'details',
							'entry_id' => $entry->entry_id,
							'_wpnonce' => wp_create_nonce( 'wpforms_google_drive_reupload' ),
						],
						admin_url( 'admin.php' )
					),
					'icon'  => 'fa fa-google',
					'label' => esc_html__( 'Reupload to Google Drive', 'wpforms-google-drive' ),
				],
			],
			'print',
			'before'
		);
	}

	/**
	 * Check if reupload is available for the entry.
	 *
	 * @since 1.1.0
	 *
	 * @param object $entry     Entry object.
	 * @param array  $form_data Form data.
	 *
	 * @return bool
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	private function is_reupload_available( $entry, $form_data ): bool {

		if ( empty( $form_data['providers'][ Plugin::SLUG ] ) ) {
			return false;
		}

		$fields = wpforms_decode( $entry->fields );

		if ( empty( $fields ) ) {
			return false;
		}

		foreach ( $fields as $field ) {
			if ( ! Field::is_file_upload_field( $field ) ) {
				continue;
			}

			$field_obj = new Field( $field, $entry->entry_id );

			if ( $field_obj->has_local_files() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Display a notice about reuploaded files if applicable.
	 *
	 * @since 1.1.0
	 *
	 * @param WPForms_Entries_Single $entry_single Entry object.
	 */
	public function maybe_display_reupload_notice( WPForms_Entries_Single $entry_single ): void {

		$entry_meta = wpforms()->obj( 'entry_meta' );

		if ( ! $entry_meta ) {
			return;
		}

		$existing_record = $entry_meta->get_meta(
			[
				'entry_id' => $entry_single->entry->entry_id,
				'type'     => self::LAST_UPDATE_META,
				'number'   => 1,
			]
		);

		if ( empty( $existing_record[0] ) ) {
			return;
		}

		$uploaded_files = $existing_record[0]->data ? json_decode( $existing_record[0]->data, true ) : [];

		$entry_meta->delete( $existing_record[0]->id );

		if ( empty( $uploaded_files ) ) {
			Notice::info( __( 'Files were skipped because they were previously uploaded to Google Drive successfully.', 'wpforms-google-drive' ) );

			return;
		}

		$count = count( $uploaded_files );

		Notice::success(
			sprintf( /* translators: %d – number of uploaded files. */
				_n( '%d file was reuploaded to Google Drive.', '%d files were reuploaded to Google Drive.', $count, 'wpforms-google-drive' ),
				$count
			)
		);
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_style(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-google-drive-admin',
			WPFORMS_GOOGLE_DRIVE_URL . "assets/css/admin$min.css",
			[ 'wpforms-admin' ],
			WPFORMS_GOOGLE_DRIVE_VERSION
		);
	}

	/**
	 * Include styles for file links inside the print preview.
	 *
	 * @since 1.0.0
	 */
	public function print_preview_head(): void {

		$min = wpforms_get_min_suffix();

		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		echo '<link rel="stylesheet" href="' . esc_url( WPFORMS_GOOGLE_DRIVE_URL . "assets/css/admin$min.css" ) . '" type="text/css">';
	}


	/**
	 * Customize HTML field values.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value     Field value.
	 * @param array  $field     Field data.
	 * @param array  $form_data Form data.
	 * @param string $context   Entry context.
	 *
	 * @return string
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function field_html_value( $value, array $field, array $form_data = [], string $context = '' ): string {

		$value = (string) $value;

		if ( $context !== 'entry-single' ) {
			return $value;
		}

		// It's impossible to check nonce here.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['entry_id'] ) ) {
			return $value;
		}

		if ( ! Field::is_file_upload_field( $field ) ) {
			return $value;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$entry_id  = absint( $_GET['entry_id'] );
		$field_obj = new Field( $field, $entry_id );

		if ( ! $field_obj->has_uploaded_files() ) {
			return $value;
		}

		return $this->generate_field_links( $field_obj );
	}

	/**
	 * Generate file link HTML for a given file entry.
	 *
	 * @since 1.0.0
	 *
	 * @param Field $field Field.
	 *
	 * @return string Generated HTML for file links.
	 */
	private function generate_field_links( Field $field ): string {

		$files = $field->get_files();

		if ( ! $files ) {
			return '';
		}

		$markup = '';

		foreach ( $files as $file ) {
			$markup .= $this->generate_file_link( $file );
		}

		return $markup;
	}

	/**
	 * Generate file link.
	 *
	 * @since 1.0.0
	 *
	 * @param File $file File data.
	 *
	 * @return string Generated HTML for file links.
	 * @noinspection HtmlUnknownTarget
	 */
	private function generate_file_link( File $file ): string {

		$url = $file->get_google_drive_file_url();

		if ( ! $url ) {
			return '';
		}

		$html = '<span class="wpforms-google-drive-file-line">';

		if ( $file->has_local() ) {
			$html .= $file->get_local_file_html();
			$html .= '<span class="wpforms-google-drive-divider">|</span>';
		}

		$name = $file->has_local() ? __( 'View in Google Drive', 'wpforms-google-drive' ) : $file->get_name();

		$html .= sprintf(
			'%s<a href="%s" rel="noopener noreferrer" target="_blank">%s</a>',
			$file->has_local() ? '' : '<span class="file-icon"><i class="fa fa-google"></i></span>',
			esc_url( $file->get_google_drive_file_url() ),
			esc_html( $name )
		);

		$html .= '<br></span>';

		return $html;
	}

	/**
	 * Delete local files.
	 *
	 * @since 1.0.0
	 *
	 * @param int $entry_id Entry ID.
	 *
	 * @return void
	 */
	public static function delete_local_files( int $entry_id ): void {

		$uploaded_attachments = self::get_saved_attachments( $entry_id );

		if ( ! $uploaded_attachments || empty( $uploaded_attachments->data ) ) {
			return;
		}

		foreach ( $uploaded_attachments->data as $file ) {
			if ( ! isset( $file['field_id'] ) ) {
				continue;
			}

			self::delete_local_file( $file );
			self::delete_files_in_entries_fields( $file['field_id'], $entry_id );
		}
	}

	/**
	 * Delete local files.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field File data.
	 */
	private static function delete_local_file( array $field ): void {

		if ( ! empty( $field['attachment_id'] ) ) {
			wp_delete_attachment( $field['attachment_id'], true );

			return;
		}

		if ( empty( $field['path'] ) ) {
			return;
		}

		FileHelper::delete( $field['path'] );
	}

	/**
	 * Delete local files from DB.
	 *
	 * @since 1.0.0
	 *
	 * @param int $field_id Field ID.
	 * @param int $entry_id Entry ID.
	 *
	 * @return void
	 */
	private static function delete_files_in_entries_fields( int $field_id, int $entry_id ): void {

		global $wpdb;

		$entry_fields_obj = wpforms()->obj( 'entry_fields' );

		if ( ! $entry_fields_obj ) {
			return;
		}

		$entry_fields_table = $entry_fields_obj->table_name;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $entry_fields_table WHERE entry_id = %d AND field_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$entry_id,
				$field_id
			)
		);

		$entry_obj = wpforms()->obj( 'entry' );

		if ( ! $entry_obj ) {
			return;
		}

		$entry = $entry_obj->get( $entry_id );

		if ( empty( $entry ) ) {
			return;
		}

		$entry_fields = wpforms_decode( $entry->fields );

		if ( ! empty( $entry_fields[ $field_id ] ) ) {
			$entry_fields[ $field_id ]['value'] = '';

			if ( ! empty( $entry_fields[ $field_id ]['value_raw'] ) ) {
				$entry_fields[ $field_id ]['value_raw'] = self::remove_non_protected_files( $entry_fields[ $field_id ]['value_raw'] );
			}
		}

		$entry_data = [
			'fields'        => wp_json_encode( $entry_fields ),
			'date_modified' => current_time( 'Y-m-d H:i:s' ),
		];

		$entry_obj->update(
			$entry_id,
			$entry_data,
			'',
			'edit_entry',
			[ 'cap' => false ]
		);
	}

	/**
	 * Process value_raw by keeping only valid protection hashes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value_raw The raw field values.
	 *
	 * @return array|string Processed value_raw or an empty string.
	 */
	private static function remove_non_protected_files( array $value_raw ) {

		$value_raw = array_filter(
			array_map(
				static function ( $item ) {

					return ! empty( $item['protection_hash'] )
						? [ 'protection_hash' => $item['protection_hash'] ]
						: null;
				},
				$value_raw
			)
		);

		return empty( $value_raw ) ? '' : array_values( $value_raw );
	}

	/**
	 * Get saved attachments for the specified entry and form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $entry_id Entry ID.
	 *
	 * @return object|null
	 */
	public static function get_saved_attachments( int $entry_id ): ?object {

		static $attachments = [];

		if ( ! empty( $attachments[ $entry_id ] ) ) {
			return $attachments[ $entry_id ];
		}

		$entry_meta = wpforms()->obj( 'entry_meta' );

		if ( ! $entry_meta ) {
			return null;
		}

		// Retrieve the existing record.
		$existing_record = $entry_meta->get_meta(
			[
				'entry_id' => $entry_id,
				'type'     => self::META,
				'number'   => 1,
			]
		);

		if ( empty( $existing_record ) || empty( $existing_record[0]->data ) || empty( $existing_record[0]->id ) ) {
			return null;
		}

		$existing_record[0]->data = (array) json_decode( $existing_record[0]->data, true );

		$existing_record[0]       = (object) $existing_record[0];
		$attachments[ $entry_id ] = $existing_record[0];

		return $existing_record[0];
	}

	/**
	 * Save uploaded files to an entry.
	 *
	 * @since 1.0.0
	 *
	 * @param array $files    Files.
	 * @param int   $entry_id Entry ID.
	 * @param int   $form_id  Form ID.
	 *
	 * @return void
	 */
	public static function save_google_drive_files( array $files, int $entry_id, int $form_id ): void {

		$entry_meta = wpforms()->obj( 'entry_meta' );

		if ( ! $entry_meta ) {
			return;
		}

		$uploaded_attachments = self::get_saved_attachments( $entry_id );

		if ( ! $uploaded_attachments || empty( $uploaded_attachments->id ) ) {
			$entry_meta->add(
				[
					'entry_id' => $entry_id,
					'form_id'  => absint( $form_id ),
					'user_id'  => absint( get_current_user_id() ),
					'type'     => self::META,
					'data'     => wp_json_encode( $files ),
				],
				'entry_meta'
			);

			return;
		}

		foreach ( $files as $file ) {
			if ( ! isset( $file['field_id'], $file['file_id'] ) ) {
				continue;
			}

			$entry_file_id = self::is_already_added( $uploaded_attachments->data, (int) $file['field_id'], (int) $file['file_id'] );

			if ( $entry_file_id === false ) {
				$uploaded_attachments->data[] = $file;

				continue;
			}

			$uploaded_attachments->data[ $entry_file_id ] = $file;
		}

		// Update the merged data in the database.
		$entry_meta->update(
			$uploaded_attachments->id,
			[
				'data' => wp_json_encode( $uploaded_attachments->data ),
			]
		);
	}

	/**
	 * Check if a file is already present in the uploaded attachments.
	 *
	 * @since 1.1.0
	 *
	 * @param array $uploaded_attachments The list of already uploaded attachments.
	 * @param int   $field_id             Field ID.
	 * @param int   $file_id              File ID.
	 *
	 * @return false|int
	 */
	private static function is_already_added( array $uploaded_attachments, int $field_id, int $file_id ) {

		foreach ( $uploaded_attachments as $key => $uploaded_file ) {
			if (
				isset( $uploaded_file['field_id'], $uploaded_file['file_id'] )
				&& $uploaded_file['field_id'] === $field_id
				&& $uploaded_file['file_id'] === $file_id
			) {
				return (int) $key;
			}
		}

		return false;
	}

	/**
	 * Save information about the last uploaded files to Google Drive.
	 *
	 * @since 1.1.0
	 *
	 * @param int   $entry_id        Entry ID.
	 * @param int   $form_id         Form ID.
	 * @param array $old_attachments Previous attachments data.
	 */
	public static function save_last_uploaded_files( int $entry_id, int $form_id, array $old_attachments ): void {

		$entry_meta = wpforms()->obj( 'entry_meta' );

		if ( ! $entry_meta ) {
			return;
		}

		$new_saved_attachments = self::get_saved_attachments( $entry_id );
		$new_saved_attachments = $new_saved_attachments && property_exists( $new_saved_attachments, 'data' ) ? $new_saved_attachments->data : [];

		$last_updated = [];

		foreach ( $old_attachments as $old_attachment ) {
			foreach ( $new_saved_attachments as $new_attachment ) {
				if (
					$old_attachment['field_id'] === $new_attachment['field_id'] &&
					$old_attachment['file_id'] === $new_attachment['file_id'] &&
					$old_attachment['google_drive_id'] !== $new_attachment['google_drive_id']
				) {
					$last_updated[] = $new_attachment['google_drive_id'];
				}
			}
		}

		$existing_record = $entry_meta->get_meta(
			[
				'entry_id' => $entry_id,
				'type'     => self::LAST_UPDATE_META,
				'number'   => 1,
			]
		);

		if ( ! empty( $existing_record[0]->id ) ) {
			$entry_meta->update(
				$existing_record[0]->id,
				[
					'data' => wp_json_encode( $last_updated ),
				]
			);

			return;
		}

		$entry_meta->add(
			[
				'entry_id' => $entry_id,
				'form_id'  => absint( $form_id ),
				'user_id'  => absint( get_current_user_id() ),
				'type'     => self::LAST_UPDATE_META,
				'data'     => wp_json_encode( $last_updated ),
			],
			'entry_meta'
		);
	}
}
