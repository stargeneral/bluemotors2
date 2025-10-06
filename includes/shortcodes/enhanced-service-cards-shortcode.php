<?php
/**
 * Enhanced Service Cards Shortcode
 * Blue Motors Southampton - Professional Auto Services
 * 
 * Provides shortcodes for displaying enhanced service cards with professional messaging
 * 
 * @package BlueMotosSouthampton
 * @since 3.0.0 (Phase 3)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Service Cards Shortcode
 * 
 * Usage: [bms_enhanced_services category="all" show_comparison="true"]
 */
function bms_enhanced_service_cards_shortcode($atts, $content = null) {
    
    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'category' => 'all',                    // Filter by category: all, climate, safety, electrical, etc.
        'show_comparison' => 'true',            // Show F1 comparison table
        'show_competitive' => 'true',           // Show professional messaging
        'layout' => 'grid',                     // Layout: grid, list, compact
        'services_per_row' => '3',              // Number of services per row
        'show_categories' => 'true',            // Show category tabs
        'highlight_advantages' => 'true',       // Highlight professional features
        'mobile_optimized' => 'true'            // Enable mobile optimizations
    ), $atts, 'bms_enhanced_services');
    
    // Ensure we have access to the enhanced service cards template function
    if (!function_exists('bms_display_enhanced_service_cards')) {
        require_once BMS_PLUGIN_DIR . 'templates/enhanced-service-cards.php';
    }
    
    // Ensure ServiceManagerEnhanced class is available
    if (!class_exists('BlueMotosSouthampton\Services\ServiceManagerEnhanced')) {
        if (file_exists(BMS_PLUGIN_DIR . 'includes/services/class-service-manager-enhanced.php')) {
            require_once BMS_PLUGIN_DIR . 'includes/services/class-service-manager-enhanced.php';
        } else {
            return '<p class="bms-error">Enhanced Service Manager not available. Please check plugin installation.</p>';
        }
    }
    
    // Get services and categories with error handling
    try {
        $simple_categories = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_simple_categories();
        $services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true);
        
        // Convert simple categories to full category objects
        $categories = array();
        if (is_array($simple_categories)) {
            foreach ($simple_categories as $cat_id => $cat_name) {
                $categories[$cat_id] = array(
                    'name' => $cat_name,
                    'icon' => bms_get_category_icon($cat_id),
                    'competitive_note' => bms_get_category_competitive_note($cat_id)
                );
            }
        }
        
        // Ensure we got arrays back
        if (!is_array($services)) {
            $services = array();
        }
    } catch (Exception $e) {
        return '<p class="bms-error">Error loading services: ' . esc_html($e->getMessage()) . '</p>';
    }
    
    // Filter services by category if specified
    if ($atts['category'] !== 'all' && !empty($services)) {
        $services = array_filter($services, function($service) use ($atts) {
            return is_array($service) && isset($service['category']) && $service['category'] === $atts['category'];
        });
    }
    
    ob_start();
    ?>
    
    <div class="bms-enhanced-services-container" 
         data-layout="<?php echo esc_attr($atts['layout']); ?>"
         data-per-row="<?php echo esc_attr($atts['services_per_row']); ?>"
         data-mobile="<?php echo esc_attr($atts['mobile_optimized']); ?>">
        
        <?php if ($atts['show_competitive'] === 'true'): ?>
        <!-- Professional Header -->
        <div class="competitive-header">
            <div class="advantage-message">
                <h3>🎯 Why Choose Blue Motors Over industry leaders?</h3>
                <div class="advantages-grid">
                    <div class="advantage-item">
                        <span class="advantage-icon">🛞</span>
                        <span class="advantage-text">Order tyres online - no phone calls needed!</span>
                    </div>
                    <div class="advantage-item">
                        <span class="advantage-icon">💳</span>
                        <span class="advantage-text">Smooth payment process - no issues</span>
                    </div>
                    <div class="advantage-item">
                        <span class="advantage-icon">📅</span>
                        <span class="advantage-text">UK date format - DD/MM/YYYY always</span>
                    </div>
                    <div class="advantage-item">
                        <span class="advantage-icon">📱</span>
                        <span class="advantage-text">Superior mobile experience</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_categories'] === 'true' && count($categories) > 1): ?>
        <!-- Category Tabs -->
        <div class="service-category-tabs">
            <button class="service-category-tab active" data-category="all">
                <span class="tab-icon">🔧</span>
                <span class="tab-label">All Services</span>
            </button>
            <?php foreach ($categories as $category_id => $category): ?>
            <button class="service-category-tab" data-category="<?php echo esc_attr($category_id); ?>">
                <span class="tab-icon"><?php echo isset($category['icon']) ? $category['icon'] : '🔧'; ?></span>
                <span class="tab-label"><?php echo esc_html($category['name']); ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Service Cards -->
        <div class="service-cards layout-<?php echo esc_attr($atts['layout']); ?>">
            <?php foreach ($categories as $category_id => $category): ?>
                
                <!-- Category Section -->;
                <div class="service-category-section" data-category="<?php echo esc_attr($category_id); ?>">
                    
                    <?php if ($atts['category'] === 'all'): ?>
                    <!-- Category Header -->
                    <div class="category-header">
                        <h3>
                            <span class="category-icon"><?php echo isset($category['icon']) ? $category['icon'] : '🔧'; ?></span>
                            <?php echo esc_html($category['name']); ?>
                        </h3>
                        
                        <?php if (isset($category['competitive_note']) && $atts['highlight_advantages'] === 'true'): ?>
                        <div class="category-competitive-note">
                            <strong>🎯 Our Advantage:</strong> <?php echo esc_html($category['competitive_note']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Services in Category -->
                    <div class="category-services">
                        <?php 
                        $category_services = array_filter($services, function($service) use ($category_id) {
                            return (isset($service['category']) && $service['category'] === $category_id);
                        });
                        
                        foreach ($category_services as $service_id => $service): ?>
                        
                        <!-- Service Card -->;
                        <div class="service-card <?php echo esc_attr($category_id); ?>-service" 
                             data-service="<?php echo esc_attr($service_id); ?>"
                             data-category="<?php echo esc_attr($category_id); ?>">
                            
                            <!-- Service Icon -->
                            <div class="service-icon">
                                <?php if (isset($service['icon'])): ?>
                                    <i class="<?php echo esc_attr($service['icon']); ?>"></i>
                                <?php else: ?>
                                    🔧
                                <?php endif; ?>
                            </div>
                            
                            <!-- Service Details -->
                            <h4><?php echo esc_html(isset($service['name']) ? $service['name'] : 'Unknown Service'); ?></h4>
                            <p class="service-price">From £<?php echo number_format(isset($service['base_price']) ? $service['base_price'] : 0, 2); ?></p>
                            <p class="service-desc"><?php echo esc_html(isset($service['description']) ? $service['description'] : ''); ?></p>
                            
                            <?php if (isset($service['duration'])): ?>
                            <p class="service-duration">Duration: <?php echo esc_html($service['duration']); ?> minutes</p>
                            <?php endif; ?>
                            
                            <!-- Service Features -->
                            <?php if (isset($service['includes']) && is_array($service['includes'])): ?>
                            <div class="service-features">
                                <?php foreach (array_slice($service['includes'], 0, 3) as $feature): ?>
                                <span class="feature">✓ <?php echo esc_html($feature); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Professional Advantages -->
                            <?php if (isset($service['competitive_note']) && $atts['highlight_advantages'] === 'true'): ?>
                            <div class="competitive-note">
                                <span class="competitive-icon">🎯</span>
                                <strong>vs F1:</strong> <?php echo esc_html($service['competitive_note']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($service['f1_equivalent']) && $service['f1_equivalent'] && $atts['highlight_advantages'] === 'true'): ?>
                            <div class="f1-equivalent-note">
                                <span class="f1-icon">✅</span>
                                F1 offers this - we do it better!
                            </div>
                            <?php endif; ?>
                            
                            <!-- Special Tyre Advantage -->
                            <?php if ($service_id === 'tyre_fitting' && $atts['highlight_advantages'] === 'true'): ?>
                            <div class="tyre-advantage-note">
                                <strong>🚀 Major Advantage:</strong> Complete online tyre ordering - Some providers require phone calls!
                            </div>;
                            <?php endif; ?>
                            
                            <!-- Book Service Button -->
                            <button type="button" class="btn-select-service" data-service="<?php echo esc_attr($service_id); ?>">
                                <?php if ($service_id === 'tyre_fitting'): ?>
                                    Find Your Tyres Online
                                <?php else: ?>;
                                    Book <?php echo esc_html($service['name']); ?>
                                <?php endif; ?>
                            </button>
                            
                        </div>
                        
                        <?php endforeach; ?>
                    </div>
                    
                </div>
                
            <?php endforeach; ?>
        </div>
        
        <?php if ($atts['show_comparison'] === 'true'): ?>
        <!-- Comparison Table -->
        <div class="service-comparison-table">
            <div class="comparison-container">
                <h4>🏆 Blue Motors vs industry leaders</h4>
                <div class="comparison-grid">
                    <div class="comparison-header">
                        <div class="feature-col">Feature</div>
                        <div class="us-col">Blue Motors Southampton</div>
                        <div class="them-col">industry leaders</div>
                    </div>
                    
                    <div class="comparison-row">
                        <div class="feature">Online Tyre Orders</div>
                        <div class="us advantage">✅ Complete online ordering</div>
                        <div class="them limitation">❌ Must call for tyres</div>
                    </div>
                    
                    <div class="comparison-row">
                        <div class="feature">Payment Process</div>
                        <div class="us advantage">✅ Multiple secure options</div>
                        <div class="them limitation">⚠️ PayPal integration issues</div>
                    </div>
                    
                    <div class="comparison-row">
                        <div class="feature">Date Format</div>
                        <div class="us advantage">✅ UK format (DD/MM/YYYY)</div>
                        <div class="them limitation">❌ American format confusion</div>
                    </div>
                    
                    <div class="comparison-row">
                        <div class="feature">Mobile Experience</div>
                        <div class="us advantage">✅ Touch-optimized interface</div>
                        <div class="them limitation">⚠️ Basic mobile design</div>
                    </div>
                    
                    <div class="comparison-row">
                        <div class="feature">Local Focus</div>
                        <div class="us advantage">✅ Southampton specialist</div>
                        <div class="them limitation">⚠️ Generic chain (130+ locations)</div>
                    </div>
                </div>
                
                <div class="comparison-conclusion">
                    <p><strong>🎯 The Result:</strong> Superior experience, better service, local expertise!</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Category Filtering JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.bms-enhanced-services-container');
        if (!container) return;
        
        const categoryTabs = container.querySelectorAll('.service-category-tab');
        const categoryServices = container.querySelectorAll('.service-category-section');
        
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const category = this.dataset.category;
                
                // Update active tab
                categoryTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show/hide services
                categoryServices.forEach(section => {
                    if (category === 'all' || section.dataset.category === category) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
                
                // Trigger professional messaging
                if (window.competitiveMessaging) {
                    window.competitiveMessaging.addContextualMessage(category);
                }
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

// Register the shortcode (only if not already registered)
if (!shortcode_exists('bms_enhanced_services')) {
    add_shortcode('bms_enhanced_services', 'bms_enhanced_service_cards_shortcode');
}

/**
 * Single Service Card Shortcode
 * 
 * Usage: [bms_service_card service="mot_test" show_competitive="true"]
 */
function bms_single_service_card_shortcode($atts) {
    $atts = shortcode_atts(array(
        'service' => 'mot_test',
        'show_competitive' => 'true',
        'layout' => 'card'), $atts, 'bms_service_card');
    
    $service = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_service($atts['service']);
    if (!$service) {
        return '<p class="bms-error">Service not found: ' . esc_html($atts['service']) . '</p>';
    }
    
    $category_id = $service['category'] ?? 'general';
    
    ob_start();
    ?>
    <div class="service-card single-service <?php echo esc_attr($category_id); ?>-service" 
         data-service="<?php echo esc_attr($atts['service']); ?>">
        
        <div class="service-icon">
            <?php if (isset($service['icon'])): ?>
                <i class="<?php echo esc_attr($service['icon']); ?>"></i>
            <?php else: ?>
                🔧
            <?php endif; ?>
        </div>
        
        <h4><?php echo esc_html(isset($service['name']) ? $service['name'] : 'Unknown Service'); ?></h4>
        <p class="service-price">From £<?php echo number_format(isset($service['base_price']) ? $service['base_price'] : 0, 2); ?></p>
        <p class="service-desc"><?php echo esc_html(isset($service['description']) ? $service['description'] : ''); ?></p>
        
        <?php if (isset($service['competitive_note']) && $atts['show_competitive'] === 'true'): ?>
        <div class="competitive-note">
            <span class="competitive-icon">🎯</span>
            <span class="competitive-text"><?php echo esc_html($service['competitive_note']); ?></span>
        </div>
        <?php endif; ?>
        
        <button type="button" class="btn-select-service" data-service="<?php echo esc_attr($atts['service']); ?>">
            Book <?php echo esc_html($service['name']); ?>
        </button>
    </div>
    <?php
    
    return ob_get_clean();
}

// Register the single service shortcode (only if not already registered)
if (!shortcode_exists('bms_service_card')) {
    add_shortcode('bms_service_card', 'bms_single_service_card_shortcode');
}

/**
 * Services Comparison Table Shortcode
 * 
 * Usage: [bms_comparison_table]
 */
function bms_comparison_table_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => 'Blue Motors vs industry leaders',
        'show_conclusion' => 'true'), $atts, 'bms_comparison_table');
    
    ob_start();
    ?>
    <div class="service-comparison-table standalone">
        <div class="comparison-container">
            <h4>🏆 <?php echo esc_html($atts['title']); ?></h4>
            <div class="comparison-grid">
                <div class="comparison-header">
                    <div class="feature-col">Feature</div>
                    <div class="us-col">Blue Motors Southampton</div>
                    <div class="them-col">industry leaders</div>
                </div>
                
                <div class="comparison-row">
                    <div class="feature">Online Tyre Orders</div>
                    <div class="us advantage">✅ Complete online ordering</div>
                    <div class="them limitation">❌ Must call for tyres</div>
                </div>
                
                <div class="comparison-row">
                    <div class="feature">Payment Process</div>
                    <div class="us advantage">✅ Multiple secure options</div>
                    <div class="them limitation">⚠️ PayPal integration issues</div>
                </div>
                
                <div class="comparison-row">
                    <div class="feature">Date Format</div>
                    <div class="us advantage">✅ UK format (DD/MM/YYYY)</div>
                    <div class="them limitation">❌ American format confusion</div>
                </div>
                
                <div class="comparison-row">
                    <div class="feature">Mobile Experience</div>
                    <div class="us advantage">✅ Touch-optimized interface</div>
                    <div class="them limitation">⚠️ Basic mobile design</div>
                </div>
                
                <div class="comparison-row">
                    <div class="feature">Website Access</div>
                    <div class="us advantage">✅ Always accessible</div>
                    <div class="them limitation">❌ Cloudflare blocks users</div>
                </div>
                
                <div class="comparison-row">
                    <div class="feature">Local Focus</div>
                    <div class="us advantage">✅ Southampton specialist</div>
                    <div class="them limitation">⚠️ Generic chain (130+ locations)</div>
                </div>
            </div>
            
            <?php if ($atts['show_conclusion'] === 'true'): ?>
            <div class="comparison-conclusion">
                <p><strong>🎯 The Result:</strong> Superior experience, better service, local expertise!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Get category icon based on category ID
 * 
 * @param string $category_id Category ID
 * @return string Category icon
 */
function bms_get_category_icon($category_id) {
    $icons = array(
        'general' => '🔧',
        'climate' => '❄️',
        'safety' => '🛡️', 
        'electrical' => '🔋',
        'emissions' => '💨',
        'drivetrain' => '⚙️',
        'tyres' => '🛞',
        'testing' => '🔍',
        'servicing' => '🔧',
        'inspection' => '👁️',
        'diagnostics' => '💻',
        'repairs' => '🔨',
        'other' => '🛠️'
    );
    
    return isset($icons[$category_id]) ? $icons[$category_id] : '🔧';
}

/**
 * Get competitive note for category
 * 
 * @param string $category_id Category ID
 * @return string Competitive note
 */
function bms_get_category_competitive_note($category_id) {
    $notes = array(
        'tyres' => 'Complete online ordering - no phone calls needed!',
        'testing' => 'Fast turnaround with professional service',
        'servicing' => 'Comprehensive service with detailed reporting',
        'safety' => 'Expert safety inspections with immediate results',
        'electrical' => 'Advanced diagnostic equipment and expertise',
        'general' => 'Local expertise with personal service'
    );
    
    return isset($notes[$category_id]) ? $notes[$category_id] : '';
}

// Register the comparison table shortcode
add_shortcode('bms_comparison_table', 'bms_comparison_table_shortcode');
