<?php
/**
 * Database Status and Management Page
 * 
 * @package BlueMotosSouthampton
 * @since 1.3.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render database status page
 */
function bms_database_status_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Handle database actions
    if (isset($_POST['bms_database_action']) && check_admin_referer('bms_database_nonce')) {
        $action = sanitize_text_field($_POST['bms_database_action']);
        bms_handle_database_action($action);
    }
    
    // Get database status
    $status = BMS_Database_Manager::get_status();
    $check = BMS_Database_Manager::check_database();
    
    ?>
    <div class="wrap">
        <h1>
            <span class="dashicons dashicons-database" style="font-size: 30px; margin-right: 10px;"></span>
            Database Status
        </h1>
        
        <!-- Database Status Card -->
        <div class="postbox">
            <div class="postbox-header">
                <h2>Database Tables Status</h2>
            </div>
            <div class="inside">
                <div class="bms-status-indicator bms-status-<?php echo esc_attr($status['status']); ?>">
                    <?php
                    $icon = array(
                        'success' => 'yes-alt',
                        'warning' => 'warning',
                        'error' => 'dismiss');
                    ?>
                    <span class="dashicons dashicons-<?php echo $icon[$status['status']]; ?>"></span>
                    <strong><?php echo esc_html($status['message']); ?></strong>
                </div>
                
                <div class="bms-database-details">
                    <h3>Database Information</h3>
                    <table class="form-table">
                        <tr>
                            <th>Tables Exist</th>
                            <td>
                                <?php if ($check['tables_exist']): ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span> Yes
                                <?php else: ?>
                                    <span class="dashicons dashicons-dismiss" style="color: red;"></span> No
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Current Version</th>
                            <td><?php echo esc_html($check['current_version']); ?></td>
                        </tr>
                        <tr>
                            <th>Required Version</th>
                            <td><?php echo esc_html($check['required_version']); ?></td>
                        </tr>
                        <tr>
                            <th>Needs Update</th>
                            <td>
                                <?php if ($check['needs_update']): ?>
                                    <span class="dashicons dashicons-warning" style="color: orange;"></span> Yes
                                <?php else: ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: green;"></span> No
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php if ($status['action'] !== 'none'): ?>
                <div class="bms-database-actions">
                    <h3>Available Actions</h3>
                    <form method="post" action="">
                        <?php wp_nonce_field('bms_database_nonce'); ?>
                        
                        <?php if ($status['action'] === 'create'): ?>
                            <p>Database tables are missing. Click below to create them:</p>
                            <button type="submit" name="bms_database_action" value="create" class="button button-primary">
                                <span class="dashicons dashicons-plus-alt"></span> Create Database Tables
                            </button>
                        <?php elseif ($status['action'] === 'update'): ?>
                            <p>Database tables need to be updated. Click below to update them:</p>
                            <button type="submit" name="bms_database_action" value="update" class="button button-primary">
                                <span class="dashicons dashicons-update"></span> Update Database Tables
                            </button>
                        <?php endif; ?>
                        
                        <button type="submit" name="bms_database_action" value="recreate" class="button button-secondary">
                            <span class="dashicons dashicons-backup"></span> Recreate All Tables
                        </button>
                        
                        <button type="submit" name="bms_database_action" value="sample_data" class="button button-secondary">
                            <span class="dashicons dashicons-admin-users"></span> Create Sample Data
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Table Information Card -->
        <div class="postbox">
            <div class="postbox-header">
                <h2>Table Information</h2>
            </div>
            <div class="inside">
                <?php bms_display_table_info(); ?>
            </div>
        </div>
    </div>
    
    <style>
    .bms-status-indicator {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .bms-status-success {
        background: #d1f5d3;
        border-left: 4px solid #46b450;
    }
    
    .bms-status-warning {
        background: #fff3cd;
        border-left: 4px solid #ffb900;
    }
    
    .bms-status-error {
        background: #f8d7da;
        border-left: 4px solid #dc3232;
    }
    
    .bms-status-indicator .dashicons {
        font-size: 20px;
        vertical-align: middle;
        margin-right: 10px;
    }
    
    .bms-database-actions {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
    }
    
    .bms-database-actions .button {
        margin-right: 10px;
        margin-bottom: 10px;
    }
    
    .bms-database-actions .dashicons {
        font-size: 16px;
        margin-right: 5px;
        vertical-align: text-top;
    }
    </style>
    <?php
}

/**
 * Handle database actions
 */
function bms_handle_database_action($action) {
    switch ($action) {
        case 'create':
        case 'update':
        case 'recreate':
            $result = BMS_Database_Manager::create_tables();
            if ($result) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>Success!</strong> Database tables have been created/updated successfully.</p>';
                echo '</div>';
            } else {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>Error!</strong> Failed to create/update database tables.</p>';
                echo '</div>';
            }
            break;
            
        case 'sample_data':
            $count = BMS_Database_Manager::create_sample_data();
            if ($count > 0) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>Success!</strong> Created ' . $count . ' sample booking records.</p>';
                echo '</div>';
            } else {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p><strong>Info:</strong> Sample data already exists or could not be created.</p>';
                echo '</div>';
            }
            break;
    }
}

/**
 * Display table information
 */
function bms_display_table_info() {
    global $wpdb;
    
    $tables = array(
        'bms_appointments' => 'Main booking appointments',
        'bms_booking_logs' => 'Booking activity logs',
        'bms_services' => 'Service definitions');
    
    echo '<div class="bms-table-info">';
    
    foreach ($tables as $table_suffix => $description) {
        $table_name = $wpdb->prefix . $table_suffix;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        echo '<div class="bms-table-status">';
        echo '<h4>' . $table_suffix . '</h4>';
        echo '<p>' . $description . '</p>';
        
        if ($table_exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            echo '<p><span class="dashicons dashicons-yes-alt" style="color: green;"></span> ';
            echo 'Table exists with ' . $count . ' records</p>';
            
            // Show recent records for appointments table
            if ($table_suffix === 'bms_appointments' && $count > 0) {
                $recent = $wpdb->get_results("SELECT booking_reference, booking_date, booking_time, customer_name, service_type FROM $table_name ORDER BY created_at DESC LIMIT 3");
                
                echo '<div class="bms-recent-bookings">';
                echo '<strong>Recent Bookings:</strong>';
                echo '<ul>';
                foreach ($recent as $booking) {
                    echo '<li>' . esc_html($booking->booking_reference) . ' - ';
                    echo esc_html($booking->customer_name) . ' - ';
                    echo esc_html($booking->service_type) . ' - ';
                    echo esc_html($booking->booking_date) . ' ' . esc_html($booking->booking_time);
                    echo '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        } else {
            echo '<p><span class="dashicons dashicons-dismiss" style="color: red;"></span> Table does not exist</p>';
        }
        
        echo '</div>';
        echo '<hr>';
    }
    
    echo '</div>';
}
