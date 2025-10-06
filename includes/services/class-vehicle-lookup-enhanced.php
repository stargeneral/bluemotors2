<?php
/**
 * Enhanced Vehicle Lookup - DVLA Integration with Pricing
 * Combines vehicle lookup with automatic price calculation
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

namespace BlueMotosSouthampton\Services;

class VehicleLookupEnhanced {
    
    /**
     * DVLA API URL
     * @var string
     */
    private $api_url = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';
    
    /**
     * API Key for DVLA
     * @var string
     */
    private $api_key;
    
    /**
     * Pricing calculator instance
     * @var PricingCalculator
     */
    private $pricing_calculator;
    
    /**
     * Service manager instance
     * @var ServiceManager
     */
    private $service_manager;
    
    /**
     * Cache duration in seconds
     * @var int
     */
    private $cache_duration = 86400; // 24 hours
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = defined('BM_DVLA_API_KEY') ? BM_DVLA_API_KEY : get_option('bm_dvla_api_key');
        $this->pricing_calculator = new \BlueMotosSouthampton\Services\PricingCalculatorEnhanced();
        $this->service_manager = new \BlueMotosSouthampton\Services\ServiceManagerEnhanced();
    }
    
    /**
     * Lookup vehicle details from DVLA
     * 
     * @param string $registration Vehicle registration number
     * @return array|WP_Error
     */
    public function lookup($registration) {
        // Clean registration number
        $registration = strtoupper(preg_replace('/[^A-Z0-9]/', '', $registration));
        
        // Check cache first
        $cached = $this->get_cached_vehicle($registration);
        if ($cached !== false) {
            return $cached;
        }
        
        // Make API request
        $response = wp_remote_post($this->api_url, [
            'headers' => [
                'x-api-key' => $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'registrationNumber' => $registration
            ]),
            'timeout' => 10
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data)) {
            return new \WP_Error('invalid_response', 'Invalid response from DVLA API');
        }
        
        // Cache the result
        $this->cache_vehicle($registration, $data);
        
        return $data;
    }
    
    /**
     * Alias for lookup() method for compatibility
     * 
     * @param string $registration Vehicle registration number
     * @return array|WP_Error
     */
    public function lookup_vehicle($registration) {
        return $this->lookup($registration);
    }
    
    /**
     * Lookup vehicle with automatic price calculation
     * 
     * @param string $registration Vehicle registration
     * @param string $service_type Service type for pricing
     * @return array|WP_Error
     */
    public function lookup_with_pricing($registration, $service_type) {
        // Get vehicle details
        $vehicle_data = $this->lookup($registration);
        
        if (is_wp_error($vehicle_data)) {
            return $vehicle_data;
        }
        
        // Extract and normalize vehicle information
        $engine_size = $this->extract_engine_size($vehicle_data);
        $fuel_type = $this->normalize_fuel_type($vehicle_data['fuelType'] ?? 'petrol');
        
        // Calculate service price
        $price = $this->pricing_calculator->calculate($service_type, $engine_size, $fuel_type);
        
        // Enhance vehicle data with our calculations
        $enhanced_data = array_merge($vehicle_data, [
            'engine_size_cc' => $engine_size,
            'normalized_fuel_type' => $fuel_type,
            'service_type' => $service_type,
            'calculated_price' => $price,
            'formatted_price' => '£' . number_format($price, 2),
            'registration_formatted' => $this->format_registration($registration)
        ]);
        
        return $enhanced_data;
    }
    
    /**
     * Extract engine size from DVLA data
     * 
     * @param array $vehicle_data
     * @return int Engine size in cc
     */
    private function extract_engine_size($vehicle_data) {
        // DVLA provides engineCapacity in cc
        if (isset($vehicle_data['engineCapacity'])) {
            return intval($vehicle_data['engineCapacity']);
        }
        
        // Fallback to cylinderCapacity if available
        if (isset($vehicle_data['cylinderCapacity'])) {
            return intval($vehicle_data['cylinderCapacity']);
        }
        
        // Default to 1600cc if not available
        return 1600;
    }
    
    /**
     * Normalize fuel type from DVLA data
     * 
     * @param string $fuel_type
     * @return string
     */
    private function normalize_fuel_type($fuel_type) {
        $fuel_map = [
            'petrol' => 'petrol',
            'diesel' => 'diesel',
            'hybrid electric' => 'hybrid',
            'plug-in hybrid electric' => 'hybrid',
            'electric' => 'electric',
            'electricity' => 'electric',
            'gas' => 'petrol', // Treat gas as petrol for pricing
            'gas bi-fuel' => 'petrol'
        ];
        
        $normalized = strtolower(trim($fuel_type));
        return $fuel_map[$normalized] ?? 'petrol';
    }
    
    /**
     * Format registration for display
     * 
     * @param string $registration
     * @return string
     */
    private function format_registration($registration) {
        // Add space for readability (e.g., AB12CDE -> AB12 CDE)
        $registration = strtoupper($registration);
        if (strlen($registration) == 7) {
            return substr($registration, 0, 4) . ' ' . substr($registration, 4);
        }
        return $registration;
    }
    
    /**
     * Get cached vehicle data
     * 
     * @param string $registration
     * @return mixed False if not cached, array if cached
     */
    private function get_cached_vehicle($registration) {
        $cache_key = 'bm_vehicle_' . md5($registration);
        return get_transient($cache_key);
    }
    
    /**
     * Cache vehicle data
     * 
     * @param string $registration
     * @param array $data
     */
    private function cache_vehicle($registration, $data) {
        $cache_key = 'bm_vehicle_' . md5($registration);
        set_transient($cache_key, $data, $this->cache_duration);
    }
    
    /**
     * Clear cached vehicle data
     * 
     * @param string $registration
     */
    public function clear_cache($registration) {
        $cache_key = 'bm_vehicle_' . md5($registration);
        delete_transient($cache_key);
    }
    
    /**
     * Get mock data for testing (when API key not available)
     * 
     * @param string $registration
     * @return array
     */
    public function get_mock_vehicle($registration) {
        return [
            'registrationNumber' => strtoupper($registration),
            'make' => 'FORD',
            'model' => 'FOCUS',
            'colour' => 'BLUE',
            'fuelType' => 'PETROL',
            'engineCapacity' => 1600,
            'yearOfManufacture' => 2018,
            'motStatus' => 'Valid',
            'motExpiryDate' => '2025-08-15',
            'taxStatus' => 'Taxed',
            'taxDueDate' => '2025-09-01',
            'vehicleClass' => 'M1',
            'mock_data' => true
        ];
    }
}