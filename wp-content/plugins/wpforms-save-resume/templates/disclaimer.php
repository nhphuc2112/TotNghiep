<?php
/**
 * Disclaimer message template.
 *
 * @since 1.8.0}
 *
 * @var array  $form_data Form data.
 * @var string $message   Message.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wpforms-save-resume-disclaimer" id="wpforms-save-resume-disclaimer-<?php echo absint( $form_data['id'] ); ?>" style="display: none">
	<?php

	/**
	 * Fires before disclaimer block.
	 *
	 * @since 1.4.0
	 *
	 * @param array $form_data Form data.
	 */
	do_action( 'wpforms_save_resume_frontend_display_disclaimer_before', $form_data );
	?>
	<div class="message">
		<?php echo wp_kses_post( wpautop( $message ) ); ?>
	</div>

	<div class="wpforms-form">
		<button type="submit" class="wpforms-save-resume-disclaimer-continue wpforms-submit">
			<?php esc_html_e( 'Continue', 'wpforms-save-resume' ); ?>
		</button>
		<a href="#" class="wpforms-save-resume-disclaimer-back">
			<span><?php esc_html_e( 'Go Back', 'wpforms-save-resume' ); ?></span>
		</a>
	</div>

	<?php

	/**
	 * Fires after disclaimer block.
	 *
	 * @since 1.4.0
	 *
	 * @param array $form_data Form data.
	 */
	do_action( 'wpforms_save_resume_frontend_display_disclaimer_after', $form_data );
	?>
</div>
