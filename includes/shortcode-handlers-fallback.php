<?php
/**
 * Missing Shortcode Handlers
 * Blue Motors Southampton Plugin
 * 
 * Provides fallback handlers for shortcodes that are referenced but missing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Service List Shortcode Handler
 */
if (!function_exists('bms_service_list_shortcode_handler')) {
    function bms_service_list_shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'style' => 'list',
            'show_prices' => 'true',
            'category' => 'all'
        ), $atts, 'bms_service_list');
        
        ob_start();
        ?>
        <div class="bms-service-list">
            <h3>Our Services</h3>
            <ul class="service-list">
                <li class="service-item">
                    <strong>MOT Test</strong>
                    <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">£40.00</span>
                    <?php endif; ?>
                    <p>Class 4 MOT test for cars up to 3,000kg</p>
                </li>
                <li class="service-item">
                    <strong>Full Service</strong>
                    <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">From £149.00</span>
                    <?php endif; ?>
                    <p>Comprehensive vehicle service with detailed inspection</p>
                </li>
                <li class="service-item">
                    <strong>Interim Service</strong>
                    <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">From £89.00</span>
                    <?php endif; ?>
                    <p>Essential maintenance between full services</p>
                </li>
                <li class="service-item">
                    <strong>Brake Check</strong>
                    <?php if ($atts['show_prices'] === 'true'): ?>
                        <span class="service-price">£25.00</span>
                    <?php endif; ?>
                    <p>Comprehensive brake system inspection</p>
                </li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Tyre Search Shortcode Handler
 */
if (!function_exists('bms_tyre_search_shortcode_handler')) {
    function bms_tyre_search_shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'style' => 'compact',
            'competitive_messaging' => 'true'
        ), $atts, 'bms_tyre_search');
        
        ob_start();
        ?>
        <div class="bms-tyre-search">
            <h3>🛞 Tyre Search & Fitting</h3>
            
            <?php if ($atts['competitive_messaging'] === 'true'): ?>
            <div class="competitive-message">
                <p><strong>✅ Order online - no phone calls needed!</strong></p>
                <p>Unlike others, we make tyre ordering simple and convenient.</p>
            </div>
            <?php endif; ?>
            
            <form class="tyre-search-form">
                <div class="form-row">
                    <label for="tyre-width">Width:</label>
                    <input type="number" id="tyre-width" name="width" placeholder="195" min="125" max="335" step="5" />
                </div>
                <div class="form-row">
                    <label for="tyre-profile">Profile:</label>
                    <input type="number" id="tyre-profile" name="profile" placeholder="65" min="25" max="85" step="5" />
                </div>
                <div class="form-row">
                    <label for="tyre-rim">Rim Size:</label>
                    <input type="number" id="tyre-rim" name="rim" placeholder="15" min="13" max="22" />
                </div>
                <button type="button" class="btn-search-tyres">Search Tyres</button>
            </form>
            
            <div class="tyre-results" style="display: none;">
                <h4>Available Tyres</h4>
                <p>Tyre search results will appear here...</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Smart Scheduler Shortcode Handler
 */
if (!function_exists('bms_smart_scheduler_shortcode_handler')) {
    function bms_smart_scheduler_shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'style' => 'calendar',
            'service' => 'any'
        ), $atts, 'bms_smart_scheduler');
        
        ob_start();
        ?>
        <div class="bms-smart-scheduler">
            <h3>📅 Smart Appointment Scheduler</h3>
            <p>Book your appointment using our intelligent scheduling system.</p>
            
            <div class="scheduler-container">
                <div class="service-selection">
                    <label for="scheduler-service">Service Type:</label>
                    <select id="scheduler-service" name="service">
                        <option value="mot_test">MOT Test</option>
                        <option value="full_service">Full Service</option>
                        <option value="interim_service">Interim Service</option>
                        <option value="brake_check">Brake Check</option>
                        <option value="diagnostic_check">Diagnostic Check</option>
                    </select>
                </div>
                
                <div class="date-selection">
                    <label for="preferred-date">Preferred Date:</label>
                    <input type="date" id="preferred-date" name="preferred_date" 
                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" />
                </div>
                
                <div class="time-slots" style="display: none;">
                    <h4>Available Times</h4>
                    <div class="time-slot-grid">
                        <button type="button" class="time-slot" data-time="09:00">09:00</button>
                        <button type="button" class="time-slot" data-time="10:30">10:30</button>
                        <button type="button" class="time-slot" data-time="14:00">14:00</button>
                        <button type="button" class="time-slot" data-time="15:30">15:30</button>
                    </div>
                </div>
                
                <button type="button" class="btn-check-availability">Check Availability</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Location Info Shortcode Handler
 */
if (!function_exists('bms_location_info_shortcode_handler')) {
    function bms_location_info_shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'style' => 'card',
            'show_map' => 'false'
        ), $atts, 'bms_location_info');
        
        ob_start();
        ?>
        <div class="bms-location-info">
            <h3>📍 Blue Motors Southampton</h3>
            
            <div class="location-details">
                <div class="address">
                    <h4>Address</h4>
                    <p>
                        Blue Motors Southampton<br>
                        Southampton, Hampshire<br>
                        United Kingdom
                    </p>
                </div>
                
                <div class="contact">
                    <h4>Contact</h4>
                    <p>
                        📞 Phone: Available during business hours<br>
                        📧 Email: info@bluemotors-southampton.co.uk<br>
                        🌐 Website: bluemotors-southampton.co.uk
                    </p>
                </div>
                
                <div class="opening-hours">
                    <h4>Opening Hours</h4>
                    <ul>
                        <li>Monday - Friday: 8:00 AM - 6:00 PM</li>
                        <li>Saturday: 8:00 AM - 4:00 PM</li>
                        <li>Sunday: Closed</li>
                    </ul>
                </div>
                
                <?php if ($atts['show_map'] === 'true'): ?>
                <div class="location-map">
                    <p><em>Interactive map functionality coming soon...</em></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Contact Form Shortcode Handler
 */
