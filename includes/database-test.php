<?php
/**
 * Database Testing Script for Blue Motors Southampton
 * Run this to test database creation and diagnose issues
 */

// Only run if we're in WordPress admin or if WP_DEBUG is true
if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

// Include the fixed database manager
require_once plugin_dir_path(__FILE__) . 'class-database-manager-fixed.php';

function bms_test_database() {
    global $wpdb;
    
    echo "<h2>🔧 Blue Motors Southampton - Database Test</h2>";
    
    // Test 1: Check current state
    echo "<h3>📊 Current Database State</h3>";
    $status = BMS_Database_Manager::get_status();
    echo "<p><strong>Status:</strong> " . $status['status'] . "</p>";
    echo "<p><strong>Message:</strong> " . $status['message'] . "</p>";
    
    // Test 2: List existing tables
    echo "<h3>📋 Existing Tables</h3>";
    $tables = array(
        'bms_appointments',
        'bms_booking_logs', 
        'bms_services',
        'bms_tyres',
        'bms_tyre_bookings');
    
    foreach ($tables as $table) {
        $full_table = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") == $full_table;
        $status_icon = $exists ? "✅" : "❌";
        $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $full_table") : 0;
        echo "<p>$status_icon <strong>$full_table</strong> - " . ($exists ? "EXISTS ($count records)" : "MISSING") . "</p>";
    }
    
    // Test 3: Drop tables if requested
    if (isset($_GET['drop_tables']) && $_GET['drop_tables'] == '1') {
        echo "<h3>🗑️ Dropping Tables</h3>";
        $result = BMS_Database_Manager::drop_tables();
        echo $result ? "<p>✅ All tables dropped successfully</p>" : "<p>❌ Error dropping tables</p>";
    }
    
    // Test 4: Create tables if requested
    if (isset($_GET['create_tables']) && $_GET['create_tables'] == '1') {
        echo "<h3>🔨 Creating Tables</h3>";
        
        // Turn on error reporting temporarily
        $wpdb->show_errors(true);
        
        $result = BMS_Database_Manager::create_tables();
        
        if ($result) {
            echo "<p>✅ Tables created successfully</p>";
            
            // Test sample data creation
            echo "<h4>📝 Creating Sample Data</h4>";
            $sample_count = BMS_Database_Manager::create_sample_data();
            echo "<p>✅ Created $sample_count sample bookings</p>";
            
        } else {
            echo "<p>❌ Error creating tables</p>";
        }
        
        // Show any SQL errors
        if ($wpdb->last_error) {
            echo "<p><strong>Last SQL Error:</strong> " . $wpdb->last_error . "</p>";
        }
    }
    
    // Test 5: Action buttons
    echo "<h3>🎮 Actions</h3>";
    echo "<p><a href='?page=bms_test_db&create_tables=1' class='button button-primary'>Create Tables</a></p>";
    echo "<p><a href='?page=bms_test_db&drop_tables=1' class='button button-secondary' onclick='return confirm(\"Are you sure you want to drop all tables?\")'>Drop All Tables</a></p>";
    echo "<p><a href='?page=bms_test_db' class='button'>Refresh Status</a></p>";
    
    // Test 6: SQL queries for debugging
    echo "<h3>🔍 Debug Information</h3>";
    echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>";
    echo "<p><strong>MySQL Version:</strong> " . $wpdb->db_version() . "</p>";
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Table Prefix:</strong> " . $wpdb->prefix . "</p>";
    echo "<p><strong>Charset Collate:</strong> " . $wpdb->get_charset_collate() . "</p>";
}

// Add admin menu for testing
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'BMS Database Test',
        'BMS Database Test', 
        'manage_options',
        'bms_test_db',
        'bms_test_database'
    );
});
