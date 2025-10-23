<?php

namespace WPFormsGoogleDrive\Provider\Settings;

use WP_Post;
use WPFormsGoogleDrive\Plugin;
use WPFormsGoogleDrive\Api\Client;
use WPForms\Providers\Provider\Status;
use WPForms\Providers\Provider\Settings\FormBuilder as FormBuilderAbstract;

/**
 * Class FormBuilder handles functionality in the Form Builder.
 *
 * @since 1.0.0
 */
class FormBuilder extends FormBuilderAbstract {

	/**
	 * Register all hooks (actions and filters).
	 *
	 * @since 1.0.0
	 */
	protected function init_hooks(): void {

		parent::init_hooks();

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks(): void {

		$slug = $this->core->slug;

		add_filter( "wpforms_providers_settings_builder_ajax_connections_get_$slug", [ $this, 'ajax_connections_get' ] );
		add_filter( "wpforms_providers_settings_builder_ajax_access_token_get_$slug", [ $this, 'ajax_access_token_get' ] );
		add_filter( 'wpforms_save_form_args', [ $this, 'save_form' ], 11, 3 );
		add_filter( 'wpforms_builder_settings_sections', [ $this, 'panel_sidebar' ], $this->core::PRIORITY, 2 );
		add_action( 'wpforms_form_settings_panel_content', [ $this, 'display_content' ], $this->core::PRIORITY );
		add_filter( 'wpforms_builder_strings', [ $this, 'builder_strings' ], 10, 2 );
		add_filter( 'wpforms_builder_save_form_response_data', [ $this, 'refresh_connections' ], 10, 3 );
		add_filter( "wpforms_providers_provider_settings_formbuilder_display_content_default_screen_{$slug}", [ $this, 'update_default_screen' ] );
	}

	/**
	 * Pre-process provider data before saving it in form_data when editing a form.
	 *
	 * @since 1.0.0
	 *
	 * @param array|mixed $form Form array, usable with wp_update_post.
	 * @param array       $data Data retrieved from $_POST and processed.
	 * @param array       $args Custom data aren't intended to be saved.
	 *
	 * @return array
	 * @noinspection NullPointerExceptionInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function save_form( $form, array $data, array $args ): array {

		$form = (array) $form;

		$form_data = json_decode( stripslashes( $form['post_content'] ), true );

		if ( ! empty( $form_data['providers'][ Plugin::SLUG ] ) ) {
			$modified_form_data = $this->modify_form_data( $form_data );

			if ( ! empty( $modified_form_data ) ) {
				$form['post_content'] = wpforms_encode( $modified_form_data );

				return $form;
			}
		}

		/*
		 * This part works when modification is locked or the current filter was called on NOT the Providers panel.
		 * Then we need to restore provider connections from the previous form content.
		 */

		// Get a "previous" form content (current content is still not saved).
		$prev_form = ! empty( $data['id'] ) ? wpforms()->obj( 'form' )->get( $data['id'], [ 'content_only' => true ] ) : [];

		if ( ! empty( $prev_form['providers'][ Plugin::SLUG ] ) ) {
			$provider = $prev_form['providers'][ Plugin::SLUG ];

			if ( ! isset( $form_data['providers'] ) ) {
				$form_data = array_merge( $form_data, [ 'providers' => [] ] );
			}

			$form_data['providers'] = array_merge( (array) $form_data['providers'], [ Plugin::SLUG => $provider ] );
			$form['post_content']   = wpforms_encode( $form_data );
		}

		return $form;
	}

