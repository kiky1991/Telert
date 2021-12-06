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

// load api
include_once TELERT_PLUGIN_PATH . 'classes/api/telegram.php';

// load files.
require_once TELERT_PLUGIN_PATH . 'autoload.php';

if (!class_exists('Telert')) {

    final class Telert
    {
        
    }

    new Telert();
}