<?php
/**
 * Plugin Name: Blue Motors Southampton
 * Plugin URI: https://bluemotors-southampton.co.uk
 * Description: Professional booking system for Blue Motors Southampton garage with Google Calendar integration, comprehensive tyre services, and DVLA vehicle lookup.
 * Version: 1.4.0
 * Author: Blue Motors Development Team
 * Author URI: https://bluemotors-southampton.co.uk
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: blue-motors-southampton
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 8.0
 * 
 * @package BlueMotosSouthampton
 * @version 1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BMS_VERSION', '1.4.0');
define('BMS_PLUGIN_FILE', __FILE__);
define('BMS_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Load autoloader
require_once BMS_PLUGIN_DIR . 'includes/autoloader.php';
define('BMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BMS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('BMS_MIN_PHP_VERSION', '8.0');
define('BMS_MIN_WP_VERSION', '6.0');

// Load configuration (this will define BM_PLUGIN_DIR and other constants)
require_once BMS_PLUGIN_DIR . 'config/constants.php';

// Compatibility check - BM_PLUGIN_DIR should now be defined by constants.php
if (!defined('BM_PLUGIN_DIR')) {
    // Fallback if constants.php somehow didn't define it
    define('BM_PLUGIN_DIR', BMS_PLUGIN_DIR);
}
require_once BMS_PLUGIN_DIR . 'includes/shortcode-callbacks.php';
require_once BMS_PLUGIN_DIR . 'includes/shortcode-init.php';
require_once BMS_PLUGIN_DIR . 'includes/shortcode-handlers-fallback.php'; // Fallback handlers for missing shortcodes

// Load Google Calendar integration
require_once BMS_PLUGIN_DIR . 'includes/google-calendar-setup.php';

// Initialize all shortcodes
add_action('init', 'bms_init_all_shortcodes');


// Load Stripe library if enabled and available
if (defined('BM_STRIPE_ENABLED') && BM_STRIPE_ENABLED) {
    if (!class_exists('\Stripe\Stripe')) {
        // Try to load Stripe from composer
        if (file_exists(BMS_PLUGIN_DIR . 'vendor/autoload.php')) {
            require_once BMS_PLUGIN_DIR . 'vendor/autoload.php';
        } else {
            // For now, we'll provide a notice that Stripe library is needed
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning"><p>';
                echo '<strong>Blue Motors Southampton:</strong> Stripe PHP library not found. ';
                echo 'To enable payments, install via: <code>composer require stripe/stripe-php</code>';
                echo '</p></div>';
            });
        }
    }
}

// Load core classes
require_once BMS_PLUGIN_DIR . 'includes/class-blue-motors-southampton.php';
// Load service classes
require_once BMS_PLUGIN_DIR . 'includes/services/class-pricing-calculator.php';
require_once BMS_PLUGIN_DIR . 'includes/services/class-service-manager.php';
require_once BMS_PLUGIN_DIR . 'includes/services/class-service-manager-enhanced.php'; // Phase 3
require_once BMS_PLUGIN_DIR . 'includes/services/class-vehicle-lookup-enhanced.php';
require_once BMS_PLUGIN_DIR . 'includes/services/class-dvla-api-enhanced.php';
require_once BMS_PLUGIN_DIR . 'includes/services/class-dvsa-mot-api-enhanced.php';
require_once BMS_PLUGIN_DIR . 'includes/services/class-vehicle-lookup-combined.php';
require_once BMS_PLUGIN_DIR . 'includes/class-bms-session.php';

// Create class alias for BMS_Session to fix namespace issues
if (!class_exists('BMS_Session')) {
    class_alias('\\BlueMotosSouthampton\\Utils\\BMS_Session', 'BMS_Session');
}
require_once BMS_PLUGIN_DIR . 'includes/services/class-pricing-calculator.php';
require_once BMS_PLUGIN_DIR . 'includes/service-selection-ajax.php';
require_once BMS_PLUGIN_DIR . 'includes/services/class-tyre-service.php'; // Phase 2: Tyre ordering system

// Load Cache Manager (required by Customer Service and Smart Scheduler)
require_once BMS_PLUGIN_DIR . 'includes/services/class-cache-manager.php';

// Load Phase 4 Advanced Services
require_once BMS_PLUGIN_DIR . 'includes/services/class-customer-service.php'; // Phase 4: Customer history tracking
require_once BMS_PLUGIN_DIR . 'includes/services/class-smart-scheduler.php'; // Phase 4: AI-powered scheduling

// Load SMTP Email System
require_once BMS_PLUGIN_DIR . 'includes/services/class-bms-smtp.php';
require_once BMS_PLUGIN_DIR . 'includes/services/class-email-manager.php';
require_once BMS_PLUGIN_DIR . 'includes/smtp-status-notice.php';

// Load Settings Management System (Phase 2)
require_once BMS_PLUGIN_DIR . 'includes/services/class-settings-migrator.php';

// Load Shortcode Validator (Phase 4+)
require_once BMS_PLUGIN_DIR . 'includes/class-shortcode-validator.php';

// Load Database Management System (Phase 3)
if (file_exists(BMS_PLUGIN_DIR . 'includes/class-database-manager.php')) {
    require_once BMS_PLUGIN_DIR . 'includes/class-database-manager.php';
}

// Load Enhanced Database Management System (Phase 3+)
if (file_exists(BMS_PLUGIN_DIR . 'includes/services/class-database-manager-enhanced.php')) {
    require_once BMS_PLUGIN_DIR . 'includes/services/class-database-manager-enhanced.php';
}

// Load Booking Integration System (Phase 3)
require_once BMS_PLUGIN_DIR . 'includes/services/class-booking-integration.php';

// Load Phase 2 completion notice
require_once BMS_PLUGIN_DIR . 'includes/phase2-notice.php';

// Load Phase 2 testing (for development/testing)
if (defined('WP_DEBUG') && WP_DEBUG) {
    require_once BMS_PLUGIN_DIR . 'testing/test-phase2.php';
    require_once BMS_PLUGIN_DIR . 'testing/test-phase3.php';
    require_once BMS_PLUGIN_DIR . 'testing/test-phase4-integration.php'; // Phase 4 integration test
}

// Load AJAX handlers
require_once BMS_PLUGIN_DIR . 'includes/ajax/vehicle-lookup.php';
require_once BMS_PLUGIN_DIR . 'includes/ajax/tyre-ajax.php'; // Phase 2: Tyre ordering system
require_once BMS_PLUGIN_DIR . 'includes/ajax/shortcode-testing-ajax.php'; // Shortcode testing AJAX

// Load shortcodes
require_once BMS_PLUGIN_DIR . 'includes/shortcodes/vehicle-lookup-shortcode.php';
require_once BMS_PLUGIN_DIR . 'includes/shortcodes/tyre-search-shortcode.php'; // Phase 2: Tyre search shortcode
require_once BMS_PLUGIN_DIR . 'includes/shortcodes/enhanced-service-cards-shortcode.php'; // Phase 3
require_once BMS_PLUGIN_DIR . 'includes/shortcodes/booking-form-shortcode.php'; // Main booking form
require_once BMS_PLUGIN_DIR . 'includes/shortcodes/location-info-shortcode.php'; // Location and contact info
require_once BMS_PLUGIN_DIR . 'includes/shortcodes/competitive-shortcodes.php'; // Service excellence shortcodes

// Load AGGRESSIVE calendar fix - CRITICAL for WordPress pages
require_once BMS_PLUGIN_DIR . 'aggressive-calendar-fix.php'; // Direct WordPress page fix

// Load STICKY calendar fix - Prevents calendar from disappearing
require_once BMS_PLUGIN_DIR . 'sticky-calendar-fix.php'; // Keeps calendar open

// Calendar functionality is integrated into the main tyre-booking.js file
// See assets/js/tyre-booking.js for calendar implementation
// Additional WordPress-level calendar fixes are in:
// - aggressive-calendar-fix.php (ensures calendar loads on WordPress pages)
// - sticky-calendar-fix.php (prevents calendar from closing prematurely)

/**
 * Begins execution of the plugin - Enhanced for Phase 3
 */
