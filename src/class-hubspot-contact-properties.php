<?php
/**
 * Hubspot Contact_Properties.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Contact_Properties.
	 */
	class HubSpot_Contact_Properties extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		public function get_all_contact_properties() {
			return $this->run( 'properties/v1/contacts/properties' );
		}

		public function get_a_contact_property( $property_name ) {
			return $this->run( "properties/v1/contacts/properties/named/$property_name" );
		}

		public function create_a_contact_property( $contact_property_json ) {
			return $this->run( 'properties/v1/contacts/properties', $contact_property_json, 'POST' );
		}

		public function update_a_contact_property( $property_name, $contact_property_json ) {
			return $this->run( "properties/v1/contacts/properties/named/$property_name", $contact_property_json, 'PUT' );
		}

		public function delete_a_contact_property( $property_name, $contact_property_json ) {
			return $this->run( "properties/v1/contacts/properties/named/$property_name", array(), 'DELETE' );
		}

		public function get_contact_property_groups( $include_properties = null ) {
			$args = $this->filter_args( array( 'includeProperties' => $include_properties ) );
			return $this->run( 'properties/v1/contacts/groups', $args );
		}

		public function get_contact_property_group_details( $group_name, $include_properties = null ) {
			$args = $this->filter_args( array( 'includeProperties' => $include_properties ) );
			return $this->run( "properties/v1/contacts/groups/named/$group_name", $args );
		}

		public function create_contact_property_group( $group_name, $display_name, $display_order = null ) {
			$args = $this->filter_args(
				array(
					'name'         => $group_name,
					'displayName'  => $display_name,
					'displayOrder' => $display_order,
				)
			);
			return $this->run( 'properties/v1/contacts/groups', $args, 'POST' );
		}

		public function update_contact_property_group( $group_name, $group_json ) {
			return $this->run( "properties/v1/contacts/groups/named/$group_name", $group_json, 'PUT' );
		}

		public function delete_contact_property_group( $group_name, $group_json ) {
			return $this->run( "properties/v1/contacts/groups/named/$group_name", array(), 'DELETE' );
		}

	}

	new HubSpot_Contact_Properties();

}
