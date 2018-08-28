<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/HayPils
 * @since             1.0.0
 * @package           Wp_Foosnines
 *
 * @wordpress-plugin
 * Plugin Name:       FoosNINES
 * Plugin URI:        https://github.com/HayPils/wp-foosnines
 * Description:       Builds a site that tracks 5NINES foosball events and player stats.
 * Version:           2.0.0
 * Author:            Hayden Pilsner
 * Author URI:        https://github.com/HayPils
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-foosnines
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'FOOSNINES_VERSION', '2.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-foosnines-activator.php
 */
function activate_wp_foosnines() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-foosnines-activator.php';
	Wp_Foosnines_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-foosnines-deactivator.php
 */
function deactivate_wp_foosnines() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-foosnines-deactivator.php';
	Wp_Foosnines_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_foosnines' );
register_deactivation_hook( __FILE__, 'deactivate_wp_foosnines' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-foosnines.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_foosnines() {

	$plugin = new Wp_Foosnines();
	$plugin->run();

}
run_wp_foosnines();
