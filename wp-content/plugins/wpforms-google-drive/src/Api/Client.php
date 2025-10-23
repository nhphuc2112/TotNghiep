<?php

namespace WPFormsGoogleDrive\Api;

use WPForms\Helpers\File;
use InvalidArgumentException;
use WPFormsGoogleDrive\Plugin;
use WPFormsGoogleDrive\Api\Http\Request;

/**
 * Client class.
 *
 * @since 1.0.0
 */
class Client {

	/**
	 * Files more than 5MB should be uploaded by chunks.
	 *
	 * @since 1.0.0
	 */
	private const CHUNK_SIZE = 4.5 * MB_IN_BYTES;

	/**
	 * WPForms website URL.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private const MIDDLEWARE_URL = 'https://wpforms.com/oauth/google-drive-connect/';

	/**
	 * Account settings.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $account;

	/**
	 * Class for making HTTP requests.
	 *
	 * @since 1.0.0
	 *
	 * @var Request
	 */
	private $request;

	/**
	 * Client constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $account Account settings.
	 *
	 * @throws InvalidArgumentException If the access token, refresh token, or account ID is empty.
	 */
	public function __construct( array $account ) {

		if ( empty( $account['access_token'] ) ) {
			throw new InvalidArgumentException( 'Access token cannot be empty.' );
		}

		if ( empty( $account['refresh_token'] ) ) {
			throw new InvalidArgumentException( 'Refresh token cannot be empty.' );
		}

		if ( empty( $account['id'] ) ) {
			throw new InvalidArgumentException( 'Account ID cannot be empty.' );
		}

		$this->account = $account;

		$this->maybe_refresh_token();

		$this->request = new Request( $this->account['access_token'] );
	}

