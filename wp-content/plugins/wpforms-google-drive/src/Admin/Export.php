<?php

namespace WPFormsGoogleDrive\Admin;

use WPFormsGoogleDrive\Field;

/**
 * Export class.
 *
 * @since 1.0.0
 */
class Export {

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {

		if (
			wpforms_is_admin_page( 'tools', 'export' ) ||
			wpforms_is_ajax( 'wpforms_tools_entries_export_step' )
		) {
			$this->hooks();
		}
	}

	/**
	 * Export hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_pro_admin_entries_export_additional_info_fields', [ $this, 'add_additional_info_field' ] );
		add_filter( 'wpforms_pro_admin_entries_export_ajax_get_additional_info_value', [ $this, 'get_additional_info_value' ], 10, 3 );
		add_filter( 'wpforms_pro_admin_entries_export_ajax_get_csv_cols', [ $this, 'update_csv_cols' ], 10, 2 );
	}

	/**
	 * Add Google Drive info to the additional export fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array|mixed $additional_fields Additional export fields.
	 *
	 * @return array
	 */
	public function add_additional_info_field( $additional_fields ): array {

		$additional_fields = (array) $additional_fields;

		$additional_fields['google_drive_link'] = esc_html__( 'Google Drive Links', 'wpforms-google-drive' );

		return $additional_fields;
	}

	/**
	 * Get the value of an additional information column.
	 *
	 * @since 1.0.0
	 *
	 * @param string|mixed $val    The value.
	 * @param string       $col_id Column id.
	 * @param array        $entry  Entry object.
	 *
	 * @return string
	 */
	public function get_additional_info_value( $val, string $col_id, array $entry ): string {

		$val = (string) $val;

		if ( strpos( $col_id, 'google_drive_link' ) === false ) {
			return $val;
		}

		$field_id = filter_var( $col_id, FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $field_id ) ) {
			return $val;
		}

		if ( empty( $entry['entry_id'] ) || empty( $entry['fields'] ) ) {
			return $val;
		}

		$fields = (array) json_decode( $entry['fields'], true );

		if ( empty( $fields[ $field_id ] ) ) {
			return $val;
		}

		$field = new Field( $fields[ $field_id ], (int) $entry['entry_id'] );

		$files = $field->get_files();
		$urls  = [];

		foreach ( $files as $file ) {
			$urls[] = $file->get_google_drive_file_url();
		}

		return implode( "\n", $urls );
	}

	/**
	 * Update CSV columns.
	 *
	 * @since        1.0.0
	 *
	 * @param array $columns_row  Columns row.
	 * @param array $request_data Request data.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function update_csv_cols( $columns_row, array $request_data ): array {

		$columns_row = (array) $columns_row;

		if ( ! isset( $columns_row['google_drive_link'] ) ) {
			return $columns_row;
		}

		if ( empty( $request_data['form_data']['fields'] ) ) {
			return $columns_row;
		}

		$fields = $request_data['form_data']['fields'];

		unset( $columns_row['google_drive_link'] );

		foreach ( $fields as $field ) {
			if ( ! Field::is_file_upload_field( $field ) ) {
				continue;
			}

			if ( ! isset( $field['label'], $field['id'] ) ) {
				continue;
			}

			$label = sprintf(
				'%1$s %2$s',
				esc_html__( 'Google Drive Links: Field', 'wpforms-google-drive' ),
				esc_html( $field['label'] )
			);

			$columns_row[ 'google_drive_link_' . $field['id'] ] = $label;
		}

		return $columns_row;
	}
}
