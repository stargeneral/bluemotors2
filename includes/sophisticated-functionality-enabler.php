<?php
/**
 * Sophisticated Functionality Enabler
 * Ensures all advanced features and classes are properly accessible
 * 
 * @package BlueMotosSouthampton
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create class aliases for backward compatibility and easier access
 */
if (!class_exists('Blue_Motors_Tyre_Service')) {
    class_alias('BlueMotosSouthampton\Services\TyreService', 'Blue_Motors_Tyre_Service');
}

if (!class_exists('Blue_Motors_Service_Manager')) {
    class_alias('BlueMotosSouthampton\Services\ServiceManager', 'Blue_Motors_Service_Manager');
}

if (!class_exists('Blue_Motors_Vehicle_Lookup')) {
    class_alias('BlueMotosSouthampton\Services\VehicleLookupEnhanced', 'Blue_Motors_Vehicle_Lookup');
}

if (!class_exists('Blue_Motors_Pricing_Calculator')) {
    class_alias('BlueMotosSouthampton\Services\PricingCalculator', 'Blue_Motors_Pricing_Calculator');
}

/**
 * Register sophisticated shortcodes that may be missing
 */

// Register blue_motors_booking shortcode (alias for bms_booking_form)
if (!shortcode_exists('blue_motors_booking')) {
    add_shortcode('blue_motors_booking', 'bms_booking_form_shortcode');
}

