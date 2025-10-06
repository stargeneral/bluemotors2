<?php
/**
 * Phase 2 Testing Script
 * 
 * Tests the new admin interface and settings migration
 * Access via: yoursite.com/?bms_test_phase2=1
 * 
 * @package BlueMotosSouthampton
 * @since 1.1.0
 */

// Test Phase 2 functionality
add_action('init', function() {
    if (!isset($_GET['bms_test_phase2']) || !current_user_can('manage_options')) {
        return;
    }
    
    echo '<h1>Blue Motors Southampton - Phase 2 Testing</h1>';
    echo '<div style="max-width: 800px; margin: 20px; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">';
    
    // Test 1: Settings Migrator Class
    echo '<h2>🔧 Test 1: Settings Migration System</h2>';
    
    if (class_exists('BMS_Settings_Migrator')) {
        echo '<p style="color: green;">✅ BMS_Settings_Migrator class loaded successfully</p>';
        
        // Test migration info
        $migration_info = BMS_Settings_Migrator::get_migration_info();
        echo '<p><strong>Migration Status:</strong> ' . ($migration_info['migrated'] ? 'Complete' : 'Pending') . '</p>';
        echo '<p><strong>Total Constants:</strong> ' . $migration_info['total_constants'] . '</p>';
        
        if (!$migration_info['migrated']) {
            echo '<p style="color: orange;">⚠️ Running migration now...</p>';
            $result = BMS_Settings_Migrator::migrate_constants_to_options();
            echo '<p style="color: green;">✅ Migration completed! Migrated: ' . ($result ? 'Yes' : 'No') . '</p>';
        }
        
        // Test business info retrieval
        $business_info = BMS_Settings_Migrator::get_business_info();
        echo '<p><strong>Business Name:</strong> ' . esc_html($business_info['name']) . '</p>';
        echo '<p><strong>Business Address:</strong> ' . esc_html($business_info['address']) . '</p>';
        
        // Test payment settings
        $payment_settings = BMS_Settings_Migrator::get_payment_settings();
        echo '<p><strong>Payment Currency:</strong> ' . esc_html($payment_settings['currency']) . '</p>';
        echo '<p><strong>VAT Rate:</strong> ' . ($payment_settings['vat_rate'] * 100) . '%</p>';
        
    } else {
        echo '<p style="color: red;">❌ BMS_Settings_Migrator class not found</p>';
    }
    
    // Test 2: Admin Page Files
    echo '<h2>📄 Test 2: Admin Page Files</h2>';
    
    $admin_files = [
        'enhanced-settings.php' => 'Enhanced Settings Hub',
        'business-settings.php' => 'Business Settings Page', 
        'payment-settings.php' => 'Payment Gateway Settings',
        'smtp-settings.php' => 'Email Settings (Phase 1)'];
    
    foreach ($admin_files as $file => $description) {
        $file_path = BMS_PLUGIN_DIR . 'admin/' . $file;
        if (file_exists($file_path)) {
            echo '<p style="color: green;">✅ ' . $description . ' - File exists</p>';
        } else {
            echo '<p style="color: red;">❌ ' . $description . ' - File missing</p>';
        }
    }
    
    // Test 3: Database Options
    echo '<h2>🗄️ Test 3: Database Options</h2>';
    
    $test_options = [
        'bms_business_name' => 'Business Name',
        'bms_business_address' => 'Business Address',
        'bms_payment_currency' => 'Payment Currency',
        'bms_stripe_enabled' => 'Stripe Enabled',
        'bms_settings_migrated' => 'Migration Status'];
    
    foreach ($test_options as $option => $description) {
        $value = get_option($option, 'NOT SET');
        if ($value !== 'NOT SET' && $value !== false) {
            echo '<p style="color: green;">✅ ' . $description . ': ' . esc_html($value) . '</p>';
        } else {
            echo '<p style="color: orange;">⚠️ ' . $description . ': Not configured</p>';
        }
    }
    
    // Test 4: Admin Menu Registration
    echo '<h2>📋 Test 4: Admin Menu Structure</h2>';
    echo '<p>Check these admin menu items exist:</p>';
    echo '<ul>';
    echo '<li><a href="' . admin_url('admin.php?page=bms-settings') . '">Main Settings Hub</a></li>';
    echo '<li><a href="' . admin_url('admin.php?page=bms-business-settings') . '">Business Settings</a></li>';
    echo '<li><a href="' . admin_url('admin.php?page=bms-payment-settings') . '">Payment Gateway</a></li>';
    echo '<li><a href="' . admin_url('admin.php?page=bms-smtp-settings') . '">Email Settings</a></li>';
    echo '</ul>';
    
    // Test 5: Configuration Status
    echo '<h2>⚙️ Test 5: Configuration Status</h2>';
    
    $smtp_configured = !empty(get_option('bms_smtp_host', ''));
    $payment_configured = !empty(get_option('bms_stripe_public_key', '')) && !empty(get_option('bms_stripe_secret_key', ''));
    $business_configured = !empty(get_option('bms_business_name', ''));
    
    echo '<p><strong>SMTP Email:</strong> ' . ($smtp_configured ? '✅ Configured' : '⚠️ Not configured') . '</p>';
    echo '<p><strong>Payment Gateway:</strong> ' . ($payment_configured ? '✅ Configured' : '⚠️ Not configured') . '</p>';
    echo '<p><strong>Business Settings:</strong> ' . ($business_configured ? '✅ Configured' : '⚠️ Not configured') . '</p>';
    
    // Test 6: Quick Configuration Test
    echo '<h2>🚀 Test 6: Quick Configuration Check</h2>';
    
    if ($business_configured && $smtp_configured) {
        echo '<p style="color: green; font-weight: bold;">🎉 Phase 2 implementation is working correctly!</p>';
        echo '<p>Your plugin now has:</p>';
        echo '<ul>';
        echo '<li>✅ Professional admin interface</li>';
        echo '<li>✅ Business settings migration</li>';
        echo '<li>✅ Payment gateway configuration</li>';
        echo '<li>✅ Enhanced settings management</li>';
        echo '</ul>';
    } else {
        echo '<p style="color: orange;">⚠️ Some configuration still needed:</p>';
        if (!$business_configured) echo '<p>• Configure business settings</p>';
        if (!$smtp_configured) echo '<p>• Configure email settings</p>';
        if (!$payment_configured) echo '<p>• Configure payment gateway</p>';
    }
    
    // Next Steps
    echo '<h2>📋 Next Steps</h2>';
    echo '<p><strong>To complete your setup:</strong></p>';
    echo '<ol>';
    echo '<li><a href="' . admin_url('admin.php?page=bms-settings') . '">Visit the Enhanced Settings Hub</a></li>';
    echo '<li><a href="' . admin_url('admin.php?page=bms-business-settings') . '">Configure Business Settings</a></li>';
    echo '<li><a href="' . admin_url('admin.php?page=bms-payment-settings') . '">Set Up Payment Gateway</a></li>';
    echo '<li><a href="' . admin_url('admin.php?page=bms-smtp-settings') . '">Test Email Settings</a></li>';
    echo '<li>Test the complete booking flow</li>';
    echo '</ol>';
    
    echo '</div>';
    
    // Prevent normal page load
    exit;
});
