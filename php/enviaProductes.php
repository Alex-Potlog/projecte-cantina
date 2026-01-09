<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$prodsDir = __DIR__ . '/../data/products/';
$prodsFile = $prodsDir . 'products.json';
$productes = file_get_contents($prodsFile);

if ($productes){
    echo json_encode([
    'success' => true,
    'productes' => $productes
]);
} else {
    echo json_encode([
        'success' => false,
        'reason' => "Productes no trobats"
    ]);
}
