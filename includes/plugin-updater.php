<?php
/**
 * Plugin updater functions.
 *
 * @package   Top_Ten_Fast_Tracker
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2016-2017 WebberZone
 */

/**
 * URL of the WebberZone store.
 *
 * @since 1.0.0
 *
 * @var string WebberZone store.
 */
if ( ! defined( 'WZ_STORE_URL' ) ) {
	// define( 'WZ_STORE_URL', 'https://webberzone.com' );
	define( 'WZ_STORE_URL', 'https://ajaydsouza.org' );
}

/**
 * Name of the product.
 *
 * @since 1.0.0
 *
 * @var string Product name.
 */
if ( ! defined( 'TPTN_FT_PLUGIN_NAME' ) ) {
	define( 'TPTN_FT_PLUGIN_NAME', 'Top 10 Fast Tracker' );
}

// the name of the settings page for the license input to be displayed
define( 'TPTN_FT_PLUGIN_LICENSE_PAGE', 'tptnft_license_page' );

if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

/**
 * Plugin updater.
 *
 * @since 1.0.0
 */
function tptnft_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'tptnft_license_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( WZ_STORE_URL, TPTN_FT_PLUGIN_FILE, array(
			'version'   => '0.9.0',             // current version number
			'license'   => $license_key,        // license key (used get_option above to retrieve from DB)
			'item_name' => TPTN_FT_PLUGIN_NAME, // name of this plugin
			'author'    => 'WebberZone',        // author of this plugin
			'beta'		=> false
		)
	);

}
add_action( 'admin_init', 'tptnft_plugin_updater', 0 );


/**
 * Add a menu to access the license page of the plugin.
 *
 * @since 1.0.0
 */
function tptnft_license_menu() {
	// add_submenu_page( 'tptn_options', __( 'Plugin License', 'top-10' ), __( 'Plugin License', 'top-10' ), 'manage_options', 'tptnft_license_page', 'tptnft_license_page' );
	$plugin_page = add_submenu_page( 'tptn_options', __( 'Plugin License', 'top-10' ), __( 'Plugin License', 'top-10' ), 'manage_options', 'tptnft_license_page', 'tptnft_license_page' );
}
add_action( 'admin_menu', 'tptnft_license_menu', 11 );

/**
 * Generate the license page.
 *
 * @since 1.0.0
 */
function tptnft_license_page() {
	$license = get_option( 'tptnft_license_key' );
	$status  = get_option( 'tptnft_license_status' );
	?>
	<div class="wrap">
		<h2><?php _e( 'Plugin License Options' ); ?></h2>
		<form method="post" action="options.php">

			<?php settings_fields( 'tptnft_license' ); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'License Key' ); ?>
						</th>
						<td>
							<input id="tptnft_license_key" name="tptnft_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label class="description" for="tptnft_license_key"><?php _e( 'Enter your license key' ); ?></label>
						</td>
					</tr>
					<?php if ( false !== $license ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e( 'Activate License' ); ?>
							</th>
							<td>
								<?php if ( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e( 'active' ); ?></span>
									<?php wp_nonce_field( 'tptnft_nonce', 'tptnft_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_license_deactivate" value="<?php _e( 'Deactivate License' ); ?>"/>
								<?php } else {
									wp_nonce_field( 'tptnft_nonce', 'tptnft_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_license_activate" value="<?php _e( 'Activate License' ); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
	<?php
}

function tptnft_register_option() {
	// creates our settings in the options table
	register_setting( 'tptnft_license', 'tptnft_license_key', 'edd_sanitize_license' );
}
add_action( 'admin_init', 'tptnft_register_option' );

function edd_sanitize_license( $new ) {
	$old = get_option( 'tptnft_license_key' );
	if ( $old && $old != $new ) {
		delete_option( 'tptnft_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}



/**

 * *********************************
 * this illustrates how to activate
 * a license key
 *************************************/

function tptnft_activate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['edd_license_activate'] ) ) {

		// run a quick security check
	 	if ( ! check_admin_referer( 'tptnft_nonce', 'tptnft_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( get_option( 'tptnft_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( TPTN_FT_PLUGIN_NAME ), // the name of our product in EDD
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post( WZ_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}
		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch ( $license_data->error ) {

					case 'expired' :

						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'revoked' :

						$message = __( 'Your license key has been disabled.' );
						break;

					case 'missing' :

						$message = __( 'Invalid license.' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __( 'Your license is not active for this URL.' );
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), TPTN_FT_PLUGIN_NAME );
						break;

					case 'no_activations_left':

						$message = __( 'Your license key has reached its activation limit.' );
						break;

					default :

						$message = __( 'An error occurred, please try again.' );
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'admin.php?page=' . TPTN_FT_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"
		update_option( 'tptnft_license_status', $license_data->license );
		wp_redirect( admin_url( 'admin.php?page=' . TPTN_FT_PLUGIN_LICENSE_PAGE ) );
		exit();
	}
}
add_action( 'admin_init', 'tptnft_activate_license' );


/**

 * ********************************************
 * Illustrates how to deactivate a license key.
 * This will decrease the site count
 ***********************************************/

function tptnft_deactivate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['edd_license_deactivate'] ) ) {

		// run a quick security check
	 	if ( ! check_admin_referer( 'tptnft_nonce', 'tptnft_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( get_option( 'tptnft_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( TPTN_FT_PLUGIN_NAME ), // the name of our product in EDD
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post( WZ_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$base_url = admin_url( 'admin.php?page=' . TPTN_FT_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( $license_data->license == 'deactivated' ) {
			delete_option( 'tptnft_license_status' );
		}

		wp_redirect( admin_url( 'admin.php?page=' . TPTN_FT_PLUGIN_LICENSE_PAGE ) );
		exit();

	}
}
add_action( 'admin_init', 'tptnft_deactivate_license' );


/**

 * *********************************
 * this illustrates how to check if
 * a license key is still valid
 * the updater does this for you,
 * so this is only needed if you
 * want to do something custom
 *************************************/

function tptnft_check_license() {

	global $wp_version;

	$license = trim( get_option( 'tptnft_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( TPTN_FT_PLUGIN_NAME ),
		'url'       => home_url(),
	);

	// Call the custom API.
	$response = wp_remote_post( WZ_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( $license_data->license == 'valid' ) {
		echo 'valid';
		exit;
		// this license is still valid
	} else {
		echo 'invalid';
		exit;
		// this license is no longer valid
	}
}

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function tptnft_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch ( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

		}
	}
}
add_action( 'admin_notices', 'tptnft_admin_notices' );