<?php
/**
 * Main Router - Entry point for all requests
 * Supports SPA AJAX navigation and full page loads
 */

// Start output buffering to allow header() calls (redirects) even after HTML output starts
if (ob_get_level() === 0) {
    ob_start();
}

// Load config
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    header('Location: install.php');
    exit;
}
require_once $configFile;

// Autoload includes
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/language.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/server_manager.php';
require_once __DIR__ . '/includes/captcha.php';
require_once __DIR__ . '/includes/bootstrap.php';

// Check if installed
if (!Database::isInstalled() && basename($_SERVER['PHP_SELF']) !== 'install.php') {
    redirect('install.php');
}

// Get page
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';

// API endpoint
if ($page === 'api') {
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/pages/api.php';
    exit;
}

// Public pages (no auth required)
$publicPages = ['login', 'register'];

// Protected pages
if (!in_array($page, $publicPages) && !Auth::isLoggedIn()) {
    if (isset($_GET['ajax'])) {
        header('Location: index.php?page=login');
        exit;
    }
    $_SESSION['redirect_after'] = $_SERVER['REQUEST_URI'];
    flashMessage('warning', __('error_session_expired'));
    redirect('index.php?page=login');
}

// Admin only pages
$adminPages = ['users', 'settings'];
if (in_array($page, $adminPages) && !Auth::isAdmin()) {
    if (isset($_GET['ajax'])) {
        http_response_code(403);
        echo '<div class="alert alert-error">' . __('error_403_desc') . '</div>';
        exit;
    }
    flashMessage('error', __('error_403_desc'));
    redirect('index.php?page=dashboard');
}

// Page routing
$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (!file_exists($pageFile)) {
    $pageFile = __DIR__ . '/pages/dashboard.php';
    $page = 'dashboard';
}

// Check if AJAX request (SPA navigation)
$isAjax = isset($_GET['ajax']) || 
          (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// Capture output for AJAX responses
if ($isAjax) {
    ob_start();
    $pageTitle = __("page_{$page}");
    echo '<title>' . __('site_name') . ' - ' . htmlspecialchars($pageTitle) . '</title>';
    require_once $pageFile;
    $content = ob_get_clean();
    
    // Also capture flash messages
    $flashes = getFlashMessages();
    $flashHtml = '';
    foreach ($flashes as $flash) {
        $icon = $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : 'check');
        $flashHtml .= '<div class="alert alert-' . htmlspecialchars($flash['type']) . '">'
                    . '<svg class="icon"><use href="assets/icons/sprite.svg#icon-' . $icon . '"/></svg> '
                    . htmlspecialchars($flash['message'])
                    . '<button class="alert-close" onclick="this.parentElement.remove()">'
                    . '<svg class="icon"><use href="assets/icons/sprite.svg#icon-close"/></svg></button></div>';
    }
    
    echo $flashHtml . $content;
    exit;
}

// Security Headers (sent on every response)
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Content Security Policy (allows local assets)
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self';");

// Full page load
$pageTitle = __("page_{$page}");
require_once __DIR__ . '/templates/header.php';
require_once $pageFile;
require_once __DIR__ . '/templates/footer.php';

// Flush output buffer
while (ob_get_level() > 0) {
    ob_end_flush();
}
