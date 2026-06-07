<?php
/**
 * Installation Wizard
 */

// Determine config path
$configFile = __DIR__ . '/config.php';
$isInstalled = file_exists($configFile) && filesize($configFile) > 10;

// If already installed, redirect
if ($isInstalled) {
    require_once $configFile;
    require_once __DIR__ . '/includes/database.php';
    Database::init(DB_PATH);
    if (Database::isInstalled()) {
        header('Location: index.php');
        exit;
    }
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = (int)($_POST['step'] ?? 1);

    if ($step === 1) {
        // Check requirements
        $requirements = checkRequirements();
        $allPassed = true;
        $javaSkipped = isset($_POST['skip_java']);
        foreach ($requirements as $req) {
            if (!$req['passed'] && $req['key'] !== 'java' && !$javaSkipped) {
                $allPassed = false;
            }
            if ($req['key'] === 'java' && !$req['passed'] && !$javaSkipped) {
                $allPassed = false;
            }
        }
        if ($allPassed) {
            $step = 2;
        } else {
            $error = 'Some requirements are not met. Please fix them and try again.';
        }
    } elseif ($step === 2) {
        // Create config file
        $configContent = '<?php
/**
 * EasyTeam Minecraft Server Panel - Configuration
 * Auto-generated during installation
 */

define(\'DB_PATH\', __DIR__ . \'/storage/database/panel.sqlite\');
define(\'SITE_NAME\', \'EasyTeam Panel\');
define(\'SITE_URL\', \'http://\' . ($_SERVER[\'HTTP_HOST\'] ?? \'localhost:8080\'));
define(\'TIMEZONE\', \'Asia/Tehran\');
define(\'LANGUAGE\', \'fa\');
define(\'MINECRAFT_BASE_PATH\', __DIR__ . \'/storage/servers\');
define(\'MINECRAFT_VERSIONS_PATH\', __DIR__ . \'/storage/versions\');
define(\'MINECRAFT_MIN_RAM\', 256);
define(\'MINECRAFT_MAX_RAM\', 8192);
define(\'JAVA_PATH\', \'java\');
define(\'SESSION_NAME\', \'EASYTEAM_PANEL\');
define(\'SESSION_LIFETIME\', 86400);
define(\'PANEL_VERSION\', \'1.0.0\');
';

        file_put_contents($configFile, $configContent);
        require_once $configFile;

        // Create storage directories
        $dirs = [
            __DIR__ . '/storage/servers',
            __DIR__ . '/storage/database',
            __DIR__ . '/storage/logs',
            __DIR__ . '/storage/versions',
        ];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) mkdir($dir, 0755, true);
        }

        // Initialize DB
        require_once __DIR__ . '/includes/database.php';
        Database::init(DB_PATH);
        Database::createTables();

        $success = 'Database created successfully!';
        $step = 3;
    } elseif ($step === 3) {
        // Create admin account
        require_once $configFile;
        require_once __DIR__ . '/includes/database.php';
        require_once __DIR__ . '/includes/functions.php';
        require_once __DIR__ . '/includes/language.php';
        require_once __DIR__ . '/includes/auth.php';

        Database::init(DB_PATH);

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $email = trim($_POST['email'] ?? '');

        if (empty($username) || empty($password) || empty($email)) {
            $error = 'All fields are required';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            Database::insert('users', [
                'username' => $username,
                'email' => $email,
                'password' => $hashed,
                'role' => 'admin',
                'language' => 'fa',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $success = 'Admin account created successfully!';
            $step = 4;
        }
    }
}

function checkRequirements(): array {
    $requirements = [];

    // PHP Version
    $requirements[] = [
        'key' => 'php',
        'label' => 'PHP 8.0+',
        'passed' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'detail' => PHP_VERSION,
    ];

    // SQLite
    $requirements[] = [
        'key' => 'sqlite',
        'label' => 'SQLite Extension',
        'passed' => extension_loaded('sqlite3') && extension_loaded('pdo_sqlite'),
        'detail' => extension_loaded('sqlite3') ? 'Loaded' : 'Missing',
    ];

    // JSON
    $requirements[] = [
        'key' => 'json',
        'label' => 'JSON Extension',
        'passed' => extension_loaded('json'),
        'detail' => extension_loaded('json') ? 'Loaded' : 'Missing',
    ];

    // MBString
    $requirements[] = [
        'key' => 'mbstring',
        'label' => 'MBString Extension',
        'passed' => extension_loaded('mbstring'),
        'detail' => extension_loaded('mbstring') ? 'Loaded' : 'Missing',
    ];

    // cURL
    $requirements[] = [
        'key' => 'curl',
        'label' => 'cURL Extension',
        'passed' => extension_loaded('curl'),
        'detail' => extension_loaded('curl') ? 'Loaded' : 'Missing',
    ];

    // OpenSSL
    $requirements[] = [
        'key' => 'openssl',
        'label' => 'OpenSSL Extension',
        'passed' => extension_loaded('openssl'),
        'detail' => extension_loaded('openssl') ? 'Loaded' : 'Missing',
    ];

    // Storage Writable
    $storageWritable = is_writable(__DIR__ . '/storage') || @mkdir(__DIR__ . '/storage', 0755, true);
    $requirements[] = [
        'key' => 'writable',
        'label' => 'Storage Directory Writable',
        'passed' => $storageWritable,
        'detail' => $storageWritable ? 'Writable' : 'Not writable',
    ];

    // Java
    $javaPath = null;
    exec('which java 2>/dev/null', $output, $code);
    $javaFound = $code === 0 && !empty($output);
    $javaVersion = '';
    if ($javaFound) {
        exec('java -version 2>&1', $versionOutput);
        $javaVersion = $versionOutput[0] ?? 'Unknown';
    }
    $requirements[] = [
        'key' => 'java',
        'label' => 'Java (JDK 17+)',
        'passed' => $javaFound,
        'detail' => $javaFound ? $javaVersion : 'Not found (optional)',
    ];

    return $requirements;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب پنل ایزی‌تیم</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet" type="text/css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Vazir', 'Tahoma', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e0e0e0;
            direction: rtl;
        }
        .container {
            width: 100%;
            max-width: 680px;
            padding: 20px;
        }
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }
        h1 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 8px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .steps {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 30px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            border: 2px solid #444;
            color: #666;
            transition: all 0.3s;
        }
        .step.active {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.2);
            color: #fff;
        }
        .step.completed {
            border-color: #4caf50;
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }
        .step-line {
            width: 30px;
            height: 2px;
            background: #444;
            align-self: center;
        }
        .step-line.active {
            background: #667eea;
        }

        .requirement {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .requirement .label { font-size: 14px; }
        .requirement .detail { font-size: 12px; color: #888; }
        .requirement .status { font-size: 18px; }
        .requirement.passed { border-color: rgba(76, 175, 80, 0.3); }
        .requirement.failed { border-color: rgba(244, 67, 54, 0.3); }

        form { margin-top: 20px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #aaa; }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-family: 'Vazir', 'Tahoma', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2); }
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-family: 'Vazir', 'Tahoma', sans-serif;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: #fff;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            margin-top: 10px;
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: none;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-error { background: rgba(244, 67, 54, 0.15); border: 1px solid rgba(244, 67, 54, 0.3); color: #ef5350; }
        .alert-success { background: rgba(76, 175, 80, 0.15); border: 1px solid rgba(76, 175, 80, 0.3); color: #66bb6a; }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        .checkbox-group label { margin-bottom: 0; }
        .checkbox-group input[type="checkbox"] { width: 18px; height: 18px; accent-color: #667eea; }

        .finish-icon { font-size: 64px; text-align: center; margin-bottom: 20px; }
        .text-center { text-align: center; }
        .mt-4 { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><svg class="icon"><use href="assets/icons/sprite.svg#icon-rocket"/></svg> پنل ایزی‌تیم</h1>
            <p class="subtitle">نصب و راه‌اندازی پنل مدیریت سرور ماینکرفت</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Steps -->
            <div class="steps">
                <div class="step <?= $step >= 2 ? 'completed' : ($step == 1 ? 'active' : '') ?>">1</div>
                <div class="step-line <?= $step >= 2 ? 'active' : '' ?>"></div>
                <div class="step <?= $step >= 3 ? 'completed' : ($step == 2 ? 'active' : '') ?>">2</div>
                <div class="step-line <?= $step >= 3 ? 'active' : '' ?>"></div>
                <div class="step <?= $step >= 4 ? 'completed' : ($step == 3 ? 'active' : '') ?>">3</div>
                <div class="step-line <?= $step >= 4 ? 'active' : '' ?>"></div>
                <div class="step <?= $step == 4 ? 'active' : '' ?>">4</div>
            </div>

            <?php if ($step == 1): ?>
                <h2 style="text-align:center;margin-bottom:20px;font-size:18px;">بررسی نیازمندی‌های سیستم</h2>
                <?php $reqs = checkRequirements(); ?>
                <?php foreach ($reqs as $req): ?>
                    <div class="requirement <?= $req['passed'] ? 'passed' : 'failed' ?>">
                        <div>
                            <div class="label"><?= htmlspecialchars($req['label']) ?></div>
                            <div class="detail"><?= htmlspecialchars($req['detail']) ?></div>
                        </div>
                        <div class="status"><?= $req['passed'] ? '<svg class="icon"><use href="assets/icons/sprite.svg#icon-check"/></svg>' : '<svg class="icon"><use href="assets/icons/sprite.svg#icon-close"/></svg>' ?></div>
                    </div>
                <?php endforeach; ?>

                <form method="post">
                    <input type="hidden" name="step" value="1">
                    <div class="checkbox-group">
                        <input type="checkbox" id="skip_java" name="skip_java" value="1">
                        <label for="skip_java">رد کردن بررسی جاوا (نصب بعدا)</label>
                    </div>
                    <button type="submit" class="btn mt-4">ادامه و شروع نصب</button>
                </form>

            <?php elseif ($step == 2): ?>
                <h2 style="text-align:center;margin-bottom:10px;font-size:18px;">در حال ایجاد دیتابیس...</h2>
                <p class="text-center" style="color:#888;font-size:14px;">دیتابیس SQLite در حال ایجاد است.</p>
                <form method="post">
                    <input type="hidden" name="step" value="2">
                    <button type="submit" class="btn mt-4">ایجاد دیتابیس</button>
                </form>

            <?php elseif ($step == 3): ?>
                <h2 style="text-align:center;margin-bottom:20px;font-size:18px;">ایجاد حساب ادمین</h2>
                <form method="post">
                    <input type="hidden" name="step" value="3">
                    <div class="form-group">
                        <label>نام کاربری</label>
                        <input type="text" name="username" required minlength="3" placeholder="admin">
                    </div>
                    <div class="form-group">
                        <label>ایمیل</label>
                        <input type="email" name="email" required placeholder="admin@example.com">
                    </div>
                    <div class="form-group">
                        <label>رمز عبور</label>
                        <input type="password" name="password" required minlength="6" placeholder="حداقل ۶ کاراکتر">
                    </div>
                    <div class="form-group">
                        <label>تکرار رمز عبور</label>
                        <input type="password" name="confirm_password" required placeholder="تکرار رمز عبور">
                    </div>
                    <button type="submit" class="btn">ایجاد حساب ادمین</button>
                </form>

            <?php elseif ($step == 4): ?>
                <div class="finish-icon"><svg class="icon"><use href="assets/icons/sprite.svg#icon-check"/></svg></div>
                <h2 style="text-align:center;margin-bottom:10px;font-size:20px;">نصب با موفقیت کامل شد!</h2>
                <p class="text-center" style="color:#888;font-size:14px;margin-bottom:20px;">
                    پنل ایزی‌تیم آماده استفاده است. می‌توانید اکنون وارد شوید و مدیریت سرورهای خود را شروع کنید.
                </p>
                <a href="index.php" class="btn" style="display:block;text-align:center;text-decoration:none;">رفتن به صفحه ورود</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
