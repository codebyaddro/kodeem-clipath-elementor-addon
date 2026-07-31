<?php
namespace KodeemPortfolioCompare;

if (!defined('ABSPATH')) {
    exit;
}

class Assets {

    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'register']);
        add_action('elementor/editor/after_enqueue_scripts', [__CLASS__, 'editor_assets']);
    }

    public static function register() {

        wp_register_style(
            'kpc-widget',
            KPC_URL . 'assets/css/widget.css',
            [],
            KPC_VERSION
        );

        wp_register_script(
            'kpc-widget',
            KPC_URL . 'assets/js/widget.js',
            ['jquery'], // Added jQuery dependency
            KPC_VERSION,
            true
        );
    }

    public static function enqueue_widget_assets() {
        // Ensure assets are registered first (in case register wasn't called)
        if (!wp_script_is('kpc-widget', 'registered')) {
            self::register();
        }

        wp_enqueue_style('kpc-widget');
        wp_enqueue_script('kpc-widget');

        // Pass data to JavaScript if needed
        wp_localize_script('kpc-widget', 'kpcData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kpc_nonce'),
            'strings' => [
                'loading' => __('Loading...', 'kodeem-portfolio-compare'),
                'error' => __('An error occurred', 'kodeem-portfolio-compare'),
            ]
        ]);
    }

    public static function editor_assets() {

        wp_enqueue_style(
            'kpc-editor',
            KPC_URL . 'assets/css/editor.css',
            [],
            KPC_VERSION
        );

        wp_enqueue_script(
            'kpc-editor',
            KPC_URL . 'assets/js/editor.js',
            ['jquery', 'elementor-common'], // Added Elementor dependencies
            KPC_VERSION,
            true
        );

        // Pass editor-specific data
        wp_localize_script('kpc-editor', 'kpcEditorData', [
            'i18n' => [
                'select_image' => __('Select Image', 'kodeem-portfolio-compare'),
                'change_image' => __('Change Image', 'kodeem-portfolio-compare'),
            ]
        ]);
    }

    /**
     * Helper method to check if widget assets are enqueued
     */
    public static function is_widget_enqueued() {
        return  wp_style_is('kpc-widget', 'enqueued') && 
                wp_script_is('kpc-widget', 'enqueued');
    }

    /**
     * Helper method to get asset version with cache busting for development
     */
    private static function get_asset_version($file_path = '') {
        if (defined('WP_DEBUG') && WP_DEBUG && !empty($file_path)) {
            $full_path = KPC_PATH . $file_path;
            if (file_exists($full_path)) {
                return filemtime($full_path);
            }
        }
        return KPC_VERSION;
    }
}