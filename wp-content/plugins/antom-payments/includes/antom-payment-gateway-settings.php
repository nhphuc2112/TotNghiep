<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function antom_get_settlement_currencies() {
	return array(
		'USD',
		'SGD',
		'AUD',
		'HKD',
	);
}

function antom_get_payment_methods() {
	$dir = __DIR__;

	$payment_gateways = array(
		array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-alipay-cn.php',
			'gateway_class'        => 'WC_Gateway_Antom_Alipay_CN',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-alipay-cn-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_Alipay_CN_Block_Support',
			'slug'                 => 'antom_alipay_cn',
			'menu_title'           => __( 'Alipay CN', 'antom-payments' ) . ' ' . __( 'Settings', 'antom-payments' ),
			'pay_name'             => __( 'Alipay CN by Antom', 'antom-payments' ),
			'default_display_name' => 'Alipay CN',
			'payment_method_type'  => 'ALIPAY_CN',
			'method_title'         => __( 'Alipay CN by Antom', 'antom-payments' ),
			'support_currencies'   => array( 'CHF', 'AED', 'HKD', 'EUR', 'MYR', 'CAD', 'USD', 'CNY', 'THB', 'AUD', 'SGD', 'KRW', 'JPY', 'PLN', 'GBP', 'NZD', 'TRY' ),
			'icon'                 => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/Alipay-A+.svg',
		),
		array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-alipay-hk.php',
			'gateway_class'        => 'WC_Gateway_Antom_Alipay_HK',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-alipay-hk-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_Alipay_HK_Block_Support',
			'slug'                 => 'antom_alipay_hk',
			'default_display_name' => 'Alipay HK',
			'menu_title'           => __( 'Alipay HK', 'antom-payments' ) . ' ' . __( 'Settings', 'antom-payments' ),
			'pay_name'             => __( 'Alipay HK by Antom', 'antom-payments' ),
			'payment_method_type'  => 'ALIPAY_HK',
			'support_currencies'   => array( 'AED', 'HKD', 'CHF', 'QAR', 'EUR', 'DKK', 'CAD', 'USD', 'CNY', 'THB', 'AUD', 'SGD', 'JPY', 'PLN', 'GBP', 'NZD', 'PHP', 'TRY' ),
			'icon'                 => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/AlipayHK-A+.svg',
		),
		array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-true-money.php',
			'gateway_class'        => 'WC_Gateway_Antom_True_Money',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-true-money-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_True_Money_Block_Support',
			'slug'                 => 'antom_true_money',
			'default_display_name' => 'True Money',
			'menu_title'           => __( 'True Money', 'antom-payments' ) . ' ' . __( 'Settings', 'antom-payments' ),
			'pay_name'             => __( 'True Money by Antom', 'antom-payments' ),
			'payment_method_type'  => 'TRUE_MONEY',
			'support_currencies'   => array( 'AED', 'CHF', 'HKD', 'QAR', 'EUR', 'DKK', 'USD', 'CAD', 'CNY', 'THB', 'AUD', 'SGD', 'JPY', 'PLN', 'GBP', 'NZD', 'PHP', 'TRY' ),
			'icon'                 => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/TrueMoney Wallet-A+.svg',
		),
		array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-tng.php',
			'gateway_class'        => 'WC_Gateway_Antom_Tng',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-tng-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_Tng_Block_Support',
			'slug'                 => 'antom_tng',
			'default_display_name' => 'Tng',
			'menu_title'           => __( 'Tng', 'antom-payments' ) . ' ' . __( 'Settings', 'antom-payments' ),
			'pay_name'             => __( 'Tng by Antom', 'antom-payments' ),
			'payment_method_type'  => 'TNG',
			'support_currencies'   => array( 'AED', 'CHF', 'HKD', 'QAR', 'EUR', 'DKK', 'MYR', 'USD', 'CAD', 'CNY', 'THB', 'AUD', 'SGD', 'JPY', 'PLN', 'GBP', 'NZD', 'PHP', 'TRY' ),
			'icon'                 => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/Touch \'n Go eWallet-A+.svg',
		),
		array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-gcash.php',
			'gateway_class'        => 'WC_Gateway_Antom_GCash',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-gcash-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_GCash_Block_Support',
			'slug'                 => 'antom_gcash',
			'default_display_name' => 'GCash',
			'menu_title'           => __( 'GCash', 'antom-payments' ) . ' ' . __( 'Settings', 'antom-payments' ),
			'pay_name'             => __( 'GCash by Antom', 'antom-payments' ),
			'payment_method_type'  => 'GCASH',
			'support_currencies'   => array( 'AED', 'CHF', 'HKD', 'QAR', 'EUR', 'DKK', 'USD', 'CAD', 'CNY', 'THB', 'AUD', 'SGD', 'JPY', 'PLN', 'GBP', 'NZD', 'PHP', 'TRY' ),
			'icon'                 => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/GCash-A+.svg',
		),
		array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-dana.php',
			'gateway_class'        => 'WC_Gateway_Antom_Dana',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-dana-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_Dana_Block_Support',
			'slug'                 => 'antom_dana',
			'default_display_name' => 'Dana',
			'menu_title'           => __( 'Dana', 'antom-payments' ) . ' ' . __( 'Settings', 'antom-payments' ),
			'pay_name'             => __( 'Dana by Antom', 'antom-payments' ),
			'payment_method_type'  => 'DANA',
			'support_currencies'   => array( 'AED', 'CHF', 'HKD', 'QAR', 'EUR', 'DKK', 'USD', 'CAD', 'CNY', 'THB', 'AUD', 'SGD', 'JPY', 'PLN', 'GBP', 'IDR', 'NZD', 'PHP', 'TRY' ),
			'icon'                 => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/DANA-A+.svg',
		),
		array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-kakao-pay.php',
			'gateway_class'        => 'WC_Gateway_Antom_Kakao_Pay',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-kakao-pay-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_Kakao_Pay_Block_Support',
			'slug'                 => 'antom_kakao_pay',
			'menu_title'           => __( 'Kakao Pay', 'antom-payments' ) . ' ' . __( 'Settings', 'antom-payments' ),
			'pay_name'             => __( 'Kakao Pay by Antom', 'antom-payments' ),
			'payment_method_type'  => 'KAKAOPAY',
			'default_display_name' => 'Kakao Pay',
			'support_currencies'   => array( 'CHF', 'HKD', 'EUR', 'DKK', 'USD', 'CAD', 'CNY', 'THB', 'AUD', 'KRW', 'SGD', 'JPY', 'PLN', 'GBP', 'NZD' ),
			'icon'                 => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/Kakao Pay-A+.svg',
		),
		array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-toss-pay.php',
			'gateway_class'        => 'WC_Gateway_Antom_Toss_Pay',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-toss-pay-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_Toss_Pay_Block_Support',
			'slug'                 => 'antom_toss_pay',
			'menu_title'           => __( 'Toss Pay', 'antom-payments' ) . ' ' . __( 'Settings', 'antom-payments' ),
			'pay_name'             => __( 'Toss Pay by Antom', 'antom-payments' ),
			'payment_method_type'  => 'TOSSPAY',
			'default_display_name' => 'Toss Pay',
			'support_currencies'   => array( 'HKD', 'AUD', 'SGD', 'KRW', 'JPY', 'EUR', 'USD', 'CAD', 'THB' ),
			'icon'                 => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/Toss Pay.svg',
		),
		array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-naver-pay.php',
			'gateway_class'        => 'WC_Gateway_Antom_Naver_Pay',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-naver-pay-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_Naver_Pay_Block_Support',
			'slug'                 => 'antom_naver_pay',
			'default_display_name' => 'Naver Pay',
			'menu_title'           => __( 'Naver Pay', 'antom-payments' ) . ' ' . __( 'Settings', 'antom-payments' ),
			'pay_name'             => __( 'Naver Pay by Antom', 'antom-payments' ),
			'payment_method_type'  => 'NAVERPAY',
			'support_currencies'   => array( 'KRW' ),
			'icon'                 => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/Naverpay.svg',
		),
	);

	if ( Antom_Payment_Gateways_Statement::ANTOM_ALLOW_CARD_PAYMENT_GATEWAY ) {
		$payment_gateways[] = array(
			'gateway_file'         => $dir . '/gateways/class-wc-gateway-antom-card.php',
			'gateway_class'        => 'WC_Gateway_Antom_Card',
			'block_file'           => $dir . '/blocks/class-wc-gateway-antom-card-block-support.php',
			'block_support_class'  => 'WC_Gateway_Antom_Card_Block_Support',
			'slug'                 => 'antom_card',
			'default_display_name' => 'Visa/Mastercard',
			'menu_title'           => __( 'Visa/Mastercard', 'antom-payments' ) . ' ' . __(
				'Settings',
				'antom-payments'
			),
			'pay_name'             => __( 'Visa/Mastercard by Antom', 'antom-payments' ),
			'payment_method_type'  => 'CARD',
			'support_currencies'   => array( 'HKD', 'CHF', 'AUD', 'SGD', 'JPY', 'EUR', 'GBP', 'USD', 'CAD', 'NZD' ),
			'icon'                 => array(
				ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/VISA.svg',
				ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/MasterCard.svg',
			),
		);
	}

	/**
	 * With this hook,we can manage our Payment Gateway settings
	 *
	 * @since 1.0.0
	 */
	return apply_filters( 'antom_payment_gateway_settings', $payment_gateways );
}

