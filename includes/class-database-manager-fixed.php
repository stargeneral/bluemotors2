<?php
/**
 * Database Table Manager for Blue Motors Southampton - FIXED VERSION
 * 
 * Handles database table creation and updates
 * 
 * @package BlueMotosSouthampton
 * @since 1.3.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BMS_Database_Manager {
    
    /**
     * Create or update database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Main appointments table - using booking_date/booking_time to match code
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        $sql = "CREATE TABLE $table_name (
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
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY booking_reference (booking_reference),
            KEY idx_date (booking_date),
            KEY idx_time (booking_time),
            KEY idx_status (booking_status),
            KEY idx_customer (customer_email);
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Booking logs table
        $logs_table = $wpdb->prefix . 'bms_booking_logs';
        
        $logs_sql = "CREATE TABLE $logs_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            booking_id int(11) DEFAULT NULL,
            action varchar(50) DEFAULT NULL,
            details text,
            user_id int(11) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_booking (booking_id);
        ) $charset_collate;";
        
        dbDelta($logs_sql);
        
        // Services table for admin-configured services
        $services_table = $wpdb->prefix . 'bms_services';
        
        $services_sql = "CREATE TABLE $services_table (
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
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY service_key (service_key),
            KEY idx_enabled (enabled),
            KEY idx_category (category);
        ) $charset_collate;";
        
        dbDelta($services_sql);
        
        // Phase 2 Completion: Create tyre system tables
        self::create_tyre_tables();
        
        // Update database version
        update_option('bms_database_version', '1.3.0');
        
        // Initialize default services if table is empty
        self::init_default_services();
        
        return true;
    }
    
    /**
     * Initialize default services
     */
    private static function init_default_services() {
        global $wpdb;
        
        $services_table = $wpdb->prefix . 'bms_services';
        
        // Check if services already exist
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $services_table");
        
        if ($count == 0) {
            $default_services = array(
                array(
                    'service_key' => 'mot_test',
                    'name' => 'MOT Test',
                    'description' => 'Mandatory annual test for vehicles over 3 years old',
                    'base_price' => 40.00,
                    'pricing_type' => 'fixed',
                    'duration' => 60,
                    'category' => 'testing',
                    'enabled' => 1,
                    'sort_order' => 1,
                    'features' => json_encode(array(
                        'Safety inspection',
                        'Emissions test', 
                        'Roadworthiness check',
                        'Official MOT certificate'
                    ))
                ),
                array(
                    'service_key' => 'full_service',
                    'name' => 'Full Service',
                    'description' => 'Comprehensive vehicle service with detailed inspection',
                    'base_price' => 149.00,
                    'pricing_type' => 'engine_based',
                    'duration' => 120,
                    'category' => 'servicing',
                    'enabled' => 1,
                    'sort_order' => 2,
                    'features' => json_encode(array(
                        'Engine oil change',
                        'Filter replacements',
                        'Brake inspection',
                        'Battery test',
                        'Fluid top-ups',
                        'Comprehensive health check'
                    ))
                ),
                array(
                    'service_key' => 'interim_service',
                    'name' => 'Interim Service',
                    'description' => 'Basic service to keep your vehicle running smoothly',
                    'base_price' => 89.00,
                    'pricing_type' => 'engine_based',
                    'duration' => 90,
                    'category' => 'servicing',
                    'enabled' => 1,
                    'sort_order' => 3,
                    'features' => json_encode(array(
                        'Engine oil change',
                        'Oil filter replacement',
                        'Basic safety check',
                        'Fluid levels check',
                        'Tyre pressure check'
                    ))
                )
            );
            
            foreach ($default_services as $service) {
                $wpdb->insert($services_table, $service);
            }
        }
    }
    
    /**
     * Check if tables exist and are up to date
     */
    public static function check_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        $current_version = get_option('bms_database_version', '0.0.0');
        $needs_update = version_compare($current_version, '1.3.0', '<');
        
        return array(
            'tables_exist' => $table_exists,
            'needs_update' => $needs_update,
            'current_version' => $current_version,
            'required_version' => '1.3.0'
        );
    }
    
    /**
     * Get database status for admin display
     */
    public static function get_status() {
        $check = self::check_database();
        
        if (!$check['tables_exist']) {
            return array(
                'status' => 'error',
                'message' => 'Database tables not found. Click to create them.',
                'action' => 'create'
            );
        }
        
        if ($check['needs_update']) {
            return array(
                'status' => 'warning', 
                'message' => 'Database needs update from v' . $check['current_version'] . ' to v' . $check['required_version'],
                'action' => 'update'
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'Database tables are up to date (v' . $check['current_version'] . ')',
            'action' => 'none'
        );
    }
    
    /**
     * Create sample booking data for testing
     */
    public static function create_sample_data() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        // Check if sample data already exists
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        if ($count == 0) {
            $sample_bookings = array(
                array(
                    'booking_reference' => 'WEB-TEST01',
                    'service_type' => 'mot_test',
                    'booking_date' => date('Y-m-d', strtotime('+1 day')),
                    'booking_time' => '09:00:00',
                    'vehicle_reg' => 'AB12 CDE',
                    'vehicle_make' => 'FORD',
                    'vehicle_model' => 'FOCUS',
                    'vehicle_year' => 2018,
                    'vehicle_engine_size' => 1600,
                    'vehicle_fuel_type' => 'petrol',
                    'customer_name' => 'John Smith',
                    'customer_email' => 'john.smith@example.com',
                    'customer_phone' => '07700900000',
                    'calculated_price' => 40.00,
                    'payment_status' => 'paid',
                    'booking_status' => 'confirmed'
                ),
                array(
                    'booking_reference' => 'WEB-TEST02',
                    'service_type' => 'full_service',
                    'booking_date' => date('Y-m-d', strtotime('+2 days')),
                    'booking_time' => '10:00:00',
                    'vehicle_reg' => 'XY34 ZAB',
                    'vehicle_make' => 'VOLKSWAGEN',
                    'vehicle_model' => 'GOLF',
                    'vehicle_year' => 2020,
                    'vehicle_engine_size' => 2000,
                    'vehicle_fuel_type' => 'diesel',
                    'customer_name' => 'Jane Doe',
                    'customer_email' => 'jane.doe@example.com',
                    'customer_phone' => '07700900001',
                    'calculated_price' => 255.00,
                    'payment_status' => 'paid',
                    'booking_status' => 'confirmed'
                )
            );
            
            foreach ($sample_bookings as $booking) {
                $wpdb->insert($table_name, $booking);
            }
            
            return count($sample_bookings);
        }
        
        return 0;
    }
    
    /**
     * Create tyre system tables - Phase 2 Completion
     */
    private static function create_tyre_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tyres inventory table
        $tyres_table = $wpdb->prefix . 'bms_tyres';
        
        $tyres_sql = "CREATE TABLE $tyres_table (
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
            PRIMARY KEY  (id),
            KEY idx_size (size),
            KEY idx_brand (brand),
            KEY idx_dimensions (width, profile, rim),
            KEY idx_active_stock (is_active, stock_quantity),
            KEY idx_brand_tier (brand_tier),
            KEY idx_price (price);
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($tyres_sql);
        
        // Tyre bookings table
        $tyre_bookings_table = $wpdb->prefix . 'bms_tyre_bookings';
        
        $tyre_bookings_sql = "CREATE TABLE $tyre_bookings_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            booking_reference varchar(20) NOT NULL,
            customer_name varchar(100) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_phone varchar(20) NOT NULL,
            vehicle_reg varchar(10) NOT NULL,
            vehicle_make varchar(50) DEFAULT NULL,
            vehicle_model varchar(50) DEFAULT NULL,
            vehicle_year int(4) DEFAULT NULL,
            tyre_id int(11) NOT NULL,
            quantity int(1) NOT NULL DEFAULT 1,
            fitting_date date NOT NULL,
            fitting_time time NOT NULL,
            estimated_duration int(3) DEFAULT 30,
            tyre_price decimal(8,2) NOT NULL,
            fitting_price decimal(6,2) NOT NULL,
            subtotal decimal(8,2) NOT NULL,
            vat_amount decimal(8,2) NOT NULL,
            total_price decimal(8,2) NOT NULL,
            payment_status enum('pending','paid','failed','refunded') DEFAULT 'pending',
            payment_reference varchar(100) DEFAULT NULL,
            booking_status enum('confirmed','completed','cancelled','no-show') DEFAULT 'confirmed',
            special_requirements text DEFAULT NULL,
            fitting_notes text DEFAULT NULL,
            disposal_required tinyint(1) DEFAULT 1,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY booking_reference (booking_reference),
            KEY idx_customer_email (customer_email),
            KEY idx_fitting_date (fitting_date),
            KEY idx_payment_status (payment_status),
            KEY idx_booking_status (booking_status),
            KEY fk_tyre_id (tyre_id);
        ) $charset_collate;";
        
        dbDelta($tyre_bookings_sql);
        
        // Initialize sample tyre data
        self::init_sample_tyres();
    }
    
    /**
     * Initialize sample tyre data for testing - Phase 2 Competitive Advantage
     */
    private static function init_sample_tyres() {
        global $wpdb;
        
        $tyres_table = $wpdb->prefix . 'bms_tyres';
        
        // Check if tyres already exist
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $tyres_table");
        
        if ($count == 0) {
            // Add essential tyre inventory for immediate competitive advantage
            $sample_tyres = array(
                // Premium brands
                array('brand' => 'Michelin', 'model' => 'Energy Saver+', 'size' => '195/65R15', 'width' => 195, 'profile' => 65, 'rim' => 15, 'price' => 89.99, 'brand_tier' => 'premium', 'fuel_efficiency' => 'A', 'wet_grip' => 'B'),
                array('brand' => 'Continental', 'model' => 'EcoContact 6', 'size' => '205/55R16', 'width' => 205, 'profile' => 55, 'rim' => 16, 'price' => 94.99, 'brand_tier' => 'premium', 'fuel_efficiency' => 'A', 'wet_grip' => 'B'),
                array('brand' => 'Bridgestone', 'model' => 'Turanza T005', 'size' => '215/60R16', 'width' => 215, 'profile' => 60, 'rim' => 16, 'price' => 124.99, 'brand_tier' => 'premium', 'fuel_efficiency' => 'B', 'wet_grip' => 'A'),
                
                // Mid-range brands
                array('brand' => 'Goodyear', 'model' => 'EfficientGrip Performance', 'size' => '195/65R15', 'width' => 195, 'profile' => 65, 'rim' => 15, 'price' => 74.99, 'brand_tier' => 'mid-range', 'fuel_efficiency' => 'B', 'wet_grip' => 'B'),
                array('brand' => 'Pirelli', 'model' => 'Cinturato P7', 'size' => '205/55R16', 'width' => 205, 'profile' => 55, 'rim' => 16, 'price' => 94.99, 'brand_tier' => 'mid-range', 'fuel_efficiency' => 'C', 'wet_grip' => 'A'),
                array('brand' => 'Dunlop', 'model' => 'Sport BluResponse', 'size' => '215/60R16', 'width' => 215, 'profile' => 60, 'rim' => 16, 'price' => 84.99, 'brand_tier' => 'mid-range', 'fuel_efficiency' => 'B', 'wet_grip' => 'A'),
                
                // Budget brands
                array('brand' => 'Avon', 'model' => 'ZT7', 'size' => '175/65R14', 'width' => 175, 'profile' => 65, 'rim' => 14, 'price' => 45.99, 'brand_tier' => 'budget', 'fuel_efficiency' => 'C', 'wet_grip' => 'B'),
                array('brand' => 'Falken', 'model' => 'Sincera SN832', 'size' => '185/60R15', 'width' => 185, 'profile' => 60, 'rim' => 15, 'price' => 52.99, 'brand_tier' => 'budget', 'fuel_efficiency' => 'C', 'wet_grip' => 'B'),
                array('brand' => 'Kumho', 'model' => 'Ecowing ES01', 'size' => '195/65R15', 'width' => 195, 'profile' => 65, 'rim' => 15, 'price' => 59.99, 'brand_tier' => 'budget', 'fuel_efficiency' => 'B', 'wet_grip' => 'B'),
            );
            
            foreach ($sample_tyres as $tyre) {
                $tyre['fitting_price'] = 25.00;
                $tyre['stock_quantity'] = 15;
                $tyre['is_active'] = 1;
                $tyre['speed_rating'] = 'H';
                $tyre['load_index'] = 91;
                $tyre['noise_rating'] = 70;
                $tyre['season'] = 'summer';
                
                $wpdb->insert($tyres_table, $tyre);
            }
        }
    }
    
    /**
     * Drop all tables - for clean reinstall
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'bms_appointments',
            $wpdb->prefix . 'bms_booking_logs', 
            $wpdb->prefix . 'bms_services',
            $wpdb->prefix . 'bms_tyres',
            $wpdb->prefix . 'bms_tyre_bookings');
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        delete_option('bms_database_version');
        
        return true;
    }
}
