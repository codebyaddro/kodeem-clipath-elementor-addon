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
     * Additional helper: Convert ratio to CSS safe format
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
}