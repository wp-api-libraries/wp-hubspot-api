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
			if ( isset( $this->args['body'] ) ) {
				$args               = array_merge( $args, $this->args['body'] );
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
			} elseif ( 'application/json' === $this->args['headers']['Content-Type'] ) {
				$this->args['body'] = wp_json_encode( $args );
			} else {
				$this->args['body'] = $args;
			}

			// Hubspot api is jank and doesnt use proper URL encode standards... So we must jank it up.
			$this->route = preg_replace( '/\%5B\d+\%5D/', '', $this->route );

			return $this;
		}

		/**
		 * Run function.
		 *
		 * @access private
		 * @param mixed  $route
		 * @param array  $args (default: array())
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
			// pp( $this->base_uri . $this->route, $response );
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
		public function set_props( $count = 20, $offset = null, $properties = null, $alt_args = array() ) {
			$args = array(
				'count'    => intval( $count ),
				'limit'    => intval( $count ),
				'offset'   => $offset,
				'property' => $properties,
			);

			$this->args['body'] = $this->filter_args( $alt_args, $args );

			return $this;
		}

		/**
		 * sp function.
		 *
		 * @access public
		 * @param int   $count (default: 20)
		 * @param mixed $offset (default: null)
		 * @param mixed $properties (default: null)
		 * @param array $alt_args (default: array())
		 * @return void
		 */
		public function sp( $count = 20, $offset = null, $properties = null, $alt_args = array() ) {
			return $this->set_props( $count, $offset, $properties, $alt_args );
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
		 * @param  array $args A variable amount of arrays to merge and filter through.
		 * @return array        A single array of filtered args.
		 */
		private function filter_args( array ...$args ) {
			// Merges arrays and removes empty and null values.
			return array_filter( array_merge( ...$args ) );
		}

		/* Oauth. */

		function get_oauth_access_token( string $client_id, string $client_secret, string $redirect_uri, string $code ) {
			$this->build_request( 'oauth/v1/token', array(), 'POST' );

			$args = array(
				'grant_type'    => 'authorization_code',
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri'  => $redirect_uri,
				'code'          => $code,
			);

			$this->args['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
			$this->args['body']                    = $args;

			return $this->fetch();
		}

		function get_oauth_token_info( string $token ) {
			return $this->run( 'oauth/v1/access-tokens/' . $token );
		}

		/* Daily Usage */

		function check_daily_usage() {
			return $this->run( 'integrations/v1/limit/daily' );
		}

		/* Calendar. */

		/**
		 * Calendar - List content events.
		 *
		 * @access public
		 * @param string                                  $start_date Start Date.
		 * @param string                                  $end_date End Date.
		 * @param mixed  Optional args to send to request.
		 * @return void
		 */
		function get_content_events( $start_date, $end_date, $args = array() ) {
			$args['startDate'] = $start_date;
			$args['endDate']   = $end_date;

			return $this->run( 'calendar/v1/events/content', $args );
		}

		/**
		 * Get Social Events.
		 *
		 * @access public
		 * @param mixed $start_date Start Date.
		 * @param mixed $end_date End Date.
		 * @param mixed $args     Optional args.
		 */
		function get_social_events( $start_date, $end_date, $args = array() ) {
			$args['startDate'] = $start_date;
			$args['endDate']   = $end_date;

			return $this->run( 'calendar/v1/events/social', $args );
		}

		/**
		 * get_task_events function.
		 *
		 * @access public
		 * @param mixed $start_date Start Date.
		 * @param mixed $end_date End Date.
		 * @param mixed $args
		 */
		function get_task_events( $start_date, $end_date, $args = array() ) {
			$args['startDate'] = $start_date;
			$args['endDate']   = $end_date;

			return $this->run( 'calendar/v1/events/task', $args );
		}

		/**
		 * Get All Events.
		 *
		 * @access public
		 * @param mixed $start_date Start Date.
		 * @param mixed $end_date End Date.
		 * @param mixed $args     Optional args.
		 */
		function get_all_events( $start_date, $end_date, $args = array() ) {
			$args['startDate'] = $start_date;
			$args['endDate']   = $end_date;

			return $this->run( 'calendar/v1/events', $args );
		}

		/**
		 * Create Task.
		 *
		 * @access public
		 * @return void
		 */
		function create_task( $args ) {
			return $this->run( 'calendar/v1/events/task', $args, 'POST' );
		}

		/**
		 * Get Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		function get_task( $task_id ) {
			return $this->run( "calendar/v1/events/task/$task_id" );
		}

		/**
		 * Update Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		function update_task( $task_id, $args ) {
			return $this->run( "calendar/v1/events/task/$task_id", $args, 'PUT' );
		}

		/**
		 * Delete Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		function delete_task( $task_id ) {
			return $this->run( "calendar/v1/events/task/$task_id", array(), 'DELETE' );
		}

		/* Companies. */

		/**
		 * Add Company.
		 *
		 * @access public
		 * @return void
		 */
		function create_company( $properties ) {
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
		function update_company( $company_id, $properties ) {
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
		function update_company_group( $batch ) {
			return $this->run( 'companies/v1/batch-async/update', $batch, 'POST' );
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
		function remove_contact_from_company( $company_id, $contact_vid ) {
			return $this->run( "companies/v2/companies/$company_id/contacts/$contact_vid", array(), 'DELETE' );
		}

		/* Companies Properties. */
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
		function add_company_property( $args ) {
			return $this->run( 'properties/v1/companies/properties', $args, 'POST' );
		}

		/**
		 * update_company_property function.
		 *
		 * @access public
		 * @param mixed $args
		 * @return void
		 */
		function update_company_property( $args ) {
			return $this->run( 'properties/v1/companies/properties', $args, 'PUT' );
		}

		/**
		 * delete_company_property function.
		 *
		 * @access public
		 * @param mixed $property_name
		 * @return void
		 */
		function delete_company_property( $property_name ) {
			return $this->run( "properties/v1/companies/properties/named/$property_name", array(), 'DELETE' );
		}

		/**
		 * get_company_property_groups function.
		 *
		 * @access public
		 * @param mixed $include_properties (default: null)
		 * @return void
		 */
		function get_company_property_groups( $include_properties = null ) {
			return $this->run( 'properties/v1/companies/groups', $this->filter_args( array( 'includeProperties' => $include_properties ) ) );
		}

		/**
		 * add_company_property_group function.
		 *
		 * @see https://developers.hubspot.com/docs/methods/companies/create_company_property_group
		 * @access public
		 * @return void
		 */
		function add_company_property_group( $name, $display_name, int $display_order = null ) {
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
		function update_company_property_group( $name, $args ) {
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
		function delete_company_property_group( $name, $args ) {
			return $this->build_request( "properties/v1/companies/groups/named/$name", array(), 'DELETE' )->fetch();
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

		function update_contact( $contact_id, $properties ) {
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
		function create_or_update_contact( $email, $properties ) {
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
		function create_or_update_batch_contacts( $batch ) {
			return $this->run( 'contacts/v1/contact/batch/', $batch );
		}

		/**
		 * delete_contact function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @return void
		 */
		function delete_contact( $contact_id ) {
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
		function get_all_contacts( int $count = null, int $contact_offset = null, $property = null, string $property_mode = null, string $form_submit_mode = null, bool $list_memberships = null ) {

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
		function get_recent_updated_contacts( $args = array() ) {
			return $this->run( 'contacts/v1/lists/recently_updated/contacts/recent', $args );
		}

		/**
		 * get_recent_created_contacts function.
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
		function get_contact( $contact_id, $args = array() ) {
			return $this->run( 'contacts/v1/contact/vid/' . $contact_id . '/profile', $args );
		}

		/**
		 * get_contact_by_email function.
		 *
		 * @access public
		 * @param mixed $contact_email
		 * @param array $args (default: array())
		 * @return void
		 */
		function get_contact_by_email( $contact_email, $args = array() ) {
			return $this->run( 'contacts/v1/contact/email/' . $contact_email . '/profile' );
		}

		/**
		 * get_contact_batch_by_email function.
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
		 * get_contact_by_token function.
		 *
		 * @access public
		 * @param mixed $contact_token
		 * @param array $args (default: array())
		 * @return void
		 */
		function get_contact_by_token( $contact_token, $args = array() ) {
			return $this->run( "/contacts/v1/contact/utk/$contact_token/profile", $args );
		}

		/**
		 * search_contacts function.
		 *
		 * @access public
		 * @param mixed $search_query
		 * @param array $args (default: array())
		 * @return void
		 */
		function search_contacts( $search_query, $args = array() ) {
			$args['q'] = $search_query;
			return $this->run( 'contacts/v1/search/query', $args );
		}

		/**
		 * merge_contacts function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @param mixed $vid_to_merge
		 * @return void
		 */
		function merge_contacts( $contact_id, $vid_to_merge ) {
			$args = array(
				'vidToMerge' => $vid_to_merge,
			);

			return $this->run( "/contacts/v1/contact/merge-vids/$contact_id/", $args, 'POST' );
		}

		/* Contact Lists. */

		/**
		 * create_contact_list function.
		 *
		 * @access public
		 * @param mixed $name
		 * @param array $args (default: array())
		 * @return void
		 */
		public function create_contact_list( $name, $args = array() ) {
			$args['name'] = $name;
			return $this->run( '/contacts/v1/lists', $args, 'POST' );
		}

		/**
		 * get_contact_lists function.
		 *
		 * @access public
		 * @param array $args (default: array())
		 * @return void
		 */
		public function get_contact_lists( $args = array() ) {
			return $this->run( '/contacts/v1/lists', $args, 'POST' );
		}

		/**
		 * get_contact_list function.
		 *
		 * @access public
		 * @param mixed $list_id
		 * @return void
		 */
		public function get_contact_list( $list_id ) {
			return $this->run( "/contacts/v1/lists/$list_id" );
		}

		/**
		 * update_contact_list function.
		 *
		 * @access public
		 * @param mixed $list_id
		 * @param mixed $args
		 * @return void
		 */
		public function update_contact_list( $list_id, $args ) {
			return $this->run( "/contacts/v1/lists/$list_id", $args, 'POST' );
		}

		/**
		 * delete_contact_list function.
		 *
		 * @access public
		 * @param mixed $list_id
		 * @return void
		 */
		public function delete_contact_list( $list_id ) {
			return $this->run( "contacts/v1/lists/$list_id", array(), 'DELETE' );

		}


		/**
		 * get_batch_contact_lists function.
		 *
		 * @access public
		 * @param array $list_ids
		 * @return void
		 */
		public function get_batch_contact_lists( array $list_ids ) {
			$args = array(
				'listId' => $list_ids,
			);
			return $this->run( "contacts/v1/lists/$list_id", $args );
		}

		public function get_static_contact_lists( $count = null, $offset = null ) {
			$args = $this->filter_args(
				array(
					'count'  => $count,
					'offset' => $offset,
				)
			);
			return $this->run( 'contacts/v1/lists/static', $args );

		}

		public function get_dynamic_contact_lists( $count = null, $offset = null ) {
			$args = $this->filter_args(
				array(
					'count'  => $count,
					'offset' => $offset,
				)
			);
			return $this->run( 'contacts/v1/lists/dynamic', $args );
		}

		public function get_contacts_in_list( $list_id, $opt_args ) {
			return $this->run( "contacts/v1/lists/$list_id/contacts/all", $opt_args );
		}

		public function get_recent_contacts_in_list( $list_id, $opt_args ) {
			return $this->run( "contacts/v1/lists/$list_id/contacts/recent", $opt_args );

		}

		public function add_contact_to_list( $list_id, $contact_json ) {
			return $this->run( "contacts/v1/lists/$list_id/add", $contact_json, 'POST' );
		}

		public function delete_contact_from_list() {
			return $this->run( "contacts/v1/lists/$list_id/remove", array(), 'DELETE' );
		}


		/* Contact Properties. */

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

		/**
		 * list_all_file_metadata function.
		 *
		 * @access public
		 * @param array $args (default: array())
		 * @return void
		 */
		function list_all_file_metadata( $args = array() ) {
			return $this->run( 'filemanager/api/v2/files', $args );
		}

		/**
		 * list_file_metadata function.
		 *
		 * @access public
		 * @param mixed $file_id
		 * @return void
		 */
		function list_file_metadata( $file_id ) {
			return $this->run( 'filemanager/api/v2/files/' . $file_id );
		}

		/**
		 * hard_delete_file function.
		 *
		 * @access public
		 * @param mixed $file_id
		 * @return void
		 */
		function hard_delete_file( $file_id ) {
			return $this->run( 'filemanager/api/v2/files/' . $file_id . '/full-delete', array(), 'POST' );
		}

		/* Engagements. */

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
		function create_engagement( string $type, $engagement = array(), $metadata = array(), $associations = array(), $attachments = array() ) {
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
		function update_engagement( $engagement_id, $args ) {
			return $this->run( "engagements/v1/engagements/$engagement_id", $args, 'PATCH' );
		}

		/**
		 * [get_engagement description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/engagements/get_engagement Documentation
		 * @param  [type] $engagement_id [description]
		 * @return [type]                [description]
		 */
		function get_engagement( $engagement_id ) {
			return $this->run( "engagements/v1/engagements/$engagement_id" );
		}


		/**
		 * list_engagements function.
		 *
		 * @access public
		 * @param mixed $offset (default: null)
		 * @param int   $limit (default: 100)
		 * @return void
		 */
		function list_engagements( $offset = null, $limit = 100 ) {
			$args = array(
				'limit' => $limit,
			);

			if ( $offset ) {
				$args['offset'] = $offset;
			}

			return $this->run( 'engagements/v1/engagements/paged', $args );
		}

		function get_recent_engagements( $args ) {
			return $this->run( 'engagements/v1/engagements/recent/modified', $args );
		}

		function delete_engagement( $args ) {
			return $this->run( 'engagements/v1/engagements/recent/modified', $args );
		}

		function associate_engagement( $engagement_id, $object_type, $object_id ) {
			return $this->run( "engagements/v1/engagements/$engagement_id/associations/$object_type/$object_id", array(), 'PUT' );
		}

		/**
		 * list_associated_engagements function.
		 *
		 * @access public
		 * @param mixed $object_type
		 * @param mixed $object_id
		 * @return void
		 */
		function list_associated_engagements( $object_type, $object_id ) {
			return $this->run( "engagements/v1/engagements/associated/$object_type/$object_id/paged" );
		}

		function get_engagement_dispositions() {
			return $this->run( 'calling/v1/dispositions' );
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
			error_log( 'HubSpotAPI->get_keyword_list() Method Deprecated: HubSpot Keyword API will be removed August 1, 2018' );
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
			error_log( 'HubSpotAPI->get_keyword() Method Deprecated: HubSpot Keyword API will be removed August 1, 2018' );
			return $this->run( 'keywords/v1/keywords/' . $keyword_guid );
		}

		function add_keyword() {
			error_log( 'HubSpotAPI->add_keyword() Method Deprecated: HubSpot Keyword API will be removed August 1, 2018' );
			return $this->run( '/keywords/v1/keywords' );
		}

		/**
		 * Delete Keyword.
		 *
		 * @access public
		 * @param mixed $keyword_guid Keyword GUID.
		 * @return void
		 */
		function delete_keyword( $keyword_guid ) {
			error_log( 'HubSpotAPI->delete_keyword() Method Deprecated: HubSpot Keyword API will be removed August 1, 2018' );
			return $this->run( 'keywords/v1/keywords/' . $keyword_guid );
		}

		/* Owners. */

		function get_owners( $email = null, $include_inactive = null ) {
			$args = $this->filter_args(
				array(
					'email'           => $email,
					'includeInactive' => $include_inactive,
				)
			);
			return $this->run( 'keywords/v1/keywords/' . $keyword_guid );
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
		function update_deal( $deal_id, $deal_json ) {
			return $this->run( "deals/v1/deal/$deal_id", $deal_json, 'PUT' );
		}

		function update_deal_batch( $deal_json ) {
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
		function get_all_deals( $limit = null, $offset = null, $properties = null, $properties_with_history = null, $include_associations = null ) {
			$args = array(
				'limit'                 => $limit,
				'offset'                => $offset,
				'properties'            => $properties,
				'propertiesWithHistory' => $properties_with_history,
				'includeAssociations'   => $include_associations,
			);
			return $this->run( 'deals/v1/deal/paged', $args );
		}

		function get_recently_modified_deals( $limit = null, $offset = null, $since = null, $properties_with_versions = null ) {
			$args = array(
				'count'                   => $limit,
				'offset'                  => $offset,
				'since'                   => $since,
				'includePropertyVersions' => $properties_with_versions,
			);
			return $this->run( 'deals/v1/deal/recent/modified', $args );
		}

		function get_recent_created_deals( $limit = null, $offset = null, $since = null, $properties_with_versions = null ) {
			$args = array(
				'count'                   => $limit,
				'offset'                  => $offset,
				'since'                   => $since,
				'includePropertyVersions' => $properties_with_versions,
			);
			return $this->run( 'deals/v1/deal/recent/created', $args );
		}

		function delete_deal( $deal_id ) {
			return $this->run( "deals/v1/deal/$deal_id", array(), 'DELETE' );
		}

		function get_deal( $deal_id, $properties_with_versions = null ) {
			return $this->run( "deals/v1/deal/$deal_id", array( 'includePropertyVersions' => $properties_with_versions ) );
		}

		function associate_deal( $deal_id, $object_type, $ids ) {
			$args = array( 'id' => $ids );
			$url  = "deals/v1/deal/$deal_id/associations/$object_type";
			$url  = add_query_arg( 'id', $ids, $url );

			return $this->run( $url, $args, 'PUT' );
		}

		function delete_deal_association( $deal_id, $object_type, $ids ) {
			$args = array( 'id' => $ids );
			$url  = "deals/v1/deal/$deal_id/associations/$object_type";
			$url  = add_query_arg( 'id', $ids, $url );

			return $this->run( $url, $args, 'DELETE' );
		}

		function get_associated_deals( $object_type, $object_id, $limit = null, $offset = null, $properties = null, $properties_with_history = null, $include_associations = null ) {
			$args = array(
				'limit'                 => $limit,
				'offset'                => $offset,
				'properties'            => $properties,
				'propertiesWithHistory' => $properties_with_history,
				'includeAssociations'   => $include_associations,
			);
			return $this->run( "deals/v1/deal/associated/$object_type/$object_id/paged", $args );
		}

		/* Deal Pipelines. */

		/**
		 * Get Deal Pipelines.
		 *
		 * @access public
		 * @param mixed $pipeline_id Pipeline ID.
		 * @return void
		 */
		function show_deal_pipeline( $pipeline_id ) {
			return $this->run( 'deals/v1/pipelines/' . $pipeline_id );
		}

		/**
		 * Get all Deal Pipelines.
		 *
		 * @access public
		 * @return void
		 */
		function list_deal_pipelines() {
			return $this->run( 'deals/v1/pipelines' );
		}

		function create_deal_pipeline( $label, $display_order, $stages ) {
			$args = array(
				'label'        => $label,
				'displayOrder' => $display_order,
				'stages'       => $stages,
			);
			return $this->run( 'deals/v1/pipelines', $args, 'POST' );
		}

		function update_deal_pipeline( $pipeline_id, $label, $display_order, $stages ) {
			$args = array(
				'pipelineId'   => $pipeline_id,
				'label'        => $label,
				'displayOrder' => $display_order,
				'stages'       => $stages,
			);
			return $this->run( "deals/v1/pipelines/$pipeline_id", $args, 'POST' );
		}

		function delete_deal_pipeline( $pipeline_id ) {
			return $this->run( "deals/v1/pipelines/$pipeline_id", $args, 'DELETE' );
		}

		/* Deal Properties. */

		function add_deal_property( $property_json ) {
			return $this->run( 'properties/v1/deals/properties/', $property_json, 'POST' );
		}

		function update_deal_property( $property_name, $property_json ) {
			return $this->run( "properties/v1/deals/properties/named/$property_name", $property_json, 'PUT' );
		}

		function delete_deal_property( $property_name ) {
			return $this->run( "properties/v1/deals/properties/named/$property_name", array(), 'DELETE' );
		}

		function get_all_deal_properties() {
			return $this->run( 'properties/v1/deals/properties/' );
		}

		function get_deal_property() {
			return $this->run( "properties/v1/deals/properties/named/$property_name" );
		}

		function add_deal_property_group( $property_group_json ) {
			return $this->run( 'properties/v1/deals/groups/', $property_group_json, 'POST' );
		}

		function update_deal_property_group( $group_name ) {
			return $this->run( "properties/v1/deals/groups/named/$group_name", $property_group_json, 'PUT' );
		}

		function delete_deal_property_group( $group_name ) {
			return $this->run( "properties/v1/deals/groups/named/$group_name", array(), 'DELETE' );
		}

		function get_deal_property_groups( $include_properties = null ) {
			$args = array( 'includeProperties' => $include_properties );
			return $this->run( 'properties/v1/deals/groups', $args );
		}

		function get_deal_property_group( $property_group, $include_properties ) {
			$args = array( 'includeProperties' => $include_properties );
			return $this->run( "properties/v1/deals/groups/named/$property_group", $args );
		}

		/* Tickets. */

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
		function list_tickets( $properties = array() ) {
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
		function show_ticket( $ticket_id, $properties = array(), $deleted = false ) {
			$args = array(
				'includeDeletes' => $deleted,
			);

			if ( ! empty( $properties ) ) {
				$args['properties'] = $properties;
			}

			return $this->run( 'crm-objects/v1/objects/tickets/' . $ticket_id, $args );
		}

		/**
		 * show_tickets function.
		 *
		 * @access public
		 * @param mixed $ticket_ids
		 * @param array $properties (default: array())
		 * @param bool  $deleted (default: false)
		 * @return void
		 */
		function show_tickets( $ticket_ids, $properties = array(), $deleted = false ) {
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
		function create_ticket( $contact_id, string $status, string $source_type = 'EMAIL', array $properties = array() ) {
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
		 * in the create_ticket function.
		 *
		 * Each must include source_type, status, and created_by.
		 *
		 * @param  array $tickets An array of tickets.
		 * @return object          The response.
		 */
		function create_tickets( array $tickets ) {
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
		function update_ticket( $ticket_id, array $properties ) {
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
		function update_tickets( array $objects ) {
			return $this->run( 'crm-objects/v1/objects/tickets/batch-update', $objects, 'POST' ); // Is this not supposed to be put?
		}

		/**
		 * Deletes a ticket. Note: this is permanent, cannot be undone, and the
		 * ticket cannot be updated post-delete.
		 *
		 * @param  mixed $ticket_id The ID of the ticket to be deleted.
		 * @return object            A 204 No Content response.
		 */
		function delete_ticket( $ticket_id ) {
			return $this->run( 'crm-objects/v1/objects/tickets/' . $ticket_id, array(), 'DELETE' );
		}

		/**
		 * Deletes a bunch of tickets. Note2: this is permanent, blah blah see delete_ticket.
		 *
		 * @param  array $ticket_ids [description]
		 * @return [type]             [description]
		 */
		function delete_tickets( $ticket_ids = array() ) {
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
		function get_ticket_changes( array $args = array() ) {
			return $this->run( 'crm-objects/v1/change-log/tickets', $args );
		}

		/* Timeline. */

		function add_or_update_timeline_event( $app_id, $args ) {
			return $this->run( "integrations/v1/$app_id/timeline/event", $args, 'PUT' );
		}

		/**
		 * [get_timeline_event description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/timeline/get-event   Documentation
		 * @param  [type] $app_id        [description]
		 * @param  [type] $event_type_id [description]
		 * @param  [type] $event_id      [description]
		 * @return [type]                [description]
		 */
		function get_timeline_event( $app_id, $event_type_id, $event_id ) {
			return $this->run( "integrations/v1/$app_id/timeline/event/$event_type_id/$event_id" );
		}

		/**
		 * [get_timeline_event_types description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/timeline/get-event-types   Documentation
		 * @return [type] [description]
		 */
		function get_timeline_event_types( $app_id ) {
			return $this->run( "integrations/v1/$app_id/timeline/event-types" );
		}

		function add_new_timeline_event_type( $app_id, $user_id, $name, $args = array() ) {
			$args['applicationId'] = $app_id;
			$args['name']          = $name;

			$url = "integrations/v1/$app_id/timeline/event-types";
			$url = add_query_arg( 'userId', $user_id, $url );

			return $this->run( $url, $args, 'POST' );
		}

		/**
		 * [update_timeline_event_type description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/timeline/update-event-type Documentation
		 * @param  [type] $app_id  [description]
		 * @param  [type] $user_id [description]
		 * @param  [type] $name    [description]
		 * @param  array  $args    [description]
		 * @return [type]          [description]
		 */
		function update_timeline_event_type( $app_id, $event_type_id, $args = array() ) {
			return $this->run( "integrations/v1/$app_id/timeline/event-types/$event_type_id", $args, 'PUT' );
		}


		/**
		 * [delete_timeline_event_type description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/timeline/delete-event-type Documentation
		 * @param  [type] $app_id        [description]
		 * @param  [type] $event_type_id [description]
		 * @param  array  $user_id       [description]
		 * @return [type]                [description]
		 */
		function delete_timeline_event_type( $app_id, $event_type_id, $user_id ) {
			return $this->run( add_query_arg( 'userId', $user_id, "/integrations/v1/$app_id/timeline/event-types/$event_type_id" ), array(), 'DELETE' );
		}

		/**
		 * [get_properties_for_timeline_event_type description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/timeline/get-timeline-event-type-properties Documentation
		 * @param  [type] $app_id        [description]
		 * @param  [type] $event_type_id [description]
		 * @return [type]                [description]
		 */
		function get_properties_for_timeline_event_type( $app_id, $event_type_id ) {
			return $this->run( "integrations/v1/$app_id/timeline/event-types/$event_type_id/properties" );
		}

		/**
		 * [add_property_for_timeline_event_type description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/timeline/create-timeline-event-type-property Documentation
		 * @param [type] $app_id        [description]
		 * @param [type] $event_type_id [description]
		 * @param [type] $name          [description]
		 * @param [type] $label         [description]
		 * @param [type] $property_type [description]
		 * @param array  $args          [description]
		 */
		function add_property_for_timeline_event_type( $app_id, $event_type_id, $name, $label, $property_type, $args = array() ) {

			$args['name']         = $name;
			$args['label']        = $label;
			$args['propertyType'] = $property_type;

			return $this->run( "integrations/v1/$app_id/timeline/event-types/$event_type_id/properties", $args, 'POST' );
		}

		/**
		 * [update_property_for_timeline_event_type description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/timeline/udpate-timeline-event-type-property Documentation
		 *
		 * @param  [type] $app_id        [description]
		 * @param  [type] $event_type_id [description]
		 * @param  [type] $prop_id       [description]
		 * @param  [type] $args          [description]
		 * @return [type]                [description]
		 */
		function update_property_for_timeline_event_type( $app_id, $event_type_id, $prop_id, $args = array() ) {
			$args['id'] = $prop_id;

			return $this->run( "integrations/v1/$app_id/timeline/event-types/$event_type_id/properties", $args, 'PUT' );
		}

		/**
		 * [delete_property_for_timeline_event_type description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/timeline/delete-timeline-event-type-property Documentation
		 *
		 * @param  [type] $app_id        [description]
		 * @param  [type] $event_type_id [description]
		 * @param  [type] $prop_id       [description]
		 * @return [type]                [description]
		 */
		function delete_property_for_timeline_event_type( $app_id, $event_type_id, $prop_id ) {
			return $this->run( "integrations/v1/$app_id/timeline/event-types/$event_type_id/properties/$prop_id", array(), 'DELETE' );
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

		/**
		 * list_subscriptions function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @return void
		 */
		function list_subscriptions( $app_id ) {
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions' );
		}

		/**
		 * create_subscription function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @param mixed $subsription_type
		 * @param mixed $property_name
		 * @param bool  $enabled (default: true)
		 * @return void
		 */
		function create_subscription( $app_id, $subsription_type, $property_name, $enabled = true ) {
			$subscription = array(
				'subscriptionDetails' => array(
					'subscriptionType' => $subscription_type,
					'propertyName'     => $property_name,
				),
				'enabled'             => $enabled,
			);
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions', $subscription, 'POST' );
		}

		/**
		 * update_subscription function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @param mixed $subscription_id
		 * @param bool  $enabled
		 * @return void
		 */
		function update_subscription( $app_id, $subscription_id, bool $enabled ) {
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions/' . $subscription_id, array( 'enabled' => $enabled ), 'PUT' );
		}

		/**
		 * delete_subscription function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @param mixed $subscription_id
		 * @return void
		 */
		function delete_subscription( $app_id, $subscription_id ) {
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions/' . $subscription_id );
		}

		/**
		 * get_webhook_settings function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @return void
		 */
		function get_webhook_settings( $app_id ) {
			return $this->run( 'webhooks/v1/' . $app_id . '/settings' );
		}

		/**
		 * update_settings function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @param mixed $webhookUrl
		 * @param mixed $maxConcurrentRequests
		 * @return void
		 */
		function update_settings( $app_id, $webhookUrl, $maxConcurrentRequests ) {
			$args = array(
				'webhookUrl'            => $webhookUrl,
				'maxConcurrentRequests' => $maxConcurrentRequests,
			);
			return $this->run( 'webhooks/v1/' . $app_id . '/settings', $args, 'PUT' );
		}


		/* Associations. */

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
		function get_associations_for_crm_object( $object_id, $definition_id ) {
			return $this->run( 'crm-associations/v1/associations/' . $object_id . '/HUBSPOT_DEFINED/' . $definition_id );
		}

		/**
		 * Create an association between two users.
		 *
		 * Contact to ticket        15
		 * Ticket to contact        16
		 * Ticket to engagement 17
		 * Engagement to ticket 18
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
		function create_association( $from_id, $to_id, $definition, $category = 'HUBSPOT_DEFINED' ) {
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

	function get_all_products( $offset = null, $properties = null ) {
		return $this->run(
			'crm-objects/v1/objects/products/paged',
			array(
				'offset'     => $offset,
				'properties' => $properties,
			)
		);
	}

	function get_product_by_id( $product_id, $properties = null, bool $include_deleted = null ) {
		return $this->run(
			"crm-objects/v1/objects/products/$product_id",
			array(
				'includeDeletes' => $include_deleted,
				'properties'     => $properties,
			)
		);
	}

	function get_batch_products_by_id( $ids, $properties = null, bool $include_deleted = null ) {
		return $this->run(
			'crm-objects/v1/objects/products/batch-read',
			array(
				'ids'            => $ids,
				'properties'     => $properties,
				'includeDeletes' => $include_deleted,
			)
		);
	}

	function create_product( $product_json ) {
		return $this->run( 'crm-objects/v1/objects/products', $product_json, 'POST' );
	}

	function create_product_batch( $product_json ) {
		return $this->run( 'crm-objects/v1/objects/products/batch-create', $product_json, 'POST' );
	}

	function update_product( $product_id, $product_json ) {
		return $this->run( "crm-objects/v1/objects/products/$product_id", $product_json, 'POST' );
	}

	function update_product_batch( $product_json ) {
		return $this->run( 'crm-objects/v1/objects/products/batch-update', $product_json, 'POST' );
	}

	function delete_product( $product_id ) {
		return $this->run( "crm-objects/v1/objects/products/$product_id", array(), 'DELETE' );
	}
	function delete_product_batch( $ids ) {
		return $this->run( 'crm-objects/v1/objects/products/batch-delete', array( 'ids' => $ids ), 'POST' );
	}

	function get_changed_product_log( $args ) {
		return $this->run( 'crm-objects/v1/change-log/products', $args );
	}

	function get_broken_down_analytics( $breakdown_by, $time_period, $starte_date, $end_date, $opt_args = array() ) {
		$args = $this->filter_args(
			$opt_args,
			array(
				'start' => $starte_date,
				'end'   => $end_date,
			)
		);
		return $this->run( "analytics/v2/reports/$breakdown_by/$time_period", $args );
	}

	function get_specific_object_analytics( $object_type, $time_period, $starte_date, $end_date, $opt_args = array() ) {
		$args = $this->filter_args(
			$opt_args,
			array(
				'start' => $starte_date,
				'end'   => $end_date,
			)
		);
		return $this->run( "analytics/v2/reports/$object_type/$time_period", $args );
	}
}
