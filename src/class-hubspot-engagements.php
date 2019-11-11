<?php
/**
 * Hubspot Engagements.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Engagements.
	 */
	class HubSpot_Engagements extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Create an engagement.
		 *
		 * For the sake of verbosity, $type is potentially included twice.
		 *
		 * @param  string $type       [description]
		 * @param  [type] $engagement [description]
		 * @param  array  $metadata   [description]
		 * @return [type]             [description]
		 */
		public function create_engagement( string $type, $engagement = array(), $metadata = array(), $associations = array(), $attachments = array() ) {
			$engagement['type'] = $engagement['type'] ?? $type;

			$args = array(
				'engagement'   => $engagement,
				'associations' => $associations,
				'metadata'     => $metadata,
				'attachments'  => $attachments,
			);

			return $this->run( 'engagements/v1/engagements', $args, 'POST' );
		}

		/**
		 * [update_engagement description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/engagements/update_engagement-patch Documentation
		 * @param  [type] $engagement_id [description]
		 * @param  [type] $args          [description]
		 * @return [type]                [description]
		 */
		public function update_engagement( $engagement_id, $args ) {
			return $this->run( "engagements/v1/engagements/$engagement_id", $args, 'PATCH' );
		}

		/**
		 * [get_engagement description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/engagements/get_engagement Documentation
		 * @param  [type] $engagement_id [description]
		 * @return [type]                [description]
		 */
		public function get_engagement( $engagement_id ) {
			return $this->run( "engagements/v1/engagements/$engagement_id" );
		}


		/**
		 * list_engagements public function.
		 *
		 * @access public
		 * @param mixed $offset (default: null)
		 * @param int   $limit (default: 100)
		 * @return void
		 */
		public function list_engagements( $offset = null, $limit = 100 ) {
			$args = array(
				'limit' => $limit,
			);

			if ( $offset ) {
				$args['offset'] = $offset;
			}

			return $this->run( 'engagements/v1/engagements/paged', $args );
		}

		public function get_recent_engagements( $args ) {
			return $this->run( 'engagements/v1/engagements/recent/modified', $args );
		}

		public function delete_engagement( $args ) {
			return $this->run( 'engagements/v1/engagements/recent/modified', $args );
		}

		public function associate_engagement( $engagement_id, $object_type, $object_id ) {
			return $this->run( "engagements/v1/engagements/$engagement_id/associations/$object_type/$object_id", array(), 'PUT' );
		}

		/**
		 * list_associated_engagements public function.
		 *
		 * @access public
		 * @param mixed $object_type
		 * @param mixed $object_id
		 * @return void
		 */
		public function list_associated_engagements( $object_type, $object_id ) {
			return $this->run( "engagements/v1/engagements/associated/$object_type/$object_id/paged" );
		}

		public function get_engagement_dispositions() {
			return $this->run( 'calling/v1/dispositions' );
		}

	}

	new HubSpot_Engagements();

}
