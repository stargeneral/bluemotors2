<?php
/**
 * Blue Motors Southampton - Session Management
 * Simple session management for booking flow compatibility
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */


namespace BlueMotosSouthampton\Utils;

if (!defined('ABSPATH')) {
    exit;
}

class BMS_Session {
    
    /**
     * Initialize session if not already started
     */
    public static function init() {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }
    
    /**
     * Set session data
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set($key, $value) {
        self::init();
        $_SESSION['bms_' . $key] = $value;
        return true;
    }
    
    /**
     * Get session data
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::init();
        return $_SESSION['bms_' . $key] ?? $default;
    }
    
    /**
     * Check if session key exists
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        self::init();
        return isset($_SESSION['bms_' . $key]);
    }
    
    /**
     * Remove session data
     * 
     * @param string $key
     * @return bool
     */
    public static function remove($key) {
        self::init();
        unset($_SESSION['bms_' . $key]);
        return true;
    }
    
    /**
     * Clear all BMS session data
     * 
     * @return bool
     */
    public static function clear() {
        self::init();
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'bms_') === 0) {
                unset($_SESSION[$key]);
            }
        }
        return true;
    }
    
    /**
     * Get all BMS session data
     * 
     * @return array
     */
    public static function all() {
        self::init();
        $data = [];
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'bms_') === 0) {
                $clean_key = substr($key, 4); // Remove 'bms_' prefix
                $data[$clean_key] = $value;
            }
        }
        return $data;
    }
}
