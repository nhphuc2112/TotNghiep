<?php

namespace WPFormsCalculations\Admin;

use stdClass;

/**
 * Calculation for Entries Edit feature.
 *
 * @since 1.0.0
 */
class EntriesEdit {

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		add_filter( 'wpforms_pro_admin_entries_edit_field_output_editable', [ $this, 'is_output_editable' ], 10, 5 );
		add_action( 'wpforms_pro_admin_entries_edit_submit_completed', [ $this, 'submit_completed' ], 10, 4 );
	}

	/**
	 * Calculated fields shouldn't be editable on Entry Edit page.
	 *
	 * @since 1.0.0
	 *
	 * @param bool     $is_editable Default value.
	 * @param array    $field       Field data.
	 * @param stdClass $entry       Entry object.
	 * @param array    $form_data   Form data and settings.
	 *
	 * @return bool
	 */
	public function is_output_editable( $is_editable, $field, $entry, $form_data ) {

		if ( empty( $form_data['fields'] ) ) {
			return $is_editable;
		}

		// Whether the form has enabled calculation.
		if ( ! wpforms_calculations()->helpers->has_calculation_enabled_field( $form_data ) ) {
			return $is_editable;
		}

		// Check if the current field has a calculation enabled.
		if ( ! empty( $field['calculation_is_enabled'] ) && ! empty( $field['calculation_code_php'] ) ) {
			return false;
		}

		// Get fields used in calculations in the form.
		$used_fields     = wpforms_calculations()->helpers->get_fields_used_in_form_calculations( $form_data );
		$disabled_fields = array_merge( ...$used_fields );

		if ( in_array( $field['id'], $disabled_fields, true ) ) {
			return false;
		}

		return $is_editable;
	}

	/**
	 * Calculate fields and update the entry fields values.
	 *
	 * @since 1.0.0
	 *
	 * @param array    $form_data      Form data and settings.
	 * @param array    $response       AJAX response data.
	 * @param array    $updated_fields Updated fields.
	 * @param stdClass $entry          Entry object.
	 */
	public function submit_completed( $form_data, $response, $updated_fields, $entry ) {

		if ( empty( $entry->fields ) ) {
			return;
		}

		$fields_entry         = json_decode( $entry->fields, true );
		$updated_fields_entry = $fields_entry;

		// Replace fields with updated values.
		foreach ( $updated_fields as $field_id => $updated_field ) {
			$updated_fields_entry[ $field_id ] = $updated_field;
		}

		// Calculate fields.
		$updated_fields_entry = wpforms_calculations()->process->process_fields( $updated_fields_entry, [], $form_data, false );

		// Replace payment fields with original value.
		foreach ( $updated_fields_entry as $field_id => $updated_field ) {
			if ( ! empty( $updated_field['type'] ) && strpos( $updated_field['type'], 'payment-' ) === 0 ) {
				$updated_fields_entry[ $field_id ] = $fields_entry[ $field_id ];
			}
		}

		// Update entry.
		$entry_obj        = wpforms()->obj( 'entry' );
		$entry_fields_obj = wpforms()->obj( 'entry_fields' );

		if ( $entry_obj && $entry_fields_obj ) {
			$update_entry_data = [
				'entry_id' => (int) $entry->entry_id,
				'fields'   => wp_json_encode( $updated_fields_entry ),
			];

			$entry_obj->update( $entry->entry_id, $update_entry_data );
			$entry_fields_obj->save( $updated_fields_entry, $form_data, $entry->entry_id, true );
		}
	}
}