function blue_motors_southampton_init() {
    // Initialize enhanced services (Phase 3)
    \BlueMotosSouthampton\Services\ServiceManagerEnhanced::init_services();
    
    // Initialize main plugin
    $plugin = new Blue_Motors_Southampton();
    $plugin->run();
    
    // Initialize session management
    BMS_Session::init();
    
    // Phase 3: Initialize enhanced features
    add_action('init', 'blue_motors_phase3_init');
}

/**
 * Phase 3 initialization
 */
function blue_motors_phase3_init() {
    // Load enhanced services configuration
    $enhanced_services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::load_enhanced_services();
    
    // Update services option if needed
    $current_services = get_option('bms_services', array());
    if (count($enhanced_services) > count($current_services)) {
        update_option('bms_services', $enhanced_services);
    }
    
    // Phase 3: Add service excellence notices for admin
    if (is_admin()) {
        add_action('admin_notices', 'blue_motors_phase3_admin_notice');
    }
    
    // Phase 3: Add UK date format setting
    add_option('bms_date_format', 'DD/MM/YYYY');
    add_option('bms_competitive_messaging_enabled', true);
    add_option('bms_mobile_optimized', true);
}
add_action('plugins_loaded', 'blue_motors_southampton_init');

/**
 * Enqueue scripts and styles - Enhanced for Phase 3
 */
