# SOLUTION: Hide Contact Information Box

**Date:** October 4, 2025  
**Issue:** Remove/hide the contact information box showing "Book Your Service at Blue Motors Southampton" with address, phone, and email

## Solution Options

### Option 1: CSS Method (RECOMMENDED - Non-Destructive) ✅

**File Created:** `assets/css/hide-contact-info.css`

This CSS file has been created and will hide the contact box once you add it to the WordPress enqueue.

**Step 1:** The CSS file has already been created at:
```
C:\Users\Peter\Local Sites\bluemotorsnew\app\public\wp-content\plugins\blue-motors-southampton\assets\css\hide-contact-info.css
```

**Step 2:** Add the following code to `blue-motors-southampton.php` at line 257 (after the mobile-phase2-enhancements enqueue):

```php
    // Hide Contact Information Box (per user request October 4, 2025)
    wp_enqueue_style(
        'bms-hide-contact-info',
        BMS_PLUGIN_URL . 'assets/css/hide-contact-info.css',
        array('bms-mobile-phase2-enhancements'),
        BMS_VERSION . '.3', // Version bump for cache busting
        'all'
    );
```

**Location to add:** In `blue-motors-southampton.php`, find this section around line 250:
```php
    // Phase 2: Mobile Enhancements - Smart Scheduler, Calendar & Loading States
    wp_enqueue_style(
        'bms-mobile-phase2-enhancements',
        BMS_PLUGIN_URL . 'assets/css/mobile-phase2-enhancements.css',
        array('bms-mobile-critical-fixes'),
        BMS_VERSION . '.2', // Version bump for cache busting
        'all'
    );
    
    // ADD THE NEW ENQUEUE HERE
    
    // Phase 3: Professional Messaging System
    wp_enqueue_style(
        'bms-professional-messaging',
```

### Option 2: PHP Template Method (Permanent Removal)

If you prefer to completely remove the contact information from the template:

**File to Edit:** `public/templates/booking-form.php`

**Current Code (Lines 16-23):**
```php
    <!-- Location Header -->
    <div class="bms-location-header">
        <h2>Book Your Service at Blue Motors Southampton</h2>
        <div class="location-details">
            <p><i class="dashicons dashicons-location"></i> <?php echo BM_LOCATION_ADDRESS; ?></p>
            <p><i class="dashicons dashicons-phone"></i> <?php echo BM_LOCATION_PHONE; ?></p>
            <p><i class="dashicons dashicons-email"></i> <?php echo BM_LOCATION_EMAIL; ?></p>
        </div>
    </div>
```

**Replace with (to hide):**
```php
    <!-- Location Header - HIDDEN per request October 4, 2025 -->
    <?php /* Commented out to hide contact information
    <div class="bms-location-header">
        <h2>Book Your Service at Blue Motors Southampton</h2>
        <div class="location-details">
            <p><i class="dashicons dashicons-location"></i> <?php echo BM_LOCATION_ADDRESS; ?></p>
            <p><i class="dashicons dashicons-phone"></i> <?php echo BM_LOCATION_PHONE; ?></p>
            <p><i class="dashicons dashicons-email"></i> <?php echo BM_LOCATION_EMAIL; ?></p>
        </div>
    </div>
    */ ?>
```

**Or completely remove lines 16-23 from the file.**

## Implementation Steps

### For CSS Method (Recommended):

1. ✅ **CSS file created:** `hide-contact-info.css` (DONE)
2. ⏳ **Edit** `blue-motors-southampton.php` at line ~257
3. ⏳ **Add** the enqueue code shown above
4. ⏳ **Clear** WordPress cache
5. ⏳ **Test** on the booking page

### For PHP Method:

1. ⏳ **Edit** `public/templates/booking-form.php`
2. ⏳ **Comment out or remove** lines 16-23
3. ⏳ **Save** the file
4. ⏳ **Clear** WordPress cache
5. ⏳ **Test** on the booking page

## Testing After Implementation

1. **Clear Cache:**
   - Clear WordPress cache (if using a caching plugin)
   - Clear browser cache (Ctrl+F5)

2. **Verify on Desktop:**
   - Visit: http://bluemotorsnew.local/book-a-service-2/
   - Contact box should be hidden

3. **Verify on Mobile:**
   - Use Chrome DevTools (F12)
   - Toggle device toolbar
   - Select iPhone or Android view
   - Contact box should be hidden

## Rollback Instructions

### If Using CSS Method:
1. Comment out the enqueue lines in `blue-motors-southampton.php`
2. Clear cache
3. Contact box will reappear

### If Using PHP Method:
1. Uncomment the lines in `booking-form.php`
2. Clear cache
3. Contact box will reappear

## Files Involved

| File | Purpose | Action |
|------|---------|--------|
| `assets/css/hide-contact-info.css` | CSS to hide contact box | ✅ Created |
| `blue-motors-southampton.php` | Main plugin file | ⏳ Add enqueue at line ~257 |
| `public/templates/booking-form.php` | Booking form template | ⏳ Optional: Comment lines 16-23 |

## Visual Result

**Before:**
- Purple/blue box with "Book Your Service at Blue Motors Southampton"
- Shows address: 1 Kent St, Northam, Southampton SO14 5SP
- Shows phone: 023 8023 0443
- Shows email: admin@bluemotorsgarage.com

**After:**
- Contact box completely hidden
- Progress steps move up
- More space for booking form
- Cleaner appearance

## Recommendation

Use the **CSS method** as it's:
- ✅ Non-destructive (easy to reverse)
- ✅ Doesn't modify template files
- ✅ Can be toggled on/off easily
- ✅ Survives plugin updates better
- ✅ Already created and ready to use

Just add the enqueue code to `blue-motors-southampton.php` and the contact box will be hidden immediately after clearing cache.

---

**Status:** Solution ready for implementation  
**Risk:** None (CSS-only change or template comment)  
**Time to implement:** 2 minutes  
**Time to rollback:** 30 seconds
