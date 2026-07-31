<?php
/**
 * Plugin Name: Kodeem Portfolio Compare
 * Plugin URI: https://kodeem.com/
 * Description: Portfolio comparison widget for Elementor
 * Version: 1.0.0
 * Author: Kodeem
 * Author URI: https://kodeem.com/
 * Text Domain: kodeem-portfolio-compare
 * Domain Path: /languages
 * Elementor tested up to: 3.18.0
 * Elementor Pro tested up to: 3.18.0
 */

namespace KodeemPortfolioCompare;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Prevent multiple instances
if (defined('KPC_PLUGIN_LOADED')) {
    return;
}
define('KPC_PLUGIN_LOADED', true);

final class Plugin {

    private static $instance = null;
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.0';
    
    /**
     * Minimum required PHP version
     */
    const MIN_PHP_VERSION = '7.4';
    
    /**
     * Minimum required Elementor version
     */
    const MIN_ELEMENTOR_VERSION = '3.5.0';

    /**
     * Get plugin instance (Singleton)
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor (Singleton pattern)
     */
    private function __construct() {
        // Check requirements before initializing
        if (!$this->check_requirements()) {
            return;
        }

        $this->define_constants();
        $this->load_dependencies();
        $this->setup_hooks();
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        if (!defined('KPC_PATH')) {
            define('KPC_PATH', plugin_dir_path(KPC_FILE));
        }
        
        if (!defined('KPC_URL')) {
            define('KPC_URL', plugin_dir_url(KPC_FILE));
        }
        
        if (!defined('KPC_VERSION')) {
            define('KPC_VERSION', self::VERSION);
        }
        
        if (!defined('KPC_BASENAME')) {
            define('KPC_BASENAME', plugin_basename(KPC_FILE));
        }
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        $required_files = [
            'includes/Assets.php',
            'includes/Elementor.php',
            'includes/Helpers.php',
        ];

        foreach ($required_files as $file) {
            $file_path = KPC_PATH . $file;
            
            if (!file_exists($file_path)) {
                $this->trigger_error(
                    sprintf(
                        'Required file not found: %s',
                        $file_path
                    )
                );
                continue;
            }

            require_once $file_path;
        }
    }

    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Initialize on plugins loaded
        add_action('plugins_loaded', [$this, 'init_plugin'], 10);
        
        // Activation hooks
        register_activation_hook(KPC_FILE, [__CLASS__, 'activate']);
        register_deactivation_hook(KPC_FILE, [__CLASS__, 'deactivate']);
        
        // Plugin action links
        add_filter('plugin_action_links_' . KPC_BASENAME, [$this, 'add_action_links']);
        
