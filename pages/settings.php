<?php
/**
 * Settings Page - Full configuration
 */

if (!Auth::isAdmin()) {
    flashMessage('error', __('error_403_desc'));
    redirect('index.php?page=dashboard');
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        flashMessage('error', 'Invalid form submission');
        redirect('index.php?page=settings');
    }
    
    $settings = [
        'site_name' => $_POST['site_name'] ?? 'EasyTeam Panel',
        'timezone' => $_POST['timezone'] ?? 'Asia/Tehran',
        'java_path' => $_POST['java_path'] ?? 'java',
        'download_mirror' => $_POST['download_mirror'] ?? 'github',
        'auto_install_java' => $_POST['auto_install_java'] ?? '0',
        'default_ram' => $_POST['default_ram'] ?? '1024',
        'default_port' => $_POST['default_port'] ?? '25565',
        'max_port' => $_POST['max_port'] ?? '65535',
        'min_port' => $_POST['min_port'] ?? '1024',
        'default_max_players' => $_POST['default_max_players'] ?? '20',
        'console_poll_interval' => $_POST['console_poll_interval'] ?? '2000',
        'session_lifetime' => $_POST['session_lifetime'] ?? '86400',
        'enable_registration' => $_POST['enable_registration'] ?? '0',
        'default_language' => $_POST['default_language'] ?? 'fa',
    ];
    
    foreach ($settings as $key => $value) {
        ServerManager::setSetting($key, $value);
    }
    
    // Apply session lifetime immediately
    if (isset($settings['session_lifetime'])) {
        $lifetime = (int)$settings['session_lifetime'];
        if ($lifetime >= 3600 && $lifetime <= 604800) {
            ini_set('session.gc_maxlifetime', $lifetime);
            if (session_status() === PHP_SESSION_ACTIVE) {
                setcookie(session_name(), session_id(), [
                    'lifetime' => $lifetime,
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
        }
    }
    
    flashMessage('success', __('settings_saved'));
    redirect('index.php?page=settings');
}

// Load current settings
$currentSettings = [
    'site_name' => ServerManager::getSetting('site_name', 'EasyTeam Panel'),
    'timezone' => ServerManager::getSetting('timezone', 'Asia/Tehran'),
    'java_path' => ServerManager::getSetting('java_path', 'java'),
    'download_mirror' => ServerManager::getSetting('download_mirror', 'github'),
    'auto_install_java' => ServerManager::getSetting('auto_install_java', '0'),
    'default_ram' => ServerManager::getSetting('default_ram', '1024'),
    'default_port' => ServerManager::getSetting('default_port', '25565'),
    'max_port' => ServerManager::getSetting('max_port', '65535'),
    'min_port' => ServerManager::getSetting('min_port', '1024'),
    'default_max_players' => ServerManager::getSetting('default_max_players', '20'),
    'console_poll_interval' => ServerManager::getSetting('console_poll_interval', '2000'),
    'session_lifetime' => ServerManager::getSetting('session_lifetime', '86400'),
    'enable_registration' => ServerManager::getSetting('enable_registration', '1'),
    'default_language' => ServerManager::getSetting('default_language', 'fa'),
];

// Check Java
$javaVersion = ServerManager::checkJavaVersion();
$javaPath = ServerManager::getJavaPath();

// Get installed versions count
$installedVersions = Database::fetch("SELECT COUNT(*) as count FROM server_versions WHERE installed = 1")['count'] ?? 0;
$totalServers = Database::fetch("SELECT COUNT(*) as count FROM servers")['count'] ?? 0;
$totalUsers = Database::fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;

// Get storage usage
$storageSize = 0;
foreach (['servers', 'versions', 'logs', 'database'] as $dir) {
    $path = __DIR__ . '/../storage/' . $dir;
    if (is_dir($path)) {
        $storageSize += getDirSize($path);
    }
}
?>
<div class="settings-page">
    <div class="page-header">
        <h2><svg class="icon"><use href="assets/icons/sprite.svg#icon-settings"/></svg> <?= __('settings_title') ?></h2>
    </div>

    <!-- Java Status -->
    <div class="card" style="margin-bottom:20px;">
        <h3><svg class="icon"><use href="assets/icons/sprite.svg#icon-java"/></svg> <?= __('server_install_java_title') ?></h3>
        <?php if ($javaPath && $javaVersion): ?>
            <div class="alert alert-success">
                <svg class="icon"><use href="assets/icons/sprite.svg#icon-check"/></svg> <?= __('server_java_installed') ?><br>
                <small><?= nl2br(htmlspecialchars($javaVersion)) ?></small><br>
                <small><?= __('settings_java_path') ?>: <code><?= htmlspecialchars($javaPath) ?></code></small>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <svg class="icon"><use href="assets/icons/sprite.svg#icon-warning"/></svg> <?= __('server_no_java') ?><br>
                <small><?= __('server_install_java_desc') ?></small>
            </div>
            <form method="post" action="index.php?page=api&action=install_java" style="margin-top:10px;">
                <button type="submit" class="btn btn-primary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-java"/></svg> <?= __('server_install_java_btn') ?></button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Settings Form -->
    <div class="card">
        <h3><svg class="icon"><use href="assets/icons/sprite.svg#icon-settings"/></svg> <?= __('settings_general') ?></h3>
        <form method="post" class="form">
            <?= csrfField() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="site_name"><?= __('settings_site_name') ?></label>
                    <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($currentSettings['site_name']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="timezone"><?= __('settings_timezone') ?></label>
                    <select id="timezone" name="timezone" class="form-select">
                        <?php
                        $timezones = ['Asia/Tehran', 'Asia/Dubai', 'Asia/Baghdad', 'Europe/London', 'Europe/Berlin', 'America/New_York', 'America/Los_Angeles', 'UTC'];
                        foreach ($timezones as $tz):
                        ?>
                            <option value="<?= $tz ?>" <?= $currentSettings['timezone'] === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="default_language"><?= __('settings_language') ?></label>
                    <select id="default_language" name="default_language" class="form-select">
                        <option value="fa" <?= $currentSettings['default_language'] === 'fa' ? 'selected' : '' ?>><?= __('settings_language_fa') ?></option>
                        <option value="en" <?= $currentSettings['default_language'] === 'en' ? 'selected' : '' ?>><?= __('settings_language_en') ?></option>
                    </select>
                    <small><?= __('Default language for new users') ?></small>
                </div>

                <div class="form-group">
                    <label for="java_path"><?= __('settings_java_path') ?></label>
                    <input type="text" id="java_path" name="java_path" value="<?= htmlspecialchars($currentSettings['java_path']) ?>">
                    <small><?= __('settings_java_path_desc') ?></small>
                </div>

                <div class="form-group">
                    <label for="download_mirror"><?= __('settings_download_mirror') ?></label>
                    <select id="download_mirror" name="download_mirror" class="form-select">
                        <option value="github" <?= $currentSettings['download_mirror'] === 'github' ? 'selected' : '' ?>><?= __('settings_mirror_github') ?></option>
                        <option value="direct" <?= $currentSettings['download_mirror'] === 'direct' ? 'selected' : '' ?>><?= __('settings_mirror_direct') ?></option>
                    </select>
                    <small><?= __('settings_download_mirror_desc') ?></small>
                </div>

                <div class="form-group">
                    <label for="default_ram"><?= __('Default RAM (MB)') ?></label>
                    <input type="number" id="default_ram" name="default_ram" value="<?= htmlspecialchars($currentSettings['default_ram']) ?>" min="256" max="8192">
                </div>

                <div class="form-group">
                    <label for="default_port"><?= __('Default Port') ?></label>
                    <input type="number" id="default_port" name="default_port" value="<?= htmlspecialchars($currentSettings['default_port']) ?>" min="1024" max="65535">
                </div>

                <div class="form-group">
                    <label for="default_max_players"><?= __('Default Max Players') ?></label>
                    <input type="number" id="default_max_players" name="default_max_players" value="<?= htmlspecialchars($currentSettings['default_max_players']) ?>" min="1" max="100">
                </div>

                <div class="form-group">
                    <label for="console_poll_interval"><?= __('Console Poll Interval (ms)') ?></label>
                    <input type="number" id="console_poll_interval" name="console_poll_interval" value="<?= htmlspecialchars($currentSettings['console_poll_interval']) ?>" min="500" max="10000">
                    <small><?= __('Lower = faster updates, higher = less CPU usage') ?></small>
                </div>

                <div class="form-group">
                    <label for="session_lifetime"><?= __('Session Lifetime (seconds)') ?></label>
                    <input type="number" id="session_lifetime" name="session_lifetime" value="<?= htmlspecialchars($currentSettings['session_lifetime']) ?>" min="3600" max="604800">
                    <small><?= __('Default: 86400 (24 hours)') ?></small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="auto_install_java" value="1" <?= $currentSettings['auto_install_java'] === '1' ? 'checked' : '' ?>>
                        <?= __('settings_auto_install_java') ?>
                    </label>
                    <small><?= __('settings_auto_install_java_desc') ?></small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="enable_registration" value="1" <?= $currentSettings['enable_registration'] === '1' ? 'checked' : '' ?>>
                        <?= __('Enable Public Registration') ?>
                    </label>
                    <small><?= __('Allow new users to register') ?></small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-save"/></svg> <?= __('save') ?></button>
            </div>
        </form>
    </div>

    <!-- Storage Stats -->
    <div class="card" style="margin-top:20px;">
        <h3><svg class="icon"><use href="assets/icons/sprite.svg#icon-package"/></svg> <?= __('Storage & Statistics') ?></h3>
        <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(120px,1fr));">
            <div class="info-card">
                <span class="info-label"><?= __('Total Storage') ?></span>
                <span class="info-value"><?= formatFileSize($storageSize) ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><?= __('Installed Versions') ?></span>
                <span class="info-value"><?= $installedVersions ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><?= __('Total Servers') ?></span>
                <span class="info-value"><?= $totalServers ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><?= __('Total Users') ?></span>
                <span class="info-value"><?= $totalUsers ?></span>
            </div>
        </div>
        
        <!-- Available local versions -->
        <div style="margin-top:15px;">
            <h4 style="font-size:13px;margin-bottom:10px;color:var(--text-sec);"><?= __('Local Downloaded Versions') ?></h4>
            <?php
            $localJars = glob(MINECRAFT_VERSIONS_PATH . '/*.jar');
            if (!empty($localJars)):
            ?>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php foreach ($localJars as $jar): 
                    $name = basename($jar);
                    $size = formatFileSize(filesize($jar));
                ?>
                <span class="badge badge-version" title="<?= $size ?>">
                    <?= htmlspecialchars(str_replace('.jar', '', $name)) ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color:var(--text-muted);font-size:12px;"><?= __('No local versions found') ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panel Info -->
    <div class="card" style="margin-top:20px;">
        <h3><svg class="icon"><use href="assets/icons/sprite.svg#icon-info"/></svg> <?= __('info') ?></h3>
        <div class="info-list">
            <div class="info-row">
                <span class="info-label"><?= __('dashboard_panel_version') ?></span>
                <span class="info-value">v<?= PANEL_VERSION ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><?= __('dashboard_php_version') ?></span>
                <span class="info-value">PHP <?= phpversion() ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Database</span>
                <span class="info-value">SQLite</span>
            </div>
            <div class="info-row">
                <span class="info-label">Storage Path</span>
                <span class="info-value"><code><?= __DIR__ . '/../storage' ?></code></span>
            </div>
            <div class="info-row">
                <span class="info-label">Versions Path</span>
                <span class="info-value"><code><?= MINECRAFT_VERSIONS_PATH ?></code></span>
            </div>
        </div>
    </div>
</div>
