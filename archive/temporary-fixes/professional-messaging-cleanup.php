<?php
/**
 * Blue Motors Southampton - Professional Messaging Cleanup
 * 
 * This script replaces competitive messaging with professional language
 * while maintaining all functionality. Focus on user-facing files first.
 * 
 * SAFETY: This only modifies content, never removes files
 * 
 * @version 1.0.0
 * @date August 23, 2025
 */

// SAFETY SWITCH - Set to false to execute replacements
$DRY_RUN = false; // EXECUTING PROFESSIONAL MESSAGING CLEANUP

// Get plugin directory
$plugin_dir = dirname(__FILE__);

echo "<h1>🎯 Blue Motors Southampton - Professional Messaging Cleanup</h1>\n";
echo "<p><strong>Plugin Directory:</strong> " . $plugin_dir . "</p>\n";
echo "<p><strong>Mode:</strong> " . ($DRY_RUN ? "🔍 DRY RUN (Preview Only)" : "✏️ LIVE REPLACEMENT") . "</p>\n";

// Professional messaging replacements (based on handover document)
$replacements = [
    // Direct F1 references (most important)
    'F1 Autocentres' => 'industry leaders',
    'F1 AutoCentres' => 'industry leaders',
    'F1 autocentres' => 'industry leaders',
    'F1 autocentre' => 'industry leader',
    'F1 Autocentre' => 'industry leader',
    
    // Competitive superiority claims
    'Superior to F1 Autocentres!' => 'Premium automotive service!',
    'Beat F1\'s system!' => 'Enhanced customer experience!',
    'superior to F1' => 'premium service',
    'better than F1' => 'enhanced service',
    'vs F1 Autocentres' => 'vs competitors',
    'unlike F1' => 'with enhanced features',
    
    // Specific F1 problems mentioned
    'F1 customers must call' => 'Some providers require phone calls',
    'F1 uses confusing' => 'Some providers use confusing',
    'F1 has PayPal issues' => 'Some providers have payment issues',
    'F1 has basic' => 'Some providers offer basic',
    'F1 Cloudflare blocks' => 'Some providers may block',
    
    // Style and technical references
    'F1 Autocentres-style' => 'professional automotive',
    'f1-autocentres-style' => 'professional-automotive',
    'F1-style' => 'professional',
    'f1-style' => 'professional',
    'tyre-search-f1-style' => 'tyre-search-professional',
    
    // CSS class names and IDs (professional presentation)
    'competitive-messaging' => 'professional-messaging',
    'f1-comparison' => 'service-comparison',
    'beat-f1' => 'enhanced-service',
    'vs-f1' => 'competitive-advantage',
    'f1_problems' => 'competitor_issues',
    'beatF1' => 'enhancedService',
    'beat_f1' => 'enhanced_service',
    
    // General competitive language
    'COMPETITIVE ADVANTAGE' => 'SERVICE EXCELLENCE',
    'competitive advantage' => 'service excellence',
    'Competitive advantage' => 'Service excellence',
    'competitive messaging' => 'professional messaging',
    'Competitive messaging' => 'Professional messaging',
    'COMPETITIVE MESSAGING' => 'PROFESSIONAL MESSAGING',
    
    // Admin and user interface text
    'Competitive' => 'Professional',
    'COMPETITIVE' => 'PROFESSIONAL',
];

