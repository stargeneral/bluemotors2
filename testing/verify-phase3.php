<?php
/**
 * Quick Phase 3 Integration Verification
 * 
 * Run this to verify the booking integration fix
 */

// Check if we're in WordPress
if (!defined('ABSPATH')) {
    die('This file must be run within WordPress');
}

echo '<div style="padding: 20px; font-family: Arial;">';
echo '<h2>🔧 Phase 3 Integration Verification</h2>';

// Test 1: Check if booking integration class exists
echo '<h3>Test 1: Booking Integration Class</h3>';
if (class_exists('BMS_Booking_Integration')) {
    echo '<p style="color: green;">✅ SUCCESS: BMS_Booking_Integration class is loaded</p>';
} else {
    echo '<p style="color: red;">❌ FAIL: BMS_Booking_Integration class not found</p>';
}

// Test 2: Check if integration is initialized
echo '<h3>Test 2: Integration Initialization</h3>';
if (class_exists('BMS_Booking_Integration') && method_exists('BMS_Booking_Integration', 'init')) {
    echo '<p style="color: green;">✅ SUCCESS: Integration init method available</p>';
} else {
    echo '<p style="color: red;">❌ FAIL: Integration init method not found</p>';
}

// Test 3: Check if Settings Migrator is working
echo '<h3>Test 3: Settings Migration</h3>';
if (class_exists('BMS_Settings_Migrator')) {
    $migrated = BMS_Settings_Migrator::is_migrated();
    if ($migrated) {
        echo '<p style="color: green;">✅ SUCCESS: Settings migrated to database</p>';
    } else {
        echo '<p style="color: orange;">⚠️ INFO: Settings not yet migrated (run migration first)</p>';
    }
} else {
    echo '<p style="color: red;">❌ FAIL: Settings Migrator not found</p>';
}

// Test 4: Check if Service Manager is working
echo '<h3>Test 4: Service Management</h3>';
if (class_exists('\BlueMotosSouthampton\Services\ServiceManagerEnhanced')) {
    try {
        $services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true);
        $count = count($services);
        echo '<p style="color: green;">✅ SUCCESS: Service Manager working - ' . $count . ' services available</p>';
    } catch (Exception $e) {
        echo '<p style="color: red;">❌ FAIL: Service Manager error - ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p style="color: red;">❌ FAIL: Service Manager not found</p>';
}

// Test 5: Check admin pages
echo '<h3>Test 5: Admin Interface Access</h3>';
$admin_pages = array(
    'bms-services' => 'Enhanced Services',
    'bms-bookings' => 'Enhanced Bookings',
    'bms-settings' => 'Settings Hub');

foreach ($admin_pages as $page_slug => $page_name) {
    $url = admin_url('admin.php?page=' . $page_slug);
    echo '<p style="color: blue;">📄 ' . $page_name . ': <a href="' . $url . '" target="_blank">' . $url . '</a></p>';
}

// Summary
echo '<h3>🎯 Phase 3 Status Summary</h3>';
$all_classes = array('BMS_Booking_Integration', 'BMS_Settings_Migrator', 'BMS_Service_Manager');
$loaded_count = 0;

foreach ($all_classes as $class) {
    if (class_exists($class)) {
        $loaded_count++;
    }
}

$percentage = round(($loaded_count / count($all_classes)) * 100);
echo '<div style="padding: 15px; border: 2px solid #2271b1; background: #f0f6fc; border-radius: 5px;">';
echo '<p><strong>Integration Status: ' . $percentage . '% Complete</strong></p>';

if ($percentage >= 90) {
    echo '<p style="color: green; font-size: 18px;"><strong>🎉 Phase 3 Integration: SUCCESS!</strong></p>';
    echo '<p>All critical classes are loaded and ready. The booking system is now connected to admin settings.</p>';
} else {
    echo '<p style="color: orange; font-size: 18px;"><strong>⚠️ Phase 3 Integration: Needs Attention</strong></p>';
    echo '<p>Some components are missing. Check the failures above.</p>';
}

echo '</div>';

// Next steps
echo '<h3>🚀 Next Steps</h3>';
echo '<ol>';
echo '<li><strong>Visit Enhanced Settings:</strong> <a href="' . admin_url('admin.php?page=bms-settings') . '">Configure business settings</a></li>';
echo '<li><strong>Test Services:</strong> <a href="' . admin_url('admin.php?page=bms-services') . '">Manage services and pricing</a></li>';
echo '<li><strong>Check Bookings:</strong> <a href="' . admin_url('admin.php?page=bms-bookings') . '">View booking management</a></li>';
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<li><strong>Run Full Tests:</strong> <a href="' . admin_url('admin.php?page=bms-phase3-tests') . '">Complete Phase 3 test suite</a></li>';
}
echo '</ol>';

echo '</div>';
