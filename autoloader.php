<?php
/**
 * Dynamically loads the class attempting to be instantiated elsewhere in the
 * plugin.
 *
 * @package WP-API-Libraries\WP-HubSpot-API
 */

 // Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wp_hubspot_api_autoload' ) ) {
	/**
	 * Dynamically loads the class attempting to be instantiated elsewhere in the
	 * plugin by looking at the $class_name parameter being passed as an argument.
	 *
	 * The argument should be in the form: WP_Queue\Namespace. The
	 * function will then break the fully-qualified class name into its pieces and
	 * will then build a file to the path based on the namespace.
	 *
	 * The namespaces in this plugin map to the paths in the directory structure.
	 *
	 * @param string $class_name The fully-qualified name of the class to load.
	 */
	function wp_hubspot_api_autoload( $class_name ) {
		if ( false === strpos( $class_name, 'WP_Hubspot_API' ) ) {

			$file = 'src/class-' . strtolower( str_replace('_', '-', $class_name ) ) . '.php';
			if( file_exists( $file ) ) {
				include_once $file;
			}
		}
	}
}

spl_autoload_register( 'wp_hubspot_api_autoload' );
