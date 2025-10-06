<?php
/**
 * Blue Motors Southampton - Tyre Service Backend
 * Phase 2: Tyre Services Implementation - Our industry leaders Professional Advantage
 * 
 * This class provides the complete tyre ordering system that industry leaders lacks.
 * Some providers require phone calls for tyres - we offer complete online ordering.
 * 
 * @package BlueMotosSouthampton
 * @since 2.0.0
 */

namespace BlueMotosSouthampton\Services;

class TyreService {
    
    /**
     * Search tyres by vehicle registration
     * This is our service excellence over F1 - automatic tyre recommendations
     */
    public function search_by_registration($registration) {
        try {
            // Use existing vehicle lookup to get tyre size
            if (!class_exists('BlueMotosSouthampton\Services\VehicleLookupCombined')) {
                return new \WP_Error('class_missing', 'VehicleLookupCombined class not found');
            }
            
            $vehicle_lookup = new \BlueMotosSouthampton\Services\VehicleLookupCombined();
            
            // Check if the method exists before calling
            if (!method_exists($vehicle_lookup, 'lookup')) {
                return new \WP_Error('method_missing', 'lookup method not found in VehicleLookupCombined class');
            }
            
            $vehicle_data = $vehicle_lookup->lookup($registration);
            
            if (is_wp_error($vehicle_data)) {
                return $vehicle_data;
            }
            
            // Get recommended tyre sizes for this vehicle
            $tyre_sizes = $this->get_vehicle_tyre_sizes($vehicle_data);
            
            // Search for available tyres in each size
            $available_tyres = [];
            foreach ($tyre_sizes as $size) {
                $tyres = $this->search_by_size_string($size);
                if (!empty($tyres) && !is_wp_error($tyres)) {
                    $available_tyres[$size] = $tyres;
                }
            }
            
            return [
                'vehicle' => $vehicle_data,
                'recommended_sizes' => $tyre_sizes,
                'available_tyres' => $available_tyres,
                'total_options' => array_sum(array_map('count', $available_tyres))
            ];
            
        } catch (Exception $e) {
            error_log('TyreService search_by_registration error: ' . $e->getMessage());
            return new \WP_Error('search_failed', 'Search failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Search tyres by size string (e.g., "205/55R16")
     */
    public function search_by_size_string($size_string) {
        global $wpdb;
        
        // Parse size string
        $size_parts = $this->parse_tyre_size($size_string);
        if (!$size_parts) {
            return new \WP_Error('invalid_size', 'Invalid tyre size format');
        }
        
        $table_name = $wpdb->prefix . 'bms_tyres';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} 
             WHERE width = %d AND profile = %d AND rim = %d 
             AND is_active = 1 AND stock_quantity > 0
             ORDER BY brand_tier ASC, price ASC",
            $size_parts['width'],
            $size_parts['profile'],
            $size_parts['rim']
        ));
        
        return $this->format_tyre_results($results);
    }
    
    /**
     * Search by brand with optional size filter
     */
    public function search_by_brand($brand, $size_string = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyres';
        $where_conditions = ['brand = %s', 'is_active = 1', 'stock_quantity > 0'];
        $where_values = [$brand];
        
        if ($size_string) {
            $size_parts = $this->parse_tyre_size($size_string);
            if ($size_parts) {
                $where_conditions[] = 'width = %d AND profile = %d AND rim = %d';
                $where_values[] = $size_parts['width'];
                $where_values[] = $size_parts['profile'];
                $where_values[] = $size_parts['rim'];
            }
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY price ASC",
            ...$where_values
        ));
        
