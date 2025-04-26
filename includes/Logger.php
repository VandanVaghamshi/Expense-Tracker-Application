<?php
/**
 * Logger Class
 * 
 * Provides logging functionality for the application.
 */

class Logger {
    private static $logFile = __DIR__ . '/../logs/app.log';
    
    /**
     * Initialize logger
     */
    public static function init() {
        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }
    
    /**
     * Log a message with a specific level
     * 
     * @param string $level Log level (INFO, ERROR, etc)
     * @param string $message Message to log
     * @param array $context Additional context data
     */
    public static function log($level, $message, array $context = []) {
        self::init();
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message$contextStr\n";
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Log an info message
     */
    public static function info($message, array $context = []) {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log an error message
     */
    public static function error($message, array $context = []) {
        self::log('ERROR', $message, $context);
    }
}