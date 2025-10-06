<?php
/**
 * Services Settings Page for Blue Motors Southampton
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Service Settings</h1>
    
    <div class="notice notice-info">
        <p><strong>Service Configuration</strong></p>
        <p>Service settings are currently configured in the code. Advanced service management will be added in a future update.</p>
    </div>
    
    <div class="card" style="max-width: 800px;">
        <h2>Current Services</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Base Price</th>
                    <th>Pricing Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>MOT Testing</strong></td>
                    <td>£40.00</td>
                    <td>Fixed Price</td>
                    <td><span style="color: green;">✓ Active</span></td>
                </tr>
                <tr>
                    <td><strong>Full Service</strong></td>
                    <td>From £149.00</td>
                    <td>Engine-based Pricing</td>
                    <td><span style="color: green;">✓ Active</span></td>
                </tr>
                <tr>
                    <td><strong>Interim Service</strong></td>
                    <td>From £89.00</td>
                    <td>Engine-based Pricing</td>
                    <td><span style="color: green;">✓ Active</span></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>Pricing Matrix</h2>
        <p>Prices are calculated based on engine size and fuel type:</p>
        
        <h3>Petrol/Diesel Vehicles</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Engine Size</th>
                    <th>Interim Service</th>
                    <th>Full Service</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Up to 1000cc</td>
                    <td>£140</td>
                    <td>£225</td>
                </tr>
                <tr>
                    <td>Up to 1600cc</td>
                    <td>£175</td>
                    <td>£245</td>
                </tr>
                <tr>
                    <td>Up to 2000cc</td>
                    <td>£185</td>
                    <td>£255</td>
                </tr>
                <tr>
                    <td>Up to 3500cc</td>
                    <td>£215</td>
                    <td>£285</td>
                </tr>
            </tbody>
        </table>
        
        <h3>Hybrid Vehicles</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Engine Size</th>
                    <th>Interim Service</th>
                    <th>Full Service</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Up to 1000cc</td>
                    <td>£115</td>
                    <td>£205</td>
                </tr>
                <tr>
                    <td>Up to 1600cc</td>
                    <td>£165</td>
                    <td>£235</td>
                </tr>
                <tr>
                    <td>Up to 2000cc</td>
                    <td>£175</td>
                    <td>£245</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>Configuration Files</h2>
        <p>Service settings are currently managed in these files:</p>
        <ul>
            <li><code>config/services.php</code> - Service definitions</li>
            <li><code>config/pricing-matrix.php</code> - Pricing calculations</li>
        </ul>
        
        <p><strong>Future Updates:</strong> A user-friendly interface for managing services and pricing will be added in a future version.</p>
    </div>
</div>
