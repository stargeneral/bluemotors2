<?php
/**
 * Emergency Database Setup
 * Run this if plugin activation isn't creating tables
 */

// Include WordPress
require_once('../../../wp-config.php');

echo "<h1>🚨 Emergency Database Setup</h1>";

// Load the database manager directly
require_once('./includes/services/class-database-manager.php');

if (class_exists('BMS_Database_Manager')) {
    echo "<p>✅ Database Manager class loaded</p>";
    
    try {
        BMS_Database_Manager::create_tables();
        echo "<p>✅ Database tables created successfully!</p>";
        
        echo "<p><strong>Tables created:</strong></p>";
        echo "<ul>";
        echo "<li>bms_appointments - Service bookings</li>";
        echo "<li>bms_tyres - Tyre inventory (your F1 advantage!)</li>";
        echo "<li>bms_tyre_bookings - Tyre orders</li>";
        echo "</ul>";
        
        echo "<div style='background: #d1fae5; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>🎉 Success!</h3>";
        echo "<p>Your Phase 2 tyre system is now ready!</p>";
        echo "<p><strong>Competitive Advantage:</strong> You can now capture customers from industry leaders who are frustrated with phone-only tyre ordering!</p>";
        echo "</div>";
        
        echo "<p><a href='quick-test.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>Run Quick Test →</a></p>";
        
    } catch (Exception $e) {
        echo "<p>❌ Error creating tables: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Database Manager class not found!</p>";
    echo "<p>Check that the file exists: includes/services/class-database-manager.php</p>";
}

echo "<p><small>Emergency setup completed: " . date('Y-m-d H:i:s') . "</small></p>";
?>
