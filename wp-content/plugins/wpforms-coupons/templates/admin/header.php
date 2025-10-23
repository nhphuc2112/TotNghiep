<?php
/**
 * Add/edit coupon template.
 *
 * @since 1.0.0
 *
 * @var string $btn_url  Button URL.
 * @var string $btn_text Button text.
 * @var string $icon     SVG markup for the button icon.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<a href="<?php echo esc_url( $btn_url ); ?>" class="page-title-action wpforms-btn wpforms-btn-orange wpforms-coupons-back-to-overview">
	<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<span class="page-title-action-text"><?php echo esc_html( $btn_text ); ?></span>
</a>
