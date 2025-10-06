<?php
/**
 * Admin Booking Notification Email Template
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
    <title>New Booking - <?php echo esc_html($booking_reference); ?></title>
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
            background: #ff6600; 
            color: white; 
            padding: 20px; 
            text-align: center; 
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .content { 
            background: #f9f9f9; 
            padding: 30px 20px; 
        }
        .urgent { 
            background: #ffe6e6; 
            padding: 15px; 
            border: 1px solid #ff9999; 
            margin: 20px 0; 
            border-radius: 4px;
            text-align: center;
        }
        .urgent strong {
            color: #d63031;
        }
        .info-section { 
            background: white; 
            padding: 20px; 
            margin: 20px 0; 
            border-left: 4px solid #ff6600; 
            border-radius: 4px;
        }
        .info-section h3 {
            margin-top: 0;
            color: #ff6600;
        }
        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-section td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-section td:first-child {
            font-weight: bold;
            width: 35%;
        }
        .payment-status {
            color: #00b894;
            font-weight: bold;
        }
        .next-steps {
            background: #e8f4fd;
            border: 1px solid #3498db;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .next-steps h3 {
            margin-top: 0;
            color: #2980b9;
        }
        .next-steps ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin: 8px 0;
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
        .high-priority {
            font-size: 18px;
            font-weight: bold;
            color: #d63031;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 New Booking Received</h1>
            <p>Blue Motors Southampton</p>
        </div>
        
        <div class="content">
            <div class="urgent">
                <p class="high-priority">⚠️ NEW BOOKING REQUIRES ATTENTION</p>
                <p>A new service booking has been made online and payment has been processed successfully.</p>
                <p><strong>Booking Reference: <?php echo esc_html($booking_reference); ?></strong></p>
            </div>
            
            <div class="info-section">
                <h3>👤 Customer Information</h3>
                <table>
                    <tr>
                        <td>Customer Name:</td>
                        <td><strong><?php echo esc_html($customer_name); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Email Address:</td>
                        <td><a href="mailto:<?php echo esc_attr($customer_email ?? ''); ?>"><?php echo esc_html($customer_email ?? 'Not provided'); ?></a></td>
                    </tr>
                    <tr>
                        <td>Phone Number:</td>
                        <td><a href="tel:<?php echo esc_attr($customer_phone ?? ''); ?>"><?php echo esc_html($customer_phone ?? 'Not provided'); ?></a></td>
                    </tr>
                </table>
            </div>
            
            <div class="info-section">
                <h3>📋 Booking Information</h3>
                <table>
                    <tr>
                        <td>Reference Number:</td>
                        <td><strong><?php echo esc_html($booking_reference); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Service Type:</td>
                        <td><?php echo esc_html($service_name ?? 'Service Booking'); ?></td>
                    </tr>
                    <tr>
                        <td>Scheduled Date:</td>
                        <td><strong><?php echo esc_html($booking_date ?? 'TBC'); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Scheduled Time:</td>
                        <td><strong><?php echo esc_html($booking_time ?? 'TBC'); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Vehicle Details:</td>
                        <td><?php echo esc_html($vehicle_details ?? 'Vehicle details'); ?></td>
                    </tr>
                    <?php if (!empty($vehicle_registration)): ?>
                    <tr>
                        <td>Registration:</td>
                        <td><strong><?php echo esc_html($vehicle_registration); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Amount Paid:</td>
                        <td><strong>£<?php echo esc_html($amount_paid ?? '0.00'); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Payment Status:</td>
                        <td class="payment-status">✅ PAID IN FULL</td>
                    </tr>
                </table>
            </div>
            
            <?php if (!empty($special_notes)): ?>
            <div class="info-section">
                <h3>📝 Special Notes</h3>
                <p style="background: #fff3cd; padding: 15px; border-radius: 4px; border: 1px solid #ffeaa7;">
                    <?php echo esc_html($special_notes); ?>
                </p>
            </div>
            <?php endif; ?>
            
            <div class="next-steps">
                <h3>📌 Next Steps Required</h3>
                <ul>
                    <li><strong>Add to workshop schedule</strong> - Block the time slot in your calendar</li>
                    <li><strong>Prepare service checklist</strong> - Ready the appropriate service documentation</li>
                    <li><strong>Check parts availability</strong> - Ensure any required parts are in stock</li>
                    <li><strong>Contact customer if needed</strong> - Call if any clarification is required</li>
                    <li><strong>Set up workspace</strong> - Prepare the bay for the scheduled service</li>
                </ul>
            </div>
            
            <div class="info-section">
                <h3>📞 Customer Contact</h3>
                <p>If you need to contact the customer:</p>
                <ul>
                    <li><strong>Phone:</strong> <a href="tel:<?php echo esc_attr($customer_phone ?? ''); ?>"><?php echo esc_html($customer_phone ?? 'Not provided'); ?></a></li>
                    <li><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($customer_email ?? ''); ?>"><?php echo esc_html($customer_email ?? 'Not provided'); ?></a></li>
                </ul>
                <p><em>The customer has already received a confirmation email with all booking details.</em></p>
            </div>
            
            <p style="text-align: center; font-size: 14px; color: #666;">
                This email was automatically generated by the Blue Motors Southampton booking system at <?php echo date('Y-m-d H:i:s'); ?>.
            </p>
        </div>
        
        <div class="footer">
            <p><strong>Blue Motors Southampton Online Booking System</strong></p>
            <p>For technical support with the booking system, please contact your web administrator.</p>
        </div>
    </div>
</body>
</html>