	/**
	 * Prepare modifications for the form content if it's not locked.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form content.
	 *
	 * @return array
	 */
	private function modify_form_data( array $form_data ): array {

		$lock = '__lock__';

		/**
		 * The Connection is locked.
		 * Why? A user clicked the "Save" button when one of the AJAX requests
		 * for retrieving data from the API was in progress or failed.
		 */
		if (
			isset( $form_data['providers'][ Plugin::SLUG ][ $lock ] ) &&
			absint( $form_data['providers'][ Plugin::SLUG ][ $lock ] ) === 1
		) {
			return [];
		}

		// Modify content as we need, done by reference.
		foreach ( $form_data['providers'][ Plugin::SLUG ] as $connection_id => &$connection_data ) {
			if ( $connection_id === $lock ) {
				unset( $form_data['providers'][ Plugin::SLUG ][ $lock ] );
				continue;
 			}

			$this->sanitize_connection( $connection_data );

			if ( ! empty( $connection_data['folder_type'] ) && $connection_data['folder_type'] === 'new' ) {
				$this->create_folder( $connection_data, $form_data );
			}
		}

		return $form_data;
	}

	/**
	 * Sanitize connection.
	 *
	 * @since 1.0.0
	 *
	 * @param array $connection_data Connection data.
	 */
	private function sanitize_connection( array &$connection_data ): void {

		foreach ( [ 'id', 'name', 'account_id', 'folder_type', 'folder_id', 'folder_name' ] as $key ) {
			$connection_data[ $key ] = isset( $connection_data[ $key ] ) ? sanitize_text_field( $connection_data[ $key ] ) : '';
		}

		$this->sanitize_connection_fields( $connection_data );
		$this->sanitize_connection_conditionals( $connection_data );
	}

	/**
	 * Sanitize connection upload fields.
	 * The lists' field is multi-select, and in `$connection` are saved only the last field value.
	 * So, we should receive data from super global $_POST and receive all submitted fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $connection Connection data.
	 */
	private function sanitize_connection_fields( array &$connection ): void {

		// The nonce checked in the `wpforms_save_form` function.
		// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$form_post     = ! empty( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : [];
		$connection_id = $connection['id'];

		$connection['fields'] = wpforms_chain( $form_post )
			->map(
				static function ( $post_pair ) use ( $connection_id ) {

					$provider_slug = Plugin::SLUG;

					if (
						empty( $post_pair['name'] ) ||
						$post_pair['name'] !== "providers[$provider_slug][$connection_id][fields][]"
					) {
						return '';
					}

					return absint( $post_pair['value'] );
				}
			)
			->array_filter()
			->array_values()
			->value();
	}

	/**
	 * Create a Google Drive folder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $connection_data Connection data.
	 * @param array $form_data       Form data and settings.
	 *
	 * @return void
	 */
	public function create_folder( array &$connection_data, array $form_data ): void {

		if ( empty( $connection_data['account_id'] ) ) {
			return;
		}

		$account = wpforms_google_drive()->get( 'account' );

		if ( ! $account ) {
			return;
		}

		$client = $account->get_client_by_id( $connection_data['account_id'] );

		if ( ! $client ) {
			return;
		}

		$folder_id = $client->create_folder( $this->prepare_folder_name( $connection_data, $form_data ) );

		if ( ! $folder_id ) {
			return;
		}

		$connection_data['folder_type'] = 'existing';
		$connection_data['folder_id']   = $folder_id;

		unset( $connection_data['folder_name'] );
	}

	/**
	 * Prepare a folder name.
	 *
	 * @since 1.0.0
	 *
	 * @param array $connection_data Connection data.
	 * @param array $form_data       Form data and settings.
	 *
	 * @return string
	 */
	private function prepare_folder_name( array $connection_data, array $form_data ): string {

		if ( isset( $connection_data['folder_name'] ) && ! wpforms_is_empty_string( $connection_data['folder_name'] ) ) {
			return $connection_data['folder_name'];
		}

		return $form_data['settings']['form_title'] ?? 'WPForms Folder';
	}

