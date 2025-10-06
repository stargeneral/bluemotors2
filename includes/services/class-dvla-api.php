<?php
/**
 * DVLA API Integration for Blue Motors Southampton
 * Enhanced vehicle lookup with comprehensive error handling
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

namespace BlueMotosSouthampton\Services;

class DVLAApi {
    
    /**
     * DVLA API URL
     * @var string
     */
    private $api_url = 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles';
    
    /**
     * API Key
     * @var string
     */
    private $api_key;
    
    /**
     * Debug mode
     * @var bool
     */
    private $debug_mode;
    
    /**
     * Cache duration in seconds
     * @var int
     */
    private $cache_duration = 86400; // 24 hours
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = get_option('bm_dvla_api_key', BM_DVLA_API_KEY);
        $this->debug_mode = get_option('bm_debug_mode', BM_DEBUG_MODE);
    }
    
    /**
     * Lookup vehicle by registration number
     * 
     * @param string $registration Vehicle registration
     * @return array|WP_Error Vehicle data or error
     */
    public function lookup_vehicle($registration) {
        // Clean and validate registration
        $registration = $this->clean_registration($registration);
        
        if (!$this->is_valid_registration($registration)) {
            return new \WP_Error('invalid_registration', 'Invalid UK vehicle registration format');
        }
        
        // Check cache first
        $cache_key = 'bm_dvla_' . md5($registration);
        $cached = get_transient($cache_key);
        
        if ($cached !== false && !$this->debug_mode) {
            $this->log("Using cached data for {$registration}");
            return $cached;
        }
        
        // If no API key configured, return mock data
        if (empty($this->api_key)) {
            $this->log("No API key configured, using mock data for {$registration}");
            return $this->get_mock_vehicle_data($registration);
        }
        
        // Make API request
        $response = $this->make_api_request($registration);
        
        if (is_wp_error($response)) {
            $this->log("API error for {$registration}: " . $response->get_error_message(), 'error');
            
            // Return mock data on API failure for better user experience
            $mock_data = $this->get_mock_vehicle_data($registration);
            $mock_data['api_error'] = $response->get_error_message();
            $mock_data['using_mock_data'] = true;
            
            return $mock_data;
        }
        
        // Process and enhance the response
        $processed_data = $this->process_vehicle_data($response, $registration);
        
        // Cache successful response
        set_transient($cache_key, $processed_data, $this->cache_duration);
        
        $this->log("Successfully retrieved data for {$registration}");
        
        return $processed_data;
    }
    
    /**
     * Make API request to DVLA
     * 
     * @param string $registration
     * @return array|WP_Error
     */
    private function make_api_request($registration) {
        $this->log("Making DVLA API request for {$registration}");
        
        $response = wp_remote_post($this->api_url, [
            'headers' => [
                'x-api-key' => $this->api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => json_encode([
                'registrationNumber' => $registration
            ]),
            'timeout' => 15,
            'sslverify' => true
        ]);
        
        // Check for connection errors
        if (is_wp_error($response)) {
            return new \WP_Error(
                'dvla_connection_error',
                'Failed to connect to DVLA API: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $this->log("DVLA API response code: {$response_code}");
        
        // Handle different response codes
        switch ($response_code) {
            case 200:
                $data = json_decode($response_body, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new \WP_Error('dvla_json_error', 'Invalid JSON response from DVLA API');
                }
                return $data;
                
            case 404:
                return new \WP_Error('vehicle_not_found', 'Vehicle not found in DVLA database');
                
            case 403:
                return new \WP_Error('dvla_forbidden', 'DVLA API access denied - check API key');
                
            case 429:
                return new \WP_Error('dvla_rate_limit', 'DVLA API rate limit exceeded');
                
            case 500:
            case 502:
            case 503:
                return new \WP_Error('dvla_server_error', 'DVLA API server error');
                
            default:
                return new \WP_Error('dvla_unknown_error', "DVLA API error: HTTP {$response_code}");
        }
    }
    
    /**
     * Process and enhance DVLA vehicle data
     * 
     * @param array $data Raw DVLA data
     * @param string $registration
     * @return array Enhanced vehicle data
     */
    private function process_vehicle_data($data, $registration) {
        return [
            'registration' => $registration,
            'registration_formatted' => $this->format_registration($registration),
            'make' => $data['make'] ?? 'Unknown',
            'model' => $data['model'] ?? 'Unknown',
            'colour' => $data['colour'] ?? 'Unknown',
            'fuel_type' => $data['fuelType'] ?? 'Unknown',
            'fuel_type_normalized' => $this->normalize_fuel_type($data['fuelType'] ?? 'petrol'),
            'engine_capacity' => intval($data['engineCapacity'] ?? 1600),
            'year_of_manufacture' => intval($data['yearOfManufacture'] ?? date('Y')),
            'vehicle_class' => $data['vehicleClass'] ?? 'Unknown',
            'date_of_last_v5_issued' => $data['dateOfLastV5CIssued'] ?? null,
            'mot_status' => $data['motStatus'] ?? 'Unknown',
            'mot_expiry_date' => $data['motExpiryDate'] ?? null,
            'tax_status' => $data['taxStatus'] ?? 'Unknown',
            'tax_due_date' => $data['taxDueDate'] ?? null,
            'co2_emissions' => $data['co2Emissions'] ?? null,
            'marked_for_export' => $data['markedForExport'] ?? false,
            'euroStatus' => $data['euroStatus'] ?? null,
            'real_driving_emissions' => $data['realDrivingEmissions'] ?? null,
            'wheelplan' => $data['wheelplan'] ?? null,
            'revenue_weight' => $data['revenueWeight'] ?? null,
            'data_source' => 'dvla_api',
            'retrieved_at' => current_time('mysql'),
            'using_mock_data' => false
        ];
    }
    
    /**
     * Generate mock vehicle data for testing/fallback
     * 
     * @param string $registration
     * @return array
     */
    private function get_mock_vehicle_data($registration) {
        // Generate deterministic but realistic mock data based on registration
        $hash = md5($registration);
        
        $makes = ['FORD', 'VOLKSWAGEN', 'BMW', 'AUDI', 'MERCEDES-BENZ', 'TOYOTA', 'VAUXHALL', 'NISSAN'];
        $models = ['FOCUS', 'GOLF', '3 SERIES', 'A4', 'C-CLASS', 'COROLLA', 'ASTRA', 'QASHQAI'];
        $colours = ['BLUE', 'BLACK', 'WHITE', 'SILVER', 'RED', 'GREY'];
        $fuel_types = ['PETROL', 'DIESEL', 'HYBRID ELECTRIC', 'ELECTRIC'];
        $engine_sizes = [1000, 1200, 1400, 1600, 1800, 2000, 2500];
        
        $make_index = hexdec(substr($hash, 0, 2)) % count($makes);
        $model_index = hexdec(substr($hash, 2, 2)) % count($models);
        $colour_index = hexdec(substr($hash, 4, 2)) % count($colours);
        $fuel_index = hexdec(substr($hash, 6, 2)) % count($fuel_types);
        $engine_index = hexdec(substr($hash, 8, 2)) % count($engine_sizes);
        
        $year = 2015 + (hexdec(substr($hash, 10, 2)) % 9); // 2015-2023
        
        return [
            'registration' => $registration,
            'registration_formatted' => $this->format_registration($registration),
            'make' => $makes[$make_index],
            'model' => $models[$model_index],
            'colour' => $colours[$colour_index],
            'fuel_type' => $fuel_types[$fuel_index],
            'fuel_type_normalized' => $this->normalize_fuel_type($fuel_types[$fuel_index]),
            'engine_capacity' => $engine_sizes[$engine_index],
            'year_of_manufacture' => $year,
            'vehicle_class' => 'M1',
            'mot_status' => 'Valid',
            'mot_expiry_date' => date('Y-m-d', strtotime('+6 months')),
            'tax_status' => 'Taxed',
            'tax_due_date' => date('Y-m-d', strtotime('+3 months')),
            'co2_emissions' => 120 + (hexdec(substr($hash, 12, 2)) % 80), // 120-200
            'marked_for_export' => false,
            'data_source' => 'mock_data',
            'retrieved_at' => current_time('mysql'),
            'using_mock_data' => true
        ];
    }
    
    /**
     * Clean registration number
     * 
     * @param string $registration
     * @return string
     */
    private function clean_registration($registration) {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($registration)));
    }
    
    /**
     * Validate UK registration format
     * 
     * @param string $registration
     * @return bool
     */
    private function is_valid_registration($registration) {
        // Basic UK registration patterns
        $patterns = [
            '/^[A-Z]{2}[0-9]{2}[A-Z]{3}$/', // Current format: AB12CDE
            '/^[A-Z][0-9]{1,3}[A-Z]{3}$/',  // Prefix format: A123BCD
            '/^[A-Z]{3}[0-9]{1,3}[A-Z]$/',  // Suffix format: ABC123D
            '/^[0-9]{1,4}[A-Z]{1,3}$/'      // Dateless format: 1234AB];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $registration)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Format registration for display
     * 
     * @param string $registration
     * @return string
     */
    private function format_registration($registration) {
        if (strlen($registration) === 7) {
            // Current format: AB12CDE -> AB12 CDE
            return substr($registration, 0, 4) . ' ' . substr($registration, 4);
        }
        
        return $registration;
    }
    
    /**
     * Normalize fuel type for pricing calculations
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
            'gas' => 'petrol',
            'gas bi-fuel' => 'petrol'
        ];
        
        $normalized = strtolower(trim($fuel_type));
        return $fuel_map[$normalized] ?? 'petrol';
    }
    
    /**
     * Clear cached vehicle data
     * 
     * @param string $registration
     */
    public function clear_cache($registration) {
        $cache_key = 'bm_dvla_' . md5($this->clean_registration($registration));
        delete_transient($cache_key);
    }
    
    /**
     * Test API connection
     * 
     * @return bool|WP_Error
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return new \WP_Error('no_api_key', 'DVLA API key not configured');
        }
        
        // Test with a known registration that should exist
        $test_result = $this->make_api_request('AB12CDE');
        
        if (is_wp_error($test_result)) {
            // If it's just "vehicle not found", the API is working
            if ($test_result->get_error_code() === 'vehicle_not_found') {
                return true;
            }
            return $test_result;
        }
        
        return true;
    }
    
    /**
     * Log messages for debugging
     * 
     * @param string $message
     * @param string $level
     */
    private function log($message, $level = 'info') {
        if ($this->debug_mode) {
            error_log("[Blue Motors DVLA API] [{$level}] {$message}");
        }
    }
}
