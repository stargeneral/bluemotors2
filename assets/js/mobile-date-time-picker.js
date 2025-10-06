/**
 * Mobile-Friendly Date & Time Picker for Tyre Booking
 * Blue Motors Southampton - Enhanced Mobile UX
 * 
 * Features:
 * - Touch-friendly mobile-first design
 * - Popup calendar for date selection
 * - Popup time slots with real availability
 * - Integration with existing booking system
 */

class MobileDateTimePicker {
    constructor(options = {}) {
        this.options = {
            container: options.container || '.fitting-appointment',
            dateInputId: options.dateInputId || 'fitting-date',
            timeSelectId: options.timeSelectId || 'fitting-time',
            minDaysFromNow: options.minDaysFromNow || 2,
            maxDaysFromNow: options.maxDaysFromNow || 30,
            ajaxUrl: bmsTyreBooking?.ajaxUrl || bmsVehicleLookup?.ajaxUrl,
            nonce: bmsTyreBooking?.nonce || bmsVehicleLookup?.nonce,
            ...options
        };
        
        this.selectedDate = null;
        this.selectedTime = null;
        this.availableSlots = [];
        this.isInitialized = false;
        
        this.init();
    }
    
    init() {
        if (this.isInitialized) return;
        
        this.createPopupStructure();
        this.enhanceInputs();
        this.bindEvents();
        this.isInitialized = true;
        
        console.log('📱 Mobile Date/Time Picker initialized for tyre bookings');
    }
    
    createPopupStructure() {
        // Remove existing popups to prevent duplicates
        this.removeExistingPopups();
        
        // Create date picker popup
        this.createDatePickerPopup();
        
        // Create time picker popup
        this.createTimePickerPopup();
        
        // Add mobile-optimized styles
        this.injectStyles();
    }
    
    removeExistingPopups() {
        const existingPopups = document.querySelectorAll('.mobile-date-popup, .mobile-time-popup');
        existingPopups.forEach(popup => popup.remove());
    }
    
