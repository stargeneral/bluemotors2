<?php
/**
 * Plugin Deactivation Handler
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

class Blue_Motors_Southampton_Deactivator {
    
    /**
     * Deactivation routine
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Clear any scheduled events
     */
    private static function clear_scheduled_events() {
        wp_clear_scheduled_hook('bms_daily_cleanup');
        wp_clear_scheduled_hook('bms_hourly_checks');
    }
}