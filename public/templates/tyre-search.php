<!-- Tyre Search Interface - Blue Motors Southampton -->
<!-- Phase 2: Tyre Services Implementation -->

<div class="bms-tyre-search-container">
    <!-- Service Header -->
    <div class="bms-competitive-header">
        <h2>🛞 Professional Tyre Services</h2>
        <div class="competitive-message">
            <p><strong>🎯 Convenient Service:</strong> Order tyres online with our easy booking system.</p>
            <p>Complete your tyre order in minutes with transparent pricing.</p>
        </div>
    </div>
    
    <!-- Search Methods -->
    <div class="bms-search-methods">
        <div class="search-method active" data-method="registration">
            <h3>🚗 Search by Vehicle Registration (Recommended)</h3>
            <div class="method-description">
                <p>Enter your vehicle registration and we'll find the perfect tyres automatically</p>
            </div>
            
            <div class="reg-search-form">
                <div class="input-group">
                    <div class="reg-input-container">
                        <label for="bms-vehicle-reg" class="reg-input-label">ENTER REG</label>
                        <input type="text" 
                               id="bms-vehicle-reg" 
                               placeholder="e.g. AB12 CDE" 
                               class="reg-input"
                               maxlength="8" />
                    </div>
                    <button type="button" id="btn-search-tyres-by-reg" class="btn-primary">
                        <span class="btn-icon">🔍</span>
                        Find My Tyres
                    </button>
                </div>
                <p class="help-text">We'll automatically find tyres that fit your vehicle</p>
            </div>
        </div>
        
        <div class="search-method" data-method="size">
            <h3>📏 Search by Tyre Size</h3>
            <div class="method-description">
                <p>Know your tyre size? Search directly</p>
            </div>
            
            <div class="size-search-form">
                <div class="size-inputs">
                    <div class="size-group">
                        <label>Width</label>
                        <select id="tyre-width">
                            <option value="">Width</option>
                            <option value="175">175</option>
                            <option value="185">185</option>
                            <option value="195">195</option>
                            <option value="205">205</option>
                            <option value="215">215</option>
                            <option value="225">225</option>
                            <option value="235">235</option>
                            <option value="245">245</option>
                        </select>
                    </div>
                    <span class="separator">/</span>
                    <div class="size-group">
                        <label>Profile</label>
                        <select id="tyre-profile">
                            <option value="">Profile</option>
                            <option value="40">40</option>
                            <option value="45">45</option>
                            <option value="50">50</option>
                            <option value="55">55</option>
                            <option value="60">60</option>
                            <option value="65">65</option>
                            <option value="70">70</option>
                        </select>
                    </div>
                    <span class="separator">R</span>
                    <div class="size-group">
                        <label>Rim</label>
                        <select id="tyre-rim">
                            <option value="">Rim</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                            <option value="16">16</option>
                            <option value="17">17</option>
                            <option value="18">18</option>
                            <option value="19">19</option>
                            <option value="20">20</option>
                        </select>
                    </div>
                </div>
                <button type="button" id="btn-search-tyres-by-size" class="btn-primary">
                    Search Tyres
                </button>
                <p class="help-text">Find your tyre size on the sidewall of your current tyres</p>
            </div>
        </div>
        
        <div class="search-method" data-method="popular">
            <h3>⭐ Popular Sizes</h3>
            <div class="method-description">
                <p>Quick access to the most common tyre sizes</p>
            </div>
            
            <div class="popular-sizes">
                <button class="size-button" data-size="175/65R14">175/65R14<small>Small cars</small></button>
                <button class="size-button" data-size="185/60R15">185/60R15<small>Compact cars</small></button>
                <button class="size-button" data-size="195/65R15">195/65R15<small>Medium cars</small></button>
                <button class="size-button" data-size="205/55R16">205/55R16<small>Family cars</small></button>
                <button class="size-button" data-size="215/60R16">215/60R16<small>Larger cars</small></button>
                <button class="size-button" data-size="225/50R17">225/50R17<small>Executive cars</small></button>
            </div>
        </div>
    </div>
    
    <!-- Vehicle Information Display -->
    <div id="vehicle-info-display" class="vehicle-info" style="display: none;">
        <h4>✅ Vehicle Found</h4>
        <div class="vehicle-details"></div>
    </div>
    
    <!-- Filter Section -->
    <div id="tyre-filters" class="tyre-filters" style="display: none;">
        <h4>🔧 Filter Results</h4>
        <div class="filter-row">
            <div class="filter-group">
                <label>Brand Tier:</label>
                <select id="filter-brand-tier">
                    <option value="">All Brands</option>
                    <option value="premium">Premium (Michelin, Continental)</option>
                    <option value="mid-range">Mid-Range (Goodyear, Pirelli)</option>
                    <option value="budget">Budget (Avon, Falken)</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Sort by:</label>
                <select id="filter-sort">
                    <option value="price ASC">Price: Low to High</option>
                    <option value="price DESC">Price: High to Low</option>
                    <option value="brand ASC">Brand A-Z</option>
                    <option value="brand_tier ASC">Quality Rating</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Season:</label>
                <select id="filter-season">
                    <option value="">All Seasons</option>
                    <option value="summer">Summer</option>
                    <option value="winter">Winter</option>
                    <option value="all-season">All Season</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Loading State -->
    <div id="tyre-loading" class="loading-state" style="display: none;">
        <div class="loading-spinner"></div>
        <p class="loading-text">Searching for your perfect tyres...</p>
    </div>
    
    <!-- Results Display -->
    <div id="tyre-results" class="tyre-results" style="display: none;">
        <div class="results-header">
            <h4 id="results-title">Available Tyres</h4>
            <div class="results-count">
                <span id="results-count-text">0 tyres found</span>
            </div>
        </div>
        
        <div id="tyre-results-grid" class="tyre-grid">
            <!-- Results will be populated here by JavaScript -->
        </div>
    </div>
    
    <!-- No Results State -->
    <div id="no-results" class="no-results" style="display: none;">
        <div class="no-results-icon">🔍</div>
        <h4>No Tyres Found</h4>
        <p>We couldn't find any tyres matching your search criteria.</p>
        <div class="no-results-actions">
            <button type="button" class="btn-secondary" onclick="bmsResetSearch()">Try Different Search</button>
            <a href="tel:02380000000" class="btn-primary">Call Us: 023 8000 0000</a>
        </div>
    </div>
    
    <!-- Selected Tyre Summary -->
    <div id="tyre-selection" class="tyre-selection" style="display: none;">
        <h4>🎉 Tyre Selected</h4>
        <div class="selection-summary">
            <!-- Selection details will be populated by JavaScript -->
        </div>
        
        <div class="service-advantages">
            <h5>🎯 Professional Service Benefits</h5>
            <p>You've successfully selected your tyres with our convenient online system!</p>
            <ul>
                <li>✅ No phone calls required</li>
                <li>✅ Instant online ordering</li>
                <li>✅ Transparent pricing</li>
                <li>✅ Immediate confirmation</li>
            </ul>
        </div>
        
        <div class="next-steps">
            <h5>📅 Next: Choose Fitting Appointment</h5>
            <button type="button" id="btn-continue-to-booking" class="btn-primary btn-large">
                Book Fitting Appointment
            </button>
        </div>
    </div>
    
    <!-- Fitting Appointment Section -->
    <div id="fitting-appointment" class="fitting-appointment" style="display: none;">
        <h4>📅 Choose Fitting Appointment</h4>
        <p class="mobile-friendly-note">
            <strong>📱 Mobile-Friendly:</strong> Tap the fields below to open easy-to-use date and time selectors
        </p>
        
        <div class="appointment-form">
            <!-- Enhanced Date & Time Selection Section -->
            <div class="form-row">
                <div class="form-group">
                    <label for="fitting-date">
                        📅 Preferred Date:
                        <span class="field-hint">Click to open enhanced calendar</span>
                    </label>
                    <div class="date-picker-wrapper">
                        <input type="text" 
                               id="fitting-date" 
                               class="form-control date-picker-input" 
                               placeholder="Click to select your preferred date" 
                               autocomplete="off" />
                        <span class="calendar-icon">📅</span>
                        <!-- Calendar popup container -->
                        <div id="fitting-calendar-popup" class="calendar-popup" style="display: none;"></div>
                    </div>
                    <!-- Feedback element for date picker -->
                    <div id="date-picker-feedback" style="display: none;"></div>
                    <div class="field-help">
                        <small>📝 Professional appointments available Monday-Saturday (2-30 days ahead)</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="fitting-time">
                        ⏰ Available Times:
                        <span class="field-hint">Select date first</span>
                    </label>
                    <select id="fitting-time" class="enhanced-time-select" disabled>
                        <option value="">Select date first</option>
                    </select>
                    <div class="field-help">
                        <small>🕐 Times shown are confirmed available slots</small>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer-name">Your Name: *</label>
                    <input type="text" id="customer-name" required />
                </div>
                
                <div class="form-group">
                    <label for="customer-email">Email Address: *</label>
                    <input type="email" id="customer-email" required />
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer-phone">Phone Number: *</label>
                    <input type="tel" id="customer-phone" required />
                </div>
                
                <div class="form-group">
                    <label for="special-requirements">Special Requirements (Optional):</label>
                    <textarea id="special-requirements" 
                              rows="3" 
                              placeholder="Any special instructions or requirements..."></textarea>
                </div>
            </div>
        </div>
        
        <div class="appointment-summary">
            <h5>📋 Appointment Summary</h5>
            <div id="appointment-summary-content">
                <div class="summary-placeholder">
                    <p>Please select date and time to see appointment summary</p>
                </div>
            </div>
        </div>
        
        <div class="mobile-booking-tips">
            <h6>💡 Quick Tips:</h6>
            <ul>
                <li>✅ Use the popup calendar for easy date selection</li>
                <li>⏰ Time slots show real availability</li>
                <li>📱 All fields are optimized for mobile devices</li>
                <li>📧 You'll receive confirmation via email</li>
            </ul>
        </div>
        
        <div class="booking-actions">
            <button type="button" id="btn-confirm-booking" class="btn-primary btn-large">
                📅 Confirm Tyre Fitting Booking
            </button>
            <button type="button" id="btn-back-to-selection" class="btn-secondary">
                ← Back to Tyre Selection
            </button>
        </div>
    </div>
    
    <!-- Success State -->
    <div id="booking-success" class="booking-success" style="display: none;">
        <div class="success-icon">🎉</div>
        <h3>Booking Confirmed!</h3>
        <div class="success-details">
            <!-- Success details will be populated by JavaScript -->
        </div>
        
        <div class="service-celebration">
            <h4>🏆 Professional Service Complete!</h4>
            <p>Your tyre fitting appointment has been successfully booked with our convenient online system!</p>
            <div class="advantages-achieved">
                <div class="advantage">✅ No phone queues</div>
                <div class="advantage">✅ Instant confirmation</div>
                <div class="advantage">✅ Transparent pricing</div>
                <div class="advantage">✅ Professional service</div>
            </div>
        </div>
        
        <div class="next-actions">
            <a href="mailto:southampton@bluemotors.co.uk" class="btn-secondary">
                📧 Email Us
            </a>
            <a href="tel:02380000000" class="btn-secondary">
                📞 Call Us
            </a>
            <button type="button" onclick="window.location.reload()" class="btn-primary">
                Book Another Service
            </button>
        </div>
    </div>
