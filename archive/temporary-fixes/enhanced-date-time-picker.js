/**
 * Enhanced Date & Time Picker for Tyre Booking System
 * Blue Motors Southampton - Professional Implementation
 * 
 * This replaces the custom calendar with a modern, reliable date/time picker
 * using Flatpickr library for better cross-browser compatibility and UX.
 */

class EnhancedDateTimePicker {
    constructor() {
        this.selectedDate = null;
        this.selectedTime = null;
        this.availableSlots = [];
        this.isLoading = false;
        this.datePickerInstance = null;
        
        this.init();
    }
    
    init() {
        console.log('🗓️ Initializing Enhanced Date & Time Picker...');
        
        // Load Flatpickr library if not already loaded
        this.loadFlatpickr().then(() => {
            this.setupDatePicker();
            this.setupEventListeners();
            this.setupTimeSlotHandling();
        }).catch(error => {
            console.error('Failed to load Flatpickr:', error);
            this.fallbackToNativePicker();
        });
    }
    
    async loadFlatpickr() {
        // Check if Flatpickr is already loaded
        if (typeof flatpickr !== 'undefined') {
            return Promise.resolve();
        }
        
        // Load Flatpickr CSS
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
        document.head.appendChild(link);
        
        // Load Flatpickr theme (Blue theme to match our branding)
        const themeLink = document.createElement('link');
        themeLink.rel = 'stylesheet';
        themeLink.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css';
        document.head.appendChild(themeLink);
        
        // Load Flatpickr JavaScript
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }
    
