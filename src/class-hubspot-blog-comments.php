<?php
/**
 * Hubspot Blog Comments.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Blog_Comments.
	 */
	class HubSpot_Blog_Comments extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}



	}

	new HubSpot_Blog_Comments();

}
