<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Antom_Tng Payments Blocks integration
 *
 * @since 1.0.0
 */
final class WC_Gateway_Antom_Tng_Block_Support extends WC_Gateway_Antom_Block_Support {


	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'antom_tng';
}
