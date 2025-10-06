<?php
/**
 * Test Phase 4 Integration
 * Verifies that Customer Service and Smart Scheduler are properly registered
 * 
 * @package BlueMotosSouthampton
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test Phase 4 service integration
 */
function bms_test_phase4_integration() {
    $results = [];
    
    // Test 1: Check if namespaces are properly loaded
    $results['namespace_test'] = [
        'name' => 'Namespace Classes',
        'status' => class_exists('BlueMotosSouthampton\Services\CacheManager') && 
                   class_exists('BlueMotosSouthampton\Services\CustomerService') && 
                   class_exists('BlueMotosSouthampton\Services\SmartScheduler'),
        'message' => ''
    ];
    
    if (!$results['namespace_test']['status']) {
        $missing = [];
        if (!class_exists('BlueMotosSouthampton\Services\CacheManager')) $missing[] = 'CacheManager';
        if (!class_exists('BlueMotosSouthampton\Services\CustomerService')) $missing[] = 'CustomerService';
        if (!class_exists('BlueMotosSouthampton\Services\SmartScheduler')) $missing[] = 'SmartScheduler';
        $results['namespace_test']['message'] = 'Missing classes: ' . implode(', ', $missing);
    }
    
    // Test 2: Check if shortcodes are registered
    $results['shortcode_test'] = [
        'name' => 'Shortcode Registration',
        'status' => shortcode_exists('bms_customer_history') && shortcode_exists('bms_smart_scheduler'),
        'message' => ''
    ];
    
    if (!$results['shortcode_test']['status']) {
        $missing = [];
        if (!shortcode_exists('bms_customer_history')) $missing[] = 'bms_customer_history';
        if (!shortcode_exists('bms_smart_scheduler')) $missing[] = 'bms_smart_scheduler';
        $results['shortcode_test']['message'] = 'Missing shortcodes: ' . implode(', ', $missing);
    }
    
    // Test 3: Check if AJAX handlers are registered
    $results['ajax_test'] = [
        'name' => 'AJAX Handlers',
        'status' => has_action('wp_ajax_bms_get_smart_suggestions') && 
                   has_action('wp_ajax_nopriv_bms_get_smart_suggestions'),
        'message' => has_action('wp_ajax_bms_get_smart_suggestions') ? 'AJAX handlers registered' : 'AJAX handlers missing'
    ];
    
    // Test 4: Check if templates exist
    $results['template_test'] = [
        'name' => 'Template Files',
        'status' => file_exists(BMS_PLUGIN_DIR . 'templates/customer-history.php') && 
                   file_exists(BMS_PLUGIN_DIR . 'templates/smart-scheduler-widget.php'),
        'message' => ''
    ];
    
    if (!$results['template_test']['status']) {
        $missing = [];
        if (!file_exists(BMS_PLUGIN_DIR . 'templates/customer-history.php')) $missing[] = 'customer-history.php';
        if (!file_exists(BMS_PLUGIN_DIR . 'templates/smart-scheduler-widget.php')) $missing[] = 'smart-scheduler-widget.php';
        $results['template_test']['message'] = 'Missing templates: ' . implode(', ', $missing);
    }
    
    // Test 5: Check database tables/views
    global $wpdb;
    $results['database_test'] = [
        'name' => 'Database Components',
        'status' => true,
        'message' => ''
    ];
    
    // Check if customer history view exists
    $view_exists = $wpdb->get_var("SHOW TABLES LIKE 'vw_bms_customer_history'");
    if (!$view_exists) {
        $results['database_test']['status'] = false;
        $results['database_test']['message'] = 'Customer history view (vw_bms_customer_history) not found';
    }
    
    // Display results
    ?>
    <div class="wrap">
        <h1>Phase 4 Integration Test Results</h1>
        <p>Testing Customer Service and Smart Scheduler integration...</p>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $test): ?>
                <tr>
                    <td><strong><?php echo esc_html($test['name']); ?></strong></td>
                    <td>
                        <?php if ($test['status']): ?>
                            <span style="color: green;">✓ PASSED</span>
                        <?php else: ?>
                            <span style="color: red;">✗ FAILED</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($test['message'] ?: 'All components working correctly'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>Quick Test Links</h2>
        <p>Test the new shortcodes on a page:</p>
        <ul>
            <li><code>[bms_customer_history]</code> - Customer service history with recommendations</li>
            <li><code>[bms_smart_scheduler]</code> - AI-powered appointment scheduling</li>
        </ul>
        
        <h2>Integration Summary</h2>
        <?php
        $all_passed = array_reduce($results, function($carry, $test) {
            return $carry && $test['status'];
        }, true);
        
        if ($all_passed): ?>
            <div class="notice notice-success">
                <p><strong>✓ All Phase 4 components are properly integrated!</strong></p>
                <p>The Customer Service and Smart Scheduler features are ready to use.</p>
            </div>
        <?php else: ?>
            <div class="notice notice-error">
                <p><strong>✗ Some Phase 4 components need attention.</strong></p>
                <p>Please review the failed tests above and ensure all files are properly loaded.</p>
            </div>
        <?php endif; ?>
        
        <h2>Advanced Features Summary</h2>
        <div class="notice notice-info">
            <p><strong>Phase 4 Features Available:</strong></p>
            <ul>
                <li>🎯 <strong>Customer History Tracking:</strong> Complete service history with loyalty rewards</li>
                <li>🤖 <strong>AI-Powered Scheduling:</strong> Smart appointment suggestions based on patterns</li>
                <li>📊 <strong>Personalized Recommendations:</strong> Service reminders and special offers</li>
                <li>⚡ <strong>Advanced Caching:</strong> Lightning-fast performance optimization</li>
            </ul>
        </div>
    </div>
    <?php
}

// Menu registration is now handled in the main plugin file
