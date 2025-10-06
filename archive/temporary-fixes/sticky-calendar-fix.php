<?php
/**
 * Sticky Calendar Fix - Prevents calendar from disappearing
 * 
 * Fixes the issue where calendar shows for 1 second then disappears
 */

// Only run on frontend pages
if (!is_admin()) {
    add_action('wp_footer', 'bms_sticky_calendar_fix', 1000); // Very late priority
}

function bms_sticky_calendar_fix() {
    // Check if we need the fix
    $current_url = $_SERVER['REQUEST_URI'] ?? '';
    $need_fix = (
        strpos($current_url, 'testpage2') !== false ||
        strpos($current_url, 'tyre') !== false ||
        is_page('testpage2') ||
        (is_page() && has_shortcode(get_post()->post_content, 'bms_tyre_search'))
    );
    
    if (!$need_fix) {
        return;
    }
    
    ?>
    <script>
    console.log('🔒 STICKY CALENDAR FIX: Loading to prevent calendar disappearing...');
    
    (function() {
        'use strict';
        
        let calendarStayOpen = false;
        let dateInputElement = null;
        
        function initializeStickyFix() {
            console.log('🔒 Initializing sticky calendar fix...');
            
            dateInputElement = document.getElementById('fitting-date');
            if (!dateInputElement) {
                console.warn('⚠️ Date input not found, retrying...');
                setTimeout(initializeStickyFix, 1000);
                return;
            }
            
            console.log('✅ Date input found for sticky fix');
            
            // Method 1: Prevent blur events that might close the calendar
            dateInputElement.addEventListener('blur', function(e) {
                if (calendarStayOpen) {
                    console.log('🔒 Preventing calendar close via blur event');
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    // Re-focus to keep calendar open
                    setTimeout(() => {
                        dateInputElement.focus();
                    }, 10);
                    
                    return false;
                }
            }, true);
            
            // Method 2: Prevent mouseout events that might close calendar
            dateInputElement.addEventListener('mouseout', function(e) {
                if (calendarStayOpen) {
                    console.log('🔒 Preventing calendar close via mouseout');
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
            
            // Method 3: Prevent any focusout events
            dateInputElement.addEventListener('focusout', function(e) {
                if (calendarStayOpen) {
                    console.log('🔒 Preventing calendar close via focusout');
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);
            
            // Method 4: Enhanced click handler that keeps calendar open
            dateInputElement.addEventListener('click', function(e) {
                console.log('📅 Date input clicked - enabling sticky mode');
                
                calendarStayOpen = true;
                
                // Prevent any immediate close events
                e.preventDefault();
                e.stopPropagation();
                
                // Show calendar using multiple methods
                setTimeout(() => {
                    showStickyCalendar();
                }, 50);
                
                // Auto-disable sticky mode after 30 seconds
                setTimeout(() => {
                    calendarStayOpen = false;
                    console.log('🔒 Sticky mode auto-disabled after 30s');
                }, 30000);
                
                return false;
            }, true);
            
            // Method 5: Listen for calendar changes to disable sticky mode
            dateInputElement.addEventListener('change', function(e) {
                console.log('📅 Date selected:', e.target.value);
                calendarStayOpen = false;
                console.log('🔒 Sticky mode disabled - date selected');
            });
            
            // Method 6: Global click handler to close calendar when clicking outside
            document.addEventListener('click', function(e) {
                if (calendarStayOpen && !dateInputElement.contains(e.target)) {
                    // Check if click is on calendar element
                    const isCalendarClick = (
                        e.target.closest('.ui-datepicker') ||
                        e.target.closest('[data-handler]') ||
                        e.target.classList.contains('ui-state-default') ||
                        e.target.parentElement?.classList.contains('ui-datepicker-calendar')
                    );
                    
                    if (!isCalendarClick) {
                        console.log('🔒 Clicked outside - disabling sticky mode');
                        calendarStayOpen = false;
                    }
                }
            });
            
            console.log('✅ Sticky calendar fix initialized');
        }
        
        function showStickyCalendar() {
            console.log('📅 Showing sticky calendar...');
            
            // Method 1: Native showPicker with error handling
            try {
                if (typeof dateInputElement.showPicker === 'function') {
                    console.log('🎯 Using native showPicker...');
                    dateInputElement.showPicker();
                    
                    // Keep focus to prevent disappearing
                    setTimeout(() => {
                        if (calendarStayOpen) {
                            dateInputElement.focus();
                        }
                    }, 100);
                    
                    return;
                }
            } catch (error) {
                console.warn('⚠️ Native showPicker failed:', error);
            }
            
            // Method 2: jQuery UI datepicker if available
            if (typeof jQuery !== 'undefined' && jQuery.fn.datepicker) {
                console.log('🎯 Using jQuery UI datepicker...');
                try {
                    const $input = jQuery(dateInputElement);
                    
                    // Initialize datepicker if not already done
                    if (!$input.hasClass('hasDatepicker')) {
                        $input.datepicker({
                            dateFormat: 'yy-mm-dd',
                            minDate: '+2d',
                            maxDate: '+30d',
                            showOtherMonths: true,
                            selectOtherMonths: true,
                            changeMonth: true,
                            changeYear: true,
                            beforeShow: function() {
                                console.log('📅 jQuery datepicker about to show');
                                return true;
                            },
                            onSelect: function(dateText) {
                                console.log('📅 Date selected from jQuery datepicker:', dateText);
                                calendarStayOpen = false;
                            },
                            onClose: function() {
                                console.log('📅 jQuery datepicker closed');
                                calendarStayOpen = false;
                            }
                        });
                    }
                    
                    // Show the datepicker
                    $input.datepicker('show');
                    
                    // Prevent it from closing immediately
                    setTimeout(() => {
                        if (calendarStayOpen) {
                            $input.focus();
                        }
                    }, 100);
                    
                    return;
                } catch (error) {
                    console.warn('⚠️ jQuery datepicker failed:', error);
                }
            }
            
            // Method 3: Force focus and trigger events
            console.log('🎯 Using focus/events method...');
            dateInputElement.focus();
            
            // Trigger multiple events to show calendar
            const events = ['mousedown', 'click', 'focus'];
            events.forEach(eventType => {
                const event = new MouseEvent(eventType, {
                    bubbles: true,
                    cancelable: true,
                    view: window
                });
                dateInputElement.dispatchEvent(event);
            });
            
            // Keep focusing to prevent disappearing
            let focusAttempts = 0;
            const keepFocused = () => {
                if (calendarStayOpen && focusAttempts < 10) {
                    dateInputElement.focus();
                    focusAttempts++;
                    setTimeout(keepFocused, 200);
                }
            };
            setTimeout(keepFocused, 100);
        }
        
        // Initialize when ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeStickyFix);
        } else {
            setTimeout(initializeStickyFix, 100);
        }
        
        // Multiple initialization attempts
        setTimeout(initializeStickyFix, 1000);
        setTimeout(initializeStickyFix, 3000);
        
        console.log('🔒 Sticky calendar fix script loaded');
        
    })();
    </script>
    
    <style>
    /* Sticky calendar CSS fixes */
    #fitting-date:focus {
        outline: none !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    /* Ensure jQuery UI datepicker stays visible */
    .ui-datepicker {
        z-index: 99999 !important;
        position: absolute !important;
        display: block !important;
    }
    
    /* Prevent calendar from being hidden by other elements */
    .ui-datepicker-calendar {
        display: table !important;
    }
    
    /* Style the date input to show it's interactive */
    #fitting-date {
        position: relative !important;
    }
    
    /* Ensure calendar picker indicator is visible and clickable */
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 1 !important;
        cursor: pointer !important;
        width: 20px !important;
        height: 20px !important;
        padding: 2px !important;
        background-size: 16px !important;
    }
    
    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        background-color: rgba(59, 130, 246, 0.1) !important;
        border-radius: 4px !important;
    }
    </style>
    <?php
}
?>