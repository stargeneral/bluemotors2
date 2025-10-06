<!-- Tyre Search Interface - Formula One Autocentres Style -->
<!-- Blue Motors Southampton -->

<div class="bms-tyre-search-container">
    <!-- Main Header - Blue Section -->
    <div class="bms-main-header">
        <h1>BUY CAR TYRES ONLINE</h1>
        <div class="subtitle">BUY TYRES ONLINE OR AT YOUR LOCAL BRANCH. SAME DAY FITTING AVAILABLE</div>
        <div class="description">
            Blue Motors Southampton are the leading family owned tyre dealers and fitters in the UK. We stock an extensive range of premium 
            brand and budget car tyres, all available to buy online and in store. At Blue Motors Southampton, you'll find tyres covered by our 
            <span class="price-promise">price promise</span> - ensuring you won't find a better price anywhere else. Search by registration, tyre size or from our list of 
            <span class="tyre-manufacturers">tyre manufacturers</span>, all tyres are available to be fitted locally at your nearest <span class="formula-one-centre">Blue Motors Southampton centre</span>.
        </div>
    </div>

    <!-- Search Tabs -->
    <div class="bms-search-tabs">
        <button class="search-tab active" data-method="registration">SEARCH BY REGISTRATION</button>
        <button class="search-tab" data-method="size">SEARCH BY TYRE SIZE</button>
    </div>

    <!-- Search Form -->
    <div class="bms-search-form">
        <!-- Registration Search -->
        <div class="search-content active" id="registration-search">
            <div class="reg-search-row">
                <div class="reg-input-wrapper">
                    <input type="text" id="bms-vehicle-reg" class="reg-input" placeholder="ENTER REG" maxlength="8">
                </div>
                <select class="brand-dropdown">
                    <option>Any Tyre Brand</option>
                    <option>Michelin</option>
                    <option>Bridgestone</option>
                    <option>Continental</option>
                    <option>Pirelli</option>
                    <option>Goodyear</option>
                </select>
                <button type="button" id="btn-search-tyres-by-reg" class="search-btn">GO ➤</button>
            </div>
        </div>

        <!-- Size Search -->
        <div class="search-content" id="size-search">
            <div class="size-search-row">
                <div class="size-group">
                    <label>Width</label>
                    <select id="tyre-width" class="size-select">
                        <option value="">Width</option>
                        <option value="175">175</option>
                        <option value="185">185</option>
                        <option value="195">195</option>
                        <option value="205">205</option>
                        <option value="215">215</option>
                        <option value="225">225</option>
                        <option value="235">235</option>
                        <option value="245">245</option>
                        <option value="255">255</option>
                    </select>
                </div>
                <div class="size-separator">/</div>
                <div class="size-group">
                    <label>Profile</label>
                    <select id="tyre-profile" class="size-select">
                        <option value="">Profile</option>
                        <option value="45">45</option>
                        <option value="50">50</option>
                        <option value="55">55</option>
                        <option value="60">60</option>
                        <option value="65">65</option>
                        <option value="70">70</option>
                    </select>
                </div>
                <div class="size-separator">R</div>
                <div class="size-group">
                    <label>Rim</label>
                    <select id="tyre-rim" class="size-select">
                        <option value="">Rim</option>
                        <option value="15">15</option>
                        <option value="16">16</option>
                        <option value="17">17</option>
                        <option value="18">18</option>
                        <option value="19">19</option>
                        <option value="20">20</option>
                    </select>
                </div>
                <button type="button" id="btn-search-tyres-by-size" class="search-btn">GO ➤</button>
            </div>
        </div>
    </div>

    <!-- Process Steps Section -->
    <div class="bms-process-section">
        <div class="process-header">
            <h2>BUY TYRES ONLINE</h2>
            <div class="process-description">
                Buy tyres online at Blue Motors Southampton and have them fitted at your local branch, same or next day from only £29. With 
                over 130 centres and 50 years of experience, we make buying tyres easy - just enter your vehicle registration or tyre size and 
                choose your preferred tyre. All prices include fitting, valves, standard balancing, casing disposal and VAT. From there, select 
                your nearest centre, book your fitting and pay for your tyres online.
            </div>
        </div>

        <div class="process-steps">
            <div class="process-step">
                <div class="step-icon">💻</div>
                <div class="step-content">
                    <h3><span class="step-number">STEP 1</span> Choose your tyres online</h3>
                    <p>Enter your registration or tyre size in to our tyre search tool to view all available tyres relevant to your car. From budget to premium brands, we make sure there is an option for all budgets and needs.</p>
                </div>
            </div>

            <div class="process-step">
                <div class="step-icon">📅</div>
                <div class="step-content">
                    <h3><span class="step-number">STEP 2</span> Select your tyre fitting appointment</h3>
                    <p>Once you have added your tyres to the basket, choose an appointment time at your nearest Blue Motors Southampton. We have a range of same and next day fittings to suit your requirements.</p>
                </div>
            </div>

            <div class="process-step">
                <div class="step-icon">💳</div>
                <div class="step-content">
                    <h3><span class="step-number">STEP 3</span> Secure payment online</h3>
                    <p>After choosing your tyres and fitting appointment, simply pay online using our secure payment system. We accept all major credit and debit cards for your convenience.</p>
                </div>
            </div>

            <div class="process-step">
                <div class="step-icon">🔧</div>
                <div class="step-content">
                    <h3><span class="step-number">STEP 4</span> Attend your local tyre fitting appointment</h3>
                    <p>After paying for your tyres online, simply attend your local tyre fitting appointment. Our experienced technicians will fit your new tyres quickly and professionally.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Information Display -->
    <div id="vehicle-info-display" class="vehicle-info-display" style="display: none;">
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
    <div id="tyre-loading" class="tyre-loading" style="display: none;">
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
        
        <div id="tyre-results-grid" class="tyre-results-grid">
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
        
        <div class="appointment-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="fitting-date">Preferred Date:</label>
                    <input type="date" id="fitting-date" min="" max="" />
                </div>
                
                <div class="form-group">
                    <label for="fitting-time">Available Times:</label>
                    <select id="fitting-time">
                        <option value="">Select date first</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer-name">Your Name:</label>
                    <input type="text" id="customer-name" required />
                </div>
                
                <div class="form-group">
                    <label for="customer-email">Email Address:</label>
                    <input type="email" id="customer-email" required />
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer-phone">Phone Number:</label>
                    <input type="tel" id="customer-phone" required />
                </div>
                
                <div class="form-group">
                    <label for="special-requirements">Special Requirements (Optional):</label>
                    <textarea id="special-requirements" rows="3" placeholder="Any special instructions or requirements..."></textarea>
                </div>
            </div>
        </div>
        
        <div class="appointment-summary">
            <h5>📋 Appointment Summary</h5>
            <div id="appointment-summary-content">
                <!-- Summary will be populated by JavaScript -->
            </div>
        </div>
        
        <div class="booking-actions">
            <button type="button" id="btn-confirm-booking" class="btn-primary btn-large">
                Confirm Tyre Fitting Booking
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
            <div class="price-row">
                <span class="price-label">Per tyre:</span>
                <span class="price-value tyre-price"></span>
            </div>
            <div class="price-row">
                <span class="price-label">Fitting:</span>
                <span class="price-value fitting-cost"></span>
            </div>
            <div class="price-row total-row">
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
            <button type="button" class="btn-select-tyre">
                Select This Tyre
            </button>
        </div>
    </div>
</template>

<script>
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.search-tab');
    const contents = document.querySelectorAll('.search-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const method = this.dataset.method;
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Update active content
            contents.forEach(c => c.classList.remove('active'));
            document.getElementById(method + '-search').classList.add('active');
        });
    });
});
</script>
