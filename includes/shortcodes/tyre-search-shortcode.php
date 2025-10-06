<?php
/**
 * Blue Motors Southampton - Tyre Search Shortcode
 * Phase 2: Tyre Services Implementation
 * 
 * Provides [bms_tyre_search] shortcode for embedding tyre search interface
 * 
 * @package BlueMotosSouthampton
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register tyre search shortcode
 */
add_shortcode('bms_tyre_search', 'bms_tyre_search_shortcode');

/**
 * Simple Tyre Finder Shortcode (for widgets and compact areas)
 * 
 * Usage: [bms_tyre_finder]
 * Usage: [bms_tyre_finder title="Find Tyres"]
 */
function bms_tyre_finder_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts([
        'title' => 'Find Your Tyres',
        'style' => 'compact', 
        'competitive_messaging' => 'false',
        'show_popular_sizes' => 'false',
        'default_search_method' => 'registration'
    ], $atts);
    
    ob_start();
    
    // Add wrapper with unique ID for compact finder
    $finder_id = 'bms-tyre-finder-' . uniqid();
    ?>
    
    <div id="<?php echo esc_attr($finder_id); ?>" class="bms-tyre-finder-compact">
        <?php if (!empty($atts['title'])): ?>
            <h4 class="finder-title"><?php echo esc_html($atts['title']); ?></h4>
        <?php endif; ?>
        
        <div class="compact-search-form">
            <div class="search-input-group">
                <div class="reg-input-container-compact">
                    <label for="<?php echo esc_attr($finder_id); ?>-reg" class="reg-input-label-compact">ENTER REG</label>
                    <input type="text" 
                           id="<?php echo esc_attr($finder_id); ?>-reg" 
                           class="tyre-reg-input" 
                           placeholder="e.g. AB12 CDE" 
                           maxlength="8">
                </div>
                <button type="button" class="find-tyres-btn" data-target="<?php echo esc_attr($finder_id); ?>">
                    🔍 Find Tyres
                </button>
            </div>
            <div class="search-results" style="display: none;"></div>
        </div>
        
        <div class="alternative-search">
            <p><small>Or <a href="#" class="show-size-search">search by tyre size</a></small></p>
            <div class="size-search-form" style="display: none;">
                <select class="tyre-width">
                    <option value="">Width</option>
                    <option value="165">165</option>
                    <option value="175">175</option>
                    <option value="185">185</option>
                    <option value="195">195</option>
                    <option value="205">205</option>
                    <option value="215">215</option>
                    <option value="225">225</option>
                    <option value="235">235</option>
                    <option value="245">245</option>
                </select>
                <select class="tyre-profile">
                    <option value="">Profile</option>
                    <option value="40">40</option>
                    <option value="45">45</option>
                    <option value="50">50</option>
                    <option value="55">55</option>
                    <option value="60">60</option>
                    <option value="65">65</option>
                    <option value="70">70</option>
                </select>
                <select class="tyre-rim">
                    <option value="">Rim</option>
                    <option value="13">13</option>
                    <option value="14">14</option>
                    <option value="15">15</option>
                    <option value="16">16</option>
                    <option value="17">17</option>
                    <option value="18">18</option>
                    <option value="19">19</option>
                    <option value="20">20</option>
                </select>
                <button type="button" class="search-by-size-btn">Search</button>
            </div>
        </div>
    </div>
    
    <style>
    .bms-tyre-finder-compact {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin: 10px 0;
    }
    .finder-title {
        margin: 0 0 15px 0;
        color: #333;
        font-size: 18px;
    }
    .search-input-group {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        align-items: flex-end;
    }
    .reg-input-container-compact {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .reg-input-label-compact {
        font-size: 12px;
        font-weight: 700;
        color: #1e40af;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 3px;
    }
    .tyre-reg-input {
        flex: 1;
        padding: 16px 12px !important;
        background-color: #f5cc11 !important;
        border: 2px solid #e6b800 !important;
        border-radius: 4px;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        color: #333 !important;
        min-height: 48px;
        box-sizing: border-box;
        transition: all 0.3s ease;
        text-align: center;
        width: 100%;
    }
    .tyre-reg-input:focus {
        outline: none !important;
        border-color: #007cba !important;
        box-shadow: 0 0 0 3px rgba(245, 204, 17, 0.3) !important;
        background-color: #f7d117 !important;
    }
    .tyre-reg-input::placeholder {
        color: #666 !important;
        font-weight: normal;
        font-style: italic;
    }
    .find-tyres-btn {
        background: #007cba;
        color: white;
        border: none;
        padding: 16px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        white-space: nowrap;
        min-height: 48px;
        box-sizing: border-box;
        transition: background-color 0.3s ease;
        align-self: flex-end;
    }
    .find-tyres-btn:hover {
        background: #005a87;
    }
    .alternative-search {
        text-align: center;
        margin-top: 10px;
    }
    .size-search-form {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 80px;
        gap: 5px;
        margin-top: 10px;
    }
    .size-search-form select {
        padding: 6px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 13px;
    }
    .search-by-size-btn {
        background: #28a745;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
    }
    .show-size-search {
        color: #007cba;
        text-decoration: none;
    }
    .search-results {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        margin-top: 10px;
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const finder = document.getElementById('<?php echo $finder_id; ?>');
        if (!finder) return;
        
        // Show/hide size search
        const showSizeSearch = finder.querySelector('.show-size-search');
        const sizeForm = finder.querySelector('.size-search-form');
        
        if (showSizeSearch && sizeForm) {
            showSizeSearch.addEventListener('click', function(e) {
                e.preventDefault();
                sizeForm.style.display = sizeForm.style.display === 'none' ? 'grid' : 'none';
                this.textContent = sizeForm.style.display === 'none' ? 'search by tyre size' : 'hide size search';
            });
        }
        
        // Registration lookup
        const regInput = finder.querySelector('.tyre-reg-input');
        const findBtn = finder.querySelector('.find-tyres-btn');
        const results = finder.querySelector('.search-results');
        
        if (findBtn && regInput && results) {
            findBtn.addEventListener('click', function() {
                const reg = regInput.value.trim().toUpperCase();
                if (reg.length < 3) {
                    alert('Please enter a valid registration number');
                    return;
                }
                
                this.innerHTML = '⏳ Searching...';
                this.disabled = true;
                
                // Simulate search (replace with actual AJAX call)
                setTimeout(() => {
                    results.innerHTML = `
                        <div class="search-result">
                            <p><strong>Registration:</strong> ${reg}</p>
                            <p>Vehicle details found! <a href="#" onclick="alert('This would redirect to full tyre search')">View available tyres →</a></p>
                        </div>
                    `;
                    results.style.display = 'block';
                    
                    this.innerHTML = '🔍 Find Tyres';
                    this.disabled = false;
                }, 1500);
            });
            
            // Enter key support
            regInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    findBtn.click();
                }
            });
        }
    });
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * Register the tyre finder shortcode
 */
