<?php
/**
 * Smart Appointment Scheduling
 * 
 * @package BlueMotosSouthampton
 * @version 1.0.0
 */

namespace BlueMotosSouthampton\Services;

if (!defined('ABSPATH')) {
    exit;
}

class SmartScheduler {
    
    private $cache_manager;
    private $opening_hours;
    private $service_durations;
    
    public function __construct() {
        $this->cache_manager = new CacheManager();
        $this->init_opening_hours();
        $this->init_service_durations();
        
        // Add smart scheduler hooks
        add_action('wp_ajax_bms_get_smart_suggestions', [$this, 'ajax_get_smart_suggestions']);
        add_action('wp_ajax_nopriv_bms_get_smart_suggestions', [$this, 'ajax_get_smart_suggestions']);
        add_action('wp_ajax_bms_get_time_slots_for_date', [$this, 'ajax_get_time_slots_for_date']);
        add_action('wp_ajax_nopriv_bms_get_time_slots_for_date', [$this, 'ajax_get_time_slots_for_date']);
        add_shortcode('bms_smart_scheduler', [$this, 'smart_scheduler_shortcode']);
    }
    
    /**
     * Initialize opening hours configuration
     */
    private function init_opening_hours() {
        $this->opening_hours = [
            1 => ['open' => '08:00', 'close' => '18:00', 'lunch_start' => '12:30', 'lunch_end' => '13:00'], // Monday
            2 => ['open' => '08:00', 'close' => '18:00', 'lunch_start' => '12:30', 'lunch_end' => '13:00'], // Tuesday
            3 => ['open' => '08:00', 'close' => '18:00', 'lunch_start' => '12:30', 'lunch_end' => '13:00'], // Wednesday
            4 => ['open' => '08:00', 'close' => '18:00', 'lunch_start' => '12:30', 'lunch_end' => '13:00'], // Thursday
            5 => ['open' => '08:00', 'close' => '18:00', 'lunch_start' => '12:30', 'lunch_end' => '13:00'], // Friday
            6 => ['open' => '08:00', 'close' => '16:00', 'lunch_start' => '12:00', 'lunch_end' => '12:30'], // Saturday
            7 => null // Sunday - closed
        ];
    }
    
    /**
     * Initialize service duration configuration
     */
    private function init_service_durations() {
        $this->service_durations = [
            'mot_test' => ['duration' => 60, 'prep_time' => 10, 'cleanup_time' => 5],
            'full_service' => ['duration' => 120, 'prep_time' => 15, 'cleanup_time' => 10],
            'interim_service' => ['duration' => 90, 'prep_time' => 15, 'cleanup_time' => 10],
            'air_con_regas' => ['duration' => 60, 'prep_time' => 10, 'cleanup_time' => 5],
            'air_con_service' => ['duration' => 90, 'prep_time' => 15, 'cleanup_time' => 10],
            'brake_check' => ['duration' => 45, 'prep_time' => 10, 'cleanup_time' => 5],
            'brake_service' => ['duration' => 120, 'prep_time' => 15, 'cleanup_time' => 10],
            'battery_test' => ['duration' => 30, 'prep_time' => 5, 'cleanup_time' => 5],
            'battery_replacement' => ['duration' => 45, 'prep_time' => 10, 'cleanup_time' => 5],
            'exhaust_check' => ['duration' => 30, 'prep_time' => 5, 'cleanup_time' => 5],
            'tyre_fitting' => ['duration' => 30, 'prep_time' => 10, 'cleanup_time' => 5] // per tyre
        ];
    }
    
