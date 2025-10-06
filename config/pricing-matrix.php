<?php
/**
 * Blue Motors Southampton - Pricing Matrix
 * Engine size-based pricing following other automotive services model
 * 
 * @package BlueMotosSouthampton
 * @since 1.0.0
 */

return [
    'interim_service' => [
        'petrol' => [
            ['max_engine' => 1000, 'price' => 140],
            ['max_engine' => 1600, 'price' => 175],
            ['max_engine' => 2000, 'price' => 185],
            ['max_engine' => 3500, 'price' => 215],
            ['max_engine' => 9999, 'price' => 245] // Above 3500cc
        ],
        'diesel' => [
            ['max_engine' => 1000, 'price' => 140],
            ['max_engine' => 1600, 'price' => 175],
            ['max_engine' => 2000, 'price' => 185],
            ['max_engine' => 3500, 'price' => 215],
            ['max_engine' => 9999, 'price' => 245]
        ],
        'hybrid' => [
            ['max_engine' => 1000, 'price' => 115],
            ['max_engine' => 1600, 'price' => 165],
            ['max_engine' => 2000, 'price' => 175],
            ['max_engine' => 3500, 'price' => 195],
            ['max_engine' => 9999, 'price' => 225]
        ],
        'electric' => [
            ['max_engine' => 9999, 'price' => 89] // Fixed price for electric
        ]
    ],
    
    'full_service' => [
        'petrol' => [
            ['max_engine' => 1000, 'price' => 225],
            ['max_engine' => 1600, 'price' => 245],
            ['max_engine' => 2000, 'price' => 255],
            ['max_engine' => 3500, 'price' => 285],
            ['max_engine' => 9999, 'price' => 315]
        ],
        'diesel' => [
            ['max_engine' => 1000, 'price' => 225],
            ['max_engine' => 1600, 'price' => 245],
            ['max_engine' => 2000, 'price' => 255],
            ['max_engine' => 3500, 'price' => 285],
            ['max_engine' => 9999, 'price' => 315]
        ],
        'hybrid' => [
            ['max_engine' => 1000, 'price' => 205],
            ['max_engine' => 1600, 'price' => 235],
            ['max_engine' => 2000, 'price' => 245],
            ['max_engine' => 3500, 'price' => 275],
            ['max_engine' => 9999, 'price' => 305]
        ],
        'electric' => [
            ['max_engine' => 9999, 'price' => 149] // Fixed price for electric
        ]
    ],
    
    'mot_test' => [
        // MOT has fixed pricing regardless of engine/fuel type
        'all' => [
            ['max_engine' => 9999, 'price' => 40]
        ]
    ]
];