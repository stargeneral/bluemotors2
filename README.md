# Blue Motors Southampton - WordPress Booking Plugin

**Professional booking system for Southampton garage with Google Calendar integration and comprehensive tyre services.**

## 📋 Plugin Information

- **Plugin Name:** Blue Motors Southampton
- **Version:** 1.4.0
- **WordPress Version:** 6.0+
- **PHP Version:** 8.0+
- **Author:** Blue Motors Development Team
- **License:** GPL v2 or later

## 🚀 Features

### Core Functionality
- **Single Location Focus** - Optimized for Southampton garage
- **Service Booking System** - MOT, Full Service, Interim Service, Diagnostics, Brake Checks
- **Tyre Search & Booking** - Complete online tyre ordering system
- **Google Calendar Integration** - Real-time availability and automated event creation
- **DVLA Vehicle Lookup** - Real-time vehicle data integration
- **Dynamic Pricing** - Engine size-based calculations
- **Payment Processing** - Stripe integration (test/live modes)
- **Email Notifications** - SMTP-powered confirmations
- **Professional Admin Interface** - WordPress-standard management

### Services Offered
- **MOT Test** - £40 (fixed price)
- **Full Service** - From £149 (engine-based pricing)
- **Interim Service** - From £89 (engine-based pricing)
- **Brake Check** - £25 (fixed price)
- **Diagnostic Check** - £45 (fixed price)
- **Tyre Fitting** - Competitive pricing with online ordering

### Technical Features
- **Google Calendar Integration** - Real-time availability checking and event management
- **Comprehensive Tyre Database** - 10,000+ tyres with intelligent search
- **Vehicle Registration Lookup** - Automatic tyre size recommendations
- **WordPress Standards Compliant** - Proper hooks, security, performance
- **Mobile Responsive** - Mobile-first design with enhanced date/time pickers
- **Real-time Availability** - Google Calendar-powered slot management
- **Professional Email System** - SMTP integration with templates
- **Comprehensive Admin Panel** - Full configuration control

## 🏢 Business Information

**Blue Motors Southampton**
- **Address:** 1 Kent St, Northam, Southampton SO14 5SP
- **Phone:** 023 8000 0000
- **Email:** southampton@bluemotors.co.uk
- **Hours:** Mon-Fri 8:00-18:00, Sat 8:00-16:00, Sun Closed

## 📦 Installation

### Via WordPress Admin
1. Download the plugin zip file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Choose the zip file and click "Install Now"
4. Activate the plugin
5. Configure settings via Blue Motors menu

### Manual Installation
1. Upload the `blue-motors-southampton` folder to `/wp-content/plugins/`
2. Activate the plugin through WordPress Admin → Plugins
3. Configure settings via Blue Motors menu

## ⚙️ Configuration

### Initial Setup
1. **Business Settings** - Configure contact info and hours
2. **Payment Gateway** - Set up Stripe keys (test/live)
3. **Email Settings** - Configure SMTP for notifications
4. **Services** - Adjust pricing and availability

### Required API Keys & Setup
- **Google Calendar Service Account** - For appointment scheduling integration
- **DVLA API Key** - For vehicle lookup (optional - has fallback)
- **Stripe Keys** - For payment processing
- **SMTP Settings** - For email notifications

### Google Calendar Setup
1. Create Google Cloud Project
2. Enable Google Calendar API
3. Create Service Account
4. Download JSON credentials file
5. Place at `vendor/service-account-credentials.json`
6. Share calendar with service account email
See `vendor/GOOGLE_CALENDAR_SETUP_GUIDE.md` for detailed instructions.

## 🎯 Usage

### Service Booking Form
Add the booking form to any page with the shortcode:
```
[bms_booking_form]
```

### Tyre Search System
Add comprehensive tyre search with:
```
[bms_tyre_search]
```

### Vehicle Lookup
Add standalone vehicle lookup with:
```
[bms_vehicle_lookup]
```

### Location Information
Display business details with:
```
[bms_location_info]
```

### Service Booking Flow
1. **Service Selection** - Choose from 5 available services
2. **Vehicle Details** - Enter registration for automatic lookup and pricing
3. **Date & Time** - Select from Google Calendar-synchronized available slots
4. **Customer Info** - Contact details and special requirements
5. **Payment** - Secure Stripe payment processing
6. **Confirmation** - Email confirmation sent + Google Calendar event created

