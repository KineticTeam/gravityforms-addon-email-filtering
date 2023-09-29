<?php
/**
 * Plugin Name: Gravity Forms Email Filtering Add-On
 * Plugin URI: https://github.com/KineticTeam/gravityforms-addon-email-filtering
 * Description: Adds the ability to filter domains on the email field. Forked from CrossPeak Software's "Gravity Forms Email Blacklist" plugin.
 * Version: 3.0.0
 * Requires PHP: 8.0
 * Author: Kinetic
 * Author URI: https://kinetic.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

add_action( 'gform_loaded', array( 'GFEmailBlacklist_Bootstrap', 'load' ), 5 );

/**
 * Gravity Forms Bootstrap class to load the Add-On library and new class.
 */
class GFEmailBlacklist_Bootstrap {

	/**
	 * Load the Add-On class after checking for the frame work.
	 */
	public static function load() {
		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once 'class-gf-email-filtering-addon.php';
		GFAddOn::register( 'GFEmailBlacklist' );
	}
}

/**
 * Init the class.
 *
 * @return object Return the instance of the Add-On class.
 */
function gf_email_blacklist_addon() {
	return GFEmailBlacklist::get_instance();
}

/**
 * Load plugin textdomain for localization.
 *
 * @return void
 */
function gf_email_blacklist_plugin_textdomain() {
	load_plugin_textdomain( 'gravity-forms-email-blacklist', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'gf_email_blacklist_plugin_textdomain' );
