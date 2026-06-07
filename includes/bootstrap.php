<?php
/**
 * Bootstrap - Initialize the application
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

// Timezone
date_default_timezone_set(TIMEZONE);

// Initialize database
Database::init(DB_PATH);

// Initialize language
Language::init();

// Initialize authentication
Auth::init();

// Set language from session if available
if (isset($_SESSION['language'])) {
    Language::setLanguage($_SESSION['language']);
}

// Check CSRF on POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['page']) && $_GET['page'] !== 'api') {
    // CSRF check happens in individual pages
}

// Check server statuses periodically (once per session)
if (isset($_SESSION['last_status_check']) && time() - $_SESSION['last_status_check'] > 60) {
    ServerManager::checkAllServers();
    $_SESSION['last_status_check'] = time();
} elseif (!isset($_SESSION['last_status_check'])) {
    $_SESSION['last_status_check'] = time();
}

// Create storage directories
$dirs = [
    __DIR__ . '/../storage/servers',
    __DIR__ . '/../storage/database',
    __DIR__ . '/../storage/logs',
    __DIR__ . '/../storage/versions',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
