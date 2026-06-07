<?php
/**
 * Login Page with CuteCaptcha - Self-hosted anti-bot system
 * No external dependencies - works offline and for Iranian users
 */

// Handle logout - check action safely
$getAction = $_GET['action'] ?? '';
if ($getAction === 'logout') {
    Auth::logout();
    flashMessage('success', __('logout_success'));
    redirect('index.php?page=login');
}

// Already logged in
if (Auth::isLoggedIn()) {
    redirect('index.php?page=dashboard');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captchaAnswer = $_POST['captcha_answer'] ?? '';

    if (!validateCsrf()) {
        $error = __('security_csrf_error');
    } elseif (!CuteCaptcha::verify($captchaAnswer)) {
        $error = __('captcha_error');
    } elseif (empty($username) || empty($password)) {
        $error = __('login_error');
    } elseif (!Auth::checkRateLimit()) {
        $remaining = ceil(Auth::getRateLimitTime() / 60);
        $lang = Language::getCurrentLanguage();
        $error = $lang === 'fa'
            ? 'تعداد تلاش‌های ناموفق بیش از حد مجاز است. لطفاً ' . $remaining . ' دقیقه دیگر تلاش کنید.'
            : 'Too many failed attempts. Please try again in ' . $remaining . ' minutes.';
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
<div class="auth-card-3d-wrap" id="authCard3dWrap">
    <div class="auth-card" id="authCard">
        <div class="auth-header">
            <div class="auth-logo">
                <svg class="icon icon-48"><use href="assets/icons/sprite.svg#icon-minecraft"/></svg>
            </div>
            <h1><?= __('site_name') ?></h1>
            <p id="authSubtitle"><?= __('login_title') ?><span class="typing-cursor"></span></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error" id="errorAlert">
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

        <form method="post" class="auth-form" id="loginForm">
            <div class="form-group">
                <label for="username">
                    <svg class="icon icon-16"><use href="assets/icons/sprite.svg#icon-user"/></svg>
                    <?= __('login_username') ?>
                </label>
                <input type="text" id="username" name="username" required autofocus
                       placeholder="<?= __('login_username') ?>" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">
                    <svg class="icon icon-16"><use href="assets/icons/sprite.svg#icon-key"/></svg>
                    <?= __('login_password') ?>
                </label>
                <input type="password" id="password" name="password" required
                       placeholder="<?= __('login_password') ?>" autocomplete="current-password">
            </div>
            
            <!-- CuteCaptcha - Self-hosted anti-bot -->
            <div class="form-group captcha-container" style="margin-bottom:16px;">
                <?= CuteCaptcha::render() ?>
            </div>
            
            <?= csrfField() ?>
            
            <button type="submit" class="btn btn-primary submit-btn ripple-btn" id="loginSubmitBtn" style="width:100%;justify-content:center;">
                <svg class="icon" id="loginBtnIcon"><use href="assets/icons/sprite.svg#icon-login"/></svg>
                <span id="loginBtnText"><?= __('login_btn') ?></span>
                <svg class="icon spin" id="loginBtnSpinner" style="display:none;"><use href="assets/icons/sprite.svg#icon-loading"/></svg>
            </button>
        </form>

        <div class="auth-footer">
            <p><?= __('login_no_account') ?> <a href="index.php?page=register" data-nav id="registerLink"><?= __('register') ?></a></p>
            <div class="language-switcher" style="margin-top:12px;justify-content:center;">
                <a href="index.php?page=api&action=set_language&lang=fa&redirect=login" class="lang-btn <?= Language::getCurrentLanguage() === 'fa' ? 'active' : '' ?>">فا</a>
                <a href="index.php?page=api&action=set_language&lang=en&redirect=login" class="lang-btn <?= Language::getCurrentLanguage() === 'en' ? 'active' : '' ?>">EN</a>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    var isRtl = document.documentElement.dir === 'rtl';

    // ===== FLOATING PARTICLES (JS-Powered) =====
    function createParticles() {
        var container = document.querySelector('.auth-container');
        if (!container) return;
        var count = window.innerWidth < 768 ? 30 : 60;
        for (var i = 0; i < count; i++) {
            var particle = document.createElement('div');
            var size = Math.random() * 4 + 2;
            particle.style.cssText = [
                'position:absolute',
                'width:' + size + 'px',
                'height:' + size + 'px',
                'background:rgba(255,255,255,' + (Math.random() * 0.08 + 0.02) + ')',
                'border-radius:' + (Math.random() > 0.5 ? '50%' : '2px'),
                'top:' + (Math.random() * 100) + '%',
                'left:' + (Math.random() * 100) + '%',
                'pointer-events:none',
                'z-index:0',
                'opacity:0',
                'transition:opacity 2s ease',
            ].join(';');
            container.appendChild(particle);
            
            setTimeout(function() {
                particle.style.opacity = '1';
            }, Math.random() * 2000);
            
            var duration = Math.random() * 30 + 20;
            var xDrift = (Math.random() - 0.5) * 200;
            var yDrift = (Math.random() - 0.5) * 200;
            
            particle.animate([
                { transform: 'translate(0px, 0px) rotate(0deg)', opacity: parseFloat(particle.style.opacity) || 0.3 },
                { transform: 'translate(' + xDrift + 'px, ' + yDrift + 'px) rotate(' + (Math.random() * 360) + 'deg)', opacity: (Math.random() * 0.3 + 0.1) },
                { transform: 'translate(' + (-xDrift/2) + 'px, ' + (-yDrift/2) + 'px) rotate(' + (Math.random() * 360) + 'deg)', opacity: parseFloat(particle.style.opacity) || 0.3 },
            ], {
                duration: duration * 1000,
                iterations: Infinity,
                direction: 'alternate',
                easing: 'ease-in-out'
            });
        }
    }
    createParticles();

    // ===== 3D CARD TILT EFFECT =====
    var wrap = document.getElementById('authCard3dWrap');
    var card = document.getElementById('authCard');
    
    if (wrap && card && window.innerWidth > 768) {
        wrap.addEventListener('mousemove', function(e) {
            var rect = wrap.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var centerX = rect.width / 2;
            var centerY = rect.height / 2;
            var rotateX = ((y - centerY) / centerY) * -8;
            var rotateY = ((x - centerX) / centerX) * 8;
            card.style.transform = 'rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) scale3d(1.02, 1.02, 1.02)';
        });
        
        wrap.addEventListener('mouseleave', function() {
            card.style.transform = '';
        });
    }

    // ===== TYPING EFFECT FOR SUBTITLE =====
    var subtitleEl = document.getElementById('authSubtitle');
    if (subtitleEl) {
        var fullText = subtitleEl.textContent.trim();
        subtitleEl.textContent = '';
        var cursor = document.createElement('span');
        cursor.className = 'typing-cursor';
        
        var i = 0;
        function typeChar() {
            if (i < fullText.length) {
                var char = fullText.charAt(i);
                var span = document.createElement('span');
                span.textContent = char;
                span.style.animation = 'authFormItem 0.2s ease both';
                subtitleEl.appendChild(span);
                i++;
                setTimeout(typeChar, 30 + Math.random() * 40);
            } else {
                subtitleEl.appendChild(cursor);
            }
        }
        setTimeout(typeChar, 800);
    }

    // ===== BUTTON RIPPLE EFFECT =====
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.ripple-btn');
        if (!btn || btn.disabled) return;
        var rect = btn.getBoundingClientRect();
        var ripple = document.createElement('span');
        ripple.className = 'ripple';
        var size = Math.max(rect.width, rect.height);
        ripple.style.cssText = [
            'width:' + size + 'px',
            'height:' + size + 'px',
            'top:' + (e.clientY - rect.top - size/2) + 'px',
            (isRtl ? 'right' : 'left') + ':' + (Math.abs(e.clientX - rect.left) - size/2) + 'px',
        ].join(';');
        btn.appendChild(ripple);
        setTimeout(function() { ripple.remove(); }, 600);
    });

    // ===== INPUT FOCUS ANIMATIONS =====
    document.querySelectorAll('.auth-form input').forEach(function(input) {
        input.addEventListener('focus', function() {
            this.closest('.form-group').querySelector('label')?.style.setProperty('color', 'var(--accent-1)');
        });
        input.addEventListener('blur', function() {
            this.closest('.form-group').querySelector('label')?.style.removeProperty('color');
        });
    });

    // ===== SHAKE ON ERROR =====
    var errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        var authCard = document.getElementById('authCard');
        if (authCard) {
            authCard.classList.add('shake');
            setTimeout(function() { authCard.classList.remove('shake'); }, 600);
        }
        // Highlight invalid fields
        var inputs = document.querySelectorAll('.auth-form input[required]');
        inputs.forEach(function(inp) {
            inp.classList.add('is-invalid');
            setTimeout(function() { inp.classList.remove('is-invalid'); }, 600);
        });
    }

    // ===== COMBINED SUBMIT: validate captcha, then disable button =====
    document.getElementById('loginForm')?.addEventListener('submit', function(e) {
        // Validate captcha first
        var answer = document.getElementById('captchaAnswer');
        if (!answer || !answer.value) {
            e.preventDefault();
            var captcha = document.getElementById('cuteCaptcha');
            if (captcha) {
                captcha.classList.add('captcha-error');
                setTimeout(function() { captcha.classList.remove('captcha-error'); }, 600);
            }
            return false;
        }
        
        // Then disable button
        var btn = document.getElementById('loginSubmitBtn');
        var icon = document.getElementById('loginBtnIcon');
        var text = document.getElementById('loginBtnText');
        var spinner = document.getElementById('loginBtnSpinner');
        if (btn) {
            btn.disabled = true;
            if (icon) icon.style.display = 'none';
            if (spinner) spinner.style.display = 'inline-block';
            if (text) text.textContent = '<?= __('loading') ?>';
        }
    });

    // ===== SMOOTH LINK TRANSITIONS =====
    var registerLink = document.getElementById('registerLink');
    if (registerLink) {
        registerLink.addEventListener('click', function(e) {
            var card = document.getElementById('authCard');
            if (card) {
                card.style.animation = 'authPageExit 0.3s ease both';
            }
        });
    }
})();
</script>
