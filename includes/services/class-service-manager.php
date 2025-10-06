<?php
/**
 * Service Manager Class for Blue Motors Southampton
 * 
 * Basic service management functionality
 * 
 * @package BlueMotosSouthampton
 * @version 1.0.0
 */

namespace BlueMotosSouthampton\Services;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ServiceManager {
    
    /**
     * Available services configuration
     */
    private static $services = array();
    
    /**
     * Initialize the service manager
     */
    public static function init() {
        self::load_default_services();
        add_action('init', array(__CLASS__, 'setup_hooks'));
    }
    
    /**
     * Setup WordPress hooks
     */
    public static function setup_hooks() {
        // Hook for custom service configurations
        do_action('bms_services_loaded');
    }
    
    /**
     * Load default services configuration
     */
    private static function load_default_services() {
        self::$services = array(
            'mot_test' => array(
                'id' => 'mot_test',
                'name' => 'MOT Test',
                'description' => 'Ministry of Transport test for vehicle roadworthiness',
                'base_price' => 40.00,
                'duration' => 60, // minutes
                'engine_pricing' => false,
                'vat_included' => true,
                'category' => 'testing',
                'icon' => '🔍'
            ),
            'interim_service' => array(
                'id' => 'interim_service',
                'name' => 'Interim Service',
                'description' => 'Basic service including oil change and essential checks',
                'base_price' => 140.00,
                'duration' => 90,
                'engine_pricing' => true,
                'vat_included' => false,
                'category' => 'service',
                'icon' => '🔧'
            ),
            'full_service' => array(
                'id' => 'full_service', 
                'name' => 'Full Service',
                'description' => 'Comprehensive service including all checks and replacements',
                'base_price' => 225.00,
                'duration' => 120,
                'engine_pricing' => true,
                'vat_included' => false,
                'category' => 'service',
                'icon' => '⚙️'
            )
        );
    }
    
    /**
     * Get all available services
     */
    public static function get_services($active_only = true) {
        return self::$services;
    }
    
    /**
     * Get a specific service by ID
     */
    public static function get_service($service_id) {
        return isset(self::$services[$service_id]) ? self::$services[$service_id] : null;
    }
    
    /**
     * Add a new service
     */
    public static function add_service($service_id, $service_data) {
        self::$services[$service_id] = $service_data;
    }
    
    /**
     * Update a service
     */
    public static function update_service($service_id, $service_data) {
        if (isset(self::$services[$service_id])) {
            self::$services[$service_id] = array_merge(self::$services[$service_id], $service_data);
            return true;
        }
        return false;
    }
    
    /**
     * Remove a service
     */
    public static function remove_service($service_id) {
        if (isset(self::$services[$service_id])) {
            unset(self::$services[$service_id]);
            return true;
        }
        return false;
    }
    
    /**
     * Get services by category
     */
    public static function get_services_by_category($category) {
        $filtered_services = array();
        
        foreach (self::$services as $service_id => $service) {
            if (isset($service['category']) && $service['category'] === $category) {
                $filtered_services[$service_id] = $service;
            }
        }
        
        return $filtered_services;
    }
    
    /**
     * Get service categories
     */
    public static function get_service_categories() {
        $categories = array();
        
        foreach (self::$services as $service) {
            if (isset($service['category']) && !in_array($service['category'], $categories)) {
                $categories[] = $service['category'];
            }
        }
        
        return $categories;
    }
    
    /**
     * Validate service data
     */
    public static function validate_service_data($service_data) {
        $required_fields = array('id', 'name', 'base_price');
        
        foreach ($required_fields as $field) {
            if (!isset($service_data[$field]) || empty($service_data[$field])) {
                return new \WP_Error('missing_field', "Required field '$field' is missing");
            }
        }
        
        // Validate price
        if (!is_numeric($service_data['base_price']) || $service_data['base_price'] < 0) {
            return new \WP_Error('invalid_price', 'Service price must be a positive number');
        }
        
        return true;
    }
    
    /**
     * Check if service exists
     */
    public static function service_exists($service_id) {
        return isset(self::$services[$service_id]);
    }
    
    /**
     * Get service duration in minutes
     */
    public static function get_service_duration($service_id) {
        $service = self::get_service($service_id);
        return $service ? $service['duration'] : 60; // default 60 minutes
    }
    
    /**
     * Get available time slots for a service
     */
    public static function get_available_slots($service_id, $date) {
        $duration = self::get_service_duration($service_id);
        
        // Use the booking integration to get proper availability with Google Calendar
        if (class_exists('BMS_Booking_Integration')) {
            return \BMS_Booking_Integration::get_dynamic_available_slots(array(), $date, $duration);
        }
        
        // Fallback to basic business hours with Google Calendar checking
        $available_slots = self::get_basic_time_slots($date, $duration);
        
        // Filter out busy slots from Google Calendar if available
        if (class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
            
            if ($calendar_service->is_available()) {
                $busy_slots = $calendar_service->get_busy_slots($date);
                $busy_times = array_column($busy_slots, 'start');
                
                // Remove conflicting time slots
                $available_slots = array_filter($available_slots, function($slot) use ($busy_times, $duration, $date, $calendar_service) {
                    return $calendar_service->is_slot_available($date, $slot, $duration);
                });
            }
        }
        
        return array_values($available_slots);
    }
    
    /**
     * Get basic time slots based on business hours
     */
    private static function get_basic_time_slots($date, $duration) {
        // Basic opening hours: 8 AM to 6 PM weekdays, 8 AM to 4 PM Saturday
        $day_of_week = date('N', strtotime($date)); // 1 = Monday, 7 = Sunday
        
        if ($day_of_week == 7) { // Sunday - closed
            return array();
        }
        
        $start_hour = 8;
        $end_hour = ($day_of_week == 6) ? 16 : 18; // Saturday until 4 PM, others until 6 PM
        
        // Generate hourly slots, ensuring enough time for the service
        $slots = array();
        
        for ($hour = $start_hour; $hour < $end_hour; $hour++) {
            // Check if we have enough time before closing
            $slot_end_time = $hour * 60 + $duration; // Convert to minutes
            $closing_time = $end_hour * 60;
            
            if ($slot_end_time <= $closing_time) {
                $slots[] = sprintf('%02d:00', $hour);
            }
        }
        
        return $slots;
    }
}
