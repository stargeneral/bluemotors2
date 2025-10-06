<?php
/**
 * Enhanced Services Configuration - Match other automotive services Range
 * Blue Motors Southampton - Professional Auto Services
 * 
 * @package BlueMotosSouthampton
 * @since 3.0.0 (Phase 3)
 */

// Enhanced services array to merge with existing services
$enhanced_services = [
    
    // Air Conditioning Services
    'air_con_regas' => [
        'id' => 'air_con_regas',
        'name' => 'Air Conditioning Re-gas',
        'description' => 'Complete air conditioning system re-gas and performance check. Get your AC working like new again.',
        'long_description' => 'Our comprehensive air conditioning service includes leak detection, system evacuation, fresh refrigerant refill, and performance testing. We use the latest equipment to ensure your AC system operates at peak efficiency.',
        'base_price' => 89.00,
        'duration' => 60, // minutes
        'engine_pricing' => false, // Fixed price
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'climate',
        'icon' => 'fa-snowflake',
        'seasonal' => true, // Popular in summer
        'includes' => [
            'Complete system leak check',
            'Refrigerant recovery and recycling',
            'Fresh refrigerant refill',
            'System performance test',
            'Visual inspection of components',
            'Operating pressure check'
        ],
        'requirements' => [
            'Vehicle must be present for duration of service',
            'System must not have major leaks',
            'Compatible refrigerant type required'
        ],
        'booking_notice' => 'Please ensure your vehicle has been driven recently to warm up the AC system.',
        'competitive_note' => 'other automotive services offers this - we do too, but with online booking!',
        'f1_equivalent' => true
    ],
    
    'air_con_service' => [
        'id' => 'air_con_service',
        'name' => 'Air Conditioning Full Service',
        'description' => 'Comprehensive air conditioning service including cleaning, antibacterial treatment, and system optimization.',
        'long_description' => 'Complete AC system service including evaporator cleaning, antibacterial treatment, filter replacement, and system optimization for maximum cooling efficiency.',
        'base_price' => 129.00,
        'duration' => 90,
        'engine_pricing' => false,
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'climate',
        'icon' => 'fa-wind',
        'seasonal' => true,
        'includes' => [
            'Complete system inspection',
            'Evaporator cleaning',
            'Antibacterial treatment',
            'Cabin filter replacement',
            'Refrigerant top-up',
            'Performance optimization',
            'Vent cleaning and sanitization'
        ],
        'booking_notice' => 'Recommended annually or if you notice reduced cooling or unpleasant odors.',
        'competitive_note' => 'Premium service - enhanced service\'s basic offering'
    ],
    
    // Brake Services
    'brake_check' => [
        'id' => 'brake_check',
        'name' => 'Brake Inspection & Check',
        'description' => 'Comprehensive brake system inspection including pads, discs, fluid, and performance testing.',
        'long_description' => 'Professional brake system inspection covering all key components with detailed written report and recommendations for any necessary work.',
        'base_price' => 45.00,
        'duration' => 45,
        'engine_pricing' => false,
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'safety',
        'icon' => 'fa-ban',
        'safety_critical' => true,
        'includes' => [
            'Visual inspection of brake pads',
            'Brake disc condition check',
            'Brake fluid level and condition',
            'Handbrake adjustment check',
            'Brake pedal feel test',
            'Written report with recommendations',
            'Photos of any wear or damage'
        ],
        'booking_notice' => 'We recommend brake checks every 12,000 miles or annually.',
        'competitive_note' => 'Professional brake inspection - F1 standard service',
        'f1_equivalent' => true
    ],
    
    'brake_service' => [
        'id' => 'brake_service',
        'name' => 'Brake Pad & Disc Service',
        'description' => 'Professional brake pad and disc replacement with quality parts and comprehensive testing.',
        'long_description' => 'Complete brake service using quality parts with professional fitting, system bleeding, and road test verification.',
        'base_price' => 149.00,
        'duration' => 120,
        'engine_pricing' => false,
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'safety',
        'icon' => 'fa-tools',
        'safety_critical' => true,
        'includes' => [
            'Quality brake pad replacement',
            'Brake disc replacement (if required)',
            'Brake fluid replacement',
            'System bleeding and testing',
            'Road test verification',
            'Brake performance check',
            '12-month parts warranty'
        ],
        'booking_notice' => 'Please bring your vehicle with worn brake pads/discs identified during inspection.',
        'competitive_note' => 'Superior parts and warranty vs other automotive services'
    ],
    
    // Battery Services
    'battery_test' => [
        'id' => 'battery_test',
        'name' => 'Battery Test & Health Check',
        'description' => 'Professional battery testing with charging system check and replacement advice.',
        'long_description' => 'Comprehensive battery and charging system test using professional equipment to assess battery health and charging performance.',
        'base_price' => 25.00,
        'duration' => 30,
        'engine_pricing' => false,
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'electrical',
        'icon' => 'fa-battery-three-quarters',
        'seasonal' => true, // Popular in winter
        'includes' => [
            'Battery voltage test',
            'Load testing under simulated conditions',
            'Charging system check',
            'Terminal cleaning and protection',
            'Written health report',
            'Replacement recommendations if needed'
        ],
        'booking_notice' => 'Recommended if you\'ve experienced slow starting or electrical issues.',
        'competitive_note' => 'F1 equivalent service with better online experience',
        'f1_equivalent' => true
    ],
    
    'battery_replacement' => [
        'id' => 'battery_replacement',
        'name' => 'Battery Replacement Service',
        'description' => 'Quality battery replacement with fitting, testing, and disposal of old battery.',
        'long_description' => 'Professional battery replacement service using quality batteries with fitting, testing, and environmentally responsible disposal.',
        'base_price' => 89.00,
        'duration' => 45,
        'engine_pricing' => false,
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'electrical',
        'icon' => 'fa-battery-full',
        'includes' => [
            'Quality replacement battery',
            'Professional fitting and connection',
            'System testing and verification',
            'Terminal protection application',
            'Old battery disposal (free)',
            '24-month warranty on battery'
        ],
        'booking_notice' => 'We\'ll dispose of your old battery responsibly at no extra charge.',
        'competitive_note' => 'Better warranty than F1 - 24 months vs their standard'
    ],
    
    // Exhaust Services
    'exhaust_check' => [
        'id' => 'exhaust_check',
        'name' => 'Exhaust System Inspection',
        'description' => 'Comprehensive exhaust system check including emissions, noise levels, and component condition.',
        'long_description' => 'Complete exhaust system inspection covering all components from engine to tailpipe with emissions testing and condition report.',
        'base_price' => 35.00,
        'duration' => 30,
        'engine_pricing' => false,
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'emissions',
        'icon' => 'fa-smog',
        'includes' => [
            'Visual exhaust system inspection',
            'Emissions level check',
            'Noise level assessment',
            'Mount and bracket inspection',
            'Leak detection throughout system',
            'Written condition report with photos'
        ],
        'booking_notice' => 'Recommended if you notice unusual exhaust noise or smoke.',
        'competitive_note' => 'F1 equivalent service - we do it better online',
        'f1_equivalent' => true
    ],
    
    'exhaust_repair' => [
        'id' => 'exhaust_repair',
        'name' => 'Exhaust Repair & Replacement',
        'description' => 'Professional exhaust repair or replacement using quality parts with fitting and testing.',
        'long_description' => 'Expert exhaust repair or replacement service using quality components with professional fitting and post-installation testing.',
        'base_price' => 95.00,
        'duration' => 75,
        'engine_pricing' => false,
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'emissions',
        'icon' => 'fa-wrench',
        'includes' => [
            'Exhaust component replacement',
            'Professional welding if required',
            'Quality replacement parts',
            'Secure mounting and alignment',
            'Post-installation testing',
            '12-month parts warranty'
        ],
        'booking_notice' => 'Please book after exhaust inspection to identify required parts.',
        'competitive_note' => 'Local expertise vs F1\'s chain approach'
    ],
    
    // Clutch Services
    'clutch_check' => [
        'id' => 'clutch_check',
        'name' => 'Clutch Inspection & Check',
        'description' => 'Professional clutch system inspection including operation test and adjustment check.',
        'long_description' => 'Comprehensive clutch system inspection covering clutch operation, adjustment, and component condition assessment.',
        'base_price' => 55.00,
        'duration' => 60,
        'engine_pricing' => false,
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'drivetrain',
        'icon' => 'fa-cog',
        'includes' => [
            'Clutch operation test',
            'Pedal feel assessment',
            'Clutch adjustment check',
            'Visual inspection where possible',
            'Clutch cable/hydraulic check',
            'Written assessment report'
        ],
        'booking_notice' => 'Recommended if experiencing clutch slip, heavy pedal, or gear selection issues.',
        'competitive_note' => 'Specialist service - F1 focuses more on basic maintenance'
    ],
    
    // Suspension Services
    'suspension_check' => [
        'id' => 'suspension_check',
        'name' => 'Suspension & Steering Check',
        'description' => 'Comprehensive suspension and steering system inspection including safety check.',
        'long_description' => 'Professional suspension and steering inspection covering all components with road test and safety assessment.',
        'base_price' => 65.00,
        'duration' => 75,
        'engine_pricing' => false,
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'category' => 'safety',
        'icon' => 'fa-road',
        'safety_critical' => true,
        'includes' => [
            'Shock absorber inspection',
            'Spring condition check',
            'Steering component inspection',
            'Wheel alignment assessment',
            'Ball joint and bearing check',
            'Road test evaluation'
        ],
        'booking_notice' => 'Recommended if experiencing uneven tire wear, pulling, or poor handling.',
        'competitive_note' => 'More thorough than F1\'s basic checks'
    ]
];

return $enhanced_services;
