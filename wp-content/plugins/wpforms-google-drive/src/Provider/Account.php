<?php

namespace WPFormsGoogleDrive\Provider;

use Exception;
use RuntimeException;
use WPForms\Admin\Notice;
use WPFormsGoogleDrive\Plugin;
use WPFormsGoogleDrive\Api\Client;

/**
 * Class Account.
 *
 * @since 1.0.0
 */
class Account {

	/**
	 * Register all hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks(): void {

		add_action( 'admin_init', [ $this, 'process_auth' ] );
	}

	/**
	 * Process authorization.
	 *
	 * @since 1.0.0
	 */
	public function process_auth(): void {

		if ( ! wpforms_current_user_can( 'create_forms' ) || wp_doing_ajax() ) {
			return;
		}

		if ( ! isset( $_GET['wpforms_google_drive_connect'], $_GET['state'], $_GET['nonce'] ) ) {
			return;
		}

		$this->remove_query_args();

		if ( ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'wpforms-google-drive-connect' ) ) {
			return;
		}

		// We're using base64_decode here to safely decode the state parameter from the Dropbox OAuth flow.
		// This is required to process the OAuth request and is not used for obfuscation.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$state_data = json_decode( base64_decode( sanitize_text_field( wp_unslash( $_GET['state'] ) ) ), true );

		if ( ! is_array( $state_data ) ) {
			return;
		}

		if ( ! isset( $state_data['access_token'], $state_data['expires_in'], $state_data['refresh_token'], $state_data['email'] ) ) {
			return;
		}

		$this->save_account( $state_data );
	}

	/**
	 * Save a new account.
	 *
	 * @since 1.0.0
	 *
	 * @param array $state_data Authorize state data.
	 *
	 * @return void
	 */
	private function save_account( array $state_data ): void {

		if ( $this->is_already_exist( $state_data['email'] ) ) {
			Notice::add(
				esc_html__( 'A Google account with these credentials has already been added. Please try connecting a different account or verify the existing account details.', 'wpforms-google-drive' ),
				'error'
			);

			return;
		}

		$key  = uniqid( '', true );
		$time = time();

		$options = [
			'label'         => $state_data['email'],
			'access_token'  => $state_data['access_token'],
			'expires_in'    => $time + $state_data['expires_in'],
			'refresh_token' => $state_data['refresh_token'],
			'date'          => $time,
		];

		wpforms_update_providers_options( Plugin::SLUG, $options, $key );
	}

	/**
	 * Check if account with this API key already exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email Account email.
	 *
	 * @return bool
	 *
	 * @throws RuntimeException The account already exists.
	 */
	private function is_already_exist( string $email ): bool {

		$accounts = wpforms_get_providers_options( Plugin::SLUG );

		foreach ( $accounts as $account ) {
			if ( ! empty( $account['label'] ) && $account['label'] === $email ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Remove $_GET parameters from the URL.
	 *
	 * @since 1.0.0
	 */
	private function remove_query_args(): void {

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$_SERVER['REQUEST_URI'] = remove_query_arg(
			[
				'wpforms_google_drive_connect',
				'state',
				'nonce',
			],
			esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) )
		);
	}

	/**
	 * Get client by account ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $account_id Account ID.
	 *
	 * @return Client|null
	 */
	public function get_client_by_id( string $account_id ): ?Client {

		$accounts = $this->get_accounts();

		if ( empty( $accounts[ $account_id ] ) ) {
			return null;
		}

		$account       = $accounts[ $account_id ];
		$account['id'] = $account_id;

		try {
			return new Client( $account );
		} catch ( Exception $e ) {
			wpforms_log(
				'Can\'t create a Client',
				[
					'message' => $e->getMessage(),
					'account' => $account,
				],
				[
					'type' => [ 'provider', 'error' ],
				]
			);
		}

		return null;
	}

	/**
	 * Get all accounts in the id => label format.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_all(): array {

		$accounts = [];

		foreach ( $this->get_accounts() as $account_key => $account ) {
			if ( empty( $account['label'] ) || empty( $account['access_token'] ) ) {
				continue;
			}

			$accounts[ $account_key ] = $account['label'];
		}

		return $accounts;
	}

	/**
	 * Get all saved account settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_accounts(): array {

		static $accounts;

		if ( $accounts ) {
			return $accounts;
		}

		$accounts = wpforms_get_providers_options( Plugin::SLUG );

		return $accounts;
	}
}