</div>

<!-- Calendar Styles -->
<style>
/* Date picker styles */
.date-picker-wrapper {
    position: relative;
}

.date-picker-input {
    cursor: pointer;
    background: white;
    padding: 12px 40px 12px 16px !important;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    width: 100%;
    box-sizing: border-box;
}

.date-picker-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.calendar-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    cursor: pointer;
    pointer-events: auto;
    z-index: 2;
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.calendar-icon:hover {
    background-color: rgba(59, 130, 246, 0.1);
}

.calendar-popup {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    margin-top: 4px;
    padding: 16px;
    min-width: 300px;
}

.calendar-popup.visible {
    display: block !important;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.cal-nav {
    background: #3b82f6;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cal-nav:hover {
    background: #2563eb;
}

.cal-month-year {
    font-weight: 600;
    color: #1e3a8a;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.cal-day-header {
    text-align: center;
    font-weight: 600;
    font-size: 12px;
    color: #6b7280;
    padding: 4px;
}

.cal-day {
    text-align: center;
    padding: 8px 4px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.2s ease;
}

.cal-day:hover:not(.disabled):not(.other-month) {
    background: #e0e7ff;
}

.cal-day.today {
    background: #fef3c7;
    font-weight: 600;
}

.cal-day.selected {
    background: #3b82f6;
    color: white;
}

.cal-day.disabled {
    color: #d1d5db;
    cursor: not-allowed;
}

.cal-day.other-month {
    color: #e5e7eb;
    cursor: default;
}

/* Form styling improvements */
.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
}

