<?php
/**
 * Version Installer Page
 */

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'install') {
    $version = $_POST['version'] ?? '';
    $type = $_POST['type'] ?? 'paper';
    
    if (!empty($version)) {
        $result = ServerManager::installVersion($version, $type);
        if ($result['success']) {
            $success = __($result['message']) . ' (' . $version . ' - ' . $type . ')';
        } else {
            $error = __($result['error']);
        }
    }
}

// Get installed versions
$installedVersions = Database::fetchAll(
    "SELECT * FROM server_versions WHERE installed = 1 ORDER BY version DESC"
);

// Get available versions from API
$availableVersions = ServerManager::getAvailableVersions();
?>
<div class="versions-page">
    <div class="page-header">
        <h2><svg class="icon"><use href="assets/icons/sprite.svg#icon-package"/></svg> <?= __('version_install_title') ?></h2>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <!-- Install New Version -->
    <div class="card">
        <h3><?= __('version_available') ?></h3>
        <?php if (empty($availableVersions)): ?>
            <p style="color:#888;"><?= __('version_no_versions') ?></p>
        <?php else: ?>
            <div class="version-grid">
                <?php foreach ($availableVersions as $av): ?>
                <div class="version-card">
                    <div class="version-number">MC <?= htmlspecialchars($av['version']) ?></div>
                    <div class="version-types">
                        <?php if ($av['types']['paper'] ?? false): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="version" value="<?= htmlspecialchars($av['version']) ?>">
                                <input type="hidden" name="type" value="paper">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-package"/></svg> <?= __('version_type_paper') ?>
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if ($av['types']['vanilla'] ?? false): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="version" value="<?= htmlspecialchars($av['version']) ?>">
                                <input type="hidden" name="type" value="vanilla">
                                <button type="submit" class="btn btn-sm btn-secondary">
                                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-package"/></svg> <?= __('version_type_vanilla') ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="version-mirror-info">
                        <small style="color:#888;">
                            <svg class="icon"><use href="assets/icons/sprite.svg#icon-globe"/></svg> <?= __('version_mirror') ?>: <?= __(ServerManager::getSetting('download_mirror', 'github') === 'github' ? 'settings_mirror_github' : 'settings_mirror_direct') ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Installed Versions -->
    <div class="card" style="margin-top:20px;">
        <h3><?= __('version_installed') ?></h3>
        <?php if (empty($installedVersions)): ?>
            <p style="color:#888;text-align:center;padding:20px;"><?= __('version_no_versions') ?></p>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= __('servers_version') ?></th>
                            <th><?= __('servers_type') ?></th>
                            <th><?= __('status') ?></th>
                            <th><?= __('files_modified') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($installedVersions as $iv): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($iv['version']) ?></strong></td>
                            <td>
                                <span class="badge badge-version">
                                    <?= $iv['type'] === 'paper' ? __('version_type_paper') : __('version_type_vanilla') ?>
                                </span>
                            </td>
                            <td><span class="status-badge status-online"><svg class="icon"><use href="assets/icons/sprite.svg#icon-check"/></svg> <?= __('installed') ?></span></td>
                            <td><?= htmlspecialchars($iv['installed_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
