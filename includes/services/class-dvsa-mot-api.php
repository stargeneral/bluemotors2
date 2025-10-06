<?php
/**
 * DVSA MOT API Integration for Blue Motors Southampton
 * Retrieves MOT history and test data from DVSA API
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

namespace BlueMotosSouthampton\Services;

class DVSAMotApi {
    
    /**
     * DVSA MOT API Base URL
     * @var string
     */
    private $api_base_url = 'https://history.mot.api.gov.uk/v1/trade/vehicles/';
    
    /**
     * OAuth Token URL
     * @var string
     */
    private $token_url;
    
    /**
     * OAuth Scope URL
     * @var string
     */
    private $scope_url;
    
    /**
     * Client ID
     * @var string
     */
    private $client_id;
    
    /**
     * Client Secret
     * @var string
     */
    private $client_secret;
    
    /**
     * API Key
     * @var string
     */
    private $api_key;
    
    /**
     * Access Token
     * @var string
     */
    private $access_token;
    
    /**
     * Token Expiry
     * @var int
     */
    private $token_expiry = 0;
    
    /**
     * Debug Mode
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
        $this->client_id = get_option('bm_dvsa_client_id');
        $this->client_secret = get_option('bm_dvsa_client_secret');
        $this->api_key = get_option('bm_dvsa_api_key');
        $this->token_url = get_option('bm_dvsa_token_url');
        $this->scope_url = get_option('bm_dvsa_scope_url');
        $this->debug_mode = get_option('bm_debug_mode', BM_DEBUG_MODE);
        
        $this->log('DVSA MOT API initialized');
    }
    
    /**
     * Get MOT history for a vehicle
     * 
     * @param string $registration Vehicle registration
     * @return array|WP_Error MOT history or error
     */
    public function get_mot_history($registration) {
        // Clean registration
        $registration = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($registration)));
        
        if (empty($registration)) {
            return new \WP_Error('invalid_registration', 'Invalid vehicle registration');
        }
        
        // Check cache first
        $cache_key = 'bm_dvsa_mot_' . md5($registration);
        $cached = get_transient($cache_key);
        
        if ($cached !== false && !$this->debug_mode) {
            $this->log("Using cached MOT data for {$registration}");
            return $cached;
        }
        
        // If no API credentials, return mock data
        if (!$this->has_required_credentials()) {
            $this->log("Missing DVSA credentials, using mock data for {$registration}");
            return $this->get_mock_mot_data($registration);
        }
        
        // Get access token
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            $this->log("Token error for {$registration}: " . $token->get_error_message(), 'error');
            return $this->get_mock_mot_data($registration);
        }
        
        // Make API request
        $response = $this->make_api_request("registration/{$registration}");
        
        if (is_wp_error($response)) {
            $this->log("API error for {$registration}: " . $response->get_error_message(), 'error');
            
            // Return mock data on error
            $mock_data = $this->get_mock_mot_data($registration);
            $mock_data['api_error'] = $response->get_error_message();
            return $mock_data;
        }
        
        // Process the response
        $processed_data = $this->process_mot_data($response, $registration);
        
        // Cache successful response
        set_transient($cache_key, $processed_data, $this->cache_duration);
        
        $this->log("Successfully retrieved MOT data for {$registration}");
        
        return $processed_data;
    }
    
    /**
     * Check if required credentials are available
     * 
     * @return bool
     */
    private function has_required_credentials() {
        return !empty($this->client_id) && 
               !empty($this->client_secret) && 
               !empty($this->api_key) && 
               !empty($this->token_url) && 
               !empty($this->scope_url);
    }
    
    /**
     * Get OAuth access token
     * 
     * @return string|WP_Error
     */
    private function get_access_token() {
        // Check if we have a valid token
        if (!empty($this->access_token) && $this->token_expiry > time()) {
            return $this->access_token;
        }
        
        $this->log('Requesting new access token');
        
        $response = wp_remote_post($this->token_url, [
            'method' => 'POST',
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'scope' => $this->scope_url
            ]
        ]);
        
        if (is_wp_error($response)) {
            return new \WP_Error(
                'dvsa_token_error',
                'Failed to get access token: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            return new \WP_Error(
                'dvsa_token_error',
                "Token request failed with HTTP {$response_code}"
            );
        }
        
        $data = json_decode($response_body, true);
        
        if (empty($data['access_token'])) {
            return new \WP_Error('dvsa_token_error', 'No access token in response');
        }
        
        // Store token and expiry
        $this->access_token = $data['access_token'];
        $this->token_expiry = time() + (intval($data['expires_in'] ?? 3600) - 60); // Subtract 60s buffer
        
        $this->log('Access token retrieved successfully');
        
        return $this->access_token;
    }
    
    /**
     * Make API request to DVSA
     * 
     * @param string $endpoint
     * @return array|WP_Error
     */
    private function make_api_request($endpoint) {
        $url = rtrim($this->api_base_url, '/') . '/' . ltrim($endpoint, '/');
        
        $this->log("Making DVSA API request to {$url}");
        
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => $this->api_key,
                'Authorization' => 'Bearer ' . $this->access_token
            ]
        ]);
        
        if (is_wp_error($response)) {
            return new \WP_Error(
                'dvsa_connection_error',
                'Failed to connect to DVSA API: ' . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $this->log("DVSA API response code: {$response_code}");
        
        switch ($response_code) {
            case 200:
                $data = json_decode($response_body, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new \WP_Error('dvsa_json_error', 'Invalid JSON response');
                }
                return $data;
                
            case 404:
                return new \WP_Error('mot_not_found', 'No MOT data found for this vehicle');
                
            case 403:
                return new \WP_Error('dvsa_forbidden', 'DVSA API access denied');
                
            case 429:
                return new \WP_Error('dvsa_rate_limit', 'DVSA API rate limit exceeded');
                
            default:
                return new \WP_Error('dvsa_error', "DVSA API error: HTTP {$response_code}");
        }
    }
    
    /**
     * Process MOT data from DVSA API
     * 
     * @param array $data Raw DVSA data
     * @param string $registration
     * @return array Processed MOT data
     */
    private function process_mot_data($data, $registration) {
        $processed = [
            'registration' => $registration,
            'make' => $data['make'] ?? 'Unknown',
            'model' => $data['model'] ?? 'Unknown',
            'manufacture_year' => $data['manufactureYear'] ?? null,
            'fuel_type' => $data['fuelType'] ?? 'Unknown',
            'engine_size' => $data['engineSize'] ?? null,
            'primary_colour' => $data['primaryColour'] ?? 'Unknown',
            'mot_tests' => [],
            'current_mot_status' => 'Unknown',
            'mot_expiry_date' => null,
            'latest_mileage' => null,
            'mileage_history' => [],
            'advisory_notices' => [],
            'defects' => [],
            'data_source' => 'dvsa_api',
            'retrieved_at' => current_time('mysql'),
            'using_mock_data' => false
        ];
        
        // Process MOT tests if available
        if (isset($data['motTests']) && is_array($data['motTests'])) {
            $processed['mot_tests'] = $data['motTests'];
            
            if (!empty($data['motTests'])) {
                $latest_test = $data['motTests'][0]; // Most recent test first
                
                // Set current MOT status
                $processed['current_mot_status'] = $latest_test['testResult'] ?? 'Unknown';
                
                // Set MOT expiry date
                if (isset($latest_test['expiryDate'])) {
                    $processed['mot_expiry_date'] = $latest_test['expiryDate'];
                }
                
                // Extract mileage information
                if (isset($latest_test['odometerValue']) && $latest_test['odometerResultType'] === 'READ') {
                    $processed['latest_mileage'] = [
                        'value' => $latest_test['odometerValue'],
                        'unit' => $latest_test['odometerUnit'] === 'MI' ? 'miles' : 'km',
                        'date' => $latest_test['completedDate']
                    ];
                }
                
                // Build mileage history from all tests
                foreach ($data['motTests'] as $test) {
                    if (isset($test['odometerValue']) && $test['odometerResultType'] === 'READ') {
                        $processed['mileage_history'][] = [
                            'value' => $test['odometerValue'],
                            'unit' => $test['odometerUnit'] === 'MI' ? 'miles' : 'km',
                            'date' => $test['completedDate'],
                            'test_result' => $test['testResult']
                        ];
                    }
                }
                
                // Extract latest defects and advisories
                if (isset($latest_test['defects']) && is_array($latest_test['defects'])) {
                    foreach ($latest_test['defects'] as $defect) {
                        if ($defect['type'] === 'ADVISORY') {
                            $processed['advisory_notices'][] = $defect;
                        } else {
                            $processed['defects'][] = $defect;
                        }
                    }
                }
            }
        }
        
        // Handle vehicles that may not need MOT yet
        if (empty($processed['mot_tests']) && isset($data['motTestDueDate'])) {
            $processed['current_mot_status'] = 'Not Required';
            $processed['mot_expiry_date'] = $data['motTestDueDate'];
        }
        
        return $processed;
    }
    
    /**
     * Generate mock MOT data for testing
     * 
     * @param string $registration
     * @return array
     */
    private function get_mock_mot_data($registration) {
        $hash = md5($registration);
        
        $test_results = ['PASSED', 'FAILED', 'PASSED', 'PASSED']; // More passes than fails
        $result_index = hexdec(substr($hash, 0, 2)) % count($test_results);
        $current_result = $test_results[$result_index];
        
        $mileage = 50000 + (hexdec(substr($hash, 2, 4)) % 100000);
        $expiry_months = ($current_result === 'PASSED') ? 6 : -2; // Passed = future, failed = past
        
        return [
            'registration' => $registration,
            'make' => 'FORD',
            'model' => 'FOCUS',
            'manufacture_year' => 2018,
            'fuel_type' => 'Petrol',
            'engine_size' => 1600,
            'primary_colour' => 'Blue',
            'current_mot_status' => $current_result,
            'mot_expiry_date' => date('Y-m-d', strtotime("+{$expiry_months} months")),
            'latest_mileage' => [
                'value' => $mileage,
                'unit' => 'miles',
                'date' => date('Y-m-d', strtotime('-1 month'))
            ],
            'mileage_history' => [
                [
                    'value' => $mileage - 12000,
                    'unit' => 'miles',
                    'date' => date('Y-m-d', strtotime('-1 year')),
                    'test_result' => 'PASSED'
                ],
                [
                    'value' => $mileage,
                    'unit' => 'miles',
                    'date' => date('Y-m-d', strtotime('-1 month')),
                    'test_result' => $current_result
                ]
            ],
            'advisory_notices' => $current_result === 'PASSED' ? [
                [
                    'type' => 'ADVISORY',
                    'text' => 'Nearside front tyre worn close to legal limit',
                    'location' => 'Front'
                ]
            ] : [],
            'defects' => $current_result === 'FAILED' ? [
                [
                    'type' => 'FAIL',
                    'text' => 'Headlamp has defective beam pattern',
                    'location' => 'Front'
                ]
            ] : [],
            'mot_tests' => [
                [
                    'completedDate' => date('Y-m-d', strtotime('-1 month')),
                    'testResult' => $current_result,
                    'expiryDate' => date('Y-m-d', strtotime("+{$expiry_months} months")),
                    'odometerValue' => $mileage,
                    'odometerUnit' => 'MI',
                    'odometerResultType' => 'READ'
                ]
            ],
            'data_source' => 'mock_data',
            'retrieved_at' => current_time('mysql'),
            'using_mock_data' => true
        ];
    }
    
    /**
     * Test DVSA API connection
     * 
     * @return bool|WP_Error
     */
    public function test_connection() {
        if (!$this->has_required_credentials()) {
            return new \WP_Error('missing_credentials', 'DVSA API credentials not configured');
        }
        
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }
        
        // Test with a registration that might not exist - we just want to verify API access
        $test_result = $this->make_api_request('registration/AB12CDE');
        
        if (is_wp_error($test_result)) {
            // If it's just "not found", the API is working
            if ($test_result->get_error_code() === 'mot_not_found') {
                return true;
            }
            return $test_result;
        }
        
        return true;
    }
    
    /**
     * Clear cached MOT data
     * 
     * @param string $registration
     */
    public function clear_cache($registration) {
        $cache_key = 'bm_dvsa_mot_' . md5(strtoupper(preg_replace('/[^A-Z0-9]/', '', $registration)));
        delete_transient($cache_key);
    }
    
    /**
     * Log messages for debugging
     * 
     * @param string $message
     * @param string $level
     */
    private function log($message, $level = 'info') {
        if ($this->debug_mode) {
            error_log("[Blue Motors DVSA API] [{$level}] {$message}");
        }
    }
}
