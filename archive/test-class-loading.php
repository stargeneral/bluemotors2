<?php
/**
 * Test Class Loading - Blue Motors Southampton
 * Quick test to verify all classes can be instantiated
 */

// Include WordPress
require_once dirname(__FILE__) . '/../../../wp-config.php';

echo "Testing Blue Motors Southampton Class Loading...\n\n";

try {
    // Test if VehicleLookupEnhanced can be instantiated
    $vehicle_lookup = new \BlueMotosSouthampton\Services\VehicleLookupEnhanced();
    echo "✅ VehicleLookupEnhanced class loaded successfully\n";
    
    // Test if ServiceManagerEnhanced can be instantiated  
    $service_manager = new \BlueMotosSouthampton\Services\ServiceManagerEnhanced();
    echo "✅ ServiceManagerEnhanced class loaded successfully\n";
    
    // Test if PricingCalculatorEnhanced can be instantiated
    $pricing_calculator = new BMS_Pricing_CalculatorEnhanced();
    echo "✅ PricingCalculatorEnhanced class loaded successfully\n";
    
    // Test main plugin class
    $plugin = new Blue_Motors_Southampton();
    echo "✅ Main plugin class loaded successfully\n";
    
    echo "\n🎉 All classes loaded successfully! The critical error should be resolved.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