if (!function_exists('bms_contact_form_shortcode_handler')) {
    function bms_contact_form_shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'subject' => 'General Enquiry',
            'show_phone' => 'true'
        ), $atts, 'bms_contact_form');
        
        ob_start();
        ?>
        <div class="bms-contact-form">
            <h3>✉️ Contact Blue Motors</h3>
            <p>Get in touch with us for any questions about our services.</p>
            
            <form class="contact-form" method="post">
                <div class="form-row">
                    <label for="contact-name">Name *</label>
                    <input type="text" id="contact-name" name="name" required />
                </div>
                
                <div class="form-row">
                    <label for="contact-email">Email *</label>
                    <input type="email" id="contact-email" name="email" required />
                </div>
                
                <?php if ($atts['show_phone'] === 'true'): ?>
                <div class="form-row">
                    <label for="contact-phone">Phone</label>
                    <input type="tel" id="contact-phone" name="phone" />
                </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <label for="contact-subject">Subject</label>
                    <input type="text" id="contact-subject" name="subject" 
                           value="<?php echo esc_attr($atts['subject']); ?>" />
                </div>
                
                <div class="form-row">
                    <label for="contact-message">Message *</label>
                    <textarea id="contact-message" name="message" rows="5" required></textarea>
                </div>
                
                <button type="submit" class="btn-send-message">Send Message</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Opening Hours Shortcode Handler
 */
if (!function_exists('bms_opening_hours_shortcode_handler')) {
    function bms_opening_hours_shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'style' => 'table',
            'highlight_today' => 'true'
        ), $atts, 'bms_opening_hours');
        
        $current_day = date('w'); // 0 = Sunday, 6 = Saturday
        
        ob_start();
        ?>
        <div class="bms-opening-hours">
            <h3>🕒 Opening Hours</h3>
            
            <table class="opening-hours-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <tr <?php echo ($current_day == 1 && $atts['highlight_today'] === 'true') ? 'class="today"' : ''; ?>>
                        <td>Monday</td>
                        <td>8:00 AM - 6:00 PM</td>
                    </tr>
                    <tr <?php echo ($current_day == 2 && $atts['highlight_today'] === 'true') ? 'class="today"' : ''; ?>>
                        <td>Tuesday</td>
                        <td>8:00 AM - 6:00 PM</td>
                    </tr>
                    <tr <?php echo ($current_day == 3 && $atts['highlight_today'] === 'true') ? 'class="today"' : ''; ?>>
                        <td>Wednesday</td>
                        <td>8:00 AM - 6:00 PM</td>
                    </tr>
                    <tr <?php echo ($current_day == 4 && $atts['highlight_today'] === 'true') ? 'class="today"' : ''; ?>>
                        <td>Thursday</td>
                        <td>8:00 AM - 6:00 PM</td>
                    </tr>
                    <tr <?php echo ($current_day == 5 && $atts['highlight_today'] === 'true') ? 'class="today"' : ''; ?>>
                        <td>Friday</td>
                        <td>8:00 AM - 6:00 PM</td>
                    </tr>
                    <tr <?php echo ($current_day == 6 && $atts['highlight_today'] === 'true') ? 'class="today"' : ''; ?>>
                        <td>Saturday</td>
                        <td>8:00 AM - 4:00 PM</td>
                    </tr>
                    <tr <?php echo ($current_day == 0 && $atts['highlight_today'] === 'true') ? 'class="today"' : ''; ?>>
                        <td>Sunday</td>
                        <td>Closed</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <style>
        .opening-hours-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .opening-hours-table th, .opening-hours-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .opening-hours-table th { background-color: #f8f9fa; font-weight: bold; }
        .opening-hours-table tr.today { background-color: #e3f2fd; font-weight: bold; }
        </style>
        <?php
        return ob_get_clean();
    }
}

// Register the shortcodes (only if they don't already exist)
add_action('init', function() {
    if (!shortcode_exists('bms_service_list')) {
        add_shortcode('bms_service_list', 'bms_service_list_shortcode_handler');
    }
    
    if (!shortcode_exists('bms_tyre_search')) {
        add_shortcode('bms_tyre_search', 'bms_tyre_search_shortcode_handler');
    }
    
    if (!shortcode_exists('bms_smart_scheduler')) {
        add_shortcode('bms_smart_scheduler', 'bms_smart_scheduler_shortcode_handler');
    }
    
    if (!shortcode_exists('bms_location_info')) {
        add_shortcode('bms_location_info', 'bms_location_info_shortcode_handler');
    }
    
    if (!shortcode_exists('bms_contact_form')) {
        add_shortcode('bms_contact_form', 'bms_contact_form_shortcode_handler');
    }
    
    if (!shortcode_exists('bms_opening_hours')) {
        add_shortcode('bms_opening_hours', 'bms_opening_hours_shortcode_handler');
    }
});