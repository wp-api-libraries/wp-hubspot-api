<?php
/**
 * Hubspot Contact_Lists.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Contact_Lists.
	 */
	class HubSpot_Contact_Lists extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * create_contact_list function.
		 *
		 * @access public
		 * @param mixed $name
		 * @param array $args (default: array())
		 * @return void
		 */
		public function create_contact_list( $name, $args = array() ) {
			$args['name'] = $name;
			return $this->run( '/contacts/v1/lists', $args, 'POST' );
		}

		/**
		 * get_contact_lists function.
		 *
		 * @access public
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_contact_lists( $args = array() ) {
			return $this->run( '/contacts/v1/lists', $args, 'POST' );
		}

		/**
		 * get_contact_list function.
		 *
		 * @access public
		 * @param mixed $list_id
		 * @return void
		 */
		public function get_contact_list( $list_id ) {
			return $this->run( "/contacts/v1/lists/$list_id" );
		}

		/**
		 * update_contact_list function.
		 *
		 * @access public
		 * @param mixed $list_id
		 * @param mixed $args
		 * @return void
		 */
		public function update_contact_list( $list_id, $args ) {
			return $this->run( "/contacts/v1/lists/$list_id", $args, 'POST' );
		}

		/**
		 * delete_contact_list function.
		 *
		 * @access public
		 * @param mixed $list_id
		 * @return void
		 */
		public function delete_contact_list( $list_id ) {
			return $this->run( "contacts/v1/lists/$list_id", array(), 'DELETE' );

		}


		/**
		 * get_batch_contact_lists function.
		 *
		 * @access public
		 * @param array $list_ids
		 * @return void
		 */
		public function get_batch_contact_lists( array $list_ids ) {
			$args = array(
				'listId' => $list_ids,
			);
			return $this->run( "contacts/v1/lists/$list_id", $args );
		}

		public function get_static_contact_lists( $count = null, $offset = null ) {
			$args = $this->filter_args(
				array(
					'count'  => $count,
					'offset' => $offset,
				)
			);
			return $this->run( 'contacts/v1/lists/static', $args );

		}

		public function get_dynamic_contact_lists( $count = null, $offset = null ) {
			$args = $this->filter_args(
				array(
					'count'  => $count,
					'offset' => $offset,
				)
			);
			return $this->run( 'contacts/v1/lists/dynamic', $args );
		}

		public function get_contacts_in_list( $list_id, $opt_args ) {
			return $this->run( "contacts/v1/lists/$list_id/contacts/all", $opt_args );
		}

		public function get_recent_contacts_in_list( $list_id, $opt_args ) {
			return $this->run( "contacts/v1/lists/$list_id/contacts/recent", $opt_args );

		}

		public function add_contact_to_list( $list_id, $contact_json ) {
			return $this->run( "contacts/v1/lists/$list_id/add", $contact_json, 'POST' );
		}

		public function delete_contact_from_list() {
			return $this->run( "contacts/v1/lists/$list_id/remove", array(), 'DELETE' );
		}

	}

	new HubSpot_Contact_Lists();

}
