<?php
/**
 * Hubspot Products.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

namespace WP_Hubspot_API;

/* Exit if accessed directly. */
defined( 'ABSPATH' ) || exit;

	/**
	 * HubSpot Products.
	 */
	class HubSpot_Products extends HubSpotAPI {

		/**
		 * [__construct description]
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * [get_all_products description]
		 * @param  [type] $offset     [description]
		 * @param  [type] $properties [description]
		 * @return [type]             [description]
		 */
		public function get_all_products( $offset = null, $properties = null ) {
			return $this->run(
				'crm-objects/v1/objects/products/paged',
				array(
					'offset'     => $offset,
					'properties' => $properties,
				)
			);
		}
		/**
		 * [get_product_by_id description]
		 * @param  [type] $product_id      [description]
		 * @param  [type] $properties      [description]
		 * @param  [type] $include_deleted [description]
		 * @return [type]                  [description]
		 */
		public function get_product_by_id( $product_id, $properties = null, bool $include_deleted = null ) {
			return $this->run(
				"crm-objects/v1/objects/products/$product_id",
				array(
					'includeDeletes' => $include_deleted,
					'properties'     => $properties,
				)
			);
		}
		/**
		 * [get_batch_products_by_id description]
		 * @param  [type] $ids             [description]
		 * @param  [type] $properties      [description]
		 * @param  [type] $include_deleted [description]
		 * @return [type]                  [description]
		 */
		public function get_batch_products_by_id( $ids, $properties = null, bool $include_deleted = null ) {
			return $this->run(
				'crm-objects/v1/objects/products/batch-read',
				array(
					'ids'            => $ids,
					'properties'     => $properties,
					'includeDeletes' => $include_deleted,
				)
			);
		}
		/**
		 * [create_product description]
		 * @param  [type] $product_json [description]
		 * @return [type]               [description]
		 */
		public function create_product( $product_json ) {
			return $this->run( 'crm-objects/v1/objects/products', $product_json, 'POST' );
		}
		/**
		 * [create_product_batch description]
		 * @param  [type] $product_json [description]
		 * @return [type]               [description]
		 */
		public function create_product_batch( $product_json ) {
			return $this->run( 'crm-objects/v1/objects/products/batch-create', $product_json, 'POST' );
		}
		/**
		 * [update_product description]
		 * @param  [type] $product_id   [description]
		 * @param  [type] $product_json [description]
		 * @return [type]               [description]
		 */
		public function update_product( $product_id, $product_json ) {
			return $this->run( "crm-objects/v1/objects/products/$product_id", $product_json, 'POST' );
		}
		/**
		 * [update_product_batch description]
		 * @param  [type] $product_json [description]
		 * @return [type]               [description]
		 */
		public function update_product_batch( $product_json ) {
			return $this->run( 'crm-objects/v1/objects/products/batch-update', $product_json, 'POST' );
		}
		/**
		 * [delete_product description]
		 * @param  [type] $product_id [description]
		 * @return [type]             [description]
		 */
		public function delete_product( $product_id ) {
			return $this->run( "crm-objects/v1/objects/products/$product_id", array(), 'DELETE' );
		}
		/**
		 * [delete_product_batch description]
		 * @param  [type] $ids [description]
		 * @return [type]      [description]
		 */
		public function delete_product_batch( $ids ) {
			return $this->run( 'crm-objects/v1/objects/products/batch-delete', array( 'ids' => $ids ), 'POST' );
		}
		/**
		 * [get_changed_product_log description]
		 * @param  [type] $args [description]
		 * @return [type]       [description]
		 */
		public function get_changed_product_log( $args ) {
			return $this->run( 'crm-objects/v1/change-log/products', $args );
		}

	}

	new HubSpot_Products();

}
