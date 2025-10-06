<?php
/**
 * Simple Tyre Service Test
 * Test the fixed tyre service functionality
 */

// Include WordPress
require_once '../../../wp-load.php';

echo "<h2>🛞 Tyre Service Fix Verification</h2>";
echo "<p>Testing the fixes for the vehicle lookup method issue...</p>";

echo "<h3>1. Class Loading Test</h3>";

try {
    // Test VehicleLookupEnhanced class
    if (class_exists('BlueMotosSouthampton\Services\VehicleLookupEnhanced')) {
        echo "✅ VehicleLookupEnhanced class loaded<br>";
        
        $vehicle_lookup = new \BlueMotosSouthampton\Services\VehicleLookupEnhanced();
        
        if (method_exists($vehicle_lookup, 'lookup')) {
            echo "✅ lookup() method exists<br>";
        } else {
            echo "❌ lookup() method NOT found<br>";
        }
        
        if (method_exists($vehicle_lookup, 'lookup_vehicle')) {
            echo "✅ lookup_vehicle() method exists<br>";
        } else {
            echo "❌ lookup_vehicle() method NOT found<br>";
        }
        
    } else {
        echo "❌ VehicleLookupEnhanced class NOT loaded<br>";
    }
    
    // Test TyreService class
    if (class_exists('BlueMotosSouthampton\Services\TyreService')) {
        echo "✅ TyreService class loaded<br>";
        
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        if (method_exists($tyre_service, 'search_by_registration')) {
            echo "✅ search_by_registration() method exists<br>";
        } else {
            echo "❌ search_by_registration() method NOT found<br>";
        }
        
    } else {
        echo "❌ TyreService class NOT loaded<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Exception during class testing: " . $e->getMessage() . "<br>";
}

echo "<h3>2. Method Call Test</h3>";

try {
    if (class_exists('BlueMotosSouthampton\Services\TyreService')) {
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        echo "Testing search_by_registration('TEST123')...<br>";
        
        $result = $tyre_service->search_by_registration('TEST123');
        
        if (is_wp_error($result)) {
            echo "✅ Method executed successfully (returned WP_Error as expected)<br>";
            echo "Error message: " . $result->get_error_message() . "<br>";
        } else {
            echo "✅ Method executed successfully (returned data)<br>";
            echo "Result type: " . gettype($result) . "<br>";
        }
        
    } else {
        echo "❌ Cannot test - TyreService class not available<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Exception during method call test: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}

echo "<h3>3. AJAX Handler Test</h3>";

try {
    // Simulate the AJAX call conditions
    $_POST['nonce'] = wp_create_nonce('bms_vehicle_lookup');
    $_POST['registration'] = 'VF19XKX';
    
    echo "Simulating AJAX call conditions...<br>";
    
    // Test if the AJAX handler function exists
    if (function_exists('bms_ajax_search_tyres_by_reg')) {
        echo "✅ bms_ajax_search_tyres_by_reg function exists<br>";
    } else {
        echo "❌ bms_ajax_search_tyres_by_reg function NOT found<br>";
    }
    
    // Check if AJAX actions are registered
    $actions = ['wp_ajax_bms_search_tyres_by_reg', 'wp_ajax_nopriv_bms_search_tyres_by_reg'];
    foreach ($actions as $action) {
        if (has_action($action)) {
            echo "✅ Action '{$action}' is registered<br>";
        } else {
            echo "❌ Action '{$action}' is NOT registered<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception during AJAX test: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Autoloader Test</h3>";

if (function_exists('bms_autoloader')) {
    echo "✅ Autoloader function exists<br>";
    
    // Check if autoloader is registered
    $autoloaders = spl_autoload_functions();
    $bms_registered = false;
    foreach ($autoloaders as $autoloader) {
        if (is_string($autoloader) && $autoloader === 'bms_autoloader') {
            $bms_registered = true;
            break;
        }
    }
    
    if ($bms_registered) {
        echo "✅ BMS autoloader is registered<br>";
    } else {
        echo "❌ BMS autoloader is NOT registered<br>";
    }
} else {
    echo "❌ Autoloader function NOT found<br>";
}

echo "<hr>";
echo "<h3>🎯 Conclusion</h3>";

echo "<p><strong>Fix Status:</strong> ";
if (class_exists('BlueMotosSouthampton\Services\VehicleLookupEnhanced') && 
    method_exists(new \BlueMotosSouthampton\Services\VehicleLookupEnhanced(), 'lookup_vehicle') &&
    class_exists('BlueMotosSouthampton\Services\TyreService')) {
    echo "<span style='color: green; font-weight: bold;'>✅ LIKELY FIXED</span></p>";
    echo "<p>The missing method 'lookup_vehicle()' has been added to the VehicleLookupEnhanced class. ";
    echo "This should resolve the fatal error you were experiencing.</p>";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ NEEDS ATTENTION</span></p>";
    echo "<p>Some components are still missing or not loading properly.</p>";
}

echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Try the tyre search functionality on your website frontend</li>";
echo "<li>Check error logs for any remaining issues</li>";
echo "<li>Test the AJAX registration search with a real registration number</li>";
echo "<li>If you see any errors, share them for further debugging</li>";
echo "</ul>";

echo "<p><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>