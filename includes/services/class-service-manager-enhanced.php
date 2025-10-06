<?php
/**
 * Enhanced Service Configuration Manager
 * 
 * Manages service definitions, pricing, and availability with professional features
 * Extends the basic ServiceManager class
 * 
 * @package BlueMotosSouthampton
 * @since 1.2.0
 */

namespace BlueMotosSouthampton\Services;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ServiceManagerEnhanced extends ServiceManager {
    
    /**
     * Default services configuration
     */
    private static $default_services = array(
        'mot_test' => array(
            'name' => 'MOT Test',
            'description' => 'Mandatory annual test for vehicles over 3 years old',
            'base_price' => 40.00,
            'pricing_type' => 'fixed',
            'duration' => 60, // minutes
            'category' => 'testing',
            'enabled' => true,
            'sort_order' => 1,
            'features' => array(
                'Safety inspection',
                'Emissions test',
                'Roadworthiness check',
                'Official MOT certificate'
            )
        ),
        'full_service' => array(
            'name' => 'Full Service',
            'description' => 'Comprehensive vehicle service with detailed inspection',
            'base_price' => 149.00,
            'pricing_type' => 'engine_based',
            'duration' => 120, // minutes
            'category' => 'servicing',
            'enabled' => true,
            'sort_order' => 2,
            'features' => array(
                'Engine oil change',
                'Filter replacements',
                'Brake inspection',
                'Tire condition check',
                'Battery test',
                'Lights and signals check',
                'Comprehensive report'
            )
        ),
        'interim_service' => array(
            'name' => 'Interim Service',
            'description' => 'Essential maintenance service between full services',
            'base_price' => 89.00,
            'pricing_type' => 'engine_based',
            'duration' => 90, // minutes
            'category' => 'servicing',
            'enabled' => true,
            'sort_order' => 3,
            'features' => array(
                'Engine oil change',
                'Oil filter replacement',
                'Basic safety checks',
                'Fluid level checks',
                'Service report'
            )
        ),
        'brake_check' => array(
            'name' => 'Brake Check',
            'description' => 'Comprehensive brake system inspection',
            'base_price' => 25.00,
            'pricing_type' => 'fixed',
            'duration' => 30, // minutes
            'category' => 'inspection',
            'enabled' => true,
            'sort_order' => 4,
            'features' => array(
                'Brake pad inspection',
                'Brake disc check',
                'Brake fluid level',
                'Brake system report'
            )
        ),
        'diagnostic_check' => array(
            'name' => 'Diagnostic Check',
            'description' => 'Computer diagnostic scan for engine issues',
            'base_price' => 45.00,
            'pricing_type' => 'fixed',
            'duration' => 45, // minutes
            'category' => 'diagnostics',
            'enabled' => true,
            'sort_order' => 5,
            'features' => array(
                'Engine fault code scan',
                'Computer system check',
                'Diagnostic report',
                'Fault explanation'
            )
        )
    );
    
    /**
     * Engine-based pricing matrix
     */
    private static $engine_pricing = array(
        'petrol' => array(
            'up_to_1000' => array('interim' => 115, 'full' => 205),
            'up_to_1600' => array('interim' => 140, 'full' => 225),
            'up_to_2000' => array('interim' => 175, 'full' => 245),
            'up_to_3500' => array('interim' => 185, 'full' => 255),
            'over_3500' => array('interim' => 215, 'full' => 285)
        ),
        'diesel' => array(
            'up_to_1000' => array('interim' => 140, 'full' => 225),
            'up_to_1600' => array('interim' => 175, 'full' => 245),
            'up_to_2000' => array('interim' => 185, 'full' => 255),
            'up_to_3500' => array('interim' => 215, 'full' => 285),
            'over_3500' => array('interim' => 245, 'full' => 315)
        ),
        'hybrid' => array(
            'up_to_1000' => array('interim' => 115, 'full' => 205),
            'up_to_1600' => array('interim' => 165, 'full' => 235),
            'up_to_2000' => array('interim' => 175, 'full' => 245),
            'up_to_3500' => array('interim' => 205, 'full' => 275),
            'over_3500' => array('interim' => 235, 'full' => 305)
        ),
        'electric' => array(
            'all' => array('interim' => 95, 'full' => 185)
        )
    );
    
    /**
     * Initialize services if not set
     */
    public static function init_services() {
        $services = get_option('bms_services', array());
        
        if (empty($services)) {
            // Load enhanced services for Phase 3
            $enhanced_services = self::load_enhanced_services();
            update_option('bms_services', $enhanced_services);
            update_option('bms_engine_pricing', self::$engine_pricing);
            return $enhanced_services;
        }
        
        return $services;
    }
    
