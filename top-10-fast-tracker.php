<?php
/**
 * Top 10 - Fast Tracker.
 *
 * Addon for Top 10 WordPress plugin to use a high speed tracker.
 *
 * @package   Top_Ten_Fast_Tracker
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2016-2024 WebberZone
 *
 * @wordpress-plugin
 * Plugin Name: Top 10 - Fast Tracker
 * Plugin URI:  https://github.com/WebberZone/top-10-fast-tracker/
 * Description: Addon for Top 10 WordPress plugin to use a high speed tracker
 * Version:     1.1.0
 * Author:      WebberZone
 * Author URI:  https://webberzone.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: top-10-fast-tracker
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WebberZone/top-10-fast-tracker/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 1.0.0
 *
 * @var string Plugin folder path
 */
if ( ! defined( 'TPTN_FT_PLUGIN_DIR' ) ) {
	define( 'TPTN_FT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 1.0.0
 *
 * @var string Plugin folder URL
 */
if ( ! defined( 'TPTN_FT_PLUGIN_URL' ) ) {
	define( 'TPTN_FT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Holds the location of this file
 *
 * @since 1.0.0
 *
 * @var string Plugin Root File
 */
if ( ! defined( 'TPTN_FT_PLUGIN_FILE' ) ) {
	define( 'TPTN_FT_PLUGIN_FILE', __FILE__ );
}


/**
 * Use external tracker.
 *
 * @since 1.0.0
 *
 * @param string $home_url URL of Top script.
 * @return string
 */
function tptnft_tracker( $home_url ) {
	global $tptn_settings;

	if ( 'fast_tracker' === $tptn_settings['tracker_type'] ) {
		return TPTN_FT_PLUGIN_URL . 'includes/fast-tracker-js.php';
	} else {
		return $home_url;
	}
}
add_filter( 'tptn_add_counter_script_url', 'tptnft_tracker' );


/*
----------------------------------------------------------------------------*
 * Include files
 *---------------------------------------------------------------------------*
 */

require_once TPTN_FT_PLUGIN_DIR . 'includes/hooks.php';
