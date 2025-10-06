<?php
/**
 * Nuclear Calendar Fix - Complete takeover approach
 * 
 * When all else fails, completely replace the date input behavior
 */

// Only run on frontend pages
if (!is_admin()) {
    add_action('wp_footer', 'bms_nuclear_calendar_fix', 1001); // Highest priority
}

function bms_nuclear_calendar_fix() {
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
    console.log('☢️ NUCLEAR CALENDAR FIX: Complete takeover mode activated');
    
    (function() {
        'use strict';
        
        let nuclearCalendarActive = false;
        let datePickerOverlay = null;
        let selectedDate = null;
        
        function initializeNuclearFix() {
            console.log('☢️ Initializing nuclear calendar fix...');
            
            const dateInput = document.getElementById('fitting-date');
            if (!dateInput) {
                console.warn('⚠️ Date input not found, retrying...');
                setTimeout(initializeNuclearFix, 1000);
                return;
            }
            
            console.log('✅ Date input found - applying nuclear takeover');
            
            // NUCLEAR OPTION 1: Completely replace click behavior
            dateInput.addEventListener('click', function(e) {
                console.log('☢️ Date input clicked - NUCLEAR OVERRIDE');
                
                // Stop all normal behavior
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Show our custom calendar
                showNuclearCalendar();
                
                return false;
            }, true); // Capture phase
            
            // NUCLEAR OPTION 2: Intercept focus events
            dateInput.addEventListener('focus', function(e) {
                console.log('☢️ Date input focused - preventing normal calendar');
                if (!nuclearCalendarActive) {
                    e.preventDefault();
                    showNuclearCalendar();
                }
            }, true);
            
            // NUCLEAR OPTION 3: Block all other events
            ['mousedown', 'mouseup', 'keydown', 'keyup'].forEach(eventType => {
                dateInput.addEventListener(eventType, function(e) {
                    if (eventType === 'mousedown' && !nuclearCalendarActive) {
                        console.log('☢️ Blocking ' + eventType + ' - showing nuclear calendar');
                        e.preventDefault();
                        e.stopPropagation();
                        showNuclearCalendar();
                        return false;
                    }
                }, true);
            });
            
            console.log('☢️ Nuclear calendar takeover complete');
        }
        
        function showNuclearCalendar() {
            console.log('📅 Showing nuclear calendar overlay...');
            
            if (nuclearCalendarActive) {
                console.log('📅 Nuclear calendar already active');
                return;
            }
            
            nuclearCalendarActive = true;
            
            // Remove any existing overlay
            if (datePickerOverlay) {
                document.body.removeChild(datePickerOverlay);
            }
            
            // Create overlay
            datePickerOverlay = document.createElement('div');
            datePickerOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999999;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            
            // Create calendar container
            const calendarContainer = document.createElement('div');
            calendarContainer.style.cssText = `
                background: white;
                border-radius: 12px;
                padding: 30px;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
                max-width: 90vw;
                max-height: 90vh;
                overflow: auto;
            `;
            
            calendarContainer.innerHTML = createCalendarHTML();
            datePickerOverlay.appendChild(calendarContainer);
            
            // Add to page
            document.body.appendChild(datePickerOverlay);
            
            // Setup calendar functionality
            setupCalendarControls();
            
            // Close on overlay click
            datePickerOverlay.addEventListener('click', function(e) {
                if (e.target === datePickerOverlay) {
                    closeNuclearCalendar();
                }
            });
            
            console.log('✅ Nuclear calendar overlay created and shown');
        }
        
        function createCalendarHTML() {
            const today = new Date();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();
            
            // Generate next 2 months (current + next)
            let calendarHTML = `
                <div style="text-align: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; color: #1e40af;">📅 Select Appointment Date</h3>
                    <p style="margin: 10px 0; color: #6b7280;">Choose a date 2-30 days from today</p>
                </div>
            `;
            
            for (let monthOffset = 0; monthOffset < 2; monthOffset++) {
                const date = new Date(currentYear, currentMonth + monthOffset, 1);
                const month = date.getMonth();
                const year = date.getFullYear();
                const monthName = date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
                
                calendarHTML += `
                    <div style="margin-bottom: 30px;">
                        <h4 style="text-align: center; margin: 0 0 15px 0; color: #374151;">${monthName}</h4>
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; max-width: 350px; margin: 0 auto;">
                `;
                
                // Add day headers
                ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
                    calendarHTML += `<div style="text-align: center; padding: 8px; font-weight: bold; color: #6b7280;">${day}</div>`;
                });
                
                // Get first day of month and number of days
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const minDate = new Date(today);
                minDate.setDate(today.getDate() + 2);
                const maxDate = new Date(today);
                maxDate.setDate(today.getDate() + 30);
                
                // Add empty cells for days before month starts
                for (let i = 0; i < firstDay; i++) {
                    calendarHTML += '<div></div>';
                }
                
                // Add days of month
                for (let day = 1; day <= daysInMonth; day++) {
                    const cellDate = new Date(year, month, day);
                    const isSelectable = cellDate >= minDate && cellDate <= maxDate;
                    const isToday = cellDate.toDateString() === today.toDateString();
                    const dateString = cellDate.toISOString().split('T')[0];
                    
                    let cellStyle = `
                        text-align: center;
                        padding: 12px 8px;
                        cursor: ${isSelectable ? 'pointer' : 'not-allowed'};
                        border-radius: 6px;
                        transition: all 0.2s ease;
                        ${isToday ? 'border: 2px solid #3b82f6;' : ''}
                        ${isSelectable ? 'background: #f0f8ff; color: #1e40af; font-weight: 500;' : 'background: #f3f4f6; color: #9ca3af;'}
                    `;
                    
                    let hoverStyle = isSelectable ? 'onmouseover="this.style.background=\'#1e40af\'; this.style.color=\'white\'" onmouseout="this.style.background=\'#f0f8ff\'; this.style.color=\'#1e40af\'"' : '';
                    let clickHandler = isSelectable ? `onclick="selectNuclearDate('${dateString}')"` : '';
                    
                    calendarHTML += `<div style="${cellStyle}" ${hoverStyle} ${clickHandler}>${day}</div>`;
                }
                
                calendarHTML += '</div></div>';
            }
            
            calendarHTML += `
                <div style="text-align: center; margin-top: 20px;">
                    <button onclick="closeNuclearCalendar()" style="
                        background: #6b7280;
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 6px;
                        cursor: pointer;
                        font-size: 16px;
                        margin-right: 10px;
                    ">Cancel</button>
                </div>
            `;
            
            return calendarHTML;
        }
        
        function setupCalendarControls() {
            // Make selectNuclearDate globally available
            window.selectNuclearDate = function(dateString) {
                console.log('📅 Nuclear calendar date selected:', dateString);
                
                selectedDate = dateString;
                const dateInput = document.getElementById('fitting-date');
                if (dateInput) {
                    dateInput.value = dateString;
                    dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                
                closeNuclearCalendar();
                
                // Trigger time slots loading
                setTimeout(() => {
                    console.log('⏰ Triggering time slots load for date:', dateString);
                    const timeInput = document.getElementById('fitting-time');
                    if (timeInput) {
                        timeInput.placeholder = 'Click to select time';
                    }
                }, 100);
            };
            
            window.closeNuclearCalendar = function() {
                console.log('📅 Closing nuclear calendar');
                
                nuclearCalendarActive = false;
                if (datePickerOverlay) {
                    document.body.removeChild(datePickerOverlay);
                    datePickerOverlay = null;
                }
            };
        }
        
        // Initialize when ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeNuclearFix);
        } else {
            setTimeout(initializeNuclearFix, 100);
        }
        
        // Multiple initialization attempts
        setTimeout(initializeNuclearFix, 1000);
        setTimeout(initializeNuclearFix, 3000);
        
        console.log('☢️ Nuclear calendar fix script loaded');
        
    })();
    </script>
    
    <style>
    /* Nuclear calendar overrides */
    #fitting-date {
        cursor: pointer !important;
        pointer-events: auto !important;
    }
    
    /* Disable native date picker completely */
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 1 !important;
        cursor: pointer !important;
    }
    
    input[type="date"]::-webkit-inner-spin-button,
    input[type="date"]::-webkit-clear-button {
        display: none;
    }
    </style>
    <?php
}
?>