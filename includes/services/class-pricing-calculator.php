<?php
/**
 * Enhanced Pricing Calculator - Compatible with existing Blue Motors Southampton plugin
 * Calculates dynamic pricing based on engine size and service type
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

namespace BlueMotosSouthampton\Services;

class PricingCalculatorEnhanced {
    
    /**
     * Base pricing configuration
     * @var array
     */
    private $base_prices;
    
    /**
     * Engine size multipliers
     * @var array
     */
    private $engine_multipliers;
    
    /**
     * Fuel type adjustments
     * @var array
     */
    private $fuel_adjustments;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_pricing_config();
    }
    
    /**
     * Load pricing configuration
     */
    private function load_pricing_config() {
        $this->base_prices = [
            'interim_service' => 89.00,
            'full_service' => 149.00,
            'mot_test' => 54.85  // Correct UK MOT test price
        ];
        
        // Engine size multipliers (based on CC ranges)
        $this->engine_multipliers = [
            'small' => 1.0,    // Up to 1400cc
            'medium' => 1.15,  // 1401-2000cc
            'large' => 1.3,    // 2001-3000cc
            'xlarge' => 1.5    // Over 3000cc
        ];
        
        // Fuel type adjustments
        $this->fuel_adjustments = [
            'petrol' => 0.0,
            'diesel' => 10.0,
            'hybrid' => 15.0,
            'electric' => -20.0
        ];
    }
    
    /**
     * Calculate service price based on engine size and fuel type
     * 
     * @param string $service_type
     * @param int $engine_size
     * @param string $fuel_type
     * @return float
     */
    public function calculate_service_price($service_type, $engine_size = 1600, $fuel_type = 'petrol') {
        // Get base price;
        $base_price = $this->base_prices[$service_type] ?? 0;
        if ($base_price === 0) {
            return 0;
        }
        
        // Apply engine size multiplier
        $engine_category = $this->get_engine_category($engine_size);
        $multiplier = $this->engine_multipliers[$engine_category] ?? 1.0;
        $price = $base_price * $multiplier;
        
        // Apply fuel type adjustment
        $fuel_adjustment = $this->fuel_adjustments[strtolower($fuel_type)] ?? 0;
        $price += $fuel_adjustment;
        
        // Round to nearest £5
        $price = round($price / 5) * 5;
        
        // Ensure minimum price
        $min_price = $base_price * 0.8;
        $price = max($price, $min_price);
        
        return $price;
    }
    
    /**
     * Calculate all service prices for a vehicle
     * 
     * @param int $engine_size
     * @param string $fuel_type
     * @return array
     */
    public function calculate_service_prices($engine_size = 1600, $fuel_type = 'petrol') {
        $prices = [];
        
        foreach ($this->base_prices as $service_type => $base_price) {
            $prices[$service_type] = $this->calculate_service_price($service_type, $engine_size, $fuel_type);
        }
        
        // Add convenience aliases
        $prices['interim'] = $prices['interim_service'];
        $prices['full'] = $prices['full_service'];
        $prices['mot'] = $prices['mot_test'];
        
        return $prices;
    }
    
    /**
     * Calculate MOT combo pricing
     * 
     * @param string $service_type
     * @param int $engine_size
     * @param string $fuel_type
     * @return array
     */
    public function calculate_mot_combo($service_type, $engine_size = 1600, $fuel_type = 'petrol') {
        $service_price = $this->calculate_service_price($service_type, $engine_size, $fuel_type);
        $mot_price = $this->base_prices['mot_test'];
        
        // Combo discounts
        $discounts = [
            'interim_service' => 10.00,
            'full_service' => 15.00,
        ];
        
        $discount = $discounts[$service_type] ?? 0;
        $total_individual = $service_price + $mot_price;
        $total_combo = $total_individual - $discount;
        
        return [
            'service_price' => $service_price,
            'mot_price' => $mot_price,
            'discount' => $discount,
            'total_individual' => $total_individual,
            'total_combo' => $total_combo,
            'savings' => $discount
        ];
    }
    
    /**
     * Get engine category based on size
     * 
     * @param int $engine_size
     * @return string
     */
    private function get_engine_category($engine_size) {
        if ($engine_size <= 1400) {
            return 'small';
        } elseif ($engine_size <= 2000) {
            return 'medium';
        } elseif ($engine_size <= 3000) {
            return 'large';
        } else {
            return 'xlarge';
        }
    }
    
    /**
     * Get pricing explanation for display
     * 
     * @param string $service_type
     * @param int $engine_size
     * @param string $fuel_type
     * @return array
     */
    public function get_pricing_explanation($service_type, $engine_size = 1600, $fuel_type = 'petrol') {
        $base_price = $this->base_prices[$service_type] ?? 0;
        $final_price = $this->calculate_service_price($service_type, $engine_size, $fuel_type);
        
        $explanation = [
            'base_price' => $base_price,
            'final_price' => $final_price,
            'factors' => [],
        ];
        
        // Engine size factor
        $engine_category = $this->get_engine_category($engine_size);
        $multiplier = $this->engine_multipliers[$engine_category];
        if ($multiplier !== 1.0) {
            $explanation['factors'][] = [
                'factor' => 'Engine Size',
                'value' => $engine_size . 'cc (' . ucfirst($engine_category) . ')',
                'adjustment' => ($multiplier - 1) * 100 . '%'
            ];
        }
        
        // Fuel type factor
        $fuel_adjustment = $this->fuel_adjustments[strtolower($fuel_type)] ?? 0;
        if ($fuel_adjustment !== 0) {
            $explanation['factors'][] = [
                'factor' => 'Fuel Type',
                'value' => ucfirst($fuel_type),
                'adjustment' => ($fuel_adjustment > 0 ? '+' : '') . '£' . abs($fuel_adjustment)
            ];
        }
        
        return $explanation;
    }
    
    /**
     * Get price range for a service type
     * 
     * @param string $service_type
     * @return array
     */
    public function get_price_range($service_type) {
        $base_price = $this->base_prices[$service_type] ?? 0;
        if ($base_price === 0) {
            return null;
        }
        
        // Calculate min and max prices across all engine sizes and fuel types
        $prices = [];
        
        foreach (['small', 'medium', 'large', 'xlarge'] as $engine_category) {
            $multiplier = $this->engine_multipliers[$engine_category];
            foreach ($this->fuel_adjustments as $fuel_type => $adjustment) {
                $price = ($base_price * $multiplier) + $adjustment;
                $price = round($price / 5) * 5;
                $price = max($price, $base_price * 0.8);
                $prices[] = $price;
            }
        }
        
        return [
            'min_price' => min($prices),
            'max_price' => max($prices),
            'base_price' => $base_price
        ];
    }
    
    /**
     * Validate pricing parameters
     * 
     * @param string $service_type
     * @param int $engine_size
     * @param string $fuel_type
     * @return array
     */
    public function validate_parameters($service_type, $engine_size, $fuel_type) {
        $errors = [];
        
        // Validate service type
        if (!isset($this->base_prices[$service_type])) {
            $errors[] = 'Invalid service type: ' . $service_type;
        }
        
        // Validate engine size
        if (!is_numeric($engine_size) || $engine_size < 500 || $engine_size > 8000) {
            $errors[] = 'Invalid engine size: ' . $engine_size . 'cc';
        }
        
        // Validate fuel type
        if (!isset($this->fuel_adjustments[strtolower($fuel_type)])) {
            $errors[] = 'Invalid fuel type: ' . $fuel_type;
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

/**
 * Main PricingCalculator class for compatibility
 */
class PricingCalculator extends PricingCalculatorEnhanced {
    
    /**
     * Main calculate method for compatibility with existing code
     * 
     * @param string $service_type
     * @param int $engine_size
     * @param string $fuel_type
     * @return float
     */
    public function calculate($service_type, $engine_size = 1600, $fuel_type = 'petrol') {
        return $this->calculate_service_price($service_type, $engine_size, $fuel_type);
    }
    
    /**
     * Get all prices for compatibility
     * 
     * @param int $engine_size
     * @param string $fuel_type
     * @return array
     */
    public function get_all_prices($engine_size = 1600, $fuel_type = 'petrol') {
        return $this->calculate_service_prices($engine_size, $fuel_type);
    }
}

/**
 * Compatibility class alias for existing code
 */
if (!class_exists('BMS_Pricing_Calculator')) {
    class BMS_Pricing_Calculator extends PricingCalculator {
        // Maintain compatibility with existing method names
        public function calculate_service_prices($engine_size = 1600, $fuel_type = 'petrol') {
            return parent::calculate_service_prices($engine_size, $fuel_type);
        }
    }
}

