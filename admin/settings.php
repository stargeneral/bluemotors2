<?php
/**
 * Plugin Settings Page for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['bms_settings_nonce'], 'bms_settings')) {
    // Process form data (placeholder for future implementation)
    echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
}
?>
<div class="wrap">
    <h1>Plugin Settings</h1>
    
    <div class="notice notice-info">
        <p><strong>Configuration Notice</strong></p>
        <p>Most settings are currently configured in code files. This interface will be enhanced in future updates.</p>
    </div>
    
    <form method="post" action="">
        <?php wp_nonce_field('bms_settings', 'bms_settings_nonce'); ?>
        
        <div class="card" style="max-width: 800px;">
            <h2>Business Information</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Business Name</th>
                    <td>
                        <input type="text" class="regular-text" value="<?php echo esc_attr(BM_LOCATION_NAME); ?>" readonly>
                        <p class="description">Currently set in config/constants.php</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Address</th>
                    <td>
                        <textarea class="large-text" rows="3" readonly><?php echo esc_textarea(BM_LOCATION_ADDRESS); ?></textarea>
                        <p class="description">Currently set in config/constants.php</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Phone</th>
                    <td>
                        <input type="text" class="regular-text" value="<?php echo esc_attr(BM_LOCATION_PHONE); ?>" readonly>
                        <p class="description">Currently set in config/constants.php</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Email</th>
                    <td>
                        <input type="email" class="regular-text" value="<?php echo esc_attr(BM_LOCATION_EMAIL); ?>" readonly>
                        <p class="description">Currently set in config/constants.php</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Business Hours</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Weekdays</th>
                    <td>
                        <input type="text" class="regular-text" value="<?php echo esc_attr(BM_HOURS_WEEKDAY_OPEN . ' - ' . BM_HOURS_WEEKDAY_CLOSE); ?>" readonly>
                        <p class="description">Monday to Friday</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Saturday</th>
                    <td>
                        <input type="text" class="regular-text" value="<?php echo esc_attr(BM_HOURS_SATURDAY_OPEN . ' - ' . BM_HOURS_SATURDAY_CLOSE); ?>" readonly>
                        <p class="description">Saturday hours</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Sunday</th>
                    <td>
                        <input type="text" class="regular-text" value="Closed" readonly>
                        <p class="description">Currently closed on Sundays</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>API Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">DVLA API</th>
                    <td>
                        <input type="text" class="regular-text" value="<?php echo BM_DVLA_API_KEY ? '••••••••••••' : 'Not configured'; ?>" readonly>
                        <p class="description">
                            <?php if (BM_DVLA_API_KEY): ?>
                                <span style="color: green;">✓ API key configured</span>
                            <?php else: ?>
                                <span style="color: orange;">⚠ Using mock data - Add API key in config/constants.php</span>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Payment Gateway</th>
                    <td>
                        <input type="text" class="regular-text" value="Stripe (Not configured)" readonly>
                        <p class="description">Payment integration will be configured in next phase</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Email Settings</th>
                    <td>
                        <input type="text" class="regular-text" value="WordPress Default" readonly>
                        <p class="description">Using WordPress wp_mail() function</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Booking Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Advance Booking</th>
                    <td>
                        <input type="text" class="regular-text" value="<?php echo BM_BOOKING_MIN_DAYS; ?> to <?php echo BM_BOOKING_MAX_DAYS; ?> days" readonly>
                        <p class="description">Minimum and maximum days in advance for bookings</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Time Slots</th>
                    <td>
                        <input type="text" class="regular-text" value="<?php echo BM_BOOKING_SLOT_DURATION; ?> minutes" readonly>
                        <p class="description">Duration of each booking time slot</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Reference Format</th>
                    <td>
                        <input type="text" class="regular-text" value="<?php echo BM_BOOKING_REFERENCE_PREFIX; ?>-XXXXXX" readonly>
                        <p class="description">Format for booking reference numbers</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>Configuration Files</h2>
            <p>Settings are currently managed in these files:</p>
            <ul>
                <li><code>config/constants.php</code> - Main configuration constants</li>
                <li><code>config/services.php</code> - Service definitions</li>
                <li><code>config/pricing-matrix.php</code> - Pricing calculations</li>
            </ul>
            
            <div class="notice notice-warning inline">
                <p><strong>Future Updates:</strong> A user-friendly settings interface will be added to manage these configurations directly from the WordPress admin.</p>
            </div>
        </div>
        
        <!-- Placeholder submit button for future functionality -->
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" disabled>
            <span class="description" style="margin-left: 10px;">Settings interface will be functional in future update</span>
        </p>
    </form>
    
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>Quick Actions</h2>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="?page=bms-dashboard" class="button">View Dashboard</a>
            <a href="?page=bms-bookings" class="button">Manage Bookings</a>
            <a href="?page=bms-services" class="button">View Services</a>
            <a href="<?php echo site_url(); ?>/?bms_test_form=1" class="button" target="_blank">Test Booking Form</a>
        </div>
    </div>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.notice.inline {
    margin: 20px 0 0 0;
    padding: 10px;
}
</style>
