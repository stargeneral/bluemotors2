# Secure Configuration Guide

## Important Security Notice

**DO NOT commit API keys or secrets to version control!**

This plugin requires sensitive API keys that should be stored securely in your WordPress installation's `wp-config.php` file.

## Required API Keys

### 1. Stripe Payment Keys

Add these to your `wp-config.php` file (above the "That's all" comment):

```php
// Blue Motors Southampton - Stripe Configuration
define('BM_STRIPE_PUBLISHABLE_KEY', 'pk_test_your_key_here');
define('BM_STRIPE_SECRET_KEY', 'sk_test_your_key_here');
```

**For Production:**
Replace `pk_test_` and `sk_test_` with your live Stripe keys:
- `pk_live_...` for publishable key
- `sk_live_...` for secret key

### 2. DVLA API Key (Optional)

Add this to your `wp-config.php` file:

```php
// Blue Motors Southampton - DVLA API
define('BM_DVLA_API_KEY', 'your_dvla_api_key_here');
```

**Note:** The plugin has fallback vehicle data if DVLA API is not configured.

### 3. Google Calendar Service Account (Optional)

Place your Google Calendar service account JSON file at:
```
wp-content/plugins/blue-motors-southampton/vendor/service-account-credentials.json
```

**This file is automatically excluded from Git via .gitignore**

## Setting Up wp-config.php

1. Open your WordPress `wp-config.php` file
2. Locate the line that says: `/* That's all, stop editing! Happy publishing. */`
3. Add your configuration constants **above** that line
4. Save the file

### Example wp-config.php addition:

```php
/* Blue Motors Southampton Configuration */
// Stripe Keys (Get yours from https://dashboard.stripe.com/apikeys)
define('BM_STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_PUBLISHABLE_KEY_HERE');
define('BM_STRIPE_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY_HERE');

// DVLA API (Get yours from https://developer-portal.driver-vehicle-licensing.api.gov.uk/)
define('BM_DVLA_API_KEY', 'YOUR_DVLA_API_KEY_HERE');

/* That's all, stop editing! Happy publishing. */
```

## Verifying Configuration

After adding keys to `wp-config.php`:

1. Go to WordPress Admin → Blue Motors → Settings
2. Check the "API Settings" tab
3. Verify that your keys are detected

## Security Best Practices

✅ **DO:**
- Store API keys in `wp-config.php`
- Use test keys for development
- Use live keys only on production
- Keep `wp-config.php` secure and backed up
- Review GitHub before pushing code

❌ **DON'T:**
- Commit API keys to Git
- Share API keys publicly
- Use live keys on development sites
- Hardcode secrets in plugin files

## Deployment Checklist

Before deploying to production:

- [ ] All API keys configured in `wp-config.php`
- [ ] Stripe set to live mode (if taking real payments)
- [ ] Test booking flow completely
- [ ] Verify email notifications work
- [ ] Check Google Calendar integration
- [ ] Backup your database

## Need Help?

- **Stripe Keys:** https://dashboard.stripe.com/apikeys
- **DVLA API:** https://developer-portal.driver-vehicle-licensing.api.gov.uk/
- **Google Calendar:** See `vendor/GOOGLE_CALENDAR_SETUP_GUIDE.md`
