<?php
/**
 * Tyre Search Shortcode Script Loading Fix
 * 
 * This file fixes the issue where JavaScript and CSS are not loading properly
 * when the [bms_tyre_search] shortcode is used, specifically fixing the calendar functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced script enqueuing function with better dependency management
 */
function bms_enqueue_tyre_search_assets_enhanced() {
    // Only enqueue on pages that actually need it
    if (!bms_page_needs_tyre_assets()) {
        return;
    }
    
    // Enqueue CSS files
    wp_enqueue_style(
        'bms-tyre-search-professional',
        BMS_PLUGIN_URL . 'assets/css/tyre-search-professional.css',
        [],
        BMS_VERSION
    );
    
    wp_enqueue_style(
        'bms-mobile-date-time-picker',
        BMS_PLUGIN_URL . 'assets/css/mobile-date-time-picker.css',
        ['bms-tyre-search-professional'],
        BMS_VERSION
    );
    
    // Enqueue JavaScript files with proper dependencies
    wp_enqueue_script('jquery');
    
    wp_enqueue_script(
        'bms-vehicle-lookup',
        BMS_PLUGIN_URL . 'assets/js/vehicle-lookup.js',
        ['jquery'],
        BMS_VERSION,
        true
    );
    
    wp_enqueue_script(
        'bms-tyre-booking',
        BMS_PLUGIN_URL . 'assets/js/tyre-booking.js',
        ['jquery', 'bms-vehicle-lookup'],
        BMS_VERSION,
        true
    );
    
    wp_enqueue_script(
        'bms-mobile-date-time-picker-fixed',
        BMS_PLUGIN_URL . 'assets/js/mobile-date-time-picker-fixed.js',
        ['bms-tyre-booking'],
        BMS_VERSION,
        true
    );
    
    // Localize scripts with comprehensive data
    wp_localize_script(
        'bms-tyre-booking',
        'bmsTyreBooking',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_vehicle_lookup'),
            'strings' => [
                'searchFailed' => __('Tyre search failed. Please try again.', 'blue-motors-southampton'),
                'invalidRegistration' => __('Please enter a valid UK vehicle registration.', 'blue-motors-southampton'),
                'noTyresFound' => __('No tyres found for your search criteria.', 'blue-motors-southampton'),
                'selectTyre' => __('Please select a tyre to continue.', 'blue-motors-southampton'),
                'bookingFailed' => __('Booking creation failed. Please try again.', 'blue-motors-southampton'),
                'loading' => __('Loading...', 'blue-motors-southampton'),
                'selectDateFirst' => __('Please select a date first.', 'blue-motors-southampton'),
                'noSlotsAvailable' => __('No appointment slots available for this date.', 'blue-motors-southampton')
            ],
            'pricing' => [
                'vatRate' => 0.2,
                'currency' => 'GBP',
                'currencySymbol' => '£'
            ],
            'mobile' => [
                'enabled' => true,
                'popupCalendar' => true,
                'touchOptimized' => true
            ]
        ]
    );
    
    // Also provide bmsVehicleLookup for compatibility
    wp_localize_script(
        'bms-vehicle-lookup',
        'bmsVehicleLookup',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_vehicle_lookup'),
            'strings' => [
                'searchFailed' => __('Search failed. Please try again.', 'blue-motors-southampton'),
                'invalidRegistration' => __('Please enter a valid UK vehicle registration.', 'blue-motors-southampton'),
                'loading' => __('Loading...', 'blue-motors-southampton')
            ]
        ]
    );
}

/**
 * Check if current page needs tyre assets
 */
function bms_page_needs_tyre_assets() {
    global $post;
    
    // Always load on admin pages for testing
    if (is_admin()) {
        return true;
    }
    
    // Check if shortcode is present in post content
    if (isset($post->post_content) && has_shortcode($post->post_content, 'bms_tyre_search')) {
        return true;
    }
    
    // Check if we're currently processing any shortcodes (safer alternative)
    if (did_action('wp_enqueue_scripts') > 0 && is_singular()) {
        // Additional check for shortcode in content when scripts are being loaded
        if (isset($post->post_content) && strpos($post->post_content, '[bms_tyre_search') !== false) {
            return true;
        }
    }
    
    // Load on specific pages that might use the shortcode via widgets or other methods
    $load_on_pages = ['tyre-search', 'tyres', 'booking'];
    if (isset($post->post_name) && in_array($post->post_name, $load_on_pages)) {
        return true;
    }
    
    return false;
}

