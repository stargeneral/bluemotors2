<?php
/**
 * Blue Motors Southampton - Enhanced Service Selection Step
 * Compatible with existing booking flow and pricing calculator
 * 
 * File: public/templates/service-selection-step.php
 */

// Get service manager instance (use enhanced if available, fallback to original)
if (class_exists('\BlueMotosSouthampton\Services\ServiceManagerEnhanced')) {
     $services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true);
   
} else {
    $services = \BlueMotosSouthampton\Services\ServiceManager::get_services();
}

// Get pricing calculator instance
$pricing_calculator = new \BlueMotosSouthampton\Services\PricingCalculatorEnhanced();

// Get vehicle data if available (from session or previous step)
$vehicle_data = array();
$vehicle_reg = '';
$vehicle_make = '';
$vehicle_model = '';
$engine_size = 1600; // Default engine size

// Try to get vehicle data from session if available
$vehicle_data = BMS_Session::get('vehicle_data', array());

if (!empty($vehicle_data)) {
    $vehicle_reg = $vehicle_data['registration'] ?? '';
    $vehicle_make = $vehicle_data['make'] ?? '';
    $vehicle_model = $vehicle_data['model'] ?? '';
    $engine_size = $vehicle_data['engineCapacity'] ?? 1600;
}

// Calculate pricing using existing pricing calculator
$fuel_type = $vehicle_data['fuelType'] ?? 'petrol';
$service_prices = array();

// Calculate prices for main services
$main_services = ['interim_service', 'full_service', 'mot_test'];
foreach ($main_services as $service_type) {
    $service_prices[$service_type] = $pricing_calculator->calculate_service_price($service_type, $engine_size, $fuel_type);
}

// Add convenience aliases
$service_prices['interim'] = $service_prices['interim_service'];
$service_prices['full'] = $service_prices['full_service'];
$service_prices['mot'] = $service_prices['mot_test'];

// Calculate "From Only" prices (minimum possible prices)
$from_only_prices = array();
foreach ($main_services as $service_type) {
    if ($service_type === 'mot_test') {
        $from_only_prices[$service_type] = $service_prices[$service_type]; // MOT is fixed price
    } else {
        // Calculate minimum price (small engine + petrol)
        $from_only_prices[$service_type] = $pricing_calculator->calculate_service_price($service_type, 1200, 'petrol');
    }
}
?>