    createDatePickerPopup() {
        const datePopup = document.createElement('div');
        datePopup.className = 'mobile-date-popup';
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
        timePopup.className = 'mobile-time-popup';
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
    
    enhanceInputs() {
        const dateInput = document.getElementById(this.options.dateInputId);
        const timeSelect = document.getElementById(this.options.timeSelectId);
        
        if (dateInput) {
            // Make input readonly to force use of popup
            dateInput.setAttribute('readonly', 'readonly');
            dateInput.setAttribute('placeholder', 'Tap to select date');
            dateInput.style.cursor = 'pointer';
        }
        
        if (timeSelect) {
            // Replace select with a styled input
            this.replaceTimeSelect(timeSelect);
        }
    }
    
    replaceTimeSelect(timeSelect) {
        const timeInput = document.createElement('input');
        timeInput.type = 'text';
        timeInput.id = this.options.timeSelectId;
        timeInput.setAttribute('readonly', 'readonly');
        timeInput.setAttribute('placeholder', 'Select date first');
        timeInput.style.cursor = 'pointer';
        timeInput.className = timeSelect.className;
        
        timeSelect.parentNode.replaceChild(timeInput, timeSelect);
    }
    
    bindEvents() {
        // Date input click
        const dateInput = document.getElementById(this.options.dateInputId);
        if (dateInput) {
            dateInput.addEventListener('click', () => this.showDatePicker());
        }
        
        // Time input click
        const timeInput = document.getElementById(this.options.timeSelectId);
        if (timeInput) {
            timeInput.addEventListener('click', () => {
                if (this.selectedDate) {
                    this.showTimePicker();
                } else {
                    this.showMessage('Please select a date first', 'warning');
                }
            });
        }
        
        // Date popup events
        this.bindDatePopupEvents();
        
        // Time popup events
        this.bindTimePopupEvents();
    }
    
    bindDatePopupEvents() {
        const popup = this.datePopup;
        
        // Close buttons
        popup.querySelectorAll('.popup-close, .popup-cancel').forEach(btn => {
            btn.addEventListener('click', () => this.closeDatePicker());
        });
        
        // Overlay click
        popup.querySelector('.popup-overlay').addEventListener('click', () => this.closeDatePicker());
        
        // Month navigation
        popup.querySelector('.prev-month').addEventListener('click', () => this.changeMonth(-1));
        popup.querySelector('.next-month').addEventListener('click', () => this.changeMonth(1));
        
        // Confirm button
        popup.querySelector('.popup-confirm').addEventListener('click', () => this.confirmDate());
    }
    
    bindTimePopupEvents() {
        const popup = this.timePopup;
        
        // Close buttons
        popup.querySelectorAll('.popup-close, .popup-cancel').forEach(btn => {
            btn.addEventListener('click', () => this.closeTimePicker());
        });
        
        // Overlay click
        popup.querySelector('.popup-overlay').addEventListener('click', () => this.closeTimePicker());
        
        // Confirm button
        popup.querySelector('.popup-confirm').addEventListener('click', () => this.confirmTime());
    }
    
    showDatePicker() {
        this.currentDate = new Date();
        this.currentDate.setDate(this.currentDate.getDate() + this.options.minDaysFromNow);
        this.renderCalendar();
        this.datePopup.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    closeDatePicker() {
        this.datePopup.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    showTimePicker() {
        this.timePopup.style.display = 'flex';
        this.loadAvailableSlots();
        document.body.style.overflow = 'hidden';
    }
    
    closeTimePicker() {
        this.timePopup.style.display = 'none';
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
        const formattedDate = this.selectedDate.toISOString().split('T')[0]; // YYYY-MM-DD
        const displayDate = this.selectedDate.toLocaleDateString('en-GB'); // DD/MM/YYYY
        
        dateInput.value = formattedDate;
        dateInput.setAttribute('data-display', displayDate);
        dateInput.style.backgroundColor = '#e6f3ff';
        
        // Reset time selection when date changes
        this.selectedTime = null;
        const timeInput = document.getElementById(this.options.timeSelectId);
        if (timeInput) {
            timeInput.value = '';
            timeInput.setAttribute('placeholder', 'Tap to select time');
            timeInput.style.backgroundColor = '';
        }
        
        this.closeDatePicker();
        this.showMessage(`📅 Date selected: ${displayDate}`, 'success');
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
            const quantity = window.bmsTyreBooking?.selectedQuantity || 1;
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
            
            if (data.success && data.data.slots && data.data.slots.length > 0) {
                this.renderTimeSlots(data.data.slots);
            } else {
                this.showNoSlotsMessage();
            }
            
        } catch (error) {
            console.error('Error loading time slots:', error);
            this.showNoSlotsMessage('Unable to load available times. Please try again.');
        } finally {
            loadingMessage.style.display = 'none';
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
        
        // Add helpful message
        const helpMessage = document.createElement('div');
        helpMessage.className = 'time-slots-help';
        helpMessage.innerHTML = `
            <p><strong>💡 Tip:</strong> Tap a time slot to select your preferred appointment time.</p>
            <p>All times shown are available for booking.</p>
        `;
        timeSlotsGrid.appendChild(helpMessage);
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
    }
    
    confirmTime() {
        if (!this.selectedTime) return;
        
        const timeInput = document.getElementById(this.options.timeSelectId);
        const formattedTime = this.formatTime(this.selectedTime);
        
        timeInput.value = this.selectedTime;
        timeInput.setAttribute('data-display', formattedTime);
        timeInput.style.backgroundColor = '#e6f3ff';
        
        this.closeTimePicker();
        this.showMessage(`⏰ Time selected: ${formattedTime}`, 'success');
        
        // Trigger any existing change events
        if (window.bmsTyreBooking && typeof window.bmsTyreBooking.updateAppointmentSummary === 'function') {
            window.bmsTyreBooking.updateAppointmentSummary();
        }
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
        let messageElement = document.querySelector('.mobile-picker-message');
        
        if (!messageElement) {
            messageElement = document.createElement('div');
            messageElement.className = 'mobile-picker-message';
            
            // Insert after fitting appointment section
            const container = document.querySelector(this.options.container);
            if (container) {
                container.appendChild(messageElement);
            } else {
                document.body.appendChild(messageElement);
            }
        }
        
        messageElement.className = `mobile-picker-message message-${type}`;
        messageElement.textContent = text;
        messageElement.style.display = 'block';
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (messageElement) {
                messageElement.style.display = 'none';
            }
        }, 3000);
    }
    
    // Public methods for external access
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
            dateInput.style.backgroundColor = '';
        }
        
