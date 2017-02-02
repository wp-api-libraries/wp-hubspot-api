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

			$this->args['headers'] = array(
            'Content-Type' => 'application/json',
	        );

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

			$code = wp_remote_retrieve_response_code($response );
			if ( 200 !== $code ) {
				return new WP_Error( 'response-error', sprintf( __( 'Server response code: %d', 'text-domain' ), $code ) );
			}
			$body = wp_remote_retrieve_body( $response );
			return json_decode( $body );
		}

		/* Oauth. */


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


		/**
		 * get_companies function.
		 *
		 * @access public
		 * @param string $limit (default: '')
		 * @param string $offset (default: '')
		 * @param string $properties (default: '')
		 * @return void
		 */
		function get_companies( $limit = '', $offset = '', $properties = '' ) {
			$request = $this->base_uri . '/companies/v2/companies/paged?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * get_recently_modified_companies function.
		 *
		 * @access public
		 * @param string $offset (default: '')
		 * @param string $count (default: '')
		 * @return void
		 */
		function get_recently_modified_companies( $offset = '', $count = '' ) {
			$request = $this->base_uri . '/companies/v2/companies/recent/modified?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * get_recently_created_companies function.
		 *
		 * @access public
		 * @param string $offset (default: '')
		 * @param string $count (default: '')
		 * @return void
		 */
		function get_recently_created_companies( $offset = '', $count = '' ) {
			$request = $this->base_uri . '/companies/v2/companies/recent/created?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * get_company_by_domain function.
		 *
		 * @access public
		 * @param mixed $domain
		 * @return void
		 */
		function get_company_by_domain( $domain ) {
			$request = $this->base_uri . '/companies/v2/companies/domain/'.$domain.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * get_company function.
		 *
		 * @access public
		 * @param mixed $company_id
		 * @return void
		 */
		function get_company( $company_id ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * get_company_contacts function.
		 *
		 * @access public
		 * @param mixed $company_id
		 * @param string $vidoffset (default: '')
		 * @param string $count (default: '')
		 * @return void
		 */
		function get_company_contacts( $company_id, $vidoffset = '', $count = '' ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'/contacts?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * get_company_contacts_ids function.
		 *
		 * @access public
		 * @param mixed $company_id
		 * @param string $vidoffset (default: '')
		 * @param string $count (default: '')
		 * @return void
		 */
		function get_company_contacts_ids( $company_id, $vidoffset = '', $count = '' ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'/vids?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}


		/**
		 * add_company function.
		 *
		 * @access public
		 * @return void
		 */
		function add_company() {
			$request = $this->base_uri . '/companies/v2/companies?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * add_contact_to_company function.
		 *
		 * @access public
		 * @param mixed $company_id
		 * @param mixed $contact_vid
		 * @return void
		 */
		function add_contact_to_company( $company_id, $contact_vid ) {
			$request = $this->base_uri . '/engagements/v1/engagements/'.$company_id.'/associations/contact/'. $contact_vid .'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * update_company function.
		 *
		 * @access public
		 * @param mixed $company_id
		 * @return void
		 */
		function update_company( $company_id ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * delete_company function.
		 *
		 * @access public
		 * @param mixed $company_id
		 * @return void
		 */
		function delete_company( $company_id ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * remove_contact_from_company function.
		 *
		 * @access public
		 * @param mixed $company_id
		 * @param mixed $contact_vid
		 * @return void
		 */
		function remove_contact_from_company( $company_id, $contact_vid ) {
			$request = $this->base_uri . '/companies/v2/companies/'. $company_id .'/contacts/'.$contact_vid.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/* Companies Properties. */

		/* Contacts. */


		/**
		 * Create Contact.
		 *
		 * @access public
		 * @return void
		 */
		function create_contact() {
			$request = $this->base_uri . '/contacts/v1/contact/?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}


		/**
		 * Update Contact.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @return void
		 */
		function update_contact( $contact_id ) {
			$request = $this->base_uri . '/contacts/v1/contact/vid/'. $contact_id .'/profile?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}


		/**
		 * Create or Update Contact.
		 *
		 * @access public
		 * @param mixed $email
		 * @return void
		 */
		function create_or_update_contact( $email ) {
			$request = $this->base_uri . '/contacts/v1/contact/createOrUpdate/email/' . $email . '/?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Create or Update Batch Contacts.
		 *
		 * @access public
		 * @return void
		 */
		function create_or_update_batch_contacts() {
			$request = $this->base_uri . '/contacts/v1/contact/batch/?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		function delete_contact() {
			// https://api.hubapi.com/contacts/v1/contact/vid/61571?hapikey=demo

		}

		function get_all_contacts() {
			// https://api.hubapi.com/contacts/v1/lists/all/contacts/all?hapikey=demo&count=2
		}

		function get_recent_contacts() {
			// https://api.hubapi.com/contacts/v1/lists/recently_updated/contacts/recent?hapikey=demo&count=2

		}

		function get_contact_by_token( $contact_token ) {
			// http://api.hubapi.com/contacts/v1/contact/utk/f844d2217850188692f2610c717c2e9b/profile?hapikey=demo

		}

		function get_batch_contacts_by_token() {
			// https://api.hubapi.com/contacts/v1/contact/utks/batch/?utk=f844d2217850188692f2610c717c2e9b&utk=j94344d22178501692f2610c717c2e9b&hapikey=demo

		}

		function search_contacts( $search_query ) {
			// https://api.hubapi.com/contacts/v1/search/query?q=testingapis&hapikey=demo

		}

		function merge_contacts( $contact_id, $vid_to_merge ) {
			// https://api.hubapi.com/contacts/v1/contact/merge-vids/1343724/?hapikey=demo
		}

		/* Events. */

		/**
		 * event function.
		 *
		 * @access public
		 * @param mixed $event_id
		 * @param mixed $contact_email (default: null)
		 * @param mixed $contact_revenue (default: null)
		 * @param mixed $any_contact_property (default: null)
		 * @return void
		 */
		function event( $event_id, $contact_email = null, $contact_revenue = null, $any_contact_property = null ) {

		}

		/* Keywords. */


		/**
		 * get_keyword_list function.
		 *
		 * @access public
		 * @param mixed $search
		 * @return void
		 */
		function get_keyword_list( $search ) {
			$request = $this->base_uri . '/keywords/v1/keywords?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * get_keyword function.
		 *
		 * @access public
		 * @param mixed $keyword_guid
		 * @return void
		 */
		function get_keyword( $keyword_guid ) {
			$request = $this->base_uri . '/keywords/v1/keywords/'.$keyword_guid.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		function add_keyword() {

		}

		/**
		 * delete_keyword function.
		 *
		 * @access public
		 * @param mixed $keyword_guid
		 * @return void
		 */
		function delete_keyword( $keyword_guid ) {
			$request = $this->base_uri . '/keywords/v1/keywords/'.$keyword_guid.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/* Deals. */


		/**
		 * add_deal function.
		 *
		 * @access public
		 * @param mixed $deal_json
		 * @return void
		 */
		function add_deal( $deal_json ) {

		}

		/**
		 * update_deal function.
		 *
		 * @access public
		 * @param mixed $deal_id
		 * @param mixed $deal_json
		 * @return void
		 */
		function update_deal( $deal_id, $deal_json ) {

		}

		function get_deal() {
			// 'https://api.hubapi.com/deals/v1/deal/3865198?hapikey=demo'
		}

		/**
		 * Get all deals.
		 * Docs: https://developers.hubspot.com/docs/methods/deals/get-all-deals
		 *
		 * @access public
		 * @param mixed $limiit (default: null)
		 * @param mixed $offset (default: null)
		 * @param mixed $properties (default: null)
		 * @param mixed $properties_with_history (default: null)
		 * @param mixed $associations
		 * @return void
		 */
		function get_all_deals( $limiit = null, $offset = null, $properties = null, $properties_with_history = null, $associations ){
			$request = $this->base_uri . '/deals/v1/deal/paged?hapikey=' . static::$api_key;
			return $this->fetch( $request );
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
		 * get_deal_pipelines function.
		 *
		 * @access public
		 * @param mixed $pipeline_id
		 * @return void
		 */
		function get_deal_pipelines( $pipeline_id ) {

		}

		/**
		 * get_all_deal_pipelines function.
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

		/* Timeline. */

		/**
		 * Create a new Timeline Event Type
		 *
		 * @access public
		 * @param mixed $app_id
		 * @param mixed $name
		 * @param mixed $header_template (default: null)
		 * @param mixed $detail_template (default: null)
		 * @param mixed $object_type (default: null)
		 * @return void
		 */
		function create_timeline_event_type( $app_id, $name, $header_template = null, $detail_template = null, $object_type = null ) {
			$request = $this->base_uri . '/integrations/v1/'.$app_id.'/timeline/event-types?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}


		/* Transactional. */


		/**
		 * List SMTP API Tokens.
		 *
		 * @access public
		 * @return void
		 */
		function get_smtp_tokens() {
			$request = $this->base_uri . '/email/public/v1/smtpapi/tokens?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * add_smtp_token function.
		 *
		 * @access public
		 * @param mixed $createdby
		 * @param mixed $campaign_name
		 * @return void
		 */
		function add_smtp_token( $createdby, $campaign_name ) {
			$request = $this->base_uri . '/email/public/v1/smtpapi/tokens?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/* Workflows. */

		/* Webhooks. */

	}

}
