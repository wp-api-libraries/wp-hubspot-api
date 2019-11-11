<?php
/**
 * Hubspot CRM Associations.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot CRM_Associations.
	 */
	class HubSpot_CRM_Associations extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * get_associations_for_crm_object function.
		 *
		 * @docs https://developers.hubspot.com/docs/methods/crm-associations/get-associations
		 *
		 * @access public
		 * @param mixed  $object_id
		 * @param mixed  $definition_id
		 * @param string $limit (default: '')
		 * @param string $offset (default: '')
		 * @return void
		 */
		public function get_associations_for_crm_object( $object_id, $definition_id ) {
			return $this->run( 'crm-associations/v1/associations/' . $object_id . '/HUBSPOT_DEFINED/' . $definition_id );
		}

		/**
		 * Create an association between two objects.
		 *
		 * Contact to ticket        15
		 * Ticket to contact        16
		 * Ticket to engagement 		17
		 * Engagement to ticket 		18
		 * Deal to line item        19
		 * Line item to deal        20
		 * Company to ticket        25
		 * Ticket to company        26
		 *
		 * @param  [type] $from_id    [description]
		 * @param  [type] $to_id      [description]
		 * @param  [type] $definition [description]
		 * @param  string $category   [description]
		 * @return [type]             [description]
		 */
		public function create_association( $from_id, $to_id, $definition, $category = 'HUBSPOT_DEFINED' ) {
			return $this->run(
				'crm-associations/v1/associations',
				array(
					'fromObjectId' => $from_id,
					'toObjectId'   => $to_id,
					'definitionId' => intval( $definition ),
					'category'     => $category,
				),
				'PUT'
			);
		}

	}

	new HubSpot_CRM_Associations();

}
