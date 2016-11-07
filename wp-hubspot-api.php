<?php
/**
 * WP HubSpot API
 *
 * @package WP-HubSpot-API
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
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }


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
		private $args = array();

		/**
		 * api_key
		 *
		 * @var mixed
		 * @access private
		 * @static
		 */
		static private $api_key;

		/**
		 * oauth_token
		 *
		 * @var mixed
		 * @access private
		 * @static
		 */
		static private $oauth_token;

		/**
		 * BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://api.hubapi.com';

		/**
		 * __construct function.
		 *
		 * @access public
		 * @param mixed $api_key
		 * @return void
		 */
		function __construct( $api_key, $oauth_token = null ) {

			static::$api_key = $api_key;

			if ( ! empty( $oauth_token ) ) {
				$this->args['headers'] = array(
					'Authorization' => 'Bearer '. $oauth_token,
				);
			}

		}

		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @param mixed $request Request URL.
		 * @return $body Body.
		 */
		private function fetch( $request ) {

			$response = wp_remote_request( $request, $this->args );

			var_dump($response);

			$code = wp_remote_retrieve_response_code($response );
			if ( 200 !== $code ) {
				return new WP_Error( 'response-error', sprintf( __( 'Server response code: %d', 'text-domain' ), $code ) );
			}
			$body = wp_remote_retrieve_body( $response );
			return json_decode( $body );
		}

		/* Oauth. */

		authorize() {
			// https://app.hubspot.com/oauth/authorize
		}

		/* Calendar. */

		/**
		 * Calendar - List content events.
		 *
		 * @access public
		 * @param mixed $start_date
		 * @param mixed $end_date
		 * @param mixed $limit (default: null)
		 * @param mixed $offset (default: null)
		 * @param mixed $content_category (default: null)
		 * @param mixed $campaign_guid (default: null)
		 * @param mixed $include_no_campaigns (default: null)
		 * @return void
		 */
		function get_content_events( $start_date, $end_date, $limit = null, $offset = null, $content_category = null, $campaign_guid = null, $include_no_campaigns = null ) {

			$request = $this->base_uri . '/calendar/v1/events/content?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * get_social_events function.
		 *
		 * @access public
		 * @param mixed $start_date
		 * @param mixed $end_date
		 * @param mixed $limit (default: null)
		 * @param mixed $offset (default: null)
		 * @param mixed $campaign_guid (default: null)
		 * @param mixed $include_no_campaigns (default: null)
		 * @return void
		 */
		function get_social_events( $start_date, $end_date, $limit = null, $offset = null, $campaign_guid = null, $include_no_campaigns = null ) {

			$request = $this->base_uri . '/calendar/v1/events/social?hapikey=' . static::$api_key;
			return $this->fetch( $request );

		}

		/**
		 * get_task_events function.
		 *
		 * @access public
		 * @param mixed $start_date
		 * @param mixed $end_date
		 * @param mixed $limit (default: null)
		 * @param mixed $offset (default: null)
		 * @param mixed $campaign_guid (default: null)
		 * @param mixed $include_no_campaigns (default: null)
		 * @return void
		 */
		function get_task_events( $start_date, $end_date, $limit = null, $offset = null, $campaign_guid = null, $include_no_campaigns = null ) {

			$request = $this->base_uri . '/calendar/v1/events/task?hapikey=' . static::$api_key;
			return $this->fetch( $request );

		}

		/**
		 * create_task function.
		 *
		 * @access public
		 * @return void
		 */
		function create_task() {

			$request = $this->base_uri . '/calendar/v1/events/task?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * get_task function.
		 *
		 * @access public
		 * @param mixed $task_id
		 * @return void
		 */
		function get_task( $task_id ) {

			$request = $this->base_uri . '/calendar/v1/events/task/'. $task_id .'?hapikey=' . static::$api_key;
			return $this->fetch( $request );

		}

		/**
		 * update_task function.
		 *
		 * @access public
		 * @param mixed $task_id
		 * @return void
		 */
		function update_task( $task_id ) {
			$request = $this->base_uri . '/calendar/v1/events/task/'. $task_id .'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * delete_task function.
		 *
		 * @access public
		 * @param mixed $task_id
		 * @return void
		 */
		function delete_task( $task_id ) {
			$request = $this->base_uri . '/calendar/v1/events/task/'. $task_id .'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/* Companies. */

		function get_companies( $limit = '', $offset = '', $properties = '' ) {

		}

		function get_recently_modified_companies( $offset = '', $count = '' ) {

		}

		function get_recently_created_companies( $offset = '', $count = '' ) {

		}

		function get_company_by_domain( $domain ) {

		}

		function get_company( $company_id ) {

		}

		function get_company_contacts( $company_id, $vidoffset = '', $count = '' ) {

		}

		function get_company_contacts_ids( $company_id, $vidoffset = '', $count = '' ) {

		}

		function add_company() {

		}

		function add_contact_to_company( $company_id, $contact_vid ) {

		}

		function update_company( $company_id ) {
		}

		function delete_company( $company_id ) {
		}

		function remove_contact_from_company( $company_id, $contact_vid ) {

		}

		/* Companies Properties. */

	}

}
