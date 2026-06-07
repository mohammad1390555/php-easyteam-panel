<?php
/**
 * Register Page - with admin registration support (hidden for first user)
 */

if (Auth::isLoggedIn()) {
    redirect('index.php?page=dashboard');
}

// Check if registration is enabled in settings
$enableRegistration = ServerManager::getSetting('enable_registration', '1');
$hasUsers = Database::fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;

// Always allow registration if no users exist (first setup)
if (!$hasUsers) {
    $enableRegistration = '1';
}

if ($enableRegistration !== '1') {
    ?>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo"><svg class="icon icon-48"><use href="assets/icons/sprite.svg#icon-minecraft"/></svg></div>
            <h1><?= __('site_name') ?></h1>
            <p><?= __('register_title') ?></p>
        </div>
        <div class="alert alert-warning" style="text-align:center;">
            <svg class="icon"><use href="assets/icons/sprite.svg#icon-warning"/></svg>
            <span>ثبت‌نام غیرفعال است. با ادمین تماس بگیرید.</span>
        </div>
        <div class="auth-footer">
            <p><a href="index.php?page=login"><?= __('login') ?></a></p>
        </div>
    </div>
    <?php
    return;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $captchaAnswer = $_POST['captcha_answer'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // CSRF check
    if (!validateCsrf()) {
        $error = __('security_csrf_error');
    }
    // Verify captcha first
    elseif (!CuteCaptcha::verify($captchaAnswer)) {
        $error = __('captcha_error');
    }
    // Only allow admin role if no admin exists yet
    elseif ($role === 'admin') {
        $existingAdmin = Database::fetch("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count'] ?? 0;
        if ($existingAdmin > 0 && !Auth::isLoggedIn()) {
            $role = 'user'; // Force user role if admin exists
        }
    }

    if (empty($error)) {
        if (empty($username) || empty($email) || empty($password)) {
            $error = __('register_error_empty_fields');
        } elseif ($password !== $confirmPassword) {
            $error = __('register_error_password_match');
        } else {
            $result = Auth::register($username, $email, $password, $role);
            if ($result['success']) {
                flashMessage('success', __('register_success'));
                redirect('index.php?page=login');
            } else {
                $error = __($result['error']);
            }
        }
    }
}
?>
<div class="auth-card-3d-wrap" id="authCard3dWrap">
    <div class="auth-card" id="authCard">
        <div class="auth-header">
            <div class="auth-logo"><svg class="icon icon-48"><use href="assets/icons/sprite.svg#icon-minecraft"/></svg></div>
            <h1><?= __('site_name') ?></h1>
            <p id="authSubtitle"><?= __('register_title') ?><span class="typing-cursor"></span></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error" id="errorAlert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form" id="registerForm">
            <div class="form-group">
                <label for="username"><svg class="icon icon-16"><use href="assets/icons/sprite.svg#icon-user"/></svg> <?= __('register_username') ?></label>
                <input type="text" id="username" name="username" required minlength="3" autofocus
                       placeholder="<?= __('register_username') ?>" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="email"><svg class="icon icon-16"><use href="assets/icons/sprite.svg#icon-mail"/></svg> <?= __('register_email') ?></label>
                <input type="email" id="email" name="email" required
                       placeholder="<?= __('register_email') ?>" autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password"><svg class="icon icon-16"><use href="assets/icons/sprite.svg#icon-key"/></svg> <?= __('register_password') ?></label>
                <input type="password" id="password" name="password" required minlength="6"
                       placeholder="<?= __('register_password') ?>" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="confirm_password"><svg class="icon icon-16"><use href="assets/icons/sprite.svg#icon-key"/></svg> <?= __('register_confirm') ?></label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       placeholder="<?= __('register_confirm') ?>" autocomplete="new-password">
            </div>
            
            <!-- CuteCaptcha - Self-hosted anti-bot -->
            <div class="form-group captcha-container" style="margin-bottom:16px;">
                <?= CuteCaptcha::render() ?>
            </div>
            
            <?= csrfField() ?>
            
            <button type="submit" class="btn btn-primary submit-btn ripple-btn" id="regSubmitBtn" style="width:100%;justify-content:center;">
                <svg class="icon" id="regBtnIcon"><use href="assets/icons/sprite.svg#icon-user"/></svg>
                <span id="regBtnText"><?= __('register_btn') ?></span>
                <svg class="icon spin" id="regBtnSpinner" style="display:none;"><use href="assets/icons/sprite.svg#icon-loading"/></svg>
            </button>
        </form>

        <div class="auth-footer">
            <p><?= __('register_have_account') ?> <a href="index.php?page=login" id="loginLink"><?= __('login') ?></a></p>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    var isRtl = document.documentElement.dir === 'rtl';

    // ===== FLOATING PARTICLES =====
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
                { transform: 'translate(0px, 0px) rotate(0deg)', opacity: 0.3 },
                { transform: 'translate(' + xDrift + 'px, ' + yDrift + 'px) rotate(' + (Math.random() * 360) + 'deg)', opacity: 0.5 },
                { transform: 'translate(' + (-xDrift/2) + 'px, ' + (-yDrift/2) + 'px) rotate(' + (Math.random() * 360) + 'deg)', opacity: 0.3 },
            ], {
                duration: duration * 1000,
                iterations: Infinity,
                direction: 'alternate',
                easing: 'ease-in-out'
            });
        }
    }
    createParticles();

    // ===== 3D CARD TILT =====
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

    // ===== TYPING EFFECT =====
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

    // ===== RIPPLE EFFECT =====
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

    // ===== INPUT FOCUS =====
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
        var inputs = document.querySelectorAll('.auth-form input[required]');
        inputs.forEach(function(inp) {
            inp.classList.add('is-invalid');
            setTimeout(function() { inp.classList.remove('is-invalid'); }, 600);
        });
    }

    // ===== FORM SUBMIT: validate captcha first then disable button =====
    document.getElementById('registerForm')?.addEventListener('submit', function(e) {
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
    });

    document.getElementById('registerForm')?.addEventListener('submit', function(e) {
        if (e.defaultPrevented) return;
        var btn = document.getElementById('regSubmitBtn');
        var icon = document.getElementById('regBtnIcon');
        var text = document.getElementById('regBtnText');
        var spinner = document.getElementById('regBtnSpinner');
        if (btn) btn.disabled = true;
        if (icon) icon.style.display = 'none';
        if (spinner) spinner.style.display = 'inline-block';
        if (text) text.textContent = '<?= __('loading') ?>';
    });

    // ===== SMOOTH TRANSITION TO LOGIN =====
    var loginLink = document.getElementById('loginLink');
    if (loginLink) {
        loginLink.addEventListener('click', function(e) {
            var card = document.getElementById('authCard');
            if (card) {
                card.style.animation = 'authPageExit 0.3s ease both';
            }
        });
    }
})();
</script>
