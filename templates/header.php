<!DOCTYPE html>
<html lang="<?= Language::getCurrentLanguage() ?>" dir="<?= Language::getDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('site_name') ?> - <?= htmlspecialchars($pageTitle ?? __('dashboard')) ?></title>
    
    <!-- Vazir Font (Local) -->
    <style>
        @font-face {
            font-family: 'Vazir';
            src: url('assets/fonts/Vazir.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazir';
            src: url('assets/fonts/Vazir-Bold.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
    </style>
    
    <link rel="stylesheet" href="assets/css/style.css?v=<?= PANEL_VERSION ?>">
    
    <!-- SVG Icon Sprite (preloaded) -->
    <link rel="preload" href="assets/icons/sprite.svg" as="image" type="image/svg+xml">
</head>
<body data-page="<?= htmlspecialchars($page ?? 'login') ?>" data-lang="<?= Language::getCurrentLanguage() ?>">
    <!-- SVG Sprite -->
    <?php $sprite = @file_get_contents(__DIR__ . '/../assets/icons/sprite.svg'); ?>
    <?php if ($sprite): ?>
        <?= $sprite ?>
    <?php else: ?>
        <svg xmlns="http://www.w3.org/2000/svg" style="display:none"><defs></defs></svg>
    <?php endif; ?>

    <?php if (Auth::isLoggedIn()): ?>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <svg class="icon icon-logo"><use href="assets/icons/sprite.svg#icon-minecraft"/></svg>
                </div>
                <div class="sidebar-title"><?= __('site_name') ?></div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php?page=dashboard" data-nav class="nav-item <?= ($page ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-dashboard"/></svg>
                    <span class="nav-text"><?= __('dashboard') ?></span>
                </a>
                <a href="index.php?page=servers" data-nav class="nav-item <?= ($page ?? '') === 'servers' ? 'active' : '' ?>">
                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-server"/></svg>
                    <span class="nav-text"><?= __('servers') ?></span>
                </a>
                <a href="index.php?page=versions" data-nav class="nav-item <?= ($page ?? '') === 'versions' ? 'active' : '' ?>">
                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-package"/></svg>
                    <span class="nav-text"><?= __('version_install_title') ?></span>
                </a>
                
                <?php if (Auth::isAdmin()): ?>
                <div class="nav-section-title"><?= __('settings') ?></div>
                <a href="index.php?page=users" data-nav class="nav-item <?= ($page ?? '') === 'users' ? 'active' : '' ?>">
                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-users"/></svg>
                    <span class="nav-text"><?= __('users') ?></span>
                </a>
                <a href="index.php?page=settings" data-nav class="nav-item <?= ($page ?? '') === 'settings' ? 'active' : '' ?>">
                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-settings"/></svg>
                    <span class="nav-text"><?= __('settings') ?></span>
                </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="user-avatar"><?= strtoupper(substr(Auth::user()['username'] ?? 'U', 0, 1)) ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars(Auth::user()['username'] ?? '') ?></div>
                        <div class="user-role"><?= Auth::isAdmin() ? __('users_admin') : __('users_user') ?></div>
                    </div>
                </div>
                <a href="index.php?page=login&action=logout" class="nav-item logout-btn" onclick="return confirm('<?= __('logout') ?>?')">
                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-logout"/></svg>
                    <span class="nav-text"><?= __('logout') ?></span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <!-- Top Bar -->
            <header class="topbar">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <svg class="icon"><use href="assets/icons/sprite.svg#icon-menu"/></svg>
                </button>
                
                <div class="topbar-left">
                    <h1 class="page-title" id="pageTitle"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
                </div>
                
                <div class="topbar-right">
                    <div class="language-switcher">
                        <a href="index.php?page=api&action=set_language&lang=fa" class="lang-btn <?= Language::getCurrentLanguage() === 'fa' ? 'active' : '' ?>">فا</a>
                        <a href="index.php?page=api&action=set_language&lang=en" class="lang-btn <?= Language::getCurrentLanguage() === 'en' ? 'active' : '' ?>">EN</a>
                    </div>
                    <span class="server-time" id="serverTime"></span>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area" id="appContent">
                <?php $flashes = getFlashMessages(); ?>
                <?php foreach ($flashes as $flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                        <svg class="icon"><use href="assets/icons/sprite.svg#icon-<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : 'check') ?>"/></svg>
                        <span><?= htmlspecialchars($flash['message']) ?></span>
                        <button class="alert-close" onclick="this.parentElement.remove()">
                            <svg class="icon"><use href="assets/icons/sprite.svg#icon-close"/></svg>
                        </button>
                    </div>
                <?php endforeach; ?>
        <?php else: ?>
            <div class="auth-container" id="appContent">
        <?php endif; ?>
