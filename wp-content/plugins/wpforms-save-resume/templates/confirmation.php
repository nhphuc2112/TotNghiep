<?php
/**
 * Confirmation message template.
 *
 * @since 1.8.0
 *
 * @var array  $form_data            Form data.
 * @var string $confirmation_callout Confirmation callout.
 * @var string $confirmation         Confirmation message.
 * @var string $action               Form action.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wpforms-save-resume-confirmation" id="wpforms-save-resume-confirmation-<?php echo absint( $form_data['id'] ); ?>" style="display: none">

	<?php

	/**
	 * Fires before confirmation block.
	 *
	 * @since 1.4.0
	 *
	 * @param array $form_data Form data.
	 */
	do_action( 'wpforms_save_resume_frontend_display_confirmation_before', $form_data );

	if ( ! empty( $confirmation_callout ) ) {
		?>
		<div class="wpforms-confirmation-container-full">
			<?php echo wp_kses_post( wpautop( $confirmation_callout ) ); ?>
		</div>
		<?php
	}

	/**
	 * Fires after confirmation block.
	 *
	 * @since 1.4.0
	 *
	 * @param array $form_data Form data.
	 */
	do_action( 'wpforms_save_resume_frontend_display_confirmation_after', $form_data );
	?>

	<div class="message">
		<?php echo wp_kses_post( wpautop( $confirmation ) ); ?>
	</div>

	<div class="wpforms-save-resume-actions">
		<?php if ( ! empty( $form_data['settings']['save_resume_enable_resume_link'] ) ) : ?>
			<div class="wpforms-field">
				<label class="wpforms-field-label wpforms-save-resume-label">
					<?php esc_html_e( 'Copy Link', 'wpforms-save-resume' ); ?>
				</label>
				<div class="wpforms-save-resume-shortcode-container">
					<input type="text" class="wpforms-save-resume-shortcode" value="" disabled />
					<span class="wpforms-save-resume-shortcode-copy" title="<?php esc_attr_e( 'Copy resume link to clipboard', 'wpforms-save-resume' ); ?>">
							<span class="copy-icon"></span>
						</span>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $form_data['settings']['save_resume_enable_email_notification'] ) ) : ?>
			<form class="wpforms-validate wpforms-form wpforms-save-resume-email-notification" method="post" action="<?php echo esc_url( $action ); ?>" data-token="<?php echo esc_attr( wpforms()->obj( 'token' )->get( true ) ); ?>">
				<div class="wpforms-field wpforms-field-email">
					<label class="wpforms-field-label wpforms-save-resume-label">
						<?php esc_html_e( 'Email', 'wpforms-save-resume' ); ?>
						<span class="wpforms-required-label">*</span>
					</label>
					<input type="email" name="wpforms[save_resume_email]" required>
				</div>
				<div class="wpforms-submit-container">
					<?php wp_nonce_field( 'wpforms_save_resume_process_entries' ); ?>
					<input type="hidden" name="wpforms[form_id]" value="<?php echo esc_attr( $form_data['id'] ); ?>">
					<input type="hidden" name="wpforms[entry_id]" class="wpforms-save-resume-entry-id" value="">
					<button type="submit" name="wpforms[save-resume]" class="wpforms-submit" value="wpforms-submit" disabled>
						<?php esc_html_e( 'Send Link', 'wpforms-save-resume' ); ?>
					</button>
				</div>
			</form>
		<?php endif; ?>
	</div>
</div>
