<?php
/**
 * Hubspot Deal Properties.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Deal_Properties.
	 */
	class HubSpot_Deal_Properties extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		public function add_deal_property( $property_json ) {
			return $this->run( 'properties/v1/deals/properties/', $property_json, 'POST' );
		}

		public function update_deal_property( $property_name, $property_json ) {
			return $this->run( "properties/v1/deals/properties/named/$property_name", $property_json, 'PUT' );
		}

		public function delete_deal_property( $property_name ) {
			return $this->run( "properties/v1/deals/properties/named/$property_name", array(), 'DELETE' );
		}

		public function get_all_deal_properties() {
			return $this->run( 'properties/v1/deals/properties/' );
		}

		public function get_deal_property() {
			return $this->run( "properties/v1/deals/properties/named/$property_name" );
		}

		public function add_deal_property_group( $property_group_json ) {
			return $this->run( 'properties/v1/deals/groups/', $property_group_json, 'POST' );
		}

		public function update_deal_property_group( $group_name ) {
			return $this->run( "properties/v1/deals/groups/named/$group_name", $property_group_json, 'PUT' );
		}

		public function delete_deal_property_group( $group_name ) {
			return $this->run( "properties/v1/deals/groups/named/$group_name", array(), 'DELETE' );
		}

		public function get_deal_property_groups( $include_properties = null ) {
			$args = array( 'includeProperties' => $include_properties );
			return $this->run( 'properties/v1/deals/groups', $args );
		}

		public function get_deal_property_group( $property_group, $include_properties ) {
			$args = array( 'includeProperties' => $include_properties );
			return $this->run( "properties/v1/deals/groups/named/$property_group", $args );
		}

	}

	new HubSpot_Deal_Properties();

}
