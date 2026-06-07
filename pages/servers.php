<?php
/**
 * Servers Management Page
 */

$userId = Auth::id();
$isAdmin = Auth::isAdmin();
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'create' || $action === 'create') {
        $postAction = 'create';
        if (!validateCsrf()) {
            flashMessage('error', 'Invalid CSRF token');
            redirect('index.php?page=servers');
        }

        $data = [
            'user_id' => $isAdmin && isset($_POST['user_id']) ? (int)$_POST['user_id'] : $userId,
            'name' => $_POST['name'] ?? '',
            'version' => $_POST['version'] ?? '1.20.1',
            'type' => $_POST['type'] ?? 'paper',
            'port' => (int)($_POST['port'] ?? 25565),
            'ram' => (int)($_POST['ram'] ?? 1024),
            'max_players' => (int)($_POST['max_players'] ?? 20),
            'motd' => $_POST['motd'] ?? 'A Minecraft Server',
            'gamemode' => $_POST['gamemode'] ?? 'survival',
            'difficulty' => $_POST['difficulty'] ?? 'easy',
        ];

        $result = ServerManager::createServer($data);
        if ($result['success']) {
            flashMessage('success', __($result['message']));
        } else {
            flashMessage('error', __($result['error']));
        }
        redirect('index.php?page=servers');
    }

    if ($postAction === 'delete') {
        $serverId = (int)($_POST['server_id'] ?? 0);
        $server = ServerManager::getServer($serverId);
        if ($server && Auth::hasPermission($server['user_id'])) {
            $result = ServerManager::deleteServer($serverId);
            flashMessage($result['success'] ? 'success' : 'error', __($result['message']));
        } else {
            flashMessage('error', __('servers_error_permission'));
        }
        redirect('index.php?page=servers');
    }

    if ($postAction === 'start') {
        $serverId = (int)($_POST['server_id'] ?? 0);
        $server = ServerManager::getServer($serverId);
        if ($server && Auth::hasPermission($server['user_id'])) {
            $result = ServerManager::startServer($serverId);
            if ($result['success']) {
                flashMessage('success', __('servers_start_success'));
            } else {
                flashMessage('error', __($result['error'] ?? 'servers_create_error'));
            }
        }
        redirect('index.php?page=servers');
    }

    if ($postAction === 'stop') {
        $serverId = (int)($_POST['server_id'] ?? 0);
        $server = ServerManager::getServer($serverId);
        if ($server && Auth::hasPermission($server['user_id'])) {
            $result = ServerManager::stopServer($serverId);
            flashMessage($result['success'] ? 'success' : 'error', __($result['message']));
        }
        redirect('index.php?page=servers');
    }
}

// Get servers
if ($isAdmin && (!isset($_GET['user_id']) || $_GET['user_id'] === 'all')) {
    $servers = ServerManager::getAllServers();
} elseif ($isAdmin && isset($_GET['user_id'])) {
    $servers = ServerManager::getUserServers((int)$_GET['user_id']);
} else {
    $servers = ServerManager::getUserServers($userId);
}

// Get installed versions for dropdown
$installedVersions = Database::fetchAll("SELECT DISTINCT version FROM server_versions WHERE installed = 1 ORDER BY version DESC");

