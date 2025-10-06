<?php
/**
 * Location Info Shortcode for Blue Motors Southampton
 * 
 * Displays business location, hours, and contact information
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the location info shortcode
 */
add_shortcode('bms_location_info', 'bms_location_info_shortcode');

/**
 * Location info shortcode handler
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function bms_location_info_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_map' => 'false',        // Include Google Maps
        'show_hours' => 'true',       // Display opening hours
        'show_contact' => 'true',     // Show contact info
        'show_directions' => 'true',  // Show directions link
        'style' => 'card',            // Display style: card, list, minimal
        'theme' => 'default',         // Color theme
        'class' => ''                 // Additional CSS class), $atts, 'bms_location_info');
    ), $atts, 'bms_location_info');
    
    // Get business information from options
    $business_name = get_option('bms_business_name', 'Blue Motors Southampton');
    $business_address = get_option('bms_business_address', '1 Kent St, Northam, Southampton SO14 5SP');
    $business_phone = get_option('bms_business_phone', '023 8000 0000');
    $business_email = get_option('bms_business_email', 'southampton@bluemotors.co.uk');
    
    // Default opening hours with proper type checking
    $opening_hours = get_option('bms_business_hours', array(
        'monday' => array('open' => '08:00', 'close' => '18:00'),
        'tuesday' => array('open' => '08:00', 'close' => '18:00'),
        'wednesday' => array('open' => '08:00', 'close' => '18:00'),
        'thursday' => array('open' => '08:00', 'close' => '18:00'),
        'friday' => array('open' => '08:00', 'close' => '18:00'),
        'saturday' => array('open' => '08:00', 'close' => '16:00'),
        'sunday' => array('open' => 'closed', 'close' => 'closed')
    ));
    
    // Ensure $opening_hours is always an array
    if (!is_array($opening_hours) || empty($opening_hours)) {
        $opening_hours = array(
            'monday' => array('open' => '08:00', 'close' => '18:00'),
            'tuesday' => array('open' => '08:00', 'close' => '18:00'),
            'wednesday' => array('open' => '08:00', 'close' => '18:00'),
            'thursday' => array('open' => '08:00', 'close' => '18:00'),
            'friday' => array('open' => '08:00', 'close' => '18:00'),
            'saturday' => array('open' => '08:00', 'close' => '16:00'),
            'sunday' => array('open' => 'closed', 'close' => 'closed')
        );
    }
    
    // Google Maps coordinates (approximate for Southampton address)
    $map_lat = get_option('bms_location_lat', '50.9097');
    $map_lng = get_option('bms_location_lng', '-1.3885');
    
    // Start output buffering
    ob_start();
    ?>
    
    <div class="bms-location-info bms-location-<?php echo esc_attr($atts['style']); ?> <?php echo esc_attr($atts['class']); ?>" 
         data-theme="<?php echo esc_attr($atts['theme']); ?>">
        
        <?php if ($atts['style'] === 'card'): ?>
        <!-- Card Style Layout -->
        <div class="location-card">
            <div class="location-header">
                <h3><?php echo esc_html($business_name); ?></h3>
                <div class="location-badges">
                    <span class="badge local-specialist">Southampton Specialist</span>
                    <span class="badge competitive">Better than industry leaders</span>
                </div>
            </div>
            
            <div class="location-content">
                <?php if ($atts['show_contact'] === 'true'): ?>
                <div class="contact-section">
                    <h4>📍 Visit Our Garage</h4>
                    <div class="contact-details">
                        <div class="contact-item">
                            <span class="contact-icon">🏢</span>
                            <div class="contact-info">
                                <strong>Address:</strong><br>
                                <?php echo nl2br(esc_html($business_address)); ?>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <span class="contact-icon">📞</span>
                            <div class="contact-info">
                                <strong>Phone:</strong><br>
                                <a href="tel:<?php echo esc_attr(str_replace(' ', '', $business_phone)); ?>">
                                    <?php echo esc_html($business_phone); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <span class="contact-icon">✉️</span>
                            <div class="contact-info">
                                <strong>Email:</strong><br>
                                <a href="mailto:<?php echo esc_attr($business_email); ?>">
                                    <?php echo esc_html($business_email); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_hours'] === 'true'): ?>
                <div class="hours-section">
                    <h4>🕒 Opening Hours</h4>
                    <table class="opening-hours-table">
                        <?php foreach ($opening_hours as $day => $hours): ?>;
                        <tr class="<?php echo date('l') === ucfirst($day) ? 'current-day' : ''; ?>">
                            <td class="day-name"><?php echo esc_html(ucfirst($day)); ?></td>
                            <td class="day-hours">
                                <?php if (isset($hours['open']) && $hours['open'] === 'closed'): ?>
                                    <span class="closed">Closed</span>
                                <?php else: ?>
                                    <?php echo esc_html(isset($hours['open']) ? $hours['open'] : '08:00'); ?> - <?php echo esc_html(isset($hours['close']) ? $hours['close'] : '18:00'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_directions'] === 'true'): ?>
                <div class="directions-section">
                    <h4>🧭 Get Directions</h4>
                    <div class="direction-buttons">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($business_address); ?>" 
                           target="_blank" class="btn-directions btn-google-maps">
                            <span class="btn-icon">🗺️</span>
                            Google Maps
                        </a>
                        
                        <a href="https://waze.com/ul?ll=<?php echo $map_lat; ?>,<?php echo $map_lng; ?>&navigate=yes" 
                           target="_blank" class="btn-directions btn-waze">
                            <span class="btn-icon">🚗</span>
                            Waze
                        </a>
                        
                        <a href="https://www.bing.com/maps/directions?rtp=~pos.<?php echo $map_lat; ?>_<?php echo $map_lng; ?>_<?php echo urlencode($business_name); ?>" 
                           target="_blank" class="btn-directions btn-bing">
                            <span class="btn-icon">🔍</span>
                            Bing Maps
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="/book-service" class="btn-action btn-primary">
                        <span class="btn-icon">📅</span>
                        Book a Service
                    </a>
                    
                    <a href="tel:<?php echo esc_attr(str_replace(' ', '', $business_phone)); ?>" 
                       class="btn-action btn-secondary">
                        <span class="btn-icon">📞</span>
                        Call Now
                    </a>
                </div>
                
                <!-- Professional Advantage Note -->
                <div class="competitive-advantage-note">
                    <h5>🎯 Why Choose Blue Motors Over industry leaders?</h5>
                    <ul>
                        <li>✅ <strong>Local Expertise:</strong> Southampton specialists vs generic chain</li>
                        <li>✅ <strong>Online Booking:</strong> Easy appointment system</li>
                        <li>✅ <strong>Tyre Ordering:</strong> Order online - F1 requires phone calls!</li>
                        <li>✅ <strong>Personal Service:</strong> We know our local customers</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <?php elseif ($atts['style'] === 'list'): ?>
        <!-- List Style Layout -->
        <div class="location-list">
            <h3><?php echo esc_html($business_name); ?></h3>
            
            <ul class="location-details-list">
                <li><strong>📍 Address:</strong> <?php echo esc_html($business_address); ?></li>
                <li><strong>📞 Phone:</strong> <a href="tel:<?php echo esc_attr(str_replace(' ', '', $business_phone)); ?>"><?php echo esc_html($business_phone); ?></a></li>
                <li><strong>✉️ Email:</strong> <a href="mailto:<?php echo esc_attr($business_email); ?>"><?php echo esc_html($business_email); ?></a></li>
                
                <?php if ($atts['show_hours'] === 'true'): ?>
                <li>
                    <strong>🕒 Hours:</strong>
                    <ul class="hours-list">
                        <?php foreach ($opening_hours as $day => $hours): ?>;
                        <li><?php echo esc_html(ucfirst($day)); ?>: 
                            <?php if (isset($hours['open']) && $hours['open'] === 'closed'): ?>
                                Closed
                            <?php else: ?>
                                <?php echo esc_html(isset($hours['open']) ? $hours['open'] : '08:00'); ?> - <?php echo esc_html(isset($hours['close']) ? $hours['close'] : '18:00'); ?>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <?php else: // minimal style ?>
        <!-- Minimal Style Layout -->
        <div class="location-minimal">
            <div class="minimal-header">
                <h4><?php echo esc_html($business_name); ?></h4>
                <span class="local-badge">Southampton Garage</span>
            </div>
            
            <div class="minimal-content">
                <?php if ($atts['show_contact'] === 'true'): ?>
                <p>📍 <?php echo esc_html($business_address); ?></p>
                <p>📞 <a href="tel:<?php echo esc_attr(str_replace(' ', '', $business_phone)); ?>"><?php echo esc_html($business_phone); ?></a></p>
                <?php endif; ?>
                
                <?php if ($atts['show_hours'] === 'true'): ?>
                <div class="minimal-hours">
                    <h5>🕒 Opening Hours:</h5>
                    <ul class="hours-compact">
                        <?php foreach ($opening_hours as $day => $hours): ?>
                        <li><strong><?php echo esc_html(ucfirst($day)); ?>:</strong> 
                            <?php if (isset($hours['open']) && $hours['open'] === 'closed'): ?>
                                Closed
                            <?php else: ?>
                                <?php echo esc_html(isset($hours['open']) ? $hours['open'] : '08:00'); ?> - <?php echo esc_html(isset($hours['close']) ? $hours['close'] : '18:00'); ?>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_directions'] === 'true'): ?>
                <p>🗺️ <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($business_address); ?>" target="_blank">Get Directions</a></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_map'] === 'true'): ?>
        <!-- Google Maps Integration -->
        <div class="location-map-section">
            <h4>🗺️ Find Us on the Map</h4>
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=<?php echo urlencode($business_address); ?>&zoom=15"
                    width="100%" 
                    height="300" 
                    style="border:0; border-radius: 8px;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                
                <div class="map-overlay">
                    <p><strong>Can't see the map?</strong></p>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($business_address); ?>" 
                       target="_blank" class="btn-map-fallback">
                        Open in Google Maps
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Schema Markup for Local SEO -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "AutoRepair",
            "name": "<?php echo esc_js($business_name); ?>",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "1 Kent St, Northam",
                "addressLocality": "Southampton",
                "postalCode": "SO14 5SP",
                "addressCountry": "GB"
            },
            "telephone": "<?php echo esc_js($business_phone); ?>",
            "email": "<?php echo esc_js($business_email); ?>",
            "geo": {
                "@type": "GeoCoordinates",
                "latitude": "<?php echo esc_js($map_lat); ?>",
                "longitude": "<?php echo esc_js($map_lng); ?>"
            },
            "openingHoursSpecification": [
                <?php 
                $schema_hours = array();
                foreach ($opening_hours as $day => $hours) {
                    if (isset($hours['open']) && $hours['open'] !== 'closed') {
                        $schema_hours[] = sprintf(
                            '{"@type": "OpeningHoursSpecification", "dayOfWeek": "%s", "opens": "%s", "closes": "%s"}',
                            ucfirst($day),
                            isset($hours['open']) ? $hours['open'] : '08:00',
                            isset($hours['close']) ? $hours['close'] : '18:00'
                        );
                    }
                }
                echo implode(',', $schema_hours);
                ?>
            ],
            "priceRange": "££",
            "paymentAccepted": ["Cash", "Credit Card"],
            "currenciesAccepted": "GBP"
        }
        </script>
    </div>
    
    <style>
    .bms-location-info {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        margin: 20px 0;
    }
    
    /* Card Style */
    .location-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        max-width: 600px;
    }
    
    .location-header {
        text-align: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .location-header h3 {
        margin: 0 0 12px 0;
        color: #1e3a8a;
        font-size: 24px;
    }
    
    .location-badges {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .badge {
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge.local-specialist {
        background: #dbeafe;
        color: #1e3a8a;
    }
    
    .badge.competitive {
        background: #dcfce7;
        color: #166534;
    }
    
    .contact-section, .hours-section, .directions-section {
        margin-bottom: 24px;
    }
    
    .contact-section h4, .hours-section h4, .directions-section h4 {
        margin: 0 0 12px 0;
        color: #374151;
        font-size: 16px;
    }
    
    .contact-details {
        display: grid;
        gap: 12px;
    }
    
    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .contact-icon {
        font-size: 18px;
        margin-top: 2px;
    }
    
    .contact-info a {
        color: #3b82f6;
        text-decoration: none;
    }
    
    .contact-info a:hover {
        text-decoration: underline;
    }
    
    .opening-hours-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .opening-hours-table tr {
        border-bottom: 1px solid #f3f4f6;
    }
    
    .opening-hours-table tr.current-day {
        background: #eff6ff;
        font-weight: 600;
    }
    
    .opening-hours-table td {
        padding: 8px 12px;
    }
    
    .day-name {
        font-weight: 600;
        width: 30%;
    }
    
    .closed {
        color: #dc2626;
        font-style: italic;
    }
    
    .direction-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .btn-directions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-google-maps {
        background: #4285f4;
        color: white;
    }
    
    .btn-waze {
        background: #33ccff;
        color: white;
    }
    
    .btn-bing {
        background: #00809d;
        color: white;
    }
    
    .btn-directions:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .action-buttons {
        display: flex;
        gap: 12px;
        margin: 24px 0;
    }
    
    .btn-action {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
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
    
    .btn-secondary:hover {
        background: #4b5563;
    }
    
    .competitive-advantage-note {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 16px;
        margin-top: 24px;
    }
    
    .competitive-advantage-note h5 {
        margin: 0 0 8px 0;
        color: #1e3a8a;
    }
    
    .competitive-advantage-note ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .competitive-advantage-note li {
        margin-bottom: 4px;
    }
    
    /* List Style */
    .location-list h3 {
        color: #1e3a8a;
        margin-bottom: 16px;
    }
    
    .location-details-list {
        list-style: none;
        padding: 0;
    }
    
    .location-details-list > li {
        margin-bottom: 8px;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .hours-list {
        margin-top: 8px;
        padding-left: 20px;
    }
    
    /* Minimal Style */
    .location-minimal {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
    }
    
    .minimal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    
    .minimal-header h4 {
        margin: 0;
        color: #1e3a8a;
    }
    
    .local-badge {
        background: #dcfce7;
        color: #166534;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .minimal-content p {
        margin: 4px 0;
        font-size: 14px;
    }
    
    .minimal-content a {
        color: #3b82f6;
        text-decoration: none;
    }
    
    /* Map Section */
    .location-map-section {
        margin-top: 24px;
    }
    
    .map-container {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .map-overlay {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(255,255,255,0.9);
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
    }
    
    .btn-map-fallback {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 600;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .location-card {
            padding: 16px;
        }
        
        .direction-buttons {
            flex-direction: column;
        }
        
        .btn-directions {
            justify-content: center;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .minimal-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
    }
    </style>
    
    <?php
    
    return ob_get_clean();
}

/**
 * Register additional location-related shortcodes
 */
add_shortcode('bms_opening_hours', 'bms_opening_hours_shortcode');
add_shortcode('bms_contact_form', 'bms_contact_form_shortcode');

/**
 * Opening hours only shortcode
 */
function bms_opening_hours_shortcode($atts) {
    $atts = shortcode_atts(array(
        'style' => 'table', // table, list
        'highlight_today' => 'true'), $atts, 'bms_opening_hours');
    
    // Get business hours directly with proper type checking
    $opening_hours = get_option('bms_business_hours', array(
        'monday' => array('open' => '08:00', 'close' => '18:00'),
        'tuesday' => array('open' => '08:00', 'close' => '18:00'),
        'wednesday' => array('open' => '08:00', 'close' => '18:00'),
        'thursday' => array('open' => '08:00', 'close' => '18:00'),
        'friday' => array('open' => '08:00', 'close' => '18:00'),
        'saturday' => array('open' => '08:00', 'close' => '16:00'),
        'sunday' => array('open' => 'closed', 'close' => 'closed')
    ));
    
    // Ensure $opening_hours is always an array
    if (!is_array($opening_hours) || empty($opening_hours)) {
        $opening_hours = array(
            'monday' => array('open' => '08:00', 'close' => '18:00'),
            'tuesday' => array('open' => '08:00', 'close' => '18:00'),
            'wednesday' => array('open' => '08:00', 'close' => '18:00'),
            'thursday' => array('open' => '08:00', 'close' => '18:00'),
            'friday' => array('open' => '08:00', 'close' => '18:00'),
            'saturday' => array('open' => '08:00', 'close' => '16:00'),
            'sunday' => array('open' => 'closed', 'close' => 'closed')
        );
    }
    
    $business_name = get_option('bms_business_name', 'Blue Motors Southampton');
    
    ob_start();
    ?>
    <div class="bms-opening-hours">
        <h4>🕒 <?php echo esc_html($business_name); ?> - Opening Hours</h4>
        <div class="hours-display <?php echo esc_attr($atts['style']); ?>">
            <?php if ($atts['style'] === 'table'): ?>
                <table class="hours-table">
                    <tbody>
                        <?php foreach ($opening_hours as $day => $hours): ?>
                            <tr class="<?php echo date('l') === ucfirst($day) && $atts['highlight_today'] === 'true' ? 'today' : ''; ?>">
                                <td class="day-name"><strong><?php echo esc_html(ucfirst($day)); ?></strong></td>
                                <td class="day-hours">
                                    <?php if (isset($hours['open']) && $hours['open'] === 'closed'): ?>
                                        <span class="closed">Closed</span>
                                    <?php else: ?>
                                        <span class="open"><?php echo esc_html($hours['open'] ?? '08:00'); ?> - <?php echo esc_html($hours['close'] ?? '18:00'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <ul class="hours-list">
                    <?php foreach ($opening_hours as $day => $hours): ?>
                        <li class="<?php echo date('l') === ucfirst($day) && $atts['highlight_today'] === 'true' ? 'today' : ''; ?>">
                            <strong><?php echo esc_html(ucfirst($day)); ?>:</strong>
                            <?php if (isset($hours['open']) && $hours['open'] === 'closed'): ?>
                                <span class="closed">Closed</span>
                            <?php else: ?>
                                <span class="open"><?php echo esc_html($hours['open'] ?? '08:00'); ?> - <?php echo esc_html($hours['close'] ?? '18:00'); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    .bms-opening-hours { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 5px; }
    .bms-opening-hours h4 { margin: 0 0 10px 0; color: #333; }
    .hours-table { width: 100%; border-collapse: collapse; }
    .hours-table td { padding: 8px 12px; border-bottom: 1px solid #eee; }
    .hours-table .today { background: #e8f5e8; font-weight: bold; }
    .hours-list { list-style: none; padding: 0; margin: 0; }
    .hours-list li { padding: 5px 0; }
    .hours-list .today { background: #e8f5e8; padding: 8px; border-radius: 3px; font-weight: bold; }
    .closed { color: #d63638; }
    .open { color: #00a32a; }
    </style>
    <?php
    
    return ob_get_clean();
}

/**
 * Contact form shortcode
 */
function bms_contact_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => 'Contact Us',
        'show_location' => 'true'), $atts, 'bms_contact_form');
    
    ob_start();
    ?>
    <div class="bms-contact-form-container">
        <h3><?php echo esc_html($atts['title']); ?></h3>
        
        <?php if ($atts['show_location'] === 'true'): ?>
        <?php echo do_shortcode('[bms_location_info style="minimal" show_hours="false"]'); ?>
        <hr style="margin: 20px 0;">
        <?php endif; ?>
        
        <form class="bms-contact-form" method="post" action="">
            <div class="form-group">
                <label for="contact-name">Name *</label>
                <input type="text" id="contact-name" name="contact_name" required>
            </div>
            
            <div class="form-group">
                <label for="contact-email">Email *</label>
                <input type="email" id="contact-email" name="contact_email" required>
            </div>
            
            <div class="form-group">
                <label for="contact-phone">Phone</label>
                <input type="tel" id="contact-phone" name="contact_phone">
            </div>
            
            <div class="form-group">
                <label for="contact-subject">Subject</label>
                <select id="contact-subject" name="contact_subject">
                    <option value="general">General Enquiry</option>
                    <option value="booking">Booking Question</option>
                    <option value="service">Service Information</option>
                    <option value="tyres">Tyre Enquiry</option>
                    <option value="complaint">Complaint</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="contact-message">Message *</label>
                <textarea id="contact-message" name="contact_message" rows="5" required></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-submit">Send Message</button>
            </div>
        </form>
        
        <div class="contact-note">
            <p><strong>🎯 Why Choose Blue Motors?</strong></p>
            <p>Unlike industry leaders, we offer personal service and online booking for all services including tyres!</p>
        </div>
    </div>
    
    <style>
    .bms-contact-form-container {
        max-width: 500px;
        margin: 20px 0;
    }
    
    .bms-contact-form .form-group {
        margin-bottom: 16px;
    }
    
    .bms-contact-form label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #374151;
    }
    
    .bms-contact-form input,
    .bms-contact-form select,
    .bms-contact-form textarea {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 16px;
    }
    
    .bms-contact-form input:focus,
    .bms-contact-form select:focus,
    .bms-contact-form textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .btn-submit {
        background: #3b82f6;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .btn-submit:hover {
        background: #2563eb;
    }
    
    .contact-note {
        background: #eff6ff;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 16px;
        margin-top: 20px;
        text-align: center;
    }
    </style>
    <?php
    
    return ob_get_clean();
}
