<?php
/**
 * Blue Motors Southampton - Tyre AJAX Handlers
 * Phase 2: Tyre Services Implementation
 * 
 * Handles all AJAX requests for the tyre ordering system
 * Our competitive advantage over industry leaders
 * 
 * @package BlueMotosSouthampton
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Search tyres by vehicle registration
 */
add_action('wp_ajax_bms_search_tyres_by_reg', 'bms_ajax_search_tyres_by_reg');
add_action('wp_ajax_nopriv_bms_search_tyres_by_reg', 'bms_ajax_search_tyres_by_reg');

function bms_ajax_search_tyres_by_reg() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_vehicle_lookup')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $registration = sanitize_text_field($_POST['registration']);
    
    if (empty($registration)) {
        wp_send_json_error('Registration number is required');
        return;
    }
    
    try {
        // Use the proper namespaced class
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        $result = $tyre_service->search_by_registration($registration);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
        
    } catch (Exception $e) {
        error_log('Tyre search by registration error: ' . $e->getMessage());
        wp_send_json_error('Search failed. Please try again.');
    }
}

/**
 * Search tyres by size
 */
add_action('wp_ajax_bms_search_tyres_by_size', 'bms_ajax_search_tyres_by_size');
add_action('wp_ajax_nopriv_bms_search_tyres_by_size', 'bms_ajax_search_tyres_by_size');

function bms_ajax_search_tyres_by_size() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_vehicle_lookup')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $size = sanitize_text_field($_POST['size']);
    
    if (empty($size)) {
        wp_send_json_error('Tyre size is required');
        return;
    }
    
    try {
        // Use the proper namespaced class
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        $result = $tyre_service->search_by_size_string($size);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
        
    } catch (Exception $e) {
        error_log('Tyre search by size error: ' . $e->getMessage());
        wp_send_json_error('Search failed. Please try again.');
    }
}

/**
 * Get tyre details by ID
 */
add_action('wp_ajax_bms_get_tyre_details', 'bms_ajax_get_tyre_details');
add_action('wp_ajax_nopriv_bms_get_tyre_details', 'bms_ajax_get_tyre_details');

function bms_ajax_get_tyre_details() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_vehicle_lookup')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $tyre_id = intval($_POST['tyre_id']);
    
    if (empty($tyre_id)) {
        wp_send_json_error('Tyre ID is required');
        return;
    }
    
    try {
        // Use the proper namespaced class
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        $result = $tyre_service->get_tyre_details($tyre_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
        
    } catch (Exception $e) {
        error_log('Get tyre details error: ' . $e->getMessage());
        wp_send_json_error('Failed to load tyre details. Please try again.');
    }
}

/**
 * Calculate tyre pricing
 */
add_action('wp_ajax_bms_calculate_tyre_price', 'bms_ajax_calculate_tyre_price');
add_action('wp_ajax_nopriv_bms_calculate_tyre_price', 'bms_ajax_calculate_tyre_price');

function bms_ajax_calculate_tyre_price() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_vehicle_lookup')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $tyre_id = intval($_POST['tyre_id']);
    $quantity = intval($_POST['quantity']);
    
    if (empty($tyre_id) || empty($quantity)) {
        wp_send_json_error('Tyre ID and quantity are required');
        return;
    }
    
    if (!in_array($quantity, [1, 2, 4])) {
        wp_send_json_error('Invalid quantity. Must be 1, 2, or 4 tyres.');
        return;
    }
    
    try {
        // Use the proper namespaced class
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        $result = $tyre_service->calculate_total_price($tyre_id, $quantity);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
        
    } catch (Exception $e) {
        error_log('Tyre price calculation error: ' . $e->getMessage());
        wp_send_json_error('Price calculation failed. Please try again.');
    }
}

/**
 * Get available fitting slots
 */
add_action('wp_ajax_bms_get_fitting_slots', 'bms_ajax_get_fitting_slots');
add_action('wp_ajax_nopriv_bms_get_fitting_slots', 'bms_ajax_get_fitting_slots');

