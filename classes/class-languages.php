<?php
/**
 * Internationalization
 *
 * @link       https://radishconcepts.com
 * @since      1.0.0
 *
 * @package    Radish_Checkout_Fields
 * @subpackage Radish_Checkout_Fields/includes
 */

namespace Radish_Checkout_Fields;

/**
 * Internationalization function
 *
 * @since      1.0.0
 * @package    Radish_Checkout_Fields
 * @subpackage Radish_Checkout_Fields/includes
 * @author     Radish Concepts <info@radishconcepts.com>
 */
class Languages {

	/**
	 * Initialisation
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'radish-checkout-fields',
			false,
			basename( dirname( dirname( __FILE__ ) ) ) . '/languages'
		);
	}
}