param(
    [string]$StageDir = "desktop-stage-laravel-app-current"
)

$ErrorActionPreference = "Stop"
$root = Resolve-Path (Join-Path $PSScriptRoot "..")
$stage = if ([System.IO.Path]::IsPathRooted($StageDir)) {
    $StageDir
} else {
    Join-Path $root $StageDir
}

if (Test-Path $stage) {
    Remove-Item -LiteralPath $stage -Recurse -Force
}

New-Item -ItemType Directory -Force -Path $stage | Out-Null

$items = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "public",
    "resources",
    "routes",
    "vendor",
    "artisan",
    "composer.json",
    "composer.lock",
    ".env.desktop"
)

foreach ($item in $items) {
    $source = Join-Path $root $item
    if (Test-Path $source) {
        Copy-Item -LiteralPath $source -Destination $stage -Recurse -Force
    }
}

Remove-Item -LiteralPath (Join-Path $stage "database\database.sqlite") -Force -ErrorAction SilentlyContinue
Remove-Item -LiteralPath (Join-Path $stage "public\storage") -Recurse -Force -ErrorAction SilentlyContinue

$storageDirs = @(
    "storage\app\public",
    "storage\framework\cache",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs",
    "bootstrap\cache"
)

foreach ($dir in $storageDirs) {
    New-Item -ItemType Directory -Force -Path (Join-Path $stage $dir) | Out-Null
}

Write-Host "Laravel desktop stage prepared at $stage"
