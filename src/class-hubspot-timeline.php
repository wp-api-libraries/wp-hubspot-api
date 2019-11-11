<?php
/**
 * Hubspot Timeline.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Timeline.
	 */
	class HubSpot_Timeline extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * [add_or_update_timeline_event description]
		 * @param [type] $app_id [description]
		 * @param [type] $args   [description]
		 */
		public function add_or_update_timeline_event( $app_id, $args ) {
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
		public function get_timeline_event( $app_id, $event_type_id, $event_id ) {
			return $this->run( "integrations/v1/$app_id/timeline/event/$event_type_id/$event_id" );
		}

		/**
		 * [get_timeline_event_types description]
		 *
		 * @see https://developers.hubspot.com/docs/methods/timeline/get-event-types   Documentation
		 * @return [type] [description]
		 */
		public function get_timeline_event_types( $app_id ) {
			return $this->run( "integrations/v1/$app_id/timeline/event-types" );
		}

		public function add_new_timeline_event_type( $app_id, $user_id, $name, $args = array() ) {
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
		public function update_timeline_event_type( $app_id, $event_type_id, $args = array() ) {
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
		public function delete_timeline_event_type( $app_id, $event_type_id, $user_id ) {
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
		public function get_properties_for_timeline_event_type( $app_id, $event_type_id ) {
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
		public function add_property_for_timeline_event_type( $app_id, $event_type_id, $name, $label, $property_type, $args = array() ) {

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
		public function update_property_for_timeline_event_type( $app_id, $event_type_id, $prop_id, $args = array() ) {
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
		public function delete_property_for_timeline_event_type( $app_id, $event_type_id, $prop_id ) {
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
		public function create_timeline_event_type( $app_id, $name, $header_template = null, $detail_template = null, $object_type = null ) {
			return $this->run( 'integrations/v1/' . $app_id . '/timeline/event-types' );
		}

	}

	new HubSpot_Timeline();

}
