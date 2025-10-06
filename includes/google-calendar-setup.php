<?php
/**
 * Google Calendar Setup
 * 
 * Loads and initializes Google Calendar integration
 * 
 * @package BlueMotosSouthampton
 * @since 1.3.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load Google Calendar Service
require_once plugin_dir_path(__FILE__) . 'services/class-google-calendar-service.php';

/**
 * Initialize Google Calendar integration
 */
function bms_init_google_calendar() {
    // Only initialize if we have the required information
    $calendar_info_file = plugin_dir_path(__DIR__) . 'vendor/google-calendar-infor.txt';
    
    if (file_exists($calendar_info_file)) {
        // Create global instance
        $GLOBALS['bms_google_calendar'] = new \BlueMotosSouthampton\Services\GoogleCalendarService();
        
        // Test connection and log result
        if ($GLOBALS['bms_google_calendar']->is_available()) {
            $test_result = $GLOBALS['bms_google_calendar']->test_connection();
            
            if ($test_result['success']) {
                error_log('Google Calendar integration initialized successfully');
            } else {
                error_log('Google Calendar connection test failed: ' . $test_result['message']);
            }
        } else {
            error_log('Google Calendar service not available - check service account credentials');
        }
    } else {
        error_log('Google Calendar information file not found');
    }
}

// Initialize on WordPress init
add_action('init', 'bms_init_google_calendar');

/**
 * Add admin notice about Google Calendar setup requirements
 */
function bms_google_calendar_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $calendar_info_file = plugin_dir_path(__DIR__) . 'vendor/google-calendar-infor.txt';
    
    if (!file_exists($calendar_info_file)) {
        ?>
        <div class="notice notice-warning">
            <p><strong>Blue Motors Southampton:</strong> Google Calendar integration requires setup. Please ensure the google-calendar-infor.txt file exists in the vendor folder.</p>
        </div>
        <?php
        return;
    }
    
    // Check if we have the calendar service available
    if (class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
        $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
        
        if (!$calendar_service->is_available()) {
            ?>
            <div class="notice notice-error">
                <p><strong>Blue Motors Southampton:</strong> Google Calendar service is not properly configured. Please check your service account credentials.</p>
                <p>You need to:</p>
                <ul>
                    <li>Create a Google Cloud Project service account key (JSON format)</li>
                    <li>Update the Google Calendar service class with your private key and other credentials</li>
                    <li>Ensure the service account has access to your calendar</li>
                </ul>
            </div>
            <?php
        }
    }
}

add_action('admin_notices', 'bms_google_calendar_admin_notice');

/**
 * Add Google Calendar test page for admins
 */
function bms_add_calendar_test_page() {
    if (current_user_can('manage_options') && isset($_GET['bms_calendar_test'])) {
        
        echo '<div style="padding: 20px; font-family: monospace; background: #f9f9f9; margin: 20px;">';
        echo '<h2>Blue Motors Southampton - Google Calendar Test</h2>';
        
        $calendar_info_file = plugin_dir_path(__DIR__) . 'vendor/google-calendar-infor.txt';
        
        if (!file_exists($calendar_info_file)) {
            echo '<p style="color: red;">❌ Calendar information file not found</p>';
            return;
        }
        
        echo '<p style="color: green;">✅ Calendar information file found</p>';
        
        // Read calendar info
        $calendar_info = file_get_contents($calendar_info_file);
        echo '<h3>Calendar Information:</h3>';
        echo '<pre>' . esc_html($calendar_info) . '</pre>';
        
        // Test calendar service
        if (class_exists('BlueMotosSouthampton\Services\GoogleCalendarService')) {
            echo '<p style="color: green;">✅ Google Calendar Service class loaded</p>';
            
            $calendar_service = \BlueMotosSouthampton\Services\bms_google_calendar();
            
            if ($calendar_service->is_available()) {
                echo '<p style="color: green;">✅ Calendar service available</p>';
                
                $test_result = $calendar_service->test_connection();
                
                if ($test_result['success']) {
                    echo '<p style="color: green;">✅ Calendar connection successful</p>';
                    echo '<p>Calendar: ' . esc_html($test_result['calendar_name']) . '</p>';
                    echo '<p>Calendar ID: ' . esc_html($test_result['calendar_id']) . '</p>';
                    echo '<p>Timezone: ' . esc_html($test_result['timezone']) . '</p>';
                    echo '<p>Calendar URL: <a href="' . esc_url($calendar_service->get_calendar_url()) . '" target="_blank">View Calendar</a></p>';
                } else {
                    echo '<p style="color: red;">❌ Calendar connection failed</p>';
                    echo '<p>Error: ' . esc_html($test_result['message']) . '</p>';
                }
            } else {
                echo '<p style="color: red;">❌ Calendar service not available</p>';
            }
        } else {
            echo '<p style="color: red;">❌ Google Calendar Service class not found</p>';
        }
        
        echo '</div>';
        exit;
    }
}

add_action('init', 'bms_add_calendar_test_page', 20);