<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://jonahgeluk.com/
 * @since      1.0.0
 *
 * @package    Nf_Cleanup
 * @subpackage Nf_Cleanup/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Nf_Cleanup
 * @subpackage Nf_Cleanup/includes
 * @author     Jonah Geluk <jonah@retailenclicks.nl>
 */
class Nf_Cleanup_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'nf-cleanup',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
