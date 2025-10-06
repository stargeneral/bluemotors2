<?php
/**
 * AJAX Handler for Shortcode Testing
 * Blue Motors Southampton Plugin
 * 
 * Handles AJAX requests for shortcode testing in admin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register AJAX handlers for shortcode testing
 */
add_action('wp_ajax_bms_test_shortcode', 'bms_handle_shortcode_test_ajax');

/**
 * Handle AJAX shortcode testing
 */
function bms_handle_shortcode_test_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bms_test_shortcode')) {
        wp_die('Security check failed');
    }
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $shortcode = sanitize_text_field($_POST['shortcode']);
    
    // Initialize response
    $response = array(
        'success' => true,
        'shortcode' => $shortcode,
        'message' => '',
        'output' => '',
        'errors' => array()
    );
    
    try {
        // Capture any PHP errors
        ob_start();
        
        // Enable error reporting for testing
        $old_error_reporting = error_reporting(E_ALL);
        
        // Test if shortcode exists first
        $shortcode_name = trim($shortcode, '[]');
        if (!shortcode_exists($shortcode_name)) {
            $response['success'] = false;
            $response['message'] = 'Shortcode not registered';
            wp_send_json($response);
            return;
        }
        
        // Try to execute the shortcode
        $output = do_shortcode($shortcode);
        
        // Restore error reporting
        error_reporting($old_error_reporting);
        
        // Get any buffered output (errors)
        $buffer_content = ob_get_clean();
        
        // Check for PHP errors in output
        if (!empty($buffer_content)) {
            $response['errors'][] = 'PHP output/errors: ' . $buffer_content;
        }
        
        // Check if shortcode produced output
        if (empty($output)) {
            $response['success'] = false;
            $response['message'] = 'Shortcode executed but produced no output';
        } else {
            $response['success'] = true;
            $response['message'] = 'Shortcode executed successfully';
            $response['output'] = strlen($output) . ' characters generated';
        }
        
        // Check for specific error patterns in output
        if (strpos($output, 'Fatal error') !== false) {
            $response['success'] = false;
            $response['message'] = 'Fatal error detected';
            $response['errors'][] = 'Fatal error found in shortcode output';
        }
        
        if (strpos($output, 'class-bms-error') !== false || strpos($output, 'bms-error') !== false) {
            $response['success'] = false;
            $response['message'] = 'Error class detected in output';
        }
        
    } catch (Exception $e) {
        ob_clean(); // Clean any buffered output
        $response['success'] = false;
        $response['message'] = 'Exception: ' . $e->getMessage();
        $response['errors'][] = $e->getTraceAsString();
    } catch (Error $e) {
        ob_clean(); // Clean any buffered output
        $response['success'] = false;
        $response['message'] = 'PHP Error: ' . $e->getMessage();
        $response['errors'][] = $e->getTraceAsString();
    }
    
    wp_send_json($response);
}

/**
 * Enhanced shortcode testing with detailed analysis
 */
function bms_test_shortcode_detailed($shortcode) {
    $results = array(
        'shortcode' => $shortcode,
        'registered' => false,
        'function_exists' => false,
        'output_generated' => false,
        'errors' => array(),
        'warnings' => array(),
        'output_length' => 0,
        'execution_time' => 0
    );
    
    $shortcode_name = trim($shortcode, '[]');
    
    // Check if shortcode is registered
    global $shortcode_tags;
    $results['registered'] = isset($shortcode_tags[$shortcode_name]);
    
    if (!$results['registered']) {
        $results['errors'][] = 'Shortcode not registered in WordPress';
        return $results;
    }
    
    // Check if the handler function exists
    $handler = $shortcode_tags[$shortcode_name];
    if (is_string($handler)) {
        $results['function_exists'] = function_exists($handler);
    } elseif (is_array($handler) && count($handler) == 2) {
        $results['function_exists'] = method_exists($handler[0], $handler[1]);
    }
    
    if (!$results['function_exists']) {
        $results['errors'][] = 'Shortcode handler function/method not found';
    }
    
    // Test execution
    $start_time = microtime(true);
    
    try {
        ob_start();
        $output = do_shortcode($shortcode);
        $buffer_content = ob_get_clean();
        
        $results['execution_time'] = round((microtime(true) - $start_time) * 1000, 2); // ms
        $results['output_length'] = strlen($output);
        $results['output_generated'] = !empty($output);
        
        if (!empty($buffer_content)) {
            $results['warnings'][] = 'Unexpected output during execution: ' . $buffer_content;
        }
        
        // Check for common error patterns
        if (strpos($output, 'Fatal error') !== false) {
            $results['errors'][] = 'Fatal error detected in output';
        }
        
        if (strpos($output, 'Warning:') !== false) {
            $results['warnings'][] = 'PHP warning detected';
        }
        
    } catch (Exception $e) {
        ob_clean();
        $results['errors'][] = 'Exception: ' . $e->getMessage();
        $results['execution_time'] = round((microtime(true) - $start_time) * 1000, 2);
    }
    
    return $results;
}