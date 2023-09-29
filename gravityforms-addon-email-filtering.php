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

define('GF_EMAIL_FILTERING_ADDON_VERSION', '3.0.0');

add_action('gform_loaded', ['GF_Email_Filtering_AddOn_Bootstrap', 'load'], 5);

class GF_Email_Filtering_AddOn_Bootstrap
{
    public static function load(): void
    {
        if (! method_exists('GFForms', 'include_addon_framework')) {
            return;
        }

        require_once('class-gf-email-filtering-addon.php');
        GFAddOn::register('GFEmailFilteringAddOn');
    }
}

function gf_email_filtering_addon(): GFEmailFilteringAddOn
{
    return GFEmailFilteringAddOn::get_instance();
}
