<?php
/**
 * WordPress-Level Calendar Enhancement
 * Blue Motors Southampton
 * 
 * Ensures calendar scripts load properly on WordPress pages and provides
 * additional styling to ensure calendar accessibility.
 * 
 * This works alongside the main calendar implementation in assets/js/tyre-booking.js
 */

// Only run on frontend pages
if (!is_admin()) {
    add_action('wp_footer', 'bms_ensure_calendar_functionality', 999);
}

function bms_ensure_calendar_functionality() {
    // Check if we're on a page that needs calendar functionality
    $need_calendar = false;
    
    if (is_page() || is_single()) {
        global $post;
        if ($post && (
            has_shortcode($post->post_content, 'bms_tyre_search') ||
            has_shortcode($post->post_content, 'bms_tyre_finder') ||
            has_shortcode($post->post_content, 'bms_booking_form')
        )) {
            $need_calendar = true;
        }
    }
    
    // Also check URL for booking/tyre pages
    if (!$need_calendar) {
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($current_url, 'tyre') !== false || 
            strpos($current_url, 'booking') !== false ||
            strpos($current_url, 'test') !== false) {
            $need_calendar = true;
        }
    }
    
    if (!$need_calendar) {
        return;
    }
    
    ?>
    <script>
    console.log('📅 BMS Calendar: WordPress-level enhancements loaded');
    
    // Ensure calendar scripts are loaded
    if (typeof BlueMotosTyreBooking === 'undefined') {
        console.warn('⚠️ BMS Calendar: Main booking class not loaded - calendar may not function');
    } else {
        console.log('✅ BMS Calendar: Main booking system detected');
    }
    
    // Add safety check to ensure date input is clickable
    jQuery(document).ready(function($) {
        const dateInput = $('#fitting-date');
        if (dateInput.length) {
            // Ensure input is accessible
            dateInput.css({
                'cursor': 'pointer',
                'pointer-events': 'auto'
            });
            
            // Remove any readonly attributes that might interfere
            dateInput.removeAttr('readonly');
            
            console.log('✅ BMS Calendar: Date input prepared and accessible');
        }
    });
    </script>
    <style>
    /* Enhanced Calendar Accessibility Styles */
    #fitting-date {
        cursor: pointer !important;
        pointer-events: auto !important;
        -webkit-appearance: none;
        appearance: none;
    }
    
    /* Ensure calendar picker indicator is visible and clickable */
    input[type="date"]::-webkit-calendar-picker-indicator {
        cursor: pointer !important;
        font-size: 18px !important;
        padding: 4px !important;
        opacity: 1 !important;
    }
    
    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        background-color: rgba(59, 130, 246, 0.1) !important;
        border-radius: 4px !important;
    }
    
    /* Ensure the calendar popup is visible */
    #fitting-calendar-popup {
        z-index: 99999 !important;
        position: absolute !important;
        display: none !important;
    }
    
    #fitting-calendar-popup.visible {
        display: block !important;
    }
    
    /* Enhanced date input focus state */
    #fitting-date:focus {
        outline: none !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    /* Ensure calendar stays on top of everything */
    .calendar-container,
    .ui-datepicker {
        z-index: 99999 !important;
    }
    </style>
    <?php
}
?>