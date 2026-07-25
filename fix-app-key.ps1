# Fix missing APP_KEY - the exact cause of HTTP 500 on Printa Signages
# Run on the broken PC: Right-click -> Run with PowerShell

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "Printa Signages - Fix APP_KEY" -ForegroundColor Cyan
Write-Host "==============================" -ForegroundColor Cyan
Write-Host ""

$appDataNames = @("Printa Signages", "printa-signages")
$appDataPath = $null
foreach ($name in $appDataNames) {
    $test = Join-Path $env:APPDATA $name
    if (Test-Path $test) { $appDataPath = $test; break }
}

if (-not $appDataPath) {
    Write-Host "ERROR: AppData folder not found." -ForegroundColor Red
    exit 1
}

$envPath = Join-Path $appDataPath "runtime\laravel-app\.env"
$laravelPath = Join-Path $appDataPath "runtime\laravel-app"

$phpPaths = @(
    "${env:ProgramFiles}\Printa Signages\resources\runtime\php\php.exe",
    "php"
)
$phpExe = $phpPaths | Where-Object { Test-Path $_ -or $_ -eq "php" } | ForEach-Object {
    try { $null = & $_ --version 2>&1; $_; break } catch {}
} | Select-Object -First 1

if (-not (Test-Path $envPath)) {
    Write-Host "ERROR: .env not found at $envPath" -ForegroundColor Red
    exit 1
}

$content = Get-Content $envPath -Raw

if ($content -notmatch '(?m)^APP_KEY=base64:.+$') {
    Write-Host "APP_KEY is missing or empty - fixing..." -ForegroundColor Yellow

    if ($content -notmatch '(?m)^APP_KEY=') {
        $content = "APP_KEY=`n" + $content
        Set-Content $envPath $content -NoNewline
        Write-Host "  Added APP_KEY= line to .env" -ForegroundColor Green
    }

    if (-not $phpExe) {
        Write-Host "ERROR: PHP not found. Cannot generate key." -ForegroundColor Red
        exit 1
    }

    Push-Location $laravelPath
    & $phpExe artisan key:generate --force
    & $phpExe artisan config:clear
    Pop-Location

    Write-Host "  APP_KEY generated successfully!" -ForegroundColor Green
} else {
    Write-Host "APP_KEY already set - no fix needed." -ForegroundColor Green
}

Write-Host ""
Write-Host "Done! Restart Printa Signages." -ForegroundColor Green
Write-Host ""
