<?php
/**
 * Hubspot Deal Pipelines.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Deal_Pipelines.
	 */
	class HubSpot_Deal_Pipelines extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Get Deal Pipelines.
		 *
		 * @access public
		 * @param mixed $pipeline_id Pipeline ID.
		 * @return void
		 */
		public function show_deal_pipeline( $pipeline_id ) {
			return $this->run( 'deals/v1/pipelines/' . $pipeline_id );
		}

		/**
		 * Get all Deal Pipelines.
		 *
		 * @access public
		 * @return void
		 */
		public function list_deal_pipelines() {
			return $this->run( 'deals/v1/pipelines' );
		}

		public function create_deal_pipeline( $label, $display_order, $stages ) {
			$args = array(
				'label'        => $label,
				'displayOrder' => $display_order,
				'stages'       => $stages,
			);
			return $this->run( 'deals/v1/pipelines', $args, 'POST' );
		}

		public function update_deal_pipeline( $pipeline_id, $label, $display_order, $stages ) {
			$args = array(
				'pipelineId'   => $pipeline_id,
				'label'        => $label,
				'displayOrder' => $display_order,
				'stages'       => $stages,
			);
			return $this->run( "deals/v1/pipelines/$pipeline_id", $args, 'POST' );
		}

		public function delete_deal_pipeline( $pipeline_id ) {
			return $this->run( "deals/v1/pipelines/$pipeline_id", $args, 'DELETE' );
		}

	}

	new HubSpot_Deal_Pipelines();

}