<div class="bms-service-selection-container">


    <!-- Service Selection Header -->
    <div class="bms-service-header">
        <h1>CHOOSE YOUR CAR SERVICE</h1>
        <?php if (!empty($vehicle_make) && !empty($vehicle_model)): ?>
        <p class="service-subtitle">Select the perfect service for your <?php echo esc_html($vehicle_make . ' ' . $vehicle_model); ?> <?php echo !empty($vehicle_reg) ? '(' . esc_html($vehicle_reg) . ')' : ''; ?></p>
        <?php else: ?>
        <p class="service-subtitle">Professional car servicing in Southampton</p>
        <?php endif; ?>
    </div>

    <!-- Vehicle Summary (if vehicle data available) -->
    <?php if (!empty($vehicle_make) && !empty($vehicle_model)): ?>
    <div class="bms-vehicle-summary">
        <div class="vehicle-info">
            <span class="vehicle-icon">🚗</span>
            <div class="vehicle-details">
                <strong><?php echo esc_html($vehicle_make . ' ' . $vehicle_model); ?></strong>
                <?php if (!empty($vehicle_reg)): ?>
                <span class="reg-number"><?php echo esc_html($vehicle_reg); ?></span>
                <?php endif; ?>
                <span class="engine-info"><?php echo esc_html($engine_size); ?>cc <?php echo esc_html($vehicle_data['fuelType'] ?? 'Petrol'); ?></span>
            </div>
        </div>
        <button type="button" class="btn-change-vehicle" onclick="moveToStep(2)">
            Change Vehicle
        </button>
    </div>
    <?php endif; ?>

    <!-- Main Services Cards -->
    <div class="bms-services-showcase">
        
        <!-- Interim Service Card -->
        <div class="bms-service-card-modern interim-card" data-service="interim_service">
            <div class="service-content">
                <h2 class="service-title">INTERIM SERVICE</h2>
                <p class="service-tagline">ESSENTIAL CARE - PRICES INCLUDE<br>OIL & FILTERS</p>
                
                <div class="price-section">
                    <?php if (!empty($vehicle_data)): ?>
                        <!-- Show actual price for this vehicle -->
                        <span class="price-label">YOUR<br>PRICE</span>
                        <span class="price-main">£<?php echo number_format($service_prices['interim_service'], 0); ?></span>
                    <?php else: ?>
                        <!-- Show "From Only" price when no vehicle selected -->
                        <span class="price-label">FROM<br>ONLY</span>
                        <span class="price-main">£<?php echo number_format($from_only_prices['interim_service'], 0); ?></span>
                    <?php endif; ?>
                </div>
                
                <p class="fuel-types">PETROL, DIESEL & HYBRID</p>
                
                <div class="service-details">
                    <div class="detail-item">⏱️ Approximately 1.5 hours</div>
                    <div class="detail-item">✓ Every 6 months or 6,000 miles</div>
                </div>
            </div>
            
            <button type="button" class="btn-select-service" data-service="interim_service" data-price="<?php echo $service_prices['interim_service']; ?>">
                BOOK INTERIM SERVICE
            </button>
        </div>

        <!-- OR Separator -->
        <div class="services-separator">
            <span class="or-text">OR</span>
        </div>

        <!-- Full Service Card -->
        <div class="bms-service-card-modern full-card recommended" data-service="full_service">
            <div class="popular-badge">MOST POPULAR</div>
            
            <div class="service-content">
                <h2 class="service-title">FULL SERVICE</h2>
                <p class="service-tagline">COMPREHENSIVE CHECK<br>EVERYTHING INCLUDED</p>
                
                <div class="price-section">
                    <?php if (!empty($vehicle_data)): ?>
                        <!-- Show actual price for this vehicle -->
                        <span class="price-label">YOUR<br>PRICE</span>
                        <span class="price-main">£<?php echo number_format($service_prices['full_service'], 0); ?></span>
                    <?php else: ?>
                        <!-- Show "From Only" price when no vehicle selected -->
                        <span class="price-label">FROM<br>ONLY</span>
                        <span class="price-main">£<?php echo number_format($from_only_prices['full_service'], 0); ?></span>
                    <?php endif; ?>
                </div>
                
                <p class="fuel-types">PETROL, DIESEL & HYBRID</p>
                
                <div class="service-details">
                    <div class="detail-item">⏱️ Approximately 2 hours</div>
                    <div class="detail-item">✓ Every 12 months or 12,000 miles</div>
                </div>
            </div>
            
            <button type="button" class="btn-select-service" data-service="full_service" data-price="<?php echo $service_prices['full_service']; ?>">
                BOOK FULL SERVICE
            </button>
        </div>
    </div>

    <!-- MOT Combination Section -->
    <div class="bms-mot-section">
        <div class="mot-header">
            <h2>💰 SAVE TIME &amp; MONEY</h2>
            <h3>BOOK AN MOT at the same time</h3>
            <p class="mot-subtitle">Get both services done together and save on your total cost</p>
        </div>

        <div class="mot-pricing-table">
            <div class="pricing-header">
                <div class="col-service">SERVICE PRICE</div>
                <div class="col-mot">MOT PRICE</div>
                <div class="col-total">TOTAL PRICE</div>
                <div class="col-action"></div>
            </div>

            <!-- Interim + MOT Row -->
            <div class="pricing-row interim-row">
                <div class="col-service">
                    <span class="service-name">Interim</span>
                    <?php if (!empty($vehicle_data)): ?>
                        <span class="service-price" id="interim-service-price">£<?php echo number_format($service_prices['interim_service'], 2); ?></span>
                    <?php else: ?>
                        <span class="service-price" id="interim-service-price">£<?php echo number_format($from_only_prices['interim_service'], 2); ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-mot">
                    <span class="mot-price" id="mot-price-display">+ £40.00</span>
                </div>
                <div class="col-total">
                    <?php 
                    // Use base "FROM ONLY" prices for combo calculation to ensure consistency
                    $base_interim = $from_only_prices['interim_service'];
                    $base_mot = 40.00; // Standard MOT price for display consistency
                    $interim_total = $base_interim + $base_mot;
                    $interim_combo = $interim_total - 10.00; // £10 discount
                    
                    // If vehicle data available, calculate actual combo price
                    if (!empty($vehicle_data)) {
                        $actual_interim_total = $service_prices['interim_service'] + $service_prices['mot_test'];
                        $actual_interim_combo = $actual_interim_total - 10.00;
                    } else {
                        $actual_interim_combo = $interim_combo;
                    }
                    ?>
                    <span class="total-price" id="interim-combo-price">£<?php echo number_format($actual_interim_combo, 2); ?></span>
                    <span class="savings" id="interim-savings">Save £10.00</span>
                </div>
                <div class="col-action">
                    <button type="button" class="btn-select-combo btn-primary" 
                            data-service="interim_service" 
                            data-mot="true" 
                            data-price="<?php echo $actual_interim_combo; ?>"
                            id="btn-interim-combo">
                        BOOK NOW
                    </button>
                </div>
            </div>

            <!-- Full + MOT Row -->
            <div class="pricing-row full-row">
                <div class="col-service">
                    <span class="service-name">Full</span>
                    <?php if (!empty($vehicle_data)): ?>
                        <span class="service-price" id="full-service-price">£<?php echo number_format($service_prices['full_service'], 2); ?></span>
                    <?php else: ?>
                        <span class="service-price" id="full-service-price">£<?php echo number_format($from_only_prices['full_service'], 2); ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-mot">
                    <span class="mot-price" id="mot-price-display-2">+ £40.00</span>
                </div>
                <div class="col-total">
                    <?php 
                    // Use base "FROM ONLY" prices for combo calculation to ensure consistency
                    $base_full = $from_only_prices['full_service'];
                    $base_mot = 40.00; // Standard MOT price for display consistency
                    $full_total = $base_full + $base_mot;
                    $full_combo = $full_total - 15.00; // £15 discount
                    
                    // If vehicle data available, calculate actual combo price
                    if (!empty($vehicle_data)) {
                        $actual_full_total = $service_prices['full_service'] + $service_prices['mot_test'];
                        $actual_full_combo = $actual_full_total - 15.00;
                    } else {
                        $actual_full_combo = $full_combo;
                    }
                    ?>
                    <span class="total-price" id="full-combo-price">£<?php echo number_format($actual_full_combo, 2); ?></span>
                    <span class="savings" id="full-savings">Save £15.00</span>
                </div>
                <div class="col-action">
                    <button type="button" class="btn-select-combo btn-primary" 
                            data-service="full_service" 
                            data-mot="true" 
                            data-price="<?php echo $actual_full_combo; ?>"
                            id="btn-full-combo">
                        BOOK NOW
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Comparison Section -->
    <div class="bms-service-comparison">
        <div class="comparison-header">
            <h2>🔧 CAR SERVICING CHECKLIST</h2>
            <p>Find out the difference between our Full and Interim Service</p>
        </div>

        <div class="comparison-content" id="service-comparison" style="display: none;">
            <div class="comparison-table">
                <div class="comparison-row header-row">
                    <div class="check-item">Service Check</div>
                    <div class="interim-col">Interim Service</div>
                    <div class="full-col">Full Service</div>
                </div>
                
                <div class="comparison-row">
                    <div class="check-item">Engine oil &amp; filter change</div>
                    <div class="interim-col">✓</div>
                    <div class="full-col">✓</div>
                </div>
                
                <div class="comparison-row">
                    <div class="check-item">Fluid level checks</div>
                    <div class="interim-col">✓</div>
                    <div class="full-col">✓</div>
                </div>
                
                <div class="comparison-row">
                    <div class="check-item">Battery test</div>
                    <div class="interim-col">✓</div>
                    <div class="full-col">✓</div>
                </div>
                
                <div class="comparison-row">
                    <div class="check-item">Comprehensive engine check</div>
                    <div class="interim-col">-</div>
                    <div class="full-col">✓</div>
                </div>
                
                <div class="comparison-row">
                    <div class="check-item">Brake system inspection</div>
                    <div class="interim-col">Basic</div>
                    <div class="full-col">✓ Full</div>
                </div>
                
                <div class="comparison-row">
                    <div class="check-item">Air filter replacement</div>
                    <div class="interim-col">-</div>
                    <div class="full-col">✓</div>
                </div>
            </div>
        </div>

        <button type="button" class="btn-more-info" id="btn-more-info">
            MORE INFO
        </button>
    </div>

    <!-- Selection Summary (Hidden initially) -->
    <div class="bms-selection-summary" id="selection-summary" style="display: none;">
        <div class="summary-content">
            <h3>Your Selection</h3>
            <div class="selected-service">
                <span class="service-name" id="summary-service-name"></span>
                <span class="service-price" id="summary-service-price"></span>
            </div>
            <div class="selected-options" id="summary-options"></div>
            <div class="total-price">
                <strong>Total: <span id="summary-total-price"></span></strong>
            </div>
            <button type="button" class="btn-continue btn-primary" onclick="continueToNextStep()">
                Continue to Vehicle Details →
            </button>
        </div>
    </div>

    <!-- Blue Motors Advantages Footer -->
    <div class="bms-competitive-footer">
        <h3>🏆 Why Choose Blue Motors Southampton</h3>
        <div class="advantages-grid">
            <div class="advantage-item">
                <span class="advantage-icon">🔧</span>
                <h4>Southampton Specialists</h4>
                <p>Local expertise and personalized service</p>
            </div>
            <div class="advantage-item">
                <span class="advantage-icon">💻</span>
                <h4>Complete Online Booking</h4>
                <p>Book everything online at your convenience</p>
            </div>
            <div class="advantage-item">
                <span class="advantage-icon">🇬🇧</span>
                <h4>UK-First Experience</h4>
                <p>Designed for UK customers with familiar formats</p>
            </div>
            <div class="advantage-item">
                <span class="advantage-icon">💳</span>
                <h4>Secure Payments</h4>
                <p>Safe and reliable payment processing</p>
            </div>
        </div>
    </div>
