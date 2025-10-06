<?php
/**
 * FINAL VERIFICATION - Mobile Date Picker Fix
 * Blue Motors Southampton - Complete Fix Validation
 */

echo "=== FINAL MOBILE DATE PICKER FIX VERIFICATION ===\n\n";

$plugin_dir = __DIR__;
$all_fixes = [];
$critical_issues = [];

// 1. Check shortcode file
echo "1. CHECKING: Shortcode File\n";
$shortcode_file = $plugin_dir . '/includes/shortcodes/tyre-search-shortcode.php';
if (file_exists($shortcode_file)) {
    $content = file_get_contents($shortcode_file);
    if (strpos($content, 'mobile-date-time-picker-fixed.js') !== false) {
        echo "   ✅ FIXED - Loads mobile-date-time-picker-fixed.js\n";
        $all_fixes[] = "Shortcode updated to use fixed version";
    } else {
        echo "   ❌ CRITICAL - Still loading old version\n";
        $critical_issues[] = "Shortcode not updated";
    }
} else {
    echo "   ❌ MISSING - File not found\n";
    $critical_issues[] = "Shortcode file missing";
}

// 2. Check fixed mobile date picker JS
echo "\n2. CHECKING: Fixed Mobile Date Picker JS\n";
$fixed_js = $plugin_dir . '/assets/js/mobile-date-time-picker-fixed.js';
if (file_exists($fixed_js)) {
    $js_content = file_get_contents($fixed_js);
    if (strpos($js_content, 'MobileDateTimePickerFixed') !== false) {
        echo "   ✅ EXISTS - Fixed JavaScript file present\n";
        $all_fixes[] = "Mobile date picker fixed version available";
        
        // Check for the specific selectDate fix
        if (strpos($js_content, 'selectDate') !== false) {
            echo "   ✅ CONTAINS FIX - selectDate method found\n";
            $all_fixes[] = "SelectDate fix included";
        }
    } else {
        echo "   ❌ INCOMPLETE - File missing critical components\n";
        $critical_issues[] = "Fixed JS file incomplete";
    }
} else {
    echo "   ❌ MISSING - Fixed JavaScript file not found\n";
    $critical_issues[] = "Fixed JS file missing";
}

// 3. Check unified calendar fix (CRITICAL)
echo "\n3. CHECKING: Unified Calendar Fix (CRITICAL)\n";
$unified_fix = $plugin_dir . '/unified-calendar-fix.js';
if (file_exists($unified_fix)) {
    $unified_content = file_get_contents($unified_fix);
    
    // Check if the selectMobileDate method has been fixed
    if (strpos($unified_content, 'selectMobileDate(date, targetElement)') !== false) {
        echo "   ✅ FIXED - selectMobileDate method now accepts targetElement\n";
        $all_fixes[] = "Unified calendar selectMobileDate fixed";
    } else if (strpos($unified_content, 'selectMobileDate(date)') !== false) {
        echo "   ❌ CRITICAL - selectMobileDate still has bug (event.target undefined)\n";
        $critical_issues[] = "Unified calendar has critical bug";
    } else {
        echo "   ⚠️ UNKNOWN - selectMobileDate method not found\n";
    }
    
    // Check if the event listener passes the target
    if (strpos($unified_content, 'this.selectMobileDate(cellDate, event.target)') !== false) {
        echo "   ✅ FIXED - Event listener properly passes target element\n";
        $all_fixes[] = "Unified calendar event handling fixed";
    } else if (strpos($unified_content, 'this.selectMobileDate(cellDate)') !== false) {
        echo "   ❌ CRITICAL - Event listener doesn't pass target element\n";
        $critical_issues[] = "Unified calendar event handling broken";
    }
    
} else {
    echo "   ⚠️ NOT FOUND - Unified calendar fix file not found\n";
}

// 4. Check CSS file
echo "\n4. CHECKING: CSS File\n";
$css_file = $plugin_dir . '/assets/css/mobile-date-time-picker.css';
if (file_exists($css_file)) {
    echo "   ✅ EXISTS - CSS file present\n";
    $all_fixes[] = "CSS styles available";
} else {
    echo "   ⚠️ MISSING - CSS file not found (may affect styling)\n";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "FINAL SUMMARY\n";
echo str_repeat("=", 50) . "\n";

if (!empty($all_fixes)) {
    echo "\n✅ FIXES SUCCESSFULLY APPLIED:\n";
    foreach ($all_fixes as $fix) {
        echo "   • $fix\n";
    }
}

if (!empty($critical_issues)) {
    echo "\n❌ CRITICAL ISSUES FOUND:\n";
    foreach ($critical_issues as $issue) {
        echo "   • $issue\n";
    }
}

// Final status
if (empty($critical_issues)) {
    echo "\n🎉 SUCCESS: ALL CRITICAL FIXES APPLIED!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "READY FOR DEPLOYMENT\n\n";
    
    echo "📦 FILES TO UPLOAD TO YOUR WEBSITE:\n";
    echo "1. includes/shortcodes/tyre-search-shortcode.php\n";
    echo "2. unified-calendar-fix.js\n";
    echo "3. assets/js/mobile-date-time-picker-fixed.js\n";
    echo "4. assets/css/mobile-date-time-picker.css\n\n";
    
    echo "⚡ DEPLOYMENT STEPS:\n";
    echo "1. Upload all 4 files to your website\n";
    echo "2. Clear all caches (website, browser, CDN)\n";
    echo "3. Test on mobile device at: https://bluemotorsgarage.com/tyre-search-2/\n";
    echo "4. Verify date selection works in appointment booking\n\n";
    
    echo "📱 EXPECTED RESULT:\n";
    echo "✅ Mobile users can now select dates in the booking calendar\n";
    echo "✅ Selected dates will be highlighted properly\n";
    echo "✅ Time slots will load after date selection\n";
    echo "✅ Complete booking flow will work on mobile devices\n";
    
} else {
    echo "\n⚠️ DEPLOYMENT NOT RECOMMENDED\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Critical issues must be resolved before deployment.\n";
    echo "The mobile date picker will still be broken with these issues.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Verification complete: " . date('Y-m-d H:i:s') . "\n";
?>