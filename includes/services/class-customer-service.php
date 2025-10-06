<?php
/**
 * Customer Service History - Advanced feature industry leaders lacks
 * Phase 4: Superior customer experience
 * 
 * @package BlueMotosSouthampton
 * @version 1.0.0
 */

namespace BlueMotosSouthampton\Services;

if (!defined('ABSPATH')) {
    exit;
}

class CustomerService {
    
    private $cache_manager;
    
    public function __construct() {
        $this->cache_manager = new CacheManager();
        
        // Add customer service hooks
        add_action('bms_booking_created', [$this, 'update_customer_profile']);
        add_action('bms_tyre_booking_created', [$this, 'update_customer_profile']);
        add_shortcode('bms_customer_history', [$this, 'customer_history_shortcode']);
    }
    
    /**
     * Get comprehensive customer service history
     */
    public function get_customer_history($email) {
        global $wpdb;
        
        // Try cache first
        $cache_key = 'customer_history_' . md5($email);
        $cached_result = $this->cache_manager->get_cached_dashboard_data($cache_key);
        
        if ($cached_result !== false) {
            return $cached_result;
        }
        
        // Get service history from view (created in database optimization)
        $service_history = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM vw_bms_customer_history 
             WHERE customer_email = %s
             ORDER BY appointment_date DESC, appointment_time DESC
             LIMIT 50",
            $email
        ));
        
        // Get customer statistics
        $stats = $this->calculate_customer_stats($service_history);
        
        // Generate intelligent recommendations
        $recommendations = $this->generate_service_recommendations($service_history, $stats);
        
        // Get loyalty status
        $loyalty = $this->calculate_loyalty_status($stats);
        
        $result = [
            'history' => $service_history,
            'stats' => $stats,
            'recommendations' => $recommendations,
            'loyalty' => $loyalty,
            'last_updated' => current_time('Y-m-d H:i:s'),
            'competitive_advantage' => 'industry leaders does not track customer history like this!'
        ];
        
        // Cache for 1 hour
        $this->cache_manager->cache_dashboard_data($result, $cache_key);
        
        return $result;
    }
    
    /**
     * Calculate comprehensive customer statistics
     */
    private function calculate_customer_stats($history) {
        if (empty($history)) {
            return [
                'total_bookings' => 0,
                'total_spent' => 0,
                'avg_booking_value' => 0,
                'last_service_date' => null,
                'most_common_service' => null,
                'loyalty_tier' => 'new',
                'avg_time_between_services' => 0,
                'preferred_day_of_week' => null,
                'seasonal_pattern' => null
            ];
        }
        
        $total_bookings = count($history);
        $total_spent = array_sum(array_column($history, 'calculated_price'));
        $avg_booking_value = $total_spent / $total_bookings;
        
        // Find most common service
        $services = array_column($history, 'service_type');
        $service_counts = array_count_values($services);
        arsort($service_counts);
        $most_common_service = key($service_counts);
        
        // Calculate average time between services
        $avg_time_between = $this->calculate_avg_time_between_services($history);
        
        // Find preferred day of week
        $days = array_map(function($booking) {
            return date('l', strtotime($booking->appointment_date));
        }, $history);
        $day_counts = array_count_values($days);
        arsort($day_counts);
        $preferred_day = key($day_counts);
        
        // Analyze seasonal patterns
        $seasonal_pattern = $this->analyze_seasonal_pattern($history);
        
        // Determine loyalty tier based on multiple factors
        $loyalty_tier = $this->determine_loyalty_tier($total_bookings, $total_spent, $avg_time_between);
        
        return [
            'total_bookings' => $total_bookings,
            'total_spent' => round($total_spent, 2),
            'avg_booking_value' => round($avg_booking_value, 2),
            'last_service_date' => $history[0]->appointment_date ?? null,
            'most_common_service' => $most_common_service,
            'loyalty_tier' => $loyalty_tier,
            'avg_time_between_services' => $avg_time_between,
            'preferred_day_of_week' => $preferred_day,
            'seasonal_pattern' => $seasonal_pattern,
            'years_as_customer' => $this->calculate_customer_tenure($history)
        ];
    }
    
    /**
     * Generate intelligent service recommendations
     */
    private function generate_service_recommendations($history, $stats) {
        $recommendations = [];
        
        if (empty($history)) {
            return [
                [
                    'type' => 'welcome',
                    'title' => 'Welcome to Blue Motors Southampton!',
                    'description' => 'Book your first service and experience our superior customer care.',
                    'service' => 'mot_test',
                    'discount' => 10,
                    'urgency' => 'low',
                    'f1_comparison' => 'industry leaders cannot provide personalized recommendations like this!'
                ]
            ];
        }
        
        $last_service = $history[0];
        $last_service_date = new \DateTime($last_service->appointment_date);
        $now = new \DateTime();
        $days_since_last = $now->diff($last_service_date)->days;
        
        // MOT reminder based on UK requirements
        if ($this->needs_mot_reminder($history)) {
            $recommendations[] = [
                'type' => 'mot_due',
                'title' => 'MOT Test Due Soon',
                'description' => 'Based on your service history, your MOT may be due. Book now to avoid any issues.',
                'service' => 'mot_test',
                'urgency' => 'high',
                'priority' => 10,
                'estimated_due_date' => $this->estimate_mot_due_date($history)
            ];
        }
        
        // Service interval recommendations based on vehicle and usage
        if ($days_since_last >= 180) { // 6 months
            $recommended_service = $this->get_recommended_service_type($history, $days_since_last);
            $recommendations[] = [
                'type' => 'service_due',
                'title' => 'Service Recommended',
                'description' => "It's been {$days_since_last} days since your last service. Time for a check-up!",
                'service' => $recommended_service,
                'urgency' => $days_since_last > 365 ? 'high' : 'medium',
                'priority' => 8
            ];
        }
        
        // Seasonal recommendations
        $seasonal_rec = $this->get_seasonal_recommendation($history);
        if ($seasonal_rec) {
            $recommendations[] = $seasonal_rec;
        }
        
        // Loyalty-based recommendations
        $loyalty_rec = $this->get_loyalty_recommendation($stats);
        if ($loyalty_rec) {
            $recommendations[] = $loyalty_rec;
        }
        
        // Sort by priority and urgency
        usort($recommendations, function($a, $b) {
            $urgency_weight = ['low' => 1, 'medium' => 2, 'high' => 3];
            $a_weight = ($urgency_weight[$a['urgency']] ?? 1) * ($a['priority'] ?? 5);
            $b_weight = ($urgency_weight[$b['urgency']] ?? 1) * ($b['priority'] ?? 5);
            return $b_weight - $a_weight;
        });
        
        return array_slice($recommendations, 0, 5); // Top 5 recommendations
    }
    
    /**
     * Check if customer needs MOT reminder based on UK requirements
     */
    private function needs_mot_reminder($history) {
        // Check if they've had an MOT in the last 11 months
        $eleven_months_ago = new \DateTime('-11 months');
        
        foreach ($history as $booking) {
            if (strpos($booking->service_type, 'mot') !== false) {
                $booking_date = new \DateTime($booking->appointment_date);
                if ($booking_date > $eleven_months_ago) {
                    return false; // Recent MOT found
                }
            }
        }
        
        return true; // No recent MOT, likely due
    }
    
    /**
     * Estimate MOT due date based on service history
     */
    private function estimate_mot_due_date($history) {
        // Find the most recent MOT
        foreach ($history as $booking) {
            if (strpos($booking->service_type, 'mot') !== false) {
                $mot_date = new \DateTime($booking->appointment_date);
                $due_date = $mot_date->add(new \DateInterval('P1Y')); // Add 1 year
                return $due_date->format('Y-m-d');
            }
        }
        
        // No previous MOT found, estimate based on vehicle age
        return date('Y-m-d', strtotime('+1 month'));
    }
    
    /**
     * Get recommended service type based on history and time elapsed
     */
    private function get_recommended_service_type($history, $days_since_last) {
        $last_service_type = $history[0]->service_type;
        
        // If last service was interim and it's been >6 months, recommend full
        if (strpos($last_service_type, 'interim') !== false && $days_since_last >= 180) {
            return 'full_service';
        }
        
        // If last service was full and it's been >1 year, recommend full again
        if (strpos($last_service_type, 'full') !== false && $days_since_last >= 365) {
            return 'full_service';
        }
        
        // Default recommendation
        return $days_since_last >= 365 ? 'full_service' : 'interim_service';
    }
    
    /**
     * Get seasonal service recommendations
     */
    private function get_seasonal_recommendation($history) {
        $month = (int)date('n');
        
        // Summer AC recommendations (June-August)
        if ($month >= 6 && $month <= 8) {
            if (!$this->has_recent_service($history, 'air_con', 12)) {
                return [
                    'type' => 'seasonal',
                    'title' => 'Summer AC Service Special',
                    'description' => 'Keep cool this summer! Air conditioning service recommended.',
                    'service' => 'air_con_regas',
                    'urgency' => 'medium',
                    'priority' => 6,
                    'seasonal_discount' => 15
                ];
            }
        }
        
        // Winter battery checks (November-February)
        if ($month >= 11 || $month <= 2) {
            if (!$this->has_recent_service($history, 'battery', 6)) {
                return [
                    'type' => 'seasonal',
                    'title' => 'Winter Battery Check',
                    'description' => 'Cold weather is hard on batteries. Free battery test recommended.',
                    'service' => 'battery_test',
                    'urgency' => 'medium',
                    'priority' => 7
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Get loyalty-based recommendations
     */
    private function get_loyalty_recommendation($stats) {
        if ($stats['loyalty_tier'] === 'gold') {
            return [
                'type' => 'loyalty',
                'title' => 'Gold Customer Exclusive',
                'description' => 'As a valued gold customer, enjoy 15% off your next premium service.',
                'service' => 'full_service',
                'urgency' => 'low',
                'priority' => 3,
                'discount' => 15,
                'exclusive' => true
            ];
        }
        
        if ($stats['loyalty_tier'] === 'silver') {
            return [
                'type' => 'loyalty',
                'title' => 'Silver Customer Benefit',
                'description' => 'Thank you for your loyalty! Enjoy 10% off your next service.',
                'discount' => 10,
                'urgency' => 'low',
                'priority' => 4
            ];
        }
        
        return null;
    }
    
    /**
     * Check if customer has recent service of specific type
     */
    private function has_recent_service($history, $service_type, $months = 12) {
        $cutoff_date = new \DateTime("-{$months} months");
        
        foreach ($history as $booking) {
            if (strpos($booking->service_type, $service_type) !== false) {
                $booking_date = new \DateTime($booking->appointment_date);
                if ($booking_date > $cutoff_date) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Calculate customer tenure in years
     */
    private function calculate_customer_tenure($history) {
        if (empty($history)) return 0;
        
        $first_service = end($history);
        $first_date = new \DateTime($first_service->appointment_date);
        $now = new \DateTime();
        
        $interval = $now->diff($first_date);
        return round($interval->days / 365.25, 1);
    }
    
    /**
     * Calculate average time between services
     */
    private function calculate_avg_time_between_services($history) {
        if (count($history) < 2) return 0;
        
        $intervals = [];
        for ($i = 0; $i < count($history) - 1; $i++) {
            $date1 = new \DateTime($history[$i]->appointment_date);
            $date2 = new \DateTime($history[$i + 1]->appointment_date);
            $intervals[] = $date1->diff($date2)->days;
        }
        
        return round(array_sum($intervals) / count($intervals));
    }
    
    /**
     * Analyze seasonal booking patterns
     */
    private function analyze_seasonal_pattern($history) {
        $seasons = ['Spring' => 0, 'Summer' => 0, 'Autumn' => 0, 'Winter' => 0];
        
        foreach ($history as $booking) {
            $month = (int)date('n', strtotime($booking->appointment_date));
            
            if ($month >= 3 && $month <= 5) $seasons['Spring']++;
            elseif ($month >= 6 && $month <= 8) $seasons['Summer']++;
            elseif ($month >= 9 && $month <= 11) $seasons['Autumn']++;
            else $seasons['Winter']++;
        }
        
        arsort($seasons);
        return key($seasons);
    }
    
    /**
     * Determine loyalty tier based on multiple factors
     */
    private function determine_loyalty_tier($total_bookings, $total_spent, $avg_time_between) {
        $score = 0;
        
        // Booking frequency score
        if ($total_bookings >= 15) $score += 3;
        elseif ($total_bookings >= 10) $score += 2;
        elseif ($total_bookings >= 5) $score += 1;
        
        // Spending score
        if ($total_spent >= 2000) $score += 3;
        elseif ($total_spent >= 1000) $score += 2;
        elseif ($total_spent >= 500) $score += 1;
        
        // Regularity score (lower time between services is better)
        if ($avg_time_between > 0 && $avg_time_between <= 180) $score += 3; // Very regular
        elseif ($avg_time_between <= 365) $score += 2; // Regular
        elseif ($avg_time_between <= 730) $score += 1; // Somewhat regular
        
        // Determine tier
        if ($score >= 7) return 'platinum';
        if ($score >= 5) return 'gold';
        if ($score >= 3) return 'silver';
        if ($score >= 1) return 'bronze';
        return 'new';
    }
    
    /**
     * Calculate loyalty status with benefits
     */
    private function calculate_loyalty_status($stats) {
        $tier = $stats['loyalty_tier'];
        
        $benefits = [
            'platinum' => [
                'discount' => 20,
                'priority_booking' => true,
                'free_pickup_delivery' => true,
                'title' => 'Platinum Customer',
                'description' => 'Our most valued customer with premium benefits'
            ],
            'gold' => [
                'discount' => 15,
                'priority_booking' => true,
                'free_pickup_delivery' => false,
                'title' => 'Gold Customer', 
                'description' => 'Loyal customer with excellent benefits'
            ],
            'silver' => [
                'discount' => 10,
                'priority_booking' => false,
                'free_pickup_delivery' => false,
                'title' => 'Silver Customer',
                'description' => 'Valued customer with good benefits'
            ],
            'bronze' => [
                'discount' => 5,
                'priority_booking' => false,
                'free_pickup_delivery' => false,
                'title' => 'Bronze Customer',
                'description' => 'Returning customer with benefits'
            ],
            'new' => [
                'discount' => 0,
                'priority_booking' => false,
                'free_pickup_delivery' => false,
                'title' => 'New Customer',
                'description' => 'Welcome to Blue Motors Southampton!'
            ]
        ];
        
        return array_merge($benefits[$tier] ?? $benefits['new'], [
            'tier' => $tier,
            'competitive_advantage' => 'industry leaders has no loyalty program like this!'
        ]);
    }
    
    /**
     * Update customer profile when booking is created
     */
    public function update_customer_profile($booking_id) {
        // Clear customer cache when new booking is made
        global $wpdb;
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT customer_email FROM wp_bms_appointments WHERE id = %d",
            $booking_id
        ));
        
        if ($booking) {
            $cache_key = 'customer_history_' . md5($booking->customer_email);
            wp_cache_delete($cache_key);
        }
    }
    
    /**
     * Shortcode for displaying customer history
     */
    public function customer_history_shortcode($atts) {
        $atts = shortcode_atts([
            'email' => '',
            'show_recommendations' => 'true',
            'show_loyalty' => 'true'
        ], $atts);
        
        if (empty($atts['email']) && is_user_logged_in()) {
            $atts['email'] = wp_get_current_user()->user_email;
        }
        
        if (empty($atts['email'])) {
            return '<p>Please log in to view your service history.</p>';
        }
        
        $history = $this->get_customer_history($atts['email']);
        
        ob_start();
        include BMS_PLUGIN_DIR . 'templates/customer-history.php';
        return ob_get_clean();
    }
}

// Initialize customer service only if class exists
if (class_exists('BlueMotosSouthampton\Services\CustomerService')) {
    new CustomerService();
}
