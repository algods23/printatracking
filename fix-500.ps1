# One-click fix for Printa Signages HTTP 500 after moving to another computer
# Copy this file to the broken PC, right-click -> Run with PowerShell

$ErrorActionPreference = "Continue"

Write-Host ""
Write-Host "Printa Signages - Fix HTTP 500" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$migrateScript = Join-Path $scriptDir "migrate-data.ps1"
$repairScript = Join-Path $scriptDir "repair-tool.ps1"

if (-not (Test-Path $migrateScript)) {
    $migrateScript = Join-Path $env:ProgramFiles "Printa Signages\resources\migrate-data.ps1"
}
if (-not (Test-Path $repairScript)) {
    $repairScript = Join-Path $env:ProgramFiles "Printa Signages\resources\repair-tool.ps1"
}

if (Test-Path $migrateScript) {
    Write-Host "Step 1: Fixing paths for this computer..." -ForegroundColor Yellow
    & $migrateScript
} else {
    Write-Host "Step 1: migrate-data.ps1 not found, skipping path migration" -ForegroundColor Yellow
}

Write-Host ""

if (Test-Path $repairScript) {
    Write-Host "Step 2: Repairing storage and caches..." -ForegroundColor Yellow
    & $repairScript
} else {
    Write-Host "Step 2: repair-tool.ps1 not found, skipping repair" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Done! Restart Printa Signages." -ForegroundColor Green
Write-Host ""
Write-Host "If it still shows 500, run collect-diagnostics.ps1 and send the ZIP to your developer." -ForegroundColor Yellow
Write-Host ""
