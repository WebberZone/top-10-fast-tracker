<?php
/**
 * Update counts to database.
 *
 * @package   Top_Ten_Fast_Tracker
 */

Header( 'content-type: application/x-javascript' );

// Force a short-init since we just need core WP, not the entire framework stack.
define( 'SHORTINIT', true );

// Build the wp-config.php path from a plugin/theme.
$wp_config_path     = dirname( dirname( dirname( __DIR__ ) ) );
$wp_config_filename = '/wp-load.php';

// Check if the file exists in the root or one level up.
if ( ! file_exists( $wp_config_path . $wp_config_filename ) ) {
	// Just in case the user may have placed wp-config.php one more level up from the root.
	$wp_config_filename = dirname( $wp_config_path ) . $wp_config_filename;
}
// Require the wp-config.php file.
require $wp_config_filename;

// Include the now instantiated global $wpdb Class for use.
global $wpdb;


/**
 * Use external tracker.
 *
 * @since 1.0.0
 */
function tptn_inc_count() {
	global $wpdb;
	$table_name       = $wpdb->base_prefix . 'top_ten';
	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';
	$str              = '';

	$id               = isset( $_POST['top_ten_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['top_ten_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$blog_id          = isset( $_POST['top_ten_blog_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['top_ten_blog_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$activate_counter = isset( $_POST['activate_counter'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['activate_counter'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$top_ten_debug    = isset( $_POST['top_ten_debug'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['top_ten_debug'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $id > 0 ) {

		if ( ( 1 === $activate_counter ) || ( 11 === $activate_counter ) ) {

			$tt = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES( %d, '1',  %d ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$str .= ( false === $tt ) ? 'tte' : 'tt' . $tt;
		}

		if ( ( 10 === $activate_counter ) || ( 11 === $activate_counter ) ) {

			$current_date = current_time( 'Y-m-d H' );

			$ttd = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name_daily} (postnumber, cntaccess, dp_date, blog_id) VALUES( %d, '1',  %s,  %d ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $current_date, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$str .= ( false === $ttd ) ? ' ttde' : ' ttd' . $ttd;
		}
	}

	// If the debug parameter is set then we output $str else we send a No Content header.
	if ( 1 === $top_ten_debug ) {
		echo esc_html( $str );
	} else {
		header( 'HTTP/1.0 204 No Content' );
		header( 'Cache-Control: max-age=15, s-maxage=0' );
	}
}

// Ajax Increment Counter.
tptn_inc_count();
