<?php
/**
 * Settings Migrator Class
 * Handles settings migration between versions
 */

if (!defined("ABSPATH")) {
    exit;
}

class BMS_Settings_Migrator {
    
    private static $migration_version = "1.3.0";
    
    /**
     * Check if settings have been migrated
     */
    public static function is_migrated() {
        return get_option("bms_settings_migrated", false);
    }
    
    /**
     * Run settings migration
     */
    public static function migrate() {
        // Migration steps
        $migration_success = true;
        
        try {
            // Step 1: Migrate basic settings
            self::migrate_basic_settings();
            
            // Step 2: Migrate service settings
            self::migrate_service_settings();
            
            // Step 3: Migrate booking settings
            self::migrate_booking_settings();
            
            // Mark as migrated
            update_option("bms_settings_migrated", true);
            update_option("bms_migration_version", self::$migration_version);
            update_option("bms_migration_date", current_time("mysql"));
            
            return array(
                "success" => true,
                "message" => "Settings migration completed successfully"
            );
            
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Migration failed: " . $e->getMessage()
            );
        }
    }
    
    /**
     * Migrate basic settings
     */
    private static function migrate_basic_settings() {
        $default_settings = array(
            "business_name" => "Blue Motors Garage",
            "business_phone" => "",
            "business_email" => get_option("admin_email"),
            "business_hours" => array(
                "monday" => "09:00-17:00",
                "tuesday" => "09:00-17:00", 
                "wednesday" => "09:00-17:00",
                "thursday" => "09:00-17:00",
                "friday" => "09:00-17:00",
                "saturday" => "09:00-13:00",
                "sunday" => "Closed"
            ),
            "booking_enabled" => true,
            "email_notifications" => true
        );
        
        foreach ($default_settings as $key => $value) {
            $option_name = "bms_" . $key;
            if (!get_option($option_name)) {
                update_option($option_name, $value);
            }
        }
    }
    
    /**
     * Migrate service settings
     */
    private static function migrate_service_settings() {
        $default_services = array(
            "mot_testing" => array("name" => "MOT Testing", "price" => 54.85, "duration" => 60),
            "car_service" => array("name" => "Car Service", "price" => 150.00, "duration" => 120),
            "brake_service" => array("name" => "Brake Service", "price" => 80.00, "duration" => 90),
            "tyre_fitting" => array("name" => "Tyre Fitting", "price" => 25.00, "duration" => 30)
        );
        
        update_option("bms_services", $default_services);
    }
    
    /**
     * Migrate booking settings
     */
    private static function migrate_booking_settings() {
        $booking_settings = array(
            "advance_booking_days" => 30,
            "booking_confirmation" => true,
            "booking_reminders" => true,
            "cancellation_hours" => 24
        );
        
        update_option("bms_booking_settings", $booking_settings);
    }
    
    /**
     * Get migration status
     */
    public static function get_migration_status() {
        return array(
            "migrated" => self::is_migrated(),
            "version" => get_option("bms_migration_version", "none"),
            "date" => get_option("bms_migration_date", "none")
        );
    }
    
    /**
     * Force re-migration (for testing)
     */
    public static function reset_migration() {
        delete_option("bms_settings_migrated");
        delete_option("bms_migration_version");
        delete_option("bms_migration_date");
    }
}

// Include the class in main plugin file
if (!class_exists("BMS_Settings_Migrator")) {
    // Auto-migrate on load if not already migrated
    if (!BMS_Settings_Migrator::is_migrated()) {
        add_action("init", array("BMS_Settings_Migrator", "migrate"));
    }
}