function blue_motors_southampton_enqueue_scripts() {
    // Phase 3: UK Date Handler (Priority - Load First)
    wp_enqueue_style(
        'bms-uk-date-styles',
        BMS_PLUGIN_URL . 'assets/css/uk-date-styles.css',
        array(),
        BMS_VERSION,
        'all'
    );
    
    // Shortcode Styles (Essential for proper display)
    wp_enqueue_style(
        'bms-shortcode-styles',
        BMS_PLUGIN_URL . 'assets/css/shortcode-styles.css',
        array(),
        BMS_VERSION,
        'all'
    );
    
    wp_enqueue_script(
        'bms-uk-date-handler',
        BMS_PLUGIN_URL . 'assets/js/uk-date-handler.js',
        array('jquery'),
        BMS_VERSION,
        true
    );
    
    // Phase 3: Mobile Enhancements
    wp_enqueue_style(
        'bms-mobile-enhancements',
        BMS_PLUGIN_URL . 'assets/css/mobile-enhancements.css',
        array('bms-uk-date-styles'),
        BMS_VERSION,
        'all'
    );
    
    // Sprint 1: Mobile Critical Fixes (loaded after mobile-enhancements to override)
    wp_enqueue_style(
        'bms-mobile-critical-fixes',
        BMS_PLUGIN_URL . 'assets/css/mobile-critical-fixes.css',
        array('bms-mobile-enhancements'),
        BMS_VERSION . '.1', // Version bump for cache busting
        'all'
    );
    
    // Phase 2: Mobile Enhancements - Smart Scheduler, Calendar & Loading States
    wp_enqueue_style(
        'bms-mobile-phase2-enhancements',
        BMS_PLUGIN_URL . 'assets/css/mobile-phase2-enhancements.css',
        array('bms-mobile-critical-fixes'),
        BMS_VERSION . '.2', // Version bump for cache busting
        'all'
    );
    
    // Phase 3: Professional Messaging System
    wp_enqueue_style(
        'bms-professional-messaging',
        BMS_PLUGIN_URL . 'assets/css/professional-messaging.css',
        array('bms-mobile-enhancements'),
        BMS_VERSION,
        'all'
    );
    
    wp_enqueue_script(
        'bms-professional-messaging',
        BMS_PLUGIN_URL . 'assets/js/professional-messaging.js',
        array('jquery', 'bms-uk-date-handler'),
        BMS_VERSION,
        true
    );
    
    // Existing: Vehicle lookup styles
    wp_enqueue_style(
        'bms-vehicle-lookup',
        BMS_PLUGIN_URL . 'assets/css/vehicle-lookup.css',
        array('bms-professional-messaging'),
        BMS_VERSION
    );
    
    // Existing: Public styles (now depends on Phase 3 styles)
    wp_enqueue_style(
        'bms-public',
        BMS_PLUGIN_URL . 'assets/css/public.css',
        array('bms-vehicle-lookup'),
        BMS_VERSION
    );
    
    // Phase 2: Tyre search styles
    wp_enqueue_style(
        'bms-tyre-search',
        BMS_PLUGIN_URL . 'assets/css/tyre-search.css',
        array('bms-public'),
        BMS_VERSION
    );
    
    // Phase 4: Booking form enhancements
    wp_enqueue_style(
        'bms-booking-enhancements',
        BMS_PLUGIN_URL . 'assets/css/booking-form-enhancements.css',
        array('bms-tyre-search'),
        BMS_VERSION
    );
    
    // Existing: Vehicle lookup JavaScript
    wp_enqueue_script(
        'bms-vehicle-lookup',
        BMS_PLUGIN_URL . 'assets/js/vehicle-lookup.js',
        array('jquery', 'bms-professional-messaging'),
        BMS_VERSION,
        true
    );
    
    // Phase 2: Tyre booking JavaScript
    wp_enqueue_script(
        'bms-tyre-booking',
        BMS_PLUGIN_URL . 'assets/js/tyre-booking.js',
        array('jquery', 'bms-vehicle-lookup'),
        BMS_VERSION,
        true
    );
    
    // Enhanced Date & Time Picker - Integrated into tyre-booking.js
    // The enhanced calendar functionality is now part of the main tyre-booking system
    // No separate files needed - see assets/js/tyre-booking.js for implementation
    
    // Phase 3: Enhanced payment processing
    wp_enqueue_script(
        'bms-payment-improvements',
        BMS_PLUGIN_URL . 'assets/js/payment-improvements.js',
        array('jquery'),
        BMS_VERSION,
        true
    );
    
    // Existing: Main booking JavaScript
    wp_enqueue_script(
        'bms-booking',
        BMS_PLUGIN_URL . 'assets/js/booking.js',
        array('jquery', 'bms-tyre-booking', 'bms-payment-improvements'),
        BMS_VERSION,
        true
    );
    
    // Phase 4: Booking form enhancements JavaScript
    wp_enqueue_script(
        'bms-booking-enhancements',
        BMS_PLUGIN_URL . 'assets/js/booking-enhancements.js',
        array('jquery', 'bms-booking'),
        BMS_VERSION,
        true
    );
    // Service selection styles
    wp_enqueue_style(
        'bms-service-selection-style',
        BMS_PLUGIN_URL . 'assets/css/service-selection.css',
        array('bms-public'),
        BMS_VERSION
    );
    
    // Service selection script
    wp_enqueue_script(
        'bms-service-selection-script',
        BMS_PLUGIN_URL . 'assets/js/service-selection.js',
        array('jquery', 'bms-booking'),
        BMS_VERSION,
        true
    );

    // Localize scripts with AJAX data
    wp_localize_script(
        'bms-vehicle-lookup',
        'bmsVehicleLookup',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_vehicle_lookup'),
            'adminNonce' => wp_create_nonce('bms_admin_actions'),
            'strings' => array(
                'lookupFailed' => __('Vehicle lookup failed. Please try again.', 'blue-motors-southampton'),
                'invalidRegistration' => __('Please enter a valid UK vehicle registration.', 'blue-motors-southampton'),
                'networkError' => __('Network error. Please check your connection and try again.', 'blue-motors-southampton'),
                'loading' => __('Looking up vehicle...', 'blue-motors-southampton'),
                'motHistoryUnavailable' => __('MOT history is currently unavailable.', 'blue-motors-southampton'),
                'clearConfirm' => __('Are you sure you want to clear the vehicle data?', 'blue-motors-southampton')
            )
        )
    );
    
    // Phase 3: Localize professional messaging
    wp_localize_script(
        'bms-professional-messaging',
        'bmsProfessional',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_competitive'),
            'businessName' => get_option('bms_business_name', 'Blue Motors Southampton'),
            'advantages' => array(
                'tyre_ordering' => __('Order tyres online - F1 requires phone calls!', 'blue-motors-southampton'),
                'uk_dates' => __('UK date format - proper UK formatting!', 'blue-motors-southampton'),
                'payment' => __('Smooth payment - no PayPal issues like F1!', 'blue-motors-southampton'),
                'mobile' => __('Superior mobile experience vs F1!', 'blue-motors-southampton'),
                'local' => __('Southampton specialist vs F1\'s generic chain!', 'blue-motors-southampton')
            ),
            'competitor_issues' => array(
                'tyre_calls' => __('Some providers require phone calls for tyres', 'blue-motors-southampton'),
                'date_format' => __('Some providers use confusing American date format', 'blue-motors-southampton'),
                'payment_issues' => __('F1 has PayPal integration problems', 'blue-motors-southampton'),
                'mobile_basic' => __('Some providers offer basic mobile experience', 'blue-motors-southampton'),
                'cloudflare' => __('Some providers may block legitimate users', 'blue-motors-southampton')
            )
        )
    );
    
    // Phase 3: Enhanced services data
    wp_localize_script(
        'bms-booking',
        'bmsServices',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_booking'),
            'categories' => \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_categories(),
            'services' => \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true),
            'currency' => '£',
            'dateFormat' => 'DD/MM/YYYY', // Phase 3: UK date format
            'strings' => array(
                'selectService' => __('Please select a service', 'blue-motors-southampton'),
                'enterVehicle' => __('Please enter vehicle details', 'blue-motors-southampton'),
                'selectDate' => __('Please select a date and time', 'blue-motors-southampton'),
                'enterDetails' => __('Please enter your contact details', 'blue-motors-southampton'),
                'processingPayment' => __('Processing payment...', 'blue-motors-southampton'),
                'bookingSuccess' => __('Booking confirmed!', 'blue-motors-southampton'),
                'ukDateFormat' => __('Use UK date format: DD/MM/YYYY', 'blue-motors-southampton')
            )
        )
    );
    
    // Phase 3: Enhanced payment data
    wp_localize_script(
        'bms-payment-improvements',
        'bmsPayment',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_payment'),
            'stripePublishableKey' => defined('BM_STRIPE_PUBLISHABLE_KEY') ? BM_STRIPE_PUBLISHABLE_KEY : '',
            'currency' => 'gbp',
            'locale' => 'en-GB',
            'advantages' => array(
                'no_paypal_issues' => __('No PayPal integration issues like F1!', 'blue-motors-southampton'),
                'uk_optimized' => __('UK-optimized payment process', 'blue-motors-southampton'),
                'multiple_options' => __('Multiple secure payment methods', 'blue-motors-southampton'),
                'instant_confirmation' => __('Instant booking confirmation', 'blue-motors-southampton')
            ),
            'strings' => array(
                'paymentProcessing' => __('Processing your payment securely...', 'blue-motors-southampton'),
                'paymentSuccess' => __('Payment successful!', 'blue-motors-southampton'),
                'paymentFailed' => __('Payment failed. Please try again.', 'blue-motors-southampton'),
                'cardDeclined' => __('Card declined. Please try a different payment method.', 'blue-motors-southampton'),
                'networkError' => __('Network error. Please check your connection.', 'blue-motors-southampton')
            )
        )
    );
}
add_action('wp_enqueue_scripts', 'blue_motors_southampton_enqueue_scripts');

