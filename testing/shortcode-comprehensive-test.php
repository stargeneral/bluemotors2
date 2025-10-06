<?php
/**
 * Comprehensive Shortcode Testing Tool
 * Blue Motors Southampton Plugin
 * 
 * Tests all shortcodes for functionality, rendering, and integration
 * 
 * Usage: Access via WordPress admin or run directly
 */

// Prevent direct access
if (!defined('ABSPATH') && !defined('WP_DEBUG')) {
    // Allow direct execution in development
    if (file_exists('../../../wp-config.php')) {
        require_once '../../../wp-config.php';
    } else {
        die('WordPress environment not found');
    }
}

/**
 * Shortcode Comprehensive Test Class
 */
class BMS_Shortcode_Comprehensive_Test {
    
    private $shortcodes_to_test = [];
    private $test_results = [];
    private $total_tests = 0;
    private $passed_tests = 0;
    private $failed_tests = 0;
    
    public function __construct() {
        $this->initialize_shortcodes();
    }
    
    /**
     * Initialize all shortcodes to test
     */
    private function initialize_shortcodes() {
        $this->shortcodes_to_test = [
            // Core Vehicle & Booking Shortcodes
            'bms_vehicle_lookup' => [
                'name' => 'Vehicle Lookup Form',
                'basic' => '[bms_vehicle_lookup]',
                'advanced' => '[bms_vehicle_lookup title="Custom Title" show_mot_history="true" theme="compact"]',
                'integration' => '[bms_vehicle_lookup integration_mode="booking"]',
                'expected_elements' => ['bms-vehicle-lookup-container', 'bms-registration-input', 'bms-lookup-button'],
                'css_classes' => ['bms-vehicle-lookup-container'],
                'js_handlers' => ['BMS.VehicleLookup']
            ],
            
            'bms_booking_form' => [
                'name' => 'Main Booking Form',
                'basic' => '[bms_booking_form]',
                'advanced' => '[bms_booking_form service="mot_test" theme="blue" show_competitive="true"]',
                'expected_elements' => ['bms-booking-container', 'bms-progress-steps', 'step-1'],
                'css_classes' => ['bms-booking-container'],
                'js_handlers' => ['BMSBookingForm']
            ],
            
            // Service Display Shortcodes
            'bms_service_cards' => [
                'name' => 'Service Cards Display',
                'basic' => '[bms_service_cards]',
                'advanced' => '[bms_service_cards columns="2" show_booking_buttons="true" category="all"]',
                'expected_elements' => ['bms-service-cards', 'service-card'],
                'css_classes' => ['bms-service-cards']
            ],
            
            'bms_enhanced_services' => [
                'name' => 'Enhanced Service Cards',
                'basic' => '[bms_enhanced_services]',
                'advanced' => '[bms_enhanced_services category="all" show_comparison="true" layout="grid"]',
                'expected_elements' => ['bms-enhanced-services-container', 'service-cards'],
                'css_classes' => ['bms-enhanced-services-container']
            ],
            
            'bms_service_list' => [
                'name' => 'Service List Display',
                'basic' => '[bms_service_list]',
                'advanced' => '[bms_service_list style="list" show_prices="true" category="all"]',
                'expected_elements' => ['bms-service-list', 'service-list-container'],
                'css_classes' => ['bms-service-list']
            ],
            
            'bms_service_card' => [
                'name' => 'Single Service Card',
                'basic' => '[bms_service_card service="mot_test"]',
                'advanced' => '[bms_service_card service="tyre_fitting" show_competitive="true"]',
                'expected_elements' => ['service-card'],
                'css_classes' => ['service-card']
            ],
            
            // Tyre Services Shortcodes
            'bms_tyre_search' => [
                'name' => 'Tyre Search Interface',
                'basic' => '[bms_tyre_search]',
                'advanced' => '[bms_tyre_search style="compact" competitive_messaging="true"]',
                'expected_elements' => ['bms-tyre-search-wrapper'],
                'css_classes' => ['bms-tyre-search-wrapper'],
                'js_handlers' => ['BlueMotosTyreBooking']
            ],
            
            // Smart Scheduling
            'bms_smart_scheduler' => [
                'name' => 'AI Smart Scheduler',
                'basic' => '[bms_smart_scheduler]',
                'advanced' => '[bms_smart_scheduler show_customer_prefs="true" max_suggestions="5"]',
                'expected_elements' => ['bms-smart-scheduler', 'suggestion-slots'],
                'css_classes' => ['bms-smart-scheduler']
            ],
            
            // Location & Contact
            'bms_location_info' => [
                'name' => 'Location Information',
                'basic' => '[bms_location_info]',
                'advanced' => '[bms_location_info show_map="false" show_hours="true" style="card"]',
                'expected_elements' => ['bms-location-info', 'location-card'],
                'css_classes' => ['bms-location-info']
            ],
            
            'bms_opening_hours' => [
                'name' => 'Opening Hours Only',
                'basic' => '[bms_opening_hours]',
                'expected_elements' => ['bms-location-info'],
                'css_classes' => ['bms-location-info']
            ],
            
            'bms_contact_form' => [
                'name' => 'Contact Form',
                'basic' => '[bms_contact_form]',
                'advanced' => '[bms_contact_form title="Get In Touch" show_location="true"]',
                'expected_elements' => ['bms-contact-form-container', 'bms-contact-form'],
                'css_classes' => ['bms-contact-form-container']
            ],
            
            // Competitive & Comparison
            'bms_vs_f1' => [
                'name' => 'Comparison vs industry leaders',
                'basic' => '[bms_vs_f1]',
                'advanced' => '[bms_vs_f1 style="table" show_title="true"]',
                'expected_elements' => ['bms-vs-f1-comparison'],
                'css_classes' => ['bms-vs-f1-comparison']
            ],
            
            'bms_comparison_table' => [
                'name' => 'Comparison Table',
                'basic' => '[bms_comparison_table]',
                'advanced' => '[bms_comparison_table title="Our Advantages" show_conclusion="true"]',
                'expected_elements' => ['service-comparison-table'],
                'css_classes' => ['service-comparison-table']
            ],
            
            // Legacy/Alias Shortcodes
            'vehicle_lookup' => [
                'name' => 'Vehicle Lookup (Alias)',
                'basic' => '[vehicle_lookup]',
                'expected_elements' => ['bms-vehicle-lookup-container'],
                'css_classes' => ['bms-vehicle-lookup-container'],
                'note' => 'Alias for bms_vehicle_lookup'
            ]
        ];
    }
    