    /**
     * Load enhanced services configuration (Phase 3)
     * Includes all industry leaders equivalent services plus our advantages
     */
    public static function load_enhanced_services() {
        // Load enhanced services from config file
        $enhanced_services_file = BMS_PLUGIN_DIR . 'config/enhanced-services.php';
        $enhanced_services = array();
        
        if (file_exists($enhanced_services_file)) {
            $enhanced_services = include $enhanced_services_file;
        }
        
        // Merge with default services
        $all_services = array_merge(self::$default_services, $enhanced_services);
        
        return $all_services;
    }
    
    /**
     * Get services by category
     * 
     * @param string|null $category Category filter
     * @return array Services filtered by category
     */
    public static function get_services_by_category($category = null) {
        $services = self::get_services(true);
        
        if (!$category) {
            return $services;
        }
        
        return array_filter($services, function($service) use ($category) {
            return isset($service['category']) && $service['category'] === $category;
        });
    }
    
    /**
     * Get all services
     * 
     * @param bool $enabled_only Return only enabled services
     * @return array Services array
     */
    public static function get_services($enabled_only = false) {
        $services = get_option('bms_services', self::$default_services);
        
        if ($enabled_only) {
            $services = array_filter($services, function($service) {
                return isset($service['enabled']) && $service['enabled'];
            });
        }
        
        // Sort by sort_order
        uasort($services, function($a, $b) {
            $order_a = isset($a['sort_order']) ? $a['sort_order'] : 999;
            $order_b = isset($b['sort_order']) ? $b['sort_order'] : 999;
            return $order_a - $order_b;
        });
        
        return $services;
    }
    
    /**
     * Get single service
     * 
     * @param string $service_id Service ID
     * @return array|false Service data or false if not found
     */
    public static function get_service($service_id) {
        $services = self::get_services();
        return isset($services[$service_id]) ? $services[$service_id] : false;
    }
    
    /**
     * Update service
     * 
     * @param string $service_id Service ID
     * @param array $service_data Service data
     * @return bool Success
     */
    public static function update_service($service_id, $service_data) {
        $services = self::get_services();
        $services[$service_id] = $service_data;
        return update_option('bms_services', $services);
    }
    
    /**
     * Delete service
     * 
     * @param string $service_id Service ID
     * @return bool Success
     */
    public static function delete_service($service_id) {
        $services = self::get_services();
        unset($services[$service_id]);
        return update_option('bms_services', $services);
    }
    
    /**
     * Calculate service price
     * 
     * @param string $service_id Service ID
     * @param array $vehicle_data Vehicle information
     * @return float Calculated price
     */
    public static function calculate_price($service_id, $vehicle_data = array()) {
        $service = self::get_service($service_id);
        if (!$service) {
            return 0;
        }
        
        if ($service['pricing_type'] === 'fixed') {
            return floatval($service['base_price']);
        }
        
        if ($service['pricing_type'] === 'engine_based') {
            return self::calculate_engine_based_price($service_id, $vehicle_data);
        }
        
        return floatval($service['base_price']);
    }
    
    /**
     * Calculate engine-based pricing
     * 
     * @param string $service_id Service ID  
     * @param array $vehicle_data Vehicle information
     * @return float Calculated price
     */
    private static function calculate_engine_based_price($service_id, $vehicle_data) {
        $engine_pricing = get_option('bms_engine_pricing', self::$engine_pricing);
        $service = self::get_service($service_id);
        
        if (!$service || !isset($vehicle_data['engine_size']) || !isset($vehicle_data['fuel_type'])) {
            return floatval($service['base_price']);
        }
        
        $engine_size = intval($vehicle_data['engine_size']);
        $fuel_type = strtolower($vehicle_data['fuel_type']);
        
        // Normalize fuel type
        if (in_array($fuel_type, array('petrol', 'gasoline', 'gas'))) {
            $fuel_type = 'petrol';
        } elseif (in_array($fuel_type, array('diesel', 'oil'))) {
            $fuel_type = 'diesel';
        } elseif (in_array($fuel_type, array('hybrid', 'petrol/electric', 'diesel/electric'))) {
            $fuel_type = 'hybrid';
        } elseif (in_array($fuel_type, array('electric', 'battery', 'ev'))) {
            $fuel_type = 'electric';
        } else {
            $fuel_type = 'petrol'; // Default fallback
        }
        
        // Get pricing matrix for fuel type
        if (!isset($engine_pricing[$fuel_type])) {
            return floatval($service['base_price']);
        }
        
        $pricing_matrix = $engine_pricing[$fuel_type];
        
        // Handle electric vehicles (single pricing)
        if ($fuel_type === 'electric') {
            $service_type = ($service_id === 'full_service') ? 'full' : 'interim';
            return isset($pricing_matrix['all'][$service_type]) ? 
                floatval($pricing_matrix['all'][$service_type]) : 
                floatval($service['base_price']);
        }
        
        // Determine engine size category
        $size_category = 'up_to_1600'; // Default
        if ($engine_size <= 1000) {
            $size_category = 'up_to_1000';
        } elseif ($engine_size <= 1600) {
            $size_category = 'up_to_1600';
        } elseif ($engine_size <= 2000) {
            $size_category = 'up_to_2000';
        } elseif ($engine_size <= 3500) {
            $size_category = 'up_to_3500';
        } else {
            $size_category = 'over_3500';
        }
        
        // Get service type for pricing
        $service_type = 'full'; // Default
        if ($service_id === 'interim_service') {
            $service_type = 'interim';
        } elseif ($service_id === 'full_service') {
            $service_type = 'full';
        }
        
        // Return calculated price
        if (isset($pricing_matrix[$size_category][$service_type])) {
            return floatval($pricing_matrix[$size_category][$service_type]);
        }
        
        return floatval($service['base_price']);
    }
    
