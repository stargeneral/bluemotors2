<?php
/**
 * Enhanced DVSA MOT API Integration for Blue Motors Southampton
 * Based on the comprehensive implementation from the original Blue Motors plugin
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

namespace BlueMotosSouthampton\Services;

class DVSAMotApiEnhanced {
    
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
    private $cache_duration;
    
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
        $this->cache_duration = get_option('bm_api_cache_duration', 86400);
        
        $this->log('DVSA MOT API Enhanced initialized');
        $this->log_credentials_status();
    }
    
    /**
     * Log credentials status for debugging
     */
    private function log_credentials_status() {
        $this->log('Client ID set: ' . (!empty($this->client_id) ? 'Yes' : 'No'));
        $this->log('Client Secret set: ' . (!empty($this->client_secret) ? 'Yes' : 'No'));
        $this->log('API Key set: ' . (!empty($this->api_key) ? 'Yes' : 'No'));
        $this->log('Token URL set: ' . (!empty($this->token_url) ? 'Yes' : 'No'));
        $this->log('Scope URL set: ' . (!empty($this->scope_url) ? 'Yes' : 'No'));
    }
    
    /**
     * Get MOT history for a vehicle with enhanced error handling
     * 
     * @param string $registration Vehicle registration
     * @return array|WP_Error MOT history or error
     */
    public function get_mot_history($registration) {
        // Clean registration
        $registration = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($registration)));
        
        if (empty($registration)) {
            return new \WP_Error('invalid_registration', 'Invalid vehicle registration number');
        }
        
        // Check cache first
        $cache_key = 'bm_dvsa_mot_enhanced_' . md5($registration);
        $cached = get_transient($cache_key);
        
        if ($cached !== false && !$this->debug_mode) {
            $this->log("Using cached enhanced MOT data for {$registration}");
            return $cached;
        }
        
        // If no API credentials, return enhanced mock data
        if (!$this->has_required_credentials()) {
            $this->log("Missing DVSA credentials, using enhanced mock data for {$registration}");
            return $this->get_enhanced_mock_mot_data($registration);
        }
        
        // Get access token with enhanced error handling
        $token = $this->get_access_token_enhanced();
        if (is_wp_error($token)) {
            $this->log("Token error for {$registration}: " . $token->get_error_message(), 'error');
            
            // Return enhanced mock data with error info
            $mock_data = $this->get_enhanced_mock_mot_data($registration);
            $mock_data['token_error'] = $token->get_error_message();
            return $mock_data;
        }
        
        // Make API request with retry logic
        $response = $this->make_api_request_enhanced("registration/{$registration}");
        
        if (is_wp_error($response)) {
            $this->log("API error for {$registration}: " . $response->get_error_message(), 'error');
            
            // Return enhanced mock data on error
            $mock_data = $this->get_enhanced_mock_mot_data($registration);
            $mock_data['api_error'] = $response->get_error_message();
            return $mock_data;
        }
        
        // Process the response with enhanced data
        $processed_data = $this->process_enhanced_mot_data($response, $registration);
        
        // Cache successful response
        set_transient($cache_key, $processed_data, $this->cache_duration);
        
        $this->log("Successfully retrieved enhanced MOT data for {$registration}");
        
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
     * Get OAuth access token with enhanced error handling and connectivity testing
     * 
     * @return string|WP_Error
     */
    private function get_access_token_enhanced() {
        // Check if we have a valid token
        if (!empty($this->access_token) && $this->token_expiry > time()) {
            $this->log('Using existing access token');
            return $this->access_token;
        }
        
        // Check credentials first
        $missing = [];
        if (empty($this->client_id)) $missing[] = 'Client ID';
        if (empty($this->client_secret)) $missing[] = 'Client Secret';
        if (empty($this->token_url)) $missing[] = 'Token URL';
        if (empty($this->scope_url)) $missing[] = 'Scope URL';
        
        if (!empty($missing)) {
            $this->log('Missing credentials: ' . implode(', ', $missing), 'error');
            return new \WP_Error(
                'missing_credentials',
                'Missing DVSA MOT API credentials: ' . implode(', ', $missing)
            );
        }
        
        $this->log('Requesting new access token');
        
        // Test general connectivity first
        if (!$this->test_connectivity()) {
            return new \WP_Error(
                'connectivity_error',
                'Network connectivity issue - unable to reach external services'
            );
        }
        
        $response = wp_remote_post($this->token_url, [
            'method' => 'POST',
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => 'Blue Motors Southampton/1.0'
            ],
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'scope' => $this->scope_url
            ],
            'sslverify' => true
        ]);
        
        // Check for connection errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $error_code = $response->get_error_code();
            
            $this->log('Token request error code: ' . $error_code, 'error');
            $this->log('Token request error message: ' . $error_message, 'error');
            
            // Handle DNS resolution issues specifically
            if ($error_code === 6 || strpos($error_message, 'Could not resolve host') !== false) {
                return new \WP_Error(
                    'dvsa_dns_error',
                    'Cannot resolve DVSA API host. Please check your network connectivity and DNS settings.',
                    ['original_error' => $error_message]
                );
            }
            
            return new \WP_Error(
                'dvsa_token_connection_error',
                'Failed to connect to DVSA token service: ' . $error_message,
                ['error_code' => $error_code]
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $this->log("Token response code: {$response_code}");
        
        // Enhanced error handling for token response
        if ($response_code < 200 || $response_code >= 300) {
            $error_data = json_decode($response_body, true);
            $error_message = $this->extract_error_message($error_data);
            
            $this->log('Token error: ' . $error_message, 'error');
            
            return new \WP_Error(
                'dvsa_token_error_' . $response_code,
                "Error retrieving DVSA access token: {$error_message}",
                ['status' => $response_code, 'response' => $error_data]
            );
        }
        
        // Decode response body
        $data = json_decode($response_body, true);
        
        // Check for JSON decode error
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $json_error = json_last_error_msg();
            $this->log('Token JSON decode error: ' . $json_error, 'error');
            
            return new \WP_Error(
                'dvsa_token_json_error',
                "Error decoding token response: {$json_error}",
                ['raw_response' => $response_body]
            );
        }
        
        // Check if token is in the response
        if (empty($data['access_token'])) {
            $this->log('No access token in response', 'error');
            
            return new \WP_Error(
                'dvsa_no_access_token',
                'No access token in response',
                ['response' => $data]
            );
        }
        
        // Store token and expiry with buffer
        $this->access_token = $data['access_token'];
        $expires_in = isset($data['expires_in']) ? intval($data['expires_in']) : 3600;
        $this->token_expiry = time() + $expires_in - 60; // 60 second buffer
        
        $this->log('Access token retrieved successfully (expires in ' . $expires_in . ' seconds)');
        
        return $this->access_token;
    }
    
    /**
     * Test general network connectivity
     * 
     * @return bool
     */
    private function test_connectivity() {
        $this->log('Testing general network connectivity');
        
        $response = wp_remote_get('https://www.google.com', [
            'timeout' => 5,
            'sslverify' => true,
        ]);
        
        if (is_wp_error($response)) {
            $this->log('Connectivity test failed: ' . $response->get_error_message(), 'error');
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $this->log('Connectivity test response code: ' . $response_code);
        
        return $response_code > 0;
    }
    
    /**
     * Make API request to DVSA with enhanced error handling and retry logic
     * 
     * @param string $endpoint
     * @param int $retry_count
     * @return array|WP_Error
     */
    private function make_api_request_enhanced($endpoint, $retry_count = 0) {
        $max_retries = 2;
        
        // Check if API key is set
        if (empty($this->api_key)) {
            return new \WP_Error('missing_api_key', 'DVSA MOT API key is not set');
        }
        
        $url = rtrim($this->api_base_url, '/') . '/' . ltrim($endpoint, '/');
        
        $this->log("Making DVSA API request to {$url} (attempt " . ($retry_count + 1) . ")");
        
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => $this->api_key,
                'Authorization' => 'Bearer ' . $this->access_token,
                'User-Agent' => 'Blue Motors Southampton MOT API Client/1.0'
            ],
            'sslverify' => true
        ]);
        
        return $this->handle_api_response($response, $endpoint, $retry_count, $max_retries);
    }
    
    /**
     * Handle API response with comprehensive error handling
     * 
     * @param mixed $response
     * @param string $endpoint
     * @param int $retry_count
     * @param int $max_retries
     * @return array|WP_Error
     */
    private function handle_api_response($response, $endpoint, $retry_count, $max_retries) {
        // Check for connection errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $error_code = $response->get_error_code();
            
            $this->log('Request error code: ' . $error_code, 'error');
            $this->log('Request error message: ' . $error_message, 'error');
            
            // Handle DNS resolution issues
            if ($error_code === 6 || strpos($error_message, 'Could not resolve host') !== false) {
                $this->log('DNS resolution issue detected', 'error');
                
                $connectivity_test = $this->test_connectivity();
                $this->log('Network connectivity test result: ' . ($connectivity_test ? 'Success' : 'Failed'), 'error');
                
                return new \WP_Error(
                    'dvsa_dns_error',
                    'Cannot resolve DVSA MOT API host. Please check your network connectivity and DNS settings.',
                    ['original_error' => $error_message, 'connectivity_test' => $connectivity_test]
                );
            }
            
            // Retry on certain errors
            if ($retry_count < $max_retries && $this->is_retryable_error($response)) {
                $this->log("Retryable error, attempting retry...");
                sleep(1);
                return $this->make_api_request_enhanced($endpoint, $retry_count + 1);
            }
            
            return new \WP_Error(
                'dvsa_connection_error',
                "Cannot connect to DVSA MOT API: {$error_message}",
                ['error_code' => $error_code]
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $this->log("Response code: {$response_code}");
        
        // Log response body for debugging (truncated)
        if ($this->debug_mode) {
            $log_body = substr($response_body, 0, 500);
            if (strlen($response_body) > 500) {
                $log_body .= '... [truncated]';
            }
            $this->log('Response body: ' . $log_body);
        }
        
        // Handle different response codes
        switch ($response_code) {
            case 200:
                $data = json_decode($response_body, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = json_last_error_msg();
                    $this->log('JSON decode error: ' . $json_error, 'error');
                    
                    return new \WP_Error(
                        'dvsa_json_error',
                        "Error decoding DVSA API response: {$json_error}",
                        ['raw_response' => $response_body]
                    );
                }
                
                $this->log('Request successful');
                return $data;
                
            case 404:
                return new \WP_Error(
                    'mot_not_found',
                    'No MOT test results found for this vehicle',
                    ['status' => $response_code]
                );
                
            case 403:
                return new \WP_Error(
                    'dvsa_forbidden',
                    'DVSA API access denied - check API key and permissions',
                    ['status' => $response_code]
                );
                
            case 429:
                // Rate limit - retry if possible
                if ($retry_count < $max_retries) {
                    $this->log("Rate limit hit, retrying after delay...");
                    sleep(2);
                    return $this->make_api_request_enhanced($endpoint, $retry_count + 1);
                }
                return new \WP_Error(
                    'dvsa_rate_limit',
                    'DVSA API rate limit exceeded - please try again later',
                    ['status' => $response_code]
                );
                
            case 500:
            case 502:
            case 503:
                // Server errors - retry if possible
                if ($retry_count < $max_retries) {
                    $this->log("Server error {$response_code}, retrying...");
                    sleep(2);
                    return $this->make_api_request_enhanced($endpoint, $retry_count + 1);
                }
                return new \WP_Error(
                    'dvsa_server_error',
                    "DVSA API server error: HTTP {$response_code}",
                    ['status' => $response_code]
                );
                
            default:
                $error_data = json_decode($response_body, true);
                $error_message = $this->extract_error_message($error_data);
                
                $this->log('API error: ' . $error_message, 'error');
                
                return new \WP_Error(
                    'dvsa_error_' . $response_code,
                    "DVSA MOT API Error: {$error_message}",
                    ['status' => $response_code, 'response' => $error_data]
                );
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
     * Extract error message from API response
     * 
     * @param array|null $error_data
     * @return string
     */
    private function extract_error_message($error_data) {
        if (is_array($error_data)) {
            if (!empty($error_data['message'])) {
                return $error_data['message'];
            } elseif (!empty($error_data['error'])) {
                return is_array($error_data['error']) ? json_encode($error_data['error']) : $error_data['error'];
            } elseif (!empty($error_data['detail'])) {
                return $error_data['detail'];
            } elseif (!empty($error_data['errors']) && is_array($error_data['errors'])) {
                return json_encode($error_data['errors']);
            }
        }
        
        return 'Unknown error';
    }
    
    /**
     * Process MOT data from DVSA API with enhanced analysis
     * 
     * @param array $data Raw DVSA data
     * @param string $registration
     * @return array Enhanced processed MOT data
     */
    private function process_enhanced_mot_data($data, $registration) {
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
            'failure_reasons' => [],
            'passes_total' => 0,
            'fails_total' => 0,
            'last_service_indicator' => null,
            'maintenance_score' => null,
            'risk_assessment' => null,
            'data_source' => 'dvsa_api_enhanced',
            'retrieved_at' => current_time('mysql'),
            'using_mock_data' => false,
            'cache_expires_at' => date('Y-m-d H:i:s', time() + $this->cache_duration)
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
                
                // Process mileage information
                if (isset($latest_test['odometerValue']) && $latest_test['odometerResultType'] === 'READ') {
                    $processed['latest_mileage'] = [
                        'value' => $latest_test['odometerValue'],
                        'unit' => $latest_test['odometerUnit'] === 'MI' ? 'miles' : 'km',
                        'date' => $latest_test['completedDate']
                    ];
                }
                
                // Enhanced analysis of all tests
                $pass_count = 0;
                $fail_count = 0;
                $all_advisories = [];
                $all_defects = [];
                $all_failures = [];
                
                foreach ($data['motTests'] as $test) {
                    // Count passes and fails
                    if ($test['testResult'] === 'PASSED') {
                        $pass_count++;
                    } elseif ($test['testResult'] === 'FAILED') {
                        $fail_count++;
                    }
                    
                    // Build mileage history
                    if (isset($test['odometerValue']) && $test['odometerResultType'] === 'READ') {
                        $processed['mileage_history'][] = [
                            'value' => $test['odometerValue'],
                            'unit' => $test['odometerUnit'] === 'MI' ? 'miles' : 'km',
                            'date' => $test['completedDate'],
                            'test_result' => $test['testResult']
                        ];
                    }
                    
                    // Process defects and advisories
                    if (isset($test['defects']) && is_array($test['defects'])) {
                        foreach ($test['defects'] as $defect) {
                            switch ($defect['type']) {
                                case 'ADVISORY':
                                    $all_advisories[] = [
                                        'text' => $defect['text'],
                                        'location' => $defect['location'] ?? '',
                                        'date' => $test['completedDate'],
                                        'test_result' => $test['testResult']
                                    ];
                                    break;
                                    
                                case 'FAIL':
                                    $all_failures[] = [
                                        'text' => $defect['text'],
                                        'location' => $defect['location'] ?? '',
                                        'date' => $test['completedDate'],
                                        'dangerous' => ($defect['type'] === 'DANGEROUS') ? true : false
                                    ];
                                    break;
                                    
                                case 'DANGEROUS':
                                    $all_defects[] = [
                                        'text' => $defect['text'],
                                        'location' => $defect['location'] ?? '',
                                        'date' => $test['completedDate'],
                                        'dangerous' => true
                                    ];
                                    break;
                                    
                                default:
                                    $all_defects[] = [
                                        'text' => $defect['text'],
                                        'location' => $defect['location'] ?? '',
                                        'date' => $test['completedDate'],
                                        'type' => $defect['type']
                                    ];
                            }
                        }
                    }
                }
                
                // Set totals and recent items
                $processed['passes_total'] = $pass_count;
                $processed['fails_total'] = $fail_count;
                $processed['advisory_notices'] = array_slice($all_advisories, 0, 10); // Latest 10
                $processed['defects'] = array_slice($all_defects, 0, 10); // Latest 10
                $processed['failure_reasons'] = array_slice($all_failures, 0, 10); // Latest 10
                
                // Calculate maintenance score (0-100)
                $processed['maintenance_score'] = $this->calculate_maintenance_score($data['motTests'], $processed);
                
                // Assess risk level
                $processed['risk_assessment'] = $this->assess_vehicle_risk($processed);
                
                // Determine last service indicator
                $processed['last_service_indicator'] = $this->determine_service_indicator($processed);
            }
        }
        
        // Handle vehicles that may not need MOT yet
        if (empty($processed['mot_tests']) && isset($data['motTestDueDate'])) {
            $processed['current_mot_status'] = 'Not Required';
            $processed['mot_expiry_date'] = $data['motTestDueDate'];
            $processed['maintenance_score'] = 85; // New vehicles get good score
            $processed['risk_assessment'] = 'low';
        }
        
        return $processed;
    }
    
    /**
     * Calculate maintenance score based on MOT history
     * 
     * @param array $mot_tests
     * @param array $processed_data
     * @return int Score from 0-100
     */
    private function calculate_maintenance_score($mot_tests, $processed_data) {
        $score = 100; // Start with perfect score
        
        $total_tests = count($mot_tests);
        if ($total_tests === 0) return 85; // New vehicle
        
        $fail_rate = $total_tests > 0 ? ($processed_data['fails_total'] / $total_tests) : 0;
        $advisory_count = count($processed_data['advisory_notices']);
        $defect_count = count($processed_data['defects']);
        
        // Reduce score based on failures
        $score -= ($fail_rate * 30);
        
        // Reduce score based on advisories
        $score -= min(20, $advisory_count * 2);
        
        // Reduce score based on defects
        $score -= min(25, $defect_count * 5);
        
        // Bonus for consistent passes
        if ($processed_data['fails_total'] === 0 && $total_tests >= 3) {
            $score += 10;
        }
        
        return max(0, min(100, intval($score)));
    }
    
    /**
     * Assess vehicle risk level
     * 
     * @param array $processed_data
     * @return string Risk level: low, medium, high
     */
    private function assess_vehicle_risk($processed_data) {
        $risk_factors = 0;
        
        // Recent failures
        if ($processed_data['current_mot_status'] === 'FAILED') {
            $risk_factors += 3;
        }
        
        // High fail rate
        $total_tests = $processed_data['passes_total'] + $processed_data['fails_total'];
        if ($total_tests > 0) {
            $fail_rate = $processed_data['fails_total'] / $total_tests;
            if ($fail_rate > 0.5) $risk_factors += 2;
            elseif ($fail_rate > 0.3) $risk_factors += 1;
        }
        
        // Dangerous defects
        foreach ($processed_data['defects'] as $defect) {
            if (isset($defect['dangerous']) && $defect['dangerous']) {
                $risk_factors += 2;
            }
        }
        
        // Recent advisories
        if (count($processed_data['advisory_notices']) > 5) {
            $risk_factors += 1;
        }
        
        // Low maintenance score
        if ($processed_data['maintenance_score'] < 50) {
            $risk_factors += 2;
        } elseif ($processed_data['maintenance_score'] < 70) {
            $risk_factors += 1;
        }
        
        // Determine risk level
        if ($risk_factors >= 5) return 'high';
        if ($risk_factors >= 3) return 'medium';
        return 'low';
    }
    
    /**
     * Determine service indicator
     * 
     * @param array $processed_data
     * @return string|null
     */
    private function determine_service_indicator($processed_data) {
        if ($processed_data['current_mot_status'] === 'FAILED') {
            return 'immediate_attention_required';
        }
        
        if ($processed_data['risk_assessment'] === 'high') {
            return 'comprehensive_service_recommended';
        }
        
        if (count($processed_data['advisory_notices']) > 3) {
            return 'preventive_maintenance_due';
        }
        
        if ($processed_data['maintenance_score'] < 70) {
            return 'service_recommended';
        }
        
        return null;
    }
    
    /**
     * Generate enhanced mock MOT data for testing
     * 
     * @param string $registration
     * @return array
     */
    private function get_enhanced_mock_mot_data($registration) {
        $hash = md5($registration);
        
        // Generate more realistic test patterns
        $test_count = 2 + (hexdec(substr($hash, 0, 1)) % 4); // 2-5 tests
        $current_year = date('Y');
        $tests = [];
        
        for ($i = 0; $i < $test_count; $i++) {
            $test_date = date('Y-m-d', strtotime("-{$i} year"));
            $mileage = 60000 - ($i * 12000) + (hexdec(substr($hash, $i, 2)) % 5000);
            
            // Determine test result (weight towards passes)
            $result_chance = hexdec(substr($hash, $i + 2, 1)) % 10;
            $test_result = ($result_chance < 7) ? 'PASSED' : 'FAILED';
            
            $tests[] = [
                'completedDate' => $test_date,
                'testResult' => $test_result,
                'expiryDate' => ($test_result === 'PASSED') ? date('Y-m-d', strtotime("+1 year", strtotime($test_date))) : null,
                'odometerValue' => $mileage,
                'odometerUnit' => 'MI',
                'odometerResultType' => 'READ',
                'defects' => $this->generate_mock_defects($hash, $i, $test_result)
            ];
        }
        
        // Calculate enhanced statistics
        $passes = array_filter($tests, function($test) { return $test['testResult'] === 'PASSED'; });
        $fails = array_filter($tests, function($test) { return $test['testResult'] === 'FAILED'; });
        
        $processed = [
            'registration' => $registration,
            'make' => 'FORD',
            'model' => 'FOCUS',
            'manufacture_year' => 2018,
            'fuel_type' => 'Petrol',
            'engine_size' => 1600,
            'primary_colour' => 'Blue',
            'current_mot_status' => $tests[0]['testResult'],
            'mot_expiry_date' => $tests[0]['expiryDate'],
            'latest_mileage' => [
                'value' => $tests[0]['odometerValue'],
                'unit' => 'miles',
                'date' => $tests[0]['completedDate']
            ],
            'mileage_history' => array_map(function($test) {
                return [
                    'value' => $test['odometerValue'],
                    'unit' => 'miles',
                    'date' => $test['completedDate'],
                    'test_result' => $test['testResult']
                ];
            }, $tests),
            'advisory_notices' => [],
            'defects' => [],
            'failure_reasons' => [],
            'passes_total' => count($passes),
            'fails_total' => count($fails),
            'mot_tests' => $tests,
            'data_source' => 'mock_data_enhanced',
            'retrieved_at' => current_time('mysql'),
            'using_mock_data' => true,
            'cache_expires_at' => null
        ];
        
        // Extract defects and advisories
        foreach ($tests as $test) {
            foreach ($test['defects'] as $defect) {
                if ($defect['type'] === 'ADVISORY') {
                    $processed['advisory_notices'][] = $defect;
                } else {
                    if ($test['testResult'] === 'FAILED') {
                        $processed['failure_reasons'][] = $defect;
                    } else {
                        $processed['defects'][] = $defect;
                    }
                }
            }
        }
        
        // Calculate enhanced metrics
        $processed['maintenance_score'] = $this->calculate_maintenance_score($tests, $processed);
        $processed['risk_assessment'] = $this->assess_vehicle_risk($processed);
        $processed['last_service_indicator'] = $this->determine_service_indicator($processed);
        
        return $processed;
    }
    
    /**
     * Generate mock defects for test data
     * 
     * @param string $hash
     * @param int $test_index
     * @param string $test_result
     * @return array
     */
    private function generate_mock_defects($hash, $test_index, $test_result) {
        $defects = [];
        
        $common_advisories = [
            "Nearside front tyre worn close to legal limit",
            "Offside rear tyre worn close to legal limit", 
            "Brake disc worn, pitted or scored, but not seriously weakened",
            "Engine oil leak, but not excessive",
            "Windscreen washer reservoir has a leak",
            "Exhaust system corroded but not leaking"
        ];
        
        $common_failures = [
            "Headlamp has defective beam pattern",
            "Brake pad(s) less than 1.5 mm thick",
            "Tyre has a cut in excess of the requirements",
            "Windscreen wiper does not clear the windscreen effectively",
            "Horn not working",
            "Exhaust system leaking"
        ];
        
        // Add some advisories for older tests or passes
        if ($test_result === 'PASSED' && hexdec(substr($hash, $test_index + 10, 1)) % 3 === 0) {
            $advisory_count = 1 + (hexdec(substr($hash, $test_index + 11, 1)) % 3);
            for ($i = 0; $i < $advisory_count; $i++) {
                $advisory_index = (hexdec(substr($hash, $test_index + 12 + $i, 1)) % count($common_advisories));
                $defects[] = [
                    'type' => 'ADVISORY',
                    'text' => $common_advisories[$advisory_index],
                    'location' => $this->get_mock_location($hash, $i)
                ];
            }
        }
        
        // Add failures for failed tests
        if ($test_result === 'FAILED') {
            $failure_count = 1 + (hexdec(substr($hash, $test_index + 15, 1)) % 2);
            for ($i = 0; $i < $failure_count; $i++) {
                $failure_index = (hexdec(substr($hash, $test_index + 16 + $i, 1)) % count($common_failures));
                $defects[] = [
                    'type' => 'FAIL',
                    'text' => $common_failures[$failure_index],
                    'location' => $this->get_mock_location($hash, $i)
                ];
            }
        }
        
        return $defects;
    }
    
    /**
     * Get mock location for defects
     * 
     * @param string $hash
     * @param int $index
     * @return string
     */
    private function get_mock_location($hash, $index) {
        $locations = ['Front', 'Rear', 'Nearside', 'Offside', 'Central'];
        $location_index = (hexdec(substr($hash, 20 + $index, 1)) % count($locations));
        return $locations[$location_index];
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
        
        $token = $this->get_access_token_enhanced();
        if (is_wp_error($token)) {
            return $token;
        }
        
        // Test with a registration that might not exist - we just want to verify API access
        $test_result = $this->make_api_request_enhanced('registration/AB12CDE');
        
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
        $cache_key = 'bm_dvsa_mot_enhanced_' . md5(strtoupper(preg_replace('/[^A-Z0-9]/', '', $registration)));
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
            error_log("[Blue Motors DVSA Enhanced] [{$level}] {$message}");
        }
        
        // Allow for custom logging actions
        do_action('blue_motors_dvsa_api_log', $message, $level);
    }
}
