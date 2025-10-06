<?php
/**
 * Blue Motors Southampton - Quick Test Script
 * 
 * Tests both Stripe and Database fixes
 * 
 * Access via: /wp-content/plugins/blue-motors-southampton/test-fixes.php
 */

// Basic WordPress check
if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    require_once('../../../wp-config.php');
}

echo "<h1>🔧 Blue Motors Southampton - Fix Verification</h1>";
echo "<style>body{font-family:Arial;margin:40px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// Test 1: Stripe Library Loading
echo "<h2>1. Stripe Library Test</h2>";

try {
    // Load the autoloader
    $autoloader_path = __DIR__ . '/vendor/autoload.php';
    
    if (file_exists($autoloader_path)) {
        echo "<div class='success'>✅ Autoloader file exists</div>";
        
        require_once $autoloader_path;
        
        if (class_exists('\Stripe\Stripe')) {
            echo "<div class='success'>✅ Stripe\Stripe class loaded successfully</div>";
            
            // Test basic Stripe functionality
            \Stripe\Stripe::setApiKey('sk_test_dummy_key_for_testing');
            echo "<div class='success'>✅ Stripe API key setting works</div>";
            
            // Check if main Stripe classes exist
            $stripe_classes = [
                '\Stripe\PaymentIntent',
                '\Stripe\Customer', 
                '\Stripe\Charge'
            ];
            
            foreach ($stripe_classes as $class) {
                if (class_exists($class)) {
                    echo "<div class='success'>✅ $class available</div>";
                } else {
                    echo "<div class='error'>❌ $class missing</div>";
                }
            }
            
        } else {
            echo "<div class='error'>❌ Stripe\Stripe class not found</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Autoloader not found at: $autoloader_path</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Stripe test failed: " . $e->getMessage() . "</div>";
}

// Test 2: Database Manager  
echo "<h2>2. Database Manager Test</h2>";

try {
    // Load the safe database manager
    require_once __DIR__ . '/includes/class-database-manager-safe.php';
    
    if (class_exists('BMS_Database_Manager_Safe')) {
        echo "<div class='success'>✅ Safe Database Manager loaded</div>";
        
        // Check table status
        $table_status = BMS_Database_Manager_Safe::verify_tables();
        
        echo "<h3>Table Status:</h3>";
        foreach ($table_status as $table => $exists) {
            if ($exists) {
                echo "<div class='success'>✅ $table exists</div>";
            } else {
                echo "<div class='warning'>⚠️  $table missing (will be created on plugin activation)</div>";
            }
        }
        
    } else {
        echo "<div class='error'>❌ Safe Database Manager not loaded</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Database test failed: " . $e->getMessage() . "</div>";
}

// Test 3: WordPress Integration
echo "<h2>3. WordPress Integration Test</h2>";

if (function_exists('wp_get_current_user')) {
    echo "<div class='success'>✅ WordPress functions available</div>";
    
    // Check if plugin constants are defined
    if (defined('BMS_VERSION')) {
        echo "<div class='success'>✅ Plugin constants defined (BMS_VERSION: " . BMS_VERSION . ")</div>";
    } else {
        echo "<div class='warning'>⚠️  Plugin constants not defined (plugin may not be activated)</div>";
    }
    
    // Check plugin activation status
    $plugin_file = 'blue-motors-southampton/blue-motors-southampton.php';
    if (is_plugin_active($plugin_file)) {
        echo "<div class='success'>✅ Plugin is activated</div>";
    } else {
        echo "<div class='warning'>⚠️  Plugin is not activated</div>";
    }
    
} else {
    echo "<div class='error'>❌ WordPress not properly loaded</div>";
}

echo "<h2>🎯 Summary</h2>";
echo "<p><strong>If all tests show ✅:</strong> Your Stripe and database issues are resolved!</p>";
echo "<p><strong>If you see ❌ errors:</strong> Please share the error details for further assistance.</p>";

echo "<hr><p><small>Test completed at: " . date('Y-m-d H:i:s') . "</small></p>";
