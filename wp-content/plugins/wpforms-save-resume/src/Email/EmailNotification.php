<?php

namespace WPFormsSaveResume\Email;

use WPForms\Emails\Mailer;
use WPForms\Emails\Helpers;

/**
 * The Notification class.
 *
 * @since 1.0.0
 */
class EmailNotification {

	/**
	 * Send Email.
	 *
	 * @since 1.0.0
	 *
	 * @param array $email Email data to send.
	 *
	 * @return bool|void
	 */
	public function send( $email ) {

		// Extract the email message.
		$message = $email['message'];

		// If it's not a plain text template, replace line breaks.
		if ( ! Helpers::is_plain_text_template() ) {
			// Replace line breaks with <br/> tags.
			$message = str_replace( "\r\n", '<br/>', $message );
			// Wrap the message in a table row.
			$message = sprintf( '<tr><td class="field-name field-value">%1$s</td></tr>', $message );
		}

		// Create an arguments array for the template.
		$args = [
			'body' => [
				'message' => $message,
			],
		];

		/**
		 * Filter to customize the email template name independently of the global setting.
		 *
		 * @since 1.7.0
		 *
		 * @param string $template_name The template name to be used.
		 */
		$template_name  = apply_filters( 'wpforms_save_resume_email_notification_template_name', Helpers::get_current_template_name() );
		$template_class = Helpers::get_current_template_class( $template_name, __NAMESPACE__ . '\Templates\SaveResume' );
		$template       = ( new $template_class() )->set_args( $args );

		/**
		 * This filter allows overwriting email template.
		 *
		 * @since 1.0.0
		 *
		 * @param object $template Template object.
		 * @param array  $email    Email data.
		 */
		$template = apply_filters( 'wpforms_save_resume_email_emailnotification_send_template', $template, $email );

		$content = $template->get();

		if ( ! $content ) {
			return;
		}

		return ( new Mailer() )
			->template( $template )
			->subject( $email['subject'] )
			->to_email( $email['address'] )
			->send();
	}
}
