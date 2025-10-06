<?php
/**
 * Direct WordPress Page Calendar Fix - AGGRESSIVE APPROACH
 * 
 * This will be injected directly into WordPress pages to force calendar functionality
 */

// Only run on frontend pages
if (!is_admin()) {
    add_action('wp_footer', 'bms_aggressive_calendar_fix', 999);
}

function bms_aggressive_calendar_fix() {
    // Check if we're on a page that needs the fix
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
    console.log('🚨 AGGRESSIVE CALENDAR FIX: Loading on <?php echo esc_js($current_url); ?>');
    
    // Force calendar fix - AGGRESSIVE APPROACH
    (function() {
        'use strict';
        
        console.log('🔧 Aggressive Calendar Fix: Starting...');
        
        // Wait for page to be fully ready
        function initializeAggressiveFix() {
            console.log('🎯 Initializing aggressive calendar fix...');
            
            // Find the date input
            const dateInput = document.getElementById('fitting-date');
            if (!dateInput) {
                console.warn('⚠️ Date input (fitting-date) not found, will retry...');
                setTimeout(initializeAggressiveFix, 1000);
                return;
            }
            
            console.log('✅ Found date input:', dateInput);
            
            // Remove any existing event listeners
            dateInput.removeAttribute('readonly');
            
            // Force enable the date input
            dateInput.disabled = false;
            dateInput.style.pointerEvents = 'auto';
            dateInput.style.cursor = 'pointer';
            
            // Add click handler for date input
            dateInput.addEventListener('click', function(e) {
                console.log('📅 Date input clicked - forcing calendar...');
                
                // Try multiple methods to show calendar
                try {
                    // Method 1: Native showPicker if available
                    if (typeof dateInput.showPicker === 'function') {
                        console.log('🎯 Using native showPicker...');
                        dateInput.showPicker();
                        return;
                    }
                } catch (error) {
                    console.warn('⚠️ showPicker failed:', error);
                }
                
                // Method 2: Focus and trigger events
                try {
                    console.log('🎯 Using focus/events method...');
                    dateInput.focus();
                    dateInput.click();
                    
                    // Create and dispatch various events
                    const events = ['mousedown', 'mouseup', 'click', 'focus'];
                    events.forEach(eventType => {
                        const event = new Event(eventType, { bubbles: true });
                        dateInput.dispatchEvent(event);
                    });
                } catch (error) {
                    console.warn('⚠️ Focus/events method failed:', error);
                }
                
                // Method 3: jQuery UI Datepicker if available
                if (typeof jQuery !== 'undefined' && jQuery.fn.datepicker) {
                    console.log('🎯 Trying jQuery UI datepicker...');
                    try {
                        jQuery(dateInput).datepicker('show');
                    } catch (error) {
                        console.warn('⚠️ jQuery datepicker failed:', error);
                    }
                }
            });
            
            // Setup time input handling
            const timeInput = document.getElementById('fitting-time');
            if (timeInput) {
                console.log('✅ Found time input:', timeInput);
                
                timeInput.style.cursor = 'pointer';
                timeInput.addEventListener('click', function(e) {
                    console.log('⏰ Time input clicked');
                    
                    if (!dateInput.value) {
                        alert('Please select a date first');
                        return;
                    }
                    
                    showTimeSelector(timeInput, dateInput.value);
                });
            }
            
            // Setup date change handler for time slots
            dateInput.addEventListener('change', function(e) {
                console.log('📅 Date changed to:', e.target.value);
                loadTimeSlots(e.target.value);
            });
            
            console.log('🎉 Aggressive calendar fix initialized successfully!');
        }
        
        // Time selector function
        function showTimeSelector(timeInput, selectedDate) {
            console.log('⏰ Showing time selector for date:', selectedDate);
            
            // Remove any existing time selector
            const existing = document.getElementById('custom-time-selector');
            if (existing) {
                existing.remove();
            }
            
            // Create time options
            const times = [
                '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', 
                '15:00', '15:30', '16:00', '16:30', '17:00'
            ];
            
            // Create dropdown
            const dropdown = document.createElement('select');
            dropdown.id = 'custom-time-selector';
            dropdown.style.cssText = `
                position: absolute;
                z-index: 10000;
                background: white;
                border: 2px solid #1d4ed8;
                border-radius: 8px;
                padding: 10px;
                font-size: 16px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                min-width: 120px;
            `;
            
            // Add default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select time';
            dropdown.appendChild(defaultOption);
            
            // Add time options
            times.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                dropdown.appendChild(option);
            });
            
            // Position dropdown
            const rect = timeInput.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + window.scrollY + 5) + 'px';
            dropdown.style.left = (rect.left + window.scrollX) + 'px';
            
            // Handle selection
            dropdown.addEventListener('change', function() {
                if (this.value) {
                    timeInput.value = this.value;
                    timeInput.dispatchEvent(new Event('change'));
                    document.body.removeChild(this);
                    console.log('✅ Time selected:', this.value);
                }
            });
            
            // Close on outside click
            const closeHandler = function(e) {
                if (!dropdown.contains(e.target) && e.target !== timeInput) {
                    if (dropdown.parentNode) {
                        dropdown.parentNode.removeChild(dropdown);
                    }
                    document.removeEventListener('click', closeHandler);
                }
            };
            
            setTimeout(() => {
                document.addEventListener('click', closeHandler);
            }, 100);
            
            document.body.appendChild(dropdown);
            dropdown.focus();
            
            console.log('✅ Time selector created and displayed');
        }
        
        // Load time slots via AJAX
        function loadTimeSlots(date) {
            console.log('📡 Loading time slots for date:', date);
            
            // Get AJAX settings
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            const nonce = '<?php echo wp_create_nonce('bms_vehicle_lookup'); ?>';
            
            console.log('🌐 AJAX URL:', ajaxUrl);
            
            const timeInput = document.getElementById('fitting-time');
            if (timeInput) {
                timeInput.placeholder = 'Loading times...';
            }
            
            // Make AJAX request
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'bms_get_fitting_slots',
                    nonce: nonce,
                    date: date,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('📡 Time slots response:', data);
                
                if (timeInput) {
                    if (data.success && data.data && data.data.slots && data.data.slots.length > 0) {
                        timeInput.placeholder = 'Click to select time';
                        timeInput.setAttribute('data-slots', JSON.stringify(data.data.slots));
                        console.log('✅ Time slots loaded:', data.data.slots.length);
                    } else {
                        timeInput.placeholder = 'No times available';
                        console.warn('⚠️ No time slots available');
                    }
                }
            })
            .catch(error => {
                console.error('❌ Time slots AJAX error:', error);
                if (timeInput) {
                    timeInput.placeholder = 'Click to select time (using defaults)';
                }
            });
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeAggressiveFix);
        } else {
            // DOM already loaded
            setTimeout(initializeAggressiveFix, 100);
        }
        
        // Also try to initialize after a delay to catch late-loading content
        setTimeout(initializeAggressiveFix, 2000);
        setTimeout(initializeAggressiveFix, 5000);
        
    })();
    
    console.log('🚀 Aggressive Calendar Fix: Script loaded!');
    </script>
    
    <style>
    /* Aggressive CSS fixes */
    #fitting-date {
        cursor: pointer !important;
        pointer-events: auto !important;
        background-color: white !important;
        border: 2px solid #1d4ed8 !important;
        border-radius: 6px !important;
        padding: 12px !important;
        font-size: 16px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    
    #fitting-date:focus {
        outline: none !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    #fitting-time {
        cursor: pointer !important;
        pointer-events: auto !important;
        background-color: white !important;
        border: 2px solid #1d4ed8 !important;
        border-radius: 6px !important;
        padding: 12px !important;
        font-size: 16px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    
    #fitting-time:focus {
        outline: none !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    /* Ensure date input shows calendar */
    input[type="date"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        font-size: 16px;
        padding: 4px;
    }
    </style>
    <?php
}
?>