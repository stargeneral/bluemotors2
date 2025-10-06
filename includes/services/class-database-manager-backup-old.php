<?php
/**
 * Enhanced Database Manager for Blue Motors Southampton
 * 
 * Complete database management with Phase 3 enhancements
 * Handles all database operations through admin interface
 * 
 * @package BlueMotosSouthampton
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BMS_Database_Manager_Enhanced {
    
    const VERSION = '2.0.0';
    
    /**
     * Create or update all database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // 1. Main appointments table
        self::create_appointments_table($charset_collate);
        
        // 2. Booking logs table
        self::create_logs_table($charset_collate);
        
        // 3. Enhanced services table
        self::create_services_table($charset_collate);
        
        // 4. Comprehensive tyre system
        self::create_tyre_system_tables($charset_collate);
        
        // 5. Customer service history
        self::create_customer_tables($charset_collate);
        
        // 6. Performance optimization indexes
        self::create_optimization_indexes();
        
        // Update database version
        update_option('bms_database_version', self::VERSION);
        
        // Initialize data
        self::init_all_default_data();
        
        return true;
    }
    
    /**
     * Create main appointments table with optimizations
     */
    private static function create_appointments_table($charset_collate) {
        global $wpdb;
        
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
            reminder_sent tinyint(1) DEFAULT 0,
            completed_at timestamp NULL DEFAULT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY booking_reference (booking_reference),
            KEY idx_date_time (booking_date, booking_time),
            KEY idx_customer_email (customer_email),
            KEY idx_status_combo (booking_status, payment_status),
            KEY idx_service_date (service_type, booking_date),
            KEY idx_created_at (created_at);
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create comprehensive tyre system tables
     */
    private static function create_tyre_system_tables($charset_collate) {
        global $wpdb;
        
        // Enhanced tyres inventory table
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
            PRIMARY KEY (id),
            KEY idx_size_active (size, is_active, stock_quantity),
            KEY idx_brand_tier_price (brand_tier, price),
            KEY idx_dimensions_active (width, profile, rim, is_active),
            KEY idx_brand_active (brand, is_active),
            FULLTEXT KEY idx_search (brand, model, size);
        ) $charset_collate;";
        
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
            PRIMARY KEY (id),
            UNIQUE KEY booking_reference (booking_reference),
            KEY idx_customer_email (customer_email),
            KEY idx_fitting_date_time (fitting_date, fitting_time),
            KEY idx_status_combo (payment_status, booking_status),
            KEY fk_tyre_id (tyre_id),
            FOREIGN KEY (tyre_id) REFERENCES $tyres_table(id) ON DELETE RESTRICT) $charset_collate;";
        
        dbDelta($tyre_bookings_sql);
    }
    
    /**
     * Create enhanced services table with Phase 3 services
     */
    private static function create_services_table($charset_collate) {
        global $wpdb;
        
        $services_table = $wpdb->prefix . 'bms_services';
        
        $services_sql = "CREATE TABLE $services_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            service_key varchar(50) NOT NULL,
            name varchar(255) NOT NULL,
            description text,
            long_description text,
            base_price decimal(10,2) NOT NULL,
            pricing_type varchar(20) DEFAULT 'fixed',
            duration int(11) DEFAULT 60,
            category varchar(50) DEFAULT NULL,
            enabled tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            features text,
            competitive_note text,
            seasonal_demand tinyint(1) DEFAULT 0,
            f1_equivalent tinyint(1) DEFAULT 0,
            icon varchar(10) DEFAULT '🔧',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY service_key (service_key),
            KEY idx_enabled_category (enabled, category),
            KEY idx_sort_order (sort_order);
        ) $charset_collate;";
        
        dbDelta($services_sql);
    }
    
    /**
     * Create customer service history tables
     */
    private static function create_customer_tables($charset_collate) {
        global $wpdb;
        
        // Customer profiles table
        $customers_table = $wpdb->prefix . 'bms_customers';
        
        $customers_sql = "CREATE TABLE $customers_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            name varchar(255) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            address text DEFAULT NULL,
            postcode varchar(10) DEFAULT NULL,
            total_bookings int(11) DEFAULT 0,
            total_spent decimal(10,2) DEFAULT 0.00,
            loyalty_tier enum('new','bronze','silver','gold') DEFAULT 'new',
            last_service_date date DEFAULT NULL,
            email_marketing_consent tinyint(1) DEFAULT 0,
            notes text DEFAULT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY idx_loyalty_tier (loyalty_tier),
            KEY idx_last_service (last_service_date);
        ) $charset_collate;";
        
        dbDelta($customers_sql);
    }
    
    /**
     * Create performance optimization indexes
     */
    private static function create_optimization_indexes() {
        global $wpdb;
        
        // Additional indexes for performance
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_dashboard_stats ON {$wpdb->prefix}bms_appointments (booking_date, booking_status, payment_status, service_type)");
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_revenue_analysis ON {$wpdb->prefix}bms_appointments (booking_date, calculated_price, payment_status)");
        $wpdb->query("CREATE INDEX IF NOT EXISTS idx_customer_history ON {$wpdb->prefix}bms_appointments (customer_email, booking_date DESC)");
    }
    
    /**
     * Initialize all default data
     */
    public static function init_all_default_data() {
        self::init_enhanced_services();
        self::init_comprehensive_tyre_data();
        self::create_database_views();
    }
    
    /**
     * Initialize enhanced services including Phase 3 additions
     */
    private static function init_enhanced_services() {
        global $wpdb;
        
        $services_table = $wpdb->prefix . 'bms_services';
        
        // Check if services already exist
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $services_table");
        
        if ($count == 0) {
            $enhanced_services = array(
                // Core services
                array(
                    'service_key' => 'mot_test',
                    'name' => 'MOT Test',
                    'description' => 'Mandatory annual test for vehicles over 3 years old',
                    'long_description' => 'Comprehensive vehicle safety, roadworthiness and emissions test required by law.',
                    'base_price' => 40.00,
                    'pricing_type' => 'fixed',
                    'duration' => 60,
                    'category' => 'testing',
                    'enabled' => 1,
                    'sort_order' => 1,
                    'features' => json_encode(array('Safety inspection', 'Emissions test', 'Roadworthiness check', 'Official MOT certificate')),
                    'f1_equivalent' => 1,
                    'icon' => '🔍'
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
                    'features' => json_encode(array('Engine oil change', 'Filter replacements', 'Brake inspection', 'Battery test', 'Fluid top-ups')),
                    'f1_equivalent' => 1,
                    'icon' => '🔧'
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
                    'features' => json_encode(array('Engine oil change', 'Oil filter replacement', 'Basic safety check')),
                    'f1_equivalent' => 1,
                    'icon' => '⚙️'
                ),
                
                // Phase 3: Air Conditioning Services
                array(
                    'service_key' => 'air_con_regas',
                    'name' => 'Air Conditioning Re-gas',
                    'description' => 'Complete air conditioning system re-gas and performance check',
                    'long_description' => 'Professional AC service including leak detection, system evacuation, fresh refrigerant refill, and performance testing.',
                    'base_price' => 89.00,
                    'pricing_type' => 'fixed',
                    'duration' => 60,
                    'category' => 'climate',
                    'enabled' => 1,
                    'sort_order' => 10,
                    'features' => json_encode(array('System leak check', 'Refrigerant refill', 'Performance test', 'Component inspection')),
                    'competitive_note' => 'industry leaders offers this - we do too, but with online booking!',
                    'seasonal_demand' => 1,
                    'f1_equivalent' => 1,
                    'icon' => '❄️'
                ),
                array(
                    'service_key' => 'air_con_service',
                    'name' => 'Air Conditioning Full Service',
                    'description' => 'Comprehensive AC service including cleaning and antibacterial treatment',
                    'base_price' => 129.00,
                    'pricing_type' => 'fixed',
                    'duration' => 90,
                    'category' => 'climate',
                    'enabled' => 1,
                    'sort_order' => 11,
                    'features' => json_encode(array('System inspection', 'Evaporator cleaning', 'Antibacterial treatment', 'Filter replacement')),
                    'seasonal_demand' => 1,
                    'icon' => '🌬️'
                ),
                
                // Phase 3: Brake Services
                array(
                    'service_key' => 'brake_check',
                    'name' => 'Brake Inspection & Service',
                    'description' => 'Comprehensive brake system inspection with detailed report',
                    'base_price' => 45.00,
                    'pricing_type' => 'fixed',
                    'duration' => 45,
                    'category' => 'safety',
                    'enabled' => 1,
                    'sort_order' => 20,
                    'features' => json_encode(array('Brake pad inspection', 'Disc condition check', 'Fluid inspection', 'Written report')),
                    'f1_equivalent' => 1,
                    'icon' => '🛑'
                ),
                array(
                    'service_key' => 'brake_service',
                    'name' => 'Brake Pad & Disc Service',
                    'description' => 'Professional brake component replacement with warranty',
                    'base_price' => 149.00,
                    'pricing_type' => 'fixed',
                    'duration' => 120,
                    'category' => 'safety',
                    'enabled' => 1,
                    'sort_order' => 21,
                    'features' => json_encode(array('Quality parts', 'Professional fitting', 'System testing', '12-month warranty')),
                    'icon' => '🔧'
                ),
                
                // Phase 3: Battery Services
                array(
                    'service_key' => 'battery_test',
                    'name' => 'Battery Test & Health Check',
                    'description' => 'Professional battery testing with charging system check',
                    'base_price' => 25.00,
                    'pricing_type' => 'fixed',
                    'duration' => 30,
                    'category' => 'electrical',
                    'enabled' => 1,
                    'sort_order' => 30,
                    'features' => json_encode(array('Voltage testing', 'Load testing', 'Charging system check', 'Health report')),
                    'f1_equivalent' => 1,
                    'icon' => '🔋'
                ),
                array(
                    'service_key' => 'battery_replacement',
                    'name' => 'Battery Replacement Service',
                    'description' => 'Quality battery replacement with fitting and testing',
                    'base_price' => 89.00,
                    'pricing_type' => 'fixed',
                    'duration' => 45,
                    'category' => 'electrical',
                    'enabled' => 1,
                    'sort_order' => 31,
                    'features' => json_encode(array('Quality battery', 'Professional fitting', 'System testing', '24-month warranty')),
                    'icon' => '⚡'
                ),
                
                // Phase 3: Exhaust Services
                array(
                    'service_key' => 'exhaust_check',
                    'name' => 'Exhaust System Inspection',
                    'description' => 'Comprehensive exhaust check including emissions testing',
                    'base_price' => 35.00,
                    'pricing_type' => 'fixed',
                    'duration' => 30,
                    'category' => 'emissions',
                    'enabled' => 1,
                    'sort_order' => 40,
                    'features' => json_encode(array('Visual inspection', 'Emissions check', 'Noise assessment', 'Written report')),
                    'f1_equivalent' => 1,
                    'icon' => '💨'
                ),
                
                // Phase 2: Tyre Services
                array(
                    'service_key' => 'tyre_fitting',
                    'name' => 'Tyre Fitting Service',
                    'description' => 'Professional tyre fitting with balancing and disposal',
                    'base_price' => 25.00,
                    'pricing_type' => 'per_tyre',
                    'duration' => 30,
                    'category' => 'tyres',
                    'enabled' => 1,
                    'sort_order' => 50,
                    'features' => json_encode(array('Professional fitting', 'Wheel balancing', 'Valve replacement', 'Old tyre disposal')),
                    'competitive_note' => 'Order tyres online - F1 customers must call!',
                    'icon' => '🛞'
                );
            );
            
            foreach ($enhanced_services as $service) {
                $wpdb->insert($services_table, $service);
            }
        }
    }
    
    /**
     * Initialize comprehensive tyre data for competitive advantage
     */
    private static function init_comprehensive_tyre_data() {
        global $wpdb;
        
        $tyres_table = $wpdb->prefix . 'bms_tyres';
        
        // Check if tyres already exist
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $tyres_table");
        
        if ($count == 0) {
            $sample_tyres = array(
                // Premium brands
                array('Michelin', 'Pilot Sport 4', '205/55R16', 205, 55, 16, 'W', 91, 129.99, 25.00, 'premium', 'C', 'A', 69),
                array('Michelin', 'Energy Saver+', '195/65R15', 195, 65, 15, 'H', 91, 89.99, 25.00, 'premium', 'A', 'B', 68),
                array('Continental', 'PremiumContact 6', '215/60R16', 215, 60, 16, 'V', 95, 119.99, 25.00, 'premium', 'B', 'A', 71),
                array('Bridgestone', 'Turanza T005', '225/50R17', 225, 50, 17, 'W', 94, 139.99, 25.00, 'premium', 'B', 'A', 70),
                
                // Mid-range brands
                array('Goodyear', 'EfficientGrip Performance', '205/55R16', 205, 55, 16, 'V', 91, 89.99, 25.00, 'mid-range', 'B', 'A', 70),
                array('Pirelli', 'Cinturato P7', '195/65R15', 195, 65, 15, 'H', 91, 79.99, 25.00, 'mid-range', 'C', 'A', 71),
                array('Dunlop', 'Sport BluResponse', '215/60R16', 215, 60, 16, 'V', 95, 94.99, 25.00, 'mid-range', 'B', 'A', 68),
                array('Hankook', 'Ventus Prime3', '225/50R17', 225, 50, 17, 'W', 94, 99.99, 25.00, 'mid-range', 'C', 'A', 72),
                
                // Budget brands
                array('Avon', 'ZT7', '175/65R14', 175, 65, 14, 'T', 82, 45.99, 25.00, 'budget', 'C', 'B', 70),
                array('Falken', 'Sincera SN832', '185/60R15', 185, 60, 15, 'H', 84, 52.99, 25.00, 'budget', 'C', 'B', 71),
                array('Kumho', 'Ecowing ES01', '195/65R15', 195, 65, 15, 'H', 91, 59.99, 25.00, 'budget', 'B', 'B', 69),
                array('Nexen', 'N Blue HD Plus', '205/55R16', 205, 55, 16, 'V', 91, 64.99, 25.00, 'budget', 'C', 'B', 70);
            );
            
            foreach ($sample_tyres as $tyre) {
                $wpdb->insert($tyres_table, array(
                    'brand' => $tyre[0],
                    'model' => $tyre[1],
                    'size' => $tyre[2],
                    'width' => $tyre[3],
                    'profile' => $tyre[4],
                    'rim' => $tyre[5],
                    'speed_rating' => $tyre[6],
                    'load_index' => $tyre[7],
                    'price' => $tyre[8],
                    'fitting_price' => $tyre[9],
                    'brand_tier' => $tyre[10],
                    'fuel_efficiency' => $tyre[11],
                    'wet_grip' => $tyre[12],
                    'noise_rating' => $tyre[13],
                    'stock_quantity' => 15,
                    'is_active' => 1
                ));
            }
        }
    }
    
    /**
     * Create database views for performance
     */
    private static function create_database_views() {
        global $wpdb;
        
        // Dashboard statistics view
        $wpdb->query("CREATE OR REPLACE VIEW vw_bms_dashboard_stats AS
            SELECT 
                DATE(booking_date) as date,
                COUNT(*) as total_bookings,
                SUM(calculated_price) as total_revenue,
                COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as paid_bookings,
                COUNT(CASE WHEN booking_status = 'confirmed' THEN 1 END) as confirmed_bookings,
                service_type,
                HOUR(booking_time) as appointment_hour
            FROM {$wpdb->prefix}bms_appointments 
            WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(booking_date), service_type, HOUR(booking_time)");
    }
    
    /**
     * Enhanced database status check
     */
    public static function get_comprehensive_status() {
        global $wpdb;
        
        $tables = array(
            'bms_appointments' => 'Main booking appointments',
            'bms_booking_logs' => 'Booking activity logs',  
            'bms_services' => 'Service definitions',
            'bms_tyres' => 'Tyre inventory',
            'bms_tyre_bookings' => 'Tyre booking orders',
            'bms_customers' => 'Customer profiles');
        
        $status = array();
        $all_exist = true;
        $total_records = 0;
        
        foreach ($tables as $table_suffix => $description) {;
            $table_name = $wpdb->prefix . $table_suffix;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            
            if ($table_exists) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                $total_records += $count;
                $status[$table_suffix] = array(
                    'exists' => true,
                    'records' => $count,
                    'description' => $description
                );
            } else {
                $all_exist = false;
                $status[$table_suffix] = array(
                    'exists' => false,
                    'records' => 0,
                    'description' => $description
                );
            }
        }
        
        $current_version = get_option('bms_database_version', '0.0.0');
        $needs_update = version_compare($current_version, self::VERSION, '<');
        
        return array(
            'tables' => $status,
            'all_exist' => $all_exist,
            'total_records' => $total_records,
            'current_version' => $current_version,
            'required_version' => self::VERSION,
            'needs_update' => $needs_update,
            'performance_optimized' => self::check_optimization_status()
        );
    }
    
    /**
     * Check optimization status
     */
    private static function check_optimization_status() {
        global $wpdb;
        
        // Check if key indexes exist
        $indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->prefix}bms_appointments WHERE Key_name = 'idx_dashboard_stats'");
        
        return !empty($indexes);
    }
    
    /**
     * Database health check
     */
    public static function health_check() {
        global $wpdb;
        
        $health = array();
        
        // Check table sizes
        $tables = array('bms_appointments', 'bms_tyres', 'bms_tyre_bookings', 'bms_services');
        
        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $size_query = $wpdb->get_row($wpdb->prepare(
                "SELECT 
                    COUNT(*) as row_count,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = %s AND table_name = %s",
                DB_NAME, $table_name));
            
            $health[$table] = $size_query;
        }
        
        return $health;
    }
    
    /**
     * Create sample data for testing
     */
    public static function create_sample_data() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        // Check if sample data already exists
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE notes LIKE '%Sample data%'");
        
        if ($existing > 0) {
            return 0; // Sample data already exists
        }
        
        $sample_data = array(
            array(
                'booking_reference' => 'WEB-001',
                'service_type' => 'mot_test',
                'booking_date' => date('Y-m-d', strtotime('+3 days')),
                'booking_time' => '09:00:00',
                'vehicle_reg' => 'AB12CDE',
                'vehicle_make' => 'Ford',
                'vehicle_model' => 'Focus',
                'customer_name' => 'John Smith',
                'customer_email' => 'john.smith@example.com',
                'customer_phone' => '07700900123',
                'calculated_price' => 40.00,
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
                'notes' => 'Sample data for testing'
            ),
            array(
                'booking_reference' => 'WEB-002',
                'service_type' => 'full_service',
                'booking_date' => date('Y-m-d', strtotime('+5 days')),
                'booking_time' => '14:00:00',
                'vehicle_reg' => 'XY98ZAB',
                'vehicle_make' => 'BMW',
                'vehicle_model' => '3 Series',
                'customer_name' => 'Sarah Johnson',
                'customer_email' => 'sarah.johnson@example.com',
                'customer_phone' => '07700900456',
                'calculated_price' => 245.00,
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
                'notes' => 'Sample data for testing'
            );
        );
        
        $count = 0;
        foreach ($sample_data as $booking) {
            $result = $wpdb->insert($table_name, $booking);
            if ($result) $count++;
        }
        
        return $count;
    }
}