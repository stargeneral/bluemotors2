<?php
/**
 * Phase 3 Testing and Verification System
 * 
 * Tests the integration between booking system and admin settings
 * 
 * @package BlueMotosSouthampton
 * @since 1.3.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Phase 3 Integration Tests
 */
class BMS_Phase3_Tests {
    
    /**
     * Run all Phase 3 tests
     */
    public static function run_all_tests() {
        echo '<div class="wrap">';
        echo '<h1>🔧 Phase 3 Integration Testing</h1>';
        
        $tests = array(
            'test_booking_integration_loaded' => 'Booking Integration Class Loaded',
            'test_settings_migration' => 'Settings Migration Working',
            'test_service_management' => 'Service Management System',
            'test_business_hours_integration' => 'Business Hours Integration',
            'test_pricing_calculator_integration' => 'Pricing Calculator Integration',
            'test_database_tables' => 'Database Tables Created',
            'test_availability_system' => 'Availability System',
            'test_booking_reference_generation' => 'Booking Reference Generation',
            'test_admin_interfaces' => 'Admin Interface Accessibility');
        
        echo '<div class="bms-test-results">';
        foreach ($tests as $test_method => $test_name) {
            if (method_exists(__CLASS__, $test_method)) {
                $result = call_user_func(array(__CLASS__, $test_method));
                self::display_test_result($test_name, $result);
            }
        }
        echo '</div>';
        
        // Phase 3 readiness summary
        self::display_phase3_readiness();
        
        echo '</div>';
    }
    
    /**
     * Test if booking integration class is loaded
     */
    private static function test_booking_integration_loaded() {
        return array(
            'success' => class_exists('BMS_Booking_Integration'),
            'message' => class_exists('BMS_Booking_Integration') 
                ? 'Booking integration class loaded successfully' 
                : 'Booking integration class not found'
        );
    }
    
    /**
     * Test settings migration system
     */
    private static function test_settings_migration() {
        if (!class_exists('BMS_Settings_Migrator')) {
            return array('success' => false, 'message' => 'Settings migrator class not found');
        }
        
        $migrated = BMS_Settings_Migrator::is_migrated();
        return array(
            'success' => $migrated,
            'message' => $migrated 
                ? 'Settings successfully migrated to database' 
                : 'Settings not yet migrated - run migration first'
        );
    }
    
