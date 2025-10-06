<?php
/**
 * Blue Motors Southampton - Service Definitions
 * Based on other automotive services service model
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

return [
    'mot_test' => [
        'id' => 'mot_test',
        'name' => 'MOT Test',
        'description' => 'Annual MOT test for your vehicle. We\'ll check your car meets road safety and environmental standards.',
        'base_price' => 40.00,
        'duration' => 45, // minutes
        'engine_pricing' => false, // Fixed price regardless of engine size
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'booking_notice' => 'Please bring your V5C registration document and any previous MOT certificates.',
        'icon' => 'fa-clipboard-check'
    ],
    
    'full_service' => [
        'id' => 'full_service',
        'name' => 'Full Service',
        'description' => 'Comprehensive vehicle service including oil change, filter replacements, and 50+ point check.',
        'base_price' => 149.00, // Base price, actual varies by engine size
        'duration' => 120, // minutes
        'engine_pricing' => true, // Price varies by engine size
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'includes' => [
            'Engine oil and filter change',
            'Air filter replacement',
            'Spark plugs check/replacement',
            'Full brake inspection',
            'Exhaust system check',
            'Battery and charging check',
            'Coolant and fluids top-up',
            '50+ point vehicle check'
        ],
        'booking_notice' => 'Service history book will be stamped upon completion.',
        'icon' => 'fa-tools'
    ],
    
    'interim_service' => [
        'id' => 'interim_service',
        'name' => 'Interim Service',
        'description' => 'Essential service to keep your vehicle running smoothly between full services.',
        'base_price' => 89.00, // Base price, actual varies by engine size
        'duration' => 90, // minutes
        'engine_pricing' => true, // Price varies by engine size
        'vat_inclusive' => true,
        'requires_vehicle_details' => true,
        'includes' => [
            'Engine oil and filter change',
            'Windscreen washer fluid top-up',
            'Brake fluid check',
            'Power steering fluid check',
            'Lights and indicators check',
            'Tyre condition and pressure check',
            '35+ point vehicle check'
        ],
        'booking_notice' => 'Recommended every 6 months or 6,000 miles.',
        'icon' => 'fa-oil-can'
    ]
];