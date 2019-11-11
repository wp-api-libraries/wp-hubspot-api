<?php
/**
 * Hubspot Companies.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Companies.
	 */
	class HubSpot_Companies extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Add Company.
		 *
		 * @access public
		 * @return void
		 */
		public function create_company( $properties ) {
			if ( ! isset( $properties['properties'] ) ) {
				$properties = array(
					'properties' => $properties,
				);
			}
			return $this->run( 'companies/v2/companies', $properties, 'POST' );
		}

		/**
		 * Update Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @return void
		 */
		public function update_company( $company_id, $properties ) {
			if ( ! isset( $properties['properties'] ) ) {
				$properties = array(
					'properties' => $properties,
				);
			}
			return $this->run( "companies/v2/companies/$company_id", $properties, 'PUT' );
		}

		/**
		 * Update a group of Companies.
		 *
		 * @access public
		 *
		 * @param  array   Array of companies to update.
		 * @return array
		 */
		public function update_company_group( $batch ) {
			return $this->run( 'companies/v1/batch-async/update', $batch, 'POST' );
		}

		/**
		 * Delete Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @return void
		 */
		public function delete_company( $company_id ) {
			return $this->run( "companies/v2/companies/$company_id", array(), 'DELETE' );
		}

		/**
		 * Get Companies.
		 *
		 * @access public
		 * @param string $limit                   The number of records to return. Defaults to 100, has a maximum value of 250.
		 * @param string $offset                  Used to page through the results. If there are more records in your portal
		 *                                        than the limit= parameter, you will need to use the offset returned in the
		 *                                        first request to get the next set of results.
		 * @param string $properties              Used to include specific company properties in the results.  By default,
		 *                                        the results will only include the company ID, and will not include the
		 *                                        values for any properties for your companies.  Including this parameter
		 *                                        will include the data for the specified property in the results.  You can
		 *                                        include this parameter multiple times to request multiple properties.
		 *                                        Note: Companies that do not have a value set for a property will not
		 *                                        include that property, even when you specify the property. A company
		 *                                        without a value for the website property would not show the website
		 *                                        property in the results, even with &properties=website in the URL.
		 * @param string $propertiesWithHistory   Works similarly to properties=, but this parameter will include the
		 *                                        history for the specified property, instead of just including the current
		 *                                        value. Use this parameter when you need the full history of changes to a
		 *                                        property's value.
		 * @return void
		 */
		public function get_companies( int $limit = null, int $offset = null, $properties = null, $propertiesWithHistory = null ) {
			$args = $this->filter_args( compact( 'limit', 'offset', 'properties', 'propertiesWithHistory' ) );

			return $this->build_request( 'companies/v2/companies/paged', $args )->fetch();
		}

		/**
		 * Get Recently Modified Companies.
		 *
		 * @access public
		 * @param string $offset (default: '') Offset.
		 * @param string $count (default: '') Count.
		 * @return void
		 */
		public function get_recently_modified_companies( $offset = '', $count = '' ) {
			$request = 'companies/v2/companies/recent/modified';
			return $this->run( $request );
		}

		/**
		 * Get Recently Created Companies.
		 *
		 * @access public
		 * @param string $offset (default: '') Offset.
		 * @param string $count (default: '') count.
		 * @return void
		 */
		public function get_recently_created_companies( $offset = '', $count = '' ) {
			$request = 'companies/v2/companies/recent/created';
			return $this->run( $request );
		}

		/**
		 * Get company by Domain.
		 *
		 * @access public
		 * @param mixed $domain Domain.
		 * @return void
		 */
		public function get_company_by_domain( $domain ) {
			return $this->run( "companies/v2/companies/domain/$domain" );
		}

		/**
		 * Get Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @return void
		 */
		public function get_company( $company_id ) {
			return $this->run( "companies/v2/companies/$company_id" );
		}

		/**
		 * Get Company Contacts.
		 *
		 * @access public
		 * @param mixed  $company_id Company ID.
		 * @param string $vidoffset (default: '') Vid Offset.
		 * @param string $count (default: '') Count.
		 * @return void
		 */
		public function get_company_contacts( $company_id, $vidoffset = '', $count = '' ) {
			return $this->run( "companies/v2/companies/$company_id/contacts" );
		}

		/**
		 * Get Company Contacts IDs.
		 *
		 * @access public
		 * @param mixed  $company_id Company ID.
		 * @param string $vidoffset (default: '') VidOffset.
		 * @param string $count (default: '') Count.
		 * @return void
		 */
		public function get_company_contacts_ids( $company_id, $vidoffset = '', $count = '' ) {
			return $this->run( "companies/v2/companies/$company_id/vids" );
		}

		/**
		 * Add Contact to Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @param mixed $contact_vid Contact VID.
		 * @return void
		 */
		public function add_contact_to_company( $company_id, $contact_vid ) {
			return $this->run( "companies/v2/companies/$company_id/contacts/$contact_vid", array(), 'PUT' );
		}

		/**
		 * Remove Contact from Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @param mixed $contact_vid Contact VID.
		 * @return void
		 */
		public function remove_contact_from_company( $company_id, $contact_vid ) {
			return $this->run( "companies/v2/companies/$company_id/contacts/$contact_vid", array(), 'DELETE' );
		}

	}

	new HubSpot_Companies();

}
