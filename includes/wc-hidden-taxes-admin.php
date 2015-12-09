<?php
/**
 * Administration (dashboard) functionality
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
 * Class WC_Hidden_Taxes_Admin
 */
class WC_Hidden_Taxes_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'woocommerce_tax_rate_added', array( $this, 'save_tax_rate_hidden_status' ) );
		add_action( 'woocommerce_tax_rate_updated', array( $this, 'save_tax_rate_hidden_status' ) );
		add_action( 'woocommerce_tax_rate_deleted', array( $this, 'save_tax_rate_hidden_status' ) );

		add_action( 'woocommerce_settings_tax', array( $this, 'woocommerce_settings_tax' ) );

	}

	/**
	 * Executed whenever one of the WooCommerce tax settings screens are loaded.
	 *
	 * Unfortunately the tax display screen doesn't include any filters or actions for us to add our additional field to,
	 * so instead we do it via jQuery.
	 */
	public function woocommerce_settings_tax() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// @TODO: Tooltips don't display because woocommerce_admin.js fires in the header (before our additional td is added via jQuery)
		wp_enqueue_script( 'woocommerce_hidden_taxes', wc_hidden_taxes::$plugin_url . 'js/admin' . $suffix . '.js', array( 'jquery' ), wc_hidden_taxes::version, true );

		wp_localize_script( 'woocommerce_hidden_taxes', 'wc_hidden_taxes',
			array(
				'hidden_rates'   => wc_hidden_taxes()->get_hidden_tax_rates(),
				'hidden_label'   => __( 'Hidden', 'woocommerce-hidden-taxes' ),
				'hidden_tooltip' => esc_attr( __( 'Choose whether or not this tax rate is hidden from customers.', 'woocommerce-hidden-taxes' ) ),
			)
		);
	}

	/**
	 * Whenever a WooCommerce tax rate is saved, also save the tax rate's hidden status.
	 *
	 * Executed whenever a WooCommerce tax rate is added, updated or deleted.
	 *
	 * @param int $tax_rate_id The WooCommerce Tax Rate ID.
	 */
	public function save_tax_rate_hidden_status( $tax_rate_id ) {
		$hidden_rates = wc_hidden_taxes()->get_hidden_tax_rates();
		if ( isset( $_POST['tax_rate_hidden'][ $tax_rate_id ] ) ) {
			$hidden_rates[ $tax_rate_id ] = true;
		} else {
			if ( isset( $hidden_rates[ $tax_rate_id ] ) ) {
				unset( $hidden_rates[ $tax_rate_id ] );
			}
		}
		wc_hidden_taxes()->set_option( 'hidden_rates', $hidden_rates );
	}
}
