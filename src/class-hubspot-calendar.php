<?php
/**
 * Hubspot Calendar.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Calendar.
	 */
	class HubSpot_Calendar extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Calendar - List content events.
		 *
		 * @access public
		 * @param string                                  $start_date Start Date.
		 * @param string                                  $end_date End Date.
		 * @param mixed  Optional args to send to request.
		 * @return void
		 */
		public function get_content_events( $start_date, $end_date, $args = array() ) {
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
		public function get_social_events( $start_date, $end_date, $args = array() ) {
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
		public function get_task_events( $start_date, $end_date, $args = array() ) {
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
		public function get_all_events( $start_date, $end_date, $args = array() ) {
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
		public function create_task( $args ) {
			return $this->run( 'calendar/v1/events/task', $args, 'POST' );
		}

		/**
		 * Get Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		public function get_task( $task_id ) {
			return $this->run( "calendar/v1/events/task/$task_id" );
		}

		/**
		 * Update Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		public function update_task( $task_id, $args ) {
			return $this->run( "calendar/v1/events/task/$task_id", $args, 'PUT' );
		}

		/**
		 * Delete Task.
		 *
		 * @access public
		 * @param mixed $task_id Task ID.
		 * @return void
		 */
		public function delete_task( $task_id ) {
			return $this->run( "calendar/v1/events/task/$task_id", array(), 'DELETE' );
		}
	}

	new HubSpot_Companies();

}
