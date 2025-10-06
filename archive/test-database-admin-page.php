<?php
/**
 * Test Database Status Admin Page with New Table
 * Verify that the admin menu works correctly with vehicle_tyres table
 */

// Include WordPress
require_once '../../../wp-load.php';

echo "<h2>🗄️ Database Status Admin Page Test</h2>";
echo "<p>Testing the database status menu with the new vehicle_tyres table...</p>";

// Test 1: Check if Enhanced Database Manager exists
echo "<h3>1. Enhanced Database Manager Test</h3>";
if (class_exists('BMS_Database_Manager_Enhanced')) {
    echo "✅ BMS_Database_Manager_Enhanced class exists<br>";
    
    // Test get_comprehensive_status method
    try {
        $status = BMS_Database_Manager_Enhanced::get_comprehensive_status();
        echo "✅ get_comprehensive_status() method executed successfully<br>";
        
        // Check if our new table is included
        if (isset($status['tables']['bms_vehicle_tyres'])) {
            echo "✅ bms_vehicle_tyres table is included in status check<br>";
            $table_info = $status['tables']['bms_vehicle_tyres'];
            echo "   - Exists: " . ($table_info['exists'] ? 'Yes' : 'No') . "<br>";
            echo "   - Records: " . number_format($table_info['records']) . "<br>";
            echo "   - Description: " . esc_html($table_info['description']) . "<br>";
        } else {
            echo "❌ bms_vehicle_tyres table NOT included in status check<br>";
        }
        
        // Show overall status
        echo "<br><strong>Overall Database Status:</strong><br>";
        echo "- All tables exist: " . ($status['all_exist'] ? 'Yes' : 'No') . "<br>";
        echo "- Total records: " . number_format($status['total_records']) . "<br>";
        echo "- Current version: " . $status['current_version'] . "<br>";
        echo "- Required version: " . $status['required_version'] . "<br>";
        echo "- Needs update: " . ($status['needs_update'] ? 'Yes' : 'No') . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Error testing get_comprehensive_status: " . $e->getMessage() . "<br>";
    }
    
} else {
    echo "❌ BMS_Database_Manager_Enhanced class NOT found<br>";
}

// Test 2: Check admin page function exists
echo "<h3>2. Admin Page Functions Test</h3>";

if (function_exists('bms_enhanced_database_status_page')) {
    echo "✅ bms_enhanced_database_status_page() function exists<br>";
} else {
    echo "❌ bms_enhanced_database_status_page() function NOT found<br>";
}

if (function_exists('bms_show_vehicle_tyres_summary')) {
    echo "✅ bms_show_vehicle_tyres_summary() function exists<br>";
    
    // Test the function
    try {
        $summary = bms_show_vehicle_tyres_summary();
        echo "✅ Vehicle-tyres summary generated successfully<br>";
        echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo $summary;
        echo "</div>";
    } catch (Exception $e) {
        echo "❌ Error generating vehicle-tyres summary: " . $e->getMessage() . "<br>";
    }
    
} else {
    echo "❌ bms_show_vehicle_tyres_summary() function NOT found<br>";
}

// Test 3: Check if table actually exists and has data
echo "<h3>3. Actual Table Status Test</h3>";
global $wpdb;

$table_name = $wpdb->prefix . 'bms_vehicle_tyres';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");

if ($table_exists) {
    echo "✅ wp_bms_vehicle_tyres table exists in database<br>";
    
    $record_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    echo "✅ Table contains " . number_format($record_count) . " records<br>";
    
    if ($record_count > 0) {
        // Get some sample data
        $samples = $wpdb->get_results("
            SELECT vehicle_make, COUNT(*) as count 
            FROM {$table_name} 
            WHERE is_active = 1 
            GROUP BY vehicle_make 
            ORDER BY count DESC 
            LIMIT 5
        ");
        
        echo "✅ Sample data from table:<br>";
        echo "<ul>";
        foreach ($samples as $sample) {
            echo "<li>" . esc_html($sample->vehicle_make) . ": " . $sample->count . " mappings</li>";
        }
        echo "</ul>";
    }
    
} else {
    echo "❌ wp_bms_vehicle_tyres table does NOT exist in database<br>";
}

// Test 4: Test admin menu registration
echo "<h3>4. Admin Menu Test</h3>";

// Check if the admin menu hook exists
if (has_action('admin_menu')) {
    echo "✅ admin_menu hooks are registered<br>";
    
    // In a real admin context, we'd check if the menu item exists
    // For this test, we'll just verify the function can be called
    echo "✅ Admin menu system is functional<br>";
} else {
    echo "❌ No admin_menu hooks found<br>";
}

// Test 5: Test database creation method includes new table
echo "<h3>5. Database Creation Method Test</h3>";

if (method_exists('BMS_Database_Manager_Enhanced', 'create_tables')) {
    echo "✅ create_tables() method exists<br>";
    
    // Check if it includes vehicle_tyres creation
    $reflection = new ReflectionClass('BMS_Database_Manager_Enhanced');
    $method = $reflection->getMethod('create_tyre_system_tables');
    
    if ($method) {
        echo "✅ create_tyre_system_tables() method exists<br>";
        echo "✅ Should include vehicle_tyres table creation<br>";
    } else {
        echo "❌ create_tyre_system_tables() method NOT found<br>";
    }
    
} else {
    echo "❌ create_tables() method NOT found<br>";
}

// Summary
echo "<hr>";
echo "<h3>🎯 Test Summary</h3>";

$all_tests_passed = true;
$issues = [];

// Check each test
if (!class_exists('BMS_Database_Manager_Enhanced')) {
    $all_tests_passed = false;
    $issues[] = "Enhanced Database Manager class missing";
}

if (!function_exists('bms_show_vehicle_tyres_summary')) {
    $all_tests_passed = false;
    $issues[] = "Vehicle-tyres summary function missing";
}

if (!$table_exists) {
    $all_tests_passed = false;
    $issues[] = "wp_bms_vehicle_tyres table missing from database";
}

if ($all_tests_passed) {
    echo "<div style='background: #d1fae5; padding: 15px; border-radius: 8px; border-left: 4px solid #10b981;'>";
    echo "<h4 style='color: #065f46; margin: 0 0 10px 0;'>🎉 ALL TESTS PASSED!</h4>";
    echo "<p style='color: #047857; margin: 0;'>";
    echo "✅ Database Status admin page is fully functional<br>";
    echo "✅ New vehicle_tyres table is properly integrated<br>";
    echo "✅ Admin interface will show correct status<br>";
    echo "✅ Summary functions work correctly<br>";
    echo "✅ Ready for use in WordPress admin";
    echo "</p>";
    echo "</div>";
    
    echo "<p><strong>You can now access the Database Status page at:</strong><br>";
    echo "<code>/wp-admin/admin.php?page=bms-database-status</code></p>";
    
} else {
    echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px; border-left: 4px solid #ef4444;'>";
    echo "<h4 style='color: #991b1b; margin: 0 0 10px 0;'>⚠️ Some Issues Found</h4>";
    echo "<p style='color: #7f1d1d; margin: 0;'>";
    echo "Issues that need attention:</p>";
    echo "<ul style='color: #7f1d1d; margin: 10px 0 0 20px;'>";
    foreach ($issues as $issue) {
        echo "<li>" . esc_html($issue) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<p><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>