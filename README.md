<p align="center">
  <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect width="64" height="64" rx="16" fill="url(#g)"/>
    <path d="M20 28l12-8 12 8v8l-12 8-12-8v-8z" fill="#fff" opacity=".9"/>
    <path d="M28 24l8-4 8 4v6l-8 4-8-4v-6z" fill="#fff" opacity=".6"/>
    <defs><linearGradient id="g" x1="0" y1="0" x2="64" y2="64"><stop stop-color="#6366f1"/><stop offset="1" stop-color="#8b5cf6"/></linearGradient></defs>
  </svg>
</p>

<h1 align="center">🎮 EasyTeam Panel</h1>
<p align="center"><strong>Professional Minecraft Server Management Panel</strong></p>
<p align="center">Modern Web Panel — SPA Architecture — Dual Language (فارسی / English)</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/SQLite-003B57?logo=sqlite&logoColor=white" alt="SQLite">
  <img src="https://img.shields.io/badge/SPA-Enabled-6366f1" alt="SPA">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

---

<!-- ====================================================================== -->
<!-- 🇬🇧 ENGLISH SECTION                                                     -->
<!-- ====================================================================== -->

<h2 dir="ltr">🇬🇧 English</h2>

### 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [System Requirements](#-system-requirements)
- [Quick Start](#-quick-start)
- [Linux Installation](#-linux-installation)
  - [Automatic Installation](#linux-automatic-installation)
  - [Manual Installation](#linux-manual-installation)
  - [Apache Setup](#apache-setup)
  - [Nginx Setup](#nginx-setup)
  - [Install as System Service](#install-as-system-service)
- [Windows Installation](#-windows-installation)
  - [Method 1: One-Click Batch Installer](#method-1-one-click-batch-installer)
  - [Method 2: PowerShell Runner](#method-2-powershell-runner)
  - [Method 3: Manual Setup](#method-3-manual-setup)
  - [Run as Windows Service](#run-as-windows-service)
  - [Firewall Configuration](#firewall-configuration)
- [Usage Guide](#-usage-guide)
  - [Installation Wizard](#installation-wizard)
  - [Creating Your First Server](#creating-your-first-server)
  - [Console & File Management](#console--file-management)
  - [Installing Minecraft Versions](#installing-minecraft-versions)
- [Configuration](#-configuration)
- [Troubleshooting](#-troubleshooting)
  - [Linux Issues](#linux-issues)
  - [Windows Issues](#windows-issues)
  - [Common Issues](#common-issues)
- [API Reference](#-api-reference)
- [Project Structure](#-project-structure)
- [Security](#-security)
- [License](#-license)

---

### 📖 Overview

EasyTeam Panel is a **professional, lightweight Minecraft server management panel** built with PHP and SQLite. It features:

- **Single Page Application (SPA)** architecture — no page refreshes, smooth transitions like Pterodactyl/PufferPanel
- **Dual language** support — Persian (فارسی, RTL) and English (LTR)
- **Local-first design** — runs entirely on your machine with no external dependencies
- **Cross-platform** — works on Linux, Windows, and macOS
- **Sanctions-resistant** — uses GitHub mirror for Iranian users

---

### ✨ Features

| Feature | Description |
|---------|-------------|
| **Server Management** | Create, start, stop, restart, and delete Minecraft servers |
| **Real-time Console** | Live console output with command sending via AJAX polling |
| **File Manager** | Browse, upload, edit, rename, delete server files |
| **Version Installer** | Download & install Paper/Vanilla Minecraft versions automatically |
| **User System** | Register, login, admin/user roles with permissions |
| **Dual Language** | Full Persian (RTL) and English support with Vazir font |
| **SPA Navigation** | No full page reloads — smooth transitions with history.pushState |
| **Google reCAPTCHA** | Bot protection on login page |
| **SVG Icon System** | 50+ SVG icons replacing all emojis for better performance |
| **Installation Wizard** | Step-by-step guided setup |
| **Security** | CSRF tokens, SQL injection protection, path traversal prevention |
| **Java Auto-installer** | Automated JDK 17+ installation on Linux |
| **Sanctions Support** | GitHub download mirror for users in Iran |

---

### 💻 System Requirements

| Requirement | Details |
|-------------|---------|
| **PHP** | 8.0 or higher (8.1+ recommended) |
| **Extensions** | `sqlite3`, `pdo_sqlite`, `json`, `mbstring`, `curl`, `openssl` |
| **Storage** | At least 1GB free space (more for Minecraft servers) |
| **Java** | JDK 17+ (only needed for running Minecraft servers, not the panel itself) |
| **OS** | Linux, Windows 10/11, macOS |

---

### 🚀 Quick Start

```bash
# Clone the repository
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel

# Start the panel (Linux/macOS)
php -S 0.0.0.0:8080 server.php

# Start the panel (Windows - PowerShell)
powershell -ExecutionPolicy Bypass -File server.ps1

# Or double-click install_windows.bat on Windows
```

Then open **http://localhost:8080** in your browser and follow the installation wizard.

---

<!-- ===================== LINUX INSTALLATION ===================== -->

## 🐧 Linux Installation

### Linux: Automatic Installation

```bash
# 1. Install PHP and required extensions (Debian/Ubuntu)
sudo apt update
sudo apt install -y php php-cli php-sqlite3 php-json php-mbstring php-curl php-xml php-intl

# CentOS/RHEL/Fedora
sudo dnf install -y php php-cli php-sqlite php-mbstring php-curl php-xml php-intl

# Alpine
sudo apk add php php-pdo_sqlite php-json php-mbstring php-curl php-openssl

# 2. Install Java (for Minecraft servers)
sudo apt install -y openjdk-17-jdk-headless   # Debian/Ubuntu
sudo dnf install -y java-17-openjdk-headless  # Fedora/RHEL

# 3. Clone and run
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel
php -S 0.0.0.0:8080 server.php
```

### Linux: Manual Installation

```bash
# 1. Download
cd /var/www/html
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel

# 2. Set permissions
chmod -R 755 .
chmod -R 777 storage/

# 3. Verify PHP requirements
php -m | grep -E 'sqlite|json|mbstring|curl|openssl'

# 4. Start development server
php -S 0.0.0.0:8080 server.php

# 5. Open http://your-server-ip:8080 and follow installation wizard
```

### Apache Setup

```apache
<VirtualHost *:80>
    ServerName panel.yourdomain.com
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

Make sure `.htaccess` files are enabled: `sudo a2enmod rewrite && sudo systemctl restart apache2`

### Nginx Setup

```nginx
server {
    listen 80;
    server_name panel.yourdomain.com;
    root /var/www/html/easyteam-panel;
    index index.php;

    # Static files
    location ~* \.(css|js|svg|woff2|woff|png|jpg|ico)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Block storage access
    location ~ /storage/ {
        deny all;
        return 403;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 🏃‍♂️ 3 Ways to Keep the Panel Running 24/7

When you run `php -S 0.0.0.0:8080 server.php` via SSH and then close the terminal, **the panel stops**. Below are 3 ways to keep it running forever.

---

#### ✅ Method 1: systemd Service (Recommended — Auto-start on Boot)

**Best for:** Production servers, VPS, 24/7 operation. The panel auto-starts on system boot and auto-restarts if it crashes.

##### Step 1: Find your paths

```bash
# 1. Find where PHP is installed
which php
# Example output: /usr/bin/php

# 2. Find your current user
whoami
# Example output: ubuntu

# 3. Find the panel path (run this INSIDE easyteam-panel folder)
pwd
# Example output: /home/ubuntu/easyteam-panel
```

##### Step 2: Copy the service file

We provide a ready-to-use service file (`easyteam-panel.service`). First, edit it with your paths:

```bash
# Open the service file
nano easyteam-panel.service

# 🔧 Change these 3 lines to match YOUR system:
#   User=www-data     → User=ubuntu          (your username)
#   Group=www-data    → Group=ubuntu         (your group)
#   WorkingDirectory= → WorkingDirectory=/home/ubuntu/easyteam-panel
#   ExecStart=        → (/usr/bin/php from step 1)
#
# Ctrl+X, then Y, then Enter to save
```

##### Step 3: Install and start the service

```bash
# Copy service file to systemd directory
sudo cp easyteam-panel.service /etc/systemd/system/

# Reload systemd to recognize the new service
sudo systemctl daemon-reload

# Enable auto-start on boot
sudo systemctl enable easyteam-panel

# Start the service NOW
sudo systemctl start easyteam-panel

# Check if it's running (press Q to quit)
sudo systemctl status easyteam-panel
```

If everything is correct, you'll see:
```
● easyteam-panel.service - EasyTeam Minecraft Panel
   Loaded: loaded (/etc/systemd/system/easyteam-panel.service; enabled; vendor preset: enabled)
   Active: active (running) since ...
```

##### Step 4: Verify it works

```bash
# The panel is now running at:
# http://YOUR_SERVER_IP:8080
#
# For example:
# http://localhost:8080        (if on same machine)
# http://192.168.1.100:8080   (on your local network)
# http://1.2.3.4:8080         (your VPS public IP)
```

##### Useful systemd Commands

```bash
# View live logs (press Ctrl+C to stop following)
sudo journalctl -u easyteam-panel -f

# View last 50 log lines
sudo journalctl -u easyteam-panel -n 50

# Restart the service
sudo systemctl restart easyteam-panel

# Stop the service
sudo systemctl stop easyteam-panel

# Disable auto-start
sudo systemctl disable easyteam-panel

# Check if service file has errors
sudo systemd-analyze verify /etc/systemd/system/easyteam-panel.service
```

##### ✅ After this, SSH disconnection will NOT stop the panel!

> **You can close the terminal, turn off your computer, or disconnect from SSH — the panel stays running.**
> When you reboot the server, the panel starts automatically.

---

#### ✅ Method 2: screen / tmux (Best for Development)

**Best for:** Testing, development, temporary runs. Easy to see live PHP errors.

##### Using screen:

```bash
# 1. Install screen (if not installed)
sudo apt install screen -y   # Debian/Ubuntu
sudo dnf install screen -y   # Fedora/RHEL

# 2. Start a new screen session named "panel" and run the panel
screen -dmS panel bash -c 'cd /path/to/easyteam-panel && php -S 0.0.0.0:8080 server.php'

# 3. Done! The panel is running in the background.
#    Even if you close SSH, it stays running.
```

```bash
# List all screen sessions
screen -ls

# Re-attach to see the live output (Ctrl+A, D to detach)
screen -r panel

# Inside screen: Ctrl+A, then D          → Detach (leave running)
# Inside screen: Ctrl+C                  → Stop the panel
# Inside screen: Type `exit` then Enter   → Kill the session

# Kill a session from outside
screen -XS panel quit
```

##### Using tmux:

```bash
# 1. Install tmux
sudo apt install tmux -y     # Debian/Ubuntu
sudo dnf install tmux -y     # Fedora/RHEL

# 2. Start a new tmux session
cd /path/to/easyteam-panel
tmux new-session -d -s panel 'php -S 0.0.0.0:8080 server.php'
```

```bash
# List tmux sessions
tmux ls

# Re-attach (Ctrl+B, D to detach)
tmux attach -t panel

# Inside tmux: Ctrl+B, then D      → Detach (leave running)
# Inside tmux: Ctrl+C              → Stop the panel

# Kill a session from outside
tmux kill-session -t panel
```

---

#### ✅ Method 3: nohup + disown (Simple & Lightweight)

**Best for:** Quick runs, no dependencies needed. Uses only built-in shell features.

```bash
# 1. Navigate to panel directory
cd /path/to/easyteam-panel

# 2. Start the panel with nohup (immune to hangup signal)
nohup php -S 0.0.0.0:8080 server.php > /dev/null 2>&1 &
#                   ↑              ↑            ↑   ↑
#                   |              |            |   └─ Run in background
#                   |              |            └─ Redirect errors to same place
#                   |              └─ Discard normal output
#                   └─ Ignore SIGHUP (SSH disconnect signal)

# 3. The panel is now running. Close SSH safely!
```

```bash
# To stop the panel, find and kill the process:
ps aux | grep 'php -S'
# Look for the PHP process and note its PID (e.g., 12345)
kill 12345

# Or kill all PHP dev servers at once:
pkill -f 'php -S 0.0.0.0:8080'

# Or use the PID file method:
echo $! > /tmp/panel.pid   # Save PID after starting
kill $(cat /tmp/panel.pid)  # Kill using saved PID
```

---

#### Comparison Table

| Feature | systemd | screen/tmux | nohup |
|---------|---------|-------------|-------|
| **Auto-start on boot** | ✅ Yes | ❌ No | ❌ No |
| **Auto-restart on crash** | ✅ Yes | ❌ No | ❌ No |
| **See live output** | `journalctl -f` | `screen -r` | ❌ No |
| **Requires install** | ✅ Built-in | ⚠️ `apt install` | ✅ Built-in |
| **Use case** | Production | Development | Quick test |

---

#### 🔧 Changing the Port

By default, the panel runs on **port 8080**. To use a different port:

```bash
# systemd: Edit the service file then restart
sudo nano /etc/systemd/system/easyteam-panel.service
# Change: ExecStart=/usr/bin/php -S 0.0.0.0:9090 server.php
sudo systemctl daemon-reload
sudo systemctl restart easyteam-panel

# screen/tmux/nohup: Just change the port number
screen -dmS panel php -S 0.0.0.0:9090 server.php
nohup php -S 0.0.0.0:9090 server.php > /dev/null 2>&1 &
```

#### 🔐 Firewall: Allow External Access

If you want to access the panel from other devices:

```bash
# Allow port 8080 through firewall (UFW - Ubuntu)
sudo ufw allow 8080/tcp
sudo ufw reload

# FirewallD (Fedora/RHEL/CentOS)
sudo firewall-cmd --add-port=8080/tcp --permanent
sudo firewall-cmd --reload

# iptables (any Linux)
sudo iptables -A INPUT -p tcp --dport 8080 -j ACCEPT
```

---

<!-- ===================== WINDOWS INSTALLATION ===================== -->

## 🪟 Windows Installation

### Prerequisites for Windows

| Software | Download Link | Notes |
|----------|--------------|-------|
| **PHP 8.0+** | [windows.php.net/download](https://windows.php.net/download/) | Download **non-thread-safe** ZIP, extract to `C:\php` |
| **JDK 17+** | [adoptium.net](https://adoptium.net/) | Required only for Minecraft servers |
| **Git** | [git-scm.com](https://git-scm.com/) | Optional, for cloning repository |

**After installing PHP:**
1. Extract the ZIP to `C:\php`
2. Add `C:\php` to your system PATH:
   - Open **System Properties** → **Advanced** → **Environment Variables**
   - Edit `Path` → Add `C:\php`
3. Enable required extensions in `C:\php\php.ini`:
   ```ini
   extension=sqlite3
   extension=pdo_sqlite
   extension=mbstring
   extension=curl
   extension=openssl
   extension=json
   ```
4. Verify: Open **Command Prompt** and run `php -v`

---

### Method 1: One-Click Batch Installer ⭐ (Recommended)

**`install_windows.bat`** — The easiest way to get started.

Just **double-click** the file! It will:

1. ✅ Check if PHP is installed and in PATH
2. ✅ Check if Java is available
3. ✅ Create all required storage directories
4. ✅ Start the panel on `http://localhost:8080`
5. ✅ Open your browser automatically
6. ✅ Wait for you to complete installation

```cmd
:: Navigate to the panel folder and double-click, or run:
install_windows.bat
```

**If you see "PHP not found":**
- Download PHP from [windows.php.net](https://windows.php.net/download/)
- Extract to `C:\php`
- Add `C:\php` to PATH
- Restart Command Prompt and run `install_windows.bat` again

**Changing the port:**
The batch installer uses port **8080** by default. To use a different port, edit the file:
- Change `localhost:8080` to your preferred port
- Change `php -S localhost:8080 server.php` to your preferred port

---

### Method 2: PowerShell Runner

**`server.ps1`** — More control and better output formatting.

```powershell
# Basic usage
powershell -ExecutionPolicy Bypass -File server.ps1

# Custom port
powershell -ExecutionPolicy Bypass -File server.ps1 -Port 9090

# Custom directory
powershell -ExecutionPolicy Bypass -File server.ps1 -Port 8080

# Run from within PowerShell
.\server.ps1 -Port 8080
```

**PowerShell features:**
- Color-coded output (green for success, red for errors, yellow for warnings)
- Structured parameter handling
- Proper error handling with `$ErrorActionPreference = "Stop"`
- Supports custom port via `-Port` parameter
- Automatic storage directory creation

**If you get "execution policy" error:**
```powershell
# Run PowerShell as Administrator and run:
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
# Then run:
.\server.ps1
```

---

### Method 3: Manual Setup

```cmd
:: Step 1: Clone or download the panel
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel

:: Or download ZIP and extract

:: Step 2: Create storage directories
mkdir storage\database
mkdir storage\servers
mkdir storage\logs
mkdir storage\versions

:: Step 3: Start the panel
php -S localhost:8080 server.php
```

Then open **http://localhost:8080** in your browser.

---

### Run as Windows Service

Using **NSSM (Non-Sucking Service Manager)**:

```cmd
:: Download NSSM from https://nssm.cc/download
:: Extract to C:\nssm

:: Install the service
C:\nssm\win64\nssm.exe install EasyTeamPanel

:: Configure:
::   Application Path: C:\php\php.exe
::   Startup Directory: C:\path\to\easyteam-panel
::   Arguments: -S 0.0.0.0:8080 server.php

:: Start the service
C:\nssm\win64\nssm.exe start EasyTeamPanel

:: The panel will now run as a background service
:: It will auto-start on system boot
```

Using **Task Scheduler** (no third-party tools):
```powershell
# Create a scheduled task
$action = New-ScheduledTaskAction -Execute "php.exe" `
    -Argument "-S 0.0.0.0:8080 server.php" `
    -WorkingDirectory "C:\path\to\easyteam-panel"

$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount

Register-ScheduledTask -TaskName "EasyTeamPanel" `
    -Action $action `
    -Trigger $trigger `
    -Principal $principal `
    -Description "EasyTeam Minecraft Server Panel"
```

---

### Firewall Configuration

To allow connections from other devices on your network:

```powershell
# Run PowerShell as Administrator
New-NetFirewallRule -DisplayName "EasyTeam Panel" `
    -Direction Inbound -Protocol TCP -LocalPort 8080 -Action Allow
```

```cmd
:: Command Prompt (as Administrator)
netsh advfirewall firewall add rule name="EasyTeam Panel" dir=in action=allow protocol=TCP localport=8080
```

---

<!-- ===================== USAGE GUIDE ===================== -->

## 📖 Usage Guide

### Installation Wizard

When you first open the panel, the **Installation Wizard** will guide you through 4 steps:

1. **System Check** — Verify PHP, SQLite, JSON, MBString, cURL, and OpenSSL
2. **Database Setup** — Initialize the SQLite database
3. **Admin Account** — Create your admin username, email, and password
4. **Complete** — Ready to use!

> **Note:** Java check can be skipped during installation. Install Java later from the Settings page.

### Creating Your First Server

1. Log in with your admin account
2. Click **"Create Server"** from Dashboard or Servers page
3. Fill in the details:
   - **Server Name**: e.g., "Survival World"
   - **Minecraft Version**: 1.20.1 (default)
   - **Server Type**: Paper (recommended) or Vanilla
   - **Port**: 25565 (default Minecraft port)
   - **RAM**: 1024 MB minimum (adjust based on your system)
   - **Max Players**: 20
   - **Game Mode**: Survival/Creative/Adventure/Spectator
   - **Difficulty**: Peaceful/Easy/Normal/Hard
   - **MOTD**: Server description shown in server list
4. Click **"Create Server"**
5. On the Servers page, click **"Start"** to launch the server
6. Wait a moment — the server is starting!

### Console & File Management

**Console:**
- View real-time server output
- Send commands like `/op player`, `/gamemode creative`, `/stop`
- Auto-scroll follows new output
- Clear console to remove clutter

**File Manager:**
- Browse server directory structure
- Edit files like `server.properties`, `ops.json`, `bukkit.yml`
- Upload new files (plugins, configurations)
- Create new files and folders
- Rename and delete files

### Installing Minecraft Versions

1. Go to **"Version Installer"** in the sidebar
2. Browse available Minecraft versions (PaperMC API)
3. Click **Paper** or **Vanilla** button to install a version
4. Wait for the download to complete
5. The installed version will now appear when creating servers

> **For Iranian users:** The panel uses GitHub mirror for downloads. Go to **Settings** → **Download Mirror** → select "GitHub" if not already selected.

---

## ⚙️ Configuration

### config.php

The panel is configured via `config.php` (auto-generated during installation):

```php
define('DB_PATH', __DIR__ . '/storage/database/panel.sqlite');
define('SITE_NAME', 'EasyTeam Panel');
define('SITE_URL', 'http://localhost:8080');
define('TIMEZONE', 'Asia/Tehran');
define('LANGUAGE', 'fa');              // 'fa' or 'en'
define('MINECRAFT_BASE_PATH', __DIR__ . '/storage/servers');
define('MINECRAFT_VERSIONS_PATH', __DIR__ . '/storage/versions');
define('MINECRAFT_MIN_RAM', 256);
define('MINECRAFT_MAX_RAM', 8192);
define('JAVA_PATH', 'java');
define('SESSION_NAME', 'EASYTEAM_PANEL');
define('SESSION_LIFETIME', 86400);     // 24 hours
define('PANEL_VERSION', '1.0.0');
```

### Environment Variables (Optional)

These can override config.php settings at runtime:

| Variable | Description |
|----------|-------------|
| `JAVA_HOME` | Path to JDK installation (Windows auto-detection) |
| `JRE_HOME` | Path to JRE installation (Windows auto-detection) |

---

## 🔧 Troubleshooting

### Linux Issues

| Problem | Solution |
|---------|----------|
| **Port 8080 in use** | `php -S 0.0.0.0:9090 server.php` (use any port) |
| **"SQLite not found"** | `sudo apt install php-sqlite3 php-pdo` (Ubuntu) or `sudo dnf install php-sqlite` (Fedora) |
| **"Permission denied"** | `sudo chmod -R 777 storage/` |
| **"Class 'ServerManager' not found"** | Check that `config.php` exists and includes are loading correctly |
| **Server won't start** | Install Java: `sudo apt install openjdk-17-jdk-headless` |
| **Blank page** | Check PHP errors: `php -l index.php` and check `storage/logs/error.log` |
| **PHP version too old** | Add PPA: `sudo add-apt-repository ppa:ondrej/php && sudo apt update && sudo apt install php8.1` |

### Windows Issues

| Problem | Solution |
|---------|----------|
| **"PHP is not recognized"** | Add PHP to PATH or run `set PATH=%PATH%;C:\php` before running |
| **"Access denied" on port** | Run Command Prompt **as Administrator** |
| **Firewall blocking** | See [Firewall Configuration](#firewall-configuration) above |
| **PHP extensions missing** | Edit `C:\php\php.ini` and uncomment `extension=sqlite3`, `extension=curl`, etc. |
| **"VCRUNTIME140.dll not found"** | Install [VC Redistributable](https://aka.ms/vs/17/release/vc_redist.x64.exe) |
| **Console shows Chinese/garbled text** | `chcp 65001` (UTF-8) — already included in `install_windows.bat` |
| **PowerShell execution policy** | `powershell -ExecutionPolicy Bypass -File server.ps1` |
| **Slow PHP on Windows** | Use **non-thread-safe** PHP version for better performance |
| **Port 8080 already in use** | Edit `install_windows.bat` and change to another port (e.g., 9090) |
| **Java not found** | Install JDK from [adoptium.net](https://adoptium.net/) and add to PATH |

### Common Issues

| Problem | Solution |
|---------|----------|
| **Console shows no output** | Start the server first, then open console |
| **Version download fails** | Go to Settings → change Download Mirror to "GitHub" |
| **Server stuck on "Starting"** | Check Java installation and server log file |
| **Session expired frequently** | Increase `SESSION_LIFETIME` in `config.php` |
| **reCAPTCHA not working** | Replace test keys with real ones from [google.com/recaptcha](https://www.google.com/recaptcha/admin) |
| **CSS/JS not loading** | Clear browser cache or hard refresh (Ctrl+Shift+R) |
| **Can't log in after install** | Reset database: delete `storage/database/panel.sqlite` and re-run installation |

---

## 🌐 API Reference

The panel provides JSON API endpoints for AJAX interactions:

| Endpoint | Method | Parameters | Description |
|----------|--------|------------|-------------|
| `index.php?page=api&action=console_command` | POST | `server_id`, `command` | Send command to server |
| `index.php?page=api&action=console_output` | GET | `server_id` | Get server console output |
| `index.php?page=api&action=server_status` | GET | `server_id` | Get server status |
| `index.php?page=api&action=install_java` | POST | — | Auto-install JDK 17+ |
| `index.php?page=api&action=set_language` | GET | `lang` (fa/en) | Change panel language |

---

## 📁 Project Structure

```
easyteam-panel/
├── index.php              # Main SPA Router
├── install.php            # Installation Wizard
├── server.php             # PHP Dev Server Router
├── install_windows.bat    # Windows One-Click Installer
├── server.ps1             # PowerShell Runner
├── config.php             # Panel Configuration
├── config.example.php     # Configuration Template
├── .htaccess              # Apache Rewrite Rules
├── includes/
│   ├── bootstrap.php      # App Initialization
│   ├── database.php       # SQLite PDO Wrapper
│   ├── auth.php           # Authentication System
│   ├── language.php       # Dual Language System
│   ├── functions.php      # Helper Functions
│   └── server_manager.php # Minecraft Server Process Manager
├── pages/
│   ├── dashboard.php      # Dashboard with Stats
│   ├── login.php          # Login (with reCAPTCHA)
│   ├── register.php       # User Registration
│   ├── servers.php        # Server List & Create
│   ├── server-detail.php  # Server Details & Controls
│   ├── console.php        # Real-time Console
│   ├── files.php          # File Manager
│   ├── users.php          # User Management (Admin)
│   ├── settings.php       # Panel Settings
│   ├── versions.php       # Minecraft Version Installer
│   └── api.php            # JSON API Endpoints
├── templates/
│   ├── header.php         # Sidebar + Topbar + SVG Sprite
│   └── footer.php         # Footer + Scripts
├── lang/
│   ├── fa.php             # Persian Translations
│   └── en.php             # English Translations
├── assets/
│   ├── css/style.css      # SPA-Optimized Stylesheet
│   ├── js/app.js          # SPA Navigation Engine
│   ├── icons/sprite.svg   # SVG Icon Sprite (50+ icons)
│   └── fonts/             # Local Vazir Font Files
└── storage/
    ├── database/          # SQLite Database
    ├── servers/           # Minecraft Server Directories
    ├── logs/              # Error Logs
    └── versions/          # Downloaded Minecraft JARs
```

---

## 🔒 Security

- **CSRF Protection** — Token-based form validation
- **SQL Injection Prevention** — PDO prepared statements throughout
- **Path Traversal Protection** — `realpath()` + `basename()` validation
- **Password Hashing** — bcrypt with cost factor 12
- **Session Security** — httpOnly cookies, SameSite=Lax
- **Input Validation** — All user inputs sanitized before processing
- **File Upload Safety** — Restricted to server directories
- **reCAPTCHA** — Google reCAPTCHA v2 on login form
- **Storage Protection** — HTTP rules block direct database access

---

## 📄 License

**MIT License** — Free to use, modify, and distribute.

Built with ❤️ for Minecraft server administrators worldwide.

---

<!-- ====================================================================== -->
<!-- 🇮🇷 PERSIAN SECTION                                                       -->
<!-- ====================================================================== -->

---

<h2 dir="rtl">🇮🇷 فارسی</h2>

<p dir="rtl"><strong>پنل ایزی‌تیم</strong> — یک پنل مدیریت سرور ماینکرفت حرفه‌ای، سبک و سریع</p>

---

### 📋 فهرست مطالب

- [معرفی](#-معرفی)
- [امکانات](#-امکانات)
- [نیازمندی‌های سیستم](#-نیازمندیهای-سیستم)
- [نصب روی لینوکس](#-نصب-روی-لینوکس)
- [نصب روی ویندوز](#-نصب-روی-ویندوز)
- [راهنمای استفاده](#-راهنمای-استفاده)
- [عیب‌یابی](#-عیبیابی)
- [نکات کاربران ایرانی](#-نکات-کاربران-ایرانی)

---

### 📖 معرفی

پنل ایزی‌تیم یک پنل مدیریت سرور ماینکرفت تحت وب است که با PHP و SQLite ساخته شده. این پنل از معماری **تک صفحه‌ای (SPA)** استفاده می‌کند یعنی بدون رفرش شدن صفحه، بین بخش‌های مختلف جابه‌جا می‌شوید — درست مثل پنل‌های Pterodactyl و PufferPanel.

### ✨ امکانات

- **مدیریت کامل سرور**: ساخت، شروع، توقف، راه‌اندازی مجدد و حذف سرور
- **کنسول بی‌درنگ**: مشاهده خروجی و ارسال دستور به سرور بدون تاخیر
- **مدیریت فایل**: مرور، آپلود، ویرایش، تغییر نام و حذف فایل‌ها
- **نصب نسخه**: نصب خودکار نسخه‌های Paper و Vanilla ماینکرفت
- **سیستم کاربری**: ثبت‌نام، ورود، سطوح دسترسی ادمین/کاربر
- **دو زبانه**: فارسی (راست‌چین) و انگلیسی با فونت وزیر
- **SPA**: جابه‌جایی بین صفحات بدون رفرش
- **امنیت**: محافظت CSRF، تزریق SQL، مسیریابی
- **Google reCAPTCHA**: امنیت در ورود
- **آیکون‌های SVG**: بیش از ۵۰ آیکون برداری به جای ایموجی

---

### 💻 نیازمندی‌های سیستم

| نیازمندی | توضیحات |
|---------|---------|
| **PHP** | نسخه ۸.۰ یا بالاتر |
| **افزونه‌ها** | sqlite3, pdo_sqlite, json, mbstring, curl, openssl |
| **Java** | JDK 17+ (فقط برای اجرای سرور ماینکرفت) |
| **فضا** | حداقل ۱ گیگابایت فضای خالی |

---

### 🐧 نصب روی لینوکس

#### نصب خودکار

```bash
# نصب PHP و افزونه‌ها (Ubuntu/Debian)
sudo apt update
sudo apt install -y php php-cli php-sqlite3 php-json php-mbstring php-curl php-xml

# نصب جاوا
sudo apt install -y openjdk-17-jdk-headless

# دانلود و اجرا
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel
php -S 0.0.0.0:8080 server.php
```

#### نصب دستی

```bash
cd /var/www/html
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel
chmod -R 755 .
chmod -R 777 storage/
php -S 0.0.0.0:8080 server.php
```

#### 🏃 اجرای ۲۴ ساعته پنل (حتی بعد از بستن SSH)

وقتی پنل رو با `php -S 0.0.0.0:8080 server.php` اجرا می‌کنی و ترمینال رو می‌بندی، **پنل خاموش می‌شه**. سه راه داریم:

**✅ روش ۱: systemd (حرفه‌ای - همیشه روشن)**

```bash
# ۱. فایل سرویس رو کپی کن
sudo cp easyteam-panel.service /etc/systemd/system/

# ۲. ادیت کن (مسیرها رو درست کن)
sudo nano /etc/systemd/system/easyteam-panel.service
# تغییر بده:
#   User=www-data   ← User=USERNAME    (با `whoami` ببین)
#   WorkingDirectory=  ← مسیر درست پوشه پنل (با `pwd` ببین)
# ذخیره کن: Ctrl+X, Y, Enter

# ۳. فعال و اجرا کن
sudo systemctl daemon-reload
sudo systemctl enable easyteam-panel
sudo systemctl start easyteam-panel

# ۴. چک کن
sudo systemctl status easyteam-panel
```

دستورات کاربردی:
```bash
# لاگ زنده
sudo journalctl -u easyteam-panel -f

# ریستارت
sudo systemctl restart easyteam-panel

# توقف
sudo systemctl stop easyteam-panel
```

**✅ روش ۲: screen (آسان - مخصوص تست)**

```bash
# نصب
sudo apt install screen -y

# اجرا در پس‌زمینه (حتی با بستن SSH می‌مونه)
screen -dmS panel bash -c 'cd /path/to/panel && php -S 0.0.0.0:8080 server.php'

# مشاهده خروجی
screen -r panel          # Ctrl+A, D برای خارج شدن (بدون توقف)

# توقف
screen -XS panel quit
```

**✅ روش ۳: nohup (ساده و سریع - بدون نصب)**

```bash
cd /path/to/easyteam-panel
nohup php -S 0.0.0.0:8080 server.php > /dev/null 2>&1 &

# توقف:
pkill -f 'php -S 0.0.0.0:8080'
```

**مقایسه:**
| روش | auto-start | auto-restart | خروجی زنده |
|-----|-----------|-------------|-----------|
| systemd | ✅ بله | ✅ بله | ✅ `journalctl -f` |
| screen | ❌ خیر | ❌ خیر | ✅ `screen -r` |
| nohup | ❌ خیر | ❌ خیر | ❌ خیر |

**🔥 تنظیم پورت:**
```bash
# پورت 9090 به جای 8080:
sudo nano /etc/systemd/system/easyteam-panel.service
# عوض کن: ExecStart=...:9090...
sudo systemctl daemon-reload && sudo systemctl restart easyteam-panel
```

**🔐 باز کردن پورت در فایروال:**
```bash
sudo ufw allow 8080/tcp   # Ubuntu
sudo firewall-cmd --add-port=8080/tcp --permanent && sudo firewall-cmd --reload  # Fedora
```

---

### 🪟 نصب روی ویندوز

#### روش ۱ - نصب با کلیک (پیشنهادی) ⭐

فایل **`install_windows.bat`** را **دوبار کلیک** کنید. خودکار:

1. PHP و Java را بررسی می‌کند
2. پوشه‌های مورد نیاز را می‌سازد
3. پنل را روی `http://localhost:8080` اجرا می‌کند
4. مرورگر را باز می‌کند

> اگر PHP نصب نیست: از [windows.php.net](https://windows.php.net/download/) دانلود کنید، به `C:\php` استخراج کنید و به PATH اضافه کنید.

#### روش ۲ - PowerShell

```powershell
powershell -ExecutionPolicy Bypass -File server.ps1
:: یا با پورت دلخواه:
powershell -ExecutionPolicy Bypass -File server.ps1 -Port 9090
```

#### روش ۳ - دستی

```cmd
git clone https://github.com/yourusername/easyteam-panel.git
cd easyteam-panel
mkdir storage\database storage\servers storage\logs storage\versions
php -S localhost:8080 server.php
```

#### نصب به عنوان سرویس ویندوز

با NSSM:
```cmd
nssm.exe install EasyTeamPanel
# مسیر php: C:\php\php.exe
# آرگومان: -S 0.0.0.0:8080 server.php
# دایرکتوری: C:\path\to\easyteam-panel
nssm.exe start EasyTeamPanel
```

#### تنظیم فایروال

```powershell
# PowerShell as Administrator
New-NetFirewallRule -DisplayName "EasyTeam Panel" -Direction Inbound -Protocol TCP -LocalPort 8080 -Action Allow
```

---

### 📖 راهنمای استفاده

#### ویزارد نصب

بار اول که پنل را باز می‌کنید، **ویزارد نصب** شما را راهنمایی می‌کند:

1. **بررسی سیستم** — PHP، SQLite، JSON، MBString و... بررسی می‌شود
2. **دیتابیس** — دیتابیس SQLite ساخته می‌شود
3. **حساب ادمین** — نام کاربری، ایمیل و رمز عبور ادمین را تعریف کنید
4. **پایان** — پنل آماده استفاده است!

#### ساخت اولین سرور

1. وارد پنل شوید
2. روی **"ایجاد سرور"** کلیک کنید
3. اطلاعات را وارد کنید:
   - **نام**: مثلاً "Survival 1.20.1"
   - **نسخه**: 1.20.1 (پیش‌فرض)
   - **نوع**: Paper (پیشنهادی) یا Vanilla
   - **پورت**: 25565
   - **RAM**: حداقل ۱۰۲۴ مگابایت
   - **حداکثر بازیکنان**: ۲۰
4. روی **"ایجاد سرور"** کلیک کنید
5. روی **"شروع"** کلیک کنید تا سرور اجرا شود
6. از **کنسول** برای ارسال دستور استفاده کنید

---

### 🔧 عیب‌یابی

| مشکل | راه حل |
|------|--------|
| **پورت 8080 اشغال است** | از پورت دیگر استفاده کنید: `php -S 0.0.0.0:9090 server.php` |
| **SQLite کار نمی‌کند** | افزونه‌های php-sqlite3 و php-pdo را نصب کنید |
| **خطای دسترسی** | `sudo chmod -R 777 storage/` در لینوکس |
| **PHP پیدا نمی‌شود (ویندوز)** | PHP را به PATH اضافه کنید |
| **کنسول خالی است** | ابتدا سرور را روشن کنید، سپس کنسول را باز کنید |
| **دانلود نسخه انجام نمی‌شود** | به تنظیمات بروید و آینه را به "گیت‌هاب" تغییر دهید |
| **جاوا نصب نیست** | از صفحه تنظیمات روی دکمه "نصب JDK 17" کلیک کنید |
| **VCRUNTIME140.dll not found** | [VC Redistributable](https://aka.ms/vs/17/release/vc_redist.x64.exe) را نصب کنید |

---

### 🇮🇷 نکات کاربران ایرانی

**رفع تحریم و فیلتر بودن سایت‌ها:**

پنل ایزی‌تیم به طور کامل برای کاربران ایرانی بهینه شده است:

1. **آینه گیت‌هاب**: تمامی دانلودها از طریق گیت‌هاب انجام می‌شود که در ایران فیلتر نیست
2. **عدم نیاز به فیلترشکن**: پنل بدون فیلترشکن کار می‌کند
3. **تنظیمات آینه**: به بخش **تنظیمات** بروید و **"گیت‌هاب"** را انتخاب کنید
4. **دانلود نسخه‌ها**: تمام نسخه‌های ماینکرفت از طریق گیت‌هاب قابل دانلود هستند

```bash
# دانلود مستقیم از گیت‌هاب (بدون تحریم)
git clone https://github.com/yourusername/easyteam-panel.git

# اگر git در دسترس نیست
wget https://github.com/yourusername/easyteam-panel/archive/refs/heads/main.zip
unzip main.zip
cd easyteam-panel-main
```
