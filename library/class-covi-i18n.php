<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0
 * @package    COVI
 */

defined( 'ABSPATH' ) || exit;

if (!(class_exists('COVI_i18n'))) {
    class COVI_i18n {

        /* The slug specified for this plugin */
        private $slug;

        /* load the plugin text domain for translation */
        public function load_plugin_textdomain() {
            load_plugin_textdomain( $this->slug, false, $this->slug.'/languages' );
        }

        /* set the slug equal to that of the specified slug */
        public function set_slug( $slug ) {
            $this->slug = $slug;
        }

    }
}