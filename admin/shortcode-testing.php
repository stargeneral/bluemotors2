<?php
/**
 * Shortcode Testing Admin Page
 * Blue Motors Southampton Plugin
 * 
 * Provides admin interface for testing all shortcodes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode Testing Admin Page
 */
function bms_shortcode_testing_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'blue-motors-southampton'));
    }
    
    // Handle test actions
    if (isset($_POST['run_shortcode_test']) && wp_verify_nonce($_POST['_wpnonce'], 'bms_shortcode_test')) {
        $shortcode_to_test = sanitize_text_field($_POST['shortcode_to_test']);
        $test_attributes = sanitize_text_field($_POST['test_attributes']);
        
        $full_shortcode = $shortcode_to_test;
        if (!empty($test_attributes)) {
            $full_shortcode = rtrim($shortcode_to_test, ']') . ' ' . $test_attributes . ']';
        }
        
        $test_output = do_shortcode($full_shortcode);
        $test_performed = true;
    }
    
    ?>
    <div class="wrap">
        <h1>🧪 Shortcode Testing & Validation</h1>
        <p>Test all Blue Motors Southampton shortcodes to ensure they're working properly.</p>
        
        <!-- Quick Test Section -->
        <div class="shortcode-quick-test" style="background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0;">
            <h2>🔬 Quick Shortcode Test</h2>
            
            <form method="post" action="">
                <?php wp_nonce_field('bms_shortcode_test'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="shortcode_to_test">Shortcode to Test:</label></th>
                        <td>
                            <select name="shortcode_to_test" id="shortcode_to_test" class="regular-text">
                                <option value="[bms_vehicle_lookup]">Vehicle Lookup</option>
                                <option value="[bms_booking_form]">Booking Form</option>
                                <option value="[bms_service_cards]">Service Cards</option>
                                <option value="[bms_enhanced_services]">Enhanced Services</option>
                                <option value="[bms_service_list]">Service List</option>
                                <option value="[bms_tyre_search]">Tyre Search</option>
                                <option value="[bms_smart_scheduler]">Smart Scheduler</option>
                                <option value="[bms_location_info]">Location Info</option>
                                <option value="[bms_opening_hours]">Opening Hours</option>
                                <option value="[bms_contact_form]">Contact Form</option>
                                <option value="[bms_vs_f1]">VS industry leaders Comparison</option>
                                <option value="[bms_comparison_table]">Comparison Table</option>
                                <option value="[vehicle_lookup]">Vehicle Lookup (Alias)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="test_attributes">Additional Attributes:</label></th>
                        <td>
                            <input type="text" name="test_attributes" id="test_attributes" class="regular-text" 
                                   placeholder='theme="compact" show_competitive="true"' />
                            <p class="description">Add any attributes you want to test (optional).</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Test Shortcode', 'primary', 'run_shortcode_test'); ?>
            </form>
            
            <?php if (isset($test_performed)): ?>
            <div class="shortcode-test-results" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007cba;">
                <h3>Test Results</h3>
                <p><strong>Tested Shortcode:</strong> <code><?php echo esc_html($full_shortcode); ?></code></p>
                <p><strong>Output Length:</strong> <?php echo number_format(strlen($test_output)); ?> characters</p>
                
                <div style="max-height: 400px; overflow-y: auto; background: white; border: 1px solid #ddd; padding: 15px; margin-top: 10px;">
                    <h4>Rendered Output:</h4>
                    <?php echo $test_output; ?>
                </div>
                
                <details style="margin-top: 15px;">
                    <summary style="cursor: pointer; font-weight: bold;">View Raw HTML</summary>
                    <pre style="background: #2d3748; color: #e2e8f0; padding: 15px; overflow-x: auto; font-size: 12px; margin-top: 10px;"><?php echo esc_html($test_output); ?></pre>
                </details>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Comprehensive Test Section -->
        <div class="shortcode-comprehensive-test" style="background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0;">
            <h2>🧪 Run Comprehensive Test Suite</h2>
            <p>Test all shortcodes at once to identify any issues.</p>
            
            <div id="comprehensive-test-container">
                <button type="button" id="run-comprehensive-tests" class="button button-secondary">
                    Run All Tests
                </button>
                <div id="test-progress" style="display: none; margin: 15px 0;">
                    <div style="background: #ddd; border-radius: 10px; overflow: hidden;">
                        <div id="test-progress-bar" style="width: 0%; height: 20px; background: #007cba; transition: width 0.3s ease;"></div>
                    </div>
                    <p id="test-status">Preparing tests...</p>
                </div>
                <div id="comprehensive-results"></div>
            </div>
        </div>
        
        <!-- Tyre Service Debug Test -->
        <div class="tyre-service-debug" style="background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0;">
            <h2>🛞 Tyre Service Debug Test</h2>
            <p>Test tyre search functionality and debug any errors.</p>
            
            <div class="tyre-debug-buttons" style="margin-bottom: 20px;">
                <button type="button" id="test-tyre-classes" class="button button-primary">
                    Test Class Loading
                </button>
                <button type="button" id="test-tyre-ajax" class="button button-secondary">
                    Test AJAX Search
                </button>
                <button type="button" id="test-tyre-shortcode" class="button button-secondary">
                    Test Shortcode
                </button>
            </div>
            
            <div id="tyre-debug-results"></div>
        </div>
        
        <!-- Shortcode Reference -->
        <div class="shortcode-reference" style="background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0;">
            <h2>📖 Shortcode Reference</h2>
            
            <div class="shortcode-categories">
                
                <h3>🚗 Booking & Vehicle Shortcodes</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Description</th>
                            <th>Example Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[bms_vehicle_lookup]</code></td>
                            <td>DVLA vehicle lookup with MOT history</td>
                            <td><code>[bms_vehicle_lookup show_mot_history="true"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[bms_booking_form]</code></td>
                            <td>Main service booking form</td>
                            <td><code>[bms_booking_form service="mot_test"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vehicle_lookup]</code></td>
                            <td>Alias for bms_vehicle_lookup</td>
                            <td><code>[vehicle_lookup]</code></td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>🔧 Service Display Shortcodes</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Description</th>
                            <th>Example Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[bms_service_cards]</code></td>
                            <td>Display services in card format</td>
                            <td><code>[bms_service_cards columns="3"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[bms_enhanced_services]</code></td>
                            <td>Advanced service cards with features</td>
                            <td><code>[bms_enhanced_services category="all"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[bms_service_list]</code></td>
                            <td>Services in list format</td>
                            <td><code>[bms_service_list show_prices="true"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[bms_service_card]</code></td>
                            <td>Single service display</td>
                            <td><code>[bms_service_card service="mot_test"]</code></td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>🛞 Tyre Services</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Description</th>
                            <th>Example Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[bms_tyre_search]</code></td>
                            <td>Tyre search and booking interface</td>
                            <td><code>[bms_tyre_search style="compact"]</code></td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>🤖 Smart Features</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Description</th>
                            <th>Example Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[bms_smart_scheduler]</code></td>
                            <td>AI-powered appointment scheduling</td>
                            <td><code>[bms_smart_scheduler max_suggestions="5"]</code></td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>📍 Location & Contact</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Description</th>
                            <th>Example Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[bms_location_info]</code></td>
                            <td>Business location and contact info</td>
                            <td><code>[bms_location_info style="card"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[bms_opening_hours]</code></td>
                            <td>Opening hours only</td>
                            <td><code>[bms_opening_hours]</code></td>
                        </tr>
                        <tr>
                            <td><code>[bms_contact_form]</code></td>
                            <td>Contact form with location</td>
                            <td><code>[bms_contact_form title="Get In Touch"]</code></td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>🏆 Professional Advantage</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Description</th>
                            <th>Example Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[bms_vs_f1]</code></td>
                            <td>Comparison with industry leaders</td>
                            <td><code>[bms_vs_f1 style="table"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[bms_comparison_table]</code></td>
                            <td>Standalone comparison table</td>
                            <td><code>[bms_comparison_table title="Our Advantages"]</code></td>
                        </tr>
                    </tbody>
                </table>
                
            </div>
        </div>
        
        <!-- Troubleshooting Guide -->
        <div class="shortcode-troubleshooting" style="background: #fff3cd; border: 1px solid #f59e0b; border-radius: 4px; padding: 20px; margin: 20px 0;">
            <h2>🛠️ Troubleshooting Guide</h2>
            
            <h4>Common Issues & Solutions:</h4>
            <ul>
                <li><strong>Shortcode shows as plain text:</strong> The shortcode isn't registered. Check if the shortcode file is loaded.</li>
                <li><strong>Empty output:</strong> Function may have errors or missing dependencies. Check error logs.</li>
                <li><strong>Missing styles:</strong> CSS files may not be enqueued. Check wp_enqueue_style calls.</li>
                <li><strong>JavaScript not working:</strong> Check if required JS files are loaded and no console errors.</li>
                <li><strong>Database errors:</strong> Ensure database tables are created via Database Status page.</li>
            </ul>
            
            <h4>Performance Tips:</h4>
            <ul>
                <li>Use caching for shortcodes that generate complex output</li>
                <li>Lazy load heavy shortcodes on mobile devices</li>
                <li>Test shortcode combinations for conflicts</li>
                <li>Monitor page load times when using multiple shortcodes</li>
            </ul>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#run-comprehensive-tests').click(function() {
            var button = $(this);
            var container = $('#comprehensive-results');
            var progress = $('#test-progress');
            var progressBar = $('#test-progress-bar');
            var status = $('#test-status');
            
            button.prop('disabled', true).text('Running Tests...');
            progress.show();
            container.html('');
            
            // Simulate comprehensive test - in a real implementation, this would use AJAX
            var shortcodes = [
                'bms_vehicle_lookup',
                'bms_booking_form', 
                'bms_service_cards',
                'bms_enhanced_services',
                'bms_service_list',
                'bms_tyre_search',
                'bms_smart_scheduler',
                'bms_location_info',
                'bms_opening_hours',
                'bms_contact_form',
                'bms_vs_f1',
                'bms_comparison_table',
                'vehicle_lookup'
            ];
            
            var currentTest = 0;
            var totalTests = shortcodes.length;
            
            function runNextTest() {
                if (currentTest >= totalTests) {
                    button.prop('disabled', false).text('Run All Tests');
                    status.text('All tests completed!');
                    progressBar.css('width', '100%');
                    setTimeout(() => progress.hide(), 2000);
                    return;
                }
                
                var shortcode = shortcodes[currentTest];
                var progress_percent = ((currentTest + 1) / totalTests) * 100;
                
                progressBar.css('width', progress_percent + '%');
                status.text('Testing [' + shortcode + ']... (' + (currentTest + 1) + '/' + totalTests + ')');
                
                // Simulate test with AJAX call
                $.post(ajaxurl, {
                    action: 'bms_test_shortcode',
                    shortcode: '[' + shortcode + ']',
                    nonce: '<?php echo wp_create_nonce("bms_test_shortcode"); ?>'
                }, function(response) {
                    var resultClass = response.success ? 'success' : 'error';
                    var icon = response.success ? '✅' : '❌';
                    var message = response.message || (response.success ? 'PASS' : 'FAIL');
                    
                    var resultHtml = '<div style="padding: 10px; margin: 5px 0; background: ' + 
                        (resultClass === 'success' ? '#d1fae5' : '#fee2e2') + '; border-radius: 4px;">' +
                        icon + ' <strong>[' + shortcode + ']</strong> - ' + message;
                    
                    if (response.output) {
                        resultHtml += '<br><small style="color: #666;">Output: ' + response.output + '</small>';
                    }
                    
                    if (response.errors && response.errors.length > 0) {
                        resultHtml += '<br><small style="color: #d32f2f;">Errors: ' + response.errors.join(', ') + '</small>';
                    }
                    
                    resultHtml += '</div>';
                    
                    container.append(resultHtml);
                    
                    currentTest++;
                    setTimeout(runNextTest, 500);
                }).fail(function(xhr, status, error) {
                    // Handle AJAX failure with more detail
                    container.append(
                        '<div style="padding: 10px; margin: 5px 0; background: #fee2e2; border-radius: 4px;">' +
                        '❌ <strong>[' + shortcode + ']</strong> - AJAX Error: ' + error + 
                        '<br><small>Status: ' + status + '</small></div>'
                    );
                    currentTest++;
                    setTimeout(runNextTest, 500);
                });
            }
            
            runNextTest();
        });
        
        // Quick shortcode selector
        $('#shortcode_to_test').change(function() {
            var shortcode = $(this).val();
            var examples = {
                '[bms_vehicle_lookup]': 'title="Check Your Car" show_mot_history="true"',
                '[bms_booking_form]': 'service="mot_test" theme="blue"',
                '[bms_service_cards]': 'columns="2" show_booking_buttons="true"',
                '[bms_enhanced_services]': 'category="all" show_comparison="true"',
                '[bms_service_list]': 'style="list" show_prices="true"',
                '[bms_tyre_search]': 'style="compact" competitive_messaging="true"',
                '[bms_location_info]': 'style="card" show_map="false"',
                '[bms_vs_f1]': 'style="table" show_title="true"'
            };
            
            if (examples[shortcode]) {
                $('#test_attributes').val(examples[shortcode]);
            }
        });
        
        // Tyre Service Debug Tests
        $('#test-tyre-classes').click(function() {
            var button = $(this);
            var results = $('#tyre-debug-results');
            
            button.prop('disabled', true).text('Testing Classes...');
            
            $.post(ajaxurl, {
                action: 'bms_debug_tyre_classes',
                nonce: '<?php echo wp_create_nonce("bms_debug_tyre"); ?>'
            }, function(response) {
                if (response.success) {
                    var html = '<div style="background: #d1fae5; padding: 15px; margin: 10px 0; border-radius: 4px;">';
                    html += '<h4>✅ Class Loading Test Results</h4>';
                    html += '<ul>';
                    for (var key in response.data) {
                        var status = response.data[key] ? '✅' : '❌';
                        html += '<li>' + status + ' ' + key + '</li>';
                    }
                    html += '</ul></div>';
                    results.html(html);
                } else {
                    results.html('<div style="background: #fee2e2; padding: 15px; margin: 10px 0; border-radius: 4px;">❌ Error: ' + response.data + '</div>');
                }
                button.prop('disabled', false).text('Test Class Loading');
            });
        });
        
        $('#test-tyre-ajax').click(function() {
            var button = $(this);
            var results = $('#tyre-debug-results');
            
            button.prop('disabled', true).text('Testing AJAX...');
            
            // Test the AJAX endpoint that was failing
            $.post(ajaxurl, {
                action: 'bms_search_tyres_by_reg',
                registration: 'VF19XKX',
                nonce: '<?php echo wp_create_nonce("bms_vehicle_lookup"); ?>'
            }, function(response) {
                var html = '<div style="background: #d1fae5; padding: 15px; margin: 10px 0; border-radius: 4px;">';
                html += '<h4>✅ AJAX Test Results</h4>';
                html += '<p><strong>Registration tested:</strong> VF19XKX</p>';
                html += '<p><strong>Response:</strong> ' + JSON.stringify(response, null, 2) + '</p>';
                html += '</div>';
                results.html(html);
                button.prop('disabled', false).text('Test AJAX Search');
            }).fail(function(xhr, status, error) {
                var errorData = xhr.responseText || 'No response text';
                results.html('<div style="background: #fee2e2; padding: 15px; margin: 10px 0; border-radius: 4px;"><h4>❌ AJAX Test Failed</h4><p><strong>Error:</strong> ' + error + '</p><p><strong>Status:</strong> ' + status + '</p><p><strong>Response:</strong> <pre>' + errorData + '</pre></p></div>');
                button.prop('disabled', false).text('Test AJAX Search');
            });
        });
        
        $('#test-tyre-shortcode').click(function() {
            var button = $(this);
            var results = $('#tyre-debug-results');
            
            button.prop('disabled', true).text('Testing Shortcode...');
            
            $.post(ajaxurl, {
                action: 'bms_debug_tyre_shortcode',
                nonce: '<?php echo wp_create_nonce("bms_debug_tyre"); ?>'
            }, function(response) {
                if (response.success) {
                    var html = '<div style="background: #d1fae5; padding: 15px; margin: 10px 0; border-radius: 4px;">';
                    html += '<h4>✅ Shortcode Test Results</h4>';
                    html += '<p><strong>Output length:</strong> ' + response.data.length + ' characters</p>';
                    html += '<div style="max-height: 300px; overflow-y: auto; background: white; border: 1px solid #ddd; padding: 10px; margin-top: 10px;">';
                    html += response.data.output;
                    html += '</div></div>';
                    results.html(html);
                } else {
                    results.html('<div style="background: #fee2e2; padding: 15px; margin: 10px 0; border-radius: 4px;">❌ Shortcode Error: ' + response.data + '</div>');
                }
                button.prop('disabled', false).text('Test Shortcode');
            });
        });
    });
    </script>
    
    <style>
    .shortcode-categories h3 {
        color: #1e3a8a;
        margin-top: 30px;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .wp-list-table code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .wp-list-table td {
        vertical-align: top;
        padding: 12px 8px;
    }
    
    .wp-list-table th {
        background: #f8fafc;
        font-weight: 600;
    }
    
    details summary {
        padding: 10px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        margin-bottom: 5px;
    }
    
    details[open] summary {
        border-bottom: none;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
    </style>
    
    <?php
}

/**
 * AJAX handler for shortcode testing
 */
add_action('wp_ajax_bms_test_shortcode', 'bms_ajax_test_shortcode');

function bms_ajax_test_shortcode() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_test_shortcode')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $shortcode = sanitize_text_field($_POST['shortcode']);
    
    try {
        $output = do_shortcode($shortcode);
        $success = !empty($output) && $output !== $shortcode;
        
        wp_send_json_success([
            'shortcode' => $shortcode,
            'success' => $success,
            'output_length' => strlen($output),
            'has_html' => $output !== strip_tags($output)
        ]);
    } catch (Exception $e) {
        wp_send_json_error([
            'shortcode' => $shortcode,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * AJAX handler for tyre class debugging
 */
add_action('wp_ajax_bms_debug_tyre_classes', 'bms_ajax_debug_tyre_classes');

function bms_ajax_debug_tyre_classes() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_debug_tyre')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $results = [];
    
    try {
        // Test class existence
        $results['VehicleLookupEnhanced class exists'] = class_exists('BlueMotosSouthampton\Services\VehicleLookupEnhanced');
        $results['TyreService class exists'] = class_exists('BlueMotosSouthampton\Services\TyreService');
        $results['Autoloader function exists'] = function_exists('bms_autoloader');
        
        // Test class instantiation
        if (class_exists('BlueMotosSouthampton\Services\VehicleLookupEnhanced')) {
            $vehicle_lookup = new \BlueMotosSouthampton\Services\VehicleLookupEnhanced();
            $results['VehicleLookupEnhanced instantiation'] = ($vehicle_lookup !== null);
            $results['lookup() method exists'] = method_exists($vehicle_lookup, 'lookup');
            $results['lookup_vehicle() method exists'] = method_exists($vehicle_lookup, 'lookup_vehicle');
        } else {
            $results['VehicleLookupEnhanced instantiation'] = false;
            $results['lookup() method exists'] = false;
            $results['lookup_vehicle() method exists'] = false;
        }
        
        if (class_exists('BlueMotosSouthampton\Services\TyreService')) {
            $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
            $results['TyreService instantiation'] = ($tyre_service !== null);
            $results['search_by_registration() method exists'] = method_exists($tyre_service, 'search_by_registration');
        } else {
            $results['TyreService instantiation'] = false;
            $results['search_by_registration() method exists'] = false;
        }
        
        wp_send_json_success($results);
        
    } catch (Exception $e) {
        wp_send_json_error('Exception: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for tyre shortcode debugging
 */
add_action('wp_ajax_bms_debug_tyre_shortcode', 'bms_ajax_debug_tyre_shortcode');

function bms_ajax_debug_tyre_shortcode() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_debug_tyre')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    try {
        $output = do_shortcode('[bms_tyre_search]');
        
        wp_send_json_success([
            'output' => $output,
            'length' => strlen($output),
            'is_html' => $output !== strip_tags($output)
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error('Exception: ' . $e->getMessage());
    }
}
