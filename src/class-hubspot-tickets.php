<?php
/**
 * Hubspot Tickets.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Tickets.
	 */
	class HubSpot_Tickets extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Supports pagination through set_props.
		 *
		 * @param  array $properties By default, only the ID and a few other system
		 *                           fields are returned for the tickets. You can
		 *                           include ticket properties in the response by
		 *                           requesting them in the URL. This parameter can
		 *                           be included multiple times to request multiple
		 *                           properties. See the example for more details.
		 * @return [type]             [description]
		 */
		public function list_tickets( $properties = array() ) {
			$args = array();
			if ( ! empty( $properties ) ) {
				$args['properties'] = $properties;
			}

			return $this->run( 'crm-objects/v1/objects/tickets/paged', $args );
		}

		/**
		 * Retrieve a single ticket. By default, does not include a lot of properties.
		 *
		 * @param  string $ticket_id  The ID of the ticket to retrieve.
		 * @param  array  $properties By default, only the ID and a few other system
		 *                            fields are returned for the ticket. You can
		 *                            include ticket properties in the response by
		 *                            requesting them in the URL. This parameter can
		 *                            be included multiple times to request multiple
		 *                            properties. See the example for more details.
		 * @param  bool   $deleted    (Default: false) Include deleted records.
		 * @return [type]             [description]
		 */
		public function show_ticket( $ticket_id, $properties = array(), $deleted = false ) {
			$args = array(
				'includeDeletes' => $deleted,
			);

			if ( ! empty( $properties ) ) {
				$args['properties'] = $properties;
			}

			return $this->run( 'crm-objects/v1/objects/tickets/' . $ticket_id, $args );
		}

		/**
		 * show_tickets public function.
		 *
		 * @access public
		 * @param mixed $ticket_ids
		 * @param array $properties (default: array())
		 * @param bool  $deleted (default: false)
		 * @return void
		 */
		public function show_tickets( $ticket_ids, $properties = array(), $deleted = false ) {
			if ( gettype( $ticket_ids ) == 'string' ) {
				$ticket_ids = explode( ',', $ticket_ids );
			}

			$url = 'crm-objects/v1/objects/tickets/batch-read?includeDeletes=' . ( $deleted ? 'true' : 'false' );

			$args = array(
				'ids' => $ticket_ids,
				// 'includeDeletes' => $deleted
			);

			if ( ! empty( $properties ) ) {
				$args['properties'] = $properties;
				$url                = add_query_arg( array( 'properties' => $properties ), $url );
			}

			return $this->run( $url, $args, 'POST' ); // Note: is actually a get.
		}

		/**
		 * Create a ticket.
		 *
		 * @param  mixed  $contact_id  Either an int or an int-like string. The ID of
		 *                             the contact this is being created for.
		 * @param  string $status      The status of the ticket. Ie, 'NEW', 'WAITING',
		 *                             'CLOSED', or 'OPEN' (or others?).
		 * @param  string $source_type (Default: 'EMAIL') The source type of the ticket,
		 *                             such as 'EMAIL', 'CHAT', or 'PHONE'.
		 * @param  array  $properties  An array of any additional properties you wish
		 *                             to set for the ticket, in the format:
		 *                             array(
		 *                               array(
		 *                                 'name' => '<property_name>',
		 *                                 'value' => '<property_value>'
		 *                               ),
		 *                               array(
		 *                                 ... etc
		 *                               )
		 *                             )
		 * @return object              The created ticket?
		 */
		public function create_ticket( $contact_id, string $status, string $source_type = 'EMAIL', array $properties = array() ) {
			$properties = array_merge(
				array(
					array(
						'name'  => 'source_type',
						'value' => $source_type,
					),
					array(
						'name'  => 'hs_pipeline_stage',
						'value' => $status,
					),
					array(
						'name'  => 'created_by',
						'value' => $contact_id,
					),
				),
				$properties
			);

			return $this->run( 'crm-objects/v1/objects/tickets', $properties, 'POST' );
		}

		/**
		 * Create a bunch of tickets.
		 *
		 * Expects an array of ticket objects that are similar to the object we created
		 * in the create_ticket public function.
		 *
		 * Each must include source_type, status, and created_by.
		 *
		 * @param  array $tickets An array of tickets.
		 * @return object          The response.
		 */
		public function create_tickets( array $tickets ) {
			return $this->run( 'crm-objects/v1/objects/tickets/batch-create', $ticket, 'POST' );
		}

		/**
		 * Update a ticket.
		 *
		 * @param  mixed $ticket_id  The ID of the ticket.
		 * @param  array $properties An array of properties (following name and value
		 *                           format) that you wish to update.
		 * @return object             The updated ticket.
		 */
		public function update_ticket( $ticket_id, array $properties ) {
			return $this->run( 'crm-objects/v1/objects/tickets/' . $ticket_id, $properties, 'PUT' );
		}

		/**
		 * Update a group of tickets.
		 *
		 * @link https://developers.hubspot.com/docs/methods/tickets/batch-update-tickets
		 *
		 * @param  array $objects An array of objects to be updated.
		 * @return object          The response.
		 */
		public function update_tickets( array $objects ) {
			return $this->run( 'crm-objects/v1/objects/tickets/batch-update', $objects, 'POST' ); // Is this not supposed to be put?
		}

		/**
		 * Deletes a ticket. Note: this is permanent, cannot be undone, and the
		 * ticket cannot be updated post-delete.
		 *
		 * @param  mixed $ticket_id The ID of the ticket to be deleted.
		 * @return object            A 204 No Content response.
		 */
		public function delete_ticket( $ticket_id ) {
			return $this->run( 'crm-objects/v1/objects/tickets/' . $ticket_id, array(), 'DELETE' );
		}

		/**
		 * Deletes a bunch of tickets. Note2: this is permanent, blah blah see delete_ticket.
		 *
		 * @param  array $ticket_ids [description]
		 * @return [type]             [description]
		 */
		public function delete_tickets( $ticket_ids = array() ) {
			if ( gettype( $ticket_ids ) == 'string' ) {
				$ticket_ids = explode( ',', $ticket_ids );
			}

			$args = array(
				'ids' => $ticket_ids,
			);

			return $this->run( 'crm-objects/v1/objects/tickets/batch-delete', $args, 'POST' );
		}

		/**
		 * Get a log of changes for tickets
		 *
		 * Get a list of changes to ticket objects. Returns 1000 (or fewer) changes,
		 * starting with the least recent change.
		 *
		 * This endpoint is designed to be polled periodically, allowing your integration
		 * to keep track of which objects have been updated so that you can get the
		 * details of those updated objects.
		 *
		 * After each request, the timestamp, changeType, and objectId of the most
		 * recently changed record (which will be the last record in the returned list
		 * of changes) should be stored by your integration, as you can use those values
		 * to get changes that occurred later, allowing you to pull only changes that
		 * occurred after your last polling request. All three values must be stored,
		 * as the combination of those values is what your integration needs to use to
		 * get changes that occurred after your last polling attempt. See the example
		 * for more details.
		 *
		 * $args supports 'timestamp', 'changeType', and 'objectId'.
		 *
		 * 'timestamp' => The timestamp of the last change you pulled.
		 *                Note: The timestamp parameter can be used by itself, but the
		 *                results will be inclusive, meaning you may see changes that
		 *                you saw in a previous request if there was a change at the
		 *                provided timestamp. You should only use the timestamp by
		 *                itself if you haven't polled for changes before and don't
		 *                need changes previous to the timestamp you're including
		 *                (for example, after syncing all existing tickets).
		 * 'changeType'=> The last changeType you pulled.
		 * 'objectId' =>  The ID of the last object you received changes for.
		 *
		 * @param  array $args Additional arguments to filter.
		 * @return object       A list of changes.
		 */
		public function get_ticket_changes( array $args = array() ) {
			return $this->run( 'crm-objects/v1/change-log/tickets', $args );
		}


	}

	new HubSpot_Tickets();

}
