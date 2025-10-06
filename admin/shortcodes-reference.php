<?php
/**
 * Shortcodes Reference Admin Page for Blue Motors Southampton
 * 
 * Displays all available shortcodes with descriptions and examples
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function bms_shortcodes_reference_page() {
    ?>
    <div class="wrap">
        <h1>🔖 Blue Motors Southampton - Shortcodes Reference</h1>
        <p class="description">Use these shortcodes to add Blue Motors functionality to your pages and posts. Click the copy button to copy the shortcode to your clipboard.</p>
        
        <div class="bms-shortcodes-container">
            
            <!-- Main Booking System -->
            <div class="bms-shortcode-section">
                <h2>🚗 Main Booking System</h2>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_booking_form]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_booking_form]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Complete service booking form with vehicle lookup, service selection, and payment processing.</p>
                    <p><strong>Best for:</strong> Main booking page, service pages</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_booking_form]</code>
                    </div>
                    <div class="shortcode-parameters">
                        <strong>Optional Parameters:</strong>
                        <ul>
                            <li><code>service</code> - Pre-select a service (mot_test, full_service, interim_service)</li>
                            <li><code>theme</code> - Color theme (blue, green, red, dark)</li>
                        </ul>
                        <div class="parameter-examples">
                            <code>[bms_booking_form service="mot_test"]</code><br>
                            <code>[bms_booking_form theme="dark"]</code>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Vehicle Lookup -->
            <div class="bms-shortcode-section">
                <h2>🔍 Vehicle Lookup</h2>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_vehicle_lookup]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_vehicle_lookup]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Standalone vehicle registration lookup tool with DVLA integration.</p>
                    <p><strong>Best for:</strong> Information pages, tools section</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_vehicle_lookup]</code>
                    </div>
                    <div class="shortcode-parameters">
                        <strong>Optional Parameters:</strong>
                        <ul>
                            <li><code>placeholder</code> - Custom placeholder text</li>
                            <li><code>button_text</code> - Custom button text</li>
                            <li><code>show_mot</code> - Show MOT history (true/false)</li>
                        </ul>
                        <div class="parameter-examples">
                            <code>[bms_vehicle_lookup placeholder="Enter your reg e.g. AB12 CDE"]</code><br>
                            <code>[bms_vehicle_lookup button_text="Check Vehicle" show_mot="true"]</code>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tyre System -->
            <div class="bms-shortcode-section">
                <h2>🛞 Tyre Ordering System</h2>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_tyre_search]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_tyre_search]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Complete tyre search and ordering system - our service excellence over other automotive services!</p>
                    <p><strong>Best for:</strong> Tyre pages, main services</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_tyre_search]</code>
                    </div>
                    <div class="shortcode-parameters">
                        <strong>Optional Parameters:</strong>
                        <ul>
                            <li><code>default_search</code> - Default search method (reg, size, brand)</li>
                            <li><code>show_brands</code> - Comma-separated list of brands to display</li>
                            <li><code>theme</code> - Visual theme</li>
                        </ul>
                        <div class="parameter-examples">
                            <code>[bms_tyre_search default_search="reg"]</code><br>
                            <code>[bms_tyre_search show_brands="Michelin,Continental,Bridgestone"]</code>
                        </div>
                    </div>
                </div>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_tyre_finder]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_tyre_finder]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Simple tyre finder widget for sidebars or content areas.</p>
                    <p><strong>Best for:</strong> Sidebars, widget areas</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_tyre_finder]</code>
                    </div>
                </div>
            </div>
            
            <!-- Service Information -->
            <div class="bms-shortcode-section">
                <h2>⚙️ Service Information</h2>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_service_cards]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_service_cards]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Display service cards with pricing and booking links.</p>
                    <p><strong>Best for:</strong> Homepage, services overview pages</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_service_cards]</code>
                    </div>
                    <div class="shortcode-parameters">
                        <strong>Optional Parameters:</strong>
                        <ul>
                            <li><code>category</code> - Filter by category (testing, servicing, tyres, climate)</li>
                            <li><code>columns</code> - Number of columns (2, 3, 4)</li>
                            <li><code>show_prices</code> - Show pricing (true/false)</li>
                        </ul>
                        <div class="parameter-examples">
                            <code>[bms_service_cards category="servicing" columns="3"]</code><br>
                            <code>[bms_service_cards show_prices="false"]</code>
                        </div>
                    </div>
                </div>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_service_list]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_service_list]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Simple list of services with prices.</p>
                    <p><strong>Best for:</strong> Service menus, pricing pages</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_service_list]</code>
                    </div>
                </div>
            </div>
            
            <!-- Business Information -->
            <div class="bms-shortcode-section">
                <h2>📍 Business Information</h2>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_location_info]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_location_info]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Display business location, hours, and contact information.</p>
                    <p><strong>Best for:</strong> Contact pages, footer areas</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_location_info]</code>
                    </div>
                    <div class="shortcode-parameters">
                        <strong>Optional Parameters:</strong>
                        <ul>
                            <li><code>show_map</code> - Include Google Maps (true/false)</li>
                            <li><code>show_hours</code> - Display opening hours (true/false)</li>
                            <li><code>style</code> - Display style (card, list, minimal)</li>
                        </ul>
                        <div class="parameter-examples">
                            <code>[bms_location_info show_map="true" style="card"]</code><br>
                            <code>[bms_location_info show_hours="false" style="minimal"]</code>
                        </div>
                    </div>
                </div>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_contact_form]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_contact_form]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Contact form with garage-specific fields.</p>
                    <p><strong>Best for:</strong> Contact pages, enquiry sections</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_contact_form]</code>
                    </div>
                </div>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_opening_hours]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_opening_hours]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Display opening hours in a formatted table.</p>
                    <p><strong>Best for:</strong> Sidebars, contact information</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_opening_hours]</code>
                    </div>
                </div>
            </div>
            
            <!-- Professional Advantages -->
            <div class="bms-shortcode-section">
                <h2>🎯 Professional Advantages</h2>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_vs_f1]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_vs_f1]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Display comparison with other automotive services showing our advantages.</p>
                    <p><strong>Best for:</strong> Homepage, about pages</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_vs_f1]</code>
                    </div>
                    <div class="competitive-note">
                        <strong>🎯 Our Advantages:</strong> Online tyre ordering, UK date format, better mobile experience, local focus
                    </div>
                </div>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_why_choose_us]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_why_choose_us]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Highlight key benefits and professional features.</p>
                    <p><strong>Best for:</strong> Landing pages, conversion areas</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_why_choose_us]</code>
                    </div>
                </div>
            </div>
            
            <!-- Customer Tools -->
            <div class="bms-shortcode-section">
                <h2>👤 Customer Tools</h2>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_price_calculator]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_price_calculator]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Interactive price calculator for services.</p>
                    <p><strong>Best for:</strong> Pricing pages, service information</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_price_calculator]</code>
                    </div>
                </div>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_booking_status]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_booking_status]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Allow customers to check their booking status.</p>
                    <p><strong>Best for:</strong> Customer service pages</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_booking_status]</code>
                    </div>
                </div>
            </div>
            
            <!-- Phase 4: Advanced Features -->
            <div class="bms-shortcode-section">
                <h2>🚀 Advanced Features (Phase 4)</h2>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_customer_history]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_customer_history]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> Display comprehensive customer service history with AI-powered recommendations and loyalty status - a feature other automotive services doesn't offer!</p>
                    <p><strong>Best for:</strong> Customer account pages, member areas</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_customer_history]</code>
                    </div>
                    <div class="shortcode-parameters">
                        <strong>Optional Parameters:</strong>
                        <ul>
                            <li><code>email</code> - Customer email (defaults to logged-in user)</li>
                            <li><code>show_recommendations</code> - Show AI recommendations (true/false)</li>
                            <li><code>show_loyalty</code> - Show loyalty status (true/false)</li>
                        </ul>
                        <div class="parameter-examples">
                            <code>[bms_customer_history email="customer@email.com"]</code><br>
                            <code>[bms_customer_history show_recommendations="true" show_loyalty="true"]</code>
                        </div>
                    </div>
                    <div class="competitive-note">
                        <strong>🎯 Professional Advantage:</strong> Tracks complete service history, provides personalized recommendations, and rewards loyalty - features other automotive services lacks!
                    </div>
                </div>
                
                <div class="bms-shortcode-card">
                    <div class="shortcode-header">
                        <h3>[bms_smart_scheduler]</h3>
                        <button class="copy-shortcode-btn" data-shortcode="[bms_smart_scheduler]">Copy</button>
                    </div>
                    <p><strong>Description:</strong> AI-powered appointment scheduling that suggests optimal booking times based on garage capacity, customer preferences, and historical data.</p>
                    <p><strong>Best for:</strong> Booking pages, service scheduling areas</p>
                    <div class="shortcode-example">
                        <strong>Example:</strong>
                        <code>[bms_smart_scheduler]</code>
                    </div>
                    <div class="shortcode-parameters">
                        <strong>Optional Parameters:</strong>
                        <ul>
                            <li><code>service_type</code> - Pre-select service type</li>
                            <li><code>show_customer_prefs</code> - Show customer preference matching (true/false)</li>
                            <li><code>max_suggestions</code> - Maximum number of time slots to show (default: 5)</li>
                        </ul>
                        <div class="parameter-examples">
                            <code>[bms_smart_scheduler service_type="mot_test"]</code><br>
                            <code>[bms_smart_scheduler show_customer_prefs="true" max_suggestions="7"]</code>
                        </div>
                    </div>
                    <div class="competitive-note">
                        <strong>🎯 AI Advantage:</strong> Uses machine learning to optimize appointment scheduling - other automotive services has nothing like this!
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Quick Copy Section -->
        <div class="bms-quick-copy-section">
            <h2>🚀 Quick Copy - Essential Shortcodes</h2>
            <p>The most commonly used shortcodes for getting started:</p>
            
            <div class="quick-copy-grid">
                <div class="quick-copy-item">
                    <strong>Main Booking Page:</strong>
                    <code>[bms_booking_form]</code>
                    <button class="copy-shortcode-btn" data-shortcode="[bms_booking_form]">Copy</button>
                </div>
                
                <div class="quick-copy-item">
                    <strong>Tyre Ordering:</strong>
                    <code>[bms_tyre_search]</code>
                    <button class="copy-shortcode-btn" data-shortcode="[bms_tyre_search]">Copy</button>
                </div>
                
                <div class="quick-copy-item">
                    <strong>Service Cards:</strong>
                    <code>[bms_service_cards]</code>
                    <button class="copy-shortcode-btn" data-shortcode="[bms_service_cards]">Copy</button>
                </div>
                
                <div class="quick-copy-item">
                    <strong>Location Info:</strong>
                    <code>[bms_location_info]</code>
                    <button class="copy-shortcode-btn" data-shortcode="[bms_location_info]">Copy</button>
                </div>
            </div>
        </div>
        
        <!-- Usage Tips -->
        <div class="bms-usage-tips">
            <h2>💡 Usage Tips</h2>
            <div class="tips-grid">
                <div class="tip-card">
                    <h4>🏠 Homepage Setup</h4>
                    <p>Recommended shortcodes for your homepage:</p>
                    <ul>
                        <li><code>[bms_why_choose_us]</code> - Above the fold</li>
                        <li><code>[bms_service_cards columns="3"]</code> - Services section</li>
                        <li><code>[bms_tyre_finder]</code> - Sidebar widget</li>
                        <li><code>[bms_location_info style="minimal"]</code> - Footer area</li>
                    </ul>
                </div>
                
                <div class="tip-card">
                    <h4>📄 Service Pages</h4>
                    <p>For individual service pages:</p>
                    <ul>
                        <li><code>[bms_booking_form service="mot_test"]</code> - Pre-select service</li>
                        <li><code>[bms_price_calculator]</code> - Show pricing</li>
                        <li><code>[bms_vs_f1]</code> - Service excellence</li>
                    </ul>
                </div>
                
                <div class="tip-card">
                    <h4>🛞 Tyre Pages</h4>
                    <p>For tyre-focused content:</p>
                    <ul>
                        <li><code>[bms_tyre_search]</code> - Main tyre ordering</li>
                        <li><code>[bms_vehicle_lookup]</code> - Quick registration check</li>
                        <li>Highlight that F1 requires phone calls!</li>
                    </ul>
                </div>
                
                <div class="tip-card">
                    <h4>📞 Contact Pages</h4>
                    <p>For contact and location pages:</p>
                    <ul>
                        <li><code>[bms_location_info show_map="true"]</code> - Full contact info</li>
                        <li><code>[bms_contact_form]</code> - Enquiry form</li>
                        <li><code>[bms_opening_hours]</code> - Business hours</li>
                    </ul>
                </div>
            </div>
        </div>
        
    </div>
    
    <style>
    .bms-shortcodes-container {
        margin-top: 20px;
    }
    
    .bms-shortcode-section {
        margin-bottom: 40px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
    }
    
    .bms-shortcode-section h2 {
        margin-top: 0;
        color: #1e3a8a;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 10px;
    }
    
    .bms-shortcode-card {
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .shortcode-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .shortcode-header h3 {
        margin: 0;
        color: #059669;
        font-family: monospace;
        font-size: 16px;
    }
    
    .copy-shortcode-btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 5px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .copy-shortcode-btn:hover {
        background: #2563eb;
    }
    
    .copy-shortcode-btn.copied {
        background: #10b981;
    }
    
    .shortcode-example {
        background: #1f2937;
        color: #f3f4f6;
        padding: 10px;
        border-radius: 4px;
        margin: 10px 0;
        font-family: monospace;
    }
    
    .shortcode-parameters {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #d1d5db;
    }
    
    .parameter-examples {
        background: #f3f4f6;
        padding: 8px;
        border-radius: 4px;
        margin-top: 8px;
        font-family: monospace;
        font-size: 13px;
    }
    
    .competitive-note {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
        font-size: 14px;
    }
    
    .bms-quick-copy-section {
        background: #eff6ff;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 20px;
        margin: 30px 0;
    }
    
    .quick-copy-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    
    .quick-copy-item {
        background: white;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .quick-copy-item code {
        background: #f3f4f6;
        padding: 4px 8px;
        border-radius: 4px;
        font-family: monospace;
        margin: 0 10px;
    }
    
    .bms-usage-tips {
        margin-top: 30px;
    }
    
    .tips-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .tip-card {
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .tip-card h4 {
        margin-top: 0;
        color: #1e3a8a;
    }
    
    .tip-card ul {
        margin: 10px 0;
        padding-left: 20px;
    }
    
    .tip-card code {
        background: #f3f4f6;
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 12px;
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.copy-shortcode-btn');
        
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const shortcode = this.getAttribute('data-shortcode');
                
                // Create temporary textarea to copy text
                const textarea = document.createElement('textarea');
                textarea.value = shortcode;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                
                // Show feedback
                const originalText = this.textContent;
                this.textContent = '✓ Copied!';
                this.classList.add('copied');
                
                setTimeout(() => {
                    this.textContent = originalText;
                    this.classList.remove('copied');
                }, 2000);
            });
        });
    });
    </script>
    
    <?php
}