.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.field-hint {
    font-size: 12px;
    color: #6b7280;
    font-weight: normal;
    margin-left: 8px;
}

.field-help {
    margin-top: 4px;
}

.field-help small {
    color: #6b7280;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .calendar-popup {
        left: -20px;
        right: -20px;
        margin-left: 0;
        margin-right: 0;
    }
    
    .cal-day {
        padding: 12px 4px;
        font-size: 16px;
    }
    
    .calendar-days {
        gap: 8px;
    }
}
</style>

<!-- Tyre Card Template (Hidden) -->
<template id="tyre-card-template">
    <div class="tyre-card" data-tyre-id="">
        <div class="tyre-header">
            <div class="tyre-brand"></div>
            <div class="tyre-tier"></div>
        </div>
        
        <div class="tyre-model"></div>
        <div class="tyre-size"></div>
        
        <div class="tyre-specs">
            <div class="spec-item">
                <span class="spec-label">Speed:</span>
                <span class="spec-value speed-rating"></span>
            </div>
            <div class="spec-item">
                <span class="spec-label">Load:</span>
                <span class="spec-value load-index"></span>
            </div>
        </div>
        
        <div class="eu-label">
            <div class="rating-item">
                <span class="rating-label">Fuel:</span>
                <span class="rating-value fuel-rating"></span>
            </div>
            <div class="rating-item">
                <span class="rating-label">Wet:</span>
                <span class="rating-value wet-rating"></span>
            </div>
            <div class="rating-item">
                <span class="rating-label">Noise:</span>
                <span class="rating-value noise-rating"></span>
            </div>
        </div>
        
        <div class="tyre-pricing">
            <div class="price-per-tyre">
                <span class="price-label">Per tyre:</span>
                <span class="price-value tyre-price"></span>
            </div>
            <div class="fitting-price">
                <span class="price-label">Fitting:</span>
                <span class="price-value fitting-cost"></span>
            </div>
            <div class="total-price">
                <span class="price-label">Total inc. VAT:</span>
                <span class="price-value total-inc-vat"></span>
            </div>
        </div>
        
        <div class="quantity-selector">
            <label>How many tyres?</label>
            <select class="tyre-quantity">
                <option value="1">1 tyre</option>
                <option value="2">2 tyres (pair)</option>
                <option value="4" selected>4 tyres (full set)</option>
            </select>
        </div>
        
        <div class="tyre-actions">
            <button type="button" class="btn-select-tyre btn-primary">
                Select
            </button>
        </div>
    </div>
</template>