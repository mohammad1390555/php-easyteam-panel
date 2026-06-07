@echo off
title EasyTeam Panel - Windows Installer
chcp 65001 >nul 2>&1

:: ============================================
:: EasyTeam Panel - Windows One-Click Installer
:: ============================================
:: This script checks prerequisites, creates
:: storage directories, and starts the panel.
:: ============================================

setlocal enabledelayedexpansion

set "PORT=8080"
set "PANEL_DIR=%~dp0"

:: Check for custom port argument
if not "%1"=="" set "PORT=%1"

echo ============================================
echo   EasyTeam Panel - Windows Installer
echo   پنل ایزی‌تیم - نصب روی ویندوز
echo ============================================
echo.

:: === Check Administrator ===
net session >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [!] You are NOT running as Administrator.
    echo     Some features may not work properly.
    echo     Recommended: Right-click ^> Run as Administrator
    echo.
    timeout /t 3 /nobreak >nul
) else (
    echo [OK] Running as Administrator
)

:: === Check PHP ===
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] PHP not found!
    echo.
    echo PHP is REQUIRED to run the panel.
    echo.
    echo To install PHP:
    echo   1. Download PHP 8.0+ from: https://windows.php.net/download/
    echo   2. Extract the ZIP to C:\php
    echo   3. Add C:\php to your system PATH:
    echo      System Properties ^> Advanced ^> Environment Variables
    echo      Edit "Path" ^> Add "C:\php"
    echo   4. Open a NEW Command Prompt and run this installer again
    echo.
    echo Or run this command to set PATH temporarily:
    echo   set PATH=%%PATH%%;C:\php ^&^& install_windows.bat
    echo.
    pause
    exit /b 1
)

:: Show PHP version
for /f "tokens=1-2 delims= " %%a in ('php -v 2^>nul ^| findstr /i "PHP"') do (
    echo [OK] %%a %%b
    goto :php_ok
)
:php_ok

:: Check PHP extensions
echo Checking PHP extensions...
php -m > "%TEMP%\php_modules.txt" 2>nul
set "MISSING_EXT="
for %%e in (sqlite3 pdo_sqlite json mbstring curl openssl) do (
    findstr /i "%%e" "%TEMP%\php_modules.txt" >nul 2>&1
    if !ERRORLEVEL! NEQ 0 (
        echo [WARN] Missing PHP extension: %%e
        set "MISSING_EXT=1"
    )
)
if defined MISSING_EXT (
    echo [!] Some PHP extensions are missing.
    echo     Edit C:\php\php.ini and uncomment these lines:
    echo     extension=sqlite3
    echo     extension=pdo_sqlite
    echo     extension=mbstring
    echo     extension=curl
    echo     extension=openssl
    echo.
    echo     Then restart this installer.
    echo.
    pause
    exit /b 1
) else (
    echo [OK] All PHP extensions found
)
del "%TEMP%\php_modules.txt" 2>nul

:: === Check Java ===
where java >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [WARN] Java not found.
    echo        Minecraft servers REQUIRE JDK 17+.
    echo        The panel will still run without Java.
    echo.
    echo To install Java:
    echo   1. Download JDK 17 from: https://adoptium.net/
    echo   2. Run the installer
    echo   3. Java will be added to PATH automatically
    echo.
    timeout /t 3 /nobreak >nul
) else (
    for /f "tokens=1-3" %%a in ('java -version 2^>^&1 ^| findstr /i "version"') do (
        echo [OK] Java %%b
    )
)

:: === Create Storage Directories ===
echo.
echo Creating storage directories...
if not exist "%PANEL_DIR%storage\database" mkdir "%PANEL_DIR%storage\database"
if not exist "%PANEL_DIR%storage\servers" mkdir "%PANEL_DIR%storage\servers"
if not exist "%PANEL_DIR%storage\logs" mkdir "%PANEL_DIR%storage\logs"
if not exist "%PANEL_DIR%storage\versions" mkdir "%PANEL_DIR%storage\versions"
echo [OK] Directories created

:: Check if port is in use
netstat -an | findstr ":%PORT% " >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo.
    echo [WARN] Port %PORT% is already in use!
    echo        Try a different port.
    echo        Usage: install_windows.bat 9090
    echo.
    timeout /t 3 /nobreak >nul
)

:: === Start Panel ===
echo.
echo ============================================
echo  Starting EasyTeam Panel
echo  URL:      http://localhost:%PORT%
echo  Directory: %PANEL_DIR%
echo  PHP:      php -S localhost:%PORT% server.php
echo ============================================
echo.
echo The installation wizard will open in your browser.
echo Follow the steps to create your admin account.
echo.
echo Press Ctrl+C to stop the server.
echo.
timeout /t 3 /nobreak >nul

:: Open browser and start PHP server
start http://localhost:%PORT%
cd /d "%PANEL_DIR%"
php -S localhost:%PORT% server.php

echo.
echo Server stopped.
pause