add_shortcode('bms_tyre_finder', 'bms_tyre_finder_shortcode');

/**
 * Add tyre search initialization script
 */
function bms_add_tyre_search_init_script($wrapper_id, $atts) {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Initialize tyre search for this specific instance
        if (typeof BlueMotosTyreBooking !== 'undefined') {
            var tyreSearch = new BlueMotosTyreBooking();
            tyreSearch.container = '#<?php echo esc_js($wrapper_id); ?>';
            tyreSearch.settings = <?php echo json_encode($atts); ?>;
            
            // Set default search method
            if ('<?php echo esc_js($atts['default_search_method']); ?>' !== 'registration') {
                tyreSearch.switchSearchMethod('<?php echo esc_js($atts['default_search_method']); ?>');
            }
            
            console.log('🛞 Tyre search initialized for container: <?php echo esc_js($wrapper_id); ?>');
        } else {
            console.error('BlueMotosTyreBooking class not found. Please check if tyre-booking.js is loaded.');
        }
    });
    </script>
    <?php
}

/**
 * Tyre search shortcode handler
 * 
 * Usage: [bms_tyre_search]
 * Usage: [bms_tyre_search style="compact"]
 * Usage: [bms_tyre_search competitive_messaging="false"]
 */
function bms_tyre_search_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts([
        'style' => 'full', // full, compact
        'competitive_messaging' => 'false',
        'show_popular_sizes' => 'true',
        'default_search_method' => 'registration' // registration, size, popular
    ], $atts);
    
    // Enqueue required assets
    bms_enqueue_tyre_search_assets();
    
    // Start output buffering
    ob_start();
    
    // Add wrapper with unique ID
    $wrapper_id = 'bms-tyre-search-' . uniqid();
    echo '<div id="' . esc_attr($wrapper_id) . '" class="bms-tyre-search-wrapper">';
    
    // Add professional messaging if enabled
    if ($atts['competitive_messaging'] === 'true') {
        bms_render_competitive_header();
    }
    
    // Include the professional tyre search template
    $template_path = BMS_PLUGIN_DIR . 'public/templates/tyre-search.php';
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        // Fallback to F1 style template
        $fallback_template = BMS_PLUGIN_DIR . 'public/templates/tyre-search-f1-style.php';
        if (file_exists($fallback_template)) {
            include $fallback_template;
        } else {
            echo '<div class="bms-notice">Tyre search template not found. Please check plugin installation.</div>';
        }
    }
    
    echo '</div>';
    
    // Add initialization script
    bms_add_tyre_search_init_script($wrapper_id, $atts);
    
    return ob_get_clean();
}

/**
 * Enqueue tyre search assets
 */
