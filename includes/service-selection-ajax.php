<?php
/**
 * Blue Motors Southampton - Service Selection AJAX Handlers
 * Compatible with existing plugin AJAX structure and pricing calculator
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler for storing service selection
 */
function bms_ajax_store_service_selection() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bms_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    try {
        // Get selection data
        $selection = $_POST['selection'] ?? [];
        
        // Validate required fields
        if (empty($selection['service'])) {
            wp_send_json_error('Service selection is required');
            return;
        }
        
        // Sanitize data
        $clean_selection = [
            'service' => sanitize_text_field($selection['service']),
            'motIncluded' => filter_var($selection['motIncluded'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'price' => floatval($selection['price'] ?? 0),
            'totalPrice' => floatval($selection['totalPrice'] ?? 0),
            'timestamp' => current_time('mysql'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''];
        
        // Store in session
        BMS_Session::set('service_selection', $clean_selection);
        
        // Also store in WordPress user meta if user is logged in
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'bms_last_service_selection', $clean_selection);
        }
        
        wp_send_json_success([
            'message' => 'Service selection stored successfully',
            'selection' => $clean_selection
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error('Failed to store service selection: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for retrieving service selection
 */
function bms_ajax_get_service_selection() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bms_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    try {
        // Get from session first
        $selection = BMS_Session::get('service_selection');
        
        // If not in session and user is logged in, try user meta
        if (!$selection && is_user_logged_in()) {
            $user_id = get_current_user_id();
            $selection = get_user_meta($user_id, 'bms_last_service_selection', true);
        }
        
        if ($selection) {
            wp_send_json_success([
                'selection' => $selection,
                'found' => true
            ]);
        } else {
            wp_send_json_success([
                'selection' => null,
                'found' => false
            ]);
        }
        
    } catch (Exception $e) {
        wp_send_json_error('Failed to retrieve service selection: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for calculating service pricing using existing pricing calculator
 */
function bms_ajax_calculate_service_pricing() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bms_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    try {
        // Get parameters
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        $engine_size = intval($_POST['engine_size'] ?? 1600);
        $fuel_type = sanitize_text_field($_POST['fuel_type'] ?? 'petrol');
        
        // Load existing pricing calculator
        if (!class_exists('BlueMotosSouthampton\Services\PricingCalculator')) {
            require_once BMS_PLUGIN_DIR . 'includes/services/class-pricing-calculator.php';
        }
        
        $calculator = new BlueMotosSouthampton\Services\PricingCalculator();
        
        if ($service_type) {
            // Calculate specific service price
            $price = $calculator->calculate($service_type, $engine_size, $fuel_type);
            
            wp_send_json_success([
                'service_type' => $service_type,
                'price' => $price,
                'engine_size' => $engine_size,
                'fuel_type' => $fuel_type
            ]);
        } else {
            // Calculate all service prices
            $prices = [
                'interim_service' => $calculator->calculate('interim_service', $engine_size, $fuel_type),
                'full_service' => $calculator->calculate('full_service', $engine_size, $fuel_type),
                'mot_test' => $calculator->calculate('mot_test', $engine_size, $fuel_type)
            ];
            
            // Add convenience aliases
            $prices['interim'] = $prices['interim_service'];
            $prices['full'] = $prices['full_service'];
            $prices['mot'] = $prices['mot_test'];
            
            wp_send_json_success([
                'prices' => $prices,
                'engine_size' => $engine_size,
                'fuel_type' => $fuel_type
            ]);
        }
        
    } catch (Exception $e) {
        wp_send_json_error('Failed to calculate pricing: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for getting MOT combo pricing
 */
function bms_ajax_get_mot_combo_pricing() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bms_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    try {
        // Get parameters
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        $engine_size = intval($_POST['engine_size'] ?? 1600);
        $fuel_type = sanitize_text_field($_POST['fuel_type'] ?? 'petrol');
        
        if (empty($service_type)) {
            wp_send_json_error('Service type is required');
            return;
        }
        
        // Load existing pricing calculator
        if (!class_exists('BlueMotosSouthampton\Services\PricingCalculator')) {
            require_once BMS_PLUGIN_DIR . 'includes/services/class-pricing-calculator.php';
        }
        
        $calculator = new BlueMotosSouthampton\Services\PricingCalculator();
        
        // Calculate individual prices
        $service_price = $calculator->calculate($service_type, $engine_size, $fuel_type);
        $mot_price = $calculator->calculate('mot_test', $engine_size, $fuel_type);
        
        // Calculate combo discounts
        $discounts = [
            'interim_service' => 10.00,
            'full_service' => 15.00];
        
        $discount = $discounts[$service_type] ?? 0;
        $total_individual = $service_price + $mot_price;
        $total_combo = $total_individual - $discount;
        
        $combo_pricing = [
            'service_price' => $service_price,
            'mot_price' => $mot_price,
            'discount' => $discount,
            'total_individual' => $total_individual,
            'total_combo' => $total_combo,
            'savings' => $discount];
        
        wp_send_json_success([
            'combo_pricing' => $combo_pricing
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error('Failed to calculate MOT combo pricing: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for updating pricing based on vehicle details
 */
function bms_ajax_update_pricing_for_vehicle() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'bms_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    try {
        // Get vehicle parameters
        $engine_size = intval($_POST['engine_size'] ?? 1600);
        $fuel_type = sanitize_text_field($_POST['fuel_type'] ?? 'petrol');
        
        // Load existing pricing calculator
        if (!class_exists('BlueMotosSouthampton\Services\PricingCalculator')) {
            require_once BMS_PLUGIN_DIR . 'includes/services/class-pricing-calculator.php';
        }
        
        $calculator = new BlueMotosSouthampton\Services\PricingCalculator();
        
        // Calculate all service prices
        $prices = [
            'interim_service' => $calculator->calculate('interim_service', $engine_size, $fuel_type),
            'full_service' => $calculator->calculate('full_service', $engine_size, $fuel_type),
            'mot_test' => $calculator->calculate('mot_test', $engine_size, $fuel_type)
        ];
        
        // Calculate combo prices
        $combo_prices = [
            'interim_combo' => $prices['interim_service'] + $prices['mot_test'] - 10.00,
            'full_combo' => $prices['full_service'] + $prices['mot_test'] - 15.00];
        
        wp_send_json_success([
            'prices' => $prices,
            'combo_prices' => $combo_prices,
            'engine_size' => $engine_size,
            'fuel_type' => $fuel_type
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error('Failed to update pricing: ' . $e->getMessage());
    }
}

// Register AJAX handlers
add_action('wp_ajax_bms_store_service_selection', 'bms_ajax_store_service_selection');
add_action('wp_ajax_nopriv_bms_store_service_selection', 'bms_ajax_store_service_selection');

add_action('wp_ajax_bms_get_service_selection', 'bms_ajax_get_service_selection');
add_action('wp_ajax_nopriv_bms_get_service_selection', 'bms_ajax_get_service_selection');

add_action('wp_ajax_bms_calculate_service_pricing', 'bms_ajax_calculate_service_pricing');
add_action('wp_ajax_nopriv_bms_calculate_service_pricing', 'bms_ajax_calculate_service_pricing');

add_action('wp_ajax_bms_get_mot_combo_pricing', 'bms_ajax_get_mot_combo_pricing');
add_action('wp_ajax_nopriv_bms_get_mot_combo_pricing', 'bms_ajax_get_mot_combo_pricing');

add_action('wp_ajax_bms_update_pricing_for_vehicle', 'bms_ajax_update_pricing_for_vehicle');
add_action('wp_ajax_nopriv_bms_update_pricing_for_vehicle', 'bms_ajax_update_pricing_for_vehicle');
