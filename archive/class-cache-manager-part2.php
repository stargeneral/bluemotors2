    
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
            $this->cache_prefix . 'revenue_stats'];
        
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
             LIMIT 20");
        
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
             WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        
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
                'timestamp' => time();
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
                'timestamp' => time();
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
                    'timestamp' => time();
                ];
                
                $this->cache_tyre_search($size, $empty_result);
            }
        }
    }
}

// Initialize cache manager
if (class_exists('BlueMotosSouthampton\Services\CacheManager')) {
    new BMS_Cache_Manager();
}