    /**
     * Run all shortcode tests
     */
    public function run_all_tests() {
        echo $this->render_header();
        
        foreach ($this->shortcodes_to_test as $shortcode => $config) {
            $this->test_shortcode($shortcode, $config);
        }
        
        echo $this->render_summary();
        echo $this->render_recommendations();
        
        return $this->test_results;
    }
    
    /**
     * Test individual shortcode
     */
    private function test_shortcode($shortcode, $config) {
        $this->total_tests++;
        
        $result = [
            'shortcode' => $shortcode,
            'name' => $config['name'],
            'registered' => $this->test_shortcode_registration($shortcode),
            'basic_render' => false,
            'advanced_render' => false,
            'elements_present' => false,
            'css_classes' => false,
            'js_handlers' => false,
            'errors' => [],
            'warnings' => [],
            'output_length' => 0,
            'pass' => false
        ];
        
        // Test shortcode registration
        if (!$result['registered']) {
            $result['errors'][] = "Shortcode [$shortcode] is not registered";
            $this->failed_tests++;
            $this->test_results[$shortcode] = $result;
            echo $this->render_test_result($result);
            return;
        }
        
        // Test basic rendering
        try {
            $basic_output = do_shortcode($config['basic']);
            $result['basic_render'] = !empty($basic_output) && $basic_output !== $config['basic'];
            $result['output_length'] = strlen($basic_output);
            
            if ($result['basic_render']) {
                // Test for expected elements
                if (isset($config['expected_elements'])) {
                    $elements_found = 0;
                    foreach ($config['expected_elements'] as $element) {
                        if (strpos($basic_output, $element) !== false) {
                            $elements_found++;
                        }
                    }
                    $result['elements_present'] = ($elements_found >= count($config['expected_elements']) * 0.7);
                }
                
                // Test for CSS classes
                if (isset($config['css_classes'])) {
                    $classes_found = 0;
                    foreach ($config['css_classes'] as $css_class) {
                        if (strpos($basic_output, $css_class) !== false) {
                            $classes_found++;
                        }
                    }
                    $result['css_classes'] = ($classes_found >= count($config['css_classes']) * 0.7);
                }
                
                // Test advanced rendering if available
                if (isset($config['advanced'])) {
                    $advanced_output = do_shortcode($config['advanced']);
                    $result['advanced_render'] = !empty($advanced_output) && $advanced_output !== $config['advanced'];
                    
                    // Compare outputs for attribute handling
                    if ($result['advanced_render'] && strlen($advanced_output) !== strlen($basic_output)) {
                        $result['warnings'][] = 'Advanced attributes appear to be working';
                    }
                }
            } else {
                $result['errors'][] = 'Shortcode renders but produces no output or returns unprocessed';
            }
            
        } catch (Exception $e) {
            $result['errors'][] = 'Exception during rendering: ' . $e->getMessage();
        }
        
        // Test JavaScript handlers (if applicable)
        if (isset($config['js_handlers'])) {
            $result['js_handlers'] = $this->test_js_dependencies($config['js_handlers']);
        }
        
        // Determine overall pass/fail
        $result['pass'] = $result['registered'] && 
                         $result['basic_render'] && 
                         ($result['elements_present'] !== false) &&
                         (empty($result['errors']));
        
        if ($result['pass']) {
            $this->passed_tests++;
        } else {
            $this->failed_tests++;
        }
        
        $this->test_results[$shortcode] = $result;
        echo $this->render_test_result($result);
    }
    
