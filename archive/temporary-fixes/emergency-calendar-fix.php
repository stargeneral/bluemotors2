<?php
/**
 * Emergency Calendar Fix - Direct Implementation
 * 
 * This bypasses the WordPress integration and directly fixes the calendar on testpage2
 */

// Add this to your theme's functions.php or create a simple plugin

add_action('wp_footer', 'bms_emergency_calendar_fix');

function bms_emergency_calendar_fix() {
    // Only run on testpage2 or any page that might have the tyre booking form
    global $post;
    
    $should_load = false;
    
    // Check for various conditions that indicate this is a tyre booking page
    if ($post) {
        $should_load = (
            strpos(strtolower($post->post_name), 'testpage') !== false ||
            strpos(strtolower($post->post_title), 'tyre') !== false ||
            strpos(strtolower($post->post_content), 'fitting-date') !== false ||
            strpos(strtolower($post->post_content), 'tyre') !== false
        );
    }
    
    // Also check current URL
    if (!$should_load) {
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        $should_load = (
            strpos($current_url, 'testpage') !== false ||
            strpos($current_url, 'tyre') !== false
        );
    }
    
    if (!$should_load) {
        return; // Don't load on unrelated pages
    }
    
    ?>
    <script>
    console.log('🚨 BMS Emergency Calendar Fix Loading...');
    
    jQuery(document).ready(function($) {
        // Wait a bit for other scripts to load
        setTimeout(function() {
            console.log('🔍 Looking for date fields...');
            
            // Try multiple selectors to find the date input
            var dateSelectors = [
                '#fitting-date',
                'input[name="fitting-date"]',
                'input[name*="date"]',
                'input[id*="date"]',
                '.date-picker',
                'input[type="date"]'
            ];
            
            var $dateInput = null;
            var foundSelector = '';
            
            dateSelectors.forEach(function(selector) {
                if (!$dateInput || $dateInput.length === 0) {
                    var $found = $(selector);
                    if ($found.length > 0) {
                        $dateInput = $found.first();
                        foundSelector = selector;
                        console.log('✅ Found date field with selector:', selector, $dateInput[0]);
                    }
                }
            });
            
            if (!$dateInput || $dateInput.length === 0) {
                console.error('❌ No date input field found');
                return;
            }
            
            console.log('🎯 Targeting date field:', foundSelector);
            
            // Function to initialize the calendar
            function initEmergencyCalendar() {
                console.log('📅 Initializing emergency calendar...');
                
                // Convert to text input and make readonly
                $dateInput.attr('type', 'text');
                $dateInput.attr('readonly', 'readonly');
                $dateInput.attr('placeholder', 'Click to select date');
                $dateInput.css('cursor', 'pointer');
                
                // Remove any existing datepicker
                if ($dateInput.hasClass('hasDatepicker')) {
                    $dateInput.datepicker('destroy');
                    console.log('🔄 Removed existing datepicker');
                }
                
                try {
                    $dateInput.datepicker({
                        dateFormat: 'DD, dd MM yy',
                        altFormat: 'yy-mm-dd',
                        minDate: 2,
                        maxDate: 30,
                        changeMonth: true,
                        changeYear: true,
                        showAnim: 'slideDown',
                        showButtonPanel: true,
                        beforeShow: function(input, inst) {
                            console.log('📅 Calendar about to show');
                            setTimeout(function() {
                                if (inst.dpDiv) {
                                    inst.dpDiv.css({
                                        'z-index': 9999,
                                        'box-shadow': '0 5px 15px rgba(0,0,0,0.3)',
                                        'border': '1px solid #ccc',
                                        'background': 'white'
                                    });
                                    
                                    // Add custom styling
                                    inst.dpDiv.find('.ui-datepicker-header').css({
                                        'background': '#1d4ed8',
                                        'color': 'white'
                                    });
                                    
                                    inst.dpDiv.find('.ui-state-active').css({
                                        'background': '#1d4ed8',
                                        'color': 'white'
                                    });
                                }
                            }, 0);
                        },
                        onSelect: function(dateText, inst) {
                            var selectedDate = $(this).datepicker('getDate');
                            var isoDate = selectedDate.toISOString().split('T')[0];
                            
                            console.log('✅ Date selected:', dateText, '(ISO:', isoDate + ')');
                            
                            // Store the ISO format for potential AJAX calls
                            $(this).data('selected-date', isoDate);
                            
                            // Try to load time slots
                            loadTimeSlots(isoDate);
                            
                            // Show success notification
                            showNotification('✅ Date selected: ' + dateText, 'success');
                        }
                    });
                    
                    console.log('✅ Emergency calendar initialized successfully!');
                    showNotification('📅 Calendar is ready! Click the date field to select a date.', 'info');
                    
                } catch (error) {
                    console.error('❌ Calendar initialization failed:', error);
                    showNotification('❌ Calendar initialization failed: ' + error.message, 'error');
                }
            }
            
            // Function to load time slots
            function loadTimeSlots(date) {
                console.log('⏰ Loading time slots for:', date);
                
                var $timeSelect = $('#fitting-time, select[name*="time"], .time-select');
                
                if ($timeSelect.length > 0) {
                    console.log('📍 Found time select element');
                    $timeSelect.html('<option value="">Loading times...</option>');
                    
                    // Try AJAX call first
                    if (typeof bmsVehicleLookup !== 'undefined' && bmsVehicleLookup.ajaxUrl) {
                        $.ajax({
                            url: bmsVehicleLookup.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'bms_get_fitting_slots',
                                date: date,
                                quantity: 1,
                                nonce: bmsVehicleLookup.nonce
                            },
                            success: function(response) {
                                console.log('📡 AJAX response:', response);
                                
                                if (response.success && response.data && response.data.slots) {
                                    $timeSelect.html('<option value="">Choose a time</option>');
                                    
                                    response.data.slots.forEach(function(slot) {
                                        var formattedTime = formatTime(slot);
                                        $timeSelect.append('<option value="' + slot + '">' + formattedTime + '</option>');
                                    });
                                    
                                    console.log('✅ Time slots loaded from server:', response.data.slots.length);
                                    showNotification('⏰ ' + response.data.slots.length + ' appointment times loaded', 'success');
                                } else {
                                    loadDemoTimeSlots();
                                }
                            },
                            error: function() {
                                console.warn('⚠️ AJAX failed, loading demo time slots');
                                loadDemoTimeSlots();
                            }
                        });
                    } else {
                        console.warn('⚠️ No AJAX configuration, loading demo time slots');
                        loadDemoTimeSlots();
                    }
                    
                    function loadDemoTimeSlots() {
                        var demoTimes = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'];
                        
                        $timeSelect.html('<option value="">Choose a time</option>');
                        demoTimes.forEach(function(time) {
                            var formattedTime = formatTime(time);
                            $timeSelect.append('<option value="' + time + '">' + formattedTime + '</option>');
                        });
                        
                        console.log('✅ Demo time slots loaded:', demoTimes.length);
                        showNotification('⏰ ' + demoTimes.length + ' demo appointment times loaded', 'info');
                    }
                } else {
                    console.warn('⚠️ Time select element not found');
                }
            }
            
            // Format time for display
            function formatTime(timeString) {
                try {
                    var time = new Date('2000-01-01 ' + timeString);
                    return time.toLocaleTimeString('en-GB', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                } catch (error) {
                    return timeString;
                }
            }
            
            // Show notification function
            function showNotification(message, type) {
                type = type || 'info';
                
                // Remove existing notifications
                $('.bms-emergency-notification').remove();
                
                var notification = $('<div class="bms-emergency-notification">')
                    .css({
                        'position': 'fixed',
                        'top': '20px',
                        'right': '20px',
                        'background': type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1',
                        'color': type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460',
                        'border': '1px solid ' + (type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#b6d4de'),
                        'padding': '15px',
                        'border-radius': '5px',
                        'max-width': '300px',
                        'z-index': 10000,
                        'box-shadow': '0 2px 10px rgba(0,0,0,0.1)',
                        'font-family': 'Arial, sans-serif',
                        'font-size': '14px'
                    })
                    .html(message + '<button style="float: right; background: none; border: none; font-size: 18px; cursor: pointer; margin-left: 10px;" onclick="$(this).parent().remove()">×</button>');
                
                $('body').append(notification);
                
                // Auto-remove after 5 seconds
                setTimeout(function() {
                    notification.fadeOut();
                }, 5000);
            }
            
            // Check if jQuery UI is loaded
            if (typeof $.fn.datepicker === 'undefined') {
                console.log('📦 jQuery UI not found, loading...');
                
                // Load jQuery UI CSS
                var cssLink = document.createElement('link');
                cssLink.rel = 'stylesheet';
                cssLink.href = 'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css';
                document.head.appendChild(cssLink);
                
                // Load jQuery UI JS
                var script = document.createElement('script');
                script.src = 'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js';
                script.onload = function() {
                    console.log('✅ jQuery UI loaded successfully');
                    setTimeout(initEmergencyCalendar, 500);
                };
                script.onerror = function() {
                    console.error('❌ Failed to load jQuery UI');
                    showNotification('❌ Failed to load calendar library', 'error');
                };
                document.head.appendChild(script);
            } else {
                console.log('✅ jQuery UI already available');
                initEmergencyCalendar();
            }
            
        }, 1500); // Wait 1.5 seconds for page to fully load
    });
    
    // Global test function
    window.testEmergencyCalendar = function() {
        console.log('🧪 Emergency Calendar Test');
        console.log('Date field exists:', !!jQuery('#fitting-date, input[name*="date"]').length);
        console.log('jQuery available:', typeof jQuery !== 'undefined');
        console.log('jQuery UI available:', typeof jQuery.ui !== 'undefined');
        
        var $dateField = jQuery('#fitting-date, input[name*="date"]').first();
        if ($dateField.length > 0) {
            console.log('Date field info:', {
                id: $dateField.attr('id'),
                name: $dateField.attr('name'),
                type: $dateField.attr('type'),
                hasDatepicker: $dateField.hasClass('hasDatepicker')
            });
            
            if ($dateField.hasClass('hasDatepicker')) {
                console.log('✅ Datepicker is attached - try clicking the field!');
                return 'Calendar should work - try clicking the date field!';
            } else {
                console.log('❌ Datepicker not attached');
                return 'Calendar not initialized - check console for errors';
            }
        } else {
            console.log('❌ Date field not found');
            return 'Date field not found on this page';
        }
    };
    </script>
    
    <style>
    /* Emergency calendar styling */
    .ui-datepicker {
        z-index: 9999 !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important;
        border: 1px solid #ccc !important;
        font-family: Arial, sans-serif !important;
    }
    
    .ui-datepicker .ui-datepicker-header {
        background: #1d4ed8 !important;
        color: white !important;
    }
    
    .ui-datepicker .ui-state-active,
    .ui-datepicker .ui-state-highlight {
        background: #1d4ed8 !important;
        color: white !important;
    }
    
    .ui-datepicker .ui-state-hover {
        background: #eff6ff !important;
        border-color: #bfdbfe !important;
    }
    </style>
    <?php
}
