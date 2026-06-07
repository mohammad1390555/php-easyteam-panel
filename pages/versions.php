<?php
/**
 * Version Installer Page
 */

$action = $_GET['action'] ?? 'list';
$formAction = $_POST['action'] ?? $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Handle version install from API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $formAction === 'install') {
    if (!validateCsrf()) {
        $error = __('Invalid form submission');
    } else {
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
}

// Handle JAR file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $formAction === 'upload_jar') {
    if (!validateCsrf()) {
        $error = __('Invalid form submission');
    } else {
        $versionName = trim($_POST['version_name'] ?? '');
        $type = trim($_POST['type'] ?? 'custom');
        $customType = trim($_POST['custom_type'] ?? '');

        if ($type === 'custom' && !empty($customType)) {
            $type = $customType;
        }

        if (empty($versionName)) {
            $error = __('version_upload_error_version');
        } elseif (empty($_FILES['jar_file']) || $_FILES['jar_file']['error'] !== UPLOAD_ERR_OK) {
            $error = __('version_upload_error_file');
        } else {
            $file = $_FILES['jar_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($ext !== 'jar') {
                $error = __('version_upload_error_ext');
            } elseif ($file['size'] > 500 * 1024 * 1024) {
                $error = __('version_upload_error_size');
            } else {
                $versionsDir = MINECRAFT_VERSIONS_PATH;
                if (!is_dir($versionsDir)) {
                    mkdir($versionsDir, 0755, true);
                }

                // Sanitize inputs to prevent path traversal
                $safeType = preg_replace('/[^a-zA-Z0-9_-]/', '', $type);
                $safeVersion = preg_replace('/[^a-zA-Z0-9._-]/', '', $versionName);

                if (empty($safeType) || empty($safeVersion)) {
                    $error = __('version_upload_error_version');
                } elseif (!preg_match('/^[0-9]+\.[0-9]+(\.[0-9]+)?$/', $safeVersion)) {
                    $error = __('version_upload_error_version');
                } else {
                    $jarName = $safeType . '-' . $safeVersion . '.jar';
                    $jarPath = $versionsDir . '/' . $jarName;

                    if (move_uploaded_file($file['tmp_name'], $jarPath)) {
                        // Register in database
                        $existing = Database::fetch(
                            "SELECT * FROM server_versions WHERE version = ? AND type = ?",
                            [$safeVersion, $safeType]
                        );

                        if ($existing) {
                            Database::query(
                                "UPDATE server_versions SET installed = 1, path = ?, installed_at = ? WHERE id = ?",
                                [$jarPath, date('Y-m-d H:i:s'), $existing['id']]
                            );
                        } else {
                            Database::insert('server_versions', [
                                'version' => $safeVersion,
                                'type' => $safeType,
                                'installed' => 1,
                                'path' => $jarPath,
                                'installed_at' => date('Y-m-d H:i:s'),
                            ]);
                        }

                        $success = __('version_upload_success') . ' (' . $jarName . ')';
                    } else {
                        $error = __('version_upload_error_move');
                    }
                }
            }
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
                                <?= csrfField() ?>
                                <input type="hidden" name="version" value="<?= htmlspecialchars($av['version']) ?>">
                                <input type="hidden" name="type" value="paper">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-package"/></svg> <?= __('version_type_paper') ?>
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if ($av['types']['vanilla'] ?? false): ?>
                            <form method="post" style="display:inline;">
                                <?= csrfField() ?>
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

    <!-- Upload JAR Manually -->
    <div class="card" style="margin-top:20px;">
        <h3><svg class="icon"><use href="assets/icons/sprite.svg#icon-upload"/></svg> <?= __('version_upload_title') ?></h3>
        <p style="color:var(--text-sec);font-size:12px;margin-bottom:14px;"><?= __('version_upload_desc') ?></p>
        <form method="post" enctype="multipart/form-data" class="form upload-jar-form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="upload_jar">
            <div class="form-grid">
                <div class="form-group">
                    <label for="version_name"><?= __('version_upload_version_label') ?></label>
                    <input type="text" id="version_name" name="version_name" required
                           placeholder="<?= __('version_upload_version_placeholder') ?>"
                           pattern="[0-9]+\.[0-9]+(\.[0-9]+)?"
                           title="<?= __('version_upload_version_placeholder') ?>">
                    <small><?= __('version_upload_version_help') ?></small>
                </div>
                <div class="form-group">
                    <label for="type"><?= __('version_upload_type_label') ?></label>
                    <select id="type" name="type" class="form-select" onchange="toggleCustomType(this)">
                        <option value="paper">Paper</option>
                        <option value="vanilla">Vanilla</option>
                        <option value="spigot">Spigot</option>
                        <option value="fabric">Fabric</option>
                        <option value="forge">Forge</option>
                        <option value="custom"><?= __('version_upload_type_custom') ?></option>
                    </select>
                </div>
                <div class="form-group custom-type-group" style="display:none;">
                    <label for="custom_type"><?= __('version_upload_type_custom_label') ?></label>
                    <input type="text" id="custom_type" name="custom_type" placeholder="e.g. purpur">
                </div>
                <div class="form-group full-width">
                    <label for="jar_file"><?= __('version_upload_file_label') ?></label>
                    <div class="file-input-wrapper" style="display:flex;gap:10px;align-items:center;">
                        <input type="file" id="jar_file" name="jar_file" accept=".jar" required
                               style="flex:1;padding:9px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);background:rgba(255,255,255,0.04);color:var(--text);font-size:13px;cursor:pointer;">
                    </div>
                    <small><?= __('version_upload_file_help') ?></small>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-upload"/></svg> <?= __('version_upload_btn') ?>
                </button>
            </div>
        </form>
    </div>

    <script>
    function toggleCustomType(select) {
        var group = select.closest('.form-grid').querySelector('.custom-type-group');
        if (group) {
            group.style.display = select.value === 'custom' ? 'block' : 'none';
            var input = group.querySelector('input');
            if (input) input.required = select.value === 'custom';
        }
    }
    </script>

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
                                    <?php
                                    $typeLabels = [
                                        'paper' => __('version_type_paper'),
                                        'vanilla' => __('version_type_vanilla'),
                                        'spigot' => __('version_type_spigot'),
                                        'fabric' => __('version_type_fabric'),
                                        'forge' => __('version_type_forge'),
                                    ];
                                    echo $typeLabels[$iv['type']] ?? htmlspecialchars(ucfirst($iv['type']));
                                    ?>
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
