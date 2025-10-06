/**
 * Fixed Mobile-Friendly Date & Time Picker for Tyre Booking
 * Blue Motors Southampton - Addresses calendar and time slot issues
 * 
 * Fixes:
 * 1. Date field now opens native calendar when mobile picker fails
 * 2. Time slots load properly and display in popup
 * 3. Better fallback mechanisms
 * 4. Improved error handling
 */

class MobileDateTimePickerFixed {
    constructor(options = {}) {
        this.options = {
            container: options.container || '.fitting-appointment',
            dateInputId: options.dateInputId || 'fitting-date',
            timeSelectId: options.timeSelectId || 'fitting-time',
            minDaysFromNow: options.minDaysFromNow || 2,
            maxDaysFromNow: options.maxDaysFromNow || 30,
            ajaxUrl: options.ajaxUrl || (typeof bmsTyreBooking !== 'undefined' ? bmsTyreBooking.ajaxUrl : 
                     (typeof bmsVehicleLookup !== 'undefined' ? bmsVehicleLookup.ajaxUrl : null)),
            nonce: options.nonce || (typeof bmsTyreBooking !== 'undefined' ? bmsTyreBooking.nonce : 
                   (typeof bmsVehicleLookup !== 'undefined' ? bmsVehicleLookup.nonce : null)),
            ...options
        };
        
        this.selectedDate = null;
        this.selectedTime = null;
        this.availableSlots = [];
        this.isInitialized = false;
        this.useMobilePopup = this.shouldUseMobilePopup();
        
        this.init();
    }
    
    shouldUseMobilePopup() {
        // Only use mobile popup on touch devices or when explicitly requested
        return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
    }
    
    init() {
        if (this.isInitialized) return;
        
        console.log('📱 Initializing Mobile Date/Time Picker (Fixed Version)');
        
        if (this.useMobilePopup) {
            this.createPopupStructure();
            this.enhanceInputsForMobile();
        } else {
            this.enhanceInputsForDesktop();
        }
        
        this.bindEvents();
        this.setupDateLimits();
        this.isInitialized = true;
        
        console.log('✅ Mobile Date/Time Picker initialized successfully');
    }
    
    createPopupStructure() {
        // Remove existing popups to prevent duplicates
        this.removeExistingPopups();
        
        // Create date picker popup
        this.createDatePickerPopup();
        
        // Create time picker popup
        this.createTimePickerPopup();
        
        // Add styles
        this.injectStyles();
    }
    
    removeExistingPopups() {
        const existingPopups = document.querySelectorAll('.mobile-date-popup-fixed, .mobile-time-popup-fixed');
        existingPopups.forEach(popup => popup.remove());
    }
    
    createDatePickerPopup() {
        const datePopup = document.createElement('div');
        datePopup.className = 'mobile-date-popup-fixed';
        datePopup.innerHTML = `
            <div class="popup-overlay"></div>
            <div class="popup-content">
                <div class="popup-header">
                    <h4>📅 Select Appointment Date</h4>
                    <button type="button" class="popup-close">&times;</button>
                </div>
                <div class="calendar-container">
                    <div class="calendar-header">
                        <button type="button" class="nav-button prev-month">‹</button>
                        <div class="current-month"></div>
                        <button type="button" class="nav-button next-month">›</button>
                    </div>
                    <div class="calendar-grid">
                        <div class="day-labels">
                            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span>
                            <span>Thu</span><span>Fri</span><span>Sat</span>
                        </div>
                        <div class="days-grid"></div>
                    </div>
                </div>
                <div class="popup-footer">
                    <button type="button" class="btn-secondary popup-cancel">Cancel</button>
                    <button type="button" class="btn-primary popup-confirm" disabled>Confirm Date</button>
                </div>
            </div>
        `;
        document.body.appendChild(datePopup);
        this.datePopup = datePopup;
    }
    
