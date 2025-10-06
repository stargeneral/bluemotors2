<?php
/**
 * Sticky Calendar Fix - Prevents Calendar from Disappearing
 * Blue Motors Southampton
 * 
 * Prevents the calendar from closing prematurely when user is trying to select a date.
 * Works alongside the main calendar implementation in assets/js/tyre-booking.js
 */

// Only run on frontend pages
if (!is_admin()) {
    add_action('wp_footer', 'bms_sticky_calendar_fix', 1000);
}

function bms_sticky_calendar_fix() {
    // Check if we need the fix
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
    console.log('🔒 BMS Sticky Calendar: Loaded to prevent premature closing');
    
    (function() {
        'use strict';
        
        let calendarIsOpen = false;
        let dateInputElement = null;
        
        function initializeStickyBehavior() {
            dateInputElement = document.getElementById('fitting-date');
            if (!dateInputElement) {
                // Retry if date input not found yet
                setTimeout(initializeStickyBehavior, 1000);
                return;
            }
            
            console.log('🔒 Sticky Calendar: Initializing prevention system');
            
            // Track calendar open state
            dateInputElement.addEventListener('focus', function() {
                calendarIsOpen = true;
                console.log('🔒 Calendar opened');
            });
            
            // Prevent premature blur
            dateInputElement.addEventListener('blur', function(e) {
                if (calendarIsOpen) {
                    // Check if blur is from clicking calendar vs clicking away
                    const relatedTarget = e.relatedTarget;
                    const calendar = document.getElementById('fitting-calendar-popup');
                    
                    // Only prevent blur if calendar is still visible
                    if (calendar && calendar.style.display !== 'none') {
                        e.preventDefault();
                        setTimeout(() => {
                            if (dateInputElement) {
                                dateInputElement.focus();
                            }
                        }, 10);
                        return false;
                    }
                }
            });
            
            // Close calendar when date is selected
            dateInputElement.addEventListener('change', function() {
                calendarIsOpen = false;
                console.log('🔒 Date selected, calendar can close');
            });
            
            // Close calendar when clicking outside
            document.addEventListener('click', function(e) {
                if (!calendarIsOpen) return;
                
                const calendar = document.getElementById('fitting-calendar-popup');
                if (!calendar) return;
                
                // Check if click is on date input or calendar
                const clickedDateInput = e.target === dateInputElement || dateInputElement.contains(e.target);
                const clickedCalendar = e.target === calendar || calendar.contains(e.target);
                
                if (!clickedDateInput && !clickedCalendar) {
                    calendarIsOpen = false;
                    calendar.style.display = 'none';
                    console.log('🔒 Clicked outside, calendar closed');
                }
            });
            
            console.log('✅ Sticky Calendar: Prevention system ready');
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeStickyBehavior);
        } else {
            setTimeout(initializeStickyBehavior, 100);
        }
        
        // Backup initialization attempts
        setTimeout(initializeStickyBehavior, 1000);
        setTimeout(initializeStickyBehavior, 3000);
        
    })();
    </script>
    <style>
    /* Ensure calendar remains visible when intended */
    #fitting-calendar-popup {
        position: absolute !important;
        z-index: 99999 !important;
    }
    
    /* Prevent calendar from being hidden by other elements */
    .ui-datepicker {
        z-index: 99999 !important;
        display: block !important;
    }
    
    /* Style the date input to show it's interactive */
    #fitting-date {
        position: relative !important;
    }
    
    #fitting-date:focus {
        outline: none !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    </style>
    <?php
}
?>