<?php
/**
 * AJAX Handlers for Vehicle Lookup
 * Enhanced DVLA and DVSA API integration for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Vehicle Lookup AJAX Handler Class
 */
class BMS_Vehicle_Lookup_Ajax {
    
    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        // Vehicle lookup actions (using consistent naming with main class)
        add_action('wp_ajax_bms_enhanced_vehicle_lookup', [__CLASS__, 'lookup_vehicle']);
        add_action('wp_ajax_nopriv_bms_enhanced_vehicle_lookup', [__CLASS__, 'lookup_vehicle']);
        
        // MOT history lookup
        add_action('wp_ajax_bms_get_mot_history', [__CLASS__, 'get_mot_history']);
        add_action('wp_ajax_nopriv_bms_get_mot_history', [__CLASS__, 'get_mot_history']);
        
        // Vehicle validation
        add_action('wp_ajax_bms_validate_registration', [__CLASS__, 'validate_registration']);
        add_action('wp_ajax_nopriv_bms_validate_registration', [__CLASS__, 'validate_registration']);
        
        // Clear cache
        add_action('wp_ajax_bms_clear_vehicle_cache', [__CLASS__, 'clear_vehicle_cache']);
        
        // Test API connections
        add_action('wp_ajax_bms_test_api_connections', [__CLASS__, 'test_api_connections']);
    }
    
    /**
     * Main vehicle lookup handler using combined APIs
     */
    public static function lookup_vehicle() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bms_vehicle_lookup')) {
            wp_send_json_error([
                'message' => 'Security check failed',
                'code' => 'invalid_nonce'
            ]);
        }
        
        $registration = sanitize_text_field($_POST['registration'] ?? '');
        
        if (empty($registration)) {
            wp_send_json_error([
                'message' => 'Vehicle registration is required',
                'code' => 'missing_registration'
            ]);
        }
        
        try {
            // Use the combined lookup service
            $lookup_service = new \BlueMotosSouthampton\Services\VehicleLookupCombined();
            $vehicle_data = $lookup_service->lookup_vehicle_comprehensive($registration);
            
            if (is_wp_error($vehicle_data)) {
                wp_send_json_error([
                    'message' => $vehicle_data->get_error_message(),
                    'code' => $vehicle_data->get_error_code(),
                    'registration' => $registration
                ]);
            }
            
            // Log successful lookup
            self::log_lookup_success($registration, $vehicle_data);
            
            // Format response for frontend
            $response = self::format_vehicle_response($vehicle_data);
            
            wp_send_json_success($response);
            
        } catch (Exception $e) {
            error_log('[BMS Vehicle Lookup] Exception: ' . $e->getMessage());
            
            wp_send_json_error([
                'message' => 'An error occurred during vehicle lookup',
                'code' => 'lookup_exception',
                'registration' => $registration
            ]);
        }
    }
    
    /**
     * Get detailed MOT history
     */
    public static function get_mot_history() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bms_vehicle_lookup')) {
            wp_send_json_error([
                'message' => 'Security check failed',
                'code' => 'invalid_nonce'
            ]);
        }
        
        $registration = sanitize_text_field($_POST['registration'] ?? '');
        
        if (empty($registration)) {
            wp_send_json_error([
                'message' => 'Vehicle registration is required',
                'code' => 'missing_registration'
            ]);
        }
        
        try {
            $dvsa_api = new \BlueMotosSouthampton\Services\DVSAMotApiEnhanced();
            $mot_data = $dvsa_api->get_mot_history($registration);
            
            if (is_wp_error($mot_data)) {
                wp_send_json_error([
                    'message' => $mot_data->get_error_message(),
                    'code' => $mot_data->get_error_code(),
                    'registration' => $registration
                ]);
            }
            
            // Format MOT history for display
            $formatted_history = self::format_mot_history($mot_data);
            
            wp_send_json_success([
                'registration' => $registration,
                'mot_history' => $formatted_history,
                'summary' => [
                    'total_tests' => count($mot_data['mot_tests'] ?? []),
                    'passes' => $mot_data['passes_total'] ?? 0,
                    'fails' => $mot_data['fails_total'] ?? 0,
                    'current_status' => $mot_data['current_mot_status'] ?? 'Unknown',
                    'expiry_date' => $mot_data['mot_expiry_date'] ?? null,
                    'maintenance_score' => $mot_data['maintenance_score'] ?? null
                ]
            ]);
            
        } catch (Exception $e) {
            error_log('[BMS MOT History] Exception: ' . $e->getMessage());
            
            wp_send_json_error([
                'message' => 'An error occurred while retrieving MOT history',
                'code' => 'mot_exception',
                'registration' => $registration
            ]);
        }
    }
    
    /**
     * Validate vehicle registration format
     */
    public static function validate_registration() {
        $registration = sanitize_text_field($_POST['registration'] ?? '');
        
        if (empty($registration)) {
            wp_send_json_error([
                'message' => 'Registration is required',
                'code' => 'missing_registration'
            ]);
        }
        
        $is_valid = self::is_valid_uk_registration($registration);
        $formatted = self::format_registration_display($registration);
        
        wp_send_json_success([
            'valid' => $is_valid,
            'formatted' => $formatted,
            'registration' => strtoupper(preg_replace('/[^A-Z0-9]/', '', $registration))
        ]);
    }
    
    /**
     * Clear cached vehicle data (admin only)
     */
    public static function clear_vehicle_cache() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Insufficient permissions',
                'code' => 'no_permission'
            ]);
        }
        
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bms_admin_actions')) {
            wp_send_json_error([
                'message' => 'Security check failed',
                'code' => 'invalid_nonce'
            ]);
        }
        
        $registration = sanitize_text_field($_POST['registration'] ?? '');
        
        if (empty($registration)) {
            wp_send_json_error([
                'message' => 'Vehicle registration is required',
                'code' => 'missing_registration'
            ]);
        }
        
        try {
            $lookup_service = new \BlueMotosSouthampton\Services\VehicleLookupCombined();
            $lookup_service->clear_vehicle_cache($registration);
            
            wp_send_json_success([
                'message' => "Cache cleared for registration {$registration}",
                'registration' => $registration
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Failed to clear cache',
                'code' => 'cache_clear_failed'
            ]);
        }
    }
    
    /**
     * Test API connections (admin only)
     */
    public static function test_api_connections() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Insufficient permissions',
                'code' => 'no_permission'
            ]);
        }
        
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bms_admin_actions')) {
            wp_send_json_error([
                'message' => 'Security check failed',
                'code' => 'invalid_nonce'
            ]);
        }
        
        try {
            $lookup_service = new \BlueMotosSouthampton\Services\VehicleLookupCombined();
            $test_results = $lookup_service->test_all_connections();
            
            wp_send_json_success([
                'test_results' => $test_results,
                'timestamp' => current_time('mysql')
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Failed to test API connections',
                'code' => 'api_test_failed',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Format vehicle data for frontend response
     * 
     * @param array $vehicle_data
     * @return array
     */
    private static function format_vehicle_response($vehicle_data) {
        return [
            // Basic vehicle information
            'registration' => $vehicle_data['registration'] ?? '',
            'registration_formatted' => $vehicle_data['registration_formatted'] ?? '',
            'make' => $vehicle_data['make'] ?? 'Unknown',
            'model' => $vehicle_data['model'] ?? 'Unknown',
            'colour' => $vehicle_data['colour'] ?? 'Unknown',
            'year' => $vehicle_data['year_of_manufacture'] ?? null,
            'fuel_type' => $vehicle_data['fuel_type'] ?? 'Unknown',
            'fuel_type_normalized' => $vehicle_data['fuel_type_normalized'] ?? 'petrol',
            'engine_capacity' => $vehicle_data['engine_capacity'] ?? null,
            
            // Enhanced information
            'estimated_tyre_size' => $vehicle_data['estimated_tyre_size'] ?? null,
            'vehicle_category' => $vehicle_data['vehicle_category'] ?? 'standard',
            'pricing_category' => $vehicle_data['pricing_category'] ?? 'standard',
            'age_category' => $vehicle_data['age_category'] ?? 'mature',
            
            // MOT information
            'mot_status' => $vehicle_data['current_mot_status'] ?? 'Unknown',
            'mot_expiry' => $vehicle_data['mot_expiry_date'] ?? null,
            'mot_pass_rate' => $vehicle_data['mot_pass_rate'] ?? null,
            'maintenance_score' => $vehicle_data['maintenance_score'] ?? null,
            'risk_assessment' => $vehicle_data['risk_assessment'] ?? 'low',
            
            // Service recommendations
            'service_recommendations' => $vehicle_data['service_recommendations'] ?? [],
            'predicted_service_needs' => $vehicle_data['predicted_service_needs'] ?? [],
            
            // Advisory and defects summary
            'advisory_count' => count($vehicle_data['advisory_notices'] ?? []),
            'defect_count' => count($vehicle_data['defects'] ?? []),
            'recent_advisories' => array_slice($vehicle_data['advisory_notices'] ?? [], 0, 3),
            
            // Mileage information
            'latest_mileage' => $vehicle_data['latest_mileage'] ?? null,
            'estimated_annual_mileage' => $vehicle_data['estimated_annual_mileage'] ?? null,
            'mileage_category' => $vehicle_data['mileage_category'] ?? 'average',
            
            // Meta information
            'data_sources' => $vehicle_data['data_sources'] ?? [],
            'using_mock_data' => $vehicle_data['using_mock_data'] ?? false,
            'overall_condition_score' => $vehicle_data['overall_condition_score'] ?? null,
            'lookup_timestamp' => $vehicle_data['lookup_timestamp'] ?? null
        ];
    }
    
    /**
     * Format MOT history for display
     * 
     * @param array $mot_data
     * @return array
     */
    private static function format_mot_history($mot_data) {
        $formatted_tests = [];
        
        foreach ($mot_data['mot_tests'] ?? [] as $test) {
            $formatted_tests[] = [
                'date' => $test['completedDate'] ?? null,
                'result' => $test['testResult'] ?? 'Unknown',
                'expiry_date' => $test['expiryDate'] ?? null,
                'mileage' => [
                    'value' => $test['odometerValue'] ?? null,
                    'unit' => ($test['odometerUnit'] ?? 'MI') === 'MI' ? 'miles' : 'km',
                    'type' => $test['odometerResultType'] ?? 'READ'
                ],
                'defects' => array_map(function($defect) {
                    return [
                        'type' => $defect['type'] ?? 'UNKNOWN',
                        'text' => $defect['text'] ?? '',
                        'location' => $defect['location'] ?? '',
                        'dangerous' => ($defect['type'] ?? '') === 'DANGEROUS'
                    ];
                }, $test['defects'] ?? [])
            ];
        }
        
        return $formatted_tests;
    }
    
    /**
     * Validate UK registration format
     * 
     * @param string $registration
     * @return bool
     */
    private static function is_valid_uk_registration($registration) {
        $clean_reg = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($registration)));
        
        if (empty($clean_reg) || strlen($clean_reg) < 2 || strlen($clean_reg) > 8) {
            return false;
        }
        
        // UK registration patterns
        $patterns = [
            '/^[A-Z]{2}[0-9]{2}[A-Z]{3}$/', // Current format: AB12CDE
            '/^[A-Z][0-9]{1,3}[A-Z]{3}$/',  // Prefix format: A123BCD
            '/^[A-Z]{3}[0-9]{1,3}[A-Z]$/',  // Suffix format: ABC123D
            '/^[0-9]{1,4}[A-Z]{1,3}$/',     // Dateless format: 1234AB
            '/^[A-Z]{1,3}[0-9]{1,4}$/'      // Early format: AB1234]
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $clean_reg)) {
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
    private static function format_registration_display($registration) {
        $clean_reg = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($registration)));
        
        // Current format: AB12CDE -> AB12 CDE
        if (preg_match('/^([A-Z]{2})([0-9]{2})([A-Z]{3})$/', $clean_reg, $matches)) {
            return $matches[1] . $matches[2] . ' ' . $matches[3];
        }
        
        // Prefix format: A123BCD -> A123 BCD
        if (preg_match('/^([A-Z])([0-9]{1,3})([A-Z]{3})$/', $clean_reg, $matches)) {
            return $matches[1] . $matches[2] . ' ' . $matches[3];
        }
        
        // Suffix format: ABC123D -> ABC 123D
        if (preg_match('/^([A-Z]{3})([0-9]{1,3})([A-Z])$/', $clean_reg, $matches)) {
            return $matches[1] . ' ' . $matches[2] . $matches[3];
        }
        
        return $clean_reg;
    }
    
    /**
     * Log successful vehicle lookup
     * 
     * @param string $registration
     * @param array $vehicle_data
     */
    private static function log_lookup_success($registration, $vehicle_data) {
        // Update usage statistics
        $today_count = get_option('bms_api_calls_today', 0);
        update_option('bms_api_calls_today', $today_count + 1);
        
        $week_count = get_option('bms_api_calls_week', 0);
        update_option('bms_api_calls_week', $week_count + 1);
        
        // Log for debugging if enabled
        if (get_option('bm_debug_mode', false)) {
            $data_sources = $vehicle_data['data_sources'] ?? [];
            $using_mock = $vehicle_data['using_mock_data'] ?? false;
            
            error_log("[BMS Vehicle Lookup] Success: {$registration}, Sources: " . 
                     json_encode($data_sources) . ", Mock: " . ($using_mock ? 'Yes' : 'No'));
        }
    }
}

// Initialize AJAX handlers
BMS_Vehicle_Lookup_Ajax::init();
