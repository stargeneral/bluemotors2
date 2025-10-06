/**
 * Enhanced Payment Processing - Fix F1's Payment Friction Issues
 * Blue Motors Southampton - Professional Auto Services
 */

class EnhancedPaymentProcessor {
    constructor() {
        this.stripe = null;
        this.elements = null;
        this.paymentElement = null;
        this.isProcessing = false;
        this.init();
    }
    
    async init() {
        if (!bmsPayment.stripePublishableKey) {
            console.error('Stripe not configured');
            return;
        }
        
        this.stripe = Stripe(bmsPayment.stripePublishableKey, {
            locale: 'en-GB' // UK locale for better UX
        });
        
        this.setupPaymentElement();
        this.setupPaymentMethods();
    }
    
    setupPaymentElement() {
        const appearance = {
            theme: 'stripe',
            variables: {
                colorPrimary: '#1e3a8a',
                colorBackground: '#ffffff',
                colorText: '#374151',
                colorDanger: '#dc2626',
                fontFamily: '"Inter", system-ui, sans-serif',
                spacingUnit: '4px',
                borderRadius: '8px'
            },
            rules: {
                '.Input': {
                    border: '2px solid #e5e7eb',
                    boxShadow: 'none'
                },
                '.Input:focus': {
                    border: '2px solid #1e3a8a',
                    boxShadow: '0 0 0 3px rgba(30, 58, 138, 0.1)'
                },
                '.Label': {
                    fontWeight: '600',
                    color: '#374151'
                }
            }
        };
        
        this.elements = this.stripe.elements({
            appearance,
            locale: 'en-GB'
        });
        
        // Create payment element with UK-friendly options
        this.paymentElement = this.elements.create('payment', {
            layout: {
                type: 'tabs',
                defaultCollapsed: false
            },
            paymentMethodOrder: ['card', 'apple_pay', 'google_pay', 'link'],
            fields: {
                billingDetails: {
                    name: 'auto',
                    email: 'auto',
                    phone: 'auto',
                    address: {
                        country: 'never',
                        line1: 'auto',
                        line2: 'auto',
                        city: 'auto',
                        state: 'never',
                        postalCode: 'auto'
                    }
                }
            }
        });
        
        const paymentContainer = document.getElementById('payment-element');
        if (paymentContainer) {
            this.paymentElement.mount('#payment-element');
        }
        
        // Handle payment element events
        this.paymentElement.on('ready', () => {
            this.showPaymentReadyMessage();
        });
        
        this.paymentElement.on('change', (event) => {
            this.handlePaymentChange(event);
        });
    }
    
    setupPaymentMethods() {
        // Add multiple payment method buttons
        const paymentMethodsContainer = document.getElementById('payment-methods-container');
        if (!paymentMethodsContainer) return;
        
        const paymentMethods = `
            <div class="payment-methods-grid">
                <div class="payment-method-info">
                    <h4>💳 Secure Payment Options</h4>
                    <ul class="payment-features">
                        <li>✓ All major credit and debit cards</li>
                        <li>✓ Apple Pay & Google Pay</li>
                        <li>✓ Secure encryption</li>
                        <li>✓ No hidden fees</li>
                    </ul>
                </div>
                
                <div class="security-badges">
                    <div class="security-badge">
                        <span class="badge-icon">🔒</span>
                        <span>SSL Encrypted</span>
                    </div>
                    <div class="security-badge">
                        <span class="badge-icon">🛡️</span>
                        <span>PCI Compliant</span>
                    </div>
                </div>
            </div>
            
            <div class="payment-advantages">
                <h5>🎯 Better than industry leaders Payment:</h5>
                <ul>
                    <li>✅ No PayPal integration issues</li>
                    <li>✅ UK-optimized checkout</li>
                    <li>✅ Multiple payment options</li>
                    <li>✅ Instant confirmation</li>
                </ul>
            </div>
        `;
        
        paymentMethodsContainer.innerHTML = paymentMethods;
    }
    