function bms_ajax_get_fitting_slots() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_vehicle_lookup')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $date = sanitize_text_field($_POST['date']);
    $quantity = intval($_POST['quantity']) ?: 1;
    
    if (empty($date)) {
        wp_send_json_error('Date is required');
        return;
    }
    
    try {
        // Use the proper namespaced class
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        $slots = $tyre_service->get_available_fitting_slots($date, $quantity);
        
        wp_send_json_success(['slots' => $slots]);
        
    } catch (Exception $e) {
        error_log('Get fitting slots error: ' . $e->getMessage());
        wp_send_json_error('Failed to load available slots. Please try again.');
    }
}

/**
 * Create tyre booking
 */
add_action('wp_ajax_bms_create_tyre_booking', 'bms_ajax_create_tyre_booking');
add_action('wp_ajax_nopriv_bms_create_tyre_booking', 'bms_ajax_create_tyre_booking');

function bms_ajax_create_tyre_booking() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_vehicle_lookup')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Collect and sanitize booking data
    $booking_data = [
        'customer_name' => sanitize_text_field($_POST['customer_name']),
        'customer_email' => sanitize_email($_POST['customer_email']),
        'customer_phone' => sanitize_text_field($_POST['customer_phone']),
        'vehicle_reg' => sanitize_text_field($_POST['vehicle_reg']),
        'vehicle_make' => sanitize_text_field($_POST['vehicle_make']),
        'vehicle_model' => sanitize_text_field($_POST['vehicle_model']),
        'vehicle_year' => intval($_POST['vehicle_year']),
        'tyre_id' => intval($_POST['tyre_id']),
        'quantity' => intval($_POST['quantity']),
        'fitting_date' => sanitize_text_field($_POST['fitting_date']),
        'fitting_time' => sanitize_text_field($_POST['fitting_time']),
        'tyre_price' => floatval($_POST['tyre_price']),
        'fitting_price' => floatval($_POST['fitting_price']),
        'subtotal' => floatval($_POST['subtotal']),
        'vat_amount' => floatval($_POST['vat_amount']),
        'total_price' => floatval($_POST['total_price']),
        'special_requirements' => sanitize_textarea_field($_POST['special_requirements'] ?? '')
    ];
    
    try {
        // Use the proper namespaced class
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        $result = $tyre_service->create_booking($booking_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        // Send confirmation email
        bms_send_tyre_booking_confirmation($result['booking_id'], $booking_data);
        
        wp_send_json_success($result);
        
    } catch (Exception $e) {
        error_log('Create tyre booking error: ' . $e->getMessage());
        wp_send_json_error('Booking creation failed. Please try again.');
    }
}

/**
 * Get available brands
 */
add_action('wp_ajax_bms_get_tyre_brands', 'bms_ajax_get_tyre_brands');
add_action('wp_ajax_nopriv_bms_get_tyre_brands', 'bms_ajax_get_tyre_brands');

function bms_ajax_get_tyre_brands() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_vehicle_lookup')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    try {
        // Use the proper namespaced class
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        $brands = $tyre_service->get_available_brands();
        
        wp_send_json_success(['brands' => $brands]);
        
    } catch (Exception $e) {
        error_log('Get tyre brands error: ' . $e->getMessage());
        wp_send_json_error('Failed to load brands. Please try again.');
    }
}

/**
 * Advanced tyre search
 */
add_action('wp_ajax_bms_advanced_tyre_search', 'bms_ajax_advanced_tyre_search');
add_action('wp_ajax_nopriv_bms_advanced_tyre_search', 'bms_ajax_advanced_tyre_search');