function antom_get_core_setting_form_fields() {
	$currency_settings = antom_get_settlement_currencies();
	$currencies        = array(
		'' => 'Select Settlement Currency',
	);
	foreach ( $currency_settings as $currency_setting ) {
		$currencies[ $currency_setting ] = $currency_setting;
	}
	$live_client_id_url  = 'https://global.alipay.com/docs/ac/plugins/woocommerce#TGNcn';
	$test_client_id_url  = 'https://global.alipay.com/docs/ac/plugins/woocommerce#OPevG';
	$core_setting_fields = array(
		'clientid'         => array(
			'title'         => __( 'Live Mode Antom ClientId', 'antom-payments' ),
			'type'          => 'text',
			'warning'       => Antom_Payment_Gateways_Statement::ANTOM_LIVE_MODE_CAN_NOT_USE_TEST_MODE_CLIENT_ID,
			'warning_class' => 'warning-text hide live-mode-cannot-use-test-client-id',
			'description'   => sprintf(
				/* translators: %s: the site url which live client id, live client public key and live private key to get*/
				__(
					'you can find your Live mode clientId、Private、Public key in <a target="_blank" href="%s">antom</a>',
					'antom-payments'
				),
				$live_client_id_url
			),
		),
		'public_key'       => array(
			'title' => __( 'Live Mode Antom Public Key', 'antom-payments' ),
			'type'  => 'textarea',
		),
		'private_key'      => array(
			'title' => __( 'Live Mode Your Private Key', 'antom-payments' ),
			'type'  => 'password',
		),
		'prod_currency'    => array(
			'title'         => __( 'Settlement Currency', 'antom-payments' ),
			'type'          => 'select',
			'options'       => $currencies,
			'class'         => 'antm-prod-currency',
			'warning'       => Antom_Payment_Gateways_Statement::ANTOM_LIVE_MODE_SETTLEMENT_CURRENCY_WARNING,
			'warning_class' => 'warning-text prod-currency-not-selected-warning-text',
		),
		'notify_url'       => array(
			'title'          => __( 'Notification Url', 'antom-payments' ),
			'type'           => 'paragraph_copy',
			'paragraph_text' => antom_get_notify_url(),
			'description'    => __(
				'Please fill in the notify url address here on the Antom website.',
				'antom-payments'
			),
			'desc_tip'       => true,
		),
		'test_mode'        => array(
			'title'       => __( 'Test Mode', 'antom-payments' ),
			'label'       => __( 'Enable Test Mode', 'antom-payments' ),
			'type'        => 'checkbox',
			'description' => __( 'Place the payment gateway in test mode using test API keys.', 'antom-payments' ),
			'desc_tip'    => true,
			'class'       => 'antom-test-mode-checkbox',
		),
		'test_clientid'    => array(
			'title' => __( 'Test Mode Antom ClientId', 'antom-payments' ),
			'type'  => 'text',
			'class' => 'prod-test-hide',
			'description'   => sprintf(
				/* translators: %s: the site url which test client id, test client public key and test private key to get*/
				__(
					'you can find your Test mode clientId、Private、Public key in <a target="_blank" href="%s">antom</a>',
					'antom-payments'
				),
				$test_client_id_url
			),
		),
		'test_public_key'  => array(
			'title' => __( 'Test Mode Antom Public Key', 'antom-payments' ),
			'type'  => 'textarea',
			'class' => 'prod-test-hide',
		),
		'test_private_key' => array(
			'title' => __( 'Test Mode Your Private Key', 'antom-payments' ),
			'type'  => 'password',
			'class' => 'prod-test-hide',
		),
		'test_currency'    => array(
			'title'   => __( 'Test Settlement Currency', 'antom-payments' ),
			'type'    => 'select',
			'options' => $currencies,
			'class'   => 'prod-test-hide',
		),
	);

	/**
	 *  With this hook,we can manage our Payment Gateway core settings
	 *
	 * @since 1.0.0
	 */
	$core_setting_fields = apply_filters( 'antom_payment_gateways_core_setting_fields', $core_setting_fields );

	return $core_setting_fields;
}

