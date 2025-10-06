<?php
/**
 * Enhanced Main Settings Page for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display enhanced settings page with tabs
 */
function bms_enhanced_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Auto-migrate constants if not done yet
    if (!BMS_Settings_Migrator::is_migrated()) {
        BMS_Settings_Migrator::migrate_constants_to_options();
    }
    
    // Get current tab
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
    
    // Get settings data
    $business_info = BMS_Settings_Migrator::get_business_info();
    $payment_settings = BMS_Settings_Migrator::get_payment_settings();
    $booking_settings = BMS_Settings_Migrator::get_booking_settings();
    $migration_info = BMS_Settings_Migrator::get_migration_info();
    
    ?>
    <div class="wrap">
        <h1>
            <span class="dashicons dashicons-admin-settings" style="font-size: 30px; margin-right: 10px;"></span>
            Blue Motors Southampton Settings
        </h1>
        
        <?php if ($migration_info['migrated']): ?>
            <div class="notice notice-success">
                <p><strong>Settings Migration Complete!</strong> All hardcoded settings have been migrated to the database. 
                You can now manage everything through this professional admin interface.</p>
            </div>
        <?php endif; ?>
        
        <!-- Settings Navigation Tabs -->
        <nav class="nav-tab-wrapper wp-clearfix">
            <a href="?page=bms-settings&tab=overview" 
               class="nav-tab <?php echo $current_tab === 'overview' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-dashboard"></span> Overview
            </a>
            <a href="?page=bms-business-settings" class="nav-tab">
                <span class="dashicons dashicons-building"></span> Business Settings
            </a>
            <a href="?page=bms-payment-settings" class="nav-tab">
                <span class="dashicons dashicons-money-alt"></span> Payment Gateway
            </a>
            <a href="?page=bms-smtp-settings" class="nav-tab">
                <span class="dashicons dashicons-email-alt"></span> Email Settings
            </a>
            <a href="?page=bms-api-settings" class="nav-tab">
                <span class="dashicons dashicons-admin-plugins"></span> API Settings
            </a>
        </nav>
        
        <div class="bms-settings-content">
            <?php
            switch ($current_tab) {
                case 'overview':
                default:
                    bms_settings_overview_tab($business_info, $payment_settings, $booking_settings, $migration_info);
                    break;
            }
            ?>
        </div>
    </div>
    
    <style>
    .bms-settings-content {
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
    
    .bms-admin-card h2 .dashicons {
        margin-right: 8px;
        color: #0073aa;
    }
    
    .bms-status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .bms-status-item {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        text-align: center;
    }
    
    .bms-status-item .dashicons {
        font-size: 40px;
        margin-bottom: 10px;
        display: block;
    }
    
    .bms-status-item.status-good .dashicons {
        color: #5cb85c;
    }
    
    .bms-status-item.status-warning .dashicons {
        color: #f0ad4e;
    }
    
    .bms-status-item.status-error .dashicons {
        color: #d73502;
    }
    
    .bms-quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .bms-stat-box {
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
    </style>
    <?php
}

/**
 * Display overview tab content
 */
function bms_settings_overview_tab($business_info, $payment_settings, $booking_settings, $migration_info) {
    // Get system status
    $smtp_configured = !empty(get_option('bms_smtp_host', ''));
    $payment_configured = !empty($payment_settings['stripe_public_key']) && !empty($payment_settings['stripe_secret_key']);
    $dvla_configured = !empty(get_option('bms_dvla_api_key', ''));
    
    ?>
    <!-- Quick Stats -->
    <div class="bms-quick-stats">
        <div class="bms-stat-box">
            <div class="bms-stat-number"><?php echo $migration_info['migration_count']; ?></div>
            <div class="bms-stat-label">Settings Configured</div>
        </div>
        <div class="bms-stat-box">
            <div class="bms-stat-number"><?php echo $booking_settings['max_days']; ?></div>
            <div class="bms-stat-label">Max Booking Days</div>
        </div>
        <div class="bms-stat-box">
            <div class="bms-stat-number"><?php echo $booking_settings['slot_duration']; ?>min</div>
            <div class="bms-stat-label">Time Slot Duration</div>
        </div>
        <div class="bms-stat-box">
            <div class="bms-stat-number"><?php echo $payment_settings['vat_rate'] * 100; ?>%</div>
            <div class="bms-stat-label">VAT Rate</div>
        </div>
    </div>
    
    <!-- System Status -->
    <div class="bms-admin-card">
        <h2><span class="dashicons dashicons-admin-tools"></span> System Status</h2>
        <div class="bms-status-grid">
            <div class="bms-status-item <?php echo $migration_info['migrated'] ? 'status-good' : 'status-warning'; ?>">
                <span class="dashicons dashicons-database-import"></span>
                <h3>Settings Migration</h3>
                <p><?php echo $migration_info['migrated'] ? 'Complete' : 'Pending'; ?></p>
                <?php if (!$migration_info['migrated']): ?>
                    <a href="?page=bms-business-settings" class="button">Configure Now</a>
                <?php endif; ?>
            </div>
            
            <div class="bms-status-item <?php echo $smtp_configured ? 'status-good' : 'status-warning'; ?>">
                <span class="dashicons dashicons-email-alt"></span>
                <h3>Email System</h3>
                <p><?php echo $smtp_configured ? 'SMTP Configured' : 'WordPress Default'; ?></p>
                <a href="?page=bms-smtp-settings" class="button">Configure</a>
            </div>
            
            <div class="bms-status-item <?php echo $payment_configured ? 'status-good' : 'status-warning'; ?>">
                <span class="dashicons dashicons-money-alt"></span>
                <h3>Payment Gateway</h3>
                <p><?php echo $payment_configured ? 'Stripe Configured' : 'Not Configured'; ?></p>
                <a href="?page=bms-payment-settings" class="button">Configure</a>
            </div>
            
            <div class="bms-status-item <?php echo $dvla_configured ? 'status-good' : 'status-warning'; ?>">
                <span class="dashicons dashicons-admin-plugins"></span>
                <h3>DVLA API</h3>
                <p><?php echo $dvla_configured ? 'API Key Set' : 'Using Mock Data'; ?></p>
                <a href="?page=bms-api-settings" class="button">Configure</a>
            </div>
        </div>
    </div>
    
    <!-- Business Information Summary -->
    <div class="bms-admin-card">
        <h2><span class="dashicons dashicons-building"></span> Business Information</h2>
        <table class="form-table">
            <tr>
                <th>Business Name:</th>
                <td><?php echo esc_html($business_info['name']); ?></td>
            </tr>
            <tr>
                <th>Address:</th>
                <td><?php echo esc_html($business_info['address']); ?></td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td><?php echo esc_html($business_info['phone']); ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php echo esc_html($business_info['email']); ?></td>
            </tr>
        </table>
        <p>
            <a href="?page=bms-business-settings" class="button button-primary">Edit Business Settings</a>
        </p>
    </div>
    
    <!-- Quick Actions -->
    <div class="bms-admin-card">
        <h2><span class="dashicons dashicons-admin-links"></span> Quick Actions</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <h4>Configuration</h4>
                <p><a href="?page=bms-business-settings" class="button">Business Settings</a></p>
                <p><a href="?page=bms-payment-settings" class="button">Payment Gateway</a></p>
                <p><a href="?page=bms-smtp-settings" class="button">Email Configuration</a></p>
            </div>
            
            <div>
                <h4>Management</h4>
                <p><a href="?page=bms-dashboard" class="button">View Dashboard</a></p>
                <p><a href="?page=bms-bookings" class="button">Manage Bookings</a></p>
                <p><a href="?page=bms-services" class="button">Service Configuration</a></p>
            </div>
            
            <div>
                <h4>Testing</h4>
                <p><a href="<?php echo site_url(); ?>/?bms_test_form=1" class="button" target="_blank">Test Booking Form</a></p>
                <p><a href="<?php echo site_url(); ?>/?bms_test_vehicle=VF19XKX" class="button" target="_blank">Test Vehicle Lookup</a></p>
                <p><a href="?page=bms-smtp-settings" class="button">Test Email System</a></p>
            </div>
        </div>
    </div>
    
    <!-- Migration Information -->
    <?php if ($migration_info['migrated']): ?>
    <div class="bms-admin-card">
        <h2><span class="dashicons dashicons-info"></span> Migration Information</h2>
        <table class="widefat">
            <tr>
                <td><strong>Migration Status:</strong></td>
                <td><span style="color: #5cb85c;">✓ Complete</span></td>
            </tr>
            <tr>
                <td><strong>Migration Date:</strong></td>
                <td><?php echo esc_html($migration_info['migration_date']); ?></td>
            </tr>
            <tr>
                <td><strong>Settings Migrated:</strong></td>
                <td><?php echo esc_html($migration_info['migration_count']); ?> of <?php echo esc_html($migration_info['total_constants']); ?> constants</td>
            </tr>
            <tr>
                <td><strong>Next Steps:</strong></td>
                <td>Review and customize your settings using the tabs above</td>
            </tr>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- Performance Tips -->
    <div class="bms-admin-card">
        <h2><span class="dashicons dashicons-performance"></span> Performance & Best Practices</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h4>✅ Recommended Setup</h4>
                <ul>
                    <li>Configure SMTP for reliable email delivery</li>
                    <li>Set up Stripe test mode first, then live mode</li>
                    <li>Add DVLA API key for real vehicle data</li>
                    <li>Test booking flow before going live</li>
                    <li>Set appropriate business hours</li>
                </ul>
            </div>
            <div>
                <h4>⚠️ Important Notes</h4>
                <ul>
                    <li>Always test payment processing in test mode</li>
                    <li>Keep your Stripe keys secure</li>
                    <li>Regular backup of your settings</li>
                    <li>Monitor email deliverability</li>
                    <li>Update business hours for holidays</li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}
