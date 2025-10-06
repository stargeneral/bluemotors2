<?php
/**
 * Booking Form Shortcode for Blue Motors Southampton
 * 
 * Main booking system shortcode - the heart of the plugin
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the booking form shortcode (with protection against double registration)
 */
if (!shortcode_exists('bms_booking_form')) {
    add_shortcode('bms_booking_form', 'bms_booking_form_shortcode');
}

/**
 * Booking form shortcode handler
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function bms_booking_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'service' => '',           // Pre-select service
        'theme' => 'blue',         // Color theme
        'show_competitive' => 'true', // Show professional features
        'width' => '100%',         // Container width
        'class' => ''              // Additional CSS class
    ), $atts, 'bms_booking_form');
    
    // Enqueue necessary scripts and styles
    wp_enqueue_script('bms-booking');
    wp_enqueue_script('bms-vehicle-lookup');
    wp_enqueue_script('bms-tyre-booking');
    wp_enqueue_script('bms-professional-messaging');
    wp_enqueue_script('bms-uk-date-handler');
    wp_enqueue_script('bms-payment-improvements');
    
    wp_enqueue_style('bms-public');
    wp_enqueue_style('bms-vehicle-lookup');
    wp_enqueue_style('bms-tyre-search');
    wp_enqueue_style('bms-professional-messaging');
    wp_enqueue_style('bms-uk-date-styles');
    wp_enqueue_style('bms-mobile-enhancements');
    
    // Start output buffering
    ob_start();
    ?>
    
    <div class="bms-booking-container <?php echo esc_attr($atts['class']); ?>" 
         style="width: <?php echo esc_attr($atts['width']); ?>;"
         data-theme="<?php echo esc_attr($atts['theme']); ?>"
         data-preselect-service="<?php echo esc_attr($atts['service']); ?>">
        
        <?php if ($atts['show_competitive'] === 'true'): ?>
        <!-- Professional Advantage Header -->
        <div class="competitive-header">
            <div class="advantage-message">
                <h3>Why Choose Blue Motors?</h3>
                <div class="advantages-grid">
                    <div class="advantage-item">
                        <span class="advantage-icon">🛞</span>
                        <span class="advantage-text">Order tyres online - no phone calls needed!</span>
                    </div>
                    <div class="advantage-item">
                        <span class="advantage-icon">💳</span>
                        <span class="advantage-text">Smooth payment process</span>
                    </div>
                    <div class="advantage-item">
                        <span class="advantage-icon">📱</span>
                        <span class="advantage-text">Mobile-friendly booking</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Main Booking Form -->
        <div class="bms-booking-form-wrapper">
            
            <!-- Progress Steps -->
            <div class="bms-progress-steps">
                <div class="bms-step bms-step-active" data-step="1">
                    <div class="bms-step-number">1</div>
                    <div class="bms-step-label">Select Service</div>
                </div>
                <div class="bms-step" data-step="2">
                    <div class="bms-step-number">2</div>
                    <div class="bms-step-label">Vehicle Details</div>
                </div>
                <div class="bms-step" data-step="3">
                    <div class="bms-step-number">3</div>
                    <div class="bms-step-label">Date & Time</div>
                </div>
                <div class="bms-step" data-step="4">
                    <div class="bms-step-number">4</div>
                    <div class="bms-step-label">Your Details</div>
                </div>
                <div class="bms-step" data-step="5">
                    <div class="bms-step-number">5</div>
                    <div class="bms-step-label">Payment</div>
                </div>
            </div>
            
            <!-- Step 1: Service Selection -->
            <div class="bms-step-content" id="step-1" style="display: block;">
                <h3>Choose Your Service</h3>
                
                <?php echo do_shortcode('[bms_service_cards columns="2" show_booking_buttons="true"]'); ?>
                
                <!-- Tyre Services Call-out -->
                <div class="tyre-services-callout">
                    <h4>🛞 Need Tyres? Order Online!</h4>
                    <p><strong>Beat industry leaders:</strong> Complete your tyre order online - no phone calls required!</p>
                    <button type="button" class="btn-tyre-search">
                        🔍 Search for Tyres
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Vehicle Details -->
            <div class="bms-step-content" id="step-2" style="display: none;">
                <h3>Vehicle Information</h3>
                
                <?php echo do_shortcode('[bms_vehicle_lookup show_mot="true" placeholder="Enter your registration e.g. AB12 CDE"]'); ?>
                
                <div class="competitive-note">
                    <strong>🎯 Our Advantage:</strong> Automatic vehicle lookup!
                </div>
            </div>
            
            <!-- Step 3: Date & Time Selection -->
            <div class="bms-step-content" id="step-3" style="display: none;">
                <h3>Choose Your Appointment</h3>
                
                <!-- AI-Powered Smart Scheduler Integration -->
                <div class="smart-scheduler-integration">
                    <div class="ai-scheduler-header">
                        <h4>🤖 AI-Powered Appointment Scheduling</h4>
                        <p>Our intelligent system suggests the best times based on availability and your preferences</p>
                    </div>
                    
                    <!-- Embed the Smart Scheduler -->
                    <?php echo do_shortcode('[bms_smart_scheduler show_customer_prefs="true" max_suggestions="5"]'); ?>
                </div>
                
                <!-- Fallback Manual Selection -->
                <div class="manual-date-time-selection" style="display: none;">
                    <h4>Or Select Manually:</h4>
                    <div class="date-time-grid">
                        <div class="calendar-selection">
                            <label>Select Date:</label>
                            <div id="booking-calendar" class="calendar-widget">
                                <!-- Calendar will be rendered here -->
                            </div>
                        </div>
                        
                        <div class="time-selection">
                            <label>Available Times:</label>
                            <div id="available-times" class="time-slots-grid">
                                <!-- Time slots will be loaded dynamically -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="uk-date-advantage">
                        <strong>🇬🇧 UK Date Format (DD/MM/YYYY)</strong>
                        <p>Professional Services's confusing American format!</p>
                    </div>
                </div>
                
                <!-- Selected Appointment Display -->
                <div id="selected-appointment" class="selected-appointment" style="display: none;">
                    <h4>✅ Selected Appointment:</h4>
                    <div class="appointment-details">
                        <span id="selected-date"></span> at <span id="selected-time"></span>
                    </div>
                    <button type="button" class="btn-change-appointment">Change Selection</button>
                </div>
            </div>
            
            <!-- Step 4: Customer Details -->
            <div class="bms-step-content" id="step-4" style="display: none;">
                <h3>Your Contact Details</h3>
                
                <form class="customer-details-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer-name">Full Name *</label>
                            <input type="text" id="customer-name" name="customer_name" required>
                        </div>
                        <div class="form-group">
                            <label for="customer-email">Email Address *</label>
                            <input type="email" id="customer-email" name="customer_email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer-phone">Phone Number *</label>
                            <input type="tel" id="customer-phone" name="customer_phone" required>
                        </div>
                        <div class="form-group">
                            <label for="customer-postcode">Postcode</label>
                            <input type="text" id="customer-postcode" name="customer_postcode" placeholder="SO14 5SP">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer-notes">Special Requirements (Optional)</label>
                        <textarea id="customer-notes" name="customer_notes" rows="3" 
                                placeholder="Any special requirements or notes..."></textarea>
                    </div>
                </form>
            </div>
            
            <!-- Step 5: Payment -->
            <div class="bms-step-content" id="step-5" style="display: none;">
                <h3>Secure Payment</h3>
                
                <!-- Booking Summary -->
                <div class="booking-summary">
                    <h4>Booking Summary</h4>
                    <div id="summary-details">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="payment-methods">
                    <h4>💳 Secure Payment Options</h4>
                    <div class="payment-advantages">
                        <h5>🎯 Simple Payment:</h5>
                        <ul>
                            <li>✅ No PayPal integration issues</li>
                            <li>✅ UK-optimized checkout</li>
                            <li>✅ Multiple payment options</li>
                            <li>✅ Instant confirmation</li>
                        </ul>
                    </div>
                    
                    <div id="payment-element">
                        <!-- Stripe payment element will be mounted here -->
                    </div>
                    
                    <div id="payment-messages" class="payment-messages">
                        <!-- Payment messages will appear here -->
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="terms-conditions">
                    <label>
                        <input type="checkbox" id="accept-terms" required>
                        I agree to the <a href="#" target="_blank">Terms & Conditions</a>
                    </label>
                </div>
            </div>
            
            <!-- Navigation Buttons -->
            <div class="step-navigation">
                <button type="button" id="btn-previous" class="btn-secondary" style="display: none;">
                    ← Previous
                </button>
                <button type="button" id="btn-next" class="btn-primary">
                    Next →
                </button>
                <button type="button" id="btn-complete-booking" class="btn-large" style="display: none;">
                    Complete Booking & Pay
                </button>
            </div>
            
            <!-- Tyre Search Modal -->
            <div id="tyre-search-modal" class="tyre-modal" style="display: none;">
                <div class="tyre-modal-content">
                    <span class="tyre-modal-close">&times;</span>
                    <?php echo do_shortcode('[bms_tyre_search]'); ?>
                </div>
            </div>
            
        </div>
    </div>
    
    <style>
    .bms-booking-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .competitive-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: white;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    
    .advantages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }
    
    .advantage-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,0.1);
        padding: 12px;
        border-radius: 8px;
    }
    
    .bms-progress-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }
    
    .bms-progress-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 40px;
        right: 40px;
        height: 2px;
        background: #e5e7eb;
        z-index: 1;
    }
    
    .bms-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }
    
    .bms-step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .bms-step-active .bms-step-number {
        background: #3b82f6;
        color: white;
    }
    
    .tyre-services-callout {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
        text-align: center;
    }
    
    .competitive-note {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 12px 16px;
        border-radius: 6px;
        margin: 16px 0;
        font-size: 14px;
        text-align: center;
    }
    
    .uk-date-advantage {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        margin: 16px 0;
        text-align: center;
        font-size: 14px;
    }
    
    .time-slots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group label {
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }
    
    .form-group input,
    .form-group textarea {
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 16px;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .step-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    
    .btn-primary, .btn-secondary, .btn-large {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
    }
    
    .btn-large {
        background: #059669;
        color: white;
        padding: 16px 32px;
        font-size: 18px;
    }
    
    .payment-advantages {
        background: #eff6ff;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 16px;
        margin: 16px 0;
    }
    
    .payment-advantages h5 {
        margin: 0 0 8px 0;
        color: #1e3a8a;
    }
    
    .payment-advantages ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .tyre-modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
    }
    
    .tyre-modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 20px;
        border-radius: 12px;
        width: 90%;
        max-width: 1000px;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
    }
    
    .tyre-modal-close {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    /* Smart Scheduler Integration Styles */
    .smart-scheduler-integration {
        background: #f9fafb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
    }
    
    .ai-scheduler-header {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .ai-scheduler-header h4 {
        color: #1e3a8a;
        margin: 0 0 8px 0;
    }
    
    .ai-scheduler-header p {
        color: #6b7280;
        margin: 0;
    }
    
    .selected-appointment {
        background: #d1fae5;
        border: 2px solid #10b981;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        text-align: center;
    }
    
    .selected-appointment h4 {
        color: #065f46;
        margin: 0 0 12px 0;
    }
    
    .appointment-details {
        font-size: 18px;
        font-weight: 600;
        color: #047857;
        margin-bottom: 16px;
    }
    
    .btn-change-appointment {
        background: #6b7280;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .btn-change-appointment:hover {
        background: #4b5563;
    }
    
    .manual-date-time-selection {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .date-time-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 16px;
    }
    
    .calendar-widget {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        min-height: 300px;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .bms-booking-container {
            padding: 16px;
        }
        
        .advantages-grid {
            grid-template-columns: 1fr;
        }
        
        .bms-progress-steps {
            flex-direction: column;
            gap: 8px;
        }
        
        .bms-progress-steps::before {
            display: none;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .step-navigation {
            flex-direction: column;
            gap: 12px;
        }
        
        .tyre-modal-content {
            margin: 10px;
            width: calc(100% - 20px);
        }
        
        .date-time-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize booking form functionality
        if (typeof window.BMSBookingForm === 'undefined') {
            window.BMSBookingForm = {
                currentStep: 1,
                totalSteps: 5,
                bookingData: {},
                
                init: function() {
                    this.bindEvents();
                    this.preSelectService();
                },
                
                bindEvents: function() {
                    const nextBtn = document.getElementById('btn-next');
                    const prevBtn = document.getElementById('btn-previous');
                    const completeBtn = document.getElementById('btn-complete-booking');
                    const tyreBtn = document.querySelector('.btn-tyre-search');
                    
                    if (nextBtn) nextBtn.addEventListener('click', () => this.nextStep());
                    if (prevBtn) prevBtn.addEventListener('click', () => this.previousStep());
                    if (completeBtn) completeBtn.addEventListener('click', () => this.completeBooking());
                    if (tyreBtn) tyreBtn.addEventListener('click', () => this.openTyreModal());
                },
                
                preSelectService: function() {
                    const container = document.querySelector('.bms-booking-container');
                    const preselect = container?.getAttribute('data-preselect-service');
                    
                    if (preselect) {
                        // Auto-select the specified service
                        setTimeout(() => {
                            const serviceCard = document.querySelector(`[data-service="${preselect}"]`);
                            if (serviceCard) {
                                serviceCard.click();
                            }
                        }, 500);
                    }
                },
                
                nextStep: function() {
                    if (this.validateCurrentStep()) {
                        this.currentStep++;
                        this.updateStepDisplay();
                    }
                },
                
                previousStep: function() {
                    this.currentStep--;
                    this.updateStepDisplay();
                },
                
                updateStepDisplay: function() {
                    // Hide all steps
                    document.querySelectorAll('.bms-step-content').forEach(step => {
                        step.style.display = 'none';
                    });
                    
                    // Show current step
                    const currentStepEl = document.getElementById(`step-${this.currentStep}`);
                    if (currentStepEl) {
                        currentStepEl.style.display = 'block';
                    }
                    
                    // Update progress indicators
                    document.querySelectorAll('.bms-step').forEach((step, index) => {
                        step.classList.toggle('bms-step-active', index + 1 <= this.currentStep);
                    });
                    
                    // Update navigation buttons
                    document.getElementById('btn-previous').style.display = 
                        this.currentStep > 1 ? 'block' : 'none';
                    document.getElementById('btn-next').style.display = 
                        this.currentStep < this.totalSteps ? 'block' : 'none';
                    document.getElementById('btn-complete-booking').style.display = 
                        this.currentStep === this.totalSteps ? 'block' : 'none';
                },
                
                validateCurrentStep: function() {
                    // Add validation logic for each step
                    switch(this.currentStep) {
                        case 1:
                            // Validate service selection
                            if (!this.bookingData.service) {
                                alert('Please select a service');
                                return false;
                            }
                            break;
                        case 2:
                            // Validate vehicle details
                            if (!this.bookingData.vehicle_reg) {
                                alert('Please enter your vehicle registration');
                                return false;
                            }
                            break;
                        case 3:
                            // Validate date/time selection
                            if (!this.bookingData.appointment_date || !this.bookingData.appointment_time) {
                                alert('Please select an appointment date and time');
                                return false;
                            }
                            break;
                        case 4:
                            // Validate customer details
                            const name = document.getElementById('customer-name').value;
                            const email = document.getElementById('customer-email').value;
                            const phone = document.getElementById('customer-phone').value;
                            
                            if (!name || !email || !phone) {
                                alert('Please fill in all required fields');
                                return false;
                            }
                            
                            this.bookingData.customer_name = name;
                            this.bookingData.customer_email = email;
                            this.bookingData.customer_phone = phone;
                            this.bookingData.customer_postcode = document.getElementById('customer-postcode').value;
                            this.bookingData.customer_notes = document.getElementById('customer-notes').value;
                            break;
                    }
                    return true;
                },
                
                openTyreModal: function() {
                    document.getElementById('tyre-search-modal').style.display = 'block';
                },
                
                completeBooking: function() {
                    // Handle final booking submission
                    console.log('Completing booking...', this.bookingData);
                }
            };
            
            window.BMSBookingForm.init();
        }
        
        // Close tyre modal
        document.querySelector('.tyre-modal-close')?.addEventListener('click', function() {
            document.getElementById('tyre-search-modal').style.display = 'none';
        });
        
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('tyre-search-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Global function for smart scheduler integration
        window.selectAppointmentSlot = function(date, time) {
            // Store the selected appointment
            if (window.BMSBookingForm) {
                window.BMSBookingForm.bookingData.appointment_date = date;
                window.BMSBookingForm.bookingData.appointment_time = time;
                
                // Format date for UK display
                const dateObj = new Date(date);
                const ukDate = dateObj.toLocaleDateString('en-GB', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                // Update the display
                document.getElementById('selected-date').textContent = ukDate;
                document.getElementById('selected-time').textContent = time;
                
                // Hide scheduler and show selected appointment
                document.querySelector('.smart-scheduler-integration').style.display = 'none';
                document.getElementById('selected-appointment').style.display = 'block';
                
                // Enable next button
                document.getElementById('btn-next').disabled = false;
            }
        };
        
        // Handle change appointment button
        document.querySelector('.btn-change-appointment')?.addEventListener('click', function() {
            document.querySelector('.smart-scheduler-integration').style.display = 'block';
            document.getElementById('selected-appointment').style.display = 'none';
        });
        
        // When reaching step 3, trigger smart scheduler
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.id === 'step-3' && mutation.target.style.display === 'block') {
                    // Update smart scheduler with selected service
                    const serviceType = window.BMSBookingForm?.bookingData?.service || '';
                    const serviceSelect = document.getElementById('smart-service-type');
                    if (serviceSelect && serviceType) {
                        serviceSelect.value = serviceType;
                    }
                }
            });
        });
        
        // Observe step changes
        document.querySelectorAll('.bms-step-content').forEach(step => {
            observer.observe(step, { attributes: true, attributeFilter: ['style'] });
        });
    });
    </script>
    
    <?php
    
    return ob_get_clean();
}