        return $this->format_tyre_results($results);
    }
    
    /**
     * Get available brands
     */
    public function get_available_brands() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyres';
        
        $brands = $wpdb->get_results(
            "SELECT brand, brand_tier, COUNT(*) as tyre_count, 
             MIN(price) as min_price, MAX(price) as max_price
             FROM {$table_name} 
             WHERE is_active = 1 AND stock_quantity > 0 
             GROUP BY brand, brand_tier
             ORDER BY brand_tier ASC, brand ASC"
        );
        
        return $brands;
    }
    
    /**
     * Calculate total price including fitting and VAT
     */
    public function calculate_total_price($tyre_id, $quantity = 1) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyres';
        
        $tyre = $wpdb->get_row($wpdb->prepare(
            "SELECT price, fitting_price FROM {$table_name} WHERE id = %d",
            $tyre_id
        ));
        
        if (!$tyre) {
            return new \WP_Error('tyre_not_found', 'Tyre not found');
        }
        
        $tyre_cost = $tyre->price * $quantity;
        $fitting_cost = $tyre->fitting_price * $quantity;
        $subtotal = $tyre_cost + $fitting_cost;
        $vat = $subtotal * 0.2; // 20% VAT
        $total = $subtotal + $vat;
        
        return [
            'tyre_cost' => $tyre_cost,
            'fitting_cost' => $fitting_cost,
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $total,
            'quantity' => $quantity,
            'per_tyre_total' => ($tyre->price + $tyre->fitting_price) * 1.2 // Including VAT
        ];
    }
    
    /**
     * Create tyre booking
     */
    public function create_booking($booking_data) {
        global $wpdb;
        
        // Validate booking data
        $validation = $this->validate_booking_data($booking_data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Generate booking reference
        $booking_reference = 'TYRE-' . strtoupper(substr(md5(uniqid()), 0, 6));
        
        $table_name = $wpdb->prefix . 'bms_tyre_bookings';
        
        $result = $wpdb->insert(
            $table_name,
            [
                'booking_reference' => $booking_reference,
                'customer_name' => $booking_data['customer_name'],
                'customer_email' => $booking_data['customer_email'],
                'customer_phone' => $booking_data['customer_phone'],
                'vehicle_reg' => $booking_data['vehicle_reg'],
                'vehicle_make' => $booking_data['vehicle_make'] ?? '',
                'vehicle_model' => $booking_data['vehicle_model'] ?? '',
                'vehicle_year' => $booking_data['vehicle_year'] ?? null,
                'tyre_id' => $booking_data['tyre_id'],
                'quantity' => $booking_data['quantity'],
                'fitting_date' => $booking_data['fitting_date'],
                'fitting_time' => $booking_data['fitting_time'],
                'tyre_price' => $booking_data['tyre_price'],
                'fitting_price' => $booking_data['fitting_price'],
                'subtotal' => $booking_data['subtotal'],
                'vat_amount' => $booking_data['vat_amount'],
                'total_price' => $booking_data['total_price'],
                'special_requirements' => $booking_data['special_requirements'] ?? ''
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%f', '%f', '%f', '%f', '%f', '%s']
        );
        
        if ($result === false) {
            return new \WP_Error('booking_failed', 'Failed to create tyre booking: ' . $wpdb->last_error);
        }
        
        $booking_id = $wpdb->insert_id;
        
        // Create Google Calendar event for tyre booking
        $this->create_calendar_event($booking_id, $booking_data, $booking_reference);
        
        return [
            'booking_id' => $booking_id,
            'booking_reference' => $booking_reference
        ];
    }    
    /**
     * Get tyre details by ID
     */
    public function get_tyre_details($tyre_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyres';
        
        $tyre = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d AND is_active = 1",
            $tyre_id
        ));
        
        if (!$tyre) {
            return new \WP_Error('tyre_not_found', 'Tyre not found');
        }
        
        return $this->format_single_tyre_result($tyre);
    }
    
    /**
     * Get available time slots for tyre fitting
     */
    public function get_available_fitting_slots($date, $quantity = 1) {
        // Calculate duration based on quantity (30 minutes per tyre);
        $duration = $quantity * 30;
        
        // Use existing location class for opening hours
        if (class_exists('Blue_Motors_Location')) {
            $location = new \Blue_Motors_Location();
            return $location->get_available_slots($date, $duration);
        }
        
        // Fallback to basic time slots
        return $this->get_basic_time_slots($date, $duration);
    }
    
    /**
     * Update tyre stock quantity
     */
    public function update_stock($tyre_id, $quantity_change) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyres';
        
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE {$table_name} 
             SET stock_quantity = GREATEST(0, stock_quantity + %d),
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = %d",
            $quantity_change,
            $tyre_id
        ));
        
        return $result !== false;
    }
    
    /**
     * Get customer's tyre booking history
     */
    public function get_customer_booking_history($customer_email) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyre_bookings';
        $tyres_table = $wpdb->prefix . 'bms_tyres';
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT tb.*, t.brand, t.model, t.size 
             FROM {$table_name} tb
             LEFT JOIN {$tyres_table} t ON tb.tyre_id = t.id
             WHERE tb.customer_email = %s
             ORDER BY tb.fitting_date DESC, tb.fitting_time DESC",
            $customer_email
        ));
        
        return $bookings;
    }
    
    /**
     * Parse tyre size string into components
     */
    private function parse_tyre_size($size_string) {
        // Handle formats like "205/55R16" or "205/55/16"
        $pattern = '/^(\d{3})\/(\d{2})R?(\d{2})$/';
        
        if (preg_match($pattern, $size_string, $matches)) {
            return [
                'width' => intval($matches[1]),
                'profile' => intval($matches[2]),
                'rim' => intval($matches[3])
            ];
        }
        
        return false;
    }
    
    /**
     * Get recommended tyre sizes for vehicle
     */
    private function get_vehicle_tyre_sizes($vehicle_data) {
        global $wpdb;
        
        // Check if we have required vehicle data
        $make = $vehicle_data['make'] ?? ($vehicle_data['registrationNumber'] ?? 'UNKNOWN');
        $model = $vehicle_data['model'] ?? null; // This may not exist in DVLA data
        $year = $vehicle_data['yearOfManufacture'] ?? date('Y');
        
        // First try to get from vehicle_tyres table if it exists
        $table_name = $wpdb->prefix . 'bms_vehicle_tyres';
        
        // Check if the table exists before querying
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
        
        if ($table_exists && !empty($make)) {
            // If we don't have model data, search by make only
            if (!empty($model)) {
                $sizes = $wpdb->get_col($wpdb->prepare(
                    "SELECT DISTINCT front_tyre_size FROM {$table_name}
                     WHERE vehicle_make = %s AND vehicle_model = %s
                     AND (year_from <= %d AND (year_to IS NULL OR year_to >= %d))
                     AND is_active = 1",
                    strtoupper($make),
                    strtoupper($model),
                    $year,
                    $year
                ));
            } else {
                // Search by make only when model is not available
                $sizes = $wpdb->get_col($wpdb->prepare(
                    "SELECT DISTINCT front_tyre_size FROM {$table_name}
                     WHERE vehicle_make = %s
                     AND (year_from <= %d AND (year_to IS NULL OR year_to >= %d))
                     AND is_active = 1
                     LIMIT 5",
                    strtoupper($make),
                    $year,
                    $year
                ));
            }
            
            if (!empty($sizes)) {
                return array_unique($sizes);
            }
        }
        
        // Fallback based on engine size and make
        $engine_size = $vehicle_data['engineCapacity'] ?? 1600;
        $make_upper = strtoupper($make);
        
        // Make-specific adjustments
        $size_adjustments = [];
        if (strpos($make_upper, 'BMW') !== false || strpos($make_upper, 'AUDI') !== false || strpos($make_upper, 'MERCEDES') !== false) {
            $size_adjustments = ['225/50R17', '245/45R18', '255/40R19'];
        } elseif (strpos($make_upper, 'HYUNDAI') !== false || strpos($make_upper, 'KIA') !== false) {
            $size_adjustments = ['195/65R15', '205/55R16', '215/55R17'];
        } elseif (strpos($make_upper, 'FORD') !== false || strpos($make_upper, 'VAUXHALL') !== false) {
            $size_adjustments = ['195/65R15', '205/55R16', '215/60R16'];
        }
        
        // If we have make-specific sizes, return them
        if (!empty($size_adjustments)) {
            return $size_adjustments;
        }
        
        // Default fallback based on engine size
        if ($engine_size <= 1200) {
            return ['175/65R14', '185/60R15'];
        } elseif ($engine_size <= 1600) {
            return ['195/65R15', '205/55R16'];
        } elseif ($engine_size <= 2000) {
            return ['205/55R16', '215/60R16'];
        } else {
            return ['225/50R17', '235/45R18'];
        }
    }
    
    /**
     * Format tyre results for display
     */
    private function format_tyre_results($results) {
        $formatted = [];
        
        foreach ($results as $tyre) {
            $formatted[] = $this->format_single_tyre_result($tyre);
        }
        
        return $formatted;
    }
    
    /**
     * Format single tyre result
     */
    private function format_single_tyre_result($tyre) {
        return [
            'id' => intval($tyre->id),
            'brand' => $tyre->brand,
            'model' => $tyre->model,
            'size' => $tyre->size,
            'width' => intval($tyre->width),
            'profile' => intval($tyre->profile),
            'rim' => intval($tyre->rim),
            'speed_rating' => $tyre->speed_rating,
            'load_index' => intval($tyre->load_index),
            'price' => floatval($tyre->price),
            'fitting_price' => floatval($tyre->fitting_price),
            'total_per_tyre' => (floatval($tyre->price) + floatval($tyre->fitting_price)) * 1.2, // Inc VAT
            'stock_quantity' => intval($tyre->stock_quantity),
            'brand_tier' => $tyre->brand_tier,
            'fuel_efficiency' => $tyre->fuel_efficiency,
            'wet_grip' => $tyre->wet_grip,
            'noise_rating' => $tyre->noise_rating ? intval($tyre->noise_rating) : null,
            'season' => $tyre->season,
            'usage_type' => $tyre->usage_type ?? 'car'
        ];
    }
    
    /**
     * Validate booking data
     */
    private function validate_booking_data($data) {
        $required_fields = [
            'customer_name', 'customer_email', 'customer_phone',
            'vehicle_reg', 'tyre_id', 'quantity', 'fitting_date', 'fitting_time',
            'tyre_price', 'fitting_price', 'subtotal', 'vat_amount', 'total_price'
        ];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new \WP_Error('missing_field', "Missing required field: {$field}");
            }
        }
        
        // Validate email
        if (!is_email($data['customer_email'])) {
            return new \WP_Error('invalid_email', 'Invalid email address');
        }
        
        // Validate date
        if (!strtotime($data['fitting_date'])) {
            return new \WP_Error('invalid_date', 'Invalid fitting date');
        }
        
        // Validate quantity
        if (!in_array($data['quantity'], [1, 2, 4])) {
            return new \WP_Error('invalid_quantity', 'Invalid quantity. Must be 1, 2, or 4 tyres.');
        }
        
        // Validate tyre exists
        global $wpdb;
        $tyre_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}bms_tyres WHERE id = %d AND is_active = 1",
            $data['tyre_id']
        ));
        
        if (!$tyre_exists) {
            return new \WP_Error('invalid_tyre', 'Selected tyre is not available');
        }
        
        return true;
    }
    
    /**
     * Get basic time slots (fallback)
     */
    private function get_basic_time_slots($date, $duration) {
        $slots = [];
        
        // Basic opening hours: 8 AM to 6 PM, Monday to Friday; 8 AM to 4 PM Saturday
        $day_of_week = date('N', strtotime($date)); // 1 = Monday, 7 = Sunday
        
        if ($day_of_week == 7) { // Sunday - closed;
            return [];
        }
        
        $start_hour = 8;
        $end_hour = ($day_of_week == 6) ? 16 : 18; // Saturday until 4 PM, others until 6 PM
        
        // Generate 30-minute slots, ensuring enough time for the service
        $slot_duration = 30; // minutes
        $slots_needed = ceil($duration / $slot_duration);
        
        for ($hour = $start_hour; $hour < $end_hour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $slot_duration) {
                // Check if we have enough time before closing
                $slot_end = $hour * 60 + $minute + $duration;
                $closing_time = $end_hour * 60;
                
                if ($slot_end <= $closing_time) {
                    $time_string = sprintf('%02d:%02d', $hour, $minute);
                    $slots[] = $time_string;
                }
            }
        }
        
        return $slots;
    }
    
    /**
     * Get popular tyre sizes for quick access
     */
    public function get_popular_sizes() {
        return [
            '175/65R14' => 'Small cars (Fiesta, Corsa)',
            '185/60R15' => 'Compact cars (Polo, Fiesta)', 
            '195/65R15' => 'Medium cars (Focus, Astra)',
            '205/55R16' => 'Family cars (Golf, Focus)',
            '215/60R16' => 'Larger cars (Mondeo, Passat)',
            '225/50R17' => 'Executive cars (3 Series, C Class)'
        ];
    }
    
    /**
     * Search tyres with advanced filters
     */
    public function advanced_search($filters = []) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyres';
        $where_conditions = ['is_active = 1', 'stock_quantity > 0'];
        $where_values = [];
        
        // Size filter
        if (!empty($filters['size'])) {
            $size_parts = $this->parse_tyre_size($filters['size']);
            if ($size_parts) {
                $where_conditions[] = 'width = %d AND profile = %d AND rim = %d';
                $where_values[] = $size_parts['width'];
                $where_values[] = $size_parts['profile'];
                $where_values[] = $size_parts['rim'];
            }
        }
        
        // Brand filter
        if (!empty($filters['brand'])) {
            $where_conditions[] = 'brand = %s';
            $where_values[] = $filters['brand'];
        }
        
        // Brand tier filter
        if (!empty($filters['brand_tier'])) {
            $where_conditions[] = 'brand_tier = %s';
            $where_values[] = $filters['brand_tier'];
        }
        
        // Price range filter
        if (!empty($filters['min_price'])) {
            $where_conditions[] = 'price >= %f';
            $where_values[] = floatval($filters['min_price']);
        }
        
        if (!empty($filters['max_price'])) {
            $where_conditions[] = 'price <= %f';
            $where_values[] = floatval($filters['max_price']);
        }
        
        // Season filter
        if (!empty($filters['season'])) {
            $where_conditions[] = 'season = %s';
            $where_values[] = $filters['season'];
        }
        
        // Build query
        $where_clause = implode(' AND ', $where_conditions);
        $order_by = $filters['sort'] ?? 'price ASC';
        
        $allowed_sorts = ['price ASC', 'price DESC', 'brand ASC', 'brand_tier ASC'];
        if (!in_array($order_by, $allowed_sorts)) {
            $order_by = 'price ASC';
        }
        
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY {$order_by}";
        
        if (empty($where_values)) {
            $results = $wpdb->get_results($query);
        } else {
            $results = $wpdb->get_results($wpdb->prepare($query, ...$where_values));
        }
        
        return $this->format_tyre_results($results);
    }
    
    /**
     * Create Google Calendar event for tyre booking
     */
    private function create_calendar_event($booking_id, $booking_data, $booking_reference) {
        // Only create calendar event if Google Calendar service is available
        if (!class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            return;
        }
        
        $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
        
        if (!$calendar_service->is_available()) {
            error_log('Google Calendar service not available for tyre booking: ' . $booking_id);
            return;
        }
        
        // Get tyre details for better event description
        $tyre_details = $this->get_tyre_details($booking_data['tyre_id']);
        $tyre_info = '';
        
        if (!is_wp_error($tyre_details)) {
            $tyre_info = $tyre_details['brand'] . ' ' . $tyre_details['model'] . ' ' . $tyre_details['size'];
        }
        
        // Calculate duration based on quantity (30 minutes per tyre + 15 minutes setup)
        $duration = ($booking_data['quantity'] * 30) + 15;
        
        // Prepare calendar event data
        $calendar_data = array(
            'customer_name' => $booking_data['customer_name'],
            'customer_email' => $booking_data['customer_email'],
            'customer_phone' => $booking_data['customer_phone'],
            'vehicle_reg' => $booking_data['vehicle_reg'],
            'vehicle_make' => $booking_data['vehicle_make'] ?? '',
            'vehicle_model' => $booking_data['vehicle_model'] ?? '',
            'service_name' => "Tyre Fitting ({$booking_data['quantity']} x {$tyre_info})",
            'booking_date' => $booking_data['fitting_date'],
            'booking_time' => $booking_data['fitting_time'],
            'booking_reference' => $booking_reference,
            'special_requirements' => $booking_data['special_requirements'] ?? '',
            'duration' => $duration
        );
        
        // Create event
        $result = $calendar_service->create_event($calendar_data);
        
        if (!is_wp_error($result) && $result['success']) {
            // Store calendar event ID in tyre booking meta
            global $wpdb;
            $meta_table = $wpdb->prefix . 'bms_tyre_booking_meta';
            
            // Create meta table if it doesn't exist
            $this->create_meta_table_if_not_exists();
            
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
            
            error_log('Google Calendar event created successfully for tyre booking: ' . $booking_id);
        } else {
            $error_message = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
            error_log('Failed to create Google Calendar event for tyre booking ' . $booking_id . ': ' . $error_message);
        }
    }
    
    /**
     * Update Google Calendar event for tyre booking
     */
    public function update_calendar_event($booking_id, $booking_data) {
        if (!class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            return;
        }
        
        $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
        
        if (!$calendar_service->is_available()) {
            return;
        }
        
        // Get existing calendar event ID
        global $wpdb;
        $meta_table = $wpdb->prefix . 'bms_tyre_booking_meta';
        
        $event_id = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$meta_table} 
             WHERE booking_id = %d AND meta_key = 'google_calendar_event_id'",
            $booking_id
        ));
        
        if (!$event_id) {
            return;
        }
        
        // Get tyre details
        $tyre_details = $this->get_tyre_details($booking_data['tyre_id']);
        $tyre_info = '';
        
        if (!is_wp_error($tyre_details)) {
            $tyre_info = $tyre_details['brand'] . ' ' . $tyre_details['model'] . ' ' . $tyre_details['size'];
        }
        
        $duration = ($booking_data['quantity'] * 30) + 15;
        
        // Prepare update data
        $calendar_data = array(
            'customer_name' => $booking_data['customer_name'],
            'customer_email' => $booking_data['customer_email'],
            'customer_phone' => $booking_data['customer_phone'],
            'vehicle_reg' => $booking_data['vehicle_reg'],
            'vehicle_make' => $booking_data['vehicle_make'] ?? '',
            'vehicle_model' => $booking_data['vehicle_model'] ?? '',
            'service_name' => "Tyre Fitting ({$booking_data['quantity']} x {$tyre_info})",
            'booking_date' => $booking_data['fitting_date'],
            'booking_time' => $booking_data['fitting_time'],
            'booking_reference' => $booking_data['booking_reference'],
            'special_requirements' => $booking_data['special_requirements'] ?? '',
            'duration' => $duration
        );
        
        $result = $calendar_service->update_event($event_id, $calendar_data);
        
        if (is_wp_error($result)) {
            error_log('Failed to update Google Calendar event for tyre booking ' . $booking_id . ': ' . $result->get_error_message());
        }
    }
    
    /**
     * Delete Google Calendar event for tyre booking
     */
    public function delete_calendar_event($booking_id) {
        if (!class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            return;
        }
        
        $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
        
        if (!$calendar_service->is_available()) {
            return;
        }
        
        // Get existing calendar event ID
        global $wpdb;
        $meta_table = $wpdb->prefix . 'bms_tyre_booking_meta';
        
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
    
    /**
     * Check if time slot is available (including Google Calendar)
     */
    public function is_slot_available($date, $time, $duration = 60) {
        // First check basic availability
        $basic_slots = $this->get_basic_time_slots($date, $duration);
        
        if (!in_array($time, $basic_slots)) {
            return false;
        }
        
        // Check Google Calendar if available
        if (class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
            
            if ($calendar_service->is_available()) {
                return $calendar_service->is_slot_available($date, $time, $duration);
            }
        }
        
        return true;
    }
    
    /**
     * Create meta table for tyre bookings if it doesn't exist
     */
    private function create_meta_table_if_not_exists() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_tyre_booking_meta';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE {$table_name} (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                booking_id bigint(20) NOT NULL,
                meta_key varchar(255) NOT NULL,
                meta_value longtext,
                created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY booking_id (booking_id),
                KEY meta_key (meta_key)
            ) {$charset_collate};";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}

// Convenience function for global access
function bms_tyre_service() {
    static $instance = null;
    if ($instance === null) {
        $instance = new TyreService();
    }
    return $instance;
}