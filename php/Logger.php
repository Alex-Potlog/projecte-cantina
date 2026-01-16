<?php

/**
 * Classe Logger per gestionar els logs d'error i accés
 */
class Logger {
    private static $logDir = '../logs/';
    private static $errorLogFile = 'error.log';
    private static $accessLogFile = 'access.log';
    
    /**
     * Inicialitza el directori de logs si no existeix
     */
    private static function initLogDir() {
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }
    
    /**
     * Formata el missatge de log amb timestamp
     */
    private static function formatMessage($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
        
        $logMessage = "[$timestamp] [$level] [IP: $ip]";
        
        if (isset($_SESSION['usuari'])) {
            $logMessage .= " [User: {$_SESSION['usuari']}]";
        }
        
        $logMessage .= " - $message";
        
        if (!empty($context)) {
            $logMessage .= " | Context: " . json_encode($context);
        }
        
        $logMessage .= " | User-Agent: $userAgent\n";
        
        return $logMessage;
    }
    
    /**
     * Escriu en un fitxer de log
     */
    private static function writeLog($file, $message) {
        self::initLogDir();
        $filePath = self::$logDir . $file;
        file_put_contents($filePath, $message, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Registra un error
     */
    public static function error($message, $context = []) {
        $formattedMessage = self::formatMessage('ERROR', $message, $context);
        self::writeLog(self::$errorLogFile, $formattedMessage);
        
        // També escriu en el log d'errors de PHP
        error_log($message);
    }
    
    /**
     * Registra un warning
     */
    public static function warning($message, $context = []) {
        $formattedMessage = self::formatMessage('WARNING', $message, $context);
        self::writeLog(self::$errorLogFile, $formattedMessage);
    }
    
    /**
     * Registra informació general
     */
    public static function info($message, $context = []) {
        $formattedMessage = self::formatMessage('INFO', $message, $context);
        self::writeLog(self::$errorLogFile, $formattedMessage);
    }
    
    /**
     * Registra un intent d'accés
     */
    public static function access($action, $success = true, $details = '') {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'N/A';
        $uri = $_SERVER['REQUEST_URI'] ?? 'N/A';
        $user = $_SESSION['usuari'] ?? 'Anonymous';
        $status = $success ? 'SUCCESS' : 'FAILURE';
        
        $logMessage = "[$timestamp] [$status] [IP: $ip] [User: $user] [Method: $method] [URI: $uri] - $action";
        
        if (!empty($details)) {
            $logMessage .= " | Details: $details";
        }
        
        $logMessage .= "\n";
        
        self::writeLog(self::$accessLogFile, $logMessage);
    }
    
    /**
     * Registra un intent de login
     */
    public static function loginAttempt($username, $success, $isAdmin = false) {
        $details = $isAdmin ? 'Admin login' : 'User login';
        if ($success) {
            self::access("Login successful for user: $username", true, $details);
        } else {
            self::access("Login failed for user: $username", false, $details);
            self::warning("Failed login attempt", ['username' => $username]);
        }
    }
    
    /**
     * Registra un logout
     */
    public static function logout($username) {
        self::access("Logout for user: $username", true, 'User logout');
    }
    
    /**
     * Registra operacions sobre productes
     */
    public static function productOperation($operation, $productData = []) {
        self::access("Product operation: $operation", true, json_encode($productData));
        self::info("Product $operation", $productData);
    }
    
    /**
     * Registra excepcions
     */
    public static function exception($exception) {
        $message = $exception->getMessage();
        $trace = $exception->getTraceAsString();
        
        $context = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $trace
        ];
        
        self::error("Exception: $message", $context);
    }
    
    /**
     * Neteja logs antics (opcional)
     */
    public static function cleanOldLogs($days = 30) {
        self::initLogDir();
        $files = glob(self::$logDir . '*.log');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $days) {
                    unlink($file);
                }
            }
        }
    }
}
