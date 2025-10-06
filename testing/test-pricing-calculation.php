<?php
/**
 * Test Pricing Calculation for VF19XKX
 * 
 * This file tests the pricing calculation for your specific vehicle
 */

// Include WordPress
require_once '../../../wp-load.php';

// Include the pricing calculator
require_once 'includes/services/class-pricing-calculator.php';

use BlueMotosSouthampton\Services\PricingCalculatorEnhanced;

echo "<h2>🔍 Pricing Calculation Test for VF19XKX</h2>\n";

// Create pricing calculator instance
$calculator = new PricingCalculatorEnhanced();

// Test vehicle data (VF19XKX)
$engine_size = 1580; // cc
$fuel_type = 'hybrid';

echo "<h3>Vehicle Details:</h3>\n";
echo "<p><strong>Registration:</strong> VF19XKX</p>\n";
echo "<p><strong>Engine Size:</strong> {$engine_size}cc</p>\n";
echo "<p><strong>Fuel Type:</strong> {$fuel_type}</p>\n";

echo "<h3>Pricing Calculations:</h3>\n";

// Calculate each service
$services = ['interim_service', 'full_service', 'mot_test'];

foreach ($services as $service_type) {
    $price = $calculator->calculate_service_price($service_type, $engine_size, $fuel_type);
    $explanation = $calculator->get_pricing_explanation($service_type, $engine_size, $fuel_type);
    
    echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 10px 0;'>\n";
    echo "<h4>" . ucwords(str_replace('_', ' ', $service_type)) . "</h4>\n";
    echo "<p><strong>Final Price:</strong> £" . number_format($price, 2) . "</p>\n";
    echo "<p><strong>Base Price:</strong> £" . number_format($explanation['base_price'], 2) . "</p>\n";
    
    if (!empty($explanation['factors'])) {
        echo "<p><strong>Adjustments:</strong></p>\n";
        echo "<ul>\n";
        foreach ($explanation['factors'] as $factor) {
            echo "<li>{$factor['factor']}: {$factor['value']} → {$factor['adjustment']}</li>\n";
        }
        echo "</ul>\n";
    }
    echo "</div>\n";
}

// Test MOT combos
echo "<h3>MOT Combo Pricing:</h3>\n";

foreach (['interim_service', 'full_service'] as $service_type) {
    $combo = $calculator->calculate_mot_combo($service_type, $engine_size, $fuel_type);
    
    echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 10px 0;'>\n";
    echo "<h4>" . ucwords(str_replace('_', ' ', $service_type)) . " + MOT Combo</h4>\n";
    echo "<p><strong>Service Price:</strong> £" . number_format($combo['service_price'], 2) . "</p>\n";
    echo "<p><strong>MOT Price:</strong> £" . number_format($combo['mot_price'], 2) . "</p>\n";
    echo "<p><strong>Total Individual:</strong> £" . number_format($combo['total_individual'], 2) . "</p>\n";
    echo "<p><strong>Discount:</strong> -£" . number_format($combo['discount'], 2) . "</p>\n";
    echo "<p><strong>Combo Price:</strong> £" . number_format($combo['total_combo'], 2) . "</p>\n";
    echo "<p><strong>You Save:</strong> £" . number_format($combo['savings'], 2) . "</p>\n";
    echo "</div>\n";
}

// Test "From Only" pricing (minimum prices)
echo "<h3>'From Only' Pricing (Minimum Prices):</h3>\n";

foreach (['interim_service', 'full_service'] as $service_type) {
    $min_price = $calculator->calculate_service_price($service_type, 1200, 'petrol'); // Small engine + petrol
    echo "<p><strong>" . ucwords(str_replace('_', ' ', $service_type)) . " From Only:</strong> £" . number_format($min_price, 0) . "</p>\n";
}

echo "<h3>✅ Expected Results for VF19XKX:</h3>\n";
echo "<p>For your 1580cc Hybrid vehicle, you should see:</p>\n";
echo "<ul>\n";
echo "<li><strong>Interim Service:</strong> Around £117-120 (£89 base × 1.15 engine multiplier + £15 hybrid adjustment)</li>\n";
echo "<li><strong>Full Service:</strong> Around £186-190 (£149 base × 1.15 engine multiplier + £15 hybrid adjustment)</li>\n";
echo "<li><strong>MOT Test:</strong> £54.85 (fixed price)</li>\n";
echo "</ul>\n";
?>
