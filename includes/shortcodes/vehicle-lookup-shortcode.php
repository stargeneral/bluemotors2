<?php
/**
 * Vehicle Lookup Shortcode for Blue Motors Southampton
 * Enhanced DVLA and DVSA API integration shortcode
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Vehicle Lookup Shortcode Class
 */
class BMS_Vehicle_Lookup_Shortcode {
    
    /**
     * Initialize shortcode
     */
    public static function init() {
        add_shortcode('bms_vehicle_lookup', [__CLASS__, 'render_vehicle_lookup']);
        add_shortcode('vehicle_lookup', [__CLASS__, 'render_vehicle_lookup']); // Alias
        
        // Admin shortcode for backend use
        add_shortcode('bms_vehicle_lookup_admin', [__CLASS__, 'render_admin_vehicle_lookup']);
    }
    
    /**
     * Render vehicle lookup form
     * 
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     * @return string HTML output
     */
    public static function render_vehicle_lookup($atts = [], $content = '') {
        // Parse attributes with defaults
        $atts = shortcode_atts([
            'title' => 'Vehicle Lookup',
            'description' => 'Enter your vehicle registration to get detailed information including MOT history and service recommendations.',
            'show_mot_history' => 'true',
            'show_recommendations' => 'true',
            'theme' => 'default',
            'size' => 'normal',
            'auto_lookup' => 'true',
            'placeholder' => 'e.g. AB12 CDE',
            'button_text' => 'Look Up Vehicle',
            'integration_mode' => 'standalone' // standalone, booking, admin
        ], $atts);
        
        // Convert string booleans to actual booleans
        $show_mot_history = filter_var($atts['show_mot_history'], FILTER_VALIDATE_BOOLEAN);
        $show_recommendations = filter_var($atts['show_recommendations'], FILTER_VALIDATE_BOOLEAN);
        $auto_lookup = filter_var($atts['auto_lookup'], FILTER_VALIDATE_BOOLEAN);
        
        // Unique ID for multiple instances on same page
        $instance_id = 'bms-vehicle-lookup-' . uniqid();
        
        // Start output buffering
        ob_start();
        
        // Ensure scripts and styles are enqueued
        self::enqueue_assets();
        
        ?>
        <div class="bms-vehicle-lookup-container <?php echo esc_attr($atts['theme']); ?> <?php echo esc_attr($atts['size']); ?>" 
             id="<?php echo esc_attr($instance_id); ?>" 
             data-auto-lookup="<?php echo $auto_lookup ? 'true' : 'false'; ?>"
             data-integration-mode="<?php echo esc_attr($atts['integration_mode']); ?>">
             
            <?php if (!empty($atts['title'])): ?>
                <div class="bms-lookup-header">
                    <h3 class="bms-lookup-title"><?php echo esc_html($atts['title']); ?></h3>
                    <?php if (!empty($atts['description'])): ?>
                        <p class="bms-lookup-description"><?php echo esc_html($atts['description']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="bms-vehicle-lookup-form">
                <!-- Registration Input Section -->
                <div class="bms-registration-section">
                    <label for="<?php echo esc_attr($instance_id); ?>-reg" class="bms-registration-label">
                        Vehicle Registration
                    </label>
                    <div class="bms-registration-wrapper">
                        <input type="text" 
                               id="<?php echo esc_attr($instance_id); ?>-reg"
                               class="bms-registration-input" 
                               placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                               maxlength="8"
                               autocomplete="off"
                               spellcheck="false"
                               data-instance="<?php echo esc_attr($instance_id); ?>">
                        
                        <button type="button" 
                                class="bms-lookup-button" 
                                title="<?php echo esc_attr($atts['button_text']); ?>"
                                data-instance="<?php echo esc_attr($instance_id); ?>">
                            <span class="dashicons dashicons-search"></span>
                            <span class="bms-button-text"><?php echo esc_html($atts['button_text']); ?></span>
                        </button>
                        
                        <div class="bms-lookup-status" data-instance="<?php echo esc_attr($instance_id); ?>"></div>
                    </div>
                    
                    <!-- Error message container -->
                    <div class="bms-message-container" style="display: none;"></div>
                </div>
                
                <!-- Vehicle Display Section -->
                <div class="bms-vehicle-display" 
                     id="<?php echo esc_attr($instance_id); ?>-display" 
                     style="display: none;">
                     
                    <div class="bms-vehicle-header">
                        <h4>Vehicle Details</h4>
                        <button type="button" 
                                class="bms-clear-vehicle" 
                                title="Clear vehicle data"
                                data-instance="<?php echo esc_attr($instance_id); ?>">×</button>
                    </div>
                    
                    <!-- Basic Vehicle Information -->
                    <div class="bms-vehicle-info">
                        <div class="bms-vehicle-basic">
                            <span class="bms-vehicle-make"></span>
                            <span class="bms-vehicle-model"></span>
                            (<span class="bms-vehicle-year"></span>)
                        </div>
                        <div class="bms-vehicle-details">
                            <span class="bms-vehicle-colour"></span> •
                            <span class="bms-vehicle-fuel"></span> •
                            <span class="bms-vehicle-engine"></span>
                        </div>
                        
                        <!-- MOT Information -->
                        <div class="bms-vehicle-mot">
                            <div class="bms-mot-status-wrapper">
                                <span class="bms-mot-label">MOT Status:</span>
                                <span class="bms-mot-status"></span>
                            </div>
                            <div class="bms-mot-expiry-wrapper" style="display: none;">
                                <span class="bms-mot-expiry-label">Expires:</span>
                                <span class="bms-mot-expiry"></span>
                            </div>
                        </div>
                        
                        <!-- Vehicle Condition Indicators -->
                        <div class="bms-vehicle-indicators">
                            <div class="bms-maintenance-indicator">
                                <span class="bms-indicator-label">Maintenance Score:</span>
                                <span class="bms-maintenance-score"></span>
                            </div>
                            <div class="bms-risk-indicator">
                                <span class="bms-indicator-label">Risk Level:</span>
                                <span class="bms-risk-level"></span>
                            </div>
                            <div class="bms-condition-indicator">
                                <span class="bms-indicator-label">Overall Condition:</span>
                                <span class="bms-condition-score"></span>
                            </div>
                        </div>
                        
                        <!-- Advisory Notices Summary -->
                        <div class="bms-advisories-summary" style="display: none;">
                            <div class="bms-advisories-indicator">
                                <span class="bms-indicator-label">Recent Advisories:</span>
                                <span class="bms-advisory-count">0</span>
                            </div>
                            <div class="bms-recent-advisories"></div>
                        </div>
                        
                        <!-- Mileage Information -->
                        <div class="bms-mileage-info" style="display: none;">
                            <div class="bms-latest-mileage">
                                <span class="bms-indicator-label">Latest Mileage:</span>
                                <span class="bms-mileage-value"></span>
                            </div>
                            <div class="bms-annual-mileage">
                                <span class="bms-indicator-label">Estimated Annual:</span>
                                <span class="bms-annual-mileage-value"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="bms-vehicle-actions">
                        <?php if ($show_mot_history): ?>
                            <button type="button" 
                                    class="bms-mot-history-toggle"
                                    data-instance="<?php echo esc_attr($instance_id); ?>">
                                Show MOT History
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($atts['integration_mode'] === 'booking'): ?>
                            <button type="button" 
                                    class="bms-use-for-booking button button-primary"
                                    data-instance="<?php echo esc_attr($instance_id); ?>">
                                Use This Vehicle for Booking
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Mock Data Notice -->
                    <div class="bms-mock-data-notice" style="display: none;">
                        <small>⚠ Using demo data - configure API keys in admin for live vehicle information</small>
                    </div>
                </div>
                
                <!-- MOT History Section -->
                <?php if ($show_mot_history): ?>
                    <div class="bms-mot-history-details" 
                         id="<?php echo esc_attr($instance_id); ?>-mot-history" 
                         style="display: none;">
                        <!-- MOT history will be populated by JavaScript -->
                    </div>
                <?php endif; ?>
                
                <!-- Service Recommendations Section -->
                <?php if ($show_recommendations): ?>
                    <div class="bms-service-recommendations" 
                         id="<?php echo esc_attr($instance_id); ?>-recommendations" 
                         style="display: none;">
                        <!-- Service recommendations will be populated by JavaScript -->
                    </div>
                <?php endif; ?>
                
                <!-- Hidden Fields for Integration -->
                <?php if ($atts['integration_mode'] === 'booking'): ?>
                    <div class="bms-hidden-fields" style="display: none;">
                        <input type="hidden" name="vehicle_registration" class="bms-hidden-registration">
                        <input type="hidden" name="vehicle_make" class="bms-hidden-make">
                        <input type="hidden" name="vehicle_model" class="bms-hidden-model">
                        <input type="hidden" name="vehicle_year" class="bms-hidden-year">
                        <input type="hidden" name="vehicle_fuel_type" class="bms-hidden-fuel">
                        <input type="hidden" name="vehicle_engine_capacity" class="bms-hidden-engine">
                        <input type="hidden" name="vehicle_category" class="bms-hidden-category">
                        <input type="hidden" name="pricing_category" class="bms-hidden-pricing">
                        <input type="hidden" name="vehicle_data_json" class="bms-hidden-json">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php
        // Add custom content if provided
        if (!empty($content)) {
            echo '<div class="bms-shortcode-content">' . do_shortcode($content) . '</div>';
        }
        ?>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Initialize vehicle lookup for this instance
            if (typeof window.BMS !== 'undefined' && window.BMS.VehicleLookup) {
                console.log('[BMS] Initializing vehicle lookup for instance: <?php echo $instance_id; ?>');
                
                // Set instance-specific configuration
                window.BMS.VehicleLookup.config.instanceId = '<?php echo $instance_id; ?>';
                window.BMS.VehicleLookup.config.showMotHistory = <?php echo $show_mot_history ? 'true' : 'false'; ?>;
                window.BMS.VehicleLookup.config.showRecommendations = <?php echo $show_recommendations ? 'true' : 'false'; ?>;
                window.BMS.VehicleLookup.config.autoLookup = <?php echo $auto_lookup ? 'true' : 'false'; ?>;
                window.BMS.VehicleLookup.config.integrationMode = '<?php echo esc_js($atts['integration_mode']); ?>';
            }
        });
        </script>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render admin vehicle lookup (simplified version for backend)
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public static function render_admin_vehicle_lookup($atts = []) {
        // Only show in admin
        if (!is_admin()) {
            return '<p>This shortcode is only available in the WordPress admin area.</p>';
        }
        
        $atts = shortcode_atts([
            'title' => 'Quick Vehicle Lookup',
            'size' => 'compact',
            'theme' => 'admin'], $atts);
        
        return self::render_vehicle_lookup($atts);
    }
    
