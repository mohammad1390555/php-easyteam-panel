<?php
/**
 * Authentication Class
 */

class Auth {
    private static ?array $currentUser = null;

    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
            if ($user) {
                self::$currentUser = $user;
                // Update language from user settings
                if (isset($user['language'])) {
                    Language::setLanguage($user['language']);
                }
            } else {
                self::logout();
            }
        }
    }

    public static function login(string $username, string $password): bool {
        $user = Database::fetch(
            "SELECT * FROM users WHERE username = ? OR email = ?",
            [$username, $username]
        );

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            self::$currentUser = $user;

            Database::query(
                "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?",
                [$user['id']]
            );

            if (isset($user['language'])) {
                Language::setLanguage($user['language']);
            }

            return true;
        }

        return false;
    }

    public static function register(string $username, string $email, string $password, string $role = 'user'): array {
        // Validate
        if (strlen($username) < 3) {
            return ['success' => false, 'error' => 'register_error_username_length'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'register_error_password_length'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email address'];
        }

        // Check existing
        $existing = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            return ['success' => false, 'error' => 'register_error_username_exists'];
        }

        $existing = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            return ['success' => false, 'error' => 'register_error_email_exists'];
        }

        // Create user
        $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $userId = Database::insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashed,
            'role' => $role,
            'language' => Language::getCurrentLanguage(),
        ]);

        return ['success' => true, 'user_id' => $userId];
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        self::$currentUser = null;
    }

    public static function user(): ?array {
        return self::$currentUser;
    }

    public static function id(): ?int {
        return self::$currentUser['id'] ?? null;
    }

    public static function isLoggedIn(): bool {
        return self::$currentUser !== null;
    }

    public static function isAdmin(): bool {
        return self::$currentUser && self::$currentUser['role'] === 'admin';
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after'] = $_SERVER['REQUEST_URI'];
            flashMessage('warning', __('error_session_expired'));
            redirect('index.php?page=login');
        }
    }

    public static function requireAdmin(): void {
        self::requireLogin();
        if (!self::isAdmin()) {
            flashMessage('error', __('error_403_desc'));
            redirect('index.php?page=dashboard');
        }
    }

    public static function hasPermission(int $serverUserId): bool {
        if (self::isAdmin()) return true;
        return self::id() === $serverUserId;
    }
}
