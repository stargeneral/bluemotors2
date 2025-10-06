<?php
/**
 * Calendar/Appointment Booking Fix
 * Blue Motors Southampton - Fixes for tyre appointment calendar
 * 
 * This file identifies and fixes the calendar booking functionality
 */

// WordPress bootstrap
require_once('../../../../wp-load.php');

if (!defined('ABSPATH')) {
    exit('WordPress not loaded');
}

echo "<h1>🛠️ Calendar/Appointment Booking Fix</h1>";
echo "<p><strong>Fix Date:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Step 1: Check if scripts are being loaded correctly
echo "<div style='background: #f0f8ff; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
echo "<h2>Step 1: Script Loading Analysis</h2>";

// Check if the tyre-booking.js and mobile-date-time-picker.js exist and are readable
$plugin_url = plugin_dir_url(__FILE__);
$tyre_booking_js = $plugin_url . 'assets/js/tyre-booking.js';
$mobile_picker_js = $plugin_url . 'assets/js/mobile-date-time-picker.js';

echo "<p><strong>Tyre Booking JS URL:</strong> <a href='$tyre_booking_js' target='_blank'>$tyre_booking_js</a></p>";
echo "<p><strong>Mobile Picker JS URL:</strong> <a href='$mobile_picker_js' target='_blank'>$mobile_picker_js</a></p>";

// Check if shortcode is registered
if (shortcode_exists('bms_tyre_search')) {
    echo "<p style='color: green;'>✅ [bms_tyre_search] shortcode is registered</p>";
} else {
    echo "<p style='color: red;'>❌ [bms_tyre_search] shortcode is NOT registered</p>";
}

echo "</div>";

// Step 2: Force script and style loading for testing
echo "<div style='background: #f0fff0; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
echo "<h2>Step 2: Force Loading Scripts for Testing</h2>";

// Force enqueue the scripts
wp_enqueue_style(
    'bms-tyre-search-f1',
    plugin_dir_url(__FILE__) . 'assets/css/tyre-search-professional.css',
    [],
    '1.0.0'
);

wp_enqueue_style(
    'bms-mobile-date-time-picker',
    plugin_dir_url(__FILE__) . 'assets/css/mobile-date-time-picker.css',
    ['bms-tyre-search-f1'],
    '1.0.0'
);

wp_enqueue_script(
    'bms-vehicle-lookup',
    plugin_dir_url(__FILE__) . 'assets/js/vehicle-lookup.js',
    ['jquery'],
    '1.0.0',
    true
);

wp_enqueue_script(
    'bms-tyre-booking',
    plugin_dir_url(__FILE__) . 'assets/js/tyre-booking.js',
    ['jquery', 'bms-vehicle-lookup'],
    '1.0.0',
    true
);

wp_enqueue_script(
    'bms-mobile-date-time-picker',
    plugin_dir_url(__FILE__) . 'assets/js/mobile-date-time-picker.js',
    ['bms-tyre-booking'],
    '1.0.0',
    true
);

// Localize scripts with proper data
wp_localize_script(
    'bms-tyre-booking',
    'bmsTyreBooking',
    [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bms_vehicle_lookup'),
        'strings' => [
            'searchFailed' => 'Tyre search failed. Please try again.',
            'invalidRegistration' => 'Please enter a valid UK vehicle registration.',
            'noTyresFound' => 'No tyres found for your search criteria.',
            'selectTyre' => 'Please select a tyre to continue.',
            'bookingFailed' => 'Booking creation failed. Please try again.',
            'loading' => 'Loading...',
            'selectDateFirst' => 'Please select a date first.',
            'noSlotsAvailable' => 'No appointment slots available for this date.'
        ],
        'pricing' => [
            'vatRate' => 0.2,
            'currency' => 'GBP',
            'currencySymbol' => '£'
        ],
        'mobile' => [
            'enabled' => true,
            'popupCalendar' => true,
            'touchOptimized' => true
        ]
    ]
);

// Also localize for vehicle lookup (fallback)
wp_localize_script(
    'bms-vehicle-lookup',
    'bmsVehicleLookup',
    [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('bms_vehicle_lookup'),
        'strings' => [
            'searchFailed' => 'Search failed. Please try again.',
            'invalidRegistration' => 'Please enter a valid UK vehicle registration.',
            'loading' => 'Loading...'
        ]
    ]
);

