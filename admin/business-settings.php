<?php
/**
 * Business Settings Page for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display business settings page
 */
function bms_business_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Auto-migrate constants if not done yet
    if (!BMS_Settings_Migrator::is_migrated()) {
        BMS_Settings_Migrator::migrate_constants_to_options();
    }
    
    // Handle form submission
    if (isset($_POST['bms_save_business_settings']) && check_admin_referer('bms_business_settings_nonce')) {
        // Save business information
        update_option('bms_business_name', sanitize_text_field($_POST['business_name']));
        update_option('bms_business_address', sanitize_textarea_field($_POST['business_address']));
        update_option('bms_business_phone', sanitize_text_field($_POST['business_phone']));
        update_option('bms_business_email', sanitize_email($_POST['business_email']));
        update_option('bms_business_postcode', sanitize_text_field($_POST['business_postcode']));
        update_option('bms_business_latitude', floatval($_POST['business_latitude']));
        update_option('bms_business_longitude', floatval($_POST['business_longitude']));
        
        // Save business hours
        update_option('bms_hours_weekday_open', sanitize_text_field($_POST['hours_weekday_open']));
        update_option('bms_hours_weekday_close', sanitize_text_field($_POST['hours_weekday_close']));
        update_option('bms_hours_saturday_open', sanitize_text_field($_POST['hours_saturday_open']));
        update_option('bms_hours_saturday_close', sanitize_text_field($_POST['hours_saturday_close']));
        update_option('bms_hours_sunday_open', sanitize_text_field($_POST['hours_sunday_open']));
        update_option('bms_hours_sunday_close', sanitize_text_field($_POST['hours_sunday_close']));
        
        // Save booking configuration
        update_option('bms_booking_min_days', absint($_POST['booking_min_days']));
        update_option('bms_booking_max_days', absint($_POST['booking_max_days']));
        update_option('bms_booking_slot_duration', absint($_POST['booking_slot_duration']));
        update_option('bms_booking_reference_prefix', sanitize_text_field($_POST['booking_reference_prefix']));
        
        // Show success message
        echo '<div class="notice notice-success is-dismissible"><p><strong>Business settings saved successfully!</strong></p></div>';
    }
    
    // Get current settings
    $business_info = BMS_Settings_Migrator::get_business_info();
    $business_hours = BMS_Settings_Migrator::get_business_hours();
    $booking_settings = BMS_Settings_Migrator::get_booking_settings();
    $migration_info = BMS_Settings_Migrator::get_migration_info();
    ?>
    
    <div class="wrap">
        <h1>
            <span class="dashicons dashicons-building" style="font-size: 30px; margin-right: 10px;"></span>
            Business Settings
        </h1>
        
        <?php if ($migration_info['migrated']): ?>
            <div class="notice notice-info">
                <p><strong>Settings Migration Complete!</strong> Your hardcoded settings have been migrated to the database. 
                You can now configure everything through this interface.</p>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <?php wp_nonce_field('bms_business_settings_nonce'); ?>
            
            <!-- Business Information -->
            <div class="bms-admin-card">
                <h2><span class="dashicons dashicons-store"></span> Business Information</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="business_name">Business Name</label>
                        </th>
                        <td>
                            <input type="text" id="business_name" name="business_name" 
                                   value="<?php echo esc_attr($business_info['name']); ?>" 
                                   class="regular-text" required>
                            <p class="description">The name of your garage business</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="business_address">Address</label>
                        </th>
                        <td>
                            <textarea id="business_address" name="business_address" 
                                      rows="3" class="large-text" required><?php echo esc_textarea($business_info['address']); ?></textarea>
                            <p class="description">Full business address including postcode</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="business_phone">Phone Number</label>
                        </th>
                        <td>
                            <input type="tel" id="business_phone" name="business_phone" 
                                   value="<?php echo esc_attr($business_info['phone']); ?>" 
                                   class="regular-text" required>
                            <p class="description">Main contact phone number</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="business_email">Email Address</label>
                        </th>
                        <td>
                            <input type="email" id="business_email" name="business_email" 
                                   value="<?php echo esc_attr($business_info['email']); ?>" 
                                   class="regular-text" required>
                            <p class="description">Main contact email address</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="business_postcode">Postcode</label>
                        </th>
                        <td>
                            <input type="text" id="business_postcode" name="business_postcode" 
                                   value="<?php echo esc_attr($business_info['postcode']); ?>" 
                                   class="small-text">
                            <p class="description">For location services and directions</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Location Coordinates</th>
                        <td>
                            <input type="number" step="0.000001" name="business_latitude" 
                                   value="<?php echo esc_attr($business_info['latitude']); ?>" 
                                   class="small-text" placeholder="Latitude">
                            <input type="number" step="0.000001" name="business_longitude" 
                                   value="<?php echo esc_attr($business_info['longitude']); ?>" 
                                   class="small-text" placeholder="Longitude" style="margin-left: 10px;">
                            <p class="description">GPS coordinates for mapping services (optional)</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Business Hours -->
            <div class="bms-admin-card">
                <h2><span class="dashicons dashicons-clock"></span> Business Hours</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Monday - Friday</th>
                        <td>
                            <input type="time" name="hours_weekday_open" 
                                   value="<?php echo esc_attr($business_hours['weekday_open']); ?>" 
                                   class="small-text">
                            <span style="margin: 0 10px;">to</span>
                            <input type="time" name="hours_weekday_close" 
                                   value="<?php echo esc_attr($business_hours['weekday_close']); ?>" 
                                   class="small-text">
                            <p class="description">Weekday operating hours</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Saturday</th>
                        <td>
                            <input type="time" name="hours_saturday_open" 
                                   value="<?php echo esc_attr($business_hours['saturday_open']); ?>" 
                                   class="small-text">
                            <span style="margin: 0 10px;">to</span>
                            <input type="time" name="hours_saturday_close" 
                                   value="<?php echo esc_attr($business_hours['saturday_close']); ?>" 
                                   class="small-text">
                            <p class="description">Saturday operating hours</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Sunday</th>
                        <td>
                            <select name="hours_sunday_open" class="regular-text">
                                <option value="closed" <?php selected($business_hours['sunday_open'], 'closed'); ?>>Closed</option>
                                <option value="08:00" <?php selected($business_hours['sunday_open'], '08:00'); ?>>Open</option>
                            </select>
                            <?php if ($business_hours['sunday_open'] !== 'closed'): ?>
                                <span style="margin: 0 10px;">to</span>
                                <input type="time" name="hours_sunday_close" 
                                       value="<?php echo esc_attr($business_hours['sunday_close']); ?>" 
                                       class="small-text">
                            <?php else: ?>
                                <input type="hidden" name="hours_sunday_close" value="closed">
                            <?php endif; ?>
                            <p class="description">Sunday operating hours</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Booking Configuration -->
            <div class="bms-admin-card">
                <h2><span class="dashicons dashicons-calendar-alt"></span> Booking Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="booking_min_days">Minimum Days Ahead</label>
                        </th>
                        <td>
                            <input type="number" id="booking_min_days" name="booking_min_days" 
                                   value="<?php echo esc_attr($booking_settings['min_days']); ?>" 
                                   min="0" max="7" class="small-text">
                            <p class="description">Minimum days in advance customers can book</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="booking_max_days">Maximum Days Ahead</label>
                        </th>
                        <td>
                            <input type="number" id="booking_max_days" name="booking_max_days" 
                                   value="<?php echo esc_attr($booking_settings['max_days']); ?>" 
                                   min="7" max="90" class="small-text">
                            <p class="description">Maximum days in advance customers can book</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="booking_slot_duration">Time Slot Duration</label>
                        </th>
                        <td>
                            <select id="booking_slot_duration" name="booking_slot_duration" class="regular-text">
                                <option value="15" <?php selected($booking_settings['slot_duration'], 15); ?>>15 minutes</option>
                                <option value="30" <?php selected($booking_settings['slot_duration'], 30); ?>>30 minutes</option>
                                <option value="60" <?php selected($booking_settings['slot_duration'], 60); ?>>1 hour</option>
                            </select>
                            <p class="description">Duration of each booking time slot</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="booking_reference_prefix">Reference Prefix</label>
                        </th>
                        <td>
                            <input type="text" id="booking_reference_prefix" name="booking_reference_prefix" 
                                   value="<?php echo esc_attr($booking_settings['reference_prefix']); ?>" 
                                   class="small-text" maxlength="5">
                            <p class="description">Prefix for booking reference numbers (e.g., WEB-123456)</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <p class="submit">
                <input type="submit" name="bms_save_business_settings" class="button-primary" 
                       value="Save Business Settings">
                <a href="?page=bms-settings" class="button" style="margin-left: 10px;">Back to Main Settings</a>
            </p>
        </form>
        
        <!-- Migration Info -->
        <div class="bms-admin-card">
            <h3>Migration Information</h3>
            <table class="widefat">
                <tr>
                    <td><strong>Settings Migrated:</strong></td>
                    <td><?php echo $migration_info['migrated'] ? 'Yes' : 'No'; ?></td>
                </tr>
                <?php if ($migration_info['migrated']): ?>
                <tr>
                    <td><strong>Migration Date:</strong></td>
                    <td><?php echo esc_html($migration_info['migration_date']); ?></td>
                </tr>
                <tr>
                    <td><strong>Settings Migrated:</strong></td>
                    <td><?php echo esc_html($migration_info['migration_count']); ?> of <?php echo esc_html($migration_info['total_constants']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <style>
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
    
    .bms-admin-card h2 .dashicons {
        margin-right: 8px;
        color: #0073aa;
    }
    
    .form-table th {
        width: 200px;
    }
    
    .small-text {
        width: 80px;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Show/hide Sunday hours based on selection
        $('select[name="hours_sunday_open"]').change(function() {
            var isClosed = $(this).val() === 'closed';
            var timeInput = $('input[name="hours_sunday_close"]');
            
            if (isClosed) {
                timeInput.hide().val('closed');
                $(this).next('span').hide();
            } else {
                timeInput.show().val('16:00');
                $(this).next('span').show();
            }
        });
    });
    </script>
    <?php
}
