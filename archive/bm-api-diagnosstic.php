<?php
/**
 * Blue Motors API Comprehensive Diagnostic Script
 * 
 * Upload this file to your WordPress root directory and access it via:
 * https://bluemotorsgarage.com/bm-api-diagnostic.php
 * 
 * This script will identify all API issues and provide specific fixes
 */

// Security check - only run if logged in as admin or if secret key is provided
session_start();

// Set secret key for direct access (change this!)
$SECRET_KEY = 'blue-motors-diagnostic-2025';

$is_authenticated = false;

if (isset($_GET['key']) && $_GET['key'] === $SECRET_KEY) {
    $is_authenticated = true;
} elseif (file_exists('wp-load.php')) {
    require_once 'wp-load.php';
    if (current_user_can('manage_options')) {
        $is_authenticated = true;
    }
}

if (!$is_authenticated) {
    die('Access denied. Use: ?key=' . $SECRET_KEY);
}

// Start output with styling
?>
<!DOCTYPE html>
<html>
<head>
    <title>Blue Motors API Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #fafafa; }
        .success { color: #28a745; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; font-weight: bold; }
        .code-block { background: #f8f9fa; border: 1px solid #e9ecef; padding: 10px; margin: 10px 0; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
        .recommendation { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .critical { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 4px; }
        h1, h2, h3 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .status-ok { background-color: #d4edda; }
        .status-warning { background-color: #fff3cd; }
        .status-error { background-color: #f8d7da; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 Blue Motors API Comprehensive Diagnostic</h1>
    <p><strong>Server:</strong> <?php echo $_SERVER['HTTP_HOST']; ?> | <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

<?php

// Test results storage
$issues = [];
$critical_issues = [];
$recommendations = [];

// ==========================================
// TEST 1: Class Conflict Detection
// ==========================================
echo '<div class="test-section">';
echo '<h2>1. 🔍 Class Conflict Detection</h2>';

$wp_loaded = defined('ABSPATH');
$plugin_dir = $wp_loaded ? WP_PLUGIN_DIR . '/blue-motors-southampton/' : 'wp-content/plugins/blue-motors-southampton/';

$api_files = [
    'dvla_original' => $plugin_dir . 'includes/services/class-dvla-api.php',
    'dvla_enhanced' => $plugin_dir . 'includes/services/class-dvla-api-enhanced.php',
    'dvsa_original' => $plugin_dir . 'includes/services/class-dvsa-mot-api.php',
    'dvsa_enhanced' => $plugin_dir . 'includes/services/class-dvsa-mot-api-enhanced.php'];

echo '<table>';
echo '<tr><th>File</th><th>Status</th><th>Size</th><th>Modified</th></tr>';

$conflicts = [];
foreach ($api_files as $key => $file) {
    $exists = file_exists($file);
    $status_class = $exists ? 'status-ok' : 'status-error';
    $status_text = $exists ? '✅ EXISTS' : '❌ NOT FOUND';
    
    echo "<tr class='{$status_class}'>";
    echo "<td>" . basename($file) . "</td>";
    echo "<td>{$status_text}</td>";
    
    if ($exists) {
        $size = round(filesize($file) / 1024, 1) . ' KB';
        $modified = date('Y-m-d H:i:s', filemtime($file));
        echo "<td>{$size}</td><td>{$modified}</td>";
        
        if (strpos($key, 'original') !== false) {
            $conflicts[] = $key;
        }
    } else {
        echo "<td>-</td><td>-</td>";
    }
    echo "</tr>";
}
echo '</table>';

if (!empty($conflicts)) {
    $critical_issues[] = "Class conflicts detected: Both original and enhanced API classes exist";
    echo '<div class="critical">';
    echo '<h4>🚨 CRITICAL: Class Conflicts Detected</h4>';
    echo '<p>You have both original and enhanced API classes. This causes conflicts and wrong credential loading.</p>';
    echo '<p><strong>Solution:</strong> Rename original files to .backup extensions:</p>';
    foreach ($conflicts as $conflict) {
        $file = $api_files[$conflict];
        echo "<p>• Rename <code>" . basename($file) . "</code> to <code>" . basename($file) . ".backup</code></p>";
    }
    echo '</div>';
}

echo '</div>';

// ==========================================
// TEST 2: Network Connectivity
// ==========================================
echo '<div class="test-section">';
echo '<h2>2. 🌐 Network Connectivity Tests</h2>';

$connectivity_tests = [
    'Google' => 'https://www.google.com',
    'DVLA API' => 'https://driver-vehicle-licensing.api.gov.uk',
    'DVSA API' => 'https://history.mot.api.gov.uk'];

echo '<table>';
echo '<tr><th>Service</th><th>Status</th><th>Response Code</th><th>Response Time</th><th>Error</th></tr>';

foreach ($connectivity_tests as $name => $url) {
    $start_time = microtime(true);
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Blue Motors Diagnostic/1.0'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $response_time = round((microtime(true) - $start_time) * 1000, 2) . 'ms';
    
    $http_response_header = $http_response_header ?? [];
    $response_code = '';
    $error = '';
    
    if ($response !== false && !empty($http_response_header)) {
        preg_match('/\d{3}/', $http_response_header[0], $matches);
        $response_code = $matches[0] ?? 'Unknown';
        $status = '✅ SUCCESS';
        $status_class = 'status-ok';
    } else {
        $error = error_get_last()['message'] ?? 'Connection failed';
        $status = '❌ FAILED';
        $status_class = 'status-error';
        
        if ($name !== 'Google') {
            $issues[] = "Cannot connect to {$name}: {$error}";
        }
    }
    
    echo "<tr class='{$status_class}'>";
    echo "<td>{$name}</td>";
    echo "<td>{$status}</td>";
    echo "<td>{$response_code}</td>";
    echo "<td>{$response_time}</td>";
    echo "<td>{$error}</td>";
    echo "</tr>";
}
echo '</table>';

echo '</div>';

// ==========================================
// TEST 3: DNS Resolution
// ==========================================
echo '<div class="test-section">';
echo '<h2>3. 🔍 DNS Resolution Test</h2>';

$dns_hosts = [
    'driver-vehicle-licensing.api.gov.uk',
    'history.mot.api.gov.uk'];

echo '<table>';
echo '<tr><th>Hostname</th><th>IP Address</th><th>Status</th></tr>';

foreach ($dns_hosts as $host) {
    $ip = gethostbyname($host);
    
    if ($ip !== $host) {
        $status = '✅ RESOLVED';
        $status_class = 'status-ok';
    } else {
        $status = '❌ FAILED';
        $status_class = 'status-error';
        $issues[] = "DNS resolution failed for {$host}";
        $ip = 'Resolution failed';
    }
    
    echo "<tr class='{$status_class}'>";
    echo "<td>{$host}</td>";
    echo "<td>{$ip}</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}
echo '</table>';

echo '</div>';

// ==========================================
// TEST 4: Server Environment
// ==========================================
echo '<div class="test-section">';
echo '<h2>4. 🖥️ Server Environment</h2>';

$server_info = [
    'Server IP' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
    'Domain' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
    'PHP Version' => PHP_VERSION,
    'cURL Available' => extension_loaded('curl') ? '✅ Yes' : '❌ No',
    'OpenSSL Available' => extension_loaded('openssl') ? '✅ Yes' : '❌ No',
    'cURL Version' => function_exists('curl_version') ? curl_version()['version'] : 'N/A',
    'SSL Version' => function_exists('curl_version') ? curl_version()['ssl_version'] : 'N/A',
    'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Not set',
    'WordPress Loaded' => $wp_loaded ? '✅ Yes' : '❌ No'];

if ($wp_loaded) {
    $server_info['WordPress Version'] = get_bloginfo('version');
    $server_info['WP_HTTP_BLOCK_EXTERNAL'] = defined('WP_HTTP_BLOCK_EXTERNAL') && WP_HTTP_BLOCK_EXTERNAL ? '❌ BLOCKED' : '✅ Allowed';
    $server_info['WP_ACCESSIBLE_HOSTS'] = defined('WP_ACCESSIBLE_HOSTS') ? WP_ACCESSIBLE_HOSTS : 'Not restricted';
}

echo '<table>';
foreach ($server_info as $key => $value) {
    $status_class = (strpos($value, '❌') !== false) ? 'status-error' : 'status-ok';
    echo "<tr class='{$status_class}'><td><strong>{$key}</strong></td><td>{$value}</td></tr>";
}
echo '</table>';

if (!extension_loaded('curl')) {
    $critical_issues[] = "cURL extension is not available - required for API calls";
}

echo '</div>';

// ==========================================
// TEST 5: API Credentials Check
// ==========================================
echo '<div class="test-section">';
echo '<h2>5. 🔑 API Credentials Status</h2>';

if ($wp_loaded) {
    $dvla_key = get_option('bm_dvla_api_key', '');
    $dvsa_client_id = get_option('bm_dvsa_client_id', '');
    $dvsa_client_secret = get_option('bm_dvsa_client_secret', '');
    $dvsa_api_key = get_option('bm_dvsa_api_key', '');
    $dvsa_token_url = get_option('bm_dvsa_token_url', '');
    $dvsa_scope_url = get_option('bm_dvsa_scope_url', '');
    
    // Check constants too
    $dvla_constant = defined('BM_DVLA_API_KEY') ? BM_DVLA_API_KEY : '';
    
    echo '<table>';
    echo '<tr><th>Credential</th><th>Database</th><th>Constant</th><th>Status</th></tr>';
    
    $credentials = [
        'DVLA API Key' => [$dvla_key, $dvla_constant],
        'DVSA Client ID' => [$dvsa_client_id, ''],
        'DVSA Client Secret' => [$dvsa_client_secret, ''],
        'DVSA API Key' => [$dvsa_api_key, ''],
        'DVSA Token URL' => [$dvsa_token_url, ''],
        'DVSA Scope URL' => [$dvsa_scope_url, '']
    ];
    
    foreach ($credentials as $name => [$db_value, $constant_value]) {
        $db_status = !empty($db_value) ? "✅ Set (" . strlen($db_value) . " chars)" : "❌ Empty";
        $constant_status = !empty($constant_value) ? "✅ Set (" . strlen($constant_value) . " chars)" : "❌ Not defined";
        
        $overall_status = (!empty($db_value) || !empty($constant_value)) ? '✅ Available' : '❌ Missing';
        $status_class = (!empty($db_value) || !empty($constant_value)) ? 'status-ok' : 'status-error';
        
        // Check for placeholder values
        if ($db_value === 'keycovered-for-security' || $constant_value === 'keycovered-for-security') {
            $overall_status = '⚠️ Placeholder';
            $status_class = 'status-warning';
        }
        
        echo "<tr class='{$status_class}'>";
        echo "<td><strong>{$name}</strong></td>";
        echo "<td>{$db_status}</td>";
        echo "<td>{$constant_status}</td>";
        echo "<td>{$overall_status}</td>";
        echo "</tr>";
        
        if (empty($db_value) && empty($constant_value)) {
            $issues[] = "{$name} is not configured";
        }
    }
    echo '</table>';
    
} else {
    echo '<p class="warning">⚠️ WordPress not loaded - cannot check stored credentials</p>';
}

echo '</div>';

// ==========================================
// TEST 6: SSL/TLS Configuration
// ==========================================
echo '<div class="test-section">';
echo '<h2>6. 🔒 SSL/TLS Configuration</h2>';

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://driver-vehicle-licensing.api.gov.uk');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Blue Motors SSL Test/1.0');
    
    $response = curl_exec($ch);
    $ssl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ssl_verify_result = curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);
    curl_close($ch);
    
    echo '<table>';
    echo '<tr><th>SSL Test</th><th>Result</th></tr>';
    
    if ($response !== false) {
        echo "<tr class='status-ok'><td>SSL Connection</td><td>✅ Success (HTTP {$http_code})</td></tr>";
        echo "<tr class='status-ok'><td>SSL Verification</td><td>✅ Passed (Code: {$ssl_verify_result})</td></tr>";
    } else {
        echo "<tr class='status-error'><td>SSL Connection</td><td>❌ Failed</td></tr>";
        echo "<tr class='status-error'><td>SSL Error</td><td>{$ssl_error}</td></tr>";
        $issues[] = "SSL/TLS connection failed: {$ssl_error}";
    }
    
    // Check CA bundle
    $ca_info = curl_version();
    echo "<tr><td>CA Bundle</td><td>" . ($ca_info['cainfo'] ?? 'System default') . "</td></tr>";
    echo '</table>';
    
} else {
    echo '<p class="error">❌ cURL not available - cannot test SSL</p>';
}

echo '</div>';

// ==========================================
// TEST 7: Actual API Tests
// ==========================================
echo '<div class="test-section">';
echo '<h2>7. 🧪 Live API Tests</h2>';

if ($wp_loaded && !empty($critical_issues)) {
    echo '<div class="critical">';
    echo '<h4>⚠️ Skipping API tests due to critical issues:</h4>';
    foreach ($critical_issues as $issue) {
        echo "<p>• {$issue}</p>";
    }
    echo '</div>';
} elseif ($wp_loaded) {
    
    // Test DVLA API
    echo '<h3>DVLA API Test</h3>';
    
    $dvla_key = get_option('bm_dvla_api_key', '') ?: (defined('BM_DVLA_API_KEY') ? BM_DVLA_API_KEY : '');
    
    if (empty($dvla_key) || $dvla_key === 'keycovered-for-security') {
        echo '<p class="warning">⚠️ No valid DVLA API key - skipping test</p>';
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $dvla_key,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['registrationNumber' => 'AB12CDE']));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Blue Motors Diagnostic/1.0');
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p><strong>HTTP Code:</strong> {$http_code}</p>";
        
        if ($response !== false) {
            if ($http_code == 404) {
                echo '<p class="success">✅ DVLA API connection successful (404 = vehicle not found, which is expected)</p>';
            } elseif ($http_code == 403) {
                echo '<p class="error">❌ DVLA API access denied - check API key permissions</p>';
                $issues[] = "DVLA API access denied - check API key";
            } else {
                echo "<p class=\"info\">ℹ️ DVLA API responded with HTTP {$http_code}</p>";
                echo '<div class="code-block">' . htmlspecialchars(substr($response, 0, 500)) . '</div>';
            }
        } else {
            echo "<p class=\"error\">❌ DVLA API connection failed: {$error}</p>";
            $issues[] = "DVLA API connection failed: {$error}";
        }
    }
    
    // Test DVSA OAuth
    echo '<h3>DVSA OAuth Test</h3>';
    
    $dvsa_client_id = get_option('bm_dvsa_client_id', '');
    $dvsa_client_secret = get_option('bm_dvsa_client_secret', '');
    $dvsa_token_url = get_option('bm_dvsa_token_url', '');
    $dvsa_scope_url = get_option('bm_dvsa_scope_url', '');
    
    if (empty($dvsa_client_id) || empty($dvsa_client_secret) || empty($dvsa_token_url)) {
        echo '<p class="warning">⚠️ DVSA credentials incomplete - skipping test</p>';
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $dvsa_token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Blue Motors Diagnostic/1.0'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $dvsa_client_id,
            'client_secret' => $dvsa_client_secret,
            'scope' => $dvsa_scope_url
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p><strong>HTTP Code:</strong> {$http_code}</p>";
        echo "<p><strong>Token URL:</strong> {$dvsa_token_url}</p>";
        
        if ($response !== false) {
            if ($http_code == 200) {
                $data = json_decode($response, true);
                if (isset($data['access_token'])) {
                    echo '<p class="success">✅ DVSA OAuth successful - access token received</p>';
                } else {
                    echo '<p class="warning">⚠️ DVSA OAuth response unclear</p>';
                    echo '<div class="code-block">' . htmlspecialchars($response) . '</div>';
                }
            } else {
                echo "<p class=\"error\">❌ DVSA OAuth failed with HTTP {$http_code}</p>";
                echo '<div class="code-block">' . htmlspecialchars($response) . '</div>';
                
                if ($http_code == 401) {
                    $issues[] = "DVSA OAuth failed: invalid_client - check credentials or domain restrictions";
                } else {
                    $issues[] = "DVSA OAuth failed with HTTP {$http_code}";
                }
            }
        } else {
            echo "<p class=\"error\">❌ DVSA OAuth connection failed: {$error}</p>";
            $issues[] = "DVSA OAuth connection failed: {$error}";
        }
    }
    
} else {
    echo '<p class="warning">⚠️ WordPress not loaded - cannot test APIs</p>';
}

echo '</div>';

// ==========================================
// SUMMARY AND RECOMMENDATIONS
// ==========================================
echo '<div class="test-section">';
echo '<h2>8. 📋 Summary and Recommendations</h2>';

if (!empty($critical_issues)) {
    echo '<div class="critical">';
    echo '<h3>🚨 Critical Issues (Fix These First):</h3>';
    foreach ($critical_issues as $issue) {
        echo "<p>• {$issue}</p>";
    }
    echo '</div>';
}

if (!empty($issues)) {
    echo '<div class="warning" style="background: #fff3cd; border-color: #ffeaa7;">';
    echo '<h3>⚠️ Issues Found:</h3>';
    foreach ($issues as $issue) {
        echo "<p>• {$issue}</p>";
    }
    echo '</div>';
}

// Generate specific recommendations
echo '<h3>🎯 Specific Recommendations:</h3>';

// Check for class conflicts
if (!empty($critical_issues)) {
    echo '<div class="recommendation">';
    echo '<h4>1. Fix Class Conflicts (PRIORITY 1)</h4>';
    echo '<p>Rename these files to prevent conflicts:</p>';
    echo '<div class="code-block">';
    echo "# SSH/FTP to your server and run:\n";
    echo "cd wp-content/plugins/blue-motors-southampton/includes/services/\n";
    echo "mv class-dvla-api.php class-dvla-api.php.backup\n";
    echo "mv class-dvsa-mot-api.php class-dvsa-mot-api.php.backup\n";
    echo '</div>';
    echo '</div>';
}

// Check for connectivity issues
if (in_array('Cannot connect to DVLA API', array_map(function($i) { return substr($i, 0, 20); }, $issues))) {
    echo '<div class="recommendation">';
    echo '<h4>2. Fix Server Connectivity (PRIORITY 2)</h4>';
    echo '<p>Contact your hosting provider (StartTLD) and request:</p>';
    echo '<ul>';
    echo '<li>Enable outbound HTTPS requests to *.api.gov.uk</li>';
    echo '<li>Whitelist driver-vehicle-licensing.api.gov.uk</li>';
    echo '<li>Whitelist history.mot.api.gov.uk</li>';
    echo '</ul>';
    echo '</div>';
}

// Check for OAuth issues
foreach ($issues as $issue) {
    if (strpos($issue, 'invalid_client') !== false) {
        echo '<div class="recommendation">';
        echo '<h4>3. Fix DVSA Domain Restrictions (PRIORITY 2)</h4>';
        echo '<p>Contact your DVSA API provider and request:</p>';
        echo '<ul>';
        echo '<li>Add bluemotorsgarage.com to your approved domains</li>';
        echo '<li>Verify you have PRODUCTION credentials (not development)</li>';
        echo '<li>Check if IP address restrictions apply</li>';
        echo '</ul>';
        echo '</div>';
        break;
    }
}

// Environment-specific config
echo '<div class="recommendation">';
echo '<h4>4. Add Environment-Specific Configuration</h4>';
echo '<p>Add this to your wp-config.php file before "That\'s all, stop editing!":</p>';
echo '<div class="code-block">';
echo '// Environment-specific API configuration
if (strpos($_SERVER[\'HTTP_HOST\'], \'bluemotorsgarage.com\') !== false) {
    define(\'BM_ENVIRONMENT\', \'production\');
    // Use your PRODUCTION API credentials here
    define(\'BM_DVLA_API_KEY_PROD\', \'your_real_production_key\');
    define(\'BM_DVSA_CLIENT_ID_PROD\', \'your_production_client_id\');
    define(\'BM_DVSA_CLIENT_SECRET_PROD\', \'your_production_secret\');
    define(\'BM_DVSA_API_KEY_PROD\', \'your_production_api_key\');
    define(\'BM_DVSA_TOKEN_URL_PROD\', \'your_production_token_url\');
    define(\'BM_DVSA_SCOPE_URL_PROD\', \'your_production_scope_url\');
}';
echo '</div>';
echo '</div>';

echo '<div class="recommendation">';
echo '<h4>5. Clear Caches After Fixes</h4>';
echo '<p>After implementing fixes, clear all caches:</p>';
echo '<div class="code-block">';
echo "# WordPress admin: Clear any caching plugins\n";
echo "# In WordPress admin, go to Blue Motors > API Settings > Test Connection\n";
echo "# If using object cache, flush it\n";
echo '</div>';
echo '</div>';

echo '<div class="info">';
echo '<h4>📞 Next Steps</h4>';
echo '<p>1. Fix class conflicts first (rename original files)</p>';
echo '<p>2. Contact hosting provider about API connectivity</p>';
echo '<p>3. Contact DVSA about domain restrictions</p>';
echo '<p>4. Re-run this diagnostic after each fix</p>';
echo '<p>5. Test APIs in WordPress admin after all fixes</p>';
echo '</div>';

echo '</div>';

// Footer
echo '<hr>';
echo '<p><small>Diagnostic completed at ' . date('Y-m-d H:i:s') . ' | ';
echo 'Blue Motors Southampton Plugin Diagnostics v1.0</small></p>';

?>

</div>
</body>
</html>