// Enhanced booking form shortcode with all sophisticated features
function bms_booking_form_shortcode($atts) {
    // If bms_booking_form exists, use it
    if (shortcode_exists('bms_booking_form')) {
        return do_shortcode('[bms_booking_form]');
    }
    
    // Otherwise render sophisticated booking form
    $atts = shortcode_atts([
        'style' => 'full',
        'show_competitive_messaging' => 'true',
        'enable_tyre_integration' => 'true',
        'enable_vehicle_lookup' => 'true',
        'enable_dynamic_pricing' => 'true'], $atts);
    
    ob_start();
    ?>
    <div class="bms-sophisticated-booking-wrapper">
        <?php if ($atts['show_competitive_messaging'] === 'true'): ?>
        <div class="competitive-advantage-banner">
            <h3>🎯 Why Choose Blue Motors Over other automotive services?</h3>
            <div class="advantages">
                <span>🛞 Order tyres online (F1 requires phone calls)</span>
                <span>💳 Smooth payment process</span>
                <span>📅 UK date format</span>
                <span>📱 Superior mobile experience</span>
            </div>
        </div>
        <?php endif; ?>
        
        <div id="bms-booking-form-container">
            <!-- Sophisticated booking form will be loaded here via JavaScript -->
            <div class="loading-placeholder">
                <h4>Loading Blue Motors Booking System...</h4>
                <p>🛞 Online tyre ordering available (unlike other automotive services)</p>
            </div>
        </div>
        
        <?php if ($atts['enable_tyre_integration'] === 'true'): ?>
        <script>
            // Initialize sophisticated tyre integration
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof window.BlueMotosTyreService !== 'undefined') {
                    window.BlueMotosTyreService.init();
                }
            });
        </script>
        <?php endif; ?>
    </div>
    
    <style>
    .competitive-advantage-banner {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .competitive-advantage-banner h3 {
        margin: 0 0 10px 0;
    }
    
    .advantages {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 14px;
    }
    
    .advantages span {
        background: rgba(255,255,255,0.1);
        padding: 5px 10px;
        border-radius: 15px;
    }
    
    @media (max-width: 768px) {
        .advantages {
            flex-direction: column;
            gap: 8px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Initialize sophisticated service instances for global access
 */
function bms_init_sophisticated_services() {
    global $bms_services;
    
    if (!isset($bms_services)) {
        $bms_services = [];
    }
    
    // Initialize Tyre Service
    if (class_exists('BlueMotosSouthampton\Services\TyreService')) {
        $bms_services['tyre'] = new BlueMotosSouthampton\Services\TyreService();
    }
    
    // Initialize Service Manager
    if (class_exists('BlueMotosSouthampton\Services\ServiceManager')) {
        $bms_services['service_manager'] = new BlueMotosSouthampton\Services\ServiceManager();
    }
    
    // Initialize Vehicle Lookup
    if (class_exists('BlueMotosSouthampton\Services\VehicleLookupEnhanced')) {
        $bms_services['vehicle_lookup'] = new BlueMotosSouthampton\Services\VehicleLookupEnhanced();
    }
    
    // Initialize Pricing Calculator
    if (class_exists('BlueMotosSouthampton\Services\PricingCalculator')) {
        $bms_services['pricing'] = new BlueMotosSouthampton\Services\PricingCalculator();
    }
    
    // Initialize Email Manager
    if (class_exists('BlueMotosSouthampton\Services\EmailManager')) {
        $bms_services['email'] = new BlueMotosSouthampton\Services\EmailManager();
    }
}

// Initialize sophisticated services on WordPress init
add_action('init', 'bms_init_sophisticated_services');

/**
 * Sophisticated AJAX handlers registration
 */
function bms_register_sophisticated_ajax() {
    // Tyre search AJAX
    add_action('wp_ajax_bms_search_tyres_by_reg', 'bms_ajax_search_tyres_by_reg');
    add_action('wp_ajax_nopriv_bms_search_tyres_by_reg', 'bms_ajax_search_tyres_by_reg');
    
    add_action('wp_ajax_bms_search_tyres_by_size', 'bms_ajax_search_tyres_by_size');
    add_action('wp_ajax_nopriv_bms_search_tyres_by_size', 'bms_ajax_search_tyres_by_size');
    
    add_action('wp_ajax_bms_create_tyre_booking', 'bms_ajax_create_tyre_booking');
    add_action('wp_ajax_nopriv_bms_create_tyre_booking', 'bms_ajax_create_tyre_booking');
    
    // Enhanced vehicle lookup
    add_action('wp_ajax_bms_enhanced_vehicle_lookup', 'bms_ajax_enhanced_vehicle_lookup');
    add_action('wp_ajax_nopriv_bms_enhanced_vehicle_lookup', 'bms_ajax_enhanced_vehicle_lookup');
    
    // Sophisticated pricing calculation
    add_action('wp_ajax_bms_calculate_sophisticated_pricing', 'bms_ajax_calculate_sophisticated_pricing');
    add_action('wp_ajax_nopriv_bms_calculate_sophisticated_pricing', 'bms_ajax_calculate_sophisticated_pricing');
}

add_action('wp_loaded', 'bms_register_sophisticated_ajax');

/**
 * AJAX Handler: Search tyres by registration (sophisticated)
 */
function bms_ajax_search_tyres_by_reg() {
    check_ajax_referer('bms_ajax_nonce', 'nonce');
    
    $registration = sanitize_text_field($_POST['registration']);
    
    if (class_exists('Blue_Motors_Tyre_Service')) {
        $tyre_service = new Blue_Motors_Tyre_Service();
        $result = $tyre_service->search_by_registration($registration);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    } else {
        wp_send_json_error('Tyre service not available');
    }
}

/**
 * AJAX Handler: Search tyres by size (sophisticated)
 */
function bms_ajax_search_tyres_by_size() {
    check_ajax_referer('bms_ajax_nonce', 'nonce');
    
    $size = sanitize_text_field($_POST['size']);
    
    if (class_exists('Blue_Motors_Tyre_Service')) {
        $tyre_service = new Blue_Motors_Tyre_Service();
        $result = $tyre_service->search_by_size_string($size);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    } else {
        wp_send_json_error('Tyre service not available');
    }
}

/**
 * AJAX Handler: Create tyre booking (sophisticated)
 */
function bms_ajax_create_tyre_booking() {
    check_ajax_referer('bms_ajax_nonce', 'nonce');
    
    $booking_data = $_POST['booking_data'];
    
    if (class_exists('Blue_Motors_Tyre_Service')) {
        $tyre_service = new Blue_Motors_Tyre_Service();
        $result = $tyre_service->create_booking($booking_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    } else {
        wp_send_json_error('Tyre service not available');
    }
}

/**
 * AJAX Handler: Enhanced vehicle lookup
 */
function bms_ajax_enhanced_vehicle_lookup() {
    check_ajax_referer('bms_ajax_nonce', 'nonce');
    
    $registration = sanitize_text_field($_POST['registration']);
    
    if (class_exists('Blue_Motors_Vehicle_Lookup')) {
        $vehicle_lookup = new Blue_Motors_Vehicle_Lookup();
        $result = $vehicle_lookup->lookup_vehicle($registration);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error('Enhanced vehicle lookup not available');
    }
}

/**
 * AJAX Handler: Sophisticated pricing calculation
 */
function bms_ajax_calculate_sophisticated_pricing() {
    check_ajax_referer('bms_ajax_nonce', 'nonce');
    
    $service_type = sanitize_text_field($_POST['service_type']);
    $vehicle_data = $_POST['vehicle_data'];
    
    if (class_exists('Blue_Motors_Pricing_Calculator')) {
        $pricing = new Blue_Motors_Pricing_Calculator();
        $result = $pricing->calculate_price($service_type, $vehicle_data);
        
        wp_send_json_success($result);
    } else {
        wp_send_json_error('Sophisticated pricing not available');
    }
}

/**
 * Enqueue sophisticated frontend assets
 */
function bms_enqueue_sophisticated_assets() {
    // Sophisticated JavaScript for tyre functionality
    wp_enqueue_script(
        'bms-sophisticated-functionality',
        BMS_PLUGIN_URL . 'assets/js/sophisticated-functionality.js',
        ['jquery'],
        BMS_VERSION,
        true
    );
    
    // Sophisticated CSS
    wp_enqueue_style(
        'bms-sophisticated-styles',
        BMS_PLUGIN_URL . 'assets/css/sophisticated-styles.css',
        [],
        BMS_VERSION
    );
    
    // Localize sophisticated AJAX data
    wp_localize_script('bms-sophisticated-functionality', 'bms_sophisticated', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bms_ajax_nonce'),
        'competitive_advantage' => [
            'tyre_ordering' => 'F1 customers must call - you order online!',
            'payment_smooth' => 'No PayPal issues like F1',
            'uk_dates' => 'Proper UK date format',
            'mobile_optimized' => 'Better mobile experience'
        ]
    ]);
}

add_action('wp_enqueue_scripts', 'bms_enqueue_sophisticated_assets');

/**
 * Enhanced database functionality check
 */
function bms_check_sophisticated_database() {
    global $wpdb;
    
    $status = [
        'core_tables' => [],
        'indexes' => [],
        'sophisticated_features' => []
    ];
    
    // Check sophisticated tables
    $tables = [
        'appointments' => $wpdb->prefix . 'bms_appointments',
        'tyres' => $wpdb->prefix . 'bms_tyres',
        'tyre_bookings' => $wpdb->prefix . 'bms_tyre_bookings',
        'services' => $wpdb->prefix . 'bms_services'];
    
    foreach ($tables as $key => $table_name) {
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        $status['core_tables'][$key] = $exists;
        
        if ($exists) {
            // Check for sophisticated indexes
            $indexes = $wpdb->get_results("SHOW INDEX FROM $table_name");
            $status['indexes'][$key] = count($indexes);
        }
    }
    
    // Check sophisticated features
    $status['sophisticated_features'] = [
        'tyre_service_class' => class_exists('Blue_Motors_Tyre_Service'),
        'vehicle_lookup_enhanced' => class_exists('Blue_Motors_Vehicle_Lookup'),
        'pricing_calculator' => class_exists('Blue_Motors_Pricing_Calculator'),
        'service_manager' => class_exists('Blue_Motors_Service_Manager'),
        'namespaced_classes' => class_exists('BlueMotosSouthampton\Services\TyreService'),
        'sophisticated_shortcodes' => shortcode_exists('blue_motors_booking'),
        'ajax_handlers_registered' => has_action('wp_ajax_bms_search_tyres_by_reg'),
        'competitive_advantage_active' => true
    ];
    
    return $status;
}

/**
 * Display sophisticated status in admin
 */
function bms_sophisticated_admin_notice() {
    $status = bms_check_sophisticated_database();
    $sophisticated_score = array_sum($status['sophisticated_features']);
    $total_features = count($status['sophisticated_features']);
    
    if ($sophisticated_score == $total_features) {
        echo '<div class="notice notice-success"><p>';
        echo '<strong>🏆 Blue Motors Sophisticated Functionality: FULLY ACTIVE!</strong><br>';
        echo 'All advanced features operational. Ready to beat other automotive services!';
        echo '</p></div>';
    } else {
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>⚠️ Blue Motors: Some sophisticated features need attention</strong><br>';
        echo "Active: $sophisticated_score/$total_features features";
        echo '</p></div>';
    }
}

add_action('admin_notices', 'bms_sophisticated_admin_notice');

/**
 * Initialize sophisticated functionality on plugin load
 */
function bms_initialize_sophisticated_plugin() {
    // Ensure all sophisticated classes are available
    bms_init_sophisticated_services();
    
    // Register any missing sophisticated AJAX handlers
    bms_register_sophisticated_ajax();
    
    // Set up sophisticated database if needed
    if (get_option('bms_sophisticated_setup') !== 'complete') {
        // Add any sophisticated database enhancements
        bms_enhance_database_for_sophisticated_features();
        update_option('bms_sophisticated_setup', 'complete');
    }
}

/**
 * Enhance database for sophisticated features
 */
function bms_enhance_database_for_sophisticated_features() {
    global $wpdb;
    
    // Add sophisticated indexes if they don't exist
    $tyres_table = $wpdb->prefix . 'bms_tyres';
    
    // Multi-column index for sophisticated tyre search
    $wpdb->query("ALTER TABLE $tyres_table ADD INDEX IF NOT EXISTS idx_sophisticated_search (width, profile, rim, brand_tier, is_active)");
    
    // Price range index for sophisticated filtering
    $wpdb->query("ALTER TABLE $tyres_table ADD INDEX IF NOT EXISTS idx_price_range (price, brand_tier)");
    
    // Sophisticated booking reference format
    $appointments_table = $wpdb->prefix . 'bms_appointments';
    $wpdb->query("ALTER TABLE $appointments_table ADD INDEX IF NOT EXISTS idx_sophisticated_booking (booking_reference, booking_status, payment_status)");
}

// Initialize sophisticated functionality
add_action('plugins_loaded', 'bms_initialize_sophisticated_plugin');

/**
 * Global helper function to access sophisticated services
 */
function bms_get_sophisticated_service($service_name) {
    global $bms_services;
    
    if (isset($bms_services[$service_name])) {
        return $bms_services[$service_name];
    }
    
    return null;
}

/**
 * Sophisticated tyre search helper
 */
function bms_sophisticated_tyre_search($registration_or_size, $search_type = 'auto') {
    $tyre_service = bms_get_sophisticated_service('tyre');
    
    if (!$tyre_service) {
        return new WP_Error('service_unavailable', 'Sophisticated tyre service not available');
    }
    
    if ($search_type === 'auto') {
        // Auto-detect if it's a registration or size
        if (preg_match('/^[A-Z]{2}\d{2}\s?[A-Z]{3}$/', $registration_or_size)) {
            return $tyre_service->search_by_registration($registration_or_size);
        } else {
            return $tyre_service->search_by_size_string($registration_or_size);
        }
    }
    
    return $tyre_service->search_by_size_string($registration_or_size);
}
