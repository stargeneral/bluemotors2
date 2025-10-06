<?php
/**
 * SMTP Implementation Success Notice
 * 
 * @package BlueMotosSouthampton
 */

// Add notice to admin when SMTP system is ready
add_action('admin_notices', function() {
    // Only show on Blue Motors admin pages
    $screen = get_current_screen();
    if (strpos($screen->id, 'blue-motors') === false) {
        return;
    }
    
    // Check if SMTP is configured
    $smtp_enabled = get_option('bms_smtp_enabled', '0');
    $smtp_host = get_option('bms_smtp_host', '');
    
    if ($smtp_enabled && !empty($smtp_host)) {
        echo '<div class="notice notice-success"><p>';
        echo '<strong>✅ SMTP Email System Active</strong> - ';
        echo 'Booking confirmations and notifications will be sent via SMTP.';
        echo '</p></div>';
    } else {
        echo '<div class="notice notice-info"><p>';
        echo '<strong>📧 Email System Ready</strong> - ';
        echo 'Configure SMTP settings in <a href="' . admin_url('admin.php?page=bms-smtp-settings') . '">Email Settings</a> ';
        echo 'for reliable email delivery.';
        echo '</p></div>';
    }
});
