<?php
/**
 * Admin Dashboard for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render admin dashboard page
 */
function bms_admin_dashboard_page() {
    global $wpdb;
    
    // Get today's bookings
    $today = date('Y-m-d');
    $table_name = $wpdb->prefix . 'bms_appointments';
    
    $today_bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE booking_date = %s ORDER BY booking_time ASC",
        $today));
    
    // Get this week's stats
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $week_end = date('Y-m-d', strtotime('sunday this week'));
    
    $week_stats = $wpdb->get_row($wpdb->prepare(
        "SELECT 
            COUNT(*) as total_bookings,
            SUM(calculated_price) as total_revenue,
            COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as paid_bookings
         FROM $table_name 
         WHERE booking_date BETWEEN %s AND %s",
        $week_start, $week_end));
    
    // Get recent bookings
    $recent_bookings = $wpdb->get_results(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 10");
    ?>
    
    <div class="wrap">
        <h1>Blue Motors Southampton Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="bms-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            
            <div class="bms-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin: 0 0 10px 0; color: #23282d;">Today's Bookings</h3>
                <div style="font-size: 2em; font-weight: bold; color: #0073aa;"><?php echo count($today_bookings); ?></div>
                <small style="color: #666;">Scheduled for today</small>
            </div>
            
            <div class="bms-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin: 0 0 10px 0; color: #23282d;">This Week</h3>
                <div style="font-size: 2em; font-weight: bold; color: #00a32a;"><?php echo $week_stats->total_bookings ?: 0; ?></div>
                <small style="color: #666;">Total bookings</small>
            </div>
            
            <div class="bms-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin: 0 0 10px 0; color: #23282d;">Weekly Revenue</h3>
                <div style="font-size: 2em; font-weight: bold; color: #d63638;">£<?php echo number_format($week_stats->total_revenue ?: 0, 2); ?></div>
                <small style="color: #666;">Paid: <?php echo $week_stats->paid_bookings ?: 0; ?> bookings</small>
            </div>
            
            <div class="bms-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin: 0 0 10px 0; color: #23282d;">Location</h3>
                <div style="font-size: 1.2em; font-weight: bold; color: #3c434a;"><?php echo BM_LOCATION_NAME; ?></div>
                <small style="color: #666;"><?php echo BM_LOCATION_ADDRESS; ?></small>
            </div>
            
        </div>
        
        <!-- Today's Bookings -->
        <?php if ($today_bookings): ?>
        <div class="bms-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin: 20px 0;">
            <h2>Today's Schedule (<?php echo date('l, F j, Y'); ?>)</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Service</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($today_bookings as $booking): ?>
                    <tr>
                        <td><strong><?php echo date('g:i A', strtotime($booking->appointment_time)); ?></strong></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $booking->service_type)); ?></td>
                        <td>
                            <?php echo esc_html($booking->customer_name); ?><br>
                            <small><?php echo esc_html($booking->customer_phone); ?></small>
                        </td>
                        <td>
                            <?php echo esc_html($booking->vehicle_make . ' ' . $booking->vehicle_model); ?><br>
                            <small><?php echo esc_html($booking->vehicle_reg); ?></small>
                        </td>
                        <td>£<?php echo number_format($booking->calculated_price, 2); ?></td>
                        <td>
                            <span class="bms-status bms-status-<?php echo $booking->booking_status; ?>" 
                                  style="padding: 4px 8px; border-radius: 4px; font-size: 12px; 
                                         background: <?php echo $booking->booking_status === 'confirmed' ? '#d1ecf1' : '#f8d7da'; ?>; 
                                         color: <?php echo $booking->booking_status === 'confirmed' ? '#0c5460' : '#721c24'; ?>;">
                                <?php echo ucfirst($booking->booking_status); ?>
                            </span>
                        </td>
                        <td>
                            <a href="?page=bms-bookings&booking_id=<?php echo $booking->id; ?>" class="button button-small">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="bms-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin: 20px 0;">
            <h2>Today's Schedule</h2>
            <p>No bookings scheduled for today.</p>
        </div>
        <?php endif; ?>
        
        <!-- Recent Bookings -->
        <div class="bms-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin: 20px 0;">
            <h2>Recent Bookings</h2>
            <?php if ($recent_bookings): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Service</th>
                        <th>Customer</th>
                        <th>Price</th>
                        <th>Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_bookings as $booking): ?>
                    <tr>
                        <td><code><?php echo esc_html($booking->booking_reference); ?></code></td>
                        <td><?php echo date('M j, Y', strtotime($booking->booking_date)); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $booking->service_type)); ?></td>
                        <td><?php echo esc_html($booking->customer_name); ?></td>
                        <td>£<?php echo number_format($booking->calculated_price, 2); ?></td>
                        <td>
                            <span class="bms-payment-status bms-payment-<?php echo $booking->payment_status; ?>"
                                  style="padding: 2px 6px; border-radius: 3px; font-size: 11px; 
                                         background: <?php echo $booking->payment_status === 'paid' ? '#d4edda' : '#fff3cd'; ?>; 
                                         color: <?php echo $booking->payment_status === 'paid' ? '#155724' : '#856404'; ?>;">
                                <?php echo ucfirst($booking->payment_status); ?>
                            </span>
                        </td>
                        <td><?php echo ucfirst($booking->booking_status); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><a href="?page=bms-bookings" class="button">View All Bookings</a></p>
            <?php else: ?>
            <p>No bookings found.</p>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="bms-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin: 20px 0;">
            <h2>Quick Actions</h2>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="?page=bms-bookings" class="button button-primary">Manage Bookings</a>
                <a href="?page=bms-services" class="button">Service Settings</a>
                <a href="?page=bms-settings" class="button">Plugin Settings</a>
                <a href="<?php echo site_url(); ?>/?bms_test_form=1" class="button" target="_blank">Test Booking Form</a>
            </div>
        </div>
        
    </div>
    
    <style>
    .bms-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }
    
    .bms-stat-card {
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        text-align: center;
    }
    
    .bms-section {
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    .bms-section h2 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    </style>
    
    <?php
}

// Execute the dashboard page function when this file is required
bms_admin_dashboard_page();
