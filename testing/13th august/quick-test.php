<?php
/**
 * Quick Test - Check if Phase 2 tyre system is working
 */

// Include WordPress
require_once('../../../wp-config.php');

echo "<h1>🛞 Quick Phase 2 Status Check</h1>";

global $wpdb;

echo "<h2>Database Tables Status:</h2>";

// Check tyre tables
$tyres_table = $wpdb->prefix . 'bms_tyres';
$tyre_bookings_table = $wpdb->prefix . 'bms_tyre_bookings';

$tyres_exists = $wpdb->get_var("SHOW TABLES LIKE '$tyres_table'") == $tyres_table;
$bookings_exists = $wpdb->get_var("SHOW TABLES LIKE '$tyre_bookings_table'") == $tyre_bookings_table;

if ($tyres_exists) {
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $tyres_table");
    echo "✅ Tyres table: EXISTS ($count tyres loaded)<br>";
} else {
    echo "❌ Tyres table: MISSING<br>";
}

if ($bookings_exists) {
    echo "✅ Tyre bookings table: EXISTS<br>";
} else {
    echo "❌ Tyre bookings table: MISSING<br>";
}

echo "<h2>Plugin Classes Status:</h2>";

// Check if classes are loaded
$classes_to_check = [
    'BMS_Database_Manager',
    'Blue_Motors_Tyre_Service',
    'Blue_Motors_Southampton'];

foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        echo "✅ $class: LOADED<br>";
    } else {
        echo "❌ $class: NOT FOUND<br>";
    }
}

echo "<h2>Shortcodes Status:</h2>";

if (shortcode_exists('bms_tyre_search')) {
    echo "✅ [bms_tyre_search]: REGISTERED<br>";
} else {
    echo "❌ [bms_tyre_search]: NOT REGISTERED<br>";
}

if (shortcode_exists('blue_motors_booking')) {
    echo "✅ [blue_motors_booking]: REGISTERED<br>";
} else {
    echo "❌ [blue_motors_booking]: NOT REGISTERED<br>";
}

echo "<h2>🎯 Competitive Advantage Status:</h2>";

if ($tyres_exists && $count > 0) {
    echo "<div style='background: #d1fae5; padding: 15px; border-radius: 8px;'>";
    echo "<strong>🏆 COMPETITIVE ADVANTAGE ACTIVE!</strong><br>";
    echo "✅ Online tyre ordering ready (F1 customers must call)<br>";
    echo "✅ Tyre database populated with inventory<br>";
    echo "✅ Ready to capture industry leaders customers!<br>";
    echo "</div>";
} else {
    echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px;'>";
    echo "<strong>⚠️ Setup Required</strong><br>";
    echo "Need to create tyre database tables and populate inventory.<br>";
    echo "</div>";
}

echo "<p><small>Test run: " . date('Y-m-d H:i:s') . "</small></p>";
?>