// Priority files for cleanup (user-facing first)
$priority_files = [
    'Critical User-Facing Files' => [
        'blue-motors-southampton.php',
        'templates/enhanced-service-cards.php',
        'templates/customer-history.php',
        'includes/shortcode-init.php',
        'includes/shortcodes/enhanced-service-cards-shortcode.php',
        'includes/shortcodes/location-info-shortcode.php',
        'includes/shortcodes/tyre-search-shortcode.php',
        'includes/shortcodes/booking-form-shortcode.php',
    ],
    
    'Admin Interface Files' => [
        'admin/dashboard.php',
        'admin/database-status.php',
        'admin/enhanced-settings.php',
        'admin/shortcode-testing.php',
        'admin/shortcodes-reference.php',
        'admin/tyre-management.php',
        'admin/bookings-enhanced.php',
    ],
    
    'Service Classes (Core Functionality)' => [
        'includes/services/class-service-manager-enhanced.php',
        'includes/services/class-customer-service.php',
        'includes/services/class-tyre-service.php',
        'includes/services/class-cache-manager.php',
        'config/enhanced-services.php',
    ],
    
    'Frontend Assets (CSS/JS)' => [
        'assets/css/service-selection.css',
        'assets/css/mobile-enhancements.css',
        'assets/css/tyre-booking.css',
        'assets/css/competitive-messaging.css',
        'assets/js/service-selection.js',
        'assets/js/payment-improvements.js',
        'assets/js/uk-date-handler.js',
    ],
    
    'Templates and Public Files' => [
        'public/templates/service-selection-step.php',
        'public/templates/tyre-search.php',
        'templates/smart-scheduler-widget.php',
    ],
];

// Statistics tracking
$total_files = 0;
$processed_files = 0;
$total_replacements = 0;
$errors = [];

// Count total files
foreach ($priority_files as $category => $files) {
    $total_files += count($files);
}

echo "<h2>📊 Professional Messaging Cleanup</h2>\n";
echo "<ul>\n";
echo "<li><strong>Priority files to process:</strong> " . $total_files . "</li>\n";
echo "<li><strong>Replacement patterns:</strong> " . count($replacements) . "</li>\n";
echo "<li><strong>Goal:</strong> Professional presentation without competitive messaging</li>\n";
echo "</ul>\n";

// Show key replacements
echo "<h2>🔄 Key Professional Replacements</h2>\n";
echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>\n";
echo "<tr style='background: #f0f0f0;'><th>Current (Competitive)</th><th>Professional Replacement</th></tr>\n";

$key_replacements = [
    'F1 Autocentres' => 'industry leaders',
    'Superior to F1!' => 'Premium service!',
    'Beat F1\'s system!' => 'Enhanced customer experience!',
    'competitive advantage' => 'service excellence',
    'F1-style' => 'professional',
    'competitive-messaging' => 'professional-messaging',
];

foreach ($key_replacements as $old => $new) {
    echo "<tr><td><code>" . htmlspecialchars($old) . "</code></td><td><code>" . htmlspecialchars($new) . "</code></td></tr>\n";
}
echo "</table>\n";

echo "<h2>🛠️ Processing Files by Priority</h2>\n";

// Process files by priority category
foreach ($priority_files as $category => $files) {
    echo "<h3>📄 " . htmlspecialchars($category) . " (" . count($files) . " files)</h3>\n";
    echo "<ul>\n";
    
    foreach ($files as $file) {
        $full_path = $plugin_dir . '/' . $file;
        
        if (!file_exists($full_path)) {
            echo "<li><code>" . htmlspecialchars($file) . "</code> - ❌ NOT FOUND</li>\n";
            continue;
        }
        
        // Read file content
        $original_content = file_get_contents($full_path);
        if ($original_content === false) {
            $errors[] = "Failed to read file: " . $file;
            echo "<li><code>" . htmlspecialchars($file) . "</code> - ⚠️ READ ERROR</li>\n";
            continue;
        }
        
        // Apply replacements
        $modified_content = $original_content;
        $file_replacements = 0;
        
        foreach ($replacements as $search => $replace) {
            $count = 0;
            $modified_content = str_replace($search, $replace, $modified_content, $count);
            $file_replacements += $count;
        }
        
        $size = formatBytes(strlen($original_content));
        
        if ($file_replacements > 0) {
            echo "<li><code>" . htmlspecialchars($file) . "</code> - ✅ {$size} ";
            echo "<strong style='color: #007cba;'>({$file_replacements} replacements found)</strong>";
            
            if (!$DRY_RUN) {
                // Write modified content back to file
                if (file_put_contents($full_path, $modified_content) !== false) {
                    $processed_files++;
                    $total_replacements += $file_replacements;
                    echo " → 💾 <em>Updated</em>";
                } else {
                    $errors[] = "Failed to write file: " . $file;
                    echo " → ⚠️ <em>Write failed</em>";
                }
            } else {
                echo " → 🔍 <em>Ready for update</em>";
                $total_replacements += $file_replacements;
            }
        } else {
            echo "<li><code>" . htmlspecialchars($file) . "</code> - ✅ {$size} (no changes needed)";
        }
        echo "</li>\n";
    }
    echo "</ul>\n";
}

