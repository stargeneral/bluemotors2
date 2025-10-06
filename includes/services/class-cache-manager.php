<?php
/**
 * Cache Manager for Blue Motors Southampton
 * Ensuring faster performance than industry leaders
 * 
 * @package BlueMotosSouthampton
 * @version 1.0.0
 */

namespace BlueMotosSouthampton\Services;

if (!defined('ABSPATH')) {
    exit;
}

class CacheManager {
    
    private $cache_prefix = 'bms_';
    private $default_expiry = 3600; // 1 hour
    private $performance_tracking = true;
    
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_cached_assets']);
        add_action('bms_booking_created', [$this, 'clear_booking_cache']);
        add_action('bms_tyre_updated', [$this, 'clear_tyre_cache']);
        add_action('init', [$this, 'init_performance_tracking']);
        
        // Schedule cache cleanup
        if (!wp_next_scheduled('bms_cache_cleanup')) {
            wp_schedule_event(time(), 'daily', 'bms_cache_cleanup');
        }
        
        add_action('bms_cache_cleanup', [$this, 'cleanup_expired_cache']);
    }
    
    /**
     * Initialize performance tracking
     */
    public function init_performance_tracking() {
        if ($this->performance_tracking && defined('WP_DEBUG') && WP_DEBUG) {
            // Disable performance tracking on shutdown to avoid fatal errors
            // Performance logging is handled by log_performance method instead
        }
    }
    
    /**
     * Cache tyre search results with advanced indexing
     */
    public function cache_tyre_search($size_string, $results, $filters = []) {
        $cache_key = $this->cache_prefix . 'tyres_' . md5($size_string . serialize($filters));
        
        $cached_data = [
            'results' => $results,
            'count' => count($results),
            'timestamp' => time(),
            'filters_applied' => $filters
        ];
        
        $success = wp_cache_set($cache_key, $cached_data, 'bms_tyres', $this->default_expiry);
        
        if ($success) {
            $this->update_cache_stats($cache_key, 'set');
        }
        
        return $success;
    }
    
    /**
     * Get cached tyre search results with hit tracking
     */
    public function get_cached_tyre_search($size_string, $filters = []) {
        $cache_key = $this->cache_prefix . 'tyres_' . md5($size_string . serialize($filters));
        
        $cached_data = wp_cache_get($cache_key, 'bms_tyres');
        
        if ($cached_data !== false) {
            $this->update_cache_stats($cache_key, 'hit');
            
            // Check if cached data is still fresh (within last hour)
            if ((time() - $cached_data['timestamp']) < $this->default_expiry) {
                return $cached_data['results'];
            } else {
                // Cache expired, remove it
                wp_cache_delete($cache_key, 'bms_tyres');
                $this->update_cache_stats($cache_key, 'expired');
            }
        } else {
            $this->update_cache_stats($cache_key, 'miss');
        }
        
        return false;
    }
    
    /**
     * Cache vehicle lookup results with extended TTL
     */
    public function cache_vehicle_lookup($registration, $vehicle_data) {
        $cache_key = $this->cache_prefix . 'vehicle_' . strtoupper($registration);
        
        $cached_data = [
            'vehicle_data' => $vehicle_data,
            'registration' => strtoupper($registration),
            'timestamp' => time(),
            'source' => 'dvla_api'
        ];
        
        // Vehicle data is relatively static, cache for 24 hours
        wp_cache_set($cache_key, $cached_data, 'bms_vehicles', 86400);
        $this->update_cache_stats($cache_key, 'set');
        
        return true;
    }
    
    /**
     * Get cached vehicle lookup with validation
     */
    public function get_cached_vehicle_lookup($registration) {
        $cache_key = $this->cache_prefix . 'vehicle_' . strtoupper($registration);
        
        $cached_data = wp_cache_get($cache_key, 'bms_vehicles');
        
        if ($cached_data !== false) {
            $this->update_cache_stats($cache_key, 'hit');
            
            // Vehicle data is valid for 24 hours
            if ((time() - $cached_data['timestamp']) < 86400) {
                return $cached_data['vehicle_data'];
            } else {
                wp_cache_delete($cache_key, 'bms_vehicles');
                $this->update_cache_stats($cache_key, 'expired');
            }
        } else {
            $this->update_cache_stats($cache_key, 'miss');
        }
        
        return false;
    }
    
    /**
     * Cache dashboard data with smart refresh
     */
    public function cache_dashboard_data($data, $page = 'main') {
        $cache_key = $this->cache_prefix . 'dashboard_' . $page . '_' . date('Y-m-d-H');
        
        $cached_data = [
            'dashboard_data' => $data,
            'page' => $page,
            'timestamp' => time(),
            'hour' => date('H')
        ];
        
        // Dashboard data refreshes every 15 minutes
        wp_cache_set($cache_key, $cached_data, 'bms_dashboard', 900);
        $this->update_cache_stats($cache_key, 'set');
        
        return true;
    }
    
    /**
     * Get cached dashboard data
     */
    public function get_cached_dashboard_data($page = 'main') {
        $cache_key = $this->cache_prefix . 'dashboard_' . $page . '_' . date('Y-m-d-H');
        
        $cached_data = wp_cache_get($cache_key, 'bms_dashboard');
        
        if ($cached_data !== false) {
            $this->update_cache_stats($cache_key, 'hit');
            return $cached_data['dashboard_data'];
        } else {
            $this->update_cache_stats($cache_key, 'miss');
            return false;
        }
    }
    
    /**
     * Cache service pricing with dynamic updates
     */
    public function cache_service_pricing($pricing_data, $service_type = 'all') {
        $cache_key = $this->cache_prefix . 'pricing_' . $service_type;
        
        $cached_data = [
            'pricing' => $pricing_data,
            'service_type' => $service_type,
            'timestamp' => time(),
            'version' => BMS_VERSION
        ];
        
        wp_cache_set($cache_key, $cached_data, 'bms_pricing', 3600);
        $this->update_cache_stats($cache_key, 'set');
        
        return true;
    }
    
    /**
     * Get cached service pricing
     */
    public function get_cached_service_pricing($service_type = 'all') {
        $cache_key = $this->cache_prefix . 'pricing_' . $service_type;
        
        $cached_data = wp_cache_get($cache_key, 'bms_pricing');
        
        if ($cached_data !== false) {
            $this->update_cache_stats($cache_key, 'hit');
            return $cached_data['pricing'];
        } else {
            $this->update_cache_stats($cache_key, 'miss');
            return false;
        }
    }
    
    /**
     * Clear all booking-related cache
     */
    public function clear_booking_cache() {
        // Clear all booking-related cache groups
        wp_cache_flush_group('bms_dashboard');
        wp_cache_flush_group('bms_bookings');
        
        // Clear specific booking cache keys
        $cache_keys = [
            $this->cache_prefix . 'dashboard_main_' . date('Y-m-d-H'),
            $this->cache_prefix . 'bookings_today',
            $this->cache_prefix . 'bookings_week',
            $this->cache_prefix . 'revenue_stats'
        ];
        
        foreach ($cache_keys as $key) {
            wp_cache_delete($key);
            $this->update_cache_stats($key, 'cleared');
        }
        
        $this->log_performance('cache_clear_booking', 'Cache cleared for booking updates');
    }
    
    /**
     * Clear tyre-related cache
     */
    public function clear_tyre_cache() {
        wp_cache_flush_group('bms_tyres');
        
        // Clear tyre search patterns
        $common_sizes = ['195/65R15', '205/55R16', '225/45R17', '215/60R16'];
        foreach ($common_sizes as $size) {
            $cache_key = $this->cache_prefix . 'tyres_' . md5($size);
            wp_cache_delete($cache_key, 'bms_tyres');
            $this->update_cache_stats($cache_key, 'cleared');
        }
        
        $this->log_performance('cache_clear_tyre', 'Cache cleared for tyre updates');
    }
    
    /**
     * Generate critical CSS for above-the-fold content
     */
    public function generate_critical_css() {
        $critical_css = '
        /* Critical CSS for above-the-fold content - Phase 4 Optimization */
        .bms-booking-container {;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .competitive-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .advantages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        
        .advantage-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.1);
            padding: 12px;
            border-radius: 8px;
        }
        
        .service-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .service-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Loading states for fast perceived performance */
        .bms-loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        ';
        
        // Save critical CSS to file with cache busting
        $critical_css_file = BMS_PLUGIN_DIR . 'assets/css/critical.css';
        file_put_contents($critical_css_file, $critical_css);
        
        // Update version for cache busting
        update_option('bms_critical_css_version', time());
        
        return $critical_css_file;
    }
    
    /**
     * Update cache statistics for monitoring
     */
    private function update_cache_stats($cache_key, $action) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'bms_cache_stats';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return false;
        }
        
        switch ($action) {
            case 'hit':
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO $table (cache_key, hit_count, miss_count) 
                     VALUES (%s, 1, 0) 
                     ON DUPLICATE KEY UPDATE hit_count = hit_count + 1, last_accessed = NOW()",
                    $cache_key
                ));
                break;
                
            case 'miss':
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO $table (cache_key, hit_count, miss_count) 
                     VALUES (%s, 0, 1) 
                     ON DUPLICATE KEY UPDATE miss_count = miss_count + 1, last_accessed = NOW()",
                    $cache_key
                ));
                break;
                
            case 'set':
            case 'cleared':
            case 'expired':
                $wpdb->query($wpdb->prepare(
                    "INSERT INTO $table (cache_key, hit_count, miss_count) 
                     VALUES (%s, 0, 0) 
                     ON DUPLICATE KEY UPDATE last_accessed = NOW()",
                    $cache_key
                ));
                break;
        }
    }
    
    /**
     * Log performance metrics
     */
    public function log_performance($operation, $details = '') {
        if (!$this->performance_tracking) return;
        
        $execution_time = microtime(true) - (defined('WP_START_TIME') ? WP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT']);
        $memory_usage = memory_get_peak_usage(true);
        
        global $wpdb;
        $table = $wpdb->prefix . 'bms_performance_log';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return false;
        }
        
        $wpdb->insert(
            $table,
            [
                'query_type' => $operation,
                'execution_time' => round($execution_time, 4),
                'memory_usage' => $memory_usage,
                'details' => $details
            ],
            ['%s', '%f', '%d', '%s']
        );
    }
    
    /**
     * Cleanup expired cache entries
     */
    public function cleanup_expired_cache() {
        global $wpdb;
        
        // Clean up old cache stats (keep 7 days)
        $cache_table = $wpdb->prefix . 'bms_cache_stats';
        if ($wpdb->get_var("SHOW TABLES LIKE '$cache_table'") === $cache_table) {
            $wpdb->query(
                "DELETE FROM $cache_table 
                 WHERE last_accessed < DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
        }
        
        // Clean up old performance logs (keep 30 days)
        $perf_table = $wpdb->prefix . 'bms_performance_log';
        if ($wpdb->get_var("SHOW TABLES LIKE '$perf_table'") === $perf_table) {
            $wpdb->query(
                "DELETE FROM $perf_table 
                 WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
        }
        
        // Clear WordPress object cache groups that might be stale
        wp_cache_flush_group('bms_expired');
        
        $this->log_performance('cache_cleanup', 'Automated cache cleanup completed');
    }
    
    /**
     * Get cache performance statistics
     */
    public function get_cache_stats() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'bms_cache_stats';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return false;
        }
        
        $stats = $wpdb->get_results(
            "SELECT 
                cache_key,
                hit_count,
                miss_count,
                ROUND((hit_count / (hit_count + miss_count + 0.01)) * 100, 2) as hit_ratio,
                last_accessed
             FROM $table 
             WHERE hit_count > 0 OR miss_count > 0
             ORDER BY (hit_count + miss_count) DESC 
             LIMIT 20"
        );
        
        return $stats;
    }
    
    /**
     * Get performance metrics summary
     */
    public function get_performance_summary() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'bms_performance_log';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return false;
        }
        
        $summary = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_operations,
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                AVG(memory_usage) as avg_memory_usage,
                MAX(memory_usage) as max_memory_usage
             FROM $table 
             WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        return $summary;
    }
    
    /**
     * Force cache regeneration for critical data
     */
    public function warm_cache() {
        // Warm up common cache entries
        $this->warm_dashboard_cache();
        $this->warm_service_cache();
        $this->warm_tyre_cache();
        
        $this->log_performance('cache_warm', 'Cache warming completed');
    }
    
    /**
     * Warm up dashboard cache
     */
    private function warm_dashboard_cache() {
        // Pre-load today's dashboard data
        if (!$this->get_cached_dashboard_data('main')) {
            // This would normally be called by the dashboard page
            // We'll create a lightweight version
            $dashboard_data = [
                'bookings_today' => 0,
                'revenue_today' => 0,
                'cache_warmed' => true,
                'timestamp' => time()
            ];
            
            $this->cache_dashboard_data($dashboard_data, 'main');
        }
    }
    
    /**
     * Warm up service cache
     */
    private function warm_service_cache() {
        // Pre-load service pricing if not cached
        if (!$this->get_cached_service_pricing('all')) {
            // This would normally be loaded by the service manager
            $pricing_data = [
                'cache_warmed' => true,
                'timestamp' => time()
            ];
            
            $this->cache_service_pricing($pricing_data, 'all');
        }
    }
    
    /**
     * Warm up tyre cache
     */
    private function warm_tyre_cache() {
        // Pre-load common tyre sizes
        $common_sizes = ['195/65R15', '205/55R16', '225/45R17'];
        
        foreach ($common_sizes as $size) {
            if (!$this->get_cached_tyre_search($size)) {
                // Pre-cache empty result to avoid repeated database hits
                $empty_result = [
                    'cache_warmed' => true,
                    'size' => $size,
                    'timestamp' => time()
                ];
                
                $this->cache_tyre_search($size, $empty_result);
            }
        }
    }
    
    /**
     * Enqueue minified and cached assets with version control
     */
    public function enqueue_cached_assets() {
        // Check if minified versions exist, otherwise use originals
        $css_files = [
            'public.css',
            'mobile-enhancements.css',
            'uk-date-styles.css',
            'professional-messaging.css'
        ];
        
        $js_files = [
            'booking.js',
            'tyre-booking.js',
            'payment-improvements.js',
            'uk-date-handler.js',
            'professional-messaging.js'
        ];
        
        // Enqueue optimized CSS with proper dependencies
        foreach ($css_files as $index => $file) {
            $minified_file = str_replace('.css', '.min.css', $file);
            $file_path = BMS_PLUGIN_DIR . 'assets/css/' . $minified_file;
            $original_path = BMS_PLUGIN_DIR . 'assets/css/' . $file;
            
            // Use minified if exists, otherwise original
            if (file_exists($file_path)) {
                $use_file = $minified_file;
                $use_path = $file_path;
            } elseif (file_exists($original_path)) {
                $use_file = $file;
                $use_path = $original_path;
            } else {
                continue; // Skip if neither exists
            }
            
            $dependencies = $index > 0 ? ['bms-' . str_replace('.css', '', $css_files[$index - 1])] : [];
            
            wp_enqueue_style(
                'bms-' . str_replace('.css', '', $file),
                BMS_PLUGIN_URL . 'assets/css/' . $use_file,
                $dependencies,
                filemtime($use_path)
            );
        }
        
        // Enqueue optimized JS with proper dependencies
        foreach ($js_files as $index => $file) {
            $minified_file = str_replace('.js', '.min.js', $file);
            $file_path = BMS_PLUGIN_DIR . 'assets/js/' . $minified_file;
            $original_path = BMS_PLUGIN_DIR . 'assets/js/' . $file;
            
            // Use minified if exists, otherwise original
            if (file_exists($file_path)) {
                $use_file = $minified_file;
                $use_path = $file_path;
            } elseif (file_exists($original_path)) {
                $use_file = $file;
                $use_path = $original_path;
            } else {
                continue; // Skip if neither exists
            }
            
            $dependencies = ['jquery'];
            if ($index > 0) {
                $dependencies[] = 'bms-' . str_replace('.js', '', $js_files[$index - 1]);
            }
            
            wp_enqueue_script(
                'bms-' . str_replace('.js', '', $file),
                BMS_PLUGIN_URL . 'assets/js/' . $use_file,
                $dependencies,
                filemtime($use_path),
                true
            );
        }
    }
}

// Initialize cache manager
if (class_exists('BlueMotosSouthampton\Services\CacheManager')) {
    new CacheManager();
}
