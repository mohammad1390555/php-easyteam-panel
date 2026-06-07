<?php
/**
 * Built-in PHP Development Server Router
 * Usage: php server.php
 *        php -S localhost:8080 server.php
 */

// Serve static files directly
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Check if file exists in the public directory
$filePath = __DIR__ . $path;
if (is_file($filePath)) {
    // Set proper MIME types
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];
    
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    
    return false; // Let PHP serve the file
}

// Route everything to index.php
require __DIR__ . '/index.php';
