<?php
require_once 'Logger.php';

session_start();

// Verificar que l'usuari està logat i és admin
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$logDir = '../logs/';
$action = $_GET['action'] ?? 'list';

header('Content-Type: application/json');

switch($action) {
    case 'list':
        // Llistar fitxers de log disponibles
        $logFiles = [];
        if (is_dir($logDir)) {
            $files = scandir($logDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'log') {
                    $logFiles[] = [
                        'name' => $file,
                        'size' => filesize($logDir . $file),
                        'modified' => filemtime($logDir . $file)
                    ];
                }
            }
        }
        
        Logger::access('View logs list', true, 'Admin panel');
        echo json_encode(['success' => true, 'logs' => $logFiles]);
        break;
        
    case 'read':
        // Llegir contingut d'un log
        $file = $_GET['file'] ?? '';
        $lines = intval($_GET['lines'] ?? 100); // Número de línies a mostrar
        
        // Validar nom de fitxer
        if (!preg_match('/^[a-zA-Z0-9_\-]+\.log$/', $file)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file name']);
            break;
        }
        
        $filePath = $logDir . $file;
        
        if (!file_exists($filePath)) {
            echo json_encode(['success' => false, 'error' => 'File not found']);
            break;
        }
        
        // Llegir últimes N línies del fitxer
        $fileLines = file($filePath);
        $totalLines = count($fileLines);
        $lastLines = array_slice($fileLines, max(0, $totalLines - $lines));
        
        Logger::access("Read log file: $file", true, "Lines: $lines");
        echo json_encode([
            'success' => true,
            'file' => $file,
            'totalLines' => $totalLines,
            'content' => implode('', $lastLines)
        ]);
        break;
        
    case 'download':
        // Descarregar un fitxer de log
        $file = $_GET['file'] ?? '';
        
        // Validar nom de fitxer
        if (!preg_match('/^[a-zA-Z0-9_\-]+\.log$/', $file)) {
            http_response_code(400);
            exit('Invalid file name');
        }
        
        $filePath = $logDir . $file;
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('File not found');
        }
        
        Logger::access("Download log file: $file", true);
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
        
    case 'clear':
        // Netejar un fitxer de log (només deixar buit)
        $file = $_GET['file'] ?? '';
        
        // Validar nom de fitxer
        if (!preg_match('/^[a-zA-Z0-9_\-]+\.log$/', $file)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file name']);
            break;
        }
        
        $filePath = $logDir . $file;
        
        if (!file_exists($filePath)) {
            echo json_encode(['success' => false, 'error' => 'File not found']);
            break;
        }
        
        // Crear backup abans de netejar
        $backupPath = $logDir . $file . '.backup.' . date('Y-m-d_H-i-s');
        copy($filePath, $backupPath);
        
        // Netejar fitxer
        file_put_contents($filePath, '');
        
        Logger::access("Clear log file: $file", true, "Backup created: $backupPath");
        echo json_encode(['success' => true, 'message' => 'Log cleared', 'backup' => basename($backupPath)]);
        break;
        
    case 'clean-old':
        // Netejar logs antics
        $days = intval($_GET['days'] ?? 30);
        Logger::cleanOldLogs($days);
        Logger::access("Clean old logs", true, "Older than $days days");
        echo json_encode(['success' => true, 'message' => "Logs older than $days days deleted"]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
