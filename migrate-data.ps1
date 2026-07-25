# Printa Signages Data Migration Script
# This script fixes hardcoded paths when migrating data between computers

param(
    [string]$SourcePath = "."  # Path to the copied Printa Signages folder
)

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Printa Signages Data Migration Tool" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Get current username
$currentUsername = $env:USERNAME
Write-Host "Current user: $currentUsername" -ForegroundColor Green

# Define correct paths for this computer - try both possible folder names
$possibleAppDataNames = @("Printa Signages", "printa-signages")
$correctAppData = $null

foreach ($name in $possibleAppDataNames) {
    $testPath = Join-Path $env:APPDATA $name
    if (Test-Path $testPath) {
        $correctAppData = $testPath
        Write-Host "Found AppData folder: $correctAppData" -ForegroundColor Green
        break
    }
}

if (-not $correctAppData) {
    Write-Host "ERROR: Neither 'Printa Signages' nor 'printa-signages' folder found in AppData" -ForegroundColor Red
    exit 1
}

$correctDatabase = Join-Path $correctAppData "runtime\database\database.sqlite"
$correctBackupFolder = Join-Path $env:USERPROFILE "Documents\Printa Signages\backups"

Write-Host "Correct database path: $correctDatabase" -ForegroundColor Green
Write-Host "Correct backup folder: $correctBackupFolder" -ForegroundColor Green
Write-Host ""

# Check if config.json exists
$configPath = Join-Path $correctAppData "config.json"

if (-not (Test-Path $configPath)) {
    Write-Host "ERROR: config.json not found at $configPath" -ForegroundColor Red
    Write-Host "Please make sure you've copied the data from the old computer first." -ForegroundColor Yellow
    exit 1
}

Write-Host "Found config.json at: $configPath" -ForegroundColor Green
Write-Host ""

# Read the current config
$config = Get-Content $configPath -Raw | ConvertFrom-Json

Write-Host "Current configuration:" -ForegroundColor Yellow
Write-Host "  Database: $($config.database)" -ForegroundColor White
Write-Host "  Backup Folder: $($config.backupFolder)" -ForegroundColor White
Write-Host ""

# Check if paths need to be fixed
$needsFix = $false

if ($config.database -notlike "*$currentUsername*") {
    Write-Host "Database path needs to be fixed" -ForegroundColor Yellow
    $needsFix = $true
}

if ($config.backupFolder -notlike "*$currentUsername*") {
    Write-Host "Backup folder path needs to be fixed" -ForegroundColor Yellow
    $needsFix = $true
}

if (-not $needsFix) {
    Write-Host "Paths are already correct for this computer!" -ForegroundColor Green
    exit 0
}

Write-Host ""
Write-Host "Fixing paths..." -ForegroundColor Yellow

# Fix the paths
$config.database = $correctDatabase
$config.backupFolder = $correctBackupFolder

# Write the fixed config back
$config | ConvertTo-Json -Depth 10 | Set-Content $configPath

Write-Host "Configuration updated successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "New configuration:" -ForegroundColor Yellow
Write-Host "  Database: $($config.database)" -ForegroundColor Green
Write-Host "  Backup Folder: $($config.backupFolder)" -ForegroundColor Green
Write-Host ""

# Fix .env database path inside the runtime Laravel app
$envPath = Join-Path $correctAppData "runtime\laravel-app\.env"
if (Test-Path $envPath) {
    Write-Host "Fixing .env database path..." -ForegroundColor Yellow
    $envContent = Get-Content $envPath -Raw
    $dbPathForEnv = $correctDatabase -replace '\\', '/'
    if ($envContent -match '(?m)^DB_DATABASE=.*$') {
        $envContent = $envContent -replace '(?m)^DB_DATABASE=.*$', "DB_DATABASE=$dbPathForEnv"
    } else {
        $envContent = $envContent.TrimEnd() + "`nDB_DATABASE=$dbPathForEnv`n"
    }
    Set-Content -Path $envPath -Value $envContent -NoNewline
    Write-Host ".env database path updated" -ForegroundColor Green
} else {
    Write-Host "No .env file found yet (app will create it on next launch)" -ForegroundColor Yellow
}

# Clear stale Laravel caches that may still point to old paths
$laravelPath = Join-Path $correctAppData "runtime\laravel-app"
$configCache = Join-Path $laravelPath "bootstrap\cache\config.php"
if (Test-Path $configCache) {
    Remove-Item $configCache -Force
    Write-Host "Removed stale config cache" -ForegroundColor Green
}

# Generate APP_KEY if missing (common cause of HTTP 500)
$phpPaths = @(
    "php",
    "C:\Program Files\Printa Signages\resources\runtime\php\php.exe"
)
$phpExe = $null
foreach ($path in $phpPaths) {
    try {
        $null = & $path --version 2>&1
        $phpExe = $path
        break
    } catch {
        continue
    }
}

if ($phpExe -and (Test-Path (Join-Path $laravelPath "artisan"))) {
    Push-Location $laravelPath
    try {
        $envCheck = Get-Content $envPath -Raw -ErrorAction SilentlyContinue
        if ($envCheck -and $envCheck -notmatch '(?m)^APP_KEY=base64:.+$') {
            Write-Host "Generating missing APP_KEY..." -ForegroundColor Yellow
            if ($envCheck -notmatch '(?m)^APP_KEY=') {
                $envCheck = "APP_KEY=`n" + $envCheck
                Set-Content $envPath $envCheck -NoNewline
            }
            & $phpExe artisan key:generate --force 2>&1 | Out-Null
            Write-Host "APP_KEY generated" -ForegroundColor Green
        }

        foreach ($cmd in @("config:clear", "cache:clear")) {
            & $phpExe artisan $cmd 2>&1 | Out-Null
        }
        Write-Host "Laravel caches cleared" -ForegroundColor Green
    } finally {
        Pop-Location
    }
}

# Ensure the database file exists at the correct location
if (-not (Test-Path $correctDatabase)) {
    Write-Host "WARNING: Database file not found at expected location" -ForegroundColor Yellow
    Write-Host "Expected: $correctDatabase" -ForegroundColor Yellow
    
    # Try to find it in the old location
    $oldDatabase = $config.database
    if (Test-Path $oldDatabase) {
        Write-Host "Found database at old location, copying..." -ForegroundColor Yellow
        $dbDir = Split-Path $correctDatabase -Parent
        New-Item -ItemType Directory -Force -Path $dbDir | Out-Null
        Copy-Item -LiteralPath $oldDatabase -Destination $correctDatabase -Force
        Write-Host "Database copied successfully" -ForegroundColor Green
    }
}

# Ensure backup folder exists
if (-not (Test-Path $correctBackupFolder)) {
    Write-Host "Creating backup folder..." -ForegroundColor Yellow
    New-Item -ItemType Directory -Force -Path $correctBackupFolder | Out-Null
    Write-Host "Backup folder created" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Migration Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "The configuration has been updated for this computer." -ForegroundColor White
Write-Host "Please restart the Printa Signages application." -ForegroundColor Yellow
Write-Host ""
