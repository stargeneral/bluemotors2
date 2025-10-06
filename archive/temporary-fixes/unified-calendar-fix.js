/**
 * Unified Calendar Fix for Blue Motors Southampton
 * Consolidates all calendar functionality and eliminates conflicts
 * 
 * This replaces all other calendar scripts to prevent conflicts
 */

(function() {
    'use strict';
    
    console.log('🔧 Unified Calendar Fix: Loading...');
    
    // Prevent multiple initializations
    if (window.BMSUnifiedCalendarFix) {
        console.log('⚠️ Unified Calendar Fix already loaded');
        return;
    }
    
    class BMSUnifiedCalendarFix {
        constructor() {
            this.isInitialized = false;
            this.selectedDate = null;
            this.selectedTime = null;
            this.dateInput = null;
            this.timeInput = null;
            this.isMobile = this.detectMobile();
            
            // Configuration
            this.config = {
                dateInputId: 'fitting-date',
                timeSelectId: 'fitting-time',
                minDaysFromNow: 2,
                maxDaysFromNow: 30,
                ajaxUrl: this.getAjaxUrl(),
                nonce: this.getNonce()
            };
            
            this.init();
        }
        
        detectMobile() {
            return /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                   ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
        }
        
        getAjaxUrl() {
            if (typeof bmsVehicleLookup !== 'undefined' && bmsVehicleLookup.ajaxUrl) {
                return bmsVehicleLookup.ajaxUrl;
            }
            if (typeof bmsTyreBooking !== 'undefined' && bmsTyreBooking.ajaxUrl) {
                return bmsTyreBooking.ajaxUrl;
            }
            return '/wp-admin/admin-ajax.php';
        }
        
        getNonce() {
            if (typeof bmsVehicleLookup !== 'undefined' && bmsVehicleLookup.nonce) {
                return bmsVehicleLookup.nonce;
            }
            if (typeof bmsTyreBooking !== 'undefined' && bmsTyreBooking.nonce) {
                return bmsTyreBooking.nonce;
            }
            return null;
        }
        
        init() {
            if (this.isInitialized) return;
            
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeCalendar());
            } else {
                this.initializeCalendar();
            }
            
            // Start continuous checking for the date input field
            this.startContinuousCheck();
        }
        
        startContinuousCheck() {
            console.log('🔄 Starting continuous calendar check...');
            
            // Check every 2 seconds for the date input field
            this.checkInterval = setInterval(() => {
                const dateInput = document.getElementById(this.config.dateInputId);
                console.log('🔍 Checking for date input field...', dateInput ? 'FOUND' : 'NOT FOUND');
                
                if (dateInput) {
                    console.log('📅 Date input element found:', dateInput);
                    console.log('📅 Current initialization status:', this.isInitialized);
                    
                    if (!this.isInitialized) {
                        console.log('🎯 Date input found via continuous check, initializing calendar...');
                        clearInterval(this.checkInterval);
                        this.initializeCalendarForBookingForm();
                    } else {
                        // Check if the calendar is actually working by looking for event listeners
                        const hasClickHandler = dateInput.onclick || dateInput._eventListeners || 
                                              dateInput.getAttribute('data-calendar-initialized');
                        
                        if (!hasClickHandler) {
                            console.log('🔧 Date input found but calendar not properly attached, re-initializing...');
                            this.isInitialized = false;
                            this.initializeCalendarForBookingForm();
                        } else {
                            console.log('✅ Calendar appears to be properly initialized');
                        }
                    }
                } else {
                    // Also check for the fitting appointment section
                    const fittingSection = document.getElementById('fitting-appointment');
                    if (fittingSection) {
                        const isVisible = fittingSection.style.display !== 'none' && 
                                        fittingSection.offsetParent !== null;
                        console.log('📋 Fitting appointment section found, visible:', isVisible);
                    }
                }
            }, 2000);
            
            // Stop checking after 10 minutes to allow for longer booking flows
            setTimeout(() => {
                if (this.checkInterval) {
                    clearInterval(this.checkInterval);
                    console.log('⏰ Continuous check stopped after 10 minutes');
                }
            }, 600000);
        }
        
        initializeCalendar() {
            console.log('📅 Unified Calendar Fix: Initializing...');
            
            // Find the date and time inputs
            this.dateInput = document.getElementById(this.config.dateInputId);
            this.timeInput = document.getElementById(this.config.timeSelectId);
            
            if (!this.dateInput) {
                console.warn('⚠️ Date input not found initially, will retry when booking form appears');
                this.setupBookingFormObserver();
                return;
            }
            
            // Clean up any existing calendar instances
            this.cleanupExistingCalendars();
            
            // Set up the calendar based on device type
            if (this.isMobile) {
                this.setupMobileCalendar();
            } else {
                this.setupDesktopCalendar();
            }
            
            // Set up time selection
            this.setupTimeSelection();
            
            // Add styles
            this.injectStyles();
            
            this.isInitialized = true;
            console.log('✅ Unified Calendar Fix: Initialization complete!');
        }
        
        setupBookingFormObserver() {
            console.log('👀 Setting up booking form observer...');
            
            // Watch for the booking form to become visible
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    // Check for style changes (display property)
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        const target = mutation.target;
                        if (target.id === 'fitting-appointment' && target.style.display !== 'none') {
                            console.log('📅 Booking form is now visible, initializing calendar...');
                            observer.disconnect();
                            setTimeout(() => this.initializeCalendarForBookingForm(), 200);
                        }
                    }
                    
                    // Also check for new nodes being added (in case the form is dynamically created)
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                // Check if the added node is the fitting appointment section
                                if (node.id === 'fitting-appointment' || node.querySelector('#fitting-appointment')) {
                                    console.log('📅 Booking form added to DOM, initializing calendar...');
                                    setTimeout(() => this.initializeCalendarForBookingForm(), 200);
                                }
                                
                                // Check if the added node contains the date input
                                if (node.querySelector('#fitting-date')) {
                                    console.log('📅 Date input found in new content, initializing calendar...');
                                    setTimeout(() => this.initializeCalendarForBookingForm(), 200);
                                }
                            }
                        });
                    }
                });
            });
            
            // Start observing the fitting appointment section
            const fittingSection = document.getElementById('fitting-appointment');
            if (fittingSection) {
                observer.observe(fittingSection, {
                    attributes: true,
                    attributeFilter: ['style']
                });
                console.log('✅ Observer set up for fitting appointment section');
            }
            
            // Always observe the document body for dynamic content
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style']
            });
            console.log('✅ Observer set up for document body');
            
            // Also listen for the "Book Fitting Appointment" button click
            this.setupBookingButtonListener();
        }
        
        setupBookingButtonListener() {
            // Listen for clicks on the booking button
            document.addEventListener('click', (e) => {
                if (e.target.id === 'btn-continue-to-booking' || 
                    e.target.textContent.includes('Book Fitting Appointment')) {
                    console.log('📅 Booking button clicked, will initialize calendar...');
                    // Try multiple times with different delays to ensure the form is visible
                    setTimeout(() => this.initializeCalendarForBookingForm(), 200);
                    setTimeout(() => this.initializeCalendarForBookingForm(), 500);
                    setTimeout(() => this.initializeCalendarForBookingForm(), 1000);
                    setTimeout(() => this.initializeCalendarForBookingForm(), 1500);
                }
            });
            
            // Also listen for any clicks that might trigger the booking form
            document.addEventListener('click', (e) => {
                // Check if any element with booking-related text was clicked
                const clickedText = e.target.textContent || '';
                if (clickedText.toLowerCase().includes('book') || 
                    clickedText.toLowerCase().includes('appointment') ||
                    clickedText.toLowerCase().includes('fitting')) {
                    console.log('📅 Potential booking trigger clicked, checking for calendar...');
                    setTimeout(() => this.checkAndInitializeCalendar(), 300);
                    setTimeout(() => this.checkAndInitializeCalendar(), 800);
                }
            });
        }
        
        checkAndInitializeCalendar() {
            // Check if the date input exists and initialize if needed
            const dateInput = document.getElementById(this.config.dateInputId);
            if (dateInput && !this.isInitialized) {
                console.log('📅 Date input found, initializing calendar...');
                this.initializeCalendarForBookingForm();
            }
        }
        
        initializeCalendarForBookingForm() {
            console.log('� Initializing calendar for booking form...');
            
            // Find the date and time inputs again
            this.dateInput = document.getElementById(this.config.dateInputId);
            this.timeInput = document.getElementById(this.config.timeSelectId);
            
            if (!this.dateInput) {
                console.warn('⚠️ Date input still not found after booking form appeared');
                return;
            }
            
            console.log('✅ Date input found:', this.dateInput);
            
            // Clean up any existing calendar instances
            this.cleanupExistingCalendars();
            
            // Set up the calendar based on device type
            if (this.isMobile) {
                this.setupMobileCalendar();
            } else {
                this.setupDesktopCalendar();
            }
            
            // Set up time selection
            this.setupTimeSelection();
            
            // Add styles if not already added
            this.injectStyles();
            
            this.isInitialized = true;
            console.log('✅ Calendar initialized for booking form!');
        }
        
        cleanupExistingCalendars() {
            // Remove existing jQuery UI datepicker if present
            if (typeof jQuery !== 'undefined' && this.dateInput && jQuery(this.dateInput).hasClass('hasDatepicker')) {
                jQuery(this.dateInput).datepicker('destroy');
                console.log('🧹 Removed existing jQuery UI datepicker');
            }
            
            // Remove existing mobile popups
            const existingPopups = document.querySelectorAll('.mobile-date-popup, .mobile-time-popup, .mobile-date-popup-fixed, .mobile-time-popup-fixed');
            existingPopups.forEach(popup => popup.remove());
            
            // Clear any existing event listeners by cloning the elements
            if (this.dateInput) {
                const newDateInput = this.dateInput.cloneNode(true);
                this.dateInput.parentNode.replaceChild(newDateInput, this.dateInput);
                this.dateInput = newDateInput;
            }
            
            if (this.timeInput) {
                const newTimeInput = this.timeInput.cloneNode(true);
                this.timeInput.parentNode.replaceChild(newTimeInput, this.timeInput);
                this.timeInput = newTimeInput;
            }
        }
        
        setupMobileCalendar() {
            console.log('� Setting up mobile calendar');
            
            // Make date input readonly and add click handler
            this.dateInput.setAttribute('readonly', 'readonly');
            this.dateInput.setAttribute('placeholder', 'Tap to select date');
            this.dateInput.style.cursor = 'pointer';
            
            // Add click handler
            this.dateInput.addEventListener('click', (e) => {
                e.preventDefault();
                this.showMobileDatePicker();
            });
            
            // Prevent focus from opening native picker
            this.dateInput.addEventListener('focus', (e) => {
                e.preventDefault();
                this.dateInput.blur();
                this.showMobileDatePicker();
            });
        }
        
        setupDesktopCalendar() {
            console.log('🖥️ Setting up desktop calendar');
            
            // Check if jQuery UI is available
            if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined' && typeof jQuery.fn.datepicker !== 'undefined') {
                this.setupjQueryUIDatepicker();
            } else {
                this.setupNativeDatePicker();
            }
        }
        
        setupjQueryUIDatepicker() {
            console.log('� Setting up jQuery UI datepicker');
            
            const $dateInput = jQuery(this.dateInput);
            
            // Configure datepicker
            $dateInput.datepicker({
                dateFormat: 'DD, dd MM yy',
                minDate: this.config.minDaysFromNow,
                maxDate: this.config.maxDaysFromNow,
                changeMonth: true,
                changeYear: true,
                showAnim: 'slideDown',
                showButtonPanel: true,
                beforeShow: (input, inst) => {
                    setTimeout(() => {
                        if (inst.dpDiv) {
                            inst.dpDiv.css({
                                'z-index': 9999,
                                'box-shadow': '0 5px 15px rgba(0,0,0,0.3)',
                                'border': '1px solid #1d4ed8'
                            });
                        }
                    }, 0);
                },
                onSelect: (dateText, inst) => {
                    const selectedDate = $dateInput.datepicker('getDate');
                    this.selectedDate = selectedDate;
                    this.onDateSelected(selectedDate);
                }
            });
        }
        
        setupNativeDatePicker() {
            console.log('📅 Setting up native date picker');
            
            this.dateInput.type = 'date';
            
            // Set date limits
            const today = new Date();
            const minDate = new Date(today.getTime() + (this.config.minDaysFromNow * 24 * 60 * 60 * 1000));
            const maxDate = new Date(today.getTime() + (this.config.maxDaysFromNow * 24 * 60 * 60 * 1000));
            
            this.dateInput.min = minDate.toISOString().split('T')[0];
            this.dateInput.max = maxDate.toISOString().split('T')[0];
            
            // Add change handler
            this.dateInput.addEventListener('change', () => {
                if (this.dateInput.value) {
                    this.selectedDate = new Date(this.dateInput.value + 'T00:00:00');
                    this.onDateSelected(this.selectedDate);
                }
            });
        }
        
        setupTimeSelection() {
            if (!this.timeInput) {
                console.warn('⚠️ Time input not found:', this.config.timeSelectId);
                return;
            }
            
            // Convert select to input if needed
            if (this.timeInput.tagName.toLowerCase() === 'select') {
                this.convertSelectToInput();
            }
            
            // Set up time input
            this.timeInput.setAttribute('readonly', 'readonly');
            this.timeInput.setAttribute('placeholder', 'Select date first');
            this.timeInput.style.cursor = 'pointer';
            
            // Add click handler
            this.timeInput.addEventListener('click', () => {
                if (!this.selectedDate) {
                    this.showMessage('Please select a date first', 'warning');
                    return;
                }
                
                if (this.isMobile) {
                    this.showMobileTimePicker();
                } else {
                    this.showDesktopTimeSlots();
                }
            });
        }
        
        convertSelectToInput() {
            const newInput = document.createElement('input');
            newInput.type = 'text';
            newInput.id = this.timeInput.id;
            newInput.className = this.timeInput.className;
            newInput.name = this.timeInput.name;
            
            this.timeInput.parentNode.replaceChild(newInput, this.timeInput);
            this.timeInput = newInput;
        }
        
        onDateSelected(date) {
            console.log('📅 Date selected:', date);
            
            // Format date for display
            const displayDate = date.toLocaleDateString('en-GB');
            this.showMessage(`📅 Date selected: ${displayDate}`, 'success');
            
            // Reset time selection
            this.selectedTime = null;
            if (this.timeInput) {
                this.timeInput.value = '';
                this.timeInput.setAttribute('placeholder', 'Tap to select time');
            }
            
            // Load time slots
            this.loadTimeSlots(date);
        }
        
        async loadTimeSlots(date) {
            if (!this.config.ajaxUrl || !this.config.nonce) {
                console.warn('⚠️ Cannot load time slots - missing AJAX URL or nonce');
                this.showFallbackTimeSlots();
                return;
            }
            
            try {
                const formData = new URLSearchParams({
                    action: 'bms_get_fitting_slots',
                    nonce: this.config.nonce,
                    date: date.toISOString().split('T')[0],
                    quantity: 1
                });
                
                const response = await fetch(this.config.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.data && data.data.slots) {
                    this.availableSlots = data.data.slots;
                    console.log('✅ Time slots loaded:', this.availableSlots.length);
                } else {
                    console.warn('⚠️ No time slots returned from server');
                    this.showFallbackTimeSlots();
                }
            } catch (error) {
                console.error('❌ Error loading time slots:', error);
                this.showFallbackTimeSlots();
            }
        }
        
        showFallbackTimeSlots() {
            // Provide fallback time slots
            this.availableSlots = [
                '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
                '15:00', '15:30', '16:00', '16:30', '17:00'
            ];
            console.log('📋 Using fallback time slots');
        }
        
        showMobileDatePicker() {
            // Create mobile date picker popup
            this.createMobileDatePopup();
            this.renderMobileCalendar();
            this.mobileDatePopup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        showMobileTimePicker() {
            // Create mobile time picker popup
            this.createMobileTimePopup();
            this.renderMobileTimeSlots();
            this.mobileTimePopup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        showDesktopTimeSlots() {
            // Create desktop time slots dropdown
            this.createDesktopTimeDropdown();
        }
        
        createMobileDatePopup() {
            if (this.mobileDatePopup) return;
            
            const popup = document.createElement('div');
            popup.className = 'bms-unified-date-popup';
            popup.innerHTML = `
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
            
            document.body.appendChild(popup);
            this.mobileDatePopup = popup;
            
            // Bind events
            popup.querySelector('.popup-overlay').addEventListener('click', () => this.closeMobileDatePicker());
            popup.querySelector('.popup-close').addEventListener('click', () => this.closeMobileDatePicker());
            popup.querySelector('.popup-cancel').addEventListener('click', () => this.closeMobileDatePicker());
            popup.querySelector('.popup-confirm').addEventListener('click', () => this.confirmMobileDate());
            popup.querySelector('.prev-month').addEventListener('click', () => this.changeMobileMonth(-1));
            popup.querySelector('.next-month').addEventListener('click', () => this.changeMobileMonth(1));
        }
        
        createMobileTimePopup() {
            if (this.mobileTimePopup) return;
            
            const popup = document.createElement('div');
            popup.className = 'bms-unified-time-popup';
            popup.innerHTML = `
                <div class="popup-overlay"></div>
                <div class="popup-content">
                    <div class="popup-header">
                        <h4>⏰ Select Appointment Time</h4>
                        <button type="button" class="popup-close">&times;</button>
                    </div>
                    <div class="time-container">
                        <div class="selected-date-display"></div>
                        <div class="time-slots-grid"></div>
                    </div>
                    <div class="popup-footer">
                        <button type="button" class="btn-secondary popup-cancel">Cancel</button>
                        <button type="button" class="btn-primary popup-confirm" disabled>Confirm Time</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(popup);
            this.mobileTimePopup = popup;
            
            // Bind events
            popup.querySelector('.popup-overlay').addEventListener('click', () => this.closeMobileTimePicker());
            popup.querySelector('.popup-close').addEventListener('click', () => this.closeMobileTimePicker());
            popup.querySelector('.popup-cancel').addEventListener('click', () => this.closeMobileTimePicker());
            popup.querySelector('.popup-confirm').addEventListener('click', () => this.confirmMobileTime());
        }
        
        renderMobileCalendar() {
            const currentDate = this.selectedDate || new Date();
            currentDate.setDate(currentDate.getDate() + this.config.minDaysFromNow);
            
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            
            const currentMonthElement = this.mobileDatePopup.querySelector('.current-month');
            currentMonthElement.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
            
            const daysGrid = this.mobileDatePopup.querySelector('.days-grid');
            daysGrid.innerHTML = '';
            
            const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const lastDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            const startingDayOfWeek = firstDayOfMonth.getDay();
            const daysInMonth = lastDayOfMonth.getDate();
            
            const today = new Date();
            const minDate = new Date(today.getTime() + (this.config.minDaysFromNow * 24 * 60 * 60 * 1000));
            const maxDate = new Date(today.getTime() + (this.config.maxDaysFromNow * 24 * 60 * 60 * 1000));
            
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
                
                const cellDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                
                if (cellDate < minDate || cellDate > maxDate) {
                    dayCell.classList.add('disabled');
                } else {
                    dayCell.addEventListener('click', (event) => this.selectMobileDate(cellDate, event.target));
                }
                
                daysGrid.appendChild(dayCell);
            }
            
            this.currentMobileDate = currentDate;
        }
        
        renderMobileTimeSlots() {
            const selectedDateDisplay = this.mobileTimePopup.querySelector('.selected-date-display');
            selectedDateDisplay.innerHTML = `
                <h5>Selected Date: ${this.selectedDate.toLocaleDateString('en-GB', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                })}</h5>
            `;
            
            const timeSlotsGrid = this.mobileTimePopup.querySelector('.time-slots-grid');
            timeSlotsGrid.innerHTML = '';
            
            if (!this.availableSlots || this.availableSlots.length === 0) {
                this.showFallbackTimeSlots();
            }
            
            this.availableSlots.forEach(slot => {
                const timeSlot = document.createElement('button');
                timeSlot.type = 'button';
                timeSlot.className = 'time-slot';
                timeSlot.textContent = this.formatTime(slot);
                timeSlot.dataset.time = slot;
                
                timeSlot.addEventListener('click', () => this.selectMobileTime(timeSlot));
                
                timeSlotsGrid.appendChild(timeSlot);
            });
        }
        
        createDesktopTimeDropdown() {
            // Create a temporary dropdown for desktop time selection
            const dropdown = document.createElement('select');
            dropdown.className = 'bms-time-dropdown';
            dropdown.style.cssText = `
                position: absolute;
                z-index: 1000;
                background: white;
                border: 2px solid #1d4ed8;
                border-radius: 8px;
                padding: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                max-height: 200px;
                overflow-y: auto;
            `;
            
            // Add default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select time';
            dropdown.appendChild(defaultOption);
            
            // Add time options
            if (!this.availableSlots || this.availableSlots.length === 0) {
                this.showFallbackTimeSlots();
            }
            
            this.availableSlots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot;
                option.textContent = this.formatTime(slot);
                dropdown.appendChild(option);
            });
            
            // Position near the time input
            const rect = this.timeInput.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + window.scrollY + 5) + 'px';
            dropdown.style.left = (rect.left + window.scrollX) + 'px';
            dropdown.style.minWidth = rect.width + 'px';
            
            // Handle selection
            dropdown.onchange = () => {
                if (dropdown.value) {
                    this.selectedTime = dropdown.value;
                    this.timeInput.value = dropdown.value;
                    this.showMessage(`⏰ Time selected: ${this.formatTime(dropdown.value)}`, 'success');
                    document.body.removeChild(dropdown);
                }
            };
            
            // Close when clicking outside
            const closeDropdown = (e) => {
                if (!dropdown.contains(e.target) && e.target !== this.timeInput) {
                    if (document.body.contains(dropdown)) {
                        document.body.removeChild(dropdown);
                    }
                    document.removeEventListener('click', closeDropdown);
                }
            };
            
            setTimeout(() => {
                document.addEventListener('click', closeDropdown);
            }, 100);
            
            document.body.appendChild(dropdown);
            dropdown.focus();
        }
        
        selectMobileDate(date, targetElement) {
            // Remove previous selection
            this.mobileDatePopup.querySelectorAll('.day-cell.selected').forEach(cell => {
                cell.classList.remove('selected');
            });
            
            // Add selection to clicked cell (using passed targetElement)
            if (targetElement) {
                targetElement.classList.add('selected');
            }
            
            this.tempSelectedDate = new Date(date);
            this.mobileDatePopup.querySelector('.popup-confirm').disabled = false;
        }
        
        selectMobileTime(slotElement) {
            // Remove previous selection
            this.mobileTimePopup.querySelectorAll('.time-slot.selected').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Select this slot
            slotElement.classList.add('selected');
            this.tempSelectedTime = slotElement.dataset.time;
            
            // Enable confirm button
            this.mobileTimePopup.querySelector('.popup-confirm').disabled = false;
        }
        
        confirmMobileDate() {
            if (!this.tempSelectedDate) return;
            
            this.selectedDate = this.tempSelectedDate;
            const formattedDate = this.selectedDate.toISOString().split('T')[0];
            
            this.dateInput.value = formattedDate;
            this.closeMobileDatePicker();
            this.onDateSelected(this.selectedDate);
        }
        
        confirmMobileTime() {
            if (!this.tempSelectedTime) return;
            
            this.selectedTime = this.tempSelectedTime;
            this.timeInput.value = this.selectedTime;
            this.closeMobileTimePicker();
            this.showMessage(`⏰ Time selected: ${this.formatTime(this.selectedTime)}`, 'success');
        }
        
        closeMobileDatePicker() {
            if (this.mobileDatePopup) {
                this.mobileDatePopup.style.display = 'none';
            }
            document.body.style.overflow = '';
        }
        
        closeMobileTimePicker() {
            if (this.mobileTimePopup) {
                this.mobileTimePopup.style.display = 'none';
            }
            document.body.style.overflow = '';
        }
        
        changeMobileMonth(direction) {
            this.currentMobileDate.setMonth(this.currentMobileDate.getMonth() + direction);
            this.renderMobileCalendar();
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
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.bms-unified-message');
            existingMessages.forEach(msg => msg.remove());
            
            // Create new message
            const message = document.createElement('div');
            message.className = `bms-unified-message bms-message-${type}`;
            message.innerHTML = `
                ${text}
                <button onclick="this.parentElement.remove()" style="
                    float: right;
                    background: none;
                    border: none;
                    font-size: 16px;
                    cursor: pointer;
                    color: inherit;
                    opacity: 0.7;
                    margin-left: 10px;
                ">×</button>
            `;
            
            // Insert message
            const container = document.querySelector('.fitting-appointment') || document.body;
            container.insertBefore(message, container.firstChild);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (message.parentElement) {
                    message.remove();
                }
            }, 5000);
        }
        
        injectStyles() {
            if (document.getElementById('bms-unified-calendar-styles')) return;
            
            const styles = document.createElement('style');
            styles.id = 'bms-unified-calendar-styles';
            styles.textContent = `
                /* Unified Calendar Styles */
                .bms-unified-date-popup,
                .bms-unified-time-popup {
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
                
                .bms-unified-date-popup .popup-overlay,
                .bms-unified-time-popup .popup-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.6);
                    backdrop-filter: blur(2px);
                }
                
                .bms-unified-date-popup .popup-content,
                .bms-unified-time-popup .popup-content {
                    position: relative;
                    background: white;
                    border-radius: 16px;
                    width: 100%;
                    max-width: 400px;
                    max-height: 90vh;
                    overflow-y: auto;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                    animation: bmsPopupSlideIn 0.3s ease-out;
                }
                
                @keyframes bmsPopupSlideIn {
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
                
                .calendar-container,
                .time-container {
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
                
                .bms-unified-message {
                    margin: 16px 0;
                    padding: 12px 16px;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    animation: bmsMessageSlideIn 0.3s ease-out;
                }
                
                .bms-message-success {
                    background: #d1fae5;
                    color: #065f46;
                    border: 1px solid #a7f3d0;
                }
                
                .bms-message-warning {
                    background: #fef3c7;
                    color: #92400e;
                    border: 1px solid #fbbf24;
                }
                
                .bms-message-info {
                    background: #dbeafe;
                    color: #1e40af;
                    border: 1px solid #93c5fd;
                }
                
                @keyframes bmsMessageSlideIn {
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
                
                /* Mobile responsive */
                @media (max-width: 480px) {
                    .bms-unified-date-popup,
                    .bms-unified-time-popup {
                        padding: 10px;
                    }
                    
                    .bms-unified-date-popup .popup-content,
                    .bms-unified-time-popup .popup-content {
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
    }
    
    // Initialize the unified calendar fix
    window.BMSUnifiedCalendarFix = BMSUnifiedCalendarFix;
    
    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                new BMSUnifiedCalendarFix();
            }, 500);
        });
    } else {
        setTimeout(() => {
            new BMSUnifiedCalendarFix();
        }, 500);
    }
    
    console.log('🚀 Unified Calendar Fix: Script loaded and ready!');
    
})();
