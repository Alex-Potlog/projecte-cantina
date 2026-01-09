<?php
// Recull els tiquets del servidor en un arxiu i els envia al client.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$ticketsDir = __DIR__ . '/../tiquets/';

$files = glob($ticketsDir . 'tiquet-*.json');
$tickets = [];

foreach ($files as $file) {
    // Extraer el número del ticket
    $ticketNumber = preg_replace('/.*tiquet-(\d+)\.json/', '$1', $file);
   
    // Leer el contenido del archivo
    $content = file_get_contents($file);
    $data = json_decode($content, true);
   
    // Verificar que el JSON sea válido
    if (json_last_error() === JSON_ERROR_NONE) {
        $tickets[] = [
            'id' => (int)$ticketNumber,
            'filename' => basename($file),
            'data' => $data  // Incluye username, usermail, usertel y productos[]
        ];
    } else {
        // Si hay error en el JSON, incluir información del error
        $tickets[] = [
            'id' => (int)$ticketNumber,
            'filename' => basename($file),
            'data' => null,
            'error' => 'Error al leer JSON: ' . json_last_error_msg()
        ];
    }
}

echo json_encode([
    'success' => true,
    'tickets' => $tickets,
    'total' => count($tickets)
]);
