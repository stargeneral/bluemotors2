<?php
/**
 * Combined Vehicle Lookup Service for Blue Motors Southampton
 * Integrates DVLA and DVSA APIs for comprehensive vehicle information
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

namespace BlueMotosSouthampton\Services;

class VehicleLookupCombined {
    
    /**
     * DVLA API instance
     * @var DVLAApiEnhanced
     */
    private $dvla_api;
    
    /**
     * DVSA MOT API instance
     * @var DVSAMotApiEnhanced
     */
    private $dvsa_api;
    
    /**
     * Debug mode
     * @var bool
     */
    private $debug_mode;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->dvla_api = new DVLAApiEnhanced();
        $this->dvsa_api = new DVSAMotApiEnhanced();
        $this->debug_mode = get_option('bm_debug_mode', BM_DEBUG_MODE);
        
        $this->log('Combined Vehicle Lookup initialized');
    }
    
    /**
     * Simple lookup method (DVSA first, then DVLA fallback)
     * This method prioritizes DVSA API since it's working reliably
     * 
     * @param string $registration Vehicle registration
     * @return array|WP_Error Vehicle data or error
     */
    public function lookup($registration) {
        $this->log("Starting DVSA-first lookup for registration: {$registration}");
        
        // Clean registration
        $registration = strtoupper(preg_replace('/[^A-Z0-9]/', '', $registration));
        
        // Try DVSA first (since it's working on your domain)
        $dvsa_data = $this->dvsa_api->get_mot_history($registration);
        
        if (!is_wp_error($dvsa_data) && !($dvsa_data['using_mock_data'] ?? false)) {
            $this->log("DVSA lookup successful for {$registration}, extracting vehicle data");
            
            // Extract vehicle data from DVSA MOT history using existing method
            $vehicle_data = $this->extract_vehicle_data_from_mot($dvsa_data, $registration);
            
            if (!empty($vehicle_data)) {
                $this->log("Successfully extracted vehicle data from DVSA for {$registration}");
                return $vehicle_data;
            }
        }
        
        $this->log("DVSA lookup failed or returned mock data, trying DVLA for {$registration}");
        
        // Fallback to DVLA if DVSA fails or returns mock data
        $dvla_data = $this->dvla_api->lookup_vehicle($registration);
        
        if (is_wp_error($dvla_data)) {
            $this->log("Both DVSA and DVLA lookups failed for {$registration}", 'error');
            return $dvla_data;
        }
        
        $this->log("DVLA fallback successful for {$registration}");
        return $dvla_data;
    }
    
    /**
     * Extract vehicle data from DVSA MOT history
     * 
     * @param array $mot_data
     * @param string $registration
     * @return array|null
     */
    private function extract_vehicle_data_from_mot($mot_data, $registration) {
        // DVSA MOT API provides vehicle data in the response
        if (empty($mot_data) || !is_array($mot_data)) {
            return null;
        }
        
        // Look for vehicle data in MOT history
        $vehicle_info = $mot_data[0] ?? $mot_data;
        
        if (empty($vehicle_info['make'])) {
            return null;
        }
        
        // Format the data to match DVLA structure for compatibility
        return [
            'registration' => $registration,
            'registrationNumber' => $registration,
            'make' => $vehicle_info['make'] ?? 'Unknown',
            'model' => $vehicle_info['model'] ?? 'Unknown',
            'colour' => $vehicle_info['primaryColour'] ?? 'Unknown',
            'fuelType' => $vehicle_info['fuelType'] ?? 'PETROL',
            'engineCapacity' => intval($vehicle_info['engineSize'] ?? 1600),
            'yearOfManufacture' => intval($vehicle_info['manufactureYear'] ?? date('Y')),
            'vehicleClass' => $vehicle_info['vehicleType'] ?? 'M1',
            'motStatus' => ($vehicle_info['testResult'] ?? '') === 'PASSED' ? 'Valid' : 'Not valid',
            'motExpiryDate' => $vehicle_info['expiryDate'] ?? null,
            
            // Enhanced data to indicate source
            'data_source' => 'dvsa_api_primary',
            'retrieved_at' => current_time('mysql'),
            'using_mock_data' => false,
            'mot_history_available' => true
        ];
    }
    
    /**
     * Perform comprehensive vehicle lookup combining DVLA and DVSA data
     * 
     * @param string $registration Vehicle registration
     * @return array|WP_Error Combined vehicle data or error
     */
    public function lookup_vehicle_comprehensive($registration) {
        $this->log("Starting comprehensive lookup for registration: {$registration}");
        
        // Start with DVLA data (basic vehicle information)
        $dvla_data = $this->dvla_api->lookup_vehicle($registration);
        
        if (is_wp_error($dvla_data)) {
            $this->log("DVLA lookup failed: " . $dvla_data->get_error_message(), 'error');
            return $dvla_data;
        }
        
        $this->log("DVLA lookup successful for {$registration}");
        
        // Get MOT history from DVSA
        $mot_data = $this->dvsa_api->get_mot_history($registration);
        
        if (is_wp_error($mot_data)) {
            $this->log("DVSA lookup failed: " . $mot_data->get_error_message(), 'warning');
            // Don't fail the entire lookup if MOT data is unavailable
            $mot_data = $this->generate_fallback_mot_data($registration);
        } else {
            $this->log("DVSA lookup successful for {$registration}");
        }
        
        // Combine the data
        $combined_data = $this->merge_vehicle_data($dvla_data, $mot_data, $registration);
        
        $this->log("Combined lookup completed for {$registration}");
        
        return $combined_data;
    }
    
    /**
     * Merge DVLA and DVSA data into comprehensive vehicle profile
     * 
     * @param array $dvla_data DVLA vehicle data
     * @param array $mot_data DVSA MOT data
     * @param string $registration
     * @return array Combined vehicle data
     */
    private function merge_vehicle_data($dvla_data, $mot_data, $registration) {
        // Check data source reliability
        $dvla_is_mock = $dvla_data['using_mock_data'] ?? false;
        $dvsa_is_mock = $mot_data['using_mock_data'] ?? false;
        
        // If DVLA is mock but DVSA is real, prioritize DVSA data for basic vehicle info
        if ($dvla_is_mock && !$dvsa_is_mock && !empty($mot_data['make'])) {
            $this->log("DVLA using mock data, prioritizing real DVSA data for {$registration}");
            
            // Start with DVSA data as base when it's real and DVLA is mock
            $combined = [
                'registration' => $registration,
                'registration_formatted' => $this->format_registration($registration),
                'make' => $mot_data['make'] ?? ($dvla_data['make'] ?? 'Unknown'),
                'model' => $mot_data['model'] ?? ($dvla_data['model'] ?? 'Unknown'),
                'colour' => $mot_data['primaryColour'] ?? ($dvla_data['colour'] ?? 'Unknown'),
                'fuel_type' => $mot_data['fuelType'] ?? ($dvla_data['fuel_type'] ?? 'Unknown'),
                'fuel_type_normalized' => $this->normalize_fuel_type($mot_data['fuelType'] ?? ($dvla_data['fuel_type'] ?? 'petrol')),
                'engine_capacity' => intval($mot_data['engineSize'] ?? ($dvla_data['engine_capacity'] ?? 1600)),
                'year_of_manufacture' => $this->extract_year_from_date($mot_data['firstUsedDate'] ?? null) ?? ($dvla_data['year_of_manufacture'] ?? date('Y')),
                'first_used_date' => $mot_data['firstUsedDate'] ?? null,
                'registration_date' => $mot_data['registrationDate'] ?? null,
                'manufacture_date' => $mot_data['manufactureDate'] ?? null,
            ];
            
            // Add DVLA-specific fields that aren't in DVSA data
            $dvla_only_fields = ['vehicle_class', 'tax_status', 'tax_due_date', 'co2_emissions', 'marked_for_export', 'euro_status'];
            foreach ($dvla_only_fields as $field) {
                if (isset($dvla_data[$field])) {
                    $combined[$field] = $dvla_data[$field];
                }
            }
        } else {
            // Start with DVLA data as the base (normal case)
            $combined = $dvla_data;
            
            // Override with DVSA data if it's more reliable
            if (!$dvsa_is_mock && !empty($mot_data['make'])) {
                $combined['make'] = $mot_data['make'];
                $combined['model'] = $mot_data['model'];
                if (!empty($mot_data['primaryColour'])) {
                    $combined['colour'] = $mot_data['primaryColour'];
                }
                if (!empty($mot_data['fuelType'])) {
                    $combined['fuel_type'] = $mot_data['fuelType'];
                    $combined['fuel_type_normalized'] = $this->normalize_fuel_type($mot_data['fuelType']);
                }
                if (!empty($mot_data['engineSize'])) {
                    $combined['engine_capacity'] = intval($mot_data['engineSize']);
                }
            }
        }
        
        // Merge MOT specific data
        $combined['mot_history'] = $mot_data['mot_tests'] ?? [];
        $combined['current_mot_status'] = $mot_data['current_mot_status'] ?? 'Unknown';
        $combined['mot_expiry_date_dvsa'] = $mot_data['mot_expiry_date'] ?? null;
        $combined['latest_mileage'] = $mot_data['latest_mileage'] ?? null;
        $combined['mileage_history'] = $mot_data['mileage_history'] ?? [];
        $combined['advisory_notices'] = $mot_data['advisory_notices'] ?? [];
        $combined['defects'] = $mot_data['defects'] ?? [];
        $combined['failure_reasons'] = $mot_data['failure_reasons'] ?? [];
        $combined['maintenance_score'] = $mot_data['maintenance_score'] ?? null;
        $combined['risk_assessment'] = $mot_data['risk_assessment'] ?? 'low';
        $combined['last_service_indicator'] = $mot_data['last_service_indicator'] ?? null;
        
        // MOT statistics
        $combined['mot_passes_total'] = $mot_data['passes_total'] ?? 0;
        $combined['mot_fails_total'] = $mot_data['fails_total'] ?? 0;
        
        // Resolve conflicts between DVLA and DVSA data
        $combined = $this->resolve_data_conflicts($combined, $dvla_data, $mot_data);
        
        // Add comprehensive analysis
        $combined = $this->add_comprehensive_analysis($combined);
        
        // Add service recommendations
        $combined['service_recommendations'] = $this->generate_service_recommendations($combined);
        
        // Add pricing category
        $combined['pricing_category'] = $this->determine_pricing_category($combined);
        
        // Meta information
        $combined['lookup_timestamp'] = current_time('mysql');
        $combined['data_sources'] = [
            'dvla' => $dvla_data['data_source'] ?? 'unknown',
            'dvsa' => $mot_data['data_source'] ?? 'unknown'
        ];
        $combined['using_mock_data'] = $dvla_is_mock && $dvsa_is_mock; // Only true if both are mock
        $combined['primary_data_source'] = $dvla_is_mock && !$dvsa_is_mock ? 'dvsa' : 'dvla';
        
        return $combined;
    }
    
    /**
     * Resolve conflicts between DVLA and DVSA data sources
     * 
     * @param array $combined
     * @param array $dvla_data
     * @param array $mot_data
     * @return array
     */
    private function resolve_data_conflicts($combined, $dvla_data, $mot_data) {
        // MOT expiry date - prefer DVSA data if available and recent
        if (!empty($mot_data['mot_expiry_date']) && !empty($dvla_data['mot_expiry_date'])) {
            $dvsa_date = strtotime($mot_data['mot_expiry_date']);
            $dvla_date = strtotime($dvla_data['mot_expiry_date']);
            
            // Use DVSA date if it's more recent or if DVSA data is from API
            if ($dvsa_date > $dvla_date || ($mot_data['data_source'] ?? '') === 'dvsa_api_enhanced') {
                $combined['mot_expiry_date'] = $mot_data['mot_expiry_date'];
                $combined['mot_expiry_date_source'] = 'dvsa';
            } else {
                $combined['mot_expiry_date_source'] = 'dvla';
            }
        } elseif (!empty($mot_data['mot_expiry_date'])) {
            $combined['mot_expiry_date'] = $mot_data['mot_expiry_date'];
            $combined['mot_expiry_date_source'] = 'dvsa';
        } elseif (!empty($dvla_data['mot_expiry_date'])) {
            $combined['mot_expiry_date_source'] = 'dvla';
        }
        
        // Vehicle details - prefer DVLA data for basic info
        $dvla_fields = ['make', 'model', 'colour', 'fuel_type', 'engine_capacity', 'year_of_manufacture'];
        foreach ($dvla_fields as $field) {
            if (!empty($dvla_data[$field]) && $dvla_data[$field] !== 'Unknown') {
                $combined[$field . '_source'] = 'dvla';
            }
        }
        
        // Cross-validate make and model
        if (!empty($mot_data['make']) && !empty($dvla_data['make'])) {
            if (strtoupper($mot_data['make']) !== strtoupper($dvla_data['make'])) {
                $combined['data_validation_warnings'][] = 'Make differs between DVLA (' . $dvla_data['make'] . ') and DVSA (' . $mot_data['make'] . ') records';
            }
        }
        
        return $combined;
    }
    
    /**
     * Add comprehensive vehicle analysis
     * 
     * @param array $combined
     * @return array
     */
    private function add_comprehensive_analysis($combined) {
        // Age analysis
        $vehicle_age = date('Y') - ($combined['year_of_manufacture'] ?? date('Y'));
        $combined['vehicle_age_years'] = $vehicle_age;
        $combined['vehicle_age_category'] = $this->get_age_category($vehicle_age);
        
        // Mileage analysis
        if (!empty($combined['latest_mileage'])) {
            $annual_mileage = $this->calculate_annual_mileage($combined);
            $combined['estimated_annual_mileage'] = $annual_mileage;
            $combined['mileage_category'] = $this->categorize_mileage($annual_mileage);
        }
        
        // MOT performance analysis
        if ($combined['mot_passes_total'] > 0 || $combined['mot_fails_total'] > 0) {
            $total_tests = $combined['mot_passes_total'] + $combined['mot_fails_total'];
            $combined['mot_pass_rate'] = $total_tests > 0 ? round(($combined['mot_passes_total'] / $total_tests) * 100, 1) : 0;
            $combined['mot_reliability_score'] = $this->calculate_mot_reliability($combined);
        }
        
        // Overall vehicle condition score
        $combined['overall_condition_score'] = $this->calculate_overall_condition_score($combined);
        
        // Predicted service needs
        $combined['predicted_service_needs'] = $this->predict_service_needs($combined);
        
        return $combined;
    }
    
    /**
     * Generate service recommendations based on comprehensive data
     * 
     * @param array $combined
     * @return array
     */
    private function generate_service_recommendations($combined) {
        $recommendations = [];
        
        // MOT-based recommendations
        if ($combined['current_mot_status'] === 'FAILED') {
            $recommendations[] = [
                'type' => 'urgent',
                'service' => 'mot_retest_preparation',
                'description' => 'MOT failure repairs required',
                'priority' => 1
            ];
        }
        
        if (!empty($combined['advisory_notices']) && count($combined['advisory_notices']) > 0) {
            $recommendations[] = [
                'type' => 'preventive',
                'service' => 'advisory_items_check',
                'description' => 'Address ' . count($combined['advisory_notices']) . ' MOT advisory item(s)',
                'priority' => 2
            ];
        }
        
        // Age-based recommendations
        $vehicle_age = $combined['vehicle_age_years'] ?? 0;
        if ($vehicle_age > 7) {
            $recommendations[] = [
                'type' => 'maintenance',
                'service' => 'comprehensive_inspection',
                'description' => 'Comprehensive service recommended for vehicles over 7 years old',
                'priority' => 3
            ];
        } elseif ($vehicle_age > 3) {
            $recommendations[] = [
                'type' => 'maintenance',
                'service' => 'full_service',
                'description' => 'Full service recommended',
                'priority' => 4
            ];
        }
        
        // Mileage-based recommendations
        if (!empty($combined['estimated_annual_mileage'])) {
            if ($combined['estimated_annual_mileage'] > 15000) {
                $recommendations[] = [
                    'type' => 'maintenance',
                    'service' => 'high_mileage_service',
                    'description' => 'High mileage service package recommended',
                    'priority' => 3
                ];
            }
        }
        
        // Risk-based recommendations
        if (($combined['risk_assessment'] ?? 'low') === 'high') {
            $recommendations[] = [
                'type' => 'urgent',
                'service' => 'safety_inspection',
                'description' => 'Safety inspection recommended due to high risk assessment',
                'priority' => 1
            ];
        }
        
        // Sort by priority
        usort($recommendations, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        return array_slice($recommendations, 0, 3); // Return top 3 recommendations
    }
    
    /**
     * Determine pricing category for the vehicle
     * 
     * @param array $combined
     * @return string
     */
    private function determine_pricing_category($combined) {
        $make = strtoupper($combined['make'] ?? '');
        $engine_capacity = intval($combined['engine_capacity'] ?? 1600);
        $vehicle_age = $combined['vehicle_age_years'] ?? 0;
        $fuel_type = $combined['fuel_type_normalized'] ?? 'petrol';
        
        // Premium brands
        if (in_array($make, ['BMW', 'AUDI', 'MERCEDES-BENZ', 'JAGUAR', 'LEXUS', 'PORSCHE'])) {
            return 'premium';
        }
        
        // Electric/Hybrid vehicles
        if (in_array($fuel_type, ['electric', 'hybrid'])) {
            return 'specialist';
        }
        
        // High performance or large engine
        if ($engine_capacity > 2500) {
            return 'performance';
        }
        
        // Older vehicles needing more attention
        if ($vehicle_age > 15) {
            return 'classic';
        }
        
        // Commercial vehicles
        if ($combined['is_commercial'] ?? false) {
            return 'commercial';
        }
        
        return 'standard';
    }
    
    /**
     * Generate fallback MOT data when DVSA API is unavailable
     * 
     * @param string $registration
     * @return array
     */
    private function generate_fallback_mot_data($registration) {
        return [
            'registration' => $registration,
            'current_mot_status' => 'Unknown',
            'mot_expiry_date' => null,
            'mot_tests' => [],
            'latest_mileage' => null,
            'mileage_history' => [],
            'advisory_notices' => [],
            'defects' => [],
            'failure_reasons' => [],
            'passes_total' => 0,
            'fails_total' => 0,
            'maintenance_score' => 75, // Neutral score
            'risk_assessment' => 'low',
            'last_service_indicator' => null,
            'data_source' => 'fallback_data',
            'using_mock_data' => true
        ];
    }
    
    /**
     * Calculate annual mileage from history
     * 
     * @param array $combined
     * @return int|null
     */
    private function calculate_annual_mileage($combined) {
        $mileage_history = $combined['mileage_history'] ?? [];
        
        if (count($mileage_history) < 2) {
            return null;
        }
        
        // Sort by date (newest first)
        usort($mileage_history, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        $recent = $mileage_history[0];
        $older = $mileage_history[count($mileage_history) - 1];
        
        $mileage_diff = $recent['value'] - $older['value'];
        $days_diff = (strtotime($recent['date']) - strtotime($older['date'])) / 86400;
        
        if ($days_diff <= 0) {
            return null;
        }
        
        $daily_mileage = $mileage_diff / $days_diff;
        return intval($daily_mileage * 365.25);
    }
    
    /**
     * Categorize annual mileage
     * 
     * @param int $annual_mileage
     * @return string
     */
    private function categorize_mileage($annual_mileage) {
        if ($annual_mileage < 6000) return 'low';
        if ($annual_mileage < 12000) return 'average';
        if ($annual_mileage < 20000) return 'high';
        return 'very_high';
    }
    
    /**
     * Calculate MOT reliability score
     * 
     * @param array $combined
     * @return int Score from 0-100
     */
    private function calculate_mot_reliability($combined) {
        $pass_rate = $combined['mot_pass_rate'] ?? 0;
        $maintenance_score = $combined['maintenance_score'] ?? 75;
        
        // Base score on pass rate
        $reliability = $pass_rate;
        
        // Adjust based on maintenance score
        $reliability = ($reliability + $maintenance_score) / 2;
        
        // Penalty for recent failures
        if ($combined['current_mot_status'] === 'FAILED') {
            $reliability -= 20;
        }
        
        // Bonus for consistent passes
        if ($pass_rate === 100 && ($combined['mot_passes_total'] ?? 0) >= 3) {
            $reliability += 10;
        }
        
        return max(0, min(100, intval($reliability)));
    }
    
    /**
     * Calculate overall condition score
     * 
     * @param array $combined
     * @return int Score from 0-100
     */
    private function calculate_overall_condition_score($combined) {
        $factors = [];
        
        // Age factor (newer = better)
        $age = $combined['vehicle_age_years'] ?? 0;
        $age_score = max(0, 100 - ($age * 4));
        $factors['age'] = $age_score;
        
        // MOT reliability factor
        if (isset($combined['mot_reliability_score'])) {
            $factors['mot_reliability'] = $combined['mot_reliability_score'];
        }
        
        // Maintenance score factor
        if (isset($combined['maintenance_score'])) {
            $factors['maintenance'] = $combined['maintenance_score'];
        }
        
        // Risk assessment factor
        $risk_scores = ['low' => 90, 'medium' => 70, 'high' => 40];
        $risk = $combined['risk_assessment'] ?? 'low';
        $factors['risk'] = $risk_scores[$risk] ?? 75;
        
        // Mileage factor
        $mileage_category = $combined['mileage_category'] ?? 'average';
        $mileage_scores = ['low' => 95, 'average' => 85, 'high' => 70, 'very_high' => 55];
        $factors['mileage'] = $mileage_scores[$mileage_category] ?? 80;
        
        // Calculate weighted average
        $total_weight = count($factors);
        $weighted_sum = array_sum($factors);
        
        return $total_weight > 0 ? intval($weighted_sum / $total_weight) : 75;
    }
    
    /**
     * Predict service needs
     * 
     * @param array $combined
     * @return array
     */
    private function predict_service_needs($combined) {
        $needs = [];
        
        $age = $combined['vehicle_age_years'] ?? 0;
        $mileage_category = $combined['mileage_category'] ?? 'average';
        
        // Age-based predictions
        if ($age > 10) {
            $needs[] = 'timing_belt_check';
            $needs[] = 'suspension_inspection';
        }
        
        if ($age > 7) {
            $needs[] = 'brake_system_check';
            $needs[] = 'exhaust_inspection';
        }
        
        if ($age > 5) {
            $needs[] = 'coolant_system_service';
        }
        
        // Mileage-based predictions
        if ($mileage_category === 'high' || $mileage_category === 'very_high') {
            $needs[] = 'frequent_oil_changes';
            $needs[] = 'brake_pad_inspection';
            $needs[] = 'tyre_wear_check';
        }
        
        // Advisory-based predictions
        foreach ($combined['advisory_notices'] ?? [] as $advisory) {
            $text = strtolower($advisory['text'] ?? '');
            if (strpos($text, 'tyre') !== false) {
                $needs[] = 'tyre_replacement';
            }
            if (strpos($text, 'brake') !== false) {
                $needs[] = 'brake_service';
            }
            if (strpos($text, 'exhaust') !== false) {
                $needs[] = 'exhaust_repair';
            }
        }
        
        return array_unique($needs);
    }
    
    /**
     * Get vehicle age category
     * 
     * @param int $age
     * @return string
     */
    private function get_age_category($age) {
        if ($age <= 3) return 'new';
        if ($age <= 7) return 'young';
        if ($age <= 15) return 'mature';
        return 'classic';
    }
    
    /**
     * Clear all cached data for a vehicle
     * 
     * @param string $registration
     */
    public function clear_vehicle_cache($registration) {
        $this->dvla_api->clear_cache($registration);
        $this->dvsa_api->clear_cache($registration);
        $this->log("Cleared all cached data for {$registration}");
    }
    
    /**
     * Test all API connections
     * 
     * @return array Test results
     */
    public function test_all_connections() {
        $results = [];
        
        // Test DVLA
        $dvla_result = $this->dvla_api->test_connection();
        $results['dvla'] = [
            'success' => !is_wp_error($dvla_result),
            'message' => is_wp_error($dvla_result) ? $dvla_result->get_error_message() : 'Connection successful'
        ];
        
        // Test DVSA
        $dvsa_result = $this->dvsa_api->test_connection();
        $results['dvsa'] = [
            'success' => !is_wp_error($dvsa_result),
            'message' => is_wp_error($dvsa_result) ? $dvsa_result->get_error_message() : 'Connection successful'
        ];
        
        return $results;
    }
    
    /**
     * Format registration for display
     * 
     * @param string $registration
     * @return string
     */
    private function format_registration($registration) {
        $clean_reg = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($registration)));
        
        // Current format: AB12CDE -> AB12 CDE
        if (preg_match('/^([A-Z]{2})([0-9]{2})([A-Z]{3})$/', $clean_reg, $matches)) {
            return $matches[1] . $matches[2] . ' ' . $matches[3];
        }
        
        return $clean_reg;
    }
    
    /**
     * Extract year from date string
     * 
     * @param string|null $date_string
     * @return int|null
     */
    private function extract_year_from_date($date_string) {
        if (empty($date_string)) {
            return null;
        }
        
        $timestamp = strtotime($date_string);
        if ($timestamp === false) {
            return null;
        }
        
        return intval(date('Y', $timestamp));
    }
    
    /**
     * Normalize fuel type
     * 
     * @param string $fuel_type
     * @return string
     */
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
    
    /**
     * Log messages for debugging
     * 
     * @param string $message
     * @param string $level
     */
    private function log($message, $level = 'info') {
        if ($this->debug_mode) {
            error_log("[Blue Motors Combined Lookup] [{$level}] {$message}");
        }
    }
}
