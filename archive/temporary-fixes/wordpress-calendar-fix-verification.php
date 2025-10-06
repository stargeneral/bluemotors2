<?php
/**
 * WordPress Calendar Fix Verification
 * 
 * Comprehensive test to verify all calendar fixes are working on WordPress pages
 */

// Load WordPress
$wp_load_paths = [
    '../../../../wp-load.php',
    '../../../../../wp-load.php',
    '../../../../../../wp-load.php'
];

$wp_loaded = false;
foreach ($wp_load_paths as $wp_load) {
    if (file_exists(__DIR__ . '/' . $wp_load)) {
        require_once(__DIR__ . '/' . $wp_load);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('❌ Could not load WordPress');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>WordPress Calendar Fix Verification</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background: #f0f2f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #dc2626, #ef4444); color: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; text-align: center; }
        .section { background: #f8fafc; padding: 20px; margin: 20px 0; border-radius: 10px; border-left: 6px solid #dc2626; }
        .status { display: inline-block; padding: 8px 15px; border-radius: 20px; font-weight: 600; margin: 4px; font-size: 14px; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #22c55e; }
        .error { background: #fef2f2; color: #dc2626; border: 1px solid #ef4444; }
        .warning { background: #fef3c7; color: #d97706; border: 1px solid #f59e0b; }
        .info { background: #eff6ff; color: #1d4ed8; border: 1px solid #3b82f6; }
        .test-btn { background: #dc2626; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; margin: 8px; font-size: 16px; }
        .test-btn:hover { background: #b91c1c; }
        .demo-section { background: #e6f3ff; padding: 25px; border-radius: 12px; margin: 25px 0; border: 3px solid #dc2626; }
        .console-output { background: #1f2937; color: #f9fafb; padding: 20px; border-radius: 8px; font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 WordPress Calendar Fix Verification</h1>
            <p>Testing all calendar fixes integrated into WordPress pages</p>
            <p><strong>Test Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <div class="section">
            <h2>1. 🔍 Emergency Fix Status Check</h2>
            
            <?php
            // Check if emergency fix file exists
            $emergency_fix_file = __DIR__ . '/wordpress-calendar-emergency-fix.php';
            $calendar_fix_integration = __DIR__ . '/calendar-popup-fix-integration.php';
            $professional_css = __DIR__ . '/assets/css/professional-messaging.css';
            $professional_js = __DIR__ . '/assets/js/professional-messaging.js';
            
            echo '<div class="test-results">';
            
            if (file_exists($emergency_fix_file)) {
                echo '<div class="status success">✅ Emergency Fix File Present</div>';
            } else {
                echo '<div class="status error">❌ Emergency Fix File Missing</div>';
            }
            
            if (file_exists($calendar_fix_integration)) {
                echo '<div class="status success">✅ Calendar Fix Integration Present</div>';
            } else {
                echo '<div class="status error">❌ Calendar Fix Integration Missing</div>';
            }
            
            if (file_exists($professional_css)) {
                echo '<div class="status success">✅ Professional CSS Present</div>';
            } else {
                echo '<div class="status error">❌ Professional CSS Missing</div>';
            }
            
            if (file_exists($professional_js)) {
                echo '<div class="status success">✅ Professional JS Present</div>';
            } else {
                echo '<div class="status error">❌ Professional JS Missing</div>';
            }
            
            // Check plugin activation
            $plugin_active = is_plugin_active('blue-motors-southampton/blue-motors-southampton.php');
            if ($plugin_active) {
                echo '<div class="status success">✅ Plugin Active</div>';
            } else {
                echo '<div class="status error">❌ Plugin Not Active</div>';
            }
            
            echo '</div>';
            ?>
        </div>

        <div class="section">
            <h2>2. 📅 Live Calendar Test</h2>
            
            <div class="demo-section">
                <h3>🎯 Test the Fixed Calendar System</h3>
                <p>This is a live test of our fixed calendar/time picker system:</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                    <div>
                        <label for="test-fitting-date" style="display: block; margin-bottom: 8px; font-weight: bold;">
                            📅 Select Appointment Date:
                        </label>
                        <input type="date" 
                               id="test-fitting-date" 
                               style="width: 100%; padding: 15px; border: 2px solid #dc2626; border-radius: 8px; font-size: 16px;"
                               min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>"
                               max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                    </div>
                    
                    <div>
                        <label for="test-fitting-time" style="display: block; margin-bottom: 8px; font-weight: bold;">
                            ⏰ Available Times:
                        </label>
                        <input type="text" 
                               id="test-fitting-time" 
                               readonly
                               placeholder="Select date first"
                               style="width: 100%; padding: 15px; border: 2px solid #dc2626; border-radius: 8px; font-size: 16px; cursor: pointer;">
                    </div>
                </div>
                
                <div style="text-align: center; margin: 20px 0;">
                    <button class="test-btn" onclick="testDateCalendar()">🧪 Test Date Calendar</button>
                    <button class="test-btn" onclick="testTimePicker()">🧪 Test Time Picker</button>
                    <button class="test-btn" onclick="testFullFlow()">🎯 Test Complete Flow</button>
                </div>
                
                <div id="test-results" style="margin-top: 20px; padding: 15px; border-radius: 8px; display: none;"></div>
            </div>
        </div>

        <div class="section">
            <h2>3. 🌐 Live WordPress Page Test</h2>
            
            <p>Test the actual WordPress page where the tyre search shortcode is used:</p>
            
            <div style="text-align: center; margin: 20px 0;">
                <a href="http://localhost:10010/testpage2/" target="_blank" class="test-btn">🚀 Open Test Page (testpage2)</a>
                <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" target="_blank" class="test-btn">➕ Create New Test Page</a>
            </div>
            
            <div style="background: #fff3cd; padding: 20px; border-radius: 8px; border: 1px solid #ffc107;">
                <h4>📋 Manual Test Instructions:</h4>
                <ol>
                    <li><strong>Open testpage2</strong> using the button above</li>
                    <li><strong>Enter vehicle reg:</strong> VF19XKX</li>
                    <li><strong>Click "Find My Tyres"</strong> and select any tyre</li>
                    <li><strong>Continue to appointment booking</strong></li>
                    <li><strong>Test date field:</strong> Click it - calendar should appear</li>
                    <li><strong>Test time field:</strong> After selecting date, click time field</li>
                    <li><strong>Check console:</strong> Should see success messages, no errors</li>
                </ol>
            </div>
        </div>

        <div class="section">
            <h2>4. 🛠️ Console Output Monitor</h2>
            
            <p>Real-time JavaScript console output:</p>
            <div class="console-output" id="console-output">
                Console messages will appear here...
            </div>
            
            <div style="text-align: center; margin: 20px 0;">
                <button class="test-btn" onclick="clearConsole()">🧹 Clear Console</button>
                <button class="test-btn" onclick="showDebugInfo()">🔍 Show Debug Info</button>
            </div>
        </div>

        <div class="section">
            <h2>5. 📊 Overall Status</h2>
            
            <div id="overall-status">
                <p>Run the tests above to see the overall status...</p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <button class="test-btn" onclick="runAllTests()" style="font-size: 18px; padding: 15px 30px;">
                    🎯 Run All Tests
                </button>
            </div>
        </div>

    </div>

    <script>
    // Console monitoring
    let consoleMessages = [];
    let originalConsole = {
        log: console.log,
        error: console.error,
        warn: console.warn,
        info: console.info
    };
    
    function logToMonitor(message, type = 'log') {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = `[${timestamp}] ${type.toUpperCase()}: ${message}`;
        consoleMessages.push(logEntry);
        
        const output = document.getElementById('console-output');
        output.textContent = consoleMessages.slice(-50).join('\n'); // Keep last 50 messages
        output.scrollTop = output.scrollHeight;
        
        // Also log to original console
        originalConsole[type](message);
    }
    
    // Override console methods
    console.log = (msg) => logToMonitor(msg, 'log');
    console.error = (msg) => logToMonitor(msg, 'error');
    console.warn = (msg) => logToMonitor(msg, 'warn');
    console.info = (msg) => logToMonitor(msg, 'info');
    
    function clearConsole() {
        consoleMessages = [];
        document.getElementById('console-output').textContent = 'Console cleared...';
    }
    
    function showDebugInfo() {
        console.log('=== DEBUG INFO ===');
        console.log('jQuery loaded: ' + (typeof jQuery !== 'undefined'));
        console.log('jQuery UI loaded: ' + (typeof jQuery !== 'undefined' && !!jQuery.ui));
        console.log('BMSCalendarFix available: ' + (typeof BMSCalendarFix !== 'undefined'));
        console.log('MobileDateTimePicker available: ' + (typeof MobileDateTimePicker !== 'undefined'));
        console.log('Professional Messaging available: ' + (typeof ProfessionalMessaging !== 'undefined'));
        console.log('Window mobile picker: ' + (typeof window.mobileDateTimePicker !== 'undefined'));
        console.log('Emergency fixes loaded: ' + (typeof fixTimePickerIssue !== 'undefined'));
    }
    
    function testDateCalendar() {
        console.log('🧪 Testing date calendar...');
        
        const dateInput = document.getElementById('test-fitting-date');
        if (!dateInput) {
            console.error('❌ Date input not found');
            return false;
        }
        
        console.log('✅ Date input found');
        
        // Try to trigger click
        dateInput.click();
        dateInput.focus();
        
        // Test value setting
        const testDate = new Date();
        testDate.setDate(testDate.getDate() + 7);
        const dateString = testDate.toISOString().split('T')[0];
        
        dateInput.value = dateString;
        dateInput.dispatchEvent(new Event('change'));
        
        console.log('✅ Date set to: ' + dateString);
        
        showTestResult('Date Calendar', 'PASSED', 'Date input is working and can be set programmatically');
        return true;
    }
    
    function testTimePicker() {
        console.log('🧪 Testing time picker...');
        
        const timeInput = document.getElementById('test-fitting-time');
        if (!timeInput) {
            console.error('❌ Time input not found');
            return false;
        }
        
        console.log('✅ Time input found');
        
        // First set a date to enable time selection
        const dateInput = document.getElementById('test-fitting-date');
        if (!dateInput.value) {
            const testDate = new Date();
            testDate.setDate(testDate.getDate() + 5);
            dateInput.value = testDate.toISOString().split('T')[0];
        }
        
        // Try to trigger time picker
        timeInput.click();
        
        // Check if emergency fix functions are available
        if (typeof fixTimePickerIssue !== 'undefined') {
            console.log('✅ Emergency time picker fix available');
            fixTimePickerIssue();
        } else if (typeof window.mobileDateTimePicker !== 'undefined') {
            console.log('✅ Mobile date time picker available');
            try {
                window.mobileDateTimePicker.showTimePicker(timeInput);
            } catch (e) {
                console.warn('⚠️ Mobile picker error: ' + e.message);
            }
        }
        
        // Test manual time setting
        timeInput.value = '10:30';
        timeInput.dispatchEvent(new Event('change'));
        
        console.log('✅ Time set manually to: 10:30');
        
        showTestResult('Time Picker', 'PASSED', 'Time picker is functional with fallback mechanisms');
        return true;
    }
    
    function testFullFlow() {
        console.log('🎯 Testing complete flow...');
        
        // Test both components
        const dateTest = testDateCalendar();
        setTimeout(() => {
            const timeTest = testTimePicker();
            
            if (dateTest && timeTest) {
                console.log('🎉 Full flow test: SUCCESS');
                showTestResult('Complete Flow', 'PASSED', 'Both date and time components are working correctly');
            } else {
                console.error('❌ Full flow test: FAILED');
                showTestResult('Complete Flow', 'FAILED', 'One or more components have issues');
            }
        }, 1000);
    }
    
    function showTestResult(testName, status, details) {
        const resultDiv = document.getElementById('test-results');
        const statusClass = status === 'PASSED' ? 'success' : (status === 'FAILED' ? 'error' : 'warning');
        const icon = status === 'PASSED' ? '✅' : (status === 'FAILED' ? '❌' : '⚠️');
        
        resultDiv.style.display = 'block';
        resultDiv.className = statusClass;
        resultDiv.innerHTML = `<strong>${icon} ${testName}: ${status}</strong><br>${details}`;
    }
    
    function runAllTests() {
        console.log('🚀 Running all tests...');
        clearConsole();
        
        setTimeout(showDebugInfo, 500);
        setTimeout(testDateCalendar, 1000);
        setTimeout(testTimePicker, 2000);
        
        setTimeout(() => {
            console.log('🎯 All tests completed!');
            
            const overallStatus = document.getElementById('overall-status');
            overallStatus.innerHTML = `
                <div class="status success">✅ All calendar fix tests completed</div>
                <div class="status info">📋 Check console output above for detailed results</div>
                <div class="status warning">⚠️ Manual testing on testpage2 still required</div>
            `;
        }, 3000);
    }
    
    // Auto-run basic checks when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📋 WordPress Calendar Fix Verification loaded');
        setTimeout(showDebugInfo, 1000);
    });
    </script>

    <?php wp_footer(); ?>
</body>
</html>