    /**
     * Test service management system
     */
    private static function test_service_management() {
        if (!class_exists('\BlueMotosSouthampton\Services\ServiceManagerEnhanced')) {
            return array('success' => false, 'message' => 'Service manager class not found');
        }
        
        try {
            $services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true);
            $count = count($services);
            return array(
                'success' => $count > 0,
                'message' => "Service management working - {$count} services configured"
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Service management error: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test business hours integration
     */
    private static function test_business_hours_integration() {
        if (!class_exists('BMS_Booking_Integration')) {
            return array('success' => false, 'message' => 'Booking integration not loaded');
        }
        
        try {
            $hours = BMS_Booking_Integration::get_dynamic_business_hours();
            $has_hours = !empty($hours) && isset($hours['monday']);
            return array(
                'success' => $has_hours,
                'message' => $has_hours 
                    ? 'Business hours integration working' 
                    : 'Business hours not properly configured'
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Business hours integration error: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test database tables
     */
    private static function test_database_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            return array(
                'success' => false,
                'message' => 'Appointments table does not exist. Use Database Status page to create it.'
            );
        }
        
        // Check if correct columns exist
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
        $column_names = array();
        foreach ($columns as $column) {
            $column_names[] = $column->Field;
        }
        
        $required_columns = array('booking_date', 'booking_time', 'booking_reference');
        $missing_columns = array_diff($required_columns, $column_names);
        
        if (!empty($missing_columns)) {
            return array(
                'success' => false,
                'message' => 'Table exists but missing columns: ' . implode(', ', $missing_columns)
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Database table exists with correct columns'
        );
    }
    
    /**
     * Test pricing calculator integration
     */
    private static function test_pricing_calculator_integration() {
        if (!class_exists('\BlueMotosSouthampton\Services\ServiceManagerEnhanced')) {
            return array('success' => false, 'message' => 'Service manager not available');
        }
        
        try {
            // Test price calculation for MOT
            $test_vehicle = array(
                'engine_size' => 1600,
                'fuel_type' => 'petrol');
            
            $price = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::calculate_price('mot_test', $test_vehicle);
            return array(
                'success' => $price > 0,
                'message' => $price > 0 
                    ? "Pricing calculator working - MOT test: £{$price}" 
                    : 'Pricing calculator returned zero price'
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Pricing calculator error: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test availability system
     */
    private static function test_availability_system() {
        if (!class_exists('BMS_Booking_Integration')) {
            return array('success' => false, 'message' => 'Booking integration not loaded');
        }
        
        try {
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $slots = BMS_Booking_Integration::get_dynamic_available_slots(array(), $tomorrow, 60);
            
            return array(
                'success' => is_array($slots),
                'message' => is_array($slots) 
                    ? 'Availability system working - ' . count($slots) . ' slots for tomorrow' 
                    : 'Availability system not returning array'
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Availability system error: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test booking reference generation
     */
    private static function test_booking_reference_generation() {
        if (!class_exists('BMS_Booking_Integration')) {
            return array('success' => false, 'message' => 'Booking integration not loaded');
        }
        
        try {
            $reference = BMS_Booking_Integration::generate_booking_reference();
            $valid = !empty($reference) && strpos($reference, '-') !== false;
            
            return array(
                'success' => $valid,
                'message' => $valid 
                    ? "Reference generation working - Sample: {$reference}" 
                    : 'Reference generation not working properly'
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Reference generation error: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test admin interface accessibility
     */
    private static function test_admin_interfaces() {
        // Check for WordPress admin callback functions (these are always loaded)
        $admin_functions = array(
            'blue_motors_southampton_services' => 'Enhanced Services Page',
            'blue_motors_southampton_bookings' => 'Enhanced Bookings Page', 
            'blue_motors_southampton_enhanced_settings' => 'Enhanced Settings Page',
            'blue_motors_southampton_business_settings' => 'Business Settings Page',
            'blue_motors_southampton_payment_settings' => 'Payment Settings Page');
        
        $accessible_count = 0;
        $messages = array();
        
        foreach ($admin_functions as $function_name => $page_name) {
            if (function_exists($function_name)) {
                $accessible_count++;
                $messages[] = "✓ {$page_name} (callback: {$function_name})";
            } else {
                $messages[] = "✗ {$page_name} - Callback function {$function_name} not found";
            }
        }
        
        // Also check if the admin files exist
        $admin_files = array(
            'services-enhanced.php' => 'Services Admin File',
            'bookings-enhanced.php' => 'Bookings Admin File',
            'enhanced-settings.php' => 'Settings Admin File',
            'business-settings.php' => 'Business Settings File',
            'payment-settings.php' => 'Payment Settings File');
        
        foreach ($admin_files as $file => $description) {
            $file_path = BMS_PLUGIN_DIR . 'admin/' . $file;
            if (file_exists($file_path)) {
                $accessible_count++;
                $messages[] = "✓ {$description}";
            } else {
                $messages[] = "✗ {$description} - File missing";
            }
        }
        
        $total_checks = count($admin_functions) + count($admin_files);
        
        return array(
            'success' => $accessible_count >= ($total_checks - 1), // Allow 1 failure
            'message' => "Admin interfaces: {$accessible_count}/{$total_checks} accessible<br>" . 
                        implode('<br>', $messages)
        );
    }
    
    /**
     * Display test result
     */
    private static function display_test_result($test_name, $result) {
        $status = $result['success'] ? 'PASS' : 'FAIL';
        $class = $result['success'] ? 'success' : 'error';
        
        echo '<div class="notice notice-' . $class . ' inline">';
        echo '<p><strong>' . $status . ':</strong> ' . $test_name . '</p>';
        echo '<p>' . $result['message'] . '</p>';
        echo '</div>';
    }
    
    /**
     * Display Phase 3 readiness summary
     */
    private static function display_phase3_readiness() {
        echo '<div class="postbox" style="margin-top: 30px;">';
        echo '<div class="postbox-header"><h2>🚀 Phase 3 Production Readiness</h2></div>';
        echo '<div class="inside">';
        
        // Check critical components
        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "bms_appointments'") == $wpdb->prefix . "bms_appointments";
        
        $components = array(
            'Settings Migration' => class_exists('BMS_Settings_Migrator') && BMS_Settings_Migrator::is_migrated(),
            'Service Management' => class_exists('\BlueMotosSouthampton\Services\ServiceManagerEnhanced'),
            'Booking Integration' => class_exists('BMS_Booking_Integration'),
            'Database Manager' => class_exists('BMS_Database_Manager'),
            'Database Tables' => $table_exists,
            'Admin Interfaces' => function_exists('blue_motors_southampton_services') && function_exists('blue_motors_southampton_bookings'),
            'Email System' => class_exists('BMS_SMTP') && class_exists('BMS_Email_Manager'),
        );
        
        $ready_count = 0;
        echo '<ul>';
        foreach ($components as $component => $ready) {
            $status = $ready ? '✅' : '❌';
            echo '<li>' . $status . ' ' . $component . '</li>';
            if ($ready) $ready_count++;
        }
        echo '</ul>';
        
        $percentage = round(($ready_count / count($components)) * 100);
        echo '<p><strong>Phase 3 Completion: ' . $percentage . '%</strong></p>';
        
        if ($percentage >= 90) {
            echo '<div class="notice notice-success inline">';
            echo '<p><strong>🎉 Phase 3 is PRODUCTION READY!</strong></p>';
            echo '<p>All critical systems are operational. Ready for live deployment.</p>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-warning inline">';
            echo '<p><strong>⚠️ Phase 3 needs completion</strong></p>';
            echo '<p>Complete the failing components above before production deployment.</p>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }
}

// Auto-run tests if accessed directly with debug mode
if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['bms_phase3_test'])) {
    add_action('admin_init', function() {
        if (current_user_can('manage_options')) {
            BMS_Phase3_Tests::run_all_tests();
        }
    });
}
