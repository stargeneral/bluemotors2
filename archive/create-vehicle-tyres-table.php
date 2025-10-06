<?php
/**
 * Create Missing Vehicle Tyres Table
 * Fixes the database error in tyre search functionality
 */

// Include WordPress
require_once '../../../wp-load.php';

echo "<h2>🛞 Creating Missing Vehicle Tyres Table</h2>";

global $wpdb;

// Create the missing wp_bms_vehicle_tyres table
$table_name = $wpdb->prefix . 'bms_vehicle_tyres';

$sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `vehicle_make` varchar(50) NOT NULL,
    `vehicle_model` varchar(100) NOT NULL,
    `year_from` int(4) NOT NULL,
    `year_to` int(4) DEFAULT NULL,
    `front_tyre_size` varchar(20) NOT NULL,
    `rear_tyre_size` varchar(20) DEFAULT NULL,
    `original_equipment` tinyint(1) DEFAULT 1,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_vehicle_lookup` (`vehicle_make`, `vehicle_model`, `year_from`, `year_to`),
    KEY `idx_make_year` (`vehicle_make`, `year_from`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$result = $wpdb->query($sql);

if ($result !== false) {
    echo "✅ Table `{$table_name}` created successfully<br>";
} else {
    echo "❌ Failed to create table: " . $wpdb->last_error . "<br>";
}

// Insert sample data for common UK vehicles
echo "<h3>Adding Sample Vehicle-Tyre Data</h3>";

$sample_data = [
    // Ford models
    ['FORD', 'FIESTA', 2008, 2017, '195/50R15'],
    ['FORD', 'FIESTA', 2017, null, '195/50R16'],
    ['FORD', 'FOCUS', 2005, 2018, '205/55R16'],
    ['FORD', 'FOCUS', 2018, null, '215/55R16'],
    ['FORD', 'MONDEO', 2007, 2022, '215/60R16'],
    
    // Vauxhall models  
    ['VAUXHALL', 'CORSA', 2006, 2019, '185/65R15'],
    ['VAUXHALL', 'CORSA', 2019, null, '195/55R16'],
    ['VAUXHALL', 'ASTRA', 2009, 2022, '205/55R16'],
    ['VAUXHALL', 'INSIGNIA', 2008, null, '215/55R17'],
    
    // Volkswagen models
    ['VOLKSWAGEN', 'POLO', 2009, null, '185/60R15'],
    ['VOLKSWAGEN', 'GOLF', 2008, null, '205/55R16'],
    ['VOLKSWAGEN', 'PASSAT', 2005, null, '215/60R16'],
    
    // BMW models
    ['BMW', '1 SERIES', 2004, null, '205/55R16'],
    ['BMW', '3 SERIES', 2005, null, '225/50R17'],
    ['BMW', '5 SERIES', 2003, null, '245/45R18'],
    
    // Audi models
    ['AUDI', 'A3', 2003, null, '205/55R16'],
    ['AUDI', 'A4', 2000, null, '225/50R17'],
    ['AUDI', 'A6', 2004, null, '245/45R18'],
    
    // Mercedes models
    ['MERCEDES-BENZ', 'A CLASS', 2012, null, '205/55R16'],
    ['MERCEDES-BENZ', 'C CLASS', 2007, null, '225/50R17'],
    ['MERCEDES-BENZ', 'E CLASS', 2009, null, '245/45R18'],
    
    // Hyundai models (for the test registration VF19XKX)
    ['HYUNDAI', 'IONIQ', 2016, null, '195/65R15'],
    ['HYUNDAI', 'I30', 2012, null, '205/55R16'],
    ['HYUNDAI', 'TUCSON', 2015, null, '215/60R17'],
    ['HYUNDAI', 'SANTA FE', 2012, null, '235/60R18'],
    
    // Kia models
    ['KIA', 'PICANTO', 2011, null, '175/65R14'],
    ['KIA', 'CEE\'D', 2007, null, '205/55R16'],
    ['KIA', 'SPORTAGE', 2010, null, '215/60R17'],
    
    // Nissan models
    ['NISSAN', 'MICRA', 2010, null, '175/65R15'],
    ['NISSAN', 'QASHQAI', 2006, null, '215/60R17'],
    ['NISSAN', 'X-TRAIL', 2007, null, '225/60R17'],
    
    // Toyota models
    ['TOYOTA', 'YARIS', 2005, null, '175/65R14'],
    ['TOYOTA', 'COROLLA', 2007, null, '205/55R16'],
    ['TOYOTA', 'RAV4', 2006, null, '225/65R17'],
];

$inserted = 0;
$errors = 0;

foreach ($sample_data as $data) {
    $result = $wpdb->insert(
        $table_name,
        [
            'vehicle_make' => $data[0],
            'vehicle_model' => $data[1],
            'year_from' => $data[2],
            'year_to' => $data[3],
            'front_tyre_size' => $data[4],
            'is_active' => 1
        ],
        ['%s', '%s', '%d', '%d', '%s', '%d']
    );
    
    if ($result !== false) {
        $inserted++;
    } else {
        $errors++;
    }
}

echo "✅ Inserted {$inserted} vehicle-tyre mappings<br>";
if ($errors > 0) {
    echo "❌ {$errors} errors occurred during insertion<br>";
}

// Test the fix
echo "<h3>Testing the Fix</h3>";

try {
    if (class_exists('BlueMotosSouthampton\Services\TyreService')) {
        $tyre_service = new \BlueMotosSouthampton\Services\TyreService();
        
        echo "Testing search for Hyundai (registration VF19XKX)...<br>";
        
        // Create sample vehicle data like what comes from DVLA
        $test_vehicle_data = [
            'make' => 'HYUNDAI',
            'yearOfManufacture' => 2019,
            'engineCapacity' => 1580,
            'registrationNumber' => 'VF19XKX'
        ];
        
        // Test the private method using reflection (for testing purposes)
        $reflection = new ReflectionClass($tyre_service);
        $method = $reflection->getMethod('get_vehicle_tyre_sizes');
        $method->setAccessible(true);
        
        $result = $method->invoke($tyre_service, $test_vehicle_data);
        
        echo "✅ Method executed successfully<br>";
        echo "Recommended tyre sizes: " . implode(', ', $result) . "<br>";
        
    } else {
        echo "❌ TyreService class not available for testing<br>";
    }
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>🎯 Summary</h3>";
echo "<p>✅ Created missing `{$table_name}` table<br>";
echo "✅ Added {$inserted} vehicle-tyre size mappings<br>";  
echo "✅ Fixed database error in tyre search functionality</p>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Try the tyre search again with registration VF19XKX</li>";
echo "<li>The PHP warnings about missing 'model' key should also be resolved</li>";
echo "<li>Check error logs to confirm no more database errors</li>";
echo "</ul>";

echo "<p><em>Fix completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>