<?php
/**
 * Hubspot Deals.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Deals.
	 */
	class HubSpot_Deals extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Add Deal.
		 *
		 * @access public
		 * @param mixed $deal_json Deal JSON.
		 * @return void
		 */
		public function add_deal( $deal_json ) {
			return $this->run( 'deals/v1/deal/', $deal_json, 'POST' );
		}

		/**
		 * Update Deal.
		 *
		 * @access public
		 * @param mixed $deal_id Deal ID.
		 * @param mixed $deal_json Deal JSON.
		 * @return void
		 */
		public function update_deal( $deal_id, $deal_json ) {
			return $this->run( "deals/v1/deal/$deal_id", $deal_json, 'PUT' );
		}

		public function update_deal_batch( $deal_json ) {
			return $this->run( 'deals/v1/batch-async/update', $deal_json, 'POST' );
		}

		/**
		 * Get all deals.
		 * Docs: https://developers.hubspot.com/docs/methods/deals/get-all-deals
		 *
		 * @access public
		 * @param mixed $limit (default: null) Limit.
		 * @param mixed $offset (default: null) Offset.
		 * @param mixed $properties (default: null) Properties.
		 * @param mixed $properties_with_history (default: null) Properties with History.
		 * @param mixed $associations Associations.
		 * @return void
		 */
		public function get_all_deals( $limit = null, $offset = null, $properties = null, $properties_with_history = null, $include_associations = null ) {
			$args = array(
				'limit'                 => $limit,
				'offset'                => $offset,
				'properties'            => $properties,
				'propertiesWithHistory' => $properties_with_history,
				'includeAssociations'   => $include_associations,
			);
			return $this->run( 'deals/v1/deal/paged', $args );
		}

		public function get_recently_modified_deals( $limit = null, $offset = null, $since = null, $properties_with_versions = null ) {
			$args = array(
				'count'                   => $limit,
				'offset'                  => $offset,
				'since'                   => $since,
				'includePropertyVersions' => $properties_with_versions,
			);
			return $this->run( 'deals/v1/deal/recent/modified', $args );
		}

		public function get_recent_created_deals( $limit = null, $offset = null, $since = null, $properties_with_versions = null ) {
			$args = array(
				'count'                   => $limit,
				'offset'                  => $offset,
				'since'                   => $since,
				'includePropertyVersions' => $properties_with_versions,
			);
			return $this->run( 'deals/v1/deal/recent/created', $args );
		}

		public function delete_deal( $deal_id ) {
			return $this->run( "deals/v1/deal/$deal_id", array(), 'DELETE' );
		}

		public function get_deal( $deal_id, $properties_with_versions = null ) {
			return $this->run( "deals/v1/deal/$deal_id", array( 'includePropertyVersions' => $properties_with_versions ) );
		}

		public function associate_deal( $deal_id, $object_type, $ids ) {
			$args = array( 'id' => $ids );
			$url  = "deals/v1/deal/$deal_id/associations/$object_type";
			$url  = add_query_arg( 'id', $ids, $url );

			return $this->run( $url, $args, 'PUT' );
		}

		public function delete_deal_association( $deal_id, $object_type, $ids ) {
			$args = array( 'id' => $ids );
			$url  = "deals/v1/deal/$deal_id/associations/$object_type";
			$url  = add_query_arg( 'id', $ids, $url );

			return $this->run( $url, $args, 'DELETE' );
		}

		public function get_associated_deals( $object_type, $object_id, $limit = null, $offset = null, $properties = null, $properties_with_history = null, $include_associations = null ) {
			$args = array(
				'limit'                 => $limit,
				'offset'                => $offset,
				'properties'            => $properties,
				'propertiesWithHistory' => $properties_with_history,
				'includeAssociations'   => $include_associations,
			);
			return $this->run( "deals/v1/deal/associated/$object_type/$object_id/paged", $args );
		}



	}

	new HubSpot_Deals();

}
