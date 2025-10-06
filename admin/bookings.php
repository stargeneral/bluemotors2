<?php
/**
 * Bookings Management for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render bookings management page
 */
function bms_admin_bookings_page() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'bms_appointments';
    
    // Handle actions
    if (isset($_GET['action']) && isset($_GET['booking_id'])) {
        $booking_id = intval($_GET['booking_id']);
        $action = sanitize_text_field($_GET['action']);
        
        switch ($action) {
            case 'complete':
                $wpdb->update(
                    $table_name,
                    array('booking_status' => 'completed'),
                    array('id' => $booking_id),
                    array('%s'),
                    array('%d')
                );
                echo '<div class="notice notice-success"><p>Booking marked as completed.</p></div>';
                break;
                
            case 'cancel':
                $wpdb->update(
                    $table_name,
                    array('booking_status' => 'cancelled'),
                    array('id' => $booking_id),
                    array('%s'),
                    array('%d')
                );
                echo '<div class="notice notice-warning"><p>Booking cancelled.</p></div>';
                break;
        }
    }
    
    // Get filter parameters
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $date_filter = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
    
    // Build query
    $where_conditions = array('1=1');
    $where_values = array();
    
    if ($status_filter) {
        $where_conditions[] = 'booking_status = %s';
        $where_values[] = $status_filter;
    }
    
    if ($date_filter) {
        if ($date_filter === 'today') {
            $where_conditions[] = 'appointment_date = %s';
            $where_values[] = date('Y-m-d');
        } elseif ($date_filter === 'week') {
            $where_conditions[] = 'appointment_date BETWEEN %s AND %s';
            $where_values[] = date('Y-m-d', strtotime('monday this week'));
            $where_values[] = date('Y-m-d', strtotime('sunday this week'));
        }
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    if ($where_values) {
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where_clause ORDER BY appointment_date DESC, appointment_time DESC",
            ...$where_values);
    } else {
        $query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY appointment_date DESC, appointment_time DESC";
    }
    
    $bookings = $wpdb->get_results($query);
    ?>
    
    <div class="wrap">
        <h1>Manage Bookings</h1>
        
        <!-- Filters -->
        <div class="bms-filters" style="background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin: 20px 0;">
            <form method="get" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="page" value="bms-bookings">
                
                <label>
                    Status:
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="confirmed" <?php selected($status_filter, 'confirmed'); ?>>Confirmed</option>
                        <option value="completed" <?php selected($status_filter, 'completed'); ?>>Completed</option>
                        <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>>Cancelled</option>
                        <option value="no-show" <?php selected($status_filter, 'no-show'); ?>>No-Show</option>
                    </select>
                </label>
                
                <label>
                    Date:
                    <select name="date">
                        <option value="">All Dates</option>
                        <option value="today" <?php selected($date_filter, 'today'); ?>>Today</option>
                        <option value="week" <?php selected($date_filter, 'week'); ?>>This Week</option>
                    </select>
                </label>
                
                <input type="submit" class="button" value="Filter">
                <a href="?page=bms-bookings" class="button">Clear Filters</a>
            </form>
        </div>
        
        <?php if ($bookings): ?>
        
        <!-- Bookings Table -->
        <div class="bms-bookings-table" style="background: #fff; border: 1px solid #ddd; border-radius: 8px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 100px;">Reference</th>
                        <th>Date & Time</th>
                        <th>Service</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Price</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><code><?php echo esc_html($booking->booking_reference); ?></code></td>
                        <td>
                            <strong><?php echo date('M j, Y', strtotime($booking->appointment_date)); ?></strong><br>
                            <small><?php echo date('g:i A', strtotime($booking->appointment_time)); ?></small>
                        </td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $booking->service_type)); ?></td>
                        <td>
                            <strong><?php echo esc_html($booking->customer_name); ?></strong><br>
                            <small>
                                <?php echo esc_html($booking->customer_email); ?><br>
                                <?php echo esc_html($booking->customer_phone); ?>
                            </small>
                        </td>
                        <td>
                            <strong><?php echo esc_html($booking->vehicle_make . ' ' . $booking->vehicle_model); ?></strong><br>
                            <small>
                                Reg: <?php echo esc_html($booking->vehicle_reg); ?><br>
                                Engine: <?php echo esc_html($booking->vehicle_engine_size); ?>cc
                            </small>
                        </td>
                        <td><strong>£<?php echo number_format($booking->calculated_price, 2); ?></strong></td>
                        <td>
                            <span class="bms-payment-status bms-payment-<?php echo $booking->payment_status; ?>"
                                  style="padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;
                                         background: <?php echo $booking->payment_status === 'paid' ? '#d4edda' : '#fff3cd'; ?>; 
                                         color: <?php echo $booking->payment_status === 'paid' ? '#155724' : '#856404'; ?>;">
                                <?php echo ucfirst($booking->payment_status); ?>
                            </span>
                        </td>
                        <td>
                            <span class="bms-booking-status bms-status-<?php echo $booking->booking_status; ?>"
                                  style="padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;
                                         background: <?php 
                                         switch($booking->booking_status) {
                                             case 'confirmed': echo '#d1ecf1'; break;
                                             case 'completed': echo '#d4edda'; break;
                                             case 'cancelled': echo '#f8d7da'; break;
                                             default: echo '#e2e3e5';
                                         } ?>; 
                                         color: <?php 
                                         switch($booking->booking_status) {
                                             case 'confirmed': echo '#0c5460'; break;
                                             case 'completed': echo '#155724'; break;
                                             case 'cancelled': echo '#721c24'; break;
                                             default: echo '#383d41';
                                         } ?>;">
                                <?php echo ucfirst($booking->booking_status); ?>
                            </span>
                        </td>
                        <td>
                            <div class="bms-actions" style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <?php if ($booking->booking_status === 'confirmed'): ?>
                                    <a href="?page=bms-bookings&action=complete&booking_id=<?php echo $booking->id; ?>" 
                                       class="button button-small button-primary"
                                       onclick="return confirm('Mark this booking as completed?')">Complete</a>
                                    <a href="?page=bms-bookings&action=cancel&booking_id=<?php echo $booking->id; ?>" 
                                       class="button button-small"
                                       onclick="return confirm('Cancel this booking?')">Cancel</a>
                                <?php endif; ?>
                                
                                <button class="button button-small bms-view-details" 
                                        data-booking-id="<?php echo $booking->id; ?>">Details</button>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Hidden details row -->
                    <tr class="bms-booking-details" id="details-<?php echo $booking->id; ?>" style="display: none;">
                        <td colspan="9" style="background: #f9f9f9; padding: 15px;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <div>
                                    <h4>Booking Information</h4>
                                    <p><strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($booking->created_at)); ?></p>
                                    <p><strong>Reference:</strong> <?php echo esc_html($booking->booking_reference); ?></p>
                                </div>
                                
                                <div>
                                    <h4>Vehicle Details</h4>
                                    <p><strong>Registration:</strong> <?php echo esc_html($booking->vehicle_reg); ?></p>
                                    <p><strong>Make/Model:</strong> <?php echo esc_html($booking->vehicle_make . ' ' . $booking->vehicle_model); ?></p>
                                    <p><strong>Engine:</strong> <?php echo esc_html($booking->vehicle_engine_size); ?>cc</p>
                                    <p><strong>Fuel:</strong> <?php echo esc_html(ucfirst($booking->vehicle_fuel_type)); ?></p>
                                </div>
                                
                                <div>
                                    <h4>Contact Customer</h4>
                                    <p><a href="mailto:<?php echo esc_attr($booking->customer_email); ?>">📧 Send Email</a></p>
                                    <p><a href="tel:<?php echo esc_attr($booking->customer_phone); ?>">📞 Call Phone</a></p>
                                </div>
                                
                                <?php if ($booking->notes): ?>
                                <div>
                                    <h4>Notes</h4>
                                    <p><?php echo esc_html($booking->notes); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php else: ?>
        <div class="bms-no-bookings" style="background: #fff; padding: 40px; border: 1px solid #ddd; border-radius: 8px; text-align: center;">
            <h3>No bookings found</h3>
            <p>No bookings match your current filters.</p>
            <a href="?page=bms-bookings" class="button">View All Bookings</a>
        </div>
        <?php endif; ?>
        
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Toggle booking details
        $('.bms-view-details').on('click', function(e) {
            e.preventDefault();
            var bookingId = $(this).data('booking-id');
            var detailsRow = $('#details-' + bookingId);
            
            if (detailsRow.is(':visible')) {
                detailsRow.hide();
                $(this).text('Details');
            } else {
                detailsRow.show();
                $(this).text('Hide');
            }
        });
    });
    </script>
    
    <?php
}

// Execute the bookings page function when this file is required
bms_admin_bookings_page();