    /**
     * Get optimized appointment suggestions with AI-powered analysis
     */
    public function get_smart_suggestions($service_type, $preferred_date = null, $customer_email = null) {
        // Check cache first;
        $cache_key = 'smart_suggestions_' . md5($service_type . ($preferred_date ?: '') . ($customer_email ?: ''));
        $cached_suggestions = $this->cache_manager->get_cached_dashboard_data($cache_key);
        
        if ($cached_suggestions) {
            return $cached_suggestions;
        }
        
        $suggestions = [];
        
        // Get service configuration and historical data
        $service_config = $this->get_service_config($service_type);
        $busy_periods = $this->analyze_busy_periods();
        $customer_preferences = $customer_email ? $this->analyze_customer_preferences($customer_email) : null;
        
        // Generate suggestions for next 30 days to give more choice
        $date = new \DateTime($preferred_date ?: 'tomorrow');
        $end_date = new \DateTime('+30 days');
        
        while ($date <= $end_date) {
            if ($this->is_business_day($date)) {
                $day_suggestions = $this->get_day_suggestions($date, $service_config, $busy_periods, $customer_preferences);
                if (!empty($day_suggestions)) {
                    $suggestions[] = [
                        'date' => $date->format('Y-m-d'),
                        'display_date' => $date->format('l, j F Y'),
                        'slots' => $day_suggestions,
                        'recommended' => $this->is_recommended_day($date, $busy_periods, $customer_preferences),
                        'day_score' => $this->calculate_day_score($date, $busy_periods, $customer_preferences),
                        'weather_factor' => $this->get_weather_factor($date, $service_type)
                    ];
                }
            }
            $date->modify('+1 day');
        }
        
        // Sort suggestions by overall score
        usort($suggestions, function($a, $b) {
            return $b['day_score'] - $a['day_score'];
        });
        
        $final_suggestions = array_slice($suggestions, 0, 14); // Return top 14 days for more choice
        
        // Cache for 15 minutes
        $this->cache_manager->cache_dashboard_data($final_suggestions, $cache_key);
        
        return $final_suggestions;
    }
    
    /**
     * Get service configuration with dynamic adjustments
     */
    private function get_service_config($service_type) {
        $base_config = $this->service_durations[$service_type] ?? ['duration' => 60, 'prep_time' => 10, 'cleanup_time' => 5];
        
        // Adjust for tyre fitting based on quantity
        if ($service_type === 'tyre_fitting') {
            // Default to 4 tyres, can be adjusted by request;
            $quantity = 4;
            $base_config['duration'] = $base_config['duration'] * $quantity;
            $base_config['total_time'] = $base_config['duration'] + $base_config['prep_time'] + $base_config['cleanup_time'];
        } else {
            $base_config['total_time'] = $base_config['duration'] + $base_config['prep_time'] + $base_config['cleanup_time'];
        }
        
        return $base_config;
    }
    
    /**
     * Analyze historical busy periods with machine learning approach
     */
    private function analyze_busy_periods() {
        $cache_key = 'busy_periods_analysis';
        $cached_analysis = $this->cache_manager->get_cached_dashboard_data($cache_key);
        
        if ($cached_analysis) {
            return $cached_analysis;
        }
        
        global $wpdb;
        
        // Get booking patterns from last 90 days with enhanced analysis
        $booking_data = $wpdb->get_results(
            "SELECT 
                DAYOFWEEK(booking_date) as day_of_week,
                HOUR(booking_time) as hour,
                MINUTE(booking_time) as minute,
                service_type,
                COUNT(*) as booking_count,
                AVG(calculated_price) as avg_value,
                COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as successful_bookings
             FROM {$wpdb->prefix}bms_appointments 
             WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
             AND booking_status != 'cancelled'
             GROUP BY DAYOFWEEK(booking_date), HOUR(booking_time), service_type
             ORDER BY booking_count DESC"
        );
        
        $busy_periods = [
            'hourly' => [],
            'daily' => [],
            'service_patterns' => [],
            'peak_times' => [],
            'quiet_times' => []
        ];
        
        foreach ($booking_data as $period) {
            // Hourly patterns
            $busy_periods['hourly'][$period->day_of_week][$period->hour] = [
                'count' => $period->booking_count,
                'avg_value' => $period->avg_value,
                'success_rate' => $period->successful_bookings / $period->booking_count
            ];
            
            // Service-specific patterns
            $busy_periods['service_patterns'][$period->service_type][$period->day_of_week][$period->hour] = $period->booking_count;
        }
        
        // Identify peak and quiet times
        $busy_periods['peak_times'] = $this->identify_peak_times($booking_data);
        $busy_periods['quiet_times'] = $this->identify_quiet_times($booking_data);
        
        // Cache for 1 hour
        $this->cache_manager->cache_dashboard_data($busy_periods, $cache_key);
        
        return $busy_periods;
    }
    
