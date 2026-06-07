<?php
/**
 * EasyTeam Minecraft Server Panel - Configuration
 * Copy this file to config.php and adjust settings
 */

// Database
define('DB_PATH', __DIR__ . '/storage/database/panel.sqlite');

// Site Settings
define('SITE_NAME', 'EasyTeam Panel');
define('SITE_URL', 'http://localhost:8080');
define('TIMEZONE', 'Asia/Tehran');
define('LANGUAGE', 'fa'); // Default language: fa or en

// Minecraft Server Settings
define('MINECRAFT_BASE_PATH', __DIR__ . '/storage/servers');
define('MINECRAFT_VERSIONS_PATH', __DIR__ . '/storage/versions');
define('MINECRAFT_MIN_RAM', 256);  // MB
define('MINECRAFT_MAX_RAM', 8192); // MB

// Java Settings
define('JAVA_PATH', 'java');

// Session
define('SESSION_NAME', 'EASYTEAM_PANEL');
define('SESSION_LIFETIME', 86400); // 24 hours

// Installation
define('PANEL_VERSION', '1.0.0');
