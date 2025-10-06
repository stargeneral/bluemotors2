<?php
/**
 * Phase 2 Implementation Complete Notice
 * 
 * @package BlueMotosSouthampton
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display Phase 2 completion notice
 */
add_action('admin_notices', function() {
    // Only show on Blue Motors admin pages
    $screen = get_current_screen();
    if (strpos($screen->id, 'blue-motors') === false) {
        return;
    }
    
    // Check if user has dismissed this notice
    $dismissed = get_option('bms_phase2_notice_dismissed', false);
    if ($dismissed) {
        return;
    }
    
    // Check if migration has been completed (indicates Phase 2 is ready)
    if (!BMS_Settings_Migrator::is_migrated()) {
        return;
    }
    
    ?>
    <div class="notice notice-success is-dismissible bms-phase2-notice">
        <h3 style="margin-top: 10px;">🎉 Phase 2 Enhancement Complete!</h3>
        <p><strong>Blue Motors Southampton has been upgraded with professional admin features:</strong></p>
        <ul style="margin-left: 20px;">
            <li>✅ <strong>Business Settings Migration</strong> - No more hardcoded configuration!</li>
            <li>✅ <strong>Payment Gateway Configuration</strong> - Admin-configurable Stripe settings</li>
            <li>✅ <strong>Enhanced Admin Interface</strong> - Professional tabbed interface</li>
            <li>✅ <strong>Settings Management Hub</strong> - Centralized configuration</li>
        </ul>
        <p>
            <a href="?page=bms-settings" class="button button-primary">Explore New Settings</a>
            <a href="?page=bms-business-settings" class="button">Configure Business Info</a>
            <a href="?page=bms-payment-settings" class="button">Set Up Payments</a>
            <button class="button bms-dismiss-notice" data-notice="phase2">Dismiss Notice</button>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.bms-dismiss-notice').on('click', function() {
            var notice = $(this).data('notice');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bms_dismiss_notice',
                    notice: notice,
                    nonce: '<?php echo wp_create_nonce('bms_dismiss_notice'); ?>'
                },
                success: function() {
                    $('.bms-phase2-notice').fadeOut();
                }
            });
        });
    });
    </script>
    
    <style>
    .bms-phase2-notice {
        border-left-color: #5cb85c;
        background: #f8fff8;
    }
    
    .bms-phase2-notice h3 {
        color: #2e7d2e;
    }
    
    .bms-phase2-notice ul {
        margin: 10px 0;
    }
    
    .bms-phase2-notice li {
        margin: 5px 0;
    }
    </style>
    <?php
});

/**
 * Handle notice dismissal
 */
add_action('wp_ajax_bms_dismiss_notice', function() {
    check_ajax_referer('bms_dismiss_notice', 'nonce');
    
    $notice = sanitize_text_field($_POST['notice']);
    
    if ($notice === 'phase2') {
        update_option('bms_phase2_notice_dismissed', true);
    }
    
    wp_die();
});