function bms_ajax_advanced_tyre_search() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_vehicle_lookup')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Build filters array
    $filters = [];
    
    if (!empty($_POST['size'])) {
        $filters['size'] = sanitize_text_field($_POST['size']);
    }
    
    if (!empty($_POST['brand'])) {
        $filters['brand'] = sanitize_text_field($_POST['brand']);
    }
    
    if (!empty($_POST['brand_tier'])) {
        $filters['brand_tier'] = sanitize_text_field($_POST['brand_tier']);
    }
    
    if (!empty($_POST['min_price'])) {
        $filters['min_price'] = floatval($_POST['min_price']);
    }
    
    if (!empty($_POST['max_price'])) {
        $filters['max_price'] = floatval($_POST['max_price']);
    }
    
    if (!empty($_POST['season'])) {
        $filters['season'] = sanitize_text_field($_POST['season']);
    }
    
    if (!empty($_POST['sort'])) {
        $filters['sort'] = sanitize_text_field($_POST['sort']);
    }
    
    try {
        // Use the proper namespaced class
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        $results = $tyre_service->advanced_search($filters);
        
        wp_send_json_success($results);
        
    } catch (Exception $e) {
        error_log('Advanced tyre search error: ' . $e->getMessage());
        wp_send_json_error('Search failed. Please try again.');
    }
}

/**
 * Send tyre booking confirmation email
 */
function bms_send_tyre_booking_confirmation($booking_id, $booking_data) {
    try {
        // Load email service
        if (function_exists('bms_smtp')) {
            $smtp = bms_smtp();
            
            $subject = 'Tyre Fitting Confirmation - Blue Motors Southampton';
            
            $message = "
            <h2>Tyre Fitting Confirmation</h2>
            <p>Dear {$booking_data['customer_name']},</p>
            <p>Thank you for booking your tyre fitting with Blue Motors Southampton.</p>
            
            <h3>Booking Details</h3>
            <p><strong>Reference:</strong> {$booking_data['booking_reference']}</p>
            <p><strong>Fitting Date:</strong> " . date('l, F j, Y', strtotime($booking_data['fitting_date'])) . "</p>
            <p><strong>Fitting Time:</strong> " . date('g:i A', strtotime($booking_data['fitting_time'])) . "</p>
            <p><strong>Vehicle:</strong> {$booking_data['vehicle_reg']}</p>
            <p><strong>Quantity:</strong> {$booking_data['quantity']} tyre(s)</p>
            <p><strong>Total Price:</strong> £" . number_format($booking_data['total_price'], 2) . "</p>
            
            <h3>🎯 Why You Made the Right Choice</h3>
            <p>Unlike industry leaders, you were able to order your tyres completely online without any phone calls!</p>
            
            <p><strong>Our Location:</strong><br>
            Blue Motors Southampton<br>
            1 Kent St, Northam<br>
            Southampton SO14 5SP<br>
            Tel: 023 8000 0000</p>
            
            <p>Please arrive 10 minutes before your appointment time.</p>
            
            <p>Best regards,<br>
            The Blue Motors Southampton Team</p>;
            ";
            
            $smtp->send_email($booking_data['customer_email'], $subject, $message);
        }
    } catch (Exception $e) {
        error_log('Tyre booking confirmation email error: ' . $e->getMessage());
    }
}

/**
 * Additional AJAX handler for calendar system compatibility
 * Maps to the existing create_tyre_booking functionality
 */
add_action('wp_ajax_book_tyre_fitting', 'bms_ajax_book_tyre_fitting');
add_action('wp_ajax_nopriv_book_tyre_fitting', 'bms_ajax_book_tyre_fitting');

function bms_ajax_book_tyre_fitting() {
    // This is an alias for the main booking function
    bms_ajax_create_tyre_booking();
}

/**
 * Additional AJAX handler for calendar time slots compatibility  
 * Maps to the existing get_fitting_slots functionality
 */
add_action('wp_ajax_get_fitting_slots', 'bms_ajax_get_fitting_slots_alias');
add_action('wp_ajax_nopriv_get_fitting_slots', 'bms_ajax_get_fitting_slots_alias');

function bms_ajax_get_fitting_slots_alias() {
    // This is an alias for the main fitting slots function
    bms_ajax_get_fitting_slots();
}