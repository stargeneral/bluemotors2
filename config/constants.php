<?php
/**
 * Blue Motors Southampton - Configuration Constants
 * 
 * IMPORTANT: Sensitive keys should be stored in wp-config.php or .env file
 * This file contains default/fallback values only
 * 
 * @package Blue_Motors_Southampton
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin Information
define('BM_VERSION', '1.4.0');
define('BM_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));
define('BM_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));

// Database Configuration
define('BM_TABLE_PREFIX', 'bms_');
define('BM_APPOINTMENTS_TABLE', 'bms_appointments');
define('BM_SERVICES_TABLE', 'bms_services');
define('BM_TYRE_BOOKINGS_TABLE', 'bms_tyre_bookings');
define('BM_TYRES_TABLE', 'bms_tyres');
define('BM_VEHICLE_TYRES_TABLE', 'bms_vehicle_tyres');
define('BM_BOOKING_LOGS_TABLE', 'bms_booking_logs');
define('BM_BOOKING_META_TABLE', 'bms_booking_meta');
define('BM_TYRE_BOOKING_META_TABLE', 'bms_tyre_booking_meta');

// Business Configuration - Southampton Location
define('BM_BUSINESS_NAME', 'Blue Motors Southampton');
define('BM_BUSINESS_ADDRESS', '1 Kent St, Northam, Southampton SO14 5SP');
define('BM_BUSINESS_PHONE', '023 8000 0000');
define('BM_BUSINESS_EMAIL', 'southampton@bluemotors.co.uk');

// Operating Hours
define('BM_OPENING_TIME', '08:00');
define('BM_CLOSING_TIME', '18:00');
define('BM_SATURDAY_OPENING', '08:00');
define('BM_SATURDAY_CLOSING', '16:00');
define('BM_CLOSED_DAYS', serialize(['Sunday']));


// Payment Configuration
define('BM_PAYMENT_REQUIRED', true); // Require payment at booking
define('BM_PAYMENT_CURRENCY', 'GBP');
define('BM_VAT_RATE', 0.20); // 20% UK VAT rate

// Stripe Configuration
// SECURITY: These should be defined in wp-config.php instead!
// Example for wp-config.php:
// define('BM_STRIPE_PUBLISHABLE_KEY', 'your_publishable_key_here');
// define('BM_STRIPE_SECRET_KEY', 'your_secret_key_here');
if (!defined('BM_STRIPE_PUBLISHABLE_KEY')) {
    define('BM_STRIPE_PUBLISHABLE_KEY', ''); // Set in wp-config.php
}
if (!defined('BM_STRIPE_SECRET_KEY')) {
    define('BM_STRIPE_SECRET_KEY', ''); // Set in wp-config.php
}
define('BM_STRIPE_ENABLED', true);

// DVLA API Configuration
// SECURITY: API key should be defined in wp-config.php instead!
// Example for wp-config.php:
// define('BM_DVLA_API_KEY', 'your_api_key_here');
define('BM_DVLA_API_ENABLED', true);
if (!defined('BM_DVLA_API_KEY')) {
    define('BM_DVLA_API_KEY', ''); // Set in wp-config.php
}
define('BM_DVLA_API_URL', 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles');

// Email Configuration
define('BM_EMAIL_FROM_NAME', 'Blue Motors Southampton');
define('BM_EMAIL_FROM_ADDRESS', 'admin@bluemotorsgarage.com'); // UPDATE THIS TO YOUR REAL EMAIL
define('BM_EMAIL_ADMIN_NOTIFY', 'blue-motors@hotmail.com'); // UPDATE THIS TO YOUR REAL ADMIN EMAIL
