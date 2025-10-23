<?php

namespace WPFormsGoogleDrive;

use WPFormsGoogleDrive\Admin\Entry;
use WPForms\Pro\Forms\Fields\FileUpload\Field as FileUploadField;

/**
 * Field class.
 *
 * @since 1.0.0
 */
class Field {

	/**
	 * Field data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $field;

	/**
	 * File list.
	 *
	 * @since 1.0.0
	 *
	 * @var array|File[]
	 */
	private $files;

	/**
	 * Field constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field    Field data.
	 * @param int   $entry_id Entry ID.
	 */
	public function __construct( array $field, int $entry_id ) {

		if ( ! self::is_file_upload_field( $field ) ) {
			return;
		}

		if ( ! isset( $field['id'] ) ) {
			return;
		}

		$this->field = $field;
		$this->files = $this->prepare_files( $entry_id );
	}

	/**
	 * Prepare a list of files for the field.
	 *
	 * @since 1.0.0
	 *
	 * @param int $entry_id Entry ID.
	 *
	 * @return array|File[]
	 */
	private function prepare_files( int $entry_id ): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$uploaded_files = $this->get_uploaded_files( $entry_id );

		if ( ! $uploaded_files ) {
			return [];
		}

		if ( ! FileUploadField::is_modern_upload( $this->field ) ) {
			return empty( $uploaded_files[0] ) ?
				[] :
				[ new File( $uploaded_files[0], $this->field ) ];
		}

		$files = [];

		foreach ( $uploaded_files as $uploaded_file ) {
			if ( ! isset( $uploaded_file['file_id'] ) ) {
				continue;
			}

			// It can be empty if it was deleted before.
			$local_file = $this->field['value_raw'][ $uploaded_file['file_id'] ] ?? [];

			$files[] = new File( $uploaded_file, $local_file );
		}

		return $files;
	}

	/**
	 * Get uploaded files.
	 *
	 * @since 1.0.0
	 *
	 * @param int $entry_id Entry ID.
	 *
	 * @return array
	 */
	private function get_uploaded_files( int $entry_id ): array {

		$uploaded_attachments = Entry::get_saved_attachments( $entry_id );

		if ( ! $uploaded_attachments || empty( $uploaded_attachments->data ) ) {
			return [];
		}

		$uploaded_files = [];

		foreach ( $uploaded_attachments->data as $file ) {
			if ( (int) $file['field_id'] === (int) $this->field['id'] ) {
				$uploaded_files[] = $file;
			}
		}

		return $uploaded_files;
	}

	/**
	 * Determine if the field has any uploaded files.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_uploaded_files(): bool {

		return ! empty( $this->files );
	}

	/**
	 * Determine if the field has any local files.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_local_files(): bool {

		foreach ( $this->files as $file ) {
			if ( $file->has_local() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get files.
	 *
	 * @since 1.0.0
	 *
	 * @return array|File[]
	 */
	public function get_files(): array {

		return $this->files;
	}

	/**
	 * Get field label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_label(): string {

		return $this->field['label'] ?? '';
	}

	/**
	 * Determine if a field is the file upload type.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data.
	 *
	 * @return bool
	 */
	public static function is_file_upload_field( array $field ): bool {

		return isset( $field['type'] ) && $field['type'] === 'file-upload';
	}
}
