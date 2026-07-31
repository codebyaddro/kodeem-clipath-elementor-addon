<?php
namespace KodeemPortfolioCompare;

if (!defined('ABSPATH')) {
    exit;
}

class Helpers {

    public static function sanitize_ratio($ratio) {
        // Handle empty input
        if (empty($ratio)) {
            return '4:3';
        }

        // Sanitize the ratio
        $ratio = sanitize_text_field($ratio);
        
        // Remove any CSS units or invalid characters
        $ratio = preg_replace('/[^0-9:.\/]/', '', $ratio);

        $allowed = [
            '1:1',
            '4:5',
            '2:3',
            '3:4',
            '4:3',
            '16:9',
            '21:9'
        ];

        // Check if it's a valid custom ratio (e.g., "16/9" or "1.5")
        if (!in_array($ratio, $allowed, true) && !is_numeric($ratio) && !strpos($ratio, '/') && !strpos($ratio, ':')) {
            return '4:3';
        }

        return in_array($ratio, $allowed, true) 
            ? $ratio 
            : $ratio; // Return custom ratio as is
    }

    /**
     * Convert ratio to CSS safe format
     */
    public static function ratio_to_css($ratio) {
        $ratio = self::sanitize_ratio($ratio);
        
        // Handle colon format for CSS
        if (strpos($ratio, ':') !== false) {
            $parts = explode(':', $ratio);
            return $parts[0] . '/' . $parts[1];
        }
        
        return $ratio;
    }

    /**
     * Get aspect ratio for display
     */
    public static function get_aspect_ratio($settings) {
        $ratio = $settings['aspect_ratio'] ?? '4:3';
        
        // Check if custom height is set
        $custom_height = !empty($settings['preview_height']['size']);
        
        if ($custom_height) {
            return 'auto';
        }
        
        if ('custom' === $ratio) {
            $width = !empty($settings['custom_ratio_width']) ? $settings['custom_ratio_width'] : 4;
            $height = !empty($settings['custom_ratio_height']) ? $settings['custom_ratio_height'] : 3;
            return $width . '/' . $height;
        }
        
        return str_replace(':', '/', $ratio);
    }

    /**
     * Get image URL with fallback
     */
    public static function get_image_url($image_data, $size = 'full') {
        if (empty($image_data) || empty($image_data['url'])) {
            return '';
        }
        
        if (!empty($image_data['id'])) {
            $image = wp_get_attachment_image_src($image_data['id'], $size);
            if ($image) {
                return $image[0];
            }
        }
        
        return $image_data['url'];
    }
}