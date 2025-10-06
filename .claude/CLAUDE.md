# Blue Motors Southampton WordPress Plugin - Claude Code Context

## Project Overview
This is a professional WordPress booking system plugin for Blue Motors Southampton garage. The plugin provides industry-standard booking functionality with DVLA integration, dynamic pricing, and comprehensive service management.

## Business Context
- **Company**: Blue Motors Southampton
- **Location**: 1 Kent St, Northam, Southampton SO14 5SP
- **Services**: MOT Tests, Full Service, Interim Service
- **Focus**: Single location garage with professional booking system

## Technical Stack
- **WordPress**: 6.0+ required
- **PHP**: 8.0+ required  
- **Plugin Version**: 1.3.0
- **License**: GPL v2 or later
- **Text Domain**: blue-motors-southampton

## Key Features
- DVLA vehicle lookup integration
- Dynamic engine-size based pricing
- Stripe payment processing (test/live modes)
- SMTP email notifications
- Mobile-responsive design
- Real-time availability management
- Professional admin interface

## Service Pricing
- MOT Test: £40 (fixed)
- Full Service: From £149 (engine-based)
- Interim Service: From £89 (engine-based)

## Directory Structure
- `/admin` - Admin interface components
- `/assets` - CSS, JS, images
- `/includes` - Core PHP classes and functions
- `/templates` - Frontend template files
- `/config` - Configuration files
- `/database` - Database schema and migrations
- `/public` - Public-facing functionality
- `/testing` - Test files and debugging tools
- `/vendor` - Composer dependencies

## Key Files
- `blue-motors-southampton.php` - Main plugin file
- `composer.json` - PHP dependencies
- `/includes/shortcode-*.php` - Shortcode handlers
- `/config/constants.php` - Configuration constants

## Development Notes
- The plugin uses WordPress coding standards
- Autoloading via custom autoloader
- Multiple shortcodes for different booking components
- Extensive documentation in markdown files
- Comprehensive testing framework in place

## Common Development Tasks
- Adding new service types
- Modifying pricing logic
- Updating DVLA integration
- Enhancing booking flow
- Improving mobile experience
- Email template modifications
- Admin interface enhancements

## Security & Performance
- Input sanitization throughout
- Nonce verification for AJAX
- Proper capability checks
- Optimized database queries
- Caching where appropriate

## Integration Points
- DVLA API for vehicle data
- Stripe for payments
- SMTP for emails
- WordPress hooks system
- Custom database tables

When working on this plugin, always consider:
1. WordPress coding standards compliance
2. Mobile-first responsive design
3. Security best practices
4. Performance optimization
5. User experience for both customers and admin users
