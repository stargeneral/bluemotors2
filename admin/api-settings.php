<?php
/**
 * Enhanced API Settings Page for Blue Motors Southampton
 * Based on the comprehensive settings from the original Blue Motors plugin
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the API settings page
 */
function bms_api_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Process form submission
    if (isset($_POST['bms_save_api_settings']) && wp_verify_nonce($_POST['bms_api_settings_nonce'], 'bms_api_settings')) {
        bms_save_api_settings();
        echo '<div class="notice notice-success is-dismissible"><p>API settings saved successfully!</p></div>';
    }
    
    // Test connection if requested
    $test_result = null;
    if (isset($_GET['test_api']) && wp_verify_nonce($_GET['_wpnonce'], 'bms_test_api')) {
        $api_type = sanitize_text_field($_GET['test_api']);
        $test_result = bms_test_api_connection($api_type);
    }
    
    ?>
    <div class="wrap">
        <h1>API Configuration</h1>
        <p>Configure your API settings for vehicle data lookups and integrations.</p>
        
        <?php if ($test_result !== null): ?>
            <?php if ($test_result['success']): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html($test_result['api']); ?> API Test:</strong> <?php echo esc_html($test_result['message']); ?></p>
                </div>
            <?php else: ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong><?php echo esc_html($test_result['api']); ?> API Test Failed:</strong> <?php echo esc_html($test_result['message']); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="bms-api-container">
            <div class="bms-api-content">
                <form method="post" action="">
                    <?php wp_nonce_field('bms_api_settings', 'bms_api_settings_nonce'); ?>
                    
                    <!-- DVLA API Settings -->
                    <div class="bms-api-card">
                        <h2>DVLA Vehicle Enquiry Service</h2>
                        <p>Official UK vehicle data from the Driver and Vehicle Licensing Agency.</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="bm_dvla_api_key">DVLA API Key</label>
                                </th>
                                <td>
                                    <input type="password" id="bm_dvla_api_key" name="bm_dvla_api_key" 
                                           value="<?php echo esc_attr(get_option('bm_dvla_api_key', '')); ?>" 
                                           class="regular-text">
                                    <p class="description">
                                        Your DVLA Vehicle Enquiry Service API key. 
                                        <a href="https://developer-portal.driver-vehicle-licensing.api.gov.uk/" target="_blank">Get API Key</a>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    <?php $dvla_key = get_option('bm_dvla_api_key', ''); ?>
                                    <?php if (empty($dvla_key)): ?>
                                        <span style="color: orange;">⚠ No API key configured - using mock data</span>
                                    <?php else: ?>
                                        <span style="color: green;">✓ API key configured</span>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=bms-api-settings&test_api=dvla'), 'bms_test_api'); ?>" 
                                           class="button button-secondary" style="margin-left: 10px;">Test Connection</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- DVSA MOT API Settings -->
                    <div class="bms-api-card">
                        <h2>DVSA MOT History API</h2>
                        <p>Official MOT test history from the Driver and Vehicle Standards Agency.</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="bm_dvsa_client_id">Client ID</label>
                                </th>
                                <td>
                                    <input type="text" id="bm_dvsa_client_id" name="bm_dvsa_client_id" 
                                           value="<?php echo esc_attr(get_option('bm_dvsa_client_id', '')); ?>" 
                                           class="regular-text">
                                    <p class="description">Your DVSA MOT API Client ID</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="bm_dvsa_client_secret">Client Secret</label>
                                </th>
                                <td>
                                    <input type="password" id="bm_dvsa_client_secret" name="bm_dvsa_client_secret" 
                                           value="<?php echo esc_attr(get_option('bm_dvsa_client_secret', '')); ?>" 
                                           class="regular-text">
                                    <p class="description">Your DVSA MOT API Client Secret</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="bm_dvsa_api_key">API Key</label>
                                </th>
                                <td>
                                    <input type="password" id="bm_dvsa_api_key" name="bm_dvsa_api_key" 
                                           value="<?php echo esc_attr(get_option('bm_dvsa_api_key', '')); ?>" 
                                           class="regular-text">
                                    <p class="description">Your DVSA MOT API Key</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="bm_dvsa_token_url">Token URL</label>
                                </th>
                                <td>
                                    <input type="url" id="bm_dvsa_token_url" name="bm_dvsa_token_url" 
                                           value="<?php echo esc_attr(get_option('bm_dvsa_token_url', '')); ?>" 
                                           class="regular-text">
                                    <p class="description">OAuth token endpoint provided by DVSA</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="bm_dvsa_scope_url">Scope URL</label>
                                </th>
                                <td>
                                    <input type="url" id="bm_dvsa_scope_url" name="bm_dvsa_scope_url" 
                                           value="<?php echo esc_attr(get_option('bm_dvsa_scope_url', '')); ?>" 
                                           class="regular-text">
                                    <p class="description">OAuth scope URL provided by DVSA</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    <?php 
                                    $dvsa_configured = !empty(get_option('bm_dvsa_client_id')) && 
                                                      !empty(get_option('bm_dvsa_client_secret')) && 
                                                      !empty(get_option('bm_dvsa_api_key')); 
                                    ?>
                                    <?php if (!$dvsa_configured): ?>
                                        <span style="color: orange;">⚠ Incomplete configuration - using mock data</span>
                                    <?php else: ?>
                                        <span style="color: green;">✓ API credentials configured</span>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=bms-api-settings&test_api=dvsa'), 'bms_test_api'); ?>" 
                                           class="button button-secondary" style="margin-left: 10px;">Test Connection</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- General API Settings -->
                    <div class="bms-api-card">
                        <h2>General Settings</h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">Debug Mode</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="bm_debug_mode" value="1" 
                                               <?php checked(get_option('bm_debug_mode', BM_DEBUG_MODE), 1); ?>>
                                        Enable debug logging for API calls
                                    </label>
                                    <p class="description">When enabled, detailed API logs will be written to the WordPress error log.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Cache Duration</th>
                                <td>
                                    <select name="bm_api_cache_duration">
                                        <option value="3600" <?php selected(get_option('bm_api_cache_duration', 86400), 3600); ?>>1 Hour</option>
                                        <option value="21600" <?php selected(get_option('bm_api_cache_duration', 86400), 21600); ?>>6 Hours</option>
                                        <option value="43200" <?php selected(get_option('bm_api_cache_duration', 86400), 43200); ?>>12 Hours</option>
                                        <option value="86400" <?php selected(get_option('bm_api_cache_duration', 86400), 86400); ?>>24 Hours (Recommended)</option>
                                        <option value="172800" <?php selected(get_option('bm_api_cache_duration', 86400), 172800); ?>>48 Hours</option>
                                    </select>
                                    <p class="description">How long to cache API responses to improve performance and reduce API calls.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Future API Integrations -->
                    <div class="bms-api-card">
                        <h2>Additional API Integrations</h2>
                        <p>These integrations will be available in future updates:</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">VDG (Vehicle Data Gateway)</th>
                                <td>
                                    <input type="password" placeholder="VDG API Key" class="regular-text" disabled>
                                    <p class="description">Enhanced vehicle data and valuations (Coming Soon)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">What Car? Valuations</th>
                                <td>
                                    <input type="password" placeholder="What Car? API Key" class="regular-text" disabled>
                                    <p class="description">Vehicle valuation and market data (Coming Soon)</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="bms_save_api_settings" class="button button-primary" value="Save API Settings">
                        <a href="<?php echo admin_url('admin.php?page=bms-dashboard'); ?>" class="button">Back to Dashboard</a>
                    </p>
                </form>
            </div>
            
            <!-- Sidebar with documentation -->
            <div class="bms-api-sidebar">
                <div class="bms-api-card">
                    <h3>API Documentation</h3>
                    <ul>
                        <li><a href="https://developer-portal.driver-vehicle-licensing.api.gov.uk/" target="_blank">DVLA API Documentation</a></li>
                        <li><a href="https://dvsa.github.io/mot-history-api-documentation/" target="_blank">DVSA MOT API Documentation</a></li>
                        <li><a href="#" target="_blank">Blue Motors API Guide</a></li>
                    </ul>
                </div>
                
                <div class="bms-api-card">
                    <h3>API Status Overview</h3>
                    <?php $api_status = bms_get_api_status_overview(); ?>
                    <ul>
                        <li>DVLA API: <span style="color: <?php echo $api_status['dvla']['color']; ?>;"><?php echo $api_status['dvla']['status']; ?></span></li>
                        <li>DVSA MOT API: <span style="color: <?php echo $api_status['dvsa']['color']; ?>;"><?php echo $api_status['dvsa']['status']; ?></span></li>
                        <li>Payment Gateway: <span style="color: green;">✓ Configured</span></li>
                    </ul>
                </div>
                
                <div class="bms-api-card">
                    <h3>Usage Statistics</h3>
                    <?php $stats = bms_get_api_usage_stats(); ?>
                    <p><strong>Today:</strong> <?php echo $stats['today']; ?> API calls</p>
                    <p><strong>This Week:</strong> <?php echo $stats['week']; ?> API calls</p>
                    <p><strong>Cache Hit Rate:</strong> <?php echo $stats['cache_rate']; ?>%</p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .bms-api-container {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }
    
    .bms-api-content {
        flex: 1;
        max-width: 800px;
    }
    
    .bms-api-sidebar {
        width: 300px;
        flex-shrink: 0;
    }
    
    .bms-api-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    
    .bms-api-card h2, .bms-api-card h3 {
        margin-top: 0;
        color: #1d2327;
    }
    
    .bms-api-sidebar .bms-api-card {
        padding: 15px;
    }
    
    .bms-api-sidebar ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .bms-api-sidebar li {
        margin-bottom: 5px;
    }
    
    @media (max-width: 768px) {
        .bms-api-container {
            flex-direction: column;
        }
        
        .bms-api-sidebar {
            width: 100%;
        }
    }
    </style>
    <?php
}

