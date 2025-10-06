<?php
/**
 * Shortcode Validator and Fixer
 * Blue Motors Southampton Plugin
 * 
 * Identifies and fixes common shortcode issues
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode Validator Class
 */
class BMS_Shortcode_Validator {
    
    private $issues_found = [];
    private $fixes_applied = [];
    
    /**
     * Run comprehensive shortcode validation
     */
    public function validate_all_shortcodes() {
        $this->issues_found = [];
        $this->fixes_applied = [];
        
        // Check shortcode registration
        $this->check_shortcode_registrations();
        
        // Check dependencies
        $this->check_dependencies();
        
        // Check templates and files
        $this->check_template_files();
        
        // Check JavaScript and CSS dependencies
        $this->check_asset_dependencies();
        
        // Test basic rendering
        $this->test_basic_rendering();
        
        return [
            'issues' => $this->issues_found,
            'fixes' => $this->fixes_applied
        ];
    }
    
    /**
     * Check if all shortcodes are properly registered
     */
    private function check_shortcode_registrations() {
        $expected_shortcodes = [
            'bms_vehicle_lookup' => 'Vehicle Lookup Form',
            'bms_booking_form' => 'Main Booking Form',
            'bms_service_cards' => 'Service Cards Display',
            'bms_enhanced_services' => 'Enhanced Service Cards',
            'bms_service_list' => 'Service List Display',
            'bms_service_card' => 'Single Service Card',
            'bms_tyre_search' => 'Tyre Search Interface',
            'bms_smart_scheduler' => 'AI Smart Scheduler',
            'bms_location_info' => 'Location Information',
            'bms_opening_hours' => 'Opening Hours Only',
            'bms_contact_form' => 'Contact Form',
            'bms_vs_f1' => 'Comparison vs industry leaders',
            'bms_comparison_table' => 'Comparison Table',
            'bms_why_choose_us' => 'Why Choose Us',
            'bms_price_calculator' => 'Price Calculator',
            'bms_booking_status' => 'Booking Status',
            'vehicle_lookup' => 'Vehicle Lookup (Alias)'
        ];
        
        global $shortcode_tags;
        
        foreach ($expected_shortcodes as $shortcode => $name) {
            if (!isset($shortcode_tags[$shortcode])) {
                $this->issues_found[] = [
                    'type' => 'error',
                    'category' => 'registration',
                    'shortcode' => $shortcode,
                    'message' => "Shortcode [$shortcode] ($name) is not registered",
                    'fix_available' => $this->can_fix_registration($shortcode)
                ];
            } elseif (!is_callable($shortcode_tags[$shortcode])) {
                $this->issues_found[] = [
                    'type' => 'error',
                    'category' => 'registration',
                    'shortcode' => $shortcode,
                    'message' => "Shortcode [$shortcode] callback is not callable",
                    'fix_available' => false
                ];
            }
        }
    }
    
    /**
     * Check if we can fix a shortcode registration
     */
    private function can_fix_registration($shortcode) {
        $fixable_shortcodes = [
            'bms_vs_f1' => 'competitive-shortcodes.php',
            'bms_why_choose_us' => 'competitive-shortcodes.php',
            'bms_price_calculator' => 'competitive-shortcodes.php',
            'bms_booking_status' => 'competitive-shortcodes.php'
        ];
        
        return isset($fixable_shortcodes[$shortcode]);
    }
    
    /**
     * Check shortcode dependencies
     */
    private function check_dependencies() {
        // Check if Enhanced Service Manager is available
        if (!class_exists('\\BlueMotosSouthampton\\Services\\ServiceManagerEnhanced')) {
            $this->issues_found[] = [
                'type' => 'warning',
                'category' => 'dependency',
                'message' => 'ServiceManagerEnhanced class not found - enhanced services may not work',
                'fix_available' => true
            ];
        }
        
        // Check if Session class is available
        if (!class_exists('BMS_Session')) {
            $this->issues_found[] = [
                'type' => 'warning',
                'category' => 'dependency',
                'message' => 'BMS_Session class not found - booking form may not work properly',
                'fix_available' => false
            ];
        }
        
        // Check if database tables exist
        global $wpdb;
        $required_tables = ['bms_services', 'bms_appointments', 'bms_customers'];
        
        foreach ($required_tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
            
            if (!$table_exists) {
                $this->issues_found[] = [
                    'type' => 'warning',
                    'category' => 'database',
                    'message' => "Database table $table_name does not exist",
                    'fix_available' => true
                ];
            }
        }
    }
    
