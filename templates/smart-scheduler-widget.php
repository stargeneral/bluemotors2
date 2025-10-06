<!-- Smart Scheduler Widget Template -->
<div class="bms-smart-scheduler-widget">
    <div class="smart-scheduler-header">
        <h3>Available Appointments</h3>
    </div>
    
    <div class="scheduler-form">
        <div class="form-group" id="service-selection-group">
            <label for="smart-service-type">Service Type:</label>
            <select id="smart-service-type" class="form-control">
                <option value="">Select a service...</option>
                <option value="mot_test">MOT Test</option>
                <option value="full_service">Full Service</option>
                <option value="interim_service">Interim Service</option>
                <option value="air_con_regas">Air Conditioning Re-gas</option>
                <option value="brake_check">Brake Inspection</option>
                <option value="battery_test">Battery Test</option>
                <option value="tyre_fitting">Tyre Fitting</option>
            </select>
        </div>
        
        <div class="selected-service-display" id="selected-service-display" style="display: none;">
            <h4>Selected Service: <span id="display-service-name"></span></h4>
            <p>Price: £<span id="display-service-price">0.00</span></p>
        </div>
        
        <div class="form-group" id="date-picker-group" style="display: none;">
            <label for="appointment-date">Choose Your Preferred Date:</label>
            <div class="date-picker-wrapper">
                <input type="text" id="appointment-date" class="form-control date-picker-input" 
                       placeholder="Click to select date" readonly>
                <span class="calendar-icon">📅</span>
            </div>
            <div class="date-help-text">Select a date to see available time slots</div>
            <div id="calendar-popup" class="calendar-popup" style="display: none;"></div>
        </div>
        
        <button type="button" id="select-date-btn" class="btn btn-primary">
            Select Date
        </button>
        
        <button type="button" id="get-smart-suggestions" class="btn btn-primary" style="display: none;">
            View Available Times
        </button>
    </div>
    
    <div id="smart-suggestions-container" style="display: none;">
        <div class="suggestions-header">
            <h4>Available Appointment Times</h4>
        </div>
        
        <div id="smart-suggestions-list">
            <!-- Suggestions will be populated here -->
        </div>
        
    </div>
    
    <div id="smart-loading" style="display: none;">
        <div class="loading">
            <div class="loading-icon">⏳</div>
            <p>Finding available time slots...</p>
            <div class="loading-progress">
                <div class="progress-bar"></div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize calendar functionality
    let selectedDate = null;
    const today = new Date();
    const maxDate = new Date();
    maxDate.setDate(today.getDate() + 30);
    let preSelectedService = null;
    
    // Check for pre-selected service from Step 1
    function checkForPreSelectedService() {
        // Check if service was already selected in previous step
        if (typeof bookingData !== 'undefined' && bookingData.service) {
            preSelectedService = bookingData.service;
            displayPreSelectedService(bookingData);
            return true;
        }
        
        // Also check session storage or hidden fields
        const sessionService = sessionStorage.getItem('bms_selected_service');
        if (sessionService) {
            try {
                preSelectedService = JSON.parse(sessionService);
                displayPreSelectedService(preSelectedService);
                return true;
            } catch (e) {
                console.log('Error parsing session service data');
            }
        }
        
        return false;
    }
    
    function displayPreSelectedService(serviceData) {
        // Hide service selection dropdown
        $('#service-selection-group').hide();
        
        // Get service name from service type
        const serviceNames = {
            'mot_test': 'MOT Test',
            'full_service': 'Full Service', 
            'interim_service': 'Interim Service',
            'air_con_regas': 'Air Conditioning Re-gas',
            'brake_check': 'Brake Inspection',
            'battery_test': 'Battery Test',
            'tyre_fitting': 'Tyre Fitting'
        };
        
        const serviceName = serviceData.serviceName || serviceNames[serviceData.service] || serviceData.service;
        const servicePrice = serviceData.totalPrice || serviceData.price || '0.00';
        
        // Show selected service display
        $('#display-service-name').text(serviceName);
        $('#display-service-price').text(servicePrice);
        $('#selected-service-display').show();
        
        // Set the hidden service type value
        $('#smart-service-type').val(serviceData.service);
        
        // Show date picker immediately since service is already selected
        $('#date-picker-group').show();
        $('#select-date-btn').hide();
        $('#get-smart-suggestions').show();
    }
    
    // Initialize on page load
    if (checkForPreSelectedService()) {
        console.log('Pre-selected service found, skipping service selection');
    } else {
        console.log('No pre-selected service, showing service selection');
    }
    
    // Calendar functions
    function generateCalendar(year, month) {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const prevLastDay = new Date(year, month, 0);
        const daysInMonth = lastDay.getDate();
        const firstDayOfWeek = firstDay.getDay();
        const daysInPrevMonth = prevLastDay.getDate();
        
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                          'July', 'August', 'September', 'October', 'November', 'December'];
        
        let html = '<div class="calendar-header">';
        html += '<button class="cal-nav cal-prev">&lt;</button>';
        html += '<div class="cal-month-year">' + monthNames[month] + ' ' + year + '</div>';
        html += '<button class="cal-nav cal-next">&gt;</button>';
        html += '</div>';
        
        html += '<div class="calendar-days">';
        html += '<div class="cal-day-header">Sun</div>';
        html += '<div class="cal-day-header">Mon</div>';
        html += '<div class="cal-day-header">Tue</div>';
        html += '<div class="cal-day-header">Wed</div>';
        html += '<div class="cal-day-header">Thu</div>';
        html += '<div class="cal-day-header">Fri</div>';
        html += '<div class="cal-day-header">Sat</div>';
        
        // Previous month days
        for (let i = firstDayOfWeek - 1; i >= 0; i--) {
            html += '<div class="cal-day other-month">' + (daysInPrevMonth - i) + '</div>';
        }
        
        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const isToday = date.toDateString() === today.toDateString();
            const isPast = date < today;
            const isFuture = date > maxDate;
            const isSelected = selectedDate && date.toDateString() === selectedDate.toDateString();
            const isSunday = date.getDay() === 0;
            
            let classes = 'cal-day';
            if (isToday) classes += ' today';
            if (isPast || isFuture || isSunday) classes += ' disabled';
            if (isSelected) classes += ' selected';
            
            html += '<div class="' + classes + '" data-date="' + date.toISOString() + '">' + day + '</div>';
        }
        
        // Next month days
        const remainingDays = 42 - (firstDayOfWeek + daysInMonth);
        for (let day = 1; day <= remainingDays; day++) {
            html += '<div class="cal-day other-month">' + day + '</div>';
        }
        
        html += '</div>';
        
        return html;
    }
    
    function showCalendar() {
        const currentMonth = selectedDate ? selectedDate.getMonth() : today.getMonth();
        const currentYear = selectedDate ? selectedDate.getFullYear() : today.getFullYear();
        
        $('#calendar-popup').html(generateCalendar(currentYear, currentMonth)).slideDown();
    }
    
    // Calendar events
    $('#appointment-date, .calendar-icon').click(function(e) {
        e.stopPropagation();
        showCalendar();
    });
    
    $(document).on('click', '.cal-day:not(.disabled):not(.other-month)', function() {
        const date = new Date($(this).data('date'));
        selectedDate = date;
        
        // Format date in UK format
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const ukDate = day + '/' + month + '/' + year;
        
        $('#appointment-date').val(ukDate);
        $('#calendar-popup').slideUp();
        
        // Show formatted date
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const formattedDate = date.toLocaleDateString('en-GB', options);
        $('#appointment-date').attr('title', formattedDate);
    });
    
    $(document).on('click', '.cal-prev', function(e) {
        e.preventDefault();
        const currentContent = $('#calendar-popup').html();
        const monthYearMatch = currentContent.match(/class="cal-month-year">(\w+) (\d+)</);
        if (monthYearMatch) {
            const monthName = monthYearMatch[1];
            const year = parseInt(monthYearMatch[2]);
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            const month = monthNames.indexOf(monthName);
            
            let newMonth = month - 1;
            let newYear = year;
            if (newMonth < 0) {
                newMonth = 11;
                newYear--;
            }
            
            $('#calendar-popup').html(generateCalendar(newYear, newMonth));
        }
    });
    
    $(document).on('click', '.cal-next', function(e) {
        e.preventDefault();
        const currentContent = $('#calendar-popup').html();
        const monthYearMatch = currentContent.match(/class="cal-month-year">(\w+) (\d+)</);
        if (monthYearMatch) {
            const monthName = monthYearMatch[1];
            const year = parseInt(monthYearMatch[2]);
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            const month = monthNames.indexOf(monthName);
            
            let newMonth = month + 1;
            let newYear = year;
            if (newMonth > 11) {
                newMonth = 0;
                newYear++;
            }
            
            $('#calendar-popup').html(generateCalendar(newYear, newMonth));
        }
    });
    
    // Close calendar when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.date-picker-wrapper, #calendar-popup').length) {
            $('#calendar-popup').slideUp();
        }
    });
    
    // Step 1: Select service type and show date picker
    $('#select-date-btn').click(function() {
        const serviceType = $('#smart-service-type').val();
        
        if (!serviceType) {
            alert('Please select a service type first');
            return;
        }
        
        // Show date picker
        $('#date-picker-group').slideDown();
        $(this).hide();
        $('#get-smart-suggestions').show();
    });
    
    // Step 2: Get time slots for selected date
    $('#get-smart-suggestions').click(function() {
        const serviceType = $('#smart-service-type').val();
        const selectedDate = $('#appointment-date').val();
        
        if (!selectedDate) {
            alert('Please select a date');
            return;
        }
        
        // Show loading
        $('#smart-suggestions-container').hide();
        $('#smart-loading').show();
        
        // Get time slots for specific date
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'bms_get_time_slots_for_date',
                nonce: '<?php echo wp_create_nonce('bms_nonce'); ?>',
                service_type: serviceType,
                selected_date: selectedDate
            },
            success: function(response) {
                $('#smart-loading').hide();
                
                if (response.success) {
                    displayTimeSlots(response.data);
                    $('#smart-suggestions-container').show();
                } else {
                    alert('Failed to get time slots: ' + response.data);
                }
            },
            error: function() {
                $('#smart-loading').hide();
                alert('Network error. Please try again.');
            }
        });
    });
    
    // Reset form when service type changes
    $('#smart-service-type').change(function() {
        $('#date-picker-group').hide();
        $('#appointment-date').val('');
        $('#select-date-btn').show();
        $('#get-smart-suggestions').hide();
        $('#smart-suggestions-container').hide();
    });
    
    function displayTimeSlots(data) {
        let html = `
            <div class="selected-date-header">
                <h4>Available times for ${data.display_date}</h4>
                <button class="btn btn-small change-date-btn">Change Date</button>
            </div>
        `;
        
        if (data.slots && data.slots.length > 0) {
            html += '<div class="time-slots-grid">';
            
            data.slots.forEach(function(slot) {
                const slotClass = getSlotClass(slot.busy_level);
                
                html += `
                    <div class="time-slot ${slotClass}" data-time="${slot.time}">
                        <div class="slot-time">${slot.display_time}</div>
                        <div class="slot-status">${slot.busy_level}</div>
                        <button class="btn btn-small btn-select-time" data-date="${data.date}" data-time="${slot.time}">
                            Select
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
        } else {
            html += '<p class="no-slots-message">No available time slots for this date. Please select another date.</p>';
        }
        
        $('#smart-suggestions-list').html(html);
        
        // Bind events
        $('.change-date-btn').click(function() {
            $('#appointment-date').focus();
        });
        
        $('.btn-select-time').click(function() {
            const date = $(this).data('date');
            const time = $(this).data('time');
            
            // Highlight selected slot
            $('.time-slot').removeClass('selected');
            $(this).closest('.time-slot').addClass('selected');
            
            // Show confirmation
            const displayDate = data.display_date;
            const displayTime = $(this).closest('.time-slot').find('.slot-time').text();
            
            if (confirm(`Confirm appointment for ${displayDate} at ${displayTime}?`)) {
                // Trigger booking with selected date/time
                if (typeof window.selectAppointmentSlot === 'function') {
                    window.selectAppointmentSlot(date, time);
                } else {
                    alert(`Selected: ${displayDate} at ${displayTime}`);
                }
            }
        });
    }
    
    // Keep the original display function for backward compatibility
    function displaySmartSuggestions(suggestions) {
        let html = '';
        
        suggestions.forEach(function(day, index) {
            const isRecommended = day.recommended;
            const dayClass = isRecommended ? 'recommended-day' : 'regular-day';
            
            html += `
                <div class="suggestion-day ${dayClass}">
                    <div class="day-header">
                        <h5>${day.display_date}</h5>
                        ${isRecommended ? '<span class="recommended-badge">⭐ Recommended</span>' : ''}
                    </div>
                    
                    <div class="day-slots">
            `;
            
            day.slots.slice(0, <?php echo (int)$atts['max_suggestions']; ?>).forEach(function(slot) {
                const slotClass = getSlotClass(slot.busy_level);
                const matchBadge = slot.customer_match > 70 ? '<span class="match-badge">👤 Your Style</span>' : '';
                
                html += `
                    <div class="time-slot-suggestion ${slotClass}" data-date="${day.date}" data-time="${slot.time}">
                        <div class="slot-time">${slot.display_time}</div>
                        <div class="slot-info">
                            <span class="busy-level">${slot.busy_level}</span>
                            <span class="efficiency">Efficiency: ${slot.efficiency_rating}%</span>
                            ${matchBadge}
                        </div>
                        <div class="slot-recommendation">${slot.recommendation}</div>
                        <button class="btn btn-small btn-select-slot" data-date="${day.date}" data-time="${slot.time}">
                            Book This Slot
                        </button>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });
        
        $('#smart-suggestions-list').html(html);
        
        // Bind slot selection events
        $('.btn-select-slot').click(function() {
            const date = $(this).data('date');
            const time = $(this).data('time');
            
            // Trigger booking with selected date/time
            if (typeof window.selectAppointmentSlot === 'function') {
                window.selectAppointmentSlot(date, time);
            } else {
                // Fallback - could integrate with main booking form
                alert(`Selected: ${date} at ${time}`);
            }
        });
    }
    
    function getSlotClass(busyLevel) {
        switch(busyLevel) {
            case 'Optimal': return 'slot-optimal';
            case 'Good': return 'slot-good';
            case 'Moderate': return 'slot-moderate';
            default: return 'slot-busy';
        }
    }
});
</script>

