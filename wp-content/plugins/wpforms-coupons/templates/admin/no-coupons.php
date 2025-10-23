<?php
/**
 * Empty coupons table HTML template.
 *
 * @since 1.0.0
 *
 * @var string $add_coupon_link Create a Coupon link.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-admin-empty-state-container wpforms-admin-no-payments wpforms-coupons-no-coupons">
	<h2 class="waving-hand-emoji"><?php esc_html_e( 'Hi there!', 'wpforms-coupons' ); ?></h2>
	<h4><?php esc_html_e( 'It looks like you haven\'t created any coupons yet.', 'wpforms-coupons' ); ?></h4>
	<p><?php esc_html_e( 'Allow your customers to enter a custom coupon code and receive discounts on payment forms.', 'wpforms-coupons' ); ?></p>
	<img src="<?php echo esc_url( WPFORMS_COUPONS_URL . 'assets/images/no-coupons.svg' ); ?>" alt="<?php esc_html_e( 'No coupons', 'wpforms-coupons' ); ?>">

	<a href="<?php echo esc_url( $add_coupon_link ); ?>" class="wpforms-btn wpforms-btn-lg wpforms-btn-orange">
		<?php esc_html_e( 'Create a Coupon', 'wpforms-coupons' ); ?>
	</a>

	<p class="wpforms-admin-no-forms-footer">
		<?php
		printf(
			wp_kses( /* translators: %s - URL to the documentation article. */
				__( 'Need some help? Check out our <a href="%s" rel="noopener noreferrer" target="_blank">comprehensive guide.</a>', 'wpforms-coupons' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/coupons-addon/',
					'Coupons Overview',
					'Coupon Documentation'
				)
			)
		);
		?>
	</p>
</div>
