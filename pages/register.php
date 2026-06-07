<?php
/**
 * Register Page
 */

if (Auth::isLoggedIn()) {
    redirect('index.php?page=dashboard');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = __('register_error_password_match'); // Generic error
    } elseif ($password !== $confirmPassword) {
        $error = __('register_error_password_match');
    } else {
        $result = Auth::register($username, $email, $password, 'user');
        if ($result['success']) {
            flashMessage('success', __('register_success'));
            redirect('index.php?page=login');
        } else {
            $error = __($result['error']);
        }
    }
}
?>
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo"><svg class="icon"><use href="assets/icons/sprite.svg#icon-minecraft"/></svg></div>
        <h1><?= __('site_name') ?></h1>
        <p><?= __('register_title') ?></p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
        <div class="form-group">
            <label for="username"><?= __('register_username') ?></label>
            <input type="text" id="username" name="username" required minlength="3" autofocus
                   placeholder="<?= __('register_username') ?>">
        </div>
        <div class="form-group">
            <label for="email"><?= __('register_email') ?></label>
            <input type="email" id="email" name="email" required
                   placeholder="<?= __('register_email') ?>">
        </div>
        <div class="form-group">
            <label for="password"><?= __('register_password') ?></label>
            <input type="password" id="password" name="password" required minlength="6"
                   placeholder="<?= __('register_password') ?>">
        </div>
        <div class="form-group">
            <label for="confirm_password"><?= __('register_confirm') ?></label>
            <input type="password" id="confirm_password" name="confirm_password" required
                   placeholder="<?= __('register_confirm') ?>">
        </div>
        <button type="submit" class="btn btn-primary"><?= __('register_btn') ?></button>
    </form>

    <div class="auth-footer">
        <p><?= __('register_have_account') ?> <a href="index.php?page=login"><?= __('login') ?></a></p>
    </div>
</div>
