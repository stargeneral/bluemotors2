<?php
/**
 * Plugin Activation Handler
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

class Blue_Motors_Southampton_Activator {
    
    /**
     * Activation routine
     */
    public static function activate() {
        // Load safe database manager first
        if (!class_exists('BMS_Database_Manager_Safe')) {
            require_once __DIR__ . '/class-database-manager-safe.php';
        }
        
        // Create database tables using the safe database manager
        if (class_exists('BMS_Database_Manager_Safe')) {
            BMS_Database_Manager_Safe::create_tables();
        } else {
            // Fallback to basic table creation
            self::create_tables_fallback();
        }
        
        // Set default options
        self::set_default_options();
        
        // Create pages
        self::create_pages();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables using new manager
     * @deprecated Use BMS_Database_Manager::create_tables() instead
     */
    private static function create_tables() {
        // This method is kept for backward compatibility
        self::create_tables_fallback();
    }
    
    /**
     * Fallback table creation method
     */
    private static function create_tables_fallback() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        // Updated to use booking_date/booking_time to match code expectations
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_reference VARCHAR(20) UNIQUE,
            service_type VARCHAR(50) NOT NULL,
            booking_date DATE NOT NULL,
            booking_time TIME NOT NULL,
            vehicle_reg VARCHAR(10),
            vehicle_make VARCHAR(100),
            vehicle_model VARCHAR(100),
            vehicle_year INT,
            vehicle_engine_size INT,
            vehicle_fuel_type VARCHAR(20),
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(20),
            customer_address TEXT,
            calculated_price DECIMAL(10,2),
            payment_status VARCHAR(20) DEFAULT 'pending',
            payment_reference VARCHAR(100),
            booking_status VARCHAR(20) DEFAULT 'confirmed',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_date (booking_date),
            INDEX idx_time (booking_time),
            INDEX idx_reference (booking_reference),
            INDEX idx_status (booking_status);
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        // Set version
        add_option('bms_version', BMS_VERSION);
        
        // Set default business hours
        $default_hours = array(
            'monday' => array('open' => '08:00', 'close' => '18:00'),
            'tuesday' => array('open' => '08:00', 'close' => '18:00'),
            'wednesday' => array('open' => '08:00', 'close' => '18:00'),
            'thursday' => array('open' => '08:00', 'close' => '18:00'),
            'friday' => array('open' => '08:00', 'close' => '18:00'),
            'saturday' => array('open' => '08:00', 'close' => '16:00'),
            'sunday' => array('open' => 'closed', 'close' => 'closed')
        );
        
        add_option('bms_business_hours', $default_hours);
        add_option('bms_booking_min_days', 1);
        add_option('bms_booking_max_days', 30);
        add_option('bms_slot_duration', 30);
    }
    
    /**
     * Create default pages
     */
    private static function create_pages() {
        // Create booking page
        $booking_page = array(
            'post_title' => 'Book a Service',
            'post_content' => '[bms_booking_form]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_slug' => 'book-service');
        
        $booking_page_id = wp_insert_post($booking_page);
        add_option('bms_booking_page_id', $booking_page_id);
        
        // Create location page
        $location_page = array(
            'post_title' => 'Our Location',
            'post_content' => '[bms_location_info]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_slug' => 'location');
        
        $location_page_id = wp_insert_post($location_page);
        add_option('bms_location_page_id', $location_page_id);
    }
}