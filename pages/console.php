<?php
/**
 * Console Page - Real-time server console with command history and quick commands
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

// Common quick commands for Minecraft servers
$quickCommands = [
    ['cmd' => 'list', 'label' => '👥 List', 'desc' => 'List online players'],
    ['cmd' => 'op', 'label' => '⭐ Op', 'desc' => 'Op a player (type /op player)'],
    ['cmd' => 'deop', 'label' => '🔽 Deop', 'desc' => 'Deop a player'],
    ['cmd' => 'gamemode creative', 'label' => '🎨 GM C', 'desc' => 'Creative mode'],
    ['cmd' => 'gamemode survival', 'label' => '⚔️ GM S', 'desc' => 'Survival mode'],
    ['cmd' => 'time set day', 'label' => '☀️ Day', 'desc' => 'Set time to day'],
    ['cmd' => 'time set night', 'label' => '🌙 Night', 'desc' => 'Set time to night'],
    ['cmd' => 'weather clear', 'label' => '🌤️ Clear', 'desc' => 'Clear weather'],
    ['cmd' => 'weather rain', 'label' => '🌧️ Rain', 'desc' => 'Rainy weather'],
    ['cmd' => 'kill @e[type=!player]', 'label' => '💀 Clear', 'desc' => 'Kill all entities'],
    ['cmd' => 'save-all', 'label' => '💾 Save', 'desc' => 'Save the world'],
    ['cmd' => 'stop', 'label' => '⏹️ Stop', 'desc' => 'Stop the server'],
];
?>
<div class="console-page" data-server-id="<?= $serverId ?>" data-server-status="<?= $status ?>">
    <div class="page-header">
        <a href="index.php?page=server-detail&id=<?= $serverId ?>" class="btn btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-back"/></svg> <?= __('back') ?></a>
        <h2><svg class="icon"><use href="assets/icons/sprite.svg#icon-console"/></svg> <?= __('console_title') ?> - <?= htmlspecialchars($server['name']) ?></h2>
        <div class="console-status">
            <span class="status-badge status-<?= $status ?>" id="consoleStatus">
                <?= __($status === 'online' ? 'online' : 'offline') ?>
            </span>
            <button class="btn btn-sm btn-secondary" id="clearConsoleBtn" title="<?= __('console_clear') ?>"><svg class="icon"><use href="assets/icons/sprite.svg#icon-trash"/></svg></button>
        </div>
    </div>

    <!-- Quick Commands (only when online) -->
    <?php if ($status === 'online'): ?>
    <div class="quick-commands" style="display:flex;gap:4px;margin-bottom:8px;flex-wrap:wrap;">
        <?php foreach ($quickCommands as $qc): ?>                <button class="btn btn-xs btn-secondary quick-cmd-btn" 
                        data-cmd="<?= htmlspecialchars($qc['cmd'], ENT_QUOTES, 'UTF-8') ?>"
                        title="<?= htmlspecialchars($qc['desc'], ENT_QUOTES, 'UTF-8') ?>"
                        onclick="document.getElementById('commandInput').value = '<?= htmlspecialchars($qc['cmd'], ENT_QUOTES, 'UTF-8') ?> '; document.getElementById('commandInput').focus();">
            <?= htmlspecialchars($qc['label'], ENT_QUOTES, 'UTF-8') ?>
        </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

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
            <button type="submit" class="btn btn-primary" id="consoleSendBtn" <?= $status !== 'online' ? 'disabled' : '' ?>>
                <svg class="icon" id="sendBtnIcon"><use href="assets/icons/sprite.svg#icon-send"/></svg>
                <span id="sendBtnText"><?= __('console_send') ?></span>
                <svg class="icon spin" id="sendBtnSpinner" style="display:none;"><use href="assets/icons/sprite.svg#icon-loading"/></svg>
            </button>
        </form>
        <label class="auto-scroll-label">
            <input type="checkbox" id="autoScroll" checked>
            <?= __('console_auto_scroll') ?>
        </label>
    </div>
</div>

<script>
// Console form loading state
document.getElementById('consoleForm')?.addEventListener('submit', function() {
    var btn = document.getElementById('consoleSendBtn');
    var icon = document.getElementById('sendBtnIcon');
    var spinner = document.getElementById('sendBtnSpinner');
    var text = document.getElementById('sendBtnText');
    if (btn) btn.disabled = true;
    if (icon) icon.style.display = 'none';
    if (spinner) spinner.style.display = 'inline-block';
    if (text) text.textContent = '...';
    // Re-enable after short delay
    setTimeout(function() {
        if (btn) btn.disabled = false;
        if (icon) icon.style.display = 'inline-block';
        if (spinner) spinner.style.display = 'none';
        if (text) text.textContent = '<?= __('console_send') ?>';
    }, 1000);
});
</script>
