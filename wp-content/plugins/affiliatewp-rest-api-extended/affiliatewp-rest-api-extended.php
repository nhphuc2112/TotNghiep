<?php
/**
 * Plugin Name: AffiliateWP - REST API Extended
 * Plugin URI: https://affiliatewp.com/addons/rest-api-extended/
 * Description: Adds write, edit, and delete endpoints to the AffiliateWP REST API.
 * Author: AffiliateWP
 * Author URI: https://affiliatewp.com/
 * Version: 1.2.1
 * Text Domain: affiliatewp-rest-api-extended
 * Domain Path: languages
 *
 * AffiliateWP is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * AffiliateWP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AffiliateWP. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package AffiliateWP REST API Extended
 * @category Core
 * @author Drew Jaynes
 * @version 1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AffiliateWP_Requirements_Check_v1_1' ) ) {
	require_once dirname( __FILE__ ) . '/includes/lib/affwp/class-affiliatewp-requirements-check-v1-1.php';
}

/**
 * Class used to check requirements for and bootstrap the plugin.
 *
 * @since 1.1
 *
 * @see Affiliate_WP_Requirements_Check
 */
class AffiliateWP_REST_Requirements_Check extends AffiliateWP_Requirements_Check_v1_1 {

	/**
	 * Plugin slug.
	 *
	 * @since 1.1
	 * @var   string
	 */
	protected $slug = 'affiliatewp-rest-api-extended';

	/**
	 * Add-on requirements.
	 *
	 * @since 1.1
	 * @var   array[]
	 */
	protected $addon_requirements = array(
		// AffiliateWP.
		'affwp' => array(
			'minimum' => '2.6',
			'name'    => 'AffiliateWP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		),
	);

	/**
	 * Bootstrap everything.
	 *
	 * @since 1.1
	 */
	public function bootstrap() {
		if ( ! class_exists( 'Affiliate_WP' ) ) {

			if ( ! class_exists( 'AffiliateWP_Activation' ) ) {
				require_once 'includes/lib/affwp/class-affiliatewp-activation.php';
			}

			// AffiliateWP activation
			if ( ! class_exists( 'Affiliate_WP' ) ) {
				$activation = new AffiliateWP_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
				$activation = $activation->run();
			}
		} else {
			\AffiliateWP_REST_API_Extended::instance( __FILE__ );
		}
	}

	/**
	 * Loads the add-on.
	 *
	 * @since 1.1
	 */
	protected function load() {
		// Maybe include the bundled bootstrapper.
		if ( ! class_exists( 'AffiliateWP_REST_API_Extended' ) ) {
			require_once dirname( __FILE__ ) . '/includes/class-affiliatewp-rest-api-extended.php';
		}

		// Maybe hook-in the bootstrapper.
		if ( class_exists( 'AffiliateWP_REST_API_Extended' ) ) {

			$affwp_version = get_option( 'affwp_version' );

			if ( version_compare( $affwp_version, '2.7', '<' ) ) {
				add_action( 'plugins_loaded', array( $this, 'bootstrap' ), 100 );
			} else {
				add_action( 'affwp_plugins_loaded', array( $this, 'bootstrap' ), 100 );
			}

			// Register the activation hook.
			register_activation_hook( __FILE__, array( $this, 'install' ) );
		}
	}

	/**
	 * Install, usually on an activation hook.
	 *
	 * @since 1.1
	 */
	public function install() {
		// Bootstrap to include all of the necessary files
		$this->bootstrap();

		if ( defined( 'AFFWP_REST_VERSION' ) ) {
			update_option( 'affwp_rest_version', AFFWP_REST_VERSION );
		}
	}

	/**
	 * Plugin-specific aria label text to describe the requirements link.
	 *
	 * @since 1.1
	 *
	 * @return string Aria label text.
	 */
	protected function unmet_requirements_label() {
		return esc_html__( 'AffiliateWP - REST API Extended Requirements', 'affiliatewp-rest-api-extended' );
	}

	/**
	 * Plugin-specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 1.1
	 *
	 * @return string CSS selector.
	 */
	protected function unmet_requirements_name() {
		return 'affiliatewp-rest-api-extended-requirements';
	}

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 1.1
	 *
	 * @return string Unmet requirements URL.
	 */
	protected function unmet_requirements_url() {
		return 'https://docs.affiliatewp.com/article/2361-minimum-requirements-roadmaps';
	}

}

$requirements = new AffiliateWP_REST_Requirements_Check( __FILE__ );

$requirements->maybe_load();
