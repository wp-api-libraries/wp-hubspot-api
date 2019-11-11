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

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

use WP_Hubspot_API;

require_once trailingslashit( dirname( __FILE__ ) ) . 'autoloader.php';

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
		protected static $api_key;

		/**
		 * oauth_token
		 *
		 * @var mixed
		 * @access protected
		 * @static
		 */
		protected static $oauth_token;

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
		protected function run( $route, $args = array(), $method = 'GET' ) {
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

		/**
		 * [get_oauth_access_token description]
		 *
		 * @param  string $client_id     [description]
		 * @param  string $client_secret [description]
		 * @param  string $redirect_uri  [description]
		 * @param  string $code          [description]
		 * @return [type]                [description]
		 */
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

		/**
		 * [get_oauth_token_info description]
		 *
		 * @param  string $token [description]
		 * @return [type]        [description]
		 */
		function get_oauth_token_info( string $token ) {
			return $this->run( 'oauth/v1/access-tokens/' . $token );
		}

		/**
		 * [check_daily_usage description]
		 *
		 * @return [type] [description]
		 */
		function check_daily_usage() {
			return $this->run( 'integrations/v1/limit/daily' );
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


	}

}
