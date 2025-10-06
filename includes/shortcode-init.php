<?php
/**
 * Shortcode Registration and Initialization
 * Blue Motors Southampton
 * 
 * This file ensures all shortcodes are properly registered
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize all shortcodes
 */
function bms_init_all_shortcodes() {
    
    // 1. Vehicle Lookup Shortcode
    if (class_exists('BMS_Vehicle_Lookup_Shortcode')) {
        BMS_Vehicle_Lookup_Shortcode::init();
    }
    
    // 2. Service Cards Shortcode (create if missing)
    if (!shortcode_exists('bms_service_cards')) {
        add_shortcode('bms_service_cards', 'bms_service_cards_shortcode_handler');
    }
    
    // 3. Enhanced Service Cards
    if (function_exists('bms_enhanced_service_cards_shortcode')) {
        add_shortcode('bms_enhanced_services', 'bms_enhanced_service_cards_shortcode');
    }
    
    // 4. Tyre Search Shortcode
    if (!shortcode_exists('bms_tyre_search')) {
        add_shortcode('bms_tyre_search', 'bms_tyre_search_shortcode_handler');
    }
    
    // 5. Smart Scheduler Shortcode
    if (!shortcode_exists('bms_smart_scheduler')) {
        add_shortcode('bms_smart_scheduler', 'bms_smart_scheduler_shortcode_handler');
    }
    
    // 6. Service List Shortcode (missing implementation)
    if (!shortcode_exists('bms_service_list')) {
        add_shortcode('bms_service_list', 'bms_service_list_shortcode_handler');
    }
    
    // 7. Location Info Shortcode
    if (!shortcode_exists('bms_location_info')) {
        add_shortcode('bms_location_info', 'bms_location_info_shortcode_handler');
    }
}

/**
 * Simple Service Cards Shortcode Handler
 */
