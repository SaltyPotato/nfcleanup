<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://jonahgeluk.com/
 * @since             1.0.0
 * @package           Nf_Cleanup
 *
 * @wordpress-plugin
 * Plugin Name:       Ninja Forms Cleanup
 * Plugin URI:        https://github.com/SaltyPotato/nfcleanup
 * Description:       Wordpress plugin that removes duplicate submissions of Ninja Forms
 * Version:           1.0.0
 * Author:            Jonah Geluk
 * Author URI:        https://jonahgeluk.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nf-cleanup
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('NFVERSION', '3', false);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nf-cleanup-activator.php
 */
function activate_nf_cleanup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nf-cleanup-activator.php';
	Nf_Cleanup_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nf-cleanup-deactivator.php
 */
function deactivate_nf_cleanup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nf-cleanup-deactivator.php';
	Nf_Cleanup_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_nf_cleanup' );
register_deactivation_hook( __FILE__, 'deactivate_nf_cleanup' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-nf-cleanup.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_nf_cleanup() {

	$plugin = new Nf_Cleanup();
	$plugin->run();

}
run_nf_cleanup();
