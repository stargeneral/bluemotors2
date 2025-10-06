<?php
// Database check script
define('ABSPATH', 'C:/Users/user/Local Sites/test/app/public/');
require_once ABSPATH . 'wp-config.php';

global $wpdb;

// Check if tyre tables exist
$tables = [
    'wp_bms_tyres',
    'wp_bms_tyre_bookings', 
    'wp_bms_vehicle_tyres'];

echo "🔍 Checking database tables...\n";
foreach ($tables as $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        echo "✅ $table exists with $count records\n";
    } else {
        echo "❌ $table NOT FOUND\n";
    }
}

echo "\n🔧 If tables are missing, run the tyre schema SQL to create them.\n";