/**
 * Enqueue admin scripts and styles
 */
function blue_motors_southampton_admin_enqueue_scripts($hook) {
    // Only enqueue on our admin pages
    if (strpos($hook, 'blue-motors') === false) {
        return;
    }
    
    // Enqueue vehicle lookup styles for admin
    wp_enqueue_style(
        'bms-vehicle-lookup-admin',
        BMS_PLUGIN_URL . 'assets/css/vehicle-lookup.css',
        array(),
        BMS_VERSION
    );
    
    // Enqueue vehicle lookup JavaScript for admin
    wp_enqueue_script(
        'bms-vehicle-lookup-admin',
        BMS_PLUGIN_URL . 'assets/js/vehicle-lookup.js',
        array('jquery'),
        BMS_VERSION,
        true
    );
    
    // Localize script for admin
    wp_localize_script(
        'bms-vehicle-lookup-admin',
        'bmsVehicleLookup',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_vehicle_lookup'),
            'adminNonce' => wp_create_nonce('bms_admin_actions'),
            'isAdmin' => true
        )
    );
}
add_action('admin_enqueue_scripts', 'blue_motors_southampton_admin_enqueue_scripts');

/**
 * Activation hook - Enhanced for database initialization
 */
function blue_motors_southampton_activate() {
    require_once BMS_PLUGIN_DIR . 'includes/class-activator.php';
    Blue_Motors_Southampton_Activator::activate();
    
    // Enhanced: Check database initialization on activation
    bms_check_database_on_activation();
}
register_activation_hook(__FILE__, 'blue_motors_southampton_activate');

