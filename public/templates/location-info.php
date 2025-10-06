<?php
/**
 * Location Info Template
 * Blue Motors Southampton Plugin
 * 
 * Template for displaying business location and contact information
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Default attributes
$default_atts = array(
    'style' => 'card',
    'show_map' => 'false',
    'show_contact' => 'true',
    'show_hours' => 'true',
    'show_directions' => 'true'
);

// Merge with passed attributes
if (isset($atts) && is_array($atts)) {
    $atts = array_merge($default_atts, $atts);
} else {
    $atts = $default_atts;
}
?>

<div class="bms-location-info" data-style="<?php echo esc_attr($atts['style']); ?>">
    <h3>📍 Blue Motors Southampton</h3>
    
    <div class="location-details">
        <div class="address-section">
            <h4>Our Location</h4>
            <p>
                <strong>Blue Motors Southampton</strong><br>
                Southampton, Hampshire<br>
                United Kingdom
            </p>
        </div>
        
        <?php if ($atts['show_contact'] === 'true'): ?>
        <div class="contact-section">
            <h4>Contact Information</h4>
            <p>
                📞 <strong>Phone:</strong> Available during business hours<br>
                📧 <strong>Email:</strong> info@bluemotors-southampton.co.uk<br>
                🌐 <strong>Website:</strong> bluemotors-southampton.co.uk
            </p>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_hours'] === 'true'): ?>
        <div class="hours-section">
            <h4>Opening Hours</h4>
            <ul class="opening-hours-list">
                <li><strong>Monday - Friday:</strong> 8:00 AM - 6:00 PM</li>
                <li><strong>Saturday:</strong> 8:00 AM - 4:00 PM</li>
                <li><strong>Sunday:</strong> Closed</li>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_directions'] === 'true'): ?>
        <div class="directions-section">
            <h4>Getting Here</h4>
            <p>We're conveniently located in Southampton. Contact us for specific directions to our garage.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_map'] === 'true'): ?>
        <div class="map-section">
            <h4>Find Us</h4>
            <div class="map-placeholder" style="background: #f0f0f0; padding: 40px; text-align: center; border-radius: 8px; color: #666;">
                <p>📍 Interactive map coming soon</p>
                <p><em>Contact us for directions</em></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.bms-location-info {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin: 15px 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.bms-location-info h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 1.4em;
}

.location-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.location-details h4 {
    margin: 0 0 10px 0;
    color: #007cba;
    font-size: 1.1em;
}

.location-details p,
.opening-hours-list {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.opening-hours-list {
    list-style: none;
    padding: 0;
}

.opening-hours-list li {
    margin: 5px 0;
    padding: 5px 0;
    border-bottom: 1px solid #f0f0f0;
}

.opening-hours-list li:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .location-details {
        grid-template-columns: 1fr;
    }
    
    .bms-location-info {
        padding: 15px;
    }
}
</style>