    /**
     * Check template files
     */
    private function check_template_files() {
        $template_files = [
            'public/templates/tyre-search-f1-style.php' => 'Tyre search template',
            'templates/enhanced-service-cards.php' => 'Enhanced service cards template'
        ];
        
        foreach ($template_files as $file => $description) {
            $full_path = BMS_PLUGIN_DIR . $file;
            if (!file_exists($full_path)) {
                $this->issues_found[] = [
                    'type' => 'warning',
                    'category' => 'template',
                    'message' => "$description file missing: $file",
                    'fix_available' => false
                ];
            }
        }
    }
    
    /**
     * Check asset dependencies
     */
    private function check_asset_dependencies() {
        $required_scripts = [
            'bms-vehicle-lookup' => 'assets/js/vehicle-lookup.js',
            'bms-booking' => 'assets/js/booking.js',
            'bms-tyre-booking' => 'assets/js/tyre-booking.js',
            'bms-competitive-messaging' => 'assets/js/competitive-messaging.js'
        ];
        
        $required_styles = [
            'bms-vehicle-lookup' => 'assets/css/vehicle-lookup.css',
            'bms-public' => 'assets/css/public.css',
            'bms-tyre-search' => 'assets/css/tyre-search.css'
        ];
        
        foreach ($required_scripts as $handle => $file) {
            $full_path = BMS_PLUGIN_DIR . $file;
            if (!file_exists($full_path)) {
                $this->issues_found[] = [
                    'type' => 'error',
                    'category' => 'assets',
                    'message' => "Required JavaScript file missing: $file",
                    'fix_available' => false
                ];
            }
        }
        
        foreach ($required_styles as $handle => $file) {
            $full_path = BMS_PLUGIN_DIR . $file;
            if (!file_exists($full_path)) {
                $this->issues_found[] = [
                    'type' => 'error',
                    'category' => 'assets',
                    'message' => "Required CSS file missing: $file",
                    'fix_available' => false
                ];
            }
        }
    }
    
    /**
     * Test basic rendering of core shortcodes
     */
    private function test_basic_rendering() {
        $core_shortcodes = [
            '[bms_service_cards]',
            '[bms_service_list]', 
            '[bms_location_info]',
            '[bms_smart_scheduler]'
        ];
        
        foreach ($core_shortcodes as $shortcode) {
            try {
                $output = do_shortcode($shortcode);
                
                if (empty($output) || $output === $shortcode) {
                    $this->issues_found[] = [
                        'type' => 'error',
                        'category' => 'rendering',
                        'shortcode' => $shortcode,
                        'message' => "Shortcode $shortcode fails to render or returns empty output",
                        'fix_available' => false
                    ];
                } elseif (strlen($output) < 100) {
                    $this->issues_found[] = [
                        'type' => 'warning',
                        'category' => 'rendering',
                        'shortcode' => $shortcode,
                        'message' => "Shortcode $shortcode renders but output is very short (may be incomplete)",
                        'fix_available' => false
                    ];
                }
            } catch (Exception $e) {
                $this->issues_found[] = [
                    'type' => 'error',
                    'category' => 'rendering',
                    'shortcode' => $shortcode,
                    'message' => "Shortcode $shortcode throws exception: " . $e->getMessage(),
                    'fix_available' => false
                ];
            }
        }
    }
    
    /**
     * Apply automatic fixes where possible
     */
    public function apply_automatic_fixes() {
        $fixes_applied = 0;
        
        foreach ($this->issues_found as $issue) {
            if (!$issue['fix_available']) {
                continue;
            }
            
            switch ($issue['category']) {
                case 'registration':
                    if ($this->fix_shortcode_registration($issue['shortcode'])) {
                        $fixes_applied++;
                        $this->fixes_applied[] = "Fixed registration for " . $issue['shortcode'];
                    }
                    break;
                    
                case 'dependency':
                    if ($this->fix_dependency_issue($issue)) {
                        $fixes_applied++;
                        $this->fixes_applied[] = "Fixed dependency: " . $issue['message'];
                    }
                    break;
                    
                case 'database':
                    if ($this->fix_database_issue($issue)) {
                        $fixes_applied++;
                        $this->fixes_applied[] = "Fixed database: " . $issue['message'];
                    }
                    break;
            }
        }
        
        return $fixes_applied;
    }
    
