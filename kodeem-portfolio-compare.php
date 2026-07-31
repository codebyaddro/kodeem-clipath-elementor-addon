<?php
/**
 * Plugin Name: Kodeem Clipath Elementor Addon
 * Description: Professional Elementor Before/After Portfolio Showcase Widget
 * Version: 2.0.1
 * Author: Kodeem Labs
 * Requires Plugins: elementor
 */

if (!defined('ABSPATH')) {
    exit;
}

define('KPC_VERSION', '1.0.0');
define('KPC_FILE', __FILE__);
define('KPC_PATH', plugin_dir_path(__FILE__));
define('KPC_URL', plugin_dir_url(__FILE__));

require_once KPC_PATH . 'includes/Plugin.php';

KodeemPortfolioCompare\Plugin::instance();