    createTimePickerPopup() {
        const timePopup = document.createElement('div');
        timePopup.className = 'mobile-time-popup-fixed';
        timePopup.innerHTML = `
            <div class="popup-overlay"></div>
            <div class="popup-content">
                <div class="popup-header">
                    <h4>⏰ Select Appointment Time</h4>
                    <button type="button" class="popup-close">&times;</button>
                </div>
                <div class="time-selection-container">
                    <div class="selected-date-display"></div>
                    <div class="loading-message" style="display: none;">
                        <div class="spinner"></div>
                        <p>Finding available times...</p>
                    </div>
                    <div class="time-slots-grid"></div>
                    <div class="no-slots-message" style="display: none;">
                        <p>😔 No appointment slots available for this date.</p>
                        <p>Please choose a different date.</p>
                    </div>
                </div>
                <div class="popup-footer">
                    <button type="button" class="btn-secondary popup-cancel">Cancel</button>
                    <button type="button" class="btn-primary popup-confirm" disabled>Confirm Time</button>
                </div>
            </div>
        `;
        document.body.appendChild(timePopup);
        this.timePopup = timePopup;
    }
    
    enhanceInputsForMobile() {
        const dateInput = document.getElementById(this.options.dateInputId);
        const timeInput = document.getElementById(this.options.timeSelectId);
        
        if (dateInput) {
            // Add click handler but don't make readonly - allow native fallback
            dateInput.setAttribute('placeholder', 'Tap to select date');
            dateInput.style.cursor = 'pointer';
            
            // Add a wrapper div with an overlay button for mobile
            this.addMobileDateWrapper(dateInput);
        }
        
        if (timeInput) {
            // If it's a select, convert it, otherwise enhance the input
            if (timeInput.tagName.toLowerCase() === 'select') {
                this.replaceTimeSelect(timeInput);
            } else {
                timeInput.setAttribute('placeholder', 'Select date first');
                timeInput.style.cursor = 'pointer';
            }
        }
    }
    
    enhanceInputsForDesktop() {
        const dateInput = document.getElementById(this.options.dateInputId);
        const timeInput = document.getElementById(this.options.timeSelectId);
        
        if (dateInput) {
            // For desktop, use native date picker
            dateInput.type = 'date';
            dateInput.addEventListener('change', () => {
                this.selectedDate = new Date(dateInput.value + 'T00:00:00');
                this.loadTimeSlotsForDesktop();
            });
        }
        
        if (timeInput && timeInput.tagName.toLowerCase() === 'select') {
            timeInput.addEventListener('change', () => {
                this.selectedTime = timeInput.value;
            });
        }
    }
    
