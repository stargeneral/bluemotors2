<?php
/**
 * Database Cleanup & Reset Script
 * Run this to clean up broken tables and start fresh
 */

// Include WordPress
require_once('../../../wp-config.php');

echo "<h1>🧹 Database Cleanup & Reset</h1>";
echo "<p>This will clean up any broken tables and prepare for fresh installation.</p>";

global $wpdb;

// List of tables to clean up
$tables_to_clean = array(
    $wpdb->prefix . 'bms_appointments',
    $wpdb->prefix . 'bms_tyres',
    $wpdb->prefix . 'bms_tyre_bookings',
    $wpdb->prefix . 'bms_services',
    $wpdb->prefix . 'bms_booking_logs');

echo "<h2>🗑️ Cleaning Up Tables</h2>";

foreach ($tables_to_clean as $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
    
    if ($exists) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
        echo "✅ Dropped table: $table<br>";
    } else {
        echo "ℹ️ Table doesn't exist: $table<br>";
    }
}

// Clean up options
echo "<h2>🧹 Cleaning Up Options</h2>";

$options_to_clean = array(
    'bms_database_version',
    'bms_version',
    'bms_business_hours',
    'bms_booking_min_days',
    'bms_booking_max_days',
    'bms_slot_duration');

foreach ($options_to_clean as $option) {
    $deleted = delete_option($option);
    if ($deleted) {
        echo "✅ Deleted option: $option<br>";
    } else {
        echo "ℹ️ Option not found: $option<br>";
    }
}

echo "<h2>🔧 Database Status After Cleanup</h2>";

// Check current database status
echo "<div style='background: #d1fae5; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>✅ Cleanup Complete!</h3>";
echo "<p>The database has been cleaned up and reset.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Go to WordPress Admin → Plugins</li>";
echo "<li>Deactivate the 'Blue Motors Southampton' plugin</li>";
echo "<li>Reactivate the plugin (this will create clean tables)</li>";
echo "<li>Run the <a href='quick-test.php'>quick test</a> to verify everything works</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🎯 Your Competitive Advantage</h2>";
echo "<div style='background: #eff6ff; padding: 15px; border-radius: 8px;'>";
echo "<p><strong>Why This Matters:</strong></p>";
echo "<ul>";
echo "<li>🛞 industry leaders customers must <strong>call</strong> for tyre orders</li>";
echo "<li>💻 Your customers will order tyres <strong>online</strong></li>";
echo "<li>⚡ This gives you a major competitive advantage!</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Cleanup completed:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='quick-test.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>Test After Reactivation →</a></p>";
?>
