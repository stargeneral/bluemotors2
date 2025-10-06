<?php
/**
 * Enhanced Booking Management for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render enhanced bookings management page
 */
function bms_enhanced_bookings_page() {
    global $wpdb;
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $table_name = $wpdb->prefix . 'bms_appointments';
    $current_view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'today';
    
    // Handle booking actions
    if (isset($_GET['action']) && isset($_GET['booking_id'])) {
        $booking_id = intval($_GET['booking_id']);
        $action = sanitize_text_field($_GET['action']);
        bms_handle_booking_action($booking_id, $action);
    }
    
    // Get business settings for context
    $business_info = BMS_Settings_Migrator::get_business_info();
    $services = \BlueMotosSouthampton\Services\ServiceManagerEnhanced::get_services(true); // Only enabled services
    ?>
    
    <div class="wrap">
        <h1>
            <span class="dashicons dashicons-calendar-alt" style="font-size: 30px; margin-right: 10px;"></span>
            Booking Management
        </h1>
        
        <!-- View Navigation -->
        <nav class="nav-tab-wrapper wp-clearfix">
            <a href="?page=bms-bookings&view=today" 
               class="nav-tab <?php echo $current_view === 'today' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-clock"></span> Today
            </a>
            <a href="?page=bms-bookings&view=upcoming" 
               class="nav-tab <?php echo $current_view === 'upcoming' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-calendar"></span> Upcoming
            </a>
            <a href="?page=bms-bookings&view=past" 
               class="nav-tab <?php echo $current_view === 'past' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-archive"></span> Past
            </a>
            <a href="?page=bms-bookings&view=all" 
               class="nav-tab <?php echo $current_view === 'all' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-list-view"></span> All Bookings
            </a>
        </nav>
        
        <div class="bms-bookings-content">
            <?php
            switch ($current_view) {
                case 'today':
                    bms_render_today_bookings($table_name, $services);
                    break;
                case 'upcoming':
                    bms_render_upcoming_bookings($table_name, $services);
                    break;
                case 'past':
                    bms_render_past_bookings($table_name, $services);
                    break;
                case 'all':
                    bms_render_all_bookings($table_name, $services);
                    break;
            }
            ?>
        </div>
    </div>
    
    <style>
    .bms-bookings-content {
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
    
    .bms-booking-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        transition: box-shadow 0.2s;
    }
    
    .bms-booking-card:hover {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .bms-booking-status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        float: right;
    }
    
    .bms-booking-status.confirmed {
        background: #d4edda;
        color: #155724;
    }
    
    .bms-booking-status.completed {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .bms-booking-status.cancelled {
        background: #f8d7da;
        color: #721c24;
    }
    
    .bms-booking-status.no-show {
        background: #fff3cd;
        color: #856404;
    }
    
    .bms-booking-grid {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr;
        gap: 20px;
        align-items: start;
    }
    
    .bms-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .bms-stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }
    
    .bms-stat-number {
        font-size: 2em;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .bms-stat-label {
        font-size: 0.9em;
        opacity: 0.9;
    }
    
    .bms-booking-actions {
        margin-top: 15px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .bms-vehicle-details {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        margin: 10px 0;
        font-size: 14px;
    }
    </style>
    <?php
}

/**
 * Render today's bookings
 */
function bms_render_today_bookings($table_name, $services) {
    global $wpdb;
    
    $today = date('Y-m-d');
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE booking_date = %s ORDER BY booking_time ASC",
        $today));
    
    // Get today's statistics
    $total_bookings = count($bookings);
    $completed_bookings = count(array_filter($bookings, function($b) { return $b->booking_status === 'completed'; }));
    $pending_bookings = count(array_filter($bookings, function($b) { return $b->booking_status === 'confirmed'; }));
    $total_revenue = array_sum(array_column($bookings, 'price'));
    ?>
    
    <div class="bms-admin-card">
        <h2>
            <span class="dashicons dashicons-clock"></span> 
            Today's Schedule - <?php echo date('l, F j, Y'); ?>
        </h2>
        
        <!-- Today's Statistics -->
        <div class="bms-stats-grid">
            <div class="bms-stat-card">
                <div class="bms-stat-number"><?php echo $total_bookings; ?></div>
                <div class="bms-stat-label">Total Bookings</div>
            </div>
            <div class="bms-stat-card">
                <div class="bms-stat-number"><?php echo $pending_bookings; ?></div>
                <div class="bms-stat-label">Pending</div>
            </div>
            <div class="bms-stat-card">
                <div class="bms-stat-number"><?php echo $completed_bookings; ?></div>
                <div class="bms-stat-label">Completed</div>
            </div>
            <div class="bms-stat-card">
                <div class="bms-stat-number">£<?php echo number_format($total_revenue, 0); ?></div>
                <div class="bms-stat-label">Today's Revenue</div>
            </div>
        </div>
        
        <?php if (empty($bookings)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <span class="dashicons dashicons-calendar-alt" style="font-size: 48px; margin-bottom: 20px;"></span>
                <h3>No bookings for today</h3>
                <p>Your schedule is clear for today!</p>
            </div>
        <?php else: ?>
            <!-- Today's Bookings List -->
            <?php foreach ($bookings as $booking): ?>
                <?php bms_render_booking_card($booking, $services); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render upcoming bookings
 */
function bms_render_upcoming_bookings($table_name, $services) {
    global $wpdb;
    
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $next_week = date('Y-m-d', strtotime('+7 days'));
    
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE booking_date BETWEEN %s AND %s 
         AND booking_status != 'cancelled'
         ORDER BY booking_date ASC, booking_time ASC",
        $tomorrow, $next_week));
    ?>
    
    <div class="bms-admin-card">
        <h2>
            <span class="dashicons dashicons-calendar"></span> 
            Upcoming Bookings (Next 7 Days)
        </h2>
        
        <?php if (empty($bookings)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <span class="dashicons dashicons-calendar-alt" style="font-size: 48px; margin-bottom: 20px;"></span>
                <h3>No upcoming bookings</h3>
                <p>No bookings scheduled for the next 7 days.</p>
            </div>
        <?php else: ?>
            <?php 
            $current_date = '';
            foreach ($bookings as $booking): 
                if ($booking->booking_date !== $current_date) {
                    $current_date = $booking->booking_date;
                    echo '<h3 style="margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #0073aa; color: #0073aa;">';
                    echo date('l, F j, Y', strtotime($current_date));
                    echo '</h3>';
                }
                bms_render_booking_card($booking, $services);
            endforeach; 
            ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render past bookings
 */
function bms_render_past_bookings($table_name, $services) {
    global $wpdb;
    
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE booking_date < %s 
         ORDER BY booking_date DESC, booking_time DESC
         LIMIT 50",
        date('Y-m-d')
    ));
    ?>
    
    <div class="bms-admin-card">
        <h2>
            <span class="dashicons dashicons-archive"></span> 
            Recent Past Bookings (Last 50)
        </h2>
        
        <?php if (empty($bookings)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <span class="dashicons dashicons-archive" style="font-size: 48px; margin-bottom: 20px;"></span>
                <h3>No past bookings found</h3>
                <p>Past bookings will appear here once you have some booking history.</p>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <?php bms_render_booking_card($booking, $services, true); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render all bookings with pagination
 */
function bms_render_all_bookings($table_name, $services) {
    global $wpdb;
    
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    // Get total count
    $total_bookings = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_bookings / $per_page);
    
    // Get bookings for current page
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name 
         ORDER BY booking_date DESC, booking_time DESC
         LIMIT %d OFFSET %d",
        $per_page, $offset));
    ?>
    
    <div class="bms-admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>
                <span class="dashicons dashicons-list-view"></span> 
                All Bookings (<?php echo number_format($total_bookings); ?> total)
            </h2>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=bms-bookings&view=all&paged=<?php echo $page - 1; ?>" class="button">« Previous</a>
                    <?php endif; ?>
                    
                    <span style="margin: 0 10px;">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=bms-bookings&view=all&paged=<?php echo $page + 1; ?>" class="button">Next »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($bookings)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <span class="dashicons dashicons-list-view" style="font-size: 48px; margin-bottom: 20px;"></span>
                <h3>No bookings found</h3>
                <p>Bookings will appear here once customers start making appointments.</p>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <?php bms_render_booking_card($booking, $services, true); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render individual booking card
 */