    setupDatePicker() {
        const dateInput = document.getElementById('fitting-date');
        if (!dateInput) {
            console.error('Date input field not found');
            return;
        }
        
        // Remove readonly attribute to allow Flatpickr to work
        dateInput.removeAttribute('readonly');
        
        // Calculate date limits
        const today = new Date();
        const minDate = new Date(today.getTime() + (2 * 24 * 60 * 60 * 1000)); // 2 days from now
        const maxDate = new Date(today.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 days from now
        
        // Initialize Flatpickr
        this.datePickerInstance = flatpickr(dateInput, {
            dateFormat: "d/m/Y",
            minDate: minDate,
            maxDate: maxDate,
            disable: [
                // Disable Sundays
                function(date) {
                    return date.getDay() === 0;
                }
            ],
            locale: {
                firstDayOfWeek: 1 // Start week on Monday
            },
            onChange: (selectedDates, dateStr, instance) => {
                this.onDateSelected(selectedDates[0], dateStr);
            },
            onReady: (selectedDates, dateStr, instance) => {
                this.enhanceCalendarUI(instance);
            },
            onOpen: (selectedDates, dateStr, instance) => {
                this.showDatePickerFeedback();
            },
            onClose: (selectedDates, dateStr, instance) => {
                this.hideDatePickerFeedback();
            }
        });
        
        console.log('✅ Flatpickr initialized successfully');
    }
    
    enhanceCalendarUI(instance) {
        // Add custom styling and help text to the calendar
        const calendar = instance.calendarContainer;
        
        // Add help text at the bottom
        const helpDiv = document.createElement('div');
        helpDiv.className = 'flatpickr-help-text';
        helpDiv.innerHTML = `
            <small style="display: block; padding: 10px; background: #f0f9ff; border-top: 1px solid #e0e7ff; text-align: center; color: #1e40af;">
                💡 Select your preferred fitting date<br>
                📅 Available: Monday-Saturday (2-30 days ahead)<br>
                ❌ Sundays closed
            </small>
        `;
        calendar.appendChild(helpDiv);
        
        // Add custom class for our styling
        calendar.classList.add('bms-enhanced-calendar');
    }
    
    showDatePickerFeedback() {
        // Show loading indicator or helpful message
        const feedback = document.getElementById('date-picker-feedback');
        if (feedback) {
            feedback.textContent = '📅 Choose your preferred appointment date';
            feedback.style.display = 'block';
        }
    }
    
    hideDatePickerFeedback() {
        const feedback = document.getElementById('date-picker-feedback');
        if (feedback) {
            feedback.style.display = 'none';
        }
    }
    
    async onDateSelected(date, dateString) {
        this.selectedDate = date;
        console.log('📅 Date selected:', dateString);
        
        // Update visual feedback
        this.showDateSelectedFeedback(date, dateString);
        
        // Load available time slots
        await this.loadTimeSlots(date);
    }
    
    showDateSelectedFeedback(date, dateString) {
        // Show selected date with day name
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const formattedDate = date.toLocaleDateString('en-GB', options);
        
        // Update date input title
        const dateInput = document.getElementById('fitting-date');
        dateInput.setAttribute('title', formattedDate);
        
        // Update time label
        const timeLabel = document.querySelector('label[for="fitting-time"]');
        if (timeLabel) {
            timeLabel.innerHTML = `
                ⏰ Available Times for ${formattedDate}:
                <span class="field-hint">Loading slots...</span>
            `;
        }
        
        // Show success feedback
        this.showNotification('✅ Date selected: ' + formattedDate, 'success');
    }
    
    async loadTimeSlots(date) {
        const timeSelect = document.getElementById('fitting-time');
        if (!timeSelect) {
            console.error('Time select element not found');
            return;
        }
        
        // Show loading state
        this.setTimeSelectState('loading');
        this.isLoading = true;
        
        try {
            // Format date for API call (YYYY-MM-DD)
            const isoDate = date.toISOString().split('T')[0];
            
            const response = await fetch(bmsVehicleLookup.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'bms_get_fitting_slots',
                    nonce: bmsVehicleLookup.nonce,
                    date: isoDate,
                    quantity: this.getSelectedQuantity() || 4
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.data && data.data.slots) {
                this.availableSlots = data.data.slots;
                this.populateTimeSlots(data.data.slots);
                this.showNotification(`🕐 ${data.data.slots.length} time slots available`, 'success');
            } else {
                throw new Error(data.data || 'No time slots available');
            }
            
        } catch (error) {
            console.error('Time slots loading error:', error);
            this.handleTimeSlotError(error.message);
        } finally {
            this.isLoading = false;
        }
    }
    
    populateTimeSlots(slots) {
        const timeSelect = document.getElementById('fitting-time');
        
        // Clear existing options
        timeSelect.innerHTML = '';
        
        // Add default option
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Choose your preferred time';
        timeSelect.appendChild(defaultOption);
        
        // Add available slots
        slots.forEach(slot => {
            const option = document.createElement('option');
            option.value = slot;
            option.textContent = this.formatTimeSlot(slot);
            timeSelect.appendChild(option);
        });
        
        // Update label
        this.updateTimeLabel('available', slots.length);
        
        // Enable time selection
        timeSelect.disabled = false;
        
        // Focus on time selection
        timeSelect.focus();
    }
    
    formatTimeSlot(timeString) {
        try {
            // Parse time string (assuming format like "14:30" or "2:30 PM")
            const time = new Date(`2000-01-01 ${timeString}`);
            return time.toLocaleTimeString('en-GB', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        } catch (error) {
            // Fallback to original string if parsing fails
            return timeString;
        }
    }
    
    handleTimeSlotError(errorMessage) {
        this.setTimeSelectState('error', errorMessage);
        
        // Provide fallback options
        this.showFallbackTimeOptions();
        
        // Show error notification
        this.showNotification('⚠️ Could not load exact time slots. Showing standard times.', 'warning');
    }
    
    showFallbackTimeOptions() {
        const timeSelect = document.getElementById('fitting-time');
        
        // Standard business hours as fallback
        const standardTimes = [
            '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
            '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
            '15:00', '15:30', '16:00', '16:30', '17:00'
        ];
        
        timeSelect.innerHTML = '<option value="">Choose time (subject to availability)</option>';
        
        standardTimes.forEach(time => {
            const option = document.createElement('option');
            option.value = time;
            option.textContent = this.formatTimeSlot(time);
            option.classList.add('fallback-slot');
            timeSelect.appendChild(option);
        });
        
        timeSelect.disabled = false;
        this.updateTimeLabel('fallback');
    }
    
    setTimeSelectState(state, message = '') {
        const timeSelect = document.getElementById('fitting-time');
        
        switch (state) {
            case 'loading':
                timeSelect.innerHTML = '<option value="">⏳ Loading available times...</option>';
                timeSelect.disabled = true;
                this.updateTimeLabel('loading');
                break;
                
            case 'error':
                timeSelect.innerHTML = `<option value="">❌ ${message || 'Error loading times'}</option>`;
                timeSelect.disabled = true;
                this.updateTimeLabel('error');
                break;
                
            case 'empty':
                timeSelect.innerHTML = '<option value="">📅 No slots available for this date</option>';
                timeSelect.disabled = true;
                this.updateTimeLabel('empty');
                break;
        }
    }
    
    updateTimeLabel(state, count = 0) {
        const timeLabel = document.querySelector('label[for="fitting-time"]');
        if (!timeLabel) return;
        
        const dateStr = this.selectedDate ? 
            this.selectedDate.toLocaleDateString('en-GB', { weekday: 'long', month: 'short', day: 'numeric' }) : 
            'selected date';
        
        switch (state) {
            case 'loading':
                timeLabel.innerHTML = `
                    ⏰ Available Times for ${dateStr}:
                    <span class="field-hint loading">Loading...</span>
                `;
                break;
                
            case 'available':
                timeLabel.innerHTML = `
                    ⏰ Available Times for ${dateStr}:
                    <span class="field-hint success">${count} slots available</span>
                `;
                break;
                
            case 'error':
                timeLabel.innerHTML = `
                    ⏰ Times for ${dateStr}:
                    <span class="field-hint error">Using standard times</span>
                `;
                break;
                
            case 'fallback':
                timeLabel.innerHTML = `
                    ⏰ Times for ${dateStr}:
                    <span class="field-hint warning">Standard times (subject to confirmation)</span>
                `;
                break;
                
            case 'empty':
                timeLabel.innerHTML = `
                    ⏰ Times for ${dateStr}:
                    <span class="field-hint error">No slots available</span>
                `;
                break;
        }
    }
    
    setupEventListeners() {
        // Time selection handler
        const timeSelect = document.getElementById('fitting-time');
        if (timeSelect) {
            timeSelect.addEventListener('change', (e) => {
                this.onTimeSelected(e.target.value);
            });
        }
        
        // Form validation
        const confirmButton = document.getElementById('btn-confirm-booking');
        if (confirmButton) {
            confirmButton.addEventListener('click', (e) => {
                if (!this.validateDateTime()) {
                    e.preventDefault();
                    this.showValidationErrors();
                }
            });
        }
    }
    
    setupTimeSlotHandling() {
        // Add visual enhancements to time slots
        const timeSelect = document.getElementById('fitting-time');
        if (timeSelect) {
            // Style the time select
            timeSelect.classList.add('enhanced-time-select');
            
            // Add change animation
            timeSelect.addEventListener('change', () => {
                if (timeSelect.value) {
                    timeSelect.classList.add('time-selected');
                    this.showNotification(`🕐 Time selected: ${this.formatTimeSlot(timeSelect.value)}`, 'success');
                } else {
                    timeSelect.classList.remove('time-selected');
                }
            });
        }
    }
    
    onTimeSelected(time) {
        this.selectedTime = time;
        console.log('🕐 Time selected:', time);
        
        // Enable booking confirmation if both date and time are selected
        if (this.selectedDate && this.selectedTime) {
            this.enableBookingConfirmation();
        }
    }
    
    enableBookingConfirmation() {
        const confirmButton = document.getElementById('btn-confirm-booking');
        if (confirmButton) {
            confirmButton.disabled = false;
            confirmButton.classList.add('ready-to-book');
            
            // Update button text to show selected appointment
            const dateStr = this.selectedDate.toLocaleDateString('en-GB', { 
                weekday: 'short', 
                month: 'short', 
                day: 'numeric' 
            });
            const timeStr = this.formatTimeSlot(this.selectedTime);
            
            confirmButton.innerHTML = `
                📅 Confirm Booking: ${dateStr} at ${timeStr}
            `;
        }
    }
    
    validateDateTime() {
        const errors = [];
        
        if (!this.selectedDate) {
            errors.push('Please select an appointment date');
        }
        
        if (!this.selectedTime) {
            errors.push('Please select an appointment time');
        }
        
        // Check if selected date is still valid (not in the past)
        if (this.selectedDate && this.selectedDate < new Date()) {
            errors.push('Selected date is in the past');
        }
        
        this.validationErrors = errors;
        return errors.length === 0;
    }
    
    showValidationErrors() {
        if (this.validationErrors && this.validationErrors.length > 0) {
            const errorMessage = this.validationErrors.join('\n• ');
            this.showNotification('⚠️ Please fix these issues:\n• ' + errorMessage, 'error');
        }
    }
    
    showNotification(message, type = 'info') {
        // Create or update notification element
        let notification = document.getElementById('datetime-notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'datetime-notification';
            notification.className = 'datetime-notification';
            
            // Insert after the appointment form
            const appointmentForm = document.querySelector('.appointment-form');
            if (appointmentForm) {
                appointmentForm.insertAdjacentElement('afterend', notification);
            }
        }
        
        // Set notification content and style
        notification.textContent = message;
        notification.className = `datetime-notification ${type}`;
        notification.style.display = 'block';
        
        // Auto-hide success and info notifications
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                if (notification) {
                    notification.style.display = 'none';
                }
            }, 3000);
        }
    }
    
    fallbackToNativePicker() {
        console.warn('⚠️ Falling back to native date picker');
        
        const dateInput = document.getElementById('fitting-date');
        if (dateInput) {
            // Convert to native date input
            dateInput.type = 'date';
            dateInput.removeAttribute('readonly');
            
            // Set date limits
            const today = new Date();
            const minDate = new Date(today.getTime() + (2 * 24 * 60 * 60 * 1000));
            const maxDate = new Date(today.getTime() + (30 * 24 * 60 * 60 * 1000));
            
            dateInput.min = minDate.toISOString().split('T')[0];
            dateInput.max = maxDate.toISOString().split('T')[0];
            
            // Add change handler
            dateInput.addEventListener('change', (e) => {
                const selectedDate = new Date(e.target.value);
                this.onDateSelected(selectedDate, e.target.value);
            });
            
            this.showNotification('📱 Using device native date picker', 'info');
        }
    }
    
    getSelectedQuantity() {
        // Helper method to get selected tyre quantity
        // This should integrate with the main tyre booking system
        const quantitySelect = document.querySelector('.tyre-quantity');
        return quantitySelect ? parseInt(quantitySelect.value) || 4 : 4;
    }
    
    // Public methods for integration
    getSelectedDateTime() {
        return {
            date: this.selectedDate,
            time: this.selectedTime,
            dateString: this.selectedDate ? this.selectedDate.toISOString().split('T')[0] : null,
            timeString: this.selectedTime,
            formattedDate: this.selectedDate ? this.selectedDate.toLocaleDateString('en-GB') : null,
            formattedTime: this.selectedTime ? this.formatTimeSlot(this.selectedTime) : null
        };
    }
    
    reset() {
        this.selectedDate = null;
        this.selectedTime = null;
        this.availableSlots = [];
        
        if (this.datePickerInstance) {
            this.datePickerInstance.clear();
        }
        
        const timeSelect = document.getElementById('fitting-time');
        if (timeSelect) {
            timeSelect.innerHTML = '<option value="">Select date first</option>';
            timeSelect.disabled = true;
        }
        
        console.log('🔄 Date/Time picker reset');
    }
    
    destroy() {
        if (this.datePickerInstance) {
            this.datePickerInstance.destroy();
        }
        
        const notification = document.getElementById('datetime-notification');
        if (notification) {
            notification.remove();
        }
        
        console.log('🗑️ Date/Time picker destroyed');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if we're on a page with tyre booking
    if (document.getElementById('fitting-date')) {
        window.enhancedDateTimePicker = new EnhancedDateTimePicker();
    }
});

// Export for use in other scripts
window.EnhancedDateTimePicker = EnhancedDateTimePicker;