/**
 * Check database status on plugin activation
 */
function bms_check_database_on_activation() {
    // Check if enhanced database manager is available
    if (class_exists('BMS_Database_Manager_Enhanced')) {
        $status = BMS_Database_Manager_Enhanced::get_comprehensive_status();
        
        if (!$status['all_exist'] || $status['needs_update']) {
            // Set admin notice to initialize database
            set_transient('bms_database_init_notice', true, 300);
        }
    } else {
        // Enhanced database manager not available - set notice to install it
        set_transient('bms_database_init_notice', true, 300);
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p><strong>Blue Motors:</strong> Enhanced database manager not found. Please check plugin files.</p></div>';
        });
    }
}

/**
 * Deactivation hook
 */
function blue_motors_southampton_deactivate() {
    require_once BMS_PLUGIN_DIR . 'includes/class-deactivator.php';
    Blue_Motors_Southampton_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'blue_motors_southampton_deactivate');

/**
 * Show admin notice if database needs initialization
 */
add_action('admin_notices', 'bms_database_init_notice');

function bms_database_init_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (get_transient('bms_database_init_notice')) {
        $db_url = admin_url('admin.php?page=bms-database-status');
        ?>
        <div class="notice notice-warning is-dismissible">
            <h3>🚀 Blue Motors Southampton: Database Enhancement Available</h3>
            <p>
                <strong>Your database needs initialization with Phase 3+ enhancements:</strong>
            </p>
            <ul>
                <li>🛞 <strong>Complete Tyre Ordering System</strong> - Beat F1's phone-only ordering</li>
                <li>🔧 <strong>Enhanced Services</strong> - Air conditioning, brakes, battery services</li>
                <li>📊 <strong>Customer Service History</strong> - Advanced features F1 doesn't have</li>
                <li>⚡ <strong>Performance Optimizations</strong> - Faster than F1's system</li>
            </ul>
            <p>
                <a href="<?php echo esc_url($db_url); ?>" class="button button-primary button-hero">
                    🎯 Initialize Enhanced Database System
                </a>
                <small style="margin-left: 15px;"><em>Safe upgrade - preserves all existing data</em></small>
            </p>
        </div>
        <?php
        delete_transient('bms_database_init_notice');
    }
}

