<?php
/**
 * Settings Page
 */

if (!Auth::isAdmin()) {
    flashMessage('error', __('error_403_desc'));
    redirect('index.php?page=dashboard');
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => $_POST['site_name'] ?? 'EasyTeam Panel',
        'timezone' => $_POST['timezone'] ?? 'Asia/Tehran',
        'java_path' => $_POST['java_path'] ?? 'java',
        'download_mirror' => $_POST['download_mirror'] ?? 'github',
        'auto_install_java' => $_POST['auto_install_java'] ?? '0',
    ];
    
    foreach ($settings as $key => $value) {
        ServerManager::setSetting($key, $value);
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
];

// Check Java
$javaVersion = ServerManager::checkJavaVersion();
$javaPath = ServerManager::getJavaPath();
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
        <h3><?= __('settings_general') ?></h3>
        <form method="post" class="form">
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
                    <label>
                        <input type="checkbox" name="auto_install_java" value="1" <?= $currentSettings['auto_install_java'] === '1' ? 'checked' : '' ?>>
                        <?= __('settings_auto_install_java') ?>
                    </label>
                    <small><?= __('settings_auto_install_java_desc') ?></small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-save"/></svg> <?= __('save') ?></button>
            </div>
        </form>
    </div>

    <!-- Panel Info -->
    <div class="card" style="margin-top:20px;">
        <h3><svg class="icon"><use href="assets/icons/sprite.svg#icon-info"/></svg> <?= __('info') ?></h3>
        <div class="info-list">
            <div class="info-row">
                <span><?= __('dashboard_panel_version') ?></span>
                <span>v<?= PANEL_VERSION ?></span>
            </div>
            <div class="info-row">
                <span><?= __('dashboard_php_version') ?></span>
                <span>PHP <?= phpversion() ?></span>
            </div>
            <div class="info-row">
                <span>Database</span>
                <span>SQLite</span>
            </div>
            <div class="info-row">
                <span>Storage Path</span>
                <span><code><?= __DIR__ . '/../storage' ?></code></span>
            </div>
        </div>
    </div>
</div>
