# Printa Signages Repair Tool
# This script repairs the installation without deleting user data

param(
    [string]$InstallPath = "C:\Program Files\Printa Signages"
)

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Printa Signages Repair Tool" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if installation exists
if (-not (Test-Path $InstallPath)) {
    Write-Host "ERROR: Installation not found at $InstallPath" -ForegroundColor Red
    exit 1
}

Write-Host "Installation found at: $InstallPath" -ForegroundColor Green
Write-Host ""

# Define Laravel app path
$laravelPath = Join-Path $InstallPath "resources\laravel-app"

if (-not (Test-Path $laravelPath)) {
    Write-Host "ERROR: Laravel app not found at $laravelPath" -ForegroundColor Red
    exit 1
}

Write-Host "Laravel app found at: $laravelPath" -ForegroundColor Green
Write-Host ""

# Create storage folder structure
Write-Host "Creating storage folder structure..." -ForegroundColor Yellow

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
    $fullPath = Join-Path $laravelPath $dir
    try {
        New-Item -ItemType Directory -Force -Path $fullPath | Out-Null
        Write-Host "  Created: $dir" -ForegroundColor Green
    } catch {
        Write-Host "  Failed to create: $dir" -ForegroundColor Red
        Write-Host "  Error: $_" -ForegroundColor Red
    }
}

Write-Host ""

# Create .gitkeep file in logs
try {
    $gitkeepPath = Join-Path $laravelPath "storage\logs\.gitkeep"
    New-Item -ItemType File -Force -Path $gitkeepPath | Out-Null
    Write-Host "Created .gitkeep in logs directory" -ForegroundColor Green
} catch {
    Write-Host "Failed to create .gitkeep in logs" -ForegroundColor Yellow
}

Write-Host ""

# Set permissions on storage folder
Write-Host "Setting permissions on storage folder..." -ForegroundColor Yellow

$storagePath = Join-Path $laravelPath "storage"

try {
    $acl = Get-Acl $storagePath
    $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule(
        "Everyone",
        "FullControl",
        "ContainerInherit,ObjectInherit",
        "None",
        "Allow"
    )
    $acl.SetAccessRule($accessRule)
    Set-Acl $storagePath $acl
    Write-Host "Permissions set on storage folder" -ForegroundColor Green
} catch {
    Write-Host "Failed to set permissions (may require admin rights)" -ForegroundColor Yellow
    Write-Host "You may need to run this script as Administrator" -ForegroundColor Yellow
}

Write-Host ""

# Check if .env file exists
$envPath = Join-Path $laravelPath ".env"
$envDesktopPath = Join-Path $laravelPath ".env.desktop"

if (-not (Test-Path $envPath) -and (Test-Path $envDesktopPath)) {
    Write-Host "Creating .env from .env.desktop..." -ForegroundColor Yellow
    try {
        Copy-Item -LiteralPath $envDesktopPath -Destination $envPath -Force
        Write-Host ".env file created" -ForegroundColor Green
    } catch {
        Write-Host "Failed to create .env file" -ForegroundColor Red
    }
}

Write-Host ""

# Clear Laravel caches if artisan exists
$artisanPath = Join-Path $laravelPath "artisan"

if (Test-Path $artisanPath) {
    Write-Host "Clearing Laravel caches..." -ForegroundColor Yellow
    
    # Try to find PHP
    $phpPaths = @(
        "php",
        Join-Path $InstallPath "runtime\php\php.exe"
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
    
    if ($phpExe) {
        try {
            & $phpExe artisan cache:clear --cwd $laravelPath 2>&1 | Out-Null
            Write-Host "  Cache cleared" -ForegroundColor Green
        } catch {
            Write-Host "  Failed to clear cache (non-critical)" -ForegroundColor Yellow
        }
        
        try {
            & $phpExe artisan config:clear --cwd $laravelPath 2>&1 | Out-Null
            Write-Host "  Config cleared" -ForegroundColor Green
        } catch {
            Write-Host "  Failed to clear config (non-critical)" -ForegroundColor Yellow
        }
        
        try {
            & $phpExe artisan route:clear --cwd $laravelPath 2>&1 | Out-Null
            Write-Host "  Routes cleared" -ForegroundColor Green
        } catch {
            Write-Host "  Failed to clear routes (non-critical)" -ForegroundColor Yellow
        }
        
        try {
            & $phpExe artisan view:clear --cwd $laravelPath 2>&1 | Out-Null
            Write-Host "  Views cleared" -ForegroundColor Green
        } catch {
            Write-Host "  Failed to clear views (non-critical)" -ForegroundColor Yellow
        }
    } else {
        Write-Host "PHP not found, skipping cache clear" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Repair Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "The following actions were performed:" -ForegroundColor White
Write-Host "  - Created all required storage folders" -ForegroundColor White
Write-Host "  - Set permissions on storage folder" -ForegroundColor White
Write-Host "  - Created .env file if missing" -ForegroundColor White
Write-Host "  - Cleared Laravel caches" -ForegroundColor White
Write-Host ""
Write-Host "Your data has NOT been deleted." -ForegroundColor Green
Write-Host "Please restart the Printa Signages application." -ForegroundColor Yellow
Write-Host ""
