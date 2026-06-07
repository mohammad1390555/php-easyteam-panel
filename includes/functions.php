<?php
/**
 * Helper Functions
 */

/**
 * Check if a port is available
 */
function is_port_available(int $port): bool {
    $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.5);
    if (is_resource($connection)) {
        fclose($connection);
        return false;
    }
    return true;
}

/**
 * Get the public IP address
 */
function getPublicIp(): string {
    $ip = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
    if ($ip === '0.0.0.0' || $ip === '::') {
        $ip = gethostbyname(gethostname());
    }
    return $ip;
}

/**
 * Format file size
 */
function formatFileSize(int $bytes): string {
    if ($bytes === 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 1) . ' ' . $sizes[$i];
}

/**
 * Get directory size recursively
 */
function getDirSize(string $path): int {
    $size = 0;
    if (!is_dir($path)) return 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    return $size;
}

/**
 * Get file icon based on extension
 */
function getFileIcon(string $filename): string {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'txt' => '📄',
        'json' => '📋',
        'yml' => '⚙️',
        'yaml' => '⚙️',
        'xml' => '📰',
        'php' => '🐘',
        'html' => '🌐',
        'css' => '🎨',
        'js' => '📜',
        'sh' => '💻',
        'jar' => '📦',
        'zip' => '🗜️',
        'tar' => '🗜️',
        'gz' => '🗜️',
        'png' => '🖼️',
        'jpg' => '🖼️',
        'jpeg' => '🖼️',
        'gif' => '🖼️',
        'svg' => '🖼️',
        'ico' => '🖼️',
        'log' => '📝',
        'dat' => '💾',
        'properties' => '⚙️',
    ];
    return $icons[$ext] ?? '📄';
}

/**
 * Get file type category
 */
function getFileType(string $filename): string {
    if (is_dir($filename)) return 'folder';
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $imageTypes = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'bmp'];
    $textTypes = ['txt', 'json', 'yml', 'yaml', 'xml', 'php', 'html', 'css', 'js', 'sh', 'properties', 'cfg', 'conf', 'md', 'log', 'env', 'gitignore'];
    if (in_array($ext, $imageTypes)) return 'image';
    if (in_array($ext, $textTypes)) return 'text';
    if ($ext === 'jar') return 'jar';
    if (in_array($ext, ['zip', 'tar', 'gz', 'rar', '7z'])) return 'archive';
    return 'other';
}

/**
 * Generate a random password
 */
function generatePassword(int $length = 12): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Sanitize filename
 */
function sanitizeFilename(string $name): string {
    $name = preg_replace('/[^\w\-\. ]/', '', $name);
    $name = preg_replace('/\s+/', '_', $name);
    return trim($name, '_-.');
}

/**
 * Check if running on CLI or web server
 */
function isCli(): bool {
    return php_sapi_name() === 'cli';
}

/**
 * Get system resource usage
 */
function getSystemResources(): array {
    return [
        'memory' => [
            'total' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
        ],
        'disk' => [
            'free' => disk_free_space('/'),
            'total' => disk_total_space('/'),
        ],
        'load' => function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0],
    ];
}

/**
 * Validate CSRF token
 */
function validateCsrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        $stored = $_SESSION['csrf_token'] ?? '';
        return hash_equals($stored, $token);
    }
    return true;
}

/**
 * Generate CSRF token
 */
function csrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF hidden input field
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * JSON response helper
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Redirect helper
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Add flash message
 */
function flashMessage(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash messages
 */
function getFlashMessages(): array {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/**
 * Check if a string contains Persian characters
 */
function containsPersian(string $text): bool {
    return preg_match('/[\x{0600}-\x{06FF}\x{FB8A}\x{067E}\x{0686}\x{06AF}\x{0698}\x{200C}]/u', $text) === 1;
}