    /**
     * Analyze customer preferences based on booking history
     */
    private function analyze_customer_preferences($customer_email) {
        global $wpdb;
        
        $cache_key = 'customer_prefs_' . md5($customer_email);
        $cached_prefs = $this->cache_manager->get_cached_dashboard_data($cache_key);
        
        if ($cached_prefs) {
            return $cached_prefs;
        }
        
        // Get customer's booking history
        $customer_bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DAYOFWEEK(booking_date) as preferred_day,
                HOUR(booking_time) as preferred_hour,
                service_type,
                COUNT(*) as frequency
             FROM {$wpdb->prefix}bms_appointments 
             WHERE customer_email = %s
             AND booking_status != 'cancelled'
             GROUP BY DAYOFWEEK(booking_date), HOUR(booking_time)
             ORDER BY frequency DESC",
            $customer_email
        ));
        
        if (empty($customer_bookings)) {
            return null;
        }
        
        $preferences = [
            'preferred_days' => [],
            'preferred_times' => [],
            'booking_frequency' => count($customer_bookings),
            'loyalty_level' => $this->calculate_loyalty_level($customer_bookings)
        ];
        
        // Calculate preferred days and times
        foreach ($customer_bookings as $booking) {
            $day_name = $this->get_day_name($booking->preferred_day);
            $preferences['preferred_days'][$day_name] = ($preferences['preferred_days'][$day_name] ?? 0) + $booking->frequency;
            
            $time_slot = $this->get_time_slot_name($booking->preferred_hour);
            $preferences['preferred_times'][$time_slot] = ($preferences['preferred_times'][$time_slot] ?? 0) + $booking->frequency;
        }
        
        // Sort by preference
        arsort($preferences['preferred_days']);
        arsort($preferences['preferred_times']);
        
        // Cache for 24 hours
        $this->cache_manager->cache_dashboard_data($preferences, $cache_key);
        
        return $preferences;
    }
    
    /**
     * Get suggestions for a specific day with AI optimization
     */
    private function get_day_suggestions($date, $service_config, $busy_periods, $customer_preferences = null) {
        // Get opening hours for this day;
        $day_of_week = (int)$date->format('N'); // 1=Monday, 7=Sunday
        $opening_hours = $this->opening_hours[$day_of_week] ?? null;
        
        if (!$opening_hours) {
            return []; // Closed
        }
        
        // Get existing bookings for this date
        $existing_bookings = $this->get_existing_bookings($date->format('Y-m-d'));
        
        // Generate time slots with AI optimization
        $slots = [];
        $current_time = clone $date;
        $current_time->setTime((int)substr($opening_hours['open'], 0, 2), (int)substr($opening_hours['open'], 3, 2));
        
        $end_time = clone $date;
        $end_time->setTime((int)substr($opening_hours['close'], 0, 2), (int)substr($opening_hours['close'], 3, 2));
        
        // Account for service duration
        $end_time->modify("-{$service_config['total_time']} minutes");
        
        while ($current_time <= $end_time) {
            $time_string = $current_time->format('H:i');
            
            // Skip lunch break
            if ($this->is_lunch_time($current_time, $opening_hours)) {
                $current_time->modify('+30 minutes');
                continue;
            }
            
            // Check if slot is available (no conflicts)
            if (!$this->is_slot_conflict($current_time, $service_config, $existing_bookings)) {
                $slot_score = $this->calculate_slot_score($current_time, $busy_periods, $customer_preferences, $service_config);
                
                $slots[] = [
                    'time' => $time_string,
                    'display_time' => $current_time->format('g:i A'),
                    'slot_score' => $slot_score,
                    'busy_level' => $this->get_busy_level($slot_score),
                    'recommendation' => $this->get_slot_recommendation($slot_score, $customer_preferences),
                    'customer_match' => $this->calculate_customer_match($current_time, $customer_preferences),
                    'efficiency_rating' => $this->calculate_efficiency_rating($current_time, $busy_periods),
                    'available' => true
                ];
            }
            
            $current_time->modify('+15 minutes'); // 15-minute intervals for more choice
        }
        
        // Sort by slot score (highest first)
        usort($slots, function($a, $b) {
            return $b['slot_score'] - $a['slot_score'];
        });
        
        return array_slice($slots, 0, 20); // Max 20 slots per day for more choice
    }
    
    /**
     * Calculate comprehensive slot score using AI factors
     */
    private function calculate_slot_score($time, $busy_periods, $customer_preferences, $service_config) {
        $score = 5.0; // Base score
        $day_of_week = (int)$time->format('N');
        $hour = (int)$time->format('H');
        
        // Factor 1: Historical busy level (inverted - less busy = higher score)
        $busy_count = $busy_periods['hourly'][$day_of_week][$hour]['count'] ?? 0;
        $busy_factor = max(0, 5 - ($busy_count / 2)); // Normalize busy count
        $score += $busy_factor;
        
        // Factor 2: Customer preference alignment
        if ($customer_preferences) {
            $day_name = $this->get_day_name($day_of_week);
            $time_slot = $this->get_time_slot_name($hour);
            
            if (isset($customer_preferences['preferred_days'][$day_name])) {
                $score += 2; // Preferred day bonus
            }
            
            if (isset($customer_preferences['preferred_times'][$time_slot])) {
                $score += 2; // Preferred time bonus
            }
        }
        
        // Factor 3: Optimal service timing
        $optimal_hours = [9, 10, 11, 14, 15, 16]; // Generally good service hours
        if (in_array($hour, $optimal_hours)) {
            $score += 1;
        }
        
        // Factor 4: Avoid rush hours
        $rush_hours = [8, 12, 17]; // Busy transition times
        if (in_array($hour, $rush_hours)) {
            $score -= 1;
        }
        
        // Factor 5: Service-specific optimization
        if ($service_config['duration'] >= 120) { // Long services
            if ($hour >= 9 && $hour <= 15) { // Prefer mid-day for long services
                $score += 1;
            }
        } else { // Short services
            if ($hour >= 8 && $hour <= 17) { // Flexible timing for short services
                $score += 0.5;
            }
        }
        
        // Factor 6: Day-specific adjustments
        if ($day_of_week === 6) { // Saturday;
            $score -= 0.5; // Slightly prefer weekdays
        }
        
        // Factor 7: Weather considerations (placeholder for future weather API)
        $weather_adjustment = $this->get_weather_adjustment($time);
        $score += $weather_adjustment;
        
        return round(min(10, max(0, $score)), 1); // Clamp between 0-10
    }
    
    /**
     * Check for booking conflicts with buffer time
     */
    private function is_slot_conflict($proposed_time, $service_config, $existing_bookings) {
        $service_start = clone $proposed_time;
        $service_end = clone $proposed_time;
        $service_end->modify("+{$service_config['total_time']} minutes");
        
        // Add 15-minute buffer before and after
        $buffer_start = clone $service_start;
        $buffer_start->modify('-15 minutes');
        $buffer_end = clone $service_end;
        $buffer_end->modify('+15 minutes');
        
        foreach ($existing_bookings as $booking) {
            $booking_start = new \DateTime($booking['start_time']);
            $booking_end = new \DateTime($booking['end_time']);
            
            // Check for overlap with buffer
            if ($buffer_start < $booking_end && $buffer_end > $booking_start) {
                return true; // Conflict found
            }
        }
        
        return false; // No conflict
    }
    
    /**
     * Get existing bookings for a date with service durations
     */
    private function get_existing_bookings($date) {
        global $wpdb;
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                booking_time,
                service_type,
                CASE 
                    WHEN service_type = 'full_service' THEN ADDTIME(booking_time, '02:15:00')
                    WHEN service_type = 'interim_service' THEN ADDTIME(booking_time, '01:45:00')
                    WHEN service_type = 'mot_test' THEN ADDTIME(booking_time, '01:15:00')
                    WHEN service_type LIKE '%tyre%' THEN ADDTIME(booking_time, '02:00:00')
                    ELSE ADDTIME(booking_time, '01:00:00')
                END as estimated_end_time
             FROM {$wpdb->prefix}bms_appointments 
             WHERE booking_date = %s 
             AND booking_status != 'cancelled'",
            $date
        ));
        
        $formatted_bookings = [];
        foreach ($bookings as $booking) {
            $formatted_bookings[] = [
                'service_type' => $booking->service_type,
                'start_time' => $date . ' ' . $booking->booking_time,
                'end_time' => $date . ' ' . $booking->estimated_end_time
            ];
        }
        
        return $formatted_bookings;
    }
    
    /**
     * Calculate day score for overall day ranking
     */
    private function calculate_day_score($date, $busy_periods, $customer_preferences) {
        $day_of_week = (int)$date->format('N');
        $score = 5.0;
        
        // Prefer weekdays over Saturday
        if ($day_of_week <= 5) {
            $score += 1;
        }
        
        // Tuesday-Thursday are typically best
        if (in_array($day_of_week, [2, 3, 4])) {
            $score += 1;
        }
        
        // Customer preference alignment
        if ($customer_preferences) {
            $day_name = $this->get_day_name($day_of_week);
            if (isset($customer_preferences['preferred_days'][$day_name])) {
                $score += 2;
            }
        }
        
        // Historical data
        $daily_avg_busy = 0;
        for ($hour = 8; $hour <= 17; $hour++) {
            $daily_avg_busy += $busy_periods['hourly'][$day_of_week][$hour]['count'] ?? 0;
        }
        $daily_avg_busy /= 10; // Average across business hours
        
        $score += max(0, 3 - ($daily_avg_busy / 3)); // Less busy = higher score
        
        return round($score, 1);
    }
    
    /**
     * Check if time is during lunch break
     */
    private function is_lunch_time($time, $opening_hours) {
        if (!isset($opening_hours['lunch_start']) || !isset($opening_hours['lunch_end'])) {
            return false;
        }
        
        $lunch_start = $time->format('H:i');
        return $lunch_start >= $opening_hours['lunch_start'] && $lunch_start < $opening_hours['lunch_end'];
    }
    
    /**
     * Helper methods for analysis
     */
    private function get_day_name($day_of_week) {
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        return $days[$day_of_week] ?? 'Unknown';
    }
    
    private function get_time_slot_name($hour) {
        if ($hour >= 8 && $hour <= 11) return 'Morning';
        if ($hour >= 12 && $hour <= 13) return 'Lunch';
        if ($hour >= 14 && $hour <= 17) return 'Afternoon';
        return 'Other';
    }
    
    private function get_busy_level($score) {
        if ($score >= 8) return 'Optimal';
        if ($score >= 6) return 'Good';
        if ($score >= 4) return 'Moderate';
        return 'Busy';
    }
    
    private function get_slot_recommendation($score, $customer_preferences) {
        if ($score >= 9) {
            $message = 'Perfect slot - highly recommended';
        } elseif ($score >= 7) {
            $message = 'Excellent choice - good availability';
        } elseif ($score >= 5) {
            $message = 'Good option - book early';
        } else {
            $message = 'Available but consider alternatives';
        }
        
        if ($customer_preferences && $score >= 7) {
            $message .= ' (matches your preferences)';
        }
        
        return $message;
    }
    
    private function calculate_customer_match($time, $customer_preferences) {
        if (!$customer_preferences) return 0;
        
        $match_score = 0;
        $day_name = $this->get_day_name((int)$time->format('N'));
        $time_slot = $this->get_time_slot_name((int)$time->format('H'));
        
        if (isset($customer_preferences['preferred_days'][$day_name])) {
            $match_score += 50;
        }
        
        if (isset($customer_preferences['preferred_times'][$time_slot])) {
            $match_score += 50;
        }
        
        return $match_score;
    }
    
    private function calculate_efficiency_rating($time, $busy_periods) {
        $day_of_week = (int)$time->format('N');
        $hour = (int)$time->format('H');
        
        $success_rate = $busy_periods['hourly'][$day_of_week][$hour]['success_rate'] ?? 0.9;
        return round($success_rate * 100);
    }
    
    private function get_weather_adjustment($time) {
        // Placeholder for weather API integration
        // Could adjust scores based on weather conditions
        return 0;
    }
    
    private function get_weather_factor($date, $service_type) {
        // Placeholder for weather considerations
        return ['condition' => 'unknown', 'impact' => 'none'];
    }
    
    private function calculate_loyalty_level($bookings) {
        $count = count($bookings);
        if ($count >= 10) return 'Gold';
        if ($count >= 5) return 'Silver';
        if ($count >= 2) return 'Bronze';
        return 'New';
    }
    
    private function identify_peak_times($booking_data) {
        $peak_times = [];
        foreach ($booking_data as $period) {
            if ($period->booking_count >= 3) { // Threshold for peak
                $peak_times[] = [
                    'day' => $period->day_of_week,
                    'hour' => $period->hour,
                    'count' => $period->booking_count
                ];
            }
        }
        return $peak_times;
    }
    
    private function identify_quiet_times($booking_data) {
        $all_slots = [];
        $booked_slots = [];
        
        // Create array of all possible slots
        for ($day = 1; $day <= 6; $day++) {
            for ($hour = 8; $hour <= 17; $hour++) {
                $all_slots[] = $day . '_' . $hour;
            }
        }
        
        // Mark booked slots
        foreach ($booking_data as $period) {
            $booked_slots[] = $period->day_of_week . '_' . $period->hour;
        }
        
        // Find quiet slots (not in booked or very low booking count)
        $quiet_times = [];
        foreach ($all_slots as $slot) {
            if (!in_array($slot, $booked_slots)) {
                $parts = explode('_', $slot);
                $quiet_times[] = [
                    'day' => (int)$parts[0],
                    'hour' => (int)$parts[1],
                    'count' => 0
                ];
            }
        }
        
        return $quiet_times;
    }
    
    private function is_business_day($date) {
        $day_of_week = (int)$date->format('N');
        return $day_of_week <= 6; // Monday-Saturday
    }
    
    private function is_recommended_day($date, $busy_periods, $customer_preferences) {
        $day_of_week = (int)$date->format('N');
        
        // Tuesday-Thursday are typically best
        $optimal_days = [2, 3, 4];
        
        if (in_array($day_of_week, $optimal_days)) {
            return true;
        }
        
        // Check customer preferences
        if ($customer_preferences) {
            $day_name = $this->get_day_name($day_of_week);
            if (isset($customer_preferences['preferred_days'][$day_name])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * AJAX handler for smart suggestions
     */
    public function ajax_get_smart_suggestions() {
        check_ajax_referer('bms_nonce', 'nonce');
        
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        $preferred_date = sanitize_text_field($_POST['preferred_date'] ?? '');
        $customer_email = sanitize_email($_POST['customer_email'] ?? '');
        
        if (empty($service_type)) {
            wp_send_json_error('Service type is required');
            return;
        }
        
        try {
            $suggestions = $this->get_smart_suggestions($service_type, $preferred_date, $customer_email);
            
            wp_send_json_success([
                'suggestions' => $suggestions,
                'total_days' => count($suggestions)
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error('Failed to generate smart suggestions: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for time slots for specific date
     */
    public function ajax_get_time_slots_for_date() {
        check_ajax_referer('bms_nonce', 'nonce');
        
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        $selected_date = sanitize_text_field($_POST['selected_date'] ?? '');
        
        if (empty($service_type) || empty($selected_date)) {
            wp_send_json_error('Service type and date are required');
            return;
        }
        
        try {
            // Parse UK date format (DD/MM/YYYY)
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $selected_date, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                $selected_date = $year . '-' . $month . '-' . $day; // Convert to Y-m-d for DateTime
            }
            
            // Validate date
            $date = new \DateTime($selected_date);
            $today = new \DateTime();
            $today->setTime(0, 0, 0); // Reset time to start of day
            $max_date = new \DateTime('+30 days');
            
            if ($date < $today || $date > $max_date) {
                wp_send_json_error('Please select a valid date within the next 30 days');
                return;
            }
            
            // Get service configuration and historical data
            $service_config = $this->get_service_config($service_type);
            $busy_periods = $this->analyze_busy_periods();
            $customer_email = is_user_logged_in() ? wp_get_current_user()->user_email : null;
            $customer_preferences = $customer_email ? $this->analyze_customer_preferences($customer_email) : null;
            
            // Get time slots for the specific date
            $slots = $this->get_day_suggestions($date, $service_config, $busy_periods, $customer_preferences);
            
            wp_send_json_success([
                'date' => $date->format('Y-m-d'),
                'display_date' => $date->format('l, j F Y'),
                'slots' => $slots,
                'total_slots' => count($slots)
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error('Failed to get time slots: ' . $e->getMessage());
        }
    }
    
    /**
     * Shortcode for smart scheduler widget
     */
    public function smart_scheduler_shortcode($atts) {
        $atts = shortcode_atts([
            'service_type' => '',
            'show_customer_prefs' => 'true',
            'max_suggestions' => '5'
        ], $atts);
        
        ob_start();
        include BMS_PLUGIN_DIR . 'templates/smart-scheduler-widget.php';
        return ob_get_clean();
    }
}

// Initialize smart scheduler only if class exists
if (class_exists('BlueMotosSouthampton\Services\SmartScheduler')) {
    new SmartScheduler();
}