    /**
     * Ensure required assets are enqueued
     */
    private static function enqueue_assets() {
        // Enqueue vehicle lookup styles if not already enqueued
        if (!wp_style_is('bms-vehicle-lookup', 'enqueued')) {
            wp_enqueue_style(
                'bms-vehicle-lookup',
                BMS_PLUGIN_URL . 'assets/css/vehicle-lookup.css',
                array(),
                BMS_VERSION
            );
        }
        
        // Enqueue vehicle lookup JavaScript if not already enqueued
        if (!wp_script_is('bms-vehicle-lookup', 'enqueued')) {
            wp_enqueue_script(
                'bms-vehicle-lookup',
                BMS_PLUGIN_URL . 'assets/js/vehicle-lookup.js',
                array('jquery'),
                BMS_VERSION,
                true
            );
            
            // Localize script if not already done
            if (!wp_script_is('bms-vehicle-lookup', 'localized')) {
                wp_localize_script(
                    'bms-vehicle-lookup',
                    'bmsVehicleLookup',
                    array(
                        'ajaxUrl' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('bms_vehicle_lookup'),
                        'adminNonce' => wp_create_nonce('bms_admin_actions'),
                        'strings' => array(
                            'lookupFailed' => __('Vehicle lookup failed. Please try again.', 'blue-motors-southampton'),
                            'invalidRegistration' => __('Please enter a valid UK vehicle registration.', 'blue-motors-southampton'),
                            'networkError' => __('Network error. Please check your connection and try again.', 'blue-motors-southampton'),
                            'loading' => __('Looking up vehicle...', 'blue-motors-southampton'),
                            'motHistoryUnavailable' => __('MOT history is currently unavailable.', 'blue-motors-southampton'),
                            'clearConfirm' => __('Are you sure you want to clear the vehicle data?', 'blue-motors-southampton'),
                            'vehicleFound' => __('Vehicle found successfully!', 'blue-motors-southampton'),
                            'motHistoryLoaded' => __('MOT history loaded successfully.', 'blue-motors-southampton')
                        )
                    )
                );
            }
        }
    }
    
