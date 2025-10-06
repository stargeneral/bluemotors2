<?php
/**
 * Blue Motors Southampton SMTP Settings Page
 *
 * @package BlueMotosSouthampton
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display SMTP settings page
 */
function bms_smtp_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Handle form submission
    if (isset($_POST['bms_smtp_save_settings']) && check_admin_referer('bms_smtp_settings_nonce')) {
        // Save SMTP settings
        update_option('bms_smtp_enabled', isset($_POST['bms_smtp_enabled']) ? '1' : '0');
        update_option('bms_smtp_host', sanitize_text_field($_POST['bms_smtp_host']));
        update_option('bms_smtp_port', absint($_POST['bms_smtp_port']));
        update_option('bms_smtp_encryption', sanitize_text_field($_POST['bms_smtp_encryption']));
        update_option('bms_smtp_auth', isset($_POST['bms_smtp_auth']) ? '1' : '0');
        update_option('bms_smtp_username', sanitize_text_field($_POST['bms_smtp_username']));
        
        // Only update password if it's not empty
        if (!empty($_POST['bms_smtp_password'])) {
            update_option('bms_smtp_password', $_POST['bms_smtp_password']);
        }
        
        update_option('bms_smtp_from_email', sanitize_email($_POST['bms_smtp_from_email']));
        update_option('bms_smtp_from_name', sanitize_text_field($_POST['bms_smtp_from_name']));
        
        // Show success message
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('SMTP settings saved successfully.', 'blue-motors-southampton') . '</p></div>';
    }
    
    // Handle test SMTP settings
    if (isset($_POST['bms_smtp_test_settings']) && check_admin_referer('bms_smtp_test_nonce')) {
        // Create test settings array
        $test_settings = array(
            'host' => sanitize_text_field($_POST['bms_smtp_host']),
            'port' => absint($_POST['bms_smtp_port']),
            'encryption' => sanitize_text_field($_POST['bms_smtp_encryption']),
            'auth' => isset($_POST['bms_smtp_auth']) ? true : false,
            'username' => sanitize_text_field($_POST['bms_smtp_username']),
            'password' => $_POST['bms_smtp_password'],
            'from_email' => sanitize_email($_POST['bms_smtp_from_email']),
            'from_name' => sanitize_text_field($_POST['bms_smtp_from_name']),
        );
        
        // Test settings
        $smtp = bms_smtp();
        $result = $smtp->test_settings($test_settings);
        
        // Show result message
        if ($result['success']) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    // Get current settings
    $smtp_enabled = get_option('bms_smtp_enabled', '0');
    $smtp_host = get_option('bms_smtp_host', '');
    $smtp_port = get_option('bms_smtp_port', '587');
    $smtp_encryption = get_option('bms_smtp_encryption', 'tls');
    $smtp_auth = get_option('bms_smtp_auth', '1');
    $smtp_username = get_option('bms_smtp_username', '');
    $smtp_password = get_option('bms_smtp_password', '');
    $smtp_from_email = get_option('bms_smtp_from_email', get_option('admin_email'));
    $smtp_from_name = get_option('bms_smtp_from_name', 'Blue Motors Southampton');
    ?>
    
    <div class="wrap">
        <h1><?php echo esc_html__('Email Settings - Blue Motors Southampton', 'blue-motors-southampton'); ?></h1>
        
        <div class="notice notice-info">
            <p><strong>Email Configuration:</strong> Configure SMTP settings to ensure reliable email delivery for booking confirmations and notifications.</p>
        </div>
        
        <div class="bms-admin-section">
            <form method="post" action="">
                <?php wp_nonce_field('bms_smtp_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable SMTP', 'blue-motors-southampton'); ?></th>
                        <td>
                            <label for="bms_smtp_enabled">
                                <input type="checkbox" name="bms_smtp_enabled" id="bms_smtp_enabled" value="1" <?php checked('1', $smtp_enabled); ?>>
                                <?php echo esc_html__('Enable custom SMTP server for sending emails', 'blue-motors-southampton'); ?>
                            </label>
                            <p class="description"><?php echo esc_html__('When enabled, booking confirmations and notifications will be sent using the SMTP server specified below.', 'blue-motors-southampton'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php echo esc_html__('SMTP Host', 'blue-motors-southampton'); ?></th>
                        <td>
                            <input type="text" name="bms_smtp_host" id="bms_smtp_host" value="<?php echo esc_attr($smtp_host); ?>" class="regular-text">
                            <p class="description"><?php echo esc_html__('The SMTP server hostname (e.g. smtp.gmail.com).', 'blue-motors-southampton'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php echo esc_html__('SMTP Port', 'blue-motors-southampton'); ?></th>
                        <td>
                            <input type="number" name="bms_smtp_port" id="bms_smtp_port" value="<?php echo esc_attr($smtp_port); ?>" class="small-text">
                            <p class="description"><?php echo esc_html__('The SMTP server port (usually 587 for TLS, 465 for SSL).', 'blue-motors-southampton'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php echo esc_html__('Encryption', 'blue-motors-southampton'); ?></th>
                        <td>
                            <select name="bms_smtp_encryption" id="bms_smtp_encryption">
                                <option value="none" <?php selected('none', $smtp_encryption); ?>><?php echo esc_html__('None', 'blue-motors-southampton'); ?></option>
                                <option value="tls" <?php selected('tls', $smtp_encryption); ?>><?php echo esc_html__('TLS', 'blue-motors-southampton'); ?></option>
                                <option value="ssl" <?php selected('ssl', $smtp_encryption); ?>><?php echo esc_html__('SSL', 'blue-motors-southampton'); ?></option>
                            </select>
                            <p class="description"><?php echo esc_html__('The encryption method to use when connecting to the SMTP server.', 'blue-motors-southampton'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php echo esc_html__('Authentication', 'blue-motors-southampton'); ?></th>
                        <td>
                            <label for="bms_smtp_auth">
                                <input type="checkbox" name="bms_smtp_auth" id="bms_smtp_auth" value="1" <?php checked('1', $smtp_auth); ?>>
                                <?php echo esc_html__('SMTP authentication is required', 'blue-motors-southampton'); ?>
                            </label>
                            <p class="description"><?php echo esc_html__('Enable this if your SMTP server requires authentication.', 'blue-motors-southampton'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php echo esc_html__('SMTP Username', 'blue-motors-southampton'); ?></th>
                        <td>
                            <input type="text" name="bms_smtp_username" id="bms_smtp_username" value="<?php echo esc_attr($smtp_username); ?>" class="regular-text">
                            <p class="description"><?php echo esc_html__('The username for SMTP authentication.', 'blue-motors-southampton'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php echo esc_html__('SMTP Password', 'blue-motors-southampton'); ?></th>
                        <td>
                            <input type="password" name="bms_smtp_password" id="bms_smtp_password" value="" class="regular-text">
                            <p class="description">
                                <?php echo esc_html__('The password for SMTP authentication.', 'blue-motors-southampton'); ?>
                                <?php if (!empty($smtp_password)): ?>
                                    <br><?php echo esc_html__('Password is already set. Leave blank to keep the existing password.', 'blue-motors-southampton'); ?>
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php echo esc_html__('From Email', 'blue-motors-southampton'); ?></th>
                        <td>
                            <input type="email" name="bms_smtp_from_email" id="bms_smtp_from_email" value="<?php echo esc_attr($smtp_from_email); ?>" class="regular-text">
                            <p class="description"><?php echo esc_html__('The email address that booking confirmations will be sent from.', 'blue-motors-southampton'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php echo esc_html__('From Name', 'blue-motors-southampton'); ?></th>
                        <td>
                            <input type="text" name="bms_smtp_from_name" id="bms_smtp_from_name" value="<?php echo esc_attr($smtp_from_name); ?>" class="regular-text">
                            <p class="description"><?php echo esc_html__('The name that emails will be sent from.', 'blue-motors-southampton'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="bms_smtp_save_settings" class="button-primary" value="<?php echo esc_attr__('Save Settings', 'blue-motors-southampton'); ?>">
                </p>
            </form>
        </div>
        
        <div class="bms-admin-section">
            <h2><?php echo esc_html__('Test SMTP Settings', 'blue-motors-southampton'); ?></h2>
            <p><?php echo esc_html__('Use this form to test your SMTP settings. A test email will be sent to your "From Email" address.', 'blue-motors-southampton'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('bms_smtp_test_nonce'); ?>
                
                <input type="hidden" name="bms_smtp_host" value="<?php echo esc_attr($smtp_host); ?>">
                <input type="hidden" name="bms_smtp_port" value="<?php echo esc_attr($smtp_port); ?>">
                <input type="hidden" name="bms_smtp_encryption" value="<?php echo esc_attr($smtp_encryption); ?>">
                <input type="hidden" name="bms_smtp_auth" value="<?php echo esc_attr($smtp_auth); ?>">
                <input type="hidden" name="bms_smtp_username" value="<?php echo esc_attr($smtp_username); ?>">
                <input type="hidden" name="bms_smtp_password" value="<?php echo esc_attr($smtp_password); ?>">
                <input type="hidden" name="bms_smtp_from_email" value="<?php echo esc_attr($smtp_from_email); ?>">
                <input type="hidden" name="bms_smtp_from_name" value="<?php echo esc_attr($smtp_from_name); ?>">
                
                <p class="submit">
                    <input type="submit" name="bms_smtp_test_settings" class="button-secondary" value="<?php echo esc_attr__('Send Test Email', 'blue-motors-southampton'); ?>">
                </p>
            </form>
        </div>
        
        <div class="bms-admin-section">
            <h2><?php echo esc_html__('SMTP Provider Information', 'blue-motors-southampton'); ?></h2>
            <p><?php echo esc_html__('Below are common SMTP settings for popular email providers:', 'blue-motors-southampton'); ?></p>
            
            <h3><?php echo esc_html__('Gmail', 'blue-motors-southampton'); ?></h3>
            <ul>
                <li><strong><?php echo esc_html__('SMTP Host:', 'blue-motors-southampton'); ?></strong> smtp.gmail.com</li>
                <li><strong><?php echo esc_html__('SMTP Port:', 'blue-motors-southampton'); ?></strong> 587</li>
                <li><strong><?php echo esc_html__('Encryption:', 'blue-motors-southampton'); ?></strong> TLS</li>
                <li><strong><?php echo esc_html__('Authentication:', 'blue-motors-southampton'); ?></strong> Yes</li>
                <li><strong><?php echo esc_html__('Username:', 'blue-motors-southampton'); ?></strong> Your Gmail address</li>
                <li><strong><?php echo esc_html__('Password:', 'blue-motors-southampton'); ?></strong> App password (requires 2FA to be enabled on Google account)</li>
            </ul>
            
            <h3><?php echo esc_html__('Office 365', 'blue-motors-southampton'); ?></h3>
            <ul>
                <li><strong><?php echo esc_html__('SMTP Host:', 'blue-motors-southampton'); ?></strong> smtp.office365.com</li>
                <li><strong><?php echo esc_html__('SMTP Port:', 'blue-motors-southampton'); ?></strong> 587</li>
                <li><strong><?php echo esc_html__('Encryption:', 'blue-motors-southampton'); ?></strong> TLS</li>
                <li><strong><?php echo esc_html__('Authentication:', 'blue-motors-southampton'); ?></strong> Yes</li>
                <li><strong><?php echo esc_html__('Username:', 'blue-motors-southampton'); ?></strong> Your Outlook email address</li>
                <li><strong><?php echo esc_html__('Password:', 'blue-motors-southampton'); ?></strong> App password (requires 2FA to be enabled)</li>
                <li><strong><?php echo esc_html__('Note:', 'blue-motors-southampton'); ?></strong> Microsoft has disabled basic authentication. You must enable 2FA and use an app password.</li>
            </ul>
            
            <h3><?php echo esc_html__('Hotmail/Outlook.com', 'blue-motors-southampton'); ?></h3>
            <ul>
                <li><strong><?php echo esc_html__('SMTP Host:', 'blue-motors-southampton'); ?></strong> smtp-mail.outlook.com</li>
                <li><strong><?php echo esc_html__('SMTP Port:', 'blue-motors-southampton'); ?></strong> 587</li>
                <li><strong><?php echo esc_html__('Encryption:', 'blue-motors-southampton'); ?></strong> TLS</li>
                <li><strong><?php echo esc_html__('Authentication:', 'blue-motors-southampton'); ?></strong> Yes</li>
                <li><strong><?php echo esc_html__('Username:', 'blue-motors-southampton'); ?></strong> Your full email address</li>
                <li><strong><?php echo esc_html__('Password:', 'blue-motors-southampton'); ?></strong> App password (requires 2FA to be enabled)</li>
                <li><strong><?php echo esc_html__('Note:', 'blue-motors-southampton'); ?></strong> Microsoft has disabled basic authentication. You must enable 2FA and use an app password.</li>
            </ul>
            
            <h3><?php echo esc_html__('Yahoo Mail', 'blue-motors-southampton'); ?></h3>
            <ul>
                <li><strong><?php echo esc_html__('SMTP Host:', 'blue-motors-southampton'); ?></strong> smtp.mail.yahoo.com</li>
                <li><strong><?php echo esc_html__('SMTP Port:', 'blue-motors-southampton'); ?></strong> 587</li>
                <li><strong><?php echo esc_html__('Encryption:', 'blue-motors-southampton'); ?></strong> TLS</li>
                <li><strong><?php echo esc_html__('Authentication:', 'blue-motors-southampton'); ?></strong> Yes</li>
                <li><strong><?php echo esc_html__('Username:', 'blue-motors-southampton'); ?></strong> Your Yahoo email address</li>
                <li><strong><?php echo esc_html__('Password:', 'blue-motors-southampton'); ?></strong> App password (requires 2FA to be enabled on Yahoo account)</li>
            </ul>
        </div>
    </div>
    
    <style>
    .bms-admin-section {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }
    </style>
    <?php
}
