<?php
/**
 * Test Vehicle Lookup Fix
 * Verify that real API data is being returned instead of mock data
 */

// WordPress environment
require_once('../../../wp-config.php');

// Load plugin files
require_once('config/constants.php');
require_once('includes/class-bms-session.php');
require_once('includes/services/class-dvla-api-enhanced.php');
require_once('includes/services/class-dvsa-mot-api-enhanced.php');
require_once('includes/services/class-vehicle-lookup-combined.php');
require_once('includes/services/class-pricing-calculator.php');

echo "<h1>🔧 Vehicle Lookup Fix Test</h1>";
echo "<p>Testing the fixes applied to resolve mock data issues...</p>";

// Test 1: Check API Key Configuration
echo "<h2>Test 1: API Key Configuration</h2>";
$dvla_key = defined('BM_DVLA_API_KEY') ? BM_DVLA_API_KEY : 'Not defined';
echo "<p><strong>DVLA API Key:</strong> " . (strlen($dvla_key) > 20 ? substr($dvla_key, 0, 10) . '...' . substr($dvla_key, -5) : $dvla_key) . "</p>";

if (strlen($dvla_key) > 20 && $dvla_key !== 'keycovered-for-security') {
    echo "<p>✅ <strong>PASS:</strong> Valid DVLA API key configured</p>";
} else {
    echo "<p>❌ <strong>FAIL:</strong> Invalid or placeholder DVLA API key</p>";
}

// Test 2: Test Session Management
echo "<h2>Test 2: Session Management</h2>";
try {
    BlueMotosSouthampton\Utils\BMS_Session::set('test_key', 'test_value');
    $retrieved = BlueMotosSouthampton\Utils\BMS_Session::get('test_key');
    
    if ($retrieved === 'test_value') {
        echo "<p>✅ <strong>PASS:</strong> Session management working correctly</p>";
    } else {
        echo "<p>❌ <strong>FAIL:</strong> Session management not working</p>";
    }
    
    BlueMotosSouthampton\Utils\BMS_Session::remove('test_key');
} catch (Exception $e) {
    echo "<p>❌ <strong>FAIL:</strong> Session error: " . $e->getMessage() . "</p>";
}

// Test 3: Test PricingCalculator Class
echo "<h2>Test 3: PricingCalculator Class</h2>";
try {
    $calculator = new BlueMotosSouthampton\Services\PricingCalculator();
    $price = $calculator->calculate('mot_test', 1600, 'petrol');
    
    if (is_numeric($price) && $price > 0) {
        echo "<p>✅ <strong>PASS:</strong> PricingCalculator working correctly (MOT price: £{$price})</p>";
    } else {
        echo "<p>❌ <strong>FAIL:</strong> PricingCalculator not returning valid price</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>FAIL:</strong> PricingCalculator error: " . $e->getMessage() . "</p>";
}

// Test 4: Test Vehicle Lookup with Real Registrations
echo "<h2>Test 4: Vehicle Lookup Test</h2>";
echo "<p>Testing with the registrations from your debug log...</p>";

$test_registrations = ['H411DAR', 'WM65VJE'];

foreach ($test_registrations as $registration) {
    echo "<h3>Testing: {$registration}</h3>";
    
    try {
        $lookup_service = new BlueMotosSouthampton\Services\VehicleLookupCombined();
        $vehicle_data = $lookup_service->lookup_vehicle_comprehensive($registration);
        
        if (is_wp_error($vehicle_data)) {
            echo "<p>❌ <strong>ERROR:</strong> " . $vehicle_data->get_error_message() . "</p>";
            continue;
        }
        
        // Check if we're getting real data
        $using_mock = $vehicle_data['using_mock_data'] ?? true;
        $primary_source = $vehicle_data['primary_data_source'] ?? 'unknown';
        $make = $vehicle_data['make'] ?? 'Unknown';
        $model = $vehicle_data['model'] ?? 'Unknown';
        
        echo "<p><strong>Make:</strong> {$make}</p>";
        echo "<p><strong>Model:</strong> {$model}</p>";
        echo "<p><strong>Using Mock Data:</strong> " . ($using_mock ? 'Yes' : 'No') . "</p>";
        echo "<p><strong>Primary Data Source:</strong> {$primary_source}</p>";
        
        // Check data sources
        if (isset($vehicle_data['data_sources'])) {
            echo "<p><strong>Data Sources:</strong></p>";
            echo "<ul>";
            foreach ($vehicle_data['data_sources'] as $source => $type) {
                echo "<li>{$source}: {$type}</li>";
            }
            echo "</ul>";
        }
        
        // Verify expected results
        if ($registration === 'H411DAR') {
            if (strtoupper($make) === 'PORSCHE' && strtoupper($model) === 'CAYENNE') {
                echo "<p>✅ <strong>PASS:</strong> Correct vehicle data returned (Porsche Cayenne)</p>";
            } else {
                echo "<p>❌ <strong>FAIL:</strong> Expected Porsche Cayenne, got {$make} {$model}</p>";
            }
        } elseif ($registration === 'WM65VJE') {
            if (strtoupper($make) === 'FIAT' && strtoupper($model) === '500') {
                echo "<p>✅ <strong>PASS:</strong> Correct vehicle data returned (FIAT 500)</p>";
            } else {
                echo "<p>❌ <strong>FAIL:</strong> Expected FIAT 500, got {$make} {$model}</p>";
            }
        }
        
        if (!$using_mock) {
            echo "<p>✅ <strong>PASS:</strong> Using real API data (not mock)</p>";
        } else {
            echo "<p>⚠️ <strong>WARNING:</strong> Still using mock data</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

// Test 5: Test API Connections
echo "<h2>Test 5: API Connection Test</h2>";
try {
    $lookup_service = new BlueMotosSouthampton\Services\VehicleLookupCombined();
    $test_results = $lookup_service->test_all_connections();
    
    foreach ($test_results as $api => $result) {
        $status = $result['success'] ? '✅ PASS' : '❌ FAIL';
        echo "<p><strong>{$api} API:</strong> {$status} - {$result['message']}</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>ERROR:</strong> API connection test failed: " . $e->getMessage() . "</p>";
}

echo "<h2>🎯 Summary</h2>";
echo "<p><strong>If all tests show ✅:</strong> Your vehicle lookup issues are resolved!</p>";
echo "<p><strong>If you see ❌ errors:</strong> Please share the error details for further assistance.</p>";

echo "<h3>What was fixed:</h3>";
echo "<ul>";
echo "<li>✅ Updated DVLA API key from placeholder to real key</li>";
echo "<li>✅ Added placeholder API key detection</li>";
echo "<li>✅ Modified data merging to prioritize real DVSA data over mock DVLA data</li>";
echo "<li>✅ Fixed PricingCalculator class loading issues</li>";
echo "<li>✅ Fixed session management to handle headers already sent</li>";
echo "</ul>";
?>