    /**
     * Register shortcode help/documentation
     */
    public static function get_shortcode_help() {
        return [
            'bms_vehicle_lookup' => [
                'description' => 'Display enhanced vehicle lookup form with DVLA and DVSA integration',
                'attributes' => [
                    'title' => 'Form title (default: "Vehicle Lookup")',
                    'description' => 'Form description text',
                    'show_mot_history' => 'Show MOT history toggle (true/false, default: true)',
                    'show_recommendations' => 'Show service recommendations (true/false, default: true)',
                    'theme' => 'Visual theme (default, compact, minimal)',
                    'size' => 'Form size (normal, large, compact)',
                    'auto_lookup' => 'Auto-lookup on typing (true/false, default: true)',
                    'placeholder' => 'Input placeholder text (default: "e.g. AB12 CDE")',
                    'button_text' => 'Lookup button text (default: "Look Up Vehicle")',
                    'integration_mode' => 'Integration mode (standalone, booking, admin)'
                ],
                'examples' => [
                    '[bms_vehicle_lookup]' => 'Basic vehicle lookup form',
                    '[bms_vehicle_lookup title="Check Your Vehicle" theme="compact"]' => 'Compact form with custom title',
                    '[bms_vehicle_lookup integration_mode="booking" show_recommendations="true"]' => 'Booking integration with recommendations',
                    '[bms_vehicle_lookup show_mot_history="false" size="large"]' => 'Large form without MOT history'
                ]
            ],
            'vehicle_lookup' => [
                'description' => 'Alias for bms_vehicle_lookup (same functionality)',
                'note' => 'Use bms_vehicle_lookup for consistency'
            ],
            'bms_vehicle_lookup_admin' => [
                'description' => 'Admin-only vehicle lookup (for backend use)',
                'note' => 'Only works in WordPress admin area'
            ]
        ];
    }
}

