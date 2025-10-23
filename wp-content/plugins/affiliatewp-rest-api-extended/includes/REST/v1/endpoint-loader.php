<?php
namespace AffWP\AddOns\REST\v1;

use AffWP\Affiliate\REST\v1 as Affiliate;
use AffWP\Creative\REST\v1 as Creative;
use AffWP\Customer\REST\v1 as Customer;
use AffWP\Affiliate\Payout\REST\v1 as Payout;
use AffWP\Referral\REST\v1 as Referral;
use AffWP\Visit\REST\v1 as Visit;

/**
 * Loads the write, edit, and delete endpoints based on settings.
 *
 * @since 1.0.0
 */
class Endpoint_Loader {

	/**
	 * Loads endpoint classes.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function includes() {
		if ( ! class_exists( 'AffWP\REST\v1\Controller' ) ) {
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-rest-controller.php';
		}

		// Affiliates.
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/affiliates/class-create-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/affiliates/class-edit-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/affiliates/class-delete-endpoint.php';

		// Creatives.
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/creatives/class-create-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/creatives/class-edit-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/creatives/class-delete-endpoint.php';

		// Customers.
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/customers/class-create-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/customers/class-edit-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/customers/class-delete-endpoint.php';

		// Payouts.
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/payouts/class-create-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/payouts/class-edit-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/payouts/class-delete-endpoint.php';

		// Referrals.
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/referrals/class-create-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/referrals/class-edit-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/referrals/class-delete-endpoint.php';

		// Visits.
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/visits/class-create-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/visits/class-edit-endpoint.php';
		require_once AFFWP_REST_PLUGIN_DIR . 'includes/REST/v1/visits/class-delete-endpoint.php';
	}

	/**
	 * Initializes the loader.
	 *
	 * @access public
	 * @since  1.0.0
	 * @static
	 */
	public static function init() {
		$instance = new self();

		$instance->includes();

		$instance->affiliate_endpoints();
		$instance->creative_endpoints();
		$instance->customer_endpoints();
		$instance->payout_endpoints();
		$instance->referral_endpoints();
		$instance->visit_endpoints();
	}

	/**
	 * Initializes affiliates endpoints.
	 *
	 * @access protected
	 * @since  1.0.0
	 */
	protected function affiliate_endpoints() {

		// /affiliates/ POST.
		if ( affiliate_wp()->settings->get( 'rest_api_affiliates_create' ) ) {
			new Affiliate\Create_Endpoint;
		}

		// /affiliates/<id> POST.
		if ( affiliate_wp()->settings->get( 'rest_api_affiliates_edit' ) ) {
			new Affiliate\Edit_Endpoint;
		}

		// //affiliates/<id> DELETE.
		if ( affiliate_wp()->settings->get( 'rest_api_affiliates_delete' ) ) {
			new Affiliate\Delete_Endpoint;
		}
	}

	/**
	 * Initializes creatives endpoints.
	 *
	 * @access protected
	 * @since  1.0.0
	 */
	protected function creative_endpoints() {

		// /creatives/ POST.
		if ( affiliate_wp()->settings->get( 'rest_api_creatives_create' ) ) {
			new Creative\Create_Endpoint;
		}

		// /creatives/<id> POST.
		if ( affiliate_wp()->settings->get( 'rest_api_creatives_edit' ) ) {
			new Creative\Edit_Endpoint;
		}

		// /creatives/<id> DELETE.
		if ( affiliate_wp()->settings->get( 'rest_api_creatives_delete' ) ) {
			new Creative\Delete_Endpoint;
		}

	}

	/**
	 * Initializes customers endpoints.
	 *
	 * @since 1.0.5
	 */
	protected function customer_endpoints() {

		// /customers/ POST.
		if ( affiliate_wp()->settings->get( 'rest_api_customers_create' ) ) {
			new Customer\Create_Endpoint;
		}

		// /customers/<id> POST.
		if ( affiliate_wp()->settings->get( 'rest_api_customers_edit' ) ) {
			new Customer\Edit_Endpoint;
		}

		// //customers/<id> DELETE.
		if ( affiliate_wp()->settings->get( 'rest_api_customers_delete' ) ) {
			new Customer\Delete_Endpoint;
		}
	}

	/**
	 * Initializes payout endpoints.
	 *
	 * @access protected
	 * @since  1.0.0
	 */
	protected function payout_endpoints() {

		// /payouts/ POST.
		if ( affiliate_wp()->settings->get( 'rest_api_payouts_create' ) ) {
			new Payout\Create_Endpoint;
		}

		// /payouts/<id> POST.
		if ( affiliate_wp()->settings->get( 'rest_api_payouts_edit' ) ) {
			new Payout\Edit_Endpoint;
		}

		// /payouts/<id> DELETE.
		if ( affiliate_wp()->settings->get( 'rest_api_payouts_delete' ) ) {
			new Payout\Delete_Endpoint;
		}

	}

	/**
	 * Initializes referral endpoints.
	 *
	 * @access protected
	 * @since  1.0.0
	 */
	protected function referral_endpoints() {

		// /referrals/ POST.
		if ( affiliate_wp()->settings->get( 'rest_api_referrals_create' ) ) {
			new Referral\Create_Endpoint;
		}

		// /referrals/<id> POST.
		if ( affiliate_wp()->settings->get( 'rest_api_referrals_edit' ) ) {
			new Referral\Edit_Endpoint;
		}

		// /referrals/<id> DELETE.
		if ( affiliate_wp()->settings->get( 'rest_api_referrals_delete' ) ) {
			new Referral\Delete_Endpoint;
		}

	}

	/**
	 * Initializes visit endpoints.
	 *
	 * @access protected
	 * @since  1.0.0
	 */
	protected function visit_endpoints() {

		// /visits/ POST.
		if ( affiliate_wp()->settings->get( 'rest_api_visits_create' ) ) {
			new Visit\Create_Endpoint;
		}

		// /visits/<id> POST.
		if ( affiliate_wp()->settings->get( 'rest_api_visits_edit' ) ) {
			new Visit\Edit_Endpoint;
		}

		// /visits/<id> DELETE.
		if ( affiliate_wp()->settings->get( 'rest_api_visits_delete' ) ) {
			new Visit\Delete_Endpoint;
		}

	}
}
Endpoint_Loader::init();
