<?php
/**
 * Hubspot Company Properties.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Company_Properties.
	 */
	class HubSpot_Company_Properties extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}


		/**
		 * Get all Company Properties.
		 *
		 * Get all of the company properties, including their definition, for a given portal.
		 *
		 * @api GET
		 * @see https://developers.hubspot.com/docs/methods/companies/get_company_properties Documentation
		 * @return array A list of company properties.
		 */
		public function get_all_company_properties() {
			return $this->run( 'properties/v1/companies/properties' );
		}

		/**
		 * get_company_property function.
		 *
		 * @see https://developers.hubspot.com/docs/methods/companies/get_company_property
		 *
		 * @access public
		 * @return void
		 */
		public function get_company_property( string $property_name ) {
			return $this->run( "properties/v1/companies/properties/named/$property_name" );

		}

		/**
		 * add_company_property function.
		 *
		 * @access public
		 * @param mixed $args
		 * @return void
		 */
		public function add_company_property( $args ) {
			return $this->run( 'properties/v1/companies/properties', $args, 'POST' );
		}

		/**
		 * update_company_property function.
		 *
		 * @access public
		 * @param mixed $args
		 * @return void
		 */
		public function update_company_property( $args ) {
			return $this->run( 'properties/v1/companies/properties', $args, 'PUT' );
		}

		/**
		 * delete_company_property function.
		 *
		 * @access public
		 * @param mixed $property_name
		 * @return void
		 */
		public function delete_company_property( $property_name ) {
			return $this->run( "properties/v1/companies/properties/named/$property_name", array(), 'DELETE' );
		}

		/**
		 * get_company_property_groups function.
		 *
		 * @access public
		 * @param mixed $include_properties (default: null)
		 * @return void
		 */
		public function get_company_property_groups( $include_properties = null ) {
			return $this->run( 'properties/v1/companies/groups', $this->filter_args( array( 'includeProperties' => $include_properties ) ) );
		}

		/**
		 * add_company_property_group function.
		 *
		 * @see https://developers.hubspot.com/docs/methods/companies/create_company_property_group
		 * @access public
		 * @return void
		 */
		public function add_company_property_group( $name, $display_name, int $display_order = null ) {
			$args = $this->filter_args(
				array(
					'name'         => $name,
					'displayName'  => $display_name,
					'displayOrder' => $display_order,
				)
			);

			return $this->build_request( 'properties/v1/companies/groups/', $args, 'POST' )->fetch();
		}

		/**
		 * update_company_property_group function.
		 *
		 * @access public
		 * @param mixed $name
		 * @param mixed $args
		 * @return void
		 */
		public function update_company_property_group( $name, $args ) {
			return $this->build_request( "properties/v1/companies/groups/named/$name", $args, 'PUT' )->fetch();
		}

		/**
		 * delete_company_property_group function.
		 *
		 * @access public
		 * @param mixed $name
		 * @param mixed $args
		 * @return void
		 */
		public function delete_company_property_group( $name, $args ) {
			return $this->build_request( "properties/v1/companies/groups/named/$name", array(), 'DELETE' )->fetch();
		}


}

new HubSpot_Company_Properties();

}
