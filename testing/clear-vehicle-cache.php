<?php
/**
 * Clear Vehicle Cache Utility
 * Clears all cached vehicle data to ensure fresh API lookups
 */

// WordPress environment setup
if (!defined('ABSPATH')) {
    // Try to find WordPress
    $wp_paths = [
        __DIR__ . '/../../../../wp-config.php',
        __DIR__ . '/../../../wp-config.php',
        __DIR__ . '/../../wp-config.php',
        __DIR__ . '/../wp-config.php'
    ];
    
    foreach ($wp_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
    
    if (!defined('ABSPATH')) {
        die('WordPress not found. Please run this from your WordPress installation.');
    }
}

echo "<h1>🧹 Vehicle Cache Clearing Utility</h1>";

// Registrations to clear
$registrations = ['H411DAR', 'WM65VJE', 'VF19XKX'];

echo "<h2>Clearing Cache for Test Registrations</h2>";

foreach ($registrations as $registration) {
    echo "<h3>Clearing cache for: {$registration}</h3>";
    
    // Clear WordPress transients
    $cache_keys = [
        'bm_dvla_enhanced_' . md5(strtoupper(preg_replace('/[^A-Z0-9]/', '', $registration))),
        'bm_dvsa_enhanced_' . md5(strtoupper(preg_replace('/[^A-Z0-9]/', '', $registration))),
        'bm_vehicle_combined_' . md5(strtoupper(preg_replace('/[^A-Z0-9]/', '', $registration)))
    ];
    
    foreach ($cache_keys as $key) {
        $deleted = delete_transient($key);
        echo "- Cache key '{$key}': " . ($deleted ? "✅ Cleared" : "ℹ️ Not found") . "<br>";
    }
    
    // Clear any object cache if available
    if (function_exists('wp_cache_delete')) {
        foreach ($cache_keys as $key) {
            wp_cache_delete($key, 'bms_vehicle_lookup');
        }
        echo "- Object cache cleared<br>";
    }
    
    echo "<br>";
}

echo "<h2>🔍 Checking for Remaining Cache</h2>";

global $wpdb;

// Check for any remaining transients
$transients = $wpdb->get_results("
    SELECT option_name, option_value 
    FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_bm_%' 
    OR option_name LIKE '_transient_timeout_bm_%'
    ORDER BY option_name
");

if (empty($transients)) {
    echo "✅ No Blue Motors transients found in database<br>";
} else {
    echo "Found " . count($transients) . " Blue Motors transients:<br>";
    foreach ($transients as $transient) {
        echo "- {$transient->option_name}<br>";
        
        // Delete old transients
        if (strpos($transient->option_name, '_transient_bm_') === 0) {
            $key = str_replace('_transient_', '', $transient->option_name);
            delete_transient($key);
            echo "&nbsp;&nbsp;→ Deleted<br>";
        }
    }
}

echo "<h2>🎯 Manual Cache Clear Commands</h2>";
echo "<p>If you need to clear cache manually, run these in your WordPress admin or via WP-CLI:</p>";
echo "<pre>";
foreach ($registrations as $registration) {
    $clean_reg = strtoupper(preg_replace('/[^A-Z0-9]/', '', $registration));
    echo "delete_transient('bm_dvla_enhanced_" . md5($clean_reg) . "');\n";
    echo "delete_transient('bm_dvsa_enhanced_" . md5($clean_reg) . "');\n";
}
echo "</pre>";

echo "<h2>✅ Cache Clearing Complete</h2>";
echo "<p>All vehicle cache data has been cleared. Next vehicle lookups will fetch fresh data from the APIs.</p>";

echo "<h3>🧪 Test the Fix</h3>";
echo "<ol>";
echo "<li>Go to your booking page</li>";
echo "<li>Enter registration: <strong>VF19XKX</strong></li>";
echo "<li>Click 'Look Up Vehicle'</li>";
echo "<li>Verify it shows: <strong>HYUNDAI IONIQ, 1580cc, Hybrid</strong></li>";
echo "</ol>";
?>
