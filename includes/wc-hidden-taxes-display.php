<?php
/**
 * Filter/override WooCommerce's display of taxes in the cart and order screens,
 * in order to hide any hidden tax rates from the customer.
 *
 * Also hides the hidden tax rates from the order-related emails that get sent to the
 * customer and the store owner.
 *
 * Does not hide the hidden tax rates from the View/Edit order screens.
 *
 * @package    WooCommerce Hidden Taxes
 * @author     OM4
 * @since      1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	// @codingStandardsIgnoreStart
	// @codeCoverageIgnoreStart
	exit;
	// @codeCoverageIgnoreEnd
	// @codingStandardsIgnoreEnd
}

/**
 * Class WC_Hidden_Taxes_Display
 */
class WC_Hidden_Taxes_Display {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_cart_tax_totals', array( $this, 'woocommerce_tax_totals' ) , 10, 2 );
		add_filter( 'woocommerce_order_tax_totals', array( $this, 'woocommerce_tax_totals' ) , 10, 2 );
	}


	/**
	 * Intercept whenever tax totals are displayed, and hide hidden tax rates from the customer.
	 *
	 * @param array            $tax_totals    Tax totals.
	 * @param WC_Cart|WC_Order $cart_or_order Cart or order object.
	 *
	 * @return mixed
	 */
	public function woocommerce_tax_totals( $tax_totals, $cart_or_order ) {
		if ( is_admin() ) {
			return $tax_totals;
		}
		foreach ( $tax_totals as $key => $value ) {
			if ( wc_hidden_taxes()->is_hidden_tax_rate( isset( $value->tax_rate_id ) ? $value->tax_rate_id : $value->rate_id ) ) {
				unset( $tax_totals[ $key ] );
			}
		}
		return $tax_totals;
	}
}
