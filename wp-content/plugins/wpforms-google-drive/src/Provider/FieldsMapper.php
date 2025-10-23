<?php

namespace WPFormsGoogleDrive\Provider;

use WPFormsGoogleDrive\Field;
use WPForms\Pro\Forms\Fields\FileUpload\Field as FileUploadField;

/**
 * Field Mapper class.
 *
 * @since 1.0.0
 */
class FieldsMapper {

	/**
	 * Submitted fields.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $fields;

	/**
	 * Form data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $form_data;

	/**
	 * Connection data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $connection_data;

	/**
	 * FieldMapper constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields          Form fields.
	 * @param array $form_data       Form data.
	 * @param array $connection_data Connection data.
	 */
	public function __construct( array $fields, array $form_data, array $connection_data ) {

		$this->fields          = $fields;
		$this->form_data       = $form_data;
		$this->connection_data = $connection_data;
	}

	/**
	 * Get uploaded files.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_files(): array {

		$files = [];

		foreach ( $this->fields as $field ) {
			if ( ! Field::is_file_upload_field( $field ) ) {
				continue;
			}

			if ( ! empty( $this->connection_data['fields'] ) && ! in_array( $field['id'], $this->connection_data['fields'], true ) ) {
				continue;
			}

			$this->get_field_upload_files( $field, $files );
		}

		return $files;
	}

	/**
	 * Get field upload files.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data.
	 * @param array $files Files list.
	 *
	 * @return void
	 */
	private function get_field_upload_files( array $field, array &$files ): void {

		if ( empty( $field['id'] ) ) {
			return;
		}

		$field_id = $field['id'];

		// Classic field can contain only one file.
		if ( ! empty( $field['file'] ) ) {
			$field['field_id'] = $field_id;
			$files[]           = $this->get_file( $field );

			return;
		}

		if ( empty( $field['value_raw'] ) ) {
			return;
		}

		foreach ( $field['value_raw'] as $file_id => $file ) {
			$file['field_id'] = $field_id;
			$file['file_id']  = $file_id;
			$files[]          = $this->get_file( $file );
		}
	}

	/**
	 * Get file details.
	 *
	 * @since 1.0.0
	 *
	 * @param array $file File data. For the classic upload field, it also contains field details.
	 *
	 * @return array
	 * @noinspection PhpComposerExtensionStubsInspection
	 */
	private function get_file( array $file ): array {

		$path = $this->get_file_path( $file );
		$name = basename( $path );

		return [
			'field_id'      => $file['field_id'],
			'file_id'       => $file['file_id'] ?? 0,
			'folder_id'     => $this->connection_data['folder_id'] ?? '',
			'name'          => $name,
			'file_original' => $file['file_original'] ?? $name,
			'path'          => $path,
			'mime'          => mime_content_type( $path ),
			'attachment_id' => ! empty( $file['attachment_id'] ) ? (int) $file['attachment_id'] : 0,
		];
	}

	/**
	 * Get a file path.
	 *
	 * @since 1.0.0
	 *
	 * @param array $file File data.
	 *
	 * @return string
	 */
	public function get_file_path( array $file ): string {

		if ( ! empty( $file['attachment_id'] ) ) {

			$file_path = get_attached_file( $file['attachment_id'] );

			return ! empty( $file_path ) ? $file_path : '';
		}

		return ! empty( $file['file'] ) ? FileUploadField::get_form_files_path( $this->form_data['id'] ) . '/' . $file['file'] : '';
	}
}
