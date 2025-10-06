<?php
/**
 * Enhanced Service Management Page for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display enhanced service management page
 */
function bms_enhanced_services_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Initialize services if empty
    \BlueMotosSouthampton\Services\ServiceManagerEnhanced::init_services();
    
    // Get current action
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
    $service_id = isset($_GET['service_id']) ? sanitize_text_field($_GET['service_id']) : '';
    
    // Handle form submissions
    if (isset($_POST['bms_save_service']) && check_admin_referer('bms_service_nonce')) {
        $result = bms_handle_service_save();
        if ($result['success']) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
        }
        $action = 'list'; // Redirect to list after save
    }
    
    if (isset($_POST['bms_save_pricing']) && check_admin_referer('bms_pricing_nonce')) {
        $result = bms_handle_pricing_save();
        if ($result['success']) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    ?>
    <div class="wrap">
        <h1>
            <span class="dashicons dashicons-admin-tools" style="font-size: 30px; margin-right: 10px;"></span>
            Service Management
        </h1>
        
        <!-- Action Navigation -->
        <nav class="nav-tab-wrapper wp-clearfix">
            <a href="?page=bms-services&action=list" 
               class="nav-tab <?php echo $action === 'list' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-list-view"></span> Service List
            </a>
            <a href="?page=bms-services&action=add" 
               class="nav-tab <?php echo $action === 'add' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-plus-alt"></span> Add Service
            </a>
            <a href="?page=bms-services&action=pricing" 
               class="nav-tab <?php echo $action === 'pricing' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-money-alt"></span> Pricing Matrix
            </a>
        </nav>
        
        <div class="bms-services-content">
            <?php
            switch ($action) {
                case 'add':
                case 'edit':
                    bms_render_service_form($service_id);
                    break;
                case 'pricing':
                    bms_render_pricing_matrix();
                    break;
                case 'list':
                default:
                    bms_render_services_list();
                    break;
            }
            ?>
        </div>
    </div>
    
    <style>
    .bms-services-content {
        margin-top: 20px;
    }
    
    .nav-tab .dashicons {
        font-size: 16px;
        margin-right: 5px;
        vertical-align: text-top;
    }
    
    .bms-admin-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .bms-admin-card h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .bms-service-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .bms-service-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 8px;
        padding: 20px;
        transition: box-shadow 0.2s;
    }
    
    .bms-service-card:hover {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .bms-service-card.disabled {
        opacity: 0.6;
        background: #f9f9f9;
    }
    
    .bms-service-status {
        float: right;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .bms-service-status.enabled {
        background: #d4edda;
        color: #155724;
    }
    
    .bms-service-status.disabled {
        background: #f8d7da;
        color: #721c24;
    }
    
    .bms-pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .bms-pricing-section {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 15px;
    }
    
    .bms-pricing-section h4 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    </style>
    <?php
}

/**
 * Render services list
 */
function bms_render_services_list() {
    $services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services();
    $categories = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_simple_categories();
    ?>
    
    <div class="bms-admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2><span class="dashicons dashicons-admin-tools"></span> Available Services</h2>
            <a href="?page=bms-services&action=add" class="button button-primary">
                <span class="dashicons dashicons-plus-alt"></span> Add New Service
            </a>
        </div>
        
        <div class="bms-service-grid">
            <?php foreach ($services as $service_id => $service): ?>
                <div class="bms-service-card <?php echo !($service['enabled'] ?? true) ? 'disabled' : ''; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <h3 style="margin: 0 0 10px 0;"><?php echo esc_html($service['name']); ?></h3>
                        <span class="bms-service-status <?php echo ($service['enabled'] ?? true) ? 'enabled' : 'disabled'; ?>">
                            <?php echo ($service['enabled'] ?? true) ? 'Active' : 'Disabled'; ?>
                        </span>
                    </div>
                    
                    <p style="color: #666; margin: 0 0 15px 0;">
                        <?php echo esc_html($service['description']); ?>
                    </p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; font-size: 14px;">
                        <div><strong>Category:</strong> <?php echo esc_html($categories[($service['category'] ?? 'other')] ?? 'Other'); ?></div>
                        <div><strong>Duration:</strong> <?php echo esc_html(($service['duration'] ?? 60)); ?> min</div>
                        <div><strong>Pricing:</strong> 
                            <?php if (($service['pricing_type'] ?? 'fixed') === 'fixed'): ?>
                                £<?php echo number_format(($service['base_price'] ?? 0), 2); ?>
                            <?php else: ?>
                                From £<?php echo number_format(($service['base_price'] ?? 0), 2); ?>
                            <?php endif; ?>
                        </div>
                        <div><strong>Type:</strong> 
                            <?php echo ($service['pricing_type'] ?? 'fixed') === 'fixed' ? 'Fixed Price' : 'Engine Based'; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty(($service['features'] ?? array()))): ?>
                        <div style="margin-bottom: 15px;">
                            <strong>Includes:</strong>
                            <ul style="margin: 5px 0 0 20px; font-size: 13px;">
                                <?php foreach (array_slice(($service['features'] ?? array()), 0, 3) as $feature): ?>
                                    <li><?php echo esc_html($feature); ?></li>
                                <?php endforeach; ?>
                                <?php if (count(($service['features'] ?? array())) > 3): ?>
                                    <li><em>+<?php echo count(($service['features'] ?? array())) - 3; ?> more...</em></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 10px;">
                        <a href="?page=bms-services&action=edit&service_id=<?php echo esc_attr($service_id); ?>" 
                           class="button button-small">
                            <span class="dashicons dashicons-edit"></span> Edit
                        </a>
                        <a href="#" onclick="toggleService('<?php echo esc_js($service_id); ?>')" 
                           class="button button-small">
                            <span class="dashicons dashicons-<?php echo ($service['enabled'] ?? true) ? 'hidden' : 'visibility'; ?>"></span>
                            <?php echo ($service['enabled'] ?? true) ? 'Disable' : 'Enable'; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Service Statistics -->
    <div class="bms-admin-card">
        <h3>Service Statistics</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <?php 
            $enabled_count = count(array_filter($services, function($s) { return isset($s['enabled']) && $s['enabled']; }));

            $total_count = count($services);
            ?>
            <div style="text-align: center; padding: 20px; background: #f0f8f0; border-radius: 4px;">
                <div style="font-size: 2em; font-weight: bold; color: #155724;"><?php echo $enabled_count; ?></div>
                <div>Active Services</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 4px;">
                <div style="font-size: 2em; font-weight: bold; color: #495057;"><?php echo $total_count; ?></div>
                <div>Total Services</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #e7f3ff; border-radius: 4px;">
                <div style="font-size: 2em; font-weight: bold; color: #0073aa;">
                    £<?php echo number_format(array_sum(array_column($services, 'base_price')), 0); ?>
                </div>
                <div>Combined Base Pricing</div>
            </div>
        </div>
    </div>
    
    <script>
    function toggleService(serviceId) {
        if (confirm('Are you sure you want to toggle this service status?')) {
            // AJAX call to toggle service status
            jQuery.post(ajaxurl, {
                action: 'bms_toggle_service',
                service_id: serviceId,
                nonce: '<?php echo wp_create_nonce('bms_toggle_service'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
    }
    </script>
    <?php
}

/**
 * Render service form (add/edit)
 */
function bms_render_service_form($service_id = '') {
    $service = array(
        'name' => '',
        'description' => '',
        'base_price' => '',
        'pricing_type' => 'fixed',
        'duration' => 60,
        'category' => 'other',
        'enabled' => true,
        'sort_order' => 10,
        'features' => array()
    );
    
    $is_edit = false;
    if ($service_id) {
        $existing_service = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_service($service_id);
        if ($existing_service) {
            $service = $existing_service;
            $is_edit = true;
        }
    }
    
    $categories = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_simple_categories();
    $pricing_types = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_pricing_types();
    ?>
    
    <div class="bms-admin-card">
        <h2>
            <?php echo $is_edit ? 'Edit Service: ' . esc_html($service['name']) : 'Add New Service'; ?>
        </h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('bms_service_nonce'); ?>
            <input type="hidden" name="service_id" value="<?php echo esc_attr($service_id); ?>">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="service_name">Service Name</label>
                    </th>
                    <td>
                        <input type="text" id="service_name" name="service_name" 
                               value="<?php echo esc_attr($service['name']); ?>" 
                               class="regular-text" required>
                        <p class="description">The display name for this service</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="service_description">Description</label>
                    </th>
                    <td>
                        <textarea id="service_description" name="service_description" 
                                  rows="3" class="large-text" required><?php echo esc_textarea($service['description']); ?></textarea>
                        <p class="description">Brief description shown to customers</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="service_category">Category</label>
                    </th>
                    <td>
                        <select id="service_category" name="service_category" class="regular-text">
                            <?php foreach ($categories as $cat_id => $cat_name): ?>
                                <option value="<?php echo esc_attr($cat_id); ?>" 
                                        <?php selected(($service['category'] ?? 'other'), $cat_id); ?>>
                                    <?php echo esc_html($cat_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Service category for organization</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="pricing_type">Pricing Type</label>
                    </th>
                    <td>
                        <select id="pricing_type" name="pricing_type" class="regular-text">
                            <?php foreach ($pricing_types as $type_id => $type_name): ?>
                                <option value="<?php echo esc_attr($type_id); ?>" 
                                        <?php selected(($service['pricing_type'] ?? 'fixed'), $type_id); ?>>
                                    <?php echo esc_html($type_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">How pricing is calculated for this service</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="base_price">Base Price (£)</label>
                    </th>
                    <td>
                        <input type="number" id="base_price" name="base_price" 
                               value="<?php echo esc_attr(($service['base_price'] ?? 0)); ?>" 
                               step="0.01" min="0" class="small-text" required>
                        <p class="description">Base price (fixed price or starting price for engine-based)</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="duration">Duration (minutes)</label>
                    </th>
                    <td>
                        <input type="number" id="duration" name="duration" 
                               value="<?php echo esc_attr(($service['duration'] ?? 60)); ?>" 
                               min="15" max="480" class="small-text" required>
                        <p class="description">Estimated service duration for booking slots</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="sort_order">Sort Order</label>
                    </th>
                    <td>
                        <input type="number" id="sort_order" name="sort_order" 
                               value="<?php echo esc_attr(($service['sort_order'] ?? 10)); ?>" 
                               min="1" max="100" class="small-text">
                        <p class="description">Display order (lower numbers appear first)</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Service Features</th>
                    <td>
                        <div id="service-features">
                            <?php if (!empty(($service['features'] ?? array()))): ?>
                                <?php foreach (($service['features'] ?? array()) as $i => $feature): ?>
                                    <div class="feature-row" style="margin-bottom: 10px;">
                                        <input type="text" name="features[]" value="<?php echo esc_attr($feature); ?>" 
                                               class="regular-text" placeholder="Service feature">
                                        <button type="button" class="button remove-feature">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="feature-row" style="margin-bottom: 10px;">
                                    <input type="text" name="features[]" value="" 
                                           class="regular-text" placeholder="Service feature">
                                    <button type="button" class="button remove-feature">Remove</button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="add-feature" class="button">Add Feature</button>
                        <p class="description">Features and benefits included with this service</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Status</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enabled" value="1" 
                                   <?php checked(($service['enabled'] ?? true), true); ?>>
                            Service is active and available for booking
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="bms_save_service" class="button-primary" 
                       value="<?php echo $is_edit ? 'Update Service' : 'Create Service'; ?>">
                <a href="?page=bms-services" class="button" style="margin-left: 10px;">Cancel</a>
            </p>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Add feature functionality
        $('#add-feature').click(function() {
            var newRow = '<div class="feature-row" style="margin-bottom: 10px;">' +
                '<input type="text" name="features[]" value="" class="regular-text" placeholder="Service feature">' +
                '<button type="button" class="button remove-feature">Remove</button>' +
                '</div>';
            $('#service-features').append(newRow);
        });
        
        // Remove feature functionality
        $(document).on('click', '.remove-feature', function() {
            if ($('.feature-row').length > 1) {
                $(this).closest('.feature-row').remove();
            }
        });
    });
    </script>
    <?php
}

/**
 * Render pricing matrix
 */
function bms_render_pricing_matrix() {
    $pricing_matrix = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_engine_pricing();
    ?>
    
    <div class="bms-admin-card">
        <h2><span class="dashicons dashicons-money-alt"></span> Engine-Based Pricing Matrix</h2>
        <p>Configure pricing for services based on engine size and fuel type. This applies to services with "Engine Size Based" pricing.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('bms_pricing_nonce'); ?>
            
            <div class="bms-pricing-grid">
                <?php foreach ($pricing_matrix as $fuel_type => $sizes): ?>
                    <div class="bms-pricing-section">
                        <h4><?php echo ucfirst($fuel_type); ?> Vehicles</h4>
                        
                        <?php if ($fuel_type === 'electric'): ?>
                            <table class="widefat">
                                <tr>
                                    <th>Service Type</th>
                                    <th>Price (£)</th>
                                </tr>
                                <tr>
                                    <td>Interim Service</td>
                                    <td>
                                        <input type="number" step="0.01"
                                               name="pricing[<?php echo $fuel_type; ?>][all][interim]" 
                                               value="<?php echo esc_attr($sizes['all']['interim']); ?>" 
                                               class="small-text">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Full Service</td>
                                    <td>
                                        <input type="number" step="0.01" 
                                               name="pricing[<?php echo $fuel_type; ?>][all][full]" 
                                               value="<?php echo esc_attr($sizes['all']['full']); ?>" 
                                               class="small-text">
                                    </td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <table class="widefat">
                                <tr>
                                    <th>Engine Size</th>
                                    <th>Interim (£)</th>
                                    <th>Full (£)</th>
                                </tr>
                                <?php 
                                $size_labels = array(
                                    'up_to_1000' => 'Up to 1.0L',
                                    'up_to_1600' => '1.0L - 1.6L',
                                    'up_to_2000' => '1.6L - 2.0L',
                                    'up_to_3500' => '2.0L - 3.5L',
                                    'over_3500' => 'Over 3.5L'
                                );
                                ?>
                                <?php foreach ($sizes as $size_key => $prices): ?>
                                    <tr>
                                        <td><?php echo esc_html($size_labels[$size_key] ?? $size_key); ?></td>
                                        <td>
                                            <input type="number" step="0.01" 
                                                   name="pricing[<?php echo $fuel_type; ?>][<?php echo $size_key; ?>][interim]" 
                                                   value="<?php echo esc_attr($prices['interim']); ?>" 
                                                   class="small-text">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" 
                                                   name="pricing[<?php echo $fuel_type; ?>][<?php echo $size_key; ?>][full]" 
                                                   value="<?php echo esc_attr($prices['full']); ?>" 
                                                   class="small-text">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <p class="submit">
                <input type="submit" name="bms_save_pricing" class="button-primary" value="Save Pricing Matrix">
                <a href="?page=bms-services" class="button" style="margin-left: 10px;">Back to Services</a>
            </p>
        </form>
    </div>
    
    <!-- Pricing Calculator Demo -->
    <div class="bms-admin-card">
        <h3>Pricing Calculator Demo</h3>
        <p>Test how pricing works with different vehicle specifications:</p>
        
        <table class="form-table">
            <tr>
                <th>Service Type:</th>
                <td>
                    <select id="demo-service">
                        <option value="interim_service">Interim Service</option>
                        <option value="full_service">Full Service</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Fuel Type:</th>
                <td>
                    <select id="demo-fuel">
                        <option value="petrol">Petrol</option>
                        <option value="diesel">Diesel</option>
                        <option value="hybrid">Hybrid</option>
                        <option value="electric">Electric</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Engine Size (cc):</th>
                <td>
                    <input type="number" id="demo-engine" value="1600" min="1" max="6000">
                </td>
            </tr>
            <tr>
                <th>Calculated Price:</th>
                <td>
                    <strong id="demo-price">£0.00</strong>
                </td>
            </tr>
        </table>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        function updateDemoPrice() {
            var service = $('#demo-service').val();
            var fuel = $('#demo-fuel').val();
            var engine = parseInt($('#demo-engine').val());
            
            // Simple pricing calculation based on current matrix
            var pricing = <?php echo json_encode($pricing_matrix); ?>;
            var price = 0;
            
            if (fuel === 'electric') {
                var serviceType = service === 'full_service' ? 'full' : 'interim';
                price = pricing.electric.all[serviceType];
            } else {
                var serviceType = service === 'full_service' ? 'full' : 'interim';
                var sizeCategory = 'up_to_1600';
                
                if (engine <= 1000) sizeCategory = 'up_to_1000';
                else if (engine <= 1600) sizeCategory = 'up_to_1600';
                else if (engine <= 2000) sizeCategory = 'up_to_2000';
                else if (engine <= 3500) sizeCategory = 'up_to_3500';
                else sizeCategory = 'over_3500';
                
                if (pricing[fuel] && pricing[fuel][sizeCategory]) {
                    price = pricing[fuel][sizeCategory][serviceType];
                }
            }
            
            $('#demo-price').text('£' + parseFloat(price).toFixed(2));
        }
        
        $('#demo-service, #demo-fuel, #demo-engine').on('change input', updateDemoPrice);
        updateDemoPrice(); // Initial calculation
    });
    </script>
    <?php
}

/**
 * Handle service save
 */
function bms_handle_service_save() {
    $service_id = isset($_POST['service_id']) ? sanitize_text_field($_POST['service_id']) : '';
    
    // Generate service ID if new
    if (empty($service_id)) {
        $service_id = sanitize_title($_POST['service_name']);
        if (empty($service_id)) {
            $service_id = 'service_' . time();
        }
    }
    
    // Validate and sanitize input
    $service_data = array(
        'name' => sanitize_text_field($_POST['service_name']),
        'description' => sanitize_textarea_field($_POST['service_description']),
        'base_price' => floatval($_POST['base_price']),
        'pricing_type' => sanitize_text_field($_POST['pricing_type']),
        'duration' => intval($_POST['duration']),
        'category' => sanitize_text_field($_POST['service_category']),
        'enabled' => isset($_POST['enabled']),
        'sort_order' => intval($_POST['sort_order']),
        'features' => array()
    );
    
    // Process features
    if (isset($_POST['features']) && is_array($_POST['features'])) {
        foreach ($_POST['features'] as $feature) {
            $feature = trim(sanitize_text_field($feature));
            if (!empty($feature)) {
                $service_data['features'][] = $feature;
            }
        }
    }
    
    // Save service
    $result = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::update_service($service_id, $service_data);
    
    if ($result) {
        return array(
            'success' => true,
            'message' => 'Service saved successfully!'
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Failed to save service. Please try again.'
        );
    }
}

/**
 * Handle pricing matrix save
 */
function bms_handle_pricing_save() {
    if (!isset($_POST['pricing']) || !is_array($_POST['pricing'])) {
        return array(
            'success' => false,
            'message' => 'Invalid pricing data.'
        );
    }
    
    $pricing_matrix = array();
    
    foreach ($_POST['pricing'] as $fuel_type => $fuel_data) {
        $fuel_type = sanitize_text_field($fuel_type);
        $pricing_matrix[$fuel_type] = array();
        
        foreach ($fuel_data as $size_key => $size_data) {
            $size_key = sanitize_text_field($size_key);
            $pricing_matrix[$fuel_type][$size_key] = array();
            
            foreach ($size_data as $service_key => $price) {
                $service_key = sanitize_text_field($service_key);
                $pricing_matrix[$fuel_type][$size_key][$service_key] = floatval($price);
            }
        }
    }
    
    $result = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::save_engine_pricing($pricing_matrix);
    
    if ($result) {
        return array(
            'success' => true,
            'message' => 'Pricing matrix saved successfully!'
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Failed to save pricing matrix. Please try again.'
        );
    }
}

// AJAX handler for toggling service status
add_action('wp_ajax_bms_toggle_service', function() {
    check_ajax_referer('bms_toggle_service', 'nonce');
    
    $service_id = sanitize_text_field($_POST['service_id']);
    $service = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_service($service_id);
    
    if ($service) {
        $service['enabled'] = !($service['enabled'] ?? true);
        $result = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::update_service($service_id, $service);
        
        wp_send_json_success(array('enabled' => ($service['enabled'] ?? true)));
    } else {
        wp_send_json_error('Service not found');
    }
});
