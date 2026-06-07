<#
.SYNOPSIS
    EasyTeam Panel - PowerShell Server Runner
.DESCRIPTION
    Starts the EasyTeam Minecraft Server Management Panel on Windows.
    Uses the PHP built-in development server.
.PARAMETER Port
    TCP port to run the panel on (default: 8080)
.PARAMETER BindAddress
    IP address to bind to (default: 0.0.0.0 = all interfaces)
.EXAMPLE
    .\server.ps1
    .\server.ps1 -Port 9090
    .\server.ps1 -Port 8080 -BindAddress 127.0.0.1
.NOTES
    Author: EasyTeam Panel
    Requires: PHP 8.0+, PowerShell 5.1+
#>

param(
    [int]$Port = 8080,
    [string]$BindAddress = "0.0.0.0"
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$Host.UI.RawUI.WindowTitle = "EasyTeam Panel - http://localhost:$Port"

# ---- Color Output Helpers ----
function Write-Success($msg) { Write-Host "[OK] $msg" -ForegroundColor Green }
function Write-Warning($msg) { Write-Host "[!] $msg" -ForegroundColor Yellow }
function Write-Error($msg)   { Write-Host "[ERROR] $msg" -ForegroundColor Red }
function Write-Info($msg)    { Write-Host "[i] $msg" -ForegroundColor Cyan }

# ---- Header ----
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  EasyTeam Panel - PowerShell Runner"        -ForegroundColor Cyan
Write-Host "  پنل ایزی‌تیم - اجرا روی ویندوز"             -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ---- Check Administrator ----
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Warning "Not running as Administrator. Some features may not work."
    Write-Info "Recommended: Right-click PowerShell > Run as Administrator"
    Write-Host ""
}
else {
    Write-Success "Running as Administrator"
}

# ---- Check PHP ----
$php = Get-Command "php" -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Error "PHP not found! PHP 8.0+ is required."
    Write-Host ""
    Write-Host "To install PHP:" -ForegroundColor Yellow
    Write-Host "  1. Download from: https://windows.php.net/download/" -ForegroundColor Yellow
    Write-Host "  2. Extract to C:\php" -ForegroundColor Yellow
    Write-Host "  3. Add C:\php to your system PATH" -ForegroundColor Yellow
    Write-Host "  4. Open a NEW PowerShell window and run this script again" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Or set PATH temporarily:" -ForegroundColor Yellow
    Write-Host "  `$env:Path += ';C:\php'; .\server.ps1" -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

# Show PHP version
$version = & php -v | Select-String "PHP"
if ($version) {
    Write-Success "$($version.ToString().Trim())"
}

# Check PHP extensions
Write-Info "Checking PHP extensions..."
$requiredExtensions = @('sqlite3', 'pdo_sqlite', 'json', 'mbstring', 'curl', 'openssl')
$phpModules = & php -m
$missingExtensions = $requiredExtensions | Where-Object { $phpModules -notcontains $_ }

if ($missingExtensions.Count -gt 0) {
    Write-Warning "Missing PHP extension(s): $($missingExtensions -join ', ')"
    Write-Host "Edit C:\php\php.ini and uncomment:" -ForegroundColor Yellow
    foreach ($ext in $missingExtensions) {
        Write-Host "  extension=$ext" -ForegroundColor Yellow
    }
    Write-Host ""
    exit 1
}
Write-Success "All PHP extensions found"

# ---- Check Java ----
$java = Get-Command "java" -ErrorAction SilentlyContinue
if ($java) {
    $javaVer = & java -version 2>&1 | Select-String "version"
    if ($javaVer) {
        Write-Success "Java $($javaVer.ToString().Trim())"
    }
}
else {
    Write-Warning "Java not found. Minecraft servers require JDK 17+."
    Write-Info "Download from: https://adoptium.net/"
    Write-Host ""
}

# ---- Check Storage Directories ----
Write-Info "Checking storage directories..."
$dirs = @(
    @{Path = "storage\database"; Name = "Database"},
    @{Path = "storage\servers";  Name = "Servers"},
    @{Path = "storage\logs";     Name = "Logs"},
    @{Path = "storage\versions"; Name = "Versions"}
)

foreach ($dir in $dirs) {
    $fullPath = Join-Path $ProjectRoot $dir.Path
    if (-not (Test-Path $fullPath)) {
        New-Item -ItemType Directory -Path $fullPath -Force | Out-Null
        Write-Info "Created $($dir.Name) directory"
    }
}
Write-Success "Storage directories ready"

# ---- Check Port ----
$tcpConnection = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue
if ($tcpConnection) {
    Write-Warning "Port $Port is already in use by: $($tcpConnection.OwningProcess)"
    Write-Info "Try a different port: .\server.ps1 -Port 9090"
    Write-Host ""
    
    $choice = Read-Host "Continue anyway? (y/N)"
    if ($choice -ne 'y' -and $choice -ne 'Y') {
        Write-Host "Exiting." -ForegroundColor Yellow
        exit 0
    }
}

# ---- Firewall Hint ----
if ($BindAddress -ne "127.0.0.1" -and $BindAddress -ne "localhost") {
    Write-Info "To allow external access, run this command as Administrator:"
    Write-Host "  New-NetFirewallRule -DisplayName 'EasyTeam Panel ($Port)' -Direction Inbound -Protocol TCP -LocalPort $Port -Action Allow" -ForegroundColor Gray
    Write-Host ""
}

# ---- Start Server ----
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "  Starting panel on http://localhost:$Port"   -ForegroundColor Green
Write-Host "  Press Ctrl+C to stop the server"            -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Green
Write-Host ""

# Open browser
Start-Process "http://localhost:$Port"

# Change to project directory
Set-Location $ProjectRoot

# Start PHP server
& php -S "${BindAddress}:${Port}" server.php

# Clean up on exit
Write-Host ""
Write-Host "Server stopped." -ForegroundColor Yellow
