<?php
/**
 * Functions that hook into Top 10.
 *
 * @package   Top_Ten_Fast_Tracker
 */

/**
 * Add option to tracker admin page.
 *
 * @since 1.0.0
 *
 * @param array $trackers Array of trackers.
 * @return array Trackers array with fast tracker appended
 */
function tptnft_add_tracker( $trackers ) {

	$trackers[] = array(
		'id'          => 'fast_tracker',
		'name'        => __( 'Fast tracker', 'top-10' ),
		'description' => __( 'Uses an external JavaScript file', 'top-10' ),
	);

	return $trackers;

}
add_filter( 'tptn_get_tracker_types', 'tptnft_add_tracker' );
