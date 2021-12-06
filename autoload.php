<?php

/**
 * Autoload inside each apps
 */
spl_autoload_register(function ($class) {
    $path = str_replace('_', '-', strtolower($class));
    $file = TELERT_PLUGIN_PATH . "classes/class-{$path}.php";

    if (file_exists($file)) {
        require_once $file;
    }
});
