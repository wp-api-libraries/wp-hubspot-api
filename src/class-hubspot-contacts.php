<?php
/**
 * WP HubSpot API
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */



/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;


	/**
	 * HubSpot_Contacts.
	 */
	class HubSpot_Contacts extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Create a new contact.
		 *
		 * Create a new contact in HubSpot with a simple HTTP POST to the Contacts API. The contact will be created
		 * instantly inside of HubSpot, and will be assigned a unique ID (vid) that can be used to look up the contact
		 * inside of HubSpot later.
		 *
		 * @api POST
		 * @see https://developers.hubspot.com/docs/methods/contacts/create_contact Documentation
		 * @param  array $properties  An array containing one or more user property array
		 * @return array              New contact info.
		 */
		public function create_contact( $properties ) {
			if ( ! isset( $properties['properties'] ) ) {
				$properties = array(
					'properties' => $properties,
				);
			}
			return $this->run( 'contacts/v1/contact', $properties, 'POST' );
		}

		/**
		 * Update Contact.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @return void
		 */

		public function update_contact( $contact_id, $properties ) {
			$args = array(
				'properties' => $properties,
			);
			return $this->run( "contacts/v1/contact/vid/$contact_id/profile", $args, 'POST' );
		}


		/**
		 * Create or Update Contact.
		 *
		 * @access public
		 * @param mixed $email
		 * @return void
		 */
		public function create_or_update_contact( $email, $properties ) {
			$args = array(
				'properties' => $properties,
			);
			return $this->run( "contacts/v1/contact/createOrUpdate/email/$email", $args, 'POST' );
		}

		/**
		 * Create or Update Batch Contacts.
		 *
		 * @access public
		 * @return void
		 */
		public function create_or_update_batch_contacts( $batch ) {
			return $this->run( 'contacts/v1/contact/batch/', $batch );
		}

		/**
		 * delete_contact public function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @return void
		 */
		public function delete_contact( $contact_id ) {
			return $this->run( "/contacts/v1/contact/vid/$contact_id", array(), 'DELETE' );
		}

		/**
		 * Get all contacts
		 *
		 * For a given portal, return all contacts that have been created in the portal.
		 *
		 * A paginated list of contacts will be returned to you, with a maximum of 100 contacts per page.
		 *
		 * Please Note There are 2 fields here to pay close attention to: the "has-more" field that will let you know
		 * whether there are more contacts that you can pull from this portal, and the "vid-offset" field which will let
		 * you know where you are in the list of contacts. You can then use the "vid-offset" field in the "vidOffset"
		 * parameter described below.
		 *
		 * @api GET
		 * @see https://developers.hubspot.com/docs/methods/contacts/get_contacts Documentation
		 *
		 * @param  int    $count            This parameter lets you specify the amount of contacts to return in your API
		 *                                  call. The default for this parameter (if it isn't specified) is 20 contacts.
		 *                                  The maximum amount of contacts you can have returned to you via this parameter
		 *                                  is 100.
		 * @param  int    $contact_offset   Used to page through the contacts. Every call to this endpoint will return a
		 *                                  vid-offset value. This value is used in the vidOffset parameter of the next
		 *                                  call to get the next page of contacts.
		 * @param  string $property         By default, only a few standard properties will be included in the response
		 *                                  data. If you include the 'property' parameter, then you will instead get the
		 *                                  specified property in the response. This parameter may be included multiple
		 *                                  times to specify multiple properties. NOTE: Contacts only store data for
		 *                                  properties with a value, so records with no value for a property will not
		 *                                  include that property, even if the property is specified in the request URL.
		 * @param  string $property_mode    One of “value_only” or “value_and_history” to specify if the current value for a
		 *                                  property should be fetched, or the value and all the historical values for that
		 *                                  property. Default is “value_only”.
		 * @param  string $form_submit_mode One of “all”, “none”, “newest”, “oldest” to specify which form submissions
		 *                                  should be fetched. Default is “newest”.
		 * @param  bool   $list_memberships Boolean "true" or "false" to indicate whether current list memberships should be
		 *                                  fetched for the contact. Default is false.
		 * @return array                    Array of contact info.
		 */
		public function get_all_contacts( int $count = null, int $contact_offset = null, $property = null, string $property_mode = null, string $form_submit_mode = null, bool $list_memberships = null ) {

			$args = $this->filter_args(
				array(
					'count'               => $count,
					'vidOffset'           => $contact_offset,
					'property'            => $property,
					'propertyMode'        => $property_mode,
					'formSubmissionMode'  => $form_submit_mode,
					'showListMemberships' => $list_memberships,
				)
			);

			return $this->run( 'contacts/v1/lists/all/contacts/all', $args );
		}

		/**
		 * Get recently updated and created contacts
		 *
		 * For a given portal, return all contacts that have been recently updated or
		 * created.
		 *
		 * A paginated list of contacts will be returned to you, with a maximum of 100
		 * contacts per page, as specified by the "count" parameter. The endpoint only
		 * scrolls back in time 30 days.
		 *
		 * Please Note There are 3 fields here to pay close attention to: the "has-more"
		 * field that will let you know whether there are more contacts that you can pull
		 * from this portal, and the "vid-offset" and "time-offset" fields which will
		 * let you know where you are in the list of contacts. You can then use the
		 * "vid-offset" and "time-offset" fields in the "vidOffset" and "timeOffset"
		 * parameters described below.
		 *
		 * The response is sorted in descending order by last modified date; the most
		 * recently modified record is returned first.
		 *
		 * $args accepts all optional values in a key => value form:
		 *   count:
		 *     Used in the request URL  This parameter lets you specify the amount of
		 *     contacts to return in your API call. The default for this parameter
		 *     (if it isn't specified) is 20 contacts. The maximum amount of contacts
		 *     you can have returned to you via this parameter is 100.
		 *   timeOffset:
		 *     Used in the request URL  Used in conjunction with the vidOffset paramter
		 *     to page through the recent contacts. Every call to this endpoint will
		 *     return a time-offset value. This value is used in the timeOffset
		 *     parameter of the next call to get the next page of contacts.
		 *   vidOffset:
		 *     Used in the request URL  Used in conjunction with the timeOffset paramter
		 *     to page through the recent contacts. Every call to this endpoint will
		 *     return a vid-offset value. This value is used in the vidOffset parameter
		 *     of the next call to get the next page of contacts.
		 *   property:
		 *     Used in the request URL  If you include the "property" parameter, then
		 *     the properties in the "contact" object in the returned data will only
		 *     include the property or properties that you request.
		 *
		 *     For our purposes, we accept an array or a single value. See get_all_contacts.
		 *   propertyMode:
		 *     One of “value_only” or “value_and_history” to specify if the current
		 *     value for a property should be fetched, or the value and all the
		 *     historical values for that property. Default is “value_only”.
		 *   formSubmissionMode:
		 *     One of “all”, “none”, “newest”, “oldest” to specify which form submissions
		 *     should be fetched. Default is “newest”.
		 *   showListMemberships:
		 *     Boolean "true" or "false" to indicate whether current list memberships
		 *     should be fetched for the contact. Default is false.
		 *
		 * @param  array $args [description]
		 * @return [type]       [description]
		 */
		public function get_recent_updated_contacts( $args = array() ) {
			return $this->run( 'contacts/v1/lists/recently_updated/contacts/recent', $args );
		}



				/**
				 * get_recent_created_contacts public function.
				 *
				 * @access public
				 * @param array $args (default: array())
				 * @return void
				 */
				public function get_recent_created_contacts( $args = array() ) {
					return $this->run( '/contacts/v1/lists/all/contacts/recent', $args );
				}


				/**
				 * Get a single contact, by visitor_id (vid).
				 *
				 * $args supports additional all optional properties:
				 *   Property
				 *     Used in the request URL  By default, you will get all properties that
				 *     the contact has values for. If you include the "property" parameter,
				 *     then the returned data will only include the property or properties
				 *     that you request. You can include this parameter multiple times to
				 *     specify multiple properties. The lastmodifieddate and associatedcompanyid
				 *     will always be included, even if not specified. Keep in mind that only
				 *     properties that have a value will be included in the response, even if
				 *     specified in the URL.
				 *
				 *     For our purposes, we accept both a string or an array of values under
				 *     the key 'property'. ie: 'property' => 'imforza_user_id' or
				 *     'property' => array( 'imforza_user_id', 'lastname', 'firstname' ).
				 *   Property Mode
				 *     One of “value_only” or “value_and_history” to specify if the current
				 *     value for a property should be fetched, or the value and all the
				 *     historical values for that property. Default is “value_and_history”.
				 *   Form Submission Mode
				 *     One of “all”, “none”, “newest”, “oldest” to specify which form submissions
				 *     should be fetched. Default is “all”.
				 *   List Memberships
				 *     Boolean "true" or "false" to indicate whether current list memberships
				 *     should be fetched for the contact. Default is true.
				 *
				 * @param  [type] $contact_id [description]
				 * @param  array  $args       [description]
				 * @return [type]             [description]
				 */
				public function get_contact( $contact_id, $args = array() ) {
					return $this->run( 'contacts/v1/contact/vid/' . $contact_id . '/profile', $args );
				}

				/**
				 * get_contact_by_email public function.
				 *
				 * @access public
				 * @param mixed $contact_email
				 * @param array $args (default: array())
				 * @return void
				 */
				public function get_contact_by_email( $contact_email, $args = array() ) {
					return $this->run( 'contacts/v1/contact/email/' . $contact_email . '/profile' );
				}

				/**
				 * get_contact_batch_by_email public function.
				 *
				 * @access public
				 * @param mixed $emails
				 * @param array $args (default: array())
				 * @return void
				 */
				public function get_contact_batch_by_email( $emails, $args = array() ) {
					$args['email'] = $emails;
					return $this->run( '/contacts/v1/contact/emails/batch/', $args );
				}

				/**
				 * get_contact_by_token public function.
				 *
				 * @access public
				 * @param mixed $contact_token
				 * @param array $args (default: array())
				 * @return void
				 */
				public function get_contact_by_token( $contact_token, $args = array() ) {
					return $this->run( "/contacts/v1/contact/utk/$contact_token/profile", $args );
				}

				/**
				 * search_contacts public function.
				 *
				 * @access public
				 * @param mixed $search_query
				 * @param array $args (default: array())
				 * @return void
				 */
				public function search_contacts( $search_query, $args = array() ) {
					$args['q'] = $search_query;
					return $this->run( 'contacts/v1/search/query', $args );
				}

				/**
				 * merge_contacts public function.
				 *
				 * @access public
				 * @param mixed $contact_id
				 * @param mixed $vid_to_merge
				 * @return void
				 */
				public function merge_contacts( $contact_id, $vid_to_merge ) {
					$args = array(
						'vidToMerge' => $vid_to_merge,
					);

					return $this->run( "/contacts/v1/contact/merge-vids/$contact_id/", $args, 'POST' );
				}

	}

	new HubSpot_Contacts();