    /**
     * Fix shortcode registration issues
     */
    private function fix_shortcode_registration($shortcode) {
        // Try to manually register missing competitive shortcodes
        if (in_array($shortcode, ['bms_vs_f1', 'bms_why_choose_us', 'bms_price_calculator', 'bms_booking_status'])) {
            $competitive_file = BMS_PLUGIN_DIR . 'includes/shortcodes/competitive-shortcodes.php';
            if (file_exists($competitive_file)) {
                require_once $competitive_file;
                return shortcode_exists($shortcode);
            }
        }
        
        return false;
    }
    
    /**
     * Fix dependency issues
     */
    private function fix_dependency_issue($issue) {
        // Try to initialize ServiceManagerEnhanced if missing
        if (strpos($issue['message'], 'ServiceManagerEnhanced') !== false) {
            $service_manager_file = BMS_PLUGIN_DIR . 'includes/services/class-service-manager-enhanced.php';
            if (file_exists($service_manager_file)) {
                require_once $service_manager_file;
                return class_exists('\\BlueMotosSouthampton\\Services\\ServiceManagerEnhanced');
            }
        }
        
        return false;
    }
    
    /**
     * Fix database issues
     */
    private function fix_database_issue($issue) {
        // Try to create missing tables using the database manager
        if (class_exists('BMS_Database_Manager_Enhanced')) {
            try {
                $db_manager = new BMS_Database_Manager_Enhanced();
                $result = $db_manager->create_tables();
                return $result['success'] ?? false;
            } catch (Exception $e) {
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Generate validation report
     */
    public function generate_report() {
        $results = $this->validate_all_shortcodes();
        
        $total_issues = count($results['issues']);
        $errors = array_filter($results['issues'], function($issue) {
            return $issue['type'] === 'error';
        });
        $warnings = array_filter($results['issues'], function($issue) {
            return $issue['type'] === 'warning';
        });
        
        $report = [
            'summary' => [
                'total_issues' => $total_issues,
                'errors' => count($errors),
                'warnings' => count($warnings),
                'status' => $total_issues === 0 ? 'excellent' : (count($errors) === 0 ? 'good' : 'needs_attention')
            ],
            'issues' => $results['issues'],
            'fixes_available' => array_filter($results['issues'], function($issue) {
                return $issue['fix_available'];
            }),
            'recommendations' => $this->generate_recommendations($results['issues'])
        ];
        
        return $report;
    }
    
    /**
     * Generate recommendations based on issues found
     */
    private function generate_recommendations($issues) {
        $recommendations = [];
        
        $has_registration_issues = false;
        $has_rendering_issues = false;
        $has_asset_issues = false;
        $has_db_issues = false;
        
        foreach ($issues as $issue) {
            switch ($issue['category']) {
                case 'registration':
                    $has_registration_issues = true;
                    break;
                case 'rendering':
                    $has_rendering_issues = true;
                    break;
                case 'assets':
                    $has_asset_issues = true;
                    break;
                case 'database':
                    $has_db_issues = true;
                    break;
            }
        }
        
        if ($has_registration_issues) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Fix Shortcode Registration',
                'description' => 'Some shortcodes are not properly registered. Check that all shortcode files are loaded and add_shortcode() calls are working.'
            ];
        }
        
        if ($has_asset_issues) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Check Asset Files',
                'description' => 'Required CSS/JS files are missing. Ensure all asset files are present and properly enqueued.'
            ];
        }
        
        if ($has_db_issues) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Initialize Database',
                'description' => 'Some database tables are missing. Run the database initialization via Database Status page.'
            ];
        }
        
        if ($has_rendering_issues) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Fix Rendering Issues',
                'description' => 'Some shortcodes fail to render properly. Check for PHP errors in the shortcode callback functions.'
            ];
        }
        
        if (empty($issues)) {
            $recommendations[] = [
                'priority' => 'low',
                'title' => 'All Systems Operational',
                'description' => 'All shortcodes are functioning properly! Consider testing on frontend with real content.'
            ];
        }
        
        return $recommendations;
    }
}

/**
 * Helper function to run shortcode validation
 */
function bms_validate_shortcodes() {
    $validator = new BMS_Shortcode_Validator();
    return $validator->generate_report();
}

/**
 * Helper function to fix shortcode issues
 */
function bms_fix_shortcode_issues() {
    $validator = new BMS_Shortcode_Validator();
    $validator->validate_all_shortcodes();
    return $validator->apply_automatic_fixes();
}
