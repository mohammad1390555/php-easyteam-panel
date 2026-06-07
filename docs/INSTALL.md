# EasyTeam Panel — Complete Installation Guide

> **English** — Comprehensive installation guide for Linux, Windows, and macOS.
> For Persian, see [INSTALL_FA.md](./INSTALL_FA.md).
> For the main project README, see [README.md](../README.md).

---

## Table of Contents

1. [Prerequisites (All Platforms)](#1-prerequisites-all-platforms)
2. [Linux Installation](#2-linux-installation)
3. [Windows Installation](#3-windows-installation)
4. [macOS Installation](#4-macos-installation)
5. [Post-Installation](#5-post-installation)
6. [Running as a Service](#6-running-as-a-service)
7. [Production Deployment](#7-production-deployment)
8. [Security Hardening](#8-security-hardening)
9. [Upgrading](#9-upgrading)
10. [Uninstalling](#10-uninstalling)

---

## 1. Prerequisites (All Platforms)

### Required Software

| Software | Version | Purpose |
|----------|---------|---------|
| **PHP** | 8.0+ (8.1+ recommended) | Panel runtime |
| **SQLite** | 3.x (bundled with PHP) | Database |
| **Java** | JDK 17+ | Running Minecraft servers (optional for panel) |

### Required PHP Extensions

```
sqlite3, pdo_sqlite, json, mbstring, curl, openssl
```

### Verify PHP Installation

Open a terminal/command prompt and run:

```bash
php -v
php -m | grep -E 'sqlite|json|mbstring|curl|openssl'
```

Expected output:
```
PHP 8.1.x (or higher)
sqlite3
pdo_sqlite
json
mbstring
curl
openssl
```

### Verify Java Installation (Optional)

```bash
java -version
```

Expected: `openjdk version "17.x.x"` or higher.

---

## 2. Linux Installation

### 2.1. Install PHP and Extensions

#### Ubuntu/Debian (APT)

```bash
sudo apt update
sudo apt install -y php php-cli php-sqlite3 php-json php-mbstring php-curl php-xml php-intl php-bcmath php-gd
```

#### CentOS/RHEL/Fedora (DNF/YUM)

```bash
# Fedora / RHEL 8+
sudo dnf install -y php php-cli php-sqlite php-mbstring php-curl php-xml php-intl php-bcmath

# CentOS 7 (using EPEL)
sudo yum install -y epel-release
sudo yum install -y php php-cli php-sqlite php-mbstring php-curl php-xml
```

#### Alpine Linux (APK)

```bash
sudo apk add php php-pdo_sqlite php-json php-mbstring php-curl php-openssl php-dom php-xml
```

#### Arch Linux (PACMAN)

```bash
sudo pacman -S php php-sqlite php-mbstring
```

### 2.2. Install Java (for Minecraft Servers)

```bash
# Ubuntu/Debian
sudo apt install -y openjdk-17-jdk-headless

# Fedora/RHEL
sudo dnf install -y java-17-openjdk-headless

# Alpine
sudo apk add openjdk17-jre-headless

# Arch
sudo pacman -S jdk17-openjdk
```

### 2.3. Download and Install the Panel

#### Option A: Git Clone (Recommended)

```bash
cd /opt
sudo git clone https://github.com/yourusername/easyteam-panel.git
sudo chown -R $USER:$USER easyteam-panel
cd easyteam-panel
```

#### Option B: Download ZIP

```bash
cd /opt
wget https://github.com/yourusername/easyteam-panel/archive/refs/heads/main.zip
unzip main.zip
mv easyteam-panel-main easyteam-panel
cd easyteam-panel
```

### 2.4. Set Permissions

```bash
chmod -R 755 .
chmod -R 777 storage/
```

### 2.5. Start the Panel

#### Development / Testing

```bash
php -S 0.0.0.0:8080 server.php
```

Open http://localhost:8080 in your browser.

#### Custom Port

```bash
php -S 0.0.0.0:9090 server.php
```

#### Bind to Specific IP

```bash
# Local access only
php -S 127.0.0.1:8080 server.php

# All interfaces
php -S 0.0.0.0:8080 server.php
```

### 2.6. Apache Virtual Host Setup

```apache
<VirtualHost *:80>
    ServerName panel.example.com
    DocumentRoot /var/www/html/easyteam-panel

    <Directory /var/www/html/easyteam-panel>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/easyteam-error.log
    CustomLog ${APACHE_LOG_DIR}/easyteam-access.log combined
</VirtualHost>
```

Enable required modules:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 2.7. Nginx Virtual Host Setup

```nginx
server {
    listen 80;
    server_name panel.example.com;

    root /var/www/html/easyteam-panel;
    index index.php;

    # Gzip static assets
    location ~* \.(css|js|svg|woff2?)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        gzip on;
        gzip_types text/css application/javascript image/svg+xml;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Protect sensitive directories
    location ~ /(storage|includes|templates|lang) {
        deny all;
        return 403;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 2.8. HTTPS with Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-apache  # or python3-certbot-nginx

# Apache
sudo certbot --apache -d panel.example.com

# Nginx
sudo certbot --nginx -d panel.example.com
```

---

## 3. Windows Installation

### 3.1. Install PHP on Windows

#### Step-by-Step PHP Installation

1. **Download PHP**
   - Go to https://windows.php.net/download/
   - Download the latest **PHP 8.x ZIP package** (non-thread-safe recommended)
   - Example: `php-8.3.x-Win32-vs16-x64.zip`

2. **Extract PHP**
   - Extract the ZIP to `C:\php`
   - You should have `C:\php\php.exe` and `C:\php\php.ini-development`

3. **Configure php.ini**
   ```cmd
   cd C:\php
   copy php.ini-development php.ini
   ```
   Edit `C:\php\php.ini` and uncomment these lines (remove the `;`):
   ```ini
   extension=sqlite3
   extension=pdo_sqlite
   extension=mbstring
   extension=curl
   extension=openssl
   extension_dir = "ext"
   ```

4. **Add PHP to PATH**
   - Open **System Properties** → **Advanced** → **Environment Variables**
   - Under "System variables", find `Path` and click **Edit**
   - Click **New** and add `C:\php`
   - Click **OK** on all windows
   - Open a **new** Command Prompt and run: `php -v`

5. **Verify Extensions**
   ```cmd
   php -m
   ```
   You should see: `sqlite3, pdo_sqlite, json, mbstring, curl, openssl`

#### Install Visual C++ Redistributable (if needed)

If you get "VCRUNTIME140.dll not found":
- Download from: https://aka.ms/vs/17/release/vc_redist.x64.exe
- Run the installer

### 3.2. Install Java on Windows

1. Go to https://adoptium.net/
2. Download **JDK 17 (LTS)** for Windows x64
3. Run the installer (MSI or MSI)
4. Java will be added to PATH automatically

Verify:
```cmd
java -version
```

### 3.3. Download the Panel

#### Option A: Git Clone

```cmd
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel
```

#### Option B: Download ZIP
- Go to the GitHub repository
- Click **Code** → **Download ZIP**
- Extract to a folder, e.g., `C:\easyteam-panel`

### 3.4. Method 1: One-Click Installer (Recommended)

Simply **double-click** `install_windows.bat`:

```
============================================
  EasyTeam Panel - Windows Installer
============================================

[OK] PHP 8.3.6 (cli)
[OK] Java 17.0.10

Creating storage directories...
[OK] Directories created

Starting EasyTeam Panel on http://localhost:8080
...
```

The installer will:
1. Check PHP and Java installations
2. Create all required directories
3. Start the panel on port 8080
4. Open your browser automatically

**If PHP is not found:**
- Follow the PHP installation steps above
- Or run from Command Prompt with PATH set manually:
  ```cmd
  set PATH=%PATH%;C:\php
  install_windows.bat
  ```

### 3.5. Method 2: PowerShell Runner

```powershell
# Simple start
powershell -ExecutionPolicy Bypass -File server.ps1

# Custom port (e.g., 9090)
powershell -ExecutionPolicy Bypass -File server.ps1 -Port 9090

# If already in PowerShell
.\server.ps1
.\server.ps1 -Port 9090
```

**PowerShell Execution Policy:**
If you get an error, run PowerShell as Administrator and:
```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```

### 3.6. Method 3: Manual Setup

```cmd
:: Navigate to panel directory
cd C:\easyteam-panel

:: Create storage directories
mkdir storage\database
mkdir storage\servers
mkdir storage\logs
mkdir storage\versions

:: Start the panel
php -S localhost:8080 server.php

:: Or with custom port
php -S localhost:9090 server.php
```

### 3.7. Running as Windows Service

#### Option A: Using NSSM (Recommended)

1. Download NSSM from https://nssm.cc/download
2. Extract to `C:\nssm`
3. Install the service:
   ```cmd
   C:\nssm\win64\nssm.exe install EasyTeamPanel
   ```
4. In the GUI:
   - **Application Path**: `C:\php\php.exe`
   - **Startup Directory**: `C:\easyteam-panel`
   - **Arguments**: `-S 0.0.0.0:8080 server.php`
5. Start the service:
   ```cmd
   C:\nssm\win64\nssm.exe start EasyTeamPanel
   ```

#### Option B: Using Task Scheduler

```powershell
# Run PowerShell as Administrator
$action = New-ScheduledTaskAction -Execute "C:\php\php.exe" `
    -Argument "-S 0.0.0.0:8080 server.php" `
    -WorkingDirectory "C:\easyteam-panel"

$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount

Register-ScheduledTask -TaskName "EasyTeamPanel" -Action $action -Trigger $trigger -Principal $principal
```

### 3.8. Firewall Configuration

Allow external access to the panel:

```powershell
# PowerShell (Run as Administrator)
New-NetFirewallRule -DisplayName "EasyTeam Panel (8080)" `
    -Direction Inbound -Protocol TCP -LocalPort 8080 -Action Allow
```

```cmd
# Command Prompt (Run as Administrator)
netsh advfirewall firewall add rule name="EasyTeam Panel (8080)" dir=in action=allow protocol=TCP localport=8080
```

---

## 4. macOS Installation

### 4.1. Install PHP

```bash
# Using Homebrew
brew install php

# Using MacPorts
sudo port install php83 php83-sqlite3 php83-mbstring php83-curl
```

### 4.2. Install Java

```bash
brew install openjdk@17
```

### 4.3. Install the Panel

```bash
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel
chmod -R 777 storage/
php -S 0.0.0.0:8080 server.php
```

---

## 5. Post-Installation

### 5.1. Complete the Installation Wizard

1. Open http://localhost:8080 in your browser
2. Step 1: System Requirements — Verify all checks pass
3. Step 2: Database Setup — Click to create database
4. Step 3: Admin Account — Create your admin user
5. Step 4: Complete — Click "Go to Login"

### 5.2. Install Minecraft Versions

1. Log in as admin
2. Go to **Version Installer** in the sidebar
3. Find version **1.20.1** (pre-installed recommended)
4. Click **Paper** to install the Paper server for that version
5. Wait for download to complete

### 5.3. Create Your First Server

1. Go to **Servers** → **Create Server**
2. Enter:
   - Name: "My First Server"
   - Version: 1.20.1
   - Type: Paper
   - Port: 25565
   - RAM: 1024 MB
3. Click **Create**
4. Click **Start** to launch the server

---

## 6. Running as a Service

### Linux (systemd)

```bash
sudo tee /etc/systemd/system/easyteam-panel.service > /dev/null <<'EOF'
[Unit]
Description=EasyTeam Minecraft Server Panel
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/html/easyteam-panel
ExecStart=/usr/bin/php -S 0.0.0.0:8080 server.php
Restart=always
RestartSec=10
StandardOutput=append:/var/log/easyteam-panel.log
StandardError=append:/var/log/easyteam-panel.log

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable easyteam-panel
sudo systemctl start easyteam-panel
sudo systemctl status easyteam-panel
```

### Linux (tmux/screen)

```bash
# tmux
tmux new-session -d -s panel 'php -S 0.0.0.0:8080 server.php'

# screen
screen -dmS panel php -S 0.0.0.0:8080 server.php
```

### Windows (NSSM)

```cmd
:: Already covered in section 3.7
```

---

## 7. Production Deployment

### Using Docker (Unofficial)

Create a `Dockerfile`:

```dockerfile
FROM php:8.1-cli

RUN docker-php-ext-install pdo_sqlite

WORKDIR /app
COPY . .

RUN chmod -R 777 storage/

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "server.php"]
```

Build and run:
```bash
docker build -t easyteam-panel .
docker run -d -p 8080:8080 easyteam-panel
```

### Using Apache/Nginx

Follow the virtual host setup in sections 2.6 and 2.7 above.

### Performance Tips

1. **Use OPcache** — Enable in `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **Use PHP-FPM** — For Nginx, use PHP-FPM instead of the built-in server

3. **Enable gzip compression** — In Apache/Nginx config

4. **Cache static assets** — Set long expiration headers for CSS/JS/SVG files

5. **Use a CDN** — For serving static assets if available

---

## 8. Security Hardening

### 8.1. File Permissions

```bash
chmod -R 644 .
chmod -R 755 storage/
chmod 644 config.php .htaccess
```

### 8.2. PHP Security

In `php.ini`:
```ini
display_errors = Off
expose_php = Off
allow_url_fopen = Off  # (will break some features)
```

### 8.3. Database Backup

```bash
# Linux
cp storage/database/panel.sqlite backups/panel-$(date +%Y%m%d).sqlite

# Windows (PowerShell)
Copy-Item storage\database\panel.sqlite "backups\panel-$(Get-Date -Format yyyyMMdd).sqlite"
```

### 8.4. Update reCAPTCHA Keys

Replace the test keys in `pages/login.php` with real keys from:
https://www.google.com/recaptcha/admin

### 8.5. Regular Updates

```bash
# Linux
cd /path/to/easyteam-panel
git pull
php -l index.php
# Check for errors, then restart service

# Windows
cd C:\easyteam-panel
git pull
# Restart the panel
```

---

## 9. Upgrading

### From GitHub

```bash
# Backup first
cp -r storage backups/storage-backup-$(date +%Y%m%d)

# Pull latest changes
git pull

# Check for config changes
diff config.example.php config.php

# Update database schema (if needed)
# The panel handles schema updates automatically via SQLite

# Restart the panel
sudo systemctl restart easyteam-panel  # Linux service
# or simply restart the PHP process
```

### Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | Current | Initial release |

---

## 10. Uninstalling

### Linux

```bash
# Stop the service
sudo systemctl stop easyteam-panel
sudo systemctl disable easyteam-panel

# Remove files
sudo rm -rf /var/www/html/easyteam-panel

# Remove PHP extensions (optional)
sudo apt remove php-sqlite3 php-json php-mbstring  # Ubuntu/Debian
```

### Windows

```cmd
:: Stop the service
nssm.exe stop EasyTeamPanel
nssm.exe remove EasyTeamPanel confirm

:: Delete the directory
rmdir /s /q C:\easyteam-panel

:: Remove firewall rule
netsh advfirewall firewall delete rule name="EasyTeam Panel (8080)"
```

---

## Quick Reference

### Linux Start/Stop

```bash
# Start
php -S 0.0.0.0:8080 server.php

# Background
nohup php -S 0.0.0.0:8080 server.php > /dev/null 2>&1 &

# Find and kill
pkill -f "php.*server.php"
```

### Windows Start/Stop

```cmd
:: Start
php -S localhost:8080 server.php

:: Stop (Ctrl+C in the window)
:: Or find and kill the PHP process
taskkill /F /IM php.exe
```

### Useful Commands

```bash
php -l index.php          # Syntax check
php -l pages/*.php        # Check all pages
php -l includes/*.php     # Check all includes
php -m | grep sqlite      # Check if SQLite extension is loaded
```
