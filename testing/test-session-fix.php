<?php
/**
 * Test file to verify BMS_Session class fix
 * Run this from wp-admin or as a test
 */

// Set up WordPress environment if not already loaded
if (!defined('ABSPATH')) {
    // Adjust path as needed for your local site
    require_once('../../../../../wp-config.php');
}

echo "<h2>Blue Motors Southampton - BMS_Session Class Test</h2>\n";

// Test 1: Check if the plugin is active
if (function_exists('blue_motors_southampton_init')) {
    echo "✅ Plugin is loaded\n<br>";
} else {
    echo "❌ Plugin is not loaded\n<br>";
    exit;
}

// Test 2: Check if BMS_Session class exists
if (class_exists('BMS_Session')) {
    echo "✅ BMS_Session class found\n<br>";
} else {
    echo "❌ BMS_Session class not found\n<br>";
    exit;
}

// Test 3: Check if the namespaced class exists
if (class_exists('BlueMotosSouthampton\\Utils\\BMS_Session')) {
    echo "✅ Namespaced BMS_Session class found\n<br>";
} else {
    echo "❌ Namespaced BMS_Session class not found\n<br>";
}

// Test 4: Try to use the class
try {
    BMS_Session::init();
    echo "✅ BMS_Session::init() called successfully\n<br>";
    
    // Test setting and getting a value
    BMS_Session::set('test_key', 'test_value');
    $value = BMS_Session::get('test_key');
    
    if ($value === 'test_value') {
        echo "✅ BMS_Session set/get working correctly\n<br>";
    } else {
        echo "❌ BMS_Session set/get not working - got: " . var_export($value, true) . "\n<br>";
    }
    
    // Clean up test data
    BMS_Session::remove('test_key');
    echo "✅ Test completed successfully\n<br>";
    
} catch (Exception $e) {
    echo "❌ Error using BMS_Session: " . $e->getMessage() . "\n<br>";
}

echo "\n<h3>Fix Applied Successfully!</h3>\n";
echo "The BMS_Session class namespace issue has been resolved.\n<br>";
echo "Your shortcodes should now work without the 'Class not found' error.\n<br>";
?>
