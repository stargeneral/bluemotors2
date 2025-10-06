<?php
/**
 * Enhanced Service Cards Template - Phase 3
 * Blue Motors Southampton - Professional Service Display
 * 
 * Displays service categories with enhanced features
 * 
 * @package BlueMotosSouthampton
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display enhanced service cards with categories
 */
function bms_display_enhanced_service_cards() {
    $categories = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_simple_categories();
    $services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true);
    
    ob_start();
    ?>
    
    <!-- Phase 3: Professional Header -->
    <div class="competitive-header" id="competitive-header">
        <div class="advantage-message">
            <h3>🎯 Why Choose Blue Motors Over other automotive services?</h3>
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
            <div class="header-cta">
                <p><strong>Experience the difference</strong> - book with confidence at Blue Motors Southampton!</p>
            </div>
        </div>
    </div>
    
    <!-- Phase 3: Service Category Tabs (Mobile-Optimized) -->
    <div class="service-category-tabs" id="service-category-tabs">
        <button class="service-category-tab active" data-category="all">
            <span class="tab-icon">🔧</span>
            <span class="tab-label">All Services</span>
        </button>
        <?php foreach ($categories as $category_id => $category): ?>;
        <button class="service-category-tab" data-category="<?php echo esc_attr($category_id); ?>">
            <span class="tab-icon"><?php echo isset($category['icon']) ? $category['icon'] : '🔧'; ?></span>
            <span class="tab-label"><?php echo esc_html($category['name']); ?></span>
        </button>
        <?php endforeach; ?>
    </div>
    
    <!-- Service Cards Container -->
    <div class="service-cards" id="service-cards-container">
        <?php foreach ($categories as $category_id => $category): ?>
            
            <!-- Category Section -->;
            <div class="service-category-section" data-category="<?php echo esc_attr($category_id); ?>">
                
                <!-- Category Header -->
                <div class="category-header">
                    <h3>
                        <span class="category-icon"><?php echo isset($category['icon']) ? $category['icon'] : '🔧'; ?></span>
                        <?php echo esc_html($category['name']); ?>
                    </h3>
                    <p class="category-description"><?php echo esc_html($category['description'] ?? ''); ?></p>
                    
                    <?php if (isset($category['competitive_note'])): ?>
                    <div class="category-competitive-note">
                        <strong>🎯 Our Advantage:</strong> <?php echo esc_html($category['competitive_note']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($category['competitive_advantage'])): ?>
                    <div class="category-competitive-advantage">
                        <strong>🚀 Exclusive:</strong> <?php echo esc_html($category['competitive_advantage']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Services in this category -->
                <div class="category-services">
                    <?php 
                    $category_services = array_filter($services, function($service) use ($category_id) {
                        return (isset($service['category']) && $service['category'] === $category_id);
                    });
                    
                    foreach ($category_services as $service_id => $service): ?>
                    
                    <!-- Individual Service Card -->;
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
                        
                        <!-- Service Title -->
                        <h4><?php echo esc_html($service['name']); ?></h4>
                        
                        <!-- Service Price -->
                        <p class="service-price">
                            From £<?php echo number_format($service['base_price'], 2); ?>
                        </p>
                        
                        <!-- Service Description -->
                        <p class="service-desc"><?php echo esc_html($service['description']); ?></p>
                        
                        <!-- Service Duration -->
                        <?php if (isset($service['duration'])): ?>
                        <p class="service-duration">
                            Duration: <?php echo esc_html($service['duration']); ?> minutes
                        </p>
                        <?php endif; ?>
                        
                        <!-- Service Features -->
                        <?php if (isset($service['includes']) && is_array($service['includes'])): ?>
                        <div class="service-features">
                            <?php foreach (array_slice($service['includes'], 0, 3) as $feature): ?>
                            <span class="feature">✓ <?php echo esc_html($feature); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Seasonal/Special Notes -->
                        <?php if (isset($service['seasonal']) && $service['seasonal']): ?>
                        <div class="service-seasonal-note">
                            <span class="seasonal-icon">🌟</span>
                            <span>Popular seasonal service</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($service['safety_critical']) && $service['safety_critical']): ?>
                        <div class="service-safety-note">
                            <span class="safety-icon">⚠️</span>
                            <span>Safety critical service</span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Professional Notes -->
                        <?php if (isset($service['competitive_note'])): ?>
                        <div class="competitive-note competitive-note-advantage">
                            <span class="competitive-icon">🎯</span>
                            <span class="competitive-text"><strong>vs F1:</strong> <?php echo esc_html($service['competitive_note']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($service['f1_equivalent']) && $service['f1_equivalent']): ?>
                        <div class="f1-equivalent-note">
                            <span class="f1-icon">✅</span>
                            <span>other automotive services offers this - we do it better!</span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Book Service Button -->
                        <button type="button" class="btn-select-service" data-service="<?php echo esc_attr($service_id); ?>">
                            <?php if ($service_id === 'tyre_fitting'): ?>
                                Find Your Tyres Online
                            <?php else: ?>;
                                Book <?php echo esc_html($service['name']); ?>
                            <?php endif; ?>
                        </button>
                        
                        <!-- Special tyre ordering note -->
                        <?php if ($service_id === 'tyre_fitting'): ?>
                        <div class="tyre-advantage-note">
                            <strong>🚀 Major Advantage:</strong> Complete online tyre ordering - Some providers require phone calls!
                        </div>;
                        <?php endif; ?>
                        
                    </div>
                    
                    <?php endforeach; ?>
                </div>
                
            </div>
            
        <?php endforeach; ?>
    </div>
    
    <!-- Comparison Table -->
    <div class="service-comparison-table">
        <div class="comparison-container">
            <h4>🏆 Blue Motors vs other automotive services</h4>
            <div class="comparison-grid">
                <div class="comparison-header">
                    <div class="feature-col">Feature</div>
                    <div class="us-col">Blue Motors Southampton</div>
                    <div class="them-col">other automotive services</div>
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
            
            <div class="comparison-conclusion">
                <p><strong>🎯 The Result:</strong> Superior experience, better service, local expertise!</p>
            </div>
        </div>
    </div>
    
    <!-- JavaScript for category filtering -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryTabs = document.querySelectorAll('.service-category-tab');
        const categoryServices = document.querySelectorAll('.service-category-section');
        
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
                
                // Trigger professional messaging for category change
                if (window.competitiveMessaging) {
                    window.competitiveMessaging.addContextualMessage(category);
                }
            });
        });
        
        // Mobile swipe support for category tabs
        let startX = null;
        const tabsContainer = document.getElementById('service-category-tabs');
        
        tabsContainer.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
        });
        
        tabsContainer.addEventListener('touchend', function(e) {
            if (!startX) return;
            
            const endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            
            if (Math.abs(diff) > 50) { // Minimum swipe distance
                const activeTab = document.querySelector('.service-category-tab.active');
                const tabs = Array.from(categoryTabs);
                const currentIndex = tabs.indexOf(activeTab);
                
                if (diff > 0 && currentIndex < tabs.length - 1) {
                    // Swipe left - next tab
                    tabs[currentIndex + 1].click();
                } else if (diff < 0 && currentIndex > 0) {
                    // Swipe right - previous tab
                    tabs[currentIndex - 1].click();
                }
            }
            
            startX = null;
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

// Note: Shortcode function is now properly handled in includes/shortcodes/enhanced-service-cards-shortcode.php

/**
 * Get service card for specific service
 */
function bms_get_single_service_card($service_id) {
    $service = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_service($service_id);
    if (!$service) return '';
    
    $categories = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_simple_categories();
    $category_id = $service['category'] ?? 'general';
    
    ob_start();
    ?>
    <div class="service-card single-service <?php echo esc_attr($category_id); ?>-service" 
         data-service="<?php echo esc_attr($service_id); ?>">
        
        <div class="service-icon">
            <?php if (isset($service['icon'])): ?>
                <i class="<?php echo esc_attr($service['icon']); ?>"></i>
            <?php else: ?>
                🔧
            <?php endif; ?>
        </div>
        
        <h4><?php echo esc_html($service['name']); ?></h4>
        <p class="service-price">From £<?php echo number_format($service['base_price'], 2); ?></p>
        <p class="service-desc"><?php echo esc_html($service['description']); ?></p>
        
        <?php if (isset($service['competitive_note'])): ?>
        <div class="competitive-note competitive-note-advantage">
            <span class="competitive-icon">🎯</span>
            <span class="competitive-text"><?php echo esc_html($service['competitive_note']); ?></span>
        </div>
        <?php endif; ?>
        
        <button type="button" class="btn-select-service" data-service="<?php echo esc_attr($service_id); ?>">
            Book <?php echo esc_html($service['name']); ?>
        </button>
    </div>
    <?php
    
    return ob_get_clean();
}

// Note: Single service card shortcode is now properly handled in includes/shortcodes/enhanced-service-cards-shortcode.php
