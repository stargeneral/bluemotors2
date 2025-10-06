/**
 * Calendar Popup Fix Implementation
 * Blue Motors Southampton - Fixes the tyre booking calendar popup issue
 * 
 * This file specifically addresses the missing calendar popup on the fitting-date input
 * 
 * PROBLEM: HTML5 date input doesn't show calendar popup consistently
 * SOLUTION: Initialize jQuery UI datepicker with proper fallbacks
 */

(function($) {
    'use strict';
    
    console.log('🔧 BMS Calendar Fix: Initializing...');
    
    // Configuration
    const BMS_CALENDAR_CONFIG = {
        dateInputId: 'fitting-date',
        timeSelectId: 'fitting-time',
        minDaysFromNow: 2,
        maxDaysFromNow: 30,
        ajaxUrl: (typeof bmsVehicleLookup !== 'undefined') ? bmsVehicleLookup.ajaxUrl : 
                 (typeof bmsTyreBooking !== 'undefined') ? bmsTyreBooking.ajaxUrl : 
                 '/wp-admin/admin-ajax.php',
        nonce: (typeof bmsVehicleLookup !== 'undefined') ? bmsVehicleLookup.nonce : 
               (typeof bmsTyreBooking !== 'undefined') ? bmsTyreBooking.nonce : null
    };
    
    // Main initialization function
    function initializeCalendarFix() {
        console.log('📅 BMS Calendar Fix: Starting initialization...');
        
        // Wait for document ready and ensure jQuery is available
        $(document).ready(function() {
            console.log('✅ Document ready, jQuery available');
            
            // Ensure jQuery UI is loaded
            ensureJQueryUI().then(() => {
                setupDatePicker();
                setupTimeSlotHandler();
                setupMobileEnhancements();
                console.log('🎉 BMS Calendar Fix: Initialization complete!');
            }).catch(error => {
                console.error('❌ BMS Calendar Fix: Failed to load jQuery UI:', error);
                // Fallback to native date input
                setupNativeDateFallback();
            });
        });
    }
    
    // Ensure jQuery UI is loaded
    function ensureJQueryUI() {
        return new Promise((resolve, reject) => {
            if (typeof $.ui !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
                console.log('✅ jQuery UI already loaded');
                resolve();
                return;
            }
            
            console.log('⏳ Loading jQuery UI...');
            
            // Load CSS first
            const cssLink = document.createElement('link');
            cssLink.rel = 'stylesheet';
            cssLink.href = 'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css';
            document.head.appendChild(cssLink);
            
            // Load JavaScript
            const script = document.createElement('script');
            script.src = 'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js';
            script.onload = () => {
                console.log('✅ jQuery UI loaded successfully');
                resolve();
            };
            script.onerror = () => {
                console.error('❌ Failed to load jQuery UI');
                reject(new Error('Failed to load jQuery UI'));
            };
            document.head.appendChild(script);
        });
    }
    
    // Set up the datepicker on the fitting-date input
    function setupDatePicker() {
        const $dateInput = $('#' + BMS_CALENDAR_CONFIG.dateInputId);
        
        if ($dateInput.length === 0) {
            console.warn('⚠️ Date input not found:', BMS_CALENDAR_CONFIG.dateInputId);
            return;
        }
        
        console.log('📅 Setting up datepicker on:', BMS_CALENDAR_CONFIG.dateInputId);
        
        // Remove any existing datepicker
        if ($dateInput.hasClass('hasDatepicker')) {
            $dateInput.datepicker('destroy');
        }
        
        // Change input type from 'date' to 'text' to ensure datepicker works
        $dateInput.attr('type', 'text');
        $dateInput.attr('readonly', 'readonly');
        $dateInput.attr('placeholder', 'Click to select date');
        $dateInput.css('cursor', 'pointer');
        
        // Calculate date limits
        const today = new Date();
        const minDate = new Date(today.getTime() + (BMS_CALENDAR_CONFIG.minDaysFromNow * 24 * 60 * 60 * 1000));
        const maxDate = new Date(today.getTime() + (BMS_CALENDAR_CONFIG.maxDaysFromNow * 24 * 60 * 60 * 1000));
        
        // Initialize datepicker with comprehensive options
        $dateInput.datepicker({
            dateFormat: 'DD, dd MM yy',
            altField: $dateInput,
            altFormat: 'yy-mm-dd',
            minDate: BMS_CALENDAR_CONFIG.minDaysFromNow,
            maxDate: BMS_CALENDAR_CONFIG.maxDaysFromNow,
            changeMonth: true,
            changeYear: true,
            showAnim: 'slideDown',
            showButtonPanel: true,
            closeText: 'Close',
            currentText: 'Today',
            showWeek: false,
            firstDay: 1, // Monday
            beforeShow: function(input, inst) {
                console.log('📅 Datepicker about to show');
                // Ensure high z-index and proper positioning
                setTimeout(() => {
                    if (inst.dpDiv) {
                        inst.dpDiv.css({
                            'z-index': 9999,
                            'position': 'absolute',
                            'box-shadow': '0 5px 15px rgba(0,0,0,0.3)',
                            'border': '1px solid #ccc',
                            'background': 'white'
                        });
                        
                        // Add custom styling
                        inst.dpDiv.addClass('bms-custom-datepicker');
                    }
                }, 0);
                
                // Update field hint
                $('.field-hint').text('Calendar is open - select a date');
            },
            onSelect: function(dateText, inst) {
                const selectedDate = $(this).datepicker('getDate');
                const formattedDate = $.datepicker.formatDate('yy-mm-dd', selectedDate);
                
                console.log('📅 Date selected:', dateText, 'Formatted:', formattedDate);
                
                // Store the ISO format for AJAX calls
                $(this).data('selected-date', formattedDate);
                
                // Update field hint
                $('.field-hint').text('Date selected');
                
                // Clear and reload time slots
                const $timeSelect = $('#' + BMS_CALENDAR_CONFIG.timeSelectId);
                $timeSelect.html('<option value="">Loading times...</option>');
                
                // Load available time slots
                loadTimeSlots(formattedDate);
                
                // Show success message
                showMessage('✅ Date selected: ' + dateText, 'success');
            },
            onClose: function(dateText) {
                if (!dateText) {
                    $('.field-hint').text('Tap to open calendar');
                }
            }
        });
        
        // Add custom CSS for better styling
        addDatepickerStyles();
        
        console.log('✅ Datepicker initialized successfully');
    }
    
    // Load available time slots via AJAX
    function loadTimeSlots(date) {
        if (!date || !BMS_CALENDAR_CONFIG.ajaxUrl) {
            console.warn('⚠️ Cannot load time slots - missing date or AJAX URL');
            return;
        }
        
        console.log('⏰ Loading time slots for:', date);
        
        const $timeSelect = $('#' + BMS_CALENDAR_CONFIG.timeSelectId);
        
        // Prepare AJAX data
        const ajaxData = {
            action: 'bms_get_fitting_slots',
            date: date,
            quantity: (window.bmsTyreBooking && window.bmsTyreBooking.selectedQuantity) || 1
        };
        
        // Add nonce if available
        if (BMS_CALENDAR_CONFIG.nonce) {
            ajaxData.nonce = BMS_CALENDAR_CONFIG.nonce;
        }
        
        // Make AJAX call
        $.ajax({
            url: BMS_CALENDAR_CONFIG.ajaxUrl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                console.log('📡 Time slots response:', response);
                
                if (response.success && response.data && response.data.slots) {
                    $timeSelect.html('<option value="">Choose a time</option>');
                    
                    response.data.slots.forEach(slot => {
                        const formattedTime = formatTime(slot);
                        $timeSelect.append(`<option value="${slot}">${formattedTime}</option>`);
                    });
                    
                    if (response.data.slots.length === 0) {
                        $timeSelect.html('<option value="">No slots available</option>');
                    } else {
                        showMessage(`⏰ ${response.data.slots.length} time slots available`, 'success');
                    }
                    
                    console.log('✅ Time slots loaded:', response.data.slots.length);
                } else {
                    $timeSelect.html('<option value="">No times available</option>');
                    showMessage('⚠️ No appointment times available for this date', 'warning');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Time slots AJAX error:', status, error);
                $timeSelect.html('<option value="">Error loading times</option>');
                showMessage('❌ Failed to load appointment times', 'error');
            }
        });
    }
    
    // Format time string for display
    function formatTime(timeString) {
        try {
            const time = new Date(`2000-01-01 ${timeString}`);
            return time.toLocaleTimeString('en-GB', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        } catch (error) {
            return timeString;
        }
    }
    
    // Set up time selection handler
    function setupTimeSlotHandler() {
        const $timeSelect = $('#' + BMS_CALENDAR_CONFIG.timeSelectId);
        
        if ($timeSelect.length === 0) {
            console.warn('⚠️ Time select not found:', BMS_CALENDAR_CONFIG.timeSelectId);
            return;
        }
        
        console.log('⏰ Setting up time slot handler');
        
        $timeSelect.on('change', function() {
            const selectedTime = $(this).val();
            if (selectedTime) {
                console.log('✅ Time selected:', selectedTime);
                showMessage('✅ Time selected: ' + formatTime(selectedTime), 'success');
                
                // Enable booking button if it exists
                $('#btn-confirm-booking').prop('disabled', false);
            }
        });
    }
    
    // Mobile enhancements
    function setupMobileEnhancements() {
        console.log('📱 Setting up mobile enhancements');
        
        // Detect mobile devices
        const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        if (isMobile) {
            console.log('📱 Mobile device detected');
            
            // Add mobile-specific styling
            const $dateInput = $('#' + BMS_CALENDAR_CONFIG.dateInputId);
            $dateInput.css({
                'font-size': '16px', // Prevents zoom on iOS
                'padding': '12px'
            });
            
            // Show mobile-friendly message
            setTimeout(() => {
                showMessage('📱 Mobile-optimized calendar ready!', 'info');
            }, 1000);
        }
    }
    
    // Fallback for when jQuery UI fails to load
    function setupNativeDateFallback() {
        console.log('🔄 Setting up native date fallback');
        
        const $dateInput = $('#' + BMS_CALENDAR_CONFIG.dateInputId);
        
        // Ensure it's a date input
        $dateInput.attr('type', 'date');
        
        // Set up date limits
        const today = new Date();
        const minDate = new Date(today.getTime() + (BMS_CALENDAR_CONFIG.minDaysFromNow * 24 * 60 * 60 * 1000));
        const maxDate = new Date(today.getTime() + (BMS_CALENDAR_CONFIG.maxDaysFromNow * 24 * 60 * 60 * 1000));
        
        $dateInput.attr('min', minDate.toISOString().split('T')[0]);
        $dateInput.attr('max', maxDate.toISOString().split('T')[0]);
        
        // Set up change handler
        $dateInput.on('change', function() {
            const selectedDate = $(this).val();
            if (selectedDate) {
                console.log('📅 Native date selected:', selectedDate);
                loadTimeSlots(selectedDate);
            }
        });
        
        showMessage('📅 Calendar ready (native mode)', 'info');
    }
    
    // Add custom styles for the datepicker
    function addDatepickerStyles() {
        if ($('#bms-datepicker-styles').length > 0) return;
        
        const styles = `
            <style id="bms-datepicker-styles">
                .bms-custom-datepicker {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                }
                
                .bms-custom-datepicker .ui-datepicker-header {
                    background: #1d4ed8 !important;
                    color: white !important;
                    border: none !important;
                    border-radius: 4px 4px 0 0 !important;
                }
                
                .bms-custom-datepicker .ui-datepicker-title {
                    color: white !important;
                    font-weight: bold !important;
                }
                
                .bms-custom-datepicker .ui-datepicker-prev,
                .bms-custom-datepicker .ui-datepicker-next {
                    background: transparent !important;
                    border: none !important;
                    color: white !important;
                    border-radius: 50% !important;
                    width: 30px !important;
                    height: 30px !important;
                }
                
                .bms-custom-datepicker .ui-datepicker-prev:hover,
                .bms-custom-datepicker .ui-datepicker-next:hover {
                    background: rgba(255,255,255,0.2) !important;
                }
                
                .bms-custom-datepicker .ui-state-default {
                    border: 1px solid #e5e7eb !important;
                    background: white !important;
                    color: #374151 !important;
                    text-align: center !important;
                }
                
                .bms-custom-datepicker .ui-state-hover {
                    background: #eff6ff !important;
                    border-color: #bfdbfe !important;
                    color: #1d4ed8 !important;
                }
                
                .bms-custom-datepicker .ui-state-active,
                .bms-custom-datepicker .ui-state-highlight {
                    background: #1d4ed8 !important;
                    border-color: #1d4ed8 !important;
                    color: white !important;
                    font-weight: bold !important;
                }
                
                .bms-custom-datepicker .ui-datepicker-buttonpane {
                    background: #f8fafc !important;
                    border-top: 1px solid #e5e7eb !important;
                }
                
                .bms-custom-datepicker .ui-datepicker-buttonpane button {
                    background: #1d4ed8 !important;
                    color: white !important;
                    border: none !important;
                    padding: 8px 16px !important;
                    border-radius: 4px !important;
                    font-weight: 500 !important;
                }
                
                /* Mobile responsiveness */
                @media (max-width: 768px) {
                    .bms-custom-datepicker {
                        font-size: 16px !important;
                    }
                    
                    .bms-custom-datepicker .ui-datepicker-calendar td {
                        padding: 4px !important;
                    }
                    
                    .bms-custom-datepicker .ui-state-default {
                        padding: 8px 4px !important;
                        min-height: 30px !important;
                    }
                }
            </style>
        `;
        
        $('head').append(styles);
    }
    
    // Show messages to user
    function showMessage(text, type = 'info') {
        console.log('💬 Message:', text, type);
        
        // Remove existing messages
        $('.bms-calendar-message').remove();
        
        // Create new message
        const messageClass = `bms-calendar-message bms-message-${type}`;
        const messageHtml = `
            <div class="${messageClass}" style="
                margin: 10px 0;
                padding: 12px 16px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                background: ${type === 'success' ? '#d1fae5' : type === 'warning' ? '#fef3c7' : type === 'error' ? '#fef2f2' : '#dbeafe'};
                color: ${type === 'success' ? '#065f46' : type === 'warning' ? '#92400e' : type === 'error' ? '#dc2626' : '#1e40af'};
                border: 1px solid ${type === 'success' ? '#a7f3d0' : type === 'warning' ? '#fbbf24' : type === 'error' ? '#f87171' : '#93c5fd'};
                animation: slideIn 0.3s ease-out;
            ">
                ${text}
                <button onclick="$(this).parent().remove()" style="
                    float: right;
                    background: none;
                    border: none;
                    font-size: 16px;
                    cursor: pointer;
                    color: inherit;
                    opacity: 0.7;
                    margin-left: 10px;
                ">×</button>
            </div>
        `;
        
        // Insert message above the appointment form
        const $appointmentForm = $('.fitting-appointment');
        if ($appointmentForm.length > 0) {
            $appointmentForm.prepend(messageHtml);
        } else {
            // Fallback: insert after search methods
            $('.bms-search-methods').after(messageHtml);
        }
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            $(`.bms-calendar-message:contains("${text.substring(0, 20)}")`).fadeOut();
        }, 5000);
    }
    
    // Public API for external access
    window.BMSCalendarFix = {
        init: initializeCalendarFix,
        loadTimeSlots: loadTimeSlots,
        showMessage: showMessage,
        resetDatePicker: function() {
            const $dateInput = $('#' + BMS_CALENDAR_CONFIG.dateInputId);
            if ($dateInput.hasClass('hasDatepicker')) {
                $dateInput.datepicker('destroy');
            }
            setupDatePicker();
        }
    };
    
    // Auto-initialize
    initializeCalendarFix();
    
    console.log('🚀 BMS Calendar Fix: Script loaded and ready!');
    
})(jQuery);
