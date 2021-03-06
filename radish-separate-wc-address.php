<?php
/**
 * Plugin Name: Woocommerce - Separate Checkout Fields
 * Version: 1.0
 * Plugin URI: https://www.radishconcepts.com/wooocommerce-separate-checkout-fields
 * Description: Separates the address field in Woocommerce checkout
 * Author: Radish Concepts
 * Author URI: https://www.radishconcepts.com
 * Text Domain: radish-checkout-fields
 * Network: true
 * Domain Path: /languages/
 * License: GPL v3
 *
 * @package    Radish_Checkout_Fields
 * @author     Radish Concepts <info@radishconcepts.com>
 * @link       https://radishconcepts.com
 */

/**
 * Woocommerce - Separate Checkout Fields
 * Copyright (C) 2018, Radish Concepts BV - support@radishconcepts.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'RD_WC_ADDR_VERSION', '1.0' );
define( 'RD_WC_ADDR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Require all files with composer.
 */

$autoload_file = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( is_readable( $autoload_file ) ) {
	require $autoload_file;
}

new Radish_Checkout_Fields\Languages();
new Radish_Checkout_Fields\Checkout_Fields();
new Radish_Checkout_Fields\Front_End();