/**
 * Enhanced tyre search shortcode with guaranteed script loading
 */
function bms_tyre_search_shortcode_enhanced($atts) {
    // Force load assets when shortcode is called
    add_action('wp_footer', 'bms_enqueue_tyre_search_assets_enhanced');
    
    // Parse shortcode attributes
    $atts = shortcode_atts([
        'style' => 'full',
        'competitive_messaging' => 'false',
        'show_popular_sizes' => 'true',
        'default_search_method' => 'registration'
    ], $atts);
    
    // Start output buffering
    ob_start();
    
    // Add wrapper with unique ID
    $wrapper_id = 'bms-tyre-search-' . uniqid();
    echo '<div id="' . esc_attr($wrapper_id) . '" class="bms-tyre-search-wrapper">';
    
    // Add professional messaging if enabled
    if ($atts['competitive_messaging'] === 'true') {
        bms_render_competitive_header();
    }
    
    // Include the tyre search template
    $template_path = BMS_PLUGIN_DIR . 'public/templates/tyre-search.php';
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo '<div class="bms-notice error">Tyre search template not found at: ' . esc_html($template_path) . '</div>';
    }
    
    echo '</div>';
    
    // Add inline script to ensure initialization
    echo '<script type="text/javascript">';
    echo 'document.addEventListener("DOMContentLoaded", function() {';
    echo '    console.log("🛞 Tyre search shortcode loaded for:", "' . esc_js($wrapper_id) . '");';
    echo '    ';
    echo '    // Enhanced initialization with fallback';
    echo '    function initializeTyreBooking() {';
    echo '        if (typeof BlueMotosTyreBooking !== "undefined") {';
    echo '            if (!window.bmsTyreBooking) {';
    echo '                window.bmsTyreBooking = new BlueMotosTyreBooking();';
    echo '                console.log("✅ BlueMotosTyreBooking initialized");';
    echo '            }';
    echo '        } else {';
    echo '            console.warn("⚠️ BlueMotosTyreBooking class not found");';
    echo '        }';
    echo '        ';
    echo '        if (typeof MobileDateTimePickerFixed !== "undefined") {';
    echo '            if (!window.mobileDateTimePicker) {';
    echo '                window.mobileDateTimePicker = new MobileDateTimePickerFixed({';
    echo '                    container: "#' . esc_js($wrapper_id) . '",';
    echo '                    dateInputId: "fitting-date",';
    echo '                    timeSelectId: "fitting-time"';
    echo '                });';
    echo '                console.log("✅ MobileDateTimePicker initialized");';
    echo '            }';
    echo '        } else {';
    echo '            console.warn("⚠️ MobileDateTimePickerFixed class not found");';
    echo '        }';
    echo '    }';
    echo '    ';
    echo '    // Try immediate initialization';
    echo '    initializeTyreBooking();';
    echo '    ';
    echo '    // Fallback initialization after a delay';
    echo '    setTimeout(initializeTyreBooking, 1000);';
    echo '    setTimeout(initializeTyreBooking, 3000);';
    echo '});';
    echo '</script>';
    
    return ob_get_clean();
}

/**
 * Calendar functionality test and fix
 */
