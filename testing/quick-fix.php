<?php
/**
 * Quick Database Fix for Blue Motors Southampton
 * 
 * Run this script to immediately fix the database column name issues
 * 
 * Access via: your-site.com/wp-content/plugins/blue-motors-southampton/quick-fix.php
 */

// Check if we're in WordPress
if (!function_exists('is_admin')) {
    // Try to load WordPress
    $wp_load_paths = array(
        '../../../wp-load.php',
        '../../../../wp-load.php',
        '../../../../../wp-load.php');
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('Could not load WordPress. Please run this from your WordPress admin instead.');
    }
}

// Check user permissions
if (!current_user_can('manage_options')) {
    die('You do not have permission to run this script.');
}

echo '<div style="padding: 20px; font-family: Arial; max-width: 800px;">';
echo '<h1>🛠️ Blue Motors Southampton - Quick Database Fix</h1>';

// Get current status
if (class_exists('BMS_Database_Manager')) {
    $status = BMS_Database_Manager::get_status();
    echo '<h2>Current Status</h2>';
    echo '<div style="padding: 10px; background: #f0f0f0; border-left: 4px solid #0073aa; margin: 20px 0;">';
    echo '<strong>Status:</strong> ' . esc_html($status['message']);
    echo '</div>';
}

// Run the fix if requested
if (isset($_GET['run_fix']) && $_GET['run_fix'] === 'yes') {
    echo '<h2>🔧 Running Database Fix...</h2>';
    
    try {
        // Load the database manager if not already loaded
        if (!class_exists('BMS_Database_Manager')) {
            $db_manager_path = __DIR__ . '/includes/services/class-database-manager.php';
            if (file_exists($db_manager_path)) {
                require_once $db_manager_path;
            } else {
                throw new Exception('Database manager not found');
            }
        }
        
        // Create/update tables
        if (class_exists('BMS_Database_Manager')) {
            $result = BMS_Database_Manager::create_tables();
            
            if ($result) {
                echo '<div style="padding: 15px; background: #d1f5d3; border: 1px solid #46b450; border-radius: 4px; margin: 10px 0;">';
                echo '<h3 style="color: #155724; margin: 0 0 10px 0;">✅ SUCCESS!</h3>';
                echo '<p style="margin: 0;">Database tables have been created/updated successfully with the correct column names.</p>';
                echo '</div>';
                
                // Check status after fix
                $new_status = BMS_Database_Manager::get_status();
                echo '<h3>Updated Status</h3>';
                echo '<div style="padding: 10px; background: #f0f0f0; border-left: 4px solid #46b450; margin: 20px 0;">';
                echo '<strong>New Status:</strong> ' . esc_html($new_status['message']);
                echo '</div>';
                
            } else {
                throw new Exception('Failed to create database tables');
            }
        } else {
            throw new Exception('Database manager class not available');
        }
        
    } catch (Exception $e) {
        echo '<div style="padding: 15px; background: #f8d7da; border: 1px solid #dc3232; border-radius: 4px; margin: 10px 0;">';
        echo '<h3 style="color: #721c24; margin: 0 0 10px 0;">❌ ERROR</h3>';
        echo '<p style="margin: 0;">Failed to fix database: ' . esc_html($e->getMessage()) . '</p>';
        echo '</div>';
    }
    
    // Test the results
    echo '<h2>🧪 Testing Fixed Database</h2>';
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bms_appointments';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if ($table_exists) {
        // Check columns
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
        $column_names = array();
        foreach ($columns as $column) {
            $column_names[] = $column->Field;
        }
        
        $required_columns = array('booking_date', 'booking_time', 'booking_reference');
        $has_required = true;
        
        echo '<h3>Column Check:</h3>';
        echo '<ul>';
        foreach ($required_columns as $col) {
            $exists = in_array($col, $column_names);
            $status = $exists ? '✅' : '❌';
            echo '<li>' . $status . ' ' . $col . ($exists ? ' - Found' : ' - Missing') . '</li>';
            if (!$exists) $has_required = false;
        }
        echo '</ul>';
        
        if ($has_required) {
            echo '<div style="padding: 15px; background: #d1f5d3; border: 1px solid #46b450; border-radius: 4px; margin: 10px 0;">';
            echo '<h3 style="color: #155724; margin: 0 0 10px 0;">🎉 Database Fix Complete!</h3>';
            echo '<p style="margin: 0;">All required columns are now present. Your booking system should work correctly.</p>';
            echo '</div>';
        }
    } else {
        echo '<div style="padding: 15px; background: #f8d7da; border: 1px solid #dc3232; border-radius: 4px; margin: 10px 0;">';
        echo '<h3 style="color: #721c24; margin: 0 0 10px 0;">❌ Table Still Missing</h3>';
        echo '<p style="margin: 0;">The appointments table was not created. You may need to run this from WordPress admin.</p>';
        echo '</div>';
    }
    
} else {
    // Show fix options
    echo '<h2>Database Fix Options</h2>';
    echo '<p>The database issues have been identified and can be fixed automatically.</p>';
    
    echo '<div style="background: #fff3cd; border: 1px solid #ffb900; border-radius: 4px; padding: 15px; margin: 20px 0;">';
    echo '<h3 style="color: #856404; margin: 0 0 10px 0;">⚠️ Issues Found:</h3>';
    echo '<ul>';
    echo '<li>Database table uses <code>appointment_date</code> but code expects <code>booking_date</code></li>';
    echo '<li>Database table uses <code>appointment_time</code> but code expects <code>booking_time</code></li>';
    echo '<li>This causes "Unknown column" errors in booking system</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<div style="background: #d1f5d3; border: 1px solid #46b450; border-radius: 4px; padding: 15px; margin: 20px 0;">';
    echo '<h3 style="color: #155724; margin: 0 0 10px 0;">✅ Quick Fix Available:</h3>';
    echo '<p style="margin: 0 0 10px 0;">Click the button below to automatically:</p>';
    echo '<ul>';
    echo '<li>Create/update database tables with correct column names</li>';
    echo '<li>Ensure all required indexes are in place</li>';
    echo '<li>Initialize default services if needed</li>';
    echo '<li>Test the fix automatically</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<div style="text-align: center; margin: 30px 0;">';
    echo '<a href="?run_fix=yes" style="background: #0073aa; color: white; padding: 15px 30px; text-decoration: none; border-radius: 4px; font-size: 18px; display: inline-block;">';
    echo '🔧 Fix Database Issues Now';
    echo '</a>';
    echo '</div>';
}

