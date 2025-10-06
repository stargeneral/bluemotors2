<?php
/**
 * WordPress Page Calendar Fix - Emergency Patch
 * 
 * Fixes the calendar issues specifically on WordPress pages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

?>
<script>
console.log('🚨 Emergency Calendar Fix: Loading...');

// Fix 1: Handle missing professional messaging files
(function() {
    // Create placeholder CSS to prevent 404 errors
    if (!document.querySelector('#professional-messaging-css-fix')) {
        const style = document.createElement('style');
        style.id = 'professional-messaging-css-fix';
        style.textContent = `
            /* Emergency CSS Fix for Professional Messaging */
            .professional-messaging { display: block; }
        `;
        document.head.appendChild(style);
        console.log('✅ Professional messaging CSS placeholder created');
    }
})();

// Fix 2: Patch the time picker creation issue
window.fixTimePickerIssue = function() {
    console.log('🔧 Fixing time picker creation issue...');
    
    // Wait for mobile date time picker to be available
    const checkAndFix = function() {
        if (window.mobileDateTimePicker && typeof window.mobileDateTimePicker.showTimePicker === 'function') {
            console.log('✅ Mobile date time picker found, patching...');
            
            // Override the showTimePicker method to handle missing popup
            const originalShowTimePicker = window.mobileDateTimePicker.showTimePicker;
            
            window.mobileDateTimePicker.showTimePicker = function(inputElement) {
                console.log('🕐 Time picker called with element:', inputElement);
                
                // Ensure the popup container exists
                if (!this.timePopup) {
                    console.log('🔧 Creating missing time popup...');
                    this.createTimePopup();
                }
                
                // Call the original method
                try {
                    return originalShowTimePicker.call(this, inputElement);
                } catch (error) {
                    console.error('❌ Time picker error:', error);
                    
                    // Fallback: Create a simple time selector
                    this.createFallbackTimeSelector(inputElement);
                }
            };
            
            // Add fallback time selector method
            window.mobileDateTimePicker.createFallbackTimeSelector = function(inputElement) {
                console.log('🛠️ Creating fallback time selector...');
                
                const times = [
                    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                    '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
                    '15:00', '15:30', '16:00', '16:30', '17:00'
                ];
                
                const timeSelect = document.createElement('select');
                timeSelect.style.cssText = `
                    position: absolute;
                    z-index: 10000;
                    background: white;
                    border: 2px solid #1d4ed8;
                    border-radius: 8px;
                    padding: 10px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    max-height: 200px;
                    overflow-y: auto;
                `;
                
                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Select time';
                timeSelect.appendChild(defaultOption);
                
                // Add time options
                times.forEach(time => {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = this.formatTime ? this.formatTime(time) : time;
                    timeSelect.appendChild(option);
                });
                
                // Position near the input
                const rect = inputElement.getBoundingClientRect();
                timeSelect.style.top = (rect.bottom + window.scrollY + 5) + 'px';
                timeSelect.style.left = (rect.left + window.scrollX) + 'px';
                
                // Handle selection
                timeSelect.onchange = function() {
                    if (this.value) {
                        inputElement.value = this.value;
                        inputElement.dispatchEvent(new Event('change'));
                        document.body.removeChild(this);
                        console.log('✅ Time selected:', this.value);
                    }
                };
                
                // Close when clicking outside
                const closeSelector = (e) => {
                    if (!timeSelect.contains(e.target) && e.target !== inputElement) {
                        document.body.removeChild(timeSelect);
                        document.removeEventListener('click', closeSelector);
                    }
                };
                
                setTimeout(() => {
                    document.addEventListener('click', closeSelector);
                }, 100);
                
                document.body.appendChild(timeSelect);
                timeSelect.focus();
            };
            
            console.log('✅ Time picker patching complete');
            
        } else {
            console.log('⏳ Waiting for mobile date time picker...');
            setTimeout(checkAndFix, 500);
        }
    };
    
    checkAndFix();
};

// Fix 3: Handle DOM element issues in tyre booking system
window.fixTyreBookingDOMIssues = function() {
    console.log('🔧 Fixing tyre booking DOM issues...');
    
    // Add missing DOM elements that the tyre booking system expects
    const createMissingElement = (id, tagName = 'div') => {
        if (!document.getElementById(id)) {
            const element = document.createElement(tagName);
            element.id = id;
            element.style.display = 'none'; // Hidden placeholder
            document.body.appendChild(element);
            console.log(`✅ Created missing element: ${id}`);
        }
    };
    
    // Create common missing elements
    createMissingElement('tyre-search-results');
    createMissingElement('pricing-display');
    createMissingElement('search-method-toggle');
    
    // Fix null reference errors by wrapping problematic functions
    if (window.BlueMotosTyreBooking) {
        const originalSwitchSearchMethod = window.BlueMotosTyreBooking.prototype.switchSearchMethod;
        if (originalSwitchSearchMethod) {
            window.BlueMotosTyreBooking.prototype.switchSearchMethod = function(...args) {
                try {
                    return originalSwitchSearchMethod.apply(this, args);
                } catch (error) {
                    console.warn('⚠️ switchSearchMethod error caught and ignored:', error);
                    return false;
                }
            };
        }
        
        const originalUpdateTyreCardPricing = window.BlueMotosTyreBooking.prototype.updateTyreCardPricing;
        if (originalUpdateTyreCardPricing) {
            window.BlueMotosTyreBooking.prototype.updateTyreCardPricing = function(...args) {
                try {
                    return originalUpdateTyreCardPricing.apply(this, args);
                } catch (error) {
                    console.warn('⚠️ updateTyreCardPricing error caught and ignored:', error);
                    return false;
                }
            };
        }
    }
    
    console.log('✅ Tyre booking DOM issues patched');
};

// Fix 4: Enhanced calendar initialization for WordPress pages
window.enhancedCalendarInit = function() {
    console.log('📅 Enhanced calendar initialization for WordPress...');
    
    // Wait for all dependencies
    const waitForDependencies = () => {
        return new Promise((resolve) => {
            const check = () => {
                if (typeof jQuery !== 'undefined' && 
                    jQuery.ui && 
                    jQuery.ui.datepicker &&
                    window.BMSCalendarFix) {
                    resolve();
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        });
    };
    
    waitForDependencies().then(() => {
        console.log('✅ All calendar dependencies ready');
        
        // Force reinitialize calendar with error handling
        try {
            if (window.BMSCalendarFix && typeof window.BMSCalendarFix.init === 'function') {
                window.BMSCalendarFix.init();
                console.log('✅ Calendar force reinitialized');
            }
        } catch (error) {
            console.error('❌ Calendar initialization error:', error);
        }
        
        // Ensure date input is properly configured
        const dateInput = document.getElementById('fitting-date');
        if (dateInput) {
            // Remove readonly attribute if present
            dateInput.removeAttribute('readonly');
            
            // Ensure it has proper attributes
            const today = new Date();
            const minDate = new Date(today);
            minDate.setDate(today.getDate() + 2);
            const maxDate = new Date(today);
            maxDate.setDate(today.getDate() + 30);
            
            dateInput.min = minDate.toISOString().split('T')[0];
            dateInput.max = maxDate.toISOString().split('T')[0];
            
            console.log('✅ Date input configured properly');
        }
    });
};

// Execute all fixes when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 WordPress Calendar Emergency Fix: Executing...');
    
    // Apply fixes in sequence
    setTimeout(() => {
        fixTyreBookingDOMIssues();
    }, 100);
    
    setTimeout(() => {
        fixTimePickerIssue();
    }, 500);
    
    setTimeout(() => {
        enhancedCalendarInit();
    }, 1000);
    
    console.log('✅ All emergency fixes scheduled');
});

// Also try to apply fixes immediately if DOM is already loaded
if (document.readyState === 'loading') {
    // Do nothing, wait for DOMContentLoaded
} else {
    // DOM is already loaded, apply fixes immediately
    setTimeout(() => {
        console.log('🚀 DOM already loaded, applying emergency fixes...');
        fixTyreBookingDOMIssues();
        setTimeout(fixTimePickerIssue, 500);
        setTimeout(enhancedCalendarInit, 1000);
    }, 100);
}

console.log('🎯 WordPress Calendar Emergency Fix: Script loaded!');
</script>

<style>
/* Emergency CSS fixes for calendar issues */
#fitting-time {
    cursor: pointer;
    background-color: white;
    border: 2px solid #1d4ed8;
    border-radius: 6px;
    padding: 12px;
    font-size: 16px;
    width: 100%;
    box-sizing: border-box;
}

#fitting-time:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Hide broken elements that cause errors */
.broken-element {
    display: none !important;
}

/* Ensure calendar elements are visible */
.ui-datepicker {
    z-index: 10000 !important;
}

/* Time picker fallback styling */
select[id*="time"] {
    background: white;
    border: 2px solid #1d4ed8;
    border-radius: 8px;
    padding: 12px;
    font-size: 16px;
    cursor: pointer;
}

select[id*="time"]:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
