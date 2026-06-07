<?php
/**
 * Login Page with Google reCAPTCHA v2
 */

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    Auth::logout();
    flashMessage('success', __('logout_success'));
    redirect('index.php?page=login');
}

// Already logged in
if (Auth::isLoggedIn()) {
    redirect('index.php?page=dashboard');
}

$error = '';
$recaptchaSiteKey = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'; // Google test key

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // Verify reCAPTCHA
    $recaptchaSecret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'; // Google test key
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $verifyData = [
        'secret' => $recaptchaSecret,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'],
    ];

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($verifyData),
            'timeout' => 5,
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);

    $verifyResult = @file_get_contents($verifyUrl, false, $context);
    $recaptchaValid = false;
    
    if ($verifyResult) {
        $recaptchaData = json_decode($verifyResult, true);
        $recaptchaValid = $recaptchaData['success'] ?? false;
    }

    if (!$recaptchaValid) {
        $error = 'لطفاً تأیید امنیتی را کامل کنید';
    } elseif (empty($username) || empty($password)) {
        $error = __('login_error');
    } elseif (Auth::login($username, $password)) {
        $redirect = $_SESSION['redirect_after'] ?? 'index.php?page=dashboard';
        unset($_SESSION['redirect_after']);
        redirect($redirect);
    } else {
        $error = __('login_error');
    }
}

$flashes = getFlashMessages();
?>
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <svg class="icon icon-48"><use href="assets/icons/sprite.svg#icon-minecraft"/></svg>
        </div>
        <h1><?= __('site_name') ?></h1>
        <p><?= __('login_title') ?></p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <svg class="icon"><use href="assets/icons/sprite.svg#icon-error"/></svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>
    <?php foreach ($flashes as $flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <svg class="icon"><use href="assets/icons/sprite.svg#icon-<?= $flash['type'] === 'success' ? 'check' : 'error' ?>"/></svg>
            <span><?= htmlspecialchars($flash['message']) ?></span>
        </div>
    <?php endforeach; ?>

    <form method="post" class="auth-form">
        <div class="form-group">
            <label for="username">
                <svg class="icon icon-16"><use href="assets/icons/sprite.svg#icon-user"/></svg>
                <?= __('login_username') ?>
            </label>
            <input type="text" id="username" name="username" required autofocus
                   placeholder="<?= __('login_username') ?>">
        </div>
        <div class="form-group">
            <label for="password">
                <svg class="icon icon-16"><use href="assets/icons/sprite.svg#icon-key"/></svg>
                <?= __('login_password') ?>
            </label>
            <input type="password" id="password" name="password" required
                   placeholder="<?= __('login_password') ?>">
        </div>
        
        <!-- Google reCAPTCHA -->
        <div class="form-group recaptcha-container" style="display:flex;justify-content:center;margin-bottom:16px;">
            <div class="g-recaptcha" data-sitekey="<?= $recaptchaSiteKey ?>"></div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <svg class="icon"><use href="assets/icons/sprite.svg#icon-login"/></svg>
            <?= __('login_btn') ?>
        </button>
    </form>

    <div class="auth-footer">
        <p><?= __('login_no_account') ?> <a href="index.php?page=register" data-nav><?= __('register') ?></a></p>
        <div class="language-switcher" style="margin-top:12px;justify-content:center;">
            <a href="index.php?page=api&action=set_language&lang=fa&redirect=login" class="lang-btn <?= Language::getCurrentLanguage() === 'fa' ? 'active' : '' ?>">فا</a>
            <a href="index.php?page=api&action=set_language&lang=en&redirect=login" class="lang-btn <?= Language::getCurrentLanguage() === 'en' ? 'active' : '' ?>">EN</a>
        </div>
    </div>
</div>

<!-- Google reCAPTCHA Script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