// Initialize the shortcode
BMS_Vehicle_Lookup_Shortcode::init();

/**
 * Helper function to display shortcode help in admin
 */
function bms_display_shortcode_help() {
    if (!is_admin()) {
        return;
    }
    
    $help = BMS_Vehicle_Lookup_Shortcode::get_shortcode_help();
    
    echo '<div class="bms-shortcode-help">';
    echo '<h3>Available Vehicle Lookup Shortcodes</h3>';
    
    foreach ($help as $shortcode => $info) {
        echo '<div class="bms-shortcode-item">';
        echo '<h4>[' . esc_html($shortcode) . ']</h4>';
        echo '<p>' . esc_html($info['description']) . '</p>';
        
        if (isset($info['attributes'])) {
            echo '<h5>Attributes:</h5>';
            echo '<ul>';
            foreach ($info['attributes'] as $attr => $desc) {
                echo '<li><strong>' . esc_html($attr) . ':</strong> ' . esc_html($desc) . '</li>';
            }
            echo '</ul>';
        }
        
        if (isset($info['examples'])) {
            echo '<h5>Examples:</h5>';
            echo '<ul>';
            foreach ($info['examples'] as $example => $desc) {
                echo '<li><code>' . esc_html($example) . '</code> - ' . esc_html($desc) . '</li>';
            }
            echo '</ul>';
        }
        
        if (isset($info['note'])) {
            echo '<p><em>' . esc_html($info['note']) . '</em></p>';
        }
        
        echo '</div>';
    }
    
    echo '</div>';
}
