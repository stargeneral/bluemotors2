<?php
/**
 * SAFE Database Table Manager for Blue Motors Southampton
 * 
 * Handles database table creation with better error handling
 * 
 * @package BlueMotosSouthampton
 * @since 1.3.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BMS_Database_Manager_Safe {
    
    /**
     * Create or update database tables safely
     */
    public static function create_tables() {
        global $wpdb;
        
        // Suppress WordPress warnings during table creation
        $wpdb->suppress_errors();
        
        try {
            // Create tables one by one with error checking
            self::create_appointments_table();
            self::create_booking_logs_table();
            self::create_services_table();
            self::create_tyre_tables();
            self::create_tyre_bookings_table();
            
            // Set database version
            update_option('bms_database_version', '1.3.2');
            update_option('bms_database_created', current_time('mysql'));
            
            error_log('BMS: Database tables created successfully');
            
        } catch (Exception $e) {
            error_log('BMS Database Error: ' . $e->getMessage());
        }
        
        $wpdb->suppress_errors(false);
    }
    
    /**
     * Create appointments table
     */
    private static function create_appointments_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            booking_reference varchar(20) DEFAULT NULL,
            service_type varchar(50) NOT NULL,
            booking_date date NOT NULL,
            booking_time time NOT NULL,
            vehicle_reg varchar(10) DEFAULT NULL,
            vehicle_make varchar(100) DEFAULT NULL,
            vehicle_model varchar(100) DEFAULT NULL,
            vehicle_year int(4) DEFAULT NULL,
            vehicle_engine_size int(11) DEFAULT NULL,
            vehicle_fuel_type varchar(20) DEFAULT NULL,
            customer_name varchar(255) NOT NULL,
            customer_email varchar(255) NOT NULL,
            customer_phone varchar(20) DEFAULT NULL,
            customer_address text,
            customer_postcode varchar(10) DEFAULT NULL,
            calculated_price decimal(10,2) DEFAULT NULL,
            payment_status varchar(20) DEFAULT 'pending',
            payment_reference varchar(100) DEFAULT NULL,
            booking_status varchar(20) DEFAULT 'confirmed',
            notes text,
            special_requirements text,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_booking_date (booking_date),
            INDEX idx_booking_time (booking_time),
            INDEX idx_booking_status (booking_status),
            INDEX idx_customer_email (customer_email),
            INDEX idx_payment_status (payment_status)
        ) $charset_collate";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            error_log('BMS: Failed to create appointments table: ' . $wpdb->last_error);
        } else {
            error_log('BMS: Appointments table created successfully');
        }
        
        // Create unique constraint on booking reference
        $wpdb->query("ALTER TABLE $table_name ADD UNIQUE KEY booking_reference_unique (booking_reference)");
    }
    
    /**
     * Create booking logs table
     */
    private static function create_booking_logs_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_booking_logs';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            booking_id int(11) DEFAULT NULL,
            action varchar(50) DEFAULT NULL,
            details text,
            user_id int(11) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_booking_id (booking_id),
            INDEX idx_created_at (created_at)
        ) $charset_collate";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            error_log('BMS: Failed to create booking logs table: ' . $wpdb->last_error);
        }
    }
    
    /**
     * Create services table
     */
    private static function create_services_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_services';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            service_key varchar(50) NOT NULL,
            name varchar(255) NOT NULL,
            description text,
            base_price decimal(10,2) NOT NULL,
            pricing_type varchar(20) DEFAULT 'fixed',
            duration int(11) DEFAULT 60,
            category varchar(50) DEFAULT NULL,
            enabled tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            features text,
            requirements text,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY service_key_unique (service_key),
            INDEX idx_enabled (enabled),
            INDEX idx_category (category)
        ) $charset_collate";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            error_log('BMS: Failed to create services table: ' . $wpdb->last_error);
        }
    }
    
    /**
     * Create tyres table
     */
    private static function create_tyre_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyres';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            brand varchar(50) NOT NULL,
            model varchar(100) NOT NULL,
            size varchar(20) NOT NULL,
            width int(3) NOT NULL,
            profile int(3) NOT NULL,
            rim int(2) NOT NULL,
            speed_rating varchar(5) DEFAULT NULL,
            load_index int(3) DEFAULT NULL,
            price decimal(8,2) NOT NULL,
            fitting_price decimal(6,2) DEFAULT 25.00,
            stock_quantity int(11) DEFAULT 10,
            is_active tinyint(1) DEFAULT 1,
            brand_tier enum('premium','mid-range','budget') DEFAULT 'mid-range',
            fuel_efficiency varchar(2) DEFAULT NULL,
            wet_grip varchar(2) DEFAULT NULL,
            noise_rating int(1) DEFAULT NULL,
            season enum('summer','winter','all-season') DEFAULT 'summer',
            usage_type enum('car','van','4x4') DEFAULT 'car',
            supplier_code varchar(50) DEFAULT NULL,
            last_restocked date DEFAULT NULL,
            reorder_level int(11) DEFAULT 5,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_size (size),
            INDEX idx_brand (brand),
            INDEX idx_dimensions (width, profile, rim),
            INDEX idx_active_stock (is_active, stock_quantity),
            INDEX idx_brand_tier (brand_tier),
            INDEX idx_price (price)
        ) $charset_collate";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            error_log('BMS: Failed to create tyres table: ' . $wpdb->last_error);
        }
    }
    
    /**
     * Create tyre bookings table
     */
    private static function create_tyre_bookings_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyre_bookings';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            booking_reference varchar(20) NOT NULL,
            customer_name varchar(100) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_phone varchar(20) NOT NULL,
            vehicle_reg varchar(10) NOT NULL,
            vehicle_make varchar(50) DEFAULT NULL,
            vehicle_model varchar(50) DEFAULT NULL,
            tyre_id int(11) NOT NULL,
            quantity int(1) NOT NULL DEFAULT 1,
            fitting_date date NOT NULL,
            fitting_time time NOT NULL,
            tyre_price decimal(8,2) NOT NULL,
            fitting_price decimal(6,2) NOT NULL,
            total_price decimal(8,2) NOT NULL,
            payment_status enum('pending','paid','failed','refunded') DEFAULT 'pending',
            booking_status enum('confirmed','completed','cancelled') DEFAULT 'confirmed',
            notes text,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY booking_reference_unique (booking_reference),
            INDEX idx_fitting_date (fitting_date),
            INDEX idx_customer_email (customer_email),
            INDEX idx_tyre_id (tyre_id)
        ) $charset_collate";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            error_log('BMS: Failed to create tyre bookings table: ' . $wpdb->last_error);
        }
    }
    
    /**
     * Check if tables exist and are properly configured
     */
    public static function verify_tables() {
        global $wpdb;
        
        $tables = array(
            'bms_appointments',
            'bms_booking_logs', 
            'bms_services',
            'bms_tyres',
            'bms_tyre_bookings'
        );
        
        $status = array();
        
        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            $status[$table] = $exists;
        }
        
        return $status;
    }
    
    /**
     * Drop all plugin tables (for development/testing only)
     */
    public static function drop_tables() {
        global $wpdb;
        
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return false; // Only allow in debug mode
        }
        
        $tables = array(
            'bms_appointments',
            'bms_booking_logs',
            'bms_services', 
            'bms_tyres',
            'bms_tyre_bookings'
        );
        
        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
        }
        
        delete_option('bms_database_version');
        delete_option('bms_database_created');
    }
}
