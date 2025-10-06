<?php
/**
 * Example: How to integrate email notifications with booking completion
 * 
 * @package BlueMotosSouthampton
 */

/**
 * Example function showing how to send booking emails
 * This would typically be called after a successful booking and payment
 */
function bms_example_booking_completion($booking_data) {
    
    // Validate the booking data first
    if (!BMS_Email_Manager::validate_booking_data($booking_data)) {
        return array(
            'success' => false,
            'message' => 'Invalid booking data - emails not sent'
        );
    }
    
    // Send both customer confirmation and admin notification emails
    $email_results = BMS_Email_Manager::send_booking_emails($booking_data);
    
    if ($email_results['success']) {
        return array(
            'success' => true,
            'message' => 'Booking confirmed and emails sent successfully!',
            'customer_email_sent' => $email_results['customer_email_sent'],
            'admin_email_sent' => $email_results['admin_email_sent']
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Booking saved but there was an issue sending emails',
            'customer_email_sent' => $email_results['customer_email_sent'],
            'admin_email_sent' => $email_results['admin_email_sent']
        );
    }
}

/**
 * Example booking data structure
 * This is what should be passed to the email functions
 */
function bms_get_example_booking_data() {
    return array(
        // Customer Information
        'customer_name' => 'John Smith',
        'customer_email' => 'john.smith@example.com',
        'customer_phone' => '07123 456789',
        
        // Booking Information
        'booking_reference' => 'WEB-' . sprintf('%06d', wp_rand(100000, 999999)),
        'service_name' => 'MOT Test',
        'booking_date' => date('l, jS F Y', strtotime('+3 days')),
        'booking_time' => '10:00 AM',
        
        // Vehicle Information
        'vehicle_details' => '2018 Ford Focus 1.0L Petrol',
        'vehicle_registration' => 'AB18 XYZ',
        
        // Payment Information
        'amount_paid' => '40.00',
        
        // Optional Information
        'special_notes' => 'Customer mentioned slight brake noise - please check during MOT'
    );
}

/**
 * Test function to send a sample booking email
 * This can be used for testing the email system
 * 
 * Usage: Add ?bms_test_email=1 to any admin page URL to trigger test
 */
function bms_test_email_system() {
    if (isset($_GET['bms_test_email']) && current_user_can('manage_options')) {
        
        // Get example booking data
        $booking_data = bms_get_example_booking_data();
        
        // Try to send emails
        $result = bms_example_booking_completion($booking_data);
        
        // Display result
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>';
            echo '<strong>Email Test Successful!</strong><br>';
            echo 'Customer email sent: ' . ($result['customer_email_sent'] ? 'Yes' : 'No') . '<br>';
            echo 'Admin email sent: ' . ($result['admin_email_sent'] ? 'Yes' : 'No');
            echo '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>Email Test Failed:</strong> ' . $result['message'];
            echo '</p></div>';
        }
    }
}
add_action('admin_notices', 'bms_test_email_system');

/**
 * AJAX handler example for booking completion
 * This shows how to integrate emails into an AJAX booking process
 */
function bms_ajax_complete_booking() {
    // Verify nonce
    check_ajax_referer('bms_booking_nonce', 'nonce');
    
    // Get booking data from POST
    $booking_data = array(
        'customer_name' => sanitize_text_field($_POST['customer_name'] ?? ''),
        'customer_email' => sanitize_email($_POST['customer_email'] ?? ''),
        'customer_phone' => sanitize_text_field($_POST['customer_phone'] ?? ''),
        'booking_reference' => sanitize_text_field($_POST['booking_reference'] ?? ''),
        'service_name' => sanitize_text_field($_POST['service_name'] ?? ''),
        'booking_date' => sanitize_text_field($_POST['booking_date'] ?? ''),
        'booking_time' => sanitize_text_field($_POST['booking_time'] ?? ''),
        'vehicle_details' => sanitize_text_field($_POST['vehicle_details'] ?? ''),
        'vehicle_registration' => sanitize_text_field($_POST['vehicle_registration'] ?? ''),
        'amount_paid' => sanitize_text_field($_POST['amount_paid'] ?? '0.00'),
        'special_notes' => sanitize_textarea_field($_POST['special_notes'] ?? '')
    );
    
    // Save booking to database (implement as needed)
    // $booking_id = bms_save_booking_to_database($booking_data);
    
    // Send confirmation emails
    $email_result = BMS_Email_Manager::send_booking_emails($booking_data);
    
    if ($email_result['success']) {
        wp_send_json_success(array(
            'message' => 'Booking confirmed and confirmation emails sent!',
            'booking_reference' => $booking_data['booking_reference'],
            'emails_sent' => true
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Booking saved but there was an issue sending emails',
            'booking_reference' => $booking_data['booking_reference'],
            'emails_sent' => false
        ));
    }
}
add_action('wp_ajax_bms_complete_booking', 'bms_ajax_complete_booking');
add_action('wp_ajax_nopriv_bms_complete_booking', 'bms_ajax_complete_booking');