    /**
     * Test if shortcode is registered
     */
    private function test_shortcode_registration($shortcode) {
        global $shortcode_tags;
        return isset($shortcode_tags[$shortcode]) && is_callable($shortcode_tags[$shortcode]);
    }
    
    /**
     * Test JavaScript dependencies
     */
    private function test_js_dependencies($js_handlers) {
        // This is a basic check - in a real environment, we'd check if scripts are enqueued
        $dependencies_ok = true;
        
        foreach ($js_handlers as $handler) {
            if (!wp_script_is($handler, 'registered') && !wp_script_is($handler, 'enqueued')) {
                $dependencies_ok = false;
                break;
            }
        }
        
        return $dependencies_ok;
    }
    
    /**
     * Render test header
     */
    private function render_header() {
        return '
        <style>
        .shortcode-test-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .test-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .test-result {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            margin: 16px 0;
            padding: 20px;
            transition: all 0.2s ease;
        }
        
        .test-result.pass {
            border-color: #10b981;
            background: #f0fdf4;
        }
        
        .test-result.fail {
            border-color: #dc2626;
            background: #fef2f2;
        }
        
        .test-result.warning {
            border-color: #f59e0b;
            background: #fffbeb;
        }
        
        .test-name {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .test-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .test-status.pass {
            background: #10b981;
            color: white;
        }
        
        .test-status.fail {
            background: #dc2626;
            color: white;
        }
        
        .test-status.warning {
            background: #f59e0b;
            color: white;
        }
        
        .test-details {
            margin: 12px 0;
        }
        
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin: 12px 0;
        }
        
        .test-metric {
            background: rgba(0,0,0,0.05);
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            font-size: 12px;
        }
        
        .test-metric.good {
            background: #d1fae5;
            color: #065f46;
        }
        
        .test-metric.bad {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .shortcode-code {
            background: #1f2937;
            color: #f9fafb;
            padding: 8px 12px;
            border-radius: 6px;
            font-family: "Courier New", monospace;
            font-size: 12px;
            margin: 8px 0;
        }
        
        .error-list, .warning-list {
            list-style: none;
            padding: 0;
            margin: 8px 0;
        }
        
        .error-list li {
            background: #fecaca;
            color: #991b1b;
            padding: 6px 12px;
            margin: 4px 0;
            border-radius: 4px;
            border-left: 4px solid #dc2626;
        }
        
        .warning-list li {
            background: #fed7aa;
            color: #9a3412;
            padding: 6px 12px;
            margin: 4px 0;
            border-radius: 4px;
            border-left: 4px solid #f59e0b;
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin: 24px 0;
        }
        
        .summary-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .summary-card.success {
            border-color: #10b981;
            background: #f0fdf4;
        }
        
        .summary-card.danger {
            border-color: #dc2626;
            background: #fef2f2;
        }
        
        .summary-card.info {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .summary-number {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .recommendations {
            background: #eff6ff;
            border: 2px solid #3b82f6;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }
        
        .recommendations h3 {
            color: #1e3a8a;
            margin: 0 0 16px 0;
        }
        
        .recommendation-item {
            background: white;
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            margin: 12px 0;
            border-radius: 4px;
        }
        
        .recommendation-item.high {
            border-left-color: #dc2626;
            background: #fef2f2;
        }
        
        .recommendation-item.medium {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        
        .recommendation-item.low {
            border-left-color: #10b981;
            background: #f0fdf4;
        }
        </style>
        
        <div class="shortcode-test-container">
            <div class="test-header">
                <h1>🧪 Blue Motors Southampton</h1>
                <h2>Comprehensive Shortcode Testing Suite</h2>
                <p>Testing all shortcodes for functionality, rendering, and integration</p>
                <p><strong>Test Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
            </div>';
    }
    
    /**
     * Render individual test result
     */
    private function render_test_result($result) {
        $status_class = $result['pass'] ? 'pass' : 'fail';
        $status_text = $result['pass'] ? '✅ PASS' : '❌ FAIL';
        
        if (!$result['pass'] && empty($result['errors'])) {
            $status_class = 'warning';
            $status_text = '⚠️ WARNING';
        }
        
        $html = '<div class="test-result ' . $status_class . '">';
        $html .= '<div class="test-name">';
        $html .= '<span>' . esc_html($result['name']) . '</span>';
        $html .= '<span class="test-status ' . $status_class . '">' . $status_text . '</span>';
        $html .= '</div>';
        
        $html .= '<div class="shortcode-code">[' . $result['shortcode'] . ']</div>';
        
        // Test metrics grid
        $html .= '<div class="test-grid">';
        $html .= '<div class="test-metric ' . ($result['registered'] ? 'good' : 'bad') . '">';
        $html .= '<strong>Registered:</strong><br>' . ($result['registered'] ? 'Yes' : 'No');
        $html .= '</div>';
        
        $html .= '<div class="test-metric ' . ($result['basic_render'] ? 'good' : 'bad') . '">';
        $html .= '<strong>Renders:</strong><br>' . ($result['basic_render'] ? 'Yes' : 'No');
        $html .= '</div>';
        
        if ($result['elements_present'] !== false) {
            $html .= '<div class="test-metric ' . ($result['elements_present'] ? 'good' : 'bad') . '">';
            $html .= '<strong>Elements:</strong><br>' . ($result['elements_present'] ? 'Found' : 'Missing');
            $html .= '</div>';
        }
        
        if ($result['css_classes'] !== false) {
            $html .= '<div class="test-metric ' . ($result['css_classes'] ? 'good' : 'bad') . '">';
            $html .= '<strong>CSS Classes:</strong><br>' . ($result['css_classes'] ? 'Found' : 'Missing');
            $html .= '</div>';
        }
        
        $html .= '<div class="test-metric">';
        $html .= '<strong>Output Length:</strong><br>' . number_format($result['output_length']) . ' chars';
        $html .= '</div>';
        $html .= '</div>';
        
        // Errors
        if (!empty($result['errors'])) {
            $html .= '<ul class="error-list">';
            foreach ($result['errors'] as $error) {
                $html .= '<li>❌ ' . esc_html($error) . '</li>';
            }
            $html .= '</ul>';
        }
        
        // Warnings
        if (!empty($result['warnings'])) {
            $html .= '<ul class="warning-list">';
            foreach ($result['warnings'] as $warning) {
                $html .= '<li>⚠️ ' . esc_html($warning) . '</li>';
            }
            $html .= '</ul>';
        }
        
        // Note if present
        if (isset($this->shortcodes_to_test[$result['shortcode']]['note'])) {
            $html .= '<p><em>Note: ' . esc_html($this->shortcodes_to_test[$result['shortcode']]['note']) . '</em></p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render test summary
     */
    private function render_summary() {
        $pass_percentage = $this->total_tests > 0 ? round(($this->passed_tests / $this->total_tests) * 100, 1) : 0;
        
        $html = '<div class="summary-stats">';
        
        $html .= '<div class="summary-card info">';
        $html .= '<div class="summary-number">' . $this->total_tests . '</div>';
        $html .= '<div>Total Tests</div>';
        $html .= '</div>';
        
        $html .= '<div class="summary-card success">';
        $html .= '<div class="summary-number">' . $this->passed_tests . '</div>';
        $html .= '<div>Passed</div>';
        $html .= '</div>';
        
        $html .= '<div class="summary-card danger">';
        $html .= '<div class="summary-number">' . $this->failed_tests . '</div>';
        $html .= '<div>Failed</div>';
        $html .= '</div>';
        
        $html .= '<div class="summary-card ' . ($pass_percentage >= 90 ? 'success' : ($pass_percentage >= 70 ? 'info' : 'danger')) . '">';
        $html .= '<div class="summary-number">' . $pass_percentage . '%</div>';
        $html .= '<div>Success Rate</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render recommendations
     */
    private function render_recommendations() {
        $html = '<div class="recommendations">';
        $html .= '<h3>🎯 Recommendations & Next Steps</h3>';
        
        // Analyze results and provide recommendations
        $failed_shortcodes = array_filter($this->test_results, function($result) {
            return !$result['pass'];
        });
        
        if (empty($failed_shortcodes)) {
            $html .= '<div class="recommendation-item low">';
            $html .= '<strong>🎉 Excellent!</strong> All shortcodes are functioning properly. ';
            $html .= 'Your shortcode system is ready for production use.';
            $html .= '</div>';
        } else {
            foreach ($failed_shortcodes as $shortcode => $result) {
                if (!$result['registered']) {
                    $html .= '<div class="recommendation-item high">';
                    $html .= '<strong>❌ Critical:</strong> Shortcode [' . $shortcode . '] is not registered. ';
                    $html .= 'Check if the shortcode file is loaded and the add_shortcode() call is working.';
                    $html .= '</div>';
                }
                
                if (!$result['basic_render']) {
                    $html .= '<div class="recommendation-item high">';
                    $html .= '<strong>❌ Critical:</strong> Shortcode [' . $shortcode . '] fails to render. ';
                    $html .= 'Check the callback function for errors or missing dependencies.';
                    $html .= '</div>';
                }
                
                if ($result['elements_present'] === false) {
                    $html .= '<div class="recommendation-item medium">';
                    $html .= '<strong>⚠️ Warning:</strong> Shortcode [' . $shortcode . '] missing expected HTML elements. ';
                    $html .= 'Template may be incomplete or CSS classes may be incorrect.';
                    $html .= '</div>';
                }
            }
        }
        
        // Performance recommendations
        $large_outputs = array_filter($this->test_results, function($result) {
            return $result['output_length'] > 10000;
        });
        
        if (!empty($large_outputs)) {
            $html .= '<div class="recommendation-item low">';
            $html .= '<strong>💡 Optimization:</strong> Some shortcodes generate large output (>10KB). ';
            $html .= 'Consider adding caching or lazy loading for better performance.';
            $html .= '</div>';
        }
        
        // General recommendations
        $html .= '<div class="recommendation-item low">';
        $html .= '<strong>🚀 Next Steps:</strong>';
        $html .= '<ul>';
        $html .= '<li>Test shortcodes on the frontend with real content</li>';
        $html .= '<li>Verify mobile responsiveness of all shortcodes</li>';
        $html .= '<li>Check JavaScript functionality in browser</li>';
        $html .= '<li>Test with different themes and plugins</li>';
        $html .= '<li>Validate HTML output and accessibility</li>';
        $html .= '</ul>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>'; // Close container
        
        return $html;
    }
}

// Run the tests
if (function_exists('do_shortcode')) {
    $tester = new BMS_Shortcode_Comprehensive_Test();
    $tester->run_all_tests();
} else {
    echo '<div style="padding: 20px; background: #fef2f2; border: 2px solid #dc2626; border-radius: 8px; margin: 20px; font-family: Arial, sans-serif;">';
    echo '<h2>❌ WordPress Environment Not Available</h2>';
    echo '<p>This test must be run within a WordPress environment where <code>do_shortcode()</code> is available.</p>';
    echo '<p><strong>To run this test:</strong></p>';
    echo '<ul>';
    echo '<li>Access via WordPress admin dashboard</li>';
    echo '<li>Add to a WordPress page/post for testing</li>';
    echo '<li>Run through WP-CLI if available</li>';
    echo '</ul>';
    echo '</div>';
}