/**
 * Add admin menu
 */
add_action('admin_menu', function() {
    add_menu_page(
        'Blue Motors Southampton',
        'Blue Motors',
        'manage_options',
        'blue-motors-southampton',
        'blue_motors_southampton_dashboard',
        'dashicons-car',
        30
    );
    
    add_submenu_page(
        'blue-motors-southampton',
        'Bookings',
        'Bookings',
        'manage_options',
        'bms-bookings',
        'blue_motors_southampton_bookings'
    );
    
    add_submenu_page(
        'blue-motors-southampton',
        'Services',
        'Services',
        'manage_options',
        'bms-services',
        'blue_motors_southampton_services'
    );
    
    add_submenu_page(
        'blue-motors-southampton',
        'API Settings',
        'API Settings',
        'manage_options',
        'bms-api-settings',
        'blue_motors_southampton_api_settings'
    );
    
    add_submenu_page(
        'blue-motors-southampton',
        'Email Settings',
        'Email Settings',
        'manage_options',
        'bms-smtp-settings',
        'blue_motors_southampton_smtp_settings'
    );
    
    add_submenu_page(
        'blue-motors-southampton',
        'Settings',
        'Settings',
        'manage_options',
        'bms-settings',
        'blue_motors_southampton_enhanced_settings'
    );
    
    add_submenu_page(
        'blue-motors-southampton',
        'Business Settings',
        'Business Settings',
        'manage_options',
        'bms-business-settings',
        'blue_motors_southampton_business_settings'
    );
    
    add_submenu_page(
        'blue-motors-southampton',
        'Payment Gateway',
        'Payment Gateway',
        'manage_options',
        'bms-payment-settings',
        'blue_motors_southampton_payment_settings'
    );
    
    // Shortcodes Reference - Help users know what shortcodes are available
    add_submenu_page(
        'blue-motors-southampton',
        'Shortcodes Reference',
        '🔖 Shortcodes',
        'manage_options',
        'bms-shortcodes-reference',
        'blue_motors_southampton_shortcodes_reference'
    );
    
    // Shortcode Testing - Test and validate shortcodes
    add_submenu_page(
        'blue-motors-southampton',
        'Shortcode Testing',
        '🧪 Test Shortcodes',
        'manage_options',
        'bms-shortcode-testing',
        'blue_motors_southampton_shortcode_testing'
    );
    
    // Phase 2 Completion: Add Tyre Management menu
    add_submenu_page(
        'blue-motors-southampton',
        'Tyre Management',
        '🛞 Tyre Management',
        'manage_options',
        'bms-tyre-management',
        'blue_motors_southampton_tyre_management'
    );
    
    // Database Status - Enhanced (Always available for proper database management)
    add_submenu_page(
        'blue-motors-southampton',
        'Database Status',
        '💾 Database Status',
        'manage_options',
        'bms-database-status',
        'blue_motors_southampton_enhanced_database_status'
    );
    
    // Phase 3 Testing (only in debug mode)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        add_submenu_page(
            'blue-motors-southampton',
            'Phase 3 Testing',
            '🔧 Phase 3 Tests',
            'manage_options',
            'bms-phase3-tests',
            'blue_motors_southampton_phase3_tests'
        );
        
        add_submenu_page(
            'blue-motors-southampton',
            'Phase 4 Integration Test',
            '🧪 Phase 4 Test',
            'manage_options',
            'bms-phase4-test',
            'blue_motors_southampton_phase4_tests'
        );
    }
});