function bms_calendar_functionality_fix() {
    // This function can be called to test and fix calendar issues
    ?>
    <script type="text/javascript">
    // Calendar fix script
    (function() {
        console.log('📅 Calendar functionality fix loaded');
        
        function fixCalendarIssues() {
            // Find date and time inputs
            const dateInput = document.getElementById('fitting-date');
            const timeInput = document.getElementById('fitting-time');
            
            if (dateInput) {
                console.log('✅ Date input found');
                
                // Ensure click handler
                dateInput.addEventListener('click', function(e) {
                    console.log('📅 Date input clicked');
                    
                    if (window.mobileDateTimePicker && typeof window.mobileDateTimePicker.showDatePicker === 'function') {
                        e.preventDefault();
                        window.mobileDateTimePicker.showDatePicker();
                    } else {
                        console.warn('⚠️ Mobile date picker not available, falling back to native');
                        // Remove readonly to allow native picker
                        dateInput.removeAttribute('readonly');
                    }
                });
                
                // Setup date change handler
                dateInput.addEventListener('change', function() {
                    console.log('📅 Date changed to:', this.value);
                    
                    // Trigger time slot loading
                    if (window.bmsTyreBooking && typeof window.bmsTyreBooking.loadFittingSlots === 'function') {
                        window.bmsTyreBooking.loadFittingSlots(this.value);
                    }
                });
            }
            
            if (timeInput) {
                console.log('✅ Time input found');
                
                timeInput.addEventListener('click', function(e) {
                    console.log('⏰ Time input clicked');
                    
                    const dateValue = dateInput ? dateInput.value : null;
                    if (!dateValue) {
                        alert('Please select a date first');
                        return;
                    }
                    
                    if (window.mobileDateTimePicker && typeof window.mobileDateTimePicker.showTimePicker === 'function') {
                        e.preventDefault();
                        window.mobileDateTimePicker.showTimePicker();
                    } else {
                        console.warn('⚠️ Mobile time picker not available');
                    }
                });
            }
        }
        
        // Apply fixes when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fixCalendarIssues);
        } else {
            fixCalendarIssues();
        }
        
        // Also apply fixes after a delay to catch dynamic content
        setTimeout(fixCalendarIssues, 2000);
        
        // Initialize the fixed mobile picker if available
        if (typeof MobileDateTimePickerFixed !== 'undefined') {
            if (!window.mobileDateTimePicker) {
                window.mobileDateTimePicker = new MobileDateTimePickerFixed();
                console.log('🔧 Fixed mobile picker initialized from calendar fix');
            }
        }
    })();
    </script>
    <?php
}

// Hook the enhanced functions
add_shortcode('bms_tyre_search_enhanced', 'bms_tyre_search_shortcode_enhanced');

// Replace the original shortcode temporarily for testing
remove_shortcode('bms_tyre_search');
add_shortcode('bms_tyre_search', 'bms_tyre_search_shortcode_enhanced');

// Add the calendar fix script to footer
add_action('wp_footer', 'bms_calendar_functionality_fix');

/**
 * Force load tyre assets on any page that might need them
 */
function bms_force_load_tyre_assets() {
    // Load on frontend pages
    if (!is_admin()) {
        bms_enqueue_tyre_search_assets_enhanced();
    }
}
add_action('wp_enqueue_scripts', 'bms_force_load_tyre_assets');

/**
 * Debug information for troubleshooting
 */
function bms_tyre_debug_info() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo "\n<!-- BMS Tyre Debug Info -->\n";
        echo "<!-- Plugin URL: " . BMS_PLUGIN_URL . " -->\n";
        echo "<!-- Assets loaded: " . (wp_script_is('bms-tyre-booking', 'enqueued') ? 'YES' : 'NO') . " -->\n";
        echo "<!-- Mobile picker loaded: " . (wp_script_is('bms-mobile-date-time-picker', 'enqueued') ? 'YES' : 'NO') . " -->\n";
        echo "<!-- Template path: " . BMS_PLUGIN_DIR . 'public/templates/tyre-search.php' . " -->\n";
        echo "<!-- Template exists: " . (file_exists(BMS_PLUGIN_DIR . 'public/templates/tyre-search.php') ? 'YES' : 'NO') . " -->\n";
        echo "<!-- AJAX URL: " . admin_url('admin-ajax.php') . " -->\n";
        echo "<!-- Nonce: " . wp_create_nonce('bms_vehicle_lookup') . " -->\n";
        echo "<!-- End BMS Tyre Debug Info -->\n";
    }
}
add_action('wp_footer', 'bms_tyre_debug_info');