function bms_service_cards_shortcode_handler($atts) {
    $atts = shortcode_atts(array(
        'columns' => '3',
        'show_booking_buttons' => 'true',
        'category' => 'all'
    ), $atts, 'bms_service_cards');
    
    ob_start();
    ?>
    <div class="bms-service-cards" data-columns="<?php echo esc_attr($atts['columns']); ?>">
        
        <!-- MOT Test -->
        <div class="service-card" data-service="mot_test">
            <div class="service-icon">🔍</div>
            <h4>MOT Test</h4>
            <p class="service-price">£40.00</p>
            <p class="service-description">Class 4 MOT test for cars up to 3,000kg. Comprehensive safety, roadworthiness and environmental checks.</p>
            <?php if ($atts['show_booking_buttons'] === 'true'): ?>
            <button type="button" class="btn-book-service" data-service="mot_test" data-action="booking">
                Book MOT Test
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Interim Service -->
        <div class="service-card" data-service="interim_service">
            <div class="service-icon">🔧</div>
            <h4>Interim Service</h4>
            <p class="service-price">From £89.00</p>
            <p class="service-description">Essential checks every 6 months or 6,000 miles. Oil change, fluid levels, lights, and basic safety checks.</p>
            <?php if ($atts['show_booking_buttons'] === 'true'): ?>
            <button type="button" class="btn-book-service" data-service="interim_service" data-action="booking">
                Book Interim Service
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Full Service -->
        <div class="service-card" data-service="full_service">
            <div class="service-icon">⚙️</div>
            <h4>Full Service</h4>
            <p class="service-price">From £149.00</p>
            <p class="service-description">Comprehensive service every 12 months or 12,000 miles. Complete vehicle inspection and maintenance.</p>
            <?php if ($atts['show_booking_buttons'] === 'true'): ?>
            <button type="button" class="btn-book-service" data-service="full_service" data-action="booking">
                Book Full Service
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Tyre Fitting -->
        <div class="service-card" data-service="tyre_fitting">
            <div class="service-icon">🛞</div>
            <h4>Tyre Fitting & Balancing</h4>
            <p class="service-price">From £25.00 per tyre</p>
            <p class="service-description">Professional tyre fitting with wheel balancing and alignment. Wide range of premium tyres available.</p>
            <?php if ($atts['show_booking_buttons'] === 'true'): ?>
            <button type="button" class="btn-book-service" data-service="tyre_fitting" data-action="tyres">
                Find My Tyres
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Air Conditioning -->
        <div class="service-card" data-service="air_conditioning">
            <div class="service-icon">❄️</div>
            <h4>Air Conditioning Service</h4>
            <p class="service-price">From £79.00</p>
            <p class="service-description">Complete A/C system check, re-gas, and repair. Keep cool in summer with our professional service.</p>
            <?php if ($atts['show_booking_buttons'] === 'true'): ?>
            <button type="button" class="btn-book-service" data-service="air_conditioning" data-action="booking">
                Book A/C Service
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Brake Service -->
        <div class="service-card" data-service="brake_service">
            <div class="service-icon">🛑</div>
            <h4>Brake Service & Repair</h4>
            <p class="service-price">From £120.00</p>
            <p class="service-description">Brake pad replacement, disc skimming, brake fluid change, and complete brake system inspection.</p>
            <?php if ($atts['show_booking_buttons'] === 'true'): ?>
            <button type="button" class="btn-book-service" data-service="brake_service" data-action="booking">
                Book Brake Service
            </button>
            <?php endif; ?>
        </div>
        
    </div>
    
    <style>
    .bms-service-cards {
        display: grid;
        grid-template-columns: repeat(<?php echo esc_attr($atts['columns']); ?>, 1fr);
        gap: 20px;
        margin: 20px 0;
    }
    
    .service-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .service-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        transform: translateY(-2px);
    }
    
    .service-card.competitive-advantage {
        border-color: #10b981;
        background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    }
    
    .service-icon {
        font-size: 2.5rem;
        margin-bottom: 16px;
    }
    
    .service-card h4 {
        margin: 0 0 12px 0;
        color: #1f2937;
    }
    
    .service-price {
        font-size: 1.5rem;
        font-weight: bold;
        color: #3b82f6;
        margin: 8px 0;
    }
    
    .service-description {
        color: #6b7280;
        margin: 12px 0 20px 0;
        font-size: 14px;
    }
    
    .btn-book-service {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
        width: 100%;
    }
    
    .btn-book-service:hover {
        background: #2563eb;
    }
    
    .competitive-note {
        background: #10b981;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        margin: 12px 0;
    }
    
    @media (max-width: 768px) {
        .bms-service-cards {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle service booking buttons
        document.querySelectorAll('.btn-book-service').forEach(button => {
            button.addEventListener('click', function() {
                const service = this.getAttribute('data-service');
                const action = this.getAttribute('data-action');
                
                // Visual feedback
                document.querySelectorAll('.service-card, .service-list-item').forEach(card => {
                    card.classList.remove('selected');
                });
                this.closest('.service-card, .service-list-item').classList.add('selected');
                
                // Handle different actions
                if (action === 'tyres') {
                    // Redirect to tyre search page or show tyre search shortcode
                    const tyreSearchElement = document.querySelector('.bms-tyre-search-container');
                    if (tyreSearchElement) {
                        // If tyre search is on the same page, scroll to it
                        tyreSearchElement.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        // Try to find a page with tyre search or create a modal
                        console.log('Redirecting to tyre search for service:', service);
                        // You could redirect to a specific tyre page here
                        // window.location.href = '/tyres/';
                        alert('Tyre search functionality - redirect to tyre page or show tyre search form');
                    }
                } else {
                    // Handle booking action - redirect to booking form
                    const bookingFormElement = document.querySelector('.bms-booking-form, [data-shortcode="bms_booking_form"]');
                    if (bookingFormElement) {
                        // If booking form is on the same page, scroll to it and pre-select service
                        bookingFormElement.scrollIntoView({ behavior: 'smooth' });
                        
                        // Store selected service for booking form
                        if (window.BMSBookingForm) {
                            window.BMSBookingForm.bookingData.service = service;
                            if (window.BMSBookingForm.currentStep === 1) {
                                window.BMSBookingForm.nextStep();
                            }
                        }
                    } else {
                        // Try to redirect to booking page or show booking form
                        console.log('Redirecting to booking form for service:', service);
                        // You could redirect to a specific booking page here
                        // window.location.href = '/book-service/?service=' + service;
                        alert('Booking functionality - redirect to booking page or show booking form');
                    }
                }
                
                console.log('Service action:', action, 'for service:', service);
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * Simple Tyre Search Shortcode Handler
 */
function bms_tyre_search_shortcode_handler($atts) {
    $atts = shortcode_atts(array(
        'show_competitive' => 'true'
    ), $atts, 'bms_tyre_search');
    
    ob_start();
    ?>
    <div class="bms-tyre-search-container">
        
        <?php if ($atts['show_competitive'] === 'true'): ?>
        <div class="competitive-banner">
            <h4>🎯 Our other automotive services Advantage</h4>
            <p>Order tyres online instantly - Some providers require phone calls!</p>
        </div>
        <?php endif; ?>
        
        <h3>Find Your Perfect Tyres</h3>
        
        <!-- Search by Registration -->
        <div class="tyre-search-section">
            <h4>🚗 Search by Vehicle Registration</h4>
            <div class="lookup-input-group">
                <input type="text" 
                       id="tyre-vehicle-reg" 
                       placeholder="e.g. AB12 CDE" 
                       class="tyre-reg-input" />
                <button type="button" id="btn-search-tyres-by-reg" class="btn-primary">
                    Find My Tyres
                </button>
            </div>
        </div>
        
        <!-- Search Results -->
        <div id="tyre-results" style="display: none;">
            <h4>Available Tyres</h4>
            <div id="tyre-results-grid">
                <!-- Results will be populated here -->
            </div>
        </div>
        
    </div>
    
    <style>
    .bms-tyre-search-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .competitive-banner {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .tyre-search-section {
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .lookup-input-group {
        display: flex;
        gap: 12px;
        margin-top: 12px;
    }
    
    .tyre-reg-input {
        flex: 1;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 16px;
        text-transform: uppercase;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    </style>
    
    <?php
    return ob_get_clean();
}

/**
 * Simple Smart Scheduler Shortcode Handler
 */
function bms_smart_scheduler_shortcode_handler($atts) {
    $atts = shortcode_atts(array(
        'show_customer_prefs' => 'true',
        'max_suggestions' => '5'
    ), $atts, 'bms_smart_scheduler');
    
    ob_start();
    ?>
    <div class="bms-smart-scheduler">
        <h4>🤖 AI-Powered Appointment Suggestions</h4>
        
        <!-- Service Type Selection -->
        <div class="scheduler-preferences">
            <label>Service Type:</label>
            <select id="smart-service-type">
                <option value="mot_test">MOT Test (60 minutes)</option>
                <option value="interim_service">Interim Service (90 minutes)</option>
                <option value="full_service">Full Service (120 minutes)</option>
                <option value="tyre_fitting">Tyre Fitting (30 min per tyre)</option>
            </select>
        </div>
        
        <!-- Suggested Appointments -->
        <div class="smart-suggestions">
            <h5>Recommended Times:</h5>
            <div class="suggestion-slots">
                
                <!-- Example suggestions -->
                <div class="suggestion-slot recommended" onclick="selectAppointmentSlot('2025-08-25', '10:00')">
                    <div class="slot-date">Monday, 25 August 2025</div>
                    <div class="slot-time">10:00 AM</div>
                    <div class="slot-note">✨ Perfect time - typically quiet</div>
                </div>
                
                <div class="suggestion-slot" onclick="selectAppointmentSlot('2025-08-26', '14:00')">
                    <div class="slot-date">Tuesday, 26 August 2025</div>
                    <div class="slot-time">2:00 PM</div>
                    <div class="slot-note">👍 Good time - usually available</div>
                </div>
                
                <div class="suggestion-slot" onclick="selectAppointmentSlot('2025-08-27', '09:30')">
                    <div class="slot-date">Wednesday, 27 August 2025</div>
                    <div class="slot-time">9:30 AM</div>
                    <div class="slot-note">✨ Perfect time - typically quiet</div>
                </div>
                
            </div>
        </div>
        
        <!-- Manual Selection Link -->
        <div class="manual-selection-link">
            <button type="button" onclick="document.querySelector('.manual-date-time-selection').style.display='block'">
                Or choose manually →
            </button>
        </div>
        
    </div>
    
    <style>
    .bms-smart-scheduler {
        background: #f0f9ff;
        border: 2px solid #0ea5e9;
        border-radius: 12px;
        padding: 20px;
    }
    
    .scheduler-preferences {
        margin: 16px 0;
    }
    
    .scheduler-preferences select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        margin-top: 8px;
    }
    
    .suggestion-slots {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 16px;
    }
    
    .suggestion-slot {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .suggestion-slot:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
    }
    
    .suggestion-slot.recommended {
        border-color: #10b981;
        background: #f0fdf4;
    }
    
    .slot-date {
        font-weight: 600;
        color: #1f2937;
    }
    
    .slot-time {
        font-size: 1.2rem;
        color: #3b82f6;
        margin: 4px 0;
    }
    
    .slot-note {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .manual-selection-link {
        text-align: center;
        margin-top: 20px;
    }
    
    .manual-selection-link button {
        background: #6b7280;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
    }
    </style>
    
    <?php
    return ob_get_clean();
}

/**
 * Simple Service List Shortcode Handler
 */
function bms_service_list_shortcode_handler($atts) {
    $atts = shortcode_atts(array(
        'category' => 'all',
        'show_prices' => 'true',
        'show_booking_buttons' => 'true',
        'style' => 'list'
    ), $atts, 'bms_service_list');
    
    ob_start();
    ?>
    <div class="bms-service-list" data-style="<?php echo esc_attr($atts['style']); ?>">
        <h3>Our Services</h3>
        
        <div class="service-list-container">
            
            <!-- MOT Testing -->
            <div class="service-list-item" data-category="testing">
                <div class="service-info">
                    <div class="service-header">
                        <h4>🔍 MOT Test</h4>
                        <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">£40.00</span>
                        <?php endif; ?>
                    </div>
                    <p>Class 4 MOT test for cars up to 3,000kg. Comprehensive safety, roadworthiness aspects and environmental checks.</p>
                    <?php if ($atts['show_booking_buttons'] === 'true'): ?>
                    <button type="button" class="btn-book-service" data-service="mot_test" data-action="booking">
                        Book MOT Test
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Interim Service -->
            <div class="service-list-item" data-category="servicing">
                <div class="service-info">
                    <div class="service-header">
                        <h4>🔧 Interim Service</h4>
                        <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">From £89.00</span>
                        <?php endif; ?>
                    </div>
                    <p>Essential checks every 6 months or 6,000 miles. Oil change, fluid levels, lights, and basic safety checks.</p>
                    <?php if ($atts['show_booking_buttons'] === 'true'): ?>
                    <button type="button" class="btn-book-service" data-service="interim_service" data-action="booking">
                        Book Interim Service
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Full Service -->
            <div class="service-list-item" data-category="servicing">
                <div class="service-info">
                    <div class="service-header">
                        <h4>⚙️ Full Service</h4>
                        <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">From £149.00</span>
                        <?php endif; ?>
                    </div>
                    <p>Comprehensive service every 12 months or 12,000 miles. Complete vehicle inspection and maintenance.</p>
                    <?php if ($atts['show_booking_buttons'] === 'true'): ?>
                    <button type="button" class="btn-book-service" data-service="full_service" data-action="booking">
                        Book Full Service
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tyre Services -->
            <div class="service-list-item" data-category="tyres">
                <div class="service-info">
                    <div class="service-header">
                        <h4>🛞 Tyre Fitting & Balancing</h4>
                        <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">From £25.00 per tyre</span>
                        <?php endif; ?>
                    </div>
                    <p>Professional tyre fitting with wheel balancing and alignment. Wide range of premium tyres available.</p>
                    <?php if ($atts['show_booking_buttons'] === 'true'): ?>
                    <button type="button" class="btn-book-service" data-service="tyre_fitting" data-action="tyres">
                        Find My Tyres
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Air Conditioning -->
            <div class="service-list-item" data-category="climate">
                <div class="service-info">
                    <div class="service-header">
                        <h4>❄️ Air Conditioning Service</h4>
                        <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">From £79.00</span>
                        <?php endif; ?>
                    </div>
                    <p>Complete A/C system check, re-gas, and repair. Keep cool in summer with our professional service.</p>
                    <?php if ($atts['show_booking_buttons'] === 'true'): ?>
                    <button type="button" class="btn-book-service" data-service="air_conditioning" data-action="booking">
                        Book A/C Service
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Brake Service -->
            <div class="service-list-item" data-category="brakes">
                <div class="service-info">
                    <div class="service-header">
                        <h4>🛑 Brake Service & Repair</h4>
                        <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">From £120.00</span>
                        <?php endif; ?>
                    </div>
                    <p>Brake pad replacement, disc skimming, brake fluid change, and complete brake system inspection.</p>
                    <?php if ($atts['show_booking_buttons'] === 'true'): ?>
                    <button type="button" class="btn-book-service" data-service="brake_service" data-action="booking">
                        Book Brake Service
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Battery Service -->
            <div class="service-list-item" data-category="electrical">
                <div class="service-info">
                    <div class="service-header">
                        <h4>🔋 Battery Testing & Replacement</h4>
                        <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">From £89.00</span>
                        <?php endif; ?>
                    </div>
                    <p>Battery health check, replacement, and charging system diagnosis. Keep your car starting reliably.</p>
                    <?php if ($atts['show_booking_buttons'] === 'true'): ?>
                    <button type="button" class="btn-book-service" data-service="battery_service" data-action="booking">
                        Book Battery Service
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
        <div class="service-list-footer">
            <p><strong>All prices include VAT.</strong> Contact us for a detailed quote for your specific vehicle.</p>
        </div>
        
    </div>
    
    <style>
    .bms-service-list {
        max-width: 800px;
        margin: 20px 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .bms-service-list h3 {
        color: #1f2937;
        margin-bottom: 20px;
        font-size: 1.5rem;
    }
    
    .service-list-container {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .service-list-item {
        border-bottom: 1px solid #e5e7eb;
        padding: 20px;
        transition: background-color 0.2s ease;
    }
    
    .service-list-item:last-child {
        border-bottom: none;
    }
    
    .service-list-item:hover {
        background-color: #f9fafb;
    }
    
    .service-list-item.competitive-advantage {
        background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
        border-left: 4px solid #10b981;
    }
    
    .service-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .service-info h4 {
        margin: 0;
        color: #1f2937;
        font-size: 1.1rem;
        flex: 1;
    }
    
    .service-info p {
        margin: 0 0 15px 0;
        color: #6b7280;
        line-height: 1.5;
    }
    
    .service-price {
        display: inline-block;
        background: #3b82f6;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-left: 15px;
        white-space: nowrap;
    }
    
    .btn-book-service {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    
    .btn-book-service:hover {
        background: #2563eb;
    }
    
    .competitive-note {
        background: #10b981;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        margin: 8px 0;
        display: inline-block;
    }
    
    .service-list-footer {
        margin-top: 20px;
        padding: 20px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    
    .competitive-message {
        margin-top: 15px;
        padding: 15px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-radius: 6px;
    }
    
    .competitive-message ul {
        margin: 10px 0 0 0;
        padding-left: 20px;
    }
    
    .competitive-message li {
        margin: 4px 0;
        font-size: 0.9rem;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .bms-service-list {
            margin: 10px;
        }
        
        .service-list-item {
            padding: 15px;
        }
        
        .service-info h4 {
            font-size: 1rem;
        }
        
        .service-info p {
            font-size: 0.9rem;
        }
    }
    </style>
    
    <?php
    return ob_get_clean();
}

/**
 * Simple Location Info Shortcode Handler
 */
function bms_location_info_shortcode_handler($atts) {
    $atts = shortcode_atts(array(
        'show_map' => 'true',
        'show_hours' => 'true'
    ), $atts, 'bms_location_info');
    
    ob_start();
    ?>
    <div class="bms-location-info">
        <h3>Blue Motors Southampton</h3>
        <div class="location-details">
            <p><strong>Address:</strong> 1 Kent St, Northam, Southampton SO14 5SP</p>
            <p><strong>Phone:</strong> 023 8000 0000</p>
            <p><strong>Email:</strong> southampton@bluemotors.co.uk</p>
        </div>
        
        <?php if ($atts['show_hours'] === 'true'): ?>
        <div class="opening-hours">
            <h4>Opening Hours</h4>
            <ul>
                <li>Monday - Friday: 8:00 AM - 6:00 PM</li>
                <li>Saturday: 8:00 AM - 4:00 PM</li>
                <li>Sunday: Closed</li>
            </ul>
        </div>
        <?php endif; ?>
        
    </div>
    
    <style>
    .bms-location-info {
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .location-details p {
        margin: 8px 0;
    }
    
    .opening-hours h4 {
        margin-top: 20px;
        margin-bottom: 12px;
        color: #1f2937;
    }
    
    .opening-hours ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .opening-hours li {
        padding: 4px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    </style>
    
    <?php
    return ob_get_clean();
}

// Initialize shortcodes on WordPress init
add_action('init', 'bms_init_all_shortcodes');
