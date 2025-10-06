<?php
/**
 * Test Fuel Type and Engine Capacity Fix
 * Tests the fixes for VF19XKX (Hyundai IONIQ) showing correct data
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

echo "<h1>🔧 Fuel Type & Engine Capacity Fix Test</h1>";
echo "<p>Testing fixes for VF19XKX (Hyundai IONIQ) data accuracy</p>";

// Test data from the debug log
$test_cases = [
    'VF19XKX' => [
        'expected_make' => 'HYUNDAI',
        'expected_model' => 'IONIQ',
        'expected_engine' => 1580,
        'expected_fuel_raw' => 'Hybrid Electric (Clean)',
        'expected_fuel_normalized' => 'hybrid',
        'expected_year' => 2019
    ]
];

echo "<h2>🧪 Test Results</h2>";

foreach ($test_cases as $registration => $expected) {
    echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0;'>";
    echo "<h3>Testing: {$registration}</h3>";
    
    try {
        // Test fuel type normalization function
        echo "<h4>1. Fuel Type Normalization Test</h4>";
        
        // Load the combined lookup class
        require_once __DIR__ . '/../includes/services/class-dvla-api-enhanced.php';
        require_once __DIR__ . '/../includes/services/class-dvsa-mot-api-enhanced.php';
        require_once __DIR__ . '/../includes/services/class-vehicle-lookup-combined.php';
        
        $lookup = new \BlueMotosSouthampton\Services\VehicleLookupCombined();
        
        // Test the fuel type normalization directly
        $reflection = new ReflectionClass($lookup);
        $method = $reflection->getMethod('normalize_fuel_type');
        $method->setAccessible(true);
        
        $normalized_fuel = $method->invoke($lookup, $expected['expected_fuel_raw']);
        
        if ($normalized_fuel === $expected['expected_fuel_normalized']) {
            echo "✅ Fuel type normalization: PASS<br>";
            echo "&nbsp;&nbsp;&nbsp;'{$expected['expected_fuel_raw']}' → '{$normalized_fuel}'<br>";
        } else {
            echo "❌ Fuel type normalization: FAIL<br>";
            echo "&nbsp;&nbsp;&nbsp;Expected: '{$expected['expected_fuel_normalized']}', Got: '{$normalized_fuel}'<br>";
        }
        
        echo "<h4>2. Full Vehicle Lookup Test</h4>";
        
        // Test full vehicle lookup
        $vehicle_data = $lookup->lookup_vehicle_comprehensive($registration);
        
        if (is_wp_error($vehicle_data)) {
            echo "❌ Vehicle lookup failed: " . $vehicle_data->get_error_message() . "<br>";
        } else {
            // Check each field
            $tests = [
                'Make' => [$vehicle_data['make'] ?? 'Unknown', $expected['expected_make']],
                'Model' => [$vehicle_data['model'] ?? 'Unknown', $expected['expected_model']],
                'Engine Capacity' => [$vehicle_data['engine_capacity'] ?? 0, $expected['expected_engine']],
                'Fuel Type (Raw)' => [$vehicle_data['fuel_type'] ?? 'Unknown', $expected['expected_fuel_raw']],
                'Fuel Type (Normalized)' => [$vehicle_data['fuel_type_normalized'] ?? 'petrol', $expected['expected_fuel_normalized']],
                'Year' => [$vehicle_data['year_of_manufacture'] ?? 0, $expected['expected_year']]
            ];
            
            foreach ($tests as $field => $values) {
                list($actual, $expected_val) = $values;
                if ($actual == $expected_val) {
                    echo "✅ {$field}: PASS ({$actual})<br>";
                } else {
                    echo "❌ {$field}: FAIL - Expected: {$expected_val}, Got: {$actual}<br>";
                }
            }
            
            echo "<h4>3. Data Source Analysis</h4>";
            echo "Primary Data Source: " . ($vehicle_data['primary_data_source'] ?? 'unknown') . "<br>";
            echo "Using Mock Data: " . ($vehicle_data['using_mock_data'] ? 'Yes' : 'No') . "<br>";
            echo "DVLA Source: " . ($vehicle_data['data_sources']['dvla'] ?? 'unknown') . "<br>";
            echo "DVSA Source: " . ($vehicle_data['data_sources']['dvsa'] ?? 'unknown') . "<br>";
            
            echo "<h4>4. Pricing Category Test</h4>";
            $pricing_category = $vehicle_data['pricing_category'] ?? 'standard';
            echo "Pricing Category: {$pricing_category}<br>";
            
            // For hybrid vehicles, should be 'specialist'
            if ($pricing_category === 'specialist') {
                echo "✅ Pricing category correct for hybrid vehicle<br>";
            } else {
                echo "⚠️ Pricing category may be incorrect for hybrid vehicle<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Test failed with exception: " . $e->getMessage() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    echo "</div>";
}

echo "<h2>🎯 Expected vs Actual Comparison</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Field</th><th>Expected (from API)</th><th>Should Display</th></tr>";
echo "<tr><td>Make</td><td>HYUNDAI</td><td>HYUNDAI</td></tr>";
echo "<tr><td>Model</td><td>IONIQ</td><td>IONIQ</td></tr>";
echo "<tr><td>Engine</td><td>1580cc</td><td>1580cc (not 1800cc)</td></tr>";
echo "<tr><td>Fuel Type</td><td>Hybrid Electric (Clean)</td><td>Hybrid (not PETROL)</td></tr>";
echo "<tr><td>Year</td><td>2019</td><td>2019 (not 2020)</td></tr>";
echo "<tr><td>Pricing</td><td>Specialist category</td><td>Correct hybrid pricing</td></tr>";
echo "</table>";

echo "<h2>📋 Next Steps</h2>";
echo "<ol>";
echo "<li>Clear any cached data for VF19XKX</li>";
echo "<li>Test the vehicle lookup on your live site</li>";
echo "<li>Verify the interface shows: HYUNDAI IONIQ, 1580cc, Hybrid</li>";
echo "<li>Check that pricing reflects specialist/hybrid category</li>";
echo "</ol>";

echo "<p><strong>If tests pass:</strong> The fuel type and engine capacity issues should be resolved!</p>";
?>
