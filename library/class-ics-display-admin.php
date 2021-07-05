<?php
/**
 * The admin plugin class.
 *
 * @since      1.0
 * @package    ICS_Display
 */

defined( 'ABSPATH' ) || exit;

class ICS_Display_Admin {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0
     * @access   protected
     * @var      Woo_Checkout_Code_Api_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    private $plugin_name ;

    /* init class */
    public function __construct( $plugin_name ) {
        $this->plugin_name = $plugin_name;
    }
    
    /* admin area css */
    public function enqueue_styles( $hook ) {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __DIR__ ) . 'css/icsd-admin.css',
            array(),
            NULL,
            'all'
        );
    }
    
    /* admin area javascript */
    public function enqueue_scripts( $hook ) {
        wp_enqueue_script(
            $this->plugin_name . 'wcca-admin-default-js',
            plugin_dir_url( __DIR__ ) . 'js/icsd-admin.js',
            array( 'jquery', 'jquery-ui-dialog' ),
            NULL,
            false,
            999
        );
    }
    
    /* create admin menu */
    public function icsd_create_page() {
        add_menu_page( __("ICS Display", $this->plugin_name), __("ICS Display", $this->plugin_name), 'manage_options', 'ics_display', array( $this, 'icsd_options' ), 'dashicons-calendar-alt');
    }

    public function icsd_check_data ( $url ) {
        
        $args['timeout'] = 30;
        
        $response = wp_remote_get( $url , $args );
        $http_code = wp_remote_retrieve_response_code( $response );
        
        if ($http_code==200) {
            $content = wp_remote_retrieve_body( $response );
            $icsdata = explode(PHP_EOL, $content);
            $act = 0;
            if (is_array($icsdata)) {
                foreach($icsdata AS $ik => $iv) {
                    if ($ik>0 && intval(strpos($iv, ":"))==0) {
                        if (isset($icsdata[$act])) {
                            $icsdata[$act] = trim($icsdata[$act]).trim($iv);
                        }
                        unset($icsdata[$ik]);
                    }
                    $act = $ik;
                }
                $events = array(); $e = 0; $timezone = date_default_timezone_get();
                foreach ($icsdata AS $ik => $iv) {
                    if (strstr(trim($iv), 'X-WR-TIMEZONE:')) { list(,$timezone) = explode(":",trim($iv)); }
                    if (strstr(trim($iv), 'BEGIN:VEVENT')) { $events[$e] = array(); $events[$e]['timezone'] = $timezone; }
                    // find final col and start over to new event element
                    if (strstr(trim($iv), 'END:VEVENT')) { $e++; }
                }
            }
            return (count($events));
        }
        else {
            return false;
        }
    }
    
    public function icsd_options() {
        require_once plugin_dir_path( __DIR__ ) . 'admin/plugin-action.php';
        require_once plugin_dir_path( __DIR__ ) . 'admin/plugin-header.php';
        require_once plugin_dir_path( __DIR__ ) . 'admin/plugin-settings.php';
        require_once plugin_dir_path( __DIR__ ) . 'admin/plugin-help.php';
        require_once plugin_dir_path( __DIR__ ) . 'admin/plugin-premium.php';
        require_once plugin_dir_path( __DIR__ ) . 'admin/plugin-footer.php';
    }
    
}