<style>
.bms-smart-scheduler-widget {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    margin: 20px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.smart-scheduler-header h3 {
    margin: 0 0 8px 0;
    color: #1e3a8a;
}

.competitive-advantage {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    margin: 12px 0;
    font-size: 14px;
}

.selected-service-display {
    background: #f0f9ff;
    border: 2px solid #3b82f6;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
}

.selected-service-display h4 {
    margin: 0 0 8px 0;
    color: #1e3a8a;
}

.selected-service-display p {
    margin: 0;
    color: #374151;
    font-weight: 600;
}

.scheduler-form {
    margin: 20px 0;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
}

.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-small {
    padding: 8px 16px;
    font-size: 14px;
}

.btn-select-slot {
    background: #10b981;
    color: white;
    width: 100%;
    margin-top: 8px;
}

.btn-select-slot:hover {
    background: #059669;
}

.ai-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #8b5cf6;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.suggestions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
}

.suggestion-day {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 16px;
    overflow: hidden;
}

.recommended-day {
    border-color: #fbbf24;
    box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.2);
}

.day-header {
    background: #f9fafb;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.recommended-day .day-header {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
}

.recommended-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.day-score {
    background: #3b82f6;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.day-slots {
    padding: 16px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 12px;
}

.time-slot-suggestion {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    transition: all 0.2s ease;
}