        // Admin notices
        add_action('admin_notices', [$this, 'admin_notices']);
    }

    /**
     * Initialize plugin
     */
    public function init_plugin() {
        // Load textdomain
        $this->load_textdomain();

        // Check Elementor availability
        if (!$this->check_elementor_availability()) {
            return;
        }

        // Initialize plugin components
        Assets::init();
        Elementor::init();
        
        // Hook into Elementor initialization
        do_action('kpc_plugin_initialized');
    }

    /**
     * Check if all requirements are met
     */
    private function check_requirements() {
        // Check PHP version
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            $this->trigger_error(
                sprintf(
                    'Kodeem Portfolio Compare requires PHP version %s or higher. Your current version: %s',
                    self::MIN_PHP_VERSION,
                    PHP_VERSION
                )
            );
            return false;
        }

        return true;
    }

    /**
     * Check Elementor availability
     */
    private function check_elementor_availability() {
        // Check if Elementor is installed
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Kodeem Portfolio Compare', 'kodeem-portfolio-compare'); ?></strong>
                        <?php esc_html_e('requires Elementor to be installed and activated.', 'kodeem-portfolio-compare'); ?>
                    </p>
                </div>
                <?php
            });
            return false;
        }

        // Check Elementor version
        if (!defined('ELEMENTOR_VERSION') || 
            version_compare(ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '<')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <strong><?php esc_html_e('Kodeem Portfolio Compare', 'kodeem-portfolio-compare'); ?></strong>
                        <?php 
                        printf(
                            esc_html__('requires Elementor version %s or higher.', 'kodeem-portfolio-compare'),
                            self::MIN_ELEMENTOR_VERSION
                        ); 
                        ?>
                    </p>
                </div>
                <?php
            });
            return false;
        }

        return true;
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        $textdomain = 'kodeem-portfolio-compare';
        $locale = apply_filters('plugin_locale', get_locale(), $textdomain);
        
        $mofile = sprintf(
            '%s-%s.mo',
            $textdomain,
            $locale
        );
        
        $paths = [
            KPC_PATH . 'languages/' . $mofile,
            WP_LANG_DIR . '/plugins/' . $mofile,
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path) && load_textdomain($textdomain, $path)) {
                break;
            }
        }
        
        // Fallback to load_plugin_textdomain
        load_plugin_textdomain(
            $textdomain,
            false,
            dirname(KPC_BASENAME) . '/languages'
        );
    }

    /**
     * Plugin activation
     */
    public static function activate() {
        // Check requirements
        if (!class_exists('\\Elementor\\Plugin')) {
            deactivate_plugins(KPC_BASENAME);
            wp_die(
                esc_html__('Kodeem Portfolio Compare requires Elementor to be installed and activated.', 'kodeem-portfolio-compare')
            );
        }

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag
        update_option('kpc_activated', true);
    }

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clean up
        delete_option('kpc_activated');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Add plugin action links
     */
    public function add_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=kpc-settings'),
            esc_html__('Settings', 'kodeem-portfolio-compare')
        );
        
        array_unshift($links, $settings_link);
        
        $docs_link = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            'https://docs.kodeem.com/portfolio-compare',
            esc_html__('Docs', 'kodeem-portfolio-compare')
        );
        
        $links[] = $docs_link;
        
        return $links;
    }

    /**
     * Display admin notices
     */
    public function admin_notices() {
        // Check for Elementor
        if (!did_action('elementor/loaded')) {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php esc_html_e('Kodeem Portfolio Compare', 'kodeem-portfolio-compare'); ?></strong>
                    <?php esc_html_e('requires Elementor to work properly.', 'kodeem-portfolio-compare'); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Trigger error
     */
    private function trigger_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            trigger_error(
                'Kodeem Portfolio Compare: ' . $message,
                E_USER_WARNING
            );
        }
    }

    /**
     * Get plugin info
     */
    public static function get_info() {
        return [
            'name' => 'Kodeem Portfolio Compare',
            'version' => self::VERSION,
            'author' => 'Kodeem',
            'author_uri' => 'https://kodeem.com/',
            'elementor_min_version' => self::MIN_ELEMENTOR_VERSION,
            'php_min_version' => self::MIN_PHP_VERSION,
        ];
    }

    /**
     * Check if Elementor is in debug mode
     */
    public static function is_elementor_debug() {
        return defined('ELEMENTOR_DEBUG') && ELEMENTOR_DEBUG;
    }

    /**
     * Get plugin settings
     */
    public static function get_settings($key = null) {
        $defaults = [
            'enable_lightbox' => true,
            'enable_fullscreen' => true,
            'default_ratio' => '16:9',
        ];
        
        $settings = get_option('kpc_settings', $defaults);
        $settings = wp_parse_args($settings, $defaults);
        
        if ($key !== null) {
            return isset($settings[$key]) ? $settings[$key] : null;
        }
        
        return $settings;
    }

    /**
     * Update plugin settings
     */
    public static function update_settings($new_settings) {
        $current = self::get_settings();
        $updated = wp_parse_args($new_settings, $current);
        return update_option('kpc_settings', $updated);
    }
}

// Initialize plugin
function KPC() {
    return Plugin::instance();
}

// Start the plugin
KPC();