# پنل ایزی‌تیم — راهنمای کامل نصب

> **فارسی** — راهنمای جامع نصب روی لینوکس، ویندوز و مک.
> برای نسخه انگلیسی، [INSTALL.md](./INSTALL.md) را ببینید.

---

## فهرست مطالب

1. [پیش‌نیازها (همه سیستم‌عامل‌ها)](#1-پیشنیازها-همه-سیستمعاملها)
2. [نصب روی لینوکس](#2-نصب-روی-لینوکس)
3. [نصب روی ویندوز](#3-نصب-روی-ویندوز)
4. [نصب روی مک](#4-نصب-روی-مک)
5. [پس از نصب](#5-پس-از-نصب)
6. [اجرا به عنوان سرویس](#6-اجرا-به-عنوان-سرویس)
7. [استقرار در محیط تولید](#7-استقرار-در-محیط-تولید)
8. [امنیت](#8-امنیت)
9. [به‌روزرسانی](#9-بهروزرسانی)
10. [حذف نصب](#10-حذف-نصب)

---

## 1. پیش‌نیازها (همه سیستم‌عامل‌ها)

### نرم‌افزارهای مورد نیاز

| نرم‌افزار | نسخه | کاربرد |
|----------|------|--------|
| **PHP** | ۸.۰+ (۸.۱+ توصیه می‌شود) | اجرای پنل |
| **SQLite** | ۳.x (همراه PHP) | دیتابیس |
| **Java** | JDK 17+ | اجرای سرورهای ماینکرفت (اختیاری) |

### افزونه‌های مورد نیاز PHP

```
sqlite3, pdo_sqlite, json, mbstring, curl, openssl
```

### بررسی نصب PHP

```bash
php -v
php -m | grep -E 'sqlite|json|mbstring|curl|openssl'
```

خروجی مورد انتظار:
```
PHP 8.1.x (یا بالاتر)
sqlite3
pdo_sqlite
json
mbstring
curl
openssl
```

---

## 2. نصب روی لینوکس

### ۲.۱. نصب PHP و افزونه‌ها

#### Ubuntu/Debian

```bash
sudo apt update
sudo apt install -y php php-cli php-sqlite3 php-json php-mbstring php-curl php-xml
```

#### CentOS/RHEL/Fedora

```bash
sudo dnf install -y php php-cli php-sqlite php-mbstring php-curl php-xml
```

#### Alpine

```bash
sudo apk add php php-pdo_sqlite php-json php-mbstring php-curl php-openssl
```

### ۲.۲. نصب جاوا

```bash
# Ubuntu/Debian
sudo apt install -y openjdk-17-jdk-headless

# Fedora/RHEL
sudo dnf install -y java-17-openjdk-headless

# Alpine
sudo apk add openjdk17-jre-headless
```

### ۲.۳. دانلود پنل

```bash
cd /opt
sudo git clone https://github.com/yourusername/easyteam-panel.git
sudo chown -R $USER:$USER easyteam-panel
cd easyteam-panel
```

### ۲.۴. تنظیم دسترسی‌ها

```bash
chmod -R 755 .
chmod -R 777 storage/
```

### ۲.۵. اجرای پنل

```bash
# حالت تست/توسعه
php -S 0.0.0.0:8080 server.php

# با پورت دلخواه
php -S 0.0.0.0:9090 server.php

# فقط دسترسی محلی
php -S 127.0.0.1:8080 server.php
```

سپس در مرورگر به آدرس http://localhost:8080 بروید.

### ۲.۶. اجرا به عنوان سرویس سیستم

```bash
sudo tee /etc/systemd/system/easyteam-panel.service > /dev/null <<EOF
[Unit]
Description=EasyTeam Minecraft Panel
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/html/easyteam-panel
ExecStart=/usr/bin/php -S 0.0.0.0:8080 server.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable easyteam-panel
sudo systemctl start easyteam-panel
```

### ۲.۷. نکات کاربران ایرانی

```bash
# دانلود از گیت‌هاب (بدون تحریم)
git clone https://github.com/yourusername/easyteam-panel.git

# اگر git در دسترس نیست
wget https://github.com/yourusername/easyteam-panel/archive/refs/heads/main.zip
unzip main.zip
cd easyteam-panel-main
```

> همچنین می‌توانید در تنظیمات پنل، آینه دانلود را روی "گیت‌هاب" تنظیم کنید.

---

## 3. نصب روی ویندوز

### ۳.۱. نصب PHP روی ویندوز

#### مرحله به مرحله

1. **دانلود PHP**
   - به آدرس https://windows.php.net/download/ بروید
   - آخرین نسخه PHP 8.x ZIP را دانلود کنید (non-thread-safe توصیه می‌شود)
   - مثال: `php-8.3.x-Win32-vs16-x64.zip`

2. **استخراج PHP**
   - ZIP را به `C:\php` استخراج کنید
   - فایل‌های `C:\php\php.exe` و `C:\php\php.ini-development` باید وجود داشته باشند

3. **تنظیم php.ini**
   ```cmd
   cd C:\php
   copy php.ini-development php.ini
   ```
   فایل `C:\php\php.ini` را ویرایش کنید و این خطوط را از حالت کامنت خارج کنید (علامت `;` را بردارید):
   ```ini
   extension=sqlite3
   extension=pdo_sqlite
   extension=mbstring
   extension=curl
   extension=openssl
   extension_dir = "ext"
   ```

4. **اضافه کردن PHP به PATH**
   - **System Properties** → **Advanced** → **Environment Variables** را باز کنید
   - در "System variables"، `Path` را پیدا کنید و **Edit** بزنید
   - **New** بزنید و `C:\php` را اضافه کنید
   - **OK** بزنید
   - یک **Command Prompt جدید** باز کنید و اجرا کنید: `php -v`

5. **بررسی افزونه‌ها**
   ```cmd
   php -m
   ```
   باید `sqlite3, pdo_sqlite, json, mbstring, curl, openssl` را ببینید.

### ۳.۲. نصب جاوا روی ویندوز

1. به آدرس https://adoptium.net/ بروید
2. **JDK 17 (LTS)** برای Windows x64 را دانلود کنید
3. نصب‌کننده را اجرا کنید

بررسی:
```cmd
java -version
```

### ۳.۳. روش اول - نصب با یک کلیک (توصیه شده)

فایل `install_windows.bat` را **دوبار کلیک** کنید.

این اسکریپت:
1. PHP و Java را بررسی می‌کند
2. پوشه‌های مورد نیاز را می‌سازد
3. پنل را روی `http://localhost:8080` اجرا می‌کند
4. مرورگر را باز می‌کند

### ۳.۴. روش دوم - PowerShell

```powershell
powershell -ExecutionPolicy Bypass -File server.ps1

# با پورت دلخواه
powershell -ExecutionPolicy Bypass -File server.ps1 -Port 9090
```

### ۳.۵. روش سوم - دستی

```cmd
cd C:\easyteam-panel
mkdir storage\database storage\servers storage\logs storage\versions
php -S localhost:8080 server.php
```

### ۳.۶. نصب به عنوان سرویس ویندوز

با NSSM:
```cmd
:: دانلود NSSM از https://nssm.cc/download
:: استخراج به C:\nssm

C:\nssm\win64\nssm.exe install EasyTeamPanel
:: در پنجره باز شده:
::   Application Path: C:\php\php.exe
::   Startup Directory: C:\easyteam-panel
::   Arguments: -S 0.0.0.0:8080 server.php

C:\nssm\win64\nssm.exe start EasyTeamPanel
```

### ۳.۷. تنظیم فایروال

```powershell
# PowerShell as Administrator
New-NetFirewallRule -DisplayName "EasyTeam Panel" -Direction Inbound -Protocol TCP -LocalPort 8080 -Action Allow
```

---

## 4. نصب روی مک

```bash
# نصب PHP با Homebrew
brew install php

# نصب جاوا
brew install openjdk@17

# دانلود و اجرای پنل
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel
chmod -R 777 storage/
php -S 0.0.0.0:8080 server.php
```

---

## 5. پس از نصب

### ۵.۱. ویزارد نصب

1. مرورگر را به http://localhost:8080 باز کنید
2. **مرحله ۱**: بررسی نیازمندی‌های سیستم
3. **مرحله ۲**: ایجاد دیتابیس
4. **مرحله ۳**: ساخت حساب ادمین
5. **مرحله ۴**: پایان نصب - روی "رفتن به صفحه ورود" کلیک کنید

### ۵.۲. نصب نسخه ماینکرفت

1. وارد پنل شوید
2. به بخش "نصب نسخه" بروید
3. روی دکمه "Paper" کنار نسخه ۱.۲۰.۱ کلیک کنید
4. صبر کنید تا دانلود کامل شود

### ۵.۳. ساخت اولین سرور

1. به بخش "سرورها" → "ایجاد سرور" بروید
2. اطلاعات را وارد کنید
3. روی "ایجاد سرور" کلیک کنید
4. روی "شروع" کلیک کنید تا سرور اجرا شود

---

## 6. اجرا به عنوان سرویس

### لینوکس (systemd)

```bash
# فایل سرویس در بخش ۲.۶ توضیح داده شده
sudo systemctl enable --now easyteam-panel
```

### لینوکس (tmux/screen)

```bash
tmux new-session -d -s panel 'php -S 0.0.0.0:8080 server.php'
screen -dmS panel php -S 0.0.0.0:8080 server.php
```

### ویندوز (NSSM)

```cmd
nssm.exe start EasyTeamPanel
```

---

## 7. استقرار در محیط تولید

### نکات بهینه‌سازی

1. **فعال کردن OPcache** در `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   ```

2. **استفاده از Apache/Nginx** به جای سرور داخلی PHP
3. **فعال کردن gzip** در وب سرور
4. **تنظیم کش مرورگر** برای فایل‌های static

---

## 8. امنیت

### پشتیبان‌گیری دیتابیس

```bash
# لینوکس
cp storage/database/panel.sqlite backups/panel-$(date +%Y%m%d).sqlite

# ویندوز
Copy-Item storage\database\panel.sqlite "backups\panel-$(Get-Date -Format yyyyMMdd).sqlite"
```

### به‌روزرسانی کلیدهای reCAPTCHA

کلیدهای تست را با کلیدهای واقعی از https://www.google.com/recaptcha/admin جایگزین کنید.

---

## 9. به‌روزرسانی

```bash
cd /path/to/easyteam-panel
cp -r storage backups/storage-backup-$(date +%Y%m%d)
git pull
# پنل را مجدداً راه‌اندازی کنید
```

---

## 10. حذف نصب

### لینوکس

```bash
sudo systemctl stop easyteam-panel
sudo rm -rf /var/www/html/easyteam-panel
```

### ویندوز

```cmd
nssm.exe stop EasyTeamPanel
nssm.exe remove EasyTeamPanel confirm
rmdir /s /q C:\easyteam-panel
```

---

## راهنمای سریع

### شروع و توقف

```bash
# لینوکس - شروع
php -S 0.0.0.0:8080 server.php
# لینوکس - توقف (Ctrl+C)

# ویندوز - شروع
php -S localhost:8080 server.php
# ویندوز - توقف (Ctrl+C)
# یا: taskkill /F /IM php.exe
```