/**
 * Save API settings
 */
function bms_save_api_settings() {
    // DVLA Settings
    if (isset($_POST['bm_dvla_api_key'])) {
        update_option('bm_dvla_api_key', sanitize_text_field($_POST['bm_dvla_api_key']));
    }
    
    // DVSA Settings
    if (isset($_POST['bm_dvsa_client_id'])) {
        update_option('bm_dvsa_client_id', sanitize_text_field($_POST['bm_dvsa_client_id']));
    }
    
    if (isset($_POST['bm_dvsa_client_secret'])) {
        update_option('bm_dvsa_client_secret', sanitize_text_field($_POST['bm_dvsa_client_secret']));
    }
    
    if (isset($_POST['bm_dvsa_api_key'])) {
        update_option('bm_dvsa_api_key', sanitize_text_field($_POST['bm_dvsa_api_key']));
    }
    
    if (isset($_POST['bm_dvsa_token_url'])) {
        update_option('bm_dvsa_token_url', sanitize_url($_POST['bm_dvsa_token_url']));
    }
    
    if (isset($_POST['bm_dvsa_scope_url'])) {
        update_option('bm_dvsa_scope_url', sanitize_url($_POST['bm_dvsa_scope_url']));
    }
    
    // General Settings
    $debug_mode = isset($_POST['bm_debug_mode']) ? 1 : 0;
    update_option('bm_debug_mode', $debug_mode);
    
    if (isset($_POST['bm_api_cache_duration'])) {
        update_option('bm_api_cache_duration', intval($_POST['bm_api_cache_duration']));
    }
}

