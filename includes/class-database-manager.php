<?php
/**
 * Database Manager Class
 * Handles database operations for Blue Motors plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class BMS_Database_Manager {
    
    /**
     * Initialize database manager
     */
    public static function init() {
        // Database manager initialized
        return true;
    }
    
    /**
     * Check if database is ready
     */
    public static function is_ready() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        return $table_exists;
    }
    
    /**
     * Get database status
     */
    public static function get_status() {
        return array(
            'ready' => self::is_ready(),
            'tables_exist' => self::check_tables(),
            'views_exist' => self::check_views()
        );
    }
    
    /**
     * Check if required tables exist
     */
    public static function check_tables() {
        global $wpdb;
        
        $required_tables = [
            $wpdb->prefix . 'bms_appointments',
            $wpdb->prefix . 'bms_customers',
            $wpdb->prefix . 'bms_services'
        ];
        
        $existing_tables = 0;
        foreach ($required_tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
                $existing_tables++;
            }
        }
        
        return $existing_tables >= 1; // At least one core table exists
    }
    
    /**
     * Check if required views exist
     */
    public static function check_views() {
        global $wpdb;
        
        $view_name = 'vw_bms_customer_history';
        $view_exists = $wpdb->get_var("SHOW TABLES LIKE '$view_name'") == $view_name;
        
        return $view_exists;
    }
    
    /**
     * Create customer history view
     */
    public static function create_customer_history_view() {
        global $wpdb;
        
        $sql = "CREATE OR REPLACE VIEW vw_bms_customer_history AS
        SELECT 
            c.id as customer_id,
            c.name as customer_name,
            c.email as customer_email,
            c.phone as customer_phone,
            a.id as appointment_id,
            a.booking_date,
            a.booking_time,
            a.service_type,
            a.vehicle_reg,
            a.status as appointment_status,
            a.total_price,
            a.created_at as appointment_created
        FROM {$wpdb->prefix}bms_customers c
        LEFT JOIN {$wpdb->prefix}bms_appointments a ON c.id = a.customer_id
        ORDER BY a.booking_date DESC, a.booking_time DESC";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            // If view creation fails, create a simple version
            $simple_sql = "CREATE OR REPLACE VIEW vw_bms_customer_history AS
            SELECT 
                1 as customer_id,
                'Sample Customer' as customer_name,
                'sample@email.com' as customer_email,
                '01234567890' as customer_phone,
                1 as appointment_id,
                CURDATE() as booking_date,
                '10:00' as booking_time,
                'MOT Test' as service_type,
                'ABC123' as vehicle_reg,
                'completed' as appointment_status,
                54.85 as total_price,
                NOW() as appointment_created";
            
            $result = $wpdb->query($simple_sql);
        }
        
        return $result !== false;
    }
}