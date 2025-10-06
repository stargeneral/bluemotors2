<?php
/**
 * Main Plugin Class - Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

class Blue_Motors_Southampton {
    
    /**
     * Plugin version
     * @var string
     */
    protected $version;
    
    /**
     * Service manager instance
     * @var object
     */
    protected $service_manager;
    
    /**
     * Vehicle lookup instance
     * @var object
     */
    protected $vehicle_lookup;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->version = BMS_VERSION;
        $this->load_dependencies();
        $this->define_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Service classes are already loaded in main file
        $this->service_manager = new \BlueMotosSouthampton\Services\ServiceManagerEnhanced();
        $this->vehicle_lookup = new \BlueMotosSouthampton\Services\VehicleLookupEnhanced();
        
        // Load AJAX handlers if needed
        if (file_exists(BMS_PLUGIN_DIR . 'includes/ajax/class-ajax-handlers.php')) {
            require_once BMS_PLUGIN_DIR . 'includes/ajax/class-ajax-handlers.php';
        }
    }
    
    /**
     * Define all hooks and filters
     */
    private function define_hooks() {
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        add_shortcode('bms_booking_form', array($this, 'render_booking_form'));
        add_shortcode('bms_location_info', array($this, 'render_location_info'));
        
        // Admin hooks
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX hooks
        add_action('wp_ajax_bms_vehicle_lookup', array($this, 'ajax_vehicle_lookup'));
        add_action('wp_ajax_nopriv_bms_vehicle_lookup', array($this, 'ajax_vehicle_lookup'));
        add_action('wp_ajax_bms_get_available_slots', array($this, 'ajax_get_available_slots'));
        add_action('wp_ajax_nopriv_bms_get_available_slots', array($this, 'ajax_get_available_slots'));
        add_action('wp_ajax_bms_create_booking', array($this, 'ajax_create_booking'));
        add_action('wp_ajax_nopriv_bms_create_booking', array($this, 'ajax_create_booking'));
        add_action('wp_ajax_bms_create_payment_intent', array($this, 'ajax_create_payment_intent'));
        add_action('wp_ajax_nopriv_bms_create_payment_intent', array($this, 'ajax_create_payment_intent'));
    }
    
    /**
     * Run the plugin
     */
    public function run() {
        // Plugin is initialized through hooks
    }
    
    /**
     * Enqueue public scripts and styles
     */
    public function enqueue_public_scripts() {
        wp_enqueue_style(
            'bms-public-style',
            BMS_PLUGIN_URL . 'assets/css/public.css',
            array(),
            BMS_VERSION
        );
        
        wp_enqueue_script(
            'bms-public-script',
            BMS_PLUGIN_URL . 'assets/js/booking.js',
            array('jquery'),
            BMS_VERSION,
            true
        );
        
        // Enqueue Stripe.js library
        if (BM_STRIPE_ENABLED) {
            wp_enqueue_script(
                'stripe-js',
                'https://js.stripe.com/v3/',
                array(),
                null,
                true
            );
        }
        
        wp_localize_script('bms-public-script', 'bms_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bms_ajax_nonce'),
            'stripe_publishable_key' => BM_STRIPE_ENABLED ? BM_STRIPE_PUBLISHABLE_KEY : '',
            'currency' => BM_PAYMENT_CURRENCY
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_style(
            'bms-admin-style',
            BMS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            BMS_VERSION
        );
        
        wp_enqueue_script(
            'bms-admin-script',
            BMS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            BMS_VERSION,
            true
        );
    }
    
    /**
     * Render booking form shortcode
     */
    public function render_booking_form($atts) {
        // Check if template file exists, otherwise use fallback
        $template_file = BMS_PLUGIN_DIR . 'public/templates/booking-form.php';
        if (file_exists($template_file)) {
            ob_start();
            require_once $template_file;
            return ob_get_clean();
        }
        
        // Fallback: Use the fallback handler if available
        if (function_exists('bms_booking_form_shortcode_fallback')) {
            return bms_booking_form_shortcode_fallback($atts);
        }
        
        return '<p class="bms-error">Booking form template not available. Please check plugin installation.</p>';
    }
    
    /**
     * Render location info shortcode
     */
    public function render_location_info($atts) {
        // Check if template file exists, otherwise use fallback
        $template_file = BMS_PLUGIN_DIR . 'public/templates/location-info.php';
        if (file_exists($template_file)) {
            ob_start();
            require_once $template_file;
            return ob_get_clean();
        }
        
        // Fallback: Use our fallback handler instead
        if (function_exists('bms_location_info_shortcode_handler')) {
            return bms_location_info_shortcode_handler($atts);
        }
        
        return '<p class="bms-error">Location info template not available. Please check plugin installation.</p>';
    }
    
    /**
     * AJAX handler for vehicle lookup
     */
    public function ajax_vehicle_lookup() {
        check_ajax_referer('bms_ajax_nonce', 'nonce');
        
        $registration = sanitize_text_field($_POST['registration'] ?? '');
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        
        if (empty($registration)) {
            wp_send_json_error('Missing required parameters');
        }
        
        // Use the enhanced vehicle lookup system
        try {
            // Load the enhanced combined lookup service
            require_once BMS_PLUGIN_DIR . 'includes/services/class-vehicle-lookup-combined.php';
            $enhanced_lookup = new \BlueMotosSouthampton\Services\VehicleLookupCombined();
            
            // Get comprehensive vehicle data
            $vehicle_data = $enhanced_lookup->lookup_vehicle_comprehensive($registration);
            
            if (is_wp_error($vehicle_data)) {
                wp_send_json_error($vehicle_data->get_error_message());
            }
            
            // Get engine size and fuel type for pricing calculations
            $engine_size = $vehicle_data['engine_capacity'] ?? 1600;
            $fuel_type = $vehicle_data['fuel_type_normalized'] ?? 'petrol';
            
            // Get saved service selection to determine if it's a combo
            $saved_selection = BMS_Session::get('service_selection');
            $is_combo = false;
            $selected_service = $service_type;
            $calculated_price = 0;
            
            if ($saved_selection && !empty($saved_selection['service'])) {
                $selected_service = $saved_selection['service'];
                $is_combo = $saved_selection['motIncluded'] ?? false;
                
                if ($is_combo) {
                    // Calculate combo price (service + MOT with discount)
                    $service_price = $this->service_manager->calculate_price($selected_service, $engine_size, $fuel_type);
                    $mot_price = $this->service_manager->calculate_price('mot_test', $engine_size, $fuel_type);
                    
                    // Apply combo discounts
                    $discount = 0;
                    if ($selected_service === 'interim_service') {
                        $discount = 10.00;
                    } elseif ($selected_service === 'full_service') {
                        $discount = 15.00;
                    }
                    
                    $calculated_price = $service_price + $mot_price - $discount;
                } else {
                    // Calculate individual service price
                    $calculated_price = $this->service_manager->calculate_price($selected_service, $engine_size, $fuel_type);
                }
            } else {
                // Fallback: calculate price for the service type if provided
                if (!empty($service_type)) {
                    $calculated_price = $this->service_manager->calculate_price($service_type, $engine_size, $fuel_type);
                }
            }
            
            // Prepare response in the format expected by booking form
            $response_data = [
                'make' => $vehicle_data['make'] ?? 'Unknown',
                'model' => $vehicle_data['model'] ?? 'Unknown',
                'year' => $vehicle_data['year_of_manufacture'] ?? 'Unknown',
                'engineCapacity' => $engine_size,
                'fuelType' => $vehicle_data['fuel_type'] ?? 'Unknown',
                'fuelTypeNormalized' => $fuel_type,
                'colour' => $vehicle_data['colour'] ?? 'Unknown',
                'calculated_price' => $calculated_price,
                'formatted_price' => '£' . number_format($calculated_price, 2),
                'service_type' => $selected_service,
                'is_combo' => $is_combo,
                'registration' => $vehicle_data['registration'] ?? $registration,
                'registrationNumber' => $vehicle_data['registration'] ?? $registration,
                'using_mock_data' => $vehicle_data['using_mock_data'] ?? false,
                'data_sources' => $vehicle_data['data_sources'] ?? [],
                'mot_status' => $vehicle_data['current_mot_status'] ?? 'Unknown',
                'mot_expiry' => $vehicle_data['mot_expiry_date'] ?? null,
                'maintenance_score' => $vehicle_data['maintenance_score'] ?? null,
                'service_recommendations' => $vehicle_data['service_recommendations'] ?? [],
            ];
            
            wp_send_json_success($response_data);
            
        } catch (Exception $e) {
            // Fallback to mock data on any error
            error_log('[Blue Motors Booking] Enhanced lookup failed: ' . $e->getMessage());
            
            $vehicle_data = $this->vehicle_lookup->get_mock_vehicle($registration);
            
            // Get saved service selection for fallback pricing
            $saved_selection = BMS_Session::get('service_selection');
            $is_combo = false;
            $selected_service = $service_type ?: 'interim_service';
            $calculated_price = 0;
            
            if ($saved_selection && !empty($saved_selection['service'])) {
                $selected_service = $saved_selection['service'];
                $is_combo = $saved_selection['motIncluded'] ?? false;
                
                if ($is_combo) {
                    // Use saved combo price or calculate fallback
                    $calculated_price = $saved_selection['totalPrice'] ?? 100.00;
                } else {
                    $calculated_price = $saved_selection['price'] ?? $this->service_manager->calculate_price(
                        $selected_service,
                        $vehicle_data['engineCapacity'],
                        $vehicle_data['fuelType']
                    );
                }
            } else {
                $calculated_price = $this->service_manager->calculate_price(
                    $selected_service,
                    $vehicle_data['engineCapacity'],
                    $vehicle_data['fuelType']
                );
            }
            
            $vehicle_data['calculated_price'] = $calculated_price;
            $vehicle_data['formatted_price'] = '£' . number_format($calculated_price, 2);
            $vehicle_data['service_type'] = $selected_service;
            $vehicle_data['is_combo'] = $is_combo;
            $vehicle_data['using_mock_data'] = true;
            $vehicle_data['registrationNumber'] = $vehicle_data['registration'] ?? $registration;
            
            wp_send_json_success($vehicle_data);
        }
    }
    
    /**
     * AJAX handler for getting available time slots
     */
    public function ajax_get_available_slots() {
        check_ajax_referer('bms_ajax_nonce', 'nonce');
        
        $date = sanitize_text_field($_POST['date'] ?? '');
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        
        if (empty($date) || empty($service_type)) {
            wp_send_json_error('Missing required parameters');
        }
        
        $available_slots = $this->service_manager->get_available_slots($date, $service_type);
        
        wp_send_json_success(array(
            'slots' => $available_slots,
            'date' => $date,
            'service' => $service_type
        ));
    }
    
    /**
     * AJAX handler for creating a booking
     */
    public function ajax_create_booking() {
        check_ajax_referer('bms_ajax_nonce', 'nonce');
        
        // Validate and sanitize input
        $booking_data = array(
            'service_type' => sanitize_text_field($_POST['service_type'] ?? ''),
            'booking_date' => sanitize_text_field($_POST['date'] ?? ''),
            'booking_time' => sanitize_text_field($_POST['time'] ?? ''),
            'vehicle_reg' => sanitize_text_field($_POST['vehicle_reg'] ?? ''),
            'vehicle_make' => sanitize_text_field($_POST['vehicle_make'] ?? ''),
            'vehicle_model' => sanitize_text_field($_POST['vehicle_model'] ?? ''),
            'vehicle_engine_size' => intval($_POST['vehicle_engine_size'] ?? 0),
            'vehicle_fuel_type' => sanitize_text_field($_POST['vehicle_fuel_type'] ?? ''),
            'customer_name' => sanitize_text_field($_POST['customer_name'] ?? ''),
            'customer_email' => sanitize_email($_POST['customer_email'] ?? ''),
            'customer_phone' => sanitize_text_field($_POST['customer_phone'] ?? ''),
            'calculated_price' => floatval($_POST['price'] ?? 0),
        );
        
        // Generate booking reference
        $booking_data['booking_reference'] = $this->generate_booking_reference();
        
        // Save to database
        $booking_id = $this->save_booking_to_database($booking_data);
        
        if (!$booking_id) {
            wp_send_json_error(array(
                'message' => 'Failed to save booking. Please try again.'
            ));
            return;
        }
        
        // Process payment
        $payment_result = $this->process_payment($booking_data);
        
        // Update booking with payment information
        if ($payment_result['status'] === 'paid' && isset($payment_result['payment_reference'])) {
            $this->update_booking_payment_status($booking_id, 'paid', $payment_result['payment_reference']);
        }
        
        // Send confirmation email
        $email_sent = $this->send_confirmation_email($booking_data, $booking_id);
        
        // Log the booking creation
        $this->log_booking_action($booking_id, 'created', 'Booking created via website');
        
        wp_send_json_success(array(
            'booking_reference' => $booking_data['booking_reference'],
            'booking_id' => $booking_id,
            'payment_status' => $payment_result['status'],
            'email_sent' => $email_sent,
            'message' => 'Booking created successfully'
        ));
    }
    
    /**
     * AJAX handler for creating Stripe payment intent
     */
    public function ajax_create_payment_intent() {
        check_ajax_referer('bms_ajax_nonce', 'nonce');
        
        if (!BM_STRIPE_ENABLED || !BM_STRIPE_SECRET_KEY) {
            wp_send_json_error(array(
                'message' => 'Payment processing not configured'
            ));
            return;
        }
        
        $amount = intval($_POST['amount'] ?? 0);
        $currency = sanitize_text_field($_POST['currency'] ?? 'gbp');
        
        if ($amount < 50) { // Minimum 50p
            wp_send_json_error(array(
                'message' => 'Invalid payment amount'
            ));
            return;
        }
        
        try {
            // Set up Stripe
            \Stripe\Stripe::setApiKey(BM_STRIPE_SECRET_KEY);
            
            // Create payment intent
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => [
                    'source' => 'blue_motors_southampton',
                    'booking_system' => 'true'
                ],
            ]);
            
            wp_send_json_success(array(
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id
            ));
            
        } catch (\Stripe\Exception\CardException $e) {
            wp_send_json_error(array(
                'message' => 'Payment processing error: ' . $e->getError()->message
            ));
        } catch (\Exception $e) {
            wp_send_json_error(array(
                'message' => 'Payment setup failed. Please try again.'
            ));
        }
    }
    
    /**
     * Generate unique booking reference
     */
    private function generate_booking_reference() {
        return BM_BOOKING_REFERENCE_PREFIX . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }
    
    /**
     * Save booking to database
     */
    private function save_booking_to_database($booking_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'booking_reference' => $booking_data['booking_reference'],
                'service_type' => $booking_data['service_type'],
                'booking_date' => $booking_data['booking_date'],
                'booking_time' => $booking_data['booking_time'],
                'vehicle_reg' => $booking_data['vehicle_reg'],
                'vehicle_make' => $booking_data['vehicle_make'],
                'vehicle_model' => $booking_data['vehicle_model'],
                'vehicle_engine_size' => $booking_data['vehicle_engine_size'],
                'vehicle_fuel_type' => $booking_data['vehicle_fuel_type'],
                'customer_name' => $booking_data['customer_name'],
                'customer_email' => $booking_data['customer_email'],
                'customer_phone' => $booking_data['customer_phone'],
                'calculated_price' => $booking_data['calculated_price'],
                'payment_status' => 'pending',
                'booking_status' => 'confirmed'
            ),
            array(
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', 
                '%s', '%s', '%s', '%f', '%s', '%s'
            ),
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Update booking payment status
     */
    private function update_booking_payment_status($booking_id, $payment_status, $payment_reference = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_appointments';
        
        $update_data = array(
            'payment_status' => $payment_status,
        );
        
        if (!empty($payment_reference)) {
            $update_data['payment_reference'] = $payment_reference;
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $booking_id),
            array('%s', '%s'),
            array('%d'),
        );
        
        return $result !== false;
    }
    
    /**
     * Process payment with Stripe integration
     */
    private function process_payment($booking_data) {
        // Check if payment intent ID is provided (from Stripe payment)
        if (isset($booking_data['payment_intent_id']) && !empty($booking_data['payment_intent_id'])) {
            return $this->verify_stripe_payment($booking_data['payment_intent_id']);
        }
        
        // If no payment intent, mark as pending (for testing or manual payment)
        return array(
            'status' => 'pending',
            'message' => 'Payment verification pending'
        );
    }
    
    /**
     * Verify Stripe payment
     */
    private function verify_stripe_payment($payment_intent_id) {
        if (!BM_STRIPE_ENABLED || !BM_STRIPE_SECRET_KEY) {
            return array(
                'status' => 'failed',
                'message' => 'Payment processing not configured'
            );
        }
        
        try {
            // Set up Stripe
            \Stripe\Stripe::setApiKey(BM_STRIPE_SECRET_KEY);
            
            // Retrieve payment intent
            $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
            
            if ($intent->status === 'succeeded') {
                return array(
                    'status' => 'paid',
                    'payment_reference' => $payment_intent_id,
                    'amount' => $intent->amount_received / 100, // Convert from pence
                    'message' => 'Payment successful'
                );
            } else {
                return array(
                    'status' => 'failed',
                    'message' => 'Payment not completed: ' . $intent->status
                );
            }
            
        } catch (\Exception $e) {
            return array(
                'status' => 'failed',
                'message' => 'Payment verification failed: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Send confirmation email
     */
    private function send_confirmation_email($booking_data, $booking_id) {
        $to = $booking_data['customer_email'];
        $subject = 'Booking Confirmation - Blue Motors Southampton';
        
        $message = $this->get_email_template($booking_data, $booking_id);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Blue Motors Southampton <' . BM_LOCATION_EMAIL . '>'
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get email template for confirmation
     */
    private function get_email_template($booking_data, $booking_id) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .booking-details { background-color: #f8f9fa; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Blue Motors Southampton</h1>
                    <h2>Booking Confirmation</h2>
                </div>
                
                <div class="content">
                    <p>Dear <?php echo esc_html($booking_data['customer_name']); ?>,</p>
                    
                    <p>Thank you for booking your service with Blue Motors Southampton. Your booking has been confirmed.</p>
                    
                    <div class="booking-details">
                        <h3>Booking Details</h3>
                        <p><strong>Reference:</strong> <?php echo esc_html($booking_data['booking_reference']); ?></p>
                        <p><strong>Service:</strong> <?php echo esc_html(ucfirst(str_replace('_', ' ', $booking_data['service_type']))); ?></p>
                        <p><strong>Date:</strong> <?php echo esc_html(date('l, F j, Y', strtotime($booking_data['booking_date']))); ?></p>
                        <p><strong>Time:</strong> <?php echo esc_html(date('g:i A', strtotime($booking_data['booking_time']))); ?></p>
                        <p><strong>Vehicle:</strong> <?php echo esc_html($booking_data['vehicle_make'] . ' ' . $booking_data['vehicle_model'] . ' (' . $booking_data['vehicle_reg'] . ')'); ?></p>
                        <p><strong>Price:</strong> £<?php echo esc_html(number_format($booking_data['calculated_price'], 2)); ?></p>
                    </div>
                    
                    <div class="booking-details">
                        <h3>Our Location</h3>
                        <p><strong>Blue Motors Southampton</strong><br>
                        <?php echo esc_html(BM_LOCATION_ADDRESS); ?><br>
                        Tel: <?php echo esc_html(BM_LOCATION_PHONE); ?></p>
                        
                        <p><a href="https://maps.google.com/?q=<?php echo urlencode(BM_LOCATION_ADDRESS); ?>">Get Directions</a></p>
                    </div>
                    
                    <p><strong>Important:</strong> Please arrive 10 minutes before your appointment time.</p>
                    
                    <p>If you need to reschedule or cancel, please call us on <?php echo esc_html(BM_LOCATION_PHONE); ?>.</p>
                    
                    <p>Best regards,<br>The Blue Motors Team</p>
                </div>
                
                <div class="footer">
                    <p>Blue Motors Southampton | <?php echo esc_html(BM_LOCATION_ADDRESS); ?> | <?php echo esc_html(BM_LOCATION_PHONE); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Log booking action
     */
    private function log_booking_action($booking_id, $action, $details = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bms_booking_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'booking_id' => $booking_id,
                'action' => $action,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_id' => get_current_user_id()
            ),
            array('%d', '%s', '%s', '%s', '%d')
        );
    }
}
