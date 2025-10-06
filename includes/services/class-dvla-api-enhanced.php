<?php
/**
 * Enhanced DVLA API Integration for Blue Motors Southampton
 * Combines the best features from the original plugin with improved error handling
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

namespace BlueMotosSouthampton\Services;

class DVLAApiEnhanced {
    
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
    private $cache_duration;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = get_option('bm_dvla_api_key', BM_DVLA_API_KEY);
        $this->debug_mode = get_option('bm_debug_mode', BM_DEBUG_MODE);
        $this->cache_duration = get_option('bm_api_cache_duration', 86400);
        
        $this->log('DVLA API Enhanced initialized');
    }
    
    /**
     * Lookup vehicle by registration number with enhanced error handling
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
        $cache_key = 'bm_dvla_enhanced_' . md5($registration);
        $cached = get_transient($cache_key);
        
        if ($cached !== false && !$this->debug_mode) {
            $this->log("Using cached enhanced data for {$registration}");
            return $cached;
        }
        
        // Check if API key is configured and valid (not placeholder)
        if (empty($this->api_key) || $this->is_placeholder_api_key($this->api_key)) {
            $this->log("No valid API key configured, using enhanced mock data for {$registration}");
            return $this->get_enhanced_mock_vehicle_data($registration);
        }
        
        // Make API request with retry logic
        $response = $this->make_api_request_with_retry($registration);
        
        if (is_wp_error($response)) {
            $this->log("API error for {$registration}: " . $response->get_error_message(), 'error');
            
            // Return enhanced mock data on API failure for better user experience
            $mock_data = $this->get_enhanced_mock_vehicle_data($registration);
            $mock_data['api_error'] = $response->get_error_message();
            $mock_data['using_mock_data'] = true;
            
            return $mock_data;
        }
        
        // Process and enhance the response
        $processed_data = $this->process_enhanced_vehicle_data($response, $registration);
        
        // Cache successful response
        set_transient($cache_key, $processed_data, $this->cache_duration);
        
        $this->log("Successfully retrieved enhanced data for {$registration}");
        
        return $processed_data;
    }
    
    /**
     * Make API request with retry logic and enhanced error handling
     * 
     * @param string $registration
     * @param int $retry_count
     * @return array|WP_Error
     */
    private function make_api_request_with_retry($registration, $retry_count = 0) {
        $max_retries = 2;
        
        $this->log("Making DVLA API request for {$registration} (attempt " . ($retry_count + 1) . ")");
        
        $response = wp_remote_post($this->api_url, [
            'headers' => [
                'x-api-key' => $this->api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Blue Motors Southampton/1.0'
            ],
            'body' => json_encode([
                'registrationNumber' => $registration
            ]),
            'timeout' => 15,
            'sslverify' => true,
            'redirection' => 5,
            'httpversion' => '1.1'
        ]);
        
        // Check for connection errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            
            // Retry on connection errors if we haven't reached max retries
            if ($retry_count < $max_retries && $this->is_retryable_error($response)) {
                $this->log("Retryable error occurred, retrying... ({$error_message})");
                sleep(1); // Brief delay before retry
                return $this->make_api_request_with_retry($registration, $retry_count + 1);
            }
            
            return new \WP_Error(
                'dvla_connection_error',
                'Failed to connect to DVLA API after ' . ($retry_count + 1) . ' attempts: ' . $error_message
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $this->log("DVLA API response code: {$response_code}");
        
        // Enhanced error handling based on response codes
        switch ($response_code) {
            case 200:
                $data = json_decode($response_body, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new \WP_Error('dvla_json_error', 'Invalid JSON response from DVLA API');
                }
                return $data;
                
            case 400:
                return new \WP_Error('dvla_bad_request', 'Invalid request format - check registration number');
                
            case 403:
                return new \WP_Error('dvla_forbidden', 'DVLA API access denied - check API key permissions');
                
            case 404:
                return new \WP_Error('vehicle_not_found', 'Vehicle not found in DVLA database');
                
            case 429:
                // Rate limit - retry after delay if we haven't reached max retries
                if ($retry_count < $max_retries) {
                    $this->log("Rate limit hit, retrying after delay...");
                    sleep(2);
                    return $this->make_api_request_with_retry($registration, $retry_count + 1);
                }
                return new \WP_Error('dvla_rate_limit', 'DVLA API rate limit exceeded - try again later');
                
            case 500:
            case 502:
            case 503:
                // Server errors - retry if we haven't reached max retries
                if ($retry_count < $max_retries) {
                    $this->log("Server error {$response_code}, retrying...");
                    sleep(2);
                    return $this->make_api_request_with_retry($registration, $retry_count + 1);
                }
                return new \WP_Error('dvla_server_error', "DVLA API server error: HTTP {$response_code}");
                
            default:
                return new \WP_Error('dvla_unknown_error', "DVLA API error: HTTP {$response_code}");
        }
    }
    
    /**
     * Check if error is retryable
     * 
     * @param WP_Error $error
     * @return bool
     */
    private function is_retryable_error($error) {
        $retryable_codes = ['timeout', 'connection_error', 'http_request_failed'];
        return in_array($error->get_error_code(), $retryable_codes);
    }
    
    /**
     * Process and enhance DVLA vehicle data with additional fields
     * 
     * @param array $data Raw DVLA data
     * @param string $registration
     * @return array Enhanced vehicle data
     */
    private function process_enhanced_vehicle_data($data, $registration) {
        $processed = [
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
            'euro_status' => $data['euroStatus'] ?? null,
            'real_driving_emissions' => $data['realDrivingEmissions'] ?? null,
            'wheelplan' => $data['wheelplan'] ?? null,
            'revenue_weight' => $data['revenueWeight'] ?? null,
            
            // Enhanced fields
            'estimated_tyre_size' => $this->estimate_tyre_size($data),
            'vehicle_category' => $this->categorize_vehicle($data),
            'is_commercial' => $this->is_commercial_vehicle($data),
            'age_category' => $this->get_age_category($data['yearOfManufacture'] ?? date('Y')),
            'insurance_group_estimate' => $this->estimate_insurance_group($data),
            'recommended_service_category' => $this->recommend_service_category($data),
            
            // Meta data
            'data_source' => 'dvla_api_enhanced',
            'retrieved_at' => current_time('mysql'),
            'using_mock_data' => false,
            'cache_expires_at' => date('Y-m-d H:i:s', time() + $this->cache_duration)
        ];
        
        return $processed;
    }
    
    /**
     * Generate enhanced mock vehicle data with realistic patterns
     * 
     * @param string $registration
     * @return array
     */
    private function get_enhanced_mock_vehicle_data($registration) {
        // Generate deterministic but realistic mock data based on registration
        $hash = md5($registration);
        
        $vehicle_data = [
            'make' => $this->get_mock_make($hash),
            'model' => $this->get_mock_model($hash),
            'colour' => $this->get_mock_colour($hash),
            'fuel_type' => $this->get_mock_fuel_type($hash),
            'engine_capacity' => $this->get_mock_engine_capacity($hash),
            'year_of_manufacture' => $this->get_mock_year($hash),
            'vehicle_class' => 'M1'
        ];
        
        return [
            'registration' => $registration,
            'registration_formatted' => $this->format_registration($registration),
            'make' => $vehicle_data['make'],
            'model' => $vehicle_data['model'],
            'colour' => $vehicle_data['colour'],
            'fuel_type' => $vehicle_data['fuel_type'],
            'fuel_type_normalized' => $this->normalize_fuel_type($vehicle_data['fuel_type']),
            'engine_capacity' => $vehicle_data['engine_capacity'],
            'year_of_manufacture' => $vehicle_data['year_of_manufacture'],
            'vehicle_class' => $vehicle_data['vehicle_class'],
            'mot_status' => $this->get_mock_mot_status($hash),
            'mot_expiry_date' => $this->get_mock_mot_expiry($hash),
            'tax_status' => $this->get_mock_tax_status($hash),
            'tax_due_date' => $this->get_mock_tax_due($hash),
            'co2_emissions' => $this->get_mock_co2_emissions($vehicle_data),
            'marked_for_export' => false,
            
            // Enhanced mock fields
            'estimated_tyre_size' => $this->estimate_tyre_size($vehicle_data),
            'vehicle_category' => $this->categorize_vehicle($vehicle_data),
            'is_commercial' => $this->is_commercial_vehicle($vehicle_data),
            'age_category' => $this->get_age_category($vehicle_data['year_of_manufacture']),
            'insurance_group_estimate' => $this->estimate_insurance_group($vehicle_data),
            'recommended_service_category' => $this->recommend_service_category($vehicle_data),
            
            // Meta data
            'data_source' => 'mock_data_enhanced',
            'retrieved_at' => current_time('mysql'),
            'using_mock_data' => true,
            'cache_expires_at' => null
        ];
    }
    
    /**
     * Estimate tyre size based on vehicle data
     */
    private function estimate_tyre_size($vehicle_data) {
        $make = strtoupper($vehicle_data['make'] ?? '');
        $model = strtoupper($vehicle_data['model'] ?? '');
        $year = intval($vehicle_data['year_of_manufacture'] ?? date('Y'));
        $engine = intval($vehicle_data['engine_capacity'] ?? 1600);
        
        // Base sizes by vehicle type
        $base_sizes = [
            'small' => ['width' => 175, 'profile' => 65, 'rim' => 14],
            'medium' => ['width' => 195, 'profile' => 60, 'rim' => 15],
            'large' => ['width' => 215, 'profile' => 55, 'rim' => 16],
            'premium' => ['width' => 225, 'profile' => 50, 'rim' => 17],
            'luxury' => ['width' => 245, 'profile' => 45, 'rim' => 18]
        ];
        
        // Determine category
        $category = 'medium'; // Default
        
        if (in_array($make, ['BMW', 'AUDI', 'MERCEDES-BENZ', 'JAGUAR', 'LEXUS'])) {
            $category = ($year > 2015) ? 'luxury' : 'premium';
        } elseif (stripos($model, 'SUV') !== false || $engine > 2000) {
            $category = 'large';
        } elseif (in_array($make, ['FORD', 'VAUXHALL']) && $engine < 1400) {
            $category = 'small';
        }
        
        $size = $base_sizes[$category];
        
        // Add some variation based on year
        if ($year > 2018) {
            $size['rim'] += 1;
            $size['profile'] -= 5;
        }
        
        return "{$size['width']}/{$size['profile']}R{$size['rim']}";
    }
    
    /**
     * Categorize vehicle for service recommendations
     */
    private function categorize_vehicle($vehicle_data) {
        $make = strtoupper($vehicle_data['make'] ?? '');
        $engine = intval($vehicle_data['engine_capacity'] ?? 1600);
        $year = intval($vehicle_data['year_of_manufacture'] ?? date('Y'));
        
        if (in_array($make, ['BMW', 'AUDI', 'MERCEDES-BENZ', 'JAGUAR', 'LEXUS', 'PORSCHE'])) {
            return 'premium';
        } elseif ($engine > 2500 || $year < 2010) {
            return 'heavy_duty';
        } elseif ($engine < 1200) {
            return 'economy';
        } else {
            return 'standard';
        }
    }
    
    /**
     * Check if vehicle is commercial
     */
    private function is_commercial_vehicle($vehicle_data) {
        $class = $vehicle_data['vehicle_class'] ?? '';
        $model = strtoupper($vehicle_data['model'] ?? '');
        
        // Commercial vehicle classes
        if (in_array($class, ['N1', 'N2', 'N3', 'L2', 'L3', 'L4', 'L5'])) {
            return true;
        }
        
        // Common commercial models
        $commercial_models = ['TRANSIT', 'SPRINTER', 'VIVARO', 'MASTER', 'CRAFTER', 'DUCATO'];
        foreach ($commercial_models as $commercial) {
            if (stripos($model, $commercial) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get age category for vehicle
     */
    private function get_age_category($year) {
        $age = date('Y') - intval($year);
        
        if ($age <= 3) return 'new';
        if ($age <= 7) return 'young';
        if ($age <= 15) return 'mature';
        return 'classic';
    }
    
    /**
     * Estimate insurance group
     */
    private function estimate_insurance_group($vehicle_data) {
        $make = strtoupper($vehicle_data['make'] ?? '');
        $engine = intval($vehicle_data['engine_capacity'] ?? 1600);
        
        // Base group by engine size
        $base_group = min(50, max(1, floor($engine / 200) + 5));
        
        // Adjust for make
        if (in_array($make, ['BMW', 'AUDI', 'MERCEDES-BENZ'])) {
            $base_group += 10;
        } elseif (in_array($make, ['FORD', 'VAUXHALL', 'TOYOTA'])) {
            $base_group -= 5;
        }
        
        return max(1, min(50, $base_group));
    }
    
    /**
     * Recommend service category based on vehicle data
     */
    private function recommend_service_category($vehicle_data) {
        $category = $this->categorize_vehicle($vehicle_data);
        $fuel_type = $this->normalize_fuel_type($vehicle_data['fuel_type'] ?? 'petrol');
        $age = date('Y') - intval($vehicle_data['year_of_manufacture'] ?? date('Y'));
        
        if ($category === 'premium') {
            return 'premium_service';
        } elseif ($fuel_type === 'hybrid' || $fuel_type === 'electric') {
            return 'specialist_service';
        } elseif ($age > 10) {
            return 'comprehensive_service';
        } else {
            return 'standard_service';
        }
    }
    
    // Mock data generation methods
    private function get_mock_make($hash) {
        $makes = ['FORD', 'VOLKSWAGEN', 'BMW', 'AUDI', 'MERCEDES-BENZ', 'TOYOTA', 'VAUXHALL', 'NISSAN', 'HYUNDAI', 'KIA'];
        return $makes[hexdec(substr($hash, 0, 2)) % count($makes)];
    }
    
    private function get_mock_model($hash) {
        $models = ['FOCUS', 'GOLF', '3 SERIES', 'A4', 'C-CLASS', 'COROLLA', 'ASTRA', 'QASHQAI', 'I30', 'CEED'];
        return $models[hexdec(substr($hash, 2, 2)) % count($models)];
    }
    
    private function get_mock_colour($hash) {
        $colours = ['BLUE', 'BLACK', 'WHITE', 'SILVER', 'RED', 'GREY', 'GREEN', 'YELLOW'];
        return $colours[hexdec(substr($hash, 4, 2)) % count($colours)];
    }
    
    private function get_mock_fuel_type($hash) {
        $fuel_types = ['PETROL', 'DIESEL', 'HYBRID ELECTRIC', 'ELECTRIC', 'PETROL', 'DIESEL']; // Weight towards common types
        return $fuel_types[hexdec(substr($hash, 6, 2)) % count($fuel_types)];
    }
    
    private function get_mock_engine_capacity($hash) {
        $engine_sizes = [1000, 1200, 1400, 1600, 1800, 2000, 2500, 3000];
        return $engine_sizes[hexdec(substr($hash, 8, 2)) % count($engine_sizes)];
    }
    
    private function get_mock_year($hash) {
        return 2015 + (hexdec(substr($hash, 10, 2)) % 9); // 2015-2023
    }
    
    private function get_mock_mot_status($hash) {
        $statuses = ['Valid', 'Valid', 'Valid', 'Not valid']; // Weight towards valid
        return $statuses[hexdec(substr($hash, 12, 1)) % count($statuses)];
    }
    
    private function get_mock_mot_expiry($hash) {
        $months = (hexdec(substr($hash, 13, 2)) % 12) - 2; // -2 to +10 months
        return date('Y-m-d', strtotime("+{$months} months"));
    }
    
    private function get_mock_tax_status($hash) {
        $statuses = ['Taxed', 'Taxed', 'Taxed', 'SORN']; // Weight towards taxed
        return $statuses[hexdec(substr($hash, 14, 1)) % count($statuses)];
    }
    
    private function get_mock_tax_due($hash) {
        $months = (hexdec(substr($hash, 15, 2)) % 11) + 1; // 1-12 months
        return date('Y-m-d', strtotime("+{$months} months"));
    }
    
    private function get_mock_co2_emissions($vehicle_data) {
        $base_co2 = 120;
        $engine = intval($vehicle_data['engine_capacity'] ?? 1600);
        $fuel_type = $this->normalize_fuel_type($vehicle_data['fuel_type'] ?? 'petrol');
        
        // Adjust by engine size
        $co2 = $base_co2 + (($engine - 1600) / 100 * 10);
        
        // Adjust by fuel type
        if ($fuel_type === 'diesel') {
            $co2 -= 20;
        } elseif ($fuel_type === 'hybrid') {
            $co2 -= 40;
        } elseif ($fuel_type === 'electric') {
            $co2 = 0;
        }
        
        return max(0, intval($co2));
    }
    
    // Utility methods from original implementation
    private function clean_registration($registration) {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($registration)));
    }
    
    private function is_valid_registration($registration) {
        $patterns = [
            '/^[A-Z]{2}[0-9]{2}[A-Z]{3}$/', // Current format: AB12CDE
            '/^[A-Z][0-9]{1,3}[A-Z]{3}$/',  // Prefix format: A123BCD
            '/^[A-Z]{3}[0-9]{1,3}[A-Z]$/',  // Suffix format: ABC123D
            '/^[0-9]{1,4}[A-Z]{1,3}$/'      // Dateless format: 1234AB]
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $registration)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function format_registration($registration) {
        if (strlen($registration) === 7) {
            return substr($registration, 0, 4) . ' ' . substr($registration, 4);
        }
        return $registration;
    }
    
    private function normalize_fuel_type($fuel_type) {
        $normalized = strtolower(trim($fuel_type));
        
        // Check for hybrid patterns first (most specific)
        if (strpos($normalized, 'hybrid') !== false || strpos($normalized, 'plug-in') !== false) {
            return 'hybrid';
        }
        
        // Check for electric patterns
        if (strpos($normalized, 'electric') !== false && strpos($normalized, 'hybrid') === false) {
            return 'electric';
        }
        
        // Check for diesel patterns
        if (strpos($normalized, 'diesel') !== false) {
            return 'diesel';
        }
        
        // Check for gas patterns
        if (strpos($normalized, 'gas') !== false || strpos($normalized, 'lpg') !== false) {
            return 'gas';
        }
        
        // Default to petrol for anything else (including 'petrol', 'gasoline', etc.)
        return 'petrol';
    }
    
    public function clear_cache($registration) {
        $cache_key = 'bm_dvla_enhanced_' . md5($this->clean_registration($registration));
        delete_transient($cache_key);
    }
    
    public function test_connection() {
        if (empty($this->api_key)) {
            return new \WP_Error('no_api_key', 'DVLA API key not configured');
        }
        
        $test_result = $this->make_api_request_with_retry('AB12CDE');
        
        if (is_wp_error($test_result)) {
            if ($test_result->get_error_code() === 'vehicle_not_found') {
                return true;
            }
            return $test_result;
        }
        
        return true;
    }
    
    /**
     * Check if API key is a placeholder value
     * 
     * @param string $api_key
     * @return bool
     */
    private function is_placeholder_api_key($api_key) {
        $placeholder_patterns = [
            'keycovered-for-security',
            'your-api-key-here',
            'placeholder',
            'test-key',
            'dummy-key',
            'fake-key',
            'example-key'
        ];
        
        $api_key_lower = strtolower(trim($api_key));
        
        foreach ($placeholder_patterns as $pattern) {
            if (strpos($api_key_lower, $pattern) !== false) {
                return true;
            }
        }
        
        // Check if it's too short to be a real API key
        if (strlen($api_key) < 20) {
            return true;
        }
        
        return false;
    }
    
    private function log($message, $level = 'info') {
        if ($this->debug_mode) {
            error_log("[Blue Motors DVLA Enhanced] [{$level}] {$message}");
        }
    }
}
