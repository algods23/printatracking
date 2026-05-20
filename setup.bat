@echo off
REM Printa Signages - Quick Setup Script for Windows
REM This script sets up the entire project for first-time use

echo.
echo ========================================
echo Printa Signages Setup
echo ========================================
echo.

REM Check if Node.js is installed
where node >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Node.js is not installed or not in PATH
    echo Please install Node.js from https://nodejs.org/
    pause
    exit /b 1
)

REM Check if PHP is installed
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please ensure PHP is available in your system PATH
    pause
    exit /b 1
)

REM Check if Composer is installed
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Composer is not installed or not in PATH
    echo Please install Composer from https://getcomposer.org/
    pause
    exit /b 1
)

echo [1/6] Installing PHP dependencies with Composer...
call composer install
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Composer install failed
    pause
    exit /b 1
)

echo [2/6] Installing Node dependencies...
call npm install
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: npm install failed
    pause
    exit /b 1
)

echo [3/6] Copying .env file...
if not exist .env (
    copy .env.example .env
    echo .env file created
) else (
    echo .env file already exists
)

echo [4/6] Generating application key...
call php artisan key:generate

echo [5/6] Running database migrations...
call php artisan migrate

echo [6/6] Seeding demo data...
call php artisan db:seed

echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Default Credentials:
echo   Admin Email: admin@printa.local
echo   Admin Password: password
echo.
echo   Staff Email: staff@printa.local
echo   Staff Password: password
echo.
echo To start development:
echo   npm run electron-dev
echo.
echo To build for production:
echo   npm run electron-build-win
echo.
pause
