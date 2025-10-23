<?php

namespace WPFormsGoogleDrive\Admin;

use WPFormsGoogleDrive\Plugin;
use WPFormsGoogleDrive\Api\Client;
use WPFormsGoogleDrive\Provider\Settings\FormBuilder;

/**
 * Class Admin.
 *
 * @since 1.1.0
 */
class Admin {

	/**
	 * Initialize.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	private function hooks(): void {

		if (
			! wpforms_is_admin_page( 'settings', 'integrations' ) &&
			! wpforms_is_admin_page( 'entries', 'details' )
		) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wpforms_admin_strings', [ $this, 'admin_strings' ] );
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.1.0
	 */
	public function enqueue_styles(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-google-drive-admin',
			WPFORMS_GOOGLE_DRIVE_URL . "assets/css/admin$min.css",
			[],
			WPFORMS_GOOGLE_DRIVE_VERSION
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.1.0
	 */
	public function enqueue_scripts(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-google-drive-admin',
			WPFORMS_GOOGLE_DRIVE_URL . "assets/js/admin$min.js",
			[ 'jquery' ],
			WPFORMS_GOOGLE_DRIVE_VERSION,
			true
		);
	}

	/**
	 * Add own localized strings to the WPForms -> Settings -> Integration page.
	 *
	 * @since        1.1.0
	 *
	 * @param array|mixed $strings Localized strings.
	 *
	 * @return array
	 */
	public function admin_strings( $strings ): array {

		$strings = (array) $strings;

		$strings['google_drive'] = array_merge( $this->integration_page_strings(), $this->entry_details_strings() );

		return $strings;
	}

	/**
	 * Get integration page strings.
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	private function integration_page_strings(): array {

		if ( ! wpforms_is_admin_page( 'settings', 'integrations' ) ) {
			return [];
		}

		return [
			'auth_url' => Client::get_auth_url(
				add_query_arg(
					[
						'page'                => 'wpforms-settings',
						'view'                => 'integrations',
						'wpforms-integration' => Plugin::SLUG,
					],
					admin_url( 'admin.php' )
				)
			),
		];
	}

	/**
	 * Get entry details page strings.
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 *
	 * @noinspection NullPointerExceptionInspection
	 */
	private function entry_details_strings(): array {

		if ( ! wpforms_is_admin_page( 'entries', 'details' ) ) {
			return [];
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['entry_id'] ) ) {
			return [];
		}

		$entry = wpforms()->obj( 'entry' )->get( absint( $_GET['entry_id'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( empty( $entry ) ) {
			return [];
		}
		$form_data = wpforms()->obj( 'form' )->get(
			$entry->form_id,
			[ 'content_only' => true ]
		);

		if ( empty( $form_data ) ) {
			return [];
		}

		return [
			'is_file_deletion_enabled'                => FormBuilder::is_enabled_delete_local_files( $form_data ),
			'delete_local_files_confirmation_message' => esc_html__( 'This action will upload files to Google Drive and delete them from the server. Do you want to continue?', 'wpforms-google-drive' ),
			'upload_delete_confirm_button'            => esc_html__( 'Yes, Upload and Delete', 'wpforms-google-drive' ),
		];
	}
}