/**
 * Dashboard page callback
 */
function blue_motors_southampton_dashboard() {
    require_once BMS_PLUGIN_DIR . 'admin/dashboard.php';
}

/**
 * Bookings page callback - Enhanced Phase 3
 */
function blue_motors_southampton_bookings() {
    require_once BMS_PLUGIN_DIR . 'admin/bookings-enhanced.php';
    bms_enhanced_bookings_page();
}

/**
 * Services page callback - Enhanced Phase 3
 */
function blue_motors_southampton_services() {
    require_once BMS_PLUGIN_DIR . 'admin/services-enhanced.php';
    bms_enhanced_services_page();
}

/**
 * API Settings page callback
 */
function blue_motors_southampton_api_settings() {
    require_once BMS_PLUGIN_DIR . 'admin/api-settings.php';
    bms_api_settings_page();
}

/**
 * SMTP Settings page callback
 */
function blue_motors_southampton_smtp_settings() {
    require_once BMS_PLUGIN_DIR . 'admin/smtp-settings.php';
    bms_smtp_settings_page();
}

/**
 * Enhanced Settings page callback (Main settings hub)
 */
function blue_motors_southampton_enhanced_settings() {
    require_once BMS_PLUGIN_DIR . 'admin/enhanced-settings.php';
    bms_enhanced_settings_page();
}

/**
 * Business Settings page callback
 */
function blue_motors_southampton_business_settings() {
    require_once BMS_PLUGIN_DIR . 'admin/business-settings.php';
    bms_business_settings_page();
}

/**
 * Payment Settings page callback
 */
function blue_motors_southampton_payment_settings() {
    require_once BMS_PLUGIN_DIR . 'admin/payment-settings.php';
    bms_payment_settings_page();
}

/**
 * Shortcodes Reference page callback
 */
function blue_motors_southampton_shortcodes_reference() {
    require_once BMS_PLUGIN_DIR . 'admin/shortcodes-reference.php';
    bms_shortcodes_reference_page();
}

/**
 * Shortcode Testing page callback
 */
function blue_motors_southampton_shortcode_testing() {
    require_once BMS_PLUGIN_DIR . 'admin/shortcode-testing.php';
    bms_shortcode_testing_page();
}

/**
 * Tyre Management page callback - Phase 2 Completion
 */
function blue_motors_southampton_tyre_management() {
    require_once BMS_PLUGIN_DIR . 'admin/tyre-management.php';
    bms_tyre_management_page();
}

/**
 * Settings page callback (Legacy - now redirects to enhanced settings)
 */
function blue_motors_southampton_settings() {
    require_once BMS_PLUGIN_DIR . 'admin/enhanced-settings.php';
    bms_enhanced_settings_page();
}

/**
 * Phase 3 Testing page callback (Debug mode only)
 */
function blue_motors_southampton_phase3_tests() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        echo '<div class="wrap"><h1>Phase 3 Testing</h1>';
        echo '<p>Testing is only available in debug mode.</p></div>';
        return;
    }
    
    // Include CSS for better formatting
    echo '<style>
        .bms-test-results { margin: 20px 0; }
        .test-result { padding: 10px; margin: 5px 0; border-radius: 3px; }
        .test-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .test-failure { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .test-result h4 { margin: 0 0 5px 0; }
    </style>';
    
    // Ensure the class is available
    if (!class_exists('BMS_Phase3_Tests')) {
        echo '<div class="wrap"><h1>Phase 3 Testing</h1>';
        echo '<div class="notice notice-error"><p>Phase 3 test class not found. Please check plugin installation.</p></div>';
        echo '</div>';
        return;
    }
    
    try {
        BMS_Phase3_Tests::run_all_tests();
    } catch (Exception $e) {
        echo '<div class="wrap"><h1>Phase 3 Testing</h1>';
        echo '<div class="notice notice-error"><p>Error running tests: ' . esc_html($e->getMessage()) . '</p></div>';
        echo '</div>';
    }
}