function antom_get_bank_list() {
	$bank_list = array(
		'iDEAL'                => array(
			'Rabobank'              => 'RABONL2U',
			'ABN Amro'              => 'ABNANL2A',
			'Van Lanschot Bankiers' => 'FVLBNL22',
			'Triodos Bank'          => 'TRIONL2U',
			'ING Bank'              => 'INGBNL2A',
			'SNS Bank'              => 'SNSBNL2A',
			'ASN'                   => 'ASNBNL21',
			'RegioBank'             => 'RBRBNL21',
			'Knab'                  => 'KNABNL2H',
			'Bunq'                  => 'BUNQNL2A',
			'Revolut'               => 'REVOLT21',
		),
		'P24'                  => array(
			'Santander-Przelew24'        => 20,
			'Pay with Inteligo'          => 26,
			'Płacę z iPKO (PKO BP)'      => 31,
			'BNP Paribas'                => 33,
			'Bank PEKAO S.A'             => 43,
			'Credit Agricole'            => 45,
			'ING Bank Śląski'            => 49,
			'Konto Inteligo'             => 52,
			'Bank PKO BP (iPKO)'         => 53,
			'Santander'                  => 54,
			'Toyota Bank'                => 64,
			'Bank PEKAO S.A.'            => 65,
			'Volkswagen Bank'            => 69,
			'Bank Millennium'            => 85,
			'Pay with Alior Bank'        => 88,
			'Nest Bank'                  => 90,
			'Credit Agricole 2'          => 95,
			'Pay with BOŚ'               => 99,
			'Pay with ING'               => 112,
			'Pay with CitiHandlowy'      => 119,
			'Alior - Raty'               => 129,
			'Pay with Plus Bank'         => 131,
			'mBank - Raty'               => 136,
			'e-transfer Pocztowy24'      => 141,
			'Banki Spółdzielcze'         => 143,
			'Bank Nowy BFG S.A.'         => 144,
			'Getin Bank'                 => 153,
			'BLIK'                       => 154,
			'Noble Pay'                  => 158,
			'Pay with IdeaBank'          => 161,
			'NestPrzelew'                => 222,
			'BNP Paribas Płacę z Pl@net' => 223,
			'mBank - mTransfer'          => 243,
			'P24now'                     => 266,
			'mBank (PIS)'                => 270,
			'ING Bank Śląski (PIS)'      => 271,
			'BNP Paribas (PIS)'          => 272,
			'Bank PKO BP (PIS)'          => 274,
			'Santander (PIS)'            => 275,
			'Konto Inteligo (PIS)'       => 279,
			'Alior Bank (PIS)'           => 280,
		),
		'FPX (Online banking)' => array(
			'Maybank'                 => 'MYM2U',
			'Bank Islam'              => 'MYBISM',
			'RHB Bank'                => 'MYRHB',
			'Hong Leong Bank'         => 'MYHLB',
			'CIMB Bank'               => 'MYCIMBCLICKS',
			'AmBank'                  => 'MYAMB',
			'Public Bank'             => 'MYPBB',
			'Affin Bank'              => 'MYABB',
			'Agro Bank'               => 'MYAGB',
			'Alliance Bank'           => 'MYABMB',
			'Bank Muamalat'           => 'MYBMMB',
			'Bank of China'           => 'MYBOC',
			'Bank Rakyat'             => 'MYBKRM',
			'Bank Simpanan Nasional'  => 'MYBSN',
			'HSBC Bank'               => 'MYHSBC',
			'Kuwait Finance House'    => 'MYKFH',
			'OCBC Bank'               => 'MYOCBC',
			'Standard Chartered Bank' => 'MYSCB',
			'UOB Bank'                => 'MYUOB',
		),
	);

	/**
	 * With this hook, we can manage our Payment Gateway bank list
	 *
	 * @since 1.0.0
	 */
	$bank_list = apply_filters( 'antom_payment_gateways_bank_list', $bank_list );

	return $bank_list;
}

