<?php
/**
 * Dashboard Page
 */

$userId = Auth::id();
$isAdmin = Auth::isAdmin();

// Stats
if ($isAdmin) {
    $totalServers = Database::fetch("SELECT COUNT(*) as count FROM servers")['count'] ?? 0;
    $onlineServers = Database::fetch("SELECT COUNT(*) as count FROM servers WHERE status = 'online'")['count'] ?? 0;
    $totalUsers = Database::fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
    $servers = Database::fetchAll("SELECT s.*, u.username FROM servers s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC LIMIT 5");
} else {
    $totalServers = Database::fetch("SELECT COUNT(*) as count FROM servers WHERE user_id = ?", [$userId])['count'] ?? 0;
    $onlineServers = Database::fetch("SELECT COUNT(*) as count FROM servers WHERE user_id = ? AND status = 'online'", [$userId])['count'] ?? 0;
    $totalUsers = 0;
    $servers = Database::fetchAll("SELECT * FROM servers WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$userId]);
}

$offlineServers = $totalServers - $onlineServers;

// Java version
$javaVersion = ServerManager::checkJavaVersion();
?>
<div class="dashboard">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);"><svg class="icon"><use href="assets/icons/sprite.svg#icon-server"/></svg></div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalServers ?></div>
                <div class="stat-label"><?= __('dashboard_total_servers') ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);"><svg class="icon"><use href="assets/icons/sprite.svg#icon-check"/></svg></div>
            <div class="stat-info">
                <div class="stat-value" style="color:#43e97b;"><?= $onlineServers ?></div>
                <div class="stat-label"><?= __('dashboard_online_servers') ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);"><svg class="icon"><use href="assets/icons/sprite.svg#icon-error"/></svg></div>
            <div class="stat-info">
                <div class="stat-value" style="color:#f5576c;"><?= $offlineServers ?></div>
                <div class="stat-label"><?= __('dashboard_offline_servers') ?></div>
            </div>
        </div>
        <?php if ($isAdmin): ?>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);"><svg class="icon"><use href="assets/icons/sprite.svg#icon-users"/></svg></div>
            <div class="stat-info">
                <div class="stat-value" style="color:#4facfe;"><?= $totalUsers ?></div>
                <div class="stat-label"><?= __('dashboard_total_users') ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="section">
        <h2 class="section-title"><?= __('dashboard_quick_actions') ?></h2>
        <div class="quick-actions">
            <a href="index.php?page=servers&action=create" class="action-card">
                <span class="action-icon"><svg class="icon"><use href="assets/icons/sprite.svg#icon-add"/></svg></span>
                <span class="action-text"><?= __('dashboard_create_server') ?></span>
            </a>
            <a href="index.php?page=servers" class="action-card">
                <span class="action-icon"><svg class="icon"><use href="assets/icons/sprite.svg#icon-list"/></svg></span>
                <span class="action-text"><?= __('dashboard_view_servers') ?></span>
            </a>
            <a href="index.php?page=versions" class="action-card">
                <span class="action-icon"><svg class="icon"><use href="assets/icons/sprite.svg#icon-package"/></svg></span>
                <span class="action-text"><?= __('version_install_title') ?></span>
            </a>
            <a href="index.php?page=settings" class="action-card">
                <span class="action-icon"><svg class="icon"><use href="assets/icons/sprite.svg#icon-settings"/></svg></span>
                <span class="action-text"><?= __('settings') ?></span>
            </a>
        </div>
    </div>

    <!-- Recent Servers -->
    <div class="section">
        <h2 class="section-title"><?= __('dashboard_recent_servers') ?></h2>
        <?php if (empty($servers)): ?>
            <div class="empty-state">
                <div class="empty-icon"><svg class="icon"><use href="assets/icons/sprite.svg#icon-minecraft"/></svg></div>
                <p><?= __('dashboard_no_servers') ?></p>
                <a href="index.php?page=servers&action=create" class="btn btn-primary"><?= __('servers_create') ?></a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= __('servers_name') ?></th>
                            <th><?= __('servers_version') ?></th>
                            <th><?= __('servers_port') ?></th>
                            <th><?= __('servers_ram') ?></th>
                            <th><?= __('status') ?></th>
                            <th><?= __('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servers as $srv): 
                            $status = ServerManager::getServerStatus($srv['id']);
                        ?>
                        <tr>
                            <td class="server-name"><?= htmlspecialchars($srv['name']) ?></td>
                            <td><span class="badge badge-version"><?= htmlspecialchars($srv['version']) ?></span></td>
                            <td><span class="badge badge-port"><?= $srv['port'] ?></span></td>
                            <td><?= $srv['ram'] ?> MB</td>
                            <td>
                                <span class="status-badge status-<?= $status ?>">
                                    <?= __($status === 'online' ? 'online' : 'offline') ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="index.php?page=server-detail&id=<?= $srv['id'] ?>" class="btn btn-sm btn-secondary" title="<?= __('edit') ?>"><svg class="icon"><use href="assets/icons/sprite.svg#icon-eye"/></svg></a>
                                    <a href="index.php?page=console&id=<?= $srv['id'] ?>" class="btn btn-sm btn-secondary" title="<?= __('servers_console') ?>"><svg class="icon"><use href="assets/icons/sprite.svg#icon-console"/></svg></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- System Info -->
    <div class="section">
        <h2 class="section-title"><?= __('dashboard_system_info') ?></h2>
        <div class="info-grid">
            <div class="info-card">
                <span class="info-label"><?= __('dashboard_php_version') ?></span>
                <span class="info-value">PHP <?= phpversion() ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><?= __('dashboard_java_version') ?></span>
                <span class="info-value"><?= $javaVersion ? explode("\n", $javaVersion)[0] : __('server_no_java') ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><?= __('dashboard_panel_version') ?></span>
                <span class="info-value">v<?= PANEL_VERSION ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><?= __('settings_language') ?></span>
                <span class="info-value"><?= Language::getCurrentLanguage() === 'fa' ? 'فارسی' : 'English' ?></span>
            </div>
        </div>
    </div>
</div>
