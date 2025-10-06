<?php
/**
 * Blue Motors Southampton - Tyre Management Admin Interface
 * Phase 2 Completion: Admin Interface for Tyre Inventory
 * 
 * Professional interface for managing tyre inventory and bookings
 * 
 * @package BlueMotosSouthampton
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tyre Management Page
 */
function bms_tyre_management_page() {
    // Handle form submissions
    if (isset($_POST['action'])) {
        bms_handle_tyre_admin_actions();
    }
    
    // Get current tab
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'inventory';
    
    ?>
    <div class="wrap">
        <h1>🛞 Tyre Management - Blue Motors Southampton</h1>
        
        <!-- Professional Advantage Notice -->
        <div class="notice notice-success">
            <p><strong>🎯 Professional Advantage Active!</strong> 
            Your customers can order tyres online while other automotive services customers must call!</p>
        </div>
        
        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper">
            <a href="?page=bms-tyre-management&tab=inventory" 
               class="nav-tab <?php echo $current_tab === 'inventory' ? 'nav-tab-active' : ''; ?>">
                📦 Tyre Inventory
            </a>
            <a href="?page=bms-tyre-management&tab=bookings" 
               class="nav-tab <?php echo $current_tab === 'bookings' ? 'nav-tab-active' : ''; ?>">
                📅 Tyre Bookings
            </a>
            <a href="?page=bms-tyre-management&tab=add-tyre" 
               class="nav-tab <?php echo $current_tab === 'add-tyre' ? 'nav-tab-active' : ''; ?>">
                ➕ Add New Tyre
            </a>
            <a href="?page=bms-tyre-management&tab=analytics" 
               class="nav-tab <?php echo $current_tab === 'analytics' ? 'nav-tab-active' : ''; ?>">
                📊 Analytics
            </a>
        </nav>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <?php
            switch ($current_tab) {
                case 'inventory':
                    bms_render_tyre_inventory_tab();
                    break;
                case 'bookings':
                    bms_render_tyre_bookings_tab();
                    break;
                case 'add-tyre':
                    bms_render_add_tyre_tab();
                    break;
                case 'analytics':
                    bms_render_tyre_analytics_tab();
                    break;
                default:
                    bms_render_tyre_inventory_tab();
            }
            ?>
        </div>
    </div>
    
    <style>
    .tyre-inventory-table th,
    .tyre-inventory-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .tyre-card-admin {
        background: white;
        border: 1px solid #ccd0d4;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }
    
    .stock-status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .stock-high { background: #d1fae5; color: #065f46; }
    .stock-medium { background: #fef3c7; color: #92400e; }
    .stock-low { background: #fee2e2; color: #991b1b; }
    .stock-out { background: #f3f4f6; color: #374151; }
    
    .price-edit {
        width: 80px;
        padding: 4px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .tyre-tier-premium { background: #fef3c7; color: #92400e; }
    .tyre-tier-mid-range { background: #dbeafe; color: #1e40af; }
    .tyre-tier-budget { background: #d1fae5; color: #065f46; }
    </style>
    <?php
}

/**
 * Render Tyre Inventory Tab
 */
function bms_render_tyre_inventory_tab() {
    global $wpdb;
    
    // Get tyres with pagination
    $per_page = 20;
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($page - 1) * $per_page;
    
    // Get search and filter parameters
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $brand_filter = isset($_GET['brand_filter']) ? sanitize_text_field($_GET['brand_filter']) : '';
    $tier_filter = isset($_GET['tier_filter']) ? sanitize_text_field($_GET['tier_filter']) : '';
    
    // Build WHERE clause
    $where_conditions = ['1=1'];
    $where_values = [];
    
    if ($search) {
        $where_conditions[] = "(brand LIKE %s OR model LIKE %s OR size LIKE %s)";
        $search_term = '%' . $wpdb->esc_like($search) . '%';
        $where_values[] = $search_term;
        $where_values[] = $search_term;
        $where_values[] = $search_term;
    }
    
    if ($brand_filter) {
        $where_conditions[] = "brand = %s";
        $where_values[] = $brand_filter;
    }
    
    if ($tier_filter) {
        $where_conditions[] = "brand_tier = %s";
        $where_values[] = $tier_filter;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get tyres
    $table_name = $wpdb->prefix . 'bms_tyres';
    $query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY brand, model LIMIT $per_page OFFSET $offset";
    
    if ($where_values) {
        $tyres = $wpdb->get_results($wpdb->prepare($query, $where_values));
        $total_tyres = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE $where_clause", $where_values));
    } else {
        $tyres = $wpdb->get_results($query);
        $total_tyres = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where_clause");
    }
    
    // Get unique brands for filter
    $brands = $wpdb->get_col("SELECT DISTINCT brand FROM $table_name ORDER BY brand");
    
    ?>
    <div class="tyre-inventory-section">
        <h2>Tyre Inventory Management</h2>
        
        <!-- Quick Stats -->
        <div class="tyre-stats">
            <?php
            $stats = $wpdb->get_row("
                SELECT 
                    COUNT(*) as total_tyres,
                    SUM(CASE WHEN stock_quantity > 10 THEN 1 ELSE 0 END) as high_stock,
                    SUM(CASE WHEN stock_quantity <= 5 THEN 1 ELSE 0 END) as low_stock,
                    SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                    AVG(price) as avg_price
                FROM $table_name 
                WHERE is_active = 1;
            ");
            ?>
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div class="tyre-stat-card" style="background: white; padding: 16px; border-radius: 8px; border: 1px solid #ddd;">
                    <h3>📦 Total Tyres</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 0;"><?php echo $stats->total_tyres; ?></p>
                </div>
                <div class="tyre-stat-card" style="background: white; padding: 16px; border-radius: 8px; border: 1px solid #ddd;">
                    <h3>✅ High Stock</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 0;"><?php echo $stats->high_stock; ?></p>
                </div>
                <div class="tyre-stat-card" style="background: white; padding: 16px; border-radius: 8px; border: 1px solid #ddd;">
                    <h3>⚠️ Low Stock</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 0;"><?php echo $stats->low_stock; ?></p>
                </div>
                <div class="tyre-stat-card" style="background: white; padding: 16px; border-radius: 8px; border: 1px solid #ddd;">
                    <h3>💷 Avg Price</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 0;">£<?php echo number_format($stats->avg_price, 2); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="tablenav top">
            <form method="get" action="">
                <input type="hidden" name="page" value="bms-tyre-management">
                <input type="hidden" name="tab" value="inventory">
                
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 16px;">
                    <input type="text" name="search" value="<?php echo esc_attr($search); ?>" 
                           placeholder="Search tyres..." style="width: 200px;">
                    
                    <select name="brand_filter">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo esc_attr($brand); ?>" 
                                    <?php selected($brand_filter, $brand); ?>>
                                <?php echo esc_html($brand); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="tier_filter">
                        <option value="">All Tiers</option>
                        <option value="premium" <?php selected($tier_filter, 'premium'); ?>>Premium</option>
                        <option value="mid-range" <?php selected($tier_filter, 'mid-range'); ?>>Mid-Range</option>
                        <option value="budget" <?php selected($tier_filter, 'budget'); ?>>Budget</option>
                    </select>
                    
                    <input type="submit" class="button" value="Filter">
                    
                    <?php if ($search || $brand_filter || $tier_filter): ?>
                        <a href="?page=bms-tyre-management&tab=inventory" class="button">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Tyre Table -->
        <table class="wp-list-table widefat fixed striped tyre-inventory-table">
            <thead>
                <tr>
                    <th>Brand & Model</th>
                    <th>Size</th>
                    <th>Tier</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>EU Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tyres)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <p>No tyres found. <a href="?page=bms-tyre-management&tab=add-tyre">Add your first tyre</a></p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tyres as $tyre): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($tyre->brand); ?></strong><br>
                                <span style="color: #666;"><?php echo esc_html($tyre->model); ?></span>
                            </td>
                            <td>
                                <code><?php echo esc_html($tyre->size); ?></code><br>
                                <small><?php echo esc_html($tyre->speed_rating . $tyre->load_index); ?></small>
                            </td>
                            <td>
                                <span class="tyre-tier-<?php echo esc_attr($tyre->brand_tier); ?>" 
                                      style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                    <?php echo esc_html(ucfirst(str_replace('-', ' ', $tyre->brand_tier))); ?>
                                </span>
                            </td>
                            <td>
                                <input type="number" step="0.01" value="<?php echo esc_attr($tyre->price); ?>" 
                                       class="price-edit" data-tyre-id="<?php echo $tyre->id; ?>">
                                <br><small>+£<?php echo esc_html($tyre->fitting_price); ?> fitting</small>
                            </td>
                            <td>
                                <?php
                                $stock_class = 'stock-out';
                                if ($tyre->stock_quantity > 10) $stock_class = 'stock-high';
                                elseif ($tyre->stock_quantity > 5) $stock_class = 'stock-medium';
                                elseif ($tyre->stock_quantity > 0) $stock_class = 'stock-low';
                                ?>
                                <span class="stock-status <?php echo $stock_class; ?>">
                                    <?php echo $tyre->stock_quantity; ?> in stock
                                </span>
                            </td>
                            <td>
                                <?php if ($tyre->fuel_efficiency && $tyre->wet_grip): ?>
                                    <small>
                                        Fuel: <?php echo esc_html($tyre->fuel_efficiency); ?><br>
                                        Wet: <?php echo esc_html($tyre->wet_grip); ?>
                                    </small>
                                <?php else: ?>
                                    <small style="color: #999;">No rating</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="button button-small" onclick="editTyre(<?php echo $tyre->id; ?>)">
                                    Edit
                                </button>
                                <button class="button button-small" 
                                        onclick="toggleTyreStatus(<?php echo $tyre->id; ?>, <?php echo $tyre->is_active ? 'false' : 'true'; ?>)">
                                    <?php echo $tyre->is_active ? 'Disable' : 'Enable'; ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_tyres > $per_page): ?>
            <div class="tablenav bottom">
                <?php
                $total_pages = ceil($total_tyres / $per_page);
                echo paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $page
                ]);
                ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
    function editTyre(tyreId) {
        // Simple edit functionality
        window.open('?page=bms-tyre-management&tab=add-tyre&edit=' + tyreId, '_self');
    }
    
    function toggleTyreStatus(tyreId, newStatus) {
        if (confirm('Are you sure you want to change this tyre\'s status?')) {
            jQuery.post(ajaxurl, {
                action: 'bms_toggle_tyre_status',
                tyre_id: tyreId,
                status: newStatus,
                nonce: '<?php echo wp_create_nonce('bms_admin'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        }
    }
    
    // Auto-save price changes
    jQuery(document).ready(function($) {
        $('.price-edit').on('change', function() {
            var tyreId = $(this).data('tyre-id');
            var newPrice = $(this).val();
            
            $.post(ajaxurl, {
                action: 'bms_update_tyre_price',
                tyre_id: tyreId,
                price: newPrice,
                nonce: '<?php echo wp_create_nonce('bms_admin'); ?>'
            }, function(response) {
                if (response.success) {
                    // Show success indicator
                    $(this).css('border-color', '#10b981').delay(1000).queue(function() {
                        $(this).css('border-color', '#ddd').dequeue();
                    });
                } else {
                    alert('Error updating price: ' + response.data);
                }
            }.bind(this));
        });
    });
    </script>
    <?php
}

/**
 * Render Tyre Bookings Tab
 */
function bms_render_tyre_bookings_tab() {
    global $wpdb;
    
    // Get tyre bookings
    $table_name = $wpdb->prefix . 'bms_tyre_bookings';
    $bookings = $wpdb->get_results("
        SELECT tb.*, t.brand, t.model, t.size 
        FROM $table_name tb
        LEFT JOIN {$wpdb->prefix}bms_tyres t ON tb.tyre_id = t.id
        ORDER BY tb.fitting_date DESC, tb.fitting_time DESC
        LIMIT 50;
    ");
    
    ?>
    <div class="tyre-bookings-section">
        <h2>Tyre Fitting Bookings</h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Tyre</th>
                    <th>Fitting Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <p>No tyre bookings yet. When customers start ordering, they'll appear here!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>
                                <code><?php echo esc_html($booking->booking_reference); ?></code>
                            </td>
                            <td>
                                <strong><?php echo esc_html($booking->customer_name); ?></strong><br>
                                <small><?php echo esc_html($booking->customer_email); ?></small><br>
                                <small><?php echo esc_html($booking->customer_phone); ?></small>
                            </td>
                            <td>
                                <strong><?php echo esc_html($booking->vehicle_reg); ?></strong><br>
                                <small><?php echo esc_html($booking->vehicle_make . ' ' . $booking->vehicle_model); ?></small>
                            </td>
                            <td>
                                <strong><?php echo esc_html($booking->brand . ' ' . $booking->model); ?></strong><br>
                                <small><?php echo esc_html($booking->size); ?> x <?php echo $booking->quantity; ?></small>
                            </td>
                            <td>
                                <?php echo date('j M Y', strtotime($booking->fitting_date)); ?><br>
                                <small><?php echo date('g:i A', strtotime($booking->fitting_time)); ?></small>
                            </td>
                            <td>
                                <strong>£<?php echo number_format($booking->total_price, 2); ?></strong>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($booking->booking_status); ?>">
                                    <?php echo esc_html(ucfirst($booking->booking_status)); ?>
                                </span>
                            </td>
                            <td>
                                <button class="button button-small">View</button>
                                <button class="button button-small">Complete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Render Add Tyre Tab
 */
function bms_render_add_tyre_tab() {
    $editing = isset($_GET['edit']) ? intval($_GET['edit']) : false;
    $tyre_data = null;
    
    if ($editing) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bms_tyres';
        $tyre_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $editing));
        
        if (!$tyre_data) {
            echo '<div class="notice notice-error"><p>Tyre not found!</p></div>';
            return;
        }
    }
    
    ?>
    <div class="add-tyre-section">
        <h2><?php echo $editing ? 'Edit Tyre' : 'Add New Tyre'; ?></h2>
        
        <form method="post" action="">
            <input type="hidden" name="action" value="<?php echo $editing ? 'update_tyre' : 'add_tyre'; ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="tyre_id" value="<?php echo $editing; ?>">
            <?php endif; ?>
            <?php wp_nonce_field('bms_tyre_admin', 'bms_tyre_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Brand</th>
                    <td>
                        <input type="text" name="brand" value="<?php echo $tyre_data ? esc_attr($tyre_data->brand) : ''; ?>" 
                               class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Model</th>
                    <td>
                        <input type="text" name="model" value="<?php echo $tyre_data ? esc_attr($tyre_data->model) : ''; ?>" 
                               class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Size</th>
                    <td>
                        <input type="text" name="size" value="<?php echo $tyre_data ? esc_attr($tyre_data->size) : ''; ?>" 
                               class="regular-text" placeholder="e.g., 205/55R16" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Dimensions</th>
                    <td>
                        <input type="number" name="width" value="<?php echo $tyre_data ? $tyre_data->width : ''; ?>" 
                               placeholder="Width (mm)" style="width: 100px;" required>
                        <input type="number" name="profile" value="<?php echo $tyre_data ? $tyre_data->profile : ''; ?>" 
                               placeholder="Profile %" style="width: 100px;" required>
                        <input type="number" name="rim" value="<?php echo $tyre_data ? $tyre_data->rim : ''; ?>" 
                               placeholder="Rim (inches)" style="width: 100px;" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Brand Tier</th>
                    <td>
                        <select name="brand_tier" required>
                            <option value="">Select Tier</option>
                            <option value="premium" <?php echo ($tyre_data && $tyre_data->brand_tier === 'premium') ? 'selected' : ''; ?>>Premium</option>
                            <option value="mid-range" <?php echo ($tyre_data && $tyre_data->brand_tier === 'mid-range') ? 'selected' : ''; ?>>Mid-Range</option>
                            <option value="budget" <?php echo ($tyre_data && $tyre_data->brand_tier === 'budget') ? 'selected' : ''; ?>>Budget</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Pricing</th>
                    <td>
                        <input type="number" step="0.01" name="price" value="<?php echo $tyre_data ? $tyre_data->price : ''; ?>" 
                               placeholder="Price per tyre" style="width: 120px;" required> £
                        <input type="number" step="0.01" name="fitting_price" value="<?php echo $tyre_data ? $tyre_data->fitting_price : '25.00'; ?>" 
                               placeholder="Fitting price" style="width: 120px;"> £ fitting
                    </td>
                </tr>
                <tr>
                    <th scope="row">Stock Quantity</th>
                    <td>
                        <input type="number" name="stock_quantity" value="<?php echo $tyre_data ? $tyre_data->stock_quantity : '10'; ?>" 
                               style="width: 100px;" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Speed Rating & Load Index</th>
                    <td>
                        <input type="text" name="speed_rating" value="<?php echo $tyre_data ? esc_attr($tyre_data->speed_rating) : ''; ?>" 
                               placeholder="e.g., V" style="width: 60px;">
                        <input type="number" name="load_index" value="<?php echo $tyre_data ? $tyre_data->load_index : ''; ?>" 
                               placeholder="e.g., 91" style="width: 80px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row">EU Ratings</th>
                    <td>
                        Fuel Efficiency: 
                        <select name="fuel_efficiency">
                            <option value="">Not rated</option>
                            <?php foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $rating): ?>
                                <option value="<?php echo $rating; ?>" <?php echo ($tyre_data && $tyre_data->fuel_efficiency === $rating) ? 'selected' : ''; ?>><?php echo $rating; ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        Wet Grip: 
                        <select name="wet_grip">
                            <option value="">Not rated</option>
                            <?php foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $rating): ?>
                                <option value="<?php echo $rating; ?>" <?php echo ($tyre_data && $tyre_data->wet_grip === $rating) ? 'selected' : ''; ?>><?php echo $rating; ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        Noise: 
                        <input type="number" name="noise_rating" value="<?php echo $tyre_data ? $tyre_data->noise_rating : ''; ?>" 
                               placeholder="dB" style="width: 60px;"> dB
                    </td>
                </tr>
                <tr>
                    <th scope="row">Season</th>
                    <td>
                        <select name="season">
                            <option value="summer" <?php echo ($tyre_data && $tyre_data->season === 'summer') ? 'selected' : ''; ?>>Summer</option>
                            <option value="winter" <?php echo ($tyre_data && $tyre_data->season === 'winter') ? 'selected' : ''; ?>>Winter</option>
                            <option value="all-season" <?php echo ($tyre_data && $tyre_data->season === 'all-season') ? 'selected' : ''; ?>>All Season</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Status</th>
                    <td>
                        <label>
                            <input type="checkbox" name="is_active" value="1" <?php echo (!$tyre_data || $tyre_data->is_active) ? 'checked' : ''; ?>>
                            Active (available for purchase)
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php echo $editing ? 'Update Tyre' : 'Add Tyre'; ?>">
                <a href="?page=bms-tyre-management" class="button">Cancel</a>
            </p>
        </form>
    </div>
    <?php
}

/**
 * Render Analytics Tab
 */
function bms_render_tyre_analytics_tab() {
    global $wpdb;
    
    // Get analytics data
    $tyre_table = $wpdb->prefix . 'bms_tyres';
    $booking_table = $wpdb->prefix . 'bms_tyre_bookings';
    
    ?>
    <div class="tyre-analytics-section">
        <h2>🎯 Tyre Analytics - Your F1 Advantage</h2>
        
        <div class="competitive-success-notice" style="background: #d1fae5; border-left: 4px solid #10b981; padding: 16px; margin-bottom: 20px;">
            <h3>🏆 Professional Advantage Active!</h3>
            <p>Your customers can complete tyre orders online in minutes. other automotive services customers must call and wait!</p>
        </div>
        
        <?php
        // Popular sizes
        $popular_sizes = $wpdb->get_results("
            SELECT size, COUNT(*) as order_count, SUM(total_price) as revenue
            FROM $booking_table 
            WHERE booking_status != 'cancelled'
            GROUP BY size 
            ORDER BY order_count DESC 
            LIMIT 10;
        ");
        
        // Popular brands
        $popular_brands = $wpdb->get_results("
            SELECT t.brand, COUNT(tb.id) as order_count, SUM(tb.total_price) as revenue
            FROM $booking_table tb
            JOIN $tyre_table t ON tb.tyre_id = t.id
            WHERE tb.booking_status != 'cancelled'
            GROUP BY t.brand 
            ORDER BY order_count DESC 
            LIMIT 10;
        ");
        
        // Revenue stats
        $revenue_stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_price) as total_revenue,
                AVG(total_price) as avg_order_value,
                SUM(quantity) as total_tyres_sold
            FROM $booking_table 
            WHERE booking_status != 'cancelled';
        ");
        ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Popular Sizes -->
            <div class="analytics-widget" style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                <h3>📊 Most Popular Sizes</h3>
                <?php if ($popular_sizes): ?>
                    <table class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popular_sizes as $size): ?>
                                <tr>
                                    <td><code><?php echo esc_html($size->size); ?></code></td>
                                    <td><?php echo $size->order_count; ?></td>
                                    <td>£<?php echo number_format($size->revenue, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No tyre orders yet. Analytics will appear as customers start ordering!</p>
                <?php endif; ?>
            </div>
            
            <!-- Popular Brands -->
            <div class="analytics-widget" style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                <h3>🏷️ Most Popular Brands</h3>
                <?php if ($popular_brands): ?>
                    <table class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popular_brands as $brand): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($brand->brand); ?></strong></td>
                                    <td><?php echo $brand->order_count; ?></td>
                                    <td>£<?php echo number_format($brand->revenue, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No tyre orders yet. Brand analytics will appear as customers start ordering!</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Revenue Overview -->
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-top: 20px;">
            <h3>💰 Revenue Overview</h3>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <div style="text-align: center;">
                    <h4>Total Orders</h4>
                    <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo $revenue_stats->total_orders ?? 0; ?></p>
                </div>
                <div style="text-align: center;">
                    <h4>Total Revenue</h4>
                    <p style="font-size: 36px; font-weight: bold; margin: 0;">£<?php echo number_format($revenue_stats->total_revenue ?? 0, 2); ?></p>
                </div>
                <div style="text-align: center;">
                    <h4>Avg Order Value</h4>
                    <p style="font-size: 36px; font-weight: bold; margin: 0;">£<?php echo number_format($revenue_stats->avg_order_value ?? 0, 2); ?></p>
                </div>
                <div style="text-align: center;">
                    <h4>Tyres Sold</h4>
                    <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo $revenue_stats->total_tyres_sold ?? 0; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Handle Admin Actions
 */
function bms_handle_tyre_admin_actions() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    if (!wp_verify_nonce($_POST['bms_tyre_nonce'], 'bms_tyre_admin')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bms_tyres';
    
    switch ($_POST['action']) {
        case 'add_tyre':
            $result = $wpdb->insert(
                $table_name,
                [
                    'brand' => sanitize_text_field($_POST['brand']),
                    'model' => sanitize_text_field($_POST['model']),
                    'size' => sanitize_text_field($_POST['size']),
                    'width' => intval($_POST['width']),
                    'profile' => intval($_POST['profile']),
                    'rim' => intval($_POST['rim']),
                    'speed_rating' => sanitize_text_field($_POST['speed_rating']),
                    'load_index' => intval($_POST['load_index']),
                    'price' => floatval($_POST['price']),
                    'fitting_price' => floatval($_POST['fitting_price']),
                    'stock_quantity' => intval($_POST['stock_quantity']),
                    'brand_tier' => sanitize_text_field($_POST['brand_tier']),
                    'fuel_efficiency' => sanitize_text_field($_POST['fuel_efficiency']),
                    'wet_grip' => sanitize_text_field($_POST['wet_grip']),
                    'noise_rating' => intval($_POST['noise_rating']),
                    'season' => sanitize_text_field($_POST['season']),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ]
            );
            
            if ($result) {
                echo '<div class="notice notice-success"><p>Tyre added successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Error adding tyre.</p></div>';
            }
            break;
            
        case 'update_tyre':
            $result = $wpdb->update(
                $table_name,
                [
                    'brand' => sanitize_text_field($_POST['brand']),
                    'model' => sanitize_text_field($_POST['model']),
                    'size' => sanitize_text_field($_POST['size']),
                    'width' => intval($_POST['width']),
                    'profile' => intval($_POST['profile']),
                    'rim' => intval($_POST['rim']),
                    'speed_rating' => sanitize_text_field($_POST['speed_rating']),
                    'load_index' => intval($_POST['load_index']),
                    'price' => floatval($_POST['price']),
                    'fitting_price' => floatval($_POST['fitting_price']),
                    'stock_quantity' => intval($_POST['stock_quantity']),
                    'brand_tier' => sanitize_text_field($_POST['brand_tier']),
                    'fuel_efficiency' => sanitize_text_field($_POST['fuel_efficiency']),
                    'wet_grip' => sanitize_text_field($_POST['wet_grip']),
                    'noise_rating' => intval($_POST['noise_rating']),
                    'season' => sanitize_text_field($_POST['season']),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ],
                ['id' => intval($_POST['tyre_id'])]
            );
            
            if ($result !== false) {
                echo '<div class="notice notice-success"><p>Tyre updated successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Error updating tyre.</p></div>';
            }
            break;
    }
}

// AJAX handlers for quick actions
add_action('wp_ajax_bms_toggle_tyre_status', function() {
    if (!wp_verify_nonce($_POST['nonce'], 'bms_admin')) {
        wp_send_json_error('Security check failed');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bms_tyres';
    
    $result = $wpdb->update(
        $table_name,
        ['is_active' => $_POST['status'] === 'true' ? 1 : 0],
        ['id' => intval($_POST['tyre_id'])]
    );
    
    if ($result !== false) {
        wp_send_json_success('Status updated');
    } else {
        wp_send_json_error('Database error');
    }
});

add_action('wp_ajax_bms_update_tyre_price', function() {
    if (!wp_verify_nonce($_POST['nonce'], 'bms_admin')) {
        wp_send_json_error('Security check failed');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bms_tyres';
    
    $result = $wpdb->update(
        $table_name,
        ['price' => floatval($_POST['price'])],
        ['id' => intval($_POST['tyre_id'])]
    );
    
    if ($result !== false) {
        wp_send_json_success('Price updated');
    } else {
        wp_send_json_error('Database error');
    }
});
