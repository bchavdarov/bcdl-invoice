<?php
/**
 * BCDL Invoice Classes Loader
 */

namespace BCDL\Invoice;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Autoload classes from /classes
spl_autoload_register(function ($class) {
    if (strpos($class, __NAMESPACE__) === 0) {
        $relative = str_replace(__NAMESPACE__ . '\\', '', $class);
        $file = __DIR__ . '/classes/' . $relative . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});