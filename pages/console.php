<?php
/**
 * Console Page - Real-time server console
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
$consoleOutput = ServerManager::getConsoleOutput($serverId, 50);
?>
<div class="console-page" data-server-id="<?= $serverId ?>" data-server-status="<?= $status ?>">
    <div class="page-header">
        <a href="index.php?page=server-detail&id=<?= $serverId ?>" class="btn btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-back"/></svg> <?= __('back') ?></a>
        <h2><svg class="icon"><use href="assets/icons/sprite.svg#icon-console"/></svg> <?= __('console_title') ?> - <?= htmlspecialchars($server['name']) ?></h2>
        <div class="console-status">
            <span class="status-badge status-<?= $status ?>" id="consoleStatus">
                <?= __($status === 'online' ? 'online' : 'offline') ?>
            </span>
            <button class="btn btn-sm btn-secondary" id="clearConsoleBtn"><svg class="icon"><use href="assets/icons/sprite.svg#icon-trash"/></svg> <?= __('console_clear') ?></button>
        </div>
    </div>

    <!-- Console Output -->
    <div class="console-container">
        <div class="console-output" id="consoleOutput">
            <?php if (empty($consoleOutput)): ?>
                <div class="console-empty"><?= __('console_no_output') ?></div>
            <?php else: ?>
                <?php foreach ($consoleOutput as $line): ?>
                    <div class="console-line"><?= htmlspecialchars($line) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Command Input -->
    <div class="console-input-bar">
        <form id="consoleForm" class="console-form" autocomplete="off">
            <input type="hidden" name="server_id" value="<?= $serverId ?>">
            <input type="text" name="command" id="commandInput" class="console-input"
                   placeholder="<?= __('console_placeholder') ?>"
                   <?= $status !== 'online' ? 'disabled' : '' ?>
                   autofocus>
            <button type="submit" class="btn btn-primary" <?= $status !== 'online' ? 'disabled' : '' ?>>
                <?= __('console_send') ?>
            </button>
        </form>
        <label class="auto-scroll-label">
            <input type="checkbox" id="autoScroll" checked>
            <?= __('console_auto_scroll') ?>
        </label>
    </div>
</div>
