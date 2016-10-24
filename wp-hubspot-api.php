<?php
/**
 * WP HubSpot API
 *
 * @package WP-HubSpot-API
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }


if ( ! class_exists( 'HubSpotAPI' ) ) {

	/**
	 * HubSpot API Class.
	 */
	class HubSpotAPI {

		/* Calendar. */

		function get_content_events() {
		}

		function get_social_events() {

		}

		function get_task_events() {

		}

		function create_task() {

		}

		function get_task( $task_id ) {

		}

		function update_task( $task_id ) {

		}

		function delete_task( $task_id ) {

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

		function get_company_contacts( $company_id, $vidoffset == '', $count = '' ) {

		}

		function get_company_contacts_ids( $company_id, $vidoffset == '', $count = '' ) {

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
