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

		add_action( 'wp_ajax_woocommerce_tax_rates_save_changes', array( $this, 'save_tax_rate_hidden_status' ), 9 );

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
		wp_enqueue_script( 'woocommerce_hidden_taxes', wc_hidden_taxes::$plugin_url . 'js/admin' . $suffix . '.js', array( 'jquery', 'wc-settings-tax' ), wc_hidden_taxes::version, true );

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
	public function save_tax_rate_hidden_status( ) {

		// nonce and cap checks are copied from WC_AJAX::tax_rates_save_changes()
		// Use return instead of exit so that WooCommerce core will handle the AJAX error messages

		if ( ! isset( $_POST['wc_tax_nonce'], $_POST['changes'] ) ) {
			return;
		}

		$current_class = ! empty( $_POST['current_class'] ) ? $_POST['current_class'] : ''; // This is sanitized seven lines later.

		if ( ! wp_verify_nonce( $_POST['wc_tax_nonce'], 'wc_tax_nonce-class:' . $current_class ) ) {
			return;
		}

		// Check User Caps
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$hidden_rates = array();

		$current_class = WC_Tax::format_tax_rate_class( $current_class );

		$rates = WC_Tax::get_rates_for_tax_class( $current_class );

		foreach ( $rates as $tax_rate_id => $rate ) {

			if ( isset($_POST['tax_rate_hidden'][$tax_rate_id]) ) {
				// @codingStandardsIgnoreEnd
				$hidden_rates[$tax_rate_id] = true;
			} else {
				if ( isset($hidden_rates[$tax_rate_id]) ) {
					unset($hidden_rates[$tax_rate_id]);
				}
			}

		}

		wc_hidden_taxes()->set_option( 'hidden_rates', $hidden_rates );
	}
}
