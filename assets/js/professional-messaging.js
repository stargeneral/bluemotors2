/**
 * Professional Messaging JavaScript
 * 
 * Handles dynamic professional messaging features
 */

(function() {
    'use strict';
    
    console.log('💼 Professional Messaging System: Loading...');
    
    // Professional messaging functionality
    window.ProfessionalMessaging = {
        
        // Initialize professional messaging features
        init: function() {
            console.log('💼 Professional Messaging: Initializing...');
            
            this.setupDynamicMessages();
            this.setupServiceQualityIndicators();
            this.setupCompetitiveAdvantages();
            
            console.log('✅ Professional Messaging: Initialized successfully');
        },
        
        // Setup dynamic messaging based on user interactions
        setupDynamicMessages: function() {
            // Show professional service messages based on tyre selection
            document.addEventListener('tyreSelected', (event) => {
                this.showTyreSelectionMessage(event.detail);
            });
            
            // Show appointment booking success message
            document.addEventListener('appointmentBooked', (event) => {
                this.showBookingSuccessMessage(event.detail);
            });
        },
        
        // Display tyre selection confirmation message
        showTyreSelectionMessage: function(tyreData) {
            const message = `
                <div class="professional-messaging">
                    <h3>🎉 Excellent Choice!</h3>
                    <p>You've selected premium tyres with our professional online system.</p>
                    <div class="professional-badge premium">Premium Quality</div>
                    <div class="professional-badge">Expert Installation</div>
                    <div class="professional-badge quality">Quality Guaranteed</div>
                </div>
            `;
            
            this.showMessage(message, 'success');
        },
        
        // Display booking success message
        showBookingSuccessMessage: function(bookingData) {
            const message = `
                <div class="professional-messaging">
                    <h3>✅ Booking Confirmed!</h3>
                    <p>Your professional tyre fitting appointment is confirmed.</p>
                    <p><strong>Date:</strong> ${bookingData.date}</p>
                    <p><strong>Time:</strong> ${bookingData.time}</p>
                    <div class="professional-badge">Confirmed</div>
                    <div class="professional-badge premium">Professional Service</div>
                </div>
            `;
            
            this.showMessage(message, 'confirmation');
        },
        
        // Show message with animation
        showMessage: function(htmlContent, type = 'info') {
            const messageContainer = document.createElement('div');
            messageContainer.innerHTML = htmlContent;
            messageContainer.className = `professional-message-popup ${type}`;
            messageContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
                opacity: 0;
                transform: translateY(-20px);
                transition: all 0.3s ease;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            `;
            
            document.body.appendChild(messageContainer);
            
            // Animate in
            setTimeout(() => {
                messageContainer.style.opacity = '1';
                messageContainer.style.transform = 'translateY(0)';
            }, 100);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                this.hideMessage(messageContainer);
            }, 5000);
            
            // Click to dismiss
            messageContainer.addEventListener('click', () => {
                this.hideMessage(messageContainer);
            });
        },
        
        // Hide message with animation
        hideMessage: function(messageElement) {
            messageElement.style.opacity = '0';
            messageElement.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                if (messageElement.parentNode) {
                    messageElement.parentNode.removeChild(messageElement);
                }
            }, 300);
        },
        
        // Setup service quality indicators
        setupServiceQualityIndicators: function() {
            const qualityIndicators = document.querySelectorAll('.service-quality');
            
            qualityIndicators.forEach(indicator => {
                this.animateQualityNumbers(indicator);
            });
        },
        
        // Animate quality numbers when they come into view
        animateQualityNumbers: function(container) {
            const numbers = container.querySelectorAll('.number');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.countUpAnimation(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            numbers.forEach(number => {
                observer.observe(number);
            });
        },
        
        // Count up animation for numbers
        countUpAnimation: function(element) {
            const finalValue = parseInt(element.textContent) || 0;
            const duration = 1500;
            const increment = finalValue / (duration / 16);
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= finalValue) {
                    element.textContent = finalValue;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 16);
        },
        
        // Setup competitive advantages
        setupCompetitiveAdvantages: function() {
            const advantages = [
                "✅ No phone calls required - instant online ordering",
                "✅ Transparent pricing with no hidden fees",
                "✅ Professional fitting by certified technicians", 
                "✅ Premium quality tyres at competitive prices",
                "✅ Convenient appointment booking system",
                "✅ Expert vehicle-specific recommendations"
            ];
            
            // Add advantages to any competitive advantage containers
            const containers = document.querySelectorAll('.competitive-advantage ul');
            containers.forEach(container => {
                if (container.children.length === 0) {
                    advantages.forEach(advantage => {
                        const li = document.createElement('li');
                        li.textContent = advantage.substring(2); // Remove the ✅
                        container.appendChild(li);
                    });
                }
            });
        },
        
        // Highlight professional service benefits
        highlightServiceBenefits: function() {
            const benefits = document.querySelectorAll('.professional-badge');
            
            benefits.forEach((badge, index) => {
                setTimeout(() => {
                    badge.style.animation = 'pulse 0.6s ease-in-out';
                }, index * 200);
            });
        }
    };
    
    // Auto-initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('💼 Professional Messaging: DOM ready, initializing...');
        ProfessionalMessaging.init();
    });
    
    // Also initialize immediately if DOM is already loaded
    if (document.readyState !== 'loading') {
        console.log('💼 Professional Messaging: DOM already loaded, initializing...');
        ProfessionalMessaging.init();
    }
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .professional-message-popup {
            cursor: pointer;
        }
        
        .professional-message-popup:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2) !important;
        }
    `;
    document.head.appendChild(style);
    
    console.log('✅ Professional Messaging System: Loaded successfully');
    
})();