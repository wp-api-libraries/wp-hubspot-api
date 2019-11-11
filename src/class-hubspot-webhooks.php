<?php
/**
 * Hubspot Webhooks.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Webhooks.
	 */
	class HubSpot_Webhooks extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * list_subscriptions public function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @return void
		 */
		public function list_subscriptions( $app_id ) {
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions' );
		}

		/**
		 * create_subscription public function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @param mixed $subsription_type
		 * @param mixed $property_name
		 * @param bool  $enabled (default: true)
		 * @return void
		 */
		public function create_subscription( $app_id, $subsription_type, $property_name, $enabled = true ) {
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
		 * update_subscription public function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @param mixed $subscription_id
		 * @param bool  $enabled
		 * @return void
		 */
		public function update_subscription( $app_id, $subscription_id, bool $enabled ) {
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions/' . $subscription_id, array( 'enabled' => $enabled ), 'PUT' );
		}

		/**
		 * delete_subscription public function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @param mixed $subscription_id
		 * @return void
		 */
		public function delete_subscription( $app_id, $subscription_id ) {
			return $this->run( 'webhooks/v1/' . $app_id . '/subscriptions/' . $subscription_id );
		}

		/**
		 * get_webhook_settings public function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @return void
		 */
		public function get_webhook_settings( $app_id ) {
			return $this->run( 'webhooks/v1/' . $app_id . '/settings' );
		}

		/**
		 * update_settings public function.
		 *
		 * @access public
		 * @param mixed $app_id
		 * @param mixed $webhookUrl
		 * @param mixed $maxConcurrentRequests
		 * @return void
		 */
		public function update_settings( $app_id, $webhookUrl, $maxConcurrentRequests ) {
			$args = array(
				'webhookUrl'            => $webhookUrl,
				'maxConcurrentRequests' => $maxConcurrentRequests,
			);
			return $this->run( 'webhooks/v1/' . $app_id . '/settings', $args, 'PUT' );
		}



	}

	new HubSpot_Webhooks();

}
