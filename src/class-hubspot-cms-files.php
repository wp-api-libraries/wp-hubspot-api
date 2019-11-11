<?php
/**
 * Hubspot CMS Files.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot CMS_Files.
	 */
	class HubSpot_CMS_Files extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * list_all_file_metadata public function.
		 *
		 * @access public
		 * @param array $args (default: array())
		 * @return void
		 */
		public function list_all_file_metadata( $args = array() ) {
			return $this->run( 'filemanager/api/v2/files', $args );
		}

		/**
		 * list_file_metadata public function.
		 *
		 * @access public
		 * @param mixed $file_id
		 * @return void
		 */
		public function list_file_metadata( $file_id ) {
			return $this->run( 'filemanager/api/v2/files/' . $file_id );
		}

		/**
		 * hard_delete_file public function.
		 *
		 * @access public
		 * @param mixed $file_id
		 * @return void
		 */
		public function hard_delete_file( $file_id ) {
			return $this->run( 'filemanager/api/v2/files/' . $file_id . '/full-delete', array(), 'POST' );
		}

	}

	new HubSpot_CMS_Files();

}
