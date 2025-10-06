<?php
/**
 * Enhanced Payment Processor - Better than industry leaders
 * File: includes/services/class-payment-processor.php
 */

namespace BlueMotosSouthampton\Services;

class PaymentProcessor {
    
    private $stripe_secret_key;
    private $stripe_publishable_key;
    
    public function __construct() {
        $this->stripe_secret_key = defined('BM_STRIPE_SECRET_KEY') ? BM_STRIPE_SECRET_KEY : '';
        $this->stripe_publishable_key = defined('BM_STRIPE_PUBLISHABLE_KEY') ? BM_STRIPE_PUBLISHABLE_KEY : '';
        
        if (!empty($this->stripe_secret_key)) {
            \Stripe\Stripe::setApiKey($this->stripe_secret_key);
        }
    }
    
    /**
     * Create payment intent for booking
     */
    public function create_payment_intent($amount, $currency = 'gbp', $metadata = []) {
        try {
            if (empty($this->stripe_secret_key)) {
                throw new \Exception('Stripe not configured');
            }
            
            $intent = \Stripe\PaymentIntent::create([
                'amount' => round($amount * 100), // Convert to pence
                'currency' => $currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => $metadata,
                'description' => 'Blue Motors Southampton - ' . ($metadata['service_type'] ?? 'Service Booking')
            ]);
            
            return [
                'success' => true,
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id
            ];
            
        } catch (\Exception $e) {
            error_log('Payment intent creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify payment was successful
     */
    public function verify_payment($payment_intent_id) {
        try {
            if (empty($this->stripe_secret_key)) {
                throw new \Exception('Stripe not configured');
            }
            
            $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
            
            if ($intent->status === 'succeeded') {
                return [
                    'success' => true,
                    'status' => 'paid',
                    'amount_received' => $intent->amount_received / 100,
                    'payment_method' => $intent->charges->data[0]->payment_method_details ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'status' => $intent->status,
                    'message' => 'Payment not completed'
                ];
            }
            
        } catch (\Exception $e) {
            error_log('Payment verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle payment webhook
     */
    public function handle_webhook($payload, $sig_header) {
        try {
            $endpoint_secret = defined('BM_STRIPE_WEBHOOK_SECRET') ? BM_STRIPE_WEBHOOK_SECRET : '';
            
            if (empty($endpoint_secret)) {
                throw new \Exception('Webhook secret not configured');
            }
            
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
            
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handle_payment_succeeded($event->data->object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handle_payment_failed($event->data->object);
                    break;
                default:
                    error_log('Unhandled webhook event type: ' . $event->type);
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            error_log('Webhook handling failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Handle successful payment
     */
    private function handle_payment_succeeded($payment_intent) {
        global $wpdb;
        
        $booking_reference = $payment_intent->metadata->booking_reference ?? '';
        
        if (!empty($booking_reference)) {
            // Update booking status
            $wpdb->update(
                $wpdb->prefix . 'bms_appointments',
                [
                    'payment_status' => 'paid',
                    'stripe_payment_id' => $payment_intent->id
                ],
                ['booking_reference' => $booking_reference]
            );
            
            // Send confirmation email
            $this->send_payment_confirmation($booking_reference);
        }
    }
    
    /**
     * Handle failed payment
     */
    private function handle_payment_failed($payment_intent) {
        global $wpdb;
        
        $booking_reference = $payment_intent->metadata->booking_reference ?? '';
        
        if (!empty($booking_reference)) {
            $wpdb->update(
                $wpdb->prefix . 'bms_appointments',
                ['payment_status' => 'failed'],
                ['booking_reference' => $booking_reference]
            );
        }
    }
    
    /**
     * Send payment confirmation email
     */
    private function send_payment_confirmation($booking_reference) {
        // Get booking details
        global $wpdb;
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}bms_appointments WHERE booking_reference = %s",
            $booking_reference
        ));
        
        if ($booking) {
            // Send email using EmailManager
            $email_manager = new EmailManager();
            $email_manager->send_booking_confirmation($booking);
        }
    }
    
    /**
     * Refund payment
     */
    public function refund_payment($payment_intent_id, $amount = null, $reason = 'requested_by_customer') {
        try {
            if (empty($this->stripe_secret_key)) {
                throw new \Exception('Stripe not configured');
            }
            
            $refund_data = [
                'payment_intent' => $payment_intent_id,
                'reason' => $reason
            ];
            
            if ($amount !== null) {
                $refund_data['amount'] = round($amount * 100); // Convert to pence
            }
            
            $refund = \Stripe\Refund::create($refund_data);
            
            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'status' => $refund->status
            ];
            
        } catch (\Exception $e) {
            error_log('Refund failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get payment methods for customer
     */
    public function get_payment_methods($customer_id) {
        try {
            $payment_methods = \Stripe\PaymentMethod::all([
                'customer' => $customer_id,
                'type' => 'card',
            ]);
            
            return [
                'success' => true,
                'payment_methods' => $payment_methods->data
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create or retrieve Stripe customer
     */
    public function create_customer($email, $name = '', $phone = '') {
        try {
            // Check if customer already exists
            $customers = \Stripe\Customer::all([
                'email' => $email,
                'limit' => 1
            ]);
            
            if (!empty($customers->data)) {
                return [
                    'success' => true,
                    'customer' => $customers->data[0],
                    'is_new' => false
                ];
            }
            
            // Create new customer
            $customer = \Stripe\Customer::create([
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
                'metadata' => [
                    'source' => 'Blue Motors Southampton'
                ]
            ]);
            
            return [
                'success' => true,
                'customer' => $customer,
                'is_new' => true
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get Stripe configuration status
     */
    public function get_config_status() {
        return [
            'stripe_configured' => !empty($this->stripe_secret_key) && !empty($this->stripe_publishable_key),
            'webhook_configured' => defined('BM_STRIPE_WEBHOOK_SECRET') && !empty(BM_STRIPE_WEBHOOK_SECRET),
            'using_live_keys' => strpos($this->stripe_publishable_key, 'pk_live_') === 0,
            'publishable_key' => $this->stripe_publishable_key
        ];
    }
    
    /**
     * Format error message for frontend
     */
    public function format_error_message($error_code) {
        $messages = [
            'card_declined' => 'Your card was declined. Please try a different payment method.',
            'insufficient_funds' => 'Insufficient funds. Please check your account balance.',
            'expired_card' => 'Your card has expired. Please use a different card.',
            'incorrect_cvc' => 'The security code is incorrect. Please check and try again.',
            'processing_error' => 'A processing error occurred. Please try again.',
            'rate_limit' => 'Too many payment attempts. Please wait a moment.',
            'generic_decline' => 'Your card was declined. Please contact your bank.'
        ];
        
        return $messages[$error_code] ?? 'Payment failed. Please try again or contact support.';
    }
}