// Get all users for admin
$allUsers = [];
if ($isAdmin) {
    $allUsers = Database::fetchAll("SELECT id, username FROM users ORDER BY username");
}
?>
<div class="page-container">
    <div class="page-header">
        <div class="page-header-left">
            <h2><?= __('servers_title') ?></h2>
            <?php if ($isAdmin && !empty($allUsers)): ?>
            <form method="get" class="inline-form">
                <input type="hidden" name="page" value="servers">
                <select name="user_id" onchange="this.form.submit()" class="form-select">
                    <option value="all"><?= __('All Users') ?></option>
                    <?php foreach ($allUsers as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= (isset($_GET['user_id']) && (int)$_GET['user_id'] === $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php endif; ?>
        </div>
        <a href="index.php?page=servers&action=create" class="btn btn-primary">
            <svg class="icon"><use href="assets/icons/sprite.svg#icon-add"/></svg> <?= __('servers_create') ?>
        </a>
    </div>

    <!-- Create Server Modal/Form -->
    <?php if ($action === 'create'): ?>
    <div class="card form-card">
        <h3><?= __('servers_create') ?></h3>
        <form method="post" class="form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name"><?= __('servers_name') ?> *</label>
                    <input type="text" id="name" name="name" required placeholder="My Awesome Server">
                </div>

                <?php if ($isAdmin): ?>
                <div class="form-group">
                    <label for="user_id"><?= __('users_username') ?></label>
                    <select id="user_id" name="user_id" class="form-select">
                        <?php foreach ($allUsers as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="version"><?= __('servers_version') ?> *</label>
                    <select id="version" name="version" class="form-select">
                        <option value="1.21.4">1.21.4</option>
                        <option value="1.21.3">1.21.3</option>
                        <option value="1.21.1">1.21.1</option>
                        <option value="1.21">1.21</option>
                        <option value="1.20.6">1.20.6</option>
                        <option value="1.20.4">1.20.4</option>
                        <option value="1.20.2">1.20.2</option>
                        <option value="1.20.1" selected>1.20.1</option>
                        <option value="1.20">1.20</option>
                        <option value="1.19.4">1.19.4</option>
                        <option value="1.19.2">1.19.2</option>
                        <option value="1.18.2">1.18.2</option>
                        <option value="1.17.1">1.17.1</option>
                        <option value="1.16.5">1.16.5</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="type"><?= __('servers_type') ?></label>
                    <select id="type" name="type" class="form-select">
                        <option value="paper"><?= __('version_type_paper') ?></option>
                        <option value="vanilla"><?= __('version_type_vanilla') ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="port"><?= __('servers_port') ?> *</label>
                    <input type="number" id="port" name="port" value="25565" min="1024" max="65535" required>
                </div>

                <div class="form-group">
                    <label for="ram"><?= __('servers_ram') ?> *</label>
                    <input type="number" id="ram" name="ram" value="1024" min="<?= MINECRAFT_MIN_RAM ?>" max="<?= MINECRAFT_MAX_RAM ?>" required>
                    <small><?= __('servers_enter_ram') ?></small>
                </div>

                <div class="form-group">
                    <label for="max_players"><?= __('servers_max_players') ?></label>
                    <input type="number" id="max_players" name="max_players" value="20" min="1" max="100">
                </div>

                <div class="form-group">
                    <label for="gamemode"><?= __('servers_gamemode') ?></label>
                    <select id="gamemode" name="gamemode" class="form-select">
                        <option value="survival">Survival</option>
                        <option value="creative">Creative</option>
                        <option value="adventure">Adventure</option>
                        <option value="spectator">Spectator</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="difficulty"><?= __('servers_difficulty') ?></label>
                    <select id="difficulty" name="difficulty" class="form-select">
                        <option value="peaceful">Peaceful</option>
                        <option value="easy" selected>Easy</option>
                        <option value="normal">Normal</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="motd"><?= __('servers_motd') ?></label>
                    <input type="text" id="motd" name="motd" value="A Minecraft Server" maxlength="60">
                </div>
            </div>

            <div class="form-actions">
                <a href="index.php?page=servers" class="btn btn-secondary"><?= __('cancel') ?></a>
                <button type="submit" class="btn btn-primary" id="createServerBtn">
                    <svg class="icon" id="createBtnIcon"><use href="assets/icons/sprite.svg#icon-rocket"/></svg>
                    <svg class="icon spin" id="createBtnSpinner" style="display:none;"><use href="assets/icons/sprite.svg#icon-loading"/></svg>
                    <span id="createBtnText"><?= __('servers_create') ?></span>
                </button>
            </div>
        </form>
    </div>
    <script>
    document.querySelector('.form-card form')?.addEventListener('submit', function() {
        var btn = document.getElementById('createServerBtn');
        var icon = document.getElementById('createBtnIcon');
        var spinner = document.getElementById('createBtnSpinner');
        var text = document.getElementById('createBtnText');
        if (btn) { btn.disabled = true; }
        if (icon) { icon.style.display = 'none'; }
        if (spinner) { spinner.style.display = 'inline-block'; }
        if (text) { text.textContent = '<?= __('loading') ?>'; }
    });
    </script>
    <?php endif; ?>

    <!-- Server List -->
    <?php if (empty($servers)): ?>
        <div class="empty-state">
            <div class="empty-icon"><svg class="icon"><use href="assets/icons/sprite.svg#icon-minecraft"/></svg></div>
            <h3><?= __('servers_no_servers') ?></h3>
            <p><?= __('dashboard_no_servers') ?></p>
            <a href="index.php?page=servers&action=create" class="btn btn-primary"><?= __('servers_create') ?></a>
        </div>
    <?php else: ?>
        <div class="server-grid">
            <?php foreach ($servers as $srv): 
                $status = ServerManager::getServerStatus($srv['id']);
                $serverDir = MINECRAFT_BASE_PATH . '/' . preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($srv['name']));
                $hasEula = file_exists($serverDir . '/eula.txt') && strpos(file_get_contents($serverDir . '/eula.txt'), 'eula=true') !== false;
            ?>
            <div class="server-card">
                <div class="server-card-header">
                    <div class="server-card-name"><?= htmlspecialchars($srv['name']) ?></div>
                    <span class="status-badge status-<?= $status ?>">
                        <?= __($status === 'online' ? 'online' : 'offline') ?>
                    </span>
                </div>
                <div class="server-card-body">
                    <div class="server-info-row">
                        <span><svg class="icon"><use href="assets/icons/sprite.svg#icon-refresh"/></svg> <?= __('servers_version') ?>:</span>
                        <span><strong><?= htmlspecialchars($srv['version']) ?></strong></span>
                    </div>
                    <div class="server-info-row">
                        <span><svg class="icon"><use href="assets/icons/sprite.svg#icon-port"/></svg> <?= __('servers_port') ?>:</span>
                        <span><strong><?= $srv['port'] ?></strong></span>
                    </div>
                    <div class="server-info-row">
                        <span><svg class="icon"><use href="assets/icons/sprite.svg#icon-save"/></svg> <?= __('servers_ram') ?>:</span>
                        <span><strong><?= $srv['ram'] ?> MB</strong></span>
                    </div>
                    <?php if (isset($srv['username'])): ?>
                    <div class="server-info-row">
                        <span><svg class="icon"><use href="assets/icons/sprite.svg#icon-user"/></svg> <?= __('users_username') ?>:</span>
                        <span><strong><?= htmlspecialchars($srv['username']) ?></strong></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($srv['pid'] && $status === 'online' && Auth::isAdmin()): ?>
                    <div class="server-info-row">
                        <span><svg class="icon"><use href="assets/icons/sprite.svg#icon-info"/></svg> PID:</span>
                        <span><code><?= $srv['pid'] ?></code></span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="server-card-footer">
                    <?php if ($status === 'online'): ?>
                        <form method="post" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="stop">
                            <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><svg class="icon"><use href="assets/icons/sprite.svg#icon-stop"/></svg>️ <?= __('servers_stop') ?></button>
                        </form>
                        <a href="index.php?page=console&id=<?= $srv['id'] ?>" class="btn btn-sm btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-console"/></svg> <?= __('servers_console') ?></a>
                    <?php else: ?>
                        <form method="post" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="start">
                            <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-success"><svg class="icon"><use href="assets/icons/sprite.svg#icon-play"/></svg> <?= __('servers_start') ?></button>
                        </form>
                    <?php endif; ?>
                    <a href="index.php?page=server-detail&id=<?= $srv['id'] ?>" class="btn btn-sm btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-settings"/></svg></a>
                    <a href="index.php?page=files&id=<?= $srv['id'] ?>" class="btn btn-sm btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-folder"/></svg> <?= __('servers_files') ?></a>
                    <form method="post" style="display:inline;" onsubmit="return confirm('<?= __('servers_delete_confirm') ?>')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><svg class="icon"><use href="assets/icons/sprite.svg#icon-trash"/></svg>️</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
