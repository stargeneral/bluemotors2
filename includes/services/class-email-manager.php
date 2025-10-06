<?php
/**
 * Email Manager for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class BMS_Email_Manager {
    
    /**
     * Send booking confirmation email to customer
     *
     * @param array $booking_data Booking information
     * @return bool Whether email was sent successfully
     */
    public static function send_booking_confirmation($booking_data) {
        // Extract booking data
        extract($booking_data);
        
        // Load email template
        ob_start();
        include BMS_PLUGIN_DIR . 'templates/emails/booking-confirmation.php';
        $email_content = ob_get_clean();
        
        // Email details
        $to = $customer_email;
        $subject = 'Booking Confirmation - ' . $service_name . ' - ' . $booking_reference;
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Blue Motors Southampton <' . get_option('bms_smtp_from_email', 'bookings@bluemotors.co.uk') . '>'
        );
        
        // Send email
        $sent = wp_mail($to, $subject, $email_content, $headers);
        
        // Log result
        if ($sent) {
            error_log("BMS: Booking confirmation sent to {$customer_email} for booking {$booking_reference}");
        } else {
            error_log("BMS: Failed to send booking confirmation to {$customer_email} for booking {$booking_reference}");
        }
        
        return $sent;
    }
    
    /**
     * Send booking notification to admin
     *
     * @param array $booking_data Booking information
     * @return bool Whether email was sent successfully
     */
    public static function send_admin_notification($booking_data) {
        // Extract booking data
        extract($booking_data);
        
        // Load email template
        ob_start();
        include BMS_PLUGIN_DIR . 'templates/emails/admin-notification.php';
        $email_content = ob_get_clean();
        
        // Email details
        $admin_email = get_option('bms_admin_email', get_option('admin_email'));
        $subject = 'New Booking - ' . $booking_reference . ' - ' . $service_name;
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Blue Motors Southampton <' . get_option('bms_smtp_from_email', 'bookings@bluemotors.co.uk') . '>',
            'Reply-To: ' . $customer_email
        );
        
        // Send email
        $sent = wp_mail($admin_email, $subject, $email_content, $headers);
        
        // Log result
        if ($sent) {
            error_log("BMS: Admin notification sent to {$admin_email} for booking {$booking_reference}");
        } else {
            error_log("BMS: Failed to send admin notification to {$admin_email} for booking {$booking_reference}");
        }
        
        return $sent;
    }
    
    /**
     * Send booking emails (both customer and admin)
     *
     * @param array $booking_data Booking information
     * @return array Result of both email attempts
     */
    public static function send_booking_emails($booking_data) {
        $customer_sent = self::send_booking_confirmation($booking_data);
        $admin_sent = self::send_admin_notification($booking_data);
        
        return array(
            'customer_email_sent' => $customer_sent,
            'admin_email_sent' => $admin_sent,
            'success' => $customer_sent && $admin_sent
        );
    }
    
    /**
     * Validate booking data for email sending
     *
     * @param array $booking_data Booking information
     * @return bool Whether data is valid
     */
    public static function validate_booking_data($booking_data) {
        $required_fields = array(
            'customer_name',
            'customer_email',
            'customer_phone',
            'booking_reference',
            'service_name',
            'booking_date',
            'booking_time',
            'vehicle_details',
            'amount_paid'
        );
        
        foreach ($required_fields as $field) {
            if (empty($booking_data[$field])) {
                error_log("BMS: Missing required field '{$field}' in booking data");
                return false;
            }
        }
        
        // Validate email format
        if (!is_email($booking_data['customer_email'])) {
            error_log("BMS: Invalid customer email format: " . $booking_data['customer_email']);
            return false;
        }
        
        return true;
    }
    
    /**
     * Send reminder email (for future use)
     *
     * @param array $booking_data Booking information
     * @return bool Whether email was sent successfully
     */
    public static function send_booking_reminder($booking_data) {
        // Extract booking data
        extract($booking_data);
        
        // Check if reminder template exists
        $reminder_template = BMS_PLUGIN_DIR . 'templates/emails/booking-reminder.php';
        if (!file_exists($reminder_template)) {
            error_log("BMS: Booking reminder template not found");
            return false;
        }
        
        // Load email template
        ob_start();
        include $reminder_template;
        $email_content = ob_get_clean();
        
        // Email details
        $to = $customer_email;
        $subject = 'Service Reminder - Tomorrow at ' . $booking_time . ' - ' . $booking_reference;
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Blue Motors Southampton <' . get_option('bms_smtp_from_email', 'bookings@bluemotors.co.uk') . '>'
        );
        
        // Send email
        $sent = wp_mail($to, $subject, $email_content, $headers);
        
        // Log result
        if ($sent) {
            error_log("BMS: Booking reminder sent to {$customer_email} for booking {$booking_reference}");
        } else {
            error_log("BMS: Failed to send booking reminder to {$customer_email} for booking {$booking_reference}");
        }
        
        return $sent;
    }
}