echo "<p style='color: green;'>✅ Scripts forcefully enqueued for this page</p>";
echo "</div>";

// Step 3: Test AJAX endpoints
echo "<div style='background: #fff8f0; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
echo "<h2>Step 3: AJAX Endpoints Test</h2>";

$ajax_actions = [
    'bms_search_tyres_by_reg',
    'bms_get_fitting_slots',
    'bms_create_tyre_booking',
    'bms_calculate_tyre_price'
];

foreach ($ajax_actions as $action) {
    $has_handler = has_action("wp_ajax_$action") || has_action("wp_ajax_nopriv_$action");
    $status = $has_handler ? "✅ REGISTERED" : "❌ NOT REGISTERED";
    $color = $has_handler ? "green" : "red";
    echo "<p style='color: $color;'>$action: $status</p>";
}

echo "</div>";

// Step 4: Test TyreService class instantiation
echo "<div style='background: #f0f0ff; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
echo "<h2>Step 4: TyreService Class Test</h2>";

try {
    if (class_exists('\\BlueMotosSouthampton\\Services\\TyreService')) {
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        echo "<p style='color: green;'>✅ TyreService class instantiated successfully</p>";
        
        // Test the get_basic_time_slots method
        $test_date = date('Y-m-d', strtotime('+3 days'));
        $slots = $tyre_service->get_available_fitting_slots($test_date, 1);
        
        if (!empty($slots)) {
            echo "<p style='color: green;'>✅ Time slots method working: " . count($slots) . " slots found for $test_date</p>";
            echo "<p><strong>Sample slots:</strong> " . implode(', ', array_slice($slots, 0, 5)) . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Time slots method returned no results</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ TyreService class not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error instantiating TyreService: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Step 5: Enhanced Shortcode Test with Calendar Fix
echo "<div style='background: #f8f8ff; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
echo "<h2>Step 5: Enhanced Calendar Test</h2>";

// Add the WordPress head and footer actions to ensure scripts are loaded
wp_head();

echo "<div id='enhanced-calendar-test'>";
echo do_shortcode('[bms_tyre_search]');
echo "</div>";

echo "<style>";
echo "
#enhanced-calendar-test .fitting-appointment {
    display: block !important;
    background: #e6f3ff;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

#enhanced-calendar-test #fitting-date,
#enhanced-calendar-test #fitting-time {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border: 2px solid #1d4ed8;
    border-radius: 6px;
    font-size: 16px;
    background: white;
}

#enhanced-calendar-test #fitting-date:hover,
#enhanced-calendar-test #fitting-time:hover {
    cursor: pointer;
    background: #f0f8ff;
}

.calendar-test-buttons {
    text-align: center;
    margin: 20px 0;
}

.test-btn {
    background: #1d4ed8;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin: 0 10px;
    font-size: 16px;
}

.test-btn:hover {
    background: #1e40af;
}
";
echo "</style>";

echo "<div class='calendar-test-buttons'>";
echo "<button class='test-btn' onclick='testDatePicker()'>🧪 Test Date Picker</button>";
echo "<button class='test-btn' onclick='testTimePicker()'>🧪 Test Time Picker</button>";
echo "<button class='test-btn' onclick='testFullFlow()'>🧪 Test Full Calendar Flow</button>";
echo "</div>";

echo "</div>";

// Step 6: JavaScript Testing Functions
echo "<script>";
echo "
console.log('🛠️ Calendar Fix Script Loaded');

function testDatePicker() {
    console.log('🧪 Testing date picker...');
    
    const dateInput = document.getElementById('fitting-date');
    if (dateInput) {
        console.log('✅ Date input found');
        dateInput.click();
        
        // Check if mobile picker is available
        if (typeof window.mobileDateTimePicker !== 'undefined') {
            console.log('✅ Mobile date picker available');
            try {
                window.mobileDateTimePicker.showDatePicker();
                console.log('✅ Mobile date picker shown');
            } catch (e) {
                console.error('❌ Error showing mobile date picker:', e);
            }
        } else {
            console.log('⚠️ Mobile date picker not initialized');
            
            // Try to initialize it
            if (typeof MobileDateTimePicker !== 'undefined') {
                console.log('✅ MobileDateTimePicker class found, initializing...');
                window.mobileDateTimePicker = new MobileDateTimePicker();
                console.log('✅ Mobile date picker initialized');
            } else {
                console.log('❌ MobileDateTimePicker class not found');
            }
        }
        
        alert('Date picker test completed. Check console for details.');
    } else {
        alert('Date input not found!');
    }
}

function testTimePicker() {
    console.log('🧪 Testing time picker...');
    
    const timeInput = document.getElementById('fitting-time');
    if (timeInput) {
        console.log('✅ Time input found');
        
        // Set a test date first
        const dateInput = document.getElementById('fitting-date');
        if (dateInput) {
            const testDate = new Date();
            testDate.setDate(testDate.getDate() + 3);
            dateInput.value = testDate.toISOString().split('T')[0];
            console.log('✅ Test date set:', dateInput.value);
        }
        
        timeInput.click();
        
        // Check if mobile picker can show time
        if (typeof window.mobileDateTimePicker !== 'undefined') {
            try {
                window.mobileDateTimePicker.showTimePicker();
                console.log('✅ Mobile time picker shown');
            } catch (e) {
                console.error('❌ Error showing mobile time picker:', e);
            }
        } else {
            console.log('⚠️ Mobile date picker not available for time selection');
        }
        
        alert('Time picker test completed. Check console for details.');
    } else {
        alert('Time input not found!');
    }
}

function testFullFlow() {
    console.log('🧪 Testing full calendar flow...');
    
    // Check all required globals
    const checks = {
        'BlueMotosTyreBooking class': typeof BlueMotosTyreBooking !== 'undefined',
        'MobileDateTimePicker class': typeof MobileDateTimePicker !== 'undefined',
        'bmsTyreBooking global': typeof bmsTyreBooking !== 'undefined',
        'bmsVehicleLookup global': typeof bmsVehicleLookup !== 'undefined',
        'mobileDateTimePicker instance': typeof window.mobileDateTimePicker !== 'undefined'
    };
    
    let allGood = true;
    for (const [check, result] of Object.entries(checks)) {
        if (result) {
            console.log('✅', check, 'available');
        } else {
            console.log('❌', check, 'NOT available');
            allGood = false;
        }
    }
    
    if (allGood) {
        console.log('🎉 All components available!');
        alert('✅ All calendar components are available! The calendar should work.');
    } else {
        console.log('⚠️ Some components missing');
        alert('⚠️ Some calendar components are missing. Check console for details.');
    }
    
    // Try to manually fix missing components
    if (typeof window.mobileDateTimePicker === 'undefined' && typeof MobileDateTimePicker !== 'undefined') {
        try {
            window.mobileDateTimePicker = new MobileDateTimePicker();
            console.log('🔧 Mobile date picker manually initialized');
            alert('🔧 Fixed: Mobile date picker has been manually initialized!');
        } catch (e) {
            console.error('❌ Failed to manually initialize mobile date picker:', e);
        }
    }
}

// Auto-initialize missing components
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Calendar fix page loaded');
    
    // Wait a bit for other scripts to load
    setTimeout(function() {
        // Try to initialize mobile picker if not already done
        if (typeof window.mobileDateTimePicker === 'undefined' && typeof MobileDateTimePicker !== 'undefined') {
            try {
                window.mobileDateTimePicker = new MobileDateTimePicker();
                console.log('🔧 Auto-initialized mobile date picker');
            } catch (e) {
                console.error('❌ Auto-initialization failed:', e);
            }
        }
        
        // Show fitting appointment section if it's hidden
        const fittingSection = document.getElementById('fitting-appointment');
        if (fittingSection) {
            fittingSection.style.display = 'block';
            console.log('🔧 Made fitting appointment section visible');
        }
    }, 2000);
});
";
echo "</script>";

wp_footer();

echo "<div style='background: #e6ffe6; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Test the calendar functionality using the buttons above</li>";
echo "<li>Check the browser console (F12) for any JavaScript errors</li>";
echo "<li>If the mobile date picker isn't working, check if the scripts are loading</li>";
echo "<li>Test the AJAX endpoints by trying to select a date</li>";
echo "</ol>";
echo "<p><strong>If this page shows the calendar working, the issue may be with the shortcode not loading scripts properly on the actual pages.</strong></p>";
echo "</div>";
?>
