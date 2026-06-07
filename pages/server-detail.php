<?php
/**
 * Server Detail Page
 */

$serverId = (int)($_GET['id'] ?? 0);
$server = ServerManager::getServer($serverId);

if (!$server) {
    flashMessage('error', __('servers_error_not_found'));
    redirect('index.php?page=servers');
}

if (!Auth::hasPermission($server['user_id'])) {
    flashMessage('error', __('servers_error_permission'));
    redirect('index.php?page=servers');
}

$status = ServerManager::getServerStatus($serverId);
$serverDir = MINECRAFT_BASE_PATH . '/' . preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($server['name']));
$dirSize = is_dir($serverDir) ? formatFileSize(getDirSize($serverDir)) : '0 B';
?>
<div class="server-detail">
    <div class="page-header">
        <a href="index.php?page=servers" class="btn btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-back"/></svg> <?= __('back') ?></a>
        <h2><?= __('server_detail_title') ?>: <?= htmlspecialchars($server['name']) ?></h2>
    </div>

    <!-- Status Bar -->
    <div class="status-bar status-bar-<?= $status ?>">
        <span class="status-indicator"></span>
        <span><?= __($status === 'online' ? 'server_running' : 'server_offline') ?></span>
        <?php if ($status === 'online' && $server['pid']): ?>
            <?php if (Auth::isAdmin()): ?><span class="status-pid">PID: <?= $server['pid'] ?></span><?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-bar">
        <?php if ($status === 'online'): ?>
            <form method="post" action="index.php?page=servers" style="display:inline;">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="stop">
                <input type="hidden" name="server_id" value="<?= $serverId ?>">
                <button type="submit" class="btn btn-danger"><svg class="icon"><use href="assets/icons/sprite.svg#icon-stop"/></svg>️ <?= __('servers_stop') ?></button>
            </form>
        <?php else: ?>
            <form method="post" action="index.php?page=servers" style="display:inline;">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="start">
                <input type="hidden" name="server_id" value="<?= $serverId ?>">
                <button type="submit" class="btn btn-success"><svg class="icon"><use href="assets/icons/sprite.svg#icon-play"/></svg> <?= __('servers_start') ?></button>
            </form>
        <?php endif; ?>
        <a href="index.php?page=console&id=<?= $serverId ?>" class="btn btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-console"/></svg> <?= __('servers_console') ?></a>
        <a href="index.php?page=files&id=<?= $serverId ?>" class="btn btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-folder"/></svg> <?= __('servers_files') ?></a>
        <span class="dir-size"><svg class="icon"><use href="assets/icons/sprite.svg#icon-package"/></svg> <?= __('files_size') ?>: <?= $dirSize ?></span>
    </div>

    <!-- Server Information -->
    <div class="detail-grid">
        <div class="card">
            <h3><?= __('server_detail_info') ?></h3>
            <div class="info-list">
                <div class="info-row">
                    <span class="info-label"><?= __('server_detail_status') ?></span>
                    <span class="info-value"><span class="status-badge status-<?= $status ?>"><?= __($status === 'online' ? 'online' : 'offline') ?></span></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= __('server_detail_version') ?></span>
                    <span class="info-value"><span class="badge badge-version"><?= htmlspecialchars($server['version']) ?></span> (<?= htmlspecialchars($server['type']) ?>)</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= __('server_detail_port') ?></span>
                    <span class="info-value"><span class="badge badge-port"><?= $server['port'] ?></span></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= __('server_detail_ram') ?></span>
                    <span class="info-value"><?= $server['ram'] ?> MB</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= __('servers_max_players') ?></span>
                    <span class="info-value"><?= $server['max_players'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= __('servers_motd') ?></span>
                    <span class="info-value"><?= htmlspecialchars($server['motd']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= __('servers_gamemode') ?></span>
                    <span class="info-value"><?= ucfirst($server['gamemode']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= __('servers_difficulty') ?></span>
                    <span class="info-value"><?= ucfirst($server['difficulty']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= __('server_detail_created') ?></span>
                    <span class="info-value"><?= $server['created_at'] ?></span>
                </div>
                <?php if ($server['pid'] && $status === 'online'): ?>
                <div class="info-row">
                    <span class="info-label"><?= __('server_detail_pid') ?></span>
                    <span class="info-value"><code><?= $server['pid'] ?></code></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- File system info -->
        <div class="card">
            <h3><?= __('files_title') ?></h3>
            <div class="info-list">
                <div class="info-row">
                    <span class="info-label"><?= __('files_root') ?></span>
                    <span class="info-value"><code><?= htmlspecialchars($serverDir) ?></code></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= __('files_size') ?></span>
                    <span class="info-value"><?= $dirSize ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">EULA</span>
                    <span class="info-value">
                        <?php if (file_exists($serverDir . '/eula.txt')): ?>
                            <svg class="icon"><use href="assets/icons/sprite.svg#icon-check"/></svg> <?= __('yes') ?>
                        <?php else: ?>
                            <svg class="icon"><use href="assets/icons/sprite.svg#icon-close"/></svg> <?= __('no') ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <div style="margin-top:15px;display:flex;gap:10px;flex-wrap:wrap;">
                <a href="index.php?page=files&id=<?= $serverId ?>" class="btn btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-folder"/></svg> <?= __('servers_files') ?></a>
                <a href="index.php?page=console&id=<?= $serverId ?>" class="btn btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-console"/></svg> <?= __('servers_console') ?></a>
            </div>
        </div>
    </div>

    <!-- Delete Server -->
    <div class="card" style="border-color: rgba(244,67,54,0.3);margin-top:20px;">
        <h3 style="color:#f5576c;"><svg class="icon"><use href="assets/icons/sprite.svg#icon-trash"/></svg>️ <?= __('servers_delete') ?></h3>
        <p style="color:#888;margin-bottom:15px;"><?= __('servers_delete_confirm') ?></p>
        <form method="post" action="index.php?page=servers" onsubmit="return confirm('<?= __('servers_delete_confirm') ?>')">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="server_id" value="<?= $serverId ?>">
            <button type="submit" class="btn btn-danger"><svg class="icon"><use href="assets/icons/sprite.svg#icon-trash"/></svg>️ <?= __('servers_delete') ?></button>
        </form>
    </div>
</div>