    addMobileDateWrapper(dateInput) {
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.display = 'inline-block';
        wrapper.style.width = '100%';
        
        const overlay = document.createElement('div');
        overlay.style.position = 'absolute';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.backgroundColor = 'transparent';
        overlay.style.cursor = 'pointer';
        overlay.style.zIndex = '1';
        
        dateInput.parentNode.insertBefore(wrapper, dateInput);
        wrapper.appendChild(dateInput);
        wrapper.appendChild(overlay);
        
        // Click handler for the overlay
        overlay.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.showDatePicker();
        });
        
        // Also handle direct input clicks as fallback
        dateInput.addEventListener('focus', (e) => {
            if (this.useMobilePopup) {
                e.preventDefault();
                this.showDatePicker();
            }
        });
    }
    
    replaceTimeSelect(timeSelect) {
        const timeInput = document.createElement('input');
        timeInput.type = 'text';
        timeInput.id = this.options.timeSelectId;
        timeInput.setAttribute('readonly', 'readonly');
        timeInput.setAttribute('placeholder', 'Select date first');
        timeInput.style.cursor = 'pointer';
        timeInput.className = timeSelect.className;
        
        // Store original select for reference
        this.originalTimeSelect = timeSelect;
        timeSelect.parentNode.replaceChild(timeInput, timeSelect);
    }
    
    bindEvents() {
        if (this.useMobilePopup) {
            this.bindMobileEvents();
        }
        
        // Always bind time input click
        const timeInput = document.getElementById(this.options.timeSelectId);
        if (timeInput) {
            timeInput.addEventListener('click', () => {
                if (!this.selectedDate) {
                    this.showMessage('Please select a date first', 'warning');
                    return;
                }
                
                if (this.useMobilePopup) {
                    this.showTimePicker();
                } else {
                    this.loadTimeSlotsForDesktop();
                }
            });
        }
    }
    
    bindMobileEvents() {
        if (this.datePopup) {
            // Date popup events
            this.datePopup.querySelectorAll('.popup-close, .popup-cancel').forEach(btn => {
                btn.addEventListener('click', () => this.closeDatePicker());
            });
            
            this.datePopup.querySelector('.popup-overlay').addEventListener('click', () => this.closeDatePicker());
            this.datePopup.querySelector('.prev-month').addEventListener('click', () => this.changeMonth(-1));
            this.datePopup.querySelector('.next-month').addEventListener('click', () => this.changeMonth(1));
            this.datePopup.querySelector('.popup-confirm').addEventListener('click', () => this.confirmDate());
        }
        
        if (this.timePopup) {
            // Time popup events
            this.timePopup.querySelectorAll('.popup-close, .popup-cancel').forEach(btn => {
                btn.addEventListener('click', () => this.closeTimePicker());
            });
            
            this.timePopup.querySelector('.popup-overlay').addEventListener('click', () => this.closeTimePicker());
            this.timePopup.querySelector('.popup-confirm').addEventListener('click', () => this.confirmTime());
        }
    }
    
    setupDateLimits() {
        const dateInput = document.getElementById(this.options.dateInputId);
        if (dateInput && dateInput.type === 'date') {
            const today = new Date();
            const minDate = new Date(today.getTime() + (this.options.minDaysFromNow * 24 * 60 * 60 * 1000));
            const maxDate = new Date(today.getTime() + (this.options.maxDaysFromNow * 24 * 60 * 60 * 1000));
            
            dateInput.min = minDate.toISOString().split('T')[0];
            dateInput.max = maxDate.toISOString().split('T')[0];
        }
    }
    
    showDatePicker() {
        if (!this.datePopup) {
            console.error('Date popup not created');
            return;
        }
        
        this.currentDate = new Date();
        this.currentDate.setDate(this.currentDate.getDate() + this.options.minDaysFromNow);
        this.renderCalendar();
        this.datePopup.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    closeDatePicker() {
        if (this.datePopup) {
            this.datePopup.style.display = 'none';
        }
        document.body.style.overflow = '';
    }
    
    showTimePicker() {
        if (!this.timePopup) {
            console.error('Time popup not created');
            return;
        }
        
        this.timePopup.style.display = 'flex';
        this.loadAvailableSlots();
        document.body.style.overflow = 'hidden';
    }
    
    closeTimePicker() {
        if (this.timePopup) {
            this.timePopup.style.display = 'none';
        }
        document.body.style.overflow = '';
    }
    
    renderCalendar() {
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        const currentMonthElement = this.datePopup.querySelector('.current-month');
        currentMonthElement.textContent = `${monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
        
        const daysGrid = this.datePopup.querySelector('.days-grid');
        daysGrid.innerHTML = '';
        
        const firstDayOfMonth = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
        const lastDayOfMonth = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
        const startingDayOfWeek = firstDayOfMonth.getDay();
        const daysInMonth = lastDayOfMonth.getDate();
        
        const today = new Date();
        const minDate = new Date(today.getTime() + (this.options.minDaysFromNow * 24 * 60 * 60 * 1000));
        const maxDate = new Date(today.getTime() + (this.options.maxDaysFromNow * 24 * 60 * 60 * 1000));
        
        // Add empty cells for days before the first day of the month
        for (let i = 0; i < startingDayOfWeek; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'day-cell empty';
            daysGrid.appendChild(emptyCell);
        }
        
        // Add days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'day-cell';
            dayCell.textContent = day;
            
            const cellDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), day);
            
            // Disable dates outside allowed range
            if (cellDate < minDate || cellDate > maxDate) {
                dayCell.classList.add('disabled');
            } else {
                dayCell.addEventListener('click', () => this.selectDate(cellDate));
            }
            
            // Highlight selected date
            if (this.selectedDate && 
                cellDate.getTime() === this.selectedDate.getTime()) {
                dayCell.classList.add('selected');
            }
            
            daysGrid.appendChild(dayCell);
        }
    }
    
    changeMonth(direction) {
        this.currentDate.setMonth(this.currentDate.getMonth() + direction);
        this.renderCalendar();
    }
    
    selectDate(date) {
        // Remove previous selection
        this.datePopup.querySelectorAll('.day-cell.selected').forEach(cell => {
            cell.classList.remove('selected');
        });
        
        // Add selection to clicked cell
        event.target.classList.add('selected');
        
        this.selectedDate = new Date(date);
        this.datePopup.querySelector('.popup-confirm').disabled = false;
    }
    
    confirmDate() {
        if (!this.selectedDate) return;
        
        const dateInput = document.getElementById(this.options.dateInputId);
        const formattedDate = this.selectedDate.toISOString().split('T')[0];
        const displayDate = this.selectedDate.toLocaleDateString('en-GB');
        
        dateInput.value = formattedDate;
        
        // Reset time selection when date changes
        this.selectedTime = null;
        const timeInput = document.getElementById(this.options.timeSelectId);
        if (timeInput) {
            timeInput.value = '';
            timeInput.setAttribute('placeholder', 'Tap to select time');
        }
        
        this.closeDatePicker();
        this.showMessage(`📅 Date selected: ${displayDate}`, 'success');
        
        console.log('✅ Date confirmed:', formattedDate);
    }
    
    async loadAvailableSlots() {
        if (!this.selectedDate) return;
        
        const loadingMessage = this.timePopup.querySelector('.loading-message');
        const timeSlotsGrid = this.timePopup.querySelector('.time-slots-grid');
        const noSlotsMessage = this.timePopup.querySelector('.no-slots-message');
        const selectedDateDisplay = this.timePopup.querySelector('.selected-date-display');
        
        // Show loading
        loadingMessage.style.display = 'block';
        timeSlotsGrid.style.display = 'none';
        noSlotsMessage.style.display = 'none';
        
        // Display selected date
        selectedDateDisplay.innerHTML = `
            <h5>Selected Date: ${this.selectedDate.toLocaleDateString('en-GB', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            })}</h5>
        `;
        
        try {
            if (!this.options.ajaxUrl || !this.options.nonce) {
                throw new Error('AJAX URL or nonce not available');
            }
            
            const quantity = (window.bmsTyreBooking && window.bmsTyreBooking.selectedQuantity) || 1;
            const formData = new URLSearchParams({
                action: 'bms_get_fitting_slots',
                nonce: this.options.nonce,
                date: this.selectedDate.toISOString().split('T')[0],
                quantity: quantity
            });
            
            console.log('🌐 Loading time slots for:', this.selectedDate.toISOString().split('T')[0]);
            
            const response = await fetch(this.options.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });
            
            const data = await response.json();
            console.log('📡 Time slots response:', data);
            
            if (data.success && data.data && data.data.slots && data.data.slots.length > 0) {
                this.renderTimeSlots(data.data.slots);
            } else {
                this.showNoSlotsMessage(data.data || 'No slots available for this date');
            }
            
        } catch (error) {
            console.error('Error loading time slots:', error);
            this.showNoSlotsMessage('Unable to load available times. Please try again.');
        } finally {
            loadingMessage.style.display = 'none';
        }
    }
    
    async loadTimeSlotsForDesktop() {
        const timeSelect = document.getElementById(this.options.timeSelectId);
        if (!timeSelect || !this.selectedDate) return;
        
        timeSelect.innerHTML = '<option value="">Loading...</option>';
        
        try {
            const quantity = 1;
            const formData = new URLSearchParams({
                action: 'bms_get_fitting_slots',
                nonce: this.options.nonce,
                date: this.selectedDate.toISOString().split('T')[0],
                quantity: quantity
            });
            
            const response = await fetch(this.options.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.data && data.data.slots) {
                timeSelect.innerHTML = '<option value="">Choose time</option>';
                
                data.data.slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = this.formatTime(slot);
                    timeSelect.appendChild(option);
                });
                
                if (data.data.slots.length === 0) {
                    timeSelect.innerHTML = '<option value="">No slots available</option>';
                }
            } else {
                timeSelect.innerHTML = '<option value="">Failed to load slots</option>';
            }
        } catch (error) {
            console.error('Error loading desktop time slots:', error);
            timeSelect.innerHTML = '<option value="">Error loading slots</option>';
        }
    }
    
    renderTimeSlots(slots) {
        const timeSlotsGrid = this.timePopup.querySelector('.time-slots-grid');
        
        timeSlotsGrid.innerHTML = '';
        timeSlotsGrid.style.display = 'grid';
        
        slots.forEach(slot => {
            const timeSlot = document.createElement('button');
            timeSlot.type = 'button';
            timeSlot.className = 'time-slot';
            timeSlot.textContent = this.formatTime(slot);
            timeSlot.dataset.time = slot;
            
            timeSlot.addEventListener('click', () => this.selectTimeSlot(timeSlot));
            
            timeSlotsGrid.appendChild(timeSlot);
        });
        
        console.log(`✅ Rendered ${slots.length} time slots`);
    }
    
    selectTimeSlot(slotElement) {
        // Remove previous selection
        this.timePopup.querySelectorAll('.time-slot.selected').forEach(slot => {
            slot.classList.remove('selected');
        });
        
        // Select this slot
        slotElement.classList.add('selected');
        this.selectedTime = slotElement.dataset.time;
        
        // Enable confirm button
        this.timePopup.querySelector('.popup-confirm').disabled = false;
        
        console.log('✅ Time slot selected:', this.selectedTime);
    }
    
    confirmTime() {
        if (!this.selectedTime) return;
        
        const timeInput = document.getElementById(this.options.timeSelectId);
        const formattedTime = this.formatTime(this.selectedTime);
        
        timeInput.value = this.selectedTime;
        timeInput.setAttribute('data-display', formattedTime);
        
        this.closeTimePicker();
        this.showMessage(`⏰ Time selected: ${formattedTime}`, 'success');
        
        console.log('✅ Time confirmed:', this.selectedTime);
    }
    
    showNoSlotsMessage(customMessage = null) {
        const noSlotsMessage = this.timePopup.querySelector('.no-slots-message');
        
        if (customMessage) {
            noSlotsMessage.innerHTML = `<p>${customMessage}</p>`;
        }
        
        noSlotsMessage.style.display = 'block';
        this.timePopup.querySelector('.time-slots-grid').style.display = 'none';
    }
    
    formatTime(timeString) {
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
    
    showMessage(text, type = 'info') {
        // Create or update message display
        let messageElement = document.querySelector('.mobile-picker-message-fixed');
        
        if (!messageElement) {
            messageElement = document.createElement('div');
            messageElement.className = 'mobile-picker-message-fixed';
            
            // Insert at top of container
            const container = document.querySelector(this.options.container) || document.body;
            container.insertBefore(messageElement, container.firstChild);
        }
        
        messageElement.className = `mobile-picker-message-fixed message-${type}`;
        messageElement.textContent = text;
        messageElement.style.display = 'block';
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (messageElement) {
                messageElement.style.display = 'none';
            }
        }, 3000);
    }
    
    injectStyles() {
        if (document.getElementById('mobile-picker-styles-fixed')) return;
        
        const styles = document.createElement('style');
        styles.id = 'mobile-picker-styles-fixed';
        styles.textContent = `
        /* Fixed Mobile Date/Time Picker Styles */
        .mobile-date-popup-fixed,
        .mobile-time-popup-fixed {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 999999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .mobile-date-popup-fixed .popup-overlay,
        .mobile-time-popup-fixed .popup-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
        }
        
        .mobile-date-popup-fixed .popup-content,
        .mobile-time-popup-fixed .popup-content {
            position: relative;
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: popupSlideInFixed 0.3s ease-out;
        }
        
        @keyframes popupSlideInFixed {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        .popup-header {
            padding: 24px 24px 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .popup-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .popup-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .popup-close:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .calendar-container {
            padding: 16px 24px;
        }
        
        .calendar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        
        .current-month {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .nav-button {
            background: #f3f4f6;
            border: none;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .nav-button:hover {
            background: #e5e7eb;
        }
        
        .calendar-grid {
            margin-bottom: 16px;
        }
        
        .day-labels {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            margin-bottom: 8px;
        }
        
        .day-labels span {
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            padding: 8px 4px;
        }
        
        .days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }
        
        .day-cell {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        
        .day-cell:not(.disabled):not(.empty):hover {
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        
        .day-cell.selected {
            background: #1d4ed8;
            color: white;
            border-color: #1d4ed8;
        }
        
        .day-cell.disabled {
            color: #d1d5db;
            cursor: not-allowed;
        }
        
        .day-cell.empty {
            cursor: default;
        }
        
        .time-selection-container {
            padding: 16px 24px;
            max-height: 50vh;
            overflow-y: auto;
        }
        
        .selected-date-display h5 {
            margin: 0 0 16px 0;
            font-size: 16px;
            color: #1f2937;
            text-align: center;
            padding: 12px;
            background: #f0fdf4;
            border-radius: 8px;
            border: 1px solid #bbf7d0;
        }
        
        .loading-message {
            text-align: center;
            padding: 32px;
        }
        
        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #e5e7eb;
            border-top: 3px solid #1d4ed8;
            border-radius: 50%;
            animation: spinFixed 1s linear infinite;
            margin: 0 auto 16px;
        }
        
        @keyframes spinFixed {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
        }
        
        .time-slot {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px 12px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .time-slot:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
            transform: translateY(-1px);
        }
        
        .time-slot.selected {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(29, 78, 216, 0.3);
        }
        
        .no-slots-message {
            text-align: center;
            padding: 32px;
            color: #6b7280;
        }
        
        .popup-footer {
            padding: 16px 24px 24px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .popup-footer button {
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            min-width: 80px;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .btn-primary {
            background: #1d4ed8;
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: #1e40af;
        }
        
        .btn-primary:disabled {
            background: #d1d5db;
            color: #9ca3af;
            cursor: not-allowed;
        }
        
        .mobile-picker-message-fixed {
            display: none;
            margin: 16px 0;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            animation: messageSlideInFixed 0.3s ease-out;
        }
        
        .message-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .message-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fbbf24;
        }
        
        .message-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        @keyframes messageSlideInFixed {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Mobile responsive adjustments */
        @media (max-width: 480px) {
            .mobile-date-popup-fixed,
            .mobile-time-popup-fixed {
                padding: 10px;
            }
            
            .mobile-date-popup-fixed .popup-content,
            .mobile-time-popup-fixed .popup-content {
                max-width: none;
                width: 100%;
                border-radius: 12px;
            }
            
            .time-slots-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
        }
        `;
        document.head.appendChild(styles);
    }
    
    // Public methods
    getSelectedDateTime() {
        return {
            date: this.selectedDate,
            time: this.selectedTime,
            formattedDate: this.selectedDate ? this.selectedDate.toISOString().split('T')[0] : null,
            displayDate: this.selectedDate ? this.selectedDate.toLocaleDateString('en-GB') : null,
            displayTime: this.selectedTime ? this.formatTime(this.selectedTime) : null
        };
    }
    
    reset() {
        this.selectedDate = null;
        this.selectedTime = null;
        
        const dateInput = document.getElementById(this.options.dateInputId);
        const timeInput = document.getElementById(this.options.timeSelectId);
        
        if (dateInput) {
            dateInput.value = '';
        }
        
        if (timeInput) {
            timeInput.value = '';
            timeInput.setAttribute('placeholder', 'Select date first');
        }
    }
}

// Replace the original class and auto-initialize
if (typeof MobileDateTimePicker !== 'undefined') {
    window.MobileDateTimePickerOriginal = MobileDateTimePicker;
}
window.MobileDateTimePicker = MobileDateTimePickerFixed;

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (document.querySelector('#fitting-appointment') || document.querySelector('.fitting-appointment')) {
            // Initialize the fixed version
            window.mobileDateTimePicker = new MobileDateTimePickerFixed({
                container: '.fitting-appointment',
                dateInputId: 'fitting-date',
                timeSelectId: 'fitting-time'
            });
            
            console.log('✅ Fixed Mobile-friendly date/time picker initialized!');
        }
    }, 500);
});
