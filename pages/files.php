<?php
/**
 * File Manager Page
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

$basePath = MINECRAFT_BASE_PATH . '/' . preg_replace('/[^a-zA-Z0-9\-]/', '-', strtolower($server['name']));
$currentPath = $_GET['path'] ?? '';
$fullPath = realpath($basePath . '/' . $currentPath);

// Security: prevent directory traversal
if (!$fullPath || strpos($fullPath, realpath($basePath)) !== 0) {
    $fullPath = realpath($basePath);
    $currentPath = '';
}

// Handle file operations
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        $error = __('Invalid form submission');
    }
    
    $fileAction = $_POST['file_action'] ?? '';
    
    if ($fileAction === 'upload' && isset($_FILES['file'])) {
        $maxFileSize = 100 * 1024 * 1024; // 100MB max
        $file = $_FILES['file'];
        
        // Validate mime type (if fileinfo extension available)
        $allowedMimes = ['application/octet-stream', 'text/plain', 'text/x-java-source', 'application/json', 'text/xml', 'application/x-yaml', 'text/html', 'text/css', 'application/javascript', 'image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'application/zip', 'application/gzip', 'application/java-archive'];
        $mimeType = '';
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mimeType = @finfo_file($finfo, $file['tmp_name']);
                @finfo_close($finfo);
            }
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = __('files_upload_error');
        } elseif ($file['size'] > $maxFileSize) {
            $error = __('File size exceeds the maximum limit of 100 MB');
        } elseif (!empty($mimeType) && !in_array($mimeType, $allowedMimes, true)) {
            $error = __('File type not allowed');
        } else {
            $safeName = preg_replace('/[^\w\-. ]/', '_', basename($file['name']));
            if (empty($safeName)) $safeName = 'upload_' . time();
            $targetFile = $fullPath . '/' . $safeName;
            if (!file_exists($targetFile) && move_uploaded_file($file['tmp_name'], $targetFile)) {
                $success = __('files_upload_success');
            } elseif (file_exists($targetFile)) {
                $error = __('File already exists');
            } else {
                $error = __('files_upload_error');
            }
        }
    }
    
    if ($fileAction === 'create_file') {
        $name = trim($_POST['name'] ?? '');
        if (!empty($name)) {
            $filePath = $fullPath . '/' . basename($name);
            if (!file_exists($filePath)) {
                file_put_contents($filePath, '');
                $success = __('files_create_success');
            } else {
                $error = __('File already exists');
            }
        }
    }
    
    if ($fileAction === 'create_folder') {
        $name = trim($_POST['name'] ?? '');
        if (!empty($name)) {
            $folderPath = $fullPath . '/' . basename($name);
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0755);
                $success = __('files_create_success');
            } else {
                $error = __('Folder already exists');
            }
        }
    }
    
    if ($fileAction === 'delete') {
        $target = $_POST['target'] ?? '';
        $targetPath = realpath($basePath . '/' . $target);
        if ($targetPath && strpos($targetPath, realpath($basePath)) === 0 && $targetPath !== realpath($basePath)) {
            if (is_file($targetPath)) {
                unlink($targetPath);
            } elseif (is_dir($targetPath)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($targetPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $file) {
                    if ($file->isDir()) rmdir($file->getRealPath());
                    else unlink($file->getRealPath());
                }
                rmdir($targetPath);
            }
            $success = __('files_delete_success');
        }
    }
    
    if ($fileAction === 'rename') {
        $oldName = $_POST['old_name'] ?? '';
        $newName = $_POST['new_name'] ?? '';
        $oldPath = realpath($basePath . '/' . $oldName);
        
        // Validate new name doesn't contain path traversal
        $safeNewName = basename($newName);
        if (empty($safeNewName)) $safeNewName = basename($oldName);
        
        $newPath = $basePath . '/' . dirname($oldName) . '/' . $safeNewName;
        
        if ($oldPath && strpos($oldPath, realpath($basePath)) === 0 && !empty($newName) && $safeNewName === $newName) {
            if (!file_exists($newPath)) {
                rename($oldPath, $newPath);
                $success = __('files_rename_success');
            } else {
                $error = __('File already exists');
            }
        }
    }
    
    if ($fileAction === 'save_file') {
        $target = $_POST['target'] ?? '';
        $content = $_POST['content'] ?? '';
        $targetPath = realpath($basePath . '/' . $target);
        
        if ($targetPath && strpos($targetPath, realpath($basePath)) === 0 && is_file($targetPath) && is_writable($targetPath)) {
            file_put_contents($targetPath, $content);
            $success = __('files_save_success');
        } else {
            $error = __('files_error_permission');
        }
    }
}

// Get directory contents
$items = [];
if (is_dir($fullPath)) {
    $dirHandle = opendir($fullPath);
    if ($dirHandle) {
        while (($entry = readdir($dirHandle)) !== false) {
            if ($entry === '.' || $entry === '..') continue;
            $entryPath = $fullPath . '/' . $entry;
            $isDir = is_dir($entryPath);
            $items[] = [
                'name' => $entry,
                'path' => ($currentPath ? $currentPath . '/' : '') . $entry,
                'is_dir' => $isDir,
                'size' => $isDir ? 0 : filesize($entryPath),
                'modified' => date('Y-m-d H:i:s', filemtime($entryPath)),
                'type' => $isDir ? 'folder' : getFileType($entryPath),
                'icon' => $isDir ? '<svg class="icon"><use href="assets/icons/sprite.svg#icon-folder"/></svg>' : getFileIcon($entryPath),
                'permissions' => substr(sprintf('%o', fileperms($entryPath)), -4),
            ];
        }
        closedir($dirHandle);
    }
    
    // Sort: folders first, then by name
    usort($items, function($a, $b) {
        if ($a['is_dir'] !== $b['is_dir']) return $a['is_dir'] ? -1 : 1;
        return strnatcasecmp($a['name'], $b['name']);
    });
}

// Check if viewing/editing a file
$editFile = null;
$editContent = '';
if (isset($_GET['edit'])) {
    $editPath = realpath($basePath . '/' . $_GET['edit']);
    if ($editPath && strpos($editPath, realpath($basePath)) === 0 && is_file($editPath)) {
        $ext = strtolower(pathinfo($editPath, PATHINFO_EXTENSION));
        $textExtensions = ['txt', 'json', 'yml', 'yaml', 'xml', 'php', 'html', 'css', 'js', 'sh', 'properties', 'cfg', 'conf', 'md', 'log', 'env', 'gitignore', 'ini', 'toml', 'svg'];
        if (in_array($ext, $textExtensions)) {
            $editFile = $_GET['edit'];
            $editContent = file_get_contents($editPath);
        }
    }
}
?>
<div class="files-page">
    <div class="page-header">
        <a href="index.php?page=server-detail&id=<?= $serverId ?>" class="btn btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-back"/></svg> <?= __('back') ?></a>
        <h2><svg class="icon"><use href="assets/icons/sprite.svg#icon-folder"/></svg> <?= __('files_title') ?> - <?= htmlspecialchars($server['name']) ?></h2>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php?page=files&id=<?= $serverId ?>" class="breadcrumb-item">/</a>
        <?php 
        $parts = explode('/', $currentPath);
        $cumulative = '';
        foreach ($parts as $part) {
            if (empty($part)) continue;
            $cumulative .= '/' . $part;
            echo '<span class="breadcrumb-sep">/</span>';
            echo '<a href="index.php?page=files&id=' . $serverId . '&path=' . urlencode(ltrim($cumulative, '/')) . '" class="breadcrumb-item">' . htmlspecialchars($part) . '</a>';
        }
        ?>
    </div>

    <!-- Actions Bar -->
    <div class="files-actions">
        <form method="post" enctype="multipart/form-data" class="upload-form">
            <?= csrfField() ?>
            <input type="hidden" name="file_action" value="upload">
            <label class="btn btn-sm btn-secondary upload-btn">
                <svg class="icon"><use href="assets/icons/sprite.svg#icon-upload"/></svg> <?= __('files_upload') ?>
                <input type="file" name="file" onchange="this.form.submit()" style="display:none">
            </label>
        </form>
        
        <button class="btn btn-sm btn-secondary" onclick="showCreateModal('file')"><svg class="icon"><use href="assets/icons/sprite.svg#icon-file"/></svg> <?= __('files_new_file') ?></button>
        <button class="btn btn-sm btn-secondary" onclick="showCreateModal('folder')"><svg class="icon"><use href="assets/icons/sprite.svg#icon-folder"/></svg> <?= __('files_new_folder') ?></button>
    </div>

    <!-- Edit File -->
    <?php if ($editFile !== null): ?>
    <div class="card edit-card">
        <div class="edit-header">
            <h3><svg class="icon"><use href="assets/icons/sprite.svg#icon-edit"/></svg> <?= __('files_edit') ?>: <?= htmlspecialchars(basename($editFile)) ?></h3>
            <a href="index.php?page=files&id=<?= $serverId ?>&path=<?= urlencode($currentPath) ?>" class="btn btn-sm btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-close"/></svg> <?= __('cancel') ?></a>
        </div>
        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="file_action" value="save_file">
            <input type="hidden" name="target" value="<?= htmlspecialchars($editFile) ?>">
            <textarea name="content" class="edit-textarea" spellcheck="false"><?= htmlspecialchars($editContent) ?></textarea>
            <div class="form-actions">
                <a href="index.php?page=files&id=<?= $serverId ?>&path=<?= urlencode($currentPath) ?>" class="btn btn-secondary"><?= __('cancel') ?></a>
                <button type="submit" class="btn btn-primary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-save"/></svg> <?= __('files_save') ?></button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- File List -->
    <div class="file-list">
        <?php if ($currentPath !== ''): ?>
        <div class="file-item file-item-folder" onclick="window.location='index.php?page=files&id=<?= $serverId ?>&path=<?= urlencode(dirname($currentPath) !== '.' ? dirname($currentPath) : '') ?>'">
            <span class="file-icon"><svg class="icon"><use href="assets/icons/sprite.svg#icon-folder-open"/></svg></span>
            <span class="file-name">.. (<?= __('files_parent') ?>)</span>
            <span class="file-size"></span>
            <span class="file-modified"></span>
        </div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="empty-state" style="padding:40px;">
                <p><?= __('files_no_files') ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
            <div class="file-item file-item-<?= $item['is_dir'] ? 'folder' : 'file' ?>">
                <span class="file-icon"><?= $item['icon'] ?></span>
                
                <?php if ($item['is_dir']): ?>
                    <a href="index.php?page=files&id=<?= $serverId ?>&path=<?= urlencode($item['path']) ?>" class="file-name"><?= htmlspecialchars($item['name']) ?></a>
                <?php elseif ($item['type'] === 'text'): ?>
                    <a href="index.php?page=files&id=<?= $serverId ?>&path=<?= urlencode($currentPath) ?>&edit=<?= urlencode($item['path']) ?>" class="file-name"><?= htmlspecialchars($item['name']) ?></a>
                <?php else: ?>
                    <span class="file-name"><?= htmlspecialchars($item['name']) ?></span>
                <?php endif; ?>
                
                <span class="file-size"><?= $item['is_dir'] ? '-' : formatFileSize($item['size']) ?></span>
                <span class="file-modified"><?= $item['modified'] ?></span>
                <span class="file-perms"><?= $item['permissions'] ?></span>
                <span class="file-actions">
                    <button class="btn btn-xs btn-secondary" onclick='renameItem("<?= htmlspecialchars($item['path'], ENT_QUOTES, 'UTF-8') ?>", "<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>");'><svg class="icon"><use href="assets/icons/sprite.svg#icon-edit"/></svg></button>
                    <button class="btn btn-xs btn-danger" onclick='deleteItem("<?= htmlspecialchars($item['path'], ENT_QUOTES, 'UTF-8') ?>");'><svg class="icon"><use href="assets/icons/sprite.svg#icon-trash"/></svg>️</button>
                </span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Modals -->
<div class="modal" id="createModal" style="display:none;">
    <div class="modal-backdrop" onclick="closeCreateModal()"></div>
    <div class="modal-content">
        <h3 id="modalTitle"><?= __('files_new_file') ?></h3>
        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="file_action" id="modalAction" value="create_file">
            <div class="form-group">
                <label id="modalLabel" for="modalName"><?= __('files_new_file_name') ?></label>
                <input type="text" id="modalName" name="name" required autofocus>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()"><?= __('cancel') ?></button>
                <button type="submit" class="btn btn-primary"><?= __('create') ?></button>
            </div>
        </form>
    </div>
</div>

<form method="post" id="deleteForm" style="display:none;">
    <?= csrfField() ?>
    <input type="hidden" name="file_action" value="delete">
    <input type="hidden" name="target" id="deleteTarget">
</form>

<form method="post" id="renameForm" style="display:none;">
    <?= csrfField() ?>
    <input type="hidden" name="file_action" value="rename">
    <input type="hidden" name="old_name" id="renameOldName">
    <input type="hidden" name="new_name" id="renameNewName">
</form>

<script>
function showCreateModal(type) {
    document.getElementById('createModal').style.display = 'flex';
    document.getElementById('modalAction').value = type === 'folder' ? 'create_folder' : 'create_file';
    document.getElementById('modalTitle').textContent = type === 'folder' ? '<?= __('files_new_folder') ?>' : '<?= __('files_new_file') ?>';
    document.getElementById('modalLabel').textContent = type === 'folder' ? '<?= __('files_new_folder_name') ?>' : '<?= __('files_new_file_name') ?>';
    document.getElementById('modalName').value = '';
    setTimeout(() => document.getElementById('modalName').focus(), 100);
}

function closeCreateModal() {
    document.getElementById('createModal').style.display = 'none';
}

function deleteItem(path) {
    if (confirm('<?= __('files_delete_confirm') ?>')) {
        document.getElementById('deleteTarget').value = path;
        document.getElementById('deleteForm').submit();
    }
}

function renameItem(path, name) {
    const newName = prompt('<?= __('files_rename') ?>:', name);
    if (newName && newName !== name) {
        document.getElementById('renameOldName').value = path;
        document.getElementById('renameNewName').value = newName;
        document.getElementById('renameForm').submit();
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeCreateModal();
});
</script>
