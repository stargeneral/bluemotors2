<?php
/**
 * Payment Settings Page for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display payment settings page
 */
function bms_payment_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Auto-migrate constants if not done yet
    if (!BMS_Settings_Migrator::is_migrated()) {
        BMS_Settings_Migrator::migrate_constants_to_options();
    }
    
    // Handle form submission
    if (isset($_POST['bms_save_payment_settings']) && check_admin_referer('bms_payment_settings_nonce')) {
        // Save payment configuration
        update_option('bms_payment_required', isset($_POST['payment_required']) ? '1' : '0');
        update_option('bms_payment_currency', sanitize_text_field($_POST['payment_currency']));
        update_option('bms_vat_rate', floatval($_POST['vat_rate']) / 100); // Convert percentage to decimal
        update_option('bms_payment_test_mode', isset($_POST['payment_test_mode']) ? '1' : '0');
        
        // Save Stripe configuration
        update_option('bms_stripe_enabled', isset($_POST['stripe_enabled']) ? '1' : '0');
        update_option('bms_stripe_test_public_key', sanitize_text_field($_POST['stripe_test_public_key']));
        update_option('bms_stripe_test_secret_key', sanitize_text_field($_POST['stripe_test_secret_key']));
        update_option('bms_stripe_live_public_key', sanitize_text_field($_POST['stripe_live_public_key']));
        update_option('bms_stripe_live_secret_key', sanitize_text_field($_POST['stripe_live_secret_key']));
        
        // For backward compatibility, update the old constant-style options
        $test_mode = get_option('bms_payment_test_mode', '1');
        if ($test_mode) {
            update_option('bms_stripe_public_key', get_option('bms_stripe_test_public_key', ''));
            update_option('bms_stripe_secret_key', get_option('bms_stripe_test_secret_key', ''));
        } else {
            update_option('bms_stripe_public_key', get_option('bms_stripe_live_public_key', ''));
            update_option('bms_stripe_secret_key', get_option('bms_stripe_live_secret_key', ''));
        }
        
        // Show success message
        echo '<div class="notice notice-success is-dismissible"><p><strong>Payment settings saved successfully!</strong></p></div>';
    }
    
    // Handle test connection
    if (isset($_POST['bms_test_stripe_connection']) && check_admin_referer('bms_payment_settings_nonce')) {
        $test_result = bms_test_stripe_connection();
        if ($test_result['success']) {
            echo '<div class="notice notice-success is-dismissible"><p><strong>Stripe connection successful!</strong> ' . esc_html($test_result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p><strong>Stripe connection failed:</strong> ' . esc_html($test_result['message']) . '</p></div>';
        }
    }
    
    // Get current settings
    $payment_settings = BMS_Settings_Migrator::get_payment_settings();
    $test_mode = get_option('bms_payment_test_mode', '1');
    ?>
    
    <div class="wrap">
        <h1>
            <span class="dashicons dashicons-money-alt" style="font-size: 30px; margin-right: 10px;"></span>
            Payment Gateway Settings
        </h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('bms_payment_settings_nonce'); ?>
            
            <!-- Payment Configuration -->
            <div class="bms-admin-card">
                <h2><span class="dashicons dashicons-admin-settings"></span> Payment Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Payment Required</th>
                        <td>
                            <label>
                                <input type="checkbox" name="payment_required" value="1" 
                                       <?php checked(get_option('bms_payment_required', '1'), '1'); ?>>
                                Require payment at time of booking
                            </label>
                            <p class="description">When enabled, customers must pay when booking. When disabled, they can pay on arrival.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="payment_currency">Currency</label>
                        </th>
                        <td>
                            <select id="payment_currency" name="payment_currency" class="regular-text">
                                <option value="GBP" <?php selected(get_option('bms_payment_currency', 'GBP'), 'GBP'); ?>>British Pound (£)</option>
                                <option value="EUR" <?php selected(get_option('bms_payment_currency', 'GBP'), 'EUR'); ?>>Euro (€)</option>
                                <option value="USD" <?php selected(get_option('bms_payment_currency', 'GBP'), 'USD'); ?>>US Dollar ($)</option>
                            </select>
                            <p class="description">Currency for all payments and pricing</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="vat_rate">VAT Rate</label>
                        </th>
                        <td>
                            <input type="number" id="vat_rate" name="vat_rate" 
                                   value="<?php echo esc_attr(get_option('bms_vat_rate', 0.20) * 100); ?>" 
                                   min="0" max="100" step="0.1" class="small-text">
                            <span style="margin-left: 5px;">%</span>
                            <p class="description">VAT/Tax rate (UK VAT is 20%)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Test Mode</th>
                        <td>
                            <label>
                                <input type="checkbox" name="payment_test_mode" value="1" 
                                       <?php checked($test_mode, '1'); ?>>
                                Enable test mode (use test API keys)
                            </label>
                            <p class="description">
                                <strong style="color: <?php echo $test_mode ? 'orange' : 'green'; ?>;">
                                    Currently in <?php echo $test_mode ? 'TEST' : 'LIVE'; ?> mode
                                </strong>
                                - Always test with test mode first!
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Stripe Configuration -->
            <div class="bms-admin-card">
                <h2><span class="dashicons dashicons-admin-network"></span> Stripe Payment Gateway</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Stripe</th>
                        <td>
                            <label>
                                <input type="checkbox" name="stripe_enabled" value="1" 
                                       <?php checked(get_option('bms_stripe_enabled', '1'), '1'); ?>>
                                Enable Stripe payment processing
                            </label>
                            <p class="description">Stripe is the recommended payment gateway for secure card processing</p>
                        </td>
                    </tr>
                </table>
                
                <h3 style="color: #f0ad4e; margin-top: 30px;">
                    <span class="dashicons dashicons-warning"></span> Test Mode Configuration
                </h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="stripe_test_public_key">Test Publishable Key</label>
                        </th>
                        <td>
                            <input type="text" id="stripe_test_public_key" name="stripe_test_public_key" 
                                   value="<?php echo esc_attr(get_option('bms_stripe_test_public_key', '')); ?>" 
                                   class="regular-text" placeholder="pk_test_...">
                            <p class="description">Your Stripe test publishable key (starts with pk_test_)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="stripe_test_secret_key">Test Secret Key</label>
                        </th>
                        <td>
                            <input type="password" id="stripe_test_secret_key" name="stripe_test_secret_key" 
                                   value="<?php echo esc_attr(get_option('bms_stripe_test_secret_key', '')); ?>" 
                                   class="regular-text" placeholder="sk_test_...">
                            <p class="description">Your Stripe test secret key (starts with sk_test_)</p>
                        </td>
                    </tr>
                </table>
                
                <h3 style="color: #d73502; margin-top: 30px;">
                    <span class="dashicons dashicons-lock"></span> Live Mode Configuration
                </h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="stripe_live_public_key">Live Publishable Key</label>
                        </th>
                        <td>
                            <input type="text" id="stripe_live_public_key" name="stripe_live_public_key" 
                                   value="<?php echo esc_attr(get_option('bms_stripe_live_public_key', '')); ?>" 
                                   class="regular-text" placeholder="pk_live_...">
                            <p class="description">Your Stripe live publishable key (starts with pk_live_)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="stripe_live_secret_key">Live Secret Key</label>
                        </th>
                        <td>
                            <input type="password" id="stripe_live_secret_key" name="stripe_live_secret_key" 
                                   value="<?php echo esc_attr(get_option('bms_stripe_live_secret_key', '')); ?>" 
                                   class="regular-text" placeholder="sk_live_...">
                            <p class="description">Your Stripe live secret key (starts with sk_live_)</p>
                        </td>
                    </tr>
                </table>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px;">
                    <h4>Get Your Stripe API Keys:</h4>
                    <ol>
                        <li>Go to <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard → API Keys</a></li>
                        <li>Copy your <strong>Publishable key</strong> and <strong>Secret key</strong></li>
                        <li>For testing, use the <strong>Test</strong> keys first</li>
                        <li>For production, use the <strong>Live</strong> keys</li>
                    </ol>
                </div>
            </div>
            
            <p class="submit">
                <input type="submit" name="bms_save_payment_settings" class="button-primary" 
                       value="Save Payment Settings">
                <input type="submit" name="bms_test_stripe_connection" class="button" 
                       value="Test Stripe Connection" style="margin-left: 10px;">
                <a href="?page=bms-settings" class="button" style="margin-left: 10px;">Back to Main Settings</a>
            </p>
        </form>
        
        <!-- Current Configuration -->
        <div class="bms-admin-card">
            <h3>Current Configuration Status</h3>
            <table class="widefat">
                <tr>
                    <td><strong>Payment Required:</strong></td>
                    <td><?php echo get_option('bms_payment_required', '1') ? 'Yes' : 'No'; ?></td>
                </tr>
                <tr>
                    <td><strong>Currency:</strong></td>
                    <td><?php echo esc_html(get_option('bms_payment_currency', 'GBP')); ?></td>
                </tr>
                <tr>
                    <td><strong>VAT Rate:</strong></td>
                    <td><?php echo esc_html(get_option('bms_vat_rate', 0.20) * 100); ?>%</td>
                </tr>
                <tr>
                    <td><strong>Mode:</strong></td>
                    <td>
                        <span style="color: <?php echo $test_mode ? 'orange' : 'green'; ?>; font-weight: bold;">
                            <?php echo $test_mode ? 'TEST MODE' : 'LIVE MODE'; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Stripe Status:</strong></td>
                    <td>
                        <?php 
                        $stripe_enabled = get_option('bms_stripe_enabled', '1');
                        $current_public_key = $test_mode ? get_option('bms_stripe_test_public_key', '') : get_option('bms_stripe_live_public_key', '');
                        $current_secret_key = $test_mode ? get_option('bms_stripe_test_secret_key', '') : get_option('bms_stripe_live_secret_key', '');
                        
                        if (!$stripe_enabled) {
                            echo '<span style="color: #d73502;">Disabled</span>';
                        } elseif (empty($current_public_key) || empty($current_secret_key)) {
                            echo '<span style="color: #f0ad4e;">API keys missing</span>';
                        } else {
                            echo '<span style="color: #5cb85c;">Configured</span>';
                        }
                        ?>
                    </td>
                </tr>
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
    <?php
}

/**
 * Test Stripe connection
 * 
 * @return array Test result
 */
function bms_test_stripe_connection() {
    $test_mode = get_option('bms_payment_test_mode', '1');
    
    if ($test_mode) {
        $secret_key = get_option('bms_stripe_test_secret_key', '');
        $mode_text = 'test';
    } else {
        $secret_key = get_option('bms_stripe_live_secret_key', '');
        $mode_text = 'live';
    }
    
    if (empty($secret_key)) {
        return array(
            'success' => false,
            'message' => 'No Stripe secret key configured for ' . $mode_text . ' mode'
        );
    }
    
    // Simple API test - retrieve account information
    $response = wp_remote_get('https://api.stripe.com/v1/account', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ),
        'timeout' => 30));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Connection error: ' . $response->get_error_message()
        );
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if ($status_code === 200 && isset($data['id'])) {
        return array(
            'success' => true,
            'message' => 'Connected to Stripe account: ' . $data['display_name'] . ' (' . $mode_text . ' mode)');
    } else {
        $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
        return array(
            'success' => false,
            'message' => 'Stripe API error: ' . $error_message
        );
    }
}
