<?php
/**
 * Class Checkout Fields
 *
 * @package    Radish_Checkout_Fields
 */

namespace Radish_Checkout_Fields;

/**
 * Radish Checkout Fields
 *
 * Class for altering and repositioning the checkout fields.
 *
 * @category   Components
 * @package    Radish_Checkout_Fields
 * @subpackage Checkout_Fields
 * @author     Radish Concepts <info@radishconcepts.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://radishconcepts.com
 * @since      1.0.0
 */
class Checkout_Fields {
	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialisation of the plugin.
	 *
	 * @since 1.0
	 */
	public function init() {
		add_filter( 'woocommerce_billing_fields', [ $this, 'alter_billing_checkout_fields' ], 20 );
		add_filter( 'woocommerce_shipping_fields', [ $this, 'alter_shipping_checkout_fields' ], 20 );

		if ( ! is_plugin_active( 'wc-postcode-checker/wc-postcode-checker.php' ) ) {
			add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'process_fields' ] );
			add_action(
				'woocommerce_admin_order_data_after_billing_address',
				[
					$this,
					'show_extra_billing_fields',
				]
			);
			add_action(
				'woocommerce_admin_order_data_after_shipping_address',
				[
					$this,
					'show_extra_shipping_fields',
				]
			);
		}

		add_filter( 'woocommerce_checkout_fields', [ $this, 'checkout_move_country_field' ], 20 );

		add_filter( 'wpo_wcnlpc_postcode_field_countries', [ $this, 'checkout_get_wc_countries' ] );

		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'update_order_review' ] );

		add_filter( 'woocommerce_admin_billing_fields', [ $this, 'admin_address_fields' ] );

		add_filter( 'woocommerce_admin_shipping_fields', [ $this, 'admin_address_fields' ] );
	}

	/**
	 * Calls a universal function with an argument.
	 *
	 * @param array $fields Array of fields.
	 *
	 * @since 1.0
	 *
	 * @return array an array of updated fields.
	 */
	public function alter_billing_checkout_fields( $fields ) {
		return $this->alter_checkout_fields( $fields, 'billing' );
	}

	/**
	 * Calls a universal function with an argument.
	 *
	 * @param array $fields Array of fields.
	 *
	 * @since 1.0
	 *
	 * @return array an array of updated fields.
	 */
	public function alter_shipping_checkout_fields( $fields ) {
		return $this->alter_checkout_fields( $fields, 'shipping' );
	}

	/**
	 * Actually change the Checkout fields
	 *
	 * @param array $fields Array of fields.
	 *
	 * @param array $form An array of form data.
	 *
	 * @since 1.0
	 *
	 * @return array An updated array with all fields
	 */
	public function alter_checkout_fields( $fields, $form ) {
		$fields[ $form . '_address_1' ]['class'] = array_merge( $fields[ $form . '_address_1' ]['class'], [ 'form-row-third' ] );

		$extra_fields = [];

		if ( ! isset( $fields[ $form . '_street_name' ] ) ) {
			$extra_fields[ $form . '_street_name' ] = [
				'label'        => __( 'Street name', 'radish-checkout-fields' ),
				'required'     => true,
				'priority'     => 60,
				'class'        => [ 'form-row-quart-first' ],
				'autocomplete' => 'street_address',
				'default'      => ( ! empty( WC()->session->get( $form . '_street_name' ) ) ? WC()->session->get( $form . '_street_name' ) : '' ),
			];
		}

		if ( ! isset( $fields[ $form . '_house_number' ] ) ) {
			$extra_fields[ $form . '_house_number' ] = [
				'label'        => __( 'Nr.', 'radish-checkout-fields' ),
				'required'     => true,
				'priority'     => 61,
				'class'        => [ 'form-row-quart-first' ],
				'autocomplete' => 'number',
				'default'      => ( ! empty( WC()->session->get( $form . '_house_number' ) ) ? WC()->session->get( $form . '_house_number' ) : '' ),
			];
		}
		if ( ! isset( $fields[ $form . '_house_number_suffix' ] ) ) {
			$extra_fields[ $form . '_house_number_suffix' ] = [
				'label'        => __( 'Suffix', 'radish-checkout-fields' ),
				'required'     => false,
				'priority'     => 62,
				'class'        => [ 'form-row-fifth' ],
				'autocomplete' => 'suffix',
				'default'      => ( ! empty( WC()->session->get( $form . '_house_number_suffix' ) ) ? WC()->session->get( $form . '_house_number_suffix' ) : '' ),
			];
		}

		$extra_fields = apply_filters( 'radish_separate_address_fields', $extra_fields );

		return array_merge( $fields, $extra_fields );
	}

	/**
	 * Processes the fields and combines the separated fields back into one field.
	 *
	 * @param int $order_id The targeted order.
	 *
	 * @since 1.0
	 */
	public function process_fields( $order_id ) {

		// An array with default data.
		$address_data = [
			'billing'  => [
				'street_name'         => '',
				'house_number'        => '',
				'house_number_suffix' => '',
			],
			'shipping' => [
				'street_name'         => '',
				'house_number'        => '',
				'house_number_suffix' => '',
			],
		];

		// Build an array of addressData.
		foreach ( $address_data as $form => $data ) {

			if ( isset( $_POST[ $form . '_street_name' ] ) && ! empty( $_POST[ $form . '_street_name' ] ) ) { // phpcs:ignore
				$address_data[ $form ]['street_name'] = sanitize_text_field( wp_unslash( $_POST[ $form . '_street_name' ] ) ); // phpcs:ignore
			}
			if ( isset( $_POST[ $form . '_house_number' ] ) && ! empty( $_POST[ $form . '_house_number' ] ) ) { // phpcs:ignore
				$address_data[ $form ]['house_number'] = sanitize_text_field( wp_unslash( $_POST[ $form . '_house_number' ] ) ); // phpcs:ignore
			}
			if ( isset( $_POST[ $form . '_house_number_suffix' ] ) && ! empty( $_POST[ $form . '_house_number_suffix' ] ) ) { // phpcs:ignore
				$address_data[ $form ]['house_number_suffix'] = sanitize_text_field( wp_unslash( $_POST[ $form . '_house_number_suffix' ] ) ); // phpcs:ignore
			}
		}

		// Loop over again, now with new data.
		foreach ( $address_data as $form => $data ) {

			if ( $form === 'shipping' && ! isset( $_POST['ship_to_different_address'] ) ) { // phpcs:ignore
				$street_name         = $address_data['billing']['street_name'];
				$house_number        = $address_data['billing']['house_number'];
				$house_number_suffix = $address_data['billing']['house_number_suffix'];
			} else {
				$street_name         = $data['street_name'];
				$house_number        = $data['house_number'];
				$house_number_suffix = $data['house_number_suffix'];
			}

			// Update individual fields.
			update_post_meta( $order_id, '_' . $form . '_street_name', $street_name );
			update_post_meta( $order_id, '_' . $form . '_house_number', $house_number );
			update_post_meta( $order_id, '_' . $form . '_house_number_suffix', $house_number_suffix );

			// Finally combine the data back into address_1.
			update_post_meta( $order_id, '_' . $form . '_address_1', sanitize_text_field( $street_name . ' ' . $house_number . ' ' . $house_number_suffix ) );
		}

	}

	/**
	 * Universal function for viewing the separated fields on the admin view.
	 *
	 * @param array $order The order id.
	 *
	 * @param array $form An array of form data.
	 *
	 * @since 1.0
	 */
	public function show_on_admin_order( $order, $form ) {
		$street = get_post_meta( $order->get_id(), '_' . $form . '_street_name', true );
		if ( ! empty( $street ) ) {
			echo '<p><strong>' . esc_attr__( 'Street name', 'radish-checkout-fields' ) . ':</strong> ' . esc_attr( $street ) . '</p>';
		}

		$house_number = get_post_meta( $order->get_id(), '_' . $form . '_house_number', true );
		if ( ! empty( $house_number ) ) {
			echo '<p><strong>' . esc_attr__( 'Nr.', 'radish-checkout-fields' ) . ':</strong> ' . esc_attr( $house_number ) . '</p>';
		}

		$suffix = get_post_meta( $order->get_id(), '_' . $form . '_house_number_suffix', true );
		if ( ! empty( $suffix ) ) {
			echo '<p><strong>' . esc_attr__( 'Suffix', 'radish-checkout-fields' ) . ':</strong> ' . esc_attr( $suffix ) . '</p>';
		}
	}

	/**
	 * Show extra fields on billing.
	 *
	 * @param object $order The order id.
	 *
	 * @since 1.0
	 */
	public function show_extra_billing_fields( $order ) {
		$this->show_on_admin_order( $order, 'billing' );
	}

	/**
	 * Show extra fields on shipping
	 *
	 * @param object $order The order object.
	 *
	 * @since 1.0
	 */
	public function show_extra_shipping_fields( $order ) {
		$this->show_on_admin_order( $order, 'shipping' );
	}

	/**
	 * Move the country field up so that it's one of the first fields.
	 *
	 * @param array $checkout_fields An array of fields.
	 *
	 * @since 1.0
	 *
	 * @return array An updated array of fields
	 */
	public function checkout_move_country_field( $checkout_fields ) {
		$forms = [ 'billing', 'shipping' ];

		foreach ( $forms as $form ) {
			$checkout_fields[ $form ][ $form . '_country' ]['priority'] = 28;
			unset( $checkout_fields[ $form ][ $form . '_address_1' ] );
		}

		return $checkout_fields;
	}

	/**
	 * Add all countries to the postcode NL plugin to make it think that all countries are supported.
	 *
	 * @param array $country_codes An array of supported country codes.
	 *
	 * @since 1.0
	 *
	 * @return array An updated array of country codes.
	 */
	public function checkout_get_wc_countries( $country_codes ) {
		$countries = new \WC_Countries();
		$countries = $countries->__get( 'countries' );

		foreach ( $countries as $code => $country ) {
			$country_codes[] = $code;
		}

		return $country_codes;
	}

	/**
	 * Update Order Review
	 *
	 * Updates the fields in checkout so that house number and suffix are saved upon refresh.
	 *
	 * @param array $params An array of post data.
	 */
	public function update_order_review( $params ) {
		parse_str( $params, $data );

		WC()->session->set( 'billing_street_name', isset( $data['billing_street_name'] ) ? wp_unslash( $data['billing_street_name'] ) : null );
		WC()->session->set( 'billing_house_number', isset( $data['billing_house_number'] ) ? wp_unslash( $data['billing_house_number'] ) : null );
		WC()->session->set( 'billing_house_number_suffix', isset( $data['billing_house_number_suffix'] ) ? wp_unslash( $data['billing_house_number_suffix'] ) : null );

		if ( wc_ship_to_billing_address_only() ) {
			WC()->session->set( 'shipping_street_name', isset( $data['billing_street_name'] ) ? wp_unslash( $data['billing_street_name'] ) : null );
			WC()->session->set( 'shipping_house_number', isset( $data['billing_house_number'] ) ? wp_unslash( $data['billing_house_number'] ) : null );
			WC()->session->set( 'shipping_house_number_suffix', isset( $data['billing_house_number_suffix'] ) ? wp_unslash( $data['billing_house_number_suffix'] ) : null );
		} else {
			WC()->session->set( 'shipping_street_name', isset( $data['shipping_street_name'] ) ? wp_unslash( $data['shipping_street_name'] ) : null );
			WC()->session->set( 'shipping_house_number', isset( $data['shipping_house_number'] ) ? wp_unslash( $data['shipping_house_number'] ) : null );
			WC()->session->set( 'shipping_house_number_suffix', isset( $data['shipping_house_number_suffix'] ) ? wp_unslash( $data['shipping_house_number_suffix'] ) : null );
		}

	}

	/**
	 * Adds the separated fields as inputs in the admin, so fields can be managed there too.
	 *
	 * @param array $fields Array of existing fields.
	 *
	 * @return array Array of existing and new fields.
	 */
	public function admin_address_fields( $fields ) {
		$street_name = array(
			'street_name' => array(
				'label' => __( 'Street name', 'radish-checkout-fields' ),
				'show'  => false,
			),
		);

		array_splice( $fields, 3, 0, $street_name );

		$number = array(
			'number' => array(
				'label' => __( 'Nr.', 'radish-checkout-fields' ),
				'show'  => false,
			),
		);

		array_splice( $fields, 4, 0, $number );

		$suffix = array(
			'suffix' => array(
				'label' => __( 'Suffix', 'radish-checkout-fields' ),
				'show'  => false,
			),
		);

		array_splice( $fields, 5, 0, $suffix );

		return $fields;
	}

}
