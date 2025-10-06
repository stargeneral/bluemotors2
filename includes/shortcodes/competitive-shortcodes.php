<?php
/**
 * Competitive Advantage Shortcodes for Blue Motors Southampton
 * 
 * Shortcodes to highlight our advantages
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register competitive advantage shortcodes (with protection against double registration)
 */
if (!shortcode_exists('bms_vs_f1')) {
    add_shortcode('bms_vs_f1', 'bms_vs_f1_shortcode');
}
if (!shortcode_exists('bms_why_choose_us')) {
    add_shortcode('bms_why_choose_us', 'bms_why_choose_us_shortcode');
}
if (!shortcode_exists('bms_price_calculator')) {
    add_shortcode('bms_price_calculator', 'bms_price_calculator_shortcode');
}
if (!shortcode_exists('bms_booking_status')) {
    add_shortcode('bms_booking_status', 'bms_booking_status_shortcode');
}

/**
 * Blue Motors vs industry leaders comparison shortcode
 */
function bms_vs_f1_shortcode($atts) {
    $atts = shortcode_atts(array(
        'style' => 'table',     // table, cards, list
        'show_title' => 'true',
        'highlight' => 'advantages' // advantages, all), $atts, 'bms_vs_f1');
    ), $atts, 'bms_vs_f1');
    
    ob_start();
    ?>
    
    <div class="bms-vs-f1-comparison">
        <?php if ($atts['show_title'] === 'true'): ?>
        <div class="comparison-header">
            <h3>🎯 Blue Motors Southampton vs others</h3>
            <p class="comparison-subtitle">See why Southampton customers choose us over the chain</p>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['style'] === 'table'): ?>
        <div class="comparison-table-container">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th class="blue-motors-col">Blue Motors Southampton</th>
                        <th class="f1-col">industry leaders</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="advantage-row">
                        <td><strong>Online Tyre Ordering</strong></td>
                        <td class="advantage">✅ Complete online ordering system</td>
                        <td class="disadvantage">❌ Must call for tyre quotes</td>
                    </tr>
                    <tr class="advantage-row">
                        <td><strong>Payment Process</strong></td>
                        <td class="advantage">✅ Multiple secure options, UK-optimized</td>
                        <td class="disadvantage">⚠️ PayPal integration issues reported</td>
                    </tr>
                    <tr class="advantage-row">
                        <td><strong>Date Format</strong></td>
                        <td class="advantage">✅ UK format (DD/MM/YYYY)</td>
                        <td class="disadvantage">❌ American format confuses customers</td>
                    </tr>
                    <tr class="advantage-row">
                        <td><strong>Mobile Experience</strong></td>
                        <td class="advantage">✅ Touch-optimized, responsive design</td>
                        <td class="disadvantage">⚠️ Basic mobile interface</td>
                    </tr>
                    <tr>
                        <td><strong>Website Access</strong></td>
                        <td class="advantage">✅ Always accessible, fast loading</td>
                        <td class="disadvantage">❌ Cloudflare blocks legitimate users</td>
                    </tr>
                    <tr>
                        <td><strong>Local Focus</strong></td>
                        <td class="advantage">✅ Southampton specialists</td>
                        <td class="neutral">⚠️ Generic chain (130+ locations)</td>
                    </tr>
                    <tr>
                        <td><strong>MOT Testing</strong></td>
                        <td class="neutral">✅ From £40</td>
                        <td class="neutral">✅ From £35</td>
                    </tr>
                    <tr>
                        <td><strong>Service Range</strong></td>
                        <td class="neutral">✅ Full automotive services</td>
                        <td class="neutral">✅ Full range available</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <?php elseif ($atts['style'] === 'cards'): ?>
        <div class="comparison-cards">
            <div class="comparison-card blue-motors-card">
                <div class="card-header">
                    <h4>🏆 Blue Motors Southampton</h4>
                    <span class="card-badge winner">Our Advantages</span>
                </div>
                <ul class="advantage-list">
                    <li>🛞 <strong>Online Tyre Ordering:</strong> Complete system - no phone calls!</li>
                    <li>💳 <strong>Smooth Payments:</strong> UK-optimized, multiple options</li>
                    <li>📅 <strong>UK Date Format:</strong> DD/MM/YYYY always</li>
                    <li>📱 <strong>Mobile Excellence:</strong> Touch-optimized design</li>
                    <li>🏠 <strong>Local Experts:</strong> Southampton specialists</li>
                    <li>🚀 <strong>Always Available:</strong> No access barriers</li>
                </ul>
            </div>
            
            <div class="comparison-card f1-card">
                <div class="card-header">
                    <h4>🔗 industry leaders</h4>
                    <span class="card-badge competitor">Chain Alternative</span>
                </div>
                <ul class="disadvantage-list">
                    <li>📞 <strong>Tyre Orders:</strong> Must call - no online ordering</li>
                    <li>⚠️ <strong>Payment Issues:</strong> PayPal problems reported</li>
                    <li>🇺🇸 <strong>American Dates:</strong> MM/DD/YYYY confuses UK customers</li>
                    <li>📱 <strong>Basic Mobile:</strong> Limited mobile experience</li>
                    <li>🏢 <strong>Generic Chain:</strong> 130+ locations, less personal</li>
                    <li>🚫 <strong>Access Problems:</strong> Cloudflare blocks users</li>
                </ul>
            </div>
        </div>
        
        <?php else: // list style ?>
        <div class="comparison-list">
            <div class="advantages-section">
                <h4>🏆 Our Key Advantages Over industry leaders:</h4>
                <ul class="advantages-list">
                    <li><strong>Online Tyre Ordering:</strong> Complete system vs F1's phone-only approach</li>
                    <li><strong>UK-First Design:</strong> Proper date format vs F1's American confusion</li>
                    <li><strong>Payment Excellence:</strong> Multiple options vs F1's PayPal issues</li>
                    <li><strong>Mobile Optimized:</strong> Touch-friendly vs F1's basic interface</li>
                    <li><strong>Local Expertise:</strong> Southampton specialists vs generic chain</li>
                    <li><strong>Reliable Access:</strong> Always available vs F1's Cloudflare blocks</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="comparison-cta">
            <h4>Ready to Experience the Difference?</h4>
            <div class="cta-buttons">
                <a href="/book-service" class="btn-cta btn-primary">
                    📅 Book Online Now
                </a>
                <a href="/tyres" class="btn-cta btn-secondary">
                    🛞 Order Tyres Online
                </a>
            </div>
            <p class="cta-note">
                <small>✨ Try our online system - see why Southampton customers prefer us!</small>
            </p>
        </div>
    </div>
    
    <style>
    .bms-vs-f1-comparison {
        margin: 20px 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .comparison-header {
        text-align: center;
        margin-bottom: 24px;
    }
    
    .comparison-header h3 {
        color: #1e3a8a;
        margin-bottom: 8px;
    }
    
    .comparison-subtitle {
        color: #6b7280;
        font-style: italic;
    }
    
    /* Table Style */
    .comparison-table-container {
        overflow-x: auto;
        margin: 20px 0;
    }
    
    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    }
    
    .comparison-table th {
        background: #1e3a8a;
        color: white;
        padding: 16px 12px;
        text-align: left;
        font-weight: 600;
    }
    
    .blue-motors-col {
        background: #1e40af !important;
    }
    
    .f1-col {
        background: #7c2d12 !important;
    }
    
    .comparison-table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }
    
    .advantage-row {
        background: #f0f9ff;
    }
    
    .advantage {
        color: #059669;
        font-weight: 600;
    }
    
    .disadvantage {
        color: #dc2626;
        font-weight: 600;
    }
    
    .neutral {
        color: #6b7280;
        font-weight: 500;
    }
    
    /* Cards Style */
    .comparison-cards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin: 20px 0;
    }
    
    .comparison-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 2px solid #e5e7eb;
    }
    
    .blue-motors-card {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff, #f0f9ff);
    }
    
    .f1-card {
        border-color: #dc2626;
        background: linear-gradient(135deg, #fef2f2, #fef2f2);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .card-header h4 {
        margin: 0;
        color: #1e3a8a;
    }
    
    .card-badge {
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .card-badge.winner {
        background: #dcfce7;
        color: #166534;
    }
    
    .card-badge.competitor {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .advantage-list, .disadvantage-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .advantage-list li {
        margin-bottom: 8px;
        padding: 8px;
        background: rgba(34, 197, 94, 0.1);
        border-radius: 6px;
        border-left: 4px solid #22c55e;
    }
    
    .disadvantage-list li {
        margin-bottom: 8px;
        padding: 8px;
        background: rgba(239, 68, 68, 0.1);
        border-radius: 6px;
        border-left: 4px solid #ef4444;
    }
    
    /* List Style */
    .advantages-section h4 {
        color: #1e3a8a;
        margin-bottom: 16px;
    }
    
    .advantages-list {
        list-style: none;
        padding: 0;
    }
    
    .advantages-list li {
        margin-bottom: 12px;
        padding: 12px 16px;
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        border-radius: 6px;
    }
    
    /* CTA Section */
    .comparison-cta {
        background: linear-gradient(135deg, #1e3a8a, #3b82f6);
        color: white;
        padding: 24px;
        border-radius: 12px;
        text-align: center;
        margin-top: 24px;
    }
    
    .comparison-cta h4 {
        margin: 0 0 16px 0;
        color: white;
    }
    
    .cta-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-bottom: 12px;
    }
    
    .btn-cta {
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: #22c55e;
        color: white;
    }
    
    .btn-primary:hover {
        background: #16a34a;
        transform: translateY(-1px);
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #4b5563;
        transform: translateY(-1px);
    }
    
    .cta-note {
        margin: 0;
        opacity: 0.9;
        font-size: 14px;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .comparison-cards {
            grid-template-columns: 1fr;
        }
        
        .comparison-table {
            font-size: 14px;
        }
        
        .comparison-table th,
        .comparison-table td {
            padding: 8px 6px;
        }
        
        .cta-buttons {
            flex-direction: column;
        }
        
        .btn-cta {
            justify-content: center;
        }
    }
    </style>
    
    <?php
    return ob_get_clean();
}

/**
 * Why Choose Us shortcode
 */
function bms_why_choose_us_shortcode($atts) {
    $atts = shortcode_atts(array(
        'style' => 'grid',      // grid, list, highlights
        'show_title' => 'true',
        'columns' => '3'), $atts, 'bms_why_choose_us');
    
    ob_start();
    ?>
    
    <div class="bms-why-choose-us">
        <?php if ($atts['show_title'] === 'true'): ?>
        <div class="why-choose-header">
            <h3>🏆 Why Choose Blue Motors Southampton?</h3>
            <p>Experience the difference of local expertise with modern convenience</p>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['style'] === 'grid'): ?>
        <div class="benefits-grid" style="grid-template-columns: repeat(<?php echo esc_attr($atts['columns']); ?>, 1fr);">
            <div class="benefit-card">
                <div class="benefit-icon">🛞</div>
                <h4>Online Tyre Ordering</h4>
                <p>Order tyres online instantly - no phone calls required like industry leaders!</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">🇬🇧</div>
                <h4>UK-First Design</h4>
                <p>Proper British date format and UK-optimized experience throughout.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">🏠</div>
                <h4>Local Specialists</h4>
                <p>Southampton garage experts who know the local area and customers.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">💳</div>
                <h4>Smooth Payments</h4>
                <p>Multiple secure payment options without the friction of chain competitors.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">📱</div>
                <h4>Mobile Excellence</h4>
                <p>Touch-optimized mobile experience designed for modern smartphones.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">⚡</div>
                <h4>Always Available</h4>
                <p>Reliable website access without the blocking issues of competitors.</p>
            </div>
        </div>
        
        <?php else: // list or highlights ?>
        <div class="benefits-list">
            <ul class="benefits-items">
                <li>🛞 <strong>Online Tyre Ordering:</strong> Complete online system vs competitors' phone-only approach</li>
                <li>🇬🇧 <strong>UK-Optimized:</strong> British date format and local focus</li>
                <li>🏠 <strong>Southampton Experts:</strong> Local knowledge and personal service</li>
                <li>💳 <strong>Payment Excellence:</strong> Multiple secure options, no integration issues</li>
                <li>📱 <strong>Mobile First:</strong> Touch-optimized for modern devices</li>
                <li>⚡ <strong>Reliable Access:</strong> Always available, no blocking issues</li>
                <li>🎯 <strong>Competitive Pricing:</strong> Great value with superior service</li>
                <li>✅ <strong>Full Service Range:</strong> MOT, servicing, tyres, and more</li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="benefits-cta">
            <p><strong>Ready to experience the Blue Motors difference?</strong></p>
            <a href="/book-service" class="btn-experience">
                📅 Book Your Service Online
            </a>
        </div>
    </div>
    
    <style>
    .bms-why-choose-us {
        margin: 20px 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .why-choose-header {
        text-align: center;
        margin-bottom: 32px;
    }
    
    .why-choose-header h3 {
        color: #1e3a8a;
        margin-bottom: 8px;
        font-size: 24px;
    }
    
    .why-choose-header p {
        color: #6b7280;
        font-size: 16px;
        margin: 0;
    }
    
    .benefits-grid {
        display: grid;
        gap: 24px;
        margin: 24px 0;
    }
    
    .benefit-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .benefit-card:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
    
    .benefit-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }
    
    .benefit-card h4 {
        color: #1e3a8a;
        margin: 0 0 12px 0;
        font-size: 18px;
    }
    
    .benefit-card p {
        color: #6b7280;
        margin: 0;
        line-height: 1.5;
    }
    
    .benefits-list {
        margin: 24px 0;
    }
    
    .benefits-items {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .benefits-items li {
        margin-bottom: 12px;
        padding: 16px;
        background: #f8f9fa;
        border-left: 4px solid #3b82f6;
        border-radius: 6px;
        font-size: 16px;
    }
    
    .benefits-cta {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 24px;
        border-radius: 12px;
        text-align: center;
        margin-top: 32px;
    }
    
    .benefits-cta p {
        margin: 0 0 16px 0;
        font-size: 18px;
    }
    
    .btn-experience {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: white;
        color: #16a34a;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .btn-experience:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        .benefits-grid {
            grid-template-columns: 1fr !important;
        }
        
        .benefit-card {
            padding: 20px;
        }
        
        .benefit-icon {
            font-size: 36px;
        }
    }
    </style>
    
    <?php
    return ob_get_clean();
}

/**
 * Price calculator shortcode
 */
function bms_price_calculator_shortcode($atts) {
    ob_start();
    ?>
    <div class="bms-price-calculator">
        <h3>💰 Service Price Calculator</h3>
        <p>Get an instant quote for your vehicle service</p>
        
        <form class="price-calc-form">
            <div class="calc-group">
                <label for="calc-service">Service Type:</label>
                <select id="calc-service" name="service_type">
                    <option value="mot_test">MOT Test - £40</option>
                    <option value="interim_service">Interim Service - From £89</option>
                    <option value="full_service">Full Service - From £149</option>
                </select>
            </div>
            
            <div class="calc-group">
                <label for="calc-engine">Engine Size:</label>
                <select id="calc-engine" name="engine_size">
                    <option value="1000">Up to 1000cc</option>
                    <option value="1600">Up to 1600cc</option>
                    <option value="2000">Up to 2000cc</option>
                    <option value="3500">Up to 3500cc</option>
                </select>
            </div>
            
            <div class="calc-result">
                <h4>Estimated Price: <span id="calc-price">£40.00</span></h4>
                <p class="calc-note">Final price confirmed during booking</p>
            </div>
            
            <button type="button" class="btn-book-calc">
                📅 Book This Service
            </button>
        </form>
        
        <div class="calc-advantage">
            <p><strong>🎯 industry leaders Difference:</strong> They make you call for quotes - we show prices upfront!</p>
        </div>
    </div>
    
    <style>
    .bms-price-calculator {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        max-width: 400px;
        margin: 20px 0;
    }
    
    .calc-group {
        margin-bottom: 16px;
    }
    
    .calc-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #374151;
    }
    
    .calc-group select {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 16px;
    }
    
    .calc-result {
        background: #eff6ff;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        margin: 20px 0;
    }
    
    .calc-result h4 {
        margin: 0 0 8px 0;
        color: #1e3a8a;
        font-size: 20px;
    }
    
    #calc-price {
        color: #059669;
        font-size: 24px;
    }
    
    .calc-note {
        margin: 0;
        font-size: 14px;
        color: #6b7280;
    }
    
    .btn-book-calc {
        width: 100%;
        background: #3b82f6;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .btn-book-calc:hover {
        background: #2563eb;
    }
    
    .calc-advantage {
        background: #dcfce7;
        border: 2px solid #22c55e;
        border-radius: 8px;
        padding: 12px;
        margin-top: 16px;
        text-align: center;
        font-size: 14px;
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Booking status checker shortcode
 */
function bms_booking_status_shortcode($atts) {
    ob_start();
    ?>
    <div class="bms-booking-status">
        <h3>📋 Check Your Booking Status</h3>
        <p>Enter your booking reference to check the status</p>
        
        <form class="status-check-form">
            <div class="status-group">
                <label for="booking-ref">Booking Reference:</label>
                <input type="text" id="booking-ref" name="booking_reference" 
                       placeholder="e.g. WEB-ABC123" required>
            </div>
            
            <button type="button" class="btn-check-status">
                🔍 Check Status
            </button>
        </form>
        
        <div id="status-result" class="status-result" style="display: none;">
            <!-- Results will be displayed here -->
        </div>
        
        <div class="status-help">
            <h4>Need Help?</h4>
            <p>📞 Call us: <a href="tel:02380000000">023 8000 0000</a></p>
            <p>✉️ Email: <a href="mailto:southampton@bluemotors.co.uk">southampton@bluemotors.co.uk</a></p>
        </div>
    </div>
    
    <style>
    .bms-booking-status {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        max-width: 500px;
        margin: 20px 0;
    }
    
    .status-group {
        margin-bottom: 16px;
    }
    
    .status-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #374151;
    }
    
    .status-group input {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 16px;
        text-transform: uppercase;
    }
    
    .btn-check-status {
        background: #3b82f6;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .btn-check-status:hover {
        background: #2563eb;
    }
    
    .status-result {
        background: #f0f9ff;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 16px;
        margin: 20px 0;
    }
    
    .status-help {
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    
    .status-help h4 {
        margin: 0 0 8px 0;
        color: #374151;
    }
    
    .status-help p {
        margin: 4px 0;
    }
    
    .status-help a {
        color: #3b82f6;
        text-decoration: none;
    }
    
    .status-help a:hover {
        text-decoration: underline;
    }
    </style>
    <?php
    return ob_get_clean();
}
