<?php
/** 
 * Plugin Name:     ICS Display
 * Plugin URI:      https://wordpress.org/plugins/ics-display/
 * Description:     Display upcoming events from a shared Google, Outlook, iCal or other ICS calendar. 
 * Version:         2.1
 * Author:          appleuser
 * Author URI:      https://profiles.wordpress.org/appleuser
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     ics-display
 * Domain Path:     /languages
 */

defined( 'ABSPATH' ) || exit;

if ( !defined( 'ICSD_TEXT_DOMAIN' ) ) {
    define( 'ICSD_TEXT_DOMAIN', 'ics-display' );
}
if ( !defined( 'ICSD_PLUGIN_URL' ) ) {
    define( 'ICSD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

require plugin_dir_path( __FILE__ ) . 'library/class-ics-display.php';

function run_ics_display() {
    $plugin = new ICS_Display();
    $plugin->run();
}

function set_ics_premium( $key ) {
    $plugin = new ICS_Display();
    return ($plugin->set_premium($key));
}

add_action( 'plugins_loaded', 'run_ics_display' );

if (is_admin()) {
    if (!(function_exists('get_ics_ajax'))) {
        add_action( 'wp_ajax_get_ics_ajax', 'get_ics_ajax' );
        function get_ics_ajax() {
            $store = maybe_unserialize(get_option('icsd_setup'));
            $returnics = array();
            foreach ($store AS $sk => $sv) {
                $returnics[] = array(
                    'key'   => $sk.md5($sk.$sv['name']),
                    'label' => $sv['name'],
                    'value' => $sk
                );
            }
            echo json_encode($returnics);
            wp_die(); // this is required to terminate immediately and return a proper response
        }
    }
}

?>
