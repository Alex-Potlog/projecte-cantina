<?php
/**
 * Exemple d'ús del sistema de logging
 * Aquest fitxer demostra com utilitzar la classe Logger
 */

require_once 'Logger.php';
session_start();

// Simular un usuari loggejat (per a l'exemple)
if (!isset($_SESSION['usuari'])) {
    $_SESSION['usuari'] = 'usuari_demo';
    $_SESSION['admin'] = false;
}

echo "=== EXEMPLE D'ÚS DEL SISTEMA DE LOGGING ===\n\n";

// 1. Log d'informació general
echo "1. Registrant informació general...\n";
Logger::info('Exemple d\'informació general', ['exemple' => true]);

// 2. Log d'error
echo "2. Registrant un error...\n";
Logger::error('Exemple d\'error', ['codi_error' => 500, 'missatge' => 'Error de prova']);

// 3. Log de warning
echo "3. Registrant un avís...\n";
Logger::warning('Exemple d\'avís', ['nivell' => 'moderat']);

// 4. Log d'accés exitós
echo "4. Registrant un accés exitós...\n";
Logger::access('Operació de prova', true, 'Aquest és un exemple d\'accés');

// 5. Log d'accés fallit
echo "5. Registrant un accés fallit...\n";
Logger::access('Intent fallit', false, 'Exemple de fallada');

// 6. Log d'intent de login
echo "6. Registrant intent de login exitós...\n";
Logger::loginAttempt('usuari_demo', true, false);

echo "\n7. Registrant intent de login fallit...\n";
Logger::loginAttempt('usuari_inexistent', false);

// 8. Log d'operació de producte
echo "\n8. Registrant operació de producte...\n";
Logger::productOperation('Added', [
    'nom' => 'Producte de prova',
    'preu' => 9.99,
    'categoria' => 'begudes'
]);

// 9. Log d'excepció
echo "\n9. Registrant una excepció...\n";
try {
    throw new Exception('Aquesta és una excepció de prova');
} catch (Exception $e) {
    Logger::exception($e);
}

// 10. Log de logout
echo "\n10. Registrant logout...\n";
Logger::logout($_SESSION['usuari']);

echo "\n=== FI DE L'EXEMPLE ===\n";
echo "\nEls logs s'han guardat a:\n";
echo "- ../logs/error.log (errors, warnings, info)\n";
echo "- ../logs/access.log (accessos i operacions)\n\n";

echo "Pots visualitzar els logs amb:\n";
echo "- Interfície web: /pages/logs.html\n";
echo "- API: /php/viewLogs.php\n";
echo "- Directament: cat ../logs/*.log\n";

?>
