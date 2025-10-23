<?php

/**
 * Antom_Payment_Gateways_Statement
 *
 * @user Antom
 * @package includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Antom_Payment_Gateways_Statement
 */
class Antom_Payment_Gateways_Statement {

	/**
	 * Set the antom develop mode, set to true to enable the development mode.
	 * in the development mode, all of he  payment gateways which is activated will show on checkout page.
	 *
	 * @since 1.0.0
	 */
	const ANTOM_DEVELOP_MODE = false;

	/**
	 * This field is used to store the active state of the payment gateways in the database
	 *
	 * @since 1.0.0
	 */
	const ANTOM_ACTIVE_STATE_FIELD = 'antom_active_state';

	/**
	 * This is antom un active state
	 *
	 * @since 1.0.0
	 */
	const ANTOM_UN_ACTIVE_STATE = 0;

	/**
	 * This is antom active state
	 *
	 * @since 1.0.0
	 */
	const ANTOM_ACTIVE_STATE = 1;

	/**
	 * This is antom core setting file save in options table
	 *
	 * @since 1.0.0
	 */
	const ANTOM_CORE_SETTING_FIELD = 'antom_core_settings';

	/**
	 * We use this slug to render antom core setting page
	 *
	 * @since 1.0.0
	 */
	const ANTOM_CORE_SETTING_SLUG = 'antom-payment-gateway';

	/**
	 * Antom payment gateway Host
	 *
	 * @since 1.0.0
	 */
	const ANTOM_ALIPAY_API_HOST = 'https://open-sea.alipay.com';

	/**
	 * Antom payment gateway pay path
	 *
	 * @since 1.0.0
	 */
	const ANTOM_ALIPAY_PATH = '/ams/api/v1/payments/pay';

	/**
	 * Antom payment gateway refund path
	 *
	 * @since 1.0.0
	 */
	const ANTOM_ALIPAY_REFUND_PAHT = '/v1/payments/refund';

	/**
	 * Antom payment gateway sandbox pay path
	 *
	 * @since 1.0.0
	 */
	const ANTOM_ALIPAY_SANDBOX_PATH = '/ams/sandbox/api/v1/payments/pay';

	/**
	 * Antom payment gateway inquiry path
	 *
	 * @since 1.0.0
	 */
	const ANTOM_INQUIRY_API_HOST = 'https://open-sea-global.alipay.com';

	/**
	 * Antom payment gateway inquiry path
	 *
	 * @since 1.0.0
	 */
	const ANTOM_INQUIRY_PATH = '/ams/api/v1/payments/inquiryPayment';

	/**
	 * Antom payment gateway inquiry sandbox path
	 *
	 * @since 1.0.0
	 */
	const ANTOM_INQUIRY_SANDBOX_PATH = '/ams/sandbox/api/v1/payments/inquiryPayment';

	/**
	 * The Antom website URL, displayed on the plugin settings page, allows users to visit the Antom website by clicking the 'Visit' button.
	 * From there, users can log in to the Antom dashboard to view and manage their transactions.
	 *
	 * @since 1.0.0
	 */
	const ANTOM_WEBSITE_URL = 'https://dashboard.alipay.com/global-payments/home';

	/**
	 * The Antom registration URL redirects users to the Antom account registration page when they click the 'Connect' button on the plugin settings page.
	 * On this page, users can create a new Antom account.
	 *
	 * @since 1.0.0
	 */
	const ANTOM_REGESITER_URL = 'https://dashboard.alipay.com/global-payments/account/register?goto=https%3A%2F%2Fdashboard.alipay.com%2Fglobal-payments%2Fhome&bizMode=ISV_SELF_BUILD';

	/**
	 * Antom Default time zone
	 *
	 * @since 1.0.0
	 */
	const ANTOM_DEFAULT_TIME_ZONE = 'Asia/Shanghai';

	/**
	 * Antom Payment gateway reference id prefix
	 *
	 * @since 1.0.0
	 */
	const ANTOM_REFERENCE_ID_PREFIX = '2088';

	/**
	 * Antom Test mode warning message
	 *
	 * @since 1.0.0
	 */
	const ANTOM_TEST_MODE_WARNING = 'Your selected payment method is currently in sandbox mode';

	/**
	 * Set Antom payment gateway is allow to use credit card
	 *
	 * @since 1.0.0
	 */
	const ANTOM_ALLOW_CARD_PAYMENT_GATEWAY = true;

	/**
	 * To meet the PCI-DSS requirements for card payments, we encrypt card information using the following service
	 * ensuring that no plaintext card data is transmitted to the website.
	 *
	 * @since 1.0.0
	 */
	const ANTOM_CARD_TOKEN_URL = 'https://open-sea.alipay.com/amsin/api/v1/paymentMethods/cacheCard.htm';

	/**
	 * Antom payment gateway request payment id key in wc_orders_meta table
	 *
	 * @since 1.0.0
	 */
	const ANTOM_REQUEST_PAYMENT_KEY = '_antom_request_payment_id';

	/**
	 * Antom payment gateway request payment id key number can stored in database.
	 *
	 * @since 1.0.0
	 */
	const ANTOM_REQUEST_PAYMENT_KEY_LIMIT = 20;


	/**
	 * Antom payment gateway abnormal notify log key in wc_order_meta table
	 *
	 * @since 1.0.0
	 */
	const ANTOM_NOTIFY_ABNORMAL_KEY = '_antom_payment_abnormal_log';

	/**
	 * Prompt the user to settle the currency and bind the settlement bank card information.
	 *
	 * @since 1.0.0
	 */
	const ANTOM_LIVE_MODE_SETTLEMENT_CURRENCY_WARNING = 'Please ensure that you have linked the settlement card and selected the correct settlement currency before initiating live mode transactions';

	/**
	 * Prompt users not to use the client id of the sandbox environment in live mode.
	 *
	 * @since 1.0.0
	 */
	const ANTOM_LIVE_MODE_CAN_NOT_USE_TEST_MODE_CLIENT_ID = 'Live Mode clientId error, do not use sandbox clientId in live mode.';
}
