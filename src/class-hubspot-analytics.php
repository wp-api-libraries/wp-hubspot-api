<?php
/**
 * Hubspot Analytics.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Analytics.
	 */
	class HubSpot_Analytics extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * [get_broken_down_analytics description]
		 * @param  [type] $breakdown_by [description]
		 * @param  [type] $time_period  [description]
		 * @param  [type] $starte_date  [description]
		 * @param  [type] $end_date     [description]
		 * @param  array  $opt_args     [description]
		 * @return [type]               [description]
		 */
		public function get_broken_down_analytics( $breakdown_by, $time_period, $starte_date, $end_date, $opt_args = array() ) {
			$args = $this->filter_args(
				$opt_args,
				array(
					'start' => $starte_date,
					'end'   => $end_date,
				)
			);
			return $this->run( "analytics/v2/reports/$breakdown_by/$time_period", $args );
		}
		/**
		 * [get_specific_object_analytics description]
		 * @param  [type] $object_type [description]
		 * @param  [type] $time_period [description]
		 * @param  [type] $starte_date [description]
		 * @param  [type] $end_date    [description]
		 * @param  array  $opt_args    [description]
		 * @return [type]              [description]
		 */
		public function get_specific_object_analytics( $object_type, $time_period, $starte_date, $end_date, $opt_args = array() ) {
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

	new HubSpot_Analytics();

}
