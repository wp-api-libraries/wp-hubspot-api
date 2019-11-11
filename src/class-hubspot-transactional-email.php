<?php
/**
 * Hubspot Transactional_Email.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Transactional_Email.
	 */
	class HubSpot_Transactional_Email extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * List SMTP API Tokens.
		 *
		 * @access public
		 * @return void
		 */
		public function get_smtp_tokens() {
			$request = 'email/public/v1/smtpapi/tokens';
			return $this->run( $request );
		}

		/**
		 * Add SMTP Token.
		 *
		 * @access public
		 * @param mixed $createdby Created By.
		 * @param mixed $campaign_name Campaign Name.
		 * @return void
		 */
		public function add_smtp_token( $createdby, $campaign_name ) {
			$request = 'email/public/v1/smtpapi/tokens';
			return $this->run( $request );
		}

		/**
		 * [reset_smtp_api_token description]
		 * @param [type] $user_name [description]
		 */
		public function reset_smtp_api_token( $user_name ) {

		}

	}

	new HubSpot_Transactional_Email();

}
