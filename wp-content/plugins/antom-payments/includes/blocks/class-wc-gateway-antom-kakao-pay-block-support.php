<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Antom_Kakao_Pay Payments Blocks integration
 *
 * @since 1.0.0
 */
final class WC_Gateway_Antom_Kakao_Pay_Block_Support extends WC_Gateway_Antom_Block_Support {


	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'antom_kakao_pay';
}
