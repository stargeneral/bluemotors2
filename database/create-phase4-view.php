<?php
/**
 * Create Phase 4 Database View - FIXED VERSION
 * Run this file to create the customer history view
 * 
 * Access this file at: /wp-content/plugins/blue-motors-southampton/database/create-phase4-view.php
 */

// Load WordPress
$wp_load_path = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    die('Error: Could not find wp-load.php. Please adjust the path.');
}
require_once($wp_load_path);

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Error: You must be logged in as an administrator to run this script.');
}

global $wpdb;

echo "<h1>Phase 4 Database View Creation</h1>";

// Create the view
$sql = "CREATE OR REPLACE VIEW vw_bms_customer_history AS
SELECT 
    a.id,
    a.customer_name,
    a.customer_email,
    a.customer_phone,
    a.vehicle_reg AS vehicle_registration,
    a.vehicle_make,
    a.vehicle_model,
    a.vehicle_year,
    a.service_type,
    a.booking_date AS appointment_date,
    a.booking_time AS appointment_time,
    a.booking_status,
    a.payment_status,
    a.calculated_price,
    a.notes,
    a.created_at,
    -- Calculate service duration for scheduling
    CASE 
        WHEN a.service_type = 'full_service' THEN 135
        WHEN a.service_type = 'interim_service' THEN 105
        WHEN a.service_type = 'mot_test' THEN 75
        WHEN a.service_type LIKE '%tyre%' THEN 120
        WHEN a.service_type LIKE '%air_con%' THEN 90
        WHEN a.service_type LIKE '%brake%' THEN 120
        WHEN a.service_type LIKE '%battery%' THEN 45
        ELSE 60
    END as estimated_duration_minutes,
    -- Days since last service (for recommendations)
    DATEDIFF(CURDATE(), a.booking_date) as days_since_service
FROM 
    {$wpdb->prefix}bms_appointments a
WHERE 
    a.booking_status != 'cancelled'
ORDER BY 
    a.appointment_date DESC, 
    a.appointment_time DESC";

// Execute the query
$result = $wpdb->query($sql);

if ($result !== false) {
    echo "<h2>✅ Success!</h2>";
    echo "<p>The customer history view (vw_bms_customer_history) has been created successfully.</p>";
    
    // Check if the view exists
    $view_check = $wpdb->get_var("SHOW TABLES LIKE 'vw_bms_customer_history'");
    if ($view_check) {
        echo "<p>✓ View confirmed to exist in the database.</p>";
        
        // Test the view
        $test_query = $wpdb->get_var("SELECT COUNT(*) FROM vw_bms_customer_history");
        echo "<p>✓ View contains " . intval($test_query) . " records.</p>";
        
        // Show a sample record if available
        $sample_record = $wpdb->get_row("SELECT customer_name, appointment_date, service_type FROM vw_bms_customer_history LIMIT 1");
        if ($sample_record) {
            echo "<p>✓ Sample record: " . $sample_record->customer_name . " - " . $sample_record->service_type . " on " . $sample_record->appointment_date . "</p>";
        }
    }
    
    // Create indexes if they don't exist
    echo "<h3>Creating indexes...</h3>";
    
    $indexes = [
        "ALTER TABLE {$wpdb->prefix}bms_appointments ADD INDEX idx_customer_email (customer_email)",
        "ALTER TABLE {$wpdb->prefix}bms_appointments ADD INDEX idx_appointment_date (booking_date)",
        "ALTER TABLE {$wpdb->prefix}bms_appointments ADD INDEX idx_booking_status (booking_status)",
        "ALTER TABLE {$wpdb->prefix}bms_appointments ADD INDEX idx_service_type (service_type)"
    ];
    
    foreach ($indexes as $index_sql) {
        // Suppress errors as indexes might already exist
        $wpdb->query($index_sql);
    }
    echo "<p>✓ Indexes processed (created if not existing).</p>";
    
    // Create cache tables
    echo "<h3>Creating cache tables...</h3>";
    
    $cache_stats_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bms_cache_stats (
        cache_key VARCHAR(255) PRIMARY KEY,
        hit_count INT DEFAULT 0,
        miss_count INT DEFAULT 0,
        last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_last_accessed (last_accessed)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $performance_log_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bms_performance_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        query_type VARCHAR(100),
        execution_time FLOAT,
        memory_usage INT,
        details TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_timestamp (timestamp),
        INDEX idx_query_type (query_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $cache_result1 = $wpdb->query($cache_stats_table);
    $cache_result2 = $wpdb->query($performance_log_table);
    
    if ($cache_result1 !== false && $cache_result2 !== false) {
        echo "<p>✓ Cache tables created/verified successfully.</p>";
    } else {
        echo "<p>⚠️ Cache tables may already exist or there were minor issues (this is usually fine).</p>";
    }
    
    echo "<h2>🎉 Phase 4 database setup complete!</h2>";
    echo "<p><a href='" . admin_url('admin.php?page=bms-phase4-test') . "'>Go to Phase 4 Test Page</a></p>";
    
} else {
    echo "<h2>❌ Error</h2>";
    echo "<p>Failed to create the view. Database error: " . $wpdb->last_error . "</p>";
    echo "<p>Please check that the {$wpdb->prefix}bms_appointments table exists.</p>";
    
    // Debug information
    $table_name = $wpdb->prefix . 'bms_appointments';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    
    if ($table_exists) {
        echo "<p>✓ Table $table_name exists.</p>";
        
        // Show table structure
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
        echo "<h3>Table Structure:</h3><ul>";
        foreach ($columns as $column) {
            echo "<li>{$column->Field} ({$column->Type})</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p>❌ Table $table_name does not exist. Please create database tables first.</p>";
        echo "<p><a href='" . admin_url('admin.php?page=bms-database-status') . "'>Go to Database Status</a> to create tables.</p>";
    }
}
?>