	/**
	 * Check if the access token is expired.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_expired_token(): bool {

		$expires_in = $this->account['expires_in'] ?? 0;

		/**
		 * Adding one minute to cover a very rare case when a few seconds are left,
		 * and the site runs multiple API requests.
		 * The last one could be outdated.
		 */
		return ( time() + MINUTE_IN_SECONDS ) > $expires_in;
	}

	/**
	 * Refresh the access token if it is expired.
	 *
	 * @since 1.0.0
	 *
	 * @throws InvalidArgumentException If the response body does not contain the access token or expires in.
	 */
	private function maybe_refresh_token(): void {

		if ( ! $this->is_expired_token() ) {
			return;
		}

		$response = wp_remote_post(
			self::get_middleware_url(),
			[
				'body' => [
					'refresh_token' => $this->account['refresh_token'],
				],
			]
		);

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $response_body['data']['access_token'], $response_body['data']['expires_in'] ) ) {
			throw new InvalidArgumentException( esc_html__( 'Cannot refresh the token.', 'wpforms-google-drive' ) );
		}

		$this->account = array_merge(
			$this->account,
			[
				'access_token' => $response_body['data']['access_token'],
				'expires_in'   => time() + (int) ( $response_body['data']['expires_in'] ),
			]
		);

		wpforms_update_providers_options( Plugin::SLUG, $this->account, $this->account['id'] );
	}

	/**
	 * Received updated fresh access token.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_access_token(): string {

		return (string) $this->account['access_token'];
	}

	/**
	 * Create a folder.
	 *
	 * @since 1.0.0
	 *
	 * @param string $folder_name Folder name.
	 *
	 * @return string
	 */
	public function create_folder( string $folder_name ): string {

		$response = $this->request->post(
			'https://www.googleapis.com/drive/v3/files',
			[
				'name'     => $folder_name,
				'mimeType' => 'application/vnd.google-apps.folder',
			]
		);

		$body = $response->get_body();

		return isset( $body['id'] ) ? (string) $body['id'] : '';
	}

	/**
	 * Upload a file to a specific Google Drive directory.
	 *
	 * @since 1.0.0
	 *
	 * @param array $file_data File data.
	 *
	 * @return string
	 */
	public function upload_file( array $file_data ): string {

		if ( empty( $file_data['path'] ) ) {
			return '';
		}

		$this->set_time_limit();

		$file = File::get_contents( $file_data['path'] );

		if ( ! $file ) {
			return '';
		}

		$file_meta_data = $this->prepare_file_metadata( $file_data );

		if ( ! $file_meta_data ) {
			return '';
		}

		$file_id = strlen( $file ) > self::CHUNK_SIZE
			? $this->resumable_upload( $file_meta_data, $file )
			: $this->multipart_upload( $file_meta_data, $file );

		unset( $file );

		return $file_id;
	}

	/**
	 * Determine if a file exists in Google Drive.
	 *
	 * @since 1.1.0
	 *
	 * @param string $file_id File ID.
	 *
	 * @return bool
	 */
	public function file_exists( string $file_id ): bool {

		$response = $this->request->get(
			sprintf( 'https://www.googleapis.com/drive/v3/files/%s', $file_id )
		);

		$body = $response->get_body();

		return isset( $body['id'] );
	}

	/**
	 * Set time limit.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function set_time_limit(): void {

		static $set = false;

		if ( $set ) {
			return;
		}

		wpforms_set_time_limit( 300 );
		$set = true;
	}

	/**
	 * Prepare required file meta data for requests.
	 *
	 * @since 1.0.0
	 *
	 * @param array $file_data File data.
	 *
	 * @return array
	 */
	private function prepare_file_metadata( array $file_data ): array {

		if ( empty( $file_data['name'] ) || empty( $file_data['mime'] ) || empty( $file_data['folder_id'] ) ) {
			return [];
		}

		return [
			'name'     => $file_data['name'],
			'mimeType' => $file_data['mime'],
			'parents'  => [ $file_data['folder_id'] ],
		];
	}

	/**
	 * Upload big files more than 4.5 MB, submitting them by chunks.
	 *
	 * @since 1.0.0
	 *
	 * @doc https://developers.google.com/drive/api/guides/manage-uploads#resumable
	 *
	 * @param array  $file_meta_data File meta data.
	 * @param string $file           File contents.
	 *
	 * @return string
	 */
	private function resumable_upload( array $file_meta_data, string $file ): string {

		$response = $this->request->post(
			'https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable',
			$file_meta_data,
			[
				'X-Upload-Content-Type' => 'application/octet-stream',
			]
		);

		$upload_url = $response->get_location();

		if ( ! $upload_url ) {
			return '';
		}

		$file_size = strlen( $file );
		$chunks    = ceil( $file_size / self::CHUNK_SIZE );
		$uploaded  = 0;

		for ( $i = 0; $i < $chunks; $i++ ) {
			$chunk_size = min( self::CHUNK_SIZE, $file_size - $uploaded );
			$chunk      = substr( $file, $uploaded, $chunk_size );
			$range      = sprintf(
                'bytes %d-%d/%d',
				$uploaded,
				$uploaded + $chunk_size - 1,
				$file_size
			);

			$response = $this->request->put(
				$upload_url,
				$chunk,
				[
					'Content-Range' => $range,
				]
			);

			$uploaded += $chunk_size;
		}

		$body = $response->get_body();

		return empty( $body['id'] ) ? '' : (string) $body['id'];
	}

	/**
	 * Upload a small file (less than 4.5MB) per one request.
	 *
	 * @since 1.0.0
	 *
	 * @doc https://developers.google.com/drive/api/guides/manage-uploads#multipart
	 *
	 * @param array  $file_meta_data File meta data.
	 * @param string $file           File contents.
	 *
	 * @return string
	 */
	private function multipart_upload( array $file_meta_data, string $file ): string {

		$response = $this->request->post_multipart(
			'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart',
			$file_meta_data,
			$file
		);

		$body = $response->get_body();

		return empty( $body['id'] ) ? '' : (string) $body['id'];
	}

	/**
	 * Get authorization url to begin OAuth flow.
	 *
	 * @since 1.0.0
	 *
	 * @param string $redirect_uri Redirect URI.
	 *
	 * @return string
	 */
	public static function get_auth_url( string $redirect_uri ): string {

		$redirect_uri = add_query_arg(
			[
				'nonce' => wp_create_nonce( 'wpforms-google-drive-connect' ),
			],
			$redirect_uri
		);

		$state = rawurlencode(
			base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				wp_json_encode(
					[
						'redirect_uri' => $redirect_uri,
					]
				)
			)
		);

		return add_query_arg(
			[
				'action' => 'init',
				'state'  => $state,
			],
			self::get_middleware_url()
		);
	}

	/**
	 * Get middleware URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 * @noinspection PhpUndefinedConstantInspection
	 */
	private static function get_middleware_url(): string {

		if ( defined( 'WPFORMS_GOOGLE_DRIVE_MIDDLEWARE_URL' ) && WPFORMS_GOOGLE_DRIVE_MIDDLEWARE_URL ) {
			return (string) WPFORMS_GOOGLE_DRIVE_MIDDLEWARE_URL;
		}

		return self::MIDDLEWARE_URL;
	}
}
