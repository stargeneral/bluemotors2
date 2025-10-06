/**
 * UK Date Format Handler
 * Blue Motors Southampton
 */

class UKDateHandler {
    constructor() {
        this.initDatePickers();
        this.formatExistingDates();
        // Removed service excellence message
    }
    
    initDatePickers() {
        // Configure all date inputs for UK format
        const dateInputs = document.querySelectorAll('input[type="date"]');
        
        dateInputs.forEach(input => {
            this.enhanceDateInput(input);
        });
    }
    
    enhanceDateInput(input) {
        // Create custom date picker wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'uk-date-picker';
        
        // Create display input (shows DD/MM/YYYY)
        const displayInput = document.createElement('input');
        displayInput.type = 'text';
        displayInput.placeholder = 'DD/MM/YYYY';
        displayInput.className = 'uk-date-display';
        displayInput.setAttribute('inputmode', 'numeric');
        displayInput.setAttribute('autocomplete', 'off');
        
        // Copy attributes from original input
        if (input.id) displayInput.setAttribute('data-original-id', input.id);
        if (input.name) displayInput.setAttribute('data-original-name', input.name);
        if (input.required) displayInput.required = true;
        
        // Hide original date input but keep it for form submission
        input.style.display = 'none';
        input.tabIndex = -1;
        
        // Insert wrapper
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(displayInput);
        wrapper.appendChild(input);
        
        // Handle UK format input
        displayInput.addEventListener('input', (e) => {
            this.handleUKDateInput(e, input);
        });
        
        // Format on blur
        displayInput.addEventListener('blur', (e) => {
            this.formatUKDate(e, input);
        });
        
        // Handle enter key
        displayInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && this.isValidUKDate(e.target.value)) {
                e.target.blur();
            }
        });
        
        // Sync changes back from hidden input
        input.addEventListener('change', () => {
            if (input.value) {
                displayInput.value = this.formatToUKDate(input.value);
            }
        });
        
        // Set initial value if input has a value
        if (input.value) {
            displayInput.value = this.formatToUKDate(input.value);
        }
    }
    
    handleUKDateInput(event, hiddenInput) {
        let value = event.target.value.replace(/\D/g, ''); // Remove non-digits
        
        // Format as user types: DD/MM/YYYY
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '/' + value.substring(5, 9);
        }
        
        // Limit to 10 characters (DD/MM/YYYY)
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        
        event.target.value = value;
        
        // Convert to ISO format for hidden input
        if (value.length === 10) {
            const ukDate = this.parseUKDate(value);
            if (ukDate && this.isValidDate(ukDate)) {
                hiddenInput.value = ukDate.toISOString().split('T')[0];
                this.showDateConfirmation(event.target, ukDate);
                
                // Clear any previous errors
                this.clearDateError(event.target);
            } else {
                hiddenInput.value = '';
                this.showDateError(event.target, 'Please enter a valid date');
            }
        } else {
            hiddenInput.value = '';
            this.clearDateMessages(event.target);
        }
    }
    
    parseUKDate(ukDateString) {
        const parts = ukDateString.split('/');
        if (parts.length !== 3) return null;
        
        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1; // Month is 0-indexed
        const year = parseInt(parts[2], 10);
        
        // Basic validation
        if (day < 1 || day > 31 || month < 0 || month > 11 || year < 1900 || year > 2100) {
            return null;
        }
        
        const date = new Date(year, month, day);
        
        // Check if the date is valid (handles leap years, etc.)
        if (date.getDate() !== day || date.getMonth() !== month || date.getFullYear() !== year) {
            return null;
        }
        
        return date;
    }
    
    isValidDate(date) {
        if (!date || isNaN(date.getTime())) return false;
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const maxDate = new Date(today.getTime() + (90 * 24 * 60 * 60 * 1000)); // 90 days ahead
        
        return date >= today && date <= maxDate;
    }
    
    isValidUKDate(ukDateString) {
        if (ukDateString.length !== 10) return false;
        const date = this.parseUKDate(ukDateString);
        return date && this.isValidDate(date);
    }
    
    formatToUKDate(isoString) {
        if (!isoString) return '';
        
        const date = new Date(isoString);
        if (isNaN(date.getTime())) return '';
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        
        return `${day}/${month}/${year}`;
    }
    
    formatUKDate(event, hiddenInput) {
        const input = event.target;
        const value = input.value;
        
        if (value.length === 10) {
            const date = this.parseUKDate(value);
            if (date && this.isValidDate(date)) {
                // Valid date - add visual confirmation
                input.classList.add('valid-date');
                input.classList.remove('invalid-date');
                
                // Show helpful message
                this.showDateConfirmation(input, date);
            } else {
                input.classList.add('invalid-date');
                input.classList.remove('valid-date');
                this.showDateError(input, 'Please enter a valid date within the next 90 days');
            }
        } else if (value.length > 0) {
            input.classList.add('invalid-date');
            input.classList.remove('valid-date');
            this.showDateError(input, 'Please enter a complete date (DD/MM/YYYY)');
        }
    }
    
    showDateConfirmation(input, date) {
        this.clearDateMessages(input);
        
        const message = document.createElement('div');
        message.className = 'date-confirmation';
        message.innerHTML = `✓ ${date.toLocaleDateString('en-GB', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        })}`;
        
        input.parentNode.appendChild(message);
        
        setTimeout(() => {
            if (message && message.parentNode) {
                message.remove();
            }
        }, 3000);
    }
    
    showDateError(input, errorMessage) {
        this.clearDateMessages(input);
        
        const message = document.createElement('div');
        message.className = 'date-error';
        message.innerHTML = `⚠️ ${errorMessage}`;
        
        input.parentNode.appendChild(message);
    }
    
    clearDateError(input) {
        const errorMsg = input.parentNode.querySelector('.date-error');
        if (errorMsg) errorMsg.remove();
    }
    
    clearDateMessages(input) {
        const existingMessages = input.parentNode.querySelectorAll('.date-confirmation, .date-error');
        existingMessages.forEach(msg => msg.remove());
    }
    
    formatExistingDates() {
        // Find any existing dates and ensure they're in UK format
        const existingDates = document.querySelectorAll('.date-display, [data-date]');
        
        existingDates.forEach(element => {
            const dateValue = element.textContent || element.dataset.date;
            if (dateValue && this.isISODate(dateValue)) {
                const formattedDate = this.formatToUKDate(dateValue);
                if (element.textContent) {
                    element.textContent = formattedDate;
                }
            }
        });
    }
    
    isISODate(dateString) {
        return /^\d{4}-\d{2}-\d{2}$/.test(dateString);
    }
    
    // Removed service excellence message method
    
    // Public method to get all date values in UK format
    getAllDates() {
        const dates = {};
        const ukInputs = document.querySelectorAll('.uk-date-display');
        
        ukInputs.forEach(input => {
            const originalId = input.dataset.originalId;
            const originalName = input.dataset.originalName;
            
            if (originalId || originalName) {
                const key = originalId || originalName;
                dates[key] = {
                    uk_format: input.value,
                    iso_format: input.parentNode.querySelector('input[type="date"]').value,
                    is_valid: this.isValidUKDate(input.value)
                };
            }
        });
        
        return dates;
    }
    
    // Public method to validate all dates
    validateAllDates() {
        const ukInputs = document.querySelectorAll('.uk-date-display');
        let allValid = true;
        
        ukInputs.forEach(input => {
            if (input.required && !this.isValidUKDate(input.value)) {
                allValid = false;
                this.showDateError(input, 'This date is required and must be valid');
            }
        });
        
        return allValid;
    }
}

// Initialize UK date handling when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.ukDateHandler = new UKDateHandler();
});

// Also initialize if called after DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.ukDateHandler) {
            window.ukDateHandler = new UKDateHandler();
        }
    });
} else {
    if (!window.ukDateHandler) {
        window.ukDateHandler = new UKDateHandler();
    }
}
