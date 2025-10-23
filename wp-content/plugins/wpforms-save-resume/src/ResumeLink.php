<?php

namespace WPFormsSaveResume;

use WPForms\Emails\Helpers as EmailHelpers;

/**
 * The ResumeLink Smart Tag class.
 *
 * @since 1.0.0
 */
class ResumeLink {

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
	public function hooks() {

		add_filter( 'wpforms_smart_tags', [ $this, 'register_tag' ] );
		add_filter( 'wpforms_process_smart_tags', [ $this, 'resume_link' ], 10, 4 );
	}

	/**
	 * Register the new {resume_link} smart tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tags List of tags.
	 *
	 * @return array $tags List of tags.
	 */
	public function register_tag( $tags ) {

		$tags['resume_link'] = esc_html__( 'Resume Link', 'wpforms-save-resume' );

		return $tags;
	}

	/**
	 * Check for {resume_link} Smart Tag inside email messages and replace it.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message   Message.
	 * @param array  $form_data Form data.
	 * @param array  $fields    List of fields.
	 * @param string $entry_id  Entry ID.
	 *
	 * @return string
	 */
	public function resume_link( $message, $form_data, $fields = [], $entry_id = '' ) {

		// SmartTag to look for.
		$smart_tag = '{resume_link}';

		// Check to see if SmartTag is in the email notification message.
		if ( strpos( $message, $smart_tag ) === false ) {
			return $message;
		}

		$hash_url = Entry::get_hash_url_by_entry( $entry_id );

		// If the hash URL is empty, replace the SmartTag with an empty string.
		if ( empty( $hash_url ) ) {
			return str_replace( $smart_tag, '', $message );
		}

		$link = esc_url_raw( $hash_url );

		// If it's a plain text template, replace the SmartTag with a link.
		if ( EmailHelpers::is_plain_text_template() ) {
			return str_replace( $smart_tag, $link, $message );
		}

		$is_legacy_template = EmailHelpers::is_legacy_html_template();
		$style_overrides    = EmailHelpers::get_current_template_style_overrides();

		// Otherwise, replace the SmartTag with a button.
		$link = sprintf(
			'<div style="text-align:center"><a href="%1$s" class="inline button-link" style="text-decoration:none;padding:7px 15px;border-radius:3px;background-color:%2$s;border:1px solid %2$s;color:%3$s;">%4$s</a></div>',
			esc_url( $hash_url ),
			$is_legacy_template ? '#e27730' : sanitize_hex_color( $style_overrides['email_links_color'] ),
			$is_legacy_template ? '#ffffff' : sanitize_hex_color( $style_overrides['email_body_color'] ),
			esc_html__( 'Resume Form Submission', 'wpforms-save-resume' )
		);

		return str_replace( $smart_tag, $link, $message );
	}
}