.slot-optimal {
    border-color: #22c55e;
    background: rgba(34, 197, 94, 0.05);
}

.slot-good {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.slot-moderate {
    border-color: #f59e0b;
    background: rgba(245, 158, 11, 0.05);
}

.slot-busy {
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.05);
}

.slot-time {
    font-size: 18px;
    font-weight: 700;
    color: #1e3a8a;
    margin-bottom: 8px;
}

.slot-info {
    display: flex;
    gap: 12px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.slot-info span {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.match-badge {
    background: #8b5cf6 !important;
    color: white !important;
}

.slot-recommendation {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 8px;
}

.competitive-note-small {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    margin-bottom: 8px;
}

.competitive-note {
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    border-radius: 8px;
    padding: 16px;
    margin-top: 20px;
}

.competitive-note ul {
    margin: 8px 0 0 0;
    padding-left: 20px;
}

.competitive-note li {
    margin-bottom: 4px;
    font-size: 14px;
}

.loading {
    text-align: center;
    padding: 40px 20px;
}

.date-help-text {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.date-picker-wrapper {
    position: relative;
}

.date-picker-input {
    cursor: pointer;
    background: white;
    padding-right: 40px;
}

.calendar-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    cursor: pointer;
    pointer-events: none;
}

.calendar-popup {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    margin-top: 4px;
    padding: 16px;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.cal-nav {
    background: #3b82f6;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cal-nav:hover {
    background: #2563eb;
}

.cal-month-year {
    font-weight: 600;
    color: #1e3a8a;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.cal-day-header {
    text-align: center;
    font-weight: 600;
    font-size: 12px;
    color: #6b7280;
    padding: 4px;
}

.cal-day {
    text-align: center;
    padding: 8px 4px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 14px;
}

.cal-day:hover:not(.disabled):not(.other-month) {
    background: #e0e7ff;
}

.cal-day.today {
    background: #fef3c7;
    font-weight: 600;
}

.cal-day.selected {
    background: #3b82f6;
    color: white;
}

.cal-day.disabled {
    color: #d1d5db;
    cursor: not-allowed;
}

.cal-day.other-month {
    color: #e5e7eb;
    cursor: default:
}

.selected-date-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
}

.selected-date-header h4 {
    margin: 0;
    color: #1e3a8a;
}

.change-date-btn {
    background: #6b7280;
    color: white;
    padding: 6px 12px;
    font-size: 12px;
}

.change-date-btn:hover {
    background: #4b5563;
}

.time-slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.time-slot {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    transition: all 0.2s ease;
}

.time-slot:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.time-slot.selected {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
}

.slot-time {
    font-size: 16px;
    font-weight: 700;
    color: #1e3a8a;
    margin-bottom: 4px;
}

.slot-status {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 8px;
}

.btn-select-time {
    background: #10b981;
    color: white;
    width: 100%;
    padding: 6px 12px;
    font-size: 12px;
}

.btn-select-time:hover {
    background: #059669;
}

.no-slots-message {
    text-align: center;
    color: #6b7280;
    padding: 40px 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.loading-icon {
    font-size: 48px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.loading-progress {
    width: 100%;
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    margin: 20px 0;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    width: 0%;
    animation: progress 2s ease-in-out infinite;
}

@keyframes progress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .suggestions-header {
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
    }
    
    .day-header {
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
    }
    
    .day-slots {
        grid-template-columns: 1fr;
    }
    
    .slot-info {
        justify-content: center;
    }
}
</style>
