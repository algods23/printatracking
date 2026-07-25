# Printa Signages Repair Tool
# Fixes the runtime data in AppData (where the live app actually runs)

param(
    [string]$InstallPath = "C:\Program Files\Printa Signages"
)

$ErrorActionPreference = "Continue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Printa Signages Repair Tool" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

function Find-AppDataPath {
    foreach ($name in @("Printa Signages", "printa-signages")) {
        $testPath = Join-Path $env:APPDATA $name
        if (Test-Path $testPath) {
            return $testPath
        }
    }
    return $null
}

function Find-PhpExe {
    param([string]$InstallPath)

    $candidates = @(
        (Join-Path $InstallPath "resources\runtime\php\php.exe"),
        (Join-Path $InstallPath "runtime\php\php.exe"),
        "php"
    )

    foreach ($candidate in $candidates) {
        try {
            $null = & $candidate --version 2>&1
            return $candidate
        } catch {
            continue
        }
    }

    return $null
}

function Repair-LaravelPath {
    param(
        [string]$LaravelPath,
        [string]$DatabasePath,
        [string]$PhpExe
    )

    if (-not (Test-Path $LaravelPath)) {
        Write-Host "  Laravel path not found: $LaravelPath" -ForegroundColor Yellow
        return
    }

    Write-Host "  Repairing: $LaravelPath" -ForegroundColor Cyan

    $storageDirs = @(
        "storage",
        "storage\app",
        "storage\app\public",
        "storage\framework",
        "storage\framework\cache",
        "storage\framework\cache\data",
        "storage\framework\sessions",
        "storage\framework\views",
        "storage\logs",
        "bootstrap\cache"
    )

    foreach ($dir in $storageDirs) {
        $fullPath = Join-Path $LaravelPath $dir
        try {
            New-Item -ItemType Directory -Force -Path $fullPath | Out-Null
            Write-Host "    OK: $dir" -ForegroundColor Green
        } catch {
            Write-Host "    FAIL: $dir - $_" -ForegroundColor Red
        }
    }

    try {
        New-Item -ItemType File -Force -Path (Join-Path $LaravelPath "storage\logs\.gitkeep") | Out-Null
    } catch {}

    $envPath = Join-Path $LaravelPath ".env"
    $envDesktopPath = Join-Path $LaravelPath ".env.desktop"

    if (-not (Test-Path $envPath) -and (Test-Path $envDesktopPath)) {
        Copy-Item -LiteralPath $envDesktopPath -Destination $envPath -Force
        Write-Host "    Created .env from .env.desktop" -ForegroundColor Green
    }

    if (Test-Path $envPath) {
        $envContent = Get-Content $envPath -Raw
        $dbPathForEnv = $DatabasePath -replace '\\', '/'

        if ($envContent -match '(?m)^DB_DATABASE=.*$') {
            $envContent = $envContent -replace '(?m)^DB_DATABASE=.*$', "DB_DATABASE=$dbPathForEnv"
        } else {
            $envContent = $envContent.TrimEnd() + "`nDB_DATABASE=$dbPathForEnv`n"
        }

        Set-Content -Path $envPath -Value $envContent -NoNewline
        Write-Host "    Updated DB_DATABASE in .env" -ForegroundColor Green
    }

    $configCache = Join-Path $LaravelPath "bootstrap\cache\config.php"
    if (Test-Path $configCache) {
        Remove-Item $configCache -Force
        Write-Host "    Removed stale config cache" -ForegroundColor Green
    }

    $artisanPath = Join-Path $LaravelPath "artisan"
    if ($PhpExe -and (Test-Path $artisanPath)) {
        Push-Location $LaravelPath
        try {
            foreach ($cmd in @("config:clear", "cache:clear", "route:clear", "view:clear")) {
                try {
                    & $PhpExe artisan $cmd 2>&1 | Out-Null
                    Write-Host "    Cleared: $cmd" -ForegroundColor Green
                } catch {
                    Write-Host "    Skipped: $cmd" -ForegroundColor Yellow
                }
            }

            $envCheck = Get-Content $envPath -Raw -ErrorAction SilentlyContinue
            if ($envCheck -and $envCheck -notmatch '(?m)^APP_KEY=base64:.+$') {
                if ($envCheck -notmatch '(?m)^APP_KEY=') {
                    $envCheck = "APP_KEY=`n" + $envCheck
                    Set-Content $envPath $envCheck -NoNewline
                }
                & $PhpExe artisan key:generate --force 2>&1 | Out-Null
                Write-Host "    Generated missing APP_KEY" -ForegroundColor Green
            }
        } finally {
            Pop-Location
        }
    }
}

$appDataPath = Find-AppDataPath

if (-not $appDataPath) {
    Write-Host "ERROR: Printa Signages AppData folder not found." -ForegroundColor Red
    Write-Host "Expected: $env:APPDATA\Printa Signages" -ForegroundColor Yellow
    exit 1
}

Write-Host "AppData found at: $appDataPath" -ForegroundColor Green

$runtimeLaravelPath = Join-Path $appDataPath "runtime\laravel-app"
$databasePath = Join-Path $appDataPath "runtime\database\database.sqlite"
$configPath = Join-Path $appDataPath "config.json"
$backupFolder = Join-Path $env:USERPROFILE "Documents\Printa Signages\backups"

Write-Host ""
Write-Host "Fixing config.json..." -ForegroundColor Yellow

$config = @{
    appName = "Printa Signages"
    httpPort = 8000
    database = $databasePath
    backupFolder = $backupFolder
    installedAt = (Get-Date).ToString("o")
}

if (Test-Path $configPath) {
    try {
        $existing = Get-Content $configPath -Raw | ConvertFrom-Json
        if ($existing.installedAt) {
            $config.installedAt = $existing.installedAt
        }
    } catch {}
}

$config | ConvertTo-Json -Depth 10 | Set-Content $configPath
Write-Host "  config.json updated" -ForegroundColor Green

Write-Host ""
Write-Host "Ensuring database folder exists..." -ForegroundColor Yellow
$dbDir = Split-Path $databasePath -Parent
New-Item -ItemType Directory -Force -Path $dbDir | Out-Null
New-Item -ItemType Directory -Force -Path $backupFolder | Out-Null

if (-not (Test-Path $databasePath)) {
    Write-Host "  WARNING: database.sqlite is missing!" -ForegroundColor Red
    Write-Host "  Expected: $databasePath" -ForegroundColor Yellow
    Write-Host "  Copy the database from the old computer into this folder." -ForegroundColor Yellow
} else {
    $size = (Get-Item $databasePath).Length
    Write-Host "  Database found ($size bytes)" -ForegroundColor Green
}

$phpExe = Find-PhpExe -InstallPath $InstallPath
if ($phpExe) {
    Write-Host "  PHP found: $phpExe" -ForegroundColor Green
} else {
    Write-Host "  PHP not found - storage folders will still be repaired" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Repairing runtime Laravel app..." -ForegroundColor Yellow
Repair-LaravelPath -LaravelPath $runtimeLaravelPath -DatabasePath $databasePath -PhpExe $phpExe

if (Test-Path (Join-Path $InstallPath "resources\laravel-app")) {
    Write-Host ""
    Write-Host "Repairing install copy (optional)..." -ForegroundColor Yellow
    Repair-LaravelPath -LaravelPath (Join-Path $InstallPath "resources\laravel-app") -DatabasePath $databasePath -PhpExe $phpExe
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Repair Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Your data was NOT deleted." -ForegroundColor Green
Write-Host "Please restart Printa Signages." -ForegroundColor Yellow
Write-Host ""
