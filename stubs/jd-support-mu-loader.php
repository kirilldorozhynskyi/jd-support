<?php
/**
 * Plugin Name: justDev Support Loader
 * Description: Loads the justDev Support mu-plugin from the Composer vendor directory.
 * Author: justDev
 */

if (!defined('WPMU_PLUGIN_DIR')) {
    return;
}

$autoloaderPath = WPMU_PLUGIN_DIR . '/../vendor/autoload.php';
if (file_exists($autoloaderPath)) {
    require_once $autoloaderPath;
}

$pluginPath = WPMU_PLUGIN_DIR . '/../vendor/justdev/jd-support/jd-support.php';
if (!file_exists($pluginPath)) {
    error_log('justDev Support Loader: Plugin entry point not found at ' . $pluginPath);
    return;
}

require_once $pluginPath;
