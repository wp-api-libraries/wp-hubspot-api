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
* Text Domain: wp-hubspot-api
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
				return new WP_Error( 'response-error', sprintf( __( 'Server response code: %d', 'wp-hubspot-api' ), $code ) );
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

			$request = $this->base_uri . '/calendar/v1/events/content?hapikey=' . static::$api_key;
			return $this->fetch( $request );
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

			$request = $this->base_uri . '/calendar/v1/events/social?hapikey=' . static::$api_key;
			return $this->fetch( $request );

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

			$request = $this->base_uri . '/calendar/v1/events/task?hapikey=' . static::$api_key;
			return $this->fetch( $request );

		}

		/**
		 * Create Task.
		 *
		 * @access public
		 * @return void
		 */
		function create_task() {

			$request = $this->base_uri . '/calendar/v1/events/task?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Get Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		function get_task( $task_id ) {

			$request = $this->base_uri . '/calendar/v1/events/task/'. $task_id .'?hapikey=' . static::$api_key;
			return $this->fetch( $request );

		}

		/**
		 * Update Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		function update_task( $task_id ) {
			$request = $this->base_uri . '/calendar/v1/events/task/'. $task_id .'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Delete Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		function delete_task( $task_id ) {
			$request = $this->base_uri . '/calendar/v1/events/task/'. $task_id .'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/* Companies. */


		/**
		 * Get Companies.
		 *
		 * @access public
		 * @param string $limit (default: '') Limit.
		 * @param string $offset (default: '') Offset.
		 * @param string $properties (default: '') Properties.
		 * @return void
		 */
		function get_companies( $limit = '', $offset = '', $properties = '' ) {
			$request = $this->base_uri . '/companies/v2/companies/paged?hapikey=' . static::$api_key;
			return $this->fetch( $request );
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
			$request = $this->base_uri . '/companies/v2/companies/recent/modified?hapikey=' . static::$api_key;
			return $this->fetch( $request );
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
			$request = $this->base_uri . '/companies/v2/companies/recent/created?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Get company by Domain.
		 *
		 * @access public
		 * @param mixed $domain Domain.
		 * @return void
		 */
		function get_company_by_domain( $domain ) {
			$request = $this->base_uri . '/companies/v2/companies/domain/'.$domain.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Get Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @return void
		 */
		function get_company( $company_id ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Get Company Contacts.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @param string $vidoffset (default: '') Vid Offset.
		 * @param string $count (default: '') Count.
		 * @return void
		 */
		function get_company_contacts( $company_id, $vidoffset = '', $count = '' ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'/contacts?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Get Company Contacts IDs.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @param string $vidoffset (default: '') VidOffset.
		 * @param string $count (default: '') Count.
		 * @return void
		 */
		function get_company_contacts_ids( $company_id, $vidoffset = '', $count = '' ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'/vids?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}


		/**
		 * Add Company.
		 *
		 * @access public
		 * @return void
		 */
		function add_company() {
			$request = $this->base_uri . '/companies/v2/companies?hapikey=' . static::$api_key;
			return $this->fetch( $request );
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
			$request = $this->base_uri . '/engagements/v1/engagements/'.$company_id.'/associations/contact/'. $contact_vid .'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Update Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @return void
		 */
		function update_company( $company_id ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Delete Company.
		 *
		 * @access public
		 * @param mixed $company_id Company ID.
		 * @return void
		 */
		function delete_company( $company_id ) {
			$request = $this->base_uri . '/companies/v2/companies/'.$company_id.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
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
			$request = $this->base_uri . '/companies/v2/companies/'. $company_id .'/contacts/'.$contact_vid.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/* Companies Properties. */

		function add_company_property() {

		}

		function update_company_property() {

		}

		function delete_company_property() {

		}

		function get_all_company_properties() {

		}

		function get_company_property() {

		}

		function add_company_property_group() {

		}

		function update_company_property_group() {

		}

		function delete_company_property_group() {

		}

		function get_company_property_groups() {

		}

		/* Contacts. */


		/**
		 * Create Contact.
		 *
		 * @access public
		 * @return void
		 */
		function create_contact( $args ) {

			$response = wp_remote_post( $this->base_uri . '/contacts/v1/contact/?hapikey=' . static::$api_key, $args );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				echo "Something went wrong: $error_message";
			} else {
				echo 'Response:<pre>';
				print_r( $response );
				echo '</pre>';
			}
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
			$request = $this->base_uri . '/keywords/v1/keywords?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		/**
		 * Get Keyword.
		 *
		 * @access public
		 * @param mixed $keyword_guid Keyword GUID.
		 * @return void
		 */
		function get_keyword( $keyword_guid ) {
			$request = $this->base_uri . '/keywords/v1/keywords/'.$keyword_guid.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
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
			$request = $this->base_uri . '/keywords/v1/keywords/'.$keyword_guid.'?hapikey=' . static::$api_key;
			return $this->fetch( $request );
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
			// 'https://api.hubapi.com/deals/v1/deal/3865198?hapikey=demo'
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
		function get_all_deals( $limit = null, $offset = null, $properties = null, $properties_with_history = null, $associations ){
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
		 * Add SMTP Token.
		 *
		 * @access public
		 * @param mixed $createdby Created By.
		 * @param mixed $campaign_name Campaign Name.
		 * @return void
		 */
		function add_smtp_token( $createdby, $campaign_name ) {
			$request = $this->base_uri . '/email/public/v1/smtpapi/tokens?hapikey=' . static::$api_key;
			return $this->fetch( $request );
		}

		function reset_smtp_api_token( $user_name ) {

		}

		/* Workflows. */

		/* Webhooks. */

	}

}