function bms_render_booking_card($booking, $services, $show_date = false) {
    $service_name = 'Unknown Service';
    $service_duration = 60;
    
    // Try to get service name from services array
    foreach ($services as $service_id => $service_data) {
        if ($service_id === $booking->service_type || 
            $service_data['name'] === $booking->service_type) {
            $service_name = $service_data['name'];
            $service_duration = $service_data['duration'];
            break;
        }
    }
    
    // Calculate end time
    $booking_datetime = strtotime($booking->booking_date . ' ' . $booking->booking_time);
    $end_time = date('H:i', $booking_datetime + ($service_duration * 60));
    ?>
    
    <div class="bms-booking-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0;">
                        <?php if ($show_date): ?>
                            <?php echo date('M j, Y', strtotime($booking->booking_date)); ?> - 
                        <?php endif; ?>
                        <?php echo date('H:i', strtotime($booking->booking_time)); ?> - <?php echo $end_time; ?>
                    </h3>
                    <span class="bms-booking-status <?php echo esc_attr($booking->booking_status); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $booking->booking_status)); ?>
                    </span>
                </div>
                
                <div class="bms-booking-grid">
                    <!-- Customer Information -->
                    <div>
                        <h4 style="margin: 0 0 10px 0;">Customer</h4>
                        <p style="margin: 0;"><strong><?php echo esc_html($booking->customer_name); ?></strong></p>
                        <p style="margin: 5px 0; font-size: 14px;">
                            <span class="dashicons dashicons-email" style="font-size: 14px;"></span> 
                            <a href="mailto:<?php echo esc_attr($booking->customer_email); ?>">
                                <?php echo esc_html($booking->customer_email); ?>
                            </a>
                        </p>
                        <p style="margin: 5px 0; font-size: 14px;">
                            <span class="dashicons dashicons-phone" style="font-size: 14px;"></span> 
                            <a href="tel:<?php echo esc_attr($booking->customer_phone); ?>">
                                <?php echo esc_html($booking->customer_phone); ?>
                            </a>
                        </p>
                    </div>
                    
                    <!-- Service Information -->
                    <div>
                        <h4 style="margin: 0 0 10px 0;">Service Details</h4>
                        <p style="margin: 0;"><strong><?php echo esc_html($service_name); ?></strong></p>
                        <p style="margin: 5px 0; font-size: 14px;">
                            Reference: <strong><?php echo esc_html($booking->booking_reference ?? 'N/A'); ?></strong>
                        </p>
                        <p style="margin: 5px 0; font-size: 14px;">
                            Duration: <?php echo $service_duration; ?> minutes
                        </p>
                        <p style="margin: 5px 0; font-size: 14px;">
                            Price: <strong>£<?php echo number_format($booking->price, 2); ?></strong>
                        </p>
                    </div>
                    
                    <!-- Vehicle Information -->
                    <div>
                        <h4 style="margin: 0 0 10px 0;">Vehicle</h4>
                        <?php if (!empty($booking->vehicle_registration)): ?>
                            <p style="margin: 0;"><strong><?php echo esc_html($booking->vehicle_registration); ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($booking->vehicle_make) && !empty($booking->vehicle_model)): ?>
                            <p style="margin: 5px 0; font-size: 14px;">
                                <?php echo esc_html($booking->vehicle_make . ' ' . $booking->vehicle_model); ?>
                                <?php if (!empty($booking->vehicle_year)): ?>
                                    (<?php echo esc_html($booking->vehicle_year); ?>)
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($booking->vehicle_engine_size)): ?>
                            <p style="margin: 5px 0; font-size: 14px;">
                                Engine: <?php echo esc_html($booking->vehicle_engine_size); ?>cc
                                <?php if (!empty($booking->vehicle_fuel_type)): ?>
                                    (<?php echo esc_html($booking->vehicle_fuel_type); ?>)
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($booking->notes)): ?>
                    <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        <strong>Notes:</strong> <?php echo esc_html($booking->notes); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Booking Actions -->
                <div class="bms-booking-actions">
                    <?php if ($booking->booking_status === 'confirmed'): ?>
                        <a href="?page=bms-bookings&action=complete&booking_id=<?php echo $booking->id; ?>&view=<?php echo $_GET['view'] ?? 'today'; ?>" 
                           class="button button-primary">
                            <span class="dashicons dashicons-yes"></span> Mark Complete
                        </a>
                        <a href="?page=bms-bookings&action=no_show&booking_id=<?php echo $booking->id; ?>&view=<?php echo $_GET['view'] ?? 'today'; ?>" 
                           class="button">
                            <span class="dashicons dashicons-warning"></span> No Show
                        </a>
                        <a href="?page=bms-bookings&action=cancel&booking_id=<?php echo $booking->id; ?>&view=<?php echo $_GET['view'] ?? 'today'; ?>" 
                           class="button" onclick="return confirm('Are you sure you want to cancel this booking?')">
                            <span class="dashicons dashicons-no"></span> Cancel
                        </a>
                    <?php elseif ($booking->booking_status === 'completed'): ?>
                        <span style="color: green; font-weight: bold;">
                            <span class="dashicons dashicons-yes"></span> Service Completed
                        </span>
                    <?php elseif ($booking->booking_status === 'cancelled'): ?>
                        <span style="color: red; font-weight: bold;">
                            <span class="dashicons dashicons-no"></span> Cancelled
                        </span>
                    <?php elseif ($booking->booking_status === 'no_show'): ?>
                        <span style="color: orange; font-weight: bold;">
                            <span class="dashicons dashicons-warning"></span> No Show
                        </span>
                    <?php endif; ?>
                    
                    <a href="mailto:<?php echo esc_attr($booking->customer_email); ?>?subject=Your booking with Blue Motors Southampton" 
                       class="button">
                        <span class="dashicons dashicons-email"></span> Email Customer
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Handle booking actions
 */
