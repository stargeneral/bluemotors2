<?php
/**
 * Customer History Template
 * Shows comprehensive service history with AI-powered recommendations
 * 
 * @package BlueMotosSouthampton
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables from shortcode
$customer_history = $history ?? [];
$show_recommendations = filter_var($atts['show_recommendations'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
$show_loyalty = filter_var($atts['show_loyalty'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
?>

<div class="bms-customer-history-container">
    
    <?php if ($show_loyalty && !empty($customer_history['loyalty'])): ?>
    <!-- Loyalty Status Section -->
    <div class="bms-loyalty-status <?php echo esc_attr($customer_history['loyalty']['tier']); ?>">
        <div class="loyalty-header">
            <h3><?php echo esc_html($customer_history['loyalty']['title']); ?></h3>
            <p class="loyalty-description"><?php echo esc_html($customer_history['loyalty']['description']); ?></p>
        </div>
        
        <?php if ($customer_history['loyalty']['discount'] > 0): ?>
        <div class="loyalty-benefits">
            <div class="benefit-item">
                <span class="benefit-icon">💰</span>
                <span class="benefit-text"><?php echo esc_html($customer_history['loyalty']['discount']); ?>% Loyalty Discount</span>
            </div>
            
            <?php if ($customer_history['loyalty']['priority_booking']): ?>
            <div class="benefit-item">
                <span class="benefit-icon">⚡</span>
                <span class="benefit-text">Priority Booking Access</span>
            </div>
            <?php endif; ?>
            
            <?php if ($customer_history['loyalty']['free_pickup_delivery']): ?>
            <div class="benefit-item">
                <span class="benefit-icon">🚗</span>
                <span class="benefit-text">Free Pickup & Delivery</span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <p class="competitive-note"><?php echo esc_html($customer_history['loyalty']['competitive_advantage']); ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Customer Statistics -->
    <?php if (!empty($customer_history['stats'])): ?>
    <div class="bms-customer-stats">
        <h3>Your Service Summary</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-value"><?php echo esc_html($customer_history['stats']['total_bookings']); ?></span>
                <span class="stat-label">Total Services</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">£<?php echo esc_html($customer_history['stats']['total_spent']); ?></span>
                <span class="stat-label">Total Invested</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?php echo esc_html($customer_history['stats']['years_as_customer']); ?> years</span>
                <span class="stat-label">Customer Since</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?php echo esc_html($customer_history['stats']['preferred_day_of_week']); ?></span>
                <span class="stat-label">Preferred Day</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- AI-Powered Recommendations -->
    <?php if ($show_recommendations && !empty($customer_history['recommendations'])): ?>
    <div class="bms-recommendations">
        <h3>Personalized Recommendations</h3>
        <p class="recommendations-intro">Based on your service history and vehicle needs:</p>
        
        <div class="recommendations-list">
            <?php foreach ($customer_history['recommendations'] as $recommendation): ?>
            <div class="recommendation-card <?php echo esc_attr($recommendation['urgency']); ?>-urgency">
                <div class="recommendation-header">
                    <h4><?php echo esc_html($recommendation['title']); ?></h4>
                    <?php if ($recommendation['urgency'] === 'high'): ?>
                    <span class="urgency-badge high">Urgent</span>
                    <?php elseif ($recommendation['urgency'] === 'medium'): ?>
                    <span class="urgency-badge medium">Recommended</span>
                    <?php endif; ?>
                </div>
                
                <p class="recommendation-description"><?php echo esc_html($recommendation['description']); ?></p>
                
                <?php if (!empty($recommendation['estimated_due_date'])): ?>
                <p class="due-date">Estimated due: <?php echo date('d/m/Y', strtotime($recommendation['estimated_due_date'])); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($recommendation['discount'])): ?>
                <div class="recommendation-discount">
                    <span class="discount-badge"><?php echo esc_html($recommendation['discount']); ?>% OFF</span>
                    <?php if (!empty($recommendation['exclusive'])): ?>
                    <span class="exclusive-badge">Exclusive Offer</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($recommendation['service'])): ?>
                <a href="<?php echo esc_url(add_query_arg('service', $recommendation['service'], get_permalink())); ?>" 
                   class="btn-book-recommendation">Book Now</a>
                <?php endif; ?>
                
                <?php if (!empty($recommendation['f1_comparison'])): ?>
                <p class="service-comparison"><?php echo esc_html($recommendation['f1_comparison']); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Service History -->
    <?php if (!empty($customer_history['history'])): ?>
    <div class="bms-service-history">
        <h3>Service History</h3>
        <div class="history-table-wrapper">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Service</th>
                        <th>Vehicle</th>
                        <th>Cost</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customer_history['history'] as $booking): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($booking->appointment_date)); ?></td>
                        <td><?php echo esc_html(ucwords(str_replace('_', ' ', $booking->service_type))); ?></td>
                        <td><?php echo esc_html($booking->vehicle_registration ?? 'N/A'); ?></td>
                        <td>£<?php echo esc_html($booking->calculated_price); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($booking->booking_status); ?>">
                                <?php echo esc_html(ucfirst($booking->booking_status)); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="no-history">
        <p>No service history found. Book your first service with us and start enjoying our superior customer care!</p>
        <a href="<?php echo esc_url(get_permalink()); ?>" class="btn-book-first">Book Your First Service</a>
    </div>
    <?php endif; ?>
    
    <div class="competitive-footer">
        <p><?php echo esc_html($customer_history['competitive_advantage'] ?? 'This comprehensive service tracking is not available at other automotive services!'); ?></p>
    </div>
</div>

<style>
/* Customer History Styles */
.bms-customer-history-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Loyalty Status */
.bms-loyalty-status {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.bms-loyalty-status.platinum {
    background: linear-gradient(135deg, #e5e7eb 0%, #9ca3af 100%);
}

.bms-loyalty-status.gold {
    background: linear-gradient(135deg, #fef3c7 0%, #fbbf24 100%);
}

.bms-loyalty-status.silver {
    background: linear-gradient(135deg, #e5e7eb 0%, #9ca3af 100%);
}

.bms-loyalty-status.bronze {
    background: linear-gradient(135deg, #fed7aa 0%, #f97316 100%);
}

.loyalty-header h3 {
    margin: 0 0 8px 0;
    font-size: 24px;
    color: #1f2937;
}

.loyalty-benefits {
    display: flex;
    gap: 20px;
    margin: 20px 0;
    flex-wrap: wrap;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.8);
    padding: 8px 16px;
    border-radius: 20px;
}

/* Customer Stats */
.bms-customer-stats {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 28px;
    font-weight: bold;
    color: #1e3a8a;
    margin-bottom: 4px;
}

.stat-label {
    display: block;
    font-size: 14px;
    color: #6b7280;
}

/* Recommendations */
.bms-recommendations {
    background: #f9fafb;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}

.recommendations-list {
    display: grid;
    gap: 16px;
    margin-top: 20px;
}

.recommendation-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    position: relative;
}

.recommendation-card.high-urgency {
    border-color: #ef4444;
}

.recommendation-card.medium-urgency {
    border-color: #f59e0b;
}

.recommendation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.urgency-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.urgency-badge.high {
    background: #ef4444;
    color: white;
}

.urgency-badge.medium {
    background: #f59e0b;
    color: white;
}

.recommendation-discount {
    display: flex;
    gap: 12px;
    margin: 16px 0;
}

.discount-badge {
    background: #10b981;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: bold;
}

.exclusive-badge {
    background: #7c3aed;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
}

.btn-book-recommendation {
    display: inline-block;
    background: #1e3a8a;
    color: white;
    padding: 10px 24px;
    border-radius: 6px;
    text-decoration: none;
    margin-top: 12px;
    transition: background 0.3s ease;
}

.btn-book-recommendation:hover {
    background: #1e40af;
}

.service-comparison {
    font-size: 12px;
    color: #6b7280;
    font-style: italic;
    margin-top: 12px;
}

/* Service History Table */
.bms-service-history {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}

.history-table-wrapper {
    overflow-x: auto;
    margin-top: 20px;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
}

.history-table th {
    background: #f3f4f6;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #e5e7eb;
}

.history-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-confirmed {
    background: #dbeafe;
    color: #1e40af;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

/* No History */
.no-history {
    text-align: center;
    padding: 60px 20px;
    background: #f9fafb;
    border-radius: 12px;
}

.btn-book-first {
    display: inline-block;
    background: #1e3a8a;
    color: white;
    padding: 12px 32px;
    border-radius: 6px;
    text-decoration: none;
    margin-top: 20px;
    font-weight: 600;
}

/* Professional Footer */
.competitive-footer {
    text-align: center;
    padding: 20px;
    color: #6b7280;
    font-style: italic;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .loyalty-benefits {
        flex-direction: column;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .history-table {
        font-size: 14px;
    }
    
    .history-table th,
    .history-table td {
        padding: 8px;
    }
}
</style>
