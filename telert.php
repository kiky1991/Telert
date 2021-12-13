<?php

/**
 * Plugin Name:     Telert
 * Plugin URI:      https://www.cybersoftmedia.com
 * Description:     Alert anything message for telegram group / bot / channel
 * Version:         1.0.0
 * Author:          Hengky ST
 * Author URI:      https://www.cybersoftmedia.com
 * License:         GPL
 * Text Domain:     telert
 */

if (!defined('ABSPATH')) {
    exit;
}

// constants.
define('TELERT_VERSION', '1.0.0');
define('TELERT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TELERT_PLUGIN_URI', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));

// load config
foreach (glob( TELERT_PLUGIN_PATH . "config/*.php") as $filename) require_once $filename;

// load api
include_once TELERT_PLUGIN_PATH . 'api/telegram.php';

// load files.
if (defined('TELERT_PLUGIN_PATH')) require_once TELERT_PLUGIN_PATH . 'vendor/autoload.php';
require_once TELERT_PLUGIN_PATH . 'autoload.php';

if (!class_exists('Telert')) {

    final class Telert
    {
        public function __construct()
        {
            // check new version
            // add_action('init', array($this, 'check_update'));

            new Telert_Admin();
            new Telert_Cron();
            new Telert_Ajax();

            register_activation_hook(__FILE__, array($this, 'on_plugin_activation'));
            add_action( 'plugins_loaded', array($this, 'on_plugin_loaded') );
        }

        /**
         * Actions when plugin activated
         */
        public function on_plugin_activation()
        {
            $version = get_option('telert_version', '');

            // install
            if (version_compare(TELERT_VERSION, $version, '>=')) {
                new Telert_Install();
                update_option('telert_version', TELERT_VERSION);
            }

            return __return_empty_string();
        }

        /**
         * Actions when after plugin loaded
         */
        public function on_plugin_loaded()
        {
            // new Telert_Install();
        }

        public function check_update()
        {
            require TELERT_PLUGIN_PATH . 'libs/plugin-update-checker/plugin-update-checker.php';
            Puc_v4_Factory::buildUpdateChecker(
                'https://github.com/kiky1991/Telert.git',
                __FILE__,
                'telert'
            );
        }
    }

    new Telert();
}