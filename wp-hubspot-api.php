<?php
/**
 * WP HubSpot API
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

/*
* Plugin Name: WP Hubspot API
* Plugin URI: https://github.com/wp-api-libraries/wp-hubspot-api
* Description: Perform API requests to Hubspot in WordPress.
* Author: WP API Libraries
* Version: 1.0.0
* Author URI: https://wp-api-libraries.com
* GitHub Plugin URI: https://github.com/wp-api-libraries/wp-hubspot-api
* GitHub Branch: master
* Text Domain: wp-hubspot-api
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'HubSpotAPI' ) ) {

	/**
	 * HubSpot API Class.
	 */
	class HubSpotAPI {

		/**
		 * HTTP request arguments.
		 *
		 * (default value: array())
		 *
		 * @var array
		 * @access protected
		 */
		protected $args = array();

		/**
		 * api_key
		 *
		 * @var mixed
		 * @access protected
		 * @static
		 */
		static protected $api_key;

		/**
		 * oauth_token
		 *
		 * @var mixed
		 * @access protected
		 * @static
		 */
		static protected $oauth_token;

		/**
		 * BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://api.hubapi.com/';

		/**
		 * Route being called.
		 *
		 * @var string
		 */
		protected $route = '';

		/**
		 * __construct function.
		 *
		 * @access public
		 * @param mixed $api_key
		 * @return void
		 */
		function __construct( $api_key = null, $oauth_token = null ) {
			static::$api_key     = $api_key;
			static::$oauth_token = $oauth_token;
		}

		/**
		 * Prepares API request.
		 *
		 * @param  string $route   API route to make the call to.
		 * @param  array  $args    Arguments to pass into the API call.
		 * @param  array  $method  HTTP Method to use for request.
		 * @return self            Returns an instance of itself so it can be chained to the fetch method.
		 */
		protected function build_request( $route, $args = array(), $method = 'GET' ) {
			// Start building query.
			$this->set_headers();

			// Merge args with body.
			if( isset( $this->args['body'] ) ){
				$args = array_merge( $args, $this->args['body'] );
				$this->args['body'] = array(); // Just in case.
			}

			$this->args['method'] = $method;
			$this->route          = $route;

			if ( ! empty( static::$api_key ) ) {
				$this->route = add_query_arg( 'hapikey', static::$api_key, $this->route );
			}

			// Generate query string for GET requests.
			if ( 'GET' === $method ) {
				$this->route = add_query_arg( array_filter( $args ), $this->route );

				// Hubspot api is jank and doesnt use proper URL encode standards... So we must jank it up.
				$this->route = preg_replace( '/\%5B\d+\%5D/', '', $this->route );
			} elseif ( 'application/json' === $this->args['headers']['Content-Type'] ) {
				$this->args['body'] = wp_json_encode( $args );
			} else {
				$this->args['body'] = $args;
			}

			return $this;
		}

		/**
		 * run function.
		 *
		 * @access private
		 * @param mixed $route
		 * @param array $args (default: array())
		 * @param string $method (default: 'GET')
		 * @return void
		 */
		private function run( $route, $args = array(), $method = 'GET' ) {
			return $this->build_request( $route, $args, $method )->fetch();
		}


		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @return array|WP_Error Request results or WP_Error on request failure.
		 */
		protected function fetch() {
			// Make the request.
			// pp( $this->base_uri . $this->route, $this->args );
			$response = wp_remote_request( $this->base_uri . $this->route, $this->args );

			// Retrieve Status code & body.
			$code = wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ) );

			$this->clear();

			// Return WP_Error if request is not successful.
			if ( ! $this->is_status_ok( $code ) ) {
				return new WP_Error( 'response-error', sprintf( __( 'Status: %d', 'wp-hubspot-api' ), $code ), $body );
			}

			return $body;
		}

		/**
		 * Set properties and pagination settings.
		 *
		 * Allows cleaner method creation/calls.
		 *
		 * For example, to get 20 contacts offset by cid, and get properties, you could
		 *   $hubspotapi->sp( 20, null, array( 'hs_lead_status', 'firstname', 'lastname',
		 *   'hubspot_owner_id', 'lifecyclestage' ), array( 'vidOffset' => $cid ) )->get_all_contacts();
		 *
		 * @param integer $limit      [description]
		 * @param [type]  $offset     [description]
		 * @param [type]  $properties [description]
		 * @return HubspotAPI         $this.
		 */
		public function set_props( $limit = 20, $offset = null, $properties = null, $alt_args = array() ){
			$args = array(
				'limit' => intval( $limit ),
				'offset' => $offset,
				'property' => $properties
			);

			$this->args['body'] = $this->filter_args( $alt_args, $args );

			return $this;
		}

		/**
		 * Set request headers.
		 */
		protected function set_headers() {
			// Set request headers.
			$this->args['headers'] = array(
				'Content-Type' => 'application/json',
			);

			if ( ! empty( static::$oauth_token ) ) {
				$this->args['headers'] = array(
					'Authorization' => 'Bearer ' . static::$oauth_token,
				);
			}

		}

		/**
		 * Clear query data.
		 */
		protected function clear() {
			$this->args = array();
		}

		/**
		 * Check if HTTP status code is a success.
		 *
		 * @param  int $code HTTP status code.
		 * @return boolean       True if status is within valid range.
		 */
		protected function is_status_ok( $code ) {
			return ( 200 <= $code && 300 > $code );
		}

		/**
		 * Takes the elements of one or more arrays, merges them together and
		 * filters empty and null values out of the resulting array.
		 *
		 * @param  array  $args A variable amount of arrays to merge and filter through.
		 * @return array        A single array of filtered args.
		 */
		private function filter_args( array ...$args ){
			// Merges arrays and removes empty and null values.
	    return array_filter( array_merge( ...$args ) );
	  }

		/* Oauth. */


		/* Calendar. */

		/**
		 * Calendar - List content events.
		 *
		 * @access public
		 * @param mixed $start_date Start Date.
		 * @param mixed $end_date End Date.
		 * @param mixed $limit (default: null) Limit.
		 * @param mixed $offset (default: null) Offset.
		 * @param mixed $content_category (default: null) Content Category.
		 * @param mixed $campaign_guid (default: null) Campaign GUID.
		 * @param mixed $include_no_campaigns (default: null) Include No Compaigns.
		 * @return void
		 */
		function get_content_events( $start_date, $end_date, $limit = null, $offset = null, $content_category = null, $campaign_guid = null, $include_no_campaigns = null ) {

			$request = 'calendar/v1/events/content';
			return $this->run( $request );
		}

		/**
		 * Get Social Events.
		 *
		 * @access public
		 * @param mixed $start_date Start Date.
		 * @param mixed $end_date End Date.
		 * @param mixed $limit (default: null) Limit.
		 * @param mixed $offset (default: null) Offset.
		 * @param mixed $campaign_guid (default: null) Campaign GUID.
		 * @param mixed $include_no_campaigns (default: null) Include No Campaigns.
		 * @return void
		 */
		function get_social_events( $start_date, $end_date, $limit = null, $offset = null, $campaign_guid = null, $include_no_campaigns = null ) {

			$request = 'calendar/v1/events/social';
			return $this->run( $request );

		}

		/**
		 * get_task_events function.
		 *
		 * @access public
		 * @param mixed $start_date Start Date.
		 * @param mixed $end_date End Date.
		 * @param mixed $limit (default: null) Limit.
		 * @param mixed $offset (default: null) Offset.
		 * @param mixed $campaign_guid (default: null) Campaign GUID.
		 * @param mixed $include_no_campaigns (default: null) Include No Campaigns.
		 * @return void
		 */
		function get_task_events( $start_date, $end_date, $limit = null, $offset = null, $campaign_guid = null, $include_no_campaigns = null ) {
			$request = 'calendar/v1/events/task';
			return $this->run( $request );
		}

		/**
		 * Create Task.
		 *
		 * @access public
		 * @return void
		 */
		function create_task() {

			$request = 'calendar/v1/events/task';
			return $this->run( $request );
		}

		/**
		 * Get Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		function get_task( $task_id ) {

			$request = 'calendar/v1/events/task/' . $task_id . '';
			return $this->run( $request );

		}

		/**
		 * Update Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		function update_task( $task_id ) {
			$request = 'calendar/v1/events/task/' . $task_id . '';
			return $this->run( $request );
		}

		/**
		 * Delete Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		function delete_task( $task_id ) {
			$request = 'calendar/v1/events/task/' . $task_id . '';
			return $this->run( $request );
		}

		/* Companies. */

		/**
		 * Add Company.
		 *
		 * @access public
		 * @return void
		 */
		function create_company( $properties ) {
			if( ! isset( $properties['properties'] ) ){
				$properties = array(
					'properties' => $properties
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
		function update_company( $company_id, $properties ) {
			if( ! isset( $properties['properties'] ) ){
				$properties = array(
					'properties' => $properties
				);
			}
			return $this->run( "companies/v2/companies/$company_id", $properties, 'PUT' );
		}

		/**
		 * Update a group of Companies.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @return void
		 */
		function update_company_group( $company_id, $batch ) {
			return $this->run( "companies/v1/batch-async/update", $batch, 'PUT' );
		}

		/**
		 * Delete Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @return void
		 */
		function delete_company( $company_id ) {
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
		function get_companies( int $limit = null, int $offset = null, $properties = null, $propertiesWithHistory = null ) {
			$args = $this->filter_args( compact('limit', 'offset', 'properties', 'propertiesWithHistory' ) );

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
		function get_recently_modified_companies( $offset = '', $count = '' ) {
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
		function get_recently_created_companies( $offset = '', $count = '' ) {
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
		function get_company_by_domain( $domain ) {
			return $this->run( "companies/v2/companies/domain/$domain" );
		}

		/**
		 * Get Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @return void
		 */
		function get_company( $company_id ) {
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
		function get_company_contacts( $company_id, $vidoffset = '', $count = '' ) {
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
		function get_company_contacts_ids( $company_id, $vidoffset = '', $count = '' ) {
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
		function add_contact_to_company( $company_id, $contact_vid ) {
			return $this->run( "engagements/v1/engagements/$company_id/associations/contact/$contact_vid" );
		}

		/**
		 * Remove Contact from Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @param mixed $contact_vid Contact VID.
		 * @return void
		 */
		function remove_contact_from_company( $company_id, $contact_vid ) {
			return $this->run( "companies/v2/companies/$company_id/contacts/$contact_vid", array(), 'DELETE' );
		}

		/* Companies Properties. */

		function add_company_property() {

		}

		function update_company_property() {

		}

		function delete_company_property() {

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
		function get_all_company_properties() {
			return $this->build_request( 'properties/v1/companies/properties' )->fetch();
		}

		/**
		 * get_company_property function.
		 * @see https://developers.hubspot.com/docs/methods/companies/get_company_property
		 *
		 * @access public
		 * @return void
		 */
		function get_company_property( $property_name ) {
			return $this->build_request( 'properties/v1/companies/properties/named/' . $property_name )->fetch();

		}

		/**
		 * add_company_property_group function.
		 *
		 * @see https://developers.hubspot.com/docs/methods/companies/create_company_property_group
		 * @access public
		 * @return void
		 */
		function add_company_property_group() {
			return $this->build_request( 'properties/v1/companies/groups/' . $property_name, 'POST' )->fetch();
		}

		function update_company_property_group() {

		}

		function delete_company_property_group() {

		}

		function get_company_property_groups() {

		}

		/* Contacts. */


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
		function create_contact( $properties ) {
			if( ! isset( $properties['properties'] ) ){
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

		function update_contact( $contact_id, $properties ) {
			$args = array(
				'properties' => $properties
			);
			return $this->run( 'contacts/v1/contact/vid/'. $contact_id .'/profile', $args, 'POST' );
		}


		/**
		 * Create or Update Contact.
		 *
		 * @access public
		 * @param mixed $email
		 * @return void
		 */
		function create_or_update_contact( $email ) {
			$request = 'contacts/v1/contact/createOrUpdate/email/' . $email . '/';
			return $this->run( $request );
		}

		/**
		 * Create or Update Batch Contacts.
		 *
		 * @access public
		 * @return void
		 */
		function create_or_update_batch_contacts() {
			$request = 'contacts/v1/contact/batch/';
			return $this->run( $request );
		}

		function delete_contact() {
			// https://api.hubapi.com/contacts/v1/contact/vid/61571?hapikey=demo
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
		function get_all_contacts( int $count = null, int $contact_offset = null, $property = null, string $property_mode = null, string $form_submit_mode = null, bool $list_memberships = null ) {

			$args = $this->filter_args(array(
				'count' => $count,
				'vidOffset' => $contact_offset,
				'property' => $property,
				'propertyMode' => $property_mode,
				'formSubmissionMode' => $form_submit_mode,
				'showListMemberships' => $list_memberships
			));

			return $this->run( 'contacts/v1/lists/all/contacts/all', $args );
		}

		/**
		 * Get a single contact, by visitor_id (vid).
		 *
		 * $args supports additional all optional properties:
		 *   Property
		 *     Used in the request URL	By default, you will get all properties that
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
		function get_contact( $contact_id, $args = array() ) {
			return $this->run( 'contacts/v1/contact/vid/' . $contact_id . '/profile', $args );
		}

		function get_contact_by_email( $contact_email ) {
			return $this->run( 'contacts/v1/contact/email/' . $contact_email . '/profile' );
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
		 *     Used in the request URL	This parameter lets you specify the amount of
		 *     contacts to return in your API call. The default for this parameter
		 *     (if it isn't specified) is 20 contacts. The maximum amount of contacts
		 *     you can have returned to you via this parameter is 100.
		 *   timeOffset:
		 *     Used in the request URL	Used in conjunction with the vidOffset paramter
		 *     to page through the recent contacts. Every call to this endpoint will
		 *     return a time-offset value. This value is used in the timeOffset
		 *     parameter of the next call to get the next page of contacts.
		 *   vidOffset:
		 *     Used in the request URL	Used in conjunction with the timeOffset paramter
		 *     to page through the recent contacts. Every call to this endpoint will
		 *     return a vid-offset value. This value is used in the vidOffset parameter
		 *     of the next call to get the next page of contacts.
		 *   property:
		 *     Used in the request URL	If you include the "property" parameter, then
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
		 * @param  array  $args [description]
		 * @return [type]       [description]
		 */
		function get_recent_contacts( $args = array() ) {
			return $this->run( 'contacts/v1/lists/recently_updated/contacts/recent', $args );
		}

		function get_contact_by_token( $contact_token ) {
			// http://api.hubapi.com/contacts/v1/contact/utk/f844d2217850188692f2610c717c2e9b/profile?hapikey=demo
		}

		function get_batch_contacts_by_token() {
			// https://api.hubapi.com/contacts/v1/contact/utks/batch/?utk=f844d2217850188692f2610c717c2e9b&utk=j94344d22178501692f2610c717c2e9b&hapikey=demo
		}

		function search_contacts( $search_query ) {
			return $this->run( 'contacts/v1/search/query', array( 'q' => $search_query ) );
			// https://api.hubapi.com/contacts/v1/search/query?q=testingapis&hapikey=demo
		}

		function merge_contacts( $contact_id, $vid_to_merge ) {
			// https://api.hubapi.com/contacts/v1/contact/merge-vids/1343724/?hapikey=demo
		}

		/* Contact Lists. */

		/* Content Properties. */

		/* Email Events. */

		/**
		 * Event.
		 *
		 * @access public
		 * @param mixed $event_id Event ID.
		 * @param mixed $contact_email (default: null) Contact Email.
		 * @param mixed $contact_revenue (default: null) Contact Revenue.
		 * @param mixed $any_contact_property (default: null) Any Contact Property.
		 * @return void
		 */
		function event( $event_id, $contact_email = null, $contact_revenue = null, $any_contact_property = null ) {

		}

		/* Keywords. */


		/**
		 * Get Keyword list.
		 *
		 * @access public
		 * @param mixed $search Search.
		 * @return void
		 */
		function get_keyword_list( $search ) {
			$request = 'keywords/v1/keywords';
			return $this->run( $request );
		}

		/**
		 * Get Keyword.
		 *
		 * @access public
		 * @param mixed $keyword_guid Keyword GUID.
		 * @return void
		 */
		function get_keyword( $keyword_guid ) {
			return $this->run( 'keywords/v1/keywords/' . $keyword_guid );
		}

		function add_keyword() {

		}

		/**
		 * Delete Keyword.
		 *
		 * @access public
		 * @param mixed $keyword_guid Keyword GUID.
		 * @return void
		 */
		function delete_keyword( $keyword_guid ) {
			return $this->run( 'keywords/v1/keywords/' . $keyword_guid );
		}

		/* Owners. */

		function get_owners() {

		}

		/* Deals. */


		/**
		 * Add Deal.
		 *
		 * @access public
		 * @param mixed $deal_json Deal JSON.
		 * @return void
		 */
		function add_deal( $deal_json ) {

		}

		/**
		 * Update Deal.
		 *
		 * @access public
		 * @param mixed $deal_id Deal ID.
		 * @param mixed $deal_json Deal JSON.
		 * @return void
		 */
		function update_deal( $deal_id, $deal_json ) {

		}

		function get_deal() {
			// 'https://api.hubapi.com/deals/v1/deal/3865198'
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
		function get_all_deals( $limit = null, $offset = null, $properties = null, $properties_with_history = null, $associations ) {
			return $this->run( 'deals/v1/deal/paged' );
		}

		function get_recently_modified_deals() {
			// https://api.hubapi.com/deals/v1/deal/recent/modified?hapikey=demo
		}

		function get_recent_created_deals() {
			// https://api.hubapi.com/deals/v1/deal/recent/created?hapikey=demo'
		}

		function delete_deal() {
			// Example URL: 'https://api.hubapi.com/deals/v1/deal/10444744?hapikey=demo'
		}

		function associate_deal() {
			// 'https://api.hubapi.com/deals/v1/deal/1126609/associations/CONTACT?id=394455&hapikey=demo'
		}

		function delete_deal_association() {
			// 'https://api.hubapi.com/deals/v1/deal/1126609/associations/CONTACT?id=394455&hapikey=demo'
		}

		function get_associated_deals() {
			// https://api.hubapi.com/deals/v1/deal/associated/contact/1002325/paged?hapikey=demo&includeAssociations=true&limit=10&properties=dealname
		}

		/* Deal Pipelines. */

		/**
		 * Get Deal Pipelines.
		 *
		 * @access public
		 * @param mixed $pipeline_id Pipeline ID.
		 * @return void
		 */
		function get_deal_pipelines( $pipeline_id ) {

		}

		/**
		 * Get all Deal Pipelines.
		 *
		 * @access public
		 * @return void
		 */
		function get_all_deal_pipelines() {

		}

		function add_deal_pipeline() {

		}

		function update_deal_pipeline() {

		}

		function delete_deal_pipeline() {

		}

		/* Deal Properties. */

		function add_deal_property() {

		}

		function update_deal_property() {

		}

		function delete_deal_property() {

		}

		function get_all_deal_properties() {

		}

		function get_deal_property() {

		}

		function add_deal_property_group() {

		}

		function update_deal_property_group() {

		}

		function delete_deal_property_group() {

		}

		function get_deal_property_groups() {

		}

		function get_deal_property_group() {

		}

		/* Timeline. */

		function add_or_update_timeline_event() {

		}

		function get_timeline_event_types() {

		}

		function add_new_timeline_event_type() {

		}

		function update_timeline_event_type() {

		}

		function delete_timeline_event_type() {

		}

		function get_properties_for_timeline_event_type() {

		}

		function add_property_for_timeline_event_type() {

		}

		function update_property_for_timeline_event_type() {

		}

		function delete_property_for_timeline_event_type() {

		}

		/**
		 * Create a new Timeline Event Type.
		 *
		 * @access public
		 * @param mixed $app_id APP ID.
		 * @param mixed $name Name.
		 * @param mixed $header_template (default: null) Header Template.
		 * @param mixed $detail_template (default: null) Detail Template.
		 * @param mixed $object_type (default: null) Object Type.
		 * @return void
		 */
		function create_timeline_event_type( $app_id, $name, $header_template = null, $detail_template = null, $object_type = null ) {
			return $this->run( 'integrations/v1/' . $app_id . '/timeline/event-types' );
		}


		/* Transactional. */


		/**
		 * List SMTP API Tokens.
		 *
		 * @access public
		 * @return void
		 */
		function get_smtp_tokens() {
			$request = 'email/public/v1/smtpapi/tokens';
			return $this->run( $request );
		}

		/**
		 * Add SMTP Token.
		 *
		 * @access public
		 * @param mixed $createdby Created By.
		 * @param mixed $campaign_name Campaign Name.
		 * @return void
		 */
		function add_smtp_token( $createdby, $campaign_name ) {
			$request = 'email/public/v1/smtpapi/tokens';
			return $this->run( $request );
		}

		function reset_smtp_api_token( $user_name ) {

		}

		/* Workflows. */

		/* Webhooks. */

		function list_subscriptions( $app_id ){
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions' );
		}

		function create_subscription( $app_id, $subsription_type, $property_name, $enabled = true ){
			$subscription = array(
				'subscriptionDetails' => array(
					'subscriptionType' => $subscription_type,
					'propertyName' => $property_name
				),
				'enabled' => $enabled
			);
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions', $subscription, 'POST' );
		}

		function update_subscription( $app_id, $subscription_id, bool $enabled ){
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions/' . $subscription_id, array( 'enabled' => $enabled ), 'PUT' );
		}

		function delete_subscription( $app_id, $subscription_id ){
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions/' . $subscription_id );
		}

		function get_webhook_settings( $app_id ){
			return $this->run( 'webhooks/v1/' . $app_id . '/settings' );
		}

		function update_settings( $app_id, $webhookUrl, $maxConcurrentRequests ){
			$args = array(
				'webhookUrl' => $webhookUrl,
				'maxConcurrentRequests' => $maxConcurrentRequests
			);
			return $this->run( 'webhooks/v1/' . $app_id . '/settings', $args, 'PUT' );
		}

	}

}
