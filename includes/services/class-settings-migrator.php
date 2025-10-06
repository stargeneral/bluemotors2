<?php
/**
 * Settings Migration Service
 * 
 * Migrates hardcoded constants to database options for admin configuration
 * 
 * @package BlueMotosSouthampton
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BMS_Settings_Migrator {
    
    /**
     * Constants to migrate to database options
     */
    private static $constants_map = array(
        // Business Information
        'BM_LOCATION_NAME' => 'bms_business_name',
        'BM_LOCATION_ADDRESS' => 'bms_business_address',
        'BM_LOCATION_PHONE' => 'bms_business_phone',
        'BM_LOCATION_EMAIL' => 'bms_business_email',
        'BM_LOCATION_POSTCODE' => 'bms_business_postcode',
        'BM_LOCATION_LAT' => 'bms_business_latitude',
        'BM_LOCATION_LNG' => 'bms_business_longitude',
        
        // Business Hours
        'BM_HOURS_WEEKDAY_OPEN' => 'bms_hours_weekday_open',
        'BM_HOURS_WEEKDAY_CLOSE' => 'bms_hours_weekday_close',
        'BM_HOURS_SATURDAY_OPEN' => 'bms_hours_saturday_open',
        'BM_HOURS_SATURDAY_CLOSE' => 'bms_hours_saturday_close',
        'BM_HOURS_SUNDAY_OPEN' => 'bms_hours_sunday_open',
        'BM_HOURS_SUNDAY_CLOSE' => 'bms_hours_sunday_close',
        
        // Booking Configuration
        'BM_BOOKING_MIN_DAYS' => 'bms_booking_min_days',
        'BM_BOOKING_MAX_DAYS' => 'bms_booking_max_days',
        'BM_BOOKING_SLOT_DURATION' => 'bms_booking_slot_duration',
        'BM_BOOKING_REFERENCE_PREFIX' => 'bms_booking_reference_prefix',
        
        // Payment Configuration
        'BM_PAYMENT_REQUIRED' => 'bms_payment_required',
        'BM_PAYMENT_CURRENCY' => 'bms_payment_currency',
        'BM_VAT_RATE' => 'bms_vat_rate',
        
        // Stripe Configuration
        'BM_STRIPE_PUBLISHABLE_KEY' => 'bms_stripe_public_key',
        'BM_STRIPE_SECRET_KEY' => 'bms_stripe_secret_key',
        'BM_STRIPE_ENABLED' => 'bms_stripe_enabled',
        
        // Email Configuration
        'BM_EMAIL_FROM_NAME' => 'bms_email_from_name',
        'BM_EMAIL_FROM_ADDRESS' => 'bms_email_from_address',
        'BM_EMAIL_ADMIN_NOTIFY' => 'bms_email_admin_notify',
        
        // API Configuration
        'BM_DVLA_API_ENABLED' => 'bms_dvla_api_enabled',
        'BM_DVLA_API_KEY' => 'bms_dvla_api_key',
        'BM_DVLA_API_URL' => 'bms_dvla_api_url',
        
        // Google Maps
        'BM_GOOGLE_MAPS_API_KEY' => 'bms_google_maps_api_key',
        'BM_GOOGLE_MAPS_ENABLED' => 'bms_google_maps_enabled',
        
        // Debug Settings
        'BM_DEBUG_MODE' => 'bms_debug_mode',
        'BM_LOG_BOOKINGS' => 'bms_log_bookings'
    );
    
    /**
     * Migrate constants to database options
     * 
     * @return bool Success status
     */
    public static function migrate_constants_to_options() {
        $migrated_count = 0;
        
        foreach (self::$constants_map as $constant => $option) {
            if (defined($constant)) {
                $value = constant($constant);
                
                // Only migrate if option doesn't exist yet
                if (get_option($option) === false) {
                    update_option($option, $value);
                    $migrated_count++;
                }
            }
        }
        
        // Set migration flag
        if ($migrated_count > 0) {
            update_option('bms_settings_migrated', true);
            update_option('bms_settings_migration_date', current_time('mysql'));
            update_option('bms_settings_migration_count', $migrated_count);
        }
        
        return $migrated_count > 0;
    }
    
    /**
     * Check if migration has been completed
     * 
     * @return bool Migration status
     */
    public static function is_migrated() {
        return get_option('bms_settings_migrated', false);
    }
    
    /**
     * Get setting value with fallback to constant
     * 
     * @param string $option_name Database option name
     * @param string $constant_name Constant name as fallback
     * @param mixed $default Default value if neither exists
     * @return mixed Setting value
     */
    public static function get_setting($option_name, $constant_name = '', $default = '') {
        // First try database option;
        $value = get_option($option_name);
        
        if ($value !== false) {
            return $value;
        }
        
        // Fallback to constant if exists
        if ($constant_name && defined($constant_name)) {
            return constant($constant_name);
        }
        
        // Return default
        return $default;
    }
    
    /**
     * Get business information
     * 
     * @return array Business details
     */
    public static function get_business_info() {
        return array(
            'name' => self::get_setting('bms_business_name', 'BM_LOCATION_NAME', 'Blue Motors Southampton'),
            'address' => self::get_setting('bms_business_address', 'BM_LOCATION_ADDRESS', '1 Kent St, Northam, Southampton SO14 5SP'),
            'phone' => self::get_setting('bms_business_phone', 'BM_LOCATION_PHONE', '023 8000 0000'),
            'email' => self::get_setting('bms_business_email', 'BM_LOCATION_EMAIL', 'southampton@bluemotors.co.uk'),
            'postcode' => self::get_setting('bms_business_postcode', 'BM_LOCATION_POSTCODE', 'SO14 5SP'),
            'latitude' => self::get_setting('bms_business_latitude', 'BM_LOCATION_LAT', 50.9097),
            'longitude' => self::get_setting('bms_business_longitude', 'BM_LOCATION_LNG', -1.3885)
        );
    }
    
    /**
     * Get business hours
     * 
     * @return array Business hours
     */
    public static function get_business_hours() {
        return array(
            'weekday_open' => self::get_setting('bms_hours_weekday_open', 'BM_HOURS_WEEKDAY_OPEN', '08:00'),
            'weekday_close' => self::get_setting('bms_hours_weekday_close', 'BM_HOURS_WEEKDAY_CLOSE', '18:00'),
            'saturday_open' => self::get_setting('bms_hours_saturday_open', 'BM_HOURS_SATURDAY_OPEN', '08:00'),
            'saturday_close' => self::get_setting('bms_hours_saturday_close', 'BM_HOURS_SATURDAY_CLOSE', '16:00'),
            'sunday_open' => self::get_setting('bms_hours_sunday_open', 'BM_HOURS_SUNDAY_OPEN', 'closed'),
            'sunday_close' => self::get_setting('bms_hours_sunday_close', 'BM_HOURS_SUNDAY_CLOSE', 'closed')
        );
    }
    
    /**
     * Get payment settings
     * 
     * @return array Payment configuration
     */
    public static function get_payment_settings() {
        return array(
            'enabled' => self::get_setting('bms_payment_required', 'BM_PAYMENT_REQUIRED', true),
            'currency' => self::get_setting('bms_payment_currency', 'BM_PAYMENT_CURRENCY', 'GBP'),
            'vat_rate' => self::get_setting('bms_vat_rate', 'BM_VAT_RATE', 0.20),
            'stripe_enabled' => self::get_setting('bms_stripe_enabled', 'BM_STRIPE_ENABLED', true),
            'stripe_public_key' => self::get_setting('bms_stripe_public_key', 'BM_STRIPE_PUBLISHABLE_KEY', ''),
            'stripe_secret_key' => self::get_setting('bms_stripe_secret_key', 'BM_STRIPE_SECRET_KEY', ''),
            'test_mode' => self::get_setting('bms_payment_test_mode', '', true)
        );
    }
    
    /**
     * Get booking configuration
     * 
     * @return array Booking settings
     */
    public static function get_booking_settings() {
        return array(
            'min_days' => self::get_setting('bms_booking_min_days', 'BM_BOOKING_MIN_DAYS', 1),
            'max_days' => self::get_setting('bms_booking_max_days', 'BM_BOOKING_MAX_DAYS', 30),
            'slot_duration' => self::get_setting('bms_booking_slot_duration', 'BM_BOOKING_SLOT_DURATION', 30),
            'reference_prefix' => self::get_setting('bms_booking_reference_prefix', 'BM_BOOKING_REFERENCE_PREFIX', 'WEB')
        );
    }
    
    /**
     * Force re-migration (for testing or updates)
     * 
     * @return bool Success status
     */
    public static function force_remigration() {
        delete_option('bms_settings_migrated');
        return self::migrate_constants_to_options();
    }
    
    /**
     * Get migration information
     * 
     * @return array Migration details
     */
    public static function get_migration_info() {
        return array(
            'migrated' => self::is_migrated(),
            'migration_date' => get_option('bms_settings_migration_date', ''),
            'migration_count' => get_option('bms_settings_migration_count', 0),
            'total_constants' => count(self::$constants_map)
        );
    }
}