        if (timeInput) {
            timeInput.value = '';
            timeInput.style.backgroundColor = '';
            timeInput.setAttribute('placeholder', 'Select date first');
        }
    }
    
    injectStyles() {
        if (document.getElementById('mobile-picker-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'mobile-picker-styles';
        styles.textContent = this.getStyles();
        document.head.appendChild(styles);
    }
    
    getStyles() {
        return `
        /* Mobile Date/Time Picker Styles */
        .mobile-date-popup,
        .mobile-time-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .mobile-date-popup .popup-overlay,
        .mobile-time-popup .popup-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
        }
        
        .mobile-date-popup .popup-content,
        .mobile-time-popup .popup-content {
            position: relative;
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: popupSlideIn 0.3s ease-out;
        }
        
        @keyframes popupSlideIn {
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
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }
        
        @keyframes spin {
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
        
        .time-slots-help {
            grid-column: 1 / -1;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }
        
        .time-slots-help p {
            margin: 0;
            font-size: 14px;
            color: #92400e;
        }
        
        .time-slots-help p:first-child {
            margin-bottom: 8px;
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
        
        .mobile-picker-message {
            display: none;
            margin: 16px 0;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            animation: messageSlideIn 0.3s ease-out;
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
        
        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Enhanced input styling */
        #fitting-date,
        #fitting-time {
            background: #f9fafb !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            padding: 16px !important;
            font-size: 16px !important;
            transition: all 0.2s ease !important;
            box-sizing: border-box !important;
        }
        
        #fitting-date:focus,
        #fitting-time:focus {
            outline: none !important;
            border-color: #1d4ed8 !important;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1) !important;
        }
        
        /* Mobile responsive adjustments */
        @media (max-width: 480px) {
            .mobile-date-popup,
            .mobile-time-popup {
                padding: 10px;
            }
            
            .mobile-date-popup .popup-content,
            .mobile-time-popup .popup-content {
                max-width: none;
                width: 100%;
                border-radius: 12px;
            }
            
            .popup-header {
                padding: 20px 20px 12px;
            }
            
            .popup-header h4 {
                font-size: 16px;
            }
            
            .calendar-container,
            .time-selection-container {
                padding: 12px 20px;
            }
            
            .time-slots-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .time-slot {
                padding: 14px 10px;
                font-size: 13px;
            }
            
            .popup-footer {
                padding: 12px 20px 20px;
                gap: 10px;
            }
            
            .popup-footer button {
                flex: 1;
                padding: 14px 16px;
            }
        }
        
        /* Very small screens */
        @media (max-width: 360px) {
            .day-labels span {
                font-size: 11px;
                padding: 6px 2px;
            }
            
            .day-cell {
                font-size: 13px;
            }
        }
        `;
    }
}

// Auto-initialize when DOM is ready (disabled for tyre search - use regular calendar)
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're in a tyre search context - if so, skip mobile picker
    if (document.querySelector('#fitting-appointment') || document.querySelector('.fitting-appointment')) {
        console.log('📅 Tyre booking detected - using unified calendar system (mobile picker disabled)');
        return; // Don't initialize mobile picker for tyre booking
    }
    
    // Check if we should use mobile picker for other forms
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                     window.innerWidth <= 768;
    
    // Only initialize mobile picker on mobile devices or if forced, and NOT for tyre booking
    const forceMobile = new URLSearchParams(window.location.search).get('mobile_picker') === 'true';
    
    if ((isMobile || forceMobile) && !document.querySelector('[id*="tyre"]')) {
        // Wait for other booking forms to be ready
        setTimeout(() => {
            if (document.querySelector('.booking-form') && !document.querySelector('#fitting-appointment')) {
                window.mobileDateTimePicker = new MobileDateTimePicker({
                    container: '.booking-form',
                    dateInputId: 'appointment-date',
                    timeSelectId: 'appointment-time'
                });
                
                console.log('📱 Mobile-friendly date/time picker ready for other booking forms!');
            }
        }, 1000);
    } else {
        console.log('📅 Desktop calendar mode - mobile picker disabled');
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MobileDateTimePicker;
}
