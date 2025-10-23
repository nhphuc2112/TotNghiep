<?php

namespace WPFormsGoogleDrive;

use WPForms\Helpers\Crypto;
use WPForms\Pro\Forms\Fields\FileUpload\Field as FileUploadField;

/**
 * File class.
 *
 * @since 1.0.0
 */
class File {

	/**
	 * Uploaded file data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $uploaded_file;

	/**
	 * Local file data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $local_file;

	/**
	 * File construct.
	 *
	 * @since 1.0.0
	 *
	 * @param array $uploaded_file Uploaded file data.
	 * @param array $local_file    Local file data.
	 */
	public function __construct( array $uploaded_file, array $local_file ) {

		$this->uploaded_file = $uploaded_file;
		$this->local_file    = $local_file;

		// When a local file is deleted, we don't have the local file field ID, but it's required to generate URL.
		if ( empty( $this->local_file['id'] ) && ! empty( $this->uploaded_file['field_id'] ) ) {
			$this->local_file['id'] = $this->uploaded_file['field_id'];
		}
	}

	/**
	 * A local file wasn't deleted.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_local(): bool {

		return ! empty( $this->local_file['value'] );
	}

	/**
	 * Is the file protected?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_protected(): bool {

		return ! empty( $this->local_file['protection_hash'] );
	}

	/**
	 * Get file name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_name(): string {

		return isset( $this->uploaded_file['file_original'] ) ? (string) $this->uploaded_file['file_original'] : '';
	}

	/**
	 * Get file URL to Google Drive storage.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_google_drive_file_url(): string {

		$google_drive_url = $this->get_pure_google_drive_file_url();

		if ( ! $google_drive_url ) {
			return '';
		}

		return self::get_protected_url( $google_drive_url, $this->local_file );
	}

	/**
	 * Get Google Drive file URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_pure_google_drive_file_url(): string {

		if ( empty( $this->uploaded_file['google_drive_id'] ) ) {
			return '';
		}

		return sprintf( 'https://drive.google.com/file/d/%1$s/view', $this->uploaded_file['google_drive_id'] );
	}

	/**
	 * Prepare HTML markup for a local file.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	public function get_local_file_html(): string {

		$file_upload_obj = new FileUploadField( false );

		$html = $file_upload_obj->file_icon_html( $this->local_file );

		$html .= sprintf(
			'<a href="%s" rel="noopener noreferrer" target="_blank">%s</a>',
			esc_url( $file_upload_obj->get_file_url( $this->local_file ) ),
			esc_html( $file_upload_obj->get_file_name( $this->local_file ) )
		);

		return $html;
	}

	/**
	 * Get local file URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_local_file_url(): string {

		return ( new FileUploadField( false ) )->get_file_url( $this->local_file );
	}

	/**
	 * Prepare HTML markup for an uploaded file.
	 *
	 * @since 1.0.0
	 *
	 * @param int $file_id  File ID.
	 * @param int $entry_id Entry ID.
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	public function get_uploaded_file_html( int $file_id, int $entry_id ): string {

		return sprintf(
			'<a href="%s" rel="noopener noreferrer" target="_blank">%s</a>',
			esc_url( self::generate_download_url( $this->local_file, $file_id, $entry_id ) ),
			esc_html( $this->get_name() )
		);
	}

	/**
	 * Prepare HTML markup for an uploaded file.
	 *
	 * @since 1.0.0
	 *
	 * @param int $file_id  File ID.
	 * @param int $entry_id Entry ID.
	 *
	 * @return string
	 */
	public function get_uploaded_file_plain( int $file_id, int $entry_id ): string {

		return esc_url_raw( self::generate_download_url( $this->local_file, $file_id, $entry_id ) );
	}

	/**
	 * Generate download URL.
	 *
	 * @since 1.0.0
	 *
	 * @param array $local_file Local file data.
	 * @param int   $file_id    File ID.
	 * @param int   $entry_id   Entry ID.
	 *
	 * @return string
	 */
	public static function generate_download_url( array $local_file, int $file_id, int $entry_id ): string {

		if ( empty( $local_file['id'] ) ) {
			return '';
		}

		$url = add_query_arg(
			[
				'wpforms_google_drive' => 'download',
				'file'                 => Crypto::encrypt(
					wp_json_encode(
						[
							'field_id' => $local_file['id'],
							'file_id'  => $file_id,
							'entry_id' => $entry_id,
						]
					)
				),
			],
			home_url()
		);

		return self::get_protected_url( $url, $local_file );
	}

	/**
	 * Get protected URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url        File download or Google Drive URL.
	 * @param array  $local_file Local file data.
	 *
	 * @return string
	 */
	private static function get_protected_url( string $url, array $local_file ): string {

		if ( empty( $local_file['protection_hash'] ) ) {
			return $url;
		}

		$file_upload_obj = ( new FileUploadField() );

		$encrypt_url = Crypto::encrypt( $url );
		$url_encoded = rawurlencode( $encrypt_url );

		return $file_upload_obj->get_file_url(
			$local_file,
			[
				'wpforms_google_drive_url' => $url_encoded,
			]
		);
	}
}
