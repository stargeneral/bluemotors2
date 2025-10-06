/**
 * Blue Motors Southampton - Booking Form JavaScript
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

jQuery(document).ready(function ($) {

    // Booking data object to store selections
    let bookingData = {
        service: null,
        vehicle: {},
        appointment: {},
        customer: {},
        price: 0
    };

    // Current step tracker
    let currentStep = 1;

    // Initialize Stripe
    let stripe = null;
    let elements = null;
    let cardElement = null;

    // Initialize Stripe if enabled and key is available
    if (typeof bms_ajax !== 'undefined' && bms_ajax.stripe_publishable_key) {
        stripe = Stripe(bms_ajax.stripe_publishable_key);
        elements = stripe.elements();
    }

    /**
     * Step 2: Vehicle Lookup
     */
    $('#btn-lookup-vehicle').on('click', function (e) {
        e.preventDefault();

        const registration = $('#vehicle-reg').val().replace(/\s/g, '').toUpperCase();

        if (!registration || registration.length < 5) {
            showMessage('Please enter a valid registration number', 'error');
            return;
        }

        // Show loading state
        $('#lookup-status').html('<div class="loading">Looking up vehicle...</div>');
        $(this).prop('disabled', true);

        // AJAX request for vehicle lookup
        $.ajax({
            url: bms_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bms_vehicle_lookup',
                registration: registration,
                service_type: bookingData.service,
                nonce: bms_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    // Store vehicle data
                    bookingData.vehicle = response.data;
                    bookingData.price = response.data.calculated_price;

                    // Display vehicle details
                    displayVehicleDetails(response.data);

                    $('#lookup-status').empty();
                    $('#vehicle-details-display').slideDown();
                } else {
                    showMessage('Vehicle not found. Please check the registration or enter details manually.', 'error');
                }
            },
            error: function () {
                showMessage('An error occurred. Please try again or enter details manually.', 'error');
            },
            complete: function () {
                $('#btn-lookup-vehicle').prop('disabled', false);
            }
        });
    });

    /**
     * Core function to move between booking steps
     */
    function moveToStep(stepNumber) {
        // Hide all step content
        $('.bms-step-content').hide();

        // Show target step content using correct ID format
        $('#step-' + stepNumber + '-content').fadeIn();

        // Update progress indicators
        $('.step').removeClass('active completed');

        // Mark previous steps as completed
        for (let i = 1; i < stepNumber; i++) {
            $('.step[data-step="' + i + '"]').addClass('completed');
        }

        // Mark current step as active
        $('.step[data-step="' + stepNumber + '"]').addClass('active');

        // Update current step tracker
        currentStep = stepNumber;

        // Scroll to top of form
        $('html, body').animate({
            scrollTop: $('.bms-booking-container').offset().top - 100
        }, 500);

        // Step-specific actions
        switch (stepNumber) {
            case 1:
                // Service selection step - remove any continue buttons from previous visits
                $('.continue-section').remove();
                break;
            case 2:
                // Vehicle details step
                $('#vehicle-reg').focus();
                // Ensure service is selected
                if (!bookingData.service) {
                    moveToStep(1);
                    showMessage('Please select a service first', 'error');
                    return;
                }
                break;
            case 3:
                // Date/time step - ensure service is selected
                if (!bookingData.service) {
                    moveToStep(1);
                    showMessage('Please select a service first', 'error');
                    return;
                }
                break;
            case 4:
                // Customer details step
                $('#customer-name').focus();
                break;
            case 5:
                // Payment step
                updateBookingSummary();
                initializePayment();
                break;
        }
    }

    /**
     * Display vehicle details after successful lookup
     */
    function displayVehicleDetails(vehicleData) {
        $('#display-make').text(vehicleData.make || 'N/A');
        $('#display-model').text(vehicleData.model || 'N/A');
        $('#display-year').text(vehicleData.year || 'N/A');
        $('#display-engine').text(vehicleData.engineCapacity || 'N/A');
        $('#display-fuel').text(vehicleData.fuelType || 'N/A');
        $('#service-price').text((vehicleData.calculated_price || 0).toFixed(2));

        // Update global booking data with vehicle information and correct pricing
        bookingData.vehicle = vehicleData;
        bookingData.price = vehicleData.calculated_price || 0;

        // If this is a combo selection, update the service type and MOT flag
        if (vehicleData.is_combo) {
            bookingData.motIncluded = true;
            bookingData.service = vehicleData.service_type;
        }

        // Update service pricing based on vehicle data
        if (typeof window.updateServicePricing === 'function') {
            window.updateServicePricing(vehicleData);
        }
    }

    /**
     * Show message to user
     */
    function showMessage(message, type = 'info') {
        // Remove existing messages
        $('.bms-message').remove();

        const messageHtml = `
            <div class="bms-message bms-message-${type}">
                ${message}
                <button type="button" class="message-close">&times;</button>
            </div>
        `;

        $('.bms-booking-container').prepend(messageHtml);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            $('.bms-message').fadeOut();
        }, 5000);

        // Close button handler
        $('.message-close').on('click', function () {
            $(this).parent().fadeOut();
        });
    }

    /**
     * Update booking summary for final step
     */
    function updateBookingSummary() {
        let summaryHtml = '<div class="summary-section">';

        // Service details
        if (bookingData.service) {
            summaryHtml += `
                <div class="summary-item">
                    <strong>Service:</strong> ${getServiceName(bookingData.service)}
                    <span class="summary-price">£${bookingData.price.toFixed(2)}</span>
                </div>
            `;
        }

        // Vehicle details
        if (bookingData.vehicle.make) {
            summaryHtml += `
                <div class="summary-item">
                    <strong>Vehicle:</strong> ${bookingData.vehicle.make} ${bookingData.vehicle.model}
                    <span class="summary-detail">${bookingData.vehicle.registration || ''}</span>
                </div>
            `;
        }

        // Appointment details
        if (bookingData.appointment.date) {
            summaryHtml += `
                <div class="summary-item">
                    <strong>Appointment:</strong> ${bookingData.appointment.date}
                    <span class="summary-detail">${bookingData.appointment.time || ''}</span>
                </div>
            `;
        }

        summaryHtml += '</div>';
        $('#booking-summary-details').html(summaryHtml);
        $('#total-amount').text(bookingData.price.toFixed(2));
    }

    /**
     * Get display name for service
     */
    function getServiceName(serviceType) {
        const names = {
            'interim_service': 'Interim Service',
            'full_service': 'Full Service',
            'mot_test': 'MOT Test'
        };
        return names[serviceType] || serviceType;
    }

    /**
     * Initialize payment processing
     */
    function initializePayment() {
        if (stripe && elements) {
            // Payment initialization code here
            console.log('Payment system ready');
        }
    }

    // Make moveToStep globally available
    window.moveToStep = moveToStep;
    window.bookingData = bookingData;

    // Continue button handlers
    $('#btn-continue-to-date').on('click', function () {
        moveToStep(3); // Move to Date & Time step
    });

    $('#btn-continue-to-details').on('click', function () {
        moveToStep(4); // Move to Customer Details step
    });

    $('#btn-continue-to-payment').on('click', function () {
        moveToStep(5); // Move to Payment step
    });

});
