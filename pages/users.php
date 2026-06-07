<?php
/**
 * User Management Page (Admin only)
 */

if (!Auth::isAdmin()) {
    flashMessage('error', __('error_403_desc'));
    redirect('index.php?page=dashboard');
}

// Handle user operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['user_action'] ?? '';
    
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        
        $result = Auth::register($username, $email, $password, $role);
        if ($result['success']) {
            flashMessage('success', __('users_create_success'));
        } else {
            flashMessage('error', __($result['error']));
        }
        redirect('index.php?page=users');
    }
    
    if ($action === 'edit') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $language = $_POST['language'] ?? 'fa';
        
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if ($user) {
            $updateData = ['email' => $email, 'role' => $role, 'language' => $language];
            if (!empty($password)) {
                $updateData['password'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            }
            
            $sets = [];
            $values = [];
            foreach ($updateData as $key => $value) {
                $sets[] = "{$key} = ?";
                $values[] = $value;
            }
            $values[] = $userId;
            
            Database::query(
                "UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?",
                $values
            );
            
            flashMessage('success', __('users_update_success'));
        }
        redirect('index.php?page=users');
    }
    
    if ($action === 'delete') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId === Auth::id()) {
            flashMessage('error', __('users_error_self_delete'));
        } else {
            Database::delete('users', 'id = ?', [$userId]);
            flashMessage('success', __('users_delete_success'));
        }
        redirect('index.php?page=users');
    }
}

$users = Database::fetchAll("SELECT * FROM users ORDER BY created_at DESC");
$editUser = null;
if (isset($_GET['edit'])) {
    $editUser = Database::fetch("SELECT * FROM users WHERE id = ?", [(int)$_GET['edit']]);
}
?>
<div class="users-page">
    <div class="page-header">
        <h2><svg class="icon"><use href="assets/icons/sprite.svg#icon-users"/></svg> <?= __('users_title') ?></h2>
        <button class="btn btn-primary" onclick="showCreateUser()"><svg class="icon"><use href="assets/icons/sprite.svg#icon-add"/></svg> <?= __('users_create') ?></button>
    </div>

    <!-- Create User Modal -->
    <div class="modal" id="createUserModal" style="display:none;">
        <div class="modal-backdrop" onclick="closeModal('createUserModal')"></div>
        <div class="modal-content">
            <h3><?= __('users_create') ?></h3>
            <form method="post">
                <input type="hidden" name="user_action" value="create">
                <div class="form-group">
                    <label><?= __('users_username') ?></label>
                    <input type="text" name="username" required minlength="3">
                </div>
                <div class="form-group">
                    <label><?= __('users_email') ?></label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label><?= __('register_password') ?></label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label><?= __('users_role') ?></label>
                    <select name="role" class="form-select">
                        <option value="user"><?= __('users_user') ?></option>
                        <option value="admin"><?= __('users_admin') ?></option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= __('users_create') ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Section -->
    <?php if ($editUser): ?>
    <div class="card form-card">
        <h3><svg class="icon"><use href="assets/icons/sprite.svg#icon-edit"/></svg> <?= __('users_edit') ?>: <?= htmlspecialchars($editUser['username']) ?></h3>
        <form method="post" class="form">
            <input type="hidden" name="user_action" value="edit">
            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label><?= __('users_username') ?></label>
                    <input type="text" value="<?= htmlspecialchars($editUser['username']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label><?= __('users_email') ?></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label><?= __('register_password') ?> <small>(<?= __('users_password_desc') ?>)</small></label>
                    <input type="password" name="password" minlength="6">
                </div>
                <div class="form-group">
                    <label><?= __('users_role') ?></label>
                    <select name="role" class="form-select">
                        <option value="user" <?= $editUser['role'] === 'user' ? 'selected' : '' ?>><?= __('users_user') ?></option>
                        <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>><?= __('users_admin') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= __('settings_language') ?></label>
                    <select name="language" class="form-select">
                        <option value="fa" <?= $editUser['language'] === 'fa' ? 'selected' : '' ?>><?= __('settings_language_fa') ?></option>
                        <option value="en" <?= $editUser['language'] === 'en' ? 'selected' : '' ?>><?= __('settings_language_en') ?></option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <a href="index.php?page=users" class="btn btn-secondary"><?= __('cancel') ?></a>
                <button type="submit" class="btn btn-primary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-save"/></svg> <?= __('save') ?></button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- User List -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('users_id') ?></th>
                    <th><?= __('users_username') ?></th>
                    <th><?= __('users_email') ?></th>
                    <th><?= __('users_role') ?></th>
                    <th><?= __('settings_language') ?></th>
                    <th><?= __('users_last_login') ?></th>
                    <th><?= __('users_created') ?></th>
                    <th><?= __('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="badge badge-<?= $user['role'] === 'admin' ? 'admin' : 'user' ?>">
                            <?= $user['role'] === 'admin' ? __('users_admin') : __('users_user') ?>
                        </span>
                    </td>
                    <td><?= $user['language'] === 'fa' ? 'فارسی' : 'English' ?></td>
                    <td><?= $user['last_login'] ?? '-' ?></td>
                    <td><?= $user['created_at'] ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="index.php?page=users&edit=<?= $user['id'] ?>" class="btn btn-sm btn-secondary"><svg class="icon"><use href="assets/icons/sprite.svg#icon-edit"/></svg></a>
                            <?php if ($user['id'] !== Auth::id()): ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('<?= __('users_delete_confirm') ?>')">
                                <input type="hidden" name="user_action" value="delete">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><svg class="icon"><use href="assets/icons/sprite.svg#icon-trash"/></svg>️</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showCreateUser() {
    document.getElementById('createUserModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>
