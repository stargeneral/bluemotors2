/**
 * Booking Form Enhancements
 * Smart Scheduler Integration
 */

jQuery(document).ready(function($) {
    
    // Global function for smart scheduler integration
    window.selectAppointmentSlot = function(date, time) {
        // Store the selected appointment
        $('#appointment-date').val(date);
        $('#appointment-time').val(time);
        
        // Format date for UK display
        const dateObj = new Date(date);
        const ukDate = dateObj.toLocaleDateString('en-GB', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Update the display
        $('#selected-date').text(ukDate);
        $('#selected-time').text(time);
        
        // Hide scheduler and show selected appointment
        $('.smart-scheduler-integration').slideUp();
        $('#selected-appointment').slideDown();
        
        // Enable continue button
        $('#btn-continue-to-details').show();
    };
    
    // Handle change appointment button
    $(document).on('click', '.btn-change-appointment', function() {
        $('.smart-scheduler-integration').slideDown();
        $('#selected-appointment').slideUp();
        $('#btn-continue-to-details').hide();
    });
    
    // When step 3 is shown, update smart scheduler with selected service
    $(document).on('click', '#btn-continue-to-date', function() {
        // Get selected service from form data
        const selectedService = $('input[name="selected_service"]').val() || 
                               $('.service-card.selected').data('service') || 
                               'mot_test';
        
        // Update smart scheduler service type
        setTimeout(function() {
            $('#smart-service-type').val(selectedService).trigger('change');
        }, 500);
    });
    
    // Track selected service
    $(document).on('click', '.btn-select-service', function() {
        const service = $(this).data('service');
        $('.service-card').removeClass('selected');
        $(this).closest('.service-card').addClass('selected');
        
        // Store selected service
        if (!$('input[name="selected_service"]').length) {
            $('#bms-booking-form').append('<input type="hidden" name="selected_service" value="' + service + '">');
        } else {
            $('input[name="selected_service"]').val(service);
        }
    });
    
    // Enhance continue button behavior
    $(document).on('click', '#btn-continue-to-details', function() {
        const appointmentDate = $('#appointment-date').val();
        const appointmentTime = $('#appointment-time').val();
        
        if (!appointmentDate || !appointmentTime) {
            alert('Please select an appointment date and time');
            return false;
        }
        
        // Continue to next step
        $('.bms-step-content').hide();
        $('#step-4-content').show();
        
        // Update progress steps
        $('.step').removeClass('active');
        $('.step[data-step="4"]').addClass('active');
        $('.step[data-step="1"], .step[data-step="2"], .step[data-step="3"]').addClass('completed');
    });
    
    // Add completed step styling
    const style = `
        <style>
        .step.completed .step-number {
            background: #10b981;
            color: white;
        }
        .step.completed .step-number::after {
            content: '✓';
            position: absolute;
            font-size: 16px;
        }
        .service-card.selected {
            border: 2px solid #3b82f6;
            background: #eff6ff;
        }
        </style>
    `;
    $('head').append(style);
});
