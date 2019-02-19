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

namespace Radish_Checkout_Fields;

class Radish_Checkout_Fields {
	function __construct() {
		$this->init();
	}
	
	public function init() {
		add_filter( 'woocommerce_billing_fields', array( $this, 'alter_billing_checkout_fields' ), 20 );
		add_filter( 'woocommerce_shipping_fields', array( $this, 'alter_shipping_checkout_fields' ), 20 );
		
		if ( ! is_plugin_active( 'wc-postcode-checker/wc-postcode-checker.php' ) ) {
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'process_fields' ) );
			add_action( 'woocommerce_admin_order_data_after_billing_address', array(
				$this,
				'show_extra_billing_fields'
			) );
			add_action( 'woocommerce_admin_order_data_after_shipping_address', array(
				$this,
				'show_extra_shipping_fields'
			) );
		}
		
		add_filter( 'woocommerce_checkout_fields', array( $this, 'checkout_move_country_field' ), 20 );
		
		add_filter( 'woocommerce_default_address_fields', array( $this, 'checkout_change_address_1_placeholder' ), 20 );
		
		add_filter( 'wpo_wcnlpc_postcode_field_countries', function ( $country_codes ) {
			$countries = new \WC_Countries();
			$countries = $countries->__get( 'countries' );
			
			foreach ( $countries as $code => $country ) {
				$country_codes[] = $code;
			}
			
			return $country_codes;
		} );
	}
	
	public function alter_billing_checkout_fields( $fields ) {
		return $this->alter_checkout_fields( $fields, 'billing' );
	}
	
	public function alter_shipping_checkout_fields( $fields ) {
		return $this->alter_checkout_fields( $fields, 'shipping' );
	}
	
	public function alter_checkout_fields( $fields, $form ) {
		$fields[ $form . '_address_1' ]['class'] = array_merge( $fields[ $form . '_address_1' ]['class'], array( 'form-row-third' ) );
		
		
		$extra_fields = array();
		
		if ( ! isset( $fields[ $form . '_house_number' ] ) ) {
			$extra_fields[ $form . '_house_number' ] = array(
				'label'        => __( 'Nr.', 'radish-checkout-fields' ),
				'required'     => true,
				'priority'     => 61,
				'class'        => array( 'form-row-quart-first' ),
				'autocomplete' => 'number',
			);
		}
		if ( ! isset( $fields[ $form . '_house_number_suffix' ] ) ) {
			$extra_fields[ $form . '_house_number_suffix' ] = array(
				'label'        => __( 'Suffix', 'radish-checkout-fields' ),
				'required'     => false,
				'priority'     => 62,
				'class'        => array( 'form-row-fifth' ),
				'autocomplete' => 'suffix',
			);
		}
		
		$extra_fields = apply_filters( 'radish_separate_address_fields', $extra_fields );
		
		return array_merge( $fields, $extra_fields );
	}
	
	public function process_fields( $order_id ) {
		
		// Default array.
		$addressData = array(
			'billing'  => array(
				'street_name'         => '',
				'house_number'        => '',
				'house_number_suffix' => ''
			),
			'shipping' => array(
				'street_name'         => '',
				'house_number'        => '',
				'house_number_suffix' => ''
			)
		);
		
		// Build an array of addressData.
		foreach ( $addressData as $form => $data ) {
			
			if ( isset( $_POST[ $form . '_address_1' ] ) && ! empty( $_POST[ $form . '_address_1' ] ) ) {
				$addressData[ $form ]['street_name'] = sanitize_text_field( $_POST[ $form . '_address_1' ] );
			}
			if ( isset( $_POST[ $form . '_house_number' ] ) && ! empty( $_POST[ $form . '_house_number' ] ) ) {
				$addressData[ $form ]['house_number'] = sanitize_text_field( $_POST[ $form . '_house_number' ] );
			}
			if ( isset( $_POST[ $form . '_house_number_suffix' ] ) && ! empty( $_POST[ $form . '_house_number_suffix' ] ) ) {
				$addressData[ $form ]['house_number_suffix'] = sanitize_text_field( $_POST[ $form . '_house_number_suffix' ] );
			}
		}
		
		// Loop over again, now with new data.
		foreach ( $addressData as $form => $data ) {
			
			$street_name         = $data['street_name'];
			$house_number        = $data['house_number'];
			$house_number_suffix = $data['house_number_suffix'];
			
			// Shipping fields are not always available.
			if ( $form === 'shipping' ) {
				if ( empty( $street_name ) ) {
					$street_name = $addressData['billing']['street_name'];
				}
				if ( empty( $house_number ) ) {
					$house_number = $addressData['billing']['house_number'];
				}
				if ( empty( $house_number_suffix ) ) {
					$house_number_suffix = $addressData['billing']['house_number_suffix'];
				}
			}
			
			// Update individual fields.
			update_post_meta( $order_id, '_' . $form . '_street_name', $street_name );
			update_post_meta( $order_id, '_' . $form . '_house_number', $house_number );
			update_post_meta( $order_id, '_' . $form . '_house_number_suffix', $house_number_suffix );
			
			// Finally combine the data back into address_1.
			if ( ! empty( $street_name ) && ! empty( $house_number ) ) {
				update_post_meta( $order_id, '_' . $form . '_address_1', sanitize_text_field( $street_name . ' ' . $house_number . ' ' . $house_number_suffix ) );
			}
			
		}
		
		
	}
	
	public function show_on_admin_order( $order, $form ) {
		$street = get_post_meta( $order->get_id(), '_' . $form . '_address_1', true );
		if ( ! empty( $street ) ) {
			echo '<p><strong>' . __( 'Address', 'woocommerce' ) . ':</strong> ' . $street . '</p>';
		}
		
		$house_number = get_post_meta( $order->get_id(), '_' . $form . '_house_number', true );
		if ( ! empty( $house_number ) ) {
			echo '<p><strong>' . __( 'Nr.', 'radish-checkout-fields' ) . ':</strong> ' . $house_number . '</p>';
		}
		
		$suffix = get_post_meta( $order->get_id(), '_' . $form . '_house_number_suffix', true );
		if ( ! empty( $suffix ) ) {
			echo '<p><strong>' . __( 'Suffix', 'radish-checkout-fields' ) . ':</strong> ' . $suffix . '</p>';
		}
	}
	
	public function show_extra_billing_fields( $order ) {
		$this->show_on_admin_order( $order, 'billing' );
	}
	
	public function show_extra_shipping_fields( $order ) {
		$this->show_on_admin_order( $order, 'shipping' );
	}
	
	public function checkout_move_country_field( $checkout_fields ) {
		$forms = array( 'billing', 'shipping' );
		
		foreach ( $forms as $form ) {
			$checkout_fields[ $form ][ $form . '_country' ]['priority']      = 28;
			$checkout_fields[ $form ][ $form . '_address_1' ]['placeholder'] = __( "Street address", 'woocommerce' );
		}
		
		return $checkout_fields;
	}
	
	public function checkout_change_address_1_placeholder( $fields ) {
		$fields['address_1']['placeholder'] = __( 'Street' );
		
		return $fields;
	}
	
}

$radish_checkout_fields = new Radish_Checkout_Fields();