echo '<hr style="margin: 40px 0;">';

echo '<h2>Alternative Fix Methods</h2>';
echo '<div style="background: #f0f6fc; border: 1px solid #0969da; border-radius: 4px; padding: 15px; margin: 20px 0;">';
echo '<h3 style="color: #0969da; margin: 0 0 10px 0;">📋 Other Ways to Fix:</h3>';
echo '<ol>';
echo '<li><strong>WordPress Admin:</strong> Go to Blue Motors → 💾 Database Status (requires debug mode)</li>';
echo '<li><strong>Plugin Reactivation:</strong> Deactivate and reactivate the plugin</li>';
echo '<li><strong>Manual SQL:</strong> Run SQL commands to rename columns (advanced users)</li>';
echo '</ol>';
echo '</div>';

echo '<h3>Next Steps After Fix:</h3>';
echo '<ol>';
echo '<li><strong>Test Phase 3:</strong> Go to Blue Motors → 🔧 Phase 3 Tests</li>';
echo '<li><strong>Configure Settings:</strong> Set up business hours and contact info</li>';
echo '<li><strong>Test Booking:</strong> Create a test booking to verify everything works</li>';
echo '<li><strong>Go Live:</strong> Start taking real bookings!</li>';
echo '</ol>';

echo '<div style="background: #f0f0f0; border-radius: 4px; padding: 15px; margin: 30px 0; text-align: center;">';
echo '<p style="margin: 0; color: #666;"><small>';
echo 'Blue Motors Southampton - Database Quick Fix Tool<br>';
echo 'Version 1.3.0 - January 2025';
echo '</small></p>';
echo '</div>';

echo '</div>';
