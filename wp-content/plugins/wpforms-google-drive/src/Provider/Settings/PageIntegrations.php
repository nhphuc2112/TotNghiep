<?php

namespace WPFormsGoogleDrive\Provider\Settings;

use WPFormsGoogleDrive\Plugin;
use WPFormsGoogleDrive\Api\Client;
use WPForms\Providers\Provider\Core;
use WPForms\Providers\Provider\Settings\PageIntegrations as PageIntegrationsAbstract;

/**
 * Class PageIntegrations handles functionality on the Settings > Integrations page.
 *
 * @since 1.0.0
 */
class PageIntegrations extends PageIntegrationsAbstract {

	/**
	 * Any new connection should be added.
	 * So display the content of that.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	protected function display_add_new(): void {

		?>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render( WPFORMS_GOOGLE_DRIVE_PATH . 'templates/sign-in' );
		?>
		<br>
		<p>
			<?php
			printf(
				wp_kses( /* translators: %1$s - documentation URL. */
					__(
						'If you need help connecting WPForms to Google Drive, <a href="%1$s" rel="noopener" target="_blank">read our documentation</a>.',
						'wpforms-google-drive'
					),
					[
						'a' => [
							'href'   => [],
							'rel'    => [],
							'target' => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/google-drive-addon/', 'Settings - Integration', 'Google Drive Documentation' ) )
			);
			?>
		</p>
		<?php
	}
}