/**
 * Enhanced Database Status page callback - Always available for proper management
 */
function blue_motors_southampton_enhanced_database_status() {
    require_once BMS_PLUGIN_DIR . 'admin/database-status.php';
    
    // Use enhanced database status if enhanced manager is available
    if (function_exists('bms_enhanced_database_status_page')) {
        bms_enhanced_database_status_page();
    } else {
        bms_database_status_page();
    }
}

/**
 * Database Status page callback (Legacy - Debug mode only)
 */
function blue_motors_southampton_database_status() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        require_once BMS_PLUGIN_DIR . 'admin/database-status.php';
        bms_database_status_page();
    } else {
        echo '<div class="wrap"><h1>Database Status</h1>';
        echo '<p>Database management is only available in debug mode.</p></div>';
    }
}

/**
 * Phase 4 Testing page callback (Debug mode only)
 */
function blue_motors_southampton_phase4_tests() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        echo '<div class="wrap"><h1>Phase 4 Integration Test</h1>';
        echo '<p>Testing is only available in debug mode.</p></div>';
        return;
    }
    
    // Include CSS for better formatting
    echo '<style>
        .bms-phase4-results { margin: 20px 0; }
        .phase4-test-result { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .phase4-test-success { background: #d4edda; border-left: 4px solid #28a745; }
        .phase4-test-failure { background: #f8d7da; border-left: 4px solid #dc3545; }
        .phase4-test-warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .phase4-test-result h4 { margin: 0 0 10px 0; font-size: 16px; }
        .phase4-test-message { margin: 5px 0; }
    </style>';
    
    // Check if the test function exists
    if (!function_exists('bms_test_phase4_integration')) {
        echo '<div class="wrap"><h1>Phase 4 Integration Test</h1>';
        echo '<div class="notice notice-error"><p>Phase 4 test function not found. Please check plugin installation.</p></div>';
        echo '</div>';
        return;
    }
    
    try {
        bms_test_phase4_integration();
    } catch (Exception $e) {
        echo '<div class="wrap"><h1>Phase 4 Integration Test</h1>';
        echo '<div class="notice notice-error"><p>Error running Phase 4 tests: ' . esc_html($e->getMessage()) . '</p></div>';
        echo '</div>';
    }
}

// Initialize SMTP system
bms_smtp();

/**
 * Phase 3 admin notice - Professional features
 */
function blue_motors_phase3_admin_notice() {
    if (!current_user_can('manage_options')) return;
    
    // Only show on our plugin pages
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'blue-motors') === false) return;
    
    // Check if notice has been dismissed
    if (get_option('bms_phase3_notice_dismissed')) return;
    
    echo '<div class="notice notice-success is-dismissible" id="bms-phase3-notice">';
    echo '<h3>🎉 Phase 3 Complete: Enhanced Features Activated!</h3>';
    echo '<p><strong>New professional features now available:</strong></p>';
    echo '<ul>';
    echo '<li>🇬🇧 <strong>UK Date Format:</strong> DD/MM/YYYY format throughout the system</li>';
    echo '<li>📱 <strong>Mobile Optimized:</strong> Touch-friendly interface for all devices</li>';
    echo '<li>🛞 <strong>Complete Service Range:</strong> Air conditioning, brakes, battery services, and more</li>';
    echo '<li>🎯 <strong>Professional Interface:</strong> Streamlined booking and management system</li>';
    echo '</ul>';
    echo '<p><em>Your Southampton garage now offers an exceptional customer experience!</em></p>';
    echo '<script>jQuery(document).on("click", "#bms-phase3-notice .notice-dismiss", function() {
        jQuery.post(ajaxurl, {action: "bms_dismiss_phase3_notice", nonce: "' . wp_create_nonce('bms_admin') . '"});
    });</script>';
    echo '</div>';
}

/**
 * AJAX handler to dismiss Phase 3 notice
 */
add_action('wp_ajax_bms_dismiss_phase3_notice', function() {
    if (!wp_verify_nonce($_POST['nonce'], 'bms_admin')) wp_die();
    update_option('bms_phase3_notice_dismissed', true);
    wp_die();
});
