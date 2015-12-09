<?php
/**
 * Plugin Name: WooCommerce Hidden Taxes
 * Plugin URI: https://om4.com.au/plugins/woocommerce-hidden-taxes/
 * Description: Hide one or more WooCommerce tax rates from your customers.
 * Version: 1.0
 * Author: OM4
 * Author URI: https://om4.com.au/plugins/
 * License: GPLv2+
 * Text Domain: woocommerce-hidden-taxes
 * Domain Path: /languages
 * Git URI: https://github.com/OM4/woocommerce-hidden-taxes
 * Git Branch: release
 *
 * @package    WooCommerce Hidden Taxes
 * @author     OM4
 * @since      1.0
 */

/*
Copyright 2015 OM4 (email: info@om4.com.au    web: https://om4.com.au/)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( ! class_exists( 'WC_Hidden_Taxes' ) ) {

	/**
	 * This class is a singleton, and should be accessed via the wc_hidden_taxes() function.
	 *
	 * Class WC_Hidden_Taxes
	 */
	class WC_Hidden_Taxes {

		/**
		 * Plugin version (used for JS file versioning)
		 */
		const version = '1.0';

		/**
		 * Database version (used for install/upgrade tasks if required)
		 */
		const db_version = 1;

		/**
		 * The prefix used for all options for this plugin
		 */
		const option_prefix = 'woocommerce_hidden_taxes_';

		/**
		 * The minimum WooCommerce version that this plugin supports.
		 */
		const MINIMUM_SUPPORTED_WOOCOMMERCE_VERSION = '2.4.0';

		/**
		 * URL to the documentation for this plugin.
		 *
		 * @TODO Write this documentation
		 */
		const documentation_url = 'https://om4.com.au/plugins/woocommerce-hidden-taxes/docs/';

		/**
		 * Stores the one and only instance of this class
		 * @var WC_Hidden_Taxes
		 */
		private static $instance;

		/**
		 * Stores the WC_Hidden_Taxes_Admin class
		 * @var WC_Hidden_Taxes_Admin
		 */
		private $admin;

		/**
		 * Full path to this plugin's main file
		 * @var string
		 */
		public static $plugin_file;

		/**
		 * Full path to this plugin's directory (including trailing slash)
		 * @var string
		 */
		public static $plugin_path;

		/**
		 * Full URL to this plugin's directory (including trailing slash)
		 * @var string
		 */
		public static $plugin_url;

		/**
		 * The main WC_Hidden_Taxes instance.
		 *
		 * @return WC_Hidden_Taxes
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->initialise();
			}
			return self::$instance;
		}

		/**
		 * Initialise the plugin
		 */
		private function initialise() {

			self::$plugin_file = __FILE__;
			self::$plugin_path = dirname( self::$plugin_file ) . '/';
			self::$plugin_url  = plugin_dir_url( self::$plugin_file );

			// Set up class autoloading.
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}
			spl_autoload_register( array( $this, 'autoload' ) );

			load_plugin_textdomain( 'woocommerce-hidden-taxes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		}

		/**
		 * Autoload the WC_Hidden_Taxes_* classes when required.
		 * Helps keep code simple and memory consumption down.
		 *
		 * @param string $class_name The class name that is being requested.
		 */
		public function autoload( $class_name ) {

			// Only act on classes in this plugin.
			if ( false === strpos( $class_name, 'WC_Hidden_Taxes' ) ) {
				return;
			}

			$filename = "{$class_name}.php";
			$filename = strtolower( str_replace( '_', '-', $filename ) );

			$directory = self::$plugin_path . 'includes/';

			$file = $directory . $filename;

			if ( file_exists( $file ) ) {
				require_once( $file );
			}

		}

		/**
		 * Executed during the 'plugins_loaded' WordPress hook.
		 *
		 * - Checks that we're running the a supported WooCommerce Version
		 * - Sets up various hooks
		 * - Loads the admin/dashboard interface if required
		 */
		public function plugins_loaded() {

			if ( ! class_exists( 'WooCommerce' ) ) {
				// WooCommerce isn't active.
				return;
			}

			// WooCommerce version check.
			if ( version_compare( WOOCOMMERCE_VERSION, self::MINIMUM_SUPPORTED_WOOCOMMERCE_VERSION, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notice' ) );
				return;
			}

			// User has a supported version of WooCommerce, so let's proceed.
			add_action( 'init', array( $this, 'init' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		}

		/**
		 * Displays a message if the user isn't using a supported version of WooCommerce.
		 */
		public function admin_notice() {
			?>
			<div id="message" class="error">
				<p><?php esc_html_e( sprintf( __( 'The WooCommerce Hidden Taxes plugin is only compatible with WooCommerce version %s or later. Please update WooCommerce.', 'woocommerce-hidden-taxes' ), self::MINIMUM_SUPPORTED_WOOCOMMERCE_VERSION ) ); ?></p>
			</div>
			<?php
		}

		/**
		 * Initialises main classes and functionality
		 *
		 * Executed during the 'init' WordPress hook.
		 */
		public function init() {

			if ( is_admin() ) {
				$this->admin = new WC_Hidden_Taxes_Admin();
			}

			new WC_Hidden_Taxes_Display();

		}

		/**
		 * Retrieve an option/setting for this plugin.
		 *
		 * @param string $option_name The name/key of the option/setting.
		 *
		 * @return int|mixed|string|void
		 */
		public function get_option( $option_name ) {
			$value = get_option( self::option_prefix . $option_name );
			switch ( $option_name ) {
				case 'db_version':
					$value = intval( $value );
					break;
				case 'hidden_rates':
					if ( false === $value ) {
						$value = array();
					}
					break;
				default:

					break;
			}
			return $value;
		}

		/**
		 * Set/save an option/setting for this plugin.
		 *
		 * @param string $option_name  The name/key of the option/setting.
		 * @param string $option_value The value of the option/setting.
		 *
		 * @return bool
		 */
		public function set_option( $option_name, $option_value ) {
			if ( '' === $option_value ) {
				return delete_option( self::option_prefix . $option_name );
			}
			return update_option( self::option_prefix . $option_name, $option_value );
		}


		/**
		 * Adds additional link(s) to the plugins screen (near the deactivate link) for this plugin.
		 *
		 * @param array $links Array of links.
		 *
		 * @return array
		 */
		public function action_links( $links ) {

			$plugin_links = array(
					'<a href="' . self::documentation_url . '">' . __( 'Documentation', 'woocommerce-hidden-taxes' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}


		/**
		 * Check whether the specified tax rate is hidden.
		 *
		 * @param mixed $key_or_rate Tax rate ID, or the db row itself in object format.
		 */
		public function is_hidden_tax_rate( $key_or_rate ) {

			if ( is_object( $key_or_rate ) ) {
				$rate_id = $key_or_rate->tax_rate_id;
			} else {
				$rate_id = intval( $key_or_rate );
			}

			$hidden_rates = $this->get_hidden_tax_rates();

			if ( isset( $hidden_rates[ $rate_id ] ) ) {
				return true;
			}
			return false;

		}

		/**
		 * Get the list of hidden tax zones.
		 * @return array
		 */
		public function get_hidden_tax_rates() {
			return $this->get_option( 'hidden_rates' );
		}
	}

	/**
	 * This function should be used to access the WC_Hidden_Taxes singleton class.
	 *
	 * It's simpler to use this function instead of a global variable.
	 *
	 * @return WC_Hidden_Taxes
	 */
	function wc_hidden_taxes() {
		return WC_Hidden_Taxes::instance();
	}

	// Let's get this thing started!
	wc_hidden_taxes();

}
