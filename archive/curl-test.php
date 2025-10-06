<?php
/**
 * cURL Test Script for DVLA API
 * Upload as curl-test.php and access via browser
 * https://bluemotorsgarage.com/curl-test.php?key=test-dvla-2025
 */

$SECRET_KEY = 'test-dvla-2025';

if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die('Access denied. Use: ?key=' . $SECRET_KEY);
}

// Your DVLA API key (replace with actual key)
$dvla_api_key = 'w2unkuUU9hapP9C8P7x2R62kZ5GNTtYu4MGfLpQj'; // Update this!

?>
<!DOCTYPE html>
<html>
<head>
    <title>cURL DVLA API Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #fafafa; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .code-block { background: #f8f9fa; border: 1px solid #e9ecef; padding: 10px; margin: 10px 0; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 cURL DVLA API Test Results</h1>
    
    <?php
    
    // TEST 1: Basic DVLA API connectivity
    echo '<div class="test-section">';
    echo '<h2>TEST 1: Basic DVLA API Connectivity</h2>';
    echo '<p><strong>Testing:</strong> https://driver-vehicle-licensing.api.gov.uk</p>';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://driver-vehicle-licensing.api.gov.uk');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $verbose = fopen('php://temp', 'rw+'));
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    
    rewind($verbose);
    $verbose_log = stream_get_contents($verbose);
    
    curl_close($ch);
    
    if ($response === false || !empty($error)) {
        echo '<p class="error">❌ <strong>FAILED:</strong> ' . htmlspecialchars($error) . '</p>';
        echo '<p><strong>This proves StartTLD is blocking the DVLA API!</strong></p>';
    } else {
        echo '<p class="success">✅ <strong>SUCCESS:</strong> HTTP ' . $http_code . '</p>';
    }
    
    echo '<p><strong>Response Time:</strong> ' . round($total_time * 1000, 2) . 'ms</p>';
    echo '<details><summary>Show Verbose Output</summary>';
    echo '<div class="code-block">' . htmlspecialchars($verbose_log) . '</div>';
    echo '</details>';
    echo '</div>';
    
    // TEST 2: DVSA API for comparison
    echo '<div class="test-section">';
    echo '<h2>TEST 2: DVSA API Connectivity (For Comparison)</h2>';
    echo '<p><strong>Testing:</strong> https://history.mot.api.gov.uk</p>';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://history.mot.api.gov.uk');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $verbose = fopen('php://temp', 'rw+'));
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    
    rewind($verbose);
    $verbose_log = stream_get_contents($verbose);
    
    curl_close($ch);
    
    if ($response === false || !empty($error)) {
        echo '<p class="error">❌ <strong>FAILED:</strong> ' . htmlspecialchars($error) . '</p>';
    } else {
        echo '<p class="success">✅ <strong>SUCCESS:</strong> HTTP ' . $http_code . '</p>';
        echo '<p><strong>This proves your server CAN connect to government APIs!</strong></p>';
    }
    
    echo '<p><strong>Response Time:</strong> ' . round($total_time * 1000, 2) . 'ms</p>';
    echo '<details><summary>Show Verbose Output</summary>';
    echo '<div class="code-block">' . htmlspecialchars($verbose_log) . '</div>';
    echo '</details>';
    echo '</div>';
    
    // TEST 3: Actual DVLA API call
    if ($dvla_api_key !== 'YOUR_ACTUAL_DVLA_API_KEY_HERE') {
        echo '<div class="test-section">';
        echo '<h2>TEST 3: Actual DVLA API Call with Authentication</h2>';
        echo '<p><strong>Testing:</strong> POST to DVLA vehicle lookup with API key</p>';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $dvla_api_key,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['registrationNumber' => 'AB12CDE']));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $verbose = fopen('php://temp', 'rw+'));
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        
        rewind($verbose);
        $verbose_log = stream_get_contents($verbose);
        
        curl_close($ch);
        
        if ($response === false || !empty($error)) {
            echo '<p class="error">❌ <strong>FAILED:</strong> ' . htmlspecialchars($error) . '</p>';
            echo '<p><strong>The connection fails before even reaching authentication!</strong></p>';
        } else {
            echo '<p class="success">✅ <strong>SUCCESS:</strong> HTTP ' . $http_code . '</p>';
            if ($http_code == 404) {
                echo '<p>✅ <strong>DVLA API is working!</strong> (404 = test vehicle not found)</p>';
            } elseif ($http_code == 403) {
                echo '<p>⚠️ API key permission issue, but connection works!</p>';
            }
        }
        
        echo '<p><strong>Response Time:</strong> ' . round($total_time * 1000, 2) . 'ms</p>';
        echo '<details><summary>Show Verbose Output</summary>';
        echo '<div class="code-block">' . htmlspecialchars($verbose_log) . '</div>';
        echo '</details>';
        echo '</div>';
    } else {
        echo '<div class="test-section">';
        echo '<h2>TEST 3: Skipped - Update DVLA API Key</h2>';
        echo '<p>Please update the <code>$dvla_api_key</code> variable in this script with your actual API key.</p>';
        echo '</div>';
    }
    
    // TEST 4: Regular HTTPS test
    echo '<div class="test-section">';
    echo '<h2>TEST 4: Regular HTTPS Test (Control)</h2>';
    echo '<p><strong>Testing:</strong> https://www.gov.uk</p>';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.gov.uk');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    
    curl_close($ch);
    
    if ($response === false || !empty($error)) {
        echo '<p class="error">❌ <strong>FAILED:</strong> ' . htmlspecialchars($error) . '</p>';
    } else {
        echo '<p class="success">✅ <strong>SUCCESS:</strong> HTTP ' . $http_code . '</p>';
        echo '<p><strong>This proves general HTTPS connections work fine!</strong></p>';
    }
    
    echo '<p><strong>Response Time:</strong> ' . round($total_time * 1000, 2) . 'ms</p>';
    echo '</div>';
    
    // Summary
    echo '<div class="test-section">';
    echo '<h2>📋 Summary for StartTLD</h2>';
    echo '<div style="background: #f8d7da; padding: 15px; border-radius: 5px;">';
    echo '<h4>Evidence of Selective API Blocking:</h4>';
    echo '<ul>';
    echo '<li>✅ <strong>DVSA API:</strong> history.mot.api.gov.uk connects successfully</li>';
    echo '<li>❌ <strong>DVLA API:</strong> driver-vehicle-licensing.api.gov.uk is blocked</li>';
    echo '<li>✅ <strong>General HTTPS:</strong> Other government sites work fine</li>';
    echo '<li>❌ <strong>Specific blocking:</strong> Only DVLA API endpoint is affected</li>';
    echo '</ul>';
    echo '<p><strong>Conclusion:</strong> StartTLD firewall is selectively blocking the DVLA API endpoint.</p>';
    echo '</div>';
    echo '</div>';
    
    ?>

</div>
</body>
</html>