</div>

<script>
// Compatibility function for step navigation
function continueToNextStep() {
    // This will be handled by the main booking.js file
    if (typeof moveToStep === 'function') {
        moveToStep(2); // Move to vehicle details step (step 2)
    } else if (typeof window.BMSServiceSelection !== 'undefined') {
        // Fallback to service selection handler
        window.BMSServiceSelection.continueToDateTime();
    }
}

// Enhanced service selection for modern cards
document.addEventListener('DOMContentLoaded', function() {
    // Store current pricing data for JavaScript access
    window.bmsPricingData = {
        interim_service: <?php echo $service_prices['interim_service']; ?>,
        full_service: <?php echo $service_prices['full_service']; ?>,
        mot_test: <?php echo $service_prices['mot_test']; ?>,
        engine_size: <?php echo $engine_size; ?>,
        fuel_type: '<?php echo esc_js($fuel_type); ?>'
    };
    
    // Function to update pricing when vehicle data changes
    window.updateServicePricing = function(vehicleData) {
        if (!vehicleData || !vehicleData.engineCapacity) return;
        
        const engineSize = vehicleData.engineCapacity;
        const fuelType = vehicleData.fuelType || 'petrol';
        
        // Use AJAX to get accurate server-side pricing
        jQuery.ajax({
            url: bms_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bms_update_pricing_for_vehicle',
                engine_size: engineSize,
                fuel_type: fuelType,
                nonce: bms_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const prices = response.data.prices;
                    const comboprices = response.data.combo_prices;
                    
                    // Update individual service prices and labels
                    document.querySelector('.interim-card .price-label').innerHTML = 'YOUR<br>PRICE';
                    document.querySelector('.interim-card .price-main').textContent = '£' + Math.round(prices.interim_service);
                    document.querySelector('.full-card .price-label').innerHTML = 'YOUR<br>PRICE';
                    document.querySelector('.full-card .price-main').textContent = '£' + Math.round(prices.full_service);
                    
                    // Update MOT combo pricing
                    updateMotComboPricing(prices.interim_service, prices.full_service, prices.mot_test);
                    
                    // Update stored pricing data
                    window.bmsPricingData.interim_service = prices.interim_service;
                    window.bmsPricingData.full_service = prices.full_service;
                    window.bmsPricingData.mot_test = prices.mot_test;
                    window.bmsPricingData.engine_size = engineSize;
                    window.bmsPricingData.fuel_type = fuelType;
                }
            },
            error: function() {
                console.warn('Failed to update pricing for vehicle');
            }
        });
    };
    
    // Function to update MOT combo pricing display
    function updateMotComboPricing(interimPrice, fullPrice, motPrice) {
        // Use standard £40 MOT price for display consistency
        const displayMotPrice = 40.00;
        
        // Update interim combo
        const interimTotal = interimPrice + displayMotPrice;
        const interimCombo = interimTotal - 10.00; // £10 discount
        document.getElementById('interim-service-price').textContent = '£' + interimPrice.toFixed(2);
        document.getElementById('interim-combo-price').textContent = '£' + interimCombo.toFixed(2);
        document.getElementById('btn-interim-combo').setAttribute('data-price', interimCombo.toFixed(2));
        
        // Update full combo
        const fullTotal = fullPrice + displayMotPrice;
        const fullCombo = fullTotal - 15.00; // £15 discount
        document.getElementById('full-service-price').textContent = '£' + fullPrice.toFixed(2);
        document.getElementById('full-combo-price').textContent = '£' + fullCombo.toFixed(2);
        document.getElementById('btn-full-combo').setAttribute('data-price', fullCombo.toFixed(2));
        
        // Keep MOT price displays consistent at £40
        document.getElementById('mot-price-display').textContent = '+ £40.00';
        document.getElementById('mot-price-display-2').textContent = '+ £40.00';
        
        // Update individual service button data-price attributes too
        document.querySelector('.btn-select-service[data-service="interim_service"]').setAttribute('data-price', interimPrice.toFixed(2));
        document.querySelector('.btn-select-service[data-service="full_service"]').setAttribute('data-price', fullPrice.toFixed(2));
    }
    
    // Basic pricing calculator (should match server-side logic)
    function calculateServicePrice(serviceType, engineSize, fuelType) {
        let basePrice = 0;
        
        switch(serviceType) {
            case 'interim_service':
                basePrice = engineSize <= 1600 ? 85 : (engineSize <= 2000 ? 95 : 110);
                break;
            case 'full_service':
                basePrice = engineSize <= 1600 ? 150 : (engineSize <= 2000 ? 165 : 185);
                break;
            case 'mot_test':
                basePrice = 54.85; // Standard MOT price
                break;
        }
        
        // Add fuel type adjustments if needed
        if (fuelType === 'diesel' && serviceType !== 'mot_test') {
            basePrice += 15; // Diesel surcharge
        }
        
        return basePrice;
    }
});
</script>