// Results summary
echo "<h2>📈 Results Summary</h2>\n";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>\n";
echo "<ul style='margin: 0;'>\n";

if ($DRY_RUN) {
    echo "<li><strong>Files analyzed:</strong> " . $total_files . "</li>\n";
    echo "<li><strong>Total replacements found:</strong> " . $total_replacements . "</li>\n";
    echo "<li><strong>Files needing updates:</strong> " . ($total_files - count(array_filter(range(1, $total_files), function($i) { return $i === 0; }))) . "</li>\n";
} else {
    echo "<li><strong>Files processed:</strong> " . $processed_files . "/" . $total_files . "</li>\n";
    echo "<li><strong>Total replacements made:</strong> " . $total_replacements . "</li>\n";
}

if (!empty($errors)) {
    echo "<li><strong>Errors encountered:</strong> " . count($errors) . "</li>\n";
}

echo "</ul>\n";
echo "</div>\n";

// Show errors if any
if (!empty($errors)) {
    echo "<h3>⚠️ Errors Encountered</h3>\n";
    echo "<ul>\n";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>\n";
    }
    echo "</ul>\n";
}

// Next steps
echo "<h2>🚀 Next Steps</h2>\n";

if ($DRY_RUN) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0;'>\n";
    echo "<h4>🔍 This was a DRY RUN - No files were modified</h4>\n";
    echo "<p><strong>To execute the professional messaging cleanup:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Review the replacements shown above</li>\n";
    echo "<li>Set <code>\$DRY_RUN = false;</code> in this script</li>\n";
    echo "<li>Run this script again to apply changes</li>\n";
    echo "<li>Test your WordPress admin after changes</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>\n";
    echo "<h4>✅ Professional Messaging Cleanup Complete!</h4>\n";
    echo "<p><strong>Professional presentation has been implemented:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>F1 references replaced with 'industry leaders'</li>\n";
    echo "<li>Competitive language updated to professional tone</li>\n";
    echo "<li>CSS classes and JavaScript variables cleaned</li>\n";
    echo "<li>Admin interface messaging professionalized</li>\n";
    echo "</ul>\n";
    echo "<p><strong>Recommended verification:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Visit <a href='/wp-admin/admin.php?page=blue-motors-southampton'>WordPress Admin</a></li>\n";
    echo "<li>Test frontend shortcodes on pages</li>\n";
    echo "<li>Clear any CSS/JS caches</li>\n";
    echo "<li>Check for any display issues</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
}

// Professional presentation message
if (!$DRY_RUN && $total_replacements > 0) {
    echo "<div style='background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>\n";
    echo "<h4>🎉 Professional Transformation Complete</h4>\n";
    echo "<p>Your Blue Motors Southampton plugin now presents with:</p>\n";
    echo "<ul>\n";
    echo "<li><strong>Professional messaging</strong> throughout the interface</li>\n";
    echo "<li><strong>Industry-standard language</strong> instead of competitive comparisons</li>\n";
    echo "<li><strong>Enhanced brand image</strong> focused on your service quality</li>\n";
    echo "<li><strong>Maintained functionality</strong> with improved presentation</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
}

// Utility function
function formatBytes($size, $precision = 2) {
    if ($size === 0) return '0 B';
    $units = array('B', 'KB', 'MB', 'GB');
    $base = log($size, 1024);
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
}

echo "<hr>\n";
echo "<p><em>Professional messaging cleanup completed: " . date('Y-m-d H:i:s') . "</em></p>\n";
echo "<p><strong>🎯 Result:</strong> Professional presentation focused on service excellence</p>\n";
?>