/**
 * Get antom card payment method validate errors.
 *
 * @param mixed $name the message you want to get , if $name = '', you will get all error messages.
 *
 * @return mixed
 */
function antom_get_card_error_message( $name = '' ) {
	$card_payment_gateway_error_languages = array(
		'card_empty_error_message'     => __( 'Please fill in the card number.', 'antom-payments' ),
		'card_invalid_error_message'   => __(
			'The card number you filled in is incorrect.',
			'antom-payments'
		),
		'expiry_empty_error_message'   => __( 'Please fill in the expiry date.', 'antom-payments' ),
		'expire_invalid_error_message' => __(
			'The expiration time you filled in is incorrect. The correct format is: month/year',
			'antom-payments'
		),
		'cvv_empty_error_message'      => __( 'Please fill in the CVV.', 'antom-payments' ),
		'cvv_invalid_error_message'    => __( 'CVV should be a 3-digit number.', 'antom-payments' ),
		'holder_name_required'         => __( 'Card Holder name is required', 'antom-payments' ),
		'antom_card_token_fetch_error' => __( 'Card Info Error, Please change a card or check your card info', 'antom-payments' ),
	);
	if ( ! $name ) {
		return $card_payment_gateway_error_languages;
	} else {
		return isset( $card_payment_gateway_error_languages[ $name ] ) ? $card_payment_gateway_error_languages[ $name ] : '';
	}
}

/**
 * Get antom payments gateway loading animate setting.
 *
 * @return array
 */
function antom_get_loading_animate_setting() {
	return array(
		'redirect_to_antom_loading_image_url'   => ANTOM_PAYMENT_GATEWAYS_URL . 'assets/images/loading.gif',
		'request_to_antom_payments_gateway'     => __(
			'Requesting payment data from the payment gateway, please wait a moment.',
			'antom-payments'
		),
		'redirect_to_antom_loading_description' => __( 'The page is jumping, please wait a moment.', 'antom-payments' ),
	);
}
