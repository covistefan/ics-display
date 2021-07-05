<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0
 * @package    COVI
 * @subpackage ICS_Display
 */

defined( 'ABSPATH' ) || exit;

class ICS_Display {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0
     * @access   protected
     * @var      $loader » Maintains and registers all hooks for the plugin.
     */
    protected  $loader ;
    protected  $plugin_name ;
    
    /* define the core functionality of the plugin.
     *
     * det the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     */
    public function __construct( $loader = true ) {
        if ($loader===true) {
            $this->plugin_name = ICSD_TEXT_DOMAIN;
            $this->get_premium();
            $this->load_dependencies();
            $this->define_public_hooks();
            $this->define_admin_hooks();
            $this->set_locale();
        }
    }
    
    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Woo_Checkout_Code_Api_Loader. Orchestrates the hooks of the plugin.
     * - Woo_Checkout_Code_Api_i18n. Defines internationalization functionality.
     * - Woo_Checkout_Code_Api_Admin. Defines all hooks for the admin area.
     * - Woo_Checkout_Code_Api_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0
     * @access   private
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters of the core plugin.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'library/class-covi-loader.php';
        /* The class responsible for defining internationalization functionality of the plugin. */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'library/class-covi-i18n.php';
        /* The class responsible for defining all actions in public and admin */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'library/class-ics-display-public.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'library/class-ics-display-admin.php';
        $this->loader = new COVI_Loader();
    }
    
    private function get_premium() {
        // get options from wp db
        $opts = maybe_unserialize(get_option('icsd_pro'));
        if (isset($opts['runtime']) && isset($opts['hash']) && intval($opts['runtime'])>0 && trim($opts['hash'])!='') {
            // if there is only one week left of premium - recheck
            if (intval($opts['runtime'])<(time()-432000)) {
                define( 'ICSD_PRO_INFO', 'Pro version will expire soon.');
            } else {
                define( 'ICSD_PRO_INFO', '');
                define( 'ICSD_PRO', true);
                define( 'ICSD_PRO_TIME', $opts['runtime']);
            }
        }
        else if ( !defined( 'ICSD_PRO_INFO' ) ) {
            define( 'ICSD_PRO_INFO', __(' (PRO version)', 'ics-display') );
        }
    }
    
    static function set_premium( $key ) {
        $args = array();
        $opts = maybe_unserialize(get_option('icsd_pro'));
        // returning (and accepted) content type
        $args['headers']['Content-Type'] = 'application/json';
        $args['headers']['Accept'] = 'application/json';
        $args['body'] = array(
            'apikey' => $key,
            'apihash' => (isset($opts['hash'])?trim($opts['hash']):''),
            'runtime' => (isset($opts['runtime'])?intval($opts['runtime']):0)
        );
        $args['timeout'] = 30;
        $args['method'] = 'GET';
        $response = wp_remote_get('https://api.covi.de', $args );
        $http_code = wp_remote_retrieve_response_code( $response );
        $api_body = wp_remote_retrieve_body( $response );
        if ($http_code==200) {
            $api_res = json_decode($api_body, true);
            if ($api_res['success']==true) {
                $saveopts = array(
                    'hash' => $api_res['hash'],
                    'runtime' => $api_res['runtime']
                );
                update_option('icsd_pro', $saveopts);
                return true;
            } else {
                return $api_res;
            }
        } else {
            return array('type' => 'error', 'msg' => 'no connect to api');
        }
    }
    
    /* define the locale for this plugin for internationalization. */
    private function set_locale() {
        $plugin_i18n = new COVI_i18n();
        $plugin_i18n->set_slug( $this->get_plugin_name() );
        $this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
    }
    
    /* Register all of the hooks related to the public-facing functionality of the plugin. */
    private function define_public_hooks() {
        
        $plugin_public = new ICS_Display_Public( $this->get_plugin_name() );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
        $this->loader->add_action( 'init', $plugin_public, 'icsd_create_block' );
        
    }
    
    /* Register all of the hooks related to the admin-facing functionality of the plugin. */
    private function define_admin_hooks() {
        
        $plugin_admin = new ICS_Display_Admin( $this->get_plugin_name() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'icsd_create_page' );

    }
    
    /* Retrieve the plugin name. */
    public function get_plugin_name() { return $this->plugin_name; }
    
    /* The reference to the class that orchestrates the hooks with the plugin. */
    public function get_loader() { return $this->loader; }

    /* Run the loader to execute all of the hooks with WordPress. */
    public function run() { $this->loader->run(); }
    
}