function bms_enqueue_tyre_search_assets() {
    // Enqueue professional CSS
    wp_enqueue_style(
        'bms-tyre-search-f1',
        BMS_PLUGIN_URL . 'assets/css/tyre-search-professional.css',
        ['bms-public'],
        BMS_VERSION
    );
    
    // Enqueue mobile date/time picker CSS
    wp_enqueue_style(
        'bms-mobile-date-time-picker',
        BMS_PLUGIN_URL . 'assets/css/mobile-date-time-picker.css',
        ['bms-tyre-search-f1'],
        BMS_VERSION
    );
    
    // Enqueue JavaScript
    wp_enqueue_script(
        'bms-tyre-booking',
        BMS_PLUGIN_URL . 'assets/js/tyre-booking.js',
        ['jquery', 'bms-vehicle-lookup'],
        BMS_VERSION,
        true
    );
    
    // Enqueue mobile date/time picker JS (Fixed Version)
    wp_enqueue_script(
        'bms-mobile-date-time-picker-fixed',
        BMS_PLUGIN_URL . 'assets/js/mobile-date-time-picker-fixed.js',
        ['bms-tyre-booking'],
        BMS_VERSION,
        true
    );
    
    // Localize script with AJAX data
    wp_localize_script(
        'bms-tyre-booking',
        'bmsTyreBooking',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_vehicle_lookup'),
            'strings' => [
                'searchFailed' => __('Tyre search failed. Please try again.', 'blue-motors-southampton'),
                'invalidRegistration' => __('Please enter a valid UK vehicle registration.', 'blue-motors-southampton'),
                'noTyresFound' => __('No tyres found for your search criteria.', 'blue-motors-southampton'),
                'selectTyre' => __('Please select a tyre to continue.', 'blue-motors-southampton'),
                'bookingFailed' => __('Booking creation failed. Please try again.', 'blue-motors-southampton'),
                'loading' => __('Loading...', 'blue-motors-southampton'),
                'selectDateFirst' => __('Please select a date first.', 'blue-motors-southampton'),
                'noSlotsAvailable' => __('No appointment slots available for this date.', 'blue-motors-southampton')
            ],
            'pricing' => [
                'vatRate' => 0.2,
                'currency' => 'GBP',
                'currencySymbol' => '£'
            ],
            'mobile' => [
                'enabled' => true,
                'popupCalendar' => true,
                'touchOptimized' => true
            ]
        ]
    );
}

/**
 * Render service header
 */
function bms_render_competitive_header() {
    ?>
    <div class="bms-competitive-advantage-header">
        <h3>🛞 Professional Tyre Services</h3>
        <div class="competitive-highlights">
            <div class="highlight-item">
                <span class="highlight-icon">✅</span>
                <span class="highlight-text">Order online - quick and convenient</span>
            </div>
            <div class="highlight-item">
                <span class="highlight-icon">🎯</span>
                <span class="highlight-text">Instant vehicle matching</span>
            </div>
            <div class="highlight-item">
                <span class="highlight-icon">💷</span>
                <span class="highlight-text">Transparent pricing with VAT</span>
            </div>
            <div class="highlight-item">
                <span class="highlight-icon">⚡</span>
                <span class="highlight-text">Same-day fitting available</span>
            </div>
        </div>
        <p class="competitive-message">
            <strong>🏆 Professional Service:</strong> Complete your tyre order in minutes online with our convenient booking system.
        </p>
    </div>
    <?php
}/**
 * Register tyre search widget (for use in sidebars)
 */
add_action('widgets_init', 'bms_register_tyre_search_widget');

function bms_register_tyre_search_widget() {
    register_widget('BMS_Tyre_Search_Widget');
}

/**
 * Tyre Search Widget Class
 */
class BMS_Tyre_Search_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'bms_tyre_search_widget',
            __('Blue Motors - Tyre Search', 'blue-motors-southampton'),
            [
                'description' => __('Add tyre search functionality to any widget area.', 'blue-motors-southampton')
            ]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        // Render compact tyre search
        echo do_shortcode('[bms_tyre_search style="compact" competitive_messaging="false"]');
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Find Your Tyres', 'blue-motors-southampton');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:', 'blue-motors-southampton'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <small>This widget displays a compact tyre search form. 
            Perfect for sidebars and footer areas.</small>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

/**
 * Add tyre search to service booking form
 */
add_action('bms_after_service_selection', 'bms_add_tyre_option_to_services');

function bms_add_tyre_option_to_services() {
    ?>
    <div class="service-card tyre-service" data-service="tyre_fitting">
        <div class="service-icon">🛞</div>
        <h4>Tyre Fitting</h4>
        <p class="service-price">From £25.00 per tyre</p>
        <p class="service-description">
            Professional tyre fitting with balancing, wheel alignment check, and disposal of old tyres.
        </p>
        <div class="service-duration">Duration: 30 minutes per tyre</div>
        <div class="service-features">
            <span class="feature">✓ All major brands available</span>
            <span class="feature">✓ Online ordering available</span>
            <span class="feature">✓ Instant vehicle matching</span>
            <span class="feature">✓ Same-day fitting available</span>
        </div>
        <button type="button" class="btn-select-service" data-service="tyre_fitting">
            Find Your Tyres Online
        </button>
    </div>
    <?php
}
