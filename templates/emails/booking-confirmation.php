<?php
/**
 * Booking Confirmation Email Template - Customer
 * 
 * @package BlueMotosSouthampton
 */

// Make sure we have the required variables
if (!isset($customer_name) || !isset($booking_reference)) {
    return;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Blue Motors Southampton</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0; 
            padding: 0; 
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .header { 
            background: #3366CC; 
            color: white; 
            padding: 20px; 
            text-align: center; 
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            font-size: 16px;
        }
        .content { 
            background: #f9f9f9; 
            padding: 30px 20px; 
        }
        .booking-details { 
            background: white; 
            padding: 20px; 
            margin: 20px 0; 
            border-left: 4px solid #3366CC; 
            border-radius: 4px;
        }
        .booking-details h3 {
            margin-top: 0;
            color: #3366CC;
        }
        .booking-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .booking-details td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .booking-details td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .location-info {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .location-info h3 {
            margin-top: 0;
            color: #333;
        }
        .important-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer { 
            text-align: center; 
            color: #666; 
            font-size: 12px; 
            margin-top: 30px; 
            padding: 20px;
            background: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }
        .button { 
            background: #4CAF50; 
            color: white; 
            padding: 12px 24px; 
            text-decoration: none; 
            border-radius: 5px; 
            display: inline-block;
            margin: 10px 0;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Confirmation</h1>
            <p>Blue Motors Southampton</p>
        </div>
        
        <div class="content">
            <p>Dear <?php echo esc_html($customer_name); ?>,</p>
            
            <p>Thank you for booking your service with Blue Motors Southampton. Your booking has been confirmed and payment has been processed successfully.</p>
            
            <div class="booking-details">
                <h3>📋 Booking Details</h3>
                <table>
                    <tr>
                        <td>Reference Number:</td>
                        <td><strong><?php echo esc_html($booking_reference); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Service:</td>
                        <td><?php echo esc_html($service_name ?? 'Service Booking'); ?></td>
                    </tr>
                    <tr>
                        <td>Date:</td>
                        <td><?php echo esc_html($booking_date ?? 'TBC'); ?></td>
                    </tr>
                    <tr>
                        <td>Time:</td>
                        <td><?php echo esc_html($booking_time ?? 'TBC'); ?></td>
                    </tr>
                    <tr>
                        <td>Vehicle:</td>
                        <td><?php echo esc_html($vehicle_details ?? 'Vehicle details'); ?></td>
                    </tr>
                    <?php if (!empty($vehicle_registration)): ?>
                    <tr>
                        <td>Registration:</td>
                        <td><strong><?php echo esc_html($vehicle_registration); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Total Paid:</td>
                        <td class="total-amount">£<?php echo esc_html($amount_paid ?? '0.00'); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="location-info">
                <h3>📍 Our Location</h3>
                <p>
                    <strong>Blue Motors Southampton</strong><br>
                    1 Kent St, Northam<br>
                    Southampton SO14 5SP<br>
                    📞 Tel: 023 8000 0000<br>
                    ✉️ Email: southampton@bluemotors.co.uk
                </p>
                
                <p>
                    <a href="https://www.google.com/maps/search/?api=1&query=1+Kent+St+Northam+Southampton+SO14+5SP" 
                       class="button" target="_blank">
                        📍 Get Directions
                    </a>
                </p>
            </div>
            
            <div class="important-note">
                <p><strong>⏰ Important:</strong> Please arrive 10 minutes before your appointment time with your vehicle keys and any relevant documentation.</p>
            </div>
            
            <p><strong>Need to make changes?</strong><br>
            If you need to reschedule or cancel your appointment, please call us on <strong>023 8000 0000</strong> as soon as possible.</p>
            
            <?php if (!empty($special_notes)): ?>
            <div class="booking-details">
                <h3>📝 Additional Notes</h3>
                <p><?php echo esc_html($special_notes); ?></p>
            </div>
            <?php endif; ?>
            
            <p>We look forward to servicing your vehicle and providing you with excellent service!</p>
            
            <p>Best regards,<br>
            <strong>The Blue Motors Southampton Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This email was sent from Blue Motors Southampton<br>
            1 Kent St, Northam, Southampton SO14 5SP | 023 8000 0000</p>
            <p>If you have any questions, please don't hesitate to contact us.</p>
        </div>
    </div>
</body>
</html>