    async processPayment(bookingData) {
        if (this.isProcessing) {
            throw new Error('Payment already in progress');
        }
        
        this.isProcessing = true;
        this.updatePaymentButton('Processing...', true);
        
        try {
            // Create payment intent
            const paymentIntentResponse = await this.createPaymentIntent(bookingData);
            
            if (!paymentIntentResponse.success) {
                throw new Error(paymentIntentResponse.data.message || 'Failed to initialize payment');
            }
            
            const clientSecret = paymentIntentResponse.data.client_secret;
            
            // Confirm payment with enhanced error handling
            const {error, paymentIntent} = await this.stripe.confirmPayment({
                elements: this.elements,
                confirmParams: {
                    return_url: window.location.href,
                    payment_method_data: {
                        billing_details: {
                            name: bookingData.customer_name,
                            email: bookingData.customer_email,
                            phone: bookingData.customer_phone
                        }
                    }
                },
                redirect: 'if_required'
            });
            
            if (error) {
                throw new Error(this.getFriendlyErrorMessage(error));
            }
            
            if (paymentIntent.status === 'succeeded') {
                this.updatePaymentButton('Payment Successful!', true);
                
                // Show competitive success message
                this.showProfessionalSuccessMessage();
                
                return {
                    success: true,
                    payment_intent_id: paymentIntent.id,
                    amount: paymentIntent.amount_received / 100,
                    last_four: paymentIntent.charges.data[0]?.payment_method_details?.card?.last4,
                    brand: paymentIntent.charges.data[0]?.payment_method_details?.card?.brand
                };
            } else {
                throw new Error('Payment was not completed successfully');
            }
            
        } catch (error) {
            this.updatePaymentButton('Try Again', false);
            throw error;
        } finally {
            this.isProcessing = false;
        }
    }
    
    async createPaymentIntent(bookingData) {
        const response = await fetch(bmsPayment.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'bms_create_payment_intent',
                nonce: bmsPayment.nonce,
                amount: Math.round(bookingData.total_amount * 100), // Convert to pence
                currency: 'gbp',
                customer_email: bookingData.customer_email,
                booking_reference: bookingData.booking_reference || '',
                service_type: bookingData.service_type || ''
            })
        });
        
        return await response.json();
    }
    
    getFriendlyErrorMessage(error) {
        // Convert Stripe errors to user-friendly messages
        const errorMessages = {
            'card_declined': 'Your card was declined. Please try a different payment method or contact your bank.',
            'insufficient_funds': 'Insufficient funds. Please check your account balance or try a different card.',
            'expired_card': 'Your card has expired. Please use a different card.',
            'incorrect_cvc': 'The security code is incorrect. Please check and try again.',
            'processing_error': 'A processing error occurred. Please try again in a moment.',
            'rate_limit': 'Too many payment attempts. Please wait a moment and try again.',
            'api_connection_error': 'Network connection error. Please check your internet connection.',
            'api_error': 'Payment system error. Please try again or contact support.',
            'authentication_error': 'Payment authentication failed. Please try again.',
            'invalid_request_error': 'Invalid payment request. Please refresh the page and try again.'
        };
        
        return errorMessages[error.code] || error.message || 'Payment failed. Please try again.';
    }
    
    updatePaymentButton(text, disabled) {
        const button = document.getElementById('complete-payment-button');
        if (button) {
            button.textContent = text;
            button.disabled = disabled;
            
            if (disabled) {
                button.classList.add('processing');
            } else {
                button.classList.remove('processing');
            }
        }
    }
    
    showPaymentReadyMessage() {
        const readyMessage = document.createElement('div');
        readyMessage.className = 'payment-ready-message';
        readyMessage.innerHTML = '✓ Secure payment system ready';
        
        const paymentElement = document.getElementById('payment-element');
        if (paymentElement && !document.querySelector('.payment-ready-message')) {
            paymentElement.parentNode.insertBefore(readyMessage, paymentElement.nextSibling);
        }
        
        setTimeout(() => readyMessage.remove(), 3000);
    }
    
    handlePaymentChange(event) {
        const messageContainer = document.getElementById('payment-messages');
        if (!messageContainer) return;
        
        if (event.error) {
            messageContainer.innerHTML = `
                <div class="payment-error">
                    ⚠️ ${event.error.message}
                </div>
            `;
        } else if (event.complete) {
            messageContainer.innerHTML = `
                <div class="payment-complete">
                    ✓ Payment details complete
                </div>
            `;
        } else {
            messageContainer.innerHTML = '';
        }
    }
    
    showProfessionalSuccessMessage() {
        const successPopup = document.createElement('div');
        successPopup.className = 'competitive-payment-success';
        successPopup.innerHTML = `
            <div class="popup-content">
                <h4>🎉 Payment Successful!</h4>
                <p><strong>🎯 Unlike industry leaders:</strong> No PayPal integration issues here!</p>
                <p>Your booking is confirmed with our superior payment system.</p>
                <button onclick="this.parentElement.parentElement.remove()" class="btn-primary">
                    Excellent! 🚀
                </button>
            </div>
        `;
        
        successPopup.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10000;
            background: rgba(0,0,0,0.8);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        document.body.appendChild(successPopup);
        
        setTimeout(() => successPopup.remove(), 5000);
    }
}

// Initialize enhanced payment processor
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('payment-element')) {
        window.enhancedPayment = new EnhancedPaymentProcessor();
    }
});
