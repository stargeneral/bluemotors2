<?php
/**
 * Booking Form Template - Updated with Enhanced Service Selection
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Get service manager instance
$services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true);

// Session is already initialized by the main plugin
?>

<div class="bms-booking-container">
    <!-- Location Header -->
    <div class="bms-location-header">
        <h2>Book Your Service at Blue Motors Southampton</h2>
        <div class="location-details">
            <p><i class="dashicons dashicons-location"></i> <?php echo BM_LOCATION_ADDRESS; ?></p>
            <p><i class="dashicons dashicons-phone"></i> <?php echo BM_LOCATION_PHONE; ?></p>
            <p><i class="dashicons dashicons-email"></i> <?php echo BM_LOCATION_EMAIL; ?></p>
        </div>
    </div>
    
    <!-- Progress Steps -->
    <div class="bms-progress-steps">
        <div class="step active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-label">Select Service</span>
        </div>
        <div class="step" data-step="2">
            <span class="step-number">2</span>
            <span class="step-label">Vehicle Details</span>
        </div>
        <div class="step" data-step="3">
            <span class="step-number">3</span>
            <span class="step-label">Date & Time</span>
        </div>
        <div class="step" data-step="4">
            <span class="step-number">4</span>
            <span class="step-label">Your Details</span>
        </div>
        <div class="step" data-step="5">
            <span class="step-number">5</span>
            <span class="step-label">Payment</span>
        </div>
    </div>
    
    <!-- Booking Form -->
    <form id="bms-booking-form" class="bms-form">
        
        <!-- Step 1: Enhanced Service Selection -->
        <div class="bms-step-content" id="step-1-content">
            <?php 
            // Include the enhanced service selection step
            include BMS_PLUGIN_DIR . 'public/templates/service-selection-step.php'; 
            ?>
        </div>
        
        <!-- Step 2: Vehicle Details -->
        <div class="bms-step-content" id="step-2-content" style="display:none;">
            <h3>Enter Your Vehicle Details</h3>
            
            <div class="vehicle-lookup-section">
                <label for="vehicle-reg">Vehicle Registration Number:</label>
                <div class="lookup-input-group">
                    <input type="text" id="vehicle-reg" name="vehicle_reg" placeholder="e.g. AB12 CDE" />
                    <button type="button" id="btn-lookup-vehicle" class="btn-primary">
                        Look Up Vehicle
                    </button>
                </div>
                <div id="lookup-status"></div>
            </div>
            
            <div id="vehicle-details-display" style="display:none;">
                <h4>Vehicle Information:</h4>
                <div class="vehicle-info-grid">
                    <div><strong>Make:</strong> <span id="display-make"></span></div>
                    <div><strong>Model:</strong> <span id="display-model"></span></div>
                    <div><strong>Year:</strong> <span id="display-year"></span></div>
                    <div><strong>Engine:</strong> <span id="display-engine"></span>cc</div>
                    <div><strong>Fuel:</strong> <span id="display-fuel"></span></div>
                </div>
                
                <div class="price-display">
                    <h4>Your Service Price:</h4>
                    <p class="calculated-price">£<span id="service-price">0.00</span></p>
                </div>
                
                <button type="button" id="btn-continue-to-date" class="btn-primary">Continue</button>
            </div>
            
            <p class="manual-entry-option">
                <a href="#" id="manual-vehicle-entry">Enter vehicle details manually</a>
            </p>
        </div>
        
        <!-- Step 3: Date & Time Selection -->
        <div class="bms-step-content" id="step-3-content" style="display:none;">
            <h3>Choose Your Appointment Date & Time</h3>
            
            <!-- Smart Scheduler Integration -->
            <div class="smart-scheduler-integration">
                <div class="scheduler-header">
                    <h4>Select Your Preferred Appointment</h4>
                    <p>Choose from our available time slots</p>
                </div>
                
                <!-- Embed the Smart Scheduler -->
                <?php echo do_shortcode('[bms_smart_scheduler show_customer_prefs="false" max_suggestions="5"]'); ?>
            </div>
            
            <!-- Selected Appointment Display -->
            <div id="selected-appointment" class="selected-appointment" style="display: none;">
                <h4>✅ Selected Appointment:</h4>
                <div class="appointment-details">
                    <span id="selected-date"></span> at <span id="selected-time"></span>
                </div>
                <button type="button" class="btn-change-appointment">Change Selection</button>
            </div>
            
            <!-- Hidden inputs to store selection -->
            <input type="hidden" id="appointment-date" name="appointment_date" />
            <input type="hidden" id="appointment-time" name="appointment_time" />
            
            <button type="button" id="btn-continue-to-details" class="btn-primary" style="display:none;">
                Continue
            </button>
        </div>
        
        <!-- Step 4: Customer Details -->
        <div class="bms-step-content" id="step-4-content" style="display:none;">
            <h3>Your Contact Details</h3>
            
            <div class="form-group">
                <label for="customer-name">Full Name: *</label>
                <input type="text" id="customer-name" name="customer_name" required />
            </div>
            
            <div class="form-group">
                <label for="customer-email">Email Address: *</label>
                <input type="email" id="customer-email" name="customer_email" required />
            </div>
            
            <div class="form-group">
                <label for="customer-phone">Phone Number: *</label>
                <input type="tel" id="customer-phone" name="customer_phone" required />
            </div>
            
            <div class="form-group">
                <label for="customer-address">Address:</label>
                <textarea id="customer-address" name="customer_address" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="customer-postcode">Postcode:</label>
                <input type="text" id="customer-postcode" name="customer_postcode" />
            </div>
            
            <div class="form-group">
                <label for="booking-notes">Additional Notes (optional):</label>
                <textarea id="booking-notes" name="notes" rows="3" 
                          placeholder="Any special requirements or information about your vehicle"></textarea>
            </div>
            
            <button type="button" id="btn-continue-to-payment" class="btn-primary">
                Continue to Payment
            </button>
        </div>
        
        <!-- Step 5: Payment & Confirmation -->
        <div class="bms-step-content" id="step-5-content" style="display:none;">
            <h3>Review & Payment</h3>
            
            <div class="booking-summary">
                <h4>Booking Summary</h4>
                <div id="booking-summary-details">
                    <!-- Summary will be populated here -->
                </div>
            </div>
            
            <div class="payment-section">
                <h4>Payment Details</h4>
                <p>Total Amount: £<span id="total-amount">0.00</span></p>
                
                <!-- Stripe payment element will go here -->
                <div id="payment-element"></div>
                
                <button type="submit" id="btn-complete-booking" class="btn-primary">
                    Complete Booking & Pay
                </button>
            </div>
        </div>
        
    </form>
</div>

<script>
// Enhanced integration with existing booking flow
jQuery(document).ready(function($) {
    
    // Override the continueToNextStep function for service selection
    window.continueToNextStep = function() {
        // Get service selection data
        const selection = window.BMSServiceSelection ? window.BMSServiceSelection.getSelection() : null;
        
        if (!selection || !selection.service) {
            alert('Please select a service first');
            return;
        }
        
        // Store selection in global booking data
        if (typeof bookingData !== 'undefined') {
            bookingData.service = selection.service;
            bookingData.price = selection.totalPrice;
            bookingData.motIncluded = selection.motIncluded;
        } else {
            // Create global booking data if it doesn't exist
            window.bookingData = {
                service: selection.service,
                price: selection.totalPrice,
                motIncluded: selection.motIncluded
            };
        }
        
        // Also store in sessionStorage for smart scheduler
        sessionStorage.setItem('bms_selected_service', JSON.stringify({
            service: selection.service,
            totalPrice: selection.totalPrice,
            price: selection.price,
            motIncluded: selection.motIncluded
        }));
        
        console.log('Service selection stored:', selection);
        
        // Move to vehicle details step
        moveToStep(2);
    };
    
    // Enhanced moveToStep function to handle service selection data
    const originalMoveToStep = window.moveToStep;
    window.moveToStep = function(stepNumber) {
        // If moving from step 1 (service selection), ensure data is stored
        if (stepNumber === 2) {
            const selection = window.BMSServiceSelection ? window.BMSServiceSelection.getSelection() : null;
            if (selection && selection.service) {
                // Store in session via AJAX
                $.ajax({
                    url: bms_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bms_store_service_selection',
                        selection: selection,
                        nonce: bms_ajax.nonce
                    },
                    success: function(response) {
                        console.log('Service selection stored successfully');
                    }
                });
            }
        }
        
        // Call original function if it exists
        if (typeof originalMoveToStep === 'function') {
            originalMoveToStep(stepNumber);
        } else {
            // Fallback step navigation
            $('.bms-step-content').hide();
            $('#step-' + stepNumber + '-content').show();
            
            // Update progress steps
            $('.step').removeClass('active');
            $('.step[data-step="' + stepNumber + '"]').addClass('active');
        }
    };
    
    // Removed auto-loading of saved service selection to prevent unwanted auto-selection
    // Users should make fresh selections each time for better UX
});
</script>