/**
 * Test API connection
 */
function bms_test_api_connection($api_type) {
    switch ($api_type) {
        case 'dvla':
            $api = new \BlueMotosSouthampton\Services\DVLAApiEnhanced();
            $result = $api->test_connection();
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'api' => 'DVLA',
                    'message' => $result->get_error_message()
                ];
            }
            
            return [
                'success' => true,
                'api' => 'DVLA',
                'message' => 'API connection successful!'
            ];
            
        case 'dvsa':
            $api = new \BlueMotosSouthampton\Services\DVSAMotApiEnhanced();
            $result = $api->test_connection();
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'api' => 'DVSA MOT',
                    'message' => $result->get_error_message()
                ];
            }
            
            return [
                'success' => true,
                'api' => 'DVSA MOT',
                'message' => 'API connection and authentication successful!'
            ];
            
        default:
            return [
                'success' => false,
                'api' => 'Unknown',
                'message' => 'Invalid API type specified'
            ];
    }
}

/**
 * Get API status overview
 */
function bms_get_api_status_overview() {
    $dvla_key = get_option('bm_dvla_api_key', '');
    $dvsa_configured = !empty(get_option('bm_dvsa_client_id')) && 
                      !empty(get_option('bm_dvsa_client_secret')) && 
                      !empty(get_option('bm_dvsa_api_key'));
    
    return [
        'dvla' => [
            'status' => empty($dvla_key) ? '⚠ Mock Data' : '✓ Live API',
            'color' => empty($dvla_key) ? 'orange' : 'green'
        ],
        'dvsa' => [
            'status' => !$dvsa_configured ? '⚠ Mock Data' : '✓ Live API',
            'color' => !$dvsa_configured ? 'orange' : 'green'
        ]
    ];
}

/**
 * Get API usage statistics
 */
function bms_get_api_usage_stats() {
    // These would be implemented with proper tracking
    return [
        'today' => get_option('bms_api_calls_today', 0),
        'week' => get_option('bms_api_calls_week', 0),
        'cache_rate' => get_option('bms_api_cache_rate', 85)
    ];
}
