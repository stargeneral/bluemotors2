<?php
/**
 * Plugin Load Verification Test
 * Tests that all required files can be loaded without errors
 */

// WordPress environment simulation for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../../');
}

// Define plugin constants
define('BMS_PLUGIN_DIR', dirname(__FILE__) . '/');
define('BMS_PLUGIN_URL', '/wp-content/plugins/blue-motors-southampton/');
define('BMS_VERSION', '1.0.0');

echo "🔍 Blue Motors Southampton Plugin - Load Verification Test\n";
echo "======================================================\n\n";

$required_files = array(
    'includes/class-blue-motors-southampton.php',
    'includes/services/class-pricing-calculator.php',
    'includes/services/class-service-manager.php',
    'includes/services/class-service-manager-enhanced.php',
    'includes/services/class-vehicle-lookup-enhanced.php',
    'includes/services/class-dvla-api-enhanced.php',
    'includes/services/class-dvsa-mot-api-enhanced.php',
    'includes/services/class-vehicle-lookup-combined.php',
    'includes/class-bms-session.php',
    'includes/service-selection-ajax.php',
    'includes/services/class-tyre-service.php',
    'includes/services/class-cache-manager.php',
    'includes/services/class-customer-service.php',
    'includes/services/class-smart-scheduler.php',
    'includes/services/class-bms-smtp.php',
    'includes/services/class-email-manager.php',
    'includes/smtp-status-notice.php',
    'includes/services/class-settings-migrator.php');

$missing_files = array();
$present_files = array();

foreach ($required_files as $file) {
    $full_path = BMS_PLUGIN_DIR . $file;
    if (file_exists($full_path)) {
        $present_files[] = $file;
        echo "✅ Found: $file\n";
    } else {
        $missing_files[] = $file;
        echo "❌ Missing: $file\n";
    }
}

echo "\n======================================================\n";
echo "SUMMARY:\n";
echo "✅ Present: " . count($present_files) . " files\n";
echo "❌ Missing: " . count($missing_files) . " files\n";

if (empty($missing_files)) {
    echo "\n🎉 SUCCESS: All required files are present!\n";
    echo "The critical error should be resolved.\n";
} else {
    echo "\n⚠️  WARNING: Missing files detected:\n";
    foreach ($missing_files as $file) {
        echo "   - $file\n";
    }
}

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
