<?php
/**
 * Blue Motors Database Setup Test
 * Run this to verify database integration is working
 */

// WordPress environment check
if (!defined('ABSPATH')) {
    // Load WordPress if running standalone
    require_once '../../../wp-load.php';
}

echo "🧪 Blue Motors Database Integration Test\n";
echo "==========================================\n\n";

// Test 1: Check if enhanced database manager is available
echo "1. Testing Enhanced Database Manager Class...\n";
if (class_exists('BMS_Database_Manager_Enhanced')) {
    echo "   ✅ BMS_Database_Manager_Enhanced class loaded successfully\n";
    
    // Test comprehensive status
    $status = BMS_Database_Manager_Enhanced::get_comprehensive_status();
    echo "   📊 Database Status: " . ($status['all_exist'] ? '✅ Ready' : '⚠️ Needs Setup') . "\n";
    
    if (!$status['all_exist']) {
        echo "   🔧 Initializing database tables...\n";
        $result = BMS_Database_Manager_Enhanced::create_tables();
        if ($result) {
            echo "   ✅ Database tables created successfully\n";
        } else {
            echo "   ❌ Database table creation failed\n";
        }
    }
    
} else {
    echo "   ❌ Enhanced Database Manager not found\n";
    echo "   🔄 Attempting to load manually...\n";
    
    $enhanced_path = plugin_dir_path(__FILE__) . 'includes/services/class-database-manager-enhanced.php';
    if (file_exists($enhanced_path)) {
        require_once $enhanced_path;
        echo "   ✅ Enhanced Database Manager loaded manually\n";
    } else {
        echo "   ❌ Enhanced Database Manager file not found\n";
    }
}

echo "\n";

// Test 2: Check admin functions
echo "2. Testing Admin Functions...\n";
$admin_file = plugin_dir_path(__FILE__) . 'admin/database-status.php';
if (file_exists($admin_file)) {
    echo "   ✅ Database status admin file exists\n";
    
    // Load admin functions
    require_once $admin_file;
    
    if (function_exists('bms_enhanced_database_status_page')) {
        echo "   ✅ Enhanced database status function available\n";
    } else {
        echo "   ⚠️ Enhanced database status function not found\n";
    }
} else {
    echo "   ❌ Database status admin file not found\n";
}

echo "\n";

// Test 3: Check plugin activation
echo "3. Testing Plugin Integration...\n";
if (is_plugin_active('blue-motors-southampton/blue-motors-southampton.php')) {
    echo "   ✅ Plugin is active\n";
} else {
    echo "   ⚠️ Plugin is not active - activate it in WordPress admin\n";
}

echo "\n";

// Test 4: Database Tables Check
global $wpdb;
echo "4. Testing Database Tables...\n";

$required_tables = [
    $wpdb->prefix . 'bms_appointments',
    $wpdb->prefix . 'bms_services', 
    $wpdb->prefix . 'bms_tyres',
    $wpdb->prefix . 'bms_tyre_bookings'];

foreach ($required_tables as $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
    echo "   " . ($exists ? '✅' : '❌') . " Table: $table\n";
}

echo "\n🎉 Test Complete!\n";
echo "==========================================\n";
echo "Next Steps:\n";
echo "1. Go to WordPress Admin → Blue Motors → Database Status\n";
echo "2. Initialize database if needed\n";
echo "3. Test booking system functionality\n";
echo "4. Configure services and pricing\n";