function bms_handle_booking_action($booking_id, $action) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'bms_appointments';
    $valid_actions = array('complete', 'cancel', 'no_show', 'confirm');
    
    if (!in_array($action, $valid_actions)) {
        return;
    }
    
    // Map actions to status
    $status_map = array(
        'complete' => 'completed',
        'cancel' => 'cancelled', 
        'no_show' => 'no_show',
        'confirm' => 'confirmed');
    
    $new_status = $status_map[$action];
    
    // Update booking status
    $result = $wpdb->update(
        $table_name,
        array('booking_status' => $new_status),
        array('id' => $booking_id),
        array('%s'),
        array('%d')
    );
    
    if ($result !== false) {
        $messages = array(
            'complete' => 'Booking marked as completed.',
            'cancel' => 'Booking cancelled.',
            'no_show' => 'Booking marked as no-show.',
            'confirm' => 'Booking confirmed.');
        
        $notice_type = ($action === 'cancel' || $action === 'no_show') ? 'warning' : 'success';
        
        echo '<div class="notice notice-' . $notice_type . ' is-dismissible">';
        echo '<p>' . esc_html($messages[$action]) . '</p>';
        echo '</div>';
    } else {
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p>Failed to update booking status. Please try again.</p>';
        echo '</div>';
    }
}
