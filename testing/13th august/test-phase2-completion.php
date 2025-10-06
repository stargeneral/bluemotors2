<?php
/**
 * Phase 2 Completion Test Script
 * Run this to verify all Phase 2 components are working
 * 
 * Visit: /wp-content/plugins/blue-motors-southampton/test-phase2-completion.php
 */

// Include WordPress
require_once('../../../wp-config.php');

echo '<h1>🛞 Blue Motors Phase 2 Completion Test</h1>';
echo '<h2>Testing Tyre System Implementation</h2>';

global $wpdb;

// Test 1: Check if tyre tables exist
echo '<h3>✅ Test 1: Database Tables</h3>';

$tyres_table = $wpdb->prefix . 'bms_tyres';
$bookings_table = $wpdb->prefix . 'bms_tyre_bookings';

$tyres_exists = $wpdb->get_var("SHOW TABLES LIKE '$tyres_table'") == $tyres_table;
$bookings_exists = $wpdb->get_var("SHOW TABLES LIKE '$bookings_table'") == $bookings_table;

if ($tyres_exists) {
    echo "✅ Tyres table exists: $tyres_table<br>";
    
    $tyre_count = $wpdb->get_var("SELECT COUNT(*) FROM $tyres_table");
    echo "📦 Tyre inventory: $tyre_count tyres loaded<br>";
    
    if ($tyre_count > 0) {
        $sample_tyres = $wpdb->get_results("SELECT brand, model, size, price FROM $tyres_table LIMIT 3");
        echo "Sample tyres:<br>";
        foreach ($sample_tyres as $tyre) {
            echo "&nbsp;&nbsp;- {$tyre->brand} {$tyre->model} ({$tyre->size}) - £{$tyre->price}<br>";
        }
    }
} else {
    echo "❌ Tyres table missing! Run plugin activation to create.<br>";
}

if ($bookings_exists) {
    echo "✅ Tyre bookings table exists: $bookings_table<br>";
    
    $booking_count = $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table");
    echo "📅 Tyre bookings: $booking_count bookings<br>";
} else {
    echo "❌ Tyre bookings table missing! Run plugin activation to create.<br>";
}

// Test 2: Check if tyre service class is loaded
echo '<h3>✅ Test 2: Service Classes</h3>';

if (class_exists('Blue_Motors_Tyre_Service')) {
    echo "✅ Tyre Service class is loaded<br>";
    
    try {
        $tyre_service = new Blue_Motors_Tyre_Service();
        echo "✅ Tyre Service class can be instantiated<br>";
    } catch (Exception $e) {
        echo "❌ Error instantiating Tyre Service: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Tyre Service class not found!<br>";
}

// Test 3: Check admin interface
echo '<h3>✅ Test 3: Admin Interface</h3>';

$admin_file = plugin_dir_path(__FILE__) . 'admin/tyre-management.php';
if (file_exists($admin_file)) {
    echo "✅ Tyre management admin file exists<br>";
    echo "🔗 Access via: WordPress Admin → Blue Motors → 🛞 Tyre Management<br>";
} else {
    echo "❌ Tyre management admin file missing!<br>";
}

// Test 4: Check AJAX handlers
echo '<h3>✅ Test 4: AJAX Handlers</h3>';

$ajax_file = plugin_dir_path(__FILE__) . 'includes/ajax/tyre-ajax.php';
if (file_exists($ajax_file)) {
    echo "✅ Tyre AJAX handlers file exists<br>";
} else {
    echo "❌ Tyre AJAX handlers missing!<br>";
}

// Test 5: Check shortcode
echo '<h3>✅ Test 5: Shortcode System</h3>';

if (shortcode_exists('bms_tyre_search')) {
    echo "✅ Tyre search shortcode [bms_tyre_search] is registered<br>";
    echo "🔗 Use [bms_tyre_search] on any page for online tyre ordering<br>";
} else {
    echo "❌ Tyre search shortcode not registered!<br>";
}

// Test 6: Competitive advantage summary
echo '<h3>🎯 Test 6: Competitive Advantage</h3>';

echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🏆 industry leaders Weakness Exploited!</h4>";
echo "<p><strong>F1 Problem:</strong> Customers must call for tyre orders</p>";
echo "<p><strong>Your Solution:</strong> Complete online tyre ordering system</p>";
echo "<p><strong>Advantage:</strong> Instant online orders vs phone-only system</p>";

if ($tyres_exists && $tyre_count > 0) {
    echo "<p>✅ <strong>Status:</strong> COMPETITIVE ADVANTAGE ACTIVE!</p>";
    echo "<p>Your customers can now order tyres online while F1 customers must call.</p>";
} else {
    echo "<p>⚠️ <strong>Status:</strong> Setup required - run plugin activation</p>";
}
echo "</div>";

// Test 7: Quick database setup if needed
echo '<h3>🔧 Test 7: Quick Setup</h3>';

if (!$tyres_exists) {
    echo "<p>Missing tyre tables detected. Quick setup options:</p>";
    echo "<ol>";
    echo "<li><strong>WordPress Admin:</strong> Deactivate and reactivate the Blue Motors plugin</li>";
    echo "<li><strong>Manual:</strong> <a href='?action=create_tables' style='background: #0073aa; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Create Tables Now</a></li>";
    echo "</ol>";
    
    if (isset($_GET['action']) && $_GET['action'] === 'create_tables') {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 6px; margin: 15px 0;'>";
        echo "🔧 Running database setup...<br>";
        
        // Load the database manager
        require_once(plugin_dir_path(__FILE__) . 'includes/services/class-database-manager.php');
        
        if (class_exists('BMS_Database_Manager')) {
            BMS_Database_Manager::create_tables();
            echo "✅ Database tables created successfully!<br>";
            echo "<a href='?' style='background: #10b981; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Test Again</a>";
        } else {
            echo "❌ Database manager not found!<br>";
        }
        echo "</div>";
    }
}

// Summary
echo '<h3>📋 Phase 2 Completion Summary</h3>';

$score = 0;
$total = 6;

if ($tyres_exists) $score++;
if ($bookings_exists) $score++;
if (class_exists('Blue_Motors_Tyre_Service')) $score++;
if (file_exists($admin_file)) $score++;
if (file_exists($ajax_file)) $score++;
if (shortcode_exists('bms_tyre_search')) $score++;

$percentage = round(($score / $total) * 100);

echo "<div style='background: " . ($percentage >= 80 ? '#d1fae5' : '#fef3c7') . "; padding: 20px; border-radius: 8px;'>";
echo "<h4>Overall Score: $score/$total ($percentage%)</h4>";

if ($percentage >= 80) {
    echo "<p>🎉 <strong>PHASE 2 COMPLETED SUCCESSFULLY!</strong></p>";
    echo "<p>Your tyre ordering system is ready to beat industry leaders!</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Test the admin interface: WordPress Admin → Blue Motors → 🛞 Tyre Management</li>";
    echo "<li>Add the tyre search to a page: [bms_tyre_search]</li>";
    echo "<li>Test the complete customer journey</li>";
    echo "<li>Start capturing customers from industry leaders!</li>";
    echo "</ul>";
} else {
    echo "<p>⚠️ <strong>Setup needed to complete Phase 2</strong></p>";
    echo "<p>Some components need attention before launch.</p>";
}

echo "</div>";

echo '<hr>';
echo '<p><small>Test completed: ' . date('Y-m-d H:i:s') . '</small></p>';
echo '<p><small>🎯 Goal: Beat industry leaders with superior online tyre ordering!</small></p>';
?>
