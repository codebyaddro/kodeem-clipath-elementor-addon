<?php
namespace KodeemPortfolioCompare;

use Elementor\Widgets_Manager; // Import the Widgets_Manager class

if (!defined('ABSPATH')) {
    exit;
}

class Elementor {

    /**
     * Check if Elementor is active
     */
    public static function is_elementor_active() {
        return did_action('elementor/loaded');
    }

    public static function init() {
        // Only initialize if Elementor is active
        if (!self::is_elementor_active()) {
            return;
        }

        add_action(
            'elementor/widgets/register',
            [__CLASS__, 'register_widgets']
        );

        // Register widget category
        add_action(
            'elementor/elements/categories_registered',
            [__CLASS__, 'register_widget_category']
        );
    }

    /**
     * Register custom widget category
     */
    public static function register_widget_category($elements_manager) {
        $elements_manager->add_category(
            'kodeem-portfolio-compare',
            [
                'title' => __('Portfolio Compare', 'kodeem-portfolio-compare'),
                'icon' => 'fa fa-images',
            ]
        );
    }

    /**
     * Register widgets with Elementor
     */
    public static function register_widgets(Widgets_Manager $widgets_manager) {
        
        $widget_file = KPC_PATH . 'includes/Widgets/PortfolioCompareWidget.php';
        
        // Check if widget file exists
        if (!file_exists($widget_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(
                    sprintf(
                        'Kodeem Portfolio Compare: Widget file not found at %s',
                        $widget_file
                    )
                );
            }
            return;
        }

        try {
            require_once $widget_file;

            // Check if the class exists
            $widget_class = '\\KodeemPortfolioCompare\\Widgets\\PortfolioCompareWidget';
            
            if (!class_exists($widget_class)) {
                throw new \Exception(
                    sprintf(
                        'Widget class %s not found',
                        $widget_class
                    )
                );
            }

            // Register the widget
            $widgets_manager->register(
                new $widget_class()
            );

        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(
                    sprintf(
                        'Kodeem Portfolio Compare Widget Registration Error: %s',
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * Alternative registration method with direct class loading
     */
    public static function register_widgets_alternative(Widgets_Manager $widgets_manager) {
        $widgets = [
            'PortfolioCompareWidget',
        ];

        foreach ($widgets as $widget) {
            $file_path = KPC_PATH . 'includes/Widgets/' . $widget . '.php';
            
            if (file_exists($file_path)) {
                require_once $file_path;
                
                $class_name = '\\KodeemPortfolioCompare\\Widgets\\' . $widget;
                
                if (class_exists($class_name)) {
                    $widgets_manager->register(new $class_name());
                }
            }
        }
    }
}