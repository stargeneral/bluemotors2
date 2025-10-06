/**
 * Blue Motors Southampton - Service Selection JavaScript
 * Compatible with existing jQuery-based booking system
 * Enhanced functionality based on industry leaders model
 * 
 * File: assets/js/service-selection.js
 */

jQuery(document).ready(function($) {
    
    // Service selection state
    let serviceSelection = {
        service: null,
        motIncluded: false,
        price: 0,
        totalPrice: 0,
        motPrice: 40.00
    };
    
    /**
     * Initialize service selection functionality
     */
    function initServiceSelection() {
        bindServiceSelectionEvents();
        initializeAnimations();
        trackUserBehavior();
    }
    
    /**
     * Bind event handlers for service selection
     */
    function bindServiceSelectionEvents() {
        // Service selection buttons
        $('.btn-select-service').on('click', function(e) {
            e.preventDefault();
            selectService($(this));
        });
        
        // MOT combo buttons
        $('.btn-select-combo').on('click', function(e) {
            e.preventDefault();
            selectServiceCombo($(this));
        });
        
        // Continue button
        $(document).on('click', '.btn-continue', function(e) {
            e.preventDefault();
            continueToDateTime();
        });
        
        // More info toggle - use both class and ID for reliability
        $('.btn-more-info, #btn-more-info').on('click', function(e) {
            e.preventDefault();
            toggleServiceComparison();
        });
        
        // Change vehicle button
        $('.btn-change-vehicle').on('click', function(e) {
            e.preventDefault();
            if (typeof moveToStep === 'function') {
                moveToStep(2); // Go back to vehicle details
            }
        });
    }
    
    /**
     * Handle individual service selection
     */
    function selectService(button) {
        const serviceType = button.data('service');
        const servicePrice = parseFloat(button.data('price'));
        
        // Update selection state
        serviceSelection.service = serviceType;
        serviceSelection.motIncluded = false;
        serviceSelection.price = servicePrice;
        serviceSelection.totalPrice = servicePrice;
        
        // Update UI - clear all selections first
        $('.bms-service-card-modern').removeClass('selected');
        $('.pricing-row').removeClass('selected');
        
        // Reset all button texts
        $('.btn-select-service').text('BOOK INTERIM SERVICE').prop('disabled', false);
        $('.btn-select-service[data-service="interim_service"]').text('BOOK INTERIM SERVICE');
        $('.btn-select-service[data-service="full_service"]').text('BOOK FULL SERVICE');
        $('.btn-select-combo').text('BOOK NOW').prop('disabled', false);
        
        // Update selected service
        const serviceCard = button.closest('.bms-service-card-modern');
        serviceCard.addClass('selected');
        button.text('✓ Selected').prop('disabled', true);
        
        // Show success message
        showNotification('Great choice! Click "Continue to Vehicle Details" when ready to proceed.', 'success');
        
        // Store in global booking data if available
        if (typeof bookingData !== 'undefined') {
            bookingData.service = serviceType;
            bookingData.price = servicePrice;
            bookingData.motIncluded = false;
        }
        
        // Store selection data
        storeSelectionData();
        
        // Track selection
        trackServiceSelection(serviceType, servicePrice, false);
        
        // Show continue button (no automatic advancement)
        showContinueButton();
    }
    
    /**
     * Handle service + MOT combo selection
     */
    function selectServiceCombo(button) {
        const serviceType = button.data('service');
        const totalPrice = parseFloat(button.data('price'));
        const motIncluded = button.data('mot') === true || button.data('mot') === 'true';
        
        // Calculate base service price
        const basePrice = totalPrice - (motIncluded ? serviceSelection.motPrice : 0);
        
        // Update selection state
        serviceSelection.service = serviceType;
        serviceSelection.motIncluded = motIncluded;
        serviceSelection.price = basePrice;
        serviceSelection.totalPrice = totalPrice;
        
        // Update UI - clear all selections first
        $('.bms-service-card-modern').removeClass('selected');
        $('.pricing-row').removeClass('selected');
        
        // Reset all button texts
        $('.btn-select-service').text('BOOK INTERIM SERVICE').prop('disabled', false);
        $('.btn-select-service[data-service="interim_service"]').text('BOOK INTERIM SERVICE');
        $('.btn-select-service[data-service="full_service"]').text('BOOK FULL SERVICE');
        $('.btn-select-combo').text('BOOK NOW').prop('disabled', false);
        
        // Update selected combo
        const serviceCard = button.closest('.pricing-row');
        serviceCard.addClass('selected');
        button.text('✓ Selected').prop('disabled', true);
        
        // Show success message
        showNotification('Excellent! Service + MOT combo selected. Click "Continue to Vehicle Details" when ready.', 'success');
        
        // Store in global booking data if available
        if (typeof bookingData !== 'undefined') {
            bookingData.service = serviceType;
            bookingData.price = totalPrice;
            bookingData.motIncluded = motIncluded;
        }
        
        // Store selection data
        storeSelectionData();
        
        // Track selection
        trackServiceSelection(serviceType, totalPrice, motIncluded);
        
        // Show continue button (no automatic advancement)
        showContinueButton();
    }
    
    /**
     * Update service card visual selection
     */
    function updateServiceCardSelection(serviceType) {
        // Remove previous selections
        $('.bms-service-card').removeClass('selected');
        
        // Add selection to current service
        $(`.bms-service-card[data-service="${serviceType}"]`).addClass('selected');
        
        // Update button text
        $('.btn-select-service').text('Select This Service');
        $(`.bms-service-card[data-service="${serviceType}"] .btn-select-service`).text('✓ Selected');
        
        // Add animation
        $(`.bms-service-card[data-service="${serviceType}"]`).addClass('fade-in');
    }
    
    /**
     * Show selection summary
     */
    function showSelectionSummary() {
        const summary = $('#selection-summary');
        const serviceName = getServiceDisplayName(serviceSelection.service);
        
        // Update summary content
        $('#summary-service-name').text(serviceName);
        $('#summary-service-price').text(`£${serviceSelection.price.toFixed(2)}`);
        
        // Update options
        let optionsText = '';
        if (serviceSelection.motIncluded) {
            optionsText += `<div class="option-item">+ MOT Test (£${serviceSelection.motPrice.toFixed(2)})</div>`;
        }
        $('#summary-options').html(optionsText);
        
        // Update total price
        $('#summary-total-price').text(`£${serviceSelection.totalPrice.toFixed(2)}`);
        
        // Show summary with animation
        summary.addClass('slide-up').slideDown(500);
    }
    
    /**
     * Show positive service selection message
     */
    function showProfessionalMessage(serviceType, motIncluded = false) {
        let message = '';
        
        if (serviceType === 'interim_service') {
            message = '🎉 Great choice! Your interim service is booked with our Southampton specialists.';
        } else if (serviceType === 'full_service') {
            message = '⭐ Excellent! Our comprehensive service provides complete peace of mind.';
        }
        
        if (motIncluded) {
            message += ' Plus you\'re saving money with our convenient MOT combo deal!';
        }
        
        if (message) {
            showNotification(message, 'success', 5000);
        }
    }
    
    /**
     * Continue to vehicle details (step 2)
     */
    function continueToDateTime() {
        // Validate selection
        if (!serviceSelection.service) {
            showNotification('Please select a service first', 'error');
            return;
        }
        
        // Store selection data
        storeSelectionData();
        
        // Show transition message
        showNotification('Taking you to vehicle details...', 'info');
        
        // Move to vehicle details step (step 2) after brief delay
        setTimeout(function() {
            if (typeof moveToStep === 'function') {
                moveToStep(2); // Vehicle details step
            } else {
                // Fallback for compatibility
                window.location.hash = '#step-2';
            }
        }, 1000);
        
        // Track conversion
        trackStepCompletion('service_selection');
    }
    
    /**
     * Show continue button after service selection
     */
    function showContinueButton() {
        // Remove any existing continue button
        $('.continue-section').remove();
        
        // Create new continue button with proper styling
        const buttonHtml = `
            <div class="continue-section" style="text-align:center;margin:30px 0;padding:20px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;">
                <button type="button" id="btn-continue-to-vehicle" class="btn-primary" style="padding:15px 30px;font-size:18px;background:#1e3a8a;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                    Continue to Vehicle Details →
                </button>
            </div>
        `;
        
        // Append to service selection container
        $('.bms-service-selection-container').append(buttonHtml);
        
        // Bind click event
        $('#btn-continue-to-vehicle').off('click').on('click', function() {
            continueToDateTime();
        });
        
        // Scroll to button smoothly
        setTimeout(function() {
            $('html, body').animate({
                scrollTop: $('#btn-continue-to-vehicle').offset().top - 100
            }, 800);
        }, 100);
    }
    
    /**
     * Toggle service comparison table
     */
    function toggleServiceComparison() {
        const comparison = $('#service-comparison');
        const button = $('.btn-more-info, #btn-more-info');
        
        if (!comparison.length || !button.length) {
            console.warn('Service comparison elements not found');
            return;
        }
        
        // Check current visibility state
        const isVisible = comparison.is(':visible');
        
        if (isVisible) {
            // Hide the comparison table
            comparison.slideUp(400, function() {
                button.text('MORE INFO');
            });
        } else {
            // Show the comparison table
            comparison.slideDown(400, function() {
                button.text('LESS INFO');
                
                // Smooth scroll to make sure it's visible after animation completes
                setTimeout(() => {
                    $('html, body').animate({
                        scrollTop: comparison.offset().top - 100
                    }, 500);
                }, 100);
            });
        }
        
        // Track interaction
        if (typeof gtag !== 'undefined') {
            gtag('event', 'service_comparison_toggle', {
                event_category: 'engagement',
                event_label: isVisible ? 'closed' : 'opened'
            });
        }
    }
    
    /**
     * Store selection data in session/local storage
     */
    function storeSelectionData() {
        const selectionData = {
            service: serviceSelection.service,
            motIncluded: serviceSelection.motIncluded,
            price: serviceSelection.price,
            totalPrice: serviceSelection.totalPrice,
            timestamp: new Date().toISOString()
        };
        
        // Store in localStorage for persistence
        localStorage.setItem('bms_service_selection', JSON.stringify(selectionData));
        
        // Send to server via AJAX if available
        if (typeof bms_ajax !== 'undefined') {
            $.ajax({
                url: bms_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'bms_store_service_selection',
                    selection: selectionData,
                    nonce: bms_ajax.nonce
                },
                success: function(response) {
                    console.log('Service selection stored successfully');
                },
                error: function() {
                    console.warn('Failed to store service selection on server');
                }
            });
        }
    }
    
    /**
     * Get display name for service type
     */
    function getServiceDisplayName(serviceType) {
        const names = {
            'interim_service': 'Interim Service',
            'full_service': 'Full Service',
            'mot_test': 'MOT Test'
        };
        return names[serviceType] || serviceType;
    }
    
    /**
     * Show notification message
     */
    function showNotification(message, type = 'info', duration = 3000) {
        // Remove existing notifications
        $('.bms-notification').remove();
        
        // Create notification element
        const notification = $(`
            <div class="bms-notification bms-notification-${type}">
                <div class="notification-content">
                    <span class="notification-message">${message}</span>
                    <button class="notification-close">&times;</button>
                </div>
            </div>
        `);
        
        // Add to page
        $('body').append(notification);
        
        // Show with animation
        notification.addClass('fade-in');
        
        // Auto-hide after duration
        setTimeout(() => {
            notification.fadeOut(300, () => notification.remove());
        }, duration);
        
        // Close button handler
        notification.find('.notification-close').on('click', () => {
            notification.fadeOut(300, () => notification.remove());
        });
    }
    
    /**
     * Smooth scroll to summary section
     */
    function scrollToSummary() {
        const summary = $('#selection-summary');
        if (summary.length) {
            $('html, body').animate({
                scrollTop: summary.offset().top - 100
            }, 800, 'easeInOutCubic');
        }
    }
    
    /**
     * Initialize animations and interactions
     */
    function initializeAnimations() {
        // Add hover effects to service cards
        $('.bms-service-card').on('mouseenter', function() {
            $(this).addClass('hover-effect');
        }).on('mouseleave', function() {
            $(this).removeClass('hover-effect');
        });
        
        // Animate elements on scroll
        $(window).on('scroll', function() {
            $('.bms-service-card, .bms-mot-section, .bms-competitive-footer').each(function() {
                const element = $(this);
                const elementTop = element.offset().top;
                const scrollTop = $(window).scrollTop();
                const windowHeight = $(window).height();
                
                if (elementTop < scrollTop + windowHeight - 100) {
                    element.addClass('animate-in');
                }
            });
        });
    }
    
    /**
     * Track user behavior and analytics
     */
    function trackUserBehavior() {
        // Track page view
        if (typeof gtag !== 'undefined') {
            gtag('event', 'page_view', {
                page_title: 'Service Selection',
                page_location: window.location.href
            });
        }
        
        // Track time spent on page
        const startTime = Date.now();
        $(window).on('beforeunload', function() {
            const timeSpent = Math.round((Date.now() - startTime) / 1000);
            if (typeof gtag !== 'undefined') {
                gtag('event', 'timing_complete', {
                    name: 'service_selection_time',
                    value: timeSpent
                });
            }
        });
    }
    
    /**
     * Track service selection for analytics
     */
    function trackServiceSelection(serviceType, price, motIncluded) {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'service_selected', {
                event_category: 'booking_flow',
                event_label: serviceType,
                value: price,
                custom_parameters: {
                    service_type: serviceType,
                    price: price,
                    mot_included: motIncluded,
                    competitive_advantage: 'online_booking'
                }
            });
        }
        
        // Track conversion funnel
        if (typeof fbq !== 'undefined') {
            fbq('track', 'AddToCart', {
                content_name: getServiceDisplayName(serviceType),
                content_category: 'automotive_service',
                value: price,
                currency: 'GBP'
            });
        }
    }
    
    /**
     * Track step completion
     */
    function trackStepCompletion(stepName) {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'step_completed', {
                event_category: 'booking_flow',
                event_label: stepName,
                step_number: 1
            });
        }
    }
    
    /**
     * Load saved selection on page load
     */
    function loadSavedSelection() {
        const saved = localStorage.getItem('bms_service_selection');
        if (saved) {
            try {
                const selectionData = JSON.parse(saved);
                // Restore selection if recent (within 1 hour)
                const savedTime = new Date(selectionData.timestamp);
                const now = new Date();
                const hoursDiff = (now - savedTime) / (1000 * 60 * 60);
                
                if (hoursDiff < 1) {
                    serviceSelection = selectionData;
                    updateServiceCardSelection(selectionData.service);
                    showSelectionSummary();
                }
            } catch (e) {
                console.warn('Failed to load saved service selection');
            }
        }
    }
    
    // Initialize when document is ready
    initServiceSelection();
    // Removed loadSavedSelection() to prevent auto-selection on page load
    
    // Expose functions for external use
    window.BMSServiceSelection = {
        selectService: selectService,
        selectServiceCombo: selectServiceCombo,
        getSelection: () => serviceSelection,
        continueToDateTime: continueToDateTime,
        toggleServiceComparison: toggleServiceComparison
    };
});

/**
 * CSS for notifications (injected dynamically)
 */
jQuery(document).ready(function($) {
    const notificationStyles = `
        <style>
        .bms-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }
        
        .bms-notification.fade-in {
            opacity: 1;
            transform: translateX(0);
        }
        
        .bms-notification-success {
            border-left: 4px solid #10b981;
        }
        
        .bms-notification-error {
            border-left: 4px solid #ef4444;
        }
        
        .bms-notification-info {
            border-left: 4px solid #3b82f6;
        }
        
        .notification-content {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }
        
        .notification-message {
            flex: 1;
            font-size: 0.9rem;
            line-height: 1.4;
            color: #1e293b;
        }
        
        .notification-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .notification-close:hover {
            color: #1e293b;
        }
        
        @media (max-width: 480px) {
            .bms-notification {
                top: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
        </style>
    `;
    
    $('head').append(notificationStyles);
});

/**
 * Compatibility functions for global access
 */
function toggleServiceComparison() {
    if (window.BMSServiceSelection) {
        window.BMSServiceSelection.toggleServiceComparison();
    }
}

function continueToNextStep() {
    if (window.BMSServiceSelection) {
        window.BMSServiceSelection.continueToDateTime();
    }
}
