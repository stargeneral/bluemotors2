<?php
/**
 * Emergency Database Fix Script for Blue Motors Southampton
 * 
 * This script fixes the duplicate index error and ensures clean database setup
 * 
 * Run this from WordPress admin or via command line to fix the activation error
 */

// Ensure WordPress is loaded
if (!defined('ABSPATH')) {
    // If running from command line, try to load WordPress
    $wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
    } else {
        die('WordPress not found. Run this from WordPress admin or ensure wp-load.php is accessible.');
    }
}

class BMS_Emergency_Database_Fix {
    
    public static function run() {
        global $wpdb;
        
        echo "<h2>🔧 Blue Motors Southampton - Emergency Database Fix</h2>\n";
        echo "<p>Fixing duplicate index issue...</p>\n";
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        // Step 1: Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            echo "<p>✅ Table doesn't exist yet - will create clean table.</p>\n";
            return self::create_clean_tables();
        }
        
        echo "<p>📋 Table exists - checking for problematic indexes...</p>\n";
        
        // Step 2: Get current indexes
        $indexes = $wpdb->get_results("SHOW INDEX FROM $table_name", ARRAY_A);
        $existing_indexes = array();
        
        foreach ($indexes as $index) {
            if ($index['Key_name'] !== 'PRIMARY') {
                $existing_indexes[] = $index['Key_name'];
            }
        }
        
        echo "<p>Current indexes: " . implode(', ', $existing_indexes) . "</p>\n";
        
        // Step 3: Remove problematic duplicate indexes
        $problematic_indexes = array('idx_date', 'idx_time', 'idx_reference', 'idx_status');
        $removed_count = 0;
        
        foreach ($problematic_indexes as $index_name) {
            if (in_array($index_name, $existing_indexes)) {
                $result = $wpdb->query("ALTER TABLE $table_name DROP INDEX $index_name");
                if ($result !== false) {
                    echo "<p>✅ Removed duplicate index: $index_name</p>\n";
                    $removed_count++;
                } else {
                    echo "<p>⚠️ Failed to remove index: $index_name</p>\n";
                }
            }
        }
        
        if ($removed_count > 0) {
            echo "<p>🎉 Successfully removed $removed_count problematic indexes!</p>\n";
        } else {
            echo "<p>✅ No problematic indexes found to remove.</p>\n";
        }
        
        // Step 4: Add correct indexes with unique names
        self::add_correct_indexes();
        
        // Step 5: Update database version
        update_option('bms_database_version', '1.3.1-fixed');
        
        echo "<p>✅ Database fix completed successfully!</p>\n";
        echo "<p>🚀 You can now reactivate the plugin without errors.</p>\n";
        
        return true;
    }
    
    private static function add_correct_indexes() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        // Define the correct indexes we want
        $desired_indexes = array(
            'idx_booking_date' => 'booking_date',
            'idx_booking_time' => 'booking_time', 
            'idx_booking_status' => 'booking_status',
            'idx_customer_email' => 'customer_email',
            'idx_payment_status' => 'payment_status');
        
        // Get current indexes
        $current_indexes = array();
        $indexes = $wpdb->get_results("SHOW INDEX FROM $table_name", ARRAY_A);
        
        foreach ($indexes as $index) {
            if ($index['Key_name'] !== 'PRIMARY') {
                $current_indexes[] = $index['Key_name'];
            }
        }
        
        // Add missing indexes
        $added_count = 0;
        foreach ($desired_indexes as $index_name => $column) {
            if (!in_array($index_name, $current_indexes)) {
                $result = $wpdb->query("ALTER TABLE $table_name ADD INDEX $index_name ($column)");
                if ($result !== false) {
                    echo "<p>✅ Added index: $index_name on $column</p>\n";
                    $added_count++;
                } else {
                    echo "<p>⚠️ Failed to add index: $index_name</p>\n";
                }
            }
        }
        
        if ($added_count > 0) {
            echo "<p>🎉 Added $added_count new indexes successfully!</p>\n";
        } else {
            echo "<p>✅ All required indexes already exist.</p>\n";
        }
    }
    
    private static function create_clean_tables() {
        global $wpdb;
        
        echo "<p>🚀 Creating clean database tables...</p>\n";
        
        // Load the fixed database manager
        require_once __DIR__ . '/includes/services/class-database-manager-fixed.php';
        
        if (class_exists('BMS_Database_Manager_Fixed')) {
            $result = BMS_Database_Manager_Fixed::create_tables();
            
            if ($result) {
                echo "<p>✅ Clean database tables created successfully!</p>\n";
                echo "<p>🎉 Plugin is ready to use!</p>\n";
            } else {
                echo "<p>❌ Failed to create database tables.</p>\n";
            }
            
            return $result;
        } else {
            echo "<p>❌ Fixed database manager not found.</p>\n";
            return false;
        }
    }
}

// Run the fix if accessed directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    echo "<!DOCTYPE html>\n<html>\n<head>\n<title>BMS Database Fix</title>\n";
    echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;line-height:1.6}</style>\n";
    echo "</head>\n<body>\n";
    
    BMS_Emergency_Database_Fix::run();
    
    echo "<hr>\n";
    echo "<p><strong>Next Steps:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Go to your WordPress admin → Plugins</li>\n";
    echo "<li>Deactivate Blue Motors Southampton plugin</li>\n";
    echo "<li>Reactivate the plugin</li>\n";
    echo "<li>Check that activation completes without errors</li>\n";
    echo "</ol>\n";
    echo "<p><em>If you still get errors, contact support with the error details.</em></p>\n";
    
    echo "</body>\n</html>\n";
}
