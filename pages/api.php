<?php
/**
 * API Handler - AJAX and JSON endpoints
 */

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'error' => 'Invalid action'];

try {    // Set language
    if ($action === 'set_language') {
        $lang = $_GET['lang'] ?? 'fa';
        Language::setLanguage($lang);
        
        // Update user preference if logged in
        if (Auth::isLoggedIn()) {
            Database::query("UPDATE users SET language = ? WHERE id = ?", [$lang, Auth::id()]);
        }
        
        // Restore previous page from redirect param (preserves context like id=5)
        $redirectRaw = $_GET['redirect'] ?? '';
        if ($redirectRaw !== '') {
            // Validate: only allow page name with optional &key=value params
            if (preg_match('#^[a-z-]+(&[a-z_]+=[a-zA-Z0-9_.%/-]+)*$#', $redirectRaw)) {
                $parts = explode('&', $redirectRaw);
                $pageName = $parts[0];
                $allowedRedirects = ['login', 'register', 'dashboard', 'servers', 'server-detail', 'console', 'files', 'settings', 'versions', 'users', 'home'];
                if (in_array($pageName, $allowedRedirects, true)) {
                    redirect('index.php?page=' . $redirectRaw);
                }
            }
        }
        
        // Safe referer fallback - only redirect to same host path
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $refererHost = $referer ? (parse_url($referer, PHP_URL_HOST) ?: '') : '';
        $expectedHost = $_SERVER['HTTP_HOST'] ?? '';
        if ($refererHost !== '' && $refererHost === $expectedHost) {
            $refererPath = parse_url($referer, PHP_URL_PATH) ?: '';
            $refererQuery = parse_url($referer, PHP_URL_QUERY) ?: '';
            $redirectUrl = $refererPath . ($refererQuery ? '?' . $refererQuery : '');
            if (strpos($redirectUrl, 'index.php') !== false) {
                redirect($redirectUrl);
            }
        }
        redirect('index.php?page=dashboard');
    }
    
    // Console command
    if ($action === 'console_command' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $serverId = (int)($_POST['server_id'] ?? 0);
        $command = $_POST['command'] ?? '';
        
        $server = ServerManager::getServer($serverId);
        if ($server && Auth::hasPermission($server['user_id'])) {
            $result = ServerManager::sendConsoleCommand($serverId, $command);
            $response = $result;
        } else {
            $response = ['success' => false, 'error' => 'Permission denied'];
        }
    }
    
    // Get console output
    if ($action === 'console_output') {
        $serverId = (int)($_GET['server_id'] ?? 0);
        $server = ServerManager::getServer($serverId);
        
        if ($server && Auth::hasPermission($server['user_id'])) {
            $output = ServerManager::getConsoleOutput($serverId, 100);
            $status = ServerManager::getServerStatus($serverId);
            $response = [
                'success' => true,
                'output' => $output,
                'server_status' => $status,
            ];
        } else {
            $response = ['success' => false, 'error' => 'Permission denied'];
        }
    }
    
    // Install Java
    if ($action === 'install_java') {
        if (!Auth::isAdmin()) {
            $response = ['success' => false, 'error' => 'Permission denied'];
        } else {
            $result = ServerManager::installJava();
            if ($result['success']) {
                flashMessage('success', __($result['message']));
            } else {
                flashMessage('error', __($result['error']));
            }
            redirect('index.php?page=settings');
        }
    }

    // Get server status (for AJAX polling)
    if ($action === 'server_status') {
        $serverId = (int)($_GET['server_id'] ?? 0);
        $server = ServerManager::getServer($serverId);
        
        if ($server && Auth::hasPermission($server['user_id'])) {
            $status = ServerManager::getServerStatus($serverId);
            $response = [
                'success' => true,
                'status' => $status,
                'pid' => $server['pid'],
            ];
        }
    }
    
} catch (Exception $e) {
    $response = ['success' => false, 'error' => $e->getMessage()];
}

// Don't send JSON for redirect responses
if (!in_array($action, ['set_language', 'install_java'])) {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
