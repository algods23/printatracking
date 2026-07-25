# Collect Printa Signages diagnostic files for troubleshooting
# Run on the computer that shows the 500 error, then upload the ZIP to your developer.

$ErrorActionPreference = "Continue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Printa Signages Diagnostic Collector" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$possibleAppDataNames = @("Printa Signages", "printa-signages")
$appDataPath = $null

foreach ($name in $possibleAppDataNames) {
    $testPath = Join-Path $env:APPDATA $name
    if (Test-Path $testPath) {
        $appDataPath = $testPath
        break
    }
}

if (-not $appDataPath) {
    Write-Host "ERROR: Printa Signages AppData folder not found." -ForegroundColor Red
    Write-Host "Expected one of:" -ForegroundColor Yellow
    foreach ($name in $possibleAppDataNames) {
        Write-Host "  $env:APPDATA\$name" -ForegroundColor White
    }
    exit 1
}

Write-Host "Found AppData at: $appDataPath" -ForegroundColor Green

$stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$outputDir = Join-Path $env:USERPROFILE "Desktop\printa-diagnostics-$stamp"
New-Item -ItemType Directory -Force -Path $outputDir | Out-Null

$filesToCopy = @(
    "config.json",
    "startup.log",
    "runtime\laravel-app\.env",
    "runtime\laravel-app\storage\logs\laravel.log",
    "runtime\.database-ready"
)

$copied = @()
$missing = @()

foreach ($relativePath in $filesToCopy) {
    $source = Join-Path $appDataPath $relativePath
    if (Test-Path $source) {
        $destDir = Join-Path $outputDir (Split-Path $relativePath -Parent)
        if ($destDir -and -not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Force -Path $destDir | Out-Null
        }
        Copy-Item -LiteralPath $source -Destination (Join-Path $outputDir $relativePath) -Force
        $copied += $relativePath
    } else {
        $missing += $relativePath
    }
}

$dbPath = Join-Path $appDataPath "runtime\database\database.sqlite"
$dbInfo = @{
    path = $dbPath
    exists = Test-Path $dbPath
    sizeBytes = if (Test-Path $dbPath) { (Get-Item $dbPath).Length } else { 0 }
}
$dbInfo | ConvertTo-Json | Set-Content (Join-Path $outputDir "database-info.json")

$summary = @"
Printa Signages Diagnostic Report
Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Computer: $env:COMPUTERNAME
User: $env:USERNAME
AppData: $appDataPath

Files copied:
$([string]::Join("`n", ($copied | ForEach-Object { "  - $_" })))

Files missing:
$([string]::Join("`n", ($missing | ForEach-Object { "  - $_" })))

Database exists: $($dbInfo.exists)
Database size: $($dbInfo.sizeBytes) bytes
"@

Set-Content -Path (Join-Path $outputDir "summary.txt") -Value $summary

$zipPath = "$outputDir.zip"
if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}
Compress-Archive -Path "$outputDir\*" -DestinationPath $zipPath -Force

Write-Host ""
Write-Host "Diagnostics saved to:" -ForegroundColor Green
Write-Host "  $zipPath" -ForegroundColor White
Write-Host ""
Write-Host "Please upload this ZIP file so the developer can find the 500 error cause." -ForegroundColor Yellow
Write-Host ""