    /**
     * Get service categories (Enhanced for Phase 3)
     * 
     * @return array Categories with icons and descriptions
     */
    public static function get_categories() {
        return array(
            'general' => array(
                'name' => 'General Services',
                'description' => 'Core automotive services',
                'icon' => 'fa-tools',
                'services' => array('mot_test', 'full_service', 'interim_service')
            ),
            'climate' => array(
                'name' => 'Air Conditioning',
                'description' => 'Climate control and AC services',
                'icon' => 'fa-snowflake',
                'services' => array('air_con_regas', 'air_con_service'),
                'seasonal' => true,
                'competitive_note' => 'industry leaders offers this - we do too!'
            ),
            'safety' => array(
                'name' => 'Brakes & Safety',
                'description' => 'Critical safety systems',
                'icon' => 'fa-ban',
                'services' => array('brake_check', 'brake_service', 'suspension_check'),
                'safety_critical' => true
            ),
            'electrical' => array(
                'name' => 'Battery & Electrical',
                'description' => 'Electrical system services',
                'icon' => 'fa-battery-three-quarters',
                'services' => array('battery_test', 'battery_replacement'),
                'seasonal' => true // Popular in winter
            ),
            'emissions' => array(
                'name' => 'Exhaust & Emissions',
                'description' => 'Exhaust and emissions services',
                'icon' => 'fa-smog',
                'services' => array('exhaust_check', 'exhaust_repair')
            ),
            'drivetrain' => array(
                'name' => 'Clutch & Drivetrain',
                'description' => 'Transmission and drivetrain',
                'icon' => 'fa-cog',
                'services' => array('clutch_check')
            ),
            'tyres' => array(
                'name' => 'Tyres & Fitting',
                'description' => 'Tyre services and fitting',
                'icon' => 'fa-circle',
                'services' => array('tyre_fitting'),
                'competitive_advantage' => 'Online ordering - F1 requires phone calls!'
            ),
            'testing' => array(
                'name' => 'Testing & Inspection', 
                'description' => 'MOT and diagnostic testing',
                'icon' => 'fa-clipboard-check',
                'services' => array('mot_test', 'diagnostic_check')
            ),
            'servicing' => array(
                'name' => 'Servicing & Maintenance',
                'description' => 'Regular maintenance services', 
                'icon' => 'fa-oil-can',
                'services' => array('full_service', 'interim_service')
            )
        );
    }
    
    /**
     * Get service categories (legacy method for compatibility)
     * 
     * @return array Simple categories array
     */
    public static function get_simple_categories() {
        return array(
            'general' => 'General Services',
            'climate' => 'Air Conditioning',
            'safety' => 'Brakes & Safety', 
            'electrical' => 'Battery & Electrical',
            'emissions' => 'Exhaust & Emissions',
            'drivetrain' => 'Clutch & Drivetrain',
            'tyres' => 'Tyres & Fitting',
            'testing' => 'Testing & Inspection',
            'servicing' => 'Servicing & Maintenance',
            'inspection' => 'Safety Inspections',
            'diagnostics' => 'Diagnostics & Fault Finding',
            'repairs' => 'Repairs & Parts',
            'other' => 'Other Services'
        );
    }
    
    /**
     * Get pricing types
     * 
     * @return array Pricing types
     */
    public static function get_pricing_types() {
        return array(
            'fixed' => 'Fixed Price',
            'engine_based' => 'Engine Size Based',
            'custom' => 'Custom Pricing'
        );
    }
    
    /**
     * Save engine pricing matrix
     * 
     * @param array $pricing_matrix Pricing matrix
     * @return bool Success
     */
    public static function save_engine_pricing($pricing_matrix) {
        return update_option('bms_engine_pricing', $pricing_matrix);
    }
    
    /**
     * Get engine pricing matrix
     * 
     * @return array Pricing matrix
     */
    public static function get_engine_pricing() {
        return get_option('bms_engine_pricing', self::$engine_pricing);
    }
}
