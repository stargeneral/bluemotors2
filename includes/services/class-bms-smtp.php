<?php
/**
 * Blue Motors Southampton SMTP Class
 *
 * Handles SMTP configuration and integration with WordPress mail function.
 *
 * @package BlueMotosSouthampton
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * BMS SMTP class.
 *
 * This class handles all SMTP configuration and mail functionality.
 */
class BMS_SMTP {

    /**
     * Singleton instance
     *
     * @var BMS_SMTP
     */
    private static $instance = null;
    
    /**
     * SMTP settings
     *
     * @var array
     */
    private $settings = array();
    
    /**
     * Whether SMTP is enabled
     *
     * @var bool
     */
    private $is_enabled = false;
    
    /**
     * Get singleton instance
     *
     * @return BMS_SMTP
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Load settings
        $this->load_settings();
        
        // If SMTP is enabled, hook into wp_mail
        if ($this->is_enabled) {
            add_action('phpmailer_init', array($this, 'configure_smtp'), 10, 1);
        }
    }
    
    /**
     * Load SMTP settings
     */
    private function load_settings() {
        // Get settings
        $this->is_enabled = (bool) get_option('bms_smtp_enabled', false);
        
        if ($this->is_enabled) {
            $this->settings = array(
                'host'          => get_option('bms_smtp_host', ''),
                'port'          => get_option('bms_smtp_port', 587),
                'encryption'    => get_option('bms_smtp_encryption', 'tls'),
                'auth'          => (bool) get_option('bms_smtp_auth', true),
                'username'      => get_option('bms_smtp_username', ''),
                'password'      => get_option('bms_smtp_password', ''),
                'from_email'    => get_option('bms_smtp_from_email', get_option('admin_email')),
                'from_name'     => get_option('bms_smtp_from_name', 'Blue Motors Southampton'),
            );
        }
    }
    
    /**
     * Configure PHPMailer to use SMTP
     *
     * @param PHPMailer $phpmailer PHPMailer instance
     */
    public function configure_smtp($phpmailer) {
        // Set the mailer to use SMTP
        $phpmailer->isSMTP();
        
        // Set the SMTP host
        $phpmailer->Host = $this->settings['host'];
        
        // Set the SMTP port
        $phpmailer->Port = $this->settings['port'];
        
        // Set encryption
        if ('tls' === $this->settings['encryption']) {
            $phpmailer->SMTPSecure = 'tls';
        } elseif ('ssl' === $this->settings['encryption']) {
            $phpmailer->SMTPSecure = 'ssl';
        } else {
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        }
        
        // Set authentication
        if ($this->settings['auth']) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $this->settings['username'];
            $phpmailer->Password = $this->settings['password'];
        } else {
            $phpmailer->SMTPAuth = false;
        }
        
        // Set the sender
        $phpmailer->setFrom($this->settings['from_email'], $this->settings['from_name']);
        
        // Debug settings (only in WP_DEBUG mode)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $phpmailer->SMTPDebug = 1;
            $phpmailer->Debugoutput = 'error_log';
        }
    }
    
    /**
     * Test SMTP settings
     *
     * @param array $settings SMTP settings to test
     * @return array Result of the test
     */
    public function test_settings($settings) {
        $result = array(
            'success' => false,
            'message' => 'Unknown error occurred',
        );
        
        try {
            // Save original settings
            $original_settings = $this->settings;
            $original_enabled = $this->is_enabled;
            
            // Temporarily override settings
            $this->settings = $settings;
            $this->is_enabled = true;
            
            // Send a test email
            $to = $settings['from_email'];
            $subject = 'Blue Motors Southampton SMTP Test';
            $message = 'This is a test email from Blue Motors Southampton SMTP settings. If you received this email, your SMTP settings are working correctly.';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            
            // Add the filter to configure SMTP
            add_action('phpmailer_init', array($this, 'configure_smtp'), 10, 1);
            
            // Send the email
            $sent = wp_mail($to, $subject, $message, $headers);
            
            // Remove the filter
            remove_action('phpmailer_init', array($this, 'configure_smtp'), 10);
            
            // Restore original settings
            $this->settings = $original_settings;
            $this->is_enabled = $original_enabled;
            
            if ($sent) {
                $result['success'] = true;
                $result['message'] = 'Test email sent successfully to ' . $to;
            } else {
                $result['message'] = 'Failed to send test email. Please check your settings.';
            }
        } catch (Exception $e) {
            // Restore original settings
            $this->settings = $original_settings;
            $this->is_enabled = $original_enabled;
            
            $result['message'] = 'Exception: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Get SMTP settings
     *
     * @return array SMTP settings
     */
    public function get_settings() {
        return array(
            'enabled' => $this->is_enabled,
            'settings' => $this->settings,
        );
    }
}

// Initialize SMTP
function bms_smtp() {
    return BMS_SMTP::get_instance();
}
