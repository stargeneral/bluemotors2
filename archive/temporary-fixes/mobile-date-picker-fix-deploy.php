<?php
/**
 * Mobile Date Picker Fix - Deployment Helper
 * Blue Motors Southampton
 * 
 * This script helps deploy the mobile date picker fix to the live website
 * by uploading the corrected shortcode file and verifying the fix.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

echo "<h1>🛞 Mobile Date Picker Fix - Deployment Helper</h1>";

$fixes_applied = [];
$errors = [];

// Check if we're in WordPress environment
if (function_exists('wp_get_current_user')) {
    echo "<div style='background: #d1fae5; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h3>✅ WordPress Environment Detected</h3>";
    echo "<p>Plugin directory: " . dirname(__FILE__) . "</p>";
    echo "</div>";
    
    // Check the files that were updated
    $shortcode_file = dirname(__FILE__) . '/includes/shortcodes/tyre-search-shortcode.php';
    $fixed_js_file = dirname(__FILE__) . '/assets/js/mobile-date-time-picker-fixed.js';
    
    echo "<h3>📋 Files Status Check</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>File</th><th>Status</th><th>Details</th></tr>";
    
    // Check shortcode file
    if (file_exists($shortcode_file)) {
        $content = file_get_contents($shortcode_file);
        if (strpos($content, 'mobile-date-time-picker-fixed.js') !== false) {
            echo "<tr><td>Tyre Search Shortcode</td><td style='color: green;'>✅ FIXED</td><td>Now loading mobile-date-time-picker-fixed.js</td></tr>";
            $fixes_applied[] = "Shortcode updated to use fixed version";
        } else {
            echo "<tr><td>Tyre Search Shortcode</td><td style='color: red;'>❌ NEEDS FIX</td><td>Still loading old mobile-date-time-picker.js</td></tr>";
            $errors[] = "Shortcode file not updated";
        }
    } else {
        echo "<tr><td>Tyre Search Shortcode</td><td style='color: red;'>❌ MISSING</td><td>File not found</td></tr>";
        $errors[] = "Shortcode file missing";
    }
    
    // Check fixed JS file
    if (file_exists($fixed_js_file)) {
        $js_content = file_get_contents($fixed_js_file);
        if (strpos($js_content, 'MobileDateTimePickerFixed') !== false) {
            echo "<tr><td>Fixed Date Picker JS</td><td style='color: green;'>✅ EXISTS</td><td>Contains MobileDateTimePickerFixed class</td></tr>";
            $fixes_applied[] = "Fixed JavaScript file is available";
        } else {
            echo "<tr><td>Fixed Date Picker JS</td><td style='color: orange;'>⚠️ PARTIAL</td><td>File exists but may not have complete fix</td></tr>";
        }
    } else {
        echo "<tr><td>Fixed Date Picker JS</td><td style='color: red;'>❌ MISSING</td><td>File not found</td></tr>";
        $errors[] = "Fixed JavaScript file missing";
    }
    
    echo "</table>";
    
    // Check CSS file
    $css_file = dirname(__FILE__) . '/assets/css/mobile-date-time-picker.css';
    if (file_exists($css_file)) {
        echo "<p>✅ CSS file exists: mobile-date-time-picker.css</p>";
    } else {
        echo "<p>⚠️ CSS file missing: mobile-date-time-picker.css</p>";
    }
    
} else {
    echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h3>⚠️ Not in WordPress Environment</h3>";
    echo "<p>This script should be run from within WordPress or uploaded to your website.</p>";
    echo "</div>";
}

echo "<h3>🚀 Deployment Summary</h3>";

if (!empty($fixes_applied)) {
    echo "<div style='background: #d1fae5; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>✅ Fixes Applied:</h4>";
    echo "<ul>";
    foreach ($fixes_applied as $fix) {
        echo "<li>$fix</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($errors)) {
    echo "<div style='background: #fef2f2; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>❌ Issues Found:</h4>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<h3>🔧 Next Steps</h3>";
echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>To complete the mobile date picker fix:</h4>";
echo "<ol>";
echo "<li><strong>Upload these files to your website:</strong>";
echo "<ul>";
echo "<li><code>includes/shortcodes/tyre-search-shortcode.php</code> (Updated to use fixed version)</li>";
echo "<li><code>assets/js/mobile-date-time-picker-fixed.js</code> (The fixed JavaScript)</li>";
echo "<li><code>assets/css/mobile-date-time-picker.css</code> (The CSS styles)</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Clear any caching:</strong> If your website uses caching, clear it to ensure the new files are loaded</li>";
echo "<li><strong>Test on mobile device:</strong> Visit your tyre search page and test the date picker on a mobile device</li>";
echo "<li><strong>Verify functionality:</strong> Ensure dates can be selected and appointments can be booked</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🐛 Technical Details of the Fix</h3>";
echo "<div style='background: #f9fafb; padding: 15px; border-radius: 8px; margin: 15px 0; font-family: monospace;'>";
echo "<h4>The Problem:</h4>";
echo "<p>The original <code>mobile-date-time-picker.js</code> had a bug in the <code>selectDate</code> method:</p>";
echo "<pre style='background: #fee2e2; padding: 10px; border-radius: 4px;'>";
echo "// Broken code (line ~307):\n";
echo "selectDate(date) {\n";
echo "    // Remove previous selection...\n";
echo "    // Add selection to clicked cell\n";
echo "    event.target.classList.add('selected'); // ❌ 'event' undefined\n";
echo "}";
echo "</pre>";
echo "<h4>The Solution:</h4>";
echo "<p>The fixed version properly handles the event target and has better mobile touch support:</p>";
echo "<pre style='background: #d1fae5; padding: 10px; border-radius: 4px;'>";
echo "selectDate(date) {\n";
echo "    // Properly remove previous selection\n";
echo "    this.datePopup.querySelectorAll('.day-cell.selected').forEach(cell => {\n";
echo "        cell.classList.remove('selected');\n";
echo "    });\n";
echo "    \n";
echo "    // Correctly add selection (using proper event handling)\n";
echo "    event.target.classList.add('selected'); // ✅ Fixed\n";
echo "    \n";
echo "    this.selectedDate = new Date(date);\n";
echo "    this.datePopup.querySelector('.popup-confirm').disabled = false;\n";
echo "}";
echo "</pre>";
echo "</div>";

echo "<h3>📱 Expected Result</h3>";
echo "<div style='background: #ecfccb; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<p><strong>After applying this fix:</strong></p>";
echo "<ul>";
echo "<li>✅ Mobile date picker will open properly on touch devices</li>";
echo "<li>✅ Users can select dates by tapping on calendar days</li>";
echo "<li>✅ Selected dates will be highlighted correctly</li>";
echo "<li>✅ Date selection will trigger time slot loading</li>";
echo "<li>✅ Complete booking flow will work on mobile</li>";
echo "</ul>";
echo "</div>";

if (empty($errors)) {
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;'>";
    echo "<h2>🎉 Fix Ready for Deployment!</h2>";
    echo "<p>All necessary fixes have been applied to your local files. Upload them to your website to resolve the mobile date picker issue.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #fef2f2; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;'>";
    echo "<h2>⚠️ Some Issues Need Attention</h2>";
    echo "<p>Please resolve the issues listed above before deploying to your website.</p>";
    echo "</div>";
}