### Tyre Booking Flow
1. **Vehicle Registration** - Enter reg for automatic tyre size detection
2. **Tyre Selection** - Browse recommended tyres with pricing
3. **Quantity Selection** - Choose 1, 2, or 4 tyres
4. **Fitting Date** - Select available appointment slots
5. **Customer Details** - Contact information and special requirements
6. **Payment** - Secure payment with automatic calendar booking

## 🔧 Admin Features

### Dashboard
- Today's bookings overview
- Weekly statistics
- Popular services tracking
- System status monitoring

### Booking Management
- View all bookings (today, upcoming, past)
- Update booking status
- Customer communication
- Professional booking interface

### Service Configuration
- Manage service offerings
- Update pricing structure
- Control availability
- Service feature management

### Business Settings
- Contact information
- Opening hours
- Booking parameters
- Location details

### Payment Settings
- Stripe configuration
- Test/Live mode switching
- Currency settings
- VAT configuration

## 📧 Email System

### Automatic Notifications
- **Customer Confirmation** - Booking details and directions
- **Admin Notification** - New booking alerts
- **Reminder Emails** - Optional 24-hour reminders

### SMTP Configuration
- Professional email delivery
- Template customization
- Delivery status monitoring
- Fallback to wp_mail()

## 🛠️ Technical Details

### Database Tables
- `wp_bms_appointments` - Main service booking storage
- `wp_bms_tyre_bookings` - Tyre appointment storage
- `wp_bms_tyres` - Comprehensive tyre database (10,000+ tyres)
- `wp_bms_vehicle_tyres` - Vehicle-to-tyre size mapping
- `wp_bms_services` - Service configuration
- `wp_bms_booking_logs` - Activity tracking
- `wp_bms_booking_meta` - Google Calendar event IDs and metadata
- `wp_bms_tyre_booking_meta` - Tyre booking calendar integration

### Security Features
- **Nonce Verification** - CSRF protection
- **Input Sanitization** - XSS prevention
- **Capability Checks** - Role-based access
- **Secure API Integration** - Encrypted key storage

### Performance Optimization
- **Caching System** - 24-hour API response cache
- **Database Indexing** - Optimized queries
- **Conditional Loading** - Scripts loaded when needed
- **Error Handling** - Graceful fallbacks

## 🧪 Testing

### Development Mode
Enable WordPress debug mode to access testing features:
```php
define('WP_DEBUG', true);
```

### Available Tests
- **Phase 3 Tests** - Comprehensive system verification
- **Database Status** - Health monitoring
- **Email Testing** - SMTP verification

## 📱 Mobile Support

- **Responsive Design** - Works on all devices
- **Touch Optimized** - Mobile-friendly interface
- **Fast Loading** - Optimized for mobile networks
- **Accessible** - Screen reader compatible

## 🔄 Updates & Maintenance

### Version History
- **v1.3.0** - Production release with full feature set
- **v1.2.0** - Enhanced admin interfaces and integration
- **v1.1.0** - SMTP system and vehicle lookup
- **v1.0.0** - Initial release with core functionality

### Backup Recommendations
- Regular database backups
- Plugin settings export
- Email template backups
- Test environment maintenance

## 🆘 Support & Troubleshooting

### Common Issues

**Database Errors:**
- Ensure tables are created properly
- Check database permissions
- Use Database Status page for diagnostics

**Email Not Sending:**
- Verify SMTP settings
- Test email configuration
- Check server email limits

**Payment Issues:**
- Verify Stripe API keys
- Check test/live mode settings
- Monitor Stripe dashboard

**API Integration:**
- DVLA API key configuration
- Fallback data available
- Error logging enabled

### Debug Mode Features
When `WP_DEBUG` is enabled:
- Phase 3 testing interface
- Database status monitoring
- Enhanced error logging
- Development tools access

## 🏆 Production Readiness

### Pre-Launch Checklist
- [ ] Business settings configured
- [ ] Payment gateway tested
- [ ] Email notifications working
- [ ] Services and pricing set
- [ ] Database health verified
- [ ] Mobile experience tested

### Go-Live Steps
1. Switch Stripe to live mode
2. Configure live email settings
3. Test complete booking flow
4. Monitor initial bookings
5. Gather customer feedback

## 📞 Support Information

For technical support or customization requests:
- Review plugin documentation
- Check WordPress debug logs
- Test in staging environment
- Contact development team

## 📜 License

This plugin is licensed under the GPL v2 or later.
```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## 🎉 Credits

Developed for Blue Motors Southampton garage, implementing industry leaders-style booking functionality optimized for single-location operations.

---

**Ready to take bookings! 🚀**