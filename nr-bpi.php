<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.nativerank.com
 * @since             1.0.0
 * @package           Nr_Bpi
 *
 * @wordpress-plugin
 * Plugin Name:       nativerank-biote-providers-importer
 * Plugin URI:        https://www.nativerank.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Sahil Khanna
 * Author URI:        https://www.nativerank.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nr-bpi
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'NR_BIOTE_PROVIDERS_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nr-bpi-activator.php
 */
function activate_nr_bpi() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nr-bpi-activator.php';
	Nr_Bpi_Activator::activate();
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'nr_bpi_providers';

    $sql = "CREATE TABLE $table_name (
		company_id int(20) NOT NULL,
		post_id INT(11), name VARCHAR(150), street VARCHAR(80), city VARCHAR(40), state VARCHAR(40), zip CHAR(6), website VARCHAR(2083), phone VARCHAR(20), latitude DECIMAL(10, 8), longitude DECIMAL(11, 8), slug varchar(2083),
		PRIMARY KEY (company_id)
	) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nr-bpi-deactivator.php
 */
function deactivate_nr_bpi() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nr-bpi-deactivator.php';
	Nr_Bpi_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_nr_bpi' );
register_deactivation_hook( __FILE__, 'deactivate_nr_bpi' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-nr-bpi.php';



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_nr_bpi() {

	$plugin = new Nr_Bpi();
	$plugin->run();

}
run_nr_bpi();
