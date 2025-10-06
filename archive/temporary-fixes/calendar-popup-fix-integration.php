<?php
/**
 * Calendar Popup Fix Integration
 * Blue Motors Southampton - Integrates the calendar popup fix into WordPress
 * 
 * This file ensures the calendar fix JavaScript is loaded on pages with tyre booking forms
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BMS_Calendar_Fix_Integration {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_calendar_fix_assets'));
        add_action('wp_footer', array($this, 'add_calendar_fix_inline_script'));
    }
    
    /**
     * Enqueue the necessary assets for the calendar fix
     */
    public function enqueue_calendar_fix_assets() {
        // Only load on pages that might have the tyre booking form
        if (!$this->should_load_calendar_fix()) {
            return;
        }
        
        // Ensure jQuery and jQuery UI are loaded
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        
        // Enqueue jQuery UI CSS if not already loaded
        if (!wp_style_is('jquery-ui-style', 'enqueued')) {
            wp_enqueue_style(
                'jquery-ui-style', 
                'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css',
                array(),
                '1.13.2'
            );
        }
        
        // Enqueue our calendar fix script
        wp_enqueue_script(
            'bms-calendar-fix',
            plugin_dir_url(__FILE__) . 'calendar-popup-fix-implementation.js',
            array('jquery', 'jquery-ui-datepicker'),
            '1.0.0',
            true
        );
        
        // Localize script with AJAX data
        $ajax_data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_calendar_fix_nonce'),
            'debug' => defined('WP_DEBUG') && WP_DEBUG
        );
        
        // Also include existing AJAX data if available
        if (function_exists('bms_get_vehicle_lookup_data')) {
            $existing_data = bms_get_vehicle_lookup_data();
            $ajax_data = array_merge($ajax_data, $existing_data);
        }
        
        wp_localize_script('bms-calendar-fix', 'bmsCalendarFix', $ajax_data);
    }
    
    /**
     * Add inline script to initialize the calendar fix
     */
    public function add_calendar_fix_inline_script() {
        if (!$this->should_load_calendar_fix()) {
            return;
        }
        
        ?>
        <script type="text/javascript">
        console.log('🔧 BMS Calendar Fix Integration: Loaded');
        
        // Ensure the fix initializes after all other scripts
        jQuery(document).ready(function($) {
            console.log('📅 BMS Calendar Fix Integration: Document ready');
            
            // Wait a bit for other scripts to initialize
            setTimeout(function() {
                if (typeof BMSCalendarFix !== 'undefined') {
                    console.log('✅ BMS Calendar Fix: Reinitializing...');
                    BMSCalendarFix.init();
                } else {
                    console.warn('⚠️ BMS Calendar Fix: BMSCalendarFix object not found');
                }
            }, 1000);
            
            // Also reinitialize after AJAX calls complete
            $(document).ajaxComplete(function() {
                setTimeout(function() {
                    if (typeof BMSCalendarFix !== 'undefined') {
                        console.log('🔄 BMS Calendar Fix: Reinitializing after AJAX');
                        BMSCalendarFix.init();
                    }
                }, 500);
            });
        });
        
        // Global function for manual testing
        window.testCalendarFix = function() {
            if (typeof BMSCalendarFix !== 'undefined') {
                console.log('🧪 Testing calendar fix...');
                BMSCalendarFix.showMessage('📅 Calendar fix test - Click the date field!', 'info');
                return true;
            } else {
                console.error('❌ Calendar fix not loaded');
                return false;
            }
        };
        </script>
        <?php
    }
    
    /**
     * Determine if we should load the calendar fix on this page
     */
    private function should_load_calendar_fix() {
        global $post;
        
        // Always load on pages that might contain tyre booking forms
        $load_conditions = array(
            // If page contains tyre search shortcode
            has_shortcode(get_post_field('post_content', get_the_ID()), 'tyre_search'),
            has_shortcode(get_post_field('post_content', get_the_ID()), 'bms_tyre_search'),
            
            // If page slug suggests tyre functionality
            is_page() && strpos(get_post_field('post_name', get_the_ID()), 'tyre') !== false,
            is_page() && strpos(get_post_field('post_name', get_the_ID()), 'testpage') !== false,
            
            // If page title contains relevant keywords
            strpos(strtolower(get_the_title()), 'tyre') !== false,
            strpos(strtolower(get_the_title()), 'booking') !== false,
            
            // Admin pages for testing
            is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'bms') !== false,
            
            // Load on specific pages where we know the form appears
            is_page(array('testpage2', 'tyre-search', 'book-tyres')),
        );
        
        // Check if any condition is true
        foreach ($load_conditions as $condition) {
            if ($condition) {
                return true;
            }
        }
        
        // Also check post content directly for tyre-related content
        if ($post && strpos($post->post_content, 'fitting-date') !== false) {
            return true;
        }
        
        return false;
    }
}

// Initialize the calendar fix integration
new BMS_Calendar_Fix_Integration();

/**
 * Manual activation function for testing
 */
function bms_activate_calendar_fix() {
    echo '<div class="notice notice-success"><p>';
    echo '📅 <strong>BMS Calendar Fix Activated!</strong> ';
    echo 'The calendar popup fix is now active on tyre booking pages.';
    echo '</p></div>';
}

/**
 * Admin notice about calendar fix status
 */
function bms_calendar_fix_admin_notice() {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>📅 BMS Calendar Fix:</strong> Calendar popup enhancement is active. ';
        echo 'Test it on your <a href="' . home_url('/testpage2/') . '">tyre booking page</a>.</p>';
        echo '</div>';
    }
}

// Add admin notice (only show once)
if (get_option('bms_calendar_fix_notice_shown') !== 'yes') {
    add_action('admin_notices', 'bms_calendar_fix_admin_notice');
    update_option('bms_calendar_fix_notice_shown', 'yes');
}
