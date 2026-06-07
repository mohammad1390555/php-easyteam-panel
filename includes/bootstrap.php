<?php
/**
 * Bootstrap - Initialize the application (optimized)
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

// Performance: Enable OPcache revalidation once per session
ini_set('opcache.revalidate_freq', '60');

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

// Check server statuses periodically (only when needed)
$statusCheckInterval = (int)(ServerManager::getSetting('status_check_interval', '30'));
if (!isset($_SESSION['last_status_check']) || (time() - $_SESSION['last_status_check']) > $statusCheckInterval) {
    ServerManager::checkAllServers();
    $_SESSION['last_status_check'] = time();
}

// Create storage directories (only once per session)
if (!isset($_SESSION['_storage_dirs_created'])) {
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
    $_SESSION['_storage_dirs_created'] = true;
}
