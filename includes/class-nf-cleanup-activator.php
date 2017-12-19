<?php

/**
 * Fired during plugin activation
 *
 * @link       https://jonahgeluk.com/
 * @since      1.0.0
 *
 * @package    Nf_Cleanup
 * @subpackage Nf_Cleanup/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Nf_Cleanup
 * @subpackage Nf_Cleanup/includes
 * @author     Jonah Geluk <jonah@retailenclicks.nl>
 */

include_once 'activate-funcs.php';

class Nf_Cleanup_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

	public static function activate()
    {
        //register_activation_hook(__FILE__, 'nfc_install');
        //register_activation_hook(__FILE__, 'nfc_insert_data');
        //nfc_install();
        //nfc_insert_data();

        plugin_db_version_check();
	}




}
