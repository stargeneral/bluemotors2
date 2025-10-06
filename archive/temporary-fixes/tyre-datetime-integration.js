/**
 * Tyre Booking Date/Time Integration
 * Blue Motors Southampton - Integration Handler
 * 
 * This file ensures smooth integration between the original tyre booking system
 * and the enhanced date/time picker while maintaining backward compatibility.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔗 Initializing Tyre Booking Date/Time Integration...');
    
    // Wait for both systems to be ready
    const checkSystemsReady = setInterval(() => {
        const hasTyreBooking = typeof BlueMotosTyreBooking !== 'undefined';
        const hasEnhancedPicker = typeof EnhancedDateTimePicker !== 'undefined';
        const hasDateField = document.getElementById('fitting-date');
        
        if (hasTyreBooking && hasDateField) {
            clearInterval(checkSystemsReady);
            initializeIntegration();
        }
    }, 100);
    
    // Timeout after 10 seconds
    setTimeout(() => {
        clearInterval(checkSystemsReady);
    }, 10000);
});

function initializeIntegration() {
    console.log('🎯 Integrating enhanced date/time picker with tyre booking system...');
    
    // Get the main tyre booking instance
    let tyreBookingInstance = null;
    
    // Check if the global instance exists
    if (window.blueMotosTyreBooking) {
        tyreBookingInstance = window.blueMotosTyreBooking;
    } else if (typeof BlueMotosTyreBooking !== 'undefined') {
        // Create instance if class exists but no global instance
        tyreBookingInstance = new BlueMotosTyreBooking();
        window.blueMotosTyreBooking = tyreBookingInstance;
    }
    
    if (tyreBookingInstance && window.enhancedDateTimePicker) {
        // Disable the old calendar system
        disableOldCalendarSystem(tyreBookingInstance);
        
        // Integrate with the enhanced picker
        integrateWithEnhancedPicker(tyreBookingInstance, window.enhancedDateTimePicker);
        
        console.log('✅ Integration complete - Enhanced date/time picker active');
        
        // Show success notification
        showIntegrationSuccess();
    } else {
        console.warn('⚠️ Integration failed - falling back to original system');
        handleIntegrationFallback();
    }
}

function disableOldCalendarSystem(tyreBookingInstance) {
    // Disable the old calendar popup
    const oldCalendarPopup = document.getElementById('fitting-calendar-popup');
    if (oldCalendarPopup) {
        oldCalendarPopup.style.display = 'none';
        oldCalendarPopup.classList.add('disabled-old-calendar');
    }
    
    // Remove old calendar click handlers from the date input
    const dateInput = document.getElementById('fitting-date');
    if (dateInput) {
        // Clone the node to remove all event listeners
        const newDateInput = dateInput.cloneNode(true);
        dateInput.parentNode.replaceChild(newDateInput, dateInput);
        
        // Remove readonly attribute for the enhanced picker
        newDateInput.removeAttribute('readonly');
        console.log('🔄 Old calendar system disabled');
    }
}

function integrateWithEnhancedPicker(tyreBookingInstance, enhancedPicker) {
    // Override the original loadFittingSlots method to work with enhanced picker
    const originalLoadFittingSlots = tyreBookingInstance.loadFittingSlots;
    
    tyreBookingInstance.loadFittingSlots = async function(date) {
        console.log('🔄 Using enhanced time slot loading for date:', date);
        
        // Call the enhanced picker's loadTimeSlots method
        if (enhancedPicker.loadTimeSlots) {
            const dateObj = typeof date === 'string' ? new Date(date) : date;
            await enhancedPicker.loadTimeSlots(dateObj);
        } else {
            // Fallback to original method
            return originalLoadFittingSlots.call(this, date);
        }
    };
    
    // Override form validation to use enhanced picker data
    const originalValidateDateTime = tyreBookingInstance.validateDateTime || function() { return true; };
    
    tyreBookingInstance.validateDateTime = function() {
        const enhancedData = enhancedPicker.getSelectedDateTime();
        
        if (!enhancedData.date || !enhancedData.time) {
            console.log('⚠️ Enhanced validation failed:', enhancedData);
            return false;
        }
        
        console.log('✅ Enhanced validation passed:', enhancedData);
        return true;
    };
    
    // Add method to get enhanced date/time data
    tyreBookingInstance.getEnhancedDateTime = function() {
        return enhancedPicker.getSelectedDateTime();
    };
    
    console.log('🔗 Enhanced picker integration complete');
}

function handleIntegrationFallback() {
    // If enhanced picker fails, ensure original system still works
    const dateInput = document.getElementById('fitting-date');
    if (dateInput && !dateInput.hasAttribute('readonly')) {
        // Restore readonly attribute for old system
        dateInput.setAttribute('readonly', true);
    }
    
    // Show fallback notification
    showFallbackNotification();
}

function showIntegrationSuccess() {
    // Create temporary success message
    const successDiv = document.createElement('div');
    successDiv.innerHTML = `
        <div style="background: #f0fdf4; border: 2px solid #16a34a; border-radius: 8px; padding: 12px; margin: 10px 0; text-align: center; color: #166534; font-size: 14px; font-weight: 500;">
            ✅ Enhanced date picker loaded successfully!<br>
            <small>Click the date field to use the improved calendar system</small>
        </div>
    `;
    
    const appointmentForm = document.querySelector('.appointment-form');
    if (appointmentForm) {
        appointmentForm.insertBefore(successDiv, appointmentForm.firstChild);
        
        // Auto-remove after 4 seconds
        setTimeout(() => {
            if (successDiv.parentElement) {
                successDiv.remove();
            }
        }, 4000);
    }
}

function showFallbackNotification() {
    const warningDiv = document.createElement('div');
    warningDiv.innerHTML = `
        <div style="background: #fffbeb; border: 2px solid #f59e0b; border-radius: 8px; padding: 12px; margin: 10px 0; text-align: center; color: #92400e; font-size: 14px; font-weight: 500;">
            ⚠️ Using standard date picker<br>
            <small>Enhanced features unavailable - basic functionality maintained</small>
        </div>
    `;
    
    const appointmentForm = document.querySelector('.appointment-form');
    if (appointmentForm) {
        appointmentForm.insertBefore(warningDiv, appointmentForm.firstChild);
        
        // Auto-remove after 6 seconds
        setTimeout(() => {
            if (warningDiv.parentElement) {
                warningDiv.remove();
            }
        }, 6000);
    }
}

// Enhanced form submission handler
document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Check if this is a tyre booking form
    if (form.querySelector('#fitting-date, #fitting-time')) {
        const enhancedPicker = window.enhancedDateTimePicker;
        const tyreBooking = window.blueMotosTyreBooking;
        
        if (enhancedPicker && tyreBooking) {
            const dateTimeData = enhancedPicker.getSelectedDateTime();
            
            if (!dateTimeData.date || !dateTimeData.time) {
                e.preventDefault();
                
                // Show enhanced validation error
                const errorDiv = document.createElement('div');
                errorDiv.innerHTML = `
                    <div style="background: #fef2f2; border: 2px solid #dc2626; border-radius: 8px; padding: 12px; margin: 10px 0; text-align: center; color: #dc2626; font-size: 14px; font-weight: 600;">
                        ❌ Please select both date and time for your appointment
                    </div>
                `;
                
                const appointmentForm = document.querySelector('.appointment-form');
                if (appointmentForm) {
                    appointmentForm.insertBefore(errorDiv, appointmentForm.firstChild);
                    
                    // Scroll to error
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        if (errorDiv.parentElement) {
                            errorDiv.remove();
                        }
                    }, 5000);
                }
                
                return false;
            }
            
            console.log('📅 Form submission with enhanced date/time:', dateTimeData);
        }
    }
});

// Debug helper function
window.debugTyreDateTimePicker = function() {
    console.log('🔍 Tyre Date/Time Picker Debug Info:');
    console.log('- Enhanced Picker Available:', typeof window.enhancedDateTimePicker !== 'undefined');
    console.log('- Tyre Booking Available:', typeof window.blueMotosTyreBooking !== 'undefined');
    console.log('- Date Input Element:', document.getElementById('fitting-date'));
    console.log('- Time Select Element:', document.getElementById('fitting-time'));
    
    if (window.enhancedDateTimePicker) {
        console.log('- Enhanced Picker Data:', window.enhancedDateTimePicker.getSelectedDateTime());
    }
    
    if (window.blueMotosTyreBooking) {
        console.log('- Tyre Booking Selected:', {
            tyre: window.blueMotosTyreBooking.selectedTyre,
            quantity: window.blueMotosTyreBooking.selectedQuantity
        });
    }
};

console.log('📝 Tyre booking date/time integration script loaded. Use debugTyreDateTimePicker() for debugging.');