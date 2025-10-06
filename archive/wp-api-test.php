<?php
/**
 * WordPress API Test Script
 * Upload this as wp-api-test.php to your WordPress root directory
 * Access via: https://bluemotorsgarage.com/wp-api-test.php?key=your-secret-key
 */

$SECRET_KEY = 'blue-tyuetry-1976Hg'; // Change this!

if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die('Access denied. Use: ?key=' . $SECRET_KEY);
}

// Load WordPress
require_once 'wp-load.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>WordPress API Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .code-block { background: #f8f9fa; border: 1px solid #e9ecef; padding: 10px; margin: 10px 0; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 WordPress API Test Results</h1>
    <p><strong>Server:</strong> <?php echo $_SERVER['HTTP_HOST']; ?> | <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

    <h2>1. WordPress Environment</h2>
    <table>
        <tr><th>Setting</th><th>Value</th></tr>
        <tr><td>WordPress Version</td><td><?php echo get_bloginfo('version'); ?></td></tr>
        <tr><td>Site URL</td><td><?php echo home_url(); ?></td></tr>
        <tr><td>Plugin Active</td><td><?php echo is_plugin_active('blue-motors-southampton/blue-motors-southampton.php') ? '✅ Yes' : '❌ No'; ?></td></tr>
    </table>

    <h2>2. API Credentials Check</h2>
    <?php
    $dvla_key = get_option('bm_dvla_api_key', '');
    $dvsa_client_id = get_option('bm_dvsa_client_id', '');
    $dvsa_client_secret = get_option('bm_dvsa_client_secret', '');
    $dvsa_api_key = get_option('bm_dvsa_api_key', '');
    $dvsa_token_url = get_option('bm_dvsa_token_url', '');
    $dvsa_scope_url = get_option('bm_dvsa_scope_url', '');
    
    echo '<table>';
    echo '<tr><th>Credential</th><th>Status</th><th>Length</th></tr>';
    
    $credentials = [
        'DVLA API Key' => $dvla_key,
        'DVSA Client ID' => $dvsa_client_id,
        'DVSA Client Secret' => $dvsa_client_secret,
        'DVSA API Key' => $dvsa_api_key,
        'DVSA Token URL' => $dvsa_token_url,
        'DVSA Scope URL' => $dvsa_scope_url];
    
    foreach ($credentials as $name => $value) {
        $status = !empty($value) ? '✅ Set' : '❌ Empty';
        $length = !empty($value) ? strlen($value) . ' chars' : '0 chars';
        
        if ($value === 'keycovered-for-security') {
            $status = '⚠️ Placeholder';
        }
        
        echo "<tr><td>{$name}</td><td>{$status}</td><td>{$length}</td></tr>";
    }
    echo '</table>';
    ?>

    <h2>3. Live API Tests</h2>
    
    <h3>DVLA API Test</h3>
    <?php
    if (class_exists('BlueMotosSouthampton\\Services\\DVLAApiEnhanced')) {
        echo '<p class="success">✅ DVLAApiEnhanced class found</p>';
        
        try {
            $dvla_api = new BlueMotosSouthampton\Services\DVLAApiEnhanced();
            $result = $dvla_api->test_connection();
            
            if (is_wp_error($result)) {
                echo '<p class="error">❌ DVLA Test Failed: ' . $result->get_error_message() . '</p>';
                echo '<p><strong>Error Code:</strong> ' . $result->get_error_code() . '</p>';
                
                $error_data = $result->get_error_data();
                if ($error_data) {
                    echo '<div class="code-block">' . print_r($error_data, true) . '</div>';
                }
            } else {
                echo '<p class="success">✅ DVLA API Connection Successful!</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ DVLA Exception: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p class="error">❌ DVLAApiEnhanced class not found</p>';
    }
    ?>

    <h3>DVSA API Test</h3>
    <?php
    if (class_exists('BlueMotosSouthampton\\Services\\DVSAMotApiEnhanced')) {
        echo '<p class="success">✅ DVSAMotApiEnhanced class found</p>';
        
        try {
            $dvsa_api = new BlueMotosSouthampton\Services\DVSAMotApiEnhanced();
            $result = $dvsa_api->test_connection();
            
            if (is_wp_error($result)) {
                echo '<p class="error">❌ DVSA Test Failed: ' . $result->get_error_message() . '</p>';
                echo '<p><strong>Error Code:</strong> ' . $result->get_error_code() . '</p>';
                
                $error_data = $result->get_error_data();
                if ($error_data) {
                    echo '<div class="code-block">' . print_r($error_data, true) . '</div>';
                }
            } else {
                echo '<p class="success">✅ DVSA API Connection Successful!</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ DVSA Exception: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p class="error">❌ DVSAMotApiEnhanced class not found</p>';
    }
    ?>

    <h2>4. WordPress HTTP Test</h2>
    <?php
    echo '<h4>Testing wp_remote_get to DVLA API:</h4>';
    
    $response = wp_remote_get('https://driver-vehicle-licensing.api.gov.uk', [
        'timeout' => 15,
        'user-agent' => 'Blue Motors WordPress Test/1.0']);
    
    if (is_wp_error($response)) {
        echo '<p class="error">❌ WordPress HTTP Failed: ' . $response->get_error_message() . '</p>';
        echo '<p><strong>Error Code:</strong> ' . $response->get_error_code() . '</p>';
    } else {
        $code = wp_remote_retrieve_response_code($response);
        echo '<p class="success">✅ WordPress HTTP Success: HTTP ' . $code . '</p>';
    }
    
    echo '<h4>Testing wp_remote_post to DVLA API with API key:</h4>';
    
    if (!empty($dvla_key) && $dvla_key !== 'keycovered-for-security') {
        $response = wp_remote_post('https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles', [
            'headers' => [
                'x-api-key' => $dvla_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => json_encode(['registrationNumber' => 'AB12CDE']),
            'timeout' => 15]);
        
        if (is_wp_error($response)) {
            echo '<p class="error">❌ DVLA API POST Failed: ' . $response->get_error_message() . '</p>';
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            echo '<p><strong>HTTP Code:</strong> ' . $code . '</p>';
            
            if ($code == 404) {
                echo '<p class="success">✅ DVLA API Working! (404 = test vehicle not found, which is expected)</p>';
            } elseif ($code == 403) {
                echo '<p class="error">❌ DVLA API Access Denied - Check API key permissions</p>';
            } else {
                echo '<p class="warning">⚠️ DVLA API Response: HTTP ' . $code . '</p>';
                echo '<div class="code-block">' . htmlspecialchars(substr($body, 0, 200)) . '</div>';
            }
        }
    } else {
        echo '<p class="warning">⚠️ No DVLA API key configured - skipping test</p>';
    }
    ?>

    <h2>5. Summary</h2>
    <div style="background: #d4edda; padding: 15px; border-radius: 5px;">
        <h4>What This Test Reveals:</h4>
        <ul>
            <li>Whether your WordPress plugin can load API classes</li>
            <li>Whether your stored API credentials are valid</li>
            <li>Whether WordPress HTTP functions can reach APIs</li>
            <li>Exact error messages from your live environment</li>
        </ul>
        
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Contact StartTLD about server firewall (Priority 1)</li>
            <li>Test APIs again after firewall fix</li>
            <li>Check for specific credential issues</li>
        </ol>
    </div>

</div>
</body>
</html>