	/**
	 * Retrieve saved provider connections data.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_connections_data(): array {

		return (array) ( $this->form_data['providers'][ Plugin::SLUG ] ?? [] );
	}

	/**
	 * Refresh the builder to update a new spreadsheet or a new sheet.
	 *
	 * @since 1.0.0
	 *
	 * @param array|mixed $response_data The data to be sent in the response.
	 * @param int|mixed   $form_id       Form ID.
	 * @param array       $data          Form data.
	 *
	 * @return array
	 */
	public function refresh_connections( $response_data, $form_id, array $data ): array {

		$response_data = (array) $response_data;
		$form_id       = (int) $form_id;

		$form_obj = wpforms()->obj( 'form' );

		if ( empty( $data['providers'][ $this->core->slug ] ) || ! $form_obj ) {
			return $response_data;
		}

		$this->form_data = $form_obj->get( $form_id, [ 'content_only' => true ] );

		$response_data[ $this->core->slug ] = $this->ajax_connections_get();

		return $response_data;
	}

	/**
	 * Get the list of all saved connections.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function ajax_connections_get(): array {

		$account_obj = wpforms_google_drive()->get( 'account' );
		$connections = [
			'accounts'     => $account_obj ? $account_obj->get_all() : [],
			'connections'  => array_reverse( $this->get_connections_data(), true ),
			'conditionals' => [],
		];

		foreach ( $connections['connections'] as $key => $connection ) {
			if ( empty( $connection['id'] ) ) {
				unset( $connections['connections'][ $key ] );
				continue;
			}

			// This will either return an empty placeholder or complete set of rules, as a DOM.
			$connections['conditionals'][ $connection['id'] ] = wpforms_conditional_logic()
				->builder_block(
					[
						'form'       => $this->form_data,
						'type'       => 'panel',
						'parent'     => 'providers',
						'panel'      => Plugin::SLUG,
						'subsection' => $connection['id'],
						'reference'  => __( 'Marketing provider connection', 'wpforms-google-drive' ),
					],
					false
				);
		}

		return $connections;
	}

	/**
	 * Retrieve Google Drive access token data.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function ajax_access_token_get(): array {

		// Nonce checked in the parent::process_ajax method.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['account_id'] ) ) {
			return [];
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$account_id = sanitize_text_field( wp_unslash( $_POST['account_id'] ) );
		$account    = wpforms_google_drive()->get( 'account' );

		if ( ! $account ) {
			return [];
		}

		$client = $account->get_client_by_id( $account_id );

		if ( ! $client ) {
			return [];
		}

		return [
			'access_token' => $client->get_access_token(),
		];
	}

	/**
	 * Enqueue JavaScript and CSS files.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets(): void {

		parent::enqueue_assets();

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-google-drive-builder',
			WPFORMS_GOOGLE_DRIVE_URL . "assets/css/builder$min.css",
			[],
			WPFORMS_GOOGLE_DRIVE_VERSION
		);

		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
		wp_enqueue_script(
			'wpforms-google-drive-api',
			'https://apis.google.com/js/api.js',
			[],
			'',
			true
		);
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion

		wp_enqueue_script(
			'wpforms-google-drive-builder',
			WPFORMS_GOOGLE_DRIVE_URL . "assets/js/builder$min.js",
			[ 'wpforms-google-drive-api', 'wpforms-admin-builder-providers', 'choicesjs' ],
			WPFORMS_GOOGLE_DRIVE_VERSION,
			true
		);
	}

	/**
	 * Add own localized strings to the Builder.
	 *
	 * @since        1.0.0
	 *
	 * @param array|mixed $strings Localized strings.
	 * @param WP_Post     $form    Current form.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function builder_strings( $strings, $form ): array {

		$strings = (array) $strings;

		$strings['google_drive'] = [
			'auth_url'                           => Client::get_auth_url(
				add_query_arg(
					[
						'page'    => 'wpforms-builder',
						'view'    => 'settings',
						'form_id' => $form->ID ?? 0,
						'section' => Plugin::SLUG,
					],
					admin_url( 'admin.php' )
				)
			),
			'delete_local_files_prompt'          => esc_html__( 'The Media Library file deletion setting affects all connections in this form. Are you sure you want to continue?', 'wpforms-google-drive' ),
			'delete_local_files_confirm'         => esc_html__( 'Yes, Continue', 'wpforms-google-drive' ),
			'dropbox_compatibility_message'      => esc_html__( "Dropbox cannot be enabled because it's incompatible with Google Drive, which is already connected to this form. Remove the Google Drive connection to proceed.", 'wpforms-google-drive' ),
			'google_drive_compatibility_message' => esc_html__( "Google Drive cannot be enabled because it's incompatible with Dropbox, which is already connected to this form. Remove the Dropbox connection to proceed.", 'wpforms-google-drive' ),
			/**
			 * Display shared drives in Google Picker.
			 *
			 * @since 1.1.0
			 *
			 * @param bool    $is_enabled Display Shared Drives directories.
			 * @param WP_Post $form       Current form.
			 */
			'enabled_shared_drives'              => (bool) apply_filters( 'wpforms_google_drive_provider_settings_form_builder_enabled_shared_drives', false, $form ),
		];

		return $strings;
	}

	/**
	 * Use this method to register own templates for form builder.
	 * Make sure that you have `tmpl-` in the template name in `<script id="tmpl-*">`.
	 *
	 * @since 1.0.0
	 */
	public function builder_custom_templates(): void {

		$templates = [
			'connection',
			'error',
		];

		foreach ( $templates as $template ) {
			$template_name = ucwords( str_replace( '-', ' ', $template ) );
			$script_id     = 'tmpl-wpforms-' . esc_attr( Plugin::SLUG ) . '-builder-content-connection';

			if ( $template !== 'connection' ) {
				$script_id .= '-' . $template;
			}
			?>
			<!-- Single Google Drive connection block: <?php echo esc_attr( $template_name ); ?>. -->
			<script type="text/html" id="<?php echo esc_attr( $script_id ); ?>">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wpforms_render( WPFORMS_GOOGLE_DRIVE_PATH . 'templates/builder/' . $template );
				?>
			</script>
			<?php
		}
	}

	/**
	 * Section content header.
	 *
	 * @since 1.0.0
	 */
	protected function display_content_header(): void {

		$provider_status = Status::init( $this->core->slug );
		$is_configured   = $provider_status->is_configured();

		if ( $is_configured ) {
			parent::display_content_header();

			return;
		}

		?>
		<div class="wpforms-builder-provider-title wpforms-panel-content-section-title">
			<?php echo esc_html( $this->core->name ); ?>

			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wpforms_render( WPFORMS_GOOGLE_DRIVE_PATH . 'templates/sign-in' );
			?>
		</div>
		<?php
	}

	/**
	 * Add a new item `Google Drive` to the panel sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @param array $sections  Registered sections.
	 * @param array $form_data Contains an array of the form data (post_content).
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function panel_sidebar( $sections, $form_data ): array {

		$sections = (array) $sections;

		$sections[ Plugin::SLUG ] = esc_html__( 'Google Drive', 'wpforms-google-drive' );

		return $sections;
	}

	/**
	 * Change default screen content.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	public function update_default_screen(): string {

		$is_configured = Status::init( $this->core->slug )->is_configured();

		$title = $is_configured ?
			esc_html__( 'Easily send file uploads to a folder of your choosing on Google Drive', 'wpforms-google-drive' ) :
			esc_html__( 'Connect your Google Account to start working with Google Drive', 'wpforms-google-drive' );

		return sprintf(
			'<h4>%1$s</h4><p><a href="%2$s" rel="noopener noreferrer" target="_blank">%3$s</a></p>',
			$title,
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/google-drive-addon/',
					'Marketing Integrations',
					'Google Drive Documentation'
				)
			),
			esc_html__( 'Learn how to get started with Google Drive', 'wpforms-google-drive' )
		);
	}

	/**
	 * Determine if deleting local files feature is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	public static function is_enabled_delete_local_files( array $form_data ): bool {

		if ( empty( $form_data['providers'][ Plugin::SLUG ] ) ) {
			return false;
		}

		$connection = reset( $form_data['providers'][ Plugin::SLUG ] );

		return ! empty( $connection['delete_local_files'] );
	}
}
