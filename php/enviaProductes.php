<?php
require_once 'Logger.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$prodsDir = __DIR__ . '/../data/products/';
$prodsFile = $prodsDir . 'products.json';
$productes = file_get_contents($prodsFile);

if ($productes){
    Logger::access('Fetch products', true, 'Public products endpoint');
    echo json_encode([
    'success' => true,
    'productes' => $productes
]);
} else {
    Logger::error('Products not found', ['file' => $prodsFile]);
    echo json_encode([
        'success' => false,
        'reason' => "Productes no trobats"
    ]);
}
