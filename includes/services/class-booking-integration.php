<?php
/**
 * Booking System Integration Service
 * 
 * Connects frontend booking system with admin configuration settings
 * 
 * @package BlueMotosSouthampton
 * @since 1.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BMS_Booking_Integration {
    
    /**
     * Initialize integration hooks
     */
    public static function init() {
        // Hook into WordPress init to replace constants
        add_action('init', array(__CLASS__, 'setup_dynamic_settings'), 1);
        
        // Hook into booking system to use admin settings
        add_filter('bms_get_business_hours', array(__CLASS__, 'get_dynamic_business_hours'));
        add_filter('bms_get_booking_settings', array(__CLASS__, 'get_dynamic_booking_settings'));
        add_filter('bms_get_payment_settings', array(__CLASS__, 'get_dynamic_payment_settings'));
        add_filter('bms_get_services_list', array(__CLASS__, 'get_dynamic_services_list'));
        add_filter('bms_calculate_service_price', array(__CLASS__, 'calculate_dynamic_price'), 10, 3);
        
        // Hook into availability calculation
        add_filter('bms_get_available_slots', array(__CLASS__, 'get_dynamic_available_slots'), 10, 3);
        
        // Hook into booking creation
        add_action('bms_before_booking_save', array(__CLASS__, 'prepare_booking_data'), 10, 2);
        add_action('bms_after_booking_save', array(__CLASS__, 'create_calendar_event'), 10, 2);
        
        // Shortcode integration
        add_filter('bms_vehicle_lookup_config', array(__CLASS__, 'get_dynamic_lookup_config'));
    }
    
    /**
     * Setup dynamic settings to override constants
     */
    public static function setup_dynamic_settings() {
        // Only run if migration has been completed
        if (!BMS_Settings_Migrator::is_migrated()) {
            return;
        }
        
        // Get settings from database
        $business_info = BMS_Settings_Migrator::get_business_info();
        $business_hours = BMS_Settings_Migrator::get_business_hours();
        $payment_settings = BMS_Settings_Migrator::get_payment_settings();
        $booking_settings = BMS_Settings_Migrator::get_booking_settings();
        
        // Define dynamic constants (if not already defined)
        if (!defined('BMS_DYNAMIC_CONFIG')) {
            define('BMS_DYNAMIC_CONFIG', true);
            
            // Business information
            define('BMS_BUSINESS_NAME', $business_info['name']);
            define('BMS_BUSINESS_ADDRESS', $business_info['address']);
            define('BMS_BUSINESS_PHONE', $business_info['phone']);
            define('BMS_BUSINESS_EMAIL', $business_info['email']);
            
            // Business hours
            define('BMS_HOURS_WEEKDAY_OPEN', $business_hours['weekday_open']);
            define('BMS_HOURS_WEEKDAY_CLOSE', $business_hours['weekday_close']);
            define('BMS_HOURS_SATURDAY_OPEN', $business_hours['saturday_open']);
            define('BMS_HOURS_SATURDAY_CLOSE', $business_hours['saturday_close']);
            define('BMS_HOURS_SUNDAY_OPEN', $business_hours['sunday_open']);
            define('BMS_HOURS_SUNDAY_CLOSE', $business_hours['sunday_close']);
            
            // Payment settings
            define('BMS_PAYMENT_CURRENCY', $payment_settings['currency']);
            define('BMS_VAT_RATE', $payment_settings['vat_rate']);
            define('BMS_STRIPE_ENABLED', $payment_settings['stripe_enabled']);
            define('BMS_STRIPE_PUBLIC_KEY', $payment_settings['stripe_public_key']);
            define('BMS_STRIPE_SECRET_KEY', $payment_settings['stripe_secret_key']);
            
            // Booking settings
            define('BMS_BOOKING_MIN_DAYS', $booking_settings['min_days']);
            define('BMS_BOOKING_MAX_DAYS', $booking_settings['max_days']);
            define('BMS_BOOKING_SLOT_DURATION', $booking_settings['slot_duration']);
            define('BMS_BOOKING_REFERENCE_PREFIX', $booking_settings['reference_prefix']);
        }
    }
    
    /**
     * Get dynamic business hours
     */
    public static function get_dynamic_business_hours($default_hours = array()) {
        $business_hours = BMS_Settings_Migrator::get_business_hours();
        
        return array(
            'monday' => array(
                'open' => $business_hours['weekday_open'],
                'close' => $business_hours['weekday_close']
            ),
            'tuesday' => array(
                'open' => $business_hours['weekday_open'],
                'close' => $business_hours['weekday_close']
            ),
            'wednesday' => array(
                'open' => $business_hours['weekday_open'],
                'close' => $business_hours['weekday_close']
            ),
            'thursday' => array(
                'open' => $business_hours['weekday_open'],
                'close' => $business_hours['weekday_close']
            ),
            'friday' => array(
                'open' => $business_hours['weekday_open'],
                'close' => $business_hours['weekday_close']
            ),
            'saturday' => array(
                'open' => $business_hours['saturday_open'],
                'close' => $business_hours['saturday_close']
            ),
            'sunday' => array(
                'open' => $business_hours['sunday_open'],
                'close' => $business_hours['sunday_close']
            )
        );
    }
    
    /**
     * Get dynamic booking settings
     */
    public static function get_dynamic_booking_settings($default_settings = array()) {
        return BMS_Settings_Migrator::get_booking_settings();
    }
    
    /**
     * Get dynamic payment settings
     */
    public static function get_dynamic_payment_settings($default_settings = array()) {
        return BMS_Settings_Migrator::get_payment_settings();
    }
    
    /**
     * Get dynamic services list
     */
    public static function get_dynamic_services_list($default_services = array()) {
        return \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true); // Only enabled services
    }
    
    /**
     * Calculate dynamic service price
     */
    public static function calculate_dynamic_price($default_price, $service_id, $vehicle_data = array()) {
        return \BlueMotosSouthampton\Services\ServiceManagerEnhanced::calculate_price($service_id, $vehicle_data);
    }
    
    /**
     * Get dynamic available slots
     */
    public static function get_dynamic_available_slots($default_slots, $date, $service_duration = 60) {
        $business_hours = self::get_dynamic_business_hours();
        $booking_settings = self::get_dynamic_booking_settings();
        
        // Get day of week
        $day_of_week = strtolower(date('l', strtotime($date)));
        
        if (!isset($business_hours[$day_of_week])) {
            return array();
        }
        
        $day_hours = $business_hours[$day_of_week];
        
        // Check if closed
        if ($day_hours['open'] === 'closed' || $day_hours['close'] === 'closed') {
            return array();
        }
        
        // Generate time slots
        $slots = array();
        $start_time = strtotime($date . ' ' . $day_hours['open']);
        $end_time = strtotime($date . ' ' . $day_hours['close']);
        $slot_duration = $booking_settings['slot_duration'] * 60; // Convert to seconds
        
        for ($time = $start_time; $time < $end_time - ($service_duration * 60); $time += $slot_duration) {
            $slots[] = date('H:i', $time);
        }
        
        // Remove booked slots
        $booked_slots = self::get_booked_slots($date);
        $available_slots = array_diff($slots, $booked_slots);
        
        return array_values($available_slots);
    }
    
    /**
     * Get booked slots for a date
     */
    private static function get_booked_slots($date) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        // Get booked slots from database
        $db_booked = $wpdb->get_col($wpdb->prepare(
            "SELECT TIME_FORMAT(booking_time, '%%H:%%i') 
             FROM $table_name 
             WHERE booking_date = %s 
             AND booking_status NOT IN ('cancelled', 'no_show')",
            $date,
        ));
        
        $booked_slots = $db_booked ?: array();
        
        // Also check Google Calendar for busy slots
        if (class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
            $busy_slots = $calendar_service->get_busy_slots($date);
            
            foreach ($busy_slots as $busy_slot) {
                if (!in_array($busy_slot['start'], $booked_slots)) {
                    $booked_slots[] = $busy_slot['start'];
                }
            }
        }
        
        return $booked_slots;
    }
    
    /**
     * Prepare booking data before save
     */
    public static function prepare_booking_data(&$booking_data, $form_data) {
        // Add dynamic business information
        $business_info = BMS_Settings_Migrator::get_business_info();
        $booking_settings = BMS_Settings_Migrator::get_booking_settings();
        
        // Generate booking reference if not set
        if (empty($booking_data['booking_reference'])) {
            $booking_data['booking_reference'] = self::generate_booking_reference();
        }
        
        // Add business context
        $booking_data['business_name'] = $business_info['name'];
        $booking_data['business_address'] = $business_info['address'];
        $booking_data['business_phone'] = $business_info['phone'];
        $booking_data['business_email'] = $business_info['email'];
        
        // Calculate accurate pricing
        if (isset($form_data['service_id']) && isset($form_data['vehicle_data'])) {
            $calculated_price = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::calculate_price(
                $form_data['service_id'], 
                $form_data['vehicle_data'],
            );
            
            if ($calculated_price > 0) {
                $booking_data['price'] = $calculated_price;
                
                // Add VAT if configured
                $payment_settings = BMS_Settings_Migrator::get_payment_settings();
                if ($payment_settings['vat_rate'] > 0) {
                    $booking_data['vat_amount'] = $calculated_price * $payment_settings['vat_rate'];
                    $booking_data['total_price'] = $calculated_price + $booking_data['vat_amount'];
                } else {
                    $booking_data['total_price'] = $calculated_price;
                }
            }
        }
    }
    
    /**
     * Generate booking reference
     */
    public static function generate_booking_reference() {
        $booking_settings = BMS_Settings_Migrator::get_booking_settings();
        $prefix = $booking_settings['reference_prefix'];
        
        // Generate unique reference
        $reference = $prefix . '-' . strtoupper(substr(uniqid(), -6));
        
        // Ensure uniqueness
        global $wpdb;
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE booking_reference = %s",
            $reference,
        ));
        
        if ($exists > 0) {
            // Recursively generate new reference if exists
            return self::generate_booking_reference();
        }
        
        return $reference;
    }
    
    /**
     * Get dynamic vehicle lookup config
     */
    public static function get_dynamic_lookup_config($default_config = array()) {
        $business_info = BMS_Settings_Migrator::get_business_info();
        $services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true);
        
        return array_merge($default_config, array(
            'business_name' => $business_info['name'],
            'business_phone' => $business_info['phone'],
            'available_services' => $services,
            'booking_enabled' => true,
            'payment_required' => BMS_Settings_Migrator::get_setting('bms_payment_required', 'BM_PAYMENT_REQUIRED', true)
        ));
    }
    
    /**
     * Validate booking date against business rules
     */
    public static function validate_booking_date($date) {
        $booking_settings = self::get_dynamic_booking_settings();
        $business_hours = self::get_dynamic_business_hours();
        
        // Check minimum days ahead
        $min_date = date('Y-m-d', strtotime('+' . $booking_settings['min_days'] . ' days'));
        if ($date < $min_date) {
            return array(
                'valid' => false,
                'message' => 'Bookings must be made at least ' . $booking_settings['min_days'] . ' days in advance.'
            );
        }
        
        // Check maximum days ahead
        $max_date = date('Y-m-d', strtotime('+' . $booking_settings['max_days'] . ' days'));
        if ($date > $max_date) {
            return array(
                'valid' => false,
                'message' => 'Bookings can only be made up to ' . $booking_settings['max_days'] . ' days in advance.'
            );
        }
        
        // Check if business is open on that day
        $day_of_week = strtolower(date('l', strtotime($date)));
        if (!isset($business_hours[$day_of_week]) || 
            $business_hours[$day_of_week]['open'] === 'closed') {
            return array(
                'valid' => false,
                'message' => 'We are closed on ' . date('l', strtotime($date)) . 's.'
            );
        }
        
        return array('valid' => true);
    }
    
    /**
     * Get booking summary for confirmation
     */
    public static function get_booking_summary($booking_data) {
        $business_info = BMS_Settings_Migrator::get_business_info();
        $payment_settings = BMS_Settings_Migrator::get_payment_settings();
        
        return array(
            'business' => $business_info,
            'booking' => $booking_data,
            'payment' => $payment_settings,
            'confirmation_url' => self::get_confirmation_url($booking_data['booking_reference'] ?? ''),
            'cancellation_url' => self::get_cancellation_url($booking_data['booking_reference'] ?? '')
        );
    }
    
    /**
     * Get confirmation URL
     */
    private static function get_confirmation_url($reference) {
        return add_query_arg(array(
            'bms_action' => 'confirm',
            'ref' => $reference
        ), home_url());
    }
    
    /**
     * Get cancellation URL
     */
    private static function get_cancellation_url($reference) {
        return add_query_arg(array(
            'bms_action' => 'cancel',
            'ref' => $reference
        ), home_url());
    }
    
    /**
     * Create Google Calendar event after booking is saved
     */
    public static function create_calendar_event($booking_id, $booking_data) {
        // Only create calendar event if Google Calendar service is available
        if (!class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            return;
        }
        
        $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
        
        if (!$calendar_service->is_available()) {
            error_log('Google Calendar service not available for booking: ' . $booking_id);
            return;
        }
        
        // Prepare calendar event data
        $calendar_data = array(
            'customer_name' => $booking_data['customer_name'] ?? '',
            'customer_email' => $booking_data['customer_email'] ?? '',
            'customer_phone' => $booking_data['customer_phone'] ?? '',
            'vehicle_reg' => $booking_data['vehicle_reg'] ?? '',
            'vehicle_make' => $booking_data['vehicle_make'] ?? '',
            'vehicle_model' => $booking_data['vehicle_model'] ?? '',
            'service_name' => $booking_data['service_name'] ?? 'Service Appointment',
            'booking_date' => $booking_data['booking_date'] ?? '',
            'booking_time' => $booking_data['booking_time'] ?? '',
            'booking_reference' => $booking_data['booking_reference'] ?? '',
            'special_requirements' => $booking_data['special_requirements'] ?? '',
            'duration' => $booking_data['service_duration'] ?? 60
        );
        
        // Create event
        $result = $calendar_service->create_event($calendar_data);
        
        if (!is_wp_error($result) && $result['success']) {
            // Store calendar event ID in booking meta
            global $wpdb;
            $meta_table = $wpdb->prefix . 'bms_booking_meta';
            
            // Check if meta table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '{$meta_table}'") == $meta_table) {
                $wpdb->insert(
                    $meta_table,
                    array(
                        'booking_id' => $booking_id,
                        'meta_key' => 'google_calendar_event_id',
                        'meta_value' => $result['event_id']
                    ),
                    array('%d', '%s', '%s')
                );
                
                $wpdb->insert(
                    $meta_table,
                    array(
                        'booking_id' => $booking_id,
                        'meta_key' => 'google_calendar_event_link',
                        'meta_value' => $result['event_link']
                    ),
                    array('%d', '%s', '%s')
                );
            }
            
            error_log('Google Calendar event created successfully for booking: ' . $booking_id);
        } else {
            $error_message = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
            error_log('Failed to create Google Calendar event for booking ' . $booking_id . ': ' . $error_message);
        }
    }
    
    /**
     * Update Google Calendar event when booking is modified
     */
    public static function update_calendar_event($booking_id, $booking_data) {
        if (!class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            return;
        }
        
        $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
        
        if (!$calendar_service->is_available()) {
            return;
        }
        
        // Get existing calendar event ID
        global $wpdb;
        $meta_table = $wpdb->prefix . 'bms_booking_meta';
        
        $event_id = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$meta_table} 
             WHERE booking_id = %d AND meta_key = 'google_calendar_event_id'",
            $booking_id
        ));
        
        if (!$event_id) {
            // No existing event, create new one
            self::create_calendar_event($booking_id, $booking_data);
            return;
        }
        
        // Prepare update data
        $calendar_data = array(
            'customer_name' => $booking_data['customer_name'] ?? '',
            'customer_email' => $booking_data['customer_email'] ?? '',
            'customer_phone' => $booking_data['customer_phone'] ?? '',
            'vehicle_reg' => $booking_data['vehicle_reg'] ?? '',
            'vehicle_make' => $booking_data['vehicle_make'] ?? '',
            'vehicle_model' => $booking_data['vehicle_model'] ?? '',
            'service_name' => $booking_data['service_name'] ?? 'Service Appointment',
            'booking_date' => $booking_data['booking_date'] ?? '',
            'booking_time' => $booking_data['booking_time'] ?? '',
            'booking_reference' => $booking_data['booking_reference'] ?? '',
            'special_requirements' => $booking_data['special_requirements'] ?? '',
            'duration' => $booking_data['service_duration'] ?? 60
        );
        
        $result = $calendar_service->update_event($event_id, $calendar_data);
        
        if (is_wp_error($result)) {
            error_log('Failed to update Google Calendar event for booking ' . $booking_id . ': ' . $result->get_error_message());
        }
    }
    
    /**
     * Delete Google Calendar event when booking is cancelled
     */
    public static function delete_calendar_event($booking_id) {
        if (!class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            return;
        }
        
        $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
        
        if (!$calendar_service->is_available()) {
            return;
        }
        
        // Get existing calendar event ID
        global $wpdb;
        $meta_table = $wpdb->prefix . 'bms_booking_meta';
        
        $event_id = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$meta_table} 
             WHERE booking_id = %d AND meta_key = 'google_calendar_event_id'",
            $booking_id
        ));
        
        if ($event_id) {
            $result = $calendar_service->delete_event($event_id);
            
            if (!is_wp_error($result)) {
                // Remove meta entries
                $wpdb->delete(
                    $meta_table,
                    array('booking_id' => $booking_id, 'meta_key' => 'google_calendar_event_id'),
                    array('%d', '%s')
                );
                
                $wpdb->delete(
                    $meta_table,
                    array('booking_id' => $booking_id, 'meta_key' => 'google_calendar_event_link'),
                    array('%d', '%s')
                );
            }
        }
    }
}

// Initialize the integration
BMS_Booking_Integration::init();
